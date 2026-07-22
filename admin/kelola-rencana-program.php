<?php
define('ADMIN_PAGE', true);
require_once '../includes/session.php';
require_once '../includes/db.php';
require_login();

$title = "Kelola Rencana Program — SLB BC KARYA SEJAHTERA " . SITE_NAME;
$page_title = "Kelola Rencana Program";
$success = '';
$error = '';

// Handle Add Rencana Program
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_rencana'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $data = [
            'nama' => trim($_POST['nama']),
            'deskripsi' => trim($_POST['deskripsi']),
            'durasi' => trim($_POST['durasi']),
            'target' => trim($_POST['target']),
            'status' => $_POST['status'],
            'jenis' => $_POST['jenis'],
            'urutan' => intval($_POST['urutan'])
        ];
        $result = supabaseInsert('rencana_program', $data);
        if ($result['success']) {
          $success = 'Rencana Program berhasil ditambahkan!';
        } else {
          $error = 'Gagal menambahkan Rencana Program: ' . ($result['error'] ?? json_encode($result['data'] ?? $result));
        }
    }
}

// Handle Edit Rencana Program
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_rencana'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $rencanaprogramId = $_POST['rencanaprogram_id'];
        $data = [
            'nama' => trim($_POST['edit_nama']),
            'deskripsi' => trim($_POST['edit_deskripsi']),
            'durasi' => trim($_POST['edit_durasi']),
            'target' => trim($_POST['edit_target']),
            'status' => $_POST['edit_status'],
            'jenis' => $_POST['edit_jenis'],
            'urutan' => intval($_POST['edit_urutan']),
            'updated_at' => date('c')
        ];
        $result = supabaseUpdate('rencana_program', $data, $rencanaprogramId);
        if ($result['success']) {
          $success = 'Rencana Program berhasil diedit!';
        } else {
          $error = 'Gagal edit Rencana Program: ' . ($result['error'] ?? json_encode($result['data'] ?? $result));
        }
    }
}

// Handle Delete Rencana Program
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        $result = supabaseDelete('rencana_program', $_GET['delete']);
        if ($result['success']) {
          $success = 'Rencana Program berhasil dihapus!';
        } else {
          $error = 'Gagal menghapus Rencana Program: ' . ($result['error'] ?? 'Unknown error');
        }
    }
}

