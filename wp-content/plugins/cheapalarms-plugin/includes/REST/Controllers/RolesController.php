<?php

namespace CheapAlarms\Plugin\REST\Controllers;

use CheapAlarms\Plugin\REST\Auth\Authenticator;
use CheapAlarms\Plugin\Services\AuthorizationService;
use CheapAlarms\Plugin\Services\Container;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

use function get_user_by;
use function wp_get_current_user;
use function is_email;
use function is_wp_error;
use function sanitize_email;
use function sanitize_text_field;
use function wp_create_user;
use function wp_delete_user;
use function wp_generate_password;

class RolesController implements ControllerInterface
{
    private Authenticator $auth;
    private AuthorizationService $authorization;

    public function __construct(private Container $container)
    {
        $this->auth = $this->container->get(Authenticator::class);
        $this->authorization = $this->container->get(AuthorizationService::class);
    }

    public function register(): void
    {
        register_rest_route('ca/v1', '/roles', [
            'methods'             => 'GET',
            'permission_callback' => fn () => $this->auth->requirePermission('admin.access'),
            'callback'            => [$this, 'listRoles'],
        ]);

        register_rest_route('ca/v1', '/admin/staff-users', [
            'methods'             => 'GET',
            'permission_callback' => fn () => $this->auth->requirePermission('admin.access'),
            'callback'            => [$this, 'listStaffUsers'],
        ]);

        register_rest_route('ca/v1', '/admin/users/(?P<userId>[0-9]+)/role', [
            'methods'             => 'PUT',
            'permission_callback' => fn () => true,
            'callback'            => function (WP_REST_Request $request) {
                $authError = $this->requireAuthenticated();
                if (is_wp_error($authError)) {
                    return $this->errorResponse($authError);
                }

                $check = $this->auth->requirePermission('settings.manage');
                if (is_wp_error($check)) {
                    return $this->errorResponse($check);
                }

                return $this->assignRole($request);
            },
            'args'                => [
                'userId' => [
                    'required' => true,
                    'type'     => 'integer',
                ],
            ],
        ]);

        register_rest_route('ca/v1', '/admin/users', [
            'methods'             => 'POST',
            'permission_callback' => fn () => true,
            'callback'            => function (WP_REST_Request $request) {
                return $this->createStaffUser($request);
            },
        ]);
    }

    public function listRoles(WP_REST_Request $request): WP_REST_Response
    {
        $user = wp_get_current_user();
        $assignable = $this->authorization->getAssignableRoles($user);

        $response = new WP_REST_Response([
            'ok'    => true,
            'roles' => $this->authorization->getRoleCatalog(),
            'assignable_roles' => $assignable,
        ], 200);
        $this->addSecurityHeaders($response);

        return $response;
    }

    public function listStaffUsers(WP_REST_Request $request): WP_REST_Response
    {
        $limit = (int) ($request->get_param('limit') ?: 100);
        $users = $this->authorization->listStaffUsers($limit);

        $response = new WP_REST_Response([
            'ok'    => true,
            'users' => $users,
            'total' => count($users),
        ], 200);
        $this->addSecurityHeaders($response);

        return $response;
    }

    public function assignRole(WP_REST_Request $request): WP_REST_Response
    {
        $userId = (int) $request->get_param('userId');
        $body = $request->get_json_params() ?? [];
        $roleKey = sanitize_text_field($body['role_key'] ?? '');
        $allowedLocationIds = isset($body['allowed_location_ids']) && is_array($body['allowed_location_ids'])
            ? $body['allowed_location_ids']
            : null;

        $target = get_user_by('id', $userId);
        if (!$target) {
            return $this->errorResponse(new WP_Error('not_found', __('User not found.', 'cheapalarms'), ['status' => 404]));
        }

        $actor = wp_get_current_user();
        $result = $this->authorization->assignProductRole($target, $roleKey, $actor, $allowedLocationIds);
        if (is_wp_error($result)) {
            return $this->errorResponse($result);
        }

        $resolved = $this->authorization->resolveForUser($target);

        $response = new WP_REST_Response([
            'ok'   => true,
            'user' => [
                'id'                   => $target->ID,
                'email'                => $target->user_email,
                'role_key'             => $resolved['role_key'],
                'role_label'           => $resolved['role_label'],
                'permissions'          => $resolved['permissions'],
                'allowed_location_ids' => $resolved['allowed_location_ids'],
            ],
        ], 200);
        $this->addSecurityHeaders($response);

        return $response;
    }

