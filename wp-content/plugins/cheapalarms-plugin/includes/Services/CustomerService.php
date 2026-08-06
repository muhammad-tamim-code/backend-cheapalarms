<?php

namespace CheapAlarms\Plugin\Services;

use WP_Error;
use WP_User;

use CheapAlarms\Plugin\Config\CacheConfig;
use CheapAlarms\Plugin\Config\Config;
use CheapAlarms\Plugin\Services\Contact\ContactSnapshotRepository;
use CheapAlarms\Plugin\Services\GhlClient;
use CheapAlarms\Plugin\Services\Logger;
use CheapAlarms\Plugin\Services\Container;

use function add_query_arg;
use function email_exists;
use function esc_html;
use function esc_url;
use function get_password_reset_key;
use function get_user_by;
use function is_wp_error;
use function rawurlencode;
use function sanitize_email;
use function sanitize_text_field;
use function sprintf;
use function strcasecmp;
use function stripos;
use function trailingslashit;
use function trim;
use function update_user_meta;
use function wp_create_user;
use function wp_generate_password;
use function wp_mail;
use function wp_update_user;
use function __;

class CustomerService
{
    public function __construct(
        private GhlClient $ghlClient,
        private Logger $logger,
        private Container $container
    ) {
    }

    /**
     * Links a GHL contact to a WordPress user
     * Stores ghl_contact_id in user meta
     */
    public function linkGhlContact(int $userId, string $ghlContactId): bool
    {
        $result = update_user_meta($userId, 'ghl_contact_id', sanitize_text_field($ghlContactId));
        return $result !== false;
    }

    /**
     * Creates WordPress user from GHL contact
     * Returns user ID or WP_Error
     */
    public function createUserFromGhlContact(array $ghlContact): int|WP_Error
    {
        $email = sanitize_email($ghlContact['email'] ?? '');
        if (!$email) {
            return new WP_Error('no_email', 'GHL contact must have email to create WordPress user');
        }

        // Check if user already exists
        $userId = email_exists($email);
        if ($userId) {
            return $userId; // User already exists
        }

        // Create user
        $password = wp_generate_password(20);
        $userId = wp_create_user($email, $password, $email);
        if (is_wp_error($userId)) {
            return $userId;
        }

        // Update user details
        wp_update_user([
            'ID' => $userId,
            'first_name' => sanitize_text_field($ghlContact['firstName'] ?? ''),
            'last_name' => sanitize_text_field($ghlContact['lastName'] ?? ''),
            'role' => 'ca_customer', // Use ca_customer role (has ca_access_portal capability)
        ]);

        // Link GHL contact
        if (!empty($ghlContact['id'])) {
            update_user_meta($userId, 'ghl_contact_id', sanitize_text_field($ghlContact['id']));
        }

        $this->logger->info('WordPress user created from GHL contact', [
            'userId' => $userId,
            'email' => $email,
            'ghlContactId' => $ghlContact['id'] ?? null,
        ]);

        return $userId;
    }

