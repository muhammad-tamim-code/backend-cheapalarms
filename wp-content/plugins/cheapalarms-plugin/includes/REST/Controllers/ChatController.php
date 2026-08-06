<?php

namespace CheapAlarms\Plugin\REST\Controllers;

use CheapAlarms\Plugin\Config\Config;
use CheapAlarms\Plugin\REST\Auth\Authenticator;
use CheapAlarms\Plugin\Services\Container;
use CheapAlarms\Plugin\Services\DeepSeekService;
use CheapAlarms\Plugin\Services\ChatHandoffNotifier;
use CheapAlarms\Plugin\Services\ChatLeadService;
use CheapAlarms\Plugin\Services\ChatQuoteService;
use CheapAlarms\Plugin\Services\ChatConversationService;
use CheapAlarms\Plugin\Services\ChatRouterService;
use CheapAlarms\Plugin\Services\ChatUiSuggester;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

use function is_wp_error;
use function register_rest_route;
use function sanitize_text_field;
use function wp_strip_all_tags;

class ChatController implements ControllerInterface
{
    private const MAX_MESSAGES     = 20;
    private const MAX_CONTENT_LENGTH = 2000;

    public function __construct(private Container $container)
    {
    }

    public function register(): void
    {
        register_rest_route('ca/v1', '/chat', [
            'methods'             => 'POST',
            'permission_callback' => '__return_true',
            'callback'            => [$this, 'handleChat'],
        ]);

        register_rest_route('ca/v1', '/chat/status', [
            'methods'             => 'GET',
            'permission_callback' => '__return_true',
            'callback'            => [$this, 'status'],
        ]);

        register_rest_route('ca/v1', '/chat/lead', [
            'methods'             => 'POST',
            'permission_callback' => '__return_true',
            'callback'            => [$this, 'handleLead'],
        ]);

        register_rest_route('ca/v1', '/chat/quote', [
            'methods'             => 'POST',
            'permission_callback' => '__return_true',
            'callback'            => [$this, 'handleQuote'],
        ]);

        register_rest_route('ca/v1', '/chat/route', [
            'methods'             => 'POST',
            'permission_callback' => '__return_true',
            'callback'            => [$this, 'handleRoute'],
        ]);

        register_rest_route('ca/v1', '/chat/poll', [
            'methods'             => 'POST',
            'permission_callback' => '__return_true',
            'callback'            => [$this, 'handlePoll'],
        ]);
    }

    public function status(WP_REST_Request $request): WP_REST_Response
    {
        $config = $this->container->get(Config::class);

        return $this->respond([
            'ok'        => true,
            'available' => $config->isDeepSeekConfigured(),
        ]);
    }

