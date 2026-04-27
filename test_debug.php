<?php
require_once __DIR__ . '/config/db.php';

// Check if trips has bus_id
$trips = $pdo->query("SELECT id, bus_id, status FROM trips")->fetchAll(PDO::FETCH_ASSOC);
echo "Trips:\n";
print_r($trips);

// Check if bus_live_locations exists and has entries
try {
    $locs = $pdo->query("SELECT * FROM bus_live_locations")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nLive Locations:\n";
    print_r($locs);
} catch (Exception $e) {
    echo "\nError fetching locations: " . $e->getMessage();
}

$drivers = $pdo->query("SELECT * FROM drivers")->fetchAll(PDO::FETCH_ASSOC);
echo "\nDrivers:\n";
print_r($drivers);
?>
