<?php
require_once __DIR__ . '/includes/functions.php';

$token = sanitize($_GET['token'] ?? '');

if (!$token) {
    die("Invalid download link.");
}

$db = getDB();

// Find order item by token
$stmt = $db->prepare("
    SELECT oi.*, dp.file_path, dp.file_name, o.payment_status 
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN digital_products dp ON oi.product_id = dp.product_id
    WHERE oi.download_token = ?
");
$stmt->execute([$token]);
$item = $stmt->fetch();

if (!$item) {
    die("Download link not found or expired.");
}

if ($item['payment_status'] !== 'paid') {
    die("File access restricted. Payment not confirmed.");
}

$filePath = __DIR__ . '/uploads/digital/' . $item['file_path'];

if (!file_exists($filePath)) {
    // In a real app, you'd have the file. Here we'll show an error or mock it.
    die("The requested file is currently unavailable. Please contact support.");
}

// Update download count
$upd = $db->prepare("UPDATE order_items SET download_count = download_count + 1 WHERE id = ?");
$upd->execute([$item['id']]);

// Stream file
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($item['file_name']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
