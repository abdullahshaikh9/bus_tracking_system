<?php
$page_title = 'Emergency Alert';
$active_page = 'emergency';
require_once __DIR__ . '/../layouts/header.php';

if ($_SESSION['role_name'] != 'Driver') {
    die("Unauthorized Access.");
}

$stmt = $pdo->prepare("SELECT id FROM drivers WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$driver = $stmt->fetch();

$today = date('Y-m-d');
$stmt_trips = $pdo->prepare("SELECT t.id, r.route_name FROM trips t JOIN routes r ON t.route_id = r.id WHERE t.driver_id = ? AND t.trip_date = ? AND t.status != 'completed'");
$stmt_trips->execute([$driver->id, $today]);
$active_trips = $stmt_trips->fetchAll();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_alert'])) {
    try {
        $trip_id = !empty($_POST['trip_id']) ? $_POST['trip_id'] : null;
        $message = clean($_POST['message']);
        
        $stmt_insert = $pdo->prepare("INSERT INTO emergency_alerts (driver_id, trip_id, message) VALUES (?, ?, ?)");
        if ($stmt_insert->execute([$driver->id, $trip_id, $message])) {
            set_flash('error', 'EMERGENCY ALERT BROADCASTED. Help is being coordinated.'); // Using error flash for high visibility red alert
            redirect('emergency.php');
        }
    } catch (Exception $e) {
        set_flash('error', 'System Error: Could not broadcast alert. Call Dispatch immediately.');
    }
}
?>

<div class="max-w-2xl mx-auto flex flex-col gap-8">
    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border-4 border-red-500/20 shadow-2xl overflow-hidden relative">
        <!-- Visual Warning Pattern -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(239,68,68,0.1),transparent)] pointer-events-none"></div>
        <div class="absolute top-0 right-0 h-40 w-40 bg-red-500/10 blur-[80px] rounded-full -mr-20 -mt-20"></div>

        <div class="bg-red-600 px-8 py-8 text-white relative flex items-center justify-between overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-3xl font-black uppercase tracking-tighter flex items-center gap-3">
                    <i class="ph ph-warning-octagon animate-pulse"></i>
                    Emergency SOS
                </h2>
                <p class="text-red-100 font-bold text-sm mt-1 uppercase tracking-widest opacity-80">Dispatcher Immediate Response</p>
            </div>
            <i class="ph ph-megaphone text-8xl text-white/10 absolute -right-4 -bottom-4 rotate-12"></i>
        </div>

        <div class="p-8 md:p-12">
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-red-50 dark:bg-red-900/20 text-red-600 mb-6 border-2 border-red-100 dark:border-red-800/50 shadow-inner">
                    <i class="ph ph-siren-bold text-5xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 uppercase">Critical Protocol Required</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto">Use this portal only for breakdowns, accidents, or life-threatening situations. Abuse of the SOS system is strictly prohibited.</p>
            </div>
            
            <form method="POST" class="space-y-8">
                <div class="space-y-2">
                    <label class="text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">Active Incident Context</label>
                    <div class="relative">
                        <i class="ph ph-path absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <select name="trip_id" class="w-full pl-12 pr-4 py-4 bg-gray-50 dark:bg-gray-900 border-2 border-gray-100 dark:border-gray-700 rounded-2xl focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none appearance-none font-bold text-gray-900 dark:text-white">
                            <option value="">-- NO ACTIVE TRIP (STANDBY) --</option>
                            <?php foreach ($active_trips as $t): ?>
                                <option value="<?= $t->id; ?>"><?= htmlspecialchars($t->route_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="ph ph-caret-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">Incident Description <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <textarea name="message" required rows="5" placeholder="Engine fire, break failure, or medical emergency details..." class="w-full p-6 bg-gray-50 dark:bg-gray-900 border-2 border-gray-100 dark:border-gray-700 rounded-2xl focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none resize-none font-medium text-gray-900 dark:text-white placeholder:text-gray-400"></textarea>
                    </div>
                </div>
                
                <div class="pt-4">
                    <button type="submit" name="submit_alert" onclick="return confirm('MUSTER WIDE ALERT. This will notify all administrators and dispatchers. Confirm Emergency?');" 
                            class="w-full py-5 bg-red-600 hover:bg-red-700 text-white font-black text-xl rounded-2xl shadow-xl shadow-red-500/30 transition-all active:scale-95 flex items-center justify-center gap-3 group">
                        <i class="ph ph-broadcast text-2xl group-hover:scale-125 transition-transform"></i>
                        BROADCAST SOS
                    </button>
                    <p class="text-center text-[10px] text-gray-400 dark:text-gray-500 mt-4 uppercase font-bold tracking-[0.2em]">GPS coordinates will be attached automatically</p>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Emergency Contacts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4 group hover:border-red-500/30 transition-colors">
            <div class="w-12 h-12 rounded-2xl bg-gray-50 dark:bg-gray-900 flex items-center justify-center text-red-500 border border-gray-100 dark:border-gray-700">
                <i class="ph ph-phone-call text-2xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Main Dispatch</p>
                <h4 class="font-bold text-gray-900 dark:text-white">+92 22 1234567</h4>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4 group hover:border-red-500/30 transition-colors">
            <div class="w-12 h-12 rounded-2xl bg-gray-50 dark:bg-gray-900 flex items-center justify-center text-blue-500 border border-gray-100 dark:border-gray-700">
                <i class="ph ph-hospital text-2xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Campus Clinic</p>
                <h4 class="font-bold text-gray-900 dark:text-white">+92 22 7654321</h4>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
