<?php
/**
 * Adept Cinema - Admin Common Header
 */
require_once __DIR__ . '/../../common/config.php';

// Strictly enforce admin session
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

$adminPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Adept Cinema - Admin Console</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        body { user-select: none; -webkit-user-select: none; }
        input, textarea, select { user-select: text; -webkit-user-select: text; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-950 text-white min-h-screen flex flex-col antialiased pb-20">

    <!-- Admin Top Bar -->
    <header class="sticky top-0 z-40 bg-gray-950/95 backdrop-blur-md border-b border-gray-900 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="index.php" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-red-600 flex items-center justify-center shadow-lg shadow-red-950/60">
                    <i class="fa-solid fa-shield-halved text-white text-sm"></i>
                </div>
                <div class="flex flex-col">
                    <span class="font-black text-sm tracking-wide text-white font-sans uppercase">
                        ADEPT <span class="text-red-500">ADMIN</span>
                    </span>
                    <span class="text-[9px] text-gray-400 uppercase tracking-wider">Management Console</span>
                </div>
            </a>
        </div>

        <div class="flex items-center gap-2">
            <!-- View Live Site -->
            <a href="../index.php" class="px-2.5 py-1.5 rounded-lg bg-gray-900 hover:bg-gray-800 text-gray-300 hover:text-white text-xs font-medium border border-gray-800 transition flex items-center gap-1.5" title="Preview Live App">
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                <span class="hidden sm:inline">View App</span>
            </a>
            <!-- Sign Out -->
            <a href="../profile.php?action=logout" class="px-2.5 py-1.5 rounded-lg bg-red-950/50 hover:bg-red-900/60 text-red-300 text-xs font-medium border border-red-900/50 transition flex items-center gap-1.5" title="Logout">
                <i class="fa-solid fa-power-off text-[10px]"></i>
                <span class="hidden sm:inline">Logout</span>
            </a>
        </div>
    </header>

    <!-- Admin Navigation Pill Bar -->
    <div class="bg-gray-900/60 border-b border-gray-900 px-4 py-2 sticky top-[57px] z-30 backdrop-blur-md overflow-x-auto no-scrollbar flex items-center gap-2">
        <a href="index.php" class="shrink-0 px-3 py-1.5 rounded-xl text-xs font-semibold transition <?= ($adminPage === 'index.php') ? 'bg-red-600 text-white shadow' : 'bg-gray-950 text-gray-300 hover:bg-gray-800 border border-gray-800' ?>">
            <i class="fa-solid fa-chart-pie mr-1 text-[11px]"></i> Dashboard
        </a>
        <a href="movies.php" class="shrink-0 px-3 py-1.5 rounded-xl text-xs font-semibold transition <?= ($adminPage === 'movies.php' || $adminPage === 'manage_movie.php') ? 'bg-red-600 text-white shadow' : 'bg-gray-950 text-gray-300 hover:bg-gray-800 border border-gray-800' ?>">
            <i class="fa-solid fa-film mr-1 text-[11px]"></i> Movies
        </a>
        <a href="categories.php" class="shrink-0 px-3 py-1.5 rounded-xl text-xs font-semibold transition <?= ($adminPage === 'categories.php') ? 'bg-red-600 text-white shadow' : 'bg-gray-950 text-gray-300 hover:bg-gray-800 border border-gray-800' ?>">
            <i class="fa-solid fa-layer-group mr-1 text-[11px]"></i> Categories
        </a>
        <a href="banners.php" class="shrink-0 px-3 py-1.5 rounded-xl text-xs font-semibold transition <?= ($adminPage === 'banners.php') ? 'bg-red-600 text-white shadow' : 'bg-gray-950 text-gray-300 hover:bg-gray-800 border border-gray-800' ?>">
            <i class="fa-solid fa-images mr-1 text-[11px]"></i> Banners
        </a>
    </div>

    <!-- Flash message -->
    <?php $flash = getFlash(); if ($flash): ?>
        <div class="mx-4 mt-3 p-3 rounded-xl text-xs flex items-center justify-between border <?= ($flash['type'] === 'success') ? 'bg-emerald-950/80 border-emerald-800 text-emerald-300' : 'bg-red-950/80 border-red-800 text-red-300' ?>">
            <div class="flex items-center gap-2">
                <i class="fa-solid <?= ($flash['type'] === 'success') ? 'fa-circle-check text-emerald-400' : 'fa-circle-exclamation text-red-400' ?>"></i>
                <span><?= htmlspecialchars($flash['message']) ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    <?php endif; ?>

    <main class="flex-1 w-full max-w-6xl mx-auto p-4">
