<?php
/**
 * Authentication API Endpoint
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

$auth = new Auth();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    switch ($action) {
        case 'register':
            $result = $auth->register(
                sanitizeInput($data['username']),
                sanitizeInput($data['email']),
                $data['password'],
                sanitizeInput($data['full_name']),
                sanitizeInput($data['phone'] ?? null),
                sanitizeInput($data['address'] ?? null)
            );
            jsonResponse($result, $result['success'] ? 201 : 400);
            break;
            
        case 'login':
            $result = $auth->login(
                sanitizeInput($data['username']),
                $data['password']
            );
            jsonResponse($result, $result['success'] ? 200 : 401);
            break;
            
        case 'logout':
            $result = $auth->logout();
            jsonResponse($result);
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} else if ($method === 'GET' && $action === 'check') {
    if ($auth->isLoggedIn()) {
        jsonResponse([
            'success' => true,
            'logged_in' => true,
            'user' => [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'full_name' => $_SESSION['full_name'],
                'role' => $_SESSION['role']
            ]
        ]);
    } else {
        jsonResponse(['success' => true, 'logged_in' => false]);
    }
} else {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
?>
