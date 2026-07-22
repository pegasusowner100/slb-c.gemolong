

<?php
define('ADMIN_PAGE', true);
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/cloudinary-on.php';
require_login();

$title = "Kelola Fasilitas — SLB BC KARYA SEJAHTERA";
$page_title = "Kelola Fasilitas";
$success = '';
$error = '';

// Handle Add Fasilitas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_fasilitas'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $gambar = 'https://picsum.photos/seed/facility/500/300'; // Default
        
        // Handle file upload
        if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['gambar_file'], 'fasilitas');
            if ($uploadResult['success']) {
                $gambar = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload gambar: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }
        
        $data = [
            'nama' => $_POST['nama'],
            'deskripsi' => $_POST['deskripsi'],
            'icon' => $_POST['icon'] ?? 'mdi:office-building',
            'gambar' => $gambar,
            'urutan' => intval($_POST['urutan'])
        ];
        $result = supabaseInsert('fasilitas', $data);
        if ($result['success']) {
          $success = 'Fasilitas berhasil ditambahkan!';
        } else {
          $error = 'Gagal menambahkan fasilitas: ' . ($result['error'] ?? json_encode($result['data'] ?? $result));
        }
    }
}

// Handle Edit Fasilitas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_fasilitas'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $fasilitasId = $_POST['fasilitas_id'];
        
        // Get current fasilitas to keep gambar if no new file
        $currentFasilitasResult = supabaseSelect('fasilitas', ['id' => 'eq.' . $fasilitasId, 'limit' => '1']);
        $currentGambar = 'https://picsum.photos/seed/facility/500/300';
        if ($currentFasilitasResult['success'] && !empty($currentFasilitasResult['data'])) {
            $currentGambar = $currentFasilitasResult['data'][0]['gambar'] ?? $currentGambar;
        }
        
        // Handle file upload
        if (isset($_FILES['edit_gambar_file']) && $_FILES['edit_gambar_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['edit_gambar_file'], 'fasilitas');
            if ($uploadResult['success']) {
                $currentGambar = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload gambar: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }
        
        $data = [
            'nama' => $_POST['edit_nama'],
            'deskripsi' => $_POST['edit_deskripsi'],
            'icon' => $_POST['edit_icon'] ?? 'mdi:office-building',
            'gambar' => $currentGambar,
            'urutan' => intval($_POST['edit_urutan'])
        ];
        $result = supabaseUpdate('fasilitas', $data, $fasilitasId);
        if ($result['success']) {
          $success = 'Fasilitas berhasil diedit!';
        } else {
          $error = 'Gagal edit fasilitas: ' . ($result['error'] ?? json_encode($result['data'] ?? $result));
        }
    }
}

// Handle Delete Fasilitas
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        $result = supabaseDelete('fasilitas', $_GET['delete']);
        if ($result['success']) {
          $success = 'Fasilitas berhasil dihapus!';
        } else {
          $error = 'Gagal menghapus fasilitas: ' . ($result['error'] ?? json_encode($result['data'] ?? $result));
        }
    }
}

// Get All Fasilitas
$fasilitas_list = [];
if ($supabaseConnected) {
    $fasilitasResult = supabaseSelect('fasilitas', ['order' => 'urutan.asc']);
    if ($fasilitasResult['success']) {
        $fasilitas_list = $fasilitasResult['data'];
    }
}

