<?php
$page_title = 'Change Password';
$active_page = 'profile'; // Keep profile active in sidebar
require_once __DIR__ . '/../layouts/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!password_verify($current_password, $user->password)) {
        set_flash('error', 'Incorrect current password.');
    } elseif ($new_password !== $confirm_password) {
        set_flash('error', 'New passwords do not match.');
    } elseif (strlen($new_password) < 6) {
        set_flash('error', 'New password must be at least 6 characters long.');
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($updateStmt->execute([$hashed, $_SESSION['user_id']])) {
            set_flash('success', 'Password successfully updated.');
        } else {
            set_flash('error', 'Failed to update password.');
        }
    }
    redirect('change_password.php');
}
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="profile.php" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition">
            <i class="ph ph-arrow-left"></i> Back to Profile
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-8 text-center border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
            <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-white dark:border-gray-800 shadow-sm">
                <i class="ph ph-shield-check text-3xl hidden md:block"></i>
                <i class="ph ph-shield-check text-4xl md:hidden"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Security Settings</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Update your password to keep your account secure.</p>
        </div>

        <form method="POST" class="p-8 space-y-6">
            <!-- Current Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current Password</label>
                <input type="password" name="current_password" required placeholder="••••••••"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-gray-900 dark:text-white">
            </div>

            <!-- New Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">New Password</label>
                <input type="password" name="new_password" required placeholder="••••••••"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-gray-900 dark:text-white">
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Must be at least 6 characters long.</p>
            </div>

            <!-- Confirm New Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm New Password</label>
                <input type="password" name="confirm_password" required placeholder="••••••••"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-gray-900 dark:text-white">
            </div>

            <div class="pt-6 border-t border-gray-100 dark:border-gray-700">
                <button type="submit" name="change_password" class="w-full px-8 py-3.5 bg-gray-900 hover:bg-black dark:bg-primary-600 dark:hover:bg-primary-700 text-white font-medium rounded-xl shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all focus:ring-4 focus:ring-gray-300 dark:focus:ring-primary-500/30">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
