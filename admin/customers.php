<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';
$pageTitle = 'User Accounts';
$pageKey = 'customers';
$assetBase = '';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

$whereClauses = [];
if ($search) {
    $whereClauses[] = "(name LIKE '%$search%' OR email LIKE '%$search%')";
}
if ($statusFilter) {
    $whereClauses[] = "status = '$statusFilter'";
}

$whereSql = count($whereClauses) > 0 ? "WHERE " . implode(' AND ', $whereClauses) : '';

$customers = [];
$sql = "SELECT id, name, email, phone, address, city, province, status, created_at, last_login, last_logout 
        FROM customers 
        $whereSql
        ORDER BY created_at DESC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    $result->free();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>User Accounts</h1>
        <p>Manage registered customers and their access.</p>
    </div>

    <section class="table-controls">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <form action="" method="get" class="search-form" style="flex: 1;">
                <div style="flex: 1; max-width: 300px;">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div style="width: 160px;">
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="Active" <?php echo $statusFilter === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo $statusFilter === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <?php if ($search || $statusFilter) : ?>
                    <a href="customers.php" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </form>
            <a href="?export=csv" class="btn btn-outline-secondary">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Export CSV
            </a>
        </div>
    </section>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Full Name</th>
                    <th>Email / Mobile</th>
                    <th>Address</th>
                    <th>Registered</th>
                    <th>Status</th>
                    <th>Activity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$customers) : ?>
                    <tr><td colspan="8">No customers found.</td></tr>
                <?php else : ?>
                    <?php foreach ($customers as $customer) : ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($customer['id']); ?></td>
                            <td><strong><?php echo htmlspecialchars($customer['name']); ?></strong></td>
                            <td>
                                <div><?php echo htmlspecialchars($customer['email']); ?></div>
                                <small><?php echo htmlspecialchars($customer['phone'] ?? '-'); ?></small>
                            </td>
                            <td>
                                <small>
                                    <?php echo htmlspecialchars($customer['address']); ?>, 
                                    <?php echo htmlspecialchars($customer['city']); ?>, 
                                    <?php echo htmlspecialchars($customer['province']); ?>
                                </small>
                            </td>
                            <td><?php echo htmlspecialchars(date('M d, Y', strtotime($customer['created_at']))); ?></td>
                            <td>
                                <span class="status-pill <?php echo $customer['status'] === 'Active' ? 'status-completed' : 'status-cancelled'; ?>">
                                    <?php echo htmlspecialchars($customer['status']); ?>
                                </span>
                            </td>
                            <td>
                                <small>Login: <?php echo $customer['last_login'] ? date('M d, H:i', strtotime($customer['last_login'])) : 'Never'; ?></small><br>
                                <small>Logout: <?php echo $customer['last_logout'] ? date('M d, H:i', strtotime($customer['last_logout'])) : 'Never'; ?></small>
                            </td>
                            <td>
                                <div class="icon-actions">
                                    <a href="view_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-outline-primary btn-sm" title="View Profile">
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </a>
                                    <button class="btn <?php echo $customer['status'] === 'Active' ? 'btn-outline-danger' : 'btn-outline-success'; ?> btn-sm btn-icon" title="<?php echo $customer['status'] === 'Active' ? 'Deactivate' : 'Reactivate'; ?>">
                                        <?php if ($customer['status'] === 'Active') : ?>
                                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                        <?php else : ?>
                                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                        <?php endif; ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
