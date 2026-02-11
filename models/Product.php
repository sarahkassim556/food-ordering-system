<?php
class Product {
    private $conn;
    private $table = 'products';

    public $id;
    public $name;
    public $description;
    public $price;
    public $category_id;
    public $image;
    public $is_vegetarian;
    public $is_spicy;
    public $is_active;

    // ...

    // Get all products
    public function getAll() {
        $query = "SELECT 
                    p.*, 
                    c.name as category_name
                  FROM " . $this->table . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.is_active = 1
                  ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get single product
    public function getById($id) {
        $query = "SELECT 
                    p.*, 
                    c.name as category_name
                  FROM " . $this->table . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = ? AND p.is_active = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt;
    }

    // Get products by category
    public function getByCategory($category_id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE category_id = ? AND is_active = 1
                  ORDER BY name";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $category_id);
        $stmt->execute();
        return $stmt;
    }

    // Search products
    public function search($keyword) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE (name LIKE ? OR description LIKE ?) 
                  AND is_active = 1
                  ORDER BY name";

        $keyword = "%{$keyword}%";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $keyword);
        $stmt->bindParam(2, $keyword);
        $stmt->execute();
        return $stmt;
    }
}
?>