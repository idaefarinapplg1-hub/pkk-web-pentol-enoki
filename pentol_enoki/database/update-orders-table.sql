-- Update Orders Table for Guest Checkout Support
-- Run this if you already have the database created

USE siap_santap;

-- Add new columns for guest checkout
ALTER TABLE orders 
MODIFY COLUMN user_id INT NULL,
ADD COLUMN IF NOT EXISTS customer_name VARCHAR(100) NOT NULL AFTER order_number,
ADD COLUMN IF NOT EXISTS customer_email VARCHAR(100) NOT NULL AFTER customer_name;

-- Update payment_method enum to include new options
ALTER TABLE orders 
MODIFY COLUMN payment_method ENUM('e-wallet', 'cod', 'transfer', 'ewallet') NOT NULL;

-- Update foreign key constraint to SET NULL instead of CASCADE
ALTER TABLE orders DROP FOREIGN KEY orders_ibfk_1;
ALTER TABLE orders ADD FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL;

-- Update existing orders with customer info from users table
UPDATE orders o
INNER JOIN users u ON o.user_id = u.user_id
SET o.customer_name = u.full_name,
    o.customer_email = u.email
WHERE o.customer_name IS NULL OR o.customer_name = '';
