<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$db = getDB();
$flash = getFlash();

// Create / Update Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    $name = sanitize($_POST['name']);
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($name)));
    $desc = sanitize($_POST['description'] ?? '');
    $parentId = $_POST['parent_id'] ? (int)$_POST['parent_id'] : null;
    $icon = sanitize($_POST['icon'] ?? '🎵');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $editId = (int)($_POST['category_id'] ?? 0);

    if ($editId > 0) {
        $db->prepare("UPDATE categories SET name=?, slug=?, description=?, parent_id=?, icon=?, sort_order=? WHERE id=?")
            ->execute([$name, $slug, $desc, $parentId, $icon, $sortOrder, $editId]);
        setFlash('success', "Category '$name' updated.");
    } else {
        $db->prepare("INSERT INTO categories (name, slug, description, parent_id, icon, sort_order) VALUES (?,?,?,?,?,?)")
            ->execute([$name, $slug, $desc, $parentId, $icon, $sortOrder]);
        setFlash('success', "Category '$name' created.");
    }
    header('Location: categories.php');
    exit;
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $delId = (int)$_POST['category_id'];
    // Check if products exist
    $count = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND is_active = 1");
    $count->execute([$delId]);
    if ($count->fetchColumn() > 0) {
        setFlash('danger', 'Cannot delete category with active products.');
    } else {
        $db->prepare("UPDATE categories SET is_active = 0 WHERE id = ?")->execute([$delId]);
        setFlash('success', 'Category deleted.');
    }
    header('Location: categories.php');
    exit;
}

$categories = $db->query("
    SELECT c.*, pc.name as parent_name, COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN categories pc ON c.parent_id = pc.id
    LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
    WHERE c.is_active = 1
    GROUP BY c.id
    ORDER BY c.parent_id IS NULL DESC, c.sort_order, c.name
")->fetchAll();

$parentCats = array_filter($categories, fn($c) => $c['parent_id'] === null);

$pageTitle = 'Manage Categories';
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
                <h1 style="font-size:1.6rem;margin-bottom:4px;">Categories</h1>
                <p style="color:var(--text-muted);font-size:.9rem;"><?= count($categories) ?> categories</p>
            </div>
        </header>

        <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div><?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 360px;gap:32px;align-items:start;">
            <!-- Categories List -->
            <div class="card">
                <div class="card-body" style="padding:0;">
                    <div class="table-wrap" style="border:none;border-radius:0;">
                        <table>
                            <thead><tr><th>Icon</th><th>Name</th><th>Parent</th><th>Products</th><th>Order</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($categories as $c): ?>
                                <tr>
                                    <td style="font-size:20px;"><?= $c['icon'] ?></td>
                                    <td>
                                        <span style="font-weight:600;"><?= $c['parent_id'] ? '↳ ' : '' ?><?= sanitize($c['name']) ?></span>
                                        <div style="font-size:.72rem;color:var(--text-muted);">slug: <?= $c['slug'] ?></div>
                                    </td>
                                    <td><?= $c['parent_name'] ? sanitize($c['parent_name']) : '<span style="color:var(--text-muted)">—</span>' ?></td>
                                    <td><span class="pill pill-info"><?= $c['product_count'] ?></span></td>
                                    <td><?= $c['sort_order'] ?></td>
                                    <td>
                                        <div style="display:flex;gap:6px;">
                                            <button class="btn btn-glass btn-sm" onclick="editCategory(<?= htmlspecialchars(json_encode($c)) ?>)">Edit</button>
                                            <form method="POST" onsubmit="return confirm('Delete this category?');">
                                                <input type="hidden" name="category_id" value="<?= $c['id'] ?>">
                                                <button type="submit" name="delete_category" class="btn btn-danger btn-sm" style="background:#ef4444; border:none; color:#fff; cursor:pointer; padding:6px 12px; border-radius:6px;">Delete</button>
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

            <!-- Create / Edit Form -->
            <div class="card" style="position:sticky;top:32px;">
                <div class="card-header"><h3 style="font-size:1rem;margin:0;" id="formTitle">Add Category</h3></div>
                <div class="card-body">
                    <form method="POST" id="catForm">
                        <input type="hidden" name="category_id" id="catId" value="0">
                        <div class="form-group">
                            <label class="form-label">Category Name *</label>
                            <input type="text" name="name" id="catName" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Parent Category</label>
                            <select name="parent_id" id="catParent" class="form-control">
                                <option value="">None (Top-Level)</option>
                                <?php foreach ($parentCats as $pc): ?>
                                <option value="<?= $pc['id'] ?>"><?= sanitize($pc['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="grid-2">
                            <div class="form-group">
                                <label class="form-label">Icon (Emoji)</label>
                                <input type="text" name="icon" id="catIcon" class="form-control" value="🎵" maxlength="4">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="catSort" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="catDesc" class="form-control" rows="3"></textarea>
                        </div>
                        <div style="display:flex;gap:8px;">
                            <button type="submit" name="save_category" class="btn btn-primary btn-block">Save</button>
                            <button type="button" class="btn btn-glass" onclick="resetForm()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
    function editCategory(c) {
        document.getElementById('formTitle').innerHTML = 'Edit Category';
        document.getElementById('catId').value = c.id;
        document.getElementById('catName').value = c.name;
        document.getElementById('catParent').value = c.parent_id || '';
        document.getElementById('catIcon').value = c.icon;
        document.getElementById('catSort').value = c.sort_order;
        document.getElementById('catDesc').value = c.description || '';
    }
    function resetForm() {
        document.getElementById('formTitle').innerHTML = 'Add Category';
        document.getElementById('catId').value = 0;
        document.getElementById('catForm').reset();
    }
    </script>
</body>
</html>
