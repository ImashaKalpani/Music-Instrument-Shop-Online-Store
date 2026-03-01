<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$db = getDB();
$flash = getFlash();

// Update user role or status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_role'])) {
        $uid = (int)$_POST['user_id'];
        $role = sanitize($_POST['role']);
        $allowed = ['customer','staff','admin'];
        if (in_array($role, $allowed) && $uid != getCurrentUser()['id']) {
            $db->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$role, $uid]);
            setFlash('success', 'User role updated.');
        }
        header('Location: users.php'); exit;
    }
    if (isset($_POST['toggle_active'])) {
        $uid = (int)$_POST['user_id'];
        if ($uid != getCurrentUser()['id']) {
            $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?")->execute([$uid]);
            setFlash('success', 'User status toggled.');
        }
        header('Location: users.php'); exit;
    }
}

$search = trim($_GET['q'] ?? '');
$roleFilter = $_GET['role'] ?? '';

$where = ['1=1']; $params = [];
if ($search) { $where[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)"; $params = array_merge($params, ["%$search%","%$search%","%$search%"]); }
if ($roleFilter) { $where[] = "role = ?"; $params[] = $roleFilter; }

$whereSQL = implode(' AND ', $where);
$users = $db->prepare("SELECT *, (SELECT COUNT(*) FROM orders WHERE user_id = users.id) as order_count FROM users WHERE $whereSQL ORDER BY created_at DESC");
$users->execute($params);
$userList = $users->fetchAll();

$pageTitle = 'Manage Users';
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
                <h1 style="font-size:1.6rem;margin-bottom:4px;">Users</h1>
                <p style="color:var(--text-muted);font-size:.9rem;"><?= count($userList) ?> users</p>
            </div>
        </header>
        <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div><?php endif; ?>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body" style="padding:14px 20px;">
                <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                    <div class="navbar-search" style="flex:1;">
                        <span class="search-icon">🔍</span>
                        <input type="text" name="q" placeholder="Search by name or email..." value="<?= sanitize($search) ?>" style="width:100%;">
                    </div>
                    <select name="role" class="form-control" style="width:auto;" onchange="this.form.submit()">
                        <option value="">All Roles</option>
                        <option value="customer" <?= $roleFilter==='customer'?'selected':'' ?>>Customer</option>
                        <option value="staff" <?= $roleFilter==='staff'?'selected':'' ?>>Staff</option>
                        <option value="admin" <?= $roleFilter==='admin'?'selected':'' ?>>Admin</option>
                    </select>
                    <button class="btn btn-primary btn-sm">Search</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="padding:0;">
                <div class="table-wrap" style="border:none;border-radius:0;">
                    <table>
                        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Orders</th><th>Joined</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($userList as $u): ?>
                            <tr>
                                <td style="font-weight:600;"><?= sanitize($u['first_name'].' '.$u['last_name']) ?></td>
                                <td style="font-size:.82rem;"><?= sanitize($u['email']) ?></td>
                                <td>
                                    <?php $rc = match($u['role']){ 'admin'=>'pill-danger','staff'=>'pill-purple',default=>'pill-info' }; ?>
                                    <span class="pill <?= $rc ?>"><?= ucfirst($u['role']) ?></span>
                                </td>
                                <td><?= $u['order_count'] ?></td>
                                <td style="font-size:.82rem;"><?= date('j M Y', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <span class="pill <?= $u['is_active'] ? 'pill-success' : 'pill-danger' ?>"><?= $u['is_active'] ? 'Active' : 'Disabled' ?></span>
                                </td>
                                <td>
                                    <?php if ($u['id'] != getCurrentUser()['id']): ?>
                                    <div style="display:flex;gap:6px;">
                                        <form method="POST" style="display:flex;gap:6px;">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <select name="role" class="form-control" style="width:auto;padding:4px 8px;font-size:.78rem;">
                                                <?php foreach (['customer','staff','admin'] as $r): ?>
                                                <option value="<?= $r ?>" <?= $u['role']===$r?'selected':'' ?>><?= ucfirst($r) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="update_role" class="btn btn-glass btn-sm">Set</button>
                                        </form>
                                        <form method="POST">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" name="toggle_active" class="btn <?= $u['is_active']?'btn-danger':'btn-success' ?> btn-sm"><?= $u['is_active']?'Disable':'Enable' ?></button>
                                        </form>
                                    </div>
                                    <?php else: ?>
                                    <span style="font-size:.75rem;color:var(--text-muted);">You</span>
                                    <?php endif; ?>
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
