<?php
$page_title = 'Role Permission Assignment';
$active_page = 'roles'; // Keeping it under the roles navbar group
require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_roles');

// Save Assignments (with PRG pattern)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_permissions'])) {
    try {
        $pdo->beginTransaction();
        
        // Remove all but Super Admin permissions (Role ID 1) if we want to protect them manually
        // Or just let the logic handle it. The UI disables Role 1 anyway.
        $del_stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id != ?");
        $del_stmt->execute([1]);
        
        if (isset($_POST['perm'])) {
            $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($_POST['perm'] as $role_id => $perms) {
                $role_id = (int)$role_id;
                if ($role_id === 1) continue; // Skip super admin, already handled/protected
                
                foreach ($perms as $perm_id => $val) {
                    $stmt->execute([$role_id, (int)$perm_id]);
                }
            }
        }
        
        $pdo->commit();
        set_flash('success', 'Security permissions updated successfully!');
        redirect('roles.php'); // PRG: Redirect after POST
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash('error', 'Critical Error: ' . $e->getMessage());
    }
}

$roles_stmt = $pdo->prepare("SELECT * FROM roles ORDER BY id ASC");
$roles_stmt->execute();
$roles = $roles_stmt->fetchAll();

$perms_stmt = $pdo->prepare("SELECT * FROM permissions ORDER BY name ASC");
$perms_stmt->execute();
$permissions = $perms_stmt->fetchAll();

// Get current mapping
$mapping = [];
$map_stmt = $pdo->prepare("SELECT * FROM role_permissions");
$map_stmt->execute();
$res = $map_stmt->fetchAll();
foreach ($res as $r) {
    $mapping[$r->role_id][$r->permission_id] = true;
}
?>

<div class="flex flex-col gap-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Permission Matrix</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure granular access controls for each system role.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="roles.php" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-200 transition-all">
                <i class="ph ph-arrow-left text-lg"></i>
                Cancel
            </a>
            <button type="submit" form="permissionForm" name="save_permissions" class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-primary-500/20 transition-all duration-200">
                <i class="ph ph-check-circle text-lg"></i>
                Save Authorization Map
            </button>
        </div>
    </div>

    <!-- Permission Grid -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xl overflow-hidden">
        <form id="permissionForm" action="" method="POST">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-900/10 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-8 py-5 text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-800 sticky left-0 z-10 w-72">Permission Description</th>
                            <?php foreach ($roles as $r): ?>
                                <th class="px-6 py-5 text-center min-w-[120px]">
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="h-8 w-8 rounded-lg bg-gray-50 dark:bg-gray-900 flex items-center justify-center text-xs font-bold text-gray-400 border border-gray-100 dark:border-gray-700 uppercase">
                                            <?= strtoupper(substr($r->name, 0, 1)) ?>
                                        </div>
                                        <span class="text-[12px] font-bold text-gray-700 dark:text-gray-300 uppercase tracking-tight"><?= htmlspecialchars($r->name) ?></span>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        <?php foreach ($permissions as $p): ?>
                        <tr class="group hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-8 py-5 sticky left-0 z-10 bg-white dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900 transition-colors">
                                <div class="font-bold text-gray-900 dark:text-white text-sm"><?= ucwords(str_replace('_', ' ', $p->name)) ?></div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5"><?= htmlspecialchars($p->description) ?></div>
                            </td>
                            <?php foreach ($roles as $r): ?>
                            <td class="px-6 py-5">
                                <div class="flex justify-center">
                                    <label class="relative inline-flex items-center <?= ($r->id == 1) ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' ?>">
                                        <input type="checkbox" name="perm[<?= $r->id ?>][<?= $p->id ?>]" 
                                            class="sr-only peer"
                                            <?= isset($mapping[$r->id][$p->id]) ? 'checked' : '' ?>
                                            <?= ($r->id == 1) ? 'disabled' : '' ?>>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                        <?php if ($r->id == 1): ?>
                                            <!-- Ensure Super Admin permissions are hidden fields so they stay in DB if the query handles it that way -->
                                            <!-- But my DELETE logic protects Role 1, so no need for hidden fields -->
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="px-8 py-6 bg-gray-50/50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3 text-xs text-amber-600 dark:text-amber-400 font-medium">
                    <i class="ph ph-warning-circle text-lg"></i>
                    Super Admin role has immutable full-system access.
                </div>
                <button type="submit" name="save_permissions" class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/30 transition-all duration-200">
                    Apply Secure Assignments
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
