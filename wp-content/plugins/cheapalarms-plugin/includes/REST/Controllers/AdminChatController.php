<?php

namespace CheapAlarms\Plugin\REST\Controllers;

use CheapAlarms\Plugin\REST\Auth\Authenticator;
use CheapAlarms\Plugin\Services\ChatConversationService;
use CheapAlarms\Plugin\Services\Container;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

use function is_wp_error;
use function register_rest_route;
use function sanitize_text_field;
use function wp_get_current_user;
use function wp_strip_all_tags;

class AdminChatController implements ControllerInterface
{
    public function __construct(private Container $container)
    {
    }

    public function register(): void
    {
        register_rest_route('ca/v1', '/admin/chat/queue', [
            'methods'             => 'GET',
            'permission_callback' => function () {
                $auth = $this->container->get(Authenticator::class);
                $check = $auth->requireCapability('ca_manage_portal');
                return is_wp_error($check) ? $check : true;
            },
            'callback'            => [$this, 'queue'],
        ]);

        register_rest_route('ca/v1', '/admin/chat/conversations/(?P<id>\d+)', [
            'methods'             => 'GET',
            'permission_callback' => function () {
                $auth = $this->container->get(Authenticator::class);
                $check = $auth->requireCapability('ca_manage_portal');
                return is_wp_error($check) ? $check : true;
            },
            'callback'            => [$this, 'getConversation'],
        ]);

        register_rest_route('ca/v1', '/admin/chat/conversations/(?P<id>\d+)/claim', [
            'methods'             => 'POST',
            'permission_callback' => function () {
                $auth = $this->container->get(Authenticator::class);
                $check = $auth->requireCapability('ca_manage_portal');
                return is_wp_error($check) ? $check : true;
            },
            'callback'            => [$this, 'claim'],
        ]);

        register_rest_route('ca/v1', '/admin/chat/conversations/(?P<id>\d+)/messages', [
            [
                'methods'             => 'GET',
                'permission_callback' => function () {
                    $auth = $this->container->get(Authenticator::class);
                    $check = $auth->requireCapability('ca_manage_portal');
                    return is_wp_error($check) ? $check : true;
                },
                'callback'            => [$this, 'listMessages'],
            ],
            [
                'methods'             => 'POST',
                'permission_callback' => function () {
                    $auth = $this->container->get(Authenticator::class);
                    $check = $auth->requireCapability('ca_manage_portal');
                    return is_wp_error($check) ? $check : true;
                },
                'callback'            => [$this, 'postMessage'],
            ],
        ]);

        register_rest_route('ca/v1', '/admin/chat/conversations/(?P<id>\d+)/resolve', [
            'methods'             => 'POST',
            'permission_callback' => function () {
                $auth = $this->container->get(Authenticator::class);
                $check = $auth->requireCapability('ca_manage_portal');
                return is_wp_error($check) ? $check : true;
            },
            'callback'            => [$this, 'resolve'],
        ]);
    }

    public function queue(WP_REST_Request $request): WP_REST_Response
    {
        $limit  = max(1, min(100, (int) $request->get_param('limit') ?: 50));
        $offset = max(0, (int) $request->get_param('offset'));
        $status = sanitize_text_field((string) ($request->get_param('status') ?? ''));

        $service = $this->container->get(ChatConversationService::class);
        if ($status === 'history') {
            $result = $service->listHistory($limit, $offset);
        } elseif ($status !== '') {
            $result = $service->listRecent($limit, $offset, $status);
        } else {
            // Expire stale waiting chats before showing the live inbox.
            $service->timeoutExpiredWaitingAgents();
            $result = $service->listQueue(null, $limit, $offset);
        }

        $items = [];
        foreach ($result['items'] as $row) {
            $items[] = $this->summarizeConversation($row);
        }

        return $this->ok([
            'items' => $items,
            'total' => (int) ($result['total'] ?? 0),
        ]);
    }

    public function getConversation(WP_REST_Request $request): WP_REST_Response
    {
        $id      = (int) $request->get_param('id');
        $service = $this->container->get(ChatConversationService::class);
        $row     = $service->getWithMessages($id);
        if ($row === null) {
            return $this->fail(new WP_Error('not_found', __('Conversation not found.', 'cheapalarms'), ['status' => 404]));
        }

        return $this->ok($this->formatConversation($row));
    }

