<?php
define('ADMIN_PAGE', true);
require_once '../includes/session.php';
require_once '../includes/db.php';
require_login();

$title = "Data Guru & Staff — SLB BC KARYA SEJAHTERA";
$page_title = "Guru & Staff";

// Data guru akan diambil dari database
$guru_list = [];

include 'components/head.php';
include 'components/sidebar.php';
?>

  <!-- Main Content -->
  <main class="flex-1 flex flex-col overflow-hidden">
    <?php include 'components/header.php'; ?>

    <div class="flex-1 overflow-y-auto p-8">
      <div class="flex items-center justify-between mb-8">
        <h3 class="text-xl font-semibold text-[#1F2D26]">Daftar Guru & Tendik</h3>
        <button class="bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest flex items-center gap-2">
          <iconify-icon icon="lucide:user-plus"></iconify-icon>
          Tambah Personel
        </button>
      </div>

      <div class="bg-white rounded-lg border border-[#E8E4D9] shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#F9F8F4] border-b border-[#E8E4D9]">
              <th class="px-6 py-4 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">Foto</th>
              <th class="px-6 py-4 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">Nama / NIP</th>
              <th class="px-6 py-4 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">Jabatan</th>
              <th class="px-6 py-4 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">Mata Pelajaran</th>
              <th class="px-6 py-4 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E8E4D9]">
            <?php foreach ($guru_list as $guru): ?>
            <tr>
              <td class="px-6 py-4">
                <img src="<?php echo $guru['foto']; ?>" alt="Guru" class="w-10 h-10 object-cover rounded-full">
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-[#1F2D26]"><?php echo $guru['nama']; ?></div>
                <div class="text-[10px] text-[#5F6F65]">NIP: <?php echo $guru['nip']; ?></div>
              </td>
              <td class="px-6 py-4">
                <span class="text-xs text-[#5F6F65]"><?php echo $guru['jabatan']; ?></span>
              </td>
              <td class="px-6 py-4">
                <span class="text-xs text-[#5F6F65]"><?php echo $guru['mapel']; ?></span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <button class="p-2 text-[#5F6F65] hover:text-[#3E6B4E] transition-colors"><iconify-icon icon="lucide:edit-2"></iconify-icon></button>
                  <button class="p-2 text-[#5F6F65] hover:text-red-500 transition-colors"><iconify-icon icon="lucide:trash-2"></iconify-icon></button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</body>
</html>
