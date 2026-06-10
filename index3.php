
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';
require_once 'includes/db.php';

$title = SITE_NAME . " — Unggul, Berkarakter, Berprestasi";

// Cek koneksi database
$db_status = $supabaseConnected ? 'TERHUBUNG (SUPABASE ONLINE)' : 'TIDAK TERHUBUNG';
$db_class = $supabaseConnected ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';

// Ambil profil sekolah dari Supabase (dengan try-catch)
$profil = [
    'nama_sekolah' => SITE_NAME,
    'visi' => '',
    'misi' => ''
];
try {
    $profilResult = supabaseSelect('profil_sekolah', ['id' => 'eq.1', 'limit' => 1]);
    if ($profilResult['success'] && !empty($profilResult['data'])) {
        $profil = $profilResult['data'][0];
    }
} catch (Exception $e) {
    echo "Error profil: " . $e->getMessage() . "<br>";
}

// Ambil 3 berita terbaru dari Supabase
try {
    $beritaResult = supabaseSelect('berita', ['status' => 'eq.published', 'order' => 'tanggal.desc', 'limit' => 3]);
    if ($beritaResult['success']) {
        $latest_news = $beritaResult['data'];
    } else {
        $latest_news = [
            [
                'judul' => 'Selamat Datang di Website Sekolah',
                'kategori' => 'Pengumuman',
                'tanggal' => date('Y-m-d'),
                'gambar' => 'https://picsum.photos/seed/news1/800/400.jpg',
                'konten' => 'Selamat datang di website resmi ' . SITE_NAME . '.'
            ]
        ];
    }
} catch (Exception $e) {
    echo "Error berita: " . $e->getMessage() . "<br>";
    $latest_news = [];
}

// Ambil data guru dari Supabase
try {
    $guruResult = supabaseSelect('guru', ['limit' => 4]);
    if ($guruResult['success']) {
        $guru_list = $guruResult['data'];
    } else {
        $guru_list = [];
    }
} catch (Exception $e) {
    echo "Error guru: " . $e->getMessage() . "<br>";
    $guru_list = [];
}

