<?php
require_once __DIR__ . '/../config/database.php';

class AuthController {
    private $conn;
    private $table = 'users';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validation
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            echo json_encode([
                'success' => false,
                'error' => 'Username, email and password are required'
            ]);
            return;
        }
        
        // Check if email exists
        $checkQuery = "SELECT id FROM " . $this->table . " WHERE email = ?";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->execute([$data['email']]);
        
        if ($checkStmt->rowCount() > 0) {
            echo json_encode([
                'success' => false,
                'error' => 'Email already registered'
            ]);
            return;
        }
        
        // Insert user
        $query = "INSERT INTO " . $this->table . " 
                  (username, email, password, full_name, role) 
                  VALUES (?, ?, ?, ?, ?)";
        
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        $fullName = $data['full_name'] ?? '';
        $role = 'customer';
        
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([$data['username'], $data['email'], $hashedPassword, $fullName, $role])) {
            $userId = $this->conn->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'id' => $userId,
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'role' => $role
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Registration failed'
            ]);
        }
    }

    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['email']) || empty($data['password'])) {
            echo json_encode([
                'success' => false,
                'error' => 'Email and password required'
            ]);
            return;
        }
        
        $query = "SELECT id, username, email, password, role 
                  FROM " . $this->table . " 
                  WHERE email = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$data['email']]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($data['password'], $user['password'])) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'data' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'token' => base64_encode($user['id'] . '_' . time())
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid password'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'User not found'
            ]);
        }
    }
}
?>