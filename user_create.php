<?php
$page_title = 'Add New User';
$active_page = 'users';
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
        <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="ph ph-user-plus text-primary-600"></i>
                Create New Account
            </h2>
            <p class="text-xs text-gray-500 mt-1">Fill in the details below to register a new user or driver.</p>
        </div>

        <form action="process_action.php" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="action" value="add_user">
            <input type="hidden" name="redirect" value="users.php">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Full Name -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Full Name</label>
                    <div class="relative">
                        <i class="ph ph-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="full_name" placeholder="John Doe" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition outline-none">
                    </div>
                </div>

                <!-- Email -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Email Address</label>
                    <div class="relative">
                        <i class="ph ph-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="email" name="email" placeholder="john@example.com" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition outline-none">
                    </div>
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Password</label>
                    <div class="relative">
                        <i class="ph ph-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" name="password" placeholder="••••••••" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition outline-none">
                    </div>
                </div>

                <!-- Role -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300">System Role</label>
                    <div class="relative">
                        <i class="ph ph-shield-check absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <select name="role_id" required class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition outline-none appearance-none">
                            <option value="">Select a Role</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r->id; ?>"><?= $r->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="ph ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex flex-col gap-3">
                <button type="submit" class="w-full py-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/30 transition-all duration-200 transform hover:scale-[1.01]">
                    Register User
                </button>
                <p class="text-[10px] text-center text-gray-400">By creating an account, the user will be able to log in immediately depending on their status.</p>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
