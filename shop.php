<?php
require_once __DIR__ . '/includes/functions.php';
$db = getDB();

// ---- Filters ----
$searchQ   = trim($_GET['q'] ?? '');
$catSlug   = trim($_GET['category'] ?? '');
$brandF    = trim($_GET['brand'] ?? '');
$minPrice  = (float)($_GET['min_price'] ?? 0);
$maxPrice  = (float)($_GET['max_price'] ?? 5000);
$sort      = $_GET['sort'] ?? 'featured';
$typeF     = $_GET['type'] ?? '';
$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 100; // Increased to show all products as requested

// Fetch all categories for sidebar
$allCategories = $db->query("SELECT c.*, COUNT(p.id) as product_count
  FROM categories c
  LEFT JOIN products p ON p.category_id=c.id AND p.is_active=1
  WHERE c.is_active=1
  GROUP BY c.id ORDER BY c.parent_id IS NULL DESC, c.sort_order, c.name")->fetchAll();

// Build product query
$where = ['p.is_active = 1'];
$params = [];

if ($searchQ) { 
    $where[] = '(p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ? OR c.name LIKE ?)'; 
    $params[] = "%$searchQ%"; $params[] = "%$searchQ%"; $params[] = "%$searchQ%"; $params[] = "%$searchQ%"; 
}
if ($catSlug) {
    $catRow = $db->prepare('SELECT id, parent_id FROM categories WHERE slug = ?');
    $catRow->execute([$catSlug]);
    $catData = $catRow->fetch();
    if ($catData) {
        if ($catData['parent_id'] === null) {
            // Parent cat — include all subcategory products
            $childIds = $db->prepare('SELECT id FROM categories WHERE parent_id = ? OR id = ?');
            $childIds->execute([$catData['id'], $catData['id']]);
            $ids = array_column($childIds->fetchAll(), 'id');
            $where[] = 'p.category_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')';
            $params = array_merge($params, $ids);
        } else {
            $where[] = 'p.category_id = ?'; $params[] = $catData['id'];
        }
    }
}
if ($brandF)    { $where[] = 'p.brand = ?'; $params[] = $brandF; }
if ($typeF)     { $where[] = 'p.product_type = ?'; $params[] = $typeF; }
if ($minPrice > 0) { $where[] = 'COALESCE(p.sale_price, p.price) >= ?'; $params[] = $minPrice; }
if ($maxPrice < 5000) { $where[] = 'COALESCE(p.sale_price, p.price) <= ?'; $params[] = $maxPrice; }

$orderBy = match($sort) {
    'price_asc'   => 'COALESCE(p.sale_price, p.price) ASC',
    'price_desc'  => 'COALESCE(p.sale_price, p.price) DESC',
    'newest'      => 'p.created_at DESC',
    'name_asc'    => 'p.name ASC',
    'rating'      => 'avg_rating DESC',
    default       => 'p.is_featured DESC, p.created_at DESC',
};

$whereSQL = 'WHERE ' . implode(' AND ', $where);

// Count
$countStmt = $db->prepare("SELECT COUNT(DISTINCT p.id) FROM products p LEFT JOIN categories c ON p.category_id=c.id $whereSQL");
$countStmt->execute($params);
$totalProducts = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalProducts / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

// Fetch products
$sql = "SELECT p.*, c.name as cat_name, c.slug as cat_slug,
  COALESCE(AVG(r.rating),0) as avg_rating,
  COUNT(DISTINCT r.id) as review_count
  FROM products p
  LEFT JOIN categories c ON p.category_id = c.id
  LEFT JOIN reviews r ON r.product_id = p.id AND r.is_approved = 1
  $whereSQL
  GROUP BY p.id
  ORDER BY $orderBy
  LIMIT $perPage OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get brands for filter
$brands = $db->query("SELECT DISTINCT brand FROM products WHERE is_active=1 AND brand IS NOT NULL ORDER BY brand")->fetchAll(PDO::FETCH_COLUMN);

// Active category name
$activeCatName = '';
if ($catSlug) {
    $r = $db->prepare('SELECT name FROM categories WHERE slug=?');
    $r->execute([$catSlug]);
    $activeCatName = $r->fetchColumn();
}

$pageTitle = ($activeCatName ?: ($searchQ ? "Search: $searchQ" : 'Shop')) . ' – ' . SITE_NAME;
$metaDesc  = 'Browse our full collection of musical instruments and accessories.';
include 'includes/header.php';

