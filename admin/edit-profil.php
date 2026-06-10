<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/cloudinary.php';
require_login();

$title = "Edit Profil Sekolah — " . SITE_NAME;
$page_title = "Edit Profil";
$success = '';
$error = '';

// Default profil data
$defaultProfil = [
    'nama_sekolah' => SITE_NAME,
    'akreditasi' => 'A',
    'sejarah' => 'SLB-C YPSLB Gemolong didirikan dengan tujuan memberikan pendidikan terbaik untuk anak berkebutuhan khusus. Berkomitmen untuk menciptakan generasi mandiri, berkarakter, dan berprestasi.',
    'visi' => 'Menjadikan SLB-C YPSLB Gemolong sebagai lembaga pendidikan luar biasa yang unggul dalam pengembangan potensi anak berkebutuhan khusus secara optimal, berkarakter, mandiri, dan berprestasi.',
    'misi' => 'Menyelenggarakan pendidikan yang berkualitas, mengembangkan potensi akademik dan non-akademik, serta membangun karakter, serta menjalin kerjasama dengan berbagai pihak.',
    'profil_kepala_sekolah' => 'Kepala sekolah yang inovatif, berdedikasi, dan berpengalaman dalam dunia pendidikan khusus.',
    'sambutan' => 'Pendidikan bukan sekadar menuntut ilmu, melainkan proses membentuk karakter, membangun mimpi, dan memberdayakan generasi yang akan membawa perubahan bagi bangsa. Di SLB-C YPSLB Gemolong, kami berkomitmen untuk menjadi rumah kedua bagi setiap siswa agar mereka tumbuh menjadi pribadi yang unggul dan berkarakter.',
    'alamat' => 'Jl. Pendidikan No. 1, Gemolong, Kabupaten Sragen, Jawa Tengah',
    'telepon' => '(0271) 123456',
    'email' => 'info@slbc-gemolong.sch.id',
    'gambar_gedung' => 'https://picsum.photos/seed/school-building-front/700/525.jpg',
    'struktur_organisasi' => 'https://picsum.photos/seed/struktur-organisasi/1000/600.jpg',
    'dasar_hukum' => '1. Undang-Undang Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional
2. Peraturan Pemerintah Nomor 19 Tahun 2005 tentang Pendidikan Anak Berkebutuhan Khusus
3. Peraturan Menteri Pendidikan dan Kebudayaan Nomor 70 Tahun 2013 tentang Pendidikan Dasar
4. Peraturan Daerah Provinsi Jawa Tengah Nomor 12 Tahun 2018 tentang Pendidikan Luar Biasa
5. Akta Notaris Pendirian Yayasan YPSLB Gemolong Nomor 01 Tanggal 01 Januari 2000',
    'nama_kepala_sekolah' => 'Drs. Ahmad Sudrajat, M.Pd',
    'foto_kepala_sekolah' => 'https://picsum.photos/seed/kepsek-portrait/480/600.jpg',
    'instagram' => 'https://instagram.com/slbcypslbgemolong',
    'facebook' => 'https://facebook.com/slbcypslbgemolong',
    'youtube' => 'https://youtube.com/@slbcypslbgemolong',
    'tiktok' => 'https://tiktok.com/@slbcypslbgemolong',
    'website' => 'https://slbc-gemolong.sch.id',
    'maps_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3955.012345678901!2d110.98765432109876!3d-7.456789012345679!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a1234567890ab%3A0x123456789abcdef!2sSLB-C%20YPSLB%20Gemolong!5e0!3m2!1sid!2sid!4v1234567890123!5m2!1sid!2sid',
    'video_profil' => '',
    'logo_url' => ''
];
$profil = $defaultProfil;