    /**
     * Sends portal invite to GHL contact (without estimate requirement)
     * Creates WP user if needed, sends password reset link
     */
    public function inviteGhlContactToPortal(string $ghlContactId, array $ghlContact): array|WP_Error
    {
        $email = sanitize_email($ghlContact['email'] ?? '');
        if (!$email) {
            return new WP_Error('no_email', 'GHL contact must have email to receive portal invite');
        }

        // Create or get WP user
        $userId = email_exists($email);
        if (!$userId) {
            $result = $this->createUserFromGhlContact($ghlContact);
            if (is_wp_error($result)) {
                return $result;
            }
            $userId = $result;
        }

        // Get user object (required for role check and password reset key generation)
        $user = get_user_by('id', $userId);
        if (!$user) {
            return new WP_Error('user_not_found', 'User not found after creation');
        }

        // Ensure user has ca_customer role (has ca_access_portal capability)
        if (!in_array('ca_customer', $user->roles, true)) {
            wp_update_user(['ID' => $userId, 'role' => 'ca_customer']);
        }

        // Link GHL contact
        $this->linkGhlContact($userId, $ghlContactId);

        // Use frontend URL (Next.js on Vercel) instead of WordPress backend URL
        $config = $this->container->get(\CheapAlarms\Plugin\Config\Config::class);
        $frontendUrl = $config->getFrontendUrl();
        $portalUrl = trailingslashit($frontendUrl) . 'portal';
        $firstName = sanitize_text_field($ghlContact['firstName'] ?? 'Customer');
        $lastName = sanitize_text_field($ghlContact['lastName'] ?? '');
        $name = trim($firstName . ' ' . $lastName) ?: 'Customer';

        // Generate password reset key pointing to Next.js frontend
        $key = get_password_reset_key($user);
        $resetUrl = null;
        if (!is_wp_error($key)) {
            $resetUrl = add_query_arg(
                [
                    'key' => $key,
                    'login' => rawurlencode($user->user_login),
                ],
                trailingslashit($frontendUrl) . 'set-password'
            );
        }

        // Get user context for email personalization
        $userContext = \CheapAlarms\Plugin\Services\UserContextHelper::getUserContext($userId, $email);

        // Render context-aware email template
        $emailTemplate = null;
        $brandName = $config->getBrandName();
        $brandTeam = $brandName . ' Team';
        try {
            $emailTemplateService = $this->container->get(\CheapAlarms\Plugin\Services\EmailTemplateService::class);
            $emailData = [
                'customerName' => $name,
                'portalUrl' => $portalUrl,
                'resetUrl' => $resetUrl,
                'isResend' => false,
                'estimateNumber' => '',
            ];

            $emailTemplate = $emailTemplateService->renderPortalInviteEmail($userContext, $emailData);
            $subject = $emailTemplate['subject'] ?? sprintf(__('Your %s portal is ready', 'cheapalarms'), $brandName);
            $body = $emailTemplate['body'] ?? '';

            // Fallback if template rendering failed
            if (empty($body)) {
                error_log('[CA][WARNING] Portal invite email template returned empty body, using fallback');
                $subject = sprintf(__('Your %s portal is ready', 'cheapalarms'), $brandName);
                $body = '<p>' . esc_html(sprintf(__('Hi %s,', 'cheapalarms'), $name)) . '</p>';
                $body .= '<p>' . esc_html(sprintf(__('We have prepared your %s portal. Use the links below to access your portal.', 'cheapalarms'), $brandName)) . '</p>';
                $body .= '<p>' . EmailTemplateHtmlHelper::inlineCtaButton($config, $portalUrl, __('Access your portal', 'cheapalarms')) . '</p>';
                if ($resetUrl) {
                    $body .= '<p><a href="' . esc_url($resetUrl) . '" style="color: #2fb6c9; text-decoration: underline;">' . esc_html(__('Set your password', 'cheapalarms')) . '</a></p>';
                }
                $body .= '<p>' . esc_html(__('Thanks,', 'cheapalarms')) . '<br />' . esc_html($brandTeam) . '</p>';
            }
        } catch (\Exception $e) {
            error_log('[CA][ERROR] Failed to render portal invite email template: ' . $e->getMessage());
            // Fallback to simple email
            $subject = sprintf(__('Your %s portal is ready', 'cheapalarms'), $brandName);
            $body = '<p>' . esc_html(sprintf(__('Hi %s,', 'cheapalarms'), $name)) . '</p>';
            $body .= '<p>' . esc_html(sprintf(__('We have prepared your %s portal. Use the links below to access your portal.', 'cheapalarms'), $brandName)) . '</p>';
            $body .= '<p>' . EmailTemplateHtmlHelper::inlineCtaButton($config, $portalUrl, __('Access your portal', 'cheapalarms')) . '</p>';
            if ($resetUrl) {
                $body .= '<p><a href="' . esc_url($resetUrl) . '" style="color: #2fb6c9; text-decoration: underline;">' . esc_html(__('Set your password', 'cheapalarms')) . '</a></p>';
            }
            $body .= '<p>' . esc_html(__('Thanks,', 'cheapalarms')) . '<br />' . esc_html($brandTeam) . '</p>';
        }

        // Send via GHL
        $sent = false;
        if ($ghlContactId) {
            $ghlClient = $this->container->get(GhlClient::class);
            $config = $this->container->get(\CheapAlarms\Plugin\Config\Config::class);
            $payload = [
                'contactId' => $ghlContactId,
                'type' => 'Email',
                'status' => 'pending',
                'subject' => $subject,
                'html' => $body,
                'emailFrom' => $config->getEmailFromHeader(),
            ];
            
            if ($config->getLocationId()) {
                $payload['locationId'] = $config->getLocationId();
            }
            
            $result = $ghlClient->post('/conversations/messages', $payload);
            $sent = !is_wp_error($result);
        }

        $this->logger->info('Portal invite sent to GHL contact via GHL email', [
            'ghlContactId' => $ghlContactId,
            'userId' => $userId,
            'email' => $email,
            'sentViaGhl' => $sent,
        ]);

        return [
            'ok' => true,
            'userId' => $userId,
            'inviteSent' => $sent,
            'resetUrl' => $resetUrl,
        ];
    }

