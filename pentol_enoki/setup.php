<?php
/**
 * Setup Database - Insert Products
 */

require_once __DIR__ . '/backend/config/database.php';

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup Database</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; padding: 10px; background: #e8f5e9; border-radius: 5px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #ffebee; border-radius: 5px; margin: 10px 0; }
        .btn { padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Setup Database - Siap Santap</h1>
    
    <?php
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        echo '<div class="success">✓ Database connected</div>';
        
        // Check if products exist
        $stmt = $db->query("SELECT COUNT(*) as total FROM products");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $result['total'];
        
        echo "<p>Current products in database: <strong>$total</strong></p>";
        
        if ($total == 0) {
            echo '<p>Inserting sample products...</p>';
            
            $products = [
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
            foreach ($products as $product) {
                if ($stmt->execute($product)) {
                    $inserted++;
                }
            }
            
            echo "<div class='success'>✓ Inserted $inserted products</div>";
        } else {
            echo '<p>Enabling all products...</p>';
            $stmt = $db->prepare("UPDATE products SET is_available = 1, is_archived = 0");
            $stmt->execute();
            echo '<div class="success">✓ All products enabled</div>';
        }
        
        // Show products
        echo '<h2>Products in Database:</h2>';
        $stmt = $db->query("SELECT product_id, product_name, price, stock, is_available, is_archived FROM products");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<table border="1" cellpadding="10" style="width:100%; border-collapse: collapse;">';
        echo '<tr><th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Available</th><th>Archived</th></tr>';
        foreach ($products as $p) {
            echo '<tr>';
            echo '<td>' . $p['product_id'] . '</td>';
            echo '<td>' . htmlspecialchars($p['product_name']) . '</td>';
            echo '<td>Rp ' . number_format($p['price'], 0, ',', '.') . '</td>';
            echo '<td>' . $p['stock'] . '</td>';
            echo '<td>' . ($p['is_available'] ? '✓' : '✗') . '</td>';
            echo '<td>' . ($p['is_archived'] ? '✓' : '✗') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        
        echo '<h2>Next Steps:</h2>';
        echo '<ol>';
        echo '<li>Clear browser cache (Ctrl+Shift+Delete)</li>';
        echo '<li>Open <a href="frontend/pages/index.html" target="_blank">Home Page</a></li>';
        echo '<li>Open <a href="frontend/pages/menu.html" target="_blank">Menu Page</a></li>';
        echo '</ol>';
        
    } catch (Exception $e) {
        echo '<div class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    ?>
    
</body>
</html>