include 'components/head.php';
include 'components/sidebar.php';
?>

  <!-- Main Content -->
  <main class="flex-1 flex flex-col overflow-hidden">
    <?php include 'components/header.php'; ?>

    <div class="flex-1 overflow-y-auto p-8">
      <div class="max-w-6xl">
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
        
        <!-- Add Fasilitas Button -->
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-8">
          <h2 class="text-2xl font-semibold text-[#1F2D26]">Daftar Fasilitas</h2>
          <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex items-center bg-white rounded-lg border border-[#E8E4D9] p-1">
              <button id="fasilitasGridViewBtn" class="px-4 py-2 rounded-md bg-[#3E6B4E] text-white text-xs font-bold transition-colors">
                <iconify-icon icon="lucide:grid-3x3" class="inline mr-1"></iconify-icon> Grid
              </button>
              <button id="fasilitasTableViewBtn" class="px-4 py-2 rounded-md text-[#5F6F65] hover:bg-[#F9F8F4] text-xs font-bold transition-colors">
                <iconify-icon icon="lucide:table" class="inline mr-1"></iconify-icon> Tabel
              </button>
            </div>
            <button onclick="document.getElementById('modalFasilitas').classList.remove('hidden')" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest flex items-center gap-2 disabled:opacity-50">
              <iconify-icon icon="lucide:plus"></iconify-icon> Tambah Fasilitas
            </button>
          </div>
        </div>
        <div class="mb-8">
          <input id="searchInput" type="search" placeholder="Cari fasilitas berdasarkan nama, deskripsi, atau urutan..." oninput="filterAdminTable(this)" data-filter-selector="#fasilitasGridView > div, #fasilitasTableView tbody tr" class="w-full md:w-1/2 pl-10 pr-4 py-3 bg-white border border-[#E8E4D9] rounded-lg focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all text-sm" />
        </div>
        
        <!-- Fasilitas List -->
        <?php if (empty($fasilitas_list)): ?>
          <div id="fasilitasGridView" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="col-span-full text-center py-12 bg-white rounded-lg border border-[#E8E4D9]">
              <iconify-icon icon="lucide:building-2" class="text-4xl text-[#9FB5A5] mb-4"></iconify-icon>
              <p class="text-[#5F6F65]">Belum ada fasilitas.</p>
            </div>
          </div>
          <div id="fasilitasTableView" class="hidden">
            <div class="bg-white rounded-xl border border-[#E8E4D9] shadow-sm overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-[#3E6B4E] text-white">
                    <tr>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">No</th>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Nama</th>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Deskripsi</th>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Urutan</th>
                      <th class="text-center px-4 py-4 text-xs font-bold uppercase tracking-wider">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="bg-white">
                      <td class="px-4 py-4" colspan="6">
                        <div class="text-center py-12 text-[#5F6F65]">Belum ada fasilitas.</div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div id="fasilitasGridView" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($fasilitas_list as $fasilitas): ?>
              <div class="bg-white rounded-lg border border-[#E8E4D9] shadow-sm overflow-hidden">
                <img src="<?php echo htmlspecialchars($fasilitas['gambar'] ?? 'https://picsum.photos/seed/facility/500/300'); ?>" alt="<?php echo htmlspecialchars($fasilitas['nama']); ?>" class="w-full h-48 object-cover">
                <div class="p-6">
                  <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-lg text-[#1F2D26]"><?php echo htmlspecialchars($fasilitas['nama']); ?></h3>
                    <div class="flex items-center gap-2">
                      <button onclick="openEditFasilitasModal(
                        '<?php echo htmlspecialchars($fasilitas['id']); ?>',
                        '<?php echo addslashes(htmlspecialchars($fasilitas['nama'])); ?>',
                        '<?php echo addslashes(htmlspecialchars($fasilitas['deskripsi'])); ?>',
                        '<?php echo addslashes(htmlspecialchars($fasilitas['gambar'])); ?>',
                        '<?php echo htmlspecialchars($fasilitas['urutan'] ?? 0); ?>',
                        '<?php echo htmlspecialchars($fasilitas['icon'] ?? 'mdi:office-building'); ?>'
                      )" class="text-blue-500 hover:text-blue-700 transition-colors">
                        <iconify-icon icon="lucide:edit"></iconify-icon>
                      </button>
                      <a href="?delete=<?php echo $fasilitas['id']; ?>" onclick="return confirm('Yakin ingin menghapus fasilitas ini?')" class="text-red-500 hover:text-red-700 transition-colors">
                        <iconify-icon icon="lucide:trash-2"></iconify-icon>
                      </a>
                    </div>
                  </div>
                  <p class="text-sm text-[#5F6F65] font-light line-clamp-3"><?php echo htmlspecialchars($fasilitas['deskripsi']); ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div id="fasilitasTableView" class="hidden">
            <div class="bg-white rounded-xl border border-[#E8E4D9] shadow-sm overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-[#3E6B4E] text-white">
                    <tr>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">No</th>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Foto</th>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Nama</th>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Deskripsi</th>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Urutan</th>
                      <th class="text-center px-4 py-4 text-xs font-bold uppercase tracking-wider">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-[#E8E4D9]">
                    <?php $fasilitas_no = 1; foreach ($fasilitas_list as $fasilitas): ?>
                      <tr class="hover:bg-[#F9F8F4] transition-all duration-200">
                        <td class="px-4 py-4 text-sm text-[#5F6F65] font-medium"><?php echo $fasilitas_no++; ?></td>
                        <td class="px-4 py-4">
                          <img src="<?php echo htmlspecialchars($fasilitas['gambar'] ?? 'https://picsum.photos/seed/facility/120/90'); ?>" alt="<?php echo htmlspecialchars($fasilitas['nama']); ?>" class="w-16 h-12 object-cover rounded-lg border border-[#E8E4D9]">
                        </td>
                        <td class="px-4 py-4 text-sm text-[#1F2D26] font-semibold"><?php echo htmlspecialchars($fasilitas['nama']); ?></td>
                        <td class="px-4 py-4 text-sm text-[#5F6F65] line-clamp-2"><?php echo htmlspecialchars($fasilitas['deskripsi']); ?></td>
                        <td class="px-4 py-4 text-sm text-[#5F6F65]"><?php echo htmlspecialchars($fasilitas['urutan']); ?></td>
                        <td class="px-4 py-4 text-center">
                          <div class="flex items-center justify-center gap-2">
                            <button onclick="openEditFasilitasModal(
                              '<?php echo htmlspecialchars($fasilitas['id']); ?>',
                              '<?php echo addslashes(htmlspecialchars($fasilitas['nama'])); ?>',
                              '<?php echo addslashes(htmlspecialchars($fasilitas['deskripsi'])); ?>',
                              '<?php echo addslashes(htmlspecialchars($fasilitas['gambar'])); ?>',
                              '<?php echo htmlspecialchars($fasilitas['urutan'] ?? 0); ?>',
                              '<?php echo htmlspecialchars($fasilitas['icon'] ?? 'mdi:office-building'); ?>'
                            )" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors">
                              <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                            </button>
                            <a href="?delete=<?php echo $fasilitas['id']; ?>" onclick="return confirm('Yakin ingin menghapus fasilitas ini?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
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
    </div>
  </main>

  <!-- Modal Tambah Fasilitas -->
  <div id="modalFasilitas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('modalFasilitas').classList.add('hidden')"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-lg w-full overflow-hidden">
        <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between bg-[#3E6B4E]">
          <h3 class="font-semibold text-lg text-white">Tambah Fasilitas Baru</h3>
          <button onclick="document.getElementById('modalFasilitas').classList.add('hidden')" class="text-white/70 hover:text-white">
            <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
          </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
          <input type="hidden" name="tambah_fasilitas" value="1">
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Icon (Lucide/Iconify Name)</label>
            <div class="flex gap-2 mb-2">
              <input type="text" name="icon" id="add_icon_fasilitas" oninput="updateIconPreview('add_icon_fasilitas', 'add_icon_preview_fasilitas')" placeholder="Contoh: mdi:office-building" class="flex-1 px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
              <div class="w-12 h-12 bg-[#F9F8F4] border border-[#E8E4D9] rounded flex items-center justify-center text-2xl text-[#3E6B4E] flex-shrink-0">
                <iconify-icon id="add_icon_preview_fasilitas" icon="mdi:office-building"></iconify-icon>
              </div>
            </div>
            <div class="p-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg">
              <span class="block text-[10px] font-bold text-[#9FB5A5] uppercase mb-2">Pilih Ikon Cepat:</span>
              <div class="grid grid-cols-6 gap-2">
                <button type="button" onclick="selectIcon('add_icon_fasilitas', 'add_icon_preview_fasilitas', 'mdi:office-building')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Gedung">
                  <iconify-icon icon="mdi:office-building" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('add_icon_fasilitas', 'add_icon_preview_fasilitas', 'lucide:home')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Kelas/Rumah">
                  <iconify-icon icon="lucide:home" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('add_icon_fasilitas', 'add_icon_preview_fasilitas', 'lucide:book-open')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Perpustakaan">
                  <iconify-icon icon="lucide:book-open" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('add_icon_fasilitas', 'add_icon_preview_fasilitas', 'lucide:cpu')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Lab/Komputer">
                  <iconify-icon icon="lucide:cpu" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('add_icon_fasilitas', 'add_icon_preview_fasilitas', 'lucide:graduation-cap')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Kepala Sekolah">
                  <iconify-icon icon="lucide:graduation-cap" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('add_icon_fasilitas', 'add_icon_preview_fasilitas', 'lucide:users')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Guru">
                  <iconify-icon icon="lucide:users" class="text-lg"></iconify-icon>
                </button>
              </div>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Nama Fasilitas</label>
            <input type="text" name="nama" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Deskripsi</label>
            <textarea name="deskripsi" rows="3" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm resize-none"></textarea>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Gambar (Pilih File)</label>
            <input type="file" name="gambar_file" accept="image/*" onchange="previewGambar(event, 'gambarPreviewFasilitas')" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
            <div id="gambarPreviewFasilitas" class="mt-3">
              <p class="text-xs text-[#9FB5A5] italic">Preview gambar akan muncul di sini</p>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Urutan</label>
            <input type="number" name="urutan" value="0" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
          </div>
          <div class="flex items-center gap-4 pt-4">
            <button type="button" onclick="document.getElementById('modalFasilitas').classList.add('hidden')" class="flex-1 px-6 py-3 rounded border border-[#E8E4D9] text-[#5F6F65] hover:bg-[#F9F8F4] transition-colors text-sm">Batal</button>
            <button type="submit" name="tambah_fasilitas" class="flex-1 bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest">Simpan Fasilitas</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Edit Fasilitas -->
  <div id="modalEditFasilitas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('modalEditFasilitas').classList.add('hidden')"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-lg w-full overflow-hidden">
        <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between bg-[#3E6B4E]">
          <h3 class="font-semibold text-lg text-white">Edit Fasilitas</h3>
          <button onclick="document.getElementById('modalEditFasilitas').classList.add('hidden')" class="text-white/70 hover:text-white">
            <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
          </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
          <input type="hidden" name="fasilitas_id" id="edit_fasilitas_id" value="">
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Icon (Lucide/Iconify Name)</label>
            <div class="flex gap-2 mb-2">
              <input type="text" name="edit_icon" id="edit_icon_fasilitas" oninput="updateIconPreview('edit_icon_fasilitas', 'edit_icon_preview_fasilitas')" placeholder="Contoh: mdi:office-building" class="flex-1 px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
              <div class="w-12 h-12 bg-[#F9F8F4] border border-[#E8E4D9] rounded flex items-center justify-center text-2xl text-[#3E6B4E] flex-shrink-0">
                <iconify-icon id="edit_icon_preview_fasilitas" icon="mdi:office-building"></iconify-icon>
              </div>
            </div>
            <div class="p-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg">
              <span class="block text-[10px] font-bold text-[#9FB5A5] uppercase mb-2">Pilih Ikon Cepat:</span>
              <div class="grid grid-cols-6 gap-2">
                <button type="button" onclick="selectIcon('edit_icon_fasilitas', 'edit_icon_preview_fasilitas', 'mdi:office-building')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Gedung">
                  <iconify-icon icon="mdi:office-building" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('edit_icon_fasilitas', 'edit_icon_preview_fasilitas', 'lucide:home')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Kelas/Rumah">
                  <iconify-icon icon="lucide:home" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('edit_icon_fasilitas', 'edit_icon_preview_fasilitas', 'lucide:book-open')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Perpustakaan">
                  <iconify-icon icon="lucide:book-open" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('edit_icon_fasilitas', 'edit_icon_preview_fasilitas', 'lucide:cpu')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Lab/Komputer">
                  <iconify-icon icon="lucide:cpu" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('edit_icon_fasilitas', 'edit_icon_preview_fasilitas', 'lucide:graduation-cap')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Kepala Sekolah">
                  <iconify-icon icon="lucide:graduation-cap" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('edit_icon_fasilitas', 'edit_icon_preview_fasilitas', 'lucide:users')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Guru">
                  <iconify-icon icon="lucide:users" class="text-lg"></iconify-icon>
                </button>
              </div>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Nama Fasilitas</label>
            <input type="text" name="edit_nama" id="edit_nama_fasilitas" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Deskripsi</label>
            <textarea name="edit_deskripsi" id="edit_deskripsi_fasilitas" rows="3" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm resize-none"></textarea>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Gambar (Pilih File, biarkan kosong untuk tidak mengedit)</label>
            <input type="file" name="edit_gambar_file" accept="image/*" onchange="previewGambar(event, 'editGambarPreviewFasilitas')" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
            <div id="editGambarPreviewFasilitas" class="mt-3">
              <p class="text-xs text-[#9FB5A5] italic">Preview gambar akan muncul di sini</p>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Urutan</label>
            <input type="number" name="edit_urutan" id="edit_urutan_fasilitas" value="0" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
          </div>
          <div class="flex items-center gap-4 pt-4">
            <button type="button" onclick="document.getElementById('modalEditFasilitas').classList.add('hidden')" class="flex-1 px-6 py-3 rounded border border-[#E8E4D9] text-[#5F6F65] hover:bg-[#F9F8F4] transition-colors text-sm">Batal</button>
            <button type="submit" name="edit_fasilitas" class="flex-1 bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function previewGambar(event, previewId) {
      const previewDiv = document.getElementById(previewId);
      const file = event.target.files[0];
      
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          previewDiv.innerHTML = `
            <img src="${e.target.result}" alt="Preview" class="w-full h-40 object-cover rounded border border-[#E8E4D9]">
          `;
        };
        reader.readAsDataURL(file);
      } else {
        previewDiv.innerHTML = `
          <p class="text-xs text-[#9FB5A5] italic">Preview gambar akan muncul di sini</p>
        `;
      }
    }

    function selectIcon(inputId, previewId, iconName) {
      document.getElementById(inputId).value = iconName;
      updateIconPreview(inputId, previewId);
    }

    function updateIconPreview(inputId, previewId) {
      const inputVal = document.getElementById(inputId).value;
      const previewEl = document.getElementById(previewId);
      if (previewEl) {
        previewEl.setAttribute('icon', inputVal || 'mdi:office-building');
      }
    }

    function openEditFasilitasModal(id, nama, deskripsi, gambar, urutan, icon) {
      document.getElementById('edit_fasilitas_id').value = id;
      document.getElementById('edit_nama_fasilitas').value = nama;
      document.getElementById('edit_deskripsi_fasilitas').value = deskripsi;
      document.getElementById('edit_urutan_fasilitas').value = urutan;
      document.getElementById('edit_icon_fasilitas').value = icon || 'mdi:office-building';
      updateIconPreview('edit_icon_fasilitas', 'edit_icon_preview_fasilitas');
      document.getElementById('editGambarPreviewFasilitas').innerHTML = `
        <img src="${gambar}" alt="Current Image" class="w-full h-40 object-cover rounded border border-[#E8E4D9]">
      `;
      document.getElementById('modalEditFasilitas').classList.remove('hidden');
    }

    const fasilitasGridViewBtn = document.getElementById('fasilitasGridViewBtn');
    const fasilitasTableViewBtn = document.getElementById('fasilitasTableViewBtn');
    const fasilitasGridView = document.getElementById('fasilitasGridView');
    const fasilitasTableView = document.getElementById('fasilitasTableView');

    function setFasilitasView(view) {
      if (!fasilitasGridView || !fasilitasTableView || !fasilitasGridViewBtn || !fasilitasTableViewBtn) return;
      if (view === 'grid') {
        fasilitasGridView.classList.remove('hidden');
        fasilitasTableView.classList.add('hidden');
        fasilitasGridViewBtn.classList.add('bg-[#3E6B4E]', 'text-white');
        fasilitasGridViewBtn.classList.remove('text-[#5F6F65]', 'hover:bg-[#F9F8F4]');
        fasilitasTableViewBtn.classList.remove('bg-[#3E6B4E]', 'text-white');
        fasilitasTableViewBtn.classList.add('text-[#5F6F65]', 'hover:bg-[#F9F8F4]');
      } else {
        fasilitasGridView.classList.add('hidden');
        fasilitasTableView.classList.remove('hidden');
        fasilitasTableViewBtn.classList.add('bg-[#3E6B4E]', 'text-white');
        fasilitasTableViewBtn.classList.remove('text-[#5F6F65]', 'hover:bg-[#F9F8F4]');
        fasilitasGridViewBtn.classList.remove('bg-[#3E6B4E]', 'text-white');
        fasilitasGridViewBtn.classList.add('text-[#5F6F65]', 'hover:bg-[#F9F8F4]');
      }
      localStorage.setItem('fasilitasView', view);
    }

    if (fasilitasGridViewBtn && fasilitasTableViewBtn) {
      fasilitasGridViewBtn.addEventListener('click', function() {
        setFasilitasView('grid');
      });
      fasilitasTableViewBtn.addEventListener('click', function() {
        setFasilitasView('table');
      });
    }

    document.addEventListener('DOMContentLoaded', function() {
      const savedFasilitasView = localStorage.getItem('fasilitasView') || 'grid';
      setFasilitasView(savedFasilitasView);
    });
  </script>

</body>
</html>
