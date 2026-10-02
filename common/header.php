<?php
/**
 * Adept Cinema - User Common Header
 */
require_once __DIR__ . '/config.php';
$currentUser = getCurrentUser($pdo);
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Adept Cinema - Stream Premium Movies & Series</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Mobile-app strict zero-selection & viewport lockdown */
        * {
            -webkit-tap-highlight-color: transparent;
        }
        body {
            user-select: none;
            -webkit-user-select: none;
            overscroll-behavior-y: none;
        }
        input, textarea, [contenteditable="true"] {
            user-select: text;
            -webkit-user-select: text;
        }
        /* Hide scrollbars while keeping smooth momentum scrolling */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-gray-950 text-white min-h-screen flex flex-col antialiased selection:bg-none pb-20">

    <!-- Sticky Glassmorphic Top Header -->
    <header class="sticky top-0 z-40 bg-gray-950/95 backdrop-blur-md border-b border-gray-900/80 px-4 py-3 flex items-center justify-between transition-all">
        <!-- Logo -->
        <a href="index.php" class="flex items-center gap-2 group">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-red-700 to-red-500 flex items-center justify-center shadow-lg shadow-red-900/40 group-active:scale-95 transition">
                <i class="fa-solid fa-play text-white text-xs ml-0.5"></i>
            </div>
            <div class="flex flex-col">
                <span class="font-black text-lg tracking-wider text-white font-sans uppercase leading-none">
                    ADEPT<span class="text-red-500 font-extrabold ml-1">CINEMA</span>
                </span>
                <span class="text-[9px] tracking-widest text-gray-400 font-medium uppercase">STREAM PRO</span>
            </div>
        </a>

        <!-- Top Right Actions -->
        <div class="flex items-center gap-3">
            <!-- Search Icon Link -->
            <a href="search.php" class="w-9 h-9 rounded-full bg-gray-900 border border-gray-800 flex items-center justify-center text-gray-300 hover:text-white hover:border-gray-700 active:scale-95 transition <?= ($currentPage === 'search.php') ? 'text-red-500 border-red-500/40 bg-red-950/20' : '' ?>" title="Search Movies">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </a>

            <!-- Admin shortcut (if logged in as admin) -->
            <?php if (isAdmin()): ?>
                <a href="admin/index.php" class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-950/60 border border-red-800/80 text-red-400 text-xs font-semibold hover:bg-red-900/60 transition" title="Admin Dashboard">
                    <i class="fa-solid fa-shield-halved text-[11px]"></i> Admin
                </a>
            <?php endif; ?>

            <!-- User Profile / Auth Link -->
            <?php if ($currentUser): ?>
                <a href="profile.php" class="flex items-center gap-2 group" title="<?= htmlspecialchars($currentUser['username']) ?>">
                    <img src="<?= htmlspecialchars($currentUser['avatar'] ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100') ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover border-2 <?= isAdmin() ? 'border-red-500' : 'border-gray-700' ?> group-active:scale-95 transition">
                </a>
            <?php else: ?>
                <a href="login.php" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 active:scale-95 text-white text-xs font-semibold transition shadow-md shadow-red-900/30">
                    <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i> Sign In
                </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Flash Notification Banner if any -->
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

    <!-- Main Content Container -->
    <main class="flex-1 w-full max-w-7xl mx-auto">
