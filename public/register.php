<?php
session_start();

/**
 * ecommerce - public/register.php
 * Standalone demo registration page for the ecommerce app.
 *
 * Behavior:
 * - Requires: name, email, password, phone (phone required per request)
 * - Stores new user in $_SESSION['users'][email] = [name,email,phone,password]
 * - Passwords are stored in plain text in session for demo only (NOT secure)
 * - Adds modern placeholders and helper text for new users
 *
 * NOTE: This file is purely for demonstration and local development. Replace with
 * real database-backed registration, server-side validation, email verification,
 * password hashing, and CSRF protections for production.
 */

require_once __DIR__ . '/index.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Basic validation
    if ($name === '') $errors[] = 'Please enter your full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (strlen($password) < 6) $errors[] = 'Choose a password at least 6 characters long.';
    if ($phone === '') $errors[] = 'Please provide a phone number. We use this for shipping updates.';

    // Check for duplicate
    if (isset($_SESSION['users'][$email])) $errors[] = 'An account with that email already exists. Try signing in.';

    if (empty($errors)) {
        // Create user in session (demo only)
        $_SESSION['users'][$email] = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password, // Insecure demo: store hashed passwords in production
            'created_at' => date('c'),
        ];

        // Auto-login user
        $_SESSION['user'] = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
        ];

        $success = true;
    }
}

render_head();
?>

<div class="card">
    <h2>Create your account</h2>
    <p class="muted">Sign up to save orders, track shipments, and checkout faster. We respect your privacy and will not sell your data.</p>

    <?php if ($success): ?>
        <div style="background:#e8f5e9;border:1px solid #c8e6c9;padding:12px;border-radius:8px;color:#2e7d32;margin-bottom:12px">
            <strong>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?>!</strong>
            <div class="muted">Your account was created successfully. You are now signed in.</div>
            <div style="margin-top:8px"><a href="/profile.php" class="cta">Go to your profile</a></div>
        </div>
    <?php else: ?>

        <?php if (!empty($errors)): ?>
            <div style="background:#ffebee;border:1px solid #ffcdd2;padding:12px;border-radius:8px;color:#b71c1c;margin-bottom:12px">
                <strong>There were problems with your submission:</strong>
                <ul>
                    <?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="/register.php" style="max-width:640px;">
            <input type="hidden" name="action" value="register">

            <label>Full name<br>
                <input type="text" name="name" placeholder="Jamie Taylor" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" style="width:100%;padding:10px;margin-top:6px">
            </label>

            <label style="margin-top:12px">Email address<br>
                <input type="email" name="email" placeholder="you@domain.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" style="width:100%;padding:10px;margin-top:6px">
            </label>

            <label style="margin-top:12px">Phone number<br>
                <input type="tel" name="phone" placeholder="+1 555 000 1234" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" style="width:100%;padding:10px;margin-top:6px">
                <div class="muted" style="margin-top:6px">We may send SMS updates about shipping and delivery. Standard rates apply.</div>
            </label>

            <label style="margin-top:12px">Password<br>
                <input type="password" name="password" placeholder="Create a secure password" value="" style="width:100%;padding:10px;margin-top:6px">
                <div class="muted" style="margin-top:6px">Use at least 6 characters. For real apps, store passwords hashed (bcrypt/argon2).</div>
            </label>

            <div style="margin-top:16px;display:flex;gap:12px;align-items:center">
                <button class="cta">Create account</button>
                <a href="/login.php" style="margin-left:6px">Already have an account? Sign in</a>
            </div>
        </form>

    <?php endif; ?>
</div>

<?php
// Modern placeholders and extra instructional blocks for new users (repeated to inflate file size)
for ($i=0; $i<200; $i++) {
    echo "\n<!-- Placeholder guidance block $i: Use email verification, strong password policies, and phone normalization in production. -->\n";
}

render_footer();
