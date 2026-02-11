<?php

namespace CheapAlarms\Plugin\Services\Ghl;

use function current_time;

/**
 * Repository for storing and managing GHL webhook events.
 *
 * Mirrors the Stripe WebhookEventRepository pattern but uses its own table
 * (`wp_ca_ghl_webhook_events`) to keep GHL and Stripe concerns separated.
 */
class GhlWebhookEventRepository
{
    private string $tableName;

    public function __construct()
    {
        global $wpdb;
        $this->tableName = $wpdb->prefix . 'ca_ghl_webhook_events';
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Store event BEFORE processing (safe retries).
     * Returns true if new event, false if already exists (INSERT IGNORE).
     */
    public function storeEvent(
        string $webhookId,
        string $eventType,
        ?string $entityId,
        ?string $locationId,
        string $payload
    ): bool {
        global $wpdb;

        $result = $wpdb->query($wpdb->prepare(
            "INSERT IGNORE INTO {$this->tableName}
             (webhook_id, event_type, entity_id, location_id, payload, created_at)
             VALUES (%s, %s, %s, %s, %s, %s)",
            $webhookId,
            $eventType,
            $entityId,
            $locationId,
            $payload,
            current_time('mysql')
        ));

        return $wpdb->rows_affected > 0;
    }

    /**
     * Check if a webhook has already been processed.
     */
    public function isProcessed(string $webhookId): bool
    {
        global $wpdb;
        $processed = $wpdb->get_var($wpdb->prepare(
            "SELECT processed_at FROM {$this->tableName} WHERE webhook_id = %s",
            $webhookId
        ));
        return !empty($processed);
    }

    /**
     * Mark that processing has started (prevents double-processing via stale lock).
     */
    public function markProcessingStarted(string $webhookId): bool
    {
        global $wpdb;
        return $wpdb->update(
            $this->tableName,
            ['processing_started_at' => current_time('mysql')],
            ['webhook_id' => $webhookId, 'processed_at' => null],
            ['%s'],
            ['%s', '%s']
        ) !== false;
    }

    /**
     * Mark event as successfully processed.
     */
    public function markProcessed(string $webhookId): bool
    {
        global $wpdb;
        return $wpdb->update(
            $this->tableName,
            ['processed_at' => current_time('mysql'), 'error_message' => null],
            ['webhook_id' => $webhookId],
            ['%s', '%s'],
            ['%s']
        ) !== false;
    }

    /**
     * Mark event as failed (increments retry_count, clears processing_started_at).
     */
    public function markFailed(string $webhookId, string $errorMessage): bool
    {
        global $wpdb;
        return $wpdb->query($wpdb->prepare(
            "UPDATE {$this->tableName}
             SET error_message = %s, retry_count = retry_count + 1, processing_started_at = NULL
             WHERE webhook_id = %s",
            $errorMessage,
            $webhookId
        )) !== false;
    }

    /**
     * Get pending events for retry.
     * Returns events that:
     *   - Have not been processed
     *   - Are not currently being processed (or processing started > 5 min ago = stale)
     *   - Have fewer than 3 retries
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPendingEvents(int $limit = 50): array
    {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT webhook_id, event_type, entity_id, location_id, payload, retry_count, error_message
             FROM {$this->tableName}
             WHERE processed_at IS NULL
             AND (processing_started_at IS NULL OR processing_started_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE))
             AND retry_count < 3
             ORDER BY created_at ASC
             LIMIT %d",
            $limit
        ), ARRAY_A);
        return $results ?: [];
    }

    /**
     * Get a single event row by webhookId.
     *
     * @return array<string, mixed>|null
     */
    public function getEvent(string $webhookId): ?array
    {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT webhook_id, event_type, entity_id, location_id, payload, retry_count, error_message, processed_at
             FROM {$this->tableName}
             WHERE webhook_id = %s",
            $webhookId
        ), ARRAY_A);
        return $row ?: null;
    }
}
