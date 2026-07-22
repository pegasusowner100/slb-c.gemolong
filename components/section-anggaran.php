<?php $tahun_ini = date('Y'); ?>
<section class="py-20 bg-white">
  <style>
    /* Ensure buttons with blue background show white text/icons */
    .bg-blue-600, .bg-blue-600 * {
      color: #ffffff !important;
    }
    .bg-blue-600 svg, .bg-blue-600 i, .bg-blue-600 iconify-icon {
      color: #ffffff !important;
      fill: currentColor !important;
    }
  </style>
    <div class="max-w-7xl mx-auto px-6">
      <div class="glass-section">
        <div class="text-center mb-16">
          <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Keuangan</span>
            <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Anggaran & Belanja</h2>
          </div>
          <div class="w-20 h-1 bg-brand-accent mx-auto"></div>
        </div>
      </div>

      <!-- RENCANA JANGKA PANJANG/PENDEK/MENENGAH -->
      <div class="mb-20">
        <h3 class="text-2xl font-bold text-gray-800 mb-8 text-center">Rencana Anggaran</h3>
        <div class="grid md:grid-cols-3 gap-8">
          <?php 
            $jenis_rencana = ['pendek', 'menengah', 'panjang'];
            $jenis_label = [
                'pendek' => 'Rencana Jangka Pendek',
                'menengah' => 'Rencana Jangka Menengah',
                'panjang' => 'Rencana Jangka Panjang'
            ];
            $jenis_warna_header = [
                'pendek' => 'bg-green-600',
                'menengah' => 'bg-yellow-600',
                'panjang' => 'bg-red-600'
            ];
            
            foreach ($jenis_rencana as $jenis):
              $data_rencana = array_filter($rencana, function($r) use ($jenis) {
                  return $r['jenis_rencana'] == $jenis;
              });
              $data_rencana = array_shift($data_rencana);
          ?>
          <div class="bg-white p-8 rounded-2xl shadow-lg border border-brand-border/30 overflow-hidden">
            <div class="<?php echo $jenis_warna_header[$jenis]; ?> -mx-8 -mt-8 px-8 py-4 mb-6">
              <h4 class="font-serif text-2xl font-semibold text-center text-white"><?php echo $jenis_label[$jenis]; ?></h4>
            </div>
            <?php if ($data_rencana): ?>
              <h5 class="font-semibold text-xl text-center mb-3 text-brand-dark"><?php echo htmlspecialchars($data_rencana['judul']); ?></h5>
              <?php if ($data_rencana['deskripsi']): ?>
                <div class="text-brand-muted text-sm mb-6 text-justify">
                  <?php 
                  $descText = htmlspecialchars($data_rencana['deskripsi']);
                  $lines = explode("\n", $descText);
                  foreach ($lines as $index => $line) {
                      $trimmedLine = trim($line);
                      if (!empty($trimmedLine)) {
                          if (preg_match('/^\d/', $trimmedLine)) {
                              echo '<div class="mb-2">' . $line . '</div>';
                          } else {
                              echo '<div class="mb-2" style="padding-left: 1.5rem;">' . $line . '</div>';
                          }
                      }
                  }
                  ?>
                </div>
              <?php endif; ?>
              <div class="flex justify-center">
                <?php if ($data_rencana['file_pdf']): ?>
                  <a href="<?php echo htmlspecialchars(function_exists('resolveAbsoluteUrl') ? resolveAbsoluteUrl($data_rencana['file_pdf']) : $data_rencana['file_pdf']); ?>" target="_blank" class="inline-flex items-center gap-2 px-8 py-3 border-2 border-brand-dark text-brand-dark font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-brand-dark hover:text-white transition-colors">
                    <iconify-icon icon="lucide:file-text"></iconify-icon> Lihat Rencana
                  </a>
                <?php endif; ?>
              </div>
            <?php else: ?>
              <div class="text-center py-8 text-gray-500">
                <p>Belum ada rencana.</p>
              </div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="glass-section">

        <div class="mb-16">
          <h3 class="text-2xl font-bold text-gray-800 mb-8 text-center">Total Anggaran Dana</h3>
          <div class="grid md:grid-cols-3 gap-8">
          <?php 
            $tampil_anggaran = array_slice($anggaran, 0, 3);
            if (empty($tampil_anggaran)):
          ?>
          <div class="col-span-full text-center py-12 bg-gray-50 rounded-xl">
            <p class="text-gray-500">Belum ada data anggaran.</p>
          </div>
          <?php else:
            foreach ($tampil_anggaran as $a): 
              $persen = $a['total_anggaran'] > 0 ? round(($a['realisasi'] / $a['total_anggaran']) * 100, 1) : 0;
          ?>
          <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-xl p-8 border border-blue-200 shadow-lg">
            <div class="text-center">
              <div class="text-5xl font-bold text-blue-700 mb-2">Tahun <?php echo $a['tahun']; ?></div>
              <div class="text-lg text-gray-700 mb-6">
                Rp <span class="font-bold"><?php echo number_format($a['total_anggaran'], 0, ',', '.'); ?></span>
              </div>
              
              <div class="mb-6">
                <div class="flex justify-between text-sm mb-2">
                  <span class="font-semibold text-gray-700">Realisasi</span>
                  <span class="font-bold <?php echo $persen >= 80 ? 'text-green-600' : ($persen >=50 ? 'text-yellow-600' : 'text-red-600'); ?>">
                    <?php echo number_format($a['realisasi'], 0, ',', '.'); ?> (<?php echo $persen; ?>%)
                  </span>
                </div>
                <div class="w-full bg-gray-300 rounded-full h-4 overflow-hidden">
                  <div class="h-full rounded-full transition-all duration-1500 <?php echo $persen >=80 ? 'bg-green-500' : ($persen >=50 ? 'bg-yellow-500' : 'bg-red-500'); ?>" style="width: <?php echo $persen; ?>%"></div>
                </div>
              </div>

              <!-- No PDF link for yearly totals -->
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- PRESENTASE BELANJA PER SEMESTER -->
      <div class="mb-20">
        <h3 class="text-2xl font-bold text-gray-800 mb-8 text-center">Presentase Belanja Per Semester</h3>
        <div class="max-w-4xl mx-auto">
          <div class="bg-white rounded-xl p-8 border border-gray-200 shadow-lg">
            <?php
              // Hitung per semester
              $semester_1 = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];
              $semester_2 = ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
              
              $total_sem1_anggaran = 0;
              $total_sem1_realisasi = 0;
              $total_sem2_anggaran = 0;
              $total_sem2_realisasi = 0;
              
              foreach ($realisasi as $r) {
                  if ($r['tahun'] == $tahun_ini) {
                      if (in_array($r['bulan'], $semester_1)) {
                          $total_sem1_anggaran += $r['anggaran'];
                          $total_sem1_realisasi += $r['realisasi'];
                      } else {
                          $total_sem2_anggaran += $r['anggaran'];
                          $total_sem2_realisasi += $r['realisasi'];
                      }
                  }
              }
              
              $persen_sem1 = $total_sem1_anggaran > 0 ? round(($total_sem1_realisasi / $total_sem1_anggaran) * 100, 1) : 0;
              $persen_sem2 = $total_sem2_anggaran > 0 ? round(($total_sem2_realisasi / $total_sem2_anggaran) * 100, 1) : 0;
            ?>
            <div class="grid md:grid-cols-2 gap-8">
              <!-- Semester 1 -->
              <div class="text-center">
                <h4 class="text-xl font-bold text-gray-800 mb-4">Semester 1 (Jan-Jun)</h4>
                <div class="mb-4">
                  <div class="text-3xl font-bold text-emerald-700 mb-2"><?php echo $persen_sem1; ?>%</div>
                  <div class="text-sm text-gray-600">Rp <?php echo number_format($total_sem1_realisasi, 0, ',', '.'); ?> dari Rp <?php echo number_format($total_sem1_anggaran, 0, ',', '.'); ?></div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-6 overflow-hidden">
                  <div class="h-full rounded-full transition-all duration-1500 bg-emerald-500" style="width: <?php echo $persen_sem1; ?>%"></div>
                </div>
              </div>
              
              <!-- Semester 2 -->
              <div class="text-center">
                <h4 class="text-xl font-bold text-gray-800 mb-4">Semester 2 (Jul-Des)</h4>
                <div class="mb-4">
                  <div class="text-3xl font-bold text-emerald-700 mb-2"><?php echo $persen_sem2; ?>%</div>
                  <div class="text-sm text-gray-600">Rp <?php echo number_format($total_sem2_realisasi, 0, ',', '.'); ?> dari Rp <?php echo number_format($total_sem2_anggaran, 0, ',', '.'); ?></div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-6 overflow-hidden">
                  <div class="h-full rounded-full transition-all duration-1500 bg-emerald-500" style="width: <?php echo $persen_sem2; ?>%"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- GRAFIK PER SEMESTER -->
      <div class="mb-20">
        <h3 class="text-2xl font-bold text-gray-800 mb-8 text-center">Grafik Anggaran Per Semester</h3>
        <div class="bg-gradient-to-br from-slate-50 to-slate-100 border border-slate-200 rounded-2xl p-6 md:p-8 shadow-xl">
          <!-- Dropdown Filter for Semester Chart -->
          <div class="flex flex-col sm:flex-row gap-4 items-center justify-between mb-8 pb-6 border-b border-slate-200">
            <span class="text-sm font-semibold text-slate-500">Pilih Tahun untuk Grafik Semester:</span>
            <div class="flex gap-3">
              <select id="semesterChartTahun" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-800 text-sm font-semibold focus:outline-none focus:ring-4 focus:ring-purple-100 transition-all">
                <?php 
                  $years = array_unique(array_column($anggaran_semester, 'tahun'));
                  sort($years);
                  $years = array_reverse($years);
                  if (empty($years)) {
                      $years = [$tahun_ini];
                  }
                  foreach ($years as $y):
                ?>
                  <option value="<?php echo $y; ?>" <?php echo $y == $tahun_ini ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Canvas Chart for Semester -->
          <div class="relative w-full h-80 bg-white p-4 rounded-xl border border-slate-100 shadow-inner mb-8">
            <canvas id="semesterChart"></canvas>
          </div>
        </div>
      </div>

      <!-- GRAFIK & INDIKATOR ANGGARAN & REALISASI BULANAN -->
      <div class="mb-20">
        <h3 class="text-2xl font-bold text-gray-800 mb-8 text-center">Grafik Perbandingan Anggaran & Realisasi Bulanan</h3>
        <div class="bg-gradient-to-br from-slate-50 to-slate-100 border border-slate-200 rounded-2xl p-6 md:p-8 shadow-xl">
          <!-- Dropdown Filter -->
          <div class="flex flex-col sm:flex-row gap-4 items-center justify-between mb-8 pb-6 border-b border-slate-200">
            <span class="text-sm font-semibold text-slate-500">Pilih Periode Laporan Bulanan:</span>
            <div class="flex gap-3">
              <select id="chartTahun" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-800 text-sm font-semibold focus:outline-none focus:ring-4 focus:ring-blue-100 transition-all">
                <?php 
                  $years = array_unique(array_column($anggaran_semester, 'tahun'));
                  sort($years);
                  $years = array_reverse($years);
                  if (empty($years)) {
                      $years = [$tahun_ini];
                  }
                  foreach ($years as $y):
                ?>
                  <option value="<?php echo $y; ?>" <?php echo $y == $tahun_ini ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endforeach; ?>
              </select>
              <select id="chartSemester" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-800 text-sm font-semibold focus:outline-none focus:ring-4 focus:ring-blue-100 transition-all">
                <option value="1" selected>Semester 1 (Jan - Jun)</option>
                <option value="2">Semester 2 (Jul - Des)</option>
              </select>
            </div>
          </div>

          <!-- Indikator Utama -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Anggaran -->
            <div class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
              <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-2xl">
                <iconify-icon icon="lucide:wallet"></iconify-icon>
              </div>
              <div>
                <span class="text-xs font-semibold text-slate-400 block uppercase tracking-wider">Total Anggaran</span>
                <strong id="indTotalAnggaran" class="text-lg font-bold text-slate-800">Rp 0</strong>
              </div>
            </div>
            <!-- Dilaksanakan (Realisasi) -->
            <div class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
              <div class="w-12 h-12 rounded-lg bg-green-50 text-green-600 flex items-center justify-center text-2xl">
                <iconify-icon icon="lucide:check-circle"></iconify-icon>
              </div>
              <div class="flex-1">
                <span class="text-xs font-semibold text-slate-400 block uppercase tracking-wider">Dilaksanakan</span>
                <div class="flex items-center gap-2">
                  <strong id="indDilaksanakan" class="text-lg font-bold text-slate-800">Rp 0</strong>
                  <span id="indPersentase" class="text-xs font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700">0%</span>
                </div>
              </div>
            </div>
            <!-- Sisa Anggaran -->
            <div class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
              <div class="w-12 h-12 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-2xl">
                <iconify-icon icon="lucide:percent"></iconify-icon>
              </div>
              <div>
                <span class="text-xs font-semibold text-slate-400 block uppercase tracking-wider">Sisa Anggaran</span>
                <strong id="indSisaAnggaran" class="text-lg font-bold text-slate-800">Rp 0</strong>
              </div>
            </div>
          </div>

          <!-- Canvas Chart -->
          <div class="relative w-full h-80 bg-white p-4 rounded-xl border border-slate-100 shadow-inner">
            <canvas id="anggaranChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Load Chart.js -->
      <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
      <script>
        document.addEventListener('DOMContentLoaded', () => {
          const rawSemData = <?php echo json_encode($anggaran_semester); ?>;
          const rawRealData = <?php echo json_encode($realisasi); ?>;
          
          // Monthly Chart
          const ctxMonthly = document.getElementById('anggaranChart').getContext('2d');
          let currentMonthlyChart = null;

          // Semester Chart
          const ctxSemester = document.getElementById('semesterChart').getContext('2d');
          let currentSemesterChart = null;

          const sem1Months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];
          const sem2Months = ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

          function renderMonthlyChart() {
            const tahun = parseInt(document.getElementById('chartTahun').value);
            const semester = parseInt(document.getElementById('chartSemester').value);
            
            // Temukan record data semester
            const record = rawSemData.find(item => parseInt(item.tahun) === tahun && parseInt(item.semester) === semester);
            const paguTotalSemester = record ? parseFloat(record.total_anggaran || 0) : 0;
            
            const labels = (semester === 1) ? sem1Months : sem2Months;
            const dataAnggaran = [];
            const dataRealisasi = [];

            labels.forEach(m => {
              const rRecord = rawRealData.find(r => parseInt(r.tahun) === tahun && r.bulan === m);
              dataAnggaran.push(rRecord ? parseFloat(rRecord.anggaran || 0) : 0);
              dataRealisasi.push(rRecord ? parseFloat(rRecord.realisasi || 0) : 0);
            });

            // Hitung total semester
            const totalAng = dataAnggaran.reduce((a, b) => a + b, 0);
            const totalReal = dataRealisasi.reduce((a, b) => a + b, 0);
            const sisa = paguTotalSemester - totalReal;
            const persen = paguTotalSemester > 0 ? ((totalReal / paguTotalSemester) * 100).toFixed(1) : '0.0';

            // Update Indikator UI
            document.getElementById('indTotalAnggaran').textContent = 'Rp ' + paguTotalSemester.toLocaleString('id-ID');
            document.getElementById('indDilaksanakan').textContent = 'Rp ' + totalReal.toLocaleString('id-ID');
            document.getElementById('indSisaAnggaran').textContent = 'Rp ' + (sisa < 0 ? 0 : sisa).toLocaleString('id-ID');
            
            const pctBadge = document.getElementById('indPersentase');
            pctBadge.textContent = persen + '%';
            pctBadge.className = `text-xs font-bold px-2 py-0.5 rounded-full ${paguTotalSemester > 0 && parseFloat(persen) >= 80 ? 'bg-green-100 text-green-700' : (parseFloat(persen) >= 50 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700')}`;

            if (currentMonthlyChart) {
              currentMonthlyChart.destroy();
            }

            currentMonthlyChart = new Chart(ctxMonthly, {
              type: 'bar',
              data: {
                labels: labels,
                datasets: [
                  {
                    label: 'Anggaran (Target)',
                    data: dataAnggaran,
                    backgroundColor: 'rgba(59, 130, 246, 0.85)', // Tailwind Blue 500
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    borderRadius: 6
                  },
                  {
                    label: 'Realisasi (Dilaksanakan)',
                    data: dataRealisasi,
                    backgroundColor: 'rgba(16, 185, 129, 0.85)', // Tailwind Emerald 500 (Green)
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 1,
                    borderRadius: 6
                  }
                ]
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: {
                    position: 'top',
                    labels: {
                      font: {
                        family: "'Outfit', sans-serif",
                        weight: '600'
                      },
                      color: '#475569'
                    }
                  },
                  tooltip: {
                    padding: 12,
                    bodyFont: {
                      family: "'Outfit', sans-serif"
                    },
                    callbacks: {
                      label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                          label += ': ';
                        }
                        if (context.parsed.y !== null) {
                          label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                        }
                        return label;
                      }
                    }
                  }
                },
                scales: {
                  y: {
                    beginAtZero: true,
                    grid: {
                      color: '#f1f5f9'
                    },
                    ticks: {
                      font: {
                        family: "'Outfit', sans-serif"
                      },
                      color: '#64748b',
                      callback: function(value) {
                        if (value >= 1e6) {
                          return 'Rp ' + (value / 1e6) + ' Jt';
                        }
                        return 'Rp ' + value.toLocaleString('id-ID');
                      }
                    }
                  },
                  x: {
                    grid: {
                      display: false
                    },
                    ticks: {
                      font: {
                        family: "'Outfit', sans-serif",
                        weight: '600'
                      },
                      color: '#64748b'
                    }
                  }
                }
              }
            });
          }

          function renderSemesterChart() {
            const tahun = parseInt(document.getElementById('semesterChartTahun').value);
            
            // Temukan record data untuk tahun tersebut
            const recordsForYear = rawSemData.filter(item => parseInt(item.tahun) === tahun);
            
            const labels = [];
            const dataAnggaran = [];
            const dataRealisasi = [];

            // Cari semester 1 dan 2
            for (let sem = 1; sem <= 2; sem++) {
              const record = recordsForYear.find(item => parseInt(item.semester) === sem);
              labels.push(`Semester ${sem}`);
              dataAnggaran.push(record ? parseFloat(record.total_anggaran || 0) : 0);
              dataRealisasi.push(record ? parseFloat(record.total_realisasi || 0) : 0);
            }

            if (currentSemesterChart) {
              currentSemesterChart.destroy();
            }

            currentSemesterChart = new Chart(ctxSemester, {
              type: 'bar',
              data: {
                labels: labels,
                datasets: [
                  {
                    label: 'Anggaran (Target)',
                    data: dataAnggaran,
                    backgroundColor: 'rgba(139, 92, 246, 0.85)', // Tailwind Purple 500
                    borderColor: 'rgb(139, 92, 246)',
                    borderWidth: 1,
                    borderRadius: 6
                  },
                  {
                    label: 'Realisasi (Dilaksanakan)',
                    data: dataRealisasi,
                    backgroundColor: 'rgba(16, 185, 129, 0.85)', // Tailwind Emerald 500 (Green)
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 1,
                    borderRadius: 6
                  }
                ]
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: {
                    position: 'top',
                    labels: {
                      font: {
                        family: "'Outfit', sans-serif",
                        weight: '600'
                      },
                      color: '#475569'
                    }
                  },
                  tooltip: {
                    padding: 12,
                    bodyFont: {
                      family: "'Outfit', sans-serif"
                    },
                    callbacks: {
                      label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                          label += ': ';
                        }
                        if (context.parsed.y !== null) {
                          label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                        }
                        return label;
                      }
                    }
                  }
                },
                scales: {
                  y: {
                    beginAtZero: true,
                    grid: {
                      color: '#f1f5f9'
                    },
                    ticks: {
                      font: {
                        family: "'Outfit', sans-serif"
                      },
                      color: '#64748b',
                      callback: function(value) {
                        if (value >= 1e6) {
                          return 'Rp ' + (value / 1e6) + ' Jt';
                        }
                        return 'Rp ' + value.toLocaleString('id-ID');
                      }
                    }
                  },
                  x: {
                    grid: {
                      display: false
                    },
                    ticks: {
                      font: {
                        family: "'Outfit', sans-serif",
                        weight: '600'
                      },
                      color: '#64748b'
                    }
                  }
                }
              }
            });
          }

          // Event Listeners for Monthly Chart
          document.getElementById('chartTahun').addEventListener('change', renderMonthlyChart);
          document.getElementById('chartSemester').addEventListener('change', renderMonthlyChart);

          // Event Listeners for Semester Chart
          document.getElementById('semesterChartTahun').addEventListener('change', renderSemesterChart);

          // Initial Render
          renderMonthlyChart();
          renderSemesterChart();
        });
      </script>


      <!-- REALISASI TAHUN INI -->
      <div class="mb-16">
        <h3 class="text-2xl font-bold text-gray-800 mb-8 text-center">Realisasi Tahun Ini</h3>
        <div class="overflow-x-auto">
          <?php 
            $tampil_realisasi = array_filter($realisasi, function($r) use ($tahun_ini) {
                return $r['tahun'] == $tahun_ini;
            });
            $items_per_page = 6;
            $total_items = count($tampil_realisasi);
            $total_pages = ceil($total_items / $items_per_page);
            if (empty($tampil_realisasi)):
          ?>
          <div class="text-center py-12 bg-gray-50 rounded-xl">
            <p class="text-gray-500">Belum ada data realisasi tahun <?php echo $tahun_ini; ?>.</p>
          </div>
          <?php else:
          ?>
          <table class="w-full border-collapse bg-white rounded-xl shadow-lg overflow-hidden" id="realisasiTable">
            <thead>
              <tr class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                <th class="px-6 py-4 text-left font-bold">Bulan</th>
                <th class="px-6 py-4 text-center font-bold">Target (Anggaran)</th>
                <th class="px-6 py-4 text-center font-bold">Realisasi</th>
                <th class="px-6 py-4 text-center font-bold">Indikator Persentase</th>
                <th class="px-6 py-4 text-center font-bold">Aksi</th>
              </tr>
            </thead>
            <tbody id="realisasiTbody">
              <?php
                $index = 0;
                foreach ($tampil_realisasi as $r): 
                  $persen = $r['anggaran'] > 0 ? round(($r['realisasi'] / $r['anggaran']) * 100, 1) : 0;
              ?>
              <tr class="border-b border-gray-100 hover:bg-blue-50 transition-colors realisasi-row" data-index="<?php echo $index; ?>">
                <td class="px-6 py-4 font-semibold text-gray-800"><?php echo $r['bulan']; ?> <?php echo $r['tahun']; ?></td>
                <td class="px-6 py-4 text-center text-gray-700 font-medium">Rp <?php echo number_format($r['anggaran'], 0, ',', '.'); ?></td>
                <td class="px-6 py-4 text-center text-gray-700 font-medium">Rp <?php echo number_format($r['realisasi'], 0, ',', '.'); ?></td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-center gap-3">
                    <div class="w-48 bg-gray-200 rounded-full h-4 overflow-hidden">
                      <div class="h-full rounded-full transition-all duration-1000 <?php echo $persen >=80 ? 'bg-green-500' : ($persen >=50 ? 'bg-yellow-500' : 'bg-red-500'); ?>" style="width: <?php echo $persen; ?>%"></div>
                    </div>
                    <span class="font-bold <?php echo $persen >= 80 ? 'text-green-600' : ($persen >=50 ? 'text-yellow-600' : 'text-red-600'); ?>"><?php echo $persen; ?>%</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-center">
                  <?php if ($r['file_pdf']): ?>
                    <a href="<?php echo htmlspecialchars(function_exists('resolveAbsoluteUrl') ? resolveAbsoluteUrl($r['file_pdf']) : $r['file_pdf']); ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors">
                      <iconify-icon icon="lucide:file-text"></iconify-icon> Lihat PDF
                    </a>
                  <?php else: ?>
                    <span class="text-gray-400">-</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php $index++; endforeach; ?>
            </tbody>
          </table>
          <?php if ($total_pages > 1): ?>
          <div class="flex items-center justify-center gap-4 mt-6">
            <button id="prevBtn" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
              <iconify-icon icon="lucide:chevron-left" class="w-5 h-5 inline-block mr-1"></iconify-icon> Sebelumnya
            </button>
            <span class="text-gray-600 font-medium">Halaman <span id="currentPage">1</span> dari <?php echo $total_pages; ?></span>
            <button id="nextBtn" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
              Selanjutnya <iconify-icon icon="lucide:chevron-right" class="w-5 h-5 inline-block ml-1"></iconify-icon>
            </button>
          </div>
          <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>


    </div>
  </section>
