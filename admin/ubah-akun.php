<?php
define('ADMIN_PAGE', true);
require_once '../includes/session.php';
require_once '../includes/db.php';
require_login();

$title = "Ubah Akun — SLB BC KARYA SEJAHTERA " . SITE_NAME;
$page_title = "Ubah Username & Password";

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $currentUsername = $_SESSION['admin_username'];
        
        if (isset($_POST['change_username']) && !empty(trim($_POST['new_username']))) {
            $newUsername = trim($_POST['new_username']);
            
            // Check if new username already exists for another admin
            $checkResult = supabaseSelect('admin', ['username' => 'eq.' . $newUsername]);
            $usernameTaken = false;
            if ($checkResult['success'] && !empty($checkResult['data'])) {
                foreach ($checkResult['data'] as $admin) {
                    if ($admin['username'] !== $currentUsername) {
                        $usernameTaken = true;
                        break;
                    }
                }
            }
            
            if ($usernameTaken) {
                $error = 'Username sudah digunakan!';
            } else {
                // Get current admin ID
                $adminResult = supabaseSelect('admin', ['username' => 'eq.' . $currentUsername]);
                if ($adminResult['success'] && !empty($adminResult['data'])) {
                    $adminId = $adminResult['data'][0]['id'];
                    
                    $updateResult = supabaseUpdate('admin', [
                        'username' => $newUsername,
                        'updated_at' => date('Y-m-d H:i:s')
                    ], $adminId);
                    
                    if ($updateResult['success']) {
                        $_SESSION['admin_username'] = $newUsername;
                        $success = 'Username berhasil diubah!';
                    } else {
                        $error = 'Gagal mengubah username! ' . $updateResult['error'];
                    }
                } else {
                    // Create admin entry if not exists (using legacy password hash)
                    // First, we need to get the legacy password - but since we don't have the plain text, we'll create a temporary password
                    // Wait, better to create admin entry first by having user change password, then change username
                    $error = 'Silakan ubah password terlebih dahulu untuk membuat akun di database!';
                }
            }
        }
        
        if (isset($_POST['change_password']) && !empty(trim($_POST['current_password'])) && !empty(trim($_POST['new_password'])) && !empty(trim($_POST['confirm_password']))) {
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            if ($newPassword !== $confirmPassword) {
                $error = 'Password baru dan konfirmasi tidak cocok!';
            } elseif (strlen($newPassword) < 6) {
                $error = 'Password baru harus minimal 6 karakter!';
            } else {
                // Get current admin
                $adminResult = supabaseSelect('admin', ['username' => 'eq.' . $currentUsername]);
                $passwordMatched = false;
                $admin = null;
                
                if ($adminResult['success'] && !empty($adminResult['data'])) {
                    $admin = $adminResult['data'][0];
                    // Verify current password against database
                    if (password_verify($currentPassword, $admin['password'])) {
                        $passwordMatched = true;
                    }
                }
                
                // If no match yet, try legacy auth
                if (!$passwordMatched) {
                    $passwordHash = hash_hmac('sha256', $currentPassword, ADMIN_PASSWORD_SALT);
                    if ($currentUsername === ADMIN_USERNAME && hash_equals(ADMIN_PASSWORD_HASH, $passwordHash)) {
                        $passwordMatched = true;
                    }
                }
                
                if ($passwordMatched) {
                    $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                    
                    if ($admin) {
                        // Update existing admin
                        $updateResult = supabaseUpdate('admin', [
                            'password' => $hashedNewPassword,
                            'updated_at' => date('Y-m-d H:i:s')
                        ], $admin['id']);
                        
                        if ($updateResult['success']) {
                            $success = 'Password berhasil diubah!';
                        } else {
                            $error = 'Gagal mengubah password! ' . $updateResult['error'];
                        }
                    } else {
                        // Insert new admin
                        $insertResult = supabaseInsert('admin', [
                            'username' => $currentUsername,
                            'password' => $hashedNewPassword
                        ]);
                        
                        if ($insertResult['success']) {
                            $success = 'Password berhasil diubah!';
                        } else {
                            $error = 'Gagal mengubah password! ' . $insertResult['error'];
                        }
                    }
                } else {
                    $error = 'Password saat ini salah!';
                }
            }
        }
    }
}

include 'components/head.php';
include 'components/sidebar.php';
?>
  <!-- Main Content -->
  <main class="flex-1 flex flex-col overflow-hidden">
    <?php include 'components/header.php'; ?>
    <div class="flex-1 overflow-y-auto p-8">
      <div class="max-w-3xl mx-auto">
        <?php if ($success): ?>
          <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl flex items-center gap-3">
            <iconify-icon icon="lucide:check-circle"></iconify-icon>
            <?php echo $success; ?>
          </div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-center gap-3">
            <iconify-icon icon="lucide:alert-circle"></iconify-icon>
            <?php echo $error; ?>
          </div>
        <?php endif; ?>

        <!-- Change Username -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-md p-6 mb-8">
          <h3 class="text-lg font-semibold text-slate-900 mb-6">Ubah Username</h3>
          <form method="POST" class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Username Saat Ini</label>
              <input type="text" value="<?php echo htmlspecialchars($_SESSION['admin_username']); ?>" disabled class="w-full px-4 py-3 bg-slate-100 border border-slate-300 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 text-slate-500">
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Username Baru</label>
              <input type="text" name="new_username" required class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all">
            </div>
            <button type="submit" name="change_username" class="inline-flex items-center justify-center rounded-full bg-blue-600 px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-300/30 transition-colors hover:bg-blue-700">
              Ubah Username
            </button>
          </form>
        </div>

        <!-- Change Password -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-md p-6">
          <h3 class="text-lg font-semibold text-slate-900 mb-6">Ubah Password</h3>
          <form method="POST" class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Password Saat Ini</label>
              <input type="password" name="current_password" required class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all">
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Password Baru</label>
              <input type="password" name="new_password" required minlength="6" class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all">
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Konfirmasi Password Baru</label>
              <input type="password" name="confirm_password" required minlength="6" class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all">
            </div>
            <button type="submit" name="change_password" class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-300/30 transition-colors hover:bg-emerald-700">
              Ubah Password
            </button>
          </form>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
