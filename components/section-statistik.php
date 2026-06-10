<?php
// Reusable Statistik Section
$usiaCounts = [];
$pekerjaanCounts = [
    'ASN/TNI/Polri' => 0,
    'Petani/Nelayan' => 0,
    'Buruh' => 0,
    'Wiraswasta' => 0,
    'Lainnya' => 0
];
$countLaki = 0;
$countPerempuan = 0;

foreach ($all_siswa ?? [] as $s) {
    if (($s['jenis_kelamin'] ?? '') === 'Laki-laki') {
        $countLaki++;
    } elseif (($s['jenis_kelamin'] ?? '') === 'Perempuan') {
        $countPerempuan++;
    }

    $usia = intval($s['usia'] ?? 0);
    if ($usia > 0) {
        if (!isset($usiaCounts[$usia])) {
            $usiaCounts[$usia] = 0;
        }
        $usiaCounts[$usia]++;
    }

    $p = $s['pekerjaan_ortu'] ?? 'Lainnya';
    if (isset($pekerjaanCounts[$p])) {
        $pekerjaanCounts[$p]++;
    } else {
        $pekerjaanCounts['Lainnya']++;
    }
}

$countGuru = 0;
$countTendik = 0;
foreach ($guru_list ?? [] as $g) {
    $jabatan = strtolower($g['jabatan'] ?? '');
    if (strpos($jabatan, 'guru') !== false || empty($jabatan)) {
        $countGuru++;
    } else {
        $countTendik++;
    }
}

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
} else {
    $ageLabels = ['15 Tahun', '16 Tahun', '17 Tahun', '18 Tahun', '19 Tahun'];
    $ageData = [31.1, 39.2, 28.3, 1.1, 0.3];
    $ageColorsList = ['#3b82f6', '#ef4444', '#f59e0b', '#22c55e', '#8b5cf6'];
}

$countLaki = $countLaki > 0 ? $countLaki : 224;
$countPerempuan = $countPerempuan > 0 ? $countPerempuan : 264;
$countGuru = $countGuru > 0 ? $countGuru : 34;
$countTendik = $countTendik > 0 ? $countTendik : 6;
$totalPekerjaan = array_sum($pekerjaanCounts);

