<?php
$page_title = 'System Reports & Logs';
$active_page = 'reports';
require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'view_reports');

$logs_stmt = $pdo->prepare("SELECT l.*, u.full_name 
                     FROM logs l 
                     LEFT JOIN users u ON l.user_id = u.id 
                     ORDER BY l.created_at DESC LIMIT 50");
$logs_stmt->execute();
$logs = $logs_stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 px-4 sm:px-0">
        <div>
            <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <i class="ph ph-scroll text-primary-600 dark:text-primary-400"></i> Audit Logs
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-medium">Track all administrative actions and system security events.</p>
        </div>
        <a href="export_reports.php" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 font-bold rounded-xl shadow-sm hover:shadow-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-all w-full sm:w-auto justify-center">
            <i class="ph ph-download-simple text-xl"></i> Export CSV
        </a>
    </div>

    <!-- Data Table -->
    <div class="px-4 sm:px-0">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Occurred At</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Actor</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Operation</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Event Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 mb-4">
                                    <i class="ph ph-file-search text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No Activity Logged</h3>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">System events will appear here as they occur.</p>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php foreach ($logs as $l): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">
                                        <?= date("M d, Y", strtotime($l->created_at)) ?>
                                    </span>
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                        <i class="ph ph-clock text-primary-500"></i>
                                        <?= date("H:i", strtotime($l->created_at)) ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center font-bold text-xs text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-600 shrink-0 shadow-sm">
                                        <?= strtoupper(substr($l->full_name ?? 'S', 0, 1)) ?>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($l->full_name ?? 'System'); ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $action = strtoupper($l->action);
                                $badgeClass = 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
                                if (str_contains($action, 'DELETE') || str_contains($action, 'REMOVE')) $badgeClass = 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800/50';
                                elseif (str_contains($action, 'CREATE') || str_contains($action, 'ADD')) $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800/50';
                                elseif (str_contains($action, 'UPDATE') || str_contains($action, 'EDIT')) $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800/50';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black border tracking-widest <?= $badgeClass ?>">
                                    <?= $action ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600 dark:text-gray-400 font-medium max-w-md truncate lg:whitespace-normal" title="<?= htmlspecialchars($l->details) ?>">
                                    <?= htmlspecialchars($l->details); ?>
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