function renderStars(float $rating): string {
    $h = '<div class="product-card-stars">';
    for ($i = 1; $i <= 5; $i++) $h .= '<span class="star ' . ($i <= round($rating) ? '' : 'empty') . '">★</span>';
    return $h . '</div>';
}
?>

<!-- Page Banner Removed -->

<div class="section">
  <div class="container">
    <div style="display:flex; flex-direction:column; gap:32px;">

      <!-- ============================================================
           HORIZONTAL FILTERS
           ============================================================ -->
      <div class="card" style="padding: 20px; overflow:visible;">

        
        <form method="GET" id="filterForm" style="display:flex; flex-wrap:wrap; gap:24px; align-items:flex-end;">
          <?php if ($searchQ): ?><input type="hidden" name="q" value="<?= sanitize($searchQ) ?>"><?php endif; ?>

          <!-- Categories -->
          <div style="flex:1; min-width: 200px;">
            <label class="form-label">Category</label>
            <select name="category" class="form-control" onchange="this.form.submit()">
              <option value="">All Categories</option>
              <?php
              $parents = array_filter($allCategories, fn($c) => $c['parent_id'] === null);
              $children = array_filter($allCategories, fn($c) => $c['parent_id'] !== null);
              foreach ($parents as $cat):
                $subs = array_filter($children, fn($c) => $c['parent_id'] == $cat['id']);
              ?>
                <option value="<?= $cat['slug'] ?>" <?= $catSlug===$cat['slug']?'selected':'' ?>><?= sanitize($cat['name']) ?> (<?= $cat['product_count'] ?>)</option>
                <?php foreach ($subs as $sub): ?>
                  <option value="<?= $sub['slug'] ?>" <?= $catSlug===$sub['slug']?'selected':'' ?>>&nbsp;&nbsp;— <?= sanitize($sub['name']) ?> (<?= $sub['product_count'] ?>)</option>
                <?php endforeach; ?>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Price -->
          <div style="flex:1; min-width: 200px;">
            <label class="form-label" style="display:flex; justify-content:space-between;">
              <span>Max Price</span>
              <span id="maxPriceVal">£<?= $maxPrice ?></span>
            </label>
            <input type="range" name="max_price" min="0" max="5000" step="10" value="<?= $maxPrice ?>" oninput="document.getElementById('maxPriceVal').textContent='£'+this.value" onchange="this.form.submit()" style="width:100%; accent-color:var(--primary);">
          </div>

          <!-- Brand -->
          <div style="flex:1; min-width: 150px;">
            <label class="form-label">Brand</label>
            <select name="brand" class="form-control" onchange="this.form.submit()">
              <option value="">All Brands</option>
              <?php foreach ($brands as $brand): ?>
                <option value="<?= htmlspecialchars($brand) ?>" <?= $brandF===$brand?'selected':'' ?>><?= sanitize($brand) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Type -->
          <div style="flex:1; min-width: 150px;">
            <label class="form-label">Product Type</label>
            <select name="type" class="form-control" onchange="this.form.submit()">
              <option value="">All Types</option>
              <option value="physical" <?= $typeF==='physical'?'selected':'' ?>>Physical Products</option>
              <option value="digital" <?= $typeF==='digital'?'selected':'' ?>>Digital Downloads</option>
            </select>
          </div>

          <div style="flex: 0 0 auto;">
            <a href="shop.php<?= $searchQ ? '?q='.urlencode($searchQ) : '' ?>" class="btn btn-outline" style="height: 44px; padding: 0 24px; display:inline-flex; align-items:center;">Reset Filters</a>
          </div>
        </form>
      </div>

      <!-- ============================================================
           PRODUCTS GRID
           ============================================================ -->
      <div>
        <!-- Sort bar -->
        <div class="flex-between mb-3" style="flex-wrap:wrap;gap:12px;">
          <p style="font-size:.85rem;color:var(--text-muted);">
            Showing <strong style="color:var(--text-primary)"><?= count($products) ?></strong> of <strong style="color:var(--text-primary)"><?= $totalProducts ?></strong> results
          </p>
          <form method="GET">
            <?php if ($catSlug): ?><input type="hidden" name="category" value="<?= sanitize($catSlug) ?>"><?php endif; ?>
            <?php if ($searchQ): ?><input type="hidden" name="q" value="<?= sanitize($searchQ) ?>"><?php endif; ?>
            <?php if ($brandF): ?><input type="hidden" name="brand" value="<?= sanitize($brandF) ?>"><?php endif; ?>
            <?php if ($typeF): ?><input type="hidden" name="type" value="<?= sanitize($typeF) ?>"><?php endif; ?>
            <select name="sort" class="form-control" style="width:auto;padding:8px 12px;" onchange="this.form.submit()">
              <option value="featured"   <?= $sort==='featured'?'selected':'' ?>>Featured</option>
              <option value="newest"     <?= $sort==='newest'?'selected':'' ?>>Newest First</option>
              <option value="price_asc"  <?= $sort==='price_asc'?'selected':'' ?>>Price: Low → High</option>
              <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Price: High → Low</option>
              <option value="name_asc"   <?= $sort==='name_asc'?'selected':'' ?>>Name A–Z</option>
              <option value="rating"     <?= $sort==='rating'?'selected':'' ?>>Highest Rated</option>
            </select>
          </form>
        </div>

        <?php if (empty($products)): ?>
        <div class="text-center" style="padding:80px 0;">
          <div style="font-size:60px;margin-bottom:16px;opacity:.3;">🎵</div>
          <h3 style="margin-bottom:8px;">No products found</h3>
          <p>Try adjusting your filters or search term.</p>
          <a href="shop.php" class="btn btn-primary mt-3">Clear All Filters</a>
        </div>
        <?php else: ?>
        <div class="grid-auto">
          <?php foreach ($products as $p):
            $cp = $p['sale_price'] ?? $p['price'];
            $hasDiscount = $p['sale_price'] && $p['sale_price'] < $p['price'];
            $discPct = $hasDiscount ? round((($p['price'] - $p['sale_price']) / $p['price']) * 100) : 0;
            $isOut = $p['product_type'] === 'physical' && $p['stock_quantity'] === 0;
            $isLow = $p['product_type'] === 'physical' && $p['stock_quantity'] <= 3 && $p['stock_quantity'] > 0;
          ?>
          <div class="product-card">
            <div class="product-card-img">
              <?php if ($hasDiscount): ?><span class="product-card-badge badge-sale">-<?= $discPct ?>%</span>
              <?php elseif ($p['product_type']==='digital'): ?><span class="product-card-badge badge-digital">📄 Digital</span>
              <?php elseif ($isLow): ?><span class="product-card-badge badge-low-stock">Low Stock</span>
              <?php elseif ($isOut): ?><span class="product-card-badge badge-out">Out of Stock</span>
              <?php endif; ?>
              <a href="product.php?slug=<?= $p['slug'] ?>">
                <img src="assets/images/products/<?= $p['image'] ?>" onerror="this.src='assets/images/placeholder.svg'" alt="<?= sanitize($p['name']) ?>" loading="lazy">
              </a>
            </div>
            <div class="product-card-body">
              <div class="product-card-brand"><?= sanitize($p['brand']) ?></div>
              <h3 class="product-card-name"><a href="product.php?slug=<?= $p['slug'] ?>"><?= sanitize($p['name']) ?></a></h3>
              <?= renderStars((float)$p['avg_rating']) ?>
              <div class="product-card-footer">
                <div class="product-price">
                  <span class="price-current"><?= formatPrice((float)$cp) ?></span>
                  <?php if ($hasDiscount): ?><span class="price-original"><?= formatPrice((float)$p['price']) ?></span><?php endif; ?>
                </div>
                <?php if (!$isOut): ?>
                <form method="POST" action="cart.php" style="display:inline;">
                  <input type="hidden" name="action" value="add">
                  <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                  <input type="hidden" name="quantity" value="1">
                  <button type="submit" class="btn-add-cart" title="Add to Cart">+</button>
                </form>
                <?php else: ?>
                <button class="btn-add-cart" disabled style="opacity:.4;cursor:not-allowed;">✕</button>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php
          $qs = http_build_query(array_filter([
              'category'  => $catSlug,
              'q'         => $searchQ,
              'brand'     => $brandF,
              'type'      => $typeF,
              'sort'      => $sort !== 'featured' ? $sort : '',
              'max_price' => $maxPrice < 5000 ? $maxPrice : '',
          ]));
          function pgLink(int $p, string $qs): string {
              return 'shop.php?' . $qs . ($qs ? '&' : '') . 'page=' . $p;
          }
          ?>
          <a href="<?= pgLink(max(1,$page-1), $qs) ?>" class="page-btn <?= $page<=1?'disabled':'' ?>">‹</a>
          <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
          <a href="<?= pgLink($i,$qs) ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
          <?php endfor; ?>
          <a href="<?= pgLink(min($totalPages,$page+1),$qs) ?>" class="page-btn <?= $page>=$totalPages?'disabled':'' ?>">›</a>
        </div>
        <?php endif; ?>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
