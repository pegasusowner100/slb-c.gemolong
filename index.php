<?php
require_once 'includes/db.php';
$title = "SLB-C YPSLB Gemolong — Pendidikan Luar Biasa Berkualitas";
$page = 'home';

// Default hero data
$hero = [
    'tagline' => 'PENERIMAAN SISWA BARU MASIH DIBUKA, SISA KUOTA 5 SISWA',
    'judul' => 'SLB-C YPSLB Gemolong',
    'deskripsi' => 'Membentuk generasi unggul, berkarakter, dan berprestasi melalui pendidikan berkualitas dengan lingkungan belajar yang inspiratif dan inovatif.',
    'background_image' => 'https://picsum.photos/seed/school-hero-main/1920/1080',
    'background_images' => 'https://picsum.photos/seed/hero1/1920/1080,https://picsum.photos/seed/hero2/1920/1080,https://picsum.photos/seed/hero3/1920/1080',
    'cta1_text' => 'Daftar PPDB',
    'cta1_link' => '#',
    'cta2_text' => 'Jelajahi Sekolah',
    'cta2_link' => '#',
    'motto' => 'Mandiri berkarakter berdikari',
    'tahun_berdiri' => 1990,
    'siswa_aktif' => 1250,
    'alumni' => 5000,
    'tenaga_pendidik' => 85,
    'total_prestasi' => 150,
    'jumlah_ruangan' => 26,
    'buku_paket' => 500,
    'latitude' => '-7.4585',
    'longitude' => '110.9567'
];
$programs = [];
$fasilitas = [];
$berita = [];
$galeri = [
    'Photo' => [],
    'Video' => []
]; // Grouped by jenis_galeri
$faqs = []; // Untuk menyimpan FAQ dari database
$profil = [
    'nama_sekolah' => SITE_NAME,
    'akreditasi' => 'A',
    'sejarah' => 'SLB-C YPSLB Gemolong didirikan dengan tujuan memberikan pendidikan terbaik untuk anak berkebutuhan khusus. Berkomitmen untuk menciptakan generasi mandiri, berkarakter, dan berprestasi.',
    'visi' => 'Menjadikan SLB-C YPSLB Gemolong sebagai lembaga pendidikan luar biasa yang unggul dalam pengembangan potensi anak berkebutuhan khusus secara optimal, berkarakter, mandiri, dan berprestasi.',
    'misi' => 'Menyelenggarakan pendidikan yang berkualitas, mengembangkan potensi akademik dan non-akademik, serta membangun karakter, serta menjalin kerjasama dengan berbagai pihak.',
    'profil_kepala_sekolah' => 'Kepala sekolah yang inovatif, berdedikasi, dan berpengalaman dalam dunia pendidikan khusus.',
    'sambutan' => 'Pendidikan bukan sekadar menuntut ilmu, melainkan proses membentuk karakter, membangun mimpi, dan memberdayakan generasi yang akan membawa perubahan bagi bangsa. Di SLB-C YPSLB Gemolong, kami berkomitmen untuk menjadi rumah kedua bagi setiap siswa agar mereka tumbuh menjadi pribadi yang unggul dan berkarakter.',
    'alamat' => 'Jl. Pendidikan No. 1, Gemolong, Kabupaten Sragen, Jawa Tengah',
    'telepon' => '(0271) 123456',
    'email' => 'info@slbc-gemolong.sch.id',
    'gambar_gedung' => 'https://picsum.photos/seed/school-building-front/700/525.jpg',
    'struktur_organisasi' => 'https://picsum.photos/seed/struktur-organisasi/1000/600.jpg',
    'nama_kepala_sekolah' => 'Drs. Ahmad Sudrajat, M.Pd',
    'foto_kepala_sekolah' => 'https://picsum.photos/seed/kepsek-portrait/480/600.jpg',
    'instagram' => '',
    'facebook' => '',
    'youtube' => '',
    'tiktok' => '',
    'website' => '',
    'video_profil' => ''
];

$homepageSectionsPath = __DIR__ . '/includes/homepage_sections.php';
$homepageSections = [
    'hero' => true,
    'running_text' => true,
    'profil' => true,
    'tentang' => true,
    'struktur' => true,
    'sumberdaya_preview' => true,
    'program' => true,
    'fasilitas' => true,
    'prestasi' => true,
    'cta_ppdb' => true,
    'berita' => true,
    'galeri' => true,
    'statistik' => true,
    'anggaran' => true,
    'layanan' => true,
    'faq' => true,
];
if (file_exists($homepageSectionsPath)) {
    $loadedSections = include $homepageSectionsPath;
    if (is_array($loadedSections)) {
        $homepageSections = array_merge($homepageSections, $loadedSections);
    }
}

// Inisialisasi variabel
$all_siswa = [];
$guru_list = [];
$anggaran = [];
$realisasi = [];
$rencana = [];
$prestasi_list = [];

try {
    if ($supabaseConnected) {
        $heroResult = supabaseSelect('hero', ['id' => 'eq.1', 'limit' => 1]);
        if ($heroResult['success'] && !empty($heroResult['data'])) {
            $hero = array_merge($hero, $heroResult['data'][0]);
        }
        if (empty($hero['latitude'])) {
            $hero['latitude'] = '-7.4585';
        }
        if (empty($hero['longitude'])) {
            $hero['longitude'] = '110.9567';
        }
        
        // Fetch profil sekolah
        $profilResult = supabaseSelect('profil_sekolah', ['id' => 'eq.1', 'limit' => 1]);
        if ($profilResult['success'] && !empty($profilResult['data'])) {
            $profil = array_merge($profil, $profilResult['data'][0]);
        }
        
        $programResult = supabaseSelect('program', ['order' => 'urutan.asc']);
        if ($programResult['success']) {
            $programs = $programResult['data'];
        }
        
        $fasilitasResult = supabaseSelect('fasilitas', ['order' => 'urutan.asc']);
        if ($fasilitasResult['success']) {
            $fasilitas = $fasilitasResult['data'];
        }
        
        // Fetch published berita from database
        $beritaResult = supabaseSelect('berita', ['order' => 'tanggal.desc']);

        $berita = [];
        if ($beritaResult['success']) {
            // Only show published berita
            foreach ($beritaResult['data'] as $item) {
                if (($item['status'] ?? 'published') === 'published') {
                    $berita[] = $item;
                }
            }
        }

        // Fetch published galeri from database
        $galeriResult = supabaseSelect('galeri', ['order' => 'tanggal_upload.desc']);

        $galeri = [
            'Photo' => [],
            'Video' => []
        ];
        if ($galeriResult['success']) {
            // Only show published galeri
            foreach ($galeriResult['data'] as $item) {
                if (($item['status'] ?? 'published') === 'published') {
                    $jenis = $item['jenis_galeri'] ?? 'Photo';
                    if (!isset($galeri[$jenis])) {
                        $galeri[$jenis] = [];
                    }
                    $galeri[$jenis][] = $item;
                }
            }
        }

        // Fetch published FAQ from database
        $faqResult = supabaseSelect('faq', ['order' => 'urutan.asc, created_at.asc']);
        if ($faqResult['success']) {
            // Only show published FAQ
            foreach ($faqResult['data'] as $item) {
                if (($item['status'] ?? 'published') === 'published') {
                    $faqs[] = $item;
                }
            }
        }

        // Get all active students for statistics
        $siswaResult = supabaseSelect('siswa', ['status' => 'eq.Aktif', 'order' => 'no_induk.asc']);
        if ($siswaResult['success']) {
            $all_siswa = $siswaResult['data'];
            $hero['siswa_aktif'] = count($all_siswa);
        }
        
        // Get all teachers ordered by 'urutan' (database nomor urut).
        // Fallback to created_at or default query if urutan tidak tersedia.
        $guruResult = supabaseSelect('guru', ['order' => 'urutan.asc']);
        if (!$guruResult['success']) {
            $guruResult = supabaseSelect('guru', ['order' => 'created_at.asc']);
        }
        if (!$guruResult['success']) {
            $guruResult = supabaseSelect('guru', []);
        }
        if ($guruResult['success']) {
            $guru_list = $guruResult['data'];
            $hero['tenaga_pendidik'] = count($guru_list);
        }

        // Get anggaran
        $anggaranResult = supabaseSelect('anggaran_bosn', ['order' => 'tahun.desc']);
        if ($anggaranResult['success']) {
            $anggaran = $anggaranResult['data'];
        }

        // Get realisasi
        $realisasiResult = supabaseSelect('realisasi_bulanan', ['order' => 'tahun.desc']);
        if ($realisasiResult['success']) {
            $realisasi = $realisasiResult['data'];
            
            // Function to get month number from name
            function getMonthNumber($monthName) {
                $months = [
                    'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4, 'Mei' => 5, 'Juni' => 6,
                    'Juli' => 7, 'Agustus' => 8, 'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12
                ];
                return $months[$monthName] ?? 0;
            }
            
            // Sort realisasi by year and month number
            usort($realisasi, function($a, $b) {
                if ($a['tahun'] != $b['tahun']) {
                    return $b['tahun'] - $a['tahun'];
                }
                return getMonthNumber($a['bulan']) - getMonthNumber($b['bulan']);
            });
        }

        // Get rencana
        $rencanaResult = supabaseSelect('rencana_anggaran', ['order' => 'created_at.desc']);
        if ($rencanaResult['success']) {
            $rencana = $rencanaResult['data'];
        }

        // Get prestasi
        $prestasiResult = supabaseSelect('prestasi', ['order' => 'tahun.desc']);
        if ($prestasiResult['success']) {
            $prestasi_list = $prestasiResult['data'];
            $hero['total_prestasi'] = count($prestasi_list);
        }
    }
} catch (Exception $e) {}

