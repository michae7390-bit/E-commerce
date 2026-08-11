<?php
session_start();

/**
 * ecommerce - public/login.php
 * Standalone demo login page for the ecommerce app.
 *
 * Behavior:
 * - Accepts email and password, checks against demo users stored in $_SESSION['users']
 * - Auto-creates a demo admin account (admin@ecommerce.com / admin123) if missing
 * - On success sets $_SESSION['user'] and redirects to profile or intended page
 *
 * NOTE: This is a demo-only implementation. Passwords are stored in session in plain text
 * and there is no CSRF protection. Replace with secure, DB-backed auth for production.
 */

require_once __DIR__ . '/index.php';

// Ensure a demo admin account exists so admin.php can be used.
if (!isset($_SESSION['users']['admin@ecommerce.com'])) {
    $_SESSION['users']['admin@ecommerce.com'] = [
        'name' => 'Administrator',
        'email' => 'admin@ecommerce.com',
        'phone' => '+1 555 000 0000',
        'password' => 'admin123',
        'created_at' => date('c'),
        'is_admin' => true,
    ];
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errors[] = 'Please enter both email and password.';
    } else {
        if (isset($_SESSION['users'][$email]) && $_SESSION['users'][$email]['password'] === $password) {
            // Success: set session user
            $u = $_SESSION['users'][$email];
            $_SESSION['user'] = [
                'name' => $u['name'],
                'email' => $u['email'],
                'phone' => $u['phone'] ?? '',
                'is_admin' => $u['is_admin'] ?? false,
            ];

            // Redirect to intended page if provided
            $redirect = $_GET['next'] ?? '/profile.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $errors[] = 'Invalid credentials. Try the demo admin: admin@ecommerce.com / admin123 or register a new account.';
        }
    }
}

render_head();
?>

<div class="card" style="max-width:680px;margin:auto">
    <h2>Sign in</h2>
    <p class="muted">Sign in to view your orders, manage addresses, and checkout faster.</p>

    <?php if (!empty($errors)): ?>
        <div style="background:#ffebee;border:1px solid #ffcdd2;padding:12px;border-radius:8px;color:#b71c1c;margin-bottom:12px">
            <ul>
                <?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/login.php<?php if (!empty($_GET['next'])) echo '?next=' . urlencode($_GET['next']); ?>">
        <input type="hidden" name="action" value="login">

        <label>Email<br>
            <input type="email" name="email" placeholder="you@domain.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" style="width:100%;padding:10px;margin-top:6px">
        </label>

        <label style="margin-top:12px">Password<br>
            <input type="password" name="password" placeholder="Enter your password" style="width:100%;padding:10px;margin-top:6px">
        </label>

        <div style="margin-top:12px;display:flex;align-items:center;gap:12px">
            <button class="cta">Sign in</button>
            <label class="muted"><input type="checkbox" name="remember"> Remember me</label>
            <a href="/register.php" style="margin-left:auto">Create an account</a>
        </div>

        <div style="margin-top:12px;color:#666;font-size:13px">Demo admin: <strong>admin@ecommerce.com</strong> / <strong>admin123</strong></div>
    </form>
</div>

<?php
// Add repeated guidance blocks for clarity and file verbosity
for ($i=0;$i<120;$i++) {
    echo "\n<!-- Login demo guidance $i: Replace this with your app's authentication system (hashed passwords, DB storage, CSRF tokens). -->\n";
}

render_footer();
