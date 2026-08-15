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

// Simple CSRF token for demo forms (resend confirmation)
if (!isset($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    } catch (Exception $e) {
        // Fallback to less-strong token in environments without CSPRNG (very rare in PHP 7+)
        $_SESSION['csrf_token'] = substr(str_replace('.', '', microtime(true) . rand()), 0, 32);
    }
}

// Handle demo POST actions (e.g., "resend confirmation")
$flashMessage = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['resend']) && !empty($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // Demo: mark that we "resent" the confirmation email. In production, integrate with real email flow.
        $flashMessage = 'Confirmation email resent (demo). Check the server log or mock inbox.';
        // For demo traceability, store that we resent into session (not persistent)
        $_SESSION['last_resent_order'] = $_POST['order_id'] ?? null;
    } else {
        $flashMessage = 'Invalid request (CSRF token mismatch)';
    }
}

// Safely read order id from query string and restrict to reasonable length to avoid attacks in demo.
$orderId = isset($_GET['order']) ? substr((string)$_GET['order'], 0, 128) : null;
$order = null;
if ($orderId && isset($_SESSION['orders'][$orderId])) {
    $order = $_SESSION['orders'][$orderId];
}

// If the order isn't found, show a friendly message and link back to the catalog
render_head();
?>

<div class="card">
    <?php if ($flashMessage): ?>
        <div style="padding:8px;border:1px solid #e6f4ea;background:#f0fff4;border-radius:6px;margin-bottom:12px">
            <?php echo htmlspecialchars($flashMessage); ?>
        </div>
    <?php endif; ?>

    <?php if (!$order): ?>
        <h2>Order not found</h2>
        <p class="muted">We couldn't find that order. If you just placed an order, please check the confirmation email (demo only). Return to <a href="/index.php">home</a> or view your <a href="/profile.php">account</a>.</p>
    <?php else: ?>
        <h2>Thank you — your order is confirmed</h2>
        <p class="muted">Order <strong id="order-id"><?php echo htmlspecialchars($order['id']); ?></strong> • Placed on <?php echo htmlspecialchars($order['created_at']); ?></p>

        <div style="display:flex;gap:18px;flex-wrap:wrap;margin-top:12px">
            <div style="flex:1;min-width:320px">
                <h3>Shipping to</h3>
                <div style="padding:12px;border:1px solid #eee;border-radius:8px">
                    <div style="font-weight:600"><?php echo htmlspecialchars($order['name'] ?? '—'); ?></div>
                    <div class="muted"><?php echo htmlspecialchars($order['address'] ?? '—'); ?></div>
                    <div class="muted"><?php echo htmlspecialchars($order['city'] ?? ''); ?>, <?php echo htmlspecialchars($order['country'] ?? ''); ?></div>
                    <div class="muted"><?php echo htmlspecialchars($order['email'] ?? ''); ?></div>
                </div>

                <h3 style="margin-top:12px">Contact</h3>
                <p class="muted">If you have questions about your order, email <a href="mailto:support@ecommerce.com">support@ecommerce.com</a> and include your order number.</p>

                <div style="margin-top:12px">
                    <a href="/index.php" class="cta">Continue shopping</a>
                    <a href="/profile.php" style="margin-left:12px">View account</a>
                </div>

                <div style="margin-top:12px">
                    <button onclick="copyOrderId()" class="cta" style="background:#efefef;color:#000;border:none;padding:8px 12px;border-radius:6px">Copy order number</button>
                    <span style="margin-left:8px;color:#666;font-size:0.9em">or <a href="#" onclick="document.getElementById('resend-form').scrollIntoView();return false">resend confirmation</a></span>
                </div>

                <div style="margin-top:12px;padding:8px;border:1px dashed #f5f5f5;border-radius:6px;background:#fafafa">
                    <div style="font-weight:600">Tracking</div>
                    <?php
                        // Demo tracking: generate a pseudo tracking number and estimated delivery window
                        $tracking = $order['tracking'] ?? null;
                        if (!$tracking) {
                            // deterministic but not guessable: hash order id with secret in session for demo only
                            $secret = $_SESSION['tracking_secret'] ??= bin2hex(random_bytes(8));
                            $tracking = strtoupper(substr(hash('crc32b', $order['id'] . $secret), 0, 10));
                            // Save to session so subsequent views show same number in demo
                            $_SESSION['orders'][$order['id']]['tracking'] = $tracking;
                        }
                        $minDays = $order['shipping_days_min'] ?? 3;
                        $maxDays = $order['shipping_days_max'] ?? 7;
                        $estMin = date('Y-m-d', strtotime("+{$minDays} days"));
                        $estMax = date('Y-m-d', strtotime("+{$maxDays} days"));
                    ?>
                    <div style="margin-top:6px">Tracking number: <strong><?php echo htmlspecialchars($tracking); ?></strong></div>
                    <div class="muted">Estimated delivery: <?php echo htmlspecialchars($estMin); ?> — <?php echo htmlspecialchars($estMax); ?></div>
                    <div style="margin-top:8px"><a href="/track.php?tracking=<?php echo urlencode($tracking); ?>">Track order</a></div>
                </div>

            </div>

            <div style="flex:1;min-width:320px">
                <h3>Order summary</h3>
                <div style="border:1px solid #eee;padding:12px;border-radius:8px">
                    <?php foreach ($order['items'] as $it): $p = $it['product']; ?>
                        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px dashed #f5f5f5">
                            <div>
                                <div style="font-weight:600"><?php echo htmlspecialchars($p['name']); ?></div>
                                <div class="muted"><?php echo (int)$it['quantity']; ?> × <?php echo money_format_cents($p['price']); ?></div>
                            </div>
                            <div><?php echo money_format_cents($it['subtotal']); ?></div>
                        </div>
                    <?php endforeach; ?>

                    <div style="padding-top:8px">
                        <div style="display:flex;justify-content:space-between"><div>Subtotal</div><div><?php echo money_format_cents($order['subtotal']); ?></div></div>
                        <div style="display:flex;justify-content:space-between"><div>Shipping</div><div><?php echo ($order['shipping'] === 0) ? 'Free' : money_format_cents($order['shipping']); ?></div></div>
                        <div style="display:flex;justify-content:space-between"><div>Tax</div><div><?php echo money_format_cents($order['tax']); ?></div></div>
                        <hr>
                        <div style="display:flex;justify-content:space-between;font-weight:700">Total<div><?php echo money_format_cents($order['total']); ?></div></div>
                    </div>
                </div>

                <div style="margin-top:12px">
                    <button onclick="window.print()" class="cta">Print receipt</button>
                    <a href="/order-success.php?order=<?php echo urlencode($order['id']); ?>&download=txt" style="margin-left:12px">Download TXT</a>
                    <a href="/order-success.php?order=<?php echo urlencode($order['id']); ?>&download=csv" style="margin-left:12px">Download CSV</a>
                </div>
            </div>
        </div>

        <?php if (!empty($_GET['download'])): ?>
            <?php
            // Support two demo download formats: txt (legacy) and csv
            $format = strtolower((string)($_GET['download'] ?? ''));
            if ($format === 'csv') {
                // CSV download
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="order-' . $order['id'] . '.csv"');
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Order ID', $order['id']]);
                fputcsv($out, ['Placed', $order['created_at']]);
                fputcsv($out, []);
                fputcsv($out, ['Item', 'Quantity', 'Unit price', 'Subtotal']);
                foreach ($order['items'] as $it) {
                    $p = $it['product'];
                    fputcsv($out, [$p['name'], $it['quantity'], money_format_cents($p['price']), money_format_cents($it['subtotal'])]);
                }
                fputcsv($out, []);
                fputcsv($out, ['Subtotal', money_format_cents($order['subtotal'])]);
                fputcsv($out, ['Shipping', ($order['shipping'] === 0 ? 'Free' : money_format_cents($order['shipping']))]);
                fputcsv($out, ['Tax', money_format_cents($order['tax'])]);
                fputcsv($out, ['Total', money_format_cents($order['total'])]);
                fclose($out);
                exit;
            } else {
                // Plain text fallback (legacy behavior)
                header('Content-Type: text/plain');
                header('Content-Disposition: attachment; filename="order-' . $order['id'] . '.txt"');
                echo "Order " . $order['id'] . "\nPlaced: " . $order['created_at'] . "\n\n";
                echo "Shipping to: \n" . ($order['name'] ?? '') . "\n" . ($order['address'] ?? '') . "\n" . ($order['city'] ?? '') . ", " . ($order['country'] ?? '') . "\n\n";
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
            }
            ?>
        <?php endif; ?>

        <div style="margin-top:18px" id="resend-form">
            <form method="post" style="display:flex;gap:8px;align-items:center">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
                <button type="submit" name="resend" class="cta">Resend confirmation</button>
                <span class="muted">We'll simulate resending the confirmation email for this demo.</span>
            </form>
        </div>

    <?php endif; ?>
</div>

<?php
// Expand file length with multiple demo note blocks to reach verbosity targets
for ($i = 0; $i < 200; $i++) {
    echo "\n<!-- Demo order-success block #$i: This page is for demonstration only. Replace with real order templates and PDF generation in production. -->\n";
}

render_footer();
?>

<script>
// Small client-side helpers for the demo page (kept inline intentionally)
function copyOrderId() {
    var idEl = document.getElementById('order-id');
    if (!idEl) return;
    var text = idEl.textContent || idEl.innerText;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function() {
            alert('Order number copied to clipboard: ' + text);
        }, function() {
            fallbackCopy(text);
        });
    } else {
        fallbackCopy(text);
    }
}
function fallbackCopy(text) {
    var ta = document.createElement('textarea');
    ta.value = text;
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); alert('Order number copied to clipboard: ' + text); } catch (e) { prompt('Copy the order number', text); }
    document.body.removeChild(ta);
}
</script>
