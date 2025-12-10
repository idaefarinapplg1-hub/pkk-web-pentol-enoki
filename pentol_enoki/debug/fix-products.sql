-- Fix Products Database
-- Run this SQL to ensure products are available

-- 1. Enable all existing products
UPDATE products 
SET is_available = 1, is_archived = 0
WHERE 1=1;

-- 2. Check if products exist
SELECT COUNT(*) as total_products FROM products;

-- 3. If no products, insert sample data
INSERT INTO products (category_id, product_name, description, price, stock, is_available, is_archived, image_url) 
SELECT * FROM (
    SELECT 1, 'Nasi Goreng Special', 'Nasi goreng dengan telur, ayam, dan sayuran segar', 25000, 50, 1, 0, '../assets/placeholder.jpg'
    UNION ALL
    SELECT 1, 'Mie Goreng', 'Mie goreng pedas dengan topping lengkap', 20000, 45, 1, 0, '../assets/placeholder.jpg'
    UNION ALL
    SELECT 1, 'Ayam Geprek', 'Ayam goreng crispy dengan sambal geprek pedas', 30000, 40, 1, 0, '../assets/placeholder.jpg'
    UNION ALL
    SELECT 1, 'Soto Ayam', 'Soto ayam kuah kuning dengan nasi', 22000, 35, 1, 0, '../assets/placeholder.jpg'
    UNION ALL
    SELECT 2, 'Es Teh Manis', 'Es teh manis segar', 5000, 100, 1, 0, '../assets/placeholder.jpg'
    UNION ALL
    SELECT 2, 'Jus Jeruk', 'Jus jeruk segar tanpa gula', 12000, 50, 1, 0, '../assets/placeholder.jpg'
    UNION ALL
    SELECT 3, 'Pisang Goreng', 'Pisang goreng crispy', 10000, 30, 1, 0, '../assets/placeholder.jpg'
    UNION ALL
    SELECT 3, 'Tahu Isi', 'Tahu isi sayuran dengan saus kacang', 8000, 40, 1, 0, '../assets/placeholder.jpg'
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM products LIMIT 1);

-- 4. Verify products
SELECT 
    product_id,
    product_name,
    price,
    stock,
    is_available,
    is_archived,
    CASE 
        WHEN is_available = 1 AND is_archived = 0 THEN 'VISIBLE ✓'
        WHEN is_available = 0 THEN 'NOT AVAILABLE ✗'
        WHEN is_archived = 1 THEN 'ARCHIVED 📁'
    END as status
FROM products
ORDER BY product_id;

-- 5. Count visible products
SELECT COUNT(*) as visible_products 
FROM products 
WHERE is_available = 1 AND is_archived = 0;
