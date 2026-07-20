<?php

namespace CheapAlarms\Plugin\Services\Chat;

use function current_time;
use function sanitize_text_field;
use function wp_json_encode;
use function wp_strip_all_tags;

class ChatMessageRepository
{
    private function table(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'ca_messages';
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function append(int $conversationId, string $role, string $content, array $meta = []): int
    {
        global $wpdb;

        if ($conversationId < 1) {
            return 0;
        }

        $role = sanitize_text_field($role);
        if (!in_array($role, ['user', 'assistant', 'system'], true)) {
            $role = 'system';
        }

        $content = wp_strip_all_tags($content);
        $content = trim(preg_replace('/\s+/u', ' ', $content) ?? '');
        if ($content === '') {
            return 0;
        }

        if (mb_strlen($content) > 4000) {
            $content = mb_substr($content, 0, 3997) . '…';
        }

        $wpdb->insert(
            $this->table(),
            [
                'conversation_id' => $conversationId,
                'role'            => $role,
                'content'         => $content,
                'created_at'      => current_time('mysql'),
                'meta_json'       => $meta !== [] ? wp_json_encode($meta) : null,
            ],
            ['%d', '%s', '%s', '%s', '%s']
        );

        return (int) $wpdb->insert_id;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listForConversation(int $conversationId, int $limit = 100): array
    {
        global $wpdb;

        if ($conversationId < 1) {
            return [];
        }

        $limit = max(1, min(500, $limit));
        $rows  = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM ' . $this->table() . ' WHERE conversation_id = %d ORDER BY id ASC LIMIT %d',
                $conversationId,
                $limit
            ),
            ARRAY_A
        );

        return is_array($rows) ? $rows : [];
    }

    public function countForConversation(int $conversationId): int
    {
        global $wpdb;

        if ($conversationId < 1) {
            return 0;
        }

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(*) FROM ' . $this->table() . ' WHERE conversation_id = %d',
                $conversationId
            )
        );
    }
}
