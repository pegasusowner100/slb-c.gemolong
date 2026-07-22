<?php
define('ADMIN_PAGE', true);
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/cloudinary-on.php';
require_login();

$title = "Kelola Testimoni — " . SITE_NAME;
$page_title = "Kelola Testimoni";
$success = '';
$error = '';

// Handle tambah testimoni
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_testimoni'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $nama = trim($_POST['nama']);
        $jabatan = trim($_POST['jabatan']);
        $pesan = trim($_POST['pesan']);
        $bintang = (int)($_POST['bintang'] ?? 5);
        $urutan = (int)($_POST['urutan'] ?? 0);
        $status = $_POST['status'];
        $foto = null;

        if (isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['foto_file'], 'testimoni');
            if ($uploadResult['success']) {
                $foto = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload foto: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }

        $data = [
            'nama' => $nama,
            'jabatan' => $jabatan,
            'pesan' => $pesan,
            'foto' => $foto ?: null,
            'bintang' => $bintang,
            'urutan' => $urutan,
            'status' => $status,
        ];

        $result = supabaseInsert('testimoni', $data);
        if ($result['success']) {
            $success = 'Testimoni berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan testimoni! ' . ($result['error'] ?? '');
        }
    }
}

// Handle edit testimoni
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_testimoni'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $testimoniId = $_POST['testimoni_id'];
        $nama = trim($_POST['edit_nama']);
        $jabatan = trim($_POST['edit_jabatan']);
        $pesan = trim($_POST['edit_pesan']);
        $bintang = (int)($_POST['edit_bintang'] ?? 5);
        $urutan = (int)($_POST['edit_urutan'] ?? 0);
        $status = $_POST['edit_status'];
        $foto = null;

        // Keep current foto if no new upload is provided
        $currentTestimoni = supabaseSelect('testimoni', ['id' => 'eq.' . $testimoniId, 'limit' => 1]);
        if ($currentTestimoni['success'] && !empty($currentTestimoni['data'])) {
            $foto = $currentTestimoni['data'][0]['foto'] ?? null;
        }

        if (isset($_FILES['edit_foto_file']) && $_FILES['edit_foto_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['edit_foto_file'], 'testimoni');
            if ($uploadResult['success']) {
                $foto = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload foto: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }

        $data = [
            'nama' => $nama,
            'jabatan' => $jabatan,
            'pesan' => $pesan,
            'foto' => $foto ?: null,
            'bintang' => $bintang,
            'urutan' => $urutan,
            'status' => $status,
            'updated_at' => date('c')
        ];

        $result = supabaseUpdate('testimoni', $data, $testimoniId);
        if ($result['success']) {
            $success = 'Testimoni berhasil diperbarui!';
        } else {
            $error = 'Gagal memperbarui testimoni! ' . ($result['error'] ?? '');
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        $result = supabaseDelete('testimoni', $_GET['delete']);
        if ($result['success']) {
            header('Location: kelola-testimoni.php?success=deleted');
            exit;
        } else {
            $error = 'Gagal menghapus testimoni! ' . ($result['error'] ?? '');
        }
    }
}

if (isset($_GET['success']) && $_GET['success'] === 'deleted') {
    $success = 'Testimoni berhasil dihapus!';
}

// Handle search query
$search = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
}

