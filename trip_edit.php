<?php
$page_title = 'Edit Trip';
$active_page = 'trips';

require_once __DIR__ . '/../layouts/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash('error', 'No trip ID specified');
    redirect('trips.php');
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM trips WHERE id = ?");
$stmt->execute([$id]);
$trip = $stmt->fetch();

if (!$trip) {
    set_flash('error', 'Scheduled trip not found');
    redirect('trips.php');
}

require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_routes');

$routes_stmt = $pdo->prepare("SELECT id, route_name, start_point, end_point FROM routes");
$routes_stmt->execute();
$routes = $routes_stmt->fetchAll();

$buses_stmt = $pdo->prepare("SELECT id, bus_number, capacity FROM buses");
$buses_stmt->execute();
$buses = $buses_stmt->fetchAll();

$drivers_stmt = $pdo->prepare("SELECT d.id, u.full_name, d.license_number FROM drivers d JOIN users u ON d.user_id = u.id");
$drivers_stmt->execute();
$drivers = $drivers_stmt->fetchAll();
?>

<div class="max-w-2xl mx-auto flex flex-col gap-8">
    <div class="flex items-center justify-between">
        <a href="trips.php" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary-600 transition-colors">
            <i class="ph ph-arrow-left"></i>
            Back to Trips
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xl overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <i class="ph ph-calendar-check text-primary-600"></i>
                    Edit Scheduled Trip
                </h2>
                <p class="text-xs text-gray-500 mt-1">Adjust trip details, assignments, or update current progress status.</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 font-bold border border-primary-200 dark:border-primary-800/50 shadow-sm shrink-0">
                <i class="ph ph-clock-countdown text-2xl"></i>
            </div>
        </div>

        <form action="process_action.php" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="action" value="edit_trip">
            <input type="hidden" name="id" value="<?= $trip->id ?>">
            <input type="hidden" name="redirect" value="trips.php">
            
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Select Transit Route</label>
                <div class="relative">
                    <i class="ph ph-path absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <select name="route_id" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 transition outline-none appearance-none">
                        <?php foreach ($routes as $r): ?>
                            <option value="<?= $r->id; ?>" <?= $r->id == $trip->route_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r->route_name); ?> (<?= htmlspecialchars($r->start_point); ?> ➔ <?= htmlspecialchars($r->end_point); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="ph ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Bus Assignment -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Assign Bus</label>
                    <div class="relative">
                        <i class="ph ph-bus absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <select name="bus_id" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 transition outline-none appearance-none">
                            <?php foreach ($buses as $b): ?>
                                <option value="<?= $b->id; ?>" <?= $b->id == $trip->bus_id ? 'selected' : '' ?>>
                                    #<?= htmlspecialchars($b->bus_number); ?> (Cap: <?= $b->capacity; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="ph ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>

                <!-- Driver Assignment -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Assign Driver</label>
                    <div class="relative">
                        <i class="ph ph-identification-badge absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <select name="driver_id" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 transition outline-none appearance-none">
                            <?php foreach ($drivers as $d): ?>
                                <option value="<?= $d->id; ?>" <?= $d->id == $trip->driver_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d->full_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="ph ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>

                <!-- Trip Date -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Departure Date</label>
                    <div class="relative">
                        <i class="ph ph-calendar absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="date" name="trip_date" value="<?= $trip->trip_date ?>" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 transition outline-none">
                    </div>
                </div>

                <!-- Start Time -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Departure Time</label>
                    <div class="relative">
                        <i class="ph ph-clock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="time" name="start_time" value="<?= $trip->start_time ?>" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 transition outline-none">
                    </div>
                </div>

                <!-- Status Assignment -->
                <div class="md:col-span-2 space-y-2 pt-2 border-t border-gray-100 dark:border-gray-700/50 mt-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Update Voyage Status</label>
                    <div class="relative">
                        <i class="ph ph-activity absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <select name="status" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 transition outline-none appearance-none">
                             <option value="scheduled" <?= $trip->status == 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                             <option value="in_progress" <?= $trip->status == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                             <option value="completed" <?= $trip->status == 'completed' ? 'selected' : '' ?>>Completed</option>
                             <option value="cancelled" <?= $trip->status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <i class="ph ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
            </div>

            <div class="pt-6 flex flex-col gap-3">
                <button type="submit" class="w-full py-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/30 transition-all duration-200">
                    Save Trip Adjustments
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