include 'components/head.php';
?>
<body class="bg-brand-bg text-brand-dark font-sans">
  <?php include 'components/navbar.php'; ?>

  <!-- HERO -->
  <?php if (!empty($homepageSections['hero'])): ?>
  <section class="relative h-screen min-h-[600px] flex items-center justify-center overflow-hidden">
    <?php
    // Parse background images
    $bg_images = [];
    if (!empty($hero['background_images'])) {
        $bg_images = array_filter(array_map('trim', explode(',', $hero['background_images'])));
    }
    if (empty($bg_images)) {
        $bg_images = [
            'https://picsum.photos/seed/school-hero-main/1920/1080'];
    }
    ?>
    <div class="absolute inset-0 z-0">
      <?php if (!empty($hero['background_video'])): ?>
        <?php 
        // For hero background, we need to handle iframe differently
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $hero['background_video'], $matches)): 
            $videoId = $matches[1];
        ?>
            <div class="absolute inset-0 w-full h-full overflow-hidden">
                <iframe src="https://www.youtube.com/embed/<?php echo $videoId; ?>?autoplay=1&mute=1&loop=1&playlist=<?php echo $videoId; ?>&controls=0&showinfo=0&rel=0&modestbranding=1"
                        class="w-full h-full"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                </iframe>
            </div>
        <?php elseif (preg_match('/vimeo\.com\/(?:.*\/)?(\d+)/', $hero['background_video'], $matches)): 
            $videoId = $matches[1];
        ?>
            <div class="absolute inset-0 w-full h-full overflow-hidden">
                <iframe src="https://player.vimeo.com/video/<?php echo $videoId; ?>?autoplay=1&muted=1&loop=1&title=0&byline=0&portrait=0&controls=0"
                        class="w-full h-full"
                        frameborder="0"
                        allow="autoplay; fullscreen"
                        allowfullscreen>
                </iframe>
            </div>
        <?php else: ?>
            <video autoplay muted loop playsinline src="<?php echo htmlspecialchars($hero['background_video']); ?>" class="absolute inset-0 w-full h-full object-cover">
            </video>
        <?php endif; ?>
      <?php else: ?>
        <?php foreach ($bg_images as $index => $img): ?>
          <img src="<?php echo htmlspecialchars($img); ?>" 
               alt="Hero Background <?php echo $index + 1; ?>" 
               class="absolute inset-0 w-full h-full object-cover transition-opacity duration-700 <?php echo $index === 0 ? 'opacity-100' : 'opacity-0'; ?>" 
               data-slide="<?php echo $index; ?>">
        <?php endforeach; ?>
      <?php endif; ?>
      <div class="absolute inset-0 bg-brand-dark/60"></div>
    </div>
    
    <!-- Navigation -->
    <?php if (count($bg_images) > 1): ?>
    <button id="hero-prev" class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-white/40 transition-colors">
      <iconify-icon icon="lucide:chevron-left" class="w-6 h-6"></iconify-icon>
    </button>
    <button id="hero-next" class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-white/40 transition-colors">
      <iconify-icon icon="lucide:chevron-right" class="w-6 h-6"></iconify-icon>
    </button>
    
    <!-- Indicators -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 flex gap-2">
      <?php foreach ($bg_images as $index => $img): ?>
        <button data-slide="<?php echo $index; ?>" class="w-3 h-3 rounded-full transition-all <?php echo $index === 0 ? 'bg-brand-accent w-8' : 'bg-white/50'; ?>"></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <div class="relative z-10 max-w-4xl mx-auto px-6 text-center">
      <h1 class="font-bold text-4xl md:text-4xl lg:text-5xl tracking-tight mb-6 leading-[1.1]">
        <span class="hero-sweep-text px-4 py-2 bg-white/10 backdrop-blur-sm rounded-lg"><?php echo htmlspecialchars($hero['judul'] ?? 'SLB-C YPSLB Gemolong'); ?></span>
      </h1>
      <p class="text-white/80 text-lg md:text-xl max-w-2xl mx-auto mb-6"><?php echo htmlspecialchars($hero['deskripsi'] ?? ''); ?></p>
      <span class="inline-block text-sm md:text-base lg:text-lg font-bold tracking-[0.2em] uppercase text-white mb-10 px-6 py-3 rounded animate-pulse bg-brand-accent shadow-[0_0_15px_rgba(62,107,78,0.8)]"><?php echo htmlspecialchars($hero['tagline'] ?? ''); ?></span>
      <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
        <a href="<?php echo htmlspecialchars($hero['cta2_link'] ?? '#profil'); ?>" class="w-full sm:w-[180px] flex items-center justify-center px-6 py-4 border-2 border-white text-white font-bold text-xs uppercase tracking-widest rounded hover:bg-white hover:text-brand-dark transition-colors whitespace-nowrap">
          Profil Sekolah
        </a>
        <?php
        // Check if maps_url exists in profil and is accessible
        $mapsLink = '';
        if (!empty($profil['maps_url'])) {
            // If it's an embed URL, convert to clickable link
            if (strpos($profil['maps_url'], 'maps/embed') !== false) {
                // Extract coordinates from embed URL if available
                // For now, use Google Maps with address search format
                if (!empty($profil['alamat'])) {
                    $mapsLink = 'https://www.google.com/maps/search/' . urlencode($profil['alamat']);
                } elseif (!empty($hero['latitude']) && !empty($hero['longitude'])) {
                    $mapsLink = 'https://www.google.com/maps?q=' . urlencode($hero['latitude']) . ',' . urlencode($hero['longitude']);
                }
            } else {
                // Use maps_url directly if it's already a clickable link
                $mapsLink = $profil['maps_url'];
            }
        } elseif (!empty($hero['latitude']) && !empty($hero['longitude'])) {
            // Fallback to hero coordinates
            $mapsLink = 'https://www.google.com/maps?q=' . urlencode($hero['latitude']) . ',' . urlencode($hero['longitude']);
        }

        if (!empty($mapsLink)):
        ?>
            <a href="<?php echo htmlspecialchars($mapsLink); ?>" target="_blank" rel="noopener noreferrer" class="w-full sm:w-[180px] flex items-center justify-center gap-3 px-6 py-4 border-2 border-white text-white font-bold text-xs uppercase tracking-widest rounded hover:bg-white hover:text-brand-dark transition-colors whitespace-nowrap">
              <iconify-icon icon="mdi:map-marker" style="font-size: 200%; color: #3E6B4E;"></iconify-icon>
              Lokasi
            </a>
        <?php endif; ?>
        <a href="<?php echo htmlspecialchars($hero['cta1_link'] ?? 'pages/ppdb.php'); ?>" class="w-full sm:w-[180px] flex items-center justify-center px-6 py-4 border-2 border-white text-white font-bold text-xs uppercase tracking-widest rounded hover:bg-white hover:text-brand-dark transition-colors whitespace-nowrap">
          <?php echo htmlspecialchars($hero['cta1_text'] ?? ''); ?>
        </a>
      </div>
    </div>
  </section>
  
  <!-- Hero Slider Script -->
  <?php if (count($bg_images) > 1): ?>
  <script>
    (function() {
        const slides = document.querySelectorAll('[data-slide][alt^="Hero Background"]');
        const indicators = document.querySelectorAll('[data-slide].w-3');
        const prevBtn = document.getElementById('hero-prev');
        const nextBtn = document.getElementById('hero-next');
        let currentSlide = 0;
        const totalSlides = slides.length;
        let autoSlideInterval;
        
        function showSlide(index) {
            if (index < 0) index = totalSlides - 1;
            if (index >= totalSlides) index = 0;
            
            slides.forEach((slide, i) => {
                slide.classList.toggle('opacity-100', i === index);
                slide.classList.toggle('opacity-0', i !== index);
            });
            
            indicators.forEach((indicator, i) => {
                indicator.classList.toggle('w-8', i === index);
                indicator.classList.toggle('bg-brand-accent', i === index);
                indicator.classList.toggle('bg-white/50', i !== index);
            });
            
            currentSlide = index;
        }
        
        function nextSlide() {
            showSlide(currentSlide + 1);
        }
        
        function prevSlide() {
            showSlide(currentSlide - 1);
        }
        
        function startAutoSlide() {
            autoSlideInterval = setInterval(nextSlide, 5000);
        }
        
        function stopAutoSlide() {
            clearInterval(autoSlideInterval);
        }
        
        prevBtn.addEventListener('click', () => {
            stopAutoSlide();
            prevSlide();
            startAutoSlide();
        });
        
        nextBtn.addEventListener('click', () => {
            stopAutoSlide();
            nextSlide();
            startAutoSlide();
        });
        
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                stopAutoSlide();
                showSlide(index);
                startAutoSlide();
            });
        });
        
        startAutoSlide();
    })();
  </script>
  <?php endif; ?>
  
  <?php endif; ?>

  <!-- Running Text -->
  <?php if (!empty($homepageSections['running_text'])): ?>
  <section class="bg-blue-600 py-4 overflow-hidden">
    <div class="flex animate-marquee" style="width: 200%;">
      <span class="text-white font-bold text-lg mx-8 flex-shrink-0">Selamat Datang di <?php echo htmlspecialchars($hero['judul'] ?? 'SLB-C YPSLB Gemolong'); ?></span>
      <span class="text-white font-bold text-lg mx-8 flex-shrink-0">•••</span>
      <span class="text-white font-bold text-lg mx-8 flex-shrink-0">Visi: <?php echo htmlspecialchars($profil['visi'] ?? 'Menjadikan SLB-C YPSLB Gemolong sebagai lembaga pendidikan luar biasa yang unggul dalam pengembangan potensi anak berkebutuhan khusus secara optimal, berkarakter, mandiri, dan berprestasi'); ?></span>
      <span class="text-white font-bold text-lg mx-8 flex-shrink-0">•••</span>
      <span class="text-white font-bold text-lg mx-8 flex-shrink-0">Misi: <?php echo htmlspecialchars($profil['misi'] ?? 'Menyelenggarakan pendidikan yang berkualitas, mengembangkan potensi akademik dan non-akademik, serta membangun karakter, serta menjalin kerjasama dengan berbagai pihak'); ?></span>
      <span class="text-white font-bold text-lg mx-8 flex-shrink-0">•••</span>
      <!-- Duplicate for seamless loop -->
      <span class="text-white font-bold text-lg mx-8 flex-shrink-0">Selamat Datang di <?php echo htmlspecialchars($hero['judul'] ?? 'SLB-C YPSLB Gemolong'); ?></span>
      <span class="text-white font-bold text-lg mx-8 flex-shrink-0">•••</span>
      <span class="text-white font-bold text-lg mx-8 flex-shrink-0">Visi: <?php echo htmlspecialchars($profil['visi'] ?? 'Menjadikan SLB-C YPSLB Gemolong sebagai lembaga pendidikan luar biasa yang unggul dalam pengembangan potensi anak berkebutuhan khusus secara optimal, berkarakter, mandiri, dan berprestasi'); ?></span>
    </div>
  </section>
  
  <?php endif; ?>

  <!-- STATS BAR removed per request -->

  <!-- PROFIL SEKOLAH -->
  <?php if (!empty($homepageSections['profil'])): ?>
  <section id="profil" class="py-12">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center mb-8">
        <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Tentang Kami</span>
          <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Profil Sekolah</h2>
        </div>
        <div class="w-20 h-1 bg-brand-accent mx-auto"></div>
      </div>
      <div class="grid lg:grid-cols-5 gap-12 items-center">
        <div class="lg:col-span-2 relative">
          <div class="relative">
            <?php if (!empty($profil['video_profil'])): ?>
            <div class="w-full h-[450px] rounded-lg shadow-lg overflow-hidden">
              <?php echo getVideoEmbed($profil['video_profil'], '100%', '100%'); ?>
            </div>
          <?php else: ?>
            <img src="<?php echo htmlspecialchars($profil['gambar_gedung'] ?? 'https://picsum.photos/seed/school-building/480/600.jpg'); ?>" alt="Gedung Sekolah" class="w-full h-[450px] object-cover rounded-lg shadow-lg">
          <?php endif; ?>
            <div class="absolute -bottom-4 -right-4 w-24 h-24 border-2 border-brand-accent rounded-lg"></div>
            <div class="absolute -top-4 -left-4 w-24 h-24 border-2 border-brand-accent/30 rounded-lg"></div>
          </div>
        </div>
        <div class="lg:col-span-3">
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white bg-brand-accent px-4 py-2 rounded">Sambutan Kepala Sekolah</span>
          <h2 class="font-serif text-4xl md:text-5xl font-normal tracking-tight mt-4 mb-6 leading-[1.1]">Selamat Datang di <em><?php echo htmlspecialchars($profil['nama_sekolah'] ?? 'SLB-C YPSLB Gemolong'); ?></em></h2>
          <div class="relative pl-6 border-l-2 border-brand-accent mb-6">
            <iconify-icon icon="lucide:quote" class="text-brand-accent/30 text-4xl absolute -left-5 -top-2 bg-brand-bg px-1"></iconify-icon>
            <p class="text-brand-muted text-sm font-light leading-relaxed italic text-justify">
              "<?php echo nl2br(htmlspecialchars($profil['sambutan'] ?? 'Pendidikan bukan sekadar menuntut ilmu, melainkan proses membentuk karakter, membangun mimpi, dan mempersiapkan generasi yang akan membawa perubahan bagi bangsa.')); ?>"
            </p>
          </div>
          <div class="flex items-center gap-4">
            <img src="<?php echo htmlspecialchars($profil['foto_kepala_sekolah'] ?? 'https://picsum.photos/seed/kepsek/100/100.jpg'); ?>" alt="Kepala Sekolah" class="w-16 h-16 rounded-full object-cover border-2 border-brand-accent/30">
            <div>
              <h4 class="font-serif text-lg font-semibold"><?php echo htmlspecialchars($profil['nama_kepala_sekolah'] ?? 'Drs. Ahmad Sudrajat, M.Pd'); ?></h4>
              <p class="text-xs text-brand-muted">Kepala Sekolah <?php echo htmlspecialchars($profil['nama_sekolah'] ?? 'SLB-C YPSLB Gemolong'); ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php endif; ?>

  <!-- Tentang Sekolah -->
  <?php if (!empty($homepageSections['tentang'])): ?>
  <section class="py-24 bg-brand-bg/50">
    <div class="max-w-7xl mx-auto px-6">
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        
        <!-- Kolom 1: Sejarah -->
        <div class="bg-white p-8 rounded-2xl shadow-lg border border-brand-border/30 overflow-hidden">
          <div class="bg-brand-accent -mx-8 -mt-8 px-8 py-4 mb-6">
            <h3 class="font-serif text-2xl font-semibold text-center text-white">Sejarah</h3>
          </div>
          <p class="text-brand-muted text-sm leading-relaxed mb-6 text-justify">
            <?php echo nl2br(htmlspecialchars($profil['sejarah'] ?? 'SLB-C YPSLB Gemolong didirikan dengan tujuan memberikan pendidikan terbaik untuk anak berkebutuhan khusus.')); ?>
          </p>
          <div class="flex justify-center">
            <a href="<?= BASE_URL ?>/pages/profil.php#sejarah" class="px-8 py-3 border-2 border-brand-dark text-brand-dark font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-brand-dark hover:text-white transition-colors">
              Lihat Selengkapnya
            </a>
          </div>
        </div>

        <!-- Kolom 2: Dasar Hukum -->
        <div class="bg-white p-8 rounded-2xl shadow-lg border border-brand-border/30 overflow-hidden">
          <div class="bg-brand-accent -mx-8 -mt-8 px-8 py-4 mb-6">
            <h3 class="font-serif text-2xl font-semibold text-center text-white">Dasar Hukum</h3>
          </div>
          <div class="text-brand-muted text-sm leading-relaxed mb-6">
            <?php 
            $dasarHukum = $profil['dasar_hukum'] ?? '1. Undang-Undang Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional
