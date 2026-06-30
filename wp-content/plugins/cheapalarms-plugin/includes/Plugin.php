<?php

namespace CheapAlarms\Plugin;

use CheapAlarms\Plugin\Admin\UserCapabilities;
use CheapAlarms\Plugin\Config\Config;
use CheapAlarms\Plugin\Db\Schema;
use CheapAlarms\Plugin\Frontend\PortalPage;
use CheapAlarms\Plugin\REST\ApiKernel;
use CheapAlarms\Plugin\REST\Auth\Authenticator;
use CheapAlarms\Plugin\Services\AuthorizationService;
use CheapAlarms\Plugin\Services\Container;
use CheapAlarms\Plugin\Services\Logger;
use CheapAlarms\Plugin\Services\SentryService;
use CheapAlarms\Plugin\Services\ProductRepository;
use CheapAlarms\Plugin\Middleware\RequestIdMiddleware;
use CheapAlarms\Plugin\Middleware\RateLimitHeaderMiddleware;
use CheapAlarms\Plugin\Services\Estimate\EstimateSnapshotRepository;
use CheapAlarms\Plugin\Services\Estimate\EstimateSnapshotSyncService;
use CheapAlarms\Plugin\Services\Invoice\InvoiceEstimateLinkRepository;
use CheapAlarms\Plugin\Services\Invoice\InvoiceSnapshotRepository;
use CheapAlarms\Plugin\Services\Invoice\InvoiceSnapshotSyncService;
use CheapAlarms\Plugin\Services\Contact\ContactSnapshotRepository;
use CheapAlarms\Plugin\Services\Contact\ContactSnapshotSyncService;
use CheapAlarms\Plugin\Services\Product\ProductSnapshotRepository;
use CheapAlarms\Plugin\Services\Product\ProductSnapshotSyncService;
use CheapAlarms\Plugin\REST\Controllers\HealthController;
use CheapAlarms\Plugin\REST\Controllers\SetupBootstrapController;
use CheapAlarms\Plugin\REST\Controllers\CalculatorController;
use CheapAlarms\Plugin\Services\ServiceM8\Sm8JobSnapshotRepository;
use CheapAlarms\Plugin\Services\ServiceM8\Sm8CompanySnapshotRepository;
use CheapAlarms\Plugin\Services\ServiceM8\Sm8SnapshotSyncService;

use function add_action;
use function add_filter;
use function add_role;
use function get_role;
use function header;
use function in_array;
use function status_header;

class Plugin
{
    private static ?Plugin $instance = null;

    private Container $container;

    /**
     * @var array<string, array{label:string, capabilities:array<string,bool>}>
     */
    private const ROLE_DEFINITIONS = [
        'ca_superadmin' => [
            'label'        => 'Portal Superadmin',
            'capabilities' => [
                'read'                => true,
                'ca_manage_portal'    => true,
                'ca_manage_support'   => true,
                'ca_view_estimates'   => true,
                'ca_invite_customers' => true,
                'ca_access_portal'    => true,
                'ca_manage_settings'  => true,
            ],
        ],
        'ca_admin' => [
            'label'        => 'Portal Admin',
            'capabilities' => [
                'read'                => true,
                'ca_manage_portal'    => true,
                'ca_manage_support'   => true,
                'ca_view_estimates'   => true,
                'ca_invite_customers' => true,
                'ca_access_portal'    => true,
            ],
        ],
        'ca_moderator' => [
            'label'        => 'Portal Moderator',
            'capabilities' => [
                'read'              => true,
                'ca_view_estimates' => true,
                'ca_access_portal'  => true,
            ],
        ],
        'ca_support' => [
            'label'        => 'Portal Support',
            'capabilities' => [
                'read'                => true,
                'ca_manage_support'   => true,
                'ca_access_portal'    => true,
            ],
        ],
        'ca_customer' => [
            'label'        => 'Portal Customer',
            'capabilities' => [
                'read'             => true,
                'ca_access_portal' => true,
            ],
        ],
    ];

    private const ADMIN_GRANTED_CAPS = [
        'ca_manage_portal',
        'ca_manage_support',
        'ca_view_estimates',
        'ca_invite_customers',
        'ca_access_portal',
    ];

    private function __construct()
    {
        $this->container = new Container();
    }

    public static function instance(): Plugin
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function boot(): void
    {
        add_action('init', [$this, 'bootstrap']);
    }

