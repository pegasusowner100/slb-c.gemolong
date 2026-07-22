<?php
define('ADMIN_PAGE', true);
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/supabase_storage.php';
require_once '../includes/cloudinary-on.php';
require_login();

$title = "Kelola Pengumuman — SLB BC KARYA SEJAHTERA";
$page_title = "Kelola Pengumuman";
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
$uploadMaxSize = ini_get('upload_max_filesize') ?: 'sesuai konfigurasi server';

function pengumumanUploadErrorMessage($code) {
    switch ((int) $code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'Ukuran file melebihi batas upload server.';
        case UPLOAD_ERR_PARTIAL:
            return 'File hanya terupload sebagian. Coba unggah ulang.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Folder sementara upload tidak tersedia di server.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Server gagal menulis file upload.';
        case UPLOAD_ERR_EXTENSION:
            return 'Upload dihentikan oleh ekstensi PHP.';
        default:
            return 'Upload file gagal: kode error ' . $code;
    }
}

function isFileUrlAccessible($url) {
    if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode >= 200 && $httpCode < 400;
}

function uploadPengumumanPdf($file) {
    if (!isset($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [
            'success' => true,
            'url' => '',
            'file_name' => '',
            'no_file' => true
        ];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'url' => '',
            'file_name' => '',
            'error' => pengumumanUploadErrorMessage($file['error'] ?? 'unknown')
        ];
    }

    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if ($extension !== 'pdf') {
        return [
            'success' => false,
            'url' => '',
            'file_name' => '',
            'error' => 'File pengumuman harus berupa PDF.'
        ];
    }

    $fileName = $file['name'] ?? '';
    $lastError = '';

    if (function_exists('uploadToSupabaseStorage')) {
        $supabaseResult = uploadToSupabaseStorage($file, 'pengumuman');
        if ($supabaseResult['success']) {
            return [
                'success' => true,
                'url' => $supabaseResult['url'],
                'file_name' => $fileName,
                'storage' => 'supabase'
            ];
        }
        $lastError = 'Supabase Storage: ' . ($supabaseResult['error'] ?? 'Upload gagal.');
    } else {
        $lastError = 'Supabase Storage tidak tersedia.';
    }

    return [
        'success' => false,
        'url' => '',
        'file_name' => '',
        'error' => $lastError !== '' ? $lastError : 'Upload PDF gagal.'
    ];
}

function pengumumanUploadTimestamp($item) {
    $date = $item['created_at'] ?? $item['updated_at'] ?? $item['tgl'] ?? '';
    $time = $date ? strtotime($date) : false;
    return $time ?: 0;
}

// --- Handle Tambah Pengumuman ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_pengumuman'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $no = trim($_POST['no'] ?? '');
        $judul = trim($_POST['judul'] ?? '');
        $konten = trim($_POST['konten'] ?? '');
        $sumber = trim($_POST['sumber'] ?? '');
        $tgl = $_POST['tgl'] ?? '';
        $prioritas = $_POST['prioritas'] ?? 'Normal';
        $status = $_POST['status'] ?? 'published';

        $uploadResult = uploadPengumumanPdf($_FILES['file_pdf'] ?? null);

        if (!$uploadResult['success']) {
            $error = $uploadResult['error'] ?? 'Gagal upload PDF.';
        } else {
            $data = [
                'no' => $no,
                'judul' => $judul,
                'konten' => $konten,
                'sumber' => $sumber,
                'tgl' => $tgl,
                'prioritas' => $prioritas,
                'pdf' => $uploadResult['url'],
                'file_name' => $uploadResult['file_name'],
                'status' => $status
            ];

            $response = supabaseInsert('pengumuman', $data);
            if ($response['success']) {
                $storageInfo = !empty($uploadResult['storage']) ? ' File tersimpan di ' . $uploadResult['storage'] . '.' : '';
                $fallbackInfo = !empty($uploadResult['fallback_reason']) ? ' ' . $uploadResult['fallback_reason'] . '.' : '';
                $_SESSION['success'] = 'Pengumuman berhasil ditambahkan!' . $storageInfo . $fallbackInfo;
                header('Location: kelola-pengumuman.php');
                exit;
            } else {
                $error = 'Gagal menyimpan pengumuman: ' . ($response['error'] ?? 'Unknown error');
            }
        }
    }
}