include 'components/head.php';
include 'components/navbar.php';
?>
  <!-- Spacer untuk navbar fixed -->
  <div class="h-20"></div>
  
  <!-- Status Koneksi Database -->
  <div class="bg-gray-900 py-2">
    <div class="max-w-7xl mx-auto px-6 text-right">
      <span class="text-xs font-mono px-2 py-1 rounded <?php echo $db_class; ?>">
        DATABASE: <?php echo $db_status; ?>
      </span>
    </div>
  </div>

  <!-- ========== HERO ========== -->
  <section class="relative h-screen min-h-[700px] flex items-center justify-center overflow-hidden">
    <img src="https://picsum.photos/seed/school-hero-main/1920/1080.jpg" alt="<?php echo SITE_NAME; ?>" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0" style="background:linear-gradient(to top, rgba(255,255,255,0.05), rgba(255,255,255,0.02), transparent);"></div>
    <div class="relative z-10 text-center px-6 max-w-5xl mx-auto">
      <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-5 py-2 mb-8">
        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
        <span class="text-sm md:text-base lg:text-lg font-bold tracking-[0.2em] uppercase text-brand-label">PENERIMAAN SISWA BARU MASIH DIBUKA, SISA KUOTA 5 SISWA</span>
      </div>
      <h1 class="font-serif text-5xl md:text-7xl font-normal text-white tracking-tight leading-[1.1] mb-6">
        <?php echo htmlspecialchars($profil['nama_sekolah']); ?>
      </h1>
      <p class="text-white/70 text-base md:text-lg font-light leading-relaxed max-w-2xl mx-auto mb-10">
        Membentuk generasi unggul, berkarakter, dan berprestasi melalui pendidikan berkualitas dengan lingkungan belajar yang inspiratif dan inovatif.
      </p>
      <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
        <a href="pages/ppdb.php" class="bg-brand-accent hover:bg-brand-accent-hover text-white text-xs font-semibold tracking-widest uppercase px-8 py-4 rounded transition-colors duration-150 inline-flex items-center gap-2">
          Daftar PPDB
          <iconify-icon icon="lucide:arrow-right" class="text-sm"></iconify-icon>
        </a>
        <a href="pages/profil.php" class="bg-white/10 hover:bg-white/20 border border-white/30 text-white text-xs font-semibold tracking-widest uppercase px-8 py-4 rounded transition-colors duration-150 inline-flex items-center gap-2">
          Jelajahi Sekolah
        </a>
      </div>
    </div>
  </section>

  <!-- ========== STATS BAR ========== -->
  <section class="bg-brand-dark py-12 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-8 relative z-10">
      <div class="text-center">
        <div class="font-serif text-4xl md:text-5xl text-white mb-2 counter">1250</div>
        <div class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label">Siswa Aktif</div>
      </div>
      <div class="text-center">
        <div class="font-serif text-4xl md:text-5xl text-white mb-2 counter">85</div>
        <div class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label">Tenaga Pendidik</div>
      </div>
      <div class="text-center">
        <div class="font-serif text-4xl md:text-5xl text-white mb-2 counter">150</div>
        <div class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label">Prestasi Nasional</div>
      </div>
      <div class="text-center">
        <div class="font-serif text-4xl md:text-5xl text-white mb-2 counter">35</div>
        <div class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label">Tahun Berdiri</div>
      </div>
    </div>
  </section>

  <!-- ========== GURU & STAFF ========== -->
  <?php if (!empty($guru_list)): ?>
  <section class="py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center mb-12">
        <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label">Guru & Staff</span>
        <h2 class="font-serif text-3xl md:text-4xl mt-4">Tenaga Pendidik Kami</h2>
      </div>
      <div class="grid md:grid-cols-4 gap-8">
        <?php foreach ($guru_list as $guru): ?>
          <div class="bg-white rounded-lg p-6 text-center shadow-sm border border-gray-200">
            <div class="w-24 h-24 mx-auto mb-4 rounded-full overflow-hidden">
              <img src="<?php echo htmlspecialchars($guru['foto'] ?? 'https://i.pravatar.cc/150'); ?>" alt="<?php echo htmlspecialchars($guru['nama']); ?>" class="w-full h-full object-cover">
            </div>
            <h3 class="font-semibold text-lg mb-1"><?php echo htmlspecialchars($guru['nama']); ?></h3>
            <p class="text-sm text-brand-muted mb-1"><?php echo htmlspecialchars($guru['jabatan'] ?? 'Guru'); ?></p>
            <?php if (!empty($guru['mapel']) && $guru['mapel'] !== '-'): ?>
              <p class="text-xs text-brand-muted"><?php echo htmlspecialchars($guru['mapel']); ?></p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ========== BERITA TERBARU ========== -->
  <?php if (!empty($latest_news)): ?>
  <section class="py-24 bg-brand-dark">
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex items-end justify-between mb-12">
        <div>
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label">Kabar Terbaru</span>
          <h2 class="font-serif text-3xl md:text-4xl text-white mt-4">Berita & Kegiatan</h2>
        </div>
        <a href="pages/berita.php" class="text-brand-accent text-xs font-bold tracking-widest uppercase flex items-center gap-2 hover:text-brand-label transition-colors">Lihat Semua</a>
      </div>
      <div class="grid md:grid-cols-3 gap-8">
        <?php foreach ($latest_news as $news): ?>
          <article class="news-card group cursor-pointer" onclick="location.href='pages/berita.php'">
            <div class="overflow-hidden rounded-lg mb-4">
              <img src="<?php echo htmlspecialchars($news['gambar'] ?? 'https://picsum.photos/seed/news/' . ($news['id'] ?? time()) . '/800/400.jpg'); ?>" class="w-full h-48 object-cover transition-transform group-hover:scale-105" alt="<?php echo htmlspecialchars($news['judul']); ?>">
            </div>
            <div class="flex items-center gap-3 mb-2">
              <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-accent"><?php echo htmlspecialchars($news['kategori'] ?? 'Umum'); ?></span>
              <span class="text-white/30">•</span>
              <span class="text-xs text-white/40"><?php echo date('d M Y', strtotime($news['tanggal'])); ?></span>
            </div>
            <h3 class="font-serif text-lg text-white mb-2 group-hover:text-brand-accent transition-colors"><?php echo htmlspecialchars($news['judul']); ?></h3>
            <p class="text-white/50 text-xs font-light line-clamp-2"><?php echo htmlspecialchars(substr($news['konten'] ?? '', 0, 100)) . '...'; ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

<?php include 'components/footer.php'; ?>
