<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('staff');

$db = getDB();
$flash = getFlash();

// Approval logic removed - all reviews are auto-approved.

// ---- DELETE ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    $revId = (int)$_POST['review_id'];
    $db->prepare("DELETE FROM reviews WHERE id = ?")->execute([$revId]);
    setFlash('success', 'Review deleted successfully.');
    header('Location: reviews.php');
    exit;
}

// Fetch reviews
$reviews = $db->query("
    SELECT r.*, p.name as product_name, p.image as product_image, u.first_name, u.last_name, u.email 
    FROM reviews r 
    JOIN products p ON r.product_id = p.id 
    JOIN users u ON r.user_id = u.id 
    ORDER BY r.created_at DESC
")->fetchAll();

$pageTitle = 'Manage Reviews';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?> | <?= SITE_NAME ?> Admin</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body class="admin-layout">

    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="admin-main">
        <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div><?php endif; ?>

        <header style="margin-bottom:32px;">
            <h1 style="font-size:1.6rem;margin-bottom:4px;">Customer Reviews</h1>
            <p style="color:var(--text-muted);font-size:.9rem;"><?= count($reviews) ?> total reviews</p>
        </header>

        <div class="card">
            <div class="card-body" style="padding:0;">
                <div class="table-wrap" style="border:none;border-radius:0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Customer</th>
                                <th>Review</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reviews)): ?>
                            <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted);">No reviews found.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($reviews as $r): ?>
                            <tr>
                                <td style="width:200px;">
                                    <div style="display:flex;gap:10px;align-items:center;">
                                        <img src="<?= SITE_URL ?>/assets/images/products/<?= $r['product_image'] ?>" onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.svg'" style="width:32px;height:32px;object-fit:contain;background:var(--bg-card2);padding:2px;border-radius:4px;">
                                        <div style="font-size:.8rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:150px;"><?= sanitize($r['product_name']) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size:.85rem;font-weight:600;"><?= sanitize($r['first_name'] . ' ' . $r['last_name']) ?></div>
                                    <div style="font-size:.72rem;color:var(--text-muted);"><?= sanitize($r['email']) ?></div>
                                </td>
                                <td style="max-width:300px;">
                                    <div style="font-weight:600;font-size:.82rem;margin-bottom:4px;"><?= sanitize($r['title'] ?: 'No Title') ?></div>
                                    <div style="font-size:.78rem;color:var(--text-secondary);line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?= sanitize($r['body']) ?></div>
                                    <div style="font-size:.65rem;color:var(--text-muted);margin-top:6px;"><?= date('j M Y, H:i', strtotime($r['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div style="display:flex;gap:2px;color:var(--primary);font-size:12px;">
                                        <?php for($i=1;$i<=5;$i++) echo $i<=$r['rating'] ? '★' : '<span style="color:var(--border);">★</span>'; ?>
                                    </div>
                                </td>
                                <!-- Status column removed -->
                                <td>
                                    <div style="display:flex;gap:6px;">
                                        <form method="POST" onsubmit="return confirm('Delete this review?');">
                                            <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
                                            <button type="submit" name="delete_review" class="btn btn-danger btn-sm" title="Delete Review" style="background:#ef4444; border:none; padding:8px 12px; border-radius:6px; color:#fff; cursor:pointer;">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
