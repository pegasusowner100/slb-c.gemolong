
<?php
define('ADMIN_PAGE', true);
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/cloudinary-on.php';
require_login();

$title = "Kelola Prestasi — SLB BC KARYA SEJAHTERA";
$page_title = "Kelola Prestasi";
$success = '';
$error = '';

// Handle Search Query
$search = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
}

// Handle Add Prestasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_prestasi'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $foto = 'https://picsum.photos/seed/' . time() . '/400/300.jpg'; // Default
        
        // Handle file upload
        if (isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['foto_file'], 'prestasi');
            if ($uploadResult['success']) {
                $foto = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload foto: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }
        
        $data = [
            'nama' => trim($_POST['nama']),
            'kategori' => trim($_POST['kategori']),
            'tahun' => intval($_POST['tahun']),
            'lokasi' => trim($_POST['lokasi']),
            'peraih' => trim($_POST['peraih']),
            'foto' => $foto
        ];
        $result = supabaseInsert('prestasi', $data);
        if ($result['success']) {
            $success = 'Prestasi berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan prestasi! ' . (isset($result['error']) ? $result['error'] : 'Unknown error');
        }
    }
}

// Handle Edit Prestasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_prestasi'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $prestasiId = $_POST['prestasi_id'];
        
        // Get current data to preserve foto if no new file
        $currentResult = supabaseSelect('prestasi', ['id' => 'eq.' . $prestasiId, 'limit' => 1]);
        $currentFoto = 'https://picsum.photos/seed/' . time() . '/400/300.jpg';
        if ($currentResult['success'] && !empty($currentResult['data'])) {
            $currentFoto = $currentResult['data'][0]['foto'] ?? $currentFoto;
        }
        
        // Handle file upload
        if (isset($_FILES['edit_foto_file']) && $_FILES['edit_foto_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['edit_foto_file'], 'prestasi');
            if ($uploadResult['success']) {
                $currentFoto = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload foto: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }
        
        $data = [
            'nama' => trim($_POST['edit_nama']),
            'kategori' => trim($_POST['edit_kategori']),
            'tahun' => intval($_POST['edit_tahun']),
            'lokasi' => trim($_POST['edit_lokasi']),
            'peraih' => trim($_POST['edit_peraih']),
            'foto' => $currentFoto
        ];
        $result = supabaseUpdate('prestasi', $data, $prestasiId);
        if ($result['success']) {
            $success = 'Prestasi berhasil diperbarui!';
        } else {
            $error = 'Gagal memperbarui prestasi! ' . (isset($result['error']) ? $result['error'] : 'Unknown error');
        }
    }
}

// Handle Delete Prestasi
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        $result = supabaseDelete('prestasi', $_GET['delete']);
        if ($result['success']) {
            $success = 'Prestasi berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus prestasi! ' . (isset($result['error']) ? $result['error'] : 'Unknown error');
        }
    }
}

