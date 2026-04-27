<?php
$page_title = 'Permissions Management';
$active_page = 'permissions';
require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_roles');

$permissions_stmt = $pdo->prepare("SELECT * FROM permissions ORDER BY id ASC");
$permissions_stmt->execute();
$permissions = $permissions_stmt->fetchAll();
?>

<style>
/* ==== PREMIUM CSS ==== */
body {
    background: linear-gradient(135deg, #eef2ff, #f8fafc);
    font-family: 'Inter', sans-serif;
}

body.dark {
    background: linear-gradient(135deg, #0f172a, #1e293b);
}

.card-premium {
    background: rgba(255, 255, 255, 0.75) !important;
    backdrop-filter: blur(14px);
    border-radius: 24px;
    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

body.dark .card-premium {
    background: rgba(30, 41, 59, 0.75) !important;
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.table-modern {
    width: 100%;
    border-collapse: separate;
}

.table-modern thead {
    background: linear-gradient(90deg, #f1f5f9, #f8fafc);
}

body.dark .table-modern thead {
    background: linear-gradient(90deg, #334155, #475569);
}

.table-modern th {
    font-size: 12px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #64748b;
    padding: 16px;
}

body.dark .table-modern th {
    color: #94a3b8;
}

.table-modern td {
    font-size: 14px;
    color: #334155;
    padding: 16px;
}

body.dark .table-modern td {
    color: #e2e8f0;
}

.table-modern tbody tr:hover {
    background: rgba(99, 102, 241, 0.06);
}

body.dark .table-modern tbody tr:hover {
    background: rgba(99, 102, 241, 0.15);
}

/* Button */
.btn-primary {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: white;
    border-radius: 12px;
    font-weight: 600;
    padding: 8px 16px;
    box-shadow: 0 6px 20px rgba(99,102,241,0.3);
    transition: 0.3s;
    text-decoration: none;
}

.btn-primary:hover {
    transform: translateY(-2px);
}

/* Code Badge */
.code-badge {
    background: #f1f5f9;
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 13px;
    font-family: monospace;
    color: #475569;
}

body.dark .code-badge {
    background: #334155;
    color: #e2e8f0;
}

/* Dark mode text */
body.dark h2 {
    color: #f1f5f9;
}

/* Action Buttons */
.action-btn {
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: 0.25s;
}

.action-edit {
    color: #3b82f6;
}

.action-edit:hover {
    background: rgba(59,130,246,0.1);
}

.action-delete {
    color: #ef4444;
}

.action-delete:hover {
    background: rgba(239,68,68,0.1);
}
</style>

<div class="flex flex-col gap-6">

    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">System Permissions</h2>

        <a href="permission_create.php" class="btn-primary">
            + Add Permission
        </a>
    </div>

    <!-- Table -->
    <div class="card-premium overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($permissions)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding:40px; color:#94a3b8;">
                                No permissions found
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($permissions as $p): ?>
                    <tr>
                        <td>#<?= $p->id ?></td>

                        <td>
                            <span class="code-badge">
                                <?= htmlspecialchars($p->name) ?>
                            </span>
                        </td>

                        <td>
                            <?= htmlspecialchars($p->description ?: 'No description') ?>
                        </td>

                        <td style="text-align:right;">
                            <a href="permission_edit.php?id=<?= $p->id ?>" class="action-btn action-edit">
                                Edit
                            </a>

                            <a href="permission_delete.php?id=<?= $p->id ?>" 
                               class="action-btn action-delete"
                               onclick="return confirm('Delete permission?')">
                               Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>