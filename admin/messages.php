<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';
$pageTitle = 'Messages Inbox';
$pageKey = 'messages';
$assetBase = '';

$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

$whereSql = $statusFilter ? "WHERE status = '$statusFilter'" : "";

$messages = [];
$sql = "SELECT id, name, email, subject, message, status, created_at FROM messages $whereSql ORDER BY created_at DESC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $result->free();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Messages Inbox</h1>
        <p>Manage inquiries submitted through the contact page.</p>
    </div>

    <section class="table-controls">
        <div class="filter-tabs">
            <a href="messages.php" class="btn btn-sm <?php echo !$statusFilter ? 'btn-primary' : 'btn-outline-secondary'; ?>">All</a>
            <a href="messages.php?status=Unread" class="btn btn-sm <?php echo $statusFilter === 'Unread' ? 'btn-primary' : 'btn-outline-secondary'; ?>">Unread</a>
            <a href="messages.php?status=Read" class="btn btn-sm <?php echo $statusFilter === 'Read' ? 'btn-primary' : 'btn-outline-secondary'; ?>">Read</a>
            <a href="messages.php?status=Replied" class="btn btn-sm <?php echo $statusFilter === 'Replied' ? 'btn-primary' : 'btn-outline-secondary'; ?>">Replied</a>
        </div>
    </section>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Sender Name</th>
                    <th>Email Address</th>
                    <th>Subject</th>
                    <th>Message Preview</th>
                    <th>Date Sent</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$messages) : ?>
                    <tr><td colspan="7">No messages found.</td></tr>
                <?php else : ?>
                    <?php foreach ($messages as $msg) : ?>
                        <?php 
                        $statusClass = 'status-' . strtolower($msg['status']);
                        if ($msg['status'] === 'Unread') $statusClass = 'status-pending';
                        if ($msg['status'] === 'Read') $statusClass = 'status-confirmed';
                        if ($msg['status'] === 'Replied') $statusClass = 'status-completed';
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($msg['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($msg['email']); ?></td>
                            <td><?php echo htmlspecialchars($msg['subject'] ?: '(No Subject)'); ?></td>
                            <td title="<?php echo htmlspecialchars($msg['message']); ?>">
                                <small><?php echo htmlspecialchars(substr($msg['message'], 0, 50)) . (strlen($msg['message']) > 50 ? '...' : ''); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($msg['created_at']))); ?></td>
                            <td><span class="status-pill <?php echo $statusClass; ?>"><?php echo $msg['status']; ?></span></td>
                            <td>
                                <div class="icon-actions">
                                    <button class="btn btn-link btn-sm" onclick="viewMessage(<?php echo htmlspecialchars(json_encode($msg)); ?>)" title="Read Message">Read</button>
                                    <select class="form-control form-control-sm status-select" onchange="updateMessageStatus(<?php echo $msg['id']; ?>, this.value)">
                                        <option value="Unread" <?php echo $msg['status'] === 'Unread' ? 'selected' : ''; ?>>Unread</option>
                                        <option value="Read" <?php echo $msg['status'] === 'Read' ? 'selected' : ''; ?>>Read</option>
                                        <option value="Replied" <?php echo $msg['status'] === 'Replied' ? 'selected' : ''; ?>>Replied</option>
                                    </select>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Message View Modal -->
    <div id="message-modal" class="modal" aria-hidden="true">
        <div class="modal-backdrop" onclick="closeModal()"></div>
        <div class="modal-dialog">
            <h2 id="modal-subject">Subject</h2>
            <div class="modal-body">
                <p><strong>From:</strong> <span id="modal-from"></span></p>
                <p><strong>Date:</strong> <span id="modal-date"></span></p>
                <hr>
                <div id="modal-message" style="white-space: pre-wrap; margin-top: 15px;"></div>
            </div>
            <div class="modal-actions mt-3">
                <button class="btn btn-secondary" onclick="closeModal()">Close</button>
                <button class="btn btn-primary" id="btn-reply">Reply (Draft)</button>
            </div>
        </div>
    </div>
</main>

<script>
function viewMessage(msg) {
    document.getElementById('modal-subject').innerText = msg.subject || '(No Subject)';
    document.getElementById('modal-from').innerText = msg.name + ' (' + msg.email + ')';
    document.getElementById('modal-date').innerText = msg.created_at;
    document.getElementById('modal-message').innerText = msg.message;
    document.getElementById('message-modal').classList.add('active');
    
    if (msg.status === 'Unread') {
        updateMessageStatus(msg.id, 'Read', true);
    }
}

function closeModal() {
    document.getElementById('message-modal').classList.remove('active');
}

function updateMessageStatus(id, status, silent = false) {
    fetch('api/update_message_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'message_id=' + id + '&status=' + encodeURIComponent(status)
    }).then(res => res.json()).then(data => {
        if (data.success && !silent) location.reload();
    });
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
