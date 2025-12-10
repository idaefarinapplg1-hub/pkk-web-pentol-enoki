<?php
/**
 * Fix All - Comprehensive Fix for Products
 */

header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix All - Siap Santap</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: white;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .section {
            background: white;
            padding: 2rem;
            margin-bottom: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .section h2 {
            color: #667eea;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid #667eea;
            font-size: 1.5rem;
        }
        .success {
            color: #4CAF50;
            font-weight: bold;
            padding: 1rem;
            background: #e8f5e9;
            border-left: 4px solid #4CAF50;
            border-radius: 5px;
            margin: 0.5rem 0;
        }
        .error {
            color: #F44336;
            font-weight: bold;
            padding: 1rem;
            background: #ffebee;
            border-left: 4px solid #F44336;
            border-radius: 5px;
            margin: 0.5rem 0;
        }
        .warning {
            color: #FF9800;
            font-weight: bold;
            padding: 1rem;
            background: #fff3e0;
            border-left: 4px solid #FF9800;
            border-radius: 5px;
            margin: 0.5rem 0;
        }
        .info {
            color: #2196F3;
            font-weight: bold;
            padding: 1rem;
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            border-radius: 5px;
            margin: 0.5rem 0;
        }
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            margin: 0.5rem;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn:hover {
            background: #764ba2;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
        }
        .btn-success {
            background: #4CAF50;
        }
        .btn-success:hover {
            background: #45a049;
        }
        .btn-danger {
            background: #F44336;
        }
        .btn-danger:hover {
            background: #da190b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
            color: #667eea;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-success {
            background: #4CAF50;
            color: white;
        }
        .badge-danger {
            background: #F44336;
            color: white;
        }
        .badge-warning {
            background: #FF9800;
            color: white;
        }
        .badge-secondary {
            background: #9E9E9E;
            color: white;
        }
        pre {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 1rem;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 0.9rem;
        }
        .step {
            background: #f5f5f5;
            padding: 1rem;
            margin: 0.5rem 0;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            font-weight: bold;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix All - Siap Santap</h1>

        <?php
        $fixes = [];
        $errors = [];
        
        // Step 1: Database Connection
        echo '<div class="section">';
        echo '<h2><span class="step-number">1</span> Database Connection</h2>';
        
        try {
            require_once __DIR__ . '/backend/config/database.php';
            $database = new Database();
            $db = $database->getConnection();
            
            if ($db) {
                echo '<div class="success">✓ Database connected successfully!</div>';
                $fixes[] = 'Database connection established';
            } else {
                echo '<div class="error">✗ Database connection failed!</div>';
                $errors[] = 'Database connection failed';
            }
        } catch (Exception $e) {
            echo '<div class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            $errors[] = 'Database error: ' . $e->getMessage();
        }
        echo '</div>';

        if ($db) {
            // Step 2: Check Products Table
            echo '<div class="section">';
            echo '<h2><span class="step-number">2</span> Check Products Table</h2>';
            
            try {
                $stmt = $db->query("SELECT COUNT(*) as total FROM products");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalProducts = $result['total'];
                
                if ($totalProducts > 0) {
                    echo '<div class="info">ℹ Found ' . $totalProducts . ' products in database</div>';
                    $fixes[] = 'Products table has data';
                } else {
                    echo '<div class="warning">⚠ No products found! Will insert sample data...</div>';
                }
            } catch (Exception $e) {
                echo '<div class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                $errors[] = 'Products table error';
            }
            echo '</div>';

            // Step 3: Insert Sample Data if Needed
            if ($totalProducts == 0) {
                echo '<div class="section">';
                echo '<h2><span class="step-number">3</span> Insert Sample Data</h2>';
                
                try {
                    $sampleProducts = [
                        [1, 'Nasi Goreng Special', 'Nasi goreng dengan telur, ayam, dan sayuran segar', 25000, 50],
                        [1, 'Mie Goreng', 'Mie goreng pedas dengan topping lengkap', 20000, 45],
                        [1, 'Ayam Geprek', 'Ayam goreng crispy dengan sambal geprek pedas', 30000, 40],
                        [1, 'Soto Ayam', 'Soto ayam kuah kuning dengan nasi', 22000, 35],
                        [2, 'Es Teh Manis', 'Es teh manis segar', 5000, 100],
                        [2, 'Jus Jeruk', 'Jus jeruk segar tanpa gula', 12000, 50],
                        [3, 'Pisang Goreng', 'Pisang goreng crispy', 10000, 30],
                        [3, 'Tahu Isi', 'Tahu isi sayuran dengan saus kacang', 8000, 40]
                    ];
                    
                    $stmt = $db->prepare("INSERT INTO products (category_id, product_name, description, price, stock, is_available, is_archived) VALUES (?, ?, ?, ?, ?, 1, 0)");
                    
                    $inserted = 0;
                    foreach ($sampleProducts as $product) {
                        if ($stmt->execute($product)) {
                            $inserted++;
                        }
                    }
                    
                    echo '<div class="success">✓ Inserted ' . $inserted . ' sample products</div>';
                    $fixes[] = 'Sample products inserted';
                    $totalProducts = $inserted;
                } catch (Exception $e) {
                    echo '<div class="error">✗ Error inserting sample data: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    $errors[] = 'Failed to insert sample data';
                }
                echo '</div>';
            }

            // Step 4: Enable All Products
            echo '<div class="section">';
            echo '<h2><span class="step-number">4</span> Enable All Products</h2>';
            
            try {
                $stmt = $db->prepare("UPDATE products SET is_available = 1, is_archived = 0");
                if ($stmt->execute()) {
                    $affected = $stmt->rowCount();
                    echo '<div class="success">✓ Enabled all products (affected: ' . $affected . ' rows)</div>';
                    $fixes[] = 'All products enabled';
                } else {
                    echo '<div class="warning">⚠ No products were updated</div>';
                }
            } catch (Exception $e) {
                echo '<div class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                $errors[] = 'Failed to enable products';
            }
            echo '</div>';

            // Step 5: Verify Products
            echo '<div class="section">';
            echo '<h2><span class="step-number">5</span> Current Products Status</h2>';
            
            try {
                $stmt = $db->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.product_id");
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($products) > 0) {
                    $available = array_filter($products, function($p) {
                        return $p['is_available'] == 1 && $p['is_archived'] == 0;
                    });
                    
                    echo '<div class="info">ℹ Total: ' . count($products) . ' | Available: ' . count($available) . '</div>';
                    
                    echo '<table>';
                    echo '<thead><tr>';
                    echo '<th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th>';
                    echo '</tr></thead>';
                    echo '<tbody>';
                    
                    foreach ($products as $p) {
                        echo '<tr>';
                        echo '<td>' . $p['product_id'] . '</td>';
                        echo '<td>' . htmlspecialchars($p['product_name']) . '</td>';
                        echo '<td>' . htmlspecialchars($p['category_name'] ?? 'None') . '</td>';
                        echo '<td>Rp ' . number_format($p['price'], 0, ',', '.') . '</td>';
                        echo '<td>' . $p['stock'] . '</td>';
                        echo '<td>';
                        
                        if ($p['is_archived']) {
                            echo '<span class="badge badge-secondary">Archived</span>';
                        } elseif ($p['is_available']) {
                            echo '<span class="badge badge-success">Available</span>';
                        } else {
                            echo '<span class="badge badge-danger">Unavailable</span>';
                        }
                        
                        echo '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody></table>';
                } else {
                    echo '<div class="warning">⚠ No products to display</div>';
                }
            } catch (Exception $e) {
                echo '<div class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            echo '</div>';

            // Step 6: Test API
            echo '<div class="section">';
            echo '<h2><span class="step-number">6</span> Test API Endpoint</h2>';
            
            try {
                require_once __DIR__ . '/backend/models/Product.php';
                $product = new Product($db);
                $apiResult = $product->getAll(false);
                
                if (is_array($apiResult) && count($apiResult) > 0) {
                    echo '<div class="success">✓ API working! Returns ' . count($apiResult) . ' products</div>';
                    $fixes[] = 'API endpoint working';
                } else {
                    echo '<div class="warning">⚠ API returns empty array</div>';
                    $errors[] = 'API returns no data';
                }
            } catch (Exception $e) {
                echo '<div class="error">✗ API Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                $errors[] = 'API error';
            }
            echo '</div>';
        }

        // Summary
        echo '<div class="section">';
        echo '<h2>📊 Summary</h2>';
        
        if (count($errors) == 0) {
            echo '<div class="success">';
            echo '<h3 style="margin-bottom: 1rem;">✓ All Checks Passed!</h3>';
            echo '<p><strong>Fixes Applied:</strong></p>';
            echo '<ul style="margin-left: 2rem; margin-top: 0.5rem;">';
            foreach ($fixes as $fix) {
                echo '<li>' . htmlspecialchars($fix) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        } else {
            echo '<div class="error">';
            echo '<h3 style="margin-bottom: 1rem;">✗ Some Issues Found</h3>';
            echo '<p><strong>Errors:</strong></p>';
            echo '<ul style="margin-left: 2rem; margin-top: 0.5rem;">';
            foreach ($errors as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
        echo '</div>';

        // Next Steps
        echo '<div class="section">';
        echo '<h2>🚀 Next Steps</h2>';
        echo '<div class="step">';
        echo '<p><strong>1. Clear Browser Cache</strong></p>';
        echo '<p>Press Ctrl+Shift+Delete and clear cache</p>';
        echo '</div>';
        echo '<div class="step">';
        echo '<p><strong>2. Test Pages</strong></p>';
        echo '<p>';
        echo '<a href="frontend/pages/index.html" class="btn btn-success" target="_blank">🏠 Home Page</a>';
        echo '<a href="frontend/pages/menu.html" class="btn btn-success" target="_blank">📋 Menu Page</a>';
        echo '<a href="frontend/pages/admin.html" class="btn btn-success" target="_blank">⚙️ Admin Page</a>';
        echo '</p>';
        echo '</div>';
        echo '<div class="step">';
        echo '<p><strong>3. Test Tools</strong></p>';
        echo '<p>';
        echo '<a href="test-api-direct.html" class="btn" target="_blank">🧪 Test API</a>';
        echo '<a href="test-archive-api.html" class="btn" target="_blank">📁 Test Archive</a>';
        echo '<a href="debug-products.php" class="btn" target="_blank">🔍 Debug Products</a>';
        echo '</p>';
        echo '</div>';
        echo '<div class="step">';
        echo '<p><strong>4. Refresh This Page</strong></p>';
        echo '<p><button class="btn btn-danger" onclick="location.reload()">🔄 Refresh</button></p>';
        echo '</div>';
        echo '</div>';
        ?>

    </div>
</body>
</html>
