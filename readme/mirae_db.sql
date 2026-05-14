-- ============================================================
-- MIRAE Admin Dashboard - Database Schema
-- Database: mirae_db
-- ============================================================

CREATE DATABASE IF NOT EXISTS mirae_db;
USE mirae_db;

-- ============================================================
-- TABLE: admins
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'Administrator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin: username = admin | password = admin123 (hashed)
INSERT INTO admins (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');


-- ============================================================
-- TABLE: products
-- ============================================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    sku VARCHAR(100) NOT NULL UNIQUE,
    variant VARCHAR(100),
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    status ENUM('In Stock', 'Out of Stock', 'Archived') NOT NULL DEFAULT 'In Stock',
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO products (name, description, sku, variant, price, stock, status, image) VALUES
(
    'e-WASH Multipurpose Cleaner',
    'e-WASH Multipurpose Cleaner is a powerful cleaning solution that effectively removes dirt, grease, and stains. Safe for everyday use.',
    'EWC-500P',
    '500ml (Pouch)',
    150.00,
    100,
    'In Stock',
    'uploads/products/ewc-500p.png'
),
(
    'e-WASH Multipurpose Cleaner',
    'e-WASH Multipurpose Cleaner is a powerful cleaning solution that effectively removes dirt, grease, and stains. Safe for everyday use.',
    'EWC-500B',
    '500ml (Bottle)',
    150.00,
    80,
    'In Stock',
    'uploads/products/ewc-500b.png'
),
(
    'e-WASH Multipurpose Cleaner',
    'e-WASH Multipurpose Cleaner is a powerful cleaning solution that effectively removes dirt, grease, and stains. Safe for everyday use.',
    'EWC-250B',
    '250ml (Bottle)',
    120.00,
    120,
    'In Stock',
    'uploads/products/ewc-250b.png'
);


-- ============================================================
-- TABLE: customers
-- ============================================================
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(20),
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO customers (name, email, phone, address, city, province, postal_code, status) VALUES
('Juan Dela Cruz',  'juandelacruz@email.com',  '0917 123 4567', '123 Sampaloc St.',   'Manila',      'Metro Manila', '1008', 'Active'),
('Maria Santos',    'mariasantos@email.com',   '0923 456 7890', '456 Quezon Ave.',     'Quezon City', 'Metro Manila', '1103', 'Active'),
('Peter Reyes',     'peterreyes@email.com',    '0908 765 4321', '789 JP Rizal St.',    'Makati City', 'Metro Manila', '1200', 'Active'),
('Ana Garcia',      'anagarcia@email.com',     '0916 234 5678', '321 Divisoria St.',   'Manila',      'Metro Manila', '1006', 'Active'),
('Mark Lopez',      'marklopez@email.com',     '0999 888 7777', '654 Taft Ave.',       'Pasay City',  'Metro Manila', '1300', 'Active');


-- ============================================================
-- TABLE: orders
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    payment_status ENUM('Paid', 'Unpaid') NOT NULL DEFAULT 'Unpaid',
    payment_method VARCHAR(100) DEFAULT NULL,
    order_status ENUM('Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    shipping_method VARCHAR(100) DEFAULT NULL,
    tracking_number VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

INSERT INTO orders (order_code, customer_id, total, payment_status, payment_method, order_status, shipping_method, tracking_number, created_at) VALUES
('ORD-000156', 1, 150.00, 'Paid', 'GCash',            'Pending',    'J&T Express',   'JT123456789PH',  '2024-05-26 10:30:00'),
('ORD-000155', 2, 300.00, 'Paid', 'Maya',             'Processing', 'LBC Express',   'LBC98765432lPH', '2024-05-26 09:15:00'),
('ORD-000154', 3, 150.00, 'Paid', 'Bank Transfer',    'Shipped',    'Flash Express', 'FP123789456PH',  '2024-05-25 16:50:00'),
('ORD-000153', 4, 450.00, 'Paid', 'GCash',            'Completed',  'J&T Express',   'JT564738291PH',  '2024-05-25 11:20:00'),
('ORD-000152', 5, 150.00, 'Paid', 'Cash on Delivery', 'Cancelled',  'LBC Express',   'LBC456123789PH', '2024-05-24 14:05:00');


-- ============================================================
-- TABLE: order_items
-- ============================================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
-- ORD-000156: 1x 250ml Bottle
(1, 3, 1, 120.00),
-- ORD-000155: 1x 500ml Pouch + 1x 250ml Bottle
(2, 1, 1, 150.00),
(2, 3, 1, 120.00),
-- ORD-000154: 1x 500ml Bottle
(3, 2, 1, 150.00),
-- ORD-000153: 1x 250ml Bottle + 1x 500ml Pouch + 1x 500ml Bottle
(4, 3, 1, 120.00),
(4, 1, 1, 150.00),
(4, 2, 1, 150.00),
-- ORD-000152: 1x 500ml Pouch
(5, 1, 1, 150.00);


-- ============================================================
-- TABLE: messages
-- ============================================================
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO messages (name, email, subject, message, is_read) VALUES
('Juan Dela Cruz',  'juandelacruz@email.com', 'Order Inquiry',       'Hi, I would like to ask about my order #ORD-000156. When will it be shipped?', 0),
('Maria Santos',    'mariasantos@email.com',  'Product Question',    'Is the 500ml pouch refillable?', 0),
('Peter Reyes',     'peterreyes@email.com',   'Bulk Order',          'Hi, I am interested in ordering in bulk for our office. Can we get a discount?', 1),
('Ana Garcia',      'anagarcia@email.com',    'Feedback',            'I love your product! The 250ml bottle is perfect for daily use.', 1),
('Mark Lopez',      'marklopez@email.com',    'Cancellation Reason', 'I had to cancel my order. I accidentally ordered the wrong variant.', 1);


-- ============================================================
-- TABLE: content_pages
-- ============================================================
CREATE TABLE IF NOT EXISTS content_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_key VARCHAR(100) NOT NULL UNIQUE,
    page_name VARCHAR(100) NOT NULL,
    content LONGTEXT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO content_pages (page_key, page_name, content) VALUES
('home',     'Home Page',    'Edit homepage content, texts, and sections here.'),
('product',  'Product Page', 'Edit product page content and settings here.'),
('data',     'Data Page',    'Edit data/information displayed on data page here.'),
('about',    'About Us',     'Edit about us page content here.'),
('faqs',     'FAQs',         'Add, edit or remove frequently asked questions here.'),
('contacts', 'Contacts',     'Edit contact information and details here.');


-- ============================================================
-- TABLE: settings
-- ============================================================
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name',           'MIRAE'),
('currency',            'PHP'),
('currency_symbol',     '₱'),
('contact_email',       'admin@mirae.com'),
('contact_phone',       '+63 900 000 0000'),
('low_stock_threshold', '10');
