<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$pageKey = 'profile';
$error = '';
$success = '';

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name) || empty($email) || empty($phone)) {
            $error = 'Please fill in all fields.';
        } else {
            $stmt = $conn->prepare("UPDATE customers SET name = ?, email = ?, phone = ? WHERE id = ?");
            $stmt->bind_param('sssi', $name, $email, $phone, $userId);
            if ($stmt->execute()) {
                $_SESSION['user_name'] = $name;
                $success = 'Profile updated successfully!';
            } else {
                $error = 'Failed to update profile: ' . $conn->error;
            }
            $stmt->close();
        }
    } elseif (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = $conn->prepare("SELECT password FROM customers WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!password_verify($current, $user['password'])) {
            $error = 'Incorrect current password.';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new) < 6) {
            $error = 'New password must be at least 6 characters.';
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE customers SET password = ? WHERE id = ?");
            $stmt->bind_param('si', $hashed, $userId);
            if ($stmt->execute()) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password.';
            }
            $stmt->close();
        }
    }
}

// Get current data
$stmt = $conn->prepare("SELECT name, email, phone FROM customers WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - MIRAE</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/loader.css">
    <style>
        :root { --primary: #1b7d3f; --primary-light: #ecfdf5; --text-dark: #111827; --text-gray: #6b7280; --border: #e5e7eb; }
        body { font-family: 'DM Sans', sans-serif; background: #f9fafb; margin: 0; color: var(--text-dark); }
        .user-layout { display: flex; min-height: 100vh; }
        
        .user-sidebar { width: 260px; background: white; border-right: 1px solid var(--border); padding: 30px 20px; }
        .nav-link { display: block; padding: 12px 15px; color: var(--text-dark); text-decoration: none; border-radius: 8px; margin-bottom: 5px; font-weight: 500; transition: all 0.2s; }
        .nav-link:hover { background: var(--primary-light); color: var(--primary); }
        .nav-link.active { background: var(--primary); color: white; }
        
        .user-main { flex: 1; padding: 40px; }
        .page-header { margin-bottom: 30px; }
        .page-header h1 { font-size: 28px; margin: 0; }
        
        .profile-container { max-width: 600px; }
        .card { background: white; border-radius: 12px; border: 1px solid var(--border); padding: 30px; margin-bottom: 30px; }
        .card h3 { margin: 0 0 20px 0; font-size: 18px; border-bottom: 1px solid var(--border); padding-bottom: 15px; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px; }
        input { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
        input:focus { outline: none; border-color: var(--primary); }
        
        .btn-primary { padding: 12px 24px; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .btn-primary:hover { background: #145c2e; }
        
        .error { color: #dc2626; background: #fef2f2; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .success { color: #059669; background: #ecfdf5; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
    </style>
</head>
<body class="profile-page">
    <div class="preloader">
        <div class="loader"></div>
    </div>
    <div class="user-layout">
        <aside class="user-sidebar">
            <div style="margin-bottom: 40px; padding-left: 10px;">
                <h1 style="font-size: 24px; color: var(--primary); margin: 0;">MIRAE</h1>
                <span style="font-size: 12px; color: var(--text-gray);">My Account</span>
            </div>
            <nav>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="profile.php" class="nav-link active">My Profile</a>
                <a href="addresses.php" class="nav-link">My Addresses</a>
                <a href="orders.php" class="nav-link">My Orders</a>
                <a href="messages.php" class="nav-link">My Messages</a>
                <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--border);">
                <a href="logout.php" class="nav-link" style="color: #dc2626;">Log Out</a>
            </nav>
        </aside>
        
        <main class="user-main">
            <div class="page-header">
                <h1>Personal Information</h1>
            </div>
            
            <div class="profile-container">
                <?php if ($error) echo "<div class='error'>$error</div>"; ?>
                <?php if ($success) echo "<div class='success'>$success</div>"; ?>

                <div class="card">
                    <h3>Update Profile</h3>
                    <form method="post" action="">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($userData['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Mobile Number</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($userData['phone']); ?>" required>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>

                <div class="card">
                    <h3>Change Password</h3>
                    <form method="post" action="">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary">Update Password</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script src="../js/main.js"></script>
</body>
</html>
