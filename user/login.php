<?php
session_start();
require_once '../config/db.php';

$error = '';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, status FROM customers WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'Inactive') {
                $error = 'Your account has been deactivated. Please contact support.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                // Update last login
                $conn->query("UPDATE customers SET last_login = CURRENT_TIMESTAMP WHERE id = " . $user['id']);

                // Log activity
                $ip = $_SERVER['REMOTE_ADDR'];
                $stmt = $conn->prepare("INSERT INTO login_logs (user_type, user_id, action, ip_address) VALUES ('customer', ?, 'login', ?)");
                $stmt->bind_param('is', $user['id'], $ip);
                $stmt->execute();
                $stmt->close();

                header('Location: dashboard.php');
                exit();
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MIRAE</title>
    <link rel="icon" type="image/png" href="../images/MD.png">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/loader.css">
    <style>
        body { font-family: 'DM Sans', sans-serif; background: #f9fafb; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .login-container { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); width: 100%; max-width: 400px; }
        .login-container h1 { margin-bottom: 8px; font-size: 24px; color: #111827; }
        .login-container p.subtitle { color: #6b7280; margin-bottom: 30px; font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 8px; }
        input { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
        input:focus { outline: none; border-color: #1b7d3f; }
        .btn-login { width: 100%; padding: 12px; background: #1b7d3f; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 10px; transition: background 0.2s; }
        .btn-login:hover { background: #145c2e; }
        .error { color: #dc2626; background: #fef2f2; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .footer-link { text-align: center; margin-top: 24px; font-size: 14px; color: #6b7280; }
        .footer-link a { color: #1b7d3f; text-decoration: none; font-weight: 500; }

        @media (max-width: 576px) {
            .login-container { padding: 30px 20px; border-radius: 0; box-shadow: none; height: 100vh; display: flex; flex-direction: column; justify-content: center; }
            body { background: white; }
        }
    </style>
</head>
<body class="login-page">
    <div class="preloader">
        <div class="loader"></div>
    </div>
    <div class="login-container">
        <h1>Welcome Back</h1>
        <p class="subtitle">Sign in to your MIRAE account.</p>
        
        <?php if ($error) echo "<div class='error'>$error</div>"; ?>

        <form method="post" action="">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="Enter your email">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Enter your password">
            </div>
            <button type="submit" class="btn btn-success btn-block">Sign In</button>
        </form>

        <div class="footer-link">
            Don't have an account? <a href="signup.php">Create one here</a>
        </div>
    </div>
    <script src="../js/main.js"></script>
</body>
</html>