    /**
     * Send a set-password invite for an existing WP user.
     * - Customers: GHL Conversations API (requires a CRM contact — correct for customers).
     * - Staff/Owner: wp_mail only — never create/link a GHL contact (staff are not customers).
     *
     * Does not change the user's product role. Used by admin Team "Add user".
     *
     * @return array{ok: bool, inviteSent: bool, channel: string, ghlContactId: ?string, resetUrl: ?string}|WP_Error
     */
    public function sendAccountInviteEmail(WP_User $user): array|WP_Error
    {
        $email = sanitize_email($user->user_email ?? '');
        if ($email === '') {
            return new WP_Error('no_email', __('User must have an email to receive an invite.', 'cheapalarms'), ['status' => 400]);
        }

        /** @var Config $config */
        $config = $this->container->get(Config::class);
        $authorization = $this->container->get(AuthorizationService::class);
        $resolved = $authorization->resolveForUser($user);
        $isAdminRole = !empty($resolved['is_admin']);

        $frontendUrl = $config->getFrontendUrl();
        $accessPath = $isAdminRole ? 'admin' : 'portal';
        $portalUrl = trailingslashit($frontendUrl) . $accessPath;
        $name = trim((string) ($user->display_name ?: $user->first_name ?: '')) ?: 'there';

        $key = get_password_reset_key($user);
        if (is_wp_error($key)) {
            return new WP_Error(
                'reset_key_failed',
                __('Could not generate a set-password link for this user.', 'cheapalarms'),
                ['status' => 500]
            );
        }

        $resetUrl = add_query_arg(
            [
                'key' => $key,
                'login' => rawurlencode($user->user_login),
            ],
            trailingslashit($frontendUrl) . 'set-password'
        );

        [$subject, $body] = $this->buildAccountInviteEmailContent(
            $user,
            $email,
            $name,
            $portalUrl,
            $resetUrl,
            $config
        );

        if ($isAdminRole) {
            return $this->sendAccountInviteViaWpMail($user, $email, $subject, $body, $resetUrl, $accessPath);
        }

        return $this->sendAccountInviteViaGhl($user, $email, $subject, $body, $resetUrl, $accessPath, $config);
    }

    /**
     * @return array{0: string, 1: string} [subject, html body]
     */
    private function buildAccountInviteEmailContent(
        WP_User $user,
        string $email,
        string $name,
        string $portalUrl,
        string $resetUrl,
        Config $config
    ): array {
        $brandName = $config->getBrandName();
        $brandTeam = $brandName . ' Team';
        $subject = sprintf(__('Your %s account is ready', 'cheapalarms'), $brandName);
        $body = '';

        try {
            $userContext = UserContextHelper::getUserContext((int) $user->ID, $email);
            $emailTemplateService = $this->container->get(EmailTemplateService::class);
            $emailTemplate = $emailTemplateService->renderPortalInviteEmail($userContext, [
                'customerName' => $name,
                'portalUrl' => $portalUrl,
                'resetUrl' => $resetUrl,
                'isResend' => false,
                'estimateNumber' => '',
            ]);
            $subject = $emailTemplate['subject'] ?? $subject;
            $body = $emailTemplate['body'] ?? '';
        } catch (\Exception $e) {
            $this->logger->error('Failed to render account invite email template', [
                'email' => $email,
                'userId' => $user->ID,
                'error' => $e->getMessage(),
            ]);
        }

        if ($body === '') {
            $body = '<p>' . esc_html(sprintf(__('Hi %s,', 'cheapalarms'), $name)) . '</p>';
            $body .= '<p>' . esc_html(sprintf(__('An account has been created for you on %s. Use the links below to set your password and sign in.', 'cheapalarms'), $brandName)) . '</p>';
            $body .= '<p>' . EmailTemplateHtmlHelper::inlineCtaButton($config, $resetUrl, __('Set your password', 'cheapalarms')) . '</p>';
            $body .= '<p><a href="' . esc_url($portalUrl) . '" style="color: #2fb6c9; text-decoration: underline;">' . esc_html(__('Open your account', 'cheapalarms')) . '</a></p>';
            $body .= '<p>' . esc_html(__('Thanks,', 'cheapalarms')) . '<br />' . esc_html($brandTeam) . '</p>';
        }

        return [$subject, $body];
    }

