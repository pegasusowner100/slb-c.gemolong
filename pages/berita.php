<?php
/**
 * ========================================
 * HALAMAN BERITA & KEGIATAN - pages/berita.php
 * ========================================
 * 
 * File ini menampilkan halaman daftar berita dan detail berita sekolah.
 * Fitur:
 * - Daftar berita dengan pagination
 * - Sidebar "Lihat Berita Lainnya" dengan 10 berita per halaman
 * - Cari berita berdasarkan judul, konten, atau kategori
 * - Detail berita dengan share buttons (Facebook, Twitter, WhatsApp, LinkedIn, Telegram, Email)
 * - Format tanggal Indonesia
 * - Alternative list view untuk semua berita
 * 
 * CATATAN PENTING:
 * - Halaman ini menggunakan Supabase untuk mengambil data berita
 * - Script share buttons berasal dari components/share.php (JANGAN DIHAPUS)
 * - Pastikan file components/share.php ada dan tidak ada error di dalamnya
 * - Jika halaman blank, lihat browser console dan server error log untuk debug
 * 
 * ========================================
 */

// ===== DEBUG MODE: Aktifkan untuk melihat error (PENTING untuk troubleshooting) =====
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include file-file yang diperlukan
require_once '../includes/db.php';

// Track pengunjung (optional - jika file ada)
if (file_exists(__DIR__ . '/../includes/track-visitor.php')) {
    require_once '../includes/track-visitor.php';
    trackVisitor('/pages/berita');
}
$title = "Berita & Kegiatan — SLB BC KARYA SEJAHTERA";

// Ambil data berita dari database
$all_berita = [];
if ($supabaseConnected) {
    $beritaResult = supabaseSelect('berita', ['order' => 'tanggal.desc']);
    if ($beritaResult['success']) {
        // Only show published berita
        $all_berita = array_filter($beritaResult['data'], function($item) {
            return ($item['status'] ?? 'published') === 'published';
        });
    }
}

$selectedId = $_GET['id'] ?? null;
$searchQuery = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$sidebarPerPage = 10;

if ($searchQuery !== '' && !empty($all_berita)) {
    $all_berita = array_filter($all_berita, function ($item) use ($searchQuery) {
        $judul = $item['judul'] ?? '';
        $konten = $item['konten'] ?? '';
        $kategori = $item['kategori'] ?? '';
        return stripos($judul, $searchQuery) !== false
            || stripos($konten, $searchQuery) !== false
            || stripos($kategori, $searchQuery) !== false;
    });
}

$selectedBerita = null;
if (!empty($selectedId) && !empty($all_berita)) {
    foreach ($all_berita as $item) {
        if ((string)($item['id'] ?? '') === (string)$selectedId) {
            $selectedBerita = $item;
            break;
        }
    }
}

if (!$selectedBerita && !empty($all_berita)) {
    $selectedBerita = reset($all_berita);
}

function buildQuery(array $params = []) {
    $current = $_GET;
    foreach ($params as $key => $value) {
        if ($value === null) {
            unset($current[$key]);
        } else {
            $current[$key] = $value;
        }
    }
    return http_build_query($current);
}

function formatTanggal($tanggalString = null) {
    try {
        $date = new DateTime($tanggalString ?: 'now');
    } catch (Exception $e) {
        $date = new DateTime();
    }

    $previousLocale = setlocale(LC_TIME, 0);
    setlocale(LC_TIME, 'id_ID.UTF-8', 'id_ID', 'Indonesian_indonesia.1252', 'indonesia');
    $formatted = strftime('%d %B %Y', $date->getTimestamp());
    setlocale(LC_TIME, $previousLocale);
    return $formatted ?: $date->format('d F Y');
}

if ($selectedBerita) {
    $title = htmlspecialchars($selectedBerita['judul']) . " — SLB BC KARYA SEJAHTERA";
    $og_title = $selectedBerita['judul'];
    $clean_content = strip_tags($selectedBerita['konten'] ?? '');
    $og_description = mb_strimwidth($clean_content, 0, 150, "...");
    $og_image = $selectedBerita['gambar'] ?? '';
    $og_type = 'article';
}

