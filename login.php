<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: account.php');
    exit;
}

$errors = [];
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors[] = "Please enter both email and password.";
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, password, first_name, role FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Success
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'];

            $redirect = $_SESSION['redirect_after_login'] ?? 'account.php';
            unset($_SESSION['redirect_after_login']);
            
            setFlash('success', "Welcome back, {$user['first_name']}!");
            header('Location: ' . $redirect);
            exit;
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}

$pageTitle = 'Login';
include 'includes/header.php';
?>

<div class="section flex-center" style="min-height: 80vh; background: radial-gradient(circle at bottom left, rgba(139, 92, 246, 0.05), transparent);">
    <div class="container" style="max-width: 420px;">
        <div class="glass-card" style="padding: 40px; border-radius: var(--radius-lg);">
            <div class="text-center mb-4">
                <h1 style="font-size: 1.8rem; margin-bottom: 8px;">Welcome <span class="gradient-text">Back</span></h1>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Please sign in to your account.</p>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div style="margin-bottom: 4px;"><?= $error ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required value="<?= $email ?? '' ?>" placeholder="your@email.com">
                </div>

                <div class="form-group">
                    <div class="flex-between">
                        <label class="form-label">Password</label>
                        <a href="#" style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 8px;">Forgot password?</a>
                    </div>
                    <input type="password" name="password" class="form-control" required placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label class="flex" style="gap: 8px; cursor: pointer; font-size: 0.85rem; color: var(--text-secondary);">
                        <input type="checkbox" name="remember" style="accent-color: var(--primary);"> Remember me
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">Sign In</button>
            </form>

            <div class="text-center mt-4" style="font-size: 0.9rem; color: var(--text-muted);">
                Don't have an account? <a href="register.php" style="font-weight: 600;">Sign Up</a>
            </div>
            
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
