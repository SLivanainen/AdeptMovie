<?php
/**
 * Adept Cinema - Database Schema & Seed Data Installer
 */

if (file_exists(__DIR__ . '/common/config.php')) {
    require_once __DIR__ . '/common/config.php';
}

function runInstaller($pdo) {
    // Detect driver
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $isSqlite = ($driver === 'sqlite');

    $autoInc = $isSqlite ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT AUTO_INCREMENT PRIMARY KEY";
    $textType = "TEXT";
    $timestampType = $isSqlite ? "DATETIME DEFAULT CURRENT_TIMESTAMP" : "DATETIME DEFAULT CURRENT_TIMESTAMP";

    // 1. Categories Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id $autoInc,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL,
        display_order INT DEFAULT 0,
        created_at $timestampType
    );");

    // 2. Movies Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS movies (
        id $autoInc,
        title VARCHAR(255) NOT NULL,
        category_id INT NOT NULL,
        poster_url $textType NOT NULL,
        backdrop_url $textType,
        description $textType NOT NULL,
        rating DECIMAL(3, 1) DEFAULT 7.5,
        release_year INT NOT NULL,
        duration VARCHAR(50) DEFAULT '2h 10m',
        watch_link $textType NOT NULL,
        featured INT DEFAULT 0,
        created_at $timestampType
    );");

    // 3. Banners Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS banners (
        id $autoInc,
        title VARCHAR(255) NOT NULL,
        tagline VARCHAR(255),
        image_url $textType NOT NULL,
        movie_id INT NULL,
        button_link $textType,
        is_active INT DEFAULT 1,
        created_at $timestampType
    );");

    // 4. Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id $autoInc,
        username VARCHAR(100) NOT NULL UNIQUE,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'user',
        avatar VARCHAR(255),
        created_at $timestampType
    );");

    // 5. Watchlist Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS watchlist (
        id $autoInc,
        user_id INT NOT NULL,
        movie_id INT NOT NULL,
        created_at $timestampType
    );");

    // Seed Categories if empty
    $catCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if ($catCount == 0) {
        $categories = [
            ['Trending Now', 'trending-now', 1],
            ['Action & Thrillers', 'action-thrillers', 2],
            ['Sci-Fi & Cyberpunk', 'sci-fi-cyberpunk', 3],
            ['Top Rated Masterpieces', 'top-rated', 4],
            ['Anime & Animation', 'anime-animation', 5],
            ['Horror & Suspense', 'horror-suspense', 6]
        ];
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, display_order) VALUES (?, ?, ?)");
        foreach ($categories as $cat) {
            $stmt->execute($cat);
        }
    }

    // Seed Movies if empty
    $movieCount = $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
    if ($movieCount == 0) {
        $movies = [
            // Category 1: Trending Now
            [
                'Cyber Heist 2099',
                1,
                'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=1200&auto=format&fit=crop&q=80',
                'In Neo-Shibuya, a syndicate of rogue memory hackers attempt to breach the world’s most encrypted biometric vault before the neural grid locks down.',
                8.9,
                2025,
                '2h 18m',
                'https://www.youtube.com/watch?v=YoHD9XEInc0',
                1
            ],
            [
                'Dune: Prophecy of Sand',
                1,
                'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=1200&auto=format&fit=crop&q=80',
                'Beneath the scorching dunes of Arrakis, ancient secrets and forbidden spice veins awaken a forgotten lineage destined to alter the empire.',
                9.1,
                2024,
                '2h 45m',
                'https://www.youtube.com/watch?v=Way9Dexny3w',
                1
            ],
            [
                'Shadow Protocol',
                1,
                'https://images.unsplash.com/photo-1485846234645-a62644f84728?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=1200&auto=format&fit=crop&q=80',
                'An elite black-ops operative is framed for an orbital weapons malfunction and must evade both international agencies and covert rogue AI.',
                8.4,
                2024,
                '1h 58m',
                'https://www.youtube.com/watch?v=LdOM0x0XDMo',
                0
            ],
            [
                'Midnight Velocity',
                1,
                'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1200&auto=format&fit=crop&q=80',
                'Underground midnight street racers in Hong Kong uncover an illicit prototype hypercar battery that everyone is prepared to kill for.',
                7.8,
                2025,
                '2h 04m',
                'https://www.youtube.com/watch?v=2LqzF5WauAw',
                0
            ],

            // Category 2: Action & Thrillers
            [
                'Valkyrie Down',
                2,
                'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?w=1200&auto=format&fit=crop&q=80',
                'When a stealth dropship crashes deep behind hostile Arctic perimeter lines, surviving crew members must battle lethal elements and specialized mercenaries.',
                8.3,
                2024,
                '2h 12m',
                'https://www.youtube.com/watch?v=YoHD9XEInc0',
                0
            ],
            [
                'Apex Predator: Hunted',
                2,
                'https://images.unsplash.com/photo-1533488765986-dfa2a9939acd?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=1200&auto=format&fit=crop&q=80',
                'Trapped in an uncharted jungle archipelago, an elite survivalist becomes both hunter and prey against genetically enhanced apex creatures.',
                7.9,
                2023,
                '1h 52m',
                'https://www.youtube.com/watch?v=Way9Dexny3w',
                0
            ],
            [
                'Red Line Extraction',
                2,
                'https://images.unsplash.com/photo-1509281373149-e957c6296406?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1478760329108-5c3ed9d495a0?w=1200&auto=format&fit=crop&q=80',
                'A battle-tested tactical convoy must navigate through enemy-controlled city ruins to deliver a vital whistleblower to the demilitarized zone.',
                8.1,
                2024,
                '2h 05m',
                'https://www.youtube.com/watch?v=LdOM0x0XDMo',
                0
            ],

            // Category 3: Sci-Fi & Cyberpunk
            [
                'Quantum Paradox',
                3,
                'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1506703719100-a0f3a48c0f86?w=1200&auto=format&fit=crop&q=80',
                'A team of particle physicists accidentally split their research station across 4 parallel realities where different decisions triggered different apocalypses.',
                8.7,
                2025,
                '2h 20m',
                'https://www.youtube.com/watch?v=2LqzF5WauAw',
                1
            ],
            [
                'Neon Syndicate',
                3,
                'https://images.unsplash.com/photo-1508739773434-c26b3d09e071?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1514565131-fce0801e5785?w=1200&auto=format&fit=crop&q=80',
                'In 2110 Megacity Prime, synthetic android detective Kazuo investigates a series of murders targeting cyborg engineers with cybernetic neuro-cores.',
                8.6,
                2024,
                '2h 08m',
                'https://www.youtube.com/watch?v=YoHD9XEInc0',
                0
            ],
            [
                'Solaris Awakening',
                3,
                'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?w=1200&auto=format&fit=crop&q=80',
                'Deep space terraformers discover a sentient stellar entity orbiting a dying star that communicates through human memories and dreams.',
                8.2,
                2023,
                '2h 15m',
                'https://www.youtube.com/watch?v=Way9Dexny3w',
                0
            ],

            // Category 4: Top Rated Masterpieces
            [
                'The Last Sovereign',
                4,
                'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=1200&auto=format&fit=crop&q=80',
                'A gripping historical epic chronicling the rise and collapse of a renaissance kingdom caught between naval powers and political turmoil.',
                9.4,
                2023,
                '3h 10m',
                'https://www.youtube.com/watch?v=YoHD9XEInc0',
                1
            ],
            [
                'Echoes of Eternity',
                4,
                'https://images.unsplash.com/photo-1518709766631-a6a7f45921c3?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=1200&auto=format&fit=crop&q=80',
                'An emotionally riveting saga of an interstellar explorer returning to an Earth where 300 years have passed in a matter of months.',
                9.2,
                2024,
                '2h 35m',
                'https://www.youtube.com/watch?v=Way9Dexny3w',
                0
            ],
            [
                'Requiem for Venice',
                4,
                'https://images.unsplash.com/photo-1516483638261-f4dbaf036963?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200&auto=format&fit=crop&q=80',
                'A master violinist uncovers lost baroque compositions that possess mathematical frequencies capable of unravelling historical mysteries.',
                9.0,
                2024,
                '2h 02m',
                'https://www.youtube.com/watch?v=LdOM0x0XDMo',
                0
            ],

            // Category 5: Anime & Animation
            [
                'Spirit Blade: Celestial War',
                5,
                'https://images.unsplash.com/photo-1563089145-599997674d42?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?w=1200&auto=format&fit=crop&q=80',
                'A young spirit guardian must awaken five sacred celestial swords to protect the floating realm of Aethelgard from the Shadow Emperor.',
                8.8,
                2025,
                '1h 48m',
                'https://www.youtube.com/watch?v=2LqzF5WauAw',
                1
            ],
            [
                'Neon Dreamer: Neo Tokyo',
                5,
                'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1514565131-fce0801e5785?w=1200&auto=format&fit=crop&q=80',
                'A girl who can enter cyberspace through lucid dreams discovers an abandoned digital wonderland harbouring memories of forgotten humans.',
                8.5,
                2024,
                '1h 55m',
                'https://www.youtube.com/watch?v=YoHD9XEInc0',
                0
            ],

            // Category 6: Horror & Suspense
            [
                'The Whispering Abyss',
                6,
                'https://images.unsplash.com/photo-1509248961158-e54f6934749c?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1509248961158-e54f6934749c?w=1200&auto=format&fit=crop&q=80',
                'A deep-sea submersible crew discovers a mile-wide subterranean cathedral beneath the Mariana Trench that emits an eerie rhythmic pulse.',
                8.0,
                2024,
                '1h 50m',
                'https://www.youtube.com/watch?v=Way9Dexny3w',
                0
            ],
            [
                'Asylum 13: Protocol',
                6,
                'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1478760329108-5c3ed9d495a0?w=1200&auto=format&fit=crop&q=80',
                'An investigative journalist spends a night in an abandoned high-security asylum only to find the original patient experiments never actually ended.',
                7.7,
                2023,
                '1h 44m',
                'https://www.youtube.com/watch?v=LdOM0x0XDMo',
                0
            ]
        ];

        $stmt = $pdo->prepare("INSERT INTO movies (title, category_id, poster_url, backdrop_url, description, rating, release_year, duration, watch_link, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($movies as $m) {
            $stmt->execute($m);
        }
    }

    // Seed Banners if empty
    $bannerCount = $pdo->query("SELECT COUNT(*) FROM banners")->fetchColumn();
    if ($bannerCount == 0) {
        $banners = [
            [
                'Cyber Heist 2099',
                'In Neo-Shibuya, the ultimate biometric heist begins tonight.',
                'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=1600&auto=format&fit=crop&q=80',
                1,
                'movie_details.php?id=1',
                1
            ],
            [
                'Dune: Prophecy of Sand',
                'Fear is the mind killer. The desert claims its reckoning.',
                'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=1600&auto=format&fit=crop&q=80',
                2,
                'movie_details.php?id=2',
                1
            ],
            [
                'Quantum Paradox',
                'Four realities. One surviving timeline. Choose wisely.',
                'https://images.unsplash.com/photo-1506703719100-a0f3a48c0f86?w=1600&auto=format&fit=crop&q=80',
                8,
                'movie_details.php?id=8',
                1
            ],
            [
                'Spirit Blade: Celestial War',
                'Awaken the swords of eternity before the darkness falls.',
                'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?w=1600&auto=format&fit=crop&q=80',
                14,
                'movie_details.php?id=14',
                1
            ]
        ];
        $stmt = $pdo->prepare("INSERT INTO banners (title, tagline, image_url, movie_id, button_link, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($banners as $b) {
            $stmt->execute($b);
        }
    }

    // Seed Users if empty
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($userCount == 0) {
        $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $userPass = password_hash('user123', PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, avatar) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@adeptcinema.com', $adminPass, 'admin', 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150']);
        $stmt->execute(['demouser', 'user@adeptcinema.com', $userPass, 'user', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150']);
    }

    return true;
}

