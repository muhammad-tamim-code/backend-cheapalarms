<?php

namespace CheapAlarms\Plugin\REST\Controllers;

use CheapAlarms\Plugin\REST\Auth\Authenticator;
use CheapAlarms\Plugin\REST\Controllers\Base\AdminController;
use CheapAlarms\Plugin\Services\Container;
use CheapAlarms\Plugin\Services\GhlClient;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

use function sanitize_email;
use function sanitize_text_field;

class GhlController extends AdminController
{
    private GhlClient $client;
    private Authenticator $auth;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->client = $this->container->get(GhlClient::class);
        $this->auth   = $this->container->get(Authenticator::class);
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
                'callback'            => function (WP_REST_Request $request) {
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

                    $email = !empty($body['email']) ? sanitize_email($body['email']) : '';
                    $phone = !empty($body['phone']) ? sanitize_text_field($body['phone']) : '';
                    $firstName = !empty($body['firstName']) ? sanitize_text_field($body['firstName']) : '';
                    $lastName = !empty($body['lastName']) ? sanitize_text_field($body['lastName']) : '';

                    if (empty($email) && empty($phone)) {
                        return $this->respond(new WP_Error('bad_request', __('Email or phone is required.', 'cheapalarms'), ['status' => 400]));
                    }

                    $payload = [
                        'email' => $email,
                        'phone' => $phone,
                        'firstName' => $firstName,
                        'lastName' => $lastName,
                        'locationId' => $config->getLocationId(),
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
                                    $errorBody = null; // Don't use invalid JSON
                                }
                            }
                            
                            // Check if error indicates duplicate contact with contactId in meta
                            // GHL error structure: { "statusCode": 400, "message": "...", "meta": { "contactId": "..." } }
                            if (is_array($errorBody)) {
                                $errorMessage = $errorBody['message'] ?? '';
                                $statusCode = $errorBody['statusCode'] ?? $errorData['code'] ?? null;
                                $hasDuplicateMessage = stripos($errorMessage, 'duplicate') !== false || stripos($errorMessage, 'duplicated') !== false;
                                $hasContactId = isset($errorBody['meta']['contactId']) && !empty($errorBody['meta']['contactId']);
                                
                                if ($statusCode === 400 && ($hasDuplicateMessage || $hasContactId)) {
                                    // Extract contactId from error metadata
                                    $existingContactId = $errorBody['meta']['contactId'] ?? '';
                                    
                                    if (!empty($existingContactId)) {
                                        // Contact exists, return it as success
                                        return $this->respond([
                                            'contact' => [
                                                'id' => $existingContactId,
                                                'email' => $email,
                                                'firstName' => $firstName,
                                                'lastName' => $lastName,
                                            ],
                                        ]);
                                    }
                                }
                            }
                        }
                        
                        // For other errors, return the error
                        return $this->respond($result);
                    }

                    return $this->respond(['contact' => $result]);
                },
            ],
        ]);

        // List contacts endpoint
        register_rest_route('ca/v1', '/ghl/contacts/list', [
            'methods'             => 'GET',
            'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_view_estimates'),
            'callback'            => function (WP_REST_Request $request) {
                $config = $this->container->get(\CheapAlarms\Plugin\Config\Config::class);
                
                if (empty($config->getGhlToken())) {
                    return $this->respond(new WP_Error('configuration_error', __('GHL API key not configured.', 'cheapalarms'), ['status' => 500]));
                }
                
                if (empty($config->getLocationId())) {
                    return $this->respond(new WP_Error('configuration_error', __('GHL location ID not configured.', 'cheapalarms'), ['status' => 500]));
                }

                $limit = (int) ($request->get_param('limit') ?: 50);
                
                // Cap limit at 100 for performance
                $limit = min($limit, 100);

                // Pass locationId as query parameter (required by GHL API for /contacts/ endpoint)
                // Also pass as header for compatibility
                // Note: GHL API doesn't support 'offset' parameter for /contacts/ endpoint
                $locationId = $config->getLocationId();
                $result = $this->client->get('/contacts/', [
                    'locationId' => $locationId,  // Required in query string
                    'limit' => $limit,
                    // 'offset' is not supported by GHL API for /contacts/ endpoint
                ], 25, $locationId);  // Also pass as header
                
                if (is_wp_error($result)) {
                    $logger = $this->container->get(\CheapAlarms\Plugin\Services\Logger::class);
                    $logger->error('GHL contacts list error', [
                        'error' => $result->get_error_message(),
                        'code' => $result->get_error_code(),
                        'data' => $result->get_error_data(),
                    ]);
                    return $this->respond($result);
                }

                // GHL API response structure can vary - handle different formats
                $contacts = [];
                $total = 0;
                
                // Try different possible response structures
                if (isset($result['contacts']) && is_array($result['contacts'])) {
                    // Standard format: { contacts: [...], meta: { total: ... } }
                    $contacts = $result['contacts'];
                    $total = $result['meta']['total'] ?? count($contacts);
                } elseif (isset($result['contact']) && is_array($result['contact'])) {
                    // Single contact wrapped
                    $contacts = [$result['contact']];
                    $total = 1;
                } elseif (is_array($result) && isset($result[0]) && isset($result[0]['id'])) {
                    // Direct array of contacts
                    $contacts = $result;
                    $total = count($contacts);
                } else {
                    // Log the actual response structure for debugging
                    $logger = $this->container->get(\CheapAlarms\Plugin\Services\Logger::class);
                    $logger->warning('Unexpected GHL contacts response structure', [
                        'response_keys' => array_keys($result),
                        'response_sample' => is_array($result) ? array_slice($result, 0, 1) : $result,
                    ]);
                }

                return $this->respond([
                    'contacts' => $contacts,
                    'total' => $total,
                    'limit' => $limit,
                ]);
            },
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

