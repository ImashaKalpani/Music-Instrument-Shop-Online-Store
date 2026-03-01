<?php
// Redirect to the users page with staff filter
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');
header('Location: users.php?role=staff');
exit;
