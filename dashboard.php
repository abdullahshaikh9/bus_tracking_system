<?php
$page_title = 'Passenger Dashboard';
$active_page = 'dashboard';
require_once __DIR__ . '/../layouts/header.php';

// Fetch Active Trips
$live_trips_stmt = $pdo->prepare("SELECT t.*, r.route_name, r.start_point, r.end_point, b.bus_number, u.phone as driver_phone
                           FROM trips t 
                           JOIN routes r ON t.route_id = r.id 
                           JOIN buses b ON t.bus_id = b.id
                           JOIN drivers d ON t.driver_id = d.id
                           JOIN users u ON d.user_id = u.id
                           WHERE t.status = 'in_progress'
                           ORDER BY t.trip_date ASC, t.start_time ASC");
$live_trips_stmt->execute();
$live_trips = $live_trips_stmt->fetchAll();

// Fetch Route Points for the selected active trip
$points = [];
$selected_trip_index = 0;

if (!empty($live_trips)) {
    // Determine selected trip if requested
    if (isset($_GET['trip'])) {
        foreach ($live_trips as $index => $t) {
            if ($t->id == $_GET['trip']) {
                $selected_trip_index = $index;
                break;
            }
        }
    }
    
    $selected_route_id = $live_trips[$selected_trip_index]->route_id;
    $stmt = $pdo->prepare("SELECT * FROM bus_points WHERE route_id = ? ORDER BY sequence_order ASC");
    $stmt->execute([$selected_route_id]);
    $points = $stmt->fetchAll();
}
?>

<!-- Hero Section -->
<div class="mb-8 rounded-3xl overflow-hidden shadow-lg relative bg-gray-900 border border-gray-800">
    <!-- Abstract gradient background -->
    <div class="absolute inset-0 bg-gradient-to-br from-primary-900 via-gray-900 to-indigo-900 opacity-90"></div>
    <div
        class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-20 hidden dark:block">
    </div>

    <div class="relative p-8 md:p-12 lg:p-16 flex flex-col md:flex-row items-center justify-between z-10">
        <div class="text-white max-w-2xl text-center md:text-left">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-extrabold tracking-tight mb-4">Live Bus Tracker 📡</h2>
            <p
                class="text-primary-100 text-lg md:text-xl font-medium opacity-90 leading-relaxed max-w-xl mx-auto md:mx-0">
                Find your bus in real-time, view upcoming stops, and plan your campus commute seamlessly.
            </p>
        </div>
        <div class="mt-8 md:mt-0 hidden md:block animate-bounce">
            <div
                class="w-32 h-32 bg-white/10 backdrop-blur-md rounded-full flex items-center justify-center border border-white/20 shadow-2xl">
                <i class="ph ph-bus text-6xl text-white"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

    <!-- Map Section -->
    <div class="xl:col-span-2 flex flex-col h-[600px]">
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex-1 flex flex-col">
            <div
                class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
                <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="ph ph-map-trifold text-xl text-primary-500"></i> Live Map
                </h3>
            </div>
            <div id="dashboard_map" class="flex-1 relative bg-gray-100 dark:bg-gray-900 overflow-hidden">
                <?php if(empty($live_trips)): ?>
                    <div class="absolute inset-0 flex items-center justify-center bg-gray-200/20 backdrop-blur-sm z-10 text-center p-8">
                        <div>
                             <i class="ph ph-map-pin-line text-5xl text-gray-400 mb-2"></i>
                             <h4 class="text-lg font-bold text-gray-900 dark:text-white">Transit Monitor Offline</h4>
                             <p class="text-sm text-gray-500">Select an active trip to begin tracking.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Live Status Sidebar -->
    <div class="flex flex-col h-[600px]">
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex-1 flex flex-col overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="ph ph-broadcast text-xl text-amber-500 animate-pulse"></i> Active Trips
                </h3>
            </div>

            <div class="p-6 flex-1 overflow-y-auto scrollbar-hide">
                <?php if (empty($live_trips)): ?>
                    <div class="text-center py-12 px-4">
                        <div
                            class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                            <i class="ph ph-coffee text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Active Buses</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">There are no buses currently en-route. Please
                            check back later or view the full schedule.</p>
                        <a href="routes.php"
                            class="mt-6 inline-flex items-center justify-center px-4 py-2 bg-primary-50 text-primary-700 rounded-lg hover:bg-primary-100 transition font-medium text-sm dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/40">
                            View All Routes
                        </a>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($live_trips as $i => $t): 
                            $isSelected = $i === $selected_trip_index;
                        ?>
                            <div onclick="window.location.href='?trip=<?= $t->id ?>'" class="relative overflow-hidden group p-5 rounded-2xl border transition-all duration-300 cursor-pointer <?= $isSelected ? 'border-primary-500 dark:border-primary-500 bg-primary-50/50 dark:bg-primary-900/10 shadow-md shadow-primary-500/10' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-sm' ?>">
                                
                                <?php if($isSelected): ?>
                                    <div class="absolute top-0 right-0 bg-primary-500 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-bl-lg shadow-sm">
                                        Selected
                                    </div>
                                <?php endif; ?>
                                
                                <h4 class="font-bold text-gray-900 dark:text-white mb-1 pr-16"><?= htmlspecialchars($t->route_name) ?></h4>
                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-4 font-medium">
                                    <span class="truncate"><?= htmlspecialchars($t->start_point) ?></span>
                                    <i class="ph ph-arrow-right text-gray-400 shrink-0"></i>
                                    <span class="truncate"><?= htmlspecialchars($t->end_point) ?></span>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg text-sm font-semibold border border-gray-200 dark:border-gray-600 shadow-sm">
                                        <i class="ph ph-bus text-primary-500 text-lg"></i>
                                        <?= htmlspecialchars($t->bus_number) ?>
                                    </span>
                                    <?php if (!empty($t->driver_phone)): ?>
                                        <a href="tel:<?= htmlspecialchars($t->driver_phone) ?>" onclick="event.stopPropagation();" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-700 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/40 transition-colors" title="Call Driver">
                                            <i class="ph ph-phone text-lg"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <?php if ($isSelected && !empty($points)): ?>
                                    <div class="mt-5 pt-5 border-t border-gray-200 dark:border-gray-700">
                                        <h5
                                            class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                                            <i class="ph ph-clock"></i> Upcoming Stops
                                        </h5>
                                        <div
                                            class="space-y-3 relative before:absolute before:inset-y-0 before:left-2 before:w-px before:bg-gray-200 dark:before:bg-gray-700">
                                            <?php foreach (array_slice($points, 0, 3) as $idx => $p): ?>
                                                <div class="relative pl-6 flex justify-between items-center group/stop">
                                                    <!-- Timeline dot -->
                                                    <div
                                                        class="absolute left-[3px] top-1/2 -translate-y-1/2 w-[10px] h-[10px] rounded-full bg-white dark:bg-gray-800 border-2 <?= $idx === 0 ? 'border-primary-500 shadow-sm shadow-primary-500/50' : 'border-gray-300 dark:border-gray-600' ?> transition-colors z-10">
                                                    </div>

                                                    <span
                                                        class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover/stop:text-primary-600 dark:group-hover/stop:text-primary-400 transition-colors"><?= htmlspecialchars($p->point_name) ?></span>
                                                    <span
                                                        class="text-xs font-bold px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 shadow-sm">
                                                        <?= format_time($p->arrival_time) ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($live_trips)): ?>
