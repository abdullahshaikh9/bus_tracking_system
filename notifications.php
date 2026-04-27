<?php
$page_title = 'My Notifications';
$active_page = 'dashboard'; 
require_once __DIR__ . '/../layouts/functions.php';

require_login();
$user = get_user($pdo, $_SESSION['user_id']);

// Mark as read action
if (isset($_GET['mark_read'])) {
    $notif_id = (int)$_GET['mark_read'];
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND (target_user_id = ? OR target_user_id IS NULL)");
    $stmt->execute([$notif_id, $_SESSION['user_id']]);
    redirect('notifications.php');
}

if (isset($_GET['mark_all'])) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE target_user_id = ? OR (target_user_id IS NULL AND target_role_id = ?)");
    $stmt->execute([$_SESSION['user_id'], $user->role_id]);
    redirect('notifications.php');
}

require_once __DIR__ . '/../layouts/header.php';

// Fetch notifications for this user (either specifically targeted to them, or to their role)
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE target_user_id = ? OR (target_user_id IS NULL AND target_role_id = ?)
    ORDER BY created_at DESC 
    LIMIT 50
");
$stmt->execute([$_SESSION['user_id'], $user->role_id]);
$notifications = $stmt->fetchAll();
?>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="ph ph-bell-ringing text-primary-500"></i> Notifications
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Stay updated with route changes and system alerts.</p>
        </div>
        
        <?php if (!empty($notifications) && array_search(0, array_column($notifications, 'is_read')) !== false): ?>
            <a href="?mark_all=1" class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 px-4 py-2 rounded-xl transition-colors">
                Mark All as Read
            </a>
        <?php endif; ?>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <?php if (empty($notifications)): ?>
            <div class="p-12 text-center text-gray-500 dark:text-gray-400">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                    <i class="ph ph-bell-slash text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No Notifications</h3>
                <p class="text-sm">You're all caught up! There are no new alerts right now.</p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                <?php foreach ($notifications as $notif): 
                    $is_unread = !$notif->is_read;
                    $icon = 'info';
                    $color = 'blue';
                    if ($notif->type === 'warning') { $icon = 'warning-circle'; $color = 'amber'; }
                    elseif ($notif->type === 'alert') { $icon = 'warning-octagon'; $color = 'red'; }
                ?>
                <div class="p-6 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/30 <?= $is_unread ? 'bg-primary-50/30 dark:bg-primary-900/10' : '' ?>">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-<?= $color ?>-100 dark:bg-<?= $color ?>-900/30 text-<?= $color ?>-600 dark:text-<?= $color ?>-400 flex items-center justify-center shrink-0">
                            <i class="ph ph-<?= $icon ?> text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between gap-4">
                                <h4 class="font-bold text-gray-900 dark:text-white mb-1 <?= $is_unread ? 'text-primary-900 dark:text-primary-100' : '' ?>">
                                    <?= htmlspecialchars($notif->title) ?>
                                    <?php if ($is_unread): ?>
                                        <span class="inline-block w-2 h-2 rounded-full bg-primary-500 ml-2 animate-pulse"></span>
                                    <?php endif; ?>
                                </h4>
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium whitespace-nowrap">
                                    <?= format_time($notif->created_at) ?>
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                <?= nl2br(htmlspecialchars($notif->message)) ?>
                            </p>
                            
                            <?php if ($is_unread): ?>
                            <div class="mt-3">
                                <a href="?mark_read=<?= $notif->id ?>" class="text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 transition-colors">
                                    Mark as read
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
