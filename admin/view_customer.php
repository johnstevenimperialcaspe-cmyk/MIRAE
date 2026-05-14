<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';

$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($customerId <= 0) {
    header('Location: customers.php');
    exit();
}

// Fetch customer details
$customer = null;
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $customer = $result->fetch_assoc();
}
$stmt->close();

if (!$customer) {
    die("Customer not found.");
}

// Fetch addresses
$addresses = [];
$stmt = $conn->prepare("SELECT * FROM customer_addresses WHERE customer_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $addresses[] = $row;
}
$stmt->close();

// Fetch orders
$orders = [];
$stmt = $conn->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

$pageTitle = 'Customer Profile - ' . htmlspecialchars($customer['name']);
$pageKey = 'customers';
$assetBase = '';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<main class="admin-content">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                <a href="customers.php" class="btn btn-outline-secondary btn-sm" style="width: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </a>
                <span style="color: var(--admin-muted); font-size: 0.9rem;">Back to Customers</span>
            </div>
            <h1>Customer Profile</h1>
        </div>
        <div class="header-actions">
            <button class="btn <?php echo $customer['status'] === 'Active' ? 'btn-outline-danger' : 'btn-outline-success'; ?>">
                <?php echo $customer['status'] === 'Active' ? 'Deactivate Account' : 'Reactivate Account'; ?>
            </button>
        </div>
    </div>

    <div class="dashboard-grid" style="grid-template-columns: 350px 1fr; gap: 1.5rem;">
        <!-- Left Column: Personal Info -->
        <div class="content-card" style="height: fit-content;">
            <div class="table-header">
                <h3>Personal Information</h3>
            </div>
            <div style="padding: 1.5rem;">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <div style="width: 80px; height: 80px; background: #f3f4f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem; font-weight: 700; color: var(--admin-accent);">
                        <?php echo strtoupper(substr($customer['name'], 0, 1)); ?>
                    </div>
                    <h2 style="margin: 0; font-size: 1.25rem;"><?php echo htmlspecialchars($customer['name']); ?></h2>
                    <span class="status-pill <?php echo $customer['status'] === 'Active' ? 'status-completed' : 'status-cancelled'; ?>" style="margin-top: 0.5rem;">
                        <?php echo $customer['status']; ?>
                    </span>
                </div>

                <div class="metric-stack" style="border-top: 1px solid var(--admin-border); padding-top: 1rem;">
                    <div class="metric-row">
                        <span>Email</span>
                        <strong><?php echo htmlspecialchars($customer['email']); ?></strong>
                    </div>
                    <div class="metric-row">
                        <span>Phone</span>
                        <strong><?php echo htmlspecialchars($customer['phone'] ?: 'N/A'); ?></strong>
                    </div>
                    <div class="metric-row">
                        <span>Joined</span>
                        <strong><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></strong>
                    </div>
                    <div class="metric-row">
                        <span>Last Login</span>
                        <strong><?php echo $customer['last_login'] ? date('M d, H:i', strtotime($customer['last_login'])) : 'Never'; ?></strong>
                    </div>
                </div>

                <div style="margin-top: 1.5rem;">
                    <label style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--admin-muted); display: block; margin-bottom: 0.5rem;">Internal Notes</label>
                    <textarea class="form-control" style="width: 100%; height: 80px; font-size: 0.85rem;" placeholder="Add private notes about this customer..."><?php echo htmlspecialchars($customer['notes'] ?? ''); ?></textarea>
                    <button class="btn btn-primary btn-sm" style="margin-top: 0.5rem; width: 100%;">Save Notes</button>
                </div>
            </div>
        </div>

        <!-- Right Column: Activity & History -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <!-- Saved Addresses -->
            <div class="table-card">
                <div class="table-header">
                    <h3>Saved Addresses</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Recipient</th>
                                <th>Address</th>
                                <th>Type</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($addresses)) : ?>
                                <tr><td colspan="4">No addresses saved.</td></tr>
                            <?php else : ?>
                                <?php foreach ($addresses as $addr) : ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($addr['recipient_name']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($addr['mobile_number']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($addr['house_unit_no'] . ' ' . $addr['street']); ?>,
                                            <?php echo htmlspecialchars($addr['barangay']); ?>,
                                            <?php echo htmlspecialchars($addr['city_municipality']); ?>,
                                            <?php echo htmlspecialchars($addr['province']); ?> <?php echo htmlspecialchars($addr['zip_code']); ?>
                                        </td>
                                        <td>
                                            <?php if ($addr['is_default']) : ?>
                                                <span class="status-pill status-completed">Default</span>
                                            <?php else : ?>
                                                <span class="status-pill status-pending">Secondary</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><small><?php echo date('M d, Y', strtotime($addr['created_at'])); ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Order History -->
            <div class="table-card">
                <div class="table-header">
                    <h3>Order History</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Order Code</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)) : ?>
                                <tr><td colspan="5">No orders placed yet.</td></tr>
                            <?php else : ?>
                                <?php foreach ($orders as $order) : ?>
                                    <tr>
                                        <td><strong>#<?php echo htmlspecialchars($order['order_code']); ?></strong></td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td>PHP <?php echo number_format($order['total'], 2); ?></td>
                                        <td>
                                            <?php 
                                            $os = strtolower(str_replace(' ', '-', $order['order_status']));
                                            $os_class = 'status-' . $os;
                                            if ($os === 'confirmed') $os_class = 'status-processing'; // Map to existing classes
                                            ?>
                                            <span class="status-pill <?php echo $os_class; ?>">
                                                <?php echo htmlspecialchars($order['order_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-outline-primary btn-sm btn-icon" title="View Order">
                                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