    public function createStaffUser(WP_REST_Request $request): WP_REST_Response
    {
        $authError = $this->requireAuthenticated();
        if (is_wp_error($authError)) {
            return $this->errorResponse($authError);
        }

        $body = $request->get_json_params() ?? [];
        $email = sanitize_email($body['email'] ?? '');
        $displayName = sanitize_text_field($body['display_name'] ?? $body['displayName'] ?? '');
        $roleKey = sanitize_text_field($body['role_key'] ?? AuthorizationService::ROLE_CUSTOMER);
        $allowedLocationIds = isset($body['allowed_location_ids']) && is_array($body['allowed_location_ids'])
            ? $body['allowed_location_ids']
            : null;

        if ($email === '' || !is_email($email)) {
            return $this->errorResponse(new WP_Error('bad_request', __('Valid email is required.', 'cheapalarms'), ['status' => 400]));
        }

        $actor = wp_get_current_user();
        $isStaffRole = in_array($roleKey, [AuthorizationService::ROLE_STAFF, AuthorizationService::ROLE_OWNER], true);
        if ($isStaffRole) {
            $ownerCheck = $this->auth->requirePermission('settings.manage');
            if (is_wp_error($ownerCheck)) {
                return $this->errorResponse($ownerCheck);
            }
        } else {
            $inviteCheck = $this->auth->requirePermission('customers.invite');
            if (is_wp_error($inviteCheck)) {
                return $this->errorResponse($inviteCheck);
            }
        }

        $precheck = $this->authorization->validateNewUserRole($actor, $roleKey);
        if (is_wp_error($precheck)) {
            return $this->errorResponse($precheck);
        }

        if (get_user_by('email', $email)) {
            return $this->errorResponse(new WP_Error('bad_request', __('A user with this email already exists.', 'cheapalarms'), ['status' => 409]));
        }

        $userId = wp_create_user($email, wp_generate_password(24, true, true), $email);
        if (is_wp_error($userId)) {
            return $this->errorResponse($userId);
        }

        $target = get_user_by('id', $userId);
        if (!$target) {
            return $this->errorResponse(new WP_Error('server_error', __('Failed to create user.', 'cheapalarms'), ['status' => 500]));
        }

        if ($displayName !== '') {
            $target->display_name = $displayName;
            $target->first_name = $displayName;
            $target->save();
        }

        $assign = $this->authorization->assignProductRole($target, $roleKey, $actor, $allowedLocationIds);
        if (is_wp_error($assign)) {
            if (!function_exists('wp_delete_user')) {
                require_once ABSPATH . 'wp-admin/includes/user.php';
            }
            wp_delete_user($userId);
            return $this->errorResponse($assign);
        }

        $resolved = $this->authorization->resolveForUser($target);

        $response = new WP_REST_Response([
            'ok'   => true,
            'user' => [
                'id'                   => $target->ID,
                'email'                => $target->user_email,
                'displayName'          => $target->display_name,
                'role_key'             => $resolved['role_key'],
                'role_label'           => $resolved['role_label'],
                'permissions'          => $resolved['permissions'],
                'allowed_location_ids' => $resolved['allowed_location_ids'],
            ],
        ], 201);
        $this->addSecurityHeaders($response);

        return $response;
    }

    private function requireAuthenticated(): bool|WP_Error
    {
        $this->auth->ensureUserLoaded();
        $user = wp_get_current_user();
        if (!$user || $user->ID <= 0) {
            return new WP_Error(
                'unauthorized',
                __('Authentication required.', 'cheapalarms'),
                ['status' => 401]
            );
        }

        return true;
    }

    private function errorResponse(WP_Error $error): WP_REST_Response
    {
        $data = $error->get_error_data();
        $status = (is_array($data) && isset($data['status'])) ? (int) $data['status'] : 500;

        $response = new WP_REST_Response([
            'ok'    => false,
            'error' => $error->get_error_message(),
            'code'  => $error->get_error_code(),
        ], $status);
        $this->addSecurityHeaders($response);

        return $response;
    }

    private function addSecurityHeaders(WP_REST_Response $response): void
    {
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-XSS-Protection', '1; mode=block');
        $response->header('X-Frame-Options', 'DENY');
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
