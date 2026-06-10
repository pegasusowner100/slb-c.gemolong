<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/cloudinary.php';
require_login();

$title = "Kelola Siswa — " . SITE_NAME;
$page_title = "Kelola Siswa";
$success = '';
$error = '';

// Handle search query
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle add siswa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_siswa'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $no_induk = trim($_POST['no_induk']);
        $nama = trim($_POST['nama']);
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $usia = intval($_POST['usia']);
        $nama_ortu = trim($_POST['nama_ortu']);
        $telpon_ortu = trim($_POST['telpon_ortu']);
        $pekerjaan_ortu = $_POST['pekerjaan_ortu'];
        $alamat_ortu = trim($_POST['alamat_ortu']);
        $status = $_POST['status'];

        $foto = 'https://picsum.photos/seed/default-siswa/300/400.jpg';

        if (isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['foto_file'], 'siswa');
            if ($uploadResult['success']) {
                $foto = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload foto: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }

        $data = [
            'no_induk' => $no_induk,
            'nama' => $nama,
            'jenis_kelamin' => $jenis_kelamin,
            'usia' => $usia,
            'nama_ortu' => $nama_ortu,
            'telpon_ortu' => $telpon_ortu,
            'pekerjaan_ortu' => $pekerjaan_ortu,
            'alamat_ortu' => $alamat_ortu,
            'foto' => $foto,
            'status' => $status
        ];

        $result = supabaseInsert('siswa', $data);

        if ($result['success']) {
            $success = 'Siswa berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan siswa!';
        }
    }
}

