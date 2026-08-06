<?php

namespace CheapAlarms\Plugin\Db;

use function current_time;
use function get_option;
use function update_option;

class Schema
{
    public const OPTION_KEY = 'ca_db_schema_version';
    public const VERSION    = '2026-07-20-01'; // Chat live-agent handoff (claim columns)

    public static function maybeMigrate(): void
    {
        $current = (string) get_option(self::OPTION_KEY, '');
        if ($current === self::VERSION) {
            return;
        }

        self::migrate();
        update_option(self::OPTION_KEY, self::VERSION, true);
        update_option('ca_db_schema_last_migrated_at', current_time('mysql'), false);
    }

    public static function migrate(): void
    {
        global $wpdb;

        // dbDelta lives here.
        if (!function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        $tableName      = $wpdb->prefix . 'ca_estimate_snapshots';
        $charsetCollate = $wpdb->get_charset_collate();

        // Note: keep columns intentionally small/indexed for admin list performance.
        // Raw GHL payload is stored in `raw_json` to avoid future breaking changes.
        $sql = "CREATE TABLE {$tableName} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            location_id VARCHAR(64) NOT NULL,
            estimate_id VARCHAR(64) NOT NULL,
            estimate_number VARCHAR(64) NULL,
            email VARCHAR(190) NULL,
            ghl_status VARCHAR(32) NULL,
            total DECIMAL(18,2) NULL,
            currency VARCHAR(8) NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            synced_at DATETIME NOT NULL,
            raw_json LONGTEXT NULL,
            deleted_at DATETIME NULL,
            deleted_by BIGINT(20) UNSIGNED NULL,
            deletion_reason VARCHAR(255) NULL,
            deletion_scope VARCHAR(20) NULL,
            restored_at DATETIME NULL,
            restored_by BIGINT(20) UNSIGNED NULL,
            PRIMARY KEY (id),
            UNIQUE KEY location_estimate (location_id, estimate_id),
            KEY location_updated (location_id, updated_at),
            KEY location_synced (location_id, synced_at),
            KEY estimate_id (estimate_id),
            KEY idx_deleted_at (deleted_at),
            KEY idx_deletion_scope (deletion_scope, deleted_at)
        ) {$charsetCollate};";

        dbDelta($sql);

        // Add soft delete columns if they don't exist (for existing installations)
        $columns = $wpdb->get_col("DESCRIBE {$tableName}");
        $columnsToAdd = [];

        if (!in_array('deleted_at', $columns, true)) {
            $columnsToAdd[] = "ALTER TABLE {$tableName} ADD COLUMN deleted_at DATETIME NULL";
        }
        if (!in_array('deleted_by', $columns, true)) {
            $columnsToAdd[] = "ALTER TABLE {$tableName} ADD COLUMN deleted_by BIGINT(20) UNSIGNED NULL";
        }
        if (!in_array('deletion_reason', $columns, true)) {
            $columnsToAdd[] = "ALTER TABLE {$tableName} ADD COLUMN deletion_reason VARCHAR(255) NULL";
        }
        if (!in_array('deletion_scope', $columns, true)) {
            $columnsToAdd[] = "ALTER TABLE {$tableName} ADD COLUMN deletion_scope VARCHAR(20) NULL";
        }
        if (!in_array('restored_at', $columns, true)) {
            $columnsToAdd[] = "ALTER TABLE {$tableName} ADD COLUMN restored_at DATETIME NULL";
        }
        if (!in_array('restored_by', $columns, true)) {
            $columnsToAdd[] = "ALTER TABLE {$tableName} ADD COLUMN restored_by BIGINT(20) UNSIGNED NULL";
        }

        foreach ($columnsToAdd as $alterSql) {
            $result = $wpdb->query($alterSql);
            if ($result === false && !empty($wpdb->last_error)) {
                // Log but don't fail - column might already exist or migration might have run
                error_log('Schema migration warning (column add): ' . $wpdb->last_error);
            }
        }

        // Add indexes if they don't exist (use prepared statements for safety)
        $indexes = $wpdb->get_col($wpdb->prepare(
            "SHOW INDEX FROM {$tableName} WHERE Key_name = %s",
            'idx_deleted_at'
        ));
        if (empty($indexes)) {
            $result = $wpdb->query("ALTER TABLE {$tableName} ADD INDEX idx_deleted_at (deleted_at)");
            if ($result === false && !empty($wpdb->last_error)) {
                // Log but don't fail - index might already exist
                error_log('Schema migration warning (index add): ' . $wpdb->last_error);
            }
        }

        $indexes = $wpdb->get_col($wpdb->prepare(
            "SHOW INDEX FROM {$tableName} WHERE Key_name = %s",
            'idx_deletion_scope'
        ));
        if (empty($indexes)) {
            $result = $wpdb->query("ALTER TABLE {$tableName} ADD INDEX idx_deletion_scope (deletion_scope, deleted_at)");
            if ($result === false && !empty($wpdb->last_error)) {
                // Log but don't fail - index might already exist
                error_log('Schema migration warning (index add): ' . $wpdb->last_error);
            }
        }

        // Create webhook events table
        $webhookTableName = $wpdb->prefix . 'ca_webhook_events';
        $webhookSql = "CREATE TABLE IF NOT EXISTS {$webhookTableName} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            estimate_id VARCHAR(255) NOT NULL,
            event_id VARCHAR(255) NOT NULL,
            event_type VARCHAR(100) NOT NULL,
            payload LONGTEXT NULL COMMENT 'Raw JSON payload for retry/replay',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            processing_started_at DATETIME NULL COMMENT 'When processing started',
            processed_at DATETIME NULL COMMENT 'NULL = pending, timestamp = processed',
            retry_count INT UNSIGNED DEFAULT 0,
            error_message TEXT NULL COMMENT 'Error if processing failed',
            PRIMARY KEY (id),
            UNIQUE KEY unique_event (event_id),
            KEY idx_estimate_id (estimate_id),
            KEY idx_event_type (event_type),
            KEY idx_processed_at (processed_at),
            KEY idx_pending (processed_at, processing_started_at) COMMENT 'Find pending/failed events'
        ) {$charsetCollate};";
        
