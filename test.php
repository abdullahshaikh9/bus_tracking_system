<?php
require 'c:/xampp/htdocs/bus2/config/db.php';
$stmt = $pdo->query("SELECT * FROM roles");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt2 = $pdo->query("SELECT u.id, u.full_name, u.email, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
