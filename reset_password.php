<?php
require_once __DIR__ . '/includes/functions.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = false;

if (!$token) {
    header('Location: login.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW() AND is_active = 1");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    $error = "This password reset link is invalid or has expired.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $pass = $_POST['password'] ?? '';
    $conf = $_POST['confirm_password'] ?? '';
    
    if (strlen($pass) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($pass !== $conf) {
        $error = "Passwords do not match.";
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $upd = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $upd->execute([$hash, $user['id']]);
        $success = true;
    }
}

$pageTitle = 'Reset Password';
include 'includes/header.php';
?>

<div class="section flex-center" style="min-height: 70vh;">
    <div class="container" style="max-width: 460px;">
        <div class="glass-card" style="padding: 40px;">
            <div class="text-center mb-4">
                <h1 style="font-size: 1.6rem; margin-bottom: 8px;">Reset <span class="gradient-text">Password</span></h1>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Please enter your new password below.</p>
            </div>

            <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="text-align:center; flex-direction:column; padding:32px;">
                    <div style="font-size:2rem; margin-bottom:12px;">✅</div>
                    <p>Password updated successfully!</p>
                    <a href="login.php" class="btn btn-primary btn-sm mt-3">Sign In Now</a>
                </div>
            <?php elseif (!$error): ?>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="••••••••" minlength="6">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required placeholder="••••••••" minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">Update Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
