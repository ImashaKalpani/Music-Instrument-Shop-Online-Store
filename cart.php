<?php
require_once __DIR__ . '/includes/functions.php';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = (int)($_POST['product_id'] ?? 0);
    $qty = (int)($_POST['quantity'] ?? 1);
    $redirect = $_POST['redirect'] ?? 'cart.php';

    if ($action === 'add' && $productId > 0) {
        $success = addToCart($productId, $qty);
        setFlash($success ? 'success' : 'danger', $success ? 'Item added to cart!' : 'Could not add item. It may be out of stock.');
        header('Location: ' . ($redirect === 'cart' ? 'cart.php' : $_SERVER['HTTP_REFERER'] ?? 'shop.php'));
        exit;
    }
    if ($action === 'update' && $productId > 0) {
        updateCartQty($productId, $qty);
        header('Location: cart.php');
        exit;
    }
    if ($action === 'remove' && $productId > 0) {
        removeFromCart($productId);
        setFlash('success', 'Item removed from cart.');
        header('Location: cart.php');
        exit;
    }
    if ($action === 'clear') {
        clearCart();
        setFlash('success', 'Cart cleared successfully.');
        header('Location: cart.php');
        exit;
    }
}

$cart     = getCart();
$subtotal = getCartTotal();
$shipping = getShippingCost();
$total    = $subtotal + $shipping;
$flash    = getFlash();

$pageTitle = 'Shopping Cart';
$metaDesc  = 'Review your shopping cart at Melody Masters.';
include 'includes/header.php';
?>

<div class="page-banner" style="padding:36px 0;">
  <div class="container">
    <h1>Shopping Cart</h1>
    <div class="breadcrumb" style="justify-content:center;">
      <a href="index.php">Home</a><span>›</span><span class="current">Cart</span>
    </div>
  </div>
</div>

<div class="section">
  <div class="container">
    <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><?= sanitize($flash['message']) ?></div><?php endif; ?>

    <?php if (empty($cart)): ?>
    <div class="text-center" style="padding:80px 0;">
      <div style="font-size:60px;margin-bottom:20px;opacity:.3; display:flex; justify-content:center;"><i data-lucide="shopping-cart" style="width:60px; height:60px;"></i></div>
      <h2>Your cart is empty</h2>
      <p style="margin-bottom:30px;">Discover our amazing collection of musical instruments!</p>
      <a href="shop.php" class="btn btn-primary btn-lg">Continue Shopping</a>
    </div>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:1fr 360px;gap:32px;align-items:start;">

      <!-- Cart Items -->
      <div class="card">
        <div class="card-header flex-between">
          <h2 style="font-size:1.1rem;margin:0;">Cart Items (<?= getCartCount() ?>)</h2>
          <form method="POST">
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="product_id" value="0"> <!-- placeholder -->
          </form>
        </div>
        <div class="card-body" style="padding:0 24px;">
          <?php foreach ($cart as $pid => $item):
            $itemPrice = $item['sale_price'] ?? $item['price'];
            $lineTotal = $itemPrice * $item['quantity'];
          ?>
          <div class="cart-item">
            <div class="cart-item-img">
              <img src="assets/images/products/<?= $item['image'] ?>" onerror="this.src='assets/images/placeholder.svg'" alt="<?= sanitize($item['name']) ?>">
            </div>
            <div class="cart-item-info">
              <a href="product.php?id=<?= $pid ?>" class="cart-item-name" style="text-decoration:none;color:var(--text-primary);font-size:.92rem;"><?= sanitize($item['name']) ?></a>
              <div class="cart-item-type"><?= ucfirst($item['product_type']) ?></div>
              <div style="display:flex;align-items:center;gap:12px;margin-top:10px;flex-wrap:wrap;">
                <?php if ($item['product_type'] === 'physical'): ?>
                <form method="POST" style="display:flex;align-items:center;gap:0;">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="product_id" value="<?= $pid ?>">
                  <div class="qty-control" style="transform:scale(.85);transform-origin:left;">
                    <button type="button" class="qty-btn" onclick="this.form.quantity.value=Math.max(1,+this.form.quantity.value-1);this.form.submit()">-</button>
                    <input type="number" name="quantity" id="qty-<?= $pid ?>" class="qty-input" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" onchange="this.form.submit()">
                    <button type="button" class="qty-btn" onclick="this.form.quantity.value=Math.min(<?= $item['stock'] ?>,+this.form.quantity.value+1);this.form.submit()">+</button>
                  </div>
                </form>
                <?php else: ?>
                <span class="pill pill-info" style="font-size:.7rem;">📄 Digital × 1</span>
                <?php endif; ?>

                <form method="POST">
                  <input type="hidden" name="action" value="remove">
                  <input type="hidden" name="product_id" value="<?= $pid ?>">
                  <button type="submit" style="background:none;border:none;color:var(--danger);cursor:pointer;font-size:.82rem;">🗑 Remove</button>
                </form>
              </div>
            </div>
            <div class="cart-item-price"><?= formatPrice($lineTotal) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="card-footer flex-between">
          <a href="shop.php" class="btn btn-glass btn-sm">← Continue Shopping</a>
          <form method="POST" onsubmit="return confirm('Clear all cart items?')">
            <input type="hidden" name="action" value="clear">
            <button type="button" class="btn btn-glass btn-sm" onclick="if(confirm('Clear cart?')){this.form.submit();}">Clear Cart</button>
          </form>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="card" style="position:sticky;top:100px;">
        <div class="card-header"><h2 style="font-size:1.1rem;margin:0;">Order Summary</h2></div>
        <div class="card-body">
          <div class="order-summary-row">
            <span>Subtotal</span>
            <span><?= formatPrice($subtotal) ?></span>
          </div>
          <div class="order-summary-row">
            <span>Shipping</span>
            <span>
              <?php if ($shipping === 0.0): ?>
              <span style="color:var(--success); font-weight:600;">Free</span>
              <?php else: ?>
              <?= formatPrice($shipping) ?>
              <?php endif; ?>
            </span>
          </div>
          <?php if ($shipping > 0 && $subtotal < FREE_SHIPPING_THRESHOLD): ?>
          <div style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);border-radius:8px;padding:10px 14px;margin:10px 0;font-size:.8rem;color:var(--success);">
            ✓ Add <?= formatPrice(FREE_SHIPPING_THRESHOLD - $subtotal) ?> more for free shipping!
          </div>
          <?php endif; ?>
          <div class="order-summary-row order-summary-total">
            <span>Total</span>
            <span><?= formatPrice($total) ?></span>
          </div>
          <a href="checkout.php" class="btn btn-primary btn-block btn-lg mt-3">Proceed to Checkout</a>
          <div style="text-align:center;margin-top:12px;font-size:.75rem;color:var(--text-muted);">
            🔒 Secure checkout &nbsp;|&nbsp; 256-bit SSL
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
