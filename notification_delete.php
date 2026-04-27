<?php
$page_title = 'Delete Notification';
$active_page = 'notifications';
require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_users');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: notifications.php?msg=error&err=No ID specified");
    exit();
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE id = ?");
$stmt->execute([$id]);
$notif = $stmt->fetch();

if (!$notif) {
    header("Location: notifications.php?msg=error&err=Alert not found");
    exit();
}
?>

<div class="card" style="max-width: 500px; margin: 0 auto; text-align: center;">
    <div class="content-body" style="padding: 3rem 2rem;">
        <div style="width: 80px; height: 80px; background: rgba(239, 68, 68, 0.1); color: var(--danger); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem;">
            ⚠️
        </div>
        <h2 style="font-weight: 700; margin-bottom: 1rem; color: var(--dark);">Delete Notification?</h2>
        <p style="color: var(--gray); margin-bottom: 2rem; line-height: 1.6;">
            Are you sure you want to permanently delete the notification <strong>"<?php echo htmlspecialchars($notif->title); ?>"</strong>? <br>
            It will be removed from all users' inboxes.
        </p>
        
        <form action="process_action.php" method="POST" style="display: flex; gap: 1rem; justify-content: center;">
            <input type="hidden" name="action" value="delete_notification">
            <input type="hidden" name="id" value="<?php echo $notif->id; ?>">
            <input type="hidden" name="redirect" value="notifications.php">
            
            <a href="notifications.php" class="btn" style="background: var(--light); color: var(--dark); border: 1px solid #E2E8F0; text-decoration: none; flex: 1;">Cancel</a>
            <button type="submit" class="btn btn-primary" style="background: var(--danger); flex: 1;">Yes, Delete It</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
