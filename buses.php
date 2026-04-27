<?php
$page_title = 'Bus Fleet Management';
$active_page = 'buses';
require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_routes');

$buses_stmt = $pdo->prepare("SELECT * FROM buses ORDER BY bus_number ASC");
$buses_stmt->execute();
$buses = $buses_stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 px-4 sm:px-0">
        <div>
            <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <i class="ph ph-bus text-primary-600 dark:text-primary-400"></i> Bus Fleet
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-medium">Manage buses and their operational status</p>
        </div>
        <a href="bus_create.php" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-400 text-white font-bold rounded-xl shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all w-full sm:w-auto justify-center">
            <i class="ph ph-plus-circle text-xl"></i> Add Bus
        </a>
    </div>

    <?php
    $total = count($buses);
    $active = count(array_filter($buses, fn($b) => $b->status === 'active'));
    $maintenance = count(array_filter($buses, fn($b) => $b->status === 'maintenance'));
    $oos = $total - $active - $maintenance;
    ?>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 px-4 sm:px-0 mb-8">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col">
            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Total Fleet</span>
            <span class="text-3xl font-black text-gray-900 dark:text-white"><?= $total ?></span>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-emerald-100 dark:border-emerald-900/30 flex flex-col relative overflow-hidden">
            <div class="absolute -right-4 -top-4 text-emerald-500/10 text-7xl"><i class="ph ph-check-circle"></i></div>
            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-1 relative z-10">Active</span>
            <span class="text-3xl font-black text-emerald-700 dark:text-emerald-300 relative z-10"><?= $active ?></span>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-amber-100 dark:border-amber-900/30 flex flex-col relative overflow-hidden">
            <div class="absolute -right-4 -top-4 text-amber-500/10 text-7xl"><i class="ph ph-wrench"></i></div>
            <span class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-1 relative z-10">Maintenance</span>
            <span class="text-3xl font-black text-amber-700 dark:text-amber-300 relative z-10"><?= $maintenance ?></span>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-red-100 dark:border-red-900/30 flex flex-col relative overflow-hidden">
            <div class="absolute -right-4 -top-4 text-red-500/10 text-7xl"><i class="ph ph-warning-circle"></i></div>
            <span class="text-xs font-bold text-red-600 dark:text-red-400 uppercase tracking-wider mb-1 relative z-10">Out of Service</span>
            <span class="text-3xl font-black text-red-700 dark:text-red-300 relative z-10"><?= $oos ?></span>
        </div>
    </div>

    <!-- Data Table -->
    <div class="px-4 sm:px-0">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Bus Identifier</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Plate Tag</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Capacity</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Status</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        <?php if (empty($buses)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 mb-4">
                                    <i class="ph ph-bus text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No Buses Registered</h3>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">Add your first vehicle to the fleet to begin routing.</p>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php foreach ($buses as $b): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center text-primary-600 dark:text-primary-400 shrink-0">
                                        <i class="ph ph-bus text-xl"></i>
                                    </div>
                                    <span class="font-black text-gray-900 dark:text-white text-lg block"><?= htmlspecialchars($b->bus_number) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-mono text-sm font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 uppercase">
                                    <?= htmlspecialchars($b->plate_number) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">
                                <?= (int)$b->capacity ?> <span class="text-gray-400 font-medium text-xs">seats</span>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                    $status = $b->status;
                                    $bgClass = 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800/50';
                                    $dotClass = 'bg-red-500';
                                    if ($status == 'active') {
                                        $bgClass = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800/50';
                                        $dotClass = 'bg-emerald-500';
                                    } elseif ($status == 'maintenance') {
                                        $bgClass = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800/50';
                                        $dotClass = 'bg-amber-500';
                                    }
                                ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black border <?= $bgClass ?> tracking-wide">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $dotClass ?>"></span> <?= ucfirst(str_replace('_',' ', $status)) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="bus_edit.php?id=<?= $b->id ?>" class="p-2 text-primary-600 hover:text-primary-700 hover:bg-primary-50 dark:text-primary-400 dark:hover:text-primary-300 dark:hover:bg-primary-900/30 rounded-lg transition-colors" title="Edit Bus">
                                        <i class="ph ph-pencil-simple text-xl"></i>
                                    </a>
                                    <a href="bus_delete.php?id=<?= $b->id ?>" class="p-2 text-red-500 hover:text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-900/30 rounded-lg transition-colors" onclick="return confirm('Delete this bus irrevocably?')" title="Delete Bus">
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