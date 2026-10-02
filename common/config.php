<?php
/**
 * Adept Cinema - Centralized Database Configuration & Session Management
 * Host: 127.0.0.1, User: root, Pass: root
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'adept_cinema');

// Global PDO Connection
global $pdo;

try {
    // Attempt standard MySQL PDO connection first
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 2,
    ]);
} catch (Throwable $e) {
    // Seamless fallback to SQLite PDO when local MySQL service is not running
    $dbPath = __DIR__ . '/../adept_cinema.db';
    $dsn = "sqlite:" . $dbPath;
    $pdo = new PDO($dsn, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("PRAGMA journal_mode = WAL;");
    $pdo->exec("PRAGMA foreign_keys = ON;");
}

/**
 * Auto-install / verify schema and seed initial data if needed
 */
function ensureDatabaseInitialized($pdo) {
    static $checked = false;
    if ($checked) return;
    $checked = true;

    try {
        $test = $pdo->query("SELECT 1 FROM movies LIMIT 1");
    } catch (Throwable $e) {
        // Run installer if tables do not exist
        $installScript = __DIR__ . '/../install.php';
        if (file_exists($installScript)) {
            require_once $installScript;
            if (function_exists('runInstaller')) {
                runInstaller($pdo);
            }
        }
    }
}

ensureDatabaseInitialized($pdo);

// Helper function to check if current logged in user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to get current user
function getCurrentUser($pdo) {
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT id, username, email, role, avatar, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Helper for flash messages
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
