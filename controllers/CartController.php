<?php
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../controllers/AuthController.php';

class CartController {
    private $db;
    private $cart;

    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
        $this->cart = new Cart($this->db);
    }

    // Get user's cart
    public function getCart() {
        $user_id = $this->getAuthenticatedUserId();
        
        if (!$user_id) {
            http_response_code(401);
            return json_encode([
                'success' => false,
                'message' => 'Authentication required'
            ]);
        }

        $cart_data = $this->cart->getCartWithItems($user_id);
        
        return json_encode([
            'success' => true,
            'data' => $cart_data
        ]);
    }

    // Add item to cart
    public function addToCart() {
        $user_id = $this->getAuthenticatedUserId();
        
        if (!$user_id) {
            http_response_code(401);
            return json_encode([
                'success' => false,
                'message' => 'Authentication required'
            ]);
        }

        $data = json_decode(file_get_contents("php://input"));

        // Validate input
        if (empty($data->product_id) || !is_numeric($data->product_id)) {
            http_response_code(400);
            return json_encode([
                'success' => false,
                'message' => 'Valid product ID is required'
            ]);
        }

        $product_id = $data->product_id;
        $quantity = $data->quantity ?? 1;
        $special_instructions = $data->special_instructions ?? '';

        // Check if product exists and is available
        $product_check = $this->db->prepare("SELECT id, is_available, stock_quantity FROM products WHERE id = ?");
        $product_check->execute([$product_id]);
        
        if ($product_check->rowCount() === 0) {
            http_response_code(404);
            return json_encode([
                'success' => false,
                'message' => 'Product not found'
            ]);
        }

        $product = $product_check->fetch(PDO::FETCH_ASSOC);
        if (!$product['is_available']) {
            http_response_code(400);
            return json_encode([
                'success' => false,
                'message' => 'Product is not available'
            ]);
        }

        // Add to cart
        if ($this->cart->addItem($user_id, $product_id, $quantity, $special_instructions)) {
            return json_encode([
                'success' => true,
                'message' => 'Item added to cart'
            ]);
        } else {
            http_response_code(500);
            return json_encode([
                'success' => false,
                'message' => 'Failed to add item to cart'
            ]);
        }
    }

    // Remove item from cart
    public function removeFromCart() {
        $user_id = $this->getAuthenticatedUserId();
        
        if (!$user_id) {
            http_response_code(401);
            return json_encode([
                'success' => false,
                'message' => 'Authentication required'
            ]);
        }

        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->cart_item_id) || !is_numeric($data->cart_item_id)) {
            http_response_code(400);
            return json_encode([
                'success' => false,
                'message' => 'Valid cart item ID is required'
            ]);
        }

        if ($this->cart->removeItem($user_id, $data->cart_item_id)) {
            return json_encode([
                'success' => true,
                'message' => 'Item removed from cart'
            ]);
        } else {
            http_response_code(500);
            return json_encode([
                'success' => false,
                'message' => 'Failed to remove item'
            ]);
        }
    }

    // Update cart item quantity
    public function updateCartItem() {
        $user_id = $this->getAuthenticatedUserId();
        
        if (!$user_id) {
            http_response_code(401);
            return json_encode([
                'success' => false,
                'message' => 'Authentication required'
            ]);
        }

        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->cart_item_id) || !is_numeric($data->cart_item_id) || 
            empty($data->quantity) || !is_numeric($data->quantity)) {
            http_response_code(400);
            return json_encode([
                'success' => false,
                'message' => 'Valid cart item ID and quantity are required'
            ]);
        }

        if ($this->cart->updateItemQuantity($user_id, $data->cart_item_id, $data->quantity)) {
            return json_encode([
                'success' => true,
                'message' => 'Cart updated'
            ]);
        } else {
            http_response_code(500);
            return json_encode([
                'success' => false,
                'message' => 'Failed to update cart'
            ]);
        }
    }

    // Clear cart
    public function clearCart() {
        $user_id = $this->getAuthenticatedUserId();
        
        if (!$user_id) {
            http_response_code(401);
            return json_encode([
                'success' => false,
                'message' => 'Authentication required'
            ]);
        }

        if ($this->cart->clearCart($user_id)) {
            return json_encode([
                'success' => true,
                'message' => 'Cart cleared'
            ]);
        } else {
            http_response_code(500);
            return json_encode([
                'success' => false,
                'message' => 'Failed to clear cart'
            ]);
        }
    }

    // Get authenticated user ID from token
    private function getAuthenticatedUserId() {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
            return AuthController::verifyToken($token);
        }
        
        return false;
    }
}
?>