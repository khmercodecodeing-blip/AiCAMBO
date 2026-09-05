<?php

define('APP_ROOT', __DIR__ . '/ui');
define('APP_URL', 'https://example.com');
function e($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function format_price($amount, $currency): string { return '$' . number_format((float) $amount, 2); }
function csrf_field(): string { return '<input type="hidden" name="csrf_token" value="test-only">'; }

set_error_handler(function (int $severity, string $message): bool {
    throw new RuntimeException($message);
});

foreach (['completed', 'pending', 'expired'] as $paymentStatus) {
    foreach (['pending', 'processing', 'delivered'] as $registrationStatus) {
        $invoice = [
            'invoice_no' => 'INV-TEST', 'course_title' => 'Test Software', 'product_type' => 'tool',
            'amount' => 7, 'currency' => 'USD', 'license_key' => 'PAID-KEY-SENTINEL',
            'payment_status' => $paymentStatus,
        ];
        $licenseDeliveryStatus = $registrationStatus;
        http_response_code(200);
        ob_start();
        require dirname(__DIR__) . '/app/views/payment/success.php';
        $html = ob_get_clean();
        if ($paymentStatus !== 'completed') {
            if ($html !== '' || http_response_code() !== 404) {
                throw new RuntimeException('Unpaid success view must deny access.');
            }
            continue;
        }
        if (!str_contains($html, 'PAID-KEY-SENTINEL') || !str_contains($html, 'id="download-key-btn"')) {
            throw new RuntimeException('Paid buyer must receive their existing key without waiting for registration.');
        }
        if (str_contains($html, 'Retry Registration') !== ($registrationStatus !== 'delivered')) {
            throw new RuntimeException('Registration status must remain honest and retryable.');
        }
        if (str_contains($html, 'autoDownloadReceiptPdf') || str_contains($html, "createElement('iframe')")
            || str_contains($html, 'html2canvas') || str_contains($html, 'jspdf')
            || str_contains($html, 'pdf.save(')
            || !str_contains($html, '/payment/receipt/INV-TEST')) {
            throw new RuntimeException('Receipts must remain available manually without automatic downloads.');
        }
    }
}
restore_error_handler();
echo "PASS: Paid key visibility, download controls, registration status and unpaid denial\n";