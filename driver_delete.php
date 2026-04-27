<?php
$page_title = 'Remove Driver';
$active_page = 'drivers';

// 🔥 FIRST: logic (NO HTML BEFORE THIS)
require_once __DIR__ . '/../layouts/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: drivers.php?msg=error&err=No driver ID specified");
    exit();
}

require_login();
// require_permission will check permission
require_permission($pdo, 'manage_users');

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT u.full_name, u.email, d.* FROM drivers d JOIN users u ON d.user_id = u.id WHERE d.user_id = ?");
$stmt->execute([$id]);
$driver = $stmt->fetch();

if (!$driver) {
    header("Location: drivers.php?msg=error&err=Driver not found");
    exit();
}

require_once __DIR__ . '/../layouts/header.php';
?>
<div class="card" style="max-width: 500px; margin: 0 auto; text-align: center;">
    <div class="content-body" style="padding: 3rem 2rem;">
        <div style="width: 80px; height: 80px; background: rgba(239, 68, 68, 0.1); color: var(--danger); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem;">
            ⚠️
        </div>
        <h2 style="font-weight: 700; margin-bottom: 1rem; color: var(--dark);">Remove Driver?</h2>
        <p style="color: var(--gray); margin-bottom: 2rem; line-height: 1.6;">
            Are you sure you want to remove the driver <strong>"<?php echo htmlspecialchars($driver->full_name); ?>"</strong>? <br>
            This will also delete their user account and system access. This action cannot be undone.
        </p>
        
        <form action="process_action.php" method="POST" style="display: flex; gap: 1rem; justify-content: center;">
            <input type="hidden" name="action" value="delete_user"> <!-- Drivers are removed via their user_id with CASCADE -->
            <input type="hidden" name="id" value="<?php echo $driver->user_id; ?>">
            <input type="hidden" name="redirect" value="drivers.php">
            
            <a href="drivers.php" class="btn" style="background: var(--light); color: var(--dark); border: 1px solid #E2E8F0; text-decoration: none; flex: 1;">Cancel</a>
            <button type="submit" class="btn btn-primary" style="background: var(--danger); flex: 1;">Yes, Remove Driver</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