    public function claim(WP_REST_Request $request): WP_REST_Response
    {
        $user = wp_get_current_user();
        $id   = (int) $request->get_param('id');
        $result = $this->container->get(ChatConversationService::class)->claim($id, (int) $user->ID);
        if (is_wp_error($result)) {
            return $this->fail($result);
        }

        return $this->ok($this->formatConversation($result));
    }

    public function listMessages(WP_REST_Request $request): WP_REST_Response
    {
        $id      = (int) $request->get_param('id');
        $afterId = max(0, (int) $request->get_param('after'));
        $poll    = $this->container->get(ChatConversationService::class)->pollMessages($id, $afterId);

        return $this->ok($poll);
    }

    public function postMessage(WP_REST_Request $request): WP_REST_Response
    {
        $user = wp_get_current_user();
        $id   = (int) $request->get_param('id');
        $body = $request->get_json_params();
        if (!is_array($body)) {
            $body = [];
        }

        $content = wp_strip_all_tags((string) ($body['content'] ?? $body['message'] ?? ''));
        $content = trim(preg_replace('/\s+/u', ' ', $content) ?? '');

        $result = $this->container->get(ChatConversationService::class)->appendAgentMessage(
            $id,
            (int) $user->ID,
            $content
        );
        if (is_wp_error($result)) {
            return $this->fail($result);
        }

        return $this->ok($result);
    }

    public function resolve(WP_REST_Request $request): WP_REST_Response
    {
        $user = wp_get_current_user();
        $id   = (int) $request->get_param('id');
        $body = $request->get_json_params();
        if (!is_array($body)) {
            $body = [];
        }

        $returnToAi = !empty($body['returnToAi']);
        $result     = $this->container->get(ChatConversationService::class)->resolveConversation(
            $id,
            (int) $user->ID,
            $returnToAi
        );
        if (is_wp_error($result)) {
            return $this->fail($result);
        }

        return $this->ok($result);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function summarizeConversation(array $row): array
    {
        $meta    = $this->decodeMeta($row['meta_json'] ?? null);
        $contact = is_array($meta['contact'] ?? null) ? $meta['contact'] : [];

        return [
            'id'               => (int) ($row['id'] ?? 0),
            'sessionKey'       => (string) ($row['session_key'] ?? ''),
            'status'           => (string) ($row['status'] ?? ''),
            'intent'           => (string) ($row['intent'] ?? ''),
            'pagePath'         => (string) ($row['page_path'] ?? ''),
            'pageTitle'        => (string) ($row['page_title'] ?? ''),
            'ghlContactId'     => (string) ($row['ghl_contact_id'] ?? ''),
            'messageCount'     => (int) ($row['message_count'] ?? 0),
            'claimedBy'        => isset($row['claimed_by']) && $row['claimed_by'] !== null ? (int) $row['claimed_by'] : null,
            'claimedAt'        => (string) ($row['claimed_at'] ?? ''),
            'updatedAt'        => (string) ($row['updated_at'] ?? ''),
            'lastUserMessageAt'=> (string) ($row['last_user_message_at'] ?? ''),
            'contact'          => $contact,
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function formatConversation(array $row): array
    {
        $summary  = $this->summarizeConversation($row);
        $messages = [];
        foreach (($row['messages'] ?? []) as $msg) {
            if (!is_array($msg)) {
                continue;
            }
            $messages[] = [
                'id'        => (int) ($msg['id'] ?? 0),
                'role'      => (string) ($msg['role'] ?? ''),
                'content'   => (string) ($msg['content'] ?? ''),
                'createdAt' => (string) ($msg['created_at'] ?? ''),
            ];
        }
        $summary['messages'] = $messages;
        $summary['meta']     = is_array($row['meta'] ?? null) ? $row['meta'] : $this->decodeMeta($row['meta_json'] ?? null);

        return $summary;
    }

    /**
     * @param mixed $raw
     * @return array<string, mixed>
     */
    private function decodeMeta($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (!is_string($raw) || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function ok(array $data): WP_REST_Response
    {
        if (!isset($data['ok'])) {
            $data['ok'] = true;
        }

        return new WP_REST_Response($data, 200);
    }

    private function fail(WP_Error $error): WP_REST_Response
    {
        $status = $error->get_error_data()['status'] ?? 500;

        return new WP_REST_Response([
            'ok'   => false,
            'err'  => $error->get_error_message(),
            'code' => $error->get_error_code(),
        ], (int) $status);
    }
}
