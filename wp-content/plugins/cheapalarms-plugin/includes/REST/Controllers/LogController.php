<?php

namespace CheapAlarms\Plugin\REST\Controllers;

use CheapAlarms\Plugin\REST\Auth\Authenticator;
use CheapAlarms\Plugin\REST\Controllers\Helpers\LogControllerHelper;
use CheapAlarms\Plugin\Services\Container;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

use function defined;
use function file_exists;
use function filesize;
use function ini_get;
use function is_readable;
use function realpath;
use function sanitize_text_field;
use function strtolower;

if (!defined('ABSPATH')) {
    exit;
}

class LogController implements ControllerInterface
{
    private Authenticator $auth;
    private const MAX_LINES = 1000; // Maximum lines per request
    private const DEFAULT_LINES = 100;
    private const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB max file size to read

    public function __construct(private Container $container)
    {
        $this->auth = $this->container->get(Authenticator::class);
    }

    public function register(): void
    {
        register_rest_route('ca/v1', '/admin/logs', [
            'methods'             => 'GET',
            'permission_callback' => fn () => true, // Check in callback
            'callback'            => [$this, 'getLogs'],
            'args'                => [
                'limit'      => [
                    'type'              => 'integer',
                    'default'           => self::DEFAULT_LINES,
                    'sanitize_callback' => 'absint',
                    'validate_callback' => function ($param) {
                        return $param > 0 && $param <= self::MAX_LINES;
                    },
                ],
                'level'      => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => function ($param) {
                        return empty($param) || in_array(strtolower($param), ['error', 'warning', 'info', 'debug'], true);
                    },
                ],
                'search'     => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'request_id' => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
    }

    /**
     * Get logs with filtering and pagination
     */
    public function getLogs(WP_REST_Request $request): WP_REST_Response
    {
        // Check authentication using Authenticator pattern (consistent with other controllers)
        $this->auth->ensureUserLoaded();
        
        $authCheck = $this->auth->requireCapability('ca_manage_portal');
        if (is_wp_error($authCheck)) {
            return $this->errorResponse($authCheck);
        }

        $limit = (int) $request->get_param('limit');
        $levelFilter = strtolower((string) $request->get_param('level'));
        $searchQuery = sanitize_text_field((string) $request->get_param('search'));
        $requestIdFilter = sanitize_text_field((string) $request->get_param('request_id'));

        // Set timeout for file operations (30 seconds) to prevent long-running requests
        $originalTimeLimit = ini_get('max_execution_time');
        @set_time_limit(30);

        try {
            // Get log file path
            $logPath = $this->getLogFilePath();
            if (!$logPath) {
                // Return empty logs instead of error (file might not exist yet or logging disabled)
                // This is better UX - admin can still access the page
                $response = new WP_REST_Response([
                    'ok'        => true,
                    'logs'      => [],
                    'total'     => 0,
                    'file_size' => 0,
                    'file_path' => '[NOT CONFIGURED]',
                    'message'   => defined('WP_DEBUG_LOG') && WP_DEBUG_LOG === false
                        ? 'Logging is disabled. Set WP_DEBUG_LOG=true in wp-config.php to enable logs.'
                        : 'Log file not found. Ensure logging is enabled in wp-config.php or PHP error_log is configured.',
                ], 200);
                
                $this->addSecurityHeaders($response);
                return $response;
            }

            // Check file size (prevent reading huge files)
            $fileSize = @filesize($logPath);
            if ($fileSize === false) {
                $error = error_get_last();
                if (function_exists('error_log') && $error) {
                    error_log('[CA] Failed to get log file size: ' . $error['message']);
                }
                return $this->errorResponse(new WP_Error(
                    'log_file_size_check_failed',
                    'Failed to check log file size.',
                    ['status' => 500]
                ));
            }
            
            if ($fileSize > self::MAX_FILE_SIZE) {
                return $this->errorResponse(new WP_Error(
                    'log_file_too_large',
                    'Log file is too large to read safely.',
                    ['status' => 413]
                ));
            }

            // Read and parse logs (pass level filter for early filtering optimization)
            $logs = $this->readLogFile($logPath, $limit, $levelFilter);
            if (is_wp_error($logs)) {
                return $this->errorResponse($logs);
            }

            // Apply remaining filters (search and request_id)
            $filteredLogs = LogControllerHelper::filterLogs($logs, '', $searchQuery, $requestIdFilter);

            // Sanitize output (security: ensure no sensitive data leaks)
            $sanitizedLogs = LogControllerHelper::sanitizeLogs($filteredLogs);

            $response = new WP_REST_Response([
                'ok'        => true,
                'logs'      => $sanitizedLogs,
                'total'     => count($filteredLogs),
                'file_size' => $fileSize,
                'file_path' => defined('WP_DEBUG') && WP_DEBUG ? $logPath : '[REDACTED]',
            ], 200);
            
            $this->addSecurityHeaders($response);
            return $response;
        } finally {
            // Restore original time limit
            // Handle both false and '0' (unlimited) cases
            if ($originalTimeLimit !== false && $originalTimeLimit !== '0') {
                @set_time_limit((int)$originalTimeLimit);
            }
        }
    }

    /**
     * Get log file path (prioritizes PHP error_log since that's where plugin logs)
     */
    private function getLogFilePath(): ?string
    {
        $path = null;
        
        // PRIORITY 1: Check PHP error_log first (where plugin's Logger service actually writes)
        // This is the most reliable source since error_log() always uses PHP's error_log setting
        $phpLogPath = ini_get('error_log');
        if (!empty($phpLogPath) && file_exists($phpLogPath) && is_readable($phpLogPath)) {
            $path = $phpLogPath;
        }
        
        // PRIORITY 2: Check WP_DEBUG_LOG (WordPress debug log)
        if (!$path && defined('WP_DEBUG_LOG')) {
            if (WP_DEBUG_LOG === true || WP_DEBUG_LOG === 'true' || WP_DEBUG_LOG === '1') {
                $wpDebugPath = WP_CONTENT_DIR . '/debug.log';
                if (file_exists($wpDebugPath) && is_readable($wpDebugPath)) {
                    $path = $wpDebugPath;
                }
            } elseif (is_string(WP_DEBUG_LOG) && !empty(WP_DEBUG_LOG)) {
                if (file_exists(WP_DEBUG_LOG) && is_readable(WP_DEBUG_LOG)) {
                    $path = WP_DEBUG_LOG;
                }
            }
        }
        
        // If no valid path found, return null
        if (!$path) {
            return null;
        }

        // SECURITY: Validate path for WordPress debug.log files (must be within WP_CONTENT_DIR)
        // PHP error_log files are allowed outside WP_CONTENT_DIR (system-level logs)
        $realPath = realpath($path);
        $wpContentRealPath = realpath(WP_CONTENT_DIR);
        
        if ($realPath === false) {
            // Can't resolve path - reject for security
            if (function_exists('error_log')) {
                error_log('[CA] Security: Failed to resolve log file path');
            }
            return null;
        }
        
        // Check if this is a WordPress debug.log path (must be within WP_CONTENT_DIR)
        $isWordPressLog = (
            strpos($path, WP_CONTENT_DIR) === 0 ||
            (defined('WP_DEBUG_LOG') && is_string(WP_DEBUG_LOG) && $path === WP_DEBUG_LOG)
        );
        
        if ($isWordPressLog && $wpContentRealPath !== false) {
            // For WordPress logs, enforce WP_CONTENT_DIR restriction
            $separator = DIRECTORY_SEPARATOR;
            $normalizedRealPath = str_replace(['/', '\\'], $separator, $realPath);
            $normalizedWpContent = str_replace(['/', '\\'], $separator, $wpContentRealPath);
            
            $isWithinContentDir = (
                strpos($normalizedRealPath, $normalizedWpContent . $separator) === 0 ||
                $normalizedRealPath === $normalizedWpContent
            );
            
            if (!$isWithinContentDir) {
                // Log security warning but don't expose path
                if (function_exists('error_log')) {
                    error_log('[CA] Security: WordPress log file path outside WP_CONTENT_DIR rejected');
                }
                return null;
            }
        }
        
        // PHP error_log files are allowed (system-level, typically outside WP_CONTENT_DIR)
        // No additional security check needed - PHP's ini_get('error_log') is trusted

        return $path;
    }

    /**
     * Read last N lines from log file efficiently (chunk-based reverse reading)
     * 
     * @param string $filePath
     * @param int $lines
     * @param string|null $levelFilter Optional level filter for early optimization
     * @return array<int, array<string, mixed>>|WP_Error
     */
    private function readLogFile(string $filePath, int $lines, ?string $levelFilter = null): array|WP_Error
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return new WP_Error('file_not_readable', 'Log file is not readable.');
        }

        $handle = @fopen($filePath, 'rb');
        if ($handle === false) {
            $error = error_get_last();
            if (function_exists('error_log') && $error) {
                error_log('[CA] Failed to open log file: ' . $error['message']);
            }
            return new WP_Error('file_open_failed', 'Failed to open log file.');
        }

        try {
            // Get file size
            if (@fseek($handle, 0, SEEK_END) !== 0) {
                $error = error_get_last();
                if (function_exists('error_log') && $error) {
                    error_log('[CA] Failed to seek to end of log file: ' . $error['message']);
                }
                return new WP_Error('file_seek_failed', 'Failed to read log file.');
            }
            
            $fileSize = @ftell($handle);
            if ($fileSize === false) {
                $error = error_get_last();
                if (function_exists('error_log') && $error) {
                    error_log('[CA] Failed to get log file size: ' . $error['message']);
                }
                return new WP_Error('file_size_failed', 'Failed to get log file size.');
            }
            
            if ($fileSize === 0) {
                return [];
            }

            // Use chunk-based reading for better performance (8KB chunks)
            $chunkSize = 8192;
            $maxLineLength = 1024 * 1024; // 1MB max line length (safety limit for extremely long lines)
            $buffer = '';
            $lineCount = 0;
            $position = $fileSize;
            $parsedLogs = [];
            $bufferSize = 0; // Track buffer size to prevent memory issues

            // Read backwards from end of file in chunks
            while ($position > 0 && $lineCount < $lines) {
                $readSize = min($chunkSize, $position);
                $position -= $readSize;
                
                if (@fseek($handle, $position, SEEK_SET) !== 0) {
                    $error = error_get_last();
                    if (function_exists('error_log') && $error) {
                        error_log('[CA] Failed to seek in log file: ' . $error['message']);
                    }
                    break; // Break on seek failure
                }
                
                $chunk = @fread($handle, $readSize);
                
                if ($chunk === false) {
                    $error = error_get_last();
                    if (function_exists('error_log') && $error) {
                        error_log('[CA] Failed to read log file chunk: ' . $error['message']);
                    }
                    break;
                }
                
                // Skip empty chunks (end of file reached)
                if ($chunk === '') {
                    break;
                }
                
                $buffer = $chunk . $buffer;
                $bufferSize += strlen($chunk);
                
                // Safety: prevent buffer from growing too large (indicates very long line)
                if ($bufferSize > $maxLineLength) {
                    // Extract what we can and continue
                    $lastNewline = strrpos($buffer, "\n");
                    if ($lastNewline !== false) {
                        $line = substr($buffer, $lastNewline + 1);
                        $buffer = substr($buffer, 0, $lastNewline);
                        $bufferSize = strlen($buffer);
                        
                        if (!empty(trim($line))) {
                            $parsed = LogControllerHelper::parseLogLine(trim($line));
                            if ($parsed !== null) {
                                $parsedLogs[] = $parsed;
                                $lineCount++;
                            }
                        }
                    } else {
                        // No newline found - line is too long, truncate and continue
                        // Keep first part (most recent data) since we prepend chunks
                        $buffer = substr($buffer, 0, $maxLineLength);
                        $bufferSize = $maxLineLength;
                    }
                }
                
                // Process complete lines from buffer
                while (($newlinePos = strrpos($buffer, "\n")) !== false && $lineCount < $lines) {
                    $line = substr($buffer, $newlinePos + 1);
                    $buffer = substr($buffer, 0, $newlinePos);
                    $bufferSize = strlen($buffer);
                    
                    // Early level filter optimization (before full parsing)
                    if ($levelFilter && !empty($line)) {
                        $hasLevel = LogControllerHelper::quickLevelCheck($line, $levelFilter);
                        // If level filter is set and line doesn't match, skip parsing
                        if (!$hasLevel) {
                            continue;
                        }
                    }
                    
                    if (!empty(trim($line))) {
                        $parsed = LogControllerHelper::parseLogLine(trim($line));
                        if ($parsed !== null) {
                            $parsedLogs[] = $parsed;
                            $lineCount++;
                        }
                    }
                }
            }
            
            // Process remaining buffer (first line of file)
            if (!empty(trim($buffer)) && $lineCount < $lines) {
                // Apply level filter to remaining buffer if set
                if ($levelFilter && !empty($buffer)) {
                    $hasLevel = LogControllerHelper::quickLevelCheck($buffer, $levelFilter);
                    if (!$hasLevel) {
                        return array_reverse($parsedLogs);
                    }
                }
                
                $parsed = LogControllerHelper::parseLogLine(trim($buffer));
                if ($parsed !== null) {
                    $parsedLogs[] = $parsed;
                }
            }

            // Reverse to get chronological order (oldest first)
            return array_reverse($parsedLogs);
        } finally {
            // Always close handle, even on error or early return
            fclose($handle);
        }
    }

