<?php
define('ADMIN_PAGE', true);
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/cloudinary-on.php';
require_login();

$title = "Kelola Siswa — SLB BC KARYA SEJAHTERA " . SITE_NAME;
$page_title = "Kelola Siswa";
$success = '';
$error = '';

// Handle search and filter queries
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_jenis_kelamin = isset($_GET['jenis_kelamin']) ? trim($_GET['jenis_kelamin']) : '';
$filter_usia_min = isset($_GET['usia_min']) ? intval($_GET['usia_min']) : '';
$filter_usia_max = isset($_GET['usia_max']) ? intval($_GET['usia_max']) : '';
$filter_jenjang = isset($_GET['jenjang']) ? trim($_GET['jenjang']) : '';
$filter_kelas = isset($_GET['kelas']) ? trim($_GET['kelas']) : '';

// Handle add siswa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_siswa'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $no_induk = trim($_POST['no_induk']);
        $nama = trim($_POST['nama']);
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $usia = intval($_POST['usia']);
        $jenjang = trim($_POST['jenjang']);
        $kelas = trim($_POST['kelas']);
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
            'jenjang' => $jenjang,
            'kelas' => $kelas,
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
        $jenjang = trim($_POST['edit_jenjang']);
        $kelas = trim($_POST['edit_kelas']);
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
            'jenjang' => $jenjang,
            'kelas' => $kelas,
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

// Get ALL siswa (for live filtering)
$all_siswa = [];
if ($supabaseConnected) {
  $result = supabaseSelect('siswa', ['order' => 'no_induk.asc']);
  if ($result['success']) {
    $all_siswa = $result['data'];
  }
}

// Handle Excel Export
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
  // Filter the data for export
  $filtered_siswa = $all_siswa;
  
  // Search filter
  if (!empty($search_query)) {
    $search_lower = strtolower(trim($search_query));
    $keywords = array_filter(explode(' ', $search_lower));
    $filtered_siswa = array_filter($filtered_siswa, function($siswa) use ($search_lower, $keywords) {
      $jk = $siswa['jenis_kelamin'] ?? '';
      $jk_short = ($jk === 'Laki-laki') ? 'L' : (($jk === 'Perempuan') ? 'P' : '');
      $usia = strval($siswa['usia'] ?? '');
      $kelas = strval($siswa['kelas'] ?? '');
      
      $rowText = strtolower(implode(' ', [
        $siswa['no_induk'] ?? '',
        $siswa['nama'] ?? '',
        $jk,
        $jk_short,
        $usia,
        $usia ? $usia . ' tahun' : '',
        $siswa['jenjang'] ?? '',
        $kelas,
        $kelas ? 'kelas ' . $kelas : '',
        $siswa['nama_ortu'] ?? '',
        $siswa['alamat_ortu'] ?? '',
        $siswa['telpon_ortu'] ?? '',
        $siswa['pekerjaan_ortu'] ?? '',
        $siswa['status'] ?? ''
      ]));

      // Direct match of the exact typed string
      if (str_contains($rowText, $search_lower)) {
        return true;
      }

      // Fallback: match all individual keywords
      foreach ($keywords as $kw) {
        if (!str_contains($rowText, $kw)) {
          return false;
        }
      }
      return true;
    });
  }
  
  // Jenis Kelamin filter
  if (!empty($filter_jenis_kelamin)) {
    $filtered_siswa = array_filter($filtered_siswa, function($siswa) use ($filter_jenis_kelamin) {
      return $siswa['jenis_kelamin'] === $filter_jenis_kelamin;
    });
  }
  
  // Usia range filter
  if ($filter_usia_min !== '') {
    $filtered_siswa = array_filter($filtered_siswa, function($siswa) use ($filter_usia_min) {
      return $siswa['usia'] >= intval($filter_usia_min);
    });
  }
  if ($filter_usia_max !== '') {
    $filtered_siswa = array_filter($filtered_siswa, function($siswa) use ($filter_usia_max) {
      return $siswa['usia'] <= intval($filter_usia_max);
    });
  }
  
  // Jenjang filter
  if (!empty($filter_jenjang)) {
    $filtered_siswa = array_filter($filtered_siswa, function($siswa) use ($filter_jenjang) {
      return $siswa['jenjang'] === $filter_jenjang;
    });
  }
  
  // Kelas filter
  if (!empty($filter_kelas)) {
    $filtered_siswa = array_filter($filtered_siswa, function($siswa) use ($filter_kelas) {
      return $siswa['kelas'] === $filter_kelas;
    });
  }

  // Set headers for download
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="data_siswa_' . date('Y-m-d_H-i-s') . '.csv"');
  
  // Open output stream
  $output = fopen('php://output', 'w');
  
  // Add UTF-8 BOM to fix Excel character encoding
  fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
  
  // Write CSV header row
  fputcsv($output, [
    'No Induk',
    'Nama Lengkap',
    'Jenis Kelamin',
    'Usia',
    'Jenjang',
    'Kelas',
    'Nama Orang Tua',
    'Alamat Orang Tua',
    'Pekerjaan Orang Tua',
    'Telepon Orang Tua',
    'Status',
    'Tanggal Dibuat',
    'Tanggal Diperbarui'
  ]);
  
  // Write data rows
  foreach ($filtered_siswa as $index => $siswa) {
    fputcsv($output, [
      $siswa['no_induk'] ?? '',
      $siswa['nama'] ?? '',
      $siswa['jenis_kelamin'] ?? '',
      $siswa['usia'] ?? '',
      $siswa['jenjang'] ?? '',
      $siswa['kelas'] ?? '',
      $siswa['nama_ortu'] ?? '',
      $siswa['alamat_ortu'] ?? '',
      $siswa['pekerjaan_ortu'] ?? '',
      $siswa['telpon_ortu'] ?? '',
      $siswa['status'] ?? '',
      $siswa['created_at'] ?? '',
      $siswa['updated_at'] ?? ''
    ]);
  }
  
  // Close output stream and exit
  fclose($output);
  exit;
}

