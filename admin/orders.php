<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('staff');

$db = getDB();
$flash = getFlash();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = sanitize($_POST['status']);
    $tracking = sanitize($_POST['tracking_number'] ?? '');
    
    $allowed = ['pending','processing','shipped','delivered','cancelled','refunded'];
    if (in_array($newStatus, $allowed)) {
        $upd = $db->prepare("UPDATE orders SET status = ?, tracking_number = ? WHERE id = ?");
        $upd->execute([$newStatus, $tracking, $orderId]);
        setFlash('success', "Order status updated to '$newStatus'.");
        header('Location: orders.php');
        exit;
    }
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$where = ['1=1'];
$params = [];
if ($statusFilter) { $where[] = 'o.status = ?'; $params[] = $statusFilter; }
if ($search) { $where[] = "(o.order_number LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)"; $params = array_merge($params, ["%$search%","%$search%","%$search%","%$search%"]); }

$whereSQL = implode(' AND ', $where);

$total = $db->prepare("SELECT COUNT(*) FROM orders o JOIN users u ON o.user_id=u.id WHERE $whereSQL");
$total->execute($params);
$totalOrders = (int)$total->fetchColumn();
$totalPages = max(1, ceil($totalOrders / $perPage));
$offset = ($page - 1) * $perPage;

$stmt = $db->prepare("
    SELECT o.*, u.first_name, u.last_name, u.email,
    (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE $whereSQL
    ORDER BY o.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

$pageTitle = 'Manage Orders';
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
        <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;">
            <div>
                <h1 style="font-size:1.6rem;margin-bottom:4px;">Orders</h1>
                <p style="color:var(--text-muted);font-size:.9rem;"><?= $totalOrders ?> total orders</p>
            </div>
        </header>

        <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div><?php endif; ?>

        <!-- Filter Bar -->
        <div class="card mb-4">
            <div class="card-body" style="padding:16px 20px;">
                <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                    <div class="navbar-search" style="flex:1;">
                        <span class="search-icon">🔍</span>
                        <input type="text" name="q" placeholder="Search by order #, customer name or email..." value="<?= sanitize($search) ?>" style="width:100%;">
                    </div>
                    <select name="status" class="form-control" style="width:auto;padding:8px 14px;" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <?php foreach (['pending','processing','shipped','delivered','cancelled','refunded'] as $s): ?>
                        <option value="<?= $s ?>" <?= $statusFilter===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary btn-sm">Filter</button>
                    <?php if ($search || $statusFilter): ?><a href="orders.php" class="btn btn-glass btn-sm">Reset</a><?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card">
            <div class="card-body" style="padding:0;">
                <?php if (empty($orders)): ?>
                    <div class="text-center" style="padding:60px;">
                        <h3>No orders found</h3>
                    </div>
                <?php else: ?>
                <div class="table-wrap" style="border:none;border-radius:0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                            <tr>
                                <td><strong style="font-family:monospace;"><?= $o['order_number'] ?></strong></td>
                                <td>
                                    <div style="font-weight:600;font-size:.85rem;"><?= sanitize($o['first_name'].' '.$o['last_name']) ?></div>
                                    <div style="font-size:.75rem;color:var(--text-muted);"><?= sanitize($o['email']) ?></div>
                                </td>
                                <td><?= $o['item_count'] ?></td>
                                <td style="font-weight:700;color:var(--primary);"><?= formatPrice($o['total']) ?></td>
                                <td>
                                    <?php
                                    $pc = match($o['status']){
                                        'delivered'=>'pill-success','shipped'=>'pill-info',
                                        'processing'=>'pill-purple','cancelled','refunded'=>'pill-danger',
                                        default=>'pill-warning'
                                    };
                                    ?>
                                    <span class="pill <?= $pc ?>"><?= ucfirst($o['status']) ?></span>
                                </td>
                                <td style="font-size:.82rem;"><?= date('j M Y', strtotime($o['created_at'])) ?></td>
                                <td>
                                    <button class="btn btn-glass btn-sm" onclick="toggleOrderEdit(<?= $o['id'] ?>)">Edit</button>
                                </td>
                            </tr>
                            <!-- Inline edit row -->
                            <tr id="order-edit-<?= $o['id'] ?>" style="display:none;">
                                <td colspan="7" style="background:var(--bg-card2);padding:16px;">
                                    <form method="POST" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                        <select name="status" class="form-control" style="width:auto;">
                                            <?php foreach (['pending','processing','shipped','delivered','cancelled','refunded'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="text" name="tracking_number" class="form-control" style="width:200px;" placeholder="Tracking number..." value="<?= sanitize($o['tracking_number'] ?? '') ?>">
                                        <button type="submit" name="update_status" class="btn btn-primary btn-sm">Save</button>
                                        <button type="button" class="btn btn-glass btn-sm" onclick="toggleOrderEdit(<?= $o['id'] ?>)">Cancel</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="orders.php?page=<?= $i ?>&status=<?= $statusFilter ?>&q=<?= urlencode($search) ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </main>

    <script>
    function toggleOrderEdit(id) {
        const row = document.getElementById('order-edit-' + id);
        row.style.display = row.style.display === 'none' ? '' : 'none';
    }
    </script>
</body>
</html>
