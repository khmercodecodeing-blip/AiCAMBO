<?php
/**
 * Database Migration Script
 * Creates the product_stocks table and adds delivered_stock column to invoices
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/Database.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    
    // 1. Create product_stocks table
    $db->query("
        CREATE TABLE IF NOT EXISTS product_stocks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_id INT NOT NULL,
            stock_content TEXT NOT NULL,
            is_sold TINYINT(1) NOT NULL DEFAULT 0,
            invoice_no VARCHAR(50) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            sold_at TIMESTAMP NULL DEFAULT NULL,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
            INDEX idx_course_sold (course_id, is_sold),
            INDEX idx_invoice_no (invoice_no)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Table 'product_stocks' verified/created successfully.\n";
    
    // 2. Add columns to invoices table if they don't exist
    $cols = $db->fetchAll("SHOW COLUMNS FROM invoices");
    $hasDeliveredStock = false;
    foreach ($cols as $col) {
        if ($col['Field'] === 'delivered_stock') $hasDeliveredStock = true;
    }
    
    if (!$hasDeliveredStock) {
        $db->query("ALTER TABLE invoices ADD COLUMN delivered_stock TEXT DEFAULT NULL AFTER license_key");
        echo "Column 'delivered_stock' added successfully to invoices.\n";
    } else {
        echo "Column 'delivered_stock' already exists in invoices.\n";
    }
    
    echo "Migration completed successfully!\n";
} catch (\Throwable $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
}
