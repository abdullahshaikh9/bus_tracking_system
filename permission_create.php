<?php
$page_title = 'Create New Permission';
$active_page = 'permissions'; // Assuming permissions falls under roles or its own page
require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_roles');
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h3 style="font-weight: 700;">Create New Permission</h3>
        <a href="permissions.php" class="btn btn-secondary" style="width: auto; padding: 0.5rem 1rem; font-size: 0.875rem; background: var(--gray); color: white; text-decoration: none; border-radius: 0.5rem;">Back to Permissions</a>
    </div>
    <div class="content-body" style="padding: 1.5rem;">
        <form action="process_action.php" method="POST">
            <input type="hidden" name="action" value="add_permission">
            <input type="hidden" name="redirect" value="permissions.php">
            
            <div class="form-group">
                <label>Permission Name (identifier, e.g., 'manage_users')</label>
                <input type="text" name="name" class="form-control" placeholder="e.g., manage_users" required>
            </div>
            
            <div class="form-group">
                <label>Description (Optional)</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Brief description of the permission..."></textarea>
            </div>
            
            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">Create Permission</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
