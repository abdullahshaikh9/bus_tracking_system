<?php
$page_title = 'Bus Points & Timing';
$active_page = 'points';
require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_routes');

$filter_route_id = isset($_GET['route_id']) ? (int)$_GET['route_id'] : 0;

if ($filter_route_id > 0) {
    $stmt = $pdo->prepare("SELECT bp.*, r.route_name 
                           FROM bus_points bp 
                           JOIN routes r ON bp.route_id = r.id 
                           WHERE bp.route_id = ?
                           ORDER BY bp.sequence_order");
    $stmt->execute([$filter_route_id]);
    $points = $stmt->fetchAll();
} else {
    $points_stmt = $pdo->prepare("SELECT bp.*, r.route_name 
                           FROM bus_points bp 
                           JOIN routes r ON bp.route_id = r.id 
                           ORDER BY bp.route_id, bp.sequence_order");
    $points_stmt->execute();
    $points = $points_stmt->fetchAll();
}

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

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 px-4 sm:px-0">
        <div>
            <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <i class="ph ph-map-pin-line text-primary-600 dark:text-primary-400"></i> Route Waypoints
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-medium">Manage and sequence bus stop schedules and ETAs.</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <div class="relative">
                <i class="ph ph-funnel absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <select class="pl-10 pr-10 py-3 sm:py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm font-semibold text-gray-900 dark:text-white shadow-sm appearance-none outline-none transition-colors w-full sm:w-auto" onchange="window.location.href='?route_id=' + this.value">
                    <option value="0">All Transit Routes</option>
                    <?php foreach ($routes as $r): ?>
                        <option value="<?php echo $r->id; ?>" <?php echo $filter_route_id == $r->id ? 'selected' : ''; ?>>
                            <?php 
                                $driver_info = $r->driver_name ? " - Driver: {$r->driver_name}" : "";
                                echo htmlspecialchars($r->route_name . $driver_info); 
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <i class="ph ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
            </div>
            
            <a href="bus_point_create.php" class="inline-flex items-center gap-2 px-6 py-3 sm:py-2.5 bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-400 text-white font-bold rounded-xl shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all text-sm justify-center">
                <i class="ph ph-plus-circle text-lg"></i> Add Waypoint
            </a>
        </div>
    </div>

    <!-- Data Table -->
    <div class="px-4 sm:px-0">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Route Group</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Stop Name</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Seq</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Arrival Checkpoint</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        <?php if (empty($points)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 mb-4">
                                    <i class="ph ph-map-pin text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No Waypoints Mapped</h3>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">Add stops to this route to build the timeline.</p>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php foreach ($points as $p): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                                    <?= htmlspecialchars($p->route_name); ?>
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center text-primary-600 dark:text-primary-400 shrink-0">
                                        <i class="ph ph-map-pin-fill text-sm"></i>
                                    </div>
                                    <span class="font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($p->point_name); ?></span>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="w-8 h-8 flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded-lg text-xs font-black text-gray-500 dark:text-gray-400">
                                    #<?= htmlspecialchars($p->sequence_order); ?>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="inline-flex items-center gap-1.5 font-bold text-gray-600 dark:text-gray-300">
                                    <i class="ph ph-clock text-primary-500"></i>
                                    <?= format_time($p->arrival_time); ?>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="bus_point_edit.php?id=<?= $p->id ?>" class="p-2 text-primary-600 hover:text-primary-700 hover:bg-primary-50 dark:text-primary-400 dark:hover:text-primary-300 dark:hover:bg-primary-900/30 rounded-lg transition-colors" title="Edit Waypoint">
                                        <i class="ph ph-pencil-simple text-xl"></i>
                                    </a>
                                    <a href="bus_point_delete.php?id=<?= $p->id ?>" class="p-2 text-red-500 hover:text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-900/30 rounded-lg transition-colors" onclick="return confirm('Remove this stop from the route?')" title="Delete Waypoint">
                                        <i class="ph ph-trash text-xl"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>