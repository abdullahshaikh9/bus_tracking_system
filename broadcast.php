<?php
$page_title = 'Live Broadcasting';
$active_page = 'dashboard';
require_once __DIR__ . '/../layouts/functions.php';

require_login();
$user = get_user($pdo, $_SESSION['user_id']);

// Verify driver role
if ($_SESSION['role_name'] != 'Driver' && !has_permission($pdo, $_SESSION['user_id'], 'driver_access')) {
    set_flash('error', 'Unauthorized Access.');
    redirect(BASE_URL . '/login.php');
}

// Fetch bus ID
$stmt = $pdo->prepare("SELECT d.id, b.id as bus_id, b.bus_number FROM drivers d LEFT JOIN buses b ON d.bus_number = b.bus_number WHERE d.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$driver = $stmt->fetch();

if (!$driver || !$driver->bus_id) {
    set_flash('error', 'You are not assigned to a bus capable of broadcasting.');
    redirect(BASE_URL . '/driver/dashboard.php');
}

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-3xl mx-auto py-8">
    
    <!-- Critical Warning Box -->
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-4 mb-6 flex gap-4 items-start shadow-sm">
        <div class="bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-500 rounded-full p-2 flex-shrink-0 mt-0.5">
            <i class="ph ph-warning-circle text-xl"></i>
        </div>
        <div>
            <h4 class="text-amber-800 dark:text-amber-400 font-bold text-sm mb-1">APP MUST REMAIN OPEN</h4>
            <p class="text-amber-700/80 dark:text-amber-500/80 text-xs leading-relaxed">
                Mobile browsers strictly disable GPS processing when a tab is minimized or pushed to the background to save battery. 
                <strong>You must keep this specific page open and actively visible on your screen while driving.</strong> The screen will be locked awake automatically.
            </p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden text-center p-8">
        
        <div class="w-24 h-24 mx-auto bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-full flex items-center justify-center mb-6">
            <i class="ph ph-broadcast text-5xl"></i>
        </div>
        
        <h2 class="text-3xl font-black text-gray-900 dark:text-white mb-2">Live Broadcast</h2>
        <p class="text-gray-500 font-medium mb-8">You are broadcasting location for <span class="text-primary-600 dark:text-primary-400 font-bold">Bus <?= htmlspecialchars($driver->bus_number) ?></span></p>

        <!-- Status Indicator -->
        <div id="statusIndicator" class="inline-flex items-center gap-2 px-6 py-3 rounded-full font-bold text-sm bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 mb-10 transition-colors duration-300">
            <span class="w-3 h-3 rounded-full bg-gray-400"></span> Offline
        </div>

        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <button id="startBtn" onclick="startTracking()" class="px-8 py-4 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition flex items-center justify-center gap-2 text-lg">
                <i class="ph ph-play-circle text-2xl"></i> Start Broadcasting
            </button>
            <button id="stopBtn" onclick="stopTracking()" class="px-8 py-4 bg-red-500 hover:bg-red-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition hidden items-center justify-center gap-2 text-lg">
                <i class="ph ph-stop-circle text-2xl"></i> Stop Broadcasting
            </button>
        </div>
        
        <div class="mt-12 p-6 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 text-left">
            <h4 class="font-bold text-gray-900 dark:text-white text-sm mb-3 uppercase tracking-wider">Telematics Log</h4>
            <div id="consoleLog" class="font-mono text-xs text-gray-600 dark:text-gray-400 h-32 overflow-y-auto space-y-1 bg-white dark:bg-black p-4 rounded-xl shadow-inner border border-gray-100 dark:border-gray-800 flex flex-col-reverse">
                <div>System ready. Waiting for initialization...</div>
            </div>
        </div>

    </div>
</div>

<script>
    const busId = <?= (int)$driver->bus_id; ?>;
    let watchId = null;
    let lastLogTime = 0;

    const startBtn = document.getElementById('startBtn');
    const stopBtn = document.getElementById('stopBtn');
    const statusInd = document.getElementById('statusIndicator');
    const logs = document.getElementById('consoleLog');

    let wakeLock = null;
    let isBroadcasting = false;

    // Advanced WakeLock wrapper
    const requestWakeLock = async () => {
        try {
            if ('wakeLock' in navigator) {
                wakeLock = await navigator.wakeLock.request('screen');
                wakeLock.addEventListener('release', () => {
                    logMessage('Screen lock released by system.', true);
                });
                logMessage('Screen is locked awake.');
            }
        } catch (err) {
            logMessage(`WakeLock error: ${err.message}`, true);
        }
    };

    // Re-acquire WakeLock if the driver temporarily backgrounds and foregrounds the app
    document.addEventListener('visibilitychange', async () => {
        if (wakeLock !== null && document.visibilityState === 'visible' && isBroadcasting) {
            await requestWakeLock();
            logMessage('App brought to foreground. GPS resumed.');
        } else if (document.visibilityState === 'hidden' && isBroadcasting) {
            logMessage('WARNING: App sent to background! GPS may pause!', true);
        }
    });

    function logMessage(msg, isError = false) {
        const div = document.createElement('div');
        div.className = isError ? 'text-red-500 font-bold' : 'text-emerald-500';
        const d = new Date();
        div.textContent = `[${d.toLocaleTimeString()}] ${msg}`;
        logs.prepend(div);
    }

    function startTracking() {
        if (!navigator.geolocation) {
            alert("Geolocation is not supported by your browser!");
            return;
        }

        isBroadcasting = true;
        requestWakeLock(); // Lock screen on

        logMessage("Starting GPS engine...");
        
        watchId = navigator.geolocation.watchPosition(
            successCallback, 
            errorCallback, 
            { enableHighAccuracy: true, maximumAge: 0, timeout: 5000 }
        );

        startBtn.classList.add('hidden');
        startBtn.classList.remove('flex');
        stopBtn.classList.remove('hidden');
        stopBtn.classList.add('flex');
        
        statusInd.className = "inline-flex items-center gap-2 px-6 py-3 rounded-full font-bold text-sm bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 mb-10 transition-colors duration-300 shadow-[0_0_15px_rgba(16,185,129,0.3)]";
        statusInd.innerHTML = `<span class="w-3 h-3 rounded-full bg-emerald-500 animate-ping absolute"></span><span class="w-3 h-3 rounded-full bg-emerald-500 relative"></span> LIVE`;
    }

    function stopTracking() {
        isBroadcasting = false;
        if (watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
        }
        
        if (wakeLock !== null) {
            wakeLock.release().then(() => wakeLock = null);
        }
        
        logMessage("GPS broadcasting paused.");
        
        startBtn.classList.remove('hidden');
        startBtn.classList.add('flex');
        stopBtn.classList.add('hidden');
        stopBtn.classList.remove('flex');
        
        statusInd.className = "inline-flex items-center gap-2 px-6 py-3 rounded-full font-bold text-sm bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 mb-10 transition-colors duration-300";
        statusInd.innerHTML = `<span class="w-3 h-3 rounded-full bg-gray-400"></span> Offline`;
    }

    function successCallback(position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        
        const now = Date.now();
        // Limit explicit fetch POST to once every 5 seconds to match requirements, but watchPosition might fire faster
        if (now - lastLogTime < 5000) return;
        lastLogTime = now;

        logMessage(`Coords: ${lat.toFixed(5)}, ${lng.toFixed(5)}`);

        // Send to server
        fetch('<?= BASE_URL ?>/api/update_location.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ bus_id: busId, lat: lat, lng: lng })
        })
        .then(response => response.json())
        .then(data => {
            if(!data.success) {
                logMessage("Server error syncing location", true);
            } else {
                // Occasional success confirmation in logs (not every time to avoid clutter)
                if (Math.random() > 0.9) logMessage("Location synced to satellite.");
            }
        })
        .catch(error => {
            logMessage("Network disconnected.", true);
        });
    }

    function errorCallback(error) {
        logMessage("GPS Error: " + error.message, true);
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
