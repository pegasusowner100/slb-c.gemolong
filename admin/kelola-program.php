

<?php
define('ADMIN_PAGE', true);
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/cloudinary-on.php';
require_login();

$title = "Kelola Program Unggulan — SLB BC KARYA SEJAHTERA";
$page_title = "Kelola Program Unggulan";
$success = '';
$error = '';

// Handle Add Program
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_program'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $gambar = 'https://picsum.photos/seed/program/500/300'; // Default
        
        // Handle file upload
        if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['gambar_file'], 'program');
            if ($uploadResult['success']) {
                $gambar = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload gambar: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }
        
        $data = [
            'icon' => $_POST['icon'],
            'nama' => $_POST['nama'],
            'deskripsi' => $_POST['deskripsi'],
            'gambar' => $gambar,
            'urutan' => intval($_POST['urutan'])
        ];
        $result = supabaseInsert('program', $data);
        if ($result['success']) {
            $success = 'Program berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan program! Error: ' . json_encode($result);
        }
    }
}

// Handle Edit Program
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_program'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $programId = $_POST['program_id'];
        
        // Get current program to keep gambar if no new file
        $currentProgramResult = supabaseSelect('program', ['id' => 'eq.' . $programId, 'limit' => '1']);
        $currentGambar = 'https://picsum.photos/seed/program/500/300';
        if ($currentProgramResult['success'] && !empty($currentProgramResult['data'])) {
            $currentGambar = $currentProgramResult['data'][0]['gambar'] ?? $currentGambar;
        }
        
        // Handle file upload
        if (isset($_FILES['edit_gambar_file']) && $_FILES['edit_gambar_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadToCloudinary($_FILES['edit_gambar_file'], 'program');
            if ($uploadResult['success']) {
                $currentGambar = $uploadResult['url'];
            } elseif (!isset($uploadResult['skip_upload'])) {
                $error = 'Gagal upload gambar: ' . ($uploadResult['error'] ?? 'Unknown error');
            }
        }
        
        $data = [
            'icon' => $_POST['edit_icon'],
            'nama' => $_POST['edit_nama'],
            'deskripsi' => $_POST['edit_deskripsi'],
            'gambar' => $currentGambar,
            'urutan' => intval($_POST['edit_urutan'])
        ];
        $result = supabaseUpdate('program', $data, $programId);
        if ($result['success']) {
            $success = 'Program berhasil diedit!';
        } else {
            $error = 'Gagal edit program! Error: ' . json_encode($result);
        }
    }
}

// Handle Delete Program
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    if (!$supabaseConnected) {
        $error = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        $result = supabaseDelete('program', $_GET['delete']);
        if ($result['success']) {
            $success = 'Program berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus program!';
        }
    }
}

