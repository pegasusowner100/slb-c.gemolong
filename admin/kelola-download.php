<?php
define('ADMIN_PAGE', true);
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/supabase_storage.php';
require_once '../includes/cloudinary-on.php';
require_login();

$title = "Kelola Download — SLB BC KARYA SEJAHTERA " . SITE_NAME;
$page_title = "Kelola Download";
$success = '';
$error = '';

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

function uploadPublicPdfFile($file, $folder = 'download') {
    if (!isset($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'url' => null,
            'error' => 'File tidak tersedia atau gagal diupload.'
        ];
    }

    if (function_exists('uploadToSupabaseStorage')) {
        $supabaseResult = uploadToSupabaseStorage($file, $folder);
        if ($supabaseResult['success']) {
            return [
                'success' => true,
                'url' => $supabaseResult['url'],
                'storage' => 'supabase'
            ];
        }
        $lastError = 'Supabase Storage: ' . ($supabaseResult['error'] ?? 'Upload gagal.');
    } else {
        $lastError = 'Supabase Storage tidak tersedia.';
    }

    return [
        'success' => false,
        'url' => null,
        'error' => $lastError
    ];
}

// Handle add download
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_download'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $kategori = $_POST['kategori'] ?? 'Umum';
        $status = $_POST['status'] ?? 'published';

        if (empty($judul)) {
            $error = 'Judul harus diisi!';
        } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $error = 'File harus dipilih!';
        } else {
            $uploadResult = uploadPublicPdfFile($_FILES['file'], 'download');
            if ($uploadResult['success']) {
                $data = [
                    'judul' => $judul,
                    'deskripsi' => $deskripsi,
                    'kategori' => $kategori,
                    'file_url' => $uploadResult['url'],
                    'status' => $status,
                    'urutan' => 999
                ];

                $result = supabaseInsert('download', $data);

                if ($result['success']) {
                    $success = 'Download berhasil ditambahkan!';
                } else {
                    $error = 'Gagal menambahkan download: ' . ($result['error'] ?? 'Unknown error');
                    error_log('Supabase Insert Error: ' . json_encode($result));
                }
            } else {
                $error = 'Gagal upload file: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }
    }
}

// Handle edit download
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_download'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $downloadId = $_POST['download_id'] ?? '';
        $judul = trim($_POST['edit_judul'] ?? '');
        $deskripsi = trim($_POST['edit_deskripsi'] ?? '');
        $kategori = $_POST['edit_kategori'] ?? 'Umum';
        $status = $_POST['edit_status'] ?? 'published';

        if (empty($judul)) {
            $error = 'Judul harus diisi!';
        } else {
            // Get current file URL
            $currentResult = supabaseSelect('download', ['id' => 'eq.' . $downloadId, 'limit' => 1]);
            $currentFileUrl = '';
            if ($currentResult['success'] && !empty($currentResult['data'])) {
                $currentFileUrl = $currentResult['data'][0]['file_url'] ?? '';
            }

            // Handle file upload if provided
            if (isset($_FILES['edit_file']) && $_FILES['edit_file']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadPublicPdfFile($_FILES['edit_file'], 'download');
                if ($uploadResult['success']) {
                    $currentFileUrl = $uploadResult['url'];
                } else {
                    $error = 'Gagal upload file: ' . ($uploadResult['error'] ?? 'Unknown error');
                }
            }

            if (empty($error)) {
                $data = [
                    'judul' => $judul,
                    'deskripsi' => $deskripsi,
                    'kategori' => $kategori,
                    'file_url' => $currentFileUrl,
                    'status' => $status
                ];

                $result = supabaseUpdate('download', $data, $downloadId);

                if ($result['success']) {
                    $success = 'Download berhasil diperbarui!';
                } else {
                    $error = 'Gagal memperbarui download!';
                }
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        $result = supabaseDelete('download', $_GET['delete']);
        if ($result['success']) {
            $success = 'Download berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus download!';
        }
    }
}

