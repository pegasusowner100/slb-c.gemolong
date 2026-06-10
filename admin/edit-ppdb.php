
<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_login();

// detect AJAX requests
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

$title = "Edit PPDB — SLB-C YPSLB Gemolong";
$page_title = "Edit PPDB";
$success = '';
$error = '';
$pendaftar = null;

// Get ID from URL
$id = $_GET['id'] ?? '';
if (empty($id)) {
    header('Location: kelola-ppdb.php');
    exit;
}

// Fetch pendaftar data
if ($supabaseConnected) {
    $result = supabaseSelect('ppdb', ['id' => "eq.$id"]);
    if ($result['success'] && !empty($result['data'])) {
        $pendaftar = $result['data'][0];
    } else {
        $error = 'Data pendaftar tidak ditemukan!';
    }
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($error) === false) {
    $data = [
        'nama_lengkap' => trim($_POST['nama_lengkap'] ?? ''),
        'nisn' => trim($_POST['nisn'] ?? '') ?: null,
        'tempat_lahir' => trim($_POST['tempat_lahir'] ?? '') ?: null,
        'tanggal_lahir' => trim($_POST['tanggal_lahir'] ?? '') ?: null,
        'jenis_kelamin' => trim($_POST['jenis_kelamin'] ?? ''),
        'agama' => trim($_POST['agama'] ?? '') ?: null,
        'alamat' => trim($_POST['alamat'] ?? ''),
        'sekolah_asal' => trim($_POST['sekolah_asal'] ?? ''),
        'tahun_lulusan' => trim($_POST['tahun_lulusan'] ?? '') ?: null,
        'nama_ayah' => trim($_POST['nama_ayah'] ?? ''),
        'pekerjaan_ayah' => trim($_POST['pekerjaan_ayah'] ?? '') ?: null,
        'nama_ibu' => trim($_POST['nama_ibu'] ?? ''),
        'pekerjaan_ibu' => trim($_POST['pekerjaan_ibu'] ?? '') ?: null,
        'no_hp_ortu' => trim($_POST['no_hp_ortu'] ?? ''),
        'status' => trim($_POST['status'] ?? 'pending')
    ];

    // Validasi
    if (empty($data['nama_lengkap'])) {
        $error = 'Nama lengkap harus diisi!';
    } elseif (empty($data['jenis_kelamin'])) {
        $error = 'Jenis kelamin harus dipilih!';
    } elseif (empty($data['alamat'])) {
        $error = 'Alamat harus diisi!';
    } elseif (empty($data['sekolah_asal'])) {
        $error = 'Sekolah asal harus diisi!';
    } elseif (empty($data['nama_ayah'])) {
        $error = 'Nama ayah harus diisi!';
    } elseif (empty($data['nama_ibu'])) {
        $error = 'Nama ibu harus diisi!';
    } elseif (empty($data['no_hp_ortu'])) {
        $error = 'Nomor HP orang tua harus diisi!';
    } else {
        $result = supabaseUpdate('ppdb', $data, $id);
        if ($result['success']) {
          $success = 'Data pendaftar berhasil diperbarui!';
          // Refresh data
          $getResult = supabaseSelect('ppdb', ['id' => "eq.$id"]);
          if ($getResult['success'] && !empty($getResult['data'])) {
            $pendaftar = $getResult['data'][0];
          }
          if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $success, 'data' => $pendaftar]);
            exit;
          }
        } else {
          $error = 'Gagal memperbarui data!';
          if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $error]);
            exit;
          }
        }
    }
}

