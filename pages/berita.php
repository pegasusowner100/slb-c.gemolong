<?php
require_once '../includes/db.php';
$title = "Berita & Kegiatan — SLB-C YPSLB Gemolong";

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
$page = max(1, (int)($_GET['page'] ?? 1));
$sidebarPerPage = 10;

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

include '../components/head.php';
?>
<body class="text-brand-dark font-sans glass-body">
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
        <?php if (empty($all_berita)): ?>
          <div class="text-center py-12">
            <iconify-icon icon="lucide:newspaper" class="text-6xl text-brand-muted/30 mb-4"></iconify-icon>
            <p class="text-brand-muted">Belum ada berita untuk saat ini.</p>
          </div>
        <?php else: ?>
          <div class="rounded-[32px] border border-brand-border/40 bg-brand-accent/10 overflow-hidden shadow-xl">
            <div class="border-b border-brand-border/40 bg-brand-accent/20 px-6 py-4">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <p class="text-[10px] uppercase tracking-[0.4em] text-brand-accent/90 font-semibold">Papan Berita</p>
                  <h3 class="text-lg font-semibold text-brand-dark"></h3>
                </div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1.5 text-[10px] uppercase tracking-[0.28em] font-semibold text-brand-dark">
                  <span class="h-2 w-2 rounded-full bg-brand-accent"></span>
                  Update
                </div>
              </div>
            </div>

            <div class="lg:grid lg:grid-cols-[2fr_1fr] gap-6 p-4 lg:p-6 bg-white">
              <main class="space-y-6">
                <?php if ($selectedBerita): ?>
                  <article class="bg-white overflow-hidden">
                    <div class="relative overflow-hidden h-[380px] md:h-[460px] rounded-none">
                      <img src="<?php echo htmlspecialchars($selectedBerita['gambar'] ?? 'https://picsum.photos/seed/' . urlencode($selectedBerita['judul'] ?? 'default') . '/1200/700'); ?>"
                           alt="<?php echo htmlspecialchars($selectedBerita['judul'] ?? 'Berita'); ?>"
                           class="w-full h-full object-cover rounded-none">
                    </div>
                    <div class="p-6 lg:p-8">
                      <div class="flex flex-wrap items-center justify-between gap-3 mb-4 text-xs uppercase tracking-[0.24em] font-semibold text-brand-muted">
                        <span><?php echo htmlspecialchars(formatTanggal($selectedBerita['tanggal_upload'] ?? $selectedBerita['tanggal'] ?? '')); ?></span>
                        <span class="bg-brand-accent text-black px-3 py-1.5 rounded-full shadow-sm"><?php echo htmlspecialchars($selectedBerita['kategori'] ?? 'Umum'); ?></span>
                      </div>
                      <div class="border-t border-brand-border/30 pt-4">
                        <div class="flex items-center gap-3 mb-3">
                          <span class="block h-1.5 w-14 bg-brand-accent rounded-full"></span>
                          <span class="text-[11px] uppercase tracking-[0.4em] text-brand-accent font-semibold">Berita</span>
                          <span class="block h-1.5 flex-1 bg-brand-accent rounded-full"></span>
                        </div>
                        <h2 class="font-serif text-3xl md:text-4xl font-bold text-brand-dark leading-tight mb-4"><?php echo htmlspecialchars($selectedBerita['judul'] ?? 'Berita Terbaru'); ?></h2>
                        <div class="prose prose-brand max-w-none text-brand-dark leading-relaxed">
                          <?php echo nl2br(htmlspecialchars(strip_tags($selectedBerita['konten'] ?? 'Belum ada konten untuk berita ini.'))); ?>
                        </div>
                      </div>
                    </div>
                  </article>
                <?php endif; ?>
              </main>

              <aside class="space-y-4 bg-slate-900 text-white rounded-[28px] border border-white/10 p-4 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                  <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-white/60">Berita Terbaru</p>
                    <h3 class="font-serif text-xl font-bold text-white">Lihat Lainnya</h3>
                  </div>
                </div>
                <?php
                  $sidebarTotalItems = count($all_berita);
                  $sidebarTotalPages = max(1, (int)ceil($sidebarTotalItems / $sidebarPerPage));
                  $sidebarItems = array_slice($all_berita, ($page - 1) * $sidebarPerPage, $sidebarPerPage);
                ?>
                <div class="divide-y divide-white/15">
                  <?php foreach ($sidebarItems as $item): ?>
                    <a href="?<?php echo buildQuery(['id' => $item['id'], 'page' => $page]); ?>" class="group block py-4 transition-all <?php echo ($selectedBerita && ($selectedBerita['id'] ?? '') === ($item['id'] ?? '')) ? 'bg-white/10 border-l-4 border-brand-accent pl-4 pr-4' : 'hover:bg-white/5 px-4'; ?>">
                      <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-14 h-14 overflow-hidden rounded-none bg-white/10 border border-white/10">
                          <img src="<?php echo htmlspecialchars($item['gambar'] ?? 'https://picsum.photos/seed/' . urlencode($item['judul'] ?? 'list') . '/240/240'); ?>" alt="<?php echo htmlspecialchars($item['judul'] ?? ''); ?>" class="w-full h-full object-cover rounded-none">
                        </div>
                        <div class="min-w-0">
                          <div class="flex flex-wrap items-center gap-2 mb-2 text-[10px] uppercase tracking-[0.24em] text-white/60">
                            <span><?php echo htmlspecialchars(formatTanggal($item['tanggal_upload'] ?? $item['tanggal'] ?? '')); ?></span>
                            <span class="inline-flex items-center rounded-full bg-brand-accent px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-black"><?php echo htmlspecialchars($item['kategori'] ?? 'Umum'); ?></span>
                          </div>
                          <h4 class="text-sm font-semibold text-white line-clamp-2"><?php echo htmlspecialchars($item['judul'] ?? ''); ?></h4>
                        </div>
                      </div>
                    </a>
                  <?php endforeach; ?>
                </div>
                <div class="mt-4 flex items-center justify-between gap-3 text-sm text-white/80">
                  <a href="?<?php echo buildQuery(['page' => max(1, $page - 1), 'id' => $selectedBerita['id'] ?? null]); ?>" class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-2 transition hover:bg-white/10 <?php echo $page <= 1 ? 'opacity-50 pointer-events-none' : ''; ?>">
                    <iconify-icon icon="lucide:chevrons-left" class="w-4 h-4"></iconify-icon> Sebelumnya
                  </a>
                  <span class="whitespace-nowrap">Halaman <?php echo $page; ?> / <?php echo $sidebarTotalPages; ?></span>
                  <a href="?<?php echo buildQuery(['page' => min($sidebarTotalPages, $page + 1), 'id' => $selectedBerita['id'] ?? null]); ?>" class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-2 transition hover:bg-white/10 <?php echo $page >= $sidebarTotalPages ? 'opacity-50 pointer-events-none' : ''; ?>">
                    Selanjutnya <iconify-icon icon="lucide:chevrons-right" class="w-4 h-4"></iconify-icon>
                  </a>
                </div>
              </aside>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  </div> <!-- glass-content-wrapper end -->
  <?php include '../components/footer.php'; ?>
</body>
</html>