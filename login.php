<?php
/**
 * Adept Cinema - User Login & Registration (Traditional PHP Form POST)
 */
require_once __DIR__ . '/common/config.php';

// If already logged in, redirect
if (isLoggedIn()) {
    header('Location: profile.php');
    exit;
}

$tab = isset($_GET['tab']) && $_GET['tab'] === 'signup' ? 'signup' : 'login';
$error = '';
$success = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'login') {
        $loginInput = trim($_POST['username_or_email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($loginInput) || empty($password)) {
            $error = 'Please fill in all credentials.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$loginInput, $loginInput]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];

                setFlash('success', 'Welcome back, ' . htmlspecialchars($user['username']) . '!');
                if ($user['role'] === 'admin') {
                    header('Location: admin/index.php');
                } else {
                    header('Location: profile.php');
                }
                exit;
            } else {
                $error = 'Invalid username/email or password.';
            }
        }
    } elseif ($action === 'signup') {
        $tab = 'signup';
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            $error = 'All fields are required.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            // Check if username or email already exists
            $chk = $pdo->prepare("SELECT 1 FROM users WHERE username = ? OR email = ?");
            $chk->execute([$username, $email]);
            if ($chk->fetch()) {
                $error = 'Username or email already in use.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $ins = $pdo->prepare("INSERT INTO users (username, email, password, role, avatar) VALUES (?, ?, ?, 'user', ?)");
                $avatar = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150';
                $ins->execute([$username, $email, $hashed, $avatar]);

                $newId = $pdo->lastInsertId();
                $_SESSION['user_id'] = $newId;
                $_SESSION['username'] = $username;
                $_SESSION['user_role'] = 'user';

                setFlash('success', 'Account created successfully! Welcome to Adept Cinema.');
                header('Location: profile.php');
                exit;
            }
        }
    }
}

require_once __DIR__ . '/common/header.php';
?>

<div class="px-4 py-8 max-w-md mx-auto">
    <!-- Brand Badge Header -->
    <div class="text-center mb-6">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-red-700 to-red-500 flex items-center justify-center mx-auto mb-3 shadow-xl shadow-red-900/50">
            <i class="fa-solid fa-play text-white text-xl ml-1"></i>
        </div>
        <h1 class="text-2xl font-black text-white tracking-tight">
            ADEPT<span class="text-red-500 ml-1">CINEMA</span>
        </h1>
        <p class="text-xs text-gray-400 mt-1">Unlimited movies, series, and high-definition streams</p>
    </div>

    <!-- Auth Card -->
    <div class="bg-gray-900/90 border border-gray-800 rounded-2xl p-6 shadow-2xl backdrop-blur-sm">
        <!-- Tab Switcher (Traditional GET link switch) -->
        <div class="flex items-center p-1 bg-gray-950 rounded-xl mb-6 border border-gray-800/80">
            <a href="login.php?tab=login" class="flex-1 py-2 text-center text-xs font-bold rounded-lg transition <?= ($tab === 'login') ? 'bg-red-600 text-white shadow-md' : 'text-gray-400 hover:text-white' ?>">
                Sign In
            </a>
            <a href="login.php?tab=signup" class="flex-1 py-2 text-center text-xs font-bold rounded-lg transition <?= ($tab === 'signup') ? 'bg-red-600 text-white shadow-md' : 'text-gray-400 hover:text-white' ?>">
                Create Account
            </a>
        </div>

        <!-- Feedback Alert -->
        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 rounded-xl bg-red-950/80 border border-red-800 text-red-300 text-xs flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation shrink-0"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="mb-4 p-3 rounded-xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs flex items-center gap-2">
                <i class="fa-solid fa-circle-check shrink-0"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <!-- FORM 1: LOGIN -->
        <?php if ($tab === 'login'): ?>
            <form method="POST" action="login.php" class="space-y-4">
                <input type="hidden" name="action" value="login">

                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1.5">Username or Email</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500 text-xs">
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <input 
                            type="text" 
                            name="username_or_email" 
                            id="login_user"
                            required
                            placeholder="e.g. demouser or admin"
                            class="w-full pl-10 pr-3 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs placeholder-gray-600 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition"
                        >
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-gray-300">Password</label>
                        <span class="text-[11px] text-gray-500">Encrypted</span>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500 text-xs">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input 
                            type="password" 
                            name="password" 
                            id="login_pass"
                            required
                            placeholder="••••••••"
                            class="w-full pl-10 pr-3 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs placeholder-gray-600 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition"
                        >
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 active:scale-[0.98] text-white text-xs font-bold rounded-xl transition shadow-lg shadow-red-900/40">
                    Sign In
                </button>
            </form>

            <!-- Quick Demo Credentials Box -->
            <div class="mt-6 pt-5 border-t border-gray-800/80">
                <span class="block text-[11px] uppercase tracking-wider text-gray-400 font-bold mb-2">
                    <i class="fa-solid fa-wand-magic-sparkles text-red-500 mr-1"></i> Quick Demo Logins
                </span>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="fillCreds('admin', 'admin123')" class="p-2 rounded-xl bg-gray-950 hover:bg-gray-800/80 border border-gray-800 text-left transition group">
                        <span class="block text-[10px] text-red-400 font-bold uppercase">Admin Account</span>
                        <span class="text-[11px] text-gray-300 font-mono">admin / admin123</span>
                    </button>
                    <button type="button" onclick="fillCreds('demouser', 'user123')" class="p-2 rounded-xl bg-gray-950 hover:bg-gray-800/80 border border-gray-800 text-left transition group">
                        <span class="block text-[10px] text-gray-400 font-bold uppercase">Member Account</span>
                        <span class="text-[11px] text-gray-300 font-mono">demouser / user123</span>
                    </button>
                </div>
            </div>

        <!-- FORM 2: SIGN UP -->
        <?php else: ?>
            <form method="POST" action="login.php?tab=signup" class="space-y-3.5">
                <input type="hidden" name="action" value="signup">

                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Username</label>
                    <input 
                        type="text" 
                        name="username" 
                        required
                        placeholder="Choose a username"
                        class="w-full px-3 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs placeholder-gray-600 focus:outline-none focus:border-red-500 transition"
                    >
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Email Address</label>
                    <input 
                        type="email" 
                        name="email" 
                        required
                        placeholder="you@example.com"
                        class="w-full px-3 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs placeholder-gray-600 focus:outline-none focus:border-red-500 transition"
                    >
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        required
                        placeholder="At least 6 characters"
                        class="w-full px-3 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs placeholder-gray-600 focus:outline-none focus:border-red-500 transition"
                    >
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Confirm Password</label>
                    <input 
                        type="password" 
                        name="confirm_password" 
                        required
                        placeholder="Re-type password"
                        class="w-full px-3 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-white text-xs placeholder-gray-600 focus:outline-none focus:border-red-500 transition"
                    >
                </div>

                <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 active:scale-[0.98] text-white text-xs font-bold rounded-xl transition shadow-lg shadow-red-900/40">
                    Create Account
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    function fillCreds(u, p) {
        const uInput = document.getElementById('login_user');
        const pInput = document.getElementById('login_pass');
        if (uInput && pInput) {
            uInput.value = u;
            pInput.value = p;
        }
    }
</script>

<?php
require_once __DIR__ . '/common/bottom.php';
?>
