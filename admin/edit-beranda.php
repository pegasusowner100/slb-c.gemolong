<?php
define('ADMIN_PAGE', true);
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/cloudinary-on.php';
require_once '../includes/supabase_storage.php';
require_once '../includes/public_audio.php';
require_login();

$title = "Edit Beranda — SLB BC KARYA SEJAHTERA";
$page_title = "Edit Beranda";
$success = '';
$error = '';
$audioConfig = loadPublicAudioConfig();
$publicAudioUrl = $audioConfig['url'] ?? '';
$publicAudioEnabled = !empty($audioConfig['enabled']);

// Default hero data
$hero = [
    'tagline' => '',
    'judul' => 'SLB BC KARYA SEJAHTERA',
    'deskripsi' => '',
    'background_image' => '',
    'background_images' => '',
    'cta1_text' => '',
    'cta1_link' => '',
    'cta2_text' => '',
    'cta2_link' => '',
    'motto' => '',
    'tahun_berdiri' => 0,
    'siswa_aktif' => 0,
    'alumni' => 0,
    'tenaga_pendidik' => 0,
    'total_prestasi' => 0,
    'jumlah_ruangan' => 0,
    'buku_paket' => 0,
    'latitude' => '',
    'longitude' => ''
];

function mergeHeroRowWithDefaults(array $hero, array $dbHero): array {
    foreach ($hero as $k => $v) {
        if (array_key_exists($k, $dbHero) && trim((string) $dbHero[$k]) !== '') {
            $hero[$k] = $dbHero[$k];
        }
    }
    return $hero;
}

