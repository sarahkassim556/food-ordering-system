<?php
class Cart {
    private $conn;
    private $cart_table = 'carts';
    private $cart_items_table = 'cart_items';

    public $id;
    public $user_id;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get or create cart for user
    public function getOrCreateCart($user_id) {
        // Check if cart exists
        $query = "SELECT id FROM " . $this->cart_table . " 
                  WHERE user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['id'];
        } else {
            // Create new cart
            $query = "INSERT INTO " . $this->cart_table . " (user_id) VALUES (?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $user_id);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        }
    }

    // Get cart with items
    public function getCartWithItems($user_id) {
        $cart_id = $this->getOrCreateCart($user_id);
        
        $query = "SELECT 
                    ci.id as cart_item_id,
                    ci.quantity,
                    ci.special_instructions,
                    p.id as product_id,
                    p.name,
                    p.description,
                    p.price,
                    p.image,
                    (p.price * ci.quantity) as item_total
                  FROM " . $this->cart_items_table . " ci
                  JOIN products p ON ci.product_id = p.id
                  WHERE ci.cart_id = ? AND p.is_available = 1
                  ORDER BY ci.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $cart_id);
        $stmt->execute();
        
        $items = [];
        $total = 0;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = $row;
            $total += $row['item_total'];
        }
        
        return [
            'cart_id' => $cart_id,
            'items' => $items,
            'total_items' => count($items),
            'total_amount' => $total
        ];
    }

    // Add item to cart
    public function addItem($user_id, $product_id, $quantity = 1, $special_instructions = '') {
        $cart_id = $this->getOrCreateCart($user_id);
        
        // Check if item already exists in cart
        $query = "SELECT id, quantity FROM " . $this->cart_items_table . " 
                  WHERE cart_id = ? AND product_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $cart_id);
        $stmt->bindParam(2, $product_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Update quantity
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $new_quantity = $row['quantity'] + $quantity;
            
            $query = "UPDATE " . $this->cart_items_table . " 
                      SET quantity = ?, updated_at = NOW()
                      WHERE id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $new_quantity);
            $stmt->bindParam(2, $row['id']);
        } else {
            // Insert new item
            $query = "INSERT INTO " . $this->cart_items_table . " 
                      (cart_id, product_id, quantity, special_instructions) 
                      VALUES (?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $cart_id);
            $stmt->bindParam(2, $product_id);
            $stmt->bindParam(3, $quantity);
            $stmt->bindParam(4, $special_instructions);
        }
        
        return $stmt->execute();
    }

    // Remove item from cart
    public function removeItem($user_id, $cart_item_id) {
        $cart_id = $this->getOrCreateCart($user_id);
        
        $query = "DELETE FROM " . $this->cart_items_table . " 
                  WHERE id = ? AND cart_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $cart_item_id);
        $stmt->bindParam(2, $cart_id);
        
        return $stmt->execute();
    }

    // Update cart item quantity
    public function updateItemQuantity($user_id, $cart_item_id, $quantity) {
        $cart_id = $this->getOrCreateCart($user_id);
        
        if ($quantity <= 0) {
            return $this->removeItem($user_id, $cart_item_id);
        }
        
        $query = "UPDATE " . $this->cart_items_table . " 
                  SET quantity = ?, updated_at = NOW()
                  WHERE id = ? AND cart_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $quantity);
        $stmt->bindParam(2, $cart_item_id);
        $stmt->bindParam(3, $cart_id);
        
        return $stmt->execute();
    }

    // Clear cart
    public function clearCart($user_id) {
        $cart_id = $this->getOrCreateCart($user_id);
        
        $query = "DELETE FROM " . $this->cart_items_table . " WHERE cart_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $cart_id);
        
        return $stmt->execute();
    }
}
?>