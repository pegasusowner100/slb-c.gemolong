<header class="h-16 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 border-b border-slate-700 flex items-center justify-between px-8 text-white shadow-sm">
  <div class="flex items-center gap-4">
    <a href="dashboard.php" class="inline-flex items-center gap-3 text-white/90 hover:text-white transition-colors duration-150">
      <iconify-icon icon="lucide:arrow-left" class="w-4 h-4"></iconify-icon>
      <h2 class="font-semibold text-white"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h2>
    </a>
  </div>
  <div class="flex items-center gap-4">
    <a href="<?= BASE_URL ?>/index.php" class="text-xs text-white/80 hover:text-white/100 hover:underline transition-colors duration-150">Lihat Website</a>
    <span class="text-xs text-white/90">Selamat datang, Admin</span>
    <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center ring-1 ring-white/30">
      <iconify-icon icon="lucide:user" class="text-white"></iconify-icon>
    </div>
  </div>
</header>
