<?php
require_once __DIR__ . '/../models/Category.php';

class CategoryController {
    private $db;
    private $category;

    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
        $this->category = new Category($this->db);
    }

    // Get all categories
    public function getAllCategories() {
        $stmt = $this->category->getAllWithCount();
        
        $categories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'image' => $row['image'] ? 'uploads/products/' . $row['image'] : 'assets/images/default-category.jpg',
                'product_count' => (int) $row['product_count'],
                'created_at' => $row['created_at']
            ];
        }

        return json_encode([
            'success' => true,
            'data' => $categories,
            'count' => count($categories)
        ]);
    }
}
?>