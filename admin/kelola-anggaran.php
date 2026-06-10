<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_login();

$title = "Kelola Anggaran & Belanja";
$page_title = "Kelola Anggaran & Belanja";
$success = '';
$error = '';

// --- ANGGARAN BOSN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_anggaran'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $tahun = trim($_POST['tahun']);
        $total_anggaran = preg_replace('/[^0-9]/g', '', $_POST['total_anggaran']);
        $realisasi = preg_replace('/[^0-9]/g', '', $_POST['realisasi']);
        
        $file_pdf = '';
        if (isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToSupabaseStorage($_FILES['file_pdf'], CLOUDINARY_FOLDER);
            if ($uploadResult['success']) {
                $file_pdf = $uploadResult['url'];
            }
        }

        $data = [
            'tahun' => $tahun,
            'total_anggaran' => (int)$total_anggaran,
            'realisasi' => (int)$realisasi,
            'file_pdf' => $file_pdf
        ];
        
        $response = supabaseInsert('anggaran_bosn', $data);
        if ($response['success']) {
            $success = 'Anggaran berhasil ditambahkan!';
        } else {
            $error = 'Gagal menyimpan anggaran!';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_anggaran'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $id = trim($_POST['id']);
        $tahun = trim($_POST['tahun']);
        $total_anggaran = preg_replace('/[^0-9]/g', '', $_POST['total_anggaran']);
        $realisasi = preg_replace('/[^0-9]/g', '', $_POST['realisasi']);
        
        $file_pdf = '';
        if (isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToSupabaseStorage($_FILES['file_pdf'], CLOUDINARY_FOLDER);
            if ($uploadResult['success']) {
                $file_pdf = $uploadResult['url'];
            }
        } else if (isset($_POST['existing_pdf']) && !empty($_POST['existing_pdf'])) {
            $file_pdf = $_POST['existing_pdf'];
        }

        $data = [
            'tahun' => $tahun,
            'total_anggaran' => (int)$total_anggaran,
            'realisasi' => (int)$realisasi,
            'file_pdf' => $file_pdf
        ];
        
        $response = supabaseUpdate('anggaran_bosn', $data, $id);
        if ($response['success']) $success = 'Anggaran berhasil diperbarui!';
        else $error = 'Gagal memperbarui anggaran!';
    }
}

if (isset($_GET['hapus_anggaran']) && !empty($_GET['hapus_anggaran'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        $response = supabaseDelete('anggaran_bosn', $_GET['hapus_anggaran']);
        if ($response['success']) $success = 'Anggaran berhasil dihapus!';
        else $error = 'Gagal menghapus anggaran!';
    }
}

// --- REALISASI BULANAN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_realisasi'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $tahun = trim($_POST['tahun_realisasi']);
        $bulan = trim($_POST['bulan']);
        $anggaran = preg_replace('/[^0-9]/g', '', $_POST['anggaran_bulan']);
        $realisasi = preg_replace('/[^0-9]/g', '', $_POST['realisasi_bulan']);
        
        $file_pdf = '';
        if (isset($_FILES['file_pdf_realisasi']) && $_FILES['file_pdf_realisasi']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToSupabaseStorage($_FILES['file_pdf_realisasi'], CLOUDINARY_FOLDER);
            if ($uploadResult['success']) {
                $file_pdf = $uploadResult['url'];
            }
        }

        $data = [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'anggaran' => (int)$anggaran,
            'realisasi' => (int)$realisasi,
            'file_pdf' => $file_pdf
        ];
        
        $response = supabaseInsert('realisasi_bulanan', $data);
        if ($response['success']) $success = 'Realisasi bulanan berhasil ditambahkan!';
        else $error = 'Gagal menyimpan realisasi!';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_realisasi'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $id = trim($_POST['id']);
        $tahun = trim($_POST['tahun_realisasi']);
        $bulan = trim($_POST['bulan']);
        $anggaran = preg_replace('/[^0-9]/g', '', $_POST['anggaran_bulan']);
        $realisasi = preg_replace('/[^0-9]/g', '', $_POST['realisasi_bulan']);
        
        $file_pdf = '';
        if (isset($_FILES['file_pdf_realisasi']) && $_FILES['file_pdf_realisasi']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToSupabaseStorage($_FILES['file_pdf_realisasi'], CLOUDINARY_FOLDER);
            if ($uploadResult['success']) {
                $file_pdf = $uploadResult['url'];
            }
        } else if (isset($_POST['existing_pdf']) && !empty($_POST['existing_pdf'])) {
            $file_pdf = $_POST['existing_pdf'];
        }

        $data = [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'anggaran' => (int)$anggaran,
            'realisasi' => (int)$realisasi,
            'file_pdf' => $file_pdf
        ];
        
        $response = supabaseUpdate('realisasi_bulanan', $data, $id);
        if ($response['success']) $success = 'Realisasi berhasil diperbarui!';
        else $error = 'Gagal memperbarui realisasi!';
    }
}

