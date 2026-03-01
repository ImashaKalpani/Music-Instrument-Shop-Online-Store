<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'About Us';
include 'includes/header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1>About <span class="gradient-text">Melody Masters</span></h1>
        <p>Your journey into the world of music starts here.</p>
    </div>
</div>

<div class="section">
    <div class="container">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:60px; align-items:center;">
            <div>
                <h2 style="margin-bottom:20px;">Our Story</h2>
                <p style="margin-bottom:20px;">Founded in 2010, Melody Masters began as a small boutique shop for professional musicians in the heart of London. Our passion for high-quality instruments and exceptional service quickly made us a favorite among the local music community.</p>
                <p style="margin-bottom:20px;">Today, we've expanded our reach online, bringing the world's finest guitars, keyboards, drums, and wind instruments to musicians of all levels across the globe.</p>
                
                <div class="grid-2 mt-4">
                    <div class="stat-card" style="padding:20px;">
                        <div>
                            <div class="stat-value" style="font-size:1.5rem;">15+</div>
                            <div class="stat-label" style="font-size:0.7rem;">Years of Excellence</div>
                        </div>
                    </div>
                    <div class="stat-card" style="padding:20px;">
                        <div>
                            <div class="stat-value" style="font-size:1.5rem;">5k+</div>
                            <div class="stat-label" style="font-size:0.7rem;">Happy Musicians</div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <img src="https://images.unsplash.com/photo-1511379938547-c1f69419868d?auto=format&fit=crop&w=800&q=80" alt="Music Shop" style="border-radius:var(--radius-lg); box-shadow: var(--shadow-card);">
            </div>
        </div>
    </div>
</div>

<div class="section" style="background:var(--bg-card); border-top:1px solid var(--border); border-bottom:1px solid var(--border);">
    <div class="container">
        <div class="section-header">
            <span class="section-eyebrow">Our Values</span>
            <h2 class="section-title">Why Choose <span class="gradient-text">Melody Masters</span>?</h2>
        </div>
        
        <div class="grid-3">
            <div class="glass-card" style="padding:32px; text-align:center;">
                <div style="font-size:3rem; margin-bottom:20px;">🎸</div>
                <h3 style="margin-bottom:12px;">Premium Quality</h3>
                <p>We hand-pick every instrument in our catalog, ensuring only the highest standards of craftsmanship and sound quality.</p>
            </div>
            <div class="glass-card" style="padding:32px; text-align:center;">
                <div style="font-size:3rem; margin-bottom:20px;">👨‍🔧</div>
                <h3 style="margin-bottom:12px;">Expert Support</h3>
                <p>Our team consists of passionate musicians and technicians ready to help you find your perfect sound or maintain your gear.</p>
            </div>
            <div class="glass-card" style="padding:32px; text-align:center;">
                <div style="font-size:3rem; margin-bottom:20px;">🚚</div>
                <h3 style="margin-bottom:12px;">Safe Delivery</h3>
                <p>We use specialized musical instrument packaging and trackable couriers to ensure your instrument arrives in perfect condition.</p>
            </div>
        </div>
    </div>
</div>

<div class="section">
    <div class="container text-center">
        <h2 style="margin-bottom:30px;">Ready to find your <span class="gradient-text">Masterpiece</span>?</h2>
        <a href="shop.php" class="btn btn-primary btn-lg">Browse Our Collection</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
