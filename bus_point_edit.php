<?php
$page_title = 'Edit Bus Point';
$active_page = 'points';

require_once __DIR__ . '/../layouts/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash('error', 'No point ID specified');
    redirect('bus_points.php');
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM bus_points WHERE id = ?");
$stmt->execute([$id]);
$point = $stmt->fetch();

if (!$point) {
    set_flash('error', 'Bus point not found');
    redirect('bus_points.php');
}

require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_routes');

$routes_stmt = $pdo->prepare("
    SELECT r.*, u.full_name as driver_name 
    FROM routes r
    LEFT JOIN drivers d ON r.driver_id = d.id
    LEFT JOIN users u ON d.user_id = u.id
    ORDER BY r.route_name ASC
");
$routes_stmt->execute();
$routes = $routes_stmt->fetchAll();
?>

<div class="max-w-4xl mx-auto flex flex-col gap-8">
    <div class="flex items-center justify-between px-4 sm:px-0">
        <a href="bus_points.php" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary-600 transition-colors">
            <i class="ph ph-arrow-left"></i>
            Back to Waypoints
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xl overflow-hidden px-4 sm:px-0">
        <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <i class="ph ph-pencil-simple text-primary-600"></i>
                    Edit Waypoint: <?= htmlspecialchars($point->point_name) ?>
                </h2>
                <p class="text-xs text-gray-500 mt-1">Update the location or timing data for this specific transit stop.</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 border border-primary-200 dark:border-primary-800/50 shadow-sm shrink-0">
                <i class="ph ph-map-pin text-2xl"></i>
            </div>
        </div>

        <form action="process_action.php" method="POST" class="p-8 space-y-8">
            <input type="hidden" name="action" value="edit_bus_point">
            <input type="hidden" name="id" value="<?= $point->id; ?>">
            <input type="hidden" name="redirect" value="bus_points.php">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Route Selection -->
                <div class="md:col-span-2 space-y-3">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                        <i class="ph ph-path text-primary-500"></i> Assigned Transit Route
                    </label>
                    <div class="relative">
                        <select name="route_id" class="w-full pl-4 pr-10 py-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-primary-500 transition outline-none appearance-none font-medium text-gray-900 dark:text-white" required onchange="fetchRoutePoints(this.value)">
                            <?php foreach ($routes as $r): ?>
                                <option value="<?php echo $r->id; ?>" <?php echo $r->id == $point->route_id ? 'selected' : ''; ?>>
                                    <?php 
                                        $driver_info = $r->driver_name ? " (Dr. {$r->driver_name})" : " (No Driver)";
                                        echo htmlspecialchars($r->route_name . $driver_info); 
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="ph ph-caret-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>
                    <div id="existingPoints" class="min-h-[20px]"></div>
                </div>

                <!-- Point Name -->
                <div class="space-y-3">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Waypoint / Stop Name</label>
                    <div class="relative">
                        <i class="ph ph-tag absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="point_name" value="<?= htmlspecialchars($point->point_name) ?>" required class="w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-primary-500 transition outline-none text-gray-900 dark:text-white font-medium">
                    </div>
                </div>

                <!-- Arrival Time -->
                <div class="space-y-3">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Updated ETA</label>
                    <div class="relative">
                        <i class="ph ph-clock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="time" name="arrival_time" value="<?= htmlspecialchars($point->arrival_time) ?>" required class="w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-primary-500 transition outline-none text-gray-900 dark:text-white font-medium">
                    </div>
                </div>

                <!-- Sequence Order -->
                <div class="space-y-3">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Sequence Order</label>
                    <div class="relative">
                        <i class="ph ph-list-numbers absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="number" name="sequence_order" value="<?= htmlspecialchars($point->sequence_order) ?>" min="1" required class="w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-primary-500 transition outline-none text-gray-900 dark:text-white font-medium">
                    </div>
                </div>

                <!-- Coordinates Heading -->
                <div class="md:col-span-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest flex items-center gap-2">
                        <i class="ph ph-navigation-arrow text-primary-500"></i> Geospatial Calibration
                    </h4>
                    <p class="text-xs text-gray-500 mt-1">The current location is pinned below. Click elsewhere to update it.</p>
                </div>

                <!-- Lat/Lng -->
                <div class="space-y-3">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-tighter">Latitude</label>
                    <input type="text" id="lat" name="latitude" value="<?= htmlspecialchars($point->latitude ?? '') ?>" required readonly class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-xl text-gray-500 dark:text-gray-400 font-mono text-sm cursor-not-allowed">
                </div>

                <div class="space-y-3">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-tighter">Longitude</label>
                    <input type="text" id="lng" name="longitude" value="<?= htmlspecialchars($point->longitude ?? '') ?>" required readonly class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-xl text-gray-500 dark:text-gray-400 font-mono text-sm cursor-not-allowed">
                </div>

                <!-- Interactive Map -->
                <div class="md:col-span-2">
                    <div id="map" class="w-full h-80 rounded-3xl border-2 border-gray-100 dark:border-gray-700 shadow-inner z-10"></div>
                </div>
            </div>

            <div class="pt-6">
                <button type="submit" class="w-full py-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-2xl shadow-xl shadow-primary-500/30 transform hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-3">
                    <i class="ph ph-floppy-disk text-xl"></i> Save Changes to Waypoint
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
function fetchRoutePoints(routeId) {
    const container = document.getElementById('existingPoints');
    if (!routeId) {
        container.innerHTML = '';
        return;
    }
    
    container.innerHTML = '<div class="flex items-center gap-2 text-xs text-gray-400 mt-2"><i class="ph ph-spinner animate-spin"></i> Analyzing current route path...</div>';
    
    fetch('api_get_route_points.php?route_id=' + routeId)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                container.innerHTML = '<div class="text-xs text-amber-500 font-bold mt-2 flex items-center gap-1.5"><i class="ph ph-warning-circle"></i> This route currently has zero stops mapped.</div>';
            } else {
                let html = '<div class="mt-4"><p class="text-[10px] font-black uppercase text-gray-400 mb-2">Current Timeline Progress:</p><div class="flex flex-wrap gap-2">';
                data.forEach(p => {
                    const isCurrent = (p.id == <?= $point->id ?>);
                    const classes = isCurrent 
                        ? 'bg-primary-600 text-white border-primary-700 shadow-sm' 
                        : 'bg-primary-50 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400 border-primary-100 dark:border-primary-800';
                    html += `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold border ${classes}">#${p.sequence_order} ${p.point_name}</span>`;
                });
                html += '</div></div>';
                container.innerHTML = html;
            }
        })
        .catch(err => {
            container.innerHTML = '<div class="text-xs text-red-500 font-bold mt-2">Error loading waypoints.</div>';
        });
}

document.addEventListener("DOMContentLoaded", function () {
    const defaultLat = <?= $point->latitude ?? '25.4093' ?>;
    const defaultLng = <?= $point->longitude ?? '68.2619' ?>;
    const defaultLocation = [parseFloat(defaultLat), parseFloat(defaultLng)];
    
    const map = L.map('map', {
        zoomControl: false
    }).setView(defaultLocation, 14);
    
    L.control.zoom({ position: 'bottomright' }).addTo(map);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    let marker = L.marker(defaultLocation).addTo(map);

    map.on("click", function (e) {
        const position = e.latlng;
        marker.setLatLng(position);
        document.getElementById("lat").value = position.lat.toFixed(6);
        document.getElementById("lng").value = position.lng.toFixed(6);
    });
    
    setTimeout(() => { map.invalidateSize(); }, 500);
    fetchRoutePoints(<?= $point->route_id ?>);
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>