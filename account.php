<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$db = getDB();
$user = getCurrentUser();
$flash = getFlash();

$activeTab = $_GET['tab'] ?? 'profile';

// Fetch orders
$orderStmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orderStmt->execute([$user['id']]);
$orders = $orderStmt->fetchAll();

// Fetch digital downloads (available from paid orders)
$dlStmt = $db->prepare("
    SELECT oi.*, dp.file_name, dp.file_size, dp.file_format, o.order_number, o.created_at as order_date
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN digital_products dp ON oi.product_id = dp.product_id
    WHERE o.user_id = ? AND o.payment_status = 'paid' AND oi.product_type = 'digital'
    ORDER BY o.created_at DESC
");
$dlStmt->execute([$user['id']]);
$downloads = $dlStmt->fetchAll();

// Handle profile update
$profileErrors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $first = sanitize($_POST['first_name'] ?? '');
    $last  = sanitize($_POST['last_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $addr1 = sanitize($_POST['address_line1'] ?? '');
    $city  = sanitize($_POST['city'] ?? '');
    $post  = sanitize($_POST['postcode'] ?? '');

    if (!$first || !$last) {
        $profileErrors[] = "Name fields are required.";
    } else {
        $upd = $db->prepare("UPDATE users SET first_name=?, last_name=?, phone=?, address_line1=?, city=?, postcode=? WHERE id=?");
        $upd->execute([$first, $last, $phone, $addr1, $city, $post, $user['id']]);
        setFlash('success', 'Profile updated successfully.');
        header('Location: account.php?tab=profile');
        exit;
    }
}

$pageTitle = 'My Account';
include 'includes/header.php';
?>

<div class="page-banner" style="padding: 40px 0;">
    <div class="container">
        <h1>My Account</h1>
        <p>Manage your orders, profile, and digital downloads.</p>
        <div class="breadcrumb" style="justify-content: center;">
            <a href="index.php">Home</a>
            <span>›</span>
            <span class="current">Account</span>
        </div>
    </div>
</div>

<div class="section">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 280px 1fr; gap: 40px; align-items: start;">
            
            <!-- Account Sidebar -->
            <aside>
                <div class="card">
                    <div class="card-body account-nav" style="padding: 12px;">
                        <div style="padding: 20px 16px; border-bottom: 1px solid var(--border); margin-bottom: 12px;">
                            <div style="font-weight: 700; color: var(--text-primary);"><?= sanitize($user['first_name'] . ' ' . $user['last_name']) ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?= sanitize($user['email']) ?></div>
                            <div class="pill pill-purple mt-1" style="font-size: 0.65rem;"><?= ucfirst($user['role']) ?></div>
                        </div>

                        <a href="?tab=profile" class="nav-item <?= $activeTab === 'profile' ? 'active' : '' ?>">Profile Information</a>
                        <a href="?tab=orders" class="nav-item <?= $activeTab === 'orders' ? 'active' : '' ?>">Order History</a>
                        <a href="?tab=downloads" class="nav-item <?= $activeTab === 'downloads' ? 'active' : '' ?>">Digital Downloads</a>
                        <hr class="divider">
                        <a href="logout.php" class="nav-item" style="color: var(--danger);">Logout</a>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div>
                <!-- Tab: Profile -->
                <?php if ($activeTab === 'profile'): ?>
                <div class="card">
                    <div class="card-header"><h2 style="font-size: 1.1rem; margin: 0;">Profile Details</h2></div>
                    <div class="card-body">
                        <?php if (!empty($profileErrors)): ?>
                            <div class="alert alert-danger"><?= implode('<br>', $profileErrors) ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="grid-2">
                                <div class="form-group">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="first_name" class="form-control" value="<?= sanitize($user['first_name']) ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name" class="form-control" value="<?= sanitize($user['last_name']) ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Address (Cannot be changed)</label>
                                <input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" disabled style="opacity: 0.6;">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?= sanitize($user['phone'] ?? '') ?>" placeholder="+44 ...">
                            </div>
                            
                            <hr class="divider">
                            <h3 style="font-size: 0.95rem; margin-bottom: 16px; color: var(--text-secondary);">Mailing Address</h3>
                            
                            <div class="form-group">
                                <label class="form-label">Address Line 1</label>
                                <input type="text" name="address_line1" class="form-control" value="<?= sanitize($user['address_line1'] ?? '') ?>">
                            </div>
                            <div class="grid-2">
                                <div class="form-group">
                                    <label class="form-label">City</label>
                                    <input type="text" name="city" class="form-control" value="<?= sanitize($user['city'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Postcode</label>
                                    <input type="text" name="postcode" class="form-control" value="<?= sanitize($user['postcode'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tab: Orders -->
                <?php if ($activeTab === 'orders'): ?>
                <div class="card">
                    <div class="card-header"><h2 style="font-size: 1.1rem; margin: 0;">My Orders</h2></div>
                    <div class="card-body" style="padding: 0;">
                        <?php if (empty($orders)): ?>
                            <div style="padding: 60px; text-align: center;">
                                <div style="font-size: 50px; opacity: 0.2; margin-bottom: 16px;">📦</div>
                                <h3>No orders found</h3>
                                <p>You haven't placed any orders yet.</p>
                                <a href="shop.php" class="btn btn-outline btn-sm mt-3">Start Shopping</a>
                            </div>
                        <?php else: ?>
                            <div class="table-wrap" style="border: none; border-radius: 0;">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $o): ?>
                                        <tr>
                                            <td><span style="font-family: monospace; font-weight: 600;"><?= $o['order_number'] ?></span></td>
                                            <td><?= date('j M Y', strtotime($o['created_at'])) ?></td>
                                            <td style="font-weight: 600; color: var(--primary);"><?= formatPrice($o['total']) ?></td>
                                            <td>
                                                <?php
                                                $pClass = match($o['status']) {
                                                    'delivered' => 'pill-success',
                                                    'processing','shipped' => 'pill-info',
                                                    'cancelled','refunded' => 'pill-danger',
                                                    default => 'pill-warning'
                                                };
                                                ?>
                                                <span class="pill <?= $pClass ?>"><?= ucfirst($o['status']) ?></span>
                                            </td>
                                            <td>
                                                <a href="order_confirmation.php?order=<?= $o['id'] ?>" class="btn btn-glass btn-sm">View Details</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tab: Downloads -->
                <?php if ($activeTab === 'downloads'): ?>
                <div class="card">
                    <div class="card-header"><h2 style="font-size: 1.1rem; margin: 0;">Digital Downloads</h2></div>
                    <div class="card-body">
                        <?php if (empty($downloads)): ?>
                            <div style="padding: 60px; text-align: center;">
                                <div style="font-size: 50px; opacity: 0.2; margin-bottom: 16px;">📄</div>
                                <h3>No downloads available</h3>
                                <p>Digital products will appear here after purchase.</p>
                                <a href="shop.php?category=digital-sheet-music" class="btn btn-outline btn-sm mt-3">Browse Sheet Music</a>
                            </div>
                        <?php else: ?>
                            <div class="grid-2">
                                <?php foreach ($downloads as $dl): ?>
                                <div class="glass-card" style="padding: 20px; display: flex; align-items: center; gap: 16px;">
                                    <div style="font-size: 32px;">📄</div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 700; font-size: 0.95rem; margin-bottom: 2px; color: var(--text-primary);"><?= sanitize($dl['product_name']) ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 10px;">
                                            <?= $dl['file_format'] ?> &bull; <?= $dl['file_size'] ?> &bull; Purchased <?= date('j M Y', strtotime($dl['order_date'])) ?>
                                        </div>
                                        <a href="download.php?token=<?= $dl['download_token'] ?>" class="btn btn-primary btn-sm btn-block" style="justify-content: center;">Download Now</a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
