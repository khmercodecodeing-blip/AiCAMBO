<?php

require_once dirname(__DIR__) . '/app/Models/InvoiceModel.php';
require_once dirname(__DIR__) . '/app/Services/LicenseDeliveryService.php';

class DeliveryTestInvoices extends App\Models\InvoiceModel
{
    public bool $claimable = true;
    public string $status = 'pending';

    public function __construct() {}

    public function claimLicenseDelivery(string $invoiceNo): bool
    {
        if (!$this->claimable) {
            return false;
        }
        $this->claimable = false;
        $this->status = 'processing';
        return true;
    }

    public function finishLicenseDelivery(string $invoiceNo, bool $delivered): void
    {
        $this->status = $delivered ? 'delivered' : 'pending';
    }

    public function getByInvoiceNo(string $invoiceNo): ?array
    {
        return ['license_delivery_status' => $this->status];
    }
}

$invoice = ['invoice_no' => 'INV-TEST', 'payment_status' => 'completed', 'license_key' => 'test-key'];
$invoices = new DeliveryTestInvoices();
$calls = 0;
$service = new App\Services\LicenseDeliveryService($invoices, function () use (&$calls): bool {
    $calls++;
    return $calls > 1;
});
if ($service->deliver(array_replace($invoice, ['payment_status' => 'pending'])) !== 'not_required' || $calls !== 0) {
    throw new RuntimeException('Unpaid license must not be delivered.');
}
if ($service->deliver($invoice) !== 'pending' || $invoices->status !== 'pending') {
    throw new RuntimeException('Failed delivery must remain retryable.');
}
if ($service->deliver($invoice) !== 'pending' || $calls !== 1) {
    throw new RuntimeException('Cooldown must prevent duplicate calls.');
}
$invoices->claimable = true;
if ($service->deliver($invoice) !== 'delivered' || $calls !== 2) {
    throw new RuntimeException('Retry must complete delivery.');
}
if ($service->deliver($invoice) !== 'delivered' || $calls !== 2) {
    throw new RuntimeException('Delivered purchase must not register again.');
}
$invoices->claimable = true;
$throwing = new App\Services\LicenseDeliveryService($invoices, function (): bool {
    throw new RuntimeException('Simulated timeout');
});
if ($throwing->deliver($invoice) !== 'pending' || $invoices->status !== 'pending') {
    throw new RuntimeException('Timeout must remain retryable.');
}
echo "PASS: License failure, retry, cooldown, timeout and duplicate delivery checks\n";

require_once dirname(__DIR__) . '/app/Services/LicenseClient.php';
define('LICENSE_API_KEY', 'test-only');
define('LICENSE_SIGNING_SECRET', 'test-signing-secret');
foreach ([1, 2, 3] as $plan) {
    $key = App\Services\LicenseClient::keyForPlan($plan);
    if (!preg_match('/^[A-Z0-9]{4}(-[A-Z0-9]{4}){3}$/', $key)
        || App\Services\LicenseClient::keyForPlan($plan, $key) !== $key) {
        throw new RuntimeException('Invalid generated plan key.');
    }
}
try {
    App\Services\LicenseClient::keyForPlan(1, App\Services\LicenseClient::keyForPlan(3));
    throw new RuntimeException('Cross-plan license key accepted.');
} catch (InvalidArgumentException $expected) {
}
if (App\Services\LicenseClient::keyForPlan(99) !== null) {
    throw new RuntimeException('Non-license product generated a key.');
}
$purchase = array_replace($invoice, [
    'course_id' => 1, 'paid_at' => '2026-09-05 12:00:00', 'buyer_name' => 'Test Buyer', 'amount' => '7.00',
]);
$payload = App\Services\LicenseClient::payload($purchase);
if ($payload['expires_at'] !== '2026-10-05' || $payload['plan'] !== '30 Days' || $payload['hardware_id'] !== '') {
    throw new RuntimeException('License term must follow the paid product and payment date.');
}
if ($payload !== App\Services\LicenseClient::payload($purchase)) {
    throw new RuntimeException('Retry payload must be stable.');
}
echo "PASS: License term, guest purchase and stable retry payload checks\n";