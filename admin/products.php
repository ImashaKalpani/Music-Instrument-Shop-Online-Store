<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('staff');

$db = getDB();
$flash = getFlash();
$action = $_GET['action'] ?? 'list';
$editId = (int)($_GET['id'] ?? 0);

// ---- DELETE ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $delId = (int)$_POST['product_id'];
    $db->prepare("UPDATE products SET is_active = 0 WHERE id = ?")->execute([$delId]);
    setFlash('success', 'Product removed successfully.');
    header('Location: products.php');
    exit;
}

// ---- CREATE / UPDATE ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $data = [
        'name'              => sanitize($_POST['name']),
        'slug'              => strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($_POST['name']))),
        'category_id'       => (int)$_POST['category_id'],
        'brand'             => sanitize($_POST['brand']),
        'price'             => (float)$_POST['price'],
        'sale_price'        => $_POST['sale_price'] ? (float)$_POST['sale_price'] : null,
        'stock_quantity'    => (int)$_POST['stock_quantity'],
        'sku'               => sanitize($_POST['sku']),
        'product_type'      => $_POST['product_type'],
        'short_description' => sanitize($_POST['short_description']),
        'description'       => sanitize($_POST['description']),
        'is_featured'       => isset($_POST['is_featured']) ? 1 : 0,
        'is_active'         => isset($_POST['is_active']) ? 1 : 0,
    ];

    // Handle image upload
    $imageName = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/products/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];
        if (in_array($ext, $allowed)) {
            $imageName = $data['slug'] . '.' . $ext;
            move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadDir . $imageName);
        }
    }

    if ($editId > 0) {
        // Update
        $sql = "UPDATE products SET name=?, slug=?, category_id=?, brand=?, price=?, sale_price=?, stock_quantity=?, sku=?, product_type=?, short_description=?, description=?, is_featured=?, is_active=?";
        $params = array_values($data);
        if ($imageName) { $sql .= ", image=?"; $params[] = $imageName; }
        $sql .= " WHERE id=?";
        $params[] = $editId;
        $db->prepare($sql)->execute($params);
        $productId = $editId;
        setFlash('success', 'Product updated successfully.');
    } else {
        // Create
        $sql = "INSERT INTO products (name,slug,category_id,brand,price,sale_price,stock_quantity,sku,product_type,short_description,description,is_featured,is_active,image)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $params = array_values($data);
        $params[] = $imageName;
        $db->prepare($sql)->execute($params);
        $productId = $db->lastInsertId();
        setFlash('success', 'Product created successfully.');
    }

    // Handle digital file upload
    if ($data['product_type'] === 'digital' && isset($_FILES['digital_file']) && $_FILES['digital_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/digital/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $fileName = $_FILES['digital_file']['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $uniqueName = 'digital_' . $productId . '_' . time() . '.' . $ext;
        
        if (move_uploaded_file($_FILES['digital_file']['tmp_name'], $uploadDir . $uniqueName)) {
            // Check if digital_products entry exists
            $check = $db->prepare("SELECT id FROM digital_products WHERE product_id = ?");
            $check->execute([$productId]);
            if ($check->fetch()) {
                $updDl = $db->prepare("UPDATE digital_products SET file_name = ?, file_path = ?, file_size = ?, file_format = ? WHERE product_id = ?");
                $updDl->execute([$fileName, $uniqueName, $_FILES['digital_file']['size'], $ext, $productId]);
            } else {
                $insDl = $db->prepare("INSERT INTO digital_products (product_id, file_name, file_path, file_size, file_format) VALUES (?, ?, ?, ?, ?)");
                $insDl->execute([$productId, $fileName, $uniqueName, $_FILES['digital_file']['size'], $ext]);
            }
        }
    }
    header('Location: products.php');
    exit;
}

// ---- LOAD DATA ----
$categories = $db->query("SELECT id, name, parent_id FROM categories WHERE is_active=1 ORDER BY parent_id IS NULL DESC, sort_order, name")->fetchAll();
$editProduct = null;
if ($editId) {
    $s = $db->prepare("SELECT * FROM products WHERE id=?");
    $s->execute([$editId]);
    $editProduct = $s->fetch();
}

