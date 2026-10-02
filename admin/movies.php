<?php
/**
 * Adept Cinema - Admin Movies Management (Add & List Movies)
 */
require_once __DIR__ . '/common/header.php';

$error = '';
$success = '';

// Handle Adding New Movie (Standard PHP Form POST, NO AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_movie') {
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
        $error = 'Please fill in all required fields (Title, Category, Poster URL, Description, Watch Link).';
    } else {
        if (empty($backdropUrl)) {
            $backdropUrl = $posterUrl;
        }

        $stmt = $pdo->prepare("
            INSERT INTO movies (title, category_id, poster_url, backdrop_url, description, rating, release_year, duration, watch_link)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $title,
            $categoryId,
            $posterUrl,
            $backdropUrl,
            $description,
            $rating,
            $releaseYear,
            $duration,
            $watchLink
        ]);

        setFlash('success', 'Movie "' . htmlspecialchars($title) . '" has been added successfully!');
        header('Location: movies.php');
        exit;
    }
}

// Fetch Categories for the dropdown
$stmtCats = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC, name ASC");
$categories = $stmtCats->fetchAll();

// Fetch All Movies for the List
$filterCat = isset($_GET['filter_cat']) ? (int)$_GET['filter_cat'] : 0;
$searchQ = isset($_GET['q']) ? trim($_GET['q']) : '';

$listSql = "SELECT m.*, c.name AS category_name FROM movies m LEFT JOIN categories c ON m.category_id = c.id WHERE 1=1";
$params = [];

if ($filterCat > 0) {
    $listSql .= " AND m.category_id = ?";
    $params[] = $filterCat;
}
if ($searchQ !== '') {
    $listSql .= " AND (m.title LIKE ? OR m.description LIKE ?)";
    $params[] = "%$searchQ%";
    $params[] = "%$searchQ%";
}

$listSql .= " ORDER BY m.id DESC";
$stmtMovies = $pdo->prepare($listSql);
$stmtMovies->execute($params);
$moviesList = $stmtMovies->fetchAll();
?>

