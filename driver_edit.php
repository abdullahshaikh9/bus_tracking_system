<?php
$page_title = 'Edit Driver Details';
$active_page = 'drivers';

require_once __DIR__ . '/../layouts/functions.php';

// Check ID before any output
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: drivers.php?msg=error&err=No driver ID specified");
    exit();
}

$user_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT u.full_name, u.email, u.phone, u.profile_photo, d.* FROM drivers d JOIN users u ON d.user_id = u.id WHERE d.user_id = ?");
$stmt->execute([$user_id]);
$driver = $stmt->fetch();

if (!$driver) {
    header("Location: drivers.php?msg=error&err=Driver not found");
    exit();
}

require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_users');

// Fetch Assigned Trips for Schedule
$trips_stmt = $pdo->prepare("SELECT t.*, r.route_name, r.start_point, r.end_point, b.bus_number
                             FROM trips t
                             JOIN routes r ON t.route_id = r.id
                             JOIN buses b ON t.bus_id = b.id
                             WHERE t.driver_id = ? AND t.status IN ('scheduled', 'in_progress')
                             ORDER BY t.trip_date ASC, t.start_time ASC");
$trips_stmt->execute([$driver->id]);
$assigned_trips = $trips_stmt->fetchAll();
?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; max-width: 1000px; margin: 0 auto;">
    <div class="card">
    <div class="card-header">
        <h3 style="font-weight: 700;">Edit Driver: <?php echo htmlspecialchars($driver->full_name); ?></h3>
        <a href="drivers.php" class="btn btn-secondary" style="width: auto; padding: 0.5rem 1rem; font-size: 0.875rem; background: var(--gray); color: white; text-decoration: none; border-radius: 0.5rem;">Back to Drivers</a>
    </div>
    <div class="content-body" style="padding: 1.5rem;">
        <form action="process_action.php" method="POST">
            <input type="hidden" name="action" value="edit_driver">
            <input type="hidden" name="user_id" value="<?php echo $driver->user_id; ?>">
            <input type="hidden" name="redirect" value="drivers.php">
            
            <div class="form-group">
                <label>License Number</label>
                <input type="text" name="license_number" class="form-control" value="<?php echo htmlspecialchars($driver->license_number); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($driver->phone); ?>">
            </div>
            
            <div class="form-group">
                <label>Bus Number</label>
                <select name="bus_number" class="form-control">
                    <option value="">-- No Bus Assigned --</option>
                    $buses_stmt = $pdo->prepare("SELECT bus_number, plate_number FROM buses WHERE status = 'active' ORDER BY bus_number ASC");
                    $buses_stmt->execute();
                    $buses = $buses_stmt->fetchAll();
                    foreach ($buses as $b) {
                        $selected = ($driver->bus_number === $b->bus_number) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($b->bus_number) . '" ' . $selected . '>';
                        echo htmlspecialchars($b->bus_number . ' (' . $b->plate_number . ')');
                        echo '</option>';
                    }
                </select>
            </div>
            
            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
    </div> <!-- End Card 1 -->

    <div class="card">
        <div class="card-header">
            <h3 style="font-weight: 700;">Assigned Schedule</h3>
            <a href="trip_create.php" class="btn btn-secondary" style="width: auto; padding: 0.5rem 1rem; font-size: 0.75rem; background: var(--gray); color: white; text-decoration: none; border-radius: 0.5rem;">+ Assign Trip</a>
        </div>
        <div class="content-body" style="padding: 1.5rem;">
            <?php if (empty($assigned_trips)): ?>
                <div style="text-align: center; color: var(--gray); padding: 2rem; background: #F8FAFC; border-radius: 8px;">
                    No upcoming trips assigned.
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($assigned_trips as $t): ?>
                        <div style="border: 1px solid #E2E8F0; padding: 1rem; border-radius: 8px; background: #F8FAFC;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <strong><?php echo date("M d, Y", strtotime($t->trip_date)); ?> @ <?php echo format_time($t->start_time); ?></strong>
                                <?php if ($t->status == 'in_progress'): ?>
                                    <span class="badge badge-success" style="background:#FEF08A; color:#854D0E; font-size: 0.65rem;">Live</span>
                                <?php else: ?>
                                    <span class="badge badge-gray" style="font-size: 0.65rem;">Scheduled</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 0.875rem; color: var(--dark); font-weight: 600; margin-bottom: 0.25rem;">
                                <?php echo htmlspecialchars($t->route_name); ?>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--gray);">
                                Bus: <?php echo htmlspecialchars($t->bus_number); ?> | Route: <?php echo htmlspecialchars($t->start_point); ?> &rarr; <?php echo htmlspecialchars($t->end_point); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div> <!-- End Card 2 -->
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
