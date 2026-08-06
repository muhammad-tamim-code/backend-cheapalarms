<?php

namespace CheapAlarms\Plugin\REST\Controllers;

use CheapAlarms\Plugin\Config\Config;
use CheapAlarms\Plugin\Services\ChatConversationService;
use CheapAlarms\Plugin\Services\Container;
use CheapAlarms\Plugin\Services\TelegramBotService;
use WP_REST_Request;
use WP_REST_Response;

use function hash_equals;
use function htmlspecialchars;
use function is_array;
use function is_wp_error;
use function json_decode;
use function preg_match;
use function preg_split;
use function register_rest_route;
use function rest_url;
use function sanitize_text_field;
use function sprintf;
use function strpos;
use function strtolower;
use function wp_strip_all_tags;

/**
 * Telegram Bot webhook for live-chat claim + private agent DMs.
 *
 * Flow: staff group gets alert → Claim → bot opens a private DM with the
 * claiming agent. Further customer ↔ agent traffic stays in that DM.
 */
class TelegramWebhookController implements ControllerInterface
{
    public function __construct(private Container $container)
    {
    }

    public function register(): void
    {
        register_rest_route('ca/v1', '/telegram/webhook', [
            'methods'             => 'POST',
            'permission_callback' => '__return_true',
            'callback'            => [$this, 'handleWebhook'],
        ]);

        register_rest_route('ca/v1', '/telegram/setup-webhook', [
            'methods'             => 'POST',
            'permission_callback' => '__return_true',
            'callback'            => [$this, 'setupWebhook'],
        ]);
    }

    public function setupWebhook(WP_REST_Request $request): WP_REST_Response
    {
        $config = $this->container->get(Config::class);
        if (!$config->isTelegramConfigured()) {
            return new WP_REST_Response([
                'ok'    => false,
                'error' => 'Telegram is not configured.',
            ], 400);
        }

        $provided = sanitize_text_field((string) ($request->get_header('X-CA-Telegram-Setup') ?? ''));
        $expected = $config->getTelegramWebhookSecret();
        if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
            return new WP_REST_Response([
                'ok'    => false,
                'error' => 'Invalid setup secret.',
            ], 403);
        }

        $url = rest_url('ca/v1/telegram/webhook');
        $ok  = $this->container->get(TelegramBotService::class)->setWebhook($url, $expected);

