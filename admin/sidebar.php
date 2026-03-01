<?php
// Admin Sidebar Include
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<aside class="admin-sidebar">
    <div class="admin-sidebar-logo">
        <a href="<?= SITE_URL ?>" style="color:var(--primary);text-decoration:none;"> <?= SITE_NAME ?> <span style="font-size:.7rem;opacity:.6;display:block;font-family:'Inter',sans-serif;">Admin Panel</span></a>
    </div>
    
    <div class="admin-nav-section">Main Menu</div>
    <a href="index.php" class="admin-nav-link <?= $currentPage==='index'?'active':'' ?>"> Dashboard</a>
    <a href="orders.php" class="admin-nav-link <?= $currentPage==='orders'?'active':'' ?>"> Orders</a>
    <a href="products.php" class="admin-nav-link <?= $currentPage==='products'?'active':'' ?>"> Products</a>
    <a href="categories.php" class="admin-nav-link <?= $currentPage==='categories'?'active':'' ?>"> Categories</a>
    <a href="reviews.php" class="admin-nav-link <?= $currentPage==='reviews'?'active':'' ?>"> Reviews</a>
    
    <div class="admin-nav-section">Users</div>
    <a href="users.php" class="admin-nav-link <?= $currentPage==='users'?'active':'' ?>"> All Users</a>
    
    <div class="admin-nav-section" style="margin-top:auto;">System</div>
    <a href="<?= SITE_URL ?>/index.php" class="admin-nav-link"> View Store</a>
    <a href="<?= SITE_URL ?>/logout.php" class="admin-nav-link" style="color:var(--danger);"> Logout</a>
</aside>
