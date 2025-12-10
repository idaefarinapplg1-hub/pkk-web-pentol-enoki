-- Sample Data for Siap Santap
-- Insert this after running schema.sql

USE siap_santap;

-- Sample Products
INSERT INTO products (category_id, product_name, description, price, image_url, stock, is_available) VALUES
(1, 'Nasi Goreng Special', 'Nasi goreng dengan telur, ayam, dan sayuran segar', 25000, 'uploads/nasi-goreng.jpg', 50, TRUE),
(1, 'Mie Goreng Seafood', 'Mie goreng dengan udang, cumi, dan sayuran', 30000, 'uploads/mie-goreng.jpg', 40, TRUE),
(1, 'Nasi Ayam Geprek', 'Nasi dengan ayam goreng geprek pedas level 1-5', 28000, 'uploads/ayam-geprek.jpg', 45, TRUE),
(1, 'Soto Ayam', 'Soto ayam kuah kuning dengan nasi dan kerupuk', 22000, 'uploads/soto-ayam.jpg', 35, TRUE),
(1, 'Nasi Rendang', 'Nasi dengan rendang daging sapi empuk', 35000, 'uploads/rendang.jpg', 30, TRUE),

(2, 'Pisang Goreng', 'Pisang goreng crispy dengan topping coklat/keju', 15000, 'uploads/pisang-goreng.jpg', 60, TRUE),
(2, 'Tahu Crispy', 'Tahu goreng crispy dengan saus sambal', 12000, 'uploads/tahu-crispy.jpg', 50, TRUE),
(2, 'Kentang Goreng', 'French fries dengan saus sambal/mayo', 18000, 'uploads/kentang-goreng.jpg', 55, TRUE),
(2, 'Risoles Mayo', 'Risoles isi sayuran dengan mayo', 20000, 'uploads/risoles.jpg', 40, TRUE),

(3, 'Es Teh Manis', 'Es teh manis segar', 5000, 'uploads/es-teh.jpg', 100, TRUE),
(3, 'Es Jeruk', 'Es jeruk peras segar', 8000, 'uploads/es-jeruk.jpg', 80, TRUE),
(3, 'Jus Alpukat', 'Jus alpukat dengan susu coklat', 15000, 'uploads/jus-alpukat.jpg', 50, TRUE),
(3, 'Kopi Susu', 'Kopi susu dingin/panas', 12000, 'uploads/kopi-susu.jpg', 70, TRUE),
(3, 'Thai Tea', 'Thai tea original dengan susu', 10000, 'uploads/thai-tea.jpg', 60, TRUE),

(4, 'Es Krim Goreng', 'Es krim goreng dengan topping', 18000, 'uploads/es-krim-goreng.jpg', 35, TRUE),
(4, 'Puding Coklat', 'Puding coklat lembut', 12000, 'uploads/puding.jpg', 40, TRUE),
(4, 'Pancake', 'Pancake dengan sirup maple dan butter', 22000, 'uploads/pancake.jpg', 30, TRUE);

-- Sample Customer
INSERT INTO users (username, email, password, full_name, phone, address, role) VALUES
('customer1', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Customer', '081234567890', 'Jl. Contoh No. 123, Jakarta', 'customer');

-- Sample Order
INSERT INTO orders (user_id, order_number, total_amount, payment_method, payment_status, order_status, shipping_address, phone, notes) VALUES
(2, 'ORD-20251209-SAMPLE', 75000, 'e-wallet', 'paid', 'delivered', 'Jl. Contoh No. 123, Jakarta', '081234567890', 'Tolong antar sebelum jam 12');

-- Sample Order Items
INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES
(1, 1, 2, 25000, 50000),
(1, 10, 1, 5000, 5000),
(1, 11, 2, 10000, 20000);

-- Sample Order Tracking
INSERT INTO order_tracking (order_id, status, description) VALUES
(1, 'pending', 'Order placed successfully'),
(1, 'processing', 'Order is being prepared'),
(1, 'shipped', 'Order is on the way'),
(1, 'delivered', 'Order has been delivered');

-- Sample Message
INSERT INTO messages (user_id, subject, message, status) VALUES
(2, 'Pertanyaan tentang menu', 'Apakah ada menu vegetarian?', 'unread');
