<?php

namespace CheapAlarms\Plugin\Services;

use CheapAlarms\Plugin\Config\Config;

use function current_time;
use function esc_html;
use function esc_url;
use function get_option;
use function is_array;
use function json_decode;
use function sanitize_text_field;
use function sprintf;
use function wp_mail;

/**
 * Notifies staff when a website chat visitor requests a live agent.
 * Email + optional Telegram staff group alert.
 */
class ChatHandoffNotifier
{
    public function __construct(
        private Config $config,
        private Logger $logger,
        private ?TelegramBotService $telegram = null,
        private ?ChatConversationService $conversations = null
    ) {
    }

    /**
     * @param array<string, mixed> $contact
     */
    public function notifyWaitingAgent(int $conversationId, array $contact): void
    {
        $this->notifyEmail($conversationId, $contact);
        $this->notifyTelegram($conversationId, $contact);
    }

    /**
     * Forward a visitor message to the claiming agent's private DM,
     * or to the staff group thread while still waiting to be claimed.
     */
    public function notifyVisitorTelegramMessage(int $conversationId, string $content): void
    {
        if ($this->telegram === null || !$this->telegram->isConfigured() || $this->conversations === null) {
            return;
        }

        $conversation = $this->conversations->findById($conversationId);
        if ($conversation === null) {
            return;
        }

        $meta = [];
        if (!empty($conversation['meta_json']) && is_string($conversation['meta_json'])) {
            $decoded = json_decode($conversation['meta_json'], true);
            $meta = is_array($decoded) ? $decoded : [];
        } elseif (is_array($conversation['meta'] ?? null)) {
            $meta = $conversation['meta'];
        }

        $tg = is_array($meta['telegram'] ?? null) ? $meta['telegram'] : [];
        $agentDm = sanitize_text_field((string) ($tg['agentDmChatId'] ?? ''));
        if ($agentDm === '' && !empty($tg['claimedById'])) {
            $agentDm = (string) (int) $tg['claimedById'];
        }

        $contact = is_array($meta['contact'] ?? null) ? $meta['contact'] : [];
        $customerName = sanitize_text_field((string) ($contact['name'] ?? ''));
        if ($customerName === '') {
            $customerName = 'Customer';
        }

        $text = sprintf(
            "<b>%s</b>\n%s",
            esc_html($customerName),
            esc_html(sanitize_text_field($content))
        );

        // Claimed chats → private DM with the agent (no group swipe-reply needed).
        if ($agentDm !== '' && (string) ($conversation['status'] ?? '') === ChatConversationService::STATUS_AGENT_ACTIVE) {
            $this->telegram->sendMessage($agentDm, $text);

            return;
        }

        // Still waiting → keep group thread updated so staff see urgency before claim.
        $replyTo = (int) ($tg['alertMessageId'] ?? 0);
        $chatId = $this->config->getTelegramStaffChatId();
        $result = $this->telegram->sendMessage(
            $chatId,
            $text,
            [],
            $replyTo > 0 ? $replyTo : null
        );

        if (is_array($result) && !empty($result['message_id'])) {
            $this->conversations->rememberTelegramMessage($conversationId, (int) $result['message_id']);
        }
    }

