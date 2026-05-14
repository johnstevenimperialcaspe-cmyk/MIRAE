<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Get user addresses
$addresses = [];
$stmt = $conn->prepare("SELECT * FROM customer_addresses WHERE customer_id = ? ORDER BY is_default DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $addresses[] = $row;
$stmt->close();

if (empty($addresses)) {
    header('Location: addresses.php?notice=' . urlencode('Please add a delivery address first.'));
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - MIRAE</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/loader.css">
    <style>
        :root { --primary: #1b7d3f; --primary-light: #ecfdf5; --text-dark: #111827; --text-gray: #6b7280; --border: #e5e7eb; }
        body { font-family: 'DM Sans', sans-serif; background: #f9fafb; margin: 0; color: var(--text-dark); padding: 40px 20px; }
        .checkout-container { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1fr 350px; gap: 30px; }
        .card { background: white; border-radius: 12px; border: 1px solid var(--border); padding: 25px; margin-bottom: 20px; }
        h1 { font-size: 24px; margin-bottom: 30px; }
        h2 { font-size: 18px; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #f3f4f6; padding-bottom: 10px; }
        
        .address-item { border: 1px solid var(--border); padding: 15px; border-radius: 8px; margin-bottom: 10px; cursor: pointer; position: relative; }
        .address-item.selected { border-color: var(--primary); background: var(--primary-light); }
        .address-item input { position: absolute; opacity: 0; }
        .address-item p { margin: 2px 0; font-size: 14px; }
        
        .order-summary-item { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; }
        .total-row { border-top: 2px solid #f3f4f6; padding-top: 15px; margin-top: 15px; font-size: 18px; font-weight: 700; }
        
        .btn-place-order { width: 100%; padding: 15px; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 16px; margin-top: 20px; }
        .btn-place-order:disabled { background: #9ca3af; cursor: not-allowed; }
        
        .cart-item-small { display: flex; gap: 15px; margin-bottom: 15px; }
        .cart-item-small img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
        .cart-item-small-info { flex: 1; }
        .cart-item-small-name { font-size: 14px; font-weight: 600; }
        .cart-item-small-qty { font-size: 12px; color: var(--text-gray); }

        @media (max-width: 992px) {
            .checkout-container { grid-template-columns: 1fr; gap: 20px; }
            body { padding: 20px 15px; }
        }

        @media (max-width: 576px) {
            .card { padding: 15px; }
            h1 { font-size: 20px; margin-bottom: 20px; }
            .btn-place-order { padding: 12px; font-size: 14px; }
        }
    </style>
</head>
<body class="checkout-page">
    <div class="preloader">
        <div class="loader"></div>
    </div>
    <script>
        // Preloader fallback for speed
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const p = document.querySelector('.preloader');
                if (p && p.style.display !== 'none') {
                    p.classList.add('fade-out');
                    setTimeout(() => p.style.display = 'none', 600);
                }
            }, 3000); // 3s max wait
        });
    </script>
    <div class="checkout-container">
        <div class="left-col">
            <h1>Checkout</h1>
            
            <div class="card">
                <h2>1. Delivery Address</h2>
                <form id="checkout-form">
                    <?php foreach ($addresses as $addr): ?>
                        <label class="address-item <?php echo $addr['is_default'] ? 'selected' : ''; ?>">
                            <input type="radio" name="address_id" value="<?php echo $addr['id']; ?>" <?php echo $addr['is_default'] ? 'checked' : ''; ?> onchange="updateSelection(this)">
                            <p><strong><?php echo htmlspecialchars($addr['recipient_name']); ?></strong> (<?php echo htmlspecialchars($addr['mobile_number']); ?>)</p>
                            <p><?php echo htmlspecialchars($addr['house_unit_no'] . ' ' . $addr['street']); ?>, <?php echo htmlspecialchars($addr['barangay']); ?></p>
                            <p><?php echo htmlspecialchars($addr['city_municipality'] . ', ' . $addr['province'] . ' ' . $addr['zip_code']); ?></p>
                        </label>
                    <?php endforeach; ?>
                </form>
            </div>
            
            <div class="card">
                <h2>2. Payment Method</h2>
                <div class="d-flex">
                    <div class="custom-control custom-radio mr-4">
                        <input type="radio" id="pm-cod" name="payment_method" class="custom-control-input" value="COD" checked>
                        <label class="custom-control-label" for="pm-cod">Cash on Delivery</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="pm-gcash" name="payment_method" class="custom-control-input" value="GCash">
                        <label class="custom-control-label" for="pm-gcash">GCash</label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="right-col">
            <div class="card">
                <h2>Order Summary</h2>
                <div id="checkout-cart-items">
                    <!-- Loaded from JS -->
                </div>
                
                <div class="order-summary-item">
                    <span>Subtotal</span>
                    <span id="summary-subtotal">PHP 0.00</span>
                </div>
                <div class="order-summary-item">
                    <span>Shipping Fee</span>
                    <span>PHP 50.00</span>
                </div>
                <div class="order-summary-item total-row">
                    <span>Total</span>
                    <span id="summary-total" style="color: var(--primary);">PHP 0.00</span>
                </div>
                
                <button type="button" class="btn btn-success btn-lg btn-block" id="place-order-btn">Place Order</button>
            </div>
            <a href="../product.html" style="display: block; text-align: center; font-size: 14px; color: var(--text-gray); text-decoration: none;">← Back to Shopping</a>
        </div>
    </div>

    <script>
        const cart = JSON.parse(localStorage.getItem('mirae_cart') || '[]');
        const itemsContainer = document.getElementById('checkout-cart-items');
        const subtotalEl = document.getElementById('summary-subtotal');
        const totalEl = document.getElementById('summary-total');

        function renderSummary() {
            if (cart.length === 0) {
                window.location.href = '../product.html';
                return;
            }

            itemsContainer.innerHTML = '';
            let subtotal = 0;

            cart.forEach(item => {
                subtotal += item.unitPrice * item.qty;
                itemsContainer.innerHTML += `
                    <div class="cart-item-small">
                        <img src="../${item.image}" alt="">
                        <div class="cart-item-small-info">
                            <div class="cart-item-small-name">${item.name}</div>
                            <div class="cart-item-small-qty">Qty: ${item.qty} × PHP ${item.unitPrice.toFixed(2)}</div>
                        </div>
                        <div style="font-size: 14px; font-weight: 600;">PHP ${(item.unitPrice * item.qty).toFixed(2)}</div>
                    </div>
                `;
            });

            subtotalEl.textContent = `PHP ${subtotal.toFixed(2)}`;
            totalEl.textContent = `PHP ${(subtotal + 50).toFixed(2)}`;
        }

        function updateSelection(radio) {
            document.querySelectorAll('.address-item').forEach(el => el.classList.remove('selected'));
            radio.parentElement.classList.add('selected');
        }

        document.getElementById('place-order-btn').addEventListener('click', async () => {
            const btn = document.getElementById('place-order-btn');
            btn.disabled = true;
            btn.textContent = 'Processing...';

            try {
                const addressId = document.querySelector('input[name="address_id"]:checked').value;
                const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

                const response = await fetch('api/place_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        address_id: addressId,
                        payment_method: paymentMethod,
                        items: cart
                    })
                });

                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    throw new Exception('Server returned invalid response: ' + text.substring(0, 100));
                }

                if (result.success) {
                    localStorage.removeItem('mirae_cart');
                    alert('Order placed successfully!');
                    window.location.href = 'orders.php';
                } else {
                    alert('Error: ' + result.message);
                    btn.disabled = false;
                    btn.textContent = 'Place Order';
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred while placing your order. Please check the console for details.');
                btn.disabled = false;
                btn.textContent = 'Place Order';
            }
        });

        renderSummary();
    </script>
    <script src="../js/main.js"></script>
</body>
</html>
