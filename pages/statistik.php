<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$title = "Statistik — " . SITE_NAME;

// Default data
$hero = [
    'tahun_berdiri' => 1990,
    'siswa_aktif' => 0,
    'alumni' => 5000,
    'tenaga_pendidik' => 0,
    'total_prestasi' => 0,
    'jumlah_ruangan' => 26,
    'buku_paket' => 500,
    'motto' => 'Mandiri berkarakter berdikari'
];

// Initialize variables
$all_siswa = [];
$guru_list = [];

if ($supabaseConnected) {
    // Get hero
    $heroResult = supabaseSelect('hero', ['id' => 'eq.1', 'limit' => 1]);
    if ($heroResult['success'] && !empty($heroResult['data'])) {
        $hero = array_merge($hero, $heroResult['data'][0]);
    }
    
    // Get active students count and data
    $siswaResult = supabaseSelect('siswa', ['status' => 'eq.Aktif', 'order' => 'no_induk.asc']);
    if ($siswaResult['success']) {
        $all_siswa = $siswaResult['data'];
        $hero['siswa_aktif'] = count($all_siswa);
    }
    
    // Get guru count and data
    $guruResult = supabaseSelect('guru', []);
    if ($guruResult['success']) {
        $guru_list = $guruResult['data'];
        $hero['tenaga_pendidik'] = count($guru_list);
    }
    
    // Get prestasi count
    $prestasiResult = supabaseSelect('prestasi', []);
    if ($prestasiResult['success']) {
        $hero['total_prestasi'] = count($prestasiResult['data']);
    }
}

// Calculate additional data
$countLaki = 0;
$countPerempuan = 0;
$usiaCounts = [];
$pekerjaanCounts = [
    'ASN/TNI/Polri' => 0,
    'Petani/Nelayan' => 0,
    'Buruh' => 0,
    'Wiraswasta' => 0,
    'Lainnya' => 0
];

foreach ($all_siswa as $s) {
    // Jenis kelamin
    if ($s['jenis_kelamin'] == 'Laki-laki') {
        $countLaki++;
    } elseif ($s['jenis_kelamin'] == 'Perempuan') {
        $countPerempuan++;
    }

    // Usia
    $usia = intval($s['usia'] ?? 0);
    if ($usia > 0) {
        if (!isset($usiaCounts[$usia])) {
            $usiaCounts[$usia] = 0;
        }
        $usiaCounts[$usia]++;
    }

    // Pekerjaan orang tua
    $p = $s['pekerjaan_ortu'] ?? 'Lainnya';
    if (isset($pekerjaanCounts[$p])) {
        $pekerjaanCounts[$p]++;
    } else {
        $pekerjaanCounts['Lainnya']++;
    }
}

// Calculate guru and tendik
$countGuru = 0;
$countTendik = 0;
foreach ($guru_list as $g) {
    $jabatan = strtolower($g['jabatan'] ?? '');
    if (strpos($jabatan, 'guru') !== false || empty($jabatan)) {
        $countGuru++;
    } else {
        $countTendik++;
    }
}

// Calculate age percentages per individual year
$totalSiswaForAge = array_sum($usiaCounts);
$yearColors = [
    7 => '#3b82f6',
    8 => '#0ea5e9',
    9 => '#22c55e',
    10 => '#f97316',
    11 => '#eab308',
    12 => '#f59e0b',
    13 => '#ef4444',
    14 => '#d946ef',
    15 => '#8b5cf6',
    16 => '#10b981',
    17 => '#38bdf8',
    18 => '#f43f5e',
    19 => '#a855f7',
    20 => '#14b8a6'
];
$ageLabels = [];
$ageData = [];
$ageColorsList = [];

if ($totalSiswaForAge > 0) {
    ksort($usiaCounts);
    foreach ($usiaCounts as $usia => $count) {
        $ageLabels[] = $usia . ' Tahun';
        $ageData[] = round(($count / $totalSiswaForAge) * 100, 1);
        $ageColorsList[] = $yearColors[$usia] ?? $yearColors[7];
    }
}

// Check if we have data for each section
$hasSiswaData = $hero['siswa_aktif'] > 0;
$hasGuruData = $hero['tenaga_pendidik'] > 0;
$hasPekerjaanData = array_sum($pekerjaanCounts) > 0;
$hasAgeData = !empty($ageData);

include '../components/head.php';
?>
<body class="text-brand-dark font-sans glass-body">
  <?php include '../components/navbar.php'; ?>

  <!-- Header -->
  <section class="page-hero bg-brand-dark">
    <div class="max-w-7xl mx-auto px-6">
      <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label fade-in-up">Data Kami</span>
      <h1 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1] fade-in-up delay-100">Statistik <em>Sekolah</em></h1>
    </div>
  </section>

  <?php include __DIR__ . '/../components/section-statistik.php'; ?>

  <?php include '../components/footer.php'; ?>
</body>
</html>
