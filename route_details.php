<?php
require_once __DIR__ . '/../layouts/functions.php';
require_login();

$page_title = 'Route Details';
$active_page = 'routes';

if (!isset($_GET['id'])) {
    header("Location: routes.php");
    exit();
}

$route_id = $_GET['id'];

// Handle favorites toggle before any HTML is rendered via header.php
if (isset($_GET['toggle_fav'])) {
    $is_favorite_stmt = $pdo->prepare("SELECT id FROM favorite_routes WHERE user_id = ? AND route_id = ?");
    $is_favorite_stmt->execute([$_SESSION['user_id'], $route_id]);
    $is_fav = $is_favorite_stmt->fetch();

    if ($is_fav) {
        $pdo->prepare("DELETE FROM favorite_routes WHERE user_id = ? AND route_id = ?")->execute([$_SESSION['user_id'], $route_id]);
    } else {
        $pdo->prepare("INSERT INTO favorite_routes (user_id, route_id) VALUES (?, ?)")->execute([$_SESSION['user_id'], $route_id]);
    }
    header("Location: route_details.php?id=" . $route_id);
    exit();
}

require_once __DIR__ . '/../layouts/header.php';

$stmt = $pdo->prepare("SELECT * FROM routes WHERE id = ?");
$stmt->execute([$route_id]);
$route = $stmt->fetch();

if (!$route) {
    die("Route not found.");
}

$stmt_points = $pdo->prepare("SELECT * FROM bus_points WHERE route_id = ? ORDER BY sequence_order ASC");
$stmt_points->execute([$route_id]);
$points = $stmt_points->fetchAll();

// ✅ Distance API start
$distance_text = "N/A";
$duration_text = "N/A";

if (!empty($points)) {

    $origins = $points[0]->latitude . "," . $points[0]->longitude;
    $last = $points[count($points) - 1];
    $destinations = $last->latitude . "," . $last->longitude;

    $apiKey = "AIzaSyDYlHVHcM3sLmU-jYsK8XMBIcSrglLbj9c";

    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$origins&destinations=$destinations&key=$apiKey";

    // Use cURL for a robust network request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Short timeout to not block UI completely if API fails
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response && $http_code == 200) {
        $data = json_decode($response, true);
        if (isset($data['rows'][0]['elements'][0]['distance']['text'])) {
            $distance_text = $data['rows'][0]['elements'][0]['distance']['text'];
            $duration_text = $data['rows'][0]['elements'][0]['duration']['text'];
        }
    }
}
// ✅ Distance API end

// Check favorites status for UI
$is_favorite_stmt = $pdo->prepare("SELECT id FROM favorite_routes WHERE user_id = ? AND route_id = ?");
$is_favorite_stmt->execute([$_SESSION['user_id'], $route_id]);
$is_fav = $is_favorite_stmt->fetch();
?>

<div class="flex flex-wrap md:flex-nowrap gap-4 mb-6 items-center justify-between">
    <div class="flex items-center gap-4">
        <a href="routes.php"
            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors gap-2">
            <i class="ph ph-arrow-left"></i> Back to Routes
        </a>
        <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="ph ph-map-pin-line text-primary-500"></i>
            <?php echo htmlspecialchars($route->route_name); ?>
        </h2>
    </div>

    <a href="route_details.php?id=<?php echo $route_id; ?>&toggle_fav=1"
        class="inline-flex items-center px-4 py-2 border rounded-lg text-sm font-medium transition-colors gap-2 <?php echo $is_fav ? 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-300 dark:border-yellow-700 text-yellow-600 dark:text-yellow-500 hover:bg-yellow-100 dark:hover:bg-yellow-900/40' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'; ?>">
        <?php echo $is_fav ? '<i class="ph ph-star-fill"></i> Saved to Favorites' : '<i class="ph ph-star"></i> Save Route'; ?>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Map Container -->
    <div
        class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col min-h-[600px] overflow-hidden">
        <div
            class="px-6 py-4 bg-gradient-to-r from-gray-900 to-primary-700 dark:from-gray-800 dark:to-primary-900 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">

                <p class="text-sm text-white mt-1">
                    Distance: <?php echo $distance_text; ?> | ETA: <?php echo $duration_text; ?>
                </p>

                <i class="ph ph-map-trifold"></i> Route GPS Trace
            </h3>
        </div>
        <div class="flex-1 relative bg-gray-100 dark:bg-gray-900 flex items-center justify-center overflow-hidden">
            <!-- Simulated Map -->
            <div id="map" class="w-full h-full"></div>


        </div>
    </div>

    <!-- Stops Timeline -->
    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col max-h-[600px]">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="ph ph-list-numbers text-primary-500"></i> All Bus Stops
            </h3>
        </div>
        <div class="p-6 overflow-y-auto flex-1">
            <?php if (empty($points)): ?>
                <div class="text-center py-8 text-red-500 dark:text-red-400">
                    <i class="ph ph-warning-circle text-4xl mb-2 flex justify-center"></i>
                    <p>No stops available for this route.</p>
                </div>
            <?php else: ?>
                <div class="relative pl-8">
                    <!-- Vertical Line -->
                    <div class="absolute left-[15px] top-6 bottom-4 w-1 bg-gray-200 dark:bg-gray-700 rounded-full"></div>

                    <?php foreach ($points as $index => $p): ?>
                        <div class="relative mb-8 last:mb-0">
                            <!-- Node -->
                            <div
                                class="absolute -left-8 top-1 w-4 h-4 rounded-full border-4 z-10 <?php echo $index == 0 ? 'bg-green-500 border-green-200 dark:border-green-900/50' : ($index == count($points) - 1 ? 'bg-red-500 border-red-200 dark:border-red-900/50' : 'bg-white dark:bg-gray-800 border-primary-500'); ?>">
                            </div>

                            <h4 class="text-lg font-bold text-gray-900 dark:text-white leading-tight mb-1">
                                <?php echo htmlspecialchars($p->point_name); ?>
                            </h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-mono flex items-center gap-1">
                                <i class="ph ph-clock"></i> Standard ETA:
                                <?php echo format_time($p->arrival_time); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php if (!empty($points)): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<style>