include 'components/head.php';
include 'components/sidebar.php';

// Prepare data for JavaScript
$jsSiswaData = [];
foreach ($all_siswa as $siswa) {
  $jsSiswaData[] = [
    'id' => $siswa['id'],
    'no_induk' => $siswa['no_induk'],
    'nama' => $siswa['nama'],
    'jenis_kelamin' => $siswa['jenis_kelamin'],
    'usia' => $siswa['usia'],
    'jenjang' => $siswa['jenjang'],
    'kelas' => $siswa['kelas'],
    'nama_ortu' => $siswa['nama_ortu'],
    'telpon_ortu' => $siswa['telpon_ortu'],
    'pekerjaan_ortu' => $siswa['pekerjaan_ortu'],
    'alamat_ortu' => $siswa['alamat_ortu'],
    'foto' => $siswa['foto'] ?? 'https://picsum.photos/seed/default-siswa/300/400.jpg',
    'status' => $siswa['status']
  ];
}
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
              <button onclick="document.getElementById('modalSiswa').classList.remove('hidden')" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded-lg hover:bg-[#2F5B41] transition-colors uppercase tracking-widest flex items-center gap-2 disabled:opacity-50">
                <iconify-icon icon="lucide:plus"></iconify-icon>
                Tambah Siswa
              </button>
            </div>
          </div>

          <!-- Search & Filter Form -->
          <form method="get" action="kelola-siswa.php">
            <div class="bg-white p-4 rounded-xl border border-[#E8E4D9] shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4 mb-4">
              <div class="relative md:col-span-2">
                <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-[#9FB5A5]"></iconify-icon>
                <input id="searchInput" type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Cari..." class="w-full pl-10 pr-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm">
              </div>
              
              <div>
                <select name="jenis_kelamin" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm">
                  <option value="">Semua JK</option>
                  <option value="Laki-laki" <?php echo $filter_jenis_kelamin === 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                  <option value="Perempuan" <?php echo $filter_jenis_kelamin === 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                </select>
              </div>
              
              <div class="flex gap-2 items-center">
                <div class="flex-1">
                  <input type="number" name="usia_min" value="<?php echo htmlspecialchars($filter_usia_min); ?>" placeholder="Usia min" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm">
                </div>
                <span class="text-[#9FB5A5] text-sm font-bold">s/d</span>
                <div class="flex-1">
                  <input type="number" name="usia_max" value="<?php echo htmlspecialchars($filter_usia_max); ?>" placeholder="Usia max" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm">
                </div>
              </div>
              
              <div>
                <select name="jenjang" id="filterJenjang" onchange="updateFilterKelas()" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm">
                  <option value="">Semua Jenjang</option>
                  <option value="SDLB" <?php echo $filter_jenjang === 'SDLB' ? 'selected' : ''; ?>>SDLB</option>
                  <option value="SMPLB" <?php echo $filter_jenjang === 'SMPLB' ? 'selected' : ''; ?>>SMPLB</option>
                  <option value="SMALB" <?php echo $filter_jenjang === 'SMALB' ? 'selected' : ''; ?>>SMALB</option>
                </select>
              </div>
              
              <div>
                <select name="kelas" id="filterKelas" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm">
                  <option value="">Semua Kelas</option>
                </select>
              </div>
              
              <div class="flex gap-2 md:col-span-2">
                <button type="submit" class="flex-1 bg-[#3E6B4E] text-white text-xs font-bold px-4 py-3 rounded-lg hover:bg-[#2F5B41] transition-colors uppercase tracking-widest flex items-center justify-center gap-2">
                  <iconify-icon icon="lucide:filter"></iconify-icon>
                  Filter
                </button>
                <a href="kelola-siswa.php" class="flex-1 bg-[#5F6F65] text-white text-xs font-bold px-4 py-3 rounded-lg hover:bg-[#4a5a51] transition-colors uppercase tracking-widest flex items-center justify-center gap-2">
                  <iconify-icon icon="lucide:refresh-cw"></iconify-icon>
                  Reset
                </a>
                <button type="submit" name="export" value="excel" class="flex-1 bg-orange-500 text-white text-xs font-bold px-4 py-3 rounded-lg hover:bg-orange-600 transition-colors uppercase tracking-widest flex items-center justify-center gap-2">
                  <iconify-icon icon="lucide:file-spreadsheet"></iconify-icon>
                  Export Excel
                </button>
              </div>
            </div>
          </div>
          </form>
                  </div>

        <!-- Siswa List -->
        <?php if (empty($all_siswa)): ?>
          <div class="text-center py-12 bg-white rounded-xl border border-[#E8E4D9]">
            <iconify-icon icon="lucide:users" class="text-4xl text-[#9FB5A5] mb-4"></iconify-icon>
            <p class="text-[#5F6F65]">Belum ada data siswa.</p>
          </div>
        <?php else: ?>
          <!-- Table View -->
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
                    <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Jenjang</th>
                    <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Kelas</th>
                    <th class="text-left px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Status</th>
                    <th class="text-center px-4 py-4 text-xs font-bold text-white uppercase tracking-wider">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E4D9]">
                  <?php $no_urut = 1; foreach ($all_siswa as $siswa): ?>
                    <tr class="hover:bg-[#F9F8F4] transition-all duration-200 siswa-item <?php echo $siswa['status'] == 'Tidak Aktif' ? 'opacity-60' : ''; ?>" 
                        data-no-induk="<?php echo htmlspecialchars($siswa['no_induk']); ?>" 
                        data-nama="<?php echo htmlspecialchars($siswa['nama']); ?>" 
                        data-jenis-kelamin="<?php echo htmlspecialchars($siswa['jenis_kelamin']); ?>" 
                        data-usia="<?php echo htmlspecialchars($siswa['usia']); ?>" 
                        data-jenjang="<?php echo htmlspecialchars($siswa['jenjang']); ?>" 
                        data-kelas="<?php echo htmlspecialchars($siswa['kelas']); ?>" 
                        data-nama-ortu="<?php echo htmlspecialchars($siswa['nama_ortu'] ?? ''); ?>" 
                        data-alamat-ortu="<?php echo htmlspecialchars($siswa['alamat_ortu'] ?? ''); ?>" 
                        data-telpon-ortu="<?php echo htmlspecialchars($siswa['telpon_ortu'] ?? ''); ?>"
                        data-pekerjaan-ortu="<?php echo htmlspecialchars($siswa['pekerjaan_ortu'] ?? ''); ?>"
                        data-status="<?php echo htmlspecialchars($siswa['status'] ?? ''); ?>">
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
                      <td class="px-4 py-4 text-sm text-[#5F6F65]"><?php echo htmlspecialchars($siswa['jenjang'] ?? '-'); ?></td>
                      <td class="px-4 py-4 text-sm text-[#5F6F65]"><?php echo htmlspecialchars($siswa['kelas'] ?? '-'); ?></td>
                      <td class="px-4 py-4">
                        <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $siswa['status'] == 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                          <?php echo htmlspecialchars($siswa['status']); ?>
                        </span>
                      </td>
                      <td class="px-4 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                          <button onclick='openEditSiswaModal(<?php echo json_encode($siswa['id']); ?>, <?php echo json_encode($siswa['no_induk']); ?>, <?php echo json_encode($siswa['nama']); ?>, <?php echo json_encode($siswa['jenis_kelamin']); ?>, <?php echo json_encode($siswa['usia']); ?>, <?php echo json_encode($siswa['nama_ortu'] ?? ''); ?>, <?php echo json_encode($siswa['alamat_ortu'] ?? ''); ?>, <?php echo json_encode($siswa['pekerjaan_ortu'] ?? ''); ?>, <?php echo json_encode($siswa['telpon_ortu'] ?? ''); ?>, <?php echo json_encode($siswa['foto'] ?? 'https://picsum.photos/seed/default-siswa/300/400.jpg'); ?>, <?php echo json_encode($siswa['status'] ?? 'Aktif'); ?>, <?php echo json_encode($siswa['jenjang'] ?? ''); ?>, <?php echo json_encode($siswa['kelas'] ?? ''); ?>)' class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors">
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
        <?php endif; ?>
      </div>
    </div>
  </main>

  <!-- Modal Tambah Siswa -->
  <div id="modalSiswa" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('modalSiswa').classList.add('hidden')"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] flex flex-col overflow-hidden">
        <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between bg-gradient-to-r from-[#3E6B4E] to-[#2F5B41] flex-shrink-0">
          <h3 class="font-serif text-xl text-white">Tambah Siswa Baru</h3>
          <button onclick="document.getElementById('modalSiswa').classList.add('hidden')" class="text-white/80 hover:text-white transition-colors">
            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
          </button>
        </div>
        <form action="" method="POST" class="p-6 space-y-5 flex-1 overflow-y-auto" enctype="multipart/form-data">
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
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Jenjang</label>
              <select name="jenjang" id="jenjangTambah" required onchange="updateKelasOptions('Tambah')" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                <option value="">Pilih Jenjang</option>
                <option value="SDLB">SDLB</option>
                <option value="SMPLB">SMPLB</option>
                <option value="SMALB">SMALB</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Kelas</label>
              <select name="kelas" id="kelasTambah" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                <option value="">Pilih Kelas</option>
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
                  <option value="Petani">Petani</option>
                  <option value="Karyawan Swasta">Karyawan Swasta</option>
                  <option value="Pedagang Kecil">Pedagang Kecil</option>
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
      <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] flex flex-col overflow-hidden">
        <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between bg-gradient-to-r from-[#3E6B4E] to-[#2F5B41] flex-shrink-0">
          <h3 class="font-serif text-xl text-white">Edit Data Siswa</h3>
          <button onclick="document.getElementById('modalEditSiswa').classList.add('hidden')" class="text-white/80 hover:text-white transition-colors">
            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
          </button>
        </div>
        <form action="" method="POST" class="p-6 space-y-5 flex-1 overflow-y-auto" enctype="multipart/form-data">
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
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Jenjang</label>
              <select name="edit_jenjang" id="jenjangEdit" required onchange="updateKelasOptions('Edit')" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                <option value="">Pilih Jenjang</option>
                <option value="SDLB">SDLB</option>
                <option value="SMPLB">SMPLB</option>
                <option value="SMALB">SMALB</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Kelas</label>
              <select name="edit_kelas" id="kelasEdit" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                <option value="">Pilih Kelas</option>
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
                  <option value="Petani">Petani</option>
                  <option value="Karyawan Swasta">Karyawan Swasta</option>
                  <option value="Pedagang Kecil">Pedagang Kecil</option>
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

    // Update kelas options based on jenjang
    function updateKelasOptions(mode) {
      const jenjangSelect = document.getElementById('jenjang' + mode);
      const kelasSelect = document.getElementById('kelas' + mode);
      const jenjang = jenjangSelect.value;
      
      let kelasOptions = [];
      if (jenjang === 'SDLB') {
        kelasOptions = ['1', '2', '3', '4', '5', '6'];
      } else if (jenjang === 'SMPLB') {
        kelasOptions = ['7', '8', '9'];
      } else if (jenjang === 'SMALB') {
        kelasOptions = ['10', '11', '12'];
      }
      
      // Clear existing options
      kelasSelect.innerHTML = '<option value="">Pilih Kelas</option>';
      
      // Add new options
      kelasOptions.forEach(kelas => {
        const option = document.createElement('option');
        option.value = kelas;
        option.textContent = 'Kelas ' + kelas;
        kelasSelect.appendChild(option);
      });
    }

    function openEditSiswaModal(id, no_induk, nama, jenis_kelamin, usia, nama_ortu, alamat_ortu, pekerjaan_ortu, telpon_ortu, foto, status, jenjang = '', kelas = '') {
      document.getElementById('editSiswaId').value = id;
      document.getElementById('editNoInduk').value = no_induk;
      document.getElementById('editNama').value = nama;
      document.getElementById('editJenisKelamin').value = jenis_kelamin;
      document.getElementById('editUsia').value = usia;
      document.getElementById('jenjangEdit').value = jenjang;
      
      // Update kelas options first
      updateKelasOptions('Edit');
      // Then set kelas value
      document.getElementById('kelasEdit').value = kelas;
      
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

    // Update filter kelas options
    function updateFilterKelas() {
      const jenjangSelect = document.getElementById('filterJenjang');
      const kelasSelect = document.getElementById('filterKelas');
      const jenjang = jenjangSelect.value;
      
      let kelasOptions = [];
      if (jenjang === 'SDLB') {
        kelasOptions = ['1', '2', '3', '4', '5', '6'];
      } else if (jenjang === 'SMPLB') {
        kelasOptions = ['7', '8', '9'];
      } else if (jenjang === 'SMALB') {
        kelasOptions = ['10', '11', '12'];
      }
      
      // Clear existing options
      kelasSelect.innerHTML = '<option value="">Semua Kelas</option>';
      
      // Add new options
      kelasOptions.forEach(kelas => {
        const option = document.createElement('option');
        option.value = kelas;
        option.textContent = kelas;
        <?php if (!empty($filter_kelas)): ?>
          if (kelas === '<?php echo addslashes($filter_kelas); ?>') {
            option.selected = true;
          }
        <?php endif; ?>
        kelasSelect.appendChild(option);
      });
    }

    // Live filtering function
    function applyLiveFilter() {
      const searchInput = document.getElementById('searchInput').value.toLowerCase().trim();
      const searchKeywords = searchInput ? searchInput.split(/\s+/).filter(Boolean) : [];
      const jkSelect = document.querySelector('select[name="jenis_kelamin"]').value;
      const usiaMinInput = document.querySelector('input[name="usia_min"]').value;
      const usiaMaxInput = document.querySelector('input[name="usia_max"]').value;
      const jenjangSelect = document.getElementById('filterJenjang').value;
      const kelasSelect = document.getElementById('filterKelas').value;
      const siswaItems = document.querySelectorAll('.siswa-item');
      let matchCount = 0;

      siswaItems.forEach((item, index) => {
        const noInduk = item.dataset.noInduk || '';
        const nama = item.dataset.nama || '';
        const jenisKelamin = item.dataset.jenisKelamin || '';
        const jkShort = (jenisKelamin === 'Laki-laki') ? 'L' : ((jenisKelamin === 'Perempuan') ? 'P' : '');
        const usia = item.dataset.usia || '';
        const jenjang = item.dataset.jenjang || '';
        const kelas = item.dataset.kelas || '';
        const namaOrtu = item.dataset.namaOrtu || '';
        const alamatOrtu = item.dataset.alamatOrtu || '';
        const telponOrtu = item.dataset.telponOrtu || '';
        const pekerjaanOrtu = item.dataset.pekerjaanOrtu || '';
        const status = item.dataset.status || '';
        const rowNo = (index + 1).toString();

        const rowText = (
          rowNo + " " +
          noInduk + " " +
          nama + " " +
          jenisKelamin + " " + jkShort + " " +
          usia + " " + usia + " tahun " +
          jenjang + " " +
          kelas + " kelas " + kelas + " " +
          namaOrtu + " " +
          alamatOrtu + " " +
          telponOrtu + " " +
          pekerjaanOrtu + " " +
          status + " " +
          item.innerText
        ).toLowerCase();

        let matches = true;

        // Search filter: match exact typed text or all keywords across all fields
        if (searchInput) {
          if (!rowText.includes(searchInput)) {
            // If direct string match fails, check if all individual keywords match
            for (const keyword of searchKeywords) {
              if (!rowText.includes(keyword)) {
                matches = false;
                break;
              }
            }
          }
        }

        // Jenis Kelamin filter
        if (jkSelect && jenisKelamin !== jkSelect) {
          matches = false;
        }

        // Usia range filter
        const usiaNum = parseInt(usia, 10);
        if (usiaMinInput && (!isNaN(usiaNum) && usiaNum < parseInt(usiaMinInput, 10))) {
          matches = false;
        }
        if (usiaMaxInput && (!isNaN(usiaNum) && usiaNum > parseInt(usiaMaxInput, 10))) {
          matches = false;
        }

        // Jenjang filter
        if (jenjangSelect && jenjang.toLowerCase() !== jenjangSelect.toLowerCase()) {
          matches = false;
        }

        // Kelas filter
        if (kelasSelect && kelas.toLowerCase() !== kelasSelect.toLowerCase()) {
          matches = false;
        }

        if (matches) {
          item.style.display = '';
          matchCount++;
        } else {
          item.style.display = 'none';
        }
      });

      // Update filter results info
      const filterInfo = document.getElementById('filterResultsInfo');
      if (searchInput || jkSelect || usiaMinInput || usiaMaxInput || jenjangSelect || kelasSelect) {
        if (!filterInfo) {
          const newInfo = document.createElement('div');
          newInfo.id = 'filterResultsInfo';
          newInfo.className = 'p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg flex items-center gap-2 mt-4';
          newInfo.innerHTML = '<iconify-icon icon="lucide:info"></iconify-icon> Menemukan <span id="matchCount">0</span> siswa dengan filter yang diterapkan';
          const headerSection = document.querySelector('.mb-8');
          headerSection.appendChild(newInfo);
        }
        document.getElementById('matchCount').textContent = matchCount;
      } else if (filterInfo) {
        filterInfo.remove();
      }
    }

    // Initialize filter kelas
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize filter kelas options
      <?php if (!empty($filter_jenjang)): ?>
        updateFilterKelas();
      <?php endif; ?>

      // Add live filter event listeners
      document.getElementById('searchInput').addEventListener('input', applyLiveFilter);
      document.querySelector('select[name="jenis_kelamin"]').addEventListener('change', applyLiveFilter);
      document.querySelector('input[name="usia_min"]').addEventListener('input', applyLiveFilter);
      document.querySelector('input[name="usia_max"]').addEventListener('input', applyLiveFilter);
      document.getElementById('filterJenjang').addEventListener('change', function() {
        updateFilterKelas();
        applyLiveFilter();
      });
      document.getElementById('filterKelas').addEventListener('change', applyLiveFilter);

      // Apply initial filters if any
      <?php if (!empty($search_query) || !empty($filter_jenis_kelamin) || $filter_usia_min !== '' || $filter_usia_max !== '' || !empty($filter_jenjang) || !empty($filter_kelas)): ?>
        applyLiveFilter();
      <?php endif; ?>
    });
  </script>
</body>
</html>
