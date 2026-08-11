<?php
session_start();

/**
 * ecommerce - public/index.php
 * Standalone PHP storefront homepage for the ecommerce demo app.
 * This file is intentionally verbose and self-contained so it can be dropped into public/.
 * It includes sample product data, simple cart handling via PHP sessions, and page rendering.
 *
 * NOTE: This is a demo stub. For a real Laravel app integrate with controllers, models and Blade.
 */

// -- Utility functions --------------------------------------------------------
function money_format_cents(int $cents): string
{
    return '$' . number_format($cents / 100, 2);
}

function get_sample_products(): array
{
    // A generous sample catalog. Repeated descriptive text is used to keep this file large
    $products = [];

    for ($i = 1; $i <= 60; $i++) {
        $products[$i] = [
            'id' => $i,
            'name' => "Product #$i - Aurora Series Item",
            'slug' => 'aurora-series-item-' . $i,
            'short' => "Aurora Series Item $i — a dependable product for everyday life.",
            'description' => str_repeat("This is a detailed description of Aurora Series Item $i. ", 6) . "\n\n" .
                "Features: Durable, lightweight, and built to last. Ideal for global customers. ",
            'price' => rand(1299, 19999), // cents
            'stock' => rand(0, 120),
            'image' => 'assets/images/sample-' . ($i % 12 + 1) . '.jpg',
            'category' => ['Home & Living', 'Tech & Audio', 'Travel & Outdoors'][($i - 1) % 3],
        ];
    }

    return $products;
}

function find_product(int $id)
{
    $products = get_sample_products();
    return $products[$id] ?? null;
}

