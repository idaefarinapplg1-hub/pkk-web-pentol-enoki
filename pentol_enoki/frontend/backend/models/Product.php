<?php
/**
 * Product Model
 */

class Product {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getAll($includeArchived = false) {
        $query = "SELECT p.*, c.category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.category_id";
        
        if (!$includeArchived) {
            $query .= " WHERE p.is_archived = FALSE";
        }
        
        $query .= " ORDER BY p.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $query = "SELECT p.*, c.category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.category_id 
                  WHERE p.product_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function create($data) {
        $query = "INSERT INTO products (category_id, product_name, description, price, image_url, stock, is_available) 
                  VALUES (:category_id, :product_name, :description, :price, :image_url, :stock, :is_available)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':product_name', $data['product_name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':image_url', $data['image_url']);
        $stmt->bindParam(':stock', $data['stock']);
        $stmt->bindParam(':is_available', $data['is_available']);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        
        if (isset($data['category_id'])) {
            $fields[] = "category_id = :category_id";
            $params[':category_id'] = $data['category_id'];
        }
        if (isset($data['product_name'])) {
            $fields[] = "product_name = :product_name";
            $params[':product_name'] = $data['product_name'];
        }
        if (isset($data['description'])) {
            $fields[] = "description = :description";
            $params[':description'] = $data['description'];
        }
        if (isset($data['price'])) {
            $fields[] = "price = :price";
            $params[':price'] = $data['price'];
        }
        if (isset($data['image_url'])) {
            $fields[] = "image_url = :image_url";
            $params[':image_url'] = $data['image_url'];
        }
        if (isset($data['stock'])) {
            $fields[] = "stock = :stock";
            $params[':stock'] = $data['stock'];
        }
        if (isset($data['is_available'])) {
            $fields[] = "is_available = :is_available";
            $params[':is_available'] = $data['is_available'];
        }
        if (isset($data['is_archived'])) {
            $fields[] = "is_archived = :is_archived";
            $params[':is_archived'] = $data['is_archived'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $query = "UPDATE products SET " . implode(', ', $fields) . " WHERE product_id = :id";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM products WHERE product_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    public function archive($id, $archived = true) {
        $query = "UPDATE products SET is_archived = :archived WHERE product_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':archived', $archived, PDO::PARAM_BOOL);
        return $stmt->execute();
    }
    
    public function updateStock($id, $quantity) {
        $query = "UPDATE products SET stock = stock - :quantity WHERE product_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':quantity', $quantity);
        return $stmt->execute();
    }
}
?>