    /**
     * @return array{ok: bool, inviteSent: bool, channel: string, ghlContactId: null, resetUrl: string}|WP_Error
     */
    private function sendAccountInviteViaWpMail(
        WP_User $user,
        string $email,
        string $subject,
        string $body,
        string $resetUrl,
        string $accessPath
    ): array|WP_Error {
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        $sent = wp_mail($email, $subject, $body, $headers);

        if (!$sent) {
            $this->logger->error('Failed to send staff account invite via wp_mail', [
                'email' => $email,
                'userId' => $user->ID,
                'accessPath' => $accessPath,
            ]);

            return new WP_Error(
                'invite_send_failed',
                __('Staff invite email could not be sent. Please check WordPress mail configuration and try again.', 'cheapalarms'),
                ['status' => 502]
            );
        }

        $this->logger->info('Staff account invite sent via wp_mail (no GHL contact created)', [
            'email' => $email,
            'userId' => $user->ID,
            'accessPath' => $accessPath,
        ]);

        return [
            'ok' => true,
            'inviteSent' => true,
            'channel' => 'wp_mail',
            'ghlContactId' => null,
            'resetUrl' => $resetUrl,
        ];
    }

    /**
     * @return array{ok: bool, inviteSent: bool, channel: string, ghlContactId: string, resetUrl: string}|WP_Error
     */
    private function sendAccountInviteViaGhl(
        WP_User $user,
        string $email,
        string $subject,
        string $body,
        string $resetUrl,
        string $accessPath,
        Config $config
    ): array|WP_Error {
        $locationId = $config->getLocationId();
        if ($locationId === '') {
            return new WP_Error(
                'ghl_location_missing',
                __('Cannot send invite: GoHighLevel location is not configured.', 'cheapalarms'),
                ['status' => 500]
            );
        }

        $contactId = $this->findOrCreateGhlContactForUser($user, $email, $locationId);
        if (is_wp_error($contactId)) {
            return $contactId;
        }

        $this->linkGhlContact((int) $user->ID, $contactId);

        $payload = [
            'contactId' => $contactId,
            'type' => 'Email',
            'status' => 'pending',
            'subject' => $subject,
            'html' => $body,
            'emailFrom' => $config->getEmailFromHeader(),
            'locationId' => $locationId,
        ];

        $result = $this->ghlClient->post('/conversations/messages', $payload, 30, $locationId);
        if (is_wp_error($result)) {
            $this->logger->error('Failed to send account invite via GHL', [
                'email' => $email,
                'userId' => $user->ID,
                'ghlContactId' => $contactId,
                'error' => $result->get_error_message(),
            ]);

            return new WP_Error(
                'invite_send_failed',
                __('Account invite email could not be sent via GoHighLevel. Please try again.', 'cheapalarms'),
                ['status' => 502, 'ghlContactId' => $contactId]
            );
        }

        $this->logger->info('Customer account invite sent via GHL', [
            'email' => $email,
            'userId' => $user->ID,
            'ghlContactId' => $contactId,
            'accessPath' => $accessPath,
        ]);

        return [
            'ok' => true,
            'inviteSent' => true,
            'channel' => 'ghl',
            'ghlContactId' => $contactId,
            'resetUrl' => $resetUrl,
        ];
    }

