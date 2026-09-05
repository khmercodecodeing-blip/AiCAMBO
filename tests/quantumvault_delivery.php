<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Models\QuantumVaultOrderModel;
use App\Services\QuantumVaultClient;
use App\Services\QuantumVaultDeliveryService;

function ensure(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

putenv('QUANTUMVAULT_ENABLED=1');
$unsafe = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$unsafe->exec('CREATE TABLE invoices (qv_order_id TEXT, invoice_no TEXT)');
foreach (['', 'CREATE INDEX uq_invoices_qv_order ON invoices (qv_order_id)',
    'DROP INDEX uq_invoices_qv_order; CREATE UNIQUE INDEX uq_invoices_qv_order ON invoices (qv_order_id, invoice_no)'] as $ddl) {
    if ($ddl !== '') {
        $unsafe->exec($ddl);
    }
    try {
        new QuantumVaultOrderModel($unsafe);
        throw new LogicException('Missing or incorrect unique order index was accepted.');
    } catch (RuntimeException $expected) {
    }
}
$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE TABLE invoices (invoice_no TEXT PRIMARY KEY, payment_status TEXT, amount REAL, currency TEXT,
    qv_product_key TEXT, qv_variant_key TEXT, qv_max_cost REAL, qv_status TEXT, qv_order_id TEXT UNIQUE,
    delivered_stock TEXT, qv_response TEXT, qv_attempted_at TEXT)');
$insert = $pdo->prepare("INSERT INTO invoices (invoice_no,payment_status,amount,currency,qv_product_key,qv_max_cost,qv_status)
    VALUES (?,? ,5,'USD','sample',3,'pending')");
foreach (['paid' => 'completed', 'unpaid' => 'pending', 'timeout' => 'completed', 'price' => 'completed', 'crash' => 'completed', 'duplicate' => 'completed'] as $number => $status) {
    $insert->execute([$number, $status]);
}
$orders = new QuantumVaultOrderModel($pdo);
$buys = 0;
$fail = false;
$price = 2;
$item = ['orderId' => 'QV-SAMPLE', 'productKey' => 'sample', 'variantKey' => null, 'deliveredAt' => date(DATE_ATOM),
    'fields' => [['name' => 'email', 'label' => 'Email', 'value' => 'synthetic@example.test'], ['name' => 'cdk_code', 'label' => 'Code', 'value' => 'TEST-ONLY']]];
$client = new QuantumVaultClient('test-only', function ($method, $path) use (&$buys, &$fail, &$price, &$item) {
    if ($method === 'POST') {
        $buys++;
        if ($fail) {
            return ['status' => 0, 'error' => 28, 'body' => false];
        }
        return ['status' => 200, 'body' => json_encode(['success' => true, 'order' => ['fulfilled' => 1, 'items' => [$item]]])];
    }
    return ['status' => 200, 'body' => json_encode(str_starts_with($path, '/orders/') ? ['order' => $item] :
        ['product' => ['productKey' => 'sample', 'currency' => 'USD', 'price' => $price, 'inStock' => true, 'variants' => []]])];
});
$service = new QuantumVaultDeliveryService($orders, $client);
ensure($service->deliver($orders->get('unpaid')) === 'not_required' && $buys === 0, 'Unpaid purchase must not call supplier.');
$stale = $orders->get('paid');
ensure($service->deliver($stale) === 'delivered' && $buys === 1, 'Paid purchase must deliver.');
ensure($service->deliver($stale) === 'delivered' && $buys === 1, 'Stale duplicate must not purchase again.');
ensure(str_contains($orders->get('paid')['delivered_stock'], 'TEST-ONLY'), 'Dynamic fields must persist.');
$fail = true;
ensure($service->deliver($orders->get('timeout')) === 'review', 'Timeout must require review.');
$before = $buys;
ensure($service->deliver($orders->get('timeout')) === 'review' && $buys === $before, 'Timeout must never buy again.');
$item['orderId'] = 'QV-RECOVERED';
$service->recover('timeout', 'QV-RECOVERED');
ensure($orders->get('timeout')['qv_status'] === 'delivered' && $buys === $before, 'Recovery must be read-only.');
$price = 6;
ensure($service->deliver($orders->get('price')) === 'pending' && $buys === $before, 'Cost increase must block purchase.');
ensure($orders->claim('crash'), 'Claim must succeed.');
ensure(!$orders->claim('crash'), 'Concurrent claim must fail.');
ensure($service->deliver($orders->get('crash')) === 'processing' && $buys === $before, 'Abandoned claim must not auto-purchase.');
ensure($orders->claim('duplicate'), 'Recovery fixture must be claimed.');
try {
    $service->recover('duplicate', 'QV-RECOVERED');
    throw new LogicException('An order was delivered to two invoices.');
} catch (PDOException $expected) {
}
ensure($orders->get('duplicate')['delivered_stock'] === null, 'Failed recovery must roll back.');
$item['orderId'] = 'QV-OLD';
$item['deliveredAt'] = '2020-01-01T00:00:00Z';
try {
    $service->recover('duplicate', 'QV-OLD');
    throw new LogicException('Old order accepted.');
} catch (RuntimeException $expected) {
}
putenv('QUANTUMVAULT_ENABLED=0');
ensure($service->deliver($orders->get('price')) === 'pending' && $buys === $before, 'Disabled provider must not purchase.');
echo "PASS: Paid-only delivery, stale/concurrent claims, dynamic goods, timeout recovery, cost checks and unique orders\n";