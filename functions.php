<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Authentication
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        redirect(BASE_URL . '/login.php');
    }
}

function get_user($pdo, $user_id) {
    if (!$user_id) return null;
    $stmt = $pdo->prepare("SELECT u.*, r.name as role_name 
                           FROM users u 
                           JOIN roles r ON u.role_id = r.id 
                           WHERE u.id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function has_permission($pdo, $user_id, $permission_name) {
    $stmt = $pdo->prepare("SELECT COUNT(*) 
                           FROM users u
                           JOIN role_permissions rp ON u.role_id = rp.role_id
                           JOIN permissions p ON rp.permission_id = p.id
                           WHERE u.id = ? AND p.name = ?");
    $stmt->execute([$user_id, $permission_name]);
    return $stmt->fetchColumn() > 0;
}

function require_permission($pdo, $permission_name) {
    require_login();
    if (!has_permission($pdo, $_SESSION['user_id'], $permission_name)) {
        set_flash('error', 'You do not have permission to access that page.');
        redirect(BASE_URL . '/admin/dashboard.php');
    }
}

// Data Sanitization
function clean($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = clean($value);
        }
        return $data;
    }
    return htmlspecialchars(stripslashes(trim((string)$data)));
}

// PRG (Post-Redirect-Get) Helpers
function redirect($url) {
    header("Location: $url");
    exit();
}

function set_flash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function get_flash($type) {
    if (isset($_SESSION['flash'][$type])) {
        $msg = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $msg;
    }
    return null;
}

function display_flash() {
    $types = [
        'success' => [
            'classes' => 'bg-white dark:bg-gray-800 border-green-200 dark:border-green-900/50 shadow-green-500/10',
            'icon' => 'ph-check-circle text-green-500 dark:text-green-400',
            'text' => 'text-gray-800 dark:text-gray-100'
        ],
        'error' => [
            'classes' => 'bg-white dark:bg-gray-800 border-red-200 dark:border-red-900/50 shadow-red-500/10',
            'icon' => 'ph-warning-circle text-red-500 dark:text-red-400',
            'text' => 'text-gray-800 dark:text-gray-100'
        ],
        'warning' => [
            'classes' => 'bg-white dark:bg-gray-800 border-amber-200 dark:border-amber-900/50 shadow-amber-500/10',
            'icon' => 'ph-warning text-amber-500 dark:text-amber-400',
            'text' => 'text-gray-800 dark:text-gray-100'
        ],
        'info' => [
            'classes' => 'bg-white dark:bg-gray-800 border-blue-200 dark:border-blue-900/50 shadow-blue-500/10',
            'icon' => 'ph-info text-blue-500 dark:text-blue-400',
            'text' => 'text-gray-800 dark:text-gray-100'
        ]
    ];
    
    // Check if there are any flashes to display so we can wrap them in a container
    $has_flash = false;
    foreach ($types as $type => $config) {
        if (isset($_SESSION['flash'][$type])) $has_flash = true;
    }

    if ($has_flash) {
        echo "<div class='fixed bottom-4 right-4 z-[9999] flex flex-col gap-3 pointer-events-none' style='min-width: 320px; max-width: 400px;'>";
        foreach ($types as $type => $config) {
            $msg = get_flash($type);
            if ($msg) {
                echo "<div x-data='{ show: true }' 
                           x-show='show' 
                           x-init='setTimeout(() => show = false, 5000)'
                           x-transition:enter='transition ease-out duration-300 transform'
                           x-transition:enter-start='opacity-0 translate-y-8 sm:translate-y-0 sm:translate-x-8'
                           x-transition:enter-end='opacity-100 translate-y-0 sm:translate-x-0'
                           x-transition:leave='transition ease-in duration-200 transform'
                           x-transition:leave-start='opacity-100 translate-x-0'
                           x-transition:leave-end='opacity-0 translate-x-full'
                           class='flex items-start gap-3 p-4 rounded-2xl shadow-xl border pointer-events-auto {$config['classes']} backdrop-blur-sm'
                           role='alert'>
                        <i class='ph {$config['icon']} text-2xl flex-shrink-0 mt-0.5'></i>
                        <div class='flex-1'>
                            <p class='text-sm font-bold {$config['text']} leading-tight'>" . htmlspecialchars($msg) . "</p>
                        </div>
                        <button @click='show = false' type='button' class='flex-shrink-0 p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-gray-700 transition-colors'>
                            <i class='ph ph-x w-4 h-4 flex items-center justify-center font-bold'></i>
                        </button>
                      </div>";
            }
        }
        echo "</div>";
    }
}

function get_unread_notifications_count($pdo, $user_id, $role_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications 
                           WHERE (target_user_id = ? OR target_role_id = ? OR (target_user_id IS NULL AND target_role_id IS NULL)) 
                           AND is_read = FALSE");
    $stmt->execute([$user_id, $role_id]);
    return $stmt->fetchColumn();
}

// Formatters
function format_time($time) {
    if (empty($time)) {
        return '—';
    }
    return date("h:i A", strtotime($time));
}

// Stats
function get_dashboard_stats($pdo) {
    $stats = [];
    $stats['total_users'] = $pdo->prepare("SELECT COUNT(*) FROM users");
    $stats['total_users']->execute();
    $stats['total_users'] = $stats['total_users']->fetchColumn();

    $stats['total_drivers'] = $pdo->prepare("SELECT COUNT(*) FROM drivers");
    $stats['total_drivers']->execute();
    $stats['total_drivers'] = $stats['total_drivers']->fetchColumn();

    $stats['total_routes'] = $pdo->prepare("SELECT COUNT(*) FROM routes");
    $stats['total_routes']->execute();
    $stats['total_routes'] = $stats['total_routes']->fetchColumn();

    $stats['active_buses'] = $pdo->prepare("SELECT COUNT(*) FROM buses WHERE status = 'active'");
    $stats['active_buses']->execute();
    $stats['active_buses'] = $stats['active_buses']->fetchColumn();

    $stats['total_trips'] = $pdo->prepare("SELECT COUNT(*) FROM trips");
    $stats['total_trips']->execute();
    $stats['total_trips'] = $stats['total_trips']->fetchColumn();

    $stats['live_trips'] = $pdo->prepare("SELECT COUNT(*) FROM trips WHERE status = 'in_progress'");
    $stats['live_trips']->execute();
    $stats['live_trips'] = $stats['live_trips']->fetchColumn();
    
    return $stats;
}

// Auto-generate trips for statically assigned routes daily 
function auto_generate_daily_trips($pdo, $driver_id, $date) {
    if (!$driver_id) return;
    
    // Find the driver's bus_id based on their stored bus_number
    $bus_stmt = $pdo->prepare("SELECT b.id FROM buses b JOIN drivers d ON b.bus_number = d.bus_number WHERE d.id = ?");
    $bus_stmt->execute([$driver_id]);
    $bus_id = $bus_stmt->fetchColumn() ?: null;
    
    // Find all static assigned routes for this driver
    $stmt_static = $pdo->prepare("SELECT id, departure_time FROM routes WHERE driver_id = ?");
    $stmt_static->execute([$driver_id]);
    $static_routes = $stmt_static->fetchAll(PDO::FETCH_OBJ);

    foreach ($static_routes as $sr) {
        $chk = $pdo->prepare("SELECT id FROM trips WHERE route_id = ? AND driver_id = ? AND trip_date = ?");
        $chk->execute([$sr->id, $driver_id, $date]);
        if (!$chk->fetch()) {
            $ins = $pdo->prepare("INSERT INTO trips (route_id, bus_id, driver_id, trip_date, start_time, status) VALUES (?, ?, ?, ?, ?, 'scheduled')");
            $ins->execute([$sr->id, $bus_id, $driver_id, $date, $sr->departure_time]);
        }
    }
}
