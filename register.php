<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: account.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName  = sanitize($_POST['last_name'] ?? '');
    $email     = sanitize($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    // Validation
    if (!$firstName || !$lastName) $errors[] = "Full name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
    if ($password !== $confirm) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        $db = getDB();
        // Check if email exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email already registered.";
        } else {
            // Create user
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $ins = $db->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, 'customer')");
            try {
                $ins->execute([$firstName, $lastName, $email, $hashed]);
                setFlash('success', 'Registration successful! You can now log in.');
                header('Location: login.php');
                exit;
            } catch (Exception $e) {
                $errors[] = "An error occurred. Please try again later.";
            }
        }
    }
}

$pageTitle = 'Create Account';
include 'includes/header.php';
?>

<div class="section flex-center" style="min-height: 80vh; background: radial-gradient(circle at top right, rgba(245, 158, 11, 0.05), transparent);">
    <div class="container" style="max-width: 480px;">
        <div class="glass-card" style="padding: 40px; border-radius: var(--radius-lg);">
            <div class="text-center mb-4">
                <h1 style="font-size: 1.8rem; margin-bottom: 8px;">Join <span class="gradient-text">Melody Masters</span></h1>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Create your account to start shopping.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" required value="<?= $firstName ?? '' ?>" placeholder="John">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" required value="<?= $lastName ?? '' ?>" placeholder="Doe">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required value="<?= $email ?? '' ?>" placeholder="john@example.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Min. 8 characters">
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat password">
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">Create Account</button>
            </form>

            <div class="text-center mt-4" style="font-size: 0.9rem; color: var(--text-muted);">
                Already have an account? <a href="login.php" style="font-weight: 600;">Sign In</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
