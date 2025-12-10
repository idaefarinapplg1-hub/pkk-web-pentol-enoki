<?php
/**
 * Test Archive/Unarchive Product
 */

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/backend/config/database.php';
require_once __DIR__ . '/backend/models/Product.php';

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Archive Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: #f5f5f5;
        }
        .section {
            background: white;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #FF4444;
            border-bottom: 2px solid #FF4444;
            padding-bottom: 0.5rem;
        }
        .success { color: #4CAF50; font-weight: bold; }
        .error { color: #F44336; font-weight: bold; }
        .warning { color: #FF9800; font-weight: bold; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f0f0f0;
            font-weight: bold;
        }
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            margin: 0.25rem;
        }
        .btn-archive {
            background: #FF9800;
            color: white;
        }
        .btn-unarchive {
            background: #4CAF50;
            color: white;
        }
        .btn-enable {
            background: #2196F3;
            color: white;
        }
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-available { background: #4CAF50; color: white; }
        .badge-unavailable { background: #F44336; color: white; }
        .badge-archived { background: #9E9E9E; color: white; }
    </style>
</head>
<body>
    <h1>🧪 Test Archive/Unarchive Product</h1>

    <?php
    // Handle actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $productId = $_POST['product_id'] ?? 0;
        
        echo '<div class="section">';
        echo '<h2>Action Result</h2>';
        
        if ($action === 'archive') {
            $result = $product->update($productId, ['is_archived' => 1]);
            if ($result) {
                echo '<p class="success">✓ Product archived successfully!</p>';
            } else {
                echo '<p class="error">✗ Failed to archive product</p>';
            }
        } elseif ($action === 'unarchive') {
            $result = $product->update($productId, ['is_archived' => 0]);
            if ($result) {
                echo '<p class="success">✓ Product unarchived successfully!</p>';
            } else {
                echo '<p class="error">✗ Failed to unarchive product</p>';
            }
        } elseif ($action === 'enable') {
            $result = $product->update($productId, [
                'is_available' => 1,
                'is_archived' => 0
            ]);
            if ($result) {
                echo '<p class="success">✓ Product enabled successfully!</p>';
            } else {
                echo '<p class="error">✗ Failed to enable product</p>';
            }
        } elseif ($action === 'disable') {
            $result = $product->update($productId, ['is_available' => 0]);
            if ($result) {
                echo '<p class="success">✓ Product disabled successfully!</p>';
            } else {
                echo '<p class="error">✗ Failed to disable product</p>';
            }
        }
        
        echo '</div>';
    }
    ?>

    <div class="section">
        <h2>All Products</h2>
        <?php
        $products = $product->getAll(true);
        
        if (count($products) > 0) {
            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th>ID</th>';
            echo '<th>Name</th>';
            echo '<th>Price</th>';
            echo '<th>Stock</th>';
            echo '<th>Status</th>';
            echo '<th>Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach ($products as $p) {
                echo '<tr>';
                echo '<td>' . $p['product_id'] . '</td>';
                echo '<td>' . htmlspecialchars($p['product_name']) . '</td>';
                echo '<td>Rp ' . number_format($p['price'], 0, ',', '.') . '</td>';
                echo '<td>' . $p['stock'] . '</td>';
                echo '<td>';
                
                if ($p['is_archived']) {
                    echo '<span class="badge badge-archived">Archived</span>';
                } elseif ($p['is_available']) {
                    echo '<span class="badge badge-available">Available</span>';
                } else {
                    echo '<span class="badge badge-unavailable">Unavailable</span>';
                }
                
                echo '</td>';
                echo '<td>';
                
                // Action buttons
                if ($p['is_archived']) {
                    echo '<form method="POST" style="display: inline;">';
                    echo '<input type="hidden" name="product_id" value="' . $p['product_id'] . '">';
                    echo '<input type="hidden" name="action" value="unarchive">';
                    echo '<button type="submit" class="btn btn-unarchive">📂 Unarchive</button>';
                    echo '</form>';
                } else {
                    echo '<form method="POST" style="display: inline;">';
                    echo '<input type="hidden" name="product_id" value="' . $p['product_id'] . '">';
                    echo '<input type="hidden" name="action" value="archive">';
                    echo '<button type="submit" class="btn btn-archive">📁 Archive</button>';
                    echo '</form>';
                }
                
                if (!$p['is_available'] || $p['is_archived']) {
                    echo '<form method="POST" style="display: inline;">';
                    echo '<input type="hidden" name="product_id" value="' . $p['product_id'] . '">';
                    echo '<input type="hidden" name="action" value="enable">';
                    echo '<button type="submit" class="btn btn-enable">✓ Enable</button>';
                    echo '</form>';
                } else {
                    echo '<form method="POST" style="display: inline;">';
                    echo '<input type="hidden" name="product_id" value="' . $p['product_id'] . '">';
                    echo '<input type="hidden" name="action" value="disable">';
                    echo '<button type="submit" class="btn btn-archive">✗ Disable</button>';
                    echo '</form>';
                }
                
                echo '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p class="warning">⚠ No products found</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>Quick Actions</h2>
        <form method="POST" style="display: inline;">
            <input type="hidden" name="action" value="enable_all">
            <button type="submit" class="btn btn-enable" onclick="return confirm('Enable all products?')">
                ✓ Enable All Products
            </button>
        </form>
        
        <?php
        if (isset($_POST['action']) && $_POST['action'] === 'enable_all') {
            $stmt = $db->prepare("UPDATE products SET is_available = 1, is_archived = 0");
            if ($stmt->execute()) {
                echo '<p class="success">✓ All products enabled!</p>';
                echo '<script>setTimeout(() => location.reload(), 1000);</script>';
            }
        }
        ?>
    </div>

    <div class="section">
        <h2>Test Links</h2>
        <ul>
            <li><a href="debug-products.php" target="_blank">Debug Products</a></li>
            <li><a href="test-api-direct.html" target="_blank">Test API Direct</a></li>
            <li><a href="frontend/pages/admin.html" target="_blank">Admin Page</a></li>
            <li><a href="frontend/pages/index.html" target="_blank">Home Page</a></li>
        </ul>
    </div>

</body>
</html>
