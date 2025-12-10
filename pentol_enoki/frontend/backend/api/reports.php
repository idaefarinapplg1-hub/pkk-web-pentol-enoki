<?php
/**
 * Reports API Endpoint
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Order.php';

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);
$auth = new Auth();

$auth->requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $type = $_GET['type'] ?? 'daily';
    $date = $_GET['date'] ?? date('Y-m-d');
    
    switch ($type) {
        case 'revenue':
            $period = $_GET['period'] ?? 'daily';
            $revenue = $order->getRevenue($period, $date);
            jsonResponse(['success' => true, 'data' => $revenue]);
            break;
            
        case 'sales':
            // Get all sales transactions
            $query = "SELECT o.*, u.full_name, u.email 
                      FROM orders o 
                      JOIN users u ON o.user_id = u.user_id 
                      WHERE o.payment_status = 'paid'
                      ORDER BY o.created_at DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $sales = $stmt->fetchAll();
            
            jsonResponse(['success' => true, 'data' => $sales]);
            break;
            
        case 'summary':
            // Get comprehensive summary
            $daily = $order->getRevenue('daily', $date);
            $weekly = $order->getRevenue('weekly', $date);
            $monthly = $order->getRevenue('monthly', $date);
            
            // Get top products
            $topQuery = "SELECT p.product_name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as revenue
                         FROM order_items oi
                         JOIN products p ON oi.product_id = p.product_id
                         JOIN orders o ON oi.order_id = o.order_id
                         WHERE o.payment_status = 'paid'
                         GROUP BY p.product_id
                         ORDER BY total_sold DESC
                         LIMIT 10";
            $topStmt = $db->prepare($topQuery);
            $topStmt->execute();
            $topProducts = $topStmt->fetchAll();
            
            jsonResponse([
                'success' => true,
                'data' => [
                    'daily' => $daily,
                    'weekly' => $weekly,
                    'monthly' => $monthly,
                    'top_products' => $topProducts
                ]
            ]);
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid report type'], 400);
    }
} else {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
?>
