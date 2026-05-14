<?php
// ============================================================
// MIRAE Admin Dashboard - Login Page
// File: login.php
// ============================================================

session_start();
require_once 'config/db.php';

$error = '';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: admin/dashboard.php');
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM admins WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'];
            header('Location: admin/dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MIRAE - Admin Login</title>
    <link rel="icon" type="image/png" href="images/MD.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --green: #2D6A4F;
            --green-light: #52B788;
            --green-pale: #D8F3DC;
            --black: #0D0D0D;
            --white: #FAFAFA;
            --gray: #6B7280;
            --gray-light: #F3F4F6;
            --border: #E5E7EB;
            --error: #DC2626;
        }

        html, body {
            height: 100%;
            font-family: 'DM Sans', sans-serif;
            background-color: var(--white);
            color: var(--black);
        }

        /* Layout */
        .page {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        /* Left Panel */
        .left-panel {
            background-color: var(--black);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(82, 183, 136, 0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(82, 183, 136, 0.06) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        .left-panel::after {
            content: '';
            position: absolute;
            bottom: -120px;
            right: -120px;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            border: 1px solid rgba(82, 183, 136, 0.15);
            pointer-events: none;
        }

        .left-inner {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .brand {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .brand-wordmark {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.25rem;
            font-weight: 600;
            letter-spacing: 0.18em;
            color: var(--white);
        }

        .brand-sub {
            font-size: 0.7rem;
            font-weight: 300;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--green-light);
        }

        .left-headline {
            margin-top: auto;
            padding-bottom: 3rem;
        }

        .left-headline h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(2.4rem, 3.5vw, 3.8rem);
            font-weight: 300;
            line-height: 1.15;
            color: var(--white);
            letter-spacing: -0.01em;
        }

        .left-headline h1 em {
            font-style: italic;
            color: var(--green-light);
        }

        .left-headline p {
            margin-top: 1.25rem;
            font-size: 0.85rem;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.45);
            line-height: 1.7;
            max-width: 320px;
        }

        /* Pill stats */
        .stats-row {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 2.5rem;
        }

        .stat-pill {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 999px;
            padding: 0.45rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-pill .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--green-light);
        }

        .stat-pill span {
            font-size: 0.72rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.55);
            letter-spacing: 0.04em;
        }

        /* Right Panel */
        .right-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 2rem;
            background: var(--white);
        }

        .login-box {
            width: 100%;
            max-width: 400px;
            animation: fadeUp 0.55s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .login-box-header {
            margin-bottom: 2.5rem;
        }

        .login-box-header h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 600;
            color: var(--black);
            letter-spacing: -0.01em;
        }

        .login-box-header p {
            margin-top: 0.4rem;
            font-size: 0.82rem;
            color: var(--gray);
            font-weight: 300;
        }

        /* Form */
        .form-group {
            margin-bottom: 1.25rem;
        }

        label {
            display: block;
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--black);
            margin-bottom: 0.5rem;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap svg {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            stroke: var(--gray);
            pointer-events: none;
            transition: stroke 0.2s;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem 0.9rem 0.75rem 2.6rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            color: var(--black);
            background: var(--white);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.1);
        }

        input:focus + svg,
        .input-wrap:focus-within svg {
            stroke: var(--green);
        }

        /* Toggle password visibility */
        .toggle-pw {
            position: absolute;
            right: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            color: var(--gray);
            transition: color 0.2s;
        }

        .toggle-pw:hover { color: var(--green); }
        .toggle-pw svg { width: 16px; height: 16px; }

        /* Error */
        .error-msg {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-radius: 8px;
            padding: 0.7rem 0.9rem;
            margin-bottom: 1.25rem;
            font-size: 0.8rem;
            color: var(--error);
            animation: shake 0.35s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%       { transform: translateX(-5px); }
            60%       { transform: translateX(5px); }
        }

        .error-msg svg { width: 15px; height: 15px; flex-shrink: 0; }

        /* Submit Button */
        .btn-login {
            width: 100%;
            padding: 0.85rem;
            background: var(--green);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 500;
            letter-spacing: 0.04em;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-login:hover {
            background: #245c42;
            box-shadow: 0 4px 14px rgba(45, 106, 79, 0.3);
            transform: translateY(-1px);
        }

        .btn-login:active { transform: translateY(0); }

        .btn-login svg { width: 16px; height: 16px; }

        /* Footer note */
        .login-footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.72rem;
            color: var(--gray);
            font-weight: 300;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page { grid-template-columns: 1fr; }
            .left-panel { display: none; }
            .right-panel { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>

<div class="page">

    <!-- Left decorative panel -->
    <div class="left-panel">
        <div class="left-inner">
            <div class="brand">
                <div class="brand-wordmark">MIRAE</div>
                <div class="brand-sub">Admin Portal</div>
            </div>

            <div class="left-headline">
                <h1>Manage the <em>e-WASH</em> ecosystem.</h1>
                <p>
                    Secure access to orders, products, customers, and sales insights - all from one dashboard.
                </p>
                <div class="stats-row">
                    <div class="stat-pill"><span class="dot"></span><span>Inventory</span></div>
                    <div class="stat-pill"><span class="dot"></span><span>Orders</span></div>
                    <div class="stat-pill"><span class="dot"></span><span>Insights</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right login panel -->
    <div class="right-panel">
        <div class="login-box">
            <div class="login-box-header">
                <h2>Welcome back</h2>
                <p>Sign in to continue to MIRAE Admin.</p>
            </div>

            <?php if (!empty($error)) : ?>
                <div class="error-msg" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <circle cx="12" cy="16" r="1"></circle>
                    </svg>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <input type="text" id="username" name="username" placeholder="Enter username" required />
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password" placeholder="Enter password" required />
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-block py-3 mt-4">
                    Sign In
                </button>
            </form>

            <div class="login-footer">
                Default credentials: admin / admin123
            </div>
        </div>
    </div>
</div>

</body>
</html>
