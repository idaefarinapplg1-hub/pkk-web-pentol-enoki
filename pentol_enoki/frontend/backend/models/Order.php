<?php
/**
 * Order Model
 */

class Order {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function create($userId, $orderData, $items) {
        try {
            $this->db->beginTransaction();
            
            // Create order
            $orderNumber = generateOrderNumber();
            $query = "INSERT INTO orders (user_id, order_number, total_amount, payment_method, 
                      shipping_address, phone, notes) 
                      VALUES (:user_id, :order_number, :total_amount, :payment_method, 
                      :shipping_address, :phone, :notes)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':order_number', $orderNumber);
            $stmt->bindParam(':total_amount', $orderData['total_amount']);
            $stmt->bindParam(':payment_method', $orderData['payment_method']);
            $stmt->bindParam(':shipping_address', $orderData['shipping_address']);
            $stmt->bindParam(':phone', $orderData['phone']);
            $stmt->bindParam(':notes', $orderData['notes']);
            $stmt->execute();
            
            $orderId = $this->db->lastInsertId();
            
            // Create order items
            $itemQuery = "INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) 
                          VALUES (:order_id, :product_id, :quantity, :price, :subtotal)";
            $itemStmt = $this->db->prepare($itemQuery);
            
            foreach ($items as $item) {
                $itemStmt->bindParam(':order_id', $orderId);
                $itemStmt->bindParam(':product_id', $item['product_id']);
                $itemStmt->bindParam(':quantity', $item['quantity']);
                $itemStmt->bindParam(':price', $item['price']);
                $itemStmt->bindParam(':subtotal', $item['subtotal']);
                $itemStmt->execute();
                
                // Update stock
                $stockQuery = "UPDATE products SET stock = stock - :quantity WHERE product_id = :product_id";
                $stockStmt = $this->db->prepare($stockQuery);
                $stockStmt->bindParam(':quantity', $item['quantity']);
                $stockStmt->bindParam(':product_id', $item['product_id']);
                $stockStmt->execute();
            }
            
            // Create initial tracking
            $trackingQuery = "INSERT INTO order_tracking (order_id, status, description) 
                              VALUES (:order_id, 'pending', 'Order placed successfully')";
            $trackingStmt = $this->db->prepare($trackingQuery);
            $trackingStmt->bindParam(':order_id', $orderId);
            $trackingStmt->execute();
            
            $this->db->commit();
            
            return [
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function getByUser($userId) {
        $query = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getById($orderId) {
        $query = "SELECT o.*, u.full_name, u.email 
                  FROM orders o 
                  JOIN users u ON o.user_id = u.user_id 
                  WHERE o.order_id = :order_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function getItems($orderId) {
        $query = "SELECT oi.*, p.product_name, p.image_url 
                  FROM order_items oi 
                  JOIN products p ON oi.product_id = p.product_id 
                  WHERE oi.order_id = :order_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getTracking($orderId) {
        $query = "SELECT * FROM order_tracking WHERE order_id = :order_id ORDER BY created_at ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function updateStatus($orderId, $status, $description = '') {
        try {
            $this->db->beginTransaction();
            
            // Update order status
            $query = "UPDATE orders SET order_status = :status WHERE order_id = :order_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->execute();
            
            // Add tracking
            $trackingQuery = "INSERT INTO order_tracking (order_id, status, description) 
                              VALUES (:order_id, :status, :description)";
            $trackingStmt = $this->db->prepare($trackingQuery);
            $trackingStmt->bindParam(':order_id', $orderId);
            $trackingStmt->bindParam(':status', $status);
            $trackingStmt->bindParam(':description', $description);
            $trackingStmt->execute();
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    public function getAll() {
        $query = "SELECT o.*, u.full_name, u.email 
                  FROM orders o 
                  JOIN users u ON o.user_id = u.user_id 
                  ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getRevenue($period = 'daily', $date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        
        $conditions = [
            'daily' => "DATE(created_at) = :date",
            'weekly' => "YEARWEEK(created_at, 1) = YEARWEEK(:date, 1)",
            'monthly' => "YEAR(created_at) = YEAR(:date) AND MONTH(created_at) = MONTH(:date)"
        ];
        
        $query = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_revenue,
                    AVG(total_amount) as average_order
                  FROM orders 
                  WHERE payment_status = 'paid' AND " . $conditions[$period];
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        return $stmt->fetch();
    }
}
?>
