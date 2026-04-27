<?php
$page_title = 'Account Settings';
$active_page = 'profile'; 
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Account Settings</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage your preferences and interface settings.</p>
    </div>

    <!-- Appearance Settings -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden mb-8">
        <div class="px-8 py-5 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="ph ph-palette text-primary-500 text-xl"></i> Appearance
            </h3>
        </div>
        
        <div class="p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Dark Mode</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Toggle between light and dark themes.</p>
                </div>
                
                <!-- Toggle switch (Alpine JS bound) -->
                <button @click="darkMode = !darkMode" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" :class="darkMode ? 'bg-primary-600' : 'bg-gray-200'">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="darkMode ? 'translate-x-6' : 'translate-x-1'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Notification Settings (Toggles UI only for now) -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden mb-8">
        <div class="px-8 py-5 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="ph ph-bell text-primary-500 text-xl"></i> Notifications
            </h3>
        </div>
        
        <div class="p-8 space-y-6" x-data="{ emailAlerts: true, pushAlerts: false }">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Email Notifications</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Receive alerts regarding your routes via email.</p>
                </div>
                <!-- Toggle -->
                <button @click="emailAlerts = !emailAlerts" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" :class="emailAlerts ? 'bg-primary-600' : 'bg-gray-200'">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="emailAlerts ? 'translate-x-6' : 'translate-x-1'"></span>
                </button>
            </div>
            
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6 flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Push Alerts (Browser)</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Receive live pop-ups for bus delays and alerts.</p>
                </div>
                <!-- Toggle -->
                <button @click="pushAlerts = !pushAlerts" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" :class="pushAlerts ? 'bg-primary-600' : 'bg-gray-200'">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="pushAlerts ? 'translate-x-6' : 'translate-x-1'"></span>
                </button>
            </div>
        </div>
    </div>
    
    <div class="flex justify-end pt-4">
        <button onclick="alert('Settings saved successfully!');" class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl shadow-md transition-colors">
            Save Preferences
        </button>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
