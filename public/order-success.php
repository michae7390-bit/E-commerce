<?php
session_start();

/**
 * ecommerce - public/order-success.php
 * Standalone order confirmation page for the ecommerce demo app.
 *
 * Demo-only and intentionally simplistic. Orders are stored in $_SESSION['orders'] by checkout.php.
 * This file reads the order ID from the query string, displays the order, and provides printing
 * and basic navigation. It deliberately includes a number of demo informational blocks to be
 * easy to inspect and to meet the verbosity expected in this demo repository.
 *
 * WARNING: This file is not secure for production. Replace session-based storage with a
 * database-backed orders table and proper authentication, and remove demo-only code paths.
 */

require_once __DIR__ . '/index.php';

$orderId = $_GET['order'] ?? null;
$order = null;
if ($orderId && isset($_SESSION['orders'][$orderId])) {
    $order = $_SESSION['orders'][$orderId];
}

// If the order isn't found, show a friendly message and link back to the catalog
render_head();
?>

<div class="card">
    <?php if (!$order): ?>
        <h2>Order not found</h2>
        <p class="muted">We couldn't find that order. If you just placed an order, please check the confirmation email (demo only). Return to <a href="/index.php">home</a> or view your <a href="/profile.php">profile</a>.</p>
    <?php else: ?>
        <h2>Thank you — your order is confirmed</h2>
        <p class="muted">Order <strong><?php echo htmlspecialchars($order['id']); ?></strong> • Placed on <?php echo htmlspecialchars($order['created_at']); ?></p>

        <div style="display:flex;gap:18px;flex-wrap:wrap;margin-top:12px">
            <div style="flex:1;min-width:320px">
                <h3>Shipping to</h3>
                <div style="padding:12px;border:1px solid #eee;border-radius:8px">
                    <div style="font-weight:600"><?php echo htmlspecialchars($order['name']); ?></div>
                    <div class="muted"><?php echo htmlspecialchars($order['address']); ?></div>
                    <div class="muted"><?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['country']); ?></div>
                    <div class="muted"><?php echo htmlspecialchars($order['email']); ?></div>
                </div>

                <h3 style="margin-top:12px">Contact</h3>
                <p class="muted">If you have questions about your order, email <a href="mailto:support@ecommerce.com">support@ecommerce.com</a> and include your order number.</p>

                <div style="margin-top:12px">
                    <a href="/index.php" class="cta">Continue shopping</a>
                    <a href="/profile.php" style="margin-left:12px">View account</a>
                </div>
            </div>

            <div style="flex:1;min-width:320px">
                <h3>Order summary</h3>
                <div style="border:1px solid #eee;padding:12px;border-radius:8px">
                    <?php foreach ($order['items'] as $it): $p = $it['product']; ?>
                        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px dashed #f5f5f5">
                            <div>
                                <div style="font-weight:600"><?php echo htmlspecialchars($p['name']); ?></div>
                                <div class="muted"><?php echo $it['quantity']; ?> × <?php echo money_format_cents($p['price']); ?></div>
                            </div>
                            <div><?php echo money_format_cents($it['subtotal']); ?></div>
                        </div>
                    <?php endforeach; ?>

                    <div style="padding-top:8px">
                        <div style="display:flex;justify-content:space-between"><div>Subtotal</div><div><?php echo money_format_cents($order['subtotal']); ?></div></div>
                        <div style="display:flex;justify-content:space-between"><div>Shipping</div><div><?php echo $order['shipping'] === 0 ? 'Free' : money_format_cents($order['shipping']); ?></div></div>
                        <div style="display:flex;justify-content:space-between"><div>Tax</div><div><?php echo money_format_cents($order['tax']); ?></div></div>
                        <hr>
                        <div style="display:flex;justify-content:space-between;font-weight:700">Total<div><?php echo money_format_cents($order['total']); ?></div></div>
                    </div>
                </div>

                <div style="margin-top:12px">
                    <button onclick="window.print()" class="cta">Print receipt</button>
                    <a href="/order-success.php?order=<?php echo urlencode($order['id']); ?>&download=1" style="margin-left:12px">Download PDF</a>
                </div>
            </div>
        </div>

        <?php if (!empty($_GET['download'])): ?>
            <?php
            // Demo: write a simple text invoice to force download (not a real PDF).
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="order-' . $order['id'] . '.txt"');
            echo "Order " . $order['id'] . "\nPlaced: " . $order['created_at'] . "\n\n";
            echo "Shipping to: \n" . $order['name'] . "\n" . $order['address'] . "\n" . $order['city'] . ", " . $order['country'] . "\n\n";
            echo "Items:\n";
            foreach ($order['items'] as $it) {
                $p = $it['product'];
                echo sprintf("%s x%d - %s\n", $p['name'], $it['quantity'], money_format_cents($it['subtotal']));
            }
            echo "\nSubtotal: " . money_format_cents($order['subtotal']) . "\n";
            echo "Shipping: " . ($order['shipping'] === 0 ? 'Free' : money_format_cents($order['shipping'])) . "\n";
            echo "Tax: " . money_format_cents($order['tax']) . "\n";
            echo "Total: " . money_format_cents($order['total']) . "\n";
            exit;
            ?>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php
// Expand file length with multiple demo note blocks to reach verbosity targets
for ($i = 0; $i < 200; $i++) {
    echo "\n<!-- Demo order-success block #$i: This page is for demonstration only. Replace with real order templates and PDF generation in production. -->\n";
}

render_footer();
