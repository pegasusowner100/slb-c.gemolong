
<?php
define('ADMIN_PAGE', true);
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/supabase_storage.php';
// old visitor tracking removed
require_login();

$title = "Dashboard Admin SLB BC KARYA SEJAHTERA " . SITE_NAME;
$page_title = "Dashboard";

// Cek koneksi database
$db_status = $supabaseConnected ? 'TERHUBUNG (SUPABASE ONLINE)' : 'TIDAK TERHUBUNG';
$db_class = $supabaseConnected ? 'bg-green-100 text-green-800 border-green-300' : 'bg-red-100 text-red-800 border-red-300';

// Hitung total data secara dinamis dari Supabase
$total_berita = 0;
$total_ppdb = 0;
$total_guru = 0;
$total_siswa = 0;
$total_galeri = 0;
$total_faq = 0;
$total_faq_belum_jawab = 0;
$total_program = 0;
$total_fasilitas = 0;
$total_prestasi = 0;
$total_pengumuman = 0;
$total_surat = 0;
$total_download = 0;

if ($supabaseConnected) {
    // Total Berita
    if (function_exists('supabaseCount')) {
      $c = supabaseCount('berita');
      if (is_array($c) && isset($c['count']) && $c['count'] !== null) $total_berita = $c['count'];
    } else {
      $tmp = supabaseSelect('berita', ['limit' => 1]);
      if ($tmp['success'] && is_array($tmp['data'])) $total_berita = count($tmp['data']);
    }

    // Total PPDB
    if (function_exists('supabaseCount')) {
      $c = supabaseCount('ppdb');
      if (is_array($c) && isset($c['count']) && $c['count'] !== null) $total_ppdb = $c['count'];
    } else {
      $tmp = supabaseSelect('ppdb', ['limit' => 1]);
      if ($tmp['success'] && is_array($tmp['data'])) $total_ppdb = count($tmp['data']);
    }

    // Total Guru
    if (function_exists('supabaseCount')) {
      $c = supabaseCount('guru');
      if (is_array($c) && isset($c['count']) && $c['count'] !== null) $total_guru = $c['count'];
    } else {
      $tmp = supabaseSelect('guru', ['limit' => 1]);
      if ($tmp['success'] && is_array($tmp['data'])) $total_guru = count($tmp['data']);
    }

    // Total Siswa
    if (function_exists('supabaseCount')) {
      $c = supabaseCount('siswa');
      if (is_array($c) && isset($c['count']) && $c['count'] !== null) $total_siswa = $c['count'];
    } else {
      $tmp = supabaseSelect('siswa', ['limit' => 1]);
      if ($tmp['success'] && is_array($tmp['data'])) $total_siswa = count($tmp['data']);
    }

    // Total Galeri
    if (function_exists('supabaseCount')) {
      $c = supabaseCount('galeri');
      if (is_array($c) && isset($c['count']) && $c['count'] !== null) $total_galeri = $c['count'];
    } else {
      $tmp = supabaseSelect('galeri', ['limit' => 1]);
      if ($tmp['success'] && is_array($tmp['data'])) $total_galeri = count($tmp['data']);
    }

    // Total Program
    if (function_exists('supabaseCount')) {
      $c = supabaseCount('program');
      if (is_array($c) && isset($c['count']) && $c['count'] !== null) $total_program = $c['count'];
    } else {
      $tmp = supabaseSelect('program', ['limit' => 1]);
      if ($tmp['success'] && is_array($tmp['data'])) $total_program = count($tmp['data']);
    }

    // Total Fasilitas
    if (function_exists('supabaseCount')) {
      $c = supabaseCount('fasilitas');
      if (is_array($c) && isset($c['count']) && $c['count'] !== null) $total_fasilitas = $c['count'];
    } else {
      $tmp = supabaseSelect('fasilitas', ['limit' => 1]);
      if ($tmp['success'] && is_array($tmp['data'])) $total_fasilitas = count($tmp['data']);
    }

    // Total Prestasi
    if (function_exists('supabaseCount')) {
      $c = supabaseCount('prestasi');
      if (is_array($c) && isset($c['count']) && $c['count'] !== null) $total_prestasi = $c['count'];
    } else {
      $tmp = supabaseSelect('prestasi', ['limit' => 1]);
      if ($tmp['success'] && is_array($tmp['data'])) $total_prestasi = count($tmp['data']);
    }

    // Total Pengumuman
    if (function_exists('supabaseCount')) {
      $c = supabaseCount('pengumuman');
      if (is_array($c) && isset($c['count']) && $c['count'] !== null) $total_pengumuman = $c['count'];
    } else {
      $tmp = supabaseSelect('pengumuman', ['limit' => 1]);
      if ($tmp['success'] && is_array($tmp['data'])) $total_pengumuman = count($tmp['data']);
    }

    // Total Upload File / Downloads
    if (function_exists('supabaseCount')) {
      $c = supabaseCount('download');
      if (is_array($c) && isset($c['count']) && $c['count'] !== null) $total_download = $c['count'];
    } else {
      $tmp = supabaseSelect('download', ['limit' => 1]);
      if ($tmp['success'] && is_array($tmp['data'])) $total_download = count($tmp['data']);
    }

    // Total Surat
    if (function_exists('supabaseCount')) {
      $c = supabaseCount('surat');
      if (is_array($c) && isset($c['count']) && $c['count'] !== null) $total_surat = $c['count'];
    } else {
      $tmp = supabaseSelect('surat', ['limit' => 1]);
      if ($tmp['success'] && is_array($tmp['data'])) $total_surat = count($tmp['data']);
    }

    // Surat: count entries where 'respon' is empty (not yet responded)
    $new_surat_count = 0;
    $allSurat = supabaseSelect('surat', []);
    if ($allSurat['success'] && !empty($allSurat['data'])) {
      foreach ($allSurat['data'] as $sr) {
        if (!isset($sr['respon']) || trim($sr['respon']) === '') $new_surat_count++;
      }
    }

    // PPDB: count entries with status == 'pending' (case-insensitive)
    $ppdb_unfinished = 0;
    $allPpdb = supabaseSelect('ppdb', []);
    if ($allPpdb['success'] && !empty($allPpdb['data'])) {
      foreach ($allPpdb['data'] as $pp) {
        if (isset($pp['status']) && strtolower(trim($pp['status'])) === 'pending') $ppdb_unfinished++;
      }
    }

    // Total FAQ
    if (function_exists('supabaseCount')) {
      $c = supabaseCount('faq');
      if (is_array($c) && isset($c['count']) && $c['count'] !== null) $total_faq = $c['count'];
    } else {
      $tmp = supabaseSelect('faq', ['limit' => 1]);
      if ($tmp['success'] && is_array($tmp['data'])) $total_faq = count($tmp['data']);
    }

    // Total FAQ Belum Terjawab (jawaban kosong atau null)
    $faqBelumJawabResult = supabaseSelect('faq', ['jawaban' => 'is.null']);
    if ($faqBelumJawabResult['success'] && !empty($faqBelumJawabResult['data'])) {
        $total_faq_belum_jawab = count($faqBelumJawabResult['data']);
    } else {
        // Jika query dengan is.null tidak bekerja, coba alternatif
        $allFaqResult = supabaseSelect('faq', []);
        if ($allFaqResult['success'] && !empty($allFaqResult['data'])) {
            foreach ($allFaqResult['data'] as $faq) {
                if (empty($faq['jawaban'])) {
                    $total_faq_belum_jawab++;
                }
            }
        }
    }
}

