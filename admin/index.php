<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../includes/supabase.php';

$title = "Admin Login — SLB-C YPSLB Gemolong";
$error = '';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$maxLoginAttempts = 5;
$lockoutSeconds = 900; // 15 menit

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if (!isset($_SESSION['login_lock_until'])) {
    $_SESSION['login_lock_until'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $currentTime = time();

    if ($_SESSION['login_lock_until'] > $currentTime) {
        $remaining = $_SESSION['login_lock_until'] - $currentTime;
        $minutes = ceil($remaining / 60);
        $error = "Terlalu banyak percobaan. Coba lagi dalam $minutes menit.";
    } else {
        $validLogin = false;
        $admin = null;

        // First try authenticating against Supabase `admin` table (bcrypt)
        $res = supabaseSelect('admin', ['username' => 'eq.' . $username]);
        if (!empty($res) && !empty($res['success']) && !empty($res['data']) && is_array($res['data'])) {
            $row = $res['data'][0];
            if (!empty($row['password']) && password_verify($password, $row['password'])) {
                $validLogin = true;
                $admin = [
                    'id' => $row['id'] ?? null,
                    'username' => $row['username'] ?? $username,
                    'nama' => $row['nama'] ?? ($row['display_name'] ?? 'Administrator'),
                    'role' => $row['role'] ?? 'admin'
                ];
            }
        }

        // Fallback to legacy constants-based auth (HMAC SHA256) for compatibility
        if (! $validLogin) {
            $passwordHash = hash_hmac('sha256', $password, ADMIN_PASSWORD_SALT);
            if ($username === ADMIN_USERNAME && hash_equals(ADMIN_PASSWORD_HASH, $passwordHash)) {
                $validLogin = true;
                $admin = [
                    'id' => 1,
                    'username' => ADMIN_USERNAME,
                    'nama' => 'Administrator',
                    'role' => 'superadmin'
                ];
            }
        }

        if ($validLogin) {
            set_admin_session($admin);
            unset($_SESSION['login_attempts'], $_SESSION['login_lock_until']);
            header('Location: dashboard.php');
            exit;
        }

        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] >= $maxLoginAttempts) {
            $_SESSION['login_lock_until'] = $currentTime + $lockoutSeconds;
            $error = 'Terlalu banyak percobaan login. Silakan coba lagi nanti.';
        } else {
            $attemptsLeft = $maxLoginAttempts - $_SESSION['login_attempts'];
            $error = "Username atau password salah. Sisa percobaan: $attemptsLeft.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
</head>
<body class="bg-[#F9F8F4] h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-lg shadow-xl p-8 border border-[#E8E4D9]">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-[#3E6B4E] rounded-full flex items-center justify-center mx-auto mb-4">
                <iconify-icon icon="lucide:lock" class="text-white text-2xl"></iconify-icon>
            </div>
            <h1 class="text-2xl font-semibold text-[#1F2D26]">Admin Panel</h1>
            <p class="text-sm text-[#5F6F65]">Masuk untuk mengelola konten website</p>
        </div>
        
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg flex items-center gap-3">
                <iconify-icon icon="lucide:alert-circle"></iconify-icon>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-xs font-bold uppercase text-[#1E40AF] mb-2">Username</label>
                <input type="text" name="username" required class="w-full px-4 py-4 bg-white border border-[#1E40AF] rounded-lg focus:outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/20 transition-colors text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-[#1E40AF] mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-4 bg-white border border-[#1E40AF] rounded-lg focus:outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/20 transition-colors text-sm">
            </div>
            <button type="submit" class="w-full bg-[#1E40AF] hover:bg-[#1e3a8a] text-white font-semibold py-4 rounded-lg transition-colors text-sm uppercase tracking-widest">
                Login
            </button>
        </form>
        
        <div class="mt-6 pt-6 border-t border-[#E8E4D9]">
            <div class="text-xs text-[#5F6F65] text-center">Masuk dengan kredensial yang valid. Jika perlu ubah password di konfigurasi.</div>
        </div>
        
        <div class="mt-8 text-center">
            <a href="../index.php" class="text-xs text-[#5F6F65] hover:text-[#3E6B4E] flex items-center justify-center gap-2">
                <iconify-icon icon="lucide:arrow-left"></iconify-icon> Kembali ke Website
            </a>
        </div>
    </div>
</body>
</html>
