<?php
/**
 * Test Database Connection
 */

echo "<h2>Testing Database Connection</h2>";

// Test 1: Check if database file exists
echo "<h3>1. Checking files...</h3>";
if (file_exists('backend/config/database.php')) {
    echo "✅ database.php exists<br>";
} else {
    echo "❌ database.php NOT found<br>";
}

// Test 2: Try to connect
echo "<h3>2. Testing connection...</h3>";
try {
    require_once 'backend/config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Database connection successful!<br>";
    
    // Test 3: Check tables
    echo "<h3>3. Checking tables...</h3>";
    $tables = ['users', 'products', 'categories', 'orders', 'order_items', 'order_tracking', 'messages'];
    foreach ($tables as $table) {
        $query = "SHOW TABLES LIKE '$table'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' exists<br>";
        } else {
            echo "❌ Table '$table' NOT found<br>";
        }
    }
    
    // Test 4: Check admin user
    echo "<h3>4. Checking admin user...</h3>";
    $query = "SELECT * FROM users WHERE role = 'admin'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $admin = $stmt->fetch();
        echo "✅ Admin user exists: " . $admin['username'] . "<br>";
    } else {
        echo "❌ Admin user NOT found<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>If database connection failed, check MySQL is running</li>";
echo "<li>If tables not found, import database/schema.sql</li>";
echo "<li>If admin not found, import database/schema.sql (it includes default admin)</li>";
echo "</ol>";

echo "<h3>Test API:</h3>";
echo "<a href='backend/api/auth.php?action=check' target='_blank'>Test Auth API</a><br>";
echo "<a href='backend/api/products.php' target='_blank'>Test Products API</a><br>";
?>
