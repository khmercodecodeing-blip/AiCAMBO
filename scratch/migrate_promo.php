<?php
/**
 * Database Migration Script
 * Creates the promo_codes table and adds discount columns to invoices
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/models/Database.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    
    // 1. Create promo_codes table
    $db->query("
        CREATE TABLE IF NOT EXISTS promo_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) NOT NULL UNIQUE,
            discount_type ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
            discount_value DECIMAL(10,2) NOT NULL,
            max_uses INT DEFAULT NULL,
            uses_count INT NOT NULL DEFAULT 0,
            expires_at DATETIME DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_code (code),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Table 'promo_codes' verified/created successfully.\n";
    
    // 2. Add columns to invoices table if they don't exist
    $cols = $db->fetchAll("SHOW COLUMNS FROM invoices");
    $hasPromoCode = false;
    $hasDiscountAmount = false;
    foreach ($cols as $col) {
        if ($col['Field'] === 'promo_code') $hasPromoCode = true;
        if ($col['Field'] === 'discount_amount') $hasDiscountAmount = true;
    }
    
    if (!$hasPromoCode) {
        $db->query("ALTER TABLE invoices ADD COLUMN promo_code VARCHAR(50) DEFAULT NULL AFTER amount");
        echo "Column 'promo_code' added successfully.\n";
    } else {
        echo "Column 'promo_code' already exists.\n";
    }
    
    if (!$hasDiscountAmount) {
        $db->query("ALTER TABLE invoices ADD COLUMN discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER promo_code");
        echo "Column 'discount_amount' added successfully.\n";
    } else {
        echo "Column 'discount_amount' already exists.\n";
    }
    
    // 3. Seed sample promo codes if empty
    $count = $db->fetch("SELECT COUNT(*) as cnt FROM promo_codes")['cnt'];
    if ($count == 0) {
        $db->query("INSERT INTO promo_codes (code, discount_type, discount_value, max_uses) VALUES 
            ('WELCOME10', 'percentage', 10.00, 100),
            ('SAVE5', 'fixed', 5.00, 50)
        ");
        echo "Sample promo codes seeded successfully.\n";
    } else {
        echo "Promo codes table already seeded.\n";
    }
    
    echo "Migration completed successfully!\n";
} catch (\Throwable $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
}
