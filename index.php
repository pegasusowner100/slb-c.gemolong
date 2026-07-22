<?php
/**
 * ========================================
 * HALAMAN UTAMA (HOMEPAGE) - index.php
 * ========================================
 * 
 * File ini adalah landing page/homepage sekolah SLB BC KARYA SEJAHTERA.
 * Menampilkan:
 * - Hero section dengan carousel
 * - Statistik sekolah
 * - Profil & Visi Misi
 * - Program pendidikan
 * - Fasilitas
 * - Berita terbaru
 * - Galeri foto/video
 * - FAQ
 * - Google Analytics & Google Tag Manager scripts (JANGAN DIHAPUS - diperlukan untuk tracking)
 * 
 * CATATAN PENTING:
 * - Script Google Analytics, Google Tag Manager, dan reCAPTCHA disertakan di file ini
 * - Jangan hapus script-script tersebut karena diperlukan untuk:
 *   1. Tracking pengunjung website
 *   2. Analytics data
 *   3. Security (reCAPTCHA)
 * - Jika halaman blank, periksa console/error log untuk debug
 * 
 * ========================================
 */

// ===== DEBUG MODE: Matikan di production (tampilkan error di log saja) =====
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Include file-file yang diperlukan
require_once 'includes/db.php';
require_once 'includes/session.php';

// Track pengunjung (optional - jika file ada)
if (file_exists(__DIR__ . '/includes/track-visitor.php')) {
    require_once 'includes/track-visitor.php';
    trackVisitor('home');
}

$title = "SLB BC KARYA SEJAHTERA";
$page = 'home';

