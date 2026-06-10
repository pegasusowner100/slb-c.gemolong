
<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_login();

$title = "Kelola PPDB — SLB-C YPSLB Gemolong";
$page_title = "Kelola PPDB";
$success = '';
$error = '';

// detect AJAX requests
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Handle Delete PPDB
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    if (!$supabaseConnected) {
    $error = 'Gagal menghapus: Supabase tidak terhubung!';
    if ($isAjax) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => $error]);
      exit;
    }
    } else {
        $result = supabaseDelete('ppdb', $_GET['delete']);
        if ($result['success']) {
            $success = 'Data PPDB berhasil dihapus!';
      if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $success]);
        exit;
      }
        } else {
            $error = 'Gagal menghapus data PPDB!';
      if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error]);
        exit;
      }
        }
    }
}

// Handle Update Status
if (isset($_POST['update_status']) && isset($_POST['id']) && isset($_POST['status'])) {
    if (!$supabaseConnected) {
    $error = 'Gagal memperbarui: Supabase tidak terhubung!';
    if ($isAjax) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => $error]);
      exit;
    }
    } else {
        $result = supabaseUpdate('ppdb', ['status' => $_POST['status']], $_POST['id']);
        if ($result['success']) {
            $success = 'Status berhasil diperbarui!';
      if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $success]);
        exit;
      }
        } else {
            $error = 'Gagal memperbarui status!';
      if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error]);
        exit;
      }
        }
    }
}

