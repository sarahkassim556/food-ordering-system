<?php
require_once __DIR__ . '/../config/database.php';

class UserController {
    private $conn;
    private $table = 'users';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll() {
        $query = "SELECT id, username, email, full_name, role, created_at 
                  FROM " . $this->table . " 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'count' => count($users),
            'data' => $users
        ]);
    }
}
?>