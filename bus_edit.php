<?php
$page_title = 'Edit Bus';
$active_page = 'buses';

require_once __DIR__ . '/../layouts/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash('error', 'No bus ID specified');
    redirect('buses.php');
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM buses WHERE id = ?");
$stmt->execute([$id]);
$bus = $stmt->fetch();

if (!$bus) {
    set_flash('error', 'Bus record not found');
    redirect('buses.php');
}

require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_routes');
?>

<div class="max-w-2xl mx-auto flex flex-col gap-8">
    <div class="flex items-center justify-between">
        <a href="buses.php" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary-600 transition-colors">
            <i class="ph ph-arrow-left"></i>
            Back to Fleet
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xl overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <i class="ph ph-bus text-primary-600"></i>
                    Edit Bus: <?= htmlspecialchars($bus->bus_number) ?>
                </h2>
                <p class="text-xs text-gray-500 mt-1">Modify vehicle details and service availability.</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 font-bold border border-primary-200 dark:border-primary-800/50 shadow-sm shrink-0">
                <i class="ph ph-bus text-2xl"></i>
            </div>
        </div>

        <form action="process_action.php" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="action" value="edit_bus">
            <input type="hidden" name="id" value="<?= $bus->id ?>">
            <input type="hidden" name="redirect" value="buses.php">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Bus Number -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Bus Number / ID</label>
                    <div class="relative">
                        <i class="ph ph-identification-card absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="bus_number" value="<?= htmlspecialchars($bus->bus_number) ?>" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 transition outline-none">
                    </div>
                </div>

                <!-- Plate Number -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">License Plate</label>
                    <div class="relative">
                        <i class="ph ph-cardholder absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="plate_number" value="<?= htmlspecialchars($bus->plate_number) ?>" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 transition outline-none">
                    </div>
                </div>

                <!-- Capacity -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Seating Capacity</label>
                    <div class="relative">
                        <i class="ph ph-users absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="number" name="capacity" value="<?= (int)$bus->capacity ?>" min="1" max="100" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 transition outline-none">
                    </div>
                </div>

                <!-- Status -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Service Status</label>
                    <div class="relative">
                        <i class="ph ph-activity absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <select name="status" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 transition outline-none appearance-none">
                            <option value="active" <?= $bus->status == 'active' ? 'selected' : '' ?>>Active Service</option>
                            <option value="maintenance" <?= $bus->status == 'maintenance' ? 'selected' : '' ?>>Under Maintenance</option>
                            <option value="out_of_service" <?= $bus->status == 'out_of_service' ? 'selected' : '' ?>>Out of Service</option>
                        </select>
                        <i class="ph ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex flex-col gap-3">
                <button type="submit" class="w-full py-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/30 transition-all duration-200">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
