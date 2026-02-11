<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config/database.php';

echo "Fixing Database Schema...\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Add is_active to products if missing
    try {
        $sql = "ALTER TABLE products ADD COLUMN is_active BOOLEAN DEFAULT TRUE";
        $db->exec($sql);
        echo "Added is_active to products.\n";
    } catch (PDOException $e) {
        echo "is_active might already exist in products or error: " . $e->getMessage() . "\n";
    }

    // Add image to products if missing
    try {
        $sql = "ALTER TABLE products ADD COLUMN image VARCHAR(255)";
        $db->exec($sql);
        echo "Added image to products (if missing).\n"; // Won't happen if it exists
    } catch (PDOException $e) {
         // Ignore if exists
    }

    echo "Database Fix Complete.\n";
    
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}
?>
