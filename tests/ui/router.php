<?php

if (PHP_SAPI !== 'cli-server' || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__, 2);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (str_starts_with($path, '/public/assets/')) {
    $asset = realpath($root . $path);
    $assetRoot = realpath($root . '/public/assets') . DIRECTORY_SEPARATOR;
    if ($asset && str_starts_with($asset, $assetRoot) && is_file($asset)) {
        return false;
    }
    http_response_code(404);
    exit;
}
if (in_array($path, ['/lang/km', '/lang/en'], true)) {
    header('Location: /?navigation=1&language=' . basename($path));
    exit;
}
if ($path !== '/') {
    http_response_code(404);
    exit;
}

define('APP_ROOT', __DIR__);
define('APP_URL', 'http://127.0.0.1:8097');
define('APP_NAME', 'AICAMBO.STORE');
$_SESSION = [];
function e($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function asset(string $path): string { return APP_URL . '/public/assets/' . $path; }
function current_lang(): string { return ($_GET['language'] ?? '') === 'en' ? 'en' : 'km'; }
function t(string $key): string {
    $labels = ['nav.courses' => 'Courses', 'nav.tools' => 'Tools', 'nav.tool_telegram' => 'Telegram', 'nav.login' => 'Sign in', 'nav.home' => 'Home', 'nav.support' => 'Support'];
    return $labels[$key] ?? $key;
}
function get_flash(string $type): ?string { return null; }
function format_price($amount, $currency): string { return '$' . number_format((float) $amount, 2); }
function csrf_field(): string { return '<input type="hidden" name="csrf_token" value="preview-only">'; }

$licenseDeliveryStatus = ($_GET['state'] ?? '') === 'delivered' ? 'delivered' : 'pending';
$invoice = [
    'invoice_no' => 'INV-DEMO-ONLY', 'course_title' => 'Telegram Adder Pro - 1 Month',
    'payment_status' => 'completed',
    'product_type' => 'tool', 'amount' => 7, 'currency' => 'USD',
    'license_key' => 'DEMO-ONLY-NOT-AKEY', 'download_link' => 'https://example.com/demo-download',
];
require $root . '/app/views/payment/success.php';