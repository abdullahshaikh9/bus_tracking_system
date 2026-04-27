<?php
$page_title = 'Trip Management';
$active_page = 'trips';
require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_routes');

$trips_stmt = $pdo->prepare("SELECT t.*, r.route_name, r.start_point, r.end_point, b.bus_number, d.license_number, u.full_name as driver_name 
                      FROM trips t 
                      JOIN routes r ON t.route_id = r.id 
                      JOIN buses b ON t.bus_id = b.id 
                      JOIN drivers d ON t.driver_id = d.id 
                      JOIN users u ON d.user_id = u.id
                      ORDER BY t.trip_date DESC, t.start_time DESC");
$trips_stmt->execute();
$trips = $trips_stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 px-4 sm:px-0">
        <div>
            <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <i class="ph ph-calendar-check text-primary-600 dark:text-primary-400"></i> Scheduled Trips
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-medium">Manage and monitor all active, upcoming, and past trips.</p>
        </div>
        <a href="trip_create.php" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-400 text-white font-bold rounded-xl shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all w-full sm:w-auto justify-center">
            <i class="ph ph-calendar-plus text-xl"></i> Schedule New Trip
        </a>
    </div>

    <!-- Data Table -->
    <div class="px-4 sm:px-0">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Date & Time</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Route Path</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Assigned Bus & Driver</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Live Status</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        <?php if (empty($trips)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 mb-4">
                                    <i class="ph ph-calendar-blank text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No Trips Scheduled</h3>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">Schedule your first trip to begin monitoring operations.</p>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php foreach ($trips as $t): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 dark:text-white text-base">
                                    <?= date("M d, Y", strtotime($t->trip_date)); ?>
                                </div>
                                <div class="inline-flex items-center gap-1.5 font-bold text-gray-500 dark:text-gray-400 text-sm mt-0.5">
                                    <i class="ph ph-clock text-primary-500 text-sm"></i>
                                    <?= format_time($t->start_time); ?>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-black text-gray-900 dark:text-white text-sm mb-1"><?= htmlspecialchars($t->route_name); ?></div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-md border border-gray-200 dark:border-gray-700">
                                        <?= htmlspecialchars($t->start_point); ?>
                                    </span>
                                    <i class="ph ph-arrow-right text-gray-400 text-xs"></i>
                                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-md border border-gray-200 dark:border-gray-700">
                                        <?= htmlspecialchars($t->end_point); ?>
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-100 to-indigo-200 dark:from-indigo-900/40 dark:to-indigo-800/40 border border-indigo-200 dark:border-indigo-700 flex items-center justify-center font-bold text-xs text-indigo-700 dark:text-indigo-400 shrink-0 shadow-sm">
                                        <?= strtoupper(substr($t->driver_name, 0, 1)); ?>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-900 dark:text-white leading-tight mb-0.5"><?= htmlspecialchars($t->driver_name); ?></span>
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-500 bg-gray-100 dark:bg-gray-700/50 px-1.5 py-px rounded w-fit">
                                            <i class="ph ph-bus-fill"></i> Bus #<?= htmlspecialchars($t->bus_number); ?>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <?php
                                $status = $t->status;
                                $pillClass = 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600';
                                $dotClass = 'bg-gray-400';
                                
                                if ($status == 'in_progress') {
                                    $pillClass = 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800/50';
                                    $dotClass = 'bg-indigo-500 animate-pulse';
                                } elseif ($status == 'completed') {
                                    $pillClass = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800/50';
                                    $dotClass = 'bg-emerald-500';
                                } elseif ($status == 'cancelled') {
                                    $pillClass = 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800/50';
                                    $dotClass = 'bg-red-500';
                                }
                                ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border <?= $pillClass ?> tracking-wide">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $dotClass ?>"></span> <?= ucfirst(str_replace('_',' ', $status)); ?>
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="trip_edit.php?id=<?= $t->id; ?>" class="p-2 text-primary-600 hover:text-primary-700 hover:bg-primary-50 dark:text-primary-400 dark:hover:text-primary-300 dark:hover:bg-primary-900/30 rounded-lg transition-colors" title="Edit Trip">
                                        <i class="ph ph-pencil-simple text-xl"></i>
                                    </a>
                                    <a href="trip_delete.php?id=<?= $t->id; ?>" class="p-2 text-red-500 hover:text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-900/30 rounded-lg transition-colors" onclick="return confirm('Obliterate this trip entirely?')" title="Delete Trip">
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