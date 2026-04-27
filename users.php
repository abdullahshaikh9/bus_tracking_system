<?php
$page_title = 'User Management';
$active_page = 'users';
require_once __DIR__ . '/../layouts/header.php';
require_permission($pdo, 'manage_users');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? clean($_GET['search']) : '';
$whereClause = "";
$params = [];

if ($search) {
    $whereClause = "WHERE u.full_name LIKE ? OR u.email LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Get total for pagination
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM users u $whereClause");
$stmtTotal->execute($params);
$total_users = $stmtTotal->fetchColumn();
$total_pages = ceil($total_users / $limit);

// Fetch users
$sql = "SELECT u.*, r.name as role_name 
        FROM users u 
        JOIN roles r ON u.role_id = r.id 
        $whereClause 
        ORDER BY u.created_at DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 px-4 sm:px-0">
        <div>
            <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <i class="ph ph-users-three text-primary-600 dark:text-primary-400"></i> System Users
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-medium">Manage all administrative, driver, and passenger accounts.</p>
        </div>
        <a href="user_create.php" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-400 text-white font-bold rounded-xl shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all w-full sm:w-auto justify-center">
            <i class="ph ph-user-plus text-xl"></i> Add User
        </a>
    </div>

    <!-- Search -->
    <div class="mb-6 px-4 sm:px-0">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                <input type="text" name="search" placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>" class="w-full pl-11 pr-4 py-3 sm:py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400 focus:border-transparent transition shadow-sm text-sm text-gray-900 dark:text-white dark:placeholder-gray-500 font-medium">
            </div>
            <button type="submit" class="px-6 py-3 sm:py-2.5 bg-gray-900 hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 text-white font-bold rounded-xl shadow-sm transition-colors md:w-auto w-full">
                Search
            </button>
            <?php if ($search): ?>
                <a href="users.php" class="px-6 py-3 sm:py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold rounded-xl shadow-sm transition-colors text-center md:w-auto w-full">
                    Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <div class="px-4 sm:px-0">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">User Profile</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Role & Status</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Joined Date</th>
                            <th scope="col" class="px-6 py-4 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        <?php if(empty($users)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 mb-4">
                                    <i class="ph ph-users-three text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No Users Found</h3>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">Try adjusting your search query.</p>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php foreach($users as $u): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-100 to-primary-100 dark:from-indigo-900/40 dark:to-primary-900/40 border border-primary-200 dark:border-primary-800 flex items-center justify-center text-primary-700 dark:text-primary-400 font-bold shrink-0">
                                        <?= strtoupper(substr($u->full_name,0,1)) ?>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-gray-900 dark:text-white text-sm"><?= htmlspecialchars($u->full_name) ?></div>
                                        <div class="text-gray-500 dark:text-gray-400 text-xs truncate mt-0.5"><?= htmlspecialchars($u->email) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800/50 tracking-wide">
                                        <?= htmlspecialchars($u->role_name) ?>
                                    </span>
                                    
                                    <?php if($u->status == 'active'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800/50 tracking-wide">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-red-50 text-red-700 border border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800/50 tracking-wide">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Inactive
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-600 dark:text-gray-300">
                                    <?= date("M d, Y", strtotime($u->created_at)) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="user_edit.php?id=<?= $u->id ?>" class="p-2 text-primary-600 hover:text-primary-700 hover:bg-primary-50 dark:text-primary-400 dark:hover:text-primary-300 dark:hover:bg-primary-900/30 rounded-lg transition-colors" title="Edit User">
                                        <i class="ph ph-pencil-simple text-xl"></i>
                                    </a>
                                    <?php if($u->id != 1 && $u->id != $_SESSION['user_id']): ?>
                                    <a href="user_delete.php?id=<?= $u->id ?>" class="p-2 text-red-500 hover:text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-900/30 rounded-lg transition-colors" onclick="return confirm('Permanently delete this user?')" title="Delete User">
                                        <i class="ph ph-trash text-xl"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 flex flex-col sm:flex-row justify-between items-center gap-4">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Showing <?= count($users) ?> of <?= $total_users ?></span>
                <div class="flex gap-1 overflow-x-auto max-w-full pb-1 sm:pb-0 scrollbar-hide">
                    <?php for($i=1;$i<=$total_pages;$i++): ?>
                        <a href="users.php?page=<?= $i ?><?= $search?'&search='.urlencode($search):'' ?>" 
                           class="min-w-[36px] h-9 flex items-center justify-center rounded-lg text-sm font-bold transition-all <?php echo $page==$i ? 'bg-primary-600 text-white shadow-md' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'; ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>