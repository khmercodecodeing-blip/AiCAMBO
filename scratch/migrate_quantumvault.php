<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/Database.php';
require_once dirname(__DIR__) . '/app/Models/QuantumVaultOrderModel.php';

$database = App\Models\Database::getInstance();
$columns = [
    'courses' => [
        'qv_product_key' => 'VARCHAR(191) DEFAULT NULL',
        'qv_variant_key' => 'VARCHAR(191) DEFAULT NULL',
        'qv_max_cost' => 'DECIMAL(12,4) DEFAULT NULL',
    ],
    'invoices' => [
        'qv_product_key' => 'VARCHAR(191) DEFAULT NULL',
        'qv_variant_key' => 'VARCHAR(191) DEFAULT NULL',
        'qv_max_cost' => 'DECIMAL(12,4) DEFAULT NULL',
        'qv_status' => 'VARCHAR(20) DEFAULT NULL',
        'qv_order_id' => 'VARCHAR(191) DEFAULT NULL',
        'qv_response' => 'MEDIUMTEXT DEFAULT NULL',
        'qv_attempted_at' => 'DATETIME DEFAULT NULL',
        'delivered_stock' => 'MEDIUMTEXT DEFAULT NULL',
    ],
];

foreach ($columns as $table => $definitions) {
    $existing = array_column($database->fetchAll("SHOW COLUMNS FROM `$table`"), 'Field');
    foreach ($definitions as $column => $definition) {
        if (!in_array($column, $existing, true)) {
            $database->query("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        }
    }
}
$database->query('ALTER TABLE invoices MODIFY COLUMN delivered_stock MEDIUMTEXT DEFAULT NULL');
$indexes = array_column($database->fetchAll('SHOW INDEX FROM invoices'), 'Key_name');
if (!in_array('uq_invoices_qv_order', $indexes, true)) {
    $database->query('CREATE UNIQUE INDEX uq_invoices_qv_order ON invoices (qv_order_id)');
}
App\Models\QuantumVaultOrderModel::assertUniqueOrderIndex($database->getConnection());
echo "QuantumVault schema ready. Automatic purchasing remains disabled until enabled in the server environment.\n";