<?php
/**
 * Adept Cinema - Admin Category Management
 */
require_once __DIR__ . '/common/header.php';

$error = '';

// 1. Handle Add Category (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $name = trim($_POST['name'] ?? '');
    $order = (int)($_POST['display_order'] ?? 0);

    if (empty($name)) {
        $error = 'Category name cannot be empty.';
    } else {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, display_order) VALUES (?, ?, ?)");
        $stmt->execute([$name, $slug, $order]);

        setFlash('success', 'Category "' . htmlspecialchars($name) . '" created successfully.');
        header('Location: categories.php');
        exit;
    }
}

// 2. Handle Delete Category (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_category') {
    $delId = (int)($_POST['category_id'] ?? 0);
    if ($delId > 0) {
        // Count movies in this category
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM movies WHERE category_id = ?");
        $stmtCount->execute([$delId]);
        $count = $stmtCount->fetchColumn();

        if ($count > 0) {
            $error = "Cannot delete category: It currently contains $count movie(s). Please reassign or delete those movies first.";
        } else {
            $stmtDel = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmtDel->execute([$delId]);
            setFlash('success', 'Category deleted successfully.');
            header('Location: categories.php');
            exit;
        }
    }
}

// 3. Handle Update Category (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_category') {
    $upId = (int)($_POST['category_id'] ?? 0);
    $upName = trim($_POST['name'] ?? '');
    $upOrder = (int)($_POST['display_order'] ?? 0);

    if ($upId > 0 && !empty($upName)) {
        $upSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $upName), '-'));
        $stmtUp = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, display_order = ? WHERE id = ?");
        $stmtUp->execute([$upName, $upSlug, $upOrder, $upId]);

        setFlash('success', 'Category updated successfully.');
        header('Location: categories.php');
        exit;
    }
}

// Fetch all categories with movie counts
$stmtCats = $pdo->query("
    SELECT c.*, COUNT(m.id) AS total_movies 
    FROM categories c 
    LEFT JOIN movies m ON c.id = m.category_id 
    GROUP BY c.id 
    ORDER BY c.display_order ASC, c.id ASC
");
$categories = $stmtCats->fetchAll();
?>

<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-red-600"></i> Category Taxonomy
            </h1>
            <p class="text-xs text-gray-400 mt-0.5">Define genres, display order, and organize homepage streaming rows.</p>
        </div>
        <a href="#add" class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 hover:bg-red-700 active:scale-95 text-white text-xs font-bold rounded-xl transition shadow">
            <i class="fa-solid fa-plus text-xs"></i> New Category Form
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="p-3 rounded-xl bg-red-950/80 border border-red-800 text-red-300 text-xs flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation shrink-0"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- ADD CATEGORY FORM -->
    <div id="add" class="bg-gray-900/90 border border-gray-800 rounded-3xl p-5 sm:p-6 shadow-xl">
        <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
            <span class="w-1.5 h-4 bg-red-600 rounded-full"></span>
            Add New Streaming Category
        </h2>

        <form method="POST" action="categories.php" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <input type="hidden" name="action" value="add_category">

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-300 mb-1">Category Name *</label>
                <input 
                    type="text" 
                    name="name" 
                    required 
                    placeholder="e.g. Crime Documentaries, Korean Dramas..."
                    class="w-full px-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs placeholder-gray-600 focus:outline-none focus:border-red-500 transition"
                >
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Display Order</label>
                <input 
                    type="number" 
                    name="display_order" 
                    value="10" 
                    min="0"
                    class="w-full px-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs focus:outline-none focus:border-red-500 transition"
                >
            </div>

            <div class="sm:col-span-3">
                <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 active:scale-95 text-white text-xs font-bold rounded-xl transition shadow flex items-center gap-2">
                    <i class="fa-solid fa-plus text-xs"></i> Save Category
                </button>
            </div>
        </form>
    </div>

    <!-- CATEGORIES LIST -->
    <div class="bg-gray-900/90 border border-gray-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
        <h2 class="text-base font-bold text-white flex items-center gap-2">
            <span class="w-1.5 h-4 bg-red-600 rounded-full"></span>
            Existing Categories (<?= count($categories) ?>)
        </h2>

        <div class="divide-y divide-gray-800/80">
            <?php foreach ($categories as $cat): ?>
                <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gray-950 border border-gray-800 text-red-500 flex items-center justify-center font-bold text-xs shrink-0">
                            <?= (int)$cat['display_order'] ?>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                <?= htmlspecialchars($cat['name']) ?>
                                <span class="px-2 py-0.5 rounded-full bg-gray-950 text-gray-400 text-[10px] font-semibold border border-gray-800">
                                    <?= $cat['total_movies'] ?> movie<?= $cat['total_movies'] === 1 ? '' : 's' ?>
                                </span>
                            </h3>
                            <span class="text-[10px] text-gray-500 font-mono">slug: <?= htmlspecialchars($cat['slug']) ?></span>
                        </div>
                    </div>

                    <!-- Actions & Edit Form Toggle -->
                    <div class="flex items-center gap-2 self-end sm:self-center">
                        <a href="../categories_page.php?category_id=<?= (int)$cat['id'] ?>" target="_blank" class="px-2.5 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs font-medium transition" title="View Category in App">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px] mr-1"></i> View
                        </a>

                        <!-- Delete (Traditional POST Form) -->
                        <form method="POST" action="categories.php" onsubmit="return confirm('Delete category \'<?= htmlspecialchars(addslashes($cat['name'])) ?>\'?');" class="inline">
                            <input type="hidden" name="action" value="delete_category">
                            <input type="hidden" name="category_id" value="<?= (int)$cat['id'] ?>">
                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-red-950/60 hover:bg-red-900 border border-red-900/50 text-red-400 text-xs font-medium transition" title="Delete">
                                <i class="fa-solid fa-trash-can text-[10px]"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/common/bottom.php';
?>
