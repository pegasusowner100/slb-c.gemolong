<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_login();

// Format tanggal Indonesia
function formatTanggalIndonesia($timestamp) {
  $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
  $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  
  $dt = new DateTime($timestamp);
  $namaHari = $hari[$dt->format('w')];
  $tanggal = $dt->format('d');
  $namaBulan = $bulan[(int)$dt->format('m')];
  $tahun = $dt->format('Y');
  $jam = $dt->format('H:i');
  
  return "$namaHari, $tanggal $namaBulan $tahun $jam";
}

 $title = "Kelola Surat — " . SITE_NAME;
 $page_title = "Kelola Surat";

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete' && !empty($_POST['id'])) {
        if ($supabaseConnected) {
            $del = supabaseDelete('surat', $_POST['id']);
            $message = $del['success'] ? 'Hapus sukses.' : 'Hapus gagal: ' . ($del['error'] ?? '');
        } else {
            $message = 'Supabase tidak terhubung.';
        }
    } elseif ($_POST['action'] === 'edit' && !empty($_POST['id'])) {
        if ($supabaseConnected) {
            $id = $_POST['id'];
            $data = [
                'nama' => trim($_POST['nama'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'no_hp' => trim($_POST['no_hp'] ?? ''),
                'jenis_surat' => trim($_POST['jenis_surat'] ?? ''),
                'jenis_surat_lainnya' => trim($_POST['jenis_surat_lainnya'] ?? ''),
                'keterangan' => trim($_POST['keterangan'] ?? '')
            ];
            $upd = supabaseUpdate('surat', $data, $id);
            $message = $upd['success'] ? 'Edit sukses.' : 'Edit gagal: ' . ($upd['error'] ?? '');
        } else {
            $message = 'Supabase tidak terhubung.';
        }
    } elseif ($_POST['action'] === 'respond' && !empty($_POST['id'])) {
        if ($supabaseConnected) {
          $id = $_POST['id'];
          $respon = trim($_POST['respon'] ?? '');
          $status = trim($_POST['status'] ?? 'sudah_direspon');
          $upd = supabaseUpdate('surat', ['respon' => $respon, 'status' => $status], $id);
          $message = $upd['success'] ? 'Respon tersimpan.' : 'Update gagal: ' . ($upd['error'] ?? '');
        } else {
          $message = 'Supabase tidak terhubung.';
        }
    }
}

include 'components/head.php';
include 'components/sidebar.php';
?>
  <main class="flex-1 flex flex-col overflow-hidden">
    <?php include 'components/header.php'; ?>
    <div class="flex-1 overflow-y-auto p-8">
      <div class="max-w-7xl">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-md overflow-hidden">
          <div class="p-6 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-semibold text-slate-900 text-lg"><?php echo $page_title; ?></h3>
            <div class="text-sm">
              <?php if ($supabaseConnected):
                $countInfo = function_exists('supabaseCount') ? supabaseCount('surat') : null;
                $suratCount = (is_array($countInfo) && isset($countInfo['count']) && $countInfo['count'] !== null) ? $countInfo['count'] : null;
              ?>
                <span class="text-green-600 font-medium"></span>
                <?php if ($suratCount !== null): ?> • <span class="font-semibold"><?php echo htmlspecialchars($suratCount); ?></span> entri surat
                <?php endif; ?>
              <?php else: ?>
                <span class="text-red-600 font-medium">Supabase tidak terhubung</span>
              <?php endif; ?>
            </div>
          </div>
          <div class="p-6">
            <div id="message-box" class="hidden mb-4 p-3 bg-yellow-50 border border-yellow-100 text-yellow-700 rounded"></div>

            <?php if ($supabaseConnected): ?>
              <?php
                $list = supabaseSelect('surat', ['order' => 'created_at.desc']);
                if ($list['success'] && !empty($list['data'])):
              ?>
                <div class="mb-4 flex items-center gap-3">
                  <input id="filter-search" type="text" placeholder="Cari nama, email, jenis, keterangan..." class="px-3 py-2 border rounded w-1/3 text-sm">
                  <input id="filter-from" type="date" class="px-3 py-2 border rounded text-sm">
                  <input id="filter-to" type="date" class="px-3 py-2 border rounded text-sm">
                  <button id="filter-reset" class="px-3 py-2 bg-gray-200 rounded text-sm">Reset</button>
                </div>
                <div class="overflow-x-auto">
                  <table class="w-full text-left">
                    <thead class="bg-slate-50">
                      <tr>
                        <th class="px-4 py-3 text-xs font-bold text-slate-600">No</th>
                        <th class="px-4 py-3 text-xs font-bold text-slate-600">Nama</th>
                        <th class="px-4 py-3 text-xs font-bold text-slate-600">Email</th>
                        <th class="px-4 py-3 text-xs font-bold text-slate-600">HP</th>
                        <th class="px-4 py-3 text-xs font-bold text-slate-600">Jenis Surat</th>
                        <th class="px-4 py-3 text-xs font-bold text-slate-600">Keterangan</th>
                        <th class="px-4 py-3 text-xs font-bold text-slate-600">Respon</th>
                        <th class="px-4 py-3 text-xs font-bold text-slate-600">Waktu</th>
                        <th class="px-4 py-3 text-xs font-bold text-slate-600">Aksi</th>
                      </tr>
                    </thead>
                    <tbody id="surat-tbody" class="divide-y divide-slate-100">
                      <?php $i = 1; foreach ($list['data'] as $row): ?>
                        <tr data-created_at="<?php echo htmlspecialchars($row['created_at'] ?? ''); ?>" data-search="<?php echo htmlspecialchars(strtolower(($row['nama'] ?? '') . ' ' . ($row['email'] ?? '') . ' ' . ($row['jenis_surat'] ?? '') . ' ' . ($row['keterangan'] ?? ''))); ?>">
                          <td class="px-4 py-3 text-sm"><?php echo $i++; ?></td>
                          <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($row['nama'] ?? '-'); ?></td>
                          <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($row['email'] ?? '-'); ?></td>
                          <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($row['no_hp'] ?? '-'); ?></td>
                          <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($row['jenis_surat'] ?? ($row['jenis_surat_lainnya'] ?? '-')); ?></td>
                          <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($row['keterangan'] ?? '-'); ?></td>
                          <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($row['respon'] ?? '-'); ?></td>
                          <td class="px-4 py-3 text-sm"><?php echo $row['created_at'] ? formatTanggalIndonesia($row['created_at']) : '-'; ?></td>
                          <td class="px-4 py-3 text-sm">
                            <div id="respond-<?php echo htmlspecialchars($row['id'] ?? ''); ?>" class="hidden">
                              <form method="post" class="space-y-2">
                                <input type="hidden" name="action" value="respond">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id'] ?? ''); ?>">
                                <textarea name="respon" rows="4" placeholder="Tulis respon..." class="w-full px-2 py-1 border rounded text-sm"><?php echo htmlspecialchars($row['respon'] ?? ''); ?></textarea>
                                <div class="flex gap-2 items-center">
                                  <select name="status" class="text-sm px-2 py-1 border rounded flex-1">
                                    <option value="belum_direspon" <?php echo (($row['status'] ?? '') === 'belum_direspon') ? 'selected' : ''; ?>>Belum Direspon</option>
                                    <option value="sudah_direspon" <?php echo (($row['status'] ?? '') === 'sudah_direspon') ? 'selected' : ''; ?>>Sudah Direspon</option>
                                    <option value="tidak_direspon" <?php echo (($row['status'] ?? '') === 'tidak_direspon') ? 'selected' : ''; ?>>Tidak Direspon</option>
                                    <option value="diproses" <?php echo (($row['status'] ?? '') === 'diproses') ? 'selected' : ''; ?>>Diproses</option>
                                    <option value="tidak_diproses" <?php echo (($row['status'] ?? '') === 'tidak_diproses') ? 'selected' : ''; ?>>Tidak Diproses</option>
                                  </select>
                                  <button type="submit" class="p-2 text-white bg-emerald-500 rounded transition-colors" title="Simpan"><iconify-icon icon="lucide:save"></iconify-icon></button>
                                </div>
                              </form>
                            </div>
                            <button onclick="toggleEdit('edit-<?php echo htmlspecialchars($row['id'] ?? ''); ?>')" class="p-2 text-[#5F6F65] hover:text-[#3E6B4E] transition-colors" title="Edit / Respon"><iconify-icon icon="lucide:edit-2"></iconify-icon></button>
                            <div id="edit-<?php echo htmlspecialchars($row['id'] ?? ''); ?>" class="hidden bg-slate-100 p-3 rounded mb-2">
                              <form method="post" class="space-y-2">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id'] ?? ''); ?>">
                                <input type="text" name="nama" value="<?php echo htmlspecialchars($row['nama'] ?? ''); ?>" class="w-full px-2 py-1 border rounded text-xs" placeholder="Nama">
                                <input type="email" name="email" value="<?php echo htmlspecialchars($row['email'] ?? ''); ?>" class="w-full px-2 py-1 border rounded text-xs" placeholder="Email">
                                <input type="text" name="no_hp" value="<?php echo htmlspecialchars($row['no_hp'] ?? ''); ?>" class="w-full px-2 py-1 border rounded text-xs" placeholder="No HP">
                                <input type="text" name="jenis_surat" value="<?php echo htmlspecialchars($row['jenis_surat'] ?? ''); ?>" class="w-full px-2 py-1 border rounded text-xs" placeholder="Jenis Surat">
                                <input type="text" name="jenis_surat_lainnya" value="<?php echo htmlspecialchars($row['jenis_surat_lainnya'] ?? ''); ?>" class="w-full px-2 py-1 border rounded text-xs" placeholder="Jenis Surat Lainnya">
                                <textarea name="keterangan" class="w-full px-2 py-1 border rounded text-xs" placeholder="Keterangan" rows="2"><?php echo htmlspecialchars($row['keterangan'] ?? ''); ?></textarea>
                                <button type="submit" class="p-2 text-white bg-blue-600 rounded transition-colors" title="Update"><iconify-icon icon="lucide:save"></iconify-icon></button>
                              </form>
                            </div>
                            <form method="post" onsubmit="return confirm('Hapus entry ini?');">
                              <input type="hidden" name="action" value="delete">
                              <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id'] ?? ''); ?>">
                              <button type="submit" class="p-2 text-white bg-red-500 rounded transition-colors" title="Hapus"><iconify-icon icon="lucide:trash-2"></iconify-icon></button>
                            </form>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                <div class="p-4 text-sm text-slate-600">Belum ada data pada tabel <strong>surat</strong>.</div>
              <?php endif; ?>
            <?php else: ?>
              <div class="p-4 text-sm text-red-600">Supabase tidak terhubung. Cek konfigurasi di <code>includes/config.php</code>.</div>
            <?php endif; ?>


          </div>
        </div>
      </div>
    </div>
  </main>
  <script>
    // Server message (will remain hidden until Edit is opened)
    const serverMessage = '<?php echo addslashes($message ?? ''); ?>';

    function toggleEdit(id) {
      const el = document.getElementById(id);
      const msgBox = document.getElementById('message-box');
      const wasHidden = el.classList.contains('hidden');
      el.classList.toggle('hidden');

      // also toggle respond area with same id suffix
      const suffix = id.replace(/^edit-/, '');
      const resp = document.getElementById('respond-' + suffix);
      if (resp) resp.classList.toggle('hidden');

      if (wasHidden) {
        // just opened
        if (serverMessage && serverMessage.length) {
          msgBox.textContent = serverMessage;
          msgBox.classList.remove('hidden');
        } else {
          msgBox.classList.add('hidden');
        }
      } else {
        // just closed
        msgBox.classList.add('hidden');
      }
    }
    // Filtering logic
    function matchesDate(dateStr, fromStr, toStr) {
      if (!dateStr) return false;
      const d = new Date(dateStr);
      if (isNaN(d)) return false;
      if (fromStr) {
        const f = new Date(fromStr + 'T00:00:00');
        if (d < f) return false;
      }
      if (toStr) {
        const t = new Date(toStr + 'T23:59:59');
        if (d > t) return false;
      }
      return true;
    }

    function applyFilters() {
      const q = document.getElementById('filter-search').value.trim().toLowerCase();
      const from = document.getElementById('filter-from').value;
      const to = document.getElementById('filter-to').value;
      const tbody = document.getElementById('surat-tbody');
      const rows = Array.from(tbody.querySelectorAll('tr'));
      let visibleIndex = 1;
      rows.forEach(r => {
        const text = (r.getAttribute('data-search') || '');
        const created = r.getAttribute('data-created_at') || '';
        const okText = !q || text.indexOf(q) !== -1;
        const okDate = (!from && !to) || matchesDate(created, from, to);
        if (okText && okDate) {
          r.style.display = '';
          // update number cell (first td)
          const noCell = r.querySelector('td');
          if (noCell) noCell.textContent = visibleIndex++;
        } else {
          r.style.display = 'none';
        }
      });
    }

    function resetFilters() {
      document.getElementById('filter-search').value = '';
      document.getElementById('filter-from').value = '';
      document.getElementById('filter-to').value = '';
      applyFilters();
    }

    document.addEventListener('DOMContentLoaded', function(){
      const s = document.getElementById('filter-search');
      const f = document.getElementById('filter-from');
      const t = document.getElementById('filter-to');
      const reset = document.getElementById('filter-reset');
      if (s) s.addEventListener('input', applyFilters);
      if (f) f.addEventListener('change', applyFilters);
      if (t) t.addEventListener('change', applyFilters);
      if (reset) reset.addEventListener('click', resetFilters);
    });
  </script>
</body>
</html>
