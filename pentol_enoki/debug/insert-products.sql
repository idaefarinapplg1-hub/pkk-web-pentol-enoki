-- Insert Products if not exists
-- Run this in phpMyAdmin

-- First, enable all existing products
UPDATE products SET is_available = 1, is_archived = 0 WHERE 1=1;

-- Insert sample products only if table is empty
INSERT INTO products (category_id, product_name, description, price, stock, is_available, is_archived)
SELECT * FROM (
    SELECT 1 as category_id, 'Nasi Goreng Special' as product_name, 'Nasi goreng dengan telur, ayam, dan sayuran segar' as description, 25000 as price, 50 as stock, 1 as is_available, 0 as is_archived
    UNION ALL SELECT 1, 'Mie Goreng', 'Mie goreng pedas dengan topping lengkap', 20000, 45, 1, 0
    UNION ALL SELECT 1, 'Ayam Geprek', 'Ayam goreng crispy dengan sambal geprek pedas', 30000, 40, 1, 0
    UNION ALL SELECT 1, 'Soto Ayam', 'Soto ayam kuah kuning dengan nasi', 22000, 35, 1, 0
    UNION ALL SELECT 2, 'Es Teh Manis', 'Es teh manis segar', 5000, 100, 1, 0
    UNION ALL SELECT 2, 'Jus Jeruk', 'Jus jeruk segar tanpa gula', 12000, 50, 1, 0
    UNION ALL SELECT 3, 'Pisang Goreng', 'Pisang goreng crispy', 10000, 30, 1, 0
    UNION ALL SELECT 3, 'Tahu Isi', 'Tahu isi sayuran dengan saus kacang', 8000, 40, 1, 0
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM products LIMIT 1);

-- Verify
SELECT product_id, product_name, price, stock, is_available, is_archived FROM products;
