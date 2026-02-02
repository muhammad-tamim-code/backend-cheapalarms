<?php

namespace CheapAlarms\Plugin\REST\Controllers\Helpers;

use function preg_match;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Pure validation helpers for ServiceM8 REST endpoints.
 * Used by ServiceM8Controller only.
 */
final class ServiceM8ControllerHelper
{
    /**
     * Validate UUID format (alphanumeric and hyphens only).
     *
     * @param string $uuid UUID to validate
     * @return bool True if valid format
     */
    public static function validateUuid(string $uuid): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9\-]+$/', $uuid);
    }

    /**
     * Validate estimate ID format (alphanumeric, hyphens, underscores).
     *
     * @param string $estimateId Estimate ID to validate
     * @return bool True if valid format
     */
    public static function validateEstimateId(string $estimateId): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9\-_]+$/', $estimateId);
    }
}
