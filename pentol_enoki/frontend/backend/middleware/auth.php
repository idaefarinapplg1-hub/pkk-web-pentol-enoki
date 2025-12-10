<?php
/**
 * Authentication Middleware
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function register($username, $email, $password, $full_name, $phone = null, $address = null) {
        try {
            // Validate input
            if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
                throw new Exception("All fields are required");
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }
            
            if (strlen($password) < 6) {
                throw new Exception("Password must be at least 6 characters");
            }
            
            // Check if user exists
            $query = "SELECT user_id FROM users WHERE username = :username OR email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                throw new Exception("Username or email already exists");
            }
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert user
            $query = "INSERT INTO users (username, email, password, full_name, phone, address) 
                      VALUES (:username, :email, :password, :full_name, :phone, :address)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Registration successful',
                    'user_id' => $this->db->lastInsertId()
                ];
            }
            
            throw new Exception("Registration failed");
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function login($username, $password) {
        try {
            if (empty($username) || empty($password)) {
                throw new Exception("Username and password are required");
            }
            
            $query = "SELECT * FROM users WHERE username = :username OR email = :username";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                throw new Exception("Invalid credentials");
            }
            
            $user = $stmt->fetch();
            
            if (!password_verify($password, $user['password'])) {
                throw new Exception("Invalid credentials");
            }
            
            // Set session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'user_id' => $user['user_id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role']
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Logout successful'];
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
        }
    }
    
    public function requireAdmin() {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            jsonResponse(['success' => false, 'message' => 'Admin access required'], 403);
        }
    }
}
?>
