<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/cloudinary.php';
require_login();

$title = "Kelola Galeri — " . SITE_NAME;
$page_title = "Kelola Galeri";
$success = '';
$error = '';

// Handle search query
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle add galeri
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_galeri'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $judul = trim($_POST['judul']);
        $jenis_galeri = $_POST['jenis_galeri'];
        $konten = trim($_POST['konten']);
        $slug = strtolower(str_replace(' ', '-', $judul));
        $tanggal_upload = $_POST['tanggal_upload'] ?? date('Y-m-d');
        $file_url = 'https://picsum.photos/seed/' . time() . '/800/400.jpg'; // Default

        // Handle file upload
        if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['gambar_file'], 'galeri');
            if ($uploadResult['success']) {
                $file_url = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload file: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }

        $data = [
            'judul' => $judul,
            'slug' => $slug,
            'konten' => $konten,
            'file_url' => $file_url,
            'jenis_galeri' => $jenis_galeri,
            'tanggal_upload' => $tanggal_upload,
            'status' => 'published'
        ];

        $result = supabaseInsert('galeri', $data);

        if ($result['success']) {
            $success = 'Galeri berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan galeri! ' . ($result['error'] ?? '');
        }
    }
}

// Handle edit galeri
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_galeri'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $galeriId = $_POST['galeri_id'];
        $judul = trim($_POST['edit_judul']);
        $jenis_galeri = $_POST['edit_jenis_galeri'];
        $konten = trim($_POST['edit_konten']);
        $tanggal_upload = $_POST['edit_tanggal_upload'] ?? date('Y-m-d');

        // Get current data to preserve file if no new file
        $currentResult = supabaseSelect('galeri', ['id' => 'eq.' . $galeriId, 'limit' => 1]);
        $currentFileUrl = 'https://picsum.photos/seed/' . time() . '/800/400.jpg';
        if ($currentResult['success'] && !empty($currentResult['data'])) {
            $currentFileUrl = $currentResult['data'][0]['file_url'] ?? $currentFileUrl;
        }

        // Handle file upload
        if (isset($_FILES['edit_gambar_file']) && $_FILES['edit_gambar_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['edit_gambar_file'], 'galeri');
            if ($uploadResult['success']) {
                $currentFileUrl = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload file: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }

        $data = [
            'judul' => $judul,
            'konten' => $konten,
            'file_url' => $currentFileUrl,
            'jenis_galeri' => $jenis_galeri,
            'tanggal_upload' => $tanggal_upload
        ];

        $result = supabaseUpdate('galeri', $data, $galeriId);

        if ($result['success']) {
            $success = 'Galeri berhasil diperbarui!';
        } else {
            $error = 'Gagal memperbarui galeri! ' . ($result['error'] ?? '');
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        $result = supabaseDelete('galeri', $_GET['delete']);
        if ($result['success']) {
            $success = 'Galeri berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus galeri! ' . ($result['error'] ?? '');
        }
    }
}

// Get all galeri from Supabase with search
$all_galeri = [];
if ($supabaseConnected) {
    $filters = ['order' => 'tanggal_upload.desc'];
    if (!empty($search_query)) {
        $filters['or'] = "(judul.ilike.%$search_query%,konten.ilike.%$search_query%,jenis_galeri.ilike.%$search_query%)";
    }
    $galeriResult = supabaseSelect('galeri', $filters);
    if ($galeriResult['success']) {
        $all_galeri = $galeriResult['data'];
    }
}

// Group galeri by jenis
$groupedGaleri = [
    'Photo' => [],
    'Video' => []
];
foreach ($all_galeri as $g) {
    $jenis = $g['jenis_galeri'] ?? 'Photo';
    if (!isset($groupedGaleri[$jenis])) {
        $groupedGaleri[$jenis] = [];
    }
    $groupedGaleri[$jenis][] = $g;
}

include 'components/head.php';
include 'components/sidebar.php';
?>
  <!-- Main Content -->
  <main class="flex-1 flex flex-col overflow-hidden">
    <?php include 'components/header.php'; ?>
    <div class="flex-1 overflow-y-auto p-8">
      <div class="max-w-7xl">
        <?php if ($success): ?>
          <div class="mb-6 p-4 rounded-lg bg-green-50 border-2 border-green-300 text-green-700 flex items-center gap-3 font-semibold text-sm">
            <iconify-icon icon="lucide:check-circle" class="flex-shrink-0"></iconify-icon>
            <?php echo $success; ?>
          </div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="mb-6 p-4 rounded-lg bg-red-50 border-2 border-red-300 text-red-700 flex items-center gap-3 font-semibold text-sm">
            <iconify-icon icon="lucide:alert-circle" class="flex-shrink-0"></iconify-icon>
            <?php echo $error; ?>
          </div>
        <?php endif; ?>

        <!-- Header Section -->
        <div class="mb-8 space-y-4">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h3 class="text-xl font-semibold text-[#1F2D26]">Daftar Galeri</h3>
            <div class="flex items-center gap-3">
              <!-- View Toggle -->
              <div class="flex items-center bg-white rounded-lg border border-[#E8E4D9] p-1">
                <button id="gridViewBtn" class="px-4 py-2 rounded-md bg-[#3E6B4E] text-white text-xs font-bold transition-colors">
                  <iconify-icon icon="lucide:grid-3x3" class="inline mr-1"></iconify-icon> Grid
                </button>
                <button id="tableViewBtn" class="px-4 py-2 rounded-md text-[#5F6F65] hover:bg-[#F9F8F4] text-xs font-bold transition-colors">
                  <iconify-icon icon="lucide:table" class="inline mr-1"></iconify-icon> Tabel
                </button>
              </div>
              <button onclick="document.getElementById('modalGaleri').classList.remove('hidden')" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded-lg hover:bg-[#2F5B41] transition-colors uppercase tracking-widest flex items-center gap-2 disabled:opacity-50">
                <iconify-icon icon="lucide:plus"></iconify-icon>
                Tambah Galeri Baru
              </button>
            </div>
          </div>

          <!-- Search Form -->
          <div class="bg-white p-4 rounded-xl border border-[#E8E4D9] shadow-sm">
            <div class="flex flex-col md:flex-row gap-4">
              <div class="flex-1 relative">
                <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-[#9FB5A5]"></iconify-icon>
                <input id="searchInput" type="text" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Cari berdasarkan judul, konten, atau jenis galeri..." oninput="filterAdminTable(this)" data-filter-selector="#tableView tbody tr" class="w-full pl-10 pr-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm">
              </div>
              <?php if (!empty($search_query)): ?>
                <div class="flex items-center gap-3">
                  <a href="kelola-galeri.php" class="bg-[#5F6F65] text-white text-xs font-bold px-6 py-3 rounded-lg hover:bg-[#4a5a51] transition-colors uppercase tracking-widest flex items-center gap-2">
                    <iconify-icon icon="lucide:x"></iconify-icon> Reset
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Search Results Info -->
          <?php if (!empty($search_query)): ?>
            <div class="p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg flex items-center gap-2">
              <iconify-icon icon="lucide:info"></iconify-icon>
              Menemukan <?php echo count($all_galeri); ?> hasil pencarian untuk "<?php echo htmlspecialchars($search_query); ?>"
            </div>
          <?php endif; ?>
        </div>

        <?php
        $hasAny = false;
        foreach ($groupedGaleri as $items) {
            if (!empty($items)) {
                $hasAny = true;
                break;
            }
        }

        if (!$hasAny):
        ?>
          <div class="col-span-full text-center py-12 bg-white rounded-xl border border-[#E8E4D9]">
            <iconify-icon icon="lucide:file-plus" class="text-4xl text-[#9FB5A5] mb-4"></iconify-icon>
            <p class="text-[#5F6F65]">Belum ada galeri. Klik tombol "Tambah Galeri Baru" untuk memulai.</p>
          </div>
        <?php else: ?>
          <!-- Grid View -->
          <div id="gridView">
            <?php foreach ($groupedGaleri as $jenis => $items): ?>
              <?php if (!empty($items)): ?>
                <div class="mb-12">
                  <h3 class="text-xl font-semibold text-[#1F2D26] mb-6 flex items-center gap-2">
                    <iconify-icon icon="lucide:<?php echo $jenis === 'Photo' ? 'image' : 'video'; ?>" class="text-[#3E6B4E]"></iconify-icon>
                    <?php echo $jenis; ?>
                  </h3>
                  <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($items as $item): ?>
                      <div class="bg-white rounded-xl border border-[#E8E4D9] shadow-sm overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                        <?php
                        $fileUrl = $item['file_url'] ?? 'https://picsum.photos/seed/default-galeri/800/400.jpg';
                        $isVideo = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION)) === 'mp4' || strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION)) === 'webm' || strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION)) === 'ogg' || strpos(strtolower($fileUrl), 'video') !== false;
                        if ($isVideo):
                        ?>
                          <div class="relative overflow-hidden">
                            <video class="w-full h-48 object-cover transition-transform duration-500 hover:scale-105" muted>
                              <source src="<?php echo htmlspecialchars($fileUrl); ?>" type="video/mp4">
                            </video>
                            <div class="absolute top-4 left-4">
                              <span class="px-3 py-1 bg-[#3E6B4E] text-white text-xs font-bold rounded-full uppercase tracking-wider">
                                Video
                              </span>
                            </div>
                          </div>
                        <?php else: ?>
                          <div class="relative overflow-hidden">
                            <img src="<?php echo htmlspecialchars($fileUrl); ?>" alt="<?php echo htmlspecialchars($item['judul']); ?>" class="w-full h-48 object-cover transition-transform duration-500 hover:scale-105">
                            <div class="absolute top-4 left-4">
                              <span class="px-3 py-1 bg-[#3E6B4E] text-white text-xs font-bold rounded-full uppercase tracking-wider">
                                Photo
                              </span>
                            </div>
                          </div>
                        <?php endif; ?>
                        <div class="p-6">
                          <div class="flex items-center gap-2 mb-3">
                            <iconify-icon icon="lucide:calendar" class="text-[#9FB5A5] w-4 h-4"></iconify-icon>
                            <span class="text-xs text-[#9FB5A5]">
                              <?php
                              $tanggal = isset($item['tanggal_upload']) ? new DateTime($item['tanggal_upload']) : new DateTime();
                              setlocale(LC_TIME, 'id_ID.UTF-8');
                              $tanggalFormatted = strftime('%d %B %Y', $tanggal->getTimestamp());
                              echo htmlspecialchars($tanggalFormatted);
                              ?>
                            </span>
                          </div>
                          <h3 class="font-serif text-lg font-semibold text-[#1F2D26] mb-3 line-clamp-2"><?php echo htmlspecialchars($item['judul']); ?></h3>
                          <p class="text-sm text-[#5F6F65] line-clamp-3 mb-4"><?php echo htmlspecialchars(strip_tags($item['konten'] ?? '')); ?></p>
                          <div class="flex items-center justify-between pt-4 border-t border-[#E8E4D9]">
                            <div class="flex items-center gap-2">
                              <button onclick="openEditGaleriModal('<?php echo htmlspecialchars($item['id']); ?>', '<?php echo addslashes(htmlspecialchars($item['judul'])); ?>', '<?php echo htmlspecialchars($item['jenis_galeri'] ?? 'Photo'); ?>', '<?php echo addslashes(htmlspecialchars($item['konten'] ?? '')); ?>', '<?php echo htmlspecialchars($item['file_url'] ?? ''); ?>', '<?php echo isset($item['tanggal_upload']) ? date('Y-m-d', strtotime($item['tanggal_upload'])) : date('Y-m-d'); ?>')" class="p-2 text-[#3E6B4E] hover:bg-[#3E6B4E]/10 rounded transition-colors">
                                <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                              </button>
                              <a href="?delete=<?php echo $item['id']; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" onclick="return confirm('Yakin ingin menghapus galeri ini?')" class="p-2 text-red-500 hover:bg-red-50 rounded transition-colors">
                                <iconify-icon icon="lucide:trash-2" class="w-5 h-5"></iconify-icon>
                              </a>
                            </div>
                            <button class="text-[#3E6B4E] text-sm font-semibold hover:underline">Lihat Detail →</button>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>

          <!-- Table View -->
          <div id="tableView" class="hidden">
            <?php foreach ($groupedGaleri as $jenis => $items): ?>
              <?php if (!empty($items)): ?>
                <div class="mb-12">
                  <h3 class="text-xl font-semibold text-[#1F2D26] mb-6 flex items-center gap-2">
                    <iconify-icon icon="lucide:<?php echo $jenis === 'Photo' ? 'image' : 'video'; ?>" class="text-[#3E6B4E]"></iconify-icon>
                    <?php echo $jenis; ?>
                  </h3>
                  <div class="bg-white rounded-xl border border-[#E8E4D9] shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                      <table class="w-full">
                        <thead class="bg-gradient-to-r from-[#3E6B4E] to-[#2F5B41]">
                          <tr>
                            <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">File</th>
                            <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Judul</th>
                            <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Tanggal</th>
                            <th class="text-center px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Aksi</th>
                          </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E8E4D9]">
                          <?php foreach ($items as $item): ?>
                            <tr class="hover:bg-[#F9F8F4] transition-all duration-200">
                              <td class="px-4 py-4">
                                <?php
                                $fileUrl = $item['file_url'] ?? 'https://picsum.photos/seed/default-galeri/800/400.jpg';
                                $isVideo = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION)) === 'mp4' || strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION)) === 'webm' || strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION)) === 'ogg' || strpos(strtolower($fileUrl), 'video') !== false;
                                if ($isVideo):
                                ?>
                                  <div class="relative">
                                    <video class="w-16 h-12 object-cover rounded-lg border border-[#E8E4D9]" muted>
                                      <source src="<?php echo htmlspecialchars($fileUrl); ?>" type="video/mp4">
                                    </video>
                                    <iconify-icon icon="lucide:play" class="absolute inset-0 m-auto text-white text-xl"></iconify-icon>
                                  </div>
                                <?php else: ?>
                                  <img src="<?php echo htmlspecialchars($fileUrl); ?>" alt="<?php echo htmlspecialchars($item['judul']); ?>" class="w-16 h-12 object-cover rounded-lg border border-[#E8E4D9]">
                                <?php endif; ?>
                              </td>
                              <td class="px-4 py-4 text-sm text-[#1F2D26] font-semibold max-w-xs truncate"><?php echo htmlspecialchars($item['judul']); ?></td>
                              <td class="px-4 py-4 text-sm text-[#5F6F65]">
                                <?php
                                $tanggal = isset($item['tanggal_upload']) ? new DateTime($item['tanggal_upload']) : new DateTime();
                                echo date('d/m/Y', $tanggal->getTimestamp());
                                ?>
                              </td>
                              <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                  <button onclick="openEditGaleriModal('<?php echo htmlspecialchars($item['id']); ?>', '<?php echo addslashes(htmlspecialchars($item['judul'])); ?>', '<?php echo htmlspecialchars($item['jenis_galeri'] ?? 'Photo'); ?>', '<?php echo addslashes(htmlspecialchars($item['konten'] ?? '')); ?>', '<?php echo htmlspecialchars($item['file_url'] ?? ''); ?>', '<?php echo isset($item['tanggal_upload']) ? date('Y-m-d', strtotime($item['tanggal_upload'])) : date('Y-m-d'); ?>')" class="p-2 text-blue-500 hover:bg-blue-50 rounded transition-colors">
                                    <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                                  </button>
                                  <a href="?delete=<?php echo $item['id']; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" onclick="return confirm('Yakin ingin menghapus galeri ini?')" class="p-2 text-red-500 hover:bg-red-50 rounded transition-colors">
                                    <iconify-icon icon="lucide:trash-2" class="w-5 h-5"></iconify-icon>
                                  </a>
                                </div>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <!-- Modal Tambah Galeri -->
  <div id="modalGaleri" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('modalGaleri').classList.add('hidden')"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full overflow-hidden">
        <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between bg-gradient-to-r from-[#3E6B4E] to-[#2F5B41]">
          <h3 class="font-serif text-xl text-white">Tambah Galeri Baru</h3>
          <button onclick="document.getElementById('modalGaleri').classList.add('hidden')" class="text-white/80 hover:text-white transition-colors">
            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
          </button>
        </div>
        <form action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Judul Galeri</label>
            <input type="text" name="judul" required placeholder="Masukkan judul galeri..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
          </div>
          <div class="grid md:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Tanggal Upload</label>
              <input type="date" name="tanggal_upload" value="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            </div>
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Jenis Galeri</label>
              <select name="jenis_galeri" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                <option value="Photo">Photo</option>
                <option value="Video">Video</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">File (Gambar/Video)</label>
            <input type="file" name="gambar_file" accept="image/*,video/*" id="gambarGaleriInput" onchange="previewFile(event, 'gambarGaleriPreview')" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            <div id="gambarGaleriPreview" class="mt-3">
              <p class="text-xs text-[#9FB5A5] italic">Preview file akan muncul di sini</p>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Konten Galeri</label>
            <textarea name="konten" rows="6" required placeholder="Tulis konten galeri di sini..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm resize-none" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>></textarea>
          </div>
          <div class="flex items-center gap-4 pt-4">
            <button type="button" onclick="document.getElementById('modalGaleri').classList.add('hidden')" class="flex-1 px-6 py-3 rounded-lg border-2 border-[#E8E4D9] text-[#5F6F65] hover:bg-[#F9F8F4] transition-all font-semibold text-sm">Batal</button>
            <button type="submit" name="tambah_galeri" class="flex-1 bg-[#3E6B4E] text-white text-xs font-bold px-8 py-3 rounded-lg hover:bg-[#2F5B41] transition-all uppercase tracking-widest" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>Simpan Galeri</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Edit Galeri -->
  <div id="modalEditGaleri" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('modalEditGaleri').classList.add('hidden')"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full overflow-hidden">
        <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between bg-gradient-to-r from-[#3E6B4E] to-[#2F5B41]">
          <h3 class="font-serif text-xl text-white">Edit Galeri</h3>
          <button onclick="document.getElementById('modalEditGaleri').classList.add('hidden')" class="text-white/80 hover:text-white transition-colors">
            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
          </button>
        </div>
        <form action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
          <input type="hidden" name="galeri_id" id="edit_galeri_id" value="">
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Judul Galeri</label>
            <input type="text" name="edit_judul" id="edit_judul" required placeholder="Masukkan judul galeri..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
          </div>
          <div class="grid md:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Tanggal Upload</label>
              <input type="date" name="edit_tanggal_upload" id="edit_tanggal_upload" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            </div>
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Jenis Galeri</label>
              <select name="edit_jenis_galeri" id="edit_jenis_galeri" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                <option value="Photo">Photo</option>
                <option value="Video">Video</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">File (Gambar/Video, biarkan kosong untuk tidak mengedit)</label>
            <input type="file" name="edit_gambar_file" accept="image/*,video/*" id="editGambarGaleriInput" onchange="previewFile(event, 'editGambarGaleriPreview')" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            <div id="editGambarGaleriPreview" class="mt-3">
              <p class="text-xs text-[#9FB5A5] italic">Preview file akan muncul di sini</p>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Konten Galeri</label>
            <textarea name="edit_konten" id="edit_konten" rows="6" required placeholder="Tulis konten galeri di sini..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm resize-none" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>></textarea>
          </div>
          <div class="flex items-center gap-4 pt-4">
            <button type="button" onclick="document.getElementById('modalEditGaleri').classList.add('hidden')" class="flex-1 px-6 py-3 rounded-lg border-2 border-[#E8E4D9] text-[#5F6F65] hover:bg-[#F9F8F4] transition-all font-semibold text-sm">Batal</button>
            <button type="submit" name="edit_galeri" class="flex-1 bg-[#3E6B4E] text-white text-xs font-bold px-8 py-3 rounded-lg hover:bg-[#2F5B41] transition-all uppercase tracking-widest" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function previewFile(event, previewId) {
      const previewDiv = document.getElementById(previewId);
      const file = event.target.files[0];

      if (file) {
        const fileType = file.type;
        const reader = new FileReader();
        reader.onload = function(e) {
          if (fileType.startsWith('image/')) {
            previewDiv.innerHTML = `
              <img src="${e.target.result}" alt="Preview" class="w-full h-48 object-cover rounded-lg border-2 border-[#E8E4D9]">
            `;
          } else if (fileType.startsWith('video/')) {
            previewDiv.innerHTML = `
              <video controls class="w-full h-48 object-cover rounded-lg border-2 border-[#E8E4D9]">
                <source src="${e.target.result}" type="${fileType}">
                Your browser does not support the video tag.
              </video>
            `;
          } else {
            previewDiv.innerHTML = `
              <p class="text-xs text-[#9FB5A5] italic">File tidak didukung untuk preview</p>
            `;
          }
        };
        reader.readAsDataURL(file);
      } else {
        previewDiv.innerHTML = `
          <p class="text-xs text-[#9FB5A5] italic">Preview file akan muncul di sini</p>
        `;
      }
    }

    function openEditGaleriModal(id, judul, jenisGaleri, konten, fileUrl, tanggalUpload) {
      document.getElementById('edit_galeri_id').value = id;
      document.getElementById('edit_judul').value = judul;
      document.getElementById('edit_jenis_galeri').value = jenisGaleri;
      document.getElementById('edit_konten').value = konten;
      document.getElementById('edit_tanggal_upload').value = tanggalUpload;

      // Check if the current file is a video (JS, not PHP!)
      const isVideo = fileUrl && (fileUrl.toLowerCase().endsWith('.mp4') || fileUrl.toLowerCase().endsWith('.webm') || fileUrl.toLowerCase().endsWith('.ogg') || fileUrl.toLowerCase().includes('video'));

      if (isVideo) {
        document.getElementById('editGambarGaleriPreview').innerHTML = `
          <video controls class="w-full h-48 object-cover rounded-lg border-2 border-[#E8E4D9]">
            <source src="${fileUrl}" type="video/mp4">
            Your browser does not support the video tag.
          </video>
        `;
      } else {
        document.getElementById('editGambarGaleriPreview').innerHTML = `
          <img src="${fileUrl}" alt="Current File" class="w-full h-48 object-cover rounded-lg border-2 border-[#E8E4D9]">
        `;
      }

      document.getElementById('modalEditGaleri').classList.remove('hidden');
    }

    // View Toggle Functionality
    const gridViewBtn = document.getElementById('gridViewBtn');
    const tableViewBtn = document.getElementById('tableViewBtn');
    const gridView = document.getElementById('gridView');
    const tableView = document.getElementById('tableView');

    function setView(view) {
      if (view === 'grid') {
        gridView.classList.remove('hidden');
        tableView.classList.add('hidden');
        gridViewBtn.classList.add('bg-[#3E6B4E]', 'text-white');
        gridViewBtn.classList.remove('text-[#5F6F65]', 'hover:bg-[#F9F8F4]');
        tableViewBtn.classList.remove('bg-[#3E6B4E]', 'text-white');
        tableViewBtn.classList.add('text-[#5F6F65]', 'hover:bg-[#F9F8F4]');
      } else {
        gridView.classList.add('hidden');
        tableView.classList.remove('hidden');
        tableViewBtn.classList.add('bg-[#3E6B4E]', 'text-white');
        tableViewBtn.classList.remove('text-[#5F6F65]', 'hover:bg-[#F9F8F4]');
        gridViewBtn.classList.remove('bg-[#3E6B4E]', 'text-white');
        gridViewBtn.classList.add('text-[#5F6F65]', 'hover:bg-[#F9F8F4]');
      }
      localStorage.setItem('galeriView', view);
    }

    if (gridViewBtn && tableViewBtn) {
      gridViewBtn.addEventListener('click', function() {
        setView('grid');
      });

      tableViewBtn.addEventListener('click', function() {
        setView('table');
      });
    }

    // Initialize view
    document.addEventListener('DOMContentLoaded', function() {
      const savedView = localStorage.getItem('galeriView') || 'table';
      setView(savedView);
    });
  </script>
</body>
</html>
