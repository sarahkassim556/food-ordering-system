<?php
require_once __DIR__ . '/../config/database.php';

class Product {
    private $conn;
    private $table = 'products';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " WHERE is_available = 1 ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getByCategory($category) {
        $query = "SELECT * FROM " . $this->table . " WHERE category = ? AND is_available = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$category]);
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt;
    }
}
?>