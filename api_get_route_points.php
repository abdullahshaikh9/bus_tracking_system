<?php
require_once __DIR__ . '/../layouts/functions.php';
require_login();
require_permission($pdo, 'manage_routes');

$route_id = isset($_GET['route_id']) ? (int)$_GET['route_id'] : 0;
if ($route_id > 0) {
    $stmt = $pdo->prepare("SELECT sequence_order, point_name FROM bus_points WHERE route_id = ? ORDER BY sequence_order ASC");
    $stmt->execute([$route_id]);
    $points = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($points);
} else {
    echo json_encode([]);
}
?>
