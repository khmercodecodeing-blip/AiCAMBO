<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

class TestRedirect extends RuntimeException {}
function redirect(string $path): void { throw new TestRedirect($path); }
function verify_csrf($token): bool { return $token === 'test-csrf'; }
function flash(string $type, string $message): void {}

class AccessTestInvoices extends App\Models\InvoiceModel
{
    public int $claims = 0;
    public array $invoice = [
        'invoice_no' => 'INV-TEST', 'buyer_email' => 'buyer@example.com',
        'payment_status' => 'completed', 'product_type' => 'tool',
        'license_key' => null, 'download_link' => 'https://example.com/private-download',
    ];
    public function __construct() {}
    public function getByInvoiceNo(string $invoiceNo): ?array { return $this->invoice; }
    public function claimLicenseDelivery(string $invoiceNo): bool { $this->claims++; return false; }
}

$invoices = new AccessTestInvoices();
$reflection = new ReflectionClass(App\Controllers\PaymentController::class);
$controller = $reflection->newInstanceWithoutConstructor();
$property = $reflection->getProperty('invoiceModel');
$property->setAccessible(true);
$property->setValue($controller, $invoices);

$_SESSION = [];
foreach (['showQR', 'checkPaymentStatus', 'retryDelivery'] as $method) {
    http_response_code(200);
    ob_start();
    $controller->$method('INV-TEST');
    $output = ob_get_clean();
    if (http_response_code() !== 404 || str_contains($output, 'private-download')) {
        throw new RuntimeException('Unauthorized invoice exposure: ' . $method);
    }
}
foreach (['success', 'receipt'] as $method) {
    try {
        $controller->$method('INV-TEST');
        throw new RuntimeException('Private view was rendered: ' . $method);
    } catch (TestRedirect $expected) {
    }
}
$_SESSION = ['user_email' => 'buyer@example.com'];
http_response_code(200);
ob_start();
$controller->checkPaymentStatus('INV-TEST');
$result = json_decode(ob_get_clean(), true);
if (($result['status'] ?? '') !== 'completed') {
    throw new RuntimeException('Owner could not read their payment status.');
}
$_POST = ['csrf_token' => 'wrong'];
ob_start();
$controller->retryDelivery('INV-TEST');
ob_end_clean();
if (http_response_code() !== 403 || $invoices->claims !== 0) {
    throw new RuntimeException('Retry CSRF guard failed.');
}
echo "PASS: Protected QR, polling, receipt, success and retry routes\n";