// Get All PPDB
$ppdb_list = [];
if ($supabaseConnected) {
    $ppdbResult = supabaseSelect('ppdb', ['order' => 'created_at.desc']);
    if ($ppdbResult['success']) {
        $ppdb_list = $ppdbResult['data'];
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

        <div class="mb-8">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-[#1F2D26]">Daftar Pendaftar PPDB</h2>
            <span class="text-sm text-[#9FB5A5]">Total: <span id="totalCount"><?php echo count($ppdb_list); ?></span> pendaftar</span>
          </div>

          <!-- Search & Filter -->
          <div class="bg-white rounded-lg border border-[#E8E4D9] p-4 mb-6">
            <div class="flex flex-col md:flex-row gap-4">
              <div class="flex-1">
                <label class="text-xs font-semibold text-[#9FB5A5] uppercase tracking-widest mb-2 block">Cari Pendaftar</label>
                <input type="text" id="searchInput" placeholder="Cari berdasarkan nama, NISN, atau nomor HP..."
                       class="w-full px-4 py-2 border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] focus:ring-2 focus:ring-[#3E6B4E]/20">
              </div>
              <div class="md:w-40">
                <label class="text-xs font-semibold text-[#9FB5A5] uppercase tracking-widest mb-2 block">Filter Status</label>
                <select id="statusFilter"
                        class="w-full px-4 py-2 border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E]">
                  <option value="">Semua Status</option>
                  <option value="pending">Pending</option>
                  <option value="diterima">Diterima</option>
                  <option value="ditolak">Ditolak</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- PPDB Table -->
        <div class="bg-white rounded-lg border border-[#E8E4D9] shadow-sm overflow-hidden">
          <?php if (empty($ppdb_list)): ?>
            <div class="p-12 text-center">
              <iconify-icon icon="lucide:clipboard-list" class="text-4xl text-[#9FB5A5] mb-4"></iconify-icon>
              <p class="text-[#5F6F65]">Belum ada pendaftar.</p>
            </div>
          <?php else: ?>
            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="bg-[#F9F8F4] border-b border-[#E8E4D9]">
                    <th class="px-3 py-3 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest cursor-pointer hover:bg-[#F0EDE0]" onclick="sortTable('no_reg')">
                      <div class="flex items-center gap-1">No.Reg <iconify-icon icon="lucide:arrow-up-down" class="w-3 h-3"></iconify-icon></div>
                    </th>
                    <th class="px-3 py-3 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest cursor-pointer hover:bg-[#F0EDE0]" onclick="sortTable('nama_lengkap')">
                      <div class="flex items-center gap-1">Nama <iconify-icon icon="lucide:arrow-up-down" class="w-3 h-3"></iconify-icon></div>
                    </th>
                    <th class="px-3 py-3 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest cursor-pointer hover:bg-[#F0EDE0]" onclick="sortTable('nisn')">
                      <div class="flex items-center gap-1">NISN <iconify-icon icon="lucide:arrow-up-down" class="w-3 h-3"></iconify-icon></div>
                    </th>
                    <th class="px-3 py-3 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">Tempat Lahir</th>
                    <th class="px-3 py-3 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest cursor-pointer hover:bg-[#F0EDE0]" onclick="sortTable('tanggal_lahir')">
                      <div class="flex items-center gap-1">Tgl Lahir <iconify-icon icon="lucide:arrow-up-down" class="w-3 h-3"></iconify-icon></div>
                    </th>
                    <th class="px-3 py-3 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">Jenis Kelamin</th>
                    <th class="px-3 py-3 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">Agama</th>
                    <th class="px-3 py-3 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">Sekolah Asal</th>
                    <th class="px-3 py-3 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">Tahun Lulus</th>
                    <th class="px-3 py-3 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">Nama Ayah</th>
                    <th class="px-3 py-3 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">Nama Ibu</th>
                    <th class="px-3 py-3 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest cursor-pointer hover:bg-[#F0EDE0]" onclick="sortTable('no_hp_ortu')">
                      <div class="flex items-center gap-1">No HP Ortu <iconify-icon icon="lucide:arrow-up-down" class="w-3 h-3"></iconify-icon></div>
                    </th>
                    <th class="px-3 py-3 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">Status</th>
                    <th class="px-3 py-3 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest cursor-pointer hover:bg-[#F0EDE0]" onclick="sortTable('created_at')">
                      <div class="flex items-center gap-1">Tgl Daftar <iconify-icon icon="lucide:arrow-up-down" class="w-3 h-3"></iconify-icon></div>
                    </th>
                    <th class="px-3 py-3 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E4D9]" id="tableBody">
                  <?php foreach ($ppdb_list as $pendaftar): ?>
                    <tr class="hover:bg-[#F9F8F4] ppdb-row" data-search="<?php echo strtolower(htmlspecialchars($pendaftar['nama_lengkap'] ?? '') . ' ' . htmlspecialchars($pendaftar['nisn'] ?? '') . ' ' . htmlspecialchars($pendaftar['no_hp_ortu'] ?? '')); ?>" data-status="<?php echo htmlspecialchars($pendaftar['status'] ?? 'pending'); ?>">
                      <td class="px-3 py-3 text-xs font-bold text-[#3E6B4E]"><?php echo htmlspecialchars($pendaftar['no_reg'] ?? '-'); ?></td>
                      <td class="px-3 py-3 text-sm font-medium text-[#1F2D26]"><?php echo htmlspecialchars($pendaftar['nama_lengkap'] ?? '-'); ?></td>
                      <td class="px-3 py-3 text-xs text-[#5F6F65]"><?php echo htmlspecialchars($pendaftar['nisn'] ?? '-'); ?></td>
                      <td class="px-3 py-3 text-xs text-[#5F6F65]"><?php echo htmlspecialchars($pendaftar['tempat_lahir'] ?? '-'); ?></td>
                      <td class="px-3 py-3 text-xs text-[#5F6F65]"><?php echo htmlspecialchars($pendaftar['tanggal_lahir'] ?? '-'); ?></td>
                      <td class="px-3 py-3 text-xs text-[#5F6F65]"><?php echo htmlspecialchars($pendaftar['jenis_kelamin'] ?? '-'); ?></td>
                      <td class="px-3 py-3 text-xs text-[#5F6F65]"><?php echo htmlspecialchars($pendaftar['agama'] ?? '-'); ?></td>
                      <td class="px-3 py-3 text-xs text-[#5F6F65]"><?php echo htmlspecialchars($pendaftar['sekolah_asal'] ?? '-'); ?></td>
                      <td class="px-3 py-3 text-xs text-[#5F6F65]"><?php echo htmlspecialchars($pendaftar['tahun_lulusan'] ?? '-'); ?></td>
                      <td class="px-3 py-3 text-xs text-[#5F6F65]"><?php echo htmlspecialchars($pendaftar['nama_ayah'] ?? '-'); ?></td>
                      <td class="px-3 py-3 text-xs text-[#5F6F65]"><?php echo htmlspecialchars($pendaftar['nama_ibu'] ?? '-'); ?></td>
                      <td class="px-3 py-3 text-xs text-[#5F6F65]"><?php echo htmlspecialchars($pendaftar['no_hp_ortu'] ?? '-'); ?></td>
                      <td class="px-3 py-3">
                        <form method="POST" class="inline-block">
                          <input type="hidden" name="id" value="<?php echo $pendaftar['id']; ?>">
                          <select name="status" onchange="this.form.submit()" class="text-xs bg-[#F9F8F4] border border-[#E8E4D9] rounded px-2 py-1 focus:outline-none focus:border-[#3E6B4E]">
                            <option value="pending" <?php echo ($pendaftar['status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="diterima" <?php echo ($pendaftar['status'] ?? '') === 'diterima' ? 'selected' : ''; ?>>Diterima</option>
                            <option value="ditolak" <?php echo ($pendaftar['status'] ?? '') === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                          </select>
                          <button type="submit" name="update_status" style="display:none"></button>
                        </form>
                      </td>
                      <td class="px-3 py-3 text-xs text-[#9FB5A5]">
                        <?php
                        if (!empty($pendaftar['created_at'])) {
                          $date = new DateTime($pendaftar['created_at']);
                          echo $date->format('d/m/Y H:i');
                        } else {
                          echo '-';
                        }
                        ?>
                      </td>
                      <td class="px-3 py-3">
                        <div class="flex gap-2">
                          <a href="edit-ppdb.php?id=<?php echo $pendaftar['id']; ?>" class="text-blue-500 hover:text-blue-700 transition-colors text-sm" title="Edit">
                            <iconify-icon icon="lucide:edit-2"></iconify-icon>
                          </a>
                          <a href="?delete=<?php echo $pendaftar['id']; ?>" onclick="return confirm('Yakin ingin menghapus pendaftar ini?')" class="text-red-500 hover:text-red-700 transition-colors text-sm" title="Hapus">
                            <iconify-icon icon="lucide:trash-2"></iconify-icon>
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <!-- Empty Search Result -->
            <div id="emptyState" class="hidden p-12 text-center">
              <iconify-icon icon="lucide:search" class="text-4xl text-[#9FB5A5] mb-4"></iconify-icon>
              <p class="text-[#5F6F65]">Tidak ada pendaftar yang sesuai dengan pencarian.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <script>
    let sortDirection = {};

    // Search functionality
    function filterTable() {
      const searchInput = document.getElementById('searchInput').value.toLowerCase();
      const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
      const rows = document.querySelectorAll('.ppdb-row');
      let visibleCount = 0;

      rows.forEach(row => {
        const searchText = row.dataset.search;
        const status = row.dataset.status;

        const matchesSearch = searchText.includes(searchInput);
        const matchesStatus = !statusFilter || status === statusFilter;

        if (matchesSearch && matchesStatus) {
          row.style.display = '';
          visibleCount++;
        } else {
          row.style.display = 'none';
        }
      });

      // Show empty state
      const emptyState = document.getElementById('emptyState');
      const tableBody = document.getElementById('tableBody');
      if (visibleCount === 0 && tableBody) {
        tableBody.style.display = 'none';
        emptyState.classList.remove('hidden');
      } else if (emptyState && tableBody) {
        tableBody.style.display = '';
        emptyState.classList.add('hidden');
      }

      document.getElementById('totalCount').textContent = visibleCount;
    }

    // Sort functionality
    function sortTable(column) {
      const rows = Array.from(document.querySelectorAll('.ppdb-row'));
      const columnIndex = getColumnIndex(column);

      // Toggle sort direction
      sortDirection[column] = !sortDirection[column];

      rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();

        // Try to convert to numbers
        const aNum = parseFloat(aValue.replace(/[^0-9.-]/g, ''));
        const bNum = parseFloat(bValue.replace(/[^0-9.-]/g, ''));

        let comparison = 0;
        if (!isNaN(aNum) && !isNaN(bNum)) {
          comparison = aNum - bNum;
        } else {
          comparison = aValue.localeCompare(bValue);
        }

        return sortDirection[column] ? comparison : -comparison;
      });

      const tableBody = document.getElementById('tableBody');
      rows.forEach(row => tableBody.appendChild(row));
    }

    function getColumnIndex(column) {
      const headers = {
        'no_reg': 0,
        'nama_lengkap': 1,
        'nisn': 2,
        'tanggal_lahir': 4,
        'no_hp_ortu': 10,
        'created_at': 12
      };
      return headers[column] || 0;
    }

    // Event listeners
    document.getElementById('searchInput').addEventListener('keyup', filterTable);
    document.getElementById('statusFilter').addEventListener('change', filterTable);
  </script>

  <script>
    // Helper to submit forms via AJAX (for modal forms).
    document.addEventListener('submit', function(e) {
      const form = e.target;
      if (!form.classList || !form.classList.contains('ajax-ppdb')) return;
      e.preventDefault();
      const action = form.action || window.location.href;
      const method = (form.method || 'POST').toUpperCase();
      const fd = new FormData(form);

      fetch(action, {
        method: method,
        body: fd,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      }).then(r => r.json()).then(resp => {
        if (resp && resp.success) {
          // close modal if any
          const modal = form.closest('.modal') || form.closest('[id^="modal-"]');
          if (modal && typeof window.closeModal === 'function') {
            // try to close using closeModal helper
            closeModal(modal.id || modal);
          } else if (modal) {
            modal.classList.add('hidden');
          }
          // reload page to refresh table
          window.location.reload();
        } else {
          alert(resp.message || 'Gagal menyimpan data');
        }
      }).catch(err => {
        console.error(err);
        alert('Terjadi kesalahan saat menyimpan.');
      });
    });
  </script>

</body>
</html>
