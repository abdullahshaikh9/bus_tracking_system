<?php
$page_title = 'Live Bus Tracking';
$active_page = 'live_tracking';
require_once __DIR__ . '/../layouts/header.php';

// Passenger specific
$live_trips_stmt = $pdo->prepare("SELECT t.*, r.route_name, r.start_point, r.end_point, b.bus_number 
                           FROM trips t 
                           JOIN routes r ON t.route_id = r.id 
                           JOIN buses b ON t.bus_id = b.id
                           WHERE t.status = 'in_progress'
                           ORDER BY t.trip_date ASC");
$live_trips_stmt->execute();
$live_trips = $live_trips_stmt->fetchAll();

$selected_trip_index = 0;
if (!empty($live_trips) && isset($_GET['trip'])) {
    foreach ($live_trips as $index => $t) {
        if ($t->id == $_GET['trip']) {
            $selected_trip_index = $index;
            break;
        }
    }
}

$selected_trip = $live_trips[$selected_trip_index] ?? null;
$points = [];
if ($selected_trip) {
    $stmt_points = $pdo->prepare("SELECT * FROM bus_points WHERE route_id = ? ORDER BY sequence_order ASC");
    $stmt_points->execute([$selected_trip->route_id]);
    $points = $stmt_points->fetchAll();
}
?>

<div class="h-[calc(100vh-theme(spacing.32))] -m-4 md:-m-8 flex flex-col lg:flex-row overflow-hidden relative">
    
    <!-- Floating Header Stats (Desktop) -->
    <div class="absolute top-6 left-1/2 -translate-x-1/2 z-20 hidden lg:flex items-center gap-3 pointer-events-none">
        <div class="px-6 py-3 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-full border border-white/50 dark:border-gray-700/50 shadow-2xl shadow-indigo-500/20 flex items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest"><?= count($live_trips) ?> Active Radars</span>
            </div>
            <div class="w-px h-4 bg-gray-200 dark:bg-gray-700"></div>
            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 italic">Tracking MUET Transit Fleet</span>
        </div>
    </div>

    <!-- Map Viewport -->
    <div class="flex-1 relative bg-gray-100 dark:bg-gray-900 overflow-hidden group">
        <?php if(!empty($live_trips) && $selected_trip): ?>
            <div id="live_map" class="absolute inset-0 w-full h-full"></div>
            
            <div class="absolute top-24 left-1/2 -translate-x-1/2 z-20 pointer-events-none transition-opacity duration-300" id="map_loading_indicator">
                <div class="px-4 py-2 bg-indigo-600/90 backdrop-blur-md rounded-full text-white text-xs font-bold flex items-center gap-2 shadow-2xl">
                    <i class="ph ph-spinner-gap animate-spin text-lg"></i>
                    Establishing Satellite Uplink...
                </div>
            </div>
            
        <?php else: ?>
            <!-- Empty State Mockup if no trips -->
            <div class="absolute inset-0 bg-[#f8fafc] dark:bg-[#0f172a] transition-colors duration-500">
                <!-- Grid Pattern -->
                <div class="absolute inset-0 opacity-[0.03] dark:opacity-[0.05]" style="background-image: radial-gradient(#6366f1 2px, transparent 2px); background-size: 30px 30px;"></div>
                <!-- Center Focus Indicator -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                     <div class="w-[80vw] h-[80vw] max-w-[800px] max-h-[800px] border-2 border-dashed border-indigo-500/10 rounded-full animate-[spin_60s_linear_infinite]"></div>
                </div>
            </div>
        <?php endif; ?>

            <!-- Empty State UI -->
            <?php if (empty($live_trips)): ?>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl p-10 rounded-[3rem] border border-white dark:border-gray-700 shadow-2xl text-center max-w-sm mx-6">
                        <div class="w-20 h-20 bg-indigo-50 dark:bg-indigo-900/30 rounded-3xl flex items-center justify-center text-indigo-500 mx-auto mb-6 shadow-xl">
                            <i class="ph ph-satellite-slash text-4xl"></i>
                        </div>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tighter mb-2">Fleet Offline</h3>
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No transit signals detected. Active tracking will resume once buses start their scheduled routes.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Controls Overlay -->
        <div class="absolute right-6 top-6 flex flex-col gap-3 group-hover:translate-x-0 transition-transform">
             <button class="w-12 h-12 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 hover:bg-indigo-500 hover:text-white transition-all flex items-center justify-center">
                <i class="ph ph-layers text-2xl"></i>
             </button>
             <button class="w-12 h-12 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 hover:bg-indigo-500 hover:text-white transition-all flex items-center justify-center">
                <i class="ph ph-crosshair text-2xl"></i>
             </button>
        </div>
    </div>

    <!-- Active Units List (Sidebar) -->
    <div class="w-full lg:w-[450px] bg-white dark:bg-[#070b14] border-t lg:border-t-0 lg:border-l border-gray-100 dark:border-gray-800/50 flex flex-col shadow-[-20px_0_50px_-20px_rgba(0,0,0,0.1)] z-10 transition-all">
        <div class="p-8 pb-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">Live Monitor</h3>
                <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-500 rounded-lg text-[10px] font-black uppercase border border-emerald-500/20 tracking-widest">Real-time</span>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Select a transit unit to focus the radar view.</p>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4 scrollbar-hide">
             <?php foreach ($live_trips as $i => $t): 
                 $isSelected = $i === $selected_trip_index;
             ?>
                <div onclick="window.location.href='?trip=<?= $t->id ?>'" class="group/item relative p-6 rounded-[2rem] border-2 transition-all duration-300 cursor-pointer <?= $isSelected ? 'bg-indigo-600 border-indigo-500 shadow-2xl shadow-indigo-500/30' : 'bg-gray-50 dark:bg-gray-900 border-transparent hover:border-indigo-500/30 dark:hover:border-indigo-500/20' ?>">
                    <!-- Card Glow Effect -->
                    <?php if($isSelected): ?>
                        <div class="absolute inset-x-0 bottom-0 h-px bg-white/20"></div>
                    <?php endif; ?>
                    
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center border transition-colors <?= $isSelected ? 'bg-white text-indigo-600 border-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-100 dark:border-gray-700 shadow-sm' ?>">
                                <i class="ph ph-bus text-2xl"></i>
                            </div>
                            <div>
                                <h4 class="font-black text-lg uppercase tracking-tight <?= $isSelected ? 'text-white' : 'text-gray-900 dark:text-white' ?>">Bus <?= htmlspecialchars($t->bus_number) ?></h4>
                                <div class="flex items-center gap-1.5 <?= $isSelected ? 'text-indigo-100' : 'text-gray-500 dark:text-gray-400' ?> text-[10px] font-bold uppercase tracking-widest">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $isSelected ? 'bg-white animate-pulse' : 'bg-emerald-500' ?>"></span>
                                    En-Route
                                </div>
                            </div>
                        </div>
                        <div class="h-10 w-10 rounded-full flex items-center justify-center border transition-all <?= $isSelected ? 'bg-indigo-500 text-white border-white/20 shadow-lg' : 'bg-white dark:bg-gray-800 text-gray-400 border-gray-100 dark:border-gray-700' ?>">
                            <i class="ph ph-arrow-up-right text-xl"></i>
                        </div>
                    </div>

                    <div class="space-y-3 relative before:absolute before:inset-y-0 before:left-[11px] before:w-0.5 <?= $isSelected ? 'before:bg-indigo-400' : 'before:bg-gray-200 dark:before:bg-gray-700' ?>">
                        <div class="relative pl-8">
                             <div class="absolute left-0 top-1.5 w-6 h-6 rounded-full flex items-center justify-center border-2 <?= $isSelected ? 'bg-indigo-600 border-indigo-300' : 'bg-gray-50 dark:bg-gray-900 border-gray-300 dark:border-gray-600' ?>">
                                <div class="w-1.5 h-1.5 rounded-full <?= $isSelected ? 'bg-white' : 'bg-gray-400' ?>"></div>
                             </div>
                             <p class="text-[10px] font-black uppercase tracking-widest <?= $isSelected ? 'text-indigo-200' : 'text-gray-400' ?>">Origin</p>
                             <p class="text-sm font-bold <?= $isSelected ? 'text-white' : 'text-gray-800 dark:text-gray-100' ?>"><?= htmlspecialchars($t->start_point) ?></p>
                        </div>
                        <div class="relative pl-8">
                             <div class="absolute left-0 top-1.5 w-6 h-6 rounded-full flex items-center justify-center border-2 <?= $isSelected ? 'bg-white border-white' : 'bg-indigo-500 border-indigo-500 shadow-sm' ?>">
                                <i class="ph ph-flag-bold text-[10px] <?= $isSelected ? 'text-indigo-600' : 'text-white' ?>"></i>
                             </div>
                             <p class="text-[10px] font-black uppercase tracking-widest <?= $isSelected ? 'text-indigo-200' : 'text-gray-400' ?>">Destination</p>
                             <p class="text-sm font-bold <?= $isSelected ? 'text-white' : 'text-gray-800 dark:text-gray-100' ?>"><?= htmlspecialchars($t->end_point) ?></p>
                        </div>
                    </div>

                    <?php if($isSelected): ?>
                    <div class="mt-6 pt-6 border-t border-white/10 flex items-center justify-between">
                         <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-white/20 border-2 border-indigo-600 flex items-center justify-center text-[10px] font-black text-white">24</div>
                            <div class="w-8 h-8 rounded-full bg-white/10 backdrop-blur-md border-2 border-indigo-600 flex items-center justify-center text-[10px] font-medium text-white/50"><i class="ph ph-users"></i></div>
                         </div>
                         <div class="px-4 py-2 bg-white/10 rounded-xl text-xs font-black text-white uppercase tracking-wider">ETA: 4 MINS</div>
                    </div>
                    <?php endif; ?>
                </div>
             <?php endforeach; ?>
        </div>
        
        <div class="p-8 pt-4">
             <a href="routes.php" class="flex items-center justify-center gap-2 w-full py-4 bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 font-bold rounded-2xl border border-gray-100 dark:border-gray-800 transition-all uppercase text-[10px] tracking-[0.2em]">
                Explore Full Schedule
                <i class="ph ph-calendar-plus text-lg"></i>
             </a>
        </div>
    </div>
</div>

<style>
/* Adjust mobile padding and layout */
@media (max-width: 1024px) {
    .h-\[calc\(100vh-theme\(spacing\.32\)\)\] { height: auto; max-height: none; overflow: visible; }
    .flex-1 { min-height: 500px; display: block; } /* Show map on small mobile instead of list if needed or keep both */
}
</style>

<?php if (!empty($live_trips) && $selected_trip): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<style>
/* Leaflet UI Overrides */
.leaflet-routing-container { display: none !important; }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const mapContainer = document.getElementById("live_map");
        if (!mapContainer) return;

        <?php 
        $centerLat = count($points) > 0 ? $points[0]->latitude : 25.405;
        $centerLng = count($points) > 0 ? $points[0]->longitude : 68.261;
        ?>
        const map = L.map(mapContainer, {
            zoomControl: false // keep it clean
        }).setView([<?php echo $centerLat; ?>, <?php echo $centerLng; ?>], 14);

        const tileLayer = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            
        L.tileLayer(tileLayer, {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const waypoints = [
            <?php 
            foreach ($points as $p) {
                echo "L.latLng({$p->latitude}, {$p->longitude}),";
            } 
            ?>
        ];

        if (waypoints.length >= 2) {
            const control = L.Routing.control({
                waypoints: waypoints,
                routeWhileDragging: false,
                addWaypoints: false,
                fitSelectedRoutes: true,
                lineOptions: {
                    styles: [{color: '#6366f1', opacity: 0.8, weight: 6}]
                },
                createMarker: function(i, wp, nWps) {
                    let iconColor = '#4f46e5'; 
                    if (i === 0) iconColor = '#22c55e'; 
                    if (i === nWps - 1) iconColor = '#ef4444'; 
                    
                    return L.circleMarker(wp.latLng, {
                        radius: 8,
                        fillColor: iconColor,
                        fillOpacity: 1,
                        color: '#ffffff',
                        weight: 2
                    });
                }
            }).addTo(map);

            control.on('routesfound', function(e) {
                const loadingInd = document.getElementById("map_loading_indicator");
                if(loadingInd) loadingInd.style.opacity = '0';
            });
        } else {
            // No route points, just hide the loader immediately
            const loadingInd = document.getElementById("map_loading_indicator");
            if(loadingInd) loadingInd.style.opacity = '0';
            console.log("No route points defined for this route.");
        }

        // Initialize Live Marker Immediately at Origin
        let currentPoint = [<?php echo $centerLat; ?>, <?php echo $centerLng; ?>];
        
        const busIcon = L.divIcon({
            html: `<div id="live_bus_container" class="bg-white dark:bg-gray-800 p-1.5 rounded-xl shadow-2xl border-2 border-indigo-500 flex items-center justify-center transition-all duration-500 drop-shadow-lg"><div id="live_bus_bg" class="bg-indigo-500 p-1.5 rounded-lg text-white transition-colors duration-500"><i class="ph ph-bus text-xl" id="bus_icon_anim"></i></div></div>`,
            className: '',
            iconSize: [48, 48],
            iconAnchor: [24, 48]
        });
        
        let busMarker = L.marker(currentPoint, {icon: busIcon, zIndexOffset: 1000}).addTo(map);
        let isFirstPing = true;
        
        // Polling loop
        const fetchLiveLocation = () => {
            fetch('<?= BASE_URL ?>/api/get_bus_location.php?bus_id=<?php echo (int)$selected_trip->bus_id; ?>')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.lat && data.lng) {
                    const newLatLng = [data.lat, data.lng];
                    busMarker.setLatLng(newLatLng);
                    
                    if (isFirstPing) {
                        // Gently pan to the real location to emphasize it is working
                        map.panTo(newLatLng, {animate: true, duration: 1});
                        isFirstPing = false;
                    }
                    
                    // Visual Online/Offline UI mapping
                    const markerElement = busMarker.getElement();
                    if (markerElement) {
                        const iconContainer = markerElement.querySelector('#live_bus_container');
                        const iconBg = markerElement.querySelector('#live_bus_bg');
                        
                        if (data.is_online) {
                            if (iconContainer) { iconContainer.classList.remove('border-red-500', 'animate-pulse'); iconContainer.classList.add('border-indigo-500'); }
                            if (iconBg) { iconBg.classList.remove('bg-red-500'); iconBg.classList.add('bg-indigo-500'); }
                        } else {
                            if (iconContainer) { iconContainer.classList.remove('border-indigo-500'); iconContainer.classList.add('border-red-500', 'animate-pulse'); }
                            if (iconBg) { iconBg.classList.remove('bg-indigo-500'); iconBg.classList.add('bg-red-500'); }
                        }
                    }
                }
            })
            .catch(console.error);
        };

        // Fire instantly on map load, then repeat
        fetchLiveLocation();
        setInterval(fetchLiveLocation, 4000);
    });
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
