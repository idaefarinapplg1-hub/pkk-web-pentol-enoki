<?php
/**
 * Orders API Endpoint
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Order.php';

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);
$auth = new Auth();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $auth->requireLogin();
        
        if (isset($_GET['id'])) {
            $orderId = $_GET['id'];
            $orderData = $order->getById($orderId);
            
            if (!$orderData) {
                jsonResponse(['success' => false, 'message' => 'Order not found'], 404);
            }
            
            // Check permission
            if (!$auth->isAdmin() && $orderData['user_id'] != $_SESSION['user_id']) {
                jsonResponse(['success' => false, 'message' => 'Access denied'], 403);
            }
            
            $items = $order->getItems($orderId);
            $tracking = $order->getTracking($orderId);
            
            jsonResponse([
                'success' => true,
                'data' => [
                    'order' => $orderData,
                    'items' => $items,
                    'tracking' => $tracking
                ]
            ]);
        } else {
            if ($auth->isAdmin()) {
                $orders = $order->getAll();
            } else {
                $orders = $order->getByUser($_SESSION['user_id']);
            }
            
            jsonResponse(['success' => true, 'data' => $orders]);
        }
        break;
        
    case 'POST':
        // Allow guest checkout (no auth required)
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['items']) || empty($data['items'])) {
            jsonResponse(['success' => false, 'message' => 'Order items required'], 400);
        }
        
        // Build shipping address from form fields
        $shippingAddress = sprintf(
            "%s\n%s, %s %s",
            $data['address'] ?? '',
            $data['city'] ?? '',
            $data['postal_code'] ?? '',
            ''
        );
        
        $orderData = [
            'full_name' => sanitizeInput($data['full_name'] ?? ''),
            'email' => sanitizeInput($data['email'] ?? ''),
            'phone' => sanitizeInput($data['phone'] ?? ''),
            'shipping_address' => sanitizeInput($shippingAddress),
            'city' => sanitizeInput($data['city'] ?? ''),
            'postal_code' => sanitizeInput($data['postal_code'] ?? ''),
            'payment_method' => sanitizeInput($data['payment_method'] ?? 'cod'),
            'notes' => sanitizeInput($data['notes'] ?? ''),
            'subtotal' => floatval($data['subtotal'] ?? 0),
            'tax' => floatval($data['tax'] ?? 0),
            'shipping' => floatval($data['shipping'] ?? 0),
            'total_amount' => floatval($data['total'] ?? 0)
        ];
        
        // Use user_id if logged in, otherwise null for guest
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        
        $result = $order->create($userId, $orderData, $data['items']);
        
        if ($result['success']) {
            jsonResponse($result, 201);
        } else {
            jsonResponse($result, 500);
        }
        break;
        
    case 'PUT':
        $auth->requireAdmin();
        
        $data = json_decode(file_get_contents("php://input"), true);
        $orderId = $data['order_id'] ?? $_GET['id'] ?? null;
        
        if (!$orderId) {
            jsonResponse(['success' => false, 'message' => 'Order ID required'], 400);
        }
        
        $status = $data['status'] ?? null;
        $description = $data['description'] ?? '';
        
        if (!$status) {
            jsonResponse(['success' => false, 'message' => 'Status required'], 400);
        }
        
        $result = $order->updateStatus($orderId, $status, $description);
        
        if ($result) {
            jsonResponse(['success' => true, 'message' => 'Order status updated']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to update order'], 500);
        }
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
?>
