<?php

require_once __DIR__ . '/../key/config.php';
require_once __DIR__ . '/../key/functions.php';
require_once __DIR__ . '/Services/LicenseRegistration.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    exit;
}

$data = getRequestJSON();
if (!is_array($data)) {
    jsonResponse(['success' => false, 'message' => 'Invalid JSON'], 400);
    exit;
}
$apiKey = $data['api_key'] ?? ($_SERVER['HTTP_X_API_KEY'] ?? '');
if (!is_string($apiKey) || !validateApiKey($apiKey)) {
    jsonResponse(['success' => false, 'message' => 'Invalid API key'], 401);
    exit;
}

foreach (['license_key', 'hardware_id', 'pc_name', 'customer_name', 'plan', 'expires_at', 'transaction_ref'] as $field) {
    if (isset($data[$field]) && !is_string($data[$field])) {
        jsonResponse(['success' => false, 'message' => 'Invalid input'], 400);
        exit;
    }
}
if (!is_numeric($data['amount'] ?? 0) || !is_finite((float) ($data['amount'] ?? 0))) {
    jsonResponse(['success' => false, 'message' => 'Invalid amount'], 400);
    exit;
}
$purchase = [
    'license_key' => strtoupper(trim($data['license_key'] ?? '')),
    'hardware_id' => trim($data['hardware_id'] ?? ''),
    'pc_name' => substr(trim($data['pc_name'] ?? ''), 0, 255),
    'customer_name' => substr(trim($data['customer_name'] ?? ''), 0, 255),
    'plan' => substr(trim($data['plan'] ?? ''), 0, 100),
    'amount' => (float) ($data['amount'] ?? 0),
    'expires_at' => trim($data['expires_at'] ?? '') ?: null,
    'transaction_ref' => substr(trim($data['transaction_ref'] ?? ''), 0, 255),
];
if ($purchase['license_key'] === '' || strlen($purchase['license_key']) > 255 || strlen($purchase['hardware_id']) > 512) {
    jsonResponse(['success' => false, 'message' => 'Invalid license or hardware identifier'], 400);
    exit;
}

try {
    $database = getDB();
    $ip = getClientIP();
    $result = App\Services\LicenseRegistration::register($database, $purchase, $ip);
    try {
        logAPI($database, 'register', $purchase['license_key'], $purchase['hardware_id'], $ip, $result['success'] ? 'success' : 'registration_failed');
    } catch (Throwable $loggingError) {
        error_log('License registration audit log unavailable.');
    }
    $status = $result['status'];
    unset($result['status']);
    jsonResponse($result, $status);
} catch (Throwable $error) {
    error_log('License registration failed; transaction rolled back.');
    jsonResponse(['success' => false, 'message' => 'Registration unavailable. Please retry.'], 503);
}