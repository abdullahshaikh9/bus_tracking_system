<?php
require_once __DIR__ . '/../layouts/functions.php';
require_login();
require_permission($pdo, 'manage_roles');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash('error', 'No role ID specified');
    redirect('roles.php');
}

$id = (int)$_GET['id'];

if ($id === 1) {
    set_flash('error', 'The Super Admin role is protected and cannot be deleted.');
    redirect('roles.php');
}

try {
    $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
    $stmt->execute([$id]);
    set_flash('success', 'Role successfully removed from the system.');
} catch (Exception $e) {
    set_flash('error', 'Error deleting role: ' . $e->getMessage());
}

redirect('roles.php');
?>