// Build visitor statistics: total accesses, per-month counts, and top pages
$visitor_total = 0;
$visitor_month = array_fill(1, 12, 0);
$visitor_pages = [];
$top_pages = [];

if ($supabaseConnected) {
  $allVisitors = supabaseSelect('visitor_logs', []);
  if ($allVisitors['success'] && !empty($allVisitors['data'])) {
    foreach ($allVisitors['data'] as $v) {
      $visitor_total++;
      $date = $v['visited_date'] ?? (isset($v['created_at']) ? substr($v['created_at'],0,10) : date('Y-m-d'));
      $m = (int) date('n', strtotime($date));
      if ($m >=1 && $m <= 12) $visitor_month[$m]++;
      $page = $v['page_path'] ?? 'Unknown';
      if (!isset($visitor_pages[$page])) $visitor_pages[$page] = 0;
      $visitor_pages[$page]++;
    }
    arsort($visitor_pages);
    $top_pages = array_slice($visitor_pages, 0, 3, true);
  }
}

// Bangun tren harian 7 hari terakhir (fallback jika sebelumnya di-hapus)
$daily_visitor_trend = [];
$days = 7;
for ($i = $days - 1; $i >= 0; $i--) {
  $d = date('Y-m-d', strtotime("-{$i} days"));
  $daily_visitor_trend[$d] = 0;
}

