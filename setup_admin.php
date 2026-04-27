<?php
require_once 'layouts/functions.php';

// Setup Super Admin
$email = 'admin@muet.edu.pk';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$name = 'Super Admin';
$role_id = 1; // Super Admin Role ID

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role_id]);
        echo "Super Admin created successfully!\n";
        echo "Email: $email\nPassword: admin123\n";
    } else {
        echo "Super Admin already exists.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
