<?php
// ============================================================
// Session & Auth Helpers - Melody Masters
// ============================================================
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- Auth helpers ----
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    static $user = null;
    if ($user === null) {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ? AND is_active = 1');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

function requireLogin(string $redirect = '/login.php'): void {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . $redirect);
        exit;
    }
}

function requireRole(string $role): void {
    requireLogin();
    $user = getCurrentUser();
    $hierarchy = ['customer' => 1, 'staff' => 2, 'admin' => 3];
    if (($hierarchy[$user['role']] ?? 0) < ($hierarchy[$role] ?? 99)) {
        header('Location: ' . SITE_URL . '/index.php?error=access_denied');
        exit;
    }
}

function isAdmin(): bool {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

function isStaff(): bool {
    $user = getCurrentUser();
    return $user && in_array($user['role'], ['staff', 'admin']);
}

// ---- Cart helpers ----
function getCart(): array {
    return $_SESSION['cart'] ?? [];
}

function getCartCount(): int {
    $cart = getCart();
    return array_sum(array_column($cart, 'quantity'));
}

function getCartTotal(): float {
    $cart = getCart();
    $total = 0;
    foreach ($cart as $item) {
        $price = $item['sale_price'] ?? $item['price'];
        $total += $price * $item['quantity'];
    }
    return $total;
}

function addToCart(int $productId, int $qty = 1): bool {
    $db = getDB();
    $stmt = $db->prepare('SELECT id, name, price, sale_price, stock_quantity, product_type, image FROM products WHERE id = ? AND is_active = 1');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    if (!$product) return false;

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    if (isset($_SESSION['cart'][$productId])) {
        $newQty = $_SESSION['cart'][$productId]['quantity'] + $qty;
        if ($product['product_type'] === 'digital') $newQty = 1;
        if ($newQty > $product['stock_quantity'] && $product['product_type'] !== 'digital') return false;
        $_SESSION['cart'][$productId]['quantity'] = $newQty;
    } else {
        $_SESSION['cart'][$productId] = [
            'product_id'   => $product['id'],
            'name'         => $product['name'],
            'price'        => (float)$product['price'],
            'sale_price'   => $product['sale_price'] ? (float)$product['sale_price'] : null,
            'quantity'     => $product['product_type'] === 'digital' ? 1 : $qty,
            'product_type' => $product['product_type'],
            'image'        => $product['image'],
            'stock'        => (int)$product['stock_quantity'],
        ];
    }
    return true;
}

function removeFromCart(int $productId): void {
    unset($_SESSION['cart'][$productId]);
}

function updateCartQty(int $productId, int $qty): void {
    if ($qty <= 0) { removeFromCart($productId); return; }
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] = min($qty, $_SESSION['cart'][$productId]['stock']);
    }
}

function clearCart(): void {
    $_SESSION['cart'] = [];
}

function getShippingCost(): float {
    $total = getCartTotal();
    $cart = getCart();
    // If cart has only digital products, no shipping
    $hasPhysical = false;
    foreach ($cart as $item) {
        if ($item['product_type'] === 'physical') { $hasPhysical = true; break; }
    }
    if (!$hasPhysical) return 0.0;
    return $total >= FREE_SHIPPING_THRESHOLD ? 0.0 : STANDARD_SHIPPING_COST;
}

// ---- Flash messages ----
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ---- Sanitize ----
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatPrice(float $price): string {
    return CURRENCY_SYMBOL . number_format($price, 2);
}

function generateOrderNumber(): string {
    return 'MM-' . strtoupper(substr(uniqid(), -6)) . '-' . date('Ymd');
}

function generateToken(int $length = 32): string {
    return bin2hex(random_bytes($length));
}
