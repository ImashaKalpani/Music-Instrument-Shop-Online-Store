<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$db   = getDB();
$user = getCurrentUser();
$cart = getCart();
$flash = getFlash();

if (empty($cart)) {
    setFlash('warning', 'Your cart is empty.');
    header('Location: cart.php');
    exit;
}

$subtotal = getCartTotal();
$shipping = getShippingCost();
$total    = $subtotal + $shipping;
$errors   = [];
$step     = 1;

// ---- PROCESS CHECKOUT ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $fields = [
        'shipping_name'    => trim($_POST['shipping_name'] ?? ''),
        'shipping_address' => trim($_POST['shipping_address'] ?? ''),
        'shipping_city'    => trim($_POST['shipping_city'] ?? ''),
        'shipping_county'  => trim($_POST['shipping_county'] ?? ''),
        'shipping_postcode'=> trim($_POST['shipping_postcode'] ?? ''),
        'shipping_country' => trim($_POST['shipping_country'] ?? 'Sri Lanka'),
        'payment_method'   => trim($_POST['payment_method'] ?? ''),
        'notes'            => trim($_POST['notes'] ?? ''),
    ];

    $hasPhysical = false;
    foreach ($cart as $item) { if ($item['product_type']==='physical') { $hasPhysical=true; break; } }

    if ($hasPhysical) {
        if (!$fields['shipping_name'])     $errors[] = 'Full name is required.';
        if (!$fields['shipping_address'])  $errors[] = 'Address is required.';
        if (!$fields['shipping_city'])     $errors[] = 'City is required.';
        if (!$fields['shipping_postcode']) $errors[] = 'Postcode is required.';
    }
    if (!$fields['payment_method']) $errors[] = 'Please select a payment method.';

    if (empty($errors)) {
        try {
            $db->beginTransaction();
            $orderNo = generateOrderNumber();
            $insOrder = $db->prepare("INSERT INTO orders
              (user_id, order_number, status, subtotal, shipping_cost, total,
               shipping_name, shipping_address, shipping_city, shipping_county,
               shipping_postcode, shipping_country, payment_method, payment_status, notes)
              VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,'paid',?)");
            $insOrder->execute([
                $user['id'], $orderNo, 'processing',
                $subtotal, $shipping, $total,
                $fields['shipping_name'], $fields['shipping_address'],
                $fields['shipping_city'], $fields['shipping_county'],
                $fields['shipping_postcode'], $fields['shipping_country'],
                $fields['payment_method'], $fields['notes']
            ]);
            $orderId = $db->lastInsertId();

            foreach ($cart as $pid => $item) {
                $itemPrice = $item['sale_price'] ?? $item['price'];
                $token = $item['product_type'] === 'digital' ? generateToken(16) : null;
                $insItem = $db->prepare("INSERT INTO order_items
                  (order_id,product_id,product_name,quantity,unit_price,total_price,product_type,download_token)
                  VALUES (?,?,?,?,?,?,?,?)");
                $insItem->execute([
                    $orderId, $pid, $item['name'], $item['quantity'],
                    $itemPrice, $itemPrice * $item['quantity'],
                    $item['product_type'], $token
                ]);
                // Decrease stock for physical
                if ($item['product_type'] === 'physical') {
                    $upd = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?");
                    $upd->execute([$item['quantity'], $pid, $item['quantity']]);
                }
            }
            $db->commit();
            clearCart();
            header("Location: order_confirmation.php?order=$orderId");
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'Order processing failed. Please try again. (' . $e->getMessage() . ')';
        }
    }
}

$pageTitle = 'Checkout';
$metaDesc  = 'Complete your purchase at Melody Masters.';
include 'includes/header.php';
?>

<div class="page-banner" style="padding:36px 0;">
  <div class="container">
    <h1>Checkout</h1>
    <div class="breadcrumb" style="justify-content:center;">
      <a href="index.php">Home</a><span>›</span>
      <a href="cart.php">Cart</a><span>›</span>
      <span class="current">Checkout</span>
    </div>
  </div>