if (isset($_GET['hapus_realisasi']) && !empty($_GET['hapus_realisasi'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        $response = supabaseDelete('realisasi_bulanan', $_GET['hapus_realisasi']);
        if ($response['success']) $success = 'Realisasi berhasil dihapus!';
        else $error = 'Gagal menghapus realisasi!';
    }
}

// --- RENCANA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_rencana'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $jenis = trim($_POST['jenis_rencana']);
        $judul = trim($_POST['judul']);
        $deskripsi = trim($_POST['deskripsi']);
        
        $file_pdf = '';
        if (isset($_FILES['file_pdf_rencana']) && $_FILES['file_pdf_rencana']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToSupabaseStorage($_FILES['file_pdf_rencana'], CLOUDINARY_FOLDER);
            if ($uploadResult['success']) {
                $file_pdf = $uploadResult['url'];
            }
        }

        $data = [
            'jenis_rencana' => $jenis,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'file_pdf' => $file_pdf
        ];
        
        $response = supabaseInsert('rencana_anggaran', $data);
        if ($response['success']) $success = 'Rencana berhasil ditambahkan!';
        else $error = 'Gagal menyimpan rencana!';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_rencana'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $id = trim($_POST['id']);
        $jenis = trim($_POST['jenis_rencana']);
        $judul = trim($_POST['judul']);
        $deskripsi = trim($_POST['deskripsi']);
        
        $file_pdf = '';
        if (isset($_FILES['file_pdf_rencana']) && $_FILES['file_pdf_rencana']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToSupabaseStorage($_FILES['file_pdf_rencana'], CLOUDINARY_FOLDER);
            if ($uploadResult['success']) {
                $file_pdf = $uploadResult['url'];
            }
        } else if (isset($_POST['existing_pdf']) && !empty($_POST['existing_pdf'])) {
            $file_pdf = $_POST['existing_pdf'];
        }

        $data = [
            'jenis_rencana' => $jenis,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'file_pdf' => $file_pdf
        ];
        
        $response = supabaseUpdate('rencana_anggaran', $data, $id);
        if ($response['success']) $success = 'Rencana berhasil diperbarui!';
        else $error = 'Gagal memperbarui rencana!';
    }
}

