<?php
require_once __DIR__ . '/../config/db.php';
$stmt = $pdo->prepare("SELECT u.full_name, u.email, u.phone, u.profile_photo, d.* FROM drivers d JOIN users u ON d.user_id = u.id WHERE d.user_id = 2");
$stmt->execute();
$driver = $stmt->fetch();
var_dump($driver);
?>
