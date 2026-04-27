<?php
$page_title = 'Trip Planner';
$active_page = 'trip_planner';
require_once __DIR__ . '/../layouts/header.php';

// Fetch all possible points (from bus_points and basic routes table)
$points_stmt = $pdo->prepare("
    SELECT DISTINCT point_name FROM bus_points 
    UNION 
    SELECT DISTINCT start_point FROM routes
    UNION
    SELECT DISTINCT end_point FROM routes
    ORDER BY point_name ASC
");
$points_stmt->execute();
$points = $points_stmt->fetchAll(PDO::FETCH_COLUMN);

$results = [];
$searched = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
    $searched = true;
    $from = clean($_POST['from']);
    $to = clean($_POST['to']);
    
    if ($from && $to) {
        // Query 1: Find via detailed bus points
        $stmt_points = $pdo->prepare("
            SELECT r.*, 
                   p1.arrival_time as departure_time, 
                   p2.arrival_time as arrival_time 
            FROM routes r
            JOIN bus_points p1 ON r.id = p1.route_id
            JOIN bus_points p2 ON r.id = p2.route_id
            WHERE p1.point_name = ? 
              AND p2.point_name = ? 
              AND p1.sequence_order < p2.sequence_order
            ORDER BY p1.arrival_time ASC
        ");
        $stmt_points->execute([$from, $to]);
        $results1 = $stmt_points->fetchAll();

        // Query 2: Find via basic route start/end points (Fallback for routes without detailed points mapped)
        $stmt_basic = $pdo->prepare("
            SELECT r.*, 
                   '08:00:00' as departure_time, 
                   '09:00:00' as arrival_time 
            FROM routes r
            WHERE r.start_point = ? AND r.end_point = ?
        ");
        $stmt_basic->execute([$from, $to]);
        $results2 = $stmt_basic->fetchAll();

        // Merge results, removing duplicates based on route ID
        $all_results = array_merge($results1, $results2);
        $unique_routes = [];
        foreach ($all_results as $r) {
            if (!isset($unique_routes[$r->id])) {
                $unique_routes[$r->id] = $r;
            }
        }
        $results = array_values($unique_routes);
    }
}
?>

<div class="max-w-4xl mx-auto mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="ph ph-compass-rose text-primary-500 text-xl"></i> Plan Your Journey
            </h3>
        </div>
        <div class="p-6">
            <form method="POST" class="grid grid-cols-1 md:grid-cols-[1fr_1fr_auto] gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Leaving From</label>
                    <select name="from" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 py-2.5 px-4 shadow-sm transition">
                        <option value="">Select Origin...</option>
                        <?php foreach ($points as $p): ?>
                            <option value="<?php echo htmlspecialchars($p); ?>" <?php echo (isset($_POST['from']) && $_POST['from'] === $p) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Going To</label>
                    <select name="to" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 py-2.5 px-4 shadow-sm transition">
                        <option value="">Select Destination...</option>
                        <?php foreach ($points as $p): ?>
                            <option value="<?php echo htmlspecialchars($p); ?>" <?php echo (isset($_POST['to']) && $_POST['to'] === $p) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" name="search" class="w-full md:w-auto px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg shadow-sm transition-colors flex items-center justify-center gap-2 h-[46px]">
                    <i class="ph ph-magnifying-glass text-lg"></i> Search
                </button>
            </form>
        </div>
    </div>
</div>

<?php if ($searched): ?>
<div class="max-w-4xl mx-auto">
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
        <i class="ph ph-map-pin-line text-primary-500 text-xl"></i> Route Matches Found
    </h3>
    
    <?php if (empty($results)): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-8 text-center text-red-600 dark:text-red-400">
            <i class="ph ph-warning-circle text-5xl mb-3"></i>
            <h4 class="text-xl font-bold mb-2">No Direct Routes Available</h4>
            <p class="text-red-500 dark:text-red-300">Sorry, there are no direct buses between <strong><?php echo htmlspecialchars($_POST['from']); ?></strong> and <strong><?php echo htmlspecialchars($_POST['to']); ?></strong>.</p>
        </div>
    <?php else: ?>
        <div class="flex flex-col gap-4">
            <?php foreach ($results as $index => $r): ?>
                <div class="relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm hover:shadow-md transition">
                    <?php if ($index === 0): ?>
                        <div class="absolute top-0 right-0 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-bl-lg rounded-tr-xl">FASTEST</div>
                    <?php endif; ?>
                    
                    <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
                        <div>
                            <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-1">
                                <?php echo htmlspecialchars($r->route_name); ?>
                            </h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Route Distance: <?php echo $r->distance_km; ?> km</p>
                        </div>
                        <a href="route_details.php?id=<?php echo $r->id; ?>" class="inline-flex items-center justify-center px-4 py-2 bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-medium rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/50 transition whitespace-nowrap">
                            View Journey Details
                        </a>
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-dashed border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <div class="text-center flex-1">
                            <strong class="block text-2xl text-gray-900 dark:text-white"><?php echo format_time($r->departure_time); ?></strong>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Departure (<?php echo htmlspecialchars($_POST['from']); ?>)</span>
                        </div>
                        
                        <div class="flex-1 text-center px-2">
                            <i class="ph ph-bus text-2xl text-primary-500 mb-1"></i>
                            <div class="h-0.5 w-full bg-primary-200 dark:bg-primary-800 rounded-full my-1">
                                <div class="h-full bg-primary-500 w-full rounded-full opacity-50"></div>
                            </div>
                            <span class="text-[10px] font-bold text-primary-600 dark:text-primary-400 tracking-wider">DIRECT</span>
                        </div>
                        
                        <div class="text-center flex-1">
                            <strong class="block text-2xl text-gray-900 dark:text-white"><?php echo format_time($r->arrival_time); ?></strong>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Arrival (<?php echo htmlspecialchars($_POST['to']); ?>)</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
