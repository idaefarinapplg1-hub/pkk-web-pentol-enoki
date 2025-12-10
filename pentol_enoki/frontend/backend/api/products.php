<?php
/**
 * Products API Endpoint
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Product.php';

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);
$auth = new Auth();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $result = $product->getById($_GET['id']);
            jsonResponse(['success' => true, 'data' => $result]);
        } else {
            $includeArchived = isset($_GET['archived']) && $_GET['archived'] === 'true';
            $result = $product->getAll($includeArchived);
            jsonResponse(['success' => true, 'data' => $result]);
        }
        break;
        
    case 'POST':
        $auth->requireAdmin();
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Handle file upload
        $imageUrl = null;
        if (isset($_FILES['image'])) {
            $target_dir = UPLOAD_DIR;
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $imageUrl = 'uploads/' . $new_filename;
            }
        }
        
        $productData = [
            'category_id' => $data['category_id'] ?? null,
            'product_name' => sanitizeInput($data['product_name']),
            'description' => sanitizeInput($data['description']),
            'price' => $data['price'],
            'image_url' => $imageUrl ?? $data['image_url'] ?? null,
            'stock' => $data['stock'] ?? 0,
            'is_available' => $data['is_available'] ?? true
        ];
        
        $result = $product->create($productData);
        
        if ($result) {
            jsonResponse(['success' => true, 'message' => 'Product created', 'product_id' => $result], 201);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to create product'], 500);
        }
        break;
        
    case 'PUT':
        $auth->requireAdmin();
        
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['product_id'] ?? $_GET['id'] ?? null;
        
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Product ID required'], 400);
        }
        
        // Build update data - only include fields that are provided
        $productData = [];
        
        if (isset($data['category_id'])) {
            $productData['category_id'] = $data['category_id'];
        }
        
        if (isset($data['product_name'])) {
            $productData['product_name'] = sanitizeInput($data['product_name']);
        }
        
        if (isset($data['description'])) {
            $productData['description'] = sanitizeInput($data['description']);
        }
        
        if (isset($data['price'])) {
            $productData['price'] = $data['price'];
        }
        
        if (isset($data['image_url'])) {
            $productData['image_url'] = $data['image_url'];
        }
        
        if (isset($data['stock'])) {
            $productData['stock'] = $data['stock'];
        }
        
        if (isset($data['is_available'])) {
            $productData['is_available'] = $data['is_available'] ? 1 : 0;
        }
        
        if (isset($data['is_archived'])) {
            $productData['is_archived'] = $data['is_archived'] ? 1 : 0;
        }
        
        $result = $product->update($id, $productData);
        
        if ($result) {
            jsonResponse(['success' => true, 'message' => 'Product updated']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to update product'], 500);
        }
        break;
        
    case 'DELETE':
        $auth->requireAdmin();
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Product ID required'], 400);
        }
        
        $result = $product->delete($id);
        
        if ($result) {
            jsonResponse(['success' => true, 'message' => 'Product deleted']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to delete product'], 500);
        }
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
?>