if (isset($_GET['hapus_rencana']) && !empty($_GET['hapus_rencana'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        $response = supabaseDelete('rencana_anggaran', $_GET['hapus_rencana']);
        if ($response['success']) $success = 'Rencana berhasil dihapus!';
        else $error = 'Gagal menghapus rencana!';
    }
}

// --- AMBIL DATA ---
$anggaran = [];
if ($supabaseConnected) {
    $anggaranResult = supabaseSelect('anggaran_bosn', ['order' => 'tahun.desc']);
    if ($anggaranResult['success']) {
        $anggaran = $anggaranResult['data'];
    }
}

$realisasi = [];
if ($supabaseConnected) {
    $realisasiResult = supabaseSelect('realisasi_bulanan', ['order' => 'tahun.desc']);
    if ($realisasiResult['success']) {
        $realisasi = $realisasiResult['data'];
        
        function getMonthNumber($monthName) {
            $months = [
                'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4, 'Mei' => 5, 'Juni' => 6,
                'Juli' => 7, 'Agustus' => 8, 'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12
            ];
            return $months[$monthName] ?? 0;
        }
        
        usort($realisasi, function($a, $b) {
            if ($a['tahun'] != $b['tahun']) {
                return $b['tahun'] - $a['tahun'];
            }
            return getMonthNumber($a['bulan']) - getMonthNumber($b['bulan']);
        });
    }
}

$rencana = [];
if ($supabaseConnected) {
    $rencanaResult = supabaseSelect('rencana_anggaran', ['order' => 'created_at.desc']);
    if ($rencanaResult['success']) {
        $rencana = $rencanaResult['data'];
    }
}

include 'components/head.php';
include 'components/sidebar.php';
?>
<!-- Main Content -->
<main class="flex-1 flex flex-col overflow-hidden">
    <?php include 'components/header.php'; ?>
    <div class="flex-1 overflow-y-auto p-8">
        <div class="max-w-7xl space-y-8">
            <?php if ($success): ?>
                <div class="p-4 bg-green-100 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
                    <iconify-icon icon="lucide:check-circle" class="text-xl"></iconify-icon>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="p-4 bg-red-100 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
                    <iconify-icon icon="lucide:alert-circle" class="text-xl"></iconify-icon>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (!$supabaseConnected): ?>
                <div class="p-4 bg-yellow-50 text-yellow-800 border border-yellow-200 rounded-xl">
                    <iconify-icon icon="lucide:alert-triangle" class="inline mr-2"></iconify-icon>
                    PERINGATAN: Supabase tidak terhubung! Perubahan tidak dapat disimpan.
                </div>
            <?php endif; ?>

            <!-- TAB NAVIGATION -->
            <div class="flex gap-4 border-b border-gray-200 pb-2">
                <button onclick="showTab('anggaran')" id="tab-anggaran" class="px-6 py-3 text-sm font-semibold border-b-2 border-blue-500 text-blue-600 transition-all">
                    <iconify-icon icon="lucide:wallet" class="inline mr-2"></iconify-icon>Anggaran BOSN
                </button>
                <button onclick="showTab('realisasi')" id="tab-realisasi" class="px-6 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all">
                    <iconify-icon icon="lucide:calendar" class="inline mr-2"></iconify-icon>Realisasi Bulanan
                </button>
                <button onclick="showTab('rencana')" id="tab-rencana" class="px-6 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all">
                    <iconify-icon icon="lucide:clipboard-list" class="inline mr-2"></iconify-icon>Rencana Anggaran
                </button>
            </div>

            <!-- ANGGARAN BOSN -->
            <div id="content-anggaran">
                <section>
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">Total Anggaran Dana BOSN</h3>
                        <button onclick="document.getElementById('modalAnggaran').classList.remove('hidden')" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-gradient-to-r from-blue-500 to-blue-600 text-white text-sm font-semibold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-2 disabled:opacity-50">
                            <iconify-icon icon="lucide:plus"></iconify-icon>
                            Tambah Anggaran
                        </button>
                    </div>
                    <div class="flex justify-end mb-4">
                        <div class="flex bg-white border border-gray-200 rounded-xl p-1">
                            <button onclick="setAnggaranView('table')" id="btn-anggaran-table" class="px-4 py-2 rounded-lg transition-all font-medium flex items-center gap-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md">
                                <iconify-icon icon="lucide:table"></iconify-icon> Tabel
                            </button>
                            <button onclick="setAnggaranView('grid')" id="btn-anggaran-grid" class="px-4 py-2 rounded-lg transition-all font-medium flex items-center gap-2">
                                <iconify-icon icon="lucide:grid"></iconify-icon> Grid
                            </button>
                        </div>
                    </div>
                    
                    <!-- Tabel View -->
                    <div id="anggaran-table-view" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Tahun</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Total Anggaran</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Realisasi</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Capaian</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($anggaran as $a): 
                                        $persen = $a['total_anggaran'] > 0 ? round(($a['realisasi'] / $a['total_anggaran']) * 100, 1) : 0;
                                    ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($a['tahun']); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-600">Rp <?php echo number_format($a['total_anggaran'], 0, ',', '.'); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-600">Rp <?php echo number_format($a['realisasi'], 0, ',', '.'); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-32 bg-gray-200 rounded-full h-3 overflow-hidden">
                                                    <div class="h-full rounded-full transition-all duration-1000 <?php echo $persen >=80 ? 'bg-green-500' : ($persen >=50 ? 'bg-yellow-500' : 'bg-red-500'); ?>" style="width: <?php echo $persen; ?>%"></div>
                                                </div>
                                                <span class="font-semibold text-sm <?php echo $persen >= 80 ? 'text-green-600' : ($persen >=50 ? 'text-yellow-600' : 'text-red-600'); ?>"><?php echo $persen; ?>%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <?php if ($a['file_pdf']): ?>
                                                    <a href="<?php echo $a['file_pdf']; ?>" target="_blank" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-all">
                                                        <iconify-icon icon="lucide:file-text"></iconify-icon>
                                                    </a>
                                                <?php endif; ?>
                                                <button onclick="openEditAnggaran('<?php echo htmlspecialchars($a['id']); ?>', '<?php echo htmlspecialchars($a['tahun']); ?>', '<?php echo $a['total_anggaran']; ?>', '<?php echo $a['realisasi']; ?>', '<?php echo htmlspecialchars($a['file_pdf'] ?? ''); ?>')" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-all">
                                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                                </button>
                                                <a href="?hapus_anggaran=<?php echo $a['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
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

                    <!-- Grid View -->
                    <div id="anggaran-grid-view" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
                        <?php foreach ($anggaran as $a): 
                            $persen = $a['total_anggaran'] > 0 ? round(($a['realisasi'] / $a['total_anggaran']) * 100, 1) : 0;
                        ?>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                            <div class="p-6">
                                <h4 class="text-xl font-bold text-gray-800 mb-3">Tahun <?php echo $a['tahun']; ?></h4>
                                <div class="space-y-2 text-sm text-gray-600 mb-4">
                                    <p><span class="font-semibold">Anggaran:</span> Rp <?php echo number_format($a['total_anggaran'], 0, ',', '.'); ?></p>
                                    <p><span class="font-semibold">Realisasi:</span> Rp <?php echo number_format($a['realisasi'], 0, ',', '.'); ?></p>
                                </div>
                                <div class="mb-4">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span>Capaian</span>
                                        <span class="font-bold <?php echo $persen >= 80 ? 'text-green-600' : ($persen >=50 ? 'text-yellow-600' : 'text-red-600'); ?>"><?php echo $persen; ?>%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-1000 <?php echo $persen >=80 ? 'bg-green-500' : ($persen >=50 ? 'bg-yellow-500' : 'bg-red-500'); ?>" style="width: <?php echo $persen; ?>%"></div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <?php if ($a['file_pdf']): ?>
                                            <a href="<?php echo $a['file_pdf']; ?>" target="_blank" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-all">
                                                <iconify-icon icon="lucide:file-text"></iconify-icon>
                                            </a>
                                        <?php endif; ?>
                                        <button onclick="openEditAnggaran('<?php echo htmlspecialchars($a['id']); ?>', '<?php echo htmlspecialchars($a['tahun']); ?>', '<?php echo $a['total_anggaran']; ?>', '<?php echo $a['realisasi']; ?>', '<?php echo htmlspecialchars($a['file_pdf'] ?? ''); ?>')" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-all">
                                            <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                                        </button>
                                        <a href="?hapus_anggaran=<?php echo $a['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                            <iconify-icon icon="lucide:trash-2" class="w-5 h-5"></iconify-icon>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <!-- REALISASI BULANAN -->
            <div id="content-realisasi" class="hidden">
                <section>
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">Realisasi Bulanan</h3>
                        <button onclick="document.getElementById('modalRealisasi').classList.remove('hidden')" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-gradient-to-r from-purple-500 to-purple-600 text-white text-sm font-semibold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-2 disabled:opacity-50">
                            <iconify-icon icon="lucide:plus"></iconify-icon>
                            Tambah Realisasi
                        </button>
                    </div>
                    <div class="flex justify-end mb-4">
                        <div class="flex bg-white border border-gray-200 rounded-xl p-1">
                            <button onclick="setRealisasiView('table')" id="btn-realisasi-table" class="px-4 py-2 rounded-lg transition-all font-medium flex items-center gap-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-md">
                                <iconify-icon icon="lucide:table"></iconify-icon> Tabel
                            </button>
                            <button onclick="setRealisasiView('grid')" id="btn-realisasi-grid" class="px-4 py-2 rounded-lg transition-all font-medium flex items-center gap-2">
                                <iconify-icon icon="lucide:grid"></iconify-icon> Grid
                            </button>
                        </div>
                    </div>
                    
                    <!-- Tabel View -->
                    <div id="realisasi-table-view" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gradient-to-r from-purple-500 to-purple-600 text-white">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Bulan</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Tahun</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Anggaran</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Realisasi</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Capaian</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($realisasi as $r): 
                                        $persen = $r['anggaran'] > 0 ? round(($r['realisasi'] / $r['anggaran']) * 100, 1) : 0;
                                    ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($r['bulan']); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-600"><?php echo htmlspecialchars($r['tahun']); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-600">Rp <?php echo number_format($r['anggaran'], 0, ',', '.'); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-600">Rp <?php echo number_format($r['realisasi'], 0, ',', '.'); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-32 bg-gray-200 rounded-full h-3 overflow-hidden">
                                                    <div class="h-full rounded-full transition-all duration-1000 <?php echo $persen >=80 ? 'bg-green-500' : ($persen >=50 ? 'bg-yellow-500' : 'bg-red-500'); ?>" style="width: <?php echo $persen; ?>%"></div>
                                                </div>
                                                <span class="font-semibold text-sm <?php echo $persen >= 80 ? 'text-green-600' : ($persen >=50 ? 'text-yellow-600' : 'text-red-600'); ?>"><?php echo $persen; ?>%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <?php if ($r['file_pdf']): ?>
                                                    <a href="<?php echo $r['file_pdf']; ?>" target="_blank" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-all">
                                                        <iconify-icon icon="lucide:file-text"></iconify-icon>
                                                    </a>
                                                <?php endif; ?>
                                                <button onclick="openEditRealisasi('<?php echo htmlspecialchars($r['id']); ?>', '<?php echo htmlspecialchars($r['tahun']); ?>', '<?php echo htmlspecialchars($r['bulan']); ?>', '<?php echo $r['anggaran']; ?>', '<?php echo $r['realisasi']; ?>', '<?php echo htmlspecialchars($r['file_pdf'] ?? ''); ?>')" class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-all">
                                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                                </button>
                                                <a href="?hapus_realisasi=<?php echo $r['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
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

                    <!-- Grid View -->
                    <div id="realisasi-grid-view" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
                        <?php foreach ($realisasi as $r): 
                            $persen = $r['anggaran'] > 0 ? round(($r['realisasi'] / $r['anggaran']) * 100, 1) : 0;
                        ?>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                            <div class="p-6">
                                <h4 class="text-xl font-bold text-gray-800 mb-3"><?php echo $r['bulan']; ?> <?php echo $r['tahun']; ?></h4>
                                <div class="space-y-2 text-sm text-gray-600 mb-4">
                                    <p><span class="font-semibold">Anggaran:</span> Rp <?php echo number_format($r['anggaran'], 0, ',', '.'); ?></p>
                                    <p><span class="font-semibold">Realisasi:</span> Rp <?php echo number_format($r['realisasi'], 0, ',', '.'); ?></p>
                                </div>
                                <div class="mb-4">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span>Capaian</span>
                                        <span class="font-bold <?php echo $persen >= 80 ? 'text-green-600' : ($persen >=50 ? 'text-yellow-600' : 'text-red-600'); ?>"><?php echo $persen; ?>%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-1000 <?php echo $persen >=80 ? 'bg-green-500' : ($persen >=50 ? 'bg-yellow-500' : 'bg-red-500'); ?>" style="width: <?php echo $persen; ?>%"></div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <?php if ($r['file_pdf']): ?>
                                            <a href="<?php echo $r['file_pdf']; ?>" target="_blank" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-all">
                                                <iconify-icon icon="lucide:file-text"></iconify-icon>
                                            </a>
                                        <?php endif; ?>
                                        <button onclick="openEditRealisasi('<?php echo htmlspecialchars($r['id']); ?>', '<?php echo htmlspecialchars($r['tahun']); ?>', '<?php echo htmlspecialchars($r['bulan']); ?>', '<?php echo $r['anggaran']; ?>', '<?php echo $r['realisasi']; ?>', '<?php echo htmlspecialchars($r['file_pdf'] ?? ''); ?>')" class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-all">
                                            <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                                        </button>
                                        <a href="?hapus_realisasi=<?php echo $r['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                            <iconify-icon icon="lucide:trash-2" class="w-5 h-5"></iconify-icon>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <!-- RENCANA -->
            <div id="content-rencana" class="hidden">
                <section>
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">Rencana Anggaran</h3>
                        <button onclick="document.getElementById('modalRencana').classList.remove('hidden')" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-gradient-to-r from-green-500 to-green-600 text-white text-sm font-semibold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-2 disabled:opacity-50">
                            <iconify-icon icon="lucide:plus"></iconify-icon>
                            Tambah Rencana
                        </button>
                    </div>
                    <div class="flex justify-end mb-4">
                        <div class="flex bg-white border border-gray-200 rounded-xl p-1">
                            <button onclick="setRencanaView('table')" id="btn-rencana-table" class="px-4 py-2 rounded-lg transition-all font-medium flex items-center gap-2 bg-gradient-to-r from-green-500 to-green-600 text-white shadow-md">
                                <iconify-icon icon="lucide:table"></iconify-icon> Tabel
                            </button>
                            <button onclick="setRencanaView('grid')" id="btn-rencana-grid" class="px-4 py-2 rounded-lg transition-all font-medium flex items-center gap-2">
                                <iconify-icon icon="lucide:grid"></iconify-icon> Grid
                            </button>
                        </div>
                    </div>
                    
                    <?php 
                        $jenis_label = [
                            'pendek' => 'Rencana Jangka Pendek',
                            'menengah' => 'Rencana Jangka Menengah',
                            'panjang' => 'Rencana Jangka Panjang'
                        ];
                    ?>

                    <!-- Tabel View -->
                    <div id="rencana-table-view" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gradient-to-r from-green-500 to-green-600 text-white">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Jenis Rencana</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Judul</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Deskripsi</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($rencana as $rc): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                                <?php echo htmlspecialchars(strtoupper($jenis_label[$rc['jenis_rencana']] ?? $rc['jenis_rencana'])); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($rc['judul']); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-500 text-sm line-clamp-2"><?php echo htmlspecialchars($rc['deskripsi'] ?? '-'); ?></span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <?php if ($rc['file_pdf']): ?>
                                                    <a href="<?php echo $rc['file_pdf']; ?>" target="_blank" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-all">
                                                        <iconify-icon icon="lucide:file-text"></iconify-icon>
                                                    </a>
                                                <?php endif; ?>
                                                <button onclick="openEditRencana('<?php echo htmlspecialchars($rc['id']); ?>', '<?php echo htmlspecialchars($rc['jenis_rencana']); ?>', '<?php echo addslashes(htmlspecialchars($rc['judul'])); ?>', '<?php echo addslashes(htmlspecialchars($rc['deskripsi'] ?? '')); ?>', '<?php echo htmlspecialchars($rc['file_pdf'] ?? ''); ?>')" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-all">
                                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                                </button>
                                                <a href="?hapus_rencana=<?php echo $rc['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
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

                    <!-- Grid View -->
                    <div id="rencana-grid-view" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
                        <?php foreach ($rencana as $rc): ?>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                            <div class="p-6">
                                <div class="text-xs uppercase font-bold text-gray-500 mb-2"><?php echo $jenis_label[$rc['jenis_rencana']] ?? $rc['jenis_rencana']; ?></div>
                                <h4 class="text-lg font-bold text-gray-800 mb-2"><?php echo $rc['judul']; ?></h4>
                                <?php if ($rc['deskripsi']): ?>
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-3"><?php echo htmlspecialchars($rc['deskripsi']); ?></p>
                                <?php endif; ?>
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <?php if ($rc['file_pdf']): ?>
                                            <a href="<?php echo $rc['file_pdf']; ?>" target="_blank" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-all">
                                                <iconify-icon icon="lucide:file-text"></iconify-icon>
                                            </a>
                                        <?php endif; ?>
                                        <button onclick="openEditRencana('<?php echo htmlspecialchars($rc['id']); ?>', '<?php echo htmlspecialchars($rc['jenis_rencana']); ?>', '<?php echo addslashes(htmlspecialchars($rc['judul'])); ?>', '<?php echo addslashes(htmlspecialchars($rc['deskripsi'] ?? '')); ?>', '<?php echo htmlspecialchars($rc['file_pdf'] ?? ''); ?>')" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-all">
                                            <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                                        </button>
                                        <a href="?hapus_rencana=<?php echo $rc['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                            <iconify-icon icon="lucide:trash-2" class="w-5 h-5"></iconify-icon>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

        </div>
    </div>
</main>

<!-- MODAL TAMBAH ANGGARAN -->
<div id="modalAnggaran" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-blue-500 to-blue-600">
            <h3 class="font-semibold text-lg text-white">Tambah Anggaran Dana BOSN</h3>
            <button onclick="document.getElementById('modalAnggaran').classList.add('hidden')" class="p-2 text-white/80 hover:bg-white/20 rounded-xl transition-all">
                <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun</label>
                <input type="text" name="tahun" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all" placeholder="2024">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Total Anggaran</label>
                <input type="text" name="total_anggaran" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all uang" placeholder="Rp 0">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Realisasi</label>
                <input type="text" name="realisasi" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all uang" placeholder="Rp 0">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload PDF (Opsional)</label>
                <input type="file" name="file_pdf" accept=".pdf" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all">
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modalAnggaran').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                    Batal
                </button>
                <button type="submit" name="tambah_anggaran" class="px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all shadow-md">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT ANGGARAN -->
<div id="modalEditAnggaran" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-blue-500 to-blue-600">
            <h3 class="font-semibold text-lg text-white">Edit Anggaran Dana BOSN</h3>
            <button onclick="document.getElementById('modalEditAnggaran').classList.add('hidden')" class="p-2 text-white/80 hover:bg-white/20 rounded-xl transition-all">
                <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            <input type="hidden" name="id" id="edit_anggaran_id">
            <input type="hidden" name="existing_pdf" id="edit_anggaran_existing_pdf">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun</label>
                <input type="text" name="tahun" id="edit_anggaran_tahun" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all" placeholder="2024">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Total Anggaran</label>
                <input type="text" name="total_anggaran" id="edit_anggaran_total" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all uang" placeholder="Rp 0">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Realisasi</label>
                <input type="text" name="realisasi" id="edit_anggaran_realisasi" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all uang" placeholder="Rp 0">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload PDF Baru (Opsional)</label>
                <input type="file" name="file_pdf" accept=".pdf" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all">
                <p id="edit_anggaran_pdf_info" class="text-sm text-gray-500 mt-1"></p>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modalEditAnggaran').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                    Batal
                </button>
                <button type="submit" name="edit_anggaran" class="px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all shadow-md">
                    Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH REALISASI -->
<div id="modalRealisasi" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-purple-500 to-purple-600">
            <h3 class="font-semibold text-lg text-white">Tambah Realisasi Bulanan</h3>
            <button onclick="document.getElementById('modalRealisasi').classList.add('hidden')" class="p-2 text-white/80 hover:bg-white/20 rounded-xl transition-all">
                <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun</label>
                <input type="text" name="tahun_realisasi" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all" placeholder="2024">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Bulan</label>
                <select name="bulan" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all">
                    <option>Januari</option>
                    <option>Februari</option>
                    <option>Maret</option>
                    <option>April</option>
                    <option>Mei</option>
                    <option>Juni</option>
                    <option>Juli</option>
                    <option>Agustus</option>
                    <option>September</option>
                    <option>Oktober</option>
                    <option>November</option>
                    <option>Desember</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Anggaran Bulanan</label>
                <input type="text" name="anggaran_bulan" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all uang" placeholder="Rp 0">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Realisasi Bulanan</label>
                <input type="text" name="realisasi_bulan" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all uang" placeholder="Rp 0">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload PDF (Opsional)</label>
                <input type="file" name="file_pdf_realisasi" accept=".pdf" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all">
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modalRealisasi').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                    Batal
                </button>
                <button type="submit" name="tambah_realisasi" class="px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl hover:from-purple-600 hover:to-purple-700 transition-all shadow-md">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT REALISASI -->
<div id="modalEditRealisasi" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-purple-500 to-purple-600">
            <h3 class="font-semibold text-lg text-white">Edit Realisasi Bulanan</h3>
            <button onclick="document.getElementById('modalEditRealisasi').classList.add('hidden')" class="p-2 text-white/80 hover:bg-white/20 rounded-xl transition-all">
                <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            <input type="hidden" name="id" id="edit_realisasi_id">
            <input type="hidden" name="existing_pdf" id="edit_realisasi_existing_pdf">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun</label>
                <input type="text" name="tahun_realisasi" id="edit_realisasi_tahun" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all" placeholder="2024">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Bulan</label>
                <select name="bulan" id="edit_realisasi_bulan" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all">
                    <option>Januari</option>
                    <option>Februari</option>
                    <option>Maret</option>
                    <option>April</option>
                    <option>Mei</option>
                    <option>Juni</option>
                    <option>Juli</option>
                    <option>Agustus</option>
                    <option>September</option>
                    <option>Oktober</option>
                    <option>November</option>
                    <option>Desember</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Anggaran Bulanan</label>
                <input type="text" name="anggaran_bulan" id="edit_realisasi_anggaran" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all uang" placeholder="Rp 0">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Realisasi Bulanan</label>
                <input type="text" name="realisasi_bulan" id="edit_realisasi_realisasi" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all uang" placeholder="Rp 0">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload PDF Baru (Opsional)</label>
                <input type="file" name="file_pdf_realisasi" accept=".pdf" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all">
                <p id="edit_realisasi_pdf_info" class="text-sm text-gray-500 mt-1"></p>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modalEditRealisasi').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                    Batal
                </button>
                <button type="submit" name="edit_realisasi" class="px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl hover:from-purple-600 hover:to-purple-700 transition-all shadow-md">
                    Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH RENCANA -->
<div id="modalRencana" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-green-500 to-green-600">
            <h3 class="font-semibold text-lg text-white">Tambah Rencana</h3>
            <button onclick="document.getElementById('modalRencana').classList.add('hidden')" class="p-2 text-white/80 hover:bg-white/20 rounded-xl transition-all">
                <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Rencana</label>
                <select name="jenis_rencana" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all">
                    <option value="pendek">Rencana Jangka Pendek</option>
                    <option value="menengah">Rencana Jangka Menengah</option>
                    <option value="panjang">Rencana Jangka Panjang</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Judul</label>
                <input type="text" name="judul" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all" placeholder="Judul Rencana">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                <textarea name="deskripsi" rows="4" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all" placeholder="Deskripsi rencana..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload PDF (Opsional)</label>
                <input type="file" name="file_pdf_rencana" accept=".pdf" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all">
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modalRencana').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                    Batal
                </button>
                <button type="submit" name="tambah_rencana" class="px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 transition-all shadow-md">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT RENCANA -->
<div id="modalEditRencana" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-green-500 to-green-600">
            <h3 class="font-semibold text-lg text-white">Edit Rencana</h3>
            <button onclick="document.getElementById('modalEditRencana').classList.add('hidden')" class="p-2 text-white/80 hover:bg-white/20 rounded-xl transition-all">
                <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            <input type="hidden" name="id" id="edit_rencana_id">
            <input type="hidden" name="existing_pdf" id="edit_rencana_existing_pdf">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Rencana</label>
                <select name="jenis_rencana" id="edit_rencana_jenis" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all">
                    <option value="pendek">Rencana Jangka Pendek</option>
                    <option value="menengah">Rencana Jangka Menengah</option>
                    <option value="panjang">Rencana Jangka Panjang</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Judul</label>
                <input type="text" name="judul" id="edit_rencana_judul" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all" placeholder="Judul Rencana">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                <textarea name="deskripsi" id="edit_rencana_deskripsi" rows="4" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all" placeholder="Deskripsi rencana..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload PDF Baru (Opsional)</label>
                <input type="file" name="file_pdf_rencana" accept=".pdf" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all">
                <p id="edit_rencana_pdf_info" class="text-sm text-gray-500 mt-1"></p>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modalEditRencana').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                    Batal
                </button>
                <button type="submit" name="edit_rencana" class="px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 transition-all shadow-md">
                    Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Tab Navigation
    function showTab(tab) {
        // Hide all tab content
        document.querySelectorAll('#content-anggaran, #content-realisasi, #content-rencana').forEach(el => {
            el.classList.add('hidden');
        });

        // Remove active state from all tabs
        document.querySelectorAll('#tab-anggaran, #tab-realisasi, #tab-rencana').forEach(el => {
            el.classList.remove('border-blue-500', 'text-blue-600', 'border-purple-500', 'text-purple-600', 'border-green-500', 'text-green-600');
            el.classList.add('border-transparent', 'text-gray-500');
        });

        // Show selected tab content
        document.getElementById('content-' + tab).classList.remove('hidden');

        // Add active state to selected tab
        const activeTab = document.getElementById('tab-' + tab);
        activeTab.classList.remove('border-transparent', 'text-gray-500');
        
        if (tab === 'anggaran') {
            activeTab.classList.add('border-blue-500', 'text-blue-600');
        } else if (tab === 'realisasi') {
            activeTab.classList.add('border-purple-500', 'text-purple-600');
        } else if (tab === 'rencana') {
            activeTab.classList.add('border-green-500', 'text-green-600');
        }
    }

    // Format rupiah otomatis
    document.querySelectorAll('.uang').forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/[^0-9]/g, '');
            if (!value) value = '0';
            this.value = 'Rp ' + parseInt(value).toLocaleString('id-ID');
        });
        // Format saat load
        if (input.value && input.value.startsWith('Rp')) {
            // Do nothing
        } else if (input.value) {
            input.value = 'Rp ' + parseInt(input.value).toLocaleString('id-ID');
        }
    });

    function openEditAnggaran(id, tahun, total, realisasi, pdf) {
        document.getElementById('edit_anggaran_id').value = id;
        document.getElementById('edit_anggaran_existing_pdf').value = pdf;
        document.getElementById('edit_anggaran_tahun').value = tahun;
        
        let totalInput = document.getElementById('edit_anggaran_total');
        totalInput.value = 'Rp ' + parseInt(total).toLocaleString('id-ID');
        
        let realisasiInput = document.getElementById('edit_anggaran_realisasi');
        realisasiInput.value = 'Rp ' + parseInt(realisasi).toLocaleString('id-ID');
        
        let pdfInfo = document.getElementById('edit_anggaran_pdf_info');
        if (pdf) {
            pdfInfo.textContent = 'PDF saat ini: Ada';
        } else {
            pdfInfo.textContent = '';
        }
        
        document.getElementById('modalEditAnggaran').classList.remove('hidden');
    }

    function openEditRealisasi(id, tahun, bulan, anggaran, realisasi, pdf) {
        document.getElementById('edit_realisasi_id').value = id;
        document.getElementById('edit_realisasi_tahun').value = tahun;
        document.getElementById('edit_realisasi_bulan').value = bulan;
        document.getElementById('edit_realisasi_existing_pdf').value = pdf;
        
        let anggaranInput = document.getElementById('edit_realisasi_anggaran');
        anggaranInput.value = 'Rp ' + parseInt(anggaran).toLocaleString('id-ID');
        
        let realisasiInput = document.getElementById('edit_realisasi_realisasi');
        realisasiInput.value = 'Rp ' + parseInt(realisasi).toLocaleString('id-ID');
        
        let pdfInfo = document.getElementById('edit_realisasi_pdf_info');
        if (pdf) {
            pdfInfo.textContent = 'PDF saat ini: Ada';
        } else {
            pdfInfo.textContent = '';
        }
        
        document.getElementById('modalEditRealisasi').classList.remove('hidden');
    }

    function openEditRencana(id, jenis, judul, deskripsi, pdf) {
        document.getElementById('edit_rencana_id').value = id;
        document.getElementById('edit_rencana_jenis').value = jenis;
        document.getElementById('edit_rencana_judul').value = judul;
        document.getElementById('edit_rencana_deskripsi').value = deskripsi;
        document.getElementById('edit_rencana_existing_pdf').value = pdf;
        
        let pdfInfo = document.getElementById('edit_rencana_pdf_info');
        if (pdf) {
            pdfInfo.textContent = 'PDF saat ini: Ada';
        } else {
            pdfInfo.textContent = '';
        }
        
        document.getElementById('modalEditRencana').classList.remove('hidden');
    }

    function setAnggaranView(view) {
        const gridView = document.getElementById('anggaran-grid-view');
        const tableView = document.getElementById('anggaran-table-view');
        const btnGrid = document.getElementById('btn-anggaran-grid');
        const btnTable = document.getElementById('btn-anggaran-table');

        if (view === 'table') {
            gridView.classList.add('hidden');
            tableView.classList.remove('hidden');
            btnTable.classList.add('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white', 'shadow-md');
            btnGrid.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white', 'shadow-md');
        } else {
            gridView.classList.remove('hidden');
            tableView.classList.add('hidden');
            btnGrid.classList.add('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white', 'shadow-md');
            btnTable.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white', 'shadow-md');
        }
    }

    function setRealisasiView(view) {
        const gridView = document.getElementById('realisasi-grid-view');
        const tableView = document.getElementById('realisasi-table-view');
        const btnGrid = document.getElementById('btn-realisasi-grid');
        const btnTable = document.getElementById('btn-realisasi-table');

        if (view === 'table') {
            gridView.classList.add('hidden');
            tableView.classList.remove('hidden');
            btnTable.classList.add('bg-gradient-to-r', 'from-purple-500', 'to-purple-600', 'text-white', 'shadow-md');
            btnGrid.classList.remove('bg-gradient-to-r', 'from-purple-500', 'to-purple-600', 'text-white', 'shadow-md');
        } else {
            gridView.classList.remove('hidden');
            tableView.classList.add('hidden');
            btnGrid.classList.add('bg-gradient-to-r', 'from-purple-500', 'to-purple-600', 'text-white', 'shadow-md');
            btnTable.classList.remove('bg-gradient-to-r', 'from-purple-500', 'to-purple-600', 'text-white', 'shadow-md');
        }
    }

    function setRencanaView(view) {
        const gridView = document.getElementById('rencana-grid-view');
        const tableView = document.getElementById('rencana-table-view');
        const btnGrid = document.getElementById('btn-rencana-grid');
        const btnTable = document.getElementById('btn-rencana-table');

        if (view === 'table') {
            gridView.classList.add('hidden');
            tableView.classList.remove('hidden');
            btnTable.classList.add('bg-gradient-to-r', 'from-green-500', 'to-green-600', 'text-white', 'shadow-md');
            btnGrid.classList.remove('bg-gradient-to-r', 'from-green-500', 'to-green-600', 'text-white', 'shadow-md');
        } else {
            gridView.classList.remove('hidden');
            tableView.classList.add('hidden');
            btnGrid.classList.add('bg-gradient-to-r', 'from-green-500', 'to-green-600', 'text-white', 'shadow-md');
            btnTable.classList.remove('bg-gradient-to-r', 'from-green-500', 'to-green-600', 'text-white', 'shadow-md');
        }
    }
</script>
</body>
</html>
