<?php
session_start();
require_once '../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    
    // Address fields
    $house = trim($_POST['house'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $zip = trim($_POST['zip'] ?? '');

    if (empty($name) || empty($email) || empty($password) || empty($phone) || empty($city)) {
        $error = 'All required fields must be filled.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Email is already registered.';
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Start transaction
            $conn->begin_transaction();
            try {
                // Insert into customers
                $fullAddress = trim("$house $street, $barangay");
                $stmt = $conn->prepare("INSERT INTO customers (name, email, password, phone, address, city, province, postal_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('ssssssss', $name, $email, $hashedPassword, $phone, $fullAddress, $city, $province, $zip);
                $stmt->execute();
                $customerId = $conn->insert_id;

                // Insert into customer_addresses
                $stmt = $conn->prepare("INSERT INTO customer_addresses (customer_id, recipient_name, mobile_number, house_unit_no, street, barangay, city_municipality, province, zip_code, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
                $stmt->bind_param('issssssss', $customerId, $name, $phone, $house, $street, $barangay, $city, $province, $zip);
                $stmt->execute();

                $conn->commit();
                $success = 'Account created successfully! You can now login.';
            } catch (Exception $e) {
                $conn->rollback();
                $error = 'Registration failed: ' . $e->getMessage();
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - MIRAE</title>
    <link rel="icon" type="image/png" href="../images/MD.png">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/loader.css">
    <style>
        body { font-family: 'DM Sans', sans-serif; background: #f9fafb; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .signup-container { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); width: 100%; max-width: 600px; }
        .signup-container h1 { margin-bottom: 8px; font-size: 24px; color: #111827; }
        .signup-container p.subtitle { color: #6b7280; margin-bottom: 30px; font-size: 14px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group.full { grid-column: span 2; }
        label { display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 5px; }
        input { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
        input:focus { outline: none; border-color: #1b7d3f; ring: 2px rgba(27, 125, 63, 0.1); }
        .btn-signup { width: 100%; padding: 12px; background: #1b7d3f; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; margin-top: 20px; transition: background 0.2s; }
        .btn-signup:hover { background: #145c2e; }
        .error { color: #dc2626; background: #fef2f2; padding: 10px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
        .success { color: #059669; background: #ecfdf5; padding: 10px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
        .footer-link { text-align: center; margin-top: 20px; font-size: 14px; color: #6b7280; }
        .footer-link a { color: #1b7d3f; text-decoration: none; font-weight: 500; }
    </style>
</head>
<body class="signup-page">
    <div class="preloader">
        <div class="loader"></div>
    </div>
    <div class="signup-container">
        <h1>Create Account</h1>
        <p class="subtitle">Join MIRAE and enjoy a seamless shopping experience.</p>
        
        <?php if ($error) echo "<div class='error'>$error</div>"; ?>
        <?php if ($success) echo "<div class='success'>$success</div>"; ?>

        <form method="post" action="">
            <div class="form-grid">
                <div class="form-group full">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="Juan Dela Cruz">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required placeholder="juan@example.com">
                </div>
                <div class="form-group">
                    <label>Mobile Number</label>
                    <input type="text" name="phone" class="form-control" required placeholder="09171234567">
                </div>
                <div class="form-group full">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="••••••••">
                </div>
                
                <div class="form-group full">
                    <h3 style="font-size: 16px; margin: 10px 0;">Delivery Information</h3>
                </div>
                
                <div class="form-group">
                    <label>House/Unit No.</label>
                    <input type="text" name="house" class="form-control" placeholder="Bldg 1, Unit 101">
                </div>
                <div class="form-group">
                    <label>Street</label>
                    <input type="text" name="street" class="form-control" placeholder="Main Street">
                </div>
                <div class="form-group">
                    <label>Barangay</label>
                    <input type="text" name="barangay" class="form-control" placeholder="Brgy. 123">
                </div>
                <div class="form-group">
                    <label>City / Municipality</label>
                    <input type="text" name="city" class="form-control" required placeholder="Quezon City">
                </div>
                <div class="form-group">
                    <label>Province</label>
                    <input type="text" name="province" class="form-control" placeholder="Metro Manila">
                </div>
                <div class="form-group">
                    <label>ZIP Code</label>
                    <input type="text" name="zip" class="form-control" placeholder="1100">
                </div>
            </div>

            <button type="submit" class="btn btn-success btn-block">Create Account</button>
        </form>

        <div class="footer-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
    <script src="../js/main.js"></script>
</body>
</html>
