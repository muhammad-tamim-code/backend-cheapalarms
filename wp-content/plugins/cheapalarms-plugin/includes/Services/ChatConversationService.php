<?php

namespace CheapAlarms\Plugin\Services;

use CheapAlarms\Plugin\Services\Chat\ChatConversationRepository;
use CheapAlarms\Plugin\Services\Chat\ChatMessageRepository;
use WP_Error;

use function sanitize_text_field;
use function preg_match;
use function current_time;

class ChatConversationService
{
    public function __construct(
        private ChatConversationRepository $conversations,
        private ChatMessageRepository $messages,
        private Logger $logger
    ) {
    }

    /**
     * @param array<string, mixed> $pageContext
     * @return array{id: int, sessionKey: string, isNew: bool}|WP_Error
     */
    public function resolve(string $sessionKey, array $pageContext = []): array|WP_Error
    {
        $sessionKey = $this->normalizeSessionKey($sessionKey);
        if ($sessionKey === '') {
            return new WP_Error('invalid_session', __('Invalid conversation session.', 'cheapalarms'), ['status' => 400]);
        }

        $existing = $this->conversations->findBySessionKey($sessionKey);
        if ($existing !== null) {
            $this->maybeUpdatePageContext((int) $existing['id'], $pageContext);

            return [
                'id'         => (int) $existing['id'],
                'sessionKey' => $sessionKey,
                'isNew'      => false,
            ];
        }

        $row = $this->conversations->create($sessionKey, $pageContext);

        return [
            'id'         => (int) ($row['id'] ?? 0),
            'sessionKey' => $sessionKey,
            'isNew'      => true,
        ];
    }

    /**
     * @param array<int, array{role: string, content: string}> $messages
     */
    public function syncTranscript(int $conversationId, array $messages): void
    {
        if ($conversationId < 1 || $messages === []) {
            return;
        }

        $existingCount = $this->messages->countForConversation($conversationId);
        $toAppend      = array_slice($messages, $existingCount);

        foreach ($toAppend as $entry) {
            $this->messages->append($conversationId, $entry['role'], $entry['content']);
        }

        if ($toAppend !== []) {
            $lastUser = null;
            foreach (array_reverse($messages) as $entry) {
                if ($entry['role'] === 'user') {
                    $lastUser = $entry['content'];
                    break;
                }
            }

            $this->conversations->update($conversationId, [
                'message_count'        => count($messages),
                'last_user_message_at' => current_time('mysql'),
            ]);
        }
    }

    public function logExchange(int $conversationId, string $userMessage, string $assistantReply, array $meta = []): void
    {
        if ($conversationId < 1) {
            return;
        }

        $this->messages->append($conversationId, 'user', $userMessage);
        $this->messages->append($conversationId, 'assistant', $assistantReply, $meta);

        $count = $this->messages->countForConversation($conversationId);
        $this->conversations->update($conversationId, [
            'message_count'        => $count,
            'last_user_message_at' => current_time('mysql'),
        ]);
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function markLeadCaptured(int $conversationId, string $contactId, string $intent = '', array $meta = []): void
    {
        if ($conversationId < 1) {
            return;
        }

        $this->conversations->update($conversationId, [
            'status'         => 'lead_captured',
            'intent'         => $intent,
            'ghl_contact_id' => $contactId,
            'meta_json'      => array_merge(['leadCapturedAt' => current_time('mysql')], $meta),
        ]);

        $this->messages->append(
            $conversationId,
            'system',
            'Lead captured, callback requested.',
            ['intent' => $intent, 'contactId' => $contactId]
        );
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function markQuotePriced(int $conversationId, ?float $total, array $meta = []): void
    {
        if ($conversationId < 1) {
            return;
        }

        $this->conversations->update($conversationId, [
            'status'      => 'quote_priced',
            'quote_total' => $total,
            'meta_json'   => array_merge(['quotePricedAt' => current_time('mysql')], $meta),
        ]);
    }

    public function markQuoteSubmitted(int $conversationId, string $estimateId, string $contactId = ''): void
    {
        if ($conversationId < 1) {
            return;
        }

        $fields = [
            'status'      => 'quote_submitted',
            'estimate_id' => $estimateId,
            'meta_json'   => ['quoteSubmittedAt' => current_time('mysql')],
        ];

        if ($contactId !== '') {
            $fields['ghl_contact_id'] = $contactId;
        }

        $this->conversations->update($conversationId, $fields);

        $this->messages->append(
            $conversationId,
            'system',
            'Formal quote submitted, portal invite sent.',
            ['estimateId' => $estimateId, 'contactId' => $contactId]
        );
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function listRecent(int $limit = 50, int $offset = 0, ?string $status = null): array
    {
        return $this->conversations->listRecent($limit, $offset, $status);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getWithMessages(int $conversationId): ?array
    {
        $conversation = $this->conversations->findById($conversationId);
        if ($conversation === null) {
            return null;
        }

        $conversation['messages'] = $this->messages->listForConversation($conversationId);

        return $conversation;
    }

    private function normalizeSessionKey(string $raw): string
    {
        $key = sanitize_text_field($raw);
        if ($key === '') {
            return '';
        }

        if (preg_match('/^[a-zA-Z0-9\-_]{8,64}$/', $key) !== 1) {
            return '';
        }

        return $key;
    }

    /**
     * @param array<string, mixed> $pageContext
     */
    private function maybeUpdatePageContext(int $conversationId, array $pageContext): void
    {
        if ($conversationId < 1 || $pageContext === []) {
            return;
        }

        $updates = [];
        if (!empty($pageContext['path'])) {
            $updates['page_path'] = (string) $pageContext['path'];
        }
        if (!empty($pageContext['service'])) {
            $updates['page_service'] = (string) $pageContext['service'];
        }
        if (!empty($pageContext['title'])) {
            $updates['page_title'] = (string) $pageContext['title'];
        }

        if ($updates !== []) {
            $this->conversations->update($conversationId, $updates);
        }
    }
}
