<?php
require_once '../models/User.php';

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        require_once '../config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    // User registration
    public function register() {
        $data = json_decode(file_get_contents("php://input"));

        // Validate input
        if (empty($data->email) || empty($data->password) || empty($data->username)) {
            http_response_code(400);
            return json_encode([
                'success' => false,
                'message' => 'Email, password, and username are required'
            ]);
        }

        // Check if email already exists
        $this->user->email = $data->email;
        if ($this->user->emailExists()) {
            http_response_code(409);
            return json_encode([
                'success' => false,
                'message' => 'Email already exists'
            ]);
        }

        // Set user properties
        $this->user->username = $data->username;
        $this->user->email = $data->email;
        $this->user->password = $data->password;
        $this->user->full_name = $data->full_name ?? '';
        $this->user->phone = $data->phone ?? '';
        $this->user->role_id = 2; // Default role: customer

        // Create user
        if ($this->user->create()) {
            http_response_code(201);
            return json_encode([
                'success' => true,
                'message' => 'User registered successfully'
            ]);
        } else {
            http_response_code(500);
            return json_encode([
                'success' => false,
                'message' => 'Registration failed'
            ]);
        }
    }

    // User login
    public function login() {
        $data = json_decode(file_get_contents("php://input"));

        // Validate input
        if (empty($data->email) || empty($data->password)) {
            http_response_code(400);
            return json_encode([
                'success' => false,
                'message' => 'Email and password are required'
            ]);
        }

        // Check if user exists
        $this->user->email = $data->email;
        if (!$this->user->emailExists()) {
            http_response_code(401);
            return json_encode([
                'success' => false,
                'message' => 'Invalid credentials'
            ]);
        }

        // Verify password
        if (password_verify($data->password, $this->user->password)) {
            // Generate JWT token (simplified - in real app use proper JWT)
            $token = $this->generateToken($this->user->id);
            
            // Get user details (excluding password)
            $userDetails = [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'email' => $this->user->email,
                'full_name' => $this->user->full_name,
                'role_id' => $this->user->role_id
            ];

            http_response_code(200);
            return json_encode([
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'user' => $userDetails
            ]);
        } else {
            http_response_code(401);
            return json_encode([
                'success' => false,
                'message' => 'Invalid credentials'
            ]);
        }
    }

    // Generate simple token (for demo - use JWT in production)
    private function generateToken($user_id) {
        $token_data = [
            'user_id' => $user_id,
            'created_at' => time(),
            'expires_at' => time() + (24 * 60 * 60) // 24 hours
        ];
        return base64_encode(json_encode($token_data));
    }

    // Verify token (for demo)
    public static function verifyToken($token) {
        try {
            $decoded = json_decode(base64_decode($token), true);
            if ($decoded && isset($decoded['user_id']) && $decoded['expires_at'] > time()) {
                return $decoded['user_id'];
            }
        } catch (Exception $e) {
            return false;
        }
        return false;
    }
}
?>