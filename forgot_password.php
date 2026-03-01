<?php
require_once __DIR__ . '/includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    
    if (!$email) {
        $error = "Please enter your email address.";
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $token = generateToken();
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $upd = $db->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $upd->execute([$token, $expires, $email]);
            
            // In a real app, send email. Here we show the link for the assignment.
            $resetLink = SITE_URL . "/reset_password.php?token=" . $token;
            $success = "A reset link has been generated. <br><br><a href='$resetLink' class='btn btn-primary btn-sm mt-2'>Reset Password Link (Demo)</a>";
        } else {
            // Don't reveal if email doesn't exist for security, but for assignment we might
            $error = "No account found with that email address.";
        }
    }
}

$pageTitle = 'Forgot Password';
include 'includes/header.php';
?>

<div class="section flex-center" style="min-height: 70vh;">
    <div class="container" style="max-width: 460px;">
        <div class="glass-card" style="padding: 40px;">
            <div class="text-center mb-4">
                <h1 style="font-size: 1.6rem; margin-bottom: 8px;">Forgot <span class="gradient-text">Password</span></h1>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Enter your email and we'll send you a link to reset your password.</p>
            </div>

            <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-info"><?= $success ?></div><?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required placeholder="your@email.com" value="<?= sanitize($_POST['email'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">Send Reset Link</button>
            </form>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="login.php" style="font-size: 0.9rem; font-weight: 600;">← Back to Login</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
