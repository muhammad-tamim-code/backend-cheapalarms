<?php

namespace CheapAlarms\Plugin\Services;

use CheapAlarms\Plugin\Config\Config;
use WP_Error;

use function is_wp_error;
use function wp_generate_uuid4;
use function wp_strip_all_tags;

/**
 * Minimal S3-compatible client for Wasabi (PutObject + presigned GET).
 * Uses AWS Signature Version 4 over WordPress HTTP API — no AWS SDK dependency.
 */
class WasabiS3Client
{
    public const ATTACHMENT_PREFIX = 'wasabi:';

    public function __construct(
        private Config $config,
        private Logger $logger
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->config->isWasabiConfigured();
    }

    /**
     * @return array{key: string, url: string, attachmentId: string, storage: string}|WP_Error
     */
    public function putObject(string $localPath, string $key, string $contentType)
    {
        try {
            if (!$this->isConfigured()) {
                return new WP_Error('wasabi_not_configured', __('Wasabi storage is not configured.', 'cheapalarms'), ['status' => 500]);
            }

            if (!is_readable($localPath)) {
                return new WP_Error('wasabi_read_failed', __('Unable to read uploaded file.', 'cheapalarms'), ['status' => 500]);
            }

            $body = file_get_contents($localPath);
            if ($body === false) {
                return new WP_Error('wasabi_read_failed', __('Unable to read uploaded file.', 'cheapalarms'), ['status' => 500]);
            }

            $bucket     = $this->config->getWasabiBucket();
            $region     = $this->config->getWasabiRegion();
            $host       = $this->config->getWasabiEndpointHost();
            $accessKey  = $this->config->getWasabiAccessKey();
            $secretKey  = $this->config->getWasabiSecretKey();
            $contentType = $contentType !== '' ? $contentType : 'application/octet-stream';

            $amzDate   = gmdate('Ymd\THis\Z');
            $dateStamp = gmdate('Ymd');
            $payloadHash = hash('sha256', $body);
            $canonicalUri = '/' . $this->rawurlencodePath($bucket . '/' . $key);

            $canonicalHeaders = "content-type:{$contentType}\nhost:{$host}\nx-amz-content-sha256:{$payloadHash}\nx-amz-date:{$amzDate}\n";
            $signedHeaders = 'content-type;host;x-amz-content-sha256;x-amz-date';
            $canonicalRequest = implode("\n", [
                'PUT',
                $canonicalUri,
                '',
                $canonicalHeaders,
                $signedHeaders,
                $payloadHash,
            ]);

            $credentialScope = "{$dateStamp}/{$region}/s3/aws4_request";
            $stringToSign = implode("\n", [
                'AWS4-HMAC-SHA256',
                $amzDate,
                $credentialScope,
                hash('sha256', $canonicalRequest),
            ]);

            $signature = $this->sign($secretKey, $dateStamp, $region, $stringToSign);
            $authorization = "AWS4-HMAC-SHA256 Credential={$accessKey}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";
            $url = 'https://' . $host . $canonicalUri;

            // Prefer cURL — WordPress HTTP API has mangled SigV4 headers on some hosts.
            if (!function_exists('curl_init')) {
                return new WP_Error('wasabi_upload_failed', __('cURL is required for object storage uploads.', 'cheapalarms'), ['status' => 500]);
            }

            $ch = curl_init($url);
            if ($ch === false) {
                return new WP_Error('wasabi_upload_failed', __('Unable to start upload transfer.', 'cheapalarms'), ['status' => 500]);
            }

            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST  => 'PUT',
                CURLOPT_POSTFIELDS     => $body,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => false,
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: ' . $contentType,
                    'Host: ' . $host,
                    'X-Amz-Content-Sha256: ' . $payloadHash,
                    'X-Amz-Date: ' . $amzDate,
                    'Authorization: ' . $authorization,
                ],
            ]);

            $respBody = curl_exec($ch);
            $curlErr  = curl_error($ch);
            $code     = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($respBody === false || $code === 0) {
                $this->logger->error('Wasabi PutObject cURL error', [
                    'key'   => $key,
                    'error' => $curlErr !== '' ? $curlErr : 'empty response',
                ]);
                return new WP_Error(
                    'wasabi_upload_failed',
                    __('Failed to reach object storage.', 'cheapalarms') . ($curlErr !== '' ? ' ' . $curlErr : ''),
                    [
                        'status'     => 502,
                        'curl_error' => $curlErr !== '' ? $curlErr : 'empty response',
                        'http_status'=> 0,
                    ]
                );
            }

            if ($code < 200 || $code >= 300) {
                $rawBody = substr(wp_strip_all_tags((string) $respBody), 0, 500);
                $wasabiCode = null;
                $wasabiMessage = null;
                if (is_string($respBody) && $respBody !== '') {
                    if (preg_match('/<Code>([^<]+)<\/Code>/', $respBody, $m)) {
                        $wasabiCode = $m[1];
                    }
                    if (preg_match('/<Message>([^<]+)<\/Message>/', $respBody, $m)) {
                        $wasabiMessage = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    }
                }
                $this->logger->error('Wasabi PutObject rejected', [
                    'key'  => $key,
                    'code' => $code,
                    'wasabi_code' => $wasabiCode,
                    'body' => $rawBody,
                ]);
                $human = sprintf(
                    /* translators: %d: HTTP status code from object storage */
                    __('Object storage rejected the upload (HTTP %d).', 'cheapalarms'),
                    $code
                );
                if ($wasabiCode) {
                    $human .= ' [' . $wasabiCode . ']';
                }
                if ($wasabiMessage) {
                    $human .= ' ' . $wasabiMessage;
                }
                return new WP_Error(
                    'wasabi_upload_failed',
                    $human,
                    [
                        'status'         => 502,
                        'http_status'    => $code,
                        'wasabi_code'    => $wasabiCode,
                        'wasabi_message' => $wasabiMessage,
                    ]
                );
            }

            $signedUrl = $this->presignGet($key);
            if (is_wp_error($signedUrl)) {
                return $signedUrl;
            }

            return [
                'key'          => $key,
                'url'          => $signedUrl,
                'attachmentId' => self::ATTACHMENT_PREFIX . $key,
                'storage'      => 'wasabi',
            ];
        } catch (\Throwable $e) {
            $this->logger->error('Wasabi PutObject exception', [
                'key'   => $key,
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);
            return new WP_Error(
                'wasabi_upload_failed',
                __('Object storage upload failed unexpectedly.', 'cheapalarms') . ' ' . $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * @return string|WP_Error
     */
    public function presignGet(string $key, ?int $expiresSeconds = null)
    {
        if (!$this->isConfigured()) {
            return new WP_Error('wasabi_not_configured', __('Wasabi storage is not configured.', 'cheapalarms'), ['status' => 500]);
        }

        $expires = $expiresSeconds ?? $this->config->getWasabiSignedUrlTtl();
        $expires = max(60, min(604800, $expires)); // 1 min .. 7 days

        $bucket    = $this->config->getWasabiBucket();
        $region    = $this->config->getWasabiRegion();
        $host      = $this->config->getWasabiEndpointHost();
        $accessKey = $this->config->getWasabiAccessKey();
        $secretKey = $this->config->getWasabiSecretKey();

        $amzDate   = gmdate('Ymd\THis\Z');
        $dateStamp = gmdate('Ymd');
        $credentialScope = "{$dateStamp}/{$region}/s3/aws4_request";
        $credential = "{$accessKey}/{$credentialScope}";

        $canonicalUri = '/' . $this->rawurlencodePath($bucket . '/' . $key);
        $query = [
            'X-Amz-Algorithm'     => 'AWS4-HMAC-SHA256',
            'X-Amz-Credential'    => $credential,
            'X-Amz-Date'          => $amzDate,
            'X-Amz-Expires'       => (string) $expires,
            'X-Amz-SignedHeaders' => 'host',
        ];
        ksort($query);
        $canonicalQuery = [];
        foreach ($query as $k => $v) {
            $canonicalQuery[] = rawurlencode($k) . '=' . rawurlencode($v);
        }
        $canonicalQueryString = implode('&', $canonicalQuery);

        $canonicalHeaders = "host:{$host}\n";
        $canonicalRequest = implode("\n", [
            'GET',
            $canonicalUri,
            $canonicalQueryString,
            $canonicalHeaders,
            'host',
            'UNSIGNED-PAYLOAD',
        ]);

        $stringToSign = implode("\n", [
            'AWS4-HMAC-SHA256',
            $amzDate,
            $credentialScope,
            hash('sha256', $canonicalRequest),
        ]);

        $signature = $this->sign($secretKey, $dateStamp, $region, $stringToSign);

        return 'https://' . $host . $canonicalUri . '?' . $canonicalQueryString . '&X-Amz-Signature=' . $signature;
    }

    /**
     * Refresh signed GET URLs for Wasabi-backed upload records.
     *
     * @param array<int, array<string, mixed>> $uploads
     * @return array{uploads: array<int, array<string, mixed>>, changed: bool}
     */
    public function refreshUploadUrls(array $uploads): array
    {
        $changed = false;
        $out = [];

        foreach ($uploads as $upload) {
            if (!is_array($upload)) {
                continue;
            }

            $key = $this->resolveStorageKey($upload);
            if ($key === null) {
                $out[] = $upload;
                continue;
            }

            if (!$this->isConfigured()) {
                $out[] = $upload;
                continue;
            }

            $signed = $this->presignGet($key);
            if (is_wp_error($signed)) {
                $this->logger->warning('Wasabi presign failed for stored upload', [
                    'key'   => $key,
                    'error' => $signed->get_error_message(),
                ]);
                $out[] = $upload;
                continue;
            }

            if (($upload['url'] ?? '') !== $signed) {
                $upload['url'] = $signed;
                $changed = true;
            }
            if (($upload['storage'] ?? '') !== 'wasabi') {
                $upload['storage'] = 'wasabi';
                $changed = true;
            }
            if (($upload['storageKey'] ?? '') !== $key) {
                $upload['storageKey'] = $key;
                $changed = true;
            }
            $expectedId = self::ATTACHMENT_PREFIX . $key;
            if ((string) ($upload['attachmentId'] ?? '') !== $expectedId) {
                $upload['attachmentId'] = $expectedId;
                $changed = true;
            }

            $out[] = $upload;
        }

        return ['uploads' => $out, 'changed' => $changed];
    }

    public function isWasabiAttachmentId(string $attachmentId): bool
    {
        return str_starts_with($attachmentId, self::ATTACHMENT_PREFIX);
    }

    /**
     * @param array<string, mixed> $upload
     */
    public function resolveStorageKey(array $upload): ?string
    {
        $key = $upload['storageKey'] ?? null;
        if (is_string($key) && $key !== '') {
            return ltrim($key, '/');
        }

        $attachmentId = (string) ($upload['attachmentId'] ?? '');
        if ($this->isWasabiAttachmentId($attachmentId)) {
            return ltrim(substr($attachmentId, strlen(self::ATTACHMENT_PREFIX)), '/');
        }

        if (($upload['storage'] ?? '') === 'wasabi' && !empty($upload['key']) && is_string($upload['key'])) {
            return ltrim($upload['key'], '/');
        }

        return null;
    }

    /**
     * Build object key: estimate-photos/{locationId}/{estimateId}/{yyyy}/{mm}/{uuid}.{ext}
     */
    public function buildObjectKey(string $locationId, string $estimateId, string $extension): string
    {
        $prefix = trim($this->config->getWasabiPrefix(), '/');
        $locationId = preg_replace('/[^a-zA-Z0-9_-]/', '', $locationId) ?: 'unknown';
        $estimateId = preg_replace('/[^a-zA-Z0-9_-]/', '', $estimateId) ?: 'unknown';
        $ext = strtolower(preg_replace('/[^a-z0-9]/', '', $extension) ?: 'jpg');
        $uuid = wp_generate_uuid4();

        return sprintf(
            '%s/%s/%s/%s/%s/%s.%s',
            $prefix,
            $locationId,
            $estimateId,
            gmdate('Y'),
            gmdate('m'),
            $uuid,
            $ext
        );
    }

    private function sign(string $secretKey, string $dateStamp, string $region, string $stringToSign): string
    {
        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4' . $secretKey, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', 's3', $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

        return hash_hmac('sha256', $stringToSign, $kSigning);
    }

    /**
     * Path-encode for S3 canonical URI (encode each segment, keep slashes).
     */
    private function rawurlencodePath(string $path): string
    {
        $parts = explode('/', $path);
        $encoded = array_map(static function (string $part): string {
            return str_replace('%7E', '~', rawurlencode($part));
        }, $parts);

        return implode('/', $encoded);
    }
}
