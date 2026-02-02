<?php

namespace CheapAlarms\Plugin\REST\Controllers\Helpers;

use function gmdate;
use function implode;
use function is_array;
use function json_decode;
use function json_last_error;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function str_contains;
use function strlen;
use function strtolower;
use function strtoupper;
use function trim;
use function wp_json_encode;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Pure helpers for log parsing, filtering, and sanitization.
 * Used by LogController only.
 */
final class LogControllerHelper
{
    /** @var list<string> Keys that indicate sensitive data to redact */
    public const SENSITIVE_KEYS = [
        'password', 'token', 'secret', 'key', 'authorization', 'cookie', 'api_key', 'auth',
    ];

    /**
     * Quick level check for early filtering (e.g. before full parsing).
     *
     * @param string $line       Raw log line
     * @param string $levelFilter Level to match (error, warning, info, debug)
     * @return bool
     */
    public static function quickLevelCheck(string $line, string $levelFilter): bool
    {
        $lineLower = strtolower($line);

        if (strlen($lineLower) > 0 && $lineLower[0] === '{') {
            $levelPattern = '/"level"\s*:\s*"([^"]+)"/i';
            if (preg_match($levelPattern, $lineLower, $matches)) {
                $foundLevel = strtolower(trim($matches[1], ' "'));
                return $foundLevel === $levelFilter;
            }
        }

        if (preg_match('/\[CheapAlarms\]\[(' . preg_quote($levelFilter, '/') . ')\]/i', $line)) {
            return true;
        }

        foreach (['error', 'warning', 'info', 'debug'] as $level) {
            if ($level === $levelFilter) {
                if (str_contains($lineLower, '[' . $level . ']')
                    || str_contains($lineLower, '"level":"' . $level . '"')
                    || str_contains($lineLower, '"level":"' . strtoupper($level) . '"')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Parse a single log line (JSON or plain text).
     *
     * @param string $line Raw log line
     * @return array<string, mixed>|null Parsed log entry or null
     */
    public static function parseLogLine(string $line): ?array
    {
        $trimmed = trim($line);
        if (empty($trimmed)) {
            return null;
        }

        if (strlen($trimmed) > 0 && $trimmed[0] === '{') {
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === \JSON_ERROR_NONE && is_array($decoded)) {
                if (isset($decoded['timestamp'], $decoded['level'], $decoded['message'])) {
                    return [
                        'timestamp'  => $decoded['timestamp'],
                        'level'      => strtolower($decoded['level']),
                        'message'    => $decoded['message'],
                        'context'    => $decoded['context'] ?? [],
                        'request_id' => $decoded['request_id'] ?? null,
                        'user_id'    => $decoded['user_id'] ?? null,
                        'format'     => 'json',
                        'raw'        => $line,
                    ];
                }
            }
        }

        if (preg_match('/^\[CheapAlarms\]\[(\w+)\]\s+(.+?)(?:\s+(\{.*\}))?$/', $line, $matches)) {
            $context = [];
            if (!empty($matches[3])) {
                $contextDecoded = json_decode($matches[3], true);
                if (json_last_error() === \JSON_ERROR_NONE) {
                    $context = $contextDecoded;
                }
            }

            return [
                'timestamp'  => gmdate('c'),
                'level'      => strtolower($matches[1]),
                'message'    => $matches[2],
                'context'    => $context,
                'request_id' => null,
                'user_id'    => null,
                'format'     => 'text',
                'raw'        => $line,
            ];
        }

        return [
            'timestamp'  => gmdate('c'),
            'level'      => 'info',
            'message'    => $line,
            'context'    => [],
            'request_id' => null,
            'user_id'    => null,
            'format'     => 'text',
            'raw'        => $line,
        ];
    }

    /**
     * Filter logs by level, search query, and request_id.
     *
     * @param array<int, array<string, mixed>> $logs
     * @param string $levelFilter
     * @param string $searchQuery
     * @param string $requestIdFilter
     * @return array<int, array<string, mixed>>
     */
    public static function filterLogs(
        array $logs,
        string $levelFilter,
        string $searchQuery,
        string $requestIdFilter
    ): array {
        $filtered = [];

        foreach ($logs as $log) {
            if (!is_array($log)) {
                continue;
            }

            $logLevel = $log['level'] ?? '';
            if ($levelFilter !== '' && $logLevel !== $levelFilter) {
                continue;
            }

            if ($requestIdFilter !== '') {
                $logRequestId = $log['request_id'] ?? '';
                if ($logRequestId === '' || !str_contains(strtolower((string) $logRequestId), strtolower($requestIdFilter))) {
                    continue;
                }
            }

            if ($searchQuery !== '') {
                $searchLower = strtolower($searchQuery);
                $logMessage = $log['message'] ?? '';
                $messageMatch = str_contains(strtolower((string) $logMessage), $searchLower);
                $contextMatch = false;

                if (!empty($log['context']) && is_array($log['context'])) {
                    $contextJson = wp_json_encode($log['context']);
                    if ($contextJson !== false) {
                        $contextMatch = str_contains(strtolower($contextJson), $searchLower);
                    }
                }

                if (!$messageMatch && !$contextMatch) {
                    continue;
                }
            }

            $filtered[] = $log;
        }

        return $filtered;
    }

    /**
     * Sanitize log entries for output (redact sensitive keys).
     *
     * @param array<int, array<string, mixed>> $logs
     * @param list<string>|null $sensitiveKeys Optional; defaults to self::SENSITIVE_KEYS
     * @return array<int, array<string, mixed>>
     */
    public static function sanitizeLogs(array $logs, ?array $sensitiveKeys = null): array
    {
        $sensitiveKeys = $sensitiveKeys ?? self::SENSITIVE_KEYS;
        $sanitized = [];

        foreach ($logs as $log) {
            if (!is_array($log)) {
                continue;
            }

            $sanitizedLog = $log;

            if (!empty($log['context']) && is_array($log['context'])) {
                $sanitizedLog['context'] = self::sanitizeContext($log['context'], $sensitiveKeys);
            }

            $sanitizedLog['message'] = self::sanitizeString((string) ($log['message'] ?? ''), $sensitiveKeys);
            $sanitizedLog['raw']     = self::sanitizeString((string) ($log['raw'] ?? ''), $sensitiveKeys);

            $sanitized[] = $sanitizedLog;
        }

        return $sanitized;
    }

    /**
     * Recursively sanitize context array (redact sensitive keys).
     *
     * @param array<string, mixed> $context
     * @param list<string> $sensitiveKeys
     * @return array<string, mixed>
     */
    public static function sanitizeContext(array $context, array $sensitiveKeys): array
    {
        $sanitized = [];

        foreach ($context as $key => $value) {
            $keyLower    = strtolower($key);
            $isSensitive = false;

            foreach ($sensitiveKeys as $sensitiveKey) {
                if (str_contains($keyLower, $sensitiveKey)) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = self::sanitizeContext($value, $sensitiveKeys);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize string by replacing sensitive key=value or key:value patterns.
     *
     * @param string $str
     * @param list<string> $sensitiveKeys
     * @return string
     */
    public static function sanitizeString(string $str, array $sensitiveKeys): string
    {
        foreach ($sensitiveKeys as $key) {
            $pattern = '/\b' . preg_quote($key, '/') . '\s*[:=]\s*[^\s,}]+/i';
            $str     = preg_replace($pattern, $key . '=[REDACTED]', $str);
        }

        return $str;
    }
}
