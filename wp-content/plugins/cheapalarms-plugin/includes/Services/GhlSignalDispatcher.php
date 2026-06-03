<?php

namespace CheapAlarms\Plugin\Services;

use function is_wp_error;
use function __;

/**
 * Single entry point for GHL engagement signals (tags, notes) from the portal.
 * Delegates to {@see GhlSignalService}; keeps vocabulary and call sites centralized.
 */
class GhlSignalDispatcher
{
    public function __construct(
        private GhlSignalService $signalService,
        private Logger $logger
    ) {
    }

    /**
     * After a customer accepts an estimate in the portal: tag + timeline note.
     *
     * @param array<string, mixed> $noteData estimateNumber, estimateId, acceptedAt, invoiceNumber (optional)
     */
    public function dispatchEstimateAccepted(string $contactId, string $locationId, array $noteData): void
    {
        $tagResult = $this->signalService->addAcceptanceTag($contactId, $locationId);
        if (is_wp_error($tagResult)) {
            $this->logger->warning('Failed to add GHL acceptance tag', [
                'contactId' => $contactId,
                'estimateId' => $noteData['estimateId'] ?? null,
                'locationId' => $locationId,
                'error' => $tagResult->get_error_message(),
            ]);
        }

        $noteResult = $this->signalService->addAcceptanceNote($contactId, $locationId, $noteData);
        if (is_wp_error($noteResult)) {
            $this->logger->warning('Failed to add GHL acceptance note', [
                'contactId' => $contactId,
                'estimateId' => $noteData['estimateId'] ?? null,
                'locationId' => $locationId,
                'error' => $noteResult->get_error_message(),
            ]);
        }
    }

    /**
     * After a successful deposit payment (Stripe webhook path): tag + timeline note.
     *
     * @param array<string, mixed> $noteData estimateId, estimateNumber, amount, paymentIntentId (optional)
     */
    public function dispatchDepositPaid(string $contactId, string $locationId, array $noteData): void
    {
        $tagResult = $this->signalService->mergePortalTag($contactId, $locationId, 'portal_deposit_paid');
        if (is_wp_error($tagResult)) {
            $this->logger->warning('Failed to add GHL deposit-paid tag', [
                'contactId'  => $contactId,
                'estimateId' => $noteData['estimateId'] ?? null,
                'locationId' => $locationId,
                'error'      => $tagResult->get_error_message(),
            ]);
        }

        $ref = (string) ($noteData['estimateNumber'] ?? $noteData['estimateId'] ?? '');
        $amount = isset($noteData['amount']) ? (float) $noteData['amount'] : 0.0;
        $body = sprintf(
            __('Deposit paid (%.2f) for Estimate #%s via portal.', 'cheapalarms'),
            $amount,
            $ref
        );
        $pi = (string) ($noteData['paymentIntentId'] ?? '');
        if ($pi !== '') {
            $body .= ' ' . sprintf(__('Stripe PI: %s', 'cheapalarms'), $pi);
        }

        $noteResult = $this->signalService->postContactTimelineNote($contactId, $locationId, $body);
        if (is_wp_error($noteResult)) {
            $this->logger->warning('Failed to add GHL deposit-paid note', [
                'contactId'  => $contactId,
                'estimateId' => $noteData['estimateId'] ?? null,
                'locationId' => $locationId,
                'error'      => $noteResult->get_error_message(),
            ]);
        }
    }

    /**
     * When the invoice is fully paid via portal (Stripe webhook path): tag + timeline note.
     *
     * @param array<string, mixed> $noteData estimateId, estimateNumber, amount, paymentIntentId (optional)
     */
    public function dispatchPaidInFull(string $contactId, string $locationId, array $noteData): void
    {
        $tagResult = $this->signalService->mergePortalTag($contactId, $locationId, 'portal_paid_in_full');
        if (is_wp_error($tagResult)) {
            $this->logger->warning('Failed to add GHL paid-in-full tag', [
                'contactId'  => $contactId,
                'estimateId' => $noteData['estimateId'] ?? null,
                'locationId' => $locationId,
                'error'      => $tagResult->get_error_message(),
            ]);
        }

        $ref = (string) ($noteData['estimateNumber'] ?? $noteData['estimateId'] ?? '');
        $amount = isset($noteData['amount']) ? (float) $noteData['amount'] : 0.0;
        $body = sprintf(
            __('Invoice paid in full (%.2f) for Estimate #%s via portal.', 'cheapalarms'),
            $amount,
            $ref
        );
        $pi = (string) ($noteData['paymentIntentId'] ?? '');
        if ($pi !== '') {
            $body .= ' ' . sprintf(__('Stripe PI: %s', 'cheapalarms'), $pi);
        }

        $noteResult = $this->signalService->postContactTimelineNote($contactId, $locationId, $body);
        if (is_wp_error($noteResult)) {
            $this->logger->warning('Failed to add GHL paid-in-full note', [
                'contactId'  => $contactId,
                'estimateId' => $noteData['estimateId'] ?? null,
                'locationId' => $locationId,
                'error'      => $noteResult->get_error_message(),
            ]);
        }
    }
}
