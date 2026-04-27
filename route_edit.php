<?php
$page_title = 'Edit Route';
$active_page = 'routes';

require_once __DIR__ . '/../layouts/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash('error', 'No route ID specified');
    redirect('routes.php');
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM routes WHERE id = ?");
$stmt->execute([$id]);
$route = $stmt->fetch();

if (!$route) {
    set_flash('error', 'Route not found');
    redirect('routes.php');
}

require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_routes');

$drivers_stmt = $pdo->prepare("SELECT d.id, u.full_name, d.bus_number 
                        FROM drivers d 
                        JOIN users u ON d.user_id = u.id");
$drivers_stmt->execute();
$drivers = $drivers_stmt->fetchAll();
?>

<div class="max-w-2xl mx-auto flex flex-col gap-8">
    <div class="flex items-center justify-between">
        <a href="routes.php" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary-600 transition-colors">
            <i class="ph ph-arrow-left"></i>
            Back to Routes
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xl overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <i class="ph ph-map-trifold text-primary-600"></i>
                    Edit Route: <?= htmlspecialchars($route->route_name) ?>
                </h2>
                <p class="text-xs text-gray-500 mt-1">Modify the transit path, timing, and driver assignments.</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 font-bold border border-primary-200 dark:border-primary-800/50 shadow-sm shrink-0">
                <i class="ph ph-path text-2xl"></i>
            </div>
        </div>

        <form action="process_action.php" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="action" value="edit_route">
            <input type="hidden" name="id" value="<?= $route->id ?>">
            <input type="hidden" name="redirect" value="routes.php">
            
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Route Name</label>
                <div class="relative">
                    <i class="ph ph-tag absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="route_name" value="<?= htmlspecialchars($route->route_name) ?>" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 transition outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Start Point -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Departure Point</label>
                    <div class="relative">
                        <i class="ph ph-map-pin absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="start_point" value="<?= htmlspecialchars($route->start_point) ?>" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 transition outline-none">
                    </div>
                </div>

                <!-- End Point -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Arrival Point</label>
                    <div class="relative">
                        <i class="ph ph-flag absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="end_point" value="<?= htmlspecialchars($route->end_point) ?>" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 transition outline-none">
                    </div>
                </div>

                <!-- Departure Time -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Scheduled Departure</label>
                    <div class="relative">
                        <i class="ph ph-clock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="time" name="departure_time" value="<?= htmlspecialchars($route->departure_time) ?>" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 transition outline-none">
                    </div>
                </div>

                <!-- Distance -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Total Distance (KM)</label>
                    <div class="relative">
                        <i class="ph ph-ruler absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="number" step="0.01" name="distance_km" value="<?= htmlspecialchars($route->distance_km) ?>" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 transition outline-none">
                    </div>
                </div>

                <!-- Driver Assignment -->
                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Assigned Driver (Primary)</label>
                    <div class="relative">
                        <i class="ph ph-steering-wheel absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <select name="driver_id" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 transition outline-none appearance-none">
                            <option value="">-- No Driver Assigned --</option>
                            <?php foreach ($drivers as $d): ?>
                                <option value="<?= $d->id; ?>" <?= $d->id == $route->driver_id ? 'selected' : '' ?>><?= htmlspecialchars($d->full_name); ?> (Bus #<?= htmlspecialchars($d->bus_number); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <i class="ph ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex flex-col gap-3">
                <button type="submit" class="w-full py-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/30 transition-all duration-200">
                    Update Transit Path
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
