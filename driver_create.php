<?php
$page_title = 'Add New Driver';
$active_page = 'drivers';
require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_users');
$buses_stmt = $pdo->prepare("SELECT bus_number, plate_number FROM buses WHERE status = 'active' ORDER BY bus_number ASC");
$buses_stmt->execute();
$buses = $buses_stmt->fetchAll();
?>

<div class="max-w-2xl mx-auto flex flex-col gap-8">
    <!-- Breadcrumbs/Back -->
    <div class="flex items-center justify-between">
        <a href="drivers.php" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary-600 transition-colors">
            <i class="ph ph-arrow-left"></i>
            Back to Drivers List
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xl shadow-gray-200/50 dark:shadow-none overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <i class="ph ph-steering-wheel text-primary-600 border border-primary-200 dark:border-primary-800/50 p-2 rounded-xl bg-primary-50 dark:bg-primary-900/30"></i>
                    Create Driver Account
                </h2>
                <p class="text-xs text-gray-500 mt-1">Add a new driver to the system and assign a bus.</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 font-bold border border-primary-200 dark:border-primary-800/50 shadow-sm shrink-0">
                <i class="ph ph-plus text-xl"></i>
            </div>
        </div>

        <form action="process_action.php" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="action" value="add_driver">
            <input type="hidden" name="role_id" value="3">
            <input type="hidden" name="redirect" value="drivers.php">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Full Name -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Full Name</label>
                    <div class="relative">
                        <i class="ph ph-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="full_name" required placeholder="Enter full name" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition outline-none text-gray-900 dark:text-white">
                    </div>
                </div>

                <!-- Email -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Email Address</label>
                    <div class="relative">
                        <i class="ph ph-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="email" name="email" required placeholder="Enter email address" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition outline-none text-gray-900 dark:text-white">
                    </div>
                </div>

                <!-- Phone Number -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Phone Number</label>
                    <div class="relative">
                        <i class="ph ph-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="phone" required placeholder="923XXXXXXXXX" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition outline-none text-gray-900 dark:text-white">
                    </div>
                </div>

                <!-- License Number -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">License Number</label>
                    <div class="relative">
                        <i class="ph ph-identification-card absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="license_no" required placeholder="Enter license number" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition outline-none text-gray-900 dark:text-white">
                    </div>
                </div>

                <!-- Bus Number -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Assign Bus</label>
                    <div class="relative">
                        <i class="ph ph-bus absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <select name="bus_number" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition outline-none appearance-none text-gray-900 dark:text-white">
                            <option value="">-- No Bus Assigned --</option>
                            <?php foreach ($buses as $b): ?>
                                <option value="<?= htmlspecialchars($b->bus_number) ?>">
                                    <?= htmlspecialchars($b->bus_number . ' (' . $b->plate_number . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="ph ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>

                <!-- Password -->
                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Password</label>
                    <div class="relative">
                        <i class="ph ph-key absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" name="password" value="driver123" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition outline-none text-gray-900 dark:text-white">
                    </div>
                    <p class="text-[10px] text-gray-500 font-medium">Default password is <span class="font-mono bg-gray-100 dark:bg-gray-700/50 px-1 py-0.5 rounded text-gray-600 dark:text-gray-300">driver123</span>. The driver can change it later.</p>
                </div>
            </div>

            <div class="pt-6">
                <button type="submit" class="w-full flex items-center justify-center gap-2 py-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/30 transition-all duration-200 transform hover:scale-[1.01]">
                    <i class="ph ph-check-circle text-xl"></i>
                    Create Driver Account
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>