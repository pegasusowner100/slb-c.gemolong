<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/cloudinary.php';
require_once '../includes/supabase_storage.php';
require_login();

$title = "Edit Beranda — SLB-C YPSLB Gemolong";
$page_title = "Edit Beranda";
$success = '';
$error = '';

// Default hero data
$hero = [
    'tagline' => 'PENERIMAAN SISWA BARU MASIH DIBUKA, SISA KUOTA 5 SISWA',
    'judul' => 'SLB-C YPSLB Gemolong',
    'deskripsi' => 'Membentuk generasi unggul, berkarakter, dan berprestasi melalui pendidikan berkualitas dengan lingkungan belajar yang inspiratif dan inovatif.',
    'background_image' => 'https://picsum.photos/seed/school-hero-main/1920/1080',
    'background_images' => 'https://picsum.photos/seed/hero1/1920/1080,https://picsum.photos/seed/hero2/1920/1080,https://picsum.photos/seed/hero3/1920/1080',
    'cta1_text' => 'Daftar PPDB',
    'cta1_link' => BASE_URL . '/pages/ppdb.php',
    'cta2_text' => 'Jelajahi Sekolah',
    'cta2_link' => '#profil',
    'motto' => 'Mandiri berkarakter berdikari',
    'tahun_berdiri' => 1990,
    'siswa_aktif' => 1250,
    'alumni' => 5000,
    'tenaga_pendidik' => 85,
    'total_prestasi' => 150,
    'jumlah_ruangan' => 26,
    'buku_paket' => 500,
    'latitude' => '-7.4585',
    'longitude' => '110.9567'
];

// Load hero data from Supabase
if ($supabaseConnected) {
    $heroResult = supabaseSelect('hero', ['id' => 'eq.1', 'limit' => 1]);
    if ($heroResult['success'] && !empty($heroResult['data'])) {
        $hero = array_merge($hero, $heroResult['data'][0]);
        if (empty($hero['background_image'])) {
            $hero['background_image'] = 'https://picsum.photos/seed/school-hero-main/1920/1080';
        }
        if (empty($hero['latitude'])) {
            $hero['latitude'] = '-7.4585';
        }
        if (empty($hero['longitude'])) {
            $hero['longitude'] = '110.9567';
        }
    }
    
    // Hitung jumlah siswa aktif
    $siswaResult = supabaseSelect('siswa', ['status' => 'eq.Aktif']);
    if ($siswaResult['success']) {
        $hero['siswa_aktif'] = count($siswaResult['data']);
    }
    
    // Hitung jumlah guru dan tendik
    $guruResult = supabaseSelect('guru', []);
    if ($guruResult['success']) {
        $hero['tenaga_pendidik'] = count($guruResult['data']);
    }
}

