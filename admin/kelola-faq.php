<?php
define('ADMIN_PAGE', true);
require_once '../includes/session.php';
require_once '../includes/db.php';
require_login();

$title = "Kelola FAQ — SLB BC KARYA SEJAHTERA " . SITE_NAME;
$page_title = "Kelola FAQ";
$success = '';
$error = '';

// Handle tambah FAQ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_faq'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $pertanyaan = trim($_POST['pertanyaan']);
        $jawaban = trim($_POST['jawaban']);
        $urutan = (int)($_POST['urutan'] ?? 0);
        $status = $_POST['status'];

        $data = [
            'pertanyaan' => $pertanyaan,
            'jawaban' => $jawaban,
            'urutan' => $urutan,
            'status' => $status
        ];

        $result = supabaseInsert('faq', $data);

        if ($result['success']) {
            $success = 'FAQ berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan FAQ! ' . ($result['error'] ?? '');
        }
    }
}

// Handle edit FAQ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_faq'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $faqId = $_POST['faq_id'];
        $pertanyaan = trim($_POST['edit_pertanyaan']);
        $jawaban = trim($_POST['edit_jawaban']);
        $urutan = (int)($_POST['edit_urutan'] ?? 0);
        $status = $_POST['edit_status'];

        $data = [
            'pertanyaan' => $pertanyaan,
            'jawaban' => $jawaban,
            'urutan' => $urutan,
            'status' => $status,
            'updated_at' => date('c')
        ];

        $result = supabaseUpdate('faq', $data, $faqId);

        if ($result['success']) {
            $success = 'FAQ berhasil diperbarui!';
        } else {
            $error = 'Gagal memperbarui FAQ! ' . ($result['error'] ?? '');
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        $result = supabaseDelete('faq', $_GET['delete']);
        if ($result['success']) {
            header('Location: kelola-faq.php?success=deleted');
            exit;
        } else {
            $error = 'Gagal menghapus FAQ! ' . ($result['error'] ?? '');
        }
    }
}

if (isset($_GET['success']) && $_GET['success'] === 'deleted') {
    $success = 'FAQ berhasil dihapus!';
}

// Handle search query
$search = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
}

