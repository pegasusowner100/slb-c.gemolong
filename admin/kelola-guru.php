<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/cloudinary.php';
require_login();

$title = "Kelola Guru & Staff — SLB-C YPSLB Gemolong";
$page_title = "Kelola Guru";
$success = '';
$error = '';

// Handle search query
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle Add Guru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_guru'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $foto = 'https://i.pravatar.cc/150'; // Default
        
        // Handle file upload
        if (isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['foto_file'], 'guru');
            if ($uploadResult['success']) {
                $foto = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload gambar: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }
        
        $data = [
            'nama' => $_POST['nama'],
            'nip' => $_POST['nip'],
            'jabatan' => $_POST['jabatan'],
          'mapel' => $_POST['mapel'],
          'urutan' => isset($_POST['urutan']) ? intval($_POST['urutan']) : 0,
            'foto' => $foto
        ];
        $result = supabaseInsert('guru', $data);
        if ($result['success']) {
            $success = 'Guru berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan guru!';
        }
    }
}

// Handle Edit Guru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_guru'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $id = $_POST['id'];
        $currentData = [];
        $guruResult = supabaseSelect('guru', ['id' => 'eq.' . $id, 'limit' => 1]);
        if ($guruResult['success'] && !empty($guruResult['data'])) {
            $currentData = $guruResult['data'][0];
        }
        
        $foto = $currentData['foto'] ?? 'https://i.pravatar.cc/150';
        
        if (isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['foto_file'], 'guru');
            if ($uploadResult['success']) {
                $foto = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload gambar: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }
        
        $data = [
          'nama' => $_POST['nama'],
          'nip' => $_POST['nip'],
          'jabatan' => $_POST['jabatan'],
          'mapel' => $_POST['mapel'],
          'urutan' => isset($_POST['urutan']) ? intval($_POST['urutan']) : 0,
          'foto' => $foto
        ];
        $result = supabaseUpdate('guru', $data, $id);
        if ($result['success']) {
            $success = 'Guru berhasil diperbarui!';
        } else {
            $error = 'Gagal memperbarui guru!';
        }
    }
}

// Handle Delete Guru
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        $result = supabaseDelete('guru', $_GET['delete']);
        if ($result['success']) {
            $success = 'Guru berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus guru!';
        }
    }
}

