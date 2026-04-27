<?php
require_once __DIR__ . '/../config/db.php';
$stmt = $pdo->prepare("SELECT u.full_name, r.name as role, p.name as permission 
                     FROM users u 
                     JOIN roles r ON u.role_id = r.id 
                     JOIN role_permissions rp ON r.id = rp.role_id 
                     JOIN permissions p ON rp.permission_id = p.id 
                     WHERE u.id = ?");
$stmt->execute([1]);
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
?>
