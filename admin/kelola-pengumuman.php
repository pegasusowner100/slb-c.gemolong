<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/supabase_storage.php';
require_login();

$title = "Kelola Pengumuman";
$page_title = "Kelola Pengumuman";
$success = '';
$error = '';

// --- Handle Tambah Pengumuman ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_pengumuman'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $no = trim($_POST['no']);
        $judul = trim($_POST['judul']);
        $konten = trim($_POST['konten']);
        $sumber = trim($_POST['sumber']);
        $tgl = $_POST['tgl'];
        $prioritas = $_POST['prioritas'];
        $status = $_POST['status'];

        $file_pdf = '';
        if (isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToSupabaseStorage($_FILES['file_pdf'], CLOUDINARY_FOLDER);
            if ($uploadResult['success']) {
                $file_pdf = $uploadResult['url'];
            }
        }

        $data = [
            'no' => $no,
            'judul' => $judul,
            'konten' => $konten,
            'sumber' => $sumber,
            'tgl' => $tgl,
            'prioritas' => $prioritas,
            'pdf' => $file_pdf,
            'file_name' => $_FILES['file_pdf']['name'] ?? '',
            'status' => $status
        ];

        $response = supabaseInsert('pengumuman', $data);
        if ($response['success']) {
            $success = 'Pengumuman berhasil ditambahkan!';
        } else {
            $error = 'Gagal menyimpan pengumuman!';
        }
    }
}

// --- Handle Edit Pengumuman ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_pengumuman'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $id = trim($_POST['id']);
        $no = trim($_POST['no']);
        $judul = trim($_POST['judul']);
        $konten = trim($_POST['konten']);
        $sumber = trim($_POST['sumber']);
        $tgl = $_POST['tgl'];
        $prioritas = $_POST['prioritas'];
        $status = $_POST['status'];

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
            'no' => $no,
            'judul' => $judul,
            'konten' => $konten,
            'sumber' => $sumber,
            'tgl' => $tgl,
            'prioritas' => $prioritas,
            'pdf' => $file_pdf,
            'file_name' => $_FILES['file_pdf']['name'] ?? '',
            'status' => $status
        ];

        $response = supabaseUpdate('pengumuman', $data, $id);
        if ($response['success']) $success = 'Pengumuman berhasil diperbarui!';
        else $error = 'Gagal memperbarui pengumuman!';
    }
}

// --- Handle Hapus Pengumuman ---
if (isset($_GET['hapus_pengumuman']) && !empty($_GET['hapus_pengumuman'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        $response = supabaseDelete('pengumuman', $_GET['hapus_pengumuman']);
        if ($response['success']) $success = 'Pengumuman berhasil dihapus!';
        else $error = 'Gagal menghapus pengumuman!';
    }
}

// --- Ambil Data Pengumuman ---
$pengumuman = [];
if ($supabaseConnected) {
    $pengumumanResult = supabaseSelect('pengumuman', ['order' => 'created_at.desc']);
    if ($pengumumanResult['success']) {
        $pengumuman = $pengumumanResult['data'];
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
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-emerald-500 to-emerald-600">
                <h3 class="text-xl font-serif text-white">Tambah Pengumuman Baru</h3>
                <button onclick="document.getElementById('modalPengumuman').classList.add('hidden')" class="text-white/80 hover:text-white transition-colors">
                    <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
                </button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
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
                    <select name="sumber" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                        <?php foreach ($sumber_info as $s): ?>
                            <option value="<?php echo htmlspecialchars($s['nama']); ?>"><?php echo htmlspecialchars($s['nama']); ?></option>
                        <?php endforeach; ?>
                    </select>
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
                    <input type="file" name="file_pdf" accept="application/pdf" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
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
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-emerald-500 to-emerald-600">
                <h3 class="text-xl font-serif text-white">Edit Pengumuman</h3>
                <button onclick="document.getElementById('modalEditPengumuman').classList.add('hidden')" class="text-white/80 hover:text-white transition-colors">
                    <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
                </button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
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
                    <select name="sumber" id="edit-sumber" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                        <?php foreach ($sumber_info as $s): ?>
                            <option value="<?php echo htmlspecialchars($s['nama']); ?>"><?php echo htmlspecialchars($s['nama']); ?></option>
                        <?php endforeach; ?>
                    </select>
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
                    <input type="hidden" name="existing_pdf" id="edit-existing-pdf">
                    <input type="file" name="file_pdf" accept="application/pdf" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
                    <div id="current-pdf" class="mt-2 text-sm text-gray-600">
                    </div>
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
        document.getElementById('current-pdf').innerHTML = '<a href="' + pdf + '" target="_blank" class="text-emerald-600 hover:underline">Lihat file PDF saat ini</a>';
    } else {
        document.getElementById('current-pdf').innerHTML = '';
    }
    
    document.getElementById('modalEditPengumuman').classList.remove('hidden');
}
</script>
</body>
</html>