// Load hero data from Supabase (prefer DB values; preserve defaults for empty DB values)
if ($supabaseConnected) {
  $heroResult = supabaseSelect('hero', ['id' => 'eq.1', 'limit' => 1]);
  if ($heroResult['success'] && !empty($heroResult['data'])) {
    $hero = mergeHeroRowWithDefaults($hero, $heroResult['data'][0]);
  } else {
    if (!$heroResult['success']) {
      $error = 'Gagal memuat data beranda dari Supabase: ' . ($heroResult['error'] ?? 'Tidak ada data.');
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
    $skipHeroSave = false;
    $audioUrlInput = trim((string) ($_POST['background_audio'] ?? ''));
    $audioEnabledInput = isset($_POST['background_audio_enabled']);
    $audioChanged = (isset($_FILES['background_audio_file']) && $_FILES['background_audio_file']['error'] === UPLOAD_ERR_OK)
        || $audioUrlInput !== $publicAudioUrl
        || $audioEnabledInput !== $publicAudioEnabled;

    if (isset($_POST['save_audio'])) {
        $skipHeroSave = true;
    }

    if ($audioChanged) {
        $publicAudioUrl = $audioUrlInput;

        if (isset($_FILES['background_audio_file']) && $_FILES['background_audio_file']['error'] === UPLOAD_ERR_OK) {
            $audioExtension = strtolower(pathinfo($_FILES['background_audio_file']['name'] ?? 'file', PATHINFO_EXTENSION));
            if (!in_array($audioExtension, ['mp3', 'mpeg', 'wav', 'ogg', 'm4a', 'aac'], true)) {
                $error = 'Format audio tidak didukung. Gunakan file MP3 atau audio lainnya yang umum.';
            } else {
                // Ensure upload directory exists and is writable
                $uploadDir = LOCAL_UPLOAD_PUBLIC_DIR;
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                if (!is_writable($uploadDir)) {
                    chmod($uploadDir, 0755);
                }

                $uploadResult = uploadToLocal($_FILES['background_audio_file'], 'public');
                if ($uploadResult['success'] && !empty($uploadResult['url'])) {
                    $publicAudioUrl = $uploadResult['url'];
                    $success = 'File audio berhasil diunggah dan disimpan di: ' . htmlspecialchars($uploadResult['url']);
                } elseif (!$uploadResult['success']) {
                    $error = 'Gagal upload audio: ' . ($uploadResult['error'] ?? 'Unknown error');
                    if (!empty($uploadResult['data'])) {
                        $error .= ' [Debug: ' . json_encode($uploadResult['data']) . ']';
                    }
                } else {
                    $error = 'Upload berhasil tapi URL tidak valid.';
                }
            }
        }

        if (empty($error)) {
            if (savePublicAudioConfig($publicAudioUrl, $audioEnabledInput)) {
                $success = (empty($success) ? '' : $success . ' ') . 'Audio publik berhasil diperbarui dan disimpan ke config!';
                $publicAudioEnabled = $audioEnabledInput;
            } else {
                $error = 'Audio diunggah tapi gagal menyimpan ke config. Coba ulangi.';
            }
        }
    }

    if (!$supabaseConnected && !isset($_POST['save_sections']) && !$audioChanged) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } elseif (!$supabaseConnected && !isset($_POST['save_sections'])) {
        // audio-only save already handled; skip hero save
    } else {
        if (isset($_POST['change_password'])) {
            $currentUsername = $_SESSION['admin_username'];
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            if ($newPassword !== $confirmPassword) {
                $error = 'Password baru dan konfirmasi tidak cocok!';
            } elseif (strlen($newPassword) < 6) {
                $error = 'Password baru harus minimal 6 karakter!';
            } else {
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
        } elseif (isset($_POST['save_sections'])) {
            // Save homepage sections
            $homepageSectionsPath = __DIR__ . '/../includes/homepage_sections.php';

            $sections = [
                'hero' => isset($_POST['section_hero']),
                'running_text' => isset($_POST['section_running_text']),
                'profil' => isset($_POST['section_profil']),
                'tentang' => isset($_POST['section_tentang']),
                'struktur' => isset($_POST['section_struktur']),
                'sumberdaya_preview' => isset($_POST['section_sumberdaya_preview']),
                'program' => isset($_POST['section_program']),
                'fasilitas' => isset($_POST['section_fasilitas']),
                'prestasi' => isset($_POST['section_prestasi']),
                'cta_ppdb' => isset($_POST['section_cta_ppdb']),
                'berita' => isset($_POST['section_berita']),
                'galeri' => isset($_POST['section_galeri']),
                'statistik' => isset($_POST['section_statistik']),
                'anggaran' => isset($_POST['section_anggaran']),
                'layanan' => isset($_POST['section_layanan']),
                'faq' => isset($_POST['section_faq']),
                'testimoni' => isset($_POST['section_testimoni']),
            ];
            
            // Write to file
            $content = "<?php\nreturn " . var_export($sections, true) . "\n";
            if (empty($error) && file_put_contents($homepageSectionsPath, $content)) {
                $success = 'Pengaturan section beranda berhasil diperbarui!';
            } elseif (empty($error)) {
                $error = 'Gagal menyimpan pengaturan section!';
            }
        } elseif ($skipHeroSave) {
            // Audio-only save already handled.
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
                'motto' => $_POST['motto'] ?? '',
                'latitude' => $_POST['latitude'] ?? '',
                'longitude' => $_POST['longitude'] ?? '',
                'background_image' => !empty($newBgImages) ? $newBgImages[0] : '',
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
            
            $persistHero = function($payload) use ($heroExists) {
                if ($heroExists) {
                    return supabaseUpdate('hero', $payload, 1);
                }
                $payload['id'] = 1;
                return supabaseInsert('hero', $payload);
            };
            $result = $persistHero($data);
            
            if ($result['success']) {
                $success = 'Beranda berhasil diperbarui di Supabase!';
                // Refresh data while preserving defaults for empty DB values
                $heroResult = supabaseSelect('hero', ['id' => 'eq.1', 'limit' => 1]);
                if ($heroResult['success'] && !empty($heroResult['data'])) {
                    $hero = mergeHeroRowWithDefaults($hero, $heroResult['data'][0]);
                    $bgImages = !empty($hero['background_images']) ? explode(',', $hero['background_images']) : [];
                    $bgImages = array_map('trim', $bgImages);
                    $bgImages = array_filter($bgImages);
                }
            } else {
                $error = 'Gagal memperbarui beranda! ' . ($result['error'] ?? 'Unknown error');
            }
        }
    }
}

// Load homepage sections
$homepageSectionsPath = __DIR__ . '/../includes/homepage_sections.php';
$homepageSections = include $homepageSectionsPath;

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
        <!-- Debug and missing-hero warning removed -->
        
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
                <input type="text" name="motto" value="<?php echo htmlspecialchars($hero['motto'] ?? ''); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> placeholder="Masukkan motto sekolah">
              </div>
              <div style="display:none;">
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Koordinat Lokasi</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Lintang</label>
                    <input type="number" step="any" name="latitude" value="<?php echo htmlspecialchars($hero['latitude'] ?? ''); ?>" class="w-full px-4 pr-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Bujur</label>
                    <input type="number" step="any" name="longitude" value="<?php echo htmlspecialchars($hero['longitude'] ?? ''); ?>" class="w-full px-4 pr-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
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
                    <input type="number" name="tahun_berdiri" value="<?php echo htmlspecialchars($hero['tahun_berdiri'] ?? ''); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> placeholder="Masukkan tahun berdiri">
                    <p class="text-[9px] text-[#9FB5A5] mt-1">Sesuai data beranda</p>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Siswa Aktif</label>
                    <input type="number" name="siswa_aktif" value="<?php echo htmlspecialchars($hero['siswa_aktif'] ?? 1250); ?>" class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded text-gray-500 cursor-not-allowed text-sm" disabled>
                    <p class="text-[9px] text-[#9FB5A5] mt-1">Sesuai data siswa</p>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Alumni</label>
                    <input type="number" name="alumni" value="<?php echo htmlspecialchars($hero['alumni'] ?? ''); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> placeholder="Jumlah alumni">
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Tenaga Pendidik</label>
                    <input type="number" name="tenaga_pendidik" value="<?php echo htmlspecialchars($hero['tenaga_pendidik'] ?? ''); ?>" class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded text-gray-500 cursor-not-allowed text-sm" disabled>
                    <p class="text-[9px] text-[#9FB5A5] mt-1">Sesuai data guru dan tendik</p>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Total Prestasi</label>
                    <input type="number" name="total_prestasi" value="<?php echo htmlspecialchars($hero['total_prestasi'] ?? ''); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> placeholder="Jumlah total prestasi">
                    <p class="text-[9px] text-[#9FB5A5] mt-1">Sesuai data beranda</p>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Jumlah Ruangan</label>
                    <input type="number" name="jumlah_ruangan" value="<?php echo htmlspecialchars($hero['jumlah_ruangan'] ?? ''); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> placeholder="Jumlah ruangan">
                    <p class="text-[9px] text-[#9FB5A5] mt-1">Sesuai data statistik</p>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Buku Paket</label>
                    <input type="number" name="buku_paket" value="<?php echo htmlspecialchars($hero['buku_paket'] ?? ''); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> placeholder="Jumlah buku paket">
                    <p class="text-[9px] text-[#9FB5A5] mt-1">Sesuai data statistik</p>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </form>

        <!-- Homepage Sections Settings -->
        <form method="POST" enctype="multipart/form-data" class="mt-6">
          <div class="bg-white rounded-lg border border-[#E8E4D9] shadow-sm overflow-hidden">
            <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between">
              <h3 class="font-semibold text-[#1F2D26]">Pengaturan Section Beranda</h3>
              <button type="submit" name="save_sections" class="bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest disabled:opacity-50">Simpan Pengaturan</button>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php
                $sectionLabels = [
                    'hero' => 'Hero Section',
                    'running_text' => 'Running Text',
                    'profil' => 'Profil Sekolah',
                    'tentang' => 'Tentang Sekolah',
                    'struktur' => 'Struktur Organisasi',
                    'sumberdaya_preview' => 'Sumber Daya Preview',
                    'program' => 'Program',
                    'fasilitas' => 'Fasilitas',
                    'prestasi' => 'Prestasi',
                    'cta_ppdb' => 'CTA PPDB',
                    'berita' => 'Berita',
                    'galeri' => 'Galeri',
                    'statistik' => 'Statistik',
                    'anggaran' => 'Anggaran',
                    'layanan' => 'Layanan',
                    'faq' => 'FAQ',
                    'testimoni' => 'Testimoni',
                ];
                foreach ($sectionLabels as $key => $label):
                ?>
                <label class="flex items-center justify-between p-4 bg-[#F9F8F4] rounded-lg border border-[#E8E4D9] cursor-pointer hover:bg-[#F0EDE4] transition-colors">
                  <span class="text-sm text-[#1F2D26] font-medium"><?php echo $label; ?></span>
                  <div class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="section_<?php echo $key; ?>" class="sr-only peer" <?php echo isset($homepageSections[$key]) && $homepageSections[$key] ? 'checked' : ''; ?>>
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#3E6B4E]"></div>
                  </div>
                </label>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </form>

        <!-- Public Audio Settings -->
        <form method="POST" enctype="multipart/form-data" class="mt-6">
          <div class="bg-white rounded-lg border border-[#E8E4D9] shadow-sm overflow-hidden">
            <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between">
              <h3 class="font-semibold text-[#1F2D26]">Background Audio</h3>
              <button type="submit" name="save_audio" class="bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest disabled:opacity-50" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>Simpan Audio</button>
            </div>
            <div class="p-6 space-y-6">
              <div class="flex items-center gap-3">
                <input type="checkbox" name="background_audio_enabled" id="background_audio_enabled" value="1" <?php echo $publicAudioEnabled ? 'checked' : ''; ?> class="w-4 h-4 text-[#3E6B4E] border-[#E8E4D9] rounded focus:ring-[#3E6B4E]" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                <label for="background_audio_enabled" class="text-sm font-semibold text-[#1F2D26]">Aktifkan Musik Latar (Tampilkan/Mainkan Musik Latar di Website)</label>
              </div>
              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">URL Audio</label>
                <input type="text" id="background_audio_input" name="background_audio" value="<?php echo htmlspecialchars($publicAudioUrl ?? ''); ?>" placeholder="Contoh: https://example.com/audio.mp3" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> />
              </div>
              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Upload File Audio</label>
                <input type="file" id="background_audio_file" name="background_audio_file" accept="audio/*" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> />
              </div>
              <div id="background_audio_preview" class="space-y-2" <?php echo empty($publicAudioUrl) ? 'hidden' : ''; ?> >
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase">Preview Audio Saat Ini</label>
                <audio id="background_audio_element" src="<?php echo htmlspecialchars($publicAudioUrl ?? ''); ?>" controls preload="metadata" class="w-full rounded-lg border border-[#E8E4D9] bg-[#F9F8F4]">
                  Browser Anda tidak mendukung pemutar audio.
                </audio>
              </div>
              <p class="text-xs text-gray-500">
                <iconify-icon icon="lucide:alert-circle" class="w-3 h-3 inline mr-1"></iconify-icon>
                Gunakan link direct audio MP3/WAV/OGG atau upload file audio. Setelah disimpan, URL akan disimpan di konfigurasi publik.
              </p>
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

    // Copy SQL snippet to clipboard (if present)
    var copyBtn = document.getElementById('copy-sql');
    if (copyBtn) {
      copyBtn.addEventListener('click', function(){
        var el = document.getElementById('hero-sql');
        if (!el) return;
        var txt = el.innerText || el.textContent;
        navigator.clipboard.writeText(txt).then(function(){
          copyBtn.textContent = 'Disalin!';
          setTimeout(function(){ copyBtn.textContent = 'Salin SQL'; }, 2000);
        }).catch(function(){
          alert('Gagal menyalin SQL. Silakan salin manual dari kotak di atas.');
        });
      });
    }

    // Auto-hide success notifications after a short delay
    (function(){
      try {
        var successEls = document.querySelectorAll('.bg-green-100');
        successEls.forEach(function(el){
          // keep visible briefly then fade
          setTimeout(function(){
            el.style.transition = 'opacity 400ms, max-height 400ms, margin 400ms';
            el.style.opacity = '0';
            el.style.maxHeight = '0px';
            el.style.margin = '0px';
            setTimeout(function(){ if (el && el.parentNode) el.parentNode.removeChild(el); }, 500);
          }, 5000);
        });
      } catch(e) {
        // ignore
      }
    })();

    // Hide absolute labels/icons for latitude/longitude (prevent overlap)
    (function(){
      try {
        var lat = document.querySelector('input[name="latitude"]');
        if (lat) {
          var p = lat.closest('.relative');
          if (p) {
            p.querySelectorAll('.absolute').forEach(function(el){ el.style.display = 'none'; });
            // also hide the sibling label if present
            var lbl = p.querySelector('label'); if (lbl) lbl.style.display = 'none';
          }
        }
        var lon = document.querySelector('input[name="longitude"]');
        if (lon) {
          var q = lon.closest('.relative');
          if (q) {
            q.querySelectorAll('.absolute').forEach(function(el){ el.style.display = 'none'; });
            var lbl2 = q.querySelector('label'); if (lbl2) lbl2.style.display = 'none';
          }
        }
      } catch(e) {}
    })();
  </script>
</body>
</html>
