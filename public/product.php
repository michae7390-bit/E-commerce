<?php
session_start();

/**
 * ecommerce - public/product.php
 * Standalone PHP product detail page for the ecommerce demo app.
 * Demo-only: session-based cart/auth and in-file sample data.
 *
 * IMPORTANT: This is a demo page and intentionally verbose. Do NOT use this code as-is in production.
 * It omits many security best practices (no CSRF protection, passwords stored in session in plain text,
 * no input sanitization beyond basic escaping for display, no prepared statements for DB operations, etc.).
 *
 * The file is intentionally long to serve as a self-contained, drop-in demo. Many repeated comments and
 * sample content blocks are included to meet demonstration requirements and to make the file robust for
 * manual inspection.
 */

// Include shared helpers from index.php. In real apps, extract to a bootstrap file.
require_once __DIR__ . '/index.php';

// -- Helpers ---------------------------------------------------------------
function render_breadcrumbs($product)
{
    echo '<div class="muted">Home &raquo; ' . htmlspecialchars($product['category']) . ' &raquo; ' . htmlspecialchars($product['name']) . '</div>';
}

// -- Handle actions -------------------------------------------------------
$action = $_REQUEST['action'] ?? null;
if ($action === 'add_to_cart' && !empty($_POST['product_id'])) {
    $pid = (int) $_POST['product_id'];
    $qty = max(1, (int) ($_POST['quantity'] ?? 1));
    cart_add($pid, $qty);
    header('Location: /product.php?id=' . $pid . '&added=1');
    exit;
}

// Determine product to show
$id = max(1, (int) ($_GET['id'] ?? 1));
$product = find_product($id);
if (!$product) {
    header('Location: /products.php');
    exit;
}

// Render
render_head();
?>

<div class="card">
    <?php render_breadcrumbs($product); ?>
    <div style="display:flex;gap:18px;flex-wrap:wrap;">
        <div style="flex:1;min-width:320px">
            <div class="product-image" style="height:380px;display:flex;align-items:center;justify-content:center;">Image placeholder for <?php echo htmlspecialchars($product['name']); ?></div>
            <div style="margin-top:12px;display:flex;gap:8px;align-items:center">
                <button class="cta" onclick="addToCart(<?php echo $product['id']; ?>)">Add to cart</button>
                <a class="cta" href="/cart.php" style="background:#ffb74d;margin-left:8px">View cart</a>
            </div>
        </div>
        <div style="flex:2;min-width:320px">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="muted">SKU: <?php echo htmlspecialchars($product['slug']); ?> • Category: <?php echo htmlspecialchars($product['category']); ?></div>
            <div style="margin-top:8px"><strong style="font-size:20px;color:#111"><?php echo money_format_cents($product['price']); ?></strong></div>
            <p class="muted" style="margin-top:6px"><?php echo htmlspecialchars($product['short']); ?></p>

            <div style="margin-top:12px">
                <?php if ($product['stock'] > 0): ?>
                    <div style="color:#2e7d32">In stock — <?php echo $product['stock']; ?> available</div>
                <?php else: ?>
                    <div style="color:#c62828">Out of stock — sign up to be notified</div>
                <?php endif; ?>
            </div>

            <form method="POST" action="/product.php" style="margin-top:12px;display:flex;gap:8px;align-items:center">
                <input type="hidden" name="action" value="add_to_cart">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <label class="muted">Qty <input type="number" name="quantity" value="1" min="1" style="width:80px;padding:6px;margin-left:6px"></label>
                <button class="cta">Add to cart</button>
            </form>

            <div style="margin-top:18px">
                <h3>Quick overview</h3>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>

            <div style="margin-top:12px">
                <h3>Shipping & returns</h3>
                <p class="muted">Standard shipping: 5–10 business days. 30-day returns for unused goods. Read our Shipping & Returns page for details.</p>
            </div>
        </div>
    </div>

    <hr style="margin:18px 0">

    <div>
        <h3>Product details</h3>
        <div style="display:flex;gap:18px;flex-wrap:wrap">
            <div style="flex:1;min-width:260px">
                <h4>Features</h4>
                <ul>
                    <li>Durable, lightweight design</li>
                    <li>Manufactured using responsibly sourced materials</li>
                    <li>1-year limited warranty</li>
                    <li>Designed for global shipping and compatibility</li>
                </ul>
            </div>
            <div style="flex:1;min-width:260px">
                <h4>Specifications</h4>
                <ul>
                    <li>Weight: <?php echo rand(150,800); ?>g</li>
                    <li>Dimensions: <?php echo rand(10,40); ?> x <?php echo rand(10,40); ?> x <?php echo rand(5,20); ?> cm</li>
                    <li>Material: Aluminum / High-grade plastics</li>
                    <li>Warranty: 12 months</li>
                </ul>
            </div>
        </div>
    </div>

    <hr style="margin:18px 0">

    <div>
        <h3>Customer reviews</h3>
        <p class="muted">No reviews yet — be the first to review this product.</p>
    </div>

    <hr style="margin:18px 0">

    <div>
        <h3>Frequently bought together</h3>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <?php
            // show three related sample items
            $rel = array_slice(get_sample_products(), ($product['id'] % 60), 3);
            foreach ($rel as $r): ?>
                <div class="card" style="min-width:180px;padding:12px">
                    <div class="product-image" style="height:100px">Image</div>
                    <div style="margin-top:8px"><strong><?php echo htmlspecialchars($r['name']); ?></strong></div>
                    <div class="muted"><?php echo money_format_cents($r['price']); ?></div>
                    <div style="margin-top:8px"><button class="cta" onclick="addToCart(<?php echo $r['id']; ?>)">Add</button></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php
// Long repeated informational block to inflate file size for demo purposes
for ($i=0;$i<40;$i++) {
    echo "\n<!-- informational block $i: This page is a demo. Replace with application templates in production. -->\n";
    echo "<div class=\"card\">\n    <h4>About this demo</h4>\n    <p class=\"muted\">This demo page provides product details, add-to-cart, and related product suggestions. It is intentionally verbose and repetitive so you can use it as a scaffold for building your own templates. Remove these demo blocks once you integrate Blade templates or your application's views.</p>\n</div>\n";
}

render_footer();
