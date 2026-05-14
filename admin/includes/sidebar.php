<?php
$adminName = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Administrator';
$adminRole = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : 'Administrator';
?>
<aside class="admin-sidebar">
    <div class="admin-brand">MIRAE<span>Admin Portal</span></div>
    <nav class="admin-nav">
        <a href="<?php echo $assetBase; ?>dashboard.php" class="<?php echo ($pageKey === 'dashboard') ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <span>Dashboard</span>
        </a>

        <div class="nav-group">
            <small>User Accounts</small>
            <a href="<?php echo $assetBase; ?>customers.php" class="<?php echo ($pageKey === 'customers') ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <span>All Users</span>
            </a>
            <a href="<?php echo $assetBase; ?>logs.php" class="<?php echo ($pageKey === 'logs') ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                <span>Login / Logout Logs</span>
            </a>
        </div>

        <div class="nav-group">
            <small>Orders</small>
            <a href="<?php echo $assetBase; ?>orders.php" class="<?php echo ($pageKey === 'orders' && !isset($_GET['status'])) ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                <span>All Orders</span>
            </a>
            <a href="<?php echo $assetBase; ?>orders.php?status=active" class="<?php echo ($pageKey === 'orders' && isset($_GET['status']) && $_GET['status'] === 'active') ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                <span>Active Orders</span>
            </a>
        </div>

        <div class="nav-group">
            <small>Messages</small>
            <a href="<?php echo $assetBase; ?>messages.php" class="<?php echo ($pageKey === 'messages') ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                <span>Inbox</span>
            </a>
        </div>

        <div class="nav-group">
            <small>Contents</small>
            <a href="<?php echo $assetBase; ?>content/home.php" class="<?php echo ($pageKey === 'content-home') ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                <span>Home</span>
            </a>
            <a href="<?php echo $assetBase; ?>content/product.php" class="<?php echo ($pageKey === 'content-product') ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                <span>Product</span>
            </a>
            <a href="<?php echo $assetBase; ?>content/data.php" class="<?php echo ($pageKey === 'content-data') ? 'active' : ''; ?>">
                <span>Data</span>
            </a>
            <a href="<?php echo $assetBase; ?>content/about.php" class="<?php echo ($pageKey === 'content-about') ? 'active' : ''; ?>">
                <span>About Us</span>
            </a>
            <a href="<?php echo $assetBase; ?>content/faqs.php" class="<?php echo ($pageKey === 'content-faqs') ? 'active' : ''; ?>">
                <span>FAQs</span>
            </a>
            <a href="<?php echo $assetBase; ?>content/contacts.php" class="<?php echo ($pageKey === 'content-contacts') ? 'active' : ''; ?>">
                <span>Contacts</span>
            </a>
        </div>

        <div class="nav-group">
            <small>Settings</small>
            <a href="<?php echo $assetBase; ?>settings.php" class="<?php echo ($pageKey === 'settings') ? 'active' : ''; ?>">
                <span>Settings</span>
            </a>
            <a href="<?php echo $assetBase; ?>logout.php">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</aside>
<div class="admin-main">
    <header class="admin-topbar">
        <button type="button" class="btn btn-light btn-sm d-md-none" id="sidebar-toggle">Menu</button>
        <div class="admin-user">
            <div>
                <strong><?php echo htmlspecialchars($adminName); ?></strong><br />
                <span><?php echo htmlspecialchars($adminRole); ?></span>
            </div>
        </div>
    </header>
