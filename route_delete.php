<?php
require_once __DIR__ . '/../layouts/functions.php';
require_login();
require_permission($pdo, 'manage_routes');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash('error', 'No route ID specified');
    redirect('routes.php');
}

$id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM routes WHERE id = ?");
    $stmt->execute([$id]);
    set_flash('success', 'Route successfully deleted.');
} catch (Exception $e) {
    set_flash('error', 'Error deleting route: ' . $e->getMessage());
}

redirect('routes.php');
?>
