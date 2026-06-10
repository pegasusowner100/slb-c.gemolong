<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_login();

$title = "Data Pendaftar PPDB — SMA Negeri 1 Nusantara";
$page_title = "Data PPDB";

// Dummy data PPDB
$ppdb_list = [
    [
        'no_reg' => 'PPDB-2025-001',
        'nama_lengkap' => 'Budi Santoso',
        'sekolah_asal' => 'SMP Negeri 1 Nusantara',
        'status' => 'Menunggu'
    ],
    [
        'no_reg' => 'PPDB-2025-002',
        'nama_lengkap' => 'Siti Aminah',
        'sekolah_asal' => 'SMP Negeri 2 Nusantara',
        'status' => 'Diterima'
    ],
    [
        'no_reg' => 'PPDB-2025-003',
        'nama_lengkap' => 'Andi Pratama',
        'sekolah_asal' => 'SMP Swasta Harapan',
        'status' => 'Menunggu'
    ],
    [
        'no_reg' => 'PPDB-2025-004',
        'nama_lengkap' => 'Dewi Lestari',
        'sekolah_asal' => 'SMP Negeri 3 Nusantara',
        'status' => 'Ditolak'
    ]
];

include 'components/head.php';
include 'components/sidebar.php';
?>

  <!-- Main Content -->
  <main class="flex-1 flex flex-col overflow-hidden">
    <?php include 'components/header.php'; ?>

    <div class="flex-1 overflow-y-auto p-8">
      <div class="flex items-center justify-between mb-8">
        <h3 class="text-xl font-semibold text-[#1F2D26]">Pendaftar Siswa Baru 2025/2026</h3>
        <button class="bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest flex items-center gap-2">
          <iconify-icon icon="lucide:download"></iconify-icon>
          Export Excel (CSV)
        </button>
      </div>

      <div class="bg-white rounded-lg border border-[#E8E4D9] shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#F9F8F4] border-b border-[#E8E4D9]">
              <th class="px-6 py-4 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">No. Reg</th>
              <th class="px-6 py-4 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">Nama Lengkap</th>
              <th class="px-6 py-4 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">Sekolah Asal</th>
              <th class="px-6 py-4 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">Status</th>
              <th class="px-6 py-4 text-xs font-bold text-[#9FB5A5] uppercase tracking-widest">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E8E4D9]">
            <?php foreach ($ppdb_list as $ppdb): ?>
            <tr>
              <td class="px-6 py-4 text-xs font-mono text-[#5F6F65]"><?php echo $ppdb['no_reg']; ?></td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-[#1F2D26]"><?php echo $ppdb['nama_lengkap']; ?></div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-[#5F6F65]"><?php echo $ppdb['sekolah_asal']; ?></div>
              </td>
              <td class="px-6 py-4">
                <?php
                $status_class = match($ppdb['status']) {
                    'Diterima' => 'bg-green-100 text-green-600',
                    'Ditolak' => 'bg-red-100 text-red-600',
                    default => 'bg-yellow-100 text-yellow-600'
                };
                ?>
                <span class="px-2 py-1 <?php echo $status_class; ?> text-[10px] font-bold uppercase rounded"><?php echo $ppdb['status']; ?></span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <button class="p-2 text-[#5F6F65] hover:text-[#3E6B4E] transition-colors" title="Detail"><iconify-icon icon="lucide:eye"></iconify-icon></button>
                  <button class="p-2 text-[#5F6F65] hover:text-green-600 transition-colors" title="Verifikasi"><iconify-icon icon="lucide:check-circle"></iconify-icon></button>
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