// If invoked directly from web browser or CLI
if (php_sapi_name() === 'cli' || basename($_SERVER['PHP_SELF'] ?? '') === 'install.php') {
    require_once __DIR__ . '/common/config.php';
    $message = '';
    $success = false;

    $reqMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($reqMethod === 'POST' || isset($_GET['auto']) || php_sapi_name() === 'cli') {
        try {
            runInstaller($pdo);
            $success = true;
            $message = 'Database successfully installed and seeded with rich movie data!';
        } catch (Throwable $e) {
            $message = 'Installation failed: ' . $e->getMessage();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Database Setup - Adept Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { user-select: none; -webkit-user-select: none; }
        input, textarea { user-select: text; }
    </style>
</head>
<body class="bg-gray-950 text-white min-h-screen flex flex-col justify-center items-center p-4">
    <div class="max-w-md w-full bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-2xl text-center">
        <div class="w-16 h-16 bg-red-600/20 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
            <i class="fa-solid fa-database"></i>
        </div>
        <h1 class="text-2xl font-bold tracking-tight mb-2">Adept Cinema Setup</h1>
        <p class="text-gray-400 text-sm mb-6">Initialize and seed the database with categories, high-resolution posters, trailers, and default credentials.</p>

        <?php if (!empty($message)): ?>
            <div class="p-4 mb-6 rounded-xl text-sm <?= $success ? 'bg-emerald-950/80 border border-emerald-800 text-emerald-300' : 'bg-red-950/80 border border-red-800 text-red-300' ?>">
                <i class="fa-solid <?= $success ? 'fa-circle-check' : 'fa-circle-exclamation' ?> mr-2"></i>
                <?= htmlspecialchars($message) ?>
            </div>
            <?php if ($success): ?>
                <div class="bg-gray-800/80 rounded-xl p-4 mb-6 text-left text-xs text-gray-300 space-y-1">
                    <p class="font-semibold text-white mb-1"><i class="fa-solid fa-key text-red-500 mr-1"></i> Default Credentials:</p>
                    <p>• Admin: <span class="text-white font-mono bg-gray-950 px-1.5 py-0.5 rounded">admin</span> / <span class="text-white font-mono bg-gray-950 px-1.5 py-0.5 rounded">admin123</span></p>
                    <p>• User: <span class="text-white font-mono bg-gray-950 px-1.5 py-0.5 rounded">demouser</span> / <span class="text-white font-mono bg-gray-950 px-1.5 py-0.5 rounded">user123</span></p>
                </div>
                <div class="flex gap-3">
                    <a href="index.php" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2.5 px-4 rounded-xl transition text-center text-sm">
                        <i class="fa-solid fa-play mr-1"></i> Go to App
                    </a>
                    <a href="admin/login.php" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-200 font-medium py-2.5 px-4 rounded-xl transition text-center text-sm">
                        <i class="fa-solid fa-shield mr-1"></i> Admin Panel
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!$success): ?>
            <form method="POST" action="install.php">
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 active:scale-95 text-white font-semibold py-3 px-6 rounded-xl transition shadow-lg shadow-red-900/40 text-sm">
                    <i class="fa-solid fa-bolt mr-2"></i> Run Installer & Seed Data
                </button>
            </form>
            <div class="mt-4">
                <a href="index.php" class="text-xs text-gray-400 hover:text-white transition">Return to Homepage</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Disable right click, text select & zoom
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('selectstart', e => {
            if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') e.preventDefault();
        });
        document.addEventListener('wheel', e => { if (e.ctrlKey) e.preventDefault(); }, { passive: false });
    </script>
</body>
</html>
<?php } ?>