/* Leaflet UI Overrides */
.leaflet-routing-container {
    background-color: white !important;
    padding: 10px !important;
    margin: 10px !important;
    border-radius: 12px !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    font-family: inherit !important;
    max-height: 250px !important;
    overflow-y: auto !important;
    display: none; /* Hide default routing textual UI to keep it clean */
}
body.dark .leaflet-routing-container {
    background-color: #1f2937 !important;
    color: #f3f4f6 !important;
}
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        if (!document.getElementById("map")) return;
        
        // Initialize Map
        const map = L.map('map').setView([<?php echo $points[0]->latitude; ?>, <?php echo $points[0]->longitude; ?>], 14);

        // Add Free OpenStreetMap Tiles (Standard colorful maps)
        const tileLayer = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            
        L.tileLayer(tileLayer, {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Build route coordinates
        const waypoints = [
            <?php foreach ($points as $p): ?>
                L.latLng(<?php echo $p->latitude; ?>, <?php echo $p->longitude; ?>),
            <?php endforeach; ?>
        ];

        // Draw Map Route via OSRM (100% Free routing)
        const control = L.Routing.control({
            waypoints: waypoints,
            routeWhileDragging: false,
            addWaypoints: false,
            fitSelectedRoutes: true,
            lineOptions: {
                styles: [{color: '#6366f1', opacity: 0.8, weight: 6}]
            },
            createMarker: function(i, wp, nWps) {
                // Determine icon color based on start vs end vs middle
                let iconColor = '#4f46e5'; // default indigo
                if (i === 0) iconColor = '#22c55e'; // green start
                if (i === nWps - 1) iconColor = '#ef4444'; // red end
                
                return L.circleMarker(wp.latLng, {
                    radius: 8,
                    fillColor: iconColor,
                    fillOpacity: 1,
                    color: '#ffffff',
                    weight: 2
                }).bindPopup("Endpoint"); // Just simple markers
            }
        }).addTo(map);

        // Distance / ETA Callback Handler
        control.on('routesfound', function(e) {
            const routes = e.routes;
            const summary = routes[0].summary;
            const distance = (summary.totalDistance / 1000).toFixed(1); // km
            const time = Math.round(summary.totalTime / 60); // minutes
            
            // Add custom info control natively inside leaflet map container
            const LControl = L.control({position: 'bottomleft'});
            LControl.onAdd = function (map) {
                const div = L.DomUtil.create('div', 'bg-white dark:bg-gray-800 p-3 rounded-lg shadow-xl text-sm font-bold border border-gray-200 dark:border-gray-700 z-50 text-gray-900 dark:text-white');
                div.innerHTML = `<i class="ph ph-car text-primary-500"></i> ${distance} km &bull; ETA: ${time} mins`;
                return div;
            };
            LControl.addTo(map);
        });
    });
</script>
<?php endif; ?>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>