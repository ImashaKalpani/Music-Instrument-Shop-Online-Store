<?php
require_once __DIR__ . '/includes/functions.php';
try {
    $db = getDB();
    $stmt = $db->query("SELECT email, role, is_active FROM users");
    $users = $stmt->fetchAll();
    foreach ($users as $u) {
        echo "Email: {$u['email']} | Role: {$u['role']} | Active: {$u['is_active']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
