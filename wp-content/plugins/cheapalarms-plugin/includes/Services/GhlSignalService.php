<?php

namespace CheapAlarms\Plugin\Services;

use CheapAlarms\Plugin\Config\Config;
use WP_Error;

use function sanitize_text_field;
use function current_time;
use function __;

/**
 * Service for updating GHL signals (tags, notes) when portal events occur.
 * 
 * These updates are fire-and-forget: errors are logged but don't block the main flow.
 */
class GhlSignalService
{
    public function __construct(
        private GhlClient $client,
        private Logger $logger,
        private Config $config
    ) {
    }

    /**
     * Add "portal_accepted" tag to GHL contact.
     * 
     * @param string $contactId GHL contact ID
     * @param string $locationId GHL location ID
     * @return array|WP_Error Success response or error (non-blocking)
     */
    public function addAcceptanceTag(string $contactId, string $locationId): array|WP_Error
    {
        return $this->mergePortalTag($contactId, $locationId, 'portal_accepted');
    }

    /**
     * Merge a single portal tag onto the GHL contact (fetch-merge-put, with direct-tag fallback).
     *
     * @param string $tagName Tag string as stored in GHL (e.g. portal_deposit_paid).
     */
    public function mergePortalTag(string $contactId, string $locationId, string $tagName): array|WP_Error
    {
        $contactId = sanitize_text_field($contactId);
        $locationId = sanitize_text_field($locationId);
        $tagName = sanitize_text_field($tagName);

        if (!$contactId || !$locationId || $tagName === '') {
            return new WP_Error('missing_params', __('Contact ID and Location ID required.', 'cheapalarms'), ['status' => 400]);
        }

        try {
            $currentContact = $this->client->get('/contacts/' . rawurlencode($contactId), [], 25, $locationId);

            if (is_wp_error($currentContact)) {
                $this->logger->warning('Could not fetch contact for tag update, trying direct tag add', [
                    'contactId' => $contactId,
                    'tag'       => $tagName,
                    'error'     => $currentContact->get_error_message(),
                ]);

                return $this->addTagDirect($contactId, $locationId, $tagName);
            }

            $existingTags = $currentContact['tags'] ?? [];
            if (!is_array($existingTags)) {
                $existingTags = [];
            }

            if (in_array($tagName, $existingTags, true)) {
                $this->logger->info('GHL tag already exists on contact', [
                    'contactId' => $contactId,
                    'tag'       => $tagName,
                ]);

                return ['ok' => true, 'result' => $currentContact, 'tagAlreadyExists' => true];
            }

            $updatedTags = array_merge($existingTags, [$tagName]);

            $response = $this->client->put(
                '/contacts/' . rawurlencode($contactId),
                ['tags' => $updatedTags],
                [],
                30,
                $locationId
            );

            if (is_wp_error($response)) {
                $this->logger->warning('Failed to merge GHL portal tag', [
                    'contactId'  => $contactId,
                    'locationId'   => $locationId,
                    'tag'          => $tagName,
                    'error'        => $response->get_error_message(),
                    'errorData'    => $response->get_error_data(),
                ]);

                return $response;
            }

            $this->logger->info('GHL portal tag merged successfully', [
                'contactId'  => $contactId,
                'locationId' => $locationId,
                'tag'        => $tagName,
            ]);

            return ['ok' => true, 'result' => $response];
        } catch (\Exception $e) {
            $this->logger->error('Exception merging GHL portal tag', [
                'contactId' => $contactId,
                'locationId' => $locationId,
                'tag'       => $tagName,
                'exception' => $e->getMessage(),
            ]);

            return new WP_Error('ghl_tag_exception', $e->getMessage());
        }
    }

    /**
     * Fallback method to add tag directly (if GHL supports it).
     */
    private function addTagDirect(string $contactId, string $locationId, string $tagName): array|WP_Error
    {
        // Try POST /contacts/{contactId}/tags (may not be available in all GHL accounts)
        $response = $this->client->post(
            '/contacts/' . rawurlencode($contactId) . '/tags',
            ['tags' => [$tagName]],
            30,
            $locationId
        );

        if (is_wp_error($response)) {
            // This endpoint may not exist - that's okay, we'll just log it
            $this->logger->info('Direct tag endpoint not available, tag update skipped', [
                'contactId' => $contactId,
                'error' => $response->get_error_message(),
            ]);
            return $response;
        }

        return ['ok' => true, 'result' => $response];
    }

