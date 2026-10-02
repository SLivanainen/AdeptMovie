<?php
/**
 * Adept Cinema - Search Movies Page (Standard GET submission)
 */
require_once __DIR__ . '/common/header.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$selectedCategory = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$results = [];

// Fetch categories for search filter pills
$stmtCats = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC");
$categories = $stmtCats->fetchAll();

// Execute search query if provided
if ($query !== '' || $selectedCategory > 0) {
    $sql = "SELECT m.*, c.name AS category_name FROM movies m LEFT JOIN categories c ON m.category_id = c.id WHERE 1=1";
    $params = [];

    if ($query !== '') {
        $sql .= " AND (m.title LIKE ? OR m.description LIKE ?)";
        $params[] = '%' . $query . '%';
        $params[] = '%' . $query . '%';
    }

    if ($selectedCategory > 0) {
        $sql .= " AND m.category_id = ?";
        $params[] = $selectedCategory;
    }

    $sql .= " ORDER BY m.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
} else {
    // Top recommended movies when no search term is entered
    $stmt = $pdo->query("SELECT m.*, c.name AS category_name FROM movies m LEFT JOIN categories c ON m.category_id = c.id ORDER BY m.rating DESC LIMIT 12");
    $results = $stmt->fetchAll();
}
?>

<div class="px-4 py-4 space-y-5">
    <!-- Top Search Form (Standard GET submission, NO AJAX) -->
    <form method="GET" action="search.php" class="relative max-w-2xl mx-auto">
        <div class="relative flex items-center">
            <span class="absolute left-4 text-gray-400">
                <i class="fa-solid fa-magnifying-glass text-base"></i>
            </span>
            <input 
                type="text" 
                name="q" 
                value="<?= htmlspecialchars($query) ?>" 
                placeholder="Search movies, series, or keywords..." 
                class="w-full pl-11 pr-24 py-3.5 bg-gray-900 border border-gray-800 rounded-2xl text-white placeholder-gray-500 text-sm focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition shadow-inner"
                autofocus
            >
            <div class="absolute right-2 flex items-center gap-1">
                <?php if ($query !== ''): ?>
                    <a href="search.php" class="px-2 py-1 text-xs text-gray-400 hover:text-white" title="Clear">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </a>
                <?php endif; ?>
                <button type="submit" class="px-3.5 py-1.5 bg-red-600 hover:bg-red-700 active:scale-95 text-white text-xs font-semibold rounded-xl transition shadow">
                    Search
                </button>
            </div>
        </div>

        <?php if ($selectedCategory > 0): ?>
            <input type="hidden" name="category_id" value="<?= $selectedCategory ?>">
        <?php endif; ?>
    </form>

    <!-- Category Filter Chips -->
    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
        <a href="search.php<?= $query !== '' ? '?q=' . urlencode($query) : '' ?>" class="shrink-0 px-3 py-1 rounded-full text-xs font-medium transition <?= $selectedCategory === 0 ? 'bg-red-600 text-white' : 'bg-gray-900 text-gray-300 hover:bg-gray-800 border border-gray-800' ?>">
            All Categories
        </a>
        <?php foreach ($categories as $cat): ?>
            <?php 
            $catUrl = 'search.php?category_id=' . $cat['id'];
            if ($query !== '') $catUrl .= '&q=' . urlencode($query);
            ?>
            <a href="<?= $catUrl ?>" class="shrink-0 px-3 py-1 rounded-full text-xs font-medium transition <?= $selectedCategory === (int)$cat['id'] ? 'bg-red-600 text-white' : 'bg-gray-900 text-gray-300 hover:bg-gray-800 border border-gray-800' ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Search Status / Section Header -->
    <div class="flex items-center justify-between pt-1">
        <h2 class="text-sm sm:text-base font-bold text-white flex items-center gap-2">
            <?php if ($query !== ''): ?>
                <i class="fa-solid fa-filter text-red-500 text-xs"></i>
                Results for "<span class="text-red-400"><?= htmlspecialchars($query) ?></span>"
            <?php elseif ($selectedCategory > 0): ?>
                <i class="fa-solid fa-layer-group text-red-500 text-xs"></i>
                Category Movies
            <?php else: ?>
                <i class="fa-solid fa-chart-line text-red-500 text-xs"></i>
                Trending Recommendations
            <?php endif; ?>
        </h2>
        <span class="text-xs text-gray-400 font-medium">
            <?= count($results) ?> title<?= count($results) === 1 ? '' : 's' ?>
        </span>
    </div>

    <!-- Results Grid -->
    <?php if (!empty($results)): ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
            <?php foreach ($results as $movie): ?>
                <a href="movie_details.php?id=<?= (int)$movie['id'] ?>" class="group flex flex-col focus:outline-none">
                    <div class="relative aspect-[2/3] rounded-xl overflow-hidden bg-gray-900 border border-gray-800 shadow-md group-hover:border-red-500/60 group-hover:scale-[1.03] transition-all duration-300">
                        <img src="<?= htmlspecialchars($movie['poster_url']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>" loading="lazy" class="w-full h-full object-cover group-hover:brightness-105 transition duration-300">
                        
                        <!-- Rating Badge -->
                        <div class="absolute top-1.5 right-1.5 px-1.5 py-0.5 rounded-md bg-gray-950/85 backdrop-blur-md text-[10px] font-bold text-amber-400 flex items-center gap-1 shadow">
                            <i class="fa-solid fa-star text-[9px]"></i>
                            <?= number_format($movie['rating'], 1) ?>
                        </div>

                        <!-- Hover Overlay Play -->
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
                            <span class="truncate ml-1 text-gray-500"><?= htmlspecialchars($movie['category_name'] ?: 'Feature') ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- No Results Empty State -->
        <div class="py-16 text-center max-w-sm mx-auto">
            <div class="w-14 h-14 bg-gray-900 border border-gray-800 rounded-full flex items-center justify-center text-gray-500 mx-auto mb-3 text-xl">
                <i class="fa-solid fa-film"></i>
            </div>
            <h3 class="text-base font-bold text-white mb-1">No movies matched your search</h3>
            <p class="text-xs text-gray-400 mb-5">Try checking your spelling or search for popular genres like Sci-Fi, Cyberpunk, or Action.</p>
            <a href="search.php" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-semibold transition">
                <i class="fa-solid fa-rotate-left text-xs"></i> Reset Search
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/common/bottom.php';
?>
