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

        if (!$payload) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }

        // Log incoming webhook for debugging
        error_log('Bakong webhook received: ' . $rawBody);

        // Extract MD5 hash from payload
        $md5Hash = $payload['md5'] ?? $payload['hash'] ?? $payload['transactionId'] ?? null;
        $amount  = $payload['amount'] ?? $payload['transactionAmount'] ?? null;

        if (empty($md5Hash)) {
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
            echo json_encode(['status' => 'already_processed']);
            return;
        }

        // Verify amount if provided
        if ($amount !== null) {
            if (abs((float)$amount - (float)$invoice['amount']) > 0.01) {
                error_log('Webhook: Amount mismatch. Expected: ' . $invoice['amount'] . ', Got: ' . $amount);
                http_response_code(400);
                echo json_encode(['error' => 'Amount mismatch']);
                return;
            }
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
        if (!empty($invoice['license_key']) && !empty($invoice['hardware_id'])) {
            $this->registerLicense($invoice);
        }

        // Generate Telegram invite link
        $telegramLink = null;
        try {
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
            'telegram_link' => $telegramLink,
        ]);
    }

    /**
     * Helper to call Key Server register API
     */
    private function registerLicense(array $invoice): bool
    {
        $license_key = $invoice['license_key'];
        $hardware_id = $invoice['hardware_id'];
        
        // Parse plan days from license key prefix
        $raw = strtoupper(str_replace('-', '', $license_key));
        $days = 30; // default fallback
        if (strlen($raw) === 16) {
            $body = substr($raw, 0, 12);
            $sig = substr($raw, 12, 4);
            $secret = "TGA_PR0_s3cR3t_2026xQ";
            $expected = strtoupper(substr(hash_hmac('sha256', $body, $secret), 0, 4));
            if ($sig === $expected) {
                $code = substr($body, 0, 2);
                if ($code === 'A1') $days = 30;
                elseif ($code === 'B3') $days = 90;
                elseif ($code === 'CY') $days = 365;
            }
        }

        $apiUrl = APP_URL . '/key/api/register.php';
        $apiKey = 'mK9@xP2#qL7nR4$vT8wJ1^cF5hB6yN3*';
        $expiresAt = date('Y-m-d', strtotime("+$days days"));
        
        $payload = [
            'api_key' => $apiKey,
            'license_key' => $license_key,
            'hardware_id' => $hardware_id,
            'pc_name' => 'Web Purchased',
            'customer_name' => $invoice['buyer_name'],
            'plan' => $days . ' Days',
            'amount' => $invoice['amount'],
            'expires_at' => $expiresAt,
            'transaction_ref' => $invoice['invoice_no']
        ];
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: WebCheckout'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        
        if ($err) {
            error_log("Failed to register license: " . $err);
            return false;
        }
        
        $resData = json_decode($response, true);
        if (!$resData || !$resData['success']) {
            error_log("License server API returned error: " . ($resData['message'] ?? 'Unknown error'));
            return false;
        }
        
        return true;
    }
}
