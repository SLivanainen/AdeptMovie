<?php
/**
 * Adept Cinema - Mobile Streaming Homepage (Netflix Style)
 */
require_once __DIR__ . '/common/header.php';

// Fetch active banners
$stmtBanners = $pdo->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY id ASC");
$banners = $stmtBanners->fetchAll();

// Fetch categories with at least one movie
$stmtCats = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC, id ASC");
$categories = $stmtCats->fetchAll();
?>

<!-- Hero Banner Slider -->
<?php if (!empty($banners)): ?>
<section class="relative w-full overflow-hidden bg-gray-950 aspect-[16/10] sm:aspect-[21/9] max-h-[520px]">
    <div id="banner-slider" class="relative w-full h-full">
        <?php foreach ($banners as $index => $banner): ?>
            <div class="banner-slide absolute inset-0 transition-opacity duration-700 ease-in-out <?= $index === 0 ? 'opacity-100 z-10 pointer-events-auto' : 'opacity-0 z-0 pointer-events-none' ?>" data-index="<?= $index ?>">
                <!-- Background Image -->
                <img src="<?= htmlspecialchars($banner['image_url']) ?>" alt="<?= htmlspecialchars($banner['title']) ?>" class="w-full h-full object-cover object-center transform scale-105 transition-transform duration-1000">
                
                <!-- Netflix-style Vignette Gradient Overlays -->
                <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/40 to-transparent"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-gray-950/80 via-transparent to-transparent"></div>

                <!-- Slide Content -->
                <div class="absolute bottom-6 sm:bottom-12 left-4 right-4 sm:left-8 max-w-xl z-20">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-red-600/90 text-[10px] font-bold tracking-wider text-white uppercase mb-2 shadow-lg shadow-red-900/40">
                        <i class="fa-solid fa-fire text-amber-300 text-[10px]"></i> Featured Premiere
                    </span>
                    <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-white drop-shadow-md line-clamp-1">
                        <?= htmlspecialchars($banner['title']) ?>
                    </h1>
                    <?php if (!empty($banner['tagline'])): ?>
                        <p class="text-xs sm:text-sm text-gray-300 mt-1 line-clamp-2 drop-shadow">
                            <?= htmlspecialchars($banner['tagline']) ?>
                        </p>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2.5 mt-3.5">
                        <?php 
                        $watchTarget = 'movie_details.php?id=' . ($banner['movie_id'] ?: 1);
                        if (!empty($banner['button_link'])) {
                            $watchTarget = $banner['button_link'];
                        }
                        ?>
                        <a href="<?= htmlspecialchars($watchTarget) ?>" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 active:scale-95 text-white font-semibold text-xs sm:text-sm transition shadow-lg shadow-red-900/50">
                            <i class="fa-solid fa-play text-xs"></i> Watch Now
                        </a>
                        <?php if ($banner['movie_id']): ?>
                            <a href="movie_details.php?id=<?= (int)$banner['movie_id'] ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-800/80 hover:bg-gray-700/80 active:scale-95 text-gray-200 font-medium text-xs sm:text-sm backdrop-blur-md border border-gray-700/60 transition">
                                <i class="fa-solid fa-circle-info text-xs"></i> Details
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Carousel Indicator Dots -->
    <?php if (count($banners) > 1): ?>
        <div class="absolute bottom-2 right-4 z-30 flex items-center gap-1.5">
            <?php foreach ($banners as $idx => $b): ?>
                <button type="button" onclick="goToSlide(<?= $idx ?>)" class="slider-dot w-2 h-2 rounded-full transition-all duration-300 <?= $idx === 0 ? 'bg-red-500 w-5' : 'bg-gray-600 hover:bg-gray-400' ?>" aria-label="Slide <?= $idx + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Banner Slider Vanilla JS (NO AJAX) -->
<script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.banner-slide');
    const dots = document.querySelectorAll('.slider-dot');
    const totalSlides = slides.length;

    function goToSlide(index) {
        if (totalSlides <= 1) return;
        slides.forEach((slide, i) => {
            if (i === index) {
                slide.classList.remove('opacity-0', 'pointer-events-none', 'z-0');
                slide.classList.add('opacity-100', 'pointer-events-auto', 'z-10');
            } else {
                slide.classList.remove('opacity-100', 'pointer-events-auto', 'z-10');
                slide.classList.add('opacity-0', 'pointer-events-none', 'z-0');
            }
        });
        dots.forEach((dot, i) => {
            if (i === index) {
                dot.classList.add('bg-red-500', 'w-5');
                dot.classList.remove('bg-gray-600', 'w-2');
            } else {
                dot.classList.remove('bg-red-500', 'w-5');
                dot.classList.add('bg-gray-600', 'w-2');
            }
        });
        currentSlide = index;
    }

    if (totalSlides > 1) {
        setInterval(() => {
            let next = (currentSlide + 1) % totalSlides;
            goToSlide(next);
        }, 5000);
    }
