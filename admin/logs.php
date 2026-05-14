<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';
$pageTitle = 'Login / Logout Logs';
$pageKey = 'logs';
$assetBase = '';

$logs = [];
$sql = "SELECT l.*, 
        CASE 
            WHEN l.user_type = 'admin' THEN a.username 
            WHEN l.user_type = 'customer' THEN c.name 
        END as user_name,
        CASE 
            WHEN l.user_type = 'admin' THEN 'Admin' 
            WHEN l.user_type = 'customer' THEN c.email 
        END as user_detail
        FROM login_logs l
        LEFT JOIN admins a ON l.user_type = 'admin' AND l.user_id = a.id
        LEFT JOIN customers c ON l.user_type = 'customer' AND l.user_id = c.id
        ORDER BY l.timestamp DESC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    $result->free();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Login / Logout Logs</h1>
        <p>Monitor user activity and security.</p>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>User</th>
                    <th>Detail</th>
                    <th>Action</th>
                    <th>Timestamp</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$logs) : ?>
                    <tr><td colspan="6">No activity logs found.</td></tr>
                <?php else : ?>
                    <?php foreach ($logs as $log) : ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($log['id']); ?></td>
                            <td><strong><?php echo htmlspecialchars($log['user_name'] ?? 'Unknown'); ?></strong></td>
                            <td><?php echo htmlspecialchars($log['user_detail'] ?? '-'); ?></td>
                            <td>
                                <span class="status-pill <?php echo $log['action'] === 'login' ? 'status-completed' : 'status-cancelled'; ?>">
                                    <?php echo ucfirst($log['action']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($log['timestamp']))); ?></td>
                            <td><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
