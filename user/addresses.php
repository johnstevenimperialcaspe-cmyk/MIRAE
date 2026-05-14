<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$pageKey = 'addresses';
$error = '';
$success = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_address'])) {
        $name = trim($_POST['recipient_name'] ?? '');
        $phone = trim($_POST['mobile_number'] ?? '');
        $house = trim($_POST['house_unit_no'] ?? '');
        $street = trim($_POST['street'] ?? '');
        $barangay = trim($_POST['barangay'] ?? '');
        $city = trim($_POST['city_municipality'] ?? '');
        $province = trim($_POST['province'] ?? '');
        $zip = trim($_POST['zip_code'] ?? '');
        $is_default = isset($_POST['is_default']) ? 1 : 0;

        if ($is_default) {
            $conn->query("UPDATE customer_addresses SET is_default = 0 WHERE customer_id = $userId");
        }

        $stmt = $conn->prepare("INSERT INTO customer_addresses (customer_id, recipient_name, mobile_number, house_unit_no, street, barangay, city_municipality, province, zip_code, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('issssssssi', $userId, $name, $phone, $house, $street, $barangay, $city, $province, $zip, $is_default);
        if ($stmt->execute()) $success = 'Address added successfully!';
        else $error = 'Failed to add address.';
        $stmt->close();
    } elseif (isset($_POST['edit_address'])) {
        $addrId = (int)$_POST['address_id'];
        $name = trim($_POST['recipient_name'] ?? '');
        $phone = trim($_POST['mobile_number'] ?? '');
        $house = trim($_POST['house_unit_no'] ?? '');
        $street = trim($_POST['street'] ?? '');
        $barangay = trim($_POST['barangay'] ?? '');
        $city = trim($_POST['city_municipality'] ?? '');
        $province = trim($_POST['province'] ?? '');
        $zip = trim($_POST['zip_code'] ?? '');
        $is_default = isset($_POST['is_default']) ? 1 : 0;

        if ($is_default) {
            $conn->query("UPDATE customer_addresses SET is_default = 0 WHERE customer_id = $userId");
        }

        $stmt = $conn->prepare("UPDATE customer_addresses SET recipient_name=?, mobile_number=?, house_unit_no=?, street=?, barangay=?, city_municipality=?, province=?, zip_code=?, is_default=? WHERE id=? AND customer_id=?");
        $stmt->bind_param('ssssssssiii', $name, $phone, $house, $street, $barangay, $city, $province, $zip, $is_default, $addrId, $userId);
        
        if ($stmt->execute()) $success = 'Address updated successfully!';
        else $error = 'Failed to update address.';
        $stmt->close();
    } elseif (isset($_POST['set_default'])) {
        $addrId = (int) $_POST['address_id'];
        $conn->query("UPDATE customer_addresses SET is_default = 0 WHERE customer_id = $userId");
        $conn->query("UPDATE customer_addresses SET is_default = 1 WHERE id = $addrId AND customer_id = $userId");
        $success = 'Default address updated.';
    } elseif (isset($_POST['delete_address'])) {
        $addrId = (int) $_POST['address_id'];
        $conn->query("DELETE FROM customer_addresses WHERE id = $addrId AND customer_id = $userId AND is_default = 0");
        $success = 'Address deleted.';
    }
}

