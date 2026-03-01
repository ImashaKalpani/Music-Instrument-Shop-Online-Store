<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('staff');

$db = getDB();

// Metrics
$totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $db->query("SELECT SUM(total) FROM orders WHERE payment_status = 'paid'")->fetchColumn() ?: 0;
$totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$lowStock = $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 3 AND product_type = 'physical'")->fetchColumn();

// Recent Orders
$recentOrders = $db->query("
    SELECT o.*, u.first_name, u.last_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetchAll();

// Top Products
$topProducts = $db->query("
    SELECT p.name, p.image, SUM(oi.quantity) as total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
")->fetchAll();

$pageTitle = 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?> | <?= SITE_NAME ?> Admin</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <style>
        body { background: var(--bg-dark); }
        .admin-sidebar { height: 100vh; position: sticky; top: 0; }
    </style>
</head>
<body class="admin-layout">

    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Admin Main Content -->
    <main class="admin-main">
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
            <div>
                <h1 style="font-size: 1.6rem; margin-bottom: 4px;">Dashboard</h1>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Welcome back, <?= sanitize($_SESSION['user_name']) ?>. Here's what's happening today.</p>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="grid-4 mb-5">
            <div class="stat-card">
                <div>
                    <div class="stat-value"><?= formatPrice($totalRevenue) ?></div>
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-change up">↑ 12% vs last month</div>
                </div>
            </div>
            <div class="stat-card">
                <div>
                    <div class="stat-value"><?= $totalOrders ?></div>
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-change up">↑ 5% vs last week</div>
                </div>
            </div>
            <div class="stat-card">
                <div>
                    <div class="stat-value"><?= $totalUsers ?></div>
                    <div class="stat-label">Customers</div>
                    <div class="stat-change up">↑ 8 new this week</div>
                </div>
            </div>
            <div class="stat-card">
                <div>
                    <div class="stat-value"><?= $lowStock ?></div>
                    <div class="stat-label">Low Stock Items</div>
                    <div class="stat-change" style="color: <?= $lowStock > 0 ? 'var(--danger)' : 'var(--text-muted)' ?>">Requires attention</div>
                </div>
            </div>
        </div>

        <div class="grid-2" style="grid-template-columns: 2fr 1fr; gap: 32px;">
            <!-- Recent Orders -->
            <div class="card">
                <div class="card-header flex-between">
                    <h3 style="font-size: 1rem; margin: 0;">Recent Orders</h3>
                    <a href="orders.php" class="btn btn-glass btn-sm">View All</a>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-wrap" style="border: none; border-radius: 0;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $ro): ?>
                                <tr>
                                    <td><span style="font-weight: 600; font-family: monospace;"><?= $ro['order_number'] ?></span></td>
                                    <td><?= sanitize($ro['first_name'] . ' ' . $ro['last_name']) ?></td>
                                    <td>
                                        <span class="pill <?= match($ro['status']) { 'delivered'=>'pill-success','shipped'=>'pill-info','cancelled'=>'pill-danger',default=>'pill-warning' } ?>">
                                            <?= ucfirst($ro['status']) ?>
                                        </span>
                                    </td>
                                    <td style="font-weight: 600; color: var(--primary);"><?= formatPrice($ro['total']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Best Sellers -->
            <div class="card">
                <div class="card-header"><h3 style="font-size: 1rem; margin: 0;">Popular Instruments</h3></div>
                <div class="card-body">
                    <?php if (empty($topProducts)): ?>
                        <p class="text-muted text-center" style="padding: 40px 0;">No sales data yet.</p>
                    <?php else: ?>
                        <?php foreach ($topProducts as $tp): ?>
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                            <div style="width: 48px; height: 48px; background: var(--bg-card2); border-radius: 8px; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                                <img src="<?= SITE_URL ?>/assets/images/products/<?= $tp['image'] ?>" onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.svg'" style="width: 100%; height: 100%; object-fit: contain; padding: 4px;">
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 0.88rem; font-weight: 600; color: var(--text-primary); margin-bottom: 2px;"><?= sanitize($tp['name']) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= $tp['total_sold'] ?> units sold</div>
                            </div>
                            <div style="width: 40px; height: 4px; background: var(--bg-card2); border-radius: 4px; overflow: hidden;">
                                <div style="width: <?= min(100, $tp['total_sold'] * 10) ?>%; height: 100%; background: var(--primary);"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