// --- Handle Edit Pengumuman ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_pengumuman'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $id = trim($_POST['id'] ?? '');
        $no = trim($_POST['no'] ?? '');
        $judul = trim($_POST['judul'] ?? '');
        $konten = trim($_POST['konten'] ?? '');
        $sumber = trim($_POST['sumber'] ?? '');
        $tgl = $_POST['tgl'] ?? '';
        $prioritas = $_POST['prioritas'] ?? 'Normal';
        $status = $_POST['status'] ?? 'published';

        $uploadResult = uploadPengumumanPdf($_FILES['file_pdf'] ?? null);

        if (!$uploadResult['success']) {
            $error = $uploadResult['error'] ?? 'Gagal upload PDF.';
        } else {
            $file_pdf = $uploadResult['url'] ?: ($_POST['existing_pdf'] ?? '');

            $data = [
                'no' => $no,
                'judul' => $judul,
                'konten' => $konten,
                'sumber' => $sumber,
                'tgl' => $tgl,
                'prioritas' => $prioritas,
                'pdf' => $file_pdf,
                'status' => $status
            ];

            if (!empty($uploadResult['file_name'])) {
                $data['file_name'] = $uploadResult['file_name'];
            }

            $response = supabaseUpdate('pengumuman', $data, $id);
            if ($response['success']) {
                if (!empty($uploadResult['storage'])) {
                    $fallbackInfo = !empty($uploadResult['fallback_reason']) ? ' ' . $uploadResult['fallback_reason'] . '.' : '';
                    $_SESSION['success'] = 'Pengumuman berhasil diperbarui! File tersimpan di ' . $uploadResult['storage'] . '.' . $fallbackInfo;
                } elseif (!empty($file_pdf)) {
                    $_SESSION['success'] = 'Pengumuman berhasil diperbarui. Tidak ada file PDF baru yang diterima server; PDF lama tetap dipakai.';
                } else {
                    $_SESSION['success'] = 'Pengumuman berhasil diperbarui, tetapi belum ada file PDF yang tersimpan.';
                }
                header('Location: kelola-pengumuman.php');
                exit;
            } else {
                $error = 'Gagal memperbarui pengumuman: ' . ($response['error'] ?? 'Unknown error');
            }
        }
    }
}