    /**
     * Remove Claim from the staff-group alert after a waiting chat times out.
     */
    public function markAlertTimedOut(int $conversationId): void
    {
        if ($this->telegram === null || !$this->telegram->isConfigured() || $this->conversations === null) {
            return;
        }

        $conversation = $this->conversations->findById($conversationId);
        if ($conversation === null) {
            return;
        }

        $meta = [];
        if (!empty($conversation['meta_json']) && is_string($conversation['meta_json'])) {
            $decoded = json_decode($conversation['meta_json'], true);
            $meta = is_array($decoded) ? $decoded : [];
        }

        $tg = is_array($meta['telegram'] ?? null) ? $meta['telegram'] : [];
        $messageId = (int) ($tg['alertMessageId'] ?? 0);
        if ($messageId < 1) {
            return;
        }

        $contact = is_array($meta['contact'] ?? null) ? $meta['contact'] : [];
        $name = sanitize_text_field((string) ($contact['name'] ?? 'Customer'));
        $brand = $this->config->getBrandName();
        $adminUrl = rtrim($this->config->getFrontendUrl(), '/') . '/admin/chat';

        $text = sprintf(
            "<b>%s — Live chat request</b>\nConversation #%d\nName: %s\n\n⏱ <b>Timed out</b> — no agent claimed in time.\nCustomer was told we will follow up.",
            esc_html($brand),
            $conversationId,
            esc_html($name)
        );

        try {
            $this->telegram->editMessageText(
                $this->config->getTelegramStaffChatId(),
                $messageId,
                $text,
                [
                    'inline_keyboard' => [[
                        ['text' => 'Open portal', 'url' => $adminUrl],
                    ]],
                ]
            );
        } catch (\Throwable $e) {
            $this->logger->error('Failed to mark Telegram alert timed out', [
                'conversationId' => $conversationId,
                'error'          => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $contact
     */
    private function notifyEmail(int $conversationId, array $contact): void
    {
        try {
            $adminEmail = get_option('admin_email');
            if (!is_string($adminEmail) || $adminEmail === '') {
                $this->logger->warning('No admin email for chat handoff notification', [
                    'conversationId' => $conversationId,
                ]);

                return;
            }

            $name    = sanitize_text_field((string) ($contact['name'] ?? 'Visitor'));
            $email   = sanitize_text_field((string) ($contact['email'] ?? ''));
            $phone   = sanitize_text_field((string) ($contact['phone'] ?? ''));
            $address = sanitize_text_field((string) ($contact['address'] ?? ''));

            $adminUrl = rtrim($this->config->getFrontendUrl(), '/') . '/admin/chat';
            $brand    = $this->config->getBrandName();

            $subject = sprintf('[%s] Live chat handoff request #%d', $brand, $conversationId);
            $headers = ['Content-Type: text/html; charset=UTF-8'];

            $body  = '<h2>Live chat handoff</h2>';
            $body .= '<p>A website visitor asked to speak with a real person.</p>';
            $body .= '<p><strong>Name:</strong> ' . esc_html($name) . '</p>';
            if ($email !== '') {
                $body .= '<p><strong>Email:</strong> ' . esc_html($email) . '</p>';
            }
            if ($phone !== '') {
                $body .= '<p><strong>Phone:</strong> ' . esc_html($phone) . '</p>';
            }
            if ($address !== '') {
                $body .= '<p><strong>Address:</strong> ' . esc_html($address) . '</p>';
            }
            $body .= '<p><strong>Conversation:</strong> #' . (int) $conversationId . '</p>';
            $body .= '<p><strong>Requested:</strong> ' . esc_html(current_time('F j, Y g:i A')) . '</p>';
            $body .= '<p><a href="' . esc_url($adminUrl) . '">Open live chat inbox</a></p>';
            $body .= '<p style="color:#666;font-size:12px;">Automated notification from ' . esc_html($brand) . '.</p>';

            $sent = wp_mail($adminEmail, $subject, $body, $headers);
            if ($sent) {
                $this->logger->info('Chat handoff notification sent', [
                    'conversationId' => $conversationId,
                    'adminEmail'     => $adminEmail,
                ]);
            } else {
                $this->logger->warning('Chat handoff notification failed to send', [
                    'conversationId' => $conversationId,
                    'adminEmail'     => $adminEmail,
                ]);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Exception sending chat handoff notification', [
                'conversationId' => $conversationId,
                'error'          => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $contact
     */
    private function notifyTelegram(int $conversationId, array $contact): void
    {
        if ($this->telegram === null || !$this->telegram->isConfigured()) {
            return;
        }

        try {
            $name    = sanitize_text_field((string) ($contact['name'] ?? 'Visitor'));
            $email   = sanitize_text_field((string) ($contact['email'] ?? ''));
            $phone   = sanitize_text_field((string) ($contact['phone'] ?? ''));
            $address = sanitize_text_field((string) ($contact['address'] ?? ''));
            $adminUrl = rtrim($this->config->getFrontendUrl(), '/') . '/admin/chat';
            $brand    = $this->config->getBrandName();

            $lines = [
                '<b>' . esc_html($brand) . ' — Live chat request</b>',
                'Conversation #' . (int) $conversationId,
                '',
                '<b>Name:</b> ' . esc_html($name),
            ];
            if ($email !== '') {
                $lines[] = '<b>Email:</b> ' . esc_html($email);
            }
            if ($phone !== '') {
                $lines[] = '<b>Phone:</b> ' . esc_html($phone);
            }
            if ($address !== '') {
                $lines[] = '<b>Address:</b> ' . esc_html($address);
            }
            $lines[] = '';
            $lines[] = 'Tap <b>Claim chat</b> to take this conversation in your private chat with the bot.';

            $claimLink = $this->telegram->getPrivateChatDeepLink('claim_' . (int) $conversationId);
            $keyboard = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => 'Claim chat',
                            'url'  => $claimLink,
                        ],
                        [
                            'text' => 'Open portal',
                            'url'  => $adminUrl,
                        ],
                    ],
                ],
            ];

            $result = $this->telegram->sendMessage(
                $this->config->getTelegramStaffChatId(),
                implode("\n", $lines),
                $keyboard
            );

            if (is_array($result) && !empty($result['message_id']) && $this->conversations !== null) {
                $this->conversations->rememberTelegramMessage($conversationId, (int) $result['message_id']);
                $this->logger->info('Telegram handoff alert sent', [
                    'conversationId' => $conversationId,
                    'messageId'      => (int) $result['message_id'],
                ]);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Exception sending Telegram handoff alert', [
                'conversationId' => $conversationId,
                'error'          => $e->getMessage(),
            ]);
        }
    }
}