<!-- Leaflet & Tracking Logic -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<style>.leaflet-routing-container { display: none !important; }</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const mapContainer = document.getElementById("dashboard_map");
    if (!mapContainer) return;

    <?php 
    $centerLat = count($points) > 0 ? $points[0]->latitude : 25.405;
    $centerLng = count($points) > 0 ? $points[0]->longitude : 68.261;
    $selected_trip = $live_trips[$selected_trip_index];
    ?>

    const map = L.map(mapContainer, { zoomControl: false }).setView([<?= $centerLat ?>, <?= $centerLng ?>], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    const waypoints = [
        <?php foreach ($points as $p) echo "L.latLng({$p->latitude}, {$p->longitude}),"; ?>
    ];

    if (waypoints.length >= 2) {
        L.Routing.control({
            waypoints: waypoints,
            routeWhileDragging: false,
            addWaypoints: false,
            fitSelectedRoutes: true,
            lineOptions: { styles: [{color: '#6366f1', opacity: 0.8, weight: 6}] },
            createMarker: function(i, wp, nWps) {
                return L.circleMarker(wp.latLng, { 
                    radius: 6, fillColor: (i===0?'#22c55e':'#ef4444'), fillOpacity: 1, color: '#fff', weight: 2 
                });
            }
        }).addTo(map);
    }

    const busIcon = L.divIcon({
        html: `<div id="live_bus_container" class="bg-white dark:bg-gray-800 p-1.5 rounded-xl shadow-2xl border-2 border-indigo-500 flex items-center justify-center transition-all duration-500"><div id="live_bus_bg" class="bg-indigo-500 p-1 rounded-lg text-white"><i class="ph ph-bus text-lg"></i></div></div>`,
        className: '', iconSize: [40, 40], iconAnchor: [20, 20]
    });

    let busMarker = L.marker([<?= $centerLat ?>, <?= $centerLng ?>], {icon: busIcon, zIndexOffset: 1000}).addTo(map);
    let isFirstPing = true;

    const fetchLiveLocation = () => {
        fetch('<?= BASE_URL ?>/api/get_bus_location.php?bus_id=<?= (int)$selected_trip->bus_id ?>')
        .then(r => r.json())
        .then(data => {
            if (data.success && data.lat && data.lng) {
                const newLatLng = [data.lat, data.lng];
                busMarker.setLatLng(newLatLng);
                if (isFirstPing) { map.panTo(newLatLng); isFirstPing = false; }
                
                const markerElement = busMarker.getElement();
                if (markerElement) {
                    const iconContainer = markerElement.querySelector('#live_bus_container');
                    const iconBg = markerElement.querySelector('#live_bus_bg');
                    if (data.is_online) {
                        iconContainer?.classList.remove('border-red-500', 'animate-ping');
                        iconBg?.classList.remove('bg-red-500');
                    } else {
                        iconContainer?.classList.add('border-red-500', 'animate-ping');
                        iconBg?.classList.add('bg-red-500');
                    }
                }
            }
        }).catch(console.error);
    };

    fetchLiveLocation();
    setInterval(fetchLiveLocation, 4000);
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>