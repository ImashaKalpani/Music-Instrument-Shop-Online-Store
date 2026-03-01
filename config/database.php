<?php
// ============================================================
// Database Configuration - Melody Masters Instrument Shop
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'melody_masters');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', 'Melody Masters');
define('SITE_URL', 'http://localhost/Music-Instrument-Shop-Online-Store');
define('SITE_EMAIL', 'info@melodymasters.com');
define('CURRENCY_SYMBOL', '£');
define('FREE_SHIPPING_THRESHOLD', 100.00);
define('STANDARD_SHIPPING_COST', 10.00);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// ============================================================
// Get Database Connection (PDO)
// ============================================================
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('DB Connection Error: ' . $e->getMessage());
            die(json_encode(['error' => 'Database connection failed. Please try again later.']));
        }
    }
    return $pdo;
}
