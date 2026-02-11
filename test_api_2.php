<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

echo "Testing Database Connection...\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "Connection Successful!\n";
        
        // Test Product Model
        require_once __DIR__ . '/models/Product.php';
        echo "Product Model Included.\n";
        
        $product = new Product($db);
        echo "Product Instantiated.\n";
        
        $result = $product->getAll();
        echo "getAll Executed. Row count: " . $result->rowCount() . "\n";
        
    } else {
        echo "Connection Failed (returned null).\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
?>
