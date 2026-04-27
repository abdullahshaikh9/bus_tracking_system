<?php
$page_title = 'Edit Permission';
$active_page = 'permissions';

require_once __DIR__ . '/../layouts/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: permissions.php?msg=error&err=No permission ID specified");
    exit();
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM permissions WHERE id = ?");
$stmt->execute([$id]);
$permission = $stmt->fetch();

if (!$permission) {
    header("Location: permissions.php?msg=error&err=Permission not found");
    exit();
}

require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_roles');
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h3 style="font-weight: 700;">Edit Permission: <?php echo htmlspecialchars($permission->name); ?></h3>
        <a href="permissions.php" class="btn btn-secondary" style="width: auto; padding: 0.5rem 1rem; font-size: 0.875rem; background: var(--gray); color: white; text-decoration: none; border-radius: 0.5rem;">Back to Permissions</a>
    </div>
    <div class="content-body" style="padding: 1.5rem;">
        <form action="process_action.php" method="POST">
            <input type="hidden" name="action" value="edit_permission">
            <input type="hidden" name="id" value="<?php echo $permission->id; ?>">
            <input type="hidden" name="redirect" value="permissions.php">
            
            <div class="form-group">
                <label>Permission Name (identifier, e.g., 'manage_users')</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($permission->name); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Description (Optional)</label>
                <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($permission->description); ?></textarea>
            </div>
            
            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