    public function handleChat(WP_REST_Request $request): WP_REST_Response
    {
        $auth = $this->container->get(Authenticator::class);
        $rate = $auth->enforceRateLimit('public_chat', 30, 600);
        if (is_wp_error($rate)) {
            return $this->respond($rate);
        }

        $body = $request->get_json_params();
        if (!is_array($body)) {
            return $this->respond(new WP_Error('invalid_body', __('Invalid request body.', 'cheapalarms'), ['status' => 400]));
        }

        $messages = $this->normalizeMessages($body['messages'] ?? null, $body['message'] ?? null);
        if (is_wp_error($messages)) {
            return $this->respond($messages);
        }

        $pageContext = $this->normalizePageContext($body['pageContext'] ?? null);
        $clientState = is_array($body['clientState'] ?? null) ? $body['clientState'] : [];
        $quoteSession = is_array($body['quoteSession'] ?? null) ? $body['quoteSession'] : [];
        $quoteMode = !empty($body['quoteMode']) || !empty($clientState['quoteMode']);
        $conversation = $this->resolveConversation($body, $pageContext);

        // Live-agent handoff: pause AI and store visitor messages only.
        if ($conversation !== null) {
            $convService = $this->container->get(ChatConversationService::class);
            $full        = $convService->findById($conversation['id']);
            if ($convService->isHumanHandoffActive($full)) {
                $lastUser = '';
                for ($i = count($messages) - 1; $i >= 0; $i--) {
                    if (($messages[$i]['role'] ?? '') === 'user') {
                        $lastUser = (string) ($messages[$i]['content'] ?? '');
                        break;
                    }
                }

                $stored = $convService->appendVisitorMessage($conversation['id'], $lastUser);
                if (is_wp_error($stored)) {
                    return $this->respond($stored);
                }

                if ($lastUser !== '' && $this->container->has(ChatHandoffNotifier::class)) {
                    $this->container->get(ChatHandoffNotifier::class)->notifyVisitorTelegramMessage(
                        $conversation['id'],
                        $lastUser
                    );
                }

                $status = (string) ($stored['status'] ?? ($full['status'] ?? 'waiting_agent'));
                $waitingCopy = $status === ChatConversationService::STATUS_AGENT_ACTIVE
                    ? __('Message sent to our team.', 'cheapalarms')
                    : __('Thanks, our team has been notified. Stay on this chat and someone will reply shortly.', 'cheapalarms');

                return $this->respond([
                    'ok'               => true,
                    'reply'            => $waitingCopy,
                    'handoff'          => true,
                    'status'           => $status,
                    'conversationKey'  => $conversation['sessionKey'],
                    'ui'               => [
                        'type'   => 'handoff_waiting',
                        'status' => $status,
                    ],
                ]);
            }
        }

        $useQuoteAgent = !$this->isQuoteSubmitted($clientState)
            && (
                $quoteMode
                || !empty($quoteSession['resolveToken'])
                || $this->shouldUseQuoteAgent($messages, $pageContext, $clientState)
            );

        if ($useQuoteAgent) {
            $quoteService = $this->container->get(ChatQuoteService::class);
            $result       = $quoteService->runAgent($messages, $pageContext, $quoteSession);
        } else {
            $service = $this->container->get(DeepSeekService::class);
            $result  = $service->chat($messages, $pageContext);
        }

        if (is_wp_error($result)) {
            return $this->respond($result);
        }

        $reply = $this->stripPricesFromReply((string) ($result['content'] ?? ''));
        $reply = $this->stripInternalTermsFromReply($reply);

        if ($reply === '') {
            $reply = __(
                'Tell me a bit about your property and what you need, I\'ll put together your quote and show a short form to verify your mobile. Pricing is sent to your portal, not in chat.',
                'cheapalarms'
            );
        }

        if ($conversation !== null) {
            $this->container->get(ChatConversationService::class)->syncTranscript(
                $conversation['id'],
                array_merge($messages, [['role' => 'assistant', 'content' => $reply]])
            );
        }

        $response = [
            'ok'    => true,
            'reply' => $reply,
            'model' => $result['model'] ?? null,
        ];

        if ($conversation !== null) {
            $response['conversationKey'] = $conversation['sessionKey'];
        }

        if (!empty($result['quoteSession']) && is_array($result['quoteSession'])) {
            $sanitized = $this->sanitizeQuoteSessionForClient($result['quoteSession']);
            if (!empty($sanitized['resolveToken'])) {
                $response['quoteSession'] = $sanitized;
                $response['ui']           = [
                    'type'         => 'quote_verify_form',
                    'resolveToken' => $sanitized['resolveToken'],
                    'summary'      => $sanitized['summary'] ?? [],
                ];
            }
        }

        if (!empty($result['quoteSubmitted'])) {
            $response['quoteSubmitted'] = true;
            $response['quoteResult']    = $result['quoteResult'] ?? null;
        }

        if ($useQuoteAgent) {
            $response['quoteMode'] = true;
        } elseif ($this->container->has(ChatUiSuggester::class)) {
            $handoffActive = false;
            if ($conversation !== null) {
                $full = $this->container->get(ChatConversationService::class)->findById($conversation['id']);
                $handoffActive = $this->container->get(ChatConversationService::class)->isHumanHandoffActive($full);
            }
            $ui = $this->container->get(ChatUiSuggester::class)->suggest(
                $messages,
                $reply,
                [
                    'pageContext'  => $pageContext,
                    'clientState'  => array_merge($clientState, [
                        'quoteMode'      => $quoteMode || $useQuoteAgent,
                        'handoffActive'  => $handoffActive || !empty($clientState['handoffActive']),
                    ]),
                    'quoteSession' => $response['quoteSession'] ?? null,
                ]
            );
            if ($ui !== null && empty($response['ui'])) {
                $response['ui'] = $ui;
            }
        }

        return $this->respond($response);
    }