// Handle edit siswa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_siswa'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $id = $_POST['id'];
        $no_induk = trim($_POST['edit_no_induk']);
        $nama = trim($_POST['edit_nama']);
        $jenis_kelamin = $_POST['edit_jenis_kelamin'];
        $usia = intval($_POST['edit_usia']);
        $nama_ortu = trim($_POST['edit_nama_ortu']);
        $telpon_ortu = trim($_POST['edit_telpon_ortu']);
        $pekerjaan_ortu = $_POST['edit_pekerjaan_ortu'];
        $alamat_ortu = trim($_POST['edit_alamat_ortu']);
        $status = $_POST['edit_status'];

        // Get current data
        $currentResult = supabaseSelect('siswa', ['id' => 'eq.' . $id, 'limit' => 1]);
        $currentFoto = 'https://picsum.photos/seed/default-siswa/300/400.jpg';
        if ($currentResult['success'] && !empty($currentResult['data'])) {
            $currentFoto = $currentResult['data'][0]['foto'] ?? $currentFoto;
        }

        // Handle file upload
        if (isset($_FILES['edit_foto_file']) && $_FILES['edit_foto_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['edit_foto_file'], 'siswa');
            if ($uploadResult['success']) {
                $currentFoto = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload foto: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }

        $data = [
            'no_induk' => $no_induk,
            'nama' => $nama,
            'jenis_kelamin' => $jenis_kelamin,
            'usia' => $usia,
            'nama_ortu' => $nama_ortu,
            'telpon_ortu' => $telpon_ortu,
            'pekerjaan_ortu' => $pekerjaan_ortu,
            'alamat_ortu' => $alamat_ortu,
            'foto' => $currentFoto,
            'status' => $status
        ];

        $result = supabaseUpdate('siswa', $data, $id);

        if ($result['success']) {
            $success = 'Siswa berhasil diperbarui!';
        } else {
            $error = 'Gagal memperbarui siswa!';
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        $result = supabaseDelete('siswa', $_GET['delete']);
        if ($result['success']) {
            $success = 'Siswa berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus siswa!';
        }
    }
}

// Get all siswa with search
$all_siswa = [];
if ($supabaseConnected) {
    $filters = ['order' => 'no_induk.asc'];
    if (!empty($search_query)) {
        $filters['or'] = "(no_induk.ilike.%$search_query%,nama.ilike.%$search_query%,nama_ortu.ilike.%$search_query%,alamat_ortu.ilike.%$search_query%,telpon_ortu.ilike.%$search_query%)";
    }
    $result = supabaseSelect('siswa', $filters);
    if ($result['success']) {
        $all_siswa = $result['data'];
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

        <?php if (!$supabaseConnected): ?>
          <div class="mb-6 p-4 bg-yellow-50 text-yellow-800 border border-yellow-200">
            <iconify-icon icon="lucide:alert-triangle" class="inline mr-2"></iconify-icon>
            PERINGATAN: Supabase tidak terhubung! Perubahan tidak dapat disimpan.
          </div>
        <?php endif; ?>

        <!-- Header Section -->
        <div class="mb-8 space-y-4">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h2 class="text-2xl font-semibold text-[#1F2D26]">Daftar Siswa</h2>
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
              <button onclick="document.getElementById('modalSiswa').classList.remove('hidden')" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded-lg hover:bg-[#2F5B41] transition-colors uppercase tracking-widest flex items-center gap-2 disabled:opacity-50">
                <iconify-icon icon="lucide:plus"></iconify-icon>
                Tambah Siswa
              </button>
            </div>
          </div>

          <!-- Search Form -->
          <div class="bg-white p-4 rounded-xl border border-[#E8E4D9] shadow-sm">
            <div class="flex flex-col md:flex-row gap-4">
              <div class="flex-1 relative">
                <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-[#9FB5A5]"></iconify-icon>
                <input id="searchInput" type="text" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Cari berdasarkan nama, no induk, nama ortu, alamat, atau telepon..." oninput="filterAdminTable(this)" data-filter-selector="#tableView tbody tr, #gridView .bg-white.rounded-xl" class="w-full pl-10 pr-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm">
              </div>
              <?php if (!empty($search_query)): ?>
                <div class="flex items-center gap-3">
                  <a href="kelola-siswa.php" class="bg-[#5F6F65] text-white text-xs font-bold px-6 py-3 rounded-lg hover:bg-[#4a5a51] transition-colors uppercase tracking-widest flex items-center gap-2">
                    <iconify-icon icon="lucide:x"></iconify-icon>
                    Reset
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Search Results Info -->
          <?php if (!empty($search_query)): ?>
            <div class="p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg flex items-center gap-2">
              <iconify-icon icon="lucide:info"></iconify-icon>
              Menemukan <?php echo count($all_siswa); ?> hasil pencarian untuk "<?php echo htmlspecialchars($search_query); ?>"
            </div>
          <?php endif; ?>
        </div>

        <!-- Siswa List -->
        <?php if (empty($all_siswa)): ?>
          <div class="text-center py-12 bg-white rounded-xl border border-[#E8E4D9]">
            <iconify-icon icon="lucide:users" class="text-4xl text-[#9FB5A5] mb-4"></iconify-icon>
            <p class="text-[#5F6F65]">Belum ada data siswa.</p>
          </div>
        <?php else: ?>
          <!-- Grid View (Default) -->
          <div id="gridView" class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($all_siswa as $siswa): ?>
              <div class="bg-white rounded-xl border border-[#E8E4D9] shadow-sm overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 <?php echo $siswa['status'] == 'Tidak Aktif' ? 'opacity-60' : ''; ?>">
                <div class="p-6 text-center">
                  <img src="<?php echo htmlspecialchars($siswa['foto'] ?? 'https://picsum.photos/seed/default-siswa/300/400.jpg'); ?>" alt="<?php echo htmlspecialchars($siswa['nama']); ?>" class="w-24 h-24 rounded-full object-cover border-4 border-[#3E6B4E]/20 mx-auto mb-4 shadow-md">
                  <h3 class="font-serif text-lg font-semibold text-[#1F2D26] mb-1 line-clamp-1"><?php echo htmlspecialchars($siswa['nama']); ?></h3>
                  <p class="text-sm text-[#9FB5A5] mb-3">No Induk: <?php echo htmlspecialchars($siswa['no_induk']); ?></p>
                  <div class="flex items-center justify-center gap-2 mb-2">
                    <span class="px-2 py-1 rounded-full text-xs font-bold <?php echo $siswa['jenis_kelamin'] == 'Laki-laki' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700'; ?>">
                      <?php echo htmlspecialchars($siswa['jenis_kelamin']); ?>
                    </span>
                    <span class="px-2 py-1 rounded-full text-xs font-bold <?php echo $siswa['status'] == 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                      <?php echo htmlspecialchars($siswa['status']); ?>
                    </span>
                  </div>
                  <p class="text-xs text-[#5F6F65] mb-3">Ortu: <?php echo htmlspecialchars($siswa['nama_ortu'] ?? '-'); ?></p>
                  <p class="text-xs text-[#5F6F65] mb-4">Usia: <?php echo htmlspecialchars($siswa['usia']); ?> tahun</p>
                  <div class="flex items-center justify-center gap-2 pt-4 border-t border-[#E8E4D9]">
                    <button onclick="openEditSiswaModal('<?php echo htmlspecialchars($siswa['id']); ?>', '<?php echo htmlspecialchars($siswa['no_induk']); ?>', '<?php echo htmlspecialchars($siswa['nama']); ?>', '<?php echo htmlspecialchars($siswa['jenis_kelamin']); ?>', '<?php echo htmlspecialchars($siswa['usia']); ?>', '<?php echo addslashes(htmlspecialchars($siswa['nama_ortu'] ?? '')); ?>', '<?php echo addslashes(htmlspecialchars($siswa['alamat_ortu'] ?? '')); ?>', '<?php echo addslashes(htmlspecialchars($siswa['pekerjaan_ortu'] ?? '')); ?>', '<?php echo htmlspecialchars($siswa['telpon_ortu'] ?? ''); ?>', '<?php echo htmlspecialchars($siswa['foto'] ?? 'https://picsum.photos/seed/default-siswa/300/400.jpg'); ?>', '<?php echo htmlspecialchars($siswa['status'] ?? 'Aktif'); ?>')" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors">
                      <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                    </button>
                    <a href="?delete=<?php echo $siswa['id']; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" onclick="return confirm('Yakin ingin menghapus siswa ini?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                      <iconify-icon icon="lucide:trash-2" class="w-5 h-5"></iconify-icon>
                    </a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Table View -->
          <div id="tableView" class="hidden">
            <div class="bg-white rounded-xl border border-[#E8E4D9] shadow-sm overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-gradient-to-r from-[#3E6B4E] to-[#2F5B41]">
                    <tr>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">No</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Foto</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">No Induk</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Nama Lengkap</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">JK</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Usia</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Nama Ortu</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Telpon Ortu</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Pekerjaan Ortu</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Alamat Ortu</th>
                      <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Status</th>
                      <th class="text-center px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-[#E8E4D9]">
                    <?php $no_urut = 1; foreach ($all_siswa as $siswa): ?>
                      <tr class="hover:bg-[#F9F8F4] transition-all duration-200 <?php echo $siswa['status'] == 'Tidak Aktif' ? 'opacity-60' : ''; ?>">
                        <td class="px-4 py-4 text-sm text-[#5F6F65] font-medium"><?php echo $no_urut++; ?></td>
                        <td class="px-4 py-4">
                          <img src="<?php echo htmlspecialchars($siswa['foto'] ?? 'https://picsum.photos/seed/default-siswa/300/400.jpg'); ?>" alt="<?php echo htmlspecialchars($siswa['nama']); ?>" class="w-12 h-12 rounded-full object-cover border-2 border-[#3E6B4E]/20 shadow-md">
                        </td>
                        <td class="px-4 py-4 text-sm text-[#1F2D26] font-semibold"><?php echo htmlspecialchars($siswa['no_induk']); ?></td>
                        <td class="px-4 py-4 text-sm text-[#1F2D26] font-medium"><?php echo htmlspecialchars($siswa['nama']); ?></td>
                        <td class="px-4 py-4 text-sm text-[#5F6F65]">
                          <span class="px-2 py-1 rounded-full text-xs font-bold <?php echo $siswa['jenis_kelamin'] == 'Laki-laki' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700'; ?>">
                            <?php echo htmlspecialchars(substr($siswa['jenis_kelamin'], 0, 1)); ?>
                          </span>
                        </td>
                        <td class="px-4 py-4 text-sm text-[#5F6F65]"><?php echo htmlspecialchars($siswa['usia']); ?> tahun</td>
                        <td class="px-4 py-4 text-sm text-[#5F6F65]"><?php echo htmlspecialchars($siswa['nama_ortu'] ?? '-'); ?></td>
                        <td class="px-4 py-4 text-sm text-[#5F6F65]"><?php echo htmlspecialchars($siswa['telpon_ortu'] ?? '-'); ?></td>
                        <td class="px-4 py-4 text-sm text-[#5F6F65]"><?php echo htmlspecialchars($siswa['pekerjaan_ortu'] ?? '-'); ?></td>
                        <td class="px-4 py-4 text-sm text-[#5F6F65] max-w-xs truncate"><?php echo htmlspecialchars($siswa['alamat_ortu'] ?? '-'); ?></td>
                        <td class="px-4 py-4">
                          <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $siswa['status'] == 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                            <?php echo htmlspecialchars($siswa['status']); ?>
                          </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                          <div class="flex items-center justify-center gap-2">
                            <button onclick="openEditSiswaModal('<?php echo htmlspecialchars($siswa['id']); ?>', '<?php echo htmlspecialchars($siswa['no_induk']); ?>', '<?php echo htmlspecialchars($siswa['nama']); ?>', '<?php echo htmlspecialchars($siswa['jenis_kelamin']); ?>', '<?php echo htmlspecialchars($siswa['usia']); ?>', '<?php echo addslashes(htmlspecialchars($siswa['nama_ortu'] ?? '')); ?>', '<?php echo addslashes(htmlspecialchars($siswa['alamat_ortu'] ?? '')); ?>', '<?php echo addslashes(htmlspecialchars($siswa['pekerjaan_ortu'] ?? '')); ?>', '<?php echo htmlspecialchars($siswa['telpon_ortu'] ?? ''); ?>', '<?php echo htmlspecialchars($siswa['foto'] ?? 'https://picsum.photos/seed/default-siswa/300/400.jpg'); ?>', '<?php echo htmlspecialchars($siswa['status'] ?? 'Aktif'); ?>')" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors">
                              <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                            </button>
                            <a href="?delete=<?php echo $siswa['id']; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" onclick="return confirm('Yakin ingin menghapus siswa ini?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
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

  <!-- Modal Tambah Siswa -->
  <div id="modalSiswa" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('modalSiswa').classList.add('hidden')"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full overflow-hidden">
        <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between bg-gradient-to-r from-[#3E6B4E] to-[#2F5B41]">
          <h3 class="font-serif text-xl text-white">Tambah Siswa Baru</h3>
          <button onclick="document.getElementById('modalSiswa').classList.add('hidden')" class="text-white/80 hover:text-white transition-colors">
            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
          </button>
        </div>
        <form action="" method="POST" class="p-6 space-y-5" enctype="multipart/form-data">
          <div class="grid md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Foto Siswa</label>
              <input type="file" name="foto_file" accept="image/*" id="fotoTambahInput" onchange="previewFile(event, 'fotoTambahPreview')" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
              <div id="fotoTambahPreview" class="mt-3">
                <img src="https://picsum.photos/seed/default-siswa/300/400.jpg" class="w-32 h-32 object-cover rounded-lg border border-[#E8E4D9] opacity-50">
              </div>
            </div>
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">No Induk</label>
              <input type="text" name="no_induk" required placeholder="Masukkan nomor induk..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            </div>
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Nama Lengkap</label>
              <input type="text" name="nama" required placeholder="Masukkan nama lengkap..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            </div>
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Jenis Kelamin</label>
              <select name="jenis_kelamin" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Usia (tahun)</label>
              <input type="number" name="usia" required placeholder="Masukkan usia..." min="1" max="100" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            </div>
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Status</label>
              <select name="status" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                <option value="Aktif">Aktif</option>
                <option value="Tidak Aktif">Tidak Aktif</option>
              </select>
            </div>
          </div>

          <div class="border-t border-[#E8E4D9] pt-5">
            <h4 class="font-semibold text-sm text-[#1F2D26] mb-4">Data Orang Tua</h4>
            <div class="grid md:grid-cols-2 gap-5">
              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Nama Orang Tua</label>
                <input type="text" name="nama_ortu" required placeholder="Masukkan nama orang tua..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
              </div>
              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Nomor Telepon</label>
                <input type="tel" name="telpon_ortu" required placeholder="Masukkan nomor telepon..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Alamat Orang Tua</label>
                <textarea name="alamat_ortu" rows="3" required placeholder="Masukkan alamat lengkap..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm resize-none" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>></textarea>
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Pekerjaan Orang Tua</label>
                <select name="pekerjaan_ortu" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  <option value="ASN/TNI/Polri">ASN/TNI/Polri</option>
                  <option value="Petani/Nelayan">Petani/Nelayan</option>
                  <option value="Buruh">Buruh</option>
                  <option value="Wiraswasta">Wiraswasta</option>
                  <option value="Lainnya">Lainnya</option>
                </select>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-4 pt-4">
            <button type="button" onclick="document.getElementById('modalSiswa').classList.add('hidden')" class="flex-1 px-6 py-3 rounded-lg border-2 border-[#E8E4D9] text-[#5F6F65] hover:bg-[#F9F8F4] transition-all font-semibold text-sm">Batal</button>
            <button type="submit" name="tambah_siswa" class="flex-1 bg-[#3E6B4E] text-white text-xs font-bold px-8 py-3 rounded-lg hover:bg-[#2F5B41] transition-all uppercase tracking-widest" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>Simpan Siswa</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Edit Siswa -->
  <div id="modalEditSiswa" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('modalEditSiswa').classList.add('hidden')"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full overflow-hidden">
        <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between bg-gradient-to-r from-[#3E6B4E] to-[#2F5B41]">
          <h3 class="font-serif text-xl text-white">Edit Data Siswa</h3>
          <button onclick="document.getElementById('modalEditSiswa').classList.add('hidden')" class="text-white/80 hover:text-white transition-colors">
            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
          </button>
        </div>
        <form action="" method="POST" class="p-6 space-y-5" enctype="multipart/form-data">
          <input type="hidden" name="id" id="editSiswaId">
          <div class="grid md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Foto Siswa</label>
              <input type="file" name="edit_foto_file" accept="image/*" id="fotoEditInput" onchange="previewFile(event, 'fotoEditPreview')" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
              <div id="fotoEditPreview" class="mt-3"></div>
            </div>
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">No Induk</label>
              <input type="text" name="edit_no_induk" id="editNoInduk" required placeholder="Masukkan nomor induk..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            </div>
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Nama Lengkap</label>
              <input type="text" name="edit_nama" id="editNama" required placeholder="Masukkan nama lengkap..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            </div>
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Jenis Kelamin</label>
              <select name="edit_jenis_kelamin" id="editJenisKelamin" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Usia (tahun)</label>
              <input type="number" name="edit_usia" id="editUsia" required placeholder="Masukkan usia..." min="1" max="100" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            </div>
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Status</label>
              <select name="edit_status" id="editStatus" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                <option value="Aktif">Aktif</option>
                <option value="Tidak Aktif">Tidak Aktif</option>
              </select>
            </div>
          </div>

          <div class="border-t border-[#E8E4D9] pt-5">
            <h4 class="font-semibold text-sm text-[#1F2D26] mb-4">Data Orang Tua</h4>
            <div class="grid md:grid-cols-2 gap-5">
              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Nama Orang Tua</label>
                <input type="text" name="edit_nama_ortu" id="editNamaOrtu" required placeholder="Masukkan nama orang tua..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
              </div>
              <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Nomor Telepon</label>
                <input type="tel" name="edit_telpon_ortu" id="editTelponOrtu" required placeholder="Masukkan nomor telepon..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Alamat Orang Tua</label>
                <textarea name="edit_alamat_ortu" id="editAlamatOrtu" rows="3" required placeholder="Masukkan alamat lengkap..." class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm resize-none" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>></textarea>
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Pekerjaan Orang Tua</label>
                <select name="edit_pekerjaan_ortu" id="editPekerjaanOrtu" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                  <option value="ASN/TNI/Polri">ASN/TNI/Polri</option>
                  <option value="Petani/Nelayan">Petani/Nelayan</option>
                  <option value="Buruh">Buruh</option>
                  <option value="Wiraswasta">Wiraswasta</option>
                  <option value="Lainnya">Lainnya</option>
                </select>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-4 pt-4">
            <button type="button" onclick="document.getElementById('modalEditSiswa').classList.add('hidden')" class="flex-1 px-6 py-3 rounded-lg border-2 border-[#E8E4D9] text-[#5F6F65] hover:bg-[#F9F8F4] transition-all font-semibold text-sm">Batal</button>
            <button type="submit" name="edit_siswa" class="flex-1 bg-[#3E6B4E] text-white text-xs font-bold px-8 py-3 rounded-lg hover:bg-[#2F5B41] transition-all uppercase tracking-widest" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>Perbarui Siswa</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function previewFile(event, previewId, defaultPhoto = 'https://picsum.photos/seed/default-siswa/300/400.jpg') {
      const previewDiv = document.getElementById(previewId);
      const file = event.target.files[0];

      if (file) {
        const fileType = file.type;
        const reader = new FileReader();
        reader.onload = function(e) {
          if (fileType.startsWith('image/')) {
            previewDiv.innerHTML = `
              <img src="${e.target.result}" alt="Preview" class="w-32 h-32 object-cover rounded-lg border-2 border-[#E8E4D9]">
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
          <img src="${defaultPhoto}" class="w-32 h-32 object-cover rounded-lg border border-[#E8E4D9] opacity-50">
        `;
      }
    }

    function openEditSiswaModal(id, no_induk, nama, jenis_kelamin, usia, nama_ortu, alamat_ortu, pekerjaan_ortu, telpon_ortu, foto, status) {
      document.getElementById('editSiswaId').value = id;
      document.getElementById('editNoInduk').value = no_induk;
      document.getElementById('editNama').value = nama;
      document.getElementById('editJenisKelamin').value = jenis_kelamin;
      document.getElementById('editUsia').value = usia;
      document.getElementById('editNamaOrtu').value = nama_ortu;
      document.getElementById('editAlamatOrtu').value = alamat_ortu;
      document.getElementById('editPekerjaanOrtu').value = pekerjaan_ortu;
      document.getElementById('editTelponOrtu').value = telpon_ortu;
      document.getElementById('editStatus').value = status;

      document.getElementById('fotoEditPreview').innerHTML = `
        <img src="${foto}" alt="Current Photo" class="w-32 h-32 object-cover rounded-lg border-2 border-[#E8E4D9]">
      `;

      document.getElementById('modalEditSiswa').classList.remove('hidden');
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
      localStorage.setItem('siswaView', view);
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
      const savedView = localStorage.getItem('siswaView') || 'table';
      setView(savedView);
    });
  </script>
</body>
</html>