if ($supabaseConnected) {
    $profilResult = supabaseSelect('profil_sekolah', ['id' => 'eq.1', 'limit' => 1]);
    if ($profilResult['success'] && !empty($profilResult['data'])) {
        $dbProfil = $profilResult['data'][0];
        // Merge with default, ensuring all keys exist
        $profil = [
            'nama_sekolah' => $dbProfil['nama_sekolah'] ?? $defaultProfil['nama_sekolah'],
            'akreditasi' => $dbProfil['akreditasi'] ?? $defaultProfil['akreditasi'],
            'sejarah' => $dbProfil['sejarah'] ?? $defaultProfil['sejarah'],
            'visi' => $dbProfil['visi'] ?? $defaultProfil['visi'],
            'misi' => $dbProfil['misi'] ?? $defaultProfil['misi'],
            'profil_kepala_sekolah' => $dbProfil['profil_kepala_sekolah'] ?? $defaultProfil['profil_kepala_sekolah'],
            'sambutan' => $dbProfil['sambutan'] ?? $defaultProfil['sambutan'],
            'alamat' => $dbProfil['alamat'] ?? $defaultProfil['alamat'],
            'telepon' => $dbProfil['telepon'] ?? $defaultProfil['telepon'],
            'email' => $dbProfil['email'] ?? $defaultProfil['email'],
            'gambar_gedung' => $dbProfil['gambar_gedung'] ?? $defaultProfil['gambar_gedung'],
            'logo_url' => $dbProfil['logo_url'] ?? $defaultProfil['logo_url'],
            'struktur_organisasi' => $dbProfil['struktur_organisasi'] ?? $defaultProfil['struktur_organisasi'],
            'dasar_hukum' => $dbProfil['dasar_hukum'] ?? $defaultProfil['dasar_hukum'],
            'nama_kepala_sekolah' => $dbProfil['nama_kepala_sekolah'] ?? $defaultProfil['nama_kepala_sekolah'],
            'foto_kepala_sekolah' => $dbProfil['foto_kepala_sekolah'] ?? $defaultProfil['foto_kepala_sekolah'],
            'instagram' => $dbProfil['instagram'] ?? $defaultProfil['instagram'],
            'facebook' => $dbProfil['facebook'] ?? $defaultProfil['facebook'],
            'youtube' => $dbProfil['youtube'] ?? $defaultProfil['youtube'],
            'tiktok' => $dbProfil['tiktok'] ?? $defaultProfil['tiktok'],
            'website' => $dbProfil['website'] ?? $defaultProfil['website'],
            'maps_url' => $dbProfil['maps_url'] ?? $defaultProfil['maps_url'],
            'video_profil' => $dbProfil['video_profil'] ?? $defaultProfil['video_profil']
        ];
    }
    $checkResult = $profilResult; // For debug info
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        // Initialize data with existing values (with safe fallbacks)
        $data = [
            'id' => 1,
            'nama_sekolah' => $_POST['nama_sekolah'] ?? $profil['nama_sekolah'],
            'akreditasi' => $_POST['akreditasi'] ?? $profil['akreditasi'],
            'sejarah' => $_POST['sejarah'] ?? $profil['sejarah'],
            'visi' => $_POST['visi'] ?? $profil['visi'],
            'misi' => $_POST['misi'] ?? $profil['misi'],
            'profil_kepala_sekolah' => $_POST['profil_kepala_sekolah'] ?? $profil['profil_kepala_sekolah'],
            'sambutan' => $_POST['sambutan'] ?? $profil['sambutan'],
            'nama_kepala_sekolah' => $_POST['nama_kepala_sekolah'] ?? $profil['nama_kepala_sekolah'],
            'gambar_gedung' => $profil['gambar_gedung'],
            'foto_kepala_sekolah' => $profil['foto_kepala_sekolah'],
            'struktur_organisasi' => $profil['struktur_organisasi'],
            'dasar_hukum' => $_POST['dasar_hukum'] ?? $profil['dasar_hukum'],
            'video_profil' => $_POST['video_profil'] ?? $profil['video_profil']
        ];

          // include existing logo_url by default
          $data['logo_url'] = $_POST['logo_url'] ?? $profil['logo_url'];
        
        // Handle gambar gedung upload
        if (isset($_FILES['gambar_gedung_file']) && $_FILES['gambar_gedung_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['gambar_gedung_file'], 'profil');
            if ($uploadResult['success']) {
                $data['gambar_gedung'] = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload gambar gedung: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }
        
        // Handle foto kepala sekolah upload
        if (isset($_FILES['foto_kepala_sekolah_file']) && $_FILES['foto_kepala_sekolah_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['foto_kepala_sekolah_file'], 'profil');
            if ($uploadResult['success']) {
                $data['foto_kepala_sekolah'] = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload foto kepala sekolah: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }
        
        // Handle struktur organisasi upload
        if (isset($_FILES['struktur_organisasi_file']) && $_FILES['struktur_organisasi_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['struktur_organisasi_file'], 'profil');
            if ($uploadResult['success']) {
                $data['struktur_organisasi'] = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload struktur organisasi: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }

        // Handle logo upload
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
          $uploadResult = uploadToCloudinary($_FILES['logo_file'], 'profil');
          if ($uploadResult['success']) {
            $data['logo_url'] = $uploadResult['url'];
          } elseif (!isset($uploadResult['skip_upload'])) {
            $error = 'Gagal upload logo: ' . ($uploadResult['error'] ?? 'Unknown error');
          }
        }
        
        // Check if profile exists
        $checkResult = supabaseSelect('profil_sekolah', ['id' => 'eq.1', 'limit' => 1]);
        $profileExists = $checkResult['success'] && !empty($checkResult['data']);
        
        if ($profileExists) {
            $result = supabaseUpdate('profil_sekolah', $data, 1);
            // Retry without logo_url if schema doesn't have that column
            if (!$result['success'] && isset($data['logo_url'])) {
                $errStr = strtolower($result['error'] ?? json_encode($result));
                if (strpos($errStr, 'logo_url') !== false || strpos($errStr, 'column') !== false) {
                    $backupLogo = $data['logo_url'];
                    unset($data['logo_url']);
                    $retry = supabaseUpdate('profil_sekolah', $data, 1);
                    if ($retry['success']) {
                        $result = $retry;
                        $error = 'Profil diperbarui, tetapi kolom `logo_url` belum ada di database. Jalankan SQL untuk menambahkannya jika ingin menyimpan logo.';
                    } else {
                        $result = $retry;
                    }
                }
            }
        } else {
            $result = supabaseInsert('profil_sekolah', $data);
            // Retry insert without logo_url if schema missing column
            if (!$result['success'] && isset($data['logo_url'])) {
                $errStr = strtolower($result['error'] ?? json_encode($result));
                if (strpos($errStr, 'logo_url') !== false || strpos($errStr, 'column') !== false) {
                    unset($data['logo_url']);
                    $retry = supabaseInsert('profil_sekolah', $data);
                    if ($retry['success']) {
                        $result = $retry;
                        $error = 'Profil dibuat, tetapi kolom `logo_url` belum ada di database. Jalankan SQL untuk menambahkannya jika ingin menyimpan logo.';
                    } else {
                        $result = $retry;
                    }
                }
            }
        }
        
        if ($result['success']) {
            $success = 'Profil berhasil diperbarui!';
            // Refresh data
            $profilResult = supabaseSelect('profil_sekolah', ['id' => 'eq.1', 'limit' => 1]);
            if ($profilResult['success'] && !empty($profilResult['data'])) {
                $dbProfil = $profilResult['data'][0];
                $profil = [
                    'nama_sekolah' => $dbProfil['nama_sekolah'] ?? $defaultProfil['nama_sekolah'],
                    'akreditasi' => $dbProfil['akreditasi'] ?? $defaultProfil['akreditasi'],
                    'sejarah' => $dbProfil['sejarah'] ?? $defaultProfil['sejarah'],
                    'visi' => $dbProfil['visi'] ?? $defaultProfil['visi'],
                    'misi' => $dbProfil['misi'] ?? $defaultProfil['misi'],
                    'profil_kepala_sekolah' => $dbProfil['profil_kepala_sekolah'] ?? $defaultProfil['profil_kepala_sekolah'],
                    'sambutan' => $dbProfil['sambutan'] ?? $defaultProfil['sambutan'],
                    'alamat' => $dbProfil['alamat'] ?? $defaultProfil['alamat'],
                    'telepon' => $dbProfil['telepon'] ?? $defaultProfil['telepon'],
                    'email' => $dbProfil['email'] ?? $defaultProfil['email'],
                    'gambar_gedung' => $dbProfil['gambar_gedung'] ?? $defaultProfil['gambar_gedung'],
                    'struktur_organisasi' => $dbProfil['struktur_organisasi'] ?? $defaultProfil['struktur_organisasi'],
                    'dasar_hukum' => $dbProfil['dasar_hukum'] ?? $defaultProfil['dasar_hukum'],
                    'nama_kepala_sekolah' => $dbProfil['nama_kepala_sekolah'] ?? $defaultProfil['nama_kepala_sekolah'],
                    'foto_kepala_sekolah' => $dbProfil['foto_kepala_sekolah'] ?? $defaultProfil['foto_kepala_sekolah'],
                    'instagram' => $dbProfil['instagram'] ?? $defaultProfil['instagram'],
                    'facebook' => $dbProfil['facebook'] ?? $defaultProfil['facebook'],
                    'youtube' => $dbProfil['youtube'] ?? $defaultProfil['youtube'],
                    'tiktok' => $dbProfil['tiktok'] ?? $defaultProfil['tiktok'],
                    'website' => $dbProfil['website'] ?? $defaultProfil['website'],
                    'maps_url' => $dbProfil['maps_url'] ?? $defaultProfil['maps_url'],
                    'video_profil' => $dbProfil['video_profil'] ?? $defaultProfil['video_profil'],
                    'logo_url' => $dbProfil['logo_url'] ?? $defaultProfil['logo_url']
                ];
            }
        } else {
            $error = 'Gagal memperbarui profil! ' . ($result['error'] ?? json_encode($result));
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
      <div class="max-w-5xl">
        <?php if ($success): ?>
          <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg flex items-center gap-2">
            <iconify-icon icon="lucide:check-circle"></iconify-icon>
            <?php echo $success; ?>
          </div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg flex items-center gap-2">
            <iconify-icon icon="lucide:alert-circle"></iconify-icon>
            <?php echo $error; ?>
          </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg border border-[#E8E4D9] shadow-sm overflow-hidden">
          <div class="p-6 border-b border-[#E8E4D9]">
            <h3 class="font-semibold text-[#1F2D26]">Edit Profil Sekolah</h3>
          </div>
          <form action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-8">
            
            <!-- Informasi Dasar Sekolah -->
            <div>
              <h4 class="text-sm font-semibold mb-4 text-[#1F2D26] border-b pb-2">Informasi Dasar Sekolah</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Nama Sekolah</label>
                  <input type="text" name="nama_sekolah" value="<?php echo htmlspecialchars($profil['nama_sekolah']); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                </div>
                <div>
                  <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Akreditasi</label>
                  <input type="text" name="akreditasi" value="<?php echo htmlspecialchars($profil['akreditasi']); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                  <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Logo Sekolah (URL)</label>
                  <input type="url" name="logo_url" value="<?php echo htmlspecialchars($profil['logo_url'] ?? ''); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  <p class="text-xs text-[#9FB5A5] mt-2">Masukkan URL logo jika ada.</p>
                </div>
                <div>
                  <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Unggah Logo</label>
                  <input type="file" name="logo_file" accept="image/*" id="logoFileInput" class="w-full text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  <p class="text-xs text-[#9FB5A5] mt-2">Upload file logo baru untuk menggantikan logo lama.</p>
                  <div class="mt-4">
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Preview Logo</label>
                    <img id="logoPreview" src="<?php echo htmlspecialchars(!empty($profil['logo_url']) ? $profil['logo_url'] : BASE_URL . '/assets/images/JATENG JR.jpg'); ?>" alt="Preview Logo" class="w-32 h-32 object-contain border border-[#E8E4D9] rounded-lg bg-white" onerror="this.src='https://picsum.photos/seed/logo/100/100'">
                  </div>
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                  <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Telepon</label>
                  <input type="text" value="<?php echo htmlspecialchars($profil['telepon']); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded text-sm" disabled>
                  <p class="text-xs text-[#9FB5A5] mt-2">Edit telepon di halaman <a href="edit-kontak.php" class="text-[#3E6B4E] font-semibold hover:underline">Edit Kontak</a></p>
                </div>
                <div>
                  <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Email</label>
                  <input type="email" value="<?php echo htmlspecialchars($profil['email']); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded text-sm" disabled>
                  <p class="text-xs text-[#9FB5A5] mt-2">Edit email di halaman <a href="edit-kontak.php" class="text-[#3E6B4E] font-semibold hover:underline">Edit Kontak</a></p>
                </div>
              </div>
              <div class="mt-6">
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Alamat</label>
                <textarea rows="3" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded text-sm resize-none" disabled><?php echo htmlspecialchars($profil['alamat']); ?></textarea>
                <p class="text-xs text-[#9FB5A5] mt-2">Edit alamat di halaman <a href="edit-kontak.php" class="text-[#3E6B4E] font-semibold hover:underline">Edit Kontak</a></p>
              </div>
              <div class="mt-6">
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Website</label>
                <input type="url" value="<?php echo htmlspecialchars($profil['website']); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded text-sm" disabled>
                <p class="text-xs text-[#9FB5A5] mt-2">Edit website di halaman <a href="edit-kontak.php" class="text-[#3E6B4E] font-semibold hover:underline">Edit Kontak</a></p>
              </div>
              <div class="mt-6">
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Link Google Maps (Embed)</label>
                <input type="url" value="<?php echo htmlspecialchars($profil['maps_url']); ?>" placeholder="https://www.google.com/maps/embed?pb=!1m18..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded text-sm" disabled>
                <p class="text-xs text-[#9FB5A5] mt-2">Edit Google Maps di halaman <a href="edit-kontak.php" class="text-[#3E6B4E] font-semibold hover:underline">Edit Kontak</a></p>
              </div>
            </div>
            
            <!-- Sejarah -->
            <div>
              <h4 class="text-sm font-semibold mb-4 text-[#1F2D26] border-b pb-2">Sejarah</h4>
              <div class="mb-6">
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Sejarah Sekolah</label>
                <textarea name="sejarah" rows="6" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm resize-none" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>><?php echo htmlspecialchars($profil['sejarah']); ?></textarea>
              </div>
            </div>

            <!-- Visi & Misi -->
            <div>
              <h4 class="text-sm font-semibold mb-4 text-[#1F2D26] border-b pb-2">Visi & Misi</h4>
              <div class="mb-6">
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Visi</label>
                <textarea name="visi" rows="4" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm resize-none" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>><?php echo htmlspecialchars($profil['visi']); ?></textarea>
              </div>
              <div class="mb-6">
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Misi</label>
                <textarea name="misi" rows="6" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm resize-none" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>><?php echo htmlspecialchars($profil['misi']); ?></textarea>
              </div>
            </div>
            
            <!-- Dasar Hukum -->
            <div>
              <h4 class="text-sm font-semibold mb-4 text-[#1F2D26] border-b pb-2">Dasar Hukum</h4>
              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Dasar Hukum (pisahkan dengan enter)</label>
                <textarea name="dasar_hukum" rows="8" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm resize-none" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>><?php echo htmlspecialchars($profil['dasar_hukum']); ?></textarea>
              </div>
            </div>
            
            <!-- Kepala Sekolah -->
            <div>
              <h4 class="text-sm font-semibold mb-4 text-[#1F2D26] border-b pb-2">Kepala Sekolah</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                <div>
                  <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Nama Kepala Sekolah</label>
                  <input type="text" name="nama_kepala_sekolah" value="<?php echo htmlspecialchars($profil['nama_kepala_sekolah']); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  <div class="mt-4">
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Profil Kepala Sekolah</label>
                    <textarea name="profil_kepala_sekolah" rows="6" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm resize-none" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>><?php echo htmlspecialchars($profil['profil_kepala_sekolah']); ?></textarea>
                  </div>
                  <div class="mt-4">
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Foto Kepala Sekolah (Pilih File)</label>
                    <input type="file" name="foto_kepala_sekolah_file" accept="image/*" id="fotoKepsekInput" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  </div>
                </div>
                <div class="mt-2">
                  <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Preview Foto</label>
                  <img src="<?php echo htmlspecialchars($profil['foto_kepala_sekolah']); ?>" id="fotoKepsekPreview" alt="Foto Kepala Sekolah" class="w-full max-w-xs h-auto rounded-lg border border-[#E8E4D9]">
                </div>
              </div>
            </div>
            
            <!-- Gambar Gedung & Video Profil -->
            <div>
              <h4 class="text-sm font-semibold mb-4 text-[#1F2D26] border-b pb-2">Gedung Sekolah & Video Profil</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                <div>
                  <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Gambar Gedung (Pilih File)</label>
                  <input type="file" name="gambar_gedung_file" accept="image/*" id="gambarGedungInput" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  <div class="mt-4">
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Link Video Profil (Opsional)</label>
                    <input type="url" name="video_profil" id="videoProfilInput" value="<?php echo htmlspecialchars($profil['video_profil']); ?>" placeholder="https://link-video.mp4" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  </div>
                  <div class="mt-2">
                    <button type="button" id="clearVideoProfil" class="text-xs text-red-600 hover:text-red-800">Hapus Video Profil</button>
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Preview</label>
                  <div id="videoProfilContainer" style="<?php echo empty($profil['video_profil']) ? 'display: none;' : ''; ?>">
                    <video src="<?php echo htmlspecialchars($profil['video_profil']); ?>" id="videoProfilPreview" autoplay muted loop playsinline class="w-full h-auto rounded-lg border border-[#E8E4D9]"></video>
                  </div>
                  <div id="gambarGedungContainer" style="<?php echo !empty($profil['video_profil']) ? 'display: none;' : ''; ?>">
                    <img src="<?php echo htmlspecialchars($profil['gambar_gedung']); ?>" id="gambarGedungPreview" alt="Gedung Sekolah" class="w-full h-auto rounded-lg border border-[#E8E4D9]">
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Struktur Organisasi -->
            <div>
              <h4 class="text-sm font-semibold mb-4 text-[#1F2D26] border-b pb-2">Struktur Organisasi</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                <div>
                  <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Gambar Struktur Organisasi (Pilih File)</label>
                  <input type="file" name="struktur_organisasi_file" accept="image/*" id="strukturOrganisasiInput" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                </div>
                <div>
                  <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Preview Gambar</label>
                  <img src="<?php echo htmlspecialchars($profil['struktur_organisasi']); ?>" id="strukturOrganisasiPreview" alt="Struktur Organisasi" class="w-full h-auto rounded-lg border border-[#E8E4D9]">
                </div>
              </div>
            </div>
            
            <!-- Sosial Media -->
            <div>
              <h4 class="text-sm font-semibold mb-4 text-[#1F2D26] border-b pb-2">Media Sosial</h4>
              <p class="text-xs text-[#9FB5A5] mb-4">Edit media sosial di halaman <a href="edit-kontak.php" class="text-[#3E6B4E] font-semibold hover:underline">Edit Kontak</a></p>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="flex items-center gap-2 text-xs font-bold text-[#9FB5A5] uppercase mb-2">
                    <iconify-icon icon="lucide:instagram" class="w-4 h-4"></iconify-icon> Instagram
                  </label>
                  <input type="url" value="<?php echo htmlspecialchars($profil['instagram']); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded text-sm" disabled>
                </div>
                <div>
                  <label class="flex items-center gap-2 text-xs font-bold text-[#9FB5A5] uppercase mb-2">
                    <iconify-icon icon="lucide:facebook" class="w-4 h-4"></iconify-icon> Facebook
                  </label>
                  <input type="url" value="<?php echo htmlspecialchars($profil['facebook']); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded text-sm" disabled>
                </div>
                <div>
                  <label class="flex items-center gap-2 text-xs font-bold text-[#9FB5A5] uppercase mb-2">
                    <iconify-icon icon="lucide:youtube" class="w-4 h-4"></iconify-icon> Youtube
                  </label>
                  <input type="url" value="<?php echo htmlspecialchars($profil['youtube']); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded text-sm" disabled>
                </div>
                <div>
                  <label class="flex items-center gap-2 text-xs font-bold text-[#9FB5A5] uppercase mb-2">
                    <iconify-icon icon="lucide:video" class="w-4 h-4"></iconify-icon> TikTok
                  </label>
                  <input type="url" value="<?php echo htmlspecialchars($profil['tiktok']); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded text-sm" disabled>
                </div>
              </div>
            </div>
            
            <div class="flex justify-end pt-4 border-t border-[#E8E4D9]">
              <button type="submit" class="bg-[#3E6B4E] text-white text-xs font-bold px-8 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>Simpan Profil</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
  
  <!-- JavaScript for Image & Video Preview -->
  <script>
    // Preview for Kepala Sekolah
    document.getElementById('fotoKepsekInput').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
          document.getElementById('fotoKepsekPreview').src = event.target.result;
        };
        reader.readAsDataURL(file);
      }
    });

    // Preview for Logo Sekolah
    document.getElementById('logoFileInput').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
          document.getElementById('logoPreview').src = event.target.result;
        };
        reader.readAsDataURL(file);
      }
    });

    // Preview for Gambar Gedung
    document.getElementById('gambarGedungInput').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
          document.getElementById('gambarGedungPreview').src = event.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
    
    // Preview for Struktur Organisasi
    document.getElementById('strukturOrganisasiInput').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
          document.getElementById('strukturOrganisasiPreview').src = event.target.result;
        };
        reader.readAsDataURL(file);
      }
    });

    // Preview for Video Profil
    const videoProfilInput = document.getElementById('videoProfilInput');
    const videoProfilContainer = document.getElementById('videoProfilContainer');
    const gambarGedungContainer = document.getElementById('gambarGedungContainer');
    const clearVideoProfilBtn = document.getElementById('clearVideoProfil');

    function getVideoEmbed(url) {
      if (!url) return '';
      
      // Check for YouTube
      const youtubeMatch = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
      if (youtubeMatch) {
        const videoId = youtubeMatch[1];
        return `<iframe src="https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1&loop=1&playlist=${videoId}" width="100%" height="100%" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="border-radius: 0.5rem;"></iframe>`;
      }
      
      // Check for Vimeo
      const vimeoMatch = url.match(/vimeo\.com\/(?:.*\/)?(\d+)/);
      if (vimeoMatch) {
        const videoId = vimeoMatch[1];
        return `<iframe src="https://player.vimeo.com/video/${videoId}?autoplay=1&muted=1&loop=1&title=0&byline=0&portrait=0" width="100%" height="100%" frameborder="0" allow="autoplay; fullscreen" allowfullscreen style="border-radius: 0.5rem;"></iframe>`;
      }
      
      // Default to direct video file
      return `<video autoplay muted loop playsinline src="${url}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 0.5rem;"></video>`;
    }

    function updateVideoProfilPreview(src) {
      if (src) {
        videoProfilContainer.innerHTML = getVideoEmbed(src);
        videoProfilContainer.style.display = 'block';
        gambarGedungContainer.style.display = 'none';
      } else {
        videoProfilContainer.innerHTML = '<video id="videoProfilPreview" autoplay muted loop playsinline class="w-full h-auto rounded-lg border border-[#E8E4D9]"></video>';
        videoProfilContainer.style.display = 'none';
        gambarGedungContainer.style.display = 'block';
      }
    }

    videoProfilInput.addEventListener('input', function(e) {
      updateVideoProfilPreview(e.target.value);
    });

    clearVideoProfilBtn.addEventListener('click', function() {
      videoProfilInput.value = '';
      updateVideoProfilPreview('');
    });
  </script>
</body>
</html>