</script>
<?php endif; ?>

<!-- Quick Category Navigation Pill Bar -->
<section class="px-4 py-3 bg-gray-950/80 sticky top-[57px] z-30 backdrop-blur-md border-b border-gray-900/60 overflow-x-auto no-scrollbar flex items-center gap-2">
    <a href="categories_page.php" class="shrink-0 px-3 py-1 rounded-full bg-red-600/20 border border-red-500/40 text-red-400 text-xs font-semibold flex items-center gap-1.5">
        <i class="fa-solid fa-list-ul text-[10px]"></i> All Genres
    </a>
    <?php foreach ($categories as $cat): ?>
        <a href="categories_page.php?category_id=<?= (int)$cat['id'] ?>" class="shrink-0 px-3 py-1 rounded-full bg-gray-900 hover:bg-gray-800 border border-gray-800 text-gray-300 hover:text-white text-xs font-medium transition">
            <?= htmlspecialchars($cat['name']) ?>
        </a>
    <?php endforeach; ?>
</section>

<!-- Category Rows with Horizontally Scrollable Posters -->
<div class="px-4 py-4 space-y-7">
    <?php 
    $stmtMoviesByCat = $pdo->prepare("SELECT * FROM movies WHERE category_id = ? ORDER BY id DESC");
    foreach ($categories as $category):
        $stmtMoviesByCat->execute([$category['id']]);
        $catMovies = $stmtMoviesByCat->fetchAll();
        if (empty($catMovies)) continue;
    ?>
        <section class="relative">
            <!-- Row Header -->
            <div class="flex items-center justify-between mb-2.5">
                <a href="categories_page.php?category_id=<?= (int)$category['id'] ?>" class="group inline-flex items-center gap-2">
                    <span class="w-1.5 h-4 bg-red-600 rounded-full"></span>
                    <h2 class="text-base sm:text-lg font-bold text-white group-hover:text-red-400 transition tracking-tight">
                        <?= htmlspecialchars($category['name']) ?>
                    </h2>
                    <i class="fa-solid fa-chevron-right text-[11px] text-gray-500 group-hover:text-red-400 transition transform group-hover:translate-x-0.5"></i>
                </a>
                <a href="categories_page.php?category_id=<?= (int)$category['id'] ?>" class="text-[11px] font-semibold text-gray-400 hover:text-red-400 transition">
                    See All
                </a>
            </div>

            <!-- Horizontal Scrollable Poster Carousel -->
            <div class="flex items-center gap-3 overflow-x-auto no-scrollbar scroll-smooth py-1 -mx-4 px-4">
                <?php foreach ($catMovies as $movie): ?>
                    <a href="movie_details.php?id=<?= (int)$movie['id'] ?>" class="shrink-0 w-32 sm:w-40 group flex flex-col focus:outline-none">
                        <!-- Poster Container -->
                        <div class="relative aspect-[2/3] w-full rounded-xl overflow-hidden bg-gray-900 border border-gray-800 shadow-md group-hover:border-red-500/60 group-hover:shadow-red-950/50 group-hover:scale-[1.03] transition-all duration-300">
                            <img src="<?= htmlspecialchars($movie['poster_url']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>" loading="lazy" class="w-full h-full object-cover group-hover:brightness-105 transition duration-300">

                            <!-- Top Rating Badge -->
                            <div class="absolute top-1.5 right-1.5 px-1.5 py-0.5 rounded-md bg-gray-950/85 backdrop-blur-md border border-gray-800 text-[10px] font-bold text-amber-400 flex items-center gap-1 shadow">
                                <i class="fa-solid fa-star text-[9px]"></i>
                                <?= number_format($movie['rating'], 1) ?>
                            </div>

                            <!-- Year & Play Indicator on Hover -->
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-2">
                                <span class="w-7 h-7 rounded-full bg-red-600 text-white flex items-center justify-center shadow-lg text-xs">
                                    <i class="fa-solid fa-play ml-0.5"></i>
                                </span>
                            </div>
                        </div>

                        <!-- Movie Title & Release Year -->
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
        </section>
    <?php endforeach; ?>
</div>

<?php
require_once __DIR__ . '/common/bottom.php';
?>