// Get addresses
$addresses = [];
$stmt = $conn->prepare("SELECT * FROM customer_addresses WHERE customer_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $addresses[] = $row;
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Addresses - MIRAE</title>
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
        
        .address-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .address-card { background: white; border-radius: 12px; border: 1px solid var(--border); padding: 20px; position: relative; }
        .address-card.default { border-color: var(--primary); background: var(--primary-light); }
        .default-badge { position: absolute; top: 15px; right: 15px; background: var(--primary); color: white; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; }
        .address-card h4 { margin: 0 0 10px 0; font-size: 16px; }
        .address-card p { margin: 5px 0; font-size: 14px; color: #4b5563; }
        .address-actions { margin-top: 15px; display: flex; gap: 10px; border-top: 1px solid #f3f4f6; padding-top: 15px; }
        
        .btn-action { background: none; border: none; font-size: 13px; font-weight: 600; color: var(--primary); cursor: pointer; padding: 0; }
        .btn-action.danger { color: #dc2626; }
        .btn-primary { padding: 10px 20px; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        
        /* Modal for adding address */
        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 100; }
        .modal.active { display: flex; }
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 100%; max-width: 500px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .form-group.full { grid-column: span 2; }
        input { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; }
    </style>
</head>
<body class="addresses-page">
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
                <a href="addresses.php" class="nav-link active">My Addresses</a>
                <a href="orders.php" class="nav-link">My Orders</a>
                <a href="messages.php" class="nav-link">My Messages</a>
                <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--border);">
                <a href="logout.php" class="nav-link" style="color: #dc2626;">Log Out</a>
            </nav>
        </aside>
        
        <main class="user-main">
            <div class="page-header">
                <h1>My Addresses</h1>
                <button class="btn btn-primary" onclick="toggleModal(true)">+ Add New Address</button>
            </div>

            <?php if ($success) echo "<div style='color: #059669; background: #ecfdf5; padding: 12px; border-radius: 8px; margin-bottom: 20px;'>$success</div>"; ?>

            <div class="address-grid">
                <?php foreach ($addresses as $addr): ?>
                    <div class="address-card <?php echo $addr['is_default'] ? 'default' : ''; ?>">
                        <?php if ($addr['is_default']) echo '<span class="default-badge">DEFAULT</span>'; ?>
                        <h4><?php echo htmlspecialchars($addr['recipient_name']); ?></h4>
                        <p><?php echo htmlspecialchars($addr['mobile_number']); ?></p>
                        <p>
                            <?php echo htmlspecialchars($addr['house_unit_no']); ?> <?php echo htmlspecialchars($addr['street']); ?>,<br>
                            <?php echo htmlspecialchars($addr['barangay']); ?>,<br>
                            <?php echo htmlspecialchars($addr['city_municipality']); ?>, <?php echo htmlspecialchars($addr['province']); ?><br>
                            <?php echo htmlspecialchars($addr['zip_code']); ?>
                        </p>
                        <div class="address-actions">
                            <button class="btn btn-link btn-sm p-0" onclick="fillEditModal(<?php echo htmlspecialchars(json_encode($addr)); ?>)">Edit</button>
                            <?php if (!$addr['is_default']): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="address_id" value="<?php echo $addr['id']; ?>">
                                    <button type="submit" name="set_default" class="btn btn-link btn-sm p-0 ml-2">Set as Default</button>
                                </form>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Delete this address?')">
                                    <input type="hidden" name="address_id" value="<?php echo $addr['id']; ?>">
                                    <button type="submit" name="delete_address" class="btn btn-link btn-sm p-0 ml-2 text-danger">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="addr-modal" class="modal">
                <div class="modal-content">
                    <h3 id="modal-title" style="margin-top:0;">Add New Address</h3>
                    <form method="post" id="addr-form">
                        <input type="hidden" name="address_id" id="form-addr-id">
                        <div class="form-grid">
                            <div class="form-group full">
                                <label>Recipient's Name</label>
                                <input type="text" name="recipient_name" id="form-name" class="form-control" required>
                            </div>
                            <div class="form-group full">
                                <label>Mobile Number</label>
                                <input type="text" name="mobile_number" id="form-phone" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>House/Unit No.</label>
                                <input type="text" name="house_unit_no" id="form-house" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Street</label>
                                <input type="text" name="street" id="form-street" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Barangay</label>
                                <input type="text" name="barangay" id="form-barangay" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>City / Municipality</label>
                                <input type="text" name="city_municipality" id="form-city" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Province</label>
                                <input type="text" name="province" id="form-province" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>ZIP Code</label>
                                <input type="text" name="zip_code" id="form-zip" class="form-control">
                            </div>
                        </div>
                        <div class="custom-control custom-checkbox mt-3">
                            <input type="checkbox" id="is_default" name="is_default" class="custom-control-input">
                            <label class="custom-control-label" for="is_default">Set as default address</label>
                        </div>
                        <div style="margin-top:25px; display:flex; gap:10px; justify-content: flex-end;">
                            <button type="button" class="btn btn-secondary" onclick="toggleModal(false)">Cancel</button>
                            <button type="submit" name="add_address" id="form-submit-btn" class="btn btn-success">Add Address</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>
        function toggleModal(show, mode = 'add') {
            const modal = document.getElementById('addr-modal');
            const title = document.getElementById('modal-title');
            const btn = document.getElementById('form-submit-btn');
            const form = document.getElementById('addr-form');
            
            if (!show) {
                modal.classList.remove('active');
                return;
            }

            if (mode === 'add') {
                title.textContent = 'Add New Address';
                btn.textContent = 'Add Address';
                btn.name = 'add_address';
                form.reset();
                document.getElementById('form-addr-id').value = '';
            } else {
                title.textContent = 'Edit Address';
                btn.textContent = 'Save Changes';
                btn.name = 'edit_address';
            }
            
            modal.classList.add('active');
        }

        function fillEditModal(data) {
            toggleModal(true, 'edit');
            document.getElementById('form-addr-id').value = data.id;
            document.getElementById('form-name').value = data.recipient_name;
            document.getElementById('form-phone').value = data.mobile_number;
            document.getElementById('form-house').value = data.house_unit_no;
            document.getElementById('form-street').value = data.street;
            document.getElementById('form-barangay').value = data.barangay;
            document.getElementById('form-city').value = data.city_municipality;
            document.getElementById('form-province').value = data.province;
            document.getElementById('form-zip').value = data.zip_code;
            document.getElementById('is_default').checked = data.is_default == 1;
        }
    </script>
    <script src="../js/main.js"></script>
</body>
</html>
