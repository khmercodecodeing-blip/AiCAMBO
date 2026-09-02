<?php
/**
 * Database Initialization Script
 * Inserts plans 1, 2, and 3 for Telegram Adder Pro into the courses table.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/Database.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    
    $plans = [
        [
            'id' => 1,
            'title' => 'Telegram Adder - 1 Month',
            'description' => '1 Month License Key for Telegram Adder Pro',
            'price' => 7.00,
            'currency' => 'USD',
            'type' => 'tool',
            'download_link' => 'https://aicambo.store/Update/TelegramAdderPro.exe',
            'is_active' => 1
        ],
        [
            'id' => 2,
            'title' => 'Telegram Adder - 3 Months',
            'description' => '3 Months License Key for Telegram Adder Pro',
            'price' => 18.00,
            'currency' => 'USD',
            'type' => 'tool',
            'download_link' => 'https://aicambo.store/Update/TelegramAdderPro.exe',
            'is_active' => 1
        ],
        [
            'id' => 3,
            'title' => 'Telegram Adder - 1 Year',
            'description' => '1 Year License Key for Telegram Adder Pro',
            'price' => 50.00,
            'currency' => 'USD',
            'type' => 'tool',
            'download_link' => 'https://aicambo.store/Update/TelegramAdderPro.exe',
            'is_active' => 1
        ]
    ];
    
    foreach ($plans as $plan) {
        // Check if plan already exists
        $existing = $db->fetch("SELECT id FROM courses WHERE id = :id", [':id' => $plan['id']]);
        
        if ($existing) {
            // Update existing plan
            $db->query(
                "UPDATE courses SET title = :title, description = :description, price = :price, 
                                    currency = :currency, type = :type, download_link = :download_link, 
                                    is_active = :is_active 
                 WHERE id = :id",
                $plan
            );
            echo "Plan ID {$plan['id']} ('{$plan['title']}') updated successfully.\n";
        } else {
            // Insert new plan
            $db->query(
                "INSERT INTO courses (id, title, description, price, currency, type, download_link, is_active)
                 VALUES (:id, :title, :description, :price, :currency, :type, :download_link, :is_active)",
                $plan
            );
            echo "Plan ID {$plan['id']} ('{$plan['title']}') inserted successfully.\n";
        }
    }
    
    echo "Database initialization completed successfully!\n";
} catch (\Throwable $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
