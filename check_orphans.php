<?php
require_once __DIR__ . '/../config/db.php';
$stmt = $pdo->prepare("SELECT u.id, u.full_name, p.name as role, (SELECT id FROM drivers WHERE user_id = u.id) as driver_record_id FROM users u JOIN roles p ON u.role_id = p.id WHERE p.name = ?");
$stmt->execute(['Driver']);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
