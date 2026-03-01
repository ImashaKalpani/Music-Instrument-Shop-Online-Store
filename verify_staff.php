<?php
require_once __DIR__ . '/includes/functions.php';
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT email, role, is_active FROM users WHERE email = ?");
    $stmt->execute(['staff@melodymasters.com']);
    $user = $stmt->fetch();
    if ($user) {
        print_r($user);
    } else {
        echo "User not found.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