// Get All Rencana Program
$rencana_programs = [];
if ($supabaseConnected) {
    $result = supabaseSelect('rencana_program', ['order' => 'urutan.asc']);
    if ($result['success']) {
        $rencana_programs = $result['data'];
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
        
        <!-- Header & Add Button -->
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-8">
          <h2 class="text-2xl font-semibold text-[#1F2D26]">Daftar Rencana Program</h2>
          <button onclick="openAddModal()" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest flex items-center gap-2 disabled:opacity-50">
            <iconify-icon icon="lucide:plus"></iconify-icon> Tambah Rencana Program
          </button>
        </div>
        
        <!-- Rencana Program List -->
        <?php if (empty($rencana_programs)): ?>
          <div class="bg-white rounded-xl border border-[#E8E4D9] shadow-sm">
            <div class="text-center py-12">
              <iconify-icon icon="lucide:calendar" class="text-4xl text-[#9FB5A5] mb-4 block"></iconify-icon>
              <p class="text-[#5F6F65]">Belum ada Rencana Program.</p>
            </div>
          </div>
        <?php else: ?>
          <div class="bg-white rounded-xl border border-[#E8E4D9] shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-[#3E6B4E] text-white">
                  <tr>
                    <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">No</th>
                    <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Nama</th>
                    <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Jenis</th>
                    <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Durasi</th>
                    <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Target</th>
                    <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Status</th>
                    <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Urutan</th>
                    <th class="text-center px-4 py-4 text-xs font-bold uppercase tracking-wider">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E4D9]">
                  <?php $no = 1; foreach ($rencana_programs as $rencana): ?>
                    <tr class="hover:bg-[#F9F8F4] transition-all duration-200">
                      <td class="px-4 py-4 text-sm text-[#5F6F65] font-medium"><?php echo $no++; ?></td>
                      <td class="px-4 py-4 text-sm font-semibold text-[#1F2D26]"><?php echo htmlspecialchars($rencana['nama']); ?></td>
                      <td class="px-4 py-4 text-sm">
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?php echo $rencana['jenis'] === 'pendek' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700'; ?>">
                          <?php echo $rencana['jenis'] === 'pendek' ? 'Program Jangka Pendek' : 'Program Jangka Panjang'; ?>
                        </span>
                      </td>
                      <td class="px-4 py-4 text-sm text-[#5F6F65]"><?php echo htmlspecialchars($rencana['durasi'] ?? '-'); ?></td>
                      <td class="px-4 py-4 text-sm text-[#5F6F65] line-clamp-2"><?php echo htmlspecialchars($rencana['target'] ?? '-'); ?></td>
                      <td class="px-4 py-4 text-sm">
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?php echo $rencana['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?>">
                          <?php echo $rencana['status']; ?>
                        </span>
                      </td>
                      <td class="px-4 py-4 text-sm text-[#5F6F65]"><?php echo htmlspecialchars($rencana['urutan']); ?></td>
                      <td class="px-4 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                          <button onclick="openEditModal(
                            '<?php echo htmlspecialchars($rencana['id']); ?>',
                            '<?php echo addslashes(htmlspecialchars($rencana['nama'])); ?>',
                            '<?php echo addslashes(htmlspecialchars($rencana['deskripsi'] ?? '')); ?>',
                            '<?php echo addslashes(htmlspecialchars($rencana['durasi'] ?? '')); ?>',
                            '<?php echo addslashes(htmlspecialchars($rencana['target'] ?? '')); ?>',
                            '<?php echo htmlspecialchars($rencana['status']); ?>',
                            '<?php echo htmlspecialchars($rencana['jenis'] ?? 'pendek'); ?>',
                            '<?php echo htmlspecialchars($rencana['urutan']); ?>'
                          )" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors">
                            <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                          </button>
                          <a href="?delete=<?php echo $rencana['id']; ?>" onclick="return confirm('Yakin ingin menghapus rencana program ini?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
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
    </div>
  </main>

  <!-- Modal Tambah Rencana Program -->
  <div id="modalRencana" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('modalRencana').classList.add('hidden')"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-lg w-full overflow-hidden">
        <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between bg-[#3E6B4E]">
          <h3 class="font-semibold text-lg text-white">Tambah Rencana Program Baru</h3>
          <button onclick="document.getElementById('modalRencana').classList.add('hidden')" class="text-white/70 hover:text-white">
            <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
          </button>
        </div>
        <form method="POST" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Nama Rencana Program</label>
            <input type="text" name="nama" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Jenis Program</label>
            <select name="jenis" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
              <option value="pendek">Program Jangka Pendek</option>
              <option value="panjang">Program Jangka Panjang</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Durasi</label>
            <input type="text" name="durasi" placeholder="Misal: Semester 1 2024" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Target</label>
            <textarea name="target" rows="3" placeholder="Deskripsi target yang ingin dicapai" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm resize-none"></textarea>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Deskripsi</label>
            <textarea name="deskripsi" rows="3" placeholder="Penjelasan detail rencana program" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm resize-none"></textarea>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Status</label>
            <select name="status" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Urutan</label>
            <input type="number" name="urutan" value="0" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
          </div>
          <div class="flex items-center gap-4 pt-4">
            <button type="button" onclick="document.getElementById('modalRencana').classList.add('hidden')" class="flex-1 px-6 py-3 rounded border border-[#E8E4D9] text-[#5F6F65] hover:bg-[#F9F8F4] transition-colors text-sm">Batal</button>
            <button type="submit" name="tambah_rencana" class="flex-1 bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Edit Rencana Program -->
  <div id="modalEditRencana" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('modalEditRencana').classList.add('hidden')"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-lg w-full overflow-hidden">
        <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between bg-[#3E6B4E]">
          <h3 class="font-semibold text-lg text-white">Edit Rencana Program</h3>
          <button onclick="document.getElementById('modalEditRencana').classList.add('hidden')" class="text-white/70 hover:text-white">
            <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
          </button>
        </div>
        <form method="POST" class="p-6 space-y-4">
          <input type="hidden" name="rencanaprogram_id" id="edit_rencanaprogram_id" value="">
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Nama Rencana Program</label>
            <input type="text" name="edit_nama" id="edit_nama" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Jenis Program</label>
            <select name="edit_jenis" id="edit_jenis" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
              <option value="pendek">Program Jangka Pendek</option>
              <option value="panjang">Program Jangka Panjang</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Durasi</label>
            <input type="text" name="edit_durasi" id="edit_durasi" placeholder="Misal: Semester 1 2024" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Target</label>
            <textarea name="edit_target" id="edit_target" rows="3" placeholder="Deskripsi target yang ingin dicapai" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm resize-none"></textarea>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Deskripsi</label>
            <textarea name="edit_deskripsi" id="edit_deskripsi" rows="3" placeholder="Penjelasan detail rencana program" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm resize-none"></textarea>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Status</label>
            <select name="edit_status" id="edit_status" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Urutan</label>
            <input type="number" name="edit_urutan" id="edit_urutan" value="0" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
          </div>
          <div class="flex items-center gap-4 pt-4">
            <button type="button" onclick="document.getElementById('modalEditRencana').classList.add('hidden')" class="flex-1 px-6 py-3 rounded border border-[#E8E4D9] text-[#5F6F65] hover:bg-[#F9F8F4] transition-colors text-sm">Batal</button>
            <button type="submit" name="edit_rencana" class="flex-1 bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function openAddModal() {
      document.getElementById('modalRencana').classList.remove('hidden');
    }

    function openEditModal(id, nama, deskripsi, durasi, target, status, jenis, urutan) {
      document.getElementById('edit_rencanaprogram_id').value = id;
      document.getElementById('edit_nama').value = nama;
      document.getElementById('edit_deskripsi').value = deskripsi;
      document.getElementById('edit_durasi').value = durasi;
      document.getElementById('edit_target').value = target;
      document.getElementById('edit_status').value = status;
      document.getElementById('edit_jenis').value = jenis;
      document.getElementById('edit_urutan').value = urutan;
      document.getElementById('modalEditRencana').classList.remove('hidden');
    }
  </script>

</body>
</html>
