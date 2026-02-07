<?php
require_once __DIR__ . '/../models/User.php';

class UserController {
    
    public function getAll() {
        $user = new User();
        $stmt = $user->readAll();
        
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Remove password from response
            unset($row['password']);
            $users[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'count' => count($users),
            'data' => $users
        ]);
    }
    
    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $user = new User();
        $user->username = $data['username'] ?? '';
        $user->email = $data['email'] ?? '';
        $user->password = $data['password'] ?? '';
        $user->full_name = $data['full_name'] ?? '';
        $user->role = $data['role'] ?? 'customer';
        
        if ($user->create()) {
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'User created',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create user']);
        }
    }
}
?>