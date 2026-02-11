<?php
class OrderItem {
    private $conn;
    private $table = 'order_items';

    public $id;
    public $order_id;
    public $product_id;
    public $quantity;
    public $price;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create Order Item
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET order_id = :order_id, 
                      product_id = :product_id, 
                      quantity = :quantity, 
                      price = :price";

        $stmt = $this->conn->prepare($query);

        // Bind
        $stmt->bindParam(':order_id', $this->order_id);
        $stmt->bindParam(':product_id', $this->product_id);
        $stmt->bindParam(':quantity', $this->quantity);
        $stmt->bindParam(':price', $this->price);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get items for an order
    public function getByOrder($order_id) {
        $query = "SELECT oi.*, p.name, p.image 
                  FROM " . $this->table . " oi
                  JOIN products p ON oi.product_id = p.id
                  WHERE oi.order_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $order_id);
        $stmt->execute();
        return $stmt;
    }
}
?>
