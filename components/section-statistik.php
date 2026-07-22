<?php
// Reusable Statistik Section
$usiaCounts = [];
$pekerjaanCounts = [
    'Buruh' => 0,
    'Karyawan Swasta' => 0,
    'Pedagang Kecil' => 0,
    'Petani' => 0,
    'ASN/TNI/Polri' => 0,
    'Wiraswasta' => 0,
    'Lainnya' => 0
];
$countLaki = 0;
$countPerempuan = 0;

$jenjangStat = [
    'SDLB' => ['laki' => 0, 'perempuan' => 0, 'total' => 0],
    'SMPLB' => ['laki' => 0, 'perempuan' => 0, 'total' => 0],
    'SMALB' => ['laki' => 0, 'perempuan' => 0, 'total' => 0]
];

foreach ($all_siswa ?? [] as $s) {
    if (($s['jenis_kelamin'] ?? '') === 'Laki-laki') {
        $countLaki++;
    } elseif (($s['jenis_kelamin'] ?? '') === 'Perempuan') {
        $countPerempuan++;
    }

    $j = strtoupper(trim($s['jenjang'] ?? ''));
    if (isset($jenjangStat[$j])) {
        $jk = $s['jenis_kelamin'] ?? '';
        if ($jk === 'Laki-laki') {
            $jenjangStat[$j]['laki']++;
        } elseif ($jk === 'Perempuan') {
            $jenjangStat[$j]['perempuan']++;
        }
        $jenjangStat[$j]['total']++;
    }

    $usia = intval($s['usia'] ?? 0);
    if ($usia > 0) {
        if (!isset($usiaCounts[$usia])) {
            $usiaCounts[$usia] = 0;
        }
        $usiaCounts[$usia]++;
    }

    $p = $s['pekerjaan_ortu'] ?? 'Lainnya';
    if ($p == 'Petani/Nelayan') {
        $p = 'Petani';
    }
    if (isset($pekerjaanCounts[$p])) {
        $pekerjaanCounts[$p]++;
    } else {
        $pekerjaanCounts['Lainnya']++;
    }
}

// Fallback to static numbers if no database data
$totalDbJenjang = $jenjangStat['SDLB']['total'] + $jenjangStat['SMPLB']['total'] + $jenjangStat['SMALB']['total'];
if ($totalDbJenjang === 0) {
    $jenjangStat = [
        'SDLB' => ['laki' => 21, 'perempuan' => 7, 'total' => 28],
        'SMPLB' => ['laki' => 7, 'perempuan' => 7, 'total' => 14],
        'SMALB' => ['laki' => 11, 'perempuan' => 8, 'total' => 19]
    ];
}

$totalJenjangLaki = $jenjangStat['SDLB']['laki'] + $jenjangStat['SMPLB']['laki'] + $jenjangStat['SMALB']['laki'];
$totalJenjangPerempuan = $jenjangStat['SDLB']['perempuan'] + $jenjangStat['SMPLB']['perempuan'] + $jenjangStat['SMALB']['perempuan'];
$totalJenjangTotal = $jenjangStat['SDLB']['total'] + $jenjangStat['SMPLB']['total'] + $jenjangStat['SMALB']['total'];

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

$countLaki = $countLaki > 0 ? $countLaki : 0;
$countPerempuan = $countPerempuan > 0 ? $countPerempuan : 0;
$countGuru = $countGuru > 0 ? $countGuru : 0;
$countTendik = $countTendik > 0 ? $countTendik : 0;
$totalPekerjaan = array_sum($pekerjaanCounts);