    public function handleQuote(WP_REST_Request $request): WP_REST_Response
    {
        $auth = $this->container->get(Authenticator::class);
        $rate = $auth->enforceRateLimit('chat_quote', 5, 600);
        if (is_wp_error($rate)) {
            return $this->respond($rate);
        }

        $body = $request->get_json_params();
        if (!is_array($body)) {
            return $this->respond(new WP_Error('invalid_body', __('Invalid request body.', 'cheapalarms'), ['status' => 400]));
        }

        $service = $this->container->get(ChatQuoteService::class);
        $result  = $service->submitQuote($body);
        if (is_wp_error($result)) {
            return $this->respond($result);
        }

        $pageContext  = $this->normalizePageContext($body['pageContext'] ?? null);
        $conversation = $this->resolveConversation($body, $pageContext);
        if ($conversation !== null && !empty($result['estimateId'])) {
            $convService = $this->container->get(ChatConversationService::class);
            $convService->markQuoteSubmitted(
                $conversation['id'],
                (string) $result['estimateId'],
                (string) ($result['contactId'] ?? '')
            );
            $result['conversationKey'] = $conversation['sessionKey'];
        }

        return $this->respond($result);
    }

    public function handleRoute(WP_REST_Request $request): WP_REST_Response
    {
        $auth = $this->container->get(Authenticator::class);
        $rate = $auth->enforceRateLimit('public_chat', 30, 600);
        if (is_wp_error($rate)) {
            return $this->respond($rate);
        }

        $body = $request->get_json_params();
        if (!is_array($body)) {
            return $this->respond(new WP_Error('invalid_body', __('Invalid request body.', 'cheapalarms'), ['status' => 400]));
        }

        $message = sanitize_text_field((string) ($body['message'] ?? ''));
        $answers = is_array($body['answers'] ?? null) ? $body['answers'] : [];

        $router = $this->container->get(ChatRouterService::class);
        $result = $router->recommend($message, $answers);

        $pageContext  = $this->normalizePageContext($body['pageContext'] ?? null);
        $conversation = $this->resolveConversation($body, $pageContext);
        if ($conversation !== null) {
            $result['conversationKey'] = $conversation['sessionKey'];
            $this->container->get(ChatConversationService::class)->logExchange(
                $conversation['id'],
                $message !== '' ? $message : ('Service quiz: ' . ($answers['service'] ?? 'unknown')),
                $result['reason'] ?? $result['label'] ?? '',
                ['route' => $result['recommendation'] ?? null]
            );
        }

        return $this->respond($result);
    }

    public function handleLead(WP_REST_Request $request): WP_REST_Response
    {
        $auth = $this->container->get(Authenticator::class);
        $rate = $auth->enforceRateLimit('chat_lead', 10, 600);
        if (is_wp_error($rate)) {
            return $this->respond($rate);
        }

        $body = $request->get_json_params();
        if (!is_array($body)) {
            return $this->respond(new WP_Error('invalid_body', __('Invalid request body.', 'cheapalarms'), ['status' => 400]));
        }

        $service = $this->container->get(ChatLeadService::class);
        $result  = $service->submit($body);
        if (is_wp_error($result)) {
            return $this->respond($result);
        }

        $pageContext  = $this->normalizePageContext($body['pageContext'] ?? null);
        $intent       = sanitize_text_field((string) ($body['intent'] ?? ($result['intent'] ?? 'quote')));
        $isHandoff    = $intent === 'agent_handoff' || !empty($result['handoff']);
        $conversation = $this->resolveConversation($body, $pageContext);

        // Live handoff requires a conversation row; create a session if the client omitted one.
        if ($conversation === null && $isHandoff) {
            $conversation = $this->createConversationSession($pageContext);
        }

        if ($isHandoff && ($conversation === null || empty($result['contactId']))) {
            return $this->respond(new WP_Error(
                'handoff_unavailable',
                __('Could not start live chat. Please call 1300 225 276.', 'cheapalarms'),
                ['status' => 502]
            ));
        }

        if ($conversation !== null && !empty($result['contactId'])) {
            $convService = $this->container->get(ChatConversationService::class);
            if ($isHandoff) {
                $contact = is_array($result['contact'] ?? null) ? $result['contact'] : [];
                $isNew   = $convService->markWaitingAgent(
                    $conversation['id'],
                    (string) $result['contactId'],
                    $contact
                );
                if ($isNew && $this->container->has(ChatHandoffNotifier::class)) {
                    $this->container->get(ChatHandoffNotifier::class)->notifyWaitingAgent(
                        $conversation['id'],
                        $contact
                    );
                }
                $full   = $convService->findById($conversation['id']);
                $status = (string) ($full['status'] ?? ChatConversationService::STATUS_WAITING_AGENT);
                $result['status']  = $status;
                $result['handoff'] = true;
                $result['ui']      = [
                    'type'   => 'handoff_waiting',
                    'status' => $status,
                ];
            } else {
                $convService->markLeadCaptured(
                    $conversation['id'],
                    (string) $result['contactId'],
                    $intent
                );
            }
            $result['conversationKey'] = $conversation['sessionKey'];
        }

        return $this->respond($result);
    }

