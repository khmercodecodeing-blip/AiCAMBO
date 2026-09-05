<?php

if (PHP_SAPI !== 'cli-server' || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__, 2);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (str_starts_with($path, '/public/assets/') || str_starts_with($path, '/storage/thumbnails/') || $path === '/PhotoTool/Aderr.PNG') {
    $asset = realpath($root . $path);
    $assetRoot = realpath($root . (str_starts_with($path, '/storage/') ? '/storage/thumbnails' : ($path === '/PhotoTool/Aderr.PNG' ? '/PhotoTool' : '/public/assets'))) . DIRECTORY_SEPARATOR;
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
if (!in_array($path, ['/', '/tools', '/courses', '/telegram-adder-pro'], true)) {
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
    $labels = require dirname(__DIR__, 2) . '/config/lang/' . current_lang() . '.php';
    return $labels[$key] ?? $key;
}
function get_flash(string $type): ?string { return null; }
function format_price($amount, $currency): string { return '$' . number_format((float) $amount, 2); }
function csrf_field(): string { return '<input type="hidden" name="csrf_token" value="preview-only">'; }

if ($path !== '/' || !isset($_GET['state'])) {
    $_GET['navigation'] = '1';
    $catalogType = ['/' => 'all', '/tools' => 'tool', '/courses' => 'course'][$path] ?? 'tool';
    $courses = [
        ['id' => 10, 'type' => 'tool', 'title' => 'Demo Telegram Tool', 'description' => 'កម្មវិធីសាកល្បងសម្រាប់ Windows', 'price' => 10, 'currency' => 'USD', 'thumbnail' => 'course_1779896317_dc386705.png', 'student_count' => 7],
        ['id' => 11, 'type' => 'tool', 'title' => 'Demo AI Tool', 'description' => 'ឧបករណ៍ AI សាកល្បង', 'price' => 1, 'original_price' => 3, 'currency' => 'USD', 'thumbnail' => 'course_1779900243_a059bb2d.png', 'student_count' => 8],
        ['id' => 12, 'type' => 'course', 'title' => 'Demo AI Course', 'description' => 'វគ្គសិក្សា AI សាកល្បង', 'price' => 15, 'currency' => 'USD', 'thumbnail' => 'course_1779900243_a059bb2d.png', 'student_count' => 4],
    ];
    if ($catalogType !== 'all') {
        $courses = array_values(array_filter($courses, fn(array $product): bool => $product['type'] === $catalogType));
    }
    if (isset($_GET['empty'])) $courses = [];
    require $root . '/app/views/courses/' . ($path === '/telegram-adder-pro' ? 'telegram_adder.php' : 'tools.php');
    return;
}

$licenseDeliveryStatus = ($_GET['state'] ?? '') === 'delivered' ? 'delivered' : 'pending';
$invoice = [
    'invoice_no' => 'INV-DEMO-ONLY', 'course_title' => 'Telegram Adder Pro - 1 Month',
    'payment_status' => 'completed',
    'product_type' => 'tool', 'amount' => 7, 'currency' => 'USD',
    'license_key' => 'DEMO-ONLY-NOT-AKEY', 'download_link' => 'https://example.com/demo-download',
];
if (isset($_GET['account'])) {
    $invoice = array_replace($invoice, [
        'course_title' => 'Demo Account - 1 Month', 'license_key' => null, 'download_link' => null,
        'qv_product_key' => 'demo', 'qv_status' => in_array($_GET['state'] ?? '', ['pending', 'review', 'delivered'], true) ? $_GET['state'] : 'pending',
        'delivered_stock' => "Email: demo@example.test\nPassword: DEMO-NOT-A-REAL-PASSWORD\nActivation link: https://example.test/activate/" . str_repeat('synthetic', 18),
    ]);
}
require $root . '/app/views/payment/success.php';