$ruanganCount = !empty($hero['jumlah_ruangan']) ? intval($hero['jumlah_ruangan']) : 26;
$bukuPaketCount = !empty($hero['buku_paket']) ? intval($hero['buku_paket']) : 500;
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
    <?php endif; ?>
    <div class="glass-section">
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <!-- Statistik Murid -->
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl shadow-lg p-8 border border-orange-200 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
          <div class="text-center">
            <iconify-icon icon="mdi:account-school" class="w-24 h-24 text-orange-600 mx-auto mb-6" style="font-size: 128px;"></iconify-icon>
            <div class="font-serif text-5xl font-bold text-orange-700 mb-3 counter" style="color: #D4AF37 !important;" data-target="<?php echo $hero['siswa_aktif'] ?? 488; ?>">0</div>
            <h3 class="font-bold text-xl text-orange-800 mb-6">Murid</h3>
            <div class="grid grid-cols-2 gap-4 pt-6 border-t border-orange-200">
              <div class="text-center p-3 bg-white rounded-lg">
                <span class="block font-semibold text-sm text-orange-700 mb-1">Laki-laki</span>
                <span class="text-orange-600 text-xl font-bold"><?php echo $countLaki; ?></span>
              </div>
              <div class="text-center p-3 bg-white rounded-lg">
                <span class="block font-semibold text-sm text-pink-700 mb-1">Perempuan</span>
                <span class="text-pink-600 text-xl font-bold"><?php echo $countPerempuan; ?></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistik Guru & Tendik -->
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl shadow-lg p-8 border border-orange-200 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
          <div class="text-center">
            <iconify-icon icon="mdi:account-group" class="w-24 h-24 text-orange-600 mx-auto mb-6" style="font-size: 128px;"></iconify-icon>
            <div class="font-serif text-5xl font-bold text-orange-700 mb-3 counter" style="color: #D4AF37 !important;" data-target="<?php echo $hero['tenaga_pendidik'] ?? 40; ?>">0</div>
            <h3 class="font-bold text-xl text-orange-800 mb-6">Guru & Tendik</h3>
            <div class="grid grid-cols-2 gap-4 pt-6 border-t border-orange-200">
              <div class="text-center p-3 bg-white rounded-lg">
                <span class="block font-semibold text-sm text-orange-700 mb-1">Guru</span>
                <span class="text-orange-600 text-xl font-bold"><?php echo $countGuru; ?></span>
              </div>
              <div class="text-center p-3 bg-white rounded-lg">
                <span class="block font-semibold text-sm text-purple-700 mb-1">Tendik</span>
                <span class="text-purple-600 text-xl font-bold"><?php echo $countTendik; ?></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistik Ruangan -->
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl shadow-lg p-8 border border-orange-200 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl fade-in-up delay-300">
          <div class="text-center">
            <iconify-icon icon="mdi:door-open" class="w-24 h-24 text-orange-600 mx-auto mb-6" style="font-size: 128px;"></iconify-icon>
            <div class="font-serif text-5xl font-bold text-orange-700 mb-3 counter" style="color: #D4AF37 !important;" data-target="<?php echo $ruanganCount; ?>">0</div>
            <h3 class="font-bold text-xl text-orange-800 mb-6">Ruangan</h3>
            <div class="pt-6 border-t border-orange-200">
              <div class="flex justify-between text-sm mb-3">
                <span class="text-orange-700 font-medium">Pemanfaatan</span>
                <span class="text-orange-600 font-bold">100%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-400 to-orange-600 h-full rounded-full progress-bar" style="width: 100%" data-width="100%"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistik Buku Paket -->
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl shadow-lg p-8 border border-orange-200 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl fade-in-up delay-400">
          <div class="text-center">
            <iconify-icon icon="mdi:bookshelf" class="w-24 h-24 text-orange-600 mx-auto mb-6" style="font-size: 128px;"></iconify-icon>
            <div class="font-serif text-5xl font-bold text-orange-700 mb-3">
              <span class="counter" style="color: #D4AF37 !important;" data-target="<?php echo $bukuPaketCount; ?>">0</span>
            </div>
            <h3 class="font-bold text-xl text-orange-800 mb-6">Buku Paket</h3>
            <div class="pt-6 border-t border-orange-200">
              <div class="flex justify-between text-sm mb-3">
                <span class="text-orange-700 font-medium">Pemanfaatan</span>
                <span class="text-orange-600 font-bold">100%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-400 to-orange-600 h-full rounded-full progress-bar" style="width: 100%" data-width="100%"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-orange-200 fade-in-left delay-200 flex flex-col justify-between">
          <div>
            <div class="flex items-center gap-4 mb-6 pb-4 border-b border-orange-100">
              <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center text-orange-600">
                <iconify-icon icon="lucide:wallet" class="w-6 h-6"></iconify-icon>
              </div>
              <h3 class="font-serif text-2xl font-semibold text-gray-800">Data Ekonomi Keluarga Peserta Didik</h3>
            </div>
            <div class="overflow-x-auto rounded-xl border border-orange-200 shadow-sm">
              <table class="w-full border-collapse text-sm">
                <thead>
                  <tr class="bg-gradient-to-r from-orange-600 to-orange-700 text-white font-bold uppercase tracking-wider text-xs">
                    <th class="px-5 py-3.5 text-left border-r border-white/20 text-white" style="color: #ffffff !important;">Pekerjaan Orang Tua</th>
                    <th class="px-5 py-3.5 text-center text-white" style="color: #ffffff !important;">Jumlah</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-orange-100">
                  <?php
                    $index = 0;
                    foreach ($pekerjaanCounts as $k => $j) {
                  ?>
                  <tr class="hover:bg-orange-50/50 transition-colors duration-150">
                    <td class="px-5 py-3.5 text-gray-700 font-medium border-r border-orange-100"><?php echo $k; ?></td>
                    <td class="px-5 py-3.5 text-center text-gray-900 font-bold"><?php echo $j; ?> <span class="text-xs font-normal text-gray-500">Orang</span></td>
                  </tr>
                  <?php $index++; } ?>
                  <tr class="bg-orange-50 font-bold text-gray-800 border-t-2 border-orange-200">
                    <td class="px-5 py-4 text-left uppercase text-xs tracking-wider border-r border-orange-100">Total Keseluruhan</td>
                    <td class="px-5 py-4 text-center text-base text-orange-600"><?php echo $totalPekerjaan; ?> <span class="text-xs font-medium text-gray-500">Orang</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8 border border-orange-200 fade-in-right delay-300 flex flex-col justify-between">
          <div>
            <div class="flex items-center gap-4 mb-6 pb-4 border-b border-orange-100">
              <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center text-orange-600">
                <iconify-icon icon="lucide:school" class="w-6 h-6"></iconify-icon>
              </div>
              <h3 class="font-serif text-2xl font-semibold text-gray-800">Data Murid</h3>
            </div>
            <div class="overflow-x-auto rounded-xl border border-orange-200 shadow-sm">
              <table class="w-full border-collapse text-center text-sm font-medium">
                <thead>
                  <tr class="bg-gradient-to-r from-orange-600 to-orange-700 text-white font-bold uppercase tracking-wider text-xs">
                    <th rowspan="2" class="border-r border-b border-white/20 px-3 py-3.5 align-middle text-white" style="color: #ffffff !important;">NO</th>
                    <th rowspan="2" class="border-r border-b border-white/20 px-4 py-3.5 align-middle text-white" style="color: #ffffff !important;">JENJANG/KELAS</th>
                    <th colspan="3" class="border-b border-white/20 px-4 py-2 text-white" style="color: #ffffff !important;">JUMLAH</th>
                  </tr>
                  <tr class="bg-orange-700 text-white font-bold uppercase tracking-wider text-xs">
                    <th class="border-r border-white/20 px-3 py-2.5 text-white" style="color: #ffffff !important;">LAKI-LAKI</th>
                    <th class="border-r border-white/20 px-3 py-2.5 text-white" style="color: #ffffff !important;">PEREMPUAN</th>
                    <th class="px-3 py-2.5 text-white" style="color: #ffffff !important;">JUMLAH</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-orange-100">
                  <tr class="hover:bg-orange-50/50 transition-colors duration-150">
                    <td class="border-r border-orange-100 px-3 py-3.5 text-gray-600">1.</td>
                    <td class="border-r border-orange-100 px-4 py-3.5 font-bold text-gray-800">SDLB</td>
                    <td class="border-r border-orange-100 px-3 py-3.5 text-gray-600"><?php echo $jenjangStat['SDLB']['laki']; ?></td>
                    <td class="border-r border-orange-100 px-3 py-3.5 text-gray-600"><?php echo $jenjangStat['SDLB']['perempuan']; ?></td>
                    <td class="px-3 py-3.5 font-bold text-orange-600"><?php echo $jenjangStat['SDLB']['total']; ?></td>
                  </tr>
                  <tr class="hover:bg-orange-50/50 transition-colors duration-150">
                    <td class="border-r border-orange-100 px-3 py-3.5 text-gray-600">2.</td>
                    <td class="border-r border-orange-100 px-4 py-3.5 font-bold text-gray-800">SMPLB</td>
                    <td class="border-r border-orange-100 px-3 py-3.5 text-gray-600"><?php echo $jenjangStat['SMPLB']['laki']; ?></td>
                    <td class="border-r border-orange-100 px-3 py-3.5 text-gray-600"><?php echo $jenjangStat['SMPLB']['perempuan']; ?></td>
                    <td class="px-3 py-3.5 font-bold text-orange-600"><?php echo $jenjangStat['SMPLB']['total']; ?></td>
                  </tr>
                  <tr class="hover:bg-orange-50/50 transition-colors duration-150">
                    <td class="border-r border-orange-100 px-3 py-3.5 text-gray-600">3.</td>
                    <td class="border-r border-orange-100 px-4 py-3.5 font-bold text-gray-800">SMALB</td>
                    <td class="border-r border-orange-100 px-3 py-3.5 text-gray-600"><?php echo $jenjangStat['SMALB']['laki']; ?></td>
                    <td class="border-r border-orange-100 px-3 py-3.5 text-gray-600"><?php echo $jenjangStat['SMALB']['perempuan']; ?></td>
                    <td class="px-3 py-3.5 font-bold text-orange-600"><?php echo $jenjangStat['SMALB']['total']; ?></td>
                  </tr>
                  <tr class="bg-orange-50 font-bold text-gray-800 border-t-2 border-orange-200">
                    <td colspan="2" class="border-r border-orange-100 px-4 py-4 uppercase text-xs tracking-wider text-center">JUMLAH</td>
                    <td class="border-r border-orange-100 px-3 py-4 text-orange-600"><?php echo $totalJenjangLaki; ?></td>
                    <td class="border-r border-orange-100 px-3 py-4 text-orange-600"><?php echo $totalJenjangPerempuan; ?></td>
                    <td class="px-3 py-4 text-base text-orange-600"><?php echo $totalJenjangTotal; ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="mt-6 flex items-center justify-center gap-2 text-xs font-semibold text-orange-800 bg-orange-50 py-2.5 px-4 rounded-lg border border-orange-200 w-fit mx-auto">
            <iconify-icon icon="lucide:calendar" class="w-4 h-4 text-orange-600"></iconify-icon>
            <span>Sumber: Dapodik July 2026</span>
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
  });
</script>
