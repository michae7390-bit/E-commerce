<?php
session_start();

/**
 * ecommerce - public/products.php
 * Standalone PHP product listing page for the ecommerce demo app.
 * Demo-only: session-based cart/auth and in-file sample data.
 */

require_once __DIR__ . '/index.php';
// Note: index.php defines helper functions like get_sample_products(), money_format_cents(), etc.
// We purposely include index.php for shared helpers in this demo environment. In a proper app
// you would extract shared code into an include or bootstrap file.

// Handle quick actions: add to cart via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $pid = (int) ($_POST['product_id'] ?? 0);
    $qty = max(1, (int) ($_POST['quantity'] ?? 1));
    cart_add($pid, $qty);
    header('Location: /products.php?added=' . $pid);
    exit;
}

// Filtering & Pagination
$all = get_sample_products();
$category = trim($_GET['category'] ?? '');
$search = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;

$filtered = array_values(array_filter($all, function($p) use ($category, $search) {
    if ($category && strcasecmp($p['category'], $category) !== 0) return false;
    if ($search) {
        $q = strtolower($search);
        return (strpos(strtolower($p['name']), $q) !== false) || (strpos(strtolower($p['short']), $q) !== false);
    }
    return true;
}));

$total = count($filtered);
$pages = max(1, (int) ceil($total / $perPage));
$offset = ($page - 1) * $perPage;
$items = array_slice($filtered, $offset, $perPage);

// Render
render_head();
?>

<div class="card">
    <h2>Products</h2>
    <form method="GET" action="/products.php" style="display:flex;gap:8px;margin-bottom:12px;">
        <input type="text" name="q" placeholder="Search products" value="<?php echo htmlspecialchars($search); ?>" style="padding:8px;border:1px solid #ddd;border-radius:6px;flex:1">
        <select name="category" style="padding:8px;border:1px solid #ddd;border-radius:6px">
            <option value="">All categories</option>
            <option value="Home &amp; Living" <?php if($category==='Home & Living') echo 'selected'; ?>>Home &amp; Living</option>
            <option value="Tech &amp; Audio" <?php if($category==='Tech & Audio') echo 'selected'; ?>>Tech &amp; Audio</option>
            <option value="Travel &amp; Outdoors" <?php if($category==='Travel & Outdoors') echo 'selected'; ?>>Travel &amp; Outdoors</option>
        </select>
        <button class="cta">Filter</button>
    </form>

    <div class="grid">
        <?php foreach ($items as $p): ?>
            <div class="card">
                <div class="product-image">Image</div>
                <h4><?php echo htmlspecialchars($p['name']); ?></h4>
                <div class="muted"><?php echo htmlspecialchars($p['short']); ?></div>
                <div style="margin-top:8px"><strong><?php echo money_format_cents($p['price']); ?></strong></div>
                <div style="margin-top:8px;display:flex;gap:8px;">
                    <form method="POST" action="/products.php" style="display:inline">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                        <input type="number" name="quantity" value="1" min="1" style="width:60px;padding:6px">
                        <button class="cta" style="margin-left:6px">Add to cart</button>
                    </form>
                    <button class="cta" onclick="goToProduct(<?php echo $p['id']; ?>)">View</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="margin-top:18px;display:flex;justify-content:space-between;align-items:center">
        <div class="muted">Showing <?php echo $offset + 1; ?>–<?php echo min($offset + $perPage, $total); ?> of <?php echo $total; ?> products</div>
        <div>
            <?php for($i=1;$i<=$pages;$i++): ?>
                <a href="/products.php?page=<?php echo $i; ?>&category=<?php echo urlencode($category); ?>&q=<?php echo urlencode($search); ?>" style="margin-right:6px;padding:6px 8px;border-radius:6px;background:<?php echo $i===$page ? '#ddd' : '#fff'; ?>;text-decoration:none;color:#222"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    </div>
</div>

<?php
render_footer();
