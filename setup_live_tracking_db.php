<?php
require_once __DIR__ . '/config/db.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS bus_live_locations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            bus_id INT NOT NULL,
            latitude DECIMAL(10, 8) NOT NULL,
            longitude DECIMAL(11, 8) NOT NULL,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY(bus_id)
        )
    ");
    echo "SUCCESS";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
