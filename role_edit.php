<?php
$page_title = 'Edit Role';
$active_page = 'roles';

require_once __DIR__ . '/../layouts/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash('error', 'No role ID specified');
    redirect('roles.php');
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
$stmt->execute([$id]);
$role = $stmt->fetch();

if (!$role) {
    set_flash('error', 'Role not found');
    redirect('roles.php');
}

require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_roles');
?>

<div class="max-w-2xl mx-auto flex flex-col gap-8">
    <div class="flex items-center justify-between">
        <a href="roles.php" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary-600 transition-colors">
            <i class="ph ph-arrow-left"></i>
            Back to Roles
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xl overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <i class="ph ph-shield-check text-primary-600"></i>
                    Edit Role: <?= htmlspecialchars($role->name) ?>
                </h2>
                <p class="text-xs text-gray-500 mt-1">Update the identity or description for this access level.</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 font-bold border border-primary-200 dark:border-primary-800/50 shadow-sm shrink-0">
                <i class="ph ph-pencil-line text-2xl"></i>
            </div>
        </div>

        <form action="process_action.php" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="action" value="edit_role">
            <input type="hidden" name="id" value="<?= $role->id ?>">
            <input type="hidden" name="redirect" value="roles.php">
            
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Role Identity</label>
                <div class="relative">
                    <i class="ph ph-identification-card absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="name" value="<?= htmlspecialchars($role->name) ?>" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 transition outline-none">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Description (Optional)</label>
                <div class="relative">
                    <textarea name="description" rows="4" class="w-full p-4 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 transition outline-none resize-none"><?= htmlspecialchars($role->description) ?></textarea>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full py-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/30 transition-all duration-200">
                    Save Authority Level
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
