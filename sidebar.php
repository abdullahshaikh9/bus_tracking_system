<!-- Sidebar Backdrop for Mobile -->
<div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm lg:hidden" x-cloak style="position: fixed; top: env(safe-area-inset-top);"></div>

<!-- Sidebar Area -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-72 flex flex-col bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 transition-transform duration-300 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 safe-area-pb">
    
    <!-- Logo -->
    <div class="flex items-center justify-between h-20 px-6 border-b border-gray-200 dark:border-gray-800 shrink-0">
        <a href="#" class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/30 text-white group-hover:scale-105 transition-transform duration-300">
                <i class="ph ph-bus text-2xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-indigo-600 dark:from-primary-400 dark:to-indigo-400">BusTrack</h2>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold">MUET Campus</p>
            </div>
        </a>
        <button @click="sidebarOpen = false" class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none">
            <i class="ph ph-x text-2xl"></i>
        </button>
    </div>

    <!-- Navigation Scrollable Area -->
    <div class="flex-1 overflow-y-auto py-4 px-4 space-y-1 scrollbar-hide">

<?php
// Helper function for nav item
if(!function_exists('render_nav_item')) {
    function render_nav_item($title, $icon, $url, $active_page, $page_id) {
        $is_active = ($active_page == $page_id);
        $active_classes = $is_active
            ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 border-primary-500 dark:border-primary-400'
            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-200 border-transparent';
        $icon_active = $is_active ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400';
        $weight = $is_active ? 'fill' : 'regular';

        return "
        <a href='" . BASE_URL . "{$url}' class='group flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 border border-l-4 {$active_classes} min-h-[48px]'>
            <i class='ph ph-{$icon} text-xl {$icon_active}' style='font-family: \"Phosphor Icons {$weight}\"' ></i>
            <span class='font-medium text-sm'>{$title}</span>
        </a>";
    }

    function render_nav_label($title) {
        return "<div class='pt-5 pb-2 px-3 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500'>{$title}</div>";
    }
}
?>

<?php if ($_SESSION['role_name'] == 'Super Admin' || $_SESSION['role_name'] == 'Admin'): ?>
    <?php 
        echo render_nav_label('Main Menu');
        echo render_nav_item('Dashboard', 'squares-four', '/admin/dashboard.php', $active_page ?? '', 'dashboard');
    ?>

    <?php if (has_permission($pdo, $_SESSION['user_id'], 'manage_users')): ?>
        <?php 
            echo render_nav_label('User Management');
            echo render_nav_item('Users', 'users', '/admin/users.php', $active_page ?? '', 'users');
            echo render_nav_item('Drivers', 'steering-wheel', '/admin/drivers.php', $active_page ?? '', 'drivers');
        ?>
    <?php endif; ?>

    <?php if (has_permission($pdo, $_SESSION['user_id'], 'manage_roles')): ?>
        <?php 
            echo render_nav_label('RBAC Control');
            echo render_nav_item('Roles', 'shield-check', '/admin/roles.php', $active_page ?? '', 'roles');
            echo render_nav_item('Permissions', 'key', '/admin/permissions.php', $active_page ?? '', 'permissions');
            // echo render_nav_item('Assignments', 'link', '/admin/assign_permissions.php', $active_page ?? '', 'assign');
        ?>
    <?php endif; ?>

    <?php if (has_permission($pdo, $_SESSION['user_id'], 'manage_routes')): ?>
        <?php 
            echo render_nav_label('Operational');
            echo render_nav_item('Bus Fleet', 'bus', '/admin/buses.php', $active_page ?? '', 'buses');
            echo render_nav_item('Route Management', 'map-trifold', '/admin/routes.php', $active_page ?? '', 'routes');
            echo render_nav_item('Bus Points', 'map-pin', '/admin/bus_points.php', $active_page ?? '', 'points');
            echo render_nav_item('Trips Log', 'calendar-check', '/admin/trips.php', $active_page ?? '', 'trips');
        ?>
    <?php endif; ?>

        <?php 
            echo render_nav_label('System');
            echo render_nav_item('Messages', 'chats', '/admin/messages.php', $active_page ?? '', 'messages');
            echo render_nav_item('Alerts & Notices', 'bell-ringing', '/admin/notifications.php', $active_page ?? '', 'notifications');
            echo render_nav_item('System Reports', 'chart-bar', '/admin/reports.php', $active_page ?? '', 'reports');
        ?>