    public function bootstrap(): void
    {
        if (function_exists('error_log')) {
            error_log('[CA Plugin] bootstrap executing');
        }
        
        // SECURITY: Validate configuration before proceeding
        // Instantiate Config directly since it has no dependencies and needs to be validated early
        $config = new Config();
        if (!$config->isConfigured()) {
            $missing = [];
            if (empty($config->getGhlToken())) {
                $missing[] = 'ghl_token';
            }
            if (empty($config->getLocationId())) {
                $missing[] = 'ghl_location_id';
            }
            if (empty($config->getUploadSharedSecret())) {
                $missing[] = 'upload_shared_secret';
            }
            
            $missingStr = implode(', ', $missing);
            error_log('[CA] Configuration error: Missing required secrets: ' . $missingStr);

            Schema::maybeMigrate();
            $this->bootstrapPublicCalculatorApi($config, $missingStr);

            return;
        }
        
        // Initialize Sentry early (before other services)
        $this->initializeSentry();

        // Initialize request ID tracking early (before services that need it)
        $this->initializeRequestId();

        // Run schema upgrades only when needed (versioned).
        Schema::maybeMigrate();
        $this->registerRoles();
        $this->registerServices();
        $this->container->get(AuthorizationService::class)->normalizeLegacyCustomerUsers();
        $this->container->get(Authenticator::class)->boot();
        
        // Register WP-CLI commands
        if (defined('WP_CLI') && WP_CLI) {
            $this->registerCliCommands();
        }

        // Background sync hook for estimate snapshots (WP-Cron).
        add_action('ca_sync_estimate_snapshots', function (string $locationId) {
            $this->container->get(EstimateSnapshotSyncService::class)->syncLocation($locationId);
        }, 10, 1);

        // Background sync hook for invoice snapshots (WP-Cron).
        add_action('ca_sync_invoice_snapshots', function (string $locationId) {
            $this->container->get(InvoiceSnapshotSyncService::class)->syncLocation($locationId);
        }, 10, 1);

        // Invoice→estimate link write-through. Fires whenever an estimate's portal meta is
        // saved; if the merged meta carries an invoice.id, we upsert it into the link index.
        // This is what replaces the old wp_options LIKE-scan reverse lookup.
        add_action('ca_portal_meta_updated', function (string $estimateId, array $meta) {
            $invoiceId = $meta['invoice']['id'] ?? null;
            if (!is_string($invoiceId) || $invoiceId === '') {
                return;
            }
            $locationId = is_string($meta['locationId'] ?? null) ? $meta['locationId'] : null;
            $this->container->get(InvoiceEstimateLinkRepository::class)->link($invoiceId, $estimateId, $locationId);
        }, 10, 2);

        // One-time backfill of the link index from existing portal meta.
        // Runs only when the table is empty (i.e. immediately after the schema migration that
        // created it). Idempotent — safe to re-run; bounded by N portal-meta rows.
        $this->maybeBackfillInvoiceEstimateLinks();

        // Background sync hook for contact snapshots (WP-Cron).
        add_action('ca_sync_contact_snapshots', function (string $locationId) {
            $this->container->get(ContactSnapshotSyncService::class)->syncLocation($locationId);
        }, 10, 1);

        // GHL product catalog + prices → local snapshots (chunked price batches).
        add_action('ca_sync_product_snapshots', function (string $locationId) {
            $this->container->get(ProductSnapshotSyncService::class)->startSync($locationId);
        }, 10, 1);

        add_action('ca_sync_product_price_batch', function (string $locationId, int $offset, int $batchSize) {
            $this->container->get(ProductSnapshotSyncService::class)->syncPriceBatch($locationId, $offset, $batchSize);
        }, 10, 3);

        if (!wp_next_scheduled('ca_sync_product_snapshots_daily')) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'ca_sync_product_snapshots_daily');
        }
        add_action('ca_sync_product_snapshots_daily', function () {
            $locationId = $this->container->get(Config::class)->getLocationId();
            if ($locationId !== '' && !wp_next_scheduled('ca_sync_product_snapshots', [$locationId])) {
                wp_schedule_single_event(time() + 1, 'ca_sync_product_snapshots', [$locationId]);
            }
        });

        // Retention cleanup job (daily) - permanently delete estimates soft-deleted > 30 days
        add_action('ca_cleanup_expired_deletions', function () {
            $this->container->get(\CheapAlarms\Plugin\Services\Estimate\RetentionCleanupService::class)->cleanup();
        }, 10, 0);

        // Schedule recurring retention cleanup job (if not already scheduled)
        if (!wp_next_scheduled('ca_cleanup_expired_deletions_daily')) {
            wp_schedule_event(time() + (2 * HOUR_IN_SECONDS), 'daily', 'ca_cleanup_expired_deletions_daily');
        }

        add_action('ca_cleanup_expired_deletions_daily', function () {
            wp_schedule_single_event(time() + 1, 'ca_cleanup_expired_deletions');
        });

        // Register webhook processing hooks
        add_action('ca_process_stripe_webhook', function (string $eventId) {
            $this->container->get(\CheapAlarms\Plugin\Services\StripeWebhookProcessor::class)
                ->processEvent($eventId);
        }, 10, 1);

        // GHL webhook processing (async, scheduled by GhlWebhookController)
        add_action('ca_process_ghl_webhook', function (string $webhookId) {
            $this->container->get(\CheapAlarms\Plugin\Services\Ghl\GhlWebhookProcessor::class)
                ->processEvent($webhookId);
        }, 10, 1);

        // Register custom cron schedules (must be registered globally, not conditionally)
        // This ensures WordPress can find the schedule when rescheduling events
        add_filter('cron_schedules', function ($schedules) {
            if (!isset($schedules['ca_every_5_minutes'])) {
                $schedules['ca_every_5_minutes'] = [
                    'interval' => 300, // 5 minutes
                    'display'  => __('Every 5 Minutes', 'cheapalarms'),
                ];
            }
            if (!isset($schedules['ca_every_10_minutes'])) {
                $schedules['ca_every_10_minutes'] = [
                    'interval' => 600, // 10 minutes
                    'display'  => __('Every 10 Minutes', 'cheapalarms'),
                ];
            }
            if (!isset($schedules['ca_every_30_minutes'])) {
                $schedules['ca_every_30_minutes'] = [
                    'interval' => 1800, // 30 minutes
                    'display'  => __('Every 30 Minutes', 'cheapalarms'),
                ];
            }
            return $schedules;
        });

        // Retry failed webhooks (every 5 minutes)
        add_action('ca_retry_failed_webhooks', function () {
            $this->container->get(\CheapAlarms\Plugin\Services\WebhookRetryService::class)
                ->retryPendingEvents();
        }, 10, 0);

        // Schedule retry job (every 5 minutes)
        if (!wp_next_scheduled('ca_retry_failed_webhooks_recurring')) {
            wp_schedule_event(time() + 300, 'ca_every_5_minutes', 'ca_retry_failed_webhooks_recurring');
        }

        add_action('ca_retry_failed_webhooks_recurring', function () {
            wp_schedule_single_event(time() + 1, 'ca_retry_failed_webhooks');
        });

        // Retry failed GHL webhooks (every 5 minutes, uses same schedule)
        add_action('ca_retry_failed_ghl_webhooks', function () {
            $processor = $this->container->get(\CheapAlarms\Plugin\Services\Ghl\GhlWebhookProcessor::class);
            $eventRepo = $this->container->get(\CheapAlarms\Plugin\Services\Ghl\GhlWebhookEventRepository::class);
            $logger    = $this->container->get(Logger::class);

            $pending = $eventRepo->getPendingEvents(50);
            foreach ($pending as $event) {
                $result = $processor->processEvent($event['webhook_id']);
                if (is_wp_error($result)) {
                    $logger->warning('GHL webhook retry failed', [
                        'webhookId'  => $event['webhook_id'],
                        'error'      => $result->get_error_message(),
                        'retryCount' => $event['retry_count'],
                    ]);
                }
            }
        }, 10, 0);

        // Schedule GHL webhook retry job (every 5 minutes)
        if (!wp_next_scheduled('ca_retry_failed_ghl_webhooks_recurring')) {
            wp_schedule_event(time() + 300, 'ca_every_5_minutes', 'ca_retry_failed_ghl_webhooks_recurring');
        }

        add_action('ca_retry_failed_ghl_webhooks_recurring', function () {
            wp_schedule_single_event(time() + 1, 'ca_retry_failed_ghl_webhooks');
        });

        // ── ServiceM8 snapshot sync (no webhooks — poll-based) ─────────────
        // Single-event hook: sync SM8 jobs
        add_action('ca_sync_sm8_jobs', function () {
            $this->container->get(Sm8SnapshotSyncService::class)->syncJobs();
        }, 10, 0);

        // Single-event hook: sync SM8 companies
        add_action('ca_sync_sm8_companies', function () {
            $this->container->get(Sm8SnapshotSyncService::class)->syncCompanies();
        }, 10, 0);

        // Single-event hook: full SM8 sync (jobs + companies)
        add_action('ca_sync_sm8_all', function () {
            $this->container->get(Sm8SnapshotSyncService::class)->syncAll();
        }, 10, 0);

        // Recurring: sync SM8 jobs every 10 minutes (stale tier = 15 min)
        if (!wp_next_scheduled('ca_sync_sm8_jobs_recurring')) {
            wp_schedule_event(time() + 600, 'ca_every_10_minutes', 'ca_sync_sm8_jobs_recurring');
        }
        add_action('ca_sync_sm8_jobs_recurring', function () {
            wp_schedule_single_event(time() + 1, 'ca_sync_sm8_jobs');
        });

        // Recurring: sync SM8 companies every 30 minutes (stale tier = 30 min)
        if (!wp_next_scheduled('ca_sync_sm8_companies_recurring')) {
            wp_schedule_event(time() + 1800, 'ca_every_30_minutes', 'ca_sync_sm8_companies_recurring');
        }
        add_action('ca_sync_sm8_companies_recurring', function () {
            wp_schedule_single_event(time() + 1, 'ca_sync_sm8_companies');
        });

        // Recurring: full SM8 sync daily (jobs + companies — catches anything missed)
        if (!wp_next_scheduled('ca_sync_sm8_all_daily')) {
            wp_schedule_event(time() + DAY_IN_SECONDS, 'daily', 'ca_sync_sm8_all_daily');
        }
        add_action('ca_sync_sm8_all_daily', function () {
            wp_schedule_single_event(time() + 1, 'ca_sync_sm8_all');
        });

        // Register Xero sync retry handler
        add_action('ca_retry_xero_sync', function (string $estimateId, string $ghlInvoiceId, string $locationId) {
            try {
                $portalService = $this->container->get(\CheapAlarms\Plugin\Services\PortalService::class);
                // syncInvoiceToXero is now public, can call directly
                $portalService->syncInvoiceToXero($estimateId, $ghlInvoiceId, $locationId);
            } catch (\Exception $e) {
                $logger = $this->container->get(\CheapAlarms\Plugin\Services\Logger::class);
                $logger->error('Failed to execute Xero sync retry', [
                    'estimateId' => $estimateId,
                    'ghlInvoiceId' => $ghlInvoiceId,
                    'error' => $e->getMessage(),
                ]);
            }
        }, 10, 3);

        // Register payment intent expiry cleanup (daily)
        // FIXED: Capture container in closure to avoid $this context issues
        // FIXED: Add batching and better edge case handling
        $container = $this->container;
        add_action('ca_cleanup_expired_payment_intents', function () use ($container) {
            try {
                global $wpdb;
                $optionName = 'ca_portal_meta_%';
                $batchSize = 100; // Process in batches to avoid memory issues
                $offset = 0;
                $cleaned = 0;
                $currentTime = time();
                
                do {
                    // FIXED: Add LIMIT and OFFSET for batching
                    $results = $wpdb->get_results($wpdb->prepare(
                        "SELECT option_name, option_value 
                         FROM {$wpdb->options} 
                         WHERE option_name LIKE %s
                         LIMIT %d OFFSET %d",
                        $optionName,
                        $batchSize,
                        $offset
                    ), ARRAY_A);
                    
                    if (empty($results)) {
                        break;
                    }
                    
                    foreach ($results as $row) {
                        $meta = maybe_unserialize($row['option_value']);
                        if (!is_array($meta)) continue;
                        
                        $payment = $meta['payment'] ?? [];
                        $expiresAt = $payment['paymentIntentExpiresAt'] ?? null;
                        $paymentIntentId = $payment['paymentIntentId'] ?? null;
                        
                        // If payment intent expired and no successful payment recorded, clean it up
                        if ($expiresAt && $currentTime > (int)$expiresAt && !empty($paymentIntentId)) {
                            $hasSuccessfulPayment = false;
                            
                            // Check payments array for successful payments
                            if (!empty($payment['payments']) && is_array($payment['payments'])) {
                                foreach ($payment['payments'] as $p) {
                                    $paymentStatus = $p['status'] ?? '';
                                    if ($paymentStatus === 'succeeded') {
                                        $hasSuccessfulPayment = true;
                                        break;
                                    }
                                }
                            }
                            
                            // FIXED: Extract estimateId from option_name to check for active payment lock
                            // Format: ca_portal_meta_{estimateId}
                            $prefix = 'ca_portal_meta_';
                            $estimateId = str_replace($prefix, '', $row['option_name']);
                            // Safety check: verify prefix was actually removed
                            if ($estimateId === $row['option_name']) {
                                // Prefix not found, skip lock check
                                $estimateId = null;
                            }
                            
                            // FIXED: Check if payment confirmation is currently in progress via lock
                            $hasPaymentInProgress = false;
                            if ($estimateId) {
                                $paymentLockKey = 'ca_payment_lock_' . $estimateId;
                                $lockValue = get_transient($paymentLockKey);
                                if ($lockValue !== false) {
                                    // Check if lock is stale (older than 10 seconds)
                                    $lockAge = $currentTime - (int)$lockValue;
                                    if ($lockAge <= 10) {
                                        $hasPaymentInProgress = true;
                                    }
                                }
                            }
                            
                            // FIXED: Only clean up if no successful payment AND no payment in progress
                            if (!$hasSuccessfulPayment && !$hasPaymentInProgress) {
                                $meta['payment']['paymentIntentId'] = null;
                                $meta['payment']['paymentIntentExpiresAt'] = null;
                                
                                update_option($row['option_name'], $meta);
                                $cleaned++;
                            }
                        }
                    }
                    
                    $offset += $batchSize;
                } while (count($results) === $batchSize);
                
                if ($cleaned > 0) {
                    $logger = $container->get(\CheapAlarms\Plugin\Services\Logger::class);
                    $logger->info('Cleaned up expired payment intents', ['count' => $cleaned]);
                }
            } catch (\Exception $e) {
                $logger = $container->get(\CheapAlarms\Plugin\Services\Logger::class);
                $logger->error('Failed to cleanup expired payment intents', [
                    'error' => $e->getMessage(),
                ]);
            }
        }, 10, 0);

        // Schedule cleanup job (daily at 2 AM Australia/Brisbane)
        if (!wp_next_scheduled('ca_cleanup_expired_payment_intents')) {
            $tz = new \DateTimeZone('Australia/Brisbane');
            $dt = new \DateTime('tomorrow 02:00:00', $tz);
            wp_schedule_event($dt->getTimestamp(), 'daily', 'ca_cleanup_expired_payment_intents');
        }

        $this->registerCors();
        $this->registerRestEndpoints();
        $this->registerFrontend();
        $this->registerAdmin();
        $this->registerUserTracking();
    }

    /**
     * Register hooks for tracking user password and login status
     * Used for context-aware email personalization
     */
    private function registerUserTracking(): void
    {
        // Track when password is set (via password reset flow)
        add_action('after_password_reset', function($user, $new_password) {
            if ($user && isset($user->ID)) {
                update_user_meta($user->ID, 'ca_password_set_at', current_time('mysql'));
            }
        }, 10, 2);

        // Track when user logs in
        add_action('wp_login', function($user_login, $user) {
            if ($user && isset($user->ID)) {
                update_user_meta($user->ID, 'ca_last_login', current_time('mysql'));
                // Also set password_set_at if not already set (user logged in = has password)
                if (!get_user_meta($user->ID, 'ca_password_set_at', true)) {
                    update_user_meta($user->ID, 'ca_password_set_at', current_time('mysql'));
                }
            }
        }, 10, 2);

        // Track password set via wp_set_password action (WordPress 6.2+)
        add_action('wp_set_password', function($password, $user_id, $old_user_data) {
            if ($user_id) {
                update_user_meta($user_id, 'ca_password_set_at', current_time('mysql'));
            }
        }, 10, 3);
    }

    /**
     * Initialize Sentry error tracking early
     */
    private function initializeSentry(): void
    {
        try {
            $config = new Config();
            $sentry = new SentryService($config);
            $sentry->init();

            // Register Sentry in container
            $this->container->set(SentryService::class, fn () => $sentry);

            // Set up PHP error handler to catch fatal errors
            $this->registerPhpErrorHandler($sentry);
        } catch (\Throwable $e) {
            // Don't fail if Sentry initialization fails
            if (function_exists('error_log')) {
                error_log('[CA] Sentry initialization failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Initialize request ID middleware
     */
    private function initializeRequestId(): void
    {
        try {
            // Get logger (create temporary instance if needed)
            $logger = $this->container->has(Logger::class) 
                ? $this->container->get(Logger::class)
                : new Logger();

            $middleware = new RequestIdMiddleware($logger);
            $middleware->init($this->container);

            // Register middleware to add request ID header to REST responses
            add_filter('rest_pre_serve_request', [$middleware, 'addRequestIdHeader'], 10, 4);
            
            // Register rate limit header middleware
            $rateLimitMiddleware = new RateLimitHeaderMiddleware();
            add_filter('rest_pre_serve_request', [$rateLimitMiddleware, 'addRateLimitHeaders'], 10, 4);
        } catch (\Throwable $e) {
            // Don't fail if request ID initialization fails
            if (function_exists('error_log')) {
                error_log('[CA] Request ID initialization failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Register PHP error handler to catch fatal errors
     * 
     * Note: This chains with WordPress's error handler by returning false,
     * allowing WordPress to handle errors normally while also sending to Sentry
     */
    private function registerPhpErrorHandler(SentryService $sentry): void
    {
        // Store previous handler in a variable that can be used in closure
        $previousHandlerRef = [null];
        
        // Set our error handler (set_error_handler returns the previous handler)
        $previousHandlerRef[0] = set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) use ($sentry, &$previousHandlerRef): bool {
            // Only handle errors that are not suppressed with @
            if (!(error_reporting() & $errno)) {
                // Call previous handler if it exists
                if ($previousHandlerRef[0] !== null) {
                    return call_user_func($previousHandlerRef[0], $errno, $errstr, $errfile, $errline);
                }
                return false;
            }

            // Convert error to exception for Sentry (only for errors, not warnings/notices)
            if (in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR], true)) {
                try {
                    $exception = new \ErrorException($errstr, 0, $errno, $errfile, $errline);
                    $sentry->captureException($exception, [
                        'error_type' => 'php_error',
                        'error_level' => $errno,
                    ]);
                } catch (\Throwable $e) {
                    // Don't break if Sentry fails
                }
            }

            // Call previous handler if it exists, otherwise return false to let PHP handle it
            if ($previousHandlerRef[0] !== null) {
                return call_user_func($previousHandlerRef[0], $errno, $errstr, $errfile, $errline);
            }
            return false;
        }, E_ALL & ~E_DEPRECATED & ~E_STRICT);

        // Register shutdown handler for fatal errors (runs after WordPress's handler)
        register_shutdown_function(function () use ($sentry): void {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true)) {
                try {
                    $exception = new \ErrorException(
                        $error['message'],
                        0,
                        $error['type'],
                        $error['file'],
                        $error['line']
                    );
                    $sentry->captureException($exception, [
                        'error_type' => 'fatal_error',
                    ]);
                } catch (\Throwable $e) {
                    // Don't break if Sentry fails
                }
            }
        });
    }

    /**
     * Run the one-time invoice→estimate link backfill if the index is empty.
     * Gated on a sentinel option so it does not re-scan wp_options on every request after a
     * legitimately-empty index (e.g. fresh install with no estimates yet).
     */
    private function maybeBackfillInvoiceEstimateLinks(): void
    {
        $sentinel = 'ca_invoice_estimate_links_backfilled';
        if (\get_option($sentinel) === '1') {
            return;
        }

        try {
            $repo  = $this->container->get(InvoiceEstimateLinkRepository::class);
            $count = $repo->backfillFromPortalMeta();
            \update_option($sentinel, '1', true);
            if ($count > 0 && function_exists('error_log')) {
                error_log(sprintf('[CA] Backfilled %d invoice→estimate links', $count));
            }
        } catch (\Throwable $e) {
            if (function_exists('error_log')) {
                error_log('[CA] Invoice→estimate link backfill failed: ' . $e->getMessage());
            }
        }
    }

    private function registerServices(): void
    {
        $this->container->set(Config::class, fn () => new Config());
        
        // Register SentryService if not already registered
        if (!$this->container->has(SentryService::class)) {
            $this->container->set(SentryService::class, fn () => new SentryService($this->container->get(Config::class)));
        }
        
        // Logger gets SentryService if available
        $this->container->set(Logger::class, function () {
            $sentry = $this->container->has(SentryService::class) 
                ? $this->container->get(SentryService::class) 
                : null;
            return new Logger($sentry);
        });
        $this->container->set(AuthorizationService::class, fn () => new AuthorizationService());
        $this->container->set(Authenticator::class, fn () => new Authenticator(
            $this->container->get(Config::class),
            $this->container->get(AuthorizationService::class)
        ));
        $this->container->set(ProductRepository::class, fn () => new ProductRepository());
        $this->container->set(\CheapAlarms\Plugin\Services\GhlClient::class, fn () => new \CheapAlarms\Plugin\Services\GhlClient(
            $this->container->get(Config::class),
            $this->container->get(Logger::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\EstimateService::class, fn () => new \CheapAlarms\Plugin\Services\EstimateService(
            $this->container->get(Config::class),
            $this->container->get(\CheapAlarms\Plugin\Services\GhlClient::class),
            $this->container->get(Logger::class),
            $this->container
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\InvoiceService::class, fn () => new \CheapAlarms\Plugin\Services\InvoiceService(
            $this->container->get(Config::class),
            $this->container->get(\CheapAlarms\Plugin\Services\GhlClient::class),
            $this->container->get(Logger::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\UploadService::class, fn () => new \CheapAlarms\Plugin\Services\UploadService(
            $this->container->get(Config::class),
            $this->container->get(\CheapAlarms\Plugin\Services\EstimateService::class),
            $this->container->get(Logger::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\PortalService::class, fn () => new \CheapAlarms\Plugin\Services\PortalService(
            $this->container->get(\CheapAlarms\Plugin\Services\EstimateService::class),
            $this->container->get(\CheapAlarms\Plugin\Services\Logger::class),
            $this->container,
            $this->container->get(\CheapAlarms\Plugin\Config\Config::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\ServiceM8Client::class, fn () => new \CheapAlarms\Plugin\Services\ServiceM8Client(
            $this->container->get(Config::class),
            $this->container->get(Logger::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\ServiceM8Service::class, fn () => new \CheapAlarms\Plugin\Services\ServiceM8Service(
            $this->container->get(\CheapAlarms\Plugin\Services\ServiceM8Client::class),
            $this->container->get(Config::class),
            $this->container->get(Logger::class),
            $this->container->get(\CheapAlarms\Plugin\Services\EstimateService::class),
            $this->container->get(Sm8JobSnapshotRepository::class),
            $this->container->get(Sm8CompanySnapshotRepository::class)
        ));

        // ServiceM8 snapshot repositories + sync service (local read cache — SM8 has no webhooks)
        $this->container->set(Sm8JobSnapshotRepository::class, fn () => new Sm8JobSnapshotRepository());
        $this->container->set(Sm8CompanySnapshotRepository::class, fn () => new Sm8CompanySnapshotRepository());
        $this->container->set(Sm8SnapshotSyncService::class, fn () => new Sm8SnapshotSyncService(
            $this->container->get(\CheapAlarms\Plugin\Services\ServiceM8Client::class),
            $this->container->get(Sm8JobSnapshotRepository::class),
            $this->container->get(Sm8CompanySnapshotRepository::class),
            $this->container->get(Logger::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\CustomerService::class, fn () => new \CheapAlarms\Plugin\Services\CustomerService(
            $this->container->get(\CheapAlarms\Plugin\Services\GhlClient::class),
            $this->container->get(Logger::class),
            $this->container
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\GhlSignalService::class, fn () => new \CheapAlarms\Plugin\Services\GhlSignalService(
            $this->container->get(\CheapAlarms\Plugin\Services\GhlClient::class),
            $this->container->get(Logger::class),
            $this->container->get(Config::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\GhlSignalDispatcher::class, fn () => new \CheapAlarms\Plugin\Services\GhlSignalDispatcher(
            $this->container->get(\CheapAlarms\Plugin\Services\GhlSignalService::class),
            $this->container->get(Logger::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\JobLinkService::class, fn () => new \CheapAlarms\Plugin\Services\JobLinkService(
            $this->container->get(Logger::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\Shared\LocationResolver::class, fn () => new \CheapAlarms\Plugin\Services\Shared\LocationResolver(
            $this->container->get(Config::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\Shared\PortalMetaRepository::class, fn () => new \CheapAlarms\Plugin\Services\Shared\PortalMetaRepository());
        
        // Estimate sub-services
        $this->container->set(\CheapAlarms\Plugin\Services\Estimate\EstimateNormalizer::class, fn () => new \CheapAlarms\Plugin\Services\Estimate\EstimateNormalizer(
            $this->container->get(Config::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\Estimate\EstimatePhotoService::class, fn () => new \CheapAlarms\Plugin\Services\Estimate\EstimatePhotoService(
            $this->container->get(Config::class),
            $this->container->get(\CheapAlarms\Plugin\Services\EstimateService::class),
            $this->container->get(Logger::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\Estimate\EstimateInvoiceService::class, fn () => new \CheapAlarms\Plugin\Services\Estimate\EstimateInvoiceService(
            $this->container->get(Config::class),
            $this->container->get(\CheapAlarms\Plugin\Services\GhlClient::class),
            $this->container->get(Logger::class),
            $this->container->get(\CheapAlarms\Plugin\Services\Estimate\EstimateNormalizer::class)
        ));

        // Admin performance: snapshot storage for GHL estimates (avoids repeated GHL list calls).
        $this->container->set(EstimateSnapshotRepository::class, fn () => new EstimateSnapshotRepository());
        $this->container->set(EstimateSnapshotSyncService::class, fn () => new EstimateSnapshotSyncService(
            $this->container->get(\CheapAlarms\Plugin\Services\EstimateService::class),
            $this->container->get(EstimateSnapshotRepository::class),
            $this->container->get(Logger::class),
            $this->container->get(Config::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\Estimate\RetentionCleanupService::class, fn () => new \CheapAlarms\Plugin\Services\Estimate\RetentionCleanupService(
            $this->container->get(EstimateSnapshotRepository::class),
            $this->container->get(Logger::class)
        ));

        // Invoice snapshot storage (local read cache for GHL invoices).
        $this->container->set(InvoiceSnapshotRepository::class, fn () => new InvoiceSnapshotRepository());
        $this->container->set(InvoiceSnapshotSyncService::class, fn () => new InvoiceSnapshotSyncService(
            $this->container->get(\CheapAlarms\Plugin\Services\InvoiceService::class),
            $this->container->get(InvoiceSnapshotRepository::class),
            $this->container->get(Logger::class),
            $this->container->get(Config::class)
        ));

        // Indexed invoice→estimate reverse lookup (replaces wp_options table-scan).
        $this->container->set(InvoiceEstimateLinkRepository::class, fn () => new InvoiceEstimateLinkRepository());

        // Contact snapshot storage (local read cache for GHL contacts).
        $this->container->set(ContactSnapshotRepository::class, fn () => new ContactSnapshotRepository());
        $this->container->set(ContactSnapshotSyncService::class, fn () => new ContactSnapshotSyncService(
            $this->container->get(\CheapAlarms\Plugin\Services\GhlClient::class),
            $this->container->get(ContactSnapshotRepository::class),
            $this->container->get(Logger::class),
            $this->container->get(Config::class)
        ));

        $this->container->set(ProductSnapshotRepository::class, fn () => new ProductSnapshotRepository());
        $this->container->set(ProductSnapshotSyncService::class, fn () => new ProductSnapshotSyncService(
            $this->container->get(\CheapAlarms\Plugin\Services\GhlClient::class),
            $this->container->get(ProductSnapshotRepository::class),
            $this->container->get(Logger::class),
            $this->container->get(Config::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Calculators\Resolvers\AjaxResolver::class, fn () => new \CheapAlarms\Plugin\Calculators\Resolvers\AjaxResolver(
            $this->container->get(ProductSnapshotRepository::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Calculators\CalculatorResolverRegistry::class, fn () => new \CheapAlarms\Plugin\Calculators\CalculatorResolverRegistry(
            $this->container->get(\CheapAlarms\Plugin\Calculators\Resolvers\AjaxResolver::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Calculators\ResolveTokenStore::class, fn () => new \CheapAlarms\Plugin\Calculators\ResolveTokenStore());
        $this->container->set(\CheapAlarms\Plugin\Calculators\AjaxProductSeedService::class, fn () => new \CheapAlarms\Plugin\Calculators\AjaxProductSeedService(
            $this->container->get(ProductSnapshotRepository::class)
        ));

        $this->container->set(\CheapAlarms\Plugin\Services\XeroService::class, fn () => new \CheapAlarms\Plugin\Services\XeroService(
            $this->container->get(Config::class),
            $this->container->get(Logger::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\Finance\DirectXeroInvoiceFromEstimateService::class, fn () => new \CheapAlarms\Plugin\Services\Finance\DirectXeroInvoiceFromEstimateService(
            $this->container->get(\CheapAlarms\Plugin\Services\EstimateService::class),
            $this->container->get(\CheapAlarms\Plugin\Services\Estimate\EstimateNormalizer::class),
            $this->container->get(\CheapAlarms\Plugin\Services\XeroService::class),
            $this->container->get(Logger::class),
            $this->container->get(Config::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\StripeService::class, fn () => new \CheapAlarms\Plugin\Services\StripeService(
            $this->container->get(Config::class),
            $this->container->get(Logger::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\EmailTemplateService::class, fn () => new \CheapAlarms\Plugin\Services\EmailTemplateService(
            $this->container->get(Config::class)
        ));
        
        // Webhook services
        $this->container->set(\CheapAlarms\Plugin\Services\WebhookEventRepository::class, 
            fn () => new \CheapAlarms\Plugin\Services\WebhookEventRepository()
        );
        
        $this->container->set(\CheapAlarms\Plugin\Services\StripeWebhookProcessor::class, 
            fn (Container $c) => new \CheapAlarms\Plugin\Services\StripeWebhookProcessor(
                $c->get(\CheapAlarms\Plugin\Services\WebhookEventRepository::class),
                $c->get(\CheapAlarms\Plugin\Services\Shared\PortalMetaRepository::class),
                $c->get(Logger::class),
                $c->get(\CheapAlarms\Plugin\Services\XeroService::class),
                $c->get(Config::class),
                $c->get(\CheapAlarms\Plugin\Services\EstimateService::class),
                $c->get(\CheapAlarms\Plugin\Services\GhlSignalDispatcher::class)
            )
        );
        
        $this->container->set(\CheapAlarms\Plugin\Services\WebhookRetryService::class, 
            fn (Container $c) => new \CheapAlarms\Plugin\Services\WebhookRetryService(
                $c->get(\CheapAlarms\Plugin\Services\WebhookEventRepository::class),
                $c->get(\CheapAlarms\Plugin\Services\StripeWebhookProcessor::class),
                $c->get(Logger::class)
            )
        );

        // GHL Webhook services
        $this->container->set(\CheapAlarms\Plugin\Services\Ghl\GhlWebhookEventRepository::class,
            fn () => new \CheapAlarms\Plugin\Services\Ghl\GhlWebhookEventRepository()
        );

        $this->container->set(\CheapAlarms\Plugin\Services\Ghl\GhlWebhookProcessor::class,
            fn (Container $c) => new \CheapAlarms\Plugin\Services\Ghl\GhlWebhookProcessor(
                $c->get(\CheapAlarms\Plugin\Services\Ghl\GhlWebhookEventRepository::class),
                $c->get(\CheapAlarms\Plugin\Services\Contact\ContactSnapshotRepository::class),
                $c->get(\CheapAlarms\Plugin\Services\Invoice\InvoiceSnapshotRepository::class),
                $c->get(\CheapAlarms\Plugin\Services\Estimate\EstimateSnapshotRepository::class),
                $c->get(Logger::class)
            )
        );
    }

    private function registerRestEndpoints(): void
    {
        add_action('rest_api_init', function () {
            if (function_exists('error_log')) {
                error_log('[CA Plugin] rest_api_init register controllers');
            }
            
            // Ensure request ID is initialized before API calls
            if ($this->container->has(Logger::class)) {
                $logger = $this->container->get(Logger::class);
                $middleware = new RequestIdMiddleware($logger);
                $middleware->init($this->container);
            }
            
            $kernel = new ApiKernel($this->container);
            $kernel->register();
        });
    }

    /**
     * Minimal API when secrets.php is missing — public calculator catalog/resolve only.
     */
    private function bootstrapPublicCalculatorApi(Config $config, string $missingStr): void
    {
        $this->container->set(Config::class, fn () => $config);
        $this->container->set(Logger::class, fn () => new Logger(null));
        $this->container->set(AuthorizationService::class, fn () => new AuthorizationService());
        $this->container->set(Authenticator::class, fn () => new Authenticator(
            $this->container->get(Config::class),
            $this->container->get(AuthorizationService::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Services\GhlClient::class, fn () => new \CheapAlarms\Plugin\Services\GhlClient(
            $this->container->get(Config::class),
            $this->container->get(Logger::class)
        ));
        $this->container->set(ProductSnapshotRepository::class, fn () => new ProductSnapshotRepository());
        $this->container->set(\CheapAlarms\Plugin\Calculators\Resolvers\AjaxResolver::class, fn () => new \CheapAlarms\Plugin\Calculators\Resolvers\AjaxResolver(
            $this->container->get(ProductSnapshotRepository::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Calculators\CalculatorResolverRegistry::class, fn () => new \CheapAlarms\Plugin\Calculators\CalculatorResolverRegistry(
            $this->container->get(\CheapAlarms\Plugin\Calculators\Resolvers\AjaxResolver::class)
        ));
        $this->container->set(\CheapAlarms\Plugin\Calculators\ResolveTokenStore::class, fn () => new \CheapAlarms\Plugin\Calculators\ResolveTokenStore());
        $this->container->set(\CheapAlarms\Plugin\Calculators\AjaxProductSeedService::class, fn () => new \CheapAlarms\Plugin\Calculators\AjaxProductSeedService(
            $this->container->get(ProductSnapshotRepository::class)
        ));

        add_action('rest_api_init', function () {
            (new HealthController($this->container))->register();
            (new SetupBootstrapController($this->container))->register();
            (new CalculatorController($this->container))->register();
        });

        if (is_admin()) {
            add_action('admin_notices', function () use ($missingStr) {
                echo '<div class="notice notice-warning"><p><strong>CheapAlarms:</strong> Calculator API is active, but portal features need <code>config/secrets.php</code>. Missing: '
                    . esc_html($missingStr)
                    . '</p></div>';
            });
        }
    }

    private function registerCors(): void
    {
        add_action('rest_api_init', function () {
            add_filter('rest_pre_serve_request', [$this, 'sendCorsHeaders'], 0, 4);
        });

        add_action('init', function () {
            if (isset($_SERVER['REQUEST_METHOD']) && strtoupper((string) $_SERVER['REQUEST_METHOD']) === 'OPTIONS') {
                $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
                if ($origin && $this->isOriginAllowed($origin)) {
                    $this->applyCorsHeaders($origin);
                    status_header(204);
                    exit;
                }
            }
        });
    }

    /**
     * @param mixed $served
     * @param mixed $result
     * @param mixed $request
     * @param mixed $server
     */
    public function sendCorsHeaders($served, $result, $request, $server)
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ($origin && $this->isOriginAllowed($origin)) {
            $this->applyCorsHeaders($origin);
        }

        return $served;
    }

    private function applyCorsHeaders(string $origin): void
    {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Nonce');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Vary: Origin');
    }

    private function isOriginAllowed(string $origin): bool
    {
        $origins = $this->container->get(Config::class)->getApiAllowedOrigins();
        return in_array($origin, $origins, true);
    }

    private function registerAdmin(): void
    {
        if (is_admin()) {
            new UserCapabilities($this->container);
        }
    }

    private function registerFrontend(): void
    {
        new PortalPage();
    }

    private function registerCliCommands(): void
    {
        require_once CA_PLUGIN_PATH . 'includes/Commands/RepairPaymentsCommand.php';
        require_once CA_PLUGIN_PATH . 'includes/Commands/RebuildInvoiceLinksCommand.php';
        \WP_CLI::add_command('cheapalarms repair-payments', \CheapAlarms\Plugin\Commands\RepairPaymentsCommand::class);
        \WP_CLI::add_command('cheapalarms rebuild-invoice-links', new \CheapAlarms\Plugin\Commands\RebuildInvoiceLinksCommand());
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function activate(): void
    {
        try {
            // Check PHP version first
            if (version_compare(PHP_VERSION, '7.4.0', '<')) {
                throw new \RuntimeException(
                    sprintf(
                        'Portal plugin requires PHP 7.4 or higher. You are running PHP %s.',
                        PHP_VERSION
                    )
                );
            }

            Schema::maybeMigrate();
            $this->registerRoles();
            PortalPage::activate();
            flush_rewrite_rules();
        } catch (\Throwable $e) {
            // Log error for debugging, then re-throw to show error to user
            if (function_exists('error_log')) {
                error_log('[CA] Activation error: ' . $e->getMessage());
                error_log('[CA] Stack trace: ' . $e->getTraceAsString());
            }
            // Re-throw to show error to user
            throw $e;
        }
    }

    public function deactivate(): void
    {
        // Unschedule all WP-Cron jobs
        $timestamp = wp_next_scheduled('ca_cleanup_expired_deletions_daily');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ca_cleanup_expired_deletions_daily');
        }
        
        $timestamp = wp_next_scheduled('ca_cleanup_expired_deletions');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ca_cleanup_expired_deletions');
        }
        
        // Clear all scheduled hooks (single + recurring)
        wp_clear_scheduled_hook('ca_cleanup_expired_deletions_daily');
        wp_clear_scheduled_hook('ca_cleanup_expired_deletions');
        wp_clear_scheduled_hook('ca_sync_estimate_snapshots');
        wp_clear_scheduled_hook('ca_sync_invoice_snapshots');
        wp_clear_scheduled_hook('ca_sync_contact_snapshots');
        wp_clear_scheduled_hook('ca_sync_product_snapshots');
        wp_clear_scheduled_hook('ca_sync_product_price_batch');
        wp_clear_scheduled_hook('ca_sync_product_snapshots_daily');
        wp_clear_scheduled_hook('ca_retry_failed_webhooks');
        wp_clear_scheduled_hook('ca_retry_failed_webhooks_recurring');
        wp_clear_scheduled_hook('ca_retry_failed_ghl_webhooks');
        wp_clear_scheduled_hook('ca_retry_failed_ghl_webhooks_recurring');
        wp_clear_scheduled_hook('ca_cleanup_expired_payment_intents');
        wp_clear_scheduled_hook('ca_sync_sm8_jobs');
        wp_clear_scheduled_hook('ca_sync_sm8_companies');
        wp_clear_scheduled_hook('ca_sync_sm8_all');
        wp_clear_scheduled_hook('ca_sync_sm8_jobs_recurring');
        wp_clear_scheduled_hook('ca_sync_sm8_companies_recurring');
        wp_clear_scheduled_hook('ca_sync_sm8_all_daily');
        
        flush_rewrite_rules();
    }

    private function registerRoles(): void
    {
        foreach (self::ROLE_DEFINITIONS as $roleKey => $definition) {
            $roleObject = get_role($roleKey);
            if (!$roleObject) {
                add_role($roleKey, $definition['label'], $definition['capabilities']);
                continue;
            }

            foreach ($definition['capabilities'] as $cap => $grant) {
                if ($grant) {
                    $roleObject->add_cap($cap);
                } else {
                    $roleObject->remove_cap($cap);
                }
            }
        }

        $adminRole = get_role('administrator');
        if ($adminRole) {
            foreach (self::ADMIN_GRANTED_CAPS as $cap) {
                $adminRole->add_cap($cap);
            }
        }
    }
}

