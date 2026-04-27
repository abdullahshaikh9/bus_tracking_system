<?php
$page_title = 'Edit User';
$active_page = 'users';

require_once __DIR__ . '/../layouts/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash('error', 'No user ID specified');
    redirect('users.php');
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$edit_user = $stmt->fetch();

if (!$edit_user) {
    set_flash('error', 'User not found');
    redirect('users.php');
}

require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_users');

$roles_stmt = $pdo->prepare("SELECT * FROM roles");
$roles_stmt->execute();
$roles = $roles_stmt->fetchAll();
?>

<div class="max-w-2xl mx-auto flex flex-col gap-8">
    <!-- Breadcrumbs/Back -->
    <div class="flex items-center justify-between">
        <a href="users.php" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary-600 transition-colors">
            <i class="ph ph-arrow-left"></i>
            Back to User List
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xl shadow-gray-200/50 dark:shadow-none overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <i class="ph ph-user-focus text-primary-600"></i>
                    Edit User: <?= htmlspecialchars($edit_user->full_name) ?>
                </h2>
                <p class="text-xs text-gray-500 mt-1">Update account details, role, and system permissions.</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 font-bold border border-primary-200 dark:border-primary-800/50 shadow-sm shrink-0">
                <?= strtoupper(substr($edit_user->full_name, 0, 1)) ?>
            </div>
        </div>

        <form action="process_action.php" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="action" value="edit_user">
            <input type="hidden" name="id" value="<?= $edit_user->id ?>">
            <input type="hidden" name="redirect" value="users.php">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Full Name -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Full Name</label>
                    <div class="relative">
                        <i class="ph ph-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($edit_user->full_name) ?>" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition outline-none">
                    </div>
                </div>

                <!-- Email -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Email Address</label>
                    <div class="relative">
                        <i class="ph ph-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="email" name="email" value="<?= htmlspecialchars($edit_user->email) ?>" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition outline-none">
                    </div>
                </div>

                <!-- Role -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">System Role</label>
                    <div class="relative">
                        <i class="ph ph-shield-check absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <?php $disable_role = ($edit_user->id == 1 && $_SESSION['user_id'] != 1); ?>
                        <select name="role_id" required <?= $disable_role ? 'disabled' : '' ?> class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition outline-none appearance-none disabled:opacity-75">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r->id ?>" <?= $r->id == $edit_user->role_id ? 'selected' : '' ?>><?= $r->name ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($disable_role): ?>
                            <input type="hidden" name="role_id" value="<?= $edit_user->role_id ?>">
                        <?php endif; ?>
                        <i class="ph ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>

                <!-- Status -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Account Status</label>
                    <div class="relative">
                        <i class="ph ph-toggle-left absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <select name="status" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition outline-none appearance-none">
                            <option value="active" <?= $edit_user->status == 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $edit_user->status == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                        <i class="ph ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>

                <!-- Phone Number -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Phone Number</label>
                    <div class="relative">
                        <i class="ph ph-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="phone" value="<?= htmlspecialchars($edit_user->phone ?? '') ?>" placeholder="923XXXXXXXXX" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition outline-none">
                    </div>
                </div>

                <!-- Password Update -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                        Update Password 
                        <span class="text-[10px] font-normal text-gray-400 uppercase tracking-widest">(Leave blank to keep current)</span>
                    </label>
                    <div class="relative">
                        <i class="ph ph-key absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" name="password" placeholder="Enter new password" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition outline-none">
                    </div>
                </div>
            </div>

            <div class="pt-6 flex flex-col gap-3">
                <button type="submit" class="w-full py-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/30 transition-all duration-200 transform hover:scale-[1.01]">
                    Save Changes
                </button>
                <div class="flex items-center justify-center gap-4 py-2 border-t border-gray-50 dark:border-gray-700/50 mt-2">
                    <span class="text-[10px] text-gray-400 flex items-center gap-1">
                        <i class="ph ph-clock"></i> Last Updated: <?= date("M d, Y H:i", strtotime($edit_user->created_at)) ?>
                    </span>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
