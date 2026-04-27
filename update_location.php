<?php
require_once __DIR__ . '/../layouts/functions.php';

// Accept JSON payload from the driver
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['bus_id']) || !isset($data['lat']) || !isset($data['lng'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields (bus_id, lat, lng)']);
    exit();
}

$bus_id = (int)$data['bus_id'];
$lat = (float)$data['lat'];
$lng = (float)$data['lng'];

try {
    // Auto-create table if it doesn't exist (safety wrapper)
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

    // Upsert the latest location using ON DUPLICATE KEY UPDATE
    $stmt = $pdo->prepare("
        INSERT INTO bus_live_locations (bus_id, latitude, longitude, last_updated) 
        VALUES (?, ?, ?, NOW()) 
        ON DUPLICATE KEY UPDATE 
            latitude = VALUES(latitude), 
            longitude = VALUES(longitude), 
            last_updated = NOW()
    ");
    
    if ($stmt->execute([$bus_id, $lat, $lng])) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database update failed']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
