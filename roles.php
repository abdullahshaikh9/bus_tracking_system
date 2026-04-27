<?php
$page_title = 'Roles Management';
$active_page = 'roles';
require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_roles');

$roles_stmt = $pdo->prepare("SELECT * FROM roles ORDER BY id ASC");
$roles_stmt->execute();
$roles = $roles_stmt->fetchAll();
?>

<style>
/* === Unified Admin Panel Theme === */
body {
    font-family: 'Inter', sans-serif;
}

body.dark {
    background: linear-gradient(135deg, #0f172a, #1e293b);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.page-header h2 {
    margin: 0;
    font-weight: 700;
    color: #111827;
}

body.dark .page-header h2 {
    color: #f1f5f9;
}

.page-header small {
    color: #6b7280;
    font-size: 13px;
}

body.dark .page-header small {
    color: #94a3b8;
}

/* Primary button matches Trips/Routes/Buses */
.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem; /* 10px 16px */
    background-color: #4f46e5; /* primary-600 */
    color: #ffffff;
    font-size: 0.875rem;
    font-weight: 600;
    border-radius: 1rem; /* same rounded-xl */
    box-shadow: 0 4px 6px rgba(79,70,229,0.2);
    transition: all 0.2s;
    text-decoration: none;
}

.btn-primary:hover {
    background-color: #4338ca; /* primary-700 */
    box-shadow: 0 6px 12px rgba(79,70,229,0.25);
}

/* Card table */
.card {
    background-color: #ffffff;
    border-radius: 1.5rem;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    overflow: hidden;
}

body.dark .card {
    background-color: #1e293b;
    border: 1px solid #334155;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.table-responsive {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background-color: #f9fafb;
}

body.dark thead {
    background-color: #334155;
}

thead th {
    padding: 1rem 1.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
}

body.dark thead th {
    color: #94a3b8;
}

tbody td {
    padding: 0.75rem 1.5rem;
    font-size: 0.875rem;
    color: #111827;
}

body.dark tbody td {
    color: #e2e8f0;
}

tbody tr:hover {
    background-color: #f3f4f6;
}

body.dark tbody tr:hover {
    background-color: #334155;
}

.role-icon {
    height: 2.5rem;
    width: 2.5rem;
    border-radius: 0.75rem;
    background-color: #e0e7ff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4f46e5;
    font-size: 1rem;
}

body.dark .role-icon {
    background-color: #312e81;
    color: #818cf8;
}

/* Action buttons same as Trips/Routes/Buses table */
.actions a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    border-radius: 0.75rem;
    font-size: 1rem;
    transition: all 0.2s;
}

.actions .edit {
    color: #4f46e5;
}

.actions .edit:hover {
    background-color: #eef2ff; /* primary-50 */
}

.actions .delete {
    color: #ef4444;
}

.actions .delete:hover {
    background-color: #fee2e2; /* red-50 */
}

.actions .permissions {
    color: #4f46e5;
    background-color: #eef2ff; /* primary-50 */
}

.actions .permissions:hover {
    background-color: #e0e7ff; /* primary-100 */
}

body.dark .actions .edit {
    color: #818cf8;
}

body.dark .actions .edit:hover {
    background-color: #312e81;
}

body.dark .actions .delete {
    color: #f87171;
}

body.dark .actions .delete:hover {
    background-color: #7f1d1d;
}

body.dark .actions .permissions {
    color: #818cf8;
    background-color: #312e81;
}

body.dark .actions .permissions:hover {
    background-color: #3730a3;
}

/* Dark mode text adjustments */
body.dark .font-semibold {
    color: #f1f5f9;
}

body.dark .text-gray-400 {
    color: #64748b !important;
}
</style>

<div class="flex flex-col gap-6">

    <!-- Header -->
    <div class="page-header">
        <div>
            <h2>System Roles</h2>
            <small>Manage roles & permissions</small>
        </div>
        <a href="role_create.php" class="btn-primary">+ Create Role</a>
    </div>

    <!-- Table -->
    <div class="card table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Role</th>
                    <th>Description</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($roles)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-10 text-gray-400">
                            No roles found
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($roles as $r): ?>
                    <tr>
                        <td>#<?= str_pad($r->id, 2, '0', STR_PAD_LEFT) ?></td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="role-icon">
                                    <i class="ph ph-shield-check"></i>
                                </div>
                                <div class="font-semibold"><?= htmlspecialchars($r->name) ?></div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($r->description ?: 'No description') ?></td>
                        <td class="text-right">
                            <div class="flex justify-end actions">
                                <a href="assign_permissions.php?id=<?= $r->id ?>" class="permissions">Permissions</a>
                                <a href="role_edit.php?id=<?= $r->id ?>" class="edit">✏️</a>
                                <?php if ($r->id != 1): ?>
                                    <a href="role_delete.php?id=<?= $r->id ?>" class="delete" onclick="return confirm('Delete role?')">🗑️</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>