<?php
require_once __DIR__ . '/../layouts/functions.php';

if (!isset($_GET['bus_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing bus_id parameter']);
    exit();
}

$bus_id = (int)$_GET['bus_id'];

try {
    // Also ensuring table exists here so passenger view doesn't crash if driver hasn't pinged yet
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

    $stmt = $pdo->prepare("SELECT latitude, longitude, last_updated FROM bus_live_locations WHERE bus_id = ?");
    $stmt->execute([$bus_id]);
    $location = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($location) {
        // Calculate seconds since last update to identify if the bus dropped connection
        $updated_time = strtotime($location['last_updated']);
        $seconds_offline = time() - $updated_time;
        
        echo json_encode([
            'success' => true,
            'bus_id' => $bus_id,
            'lat' => (float)$location['latitude'],
            'lng' => (float)$location['longitude'],
            'last_updated' => $location['last_updated'],
            'is_online' => ($seconds_offline <= 30) // 30 sec threshold
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No live location found for this bus']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
