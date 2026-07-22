<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/track-visitor.php';
trackVisitor('/pages/program');
$title = "Program Unggulan — SLB BC KARYA SEJAHTERA " . SITE_NAME;

// Fetch programs
$programs = [];
if ($supabaseConnected) {
    $programResult = supabaseSelect('program', ['order' => 'urutan.asc']);
    if ($programResult['success']) {
        $programs = $programResult['data'];
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
      <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label fade-in-up">Program & Ekstrakurikuler</span>
      <h1 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1] fade-in-up delay-100">Program <em>Unggulan</em></h1>
    </div>
  </section>

  <!-- PROGRAM UNGGULAN -->
  <section id="program" class="py-24">
    <div class="max-w-7xl mx-auto px-6">
      <div class="glass-section">
        <div class="text-center mb-8 fade-in-up delay-100">
          <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Program</span>
            <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Program Unggulan</h2>
          </div>
        </div>
        <div class="grid md:grid-cols-2 gap-8">
          <?php foreach ($programs as $index => $prog): ?>
            <div class="group relative overflow-hidden rounded-lg shadow-lg h-96 fade-in-up delay-<?= ($index % 5 + 1) * 100 ?>">
              <img src="<?php echo htmlspecialchars($prog['gambar'] ?? 'https://picsum.photos/seed/program-default/600/400'); ?>" alt="<?php echo htmlspecialchars($prog['nama'] ?? ''); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
              <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/80 to-transparent"></div>
              <div class="absolute bottom-6 left-6 right-6">
                <h3 class="font-serif text-xl text-white mb-2"><?php echo htmlspecialchars($prog['nama'] ?? ''); ?></h3>
                <p class="text-white/80 text-sm"><?php echo htmlspecialchars($prog['deskripsi'] ?? ''); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

    </div> <!-- glass-content-wrapper end -->
  <?php include '../components/footer.php'; ?>
</body>
</html>
