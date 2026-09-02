<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/Database.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    $courses = $db->fetchAll("SELECT * FROM courses");
    echo json_encode($courses, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
