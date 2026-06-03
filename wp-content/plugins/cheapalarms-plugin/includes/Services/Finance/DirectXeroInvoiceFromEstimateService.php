<?php

namespace CheapAlarms\Plugin\Services\Finance;

use CheapAlarms\Plugin\Config\Config;
use CheapAlarms\Plugin\Services\Estimate\EstimateNormalizer;
use CheapAlarms\Plugin\Services\EstimateService;
use CheapAlarms\Plugin\Services\Logger;
use CheapAlarms\Plugin\Services\XeroService;
use WP_Error;

/**
 * Creates a Xero ACCREC invoice from portal estimate data (no GHL invoice).
 * Guarded by {@see Config::isXeroDirectInvoicingEnabled()}; callers should check the flag first.
 */
class DirectXeroInvoiceFromEstimateService
{
    public function __construct(
        private EstimateService $estimateService,
        private EstimateNormalizer $normalizer,
        private XeroService $xeroService,
        private Logger $logger,
        private Config $config
    ) {
    }

    /**
     * @param array<string, mixed> $options Reserved for parity with GHL invoice creation (e.g. item overrides).
     * @return array<string, mixed>|WP_Error
     */
    public function createFromEstimate(string $estimateId, string $locationId, array $options = [])
    {
        unset($options);

        if (!$this->xeroService->isConnected()) {
            return new WP_Error(
                'xero_not_connected',
                __('Xero is not connected. Connect Xero before using direct invoicing.', 'cheapalarms'),
                ['status' => 503]
            );
        }

        if ($this->config->getXeroSalesAccountCode() === '') {
            return new WP_Error(
                'missing_sales_account',
                __('Sales account code is not configured for Xero.', 'cheapalarms'),
                ['status' => 500]
            );
        }

        $estimate = $this->estimateService->getEstimate([
            'estimateId' => $estimateId,
            'locationId' => $locationId,
        ]);

        if (is_wp_error($estimate)) {
            return $estimate;
        }

        $contact = $estimate['contact'] ?? [];
        $contactId = $contact['id'] ?? null;
        if (!$contactId) {
            return new WP_Error(
                'missing_contact_id',
                __('Contact ID is required to create an invoice. Estimate must have a linked contact.', 'cheapalarms'),
                ['status' => 400]
            );
        }

        $email = isset($contact['email']) ? trim((string) $contact['email']) : '';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new WP_Error(
                'missing_contact_email',
                __('A valid contact email is required for Xero direct invoicing.', 'cheapalarms'),
                ['status' => 400]
            );
        }

        $estimateItems = isset($estimate['items']) && is_array($estimate['items']) ? $estimate['items'] : [];
        if ($estimateItems === []) {
            return new WP_Error(
                'empty_items',
                __('Cannot create invoice: estimate has no items.', 'cheapalarms'),
                ['status' => 400]
            );
        }

        $currency = strtoupper((string) ($estimate['currency'] ?? 'AUD'));
        $ghlItems = [];
        foreach ($estimateItems as $item) {
            if (!is_array($item)) {
                continue;
            }
            $itemName = trim((string) ($item['name'] ?? ''));
            if ($itemName === '') {
                continue;
            }
            $itemAmount = (float) ($item['amount'] ?? 0);
            $qty = isset($item['quantity']) ? (int) $item['quantity'] : (isset($item['qty']) ? (int) $item['qty'] : 1);

            $ghlItems[] = [
                'name' => $itemName,
                'description' => (string) ($item['description'] ?? ''),
                'currency' => $currency,
                'amount' => $itemAmount,
                'qty' => max(1, $qty),
            ];
        }

        if ($ghlItems === []) {
            return new WP_Error(
                'no_valid_items',
                __('Cannot create invoice: no valid items found.', 'cheapalarms'),
                ['status' => 400]
            );
        }

        $subtotal = $estimate['subtotal'];
        if ($subtotal === null || $subtotal === '') {
            $sum = 0.0;
            foreach ($ghlItems as $row) {
                $sum += (float) $row['amount'] * (int) $row['qty'];
            }
            $subtotal = $sum;
        } else {
            $subtotal = (float) $subtotal;
        }

        $tax = (float) ($estimate['taxTotal'] ?? 0);
        $total = (float) ($estimate['total'] ?? 0);
        if ($total <= 0) {
            $total = $subtotal + $tax;
        }

        $invoiceNumber = (string) ($estimate['estimateNumber'] ?? $estimate['estimateId'] ?? $estimateId);
        $invoiceNumber = trim($invoiceNumber) !== '' ? trim($invoiceNumber) : $estimateId;

        $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '', $estimateId);
        $syntheticId = 'ca-xd-' . ($safeId !== '' ? $safeId : substr(md5($estimateId), 0, 16));

        $issueDate = $this->normalizer->formatDate($estimate['createdAt'] ?? null);
        $dueDate = $this->normalizer->formatDate(null, '+30 days');

        $ghlInvoice = [
            'id' => $syntheticId,
            'invoiceNumber' => $invoiceNumber,
            'number' => $invoiceNumber,
            'items' => $ghlItems,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'issueDate' => $issueDate,
            'dueDate' => $dueDate,
            'status' => 'AUTHORISED',
            'currency' => $currency,
            'xeroReference' => 'Estimate ' . $estimateId,
        ];

        $contactName = trim((string) ($contact['name'] ?? ''));
        if ($contactName === '') {
            $contactName = $email;
        }

        $firstName = '';
        $lastName = '';
        if ($contactName !== '') {
            $parts = preg_split('/\s+/', $contactName, 2);
            $firstName = (string) ($parts[0] ?? '');
            $lastName = (string) ($parts[1] ?? '');
        }

        $contactData = [
            'name' => $contactName,
            'email' => $email,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'phone' => (string) ($contact['phone'] ?? ''),
        ];

        $addr = $contact['address'] ?? null;
        if (is_array($addr) && $addr !== []) {
            $contactData['address'] = [
                'addressLine1' => (string) ($addr['addressLine1'] ?? $addr['line1'] ?? ''),
                'city' => (string) ($addr['city'] ?? ''),
                'state' => (string) ($addr['state'] ?? $addr['region'] ?? ''),
                'postalCode' => (string) ($addr['postalCode'] ?? $addr['zip'] ?? ''),
                'countryCode' => (string) ($addr['countryCode'] ?? $addr['country'] ?? 'AU'),
            ];
        }

        $xeroResult = $this->xeroService->createInvoice($ghlInvoice, $contactData);
        if (is_wp_error($xeroResult)) {
            $this->logger->error('Xero direct invoice creation failed', [
                'estimateId' => $estimateId,
                'locationId' => $locationId,
                'error' => $xeroResult->get_error_message(),
            ]);

            return $xeroResult;
        }

        return [
            'syntheticInvoiceId' => $syntheticId,
            'xeroInvoiceId' => $xeroResult['invoiceId'] ?? null,
            'xeroInvoiceNumber' => $xeroResult['invoiceNumber'] ?? null,
            'total' => $total,
            'currency' => strtolower($currency),
            'dueDate' => $dueDate,
            'number' => $xeroResult['invoiceNumber'] ?? $invoiceNumber,
            'xeroInvoice' => $xeroResult['invoice'] ?? null,
        ];
    }
}
