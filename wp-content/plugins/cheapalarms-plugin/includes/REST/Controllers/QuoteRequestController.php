<?php

namespace CheapAlarms\Plugin\REST\Controllers;

use CheapAlarms\Plugin\Config\CacheConfig;
use CheapAlarms\Plugin\Services\Contact\ContactSnapshotRepository;
use CheapAlarms\Plugin\Services\Container;
use CheapAlarms\Plugin\Services\GhlClient;
use CheapAlarms\Plugin\Services\EstimateService;
use CheapAlarms\Plugin\Services\PortalService;
use CheapAlarms\Plugin\Config\Config;
use CheapAlarms\Plugin\REST\Auth\Authenticator;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

use function register_rest_route;
use function sanitize_text_field;
use function sanitize_email;
use function is_wp_error;
use function email_exists;
use function wp_create_user;
use function wp_generate_password;
use function get_user_by;
use function wp_update_user;
use function current_time;
use function update_option;
use function wp_json_encode;
use function get_user_meta;
use function update_user_meta;
use function home_url;
use function get_option;
use function trailingslashit;
use function gmdate;
use function strtotime;
use function mb_substr;
use function trim;
use function str_starts_with;
use function ltrim;
use function add_query_arg;
use function get_password_reset_key;
use function rawurlencode;
use function esc_url;
use function esc_html;
use function __;
use function get_transient;
use function set_transient;
use function delete_transient;

/**
 * Public Quote Request Controller
 * Handles public quote requests from the calculator
 */
