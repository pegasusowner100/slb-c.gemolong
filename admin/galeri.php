<?php
define('ADMIN_PAGE', true);
require_once '../includes/session.php';
require_once '../includes/db.php';
require_login();

$title = "Kelola Galeri — SLB BC KARYA SEJAHTERA";
$page_title = "Galeri Foto";
include 'components/head.php';
include 'components/sidebar.php';
?>

  <!-- Main Content -->
  <main class="flex-1 flex flex-col overflow-hidden">
    <?php include 'components/header.php'; ?>

    <div class="flex-1 overflow-y-auto p-8">
      <div class="flex items-center justify-between mb-8">
        <h3 class="text-xl font-semibold text-[#1F2D26]">Koleksi Foto</h3>
        <button class="bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest flex items-center gap-2">
          <iconify-icon icon="lucide:upload"></iconify-icon>
          Upload Foto Baru
        </button>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <?php for($i=1; $i<=8; $i++): ?>
        <div class="group relative aspect-square bg-white rounded-lg border border-[#E8E4D9] shadow-sm overflow-hidden">
          <img src="https://picsum.photos/seed/gallery<?php echo $i; ?>/400/400.jpg" alt="Gallery" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
          <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-4">
            <button class="w-10 h-10 bg-[#F2E8DA] rounded-full flex items-center justify-center text-[#1F2D26] hover:bg-[#3E6B4E] hover:text-white transition-colors">
              <iconify-icon icon="lucide:edit-2"></iconify-icon>
            </button>
            <button class="w-10 h-10 bg-[#F2E8DA] rounded-full flex items-center justify-center text-red-500 hover:bg-red-500 hover:text-white transition-colors">
              <iconify-icon icon="lucide:trash-2"></iconify-icon>
            </button>
          </div>
        </div>
        <?php endfor; ?>
      </div>
    </div>
  </main>
</body>
</html>
