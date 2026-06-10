<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$title = "Anggaran & Belanja — " . SITE_NAME;

// --- AMBIL DATA ---
$anggaran = [];
$realisasi = [];
$rencana = [];
if ($supabaseConnected) {
    $anggaranResult = supabaseSelect('anggaran_bosn', ['order' => 'tahun.desc']);
    if ($anggaranResult['success']) {
        $anggaran = $anggaranResult['data'];
    }
    $realisasiResult = supabaseSelect('realisasi_bulanan', ['order' => 'tahun.desc']);
    if ($realisasiResult['success']) {
      $realisasi = $realisasiResult['data'];

      function getMonthNumber($monthName) {
        $months = [
          'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4, 'Mei' => 5, 'Juni' => 6,
          'Juli' => 7, 'Agustus' => 8, 'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12
        ];
        return $months[$monthName] ?? 0;
      }

      usort($realisasi, function($a, $b) {
        if (($a['tahun'] ?? 0) != ($b['tahun'] ?? 0)) {
          return ($b['tahun'] ?? 0) - ($a['tahun'] ?? 0);
        }
        return getMonthNumber($a['bulan'] ?? '') - getMonthNumber($b['bulan'] ?? '');
      });
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

    </div>
    <?php include '../components/footer.php'; ?>
</body>
</html>