if ($supabaseConnected) {
  if (!isset($allVisitors) || !is_array($allVisitors)) {
    $allVisitors = supabaseSelect('visitor_logs', []);
  }
  if (is_array($allVisitors) && !empty($allVisitors['data'])) {
    foreach ($allVisitors['data'] as $v) {
      $date = $v['visited_date'] ?? (isset($v['created_at']) ? substr($v['created_at'], 0, 10) : date('Y-m-d'));
      if (isset($daily_visitor_trend[$date])) $daily_visitor_trend[$date]++;
    }
  }
}

  // Parse local Supabase schema file to get list of tables (non-realtime)
  $dbTables = [];
  $schemaPath = realpath(__DIR__ . '/../supabase.sql');
  if ($schemaPath && file_exists($schemaPath)) {
    $schemaContent = file_get_contents($schemaPath);
    if ($schemaContent !== false) {
      preg_match_all('/CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?\s+([a-zA-Z0-9_]+)/i', $schemaContent, $matches);
      if (!empty($matches[1])) {
        $dbTables = array_values(array_unique($matches[1]));
      }
    }
  }

  $homepageSectionsPath = realpath(__DIR__ . '/../includes/homepage_sections.php') ?: __DIR__ . '/../includes/homepage_sections.php';
  $defaultHomepageSections = [
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
  $homepageSections = $defaultHomepageSections;
  if (file_exists($homepageSectionsPath)) {
      $loadedSections = include $homepageSectionsPath;
      if (is_array($loadedSections)) {
          $homepageSections = array_merge($defaultHomepageSections, $loadedSections);
      }
  }

  $saveMessage = '';
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['section']) && is_array($_POST['section'])) {
      $updatedSections = [];
      foreach ($defaultHomepageSections as $sectionKey => $defaultValue) {
          $updatedSections[$sectionKey] = !empty($_POST['section'][$sectionKey]);
      }
      $phpContent = "<?php\nreturn " . var_export($updatedSections, true) . ";\n";
      file_put_contents($homepageSectionsPath, $phpContent);
      $homepageSections = $updatedSections;
      $saveMessage = 'Pengaturan tampilan homepage berhasil disimpan.';
  }