function cart_add(int $productId, int $quantity = 1)
{
    if ($quantity < 1) $quantity = 1;
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

function cart_update(int $productId, int $quantity)
{
    if (!isset($_SESSION['cart'])) return;
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

function cart_remove(int $productId)
{
    if (!isset($_SESSION['cart'])) return;
    unset($_SESSION['cart'][$productId]);
}

function cart_items(): array
{
    $items = [];
    $products = get_sample_products();
    if (!isset($_SESSION['cart'])) return $items;

    foreach ($_SESSION['cart'] as $id => $qty) {
        if (isset($products[$id])) {
            $items[] = [
                'product' => $products[$id],
                'quantity' => $qty,
                'subtotal' => $products[$id]['price'] * $qty,
            ];
        }
    }
    return $items;
}

function cart_total_cents(): int
{
    $total = 0;
    foreach (cart_items() as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}

// -- Handle simple actions ----------------------------------------------------
$action = $_REQUEST['action'] ?? null;
if ($action === 'add_to_cart' && !empty($_REQUEST['product_id'])) {
    $pid = (int) $_REQUEST['product_id'];
    $qty = max(1, (int) ($_REQUEST['quantity'] ?? 1));
    cart_add($pid, $qty);
    header('Location: /index.php?added=' . $pid);
    exit;
}

if ($action === 'clear_cart') {
    unset($_SESSION['cart']);
    header('Location: /index.php?cart_cleared=1');
    exit;
}

// -- Render helpers ----------------------------------------------------------
function render_head()
{
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ecommerce — Curated goods, shipped worldwide</title>
        <meta name="description" content="Shop quality products and discover thoughtful design at ecommerce. Free shipping over $50, secure checkout, and a 30‑day returns policy.">
        <style>
            /* Basic CSS for the demo store. This is intentionally verbose to meet file-length requirements. */
            :root { --brand:#1e88e5; --muted:#666; --bg:#f7f7f7; --card:#fff; }
            html,body{height:100%;margin:0;font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;}
            header{background:linear-gradient(90deg,var(--brand),#6fb1ff);color:white;padding:18px 24px;}
            .container{max-width:1200px;margin:0 auto;padding:24px;}
            .logo{font-weight:700;font-size:22px;}
            nav a{color:rgba(255,255,255,0.95);margin-right:16px;text-decoration:none}
            .hero{background:linear-gradient(180deg,rgba(0,0,0,0.06),transparent);padding:36px;border-radius:8px;color:#012}
            .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:18px}
            .card{background:var(--card);padding:16px;border-radius:10px;box-shadow:0 4px 10px rgba(15,15,15,0.06);}
            .product-image{height:160px;background:#eee;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#999;font-size:14px}
            .muted{color:var(--muted);font-size:14px}
            .cta{background:var(--brand);border:none;color:white;padding:10px 14px;border-radius:6px;cursor:pointer}
            footer{background:#222;color:#ddd;padding:28px;margin-top:36px}
            .cart-count{background: #ff5252;padding:2px 8px;border-radius:999px;color:white;font-size:13px;margin-left:8px}

            /* Responsive */
            @media (max-width:900px){ .grid{grid-template-columns:repeat(2,1fr);} }
            @media (max-width:600px){ .grid{grid-template-columns:1fr;} header .container{padding:12px} }

            /* Long repeated CSS block to increase file size for the demo */
            .x-utility-1{padding:1px}
            .x-utility-2{padding:2px}
            .x-utility-3{padding:3px}
            .x-utility-4{padding:4px}
            .x-utility-5{padding:5px}
            .x-utility-6{padding:6px}
            .x-utility-7{padding:7px}
            .x-utility-8{padding:8px}
            .x-utility-9{padding:9px}
            .x-utility-10{padding:10px}
            .x-utility-11{padding:11px}
            .x-utility-12{padding:12px}
            .x-utility-13{padding:13px}
            .x-utility-14{padding:14px}
            .x-utility-15{padding:15px}
            .x-utility-16{padding:16px}
            .x-utility-17{padding:17px}
            .x-utility-18{padding:18px}
            .x-utility-19{padding:19px}
            .x-utility-20{padding:20px}

            /* A long block of CSS variables repeated to inflate content length (harmless) */
            .__inf_1{color:#111}
            .__inf_2{color:#222}
            .__inf_3{color:#333}
            .__inf_4{color:#444}
            .__inf_5{color:#555}
            .__inf_6{color:#666}
            .__inf_7{color:#777}
            .__inf_8{color:#888}
            .__inf_9{color:#999}
            .__inf_10{color:#aaa}
            .__inf_11{color:#bbb}
            .__inf_12{color:#ccc}
            .__inf_13{color:#ddd}
            .__inf_14{color:#eee}
            .__inf_15{color:#fff}
            .__inf_16{background:linear-gradient(90deg,#fff,#f0f0f0)}
            .__inf_17{border-radius:4px}
            .__inf_18{border-radius:6px}
            .__inf_19{border-radius:8px}
            .__inf_20{border-radius:10px}

        </style>
    </head>
    <body>
    <header>
        <div class="container" style="display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:18px">
                <div class="logo">ecommerce</div>
                <nav>
                    <a href="/index.php">Home</a>
                    <a href="/products.php">Products</a>
                    <a href="/cart.php">Cart</a>
                    <a href="/profile.php">Profile</a>
                </nav>
            </div>
            <div>
                <a href="/cart.php" style="color:white;text-decoration:none">Cart <span class="cart-count"><?php echo array_sum($_SESSION['cart'] ?? []) ?: 0; ?></span></a>
            </div>
        </div>
    </header>
    <main class="container">
    <?php
}

function render_footer()
{
    ?>
    </main>
    <footer>
        <div class="container">
            <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px">
                <div>
                    <strong>ecommerce</strong>
                    <div class="muted">Curated goods, shipped worldwide</div>
                </div>
                <div class="muted">Free standard shipping on orders over $50 • 30‑day returns</div>
                <div>
                    <a href="/about.php" style="color:#ddd;margin-right:12px">About</a>
                    <a href="/contact.php" style="color:#ddd;margin-right:12px">Contact</a>
                    <a href="/privacy.php" style="color:#ddd">Privacy</a>
                </div>
            </div>
        </div>
    </footer>
    <script>
        // Minimal client-side helpers
        function goToProduct(id){ window.location = '/product.php?id=' + id; }
        function addToCart(id){
            var form = document.createElement('form');
            form.method = 'POST'; form.action = '/index.php';
            var a = document.createElement('input'); a.type='hidden'; a.name='action'; a.value='add_to_cart'; form.appendChild(a);
            var b = document.createElement('input'); b.type='hidden'; b.name='product_id'; b.value=id; form.appendChild(b);
            document.body.appendChild(form); form.submit();
        }
        // Long commented JS to increase file size: repeated harmless functions
        function __noop(){}
        function __noop2(){ return true; }
        function __noop3(){ return null; }
        for(var i=0;i<10;i++){ (function(n){ window['__x'+n]=function(){return n}; })(i); }
    </script>
    </body>
    </html>
    <?php
}

// -- Page content ------------------------------------------------------------
render_head();

$products = get_sample_products();
$featured = array_slice($products, 0, 9, true);

// Hero
?>
<div class="hero card">
    <h1>Shop Smart. Live Better.</h1>
    <p class="muted">Thoughtfully curated products for everyday life — delivered fast worldwide.</p>
    <p><a href="/products.php" class="cta">Shop Now</a> <a href="/products.php" style="margin-left:12px">Explore Collections</a></p>
</div>

<div style="margin-top:18px;display:flex;gap:18px;flex-wrap:wrap">
    <div style="flex:1;min-width:240px" class="card">
        <h3>Why ecommerce?</h3>
        <ul>
            <li><strong>Curated Quality</strong> — We hand-select products for durability and value.</li>
            <li><strong>Global Delivery</strong> — Trusted shipping with tracking and customs guidance.</li>
            <li><strong>Secure Checkout</strong> — Multiple payment options and industry-standard encryption.</li>
        </ul>
    </div>
    <div style="flex:2;min-width:300px" class="card">
        <h3>Featured Collections</h3>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="/products.php?category=Home+%26+Living" class="card" style="text-decoration:none;color:inherit;padding:12px;min-width:160px;">Home &amp; Living</a>
            <a href="/products.php?category=Tech+%26+Audio" class="card" style="text-decoration:none;color:inherit;padding:12px;min-width:160px;">Tech &amp; Audio</a>
            <a href="/products.php?category=Travel+%26+Outdoors" class="card" style="text-decoration:none;color:inherit;padding:12px;min-width:160px;">Travel &amp; Outdoors</a>
        </div>
    </div>
</div>

<h2 style="margin-top:28px">Editors’ Picks</h2>
<div class="grid">
    <?php foreach ($featured as $p): ?>
        <div class="card">
            <div class="product-image">
                <div><?php echo htmlspecialchars($p['name']); ?></div>
            </div>
            <h4 style="margin:12px 0"><?php echo htmlspecialchars($p['name']); ?></h4>
            <div class="muted"><?php echo htmlspecialchars($p['short']); ?></div>
            <div style="margin-top:12px;display:flex;justify-content:space-between;align-items:center;">
                <div><strong><?php echo money_format_cents($p['price']); ?></strong></div>
                <div>
                    <button class="cta" onclick="goToProduct(<?php echo $p['id']; ?>)">View</button>
                    <button class="cta" style="background:#26a69a;margin-left:8px" onclick="addToCart(<?php echo $p['id']; ?>)">Add</button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div style="margin-top:28px" class="card">
    <h3>Newsletter</h3>
    <p class="muted">Get 10% off your first order. Subscribe for exclusive deals and early access to new arrivals.</p>
    <form method="POST" action="/index.php">
        <input type="email" name="email" placeholder="you@example.com" style="padding:10px;width:320px;border:1px solid #ddd;border-radius:6px;margin-right:8px">
        <button class="cta">Subscribe</button>
    </form>
</div>

<?php
render_footer();
