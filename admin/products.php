<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';
$pageTitle = 'Products';
$pageKey = 'products';
$assetBase = '';

$notice = isset($_GET['notice']) ? trim($_GET['notice']) : '';
$errors = [];
$editingProduct = null;
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

if (!isset($_SESSION['delete_token'])) {
    $_SESSION['delete_token'] = bin2hex(random_bytes(16));
}
$deleteToken = $_SESSION['delete_token'];

function fetchProduct(mysqli $conn, int $id): ?array {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    return $product ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $variant = trim($_POST['variant'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $status = trim($_POST['status'] ?? 'In Stock');
    $image = trim($_POST['image'] ?? '');
    $uploadedImagePath = '';

    if ($action === 'delete') {
        $deleteId = (int) ($_POST['id'] ?? 0);
        $postedToken = $_POST['delete_token'] ?? '';
        if (!hash_equals($_SESSION['delete_token'] ?? '', $postedToken)) {
            $errors[] = 'Delete confirmation failed. Please try again.';
        } elseif ($deleteId > 0) {
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param('i', $deleteId);
            $stmt->execute();
            $stmt->close();
            header('Location: products.php?notice=' . urlencode('Product deleted.'));
            exit();
        }
    }

    if (!empty($_FILES['image_file']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['image_file']['tmp_name'];
            $originalName = $_FILES['image_file']['name'] ?? '';
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png'];

            if (!in_array($ext, $allowedExt, true)) {
                $errors[] = 'Image must be a JPG or PNG file.';
            } elseif (!@getimagesize($tmpName)) {
                $errors[] = 'Uploaded file is not a valid image.';
            } else {
                $uploadDir = __DIR__ . '/../uploads/products';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $fileName = 'product_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destPath = $uploadDir . '/' . $fileName;
                if (move_uploaded_file($tmpName, $destPath)) {
                    $uploadedImagePath = 'uploads/products/' . $fileName;
                } else {
                    $errors[] = 'Failed to save uploaded image.';
                }
            }
        } else {
            $errors[] = 'Image upload failed.';
        }
    }

    if ($uploadedImagePath) {
        $image = $uploadedImagePath;
    }

    if ($action === 'create' || $action === 'update') {
        if ($name === '' || $sku === '') {
            $errors[] = 'Name and SKU are required.';
        }

        if (!$errors) {
            if ($action === 'create') {
                $stmt = $conn->prepare(
                    "INSERT INTO products (name, description, sku, variant, price, stock, status, image)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param('ssssdiss', $name, $description, $sku, $variant, $price, $stock, $status, $image);
                $stmt->execute();
                $stmt->close();
                header('Location: products.php?notice=' . urlencode('Product created.'));
                exit();
            }

            if ($action === 'update') {
                $updateId = (int) ($_POST['id'] ?? 0);
                $existingProduct = $updateId > 0 ? fetchProduct($conn, $updateId) : null;
                if ($image === '' && $existingProduct) {
                    $image = $existingProduct['image'] ?? '';
                }
                if ($updateId > 0) {
                    $stmt = $conn->prepare(
                        "UPDATE products
                        SET name = ?, description = ?, sku = ?, variant = ?, price = ?, stock = ?, status = ?, image = ?
                        WHERE id = ?"
                    );
                    $stmt->bind_param('ssssdissi', $name, $description, $sku, $variant, $price, $stock, $status, $image, $updateId);
                    $stmt->execute();
                    $stmt->close();
                    header('Location: products.php?notice=' . urlencode('Product updated.'));
                    exit();
                }
            }
        }
    }
}

if ($editId > 0) {
    $editingProduct = fetchProduct($conn, $editId);
}

$products = [];
if ($result = $conn->query("SELECT * FROM products ORDER BY created_at DESC")) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $result->free();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Products</h1>
        <p>Manage active and archived product listings.</p>
    </div>

    <?php if ($notice) : ?>
        <div class="card" style="margin-bottom: 1rem;"><?php echo htmlspecialchars($notice); ?></div>
    <?php endif; ?>

    <?php if ($errors) : ?>
        <div class="card" style="margin-bottom: 1rem; color: #b42318;">
            <?php foreach ($errors as $error) : ?>
                <div><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="form-card" action="products.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?php echo $editingProduct ? 'update' : 'create'; ?>" />
        <?php if ($editingProduct) : ?>
            <input type="hidden" name="id" value="<?php echo (int) $editingProduct['id']; ?>" />
        <?php endif; ?>
        <div class="form-group">
            <label for="prod-name">Product Name</label>
            <input id="prod-name" name="name" type="text" class="form-control" value="<?php echo htmlspecialchars($editingProduct['name'] ?? ''); ?>" required />
        </div>
        <div class="form-group">
            <label for="prod-sku">SKU</label>
            <input id="prod-sku" name="sku" type="text" class="form-control" value="<?php echo htmlspecialchars($editingProduct['sku'] ?? ''); ?>" required />
        </div>
        <div class="form-group">
            <label for="prod-variant">Size</label>
            <input id="prod-variant" name="variant" type="text" class="form-control" value="<?php echo htmlspecialchars($editingProduct['variant'] ?? ''); ?>" />
        </div>
        <div class="form-group">
            <label for="prod-price">Price</label>
            <input id="prod-price" name="price" type="number" step="0.01" class="form-control" value="<?php echo htmlspecialchars($editingProduct['price'] ?? ''); ?>" />
        </div>
        <div class="form-group">
            <label for="prod-stock">Stock</label>
            <input id="prod-stock" name="stock" type="number" class="form-control" value="<?php echo htmlspecialchars($editingProduct['stock'] ?? ''); ?>" />
        </div>
        <div class="form-group">
            <label for="prod-status">Status</label>
            <select id="prod-status" name="status" class="form-control">
                <?php
                $statuses = ['In Stock', 'Out of Stock', 'Archived'];
                $currentStatus = $editingProduct['status'] ?? 'In Stock';
                foreach ($statuses as $status) :
                ?>
                    <option value="<?php echo $status; ?>" <?php echo ($status === $currentStatus) ? 'selected' : ''; ?>>
                        <?php echo $status; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="prod-image">Image Path</label>
            <input id="prod-image" name="image" type="text" class="form-control" value="<?php echo htmlspecialchars($editingProduct['image'] ?? ''); ?>" />
        </div>
        <div class="form-group">
            <label for="prod-image-file">Image Upload (JPG or PNG)</label>
            <input id="prod-image-file" name="image_file" type="file" class="form-control-file" accept="image/jpeg, image/png" />
        </div>
        <div class="form-group">
            <label for="prod-desc">Description</label>
            <textarea id="prod-desc" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($editingProduct['description'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><?php echo $editingProduct ? 'Update Product' : 'Add Product'; ?></button>
        <?php if ($editingProduct) : ?>
            <a href="products.php" class="btn btn-link btn-sm">Cancel edit</a>
        <?php endif; ?>
    </form>

    <div class="table-wrap" style="margin-top: 2rem;">
        <table>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Product</th>
                    <th>Size</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$products) : ?>
                    <tr><td colspan="7">No products found.</td></tr>
                <?php else : ?>
                    <?php foreach ($products as $product) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['sku']); ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['variant']); ?></td>
                            <td>PHP <?php echo number_format((float) $product['price'], 2); ?></td>
                            <td><?php echo (int) $product['stock']; ?></td>
                            <td><span class="badge"><?php echo htmlspecialchars($product['status']); ?></span></td>
                            <td>
                                <a href="products.php?edit=<?php echo (int) $product['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="products.php" method="post" style="display: inline;">
                                    <input type="hidden" name="action" value="delete" />
                                    <input type="hidden" name="id" value="<?php echo (int) $product['id']; ?>" />
                                    <input type="hidden" name="delete_token" value="<?php echo htmlspecialchars($deleteToken); ?>" />
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
