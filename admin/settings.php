<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';
$pageTitle = 'Settings';
$pageKey = 'settings';
$assetBase = '';

$notice = '';
$settings = [
    'site_name' => 'MIRAE',
    'currency' => 'PHP',
    'contact_email' => 'admin@mirae.com',
    'low_stock_threshold' => '10'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings['site_name'] = trim($_POST['site_name'] ?? 'MIRAE');
    $settings['currency'] = trim($_POST['currency'] ?? 'PHP');
    $settings['contact_email'] = trim($_POST['contact_email'] ?? 'admin@mirae.com');
    $settings['low_stock_threshold'] = trim($_POST['low_stock_threshold'] ?? '10');

    $stmt = $conn->prepare(
        "INSERT INTO settings (setting_key, setting_value)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
    );

    foreach ($settings as $key => $value) {
        $stmt->bind_param('ss', $key, $value);
        $stmt->execute();
    }

    $stmt->close();
    $notice = 'Settings updated.';
}

if ($result = $conn->query("SELECT setting_key, setting_value FROM settings")) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    $result->free();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Settings</h1>
        <p>Configure site defaults and operational thresholds.</p>
    </div>

    <?php if ($notice) : ?>
        <div class="card" style="margin-bottom: 1rem;"><?php echo htmlspecialchars($notice); ?></div>
    <?php endif; ?>

    <form class="form-card" action="settings.php" method="post">
        <div class="form-group">
            <label for="site-name">Site Name</label>
            <input id="site-name" name="site_name" type="text" class="form-control" value="<?php echo htmlspecialchars($settings['site_name']); ?>" />
        </div>
        <div class="form-group">
            <label for="currency">Currency</label>
            <input id="currency" name="currency" type="text" class="form-control" value="<?php echo htmlspecialchars($settings['currency']); ?>" />
        </div>
        <div class="form-group">
            <label for="contact-email">Contact Email</label>
            <input id="contact-email" name="contact_email" type="email" class="form-control" value="<?php echo htmlspecialchars($settings['contact_email']); ?>" />
        </div>
        <div class="form-group">
            <label for="low-stock">Low Stock Threshold</label>
            <input id="low-stock" name="low_stock_threshold" type="number" class="form-control" value="<?php echo htmlspecialchars($settings['low_stock_threshold']); ?>" />
        </div>
        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
