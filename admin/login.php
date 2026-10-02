<?php
/**
 * Adept Cinema - Admin Login Portal
 */
require_once __DIR__ . '/../common/config.php';

// Redirect if already logged in as admin
if (isAdmin()) {
    header('Location: index.php');
    exit;
}

$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND role = 'admin'");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['user_role'] = 'admin';

            setFlash('success', 'Welcome to Adept Cinema Admin Console!');
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid admin credentials or insufficient administrator privileges.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin Sign In - Adept Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { user-select: none; -webkit-user-select: none; }
        input { user-select: text; }
    </style>
</head>
<body class="bg-gray-950 text-white min-h-screen flex flex-col items-center justify-center p-4">
    <div class="max-w-sm w-full bg-gray-900 border border-gray-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
        <!-- Shield Icon & Brand -->
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-red-600/20 text-red-500 border border-red-500/30 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-red-950/50">
                <i class="fa-solid fa-shield-halved text-2xl"></i>
            </div>
            <h1 class="text-xl font-black tracking-tight text-white uppercase">Adept Admin Portal</h1>
            <p class="text-xs text-gray-400 mt-1">Authorized personnel and content managers only</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 rounded-xl bg-red-950/80 border border-red-800 text-red-300 text-xs flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation shrink-0"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Standard PHP Form Submission (NO AJAX) -->
        <form method="POST" action="login.php" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1.5">Admin Username or Email</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500 text-xs">
                        <i class="fa-solid fa-user-shield"></i>
                    </span>
                    <input 
                        type="text" 
                        name="username" 
                        id="admin_user"
                        required 
                        placeholder="admin"
                        class="w-full pl-10 pr-3 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs placeholder-gray-600 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition"
                    >
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1.5">Password</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500 text-xs">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input 
                        type="password" 
                        name="password" 
                        id="admin_pass"
                        required 
                        placeholder="••••••••"
                        class="w-full pl-10 pr-3 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs placeholder-gray-600 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition"
                    >
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 active:scale-[0.98] text-white text-xs font-bold rounded-xl transition shadow-lg shadow-red-900/40 flex items-center justify-center gap-2">
                <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i> Enter Console
            </button>
        </form>

        <!-- Quick Demo Fill -->
        <div class="mt-6 pt-4 border-t border-gray-800/80 text-center">
            <button type="button" onclick="fillAdmin()" class="text-xs text-red-400 hover:text-red-300 transition inline-flex items-center gap-1.5 font-medium">
                <i class="fa-solid fa-key text-[10px]"></i> Use Default Admin (admin / admin123)
            </button>
            <div class="mt-3">
                <a href="../index.php" class="text-[11px] text-gray-500 hover:text-gray-300 transition">
                    ← Back to Streaming App
                </a>
            </div>
        </div>
    </div>

    <script>
        function fillAdmin() {
            document.getElementById('admin_user').value = 'admin';
            document.getElementById('admin_pass').value = 'admin123';
        }
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('selectstart', e => {
            if (e.target.tagName !== 'INPUT') e.preventDefault();
        });
    </script>
</body>
</html>
