
<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<?php if (!isset($profilSekolah)) { require_once __DIR__ . '/../../includes/db.php'; }
$sidebarLogo = !empty($profilSekolah['logo_url']) ? $profilSekolah['logo_url'] : BASE_URL . '/assets/images/JATENG JR.jpg';
?>
<aside class="w-64 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-700 text-white flex-shrink-0 hidden md:flex flex-col shadow-xl">
  <div class="p-6 border-b border-white/20">
    <div class="flex items-center gap-3">
      <img src="<?php echo htmlspecialchars($sidebarLogo); ?>" alt="Logo Sekolah" class="w-12 h-12 rounded-full object-cover ring-2 ring-white/40" onerror="this.src='https://picsum.photos/seed/logo/100/100'">
      <span class="font-semibold tracking-tight text-sm">RUANG ADMIN</span>
    </div>
  </div>
  <nav class="flex-1 p-4 space-y-2">
    <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'dashboard.php' ? 'bg-slate-700' : 'hover:bg-white/5 text-white/70 hover:text-white'; ?> rounded text-sm transition-colors">
      <iconify-icon icon="lucide:layout-dashboard"></iconify-icon> Dashboard
    </a>
    <a href="edit-beranda.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'edit-beranda.php' ? 'bg-slate-700' : 'hover:bg-white/5 text-white/70 hover:text-white'; ?> rounded text-sm transition-colors">
      <iconify-icon icon="lucide:home"></iconify-icon> Edit Beranda
    </a>
    <a href="edit-profil.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'edit-profil.php' ? 'bg-slate-700' : 'hover:bg-white/5 text-white/70 hover:text-white'; ?> rounded text-sm transition-colors">
      <iconify-icon icon="lucide:user"></iconify-icon> Edit Profil
    </a>
    <a href="kelola-program.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'kelola-program.php' ? 'bg-slate-700' : 'hover:bg-white/5 text-white/70 hover:text-white'; ?> rounded text-sm transition-colors">
      <iconify-icon icon="lucide:book-open"></iconify-icon> Kelola Program
    </a>
    <a href="kelola-fasilitas.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'kelola-fasilitas.php' ? 'bg-slate-700' : 'hover:bg-white/5 text-white/70 hover:text-white'; ?> rounded text-sm transition-colors">
      <iconify-icon icon="lucide:building-2"></iconify-icon> Kelola Fasilitas
    </a>
    <a href="kelola-guru.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'kelola-guru.php' ? 'bg-slate-700' : 'hover:bg-white/5 text-white/70 hover:text-white'; ?> rounded text-sm transition-colors">
      <iconify-icon icon="lucide:users"></iconify-icon> Kelola Guru
    </a>
    <a href="kelola-prestasi.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'kelola-prestasi.php' ? 'bg-slate-700' : 'hover:bg-white/5 text-white/70 hover:text-white'; ?> rounded text-sm transition-colors">
      <iconify-icon icon="lucide:award"></iconify-icon> Kelola Prestasi
    </a>
    <a href="kelola-siswa.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'kelola-siswa.php' ? 'bg-slate-700' : 'hover:bg-white/5 text-white/70 hover:text-white'; ?> rounded text-sm transition-colors">
      <iconify-icon icon="lucide:users-2"></iconify-icon> Kelola Siswa
    </a>
    <a href="kelola-berita.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'kelola-berita.php' ? 'bg-slate-700' : 'hover:bg-white/5 text-white/70 hover:text-white'; ?> rounded text-sm transition-colors">
      <iconify-icon icon="lucide:newspaper"></iconify-icon> Kelola Berita
    </a>
    <a href="kelola-galeri.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'kelola-galeri.php' ? 'bg-slate-700' : 'hover:bg-white/5 text-white/70 hover:text-white'; ?> rounded text-sm transition-colors">
      <iconify-icon icon="lucide:images"></iconify-icon> Kelola Galeri
    </a>
    <a href="kelola-download.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'kelola-download.php' ? 'bg-slate-700' : 'hover:bg-white/5 text-white/70 hover:text-white'; ?> rounded text-sm transition-colors">
      <iconify-icon icon="lucide:download"></iconify-icon> Kelola Download
    </a>
    <a href="kelola-pengumuman.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'kelola-pengumuman.php' ? 'bg-slate-700' : 'hover:bg-white/5 text-white/70 hover:text-white'; ?> rounded text-sm transition-colors">
      <iconify-icon icon="lucide:file-text"></iconify-icon> Kelola Pengumuman
    </a>
    <a href="kelola-anggaran.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'kelola-anggaran.php' ? 'bg-slate-700' : 'hover:bg-white/5 text-white/70 hover:text-white'; ?> rounded text-sm transition-colors">
      <iconify-icon icon="lucide:wallet"></iconify-icon> Kelola Anggaran & Belanja
    </a>
    <a href="kelola-faq.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'kelola-faq.php' ? 'bg-slate-700' : 'hover:bg-white/5 text-white/70 hover:text-white'; ?> rounded text-sm transition-colors">
      <iconify-icon icon="lucide:help-circle"></iconify-icon> Kelola FAQ
    </a>
    <a href="edit-kontak.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'edit-kontak.php' ? 'bg-slate-700' : 'hover:bg-white/5 text-white/70 hover:text-white'; ?> rounded text-sm transition-colors">
      <iconify-icon icon="lucide:phone"></iconify-icon> Edit Kontak
    </a>
    <a href="kelola-ppdb.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'kelola-ppdb.php' ? 'bg-slate-700' : 'hover:bg-white/5 text-white/70 hover:text-white'; ?> rounded text-sm transition-colors">
      <iconify-icon icon="lucide:clipboard-list"></iconify-icon> Kelola PPDB
    </a>
    <a href="kelola-surat.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'kelola-surat.php' ? 'bg-slate-700' : 'hover:bg-white/5 text-white/70 hover:text-white'; ?> rounded text-sm transition-colors">
      <iconify-icon icon="lucide:mail"></iconify-icon> Kelola Surat
    </a>
  </nav>
  <div class="p-4 border-t border-white/10">
    <a href="logout.php" class="flex items-center gap-3 px-4 py-3 hover:bg-red-500/10 rounded text-sm transition-colors text-red-400">
      <iconify-icon icon="lucide:log-out"></iconify-icon> Logout
    </a>
  </div>
</aside>
