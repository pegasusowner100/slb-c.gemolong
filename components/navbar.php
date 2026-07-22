<?php
if (!isset($hero) || empty($hero)) {
    if (!isset($supabaseConnected)) {
        require_once __DIR__ . '/../includes/db.php';
    }
    if ($supabaseConnected) {
        $heroResult = supabaseSelect('hero', ['id' => 'eq.1', 'limit' => 1]);
        if ($heroResult['success'] && !empty($heroResult['data'])) {
            $hero = array_merge($hero ?? [], $heroResult['data'][0]);
        }
    }
}
$mottoText = htmlspecialchars(trim($hero['motto'] ?? 'Mandiri berkarakter berdikari'));
?>
  <!-- ========== NAVBAR ========== -->
  <nav id="navbar" class="fixed top-0 left-0 right-0 z-50 h-20 transition-all duration-300 bg-brand-bg shadow-md border-b border-brand-border/50">
    <div class="w-full max-w-none px-6 lg:px-10 h-full flex items-center justify-between">
      <?php
        $navLogo = !empty($profilSekolah['logo_url']) ? $profilSekolah['logo_url'] : BASE_URL . '/assets/images/JATENG JR.jpg';
        $navSchoolName = mb_strtoupper(trim($profilSekolah['nama_sekolah'] ?? SITE_NAME), 'UTF-8');
      ?>
      <a href="<?= BASE_URL ?>/admin/index.php" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3">
        <img src="<?php echo htmlspecialchars($navLogo); ?>" alt="Logo Sekolah" class="w-16 h-16 rounded-full object-cover" onerror="this.src='https://picsum.photos/seed/logo/100/100'">
        <div>
          <span class="font-serif text-lg font-semibold tracking-tight logo-text-sweep block"><?php echo htmlspecialchars($navSchoolName); ?></span>
          <span class="block text-[10px] tracking-widest uppercase text-brand-muted -mt-0.5">
            <?php 
            $mottoWords = explode(' ', $mottoText);
            foreach ($mottoWords as $index => $word) {
                echo '<span class="inline-block animate-pop-in" style="animation-delay: ' . ($index * 0.15) . 's; opacity: 0; animation-fill-mode: forwards;">' . htmlspecialchars($word) . '</span> ';
            }
            ?>
          </span>
        </div>
      </a>
      <div class="hidden lg:flex items-center gap-8">
        <a href="<?= BASE_URL ?>/index.php" class="text-sm font-medium text-brand-muted hover:text-brand-accent transition-colors duration-150">Beranda</a>
        <div class="relative group">
          <button class="text-sm font-medium text-brand-muted hover:text-brand-accent transition-colors duration-150 flex items-center gap-1">
            Profil
            <iconify-icon icon="lucide:chevron-down" class="w-4 h-4"></iconify-icon>
          </button>
          <div class="absolute top-full left-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-brand-border/30 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
            <div class="py-2">
              <a href="<?= BASE_URL ?>/pages/profil.php#sambutan" class="block px-4 py-2 text-sm text-brand-muted hover:text-brand-accent hover:bg-brand-bg/50 transition-colors">Sambutan Kepala Sekolah</a>
              <a href="<?= BASE_URL ?>/pages/profil.php#dasar-hukum" class="block px-4 py-2 text-sm text-brand-muted hover:text-brand-accent hover:bg-brand-bg/50 transition-colors">Dasar Hukum</a>
              <a href="<?= BASE_URL ?>/pages/profil.php#sejarah" class="block px-4 py-2 text-sm text-brand-muted hover:text-brand-accent hover:bg-brand-bg/50 transition-colors">Sejarah Singkat</a>
              <a href="<?= BASE_URL ?>/pages/profil.php#visimisi" class="block px-4 py-2 text-sm text-brand-muted hover:text-brand-accent hover:bg-brand-bg/50 transition-colors">Visi Misi</a>
              <a href="<?= BASE_URL ?>/pages/profil.php#struktur" class="block px-4 py-2 text-sm text-brand-muted hover:text-brand-accent hover:bg-brand-bg/50 transition-colors">Struktur Organisasi</a>
              <a href="<?= BASE_URL ?>/pages/profil.php#sumberdaya" class="block px-4 py-2 text-sm text-brand-muted hover:text-brand-accent hover:bg-brand-bg/50 transition-colors">Sumber Daya Manusia</a>
              <a href="<?= BASE_URL ?>/pages/prestasi.php" class="block px-4 py-2 text-sm text-brand-muted hover:text-brand-accent hover:bg-brand-bg/50 transition-colors">Prestasi</a>
            </div>
          </div>
        </div>
        <div class="relative group">
          <button class="text-sm font-medium text-brand-muted hover:text-brand-accent transition-colors duration-150 flex items-center gap-1">
            Layanan Pendidikan
            <iconify-icon icon="lucide:chevron-down" class="w-4 h-4"></iconify-icon>
          </button>
          <div class="absolute top-full left-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-brand-border/30 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
            <div class="py-2">
              <a href="<?= BASE_URL ?>/pages/program.php" class="block px-4 py-2 text-sm text-brand-muted hover:text-brand-accent hover:bg-brand-bg/50 transition-colors">Program</a>
              <a href="<?= BASE_URL ?>/pages/fasilitas.php" class="block px-4 py-2 text-sm text-brand-muted hover:text-brand-accent hover:bg-brand-bg/50 transition-colors">Fasilitas</a>
              <a href="<?= BASE_URL ?>/pages/rencana-program.php" class="block px-4 py-2 text-sm text-brand-muted hover:text-brand-accent hover:bg-brand-bg/50 transition-colors">Rencana Program</a>
            </div>
          </div>
        </div>
        <a href="<?= BASE_URL ?>/pages/berita.php" class="text-sm font-medium text-brand-muted hover:text-brand-accent transition-colors duration-150">Berita</a>
        <a href="<?= BASE_URL ?>/pages/galeri.php" class="text-sm font-medium text-brand-muted hover:text-brand-accent transition-colors duration-150">Galeri</a>
        <div class="relative group">
          <button class="text-sm font-medium text-brand-muted hover:text-brand-accent transition-colors duration-150 flex items-center gap-1">
            Publikasi
            <iconify-icon icon="lucide:chevron-down" class="w-4 h-4"></iconify-icon>
          </button>
          <div class="absolute top-full left-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-brand-border/30 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
            <div class="py-2">
              <a href="<?= BASE_URL ?>/pages/pengumuman.php" class="block px-4 py-2 text-sm text-brand-muted hover:text-brand-accent hover:bg-brand-bg/50 transition-colors">Pengumuman</a>
              <a href="<?= BASE_URL ?>/pages/download.php" class="block px-4 py-2 text-sm text-brand-muted hover:text-brand-accent hover:bg-brand-bg/50 transition-colors">Download</a>
            </div>
          </div>
        </div>
        <a href="<?= BASE_URL ?>/pages/anggaran.php" class="text-sm font-medium text-brand-muted hover:text-brand-accent transition-colors duration-150">Anggaran & Belanja</a>
        <a href="<?= BASE_URL ?>/pages/layanan-online.php" class="text-sm font-medium text-brand-muted hover:text-brand-accent transition-colors duration-150">Layanan Online</a>
        <a href="<?= BASE_URL ?>/pages/statistik.php" class="text-sm font-medium text-brand-muted hover:text-brand-accent transition-colors duration-150">Statistik</a>
        <a href="<?= BASE_URL ?>/pages/faq.php" class="text-sm font-medium text-brand-muted hover:text-brand-accent transition-colors duration-150">FAQ</a>
        <a href="<?= BASE_URL ?>/pages/kontak.php" class="text-sm font-medium text-brand-muted hover:text-brand-accent transition-colors duration-150">Kontak</a>
        <a href="<?= BASE_URL ?>/pages/ppdb.php" class="bg-brand-accent hover:bg-brand-accent-hover text-white text-xs font-semibold tracking-widest uppercase px-6 py-3 rounded transition-colors duration-150">PPDB</a>
      </div>
      <button id="menuBtn" class="lg:hidden w-10 h-10 flex items-center justify-center rounded border border-brand-border hover:bg-brand-accent hover:text-white transition-colors duration-150">
        <iconify-icon icon="lucide:menu" class="text-xl" id="menuIcon"></iconify-icon>
      </button>
    </div>
    <div id="mobileMenu" class="hidden lg:hidden bg-brand-bg border-t border-brand-border shadow-lg">
      <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col gap-4">
        <a href="<?= BASE_URL ?>/index.php" class="mobile-link text-sm font-medium text-brand-muted hover:text-brand-accent py-2 border-b border-brand-border/50">Beranda</a>
        
        <!-- Profil Mobile Dropdown -->
        <div class="border-b border-brand-border/50">
          <button id="mobileProfilToggle" class="w-full text-left text-sm font-medium text-brand-muted hover:text-brand-accent py-2 flex items-center justify-between">
            Profil
            <iconify-icon icon="lucide:chevron-down" class="w-4 h-4 transition-transform duration-300" id="mobileProfilIcon"></iconify-icon>
          </button>
          <div id="mobileProfilMenu" class="hidden pl-4 pb-4 space-y-3">
            <a href="<?= BASE_URL ?>/pages/profil.php#sambutan" class="mobile-link text-sm text-brand-muted hover:text-brand-accent block py-1">Sambutan Kepala Sekolah</a>
            <a href="<?= BASE_URL ?>/pages/profil.php#dasar-hukum" class="mobile-link text-sm text-brand-muted hover:text-brand-accent block py-1">Dasar Hukum</a>
            <a href="<?= BASE_URL ?>/pages/profil.php#sejarah" class="mobile-link text-sm text-brand-muted hover:text-brand-accent block py-1">Sejarah Singkat</a>
            <a href="<?= BASE_URL ?>/pages/profil.php#visimisi" class="mobile-link text-sm text-brand-muted hover:text-brand-accent block py-1">Visi Misi</a>
            <a href="<?= BASE_URL ?>/pages/profil.php#struktur" class="mobile-link text-sm text-brand-muted hover:text-brand-accent block py-1">Struktur Organisasi</a>
            <a href="<?= BASE_URL ?>/pages/profil.php#sumberdaya" class="mobile-link text-sm text-brand-muted hover:text-brand-accent block py-1">Sumber Daya Manusia</a>
            <a href="<?= BASE_URL ?>/pages/prestasi.php" class="mobile-link text-sm text-brand-muted hover:text-brand-accent block py-1">Prestasi</a>
          </div>
        </div>
        
        <!-- Layanan Pendidikan Mobile Dropdown -->
        <div class="border-b border-brand-border/50">
          <button id="mobileLayananToggle" class="w-full text-left text-sm font-medium text-brand-muted hover:text-brand-accent py-2 flex items-center justify-between">
            Layanan Pendidikan
            <iconify-icon icon="lucide:chevron-down" class="w-4 h-4 transition-transform duration-300" id="mobileLayananIcon"></iconify-icon>
          </button>
          <div id="mobileLayananMenu" class="hidden pl-4 pb-4 space-y-3">
            <a href="<?= BASE_URL ?>/pages/program.php" class="mobile-link text-sm text-brand-muted hover:text-brand-accent block py-1">Program</a>
            <a href="<?= BASE_URL ?>/pages/fasilitas.php" class="mobile-link text-sm text-brand-muted hover:text-brand-accent block py-1">Fasilitas</a>
            <a href="<?= BASE_URL ?>/pages/rencana-program.php" class="mobile-link text-sm text-brand-muted hover:text-brand-accent block py-1">Rencana Program</a>
          </div>
        </div>
        
        <a href="<?= BASE_URL ?>/pages/berita.php" class="mobile-link text-sm font-medium text-brand-muted hover:text-brand-accent py-2 border-b border-brand-border/50">Berita</a>
        <a href="<?= BASE_URL ?>/pages/galeri.php" class="mobile-link text-sm font-medium text-brand-muted hover:text-brand-accent py-2 border-b border-brand-border/50">Galeri</a>

        <!-- Publikasi Mobile Dropdown -->
        <div class="border-b border-brand-border/50">
          <button id="mobilePublikasiToggle" class="w-full text-left text-sm font-medium text-brand-muted hover:text-brand-accent py-2 flex items-center justify-between">
            Publikasi
            <iconify-icon icon="lucide:chevron-down" class="w-4 h-4 transition-transform duration-300" id="mobilePublikasiIcon"></iconify-icon>
          </button>
          <div id="mobilePublikasiMenu" class="hidden pl-4 pb-4 space-y-3">
            <a href="<?= BASE_URL ?>/pages/pengumuman.php" class="mobile-link text-sm text-brand-muted hover:text-brand-accent block py-1">Pengumuman</a>
            <a href="<?= BASE_URL ?>/pages/download.php" class="mobile-link text-sm text-brand-muted hover:text-brand-accent block py-1">Download</a>
          </div>
        </div>

        <a href="<?= BASE_URL ?>/pages/anggaran.php" class="mobile-link text-sm font-medium text-brand-muted hover:text-brand-accent py-2 border-b border-brand-border/50">Anggaran & Belanja</a>
        <a href="<?= BASE_URL ?>/pages/layanan-online.php" class="mobile-link text-sm font-medium text-brand-muted hover:text-brand-accent py-2 border-b border-brand-border/50">Layanan Online</a>
        <a href="<?= BASE_URL ?>/pages/statistik.php" class="mobile-link text-sm font-medium text-brand-muted hover:text-brand-accent py-2 border-b border-brand-border/50">Statistik</a>
        <a href="<?= BASE_URL ?>/pages/faq.php" class="mobile-link text-sm font-medium text-brand-muted hover:text-brand-accent py-2 border-b border-brand-border/50">FAQ</a>
        <a href="<?= BASE_URL ?>/pages/kontak.php" class="mobile-link text-sm font-medium text-brand-muted hover:text-brand-accent py-2 border-b border-brand-border/50">Kontak</a>
        <a href="<?= BASE_URL ?>/pages/ppdb.php" class="mobile-link bg-brand-accent text-white text-xs font-semibold tracking-widest uppercase px-6 py-3 rounded text-center mt-2">PPDB </a>
      </div>
    </div>
  </nav>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const mobileProfilToggle = document.getElementById('mobileProfilToggle');
      const mobileProfilMenu = document.getElementById('mobileProfilMenu');
      const mobileProfilIcon = document.getElementById('mobileProfilIcon');
      
      if (mobileProfilToggle && mobileProfilMenu && mobileProfilIcon) {
        mobileProfilToggle.addEventListener('click', function() {
          const isHidden = mobileProfilMenu.classList.contains('hidden');
          mobileProfilMenu.classList.toggle('hidden');
          
          if (isHidden) {
            mobileProfilIcon.style.transform = 'rotate(180deg)';
          } else {
            mobileProfilIcon.style.transform = 'rotate(0deg)';
          }
        });
      }
      
      const mobileLayananToggle = document.getElementById('mobileLayananToggle');
      const mobileLayananMenu = document.getElementById('mobileLayananMenu');
      const mobileLayananIcon = document.getElementById('mobileLayananIcon');

      if (mobileLayananToggle && mobileLayananMenu && mobileLayananIcon) {
        mobileLayananToggle.addEventListener('click', function() {
          const isHidden = mobileLayananMenu.classList.contains('hidden');
          mobileLayananMenu.classList.toggle('hidden');

          if (isHidden) {
            mobileLayananIcon.style.transform = 'rotate(180deg)';
          } else {
            mobileLayananIcon.style.transform = 'rotate(0deg)';
          }
        });
      }

      const mobilePublikasiToggle = document.getElementById('mobilePublikasiToggle');
      const mobilePublikasiMenu = document.getElementById('mobilePublikasiMenu');
      const mobilePublikasiIcon = document.getElementById('mobilePublikasiIcon');

      if (mobilePublikasiToggle && mobilePublikasiMenu && mobilePublikasiIcon) {
        mobilePublikasiToggle.addEventListener('click', function() {
          const isHidden = mobilePublikasiMenu.classList.contains('hidden');
          mobilePublikasiMenu.classList.toggle('hidden');

          if (isHidden) {
            mobilePublikasiIcon.style.transform = 'rotate(180deg)';
          } else {
            mobilePublikasiIcon.style.transform = 'rotate(0deg)';
          }
        });
      }
    });
  </script>
