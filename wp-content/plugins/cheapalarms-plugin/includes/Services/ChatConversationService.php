<?php

namespace CheapAlarms\Plugin\Services;

use CheapAlarms\Plugin\Services\Chat\ChatConversationRepository;
use CheapAlarms\Plugin\Services\Chat\ChatMessageRepository;
use WP_Error;

use function apply_filters;
use function current_time;
use function do_action;
use function get_option;
use function in_array;
use function json_decode;
use function preg_match;
use function sanitize_text_field;
use function sprintf;
use function update_option;

class ChatConversationService
{
    public const STATUS_OPEN            = 'open';
    public const STATUS_LEAD_CAPTURED   = 'lead_captured';
    public const STATUS_WAITING_AGENT   = 'waiting_agent';
    public const STATUS_AGENT_ACTIVE    = 'agent_active';
    public const STATUS_RESOLVED        = 'resolved';
    public const STATUS_TIMED_OUT       = 'timed_out';
    public const STATUS_QUOTE_PRICED    = 'quote_priced';
    public const STATUS_QUOTE_SUBMITTED = 'quote_submitted';

    /** Minutes a visitor waits for an agent before auto timeout. */
    public const HANDOFF_TIMEOUT_MINUTES = 10;

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
     * @return array<string, mixed>|null
     */
    public function findById(int $conversationId): ?array
    {
        return $this->conversations->findById($conversationId);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findBySessionKey(string $sessionKey): ?array
    {
        $sessionKey = $this->normalizeSessionKey($sessionKey);
        if ($sessionKey === '') {
            return null;
        }

        return $this->conversations->findBySessionKey($sessionKey);
    }

    public function isHumanHandoffActive(?array $conversation): bool
    {
        if ($conversation === null) {
            return false;
        }

        $status = (string) ($conversation['status'] ?? '');

        return in_array($status, [self::STATUS_WAITING_AGENT, self::STATUS_AGENT_ACTIVE], true);
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
            $this->conversations->update($conversationId, [
                'message_count'        => $this->messages->countForConversation($conversationId),
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
            'status'         => self::STATUS_LEAD_CAPTURED,
            'intent'         => $intent,
            'ghl_contact_id' => $contactId,
            'meta_json'      => $this->mergeMeta($conversationId, array_merge(['leadCapturedAt' => current_time('mysql')], $meta)),
        ]);

        $this->messages->append(
            $conversationId,
            'system',
            'Lead captured, callback requested.',
            ['intent' => $intent, 'contactId' => $contactId]
        );
    }

    /**
     * @param array<string, mixed> $contactSnapshot
     * @param array<string, mixed> $meta
     */
    public function markWaitingAgent(
        int $conversationId,
        string $contactId,
        array $contactSnapshot = [],
        array $meta = []
    ): bool {
        if ($conversationId < 1) {
            return false;
        }

        $existing = $this->conversations->findById($conversationId);
        if ($existing !== null && $this->isHumanHandoffActive($existing)) {
            // Idempotent: already waiting/active — refresh contact snapshot only.
            $this->conversations->update($conversationId, [
                'ghl_contact_id' => $contactId !== '' ? $contactId : (string) ($existing['ghl_contact_id'] ?? ''),
                'meta_json'      => $this->mergeMeta($conversationId, array_merge([
                    'contact' => $contactSnapshot,
                ], $meta)),
            ]);

            return false;
        }

        $this->conversations->update($conversationId, [
            'status'         => self::STATUS_WAITING_AGENT,
            'intent'         => 'agent_handoff',
            'ghl_contact_id' => $contactId,
            'claimed_by'     => 0,
            'claimed_at'     => '',
            'meta_json'      => $this->mergeMeta($conversationId, array_merge([
                'waitingAgentAt' => current_time('mysql'),
                'contact'        => $contactSnapshot,
            ], $meta)),
        ]);

        $this->messages->append(
            $conversationId,
            'system',
            'Visitor requested a live agent and submitted contact details.',
            ['contactId' => $contactId]
        );

        $this->logger->info('Chat conversation waiting for agent', [
            'conversationId' => $conversationId,
            'contactId'      => $contactId,
        ]);

        return true;
    }

    /**
     * @return array<string, mixed>|WP_Error
     */
    public function claim(int $conversationId, int $userId): array|WP_Error
    {
        if ($conversationId < 1 || $userId < 1) {
            return new WP_Error('invalid_params', __('Invalid claim request.', 'cheapalarms'), ['status' => 400]);
        }

        global $wpdb;
        $lockName = 'ca_chat_claim_' . $conversationId;
        $gotLock  = (int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 5)', $lockName));
        if ($gotLock !== 1) {
            return new WP_Error('claim_busy', __('Another agent is claiming this chat.', 'cheapalarms'), ['status' => 409]);
        }

        try {
            $this->expireWaitingConversationIfNeeded($conversationId, null, null, true);

            $conversation = $this->conversations->findById($conversationId);
            if ($conversation === null) {
                return new WP_Error('not_found', __('Conversation not found.', 'cheapalarms'), ['status' => 404]);
            }

            $status    = (string) ($conversation['status'] ?? '');
            $claimedBy = (int) ($conversation['claimed_by'] ?? 0);

            if ($status === self::STATUS_AGENT_ACTIVE && $claimedBy > 0 && $claimedBy !== $userId) {
                return new WP_Error(
                    'already_claimed',
                    __('This chat is already claimed by another agent.', 'cheapalarms'),
                    ['status' => 409]
                );
            }

            if (!in_array($status, [self::STATUS_WAITING_AGENT, self::STATUS_AGENT_ACTIVE], true)) {
                return new WP_Error(
                    'not_waiting',
                    $status === self::STATUS_TIMED_OUT
                        ? __('This chat timed out before an agent claimed it.', 'cheapalarms')
                        : __('This conversation is not waiting for an agent.', 'cheapalarms'),
                    ['status' => 400]
                );
            }

            $alreadyMine = $status === self::STATUS_AGENT_ACTIVE && $claimedBy === $userId;

            $this->conversations->update($conversationId, [
                'status'     => self::STATUS_AGENT_ACTIVE,
                'claimed_by' => $userId,
                'claimed_at' => current_time('mysql'),
                'meta_json'  => $this->mergeMeta($conversationId, [
                    'claimedBy' => $userId,
                    'claimedAt' => current_time('mysql'),
                ]),
            ]);

            if (!$alreadyMine) {
                $this->messages->append(
                    $conversationId,
                    'system',
                    'An agent joined the chat.',
                    ['claimedBy' => $userId]
                );
            }

            return $this->getWithMessages($conversationId) ?? new WP_Error(
                'not_found',
                __('Conversation not found.', 'cheapalarms'),
                ['status' => 404]
            );
        } finally {
            $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lockName));
        }
    }

    /**
     * @return array<string, mixed>|WP_Error
     */
    public function appendAgentMessage(int $conversationId, int $userId, string $content): array|WP_Error
    {
        $conversation = $this->conversations->findById($conversationId);
        if ($conversation === null) {
            return new WP_Error('not_found', __('Conversation not found.', 'cheapalarms'), ['status' => 404]);
        }

        $status    = (string) ($conversation['status'] ?? '');
        $claimedBy = (int) ($conversation['claimed_by'] ?? 0);

        if ($status !== self::STATUS_AGENT_ACTIVE) {
            return new WP_Error('not_active', __('Claim this chat before sending a reply.', 'cheapalarms'), ['status' => 400]);
        }

        if ($claimedBy > 0 && $claimedBy !== $userId) {
            return new WP_Error('forbidden', __('Only the claiming agent can reply.', 'cheapalarms'), ['status' => 403]);
        }

        $messageId = $this->messages->append($conversationId, 'agent', $content, [
            'agentUserId' => $userId,
        ]);
        if ($messageId < 1) {
            return new WP_Error('empty_message', __('Message cannot be empty.', 'cheapalarms'), ['status' => 400]);
        }

        $this->conversations->update($conversationId, [
            'message_count' => $this->messages->countForConversation($conversationId),
        ]);

        return [
            'ok'        => true,
            'messageId' => $messageId,
            'status'    => self::STATUS_AGENT_ACTIVE,
        ];
    }

    /**
     * Store a visitor message during human handoff (AI is paused).
     *
     * @return array<string, mixed>|WP_Error
     */
    public function appendVisitorMessage(int $conversationId, string $content): array|WP_Error
    {
        $conversation = $this->conversations->findById($conversationId);
        if ($conversation === null) {
            return new WP_Error('not_found', __('Conversation not found.', 'cheapalarms'), ['status' => 404]);
        }

        if (!$this->isHumanHandoffActive($conversation)) {
            return new WP_Error('not_handoff', __('Conversation is not in live-agent mode.', 'cheapalarms'), ['status' => 400]);
        }

        $messageId = $this->messages->append($conversationId, 'user', $content);
        if ($messageId < 1) {
            return new WP_Error('empty_message', __('Message cannot be empty.', 'cheapalarms'), ['status' => 400]);
        }

        $this->conversations->update($conversationId, [
            'message_count'        => $this->messages->countForConversation($conversationId),
            'last_user_message_at' => current_time('mysql'),
        ]);

        return [
            'ok'        => true,
            'messageId' => $messageId,
            'status'    => (string) ($conversation['status'] ?? ''),
            'content'   => $content,
        ];
    }

    /**
     * Claim a waiting chat from Telegram (no WordPress user required).
     *
     * @param array{id:int,name?:string,username?:string} $telegramUser
     * @return array<string, mixed>|WP_Error
     */
    public function claimViaTelegram(int $conversationId, array $telegramUser): array|WP_Error
    {
        $telegramId = (int) ($telegramUser['id'] ?? 0);
        if ($conversationId < 1 || $telegramId < 1) {
            return new WP_Error('invalid_params', __('Invalid claim request.', 'cheapalarms'), ['status' => 400]);
        }

        global $wpdb;
        $lockName = 'ca_chat_claim_' . $conversationId;
        $gotLock  = (int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 5)', $lockName));
        if ($gotLock !== 1) {
            return new WP_Error('claim_busy', __('Another agent is claiming this chat.', 'cheapalarms'), ['status' => 409]);
        }

        try {
            $this->expireWaitingConversationIfNeeded($conversationId, null, null, true);

            $conversation = $this->conversations->findById($conversationId);
            if ($conversation === null) {
                return new WP_Error('not_found', __('Conversation not found.', 'cheapalarms'), ['status' => 404]);
            }

            $status = (string) ($conversation['status'] ?? '');
            $meta   = $this->decodeMeta($conversation['meta_json'] ?? null);
            $tg     = is_array($meta['telegram'] ?? null) ? $meta['telegram'] : [];
            $existingTgClaim = (int) ($tg['claimedById'] ?? 0);
            $claimedByWp     = (int) ($conversation['claimed_by'] ?? 0);

            if ($status === self::STATUS_AGENT_ACTIVE) {
                if ($existingTgClaim > 0 && $existingTgClaim !== $telegramId) {
                    return new WP_Error(
                        'already_claimed',
                        __('This chat is already claimed by another agent.', 'cheapalarms'),
                        ['status' => 409]
                    );
                }
                if ($claimedByWp > 0 && $existingTgClaim < 1) {
                    return new WP_Error(
                        'already_claimed',
                        __('This chat is already claimed in the portal.', 'cheapalarms'),
                        ['status' => 409]
                    );
                }
            }

            if (!in_array($status, [self::STATUS_WAITING_AGENT, self::STATUS_AGENT_ACTIVE], true)) {
                return new WP_Error(
                    'not_waiting',
                    $status === self::STATUS_TIMED_OUT
                        ? __('This chat timed out before an agent claimed it.', 'cheapalarms')
                        : __('This conversation is not waiting for an agent.', 'cheapalarms'),
                    ['status' => 400]
                );
            }

            $alreadyMine = $status === self::STATUS_AGENT_ACTIVE && $existingTgClaim === $telegramId;
            $name = trim((string) ($telegramUser['name'] ?? $telegramUser['username'] ?? 'Agent'));

            $this->conversations->update($conversationId, [
                'status'     => self::STATUS_AGENT_ACTIVE,
                'claimed_by' => 0,
                'claimed_at' => current_time('mysql'),
                'meta_json'  => $this->mergeMeta($conversationId, [
                    'telegram' => array_merge($tg, [
                        'claimedById'       => $telegramId,
                        'claimedByName'     => $name,
                        'claimedByUsername' => (string) ($telegramUser['username'] ?? ''),
                        'claimedAt'         => current_time('mysql'),
                        // Private chats use the user's Telegram ID as chat_id.
                        'agentDmChatId'     => (string) $telegramId,
                    ]),
                ]),
            ]);

            if (!$alreadyMine) {
                $this->messages->append(
                    $conversationId,
                    'system',
                    sprintf('Agent %s joined via Telegram.', $name !== '' ? $name : 'staff'),
                    ['telegramUserId' => $telegramId]
                );
            }

            $this->bindTelegramAgentActiveChat($telegramId, $conversationId);

            return $this->getWithMessages($conversationId) ?? new WP_Error(
                'not_found',
                __('Conversation not found.', 'cheapalarms'),
                ['status' => 404]
            );
        } finally {
            $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lockName));
        }
    }

    /**
     * @return array<string, mixed>|WP_Error
     */
    public function appendTelegramAgentMessage(int $conversationId, int $telegramUserId, string $content): array|WP_Error
    {
        $conversation = $this->conversations->findById($conversationId);
        if ($conversation === null) {
            return new WP_Error('not_found', __('Conversation not found.', 'cheapalarms'), ['status' => 404]);
        }

        $status = (string) ($conversation['status'] ?? '');
        if ($status !== self::STATUS_AGENT_ACTIVE) {
            return new WP_Error('not_active', __('Claim this chat before sending a reply.', 'cheapalarms'), ['status' => 400]);
        }

        $meta = $this->decodeMeta($conversation['meta_json'] ?? null);
        $tg   = is_array($meta['telegram'] ?? null) ? $meta['telegram'] : [];
        $claimedBy = (int) ($tg['claimedById'] ?? 0);
        if ($claimedBy > 0 && $claimedBy !== $telegramUserId) {
            return new WP_Error('forbidden', __('Only the claiming agent can reply.', 'cheapalarms'), ['status' => 403]);
        }

        $messageId = $this->messages->append($conversationId, 'agent', $content, [
            'telegramUserId' => $telegramUserId,
            'source'         => 'telegram',
        ]);
        if ($messageId < 1) {
            return new WP_Error('empty_message', __('Message cannot be empty.', 'cheapalarms'), ['status' => 400]);
        }

        $this->conversations->update($conversationId, [
            'message_count' => $this->messages->countForConversation($conversationId),
        ]);

        return [
            'ok'        => true,
            'messageId' => $messageId,
            'status'    => self::STATUS_AGENT_ACTIVE,
        ];
    }

    public function rememberTelegramMessage(int $conversationId, int $telegramMessageId): void
    {
        if ($conversationId < 1 || $telegramMessageId < 1) {
            return;
        }

        $conversation = $this->conversations->findById($conversationId);
        $meta = $conversation ? $this->decodeMeta($conversation['meta_json'] ?? null) : [];
        $tg   = is_array($meta['telegram'] ?? null) ? $meta['telegram'] : [];
        $ids  = is_array($tg['messageIds'] ?? null) ? $tg['messageIds'] : [];
        $ids[] = $telegramMessageId;
        $ids = array_values(array_unique(array_map('intval', $ids)));

        $this->conversations->update($conversationId, [
            'meta_json' => $this->mergeMeta($conversationId, [
                'telegram' => array_merge($tg, [
                    'alertMessageId' => (int) ($tg['alertMessageId'] ?? $telegramMessageId),
                    'messageIds'     => $ids,
                ]),
            ]),
        ]);

        $map = get_option('ca_telegram_message_map', []);
        if (!is_array($map)) {
            $map = [];
        }
        $map[(string) $telegramMessageId] = $conversationId;
        update_option('ca_telegram_message_map', $map, false);
    }

    public function findConversationIdByTelegramMessage(int $telegramMessageId): int
    {
        if ($telegramMessageId < 1) {
            return 0;
        }

        $map = get_option('ca_telegram_message_map', []);
        if (is_array($map) && isset($map[(string) $telegramMessageId])) {
            return (int) $map[(string) $telegramMessageId];
        }

        return 0;
    }

    public function bindTelegramAgentActiveChat(int $telegramUserId, int $conversationId): void
    {
        if ($telegramUserId < 1 || $conversationId < 1) {
            return;
        }

        $map = get_option('ca_telegram_agent_active', []);
        if (!is_array($map)) {
            $map = [];
        }
        $map[(string) $telegramUserId] = $conversationId;
        update_option('ca_telegram_agent_active', $map, false);
    }

    public function findActiveConversationForTelegramAgent(int $telegramUserId): int
    {
        if ($telegramUserId < 1) {
            return 0;
        }

        $map = get_option('ca_telegram_agent_active', []);
        if (!is_array($map) || !isset($map[(string) $telegramUserId])) {
            return 0;
        }

        $conversationId = (int) $map[(string) $telegramUserId];
        if ($conversationId < 1) {
            return 0;
        }

        $conversation = $this->conversations->findById($conversationId);
        if ($conversation === null) {
            return 0;
        }

        $status = (string) ($conversation['status'] ?? '');
        if ($status !== self::STATUS_AGENT_ACTIVE) {
            return 0;
        }

        $meta = $this->decodeMeta($conversation['meta_json'] ?? null);
        $tg   = is_array($meta['telegram'] ?? null) ? $meta['telegram'] : [];
        if ((int) ($tg['claimedById'] ?? 0) !== $telegramUserId) {
            return 0;
        }

        return $conversationId;
    }

    public function clearTelegramAgentActiveChat(int $telegramUserId): void
    {
        if ($telegramUserId < 1) {
            return;
        }

        $map = get_option('ca_telegram_agent_active', []);
        if (!is_array($map) || !isset($map[(string) $telegramUserId])) {
            return;
        }

        unset($map[(string) $telegramUserId]);
        update_option('ca_telegram_agent_active', $map, false);
    }

    /**
     * @return array<string, mixed>|WP_Error
     */
    public function resolveConversation(int $conversationId, int $userId, bool $returnToAi = false): array|WP_Error
    {
        $conversation = $this->conversations->findById($conversationId);
        if ($conversation === null) {
            return new WP_Error('not_found', __('Conversation not found.', 'cheapalarms'), ['status' => 404]);
        }

        $meta = $this->decodeMeta($conversation['meta_json'] ?? null);
        $tg   = is_array($meta['telegram'] ?? null) ? $meta['telegram'] : [];
        $tgUserId = (int) ($tg['claimedById'] ?? 0);

        $newStatus = $returnToAi ? self::STATUS_OPEN : self::STATUS_RESOLVED;

        $this->conversations->update($conversationId, [
            'status'     => $newStatus,
            'claimed_by' => 0,
            'claimed_at' => '',
            'meta_json'  => $this->mergeMeta($conversationId, [
                'resolvedAt'   => current_time('mysql'),
                'resolvedBy'   => $userId,
                'returnedToAi' => $returnToAi,
            ]),
        ]);

        $this->messages->append(
            $conversationId,
            'system',
            $returnToAi ? 'Live chat ended. Assistant can help again.' : 'Live chat resolved by an agent.',
            ['resolvedBy' => $userId, 'returnToAi' => $returnToAi]
        );

        if ($tgUserId > 0) {
            $this->clearTelegramAgentActiveChat($tgUserId);
        }

        return [
            'ok'     => true,
            'status' => $newStatus,
        ];
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function listRecent(int $limit = 50, int $offset = 0, ?string $status = null): array
    {
        return $this->conversations->listRecent($limit, $offset, $status);
    }

    /**
     * @param array<int, string>|null $statuses
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function listQueue(?array $statuses = null, int $limit = 50, int $offset = 0): array
    {
        $statuses = $statuses ?? [self::STATUS_WAITING_AGENT, self::STATUS_AGENT_ACTIVE];

        return $this->conversations->listByStatuses($statuses, $limit, $offset);
    }

    /**
     * Closed handoffs kept for staff reference (resolved + timed out).
     *
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function listHistory(int $limit = 50, int $offset = 0): array
    {
        return $this->conversations->listByStatuses(
            [self::STATUS_RESOLVED, self::STATUS_TIMED_OUT],
            $limit,
            $offset
        );
    }

    public function getHandoffTimeoutMinutes(): int
    {
        $minutes = (int) apply_filters('ca_chat_handoff_timeout_minutes', self::HANDOFF_TIMEOUT_MINUTES);

        return max(1, min(120, $minutes > 0 ? $minutes : self::HANDOFF_TIMEOUT_MINUTES));
    }

    /**
     * Expire waiting chats with no agent claim within the timeout window.
     *
     * @return array<int, int> Timed-out conversation IDs
     */
    public function timeoutExpiredWaitingAgents(?int $minutes = null): array
    {
        $minutes = $minutes ?? $this->getHandoffTimeoutMinutes();
        $result  = $this->conversations->listByStatuses([self::STATUS_WAITING_AGENT], 100, 0);
        $expired = [];

        foreach ($result['items'] as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id < 1) {
                continue;
            }
            if ($this->expireWaitingConversationIfNeeded($id, $minutes, $row)) {
                $expired[] = $id;
            }
        }

        return $expired;
    }

    /**
     * @param array<string, mixed>|null $conversation Preloaded row (may be stale; re-fetched under lock)
     * @param bool                      $alreadyLocked True when caller already holds ca_chat_claim_{id}
     */
    public function expireWaitingConversationIfNeeded(
        int $conversationId,
        ?int $minutes = null,
        ?array $conversation = null,
        bool $alreadyLocked = false
    ): bool {
        if ($conversationId < 1) {
            return false;
        }

        $conversation ??= $this->conversations->findById($conversationId);
        if ($conversation === null) {
            return false;
        }

        if ((string) ($conversation['status'] ?? '') !== self::STATUS_WAITING_AGENT) {
            return false;
        }

        $minutes = $minutes ?? $this->getHandoffTimeoutMinutes();
        $meta    = $this->decodeMeta($conversation['meta_json'] ?? null);
        $waitingAt = (string) ($meta['waitingAgentAt'] ?? $conversation['updated_at'] ?? $conversation['created_at'] ?? '');
        if ($waitingAt === '' || !$this->isTimestampOlderThanMinutes($waitingAt, $minutes)) {
            return false;
        }

        global $wpdb;
        $lockName = 'ca_chat_claim_' . $conversationId;
        $gotLock  = false;

        if (!$alreadyLocked) {
            $gotLock = (int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 5)', $lockName)) === 1;
            if (!$gotLock) {
                return false;
            }
        }

        try {
            // Re-check under lock so we never overwrite an in-flight claim.
            $fresh = $this->conversations->findById($conversationId);
            if ($fresh === null) {
                return false;
            }
            if ((string) ($fresh['status'] ?? '') !== self::STATUS_WAITING_AGENT) {
                return false;
            }

            $freshMeta = $this->decodeMeta($fresh['meta_json'] ?? null);
            $waitingAt = (string) ($freshMeta['waitingAgentAt'] ?? $fresh['updated_at'] ?? $fresh['created_at'] ?? '');
            if ($waitingAt === '' || !$this->isTimestampOlderThanMinutes($waitingAt, $minutes)) {
                return false;
            }

            $this->conversations->update($conversationId, [
                'status'     => self::STATUS_TIMED_OUT,
                'claimed_by' => 0,
                'claimed_at' => '',
                'meta_json'  => $this->mergeMeta($conversationId, [
                    'timedOutAt'     => current_time('mysql'),
                    'timedOutReason' => 'no_agent_response',
                ]),
            ]);

            $this->messages->append(
                $conversationId,
                'system',
                'No agents were available right now. We have your details and will contact you soon. You can keep browsing or message us anytime.',
                ['timedOut' => true]
            );

            $this->logger->info('Chat handoff timed out waiting for agent', [
                'conversationId' => $conversationId,
                'minutes'        => $minutes,
            ]);

            /**
             * Fires after a waiting chat is marked timed_out (e.g. update Telegram alert).
             *
             * @param int $conversationId
             */
            do_action('ca_chat_conversation_timed_out', $conversationId);

            return true;
        } finally {
            if ($gotLock) {
                $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lockName));
            }
        }
    }

    private function isTimestampOlderThanMinutes(string $mysqlDatetime, int $minutes): bool
    {
        try {
            $tz = function_exists('wp_timezone') ? wp_timezone() : new \DateTimeZone('UTC');
            $then = new \DateTimeImmutable($mysqlDatetime, $tz);
            $cutoff = (new \DateTimeImmutable('now', $tz))->modify('-' . $minutes . ' minutes');

            return $then <= $cutoff;
        } catch (\Throwable $e) {
            $ts = strtotime($mysqlDatetime);

            return $ts !== false && $ts <= (time() - ($minutes * 60));
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getWithMessages(int $conversationId): ?array
    {
        $this->expireWaitingConversationIfNeeded($conversationId);

        $conversation = $this->conversations->findById($conversationId);
        if ($conversation === null) {
            return null;
        }

        $conversation['messages'] = $this->messages->listForConversation($conversationId);
        $conversation['meta']     = $this->decodeMeta($conversation['meta_json'] ?? null);

        return $conversation;
    }

    /**
     * @return array{status: string, messages: array<int, array<string, mixed>>, claimedBy: int|null}
     */
    public function pollMessages(int $conversationId, int $afterId = 0): array
    {
        $this->expireWaitingConversationIfNeeded($conversationId);

        $conversation = $this->conversations->findById($conversationId);
        if ($conversation === null) {
            return [
                'status'    => '',
                'messages'  => [],
                'claimedBy' => null,
            ];
        }

        $rows = $this->messages->listSince($conversationId, $afterId);
        $out  = [];
        foreach ($rows as $row) {
            $role = (string) ($row['role'] ?? '');
            if (!in_array($role, ['agent', 'system', 'user'], true)) {
                continue;
            }
            $out[] = [
                'id'        => (int) ($row['id'] ?? 0),
                'role'      => $role,
                'content'   => (string) ($row['content'] ?? ''),
                'createdAt' => (string) ($row['created_at'] ?? ''),
            ];
        }

        return [
            'status'    => (string) ($conversation['status'] ?? ''),
            'messages'  => $out,
            'claimedBy' => isset($conversation['claimed_by']) && $conversation['claimed_by'] !== null
                ? (int) $conversation['claimed_by']
                : null,
        ];
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
            'status'      => self::STATUS_QUOTE_PRICED,
            'quote_total' => $total,
            'meta_json'   => $this->mergeMeta($conversationId, array_merge(['quotePricedAt' => current_time('mysql')], $meta)),
        ]);
    }

    public function markQuoteSubmitted(int $conversationId, string $estimateId, string $contactId = ''): void
    {
        if ($conversationId < 1) {
            return;
        }

        $fields = [
            'status'      => self::STATUS_QUOTE_SUBMITTED,
            'estimate_id' => $estimateId,
            'meta_json'   => $this->mergeMeta($conversationId, ['quoteSubmittedAt' => current_time('mysql')]),
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

    /**
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    private function mergeMeta(int $conversationId, array $extra): array
    {
        $row      = $this->conversations->findById($conversationId);
        $existing = $this->decodeMeta($row['meta_json'] ?? null);

        return array_merge($existing, $extra);
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
}
