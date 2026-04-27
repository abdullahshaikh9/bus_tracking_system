<?php
$page_title = 'Delay Report';
$active_page = 'delay_report';
require_once __DIR__ . '/../layouts/header.php';

if ($_SESSION['role_name'] != 'Driver') {
    die("Unauthorized Access.");
}

$stmt = $pdo->prepare("SELECT id FROM drivers WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$driver = $stmt->fetch();

$today = date('Y-m-d');
$stmt_trips = $pdo->prepare("
    SELECT t.id, r.route_name 
    FROM trips t 
    JOIN routes r ON t.route_id = r.id 
    WHERE t.driver_id = ? AND t.trip_date = ? AND t.status != 'completed'
");
$stmt_trips->execute([$driver->id, $today]);
$active_trips = $stmt_trips->fetchAll();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_delay'])) {
    $trip_id = !empty($_POST['trip_id']) ? $_POST['trip_id'] : null;
    $minutes = (int)$_POST['delay_minutes'];
    $reason = clean($_POST['reason']);
    
    $stmt_insert = $pdo->prepare("
        INSERT INTO delay_reports (driver_id, trip_id, delay_minutes, reason) 
        VALUES (?, ?, ?, ?)
    ");
    if ($stmt_insert->execute([$driver->id, $trip_id, $minutes, $reason])) {
        $msg = "Delay report submitted successfully. System updated.";
    }
}
?>

<div class="card" style="max-width:600px; margin:2rem auto; background: rgba(255,255,255,0.05); backdrop-blur:10px; border:1px solid rgba(255,255,255,0.1); border-radius:1rem; box-shadow:0 10px 30px rgba(0,0,0,0.3);">
    <div class="card-header" style="background: #f59e0b; color:#1f2937; padding:1rem 2rem; border-top-left-radius:1rem; border-top-right-radius:1rem;">
        <h3 style="margin:0; font-weight:700;">⏱️ Report a Delay</h3>
    </div>
    <div class="card-body" style="padding:2rem;">
        <?php if ($msg): ?>
            <div style="background: rgba(34,197,94,0.1); color:#22c55e; padding:1rem 1.5rem; border-radius:0.75rem; border:1px solid #22c55e; margin-bottom:2rem;">
                ✅ <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <p style="color:#cbd5e1; margin-bottom:1.5rem;">
            If you are stuck in traffic or experiencing an operational delay, report it here so students see accurate ETAs.
        </p>

        <form method="POST" class="space-y-4">
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label style="display:block; margin-bottom:0.5rem; color:#cbd5e1;">Affected Trip (Optional)</label>
                <select name="trip_id" style="width:100%; padding:0.75rem; border-radius:0.75rem; border:1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.1); color:white;">
                    <option value="">-- General Delay --</option>
                    <?php foreach ($active_trips as $t): ?>
                        <option value="<?php echo $t->id; ?>"><?php echo htmlspecialchars($t->route_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:1.5rem;">
                <label style="display:block; margin-bottom:0.5rem; color:#cbd5e1;">Delay Estimate (Minutes) <span style="color:#f87171">*</span></label>
                <input type="number" name="delay_minutes" required min="1" max="180" placeholder="e.g. 15"
                    style="width:100%; padding:0.75rem; border-radius:0.75rem; border:1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.1); color:white;">
            </div>

            <div class="form-group" style="margin-bottom:1.5rem;">
                <label style="display:block; margin-bottom:0.5rem; color:#cbd5e1;">Reason for Delay <span style="color:#f87171">*</span></label>
                <textarea name="reason" required rows="4" placeholder="Heavy traffic near Hyderabad bypass..."
                    style="width:100%; padding:0.75rem; border-radius:0.75rem; border:1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.1); color:white; resize:vertical;"></textarea>
            </div>

            <button type="submit" name="submit_delay"
                style="width:100%; padding:1rem; border:none; border-radius:1rem; background: #f59e0b; color:#1f2937; font-weight:700; font-size:1rem; cursor:pointer; transition:0.3s;">
                Submit Delay Report
            </button>
        </form>
    </div>
</div>