include 'components/head.php';
include 'components/sidebar.php';
?>

  <!-- Main Content -->
  <main class="flex-1 flex flex-col overflow-hidden">
    <?php include 'components/header.php'; ?>

    <div class="flex-1 overflow-y-auto p-8">
      <div class="max-w-4xl">
        <div class="mb-8 flex items-center gap-4">
          <a href="kelola-ppdb.php" class="text-[#9FB5A5] hover:text-[#3E6B4E]">
            <iconify-icon icon="lucide:arrow-left" class="w-6 h-6"></iconify-icon>
          </a>
          <h2 class="text-2xl font-semibold text-[#1F2D26]">Edit Data Pendaftar</h2>
        </div>

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

        <?php if ($pendaftar): ?>
          <form method="POST" class="ajax-ppdb bg-white rounded-lg border border-[#E8E4D9] shadow-sm p-8 space-y-6">
            <!-- No. Registrasi (Read Only) -->
            <div>
              <label class="block text-sm font-semibold text-[#1F2D26] mb-2">No. Registrasi (Tidak dapat diubah)</label>
              <input type="text" value="<?php echo htmlspecialchars($pendaftar['no_reg'] ?? '-'); ?>" readonly
                     class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded text-[#5F6F65]">
            </div>

            <!-- Data Pribadi -->
            <div class="border-t pt-6">
              <h3 class="font-bold text-lg text-[#1F2D26] mb-4">Data Pribadi</h3>
              <div class="grid md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-semibold text-[#1F2D26] mb-2">Nama Lengkap *</label>
                  <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($pendaftar['nama_lengkap'] ?? ''); ?>" required
                         class="w-full px-4 py-2 border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E]">
                </div>
                <div>
                  <label class="block text-sm font-semibold text-[#1F2D26] mb-2">NISN</label>
                  <input type="text" name="nisn" value="<?php echo htmlspecialchars($pendaftar['nisn'] ?? ''); ?>"
                         class="w-full px-4 py-2 border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E]">
                </div>
                <div>
                  <label class="block text-sm font-semibold text-[#1F2D26] mb-2">Jenis Kelamin *</label>
                  <select name="jenis_kelamin" required
                          class="w-full px-4 py-2 border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E]">
                    <option value="">-- Pilih --</option>
                    <option value="Laki-laki" <?php echo ($pendaftar['jenis_kelamin'] ?? '') === 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                    <option value="Perempuan" <?php echo ($pendaftar['jenis_kelamin'] ?? '') === 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-semibold text-[#1F2D26] mb-2">Tempat Lahir</label>
                  <input type="text" name="tempat_lahir" value="<?php echo htmlspecialchars($pendaftar['tempat_lahir'] ?? ''); ?>"
                         class="w-full px-4 py-2 border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E]">
                </div>
                <div>
                  <label class="block text-sm font-semibold text-[#1F2D26] mb-2">Tanggal Lahir</label>
                  <input type="date" name="tanggal_lahir" value="<?php echo htmlspecialchars($pendaftar['tanggal_lahir'] ?? ''); ?>"
                         class="w-full px-4 py-2 border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E]">
                </div>
                <div>
                  <label class="block text-sm font-semibold text-[#1F2D26] mb-2">Agama</label>
                  <select name="agama"
                          class="w-full px-4 py-2 border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E]">
                    <option value="">-- Pilih --</option>
                    <option value="Islam" <?php echo ($pendaftar['agama'] ?? '') === 'Islam' ? 'selected' : ''; ?>>Islam</option>
                    <option value="Kristen" <?php echo ($pendaftar['agama'] ?? '') === 'Kristen' ? 'selected' : ''; ?>>Kristen</option>
                    <option value="Katolik" <?php echo ($pendaftar['agama'] ?? '') === 'Katolik' ? 'selected' : ''; ?>>Katolik</option>
                    <option value="Hindu" <?php echo ($pendaftar['agama'] ?? '') === 'Hindu' ? 'selected' : ''; ?>>Hindu</option>
                    <option value="Buddha" <?php echo ($pendaftar['agama'] ?? '') === 'Buddha' ? 'selected' : ''; ?>>Buddha</option>
                    <option value="Konghucu" <?php echo ($pendaftar['agama'] ?? '') === 'Konghucu' ? 'selected' : ''; ?>>Konghucu</option>
                  </select>
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-semibold text-[#1F2D26] mb-2">Alamat *</label>
                  <textarea name="alamat" required rows="2"
                            class="w-full px-4 py-2 border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E]"><?php echo htmlspecialchars($pendaftar['alamat'] ?? ''); ?></textarea>
                </div>
              </div>
            </div>

            <!-- Data Pendidikan -->
            <div class="border-t pt-6">
              <h3 class="font-bold text-lg text-[#1F2D26] mb-4">Data Pendidikan</h3>
              <div class="grid md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                  <label class="block text-sm font-semibold text-[#1F2D26] mb-2">Sekolah Asal *</label>
                  <input type="text" name="sekolah_asal" value="<?php echo htmlspecialchars($pendaftar['sekolah_asal'] ?? ''); ?>" required
                         class="w-full px-4 py-2 border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E]">
                </div>
                <div>
                  <label class="block text-sm font-semibold text-[#1F2D26] mb-2">Tahun Lulusan</label>
                  <input type="text" name="tahun_lulusan" value="<?php echo htmlspecialchars($pendaftar['tahun_lulusan'] ?? ''); ?>" maxlength="4"
                         class="w-full px-4 py-2 border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E]">
                </div>
              </div>
            </div>

            <!-- Data Orang Tua -->
            <div class="border-t pt-6">
              <h3 class="font-bold text-lg text-[#1F2D26] mb-4">Data Orang Tua</h3>
              <div class="grid md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-semibold text-[#1F2D26] mb-2">Nama Ayah *</label>
                  <input type="text" name="nama_ayah" value="<?php echo htmlspecialchars($pendaftar['nama_ayah'] ?? ''); ?>" required
                         class="w-full px-4 py-2 border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E]">
                </div>
                <div>
                  <label class="block text-sm font-semibold text-[#1F2D26] mb-2">Pekerjaan Ayah</label>
                  <input type="text" name="pekerjaan_ayah" value="<?php echo htmlspecialchars($pendaftar['pekerjaan_ayah'] ?? ''); ?>"
                         class="w-full px-4 py-2 border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E]">
                </div>
                <div>
                  <label class="block text-sm font-semibold text-[#1F2D26] mb-2">Nama Ibu *</label>
                  <input type="text" name="nama_ibu" value="<?php echo htmlspecialchars($pendaftar['nama_ibu'] ?? ''); ?>" required
                         class="w-full px-4 py-2 border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E]">
                </div>
                <div>
                  <label class="block text-sm font-semibold text-[#1F2D26] mb-2">Pekerjaan Ibu</label>
                  <input type="text" name="pekerjaan_ibu" value="<?php echo htmlspecialchars($pendaftar['pekerjaan_ibu'] ?? ''); ?>"
                         class="w-full px-4 py-2 border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E]">
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-semibold text-[#1F2D26] mb-2">No HP Orang Tua *</label>
                  <input type="tel" name="no_hp_ortu" value="<?php echo htmlspecialchars($pendaftar['no_hp_ortu'] ?? ''); ?>" required
                         class="w-full px-4 py-2 border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E]">
                </div>
              </div>
            </div>

            <!-- Status -->
            <div class="border-t pt-6">
              <h3 class="font-bold text-lg text-[#1F2D26] mb-4">Status Pendaftaran</h3>
              <div class="max-w-xs">
                <select name="status"
                        class="w-full px-4 py-2 border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E]">
                  <option value="pending" <?php echo ($pendaftar['status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                  <option value="diterima" <?php echo ($pendaftar['status'] ?? '') === 'diterima' ? 'selected' : ''; ?>>Diterima</option>
                  <option value="ditolak" <?php echo ($pendaftar['status'] ?? '') === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                </select>
              </div>
            </div>

            <!-- Buttons -->
            <div class="border-t pt-6 flex gap-4 justify-end">
              <a href="kelola-ppdb.php" class="px-6 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition">Batal</a>
              <button type="button" onclick="(function(){ try{ if(window.parent && window.parent.closeModal) { window.parent.closeModal('modal-edit-ppdb'); return; } }catch(e){} if(typeof closeModal === 'function'){ closeModal('modal-edit-ppdb'); return; } if (window.opener) { window.close(); return; } history.back(); })();" class="px-6 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">Tutup</button>
              <button type="submit" class="px-6 py-2 bg-[#3E6B4E] text-white rounded hover:bg-[#2F5238] transition flex items-center gap-2">
                <iconify-icon icon="lucide:save"></iconify-icon> Simpan Perubahan
              </button>
            </div>
          </form>
        <?php else: ?>
          <div class="bg-white rounded-lg border border-[#E8E4D9] p-12 text-center">
            <iconify-icon icon="lucide:alert-circle" class="text-4xl text-red-500 mb-4"></iconify-icon>
            <p class="text-[#5F6F65]">Data pendaftar tidak ditemukan.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>

</body>
</html>
