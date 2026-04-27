<?php
$page_title = 'My Route Guide';
$active_page = 'my_route';
require_once __DIR__ . '/../layouts/header.php';

if ($_SESSION['role_name'] != 'Driver') {
    set_flash('error', 'Unauthorized Access. Driver privileges required.');
    redirect(BASE_URL . '/login.php');
}

// Fetch driver id
$stmt = $pdo->prepare("SELECT id FROM drivers WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$driver = $stmt->fetch();

$today = date('Y-m-d');
$route = null;
$points = [];

if (!$driver) {
    if (in_array($_SESSION['role_name'], ['Super Admin', 'Admin'])) {
        // For admins checking the page, show the first available trip today
        $stmt_trips = $pdo->prepare("SELECT r.*, t.start_time 
                                     FROM trips t 
                                     JOIN routes r ON t.route_id = r.id 
                                     WHERE t.trip_date = ?
                                     ORDER BY t.start_time ASC LIMIT 1");
        $stmt_trips->execute([$today]);
        $route = $stmt_trips->fetch();
    } else {
        set_flash('error', 'No driver profile associated with your account.');
        redirect(BASE_URL . '/index.php');
    }
} else {
    // Ensure statically assigned routes are visible as trips today
    auto_generate_daily_trips($pdo, $driver->id, $today);

    // Find today's route for the driver
    $stmt_trips = $pdo->prepare("SELECT r.*, t.start_time 
                                 FROM trips t 
                                 JOIN routes r ON t.route_id = r.id 
                                 WHERE t.driver_id = ? AND t.trip_date = ?
                                 ORDER BY t.start_time ASC LIMIT 1");
    $stmt_trips->execute([$driver->id, $today]);
    $route = $stmt_trips->fetch();
}

if ($route) {
    $stmt_points = $pdo->prepare("SELECT * FROM bus_points WHERE route_id = ? ORDER BY sequence_order ASC");
    $stmt_points->execute([$route->id]);
    $points = $stmt_points->fetchAll();
}
?>

<div class="max-w-4xl mx-auto flex flex-col gap-8">
    <!-- Header Card -->
    <div class="bg-gray-900 dark:bg-gray-800 rounded-[2.5rem] p-8 md:p-10 text-white shadow-2xl relative overflow-hidden group">
        <!-- Abstract Decoration -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary-600/20 to-transparent pointer-events-none"></div>
        <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-primary-500 blur-[100px] rounded-full opacity-10 group-hover:opacity-20 transition-opacity duration-1000"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex-1">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 rounded-full text-[10px] font-black uppercase tracking-widest text-primary-300 border border-white/10 mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary-400 animate-pulse"></span>
                    Today's Assignment
                </div>
                <?php if ($route): ?>
                    <h1 class="text-4xl font-black tracking-tighter mb-2"><?= htmlspecialchars($route->route_name) ?></h1>
                    <div class="flex flex-wrap items-center gap-4 text-gray-400 font-medium">
                        <span class="flex items-center gap-1.5 text-sm bg-gray-800 dark:bg-gray-900 px-3 py-1 rounded-lg border border-white/5"><i class="ph ph-navigation-arrow text-primary-500"></i> <?= $route->distance_km ?> km total distance</span>
                        <span class="flex items-center gap-1.5 text-sm bg-gray-800 dark:bg-gray-900 px-3 py-1 rounded-lg border border-white/5"><i class="ph ph-users text-primary-500"></i> Main Service Route</span>
                    </div>
                <?php else: ?>
                    <h1 class="text-4xl font-black tracking-tighter mb-2 text-gray-400">Standby Duty</h1>
                    <p class="text-gray-500 font-medium">No route has been specifically assigned to your docket for today.</p>
                <?php endif; ?>
            </div>

            <?php if ($route): ?>
            <div class="flex flex-col items-center md:items-end gap-1">
                <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Navigation Path</p>
                <div class="flex items-center gap-3 bg-white/5 p-4 rounded-2xl border border-white/10 backdrop-blur-sm shadow-xl">
                    <div class="text-center">
                        <span class="text-[10px] block opacity-40 uppercase font-black">From</span>
                        <span class="font-bold text-sm"><?= htmlspecialchars($route->start_point) ?></span>
                    </div>
                    <i class="ph ph-arrow-right text-primary-500"></i>
                    <div class="text-center">
                        <span class="text-[10px] block opacity-40 uppercase font-black">To</span>
                        <span class="font-bold text-sm"><?= htmlspecialchars($route->end_point) ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stops Sequence Timeline -->
    <?php if ($route): ?>
    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/10 flex justify-between items-center">
            <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tighter flex items-center gap-3">
                <i class="ph ph-faders-horizontal text-primary-600"></i>
                Stops Sequence Guide
            </h3>
            <span class="text-[10px] font-black bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-3 py-1 rounded-lg uppercase tracking-widest"><?= count($points) ?> Waypoints</span>
        </div>

        <div class="p-8 md:p-12 relative">
            <?php if (empty($points)): ?>
                <div class="text-center py-12">
                    <div class="w-20 h-20 bg-gray-100 dark:bg-gray-900/50 rounded-3xl flex items-center justify-center text-gray-300 mx-auto mb-6">
                        <i class="ph ph-map-pin-slash text-4xl"></i>
                    </div>
                    <p class="text-lg font-bold text-gray-400 uppercase tracking-widest">No Waypoints Defined</p>
                </div>
            <?php else: ?>
                <!-- Vertical Progress Line -->
                <div class="absolute left-[3.25rem] top-12 bottom-12 w-1 bg-gradient-to-b from-primary-500 via-primary-400 to-indigo-500 rounded-full opacity-20 hidden md:block"></div>
                
                <div class="space-y-6 md:space-y-12 relative">
                    <?php foreach ($points as $index => $p): ?>
                        <div class="flex flex-col md:flex-row items-center md:items-start gap-6 group">
                            <!-- Time Node -->
                            <div class="shrink-0 w-full md:w-32 text-center md:text-right">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-100 dark:bg-gray-900 rounded-xl text-gray-900 dark:text-white font-black text-xs border border-gray-200 dark:border-gray-700 shadow-sm group-hover:bg-primary-600 group-hover:text-white group-hover:border-primary-500 transition-all duration-300">
                                    <i class="ph ph-clock"></i>
                                    <?= format_time($p->arrival_time) ?>
                                </span>
                            </div>

                            <!-- Visual Node Dot -->
                            <div class="hidden md:flex shrink-0 w-8 h-8 rounded-full bg-white dark:bg-gray-800 border-4 border-primary-500 shadow-xl group-hover:scale-125 transition-transform duration-300 z-10 items-center justify-center">
                                <div class="w-1.5 h-1.5 bg-primary-500 rounded-full"></div>
                            </div>

                            <!-- Stop Content -->
                            <div class="flex-1 w-full bg-gray-50 dark:bg-gray-900 p-6 md:p-8 rounded-3xl border border-gray-100 dark:border-gray-700 group-hover:border-primary-500/30 group-hover:shadow-2xl group-hover:shadow-primary-500/10 transition-all duration-500 relative overflow-hidden text-center md:text-left">
                                <!-- Background accent -->
                                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                                    <i class="ph ph-map-pin text-6xl"></i>
                                </div>
                                
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-2">
                                    <h4 class="text-xl font-black text-gray-900 dark:text-white tracking-tight"><?= htmlspecialchars($p->point_name) ?></h4>
                                    <?php if ($index === 0): ?>
                                        <span class="text-[10px] font-black text-primary-600 uppercase tracking-widest px-2 py-0.5 bg-primary-50 dark:bg-primary-900/30 rounded-lg self-center md:self-auto">Start Terminal</span>
                                    <?php elseif ($index === count($points) - 1): ?>
                                        <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg self-center md:self-auto">End Terminal</span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($p->latitude && $p->longitude): ?>
                                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest flex items-center justify-center md:justify-start gap-1.5">
                                        <i class="ph ph-crosshair text-sm"></i>
                                        Locked: <?= htmlspecialchars($p->latitude) ?>, <?= htmlspecialchars($p->longitude) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
