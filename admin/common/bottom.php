<?php
/**
 * Adept Cinema - Admin Common Bottom Navigation
 */
$currAdmin = basename($_SERVER['PHP_SELF']);
?>
    </main>

    <!-- Admin Mobile Fixed Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 z-50 bg-gray-950/95 backdrop-blur-lg border-t border-gray-900 px-3 py-2">
        <div class="max-w-md mx-auto flex items-center justify-around text-xs">
            <a href="index.php" class="flex flex-col items-center py-1 px-3 <?= ($currAdmin === 'index.php') ? 'text-red-500 font-bold' : 'text-gray-400 hover:text-white' ?>">
                <i class="fa-solid fa-chart-pie text-base"></i>
                <span class="text-[10px] mt-1">Dashboard</span>
            </a>
            <a href="movies.php" class="flex flex-col items-center py-1 px-3 <?= ($currAdmin === 'movies.php' || $currAdmin === 'manage_movie.php') ? 'text-red-500 font-bold' : 'text-gray-400 hover:text-white' ?>">
                <i class="fa-solid fa-film text-base"></i>
                <span class="text-[10px] mt-1">Movies</span>
            </a>
            <a href="categories.php" class="flex flex-col items-center py-1 px-3 <?= ($currAdmin === 'categories.php') ? 'text-red-500 font-bold' : 'text-gray-400 hover:text-white' ?>">
                <i class="fa-solid fa-layer-group text-base"></i>
                <span class="text-[10px] mt-1">Categories</span>
            </a>
            <a href="banners.php" class="flex flex-col items-center py-1 px-3 <?= ($currAdmin === 'banners.php') ? 'text-red-500 font-bold' : 'text-gray-400 hover:text-white' ?>">
                <i class="fa-solid fa-images text-base"></i>
                <span class="text-[10px] mt-1">Banners</span>
            </a>
        </div>
    </nav>

    <!-- UI/UX & Mobile Lockdown Script (NO AJAX) -->
    <script>
        (function() {
            document.addEventListener('contextmenu', e => e.preventDefault(), { capture: true });
            document.addEventListener('selectstart', e => {
                const tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
                if (tag !== 'input' && tag !== 'textarea' && tag !== 'select') {
                    e.preventDefault();
                }
            }, { capture: true });
            document.addEventListener('keydown', e => {
                if ((e.ctrlKey || e.metaKey) && (e.key === '+' || e.key === '-' || e.key === '=' || e.key === '0')) {
                    e.preventDefault();
                }
            });
            document.addEventListener('wheel', e => {
                if (e.ctrlKey) e.preventDefault();
            }, { passive: false });
        })();
    </script>
</body>
</html>