// Product listing
$search = trim($_GET['q'] ?? '');
$catFilter = (int)($_GET['cat'] ?? 0);
$where = ['p.is_active = 1']; $params = [];
if ($search) { $where[] = "(p.name LIKE ? OR p.brand LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($catFilter) { $where[] = "p.category_id = ?"; $params[] = $catFilter; }
$whereSQL = implode(' AND ', $where);

$products = $db->prepare("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE $whereSQL ORDER BY p.created_at DESC");
$products->execute($params);
$productList = $products->fetchAll();

$pageTitle = ($action === 'edit' || $action === 'create') ? ($editId ? 'Edit Product' : 'New Product') : 'Manage Products';
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

        <?php if ($action === 'create' || $action === 'edit'): ?>
        <!-- ============================================================
             CREATE / EDIT FORM
             ============================================================ -->
        <header style="margin-bottom:28px;">
            <a href="products.php" style="font-size:.85rem;color:var(--text-muted);">← Back to Products</a>
            <h1 style="font-size:1.6rem;margin-top:10px;"><?= $editId ? 'Edit Product' : 'New Product' ?></h1>
        </header>

        <form method="POST" enctype="multipart/form-data">
            <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">
                <div>
                    <div class="card mb-4">
                        <div class="card-header"><h3 style="font-size:1rem;margin:0;">Product Details</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Product Name *</label>
                                <input type="text" name="name" class="form-control" required value="<?= sanitize($editProduct['name'] ?? '') ?>">
                            </div>
                            <div class="grid-2">
                                <div class="form-group">
                                    <label class="form-label">Brand</label>
                                    <input type="text" name="brand" class="form-control" value="<?= sanitize($editProduct['brand'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">SKU</label>
                                    <input type="text" name="sku" class="form-control" value="<?= sanitize($editProduct['sku'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Short Description</label>
                                <input type="text" name="short_description" class="form-control" maxlength="500" value="<?= sanitize($editProduct['short_description'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Full Description</label>
                                <textarea name="description" class="form-control" rows="6"><?= sanitize($editProduct['description'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><h3 style="font-size:1rem;margin:0;">Pricing & Inventory</h3></div>
                        <div class="card-body">
                            <div class="grid-2">
                                <div class="form-group">
                                    <label class="form-label">Price (£) *</label>
                                    <input type="number" step="0.01" name="price" class="form-control" required value="<?= $editProduct['price'] ?? '' ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Sale Price (£)</label>
                                    <input type="number" step="0.01" name="sale_price" class="form-control" value="<?= $editProduct['sale_price'] ?? '' ?>">
                                </div>
                            </div>
                            <div class="grid-2">
                                <div class="form-group">
                                    <label class="form-label">Stock Quantity</label>
                                    <input type="number" name="stock_quantity" class="form-control" value="<?= $editProduct['stock_quantity'] ?? 0 ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Product Type</label>
                                    <select name="product_type" class="form-control">
                                        <option value="physical" <?= ($editProduct['product_type']??'')==='physical'?'selected':'' ?>>Physical</option>
                                        <option value="digital" <?= ($editProduct['product_type']??'')==='digital'?'selected':'' ?>>Digital</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <div class="card mb-4">
                        <div class="card-header"><h3 style="font-size:1rem;margin:0;">Publish</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label style="display:flex;gap:8px;align-items:center;cursor:pointer;font-size:.9rem;color:var(--text-secondary);">
                                    <input type="checkbox" name="is_active" <?= ($editProduct['is_active'] ?? 1) ? 'checked' : '' ?> style="accent-color:var(--primary);"> Active (visible in store)
                                </label>
                            </div>
                            <div class="form-group">
                                <label style="display:flex;gap:8px;align-items:center;cursor:pointer;font-size:.9rem;color:var(--text-secondary);">
                                    <input type="checkbox" name="is_featured" <?= ($editProduct['is_featured'] ?? 0) ? 'checked' : '' ?> style="accent-color:var(--primary);"> Featured Product
                                </label>
                            </div>
                            <button type="submit" name="save_product" class="btn btn-primary btn-block mt-3"><?= $editId ? 'Update Product' : 'Create Product' ?></button>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header"><h3 style="font-size:1rem;margin:0;">Category</h3></div>
                        <div class="card-body">
                            <select name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($editProduct['category_id'] ?? 0)==$cat['id']?'selected':'' ?>>
                                    <?= $cat['parent_id'] ? '— ' : '' ?><?= sanitize($cat['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="card mb-4" id="digital_file_card" style="display: <?= ($editProduct['product_type'] ?? '') === 'digital' ? 'block' : 'none' ?>;">
                        <div class="card-header"><h3 style="font-size:1rem;margin:0;">Digital Product File</h3></div>
                        <div class="card-body">
                            <?php 
                            if ($editId) {
                                $dlData = $db->prepare("SELECT * FROM digital_products WHERE product_id = ?");
                                $dlData->execute([$editId]);
                                $file = $dlData->fetch();
                                if ($file) {
                                    echo '<div style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:10px;">Current file: <strong>' . sanitize($file['file_name']) . '</strong></div>';
                                }
                            }
                            ?>
                            <input type="file" name="digital_file" accept=".pdf,.zip,.mp3" class="form-control">
                            <div class="form-hint">PDF, ZIP, MP3. Max 20MB.</div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><h3 style="font-size:1rem;margin:0;">Product Image</h3></div>
                        <div class="card-body">
                            <?php if ($editProduct && $editProduct['image']): ?>
                            <div style="margin-bottom:12px;background:var(--bg-card2);border-radius:var(--radius);padding:10px;text-align:center;">
                                <img src="<?= SITE_URL ?>/assets/images/products/<?= $editProduct['image'] ?>" onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.svg'" style="max-height:160px;object-fit:contain;">
                            </div>
                            <?php endif; ?>
                            <input type="file" name="product_image" accept="image/*" class="form-control">
                            <div class="form-hint">JPG, PNG, WebP. Max 5MB.</div>
                        </div>
                    </div>

                    <script>
                    document.querySelector('select[name="product_type"]').addEventListener('change', function() {
                        document.getElementById('digital_file_card').style.display = (this.value === 'digital' ? 'block' : 'none');
                    });
                    </script>
                </div>
            </div>
        </form>

        <?php else: ?>
        <!-- ============================================================
             PRODUCT LIST
             ============================================================ -->
        <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;">
            <div>
                <h1 style="font-size:1.6rem;margin-bottom:4px;">Users</h1>
                <p style="color:var(--text-muted);font-size:.9rem;"><?= count($productList) ?> products</p>
            </div>
            <a href="products.php?action=create" class="btn btn-primary">Add Product</a>
        </header>

        <!-- Filter Bar -->
        <div class="card mb-4">
            <div class="card-body" style="padding:14px 20px;">
                <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                    <div class="navbar-search" style="flex:1;">
                        <span class="search-icon">🔍</span>
                        <input type="text" name="q" placeholder="Search products..." value="<?= sanitize($search) ?>" style="width:100%;">
                    </div>
                    <select name="cat" class="form-control" style="width:auto;padding:8px 14px;" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $catFilter==$c['id']?'selected':'' ?>><?= $c['parent_id']?'— ':'' ?><?= sanitize($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary btn-sm">Search</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="padding:0;">
                <div class="table-wrap" style="border:none;border-radius:0;">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:50px;"></th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productList as $p): ?>
                            <tr>
                                <td>
                                    <div style="width:40px;height:40px;background:var(--bg-card2);border-radius:6px;overflow:hidden;">
                                        <img src="<?= SITE_URL ?>/assets/images/products/<?= $p['image'] ?>" onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.svg'" style="width:100%;height:100%;object-fit:contain;padding:4px;">
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight:600;font-size:.85rem;"><?= sanitize($p['name']) ?></div>
                                    <div style="font-size:.72rem;color:var(--text-muted);"><?= sanitize($p['brand']) ?> | <?= $p['sku'] ?></div>
                                </td>
                                <td style="font-size:.82rem;"><?= sanitize($p['cat_name']) ?></td>
                                <td>
                                    <span style="font-weight:700;color:var(--primary);"><?= formatPrice($p['sale_price'] ?? $p['price']) ?></span>
                                    <?php if ($p['sale_price']): ?><br><span style="font-size:.72rem;text-decoration:line-through;color:var(--text-muted);"><?= formatPrice($p['price']) ?></span><?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['product_type']==='digital'): ?>
                                        <span class="pill pill-info">∞</span>
                                    <?php elseif ($p['stock_quantity'] <= 3 && $p['stock_quantity'] > 0): ?>
                                        <span class="pill pill-warning"><?= $p['stock_quantity'] ?></span>
                                    <?php elseif ($p['stock_quantity'] === 0): ?>
                                        <span class="pill pill-danger">0</span>
                                    <?php else: ?>
                                        <span class="pill pill-success"><?= $p['stock_quantity'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="pill <?= $p['product_type']==='digital'?'pill-purple':'pill-gray' ?>"><?= ucfirst($p['product_type']) ?></span></td>
                                <td>
                                    <?php if ($p['is_featured']): ?><span class="pill pill-warning">Featured</span><?php endif; ?>
                                </td>
                                <td>
                                    <div style="display:flex;gap:6px;">
                                        <a href="products.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-glass btn-sm">Edit</a>
                                        <form method="POST" onsubmit="return confirm('Delete this product?');">
                                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                            <button type="submit" name="delete_product" class="btn btn-danger btn-sm" style="background:#ef4444; border:none; color:#fff; cursor:pointer; padding:6px 12px; border-radius:6px;">Delete</button>
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
        <?php endif; ?>
    </main>
</body>
</html>