// Default hero data
$hero = [
    'tagline' => '',
    'judul' => '',
    'deskripsi' => '',
    'background_image' => '',
    'background_images' => '',
    'cta1_text' => '',
    'cta1_link' => '',
    'cta2_text' => '',
    'cta2_link' => '',
    'motto' => '',
    'tahun_berdiri' => '',
    'siswa_aktif' => '',
    'alumni' => '',
    'tenaga_pendidik' => '',
    'total_prestasi' => '',
    'jumlah_ruangan' => '',
    'buku_paket' => '',
    'latitude' => '',
    'longitude' => ''
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
    'nama_kepala_sekolah' => '',
    'foto_kepala_sekolah' => '',
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
$testimonials = [];
$anggaran_semester = [];

try {
    if ($supabaseConnected) {
        $heroResult = supabaseSelect('hero', ['id' => 'eq.1', 'limit' => 1]);
        if ($heroResult['success'] && !empty($heroResult['data'])) {
            $hero = array_merge($hero, $heroResult['data'][0]);
        }
        if (empty($hero['latitude'])) {
        $hero['latitude'] = '';
    }
    if (empty($hero['longitude'])) {
        $hero['longitude'] = '';
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

        // Get anggaran semester & aggregate
        $anggaran_semester = [];
        $anggaran = [];
        $realisasi = [];
        $semResult = supabaseSelect('anggaran_semester', ['order' => 'tahun.desc,semester.asc']);
        if ($semResult['success']) {
            $anggaran_semester = $semResult['data'];
            
            // Ambil data realisasi bulanan langsung dari tabel realisasi_bulanan
            $realResult = supabaseSelect('realisasi_bulanan', ['order' => 'tahun.desc']);
            if ($realResult['success']) {
                $realisasi = $realResult['data'];
                
                if (!function_exists('getMonthNumber')) {
                    function getMonthNumber($monthName) {
                        $months = [
                            'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4, 'Mei' => 5, 'Juni' => 6,
                            'Juli' => 7, 'Agustus' => 8, 'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12
                        ];
                        return $months[$monthName] ?? 0;
                    }
                }
                
                usort($realisasi, function($a, $b) {
                    if (($a['tahun'] ?? 0) != ($b['tahun'] ?? 0)) {
                        return ($b['tahun'] ?? 0) - ($a['tahun'] ?? 0);
                    }
                    return getMonthNumber($a['bulan'] ?? '') - getMonthNumber($b['bulan'] ?? '');
                });
            }

            // Hitung realisasi semester secara dinamis dari jumlah realisasi bulanan pada semester tersebut
            foreach ($anggaran_semester as &$as) {
                $sem_months = ($as['semester'] == 1) 
                    ? ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']
                    : ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                $sum_real = 0;
                foreach ($realisasi as $r) {
                    if ($r['tahun'] == $as['tahun'] && in_array($r['bulan'], $sem_months)) {
                        $sum_real += (float)($r['realisasi'] ?? 0);
                    }
                }
                $as['total_realisasi'] = $sum_real;
            }
            unset($as);

            // Agregasikan anggaran tahunan dari anggaran_semester (tanpa upload file)
            $yearly = [];
            foreach ($anggaran_semester as $as) {
                $tahun = $as['tahun'];
                if (!isset($yearly[$tahun])) {
                    $yearly[$tahun] = [
                        'tahun' => $tahun,
                        'total_anggaran' => 0,
                        'realisasi' => 0,
                        'file_pdf' => ''
                    ];
                }
                $yearly[$tahun]['total_anggaran'] += $as['total_anggaran'];
                $yearly[$tahun]['realisasi'] += ($as['total_realisasi'] ?? 0);
            }
            $anggaran = array_values($yearly);
            
            // Urutkan tahun desc
            usort($anggaran, function($a, $b) {
                return $b['tahun'] - $a['tahun'];
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

        // Get published testimonials
        $testimonialResult = supabaseSelect('testimoni', ['order' => 'urutan.asc, created_at.desc']);
        if ($testimonialResult['success']) {
            // Only show published testimonials
            foreach ($testimonialResult['data'] as $item) {
                if (($item['status'] ?? 'published') === 'published') {
                    $testimonials[] = $item;
                }
            }
        }
    }
} catch (Exception $e) {}

include 'components/head.php';
?>
<body class="bg-brand-bg text-brand-dark font-sans">
  <?php include 'components/navbar.php'; ?>

  <div class="relative border-l border-r border-brand-border/50">

  <!-- HERO -->
  <?php if (!empty($homepageSections['hero'])): ?>
  <section class="relative h-screen min-h-[600px] flex items-end justify-center pb-24 overflow-hidden">
    <?php
    // Parse background images
    $bg_images = [];
    if (!empty($hero['background_images'])) {
        $bg_images = array_filter(array_map('trim', explode(',', $hero['background_images'])));
    }
    if (empty($bg_images)) {
        $bg_images = [];
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
      <div class="absolute inset-0 bg-brand-dark/10"></div>
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
    
    <div class="relative z-10 max-w-4xl mx-auto px-6 text-center mb-6">
      <h1 class="font-bold text-4xl md:text-4xl lg:text-5xl tracking-tight mb-6 leading-[1.1]">
        <span class="hero-sweep-text px-4 py-2 bg-white/10 backdrop-blur-sm rounded-lg"><?php echo htmlspecialchars($hero['judul'] ?? 'SLB BC KARYA SEJAHTERA'); ?></span>
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
      <span class="text-white font-bold text-lg mx-8 flex-shrink-0">Selamat Datang di <?php echo htmlspecialchars($hero['judul'] ?? 'SLB BC KARYA SEJAHTERA'); ?></span>
      <span class="text-white font-bold text-lg mx-8 flex-shrink-0">•••</span>
      <span class="text-white font-bold text-lg mx-8 flex-shrink-0">Visi: <?php echo htmlspecialchars($profil['visi'] ?? 'Menjadikan SLB BC KARYA SEJAHTERA sebagai lembaga pendidikan luar biasa yang unggul dalam pengembangan potensi anak berkebutuhan khusus secara optimal, berkarakter, mandiri, dan berprestasi'); ?></span>
      <span class="text-white font-bold text-lg mx-8 flex-shrink-0">•••</span>
      <span class="text-white font-bold text-lg mx-8 flex-shrink-0">Misi: <?php echo htmlspecialchars($profil['misi'] ?? 'Menyelenggarakan pendidikan yang berkualitas, mengembangkan potensi akademik dan non-akademik, serta membangun karakter, serta menjalin kerjasama dengan berbagai pihak'); ?></span>
      <span class="text-white font-bold text-lg mx-8 flex-shrink-0">•••</span>
      <!-- Duplicate for seamless loop -->
      <span class="text-white font-bold text-lg mx-8 flex-shrink-0">Selamat Datang di <?php echo htmlspecialchars($hero['judul'] ?? 'SLB BC KARYA SEJAHTERA'); ?></span>
      <span class="text-white font-bold text-lg mx-8 flex-shrink-0">•••</span>
      <span class="text-white font-bold text-lg mx-8 flex-shrink-0">Visi: <?php echo htmlspecialchars($profil['visi'] ?? 'Menjadikan SLB BC KARYA SEJAHTERA sebagai lembaga pendidikan luar biasa yang unggul dalam pengembangan potensi anak berkebutuhan khusus secara optimal, berkarakter, mandiri, dan berprestasi'); ?></span>
    </div>
  </section>
  
  <?php endif; ?>

  <!-- STATS BAR -->
  <section class="bg-[#f97316] py-6 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8 relative z-10">
      <div class="text-center border-r-4 border-[#fef3c7] pr-8">
        <div class="font-serif text-4xl md:text-5xl font-bold mb-2 counter" style="color: #fef3c7 !important;" data-target="<?php echo $hero['siswa_aktif'] ?? 1250; ?>">0</div>
        <div class="text-[10px] font-bold tracking-[0.2em] uppercase text-[#fef3c7]">Siswa Aktif</div>
      </div>
      <div class="text-center border-r-4 border-[#fef3c7] pr-8">
        <div class="font-serif text-4xl md:text-5xl font-bold mb-2 counter" style="color: #fef3c7 !important;" data-target="<?php echo $hero['tenaga_pendidik'] ?? 85; ?>">0</div>
        <div class="text-[10px] font-bold tracking-[0.2em] uppercase text-[#fef3c7]">Tenaga Pendidik</div>
      </div>
      <div class="text-center border-r-4 border-[#fef3c7] pr-8">
        <div class="font-serif text-4xl md:text-5xl font-bold mb-2 counter" style="color: #fef3c7 !important;" data-target="<?php echo $hero['total_prestasi'] ?? 150; ?>">0</div>
        <div class="text-[10px] font-bold tracking-[0.2em] uppercase text-[#fef3c7]">Prestasi</div>
      </div>
      <div class="text-center border-r-4 border-[#fef3c7] pr-8">
        <div class="flex items-center justify-center gap-0 mb-2">
          <div class="font-serif text-4xl md:text-5xl font-bold counter" style="color: #fef3c7 !important;" data-target="<?php echo $hero['alumni'] ?? 5000; ?>">0</div>
          <span class="font-serif text-4xl md:text-5xl font-bold" style="color: white !important;">+</span>
        </div>
        <div class="text-[10px] font-bold tracking-[0.2em] uppercase text-[#fef3c7]">Alumni</div>
      </div>
      <div class="text-center">
        <div class="font-serif text-4xl md:text-5xl font-bold mb-2 counter" style="color: #fef3c7 !important;" data-target="<?php echo $hero['tahun_berdiri'] ?? 1990; ?>">0</div>
        <div class="text-[10px] font-bold tracking-[0.2em] uppercase text-[#fef3c7]">Tahun Berdiri</div>
      </div>
    </div>
  </section>

  <!-- PROFIL SEKOLAH -->
  <?php if (!empty($homepageSections['profil'])): ?>
  <section id="profil" class="py-12">
    <style>
      .shadow-brand-glow {
        box-shadow: 0 25px 50px -12px rgba(62, 107, 78, 0.45); /* 3E6B4E in RGBA */
      }
      @keyframes wobble-box-left {
        0%, 100% { transform: rotate(-6deg) translate(0, 0); }
        50% { transform: rotate(-10deg) translate(-6px, -6px); }
      }
      @keyframes wobble-box-right {
        0%, 100% { transform: rotate(6deg) translate(0, 0); }
        50% { transform: rotate(10deg) translate(6px, 6px); }
      }
      .animate-wobble-left {
        animation: wobble-box-left 6s ease-in-out infinite;
      }
      .animate-wobble-right {
        animation: wobble-box-right 6s ease-in-out infinite;
      }
      .film-frame {
        position: relative;
        background: #1a1a1a;
        padding: 36px 12px;
        border-radius: 8px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
      }
      .film-frame::before, .film-frame::after {
        content: '';
        position: absolute;
        left: 12px;
        right: 12px;
        height: 10px;
        background-image: repeating-linear-gradient(to right, #ffffff 0px, #ffffff 10px, transparent 10px, transparent 20px);
      }
      .film-frame::before {
        top: 12px;
      }
      .film-frame::after {
        bottom: 12px;
      }
    </style>
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
            <div class="film-frame relative z-10 shadow-brand-glow">
              <img src="<?php echo htmlspecialchars($profil['gambar_gedung'] ?? 'https://picsum.photos/seed/school-building/480/600.jpg'); ?>" alt="Gedung Sekolah" class="w-full h-[378px] object-cover rounded-md">
            </div>
          <?php endif; ?>
            <div class="absolute -bottom-4 -right-4 w-24 h-24 border-4 border-brand-accent rounded-lg rotate-6 animate-wobble-right z-20 pointer-events-none bg-white/30 backdrop-blur-[3px]"></div>
            <div class="absolute -top-4 -left-4 w-24 h-24 border-4 border-brand-accent rounded-lg -rotate-6 animate-wobble-left z-20 pointer-events-none bg-white/30 backdrop-blur-[3px]"></div>
          </div>
        </div>
        <div class="lg:col-span-3">
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white bg-brand-accent px-4 py-2 rounded">Sambutan Kepala Sekolah</span>
          <h2 class="font-serif text-4xl md:text-5xl font-normal tracking-tight mt-4 mb-6 leading-[1.1]">Selamat Datang di <em><?php echo htmlspecialchars($profil['nama_sekolah'] ?? 'SLB BC KARYA SEJAHTERA'); ?></em></h2>
          <div class="relative pl-6 border-l-2 border-brand-accent mb-6">
            <iconify-icon icon="lucide:quote" class="text-brand-accent/30 text-4xl absolute -left-5 -top-2 bg-brand-bg px-1"></iconify-icon>
            <p class="text-slate-900 text-sm font-normal leading-relaxed text-justify">
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
  </section>

  <?php endif; ?>

  <!-- Tentang Sekolah -->
  <?php if (!empty($homepageSections['tentang'])): ?>
  <section class="py-24 bg-brand-bg/50">
    <div class="max-w-7xl mx-auto px-6">
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        
        <!-- Kolom 1: Sejarah -->
        <div class="bg-white p-8 rounded-2xl shadow-lg border border-brand-border/30 overflow-hidden min-h-[500px]">
          <div class="bg-brand-accent -mx-8 -mt-8 px-8 py-4 mb-6">
            <h3 class="font-serif text-2xl font-semibold text-center text-white">Latar Belakang</h3>
          </div>
          <div class="mb-6">
            <p class="text-brand-muted text-sm leading-relaxed text-justify">
              <?php 
              $sejarahText = $profil['sejarah'] ?? 'SLB BC KARYA SEJAHTERA didirikan dengan tujuan memberikan pendidikan terbaik untuk anak berkebutuhan khusus.';
              // Cari posisi kalimat "SD Negeri Karanganyar Plupuh."
              $potongDi = strpos($sejarahText, 'SD Negeri Karanganyar Plupuh.');
              if ($potongDi !== false) {
                  // Ambil teks sampai kalimat tersebut selesai (ditambah panjang kalimat + 1 untuk spasi/akhir kalimat)
                  $teksSingkat = substr($sejarahText, 0, $potongDi + strlen('SD Negeri Karanganyar Plupuh.'));
                  echo nl2br(htmlspecialchars($teksSingkat));
              } else {
                  echo nl2br(htmlspecialchars($sejarahText));
              }
              ?>
            </p>
          </div>
          <div class="flex justify-center">
            <a href="<?= BASE_URL ?>/pages/profil.php#sejarah" class="px-8 py-3 border-2 border-brand-accent text-brand-accent font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-brand-accent hover:text-white transition-colors">
              Lihat Selengkapnya
            </a>
          </div>
        </div>

        <!-- Kolom 2: Dasar Hukum -->
        <div class="bg-white p-8 rounded-2xl shadow-lg border border-brand-border/30 overflow-hidden min-h-[500px]">
          <div class="bg-brand-accent -mx-8 -mt-8 px-8 py-4 mb-6">
            <h3 class="font-serif text-2xl font-semibold text-center text-white">Dasar Hukum</h3>
          </div>
          <div class="text-brand-muted text-sm leading-relaxed mb-6">
            <?php 
            $dasarHukum = $profil['dasar_hukum'] ?? '';
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
            <a href="<?= BASE_URL ?>/pages/profil.php#dasar-hukum" class="px-8 py-3 border-2 border-brand-accent text-brand-accent font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-brand-accent hover:text-white transition-colors">
              Lihat Selengkapnya
            </a>
          </div>
        </div>

        <!-- Kolom 3: Visi & Misi -->
        <div class="bg-white p-8 rounded-2xl shadow-lg border border-brand-border/30 overflow-hidden min-h-[500px]">
          <div class="bg-brand-accent -mx-8 -mt-8 px-8 py-4 mb-6">
            <h3 class="font-serif text-2xl font-semibold text-center text-white">Visi Misi</h3>
          </div>
          
          <div>
            <h4 class="font-semibold text-lg text-center mb-2 text-brand-dark">Visi</h4>
            <p class="text-brand-muted text-sm mb-6 text-justify">
              <?php echo nl2br(htmlspecialchars($profil['visi'] ?? 'Menjadikan SLB BC KARYA SEJAHTERA sebagai lembaga pendidikan luar biasa yang unggul.')); ?>
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
          </div>
          
          <div class="flex justify-center">
            <a href="<?= BASE_URL ?>/pages/profil.php#visimisi" class="px-8 py-3 border-2 border-brand-accent text-brand-accent font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-brand-accent hover:text-white transition-colors">
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
         
          <a href="<?= BASE_URL ?>/pages/profil.php#sumberdaya" class="font-semibold text-brand-accent hover:text-brand-dark"></a>.
        </p>
      </div>

      <div class="overflow-hidden">
        <div class="marquee-track flex flex-nowrap gap-6 min-w-max">
          <?php
          // Duplicate the list for seamless loop
          $sdm_list = !empty($guru_list) ? $guru_list : [
              ['nama' => 'Guru', 'jabatan' => 'Tenaga Pendidik', 'foto' => 'https://picsum.photos/seed/sdm1/520/380'],
              ['nama' => 'Tendik', 'jabatan' => 'Tenaga Kependidikan', 'foto' => 'https://picsum.photos/seed/sdm2/520/380'],
              ['nama' => 'Administrasi', 'jabatan' => 'Staf Sekolah', 'foto' => 'https://picsum.photos/seed/sdm3/520/380'],
              ['nama' => 'Kepala Sekolah', 'jabatan' => 'Manajemen', 'foto' => 'https://picsum.photos/seed/sdm4/520/380'],
          ];
          // Loop twice
          for ($loop = 0; $loop < 2; $loop++) {
              foreach ($sdm_list as $index => $guru) {
          ?>
                <a href="<?= BASE_URL ?>/pages/profil.php#sumberdaya" class="flex-shrink-0 w-64 group overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">
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
          }
          ?>
        </div>
      </div>

      <style>
        .marquee-track {
          display: flex;
          flex-wrap: nowrap;
          gap: 24px;
          padding: 24px;
          align-items: stretch;
          animation: marquee 30s linear infinite;
          will-change: transform;
        }
        .marquee-track:hover {
          animation-play-state: paused;
        }
        @keyframes marquee {
          from { transform: translateX(0); }
          to { transform: translateX(-50%); }
        }
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
          [
            'nama' => 'Pendidikan Akademik',
            'gambar' => 'https://picsum.photos/seed/akademik/600/400',
            'deskripsi' => 'Pendidikan akademik yang disesuaikan dengan kebutuhan dan kemampuan masing-masing siswa berkebutuhan khusus untuk mengoptimalkan potensi intelektual mereka.'
          ],
          [
            'nama' => 'Keterampilan Vokasi',
            'gambar' => 'https://picsum.photos/seed/vokasi/600/400',
            'deskripsi' => 'Pelatihan keterampilan vokasi untuk membekali siswa dengan keahlian praktis seperti tata boga, kerajinan tangan, dan kemandirian kerja agar mandiri di masa depan.'
          ],
          [
            'nama' => 'Olahraga & Seni',
            'gambar' => 'https://picsum.photos/seed/olahraga/600/400',
            'deskripsi' => 'Pengembangan bakat di bidang olahraga dan seni untuk menyalurkan kreativitas, menjaga kebugaran fisik, serta melatih kepercayaan diri siswa.'
          ],
          [
            'nama' => 'Bimbingan Konseling',
            'gambar' => 'https://picsum.photos/seed/konseling/600/400',
            'deskripsi' => 'Layanan bimbingan dan konseling khusus untuk mendukung perkembangan psikologis, sosial emosional, serta penyesuaian diri siswa.'
          ],
          [
            'nama' => 'Ekstrakurikuler',
            'gambar' => 'https://picsum.photos/seed/ekstrakurikuler/600/400',
            'deskripsi' => 'Berbagai kegiatan ekstrakurikuler menarik untuk memperluas wawasan, minat bakat, dan interaksi sosial positif antar siswa.'
          ],
          [
            'nama' => 'Pengembangan Karakter',
            'gambar' => 'https://picsum.photos/seed/karakter/600/400',
            'deskripsi' => 'Pembentukan karakter mandiri yang berakhlak mulia, disiplin, toleran, dan memiliki rasa percaya diri yang tinggi dalam kehidupan sehari-hari.'
          ]
        ];
        $total_programs = empty($programs) ? count($default_programs) : count($programs);
        
        if (empty($programs)):
          foreach ($default_programs as $index => $prog):
            $delay = $index * 0.16; ?>
            <div class="reveal-card flex flex-col items-center text-center group p-3 rounded-lg border bg-white border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-sm cursor-pointer" 
                 onclick="openDetailCardModal(this)"
                 data-category="Program Unggulan"
                 data-nama="<?php echo htmlspecialchars($prog['nama']); ?>"
                 data-gambar="<?php echo htmlspecialchars($prog['gambar']); ?>"
                 data-deskripsi="<?php echo htmlspecialchars($prog['deskripsi']); ?>"
                 style="animation-delay: <?= $delay ?>s;">
              <iconify-icon icon="mdi:book-open-page-variant" class="w-6 h-6 mx-auto mb-3 text-amber-500" style="font-size: 40px;"></iconify-icon>
              <h3 class="font-bold text-brand-dark text-xs md:text-sm"><?php echo htmlspecialchars($prog['nama']); ?></h3>
            </div>
          <?php endforeach;
        else:
          foreach ($programs as $index => $prog):
            $delay = $index * 0.16; ?>
            <div class="reveal-card flex flex-col items-center text-center group p-3 rounded-lg border bg-white border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-sm cursor-pointer" 
                 onclick="openDetailCardModal(this)"
                 data-category="Program Unggulan"
                 data-nama="<?php echo htmlspecialchars($prog['nama'] ?? ''); ?>"
                 data-gambar="<?php echo htmlspecialchars($prog['gambar'] ?? 'https://picsum.photos/seed/program-default/600/400'); ?>"
                 data-deskripsi="<?php echo htmlspecialchars($prog['deskripsi'] ?? 'Program unggulan di SLB BC Karya Sejahtera Gemolong.'); ?>"
                 style="animation-delay: <?= $delay ?>s;">
              <iconify-icon icon="mdi:book-open-page-variant" class="w-6 h-6 mx-auto mb-3 text-amber-500" style="font-size: 40px;"></iconify-icon>
              <h3 class="font-bold text-brand-dark text-xs md:text-sm"><?php echo htmlspecialchars($prog['nama'] ?? ''); ?></h3>
            </div>
          <?php endforeach;
        endif; ?>
      </div>

      <div class="text-center">
        <a href="<?= BASE_URL ?>/pages/program.php" class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold text-sm uppercase tracking-widest rounded-lg hover:from-amber-600 hover:to-orange-700 transition-all shadow-lg hover:shadow-xl">
          Lihat Selengkapnya
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
          [
            'nama' => 'Ruang Kelas',
            'icon' => 'lucide:home',
            'gambar' => 'https://picsum.photos/seed/kelas/600/400',
            'deskripsi' => 'Ruang kelas yang nyaman, bersih, terang, dan dilengkapi dengan alat peraga khusus serta media pembelajaran adaptif untuk mendukung proses belajar mengajar anak berkebutuhan khusus.'
          ],
          [
            'nama' => 'Ruang Kepala Sekolah',
            'icon' => 'lucide:graduation-cap',
            'gambar' => 'https://picsum.photos/seed/kepsek/600/400',
            'deskripsi' => 'Ruang kerja pimpinan sekolah yang berfungsi sebagai pusat administrasi, koordinasi program sekolah, dan ruang pelayanan bagi wali murid serta tamu dinas.'
          ],
          [
            'nama' => 'Ruang Guru',
            'icon' => 'lucide:users',
            'gambar' => 'https://picsum.photos/seed/guru/600/400',
            'deskripsi' => 'Ruangan khusus guru untuk berdiskusi, merancang rencana pembelajaran individual (RPI), serta beristirahat di sela-sela waktu membimbing siswa.'
          ],
          [
            'nama' => 'Perpustakaan',
            'icon' => 'lucide:book-open',
            'gambar' => 'https://picsum.photos/seed/perpustakaan/600/400',
            'deskripsi' => 'Koleksi buku-buku bacaan ramah anak, buku pelajaran adaptif, serta berbagai media pembelajaran audio-visual untuk menumbuhkan minat literasi siswa.'
          ],
          [
            'nama' => 'Laboratorium',
            'icon' => 'lucide:cpu',
            'gambar' => 'https://picsum.photos/seed/laboratorium/600/400',
            'deskripsi' => 'Fasilitas komputer dan teknologi informasi untuk mengenalkan literasi digital kepada siswa sejak dini guna membekali mereka keterampilan praktis modern.'
          ],
          [
            'nama' => 'Fasilitas Lainnya',
            'icon' => 'lucide:star',
            'gambar' => 'https://picsum.photos/seed/lainnya/600/400',
            'deskripsi' => 'Sarana pendukung lainnya seperti area bermain luar ruangan yang aman, lapangan olahraga serbaguna, dan ruang UKS untuk menjaga kesehatan siswa.'
          ]
        ];
        $total_fasilitas = empty($fasilitas) ? count($default_fasilitas) : count($fasilitas);
        
        if (empty($fasilitas)):
          foreach ($default_fasilitas as $index => $f):
            $delay = $index * 0.16; ?>
            <div class="reveal-card flex flex-col items-center text-center group p-3 rounded-lg border bg-white border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-sm cursor-pointer"
                 onclick="openDetailCardModal(this)"
                 data-category="Fasilitas"
                 data-nama="<?php echo htmlspecialchars($f['nama']); ?>"
                 data-gambar="<?php echo htmlspecialchars($f['gambar']); ?>"
                 data-deskripsi="<?php echo htmlspecialchars($f['deskripsi']); ?>"
                 style="animation-delay: <?= $delay ?>s;">
              <iconify-icon icon="<?php echo htmlspecialchars($f['icon']); ?>" class="w-6 h-6 mx-auto mb-3 text-teal-700" style="font-size: 40px;"></iconify-icon>
              <h3 class="font-bold text-brand-dark text-xs md:text-sm"><?php echo htmlspecialchars($f['nama']); ?></h3>
            </div>
          <?php endforeach;
        else:
          foreach ($fasilitas as $index => $f):
            $delay = $index * 0.16; ?>
            <div class="reveal-card flex flex-col items-center text-center group p-3 rounded-lg border bg-white border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-sm cursor-pointer"
                 onclick="openDetailCardModal(this)"
                 data-category="Fasilitas"
                 data-nama="<?php echo htmlspecialchars($f['nama'] ?? ''); ?>"
                 data-gambar="<?php echo htmlspecialchars($f['gambar'] ?? 'https://picsum.photos/seed/' . urlencode($f['nama'] ?? 'default-fasilitas') . '/500/400.jpg'); ?>"
                 data-deskripsi="<?php echo htmlspecialchars($f['deskripsi'] ?? 'Fasilitas penunjang pembelajaran di sekolah.'); ?>"
                 style="animation-delay: <?= $delay ?>s;">
              <iconify-icon icon="<?php echo htmlspecialchars($f['icon'] ?? 'mdi:office-building'); ?>" class="w-6 h-6 mx-auto mb-3 text-teal-700" style="font-size: 40px;"></iconify-icon>
              <h3 class="font-bold text-brand-dark text-xs md:text-sm"><?php echo htmlspecialchars($f['nama'] ?? ''); ?></h3>
            </div>
          <?php endforeach;
        endif; ?>
      </div>
      
      <div class="text-center">
        <a href="<?= BASE_URL ?>/pages/fasilitas.php" class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-teal-600 to-teal-800 text-white font-bold text-sm uppercase tracking-widest rounded-lg hover:from-teal-700 hover:to-teal-900 transition-all shadow-lg hover:shadow-xl">
          Lihat Selengkapnya
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
  <section class="py-3 relative overflow-visible" style="background-image: url('<?php echo ASSETS_URL; ?>/images/background3.png'); background-size: cover; background-position: center;">

    <div class="absolute inset-0 bg-[url('<?php echo ASSETS_URL; ?>/images/texture-pattern-light.png')] opacity-10"></div>
    <div class="max-w-6xl mx-auto px-6 relative z-10">
      <div class="flex flex-col items-center justify-center gap-8 md:flex-row md:items-center md:justify-center text-center">
        <div class="flex-shrink-0 cta-slide-in-left">
          <img src="assets/images/Selamat_datang.png" alt="Selamat Datang" class="h-[520px] md:h-[560px] w-auto rounded-none shadow-none object-contain" />
        </div>
        <div class="max-w-xl">
          <h2 class="font-serif text-2xl md:text-4xl text-blue-900 font-bold mb-4 cta-typing"><span class="whitespace-nowrap">Bergabung Bersama Kami</span></h2>
          <p class="text-blue-800 font-bold text-lg md:text-xl mb-6 cta-fade-in cta-delay-200">Daftarkan putra-putri Anda di SLB BC KARYA SEJAHTERA</p>
          <a href="<?= BASE_URL ?>/pages/ppdb.php" class="inline-block px-10 py-4 bg-white text-orange-600 font-bold text-xs uppercase tracking-widest rounded hover:bg-orange-100 hover:text-orange-700 transition-colors cta-fade-in cta-delay-300 cta-button-blink">Daftar Sekarang</a>
        </div>
      </div>
    </div>
  </section>

  <?php endif; ?>

  <!-- BERITA TERBARU -->
  <?php if (!empty($homepageSections['berita'])): ?>
  <section id="berita" class="py-24 bg-slate-50">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center mb-16">
        <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center rounded-3xl shadow-xl" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Informasi Terbaru</span>
          <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Berita Terbaru</h2>
        </div>
        <div class="w-20 h-1 bg-slate-900 mx-auto"></div>
      </div>

      <?php if (!empty($berita)): ?>
        <?php
          $selectedBerita = reset($berita);
          $sidebarBerita = array_slice($berita, 1);
        ?>
        <div class="rounded-[32px] border border-brand-border/40 bg-white overflow-visible shadow-xl">
          <div class="border-b border-brand-border/40 bg-slate-500 px-6 py-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div>
                <p class="text-[10px] uppercase tracking-[0.4em] text-slate-300 font-semibold">Papan Berita</p>
                <h3 class="text-lg font-semibold text-white">Berita Terbaru</h3>
              </div>
              <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-[10px] uppercase tracking-[0.28em] font-semibold text-slate-900">
                <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                Update
              </div>
            </div>
          </div>

          <div class="lg:grid lg:grid-cols-[2fr_1fr] gap-6 p-4 lg:p-6 bg-slate-50">
            <main class="space-y-6">
              <?php if ($selectedBerita): ?>
                <article class="bg-white overflow-hidden rounded-[28px] shadow-sm">
                  <?php 
                  $videoUrl = $selectedBerita['video_url'] ?? '';
                  $isYoutube = false;
                  $youtubeEmbedUrl = '';
                  if (!empty($videoUrl)) {
                      if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $videoUrl, $match)) {
                          $isYoutube = true;
                          $youtubeEmbedUrl = "https://www.youtube.com/embed/" . $match[1];
                      }
                  }
                  ?>
                  <?php if (!empty($videoUrl)): ?>
                    <div class="relative overflow-hidden w-full aspect-video bg-black rounded-t-[28px]">
                      <?php if ($isYoutube): ?>
                        <iframe class="w-full h-full border-0" 
                                src="<?php echo $youtubeEmbedUrl; ?>" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen></iframe>
                      <?php else: ?>
                        <video src="<?php echo htmlspecialchars($videoUrl); ?>" controls class="w-full h-full object-contain"></video>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <div class="relative overflow-hidden h-[340px] md:h-[420px] rounded-t-[28px]">
                      <img src="<?php echo htmlspecialchars($selectedBerita['gambar'] ?? 'https://picsum.photos/seed/' . urlencode($selectedBerita['judul'] ?? 'default') . '/1200/700'); ?>"
                           alt="<?php echo htmlspecialchars($selectedBerita['judul'] ?? 'Berita'); ?>"
                           class="w-full h-full object-cover">
                    </div>
                  <?php endif; ?>
                  <div class="p-6 lg:p-8">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4 text-xs uppercase tracking-[0.24em] font-semibold text-slate-500">
                      <span><?php echo htmlspecialchars((new DateTime($selectedBerita['tanggal_upload'] ?? $selectedBerita['tanggal'] ?? 'now'))->format('d F Y')); ?></span>
                      <span class="bg-slate-200 text-slate-900 px-3 py-1.5 rounded-full shadow-sm"><?php echo htmlspecialchars($selectedBerita['kategori'] ?? 'Umum'); ?></span>
                    </div>
                    <div class="border-t border-slate-200 pt-4">
                      <div class="flex items-center gap-3 mb-3">
                        <span class="block h-1.5 w-14 bg-slate-900 rounded-full"></span>
                        <span class="text-[11px] uppercase tracking-[0.4em] text-slate-900 font-semibold">Berita</span>
                        <span class="block h-1.5 flex-1 bg-slate-900 rounded-full"></span>
                      </div>
                      <h2 class="font-serif text-3xl md:text-4xl font-bold text-slate-900 leading-tight mb-4"><?php echo htmlspecialchars($selectedBerita['judul'] ?? 'Berita Terbaru'); ?></h2>
                      <?php
                        $share_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . rtrim(BASE_URL, '/') . '/pages/berita.php?id=' . urlencode($selectedBerita['id'] ?? '');
                        $share_title = $selectedBerita['judul'] ?? SITE_NAME;
                        include __DIR__ . '/components/share.php';
                      ?>
                      <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed text-justify">
                        <?php echo nl2br(htmlspecialchars(strip_tags($selectedBerita['konten'] ?? 'Belum ada konten untuk berita ini.'))); ?>
                      </div>
                    </div>
                  </div>
                </article>
              <?php endif; ?>
            </main>

            <aside class="space-y-4 bg-slate-200 text-slate-900 rounded-[28px] border border-slate-300 p-4 shadow-xl">
              <div class="flex items-center justify-between mb-4">
                <div>
            
                  <h3 class="font-serif text-xl font-bold text-slate-900">Lihat Berita Lainnya</h3>
                </div>
              </div>
              <div id="sidebarBeritaList" class="divide-y divide-slate-300">
                <?php foreach ($sidebarBerita as $sidebarIndex => $item): ?>
                  <a href="pages/berita.php?id=<?php echo urlencode($item['id'] ?? ''); ?>" class="sidebar-berita-item group block py-4 transition-all hover:bg-slate-100 px-4 rounded-xl" data-index="<?php echo $sidebarIndex; ?>">
                    <div class="flex items-start gap-3">
                      <div class="flex-shrink-0 w-14 h-14 overflow-hidden rounded-xl bg-slate-100 border border-slate-300">
                        <img src="<?php echo htmlspecialchars($item['gambar'] ?? 'https://picsum.photos/seed/' . urlencode($item['judul'] ?? 'list') . '/240/240'); ?>" alt="<?php echo htmlspecialchars($item['judul'] ?? ''); ?>" class="w-full h-full object-cover">
                      </div>
                      <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-2 text-[10px] uppercase tracking-[0.24em] text-slate-500">
                          <span><?php echo htmlspecialchars((new DateTime($item['tanggal_upload'] ?? $item['tanggal'] ?? 'now'))->format('d F Y')); ?></span>
                          <span class="inline-flex items-center rounded-full bg-slate-300 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-700"><?php echo htmlspecialchars($item['kategori'] ?? 'Umum'); ?></span>
                        </div>
                        <h4 class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($item['judul'] ?? ''); ?></h4>
                      </div>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
              <div class="mt-4 space-y-3 text-sm text-slate-600">
                <div class="flex items-center justify-between gap-3">
                  <button type="button" id="sidebarPrevBtn" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-2 transition hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed">
                    <iconify-icon icon="lucide:chevrons-left" class="w-4 h-4"></iconify-icon> Sebelumnya
                  </button>
                  <button type="button" id="sidebarNextBtn" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-2 transition hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed">
                    Selanjutnya <iconify-icon icon="lucide:chevrons-right" class="w-4 h-4"></iconify-icon>
                  </button>
                </div>
                <a href="pages/berita.php" class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-2 font-semibold text-slate-900 transition hover:bg-slate-100">
                  Lihat Semua Berita
                </a>
              </div>
            </aside>
          </div>
        </div>
      <?php else: ?>
        <div class="text-center py-16 bg-slate-100 rounded-3xl border border-slate-200">
          <iconify-icon icon="lucide:newspaper" class="text-6xl text-slate-400 mb-4"></iconify-icon>
          <p class="text-slate-600">Belum ada berita terbaru.</p>
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
            $galeri_items_per_page = $jenis === 'Photo' ? 4 : 6; // Photo: 4 kolom (1 baris), Video: 3 kolom (2 baris = 6 items)
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
            <div class="grid <?php echo $jenis === 'Photo' ? 'md:grid-cols-4' : 'md:grid-cols-3'; ?> gap-8" id="galeriContainer-<?php echo $jenis; ?>">
              <?php 
                $galeri_index = 0;
                foreach ($items as $item): 
              ?>
                <div class="bg-white rounded-lg overflow-hidden shadow-sm <?php echo $jenis === 'Photo' ? 'hover:shadow-lg transition-shadow group' : ''; ?> border border-brand-border/30 galeri-item-<?php echo $jenis; ?>" data-index="<?php echo $galeri_index; ?>">
                  <div class="relative overflow-hidden h-56">
                    <?php 
                    $fileUrl = $item['file_url'] ?? 'https://picsum.photos/seed/' . urlencode($item['judul']) . '/600/400';
                    $isVideo = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION)) === 'mp4' || strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION)) === 'webm' || strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION)) === 'ogg' || strpos(strtolower($fileUrl), 'video') !== false;
                    if ($isVideo): 
                    ?>
                      <video controls class="w-full h-full object-cover">
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
                    <div class="text-xs text-blue-300 mb-2">
                      <iconify-icon icon="lucide:calendar" class="inline-block mr-1"></iconify-icon>
                      <?php 
                      // Format to long date (e.g., 10 Juni 2024)
                      $tanggal = isset($item['tanggal_upload']) ? new DateTime($item['tanggal_upload']) : new DateTime();
                      setlocale(LC_TIME, 'id_ID.UTF-8');
                      $tanggalFormatted = strftime('%d %B %Y', $tanggal->getTimestamp());
                      echo htmlspecialchars($tanggalFormatted);
                      ?>
                    </div>
                    <!-- Share buttons dedicated row -->
                    <div class="flex items-center gap-2 mb-3 flex-nowrap overflow-x-auto py-1">
                      <?php
                        $galeri_share_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . rtrim(BASE_URL, '/') . '/pages/galeri.php';
                        $galeri_share_title = $item['judul'] ?? SITE_NAME;
                        $encUrl = rawurlencode($galeri_share_url);
                        $encTitle = rawurlencode($galeri_share_title);
                      ?>
                      <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $encUrl; ?>" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 transition-colors flex items-center shrink-0" title="Bagikan ke Facebook">
                        <iconify-icon icon="mdi:facebook" class="w-8 h-8"></iconify-icon>
                      </a>
                      <a href="https://twitter.com/intent/tweet?text=<?php echo $encTitle; ?>&url=<?php echo $encUrl; ?>" target="_blank" rel="noopener noreferrer" class="text-sky-400 hover:text-sky-600 transition-colors flex items-center shrink-0" title="Bagikan ke X/Twitter">
                        <iconify-icon icon="mdi:twitter" class="w-8 h-8"></iconify-icon>
                      </a>
                      <a href="https://api.whatsapp.com/send?text=<?php echo $encTitle; ?>%20<?php echo $encUrl; ?>" target="_blank" rel="noopener noreferrer" class="text-green-500 hover:text-green-700 transition-colors flex items-center shrink-0" title="Bagikan ke WhatsApp">
                        <iconify-icon icon="mdi:whatsapp" class="w-8 h-8"></iconify-icon>
                      </a>
                      <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $encUrl; ?>" target="_blank" rel="noopener noreferrer" class="text-blue-700 hover:text-blue-900 transition-colors flex items-center shrink-0" title="Bagikan ke LinkedIn">
                        <iconify-icon icon="mdi:linkedin" class="w-8 h-8"></iconify-icon>
                      </a>
                      <a href="https://t.me/share/url?url=<?php echo $encUrl; ?>&text=<?php echo $encTitle; ?>" target="_blank" rel="noopener noreferrer" class="text-blue-500 hover:text-blue-700 transition-colors flex items-center shrink-0" title="Bagikan ke Telegram">
                        <iconify-icon icon="mdi:telegram" class="w-8 h-8"></iconify-icon>
                      </a>
                      <a href="mailto:?subject=<?php echo $encTitle; ?>&body=<?php echo $encUrl; ?>" class="text-red-500 hover:text-red-700 transition-colors flex items-center shrink-0" title="Bagikan lewat Email">
                        <iconify-icon icon="mdi:email" class="w-8 h-8"></iconify-icon>
                      </a>
                      <button type="button" onclick="copyShareLink('<?php echo htmlspecialchars($galeri_share_url, ENT_QUOTES, 'UTF-8'); ?>', this)" class="text-slate-500 hover:text-slate-700 transition-colors flex items-center shrink-0" title="Salin tautan">
                        <iconify-icon icon="mdi:link-variant" class="w-8 h-8"></iconify-icon>
                      </button>
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
          <p class="text-purple-100 text-xs">Pertanyaan Sudah Diajukan</p>
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
        <h3 class="text-xl font-semibold text-brand-dark">Pertanyaan Sudah Diajukan</h3>
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
          <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Pertanyaan Sudah Diajukan</h2>
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

  <!-- TESTIMONIALS -->
  <?php if (!empty($homepageSections['testimoni'])): ?>
  <section id="testimoni" class="py-24 bg-[#4A3728] relative overflow-hidden">
    <!-- Background decoration -->
    <div class="absolute inset-0 opacity-10" style="background-image:url('<?php echo ASSETS_URL; ?>/images/background.png'); background-size: cover; background-position: center;"></div>
    
    <div class="max-w-7xl mx-auto px-6 relative z-10">
      <div class="text-center mb-16">
        <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Testimoni</span>
          <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Apa Kata Mereka</h2>
        </div>
        <div class="w-20 h-1 bg-gradient-to-r from-[#B8860B] to-[#DAA520] mx-auto"></div>
      </div>

      <?php if (empty($testimonials)): ?>
        <div class="text-center py-16 bg-white/90 rounded-xl border border-[#B8860B]/30 shadow-sm">
          <iconify-icon icon="lucide:message-square-heart" class="text-6xl text-[#4A3728]/30 mx-auto mb-4"></iconify-icon>
          <p class="text-[#4A3728]/70">Belum ada testimoni.</p>
        </div>
      <?php else: ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
          <?php foreach ($testimonials as $testimonial): ?>
            <div class="relative group">
              <!-- Quote decorations -->
              <div class="absolute -top-4 -left-2 text-[#B8860B]/20 text-8xl font-serif">"</div>
              <div class="absolute -bottom-4 -right-2 text-[#B8860B]/20 text-8xl font-serif rotate-180">"</div>
              
              <div class="bg-[#F2EBDF] rounded-3xl border-4 border-[#B8860B]/50 overflow-visible shadow-2xl transition-all duration-500 hover:shadow-[0_0_30px_rgba(184,134,11,0.4)] hover:-translate-y-3 pt-10">
                <!-- Photo -->
                <div class="relative -mt-8 mx-auto w-32 h-32 mb-6">
                  <img src="<?php echo htmlspecialchars($testimonial['foto'] ?? 'https://picsum.photos/seed/testimoni-' . $testimonial['id'] . '/200/200.jpg'); ?>" alt="<?php echo htmlspecialchars($testimonial['nama']); ?>" class="w-full h-full rounded-full object-cover" onerror="this.src='https://picsum.photos/seed/testimoni-<?php echo $testimonial['id']; ?>/200/200.jpg'">
                </div>
                
                <div class="p-8 pt-0 text-center">
                  <!-- Testimonial text -->
                  <p class="text-[#4A3728] text-sm md:text-base italic leading-relaxed mb-6 font-medium">
                    "<?php echo htmlspecialchars($testimonial['pesan']); ?>"
                  </p>
                  
                  <!-- Divider -->
                  <div class="w-16 h-1 bg-gradient-to-r from-transparent via-[#B8860B] to-transparent mx-auto mb-4"></div>
                  
                  <!-- Name and role -->
                  <h3 class="text-xl font-bold text-[#4A3728] uppercase tracking-widest mb-2">
                    <?php echo htmlspecialchars($testimonial['nama']); ?>
                  </h3>
                  <p class="text-sm text-[#B8860B] uppercase tracking-wider font-semibold mb-4">
                    <?php echo htmlspecialchars($testimonial['jabatan'] ?? '-'); ?>
                  </p>
                  
                  <!-- Stars and thumbs up -->
                  <div class="flex items-center justify-center gap-2">
                    <?php $rating = (int)($testimonial['bintang'] ?? 5); ?>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                      <iconify-icon icon="<?= $i <= $rating ? 'mdi:star' : 'mdi:star-outline' ?>" class="text-xl <?= $i <= $rating ? 'text-[#B8860B]' : 'text-gray-300' ?>"></iconify-icon>
                    <?php endfor; ?>
                    <iconify-icon icon="lucide:thumbs-up" class="text-[#4A3728] text-2xl ml-2"></iconify-icon>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

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
        <!-- Alert Box -->
        <div id="modalAlert" class="hidden rounded-lg p-4 flex items-start gap-3 border-2">
          <iconify-icon id="modalAlertIcon" icon="lucide:alert-circle" class="w-6 h-6 flex-shrink-0 mt-0.5"></iconify-icon>
          <div>
            <p id="modalAlertText" class="font-semibold text-sm"></p>
          </div>
        </div>

        <!-- Step 1: Input Form -->
        <form id="formRequestOtp" class="space-y-5">
          <!-- Nama -->
          <div>
            <label class="block text-xs font-bold text-brand-dark mb-3 uppercase tracking-wide">Nama Lengkap</label>
            <input
              type="text"
              id="input_nama"
              placeholder="Masukkan nama Anda..."
              class="w-full px-5 py-3 border-2 border-brand-border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition-all text-sm"
              required>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-xs font-bold text-brand-dark mb-3 uppercase tracking-wide">Email</label>
            <input
              type="email"
              id="input_email"
              placeholder="Masukkan email Anda..."
              class="w-full px-5 py-3 border-2 border-brand-border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition-all text-sm"
              required>
          </div>

          <!-- Pertanyaan -->
          <div>
            <label class="block text-xs font-bold text-brand-dark mb-3 uppercase tracking-wide">Pertanyaan Anda</label>
            <textarea
              id="input_pertanyaan"
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
              id="btnRequestOtp"
              class="flex-1 px-6 py-3 bg-gradient-to-r from-brand-accent to-brand-accent/80 hover:from-brand-secondary hover:to-brand-secondary/80 text-white font-bold text-sm uppercase tracking-widest rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
              <iconify-icon icon="lucide:send" class="text-base"></iconify-icon>
              Kirim Kode Verifikasi
            </button>
          </div>
        </form>

        <!-- Step 2: OTP Verification Form (Hidden Initially) -->
        <form id="formVerifyOtp" class="hidden space-y-5">
          <div class="text-center py-2">
            <iconify-icon icon="lucide:mail-check" class="text-5xl text-brand-accent mb-3"></iconify-icon>
            <p class="text-sm text-brand-muted">Masukkan 6 digit kode verifikasi yang telah dikirim ke email Anda.</p>
            
            <!-- Local testing note -->
            <div id="otpDeveloperPreview" class="hidden mt-2 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-xs font-mono">
              [DEVELOPER ONLY] OTP: <span id="developerOtpCode" class="font-bold text-sm"></span>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-brand-dark mb-3 uppercase tracking-wide text-center">Kode Verifikasi</label>
            <input
              type="text"
              id="input_otp"
              placeholder="123456"
              maxlength="6"
              pattern="[0-9]{6}"
              class="w-full px-5 py-3 border-2 border-brand-border rounded-lg text-center font-mono text-2xl tracking-[0.5em] focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition-all"
              required>
          </div>

          <!-- Buttons -->
          <div class="flex gap-3 pt-4 border-t border-brand-border">
            <button
              type="button"
              id="btnBackToStep1"
              class="flex-1 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-brand-dark font-bold text-sm uppercase tracking-widest rounded-lg transition-colors">
              Kembali
            </button>
            <button
              type="submit"
              id="btnVerifyOtp"
              class="flex-1 px-6 py-3 bg-gradient-to-r from-brand-accent to-brand-accent/80 hover:from-brand-secondary hover:to-brand-secondary/80 text-white font-bold text-sm uppercase tracking-widest rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
              <iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon>
              Verifikasi & Kirim
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- DETAIL MODAL UNTUK PROGRAM & FASILITAS -->
  <div id="detailCardModal" class="fixed inset-0 bg-black/20 backdrop-blur-[1px] flex items-center justify-center z-50 hidden px-4 py-6 pointer-events-none">
    <div class="bg-white rounded-2xl max-w-xl w-full overflow-hidden shadow-2xl transform transition-all flex flex-col max-h-[80vh]">
      <!-- Header / Image -->
      <div class="relative h-64 sm:h-80 w-full bg-slate-100 flex-shrink-0">
        <img id="detailModalImg" src="" alt="Detail Image" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/25 to-transparent"></div>
        <div class="absolute bottom-6 left-6 right-6">
          <span id="detailModalCategory" class="text-[10px] font-bold tracking-[0.2em] uppercase text-amber-400 mb-1 inline-block">Program Unggulan</span>
          <h3 id="detailModalTitle" class="font-serif text-2xl md:text-3xl text-white font-semibold"></h3>
        </div>
      </div>
      <!-- Content / Description -->
      <div class="p-6 md:p-8 overflow-y-auto flex-grow">
        <p id="detailModalDesc" class="text-slate-600 leading-relaxed text-sm md:text-base whitespace-pre-line"></p>
      </div>
    </div>
  </div>

  <!-- JavaScript untuk Modal, FAQ & Form -->
  <script>
    function openDetailCardModal(element) {
      const category = element.getAttribute('data-category');
      const title = element.getAttribute('data-nama');
      const img = element.getAttribute('data-gambar');
      const desc = element.getAttribute('data-deskripsi');

      const modal = document.getElementById('detailCardModal');
      const modalCat = document.getElementById('detailModalCategory');
      const modalTitle = document.getElementById('detailModalTitle');
      const modalImg = document.getElementById('detailModalImg');
      const modalDesc = document.getElementById('detailModalDesc');

      if (modalCat) {
        modalCat.textContent = category;
        if (category === 'Fasilitas') {
          modalCat.className = "text-[10px] font-bold tracking-[0.2em] uppercase text-teal-400 mb-1 inline-block";
        } else {
          modalCat.className = "text-[10px] font-bold tracking-[0.2em] uppercase text-amber-400 mb-1 inline-block";
        }
      }
      if (modalTitle) modalTitle.textContent = title;
      if (modalImg) modalImg.src = img;
      if (modalDesc) modalDesc.textContent = desc;

      if (modal) {
        modal.classList.remove('hidden');
      }
    }

    function closeDetailCardModal() {
      const modal = document.getElementById('detailCardModal');
      if (modal) {
        modal.classList.add('hidden');
      }
    }

    // Close detail modal when clicking outside
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('detailCardModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
          closeDetailCardModal();
        }
      });
    });

    // Close detail modal on ESC key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && document.getElementById('detailCardModal') && !document.getElementById('detailCardModal').classList.contains('hidden')) {
        closeDetailCardModal();
      }
    });

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
      showStep(1);
      hideAlert();
    }

    function closeQuestionModal() {
      document.getElementById('questionModal').classList.add('hidden');
      document.body.style.overflow = 'auto';
      document.getElementById('formRequestOtp').reset();
      document.getElementById('formVerifyOtp').reset();
      showStep(1);
      hideAlert();
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

    // Helper functions for OTP steps
    function showStep(stepNum) {
      if (stepNum === 1) {
        document.getElementById('formRequestOtp').classList.remove('hidden');
        document.getElementById('formVerifyOtp').classList.add('hidden');
      } else {
        document.getElementById('formRequestOtp').classList.add('hidden');
        document.getElementById('formVerifyOtp').classList.remove('hidden');
        document.getElementById('input_otp').value = '';
      }
    }

    function showAlert(type, message) {
      const alertBox = document.getElementById('modalAlert');
      const alertIcon = document.getElementById('modalAlertIcon');
      const alertText = document.getElementById('modalAlertText');

      alertText.textContent = message;
      if (type === 'success') {
        alertBox.className = "rounded-lg p-4 flex items-start gap-3 border-2 bg-green-50 border-green-500 text-green-700";
        alertIcon.setAttribute('icon', 'lucide:check-circle');
      } else {
        alertBox.className = "rounded-lg p-4 flex items-start gap-3 border-2 bg-red-50 border-red-500 text-red-700";
        alertIcon.setAttribute('icon', 'lucide:alert-circle');
      }
      alertBox.classList.remove('hidden');
    }

    function hideAlert() {
      document.getElementById('modalAlert').classList.add('hidden');
    }

    // Go back to step 1
    document.getElementById('btnBackToStep1')?.addEventListener('click', function() {
      showStep(1);
      hideAlert();
    });

    // AJAX Form submissions
    document.getElementById('formRequestOtp')?.addEventListener('submit', function(e) {
      e.preventDefault();
      const nama = document.getElementById('input_nama').value;
      const email = document.getElementById('input_email').value;
      const pertanyaan = document.getElementById('input_pertanyaan').value;
      const btn = document.getElementById('btnRequestOtp');

      btn.disabled = true;
      btn.innerHTML = '<iconify-icon icon="lucide:loader-2" class="animate-spin text-base"></iconify-icon> Memproses...';
      hideAlert();

      const formData = new FormData();
      formData.append('action', 'request_otp');
      formData.append('nama', nama);
      formData.append('email', email);
      formData.append('pertanyaan', pertanyaan);

      fetch('pages/faq.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="lucide:send" class="text-base"></iconify-icon> Kirim Kode Verifikasi';
        
        if (data.success) {
          showAlert('success', data.message);
          showStep(2);
          if (data.otp_preview) {
            document.getElementById('developerOtpCode').textContent = data.otp_preview;
            document.getElementById('otpDeveloperPreview').classList.remove('hidden');
          }
        } else {
          showAlert('error', data.message);
        }
      })
      .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="lucide:send" class="text-base"></iconify-icon> Kirim Kode Verifikasi';
        showAlert('error', 'Terjadi kesalahan sistem, silakan coba lagi.');
      });
    });

    document.getElementById('formVerifyOtp')?.addEventListener('submit', function(e) {
      e.preventDefault();
      const otp = document.getElementById('input_otp').value;
      const btn = document.getElementById('btnVerifyOtp');

      btn.disabled = true;
      btn.innerHTML = '<iconify-icon icon="lucide:loader-2" class="animate-spin text-base"></iconify-icon> Memverifikasi...';
      hideAlert();

      const formData = new FormData();
      formData.append('action', 'verify_otp');
      formData.append('otp', otp);

      fetch('pages/faq.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon> Verifikasi & Kirim';

        if (data.success) {
          showAlert('success', data.message);
          setTimeout(() => {
            closeQuestionModal();
            window.location.reload();
          }, 2000);
        } else {
          showAlert('error', data.message);
        }
      })
      .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon> Verifikasi & Kirim';
        showAlert('error', 'Terjadi kesalahan sistem, silakan coba lagi.');
      });
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

    // Sidebar pagination untuk Berita Lainnya
    (function() {
      const itemsPerPage = 5;
      const items = document.querySelectorAll('.sidebar-berita-item');
      const totalItems = items.length;
      const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));
      let currentPage = 1;

      const prevBtn = document.getElementById('sidebarPrevBtn');
      const nextBtn = document.getElementById('sidebarNextBtn');

      function showSidebarPage(page) {
        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;

        items.forEach((item, index) => {
          item.style.display = index >= start && index < end ? '' : 'none';
        });

        currentPage = page;
        if (prevBtn) prevBtn.disabled = page === 1;
        if (nextBtn) nextBtn.disabled = page === totalPages;
      }

      if (prevBtn && nextBtn) {
        prevBtn.addEventListener('click', function() {
          if (currentPage > 1) {
            showSidebarPage(currentPage - 1);
          }
        });

        nextBtn.addEventListener('click', function() {
          if (currentPage < totalPages) {
            showSidebarPage(currentPage + 1);
          }
        });
      }

      showSidebarPage(1);
    })();

    // Pagination untuk Galeri (Photo dan Video)
    (function() {
      // Function untuk inisialisasi pagination untuk jenis tertentu
      function initPagination(jenis) {
        const itemsPerPage = jenis === 'Photo' ? 4 : 6; // Photo: 4 kolom, Video: 6 kolom (3 per baris x 2 baris)
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

    // Helper share copy link global jika components/share.php tidak terpanggil
    if (typeof copyShareLink === 'undefined') {
      window.copyShareLink = function(url, btn) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(url).then(function() {
            const orig = btn.innerHTML;
            btn.innerHTML = '<span class="text-[10px] font-semibold text-green-600">Tersalin</span>';
            setTimeout(function(){ btn.innerHTML = orig; }, 1500);
          }).catch(function(){ alert('Salin tautan gagal.'); });
        } else {
          try {
            const ta = document.createElement('textarea');
            ta.value = url;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            alert('Tautan disalin.');
          } catch(e) { alert('Salin tautan gagal.'); }
        }
      }
    }
  </script>

  </div>

<?php include 'components/footer.php'; ?>
