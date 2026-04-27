<?php
$page_title = 'Send New Notification';
$active_page = 'notifications';
require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_users');
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h3 style="font-weight: 700;">Broadcast New Alert</h3>
        <a href="notifications.php" class="btn btn-secondary" style="width: auto; padding: 0.5rem 1rem; font-size: 0.875rem; background: var(--gray); color: white; text-decoration: none; border-radius: 0.5rem;">Back to List</a>
    </div>
    <div class="content-body" style="padding: 1.5rem;">
        <form action="process_action.php" method="POST">
            <input type="hidden" name="action" value="add_notification">
            <input type="hidden" name="redirect" value="notifications.php">
            
            <div class="form-group">
                <label>Target Audience</label>
                <select name="user_type" class="form-control" required>
                    <option value="all">All Users</option>
                    <option value="driver">Drivers Only</option>
                    <option value="passenger">Passengers Only</option>
                </select>
                <small style="color:var(--gray); display:block; margin-top:5px;">Select who should receive this alert.</small>
            </div>
            
            <div class="form-group">
                <label>Alert Title</label>
                <input type="text" name="title" class="form-control" placeholder="e.g., Service Disruption on Route A" required>
            </div>

            <div class="form-group">
                <label>Message Content</label>
                <textarea name="message" class="form-control" rows="5" placeholder="Type the notification details here..." required></textarea>
            </div>
            
            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%; display: flex; justify-content: center; gap: 10px; align-items: center;">
                    <span>📢</span> Send Broadcast
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
