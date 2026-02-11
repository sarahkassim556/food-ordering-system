<?php
require_once __DIR__ . '/../models/Product.php';

class ProductController {
    private $db;
    private $product;

    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
        $this->product = new Product($this->db);
    }

    // Get all products
    public function getAllProducts() {
        $stmt = $this->product->getAll();
        
        $products = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $this->formatProduct($row);
        }

        return json_encode([
            'success' => true,
            'data' => $products,
            'count' => count($products)
        ]);
    }

    // Get single product
    public function getProduct($id) {
        if (!is_numeric($id)) {
            http_response_code(400);
            return json_encode([
                'success' => false,
                'message' => 'Invalid product ID'
            ]);
        }

        $stmt = $this->product->getById($id);
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return json_encode([
                'success' => true,
                'data' => $this->formatProduct($row)
            ]);
        } else {
            http_response_code(404);
            return json_encode([
                'success' => false,
                'message' => 'Product not found'
            ]);
        }
    }

    // Get products by category
    public function getByCategory($category_id) {
        if (!is_numeric($category_id)) {
            http_response_code(400);
            return json_encode([
                'success' => false,
                'message' => 'Invalid category ID'
            ]);
        }

        $stmt = $this->product->getByCategory($category_id);
        
        $products = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $this->formatProduct($row);
        }

        return json_encode([
            'success' => true,
            'data' => $products,
            'count' => count($products)
        ]);
    }

    // Search products
    public function search($keyword) {
        if (empty($keyword)) {
            return $this->getAllProducts();
        }

        $stmt = $this->product->search($keyword);
        
        $products = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $this->formatProduct($row);
        }

        return json_encode([
            'success' => true,
            'data' => $products,
            'count' => count($products)
        ]);
    }

    // Create Product
    public function create() {
        $user_id = $this->getAuthenticatedUserId();
        if (!$user_id) return $this->sendError(401, 'Unauthorized');
        // Add Admin check here

        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->name) || empty($data->price) || empty($data->category_id)) {
            return $this->sendError(400, 'Incomplete data');
        }

        $this->product->name = $data->name;
        $this->product->price = $data->price;
        $this->product->description = $data->description ?? '';
        $this->product->category_id = $data->category_id;
        $this->product->image = $data->image ?? '';
        $this->product->is_active = 1;

        if ($this->product->create()) {
            echo json_encode(['success' => true, 'message' => 'Product created']);
        } else {
            $this->sendError(500, 'Failed to create product');
        }
    }

    // Update Product
    public function update($id) {
        $user_id = $this->getAuthenticatedUserId();
        if (!$user_id) return $this->sendError(401, 'Unauthorized');

        $data = json_decode(file_get_contents("php://input"));
        
        $this->product->id = $id;
        $this->product->name = $data->name;
        $this->product->price = $data->price;
        $this->product->description = $data->description ?? '';
        $this->product->category_id = $data->category_id;
        $this->product->image = $data->image ?? '';
        $this->product->is_active = $data->is_active ?? 1;

        if ($this->product->update()) {
            echo json_encode(['success' => true, 'message' => 'Product updated']);
        } else {
            $this->sendError(500, 'Failed to update product');
        }
    }

    // Delete Product
    public function delete($id) {
        $user_id = $this->getAuthenticatedUserId();
        if (!$user_id) return $this->sendError(401, 'Unauthorized');

        $this->product->id = $id;
        if ($this->product->delete()) {
             echo json_encode(['success' => true, 'message' => 'Product deleted']);
        } else {
             $this->sendError(500, 'Failed to delete product');
        }
    }

    private function sendError($code, $message) {
        http_response_code($code);
        echo json_encode(['success' => false, 'message' => $message]);
    }
    
    private function getAuthenticatedUserId() {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
             $token = str_replace('Bearer ', '', $headers['Authorization']);
             return AuthController::verifyToken($token);
        }
        return false;
    }

    // Format product response
    private function formatProduct($row) {
        return [
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'price' => (float) $row['price'],
            'category_id' => $row['category_id'],
            'category_name' => $row['category_name'] ?? '',
            'image' => $row['image'], // Return as is from DB
            'is_active' => (bool) $row['is_active'],
            'created_at' => $row['created_at']
        ];
    }
}
?>