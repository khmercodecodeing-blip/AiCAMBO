<?php

require_once dirname(__DIR__) . '/app/Services/QuantumVaultClient.php';

use App\Services\QuantumVaultClient;

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$product = ['productKey' => 'sample', 'currency' => 'USD', 'price' => null, 'inStock' => true,
    'variants' => [['key' => 'month', 'price' => 2.50, 'inStock' => true]]];
$requests = [];
$client = new QuantumVaultClient('test-only', function ($method, $path, $body) use (&$requests, &$product) {
    $requests[] = [$method, $path, $body];
    return ['status' => 200, 'body' => json_encode(['product' => $product])];
});
check($client->quote('sample', 'month', 3)['price'] === 2.5, 'Variant price must be used.');
foreach ([[null, 3], ['wrong', 3], ['month', 2]] as [$variant, $cost]) {
    try {
        $client->quote('sample', $variant, $cost);
        throw new LogicException('Invalid quote accepted.');
    } catch (RuntimeException $expected) {
    }
}
$client->purchase('sample', 'month');
check(end($requests) === ['POST', '/purchase', ['productKey' => 'sample', 'quantity' => 1, 'variantKey' => 'month']], 'Purchase contract mismatch.');
$item = ['orderId' => 'QV-TEST', 'productKey' => 'sample', 'variantKey' => 'month',
    'fields' => [['name' => 'activation_url', 'label' => 'Activation link', 'value' => 'https://example.test/activate']]];
check(QuantumVaultClient::delivery($item, 'sample', 'month') === 'Activation link: https://example.test/activate', 'Dynamic fields must be preserved.');
$calls = 0;
$client = new QuantumVaultClient('test-only', function () use (&$calls) {
    $calls++;
    return ['status' => 0, 'error' => 28, 'body' => false];
});
try {
    $client->purchase('sample', null);
    throw new LogicException('Timeout was accepted.');
} catch (RuntimeException $expected) {
    check($calls === 1, 'A timed-out purchase must never retry.');
    check(!str_contains($expected->getMessage(), 'test-only'), 'Secrets must not appear in errors.');
}
foreach ([['status' => 500, 'body' => '{"orderId":"QV-RECOVERY"}'], ['status' => 200, 'body' => '{"orderId":"QV-TRUNCATED"']] as $failed) {
    $recorded = null;
    $client = new QuantumVaultClient('test-only', fn() => $failed);
    try {
        $client->purchase('sample', null, function (array $received) use (&$recorded): void { $recorded = $received; });
        throw new LogicException('Invalid response was accepted.');
    } catch (RuntimeException $expected) {
        check(base64_decode($recorded['body_base64']) === $failed['body'], 'Received error evidence must persist before decoding.');
    }
}
echo "PASS: QuantumVault variants, cost ceiling, purchase contract, dynamic fields, raw recovery evidence and no transport retries\n";