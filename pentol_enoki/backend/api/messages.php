<?php
/**
 * Messages API Endpoint (Contact Admin)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $auth->requireLogin();
        
        if ($auth->isAdmin()) {
            // Admin sees all messages
            $query = "SELECT m.*, u.full_name, u.email 
                      FROM messages m 
                      JOIN users u ON m.user_id = u.user_id 
                      ORDER BY m.created_at DESC";
            $stmt = $db->prepare($query);
        } else {
            // User sees only their messages
            $query = "SELECT * FROM messages WHERE user_id = :user_id ORDER BY created_at DESC";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
        }
        
        $stmt->execute();
        $messages = $stmt->fetchAll();
        
        jsonResponse(['success' => true, 'data' => $messages]);
        break;
        
    case 'POST':
        $auth->requireLogin();
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if ($auth->isAdmin() && isset($data['message_id'])) {
            // Admin replying to message
            $query = "UPDATE messages SET status = 'replied', admin_reply = :reply, replied_at = NOW() 
                      WHERE message_id = :message_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':reply', sanitizeInput($data['reply']));
            $stmt->bindParam(':message_id', $data['message_id']);
            
            if ($stmt->execute()) {
                jsonResponse(['success' => true, 'message' => 'Reply sent']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to send reply'], 500);
            }
        } else {
            // User sending new message
            $query = "INSERT INTO messages (user_id, subject, message) 
                      VALUES (:user_id, :subject, :message)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':subject', sanitizeInput($data['subject']));
            $stmt->bindParam(':message', sanitizeInput($data['message']));
            
            if ($stmt->execute()) {
                jsonResponse(['success' => true, 'message' => 'Message sent'], 201);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to send message'], 500);
            }
        }
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
?>