class QuoteRequestController implements ControllerInterface
{
    private GhlClient $ghlClient;
    private EstimateService $estimateService;
    private PortalService $portalService;
    private Config $config;
    private Container $container;
    private Authenticator $authenticator;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->ghlClient = $container->get(GhlClient::class);
        $this->estimateService = $container->get(EstimateService::class);
        $this->portalService = $container->get(PortalService::class);
        $this->config = $container->get(Config::class);
        $this->authenticator = $container->get(Authenticator::class);
    }

    public function register(): void
    {
        register_rest_route('ca/v1', '/quote-request', [
            'methods'             => 'POST',
            'permission_callback' => '__return_true', // Public endpoint
            'callback'            => [$this, 'handleQuoteRequest'],
        ]);
    }

    /**
     * Handle public quote request from calculator
     */
    public function handleQuoteRequest(WP_REST_Request $request): WP_REST_Response
    {
        // Rate limit public quote requests to prevent abuse
        $rateCheck = $this->authenticator->enforceRateLimit('quote_request_public');
        if (is_wp_error($rateCheck)) {
            return $this->respond($rateCheck);
        }

        $body = $request->get_json_params();

        // Validate required fields
        $firstName = sanitize_text_field($body['firstName'] ?? '');
        $lastName = sanitize_text_field($body['lastName'] ?? '');
        $email = sanitize_email($body['email'] ?? '');
        $phone = sanitize_text_field($body['phone'] ?? '');

        if (empty($firstName) || empty($lastName) || empty($email)) {
            return $this->respond(new WP_Error('missing_params', 'Missing required fields: firstName, lastName, email', ['status' => 400]));
        }

        // Define lock key BEFORE using it (needed for duplicate prevention)
        $lockKey = 'ca_quote_request_lock_' . md5($email);

        // DUPLICATE PREVENTION: Check if same email recently created an estimate
        // Use email-based lock (60 seconds) to prevent duplicate quote requests
        $lockValue = get_transient($lockKey);
        
        if ($lockValue !== false) {
            // Check if lock is stale (older than 60 seconds - previous request completed/failed)
            $lockAge = time() - (int)$lockValue;
            if ($lockAge > 60) {
                // Lock is stale - clear it and proceed
                delete_transient($lockKey);
            } else {
                // Lock is active - check if estimate was actually created
                $userId = email_exists($email);
                if ($userId) {
                    $recentEstimateIds = get_user_meta($userId, 'ca_estimate_ids', true);
                    if (is_array($recentEstimateIds) && !empty($recentEstimateIds)) {
                        // Check if most recent estimate was created in last 60 seconds
                        $mostRecentEstimateId = end($recentEstimateIds);
                        $metaKey = "ca_portal_meta_{$mostRecentEstimateId}";
                        $recentMeta = get_option($metaKey, '{}');
                        $meta = json_decode($recentMeta, true);
                        
                        if (is_array($meta)) {
                            $createdAt = $meta['quote']['createdAt'] ?? $meta['workflow']['createdAt'] ?? null;
                            if ($createdAt) {
                                $createdTimestamp = strtotime($createdAt);
                                $timeSinceCreation = time() - $createdTimestamp;
                                
                                if ($timeSinceCreation < 60) {
                                    // Recent estimate found - duplicate request
                                    return $this->respond(new WP_Error('duplicate_request', 'A quote request was recently submitted for this email. Please check your inbox. If you need another quote, please wait a moment and try again.', ['status' => 429, 'retryAfter' => 60 - $timeSinceCreation]));
                                }
                            }
                        }
                    }
                }
                
                // Lock is active but no recent estimate found - might be processing
                return $this->respond(new WP_Error('duplicate_request', 'A quote request is currently being processed for this email. Please wait a moment and check your email.', ['status' => 429, 'retryAfter' => 60 - $lockAge]));
            }
        }
        
        // Set lock with timestamp (60 second expiry)
        set_transient($lockKey, time(), 60);

        // Validate items
        $items = $body['items'] ?? [];
        if (empty($items) || !is_array($items)) {
            return $this->respond(new WP_Error('invalid_items', 'Missing or invalid items array', ['status' => 400]));
        }
        
        // Sanitize items to match GHL expected structure
        // Also retain photo-policy hints (isPackage / isHeading / photoRequired) on a
        // parallel array; these are stripped before sending to GHL but preserved
        // for seeding portal itemsMeta after estimate creation.
        $sanitizedItems = [];
        $itemHints = [];
        foreach ($items as $item) {
            $itemName = (string)($item['name'] ?? '');
            $itemAmount = (float)($item['amount'] ?? 0);

            // Skip items without name or with zero/negative amount
            if (empty($itemName) || $itemAmount <= 0) {
                continue;
            }

            $sanitizedItems[] = [
                'name'        => $itemName,
                'description' => (string)($item['description'] ?? ''),
                'currency'    => (string)($item['currency'] ?? 'AUD'),
                'amount'      => $itemAmount,
                'qty'         => (int)($item['qty'] ?? $item['quantity'] ?? 1),
                'type'        => (string)($item['type'] ?? 'one_time'),  // GHL requires this
            ];

            $hint = ['name' => $itemName];
            if (array_key_exists('isPackage', $item))     { $hint['isPackage']     = (bool)$item['isPackage']; }
            if (array_key_exists('isHeading', $item))     { $hint['isHeading']     = (bool)$item['isHeading']; }
            if (array_key_exists('photoRequired', $item)) { $hint['photoRequired'] = (bool)$item['photoRequired']; }
            $itemHints[] = $hint;
        }
        
        // Ensure we have at least one valid item after sanitization
        if (empty($sanitizedItems)) {
            return $this->respond(new WP_Error('invalid_items', 'No valid items found. Items must have name and amount > 0.', ['status' => 400]));
        }

        // Optional fields
        $locationId = sanitize_text_field($body['locationId'] ?? '');
        $propertyProfile = sanitize_text_field($body['propertyProfile'] ?? '');
        $address = $body['address'] ?? null;

        try {
            // Use effective location ID
            $effectiveLocationId = $locationId ?: $this->config->getLocationId();
            $brandName = $this->config->getBrandName();
            $brandTeam = $brandName . ' Team';
            
            // CRITICAL: Check if user is truly new BEFORE creating contact
            // A user is truly new if email doesn't exist in BOTH WordPress AND GHL
            $wpUserId = email_exists($email);
            $isTrulyNewUser = !$wpUserId;
            $existingGhlContactId = null;
            $contactId = null; // Initialize to ensure it's always defined
            
            // If not in WordPress, check GHL before creating contact
            if ($isTrulyNewUser) {
                // ── LOCAL-FIRST: try snapshot table ──────────────────
                $localContactHit = false;
                try {
                    /** @var ContactSnapshotRepository $contactRepo */
                    $contactRepo = $this->container->get(ContactSnapshotRepository::class);
                    $local = $contactRepo->findByEmail($email, $effectiveLocationId);

                    if ($local !== null && !is_wp_error($local)) {
                        $syncedAt = $local['syncedAt'] ?? null;
                        if (CacheConfig::isFresh($syncedAt, CacheConfig::CONTACT_SEARCH_STALE_SECONDS) || !$this->config->isGhlFetchAllowed()) {
                            $localContactId = $local['contactId'] ?? null;
                            if (!empty($localContactId)) {
                                $isTrulyNewUser = false;
                                $existingGhlContactId = $localContactId;
                                $localContactHit = true;
                                error_log('[CA][INFO] Found existing contact from local snapshot for email: ' . $email . ' (contactId: ' . $existingGhlContactId . ')');
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Local lookup failed – fall through to GHL API
                    error_log('[CA][WARN] Contact snapshot lookup failed in QuoteRequestController: ' . $e->getMessage());
                }

                // ── FALLBACK: GHL API search ─────────────────────────
                if (!$localContactHit && $this->config->isGhlFetchAllowed()) {
                    // CRITICAL: locationId should be ONLY in header, NOT in query params
                    $ghlSearchResult = $this->ghlClient->get('/contacts/search', [
                        'query' => $email,
                    ], 8, $effectiveLocationId);
                    
                    if (!is_wp_error($ghlSearchResult)) {
                        $contacts = $ghlSearchResult['contacts'] ?? $ghlSearchResult['items'] ?? [];
                        foreach ($contacts as $contact) {
                            $contactEmail = $contact['email'] ?? '';
                            if ($contactEmail && strcasecmp($contactEmail, $email) === 0) {
                                // Contact exists in GHL - not a new user
                                $isTrulyNewUser = false;
                                $existingGhlContactId = $contact['id'] ?? null;
                                error_log('[CA][INFO] Found existing GHL contact for email: ' . $email . ' (contactId: ' . ($existingGhlContactId ?: 'null') . ')');
                                // Write-through: cache the found contact locally
                                $this->writeThroughContact($effectiveLocationId, $contact);
                                break;
                            }
                        }
                    } else {
                        // Search failed - log but continue (will try to create contact)
                        error_log('[CA][WARNING] GHL contact search failed before contact creation: ' . $ghlSearchResult->get_error_message());
                    }
                }
            } else {
                error_log('[CA][INFO] Email exists in WordPress (userId: ' . $wpUserId . ') - not a new user');
            }
            
            // Log truly new user status
            if ($isTrulyNewUser) {
                error_log('[CA][INFO] User is TRULY NEW - email not found in WordPress or GHL: ' . $email);
            }
            
            // Step 1: Create contact in GHL (or use existing if found)
            $contactPayload = [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'locationId' => $effectiveLocationId,
            ];

            if ($address && is_array($address)) {
                $contactPayload['address1'] = $address['address1'] ?? '';
                $contactPayload['city'] = $address['city'] ?? '';
                $contactPayload['state'] = $address['state'] ?? '';
                $contactPayload['postalCode'] = $address['postalCode'] ?? '';
                $contactPayload['country'] = $address['country'] ?? 'AU';
            }

            // Use existing contactId if we found one, otherwise create new contact
            if ($existingGhlContactId) {
                // Use existing contact - no need to create
                $contactId = $existingGhlContactId;
                error_log('[CA][INFO] Using existing GHL contact: ' . $contactId);
            } else {
                // Create new contact in GHL
                $contactResult = $this->ghlClient->post('/contacts/', $contactPayload, 8, $effectiveLocationId);
                
                if (is_wp_error($contactResult)) {
                // Handle duplicate contact errors safely:
                // - If duplicate is by EMAIL, reuse existing contactId (safe).
                // - If duplicate is by PHONE, DO NOT auto-merge (unsafe); return a friendly conflict.
                $errorData = $contactResult->get_error_data();
                $errorCode = $contactResult->get_error_code();

                $contactId = null;
                $matchingField = null;

                if ($errorCode === 'ghl_http_error' && isset($errorData['code']) && (int)$errorData['code'] === 400) {
                    $errorBody = $errorData['body'] ?? null;
                    if (is_string($errorBody)) {
                        $decoded = json_decode($errorBody, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $errorBody = $decoded;
                        }
                    }

                    if (is_array($errorBody)) {
                        $contactId = $errorBody['meta']['contactId'] ?? null;
                        $matchingField = $errorBody['meta']['matchingField'] ?? null;
                    }

                    // If GHL indicates phone-based duplication, don't auto-merge.
                    if (!empty($contactId) && is_string($matchingField) && strtolower($matchingField) === 'phone') {
                        return $this->respond(new WP_Error('phone_conflict', 'This phone number is already linked to another account. Please use the email you used previously, or contact support.', ['status' => 409]));
                    }

                    // If GHL indicates email-based duplication, it's safe to reuse.
                    if (!empty($contactId) && is_string($matchingField) && strtolower($matchingField) === 'email') {
                        // safe path: reuse $contactId
                        // Contact exists in GHL - user is not truly new
                        $isTrulyNewUser = false;
                        error_log('[CA][INFO] Contact duplicate by email detected - user is not new (contactId: ' . $contactId . ')');
                        // Write-through: cache the duplicate contact locally
                        $this->writeThroughContact($effectiveLocationId, [
                            'id'        => $contactId,
                            'email'     => $email,
                            'firstName' => $firstName,
                            'lastName'  => $lastName,
                            'phone'     => $phone,
                        ]);
                    } elseif (!empty($contactId) && empty($matchingField)) {
                        // Defensive: if matchingField is missing, verify by searching contacts by email (fast, no retry).
                        // Fail CLOSED: if we cannot confirm it's an email-duplicate, we return a friendly conflict.
                        // Also: prefer the contactId returned by search (stronger than trusting meta.contactId).
                        $foundByEmail = false;
                        $contactIdFromSearch = null;
                        if (!$this->config->isGhlFetchAllowed()) {
                            // No GHL reads: trust the duplicate error payload contactId for email flow.
                            $foundByEmail = true;
                            $contactIdFromSearch = $contactId;
                        } else {
                            try {
                                // CRITICAL: locationId should be ONLY in header, NOT in query params
                                $search = $this->ghlClient->get('/contacts/search', [
                                    'query' => $email,
                                ], 5, $effectiveLocationId, 0);

                                if (is_wp_error($search)) {
                                    return $this->respond(new WP_Error('contact_conflict', 'We found an existing contact that conflicts with the details you entered. Please use the email you used previously or contact support.', ['status' => 409]));
                                }

                                $contacts = $search['contacts'] ?? $search['items'] ?? [];
                                foreach ((array)$contacts as $c) {
                                    $cEmail = $c['email'] ?? '';
                                    if ($cEmail && strcasecmp((string)$cEmail, (string)$email) === 0) {
                                        $foundByEmail = true;
                                        $contactIdFromSearch = $c['id'] ?? ($c['contactId'] ?? null);
                                        break;
                                    }
                                }
                            } catch (\Exception $e) {
                                return $this->respond(new WP_Error('contact_conflict', 'We found an existing contact that conflicts with the details you entered. Please use the email you used previously or contact support.', ['status' => 409]));
                            }
                        }

                        if (!$foundByEmail) {
                            return $this->respond(new WP_Error('contact_conflict', 'We found an existing contact with this email/phone combination, but could not safely confirm if this is the same person. Please use the email you used previously or contact support.', ['status' => 409]));
                        }

                        if (!empty($contactIdFromSearch)) {
                            $contactId = $contactIdFromSearch;
                            // Contact found by email search - user is not truly new
                            $isTrulyNewUser = false;
                            error_log('[CA][INFO] Contact found by email search - user is not new (contactId: ' . $contactId . ')');
                        }
                    } elseif (!empty($contactId) && is_string($matchingField) && $matchingField !== '') {
                        // Unknown matching field - treat as conflict to avoid wrong merge.
                        return $this->respond(new WP_Error('contact_conflict', 'We found an existing contact that conflicts with the details you entered. Please use the email you used previously or contact support.', ['status' => 409]));
                    }
                }
            } else {
                // Contact creation succeeded
                $contactId = $contactResult['contact']['id'] ?? null;
                if ($contactId) {
                    if ($isTrulyNewUser) {
                        error_log('[CA][INFO] Created new GHL contact: ' . $contactId);
                    } else {
                        error_log('[CA][INFO] GHL contact created (but user was not truly new): ' . $contactId);
                    }
                    // Write-through: cache the newly created contact locally
                    $createdContactData = $contactResult['contact'] ?? $contactResult;
                    if (is_array($createdContactData)) {
                        $this->writeThroughContact($effectiveLocationId, $createdContactData);
                    }
                }
            }
            }

            // Check if contactId is empty AFTER error handling (outside if/else block)
            if (empty($contactId)) {
                $errorMessage = 'Contact ID missing';
                if (isset($contactResult)) {
                    if ($contactResult instanceof WP_Error) {
                        $errorMessage = 'Failed to create contact: ' . $contactResult->get_error_message();
                    } elseif (is_array($contactResult) && isset($contactResult['contact']['id'])) {
                        // Contact was created but ID extraction failed
                        $errorMessage = 'Contact created but ID extraction failed';
                    }
                } elseif ($existingGhlContactId) {
                    // This shouldn't happen, but defensive check
                    $errorMessage = 'Existing contact ID was set but contactId is empty';
                }
                error_log('[CA][ERROR] contactId is empty after contact creation: ' . wp_json_encode([
                    'email' => $email,
                    'existingGhlContactId' => $existingGhlContactId,
                    'hasContactResult' => isset($contactResult),
                    'contactResultType' => isset($contactResult) ? gettype($contactResult) : 'not set',
                ]));
                return $this->respond(new WP_Error('contact_creation_failed', $errorMessage, ['status' => 500]));
            }

            // Step 2: Create estimate in GHL
            // Format phone to E.164 (GHL might require this)
            $formattedPhone = '';
            if ($phone) {
                $formattedPhone = str_starts_with($phone, '+') 
                    ? $phone 
                    : '+61' . ltrim($phone, '0');
            }
            
            $estimateData = [
                'altId' => $effectiveLocationId,
                'altType' => 'location',
                'name' => mb_substr("Quote - {$firstName} {$lastName}", 0, 40),
                'title' => 'ESTIMATE',
                'businessDetails' => [
                    'name' => $brandName,
                    'address' => [
                        'addressLine1' => $brandName,
                        'city' => 'Brisbane',
                        'state' => 'QLD',
                        'postalCode' => '4000',
                        'countryCode' => 'AU',
                    ],
                ],
                'currency' => 'AUD',
                'discount' => [
                    'type' => 'percentage',
                    'value' => 0,
                ],
                'contactDetails' => [
                    'id' => $contactId,
                    'email' => $email,
                    'name' => trim("{$firstName} {$lastName}"),
                    'phoneNo' => $formattedPhone,
                    'address' => [
                        'addressLine1' => '',
                        'city' => '',
                        'state' => '',
                        'postalCode' => '',
                        'countryCode' => 'AU',
                    ],
                ],
                'issueDate' => gmdate('Y-m-d'),
                'expiryDate' => gmdate('Y-m-d', strtotime('+30 days')),
                'frequencySettings' => ['enabled' => false],
                'liveMode' => true,
                'items' => $sanitizedItems,  // Use sanitized items (5 fields only)
            ];

            // Add property profile to notes if provided
            if ($propertyProfile) {
                $estimateData['termsNotes'] = "Property Profile: {$propertyProfile}";
            }

            // Use shorter timeout, no retry, and skip the slow post-create PUT for public quote flow
            $estimateResult = $this->estimateService->createEstimate($estimateData, 8, 0, true);
            
            if (is_wp_error($estimateResult)) {
                return $this->respond(new WP_Error('estimate_creation_failed', 'Failed to create estimate: ' . $estimateResult->get_error_message(), ['status' => 500]));
            }

            // Check if result has 'ok' key
            if (!isset($estimateResult['ok']) || !$estimateResult['ok']) {
                return $this->respond(new WP_Error('estimate_creation_failed', 'Failed to create estimate', ['status' => 500]));
            }

            // Extract ID from nested response structure
            $response = $estimateResult['result'] ?? [];
            $estimateId = $response['estimate']['id'] ?? $response['id'] ?? $response['_id'] ?? null;
            $estimateNumberFromCreate = $response['estimate']['estimateNumber'] ?? $response['estimateNumber'] ?? null;
            $estimateTotalFromCreate = $response['estimate']['total'] ?? $response['total'] ?? null;
            $estimateCurrencyFromCreate = $response['estimate']['currency'] ?? $response['currency'] ?? 'AUD';
            
            if (!$estimateId) {
                return $this->respond(new WP_Error('estimate_id_missing', 'Estimate created but ID missing in response', ['status' => 500]));
            }

            // Step 3: Create portal entry and send invitation
            // SECURITY: Generate invite token and hash it before storage
            $inviteToken = \CheapAlarms\Plugin\Services\PortalService::generateToken();
            $inviteTokenHash = \CheapAlarms\Plugin\Services\PortalService::hashInviteToken($inviteToken);
            
            // Use frontend URL (Next.js on Vercel) instead of WordPress backend URL
            $frontendUrl = $this->config->getFrontendUrl();
            $portalUrl = add_query_arg(
                [
                    'estimateId' => $estimateId,
                    'inviteToken' => $inviteToken, // Plaintext token in URL
                ],
                trailingslashit($frontendUrl) . 'portal'
            );

            // Check if user exists
            $userId = email_exists($email);
            if (!$userId) {
                // Create WordPress user. If this fails we must NOT proceed:
                // a half-built portal entry with $userId = 0 means no resetUrl
                // can be generated and the customer ends up with a "quote ready"
                // email that has no way to set a password.
                $createResult = wp_create_user($email, wp_generate_password(), $email);
                if (is_wp_error($createResult)) {
                    error_log('[CA][ERROR] Failed to create user during quote request: ' . wp_json_encode([
                        'email'        => $email,
                        'errorCode'    => $createResult->get_error_code(),
                        'errorMessage' => $createResult->get_error_message(),
                    ]));
                    // Clear the dedupe lock so the customer can retry once the
                    // underlying problem (e.g. username collision) is resolved.
                    delete_transient($lockKey);
                    return $this->respond(new WP_Error(
                        'account_creation_failed',
                        __('We could not create your account. Please contact support if this persists.', 'cheapalarms'),
                        ['status' => 500]
                    ));
                }
                $userId = $createResult;

                $user = get_user_by('id', $userId);
                if ($user) {
                    $user->set_role('ca_customer');
                    wp_update_user([
                        'ID' => $userId,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'display_name' => trim("{$firstName} {$lastName}"),
                    ]);
                }
            } else {
                // Ensure existing user has ca_customer role (has ca_access_portal capability)
                $user = get_user_by('id', $userId);
                if ($user && !in_array('ca_customer', $user->roles, true)) {
                    wp_update_user(['ID' => $userId, 'role' => 'ca_customer']);
                }
            }

            // Seed itemsMeta so the customer photo checklist auto-hides packages
            // and auto-marks individual items as required, without admin intervention.
            $initialItemsMeta = $this->portalService->buildInitialItemsMeta($itemHints);

            // Build portal meta (will be saved after password reset generation)
            $portalMeta = [
                'itemsMeta' => $initialItemsMeta,
                'account' => [
                    'inviteToken' => $inviteTokenHash, // Store hash, not plaintext
                    'portalUrl' => $portalUrl,
                    'status' => 'pending',
                    'statusLabel' => 'Invite sent',
                    'userId' => $userId ?: null,
                    'lastInviteAt' => current_time('mysql'),
                    'canResend' => true,
                    'expiresAt' => gmdate('c', current_time('timestamp') + DAY_IN_SECONDS * 7),
                    'email' => $email,
                    'locationId' => $effectiveLocationId,
                ],
                'quote' => [
                    'status' => null, // Will be set to 'sent' after auto-send
                    'statusLabel' => null,
                    'total' => null, // Will be populated when estimate is fetched
                    'approval_requested' => false, // NEW: Customer hasn't requested review yet
                ],
                'workflow' => [
                    'status' => 'requested',
                    'currentStep' => 1,
                    'requestedAt' => current_time('mysql'),
                ],
            ];

            // CRITICAL FIX: Get user context BEFORE attaching estimate
            // This ensures correct email variation detection for new users
            // The context must be calculated when ca_estimate_ids is still empty for new users
            // Clear user cache to ensure fresh metadata (no stale ca_estimate_ids from cache)
            if ($userId && $userId > 0) {
                clean_user_cache($userId);
                wp_cache_delete($userId, 'user_meta'); // Clear user meta cache specifically
                
                // If user is truly new (doesn't exist in GHL), clear stale password metadata
                // This prevents false "has password" detection from previous tests
                if ($isTrulyNewUser) {
                    delete_user_meta($userId, 'ca_password_set_at');
                    delete_user_meta($userId, 'ca_last_login');
                    error_log('[CA][INFO] Cleared stale password metadata for truly new user: ' . $email);
                }
            }
            $userContext = \CheapAlarms\Plugin\Services\UserContextHelper::getUserContext($userId, $email, $estimateId);
            
            // CRITICAL: Override user context if user is truly new
            // Force variation 'A' regardless of any stale metadata
            if ($isTrulyNewUser) {
                $userContext['isNewUser'] = true;
                $userContext['hasPasswordSet'] = false;
                $userContext['hasPreviousEstimates'] = false;
                $userContext['estimateCount'] = 0;
                error_log('[CA][INFO] Forced user context to new user (truly new - not in WordPress or GHL): ' . wp_json_encode([
                    'email' => $email,
                    'userId' => $userId,
                    'contactId' => $contactId,
                ]));
            }

            // Attach estimate to user if user exists
            if ($userId) {
                $estimateIds = get_user_meta($userId, 'ca_estimate_ids', true);
                if (!is_array($estimateIds)) {
                    $estimateIds = [];
                }
                if (!in_array($estimateId, $estimateIds, true)) {
                    $estimateIds[] = $estimateId;
                    update_user_meta($userId, 'ca_estimate_ids', $estimateIds);
                }
                
                // Store most recent estimate ID (singular) for auto-redirect
                update_user_meta($userId, 'ca_estimate_id', $estimateId);
                
                // Store location ID mapping
                $locations = get_user_meta($userId, 'ca_estimate_locations', true);
                if (!is_array($locations)) {
                    $locations = [];
                }
                $locations[$estimateId] = $effectiveLocationId;
                update_user_meta($userId, 'ca_estimate_locations', $locations);
            }

            // Generate password reset key for new users (pointing to Next.js frontend)
            // CRITICAL: This resetUrl is essential for users to set their password
            // Must always be generated when userId exists, with proper error logging
            $resetUrl = null;
            if ($userId && $userId > 0) {
                $user = get_user_by('id', $userId);
                if (!$user) {
                    error_log('[CA][ERROR] Failed to retrieve user object for resetUrl generation: ' . wp_json_encode([
                        'userId' => $userId,
                        'email' => $email,
                        'estimateId' => $estimateId,
                    ]));
                } else {
                    $key = get_password_reset_key($user);
                    if (is_wp_error($key)) {
                        error_log('[CA][ERROR] Failed to generate password reset key: ' . wp_json_encode([
                            'userId' => $userId,
                            'email' => $email,
                            'estimateId' => $estimateId,
                            'error' => $key->get_error_message(),
                            'errorCode' => $key->get_error_code(),
                            'userLogin' => $user->user_login,
                        ]));
                    } else {
                        $frontendUrl = $this->config->getFrontendUrl();
                        if (empty($frontendUrl)) {
                            error_log('[CA][ERROR] Frontend URL is not configured - cannot generate resetUrl: ' . wp_json_encode([
                                'userId' => $userId,
                                'email' => $email,
                                'estimateId' => $estimateId,
                            ]));
                        } else {
                            $resetUrl = add_query_arg(
                                [
                                    'key' => $key,
                                    'login' => rawurlencode($user->user_login),
                                    'estimateId' => $estimateId,
                                ],
                                trailingslashit($frontendUrl) . 'set-password'
                            );
                            
                            // Add reset URL to portal meta
                            $portalMeta['account']['resetUrl'] = $resetUrl;
                            
                            // Log successful generation for debugging
                            if (defined('WP_DEBUG') && WP_DEBUG) {
                                error_log('[CA][DEBUG] Successfully generated resetUrl: ' . wp_json_encode([
                                    'userId' => $userId,
                                    'email' => $email,
                                    'estimateId' => $estimateId,
                                    'resetUrlLength' => strlen($resetUrl),
                                ]));
                            }
                        }
                    }
                }
            } else {
                error_log('[CA][WARNING] Cannot generate resetUrl - userId is invalid: ' . wp_json_encode([
                    'userId' => $userId,
                    'email' => $email,
                    'estimateId' => $estimateId,
                ]));
            }
            
            // Save portal meta once with all data (CRITICAL: Portal access depends on this)
            $jsonMeta = wp_json_encode($portalMeta);
            if ($jsonMeta === false) {
                error_log('[CA][ERROR] Failed to encode portal meta JSON for estimate: ' . $estimateId);
                return $this->respond(new WP_Error('portal_save_failed', 'Failed to save portal data. Please contact support.', ['status' => 500]));
            }
            
            $metaSaved = update_option("ca_portal_meta_{$estimateId}", $jsonMeta);
            if (!$metaSaved) {
                error_log('[CA][ERROR] Failed to save portal meta for estimate: ' . $estimateId);
                return $this->respond(new WP_Error('portal_save_failed', 'Failed to save portal data. Please contact support.', ['status' => 500]));
            }

            // Step 4: Update workflow status (skip auto-sending estimate email - we'll send consolidated quote request email below)
            // Note: sendEstimate() should only be used for manual admin resends, not for new quote requests
            // This prevents duplicate emails (estimate email + quote request email)
            try {
                // Update workflow status to 'sent' without sending email
                $stored = get_option("ca_portal_meta_{$estimateId}", '{}');
                $meta = json_decode($stored, true);
                if (is_array($meta)) {
                    // Ensure workflow and quote arrays exist
                    if (!isset($meta['workflow'])) {
                        $meta['workflow'] = [];
                    }
                    if (!isset($meta['quote'])) {
                        $meta['quote'] = [];
                    }
                    $meta['workflow']['status'] = 'sent';
                    $meta['workflow']['currentStep'] = 2;
                    $meta['quote']['status'] = 'sent';
                    $meta['quote']['statusLabel'] = 'Sent';
                    $meta['quote']['sentAt'] = current_time('mysql');
                    $meta['quote']['sendCount'] = 1;
                    $meta['quote']['approval_requested'] = false; // Ensure approval is not requested on send
                    
                    // Store estimate snapshot data for fast dashboard loading using creation response (no extra GHL call)
                    $meta['quote']['number'] = $estimateNumberFromCreate ?? $estimateId;
                    $meta['quote']['total'] = $estimateTotalFromCreate ?? 0;
                    $meta['quote']['currency'] = $estimateCurrencyFromCreate ?? 'AUD';
                    $meta['quote']['last_synced_at'] = current_time('mysql');
                    
                    update_option("ca_portal_meta_{$estimateId}", wp_json_encode($meta));
                }
            } catch (\Exception $e) {
                // Log but don't fail - estimate creation succeeded
                error_log('[CA][WARNING] Exception updating workflow status: ' . $e->getMessage());
            }

            // Send context-aware invitation email via GHL Conversations API
            $displayName = trim("{$firstName} {$lastName}");
            
            // User context was already fetched above (before attaching estimate)
            // This ensures correct email variation detection
            
            // Get estimate number for email (use creation response, avoid extra GHL call)
            $estimateNumber = $estimateNumberFromCreate ?? $estimateId; // Fallback to estimateId
            
            // Get frontend URL for login link
            $frontendUrl = $this->config->getFrontendUrl();
            $loginUrl = trailingslashit($frontendUrl) . 'login';
            
            // Initialize emailTemplate variable (may be null if rendering fails)
            $emailTemplate = null;
            
            // CRITICAL: For new users (variation 'A'), resetUrl MUST be present
            // If it's missing, log a critical error as this prevents password setup
            $detectedVariation = \CheapAlarms\Plugin\Services\UserContextHelper::detectEmailVariation('quote-request', $userContext);
            
            // Log variation detection with full context
            error_log('[CA][INFO] Email variation detected: ' . $detectedVariation . ' for email: ' . $email . ' | ' . wp_json_encode([
                'isTrulyNewUser' => $isTrulyNewUser,
                'isNewUser' => $userContext['isNewUser'] ?? false,
                'hasPasswordSet' => $userContext['hasPasswordSet'] ?? false,
                'hasPreviousEstimates' => $userContext['hasPreviousEstimates'] ?? false,
                'estimateCount' => $userContext['estimateCount'] ?? 0,
                'resetUrlExists' => !empty($resetUrl),
                'userId' => $userId,
            ]));
            
            if ($detectedVariation === 'A' && empty($resetUrl)) {
                error_log('[CA][CRITICAL] resetUrl is missing for new user (variation A) - user cannot set password!: ' . wp_json_encode([
                    'userId' => $userId,
                    'email' => $email,
                    'estimateId' => $estimateId,
                    'userContext' => $userContext,
                    'isTrulyNewUser' => $isTrulyNewUser,
                ]));
            }
            
            // Render context-aware email template
            try {
                $emailTemplateService = $this->container->get(\CheapAlarms\Plugin\Services\EmailTemplateService::class);
                $emailData = [
                    'customerName' => $displayName,
                    'estimateNumber' => $estimateNumber,
                    'portalUrl' => $portalUrl,
                    'resetUrl' => $resetUrl,
                    'loginUrl' => $loginUrl,
                ];
                
                $emailTemplate = $emailTemplateService->renderQuoteRequestEmail($userContext, $emailData);
                $subject = $emailTemplate['subject'] ?? sprintf(__('Your %s quote is ready', 'cheapalarms'), $brandName);
                $message = $emailTemplate['body'] ?? '';
                
                // Fallback if template rendering failed
                if (empty($message)) {
                    error_log('[CA][WARNING] Email template returned empty body, using fallback');
                    $subject = sprintf(__('Your %s quote is ready', 'cheapalarms'), $brandName);
                    $greeting = sprintf(__('Hi %s,', 'cheapalarms'), esc_html($displayName));
                    $message = '<p>' . $greeting . '</p>';
                    $message .= '<p>' . esc_html(__('We have prepared your quote. Click the button below to set your password and access your estimate:', 'cheapalarms')) . '</p>';
                    if ($resetUrl) {
                        $message .= '<p><a href="' . esc_url($resetUrl) . '" style="display: inline-block; padding: 12px 24px; background-color: #c95375; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">' . esc_html(__('Set Your Password', 'cheapalarms')) . '</a></p>';
                    }
                    if ($portalUrl) {
                        $message .= '<p style="margin-top: 16px; color: #64748b; font-size: 14px;">' . esc_html(__('or', 'cheapalarms')) . ' <a href="' . esc_url($portalUrl) . '" style="color: #2fb6c9; text-decoration: underline;">' . esc_html(__('see your estimate as a guest', 'cheapalarms')) . '</a></p>';
                    }
                    $message .= '<p>' . esc_html(__('Thanks,', 'cheapalarms')) . '<br />' . esc_html($brandTeam) . '</p>';
                }
            } catch (\Exception $e) {
                error_log('[CA][ERROR] Failed to render email template: ' . $e->getMessage());
                // Fallback to simple email
                $subject = sprintf(__('Your %s quote is ready', 'cheapalarms'), $brandName);
                $greeting = sprintf(__('Hi %s,', 'cheapalarms'), esc_html($displayName));
                $message = '<p>' . $greeting . '</p>';
                $message .= '<p>' . esc_html(__('We have prepared your quote. Click the button below to set your password and access your estimate:', 'cheapalarms')) . '</p>';
                if ($resetUrl) {
                    $message .= '<p><a href="' . esc_url($resetUrl) . '" style="display: inline-block; padding: 12px 24px; background-color: #c95375; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">' . esc_html(__('Set Your Password', 'cheapalarms')) . '</a></p>';
                }
                if ($portalUrl) {
                    $message .= '<p style="margin-top: 16px; color: #64748b; font-size: 14px;">' . esc_html(__('or', 'cheapalarms')) . ' <a href="' . esc_url($portalUrl) . '" style="color: #2fb6c9; text-decoration: underline;">' . esc_html(__('see your estimate as a guest', 'cheapalarms')) . '</a></p>';
                }
                $message .= '<p>' . esc_html(__('Thanks,', 'cheapalarms')) . '<br />' . esc_html($brandTeam) . '</p>';
            }
            
            // Send via GHL Conversations API
            $emailPayload = [
                'contactId' => $contactId,
                'type' => 'Email',
                'status' => 'pending',
                'subject' => $subject,
                'html' => $message,
                'emailFrom' => $this->config->getEmailFromHeader(),
                'locationId' => $effectiveLocationId,
            ];
            
            $emailResult = $this->ghlClient->post('/conversations/messages', $emailPayload);
            
            $emailSent = !is_wp_error($emailResult);
            
            if ($emailSent) {
                $variation = isset($emailTemplate) ? ($emailTemplate['variation'] ?? 'A') : 'A';
                error_log('[CA][INFO] Quote invitation email sent via GHL to: ' . $email . ' (variation: ' . $variation . ')');
            } else {
                error_log('Failed to send GHL email to: ' . $email . ' - ' . ($emailResult instanceof WP_Error ? $emailResult->get_error_message() : 'Unknown error'));
            }

            // Success! Keep the lock for full 60 seconds to prevent duplicate submissions
            // (Don't clear it - let it expire naturally)
            
            return $this->respond([
                'ok' => true,
                'contactId' => $contactId,
                'estimateId' => $estimateId,
                'locationId' => $effectiveLocationId,
                'emailSent' => $emailSent,
                'message' => 'Quote request submitted successfully! Check your email for the portal link.',
            ]);

        } catch (\Exception $e) {
            // Clear lock on error so user can retry
            delete_transient($lockKey);
            
            error_log('Quote request error: ' . $e->getMessage());
            
            return $this->respond(new WP_Error('unexpected_error', 'An unexpected error occurred. Please try again.', ['status' => 500]));
        }
    }

    /**
     * Standardized response handler
     * 
     * @param array|WP_Error $result
     * @return WP_REST_Response
     */
    private function respond($result): WP_REST_Response
    {
        if (is_wp_error($result)) {
            return $this->errorResponse($result);
        }

        if (!isset($result['ok'])) {
            $result['ok'] = true;
        }

        $response = new WP_REST_Response($result, 200);
        $this->addSecurityHeaders($response);
        return $response;
    }

    /**
     * Create standardized error response with sanitization
     *
     * @param WP_Error $error
     * @return WP_REST_Response
     */
    private function errorResponse(WP_Error $error): WP_REST_Response
    {
        $status = $error->get_error_data()['status'] ?? 500;
        $code = $error->get_error_code();
        $message = $error->get_error_message();

        $response = new WP_REST_Response([
            'ok' => false,
            'error' => $message,
            'code' => $code,
        ], $status);

        $this->addSecurityHeaders($response);
        return $response;
    }

    /**
     * Add security headers to response
     *
     * @param WP_REST_Response $response
     */
    private function addSecurityHeaders(WP_REST_Response $response): void
    {
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-Frame-Options', 'DENY');
        $response->header('X-XSS-Protection', '1; mode=block');
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
            $contactRepo = $this->container->get(ContactSnapshotRepository::class);
            $record = ContactSnapshotRepository::normalizeFromGhl($contactData);
            $res    = $contactRepo->upsertOne($locationId, $record);
            if (is_wp_error($res)) {
                error_log('[CA][WARN] Contact write-through failed: ' . $res->get_error_message());
            }
        } catch (\Throwable $e) {
            error_log('[CA][WARN] Contact write-through exception: ' . $e->getMessage());
        }
    }
}