// Get All Guru with search
$guru_list = [];
if ($supabaseConnected) {
    $filters = ['order' => 'urutan.asc'];
    if (!empty($search_query)) {
        $filters['or'] = "(nama.ilike.%$search_query%,nip.ilike.%$search_query%,jabatan.ilike.%$search_query%,mapel.ilike.%$search_query%)";
    }
    $guruResult = supabaseSelect('guru', $filters);
    if (!$guruResult['success']) {
        // Jika kolom urutan belum ada atau query gagal, fallback ke created_at
        $filters = ['order' => 'created_at.asc'];
        if (!empty($search_query)) {
            $filters['or'] = "(nama.ilike.%$search_query%,nip.ilike.%$search_query%,jabatan.ilike.%$search_query%,mapel.ilike.%$search_query%)";
        }
        $guruResult = supabaseSelect('guru', $filters);
    }
    if (!$guruResult['success']) {
        // Jika masih gagal, fallback ke query tanpa order
        $filters = [];
        if (!empty($search_query)) {
            $filters['or'] = "(nama.ilike.%$search_query%,nip.ilike.%$search_query%,jabatan.ilike.%$search_query%,mapel.ilike.%$search_query%)";
        }
        $guruResult = supabaseSelect('guru', $filters);
    }
    if ($guruResult['success']) {
        $guru_list = $guruResult['data'];
    } elseif (empty($error)) {
        $error = 'Gagal memuat data guru dari database.';
    }
}
$no_urut = 1;

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
        
        <?php if (!$supabaseConnected): ?>
          <div class="mb-6 p-4 bg-yellow-50 text-yellow-800 border border-yellow-200 rounded-lg">
            <iconify-icon icon="lucide:alert-triangle" class="inline mr-2"></iconify-icon>
            PERINGATAN: Supabase tidak terhubung! Perubahan tidak dapat disimpan.
          </div>
        <?php endif; ?>
        
        <!-- Header Section -->
        <div class="mb-8 space-y-4">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h2 class="text-2xl font-semibold text-[#1F2D26]">Daftar Guru & Tendik</h2>
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
              <button onclick="document.getElementById('modalGuru').classList.remove('hidden')" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded-lg hover:bg-[#2F5B41] transition-colors uppercase tracking-widest flex items-center gap-2 disabled:opacity-50">
                <iconify-icon icon="lucide:plus"></iconify-icon> Tambah Guru
              </button>
            </div>
          </div>
          
          <!-- Search Form -->
          <div class="bg-white p-4 rounded-xl border border-[#E8E4D9] shadow-sm">
            <div class="flex flex-col md:flex-row gap-4">
              <div class="flex-1 relative">
                <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-[#9FB5A5]"></iconify-icon>
                <input id="searchInput" type="text" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Cari berdasarkan nama, NIP, jabatan, atau mapel..." oninput="filterAdminTable(this)" data-filter-selector="#tableView tbody tr, #gridView .bg-white.rounded-xl" class="w-full pl-10 pr-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm">
              </div>
              <?php if (!empty($search_query)): ?>
                <div class="flex items-center gap-3">
                  <a href="kelola-guru.php" class="bg-[#5F6F65] text-white text-xs font-bold px-6 py-3 rounded-lg hover:bg-[#4a5a51] transition-colors uppercase tracking-widest flex items-center gap-2">
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
              Menemukan <?php echo count($guru_list); ?> hasil pencarian untuk "<?php echo htmlspecialchars($search_query); ?>"
            </div>
          <?php endif; ?>
        </div>
        
        <!-- Guru List - Grid View -->
        <?php if (empty($guru_list)): ?>
          <div class="text-center py-12 bg-white rounded-xl border border-[#E8E4D9]">
            <iconify-icon icon="lucide:users" class="text-4xl text-[#9FB5A5] mb-4"></iconify-icon>
            <p class="text-[#5F6F65]">Belum ada data guru.</p>
          </div>
        <?php else: ?>
          <!-- Grid View -->
          <div id="gridView" class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($guru_list as $guru): ?>
              <div class="bg-white rounded-xl border border-[#E8E4D9] shadow-sm overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <div class="p-6 text-center">
                  <img src="<?php echo htmlspecialchars($guru['foto'] ?? 'https://i.pravatar.cc/150'); ?>" alt="<?php echo htmlspecialchars($guru['nama']); ?>" class="w-24 h-24 rounded-full object-cover border-4 border-[#3E6B4E]/20 mx-auto mb-4 shadow-md">
                  <h3 class="font-serif text-lg font-semibold text-[#1F2D26] mb-1"><?php echo htmlspecialchars($guru['nama']); ?></h3>
                  <p class="text-sm text-[#9FB5A5] mb-2"><?php echo htmlspecialchars($guru['jabatan']); ?></p>
                  <?php if (!empty($guru['nip'])): ?>
                    <p class="text-xs text-[#5F6F65] mb-2">NIP: <?php echo htmlspecialchars($guru['nip']); ?></p>
                  <?php endif; ?>
                  <?php if (!empty($guru['mapel'])): ?>
                    <p class="text-xs text-[#5F6F65] mb-4">Mapel: <?php echo htmlspecialchars($guru['mapel']); ?></p>
                  <?php endif; ?>
                  <div class="flex items-center justify-center gap-2 pt-4 border-t border-[#E8E4D9]">
                    <button onclick="openEditGuruModal('<?php echo htmlspecialchars($guru['id']); ?>', '<?php echo htmlspecialchars($guru['nama']); ?>', '<?php echo htmlspecialchars($guru['nip'] ?? ''); ?>', '<?php echo htmlspecialchars($guru['jabatan']); ?>', '<?php echo htmlspecialchars($guru['mapel'] ?? ''); ?>', '<?php echo htmlspecialchars($guru['foto'] ?? 'https://i.pravatar.cc/150'); ?>', '<?php echo htmlspecialchars($guru['urutan'] ?? 0); ?>')" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors">
                      <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                    </button>
                    <a href="?delete=<?php echo $guru['id']; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" onclick="return confirm('Yakin ingin menghapus guru ini?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                      <iconify-icon icon="lucide:trash-2" class="w-5 h-5"></iconify-icon>
                    </a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          
          <!-- Table View (Hidden by default) -->
          <div id="tableView" class="hidden">
            <div class="bg-white rounded-xl border border-[#E8E4D9] shadow-sm overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-gradient-to-r from-[#3E6B4E] to-[#2F5B41]">
                    <tr>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Geser</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">No</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Foto</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Nama</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">NIP</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Jabatan</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Mapel</th>
                      <th class="text-center px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Aksi</th>
                    </tr>
                  </thead>
                  <tbody id="draggableGuruTableBody" class="divide-y divide-[#E8E4D9]">
                    <?php foreach ($guru_list as $guru): ?>
                      <tr class="hover:bg-[#F9F8F4] transition-all duration-200" draggable="true" data-guru-id="<?php echo htmlspecialchars($guru['id']); ?>">
                        <td class="px-4 py-4">
                          <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white border border-[#E8E4D9] text-[#5F6F65] cursor-grab hover:bg-[#F9F8F4] transition-colors">
                            <iconify-icon icon="lucide:move" class="w-4 h-4"></iconify-icon>
                          </span>
                        </td>
                        <td class="px-4 py-4 text-sm text-[#5F6F65] font-medium row-number"><?php echo $no_urut++; ?></td>
                        <td class="px-4 py-4">
                          <img src="<?php echo htmlspecialchars($guru['foto'] ?? 'https://i.pravatar.cc/150'); ?>" alt="<?php echo htmlspecialchars($guru['nama']); ?>" class="w-12 h-12 rounded-full object-cover border-2 border-[#3E6B4E]/20 shadow-md">
                        </td>
                        <td class="px-4 py-4 text-sm text-[#1F2D26] font-semibold"><?php echo htmlspecialchars($guru['nama']); ?></td>
                        <td class="px-4 py-4 text-sm text-[#5F6F65]"><?php echo htmlspecialchars($guru['nip'] ?? '-'); ?></td>
                        <td class="px-4 py-4 text-sm text-[#5F6F65]"><?php echo htmlspecialchars($guru['jabatan']); ?></td>
                        <td class="px-4 py-4 text-sm text-[#5F6F65]"><?php echo htmlspecialchars($guru['mapel'] ?? '-'); ?></td>
                        <td class="px-4 py-4 text-center">
                          <div class="flex items-center justify-center gap-2">
                            <button onclick="openEditGuruModal('<?php echo htmlspecialchars($guru['id']); ?>', '<?php echo htmlspecialchars($guru['nama']); ?>', '<?php echo htmlspecialchars($guru['nip'] ?? ''); ?>', '<?php echo htmlspecialchars($guru['jabatan']); ?>', '<?php echo htmlspecialchars($guru['mapel'] ?? ''); ?>', '<?php echo htmlspecialchars($guru['foto'] ?? 'https://i.pravatar.cc/150'); ?>', '<?php echo htmlspecialchars($guru['urutan'] ?? 0); ?>')" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors">
                              <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                            </button>
                            <a href="?delete=<?php echo $guru['id']; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" onclick="return confirm('Yakin ingin menghapus guru ini?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
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

  <!-- Modal Tambah Guru -->
  <div id="modalGuru" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('modalGuru').classList.add('hidden')"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full overflow-hidden">
        <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between bg-gradient-to-r from-[#3E6B4E] to-[#2F5B41]">
          <h3 class="font-serif text-xl text-white">Tambah Guru Baru</h3>
          <button onclick="document.getElementById('modalGuru').classList.add('hidden')" class="text-white/80 hover:text-white transition-colors">
            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
          </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Foto Guru</label>
            <input type="file" name="foto_file" accept="image/*" id="fotoGuruTambahInput" onchange="previewFileGuru(event, 'fotoGuruTambahPreview')" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            <div id="fotoGuruTambahPreview" class="mt-3">
              <img src="https://i.pravatar.cc/150" class="w-32 h-32 object-cover rounded-lg border border-[#E8E4D9] opacity-50">
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Nama Guru</label>
            <input type="text" name="nama" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">NIP</label>
            <input type="text" name="nip" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Jabatan</label>
              <input type="text" name="jabatan" value="Guru" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            </div>
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Mapel (Opsional)</label>
              <input type="text" name="mapel" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Urutan (Nomor)</label>
            <input type="number" name="urutan" value="0" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
          </div>
          <div class="flex items-center gap-4 pt-4">
            <button type="button" onclick="document.getElementById('modalGuru').classList.add('hidden')" class="flex-1 px-6 py-3 rounded-lg border-2 border-[#E8E4D9] text-[#5F6F65] hover:bg-[#F9F8F4] transition-all font-semibold text-sm">Batal</button>
            <button type="submit" name="tambah_guru" class="flex-1 bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded-lg hover:bg-[#2F5B41] transition-all uppercase tracking-widest" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>Simpan Guru</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Edit Guru -->
  <div id="modalEditGuru" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('modalEditGuru').classList.add('hidden')"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full overflow-hidden">
        <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between bg-gradient-to-r from-[#3E6B4E] to-[#2F5B41]">
          <h3 class="font-serif text-xl text-white">Edit Guru</h3>
          <button onclick="document.getElementById('modalEditGuru').classList.add('hidden')" class="text-white/80 hover:text-white transition-colors">
            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
          </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
          <input type="hidden" name="id" id="editGuruId">
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Foto Guru</label>
            <input type="file" name="foto_file" accept="image/*" id="fotoGuruEditInput" onchange="previewFileGuru(event, 'fotoGuruEditPreview')" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            <div id="fotoGuruEditPreview" class="mt-3"></div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Nama Guru</label>
            <input type="text" name="nama" id="editGuruNama" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">NIP</label>
            <input type="text" name="nip" id="editGuruNip" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Jabatan</label>
              <input type="text" name="jabatan" id="editGuruJabatan" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            </div>
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Mapel (Opsional)</label>
              <input type="text" name="mapel" id="editGuruMapel" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Urutan (Nomor)</label>
            <input type="number" name="urutan" id="editGuruUrutan" value="0" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
          </div>
          <div class="flex items-center gap-4 pt-4">
            <button type="button" onclick="document.getElementById('modalEditGuru').classList.add('hidden')" class="flex-1 px-6 py-3 rounded-lg border-2 border-[#E8E4D9] text-[#5F6F65] hover:bg-[#F9F8F4] transition-all font-semibold text-sm">Batal</button>
            <button type="submit" name="edit_guru" class="flex-1 bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded-lg hover:bg-[#2F5B41] transition-all uppercase tracking-widest" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>Perbarui Guru</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function previewFileGuru(event, previewId, defaultPhoto = 'https://i.pravatar.cc/150') {
      const preview = document.getElementById(previewId);
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.innerHTML = `<img src="${e.target.result}" class="w-32 h-32 object-cover rounded-lg border-2 border-[#3E6B4E] shadow-md">`;
        };
        reader.readAsDataURL(file);
      } else {
        preview.innerHTML = `<img src="${defaultPhoto}" class="w-32 h-32 object-cover rounded-lg border border-[#E8E4D9] opacity-50">`;
      }
    }
    
    function openEditGuruModal(id, nama, nip, jabatan, mapel, foto, urutan) {
      document.getElementById('editGuruId').value = id;
      document.getElementById('editGuruNama').value = nama;
      document.getElementById('editGuruNip').value = nip;
      document.getElementById('editGuruJabatan').value = jabatan;
      document.getElementById('editGuruMapel').value = mapel;
      document.getElementById('editGuruUrutan').value = urutan || 0;
      document.getElementById('fotoGuruEditPreview').innerHTML = `<img src="${foto}" class="w-32 h-32 object-cover rounded-lg border-2 border-[#3E6B4E] shadow-md">`;
      document.getElementById('modalEditGuru').classList.remove('hidden');
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
      localStorage.setItem('guruView', view);
    }

    if (gridViewBtn && tableViewBtn) {
      gridViewBtn.addEventListener('click', function() {
        setView('grid');
      });
      
      tableViewBtn.addEventListener('click', function() {
        setView('table');
      });
    }

    function updateGuruTableNumbers() {
      const rows = document.querySelectorAll('#draggableGuruTableBody tr');
      rows.forEach((row, index) => {
        const numberCell = row.querySelector('.row-number');
        if (numberCell) {
          numberCell.textContent = index + 1;
        }
      });
    }

    function initGuruTableDragDrop() {
      const tableBody = document.getElementById('draggableGuruTableBody');
      if (!tableBody || tableBody.dataset.dragInit === 'true') return;
      tableBody.dataset.dragInit = 'true';

      let draggedRow = null;

      tableBody.querySelectorAll('tr').forEach(row => {
        row.addEventListener('dragstart', function(e) {
          draggedRow = this;
          this.classList.add('opacity-30');
          e.dataTransfer.effectAllowed = 'move';
          e.dataTransfer.setData('text/plain', this.dataset.guruId || '');
        });

        row.addEventListener('dragend', function() {
          this.classList.remove('opacity-30');
          draggedRow = null;
        });

        row.addEventListener('dragover', function(e) {
          e.preventDefault();
          this.classList.add('bg-[#f3faf6]');
        });

        row.addEventListener('dragleave', function() {
          this.classList.remove('bg-[#f3faf6]');
        });

        row.addEventListener('drop', function(e) {
          e.preventDefault();
          if (!draggedRow || draggedRow === this) return;
          const bounding = this.getBoundingClientRect();
          const offset = e.clientY - bounding.top;
          const insertAfter = offset > bounding.height / 2;
          if (insertAfter) {
            this.parentNode.insertBefore(draggedRow, this.nextSibling);
          } else {
            this.parentNode.insertBefore(draggedRow, this);
          }
          this.classList.remove('bg-[#f3faf6]');
          updateGuruTableNumbers();
        });
      });
    }

    // Initialize view
    document.addEventListener('DOMContentLoaded', function() {
      const savedView = localStorage.getItem('guruView') || 'table';
      setView(savedView);
      initGuruTableDragDrop();
    });
  </script>
</body>
</html>
