<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$userEmail = $_SESSION['user_email'];
$pageKey = 'messages';

// Get messages sent by this user (matched by email)
$messages = [];
$stmt = $conn->prepare("SELECT id, subject, message, status, created_at FROM messages WHERE email = ? ORDER BY created_at DESC");
$stmt->bind_param('s', $userEmail);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $messages[] = $row;
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Messages - MIRAE</title>
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
        .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .page-header h1 { font-size: 28px; margin: 0; }
        
        .message-list { display: flex; flex-direction: column; gap: 20px; }
        .message-card { background: white; border-radius: 12px; border: 1px solid var(--border); padding: 25px; }
        .message-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        .message-subject { font-size: 18px; font-weight: 700; margin: 0; }
        .message-date { font-size: 13px; color: var(--text-gray); }
        .message-body { font-size: 15px; line-height: 1.6; color: #374151; background: #f9fafb; padding: 15px; border-radius: 8px; }
        
        .reply-box { margin-top: 20px; padding-top: 20px; border-top: 1px solid #f3f4f6; }
        .reply-header { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; color: var(--primary); font-weight: 600; font-size: 14px; }
        .reply-content { font-size: 14px; background: var(--primary-light); padding: 15px; border-radius: 8px; border-left: 4px solid var(--primary); }
        
        .status-badge { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; }
        .status-unread { background: #fef3c7; color: #92400e; }
        .status-replied { background: #d1fae5; color: #065f46; }
        
        .btn-new { padding: 10px 20px; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; }
    </style>
</head>
<body class="messages-page">
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
                <a href="profile.php" class="nav-link">My Profile</a>
                <a href="addresses.php" class="nav-link">My Addresses</a>
                <a href="orders.php" class="nav-link">My Orders</a>
                <a href="messages.php" class="nav-link active">My Messages</a>
                <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--border);">
                <a href="logout.php" class="nav-link" style="color: #dc2626;">Log Out</a>
            </nav>
        </aside>
        
        <main class="user-main">
            <div class="page-header">
                <h1>My Messages</h1>
                <a href="../contact.html" class="btn btn-primary">Send New Message</a>
            </div>

            <div class="message-list">
                <?php if (empty($messages)): ?>
                    <div style="text-align: center; padding: 60px; color: var(--text-gray);">
                        <p>You haven't sent any messages yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-card">
                            <div class="message-header">
                                <div>
                                    <h3 class="message-subject"><?php echo htmlspecialchars($msg['subject'] ?: '(No Subject)'); ?></h3>
                                    <span class="message-date"><?php echo date('M d, Y h:i A', strtotime($msg['created_at'])); ?></span>
                                </div>
                                <span class="status-badge <?php echo $msg['status'] === 'Replied' ? 'status-replied' : 'status-unread'; ?>">
                                    <?php echo $msg['status']; ?>
                                </span>
                            </div>
                            <div class="message-body">
                                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                            </div>
                            
                            <?php if ($msg['status'] === 'Replied'): ?>
                                <div class="reply-box">
                                    <div class="reply-header">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"></polyline><path d="M20 18v-2a4 4 0 0 0-4-4H4"></path></svg>
                                        Admin Reply
                                    </div>
                                    <div class="reply-content">
                                        Thank you for your inquiry. We have processed your request. Please check your email for further details.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="../js/main.js"></script>
</body>
</html>
