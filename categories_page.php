<?php
/**
 * Adept Cinema - Categories & Browse Page
 */
require_once __DIR__ . '/common/header.php';

$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Fetch all categories with total movie count
$stmtCats = $pdo->query("
    SELECT c.*, COUNT(m.id) AS total_movies 
    FROM categories c 
    LEFT JOIN movies m ON c.id = m.category_id 
    GROUP BY c.id 
    ORDER BY c.display_order ASC, c.id ASC
");
$allCategories = $stmtCats->fetchAll();

// If a category is selected, fetch that category and its movies
$activeCategory = null;
$categoryMovies = [];

if ($categoryId > 0) {
    foreach ($allCategories as $c) {
        if ((int)$c['id'] === $categoryId) {
            $activeCategory = $c;
            break;
        }
    }
    if ($activeCategory) {
        $stmtM = $pdo->prepare("SELECT * FROM movies WHERE category_id = ? ORDER BY id DESC");
        $stmtM->execute([$categoryId]);
        $categoryMovies = $stmtM->fetchAll();
    }
}

// Icon helper for genres
function getCategoryIcon($slug) {
    if (str_contains($slug, 'trend')) return 'fa-fire text-amber-500';
    if (str_contains($slug, 'action')) return 'fa-burst text-orange-500';
    if (str_contains($slug, 'sci-fi')) return 'fa-vr-cardboard text-cyan-400';
    if (str_contains($slug, 'top')) return 'fa-crown text-yellow-400';
    if (str_contains($slug, 'anime')) return 'fa-wand-magic-sparkles text-pink-400';
    if (str_contains($slug, 'horror')) return 'fa-skull text-purple-400';
    return 'fa-film text-red-500';
}
?>

<div class="px-4 py-4 space-y-6">
    <!-- Header Title -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-red-600"></i>
                <?= $activeCategory ? htmlspecialchars($activeCategory['name']) : 'Explore Categories' ?>
            </h1>
            <p class="text-xs text-gray-400 mt-0.5">
                <?= $activeCategory ? $activeCategory['total_movies'] . ' movies in this collection' : 'Browse our curated catalog across all film genres' ?>
            </p>
        </div>
        <?php if ($activeCategory): ?>
            <a href="categories_page.php" class="px-3 py-1.5 rounded-xl bg-gray-900 border border-gray-800 text-xs font-medium text-gray-300 hover:text-white transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left text-[10px]"></i> All Genres
            </a>
        <?php endif; ?>
    </div>

    <!-- Category Pills Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
        <a href="categories_page.php" class="shrink-0 px-3.5 py-1.5 rounded-full text-xs font-semibold transition <?= ($categoryId === 0) ? 'bg-red-600 text-white shadow-md shadow-red-900/40' : 'bg-gray-900 hover:bg-gray-800 text-gray-300 border border-gray-800' ?>">
            All Categories
        </a>
        <?php foreach ($allCategories as $cat): ?>
            <a href="categories_page.php?category_id=<?= (int)$cat['id'] ?>" class="shrink-0 px-3.5 py-1.5 rounded-full text-xs font-semibold transition <?= ($categoryId === (int)$cat['id']) ? 'bg-red-600 text-white shadow-md shadow-red-900/40' : 'bg-gray-900 hover:bg-gray-800 text-gray-300 border border-gray-800' ?>">
                <i class="fa-solid <?= getCategoryIcon($cat['slug']) ?> mr-1 text-[10px]"></i>
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- VIEW 1: Specific Category Selected -->
    <?php if ($activeCategory): ?>
        <?php if (!empty($categoryMovies)): ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
                <?php foreach ($categoryMovies as $movie): ?>
                    <a href="movie_details.php?id=<?= (int)$movie['id'] ?>" class="group flex flex-col focus:outline-none">
                        <div class="relative aspect-[2/3] rounded-xl overflow-hidden bg-gray-900 border border-gray-800 shadow-md group-hover:border-red-500/60 group-hover:scale-[1.03] transition-all duration-300">
                            <img src="<?= htmlspecialchars($movie['poster_url']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>" loading="lazy" class="w-full h-full object-cover group-hover:brightness-105 transition duration-300">
                            
                            <!-- Rating -->
                            <div class="absolute top-1.5 right-1.5 px-1.5 py-0.5 rounded-md bg-gray-950/85 backdrop-blur-md text-[10px] font-bold text-amber-400 flex items-center gap-1 shadow">
                                <i class="fa-solid fa-star text-[9px]"></i>
                                <?= number_format($movie['rating'], 1) ?>
                            </div>

                            <!-- Play Hover Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-2">
                                <span class="w-7 h-7 rounded-full bg-red-600 text-white flex items-center justify-center text-xs shadow-lg">
                                    <i class="fa-solid fa-play ml-0.5"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mt-1.5">
                            <h3 class="text-xs font-semibold text-gray-200 group-hover:text-red-400 truncate transition">
                                <?= htmlspecialchars($movie['title']) ?>
                            </h3>
                            <div class="flex items-center justify-between text-[10px] text-gray-400 mt-0.5">
                                <span><?= htmlspecialchars($movie['release_year']) ?></span>
                                <span><?= htmlspecialchars($movie['duration']) ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="py-16 text-center">
                <i class="fa-solid fa-film text-4xl text-gray-600 mb-3"></i>
                <h3 class="text-base font-bold text-white mb-1">No movies in this category yet</h3>
                <p class="text-xs text-gray-400 mb-4">Check back soon or explore other exciting categories!</p>
                <a href="categories_page.php" class="px-4 py-2 bg-red-600 text-white text-xs font-semibold rounded-xl">View All Categories</a>
            </div>
        <?php endif; ?>

    <!-- VIEW 2: Overview Grid of All Categories -->
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
            <?php 
            $stmtSampleMovies = $pdo->prepare("SELECT poster_url FROM movies WHERE category_id = ? ORDER BY id DESC LIMIT 3");
            foreach ($allCategories as $cat): 
                $stmtSampleMovies->execute([$cat['id']]);
                $samples = $stmtSampleMovies->fetchAll();
            ?>
                <a href="categories_page.php?category_id=<?= (int)$cat['id'] ?>" class="group p-4 bg-gradient-to-br from-gray-900 to-gray-900/60 hover:from-gray-800 hover:to-gray-900/90 border border-gray-800/80 hover:border-red-500/40 rounded-2xl transition-all duration-300 shadow-lg flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-10 h-10 rounded-xl bg-gray-950 border border-gray-800 flex items-center justify-center text-lg">
                                <i class="fa-solid <?= getCategoryIcon($cat['slug']) ?>"></i>
                            </span>
                            <span class="px-2 py-0.5 rounded-full bg-gray-950 text-gray-400 text-[11px] font-semibold border border-gray-800">
                                <?= $cat['total_movies'] ?> Titles
                            </span>
                        </div>
                        <h2 class="text-base font-bold text-white group-hover:text-red-400 transition">
                            <?= htmlspecialchars($cat['name']) ?>
                        </h2>
                    </div>

                    <!-- Mini Poster Thumbnails Preview -->
                    <div class="flex items-center gap-1.5 mt-4 pt-3 border-t border-gray-800/50">
                        <?php if (!empty($samples)): ?>
                            <?php foreach ($samples as $s): ?>
                                <div class="w-12 aspect-[2/3] rounded-md overflow-hidden bg-gray-950 border border-gray-800 shrink-0">
                                    <img src="<?= htmlspecialchars($s['poster_url']) ?>" alt="Movie preview" class="w-full h-full object-cover">
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <div class="ml-auto flex items-center text-xs font-semibold text-red-500 group-hover:translate-x-1 transition-transform">
                            Browse <i class="fa-solid fa-chevron-right ml-1 text-[10px]"></i>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/common/bottom.php';
?>
