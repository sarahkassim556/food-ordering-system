<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password;
    public $full_name;
    public $role;

    // Constructor: runs automatically when object is created
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create() {
       
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username = :username, 
                      email = :email, 
                      password = :password,
                      full_name = :full_name,
                      role = :role";

        // Prepare the SQL statement
        $stmt = $this->conn->prepare($query);

        // Hash the password for security
        $hashed_password = password_hash($this->password, PASSWORD_BCRYPT);

        // Bind parameters (link variables to SQL placeholders)
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":role", $this->role);

        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Check if email already exists
    public function emailExists() {
        $query = "SELECT id, username, password, role 
                  FROM " . $this->table_name . " 
                  WHERE email = ? 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Get user data
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->password = $row['password'];
            $this->role = $row['role'];
            return true;
        }

        return false;
    }

    public function readAll() {
        $query = "SELECT id, username, email, full_name, role, created_at 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
}
?>