include 'components/head.php';
include 'components/sidebar.php';
?>
  <!-- Main Content -->
  <main class="flex-1 flex flex-col overflow-hidden">
    <?php include 'components/header.php'; ?>
    <div class="flex-1 overflow-y-auto p-8">
      <div class="max-w-7xl compact-cards">
        <!-- Statistik pengunjung dihapus -->

        <!-- Statistik Cards (6 kolom per baris pada layar ekstra besar) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
          <!-- Program -->
           <div class="bg-gradient-to-br from-sky-500 to-sky-600 p-6 rounded-2xl shadow-lg hover:shadow-2xl text-white border border-white/20 hover:-translate-y-1.5 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-bold uppercase tracking-widest opacity-90">Program</span>
              <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <iconify-icon icon="lucide:book" class="text-2xl"></iconify-icon>
              </div>
            </div>
            <div class="text-4xl font-bold mb-1"><?php echo $total_program; ?></div>
            <div class="text-xs opacity-80">
              <a href="kelola-program.php" class="inline-block bg-white text-sky-600 font-semibold px-3 py-2 rounded-md shadow">Kelola</a>
            </div>
          </div>

          <!-- Fasilitas -->
          <div class="bg-gradient-to-br from-teal-500 to-teal-600 p-6 rounded-2xl shadow-lg hover:shadow-2xl text-white border border-white/20 hover:-translate-y-1.5 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-bold uppercase tracking-widest opacity-90">Fasilitas</span>
              <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <iconify-icon icon="lucide:building" class="text-2xl"></iconify-icon>
              </div>
            </div>
            <div class="text-4xl font-bold mb-1"><?php echo $total_fasilitas; ?></div>
            <div class="text-xs opacity-80">
              <a href="kelola-fasilitas.php" class="inline-block bg-white text-teal-600 font-semibold px-3 py-2 rounded-md shadow">Kelola</a>
            </div>
          </div>

          <!-- Prestasi -->
          <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 p-6 rounded-2xl shadow-lg hover:shadow-2xl text-white border border-white/20 hover:-translate-y-1.5 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-bold uppercase tracking-widest opacity-90">Prestasi</span>
              <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <iconify-icon icon="lucide:award" class="text-2xl"></iconify-icon>
              </div>
            </div>
            <div class="text-4xl font-bold mb-1"><?php echo $total_prestasi; ?></div>
            <div class="text-xs opacity-80">
              <a href="kelola-prestasi.php" class="inline-block bg-white text-yellow-700 font-semibold px-3 py-2 rounded-md shadow">Kelola</a>
            </div>
          </div>

          <!-- Guru -->
          <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 p-6 rounded-2xl shadow-lg hover:shadow-2xl text-white border border-white/20 hover:-translate-y-1.5 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-bold uppercase tracking-widest opacity-90">Guru & Staff</span>
              <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <iconify-icon icon="lucide:user-check" class="text-2xl"></iconify-icon>
              </div>
            </div>
            <div class="text-4xl font-bold mb-1"><?php echo $total_guru; ?></div>
            <div class="text-xs opacity-80">
              <a href="kelola-guru.php" class="inline-block bg-white text-emerald-700 font-semibold px-3 py-2 rounded-md shadow">Kelola</a>
            </div>
          </div>

          <!-- Siswa -->
          <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-2xl shadow-lg hover:shadow-2xl text-white border border-white/20 hover:-translate-y-1.5 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-bold uppercase tracking-widest opacity-90">Siswa</span>
              <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <iconify-icon icon="lucide:users-2" class="text-2xl"></iconify-icon>
              </div>
            </div>
            <div class="text-4xl font-bold mb-1"><?php echo $total_siswa; ?></div>
            <div class="text-xs opacity-80">
              <a href="kelola-siswa.php" class="inline-block bg-white text-blue-700 font-semibold px-3 py-2 rounded-md shadow">Kelola</a>
            </div>
          </div>

          <!-- (PPDB, Surat, FAQ moved below into a three-column row) -->

          <!-- Berita -->
          <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-2xl shadow-lg hover:shadow-2xl text-white border border-white/20 hover:-translate-y-1.5 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-bold uppercase tracking-widest opacity-90">Berita</span>
              <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <iconify-icon icon="lucide:file-text" class="text-2xl"></iconify-icon>
              </div>
            </div>
            <div class="text-4xl font-bold mb-1"><?php echo $total_berita; ?></div>
            <div class="text-xs opacity-80">
              <a href="kelola-berita.php" class="inline-block bg-white text-purple-600 font-semibold px-3 py-2 rounded-md shadow">Kelola</a>
            </div>
          </div>

          <!-- Galeri -->
          <div class="bg-gradient-to-br from-orange-500 to-orange-600 p-6 rounded-2xl shadow-lg hover:shadow-2xl text-white border border-white/20 hover:-translate-y-1.5 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-bold uppercase tracking-widest opacity-90">Galeri</span>
              <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <iconify-icon icon="lucide:images" class="text-2xl"></iconify-icon>
              </div>
            </div>
            <div class="text-4xl font-bold mb-1"><?php echo $total_galeri; ?></div>
            <div class="text-xs opacity-80">
              <a href="kelola-galeri.php" class="inline-block bg-white text-orange-600 font-semibold px-3 py-2 rounded-md shadow">Kelola</a>
            </div>
          </div>

          <!-- Upload File -->
          <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 p-6 rounded-2xl shadow-lg hover:shadow-2xl text-white border border-white/20 hover:-translate-y-1.5 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-bold uppercase tracking-widest opacity-90">Upload File</span>
              <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <iconify-icon icon="lucide:upload" class="text-2xl"></iconify-icon>
              </div>
            </div>
            <div class="text-4xl font-bold mb-1"><?php echo $total_download; ?></div>
            <div class="text-xs opacity-80">
              <a href="kelola-download.php" class="inline-block bg-white text-cyan-600 font-semibold px-3 py-2 rounded-md shadow">Kelola</a>
            </div>
          </div>

          <!-- Pengumuman -->
          <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 p-6 rounded-2xl shadow-lg hover:shadow-2xl text-white border border-white/20 hover:-translate-y-1.5 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-bold uppercase tracking-widest opacity-90">Pengumuman</span>
              <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <iconify-icon icon="lucide:megaphone" class="text-2xl"></iconify-icon>
              </div>
            </div>
            <div class="text-4xl font-bold mb-1"><?php echo $total_pengumuman; ?></div>
            <div class="text-xs opacity-80">
              <a href="kelola-pengumuman.php" class="inline-block bg-white text-indigo-600 font-semibold px-3 py-2 rounded-md shadow">Kelola</a>
            </div>
          </div>
          
          <!-- PPDB -->
          <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 p-6 rounded-2xl shadow-lg hover:shadow-2xl text-white border border-white/20 hover:-translate-y-1.5 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-bold uppercase tracking-widest opacity-90">PPDB</span>
              <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <iconify-icon icon="lucide:clipboard-list" class="text-2xl"></iconify-icon>
              </div>
            </div>
            <div class="text-4xl font-bold mb-1"><?php echo $total_ppdb; ?></div>
            <div class="flex items-center gap-3">
              <a href="kelola-ppdb.php" class="inline-block bg-white text-indigo-700 font-semibold px-3 py-2 rounded-md shadow">Kelola</a>
              <?php if (!empty($ppdb_unfinished)): ?>
                <div class="inline-flex items-center gap-2 bg-amber-100 text-amber-700 px-3 py-2 rounded-full text-lg font-bold animate-pulse">
                  <iconify-icon icon="lucide:bell" class="text-xl"></iconify-icon>
                  <span class="text-lg"><?php echo htmlspecialchars($ppdb_unfinished); ?></span>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Surat Menyurat -->
          <div class="bg-gradient-to-br from-rose-500 to-rose-600 p-6 rounded-2xl shadow-lg hover:shadow-2xl text-white border border-white/20 hover:-translate-y-1.5 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-bold uppercase tracking-widest opacity-90">Surat Menyurat</span>
              <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <iconify-icon icon="lucide:mail" class="text-2xl"></iconify-icon>
              </div>
            </div>
            <div class="text-4xl font-bold mb-1"><?php echo $total_surat; ?></div>
            <div class="flex items-center gap-3">
              <a href="kelola-surat.php" class="inline-block bg-white text-rose-600 font-semibold px-3 py-2 rounded-md shadow">Kelola</a>
              <?php if (!empty($new_surat_count)): ?>
                <div class="inline-flex items-center gap-2 bg-rose-100 text-rose-700 px-3 py-2 rounded-full text-lg font-bold animate-pulse">
                  <iconify-icon icon="lucide:bell" class="text-xl"></iconify-icon>
                  <span class="text-lg"><?php echo htmlspecialchars($new_surat_count); ?></span>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- FAQ -->
          <div class="bg-gradient-to-br from-amber-500 to-amber-600 p-6 rounded-2xl shadow-lg hover:shadow-2xl text-white border border-white/20 hover:-translate-y-1.5 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-bold uppercase tracking-widest opacity-90">FAQ</span>
              <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <iconify-icon icon="lucide:help-circle" class="text-2xl"></iconify-icon>
              </div>
            </div>
            <div class="text-4xl font-bold mb-1"><?php echo $total_faq; ?></div>
            <div class="flex items-center gap-3">
              <a href="kelola-faq.php" class="inline-block bg-white text-amber-700 font-semibold px-3 py-2 rounded-md shadow">Kelola</a>
              <?php if (!empty($total_faq_belum_jawab)): ?>
                <div class="inline-flex items-center gap-2 bg-yellow-100 text-yellow-700 px-3 py-2 rounded-full text-lg font-bold animate-pulse">
                  <iconify-icon icon="lucide:bell" class="text-xl"></iconify-icon>
                  <span class="text-lg"><?php echo htmlspecialchars($total_faq_belum_jawab); ?></span>
                </div>
              <?php endif; ?>
            </div>
          </div>

        </div>

        <?php if (!empty($saveMessage)): ?>
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900">
          <?php echo htmlspecialchars($saveMessage); ?>
        </div>
        <?php endif; ?>

        <!-- Daily visitor trend chart removed per request -->

        <!-- Top Pages Table -->
        <!-- (Old detailed top-pages table removed; replaced by concise top-3 list above) -->

        <div class="bg-white rounded-2xl border border-slate-200 shadow-md p-6 mb-8">
          <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
              <h3 class="text-lg font-semibold text-slate-900">Pengaturan Section Homepage</h3>
              <p class="text-sm text-slate-500">Centang section yang ingin ditampilkan di halaman Utama.</p>
            </div>
          </div>
          <form id="homepage-section-settings" method="post" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <?php
              $sectionOptions = [
                'hero' => 'Hero',
                  'running_text' => 'Running Text',
                'profil' => 'Profil Sekolah',
                'tentang' => 'Tentang Sekolah',
                'struktur' => 'Struktur Organisasi',
                'sumberdaya_preview' => 'Sumber Daya Manusia',
                'program' => 'Program Unggulan',
                'fasilitas' => 'Fasilitas',
                'prestasi' => 'Prestasi',
                'cta_ppdb' => 'CTA PPDB',
                'berita' => 'Berita Terbaru',
                'galeri' => 'Galeri',
                'statistik' => 'Statistik Sekolah',
                'anggaran' => 'Anggaran',
                'layanan' => 'Layanan Online',
                'faq' => 'FAQ',
              ];
              foreach ($sectionOptions as $key => $label):
            ?>
              <label class="flex items-center justify-between rounded-2xl border border-slate-200 p-4 cursor-pointer hover:border-slate-300 transition-colors bg-slate-50/30">
                <span class="text-sm font-medium text-slate-800"><?php echo $label; ?></span>
                <div class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" name="section[<?php echo $key; ?>]" value="1" <?php echo !empty($homepageSections[$key]) ? 'checked' : ''; ?> class="sr-only peer">
                  <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                </div>
              </label>
            <?php endforeach; ?>
              <div class="col-span-full flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-2">
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-300/30 transition-colors hover:bg-emerald-700">Simpan Pengaturan</button>
              </div>
          </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Berita Terbaru (3 latest items) -->
          <div class="bg-white rounded-2xl border border-slate-100 shadow-md overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex items-center justify-between bg-gradient-to-r from-purple-50 to-purple-100">
              <h3 class="font-semibold text-slate-900 text-lg">Berita Terbaru</h3>
              <a href="kelola-berita.php" class="text-sm font-bold text-purple-600 uppercase tracking-wider hover:text-purple-700 transition-colors flex items-center gap-1">
                Lihat Semua <iconify-icon icon="lucide:arrow-right"></iconify-icon>
              </a>
            </div>
            <div class="p-4">
              <?php if ($supabaseConnected):
                // Try ordering by 'tanggal' field first, fall back to 'created_at' if missing
                $beritaList = supabaseSelect('berita', ['order' => 'tanggal.desc', 'limit' => 3]);
                if ((!$beritaList['success'] || empty($beritaList['data']))) {
                  $beritaList = supabaseSelect('berita', ['order' => 'created_at.desc', 'limit' => 3]);
                } else {
                  $firstB = $beritaList['data'][0] ?? null;
                  if ($firstB && (!isset($firstB['tanggal']) || empty($firstB['tanggal']))) {
                    $beritaList = supabaseSelect('berita', ['order' => 'created_at.desc', 'limit' => 3]);
                  }
                }

                if ($beritaList['success'] && !empty($beritaList['data'])): ?>
                  <ul class="space-y-2 text-sm">
                    <?php foreach ($beritaList['data'] as $b): ?>
                      <li class="flex items-center justify-between">
                        <div class="font-medium text-[#1E40AF]"><?php echo htmlspecialchars($b['judul'] ?? '-'); ?></div>
                        <div class="text-xs text-[#1E40AF]"><?php echo date('d M Y', strtotime($b['tanggal'] ?? $b['created_at'] ?? '')); ?></div>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <div class="text-sm text-slate-600">Belum ada berita</div>
                <?php endif;
              else: ?>
                <div class="text-sm text-slate-600">Supabase tidak terhubung</div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Galeri Terbaru -->
          <div class="bg-white rounded-2xl border border-slate-100 shadow-md overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex items-center justify-between bg-gradient-to-r from-orange-50 to-orange-100">
              <h3 class="font-semibold text-slate-900 text-lg">Galeri Terbaru</h3>
              <a href="kelola-galeri.php" class="text-sm font-bold text-orange-600 uppercase tracking-wider hover:text-orange-700 transition-colors flex items-center gap-1">
                Lihat Semua <iconify-icon icon="lucide:arrow-right"></iconify-icon>
              </a>
            </div>
            <div class="p-4">
              <?php if ($supabaseConnected):
                $galeriList = supabaseSelect('galeri', ['order' => 'created_at.desc', 'limit' => 3]);
                if ($galeriList['success'] && !empty($galeriList['data'])): ?>
                  <ul class="space-y-2 text-sm">
                    <?php foreach ($galeriList['data'] as $g): ?>
                      <li class="flex items-center justify-between">
                        <div class="font-medium text-[#1E40AF]"><?php echo htmlspecialchars($g['judul'] ?? $g['caption'] ?? '-'); ?></div>
                        <div class="text-xs text-[#1E40AF]"><?php echo date('d M Y', strtotime($g['created_at'] ?? $g['tanggal'] ?? '')); ?></div>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <div class="text-sm text-slate-600">Belum ada galeri</div>
                <?php endif;
              else: ?>
                <div class="text-sm text-slate-600">Supabase tidak terhubung</div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Pengumuman Terbaru (moved above Download) -->
          <div class="bg-white rounded-2xl border border-slate-100 shadow-md overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex items-center justify-between bg-gradient-to-r from-emerald-50 to-emerald-100">
              <h3 class="font-semibold text-slate-900 text-lg">Pengumuman Terbaru</h3>
              <a href="kelola-pengumuman.php" class="text-sm font-bold text-emerald-600 uppercase tracking-wider hover:text-emerald-700 transition-colors flex items-center gap-1">
                Lihat Semua <iconify-icon icon="lucide:arrow-right"></iconify-icon>
              </a>
            </div>
            <div class="p-4">
              <?php if ($supabaseConnected):
                // Fetch pengumuman ordering by 'tanggal' with fallback to 'created_at'
                $pengList = supabaseSelect('pengumuman', ['order' => 'tanggal.desc', 'limit' => 3]);
                if ((!$pengList['success'] || empty($pengList['data']))) {
                  $pengList = supabaseSelect('pengumuman', ['order' => 'created_at.desc', 'limit' => 3]);
                } else {
                  $first = $pengList['data'][0] ?? null;
                  if ($first && (!isset($first['tanggal']) || empty($first['tanggal']))) {
                    $pengList = supabaseSelect('pengumuman', ['order' => 'created_at.desc', 'limit' => 3]);
                  }
                }

                if ($pengList['success'] && !empty($pengList['data'])): ?>
                  <ul class="space-y-2 text-sm">
                    <?php foreach ($pengList['data'] as $p): ?>
                      <li class="flex items-center justify-between">
                        <div class="font-medium text-[#1E40AF]"><?php echo htmlspecialchars($p['judul'] ?? '-'); ?></div>
                        <div class="text-xs text-[#1E40AF]"><?php echo date('d M Y', strtotime($p['tanggal'] ?? $p['created_at'] ?? '')); ?></div>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <div class="text-sm text-slate-600">Belum ada pengumuman</div>
                <?php endif;
              else: ?>
                <div class="text-sm text-slate-600">Supabase tidak terhubung</div>
              <?php endif; ?>
            </div>
          </div>

          

            <!-- Latest content rows: Berita, Galeri, Pengumuman -->
            <div class="space-y-6">
              

              <!-- Download / File Upload Terbaru -->
              <div class="bg-white rounded-2xl border border-slate-100 shadow-md overflow-hidden">
                <div class="p-6 border-b border-slate-200 flex items-center justify-between bg-gradient-to-r from-sky-50 to-sky-100">
                  <h3 class="font-semibold text-slate-900 text-lg">Upload File / Download Terbaru</h3>
                  <a href="kelola-download.php" class="text-sm font-bold text-sky-600 uppercase tracking-wider hover:text-sky-700 transition-colors flex items-center gap-1">
                    Lihat Semua <iconify-icon icon="lucide:arrow-right"></iconify-icon>
                  </a>
                </div>
                <div class="p-4">
                  <?php if ($supabaseConnected):
                    $downList = supabaseSelect('download', ['order' => 'created_at.desc', 'limit' => 3]);
                    if ($downList['success'] && !empty($downList['data'])): ?>
                      <ul class="space-y-2 text-sm">
                        <?php foreach ($downList['data'] as $d): ?>
                          <?php
                            // Prefer explicit title fields (`judul` or `title`) for display label
                            $displayLabel = null;
                            if (!empty($d['judul'])) $displayLabel = $d['judul'];
                            elseif (!empty($d['title'])) $displayLabel = $d['title'];
                            else {
                              $rawName = $d['nama_file'] ?? $d['path'] ?? 'File';
                              if (!is_string($rawName)) $rawName = 'File';
                              $displayLabel = basename(parse_url($rawName, PHP_URL_PATH) ?: $rawName);
                            }
                          ?>
                          <li class="flex items-center justify-between">
                            <div class="font-medium text-[#1E40AF]"><?php echo htmlspecialchars($displayLabel); ?></div>
                            <div class="text-xs text-[#1E40AF]"><?php echo date('d M Y', strtotime($d['created_at'] ?? '')); ?></div>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php else: ?>
                      <div class="text-sm text-slate-600">Belum ada file</div>
                    <?php endif;
                  else: ?>
                    <div class="text-sm text-slate-600">Supabase tidak terhubung</div>
                  <?php endif; ?>
                </div>
              </div>

              
            </div>

          
        </div>
      </div>
    </div>
  </main>
</body>
</html>

