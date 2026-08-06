<?php

namespace CheapAlarms\Plugin\Services;

use CheapAlarms\Plugin\Config\Config;

use function is_array;
use function is_wp_error;
use function sanitize_text_field;
use function wp_json_encode;
use function wp_remote_post;
use function wp_remote_retrieve_body;
use function wp_remote_retrieve_response_code;
use function get_transient;
use function set_transient;
use function preg_replace;

/**
 * Minimal Telegram Bot API client for live-chat staff alerts/replies.
 */
class TelegramBotService
{
    private const API_BASE = 'https://api.telegram.org/bot';

    public function __construct(
        private Config $config,
        private Logger $logger
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->config->isTelegramConfigured();
    }

    /**
     * @param array<string, mixed> $replyMarkup
     * @return array<string, mixed>|null
     */
    public function sendMessage(string $chatId, string $text, array $replyMarkup = [], ?int $replyToMessageId = null): ?array
    {
        $payload = [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ];

        if ($replyMarkup !== []) {
            $payload['reply_markup'] = wp_json_encode($replyMarkup);
        }

        if ($replyToMessageId !== null && $replyToMessageId > 0) {
            $payload['reply_to_message_id'] = $replyToMessageId;
        }

        return $this->request('sendMessage', $payload);
    }

    /**
     * @param array<string, mixed> $replyMarkup
     * @return array<string, mixed>|null
     */
    public function editMessageText(string $chatId, int $messageId, string $text, array $replyMarkup = []): ?array
    {
        $payload = [
            'chat_id'    => $chatId,
            'message_id' => $messageId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ];

        if ($replyMarkup !== []) {
            $payload['reply_markup'] = wp_json_encode($replyMarkup);
        }

        return $this->request('editMessageText', $payload);
    }

    public function answerCallbackQuery(string $callbackQueryId, string $text = '', bool $showAlert = false): bool
    {
        $result = $this->request('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text'              => $text,
            'show_alert'        => $showAlert,
        ]);

        return is_array($result);
    }

    public function setWebhook(string $url, string $secretToken = ''): bool
    {
        $payload = [
            'url'             => $url,
            'allowed_updates' => wp_json_encode(['message', 'callback_query']),
            'drop_pending_updates' => true,
        ];

        if ($secretToken !== '') {
            $payload['secret_token'] = $secretToken;
        }

        $result = $this->request('setWebhook', $payload);

        return is_array($result);
    }

    /**
     * Bot username without @ (cached in a transient).
     */
    public function getBotUsername(): string
    {
        $cached = get_transient('ca_telegram_bot_username');
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $result = $this->request('getMe', []);
        $username = sanitize_text_field((string) ($result['username'] ?? ''));
        if ($username !== '') {
            set_transient('ca_telegram_bot_username', $username, DAY_IN_SECONDS);
        }

        return $username;
    }

    public function getPrivateChatDeepLink(string $startPayload = 'chat'): string
    {
        $username = $this->getBotUsername();
        if ($username === '') {
            $username = 'SafeguardLiveChatBot';
        }

        $payload = preg_replace('/[^a-zA-Z0-9_-]/', '', $startPayload) ?: 'chat';

        return 'https://t.me/' . $username . '?start=' . $payload;
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>|null
     */
    private function request(string $method, array $params): ?array
    {
        $token = $this->config->getTelegramBotToken();
        if ($token === '') {
            return null;
        }

        $url = self::API_BASE . $token . '/' . $method;

        // getMe is GET-friendly but Telegram accepts POST with empty body too.
        $response = wp_remote_post($url, [
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($params === [] ? new \stdClass() : $params),
        ]);

        if (is_wp_error($response)) {
            $this->logger->error('Telegram API request failed', [
                'method' => $method,
                'error'  => $response->get_error_message(),
            ]);

            return null;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = (string) wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($code < 200 || $code >= 300 || !is_array($data) || empty($data['ok'])) {
            $this->logger->error('Telegram API error', [
                'method' => $method,
                'status' => $code,
                'body'   => sanitize_text_field(substr($body, 0, 500)),
            ]);

            return null;
        }

        return is_array($data['result'] ?? null) ? $data['result'] : ['ok' => true];
    }
}
