<?php

require_once dirname(__DIR__) . '/app/Services/LicenseRegistration.php';

class RegistrationTestDatabase extends PDO
{
    public bool $failActivation = false;

    public function __construct()
    {
        parent::__construct('sqlite::memory:');
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->exec('CREATE TABLE licenses (id INTEGER PRIMARY KEY, license_key TEXT UNIQUE, customer_name TEXT, expires_at TEXT, note TEXT)');
        $this->exec('CREATE TABLE activations (id INTEGER PRIMARY KEY, license_id INTEGER, hardware_id TEXT, pc_name TEXT, ip_address TEXT)');
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        if (str_contains($query, 'GET_LOCK') || str_contains($query, 'RELEASE_LOCK')) {
            $query = 'SELECT CASE WHEN ? IS NOT NULL THEN 1 ELSE 0 END';
        }
        if ($this->failActivation && str_starts_with($query, 'INSERT INTO activations')) {
            throw new RuntimeException('Simulated activation failure');
        }
        return parent::prepare(str_replace(' FOR UPDATE', '', $query), $options);
    }
}

$database = new RegistrationTestDatabase();
$purchase = [
    'license_key' => 'TEST-KEY', 'transaction_ref' => 'INV-TEST', 'plan' => '30 Days',
    'amount' => 7.0, 'customer_name' => 'Test Buyer', 'expires_at' => '2026-10-05',
    'hardware_id' => 'test-device', 'pc_name' => 'Test PC',
];
$first = App\Services\LicenseRegistration::register($database, $purchase, '127.0.0.1');
$retry = App\Services\LicenseRegistration::register($database, array_replace($purchase, ['expires_at' => '2099-01-01']), '127.0.0.1');
if (!$first['success'] || !$retry['success'] || $retry['data']['expires_at'] !== '2026-10-05'
    || (int) $database->query('SELECT COUNT(*) FROM licenses')->fetchColumn() !== 1
    || (int) $database->query('SELECT COUNT(*) FROM activations')->fetchColumn() !== 1) {
    throw new RuntimeException('Retry duplicated or extended registration.');
}
foreach (['transaction_ref' => 'INV-OTHER', 'hardware_id' => 'other-device', 'plan' => '365 Days'] as $field => $value) {
    $result = App\Services\LicenseRegistration::register($database, array_replace($purchase, [$field => $value]), '127.0.0.1');
    if ($result['status'] !== 409 || $result['success']) {
        throw new RuntimeException('Conflicting registration accepted: ' . $field);
    }
}
$database->failActivation = true;
try {
    App\Services\LicenseRegistration::register($database, array_replace($purchase, ['license_key' => 'FAIL-KEY']), '127.0.0.1');
    throw new LogicException('Activation failure was ignored.');
} catch (RuntimeException $expected) {
    if ($expected->getMessage() !== 'Simulated activation failure') {
        throw $expected;
    }
}
if ((int) $database->query('SELECT COUNT(*) FROM licenses')->fetchColumn() !== 1 || $database->inTransaction()) {
    throw new RuntimeException('Partial registration was not rolled back.');
}
echo "PASS: Registration idempotency, fixed expiry, conflicts and atomic rollback (SQLite; MySQL locks simulated)\n";