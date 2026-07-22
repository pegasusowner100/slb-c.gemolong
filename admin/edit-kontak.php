
<?php
define('ADMIN_PAGE', true);
require_once '../includes/session.php';
require_once '../includes/db.php';
require_login();

$title = "Edit Kontak — SLB BC KARYA SEJAHTERA";
$page_title = "Edit Kontak";
$success = '';
$error = '';

// Data Kontak & Sosial Media (Sama dengan Profil)
$kontak = [
    'nama_sekolah' => $profilSekolah['nama_sekolah'],
    'alamat' => $profilSekolah['alamat'],
    'telepon' => $profilSekolah['telepon'],
    'email' => $profilSekolah['email'],
    'website' => $profilSekolah['website'] ?? '',
    'instagram' => $profilSekolah['instagram'] ?? '',
    'facebook' => $profilSekolah['facebook'] ?? '',
    'youtube' => $profilSekolah['youtube'] ?? '',
    'tiktok' => $profilSekolah['tiktok'] ?? '',
    'maps_url' => $profilSekolah['maps_url'] ?? ''
];

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $data = [
            'nama_sekolah' => $_POST['nama_sekolah'] ?? $kontak['nama_sekolah'],
            'alamat' => $_POST['alamat'] ?? $kontak['alamat'],
            'telepon' => $_POST['telepon'] ?? $kontak['telepon'],
            'email' => $_POST['email'] ?? $kontak['email'],
            'website' => $_POST['website'] ?? $kontak['website'],
            'instagram' => $_POST['instagram'] ?? $kontak['instagram'],
            'facebook' => $_POST['facebook'] ?? $kontak['facebook'],
            'youtube' => $_POST['youtube'] ?? $kontak['youtube'],
            'tiktok' => $_POST['tiktok'] ?? $kontak['tiktok'],
            'maps_url' => $_POST['maps_url'] ?? $kontak['maps_url'],
            'updated_at' => date('c')
        ];
        
        $checkResult = supabaseSelect('profil_sekolah', ['order' => 'created_at.desc', 'limit' => 1]);
        $exists = $checkResult['success'] && !empty($checkResult['data']);
        $persistKontakt = function($payload, $existingId = null) {
            if ($existingId !== null) {
                return supabaseUpdate('profil_sekolah', $payload, $existingId);
            }
            return supabaseInsert('profil_sekolah', $payload);
        };
        
        if ($exists) {
            $existingId = $checkResult['data'][0]['id'];
            $result = $persistKontakt($data, $existingId);
        } else {
            $data['created_at'] = date('c');
            $result = $persistKontakt($data);
        }
        
        if ($result['success']) {
            $success = 'Kontak berhasil diperbarui!';
            // Refresh Data
            require '../includes/db.php';
            $kontak = [
                'nama_sekolah' => $profilSekolah['nama_sekolah'],
                'alamat' => $profilSekolah['alamat'],
                'telepon' => $profilSekolah['telepon'],
                'email' => $profilSekolah['email'],
                'website' => $profilSekolah['website'] ?? '',
                'instagram' => $profilSekolah['instagram'] ?? '',
                'facebook' => $profilSekolah['facebook'] ?? '',
                'youtube' => $profilSekolah['youtube'] ?? '',
                'tiktok' => $profilSekolah['tiktok'] ?? '',
                'maps_url' => $profilSekolah['maps_url'] ?? ''
            ];
        } else {
            $error = 'Gagal memperbarui kontak!';
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
      <div class="max-w-4xl">
        <?php if ($success): ?>
          <div class="mb-8 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg flex items-center gap-2">
            <iconify-icon icon="lucide:check-circle"></iconify-icon>
            <?php echo $success; ?>
          </div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="mb-8 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg flex items-center gap-2">
            <iconify-icon icon="lucide:alert-circle"></iconify-icon>
            <?php echo $error; ?>
          </div>
        <?php endif; ?>
        
        <?php if (!$supabaseConnected): ?>
          <div class="mb-8 p-4 bg-yellow-50 text-yellow-800 border border-yellow-200">
            <iconify-icon icon="lucide:alert-triangle" class="inline mr-2"></iconify-icon>
            PERINGATAN: Supabase tidak terhubung! Perubahan tidak dapat disimpan.
          </div>
        <?php endif; ?>
        
        <form method="POST">
          <div class="bg-white rounded-lg border border-[#E8E4D9] shadow-sm overflow-hidden mb-8">
            <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between">
              <h3 class="font-semibold text-[#1F2D26]">Informasi Kontak</h3>
              <button type="submit" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest disabled:opacity-50">Simpan Perubahan</button>
            </div>
            <div class="p-6 space-y-6">
              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Nama Sekolah</label>
                <input type="text" name="nama_sekolah" value="<?php echo htmlspecialchars($kontak['nama_sekolah']); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
              </div>
              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Alamat</label>
                <textarea name="alamat" rows="3" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm resize-none"><?php echo htmlspecialchars($kontak['alamat']); ?></textarea>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Telepon</label>
                  <input type="text" name="telepon" value="<?php echo htmlspecialchars($kontak['telepon']); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
                </div>
                <div>
                  <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Email</label>
                  <input type="email" name="email" value="<?php echo htmlspecialchars($kontak['email']); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
                </div>
              </div>
              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Website</label>
                <input type="url" name="website" value="<?php echo htmlspecialchars($kontak['website']); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
              </div>
              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Link Google Maps (Embed)</label>
                <input type="url" name="maps_url" value="<?php echo htmlspecialchars($kontak['maps_url']); ?>" placeholder="https://www.google.com/maps/embed?pb=!1m18..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg border border-[#E8E4D9] shadow-sm overflow-hidden">
            <div class="p-6 border-b border-[#E8E4D9]">
              <h3 class="font-semibold text-[#1F2D26]">Sosial Media</h3>
            </div>
            <div class="p-6 space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Instagram</label>
                  <input type="url" name="instagram" value="<?php echo htmlspecialchars($kontak['instagram']); ?>" placeholder="https://instagram.com/..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
                </div>
                <div>
                  <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Facebook</label>
                  <input type="url" name="facebook" value="<?php echo htmlspecialchars($kontak['facebook']); ?>" placeholder="https://facebook.com/..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">YouTube</label>
                  <input type="url" name="youtube" value="<?php echo htmlspecialchars($kontak['youtube']); ?>" placeholder="https://youtube.com/..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
                </div>
                <div>
                  <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">TikTok</label>
                  <input type="url" name="tiktok" value="<?php echo htmlspecialchars($kontak['tiktok']); ?>" placeholder="https://tiktok.com/..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </main>

</body>
</html>
