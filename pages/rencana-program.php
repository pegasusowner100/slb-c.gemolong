<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$title = "Rencana Program — " . SITE_NAME;

// Fetch rencana anggaran
$rencana = [];
if ($supabaseConnected) {
    $result = supabaseSelect('rencana_anggaran', ['order' => 'created_at.desc']);
    if ($result['success']) {
        $rencana = $result['data'];
    }
}

// Define labels dan colors untuk setiap jenis rencana
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

include '../components/head.php';
?>
<body class="text-brand-dark font-sans glass-body">
  <?php include '../components/navbar.php'; ?>
  <div class="glass-content-wrapper">

  <!-- Header -->
  <section class="page-hero bg-brand-dark">
    <div class="max-w-7xl mx-auto px-6">
      <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label fade-in-up">Perencanaan</span>
      <h1 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1] fade-in-up delay-100">Rencana <em>Program</em></h1>
    </div>
  </section>

  <!-- Rencana Program List -->
  <section id="rencana-program" class="py-24">
    <div class="max-w-7xl mx-auto px-6">
      <div class="glass-section">
        <div class="text-center mb-12 fade-in-up">
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label">Perencanaan Strategis</span>
          <h2 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-brand-dark mt-4">Rencana <em>Program</em></h2>
        </div>

        <?php if (empty($rencana)): ?>
          <div class="text-center py-12">
            <iconify-icon icon="lucide:calendar" class="text-6xl text-brand-muted/30 mb-4"></iconify-icon>
            <p class="text-brand-muted">Belum ada rencana program.</p>
          </div>
        <?php else: ?>
          <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
            <?php 
              $jenis_idx = 0;
              foreach ($jenis_rencana as $jenis):
                $data_rencana = array_filter($rencana, function($r) use ($jenis) {
                    return isset($r['jenis_rencana']) && $r['jenis_rencana'] == $jenis;
                });
                $data_rencana = !empty($data_rencana) ? array_shift($data_rencana) : null;
            ?>
              <div class="bg-white rounded-2xl shadow-lg border border-brand-border/30 overflow-hidden hover:shadow-xl transition-shadow duration-300 fade-in-up delay-<?= ($jenis_idx + 1) * 100 ?>">
                <!-- Card Header dengan Warna Berbeda -->
                <div class="<?php echo $jenis_warna_header[$jenis]; ?> px-6 py-5 text-white">
                  <h3 class="font-serif text-2xl font-semibold text-center"><?php echo $jenis_label[$jenis]; ?></h3>
                </div>

                <!-- Card Body -->
                <div class="p-8">
                  <?php if ($data_rencana): ?>
                    <!-- Judul -->
                    <h4 class="font-semibold text-lg text-brand-dark text-center mb-4">
                      <?php echo htmlspecialchars($data_rencana['judul'] ?? ''); ?>
                    </h4>

                    <!-- Deskripsi / List Items -->
                    <?php if (!empty($data_rencana['deskripsi'])): ?>
                      <div class="text-sm text-brand-muted space-y-2 mb-6">
                        <?php 
                          $lines = array_filter(array_map('trim', explode("\n", $data_rencana['deskripsi'])));
                          foreach ($lines as $line):
                            if (!empty($line)):
                        ?>
                          <div class="flex items-start gap-3">
                            <span class="text-brand-accent font-bold flex-shrink-0">•</span>
                            <span><?php echo htmlspecialchars($line); ?></span>
                          </div>
                        <?php 
                            endif;
                          endforeach; 
                        ?>
                      </div>
                    <?php endif; ?>

                    <!-- Link File (jika ada) -->
                    <?php if (!empty($data_rencana['file_pdf'])): ?>
                      <div class="flex justify-center pt-4 border-t border-brand-border">
                        <a href="<?php echo htmlspecialchars($data_rencana['file_pdf']); ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-6 py-2 text-sm font-semibold text-brand-dark hover:text-white hover:bg-brand-accent rounded-lg transition-colors border border-brand-accent">
                          <iconify-icon icon="lucide:file-text" class="w-4 h-4"></iconify-icon>
                          Lihat Rencana
                        </a>
                      </div>
                    <?php endif; ?>
                  <?php else: ?>
                    <div class="text-center py-8">
                      <iconify-icon icon="lucide:inbox" class="text-4xl text-brand-muted/30 mb-3 block"></iconify-icon>
                      <p class="text-brand-muted text-sm">Belum ada rencana.</p>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php $jenis_idx++; endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

    </div> <!-- glass-content-wrapper end -->
  <?php include '../components/footer.php'; ?>
</body>
</html>
