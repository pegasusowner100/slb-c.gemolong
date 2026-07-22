<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/track-visitor.php';
trackVisitor('/pages/prestasi');
$title = "Prestasi — SLB BC KARYA SEJAHTERA " . SITE_NAME;

// Fetch prestasi
$prestasi_list = [];
if ($supabaseConnected) {
    $prestasiResult = supabaseSelect('prestasi', ['order' => 'tahun.desc']);
    if ($prestasiResult['success']) {
        $prestasi_list = $prestasiResult['data'];
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
      <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label fade-in-up">Jejak Keunggulan</span>
      <h1 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1] fade-in-up delay-100">Prestasi <em>Siswa</em></h1>
    </div>
  </section>

  <!-- PRESTASI -->
  <section id="prestasi" class="py-24">
    <div class="max-w-7xl mx-auto px-6">
      <div class="glass-section">
        <div class="text-center mb-8 fade-in-up delay-100">
          <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Prestasi</span>
            <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Prestasi Siswa</h2>
          </div>
        </div>
        <?php if (empty($prestasi_list)): ?>
          <div class="col-span-full text-center py-12">
            <iconify-icon icon="lucide:trophy" class="text-6xl text-brand-muted/30 mb-4"></iconify-icon>
            <p class="text-brand-muted">Belum ada data prestasi.</p>
          </div>
        <?php else: ?>
          <div id="prestasiGrid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($prestasi_list as $index => $prestasi): ?>
              <div class="prestasi-item bg-white border border-brand-border rounded-lg overflow-hidden transition-all duration-300 hover:border-brand-accent/30 hover:shadow-xl hover:-translate-y-1 fade-in-up" style="animation-delay: <?php echo ($index % 5 + 1) * 100; ?>ms">
                <img src="<?php echo htmlspecialchars($prestasi['foto'] ?? 'https://picsum.photos/seed/' . htmlspecialchars($prestasi['id'] ?? 'default') . '/400/250.jpg'); ?>" alt="<?php echo htmlspecialchars($prestasi['nama']); ?>" class="w-full h-48 object-cover">
                <div class="p-6">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-yellow-500/10 flex items-center justify-center">
                      <iconify-icon icon="lucide:medal" class="text-yellow-600"></iconify-icon>
                    </div>
                    <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label">
                      <?php echo htmlspecialchars($prestasi['kategori'] ?? 'Umum'); ?> • <?php echo htmlspecialchars($prestasi['tahun'] ?? '-'); ?>
                    </span>
                  </div>
                  <h3 class="font-serif text-lg text-brand-dark mb-2"><?php echo htmlspecialchars($prestasi['nama']); ?></h3>
                  <p class="text-brand-muted text-sm font-light">
                    <?php echo htmlspecialchars($prestasi['peraih'] ?? '-'); ?><?php if (!empty($prestasi['lokasi'])): ?> — <?php echo htmlspecialchars($prestasi['lokasi']); ?><?php endif; ?>
                  </p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

    </div> <!-- glass-content-wrapper end -->
  <?php include '../components/footer.php'; ?>
</body>
</html>
