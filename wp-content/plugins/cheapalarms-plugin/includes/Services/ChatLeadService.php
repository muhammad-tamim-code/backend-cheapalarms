<?php

namespace CheapAlarms\Plugin\Services;

use CheapAlarms\Plugin\Config\Config;
use CheapAlarms\Plugin\Support\AustralianPhone;
use WP_Error;

use function sanitize_text_field;
use function sanitize_email;
use function wp_strip_all_tags;
use function current_time;
use function get_transient;
use function set_transient;
use function delete_transient;
use function is_wp_error;
use function __;

/**
 * Captures website chat leads into GHL (contact + timeline note + tags).
 */
class ChatLeadService
{
    private const ALLOWED_INTENTS = [
        'quote',
        'which_system',
        'general',
        'access_control',
        'cctv',
        'alarms',
        'intercom',
        'monitoring',
        'agent_handoff',
    ];

    private const ALLOWED_PROPERTY_TYPES = [
        'home',
        'apartment',
        'business',
        'other',
    ];

    private const MAX_TRANSCRIPT_ENTRIES = 12;

    public function __construct(
        private GhlClient $ghlClient,
        private GhlSignalService $signalService,
        private Config $config,
        private Logger $logger
    ) {
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>|WP_Error
     */
    public function submit(array $body): array|WP_Error
    {
        if ($this->config->getGhlToken() === '' || $this->config->getLocationId() === '') {
            return new WP_Error(
                'ghl_not_configured',
                __('Lead capture is temporarily unavailable. Please call us on 1300 225 276.', 'cheapalarms'),
                ['status' => 503]
            );
        }

        $firstName = sanitize_text_field((string) ($body['firstName'] ?? ''));
        $lastName  = sanitize_text_field((string) ($body['lastName'] ?? ''));
        $phoneRaw  = sanitize_text_field((string) ($body['phone'] ?? ''));
        $email     = sanitize_email((string) ($body['email'] ?? ''));
        $suburb    = sanitize_text_field((string) ($body['suburb'] ?? ''));
        $address   = sanitize_text_field((string) ($body['address'] ?? ''));
        $intent    = sanitize_text_field((string) ($body['intent'] ?? 'quote'));
        $property  = sanitize_text_field((string) ($body['propertyType'] ?? ''));
        $pagePath  = sanitize_text_field((string) ($body['pagePath'] ?? ''));
        $pageTitle = sanitize_text_field((string) ($body['pageTitle'] ?? ''));

        if (!in_array($intent, self::ALLOWED_INTENTS, true)) {
            $intent = 'quote';
        }

        $isHandoff = $intent === 'agent_handoff';

        if ($firstName === '' || $lastName === '' || $phoneRaw === '') {
            return new WP_Error(
                'missing_params',
                __('Please enter your first name, last name, and phone number.', 'cheapalarms'),
                ['status' => 400]
            );
        }

        if ($isHandoff) {
            if ($email === '') {
                return new WP_Error(
                    'missing_email',
                    __('Please enter your email address so our team can follow up.', 'cheapalarms'),
                    ['status' => 400]
                );
            }
            if ($address === '' && $suburb === '') {
                return new WP_Error(
                    'missing_address',
                    __('Please enter your address or suburb.', 'cheapalarms'),
                    ['status' => 400]
                );
            }
            if ($address === '') {
                $address = $suburb;
            }
            if ($suburb === '') {
                $suburb = $address;
            }

            // Temporary: any number with more than 9 digits (no AU format check).
            $digitsOnly = preg_replace('/\D+/', '', $phoneRaw) ?? '';
            if (strlen($digitsOnly) <= 9) {
                return new WP_Error(
                    'invalid_phone',
                    __('Please enter a phone number with more than 9 digits.', 'cheapalarms'),
                    ['status' => 400]
                );
            }
            $phone = $digitsOnly;
        } else {
            $phone = AustralianPhone::toE164($phoneRaw);
            if ($phone === null) {
                return new WP_Error(
                    'invalid_phone',
                    __('Please enter a valid Australian phone number (e.g. 04XX XXX XXX).', 'cheapalarms'),
                    ['status' => 400]
                );
            }
        }

        if ($property !== '' && !in_array($property, self::ALLOWED_PROPERTY_TYPES, true)) {
            $property = 'other';
        }

        if ($email === '') {
            $email = $this->syntheticEmailFromPhone($phone);
        }

        $lockKey = 'ca_chat_lead_lock_' . md5($phone);
        $lockVal = get_transient($lockKey);
        if ($lockVal !== false) {
            return new WP_Error(
                'duplicate_request',
                __('We already received your details, our team will call you shortly.', 'cheapalarms'),
                ['status' => 429]
            );
        }

        set_transient($lockKey, time(), 120);

        $locationId = $this->config->getLocationId();
        $contactId  = $this->resolveOrCreateContact($firstName, $lastName, $email, $phone, $locationId);

        if (is_wp_error($contactId)) {
            delete_transient($lockKey);

            return $contactId;
        }

        $tags = ['website_chat', 'chat_lead', 'chat_intent_' . $intent];
        if ($isHandoff) {
            $tags[] = 'chat_agent_handoff';
        }
        foreach ($tags as $tag) {
            $tagResult = $this->signalService->mergePortalTag($contactId, $locationId, $tag);
            if (is_wp_error($tagResult)) {
                $this->logger->warning('Chat lead tag merge failed (non-blocking)', [
                    'contactId' => $contactId,
                    'tag'       => $tag,
                    'error'     => $tagResult->get_error_message(),
                ]);
            }
        }

        $transcript = $this->normalizeTranscript($body['transcript'] ?? null);
        $noteBody   = $this->buildNoteBody(
            $intent,
            $property,
            $suburb,
            $pagePath,
            $pageTitle,
            $transcript,
            $address
        );

        $noteResult = $this->signalService->postContactTimelineNote($contactId, $locationId, $noteBody);
        if (is_wp_error($noteResult)) {
            $this->logger->warning('Chat lead timeline note failed (non-blocking)', [
                'contactId' => $contactId,
                'error'     => $noteResult->get_error_message(),
            ]);
        }

        $this->logger->info('Website chat lead captured', [
            'contactId' => $contactId,
            'intent'    => $intent,
            'suburb'    => $suburb,
        ]);

        $message = $isHandoff
            ? __('Thanks, we\'ve got your details. Connecting you with our team now…', 'cheapalarms')
            : __('Thanks, our team will call you shortly.', 'cheapalarms');

        return [
            'ok'        => true,
            'contactId' => $contactId,
            'intent'    => $intent,
            'handoff'   => $isHandoff,
            'contact'   => [
                'firstName' => $firstName,
                'lastName'  => $lastName,
                'name'      => trim($firstName . ' ' . $lastName),
                'email'     => $email,
                'phone'     => $phone,
                'address'   => $address !== '' ? $address : $suburb,
            ],
            'message'   => $message,
        ];
    }

    /**
     * @return string|WP_Error GHL contact ID
     */
    private function resolveOrCreateContact(
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        string $locationId
    ): string|WP_Error {
        $existingId = $this->findContactByPhoneOrEmail($phone, $email, $locationId);
        if ($existingId !== null) {
            return $existingId;
        }

        $payload = [
            'firstName'  => $firstName,
            'lastName'   => $lastName,
            'email'      => $email,
            'phone'      => $phone,
            'locationId' => $locationId,
        ];

        $result = $this->ghlClient->post('/contacts/', $payload, 15, $locationId);
        if (!is_wp_error($result)) {
            $id = $result['contact']['id'] ?? $result['id'] ?? $result['contactId'] ?? null;

            return is_string($id) && $id !== '' ? $id : new WP_Error(
                'ghl_contact_missing_id',
                __('Could not save your details. Please call 1300 225 276.', 'cheapalarms'),
                ['status' => 502]
            );
        }

        $errorData = $result->get_error_data();
        if ($result->get_error_code() === 'ghl_http_error' && isset($errorData['code']) && (int) $errorData['code'] === 400) {
            $errorBody = $errorData['body'] ?? null;
            if (is_string($errorBody)) {
                $decoded = json_decode($errorBody, true);
                if (is_array($decoded)) {
                    $errorBody = $decoded;
                }
            }

            if (is_array($errorBody)) {
                $contactId     = $errorBody['meta']['contactId'] ?? null;
                $matchingField = $errorBody['meta']['matchingField'] ?? null;

                if (is_string($contactId) && $contactId !== '') {
                    if (is_string($matchingField) && strtolower($matchingField) === 'phone') {
                        return $contactId;
                    }

                    if (is_string($matchingField) && strtolower($matchingField) === 'email') {
                        return $contactId;
                    }

                    if ($matchingField === null || $matchingField === '') {
                        return $contactId;
                    }
                }
            }
        }

        $this->logger->error('Chat lead contact creation failed', [
            'error' => $result->get_error_message(),
        ]);

        return new WP_Error(
            'ghl_contact_failed',
            __('Could not save your details. Please call 1300 225 276.', 'cheapalarms'),
            ['status' => 502]
        );
    }

    private function findContactByPhoneOrEmail(string $phone, string $email, string $locationId): ?string
    {
        if (!$this->config->isGhlFetchAllowed()) {
            return null;
        }

        foreach ([$phone, $email] as $query) {
            if ($query === '') {
                continue;
            }

            $search = $this->ghlClient->get('/contacts/search', ['query' => $query], 10, $locationId);
            if (is_wp_error($search)) {
                continue;
            }

            $contacts = $search['contacts'] ?? $search['items'] ?? [];
            if (!is_array($contacts)) {
                continue;
            }

            foreach ($contacts as $contact) {
                if (!is_array($contact)) {
                    continue;
                }

                $id = $contact['id'] ?? null;
                if (!is_string($id) || $id === '') {
                    continue;
                }

                $contactPhone = AustralianPhone::toE164((string) ($contact['phone'] ?? ''));
                $contactEmail = strtolower((string) ($contact['email'] ?? ''));

                if ($contactPhone === $phone || ($email !== '' && $contactEmail === strtolower($email))) {
                    return $id;
                }
            }
        }

        return null;
    }

    /**
     * @param mixed $raw
     * @return array<int, array{role: string, content: string}>
     */
    private function normalizeTranscript($raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $entries = [];
        foreach ($raw as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $role = sanitize_text_field((string) ($entry['role'] ?? ''));
            if ($role !== 'user' && $role !== 'assistant') {
                continue;
            }

            $content = wp_strip_all_tags((string) ($entry['content'] ?? ''));
            $content = trim(preg_replace('/\s+/u', ' ', $content) ?? '');
            if ($content === '') {
                continue;
            }

            if (mb_strlen($content) > 500) {
                $content = mb_substr($content, 0, 497) . '…';
            }

            $entries[] = [
                'role'    => $role,
                'content' => $content,
            ];
        }

        if (count($entries) > self::MAX_TRANSCRIPT_ENTRIES) {
            $entries = array_slice($entries, -self::MAX_TRANSCRIPT_ENTRIES);
        }

        return $entries;
    }

    /**
     * @param array<int, array{role: string, content: string}> $transcript
     */
    private function buildNoteBody(
        string $intent,
        string $property,
        string $suburb,
        string $pagePath,
        string $pageTitle,
        array $transcript,
        string $address = ''
    ): string {
        $lines   = [];
        $lines[] = 'Website chat lead, ' . current_time('Y-m-d H:i');
        $lines[] = 'Intent: ' . $this->intentLabel($intent);

        if ($property !== '') {
            $lines[] = 'Property: ' . $this->propertyLabel($property);
        }

        if ($address !== '') {
            $lines[] = 'Address: ' . $address;
        }

        if ($suburb !== '' && $suburb !== $address) {
            $lines[] = 'Suburb: ' . $suburb;
        }

        if ($pagePath !== '') {
            $lines[] = 'Page: ' . $pagePath;
        }

        if ($pageTitle !== '') {
            $lines[] = 'Page title: ' . $pageTitle;
        }

        if ($transcript !== []) {
            $lines[] = '';
            $lines[] = 'Chat transcript:';
            foreach ($transcript as $entry) {
                $prefix = $entry['role'] === 'user' ? 'Visitor' : 'Assistant';
                $lines[] = $prefix . ': ' . $entry['content'];
            }
        }

        return implode("\n", $lines);
    }

    private function intentLabel(string $intent): string
    {
        return match ($intent) {
            'quote'          => 'Get a quote',
            'which_system'   => 'Which system do I need?',
            'access_control' => 'Access control',
            'cctv'           => 'CCTV',
            'alarms'         => 'Alarm systems',
            'intercom'       => 'Intercom',
            'monitoring'     => 'Monitoring',
            'agent_handoff'  => 'Live agent handoff',
            default          => 'General enquiry',
        };
    }

    private function propertyLabel(string $property): string
    {
        return match ($property) {
            'home'       => 'Home',
            'apartment'  => 'Apartment',
            'business'   => 'Business',
            default      => 'Other',
        };
    }

    private function syntheticEmailFromPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            $digits = 'unknown';
        }

        return 'chat+' . $digits . '@quotes.safeguard.local';
    }
}
