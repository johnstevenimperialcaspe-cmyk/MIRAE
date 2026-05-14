<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';
$pageTitle = 'Dashboard';
$pageKey = 'dashboard';
$assetBase = '';

$totalProducts = 0;
$totalOrders = 0;
$totalCustomers = 0;
$unreadMessages = 0;
$totalSales = 0.0;
$recentOrders = [];
$recentProducts = [];
$newCustomers = 0;
$avgOrderValue = 0.0;

if (!isset($_SESSION['delete_token'])) {
    $_SESSION['delete_token'] = bin2hex(random_bytes(16));
}
$deleteToken = $_SESSION['delete_token'];

if ($result = $conn->query("SELECT COUNT(*) AS total FROM products")) {
    $row = $result->fetch_assoc();
    $totalProducts = (int) ($row['total'] ?? 0);
    $result->free();
}

if ($result = $conn->query("SELECT COUNT(*) AS total FROM orders")) {
    $row = $result->fetch_assoc();
    $totalOrders = (int) ($row['total'] ?? 0);
    $result->free();
}

if ($result = $conn->query("SELECT COUNT(*) AS total FROM customers")) {
    $row = $result->fetch_assoc();
    $totalCustomers = (int) ($row['total'] ?? 0);
    $result->free();
}

if ($result = $conn->query("SELECT COUNT(*) AS total FROM messages WHERE is_read = 0")) {
    $row = $result->fetch_assoc();
    $unreadMessages = (int) ($row['total'] ?? 0);
    $result->free();
}

if ($result = $conn->query("SELECT COALESCE(SUM(total), 0) AS total FROM orders")) {
    $row = $result->fetch_assoc();
    $totalSales = (float) ($row['total'] ?? 0);
    $result->free();
}

if ($totalOrders > 0) {
    $avgOrderValue = $totalSales / $totalOrders;
}

$recentSql = "SELECT o.order_code, o.total, o.order_status, o.created_at, c.name
    FROM orders o
    INNER JOIN customers c ON c.id = o.customer_id
    ORDER BY o.created_at DESC
    LIMIT 5";

if ($result = $conn->query($recentSql)) {
    while ($row = $result->fetch_assoc()) {
        $recentOrders[] = $row;
    }
    $result->free();
}

$recentProductsSql = "SELECT id, name, variant, price, stock, status, image
    FROM products
    ORDER BY created_at DESC
    LIMIT 3";

if ($result = $conn->query($recentProductsSql)) {
    while ($row = $result->fetch_assoc()) {
        $recentProducts[] = $row;
    }
    $result->free();
}

if ($result = $conn->query("SELECT COUNT(*) AS total FROM customers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")) {
    $row = $result->fetch_assoc();
    $newCustomers = (int) ($row['total'] ?? 0);
    $result->free();
}

$dailyTotals = [];
$dailySales = [];
$chartPoints = '';
$chartFillPoints = '';
$chartMax = 0.0;

$range = isset($_GET['range']) ? $_GET['range'] : '7days';
$interval = 6;
$chartLabel = 'Last 7 days';

if ($range === '30days') {
    $interval = 29;
    $chartLabel = 'Last 30 days';
} elseif ($range === 'all') {
    $interval = 89; // Show last 90 days for "All"
    $chartLabel = 'Last 90 days';
}

$dailySql = "SELECT DATE(created_at) AS sale_date, COALESCE(SUM(total), 0) AS total
    FROM orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL $interval DAY)
    GROUP BY sale_date
    ORDER BY sale_date ASC";

if ($result = $conn->query($dailySql)) {
    while ($row = $result->fetch_assoc()) {
        $dailyTotals[$row['sale_date']] = (float) ($row['total'] ?? 0);
    }
    $result->free();
}

$chartData = [];
$today = new DateTime('today');
for ($i = $interval; $i >= 0; $i--) {
    $dateObj = (clone $today)->modify('-' . $i . ' days');
    $dateKey = $dateObj->format('Y-m-d');
    $dayLabel = ($interval > 7) ? $dateObj->format('d M') : $dateObj->format('D');
    $value = $dailyTotals[$dateKey] ?? 0.0;
    
    if ($value > $chartMax) $chartMax = $value;

    $chartData[] = [
        'day' => $dayLabel,
        'value' => $value,
        'date' => $dateKey
    ];
}

