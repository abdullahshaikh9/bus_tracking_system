<?php
$page_title = 'My Bus Details';
$active_page = 'my_bus';
require_once __DIR__ . '/../layouts/header.php';

if ($_SESSION['role_name'] != 'Driver') {
    set_flash('error', 'Unauthorized Access. Driver privileges required.');
    redirect(BASE_URL . '/login.php');
}

$stmt = $pdo->prepare("SELECT id, bus_number as profile_bus FROM drivers WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$driver = $stmt->fetch();

$today = date('Y-m-d');
$stmt_bus = $pdo->prepare("SELECT b.* 
                           FROM trips t 
                           JOIN buses b ON t.bus_id = b.id 
                           WHERE t.driver_id = ? AND t.trip_date = ?
                           ORDER BY t.start_time ASC LIMIT 1");
$stmt_bus->execute([$driver->id, $today]);
$bus = $stmt_bus->fetch();

// If no trip today, check profile defaults
if (!$bus && $driver->profile_bus) {
    $stmt_fallback = $pdo->prepare("SELECT * FROM buses WHERE bus_number = ?");
    $stmt_fallback->execute([$driver->profile_bus]);
    $bus = $stmt_fallback->fetch();
}
?>

<div class="max-w-3xl mx-auto flex flex-col gap-8">
    <!-- Bus Identity Section -->
    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-100 dark:border-gray-700 shadow-xl overflow-hidden relative group">
        <!-- Abstract gradient background -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 to-transparent pointer-events-none"></div>
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary-500 blur-3xl rounded-full opacity-10 group-hover:opacity-20 transition-opacity"></div>
        
        <div class="p-8 md:p-12 flex flex-col items-center text-center relative z-10">
            <?php if (!$bus): ?>
                <div class="w-32 h-32 rounded-3xl bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-300 mb-6 border-2 border-dashed border-gray-200 dark:border-gray-600">
                    <i class="ph ph-bus text-6xl"></i>
                </div>
                <h2 class="text-3xl font-black text-gray-900 dark:text-white uppercase tracking-tight mb-2">No Vehicle Assigned</h2>
                <p class="text-gray-500 dark:text-gray-400 max-w-sm">You aren't currently linked to a specific vehicle for today's duty. Please check your schedule.</p>
                <div class="mt-8">
                    <a href="dashboard.php" class="px-8 py-3 bg-gray-900 dark:bg-white dark:text-gray-950 text-white rounded-xl font-bold shadow-lg transition-transform active:scale-95">View Duty Schedule</a>
                </div>
            <?php else: ?>
                <div class="inline-flex items-center justify-center px-4 py-1.5 bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 rounded-full text-[10px] font-black uppercase tracking-widest border border-primary-100 dark:border-primary-800/50 mb-6">
                    Assigned Duty Vehicle
                </div>
                
                <div class="w-40 h-40 rounded-[2.5rem] bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-500 shadow-inner border-2 border-indigo-100 dark:border-indigo-800/50 mb-6 group-hover:scale-110 transition-transform duration-500">
                    <i class="ph ph-bus text-7xl"></i>
                </div>
                
                <h1 class="text-5xl font-black text-gray-950 dark:text-white tracking-tighter mb-2"><?= htmlspecialchars($bus->bus_number) ?></h1>
                <p class="text-lg font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.3em] mb-8"><?= htmlspecialchars($bus->plate_number) ?></p>
                
                <div class="grid grid-cols-2 gap-4 w-full">
                    <div class="p-6 bg-gray-50 dark:bg-gray-900/50 rounded-3xl border border-gray-100 dark:border-gray-700 flex flex-col items-center gap-1">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Seating Capacity</span>
                        <span class="text-2xl font-black text-gray-900 dark:text-white"><?= $bus->capacity ?>+</span>
                    </div>
                    <div class="p-6 bg-gray-50 dark:bg-gray-900/50 rounded-3xl border border-gray-100 dark:border-gray-700 flex flex-col items-center gap-1">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Condition</span>
                        <span class="text-2xl font-black <?= $bus->status == 'active' ? 'text-emerald-500' : 'text-amber-500' ?>">
                            <?= strtoupper(str_replace('_', ' ', $bus->status)) ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Technical Status & Quick Actions -->
    <?php if ($bus): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-amber-50 dark:bg-amber-900/10 p-8 rounded-[2rem] border border-amber-100 dark:border-amber-800/50 flex flex-col justify-between">
            <div>
                <h4 class="text-lg font-black text-amber-950 dark:text-amber-400 uppercase tracking-tight mb-2 flex items-center gap-2">
                    <i class="ph ph-wrench text-xl"></i>
                    Mechanical Status
                </h4>
                <p class="text-sm text-amber-800/70 dark:text-amber-500/80 font-medium leading-relaxed">Ensure pre-trip checks for tire pressure, fluid levels, and interior cleaning are completed before departure.</p>
            </div>
            <div class="mt-6 flex items-center gap-2 text-xs font-black text-amber-600 dark:text-amber-500 uppercase tracking-widest">
                <i class="ph ph-check-square"></i> Routine Checkup Passed
            </div>
        </div>

        <div class="bg-red-50 dark:bg-red-900/10 p-8 rounded-[2rem] border border-red-100 dark:border-red-800/50 flex flex-col justify-between">
            <div>
                <h4 class="text-lg font-black text-red-900 dark:text-red-400 uppercase tracking-tight mb-2 flex items-center gap-2">
                    <i class="ph ph-warning-circle text-xl"></i>
                    Issue Reporting
                </h4>
                <p class="text-sm text-red-800/70 dark:text-red-500/80 font-medium leading-relaxed">Notice any engine issues or damaged seats? Report technically defective vehicles to the fleet manager immediately.</p>
            </div>
            <div class="mt-6">
                <a href="emergency.php" class="inline-flex items-center gap-2 text-sm font-black text-red-600 hover:text-red-700 dark:text-red-400 uppercase tracking-widest transition-transform hover:translate-x-1">
                    Mark Maintenance Issue
                    <i class="ph ph-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
