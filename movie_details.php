<?php
/**
 * Adept Cinema - Movie Details Page
 */
require_once __DIR__ . '/common/header.php';

$movieId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($movieId <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch Movie Details with Category Name
$stmt = $pdo->prepare("SELECT m.*, c.name AS category_name FROM movies m LEFT JOIN categories c ON m.category_id = c.id WHERE m.id = ?");
$stmt->execute([$movieId]);
$movie = $stmt->fetch();

if (!$movie) {
    echo '<div class="px-4 py-16 text-center">
            <i class="fa-solid fa-film text-4xl text-gray-600 mb-3"></i>
            <h2 class="text-xl font-bold text-white mb-2">Movie Not Found</h2>
            <p class="text-gray-400 text-sm mb-6">The requested movie does not exist or has been removed.</p>
            <a href="index.php" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-semibold">Return Home</a>
          </div>';
    require_once __DIR__ . '/common/bottom.php';
    exit;
}

// Check if user has this movie in watchlist
$inWatchlist = false;
if (isLoggedIn()) {
    $wlStmt = $pdo->prepare("SELECT 1 FROM watchlist WHERE user_id = ? AND movie_id = ?");
    $wlStmt->execute([$_SESSION['user_id'], $movieId]);
    $inWatchlist = (bool)$wlStmt->fetchColumn();
}

// Handle Watchlist Toggle via Traditional PHP Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_watchlist') {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    if ($inWatchlist) {
        $del = $pdo->prepare("DELETE FROM watchlist WHERE user_id = ? AND movie_id = ?");
        $del->execute([$_SESSION['user_id'], $movieId]);
        setFlash('success', 'Removed from your Watchlist');
    } else {
        $add = $pdo->prepare("INSERT INTO watchlist (user_id, movie_id) VALUES (?, ?)");
        $add->execute([$_SESSION['user_id'], $movieId]);
        setFlash('success', 'Saved to your Watchlist');
    }
    header("Location: movie_details.php?id=" . $movieId);
    exit;
}

// Fetch More Like This (movies from same category)
$stmtRelated = $pdo->prepare("SELECT * FROM movies WHERE category_id = ? AND id != ? ORDER BY id DESC LIMIT 6");
$stmtRelated->execute([$movie['category_id'], $movieId]);
$relatedMovies = $stmtRelated->fetchAll();
?>