// Parse background images into array
$bgImages = !empty($hero['background_images']) ? explode(',', $hero['background_images']) : [];
$bgImages = array_map('trim', $bgImages);
$bgImages = array_filter($bgImages);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $newBgImages = [];
        
        // Handle existing images
        if (isset($_POST['existing_images']) && is_array($_POST['existing_images'])) {
            $newBgImages = array_map('trim', $_POST['existing_images']);
            $newBgImages = array_filter($newBgImages);
        }
        
        // Handle new file uploads
        if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['name'])) {
            $fileCount = count($_FILES['new_images']['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['new_images']['error'][$i] === UPLOAD_ERR_OK && count($newBgImages) < 6) {
                    $file = [
                        'name' => $_FILES['new_images']['name'][$i],
                        'type' => $_FILES['new_images']['type'][$i],
                        'tmp_name' => $_FILES['new_images']['tmp_name'][$i],
                        'error' => $_FILES['new_images']['error'][$i],
                        'size' => $_FILES['new_images']['size'][$i]
                    ];
                    
                    $uploadResult = uploadToCloudinary($file, 'hero');
                    if ($uploadResult['success']) {
                        $newBgImages[] = $uploadResult['url'];
                    } elseif (!isset($uploadResult['skip_upload'])) {
                        $error = 'Gagal upload gambar: ' . ($uploadResult['error'] ?? 'Unknown error');
                    }
                }
            }
        }
        
        // Limit to 6 images
        $newBgImages = array_slice($newBgImages, 0, 6);
        $bgImagesString = implode(',', $newBgImages);
        
        // Handle upload file video
        $backgroundVideo = $_POST['background_video'] ?? '';
        if (isset($_FILES['background_video_file']) && $_FILES['background_video_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToSupabaseStorage($_FILES['background_video_file'], 'dokumentasi', 'video');
            if ($uploadResult['success']) {
                $backgroundVideo = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload video: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }
        
        $data = [
            'tagline' => $_POST['tagline'],
            'judul' => $_POST['judul'],
            'deskripsi' => $_POST['deskripsi'],
            'motto' => $_POST['motto'] ?? 'Mandiri berkarakter berdikari',
            'latitude' => $_POST['latitude'] ?? $hero['latitude'],
            'longitude' => $_POST['longitude'] ?? $hero['longitude'],
            'background_image' => !empty($newBgImages) ? $newBgImages[0] : $hero['background_image'],
            'background_images' => $bgImagesString,
            'background_video' => $backgroundVideo,
            'cta1_text' => $_POST['cta1_text'],
            'cta1_link' => $_POST['cta1_link'],
            'cta2_text' => $_POST['cta2_text'],
            'cta2_link' => $_POST['cta2_link'],
            'tahun_berdiri' => intval($_POST['tahun_berdiri']),
            'alumni' => intval($_POST['alumni']),
            'total_prestasi' => intval($_POST['total_prestasi']),
            'jumlah_ruangan' => intval($_POST['jumlah_ruangan'] ?? 0),
            'buku_paket' => intval($_POST['buku_paket'] ?? 0)
        ];
        
        // Check if hero exists
        $checkResult = supabaseSelect('hero', ['id' => 'eq.1', 'limit' => 1]);
        $heroExists = $checkResult['success'] && !empty($checkResult['data']);
        
        if ($heroExists) {
            $result = supabaseUpdate('hero', $data, 1);
        } else {
            $data['id'] = 1;
            $result = supabaseInsert('hero', $data);
        }
        
        if ($result['success']) {
            $success = 'Beranda berhasil diperbarui di Supabase!';
            // Refresh data
            $heroResult = supabaseSelect('hero', ['id' => 'eq.1', 'limit' => 1]);
            if ($heroResult['success'] && !empty($heroResult['data'])) {
                $hero = array_merge($hero, $heroResult['data'][0]);
                $bgImages = !empty($hero['background_images']) ? explode(',', $hero['background_images']) : [];
                $bgImages = array_map('trim', $bgImages);
                $bgImages = array_filter($bgImages);
            }
        } else {
            $error = 'Gagal memperbarui beranda!';
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
        
        <form method="POST" enctype="multipart/form-data">
          <!-- Hero Section -->
          <div class="bg-white rounded-lg border border-[#E8E4D9] shadow-sm overflow-hidden">
            <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between">
              <h3 class="font-semibold text-[#1F2D26]">Hero Section</h3>
              <button type="submit" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest disabled:opacity-50">Simpan Perubahan</button>
            </div>
            <div class="p-6 space-y-6">
              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Tagline</label>
                <input type="text" name="tagline" value="<?php echo htmlspecialchars($hero['tagline'] ?? ''); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
              </div>
              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Judul Hero</label>
                <input type="text" name="judul" value="<?php echo htmlspecialchars($hero['judul'] ?? ''); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
              </div>
              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Deskripsi Hero</label>
                <textarea name="deskripsi" rows="4" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm resize-none" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>><?php echo htmlspecialchars($hero['deskripsi'] ?? ''); ?></textarea>
              </div>
              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Motto</label>
                <input type="text" name="motto" value="<?php echo htmlspecialchars($hero['motto'] ?? 'Mandiri berkarakter berdikari'); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> placeholder="Mandiri berkarakter berdikari">
              </div>
              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Koordinat Lokasi</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="relative">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#9FB5A5]">
                        <iconify-icon icon="lucide:compass" class="w-5 h-5"></iconify-icon>
                    </div>
                    <label class="absolute left-12 top-1/2 -translate-y-1/2 text-xs text-[#9FB5A5] font-semibold">Lintang:</label>
                    <input type="number" step="any" name="latitude" value="<?php echo htmlspecialchars($hero['latitude'] ?? ''); ?>" class="w-full pl-20 pr-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  </div>
                  <div class="relative">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#9FB5A5]">
                        <iconify-icon icon="lucide:navigation-2" class="w-5 h-5"></iconify-icon>
                    </div>
                    <label class="absolute left-12 top-1/2 -translate-y-1/2 text-xs text-[#9FB5A5] font-semibold">Bujur:</label>
                    <input type="number" step="any" name="longitude" value="<?php echo htmlspecialchars($hero['longitude'] ?? ''); ?>" class="w-full pl-20 pr-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  </div>
                </div>
              </div>
              
              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Background Video (Opsional)</label>
                <div class="space-y-4">
                    <input type="text" id="background_video_input" name="background_video" value="<?php echo htmlspecialchars($hero['background_video'] ?? ''); ?>" placeholder="Contoh: https://example.com/video.mp4 (link direct MP4)" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                    
                    <div>
                      <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Atau upload file video (MP4, Max 100MB)</label>
                      <input type="file" id="background_video_file" name="background_video_file" accept="video/mp4" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                    </div>
                    
                    <div id="background_video_preview" class="relative" style="<?php echo empty($hero['background_video']) ? 'display: none;' : ''; ?>">
                      <video id="background_video_element" src="<?php echo htmlspecialchars($hero['background_video'] ?? ''); ?>" autoplay muted loop playsinline class="w-full h-40 object-cover rounded-lg border border-[#E8E4D9]"></video>
                      <button type="button" id="clear_background_video" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1">
                        <iconify-icon icon="lucide:x" class="w-4 h-4"></iconify-icon>
                      </button>
                    </div>
                    <p class="text-xs text-gray-500">
                      <iconify-icon icon="lucide:alert-circle" class="w-3 h-3 inline mr-1"></iconify-icon>
                      Gunakan link direct file MP4 (tidak YouTube), atau upload file MP4 langsung di atas.
                    </p>
                    <p class="text-xs text-blue-600">Jika video diisi, gambar akan diganti dengan video</p>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Background Images (Max 6)</label>
                
                <!-- Existing Images -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
                    <?php if (empty($bgImages)): ?>
                        <!-- Fallback if no images -->
                        <div class="relative group col-span-full md:col-span-1">
                            <img src="https://picsum.photos/seed/hero1/400/300" class="w-full h-32 object-cover rounded-lg border border-[#E8E4D9]" alt="Background Default">
                            <input type="hidden" name="existing_images[]" value="https://picsum.photos/seed/hero1/400/300">
                        </div>
                        <div class="relative group col-span-full md:col-span-1">
                            <img src="https://picsum.photos/seed/hero2/400/300" class="w-full h-32 object-cover rounded-lg border border-[#E8E4D9]" alt="Background Default">
                            <input type="hidden" name="existing_images[]" value="https://picsum.photos/seed/hero2/400/300">
                        </div>
                        <div class="relative group col-span-full md:col-span-1">
                            <img src="https://picsum.photos/seed/hero3/400/300" class="w-full h-32 object-cover rounded-lg border border-[#E8E4D9]" alt="Background Default">
                            <input type="hidden" name="existing_images[]" value="https://picsum.photos/seed/hero3/400/300">
                        </div>
                    <?php else: ?>
                        <?php foreach ($bgImages as $index => $img): ?>
                            <div class="relative group">
                                <img src="<?php echo htmlspecialchars($img); ?>" class="w-full h-32 object-cover rounded-lg border border-[#E8E4D9]" alt="Background <?php echo $index + 1; ?>">
                                <input type="hidden" name="existing_images[]" value="<?php echo htmlspecialchars($img); ?>">
                                <button type="button" onclick="this.closest('.relative').remove()" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <iconify-icon icon="lucide:x" class="w-4 h-4"></iconify-icon>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Upload New Images -->
                <input type="file" name="new_images[]" accept="image/*" multiple class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                <p class="text-xs text-[#9FB5A5] mt-2">Anda dapat memilih beberapa gambar sekaligus (maks 6 total gambar)</p>
              </div>
              
              <div class="pt-4 border-t border-[#E8E4D9]">
                <h4 class="text-sm font-semibold mb-4 text-[#1F2D26]">Tombol CTA</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Text Tombol 1</label>
                    <input type="text" name="cta1_text" value="<?php echo htmlspecialchars($hero['cta1_text'] ?? ''); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Link Tombol 1</label>
                    <input type="text" name="cta1_link" value="<?php echo htmlspecialchars($hero['cta1_link'] ?? ''); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Text Tombol 2</label>
                    <input type="text" name="cta2_text" value="<?php echo htmlspecialchars($hero['cta2_text'] ?? ''); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Link Tombol 2</label>
                    <input type="text" name="cta2_link" value="<?php echo htmlspecialchars($hero['cta2_link'] ?? ''); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  </div>
                </div>
              </div>
              
              <div class="pt-4 border-t border-[#E8E4D9]">
                <h4 class="text-sm font-semibold mb-4 text-[#1F2D26]">Statistik Sekolah</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Tahun Berdiri</label>
                    <input type="number" name="tahun_berdiri" value="<?php echo htmlspecialchars($hero['tahun_berdiri'] ?? 1990); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                    <p class="text-[9px] text-[#9FB5A5] mt-1">Sesuai data beranda</p>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Siswa Aktif</label>
                    <input type="number" name="siswa_aktif" value="<?php echo htmlspecialchars($hero['siswa_aktif'] ?? 1250); ?>" class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded text-gray-500 cursor-not-allowed text-sm" disabled>
                    <p class="text-[9px] text-[#9FB5A5] mt-1">Sesuai data siswa</p>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Alumni</label>
                    <input type="number" name="alumni" value="<?php echo htmlspecialchars($hero['alumni'] ?? 5000); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Tenaga Pendidik</label>
                    <input type="number" name="tenaga_pendidik" value="<?php echo htmlspecialchars($hero['tenaga_pendidik'] ?? 85); ?>" class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded text-gray-500 cursor-not-allowed text-sm" disabled>
                    <p class="text-[9px] text-[#9FB5A5] mt-1">Sesuai data guru dan tendik</p>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Total Prestasi</label>
                    <input type="number" name="total_prestasi" value="<?php echo htmlspecialchars($hero['total_prestasi'] ?? 150); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                    <p class="text-[9px] text-[#9FB5A5] mt-1">Sesuai data beranda</p>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Jumlah Ruangan</label>
                    <input type="number" name="jumlah_ruangan" value="<?php echo htmlspecialchars($hero['jumlah_ruangan'] ?? 26); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                    <p class="text-[9px] text-[#9FB5A5] mt-1">Sesuai data statistik</p>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Buku Paket</label>
                    <input type="number" name="buku_paket" value="<?php echo htmlspecialchars($hero['buku_paket'] ?? 500); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                    <p class="text-[9px] text-[#9FB5A5] mt-1">Sesuai data statistik</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </main>
  
  <!-- JavaScript for Live Preview -->
  <script>
    // Live preview for background video
    const videoInput = document.getElementById('background_video_input');
    const videoFileInput = document.getElementById('background_video_file');
    const videoPreview = document.getElementById('background_video_preview');
    const clearButton = document.getElementById('clear_background_video');
    
    let currentBlobUrl = null;

    function getVideoEmbed(url) {
      if (!url) return '';
      
      // Check for YouTube
      const youtubeMatch = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
      if (youtubeMatch) {
        const videoId = youtubeMatch[1];
        return `<iframe src="https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1&loop=1&playlist=${videoId}" width="100%" height="160px" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="border-radius: 0.5rem; border: 1px solid #E8E4D9;"></iframe>`;
      }
      
      // Check for Vimeo
      const vimeoMatch = url.match(/vimeo\.com\/(?:.*\/)?(\d+)/);
      if (vimeoMatch) {
        const videoId = vimeoMatch[1];
        return `<iframe src="https://player.vimeo.com/video/${videoId}?autoplay=1&muted=1&loop=1&title=0&byline=0&portrait=0" width="100%" height="160px" frameborder="0" allow="autoplay; fullscreen" allowfullscreen style="border-radius: 0.5rem; border: 1px solid #E8E4D9;"></iframe>`;
      }
      
      // Default to direct video file
      return `<video id="background_video_element" src="${url}" autoplay muted loop playsinline class="w-full h-[160px] object-cover rounded-lg border border-[#E8E4D9]"></video>`;
    }

    function showPreview(src) {
      if (src) {
        videoPreview.innerHTML = getVideoEmbed(src);
        videoPreview.style.display = 'block';
      } else {
        videoPreview.innerHTML = '<video id="background_video_element" autoplay muted loop playsinline class="w-full h-[160px] object-cover rounded-lg border border-[#E8E4D9]"></video>';
        videoPreview.style.display = 'none';
      }
    }

    videoInput.addEventListener('input', function(e) {
      showPreview(e.target.value);
    });

    videoFileInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        if (file.size > 100 * 1024 * 1024) { // 100MB limit
          alert('File terlalu besar. Maksimal 100MB.');
          videoFileInput.value = '';
          return;
        }
        // Clean up previous blob URL
        if (currentBlobUrl) {
          URL.revokeObjectURL(currentBlobUrl);
        }
        currentBlobUrl = URL.createObjectURL(file);
        showPreview(currentBlobUrl);
      }
    });

    clearButton.addEventListener('click', function() {
      videoInput.value = '';
      videoFileInput.value = '';
      if (currentBlobUrl) {
        URL.revokeObjectURL(currentBlobUrl);
        currentBlobUrl = null;
      }
      showPreview('');
    });
  </script>
</body>
</html>
