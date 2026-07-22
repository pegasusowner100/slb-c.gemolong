<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/track-visitor.php';
trackVisitor('/pages/profil');
$title = "Profil — SLB BC KARYA SEJAHTERA " . SITE_NAME;

// Default profil data
$defaultProfil = [
    'nama_sekolah' => '',
    'akreditasi' => '',
    'sejarah' => '',
    'visi' => '',
    'misi' => '',
    'profil_kepala_sekolah' => '',
    'sambutan' => '',
    'alamat' => '',
    'telepon' => '',
    'email' => '',
    'gambar_gedung' => '',
    'struktur_organisasi' => '',
    'dasar_hukum' => '',
    'nama_kepala_sekolah' => '',
    'foto_kepala_sekolah' => '',
    'instagram' => '',
    'facebook' => '',
    'youtube' => '',
    'tiktok' => '',
    'website' => '',
    'maps_url' => '',
    'video_profil' => ''
];
$profil = $defaultProfil;

if ($supabaseConnected) {
    $profilResult = supabaseSelect('profil_sekolah', ['id' => 'eq.1', 'limit' => 1]);
    if ($profilResult['success'] && !empty($profilResult['data'])) {
        $dbProfil = $profilResult['data'][0];
        // Merge with default, ensuring all keys exist
        $profil = [
            'nama_sekolah' => $dbProfil['nama_sekolah'] ?? $defaultProfil['nama_sekolah'],
            'akreditasi' => $dbProfil['akreditasi'] ?? $defaultProfil['akreditasi'],
            'sejarah' => $dbProfil['sejarah'] ?? $defaultProfil['sejarah'],
            'visi' => $dbProfil['visi'] ?? $defaultProfil['visi'],
            'misi' => $dbProfil['misi'] ?? $defaultProfil['misi'],
            'profil_kepala_sekolah' => $dbProfil['profil_kepala_sekolah'] ?? $defaultProfil['profil_kepala_sekolah'],
            'sambutan' => $dbProfil['sambutan'] ?? $defaultProfil['sambutan'],
            'alamat' => $dbProfil['alamat'] ?? $defaultProfil['alamat'],
            'telepon' => $dbProfil['telepon'] ?? $defaultProfil['telepon'],
            'email' => $dbProfil['email'] ?? $defaultProfil['email'],
            'gambar_gedung' => $dbProfil['gambar_gedung'] ?? $defaultProfil['gambar_gedung'],
            'struktur_organisasi' => $dbProfil['struktur_organisasi'] ?? $defaultProfil['struktur_organisasi'],
            'dasar_hukum' => $dbProfil['dasar_hukum'] ?? $defaultProfil['dasar_hukum'],
            'nama_kepala_sekolah' => $dbProfil['nama_kepala_sekolah'] ?? $defaultProfil['nama_kepala_sekolah'],
            'foto_kepala_sekolah' => $dbProfil['foto_kepala_sekolah'] ?? $defaultProfil['foto_kepala_sekolah'],
            'instagram' => $dbProfil['instagram'] ?? $defaultProfil['instagram'],
            'facebook' => $dbProfil['facebook'] ?? $defaultProfil['facebook'],
            'youtube' => $dbProfil['youtube'] ?? $defaultProfil['youtube'],
            'tiktok' => $dbProfil['tiktok'] ?? $defaultProfil['tiktok'],
            'website' => $dbProfil['website'] ?? $defaultProfil['website'],
            'maps_url' => $dbProfil['maps_url'] ?? $defaultProfil['maps_url'],
            'video_profil' => $dbProfil['video_profil'] ?? $defaultProfil['video_profil']
        ];
    }
}

// Fetch data for sections
$guruList = [];
$prestasiList = [];

if ($supabaseConnected) {
    // Order by `urutan` so tampil sesuai nomor/urutan database
    $guruResult = supabaseSelect('guru', ['order' => 'urutan.asc']);
    if (!$guruResult['success']) {
        $guruResult = supabaseSelect('guru', ['order' => 'created_at.asc']);
    }
    if (!$guruResult['success']) {
        $guruResult = supabaseSelect('guru', []);
    }
    if ($guruResult['success']) {
        $guruList = $guruResult['data'];
    }
    
    $prestasiResult = supabaseSelect('prestasi', ['order' => 'tahun.desc']);
    if ($prestasiResult['success']) {
        $prestasiList = $prestasiResult['data'];
    }
}

