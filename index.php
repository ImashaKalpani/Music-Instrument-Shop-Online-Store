<?php
require_once __DIR__ . '/includes/functions.php';
$db = getDB();
$flash = getFlash();

// Fetch featured products
$featured = $db->query("SELECT p.*, c.name as cat_name,
  COALESCE(AVG(r.rating),0) as avg_rating,
  COUNT(r.id) as review_count
  FROM products p
  LEFT JOIN categories c ON p.category_id = c.id
  LEFT JOIN reviews r ON r.product_id = p.id AND r.is_approved = 1
  WHERE p.is_featured = 1 AND p.is_active = 1
  GROUP BY p.id ORDER BY p.created_at DESC LIMIT 6")->fetchAll();

// Fetch parent categories with product counts
$categories = $db->query("SELECT c.*, COUNT(p.id) as product_count
  FROM categories c
  LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
  WHERE c.parent_id IS NULL AND c.is_active = 1
  GROUP BY c.id ORDER BY c.sort_order")->fetchAll();

// Fetch new arrivals
$newArrivals = $db->query("SELECT p.*, c.name as cat_name
  FROM products p LEFT JOIN categories c ON p.category_id=c.id
  WHERE p.is_active=1 ORDER BY p.created_at DESC LIMIT 4")->fetchAll();

// Fetch Best Sellers (Specific: Guitar, Violin, Piano)
$bestSellers = $db->query("SELECT p.*, c.name as cat_name 
  FROM products p 
  LEFT JOIN categories c ON p.category_id = c.id
  WHERE p.slug IN ('fender-cd-60s-acoustic', 'stentor-student-ii-violin', 'roland-fp-30x-digital-piano')
  AND p.is_active = 1
  ORDER BY FIELD(p.slug, 'fender-cd-60s-acoustic', 'stentor-student-ii-violin', 'roland-fp-30x-digital-piano')
  LIMIT 3")->fetchAll();

// Fallback to any featured if specific ones not found
if (count($bestSellers) < 3) {
    $bestSellers = $db->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.is_featured=1 AND p.is_active=1 LIMIT 3")->fetchAll();
}

function renderStars(float $rating): string {
    $html = '<div class="product-card-stars" style="display:flex; gap:2px; color:var(--accent);">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $html .= '<i data-lucide="star" style="width:14px;height:14px;fill:currentColor;"></i>';
        } else if ($i - 0.5 <= $rating) {
            $html .= '<i data-lucide="star-half" style="width:14px;height:14px;fill:currentColor;"></i>';
        } else {
            $html .= '<i data-lucide="star" style="width:14px;height:14px;opacity:0.2;"></i>';
        }
    }
    $html .= '</div>';
    return $html;
}
?>
<?php
$pageTitle = 'Home – Premium Musical Instruments';
$metaDesc  = 'Shop guitars, keyboards, drums, wind instruments, accessories and digital sheet music at Melody Masters.';
include 'includes/header.php';
?>

<?php if ($flash): ?>
<div class="container mt-3">
  <div class="alert alert-<?= $flash['type'] ?>"><?= sanitize($flash['message']) ?></div>
</div>
<?php endif; ?>

<!-- ============================================================
     HERO SECTION (Enhanced 2-Column)
     ============================================================ -->
<section class="hero" style="min-height:90vh; display:flex; align-items:center; overflow:hidden; position:relative; padding-top:var(--header-h); background:radial-gradient(ellipse at 50% 0%, rgba(37,99,235,0.05) 0%, transparent 60%), radial-gradient(ellipse at 80% 80%, rgba(139,92,246,0.05) 0%, transparent 50%), var(--bg-dark);">
  <div class="container" style="position:relative; z-index:2;">
    <div style="display:grid; grid-template-columns:1.2fr 1fr; gap:60px; align-items:center;">
      
      <!-- Left: Content -->
      <div class="fade-up" style="animation: fadeUp 1s ease-out forwards;">
        <div class="section-eyebrow" style="margin-bottom:28px; display:inline-flex; align-items:center; gap:10px; padding:8px 20px; background:rgba(37,99,235,0.1); border:1px solid rgba(37,99,235,0.2); border-radius:30px; font-weight:700; font-size:0.85rem; letter-spacing:1.5px; text-transform:uppercase; color:var(--primary); box-shadow:0 4px 15px rgba(37,99,235,0.1); backdrop-filter:blur(8px);">
            <i data-lucide="award" style="width:18px;height:18px;"></i> Premium Music Gear
        </div>
        <h1 class="hero-title" style="font-size:clamp(3rem, 5vw, 4.5rem); line-height:1.05; margin-bottom:28px; font-weight:900; letter-spacing:-1px;">
          Elevate Your <br>
          <span class="highlight" style="background:linear-gradient(135deg, #2563eb 0%, #8b5cf6 50%, #ec4899 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-size:200% 200%; animation:gradientText 5s ease infinite;">Musical Journey</span>
        </h1>
        <p class="hero-desc" style="font-size:1.25rem; margin-bottom:48px; color:var(--text-secondary); line-height:1.7; max-width:540px;">
          Explore our curated collection of professional instruments and digital sheet music designed for the modern musician to inspire creativity.
        </p>
        <div class="hero-actions" style="display:flex; gap:16px; flex-wrap:wrap;">
          <a href="shop.php" class="btn btn-primary btn-lg" style="border-radius:40px; padding:16px 36px; font-size:1.1rem; box-shadow:0 12px 30px rgba(37,99,235,0.3); transition:all .3s ease; display:flex; align-items:center; gap:10px;">
              <i data-lucide="compass"></i> Discover Shop
          </a>
          <a href="shop.php?category=digital-sheet-music" class="btn btn-outline btn-lg" style="border-radius:40px; padding:16px 36px; font-size:1.1rem; border:2px solid var(--border); background:var(--glass-bg); backdrop-filter:blur(10px); color:var(--text-primary); transition:all .3s ease; display:flex; align-items:center; gap:10px;">
              <i data-lucide="file-audio-2"></i> Sheet Music
          </a>
        </div>
      </div>

      <!-- Right: Animated Visual -->
      <div style="position:relative; height:650px; display:flex; align-items:center; justify-content:center; perspective:1000px;" class="hero-visual-container">
        
        <!-- Glow Behind -->
        <div style="position:absolute; width:500px; height:500px; background:radial-gradient(circle, rgba(37,99,235,0.4) 0%, rgba(139,92,246,0.3) 30%, transparent 70%); border-radius:50%; filter:blur(40px); animation:pulseGlow 10s ease-in-out infinite;"></div>
        
        <!-- Main Transparent Instrument Image -->
        <img src="assets/images/products/hero.png" alt="Collection of Music Instruments" style="position:relative; z-index:4; width:130%; max-width:none; left:-15%; top:-10%; filter:drop-shadow(0 30px 40px rgba(0,0,0,0.3)); transform:rotate(2deg); animation:floatMain 6s ease-in-out infinite; transform-origin:center;" class="hero-img-main-transparent">
        
        <!-- Small Decorative Floating Elements -->
        <div style="position:absolute; top:15%; right:10%; width:8px; height:8px; background:var(--primary); border-radius:50%; z-index:3; animation:pulseGlow 2s infinite alternate;"></div>
        <div style="position:absolute; bottom:25%; left:15%; width:12px; height:12px; border:2px solid var(--secondary); border-radius:50%; z-index:3; animation:pulseGlow 3s infinite alternate-reverse;"></div>

      </div>

    </div>
  </div>
</section>

<style>
  @keyframes gradientText {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes pulseGlow {
    0% { opacity: 0.15; transform: scale(1); }
    50% { opacity: 0.25; transform: scale(1.1); }
    100% { opacity: 0.15; transform: scale(1); }
  }
  @keyframes floatMain {
    0% { transform: rotate(0deg) translateY(0); }
    50% { transform: rotate(0deg) translateY(-15px); }
    100% { transform: rotate(0deg) translateY(0); }
  }
</style>

<!-- ============================================================
     TRUST STRIP
     ============================================================ -->
<!-- ============================================================
     TRUST STRIP (Modernized)
     ============================================================ -->
<section style="padding:60px 0; background:linear-gradient(to bottom, var(--bg-dark), var(--bg-card)); position:relative;">
  <div class="container">
    <div class="grid-4" style="gap:24px;">
      <div class="trust-card-premium" style="display:flex; align-items:center; gap:20px; padding:28px 24px; background:var(--glass-bg); backdrop-filter:blur(10px); border-radius:24px; border:1px solid var(--border); transition:all .4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position:relative; overflow:hidden;">
        <div style="position:absolute; top:0; left:0; width:100%; height:4px; background:linear-gradient(90deg, transparent, var(--primary), transparent); transform:scaleX(0); transition:transform .4s ease; transform-origin:left;" class="trust-line"></div>
        <div style="width:64px; height:64px; background:linear-gradient(135deg, rgba(37,99,235,0.1), rgba(37,99,235,0.05)); border-radius:20px; display:flex; align-items:center; justify-content:center; color:var(--primary); box-shadow:inset 0 0 0 1px rgba(37,99,235,0.15); transition:all .3s ease;" class="trust-icon-box"><i data-lucide="truck" style="width:28px; height:28px;"></i></div>
        <div>
          <div style="font-weight:800; font-size:1.05rem; color:var(--text-primary); margin-bottom:4px; letter-spacing:0.5px;">Free Shipping</div>
          <div style="font-size:0.85rem; color:var(--text-muted); font-weight:500;">On orders over £100</div>
        </div>
      </div>
      <div class="trust-card-premium" style="display:flex; align-items:center; gap:20px; padding:28px 24px; background:var(--glass-bg); backdrop-filter:blur(10px); border-radius:24px; border:1px solid var(--border); transition:all .4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position:relative; overflow:hidden;">
        <div style="position:absolute; top:0; left:0; width:100%; height:4px; background:linear-gradient(90deg, transparent, var(--secondary), transparent); transform:scaleX(0); transition:transform .4s ease; transform-origin:left;" class="trust-line"></div>
        <div style="width:64px; height:64px; background:linear-gradient(135deg, rgba(99,102,241,0.1), rgba(99,102,241,0.05)); border-radius:20px; display:flex; align-items:center; justify-content:center; color:var(--secondary); box-shadow:inset 0 0 0 1px rgba(99,102,241,0.15); transition:all .3s ease;" class="trust-icon-box"><i data-lucide="refresh-cw" style="width:28px; height:28px;"></i></div>
        <div>
          <div style="font-weight:800; font-size:1.05rem; color:var(--text-primary); margin-bottom:4px; letter-spacing:0.5px;">30-Day Returns</div>
          <div style="font-size:0.85rem; color:var(--text-muted); font-weight:500;">Hassle-free returns</div>
        </div>
      </div>
      <div class="trust-card-premium" style="display:flex; align-items:center; gap:20px; padding:28px 24px; background:var(--glass-bg); backdrop-filter:blur(10px); border-radius:24px; border:1px solid var(--border); transition:all .4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position:relative; overflow:hidden;">
        <div style="position:absolute; top:0; left:0; width:100%; height:4px; background:linear-gradient(90deg, transparent, var(--success), transparent); transform:scaleX(0); transition:transform .4s ease; transform-origin:left;" class="trust-line"></div>
        <div style="width:64px; height:64px; background:linear-gradient(135deg, rgba(16,185,129,0.1), rgba(16,185,129,0.05)); border-radius:20px; display:flex; align-items:center; justify-content:center; color:var(--success); box-shadow:inset 0 0 0 1px rgba(16,185,129,0.15); transition:all .3s ease;" class="trust-icon-box"><i data-lucide="shield-check" style="width:28px; height:28px;"></i></div>
        <div>
          <div style="font-weight:800; font-size:1.05rem; color:var(--text-primary); margin-bottom:4px; letter-spacing:0.5px;">Secure Payment</div>
          <div style="font-size:0.85rem; color:var(--text-muted); font-weight:500;">256-bit SSL encryption</div>
        </div>
      </div>
      <div class="trust-card-premium" style="display:flex; align-items:center; gap:20px; padding:28px 24px; background:var(--glass-bg); backdrop-filter:blur(10px); border-radius:24px; border:1px solid var(--border); transition:all .4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position:relative; overflow:hidden;">
        <div style="position:absolute; top:0; left:0; width:100%; height:4px; background:linear-gradient(90deg, transparent, #ec4899, transparent); transform:scaleX(0); transition:transform .4s ease; transform-origin:left;" class="trust-line"></div>
        <div style="width:64px; height:64px; background:linear-gradient(135deg, rgba(236,72,153,0.1), rgba(236,72,153,0.05)); border-radius:20px; display:flex; align-items:center; justify-content:center; color:#ec4899; box-shadow:inset 0 0 0 1px rgba(236,72,153,0.15); transition:all .3s ease;" class="trust-icon-box"><i data-lucide="headphones" style="width:28px; height:28px;"></i></div>
        <div>
          <div style="font-weight:800; font-size:1.05rem; color:var(--text-primary); margin-bottom:4px; letter-spacing:0.5px;">Expert Support</div>
          <div style="font-size:0.85rem; color:var(--text-muted); font-weight:500;">Mon – Sat, 9am – 6pm</div>
        </div>
      </div>
    </div>
  </div>
</section>
<style>
  .trust-card-premium:hover { transform: translateY(-8px); box-shadow: 0 15px 35px rgba(0,0,0,0.08); background: var(--bg-card); border-color:transparent; }
  .trust-card-premium:hover .trust-line { transform: scaleX(1); }
  .trust-card-premium:hover .trust-icon-box { transform: scale(1.1) rotate(5deg); box-shadow: 0 5px 15px rgba(0,0,0,0.05) inset; }
</style>

<!-- ============================================================
     CATEGORIES (Premium Grid Display)
     ============================================================ -->
<section class="section" style="padding:100px 0; background:var(--bg-card);">
  <div class="container">
    <div class="section-header" style="text-align:center; margin-bottom:60px;">
      <h2 style="font-size:2.5rem; margin-bottom:16px; font-weight:800; letter-spacing:-0.5px;">Explore Our <span style="background:linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">Categories</span></h2>
      <p style="font-size:1.1rem; color:var(--text-secondary); max-width:600px; margin:0 auto;">Find exactly what you're looking for with our curated selection of top-tier instruments and accessories.</p>
    </div>
    
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:24px;">
      <?php 
      $imageMap = [
        'guitars' => 'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?q=80&w=600&auto=format&fit=crop',
        'keyboards-pianos' => 'https://images.unsplash.com/photo-1520523839897-bd0b52f945a0?q=80&w=600&auto=format&fit=crop',
        'drums-percussion' => 'https://images.unsplash.com/photo-1519892300165-cb5542fb47c7?q=80&w=600&auto=format&fit=crop',
        'wind-instruments' => 'https://images.unsplash.com/photo-1573871666457-7c7329118cf9?q=80&w=600&auto=format&fit=crop',
        'string-instruments' => 'https://images.unsplash.com/photo-1612225330812-01a9c6b355ec?q=80&w=600&auto=format&fit=crop',
        'accessories' => 'https://images.unsplash.com/photo-1511379938547-c1f69419868d?q=80&w=600&auto=format&fit=crop',
        'digital-sheet-music' => 'https://images.unsplash.com/photo-1507838153414-b4b713384a76?q=80&w=600&auto=format&fit=crop'
      ];
      $colorVars = ['#2563eb', '#6366f1', '#ec4899', '#10b981', '#f59e0b', '#0ea5e9', '#8b5cf6'];
      
      $idx = 0;
      foreach ($categories as $cat): 
        $imageUrl = isset($imageMap[$cat['slug']]) ? $imageMap[$cat['slug']] : 'https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?q=80&w=600&auto=format&fit=crop';
        $cColor = $colorVars[$idx % count($colorVars)];
        $idx++;
      ?>
      <a href="shop.php?category=<?= $cat['slug'] ?>" class="cat-landscape-card" style="--btn-color: <?= $cColor ?>; position:relative; border-radius:16px; overflow:hidden; text-decoration:none; display:block; height:240px; box-shadow:0 10px 30px rgba(0,0,0,0.08); transition:all 0.5s cubic-bezier(0.25, 1, 0.5, 1);">
        
        <!-- Background Image -->
        <img src="<?= $imageUrl ?>" alt="<?= sanitize($cat['name']) ?>" style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; transition:transform 0.8s ease;" class="cat-bg-img">
        
        <!-- Premium Gradient Overlays -->
        <div style="position:absolute; inset:0; background:linear-gradient(to top, rgba(15,23,42,0.9) 0%, rgba(15,23,42,0.4) 50%, rgba(15,23,42,0.1) 100%); transition:all 0.5s ease;" class="cat-overlay"></div>
        <div style="position:absolute; inset:0; background:linear-gradient(135deg, <?= $cColor ?> 0%, transparent 100%); opacity:0; mix-blend-mode:overlay; transition:all 0.5s ease;" class="cat-color-overlay"></div>
        
        <!-- Content -->
        <div style="position:absolute; inset:0; padding:30px; display:flex; flex-direction:column; justify-content:flex-end; z-index:2;">
            <div style="transform:translateY(15px); transition:transform 0.5s ease;" class="cat-content-slide">
              <h3 style="font-size:1.5rem; font-weight:800; color:#fff; margin-bottom:8px; text-shadow:0 2px 10px rgba(0,0,0,0.3); letter-spacing:0.5px;"><?= sanitize($cat['name']) ?></h3>
              <div style="display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:0.95rem; color:rgba(255,255,255,0.8); font-weight:500; font-family:monospace; letter-spacing:1px;"><?= sprintf('%02d', $cat['product_count']) ?> Items</span>
                <div style="width:40px; height:40px; border-radius:50%; background:rgba(255,255,255,0.1); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; color:#fff; transition:all 0.3s ease; border:1px solid rgba(255,255,255,0.2);" class="cat-action-btn">
                  <i data-lucide="arrow-up-right" style="width:20px; height:20px;"></i>
                </div>
              </div>
            </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<style>
  .cat-landscape-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
  }
  .cat-landscape-card:hover .cat-bg-img {
    transform: scale(1.1);
  }
  .cat-landscape-card:hover .cat-overlay {
    background: linear-gradient(to top, rgba(15,23,42,0.95) 0%, rgba(15,23,42,0.6) 50%, rgba(15,23,42,0.2) 100%);
  }
  .cat-landscape-card:hover .cat-color-overlay {
    opacity: 0.8;
  }
  .cat-landscape-card:hover .cat-content-slide {
    transform: translateY(0);
  }
  .cat-landscape-card:hover .cat-action-btn {
    background: var(--btn-color);
    border-color: var(--btn-color);
    transform: rotate(45deg);
  }
</style>

<!-- ============================================================
     BEST SELLERS SECTION
     ============================================================ -->
<section class="section" style="padding:100px 0; background:var(--bg-dark);">
  <div class="container">
    <div class="section-header" style="text-align:center; margin-bottom:60px;">
      <h2 style="font-size:2.5rem; margin-bottom:16px; font-weight:800; letter-spacing:-0.5px;">Best <span style="background:linear-gradient(135deg, var(--secondary), #ec4899); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">Sellers</span></h2>
      <p style="font-size:1.1rem; color:var(--text-secondary); max-width:600px; margin:0 auto;">Our most popular instruments loved by musicians worldwide.</p>
    </div>

    <div class="grid-3" style="gap:30px;">
      <?php foreach ($bestSellers as $p): ?>
      <div class="product-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:24px; overflow:hidden; transition:all 0.4s ease; position:relative;" onmouseover="this.style.transform='translateY(-10px)'; this.style.borderColor='var(--primary)';" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='var(--border)';">
        <a href="product.php?slug=<?= $p['slug'] ?>" style="text-decoration:none; display:block;">
          <div style="height:280px; background:var(--bg-card2); padding:30px; display:flex; align-items:center; justify-content:center; position:relative; overflow:hidden;">
            <img src="assets/images/products/<?= $p['image'] ?>" onerror="this.src='assets/images/placeholder.svg'" style="max-width:100%; max-height:100%; object-fit:contain; transition:transform 0.5s ease;" alt="<?= sanitize($p['name']) ?>">
            <div style="position:absolute; top:20px; right:20px; background:rgba(37,99,235,0.1); backdrop-filter:blur(8px); padding:6px 14px; border-radius:20px; color:var(--primary); font-size:0.75rem; font-weight:700; border:1px solid rgba(37,99,235,0.2);">
              Best Seller
            </div>
          </div>
          <div style="padding:24px;">
            <div style="font-size:0.75rem; font-weight:700; color:var(--primary); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;"><?= sanitize($p['cat_name']) ?></div>
            <h3 style="font-size:1.15rem; margin-bottom:12px; color:var(--text-primary); font-weight:700; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= sanitize($p['name']) ?></h3>
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:16px;">
               <div style="display:flex; color:var(--accent);">
                 <i data-lucide="star" style="width:14px; height:14px; fill:currentColor;"></i>
                 <i data-lucide="star" style="width:14px; height:14px; fill:currentColor;"></i>
                 <i data-lucide="star" style="width:14px; height:14px; fill:currentColor;"></i>
                 <i data-lucide="star" style="width:14px; height:14px; fill:currentColor;"></i>
                 <i data-lucide="star" style="width:14px; height:14px; fill:currentColor;"></i>
               </div>
               <span style="font-size:0.8rem; color:var(--text-muted);">(5.0)</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <div style="font-size:1.3rem; font-weight:800; color:var(--text-primary);"><?= formatPrice($p['sale_price'] ?? $p['price']) ?></div>
              <div style="color:var(--primary);"><i data-lucide="shopping-cart"></i></div>
            </div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
    
    <div style="text-align:center; margin-top:50px;">
      <a href="shop.php" class="btn btn-outline" style="padding:14px 40px; border-radius:30px; font-weight:700;">View All Products</a>
    </div>
  </div>
</section>

<!-- ============================================================
     PREMIUM PROMO BANNER (The Art of Sound)
     ============================================================ -->
<section style="position:relative; margin:100px 0; background:#0f172a; overflow:hidden; border-top:1px solid rgba(255,255,255,0.05); border-bottom:1px solid rgba(255,255,255,0.05);">
  
  <!-- Parallax/Fixed Background Image -->
  <div style="position:absolute; inset:0; z-index:0;">
    <img src="https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?q=80&w=1920&auto=format&fit=crop" style="width:100%; height:100%; object-fit:cover; opacity:0.15; filter:grayscale(50%) contrast(1.2);" alt="Music Studio Experience">
    <div style="position:absolute; inset:0; background:linear-gradient(to right, rgba(15,23,42,1) 0%, rgba(15,23,42,0.8) 50%, rgba(15,23,42,0.4) 100%);"></div>
  </div>

  <div class="container" style="position:relative; z-index:2; padding:80px 0;">
    <div style="display:flex; flex-wrap:wrap; align-items:center; gap:60px;">
      
      <!-- Text Content -->
      <div style="flex:1; min-width:300px;">
        <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(236,72,153,0.1); border:1px solid rgba(236,72,153,0.2); padding:6px 16px; border-radius:30px; color:#ec4899; font-weight:700; font-size:0.85rem; letter-spacing:1px; text-transform:uppercase; margin-bottom:24px;">
          <i data-lucide="sparkles" style="width:16px; height:16px;"></i> The Art of Sound
        </div>
        <h2 style="font-size:clamp(2.5rem, 4vw, 3.5rem); font-weight:900; color:#fff; line-height:1.1; margin-bottom:24px; letter-spacing:-1px;">
          Crafted for the <br> <span style="background:linear-gradient(135deg, #ec4899, #8b5cf6); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">True Artist</span>
        </h2>
        <p style="font-size:1.15rem; color:rgba(255,255,255,0.7); line-height:1.7; margin-bottom:40px; max-width:500px;">
          Experience unparalleled resonance and master craftsmanship. Our premium collection is hand-selected by professionals to ensure you find your perfect voice.
        </p>
        <div style="display:flex; gap:16px;">
          <a href="shop.php" style="background:#fff; color:#0f172a; padding:16px 32px; border-radius:30px; font-weight:700; text-decoration:none; transition:all 0.3s ease; display:flex; align-items:center; gap:8px;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 20px rgba(255,255,255,0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
            Explore Collection <i data-lucide="arrow-right" style="width:18px; height:18px;"></i>
          </a>
        </div>
      </div>
      
      <!-- Interactive Grid Layout -->
      <div style="flex:1; min-width:300px; display:grid; grid-template-columns:1fr 1fr; gap:20px;">
         
         <div style="background:rgba(255,255,255,0.03); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.05); padding:30px; border-radius:24px; transform:translateY(20px); transition:all 0.4s ease;" class="promo-card">
           <div style="width:50px; height:50px; background:rgba(37,99,235,0.1); color:#3b82f6; border-radius:12px; display:flex; align-items:center; justify-content:center; margin-bottom:20px;">
             <i data-lucide="headphones" style="width:24px; height:24px;"></i>
           </div>
           <h4 style="color:#fff; font-size:1.2rem; font-weight:700; margin-bottom:10px;">Studio Quality</h4>
           <p style="color:rgba(255,255,255,0.6); font-size:0.95rem; line-height:1.6;">High-fidelity gear built to capture every nuance in the studio or on stage.</p>
         </div>

         <div style="background:rgba(255,255,255,0.03); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.05); padding:30px; border-radius:24px; transition:all 0.4s ease;" class="promo-card">
           <div style="width:50px; height:50px; background:rgba(236,72,153,0.1); color:#ec4899; border-radius:12px; display:flex; align-items:center; justify-content:center; margin-bottom:20px;">
             <i data-lucide="award" style="width:24px; height:24px;"></i>
           </div>
           <h4 style="color:#fff; font-size:1.2rem; font-weight:700; margin-bottom:10px;">Master Built</h4>
           <p style="color:rgba(255,255,255,0.6); font-size:0.95rem; line-height:1.6;">Curated acoustic and electric instruments from legendary luthiers.</p>
         </div>
         
      </div>

    </div>
  </div>
</section>

<style>
  .promo-card:hover { transform: translateY(-5px) !important; background:rgba(255,255,255,0.06) !important; border-color:rgba(255,255,255,0.1) !important; }
</style>

<?php include 'includes/footer.php'; ?>
