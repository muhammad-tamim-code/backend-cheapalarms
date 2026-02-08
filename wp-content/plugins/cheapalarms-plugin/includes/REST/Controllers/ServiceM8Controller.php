<?php

namespace CheapAlarms\Plugin\REST\Controllers;

use CheapAlarms\Plugin\REST\Auth\Authenticator;
use CheapAlarms\Plugin\REST\Controllers\Base\AdminController;
use CheapAlarms\Plugin\REST\Controllers\Helpers\ServiceM8ControllerHelper;
use CheapAlarms\Plugin\Services\Container;
use CheapAlarms\Plugin\Services\JobLinkService;
use CheapAlarms\Plugin\Services\ServiceM8Service;
use CheapAlarms\Plugin\Services\Shared\LocationResolver;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

use function current_time;
use function sanitize_email;
use function sanitize_text_field;
use function sanitize_textarea_field;

class ServiceM8Controller extends AdminController
{
    private ServiceM8Service $service;
    private JobLinkService $linkService;
    private Authenticator $auth;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->service = $this->container->get(ServiceM8Service::class);
        $this->linkService = $this->container->get(JobLinkService::class);
        $this->auth    = $this->container->get(Authenticator::class);
    }

    public function register(): void
    {
        // Test connection endpoint
        register_rest_route('ca/v1', '/servicem8/test', [
            'methods'             => 'GET',
            'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_view_estimates'),
            'callback'            => function () {
                $result = $this->service->testConnection();
                return $this->respond($result);
            },
        ]);

        // Companies endpoints
        register_rest_route('ca/v1', '/servicem8/companies', [
            [
                'methods'             => 'GET',
                'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_view_estimates'),
                'callback'            => function (WP_REST_Request $request) {
                    $params = [
                        'uuid' => $request->get_param('uuid'),
                        'name' => $request->get_param('name'),
                    ];
                    $result = $this->service->getCompanies($params);
                    return $this->respond($result);
                },
            ],
            [
                'methods'             => 'POST',
                'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_manage_portal'),
                'callback'            => function (WP_REST_Request $request) {
                    $body = $request->get_json_params();
                    if (!is_array($body)) {
                        $body = json_decode($request->get_body(), true);
                    }
                    if (!is_array($body)) {
                        $body = [];
                    }
                    $result = $this->service->createCompany($body);
                    return $this->respond($result);
                },
            ],
        ]);

        // Staff endpoints
        register_rest_route('ca/v1', '/servicem8/staff', [
            [
                'methods'             => 'GET',
                'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_view_estimates'),
                'callback'            => function (WP_REST_Request $request) {
                    $params = [
                        'uuid' => $request->get_param('uuid'),
                        'name' => $request->get_param('name'),
                        'email' => $request->get_param('email'),
                    ];
                    $result = $this->service->getStaff($params);
                    return $this->respond($result);
                },
            ],
        ]);

        // Single staff member endpoint
        register_rest_route('ca/v1', '/servicem8/staff/(?P<uuid>[a-zA-Z0-9\-]+)', [
            [
                'methods'             => 'GET',
                'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_view_estimates'),
                'callback'            => function (WP_REST_Request $request) {
                    $uuid = sanitize_text_field($request->get_param('uuid'));
                    $result = $this->service->getStaffMember($uuid);
                    return $this->respond($result);
                },
            ],
        ]);

        // Jobs endpoints
        register_rest_route('ca/v1', '/servicem8/jobs', [
            [
                'methods'             => 'GET',
                'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_view_estimates'),
                'callback'            => function (WP_REST_Request $request) {
                    $params = [
                        'uuid' => $request->get_param('uuid'),
                        'company_uuid' => $request->get_param('company_uuid'),
                        'status' => $request->get_param('status'),
                    ];
                    $result = $this->service->getJobs($params);
                    return $this->respond($result);
                },
            ],
            [
                'methods'             => 'POST',
                'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_manage_portal'),
                'callback'            => function (WP_REST_Request $request) {
                    $body = $request->get_json_params();
                    if (!is_array($body)) {
                        $body = json_decode($request->get_body(), true);
                    }
                    if (!is_array($body)) {
                        $body = [];
                    }
                    $result = $this->service->createJob($body);
                    return $this->respond($result);
                },
            ],
        ]);

        // Job linking endpoints (MUST be registered BEFORE single job endpoints to avoid route conflict)
        register_rest_route('ca/v1', '/servicem8/jobs/link', [
            [
                'methods'             => 'POST',
                'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_manage_portal'),
                'callback'            => function (WP_REST_Request $request) {
                    $body = $request->get_json_params();
                    if (!is_array($body)) {
                        $body = json_decode($request->get_body(), true);
                    }
                    if (!is_array($body)) {
                        return $this->respond(new WP_Error('invalid_body', 'Invalid request body', ['status' => 400]));
                    }

                    $estimateId = sanitize_text_field($body['estimateId'] ?? '');
                    $jobUuid = sanitize_text_field($body['jobUuid'] ?? '');
                    $metadata = is_array($body['metadata'] ?? null) ? $body['metadata'] : null;

                    if (empty($estimateId) || empty($jobUuid)) {
                        return $this->respond(new WP_Error('missing_params', 'estimateId and jobUuid are required', ['status' => 400]));
                    }

                    $result = $this->linkService->linkEstimateToJob($estimateId, $jobUuid, $metadata);
                    
                    if (is_wp_error($result)) {
                        return $this->respond($result);
                    }

                    $linkData = $this->linkService->getLinkByEstimateId($estimateId);
                    return $this->respond(['ok' => true, 'link' => $linkData]);
                },
            ],
            [
                'methods'             => 'GET',
                'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_view_estimates'),
                'callback'            => function (WP_REST_Request $request) {
                    $estimateId = sanitize_text_field($request->get_param('estimateId') ?? '');
                    $jobUuid = sanitize_text_field($request->get_param('jobUuid') ?? '');

                    // SECURITY: Require at least one parameter
                    if (empty($estimateId) && empty($jobUuid)) {
                        return $this->respond(new WP_Error('missing_params', 'estimateId or jobUuid is required', ['status' => 400]));
                    }

                    // SECURITY: Validate UUID format if provided
                    if (!empty($jobUuid) && !ServiceM8ControllerHelper::validateUuid($jobUuid)) {
                        return $this->respond(new WP_Error('invalid_uuid', 'Invalid job UUID format', ['status' => 400]));
                    }

                    // SECURITY: Validate estimateId format (alphanumeric, hyphens, underscores)
                    if (!empty($estimateId) && !ServiceM8ControllerHelper::validateEstimateId($estimateId)) {
                        return $this->respond(new WP_Error('invalid_estimate_id', 'Invalid estimate ID format', ['status' => 400]));
                    }

                    if (!empty($estimateId)) {
                        $linkData = $this->linkService->getLinkByEstimateId($estimateId);
                        if (!$linkData) {
                            return $this->respond(new WP_Error('not_found', 'Link not found', ['status' => 404]));
                        }
                        return $this->respond(['ok' => true, 'link' => $linkData]);
                    }

                    if (!empty($jobUuid)) {
                        $linkData = $this->linkService->getLinkByJobUuid($jobUuid);
                        if (!$linkData) {
                            return $this->respond(new WP_Error('not_found', 'Link not found', ['status' => 404]));
                        }
                        return $this->respond(['ok' => true, 'link' => $linkData]);
                    }

                    return $this->respond(new WP_Error('missing_params', 'estimateId or jobUuid is required', ['status' => 400]));
                },
            ],
            [
                'methods'             => 'DELETE',
                'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_manage_portal'),
                'callback'            => function (WP_REST_Request $request) {
                    $estimateId = sanitize_text_field($request->get_param('estimateId') ?? '');

                    if (empty($estimateId)) {
                        return $this->respond(new WP_Error('missing_params', 'estimateId is required', ['status' => 400]));
                    }

                    $result = $this->linkService->unlinkEstimateFromJob($estimateId);
                    
                    if (!$result) {
                        return $this->respond(new WP_Error('not_found', 'Link not found', ['status' => 404]));
                    }
                    return $this->respond(['ok' => true, 'message' => 'Link removed']);
                },
            ],
        ]);

        // List all links (admin only)
        register_rest_route('ca/v1', '/servicem8/jobs/links', [
            [
                'methods'             => 'GET',
                'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_manage_portal'),
                'callback'            => function (WP_REST_Request $request) {
                    $limit = (int) ($request->get_param('limit') ?: 100);
                    $limit = min($limit, 500); // Cap at 500
                    
                    $links = $this->linkService->getAllLinks($limit);
                    
                    return $this->respond(['ok' => true, 'links' => $links, 'count' => count($links)]);
                },
            ],
        ]);

        // Update job from estimate
        register_rest_route('ca/v1', '/servicem8/jobs/update-from-estimate', [
            [
                'methods'             => 'POST',
                'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_manage_portal'),
                'callback'            => function (WP_REST_Request $request) {
                    $body = $request->get_json_params();
                    if (!is_array($body)) {
                        $body = json_decode($request->get_body(), true);
                    }
                    if (!is_array($body)) {
                        return $this->respond(new WP_Error('invalid_body', 'Invalid request body', ['status' => 400]));
                    }

                    $estimateId = sanitize_text_field($body['estimateId'] ?? '');
                    $locationId = sanitize_text_field($body['locationId'] ?? '');
                    $jobUuid = sanitize_text_field($body['jobUuid'] ?? '');
                    $options = is_array($body['options'] ?? null) ? $body['options'] : [];

                    if (empty($estimateId)) {
                        return $this->respond(new WP_Error('missing_params', 'estimateId is required', ['status' => 400]));
                    }

                    if (empty($locationId)) {
                        return $this->respond(new WP_Error('missing_params', 'locationId is required', ['status' => 400]));
                    }

                    if (empty($jobUuid)) {
                        // Try to get job UUID from existing link
                        $existingLink = $this->linkService->getLinkByEstimateId($estimateId);
                        if (!$existingLink || empty($existingLink['jobUuid'])) {
                            return $this->respond(new WP_Error('missing_params', 'jobUuid is required or estimate must be linked to a job', ['status' => 400]));
                        }
                        $jobUuid = $existingLink['jobUuid'];
                    }

                    // Update job from estimate
                    $result = $this->service->updateJobFromEstimate($estimateId, $locationId, $jobUuid, $options);
                    
                    if (is_wp_error($result)) {
                        return $this->respond($result);
                    }

                    return $this->respond([
                        'ok' => true,
                        'job' => $result['job'],
                        'jobUuid' => $result['jobUuid'],
                        'company' => $result['company'],
                        'updated' => true,
                    ]);
                },
            ],
        ]);

        // Create job from estimate
        register_rest_route('ca/v1', '/servicem8/jobs/create-from-estimate', [
            [
                'methods'             => 'POST',
                'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_manage_portal'),
                'callback'            => function (WP_REST_Request $request) {
                    $body = $request->get_json_params();
                    if (!is_array($body)) {
                        $body = json_decode($request->get_body(), true);
                    }
                    if (!is_array($body)) {
                        return $this->respond(new WP_Error('invalid_body', 'Invalid request body', ['status' => 400]));
                    }

                    $estimateId = sanitize_text_field($body['estimateId'] ?? '');
                    $locationIdParam = sanitize_text_field($body['locationId'] ?? '');
                    $options = is_array($body['options'] ?? null) ? $body['options'] : [];

                    if (empty($estimateId)) {
                        return $this->respond(new WP_Error('missing_params', 'estimateId is required', ['status' => 400]));
                    }

                    // Resolve locationId from request body or config default (like other admin endpoints)
                    $locationId = !empty($locationIdParam) 
                        ? $locationIdParam 
                        : $this->locationResolver->resolve(null);
                    
                    if (empty($locationId)) {
                        return $this->respond(new WP_Error('missing_params', 'locationId is required. Please provide it in the request or configure it in settings.', ['status' => 400]));
                    }

                    // Check if estimate is already linked
                    $existingLink = $this->linkService->getLinkByEstimateId($estimateId);
                    $updateIfExists = filter_var($body['updateIfExists'] ?? $request->get_param('updateIfExists') ?? false, FILTER_VALIDATE_BOOLEAN);
                    
                    if ($existingLink && $updateIfExists) {
                        // Update existing job instead of creating new one
                        $jobUuid = $existingLink['jobUuid'] ?? null;
                        if (empty($jobUuid)) {
                            return $this->respond(new WP_Error('invalid_state', 'Existing link found but job UUID is missing', ['status' => 400]));
                        }

                        $result = $this->service->updateJobFromEstimate($estimateId, $locationId, $jobUuid, $options);
                        
                        if (is_wp_error($result)) {
                            return $this->respond($result);
                        }

                        return $this->respond([
                            'ok' => true,
                            'job' => $result['job'],
                            'jobUuid' => $result['jobUuid'],
                            'company' => $result['company'],
                            'updated' => true,
                            'linked' => true,
                        ]);
                    } elseif ($existingLink) {
                        return $this->respond(new WP_Error('conflict', 'Estimate is already linked to a job', ['status' => 409, 'existingLink' => $existingLink, 'hint' => 'Use updateIfExists=true to update the existing job']));
                    }

                    // Create job from estimate (with idempotency check via linkService)
                    $result = $this->service->createJobFromEstimate($estimateId, $locationId, $options, $this->linkService);
                    
                    if (is_wp_error($result)) {
                        return $this->respond($result);
                    }

                    // Auto-link the job
                    // Try multiple possible UUID locations in the response
                    $jobUuid = $result['jobUuid'] ?? $result['job']['uuid'] ?? $result['job']['job_uuid'] ?? $result['job']['id'] ?? null;
                    
                    if ($jobUuid) {
                        $linkMetadata = [
                            'companyUuid' => $result['company']['uuid'] ?? null,
                            'companyCreated' => $result['companyCreated'] ?? false,
                            'createdFrom' => 'estimate',
                            'createdAt' => current_time('mysql'),
                        ];
                        
                        $linkResult = $this->linkService->linkEstimateToJob($estimateId, $jobUuid, $linkMetadata);
                        if (is_wp_error($linkResult)) {
                            // Log error but don't fail the request - job was created successfully
                            error_log('Failed to link job after creation: ' . $linkResult->get_error_message());
                        }
                    } else {
                        // Log warning if UUID not found
                        error_log('Job UUID not found in response, cannot create link. Response keys: ' . implode(', ', array_keys($result)));
                    }

                    return $this->respond([
                        'ok' => true,
                        'job' => $result['job'],
                        'jobUuid' => $jobUuid, // Explicitly include UUID
                        'company' => $result['company'],
                        'companyCreated' => $result['companyCreated'] ?? false,
                        'linked' => !empty($jobUuid),
                    ]);
                },
            ],
        ]);

        // Job Activities endpoints (scheduling)
        register_rest_route('ca/v1', '/servicem8/jobs/(?P<jobUuid>[a-zA-Z0-9\-]+)/activities', [
            [
                'methods'             => 'GET',
                'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_view_estimates'),
                'callback'            => function (WP_REST_Request $request) {
                    $jobUuid = sanitize_text_field($request->get_param('jobUuid'));
                    
                    // SECURITY: Validate UUID format
                    if (!ServiceM8ControllerHelper::validateUuid($jobUuid)) {
                        return $this->respond(new WP_Error('invalid_uuid', 'Invalid job UUID format', ['status' => 400]));
                    }
                    
                    $result = $this->service->getJobActivities($jobUuid);
                    return $this->respond($result);
                },
            ],
        ]);

        // Schedule job endpoint (creates Job Activity)
        register_rest_route('ca/v1', '/servicem8/jobs/(?P<jobUuid>[a-zA-Z0-9\-]+)/schedule', [
            [
                'methods'             => 'POST',
                'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_manage_portal'),
                'callback'            => function (WP_REST_Request $request) {
                    $jobUuid = sanitize_text_field($request->get_param('jobUuid'));
                    
                    // SECURITY: Validate UUID format
                    if (!ServiceM8ControllerHelper::validateUuid($jobUuid)) {
                        return $this->respond(new WP_Error('invalid_uuid', 'Invalid job UUID format', ['status' => 400]));
                    }
                    
                    $body = $request->get_json_params();
                    if (!is_array($body)) {
                        $body = json_decode($request->get_body(), true);
                    }
                    if (!is_array($body)) {
                        $body = [];
                    }

                    $staffUuid = sanitize_text_field($body['staffUuid'] ?? '');
                    $startDate = sanitize_text_field($body['startDate'] ?? '');
                    $endDate = sanitize_text_field($body['endDate'] ?? '');

                    // SECURITY: Validate staffUuid format
                    if (!empty($staffUuid) && !ServiceM8ControllerHelper::validateUuid($staffUuid)) {
                        return $this->respond(new WP_Error('invalid_uuid', 'Invalid staff UUID format', ['status' => 400]));
                    }

                    if (empty($staffUuid)) {
                        return $this->respond(new WP_Error('missing_params', 'staffUuid is required', ['status' => 400]));
                    }
                    if (empty($startDate) || empty($endDate)) {
                        return $this->respond(new WP_Error('missing_params', 'startDate and endDate are required', ['status' => 400]));
                    }

                    $result = $this->service->scheduleJob($jobUuid, $staffUuid, $startDate, $endDate, $this->linkService);
                    return $this->respond($result);
                },
            ],
        ]);

        // Single job endpoints (MUST be registered AFTER job linking endpoints to avoid route conflict)
        register_rest_route('ca/v1', '/servicem8/jobs/(?P<uuid>[a-zA-Z0-9\-]+)', [
            [
                'methods'             => 'GET',
                'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_view_estimates'),
                'callback'            => function (WP_REST_Request $request) {
                    $uuid = sanitize_text_field($request->get_param('uuid'));
                    
                    // SECURITY: Validate UUID format
                    if (!ServiceM8ControllerHelper::validateUuid($uuid)) {
                        return $this->respond(new WP_Error('invalid_uuid', 'Invalid job UUID format', ['status' => 400]));
                    }
                    
                    $result = $this->service->getJob($uuid);
                    return $this->respond($result);
                },
            ],
            [
                'methods'             => 'DELETE',
                'permission_callback' => fn () => $this->isDevBypass() ?: $this->auth->requireCapability('ca_manage_portal'),
                'callback'            => function (WP_REST_Request $request) {
                    $uuid = sanitize_text_field($request->get_param('uuid'));
                    
                    // SECURITY: Validate UUID format
                    if (!ServiceM8ControllerHelper::validateUuid($uuid)) {
                        return $this->respond(new WP_Error('invalid_uuid', 'Invalid job UUID format', ['status' => 400]));
                    }
                    
                    $result = $this->service->deleteJob($uuid);
                    return $this->respond($result);
                },
            ],
        ]);
    }

    /**
     * @param array|WP_Error $result
     */
    protected function respond($result, ?WP_REST_Request $request = null): WP_REST_Response
    {
        if (is_wp_error($result)) {
            $status = $result->get_error_data()['status'] ?? 500;
            $errorData = $result->get_error_data();
            $body = $errorData['body'] ?? null;
            
            // Try to parse ServiceM8 error message from response body
            $errorMessage = $result->get_error_message();
            $errorDetails = null;
            
            if ($body) {
                $parsedBody = json_decode($body, true);
                if (is_array($parsedBody)) {
                    $errorDetails = $parsedBody;
                    // ServiceM8 often returns error details in 'message' or 'error' field
                    if (!empty($parsedBody['message'])) {
                        $errorMessage = $parsedBody['message'];
                    } elseif (!empty($parsedBody['error'])) {
                        $errorMessage = $parsedBody['error'];
                    } elseif (!empty($parsedBody['errors'])) {
                        if (is_array($parsedBody['errors'])) {
                            $errorMessage = implode(', ', array_map(function($err) {
                                return is_array($err) ? json_encode($err) : $err;
                            }, $parsedBody['errors']));
                        } else {
                            $errorMessage = $parsedBody['errors'];
                        }
                    }
                } else {
                    // If not JSON, include raw body
                    $errorMessage = $body;
                }
            }
            
            $response = [
                'ok'  => false,
                'err' => $errorMessage,
                'error' => $errorMessage, // Standardized field
                'code'=> $result->get_error_code(),
            ];
            
            // SECURITY: Only include detailed error information in debug mode
            if (defined('WP_DEBUG') && WP_DEBUG && !empty($errorDetails)) {
                $response['details'] = $errorDetails;
            }
            
            $restResponse = new WP_REST_Response($response, $status);
            $this->addSecurityHeaders($restResponse);
            return $restResponse;
        }

        if (!isset($result['ok'])) {
            $result['ok'] = true;
        }

        $response = new WP_REST_Response($result, 200);
        $this->addSecurityHeaders($response);
        return $response;
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

