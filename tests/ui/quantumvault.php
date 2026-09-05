<?php

if (PHP_SAPI !== 'cli-server' || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(404);
    exit;
}

header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');
define('APP_ROOT', dirname(__DIR__, 2));
define('APP_URL', '');
define('ADMIN_PREFIX', 'fixture-admin');
define('ADMIN_URL', '/fixture-admin');
define('APP_NAME', 'Admin Fixture');

function e($value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function asset(string $path): string { return '/public/assets/' . $path; }
function get_flash(string $type): ?string { return null; }
function csrf_field(): string { return '<input type="hidden" name="csrf_token" value="fixture-only">'; }

$pageTitle = 'QuantumVault - Admin';
$configured = true;
$schemaReady = true;
$refreshed = true;
$balanceLabel = '25.0000 USD';
$notices = [];
$catalog = [
    ['product' => 'creative-suite', 'variant' => 'month', 'name' => 'Creative Suite', 'variant_name' => 'One month', 'price' => '2.5000', 'currency' => 'USD', 'stock' => true],
    ['product' => str_repeat('long-product-', 12), 'variant' => 'year', 'name' => 'Professional Software Subscription with Extended Product Name', 'variant_name' => 'Annual subscription', 'price' => '12.5000', 'currency' => 'USD', 'stock' => false],
];
$invoices = [];
foreach (['pending', 'processing', 'review'] as $index => $status) {
    $invoices[] = ['invoice_no' => 'INV-FIXTURE-' . $index, 'course_id' => 4 + $index,
        'qv_product_key' => 'creative-suite', 'qv_variant_key' => 'month', 'qv_status' => $status,
        'qv_order_id' => null, 'paid_at' => '2026-09-05 12:00:00', 'qv_attempted_at' => null];
}
$providerOrders = [['orderId' => 'QV-FIXTURE-001', 'productKey' => 'creative-suite', 'variantKey' => 'month',
    'status' => 'completed', 'createdAt' => '2026-09-05T12:01:00Z']];
if (($_GET['state'] ?? '') === 'missing') {
    $configured = false;
    $schemaReady = false;
    $refreshed = false;
    $catalog = $invoices = $providerOrders = [];
    $notices = ['QuantumVault API key is missing.'];
}
require APP_ROOT . '/app/views/admin/quantumvault.php';