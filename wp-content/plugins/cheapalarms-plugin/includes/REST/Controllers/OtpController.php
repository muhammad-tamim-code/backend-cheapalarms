<?php

namespace CheapAlarms\Plugin\REST\Controllers;

use CheapAlarms\Plugin\REST\Auth\Authenticator;
use CheapAlarms\Plugin\Services\Container;
use CheapAlarms\Plugin\Services\OtpVerificationService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

use function is_wp_error;
use function register_rest_route;
use function sanitize_text_field;

class OtpController implements ControllerInterface
{
    public function __construct(private Container $container)
    {
    }

    public function register(): void
    {
        register_rest_route('ca/v1', '/otp/send', [
            'methods'             => 'POST',
            'permission_callback' => '__return_true',
            'callback'            => [$this, 'send'],
        ]);

        register_rest_route('ca/v1', '/otp/verify', [
            'methods'             => 'POST',
            'permission_callback' => '__return_true',
            'callback'            => [$this, 'verify'],
        ]);
    }

    public function send(WP_REST_Request $request): WP_REST_Response
    {
        $auth = $this->container->get(Authenticator::class);
        $rate = $auth->enforceRateLimit('otp_send', 10, 600);
        if (is_wp_error($rate)) {
            return $this->respond($rate);
        }

        $body  = $request->get_json_params();
        $phone = sanitize_text_field((string) (is_array($body) ? ($body['phone'] ?? '') : ''));

        $service = $this->container->get(OtpVerificationService::class);
        $result  = $service->sendCode($phone);

        return $this->respond($result);
    }

    public function verify(WP_REST_Request $request): WP_REST_Response
    {
        $auth = $this->container->get(Authenticator::class);
        $rate = $auth->enforceRateLimit('otp_verify', 20, 600);
        if (is_wp_error($rate)) {
            return $this->respond($rate);
        }

        $body = $request->get_json_params();
        if (!is_array($body)) {
            return $this->respond(new WP_Error('invalid_body', __('Invalid request body.', 'cheapalarms'), ['status' => 400]));
        }

        $phone = sanitize_text_field((string) ($body['phone'] ?? ''));
        $code  = sanitize_text_field((string) ($body['code'] ?? ''));

        $service = $this->container->get(OtpVerificationService::class);
        $result  = $service->verifyCode($phone, $code);

        return $this->respond($result);
    }

    /**
     * @param array<string, mixed>|WP_Error $result
     */
    private function respond($result): WP_REST_Response
    {
        if (is_wp_error($result)) {
            $status = (int) ($result->get_error_data()['status'] ?? 500);

            return new WP_REST_Response([
                'ok'   => false,
                'err'  => $result->get_error_message(),
                'code' => $result->get_error_code(),
            ], $status);
        }

        if (!isset($result['ok'])) {
            $result['ok'] = true;
        }

        return new WP_REST_Response($result, 200);
    }
}