// Get all downloads
$all_downloads = [];
if ($supabaseConnected) {
    $result = supabaseSelect('download', ['order' => 'urutan.asc, created_at.desc']);
    if ($result['success']) {
        $all_downloads = $result['data'] ?? [];
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
            <h3 class="text-xl font-semibold text-[#1F2D26]">Daftar Download</h3>
            <button onclick="document.getElementById('modalDownload').classList.remove('hidden')" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-gradient-to-r from-[#3E6B4E] to-[#3E6B4E]/80 hover:from-[#2F5340] hover:to-[#2F5340]/80 text-white text-xs font-bold px-6 py-3 rounded-lg transition-all uppercase tracking-widest flex items-center gap-2 disabled:opacity-50 shadow-md hover:shadow-lg">
              <iconify-icon icon="lucide:plus"></iconify-icon>
              Tambah Download Baru
            </button>
          </div>
          <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
            <div class="relative max-w-md">
              <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-[#9FB5A5]"></iconify-icon>
              <input id="searchInput" type="text" placeholder="Cari berdasarkan judul, kategori, status, atau tanggal..." oninput="filterAdminTable(this)" data-filter-selector="tbody tr" class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-slate-100 focus:border-slate-300 transition-all">
            </div>
          </div>
        </div>

        <!-- Downloads Table -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-md overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-[#1E40AF]">
                <tr>
                  <th class="px-6 py-4 text-xs font-bold text-white uppercase tracking-wider">Judul</th>
                  <th class="px-6 py-4 text-xs font-bold text-white uppercase tracking-wider">Kategori</th>
                  <th class="px-6 py-4 text-xs font-bold text-white uppercase tracking-wider">Status</th>
                  <th class="px-6 py-4 text-xs font-bold text-white uppercase tracking-wider">Tanggal</th>
                  <th class="px-6 py-4 text-xs font-bold text-white uppercase tracking-wider">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <?php if (empty($all_downloads)): ?>
                  <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-600">Belum ada download</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($all_downloads as $download): ?>
                    <tr class="hover:bg-blue-50 transition-colors">
                      <td class="px-6 py-4">
                        <div class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($download['judul']); ?></div>
                        <div class="text-xs text-slate-500"><?php echo htmlspecialchars(substr($download['deskripsi'] ?? '', 0, 50)) . (strlen($download['deskripsi'] ?? '') > 50 ? '...' : ''); ?></div>
                      </td>
                      <td class="px-6 py-4">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold uppercase rounded-full"><?php echo htmlspecialchars($download['kategori'] ?? 'Umum'); ?></span>
                      </td>
                      <td class="px-6 py-4">
                        <span class="px-3 py-1 <?php echo ($download['status'] ?? 'published') === 'published' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'; ?> text-xs font-bold uppercase rounded-full"><?php echo htmlspecialchars($download['status'] ?? 'published'); ?></span>
                      </td>
                      <td class="px-6 py-4 text-sm text-slate-600">
                        <?php echo date('d M Y', strtotime($download['created_at'] ?? date('Y-m-d'))); ?>
                      </td>
                      <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                          <button onclick="editDownload(<?php echo htmlspecialchars(json_encode($download)); ?>)" class="p-2 text-[#5F6F65] hover:text-[#3E6B4E] transition-colors" title="Edit"><iconify-icon icon="lucide:edit-2"></iconify-icon></button>
                          <a href="?delete=<?php echo $download['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="p-2 text-[#5F6F65] hover:text-red-600 transition-colors" title="Hapus"><iconify-icon icon="lucide:trash-2"></iconify-icon></a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Modal Tambah Download -->
  <div id="modalDownload" class="fixed inset-0 z-50 hidden bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto border-2 border-slate-200/50">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-[#3E6B4E] to-[#3E6B4E]/80 px-6 md:px-8 py-6 flex items-center justify-between border-b-2 border-slate-200">
        <h2 class="font-serif text-2xl text-white flex items-center gap-3">
          <iconify-icon icon="lucide:download-cloud" class="text-2xl"></iconify-icon>
          Tambah Download
        </h2>
        <button onclick="document.getElementById('modalDownload').classList.add('hidden')" class="text-white hover:bg-white/20 p-2 rounded-lg transition-colors">
          <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 md:p-8 space-y-5">
        <form method="POST" action="" enctype="multipart/form-data" class="space-y-5">
          <!-- Judul -->
          <div>
            <label class="block text-xs font-bold text-[#1F2D26] mb-2 uppercase tracking-wide">Judul Download</label>
            <input type="text" name="judul" required placeholder="Masukkan judul file..." class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3E6B4E] focus:border-transparent transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
          </div>

          <!-- Deskripsi -->
          <div>
            <label class="block text-xs font-bold text-[#1F2D26] mb-2 uppercase tracking-wide">Deskripsi</label>
            <textarea name="deskripsi" rows="4" placeholder="Masukkan deskripsi file..." class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3E6B4E] focus:border-transparent transition-all text-sm resize-none" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>></textarea>
          </div>

          <!-- Kategori -->
          <div>
            <label class="block text-xs font-bold text-[#1F2D26] mb-2 uppercase tracking-wide">Kategori</label>
            <select name="kategori" class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3E6B4E] focus:border-transparent transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
              <option value="Umum">Umum</option>
              <option value="Formulir">Formulir</option>
              <option value="Kurikulum">Kurikulum</option>
              <option value="Pedoman">Pedoman</option>
              <option value="Laporan">Laporan</option>
              <option value="Pengumuman">Pengumuman</option>
            </select>
          </div>

          <!-- Status -->
          <div>
            <label class="block text-xs font-bold text-[#1F2D26] mb-2 uppercase tracking-wide">Status</label>
            <select name="status" class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3E6B4E] focus:border-transparent transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
              <option value="published">Publish</option>
              <option value="draft">Draft</option>
            </select>
          </div>

          <!-- File Upload -->
          <div>
            <label class="block text-xs font-bold text-[#1F2D26] mb-2 uppercase tracking-wide">Upload File (PDF, DOCX, XLS, PPT, ZIP)</label>
            <input type="file" name="file" required accept=".pdf,.docx,.xlsx,.pptx,.zip,.doc,.xls,.ppt" class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3E6B4E] focus:border-transparent transition-all text-sm" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
            <p class="text-xs text-slate-600 mt-2">File yang diterima: PDF, DOCX, XLSX, PPTX, ZIP (maksimal 50MB)</p>
          </div>

          <!-- Buttons -->
          <div class="flex gap-3 pt-4 border-t border-slate-200">
            <button type="button" onclick="document.getElementById('modalDownload').classList.add('hidden')" class="flex-1 px-6 py-3 bg-slate-100 hover:bg-slate-200 text-[#1F2D26] font-bold text-sm uppercase tracking-widest rounded-lg transition-colors">
              Batal
            </button>
            <button type="submit" name="tambah_download" class="flex-1 px-6 py-3 bg-gradient-to-r from-[#3E6B4E] to-[#3E6B4E]/80 hover:from-[#2F5340] hover:to-[#2F5340]/80 text-white font-bold text-sm uppercase tracking-widest rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2" <?php echo !$supabaseConnected ? 'disabled' : ''; ?>>
              <iconify-icon icon="lucide:upload" class="text-base"></iconify-icon>
              Tambah Download
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Edit Download -->
  <div id="modalEditDownload" class="fixed inset-0 z-50 hidden bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto border-2 border-slate-200/50">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-[#3E6B4E] to-[#3E6B4E]/80 px-6 md:px-8 py-6 flex items-center justify-between border-b-2 border-slate-200">
        <h2 class="font-serif text-2xl text-white flex items-center gap-3">
          <iconify-icon icon="lucide:download-cloud" class="text-2xl"></iconify-icon>
          Edit Download
        </h2>
        <button onclick="document.getElementById('modalEditDownload').classList.add('hidden')" class="text-white hover:bg-white/20 p-2 rounded-lg transition-colors">
          <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 md:p-8 space-y-5">
        <form method="POST" action="" enctype="multipart/form-data" class="space-y-5">
          <input type="hidden" name="download_id" id="edit_download_id" value="">

          <!-- Judul -->
          <div>
            <label class="block text-xs font-bold text-[#1F2D26] mb-2 uppercase tracking-wide">Judul Download</label>
            <input type="text" name="edit_judul" id="edit_judul" required placeholder="Masukkan judul file..." class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3E6B4E] focus:border-transparent transition-all text-sm">
          </div>

          <!-- Deskripsi -->
          <div>
            <label class="block text-xs font-bold text-[#1F2D26] mb-2 uppercase tracking-wide">Deskripsi</label>
            <textarea name="edit_deskripsi" id="edit_deskripsi" rows="4" placeholder="Masukkan deskripsi file..." class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3E6B4E] focus:border-transparent transition-all text-sm resize-none"></textarea>
          </div>

          <!-- Kategori -->
          <div>
            <label class="block text-xs font-bold text-[#1F2D26] mb-2 uppercase tracking-wide">Kategori</label>
            <select name="edit_kategori" id="edit_kategori" class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3E6B4E] focus:border-transparent transition-all text-sm">
              <option value="Umum">Umum</option>
              <option value="Formulir">Formulir</option>
              <option value="Kurikulum">Kurikulum</option>
              <option value="Pedoman">Pedoman</option>
              <option value="Laporan">Laporan</option>
              <option value="Pengumuman">Pengumuman</option>
            </select>
          </div>

          <!-- Status -->
          <div>
            <label class="block text-xs font-bold text-[#1F2D26] mb-2 uppercase tracking-wide">Status</label>
            <select name="edit_status" id="edit_status" class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3E6B4E] focus:border-transparent transition-all text-sm">
              <option value="published">Publish</option>
              <option value="draft">Draft</option>
            </select>
          </div>

          <!-- File Upload (Optional) -->
          <div>
            <label class="block text-xs font-bold text-[#1F2D26] mb-2 uppercase tracking-wide">Upload File Baru (Opsional)</label>
            <input type="file" name="edit_file" accept=".pdf,.docx,.xlsx,.pptx,.zip,.doc,.xls,.ppt" class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3E6B4E] focus:border-transparent transition-all text-sm">
            <p class="text-xs text-slate-600 mt-2">Biarkan kosong jika tidak ingin mengganti file</p>
            <div id="edit_download_current_file" class="text-sm text-slate-600 mt-2"></div>
          </div>

          <!-- Buttons -->
          <div class="flex gap-3 pt-4 border-t border-slate-200">
            <button type="button" onclick="document.getElementById('modalEditDownload').classList.add('hidden')" class="flex-1 px-6 py-3 bg-slate-100 hover:bg-slate-200 text-[#1F2D26] font-bold text-sm uppercase tracking-widest rounded-lg transition-colors">
              Batal
            </button>
            <button type="submit" name="edit_download" class="flex-1 px-6 py-3 bg-gradient-to-r from-[#3E6B4E] to-[#3E6B4E]/80 hover:from-[#2F5340] hover:to-[#2F5340]/80 text-white font-bold text-sm uppercase tracking-widest rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
              <iconify-icon icon="lucide:save" class="text-base"></iconify-icon>
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Function -->
  <script>
    function editDownload(download) {
      document.getElementById('edit_download_id').value = download.id;
      document.getElementById('edit_judul').value = download.judul;
      document.getElementById('edit_deskripsi').value = download.deskripsi || '';
      document.getElementById('edit_kategori').value = download.kategori || 'Umum';
      document.getElementById('edit_status').value = download.status || 'published';
      const currentFile = document.getElementById('edit_download_current_file');
      if (download.file_url) {
        const resolvedFileUrl = download.file_url;
        let fileName = decodeURIComponent(resolvedFileUrl.split('/').pop());
        fileName = fileName.replace(/^[a-f0-9]+(\.[a-f0-9]+)?_/, '');
        currentFile.innerHTML = 'File saat ini: <a href="' + resolvedFileUrl + '" target="_blank" class="text-[#3E6B4E] font-semibold hover:underline">' + fileName + '</a>';
      } else {
        currentFile.innerHTML = 'Belum ada file yang diunggah.';
      }
      document.getElementById('modalEditDownload').classList.remove('hidden');
    }

    // Close modals on outside click
    document.getElementById('modalDownload').addEventListener('click', function(e) {
      if (e.target === this) this.classList.add('hidden');
    });
    document.getElementById('modalEditDownload').addEventListener('click', function(e) {
      if (e.target === this) this.classList.add('hidden');
    });

    // Close on ESC
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        document.getElementById('modalDownload').classList.add('hidden');
        document.getElementById('modalEditDownload').classList.add('hidden');
      }
    });
  </script>

</body>
</html>