<div class="relative pb-8">
    <!-- Top Back Button & Share Navigation Overlay -->
    <div class="absolute top-4 left-4 right-4 z-20 flex items-center justify-between">
        <a href="javascript:history.back()" class="w-9 h-9 rounded-full bg-gray-950/70 backdrop-blur-md border border-gray-800 flex items-center justify-center text-white active:scale-90 transition shadow-lg">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div class="flex items-center gap-2">
            <!-- Watchlist Form Button (traditional POST) -->
            <form method="POST" action="movie_details.php?id=<?= $movieId ?>">
                <input type="hidden" name="action" value="toggle_watchlist">
                <button type="submit" class="w-9 h-9 rounded-full bg-gray-950/70 backdrop-blur-md border border-gray-800 flex items-center justify-center <?= $inWatchlist ? 'text-red-500 border-red-500/50' : 'text-gray-300' ?> active:scale-90 transition shadow-lg" title="<?= $inWatchlist ? 'Remove from Watchlist' : 'Add to Watchlist' ?>">
                    <i class="fa-<?= $inWatchlist ? 'solid' : 'regular' ?> fa-bookmark text-sm"></i>
                </button>
            </form>
            <!-- Category Tag -->
            <a href="categories_page.php?category_id=<?= (int)$movie['category_id'] ?>" class="px-3 py-1.5 rounded-full bg-gray-950/70 backdrop-blur-md border border-gray-800 text-[11px] font-semibold text-gray-300 hover:text-white">
                <?= htmlspecialchars($movie['category_name'] ?: 'Action') ?>
            </a>
        </div>
    </div>

    <!-- Top Large Poster / Backdrop Header with Gradient Fade -->
    <div class="relative w-full aspect-[4/3] sm:aspect-[16/8] max-h-[460px] bg-gray-900 overflow-hidden">
        <img src="<?= htmlspecialchars($movie['backdrop_url'] ?: $movie['poster_url']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>" class="w-full h-full object-cover object-center">
        <!-- Vignette & Fades -->
        <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/50 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-gray-950/70 via-transparent to-transparent"></div>

        <!-- Floating Poster Overlay on Desktop / Large Tablet -->
        <div class="hidden sm:block absolute bottom-6 left-8 w-28 aspect-[2/3] rounded-xl overflow-hidden shadow-2xl border-2 border-gray-800">
            <img src="<?= htmlspecialchars($movie['poster_url']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>" class="w-full h-full object-cover">
        </div>
    </div>

    <!-- Movie Information Section -->
    <div class="px-4 sm:px-8 -mt-6 sm:-mt-2 relative z-10 max-w-4xl mx-auto">
        <!-- Title -->
        <h1 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
            <?= htmlspecialchars($movie['title']) ?>
        </h1>

        <!-- Metadata Pills (Rating, Release Year, Duration, Category) -->
        <div class="flex flex-wrap items-center gap-2 sm:gap-3 mt-2 text-xs text-gray-300">
            <!-- Rating Badge -->
            <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-amber-500/20 border border-amber-500/40 text-amber-400 font-bold">
                <i class="fa-solid fa-star text-[10px]"></i>
                <span><?= number_format($movie['rating'], 1) ?>/10</span>
            </div>

            <!-- Release Year -->
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-900 border border-gray-800 font-semibold text-gray-300">
                <i class="fa-regular fa-calendar text-[10px] text-gray-400"></i>
                <?= htmlspecialchars($movie['release_year']) ?>
            </span>

            <!-- Duration -->
            <?php if (!empty($movie['duration'])): ?>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-900 border border-gray-800 font-medium text-gray-400">
                    <i class="fa-regular fa-clock text-[10px] text-gray-500"></i>
                    <?= htmlspecialchars($movie['duration']) ?>
                </span>
            <?php endif; ?>

            <!-- Ultra HD / Audio Badges -->
            <span class="px-1.5 py-0.5 rounded bg-red-950/60 border border-red-800/80 text-[10px] font-bold text-red-400 uppercase tracking-wider">
                4K Ultra HD
            </span>
            <span class="px-1.5 py-0.5 rounded bg-gray-900 border border-gray-800 text-[10px] font-medium text-gray-400">
                5.1 Surround
            </span>
        </div>

        <!-- Prominent "Watch Now" Button (Opens watch_link in new tab) -->
        <div class="mt-5 flex flex-col sm:flex-row gap-3">
            <a href="<?= htmlspecialchars($movie['watch_link']) ?>" target="_blank" rel="noopener noreferrer" class="flex-1 flex items-center justify-center gap-2.5 py-3.5 px-6 rounded-xl bg-red-600 hover:bg-red-700 active:scale-[0.98] text-white font-bold text-base transition shadow-xl shadow-red-900/50">
                <i class="fa-solid fa-play text-sm"></i>
                <span>Watch Now</span>
            </a>

            <!-- Traditional Watchlist Action -->
            <form method="POST" action="movie_details.php?id=<?= $movieId ?>" class="sm:w-auto">
                <input type="hidden" name="action" value="toggle_watchlist">
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 py-3.5 px-5 rounded-xl bg-gray-900 hover:bg-gray-800 active:scale-[0.98] border border-gray-800 text-gray-200 font-semibold text-sm transition">
                    <i class="fa-<?= $inWatchlist ? 'solid' : 'regular' ?> fa-bookmark <?= $inWatchlist ? 'text-red-500' : '' ?>"></i>
                    <span><?= $inWatchlist ? 'In Watchlist' : 'Add to List' ?></span>
                </button>
            </form>
        </div>

        <!-- Full Description / Synopsis -->
        <div class="mt-6 bg-gray-900/60 border border-gray-800/80 rounded-2xl p-4 sm:p-5">
            <h2 class="text-xs uppercase tracking-wider text-gray-400 font-bold mb-2 flex items-center gap-1.5">
                <i class="fa-solid fa-align-left text-red-500"></i> Synopsis
            </h2>
            <p class="text-sm sm:text-base text-gray-300 leading-relaxed font-normal">
                <?= nl2br(htmlspecialchars($movie['description'])) ?>
            </p>
        </div>

        <!-- Streaming Specs -->
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs">
            <div class="p-3 bg-gray-900/40 border border-gray-800/60 rounded-xl">
                <span class="block text-gray-500 text-[10px] uppercase font-semibold">Audio</span>
                <span class="text-gray-200 font-medium">Dolby Atmos</span>
            </div>
            <div class="p-3 bg-gray-900/40 border border-gray-800/60 rounded-xl">
                <span class="block text-gray-500 text-[10px] uppercase font-semibold">Subtitles</span>
                <span class="text-gray-200 font-medium">English, Spanish, Multi</span>
            </div>
            <div class="p-3 bg-gray-900/40 border border-gray-800/60 rounded-xl">
                <span class="block text-gray-500 text-[10px] uppercase font-semibold">Maturity</span>
                <span class="text-gray-200 font-medium">PG-13 / TV-MA</span>
            </div>
            <div class="p-3 bg-gray-900/40 border border-gray-800/60 rounded-xl">
                <span class="block text-gray-500 text-[10px] uppercase font-semibold">Format</span>
                <span class="text-gray-200 font-medium">HDR10+ / Vision</span>
            </div>
        </div>

        <!-- Related Movies / More Like This -->
        <?php if (!empty($relatedMovies)): ?>
            <div class="mt-8 pt-6 border-t border-gray-900">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <span class="w-1.5 h-3.5 bg-red-600 rounded-full"></span>
                        More Like This
                    </h2>
                    <a href="categories_page.php?category_id=<?= (int)$movie['category_id'] ?>" class="text-xs text-gray-400 hover:text-red-400 font-medium">
                        View All
                    </a>
                </div>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2.5">
                    <?php foreach ($relatedMovies as $rel): ?>
                        <a href="movie_details.php?id=<?= (int)$rel['id'] ?>" class="group flex flex-col focus:outline-none">
                            <div class="aspect-[2/3] rounded-xl overflow-hidden bg-gray-900 border border-gray-800 relative group-hover:border-red-500/60 group-hover:scale-105 transition-all duration-300">
                                <img src="<?= htmlspecialchars($rel['poster_url']) ?>" alt="<?= htmlspecialchars($rel['title']) ?>" loading="lazy" class="w-full h-full object-cover">
                                <div class="absolute top-1 right-1 px-1 py-0.2 rounded bg-gray-950/80 text-[9px] font-bold text-amber-400">
                                    ★ <?= number_format($rel['rating'], 1) ?>
                                </div>
                            </div>
                            <span class="text-[11px] font-medium text-gray-300 group-hover:text-red-400 truncate mt-1">
                                <?= htmlspecialchars($rel['title']) ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/common/bottom.php';
?>
