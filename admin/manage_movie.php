<?php
/**
 * Adept Cinema - Admin Edit & Delete Movie Handler
 */
require_once __DIR__ . '/common/header.php';

$movieId = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$action = $_REQUEST['action'] ?? 'edit';

if ($movieId <= 0) {
    header('Location: movies.php');
    exit;
}

// 1. Handle Deletion (via POST or GET)
if ($action === 'delete') {
    // Delete any watchlist references first
    $stmtWl = $pdo->prepare("DELETE FROM watchlist WHERE movie_id = ?");
    $stmtWl->execute([$movieId]);

    // Delete movie
    $stmtDel = $pdo->prepare("DELETE FROM movies WHERE id = ?");
    $stmtDel->execute([$movieId]);

    setFlash('success', 'Movie has been deleted from catalog.');
    header('Location: movies.php');
    exit;
}

// 2. Handle Update (via POST)
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $title = trim($_POST['title'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $posterUrl = trim($_POST['poster_url'] ?? '');
    $backdropUrl = trim($_POST['backdrop_url'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $rating = (float)($_POST['rating'] ?? 7.5);
    $releaseYear = (int)($_POST['release_year'] ?? date('Y'));
    $duration = trim($_POST['duration'] ?? '2h 00m');
    $watchLink = trim($_POST['watch_link'] ?? '');

    if (empty($title) || $categoryId <= 0 || empty($posterUrl) || empty($description) || empty($watchLink)) {
        $error = 'All fields marked with an asterisk (*) are required.';
    } else {
        if (empty($backdropUrl)) {
            $backdropUrl = $posterUrl;
        }

        $stmtUp = $pdo->prepare("
            UPDATE movies 
            SET title = ?, category_id = ?, poster_url = ?, backdrop_url = ?, description = ?, rating = ?, release_year = ?, duration = ?, watch_link = ? 
            WHERE id = ?
        ");
        $stmtUp->execute([
            $title,
            $categoryId,
            $posterUrl,
            $backdropUrl,
            $description,
            $rating,
            $releaseYear,
            $duration,
            $watchLink,
            $movieId
        ]);

        setFlash('success', 'Movie "' . htmlspecialchars($title) . '" updated successfully!');
        header('Location: movies.php');
        exit;
    }
}

// 3. Fetch Movie for Edit Form
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$movieId]);
$movie = $stmt->fetch();

if (!$movie) {
    setFlash('error', 'Movie not found.');
    header('Location: movies.php');
    exit;
}

// Fetch categories for dropdown
$stmtCats = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC, name ASC");
$categories = $stmtCats->fetchAll();
?>

<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="movies.php" class="px-3 py-1.5 rounded-xl bg-gray-900 border border-gray-800 text-xs font-medium text-gray-300 hover:text-white transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left text-[10px]"></i> Back to Movies
        </a>
        <a href="../movie_details.php?id=<?= $movieId ?>" target="_blank" class="text-xs text-red-400 hover:text-red-300 flex items-center gap-1 font-semibold">
            Preview Live <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="p-3 rounded-xl bg-red-950/80 border border-red-800 text-red-300 text-xs flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation shrink-0"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- EDIT MOVIE FORM CARD -->
    <div class="bg-gray-900/90 border border-gray-800 rounded-3xl p-5 sm:p-6 shadow-xl">
        <div class="flex items-center justify-between mb-5">
            <h1 class="text-lg font-bold text-white flex items-center gap-2">
                <span class="w-1.5 h-4 bg-red-600 rounded-full"></span>
                Edit Movie: <span class="text-red-400"><?= htmlspecialchars($movie['title']) ?></span>
            </h1>

            <!-- Quick Delete in Header -->
            <form method="POST" action="manage_movie.php" onsubmit="return confirm('Permanently delete this movie?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $movieId ?>">
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-red-950/60 hover:bg-red-900 border border-red-900/50 text-red-400 text-xs font-semibold transition">
                    <i class="fa-solid fa-trash-can mr-1"></i> Delete Movie
                </button>
            </form>
        </div>

        <!-- Standard PHP Form Submission (NO AJAX) -->
        <form method="POST" action="manage_movie.php" class="space-y-4">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $movieId ?>">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Title -->
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Movie Title *</label>
                    <input 
                        type="text" 
                        name="title" 
                        required 
                        value="<?= htmlspecialchars($movie['title']) ?>"
                        class="w-full px-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs focus:outline-none focus:border-red-500 transition"
                    >
                </div>

                <!-- Category Selection Dropdown -->
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Category / Genre *</label>
                    <select 
                        name="category_id" 
                        required
                        class="w-full px-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs focus:outline-none focus:border-red-500 transition"
                    >
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= ((int)$movie['category_id'] === (int)$c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Poster URL -->
            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Poster Image URL *</label>
                <input 
                    type="url" 
                    name="poster_url" 
                    required 
                    value="<?= htmlspecialchars($movie['poster_url']) ?>"
                    class="w-full px-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs focus:outline-none focus:border-red-500 transition"
                >
            </div>

            <!-- Backdrop URL -->
            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Backdrop Image URL (Optional)</label>
                <input 
                    type="url" 
                    name="backdrop_url" 
                    value="<?= htmlspecialchars($movie['backdrop_url'] ?? '') ?>"
                    class="w-full px-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs focus:outline-none focus:border-red-500 transition"
                >
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Full Description / Synopsis *</label>
                <textarea 
                    name="description" 
                    rows="4" 
                    required 
                    class="w-full px-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs focus:outline-none focus:border-red-500 transition"
                ><?= htmlspecialchars($movie['description']) ?></textarea>
            </div>

            <!-- Row: Rating, Release Year, Duration -->
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Rating (0-10)</label>
                    <input 
                        type="number" 
                        name="rating" 
                        step="0.1" 
                        min="1" 
                        max="10" 
                        value="<?= htmlspecialchars($movie['rating']) ?>"
                        class="w-full px-3 py-2 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs focus:outline-none focus:border-red-500 transition"
                    >
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Release Year</label>
                    <input 
                        type="number" 
                        name="release_year" 
                        value="<?= htmlspecialchars($movie['release_year']) ?>"
                        class="w-full px-3 py-2 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs focus:outline-none focus:border-red-500 transition"
                    >
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Duration</label>
                    <input 
                        type="text" 
                        name="duration" 
                        value="<?= htmlspecialchars($movie['duration'] ?? '2h 00m') ?>"
                        class="w-full px-3 py-2 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs focus:outline-none focus:border-red-500 transition"
                    >
                </div>
            </div>

            <!-- Watch Link -->
            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Watch Link URL * (Opens in new tab)</label>
                <input 
                    type="url" 
                    name="watch_link" 
                    required 
                    value="<?= htmlspecialchars($movie['watch_link']) ?>"
                    class="w-full px-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs focus:outline-none focus:border-red-500 transition"
                >
            </div>

            <div class="flex items-center gap-3 pt-3">
                <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 active:scale-95 text-white text-xs font-bold rounded-xl transition shadow-lg shadow-red-900/40 flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk text-xs"></i> Update Movie
                </button>
                <a href="movies.php" class="px-5 py-3 bg-gray-950 hover:bg-gray-800 text-gray-300 text-xs font-semibold rounded-xl border border-gray-800 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/common/bottom.php';
?>
