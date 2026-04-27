<?php
require_once 'config/db.php';
session_start();

// Set driver offline if applicable
if (isset($_SESSION['user_id'], $_SESSION['role_name']) && $_SESSION['role_name'] == 'Driver') {
    $stmt = $pdo->prepare("UPDATE drivers SET is_online = 0 WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

session_destroy();
header("Location: login.php");
exit();
?>
