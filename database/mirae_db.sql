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
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(20),
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    notes TEXT DEFAULT NULL,
    last_login TIMESTAMP NULL,
    last_logout TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: customer_addresses
-- ============================================================
CREATE TABLE IF NOT EXISTS customer_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    recipient_name VARCHAR(255) NOT NULL,
    mobile_number VARCHAR(20) NOT NULL,
    house_unit_no VARCHAR(100),
    street VARCHAR(255),
    barangay VARCHAR(100),
    city_municipality VARCHAR(100),
    province VARCHAR(100),
    zip_code VARCHAR(20),
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE: login_logs
-- ============================================================
CREATE TABLE IF NOT EXISTS login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('admin', 'customer') NOT NULL,
    user_id INT NOT NULL,
    action ENUM('login', 'logout') NOT NULL,
    ip_address VARCHAR(45),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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
    order_status ENUM('Pending', 'Confirmed', 'Out for Delivery', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Pending',
    shipping_method VARCHAR(100) DEFAULT NULL,
    tracking_number VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

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

-- ============================================================
-- TABLE: messages
-- ============================================================
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    message TEXT NOT NULL,
    status ENUM('Unread', 'Read', 'Replied') NOT NULL DEFAULT 'Unread',
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
('currency_symbol',     'PHP'),
('contact_email',       'admin@mirae.com'),
('contact_phone',       '+63 900 000 0000'),
('low_stock_threshold', '10');
