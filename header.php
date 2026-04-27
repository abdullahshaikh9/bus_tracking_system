<?php
require_once __DIR__ . '/functions.php';
require_login();
$user = get_user($pdo, $_SESSION['user_id']);

if (!isset($_SESSION['role_name']) && $user && isset($user->role_name)) {
    $_SESSION['role_name'] = $user->role_name;
}

$unread_notifications_count = get_unread_notifications_count($pdo, $_SESSION['user_id'], $_SESSION['role_id'] ?? 0);
$recent_notifications = [];
if ($unread_notifications_count > 0) {
    $n_stmt = $pdo->prepare("SELECT id, title, message, created_at FROM notifications WHERE (target_user_id = ? OR target_role_id = ? OR (target_user_id IS NULL AND target_role_id IS NULL)) AND is_read = FALSE ORDER BY created_at DESC LIMIT 4");
    $n_stmt->execute([$_SESSION['user_id'], $_SESSION['role_id'] ?? 0]);
    $recent_notifications = $n_stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth antialiased text-gray-900 bg-gray-50 dark:bg-gray-900 dark:text-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#4f46e5">
    <title><?php echo $page_title ?? 'Dashboard'; ?> - MUET BusTracker</title>
    
    <!-- PWA Manifest & Meta -->
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BusTracker">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/icons/icon-192.png">

    <!-- Alpine.js for interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
        
        /* Mobile optimizations */
        @media (max-width: 640px) {
            .header-title {
                font-size: 1rem;
                max-width: 150px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .header-actions {
                gap: 0.25rem !important;
            }
            .user-name-desktop {
                display: none !important;
            }
        }
        
        /* Prevent horizontal scroll */
        body {
            overflow-x: hidden;
        }
        
        /* Smooth dropdown animations */
        .dropdown-enter {
            animation: dropdownEnter 0.2s ease-out;
        }
        
        @keyframes dropdownEnter {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Touch-friendly buttons */
        @media (max-width: 768px) {
            button, a {
                min-height: 44px;
                min-width: 44px;
            }
        }
    </style>

    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?= BASE_URL ?>/sw.js')
                    .then(reg => console.log('Service Worker registered'))
                    .catch(err => console.log('Service Worker registration failed', err));
            });
        }
    </script>