$ruanganCount = $hero['jumlah_ruangan'] ?? 26;
$bukuPaketCount = $hero['buku_paket'] ?? 500;
$ruanganPercent = min(100, max(0, round($ruanganCount / 40 * 100)));
$bukuPaketPercent = min(100, max(0, round($bukuPaketCount / 600 * 100)));
?>
<section class="py-24">
  <div class="max-w-7xl mx-auto px-6">
    <?php if (basename($_SERVER['PHP_SELF']) !== 'statistik.php'): ?>
      <div class="text-center mb-16">
        <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Statistik</span>
          <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Statistik Sekolah</h2>
        </div>
        <div class="w-20 h-1 bg-brand-accent mx-auto"></div>
      </div>
        <div class="w-20 h-1 bg-brand-accent mx-auto"></div>
      </div>
    <?php endif; ?>
    <div class="glass-section">
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <!-- Statistik Murid -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-lg p-8 border border-blue-200 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
          <div class="text-center">
            <iconify-icon icon="mdi:account-school" class="w-24 h-24 text-blue-600 mx-auto mb-6" style="font-size: 128px;"></iconify-icon>
            <div class="font-serif text-5xl font-bold text-blue-700 mb-3 counter" data-target="<?php echo $hero['siswa_aktif'] ?? 488; ?>">0</div>
            <h3 class="font-bold text-xl text-blue-800 mb-6">Murid</h3>
            <div class="grid grid-cols-2 gap-4 pt-6 border-t border-blue-200">
              <div class="text-center p-3 bg-white rounded-lg">
                <span class="block font-semibold text-sm text-blue-700 mb-1">Laki-laki</span>
                <span class="text-blue-600 text-xl font-bold"><?php echo $countLaki; ?></span>
              </div>
              <div class="text-center p-3 bg-white rounded-lg">
                <span class="block font-semibold text-sm text-pink-700 mb-1">Perempuan</span>
                <span class="text-pink-600 text-xl font-bold"><?php echo $countPerempuan; ?></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistik Guru & Tendik -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-lg p-8 border border-green-200 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
          <div class="text-center">
            <iconify-icon icon="mdi:account-group" class="w-24 h-24 text-green-600 mx-auto mb-6" style="font-size: 128px;"></iconify-icon>
            <div class="font-serif text-5xl font-bold text-green-700 mb-3 counter" data-target="<?php echo $hero['tenaga_pendidik'] ?? 40; ?>">0</div>
            <h3 class="font-bold text-xl text-green-800 mb-6">Guru & Tendik</h3>
            <div class="grid grid-cols-2 gap-4 pt-6 border-t border-green-200">
              <div class="text-center p-3 bg-white rounded-lg">
                <span class="block font-semibold text-sm text-green-700 mb-1">Guru</span>
                <span class="text-green-600 text-xl font-bold"><?php echo $countGuru; ?></span>
              </div>
              <div class="text-center p-3 bg-white rounded-lg">
                <span class="block font-semibold text-sm text-purple-700 mb-1">Tendik</span>
                <span class="text-purple-600 text-xl font-bold"><?php echo $countTendik; ?></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistik Ruangan -->
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-lg p-8 border border-purple-200 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl fade-in-up delay-300">
          <div class="text-center">
            <iconify-icon icon="mdi:door-open" class="w-24 h-24 text-purple-600 mx-auto mb-6" style="font-size: 128px;"></iconify-icon>
            <div class="font-serif text-5xl font-bold text-purple-700 mb-3 counter" data-target="<?php echo $ruanganCount; ?>">0</div>
            <h3 class="font-bold text-xl text-purple-800 mb-6">Ruangan</h3>
            <div class="pt-6 border-t border-purple-200">
              <div class="flex justify-between text-sm mb-3">
                <span class="text-purple-700 font-medium">Pemanfaatan</span>
                <span class="text-purple-600 font-bold">100%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-400 to-purple-600 h-full rounded-full progress-bar" style="width: 100%" data-width="100%"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistik Buku Paket -->
        <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl shadow-lg p-8 border border-amber-200 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl fade-in-up delay-400">
          <div class="text-center">
            <iconify-icon icon="mdi:bookshelf" class="w-24 h-24 text-amber-600 mx-auto mb-6" style="font-size: 128px;"></iconify-icon>
            <div class="font-serif text-5xl font-bold text-amber-700 mb-3">
              <span class="counter" data-target="<?php echo $bukuPaketCount; ?>">0</span>
            </div>
            <h3 class="font-bold text-xl text-amber-800 mb-6">Buku Paket</h3>
            <div class="pt-6 border-t border-amber-200">
              <div class="flex justify-between text-sm mb-3">
                <span class="text-amber-700 font-medium">Pemanfaatan</span>
                <span class="text-amber-600 font-bold">100%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div class="bg-gradient-to-r from-amber-400 to-amber-600 h-full rounded-full progress-bar" style="width: 100%" data-width="100%"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100 fade-in-left delay-200">
          <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
            <h3 class="font-serif text-2xl font-semibold text-gray-800">Data Sosial Ekonomi Orang Tua</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full border-collapse">
              <thead>
                <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                  <th class="border border-gray-200 px-4 py-3 text-center font-semibold text-sm text-gray-700 rounded-tl-lg">Pekerjaan</th>
                  <th class="border border-gray-200 px-4 py-3 text-center font-semibold text-sm text-gray-700 rounded-tr-lg">Jumlah</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $colors = ['bg-blue-50', 'bg-green-50', 'bg-orange-50', 'bg-purple-50', 'bg-yellow-50'];
                  $index = 0;
                  foreach ($pekerjaanCounts as $k => $j) {
                ?>
                <tr class="hover:bg-gray-50 transition-colors <?php echo $colors[$index % 5]; ?>">
                  <td class="border border-gray-200 px-4 py-3 text-center text-sm text-gray-700 font-medium"><?php echo $k; ?></td>
                  <td class="border border-gray-200 px-4 py-3 text-center text-sm text-gray-700 font-semibold"><?php echo $j; ?> Orang</td>
                </tr>
                <?php $index++; } ?>
                <tr class="bg-gradient-to-r from-gray-200 to-gray-300 font-bold">
                  <td class="border border-gray-200 px-4 py-3 text-center text-sm text-gray-800">Total</td>
                  <td class="border border-gray-200 px-4 py-3 text-center text-sm text-gray-800"><?php echo $totalPekerjaan; ?> Orang</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="mt-6 flex items-center justify-center gap-2 text-sm text-gray-600">
            <iconify-icon icon="lucide:calendar" class="w-5 h-5"></iconify-icon>
            <span>Sumber: Dapodik <?php echo strftime('%B %Y'); ?></span>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100 fade-in-right delay-300">
          <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
            <iconify-icon icon="lucide:birthday-cake" class="w-8 h-8 text-pink-700 flex-shrink-0"></iconify-icon>
            <h3 class="font-serif text-2xl font-semibold text-gray-800">Usia Murid</h3>
          </div>
          <div class="flex flex-col md:flex-row items-center justify-center gap-8">
            <div class="relative">
              <canvas id="ageChart" width="300" height="300"></canvas>
            </div>
            <div class="space-y-3" id="ageLegend">
              <?php for ($i = 0; $i < count($ageLabels); $i++): ?>
              <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="w-4 h-4 rounded-full shadow" style="background-color: <?php echo $ageColorsList[$i]; ?>"></div>
                <span class="text-sm font-medium text-gray-700"><?php echo $ageLabels[$i]; ?></span>
                <span class="text-sm font-semibold ml-auto" style="color: <?php echo $ageColorsList[$i]; ?>"><?php echo $ageData[$i]; ?>%</span>
              </div>
              <?php endfor; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
      const target = +counter.getAttribute('data-target');
      const suffix = counter.getAttribute('data-suffix') || '';
      const duration = 2000;
      const step = target / (duration / 16);
      let current = 0;
      const updateCounter = () => {
        current += step;
        if (current < target) {
          counter.textContent = Math.floor(current) + suffix;
          requestAnimationFrame(updateCounter);
        } else {
          counter.textContent = target + suffix;
        }
      };
      const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            updateCounter();
            counterObserver.unobserve(entry.target);
          }
        });
      }, { threshold: 0.5 });
      counterObserver.observe(counter);
    });
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
      const progressObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const width = bar.getAttribute('data-width');
            setTimeout(() => {
              bar.style.width = width;
            }, 300);
            progressObserver.unobserve(entry.target);
          }
        });
      }, { threshold: 0.5 });
      progressObserver.observe(bar);
    });
    const ctx = document.getElementById('ageChart');
    if (ctx) {
      new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: <?php echo json_encode($ageLabels); ?>,
          datasets: [{
            data: <?php echo json_encode($ageData); ?>,
            backgroundColor: <?php echo json_encode($ageColorsList); ?>,
            borderWidth: 0,
            cutout: '70%'
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false }
          }
        }
      });
    }
  });
</script>
