# MIRAE Setup Guide

This project includes a static frontend plus an optional PHP/MySQL admin dashboard.

## Prerequisites

- Windows 10 or later
- XAMPP 8.0+ (Apache + MySQL)
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.4+
- Modern browser (Chrome, Edge, Firefox)

## Option A: Run Frontend Only (Static)

1) Open index.html in a browser, or
2) Use a simple static server (optional).

This mode does not support admin login, product uploads, or content editing.

## Option B: Run Full System (PHP + MySQL)

1) Install XAMPP
   - Download from https://www.apachefriends.org

2) Move the project into XAMPP htdocs
   - Example: C:\xampp\htdocs\mirae

3) Start Apache and MySQL in XAMPP Control Panel

4) Create the database
   - Open http://localhost/phpmyadmin
   - Create a database named mirae_db
   - Import the SQL file: database/mirae_db.sql

5) Verify database connection
   - Check config/db.php and update host/user/password if needed

6) Open the site
   - Public site: http://localhost/mirae/index.html
   - Admin login: http://localhost/mirae/login.php
   - Default admin: admin / admin123

## Content Management

- Update Intro, Subtitle, and HTML body in the Admin Content pages.
- Changes appear on the public pages in the managed content slots.

## Product Image Uploads

- Images are saved to uploads/products.
- Ensure the uploads folder is writable by Apache.
- Allowed formats: JPG, JPEG, PNG.

## Troubleshooting

- Blank page or PHP errors: check Apache and PHP versions.
- DB connection errors: verify config/db.php credentials.
- Missing content: confirm data exists in content_pages table.