        return new WP_REST_Response([
            'ok'  => $ok,
            'url' => $url,
        ], $ok ? 200 : 500);
    }

    public function handleWebhook(WP_REST_Request $request): WP_REST_Response
    {
        $config = $this->container->get(Config::class);
        $secret = $config->getTelegramWebhookSecret();
        if ($secret !== '') {
            $header = sanitize_text_field((string) ($request->get_header('X-Telegram-Bot-Api-Secret-Token') ?? ''));
            if ($header === '' || !hash_equals($secret, $header)) {
                return new WP_REST_Response(['ok' => false], 403);
            }
        }

        $update = $request->get_json_params();
        if (!is_array($update)) {
            return new WP_REST_Response(['ok' => true]);
        }

        if (isset($update['callback_query']) && is_array($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);
        } elseif (isset($update['message']) && is_array($update['message'])) {
            $this->handleStaffMessage($update['message']);
        }

        return new WP_REST_Response(['ok' => true]);
    }

    /**
     * @param array<string, mixed> $callback
     */
    private function handleCallback(array $callback): void
    {
        $telegram = $this->container->get(TelegramBotService::class);
        $queryId  = (string) ($callback['id'] ?? '');
        $data     = sanitize_text_field((string) ($callback['data'] ?? ''));
        $from     = is_array($callback['from'] ?? null) ? $callback['from'] : [];
        $message  = is_array($callback['message'] ?? null) ? $callback['message'] : [];

        // Legacy inline claim buttons (pre deep-link). Prefer URL Claim chat.
        if (strpos($data, 'claim:') !== 0) {
            if ($queryId !== '') {
                $telegram->answerCallbackQuery($queryId);
            }

            return;
        }

        $conversationId = (int) substr($data, 6);
        $telegramUserId = (int) ($from['id'] ?? 0);
        $name = trim(
            sanitize_text_field((string) ($from['first_name'] ?? '')) . ' ' .
            sanitize_text_field((string) ($from['last_name'] ?? ''))
        );
        $username = sanitize_text_field((string) ($from['username'] ?? ''));

        $result = $this->claimForAgent(
            $conversationId,
            $telegramUserId,
            $name,
            $username,
            is_array($message) ? $message : []
        );

        if (is_wp_error($result)) {
            if ($queryId !== '') {
                $telegram->answerCallbackQuery($queryId, $result->get_error_message(), true);
            }

            return;
        }

        if ($queryId !== '') {
            $telegram->answerCallbackQuery($queryId, 'Claimed — check your private chat with the bot');
        }
    }

    /**
     * @param array<string, mixed> $groupMessage Optional original group alert message (legacy callback).
     * @return true|\WP_Error
     */
    private function claimForAgent(
        int $conversationId,
        int $telegramUserId,
        string $name,
        string $username,
        array $groupMessage = []
    ) {
        if ($conversationId < 1 || $telegramUserId < 1) {
            return new \WP_Error('invalid_claim', 'Invalid claim request.');
        }

        $conversations = $this->container->get(ChatConversationService::class);
        $result = $conversations->claimViaTelegram(
            $conversationId,
            [
                'id'       => $telegramUserId,
                'name'     => $name !== '' ? $name : ($username !== '' ? $username : 'Agent'),
                'username' => $username,
            ]
        );

        if (is_wp_error($result)) {
            return $result;
        }

        $full = is_array($result) ? $result : [];
        $this->openAgentDm($telegramUserId, $conversationId, $full);

        $claimer = $name !== '' ? $name : ($username !== '' ? '@' . $username : 'Agent');
        $this->markGroupAlertClaimed($conversationId, $claimer, $groupMessage, $full);

        return true;
    }

    /**
     * @param array<string, mixed> $groupMessage
     * @param array<string, mixed> $conversation
     */
    private function markGroupAlertClaimed(
        int $conversationId,
        string $claimer,
        array $groupMessage,
        array $conversation
    ): void {
        $telegram = $this->container->get(TelegramBotService::class);
        $config = $this->container->get(Config::class);
        $adminUrl = rtrim($config->getFrontendUrl(), '/') . '/admin/chat';
        $staffChat = $config->getTelegramStaffChatId();

        $messageId = (int) ($groupMessage['message_id'] ?? 0);
        $chatId = (string) ($groupMessage['chat']['id'] ?? $staffChat);
        $original = (string) ($groupMessage['text'] ?? '');

        if ($messageId < 1) {
            $meta = is_array($conversation['meta'] ?? null) ? $conversation['meta'] : [];
            if ($meta === [] && !empty($conversation['meta_json']) && is_string($conversation['meta_json'])) {
                $decoded = json_decode($conversation['meta_json'], true);
                $meta = is_array($decoded) ? $decoded : [];
            }
            $tg = is_array($meta['telegram'] ?? null) ? $meta['telegram'] : [];
            $messageId = (int) ($tg['alertMessageId'] ?? 0);
            $chatId = $staffChat;
        }

        if ($messageId < 1 || $chatId === '') {
            return;
        }

        if ($original === '' || strpos($original, 'Conversation #') === 0) {
            $meta = is_array($conversation['meta'] ?? null) ? $conversation['meta'] : [];
            if ($meta === [] && !empty($conversation['meta_json']) && is_string($conversation['meta_json'])) {
                $decoded = json_decode($conversation['meta_json'], true);
                $meta = is_array($decoded) ? $decoded : [];
            }
            $contact = is_array($meta['contact'] ?? null) ? $meta['contact'] : [];
            $customer = sanitize_text_field((string) ($contact['name'] ?? 'Customer'));
            $brand = $this->escapeHtml($config->getBrandName());
            $original = sprintf(
                "%s — Live chat request\nConversation #%d\nName: %s",
                $brand,
                $conversationId,
                $this->escapeHtml($customer)
            );
            $text = sprintf(
                "%s\n\n✅ Claimed by <b>%s</b>\nContinuing in their private chat with the bot.",
                $original,
                $this->escapeHtml($claimer)
            );
        } else {
            $text = sprintf(
                "%s\n\n✅ Claimed by <b>%s</b>\nContinuing in their private chat with the bot.",
                $this->escapeHtml($original),
                $this->escapeHtml($claimer)
            );
        }

        $telegram->editMessageText($chatId, $messageId, $text, [
            'inline_keyboard' => [[
                ['text' => 'Open portal', 'url' => $adminUrl],
            ]],
        ]);
        $this->container->get(ChatConversationService::class)
            ->rememberTelegramMessage($conversationId, $messageId);
    }

    /**
     * @param array<string, mixed> $conversation
     */
    private function openAgentDm(int $telegramUserId, int $conversationId, array $conversation): bool
    {
        if ($telegramUserId < 1) {
            return false;
        }

        $telegram = $this->container->get(TelegramBotService::class);
        $dmChatId = (string) $telegramUserId;
        $briefing = $this->buildDmBriefing($conversationId, $conversation);
        $result = $telegram->sendMessage($dmChatId, $briefing);

        return is_array($result);
    }

    /**
     * @param array<string, mixed> $conversation
     */
    private function buildDmBriefing(int $conversationId, array $conversation): string
    {
        $meta = is_array($conversation['meta'] ?? null) ? $conversation['meta'] : [];
        if ($meta === [] && !empty($conversation['meta_json']) && is_string($conversation['meta_json'])) {
            $decoded = json_decode($conversation['meta_json'], true);
            $meta = is_array($decoded) ? $decoded : [];
        }

        $contact = is_array($meta['contact'] ?? null) ? $meta['contact'] : [];
        $name    = sanitize_text_field((string) ($contact['name'] ?? 'Customer'));
        $email   = sanitize_text_field((string) ($contact['email'] ?? ''));
        $phone   = sanitize_text_field((string) ($contact['phone'] ?? ''));
        $address = sanitize_text_field((string) ($contact['address'] ?? ''));
        $adminUrl = rtrim($this->container->get(Config::class)->getFrontendUrl(), '/') . '/admin/chat';

        $lines = [
            '<b>You claimed live chat #' . (int) $conversationId . '</b>',
            '',
            '<b>Customer</b>',
            'Name: ' . $this->escapeHtml($name),
        ];
        if ($email !== '') {
            $lines[] = 'Email: ' . $this->escapeHtml($email);
        }
        if ($phone !== '') {
            $lines[] = 'Phone: ' . $this->escapeHtml($phone);
        }
        if ($address !== '') {
            $lines[] = 'Address: ' . $this->escapeHtml($address);
        }

        $messages = is_array($conversation['messages'] ?? null) ? $conversation['messages'] : [];
        $recent = array_slice($messages, -8);
        if ($recent !== []) {
            $lines[] = '';
            $lines[] = '<b>Recent messages</b>';
            foreach ($recent as $msg) {
                if (!is_array($msg)) {
                    continue;
                }
                $role = (string) ($msg['role'] ?? 'user');
                $content = trim(wp_strip_all_tags((string) ($msg['content'] ?? '')));
                if ($content === '') {
                    continue;
                }
                if (strlen($content) > 280) {
                    $content = substr($content, 0, 277) . '...';
                }
                $label = 'Customer';
                if ($role === 'agent') {
                    $label = 'You/agent';
                } elseif ($role === 'assistant' || $role === 'ai') {
                    $label = 'AI';
                } elseif ($role === 'system') {
                    $label = 'System';
                }
                $lines[] = '<b>' . $this->escapeHtml($label) . ':</b> ' . $this->escapeHtml($content);
            }
        }

        $lines[] = '';
        $lines[] = 'Reply here to message the customer.';
        $lines[] = 'Commands: /chat · /done · /ai';
        $lines[] = '<a href="' . $this->escapeHtml($adminUrl) . '">Open portal</a>';

        return implode("\n", $lines);
    }

    /**
     * @param array<string, mixed> $message
     */
    private function handleStaffMessage(array $message): void
    {
        if (!empty($message['from']['is_bot'])) {
            return;
        }

        $chat = is_array($message['chat'] ?? null) ? $message['chat'] : [];
        $chatType = (string) ($chat['type'] ?? '');
        $chatId = (string) ($chat['id'] ?? '');
        $staffChat = $this->container->get(Config::class)->getTelegramStaffChatId();

        if ($chatType === 'private') {
            $this->handlePrivateAgentMessage($message, $chatId);

            return;
        }

        // Legacy fallback: reply-to in the staff group still works.
        if ($chatId === '' || $chatId !== $staffChat) {
            return;
        }

        $text = trim(wp_strip_all_tags((string) ($message['text'] ?? '')));
        if ($text === '' || strpos($text, '/') === 0) {
            return;
        }

        $replyTo = is_array($message['reply_to_message'] ?? null) ? $message['reply_to_message'] : null;
        if ($replyTo === null) {
            return;
        }

        $replyMessageId = (int) ($replyTo['message_id'] ?? 0);
        $conversations = $this->container->get(ChatConversationService::class);
        $conversationId = $conversations->findConversationIdByTelegramMessage($replyMessageId);

        if ($conversationId < 1) {
            $haystack = (string) ($replyTo['text'] ?? '');
            if (preg_match('/Conversation #(\d+)/', $haystack, $m) === 1) {
                $conversationId = (int) $m[1];
            }
        }

        if ($conversationId < 1) {
            return;
        }

        $telegramUserId = (int) ($message['from']['id'] ?? 0);
        $stored = $conversations->appendTelegramAgentMessage($conversationId, $telegramUserId, $text);

        if (is_wp_error($stored)) {
            $this->container->get(TelegramBotService::class)->sendMessage(
                $chatId,
                '⚠️ ' . $stored->get_error_message(),
                [],
                (int) ($message['message_id'] ?? 0)
            );

            return;
        }

        $msgId = (int) ($message['message_id'] ?? 0);
        if ($msgId > 0) {
            $conversations->rememberTelegramMessage($conversationId, $msgId);
        }
    }

    /**
     * @param array<string, mixed> $message
     */
    private function handlePrivateAgentMessage(array $message, string $chatId): void
    {
        $telegram = $this->container->get(TelegramBotService::class);
        $conversations = $this->container->get(ChatConversationService::class);
        $telegramUserId = (int) ($message['from']['id'] ?? 0);
        $from = is_array($message['from'] ?? null) ? $message['from'] : [];
        $name = trim(
            sanitize_text_field((string) ($from['first_name'] ?? '')) . ' ' .
            sanitize_text_field((string) ($from['last_name'] ?? ''))
        );
        $username = sanitize_text_field((string) ($from['username'] ?? ''));
        $raw = trim((string) ($message['text'] ?? ''));
        $text = trim(wp_strip_all_tags($raw));
        $parts = preg_split('/\s+/', $text, 2) ?: [];
        $command = strtolower((string) ($parts[0] ?? ''));
        $arg = sanitize_text_field((string) ($parts[1] ?? ''));

        // Claim deep link: t.me/Bot?start=claim_8 → "/start claim_8"
        if ($command === '/start' && strpos($arg, 'claim_') === 0) {
            $conversationId = (int) substr($arg, 6);
            $result = $this->claimForAgent($conversationId, $telegramUserId, $name, $username);
            if (is_wp_error($result)) {
                $telegram->sendMessage($chatId, '⚠️ ' . $result->get_error_message());
            }

            return;
        }

        if ($command === '/start' || $command === '/chat' || $command === '/status') {
            $conversationId = $conversations->findActiveConversationForTelegramAgent($telegramUserId);
            if ($conversationId < 1) {
                $telegram->sendMessage(
                    $chatId,
                    "You're connected.\n\nWhen a live chat is waiting, tap Claim chat in the staff group — it opens here automatically.\n\nCommands: /chat · /done · /ai"
                );

                return;
            }

            $full = $conversations->getWithMessages($conversationId);
            if ($full === null) {
                $telegram->sendMessage($chatId, 'Active chat not found. Claim a new one from the staff group.');

                return;
            }

            $telegram->sendMessage($chatId, $this->buildDmBriefing($conversationId, $full));

            return;
        }

        if ($command === '/done' || $command === '/resolve' || $command === '/ai') {
            $conversationId = $conversations->findActiveConversationForTelegramAgent($telegramUserId);
            if ($conversationId < 1) {
                $telegram->sendMessage($chatId, 'No active chat. Claim one from the staff group first.');

                return;
            }

            $returnToAi = $command === '/ai';
            $result = $conversations->resolveConversation($conversationId, 0, $returnToAi);
            if (is_wp_error($result)) {
                $telegram->sendMessage($chatId, '⚠️ ' . $result->get_error_message());

                return;
            }

            $telegram->sendMessage(
                $chatId,
                $returnToAi
                    ? 'Returned chat #' . $conversationId . ' to the AI assistant.'
                    : 'Resolved chat #' . $conversationId . '. Thanks.'
            );

            return;
        }

        if ($text === '' || strpos($text, '/') === 0) {
            return;
        }

        $conversationId = $conversations->findActiveConversationForTelegramAgent($telegramUserId);
        if ($conversationId < 1) {
            $telegram->sendMessage(
                $chatId,
                'No active customer chat. Claim one from the staff group, or send /chat if you already claimed.'
            );

            return;
        }

        $stored = $conversations->appendTelegramAgentMessage($conversationId, $telegramUserId, $text);
        if (is_wp_error($stored)) {
            $telegram->sendMessage($chatId, '⚠️ ' . $stored->get_error_message());
        }
    }

    private function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
