<?php
require_once __DIR__ . '/../models/Product.php';

class ProductController {
    public function getAll() {
        $product = new Product();
        $stmt = $product->getAll();
        
        $products = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'count' => count($products),
            'data' => $products
        ]);
    }
    
    public function getByCategory($category) {
        $product = new Product();
        $stmt = $product->getByCategory($category);
        
        $products = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'count' => count($products),
            'data' => $products
        ]);
    }
}
?>