    /**
     * Add note to GHL contact timeline about estimate acceptance.
     * 
     * @param string $contactId GHL contact ID
     * @param string $locationId GHL location ID
     * @param array<string, mixed> $data Note data: estimateNumber, estimateId, acceptedAt, invoiceNumber (optional)
     * @return array|WP_Error Success response or error (non-blocking)
     */
    public function addAcceptanceNote(string $contactId, string $locationId, array $data): array|WP_Error
    {
        $contactId = sanitize_text_field($contactId);
        $locationId = sanitize_text_field($locationId);

        if (!$contactId || !$locationId) {
            return new WP_Error('missing_params', __('Contact ID and Location ID required.', 'cheapalarms'), ['status' => 400]);
        }

        $estimateNumber = sanitize_text_field($data['estimateNumber'] ?? '');
        $estimateId = sanitize_text_field($data['estimateId'] ?? '');
        $acceptedAt = sanitize_text_field($data['acceptedAt'] ?? current_time('mysql'));
        $invoiceNumber = sanitize_text_field($data['invoiceNumber'] ?? null);

        // Build note body
        $noteBody = sprintf(
            __('Customer accepted Estimate #%s via Portal on %s.', 'cheapalarms'),
            $estimateNumber ?: $estimateId,
            $acceptedAt
        );

        if ($invoiceNumber) {
            $noteBody .= ' ' . sprintf(__('Invoice created: %s.', 'cheapalarms'), $invoiceNumber);
        }

        $noteBody .= ' ' . __('Portal Status: Accepted (GHL estimate may still show draft status).', 'cheapalarms');

        return $this->postContactTimelineNote($contactId, $locationId, $noteBody);
    }

    /**
     * Post a plain-text note to the GHL contact timeline (shared by acceptance + payment milestones).
     */
    public function postContactTimelineNote(string $contactId, string $locationId, string $noteBody): array|WP_Error
    {
        $contactId = sanitize_text_field($contactId);
        $locationId = sanitize_text_field($locationId);
        $noteBody = is_string($noteBody) ? $noteBody : '';

        if (!$contactId || !$locationId || $noteBody === '') {
            return new WP_Error('missing_params', __('Contact ID, Location ID, and note body required.', 'cheapalarms'), ['status' => 400]);
        }

        try {
            $notePayload = [
                'body'      => $noteBody,
                'contactId' => $contactId,
            ];

            $response = $this->client->post(
                '/contacts/' . rawurlencode($contactId) . '/notes',
                $notePayload,
                30,
                $locationId
            );

            if (is_wp_error($response)) {
                $this->logger->info('Contact notes endpoint failed, trying general notes endpoint', [
                    'contactId' => $contactId,
                    'error'     => $response->get_error_message(),
                ]);

                $response = $this->client->post(
                    '/notes/',
                    $notePayload,
                    30,
                    $locationId
                );

                if (is_wp_error($response)) {
                    $this->logger->warning('Failed to add GHL timeline note', [
                        'contactId'  => $contactId,
                        'locationId' => $locationId,
                        'error'      => $response->get_error_message(),
                        'errorData'  => $response->get_error_data(),
                    ]);

                    return $response;
                }
            }

            $this->logger->info('GHL timeline note added successfully', [
                'contactId'  => $contactId,
                'locationId' => $locationId,
            ]);

            return ['ok' => true, 'result' => $response];
        } catch (\Exception $e) {
            $this->logger->error('Exception adding GHL timeline note', [
                'contactId'  => $contactId,
                'locationId' => $locationId,
                'exception'  => $e->getMessage(),
            ]);

            return new WP_Error('ghl_note_exception', $e->getMessage());
        }
    }

    /**
     * Update acceptance note with invoice information.
     * 
     * Adds a new note (GHL may not support note updates) with invoice details.
     * 
     * @param string $contactId GHL contact ID
     * @param string $locationId GHL location ID
     * @param string $estimateId Estimate ID
     * @param string $invoiceNumber Invoice number
     * @return array|WP_Error Success response or error (non-blocking)
     */
    public function updateAcceptanceNoteWithInvoice(string $contactId, string $locationId, string $estimateId, string $invoiceNumber): array|WP_Error
    {
        $estimateId = sanitize_text_field($estimateId);
        $invoiceNumber = sanitize_text_field($invoiceNumber);

        // Add a follow-up note with invoice info
        $noteData = [
            'estimateId' => $estimateId,
            'invoiceNumber' => $invoiceNumber,
            'acceptedAt' => current_time('mysql'),
        ];

        return $this->addAcceptanceNote($contactId, $locationId, $noteData);
    }
}

