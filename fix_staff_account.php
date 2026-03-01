<?php
require_once __DIR__ . '/includes/functions.php';

$email = 'staff@melodymasters.com';
$password = 'Admin@1234';
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $db = getDB();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $stmt = $db->prepare("UPDATE users SET password = ?, role = 'staff', is_active = 1 WHERE email = ?");
        $stmt->execute([$hash, $email]);
        echo "Staff password updated successfully!\n";
    } else {
        $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, password, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Sarah', 'Staff', $email, $hash, 'staff', 1]);
        echo "Staff user created successfully!\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
