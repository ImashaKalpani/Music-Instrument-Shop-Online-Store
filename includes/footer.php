</main>

<footer>
  <div class="footer-top">
    <div class="container">
      <div class="grid-4">
        <div class="footer-brand">
          <span class="footer-brand-logo"><?= SITE_NAME ?></span>
          <p class="footer-desc">Your one-stop destination for musical instruments, accessories, and sheet music. Serving musicians from beginners to professionals.</p>
          <div class="footer-social">
            <a href="#" class="social-btn" title="Facebook">f</a>
            <a href="#" class="social-btn" title="Instagram">𝕀</a>
            <a href="#" class="social-btn" title="Twitter">𝕏</a>
            <a href="#" class="social-btn" title="YouTube">▶</a>
          </div>
        </div>

        <div>
          <div class="footer-heading">Shop</div>
          <ul class="footer-links">
            <li><a href="<?= SITE_URL ?>/shop.php?category=guitars">Guitars</a></li>
            <li><a href="<?= SITE_URL ?>/shop.php?category=keyboards-pianos">Keyboards & Pianos</a></li>
            <li><a href="<?= SITE_URL ?>/shop.php?category=drums-percussion">Drums & Percussion</a></li>
            <li><a href="<?= SITE_URL ?>/shop.php?category=wind-instruments">Wind Instruments</a></li>
            <li><a href="<?= SITE_URL ?>/shop.php?category=string-instruments">String Instruments</a></li>
            <li><a href="<?= SITE_URL ?>/shop.php?category=accessories">Accessories</a></li>
            <li><a href="<?= SITE_URL ?>/shop.php?category=digital-sheet-music">Digital Sheet Music</a></li>
          </ul>
        </div>

        <div>
          <div class="footer-heading">Customer</div>
          <ul class="footer-links">
            <li><a href="<?= SITE_URL ?>/account.php">My Account</a></li>
            <li><a href="<?= SITE_URL ?>/cart.php">Shopping Cart</a></li>
            <li><a href="<?= SITE_URL ?>/account.php?tab=orders">Order History</a></li>
            <li><a href="#">Track My Order</a></li>
            <li><a href="#">Returns & Refunds</a></li>
            <li><a href="#">FAQs</a></li>
          </ul>
        </div>

        <div>
          <div class="footer-heading">Newsletter</div>
          <p class="footer-desc" style="font-size:.8rem;margin-bottom:16px;">Get exclusive deals and new arrivals direct to your inbox.</p>
          <form class="footer-newsletter" action="#" method="POST">
            <input type="email" placeholder="your@email.com" name="newsletter_email">
            <button type="submit">→</button>
          </form>
          <div style="margin-top:24px;">
            <div class="footer-heading">Contact</div>
            <ul class="footer-links">
              <li><a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a></li>
              <li><a href="tel:+94123456789">+94 123456789</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> <?= SITE_NAME ?> Instrument Shop. All rights reserved.</span>
      <div style="display:flex;gap:16px;">
        <a href="#" style="color:var(--text-muted);font-size:.8rem;">Privacy Policy</a>
        <a href="#" style="color:var(--text-muted);font-size:.8rem;">Terms of Service</a>
        <a href="#" style="color:var(--text-muted);font-size:.8rem;">Cookie Policy</a>
      </div>
      <div style="display:flex;gap:8px;align-items:center;">
        <span style="font-size:1.3rem;">💳</span>
        <span style="font-size:1.3rem;">🔒</span>
        <span style="font-size:.75rem;color:var(--text-muted);">Secure Payments</span>
      </div>
    </div>
  </div>
</footer>

<script>
function toggleMobileNav() {
  const nav = document.getElementById('mobileNav');
  nav.classList.toggle('open');
}
function toggleUserMenu() {
  const menu = document.getElementById('userMenu');
  menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
  const wrap = document.getElementById('userMenuWrap');
  if (wrap && !wrap.contains(e.target)) {
    const menu = document.getElementById('userMenu');
    if (menu) menu.style.display = 'none';
  }
});
// Tab component
function initTabs() {
  document.querySelectorAll('.tabs').forEach(tabGroup => {
    tabGroup.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const target = this.dataset.tab;
        tabGroup.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        const pane = document.getElementById('tab-' + target);
        if (pane) pane.classList.add('active');
      });
    });
  });
}
document.addEventListener('DOMContentLoaded', initTabs);

// Cart qty AJAX
function updateQty(productId, delta) {
  const input = document.getElementById('qty-' + productId);
  if (!input) return;
  let newQty = parseInt(input.value) + delta;
  if (newQty < 1) newQty = 1;
  input.value = newQty;
  submitCartUpdate(productId, newQty);
}
function submitCartUpdate(productId, qty) {
  const form = document.createElement('form');
  form.method = 'POST'; form.action = '<?= SITE_URL ?>/cart.php';
  const fields = {action:'update', product_id: productId, quantity: qty};
  Object.keys(fields).forEach(k => {
    const i = document.createElement('input');
    i.type='hidden'; i.name=k; i.value=fields[k]; form.appendChild(i);
  });
  document.body.appendChild(form); form.submit();
}
</script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
  // Initialize Lucide icons
  lucide.createIcons();
</script>
</body>
</html>