// Split guru into pimpinan, guru, and tendik/teknis
$pimpinanOnly = [];
$guruOnly = [];
$tendikOnly = [];
foreach ($guruList as $g) {
    $jabatan = strtolower($g['jabatan'] ?? '');
    if (strpos($jabatan, 'kepala') !== false || strpos($jabatan, 'wakil') !== false || strpos($jabatan, 'waka') !== false) {
        $pimpinanOnly[] = $g;
    } elseif (strpos($jabatan, 'guru') !== false || empty($jabatan)) {
        $guruOnly[] = $g;
    } else {
        $tendikOnly[] = $g;
    }
}

include '../components/head.php';
?>
<body class="text-brand-dark font-sans glass-body">
  <?php include '../components/navbar.php'; ?>
  <div class="glass-content-wrapper">

  <!-- Header -->
  <section class="page-hero bg-brand-dark">
    <div class="max-w-7xl mx-auto px-6">
      <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label fade-in-up">Tentang Kami</span>
      <h1 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1] fade-in-up delay-100">Profil <em>Sekolah</em></h1>
    </div>
  </section>

  <!-- Tab Navigation -->
  <section class="py-8 bg-brand-bg/50 sticky top-20 z-40">
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex flex-wrap gap-4 justify-center">
        <button class="tab-btn px-6 py-3 rounded-full border-2 border-brand-accent text-brand-accent font-bold text-sm hover:bg-brand-accent hover:text-white transition-all" data-section="sambutan">Sambutan Kepala Sekolah</button>
        <button class="tab-btn px-6 py-3 rounded-full border-2 border-brand-border text-brand-muted font-bold text-sm hover:border-brand-accent hover:text-brand-accent transition-all" data-section="dasar-hukum">Dasar Hukum</button>
        <button class="tab-btn px-6 py-3 rounded-full border-2 border-brand-border text-brand-muted font-bold text-sm hover:border-brand-accent hover:text-brand-accent transition-all" data-section="sejarah">Latar Belakang</button>
        <button class="tab-btn px-6 py-3 rounded-full border-2 border-brand-border text-brand-muted font-bold text-sm hover:border-brand-accent hover:text-brand-accent transition-all" data-section="visimisi">Visi Misi</button>
        <button class="tab-btn px-6 py-3 rounded-full border-2 border-brand-border text-brand-muted font-bold text-sm hover:border-brand-accent hover:text-brand-accent transition-all" data-section="struktur">Struktur Organisasi</button>
        <button class="tab-btn px-6 py-3 rounded-full border-2 border-brand-border text-brand-muted font-bold text-sm hover:border-brand-accent hover:text-brand-accent transition-all" data-section="prestasi">Prestasi</button>
        <button class="tab-btn px-6 py-3 rounded-full border-2 border-brand-border text-brand-muted font-bold text-sm hover:border-brand-accent hover:text-brand-accent transition-all" data-section="sumberdaya">Sumber Daya Manusia</button>
      </div>
    </div>
  </section>

  <style>
    .profil-section.hidden {
      display: none;
    }
  </style>

  <!-- SAMBUTAN KEPALA SEKOLAH -->
  <section id="sambutan" class="profil-section py-12">
    <div class="max-w-7xl mx-auto px-6">
      <div class="glass-section">
        <div class="text-center mb-8 fade-in-up delay-100">
        <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Profil</span>
          <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Sambutan Kepala Sekolah</h2>
        </div>
      </div>
        
        <div class="bg-white/70 backdrop-blur-sm rounded-xl border border-brand-border/50 p-8 max-w-4xl mx-auto fade-in-up delay-200">
          <div class="grid lg:grid-cols-5 gap-12 items-center">
            <div class="lg:col-span-2 relative">
              <div class="relative">
                <img src="<?php echo htmlspecialchars($profil['foto_kepala_sekolah'] ?? 'https://picsum.photos/seed/kepsek-portrait/480/600.jpg'); ?>" alt="Kepala Sekolah" class="w-full h-[450px] object-cover rounded-lg shadow-lg">
                <div class="absolute -bottom-4 -right-4 w-24 h-24 border-4 border-brand-accent rounded-lg rotate-6"></div>
                <div class="absolute -top-4 -left-4 w-24 h-24 border-4 border-brand-accent/30 rounded-lg -rotate-6"></div>
              </div>
            </div>
            <div class="lg:col-span-3">
              <div class="relative pl-6 border-l-2 border-brand-accent mb-6">
                <iconify-icon icon="lucide:quote" class="text-brand-accent/30 text-4xl absolute -left-5 -top-2 bg-brand-bg px-1"></iconify-icon>
                <p class="text-brand-muted text-sm font-light leading-relaxed italic text-justify">
                  "<?php echo nl2br(htmlspecialchars($profil['sambutan'] ?? 'Pendidikan bukan sekadar menuntut ilmu, melainkan proses membentuk karakter, membangun mimpi, dan mempersiapkan generasi yang akan membawa perubahan bagi bangsa.')); ?>"
                </p>
              </div>
              <div class="flex items-center gap-4">
                <img src="<?php echo htmlspecialchars($profil['foto_kepala_sekolah'] ?? 'https://picsum.photos/seed/kepsek/100/100.jpg'); ?>" alt="Kepala Sekolah" class="w-24 h-24 rounded-full object-cover border-2 border-brand-accent/30">
                <div>
                  <h4 class="font-serif text-2xl font-semibold"><?php echo htmlspecialchars($profil['nama_kepala_sekolah'] ?? 'Drs. Ahmad Sudrajat, M.Pd'); ?></h4>
                  <p class="text-sm text-brand-muted">Kepala Sekolah <?php echo htmlspecialchars($profil['nama_sekolah'] ?? 'SLB BC KARYA SEJAHTERA'); ?></p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- DASAR HUKUM -->
  <section id="dasar-hukum" class="profil-section py-12">
    <div class="max-w-7xl mx-auto px-6">
      <div class="glass-section">
        <div class="text-center mb-8 fade-in-up delay-100">
          <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Profil</span>
            <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Dasar Hukum</h2>
          </div>
        </div>
        
        <div class="bg-white p-8 rounded-2xl shadow-lg border border-brand-border/30 overflow-hidden max-w-4xl mx-auto fade-in-up delay-200">
          <div class="bg-gradient-to-r from-orange-500 to-orange-600 -mx-8 -mt-8 px-8 py-4 mb-6">
            <h3 class="font-serif text-3xl font-bold text-center text-white">Peraturan & Dasar Hukum</h3>
          </div>
          <div class="space-y-4">
            <?php 
            $dasarHukumList = explode("\n", trim($profil['dasar_hukum']));
            foreach ($dasarHukumList as $index => $item): 
              if (!empty(trim($item))):
            ?>
            <div class="p-5 bg-orange-50 rounded-xl border-l-4 border-orange-500 hover:bg-orange-100 transition-colors">
              <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center mt-0.5">
                  <span class="font-serif text-xl font-bold text-white"><?= $index + 1 ?></span>
                </div>
                <p class="font-serif text-gray-700 text-lg leading-relaxed"><?= htmlspecialchars(trim($item)) ?></p>
              </div>
            </div>
            <?php 
              endif;
            endforeach; 
            ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- SEJARAH SINGKAT -->
  <section id="sejarah" class="profil-section py-12">
    <div class="max-w-7xl mx-auto px-6">
      <div class="glass-section">
        <div class="text-center mb-8 fade-in-up delay-100">
          <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Profil</span>
            <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Latar Belakang</h2>
          </div>
        </div>
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <div class="fade-in-left delay-200">
            <p class="text-brand-muted text-sm font-light leading-relaxed text-justify mb-6">
              <?= nl2br(htmlspecialchars($profil['sejarah'])) ?>
            </p>
          </div>
          <div class="fade-in-right delay-300">
            <?php if (!empty($profil['video_profil'])): ?>
              <div class="w-full h-[400px] rounded-lg shadow-lg overflow-hidden">
                <?= getVideoEmbed($profil['video_profil'], '100%', '100%') ?>
              </div>
            <?php else: ?>
              <img src="<?= htmlspecialchars($profil['gambar_gedung']) ?>" alt="Gedung Sekolah" class="w-full h-[400px] object-cover rounded-lg shadow-lg">
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- VISI MISI -->
  <section id="visimisi" class="profil-section py-12">
    <div class="max-w-7xl mx-auto px-6">
      <div class="glass-section">
        <div class="text-center mb-8 fade-in-up delay-100">
          <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Profil</span>
            <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Visi & Misi</h2>
          </div>
        </div>
        <div class="grid md:grid-cols-2 gap-8 lg:gap-12">
          <!-- VISI -->
          <div class="fade-in-up delay-200">
            <div class="h-full bg-white p-8 rounded-2xl border-2 border-brand-accent/20 shadow-md flex flex-col hover:border-brand-accent transition-colors duration-300">
              <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 rounded-2xl bg-brand-accent flex items-center justify-center shadow-md shadow-brand-accent/20 flex-shrink-0">
                  <iconify-icon icon="lucide:target" class="text-white text-3xl"></iconify-icon>
                </div>
                <h3 class="font-serif text-3xl font-bold text-[#1F2D26]">Visi</h3>
              </div>
              <p class="text-[#1F2D26] text-base md:text-lg font-medium leading-relaxed border-l-4 border-brand-accent pl-4 py-1">
                <?= nl2br(htmlspecialchars($profil['visi'])) ?>
              </p>
            </div>
          </div>
          
          <!-- MISI -->
          <div class="fade-in-up delay-300">
            <div class="h-full bg-white p-8 rounded-2xl border-2 border-amber-500/20 shadow-md flex flex-col hover:border-amber-500 transition-colors duration-300">
              <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 rounded-2xl bg-amber-500 flex items-center justify-center shadow-md shadow-amber-500/20 flex-shrink-0">
                  <iconify-icon icon="lucide:flag" class="text-white text-3xl"></iconify-icon>
                </div>
                <h3 class="font-serif text-3xl font-bold text-[#1F2D26]">Misi</h3>
              </div>
              <div class="space-y-5">
                <?php 
                $misiText = $profil['misi'];
                $lines = explode("\n", $misiText);
                $misiIndex = 1;
                foreach ($lines as $line) {
                    $trimmedLine = trim($line);
                    if (!empty($trimmedLine)) {
                        // Hilangkan format angka manual jika ada (misal "1.", "2)") agar tidak duplikat
                        $cleanLine = preg_replace('/^\d+[\.\)\s-]+\s*/', '', $trimmedLine);
                        ?>
                        <div class="flex items-start gap-4">
                          <span class="flex-shrink-0 w-8 h-8 rounded-full bg-brand-accent text-white flex items-center justify-center font-bold text-sm shadow-sm">
                            <?= $misiIndex++ ?>
                          </span>
                          <p class="text-[#1F2D26] text-sm md:text-base font-medium leading-relaxed pt-0.5">
                            <?= htmlspecialchars($cleanLine) ?>
                          </p>
                        </div>
                        <?php
                    }
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- STRUKTUR ORGANISASI -->
  <section id="struktur" class="profil-section py-12">
    <div class="max-w-7xl mx-auto px-6">
      <div class="glass-section">
        <div class="text-center mb-8 fade-in-up delay-100">
          <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Profil</span>
            <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Struktur Organisasi</h2>
          </div>
        </div>
        
        <div class="flex justify-center fade-in-up delay-200">
          <img src="<?= htmlspecialchars($profil['struktur_organisasi']) ?>" alt="Struktur Organisasi" class="w-full max-w-5xl h-auto rounded-lg shadow-lg border border-brand-border/30">
        </div>
      </div>
    </div>
  </section>

  <!-- PRESTASI -->
  <section id="prestasi" class="profil-section py-12">
    <div class="max-w-7xl mx-auto px-6">
      <div class="glass-section">
        <div class="text-center mb-8 fade-in-up delay-100">
          <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Profil</span>
            <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Prestasi</h2>
          </div>
        </div>
        
        <?php if (empty($prestasiList)): ?>
          <div class="text-center py-12 fade-in-up delay-200">
            <iconify-icon icon="lucide:trophy" class="text-6xl text-brand-muted/30 mb-4"></iconify-icon>
            <p class="text-brand-muted">Belum ada data prestasi.</p>
          </div>
        <?php else: ?>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($prestasiList as $index => $prestasi): ?>
              <div class="bg-white border border-brand-border rounded-lg overflow-hidden transition-all duration-300 hover:border-brand-accent/30 hover:shadow-xl hover:-translate-y-1 fade-in-up" style="animation-delay: <?= ($index % 5 + 1) * 100 ?>ms">
                <img src="<?= htmlspecialchars($prestasi['foto'] ?? 'https://picsum.photos/seed/' . htmlspecialchars($prestasi['id']) . '/400/250.jpg') ?>" alt="<?= htmlspecialchars($prestasi['nama']) ?>" class="w-full h-48 object-cover">
                <div class="p-6">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-yellow-500/10 flex items-center justify-center">
                      <iconify-icon icon="lucide:trophy" class="text-yellow-600"></iconify-icon>
                    </div>
                    <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label">
                      <?= htmlspecialchars($prestasi['kategori'] ?? 'Umum') ?> • <?= htmlspecialchars($prestasi['tahun'] ?? '-') ?>
                    </span>
                  </div>
                  <h3 class="font-serif text-lg text-brand-dark mb-2"><?= htmlspecialchars($prestasi['nama']) ?></h3>
                  <p class="text-brand-muted text-sm font-light">
                    <?= htmlspecialchars($prestasi['peraih'] ?? '-') ?><?php if (!empty($prestasi['lokasi'])): ?> • <?= htmlspecialchars($prestasi['lokasi']) ?><?php endif; ?>
                  </p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- SUMBER DAYA MANUSIA -->
  <section id="sumberdaya" class="profil-section py-12">
    <div class="max-w-7xl mx-auto px-6">
      <div class="glass-section">
        <div class="text-center mb-8 fade-in-up delay-100">
          <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Profil</span>
            <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Sumber Daya Manusia</h2>
          </div>
        </div>

        <!-- 1. KEPALA SEKOLAH DAN WAKIL KEPALA SEKOLAH -->
        <div class="mb-12 fade-in-up delay-200">
          <h3 class="font-serif text-2xl font-semibold mb-6 flex items-center gap-2">
            <iconify-icon icon="lucide:user-check" class="text-brand-accent"></iconify-icon>
            Kepala Sekolah dan Wakil Kepala Sekolah
          </h3>
          <?php if (empty($pimpinanOnly)): ?>
            <div class="text-center py-12">
              <p class="text-brand-muted">Belum ada data Kepala Sekolah dan Wakil Kepala Sekolah.</p>
            </div>
          <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
              <?php foreach ($pimpinanOnly as $index => $pimpinan): ?>
                <div class="group cursor-pointer text-center">
                  <div class="overflow-hidden rounded-lg mb-4">
                    <img src="<?= htmlspecialchars($pimpinan['foto'] ?? 'https://picsum.photos/seed/pimpinan-'. $index .'/300/360') ?>" class="w-full h-[320px] md:h-[360px] object-cover object-center transition-transform duration-500 group-hover:scale-110" alt="<?= htmlspecialchars($pimpinan['nama']) ?>">
                  </div>
                  <h4 class="font-serif text-lg mb-1"><?= htmlspecialchars($pimpinan['nama']) ?></h4>
                  <p class="text-xs text-brand-muted mb-1"><?= htmlspecialchars($pimpinan['jabatan'] ?? 'Pimpinan') ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- 2. GURU -->
        <div class="mb-12 fade-in-up delay-300">
          <h3 class="font-serif text-2xl font-semibold mb-6 flex items-center gap-2">
            <iconify-icon icon="lucide:graduation-cap" class="text-brand-accent"></iconify-icon>
            Guru
          </h3>
          <?php if (empty($guruOnly)): ?>
            <div class="text-center py-12">
              <p class="text-brand-muted">Belum ada data guru.</p>
            </div>
          <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
              <?php foreach ($guruOnly as $index => $guru): ?>
                <div class="group cursor-pointer text-center">
                  <div class="overflow-hidden rounded-lg mb-4">
                    <img src="<?= htmlspecialchars($guru['foto'] ?? 'https://picsum.photos/seed/guru-'. $index .'/300/360') ?>" class="w-full h-[320px] md:h-[360px] object-cover object-center transition-transform duration-500 group-hover:scale-110" alt="<?= htmlspecialchars($guru['nama']) ?>">
                  </div>
                  <h4 class="font-serif text-lg mb-1"><?= htmlspecialchars($guru['nama']) ?></h4>
                  <p class="text-xs text-brand-muted mb-1"><?= htmlspecialchars($guru['jabatan'] ?? 'Guru') ?></p>
                  <?php if (!empty($guru['mapel'])): ?>
                    <p class="text-xs text-brand-muted">Mapel: <?= htmlspecialchars($guru['mapel']) ?></p>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- 3. TENAGA PENDIDIK DAN TEKNIS -->
        <div class="fade-in-up delay-400">
          <h3 class="font-serif text-2xl font-semibold mb-6 flex items-center gap-2">
            <iconify-icon icon="lucide:users" class="text-brand-accent"></iconify-icon>
            Tenaga Pendidik dan Teknis
          </h3>
          <?php if (empty($tendikOnly)): ?>
            <div class="text-center py-12">
              <p class="text-brand-muted">Belum ada data tenaga pendidik dan teknis.</p>
            </div>
          <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
              <?php foreach ($tendikOnly as $index => $tendik): ?>
                <div class="group cursor-pointer text-center">
                  <div class="overflow-hidden rounded-lg mb-4">
                    <img src="<?= htmlspecialchars($tendik['foto'] ?? 'https://picsum.photos/seed/tendik-'. $index .'/300/360') ?>" class="w-full h-[320px] md:h-[360px] object-cover object-center transition-transform duration-500 group-hover:scale-110" alt="<?= htmlspecialchars($tendik['nama']) ?>">
                  </div>
                  <h4 class="font-serif text-lg mb-1"><?= htmlspecialchars($tendik['nama']) ?></h4>
                  <p class="text-xs text-brand-muted mb-1"><?= htmlspecialchars($tendik['jabatan'] ?? 'Tendik') ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  </div> <!-- glass-content-wrapper end -->

  <!-- JavaScript for Tab Navigation -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const tabBtns = document.querySelectorAll('.tab-btn');
      const sections = document.querySelectorAll('.profil-section');

      function showSection(sectionId) {
        sections.forEach(section => {
          section.classList.add('hidden');
        });
        const target = document.getElementById(sectionId);
        if (target) {
          target.classList.remove('hidden');
        }
        tabBtns.forEach(btn => {
          if (btn.dataset.section === sectionId) {
            btn.classList.add('border-brand-accent');
            btn.classList.add('text-brand-accent');
            btn.classList.remove('border-brand-border');
            btn.classList.remove('text-brand-muted');
          } else {
            btn.classList.remove('border-brand-accent');
            btn.classList.remove('text-brand-accent');
            btn.classList.add('border-brand-border');
            btn.classList.add('text-brand-muted');
          }
        });
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      }

      tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
          const sectionId = this.dataset.section;
          showSection(sectionId);
          history.replaceState(null, '', `#${sectionId}`);
        });
      });

      function handleHash() {
        const hash = window.location.hash.slice(1);
        if (hash) {
          showSection(hash);
        } else {
          showSection('sambutan');
        }
      }

      window.addEventListener('hashchange', handleHash);
      handleHash();
    });
  </script>

  <?php include '../components/footer.php'; ?>
</body>
</html>
