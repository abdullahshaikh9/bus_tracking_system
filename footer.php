</div> <!-- End max-w-7xl mx-auto -->
        </main> <!-- End Main Content Scrollable Area -->

    </div> <!-- End Main Flex Area Wrapper -->
    
    <!-- Bottom Navigation for Mobile -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-50 transition-all duration-300 transform" 
         x-show="!sidebarOpen" 
         x-transition:enter="duration-200" 
         x-transition:leave="duration-200"
         style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="flex items-center justify-around h-16">
            <?php
            $role_name = $_SESSION['role_name'] ?? 'Passenger';
            $module = 'user';
            if ($role_name === 'Super Admin' || $role_name === 'Admin') $module = 'admin';
            elseif ($role_name === 'Driver') $module = 'driver';

            $nav_items = [];
            if ($module === 'admin') {
                $nav_items = [
                    ['Dashboard', 'squares-four', '/admin/dashboard.php', 'dashboard'],
                    ['Users', 'users', '/admin/users.php', 'users'],
                    ['Routes', 'map-trifold', '/admin/routes.php', 'routes'],
                    ['Profile', 'user', '/admin/profile.php', 'profile']
                ];
            } elseif ($module === 'driver') {
                $nav_items = [
                    ['Home', 'squares-four', '/driver/dashboard.php', 'dashboard'],
                    ['My Route', 'map-trifold', '/driver/my_route.php', 'my_route'],
                    ['Start', 'play-circle', '/driver/start_trip.php', 'start_trip'],
                    ['Profile', 'user', '/driver/profile.php', 'profile']
                ];
            } else {
                $nav_items = [
                    ['Home', 'squares-four', '/user/dashboard.php', 'dashboard'],
                    ['Live', 'broadcast', '/user/live_tracking.php', 'live_tracking'],
                    ['Plan', 'compass', '/user/trip_planner.php', 'trip_planner'],
                    ['Profile', 'user', '/user/profile.php', 'profile']
                ];
            }

            foreach ($nav_items as $item) {
                $is_active = ($active_page ?? '') == $item[3];
                $color = $is_active ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400';
                $weight = $is_active ? 'fill' : 'regular';
                echo "
                <a href='" . BASE_URL . "{$item[2]}' class='flex flex-col items-center justify-center w-full h-full gap-1'>
                    <i class='ph ph-{$item[1]} text-2xl {$color}'></i>
                    <span class='text-[10px] font-bold tracking-tighter {$color}'>{$item[0]}</span>
                </a>";
            }
            ?>
        </div>
    </nav>

    <!-- Content Padding for Bottom Nav -->
    <style>
        @media (max-width: 1024px) {
            main { padding-bottom: 5rem !important; }
        }
    </style>

    <!-- Mobile Navigation Improvements Script -->
    <script>
        // Handle viewport resize on orientation change
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth >= 1024) {
                    // Close mobile sidebar on desktop
                    if (typeof Alpine !== 'undefined') {
                        Alpine.store('sidebarOpen', false);
                    }
                }
            }, 250);
        });
        
        // Prevent double-tap zoom on iOS
        document.addEventListener('dblclick', function(event) {
            event.preventDefault();
        }, { passive: false });
    </script>

</body>
</html>
