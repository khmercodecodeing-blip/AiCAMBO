<?php

namespace App\Controllers;

use App\Models\InvoiceModel;
use App\Services\BakongService;
use App\Services\TelegramService;

/**
 * Webhook Controller — Handles incoming Bakong payment callbacks
 */
class WebhookController
{
    private InvoiceModel $invoiceModel;

    public function __construct()
    {
        $this->invoiceModel = new InvoiceModel();
    }

    /**
     * Handle Bakong webhook callback
     */
    public function handleBakong(): void
    {
        header('Content-Type: application/json');

        // Only accept POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        // Read raw body
        $rawBody = file_get_contents('php://input');

        if (empty($rawBody)) {
            http_response_code(400);
            echo json_encode(['error' => 'Empty request body']);
            return;
        }

        // Verify webhook signature if configured
        if (!empty(WEBHOOK_SECRET)) {
            $signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? $_SERVER['HTTP_X_SIGNATURE'] ?? '';
            $expectedSignature = hash_hmac('sha256', $rawBody, WEBHOOK_SECRET);

            if (!hash_equals($expectedSignature, $signature)) {
                error_log('Webhook signature verification failed');
                http_response_code(401);
                echo json_encode(['error' => 'Invalid signature']);
                return;
            }
        }

        // Parse payload
        $payload = json_decode($rawBody, true);

        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }

        // Extract MD5 hash from payload
        $md5Hash = $payload['md5'] ?? $payload['hash'] ?? $payload['transactionId'] ?? null;

        if (!is_string($md5Hash) || !preg_match('/^[a-f0-9]{32}$/i', $md5Hash)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing transaction identifier']);
            return;
        }

        // Find matching invoice
        $invoice = $this->invoiceModel->getByMd5Hash($md5Hash);

        if (!$invoice) {
            error_log('Webhook: No invoice found for MD5: ' . $md5Hash);
            http_response_code(404);
            echo json_encode(['error' => 'Invoice not found']);
            return;
        }

        // Prevent duplicate processing
        if ($invoice['payment_status'] === 'completed') {
            $this->registerLicense($invoice);
            echo json_encode(['status' => 'already_processed']);
            return;
        }

        try {
            $bakong = new BakongService();
            $result = $bakong->checkTransactionByMD5($invoice['md5_hash']);
            if (!$result['found'] || !is_array($result['data'])
                || !$bakong->verifyAmount($result['data'], (float) $invoice['amount'], $invoice['currency'])) {
                http_response_code(422);
                echo json_encode(['error' => 'Payment not verified']);
                return;
            }
        } catch (\Throwable $error) {
            error_log('Webhook payment verification unavailable');
            http_response_code(503);
            echo json_encode(['error' => 'Payment verification unavailable']);
            return;
        }

        // Update invoice status (atomic)
        $updated = $this->invoiceModel->updateStatus($invoice['invoice_no'], 'completed');

        if (!$updated) {
            echo json_encode(['status' => 'already_processed']);
            return;
        }

        // Increment promo code uses if applied
        if (!empty($invoice['promo_code'])) {
            try {
                $promoModel = new \App\Models\PromoCodeModel();
                $promoModel->incrementUses($invoice['promo_code']);
            } catch (\Throwable $e) {
                error_log('Webhook increment promo uses error: ' . $e->getMessage());
            }
        }

        // Auto-register license if applicable
        if (!empty($invoice['license_key'])) {
            $this->registerLicense($invoice);
        }

        // Generate Telegram invite link
        $telegramLink = null;
        try {
            if (($invoice['product_type'] ?? '') !== 'course' || empty($invoice['telegram_group_id'])) {
                echo json_encode(['status' => 'success', 'invoice_no' => $invoice['invoice_no']]);
                return;
            }
            $telegram = new TelegramService();
            $result = $telegram->createInviteLink($invoice['telegram_group_id'] ?? '', 10, 1);

            if ($result['success']) {
                $telegramLink = $result['invite_link'];
                $this->invoiceModel->updateTelegramLink(
                    $invoice['invoice_no'],
                    $result['invite_link'],
                    $result['expires_at']
                );
            }
        } catch (\Exception $e) {
            error_log('Webhook Telegram error: ' . $e->getMessage());
        }

        // Respond success
        echo json_encode([
            'status'        => 'success',
            'invoice_no'    => $invoice['invoice_no'],
        ]);
    }

    /**
     * Helper to call Key Server register API
     */
    private function registerLicense(array $invoice): bool
    {
        $current = $this->invoiceModel->getByInvoiceNo($invoice['invoice_no']);
        return $current && (new \App\Services\LicenseDeliveryService($this->invoiceModel))->deliver($current) === 'delivered';
    }
}