    /**
     * Find or create a GHL contact for the given WP user email.
     *
     * @return string|WP_Error GHL contact ID
     */
    private function findOrCreateGhlContactForUser(WP_User $user, string $email, string $locationId): string|WP_Error
    {
        $existingMeta = get_user_meta($user->ID, 'ghl_contact_id', true);
        if (is_string($existingMeta) && $existingMeta !== '') {
            return sanitize_text_field($existingMeta);
        }

        try {
            /** @var ContactSnapshotRepository $contactRepo */
            $contactRepo = $this->container->get(ContactSnapshotRepository::class);
            $local = $contactRepo->findByEmail($email, $locationId);
            if ($local !== null && !is_wp_error($local)) {
                $syncedAt = $local['syncedAt'] ?? null;
                $config = $this->container->get(Config::class);
                if (CacheConfig::isFresh($syncedAt, CacheConfig::CONTACT_SEARCH_STALE_SECONDS) || !$config->isGhlFetchAllowed()) {
                    $localContactId = $local['contactId'] ?? null;
                    if (!empty($localContactId)) {
                        return (string) $localContactId;
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Contact snapshot lookup failed in sendAccountInviteEmail', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }

        /** @var Config $config */
        $config = $this->container->get(Config::class);
        if ($config->isGhlFetchAllowed()) {
            $response = $this->ghlClient->get('/contacts/search', [
                'query' => $email,
                'locationId' => $locationId,
            ], 20, $locationId);

            if (!is_wp_error($response)) {
                $contacts = $response['contacts'] ?? $response['items'] ?? [];
                foreach ($contacts as $contact) {
                    $contactEmail = $contact['email'] ?? '';
                    if ($contactEmail && strcasecmp($contactEmail, $email) === 0) {
                        $foundId = $contact['id'] ?? '';
                        if ($foundId !== '') {
                            $this->writeThroughContact($locationId, $contact);
                            return (string) $foundId;
                        }
                    }
                }
            }
        }

        $firstName = (string) ($user->first_name ?: '');
        $lastName = (string) ($user->last_name ?: '');
        if ($firstName === '' && $lastName === '') {
            $parts = explode(' ', (string) ($user->display_name ?: $user->user_login), 2);
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? '';
        }

        $contactData = [
            'email' => $email,
            'locationId' => $locationId,
        ];
        if ($firstName !== '') {
            $contactData['firstName'] = $firstName;
        }
        if ($lastName !== '') {
            $contactData['lastName'] = $lastName;
        }

        $createResponse = $this->ghlClient->post('/contacts/', $contactData, 30, $locationId);
        if (is_wp_error($createResponse)) {
            $duplicateId = $this->extractDuplicateContactId($createResponse);
            if ($duplicateId !== null) {
                $this->writeThroughContact($locationId, [
                    'id' => $duplicateId,
                    'email' => $email,
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                ]);
                return $duplicateId;
            }

            if ($config->isGhlFetchAllowed()) {
                $retry = $this->ghlClient->get('/contacts/search', [
                    'query' => $email,
                    'locationId' => $locationId,
                ], 20, $locationId);
                if (!is_wp_error($retry)) {
                    foreach (($retry['contacts'] ?? $retry['items'] ?? []) as $contact) {
                        $contactEmail = $contact['email'] ?? '';
                        if ($contactEmail && strcasecmp($contactEmail, $email) === 0 && !empty($contact['id'])) {
                            $this->writeThroughContact($locationId, $contact);
                            return (string) $contact['id'];
                        }
                    }
                }
            }

            return new WP_Error(
                'ghl_contact_failed',
                __('Could not create or find a GoHighLevel contact for this email.', 'cheapalarms'),
                ['status' => 502]
            );
        }

        $contactId = $createResponse['contact']['id']
            ?? $createResponse['id']
            ?? $createResponse['contactId']
            ?? '';

        if ($contactId === '') {
            return new WP_Error(
                'ghl_contact_creation_failed',
                __('Contact was created but no contact ID was returned.', 'cheapalarms'),
                ['status' => 500]
            );
        }

        $writeData = is_array($createResponse['contact'] ?? null)
            ? $createResponse['contact']
            : ['id' => $contactId, 'email' => $email];
        if (empty($writeData['email'])) {
            $writeData['email'] = $email;
        }
        $this->writeThroughContact($locationId, $writeData);

        return (string) $contactId;
    }

    private function extractDuplicateContactId(WP_Error $error): ?string
    {
        $errorData = $error->get_error_data();
        if (!is_array($errorData) || ($errorData['code'] ?? null) !== 400) {
            return null;
        }

        $errorBody = $errorData['body'] ?? null;
        if (is_string($errorBody)) {
            $decoded = json_decode($errorBody, true);
            $errorBody = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        if (!is_array($errorBody)) {
            return null;
        }

        $message = (string) ($errorBody['message'] ?? '');
        $contactId = $errorBody['meta']['contactId'] ?? '';
        $looksDuplicate = $contactId !== ''
            || stripos($message, 'duplicate') !== false
            || stripos($message, 'duplicated') !== false;

        if (!$looksDuplicate || $contactId === '') {
            return null;
        }

        return sanitize_text_field((string) $contactId);
    }

    private function writeThroughContact(string $locationId, array $contactData): void
    {
        $contactId = $contactData['id'] ?? $contactData['_id'] ?? $contactData['contactId'] ?? null;
        if (!$contactId) {
            return;
        }

        try {
            $contactRepo = $this->container->get(ContactSnapshotRepository::class);
            $record = ContactSnapshotRepository::normalizeFromGhl($contactData);
            $res = $contactRepo->upsertOne($locationId, $record);
            if (is_wp_error($res)) {
                $this->logger->warning('Contact write-through failed', [
                    'contactId' => $contactId,
                    'error' => $res->get_error_message(),
                ]);
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Contact write-through exception', [
                'contactId' => $contactId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Matches GHL contact to existing WP user by email
     * Returns WP user ID or null
     */
    public function findMatchingUser(string $email, ?string $phone = null): ?int
    {
        // Try email first (primary match)
        if ($email) {
            $userId = email_exists($email);
            if ($userId) {
                return $userId;
            }
        }

        // Phone matching could be added here later if needed
        // For now, email-only matching is sufficient

        return null;
    }
}