</head>
<body class="flex min-h-screen" x-data="{ sidebarOpen: false, darkMode: localStorage.getItem('theme') === 'dark' }" x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light')); if (window.innerWidth < 1024) sidebarOpen = false" :class="{ 'dark': darkMode }">

    <!-- Sidebar Component -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="flex flex-col flex-1 w-full min-h-screen transition-all duration-300">

        <!-- Header Topbar -->
        <header class="flex items-center justify-between px-3 sm:px-4 md:px-6 py-3 sm:py-4 bg-white border-b dark:bg-gray-800 border-gray-200 dark:border-gray-700 backdrop-blur-sm bg-white/90 dark:bg-gray-800/90 sticky top-0 z-30 shadow-sm">
            <div class="flex items-center gap-2 sm:gap-4 w-full">
                <button @click="sidebarOpen = true" class="p-2 text-gray-600 rounded-lg lg:hidden hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition flex-shrink-0" aria-label="Open menu">
                    <i class="ph ph-list text-2xl"></i>
                </button>
                <h1 class="header-title text-base sm:text-lg md:text-xl font-semibold tracking-tight text-gray-800 dark:text-white truncate flex-1">
                    <?php echo $page_title ?? 'Dashboard'; ?>
                </h1>
            </div>

            <div class="header-actions hidden lg:flex items-center gap-2 md:gap-4 flex-shrink-0">
                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode" class="p-2 text-gray-500 rounded-full hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition flex-shrink-0" aria-label="Toggle dark mode">
                    <i class="ph ph-sun text-xl"></i>
                    <i class="ph ph-moon text-xl" x-show="darkMode" x-cloak></i>
                </button>

                <!-- Notifications -->
                <div x-data="{ dropdownOpen: false }" class="relative flex-shrink-0">
                    <button @click="dropdownOpen = !dropdownOpen" @click.outside="dropdownOpen = false" class="relative p-2 text-gray-500 rounded-full hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition" aria-label="Notifications">
                        <i class="ph ph-bell text-xl"></i>
                        <?php if ($unread_notifications_count > 0): ?>
                            <span class="absolute top-1 right-1 flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500 border border-white dark:border-gray-800"></span>
                            </span>
                        <?php endif; ?>
                    </button>
                    <!-- Notifications Dropdown -->
                    <div x-show="dropdownOpen" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         class="absolute right-0 mt-2 w-72 sm:w-80 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden z-50" 
                         x-cloak>
                        <div class="p-3 sm:p-4 border-b dark:border-gray-700 flex justify-between items-center bg-gradient-to-r from-primary-50 to-white dark:from-gray-700 dark:to-gray-800">
                            <span class="font-semibold text-sm text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="ph ph-bell text-primary-500"></i> Notifications
                            </span>
                            <a href="<?= BASE_URL ?>/<?= strtolower(explode(' ', $_SESSION['role_name'])[0]) ?>/notifications.php" class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium">View All</a>
                        </div>
                        <?php if ($unread_notifications_count > 0): ?>
                            <div class="max-h-64 overflow-y-auto w-full">
                                <?php foreach ($recent_notifications as $n): ?>
                                    <div class="p-3 border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($n->title) ?></p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5"><?= htmlspecialchars($n->message) ?></p>
                                        <p class="text-[10px] text-gray-400 mt-1"><?= format_time($n->created_at) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="p-2 border-t dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-center">
                                <span class="text-[10px] text-gray-500 font-medium">You have <?= $unread_notifications_count ?> unread notification(s)</span>
                            </div>
                        <?php else: ?>
                            <div class="p-6 text-sm text-center text-gray-500 dark:text-gray-400">
                                <i class="ph ph-check-circle text-3xl text-green-500 mb-2"></i><br>
                                No new notifications
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- User Profile -->
                <div x-data="{ profileOpen: false }" class="relative flex-shrink-0">
                    <button @click="profileOpen = !profileOpen" @click.outside="profileOpen = false" class="flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-full" aria-label="User menu">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-primary-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-md ring-2 ring-white dark:ring-gray-800 transform hover:scale-105 transition flex-shrink-0">
                            <?php echo strtoupper(substr($user->full_name, 0, 1)); ?>
                        </div>
                        <div class="user-name-desktop hidden lg:block text-left min-w-0">
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 truncate max-w-[120px]"><?php echo htmlspecialchars($user->full_name); ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[120px]"><?php echo htmlspecialchars($user->role_name); ?></p>
                        </div>
                        <i class="ph ph-caret-down text-gray-400 hidden lg:block flex-shrink-0"></i>
                    </button>

                    <!-- Profile Dropdown -->
                    <div x-show="profileOpen" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 top-12 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700 py-2 z-50 overflow-hidden" 
                         x-cloak>
                        <?php
                            $role_lower = strtolower($_SESSION['role_name']);
                            $module = 'user';
                            if ($role_lower === 'super admin' || $role_lower === 'admin') $module = 'admin';
                            elseif ($role_lower === 'driver') $module = 'driver';
                        ?>
                        <div class="px-4 py-3 border-b dark:border-gray-700 bg-gradient-to-r from-primary-50 to-white dark:from-gray-700 dark:to-gray-800">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate"><?php echo htmlspecialchars($user->full_name); ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate"><?php echo htmlspecialchars($user->role_name); ?></p>
                        </div>
                        <a href="<?= BASE_URL ?>/<?= $module ?>/profile.php" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                            <i class="ph ph-user text-lg text-primary-500"></i> My Profile
                        </a>
                        <a href="<?= BASE_URL ?>/<?= $module ?>/change_password.php" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                            <i class="ph ph-lock-key text-lg text-primary-500"></i> Change Password
                        </a>
                        <div class="border-t my-1 dark:border-gray-700 border-gray-100"></div>
                        <a href="<?= BASE_URL ?>/logout.php" class="flex items-center gap-3 px-4 py-3 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                            <i class="ph ph-sign-out text-lg"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Mobile/Techbar User Bar (shown on mobile and tablet below header) -->
        <div class="lg:hidden flex items-center justify-between px-3 py-3 bg-gradient-to-r from-primary-50 via-indigo-50 to-purple-50 dark:from-gray-800 dark:via-gray-800 dark:to-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center gap-2">
                <div class="h-9 w-9 rounded-full bg-gradient-to-br from-primary-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm shadow-md ring-2 ring-white dark:ring-gray-700 flex-shrink-0">
                    <?php echo strtoupper(substr($user->full_name, 0, 1)); ?>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate max-w-[140px]"><?php echo htmlspecialchars($user->full_name); ?></p>
                    <p class="text-[10px] font-medium text-primary-600 dark:text-primary-400 truncate max-w-[140px]"><?php echo htmlspecialchars($user->role_name); ?></p>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button @click="darkMode = !darkMode" class="p-2 text-gray-600 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-700 rounded-lg transition" aria-label="Dark mode">
                    <i class="ph ph-sun text-lg" x-show="!darkMode"></i>
                    <i class="ph ph-moon text-lg" x-show="darkMode" x-cloak></i>
                </button>
                <div class="relative" x-data="{ notifOpen: false }">
                    <button @click="notifOpen = !notifOpen" class="relative p-2 text-gray-600 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-700 rounded-lg transition" aria-label="Notifications">
                        <i class="ph ph-bell text-lg"></i>
                        <?php if ($unread_notifications_count > 0): ?>
                            <span class="absolute top-1 right-1 flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500 border border-white dark:border-gray-800"></span>
                            </span>
                        <?php endif; ?>
                    </button>
                    <!-- Mobile Notifications Dropdown -->
                    <div x-show="notifOpen" 
                         @click.outside="notifOpen = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 bottom-full mb-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden z-50" 
                         x-cloak>
                        <div class="p-3 border-b dark:border-gray-700 bg-gradient-to-r from-primary-50 to-white dark:from-gray-700 dark:to-gray-800">
                            <span class="font-semibold text-sm text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="ph ph-bell text-primary-500"></i> Notifications
                            </span>
                        </div>
                        <?php if ($unread_notifications_count > 0): ?>
                            <div class="max-h-60 overflow-y-auto w-full">
                                <?php foreach ($recent_notifications as $n): ?>
                                    <div class="p-3 border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($n->title) ?></p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5"><?= htmlspecialchars($n->message) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <a href="<?= BASE_URL ?>/<?= strtolower(explode(' ', $_SESSION['role_name'])[0]) ?>/notifications.php" class="block p-3 text-xs text-primary-600 dark:text-primary-400 text-center border-t dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">View All (<?= $unread_notifications_count ?>)</a>
                        <?php else: ?>
                            <div class="p-4 text-sm text-center text-gray-500 dark:text-gray-400">
                                <i class="ph ph-check-circle text-2xl text-green-500 mb-1"></i><br>
                                No new notifications
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/<?= $module ?>/profile.php" class="p-2 text-gray-600 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-700 rounded-lg transition" aria-label="Profile">
                    <i class="ph ph-user text-lg"></i>
                </a>
                <a href="<?= BASE_URL ?>/logout.php" class="p-2 text-red-600 hover:bg-white dark:hover:bg-gray-700 rounded-lg transition" aria-label="Logout">
                    <i class="ph ph-sign-out text-lg"></i>
                </a>
            </div>
        </div>

        <!-- Main Content Scrollable Area -->
        <main class="flex-1 p-4 md:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 dark:text-gray-200">
            <div class="max-w-7xl mx-auto">
                <!-- Flash Messages -->
                <?php display_flash(); ?>