2. Peraturan Pemerintah Nomor 19 Tahun 2005 tentang Pendidikan Anak Berkebutuhan Khusus
3. Peraturan Menteri Pendidikan dan Kebudayaan Nomor 70 Tahun 2013 tentang Pendidikan Dasar
4. Peraturan Daerah Provinsi Jawa Tengah Nomor 12 Tahun 2018 tentang Pendidikan Luar Biasa
5. Akta Notaris Pendirian Yayasan YPSLB Gemolong';
            $lines = explode("\n", $dasarHukum);
            foreach ($lines as $line) {
                $trimmedLine = trim($line);
                if (!empty($trimmedLine)) {
                    echo '<div class="mb-3 text-justify">'. htmlspecialchars($trimmedLine) . '</div>';
                }
            }
            ?>
          </div>
          <div class="flex justify-center">
            <a href="<?= BASE_URL ?>/pages/profil.php#dasar-hukum" class="px-8 py-3 border-2 border-brand-dark text-brand-dark font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-brand-dark hover:text-white transition-colors">
              Lihat Selengkapnya
            </a>
          </div>
        </div>

        <!-- Kolom 3: Visi & Misi -->
        <div class="bg-white p-8 rounded-2xl shadow-lg border border-brand-border/30 overflow-hidden">
          <div class="bg-brand-accent -mx-8 -mt-8 px-8 py-4 mb-6">
            <h3 class="font-serif text-2xl font-semibold text-center text-white">Visi Misi</h3>
          </div>
          
          <h4 class="font-semibold text-lg text-center mb-2 text-brand-dark">Visi</h4>
          <p class="text-brand-muted text-sm mb-6 text-justify">
            <?php echo nl2br(htmlspecialchars($profil['visi'] ?? 'Menjadikan SLB-C YPSLB Gemolong sebagai lembaga pendidikan luar biasa yang unggul.')); ?>
          </p>
          
          <div class="border-t border-brand-border/50 my-6"></div>
          
          <h4 class="font-semibold text-lg text-center mb-2 text-brand-dark">Misi</h4>
          <div class="text-brand-muted text-sm mb-6 text-justify">
            <?php 
            $misiText = $profil['misi'] ?? 'Menyelenggarakan pendidikan yang berkualitas, mengembangkan potensi, dan membangun karakter.';
            $lines = explode("\n", $misiText);
            foreach ($lines as $index => $line) {
                $trimmedLine = trim($line);
                if (!empty($trimmedLine)) {
                    // Check if line starts with a number (digit)
                    if (preg_match('/^\d/', $trimmedLine)) {
                        echo '<div class="mb-2">' . htmlspecialchars($line) . '</div>';
                    } else {
                        echo '<div class="mb-2" style="padding-left: 1.5rem;">' . htmlspecialchars($line) . '</div>';
                    }
                }
            }
            ?>
          </div>
          
          <div class="flex justify-center">
            <a href="<?= BASE_URL ?>/pages/profil.php#visimisi" class="px-8 py-3 border-2 border-brand-dark text-brand-dark font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-brand-dark hover:text-white transition-colors">
              Lihat Selengkapnya
            </a>
          </div>
        </div>
        
      </div>
    </div>
  </section>

  <?php endif; ?>

  <!-- STRUKTUR ORGANISASI -->
  <?php if (!empty($homepageSections['struktur'])): ?>
  <section id="struktur" class="py-12">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center mb-8">
        <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Organisasi</span>
          <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Struktur Organisasi</h2>
        </div>
        <div class="w-20 h-1 bg-brand-accent mx-auto"></div>
      </div>
      
      <div class="flex justify-center">
        <img src="<?php echo htmlspecialchars($profil['struktur_organisasi'] ?? 'https://picsum.photos/seed/struktur-organisasi/1000/600.jpg'); ?>" alt="Struktur Organisasi" class="w-full max-w-6xl h-auto rounded-lg shadow-lg border border-brand-border/30">
      </div>
    </div>
  </section>

  <?php endif; ?>

  <!-- SUMBER DAYA MANUSIA PREVIEW -->
  <?php if (!empty($homepageSections['sumberdaya_preview'])): ?>
  <section id="sumberdaya-preview" class="py-12 bg-brand-bg/50">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center mb-8">
        <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Profil</span>
          <h2 class="font-serif text-3xl md:text-4xl text-white mb-3">Sumber Daya Manusia</h2>
        </div>
        <div class="w-20 h-1 bg-gradient-to-r from-amber-500 to-orange-600 mx-auto"></div>
        <p class="mt-4 max-w-2xl mx-auto text-sm text-brand-muted">
          Lihat informasi SDM sesuai database guru.
          <a href="pages/profil.php#sumberdaya" class="font-semibold text-brand-accent hover:text-brand-dark">Kunjungi halaman Profil SDM</a>.
        </p>
      </div>

      <?php
        // Build a single set of slide HTML and duplicate it for smooth looping.
        ob_start();
        if (!empty($guru_list)) {
            foreach ($guru_list as $index => $guru) {
    ?>
              <a href="pages/profil.php#sumberdaya" class="group min-w-[280px] max-w-[280px] flex-shrink-0 overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div class="h-64 overflow-hidden bg-gray-100">
                  <img src="<?= htmlspecialchars($guru['foto'] ?? 'https://picsum.photos/seed/guru'.($index + 1).'/520/380') ?>" alt="<?= htmlspecialchars($guru['nama'] ?? 'SDM') ?>" class="h-full w-full object-cover object-center transition duration-500 group-hover:scale-105" />
                </div>
                <div class="p-5">
                  <p class="text-xs uppercase tracking-[0.24em] text-brand-label mb-2"><?= htmlspecialchars($guru['jabatan'] ?? 'Tenaga Pendidik') ?></p>
                  <h3 class="font-serif text-xl text-brand-dark"><?= htmlspecialchars($guru['nama'] ?? 'Nama SDM') ?></h3>
                  <?php if (!empty($guru['mapel'])): ?>
                    <p class="text-xs text-brand-muted mt-2">Mapel: <?= htmlspecialchars($guru['mapel']) ?></p>
                  <?php endif; ?>
                </div>
              </a>
    <?php
            }
        } else {
            // Fallback default slides
            $defaults = [
                ['nama' => 'Guru', 'jabatan' => 'Tenaga Pendidik', 'foto' => 'https://picsum.photos/seed/sdm1/520/380'],
                ['nama' => 'Tendik', 'jabatan' => 'Tenaga Kependidikan', 'foto' => 'https://picsum.photos/seed/sdm2/520/380'],
                ['nama' => 'Administrasi', 'jabatan' => 'Staf Sekolah', 'foto' => 'https://picsum.photos/seed/sdm3/520/380'],
                ['nama' => 'Kepala Sekolah', 'jabatan' => 'Manajemen', 'foto' => 'https://picsum.photos/seed/sdm4/520/380'],
            ];
            foreach ($defaults as $index => $g) {
    ?>
              <a href="pages/profil.php#sumberdaya" class="group min-w-[280px] max-w-[280px] flex-shrink-0 overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div class="h-64 overflow-hidden bg-gray-100">
                  <img src="<?= htmlspecialchars($g['foto']) ?>" alt="<?= htmlspecialchars($g['nama']) ?>" class="h-full w-full object-cover object-center transition duration-500 group-hover:scale-105" />
                </div>
                <div class="p-5">
                  <p class="text-xs uppercase tracking-[0.24em] text-brand-label mb-2"><?= htmlspecialchars($g['jabatan']) ?></p>
                  <h3 class="font-serif text-xl text-brand-dark"><?= htmlspecialchars($g['nama']) ?></h3>
                </div>
              </a>
    <?php
            }
        }
        $singleSlidesHtml = ob_get_clean();
      ?>

      <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-lg">
        <div class="marquee-track" aria-hidden="false">
          <?= $singleSlidesHtml . $singleSlidesHtml ?>
        </div>
      </div>

      <style>
        .marquee-track { display:flex; flex-wrap:nowrap; gap:24px; padding:24px; align-items:stretch; }
        .marquee-track a { flex: 0 0 280px; }
        @keyframes marquee {
          from { transform: translateX(0); }
          to { transform: translateX(-50%); }
        }
        .marquee-track { animation: marquee 20s linear infinite; will-change: transform; }
        .marquee-track:hover { animation-play-state: paused; }
      </style>
    </div>
  </section>

  <?php endif; ?>

  <!-- PROGRAM UNGGULAN -->
  <?php if (!empty($homepageSections['program'])): ?>
  <section id="program" class="py-12 bg-gray-100">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center mb-8">
        <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Program Kami</span>
          <h2 class="font-serif text-3xl md:text-4xl text-white mb-3">Program Unggulan</h2>
        </div>
        <div class="w-20 h-1 bg-gradient-to-r from-amber-500 to-orange-600 mx-auto"></div>
      </div>
      
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6 mb-12">
        <?php 
        $default_programs = [
          ['nama' => 'Pendidikan Akademik'],
          ['nama' => 'Keterampilan Vokasi'],
          ['nama' => 'Olahraga & Seni'],
          ['nama' => 'Bimbingan Konseling'],
          ['nama' => 'Ekstrakurikuler'],
          ['nama' => 'Pengembangan Karakter']
        ];
        $total_programs = empty($programs) ? count($default_programs) : count($programs);
        
        if (empty($programs)):
          foreach ($default_programs as $index => $prog):
            $delay = $index * 0.16; ?>
            <div class="reveal-card flex flex-col items-center text-center group p-3 rounded-lg border bg-white border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-sm" style="animation-delay: <?= $delay ?>s;">
              <iconify-icon icon="mdi:book-open-page-variant" class="w-6 h-6 mx-auto mb-3 text-amber-500" style="font-size: 40px;"></iconify-icon>
              <h3 class="font-bold text-brand-dark text-xs md:text-sm"><?php echo htmlspecialchars($prog['nama']); ?></h3>
            </div>
          <?php endforeach;
        else:
          foreach ($programs as $index => $prog):
            $delay = $index * 0.16; ?>
            <div class="reveal-card flex flex-col items-center text-center group p-3 rounded-lg border bg-white border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-sm" style="animation-delay: <?= $delay ?>s;">
              <iconify-icon icon="mdi:book-open-page-variant" class="w-6 h-6 mx-auto mb-3 text-amber-500" style="font-size: 40px;"></iconify-icon>
              <h3 class="font-bold text-brand-dark text-xs md:text-sm"><?php echo htmlspecialchars($prog['nama'] ?? ''); ?></h3>
            </div>
          <?php endforeach;
        endif; ?>
      </div>

      <div class="text-center">
        <a href="pages/program.php" class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold text-sm uppercase tracking-widest rounded-lg hover:from-amber-600 hover:to-orange-700 transition-all shadow-lg hover:shadow-xl">
          Lihat Detail Program
        </a>
      </div>
    </div>
  </section>

  <?php endif; ?>

  <!-- FASILITAS -->
  <?php if (!empty($homepageSections['fasilitas'])): ?>
  <section id="fasilitas" class="py-12 bg-brand-bg/50">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center mb-8">
        <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Sarana Prasarana</span>
          <h2 class="font-serif text-3xl md:text-4xl text-white mb-3">Fasilitas</h2>
        </div>
        <div class="w-20 h-1 bg-gradient-to-r from-teal-600 to-teal-800 mx-auto"></div>
      </div>
      
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6 mb-12">
        <?php 
        $default_fasilitas = [
          ['nama' => 'Ruang Kelas'],
          ['nama' => 'Ruang Kepala Sekolah'],
          ['nama' => 'Ruang Guru'],
          ['nama' => 'Perpustakaan'],
          ['nama' => 'Laboratorium'],
          ['nama' => 'Fasilitas Lainnya']
        ];
        $total_fasilitas = empty($fasilitas) ? count($default_fasilitas) : count($fasilitas);
        
        if (empty($fasilitas)):
          foreach ($default_fasilitas as $index => $f):
            $delay = $index * 0.16; ?>
            <div class="reveal-card flex flex-col items-center text-center group p-3 rounded-lg border bg-white border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-sm" style="animation-delay: <?= $delay ?>s;">
              <iconify-icon icon="mdi:office-building" class="w-6 h-6 mx-auto mb-3 text-teal-700" style="font-size: 40px;"></iconify-icon>
              <h3 class="font-bold text-brand-dark text-xs md:text-sm"><?php echo htmlspecialchars($f['nama']); ?></h3>
            </div>
          <?php endforeach;
        else:
          foreach ($fasilitas as $index => $f):
            $delay = $index * 0.16; ?>
            <div class="reveal-card flex flex-col items-center text-center group p-3 rounded-lg border bg-white border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-sm" style="animation-delay: <?= $delay ?>s;">
              <iconify-icon icon="mdi:office-building" class="w-6 h-6 mx-auto mb-3 text-teal-700" style="font-size: 40px;"></iconify-icon>
              <h3 class="font-bold text-brand-dark text-xs md:text-sm"><?php echo htmlspecialchars($f['nama'] ?? ''); ?></h3>
            </div>
          <?php endforeach;
        endif; ?>
      </div>
      
      <div class="text-center">
        <a href="pages/fasilitas.php" class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-teal-600 to-teal-800 text-white font-bold text-sm uppercase tracking-widest rounded-lg hover:from-teal-700 hover:to-teal-900 transition-all shadow-lg hover:shadow-xl">
          Lihat Detail Fasilitas
        </a>
      </div>
    </div>
  </section>

  <?php endif; ?>

  <!-- PRESTASI -->
  <?php if (!empty($homepageSections['prestasi'])): ?>
  <section id="prestasi" class="py-12 bg-gray-100">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center mb-8">
        <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Prestasi</span>
          <h2 class="font-serif text-3xl md:text-4xl text-white mb-3">Prestasi </h2>
        </div>
        <div class="w-20 h-1 bg-brand-accent mx-auto"></div>
      </div>

      <?php if (empty($prestasi_list)): ?>
        <div class="col-span-full text-center py-16 bg-brand-bg/50 rounded-xl">
          <iconify-icon icon="lucide:trophy" class="text-6xl text-brand-muted/30 mb-4"></iconify-icon>
          <p class="text-brand-muted">Belum ada prestasi.</p>
        </div>
      <?php else: ?>
        <?php
          $prestasi_items_per_page = 3; // 1 baris (lg:grid-cols-3)
          $prestasi_total_items = count($prestasi_list);
          $prestasi_total_pages = ceil($prestasi_total_items / $prestasi_items_per_page);
        ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="prestasiContainer">
          <?php
          $prestasi_index = 0;
          foreach ($prestasi_list as $prestasi): ?>
            <div class="bg-white rounded-xl border border-brand-border/30 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 prestasi-item" data-index="<?php echo $prestasi_index; ?>">
              <img src="<?php echo htmlspecialchars($prestasi['foto'] ?? 'https://picsum.photos/seed/prestasi-' . $prestasi_index . '/400/250.jpg'); ?>" alt="<?php echo htmlspecialchars($prestasi['nama']); ?>" class="w-full h-48 object-cover">
              <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-yellow-500 to-orange-600 flex items-center justify-center">
                    <iconify-icon icon="lucide:medal" class="text-white"></iconify-icon>
                  </div>
                  <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label">
                    <?php echo htmlspecialchars($prestasi['kategori'] ?? 'Umum'); ?> • <?php echo htmlspecialchars($prestasi['tahun'] ?? '-'); ?>
                  </span>
                </div>
                <h3 class="font-serif text-xl text-brand-dark mb-2"><?php echo htmlspecialchars($prestasi['nama']); ?></h3>
                <p class="text-brand-muted text-sm font-light">
                  <?php echo htmlspecialchars($prestasi['peraih'] ?? '-'); ?><?php if (!empty($prestasi['lokasi'])): ?> — <?php echo htmlspecialchars($prestasi['lokasi']); ?><?php endif; ?>
                </p>
              </div>
            </div>
          <?php $prestasi_index++; endforeach; ?>
        </div>
        <?php if ($prestasi_total_pages > 1): ?>
          <div class="flex items-center justify-center gap-4 mt-12" id="prestasiPagination">
            <button id="prestasiPrevBtn" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
              <iconify-icon icon="lucide:chevron-left" class="w-5 h-5 inline-block mr-1"></iconify-icon> Sebelumnya
            </button>
            <span class="text-gray-600 font-medium">Halaman <span id="prestasiCurrentPage">1</span> dari <?php echo $prestasi_total_pages; ?></span>
            <button id="prestasiNextBtn" class="px-6 py-2 bg-brand-accent hover:bg-brand-accent-hover text-white font-semibold rounded-lg transition-colors">
              Selanjutnya <iconify-icon icon="lucide:chevron-right" class="w-5 h-5 inline-block ml-1"></iconify-icon>
            </button>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </section>

  <?php endif; ?>

  <!-- CTA PPDB -->
  <?php if (!empty($homepageSections['cta_ppdb'])): ?>
  <style>
    .cta-slide-in-left { opacity: 1; transform: translateX(0); animation: ctaSlideFloat 2.8s ease-in-out infinite alternate; }
    .cta-fade-in { opacity: 0; animation: ctaFadeIn 0.9s ease-out forwards; }
    .cta-delay-200 { animation-delay: 0.4s; }
    .cta-delay-300 { animation-delay: 0.7s; }
    .cta-typing {
      display: inline-block;
      overflow: hidden;
      white-space: nowrap;
      border-right: .14em solid rgba(255, 255, 255, 0.8);
      animation: ctaTyping 4s steps(28,end) infinite, ctaBlinkCaret .75s step-end infinite;
      opacity: 1;
    }
    .cta-button-blink {
      animation: ctaButtonBlink 1.5s ease-in-out infinite;
    }
    @keyframes ctaSlideFloat {
      from { transform: translateX(-8px); }
      to { transform: translateX(8px); }
    }
    @keyframes ctaFadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes ctaTyping {
      0% { width: 0; }
      75% { width: 100%; }
      100% { width: 100%; }
    }
    @keyframes ctaBlinkCaret {
      50% { border-color: transparent; }
    }
    @keyframes ctaButtonBlink {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.75; transform: scale(0.98); }
    }
  </style>
  <section class="py-3 relative overflow-visible" style="background-image: url('<?php echo ASSETS_URL; ?>/images/Back_batik.png'); background-size: cover; background-position: center;">
    <div class="absolute inset-0 bg-black/40"></div>
    <div class="absolute inset-0 bg-[url('<?php echo ASSETS_URL; ?>/images/texture-pattern-light.png')] opacity-10"></div>
    <div class="max-w-6xl mx-auto px-6 relative z-10">
      <div class="flex flex-col items-center justify-center gap-8 md:flex-row md:items-center md:justify-center text-center">
        <div class="flex-shrink-0 cta-slide-in-left">
          <img src="assets/images/Selamat_datang.png" alt="Selamat Datang" class="h-[520px] md:h-[560px] w-auto rounded-none shadow-none object-contain" />
        </div>
        <div class="max-w-xl">
          <h2 class="font-serif text-2xl md:text-4xl text-blue-900 font-bold mb-4 cta-typing"><span class="whitespace-nowrap">Bergabung Bersama Kami</span></h2>
          <p class="text-blue-800 font-bold text-lg md:text-xl mb-6 cta-fade-in cta-delay-200">Daftarkan putra-putri Anda di SLB-C YPSLB Gemolong</p>
          <a href="pages/ppdb.php" class="inline-block px-10 py-4 bg-white text-orange-600 font-bold text-xs uppercase tracking-widest rounded hover:bg-orange-100 hover:text-orange-700 transition-colors cta-fade-in cta-delay-300 cta-button-blink">Daftar Sekarang</a>
        </div>
      </div>
    </div>
  </section>

  <?php endif; ?>

  <!-- BERITA TERBARU -->
  <?php if (!empty($homepageSections['berita'])): ?>
  <section id="berita" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center mb-16">
        <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Informasi Terbaru</span>
          <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Berita Terbaru</h2>
        </div>
        <div class="w-20 h-1 bg-brand-accent mx-auto"></div>
      </div>

      <?php if (!empty($berita)): ?>
        <?php 
          $berita_items_per_page = 4; // 1 baris (lg:grid-cols-4)
          $berita_total_items = count($berita);
          $berita_total_pages = ceil($berita_total_items / $berita_items_per_page);
        ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6" id="beritaContainer">
          <?php 
            $berita_index = 0;
            foreach (array_slice($berita, 0, 8) as $item): 
          ?>
            <article class="bg-white rounded-xl border border-brand-border/30 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-2 berita-item" data-index="<?php echo $berita_index; ?>">
              <div class="relative overflow-hidden">
                <img src="<?php echo htmlspecialchars($item['gambar'] ?? 'https://picsum.photos/seed/' . urlencode($item['judul']) . '/800/400'); ?>" 
                     alt="<?php echo htmlspecialchars($item['judul'] ?? ''); ?>" 
                     class="w-full h-56 object-cover transition-transform duration-500 hover:scale-110">
                <div class="absolute top-4 left-4">
                  <span class="px-3 py-1 bg-brand-accent text-white text-xs font-bold rounded-full uppercase tracking-wider">
                    <?php echo htmlspecialchars($item['kategori'] ?? 'Umum'); ?>
                  </span>
                </div>
              </div>
              <div class="p-6">
                <div class="flex items-center gap-2 mb-3 text-xs text-brand-muted">
                  <iconify-icon icon="lucide:calendar" class="w-4 h-4"></iconify-icon>
                  <?php 
                  $tanggal = isset($item['tanggal_upload']) ? new DateTime($item['tanggal_upload']) : new DateTime();
                  setlocale(LC_TIME, 'id_ID.UTF-8');
                  $tanggalFormatted = strftime('%d %B %Y', $tanggal->getTimestamp());
                  echo htmlspecialchars($tanggalFormatted);
                  ?>
                </div>
                <h3 class="font-serif text-xl font-semibold text-brand-dark mb-3 line-clamp-2">
                  <?php echo htmlspecialchars($item['judul'] ?? ''); ?>
                </h3>
                <p class="text-sm text-brand-muted line-clamp-3 mb-4">
                  <?php echo htmlspecialchars(strip_tags($item['konten'] ?? '')); ?>
                </p>
                <a href="pages/berita.php?id=<?php echo urlencode($item['id'] ?? ''); ?>" class="inline-flex items-center gap-2 text-brand-accent font-semibold text-sm hover:gap-3 transition-all">
                  Baca Selengkapnya
                  <iconify-icon icon="lucide:arrow-right" class="w-4 h-4"></iconify-icon>
                </a>
              </div>
            </article>
          <?php $berita_index++; endforeach; ?>
        </div>
        <?php if ($berita_total_pages > 1): ?>
          <div class="flex items-center justify-center gap-4 mt-12" id="beritaPagination">
            <button id="beritaPrevBtn" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
              <iconify-icon icon="lucide:chevron-left" class="w-5 h-5 inline-block mr-1"></iconify-icon> Sebelumnya
            </button>
            <span class="text-gray-600 font-medium">Halaman <span id="beritaCurrentPage">1</span> dari <?php echo $berita_total_pages; ?></span>
            <button id="beritaNextBtn" class="px-6 py-2 bg-brand-accent hover:bg-brand-accent-hover text-white font-semibold rounded-lg transition-colors">
              Selanjutnya <iconify-icon icon="lucide:chevron-right" class="w-5 h-5 inline-block ml-1"></iconify-icon>
            </button>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="text-center py-16 bg-brand-bg/50 rounded-xl">
          <iconify-icon icon="lucide:newspaper" class="text-6xl text-brand-muted/30 mb-4"></iconify-icon>
          <p class="text-brand-muted">Belum ada berita terbaru.</p>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <?php endif; ?>

  <!-- GALERI -->
  <?php if (!empty($homepageSections['galeri'])): ?>
  <section id="galeri" class="py-24">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center mb-16">
        <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Galeri</span>
          <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Dokumentasi dan Galeri</h2>
        </div>
        <div class="w-20 h-1 bg-brand-accent mx-auto"></div>
      </div>

      <?php foreach ($galeri as $jenis => $items): ?>
        <?php if (!empty($items)): ?>
          <?php 
            $galeri_items_per_page = $jenis === 'Photo' ? 4 : 2; // 1 baris (Photo: 4 kolom, Video: 2 kolom
            $galeri_total_items = count($items);
            $galeri_total_pages = ceil($galeri_total_items / $galeri_items_per_page);
          ?>
          <div class="mb-16 last:mb-0">
            <div class="bg-blue-100 py-4 px-6 mb-8 -mx-6 md:mx-0 rounded-md">
              <h3 class="font-serif text-2xl text-brand-dark flex items-center gap-3">
                <iconify-icon icon="lucide:<?php echo $jenis === 'Photo' ? 'image' : 'video'; ?>" class="w-6 h-6 text-brand-accent"></iconify-icon>
                <?php echo $jenis; ?>
              </h3>
            </div>
            <div class="grid <?php echo $jenis === 'Photo' ? 'md:grid-cols-4' : 'md:grid-cols-2'; ?> gap-8" id="galeriContainer-<?php echo $jenis; ?>">
              <?php 
                $galeri_index = 0;
                foreach ($items as $item): 
              ?>
                <div class="bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition-shadow group border border-brand-border/30 galeri-item-<?php echo $jenis; ?>" data-index="<?php echo $galeri_index; ?>">
                  <div class="relative overflow-hidden <?php echo $jenis === 'Video' ? 'h-80' : 'h-56'; ?>">
                    <?php 
                    $fileUrl = $item['file_url'] ?? 'https://picsum.photos/seed/' . urlencode($item['judul']) . '/600/400';
                    $isVideo = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION)) === 'mp4' || strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION)) === 'webm' || strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION)) === 'ogg' || strpos(strtolower($fileUrl), 'video') !== false;
                    if ($isVideo): 
                    ?>
                      <video controls class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <source src="<?php echo htmlspecialchars($fileUrl); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                      </video>
                    <?php else: ?>
                      <img src="<?php echo htmlspecialchars($fileUrl); ?>" alt="<?php echo htmlspecialchars($item['judul'] ?? ''); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                      <?php if ($jenis === 'Video'): ?>
                        <div class="absolute inset-0 bg-brand-dark/30 flex items-center justify-center">
                          <iconify-icon icon="lucide:play-circle" class="w-16 h-16 text-white"></iconify-icon>
                        </div>
                      <?php endif; ?>
                    <?php endif; ?>
                  </div>
                  <div class="p-6">
                    <h4 class="font-serif text-lg text-blue-800 mb-2"><?php echo htmlspecialchars($item['judul'] ?? ''); ?></h4>
                    <div class="text-xs text-blue-300 mb-3">
                      <iconify-icon icon="lucide:calendar" class="inline-block mr-1"></iconify-icon>
                      <?php 
                      // Format to long date (e.g., 10 Juni 2024)
                      $tanggal = isset($item['tanggal_upload']) ? new DateTime($item['tanggal_upload']) : new DateTime();
                      setlocale(LC_TIME, 'id_ID.UTF-8');
                      $tanggalFormatted = strftime('%d %B %Y', $tanggal->getTimestamp());
                      echo htmlspecialchars($tanggalFormatted);
                      ?>
                    </div>
                    <p class="text-brand-muted text-sm mb-4 line-clamp-3"><?php echo htmlspecialchars($item['konten'] ?? ''); ?></p>
                  </div>
                </div>
              <?php $galeri_index++; endforeach; ?>
            </div>
            <?php if ($galeri_total_pages > 1): ?>
              <div class="flex items-center justify-center gap-4 mt-8" id="galeriPagination-<?php echo $jenis; ?>">
                <button id="galeriPrevBtn-<?php echo $jenis; ?>" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                  <iconify-icon icon="lucide:chevron-left" class="w-5 h-5 inline-block mr-1"></iconify-icon> Sebelumnya
                </button>
                <span class="text-gray-600 font-medium">Halaman <span id="galeriCurrentPage-<?php echo $jenis; ?>">1</span> dari <?php echo $galeri_total_pages; ?></span>
                <button id="galeriNextBtn-<?php echo $jenis; ?>" class="px-6 py-2 bg-brand-accent hover:bg-brand-accent-hover text-white font-semibold rounded-lg transition-colors">
                  Selanjutnya <iconify-icon icon="lucide:chevron-right" class="w-5 h-5 inline-block ml-1"></iconify-icon>
                </button>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php if (empty($galeri['Photo']) && empty($galeri['Video'])): ?>
        <div class="text-center py-16">
          <iconify-icon icon="lucide:folder-open" class="text-6xl text-brand-muted/30 mb-4"></iconify-icon>
          <p class="text-brand-muted">Belum ada galeri.</p>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <?php if (!empty($homepageSections['statistik'])): ?>
    <?php include __DIR__ . '/components/section-statistik.php'; ?>
  <?php endif; ?>

  <?php endif; ?>

  <!-- ANGGARAN DAN BELANJA -->
  <?php if (!empty($homepageSections['anggaran'])): ?>
    <?php include __DIR__ . '/components/section-anggaran.php'; ?>
  <?php endif; ?>

  <!-- LAYANAN ONLINE -->
  <?php if (!empty($homepageSections['layanan'])): ?>
  <section id="layanan" class="py-24 bg-brand-bg/50">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center mb-16">
        <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Layanan</span>
          <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Layanan Online</h2>
        </div>
        <div class="w-20 h-1 bg-brand-accent mx-auto"></div>
      </div>

      <div class="grid md:grid-cols-3 gap-8 mb-12">
        <!-- Tombol 1: Formulir Surat Menyurat -->
        <button onclick="openModal('modal-surat')" class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg border border-blue-700 px-8 py-3 text-center hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group">
          <div class="mb-2">
            <iconify-icon icon="lucide:mail" class="mx-auto group-hover:scale-110 transition-transform" style="font-size: 64px; color: white;"></iconify-icon>
          </div>
          <h3 class="font-serif text-lg text-white mb-1">Formulir Surat Menyurat</h3>
          <p class="text-blue-100 text-xs">Ajukan permohonan surat secara online</p>
        </button>

        <!-- Tombol 2: Layanan Pengaduan WhatsApp -->
        <button onclick="openModal('modal-whatsapp')" class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg border border-green-700 px-8 py-3 text-center hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group">
          <div class="mb-2">
            <iconify-icon icon="logos:whatsapp-icon" class="mx-auto group-hover:scale-110 transition-transform" style="font-size: 64px;"></iconify-icon>
          </div>
          <h3 class="font-serif text-lg text-white mb-1">Layanan Pengaduan WhatsApp</h3>
          <p class="text-green-100 text-xs">Sampaikan keluhan dan aspirasi</p>
        </button>

        <!-- Tombol 3: FAQ -->
        <a href="<?= BASE_URL ?>/pages/faq.php" class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg border border-purple-700 px-8 py-3 text-center hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group">
          <div class="mb-2">
            <iconify-icon icon="lucide:help-circle" class="mx-auto group-hover:scale-110 transition-transform" style="font-size: 64px; color: white;"></iconify-icon>
          </div>
          <h3 class="font-serif text-lg text-white mb-1">FAQ</h3>
          <p class="text-purple-100 text-xs">Pertanyaan yang sering diajukan</p>
        </a>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- MODAL FORMULIR SURAT -->
  <div id="modal-surat" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
      <div class="p-6 border-b border-brand-border/30 flex items-center justify-between">
        <h3 class="text-xl font-semibold text-brand-dark">Formulir Surat Menyurat</h3>
        <button onclick="closeModal('modal-surat')" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors">
          <iconify-icon icon="lucide:x" class="w-6 h-6"></iconify-icon>
        </button>
      </div>
      <div class="p-6">
        <form id="form-surat" class="space-y-6">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
            <input type="text" name="nama" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Masukkan nama lengkap Anda">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
            <input type="email" name="email" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="nama@email.com">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor HP/WA</label>
            <input type="tel" name="no_hp" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="081234567890">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Jenis Surat</label>
            <select id="jenis_surat" name="jenis_surat" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
              <option value="">-- Pilih Jenis Surat --</option>
              <option value="Surat Keterangan">Surat Keterangan</option>
              <option value="Surat Permohonan Mutasi">Surat Permohonan Mutasi</option>
              <option value="Surat Kesediaan Menerima">Surat Kesediaan Menerima</option>
              <option value="Surat Rekomendasi">Surat Rekomendasi</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>

          <div id="lainnya_container" class="hidden">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Surat Lainnya</label>
            <input type="text" name="jenis_surat_lainnya" id="jenis_surat_lainnya" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Masukkan jenis surat lainnya">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan / Pesan</label>
            <textarea name="keterangan" rows="4" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Masukkan keterangan atau pesan Anda..."></textarea>
          </div>

          <div class="pt-4 flex gap-3">
            <button type="button" onclick="closeModal('modal-surat')" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-4 px-6 rounded-lg transition-all duration-300">
              Batal
            </button>
            <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-4 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2">
              <iconify-icon icon="lucide:send"></iconify-icon> Kirim
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- MODAL WHATSAPP -->
  <div id="modal-whatsapp" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl w-full max-w-lg mx-4">
      <div class="p-6 border-b border-brand-border/30 flex items-center justify-between">
        <h3 class="text-xl font-semibold text-brand-dark">Layanan Pengaduan WhatsApp</h3>
        <button onclick="closeModal('modal-whatsapp')" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors">
          <iconify-icon icon="lucide:x" class="w-6 h-6"></iconify-icon>
        </button>
      </div>
      <div class="p-6">
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200 mb-6">
          <h4 class="font-bold text-green-800 mb-2">Nomor WhatsApp</h4>
          <p class="text-2xl font-bold text-green-700 mb-4">+62 813-4033-1431</p>
          <p class="text-sm text-green-600">Waktu Layanan: Senin - Jumat, 08.00 - 16.00 WIB</p>
        </div>
        <div class="text-center">
          <a href="https://wa.me/6281340331431?text=Halo%20admin%20SLB-C%20YPSLB%20Gemolong,%20saya%20ingin%20menyampaikan..." target="_blank" onclick="closeModal('modal-whatsapp')" class="inline-flex items-center gap-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-4 px-8 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300">
            <iconify-icon icon="lucide:message-circle" class="w-6 h-6"></iconify-icon> Hubungi Kami
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL FAQ -->
  <div id="modal-faq" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto">
      <div class="p-6 border-b border-brand-border/30 flex items-center justify-between">
        <h3 class="text-xl font-semibold text-brand-dark">Pertanyaan yang Sering Diajukan</h3>
        <button onclick="closeModal('modal-faq')" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors">
          <iconify-icon icon="lucide:x" class="w-6 h-6"></iconify-icon>
        </button>
      </div>
      <div class="p-6">
        <?php if (empty($faqs)): ?>
          <div class="text-center py-12">
            <iconify-icon icon="lucide:inbox" class="w-12 h-12 text-gray-400 mx-auto mb-4"></iconify-icon>
            <p class="text-gray-500">Belum ada FAQ yang tersedia.</p>
          </div>
        <?php else: ?>
          <div class="space-y-4" id="accordion-faq-modal">
            <?php foreach ($faqs as $index => $faq): ?>
              <div class="faq-item bg-white rounded-xl shadow-sm border border-brand-border/30 overflow-hidden">
                <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between hover:bg-brand-bg/30 transition-colors">
                  <span class="font-semibold text-lg text-brand-dark"><?php echo htmlspecialchars($faq['pertanyaan']); ?></span>
                  <iconify-icon icon="lucide:chevron-down" class="faq-icon w-6 h-6 text-brand-accent transition-transform"></iconify-icon>
                </button>
                <div class="faq-content px-6 pb-5 text-brand-muted hidden">
                  <p><?php echo htmlspecialchars($faq['jawaban']); ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- FAQ SECTION TETAP ADA (BISA DIHAPUS JIKA TIDAK INGIN DUA KALI) -->

  <!-- FAQ -->
  <?php if (!empty($homepageSections['faq'])): ?>
  <section id="faq" class="py-24">
    <div class="max-w-4xl mx-auto px-6">
      <div class="text-center mb-16">
        <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">FAQ</span>
          <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Pertanyaan yang Sering Diajukan</h2>
        </div>
        <div class="w-20 h-1 bg-brand-accent mx-auto"></div>
      </div>

      <div class="space-y-4" id="accordion-faq-page">
        <?php if (empty($faqs)): ?>
          <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-brand-border/30">
            <iconify-icon icon="lucide:inbox" class="w-12 h-12 text-gray-400 mx-auto mb-4"></iconify-icon>
            <p class="text-gray-500">Belum ada FAQ yang tersedia.</p>
          </div>
        <?php else: ?>
          <?php foreach ($faqs as $index => $faq): ?>
            <div class="faq-item bg-white rounded-xl shadow-sm border border-brand-border/30 overflow-hidden">
              <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between hover:bg-brand-bg/30 transition-colors">
                <span class="font-semibold text-lg text-brand-dark"><?php echo htmlspecialchars($faq['pertanyaan']); ?></span>
                <iconify-icon icon="lucide:chevron-down" class="faq-icon w-6 h-6 text-brand-accent transition-transform"></iconify-icon>
              </button>
              <div class="faq-content px-6 pb-5 text-brand-muted hidden">
                <p><?php echo htmlspecialchars($faq['jawaban']); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Form Tanya Pertanyaan Baru - BUTTON UNTUK MODAL -->
  <section class="py-0 -mt-4 bg-transparent">
    <div class="max-w-4xl mx-auto px-6 text-center py-12">
      <h2 class="font-serif text-2xl text-brand-dark mb-3">Tidak menemukan jawaban?</h2>
      <p class="text-brand-muted text-sm mb-8">Ajukan pertanyaan Anda dan tim kami akan menjawab secepatnya.</p>
      <button
        onclick="openQuestionModal()"
        class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-brand-accent to-brand-accent/80 hover:from-brand-secondary hover:to-brand-secondary/80 text-white font-bold text-sm uppercase tracking-widest rounded-lg transition-all duration-300 shadow-md hover:shadow-lg">
        <iconify-icon icon="lucide:message-square" class="text-lg"></iconify-icon>
        Ajukan Pertanyaan
      </button>
    </div>
  </section>

  <!-- MODAL FORM PERTANYAAN -->
  <div id="questionModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-brand-accent to-brand-accent/80 px-6 md:px-8 py-6 flex items-center justify-between border-b-4 border-brand-accent/20">
        <h2 class="font-serif text-2xl text-white flex items-center gap-3">
          <iconify-icon icon="lucide:message-square" class="text-2xl"></iconify-icon>
          Ajukan Pertanyaan
        </h2>
        <button
          onclick="closeQuestionModal()"
          class="text-white hover:bg-white/20 p-2 rounded-lg transition-colors">
          <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 md:p-8 space-y-6">
        <form method="POST" action="pages/faq.php" class="space-y-5">
          <input type="hidden" name="action" value="submit_question">

          <!-- Nama -->
          <div>
            <label class="block text-xs font-bold text-brand-dark mb-3 uppercase tracking-wide">Nama Lengkap</label>
            <input
              type="text"
              name="nama"
              placeholder="Masukkan nama Anda..."
              class="w-full px-5 py-3 border-2 border-brand-border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition-all text-sm"
              required>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-xs font-bold text-brand-dark mb-3 uppercase tracking-wide">Email</label>
            <input
              type="email"
              name="email"
              placeholder="Masukkan email Anda..."
              class="w-full px-5 py-3 border-2 border-brand-border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition-all text-sm"
              required>
          </div>

          <!-- Pertanyaan -->
          <div>
            <label class="block text-xs font-bold text-brand-dark mb-3 uppercase tracking-wide">Pertanyaan Anda</label>
            <textarea
              name="pertanyaan"
              placeholder="Tulis pertanyaan Anda dengan detail..."
              rows="6"
              class="w-full px-5 py-3 border-2 border-brand-border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition-all resize-none text-sm"
              required></textarea>
            <p class="text-xs text-brand-muted mt-2">Ajukan pertanyaan dengan jelas dan spesifik agar mudah dijawab.</p>
          </div>

          <!-- Buttons -->
          <div class="flex gap-3 pt-4 border-t border-brand-border">
            <button
              type="button"
              onclick="closeQuestionModal()"
              class="flex-1 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-brand-dark font-bold text-sm uppercase tracking-widest rounded-lg transition-colors">
              Batal
            </button>
            <button
              type="submit"
              class="flex-1 px-6 py-3 bg-gradient-to-r from-brand-accent to-brand-accent/80 hover:from-brand-secondary hover:to-brand-secondary/80 text-white font-bold text-sm uppercase tracking-widest rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
              <iconify-icon icon="lucide:send" class="text-base"></iconify-icon>
              Kirim Pertanyaan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- JavaScript untuk Modal, FAQ & Form -->
  <script>
    function openModal(modalId) {
      document.getElementById(modalId).classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
      document.getElementById(modalId).classList.add('hidden');
      document.body.style.overflow = 'auto';
    }

    function openQuestionModal() {
      document.getElementById('questionModal').classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }

    function closeQuestionModal() {
      document.getElementById('questionModal').classList.add('hidden');
      document.body.style.overflow = 'auto';
    }

    // Close question modal when clicking outside
    document.getElementById('questionModal')?.addEventListener('click', function(e) {
      if (e.target === this) {
        closeQuestionModal();
      }
    });

    // Close question modal on ESC key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && !document.getElementById('questionModal').classList.contains('hidden')) {
        closeQuestionModal();
      }
    });

    // Tutup modal jika klik di luar konten
    document.querySelectorAll('[id^="modal-"]').forEach(modal => {
      modal.addEventListener('click', function(e) {
        if (e.target === this) {
          closeModal(this.id);
        }
      });
    });

    // Tutup modal dengan Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        document.querySelectorAll('[id^="modal-"]').forEach(modal => {
          if (!modal.classList.contains('hidden')) {
            closeModal(modal.id);
          }
        });
      }
    });

    // FAQ Accordion (untuk page dan modal)
    document.querySelectorAll('.faq-toggle').forEach(toggle => {
      toggle.addEventListener('click', () => {
        const content = toggle.nextElementSibling;
        const icon = toggle.querySelector('.faq-icon');
        
        // Tutup semua FAQ item lainnya di dalam container yang sama
        const container = toggle.closest('#accordion-faq-modal, #accordion-faq-page');
        if (container) {
          container.querySelectorAll('.faq-item').forEach(item => {
            if (item !== toggle.parentElement) {
              item.querySelector('.faq-content').classList.add('hidden');
              item.querySelector('.faq-icon').style.transform = 'rotate(0deg)';
            }
          });
        }
        
        // Toggle FAQ item ini
        content.classList.toggle('hidden');
        if (!content.classList.contains('hidden')) {
          icon.style.transform = 'rotate(180deg)';
        } else {
          icon.style.transform = 'rotate(0deg)';
        }
      });
    });

    // Tampilkan kolom "Lainnya" jika dipilih
    document.getElementById('jenis_surat').addEventListener('change', function() {
      const lainnyaContainer = document.getElementById('lainnya_container');
      const lainnyaInput = document.getElementById('jenis_surat_lainnya');
      
      if (this.value === 'Lainnya') {
        lainnyaContainer.classList.remove('hidden');
        lainnyaInput.required = true;
      } else {
        lainnyaContainer.classList.add('hidden');
        lainnyaInput.required = false;
      }
    });

    // Form submit - Kirim via WhatsApp
    // Form submit - save to Supabase via server endpoint
    document.getElementById('form-surat').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      if (formData.get('jenis_surat') === 'Lainnya') {
        formData.set('jenis_surat', formData.get('jenis_surat_lainnya') || 'Lainnya');
      }

      fetch('<?= BASE_URL ?>/pages/submit-surat.php', {
        method: 'POST',
        body: formData
      }).then(r => r.json()).then(resp => {
        if (resp.success) {
          alert('Permohonan surat berhasil dikirim. Terima kasih.');
          this.reset();
          closeModal('modal-surat');
        } else {
          alert('Gagal mengirim: ' + (resp.message || 'Unknown'));
        }
      }).catch(err => {
        console.error(err);
        alert('Terjadi kesalahan, coba lagi.');
      });
    });

    // Pagination untuk Realisasi Tahun Ini
    (function() {
      const itemsPerPage = 6; // 1 baris di tabel
      const rows = document.querySelectorAll('.realisasi-row');
      const totalItems = rows.length;
      const totalPages = Math.ceil(totalItems / itemsPerPage);
      let currentPage = 1;

      const prevBtn = document.getElementById('prevBtn');
      const nextBtn = document.getElementById('nextBtn');
      const currentPageSpan = document.getElementById('currentPage');

      function showPage(page) {
        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;

        rows.forEach((row, index) => {
          if (index >= start && index < end) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });

        currentPageSpan.textContent = page;
        currentPage = page;

        // Update button states
        prevBtn.disabled = page === 1;
        nextBtn.disabled = page === totalPages;
      }

      if (prevBtn && nextBtn) {
        prevBtn.addEventListener('click', function() {
          if (currentPage > 1) {
            showPage(currentPage - 1);
          }
        });

        nextBtn.addEventListener('click', function() {
          if (currentPage < totalPages) {
            showPage(currentPage + 1);
          }
        });

        // Initialize
        showPage(1);
      }
    })();

    // Pagination untuk Prestasi
    (function() {
      const itemsPerPage = 3; // 1 baris (lg:grid-cols-3)
      const items = document.querySelectorAll('.prestasi-item');
      const totalItems = items.length;
      const totalPages = Math.ceil(totalItems / itemsPerPage);
      let currentPage = 1;

      const prevBtn = document.getElementById('prestasiPrevBtn');
      const nextBtn = document.getElementById('prestasiNextBtn');
      const currentPageSpan = document.getElementById('prestasiCurrentPage');

      function showPage(page) {
        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;

        items.forEach((item, index) => {
          if (index >= start && index < end) {
            item.style.display = '';
          } else {
            item.style.display = 'none';
          }
        });

        currentPageSpan.textContent = page;
        currentPage = page;

        // Update button states
        prevBtn.disabled = page === 1;
        nextBtn.disabled = page === totalPages;
      }

      if (prevBtn && nextBtn) {
        prevBtn.addEventListener('click', function() {
          if (currentPage > 1) {
            showPage(currentPage - 1);
          }
        });

        nextBtn.addEventListener('click', function() {
          if (currentPage < totalPages) {
            showPage(currentPage + 1);
          }
        });

        // Initialize
        showPage(1);
      }
    })();

    // Pagination untuk Berita Terbaru
    (function() {
      const itemsPerPage = 4; // 1 baris (lg:grid-cols-4)
      const items = document.querySelectorAll('.berita-item');
      const totalItems = items.length;
      const totalPages = Math.ceil(totalItems / itemsPerPage);
      let currentPage = 1;

      const prevBtn = document.getElementById('beritaPrevBtn');
      const nextBtn = document.getElementById('beritaNextBtn');
      const currentPageSpan = document.getElementById('beritaCurrentPage');

      function showPage(page) {
        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;

        items.forEach((item, index) => {
          if (index >= start && index < end) {
            item.style.display = '';
          } else {
            item.style.display = 'none';
          }
        });

        currentPageSpan.textContent = page;
        currentPage = page;

        // Update button states
        prevBtn.disabled = page === 1;
        nextBtn.disabled = page === totalPages;
      }

      if (prevBtn && nextBtn) {
        prevBtn.addEventListener('click', function() {
          if (currentPage > 1) {
            showPage(currentPage - 1);
          }
        });

        nextBtn.addEventListener('click', function() {
          if (currentPage < totalPages) {
            showPage(currentPage + 1);
          }
        });

        // Initialize
        showPage(1);
      }
    })();

    // Pagination untuk Galeri (Photo dan Video)
    (function() {
      // Function untuk inisialisasi pagination untuk jenis tertentu
      function initPagination(jenis) {
        const itemsPerPage = jenis === 'Photo' ? 4 : 2; // 1 baris (Photo: 4 kolom, Video: 2 kolom)
        const items = document.querySelectorAll(`.galeri-item-${jenis}`);
        const totalItems = items.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        let currentPage = 1;

        const prevBtn = document.getElementById(`galeriPrevBtn-${jenis}`);
        const nextBtn = document.getElementById(`galeriNextBtn-${jenis}`);
        const currentPageSpan = document.getElementById(`galeriCurrentPage-${jenis}`);

        function showPage(page) {
          const start = (page - 1) * itemsPerPage;
          const end = start + itemsPerPage;

          items.forEach((item, index) => {
            if (index >= start && index < end) {
              item.style.display = '';
            } else {
              item.style.display = 'none';
            }
          });

          currentPageSpan.textContent = page;
          currentPage = page;

          // Update button states
          prevBtn.disabled = page === 1;
          nextBtn.disabled = page === totalPages;
        }

        if (prevBtn && nextBtn) {
          prevBtn.addEventListener('click', function() {
            if (currentPage > 1) {
              showPage(currentPage - 1);
            }
          });

          nextBtn.addEventListener('click', function() {
            if (currentPage < totalPages) {
              showPage(currentPage + 1);
            }
          });

          // Initialize
          showPage(1);
        }
      }

      // Inisialisasi untuk Photo dan Video
      initPagination('Photo');
      initPagination('Video');
    })();
  </script>

<?php include 'components/footer.php'; ?>
