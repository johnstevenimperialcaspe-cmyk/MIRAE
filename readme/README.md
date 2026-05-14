MIRAE Admin Dashboard
A full-featured e-commerce admin dashboard for managing products, orders, customers, and sales analytics — built with PHP, MySQL, and XAMPP.

Tech Stack
Layer	Technology
Server	Apache (via XAMPP)
Backend	PHP
Database	MySQL (via phpMyAdmin)
Frontend	HTML, CSS, JavaScript
Local URL	http://localhost/mirae-main
Requirements
XAMPP v8.0 or higher
PHP 8.0+
MySQL 5.7+ / MariaDB 10.4+
Web browser (Chrome, Firefox, Edge)
Installation & Setup
1. Install XAMPP
Download and install XAMPP from https://www.apachefriends.org.

2. Clone / Copy the Project
Place the project folder inside the XAMPP htdocs directory:

C:/xampp/htdocs/mirae-main/

3. Start XAMPP Services
Open the XAMPP Control Panel and start: - ✅ Apache - ✅ MySQL

4. Create the Database
Open your browser and go to: http://localhost/phpmyadmin
Click New and create a database named: mirae_db
Select the mirae_db database, click the Import tab
Import the provided SQL file: /mirae/database/mirae_db.sql
5. Configure Database Connection
Open the database config file and update if needed:

/mirae/config/db.php

```php <?php $host = 'localhost'; $db_name = 'mirae_db'; $username = 'root'; $password = ''; // default XAMPP has no password

$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); } ?> ```

6. Access the Dashboard
Open your browser and go to:

http://localhost/mirae-main/admin/dashboard.php

Folder Structure
mirae/ ├── admin/ │ ├── dashboard.php │ ├── products.php │ ├── orders.php │ ├── customers.php │ ├── messages.php │ ├── sales.php │ └── settings.php ├── config/ │ └── db.php ├── database/ │ └── mirae_db.sql ├── assets/ │ ├── css/ │ ├── js/ │ └── images/ ├── includes/ │ ├── header.php │ ├── sidebar.php │ └── footer.php └── index.php

Default Admin Credentials
Field	Value
Username	admin
Password	admin123
⚠️ Change the default credentials after first login.

Overview
The MIRAE Admin Dashboard is a web-based management panel designed for the e-WASH product line. It provides administrators with a centralized interface to monitor store performance, manage inventory, process orders, and handle customer data.

Features
Dashboard
Summary cards: Total Products, Total Orders, Total Customers, Total Messages, Total Sales
Recent Orders table with order ID, customer, date, total, and status
Product Management quick view with stock and status
Sales Overview chart (line graph, filterable by period)
Key metrics: Total Sales, Total Orders, Average Order Value, New Customers
Content Management panel with quick-edit links to all website pages
Products
View all active and archived products
Add new products with name, description, images, size/variant, price, stock quantity, and status
Edit or delete existing products
Filter by category, search by name
Pagination with configurable rows per page
Orders
Status summary: Total, Pending, Processing, Shipped, Completed, Cancelled
Search by order ID, customer, or product
Filter by payment status, order status, and shipping method
Per-order detail panel: customer info, order items, payment/shipping status, tracking number
Actions: Mark as Processing / Shipped / Completed, Cancel Order, View Invoice
Supported payment methods: GCash, Maya, Bank Transfer, Cash on Delivery (COD)
Supported couriers: J&T Express, LBC Express, Flash Express, Grab Express
Customers
Summary cards: Total Customers, With Orders, New This Month, Returning, Inactive
Customer list with contact info, address, total orders, total spent, last order, and status
Detailed customer profile: personal info, shipping addresses (Home/Work), order summary, recent orders, payment methods, preferred couriers, and notes
Filter by order status, location, and customer type
Content Management
Pages editable through the dashboard: - Home Page - Product Page - Data Page - About Us - FAQs - Contacts

Settings
Accessible from the sidebar under Settings
Navigation Structure
``` MAIN ├── Dashboard ├── Products ├── Orders ├── Customers ├── Contact Messages / Inquiries └── Sales

CONTENTS ├── Home ├── Product ├── Data ├── About Us ├── FAQs └── Contacts

SETTINGS └── Settings ```

