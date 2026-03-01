<?php
require_once __DIR__ . '/../includes/functions.php';
$user  = getCurrentUser();
$cart  = getCart();
$cCount = getCartCount();

// Active nav detection
$page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?= $metaDesc ?? 'Melody Masters – Your premier online music instrument store. Shop guitars, keyboards, drums, accessories and more.' ?>">
<title><?= isset($pageTitle) ? sanitize($pageTitle) . ' | ' . SITE_NAME : SITE_NAME . ' – Music Instrument Shop' ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎸</text></svg>">
</head>
<body>

<!-- Promo Bar Removed for cleaner look -->

<!-- Navbar -->
<nav class="navbar">
  <div class="container">
    <a href="<?= SITE_URL ?>" class="navbar-logo">
      <div>
        <span class="navbar-logo-text"><?= SITE_NAME ?></span>
        <span class="navbar-logo-sub">Instrument Shop</span>
      </div>
    </a>

    <ul class="navbar-nav">
      <li><a href="<?= SITE_URL ?>/index.php" class="nav-link <?= $page==='index'?'active':'' ?>">Home</a></li>
      <li><a href="<?= SITE_URL ?>/shop.php" class="nav-link <?= $page==='shop'?'active':'' ?>">Shop</a></li>
    </ul>

    <div class="navbar-actions" style="display:flex; align-items:center; gap:20px;">
      <form class="navbar-search" action="<?= SITE_URL ?>/shop.php" method="GET" style="position:relative; display:flex; align-items:center;">
        <i data-lucide="search" style="position:absolute; left:16px; width:20px; height:20px; color:var(--text-muted); pointer-events:none;"></i>
        <input type="text" name="q" placeholder="Search instruments..." value="<?= sanitize($_GET['q'] ?? '') ?>" style="background:var(--bg-card); border:1px solid var(--border); padding:10px 16px 10px 42px; border-radius:30px; color:var(--text-primary); font-size:0.95rem; outline:none; transition:all 0.3s ease; width:300px;" onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)';" onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none';">
      </form>

      <a href="<?= SITE_URL ?>/cart.php" class="cart-btn" style="position:relative; display:flex; justify-content:center; align-items:center; width:50px; height:50px; border-radius:50%; background:var(--glass-bg); border:1px solid var(--border); color:var(--text-primary); transition:all 0.3s ease;" onmouseover="this.style.background='var(--primary)'; this.style.color='#fff'; this.style.borderColor='var(--primary)';" onmouseout="this.style.background='var(--glass-bg)'; this.style.color='var(--text-primary)'; this.style.borderColor='var(--border)';">
        <i data-lucide="shopping-cart" style="width:24px; height:24px;"></i>
        <?php if ($cCount > 0): ?>
        <span class="cart-count" style="position:absolute; top:-5px; right:-5px; background:var(--primary); color:#fff; font-size:0.75rem; font-weight:700; width:22px; height:22px; border-radius:50%; display:flex; justify-content:center; align-items:center; border:2px solid var(--bg-dark);"><?= $cCount ?></span>
        <?php endif; ?>
      </a>

      <?php if ($user): ?>
        <div style="position:relative;" id="userMenuWrap">
          <button onclick="toggleUserMenu()" style="background:rgba(37, 99, 235, .15);border:1px solid rgba(37, 99, 235, .3);border-radius:40px;padding:8px 16px;color:var(--primary);cursor:pointer;font-size:.9rem;font-family:inherit;display:flex;align-items:center;gap:8px;">
            <i data-lucide="user" style="width:16px; height:16px;"></i> <?= sanitize($user['first_name']) ?> <span style="font-size:10px;">▼</span>
          </button>
          <div id="userMenu" style="display:none;position:absolute;right:0;top:50px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);min-width:180px;overflow:hidden;z-index:100;box-shadow:var(--shadow-card);">
            <a href="<?= SITE_URL ?>/account.php" style="display:flex;align-items:center;gap:10px;padding:12px 18px;font-size:.85rem;color:var(--text-secondary);border-bottom:1px solid var(--border);transition:all .2s;"><i data-lucide="package" style="width:16px; height:16px;"></i> My Account</a>
            <?php if (isStaff()): ?>
            <a href="<?= SITE_URL ?>/admin/index.php" style="display:flex;align-items:center;gap:10px;padding:12px 18px;font-size:.85rem;color:var(--text-secondary);border-bottom:1px solid var(--border);transition:all .2s;"><i data-lucide="settings" style="width:16px; height:16px;"></i> Admin Panel</a>
            <?php endif; ?>
            <a href="<?= SITE_URL ?>/logout.php" style="display:flex;align-items:center;gap:10px;padding:12px 18px;font-size:.85rem;color:var(--danger);transition:all .2s;"><i data-lucide="log-out" style="width:16px; height:16px;"></i> Logout</a>
          </div>
        </div>
      <?php else: ?>
        <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline btn-sm">Sign In</a>
      <?php endif; ?>

      <button class="hamburger" onclick="toggleMobileNav()" id="hamburgerBtn">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</nav>

<!-- Mobile Nav -->
<div class="mobile-nav" id="mobileNav">
  <div style="padding:16px 24px; border-bottom:1px solid var(--border);">
      <form action="<?= SITE_URL ?>/shop.php" method="GET" style="position:relative; display:flex; align-items:center; width:100%;">
        <i data-lucide="search" style="position:absolute; left:16px; width:18px; height:18px; color:var(--text-muted); pointer-events:none;"></i>
        <input type="text" name="q" placeholder="Search instruments..." value="<?= sanitize($_GET['q'] ?? '') ?>" style="width:100%; background:var(--bg-card); border:1px solid var(--border); padding:10px 16px 10px 42px; border-radius:30px; color:var(--text-primary); font-size:0.9rem; outline:none;">
      </form>
  </div>
  <a href="<?= SITE_URL ?>/index.php" class="nav-link"><i data-lucide="home" style="width:18px; margin-right:8px;"></i> Home</a>
  <a href="<?= SITE_URL ?>/shop.php" class="nav-link"><i data-lucide="shopping-bag" style="width:18px; margin-right:8px;"></i> Shop</a>
  <a href="<?= SITE_URL ?>/cart.php" class="nav-link"><i data-lucide="shopping-cart" style="width:18px; margin-right:8px;"></i> Cart (<?= $cCount ?>)</a>
  <?php if ($user): ?>
    <a href="<?= SITE_URL ?>/account.php" class="nav-link"><i data-lucide="user" style="width:18px; margin-right:8px;"></i> My Account</a>
    <a href="<?= SITE_URL ?>/logout.php" class="nav-link" style="color:var(--danger)"><i data-lucide="log-out" style="width:18px; margin-right:8px;"></i> Logout</a>
  <?php else: ?>
    <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline btn-sm mt-2">Sign In</a>
    <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary btn-sm mt-1">Register</a>
  <?php endif; ?>
</div>

<script>
function toggleMobileNav() {
  document.getElementById('mobileNav').classList.toggle('open');
  const spans = document.getElementById('hamburgerBtn').children;
  // Animation logic could go here
}

function toggleUserMenu() {
  const menu = document.getElementById('userMenu');
  menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

// Close menus on click outside
window.addEventListener('click', function(e) {
  const userMenu = document.getElementById('userMenu');
  const wrap = document.getElementById('userMenuWrap');
  if (userMenu && !wrap.contains(e.target)) {
    userMenu.style.display = 'none';
  }
});
</script>

<main>