<div class="space-y-8">
    <!-- Page Title & Section Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-clapperboard text-red-600"></i> Movie Catalog Management
            </h1>
            <p class="text-xs text-gray-400 mt-0.5">Add new titles, edit metadata, configure stream links, or remove entries.</p>
        </div>
        <a href="#add" class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 hover:bg-red-700 active:scale-95 text-white text-xs font-bold rounded-xl transition shadow">
            <i class="fa-solid fa-plus text-xs"></i> Add Movie Form
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="p-3 rounded-xl bg-red-950/80 border border-red-800 text-red-300 text-xs flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation shrink-0"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- ADD MOVIE FORM CARD -->
    <div id="add" class="bg-gray-900/90 border border-gray-800 rounded-3xl p-5 sm:p-6 shadow-xl">
        <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
            <span class="w-1.5 h-4 bg-red-600 rounded-full"></span>
            Add New Movie
        </h2>

        <!-- Standard PHP Form Submission (NO AJAX) -->
        <form method="POST" action="movies.php" class="space-y-4">
            <input type="hidden" name="action" value="add_movie">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Title -->
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Movie Title *</label>
                    <input 
                        type="text" 
                        name="title" 
                        required 
                        placeholder="e.g. Interstellar Odyssey"
                        class="w-full px-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs placeholder-gray-600 focus:outline-none focus:border-red-500 transition"
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
                        <option value="">-- Choose Category --</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Poster URL & Quick Presets -->
            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Poster Image URL *</label>
                <input 
                    type="url" 
                    name="poster_url" 
                    id="poster_url_input"
                    required 
                    placeholder="https://images.unsplash.com/..."
                    class="w-full px-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs placeholder-gray-600 focus:outline-none focus:border-red-500 transition"
                >
                <!-- Quick sample poster pickers -->
                <div class="mt-2 flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                    <span class="text-[10px] text-gray-500 shrink-0 font-medium">Quick Presets:</span>
                    <button type="button" onclick="setPoster('https://images.unsplash.com/photo-1534447677768-be436bb09401?w=500')" class="px-2 py-1 bg-gray-950 border border-gray-800 rounded text-[10px] text-gray-400 hover:text-white shrink-0">Sci-Fi Desert</button>
                    <button type="button" onclick="setPoster('https://images.unsplash.com/photo-1578632767115-351597cf2477?w=500')" class="px-2 py-1 bg-gray-950 border border-gray-800 rounded text-[10px] text-gray-400 hover:text-white shrink-0">Cyber City</button>
                    <button type="button" onclick="setPoster('https://images.unsplash.com/photo-1563089145-599997674d42?w=500')" class="px-2 py-1 bg-gray-950 border border-gray-800 rounded text-[10px] text-gray-400 hover:text-white shrink-0">Fantasy Art</button>
                    <button type="button" onclick="setPoster('https://images.unsplash.com/photo-1509281373149-e957c6296406?w=500')" class="px-2 py-1 bg-gray-950 border border-gray-800 rounded text-[10px] text-gray-400 hover:text-white shrink-0">Action Thriller</button>
                </div>
            </div>

            <!-- Backdrop URL (Optional) -->
            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Backdrop Banner URL (Optional)</label>
                <input 
                    type="url" 
                    name="backdrop_url" 
                    placeholder="https://images.unsplash.com/... (leave blank to mirror poster)"
                    class="w-full px-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs placeholder-gray-600 focus:outline-none focus:border-red-500 transition"
                >
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Full Description / Synopsis *</label>
                <textarea 
                    name="description" 
                    rows="3" 
                    required 
                    placeholder="Enter engaging movie storyline and synopsis..."
                    class="w-full px-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs placeholder-gray-600 focus:outline-none focus:border-red-500 transition"
                ></textarea>
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
                        value="8.5" 
                        class="w-full px-3 py-2 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs focus:outline-none focus:border-red-500 transition"
                    >
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Release Year</label>
                    <input 
                        type="number" 
                        name="release_year" 
                        min="1950" 
                        max="2035" 
                        value="<?= date('Y') ?>" 
                        class="w-full px-3 py-2 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs focus:outline-none focus:border-red-500 transition"
                    >
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Duration</label>
                    <input 
                        type="text" 
                        name="duration" 
                        value="2h 15m" 
                        placeholder="e.g. 2h 15m"
                        class="w-full px-3 py-2 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs focus:outline-none focus:border-red-500 transition"
                    >
                </div>
            </div>

            <!-- Watch Link -->
            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Watch Link URL * (Opens in new tab)</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500 text-xs">
                        <i class="fa-solid fa-play"></i>
                    </span>
                    <input 
                        type="url" 
                        name="watch_link" 
                        required 
                        value="https://www.youtube.com/watch?v=YoHD9XEInc0"
                        placeholder="https://www.youtube.com/watch?v=..."
                        class="w-full pl-9 pr-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs focus:outline-none focus:border-red-500 transition"
                    >
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-red-600 hover:bg-red-700 active:scale-95 text-white text-xs font-bold rounded-xl transition shadow-lg shadow-red-900/40 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up text-xs"></i> Save Movie to Catalog
                </button>
            </div>
        </form>
    </div>

    <!-- MOVIE LIST TABLE / SECTION -->
    <div class="bg-gray-900/90 border border-gray-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="w-1.5 h-4 bg-red-600 rounded-full"></span>
                    Existing Movies List (<?= count($moviesList) ?>)
                </h2>
            </div>

            <!-- Filter Controls (Traditional GET Form) -->
            <form method="GET" action="movies.php" class="flex items-center gap-2">
                <input 
                    type="text" 
                    name="q" 
                    value="<?= htmlspecialchars($searchQ) ?>" 
                    placeholder="Search titles..." 
                    class="px-3 py-1.5 bg-gray-950 border border-gray-800 rounded-xl text-xs text-white placeholder-gray-600 focus:outline-none focus:border-red-500"
                >
                <select 
                    name="filter_cat" 
                    onchange="this.form.submit()" 
                    class="px-2.5 py-1.5 bg-gray-950 border border-gray-800 rounded-xl text-xs text-white focus:outline-none focus:border-red-500"
                >
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $filterCat === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-xl text-xs font-medium">
                    Filter
                </button>
            </form>
        </div>

        <!-- Responsive Movies Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-800 text-gray-400 uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-2">Movie</th>
                        <th class="py-3 px-2">Category</th>
                        <th class="py-3 px-2">Rating</th>
                        <th class="py-3 px-2">Year</th>
                        <th class="py-3 px-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/80">
                    <?php if (!empty($moviesList)): ?>
                        <?php foreach ($moviesList as $m): ?>
                            <tr class="hover:bg-gray-950/40 transition">
                                <!-- Movie Poster & Title -->
                                <td class="py-3 px-2">
                                    <div class="flex items-center gap-3">
                                        <img src="<?= htmlspecialchars($m['poster_url']) ?>" alt="poster" class="w-10 aspect-[2/3] object-cover rounded-lg bg-gray-950 border border-gray-800 shrink-0">
                                        <div class="min-w-0">
                                            <a href="../movie_details.php?id=<?= (int)$m['id'] ?>" target="_blank" class="font-bold text-white hover:text-red-400 truncate block">
                                                <?= htmlspecialchars($m['title']) ?>
                                            </a>
                                            <span class="text-[10px] text-gray-500 line-clamp-1"><?= htmlspecialchars(substr($m['description'], 0, 60)) ?>...</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Category -->
                                <td class="py-3 px-2 text-gray-300">
                                    <span class="px-2 py-0.5 rounded-full bg-gray-950 border border-gray-800 text-[10px]">
                                        <?= htmlspecialchars($m['category_name'] ?: 'None') ?>
                                    </span>
                                </td>

                                <!-- Rating -->
                                <td class="py-3 px-2">
                                    <span class="text-amber-400 font-bold">
                                        ★ <?= number_format($m['rating'], 1) ?>
                                    </span>
                                </td>

                                <!-- Year -->
                                <td class="py-3 px-2 text-gray-400">
                                    <?= htmlspecialchars($m['release_year']) ?>
                                </td>

                                <!-- Actions -->
                                <td class="py-3 px-2 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- Edit Action -->
                                        <a href="manage_movie.php?action=edit&id=<?= (int)$m['id'] ?>" class="px-2 py-1 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-medium transition" title="Edit">
                                            <i class="fa-solid fa-pen text-[10px]"></i>
                                        </a>

                                        <!-- Preview Live -->
                                        <a href="../movie_details.php?id=<?= (int)$m['id'] ?>" target="_blank" class="px-2 py-1 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-medium transition" title="View in App">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                        </a>

                                        <!-- Delete Action (Traditional POST Form with Confirm) -->
                                        <form method="POST" action="manage_movie.php" onsubmit="return confirm('Are you sure you want to delete \'<?= htmlspecialchars(addslashes($m['title'])) ?>\'?');" class="inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                                            <button type="submit" class="px-2 py-1 rounded-lg bg-red-950/60 hover:bg-red-900 border border-red-900/50 text-red-400 text-xs font-medium transition" title="Delete">
                                                <i class="fa-solid fa-trash-can text-[10px]"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500">
                                No movies found matching the criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function setPoster(url) {
        document.getElementById('poster_url_input').value = url;
    }
</script>

<?php
require_once __DIR__ . '/common/bottom.php';
?>