        dbDelta($webhookSql);

        // Create invoice snapshots table (local read cache for GHL invoices)
        $invoiceTableName = $wpdb->prefix . 'ca_invoice_snapshots';
        $invoiceSql = "CREATE TABLE {$invoiceTableName} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            location_id VARCHAR(64) NOT NULL,
            invoice_id VARCHAR(64) NOT NULL,
            invoice_number VARCHAR(64) NULL,
            contact_id VARCHAR(64) NULL,
            email VARCHAR(190) NULL,
            name VARCHAR(255) NULL,
            ghl_status VARCHAR(32) NULL,
            total DECIMAL(18,2) NULL,
            amount_paid DECIMAL(18,2) NULL DEFAULT 0,
            currency VARCHAR(8) NULL DEFAULT 'AUD',
            due_date DATETIME NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            synced_at DATETIME NOT NULL,
            raw_json LONGTEXT NULL,
            deleted_at DATETIME NULL,
            deleted_by BIGINT(20) UNSIGNED NULL,
            deletion_reason VARCHAR(255) NULL,
            PRIMARY KEY (id),
            UNIQUE KEY location_invoice (location_id, invoice_id),
            KEY idx_location_status (location_id, ghl_status),
            KEY idx_location_updated (location_id, updated_at),
            KEY idx_contact_id (contact_id),
            KEY idx_email (email),
            KEY idx_deleted_at (deleted_at)
        ) {$charsetCollate};";

        dbDelta($invoiceSql);

        // Create contact snapshots table (local read cache for GHL contacts)
        $contactTableName = $wpdb->prefix . 'ca_contact_snapshots';
        $contactSql = "CREATE TABLE {$contactTableName} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            location_id VARCHAR(64) NOT NULL,
            contact_id VARCHAR(64) NOT NULL,
            email VARCHAR(190) NULL,
            first_name VARCHAR(255) NULL,
            last_name VARCHAR(255) NULL,
            phone VARCHAR(64) NULL,
            company_name VARCHAR(255) NULL,
            address_line1 VARCHAR(255) NULL,
            city VARCHAR(128) NULL,
            state VARCHAR(64) NULL,
            postal_code VARCHAR(16) NULL,
            tags TEXT NULL,
            ghl_date_added DATETIME NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            synced_at DATETIME NOT NULL,
            raw_json LONGTEXT NULL,
            deleted_at DATETIME NULL,
            deleted_by BIGINT(20) UNSIGNED NULL,
            deletion_reason VARCHAR(255) NULL,
            PRIMARY KEY (id),
            UNIQUE KEY location_contact (location_id, contact_id),
            KEY idx_email (email),
            KEY idx_name (first_name, last_name),
            KEY idx_location_synced (location_id, synced_at),
            KEY idx_deleted_at (deleted_at),
            KEY idx_phone (phone)
        ) {$charsetCollate};";

        dbDelta($contactSql);

        // Create GHL webhook events table (separate from Stripe webhook events)
        $ghlWebhookTableName = $wpdb->prefix . 'ca_ghl_webhook_events';
        $ghlWebhookSql = "CREATE TABLE {$ghlWebhookTableName} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            webhook_id VARCHAR(255) NOT NULL COMMENT 'GHL webhookId – unique per delivery',
            event_type VARCHAR(100) NOT NULL COMMENT 'GHL event type e.g. ContactCreate, InvoiceUpdate',
            entity_id VARCHAR(255) NULL COMMENT 'Extracted entity ID (contactId, invoiceId, estimateId)',
            location_id VARCHAR(64) NULL,
            payload LONGTEXT NULL COMMENT 'Raw JSON payload for retry/replay',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            processing_started_at DATETIME NULL COMMENT 'When processing started',
            processed_at DATETIME NULL COMMENT 'NULL = pending, timestamp = processed',
            retry_count INT UNSIGNED DEFAULT 0,
            error_message TEXT NULL COMMENT 'Error if processing failed',
            PRIMARY KEY (id),
            UNIQUE KEY unique_webhook (webhook_id),
            KEY idx_event_type (event_type),
            KEY idx_entity_id (entity_id),
            KEY idx_processed_at (processed_at),
            KEY idx_pending (processed_at, processing_started_at) COMMENT 'Find pending/failed events'
        ) {$charsetCollate};";

        dbDelta($ghlWebhookSql);

        // Create ServiceM8 jobs snapshot table (local read cache, SM8 has no webhooks)
        $sm8JobsTableName = $wpdb->prefix . 'ca_sm8_jobs';
        $sm8JobsSql = "CREATE TABLE {$sm8JobsTableName} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            job_uuid VARCHAR(64) NOT NULL,
            company_uuid VARCHAR(64) NULL,
            status VARCHAR(32) NULL,
            job_description TEXT NULL,
            job_address VARCHAR(512) NULL,
            generated_job_id VARCHAR(64) NULL,
            assigned_to_staff_uuid VARCHAR(64) NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            synced_at DATETIME NOT NULL,
            raw_json LONGTEXT NULL,
            deleted_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY idx_job_uuid (job_uuid),
            KEY idx_company_uuid (company_uuid),
            KEY idx_status (status),
            KEY idx_synced_at (synced_at),
            KEY idx_deleted_at (deleted_at)
        ) {$charsetCollate};";

        dbDelta($sm8JobsSql);

        // Create ServiceM8 companies snapshot table (local read cache)
        $sm8CompaniesTableName = $wpdb->prefix . 'ca_sm8_companies';
        $sm8CompaniesSql = "CREATE TABLE {$sm8CompaniesTableName} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            company_uuid VARCHAR(64) NOT NULL,
            name VARCHAR(255) NULL,
            email VARCHAR(190) NULL,
            phone VARCHAR(64) NULL,
            address VARCHAR(512) NULL,
            city VARCHAR(128) NULL,
            state VARCHAR(64) NULL,
            postcode VARCHAR(16) NULL,
            country VARCHAR(64) NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            synced_at DATETIME NOT NULL,
            raw_json LONGTEXT NULL,
            deleted_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY idx_company_uuid (company_uuid),
            KEY idx_name (name),
            KEY idx_email (email),
            KEY idx_synced_at (synced_at),
            KEY idx_deleted_at (deleted_at)
        ) {$charsetCollate};";

        dbDelta($sm8CompaniesSql);

        // Invoice → estimate reverse-lookup index.
        // Replaces the old "SELECT … FROM wp_options WHERE option_name LIKE 'ca_portal_meta_%'"
        // table scan that runs once per invoice page load.
        // Each row maps a GHL invoice_id back to the WP estimate_id that owns it.
        // Populated via the `ca_portal_meta_updated` action (see Plugin::bootstrap).
        $linksTableName = $wpdb->prefix . 'ca_invoice_estimate_links';
        $linksSql = "CREATE TABLE {$linksTableName} (
            invoice_id VARCHAR(64) NOT NULL,
            estimate_id VARCHAR(64) NOT NULL,
            location_id VARCHAR(64) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            PRIMARY KEY (invoice_id),
            KEY idx_estimate_id (estimate_id),
            KEY idx_location_id (location_id)
        ) {$charsetCollate};";

        dbDelta($linksSql);

        // GHL product catalog + prices (local read cache for Quick Quote, avoids per-product GHL price calls).
        $productTableName = $wpdb->prefix . 'ca_product_snapshots';
        $productSql = "CREATE TABLE {$productTableName} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            location_id VARCHAR(64) NOT NULL,
            product_id VARCHAR(64) NOT NULL,
            name VARCHAR(255) NOT NULL DEFAULT '',
            sku VARCHAR(128) NULL,
            description TEXT NULL,
            image TEXT NULL,
            price_amount DECIMAL(18,2) NULL,
            price_currency VARCHAR(8) NULL DEFAULT 'AUD',
            price_id VARCHAR(64) NULL,
            product_type VARCHAR(32) NULL,
            synced_at DATETIME NOT NULL,
            raw_json LONGTEXT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY location_product (location_id, product_id),
            KEY idx_location_name (location_id, name),
            KEY idx_location_synced (location_id, synced_at)
        ) {$charsetCollate};";

        dbDelta($productSql);

        // Website chat conversations (Safeguard assistant, marketing widget).
        $convTableName = $wpdb->prefix . 'ca_conversations';
        $convSql       = "CREATE TABLE {$convTableName} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            session_key VARCHAR(64) NOT NULL,
            page_path VARCHAR(255) NULL,
            page_service VARCHAR(64) NULL,
            page_title VARCHAR(255) NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'open',
            intent VARCHAR(64) NULL,
            ghl_contact_id VARCHAR(64) NULL,
            estimate_id VARCHAR(64) NULL,
            quote_total DECIMAL(18,2) NULL,
            message_count INT UNSIGNED NOT NULL DEFAULT 0,
            claimed_by BIGINT(20) UNSIGNED NULL,
            claimed_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            last_user_message_at DATETIME NULL,
            meta_json LONGTEXT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY session_key (session_key),
            KEY idx_status_updated (status, updated_at),
            KEY idx_service (page_service),
            KEY idx_created (created_at),
            KEY idx_claimed_by (claimed_by)
        ) {$charsetCollate};";

        dbDelta($convSql);

        $msgTableName = $wpdb->prefix . 'ca_messages';
        $msgSql       = "CREATE TABLE {$msgTableName} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            conversation_id BIGINT(20) UNSIGNED NOT NULL,
            role VARCHAR(16) NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME NOT NULL,
            meta_json LONGTEXT NULL,
            PRIMARY KEY (id),
            KEY idx_conversation_created (conversation_id, created_at)
        ) {$charsetCollate};";

        dbDelta($msgSql);
    }
}


