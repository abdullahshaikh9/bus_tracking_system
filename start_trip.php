<?php
require_once __DIR__ . '/../layouts/functions.php';

if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] != 'Driver') {
    die("Unauthorized Access. Driver privileges required.");
}

$stmt = $pdo->prepare("SELECT id FROM drivers WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$driver = $stmt->fetch();

$today = date('Y-m-d');
$stmt_trips = $pdo->prepare("SELECT t.*, r.route_name 
                             FROM trips t 
                             JOIN routes r ON t.route_id = r.id 
                             WHERE t.driver_id = ? AND t.trip_date = ? AND t.status != 'completed'
                             ORDER BY t.start_time ASC LIMIT 1");
$stmt_trips->execute([$driver ? $driver->id : 0, $today]);
$trip = $stmt_trips->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'start_trip' && $trip) {
        $pdo->prepare("UPDATE trips SET status = 'in_progress' WHERE id = ?")->execute([$trip->id]);
    } elseif ($_POST['action'] == 'complete_trip' && $trip) {
        $pdo->prepare("UPDATE trips SET status = 'completed' WHERE id = ?")->execute([$trip->id]);
    }
    header("Location: start_trip.php?msg=success");
    exit();
}

$page_title = 'Start & Manage Trip';
$active_page = 'start_trip';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-2xl mx-auto py-8">
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden text-center">
        
        <div class="bg-gray-900 dark:bg-black text-white p-6">
            <h3 class="text-xl font-black flex items-center justify-center gap-2">
                <i class="ph ph-play-circle text-2xl text-primary-500"></i> Live Trip Controller
            </h3>
        </div>
        
        <div class="p-8">
            <?php if (!$trip): ?>
                <div class="text-6xl mb-4">✅</div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">No Pending Trips</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">You have no active or scheduled trips remaining for today.</p>
                <a href="dashboard.php" class="inline-flex px-6 py-3 bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-400 text-white font-bold rounded-xl transition-all">
                    Return Home
                </a>
            <?php else: ?>
                <h2 class="text-2xl font-black text-primary-600 dark:text-primary-400 mb-2">
                    <?php echo htmlspecialchars($trip->route_name); ?>
                </h2>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-8">
                    Scheduled Time: <strong class="text-gray-900 dark:text-white"><?php echo format_time($trip->start_time); ?></strong>
                </p>

                <?php if ($trip->status == 'scheduled'): ?>
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-8 border border-dashed border-gray-200 dark:border-gray-700 rounded-2xl mb-8">
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Ready to depart? Starting the trip will allow you to broadcast your live location on the passenger portal.</p>
                        <form method="POST">
                            <input type="hidden" name="action" value="start_trip">
                            <button type="submit" class="px-8 py-4 bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-400 text-white font-black text-lg rounded-full shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all flex items-center justify-center gap-2 mx-auto w-full sm:w-auto">
                                <i class="ph ph-rocket-launch text-2xl"></i> Start Trip Now
                            </button>
                        </form>
                    </div>
                <?php elseif ($trip->status == 'in_progress'): ?>
                    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-500/30 p-8 rounded-2xl mb-8 relative overflow-hidden">
                        <i class="ph ph-broadcast text-9xl absolute -top-4 -right-4 text-emerald-500/10 pointer-events-none"></i>
                        
                        <h3 class="text-emerald-600 dark:text-emerald-400 font-black text-2xl mb-2 flex items-center justify-center gap-3 relative z-10">
                            <span class="w-3 h-3 bg-emerald-500 rounded-full animate-ping absolute"></span>
                            <span class="w-3 h-3 bg-emerald-500 rounded-full relative"></span>
                            Trip In Progress
                        </h3>
                        <p class="text-emerald-800 dark:text-emerald-200 text-sm font-bold mb-8 relative z-10">System is active.</p>

                        <div class="space-y-4 relative z-10">
                            <!-- Link to Broadcast Panel -->
                            <a href="<?= BASE_URL ?>/driver/broadcast.php" target="_blank" class="flex px-6 py-4 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-400 text-white w-full rounded-xl font-bold justify-center items-center gap-2 transition-colors">
                                <i class="ph ph-broadcast text-xl"></i> Launch Live GPS Broadcast
                            </a>

                            <form method="POST" class="m-0">
                                <input type="hidden" name="action" value="complete_trip">
                                <button type="submit" class="flex px-6 py-4 bg-white dark:bg-gray-800 border-2 border-emerald-500 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/40 w-full rounded-xl font-bold justify-center items-center gap-2 transition-colors">
                                    <i class="ph ph-check-circle text-xl"></i> Mark Trip as Completed
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
