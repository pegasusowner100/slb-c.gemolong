<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

$title = 'Pengumuman - ' . SITE_NAME;

function pengumumanFetchList() {
    global $supabaseConnected;

    if (!$supabaseConnected) {
        return [
            'success' => false,
            'data' => [],
            'error' => 'Koneksi Supabase belum tersedia.'
        ];
    }

    $attempts = [
        ['order' => 'created_at.desc'],
        ['order' => 'tgl.desc'],
        []
    ];

    $lastError = '';
    foreach ($attempts as $filters) {
        $result = supabaseSelect('pengumuman', $filters);
        if ($result['success']) {
            return $result;
        }
        $lastError = $result['error'] ?? 'Gagal memuat data.';
    }

    return [
        'success' => false,
        'data' => [],
        'error' => $lastError
    ];
}

function pengumumanDateValue($item) {
    return $item['tgl'] ?? $item['tanggal'] ?? $item['created_at'] ?? $item['updated_at'] ?? '';
}

function pengumumanFormatDate($value) {
    if (empty($value)) return '-';

    try {
        $date = new DateTime($value);
    } catch (Exception $e) {
        return '-';
    }

    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    return $date->format('d') . ' ' . $months[(int) $date->format('n')] . ' ' . $date->format('Y');
}

function pengumumanPriorityClass($priority) {
    switch ($priority) {
        case 'Segera':
            return 'bg-red-100 text-red-700 border-red-200';
        case 'Sangat Penting':
            return 'bg-orange-100 text-orange-700 border-orange-200';
        case 'Penting':
            return 'bg-amber-100 text-amber-700 border-amber-200';
        default:
            return 'bg-emerald-100 text-emerald-700 border-emerald-200';
    }
}

// Resolve and validate PDF URL for embedding
function resolvePdfUrl($pdf) {
  if (empty($pdf)) return '';
  $pdf = trim($pdf);

  // If already an absolute URL, return as-is
  if (filter_var($pdf, FILTER_VALIDATE_URL)) return $pdf;

  // If it's a protocol-relative URL
  if (strpos($pdf, '//') === 0) return 'https:' . $pdf;

  // If it's a path starting with a slash, prefix with BASE_URL
  if (strpos($pdf, '/') === 0) {
    $base = defined('BASE_URL') && BASE_URL !== '' ? rtrim(BASE_URL, '/') : '';
    return ($base === '' ? '' : $base) . $pdf;
  }

  // Otherwise assume it's a relative upload path and prefix with LOCAL_UPLOAD_BASE_URL_PUBLIC if available
  if (defined('LOCAL_UPLOAD_BASE_URL_PUBLIC') && LOCAL_UPLOAD_BASE_URL_PUBLIC !== '') {
    return rtrim(LOCAL_UPLOAD_BASE_URL_PUBLIC, '/') . '/' . ltrim($pdf, '/');
  }

  // Fallback: return as-is (may be handled by browser)
  return $pdf;
}

// Check if PDF URL is reachable and actually serves a PDF
function isPdfAccessible($url) {
  if (empty($url)) return false;
  if (!filter_var($url, FILTER_VALIDATE_URL)) return false;

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_NOBODY, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 8);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: '';
  curl_close($ch);

  if ($httpCode >= 200 && $httpCode < 400 && stripos($contentType, 'pdf') !== false) {
    return true;
  }
  return false;
}

function isPdfUrlValid($url) {
  if (empty($url)) {
    return false;
  }
  if (filter_var($url, FILTER_VALIDATE_URL)) {
    return true;
  }
  if (strpos($url, '/') === 0) {
    return true;
  }
  return false;
}

function pengumumanSortDate($item) {
    $date = $item['created_at'] ?? $item['updated_at'] ?? pengumumanDateValue($item);
    $time = $date ? strtotime($date) : false;
    return $time ?: 0;
}

$result = pengumumanFetchList();
$pengumuman = $result['success'] ? ($result['data'] ?? []) : [];
$fetchError = $result['success'] ? '' : ($result['error'] ?? 'Gagal memuat pengumuman.');

$pengumuman = array_values(array_filter($pengumuman, function ($item) {
    return ($item['status'] ?? 'published') === 'published';
}));

usort($pengumuman, function ($a, $b) {
    return pengumumanSortDate($b) <=> pengumumanSortDate($a);
});

