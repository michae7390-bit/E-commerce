<?php
session_start();

/**
 * ecommerce - public/checkout.php
 * Standalone mock checkout page for the ecommerce demo app.
 * Demo-only: session-based cart and mock payment simulation.
 *
 * IMPORTANT: This file is intentionally simplistic for demonstration purposes.
 * - No real payment integration
 * - No CSRF protections
 * - No server-side validation beyond basic checks
 * - Do NOT use as-is in production
 */

require_once __DIR__ . '/index.php';

// If cart empty, redirect to cart
if (empty($_SESSION['cart'])) {
    header('Location: /cart.php');
    exit;
}

// Calculate totals
$items = cart_items();
$subtotal = cart_total_cents();
$shipping = ($subtotal >= 5000) ? 0 : 799;
$tax = (int) round($subtotal * 0.07);
$total = $subtotal + $shipping + $tax;

$errors = [];
$processing = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'place_order') {
    // Minimal validation
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');

    if ($name === '') $errors[] = 'Please enter your full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if ($address === '') $errors[] = 'Please enter a shipping address.';
    if ($city === '') $errors[] = 'Please enter a city.';
    if ($country === '') $errors[] = 'Please enter a country.';

    // Simulate payment details (card number optional on demo)
    $card = preg_replace('/\D/', '', ($_POST['card'] ?? ''));
    if ($card !== '' && strlen($card) < 12) $errors[] = 'Card number looks too short for demo processing.';

    if (empty($errors)) {
        // Simulate processing time
        $processing = true;

        // Create a demo order record in session
        $orderId = strtoupper(substr(md5(uniqid('', true)), 0, 10));
        $order = [
            'id' => $orderId,
            'created_at' => date('c'),
            'name' => $name,
            'email' => $email,
            'address' => $address,
            'city' => $city,
            'country' => $country,
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $total,
            'status' => 'processing',
        ];

        if (!isset($_SESSION['orders'])) $_SESSION['orders'] = [];
        $_SESSION['orders'][$orderId] = $order;

        // Clear cart
        unset($_SESSION['cart']);

        // If user logged in, attach order to their session profile
        if (!empty($_SESSION['user']['email'])) {
            $uemail = $_SESSION['user']['email'];
            if (!isset($_SESSION['user_orders'])) $_SESSION['user_orders'] = [];
            $_SESSION['user_orders'][$uemail][] = $orderId;
        }

        // Redirect to success page
        header('Location: /order-success.php?order=' . urlencode($orderId));
        exit;
    }
}

render_head();
?>

<div class="card">
    <h2>Checkout</h2>

    <?php if ($processing): ?>
        <div class="muted">Processing your order... this is a demo simulation.</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div style="background:#ffebee;border:1px solid #ffcdd2;padding:12px;border-radius:8px;color:#b71c1c;margin-bottom:12px">
            <strong>There were problems with your submission:</strong>
            <ul>
                <?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/checkout.php">
        <input type="hidden" name="action" value="place_order">
        <div style="display:flex;gap:18px;flex-wrap:wrap">
            <div style="flex:1;min-width:320px">
                <h3>Shipping details</h3>
                <label>Full name<br><input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ($_SESSION['user']['name'] ?? '')); ?>" style="width:100%;padding:8px;margin-top:6px"></label>
                <label>Email<br><input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ($_SESSION['user']['email'] ?? '')); ?>" style="width:100%;padding:8px;margin-top:6px"></label>
                <label>Address<br><input type="text" name="address" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>" style="width:100%;padding:8px;margin-top:6px"></label>
                <label>City<br><input type="text" name="city" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" style="width:100%;padding:8px;margin-top:6px"></label>
                <label>Country<br><input type="text" name="country" value="<?php echo htmlspecialchars($_POST['country'] ?? ''); ?>" style="width:100%;padding:8px;margin-top:6px"></label>

                <h3 style="margin-top:12px">Payment (demo)</h3>
                <p class="muted">This demo accepts any card number for simulation. Do not enter real card data.</p>
                <label>Card number<br><input type="text" name="card" value="" style="width:100%;padding:8px;margin-top:6px"></label>
                <div style="display:flex;gap:8px;margin-top:8px">
                    <input type="text" name="exp" placeholder="MM/YY" style="padding:8px;width:120px">
                    <input type="text" name="cvc" placeholder="CVC" style="padding:8px;width:120px">
                </div>

                <div style="margin-top:12px">
                    <button class="cta">Place order</button>
                </div>
            </div>

            <div style="flex:1;min-width:320px">
                <h3>Order summary</h3>
                <div style="border:1px solid #eee;padding:12px;border-radius:8px">
                    <?php foreach ($items as $it): $p = $it['product']; ?>
                        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px dashed #f0f0f0">
                            <div>
                                <div style="font-weight:600"><?php echo htmlspecialchars($p['name']); ?></div>
                                <div class="muted"><?php echo $it['quantity']; ?> × <?php echo money_format_cents($p['price']); ?></div>
                            </div>
                            <div><?php echo money_format_cents($it['subtotal']); ?></div>
                        </div>
                    <?php endforeach; ?>

                    <div style="padding-top:8px">
                        <div style="display:flex;justify-content:space-between"><div>Subtotal</div><div><?php echo money_format_cents($subtotal); ?></div></div>
                        <div style="display:flex;justify-content:space-between"><div>Shipping</div><div><?php echo $shipping === 0 ? 'Free' : money_format_cents($shipping); ?></div></div>
                        <div style="display:flex;justify-content:space-between"><div>Tax</div><div><?php echo money_format_cents($tax); ?></div></div>
                        <hr>
                        <div style="display:flex;justify-content:space-between;font-weight:700">Total<div><?php echo money_format_cents($total); ?></div></div>
                    </div>
                </div>

                <div style="margin-top:12px;font-size:13px;color:#666">By placing your order you agree to our <a href="/terms.php">Terms</a> and <a href="/privacy.php">Privacy Policy</a>.</div>
            </div>
        </div>
    </form>
</div>

<?php
// Inflate with long demo note blocks
for ($i=0;$i<120;$i++) {
    echo "\n<!-- Checkout demo block $i: Replace with secure payment gateway integration in production. -->\n";
}

render_footer();