Product Data (e-WASH Multipurpose Cleaner)
SKU	Variant	Price	Stock
EWC-500P	500ml (Pouch)	₱150.00	100
EWC-500B	500ml (Bottle)	₱150.00	80
EWC-250B	250ml (Bottle)	₱120.00	120
Order Statuses
Status	Description
Pending	Order placed, awaiting processing
Processing	Order is being prepared
Shipped	Order dispatched to courier
Completed	Order delivered successfully
Cancelled	Order was cancelled
Sample Data
Recent Orders
Order ID	Customer	Date	Total	Status
#ORD-000156	Juan Dela Cruz	May 26, 2024	₱300.00	Pending
#ORD-000155	Maria Santos	May 26, 2024	₱150.00	Processing
#ORD-000154	Peter Reyes	May 25, 2024	₱450.00	Shipped
#ORD-000153	Ana Garcia	May 25, 2024	₱150.00	Completed
#ORD-000152	Mark Lopez	May 24, 2024	₱300.00	Cancelled
Sales Overview (This Month)
Metric	Value
Total Sales	₱23,450.00
Total Orders	156
Average Order Value	₱150.32
New Customers	12
Tech Integration Notes
Currency: Philippine Peso (₱)
Date format: Month DD, YYYY
Admin role: Administrator
The dashboard supports sidebar navigation toggling (hamburger menu icon)
Product images support PNG and JPG formats, up to 5MB per image
Admin Access
Field	Value
Role	Administrator
Username	Admin
Logout is accessible from the bottom of the sidebar.

Pages / Routes
Page	File
Dashboard	admin/dashboard.php
Products	admin/products.php
Orders	admin/orders.php
Customers	admin/customers.php
Contact Messages	admin/messages.php
Sales	admin/sales.php
Settings	admin/settings.php
Content – Home	admin/content/home.php
Content – Product	admin/content/product.php
Content – Data	admin/content/data.php
Content – About Us	admin/content/about.php
Content – FAQs	admin/content/faqs.php
Content – Contacts	admin/content/contacts.php
Database Tables (MySQL)
products
Column	Type	Description
id	INT PK AI	Product ID
name	VARCHAR(255)	Product name
description	TEXT	Product description
sku	VARCHAR(100)	Stock keeping unit
variant	VARCHAR(100)	Size / type
price	DECIMAL(10,2)	Price in PHP
stock	INT	Stock quantity
status	ENUM	In Stock, Out of Stock, Archived
image	VARCHAR(255)	Image file path
created_at	TIMESTAMP	Date added
orders
Column	Type	Description
id	INT PK AI	Order ID
order_code	VARCHAR(50)	e.g. ORD-000156
customer_id	INT FK	References customers.id
total	DECIMAL(10,2)	Order total
payment_status	ENUM	Paid, Unpaid
order_status	ENUM	Pending, Processing, Shipped, Completed, Cancelled
shipping_method	VARCHAR(100)	Courier name
tracking_number	VARCHAR(100)	Courier tracking number
created_at	TIMESTAMP	Order date
order_items
Column	Type	Description
id	INT PK AI	Item ID
order_id	INT FK	References orders.id
product_id	INT FK	References products.id
quantity	INT	Quantity ordered
price	DECIMAL(10,2)	Price at time of order
customers
Column	Type	Description
id	INT PK AI	Customer ID
name	VARCHAR(255)	Full name
email	VARCHAR(255)	Email address
phone	VARCHAR(20)	Phone number
address	TEXT	Shipping address
status	ENUM	Active, Inactive
created_at	TIMESTAMP	Date joined
messages
Column	Type	Description
id	INT PK AI	Message ID
name	VARCHAR(255)	Sender name
email	VARCHAR(255)	Sender email
message	TEXT	Message content
created_at	TIMESTAMP	Date sent
admins
Column	Type	Description
id	INT PK AI	Admin ID
username	VARCHAR(100)	Login username
password	VARCHAR(255)	Hashed password
role	VARCHAR(50)	e.g. Administrator
Notes
All passwords should be hashed using password_hash() in PHP.
Sessions are used for admin authentication ($_SESSION).
Make sure mod_rewrite is enabled in Apache if using clean URLs.
For production, move the project to a live server and set a strong MySQL password.

# MIRAE - e-WASH & Super Sol Web Platform

This repository contains the frontend implementation for the MIRAE Design Trading Inc. web platform. The system is designed to showcase technical product data, legal certifications, and provide a lightweight e-commerce experience.

## Core Products

- **e-WASH**: High-performance alkaline electrolyzed water for cleaning and disinfection.
- **Super Sol**: Advanced technical solutions for wastewater treatment and environmental impact (Mangrove process).

## Project Goals

1.  **Technical Credibility**: Extensive use of data tables and certification galleries to display lab results (JFRL, Intertek, FDA).
2.  **Performance**: Minimal dependency footprint using vanilla JavaScript and CSS-heavy animations.
3.  **Conversion**: Integrated "Fly-to-Cart" e-commerce logic for direct product acquisition.

## Quick Start

The project is a static frontend site. To view:
1.  Clone the repository.
2.  Open `index.html` (or the respective product pages) in a modern browser.
3.  Ensure the `images/` directory is populated for the galleries to function.

## Documentation Links

- Architecture Overview
- JavaScript Module Reference
- UI and Styling Guide

---
*Proprietary system for MIRAE Design Trading Inc.*