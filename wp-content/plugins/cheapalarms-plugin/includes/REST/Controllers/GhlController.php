<?php

namespace CheapAlarms\Plugin\REST\Controllers;

use CheapAlarms\Plugin\Config\CacheConfig;
use CheapAlarms\Plugin\REST\Auth\Authenticator;
use CheapAlarms\Plugin\REST\Controllers\Base\AdminController;
use CheapAlarms\Plugin\Services\Contact\ContactSnapshotRepository;
use CheapAlarms\Plugin\Services\Container;
use CheapAlarms\Plugin\Services\GhlClient;
use CheapAlarms\Plugin\Services\Logger;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

use function is_wp_error;
use function sanitize_email;
use function sanitize_text_field;
use function wp_json_encode;

class GhlController extends AdminController
{
    private GhlClient $client;
    private Authenticator $auth;
    private ContactSnapshotRepository $contactSnapshotRepo;
    private Logger $logger;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->client              = $this->container->get(GhlClient::class);
        $this->auth                = $this->container->get(Authenticator::class);
        $this->contactSnapshotRepo = $this->container->get(ContactSnapshotRepository::class);
        $this->logger              = $this->container->get(Logger::class);
    }

    public function register(): void
    {
        // Contacts endpoint
        register_rest_route('ca/v1', '/ghl/contacts', [
            [
                'methods'             => 'GET',
                'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_view_estimates'),
                'callback'            => function () {
                    $result = [
                        'ok' => true,
                        'hasKey' => !empty($this->container->get(\CheapAlarms\Plugin\Config\Config::class)->getGhlToken()),
                        'hasLocationId' => !empty($this->container->get(\CheapAlarms\Plugin\Config\Config::class)->getLocationId()),
                    ];
                    return $this->respond($result);
                },
            ],
            [
                'methods'             => 'POST',
                'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_manage_portal'),
                'callback'            => [$this, 'createContact'],
            ],
        ]);

        // List contacts endpoint — LOCAL-FIRST with stale-while-revalidate
        register_rest_route('ca/v1', '/ghl/contacts/list', [
            'methods'             => 'GET',
            'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_view_estimates'),
            'callback'            => [$this, 'listContacts'],
        ]);

        // Messages endpoint
        register_rest_route('ca/v1', '/ghl/messages', [
            'methods'             => 'POST',
            'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_manage_portal'),
            'callback'            => function (WP_REST_Request $request) {
                $config = $this->container->get(\CheapAlarms\Plugin\Config\Config::class);
                
                if (empty($config->getGhlToken())) {
                    return $this->respond(new WP_Error('configuration_error', __('GHL API key not configured.', 'cheapalarms'), ['status' => 500]));
                }

                $body = $request->get_json_params();
                if (!is_array($body)) {
                    $body = json_decode($request->get_body(), true);
                }
                if (!is_array($body)) {
                    $body = [];
                }

                $contactId = !empty($body['contactId']) ? sanitize_text_field($body['contactId']) : '';
                $subject = !empty($body['subject']) ? sanitize_text_field($body['subject']) : '';
                $html = !empty($body['html']) ? $body['html'] : '';
                $text = !empty($body['text']) ? sanitize_textarea_field($body['text']) : '';
                $fromEmail = !empty($body['fromEmail']) ? sanitize_email($body['fromEmail']) : '';

                if (empty($contactId)) {
                    return $this->respond(new WP_Error('bad_request', __('Contact ID is required.', 'cheapalarms'), ['status' => 400]));
                }

                if (empty($subject)) {
                    return $this->respond(new WP_Error('bad_request', __('Subject is required.', 'cheapalarms'), ['status' => 400]));
                }

                if (empty($html) && empty($text)) {
                    return $this->respond(new WP_Error('bad_request', __('HTML or text content is required.', 'cheapalarms'), ['status' => 400]));
                }

                $effectiveFromEmail = $fromEmail ?: get_option('ghl_from_email', 'quotes@cheapalarms.dev');
                
                // Format email with display name: "CheapAlarms <email@domain.com>"
                // This ensures email clients show "CheapAlarms" instead of just "quotes" or the email address
                $effectiveFromEmailWithName = 'CheapAlarms <' . $effectiveFromEmail . '>';

                $payload = [
                    'contactId' => $contactId,
                    'type' => 'Email',
                    'status' => 'pending',
                    'subject' => $subject,
                    'html' => !empty($html) ? $html : null,
                    'message' => !empty($text) ? $text : null,
                    'emailFrom' => $effectiveFromEmailWithName, // Format: "CheapAlarms <quotes@cheapalarms.com.au>"
                ];

                if ($config->getLocationId()) {
                    $payload['locationId'] = $config->getLocationId();
                }

                $result = $this->client->post('/conversations/messages', $payload);
                
                if (is_wp_error($result)) {
                    return $this->respond($result);
                }

                return $this->respond(['message' => $result]);
            },
        ]);
    }

    /**
     * POST /ca/v1/ghl/contacts
     * Creates a GHL contact with write-through to local snapshot.
     */
    public function createContact(WP_REST_Request $request): WP_REST_Response
    {
        $config = $this->container->get(\CheapAlarms\Plugin\Config\Config::class);

        if (empty($config->getGhlToken())) {
            return $this->respond(new WP_Error('configuration_error', __('GHL API key not configured.', 'cheapalarms'), ['status' => 500]));
        }

        if (empty($config->getLocationId())) {
            return $this->respond(new WP_Error('configuration_error', __('GHL location ID not configured.', 'cheapalarms'), ['status' => 500]));
        }

        $body = $request->get_json_params();
        if (!is_array($body)) {
            $body = json_decode($request->get_body(), true);
        }
        if (!is_array($body)) {
            $body = [];
        }

        $email     = !empty($body['email']) ? sanitize_email($body['email']) : '';
        $phone     = !empty($body['phone']) ? sanitize_text_field($body['phone']) : '';
        $firstName = !empty($body['firstName']) ? sanitize_text_field($body['firstName']) : '';
        $lastName  = !empty($body['lastName']) ? sanitize_text_field($body['lastName']) : '';

        if (empty($email) && empty($phone)) {
            return $this->respond(new WP_Error('bad_request', __('Email or phone is required.', 'cheapalarms'), ['status' => 400]));
        }

        $locationId = $config->getLocationId();
        $payload = [
            'email'      => $email,
            'phone'      => $phone,
            'firstName'  => $firstName,
            'lastName'   => $lastName,
            'locationId' => $locationId,
        ];

        $result = $this->client->post('/contacts/', $payload);

        if (is_wp_error($result)) {
            // Check if this is a duplicate contact error (400 with contactId in meta)
            $errorData = $result->get_error_data();
            $errorCode = $result->get_error_code();

            if ($errorCode === 'ghl_http_error' && isset($errorData['code']) && $errorData['code'] === 400) {
                $errorBody = $errorData['body'] ?? null;

                // Parse error body if it's a string
                if (is_string($errorBody)) {
                    $decoded = json_decode($errorBody, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $errorBody = $decoded;
                    } else {
                        $errorBody = null;
                    }
                }

                // GHL error structure: { "statusCode": 400, "message": "...", "meta": { "contactId": "..." } }
                if (is_array($errorBody)) {
                    $errorMessage = $errorBody['message'] ?? '';
                    $statusCode = $errorBody['statusCode'] ?? $errorData['code'] ?? null;
                    $hasDuplicateMessage = stripos($errorMessage, 'duplicate') !== false || stripos($errorMessage, 'duplicated') !== false;
                    $hasContactId = isset($errorBody['meta']['contactId']) && !empty($errorBody['meta']['contactId']);

                    if ($statusCode === 400 && ($hasDuplicateMessage || $hasContactId)) {
                        $existingContactId = $errorBody['meta']['contactId'] ?? '';

                        if (!empty($existingContactId)) {
                            $contactData = [
                                'id'        => $existingContactId,
                                'email'     => $email,
                                'firstName' => $firstName,
                                'lastName'  => $lastName,
                            ];

                            // Write-through: cache the duplicate contact locally
                            $this->writeThroughContact($locationId, $contactData);

                            return $this->respond(['contact' => $contactData]);
                        }
                    }
                }
            }

            return $this->respond($result);
        }

        // Write-through: cache the newly created contact locally
        $contactData = is_array($result) ? ($result['contact'] ?? $result) : $result;
        if (is_array($contactData)) {
            $this->writeThroughContact($locationId, $contactData);
        }

        return $this->respond(['contact' => $result]);
    }

    /**
     * GET /ca/v1/ghl/contacts/list
     * Local-first contact list with stale-while-revalidate fallback to GHL API.
     */
    public function listContacts(WP_REST_Request $request): WP_REST_Response
    {
        $config = $this->container->get(\CheapAlarms\Plugin\Config\Config::class);

        if (empty($config->getGhlToken())) {
            return $this->respond(new WP_Error('configuration_error', __('GHL API key not configured.', 'cheapalarms'), ['status' => 500]));
        }

        if (empty($config->getLocationId())) {
            return $this->respond(new WP_Error('configuration_error', __('GHL location ID not configured.', 'cheapalarms'), ['status' => 500]));
        }

        $locationId = $config->getLocationId();
        $limit  = min((int)($request->get_param('limit') ?: 100), 500);
        $offset = max((int)($request->get_param('offset') ?: 0), 0);
        $search = $request->get_param('search') ? sanitize_text_field($request->get_param('search')) : null;

        // ── LOCAL-FIRST: Try snapshot table ──────────────────────────
        $hasLocal = $this->contactSnapshotRepo->hasData($locationId);

        if (is_wp_error($hasLocal)) {
            // DB error — fall through to GHL API
            $this->logger->warning('Contact snapshot hasData check failed, falling back to GHL', [
                'error' => $hasLocal->get_error_message(),
            ]);
            $hasLocal = false;
        }

        if ($hasLocal) {
            $localResult = $this->contactSnapshotRepo->listByLocation($locationId, $search, $limit, $offset);

            if (!is_wp_error($localResult)) {
                // Check freshness (contacts: 10 min stale tier)
                $lastSynced = $this->contactSnapshotRepo->lastSyncedAt($locationId);
                $isStale = is_wp_error($lastSynced) || !CacheConfig::isFresh($lastSynced, CacheConfig::CONTACT_LIST_STALE_SECONDS);

                // Schedule background re-sync if stale
                if ($isStale && !wp_next_scheduled('ca_sync_contact_snapshots', [$locationId])) {
                    wp_schedule_single_event(time(), 'ca_sync_contact_snapshots', [$locationId]);
                }

                $this->logger->debug('[CONTACT_CACHE] ' . ($isStale ? 'STALE' : 'HIT'), [
                    'locationId' => $locationId,
                    'count'      => $localResult['total'],
                ]);

                $response = $this->respond([
                    'contacts' => $localResult['items'],
                    'total'    => $localResult['total'],
                    'limit'    => $limit,
                    'offset'   => $offset,
                ]);
                $response->header('X-Data-Source', $isStale ? 'local-stale' : 'local');
                return $response;
            }

            // Local read failed — log and fall through to GHL
            $this->logger->warning('Contact snapshot listByLocation failed, falling back to GHL', [
                'error' => $localResult->get_error_message(),
            ]);
        }

        // ── FALLBACK: Fetch from GHL API ─────────────────────────────
        $result = $this->client->get('/contacts/', [
            'locationId' => $locationId,
            'limit'      => min($limit, 100), // GHL caps at 100
        ], 25, $locationId);

        if (is_wp_error($result)) {
            $this->logger->error('GHL contacts list error', [
                'error' => $result->get_error_message(),
                'code'  => $result->get_error_code(),
                'data'  => $result->get_error_data(),
            ]);
            return $this->respond($result);
        }

        // Parse GHL response (handle varying structures)
        $contacts = [];
        $total = 0;

        if (isset($result['contacts']) && is_array($result['contacts'])) {
            $contacts = $result['contacts'];
            $total = $result['meta']['total'] ?? count($contacts);
        } elseif (isset($result['contact']) && is_array($result['contact'])) {
            $contacts = [$result['contact']];
            $total = 1;
        } elseif (is_array($result) && isset($result[0]) && isset($result[0]['id'])) {
            $contacts = $result;
            $total = count($contacts);
        } else {
            $this->logger->warning('Unexpected GHL contacts response structure', [
                'response_keys' => is_array($result) ? array_keys($result) : [],
            ]);
        }

        // Write-through: populate local cache with API results
        if (!empty($contacts)) {
            $normalized = [];
            foreach ($contacts as $c) {
                $contactId = $c['id'] ?? $c['_id'] ?? $c['contactId'] ?? null;
                if (!$contactId) {
                    continue;
                }
                $normalized[] = [
                    'id'           => (string)$contactId,
                    'email'        => $c['email'] ?? '',
                    'firstName'    => $c['firstName'] ?? $c['first_name'] ?? '',
                    'lastName'     => $c['lastName'] ?? $c['last_name'] ?? '',
                    'phone'        => $c['phone'] ?? '',
                    'companyName'  => $c['companyName'] ?? $c['company'] ?? '',
                    'addressLine1' => $c['address1'] ?? $c['addressLine1'] ?? '',
                    'city'         => $c['city'] ?? '',
                    'state'        => $c['state'] ?? '',
                    'postalCode'   => $c['postalCode'] ?? $c['postal_code'] ?? '',
                    'tags'         => is_array($c['tags'] ?? null) ? wp_json_encode($c['tags']) : ($c['tags'] ?? ''),
                    'dateAdded'    => $c['dateAdded'] ?? $c['createdAt'] ?? null,
                    'createdAt'    => $c['createdAt'] ?? $c['dateAdded'] ?? null,
                    'updatedAt'    => $c['updatedAt'] ?? $c['dateUpdated'] ?? null,
                    'rawJson'      => wp_json_encode($c),
                ];
            }

            $upsertResult = $this->contactSnapshotRepo->upsertMany($locationId, $normalized);
            if (is_wp_error($upsertResult)) {
                $this->logger->warning('Failed to populate contact snapshots from API fallback', [
                    'error' => $upsertResult->get_error_message(),
                ]);
            }
        }

        // Schedule full background sync to get all contacts (API only returns first page)
        if (!wp_next_scheduled('ca_sync_contact_snapshots', [$locationId])) {
            wp_schedule_single_event(time(), 'ca_sync_contact_snapshots', [$locationId]);
        }

        $this->logger->debug('[CONTACT_CACHE] MISS (API)', [
            'locationId' => $locationId,
            'count'      => $total,
        ]);

        $response = $this->respond([
            'contacts' => $contacts,
            'total'    => $total,
            'limit'    => $limit,
        ]);
        $response->header('X-Data-Source', 'api');
        return $response;
    }

    /**
     * Write-through: upsert a single contact to the local snapshot table.
     * Fails silently (logs but does not fail the main operation).
     */
    private function writeThroughContact(string $locationId, array $contactData): void
    {
        $contactId = $contactData['id'] ?? $contactData['_id'] ?? $contactData['contactId'] ?? null;
        if (!$contactId) {
            return;
        }

        try {
            $record = ContactSnapshotRepository::normalizeFromGhl($contactData);
            $res    = $this->contactSnapshotRepo->upsertOne($locationId, $record);
            if (is_wp_error($res)) {
                $this->logger->warning('Contact write-through failed', [
                    'contactId' => $contactId,
                    'error'     => $res->get_error_message(),
                ]);
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Contact write-through exception', [
                'contactId' => $contactId,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    private function isDevBypass(): bool
    {
        $header  = isset($_SERVER['HTTP_X_CA_DEV']) ? trim((string) $_SERVER['HTTP_X_CA_DEV']) : '';
        $query   = isset($_GET['__dev']) ? trim((string) $_GET['__dev']) : '';
        $addr    = $_SERVER['REMOTE_ADDR'] ?? '';
        $isLocal = in_array($addr, ['127.0.0.1', '::1'], true);
        $isDebug = defined('WP_DEBUG') && WP_DEBUG;
        if ($isLocal && $isDebug && ($header === '1' || $query === '1')) {
            return true;
        }
        if ($isLocal && $isDebug && defined('CA_DEV_BYPASS') && CA_DEV_BYPASS) {
            return true;
        }
        return false;
    }
}