// Get all testimonials from Supabase
$all_testimoni = [];
if ($supabaseConnected) {
    $query = ['order' => 'urutan.asc, created_at.asc'];
    if ($search) {
        $query['or'] = "(nama.ilike.%$search%,jabatan.ilike.%$search%,pesan.ilike.%$search%)";
    }
    $result = supabaseSelect('testimoni', $query);
    if ($result['success']) {
        $all_testimoni = $result['data'];
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
            <h3 class="text-xl font-semibold text-[#1F2D26]">Daftar Testimoni</h3>
            <button onclick="document.getElementById('modalTestimoni').classList.remove('hidden')" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-gradient-to-r from-emerald-600 to-emerald-700 text-white text-sm font-semibold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 uppercase tracking-wide flex items-center gap-2 disabled:opacity-50">
              <iconify-icon icon="lucide:plus"></iconify-icon>
              Tambah Testimoni Baru
            </button>
          </div>

          <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
            <div class="w-full md:w-96">
              <input id="searchInput" type="text" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari nama, jabatan, atau pesan..." oninput="filterAdminTable(this)" data-filter-selector="#table-view tbody tr, #grid-view > div" class="w-full flex-1 px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all">
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

          <?php if ($search): ?>
            <div class="flex items-center gap-2 text-sm text-emerald-600 bg-emerald-50 px-4 py-2 rounded-xl border border-emerald-100">
              <iconify-icon icon="lucide:search"></iconify-icon>
              Hasil pencarian untuk "<strong><?php echo htmlspecialchars($search); ?></strong>" ditemukan <strong><?php echo count($all_testimoni); ?></strong> testimoni
            </div>
          <?php endif; ?>
        </div>

        <?php if (empty($all_testimoni)): ?>
          <div class="col-span-full text-center py-16 bg-white rounded-2xl border border-gray-100 shadow-sm">
            <iconify-icon icon="lucide:message-square-heart" class="text-6xl text-gray-300 mx-auto mb-4"></iconify-icon>
            <p class="text-gray-500 mb-4">Belum ada testimoni. Klik tombol "Tambah Testimoni Baru" untuk memulai.</p>
          </div>
        <?php else: ?>
          <div id="grid-view" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($all_testimoni as $item): ?>
              <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
                <div class="p-6">
                  <div class="flex items-center gap-2 mb-4">
                    <span class="px-3 py-1 <?php echo $item['status'] === 'published' ? 'bg-gradient-to-r from-emerald-500 to-emerald-600' : 'bg-gradient-to-r from-amber-500 to-amber-600'; ?> text-white text-xs font-bold rounded-full uppercase tracking-wider">
                      <?php echo htmlspecialchars($item['status'] ?? 'published'); ?>
                    </span>
                    <span class="text-xs text-gray-400">Urutan: <?php echo htmlspecialchars($item['urutan'] ?? 0); ?></span>
                  </div>
                  <div class="relative mb-5">
                    <div class="w-full h-48 rounded-3xl overflow-hidden bg-slate-100">
                      <img src="<?php echo htmlspecialchars($item['foto'] ?: 'https://picsum.photos/seed/testimoni-' . $item['id'] . '/600/400.jpg'); ?>" alt="<?php echo htmlspecialchars($item['nama']); ?>" class="w-full h-full object-cover" onerror="this.src='https://picsum.photos/seed/testimoni-<?php echo htmlspecialchars($item['id']); ?>/600/400.jpg'">
                    </div>
                  </div>
                  <h3 class="font-serif text-lg font-semibold text-gray-800 mb-2 line-clamp-2"><?php echo htmlspecialchars($item['nama']); ?></h3>
                  <p class="text-sm text-emerald-700 font-semibold mb-3"><?php echo htmlspecialchars($item['jabatan'] ?? '-'); ?></p>
                  <p class="text-sm text-gray-500 line-clamp-4 mb-5"><?php echo htmlspecialchars(strip_tags($item['pesan'])); ?></p>
                  <div class="flex items-center justify-between gap-3 pt-4 border-t border-gray-100">
                    <div class="flex items-center gap-1 text-yellow-500">
                      <?php for ($i = 1; $i <= 5; $i++): ?>
                        <iconify-icon icon="lucide:star" class="w-4 h-4 <?php echo $i <= ($item['bintang'] ?? 5) ? 'text-yellow-500' : 'text-gray-300'; ?>"></iconify-icon>
                      <?php endfor; ?>
                    </div>
                    <div class="flex items-center gap-2">
                      <button class="btn-edit-testimoni p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all"
                              data-id="<?php echo htmlspecialchars($item['id']); ?>"
                              data-nama="<?php echo htmlspecialchars($item['nama']); ?>"
                              data-jabatan="<?php echo htmlspecialchars($item['jabatan'] ?? ''); ?>"
                              data-pesan="<?php echo htmlspecialchars($item['pesan']); ?>"
                              data-foto="<?php echo htmlspecialchars($item['foto'] ?? ''); ?>"
                              data-bintang="<?php echo htmlspecialchars($item['bintang'] ?? 5); ?>"
                              data-urutan="<?php echo htmlspecialchars($item['urutan'] ?? 0); ?>"
                              data-status="<?php echo htmlspecialchars($item['status'] ?? 'published'); ?>">
                        <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                      </button>
                      <a href="?delete=<?php echo $item['id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" onclick="return confirm('Yakin ingin menghapus testimoni ini?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
                        <iconify-icon icon="lucide:trash-2" class="w-5 h-5"></iconify-icon>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div id="table-view" class="hidden">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-gradient-to-r from-emerald-600 to-emerald-700 text-white">
                    <tr>
                      <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Nama</th>
                      <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Jabatan</th>
                      <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Pesan</th>
                      <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Bintang</th>
                      <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                      <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Urutan</th>
                      <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100">
                    <?php foreach ($all_testimoni as $item): ?>
                      <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-gray-800 font-medium"><?php echo htmlspecialchars($item['nama']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($item['jabatan']); ?></td>
                        <td class="px-6 py-4 text-gray-500 text-sm line-clamp-2"><?php echo htmlspecialchars(strip_tags($item['pesan'])); ?></td>
                        <td class="px-6 py-4 text-yellow-500 font-semibold"><?php echo htmlspecialchars($item['bintang'] ?? 5); ?></td>
                        <td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $item['status'] === 'published' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'; ?>"><?php echo htmlspecialchars(strtoupper($item['status'] ?? 'published')); ?></span></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($item['urutan'] ?? 0); ?></td>
                        <td class="px-6 py-4 text-center">
                          <div class="flex items-center justify-center gap-2">
                            <button class="btn-edit-testimoni p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all"
                                    data-id="<?php echo htmlspecialchars($item['id']); ?>"
                                    data-nama="<?php echo htmlspecialchars($item['nama']); ?>"
                                    data-jabatan="<?php echo htmlspecialchars($item['jabatan'] ?? ''); ?>"
                                    data-pesan="<?php echo htmlspecialchars($item['pesan']); ?>"
                                    data-foto="<?php echo htmlspecialchars($item['foto'] ?? ''); ?>"
                                    data-bintang="<?php echo htmlspecialchars($item['bintang'] ?? 5); ?>"
                                    data-urutan="<?php echo htmlspecialchars($item['urutan'] ?? 0); ?>"
                                    data-status="<?php echo htmlspecialchars($item['status'] ?? 'published'); ?>">
                              <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                            </button>
                            <a href="?delete=<?php echo $item['id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" onclick="return confirm('Yakin ingin menghapus testimoni ini?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
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

  <!-- Modal Tambah Testimoni -->
  <div id="modalTestimoni" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
      <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-emerald-600 to-emerald-700">
        <h3 class="text-lg font-semibold text-white">Tambah Testimoni Baru</h3>
        <button onclick="document.getElementById('modalTestimoni').classList.add('hidden')" class="p-2 text-white/80 hover:bg-white/20 rounded-lg transition-all">
          <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
        </button>
      </div>
      <form method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
          <input type="text" name="nama" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all" placeholder="Nama pemberi testimoni...">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Jabatan</label>
          <input type="text" name="jabatan" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all" placeholder="Jabatan atau peran...">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Pesan</label>
          <textarea name="pesan" rows="4" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all" placeholder="Tulis testimoni..."></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Foto Testimoni</label>
            <input id="add_foto_file" type="file" name="foto_file" accept="image/*" class="w-full text-sm text-gray-600 file:bg-emerald-600 file:text-white file:px-4 file:py-2 file:rounded-xl file:border-0" />
            <img id="add_foto_preview" src="#" alt="Preview Foto" class="hidden mt-4 w-32 h-32 rounded-full object-cover border border-gray-200" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Bintang</label>
            <select name="bintang" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?> bintang</option>
              <?php endfor; ?>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
          <button type="button" onclick="document.getElementById('modalTestimoni').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">Batal</button>
          <button type="submit" name="tambah_testimoni" class="px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-xl hover:from-emerald-700 hover:to-emerald-800 transition-all shadow-md">Simpan Testimoni</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Edit Testimoni -->
  <div id="modalEditTestimoni" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
      <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-emerald-600 to-emerald-700">
        <h3 class="text-lg font-semibold text-white">Edit Testimoni</h3>
        <button onclick="document.getElementById('modalEditTestimoni').classList.add('hidden')" class="p-2 text-white/80 hover:bg-white/20 rounded-lg transition-all">
          <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
        </button>
      </div>
      <form id="formEditTestimoni" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
        <input type="hidden" name="testimoni_id" id="edit_testimoni_id">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
          <input type="text" name="edit_nama" id="edit_nama" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Jabatan</label>
          <input type="text" name="edit_jabatan" id="edit_jabatan" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Pesan</label>
          <textarea name="edit_pesan" id="edit_pesan" rows="4" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all"></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Foto Testimoni</label>
            <input id="edit_foto_file" type="file" name="edit_foto_file" accept="image/*" class="w-full text-sm text-gray-600 file:bg-emerald-600 file:text-white file:px-4 file:py-2 file:rounded-xl file:border-0" />
            <img id="edit_foto_preview" src="#" alt="Preview Foto" class="hidden mt-4 w-32 h-32 rounded-full object-cover border border-gray-200" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Bintang</label>
            <select name="edit_bintang" id="edit_bintang" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?> bintang</option>
              <?php endfor; ?>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
          <button type="button" onclick="document.getElementById('modalEditTestimoni').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">Batal</button>
          <button type="submit" name="edit_testimoni" class="px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-xl hover:from-emerald-700 hover:to-emerald-800 transition-all shadow-md">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const addFotoFile = document.getElementById('add_foto_file');
      const addPreview = document.getElementById('add_foto_preview');
      const editFotoFile = document.getElementById('edit_foto_file');
      const editPreview = document.getElementById('edit_foto_preview');

      if (addFotoFile) {
        addFotoFile.addEventListener('change', function() {
          if (this.files && this.files[0]) {
            addPreview.src = URL.createObjectURL(this.files[0]);
            addPreview.classList.remove('hidden');
          } else {
            addPreview.classList.add('hidden');
          }
        });
      }

      if (editFotoFile) {
        editFotoFile.addEventListener('change', function() {
          if (this.files && this.files[0]) {
            editPreview.src = URL.createObjectURL(this.files[0]);
            editPreview.classList.remove('hidden');
          }
        });
      }

      document.querySelectorAll('.btn-edit-testimoni').forEach(button => {
        button.addEventListener('click', function() {
          const id = this.getAttribute('data-id');
          const nama = this.getAttribute('data-nama');
          const jabatan = this.getAttribute('data-jabatan');
          const pesan = this.getAttribute('data-pesan');
          const foto = this.getAttribute('data-foto');
          const bintang = this.getAttribute('data-bintang');
          const urutan = this.getAttribute('data-urutan');
          const status = this.getAttribute('data-status');

          document.getElementById('edit_testimoni_id').value = id;
          document.getElementById('edit_nama').value = nama;
          document.getElementById('edit_jabatan').value = jabatan;
          document.getElementById('edit_pesan').value = pesan;
          document.getElementById('edit_bintang').value = bintang;
          document.getElementById('edit_urutan').value = urutan;
          document.getElementById('edit_status').value = status;

          if (foto) {
            editPreview.src = foto;
            editPreview.classList.remove('hidden');
          } else {
            editPreview.classList.add('hidden');
          }

          document.getElementById('modalEditTestimoni').classList.remove('hidden');
        });
      });

      const savedView = localStorage.getItem('testimoniView') || 'table';
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
      localStorage.setItem('testimoniView', view);
    }

    function filterAdminTable(input) {
      const filter = input.value.toLowerCase();
      const selectors = input.dataset.filterSelector ? input.dataset.filterSelector.split(',') : [];
      selectors.forEach(selector => {
        document.querySelectorAll(selector.trim()).forEach(item => {
          const text = item.textContent.toLowerCase();
          item.style.display = text.includes(filter) ? '' : 'none';
        });
      });
    }
  </script>
</body>
</html>