    /**
     * Create standardized error response with sanitization
     *
     * @param WP_Error $error
     * @return WP_REST_Response
     */
    private function errorResponse(WP_Error $error): WP_REST_Response
    {
        $status = $error->get_error_data()['status'] ?? 500;
        $code = $error->get_error_code();
        $message = $error->get_error_message();
        
        // SECURITY: Sanitize error messages in production
        $isDebug = defined('WP_DEBUG') && WP_DEBUG;
        
        if (!$isDebug) {
            // Generic messages for production to prevent information disclosure
            $genericMessages = [
                'rest_forbidden' => 'Access denied.',
                'unauthorized' => 'Authentication required.',
                'log_file_not_found' => 'Log file not found.',
                'log_file_too_large' => 'Log file is too large.',
                'log_file_size_check_failed' => 'Log file size check failed.',
                'file_not_readable' => 'Cannot read log file.',
                'file_open_failed' => 'Failed to access log file.',
                'file_seek_failed' => 'Failed to read log file.',
                'file_size_failed' => 'Failed to get log file size.',
            ];
            
            $message = $genericMessages[$code] ?? 'An error occurred. Please try again.';
        }
        
        $response = new WP_REST_Response([
            'ok'    => false,
            'error' => $message,
            'code'  => $code,
            'err'   => $message, // Backward compatibility
        ], $status);
        
        $this->addSecurityHeaders($response);
        return $response;
    }

    /**
     * Add security headers to response
     *
     * @param WP_REST_Response $response
     * @return void
     */
    private function addSecurityHeaders(WP_REST_Response $response): void
    {
        // Prevent MIME type sniffing
        $response->header('X-Content-Type-Options', 'nosniff');
        
        // XSS protection (legacy but still useful)
        $response->header('X-XSS-Protection', '1; mode=block');
        
        // Prevent clickjacking
        $response->header('X-Frame-Options', 'DENY');
        
        // Referrer policy
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}