</div>

<!-- Checkout Steps indicator -->
<div style="background:var(--bg-card);border-bottom:1px solid var(--border);padding:16px 0;">
  <div class="container" style="display:flex;align-items:center;justify-content:center;gap:0;">
    <?php
    $steps = ['1'=>'Shipping','2'=>'Payment','3'=>'Review'];
    foreach($steps as $num=>$label):
    ?>
    <div style="display:flex;align-items:center;gap:0;">
      <div style="display:flex;align-items:center;gap:8px;padding:0 20px;">
        <div style="width:28px;height:28px;border-radius:50%;background:var(--primary);color:#0a0a0f;font-weight:700;font-size:.85rem;display:flex;align-items:center;justify-content:center;"><?= $num ?></div>
        <span style="font-size:.85rem;font-weight:600;color:var(--text-primary);"><?= $label ?></span>
      </div>
      <?php if ($num < 3): ?><div style="width:40px;height:2px;background:rgba(245,158,11,.3);"></div><?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="section">
  <div class="container">
    <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><?= sanitize($flash['message']) ?></div><?php endif; ?>
    <?php if ($errors): ?>
    <div class="alert alert-danger">
      <ul style="margin:0;padding-left:18px;"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <form method="POST">
    <div style="display:grid;grid-template-columns:1fr 360px;gap:32px;align-items:start;">

      <!-- Left: Forms -->
      <div>
        <?php
        $hasPhysical = false;
        foreach ($cart as $item) { if ($item['product_type']==='physical') { $hasPhysical=true; break; } }
        if ($hasPhysical):
        ?>
        <!-- Shipping Info -->
        <div class="card mb-4">
          <div class="card-header">
            <h2 style="font-size:1.05rem;margin:0;">🚚 Shipping Information</h2>
          </div>
          <div class="card-body">
            <div class="grid-2">
              <div class="form-group">
                <label class="form-label" for="shipping_name">Full Name *</label>
                <input type="text" id="shipping_name" name="shipping_name" class="form-control" required value="<?= sanitize($_POST['shipping_name'] ?? $user['first_name'].' '.$user['last_name']) ?>" placeholder="John Smith">
              </div>
              <div class="form-group">
                <input type="hidden" name="shipping_country" value="Sri Lanka">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="shipping_address">Address Line 1 *</label>
              <input type="text" id="shipping_address" name="shipping_address" class="form-control" required value="<?= sanitize($_POST['shipping_address'] ?? $user['address_line1'] ?? '') ?>" placeholder="123 High Street">
            </div>
            <div class="grid-2">
              <div class="form-group">
                <label class="form-label" for="shipping_city">City *</label>
                <input type="text" id="shipping_city" name="shipping_city" class="form-control" required value="<?= sanitize($_POST['shipping_city'] ?? $user['city'] ?? '') ?>" placeholder="London">
              </div>
              <div class="form-group">
                <label class="form-label" for="shipping_county">Country</label>
                <input type="text" id="shipping_county" name="shipping_county" class="form-control" value="<?= sanitize($_POST['shipping_county'] ?? $user['county'] ?? '') ?>" placeholder="Type Country">
              </div>
            </div>
            <div class="form-group" style="max-width:200px;">
              <label class="form-label" for="shipping_postcode">Postcode *</label>
              <input type="text" id="shipping_postcode" name="shipping_postcode" class="form-control" required value="<?= sanitize($_POST['shipping_postcode'] ?? $user['postcode'] ?? '') ?>" placeholder="SW1A 1AA">
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Payment Method -->
        <div class="card mb-4">
          <div class="card-header"><h2 style="font-size:1.05rem;margin:0;">Payment Method</h2></div>
          <div class="card-body">
            <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:16px;">This is a demo application. No real payment is processed.</p>
            <div style="display:flex;flex-direction:column;gap:12px;">
              <?php 
              $methods = [
                'card' => ['label' => 'Credit / Debit Card (Demo)', 'icon' => 'credit-card'],
                'paypal' => ['label' => 'PayPal (Demo)', 'icon' => 'wallet'],
                'bank_transfer' => ['label' => 'Bank Transfer (Demo)', 'icon' => 'landmark']
              ]; 
              ?>
              <?php foreach ($methods as $val => $m): ?>
              <div style="display:flex; flex-direction:column; gap:8px;">
                <label style="display:flex;align-items:center;gap:12px;padding:14px 16px;background:var(--bg-card2);border:1px solid var(--border);border-radius:var(--radius);cursor:pointer;transition:all .2s;" id="pm-<?= $val ?>">
                  <input type="radio" name="payment_method" value="<?= $val ?>" <?= (($_POST['payment_method']??'card')===$val)?'checked':'' ?> onchange="highlightPM()">
                  <i data-lucide="<?= $m['icon'] ?>" style="width:18px; height:18px; opacity:0.7;"></i>
                  <span style="font-size:.95rem; font-weight:600;"><?= $m['label'] ?></span>
                </label>
                
                <?php if ($val === 'card'): ?>
                <div id="pm-details-card" class="pm-details" style="display:none; padding:20px; background:var(--bg-card); border:1px solid var(--border); border-radius:16px; margin:0 4px 10px 4px; border-top:none; border-top-left-radius:0; border-top-right-radius:0;">
                  <div class="form-group">
                    <label class="form-label">Card Number</label>
                    <div style="position:relative;">
                      <input type="text" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19">
                      <div style="position:absolute; right:12px; top:50%; transform:translateY(-50%); display:flex; gap:6px;">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" style="height:12px; opacity:0.6;" alt="Visa">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" style="height:16px; opacity:0.6;" alt="Mastercard">
                      </div>
                    </div>
                  </div>
                  <div class="grid-2">
                    <div class="form-group">
                      <label class="form-label">Expiry Date</label>
                      <input type="text" class="form-control" placeholder="MM / YY" maxlength="5">
                    </div>
                    <div class="form-group">
                      <label class="form-label">CVV / CVC</label>
                      <input type="password" class="form-control" placeholder="123" maxlength="3">
                    </div>
                  </div>
                </div>
                <?php elseif ($val === 'paypal'): ?>
                <div id="pm-details-paypal" class="pm-details" style="display:none; padding:20px; background:var(--bg-card); border:1px solid var(--border); border-radius:16px; margin:0 4px 10px 4px; border-top:none; border-top-left-radius:0; border-top-right-radius:0;">
                  <div style="text-align:center; padding:10px;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" style="height:24px; margin-bottom:12px;" alt="PayPal">
                    <p style="font-size:0.85rem; color:var(--text-secondary); line-height:1.5;">You will be redirected to PayPal's secure site to complete your payment after clicking "Place Order".</p>
                  </div>
                </div>
                <?php elseif ($val === 'bank_transfer'): ?>
                <div id="pm-details-bank_transfer" class="pm-details" style="display:none; padding:20px; background:var(--bg-card); border:1px solid var(--border); border-radius:16px; margin:0 4px 10px 4px; border-top:none; border-top-left-radius:0; border-top-right-radius:0;">
                  <div style="background:var(--bg-card2); padding:16px; border-radius:12px; font-size:0.85rem; line-height:1.8;">
                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border); padding-bottom:4px; margin-bottom:4px;">
                      <span style="color:var(--text-muted);">Account Name:</span>
                      <span style="font-weight:600; color:var(--text-primary);">Melody Masters Store</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border); padding-bottom:4px; margin-bottom:4px;">
                      <span style="color:var(--text-muted);">Bank Name:</span>
                      <span style="font-weight:600; color:var(--text-primary);">Demo Commercial Bank</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border); padding-bottom:4px; margin-bottom:4px;">
                      <span style="color:var(--text-muted);">Account Number:</span>
                      <span style="font-weight:600; color:var(--text-primary);">1234 5678 9012</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                      <span style="color:var(--text-muted);">Sort Code:</span>
                      <span style="font-weight:600; color:var(--text-primary);">10-20-30</span>
                    </div>
                  </div>
                  <p style="font-size:0.75rem; color:var(--text-muted); margin-top:12px; text-align:center;">Please use your <strong>Order Number</strong> as the reference. Your order will be processed after the transfer is confirmed.</p>
                </div>
                <?php endif; ?>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="card">
          <div class="card-header"><h2 style="font-size:1.05rem;margin:0;">📝 Order Notes <span style="font-weight:400;color:var(--text-muted);font-size:.85rem;">(optional)</span></h2></div>
          <div class="card-body">
            <textarea name="notes" class="form-control" rows="3" placeholder="Special delivery instructions or notes for us..."><?= sanitize($_POST['notes'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- Right: Order Summary -->
      <div class="card" style="position:sticky;top:90px;">
        <div class="card-header"><h2 style="font-size:1.05rem;margin:0;">📋 Order Summary</h2></div>
        <div class="card-body">
          <?php foreach ($cart as $pid => $item):
            $ip = $item['sale_price'] ?? $item['price'];
          ?>
          <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border);">
            <div style="width:46px;height:46px;background:var(--bg-card2);border-radius:8px;overflow:hidden;flex-shrink:0;">
              <img src="assets/images/products/<?= $item['image'] ?>" onerror="this.src='assets/images/placeholder.svg'" style="width:100%;height:100%;object-fit:contain;padding:4px;" alt="">
            </div>
            <div style="flex:1;font-size:.82rem;color:var(--text-secondary);"><?= sanitize($item['name']) ?> × <?= $item['quantity'] ?></div>
            <div style="font-weight:600;font-size:.88rem;color:var(--primary);"><?= formatPrice($ip * $item['quantity']) ?></div>
          </div>
          <?php endforeach; ?>

          <div class="order-summary-row mt-2" style="padding-top:14px;">
            <span>Subtotal</span><span><?= formatPrice($subtotal) ?></span>
          </div>
          <div class="order-summary-row">
            <span>Shipping</span>
            <span><?= $shipping === 0.0 ? '<span style="color:var(--success)">Free</span>' : formatPrice($shipping) ?></span>
          </div>
          <div class="order-summary-row order-summary-total">
            <span>Total</span><span><?= formatPrice($total) ?></span>
          </div>
          <button type="submit" name="place_order" class="btn btn-primary btn-block btn-lg mt-3">
            🔒 Place Order – <?= formatPrice($total) ?>
          </button>
          <div style="text-align:center;margin-top:10px;font-size:.72rem;color:var(--text-muted);">By placing your order you agree to our Terms of Service.</div>
        </div>
      </div>
    </div>
    </form>
  </div>
</div>

<script>
function highlightPM() {
  const selected = document.querySelector('input[name="payment_method"]:checked').value;
  
  // Update labels style
  document.querySelectorAll('[id^="pm-"]').forEach(el => {
    const isChecked = el.querySelector('input').checked;
    el.style.borderColor = isChecked ? 'var(--primary)' : 'var(--border)';
    el.style.background = isChecked ? 'rgba(37,99,235,0.05)' : 'var(--bg-card2)';
  });
  
  // Hide all details and show selected
  document.querySelectorAll('.pm-details').forEach(el => el.style.display = 'none');
  const detailsEl = document.getElementById('pm-details-' + selected);
  if (detailsEl) detailsEl.style.display = 'block';
}
document.addEventListener('DOMContentLoaded', highlightPM);
</script>

<?php include 'includes/footer.php'; ?>
