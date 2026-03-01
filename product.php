<?php
require_once __DIR__ . '/includes/functions.php';
$db = getDB();

$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: shop.php'); exit; }

$stmt = $db->prepare("SELECT p.*, c.name as cat_name, c.slug as cat_slug,
  pc.name as parent_cat_name, pc.slug as parent_cat_slug,
  COALESCE(AVG(r.rating),0) as avg_rating,
  COUNT(DISTINCT r.id) as review_count
  FROM products p
  LEFT JOIN categories c ON p.category_id=c.id
  LEFT JOIN categories pc ON c.parent_id=pc.id
  LEFT JOIN reviews r ON r.product_id=p.id AND r.is_approved=1
  WHERE p.slug=? AND p.is_active=1
  GROUP BY p.id");
$stmt->execute([$slug]);
$product = $stmt->fetch();
if (!$product) { header('Location: shop.php'); exit; }

// Digital product info
$digital = null;
if ($product['product_type'] === 'digital') {
    $ds = $db->prepare('SELECT * FROM digital_products WHERE product_id=?');
    $ds->execute([$product['id']]);
    $digital = $ds->fetch();
}

// Reviews
$revStmt = $db->prepare("SELECT r.*, CONCAT(u.first_name,' ',LEFT(u.last_name,1),'.') as username
  FROM reviews r JOIN users u ON r.user_id=u.id
  WHERE r.product_id=? AND r.is_approved=1
  ORDER BY r.created_at DESC");
$revStmt->execute([$product['id']]);
$reviews = $revStmt->fetchAll();

// Has user purchased this product?
$canReview = false;
$alreadyReviewed = false;
$currentUser = getCurrentUser();
if ($currentUser) {
    $purchStmt = $db->prepare("SELECT oi.id, oi.order_id FROM order_items oi
      JOIN orders o ON oi.order_id=o.id
      WHERE o.user_id=? AND oi.product_id=? AND o.payment_status='paid' LIMIT 1");
    $purchStmt->execute([$currentUser['id'], $product['id']]);
    $purchase = $purchStmt->fetch();
    $canReview = (bool)$purchase;
    if ($canReview) {
        $revCheck = $db->prepare("SELECT id FROM reviews WHERE user_id=? AND product_id=?");
        $revCheck->execute([$currentUser['id'], $product['id']]);
        $alreadyReviewed = (bool)$revCheck->fetch();
    }
}

// Submit review
$reviewError = '';
$reviewSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    if (!$currentUser) { $reviewError = 'Please log in to submit a review.'; }
    elseif (!$canReview) { $reviewError = 'You must purchase this product to review it.'; }
    elseif ($alreadyReviewed) { $reviewError = 'You have already reviewed this product.'; }
    else {
        $rating  = (int)($_POST['rating'] ?? 0);
        $title   = trim($_POST['review_title'] ?? '');
        $body    = trim($_POST['review_body'] ?? '');
        if ($rating < 1 || $rating > 5) { $reviewError = 'Please select a star rating.'; }
        elseif (!$body) { $reviewError = 'Please write a review.'; }
        else {
            $ins = $db->prepare("INSERT INTO reviews (product_id, user_id, order_id, rating, title, body, is_approved) VALUES (?,?,?,?,?,?,1)");
            $ins->execute([$product['id'], $currentUser['id'], $purchase['order_id'], $rating, $title, $body]);
            $reviewSuccess = 'Thank you! Your review has been published. It is now visible to everyone.';
            $alreadyReviewed = true;
            
            // Re-fetch reviews to show the new one immediately
            $revStmt->execute([$product['id']]);
            $reviews = $revStmt->fetchAll();
        }
    }
}

// Related products
$relStmt = $db->prepare("SELECT * FROM products WHERE category_id=? AND id!=? AND is_active=1 ORDER BY is_featured DESC LIMIT 4");
$relStmt->execute([$product['category_id'], $product['id']]);
$related = $relStmt->fetchAll();

$specs = $product['specifications'] ? json_decode($product['specifications'], true) : [];
$cp    = $product['sale_price'] ?? $product['price'];
$hasDiscount = $product['sale_price'] && $product['sale_price'] < $product['price'];
$discPct = $hasDiscount ? round((($product['price'] - $product['sale_price']) / $product['price']) * 100) : 0;
$isOut = $product['product_type'] === 'physical' && $product['stock_quantity'] === 0;

$pageTitle = sanitize($product['name']);
$metaDesc  = sanitize($product['short_description'] ?: substr($product['description'], 0, 160));
include 'includes/header.php';

function renderStars($r,$size=14){ $h='<div style="display:flex;gap:3px;align-items:center;">'; for($i=1;$i<=5;$i++) $h.='<span style="color:'.($i<=$r?'var(--primary)':'var(--border)').';font-size:'.$size.'px;">★</span>'; return $h.'</div>'; }
?>

<div class="page-banner" style="padding:30px 0;">
  <div class="container">
    <div class="breadcrumb" style="justify-content:center;">
      <a href="index.php">Home</a><span>›</span>
      <a href="shop.php">Shop</a><span>›</span>
      <?php if ($product['parent_cat_name']): ?>
      <a href="shop.php?category=<?= $product['parent_cat_slug'] ?>"><?= sanitize($product['parent_cat_name']) ?></a><span>›</span>
      <?php endif; ?>
      <a href="shop.php?category=<?= $product['cat_slug'] ?>"><?= sanitize($product['cat_name']) ?></a><span>›</span>
      <span class="current"><?= sanitize($product['name']) ?></span>
    </div>
  </div>
</div>

<div class="section">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:start;">

      <!-- Gallery -->
      <div>
        <div class="product-gallery-main">
          <img src="assets/images/products/<?= $product['image'] ?>" onerror="this.src='assets/images/placeholder.svg'" alt="<?= sanitize($product['name']) ?>" id="mainImg">
        </div>
        <?php if ($product['gallery']): ?>
        <div class="product-gallery-thumbs">
          <?php $gallery = json_decode($product['gallery'], true) ?: []; foreach ($gallery as $img): ?>
          <div class="thumb" onclick="document.getElementById('mainImg').src='assets/images/products/<?= $img ?>'">
            <img src="assets/images/products/<?= $img ?>" onerror="this.src='assets/images/placeholder.svg'" alt="" loading="lazy">
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Info -->
      <div>
        <div style="margin-bottom:8px;display:flex;gap:8px;flex-wrap:wrap;">
          <span class="pill pill-purple"><?= sanitize($product['cat_name']) ?></span>
          <?php if ($product['product_type']==='digital'): ?><span class="pill pill-info">📄 Digital Download</span><?php endif; ?>
          <?php if ($isOut): ?><span class="pill pill-gray">Out of Stock</span><?php endif; ?>
          <?php if ($product['stock_quantity'] <= 3 && $product['stock_quantity'] > 0 && $product['product_type']==='physical'): ?>
          <span class="pill pill-warning">Only <?= $product['stock_quantity'] ?> left!</span>
          <?php endif; ?>
        </div>
        <div style="font-size:.8rem;font-weight:700;color:var(--primary);letter-spacing:1px;text-transform:uppercase;margin-bottom:10px;"><?= sanitize($product['brand']) ?></div>
        <h1 style="font-size:1.8rem;margin-bottom:14px;"><?= sanitize($product['name']) ?></h1>

        <!-- Stars -->
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;">
          <?= renderStars(round((float)$product['avg_rating'])) ?>
          <span style="font-size:.85rem;color:var(--text-muted);"><?= number_format((float)$product['avg_rating'],1) ?> (<?= $product['review_count'] ?> review<?= $product['review_count']!==1?'s':'' ?>)</span>
        </div>

        <!-- Price -->
        <div class="product-price-display">
          <span class="product-current-price"><?= formatPrice((float)$cp) ?></span>
          <?php if ($hasDiscount): ?>
          <span class="product-old-price"><?= formatPrice((float)$product['price']) ?></span>
          <span class="pill pill-danger" style="margin-left:8px;">Save <?= $discPct ?>%</span>
          <?php endif; ?>
        </div>

        <p style="margin:18px 0;font-size:.92rem;color:var(--text-secondary);line-height:1.7;"><?= nl2br(sanitize($product['short_description'] ?: substr($product['description'],0,300))) ?></p>

        <?php if ($product['product_type'] === 'digital' && $digital): ?>
        <div class="glass-card" style="padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:12px;">
          <span style="font-size:24px;">📄</span>
          <div>
            <div style="font-weight:600;font-size:.88rem;"><?= sanitize($digital['file_name']) ?></div>
            <div style="font-size:.75rem;color:var(--text-muted);"><?= $digital['file_size'] ?> &bull; <?= $digital['file_format'] ?> &bull; Up to <?= $digital['download_limit'] ?> downloads</div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Stock indicator -->
        <?php if ($product['product_type']==='physical'): ?>
        <div style="margin-bottom:20px;font-size:.85rem;">
          <?php if ($product['stock_quantity'] > 10): ?>
          <span style="color:var(--success);">✓ In Stock</span>
          <?php elseif ($product['stock_quantity'] > 0): ?>
          <span style="color:var(--warning);">⚠ Only <?= $product['stock_quantity'] ?> in stock – order soon!</span>
          <?php else: ?>
          <span style="color:var(--danger);">✕ Out of Stock</span>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Add to cart -->
        <?php if (!$isOut): ?>
        <form method="POST" action="cart.php" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:20px;">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
          <?php if ($product['product_type']==='physical'): ?>
          <div class="qty-control">
            <button type="button" class="qty-btn" onclick="stepQty(-1)">-</button>
            <input type="number" name="quantity" id="pdtQty" class="qty-input" value="1" min="1" max="<?= $product['stock_quantity'] ?>">
            <button type="button" class="qty-btn" onclick="stepQty(1)">+</button>
          </div>
          <?php else: ?>
          <input type="hidden" name="quantity" value="1">
          <?php endif; ?>
          <button type="submit" class="btn btn-primary btn-lg" style="flex:1;">Add to Cart</button>
        </form>
        <?php else: ?>
        <button class="btn btn-glass btn-lg btn-block" disabled style="opacity:.5;margin-bottom:20px;">Out of Stock</button>
        <?php endif; ?>

        <!-- SKU & Shipping info -->
        <div style="border-top:1px solid var(--border);padding-top:16px;font-size:.82rem;color:var(--text-muted);display:flex;flex-direction:column;gap:6px;">
          <?php if ($product['sku']): ?><div>SKU: <span style="color:var(--text-secondary);"><?= sanitize($product['sku']) ?></span></div><?php endif; ?>
          <?php if ($product['product_type']==='physical'): ?>
          <div>🚚 <?= (float)$cp >= FREE_SHIPPING_THRESHOLD ? '<span style="color:var(--success);">Free shipping</span>' : 'Standard shipping: ' . formatPrice(STANDARD_SHIPPING_COST) ?></div>
          <div>🔄 30-day returns</div>
          <?php else: ?>
          <div>📄 Instant download after payment</div>
          <div>🔒 Digital licence – personal use only</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- ============================================================
         TABS: Description | Specs | Reviews
         ============================================================ -->
    <div class="mt-5">
      <div class="tabs">
        <button class="tab-btn active" data-tab="desc">Description</button>
        <?php if ($specs): ?><button class="tab-btn" data-tab="specs">Specifications</button><?php endif; ?>
        <button class="tab-btn" data-tab="reviews">Reviews (<?= count($reviews) ?>)</button>
      </div>

      <div id="tab-desc" class="tab-pane active">
        <p style="font-size:.95rem;color:var(--text-secondary);line-height:1.8;max-width:800px;"><?= nl2br(sanitize($product['description'])) ?></p>
      </div>

      <?php if ($specs): ?>
      <div id="tab-specs" class="tab-pane">
        <div class="table-wrap" style="max-width:600px;">
          <table class="specs-table">
            <tbody>
              <?php foreach ($specs as $key => $val): ?>
              <tr><td><?= sanitize($key) ?></td><td><?= sanitize((string)$val) ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <div id="tab-reviews" class="tab-pane">
        <?php if ($reviewSuccess): ?><div class="alert alert-success"><?= sanitize($reviewSuccess) ?></div><?php endif; ?>
        <?php if ($reviewError):   ?><div class="alert alert-danger"><?= sanitize($reviewError) ?></div>  <?php endif; ?>

        <?php if (empty($reviews)): ?>
        <p style="color:var(--text-muted);">No reviews yet. Be the first to review this product!</p>
        <?php else: ?>
        <?php foreach ($reviews as $rev): ?>
        <div class="review-card">
          <div class="review-header">
            <div>
              <?= renderStars($rev['rating']) ?>
              <div style="margin-top:6px;font-weight:600;font-size:.9rem;"><?= sanitize($rev['title'] ?: 'Great product!') ?></div>
            </div>
            <div>
              <div class="reviewer-name"><?= sanitize($rev['username']) ?></div>
              <div class="review-date"><?= date('j M Y', strtotime($rev['created_at'])) ?></div>
            </div>
          </div>
          <p class="review-body"><?= nl2br(sanitize($rev['body'])) ?></p>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- Review Form -->
        <?php if ($canReview && !$alreadyReviewed): ?>
        <div class="card mt-4" style="max-width:600px;">
          <div class="card-header"><h3 style="font-size:1rem;margin:0;">Write a Review</h3></div>
          <div class="card-body">
            <form method="POST">
              <input type="hidden" name="action" value="submit_review">
              <div class="form-group">
                <div class="form-label">Rating</div>
                <div class="stars-input">
                  <?php for($i=5;$i>=1;$i--): ?>
                  <input type="radio" name="rating" id="star<?=$i?>" value="<?=$i?>">
                  <label for="star<?=$i?>">★</label>
                  <?php endfor; ?>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="review_title">Review Title</label>
                <input type="text" id="review_title" name="review_title" class="form-control" placeholder="Summarise your experience..." maxlength="200">
              </div>
              <div class="form-group">
                <label class="form-label" for="review_body">Your Review</label>
                <textarea id="review_body" name="review_body" class="form-control" rows="5" placeholder="Tell others about your experience with this product..." required></textarea>
              </div>
              <button type="submit" class="btn btn-primary">Submit Review</button>
            </form>
          </div>
        </div>
        <?php elseif (!$currentUser): ?>
        <div class="alert alert-info mt-3">
          <a href="login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>">Log in</a> or <a href="register.php">create an account</a> to write a review.
        </div>
        <?php elseif (!$canReview): ?>
        <div class="alert alert-warning mt-3">You must purchase this product before you can review it.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Related Products -->
    <?php if ($related): ?>
    <div class="mt-5">
      <h2 style="margin-bottom:28px;">Related <span class="gradient-text">Products</span></h2>
      <div class="grid-4">
        <?php foreach ($related as $p):
          $cp2 = $p['sale_price'] ?? $p['price'];
        ?>
        <div class="product-card">
          <div class="product-card-img">
            <a href="product.php?slug=<?= $p['slug'] ?>">
              <img src="assets/images/products/<?= $p['image'] ?>" onerror="this.src='assets/images/placeholder.svg'" alt="<?= sanitize($p['name']) ?>" loading="lazy">
            </a>
          </div>
          <div class="product-card-body">
            <div class="product-card-brand"><?= sanitize($p['brand']) ?></div>
            <div class="product-card-name"><a href="product.php?slug=<?= $p['slug'] ?>"><?= sanitize($p['name']) ?></a></div>
            <div class="product-card-footer">
              <span class="price-current"><?= formatPrice((float)$cp2) ?></span>
              <?php if ($p['stock_quantity'] > 0 || $p['product_type']==='digital'): ?>
              <form method="POST" action="cart.php"><input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= $p['id'] ?>"><input type="hidden" name="quantity" value="1"><button class="btn-add-cart" type="submit">+</button></form>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<script>
function stepQty(d) {
  const inp = document.getElementById('pdtQty');
  if (!inp) return;
  let v = parseInt(inp.value) + d;
  inp.value = Math.max(1, Math.min(parseInt(inp.max), v));
}
</script>

<?php include 'includes/footer.php'; ?>
