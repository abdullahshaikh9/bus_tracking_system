<?php
require_once __DIR__ . '/../layouts/functions.php';
require_login();
require_permission($pdo, 'manage_routes');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash('error', 'No bus ID specified');
    redirect('buses.php');
}

$id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM buses WHERE id = ?");
    $stmt->execute([$id]);
    set_flash('success', 'Bus record successfully deleted.');
} catch (Exception $e) {
    set_flash('error', 'Error deleting bus: ' . $e->getMessage());
}

redirect('buses.php');
?>
