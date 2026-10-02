<?php
/**
 * Adept Cinema - Admin Hero Banner Management
 */
require_once __DIR__ . '/common/header.php';

$error = '';

// 1. Add Banner (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_banner') {
    $title = trim($_POST['title'] ?? '');
    $tagline = trim($_POST['tagline'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');
    $movieId = (int)($_POST['movie_id'] ?? 0);
    $buttonLink = trim($_POST['button_link'] ?? '');

    if (empty($title) || empty($imageUrl)) {
        $error = 'Banner Title and Image URL are required.';
    } else {
        if (empty($buttonLink) && $movieId > 0) {
            $buttonLink = 'movie_details.php?id=' . $movieId;
        }

        $stmt = $pdo->prepare("INSERT INTO banners (title, tagline, image_url, movie_id, button_link, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$title, $tagline, $imageUrl, $movieId ?: null, $buttonLink]);

        setFlash('success', 'Banner "' . htmlspecialchars($title) . '" added successfully!');
        header('Location: banners.php');
        exit;
    }
}

// 2. Toggle Status (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_banner') {
    $bannerId = (int)($_POST['banner_id'] ?? 0);
    $current = (int)($_POST['current_status'] ?? 0);
    $newStatus = $current === 1 ? 0 : 1;

    $stmt = $pdo->prepare("UPDATE banners SET is_active = ? WHERE id = ?");
    $stmt->execute([$newStatus, $bannerId]);

    setFlash('success', 'Banner status updated.');
    header('Location: banners.php');
    exit;
}

// 3. Delete Banner (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_banner') {
    $bannerId = (int)($_POST['banner_id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
    $stmt->execute([$bannerId]);

    setFlash('success', 'Banner removed.');
    header('Location: banners.php');
    exit;
}

// Fetch Movies for dropdown
$stmtMovies = $pdo->query("SELECT id, title FROM movies ORDER BY title ASC");
$allMovies = $stmtMovies->fetchAll();

// Fetch Banners
$stmtBanners = $pdo->query("SELECT b.*, m.title AS movie_title FROM banners b LEFT JOIN movies m ON b.movie_id = m.id ORDER BY b.id DESC");
$banners = $stmtBanners->fetchAll();
?>

<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-panorama text-red-600"></i> Hero Carousel Banners
            </h1>
            <p class="text-xs text-gray-400 mt-0.5">Control top full-width showcase banners on the mobile homepage.</p>
        </div>
        <a href="#add" class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 hover:bg-red-700 active:scale-95 text-white text-xs font-bold rounded-xl transition shadow">
            <i class="fa-solid fa-plus text-xs"></i> New Banner Form
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="p-3 rounded-xl bg-red-950/80 border border-red-800 text-red-300 text-xs flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation shrink-0"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- ADD BANNER FORM -->
    <div id="add" class="bg-gray-900/90 border border-gray-800 rounded-3xl p-5 sm:p-6 shadow-xl">
        <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
            <span class="w-1.5 h-4 bg-red-600 rounded-full"></span>
            Add New Featured Banner
        </h2>

        <form method="POST" action="banners.php" class="space-y-4">
            <input type="hidden" name="action" value="add_banner">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Banner Title *</label>
                    <input 
                        type="text" 
                        name="title" 
                        required 
                        placeholder="e.g. Dune: Prophecy of Sand"
                        class="w-full px-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs placeholder-gray-600 focus:outline-none focus:border-red-500 transition"
                    >
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Associated Movie (Optional)</label>
                    <select 
                        name="movie_id" 
                        class="w-full px-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs focus:outline-none focus:border-red-500 transition"
                    >
                        <option value="0">-- None / Custom Link --</option>
                        <?php foreach ($allMovies as $m): ?>
                            <option value="<?= (int)$m['id'] ?>"><?= htmlspecialchars($m['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Tagline / Catchphrase</label>
                <input 
                    type="text" 
                    name="tagline" 
                    placeholder="e.g. In Neo-Shibuya, the ultimate biometric heist begins tonight."
                    class="w-full px-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs placeholder-gray-600 focus:outline-none focus:border-red-500 transition"
                >
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Wide Banner Image URL (Landscape 16:9 or 21:9) *</label>
                <input 
                    type="url" 
                    name="image_url" 
                    id="banner_url_input"
                    required 
                    placeholder="https://images.unsplash.com/..."
                    class="w-full px-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs placeholder-gray-600 focus:outline-none focus:border-red-500 transition"
                >
                <div class="mt-2 flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                    <span class="text-[10px] text-gray-500 shrink-0 font-medium">Sample Presets:</span>
                    <button type="button" onclick="setBanner('https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=1600')" class="px-2 py-1 bg-gray-950 border border-gray-800 rounded text-[10px] text-gray-400 hover:text-white shrink-0">Cyber Neon</button>
                    <button type="button" onclick="setBanner('https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=1600')" class="px-2 py-1 bg-gray-950 border border-gray-800 rounded text-[10px] text-gray-400 hover:text-white shrink-0">Dune Desert</button>
                    <button type="button" onclick="setBanner('https://images.unsplash.com/photo-1506703719100-a0f3a48c0f86?w=1600')" class="px-2 py-1 bg-gray-950 border border-gray-800 rounded text-[10px] text-gray-400 hover:text-white shrink-0">Cosmic Galaxy</button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Custom Action Button Link (Optional override)</label>
                <input 
                    type="text" 
                    name="button_link" 
                    placeholder="e.g. movie_details.php?id=1"
                    class="w-full px-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs placeholder-gray-600 focus:outline-none focus:border-red-500 transition"
                >
            </div>

            <button type="submit" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 active:scale-95 text-white text-xs font-bold rounded-xl transition shadow flex items-center gap-2">
                <i class="fa-solid fa-plus text-xs"></i> Publish Banner
            </button>
        </form>
    </div>

    <!-- BANNERS LIST -->
    <div class="bg-gray-900/90 border border-gray-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
        <h2 class="text-base font-bold text-white flex items-center gap-2">
            <span class="w-1.5 h-4 bg-red-600 rounded-full"></span>
            Configured Banners (<?= count($banners) ?>)
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($banners as $b): ?>
                <div class="bg-gray-950 border border-gray-800 rounded-2xl overflow-hidden flex flex-col justify-between group">
                    <div class="relative aspect-[16/9] w-full bg-gray-900">
                        <img src="<?= htmlspecialchars($b['image_url']) ?>" alt="banner" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-transparent to-transparent"></div>
                        <div class="absolute top-2 left-2">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $b['is_active'] ? 'bg-emerald-950/90 text-emerald-400 border border-emerald-800' : 'bg-gray-900/90 text-gray-400 border border-gray-700' ?>">
                                <?= $b['is_active'] ? '● Active' : '○ Inactive' ?>
                            </span>
                        </div>
                        <div class="absolute bottom-2 left-3 right-3">
                            <h3 class="text-sm font-bold text-white drop-shadow truncate"><?= htmlspecialchars($b['title']) ?></h3>
                            <?php if (!empty($b['tagline'])): ?>
                                <p class="text-[11px] text-gray-300 drop-shadow line-clamp-1"><?= htmlspecialchars($b['tagline']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="p-3 flex items-center justify-between gap-2 border-t border-gray-900">
                        <span class="text-[10px] text-gray-400 truncate">
                            Linked: <span class="text-red-400"><?= htmlspecialchars($b['movie_title'] ?: ($b['button_link'] ?: 'None')) ?></span>
                        </span>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <!-- Toggle Active Status Form -->
                            <form method="POST" action="banners.php" class="inline">
                                <input type="hidden" name="action" value="toggle_banner">
                                <input type="hidden" name="banner_id" value="<?= (int)$b['id'] ?>">
                                <input type="hidden" name="current_status" value="<?= (int)$b['is_active'] ?>">
                                <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-semibold transition <?= $b['is_active'] ? 'bg-amber-950/60 hover:bg-amber-900 text-amber-400 border border-amber-900/40' : 'bg-emerald-950/60 hover:bg-emerald-900 text-emerald-400 border border-emerald-900/40' ?>">
                                    <?= $b['is_active'] ? 'Pause' : 'Activate' ?>
                                </button>
                            </form>

                            <!-- Delete Form -->
                            <form method="POST" action="banners.php" onsubmit="return confirm('Delete this banner?');" class="inline">
                                <input type="hidden" name="action" value="delete_banner">
                                <input type="hidden" name="banner_id" value="<?= (int)$b['id'] ?>">
                                <button type="submit" class="p-1 px-2 rounded-lg bg-red-950/60 hover:bg-red-900 text-red-400 text-xs transition border border-red-900/40">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    function setBanner(url) {
        document.getElementById('banner_url_input').value = url;
    }
</script>

<?php
require_once __DIR__ . '/common/bottom.php';
?>
