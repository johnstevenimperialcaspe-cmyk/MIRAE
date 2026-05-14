<?php
// One-time admin reset utility. Delete this file after use.

require_once __DIR__ . '/config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $message = 'Please enter both username and password.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO admins (username, password, role) VALUES (?, ?, 'Administrator')\n" .
            "ON DUPLICATE KEY UPDATE password = VALUES(password), role = 'Administrator'"
        );
        $stmt->bind_param('ss', $username, $hash);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $message = 'Admin credentials updated. You can now log in.';
        } else {
            $message = 'Failed to update admin credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Reset</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; }
        form { max-width: 360px; display: grid; gap: 0.75rem; }
        label { font-weight: 600; }
        input { padding: 0.5rem; font-size: 1rem; }
        button { padding: 0.6rem; font-size: 1rem; cursor: pointer; }
        .msg { margin-bottom: 1rem; color: #b91c1c; }
    </style>
</head>
<body>
    <h1>Admin Reset</h1>
    <p><strong>Reminder:</strong> delete this file after resetting credentials.</p>
    <?php if ($message): ?>
        <div class="msg"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="username">Username</label>
        <input id="username" name="username" type="text" required />

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required />

        <button type="submit">Update Admin</button>
    </form>
</body>
</html>
