<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$db   = getDB();
$user = getCurrentUser();

$orderId = (int)($_GET['order'] ?? 0);
if (!$orderId) { header('Location: account.php'); exit; }

$stmt = $db->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
$stmt->execute([$orderId, $user['id']]);
$order = $stmt->fetch();
if (!$order) { header('Location: account.php'); exit; }

$items = $db->prepare("SELECT oi.*, p.slug as product_slug FROM order_items oi LEFT JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?");
$items->execute([$orderId]);
$orderItems = $items->fetchAll();

$hasDigital = false;
foreach ($orderItems as $i) { if ($i['product_type']==='digital') { $hasDigital=true; break; } }

$pageTitle = 'Order Confirmed – ' . $order['order_number'];
include 'includes/header.php';
?>

<div class="section" style="min-height:70vh;">
  <div class="container" style="max-width:740px;">

    <!-- Success Header -->
    <div class="text-center" style="padding:48px 0 32px;">
      <div style="width:80px;height:80px;border-radius:50%;background:rgba(16,185,129,.15);border:2px solid rgba(16,185,129,.4);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:36px;">✓</div>
      <h1 style="font-size:2rem;margin-bottom:10px;">Order Confirmed!</h1>
      <p style="color:var(--text-secondary);max-width:420px;margin:0 auto 8px;">
        Thank you, <strong><?= sanitize($user['first_name']) ?></strong>! Your order has been placed successfully.
      </p>
      <span class="pill pill-purple" style="font-size:.82rem;"><?= sanitize($order['order_number']) ?></span>
    </div>

    <!-- Order Details Card -->
    <div class="card mb-4">
      <div class="card-header flex-between">
        <h2 style="font-size:1rem;margin:0;">📋 Order Details</h2>
        <span class="pill pill-success"><?= ucfirst($order['status']) ?></span>
      </div>
      <div class="card-body">
        <div class="grid-2">
          <div>
            <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:4px;">Order Date</div>
            <div style="font-size:.9rem;font-weight:600;"><?= date('j F Y, H:i', strtotime($order['created_at'])) ?></div>
          </div>
          <div>
            <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:4px;">Payment</div>
            <div style="font-size:.9rem;font-weight:600;"><?= ucfirst(str_replace('_',' ',$order['payment_method'])) ?></div>
          </div>
        </div>

        <?php if ($order['shipping_name']): ?>
        <hr class="divider">
        <div>
          <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:6px;">Delivery Address</div>
          <address style="font-style:normal;font-size:.88rem;line-height:1.7;color:var(--text-secondary);">
            <?= sanitize($order['shipping_name']) ?><br>
            <?= sanitize($order['shipping_address']) ?><br>
            <?= sanitize($order['shipping_city']) ?><?= $order['shipping_county'] ? ', '.sanitize($order['shipping_county']) : '' ?><br>
            <?= sanitize($order['shipping_postcode']) ?>, <?= sanitize($order['shipping_country']) ?>
          </address>
          <div style="margin-top:10px;font-size:.82rem;color:var(--text-muted);">
            📦 Estimated delivery: <strong style="color:var(--text-primary);"><?= date('D, j M', strtotime('+3 days')) ?> – <?= date('D, j M', strtotime('+5 days')) ?></strong>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Items -->
    <div class="card mb-4">
      <div class="card-header"><h2 style="font-size:1rem;margin:0;">🛍️ Items Ordered</h2></div>
      <div class="card-body" style="padding:0;">
        <div class="table-wrap" style="border:none;border-radius:0;">
          <table>
            <thead><tr><th>Product</th><th>Type</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
            <tbody>
              <?php foreach ($orderItems as $item): ?>
              <tr>
                <td>
                  <?php if ($item['product_slug']): ?>
                  <a href="product.php?slug=<?= $item['product_slug'] ?>" style="color:var(--text-primary);"><?= sanitize($item['product_name']) ?></a>
                  <?php else: ?>
                  <?= sanitize($item['product_name']) ?>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($item['product_type']==='digital'): ?>
                  <span class="pill pill-info">Digital</span>
                  <?php else: ?>
                  <span class="pill pill-gray">Physical</span>
                  <?php endif; ?>
                </td>
                <td><?= $item['quantity'] ?></td>
                <td><?= formatPrice((float)$item['unit_price']) ?></td>
                <td style="color:var(--primary);font-weight:600;"><?= formatPrice((float)$item['total_price']) ?></td>
              </tr>
              <?php if ($item['product_type']==='digital' && $item['download_token']): ?>
              <tr>
                <td colspan="5" style="padding:8px 16px 16px;">
                  <a href="download.php?token=<?= $item['download_token'] ?>" class="btn btn-primary btn-sm">📥 Download Now</a>
                  <span style="font-size:.75rem;color:var(--text-muted);margin-left:8px;">Link valid for 30 days</span>
                </td>
              </tr>
              <?php endif; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer" style="display:flex;flex-direction:column;gap:8px;align-items:flex-end;">
        <div class="order-summary-row" style="width:240px;padding:6px 0;border-color:var(--border);">
          <span>Subtotal</span><span><?= formatPrice((float)$order['subtotal']) ?></span>
        </div>
        <div class="order-summary-row" style="width:240px;padding:6px 0;border-color:var(--border);">
          <span>Shipping</span>
          <span><?= $order['shipping_cost']==0 ? '<span style="color:var(--success)">Free</span>' : formatPrice((float)$order['shipping_cost']) ?></span>
        </div>
        <div style="width:240px;display:flex;justify-content:space-between;font-size:1.1rem;font-weight:700;color:var(--primary);padding-top:10px;border-top:1px solid var(--border);">
          <span>Total</span><span><?= formatPrice((float)$order['total']) ?></span>
        </div>
      </div>
    </div>

    <!-- CTAs -->
    <div style="display:flex;gap:12px;flex-wrap:wrap;">
      <a href="account.php?tab=orders" class="btn btn-outline">📦 View My Orders</a>
      <a href="shop.php" class="btn btn-glass">Continue Shopping</a>
    </div>

    <?php if ($hasDigital): ?>
    <div class="alert alert-info mt-4">
      📄 Your digital downloads are available above and in <a href="account.php?tab=downloads" style="font-weight:600;">My Account → Downloads</a>.
    </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
