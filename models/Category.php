<?php
class Category {
    private $conn;
    private $table = 'categories';

    public $id;
    public $name;
    public $description;
    public $image;
    // public $is_active; // Removed as not in schema
    // public $created_at; // Not needed regarding schema check? Schema didn't have created_at for categories? Wait, let's check. Schema: id, name, description, image. No created_at.

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all categories
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " 
                  ORDER BY name";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get category with product count
    public function getAllWithCount() {
        $query = "SELECT 
                    c.*, 
                    COUNT(p.id) as product_count
                  FROM " . $this->table . " c
                  LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
                  GROUP BY c.id
                  ORDER BY c.name";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>