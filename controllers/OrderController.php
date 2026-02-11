<?php
require_once '../models/Order.php';
require_once '../models/OrderItem.php';
require_once '../controllers/AuthController.php';

class OrderController {
    private $db;
    private $order;
    private $orderItem;

    public function __construct() {
        require_once '../config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
        $this->order = new Order($this->db);
        $this->orderItem = new OrderItem($this->db);
    }

    // Place Order
    public function placeOrder() {
        $user_id = $this->getAuthenticatedUserId();
        if (!$user_id) {
            $this->sendError(401, 'Authentication required');
            return;
        }

        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->items) || !is_array($data->items)) {
            $this->sendError(400, 'Order items are required');
            return;
        }

        try {
            $this->db->beginTransaction();

            // 1. Calculate Total & Create Order
            $total_amount = 0;
            foreach ($data->items as $item) {
                // In production, fetch price from DB to avoid manipulation
                // For this project, assuming frontend sends price or we trust it/fetch it
                // Better: Fetch product price
                $stmt = $this->db->prepare("SELECT price FROM products WHERE id = ?");
                $stmt->execute([$item->product_id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$product) {
                    throw new Exception("Product ID {$item->product_id} not found");
                }
                
                $total_amount += $product['price'] * $item->quantity;
            }

            $this->order->user_id = $user_id;
            $this->order->total_amount = $total_amount;
            $this->order->status = 'pending';

            if (!$this->order->create()) {
                throw new Exception("Failed to create order");
            }

            $order_id = $this->order->id;

            // 2. Create Order Items
            foreach ($data->items as $item) {
                $this->orderItem->order_id = $order_id;
                $this->orderItem->product_id = $item->product_id;
                $this->orderItem->quantity = $item->quantity;
                
                // Fetch price again for item record
                $stmt = $this->db->prepare("SELECT price FROM products WHERE id = ?");
                $stmt->execute([$item->product_id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                $this->orderItem->price = $product['price'];

                if (!$this->orderItem->create()) {
                    throw new Exception("Failed to create order item");
                }
            }

            // 3. Create Payment Record (Pending)
            $stmt = $this->db->prepare("INSERT INTO payments (order_id, method, status) VALUES (?, ?, 'pending')");
            $method = $data->payment_method ?? 'cash'; // Default
            $stmt->execute([$order_id, $method]);

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Order placed successfully',
                'order_id' => $order_id
            ]);

        } catch (Exception $e) {
            $this->db->rollBack();
            $this->sendError(500, $e->getMessage());
        }
    }

    // Get User Orders
    public function getUserOrders() {
        $user_id = $this->getAuthenticatedUserId();
        if (!$user_id) {
            $this->sendError(401, 'Authentication required');
            return;
        }

        $stmt = $this->order->getByUser($user_id);
        $orders = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Fetch items for each order
            $items_stmt = $this->orderItem->getByOrder($row['id']);
            $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $row['items'] = $items;
            $orders[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $orders
        ]);
    }

    // Get All Orders (Admin)
    public function getAllOrders() {
        // In a real app, verify admin role here
        $user_id = $this->getAuthenticatedUserId();
        if (!$user_id) { // Simplified check
             $this->sendError(401, 'Authentication required');
             return;
        }
        
        $stmt = $this->order->getAll();
        $orders = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $orders[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $orders
        ]);
    }

    // Update Order Status (Admin)
    public function updateOrderStatus($id) {
        // Helper to update status
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->status)) {
            $this->sendError(400, 'Status is required');
            return;
        }

        $query = "UPDATE " . $this->order->table . " SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $data->status);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
             echo json_encode(['success' => true, 'message' => 'Order status updated']);
        } else {
             $this->sendError(500, 'Failed to update order');
        }
    }

    // Helper: Send Error
    private function sendError($code, $message) {
        http_response_code($code);
        echo json_encode(['success' => false, 'message' => $message]);
    }

    // Verify Token
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
