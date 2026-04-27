<?php
$page_title = 'Favorite Routes';
$active_page = 'favorite_routes';
require_once __DIR__ . '/../layouts/header.php';

if (isset($_GET['remove'])) {
    $pdo->prepare("DELETE FROM favorite_routes WHERE id = ? AND user_id = ?")->execute([$_GET['remove'], $_SESSION['user_id']]);
    header("Location: favorite_routes.php");
    exit();
}

$stmt = $pdo->prepare("SELECT f.id as fav_id, r.* FROM favorite_routes f JOIN routes r ON f.route_id = r.id WHERE f.user_id = ? ORDER BY f.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$favorites = $stmt->fetchAll();
?>

<div class="mb-6 bg-gradient-to-r from-yellow-500 to-yellow-600 dark:from-yellow-600 dark:to-yellow-700 rounded-xl shadow-lg border border-yellow-400 dark:border-yellow-600 overflow-hidden">
    <div class="p-8">
        <h2 class="font-extrabold text-3xl text-white mb-2 flex items-center gap-2">
            <i class="ph ph-star-fill text-yellow-200"></i> Quick Access Favorites
        </h2>
        <p class="text-yellow-100 text-lg m-0">Your most frequently used routes saved for one-tap tracking.</p>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="p-6">
        <?php if (empty($favorites)): ?>
            <div class="text-center py-12 px-4">
                <i class="ph ph-star text-6xl text-gray-300 dark:text-gray-600 mb-4 inline-block"></i>
                <h4 class="font-bold text-xl text-gray-900 dark:text-white mb-2">No Saved Routes Yet</h4>
                <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">Browse through the University Routes page and click "Save Route" to pin them here.</p>
                <a href="routes.php" class="inline-flex items-center justify-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors gap-2">
                    <i class="ph ph-path"></i> Explore All Routes
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($favorites as $f): ?>
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 relative shadow-sm hover:shadow-md transition-shadow">
                        <div class="absolute top-4 right-4">
                            <a href="favorite_routes.php?remove=<?php echo $f->fav_id; ?>" onclick="return confirm('Remove from favorites?');" class="text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 transition-colors" title="Remove">
                                <i class="ph ph-x-circle text-2xl"></i>
                            </a>
                        </div>
                        
                        <div class="flex items-center gap-3 mb-4 pr-8">
                            <i class="ph ph-star-fill text-yellow-500 text-2xl"></i>
                            <h3 class="font-extrabold text-gray-900 dark:text-white text-xl truncate"><?php echo htmlspecialchars($f->route_name); ?></h3>
                        </div>
                        
                        <div class="flex flex-col gap-2 mb-6 pl-4 border-l-2 border-gray-200 dark:border-gray-700">
                            <span class="text-sm text-gray-600 dark:text-gray-300"><strong class="text-gray-900 dark:text-white">Start:</strong> <?php echo htmlspecialchars($f->start_point); ?></span>
                            <span class="text-sm text-gray-600 dark:text-gray-300"><strong class="text-gray-900 dark:text-white">End:</strong> <?php echo htmlspecialchars($f->end_point); ?></span>
                        </div>
                        
                        <div class="flex flex-col gap-3">
                            <a href="live_tracking.php?route_id=<?php echo $f->id; ?>" class="flex items-center justify-center w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors gap-2">
                                <i class="ph ph-broadcast"></i> Track Live Now
                            </a>
                            <a href="route_details.php?id=<?php echo $f->id; ?>" class="flex items-center justify-center w-full px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-semibold rounded-lg transition-colors gap-2">
                                <i class="ph ph-list-numbers"></i> View Full Stops
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
