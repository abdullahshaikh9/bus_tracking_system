<?php
require_once __DIR__ . '/config/db.php';
$stmt = $pdo->prepare("SELECT id, route_name, start_point, end_point FROM routes");
$stmt->execute();
$r = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($r);
?>
