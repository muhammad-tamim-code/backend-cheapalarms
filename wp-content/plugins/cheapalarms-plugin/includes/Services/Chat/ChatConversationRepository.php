<?php

namespace CheapAlarms\Plugin\Services\Chat;

use function current_time;
use function sanitize_text_field;
use function wp_json_encode;

class ChatConversationRepository
{
    private function table(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'ca_conversations';
    }

    /**
     * @param array<string, mixed> $pageContext
     * @return array<string, mixed>|null
     */
    public function findBySessionKey(string $sessionKey): ?array
    {
        global $wpdb;

        $sessionKey = sanitize_text_field($sessionKey);
        if ($sessionKey === '') {
            return null;
        }

        $row = $wpdb->get_row(
            $wpdb->prepare('SELECT * FROM ' . $this->table() . ' WHERE session_key = %s LIMIT 1', $sessionKey),
            ARRAY_A
        );

        return is_array($row) ? $row : null;
    }

    /**
     * @param array<string, mixed> $pageContext
     * @return array<string, mixed>
     */
    public function create(string $sessionKey, array $pageContext = []): array
    {
        global $wpdb;

        $now = current_time('mysql');
        $wpdb->insert(
            $this->table(),
            [
                'session_key'          => sanitize_text_field($sessionKey),
                'page_path'            => sanitize_text_field((string) ($pageContext['path'] ?? '')),
                'page_service'         => sanitize_text_field((string) ($pageContext['service'] ?? '')),
                'page_title'           => sanitize_text_field((string) ($pageContext['title'] ?? '')),
                'status'               => 'open',
                'message_count'        => 0,
                'created_at'           => $now,
                'updated_at'           => $now,
                'last_user_message_at' => null,
                'meta_json'            => null,
            ],
            ['%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s']
        );

        $id = (int) $wpdb->insert_id;

        return $this->findById($id) ?? [
            'id'          => $id,
            'session_key' => $sessionKey,
            'status'      => 'open',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        global $wpdb;

        if ($id < 1) {
            return null;
        }

        $row = $wpdb->get_row(
            $wpdb->prepare('SELECT * FROM ' . $this->table() . ' WHERE id = %d LIMIT 1', $id),
            ARRAY_A
        );

        return is_array($row) ? $row : null;
    }

    /**
     * @param array<string, mixed> $fields
     */
    public function update(int $id, array $fields): bool
    {
        global $wpdb;

        if ($id < 1 || $fields === []) {
            return false;
        }

        $allowed = [
            'page_path',
            'page_service',
            'page_title',
            'status',
            'intent',
            'ghl_contact_id',
            'estimate_id',
            'quote_total',
            'message_count',
            'last_user_message_at',
            'meta_json',
        ];

        $data   = [];
        $format = [];

        foreach ($allowed as $key) {
            if (!array_key_exists($key, $fields)) {
                continue;
            }

            if ($key === 'message_count') {
                $data[$key] = (int) $fields[$key];
                $format[]   = '%d';
                continue;
            }

            if ($key === 'quote_total') {
                $data[$key] = $fields[$key] !== null ? (float) $fields[$key] : null;
                $format[]   = '%f';
                continue;
            }

            if ($key === 'meta_json' && is_array($fields[$key])) {
                $data[$key] = wp_json_encode($fields[$key]);
                $format[]   = '%s';
                continue;
            }

            $data[$key] = sanitize_text_field((string) $fields[$key]);
            $format[]   = '%s';
        }

        if ($data === []) {
            return false;
        }

        $data['updated_at'] = current_time('mysql');
        $format[]           = '%s';

        $result = $wpdb->update($this->table(), $data, ['id' => $id], $format, ['%d']);

        return $result !== false;
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function listRecent(int $limit = 50, int $offset = 0, ?string $status = null): array
    {
        global $wpdb;

        $limit  = max(1, min(200, $limit));
        $offset = max(0, $offset);
        $table  = $this->table();

        if ($status !== null && $status !== '') {
            $status = sanitize_text_field($status);
            $total  = (int) $wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE status = %s", $status)
            );
            $rows   = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$table} WHERE status = %s ORDER BY updated_at DESC LIMIT %d OFFSET %d",
                    $status,
                    $limit,
                    $offset
                ),
                ARRAY_A
            );
        } else {
            $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
            $rows  = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$table} ORDER BY updated_at DESC LIMIT %d OFFSET %d",
                    $limit,
                    $offset
                ),
                ARRAY_A
            );
        }

        return [
            'items' => is_array($rows) ? $rows : [],
            'total' => $total,
        ];
    }
}
