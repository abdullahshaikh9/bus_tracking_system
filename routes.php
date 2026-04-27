<?php
$page_title = 'University Routes';
$active_page = 'routes';
require_once __DIR__ . '/../layouts/header.php';

// Passenger specific
$routes_stmt = $pdo->prepare("SELECT * FROM routes ORDER BY route_name ASC");
$routes_stmt->execute();
$routes = $routes_stmt->fetchAll();
?>

<div class="mb-6 bg-gradient-to-r from-primary-600 to-primary-800 dark:from-primary-800 dark:to-primary-950 rounded-xl shadow-lg border border-primary-500 dark:border-primary-700 overflow-hidden">
    <div class="p-8">
        <h2 class="font-extrabold text-3xl text-white mb-2 flex items-center gap-2">
            <i class="ph ph-globe-hemisphere-west"></i> All Campus Routes
        </h2>
        <p class="text-primary-100 text-lg m-0">Explore all active MUET bus routes and find the best path to your destination.</p>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($routes as $r): ?>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-200 flex flex-col">
                    <!-- Map placeholder top area -->
                    <div class="h-32 bg-gray-100 dark:bg-gray-700 relative flex items-center justify-center overflow-hidden">
                        <i class="ph ph-map-trifold text-6xl text-gray-300 dark:text-gray-600 absolute"></i>
                        <div class="absolute bottom-0 left-0 right-0 h-10 bg-gradient-to-t from-black/50 to-transparent"></div>
                    </div>
                    
                    <div class="p-6 flex flex-col flex-1">
                        <h3 class="font-extrabold text-gray-900 dark:text-white text-lg mb-2"><?php echo htmlspecialchars($r->route_name); ?></h3>
                        <div class="text-gray-500 dark:text-gray-400 text-sm mb-4 flex items-center gap-2">
                            <i class="ph ph-clock"></i> <?php echo htmlspecialchars($r->distance_km); ?> km
                        </div>
                        
                        <div class="flex flex-col gap-2 mb-6 flex-1">
                            <div class="flex items-center gap-3">
                                <div class="w-2.5 h-2.5 rounded-full border-2 border-primary-500 bg-white dark:bg-gray-800 relative z-10"></div>
                                <span class="text-sm text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($r->start_point); ?></span>
                            </div>
                            <div class="ml-[4px] border-l-2 border-dashed border-gray-300 dark:border-gray-600 h-4 my-[-8px] z-0"></div>
                            <div class="flex items-center gap-3">
                                <div class="w-2.5 h-2.5 rounded-full bg-primary-500 relative z-10"></div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($r->end_point); ?></span>
                            </div>
                        </div>

                        <a href="route_details.php?id=<?php echo $r->id; ?>" class="flex items-center justify-center w-full px-4 py-2.5 bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-semibold rounded-lg border border-primary-200 dark:border-primary-800 hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors gap-2">
                            View Route Map & Stops &rarr;
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
