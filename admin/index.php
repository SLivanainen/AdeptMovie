<?php
/**
 * Adept Cinema - Admin Dashboard
 */
require_once __DIR__ . '/common/header.php';

// Fetch quick stats
$totalMovies = $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalBanners = $pdo->query("SELECT COUNT(*) FROM banners WHERE is_active = 1")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Fetch latest movies
$stmtLatest = $pdo->query("
    SELECT m.*, c.name AS category_name 
    FROM movies m 
    LEFT JOIN categories c ON m.category_id = c.id 
    ORDER BY m.id DESC 
    LIMIT 6
");
$recentMovies = $stmtLatest->fetchAll();
?>

<div class="space-y-6">
    <!-- Welcome Header Banner -->
    <div class="bg-gradient-to-r from-red-950/60 via-gray-900 to-gray-900 border border-red-900/40 rounded-3xl p-5 sm:p-6 shadow-xl relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-red-600/30 text-red-400 text-[10px] font-bold uppercase tracking-wider mb-2 border border-red-500/30">
                    <i class="fa-solid fa-signal text-[9px]"></i> Live Operations
                </span>
                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                    Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Administrator') ?>!
                </h1>
                <p class="text-xs text-gray-400 mt-0.5">Manage streaming catalog, movies, banners, and taxonomy in real-time.</p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex items-center gap-2">
                <a href="movies.php#add" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 active:scale-95 text-white text-xs font-bold transition shadow-lg shadow-red-900/40 flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-plus text-xs"></i> Add New Movie
                </a>
                <a href="categories.php#add" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl bg-gray-900 hover:bg-gray-800 active:scale-95 text-gray-200 text-xs font-semibold border border-gray-800 transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-folder-plus text-xs text-red-500"></i> Add Category
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats Mobile-App Grid Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <!-- Movies Stat -->
        <a href="movies.php" class="bg-gray-900/80 border border-gray-800/80 hover:border-red-500/40 rounded-2xl p-4 transition-all duration-200 group shadow-md">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-400 font-medium">Total Movies</span>
                <div class="w-8 h-8 rounded-lg bg-red-600/10 text-red-500 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-film"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-white"><?= number_format($totalMovies) ?></div>
            <div class="text-[10px] text-gray-500 mt-1 flex items-center gap-1">
                <i class="fa-solid fa-arrow-trend-up text-emerald-400"></i> Active in catalog
            </div>
        </a>

        <!-- Categories Stat -->
        <a href="categories.php" class="bg-gray-900/80 border border-gray-800/80 hover:border-red-500/40 rounded-2xl p-4 transition-all duration-200 group shadow-md">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-400 font-medium">Categories</span>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-white"><?= number_format($totalCategories) ?></div>
            <div class="text-[10px] text-gray-500 mt-1 flex items-center gap-1">
                <i class="fa-solid fa-tags text-amber-400"></i> Active genres
            </div>
        </a>

        <!-- Banners Stat -->
        <a href="banners.php" class="bg-gray-900/80 border border-gray-800/80 hover:border-red-500/40 rounded-2xl p-4 transition-all duration-200 group shadow-md">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-400 font-medium">Active Banners</span>
                <div class="w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-images"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-white"><?= number_format($totalBanners) ?></div>
            <div class="text-[10px] text-gray-500 mt-1 flex items-center gap-1">
                <i class="fa-solid fa-bullhorn text-cyan-400"></i> Top featured
            </div>
        </a>

        <!-- Members Stat -->
        <div class="bg-gray-900/80 border border-gray-800/80 rounded-2xl p-4 shadow-md">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-400 font-medium">Members</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-white"><?= number_format($totalUsers) ?></div>
            <div class="text-[10px] text-gray-500 mt-1 flex items-center gap-1">
                <i class="fa-solid fa-user-check text-emerald-400"></i> Registered users
            </div>
        </div>
    </div>

    <!-- Quick Navigation Hub -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <a href="movies.php" class="flex items-center justify-between p-4 bg-gray-900 border border-gray-800 hover:border-red-500/40 rounded-2xl transition group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-600/20 text-red-500 flex items-center justify-center text-base">
                    <i class="fa-solid fa-clapperboard"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-white group-hover:text-red-400 transition">Manage Movies</h3>
                    <p class="text-[11px] text-gray-400">Add, edit, delete & set watch links</p>
                </div>
            </div>
            <i class="fa-solid fa-arrow-right text-xs text-gray-500 group-hover:text-red-400 group-hover:translate-x-1 transition-transform"></i>
        </a>

        <a href="categories.php" class="flex items-center justify-between p-4 bg-gray-900 border border-gray-800 hover:border-amber-500/40 rounded-2xl transition group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-base">
                    <i class="fa-solid fa-folder-tree"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-white group-hover:text-amber-400 transition">Manage Categories</h3>
                    <p class="text-[11px] text-gray-400">Add & reorder home screen rows</p>
                </div>
            </div>
            <i class="fa-solid fa-arrow-right text-xs text-gray-500 group-hover:text-amber-400 group-hover:translate-x-1 transition-transform"></i>
        </a>

        <a href="banners.php" class="flex items-center justify-between p-4 bg-gray-900 border border-gray-800 hover:border-cyan-500/40 rounded-2xl transition group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center text-base">
                    <i class="fa-solid fa-panorama"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-white group-hover:text-cyan-400 transition">Hero Banners</h3>
                    <p class="text-[11px] text-gray-400">Update top carousel images</p>
                </div>
            </div>
            <i class="fa-solid fa-arrow-right text-xs text-gray-500 group-hover:text-cyan-400 group-hover:translate-x-1 transition-transform"></i>
        </a>
    </div>

    <!-- Recent Movies Table / List -->
    <div class="bg-gray-900/80 border border-gray-800 rounded-3xl p-5 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm sm:text-base font-bold text-white flex items-center gap-2">
                <span class="w-1.5 h-4 bg-red-600 rounded-full"></span>
                Recent Movies Added
            </h2>
            <a href="movies.php" class="text-xs font-semibold text-red-500 hover:text-red-400 transition flex items-center gap-1">
                View All <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="divide-y divide-gray-800/80">
            <?php foreach ($recentMovies as $m): ?>
                <div class="py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <img src="<?= htmlspecialchars($m['poster_url']) ?>" alt="poster" class="w-10 aspect-[2/3] rounded-lg object-cover bg-gray-950 border border-gray-800 shrink-0">
                        <div class="min-w-0">
                            <h3 class="text-xs font-bold text-white truncate"><?= htmlspecialchars($m['title']) ?></h3>
                            <div class="flex items-center gap-2 text-[10px] text-gray-400 mt-0.5">
                                <span class="text-red-400 font-medium"><?= htmlspecialchars($m['category_name'] ?: 'None') ?></span>
                                <span>•</span>
                                <span><?= htmlspecialchars($m['release_year']) ?></span>
                                <span>•</span>
                                <span class="text-amber-400 font-semibold">★ <?= number_format($m['rating'], 1) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0">
                        <a href="manage_movie.php?action=edit&id=<?= (int)$m['id'] ?>" class="w-7 h-7 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white flex items-center justify-center text-xs transition" title="Edit Movie">
                            <i class="fa-solid fa-pen text-[10px]"></i>
                        </a>
                        <a href="../movie_details.php?id=<?= (int)$m['id'] ?>" target="_blank" class="w-7 h-7 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white flex items-center justify-center text-xs transition" title="Preview Live">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/common/bottom.php';
?>