// Get All Prestasi
$prestasi_list = [];
if ($supabaseConnected) {
    $filters = ['order' => 'tahun.desc'];
    if (!empty($search)) {
        $filters['or'] = "(nama.ilike.%$search%,kategori.ilike.%$search%,peraih.ilike.%$search%,lokasi.ilike.%$search%)";
    }
    $prestasiResult = supabaseSelect('prestasi', $filters);
    if ($prestasiResult['success']) {
        $prestasi_list = $prestasiResult['data'];
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
          <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
            <iconify-icon icon="lucide:check-circle"></iconify-icon>
            <?php echo $success; ?>
          </div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
            <iconify-icon icon="lucide:alert-circle"></iconify-icon>
            <?php echo $error; ?>
          </div>
        <?php endif; ?>

        <?php if (!$supabaseConnected): ?>
          <div class="mb-6 p-4 bg-yellow-50 text-yellow-800 border border-yellow-200 rounded-xl">
            <iconify-icon icon="lucide:alert-triangle" class="inline mr-2"></iconify-icon>
            PERINGATAN: Supabase tidak terhubung! Perubahan tidak dapat disimpan.
          </div>
        <?php endif; ?>

        <div class="flex flex-col gap-6 mb-8">
          <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <h2 class="text-2xl font-semibold text-gray-800">Daftar Prestasi</h2>
            <button onclick="document.getElementById('modalPrestasi').classList.remove('hidden')" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-gradient-to-r from-amber-500 to-orange-600 text-white text-sm font-semibold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 uppercase tracking-wider flex items-center gap-2 disabled:opacity-50">
              <iconify-icon icon="lucide:plus"></iconify-icon>
              Tambah Prestasi
            </button>
          </div>

          <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
            <div class="w-full md:w-96">
              <input id="searchInput" type="text" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari nama prestasi, kategori, atau peraih..." oninput="filterAdminTable(this)" data-filter-selector="#table-view tbody tr, #grid-view .bg-white.rounded-2xl" class="w-full flex-1 px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all">
            </div>
            <div class="flex bg-white border border-gray-200 rounded-xl p-1">
              <button onclick="setView('grid')" id="btn-grid" class="px-4 py-2 rounded-lg transition-all font-medium flex items-center gap-2">
                <iconify-icon icon="lucide:grid"></iconify-icon> Grid
              </button>
              <button onclick="setView('table')" id="btn-table" class="px-4 py-2 rounded-lg transition-all font-medium flex items-center gap-2">
                <iconify-icon icon="lucide:table"></iconify-icon> Tabel
              </button>
            </div>
          </div>

          <?php if ($search): ?>
            <div class="flex items-center gap-2 text-sm text-amber-700 bg-amber-50 px-4 py-2 rounded-xl border border-amber-100">
              <iconify-icon icon="lucide:search"></iconify-icon>
              Hasil pencarian untuk "<strong><?php echo htmlspecialchars($search); ?></strong>" ditemukan <strong><?php echo count($prestasi_list); ?></strong> prestasi
            </div>
          <?php endif; ?>
        </div>

        <?php if (empty($prestasi_list)): ?>
          <div class="text-center py-16 bg-white rounded-2xl border border-gray-100 shadow-sm">
            <iconify-icon icon="lucide:trophy" class="text-6xl text-gray-300 mx-auto mb-4"></iconify-icon>
            <p class="text-gray-500 mb-4">Belum ada prestasi. Klik tombol "Tambah Prestasi" untuk memulai.</p>
          </div>
        <?php else: ?>
          <!-- Grid View -->
          <div id="grid-view" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($prestasi_list as $prestasi): ?>
              <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <img src="<?php echo htmlspecialchars($prestasi['foto'] ?? 'https://picsum.photos/seed/' . $prestasi['id'] . '/400/300.jpg'); ?>" alt="<?php echo htmlspecialchars($prestasi['nama']); ?>" class="w-full h-48 object-cover">
                <div class="p-6">
                  <div class="flex items-center gap-2 mb-3">
                    <span class="px-3 py-1 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-xs font-bold rounded-full uppercase tracking-wider">
                      <?php echo htmlspecialchars($prestasi['kategori'] ?? 'Sekolah'); ?>
                    </span>
                    <span class="text-xs text-gray-400">Tahun <?php echo htmlspecialchars($prestasi['tahun'] ?? '-'); ?></span>
                  </div>
                  <h3 class="font-serif text-lg font-semibold text-gray-800 mb-2 line-clamp-2"><?php echo htmlspecialchars($prestasi['nama']); ?></h3>
                  <p class="text-sm text-gray-500 mb-2"><iconify-icon icon="lucide:user" class="inline mr-1"></iconify-icon><?php echo htmlspecialchars($prestasi['peraih'] ?? '-'); ?></p>
                  <p class="text-xs text-gray-400 mb-4"><iconify-icon icon="lucide:map-pin" class="inline mr-1"></iconify-icon><?php echo htmlspecialchars($prestasi['lokasi'] ?? '-'); ?></p>
                  <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <div class="flex items-center gap-2">
                      <button onclick="openEditPrestasiModal('<?php echo htmlspecialchars($prestasi['id']); ?>', '<?php echo addslashes(htmlspecialchars($prestasi['nama'])); ?>', '<?php echo addslashes(htmlspecialchars($prestasi['kategori'])); ?>', '<?php echo htmlspecialchars($prestasi['tahun']); ?>', '<?php echo addslashes(htmlspecialchars($prestasi['lokasi'])); ?>', '<?php echo addslashes(htmlspecialchars($prestasi['peraih'])); ?>', '<?php echo addslashes(htmlspecialchars($prestasi['foto'] ?? '')); ?>')" class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-all">
                        <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                      </button>
                      <a href="?delete=<?php echo $prestasi['id']; ?><?php echo $search ? '&amp;search=' . urlencode($search) : ''; ?>" onclick="return confirm('Yakin ingin menghapus prestasi ini?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
                        <iconify-icon icon="lucide:trash-2" class="w-5 h-5"></iconify-icon>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Table View -->
          <div id="table-view" class="hidden">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-gradient-to-r from-amber-500 to-orange-600 text-white">
                    <tr>
                      <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Foto</th>
                      <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Nama Prestasi</th>
                      <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Kategori</th>
                      <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Tahun</th>
                      <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Peraih</th>
                      <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Lokasi</th>
                      <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100">
                    <?php foreach ($prestasi_list as $prestasi): ?>
                      <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                          <img src="<?php echo htmlspecialchars($prestasi['foto'] ?? 'https://picsum.photos/seed/' . $prestasi['id'] . '/100/75.jpg'); ?>" alt="<?php echo htmlspecialchars($prestasi['nama']); ?>" class="w-20 h-15 object-cover rounded-lg">
                        </td>
                        <td class="px-6 py-4">
                          <div class="font-medium text-gray-800"><?php echo htmlspecialchars($prestasi['nama']); ?></div>
                        </td>
                        <td class="px-6 py-4">
                          <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                            <?php echo htmlspecialchars(strtoupper($prestasi['kategori'] ?? 'Sekolah')); ?>
                          </span>
                        </td>
                        <td class="px-6 py-4">
                          <span class="text-gray-600 font-medium"><?php echo htmlspecialchars($prestasi['tahun'] ?? '-'); ?></span>
                        </td>
                        <td class="px-6 py-4">
                          <span class="text-gray-500 text-sm"><?php echo htmlspecialchars($prestasi['peraih'] ?? '-'); ?></span>
                        </td>
                        <td class="px-6 py-4">
                          <span class="text-gray-500 text-sm"><?php echo htmlspecialchars($prestasi['lokasi'] ?? '-'); ?></span>
                        </td>
                        <td class="px-6 py-4 text-center">
                          <div class="flex items-center justify-center gap-2">
                            <button onclick="openEditPrestasiModal('<?php echo htmlspecialchars($prestasi['id']); ?>', '<?php echo addslashes(htmlspecialchars($prestasi['nama'])); ?>', '<?php echo addslashes(htmlspecialchars($prestasi['kategori'])); ?>', '<?php echo htmlspecialchars($prestasi['tahun']); ?>', '<?php echo addslashes(htmlspecialchars($prestasi['lokasi'])); ?>', '<?php echo addslashes(htmlspecialchars($prestasi['peraih'])); ?>', '<?php echo addslashes(htmlspecialchars($prestasi['foto'] ?? '')); ?>')" class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-all">
                              <iconify-icon icon="lucide:edit"></iconify-icon>
                            </button>
                            <a href="?delete=<?php echo $prestasi['id']; ?><?php echo $search ? '&amp;search=' . urlencode($search) : ''; ?>" onclick="return confirm('Yakin ingin menghapus prestasi ini?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
                              <iconify-icon icon="lucide:trash-2"></iconify-icon>
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

  <!-- Modal Tambah Prestasi -->
  <div id="modalPrestasi" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
      <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-amber-500 to-orange-600">
        <h3 class="font-semibold text-lg text-white">Tambah Prestasi Baru</h3>
        <button onclick="document.getElementById('modalPrestasi').classList.add('hidden')" class="p-2 text-white/80 hover:bg-white/20 rounded-xl transition-all">
          <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
        </button>
      </div>
      <form method="POST" class="p-6 space-y-5" enctype="multipart/form-data">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Prestasi</label>
          <input type="text" name="nama" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all" placeholder="Masukkan nama prestasi...">
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label>
          <select name="kategori" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all">
            <option value="Sekolah">Sekolah</option>
            <option value="Guru">Guru</option>
            <option value="Siswa">Siswa</option>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun</label>
            <input type="number" name="tahun" value="<?php echo date('Y'); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all">
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Lokasi</label>
            <input type="text" name="lokasi" placeholder="Masukkan lokasi..." class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all">
          </div>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Peraih</label>
          <input type="text" name="peraih" placeholder="Masukkan nama peraih..." class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all">
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Foto Prestasi</label>
          <input type="file" name="foto_file" accept="image/*" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all">
        </div>
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
          <button type="button" onclick="document.getElementById('modalPrestasi').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
            Batal
          </button>
          <button type="submit" name="tambah_prestasi" class="px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl hover:from-amber-600 hover:to-orange-700 transition-all shadow-md">
            Simpan Prestasi
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Edit Prestasi -->
  <div id="modalEditPrestasi" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
      <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-amber-500 to-orange-600">
        <h3 class="font-semibold text-lg text-white">Edit Prestasi</h3>
        <button onclick="document.getElementById('modalEditPrestasi').classList.add('hidden')" class="p-2 text-white/80 hover:bg-white/20 rounded-xl transition-all">
          <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
        </button>
      </div>
      <form id="formEditPrestasi" method="POST" class="p-6 space-y-5" enctype="multipart/form-data">
        <input type="hidden" name="prestasi_id" id="edit_prestasi_id">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Prestasi</label>
          <input type="text" name="edit_nama" id="edit_nama" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all">
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label>
          <select name="edit_kategori" id="edit_kategori" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all">
            <option value="Sekolah">Sekolah</option>
            <option value="Guru">Guru</option>
            <option value="Siswa">Siswa</option>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun</label>
            <input type="number" name="edit_tahun" id="edit_tahun" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all">
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Lokasi</label>
            <input type="text" name="edit_lokasi" id="edit_lokasi" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all">
          </div>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Peraih</label>
          <input type="text" name="edit_peraih" id="edit_peraih" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all">
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Foto Prestasi (kosongkan jika tidak ingin mengubah)</label>
          <input type="file" name="edit_foto_file" accept="image/*" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all">
        </div>
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
          <button type="button" onclick="document.getElementById('modalEditPrestasi').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
            Batal
          </button>
          <button type="submit" name="edit_prestasi" class="px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl hover:from-amber-600 hover:to-orange-700 transition-all shadow-md">
            Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openEditPrestasiModal(id, nama, kategori, tahun, lokasi, peraih, foto) {
      document.getElementById('edit_prestasi_id').value = id;
      document.getElementById('edit_nama').value = nama;
      document.getElementById('edit_kategori').value = kategori;
      document.getElementById('edit_tahun').value = tahun;
      document.getElementById('edit_lokasi').value = lokasi;
      document.getElementById('edit_peraih').value = peraih;
      document.getElementById('modalEditPrestasi').classList.remove('hidden');
    }

    function setView(view) {
      const gridView = document.getElementById('grid-view');
      const tableView = document.getElementById('table-view');
      const btnGrid = document.getElementById('btn-grid');
      const btnTable = document.getElementById('btn-table');

      if (view === 'grid') {
        gridView.classList.remove('hidden');
        tableView.classList.add('hidden');
        btnGrid.classList.add('bg-gradient-to-r', 'from-amber-500', 'to-orange-600', 'text-white', 'shadow-md');
        btnTable.classList.remove('bg-gradient-to-r', 'from-amber-500', 'to-orange-600', 'text-white', 'shadow-md');
      } else {
        gridView.classList.add('hidden');
        tableView.classList.remove('hidden');
        btnTable.classList.add('bg-gradient-to-r', 'from-amber-500', 'to-orange-600', 'text-white', 'shadow-md');
        btnGrid.classList.remove('bg-gradient-to-r', 'from-amber-500', 'to-orange-600', 'text-white', 'shadow-md');
      }
      localStorage.setItem('prestasiView', view);
    }

    // Initialize view
    document.addEventListener('DOMContentLoaded', function() {
        const savedView = localStorage.getItem('prestasiView') || 'table';
        setView(savedView);
    });
  </script>
</body>
</html>
