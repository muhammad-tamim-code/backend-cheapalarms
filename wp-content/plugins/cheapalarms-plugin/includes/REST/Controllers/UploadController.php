<?php

namespace CheapAlarms\Plugin\REST\Controllers;

use CheapAlarms\Plugin\REST\Auth\Authenticator;
use CheapAlarms\Plugin\REST\Controllers\Base\AdminController;
use CheapAlarms\Plugin\Services\Container;
use CheapAlarms\Plugin\Services\UploadService;
use WP_REST_Request;

class UploadController extends AdminController
{
    private UploadService $service;
    private Authenticator $auth;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->service = $this->container->get(UploadService::class);
        $this->auth    = $this->container->get(Authenticator::class);
    }

    public function register(): void
    {
        register_rest_route('ca/v1', '/upload/start', [
            'methods'             => 'POST',
            'permission_callback' => fn () => true,
            'callback'            => function (WP_REST_Request $request) {
                $this->auth->ensureConfigured();
                $payload = $request->get_json_params();
                if (!is_array($payload)) {
                    $payload = json_decode($request->get_body(), true);
                }
                if (!is_array($payload)) {
                    $payload = [];
                }
                $result = $this->service->start($payload);
                return $this->respond($result);
            },
        ]);

        register_rest_route('ca/v1', '/upload', [
            'methods'             => 'POST',
            'permission_callback' => fn () => true,
            'callback'            => function (WP_REST_Request $request) {
                try {
                    $this->auth->ensureConfigured();
                    $result = $this->service->handle($request);
                    return $this->respond($result);
                } catch (\Throwable $e) {
                    if (function_exists('error_log')) {
                        error_log(sprintf(
                            '[CA] Upload fatal: %s in %s:%d',
                            $e->getMessage(),
                            $e->getFile(),
                            $e->getLine()
                        ));
                    }
                    return $this->respond(new \WP_Error(
                        'upload_fatal',
                        __('Upload failed unexpectedly.', 'cheapalarms') . ' ' . $e->getMessage(),
                        ['status' => 500]
                    ));
                }
            },
        ]);
    }
}
