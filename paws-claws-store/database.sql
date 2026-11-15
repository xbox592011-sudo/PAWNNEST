CREATE DATABASE IF NOT EXISTS paws_claws_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE paws_claws_db;

CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY DEFAULT 1,
    store_name VARCHAR(100) DEFAULT 'Paws & Claws Emporium',
    currency VARCHAR(10) DEFAULT 'USD',
    currency_symbol VARCHAR(5) DEFAULT '$',
    primary_color VARCHAR(20) DEFAULT '#f97316',
    stripe_public_key VARCHAR(255) DEFAULT 'pk_test_your_stripe_public_key',
    stripe_secret_key VARCHAR(255) DEFAULT 'sk_test_your_stripe_secret_key',
    paypal_client_id VARCHAR(255) DEFAULT 'sb',
    admin_user VARCHAR(50) DEFAULT 'admin',
    admin_pass VARCHAR(255) DEFAULT 'admin123'
);

INSERT IGNORE INTO settings (id) VALUES (1);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(100) NOT NULL,
    pet_type VARCHAR(50) NOT NULL,
    stock INT NOT NULL DEFAULT 10,
    image_url VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO products (name, description, price, category, pet_type, stock, image_url) VALUES
('Premium Grain-Free Salmon Dog Food', 'Rich in Omega-3 for shiny coat and healthy digestion. 5kg bag.', 45.99, 'Dog Food', 'Dog', 25, 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?auto=format&fit=crop&w=600&q=80'),
('Interactive Feather Wand Cat Toy', 'Retractable wand with natural feathers and bell to stimulate hunting instincts.', 14.50, 'Cat Toys', 'Cat', 40, 'https://images.unsplash.com/photo-1545249390-6bdfa286032f?auto=format&fit=crop&w=600&q=80'),
('Gentle Oatmeal & Aloe Pet Shampoo', 'Soothing tear-free formula for sensitive skin. 500ml bottle.', 18.00, 'Grooming', 'Dog', 15, 'https://images.unsplash.com/photo-1583947215259-38e31be8751f?auto=format&fit=crop&w=600&q=80'),
('Orthopedic Memory Foam Pet Bed', 'Plush velvet washable cover with supportive foam base for joint relief.', 64.99, 'Accessories', 'Dog', 8, 'https://images.unsplash.com/photo-1591946614720-90a587da4a36?auto=format&fit=crop&w=600&q=80'),
('Multi-Vitamin & Joint Health Chews', 'Daily soft chews with glucosamine and chondroitin for all ages.', 24.99, 'Healthcare', 'Cat', 3, 'https://images.unsplash.com/photo-1623387641168-d9803ddd3f35?auto=format&fit=crop&w=600&q=80'),
('Natural Wooden Bird Swing & Ladder', 'Safe untreated wood playground for parakeets, cockatiels, and finches.', 16.75, 'Accessories', 'Bird', 12, 'https://images.unsplash.com/photo-1552728089-57bdde30beb3?auto=format&fit=crop&w=600&q=80');

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(150) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    shipping_address TEXT NOT NULL,
    order_notes TEXT,
    items_json LONGTEXT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_status VARCHAR(50) DEFAULT 'Paid',
    order_status VARCHAR(50) DEFAULT 'Processing',
    transaction_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
