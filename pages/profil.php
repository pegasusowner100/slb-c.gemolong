<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$title = "Profil — " . SITE_NAME;

// Default profil data
$defaultProfil = [
    'nama_sekolah' => SITE_NAME,
    'akreditasi' => 'A',
    'sejarah' => 'SLB-C YPSLB Gemolong didirikan dengan tujuan memberikan pendidikan terbaik untuk anak berkebutuhan khusus. Berkomitmen untuk menciptakan generasi mandiri, berkarakter, dan berprestasi.',
    'visi' => 'Menjadikan SLB-C YPSLB Gemolong sebagai lembaga pendidikan luar biasa yang unggul dalam pengembangan potensi anak berkebutuhan khusus secara optimal, berkarakter, mandiri, dan berprestasi.',
    'misi' => 'Menyelenggarakan pendidikan yang berkualitas, mengembangkan potensi akademis dan non-akademis, serta membangun karakter, serta menjalin kerjasama dengan berbagai pihak.',
    'profil_kepala_sekolah' => 'Kepala sekolah yang inovatif, berdedikasi, dan berpengalaman dalam dunia pendidikan khusus.',
    'sambutan' => 'Pendidikan bukan sekadar menuntut ilmu, melainkan proses membentuk karakter, membangun mimpi, dan memberdayakan generasi yang akan membawa perubahan bagi bangsa. Di SLB-C YPSLB Gemolong, kami berkomitmen untuk menjadi rumah kedua bagi setiap siswa agar mereka tumbuh menjadi pribadi yang unggul dan berkarakter.',
    'alamat' => 'Jl. Pendidikan No. 1, Gemolong, Kabupaten Sragen, Jawa Tengah',
    'telepon' => '(0271) 123456',
    'email' => 'info@slbc-gemolong.sch.id',
    'gambar_gedung' => 'https://picsum.photos/seed/school-building-front/700/525',
    'struktur_organisasi' => 'https://picsum.photos/seed/struktur-organisasi/1000/600',
    'dasar_hukum' => '1. Undang-Undang Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional
2. Peraturan Pemerintah Nomor 19 Tahun 2005 tentang Pendidikan Anak Berkebutuhan Khusus
3. Peraturan Menteri Pendidikan dan Kebudayaan Nomor 70 Tahun 2013 tentang Pendidikan Dasar
4. Peraturan Daerah Provinsi Jawa Tengah Nomor 12 Tahun 2018 tentang Pendidikan Luar Biasa
5. Akta Notaris Pendirian Yayasan YPSLB Gemolong Nomor 01 Tanggal 01 Januari 2000',
    'nama_kepala_sekolah' => 'Drs. Ahmad Sudrajat, M.Pd',
    'foto_kepala_sekolah' => 'https://picsum.photos/seed/kepsek-portrait/480/600',
    'instagram' => '',
    'facebook' => '',
    'youtube' => '',
    'tiktok' => '',
    'website' => '',
    'maps_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3955.012345678901!2d110.98765432109876!3d-7.456789012345679!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a1234567890ab%3A0x123456789abcdef!2sSLB-C%20YPSLB%20Gemolong!5e0!3m2!1sid!2sid!4v1234567890123!5m2!1sid!2sid',
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

// Split guru into guru and tendik
$guruOnly = [];
$tendikOnly = [];
foreach ($guruList as $g) {
    $jabatan = strtolower($g['jabatan'] ?? '');
    if (strpos($jabatan, 'guru') !== false || empty($jabatan)) {
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
        <button class="tab-btn px-6 py-3 rounded-full border-2 border-brand-border text-brand-muted font-bold text-sm hover:border-brand-accent hover:text-brand-accent transition-all" data-section="sejarah">Sejarah Singkat</button>
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
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label mb-4">Profil</span>
          <h2 class="font-serif text-3xl md:text-4xl text-brand-dark mb-6">Sambutan Kepala Sekolah</h2>
          <div class="w-20 h-1 bg-brand-accent mx-auto"></div>
        </div>
        
        <div class="bg-white/70 backdrop-blur-sm rounded-xl border border-brand-border/50 p-8 max-w-4xl mx-auto fade-in-up delay-200">
          <div class="grid lg:grid-cols-5 gap-12 items-center">
            <div class="lg:col-span-2 relative">
              <div class="relative">
                <img src="<?php echo htmlspecialchars($profil['foto_kepala_sekolah'] ?? 'https://picsum.photos/seed/kepsek-portrait/480/600.jpg'); ?>" alt="Kepala Sekolah" class="w-full h-[450px] object-cover rounded-lg shadow-lg">
                <div class="absolute -bottom-4 -right-4 w-24 h-24 border-2 border-brand-accent rounded-lg"></div>
                <div class="absolute -top-4 -left-4 w-24 h-24 border-2 border-brand-accent/30 rounded-lg"></div>
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
                <div>
                  <h4 class="font-serif text-lg font-semibold"><?php echo htmlspecialchars($profil['nama_kepala_sekolah'] ?? 'Drs. Ahmad Sudrajat, M.Pd'); ?></h4>
                  <p class="text-xs text-brand-muted">Kepala Sekolah <?php echo htmlspecialchars($profil['nama_sekolah'] ?? 'SLB-C YPSLB Gemolong'); ?></p>
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
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label mb-4">Profil</span>
          <h2 class="font-serif text-3xl md:text-4xl text-brand-dark mb-6">Dasar Hukum</h2>
          <div class="w-20 h-1 bg-brand-accent mx-auto"></div>
        </div>
        
        <div class="bg-white/70 backdrop-blur-sm rounded-xl border border-brand-border/50 p-8 max-w-4xl mx-auto fade-in-up delay-200">
          <div class="space-y-4">
            <?php 
            $dasarHukumList = explode("\n", trim($profil['dasar_hukum']));
            foreach ($dasarHukumList as $item): 
              if (!empty(trim($item))):
            ?>
            <div class="flex items-start gap-4 p-4 bg-brand-bg/50 rounded-lg border border-brand-border/30 hover:border-brand-accent/30 transition-colors">
              <div class="w-10 h-10 bg-brand-accent/10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                <iconify-icon icon="lucide:file-text" class="text-brand-accent w-5 h-5"></iconify-icon>
              </div>
              <p class="text-brand-dark text-sm font-medium leading-relaxed"><?= htmlspecialchars(trim($item)) ?></p>
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
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label mb-4">Profil</span>
          <h2 class="font-serif text-3xl md:text-4xl text-brand-dark mb-6">Sejarah Singkat</h2>
          <div class="w-20 h-1 bg-brand-accent mx-auto"></div>
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
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label mb-4">Profil</span>
          <h2 class="font-serif text-3xl md:text-4xl text-brand-dark mb-6">Visi & Misi</h2>
          <div class="w-20 h-1 bg-brand-accent mx-auto"></div>
        </div>
        <div class="space-y-12">
          <!-- VISI -->
          <div class="fade-in-up delay-200">
            <div class="flex flex-col items-center gap-4 mb-4">
              <div class="w-12 h-12 rounded-full bg-brand-accent/10 flex items-center justify-center">
                <iconify-icon icon="lucide:target" class="text-brand-accent text-xl"></iconify-icon>
              </div>
              <div class="text-center">
                <h3 class="font-serif text-2xl font-semibold mb-3">Visi</h3>
                <p class="text-brand-muted text-sm font-light leading-relaxed text-justify">
                  <?= nl2br(htmlspecialchars($profil['visi'])) ?>
                </p>
              </div>
            </div>
          </div>
          
          <!-- MISI -->
          <div class="fade-in-up delay-300">
            <div class="flex flex-col items-center gap-4 mb-4">
              <div class="w-12 h-12 rounded-full bg-brand-accent/10 flex items-center justify-center">
                <iconify-icon icon="lucide:flag" class="text-brand-accent text-xl"></iconify-icon>
              </div>
              <div class="w-full max-w-3xl">
                <h3 class="font-serif text-2xl font-semibold mb-3 text-center">Misi</h3>
                <div class="text-brand-muted text-sm font-light leading-relaxed text-justify">
                  <?php 
                  $misiText = $profil['misi'];
                  $lines = explode("\n", $misiText);
                  foreach ($lines as $index => $line) {
                      $trimmedLine = trim($line);
                      if (!empty($trimmedLine)) {
                          echo '<div class="mb-3">'. htmlspecialchars($trimmedLine) . '</div>';
                      }
                  }
                  ?>
                </div>
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
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label mb-4">Profil</span>
          <h2 class="font-serif text-3xl md:text-4xl text-brand-dark mb-6">Struktur Organisasi</h2>
          <div class="w-20 h-1 bg-brand-accent mx-auto"></div>
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
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label mb-4">Profil</span>
          <h2 class="font-serif text-3xl md:text-4xl text-brand-dark mb-6">Prestasi</h2>
          <div class="w-20 h-1 bg-brand-accent mx-auto"></div>
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
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label mb-4">Profil</span>
          <h2 class="font-serif text-3xl md:text-4xl text-brand-dark mb-6">Sumber Daya Manusia</h2>
          <div class="w-20 h-1 bg-brand-accent mx-auto"></div>
        </div>

        <!-- GURU -->
        <div class="mb-12 fade-in-up delay-200">
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

        <!-- TENDIK -->
        <div class="fade-in-up delay-300">
          <h3 class="font-serif text-2xl font-semibold mb-6 flex items-center gap-2">
            <iconify-icon icon="lucide:users" class="text-brand-accent"></iconify-icon>
            Tenaga Kependidikan (Tendik)
          </h3>
          <?php if (empty($tendikOnly)): ?>
            <div class="text-center py-12">
              <p class="text-brand-muted">Belum ada data tendik.</p>
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
