<?php
require 'c:\xampp\htdocs\bus2\config\db.php';
$res = $pdo->query('SELECT COUNT(*), route_id FROM bus_points GROUP BY route_id')->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
?>