// Get all FAQ from database
$all_faq = [];
if ($supabaseConnected) {
    $query = ['order' => 'urutan.asc, created_at.asc'];
    if ($search) {
        $query['or'] = "(pertanyaan.ilike.%$search%,jawaban.ilike.%$search%)";
    }
    $faqResult = supabaseSelect('faq', $query);
    if ($faqResult['success']) {
        $all_faq = $faqResult['data'];
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
        <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg flex items-center gap-3">
          <iconify-icon icon="lucide:check-circle"></iconify-icon>
          <?php echo $success; ?>
        </div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg flex items-center gap-3">
          <iconify-icon icon="lucide:alert-circle"></iconify-icon>
          <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <div class="flex flex-col gap-6 mb-8">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
          <h3 class="text-xl font-semibold text-[#1F2D26]">Daftar FAQ</h3>
          <button onclick="document.getElementById('modalFAQ').classList.remove('hidden')" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-gradient-to-r from-emerald-600 to-emerald-700 text-white text-sm font-semibold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 uppercase tracking-wide flex items-center gap-2 disabled:opacity-50">
            <iconify-icon icon="lucide:plus"></iconify-icon>
            Tambah FAQ Baru
          </button>
        </div>

        <!-- Search Form & View Toggle -->
        <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
          <div class="w-full md:w-96">
            <input id="searchInput" type="text" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari pertanyaan atau jawaban..." oninput="filterAdminTable(this)" data-filter-selector="#table-view tbody tr, #grid-view > div" class="w-full flex-1 px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all">
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

        <!-- Search Info -->
        <?php if ($search): ?>
          <div class="flex items-center gap-2 text-sm text-emerald-600 bg-emerald-50 px-4 py-2 rounded-xl border border-emerald-100">
            <iconify-icon icon="lucide:search"></iconify-icon>
            Hasil pencarian untuk "<strong><?php echo htmlspecialchars($search); ?></strong>" ditemukan <strong><?php echo count($all_faq); ?></strong> FAQ
          </div>
        <?php endif; ?>
      </div>

      <?php if (empty($all_faq)): ?>
        <div class="col-span-full text-center py-16 bg-white rounded-2xl border border-gray-100 shadow-sm">
          <iconify-icon icon="lucide:help-circle" class="text-6xl text-gray-300 mx-auto mb-4"></iconify-icon>
          <p class="text-gray-500 mb-4">Belum ada FAQ. Klik tombol "Tambah FAQ Baru" untuk memulai.</p>
        </div>
      <?php else: ?>
        <!-- Grid View -->
        <div id="grid-view" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach ($all_faq as $faq): ?>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
              <div class="p-6">
                <div class="flex items-center gap-2 mb-3">
                  <span class="px-3 py-1 <?php echo $faq['status'] === 'published' ? 'bg-gradient-to-r from-emerald-500 to-emerald-600' : 'bg-gradient-to-r from-amber-500 to-amber-600'; ?> text-white text-xs font-bold rounded-full uppercase tracking-wider">
                    <?php echo htmlspecialchars($faq['status'] ?? 'published'); ?>
                  </span>
                  <span class="text-xs text-gray-400">Urutan: <?php echo htmlspecialchars($faq['urutan'] ?? 0); ?></span>
                </div>
                <h3 class="font-serif text-lg font-semibold text-gray-800 mb-3 line-clamp-2"><?php echo htmlspecialchars($faq['pertanyaan']); ?></h3>
                <p class="text-sm text-gray-500 line-clamp-4 mb-4"><?php echo htmlspecialchars(strip_tags($faq['jawaban'] ?? '')); ?></p>
                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                  <div class="flex items-center gap-2">
                    <button class="btn-edit-faq p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all"
                            data-id="<?php echo htmlspecialchars($faq['id']); ?>"
                            data-pertanyaan="<?php echo htmlspecialchars($faq['pertanyaan']); ?>"
                            data-jawaban="<?php echo htmlspecialchars($faq['jawaban'] ?? ''); ?>"
                            data-urutan="<?php echo htmlspecialchars($faq['urutan'] ?? 0); ?>"
                            data-status="<?php echo htmlspecialchars($faq['status'] ?? 'published'); ?>">
                      <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                    </button>
                    <a href="?delete=<?php echo $faq['id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" onclick="return confirm('Yakin ingin menghapus FAQ ini?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
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
                <thead class="bg-gradient-to-r from-emerald-600 to-emerald-700 text-white">
                  <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Pertanyaan</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Jawaban</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Urutan</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <?php foreach ($all_faq as $faq): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                      <td class="px-6 py-4">
                        <div class="font-medium text-gray-800 line-clamp-2"><?php echo htmlspecialchars($faq['pertanyaan']); ?></div>
                      </td>
                      <td class="px-6 py-4">
                        <div class="text-gray-500 text-sm line-clamp-3"><?php echo htmlspecialchars(strip_tags($faq['jawaban'] ?? '')); ?></div>
                      </td>
                      <td class="px-6 py-4">
                        <span class="text-gray-600 font-medium"><?php echo htmlspecialchars($faq['urutan'] ?? 0); ?></span>
                      </td>
                      <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $faq['status'] === 'published' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'; ?>">
                          <?php echo htmlspecialchars(strtoupper($faq['status'] ?? 'published')); ?>
                        </span>
                      </td>
                      <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                          <button class="btn-edit-faq p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all"
                                  data-id="<?php echo htmlspecialchars($faq['id']); ?>"
                                  data-pertanyaan="<?php echo htmlspecialchars($faq['pertanyaan']); ?>"
                                  data-jawaban="<?php echo htmlspecialchars($faq['jawaban'] ?? ''); ?>"
                                  data-urutan="<?php echo htmlspecialchars($faq['urutan'] ?? 0); ?>"
                                  data-status="<?php echo htmlspecialchars($faq['status'] ?? 'published'); ?>">
                            <iconify-icon icon="lucide:edit"></iconify-icon>
                          </button>
                          <a href="?delete=<?php echo $faq['id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" onclick="return confirm('Yakin ingin menghapus FAQ ini?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
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

  <!-- Modal Tambah FAQ -->
  <div id="modalFAQ" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
      <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-emerald-600 to-emerald-700">
        <h3 class="text-lg font-semibold text-white">Tambah FAQ Baru</h3>
        <button onclick="document.getElementById('modalFAQ').classList.add('hidden')" class="p-2 text-white/80 hover:bg-white/20 rounded-lg transition-all">
          <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
        </button>
      </div>
      <form method="POST" class="p-6 space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Pertanyaan</label>
          <input type="text" name="pertanyaan" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all" placeholder="Masukkan pertanyaan...">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Jawaban</label>
          <textarea name="jawaban" rows="4" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all" placeholder="Masukkan jawaban..."></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Urutan</label>
            <input type="number" name="urutan" value="0" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="status" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all">
              <option value="published">Published</option>
              <option value="draft">Draft</option>
            </select>
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
          <button type="button" onclick="document.getElementById('modalFAQ').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
            Batal
          </button>
          <button type="submit" name="tambah_faq" class="px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-xl hover:from-emerald-700 hover:to-emerald-800 transition-all shadow-md">
            Simpan FAQ
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Edit FAQ -->
  <div id="modalEditFAQ" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
      <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-emerald-600 to-emerald-700">
        <h3 class="text-lg font-semibold text-white">Edit FAQ</h3>
        <button onclick="document.getElementById('modalEditFAQ').classList.add('hidden')" class="p-2 text-white/80 hover:bg-white/20 rounded-lg transition-all">
          <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
        </button>
      </div>
      <form id="formEditFAQ" method="POST" class="p-6 space-y-5">
        <input type="hidden" name="faq_id" id="edit_faq_id">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Pertanyaan</label>
          <input type="text" name="edit_pertanyaan" id="edit_pertanyaan" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Jawaban</label>
          <textarea name="edit_jawaban" id="edit_jawaban" rows="4" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Urutan</label>
            <input type="number" name="edit_urutan" id="edit_urutan" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="edit_status" id="edit_status" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all">
              <option value="published">Published</option>
              <option value="draft">Draft</option>
            </select>
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
          <button type="button" onclick="document.getElementById('modalEditFAQ').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
            Batal
          </button>
          <button type="submit" name="edit_faq" class="px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-xl hover:from-emerald-700 hover:to-emerald-800 transition-all shadow-md">
            Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Initialize edit button listeners
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.btn-edit-faq').forEach(button => {
        button.addEventListener('click', function() {
          const id = this.getAttribute('data-id');
          const pertanyaan = this.getAttribute('data-pertanyaan');
          const jawaban = this.getAttribute('data-jawaban');
          const urutan = this.getAttribute('data-urutan');
          const status = this.getAttribute('data-status');
          
          document.getElementById('edit_faq_id').value = id;
          document.getElementById('edit_pertanyaan').value = pertanyaan;
          document.getElementById('edit_jawaban').value = jawaban;
          document.getElementById('edit_urutan').value = urutan;
          document.getElementById('edit_status').value = status;
          document.getElementById('modalEditFAQ').classList.remove('hidden');
        });
      });
      
      const savedView = localStorage.getItem('faqView') || 'table';
      setView(savedView);
    });

    function setView(view) {
      const gridView = document.getElementById('grid-view');
      const tableView = document.getElementById('table-view');
      const btnGrid = document.getElementById('btn-grid');
      const btnTable = document.getElementById('btn-table');

      if (view === 'grid') {
        gridView.classList.remove('hidden');
        tableView.classList.add('hidden');
        btnGrid.classList.add('bg-gradient-to-r', 'from-emerald-600', 'to-emerald-700', 'text-white', 'shadow-md');
        btnTable.classList.remove('bg-gradient-to-r', 'from-emerald-600', 'to-emerald-700', 'text-white', 'shadow-md');
      } else {
        gridView.classList.add('hidden');
        tableView.classList.remove('hidden');
        btnTable.classList.add('bg-gradient-to-r', 'from-emerald-600', 'to-emerald-700', 'text-white', 'shadow-md');
        btnGrid.classList.remove('bg-gradient-to-r', 'from-emerald-600', 'to-emerald-700', 'text-white', 'shadow-md');
      }
      localStorage.setItem('faqView', view);
    }
  </script>
</body>
</html>
