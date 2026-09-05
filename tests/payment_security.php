<?php

require_once dirname(__DIR__) . '/app/Services/BakongService.php';

$service = (new ReflectionClass(App\Services\BakongService::class))->newInstanceWithoutConstructor();
$cases = [
    'exact USD' => [['amount' => '7.00', 'currency' => 'USD'], 7.0, 'USD', true],
    'amount alias' => [['transactionAmount' => 7, 'currency' => 'USD'], 7.0, 'USD', true],
    'exact KHR' => [['amount' => 4000, 'currency' => 'KHR'], 4000.0, 'KHR', true],
    'missing amount' => [['currency' => 'USD'], 7.0, 'USD', false],
    'missing currency' => [['amount' => 7], 7.0, 'USD', false],
    'wrong currency' => [['amount' => 7, 'currency' => 'KHR'], 7.0, 'USD', false],
    'underpaid' => [['amount' => '6.99', 'currency' => 'USD'], 7.0, 'USD', false],
    'overpaid' => [['amount' => '7.01', 'currency' => 'USD'], 7.0, 'USD', false],
    'fractional cent' => [['amount' => 6.999, 'currency' => 'USD'], 7.0, 'USD', false],
    'fractional riel' => [['amount' => 4000.1, 'currency' => 'KHR'], 4000.0, 'KHR', false],
    'invalid amount' => [['amount' => '7dollars', 'currency' => 'USD'], 7.0, 'USD', false],
    'array amount' => [['amount' => [], 'currency' => 'USD'], 7.0, 'USD', false],
    'boolean amount' => [['amount' => true, 'currency' => 'USD'], 1.0, 'USD', false],
    'nonfinite amount' => [['amount' => INF, 'currency' => 'USD'], 7.0, 'USD', false],
    'zero price' => [['amount' => 0, 'currency' => 'USD'], 0.0, 'USD', false],
    'negative price' => [['amount' => -7, 'currency' => 'USD'], -7.0, 'USD', false],
    'unknown currency' => [['amount' => 7, 'currency' => 'EUR'], 7.0, 'EUR', false],
];

foreach ($cases as $name => [$transaction, $amount, $currency, $expected]) {
    if ($service->verifyAmount($transaction, $amount, $currency) !== $expected) {
        throw new RuntimeException('Failed: ' . $name);
    }
}

echo 'PASS: ' . count($cases) . " payment verification cases\n";

require_once dirname(__DIR__) . '/vendor/autoload.php';
define('BAKONG_CURRENCY', 'USD');
define('BAKONG_ACCOUNT_ID', 'test@bank');
define('BAKONG_MERCHANT_NAME', 'Test Store');
define('BAKONG_MERCHANT_CITY', 'Phnom Penh');
define('APP_NAME', 'Test Store');

$qrService = new App\Services\KHQRService();
$first = $qrService->generatePaymentQR(7, 'INV-TEST-ONE', 'USD');
$second = $qrService->generatePaymentQR(7, 'INV-TEST-TWO', 'USD');
if ($first['md5'] === $second['md5'] || !str_contains($first['qr'], 'INV-TEST-ONE')
    || !$qrService->verifyQR($first['qr'])) {
    throw new RuntimeException('QR must be valid and bound to its invoice.');
}
foreach ([[0, 'USD'], [-1, 'USD'], [7, 'EUR'], [7.001, 'USD'], [1.5, 'KHR']] as [$amount, $currency]) {
    try {
        $qrService->generatePaymentQR($amount, 'INV-TEST', $currency);
        throw new RuntimeException('Invalid QR amount or currency accepted.');
    } catch (InvalidArgumentException $expected) {
    }
}
echo "PASS: QR invoice binding and invalid-price checks\n";

class PaymentTestPromos extends App\Models\PromoCodeModel
{
    public function __construct() {}
    public function getByCode(string $code): ?array
    {
        return ['code' => 'TEST', 'is_active' => 1, 'expires_at' => null, 'max_uses' => null,
            'discount_type' => 'percentage', 'discount_value' => 15];
    }
}

$promos = new PaymentTestPromos();
foreach ([[19.99, 'USD', 16.99], [12999, 'KHR', 11049.0]] as [$price, $currency, $expected]) {
    $promotion = $promos->validateCode('TEST', $price, $currency);
    if ($promotion['final_price'] !== $expected
        || !$service->verifyAmount(['amount' => $promotion['final_price'], 'currency' => $currency], $expected, $currency)) {
        throw new RuntimeException('Promotion did not produce a payable currency amount.');
    }
}
echo "PASS: USD and KHR promotion rounding\n";