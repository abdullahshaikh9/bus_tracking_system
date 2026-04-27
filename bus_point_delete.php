<?php
$page_title = 'Delete Bus Point';
$active_page = 'points';
require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_routes');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: bus_points.php?msg=error&err=No point ID specified");
    exit();
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT bp.*, r.route_name FROM bus_points bp JOIN routes r ON bp.route_id = r.id WHERE bp.id = ?");
$stmt->execute([$id]);
$point = $stmt->fetch();

if (!$point) {
    header("Location: bus_points.php?msg=error&err=Bus point not found");
    exit();
}
?>

<div class="card" style="max-width: 500px; margin: 0 auto; text-align: center;">
    <div class="content-body" style="padding: 3rem 2rem;">
        <div style="width: 80px; height: 80px; background: rgba(239, 68, 68, 0.1); color: var(--danger); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem;">
            ⚠️
        </div>
        <h2 style="font-weight: 700; margin-bottom: 1rem; color: var(--dark);">Delete Bus Point?</h2>
        <p style="color: var(--gray); margin-bottom: 2rem; line-height: 1.6;">
            Are you sure you want to delete the bus point <strong>"<?php echo htmlspecialchars($point->point_name); ?>"</strong> from the route <strong>"<?php echo htmlspecialchars($point->route_name); ?>"</strong>? <br>
            This action cannot be undone.
        </p>
        
        <form action="process_action.php" method="POST" style="display: flex; gap: 1rem; justify-content: center;">
            <input type="hidden" name="action" value="delete_bus_point">
            <input type="hidden" name="id" value="<?php echo $point->id; ?>">
            <input type="hidden" name="redirect" value="bus_points.php">
            
            <a href="bus_points.php" class="btn" style="background: var(--light); color: var(--dark); border: 1px solid #E2E8F0; text-decoration: none; flex: 1;">Cancel</a>
            <button type="submit" class="btn btn-primary" style="background: var(--danger); flex: 1;">Yes, Delete Point</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
