<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/track-visitor.php';
trackVisitor('/pages/guru');
$title = "Guru & Tenaga Pendidik — SLB BC KARYA SEJAHTERA " . SITE_NAME;

// Fetch guru
$guru_list = [];
if ($supabaseConnected) {
  $guruResult = supabaseSelect('guru', ['order' => 'urutan.asc']);
  if (!$guruResult['success']) {
    $guruResult = supabaseSelect('guru', ['order' => 'created_at.asc']);
  }
  if (!$guruResult['success']) {
    $guruResult = supabaseSelect('guru', []);
  }
  if ($guruResult['success']) {
    $guru_list = $guruResult['data'];
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
      <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label fade-in-up">Tenaga Pendidik</span>
      <h1 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1] fade-in-up delay-100">Guru <em>Berdedikasi</em></h1>
    </div>
  </section>

  <!-- GURU -->
  <section id="guru" class="py-24">
    <div class="max-w-7xl mx-auto px-6">
      <div class="glass-section">
        <div class="text-center mb-8 fade-in-up delay-100">
          <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Guru</span>
            <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Guru Berdedikasi</h2>
          </div>
        </div>
        <?php if (empty($guru_list)): ?>
          <div class="text-center py-12">
            <iconify-icon icon="lucide:users" class="text-6xl text-brand-muted/30 mb-4"></iconify-icon>
            <p class="text-brand-muted">Belum ada data guru.</p>
          </div>
        <?php else: ?>
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($guru_list as $index => $guru): ?>
              <div class="teacher-card group cursor-pointer text-center fade-in-up delay-<?php echo ($index % 5 + 1) * 100; ?>">
                <div class="overflow-hidden rounded-lg mb-4">
                  <img src="<?php echo htmlspecialchars($guru['foto'] ?? 'https://i.pravatar.cc/300/360'); ?>" class="teacher-img w-full h-[320px] md:h-[360px] object-cover object-center transition-transform duration-500 group-hover:scale-110" alt="<?php echo htmlspecialchars($guru['nama']); ?>">
                </div>
                <h3 class="font-serif text-lg mb-1"><?php echo htmlspecialchars($guru['nama']); ?></h3>
                <p class="text-xs text-brand-muted"><?php echo htmlspecialchars($guru['jabatan']); ?></p>
                <?php if (!empty($guru['mapel'])): ?>
                  <p class="text-xs text-brand-muted mt-1">Mapel: <?php echo htmlspecialchars($guru['mapel']); ?></p>
                <?php endif; ?>
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
