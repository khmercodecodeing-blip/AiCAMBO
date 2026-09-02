<?php
/**
 * Database Migration Script
 * Adds license_key and hardware_id columns to invoices table
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/Database.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    
    // Check columns of invoices table
    $cols = $db->fetchAll("SHOW COLUMNS FROM invoices");
    $hasLicenseKey = false;
    $hasHardwareId = false;
    foreach ($cols as $col) {
        if ($col['Field'] === 'license_key') $hasLicenseKey = true;
        if ($col['Field'] === 'hardware_id') $hasHardwareId = true;
    }
    
    if (!$hasLicenseKey) {
        $db->query("ALTER TABLE invoices ADD COLUMN license_key VARCHAR(255) DEFAULT NULL");
        echo "Column 'license_key' added successfully.\n";
    } else {
        echo "Column 'license_key' already exists.\n";
    }
    
    if (!$hasHardwareId) {
        $db->query("ALTER TABLE invoices ADD COLUMN hardware_id VARCHAR(255) DEFAULT NULL");
        echo "Column 'hardware_id' added successfully.\n";
    } else {
        echo "Column 'hardware_id' already exists.\n";
    }
    
    echo "Migration completed successfully!\n";
} catch (\Throwable $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
}