<?php elseif ($_SESSION['role_name'] == 'Driver'): ?>
        <?php 
            echo render_nav_label('Driver Portal');
            echo render_nav_item('Dashboard', 'squares-four', '/driver/dashboard.php', $active_page ?? '', 'dashboard');
            echo render_nav_item('My Route', 'map-trifold', '/driver/my_route.php', $active_page ?? '', 'my_route');
            echo render_nav_item('My Bus', 'bus', '/driver/my_bus.php', $active_page ?? '', 'my_bus');
            
            echo render_nav_label('Operations');
            echo render_nav_item('Start Trip', 'play-circle', '/driver/start_trip.php', $active_page ?? '', 'start_trip');
            echo render_nav_item('Trip History', 'history', '/driver/trip_history.php', $active_page ?? '', 'trip_history');
            echo render_nav_item('Delay Report', 'clock-warning', '/driver/delay_report.php', $active_page ?? '', 'delay_report');
            
            echo render_nav_label('Account');
            echo render_nav_item('Messages', 'chats', '/driver/messages.php', $active_page ?? '', 'messages');
            echo render_nav_item('Notifications', 'bell', '/driver/notifications.php', $active_page ?? '', 'notifications');
            echo render_nav_item('Emergency Alert', 'warning-diamond', '/driver/emergency.php', $active_page ?? '', 'emergency');
        ?>

<?php else: ?>
        <?php 
            echo render_nav_label('Passenger Portal');
            echo render_nav_item('Dashboard', 'squares-four', '/user/dashboard.php', $active_page ?? '', 'dashboard');
            echo render_nav_item('Live Tracking', 'broadcast', '/user/live_tracking.php', $active_page ?? '', 'live_tracking');
            echo render_nav_item('Trip Planner', 'compass', '/user/trip_planner.php', $active_page ?? '', 'trip_planner');
            
            echo render_nav_label('Routes & Info');
            echo render_nav_item('All Routes', 'map-trifold', '/user/routes.php', $active_page ?? '', 'routes');
            echo render_nav_item('Bus Stops', 'map-pin', '/user/bus_stops.php', $active_page ?? '', 'bus_stops');
            echo render_nav_item('Saved Routes', 'star', '/user/favorite_routes.php', $active_page ?? '', 'favorite_routes');
            
            echo render_nav_label('Account');
            echo render_nav_item('Messages', 'chats', '/user/messages.php', $active_page ?? '', 'messages');
            echo render_nav_item('Notifications', 'bell', '/user/notifications.php', $active_page ?? '', 'notifications');
            echo render_nav_item('Feedback', 'chat-circle-text', '/user/feedback.php', $active_page ?? '', 'feedback');
        ?>
<?php endif; ?>

    </div>

    <!-- User Mini Profile at bottom of sidebar -->
    <div class="mt-auto border-t border-gray-200 dark:border-gray-800 p-4 shrink-0">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 min-w-[40px] rounded-full bg-gradient-to-tr from-gray-700 to-gray-900 dark:from-gray-600 dark:to-gray-800 flex items-center justify-center text-white font-bold text-sm shadow-md">
                <?php echo strtoupper(substr($user->full_name ?? 'U', 0, 1)); ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate"><?php echo htmlspecialchars($user->full_name ?? ''); ?></p>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate"><?php echo htmlspecialchars($user->email ?? ''); ?></p>
            </div>
            <a href="<?= BASE_URL ?>/logout.php" class="p-2 text-gray-400 hover:text-red-500 transition-colors" title="Logout">
                <i class="ph ph-sign-out text-xl"></i>
            </a>
        </div>
    </div>
</aside>
