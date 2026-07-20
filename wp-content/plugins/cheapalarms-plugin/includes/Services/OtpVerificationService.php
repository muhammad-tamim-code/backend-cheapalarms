<?php

namespace CheapAlarms\Plugin\Services;

use CheapAlarms\Plugin\Config\Config;
use CheapAlarms\Plugin\Support\AustralianPhone;
use WP_Error;

use function __;
use function base64_encode;
use function delete_transient;
use function get_transient;
use function is_wp_error;
use function random_int;
use function set_transient;
use function wp_json_encode;
use function wp_remote_post;
use function wp_remote_retrieve_body;
use function wp_remote_retrieve_response_code;

/**
 * SMS OTP for quote submissions (calculator + chat). Prices stay server-side until verified submit.
 */
class OtpVerificationService
{
    private const CODE_TTL_SECONDS         = 600;
    private const VERIFIED_TOKEN_TTL       = 900;
    private const SEND_COOLDOWN_SECONDS    = 60;
    private const MAX_SENDS_PER_HOUR       = 5;

    public function __construct(
        private Config $config,
        private Logger $logger
    ) {
    }

    /**
     * @return array{ok: true, expiresIn: int, demo?: bool}|WP_Error
     */
    public function sendCode(string $phoneRaw): array|WP_Error
    {
        $phone = AustralianPhone::toE164($phoneRaw);
        if ($phone === null) {
            return new WP_Error(
                'invalid_phone',
                __('Please enter a valid Australian mobile number (e.g. 04XX XXX XXX).', 'cheapalarms'),
                ['status' => 400]
            );
        }

        $rateKey = 'ca_otp_send_rate_' . md5($phone);
        $sends   = (int) get_transient($rateKey);
        if ($sends >= self::MAX_SENDS_PER_HOUR) {
            return new WP_Error(
                'otp_rate_limit',
                __('Too many verification codes sent. Please wait an hour or call 1300 225 276.', 'cheapalarms'),
                ['status' => 429]
            );
        }

        $cooldownKey = 'ca_otp_cooldown_' . md5($phone);
        if (get_transient($cooldownKey) !== false) {
            return new WP_Error(
                'otp_cooldown',
                __('Please wait a minute before requesting another code.', 'cheapalarms'),
                ['status' => 429]
            );
        }

        $code = (string) random_int(100000, 999999);
        $codeKey = $this->codeTransientKey($phone);
        set_transient($codeKey, wp_hash_password($code), self::CODE_TTL_SECONDS);
        set_transient($cooldownKey, 1, self::SEND_COOLDOWN_SECONDS);
        set_transient($rateKey, $sends + 1, 3600);

        $brand = $this->config->getBrandName();
        $message = sprintf(
            '%s verification code: %s. Valid for 10 minutes.',
            $brand !== '' ? $brand : 'Safeguard',
            $code
        );

        $sent = $this->deliverSms($phone, $message);
        if (is_wp_error($sent)) {
            return $sent;
        }

        $response = [
            'ok'        => true,
            'expiresIn' => self::CODE_TTL_SECONDS,
        ];

        if ($this->config->isOtpDemoMode()) {
            $response['demo'] = true;
            $this->logger->info('OTP demo code generated', ['phone' => $phone, 'code' => $code]);
        }

        return $response;
    }

    /**
     * @return array{ok: true, otpVerifiedToken: string, expiresIn: int}|WP_Error
     */
    public function verifyCode(string $phoneRaw, string $code): array|WP_Error
    {
        $phone = AustralianPhone::toE164($phoneRaw);
        if ($phone === null) {
            return new WP_Error(
                'invalid_phone',
                __('Please enter a valid Australian mobile number.', 'cheapalarms'),
                ['status' => 400]
            );
        }

        $code = preg_replace('/\D/u', '', $code) ?? '';
        if (strlen($code) !== 6) {
            return new WP_Error(
                'invalid_code',
                __('Enter the 6-digit code we sent to your mobile.', 'cheapalarms'),
                ['status' => 400]
            );
        }

        if (!$this->config->isOtpDemoMode()) {
            $hash = get_transient($this->codeTransientKey($phone));
            if (!is_string($hash) || $hash === '' || !wp_check_password($code, $hash)) {
                return new WP_Error(
                    'invalid_code',
                    __('That code is incorrect or expired. Request a new code and try again.', 'cheapalarms'),
                    ['status' => 400]
                );
            }

            delete_transient($this->codeTransientKey($phone));
        }

        $token = bin2hex(random_bytes(16));
        set_transient(
            $this->verifiedTransientKey($phone),
            ['token' => $token, 'phone' => $phone],
            self::VERIFIED_TOKEN_TTL
        );

        return [
            'ok'               => true,
            'otpVerifiedToken' => $token,
            'expiresIn'        => self::VERIFIED_TOKEN_TTL,
        ];
    }

    public function consumeVerifiedToken(string $phoneE164, string $token): bool
    {
        if ($phoneE164 === '' || $token === '') {
            return false;
        }

        $stored = get_transient($this->verifiedTransientKey($phoneE164));
        if (!is_array($stored) || ($stored['token'] ?? '') !== $token) {
            return false;
        }

        delete_transient($this->verifiedTransientKey($phoneE164));

        return true;
    }

    private function codeTransientKey(string $phoneE164): string
    {
        return 'ca_otp_code_' . md5($phoneE164);
    }

    private function verifiedTransientKey(string $phoneE164): string
    {
        return 'ca_otp_verified_' . md5($phoneE164);
    }

    /**
     * @return true|WP_Error
     */
    private function deliverSms(string $phoneE164, string $body): bool|WP_Error
    {
        $sid   = $this->config->getTwilioAccountSid();
        $token = $this->config->getTwilioAuthToken();
        $from  = $this->config->getTwilioFromNumber();

        if ($this->config->isOtpDemoMode()) {
            return true;
        }

        if ($sid === '' || $token === '' || $from === '') {
            return new WP_Error(
                'sms_not_configured',
                __('SMS verification is not configured. Please call 1300 225 276.', 'cheapalarms'),
                ['status' => 503]
            );
        }

        $url = 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode($sid) . '/Messages.json';
        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($sid . ':' . $token),
            ],
            'body'    => [
                'To'   => $phoneE164,
                'From' => $from,
                'Body' => $body,
            ],
            'timeout' => 20,
        ]);

        if (is_wp_error($response)) {
            $this->logger->error('Twilio SMS failed', ['error' => $response->get_error_message()]);

            return new WP_Error(
                'sms_failed',
                __('Could not send verification code. Please try again.', 'cheapalarms'),
                ['status' => 502]
            );
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        if ($status < 200 || $status >= 300) {
            $this->logger->error('Twilio SMS error response', [
                'status' => $status,
                'body'   => wp_remote_retrieve_body($response),
            ]);

            return new WP_Error(
                'sms_failed',
                __('Could not send verification code. Please try again.', 'cheapalarms'),
                ['status' => 502]
            );
        }

        return true;
    }
}
