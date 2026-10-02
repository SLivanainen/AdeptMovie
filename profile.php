<?php
/**
 * Adept Cinema - User Profile & Saved Watchlist
 */
require_once __DIR__ . '/common/config.php';

// Must be logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser($pdo);

// Handle Logout via traditional POST/GET
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle Watchlist Removal via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_watchlist') {
    $removeId = (int)($_POST['movie_id'] ?? 0);
    if ($removeId > 0) {
        $del = $pdo->prepare("DELETE FROM watchlist WHERE user_id = ? AND movie_id = ?");
        $del->execute([$_SESSION['user_id'], $removeId]);
        setFlash('success', 'Movie removed from your Watchlist');
        header('Location: profile.php');
        exit;
    }
}

// Fetch Watchlist Movies
$stmtWl = $pdo->prepare("
    SELECT m.*, c.name AS category_name, w.created_at AS saved_at 
    FROM watchlist w 
    JOIN movies m ON w.movie_id = m.id 
    LEFT JOIN categories c ON m.category_id = c.id 
    WHERE w.user_id = ? 
    ORDER BY w.id DESC
");
$stmtWl->execute([$_SESSION['user_id']]);
$watchlistMovies = $stmtWl->fetchAll();

require_once __DIR__ . '/common/header.php';
?>

<div class="px-4 py-6 max-w-4xl mx-auto space-y-6">
    <!-- Profile Card -->
    <div class="bg-gray-900/90 border border-gray-800 rounded-3xl p-5 sm:p-6 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 right-0 w-48 h-48 bg-red-600/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 text-center sm:text-left relative z-10">
            <!-- Avatar -->
            <div class="relative">
                <img src="<?= htmlspecialchars($user['avatar'] ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200') ?>" alt="<?= htmlspecialchars($user['username']) ?>" class="w-20 h-20 rounded-2xl object-cover border-2 border-red-500 shadow-lg shadow-red-950/40">
                <span class="absolute -bottom-1 -right-1 px-1.5 py-0.5 rounded-full bg-red-600 text-white text-[9px] font-bold uppercase tracking-wider">
                    <?= htmlspecialchars($user['role']) ?>
                </span>
            </div>

            <!-- Details -->
            <div class="flex-1">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h1 class="text-xl font-bold text-white"><?= htmlspecialchars($user['username']) ?></h1>
                        <p class="text-xs text-gray-400"><?= htmlspecialchars($user['email']) ?></p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-center sm:justify-end gap-2 mt-2 sm:mt-0">
                        <?php if (isAdmin()): ?>
                            <a href="admin/index.php" class="px-3.5 py-1.5 rounded-xl bg-red-600/20 border border-red-500/50 text-red-400 hover:bg-red-600 hover:text-white text-xs font-semibold transition flex items-center gap-1.5">
                                <i class="fa-solid fa-shield-halved"></i> Admin Console
                            </a>
                        <?php endif; ?>
                        <a href="profile.php?action=logout" class="px-3.5 py-1.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white text-xs font-semibold transition flex items-center gap-1.5 border border-gray-700">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out
                        </a>
                    </div>
                </div>

                <!-- Profile Meta Badges -->
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 mt-4 text-xs text-gray-400">
                    <span class="flex items-center gap-1.5 bg-gray-950 px-2.5 py-1 rounded-lg border border-gray-800">
                        <i class="fa-regular fa-clock text-red-500"></i> Member since <?= date('M Y', strtotime($user['created_at'])) ?>
                    </span>
                    <span class="flex items-center gap-1.5 bg-gray-950 px-2.5 py-1 rounded-lg border border-gray-800">
                        <i class="fa-solid fa-bookmark text-red-500"></i> <?= count($watchlistMovies) ?> Saved in Watchlist
                    </span>
                    <span class="flex items-center gap-1.5 bg-gray-950 px-2.5 py-1 rounded-lg border border-gray-800">
                        <i class="fa-solid fa-crown text-amber-400"></i> VIP Stream Pass
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Watchlist Section -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base sm:text-lg font-bold text-white flex items-center gap-2">
                <span class="w-1.5 h-4 bg-red-600 rounded-full"></span>
                My Saved Watchlist
            </h2>
            <span class="text-xs text-gray-400"><?= count($watchlistMovies) ?> Title<?= count($watchlistMovies) === 1 ? '' : 's' ?></span>
        </div>

        <?php if (!empty($watchlistMovies)): ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
                <?php foreach ($watchlistMovies as $movie): ?>
                    <div class="group flex flex-col relative bg-gray-900/60 rounded-xl overflow-hidden border border-gray-800">
                        <a href="movie_details.php?id=<?= (int)$movie['id'] ?>" class="relative aspect-[2/3] w-full block overflow-hidden bg-gray-900">
                            <img src="<?= htmlspecialchars($movie['poster_url']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute top-1.5 right-1.5 px-1.5 py-0.5 rounded bg-gray-950/85 text-[10px] font-bold text-amber-400">
                                ★ <?= number_format($movie['rating'], 1) ?>
                            </div>
                        </a>

                        <div class="p-2 flex flex-col justify-between flex-1">
                            <h3 class="text-xs font-semibold text-gray-200 truncate group-hover:text-red-400">
                                <?= htmlspecialchars($movie['title']) ?>
                            </h3>
                            <div class="flex items-center justify-between mt-1 text-[10px] text-gray-400">
                                <span><?= htmlspecialchars($movie['release_year']) ?></span>
                                <!-- Traditional Form Submit to Remove -->
                                <form method="POST" action="profile.php" class="inline">
                                    <input type="hidden" name="action" value="remove_watchlist">
                                    <input type="hidden" name="movie_id" value="<?= (int)$movie['id'] ?>">
                                    <button type="submit" class="text-gray-500 hover:text-red-400 transition" title="Remove from list">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="py-12 bg-gray-900/40 border border-gray-800/80 rounded-2xl text-center px-4">
                <div class="w-12 h-12 bg-gray-900 rounded-full flex items-center justify-center text-gray-500 mx-auto mb-2 text-lg">
                    <i class="fa-regular fa-bookmark"></i>
                </div>
                <h3 class="text-sm font-bold text-white mb-1">Your Watchlist is empty</h3>
                <p class="text-xs text-gray-400 mb-4 max-w-xs mx-auto">Explore movies and click the bookmark icon to save titles for later streaming.</p>
                <a href="index.php" class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-semibold transition">
                    <i class="fa-solid fa-compass"></i> Discover Movies
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/common/bottom.php';
?>
