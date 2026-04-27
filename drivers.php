<?php
$page_title = 'Driver Management';
$active_page = 'drivers';
require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_users');

// Get the Driver role ID
$role_stmt = $pdo->prepare("SELECT id FROM roles WHERE LOWER(name) = 'driver'");
$role_stmt->execute();
$driver_role = $role_stmt->fetchColumn();

// Fetch all users with Driver role, left join with drivers table for additional info
$drivers = $pdo->prepare("SELECT u.id as user_id, u.full_name, u.email, u.phone, u.status, d.*
                          FROM users u
                          LEFT JOIN drivers d ON u.id = d.user_id
                          WHERE u.role_id = ?
                          ORDER BY u.created_at DESC");
$drivers->execute([$driver_role]);
$drivers = $drivers->fetchAll(PDO::FETCH_OBJ);
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 px-4 sm:px-0">
        <div>
            <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <i class="ph ph-steering-wheel text-primary-600 dark:text-primary-400"></i> Driver Management
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-medium">Verify credentials and manage all registered bus operators.</p>
        </div>
        <a href="driver_create.php" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-400 text-white font-bold rounded-xl shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all w-full sm:w-auto justify-center">
            <i class="ph ph-user-plus text-xl"></i> Add New Driver
        </a>
    </div>

    <!-- Data Table -->
    <div class="px-4 sm:px-0">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Driver Identity</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Contact Info</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Credentials</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Broadcast Status</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Assigned Bus</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        <?php if (empty($drivers)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 mb-4">
                                    <i class="ph ph-users text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No Drivers Found</h3>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">Create a user with the 'Driver' role to see them listed here.</p>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php foreach ($drivers as $d): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-100 to-indigo-100 dark:from-primary-900/40 dark:to-indigo-900/40 flex items-center justify-center text-primary-700 dark:text-primary-400 font-bold shrink-0 shadow-sm border border-primary-200 dark:border-primary-800">
                                        <?= strtoupper(substr($d->full_name, 0, 1)) ?>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-gray-900 dark:text-white text-sm"><?= htmlspecialchars($d->full_name) ?></div>
                                        <div class="text-gray-500 dark:text-gray-400 text-xs truncate mt-0.5"><?= htmlspecialchars($d->email) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-700 dark:text-gray-300">
                                <?= htmlspecialchars($d->phone ?? '-') ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 tracking-wide uppercase">
                                    <i class="ph ph-identification-card text-sm"></i>
                                    <?= htmlspecialchars($d->license_number ?? 'Pending') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($d->is_online ?? false): ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800/50 tracking-wide">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Broadcasting
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-50 text-gray-500 border border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700 tracking-wide">
                                        <span class="w-2 h-2 rounded-full bg-gray-400"></span> Offline
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($d->bus_number): ?>
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800/50 tracking-wide">
                                        <i class="ph ph-bus text-sm"></i>
                                        Bus #<?= htmlspecialchars($d->bus_number) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs font-bold text-gray-400 italic">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="driver_edit.php?id=<?= $d->user_id ?>" class="p-2 text-primary-600 hover:text-primary-700 hover:bg-primary-50 dark:text-primary-400 dark:hover:text-primary-300 dark:hover:bg-primary-900/30 rounded-lg transition-colors" title="Edit Driver">
                                        <i class="ph ph-pencil-simple text-xl"></i>
                                    </a>
                                    <a href="driver_delete.php?id=<?= $d->user_id ?>" class="p-2 text-red-500 hover:text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-900/30 rounded-lg transition-colors" onclick="return confirm('Permanently remove this driver?')" title="Delete Driver">
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