$selectedId = $_GET['id'] ?? '';
$selected = $pengumuman[0] ?? null;
if ($selectedId !== '') {
    foreach ($pengumuman as $item) {
        if ((string) ($item['id'] ?? '') === (string) $selectedId) {
            $selected = $item;
            break;
        }
    }
}

include '../components/head.php';
?>
  <?php include '../components/navbar.php'; ?>

  <main class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/20 to-slate-100">
    <section class="page-hero bg-brand-dark">
      <div class="max-w-7xl mx-auto px-6">
        <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label">Informasi</span>
        <h1 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1]">Pengumuman</h1>
      </div>
    </section>

    <section class="py-8 md:py-10">
      <div class="max-w-7xl mx-auto px-4 md:px-6">
        <div class="text-center mb-8 fade-in-up delay-100">
          <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Pengumuman</span>
            <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Pengumuman Sekolah</h2>
          </div>
        </div>
        <?php if ($fetchError): ?>
          <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">
            Gagal memuat pengumuman: <?php echo htmlspecialchars($fetchError); ?>
          </div>
        <?php endif; ?>

        <div class="grid gap-6 lg:grid-cols-[360px,1fr]">
          <aside class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-teal-50">
              <h2 class="text-xs font-black text-emerald-700 uppercase tracking-widest flex items-center gap-2">
                <iconify-icon icon="lucide:clock" class="text-sm"></iconify-icon>
                Daftar Pengumuman
              </h2>
            </div>

            <div class="p-4 border-b border-slate-100">
              <label class="relative block">
                <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></iconify-icon>
                <input id="pengumumanSearch" type="search" placeholder="Cari pengumuman..." class="w-full rounded-xl border-2 border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-xs font-bold text-slate-700 outline-none transition-all focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
              </label>
            </div>

            <div id="pengumumanList" class="max-h-[680px] overflow-y-auto p-4 space-y-2">
              <?php if (empty($pengumuman)): ?>
                <div class="p-8 text-center text-slate-400">
                  <iconify-icon icon="lucide:inbox" class="text-4xl mb-2"></iconify-icon>
                  <p class="text-xs font-black uppercase">Belum ada pengumuman</p>
                </div>
              <?php endif; ?>

              <?php foreach ($pengumuman as $index => $item): ?>
                <?php
                  $id = (string) ($item['id'] ?? $index);
                  $isSelected = $selected && (string) ($selected['id'] ?? '') === (string) ($item['id'] ?? '');
                  $priority = $item['prioritas'] ?? 'Normal';
                  $searchText = strtolower(trim(($item['no'] ?? '') . ' ' . ($item['judul'] ?? '') . ' ' . ($item['sumber'] ?? '') . ' ' . $priority));
                ?>
                <button
                  type="button"
                  data-pengumuman-card
                  data-target="pengumuman-<?php echo htmlspecialchars($id); ?>"
                  data-search="<?php echo htmlspecialchars($searchText); ?>"
                  class="w-full text-left p-4 rounded-xl border-2 transition-all relative <?php echo $isSelected ? 'bg-gradient-to-br from-emerald-600 to-teal-600 text-white border-transparent shadow-lg' : 'bg-gradient-to-br from-slate-50 to-slate-100 text-slate-900 border-slate-200 hover:border-slate-300 hover:shadow-md'; ?>"
                >
                  <?php if ($index === 0): ?>
                    <span class="absolute top-2 right-2 px-2 py-0.5 text-[8px] font-black uppercase tracking-wider rounded-full <?php echo $isSelected ? 'bg-white/25 text-white' : 'bg-emerald-600 text-white'; ?>">New</span>
                  <?php endif; ?>
                  <div class="flex items-start justify-between gap-3 mb-2">
                    <span class="text-[9px] font-black uppercase px-2 py-1 rounded-full border <?php echo $isSelected ? 'bg-white/25 border-white/20 text-white' : pengumumanPriorityClass($priority); ?>">
                      <?php echo htmlspecialchars($priority); ?>
                    </span>
                    <span class="text-[9px] font-bold <?php echo $isSelected ? 'text-white/75' : 'text-slate-500'; ?>">
                      <?php echo htmlspecialchars(pengumumanFormatDate(pengumumanDateValue($item))); ?>
                    </span>
                  </div>
                  <h3 class="font-black text-sm uppercase tracking-tight leading-tight mb-2 line-clamp-2">
                    <?php echo htmlspecialchars($item['judul'] ?? 'Tanpa Judul'); ?>
                  </h3>
                  <div class="text-[9px] font-bold uppercase flex items-center gap-1 <?php echo $isSelected ? 'text-white/75' : 'text-slate-600'; ?>">
                    <iconify-icon icon="lucide:building-2"></iconify-icon>
                    <?php echo htmlspecialchars($item['sumber'] ?? '-'); ?>
                  </div>
                </button>
              <?php endforeach; ?>
            </div>
          </aside>

          <section class="min-h-[560px]">
            <?php if (empty($pengumuman)): ?>
              <div class="h-full rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-400 flex flex-col items-center justify-center">
                <iconify-icon icon="lucide:file-text" class="text-6xl mb-4 opacity-40"></iconify-icon>
                <p class="text-xs font-black uppercase tracking-[0.3em]">Pengumuman belum tersedia</p>
              </div>
            <?php endif; ?>

            <?php foreach ($pengumuman as $index => $item): ?>
              <?php
                $id = (string) ($item['id'] ?? $index);
                $isSelected = $selected && (string) ($selected['id'] ?? '') === (string) ($item['id'] ?? '');
                $priority = $item['prioritas'] ?? 'Normal';
                $pdf = trim($item['pdf'] ?? '');
              ?>
              <article id="pengumuman-<?php echo htmlspecialchars($id); ?>" data-pengumuman-detail class="<?php echo $isSelected ? '' : 'hidden'; ?> bg-white rounded-xl p-6 md:p-8 shadow-sm border border-slate-200">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 mb-6 pb-6 border-b border-slate-100">
                  <div class="space-y-3 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                      <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-3 py-1 rounded-full uppercase">
                        <?php echo htmlspecialchars($item['no'] ?? '-'); ?>
                      </span>
                      <span class="text-[10px] font-black text-slate-500 bg-slate-100 px-3 py-1 rounded-full uppercase">
                        <?php echo htmlspecialchars(pengumumanFormatDate(pengumumanDateValue($item))); ?>
                      </span>
                      <span class="text-[10px] font-black text-emerald-700 bg-emerald-50 border border-emerald-100 px-3 py-1 rounded-full uppercase">
                        <?php echo htmlspecialchars($item['sumber'] ?? '-'); ?>
                      </span>
                      <span class="text-[10px] font-black uppercase px-3 py-1 rounded-full border <?php echo pengumumanPriorityClass($priority); ?>">
                        <?php echo htmlspecialchars($priority); ?>
                      </span>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-black text-slate-900 uppercase tracking-tight leading-tight">
                      <?php echo htmlspecialchars($item['judul'] ?? 'Tanpa Judul'); ?>
                    </h2>
                  </div>

                  <?php if ($pdf !== ''): ?>
                    <?php 
                    $resolvedPdf = resolvePdfUrl($pdf); 
                    $previewPdfUrl = $resolvedPdf;
                    if ($resolvedPdf !== '' && strpos($resolvedPdf, 'cloudinary.com') !== false) {
                        $previewPdfUrl = 'includes/pdf_proxy.php?url=' . urlencode($resolvedPdf);
                    }
                    ?>
                    <a href="<?php echo htmlspecialchars($previewPdfUrl); ?>" target="_blank" rel="noopener noreferrer" class="w-full md:w-auto bg-gradient-to-r from-slate-800 to-slate-900 text-white px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 shadow-lg hover:shadow-xl transition-all">
                      <iconify-icon icon="lucide:download"></iconify-icon>
                      PDF
                    </a>
                  <?php endif; ?>
                </div>

                <div class="mb-8 bg-gradient-to-br from-slate-50 to-blue-50/50 p-5 rounded-xl border border-slate-100">
                  <p class="text-slate-700 font-semibold leading-relaxed text-sm whitespace-pre-wrap"><?php echo htmlspecialchars($item['konten'] ?? ''); ?></p>
                </div>

                <div class="border-t border-slate-100 pt-6">
                  <h3 class="text-xs font-black uppercase tracking-widest text-slate-600 mb-4 flex items-center gap-2">
                    <iconify-icon icon="lucide:file-text"></iconify-icon>
                    Dokumen PDF
                  </h3>

                  <?php if ($pdf !== ''): ?>
                    <?php 
                    $resolvedPdf = resolvePdfUrl($pdf); 
                    $previewPdfUrl = $resolvedPdf;
                    if ($resolvedPdf !== '' && strpos($resolvedPdf, 'cloudinary.com') !== false) {
                        $previewPdfUrl = 'includes/pdf_proxy.php?url=' . urlencode($resolvedPdf);
                    }
                    ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50">
                       <div style="max-height:72vh; overflow:auto;" class="pdf-viewer-container">
                        <?php if ($resolvedPdf !== '' && (strpos($resolvedPdf, '/') === 0 || filter_var($resolvedPdf, FILTER_VALIDATE_URL))): ?>
                          <object data="<?php echo htmlspecialchars($previewPdfUrl); ?>" type="application/pdf" width="100%" style="min-height:640px; height:100%;">
                            <div class="p-6 text-center">
                              <p class="text-sm mb-3">Browser Anda tidak mendukung tampilan PDF.</p>
                              <a href="<?php echo htmlspecialchars($previewPdfUrl); ?>" target="_blank" rel="noopener noreferrer" class="text-emerald-600 font-semibold">Buka PDF di tab baru</a>
                              <span class="mx-2">•</span>
                              <a href="<?php echo htmlspecialchars($resolvedPdf); ?>" download class="text-emerald-600 font-semibold">Unduh PDF</a>
                            </div>
                          </object>
                        <?php else: ?>
                          <div class="p-6 text-center">
                            <p class="text-sm mb-3">Dokumen tidak dapat ditampilkan langsung. Gunakan tautan di bawah untuk membuka atau mengunduh.</p>
                            <?php if ($resolvedPdf !== ''): ?>
                              <a href="<?php echo htmlspecialchars($previewPdfUrl); ?>" target="_blank" rel="noopener noreferrer" class="text-emerald-600 font-semibold">Buka PDF di tab baru</a>
                              <span class="mx-2">•</span>
                              <a href="<?php echo htmlspecialchars($resolvedPdf); ?>" download class="text-emerald-600 font-semibold">Unduh PDF</a>
                              <?php if (filter_var($resolvedPdf, FILTER_VALIDATE_URL)): ?>
                                <div class="mt-3 text-xs text-slate-500">Atau coba pratinjau via Google Docs:</div>
                                <a href="https://docs.google.com/gview?url=<?php echo urlencode($resolvedPdf); ?>&embedded=true" target="_blank" rel="noopener noreferrer" class="text-emerald-600 font-semibold">Pratinjau Google Docs</a>
                              <?php endif; ?>
                            <?php else: ?>
                              <p class="text-sm text-slate-500">Tautan file tidak valid.</p>
                            <?php endif; ?>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>
                  <?php else: ?>
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                      <iconify-icon icon="lucide:file-x" class="text-4xl text-slate-300 mb-3"></iconify-icon>
                      <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Dokumen PDF tidak tersedia</p>
                    </div>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </section>
        </div>
      </div>
    </section>
  </main>

  <script>
    const cards = Array.from(document.querySelectorAll('[data-pengumuman-card]'));
    const details = Array.from(document.querySelectorAll('[data-pengumuman-detail]'));
    const searchInput = document.getElementById('pengumumanSearch');

    cards.forEach((card) => {
      card.addEventListener('click', () => {
        const target = card.dataset.target;
        cards.forEach((item) => {
          item.classList.remove('bg-gradient-to-br', 'from-emerald-600', 'to-teal-600', 'text-white', 'border-transparent', 'shadow-lg');
          item.classList.add('bg-gradient-to-br', 'from-slate-50', 'to-slate-100', 'text-slate-900', 'border-slate-200');
        });
        card.classList.remove('from-slate-50', 'to-slate-100', 'text-slate-900', 'border-slate-200');
        card.classList.add('from-emerald-600', 'to-teal-600', 'text-white', 'border-transparent', 'shadow-lg');

        details.forEach((detail) => detail.classList.toggle('hidden', detail.id !== target));
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim().toLowerCase();
        cards.forEach((card) => {
          card.classList.toggle('hidden', query !== '' && !card.dataset.search.includes(query));
        });
      });
    }
  </script>

  <?php include '../components/footer.php'; ?>
</body>
</html>
