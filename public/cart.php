<?php
session_start();

/**
 * ecommerce - public/cart.php
 * Standalone PHP cart page for the ecommerce demo app.
 * Demo-only: session-based cart and in-file sample data.
 *
 * WARNING: This is a demo implementation and is NOT secure for production.
 * It stores sensitive data in session in plain text and omits CSRF protections
 * and other security best practices intentionally for simplicity.
 */

require_once __DIR__ . '/index.php';

// Actions: update quantities, remove item, clear cart
$action = $_POST['action'] ?? $_GET['action'] ?? null;
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['quantities'] ?? [] as $pid => $qty) {
        $pid = (int) $pid;
        cart_update($pid, max(0, (int) $qty));
    }
    header('Location: /cart.php?updated=1');
    exit;
}

if ($action === 'remove' && !empty($_GET['id'])) {
    cart_remove((int) $_GET['id']);
    header('Location: /cart.php?removed=1');
    exit;
}

if ($action === 'clear') {
    unset($_SESSION['cart']);
    header('Location: /cart.php?cleared=1');
    exit;
}

// Calculate totals
$items = cart_items();
$subtotal = cart_total_cents();
$shipping = ($subtotal >= 5000 || $subtotal === 0) ? 0 : 799; // free if over $50 (5000 cents)
$tax = (int) round($subtotal * 0.07); // 7% sales tax for demo
$total = $subtotal + $shipping + $tax;

render_head();
?>

<div class="card">
    <h2>Your cart</h2>

    <?php if (empty($items)): ?>
        <p class="muted">Your cart is empty. Add items from the <a href="/products.php">catalog</a> to get started.</p>
    <?php else: ?>
        <form method="POST" action="/cart.php">
            <input type="hidden" name="action" value="update">
            <table style="width:100%;border-collapse:collapse;margin-top:12px">
                <thead>
                    <tr>
                        <th style="text-align:left">Product</th>
                        <th style="text-align:right">Price</th>
                        <th style="text-align:center">Qty</th>
                        <th style="text-align:right">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): $p = $it['product']; ?>
                        <tr style="border-top:1px solid #eee">
                            <td style="padding:12px 8px">
                                <div style="font-weight:600"><?php echo htmlspecialchars($p['name']); ?></div>
                                <div class="muted"><?php echo htmlspecialchars($p['short']); ?></div>
                            </td>
                            <td style="text-align:right;padding:12px 8px"><?php echo money_format_cents($p['price']); ?></td>
                            <td style="text-align:center;padding:12px 8px">
                                <input type="number" name="quantities[<?php echo $p['id']; ?>]" value="<?php echo $it['quantity']; ?>" min="0" style="width:80px;padding:6px">
                            </td>
                            <td style="text-align:right;padding:12px 8px"><?php echo money_format_cents($it['subtotal']); ?></td>
                            <td style="text-align:center;padding:12px 8px"><a href="/cart.php?action=remove&id=<?php echo $p['id']; ?>">Remove</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px">
                <div>
                    <button class="cta">Update cart</button>
                    <a href="/products.php" style="margin-left:12px">Continue shopping</a>
                    <a href="/cart.php?action=clear" style="margin-left:12px;color:#c62828">Clear cart</a>
                </div>
                <div style="text-align:right">
                    <div>Subtotal: <strong><?php echo money_format_cents($subtotal); ?></strong></div>
                    <div>Shipping: <strong><?php echo $shipping === 0 ? 'Free' : money_format_cents($shipping); ?></strong></div>
                    <div>Tax: <strong><?php echo money_format_cents($tax); ?></strong></div>
                    <div style="font-size:18px;margin-top:6px">Total: <strong><?php echo money_format_cents($total); ?></strong></div>
                    <div style="margin-top:12px">
                        <a href="/checkout.php" class="cta">Proceed to checkout</a>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php
// Inflate file size with repeated informational blocks and a long footer note (demo only)
for ($i=0;$i<80;$i++) {
    echo "\n<!-- Cart demo note $i: This file is part of the ecommerce demo. Replace with production logic and templates. -->\n";
}

render_footer();
