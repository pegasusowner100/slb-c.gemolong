<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$title = "Anggaran & Belanja — SLB BC KARYA SEJAHTERA " . SITE_NAME;

// --- AMBIL DATA ---
$anggaran_semester = [];
$anggaran = [];
$realisasi = [];
$rencana = [];
if ($supabaseConnected) {
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
    }

    $rencanaResult = supabaseSelect('rencana_anggaran', ['order' => 'created_at.desc']);
    if ($rencanaResult['success']) {
        $rencana = $rencanaResult['data'];
    }
}

$jenis_label = [
    'pendek' => 'Rencana Jangka Pendek',
    'menengah' => 'Rencana Jangka Menengah',
    'panjang' => 'Rencana Jangka Panjang'
];

if (!function_exists('resolveAbsoluteUrl')) {
    function resolveAbsoluteUrl($url) {
        if (empty($url)) {
            return '';
        }

        $url = trim($url);
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        if (strpos($url, '//') === 0) {
            return 'https:' . $url;
        }

        if (strpos($url, '/') === 0) {
            $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
            return ($base === '' ? '' : $base) . $url;
        }

        if (defined('LOCAL_UPLOAD_BASE_URL_PUBLIC') && LOCAL_UPLOAD_BASE_URL_PUBLIC !== '') {
            return rtrim(LOCAL_UPLOAD_BASE_URL_PUBLIC, '/') . '/' . ltrim($url, '/');
        }

        return $url;
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
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label fade-in-up block mb-4">Layanan Pendidikan</span>
            <h1 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1] fade-in-up delay-100">Anggaran & Belanja</h1>
        </div>
    </section>

    <!-- Content -->
    <?php include __DIR__ . '/../components/section-anggaran.php'; ?>

    <script>
      (function() {
        const itemsPerPage = 6;
        const rows = document.querySelectorAll('.realisasi-row');
        const totalItems = rows.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        let currentPage = 1;

        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const currentPageSpan = document.getElementById('currentPage');

        if (!prevBtn || !nextBtn || !currentPageSpan || totalPages <= 1) return;

        function showPage(page) {
          const start = (page - 1) * itemsPerPage;
          const end = start + itemsPerPage;

          rows.forEach((row, index) => {
            row.style.display = index >= start && index < end ? '' : 'none';
          });

          currentPageSpan.textContent = page;
          currentPage = page;
          prevBtn.disabled = page === 1;
          nextBtn.disabled = page === totalPages;
        }

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

        showPage(1);
      })();
    </script>

    </div>
    <?php include '../components/footer.php'; ?>
</body>
</html>