// --- Handle Hapus Pengumuman ---
if (isset($_GET['hapus_pengumuman']) && !empty($_GET['hapus_pengumuman'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        $response = supabaseDelete('pengumuman', $_GET['hapus_pengumuman']);
        if ($response['success']) {
            $_SESSION['success'] = 'Pengumuman berhasil dihapus!';
        } else {
            $_SESSION['error'] = 'Gagal menghapus pengumuman!';
        }
        header('Location: kelola-pengumuman.php');
        exit;
    }
}

// --- Ambil Data Pengumuman ---
$pengumuman = [];
if ($supabaseConnected) {
    $pengumumanResult = supabaseSelect('pengumuman', ['order' => 'created_at.desc']);
    if ($pengumumanResult['success']) {
        $pengumuman = $pengumumanResult['data'];
        usort($pengumuman, function ($a, $b) {
            return pengumumanUploadTimestamp($b) <=> pengumumanUploadTimestamp($a);
        });
    }
}

// --- Ambil Data Sumber Info ---
$sumber_info = [];
if ($supabaseConnected) {
    $sumberResult = supabaseSelect('sumber_info', ['order' => 'nama.asc']);
    if ($sumberResult['success']) {
        $sumber_info = $sumberResult['data'];
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
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="p-4 bg-red-100 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
                    <iconify-icon icon="lucide:alert-circle" class="text-xl"></iconify-icon>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!$supabaseConnected): ?>
                <div class="p-4 bg-yellow-50 text-yellow-800 border border-yellow-200 rounded-xl">
                    <iconify-icon icon="lucide:alert-triangle" class="inline mr-2"></iconify-icon>
                    PERINGATAN: Supabase tidak terhubung! Perubahan tidak dapat disimpan.
                </div>
            <?php endif; ?>

            <!-- Header Section -->
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <h3 class="text-2xl font-bold text-gray-800">Daftar Pengumuman</h3>
                <div class="flex items-center gap-4">
                    <div class="flex bg-white border border-gray-200 rounded-xl p-1">
                        <button onclick="setView('table')" id="btn-table" class="px-4 py-2 rounded-lg transition-all font-medium flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-md">
                            <iconify-icon icon="lucide:table"></iconify-icon> Tabel
                        </button>
                        <button onclick="setView('grid')" id="btn-grid" class="px-4 py-2 rounded-lg transition-all font-medium flex items-center gap-2">
                            <iconify-icon icon="lucide:grid"></iconify-icon> Grid
                        </button>
                    </div>
                    <button onclick="document.getElementById('modalPengumuman').classList.remove('hidden')" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white text-sm font-semibold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-2 disabled:opacity-50">
                        <iconify-icon icon="lucide:plus"></iconify-icon>
                        Tambah Pengumuman
                    </button>
                </div>
            </div>
            <div class="mt-6 bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
                <div class="relative max-w-md">
                    <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-[#9FB5A5]"></iconify-icon>
                    <input id="searchInput" type="text" placeholder="Cari pengumuman berdasarkan nomor, judul, sumber, atau prioritas..." oninput="filterAdminTable(this)" data-filter-selector="#table-view tbody tr, #grid-view > div" class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all">
                </div>
            </div>

            <!-- Tabel View -->
            <div id="table-view" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Nomor</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Judul</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Sumber</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Prioritas</th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($pengumuman as $p): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($p['no']); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($p['judul']); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-600"><?php echo htmlspecialchars($p['sumber'] ?? '-'); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-600"><?php echo date('d/m/Y', strtotime($p['tgl'])); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php 
                                        $prioritas_colors = [
                                            'penting' => 'bg-red-100 text-red-700',
                                            'sangat penting' => 'bg-red-200 text-red-800',
                                            'segera' => 'bg-orange-100 text-orange-700',
                                            'normal' => 'bg-green-100 text-green-700'
                                        ];
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $prioritas_colors[strtolower($p['prioritas'] ?? 'normal')]; ?>">
                                        <?php echo htmlspecialchars(strtoupper($p['prioritas'] ?? 'Normal')); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <?php if ($p['pdf']): ?>
                                            <a href="<?php echo $p['pdf']; ?>" target="_blank" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-all">
                                                <iconify-icon icon="lucide:file-text"></iconify-icon>
                                            </a>
                                        <?php endif; ?>
                                        <button onclick="openEdit('<?php echo htmlspecialchars($p['id']); ?>', '<?php echo htmlspecialchars($p['no']); ?>', '<?php echo addslashes(htmlspecialchars($p['judul'])); ?>', '<?php echo addslashes(htmlspecialchars($p['konten'] ?? '')); ?>', '<?php echo htmlspecialchars($p['sumber'] ?? ''); ?>', '<?php echo date('Y-m-d', strtotime($p['tgl'])); ?>', '<?php echo htmlspecialchars($p['prioritas'] ?? 'Normal'); ?>', '<?php echo htmlspecialchars($p['pdf'] ?? ''); ?>', '<?php echo htmlspecialchars($p['status'] ?? 'published'); ?>')" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all">
                                            <iconify-icon icon="lucide:edit"></iconify-icon>
                                        </button>
                                        <a href="?hapus_pengumuman=<?php echo $p['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
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
            <div id="grid-view" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
                <?php foreach ($pengumuman as $p): ?>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                <?php echo htmlspecialchars(strtoupper($p['prioritas'] ?? 'Normal')); ?>
                            </span>
                            <span class="text-xs text-gray-500">
                                <?php echo date('d/m/Y', strtotime($p['tgl'])); ?>
                            </span>
                        </div>
                        <h4 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($p['judul']); ?></h4>
                        <p class="text-sm text-gray-600 mb-2"><span class="font-semibold">Nomor:</span> <?php echo htmlspecialchars($p['no']); ?></p>
                        <p class="text-sm text-gray-600 mb-4"><span class="font-semibold">Sumber:</span> <?php echo htmlspecialchars($p['sumber'] ?? '-'); ?></p>
                        <p class="text-sm text-gray-500 line-clamp-3 mb-4"><?php echo htmlspecialchars(strip_tags($p['konten'] ?? '')); ?></p>
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <div class="flex items-center gap-2">
                                <?php if ($p['pdf']): ?>
                                    <a href="<?php echo $p['pdf']; ?>" target="_blank" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-all">
                                        <iconify-icon icon="lucide:file-text"></iconify-icon>
                                    </a>
                                <?php endif; ?>
                                <button onclick="openEdit('<?php echo htmlspecialchars($p['id']); ?>', '<?php echo htmlspecialchars($p['no']); ?>', '<?php echo addslashes(htmlspecialchars($p['judul'])); ?>', '<?php echo addslashes(htmlspecialchars($p['konten'] ?? '')); ?>', '<?php echo htmlspecialchars($p['sumber'] ?? ''); ?>', '<?php echo date('Y-m-d', strtotime($p['tgl'])); ?>', '<?php echo htmlspecialchars($p['prioritas'] ?? 'Normal'); ?>', '<?php echo htmlspecialchars($p['pdf'] ?? ''); ?>', '<?php echo htmlspecialchars($p['status'] ?? 'published'); ?>')" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all">
                                    <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                                </button>
                                <a href="?hapus_pengumuman=<?php echo $p['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                    <iconify-icon icon="lucide:trash-2" class="w-5 h-5"></iconify-icon>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<!-- Modal Tambah Pengumuman -->
<div id="modalPengumuman" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('modalPengumuman').classList.add('hidden')"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden max-h-[90vh]">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-emerald-500 to-emerald-600">
                <h3 class="text-xl font-serif text-white">Tambah Pengumuman Baru</h3>
                <button onclick="document.getElementById('modalPengumuman').classList.add('hidden')" class="text-white/80 hover:text-white transition-colors">
                    <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
                </button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-5 overflow-y-auto" style="max-height:calc(90vh - 96px);">
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Nomor Pengumuman</label>
                        <input type="text" name="no" required placeholder="Contoh: PENG/001/2024" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Tanggal</label>
                        <input type="date" name="tgl" value="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Judul Pengumuman</label>
                    <input type="text" name="judul" required placeholder="Masukkan judul pengumuman..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Sumber Informasi</label>
                    <input type="text" name="sumber" placeholder="Masukkan sumber informasi..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Konten / Deskripsi</label>
                    <textarea name="konten" rows="4" placeholder="Tulis konten pengumuman di sini..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm resize-none" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>></textarea>
                </div>
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Prioritas</label>
                        <select name="prioritas" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                            <option value="Normal">Normal</option>
                            <option value="Penting">Penting</option>
                            <option value="Sangat Penting">Sangat Penting</option>
                            <option value="Segera">Segera</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">File PDF (Opsional)</label>
                    <p class="text-xs text-gray-500 mb-2">Maksimal upload server: <?php echo htmlspecialchars($uploadMaxSize); ?>.</p>
                    <input id="new-file-pdf" type="file" name="file_pdf" accept="application/pdf" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                    <div id="new-pdf-preview" class="mt-3" style="max-height:50vh; overflow:auto;"></div>
                </div>
                <div class="flex items-center gap-4 pt-4">
                    <button type="button" onclick="document.getElementById('modalPengumuman').classList.add('hidden')" class="flex-1 px-6 py-3 rounded-xl border-2 border-gray-200 text-gray-600 hover:bg-gray-50 transition-all font-semibold text-sm">Batal</button>
                    <button type="submit" name="tambah_pengumuman" class="flex-1 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white text-xs font-bold px-8 py-3 rounded-xl hover:from-emerald-600 hover:to-emerald-700 transition-all uppercase tracking-widest" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>Simpan Pengumuman</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pengumuman -->
<div id="modalEditPengumuman" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('modalEditPengumuman').classList.add('hidden')"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden max-h-[90vh]">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-emerald-500 to-emerald-600">
                <h3 class="text-xl font-serif text-white">Edit Pengumuman</h3>
                <button onclick="document.getElementById('modalEditPengumuman').classList.add('hidden')" class="text-white/80 hover:text-white transition-colors">
                    <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
                </button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-5 overflow-y-auto" style="max-height:calc(90vh - 96px);">
                <input type="hidden" name="id" id="edit-id">
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Nomor Pengumuman</label>
                        <input type="text" name="no" id="edit-no" required placeholder="Contoh: PENG/001/2024" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Tanggal</label>
                        <input type="date" name="tgl" id="edit-tgl" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Judul Pengumuman</label>
                    <input type="text" name="judul" id="edit-judul" required placeholder="Masukkan judul pengumuman..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Sumber Informasi</label>
                    <input type="text" name="sumber" id="edit-sumber" placeholder="Masukkan sumber informasi..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Konten / Deskripsi</label>
                    <textarea name="konten" id="edit-konten" rows="4" placeholder="Tulis konten pengumuman di sini..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm resize-none" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>></textarea>
                </div>
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Prioritas</label>
                        <select name="prioritas" id="edit-prioritas" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                            <option value="Normal">Normal</option>
                            <option value="Penting">Penting</option>
                            <option value="Sangat Penting">Sangat Penting</option>
                            <option value="Segera">Segera</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Status</label>
                        <select name="status" id="edit-status" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">File PDF (Opsional)</label>
                    <p class="text-xs text-gray-500 mb-2">Maksimal upload server: <?php echo htmlspecialchars($uploadMaxSize); ?>. Biarkan kosong jika tidak ingin mengganti PDF.</p>
                    <input type="hidden" name="existing_pdf" id="edit-existing-pdf">
                    <input id="edit-file-pdf" type="file" name="file_pdf" accept="application/pdf" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                    <div id="current-pdf" class="mt-2 text-sm text-gray-600"></div>
                    <div id="edit-pdf-preview" class="mt-3" style="max-height:50vh; overflow:auto;"></div>
                </div>
                <div class="flex items-center gap-4 pt-4">
                    <button type="button" onclick="document.getElementById('modalEditPengumuman').classList.add('hidden')" class="flex-1 px-6 py-3 rounded-xl border-2 border-gray-200 text-gray-600 hover:bg-gray-50 transition-all font-semibold text-sm">Batal</button>
                    <button type="submit" name="edit_pengumuman" class="flex-1 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white text-xs font-bold px-8 py-3 rounded-xl hover:from-emerald-600 hover:to-emerald-700 transition-all uppercase tracking-widest" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// View Toggle Functionality
function setView(view) {
    if (view === 'table') {
        document.getElementById('table-view').classList.remove('hidden');
        document.getElementById('grid-view').classList.add('hidden');
        document.getElementById('btn-table').classList.add('bg-gradient-to-r', 'from-emerald-500', 'to-emerald-600', 'text-white', 'shadow-md');
        document.getElementById('btn-table').classList.remove('text-gray-600', 'hover:bg-gray-50');
        document.getElementById('btn-grid').classList.remove('bg-gradient-to-r', 'from-emerald-500', 'to-emerald-600', 'text-white', 'shadow-md');
        document.getElementById('btn-grid').classList.add('text-gray-600', 'hover:bg-gray-50');
    } else {
        document.getElementById('grid-view').classList.remove('hidden');
        document.getElementById('table-view').classList.add('hidden');
        document.getElementById('btn-grid').classList.add('bg-gradient-to-r', 'from-emerald-500', 'to-emerald-600', 'text-white', 'shadow-md');
        document.getElementById('btn-grid').classList.remove('text-gray-600', 'hover:bg-gray-50');
        document.getElementById('btn-table').classList.remove('bg-gradient-to-r', 'from-emerald-500', 'to-emerald-600', 'text-white', 'shadow-md');
        document.getElementById('btn-table').classList.add('text-gray-600', 'hover:bg-gray-50');
    }
    localStorage.setItem('pengumumanView', view);
}

document.addEventListener('DOMContentLoaded', function() {
    const savedView = localStorage.getItem('pengumumanView') || 'table';
    setView(savedView);
});

function openEdit(id, no, judul, konten, sumber, tgl, prioritas, pdf, status) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-no').value = no;
    document.getElementById('edit-judul').value = judul;
    document.getElementById('edit-konten').value = konten;
    document.getElementById('edit-sumber').value = sumber;
    document.getElementById('edit-tgl').value = tgl;
    document.getElementById('edit-prioritas').value = prioritas;
    document.getElementById('edit-status').value = status;
    document.getElementById('edit-existing-pdf').value = pdf;
    
    if (pdf) {
        const resolvedPdf = pdf;
        document.getElementById('current-pdf').innerHTML = '<a href="' + resolvedPdf + '" target="_blank" class="text-emerald-600 hover:underline">Lihat file PDF saat ini</a>';
        // Render preview for existing PDF
        try { renderPdfPreview(resolvedPdf, 'edit-pdf-preview'); } catch (e) { console.error('Preview error', e); }
    } else {
        document.getElementById('current-pdf').innerHTML = '';
    }
    
    document.getElementById('modalEditPengumuman').classList.remove('hidden');
}
</script>

<!-- PDF.js for admin previews -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
// Configure worker
if (window.pdfjsLib) {
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
}

async function renderPdfPreview(src, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '<div class="p-4 text-xs text-slate-500">Memuat pratinjau...</div>';

    let loadingTask;
    try {
        // If src is a File or Blob URL, pdfjs can handle it. If it's a data URL or remote URL, pass directly.
        loadingTask = pdfjsLib.getDocument(src);
        const pdf = await loadingTask.promise;
        container.innerHTML = '';

        for (let i = 1; i <= pdf.numPages; i++) {
            const page = await pdf.getPage(i);
            const viewport = page.getViewport({ scale: 1.2 });
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = Math.floor(viewport.width);
            canvas.height = Math.floor(viewport.height);
            await page.render({ canvasContext: ctx, viewport }).promise;
            const img = document.createElement('img');
            img.src = canvas.toDataURL('image/png');
            img.className = 'w-full h-auto rounded-lg shadow-sm border border-slate-100 mb-3';
            container.appendChild(img);
            const meta = document.createElement('div');
            meta.className = 'text-[10px] text-slate-400 mb-4';
            meta.textContent = 'Halaman ' + i + ' dari ' + pdf.numPages;
            container.appendChild(meta);
        }
    } catch (err) {
        console.error('PDF preview failed', err);
        container.innerHTML = '<div class="p-3 text-xs text-red-600">Gagal memuat preview PDF.</div>';
    }
}

// Attach listeners for file inputs to preview selected PDF
document.addEventListener('DOMContentLoaded', function() {
    const newInput = document.getElementById('new-file-pdf');
    const editInput = document.getElementById('edit-file-pdf');

    if (newInput) {
        newInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            renderPdfPreview(url, 'new-pdf-preview');
        });
    }

    if (editInput) {
        editInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            renderPdfPreview(url, 'edit-pdf-preview');
        });
    }
});
</script>
