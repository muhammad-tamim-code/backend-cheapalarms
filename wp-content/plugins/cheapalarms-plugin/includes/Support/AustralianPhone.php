<?php

namespace CheapAlarms\Plugin\Support;

/**
 * Australian mobile and landline numbers → E.164 (+61…).
 */
final class AustralianPhone
{
    /**
     * @return string|null E.164 (+61…) or null if invalid
     */
    public static function toE164(string $raw): ?string
    {
        $clean = self::clean($raw);
        if ($clean === '') {
            return null;
        }

        if (!self::isValid($clean)) {
            return null;
        }

        if (str_starts_with($clean, '+61')) {
            return $clean;
        }

        if (str_starts_with($clean, '0')) {
            return '+61' . substr($clean, 1);
        }

        if (preg_match('/^[45]\d{8}$/', $clean) === 1) {
            return '+61' . $clean;
        }

        return null;
    }

    public static function isValid(string $raw): bool
    {
        $clean = self::clean($raw);
        if ($clean === '') {
            return false;
        }

        return (bool) preg_match('/^(?:\+61|0)[45]\d{8}$/', $clean)
            || (bool) preg_match('/^(?:\+61|0)[2378]\d{8}$/', $clean);
    }

    private static function clean(string $raw): string
    {
        return preg_replace('/[\s\-()]/', '', trim($raw)) ?? '';
    }
}
