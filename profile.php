<?php
$page_title = 'My Profile';
$active_page = 'profile';
require_once __DIR__ . '/../layouts/header.php';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = clean($_POST['full_name']);
    $phone = clean($_POST['phone']);
    
    $updateStmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
    if ($updateStmt->execute([$full_name, $phone, $profile->id])) {
        // Update session name if changed
        $_SESSION['full_name'] = $full_name;
        set_flash('success', 'Profile updated successfully!');
    } else {
        set_flash('error', 'Failed to update profile.');
    }
    redirect('profile.php');
}
?>

<div class="max-w-4xl mx-auto">
    <!-- Profile Header -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 mb-8 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 rounded-full bg-gradient-to-br from-primary-400/20 to-indigo-500/20 blur-3xl"></div>
        
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 relative z-10">
            <div class="w-32 h-32 rounded-full bg-gradient-to-tr from-primary-500 to-indigo-600 flex items-center justify-center text-white text-5xl font-bold shadow-xl border-4 border-white dark:border-gray-800 shrink-0">
                <?= strtoupper(substr($profile->full_name, 0, 1)) ?>
            </div>
            
            <div class="text-center sm:text-left flex-1">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2"><?= htmlspecialchars($profile->full_name) ?></h1>
                <div class="flex flex-col sm:flex-row items-center sm:items-center gap-3 sm:gap-6 text-gray-500 dark:text-gray-400 mb-4">
                    <span class="flex items-center gap-1.5"><i class="ph ph-envelope-simple text-lg"></i> <?= htmlspecialchars($profile->email) ?></span>
                    <?php if(!empty($profile->phone)): ?>
                    <span class="flex items-center gap-1.5"><i class="ph ph-phone text-lg"></i> <?= htmlspecialchars($profile->phone) ?></span>
                    <?php endif; ?>
                </div>
                
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 border border-primary-200 dark:border-primary-800 shadow-sm">
                    <i class="ph ph-user"></i> Passenger Account
                </span>
            </div>
            
            <div class="shrink-0 hidden sm:block">
                <a href="change_password.php" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl font-medium transition flex items-center gap-2">
                    <i class="ph ph-lock-key"></i> Security
                </a>
            </div>
        </div>
    </div>

    <!-- Edit Profile Form -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2">
                <i class="ph ph-pencil-simple text-primary-500"></i> Edit Personal Information
            </h3>
        </div>
        
        <form method="POST" class="p-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Full Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name</label>
                    <input type="text" name="full_name" required value="<?= htmlspecialchars($profile->full_name) ?>" 
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-gray-900 dark:text-white">
                </div>
                
                <!-- Email (Disabled) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address <span class="text-xs font-normal text-gray-400">(Read Only)</span></label>
                    <input type="email" value="<?= htmlspecialchars($profile->email) ?>" disabled 
                           class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>
                
                <!-- Phone -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Contact Phone</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="ph ph-phone text-gray-400 text-lg"></i>
                        </div>
                        <input type="text" name="phone" value="<?= htmlspecialchars($profile->phone ?? '') ?>" placeholder="e.g. 0300-1234567"
                               class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-gray-900 dark:text-white">
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Used for optional SMS alerts and emergency contacts.</p>
                </div>
            </div>
            
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                <a href="change_password.php" class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 sm:hidden">
                    Change Password instead
                </a>
                <button type="submit" name="update_profile" class="w-full sm:w-auto px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all focus:ring-4 focus:ring-primary-500/30">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
