<?php

namespace CheapAlarms\Plugin\Support;

/**
 * Format GHL estimate numbers for display (prefix + sequence).
 *
 * GHL stores estimateNumber as a bare integer; their UI may show "undefined146"
 * when the prefix fails to load. We format consistently for portal/admin/emails.
 */
final class EstimateNumber
{
    public static function format($raw, string $prefix = 'EST-', ?string $fallbackId = null): string
    {
        if ($raw === null || $raw === '') {
            return $fallbackId ?? '';
        }

        $value = trim((string) $raw);
        if ($value === '') {
            return $fallbackId ?? '';
        }

        // GHL UI bug: undefined + 146 → "undefined146"
        if (preg_match('/^undefined(\d+)$/i', $value, $matches)) {
            $value = $matches[1];
        }

        if (preg_match('/^\d+$/', $value)) {
            return $prefix . $value;
        }

        // Already formatted (e.g. EST-146, QTE-12)
        if (preg_match('/^[A-Za-z]+-\S+$/', $value)) {
            return $value;
        }

        return $value;
    }
}
