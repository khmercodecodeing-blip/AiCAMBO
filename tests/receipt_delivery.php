<?php

define('APP_NAME', 'Test Store');
define('APP_URL', 'https://example.com');
function e($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function asset(string $path): string { return '/public/assets/' . $path; }
function format_price($amount, $currency): string { return '$' . number_format((float) $amount, 2); }

set_error_handler(function (int $severity, string $message): bool {
    throw new RuntimeException($message);
});
$pageTitle = 'Test Receipt';
foreach ([null, 'pending', 'processing', 'delivered'] as $status) {
    $invoice = [
        'invoice_no' => 'INV-TEST', 'course_title' => 'Test Software', 'product_type' => 'tool',
        'payment_status' => 'completed',
        'amount' => 7, 'currency' => 'USD', 'buyer_name' => 'Test Buyer',
        'paid_at' => '2026-09-05 12:00:00', 'license_key' => 'TEST-LICENSE-SENTINEL',
        'license_delivery_status' => $status,
    ];
    ob_start();
    require dirname(__DIR__) . '/app/views/payment/receipt.php';
    $html = ob_get_clean();
    if (!str_contains($html, 'TEST-LICENSE-SENTINEL')
        || !str_contains($html, $status === 'delivered' ? 'Confirmed' : 'Pending')) {
        throw new RuntimeException('Paid receipt must show the key and its separate registration status.');
    }
}
foreach (['pending', 'expired'] as $paymentStatus) {
    $invoice['payment_status'] = $paymentStatus;
    http_response_code(200);
    ob_start();
    require dirname(__DIR__) . '/app/views/payment/receipt.php';
    $html = ob_get_clean();
    if ($html !== '' || http_response_code() !== 404) {
        throw new RuntimeException('Unpaid receipt must not expose a key.');
    }
}
restore_error_handler();
echo "PASS: Paid receipt key access, registration status and unpaid denial\n";