<?php
/**
 * Category Model
 */

class Category {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getAll() {
        $query = "SELECT * FROM categories ORDER BY category_name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $query = "SELECT * FROM categories WHERE category_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function create($name, $description = null) {
        $query = "INSERT INTO categories (category_name, description) VALUES (:name, :description)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    public function update($id, $name, $description = null) {
        $query = "UPDATE categories SET category_name = :name, description = :description WHERE category_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM categories WHERE category_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
