<?php
require_once __DIR__ . '/../layouts/functions.php';
require_login();
require_permission($pdo, 'manage_routes');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash('error', 'No trip ID specified');
    redirect('trips.php');
}

$id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM trips WHERE id = ?");
    $stmt->execute([$id]);
    set_flash('success', 'Scheduled trip record successfully removed.');
} catch (Exception $e) {
    set_flash('error', 'Error deleting trip: ' . $e->getMessage());
}

redirect('trips.php');
?>
