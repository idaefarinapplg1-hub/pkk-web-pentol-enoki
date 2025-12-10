<?php
/**
 * Debug Products - Check Database and API
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Products</title>
    <style>
        body {
            font-family: monospace;
            padding: 2rem;
            background: #f5f5f5;
        }
        .section {
            background: white;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { color: #4CAF50; font-weight: bold; }
        .error { color: #F44336; font-weight: bold; }
        .warning { color: #FF9800; font-weight: bold; }
        pre {
            background: #f0f0f0;
            padding: 1rem;
            border-radius: 5px;
            overflow-x: auto;
        }
        h2 {
            color: #FF4444;
            border-bottom: 2px solid #FF4444;
            padding-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <h1>🔍 Debug Products - Siap Santap</h1>

    <?php
    // 1. Check Database Connection
    echo '<div class="section">';
    echo '<h2>1. Database Connection</h2>';
    
    try {
        require_once __DIR__ . '/backend/config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            echo '<p class="success">✓ Database connected successfully!</p>';
            echo '<p>Database: ' . $db->query("SELECT DATABASE()")->fetchColumn() . '</p>';
        } else {
            echo '<p class="error">✗ Database connection failed!</p>';
        }
    } catch (Exception $e) {
        echo '<p class="error">✗ Error: ' . $e->getMessage() . '</p>';
    }
    echo '</div>';

    // 2. Check Products Table
    echo '<div class="section">';
    echo '<h2>2. Products Table</h2>';
    
    try {
        $stmt = $db->query("SELECT COUNT(*) as total FROM products");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $result['total'];
        
        if ($total > 0) {
            echo '<p class="success">✓ Found ' . $total . ' products in database</p>';
        } else {
            echo '<p class="warning">⚠ No products found in database!</p>';
            echo '<p>You need to add products first.</p>';
        }
        
        // Show table structure
        $stmt = $db->query("DESCRIBE products");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo '<h3>Table Structure:</h3>';
        echo '<pre>';
        foreach ($columns as $col) {
            echo $col['Field'] . ' (' . $col['Type'] . ')' . PHP_EOL;
        }
        echo '</pre>';
        
    } catch (Exception $e) {
        echo '<p class="error">✗ Error: ' . $e->getMessage() . '</p>';
    }
    echo '</div>';

    // 3. Check Products Data
    echo '<div class="section">';
    echo '<h2>3. Products Data</h2>';
    
    try {
        $stmt = $db->query("SELECT p.*, c.category_name 
                           FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.category_id 
                           ORDER BY p.created_at DESC 
                           LIMIT 10");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($products) > 0) {
            echo '<p class="success">✓ Showing first 10 products:</p>';
            echo '<pre>';
            foreach ($products as $product) {
                echo '---' . PHP_EOL;
                echo 'ID: ' . $product['product_id'] . PHP_EOL;
                echo 'Name: ' . $product['product_name'] . PHP_EOL;
                echo 'Price: Rp ' . number_format($product['price'], 0, ',', '.') . PHP_EOL;
                echo 'Stock: ' . $product['stock'] . PHP_EOL;
                echo 'Available: ' . ($product['is_available'] ? 'Yes' : 'No') . PHP_EOL;
                echo 'Archived: ' . ($product['is_archived'] ? 'Yes' : 'No') . PHP_EOL;
                echo 'Category: ' . ($product['category_name'] ?? 'None') . PHP_EOL;
            }
            echo '</pre>';
        } else {
            echo '<p class="warning">⚠ No products to display</p>';
        }
        
    } catch (Exception $e) {
        echo '<p class="error">✗ Error: ' . $e->getMessage() . '</p>';
    }
    echo '</div>';

    // 4. Check Available Products
    echo '<div class="section">';
    echo '<h2>4. Available Products (for Frontend)</h2>';
    
    try {
        $stmt = $db->query("SELECT COUNT(*) as total 
                           FROM products 
                           WHERE is_available = 1 AND is_archived = 0");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $available = $result['total'];
        
        if ($available > 0) {
            echo '<p class="success">✓ ' . $available . ' products available for display</p>';
            
            $stmt = $db->query("SELECT product_id, product_name, price, stock 
                               FROM products 
                               WHERE is_available = 1 AND is_archived = 0 
                               LIMIT 5");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<pre>';
            foreach ($products as $product) {
                echo $product['product_id'] . '. ' . $product['product_name'] . 
                     ' - Rp ' . number_format($product['price'], 0, ',', '.') . 
                     ' (Stock: ' . $product['stock'] . ')' . PHP_EOL;
            }
            echo '</pre>';
        } else {
            echo '<p class="error">✗ No available products for display!</p>';
            echo '<p>Products might be:</p>';
            echo '<ul>';
            echo '<li>Marked as unavailable (is_available = 0)</li>';
            echo '<li>Archived (is_archived = 1)</li>';
            echo '<li>Not exist in database</li>';
            echo '</ul>';
        }
        
    } catch (Exception $e) {
        echo '<p class="error">✗ Error: ' . $e->getMessage() . '</p>';
    }
    echo '</div>';

    // 5. Test API Endpoint
    echo '<div class="section">';
    echo '<h2>5. API Endpoint Test</h2>';
    
    try {
        require_once __DIR__ . '/backend/models/Product.php';
        $product = new Product($db);
        $result = $product->getAll(false);
        
        if (is_array($result) && count($result) > 0) {
            echo '<p class="success">✓ API Model working! Found ' . count($result) . ' products</p>';
        } else {
            echo '<p class="warning">⚠ API Model returns empty array</p>';
        }
        
    } catch (Exception $e) {
        echo '<p class="error">✗ Error: ' . $e->getMessage() . '</p>';
    }
    echo '</div>';

    // 6. Check Categories
    echo '<div class="section">';
    echo '<h2>6. Categories</h2>';
    
    try {
        $stmt = $db->query("SELECT * FROM categories ORDER BY category_name");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($categories) > 0) {
            echo '<p class="success">✓ Found ' . count($categories) . ' categories</p>';
            echo '<pre>';
            foreach ($categories as $cat) {
                echo $cat['category_id'] . '. ' . $cat['category_name'] . PHP_EOL;
            }
            echo '</pre>';
        } else {
            echo '<p class="warning">⚠ No categories found</p>';
        }
        
    } catch (Exception $e) {
        echo '<p class="error">✗ Error: ' . $e->getMessage() . '</p>';
    }
    echo '</div>';

    // 7. Recommendations
    echo '<div class="section">';
    echo '<h2>7. Recommendations</h2>';
    
    $recommendations = [];
    
    // Check if products exist
    $stmt = $db->query("SELECT COUNT(*) as total FROM products");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($total == 0) {
        $recommendations[] = '<strong>Add Products:</strong> Run sample-data.sql or add products manually via admin panel';
    }
    
    // Check available products
    $stmt = $db->query("SELECT COUNT(*) as total FROM products WHERE is_available = 1 AND is_archived = 0");
    $available = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($available == 0 && $total > 0) {
        $recommendations[] = '<strong>Enable Products:</strong> Set is_available = 1 and is_archived = 0 for products';
    }
    
    // Check categories
    $stmt = $db->query("SELECT COUNT(*) as total FROM categories");
    $catTotal = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($catTotal == 0) {
        $recommendations[] = '<strong>Add Categories:</strong> Add at least one category first';
    }
    
    if (count($recommendations) > 0) {
        echo '<ul>';
        foreach ($recommendations as $rec) {
            echo '<li class="warning">' . $rec . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p class="success">✓ Everything looks good!</p>';
    }
    
    echo '</div>';

    // 8. Quick Fix SQL
    if ($available == 0 && $total > 0) {
        echo '<div class="section">';
        echo '<h2>8. Quick Fix SQL</h2>';
        echo '<p>Run this SQL to enable all products:</p>';
        echo '<pre>UPDATE products SET is_available = 1, is_archived = 0;</pre>';
        echo '<p><a href="http://localhost/phpmyadmin" target="_blank">Open phpMyAdmin</a></p>';
        echo '</div>';
    }
    ?>

    <div class="section">
        <h2>9. Test Links</h2>
        <ul>
            <li><a href="backend/api/products.php" target="_blank">Test API: backend/api/products.php</a></li>
            <li><a href="frontend/pages/index.html" target="_blank">Home Page</a></li>
            <li><a href="frontend/pages/menu.html" target="_blank">Menu Page</a></li>
            <li><a href="frontend/pages/admin.html" target="_blank">Admin Page</a></li>
            <li><a href="test-products.html" target="_blank">Test Products Page</a></li>
        </ul>
    </div>

</body>
</html>
