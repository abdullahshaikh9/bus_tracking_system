<?php
require_once __DIR__ . '/../layouts/functions.php';
require_login();
require_permission($pdo, 'manage_users');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash('error', 'No user ID specified');
    redirect('users.php');
}

$id = (int)$_GET['id'];

// Prevent deleting self or Super Admin 1
if ($id == $_SESSION['user_id']) {
    set_flash('error', 'You cannot delete your own account.');
    redirect('users.php');
}

if ($id == 1) {
    set_flash('error', 'The primary Super Admin account cannot be deleted.');
    redirect('users.php');
}

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    set_flash('success', 'User account successfully deleted.');
} catch (Exception $e) {
    set_flash('error', 'Error deleting user: ' . $e->getMessage());
}

redirect('users.php');
?>
