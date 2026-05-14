<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    header('Location: orders.php');
    exit();
}

// Fetch order details with customer name
$order = null;
$sql = "SELECT o.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone 
        FROM orders o 
        JOIN customers c ON o.customer_id = c.id 
        WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $order = $result->fetch_assoc();
}
$stmt->close();

if (!$order) {
    die("Order not found.");
}

// Fetch order items
$items = [];
$itemSql = "SELECT oi.*, p.name as product_name, p.variant as product_variant, p.image as product_image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?";
$stmt = $conn->prepare($itemSql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

$pageTitle = 'Order Details - #' . htmlspecialchars($order['order_code']);
$pageKey = 'orders';
$assetBase = '';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<main class="admin-content">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                <a href="orders.php" class="btn btn-outline-secondary btn-sm" style="width: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </a>
                <span style="color: var(--admin-muted); font-size: 0.9rem;">Back to Orders</span>
            </div>
            <h1>Order Details <span style="color: var(--admin-muted); font-weight: 400;">#<?php echo htmlspecialchars($order['order_code']); ?></span></h1>
        </div>
        <div class="header-actions" style="display: flex; gap: 0.75rem;">
            <button class="btn btn-outline-secondary">Print Invoice</button>
            <button class="btn btn-primary">Update Status</button>
        </div>
    </div>

    <div class="dashboard-grid" style="grid-template-columns: 1fr 350px; gap: 1.5rem;">
        <!-- Left Column: Order Items -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="table-card">
                <div class="table-header">
                    <h3>Order Items</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th style="text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item) : ?>
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <div class="product-thumb">
                                                <?php if ($item['product_image']) : ?>
                                                    <img src="../<?php echo htmlspecialchars($item['product_image']); ?>" alt="">
                                                <?php else : ?>
                                                    <span><?php echo strtoupper(substr($item['product_name'], 0, 1)); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="product-meta">
                                                <div class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                                <div class="product-variant"><?php echo htmlspecialchars($item['product_variant']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>PHP <?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td style="text-align: right;"><strong>PHP <?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align: right; padding: 1.5rem;">
                                    <div style="color: var(--admin-muted); margin-bottom: 0.5rem;">Subtotal</div>
                                    <div style="color: var(--admin-muted); margin-bottom: 0.5rem;">Shipping</div>
                                    <div style="font-size: 1.1rem; font-weight: 700; color: var(--admin-text);">Total Amount</div>
                                </td>
                                <td style="text-align: right; padding: 1.5rem;">
                                    <div style="margin-bottom: 0.5rem;">PHP <?php echo number_format($order['total'], 2); ?></div>
                                    <div style="margin-bottom: 0.5rem;">PHP 0.00</div>
                                    <div style="font-size: 1.1rem; font-weight: 700; color: var(--admin-accent);">PHP <?php echo number_format($order['total'], 2); ?></div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="content-card">
                <div class="table-header">
                    <h3>Timeline / Activity</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <div style="position: relative; padding-left: 1.5rem; border-left: 2px solid var(--admin-border);">
                        <div style="margin-bottom: 1.5rem; position: relative;">
                            <div style="position: absolute; left: -21px; top: 0; width: 10px; height: 10px; border-radius: 50%; background: var(--admin-accent); border: 2px solid #fff;"></div>
                            <div style="font-size: 0.85rem; font-weight: 600;">Order Placed</div>
                            <div style="font-size: 0.75rem; color: var(--admin-muted);"><?php echo date('M d, Y | H:i', strtotime($order['created_at'])); ?></div>
                        </div>
                        <div style="margin-bottom: 0; position: relative;">
                            <div style="position: absolute; left: -21px; top: 0; width: 10px; height: 10px; border-radius: 50%; background: var(--admin-border); border: 2px solid #fff;"></div>
                            <div style="font-size: 0.85rem; font-weight: 600; color: var(--admin-muted);">Order Processed</div>
                            <div style="font-size: 0.75rem; color: var(--admin-muted);">Pending action</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Customer & Shipping -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="content-card">
                <div class="table-header">
                    <h3>Customer Details</h3>
                    <a href="view_customer.php?id=<?php echo $order['customer_id']; ?>" class="btn btn-link btn-sm">View Profile</a>
                </div>
                <div style="padding: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                            <?php echo strtoupper(substr($order['customer_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                            <div style="font-size: 0.8rem; color: var(--admin-muted);"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                        </div>
                    </div>
                    <div style="font-size: 0.85rem;">
                        <div style="margin-bottom: 0.5rem;"><span style="color: var(--admin-muted);">Phone:</span> <?php echo htmlspecialchars($order['customer_phone'] ?: 'N/A'); ?></div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="table-header">
                    <h3>Order Summary</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <div class="metric-stack">
                        <div class="metric-row">
                            <span>Order Status</span>
                            <?php 
                            $os = strtolower(str_replace(' ', '-', $order['order_status']));
                            $os_class = 'status-' . $os;
                            ?>
                            <span class="status-pill <?php echo $os_class; ?>"><?php echo $order['order_status']; ?></span>
                        </div>
                        <div class="metric-row">
                            <span>Payment Status</span>
                            <span class="status-pill <?php echo ($order['payment_status'] === 'Paid') ? 'status-completed' : 'status-pending'; ?>">
                                <?php echo $order['payment_status']; ?>
                            </span>
                        </div>
                        <div class="metric-row">
                            <span>Payment Method</span>
                            <strong><?php echo htmlspecialchars($order['payment_method'] ?: 'N/A'); ?></strong>
                        </div>
                        <div class="metric-row">
                            <span>Shipping</span>
                            <strong><?php echo htmlspecialchars($order['shipping_method'] ?: 'Standard'); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
