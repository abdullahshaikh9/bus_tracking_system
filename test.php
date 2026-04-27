<?php
// Mocking the environment
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['action'] = 'edit_driver';
$_POST['user_id'] = 2; // Assuming driver user_id 2 exists (Hashim)
$_POST['license_number'] = 'MUET-001-EDIT';
$_POST['phone_number'] = '1234567890';
$_POST['bus_number'] = ''; // Testing empty string which might be the cause
$_POST['redirect'] = 'drivers.php';

// Bypass login/permission by overriding functions
require_once __DIR__ . '/../config/db.php';

try {
    $user_id = $_POST['user_id'];
    $license = trim($_POST['license_number']);
    $phone = trim($_POST['phone_number']);
    $bus = trim($_POST['bus_number']);
    
    // Simulate what process_action.php does
    $stmt = $pdo->prepare("UPDATE drivers SET license_number = ?, bus_number = ? WHERE user_id = ?");
    $stmt->execute([$license, $bus, $user_id]);
    echo "Driver updated successfully.\n";
    
    $stmt2 = $pdo->prepare("UPDATE users SET phone = ? WHERE id = ?");
    $stmt2->execute([$phone, $user_id]);
    echo "User updated successfully.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