    public function handlePoll(WP_REST_Request $request): WP_REST_Response
    {
        $auth = $this->container->get(Authenticator::class);
        $rate = $auth->enforceRateLimit('public_chat_poll', 60, 60);
        if (is_wp_error($rate)) {
            return $this->respond($rate);
        }

        $body = $request->get_json_params();
        if (!is_array($body)) {
            return $this->respond(new WP_Error('invalid_body', __('Invalid request body.', 'cheapalarms'), ['status' => 400]));
        }

        $pageContext  = $this->normalizePageContext($body['pageContext'] ?? null);
        $conversation = $this->resolveConversation($body, $pageContext);
        if ($conversation === null) {
            return $this->respond(new WP_Error('missing_conversation', __('Missing conversation.', 'cheapalarms'), ['status' => 400]));
        }

        $afterId = max(0, (int) ($body['afterId'] ?? $body['after'] ?? 0));
        $poll    = $this->container->get(ChatConversationService::class)->pollMessages(
            $conversation['id'],
            $afterId
        );

        // Public clients only need agent + system lines for display (user already has their own).
        $messages = [];
        foreach ($poll['messages'] as $msg) {
            if (!in_array($msg['role'] ?? '', ['agent', 'system'], true)) {
                continue;
            }
            $messages[] = $msg;
        }

        return $this->respond([
            'ok'              => true,
            'status'          => $poll['status'],
            'messages'        => $messages,
            'claimedBy'       => $poll['claimedBy'],
            'conversationKey' => $conversation['sessionKey'],
            'handoff'         => in_array(
                $poll['status'],
                [
                    ChatConversationService::STATUS_WAITING_AGENT,
                    ChatConversationService::STATUS_AGENT_ACTIVE,
                ],
                true
            ),
        ]);
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, mixed> $pageContext
     * @return array{id: int, sessionKey: string}|null
     */
    private function resolveConversation(array $body, array $pageContext): ?array
    {
        $key = sanitize_text_field((string) ($body['conversationKey'] ?? ''));
        if ($key === '') {
            return null;
        }

        if (!$this->container->has(ChatConversationService::class)) {
            return null;
        }

        $resolved = $this->container->get(ChatConversationService::class)->resolve($key, $pageContext);
        if (is_wp_error($resolved)) {
            return null;
        }

        return [
            'id'         => (int) $resolved['id'],
            'sessionKey' => (string) $resolved['sessionKey'],
        ];
    }

    /**
     * Create a new chat session when the client omitted conversationKey (handoff must not be silent).
     *
     * @param array<string, mixed> $pageContext
     * @return array{id: int, sessionKey: string}|null
     */
    private function createConversationSession(array $pageContext): ?array
    {
        if (!$this->container->has(ChatConversationService::class)) {
            return null;
        }

        try {
            $key = 'sg' . bin2hex(random_bytes(12));
        } catch (\Throwable $e) {
            $key = 'sg' . substr(hash('sha256', uniqid((string) mt_rand(), true)), 0, 24);
        }

        $resolved = $this->container->get(ChatConversationService::class)->resolve($key, $pageContext);
        if (is_wp_error($resolved)) {
            return null;
        }

        return [
            'id'         => (int) $resolved['id'],
            'sessionKey' => (string) $resolved['sessionKey'],
        ];
    }

    /**
     * @param mixed $raw
     * @return array{path?: string, service?: string, title?: string}
     */
    private function normalizePageContext($raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $context = [];

        if (isset($raw['path'])) {
            $path = sanitize_text_field((string) $raw['path']);
            if ($path !== '') {
                $context['path'] = mb_substr($path, 0, 200);
            }
        }

        if (isset($raw['service'])) {
            $service = sanitize_text_field((string) $raw['service']);
            if ($service !== '') {
                $context['service'] = mb_substr($service, 0, 64);
            }
        }

        if (isset($raw['title'])) {
            $title = sanitize_text_field((string) $raw['title']);
            if ($title !== '') {
                $context['title'] = mb_substr($title, 0, 200);
            }
        }

        return $context;
    }

    /**
     * @param array<string, mixed> $clientState
     */
    private function isQuoteSubmitted(array $clientState): bool
    {
        return !empty($clientState['quoteSubmitted']);
    }

    /**
     * @param array<int, array{role: string, content: string}> $messages
     * @param array<string, mixed>                             $pageContext
     * @param array<string, mixed>                             $clientState
     */
    private function shouldUseQuoteAgent(array $messages, array $pageContext, array $clientState): bool
    {
        if (!empty($clientState['quoteMode'])) {
            return true;
        }

        if ($this->isQuoteSubmitted($clientState)) {
            return false;
        }

        $lastUser = '';
        for ($i = count($messages) - 1; $i >= 0; $i--) {
            if (($messages[$i]['role'] ?? '') === 'user') {
                $lastUser = strtolower((string) ($messages[$i]['content'] ?? ''));
                break;
            }
        }

        if ($lastUser === '') {
            return false;
        }

        if (preg_match('/\b(quote my alarm|ajax quote|alarm quote|wireless alarm|instant quote|price my alarm|kit for my)\b/u', $lastUser)) {
            return true;
        }

        $service = (string) ($pageContext['service'] ?? '');
        if ($service === 'alarms' || $service === 'quote') {
            if (preg_match('/\b(quote|pricing|how much|cost|kit|ajax|alarm)\b/u', $lastUser)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $session
     * @return array{resolveToken?: string, summary?: array<int, array<string, mixed>>}
     */
    private function sanitizeQuoteSessionForClient(array $session): array
    {
        $token = sanitize_text_field((string) ($session['resolveToken'] ?? ''));
        if ($token === '') {
            return [];
        }

        $summary = [];
        if (is_array($session['summary'] ?? null)) {
            foreach ($session['summary'] as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $name = sanitize_text_field((string) ($row['name'] ?? ''));
                $qty  = (int) ($row['qty'] ?? 0);
                if ($name !== '' && $qty > 0) {
                    $summary[] = ['name' => $name, 'qty' => $qty];
                }
            }
        }

        return [
            'resolveToken' => $token,
            'summary'      => $summary,
        ];
    }

    /**
     * Remove dollar amounts and priced-kit summaries from assistant replies (chat is lead-capture only).
     */
    private function stripPricesFromReply(string $content): string
    {
        if ($content === '') {
            return $content;
        }

        // Drop lines that are primarily price summaries.
        $lines = preg_split('/\r\n|\r|\n/u', $content) ?: [$content];
        $kept  = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                $kept[] = $line;
                continue;
            }

            if (preg_match('/\b(estimated total|hardware subtotal|install estimate|supply\s*&\s*install|incl\.?\s*gst|gst inclusive)\b/iu', $trimmed)) {
                continue;
            }

            if (preg_match('/^\*{0,2}\s*\$\d/u', $trimmed)) {
                continue;
            }

            $kept[] = $line;
        }

        $content = trim(implode("\n", $kept));

        // Strip inline dollar amounts.
        $content = preg_replace('/\$\d[\d,]*(?:\.\d{2})?(?:\s*(?:incl\.?\s*)?GST)?/iu', '', $content) ?? $content;
        $content = preg_replace('/\b\d[\d,]*(?:\.\d{2})?\s*(?:AUD|dollars?)\b/iu', '', $content) ?? $content;
        $content = preg_replace('/\n{3,}/u', "\n\n", $content) ?? $content;

        return trim($content);
    }

    /**
     * Remove internal/dev terminology from assistant replies.
     */
    private function stripInternalTermsFromReply(string $content): string
    {
        if ($content === '') {
            return $content;
        }

        $patterns = [
            '/\b(GHL|GoHighLevel|Go High Level)\b/iu',
            '/\b(server-side|server side|backend|webhook|API|REST)\b/iu',
            '/\b(WordPress|DeepSeek|resolver|resolve_build|resolve token|transient)\b/iu',
            '/\b(calculator engine|pricing engine|same engine|online Ajax calculator)\b/iu',
            '/\b(internal systems?|as per our policy)\b/iu',
        ];

        $content = preg_replace($patterns, '', $content) ?? $content;
        $content = preg_replace('/\n{3,}/u', "\n\n", $content) ?? $content;
        $content = preg_replace('/  +/u', ' ', $content) ?? $content;

        return trim($content);
    }

    /**
     * @param mixed $messagesParam
     * @param mixed $singleMessage
     * @return array<int, array{role: string, content: string}>|WP_Error
     */
    private function normalizeMessages($messagesParam, $singleMessage)
    {
        $messages = [];

        if (is_array($messagesParam) && $messagesParam !== []) {
            foreach ($messagesParam as $entry) {
                if (!is_array($entry)) {
                    continue;
                }
                $normalized = $this->normalizeMessageEntry($entry);
                if (is_wp_error($normalized)) {
                    return $normalized;
                }
                if ($normalized !== null) {
                    $messages[] = $normalized;
                }
            }
        } elseif (is_string($singleMessage) && trim($singleMessage) !== '') {
            $normalized = $this->normalizeMessageEntry([
                'role'    => 'user',
                'content' => $singleMessage,
            ]);
            if (is_wp_error($normalized)) {
                return $normalized;
            }
            if ($normalized !== null) {
                $messages[] = $normalized;
            }
        }

        if ($messages === []) {
            return new WP_Error('missing_message', __('Please enter a message.', 'cheapalarms'), ['status' => 400]);
        }

        if (count($messages) > self::MAX_MESSAGES) {
            $messages = array_slice($messages, -self::MAX_MESSAGES);
        }

        $last = $messages[count($messages) - 1];
        if ($last['role'] !== 'user') {
            return new WP_Error('invalid_message', __('The last message must be from the user.', 'cheapalarms'), ['status' => 400]);
        }

        return $messages;
    }

    /**
     * @param array<string, mixed> $entry
     * @return array{role: string, content: string}|null|WP_Error
     */
    private function normalizeMessageEntry(array $entry)
    {
        $role = sanitize_text_field((string) ($entry['role'] ?? ''));
        if ($role !== 'user' && $role !== 'assistant') {
            return null;
        }

        $content = wp_strip_all_tags((string) ($entry['content'] ?? ''));
        $content = trim(preg_replace('/\s+/u', ' ', $content) ?? '');

        if ($content === '') {
            return null;
        }

        if (mb_strlen($content) > self::MAX_CONTENT_LENGTH) {
            return new WP_Error(
                'message_too_long',
                sprintf(
                    /* translators: %d: max characters */
                    __('Messages must be %d characters or fewer.', 'cheapalarms'),
                    self::MAX_CONTENT_LENGTH
                ),
                ['status' => 400]
            );
        }

        return [
            'role'    => $role,
            'content' => $content,
        ];
    }

    /**
     * @param array<string, mixed>|WP_Error $result
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

    private function errorResponse(WP_Error $error): WP_REST_Response
    {
        $status  = $error->get_error_data()['status'] ?? 500;
        $code    = $error->get_error_code();
        $message = $error->get_error_message();

        $response = new WP_REST_Response([
            'ok'    => false,
            'err'   => $message,
            'code'  => $code,
        ], (int) $status);

        $this->addSecurityHeaders($response);

        return $response;
    }

    private function addSecurityHeaders(WP_REST_Response $response): void
    {
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-XSS-Protection', '1; mode=block');
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
