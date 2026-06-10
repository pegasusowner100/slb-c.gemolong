<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/cloudinary.php';
require_login();

$title = "Kelola Berita — " . SITE_NAME;
$page_title = "Kelola Berita";
$success = '';
$error = '';

// Handle search query
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle add berita
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_berita'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $judul = trim($_POST['judul']);
        $kategori = $_POST['kategori'];
        $konten = trim($_POST['konten']);
        $slug = strtolower(str_replace(' ', '-', $judul));
        $tanggal_publish = $_POST['tanggal_publish'] ?? date('Y-m-d');
        $status = $_POST['status'];
        $thumbnail = 'https://picsum.photos/seed/' . time() . '/800/400.jpg'; // Default
        
        // Handle file upload
        if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['thumbnail_file'], 'berita');
            if ($uploadResult['success']) {
                $thumbnail = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload thumbnail: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }

        $data = [
            'judul' => $judul,
            'slug' => $slug,
            'konten' => $konten,
            'gambar' => $thumbnail,
            'kategori' => $kategori,
            'tanggal_upload' => $tanggal_publish,
            'status' => $status
        ];
        
        $result = supabaseInsert('berita', $data);
        
        if ($result['success']) {
            $success = 'Berita berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan berita!';
        }
    }
}

// Handle edit berita
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_berita'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $beritaId = $_POST['berita_id'];
        $judul = trim($_POST['edit_judul']);
        $kategori = $_POST['edit_kategori'];
        $konten = trim($_POST['edit_konten']);
        $tanggal_publish = $_POST['edit_tanggal_publish'] ?? date('Y-m-d');
        $status = $_POST['edit_status'];
        
        // Get current data to preserve gambar if no new file
        $currentResult = supabaseSelect('berita', ['id' => 'eq.' . $beritaId, 'limit' => 1]);
        $currentThumbnail = 'https://picsum.photos/seed/' . time() . '/800/400.jpg';
        if ($currentResult['success'] && !empty($currentResult['data'])) {
            $currentThumbnail = $currentResult['data'][0]['gambar'] ?? $currentThumbnail;
        }
        
        // Handle file upload
        if (isset($_FILES['edit_thumbnail_file']) && $_FILES['edit_thumbnail_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['edit_thumbnail_file'], 'berita');
            if ($uploadResult['success']) {
                $currentThumbnail = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload thumbnail: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }

        $data = [
            'judul' => $judul,
            'konten' => $konten,
            'gambar' => $currentThumbnail,
            'kategori' => $kategori,
            'tanggal_upload' => $tanggal_publish,
            'status' => $status
        ];
        
        $result = supabaseUpdate('berita', $data, $beritaId);
        
        if ($result['success']) {
            $success = 'Berita berhasil diperbarui!';
        } else {
            $error = 'Gagal memperbarui berita!';
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        $result = supabaseDelete('berita', $_GET['delete']);
        if ($result['success']) {
            $success = 'Berita berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus berita!';
        }
    }
}

// Get all berita from Supabase with search
$all_berita = [];
if ($supabaseConnected) {
    $filters = ['order' => 'tanggal.desc'];
    if (!empty($search_query)) {
        $filters['or'] = "(judul.ilike.%$search_query%,konten.ilike.%$search_query%,kategori.ilike.%$search_query%)";
    }
    $beritaResult = supabaseSelect('berita', $filters);
    if ($beritaResult['success']) {
        $all_berita = $beritaResult['data'];
    }
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
            <h3 class="text-xl font-semibold text-[#1F2D26]">Daftar Berita</h3>
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
              <button onclick="document.getElementById('modalBerita').classList.remove('hidden')" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded-lg hover:bg-[#2F5B41] transition-colors uppercase tracking-widest flex items-center gap-2 disabled:opacity-50">
                <iconify-icon icon="lucide:plus"></iconify-icon>
                Tambah Berita Baru
              </button>
            </div>
          </div>
          
          <!-- Search Form -->
          <div class="bg-white p-4 rounded-xl border border-[#E8E4D9] shadow-sm">
            <div class="flex flex-col md:flex-row gap-4">
              <div class="flex-1 relative">
                <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-[#9FB5A5]"></iconify-icon>
                <input id="searchInput" type="text" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Cari berdasarkan judul, konten, atau kategori..." oninput="filterAdminTable(this)" data-filter-selector="#gridView .bg-white.rounded-xl, #tableView tbody tr" class="w-full pl-10 pr-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm">
              </div>
              <?php if (!empty($search_query)): ?>
                <div class="flex items-center gap-3">
                  <a href="kelola-berita.php" class="bg-[#5F6F65] text-white text-xs font-bold px-6 py-3 rounded-lg hover:bg-[#4a5a51] transition-colors uppercase tracking-widest flex items-center gap-2">
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
              Menemukan <?php echo count($all_berita); ?> hasil pencarian untuk "<?php echo htmlspecialchars($search_query); ?>"
            </div>
          <?php endif; ?>
        </div>

        <?php if (empty($all_berita)): ?>
          <div class="col-span-full text-center py-12 bg-white rounded-xl border border-[#E8E4D9]">
            <iconify-icon icon="lucide:newspaper" class="text-4xl text-[#9FB5A5] mb-4"></iconify-icon>
            <p class="text-[#5F6F65]">Belum ada berita. Klik tombol "Tambah Berita Baru" untuk memulai.</p>
          </div>
        <?php else: ?>
          <!-- Grid View (Default) -->
          <div id="gridView" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($all_berita as $b): ?>
              <div class="bg-white rounded-xl border border-[#E8E4D9] shadow-sm overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <div class="relative overflow-hidden">
                  <img src="<?php echo htmlspecialchars($b['gambar'] ?? 'https://picsum.photos/seed/default-berita/800/400.jpg'); ?>" alt="<?php echo htmlspecialchars($b['judul']); ?>" class="w-full h-48 object-cover transition-transform duration-500 hover:scale-105">
                  <div class="absolute top-4 left-4">
                    <span class="px-3 py-1 bg-[#3E6B4E] text-white text-xs font-bold rounded-full uppercase tracking-wider">
                      <?php echo htmlspecialchars($b['kategori'] ?? 'Umum'); ?>
                    </span>
                  </div>
                  <?php if (($b['status'] ?? '') === 'draft'): ?>
                    <div class="absolute top-4 right-4">
                      <span class="px-3 py-1 bg-yellow-500 text-white text-xs font-bold rounded-full uppercase tracking-wider">
                        Draft
                      </span>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="p-6">
                  <div class="flex items-center gap-2 mb-3">
                    <iconify-icon icon="lucide:calendar" class="text-[#9FB5A5] w-4 h-4"></iconify-icon>
                    <span class="text-xs text-[#9FB5A5]">
                      <?php 
                      $tanggal = isset($b['tanggal_upload']) ? new DateTime($b['tanggal_upload']) : new DateTime();
                      setlocale(LC_TIME, 'id_ID.UTF-8');
                      $tanggalFormatted = strftime('%d %B %Y', $tanggal->getTimestamp());
                      echo htmlspecialchars($tanggalFormatted);
                      ?>
                    </span>
                  </div>
                  <h3 class="font-serif text-lg font-semibold text-[#1F2D26] mb-3 line-clamp-2"><?php echo htmlspecialchars($b['judul']); ?></h3>
                  <p class="text-sm text-[#5F6F65] line-clamp-3 mb-4"><?php echo htmlspecialchars(strip_tags($b['konten'] ?? '')); ?></p>
                  <div class="flex items-center justify-between pt-4 border-t border-[#E8E4D9]">
                    <div class="flex items-center gap-2">
                      <button onclick="openEditBeritaModal('<?php echo htmlspecialchars($b['id']); ?>', '<?php echo addslashes(htmlspecialchars($b['judul'])); ?>', '<?php echo htmlspecialchars($b['kategori'] ?? 'Umum'); ?>', '<?php echo addslashes(htmlspecialchars($b['konten'] ?? '')); ?>', '<?php echo htmlspecialchars($b['gambar'] ?? ''); ?>', '<?php echo isset($b['tanggal_upload']) ? date('Y-m-d', strtotime($b['tanggal_upload'])) : date('Y-m-d'); ?>', '<?php echo htmlspecialchars($b['status'] ?? 'published'); ?>')" class="p-2 text-[#3E6B4E] hover:bg-[#3E6B4E]/10 rounded transition-colors">
                        <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                      </button>
                      <a href="?delete=<?php echo $b['id']; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" onclick="return confirm('Yakin ingin menghapus berita ini?')" class="p-2 text-red-500 hover:bg-red-50 rounded transition-colors">
                        <iconify-icon icon="lucide:trash-2" class="w-5 h-5"></iconify-icon>
                      </a>
                    </div>
                    <button class="text-[#3E6B4E] text-sm font-semibold hover:underline">Baca Selengkapnya →</button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          
          <!-- Table View (Hidden by Default) -->
          <div id="tableView" class="hidden">
            <div class="bg-white rounded-xl border border-[#E8E4D9] shadow-sm overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-gradient-to-r from-[#3E6B4E] to-[#2F5B41]">
                    <tr>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Thumbnail</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Judul</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Kategori</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Tanggal</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Status</th>
                      <th class="text-center px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-[#E8E4D9]">
                    <?php foreach ($all_berita as $b): ?>
                      <tr class="hover:bg-[#F9F8F4] transition-all duration-200">
                        <td class="px-4 py-4">
                          <img src="<?php echo htmlspecialchars($b['gambar'] ?? 'https://picsum.photos/seed/default-berita/800/400.jpg'); ?>" alt="<?php echo htmlspecialchars($b['judul']); ?>" class="w-16 h-12 object-cover rounded-lg border border-[#E8E4D9]">
                        </td>
                        <td class="px-4 py-4 text-sm text-[#1F2D26] font-semibold max-w-xs truncate"><?php echo htmlspecialchars($b['judul']); ?></td>
                        <td class="px-4 py-4 text-sm text-[#5F6F65]"><?php echo htmlspecialchars($b['kategori'] ?? 'Umum'); ?></td>
                        <td class="px-4 py-4 text-sm text-[#5F6F65]">
                          <?php 
                          $tanggal = isset($b['tanggal_upload']) ? new DateTime($b['tanggal_upload']) : new DateTime();
                          echo date('d/m/Y', $tanggal->getTimestamp());
                          ?>
                        </td>
                        <td class="px-4 py-4">
                          <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo (($b['status'] ?? '') === 'draft') ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'; ?>">
                            <?php echo ucfirst($b['status'] ?? 'published'); ?>
                          </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                          <div class="flex items-center justify-center gap-2">
                            <button onclick="openEditBeritaModal('<?php echo htmlspecialchars($b['id']); ?>', '<?php echo addslashes(htmlspecialchars($b['judul'])); ?>', '<?php echo htmlspecialchars($b['kategori'] ?? 'Umum'); ?>', '<?php echo addslashes(htmlspecialchars($b['konten'] ?? '')); ?>', '<?php echo htmlspecialchars($b['gambar'] ?? ''); ?>', '<?php echo isset($b['tanggal_upload']) ? date('Y-m-d', strtotime($b['tanggal_upload'])) : date('Y-m-d'); ?>', '<?php echo htmlspecialchars($b['status'] ?? 'published'); ?>')" class="p-2 text-blue-500 hover:bg-blue-50 rounded transition-colors">
                              <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                            </button>
                            <a href="?delete=<?php echo $b['id']; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" onclick="return confirm('Yakin ingin menghapus berita ini?')" class="p-2 text-red-500 hover:bg-red-50 rounded transition-colors">
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
      </div>
    </div>
  </main>

  <!-- Modal Tambah Berita -->
  <div id="modalBerita" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('modalBerita').classList.add('hidden')"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full overflow-hidden">
        <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between bg-gradient-to-r from-[#3E6B4E] to-[#2F5B41]">
          <h3 class="font-serif text-xl text-white">Tambah Berita Baru</h3>
          <button onclick="document.getElementById('modalBerita').classList.add('hidden')" class="text-white/80 hover:text-white transition-colors">
            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
          </button>
        </div>
        <form action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
          <div class="grid md:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-bold text-[#1F2D26] uppercase mb-2">Judul Berita</label>
              <input type="text" name="judul" required placeholder="Masukkan judul berita..." class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3E6B4E] focus:border-transparent transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-[#1F2D26] uppercase mb-2">Kategori</label>
                <select name="kategori" class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3E6B4E] focus:border-transparent transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  <option value="Umum">Umum</option>
                  <option value="Prestasi">Prestasi</option>
                  <option value="Kegiatan">Kegiatan</option>
                  <option value="PPDB">PPDB</option>
                  <option value="Pengumuman">Pengumuman</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold text-[#1F2D26] uppercase mb-2">Status</label>
                <select name="status" class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3E6B4E] focus:border-transparent transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  <option value="published">Publish</option>
                  <option value="draft">Draft</option>
                </select>
              </div>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#1F2D26] uppercase mb-2">Tanggal Publish</label>
            <input type="date" name="tanggal_publish" value="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3E6B4E] focus:border-transparent transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#1F2D26] uppercase mb-2">Thumbnail Berita</label>
            <input type="file" name="thumbnail_file" accept="image/*" id="thumbnailInput" onchange="previewFile(event, 'thumbnailPreview')" class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3E6B4E] focus:border-transparent transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            <div id="thumbnailPreview" class="mt-3">
              <p class="text-xs text-slate-600 italic">Preview thumbnail akan muncul di sini</p>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#1F2D26] uppercase mb-2">Konten Berita</label>
            <textarea name="konten" rows="8" required placeholder="Tulis konten berita di sini..." class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3E6B4E] focus:border-transparent transition-all text-sm resize-none" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>></textarea>
          </div>
          <div class="flex items-center gap-4 pt-4 border-t border-slate-200">
            <button type="button" onclick="document.getElementById('modalBerita').classList.add('hidden')" class="flex-1 px-6 py-3 rounded-lg bg-slate-100 hover:bg-slate-200 text-[#1F2D26] transition-all font-semibold text-sm uppercase tracking-widest">Batal</button>
            <button type="submit" name="tambah_berita" class="flex-1 bg-gradient-to-r from-[#3E6B4E] to-[#3E6B4E]/80 hover:from-[#2F5340] hover:to-[#2F5340]/80 text-white text-xs font-bold px-8 py-3 rounded-lg transition-all uppercase tracking-widest shadow-md hover:shadow-lg" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>Simpan Berita</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Edit Berita -->
  <div id="modalEditBerita" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('modalEditBerita').classList.add('hidden')"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full overflow-hidden">
        <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between bg-gradient-to-r from-[#3E6B4E] to-[#2F5B41]">
          <h3 class="font-serif text-xl text-white">Edit Berita</h3>
          <button onclick="document.getElementById('modalEditBerita').classList.add('hidden')" class="text-white/80 hover:text-white transition-colors">
            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
          </button>
        </div>
        <form action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
          <input type="hidden" name="berita_id" id="edit_berita_id" value="">
          <div class="grid md:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-bold text-[#1F2D26] uppercase mb-2">Judul Berita</label>
              <input type="text" name="edit_judul" id="edit_judul" required placeholder="Masukkan judul berita..." class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3E6B4E] focus:border-transparent transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-[#1F2D26] uppercase mb-2">Kategori</label>
                <select name="edit_kategori" id="edit_kategori" class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3E6B4E] focus:border-transparent transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  <option value="Umum">Umum</option>
                  <option value="Prestasi">Prestasi</option>
                  <option value="Kegiatan">Kegiatan</option>
                  <option value="PPDB">PPDB</option>
                  <option value="Pengumuman">Pengumuman</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Status</label>
                <select name="edit_status" id="edit_status" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  <option value="published">Publish</option>
                  <option value="draft">Draft</option>
                </select>
              </div>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Tanggal Publish</label>
            <input type="date" name="edit_tanggal_publish" id="edit_tanggal_publish" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Thumbnail Berita (biarkan kosong untuk tidak mengedit)</label>
            <input type="file" name="edit_thumbnail_file" accept="image/*" id="editThumbnailInput" onchange="previewFile(event, 'editThumbnailPreview')" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            <div id="editThumbnailPreview" class="mt-3">
              <p class="text-xs text-[#9FB5A5] italic">Preview thumbnail akan muncul di sini</p>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Konten Berita</label>
            <textarea name="edit_konten" id="edit_konten" rows="8" required placeholder="Tulis konten berita di sini..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm resize-none" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>></textarea>
          </div>
          <div class="flex items-center gap-4 pt-4">
            <button type="button" onclick="document.getElementById('modalEditBerita').classList.add('hidden')" class="flex-1 px-6 py-3 rounded-lg border-2 border-[#E8E4D9] text-[#5F6F65] hover:bg-[#F9F8F4] transition-all font-semibold text-sm">Batal</button>
            <button type="submit" name="edit_berita" class="flex-1 bg-[#3E6B4E] text-white text-xs font-bold px-8 py-3 rounded-lg hover:bg-[#2F5B41] transition-all uppercase tracking-widest" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>Simpan Perubahan</button>
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

    function openEditBeritaModal(id, judul, kategori, konten, gambar, tanggalPublish, status) {
      document.getElementById('edit_berita_id').value = id;
      document.getElementById('edit_judul').value = judul;
      document.getElementById('edit_kategori').value = kategori;
      document.getElementById('edit_konten').value = konten;
      document.getElementById('edit_tanggal_publish').value = tanggalPublish;
      document.getElementById('edit_status').value = status;

      document.getElementById('editThumbnailPreview').innerHTML = `
        <img src="${gambar}" alt="Current Thumbnail" class="w-full h-48 object-cover rounded-lg border-2 border-[#E8E4D9]">
      `;

      document.getElementById('modalEditBerita').classList.remove('hidden');
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
      localStorage.setItem('beritaView', view);
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
      const savedView = localStorage.getItem('beritaView') || 'table';
      setView(savedView);
    });
  </script>
</body>
</html>
