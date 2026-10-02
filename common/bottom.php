<?php
/**
 * Adept Cinema - User Common Bottom Navigation & Script Lockdown
 */
$curr = basename($_SERVER['PHP_SELF']);
?>
    </main>

    <!-- Fixed Mobile App Bottom Navigation Bar -->
    <nav class="fixed bottom-0 left-0 right-0 z-50 bg-gray-950/95 backdrop-blur-lg border-t border-gray-900/90 px-2 py-2">
        <div class="max-w-md mx-auto flex items-center justify-around">
            <!-- Home Item -->
            <a href="index.php" class="flex flex-col items-center justify-center py-1 px-3 rounded-xl transition-all duration-200 <?= ($curr === 'index.php' || $curr === '') ? 'text-red-500 font-bold' : 'text-gray-400 hover:text-gray-200' ?>">
                <div class="relative">
                    <i class="fa-solid fa-house text-lg <?= ($curr === 'index.php' || $curr === '') ? 'scale-110' : '' ?> transition-transform"></i>
                    <?php if ($curr === 'index.php' || $curr === ''): ?>
                        <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-red-500 rounded-full"></span>
                    <?php endif; ?>
                </div>
                <span class="text-[10px] mt-1 tracking-tight">Home</span>
            </a>

            <!-- Search Item -->
            <a href="search.php" class="flex flex-col items-center justify-center py-1 px-3 rounded-xl transition-all duration-200 <?= ($curr === 'search.php') ? 'text-red-500 font-bold' : 'text-gray-400 hover:text-gray-200' ?>">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass text-lg <?= ($curr === 'search.php') ? 'scale-110' : '' ?> transition-transform"></i>
                    <?php if ($curr === 'search.php'): ?>
                        <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-red-500 rounded-full"></span>
                    <?php endif; ?>
                </div>
                <span class="text-[10px] mt-1 tracking-tight">Search</span>
            </a>

            <!-- Categories Item -->
            <a href="categories_page.php" class="flex flex-col items-center justify-center py-1 px-3 rounded-xl transition-all duration-200 <?= ($curr === 'categories_page.php') ? 'text-red-500 font-bold' : 'text-gray-400 hover:text-gray-200' ?>">
                <div class="relative">
                    <i class="fa-solid fa-layer-group text-lg <?= ($curr === 'categories_page.php') ? 'scale-110' : '' ?> transition-transform"></i>
                    <?php if ($curr === 'categories_page.php'): ?>
                        <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-red-500 rounded-full"></span>
                    <?php endif; ?>
                </div>
                <span class="text-[10px] mt-1 tracking-tight">Categories</span>
            </a>

            <!-- Profile Item -->
            <a href="<?= isLoggedIn() ? 'profile.php' : 'login.php' ?>" class="flex flex-col items-center justify-center py-1 px-3 rounded-xl transition-all duration-200 <?= ($curr === 'profile.php' || $curr === 'login.php') ? 'text-red-500 font-bold' : 'text-gray-400 hover:text-gray-200' ?>">
                <div class="relative">
                    <i class="fa-solid fa-user text-lg <?= ($curr === 'profile.php' || $curr === 'login.php') ? 'scale-110' : '' ?> transition-transform"></i>
                    <?php if ($curr === 'profile.php' || $curr === 'login.php'): ?>
                        <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-red-500 rounded-full"></span>
                    <?php endif; ?>
                </div>
                <span class="text-[10px] mt-1 tracking-tight"><?= isLoggedIn() ? 'Profile' : 'Sign In' ?></span>
            </a>
        </div>
    </nav>

    <!-- UI/UX & Mobile Lockdown Script (NO AJAX, purely UI lock as required) -->
    <script>
        (function() {
            // 1. Disable right-click context menu
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                return false;
            }, { capture: true });

            // 2. Disable text selection (preserve form input editing)
            document.addEventListener('selectstart', function(e) {
                const tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
                if (tag !== 'input' && tag !== 'textarea') {
                    e.preventDefault();
                    return false;
                }
            }, { capture: true });

            // 3. Disable zoom via keyboard shortcuts (Ctrl/Cmd + '+', '-', '0')
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && (e.key === '+' || e.key === '-' || e.key === '=' || e.key === '0')) {
                    e.preventDefault();
                }
            });

            // 4. Disable wheel zoom (Ctrl + mouse wheel)
            document.addEventListener('wheel', function(e) {
                if (e.ctrlKey) {
                    e.preventDefault();
                }
            }, { passive: false });

            // 5. Disable pinch-to-zoom and multi-touch gestures
            document.addEventListener('touchstart', function(e) {
                if (e.touches && e.touches.length > 1) {
                    e.preventDefault();
                }
            }, { passive: false });

            // 6. Disable double-tap to zoom
            let lastTouchEnd = 0;
            document.addEventListener('touchend', function(e) {
                const now = (new Date()).getTime();
                if (now - lastTouchEnd <= 300) {
                    e.preventDefault();
                }
                lastTouchEnd = now;
            }, false);
        })();
    </script>
</body>
</html>
