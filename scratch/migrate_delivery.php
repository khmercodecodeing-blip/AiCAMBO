<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/Database.php';

try {
    $database = App\Models\Database::getInstance();
    $columns = array_column($database->fetchAll('SHOW COLUMNS FROM invoices'), 'Field');
    if (!in_array('license_delivery_status', $columns, true)) {
        $database->query("ALTER TABLE invoices ADD COLUMN license_delivery_status VARCHAR(16) NOT NULL DEFAULT 'pending'");
    }
    if (!in_array('license_delivery_attempted_at', $columns, true)) {
        $database->query('ALTER TABLE invoices ADD COLUMN license_delivery_attempted_at DATETIME DEFAULT NULL');
    }
    echo "License delivery migration completed.\n";
} catch (Throwable $error) {
    fwrite(STDERR, "License delivery migration failed. Check the database configuration and schema.\n");
    exit(1);
}