include '../components/head.php';
?>
  <?php include '../components/navbar.php'; ?>
  <div class="glass-content-wrapper">

  <section class="page-hero bg-brand-dark">
    <div class="max-w-7xl mx-auto px-6">
      <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label fade-in-up">Arsip Berita</span>
      <h1 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1] fade-in-up delay-100">Berita &amp; Kegiatan</h1>
      <p class="max-w-3xl mt-4 text-sm text-white/70"></p>
    </div>
  </section>

  <section class="py-24">
    <div class="max-w-7xl mx-auto px-6">
      <div class="glass-section">
        <div class="text-center mb-8 fade-in-up delay-100">
          <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Berita</span>
            <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Berita & Kegiatan</h2>
          </div>
        </div>
        <?php if (empty($all_berita)): ?>
          <div class="text-center py-12">
            <iconify-icon icon="lucide:newspaper" class="text-6xl text-brand-muted/30 mb-4"></iconify-icon>
            <p class="text-brand-muted">Belum ada berita untuk saat ini.</p>
          </div>
        <?php else: ?>
          <div class="rounded-[32px] border border-brand-border/40 bg-brand-accent/10 overflow-visible shadow-xl">
            <div class="border-b border-brand-border/40 bg-brand-accent/20 px-6 py-4">
              <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                  <p class="text-[10px] uppercase tracking-[0.4em] text-blue font-semibold">Papan Berita</p>
                  <h3 class="text-lg font-semibold text-brand-dark"></h3>
                </div>
                <form action="" method="get" class="w-full">
                  <label class="sr-only" for="search-query">Cari Berita</label>
                  <div class="relative max-w-3xl mx-auto">
                    <input id="search-query" name="q" type="text" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Cari berita..."
                      class="w-full rounded-full border border-slate-200 bg-white px-4 py-3 pr-12 text-sm text-slate-900 shadow-sm outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-200" />
                    <button type="submit" class="absolute right-1 top-1/2 -translate-y-1/2 rounded-full bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600 transition">
                      Cari
                    </button>
                  </div>
                </form>
              </div>
            </div>

            <div class="lg:grid lg:grid-cols-[2fr_1fr] gap-6 p-4 lg:p-6 bg-white">
              <main class="space-y-6">
                <?php if ($selectedBerita): ?>
                  <article class="bg-white overflow-hidden">
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
                      <div class="relative overflow-hidden w-full aspect-video bg-black rounded-lg mb-4">
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
                      <div class="relative overflow-hidden h-[380px] md:h-[460px] rounded-none">
                        <img src="<?php echo htmlspecialchars($selectedBerita['gambar'] ?? 'https://picsum.photos/seed/' . urlencode($selectedBerita['judul'] ?? 'default') . '/1200/700'); ?>"
                             alt="<?php echo htmlspecialchars($selectedBerita['judul'] ?? 'Berita'); ?>"
                             class="w-full h-full object-cover rounded-none">
                      </div>
                    <?php endif; ?>
                    <div class="p-6 lg:p-8">
                      <div class="flex flex-wrap items-center justify-between gap-3 mb-4 text-xs uppercase tracking-[0.24em] font-semibold text-brand-muted">
                        <span><?php echo htmlspecialchars(formatTanggal($selectedBerita['tanggal_upload'] ?? $selectedBerita['tanggal'] ?? '')); ?></span>
                        <span class="bg-orange-500 text-white px-3 py-1.5 rounded-full shadow-sm"><?php echo htmlspecialchars($selectedBerita['kategori'] ?? 'Umum'); ?></span>
                      </div>
                      <div class="border-t border-brand-border/30 pt-4">
                        <div class="flex items-center gap-3 mb-3">
                          <span class="block h-1.5 w-14 bg-orange-500 rounded-full"></span>
                          <span class="text-[11px] uppercase tracking-[0.4em] text-orange-600 font-semibold">Berita</span>
                          <span class="block h-1.5 flex-1 bg-orange-500 rounded-full"></span>
                        </div>
                        <h2 class="font-serif text-3xl md:text-4xl font-bold text-brand-dark leading-tight mb-4"><?php echo htmlspecialchars($selectedBerita['judul'] ?? 'Berita Terbaru'); ?></h2>
                        <?php
                          $share_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . rtrim(BASE_URL, '/') . '/pages/berita.php?id=' . urlencode($selectedBerita['id'] ?? '');
                          $share_title = $selectedBerita['judul'] ?? SITE_NAME;
                          include __DIR__ . '/../components/share.php';
                        ?>
                        <div class="prose prose-brand max-w-none text-brand-dark leading-relaxed text-justify">
                          <?php echo nl2br(htmlspecialchars(strip_tags($selectedBerita['konten'] ?? 'Belum ada konten untuk berita ini.'))); ?>
                        </div>
                      </div>
                    </div>
                  </article>
                <?php endif; ?>
              </main>

              <aside class="space-y-4 bg-white text-slate-900 rounded-[28px] border border-slate-200 p-4 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                  <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-700">Berita Terbaru</p>
                    <h3 class="font-serif text-xl font-bold text-slate-900">Lihat Lainnya</h3>
                  </div>
                </div>
                <?php
                  $sidebarTotalItems = count($all_berita);
                  $sidebarTotalPages = max(1, (int)ceil($sidebarTotalItems / $sidebarPerPage));
                  $sidebarItems = array_slice($all_berita, ($page - 1) * $sidebarPerPage, $sidebarPerPage);
                ?>
                <div class="divide-y divide-slate-200">
                  <?php foreach ($sidebarItems as $item): ?>
                    <a href="?<?php echo buildQuery(['id' => $item['id'], 'page' => $page]); ?>" class="group block py-4 transition-all <?php echo ($selectedBerita && ($selectedBerita['id'] ?? '') === ($item['id'] ?? '')) ? 'bg-slate-100 border-l-4 border-orange-500 pl-4 pr-4' : 'hover:bg-slate-50 px-4'; ?>">
                      <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-14 h-14 overflow-hidden rounded-none bg-slate-100 border border-slate-200">
                          <img src="<?php echo htmlspecialchars($item['gambar'] ?? 'https://picsum.photos/seed/' . urlencode($item['judul'] ?? 'list') . '/240/240'); ?>" alt="<?php echo htmlspecialchars($item['judul'] ?? ''); ?>" class="w-full h-full object-cover rounded-none">
                        </div>
                        <div class="min-w-0">
                          <div class="flex flex-wrap items-center gap-2 mb-2 text-[10px] uppercase tracking-[0.24em] text-slate-700">
                            <span><?php echo htmlspecialchars(formatTanggal($item['tanggal_upload'] ?? $item['tanggal'] ?? '')); ?></span>
                            <span class="inline-flex items-center rounded-full bg-orange-500 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-white"><?php echo htmlspecialchars($item['kategori'] ?? 'Umum'); ?></span>
                          </div>
                          <h4 class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($item['judul'] ?? ''); ?></h4>
                        </div>
                      </div>
                    </a>
                  <?php endforeach; ?>
                </div>
                <div class="mt-4 flex items-center justify-between gap-3 text-sm text-slate-700">
                  <a href="?<?php echo buildQuery(['page' => max(1, $page - 1), 'id' => $selectedBerita['id'] ?? null]); ?>" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-slate-100 px-3 py-2 transition hover:bg-slate-200 text-slate-900 <?php echo $page <= 1 ? 'opacity-50 pointer-events-none' : ''; ?>">
                    <iconify-icon icon="lucide:chevrons-left" class="w-4 h-4"></iconify-icon> Sebelumnya
                  </a>
                  <span class="whitespace-nowrap">Halaman <?php echo $page; ?> / <?php echo $sidebarTotalPages; ?></span>
                  <a href="?<?php echo buildQuery(['page' => min($sidebarTotalPages, $page + 1), 'id' => $selectedBerita['id'] ?? null]); ?>" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-slate-100 px-3 py-2 transition hover:bg-slate-200 text-slate-900 <?php echo $page >= $sidebarTotalPages ? 'opacity-50 pointer-events-none' : ''; ?>">
                    Selanjutnya <iconify-icon icon="lucide:chevrons-right" class="w-4 h-4"></iconify-icon>
                  </a>
                </div>
              </aside>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Tambahan: Berita List Alternative View -->
      <?php if (!empty($all_berita)): ?>
      <div class="mt-16">
        <div class="space-y-4">
          <?php foreach ($all_berita as $item): ?>
            <div class="rounded-[16px] border border-brand-border/40 bg-white overflow-hidden shadow-md hover:shadow-lg transition-shadow">
              <div class="lg:grid lg:grid-cols-[300px_1fr] gap-0 flex flex-col">
                <div class="relative overflow-hidden h-[240px] lg:h-[300px]">
                  <img src="<?php echo htmlspecialchars($item['gambar'] ?? 'https://picsum.photos/seed/' . urlencode($item['judul'] ?? 'berita') . '/600/400'); ?>"
                       alt="<?php echo htmlspecialchars($item['judul'] ?? 'Berita'); ?>"
                       class="w-full h-full object-cover">
                </div>
                    <div class="p-4 lg:p-6 flex flex-col justify-between">
                  <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                      <span class="text-xs uppercase tracking-[0.24em] font-semibold text-brand-muted"><?php echo htmlspecialchars(formatTanggal($item['tanggal_upload'] ?? $item['tanggal'] ?? '')); ?></span>
                      <span class="bg-orange-500 text-white px-3 py-1.5 rounded-full text-xs font-semibold uppercase tracking-[0.2em]"><?php echo htmlspecialchars($item['kategori'] ?? 'Umum'); ?></span>
                    </div>
                    <div class="border-t border-brand-border/30 pt-4 mb-3">
                      <div class="flex items-center gap-3 mb-3">
                        <span class="block h-1.5 w-12 bg-orange-500 rounded-full"></span>
                        <span class="text-[11px] uppercase tracking-[0.4em] text-orange-600 font-semibold">Berita</span>
                        <span class="block h-1.5 flex-1 bg-orange-500 rounded-full"></span>
                        </div>
                        <div class="mt-4">
                          <?php
                            $share_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . rtrim(BASE_URL, '/') . '/pages/berita.php?id=' . urlencode($item['id'] ?? '');
                            $share_title = $item['judul'] ?? SITE_NAME;
                            $compact = true;
                            include __DIR__ . '/../components/share.php';
                            $compact = false;
                          ?>
                        </div>
                    </div>
                    <h3 class="font-serif text-xl lg:text-2xl font-bold text-brand-dark leading-tight mb-3"><?php echo htmlspecialchars($item['judul'] ?? 'Berita'); ?></h3>
                    <div class="text-brand-dark leading-relaxed text-sm line-clamp-3 text-justify">
                      <?php echo htmlspecialchars(strip_tags($item['konten'] ?? 'Belum ada konten untuk berita ini.')); ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </section>

  </div> <!-- glass-content-wrapper end -->
  <?php include '../components/footer.php'; ?>
</body>
</html>