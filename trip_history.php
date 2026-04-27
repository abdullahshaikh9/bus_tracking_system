<?php
$page_title = 'My Trip History';
$active_page = 'trip_history';
require_once __DIR__ . '/../layouts/header.php';

if ($_SESSION['role_name'] != 'Driver') {
    die("Unauthorized Access.");
}

// Get driver ID
$stmt = $pdo->prepare("SELECT id FROM drivers WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$driver = $stmt->fetch();

// Fetch last 50 completed trips
$hx_stmt = $pdo->prepare("
    SELECT t.*, r.route_name, b.bus_number 
    FROM trips t 
    JOIN routes r ON t.route_id = r.id 
    JOIN buses b ON t.bus_id = b.id 
    WHERE t.driver_id = ? AND t.status = 'completed'
    ORDER BY t.trip_date DESC, t.start_time DESC 
    LIMIT 50
");
$hx_stmt->execute([$driver->id]);
$history = $hx_stmt->fetchAll();
?>

<div class="max-w-6xl mx-auto py-8">
    <div class="mb-8 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h2 class="text-3xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <i class="ph ph-clock-counter-clockwise text-primary-600 dark:text-primary-400"></i> My Trip History
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1 font-medium">Log of your recently completed assignments</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                        <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Date & Time</th>
                        <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Route Driven</th>
                        <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Bus Used</th>
                        <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 mb-4">
                                    <i class="ph ph-calendar-x text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No Completed Trips</h3>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">You haven't driven any trips on record yet.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $h): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 relative">
                                    <div class="font-bold text-gray-900 dark:text-white"><?php echo date("F j, Y", strtotime($h->trip_date)); ?></div>
                                    <div class="text-xs font-bold text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-0.5">
                                        <i class="ph ph-clock"></i> <?php echo format_time($h->start_time); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-primary-600 dark:text-primary-400"><?php echo htmlspecialchars($h->route_name); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        <i class="ph ph-bus"></i> <?php echo htmlspecialchars($h->bus_number); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800/50 tracking-wide">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Completed
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>