foreach ($chartData as &$item) {
    $item['percentage'] = ($chartMax > 0) ? round(($item['value'] / $chartMax) * 100, 2) : 0;
}
unset($item);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="admin-content dashboard-content">
    <div class="page-header">
        <h1>Dashboard</h1>
        <p>Snapshot of orders, products, customers, and sales.</p>
    </div>

    <section class="stats-grid">
        <div class="stat-card">
            <div>
                <p>Total Products</p>
                <strong><?php echo $totalProducts; ?></strong>
            </div>
        </div>
        <div class="stat-card">
            <div>
                <p>Total Orders</p>
                <strong><?php echo $totalOrders; ?></strong>
            </div>
        </div>
        <div class="stat-card">
            <div>
                <p>Total Customers</p>
                <strong><?php echo $totalCustomers; ?></strong>
            </div>
        </div>
        <div class="stat-card">
            <div>
                <p>Total Messages</p>
                <strong><?php echo $unreadMessages; ?></strong>
            </div>
        </div>
        <div class="stat-card">
            <div>
                <p>Total Sales</p>
                <strong><?php echo number_format($totalSales, 0); ?></strong>
            </div>
        </div>
    </section>

    <section class="dashboard-grid">
        <div class="table-card">
            <div class="table-header">
                <div>
                    <h3>Recent Orders</h3>
                    <p>Latest customer purchases.</p>
                </div>
                <a class="btn btn-link btn-sm" href="orders.php">View All Orders</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$recentOrders) : ?>
                            <tr>
                                <td colspan="5">No recent orders found.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($recentOrders as $order) : ?>
                                <?php $statusClass = 'status-' . strtolower(str_replace(' ', '-', $order['order_status'])); ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['order_code']); ?></td>
                                    <td><?php echo htmlspecialchars($order['name']); ?></td>
                                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at']))); ?></td>
                                    <td>PHP <?php echo number_format((float) $order['total'], 2); ?></td>
                                    <td><span class="status-pill <?php echo $statusClass; ?>"><?php echo htmlspecialchars($order['order_status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <div>
                    <h3>Product Management</h3>
                    <p>Recent products and stock levels.</p>
                </div>
                <div class="table-actions">
                    <a class="btn btn-link btn-sm" href="products.php">View All Products</a>
                    <a class="btn btn-primary btn-sm" href="products.php">+ Add Product</a>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Size / Type</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$recentProducts) : ?>
                            <tr>
                                <td colspan="6">No products found.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($recentProducts as $product) : ?>
                                <?php
                                $productStatus = strtolower(str_replace(' ', '-', $product['status']));
                                $initial = strtoupper(substr($product['name'], 0, 1));
                                $imagePath = '';
                                if (!empty($product['image'])) {
                                    $imagePath = '../' . ltrim($product['image'], '/');
                                }
                                ?>
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <div class="product-thumb">
                                                <?php if ($imagePath) : ?>
                                                    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" />
                                                <?php else : ?>
                                                    <span><?php echo $initial; ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="product-meta">
                                                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                                <div class="product-variant"><?php echo htmlspecialchars($product['variant'] ?? ''); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['variant'] ?? ''); ?></td>
                                    <td>PHP <?php echo number_format((float) $product['price'], 2); ?></td>
                                    <td><?php echo (int) $product['stock']; ?></td>
                                    <td><span class="status-pill status-<?php echo $productStatus; ?>"><?php echo htmlspecialchars($product['status']); ?></span></td>
                                    <td>
                                        <div class="icon-actions">
                                            <button type="button" class="btn btn-outline-primary btn-sm btn-icon" data-action="edit" data-id="<?php echo (int) $product['id']; ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>" aria-label="Edit product">
                                                <svg viewBox="0 0 24 24" aria-hidden="true" style="width:16px; height:16px;">
                                                    <path d="M4 16.5V20h3.5l10-10-3.5-3.5-10 10z" fill="none" stroke="currentColor" stroke-width="1.5" />
                                                    <path d="M14 6l3.5 3.5" fill="none" stroke="currentColor" stroke-width="1.5" />
                                                </svg>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-icon" data-action="delete" data-id="<?php echo (int) $product['id']; ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>" aria-label="Delete product">
                                                <svg viewBox="0 0 24 24" aria-hidden="true" style="width:16px; height:16px;">
                                                    <path d="M6 7h12" fill="none" stroke="currentColor" stroke-width="1.5" />
                                                    <path d="M9 7V5h6v2" fill="none" stroke="currentColor" stroke-width="1.5" />
                                                    <path d="M8 7l1 12h6l1-12" fill="none" stroke="currentColor" stroke-width="1.5" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="dashboard-grid dashboard-bottom">
        <div class="chart-card">
            <div class="table-header">
                <div>
                    <h3>Sales Overview</h3>
                    <p>Last 7 days performance.</p>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo $chartLabel; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-menu-item <?php echo $range === '7days' ? 'active' : ''; ?>" href="?range=7days">Last 7 days</a>
                        <a class="dropdown-menu-item <?php echo $range === '30days' ? 'active' : ''; ?>" href="?range=30days">Last 30 days</a>
                        <a class="dropdown-menu-item <?php echo $range === 'all' ? 'active' : ''; ?>" href="?range=all">All (90 days)</a>
                    </div>
                </div>
            </div>
            <div class="chart-area">
                <div class="bar-chart-container">
                    <div class="bar-chart" style="--chart-gap: <?php echo ($interval > 7) ? '2px' : '1.2rem'; ?>;">
                        <?php foreach ($chartData as $data) : ?>
                            <div class="bar-item" style="--percent: <?php echo $data['percentage']; ?>%;">
                                <div class="bar-fill" title="PHP <?php echo number_format($data['value'], 2); ?>">
                                    <div class="bar-tooltip">PHP <?php echo number_format($data['value'], 2); ?></div>
                                </div>
                                <span class="bar-label"><?php echo $data['day']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="metric-stack">
                <div class="metric-row">
                    <span>Total Sales</span>
                    <strong>PHP <?php echo number_format($totalSales, 2); ?></strong>
                </div>
                <div class="metric-row">
                    <span>Total Orders</span>
                    <strong><?php echo $totalOrders; ?></strong>
                </div>
                <div class="metric-row">
                    <span>Average Order Value</span>
                    <strong>PHP <?php echo number_format($avgOrderValue, 2); ?></strong>
                </div>
                <div class="metric-row">
                    <span>New Customers</span>
                    <strong><?php echo $newCustomers; ?></strong>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="table-header">
                <div>
                    <h3>Content Management</h3>
                    <p>Manage all editable pages on your website.</p>
                </div>
            </div>
            <div class="content-list">
                <div class="content-item">
                    <div>
                        <strong>Home Page</strong>
                        <span>Edit homepage content, texts, and sections.</span>
                    </div>
                    <a class="btn btn-outline-primary btn-sm" href="content/home.php">Edit</a>
                </div>
                <div class="content-item">
                    <div>
                        <strong>Product Page</strong>
                        <span>Edit product page content and settings.</span>
                    </div>
                    <a class="btn btn-outline-primary btn-sm" href="content/product.php">Edit</a>
                </div>
                <div class="content-item">
                    <div>
                        <strong>Data Page</strong>
                        <span>Edit data/information displayed on data page.</span>
                    </div>
                    <a class="btn btn-outline-primary btn-sm" href="content/data.php">Edit</a>
                </div>
                <div class="content-item">
                    <div>
                        <strong>About Us</strong>
                        <span>Edit about us page content.</span>
                    </div>
                    <a class="btn btn-outline-primary btn-sm" href="content/about.php">Edit</a>
                </div>
                <div class="content-item">
                    <div>
                        <strong>FAQs</strong>
                        <span>Add, edit or remove frequently asked questions.</span>
                    </div>
                    <a class="btn btn-outline-primary btn-sm" href="content/faqs.php">Edit</a>
                </div>
                <div class="content-item">
                    <div>
                        <strong>Contacts</strong>
                        <span>Edit contact information and details.</span>
                    </div>
                    <a class="btn btn-outline-primary btn-sm" href="content/contacts.php">Edit</a>
                </div>
            </div>
        </div>
    </section>

    <form id="dashboard-delete-form" action="products.php" method="post">
        <input type="hidden" name="action" value="delete" />
        <input type="hidden" name="id" id="dashboard-delete-id" value="" />
        <input type="hidden" name="delete_token" value="<?php echo htmlspecialchars($deleteToken); ?>" />
    </form>

    <div class="mirae-modal" id="product-modal" aria-hidden="true">
        <div class="mirae-modal-backdrop" data-modal-close="true"></div>
        <div class="mirae-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="product-modal-title">
            <h4 id="product-modal-title">Product action</h4>
            <p id="product-modal-desc">Choose an action.</p>
            <div class="modal-actions mt-3">
                <button type="button" class="btn btn-secondary" data-modal-close="true">Cancel</button>
                <a class="btn btn-primary" href="#" id="product-modal-edit">Edit Product</a>
                <button type="button" class="btn btn-danger" id="product-modal-delete">Delete Product</button>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
