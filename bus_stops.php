<?php
$page_title = 'All Bus Stops';
$active_page = 'bus_stops';
require_once __DIR__ . '/../layouts/header.php';

// Fetch all points grouped by route
$stmt = $pdo->prepare("SELECT p.*, r.route_name FROM bus_points p JOIN routes r ON p.route_id = r.id ORDER BY r.route_name ASC, p.sequence_order ASC");
$stmt->execute();
$all_stops = $stmt->fetchAll();

$grouped = [];
foreach ($all_stops as $s) {
    $grouped[$s->route_name][] = $s;
}
?>

<div class="mb-6 bg-gradient-to-r from-gray-900 to-primary-800 dark:from-gray-800 dark:to-primary-950 rounded-xl shadow-lg border border-primary-500 dark:border-primary-700 overflow-hidden">
    <div class="p-8">
        <h2 class="font-extrabold text-3xl text-white mb-2 flex items-center gap-2">
            <i class="ph ph-map-pin text-primary-400"></i> Campus Bus Stops Directory
        </h2>
        <p class="text-primary-100 text-lg m-0">Find exactly when buses are expected to arrive at your local stop.</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
    <?php foreach ($grouped as $route_name => $stops): ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden border-t-4 border-t-primary-500 flex flex-col hover:shadow-md transition-shadow">
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-bold text-gray-900 dark:text-white text-lg flex items-center gap-2 m-0">
                    <i class="ph ph-bus text-primary-500"></i> <?php echo htmlspecialchars($route_name); ?>
                </h3>
            </div>
            <div class="overflow-x-auto flex-1 p-0 m-0">
                <table class="w-full text-left border-collapse m-0">
                    <thead class="bg-white dark:bg-gray-800 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 border-b border-gray-200 dark:border-gray-700 font-semibold">Stop Name</th>
                            <th class="px-6 py-3 border-b border-gray-200 dark:border-gray-700 font-semibold w-24 text-right">ETA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        <?php foreach ($stops as $index => $s): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300">
                                <div class="flex items-center gap-2">
                                    <?php if ($index == 0): ?>
                                        <i class="ph-fill ph-circle text-[10px] text-green-500"></i>
                                    <?php elseif ($index == count($stops)-1): ?>
                                        <i class="ph-fill ph-circle text-[10px] text-red-500"></i>
                                    <?php else: ?>
                                        <i class="ph ph-circle text-[10px] text-primary-500 font-bold"></i>
                                    <?php endif; ?>
                                    <strong class="text-gray-900 dark:text-white truncate max-w-[200px]"><?php echo htmlspecialchars($s->point_name); ?></strong>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <span class="inline-flex items-center justify-center bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-bold px-2.5 py-1 rounded w-full border border-primary-100 dark:border-primary-800 text-xs whitespace-nowrap">
                                    <?php echo format_time($s->arrival_time); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