// Get All Programs
$programs = [];
if ($supabaseConnected) {
    $programResult = supabaseSelect('program', ['order' => 'urutan.asc']);
    if ($programResult['success']) {
        $programs = $programResult['data'];
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
        
        <!-- Add Program Button -->
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-8">
          <h2 class="text-2xl font-semibold text-[#1F2D26]">Daftar Program Unggulan</h2>
          <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex items-center bg-white rounded-lg border border-[#E8E4D9] p-1">
              <button id="programGridViewBtn" class="px-4 py-2 rounded-md bg-[#3E6B4E] text-white text-xs font-bold transition-colors">
                <iconify-icon icon="lucide:grid-3x3" class="inline mr-1"></iconify-icon> Grid
              </button>
              <button id="programTableViewBtn" class="px-4 py-2 rounded-md text-[#5F6F65] hover:bg-[#F9F8F4] text-xs font-bold transition-colors">
                <iconify-icon icon="lucide:table" class="inline mr-1"></iconify-icon> Tabel
              </button>
            </div>
            <button onclick="openAddModal()" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest flex items-center gap-2 disabled:opacity-50">
              <iconify-icon icon="lucide:plus"></iconify-icon> Tambah Program
            </button>
          </div>
        </div>
        <div class="mb-8">
          <input id="searchInput" type="search" placeholder="Cari program berdasarkan nama, deskripsi, atau urutan..." oninput="filterAdminTable(this)" data-filter-selector="#programGridView > div, #programTableView tbody tr" class="w-full md:w-1/2 pl-10 pr-4 py-3 bg-white border border-[#E8E4D9] rounded-lg focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all text-sm" />
        </div>
        
        <!-- Program List -->
        <?php if (empty($programs)): ?>
          <div id="programGridView" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="col-span-full text-center py-12 bg-white rounded-lg border border-[#E8E4D9]">
              <iconify-icon icon="lucide:book-open" class="text-4xl text-[#9FB5A5] mb-4"></iconify-icon>
              <p class="text-[#5F6F65]">Belum ada program unggulan.</p>
            </div>
          </div>
          <div id="programTableView" class="hidden">
            <div class="bg-white rounded-xl border border-[#E8E4D9] shadow-sm overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-[#3E6B4E] text-white">
                    <tr>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">No</th>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Foto</th>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Nama</th>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Deskripsi</th>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Urutan</th>
                      <th class="text-center px-4 py-4 text-xs font-bold uppercase tracking-wider">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="bg-white">
                      <td class="px-4 py-4" colspan="6">
                        <div class="text-center py-12 text-[#5F6F65]">Belum ada program unggulan.</div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div id="programGridView" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($programs as $program): ?>
              <div class="bg-white rounded-lg border border-[#E8E4D9] shadow-sm overflow-hidden">
                <img src="<?php echo htmlspecialchars($program['gambar'] ?? 'https://picsum.photos/seed/program/500/300'); ?>" alt="<?php echo htmlspecialchars($program['nama']); ?>" class="w-full h-48 object-cover">
                <div class="p-6">
                  <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-lg text-[#1F2D26]"><?php echo htmlspecialchars($program['nama']); ?></h3>
                    <div class="flex items-center gap-2">
                      <button onclick="openEditModal(this)" data-program="<?php echo base64_encode(json_encode([ 'id' => $program['id'], 'icon' => $program['icon'] ?? '', 'nama' => $program['nama'], 'deskripsi' => $program['deskripsi'], 'gambar' => $program['gambar'], 'urutan' => $program['urutan'] ?? 0 ])); ?>" class="text-blue-500 hover:text-blue-700 transition-colors">
                        <iconify-icon icon="lucide:edit"></iconify-icon>
                      </button>
                      <a href="?delete=<?php echo $program['id']; ?>" onclick="return confirm('Yakin ingin menghapus program ini?')" class="text-red-500 hover:text-red-700 transition-colors">
                        <iconify-icon icon="lucide:trash-2"></iconify-icon>
                      </a>
                    </div>
                  </div>
                  <p class="text-sm text-[#5F6F65] font-light line-clamp-3"><?php echo htmlspecialchars($program['deskripsi']); ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div id="programTableView" class="hidden">
            <div class="bg-white rounded-xl border border-[#E8E4D9] shadow-sm overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-[#3E6B4E] text-white">
                    <tr>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">No</th>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Foto</th>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Nama</th>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Deskripsi</th>
                      <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-wider">Urutan</th>
                      <th class="text-center px-4 py-4 text-xs font-bold uppercase tracking-wider">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-[#E8E4D9]">
                    <?php $program_no = 1; foreach ($programs as $program): ?>
                      <tr class="hover:bg-[#F9F8F4] transition-all duration-200">
                        <td class="px-4 py-4 text-sm text-[#5F6F65] font-medium"><?php echo $program_no++; ?></td>
                        <td class="px-4 py-4">
                          <img src="<?php echo htmlspecialchars($program['gambar'] ?? 'https://picsum.photos/seed/program/120/90'); ?>" alt="<?php echo htmlspecialchars($program['nama']); ?>" class="w-16 h-12 object-cover rounded-lg border border-[#E8E4D9]">
                        </td>
                        <td class="px-4 py-4 text-sm text-[#1F2D26] font-semibold"><?php echo htmlspecialchars($program['nama']); ?></td>
                        <td class="px-4 py-4 text-sm text-[#5F6F65] line-clamp-2"><?php echo htmlspecialchars($program['deskripsi']); ?></td>
                        <td class="px-4 py-4 text-sm text-[#5F6F65]"><?php echo htmlspecialchars($program['urutan']); ?></td>
                        <td class="px-4 py-4 text-center">
                          <div class="flex items-center justify-center gap-2">
                            <button onclick="openEditModal(this)" data-program="<?php echo base64_encode(json_encode([ 'id' => $program['id'], 'icon' => $program['icon'] ?? '', 'nama' => $program['nama'], 'deskripsi' => $program['deskripsi'], 'gambar' => $program['gambar'], 'urutan' => $program['urutan'] ?? 0 ])); ?>" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors">
                              <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                            </button>
                            <a href="?delete=<?php echo $program['id']; ?>" onclick="return confirm('Yakin ingin menghapus program ini?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
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
    </div>
  </main>

  <!-- Modal Tambah Program -->
  <div id="modalProgram" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('modalProgram').classList.add('hidden')"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-lg w-full overflow-hidden">
        <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between bg-[#3E6B4E]">
          <h3 class="font-semibold text-lg text-white">Tambah Program Baru</h3>
          <button onclick="document.getElementById('modalProgram').classList.add('hidden')" class="text-white/70 hover:text-white">
            <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
          </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
          <input type="hidden" name="tambah_program" value="1">
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Icon (Lucide Icon Name)</label>
            <div class="flex gap-2 mb-2">
              <input type="text" name="icon" id="add_icon_input" oninput="updateIconPreview('add_icon_input', 'add_icon_preview')" placeholder="Contoh: lucide:globe" class="flex-1 px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
              <div class="w-12 h-12 bg-[#F9F8F4] border border-[#E8E4D9] rounded flex items-center justify-center text-2xl text-[#3E6B4E] flex-shrink-0">
                <iconify-icon id="add_icon_preview" icon="lucide:book-open"></iconify-icon>
              </div>
            </div>
            <div class="p-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg">
              <span class="block text-[10px] font-bold text-[#9FB5A5] uppercase mb-2">Pilih Ikon Cepat:</span>
              <div class="grid grid-cols-6 gap-2">
                <button type="button" onclick="selectIcon('add_icon_input', 'add_icon_preview', 'lucide:book-open')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Pendidikan">
                  <iconify-icon icon="lucide:book-open" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('add_icon_input', 'add_icon_preview', 'lucide:award')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Prestasi">
                  <iconify-icon icon="lucide:award" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('add_icon_input', 'add_icon_preview', 'lucide:calendar')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Jadwal">
                  <iconify-icon icon="lucide:calendar" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('add_icon_input', 'add_icon_preview', 'lucide:graduation-cap')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Kelulusan">
                  <iconify-icon icon="lucide:graduation-cap" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('add_icon_input', 'add_icon_preview', 'lucide:music')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Seni/Musik">
                  <iconify-icon icon="lucide:music" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('add_icon_input', 'add_icon_preview', 'lucide:activity')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Olahraga">
                  <iconify-icon icon="lucide:activity" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('add_icon_input', 'add_icon_preview', 'lucide:users')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Siswa/Guru">
                  <iconify-icon icon="lucide:users" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('add_icon_input', 'add_icon_preview', 'lucide:home')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Kelas">
                  <iconify-icon icon="lucide:home" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('add_icon_input', 'add_icon_preview', 'lucide:heart')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Karakter">
                  <iconify-icon icon="lucide:heart" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('add_icon_input', 'add_icon_preview', 'lucide:wrench')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Vokasi">
                  <iconify-icon icon="lucide:wrench" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('add_icon_input', 'add_icon_preview', 'lucide:briefcase')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Karir">
                  <iconify-icon icon="lucide:briefcase" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('add_icon_input', 'add_icon_preview', 'lucide:globe')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Dunia/Akademik">
                  <iconify-icon icon="lucide:globe" class="text-lg"></iconify-icon>
                </button>
              </div>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Nama Program</label>
            <input type="text" name="nama" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Deskripsi Program</label>
            <textarea name="deskripsi" rows="4" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm resize-none"></textarea>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Gambar Program (Pilih File)</label>
            <input type="file" name="gambar_file" accept="image/*" onchange="previewGambar(event, 'gambarPreview')" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
            <div id="gambarPreview" class="mt-3">
              <p class="text-xs text-[#9FB5A5] italic">Preview gambar akan muncul di sini</p>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Urutan</label>
            <input type="number" name="urutan" value="0" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
          </div>
          <div class="flex items-center gap-4 pt-4">
            <button type="button" onclick="document.getElementById('modalProgram').classList.add('hidden')" class="flex-1 px-6 py-3 rounded border border-[#E8E4D9] text-[#5F6F65] hover:bg-[#F9F8F4] transition-colors text-sm">Batal</button>
            <button type="submit" name="tambah_program" class="flex-1 bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest">Simpan Program</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Edit Program -->
  <div id="modalEditProgram" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('modalEditProgram').classList.add('hidden')"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-lg w-full overflow-hidden">
        <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between bg-[#3E6B4E]">
          <h3 class="font-semibold text-lg text-white">Edit Program</h3>
          <button onclick="document.getElementById('modalEditProgram').classList.add('hidden')" class="text-white/70 hover:text-white">
            <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
          </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
          <input type="hidden" name="edit_program" value="1">
          <input type="hidden" name="program_id" id="edit_program_id" value="">
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Icon (Lucide Icon Name)</label>
            <div class="flex gap-2 mb-2">
              <input type="text" name="edit_icon" id="edit_icon" oninput="updateIconPreview('edit_icon', 'edit_icon_preview')" placeholder="Contoh: lucide:globe" class="flex-1 px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
              <div class="w-12 h-12 bg-[#F9F8F4] border border-[#E8E4D9] rounded flex items-center justify-center text-2xl text-[#3E6B4E] flex-shrink-0">
                <iconify-icon id="edit_icon_preview" icon="lucide:book-open"></iconify-icon>
              </div>
            </div>
            <div class="p-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded-lg">
              <span class="block text-[10px] font-bold text-[#9FB5A5] uppercase mb-2">Pilih Ikon Cepat:</span>
              <div class="grid grid-cols-6 gap-2">
                <button type="button" onclick="selectIcon('edit_icon', 'edit_icon_preview', 'lucide:book-open')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Pendidikan">
                  <iconify-icon icon="lucide:book-open" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('edit_icon', 'edit_icon_preview', 'lucide:award')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Prestasi">
                  <iconify-icon icon="lucide:award" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('edit_icon', 'edit_icon_preview', 'lucide:calendar')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Jadwal">
                  <iconify-icon icon="lucide:calendar" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('edit_icon', 'edit_icon_preview', 'lucide:graduation-cap')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Kelulusan">
                  <iconify-icon icon="lucide:graduation-cap" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('edit_icon', 'edit_icon_preview', 'lucide:music')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Seni/Musik">
                  <iconify-icon icon="lucide:music" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('edit_icon', 'edit_icon_preview', 'lucide:activity')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Olahraga">
                  <iconify-icon icon="lucide:activity" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('edit_icon', 'edit_icon_preview', 'lucide:users')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Siswa/Guru">
                  <iconify-icon icon="lucide:users" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('edit_icon', 'edit_icon_preview', 'lucide:home')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Kelas">
                  <iconify-icon icon="lucide:home" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('edit_icon', 'edit_icon_preview', 'lucide:heart')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Karakter">
                  <iconify-icon icon="lucide:heart" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('edit_icon', 'edit_icon_preview', 'lucide:wrench')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Vokasi">
                  <iconify-icon icon="lucide:wrench" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('edit_icon', 'edit_icon_preview', 'lucide:briefcase')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Karir">
                  <iconify-icon icon="lucide:briefcase" class="text-lg"></iconify-icon>
                </button>
                <button type="button" onclick="selectIcon('edit_icon', 'edit_icon_preview', 'lucide:globe')" class="p-2 bg-white rounded border border-[#E8E4D9] hover:bg-[#3E6B4E] hover:text-white hover:border-[#3E6B4E] flex items-center justify-center transition-colors" title="Dunia/Akademik">
                  <iconify-icon icon="lucide:globe" class="text-lg"></iconify-icon>
                </button>
              </div>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Nama Program</label>
            <input type="text" name="edit_nama" id="edit_nama" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Deskripsi Program</label>
            <textarea name="edit_deskripsi" id="edit_deskripsi" rows="4" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm resize-none"></textarea>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Gambar Program (Pilih File, biarkan kosong untuk tidak mengedit)</label>
            <input type="file" name="edit_gambar_file" accept="image/*" onchange="previewGambar(event, 'editGambarPreview')" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
            <div id="editGambarPreview" class="mt-3">
              <p class="text-xs text-[#9FB5A5] italic">Preview gambar akan muncul di sini</p>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Urutan</label>
            <input type="number" name="edit_urutan" id="edit_urutan" value="0" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
          </div>
          <div class="flex items-center gap-4 pt-4">
            <button type="button" onclick="document.getElementById('modalEditProgram').classList.add('hidden')" class="flex-1 px-6 py-3 rounded border border-[#E8E4D9] text-[#5F6F65] hover:bg-[#F9F8F4] transition-colors text-sm">Batal</button>
            <button type="submit" name="edit_program" class="flex-1 bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function selectIcon(inputId, previewId, iconName) {
      document.getElementById(inputId).value = iconName;
      updateIconPreview(inputId, previewId);
    }

    function updateIconPreview(inputId, previewId) {
      const inputVal = document.getElementById(inputId).value;
      const previewEl = document.getElementById(previewId);
      if (previewEl) {
        previewEl.setAttribute('icon', inputVal || 'lucide:book-open');
      }
    }

    function previewGambar(event, previewId) {
      const previewDiv = document.getElementById(previewId);
      const file = event.target.files[0];
      
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          previewDiv.innerHTML = `
            <img src="${e.target.result}" alt="Preview" class="w-full h-40 object-cover rounded border border-[#E8E4D9]">
          `;
        };
        reader.readAsDataURL(file);
      } else {
        previewDiv.innerHTML = `
          <p class="text-xs text-[#9FB5A5] italic">Preview gambar akan muncul di sini</p>
        `;
      }
    }

    function openAddModal() {
      document.getElementById('modalProgram').classList.remove('hidden');
    }

    function openEditModal(button) {
      const raw = button.dataset.program;
      let program = {
        id: '',
        icon: '',
        nama: '',
        deskripsi: '',
        gambar: '',
        urutan: 0
      };
      try {
        program = JSON.parse(atob(raw));
      } catch (err) {
        console.error('Invalid program data', err, raw);
      }

      document.getElementById('edit_program_id').value = program.id;
      document.getElementById('edit_icon').value = program.icon;
      updateIconPreview('edit_icon', 'edit_icon_preview');
      document.getElementById('edit_nama').value = program.nama;
      document.getElementById('edit_deskripsi').value = program.deskripsi;
      document.getElementById('edit_urutan').value = program.urutan;
      document.getElementById('editGambarPreview').innerHTML = `
        <img src="${program.gambar}" alt="Current Image" class="w-full h-40 object-cover rounded border border-[#E8E4D9]">
      `;
      document.getElementById('modalEditProgram').classList.remove('hidden');
    }

    const programGridViewBtn = document.getElementById('programGridViewBtn');
    const programTableViewBtn = document.getElementById('programTableViewBtn');
    const programGridView = document.getElementById('programGridView');
    const programTableView = document.getElementById('programTableView');

    function setProgramView(view) {
      if (!programGridView || !programTableView || !programGridViewBtn || !programTableViewBtn) return;
      if (view === 'grid') {
        programGridView.classList.remove('hidden');
        programTableView.classList.add('hidden');
        programGridViewBtn.classList.add('bg-[#3E6B4E]', 'text-white');
        programGridViewBtn.classList.remove('text-[#5F6F65]', 'hover:bg-[#F9F8F4]');
        programTableViewBtn.classList.remove('bg-[#3E6B4E]', 'text-white');
        programTableViewBtn.classList.add('text-[#5F6F65]', 'hover:bg-[#F9F8F4]');
      } else {
        programGridView.classList.add('hidden');
        programTableView.classList.remove('hidden');
        programTableViewBtn.classList.add('bg-[#3E6B4E]', 'text-white');
        programTableViewBtn.classList.remove('text-[#5F6F65]', 'hover:bg-[#F9F8F4]');
        programGridViewBtn.classList.remove('bg-[#3E6B4E]', 'text-white');
        programGridViewBtn.classList.add('text-[#5F6F65]', 'hover:bg-[#F9F8F4]');
      }
      localStorage.setItem('programView', view);
    }

    if (programGridViewBtn && programTableViewBtn) {
      programGridViewBtn.addEventListener('click', function() {
        setProgramView('grid');
      });
      programTableViewBtn.addEventListener('click', function() {
        setProgramView('table');
      });
    }

    document.addEventListener('DOMContentLoaded', function() {
      const savedProgramView = localStorage.getItem('programView') || 'grid';
      setProgramView(savedProgramView);
    });
  </script>

</body>
</html>
