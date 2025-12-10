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
        
        // Check if this is a file upload (multipart/form-data)
        $isFileUpload = isset($_FILES['image']) && $_FILES['image']['size'] > 0;
        
        if ($isFileUpload) {
            // Handle multipart form data
            $data = $_POST;
            
            // Handle file upload
            $imageUrl = null;
            $target_dir = __DIR__ . '/../../uploads/';
            
            // Create uploads directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            // Validate file extension
            if (!in_array($file_extension, $allowed_extensions)) {
                jsonResponse(['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF'], 400);
            }
            
            // Validate file size (5MB max)
            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                jsonResponse(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB'], 400);
            }
            
            $new_filename = 'product_' . uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $imageUrl = '../uploads/' . $new_filename;
            } else {
                jsonResponse(['success' => false, 'message' => 'Gagal mengupload gambar'], 500);
            }
            
            $productData = [
                'category_id' => !empty($data['category_id']) ? intval($data['category_id']) : null,
                'product_name' => sanitizeInput($data['product_name']),
                'description' => sanitizeInput($data['description'] ?? ''),
                'price' => intval($data['price']),
                'image_url' => $imageUrl,
                'stock' => intval($data['stock'] ?? 0),
                'is_available' => isset($data['is_available']) && $data['is_available'] == '1'
            ];
        } else {
            // Handle JSON data
            $data = json_decode(file_get_contents("php://input"), true);
            
            $productData = [
                'category_id' => $data['category_id'] ?? null,
                'product_name' => sanitizeInput($data['product_name']),
                'description' => sanitizeInput($data['description'] ?? ''),
                'price' => intval($data['price']),
                'image_url' => $data['image_url'] ?? null,
                'stock' => intval($data['stock'] ?? 0),
                'is_available' => $data['is_available'] ?? true
            ];
        }
        
        $result = $product->create($productData);
        
        if ($result) {
            jsonResponse(['success' => true, 'message' => 'Product created', 'product_id' => $result], 201);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to create product'], 500);
        }
        break;
        
    case 'PUT':
        $auth->requireAdmin();
        
        // Check if this is a file upload via POST with product_id
        $isFileUpload = isset($_FILES['image']) && $_FILES['image']['size'] > 0;
        
        if ($isFileUpload && isset($_POST['product_id'])) {
            // Handle multipart form data for update
            $data = $_POST;
            $id = $data['product_id'];
            
            // Handle file upload
            $imageUrl = null;
            $target_dir = __DIR__ . '/../../uploads/';
            
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (!in_array($file_extension, $allowed_extensions)) {
                jsonResponse(['success' => false, 'message' => 'Format file tidak didukung'], 400);
            }
            
            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                jsonResponse(['success' => false, 'message' => 'Ukuran file terlalu besar'], 400);
            }
            
            $new_filename = 'product_' . uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $imageUrl = '../uploads/' . $new_filename;
            }
            
            $productData = [];
            
            if (isset($data['category_id']) && $data['category_id'] !== '') {
                $productData['category_id'] = intval($data['category_id']);
            }
            
            if (isset($data['product_name'])) {
                $productData['product_name'] = sanitizeInput($data['product_name']);
            }
            
            if (isset($data['description'])) {
                $productData['description'] = sanitizeInput($data['description']);
            }
            
            if (isset($data['price'])) {
                $productData['price'] = intval($data['price']);
            }
            
            if ($imageUrl) {
                $productData['image_url'] = $imageUrl;
            }
            
            if (isset($data['stock'])) {
                $productData['stock'] = intval($data['stock']);
            }
            
            if (isset($data['is_available'])) {
                $productData['is_available'] = $data['is_available'] == '1' ? 1 : 0;
            }
            
            $result = $product->update($id, $productData);
            
            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Product updated']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to update product'], 500);
            }
        } else {
            // Handle JSON data
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
                $productData['price'] = intval($data['price']);
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
