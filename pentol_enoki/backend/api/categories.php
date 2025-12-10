<?php
/**
 * Categories API Endpoint
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Category.php';

$database = new Database();
$db = $database->getConnection();
$category = new Category($db);
$auth = new Auth();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $result = $category->getById($_GET['id']);
            jsonResponse(['success' => true, 'data' => $result]);
        } else {
            $result = $category->getAll();
            jsonResponse(['success' => true, 'data' => $result]);
        }
        break;
        
    case 'POST':
        $auth->requireAdmin();
        
        $data = json_decode(file_get_contents("php://input"), true);
        $result = $category->create(
            sanitizeInput($data['category_name']),
            sanitizeInput($data['description'] ?? null)
        );
        
        if ($result) {
            jsonResponse(['success' => true, 'message' => 'Category created', 'category_id' => $result], 201);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to create category'], 500);
        }
        break;
        
    case 'PUT':
        $auth->requireAdmin();
        
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['category_id'] ?? $_GET['id'] ?? null;
        
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Category ID required'], 400);
        }
        
        $result = $category->update(
            $id,
            sanitizeInput($data['category_name']),
            sanitizeInput($data['description'] ?? null)
        );
        
        if ($result) {
            jsonResponse(['success' => true, 'message' => 'Category updated']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to update category'], 500);
        }
        break;
        
    case 'DELETE':
        $auth->requireAdmin();
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Category ID required'], 400);
        }
        
        $result = $category->delete($id);
        
        if ($result) {
            jsonResponse(['success' => true, 'message' => 'Category deleted']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to delete category'], 500);
        }
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
?>
