<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$title = "Fasilitas — " . SITE_NAME;

// Fetch fasilitas
$fasilitas = [];
if ($supabaseConnected) {
    $fasilitasResult = supabaseSelect('fasilitas', ['order' => 'urutan.asc']);
    if ($fasilitasResult['success']) {
        $fasilitas = $fasilitasResult['data'];
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
      <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label fade-in-up">Lingkungan Belajar</span>
      <h1 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1] fade-in-up delay-100">Fasilitas <em>Sekolah</em></h1>
    </div>
  </section>

  <!-- FASILITAS -->
  <section id="fasilitas" class="py-24">
    <div class="max-w-7xl mx-auto px-6">
      <div class="glass-section">
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php if (empty($fasilitas)): ?>
            <div class="col-span-full text-center py-12">
              <iconify-icon icon="lucide:building-2" class="text-6xl text-brand-muted/30 mb-4"></iconify-icon>
              <p class="text-brand-muted">Belum ada data fasilitas.</p>
            </div>
          <?php else: ?>
            <?php foreach ($fasilitas as $index => $f): ?>
              <div class="facility-card relative overflow-hidden rounded-lg cursor-pointer group fade-in-up delay-<?php echo ($index % 5 + 1) * 100; ?>">
                <img src="<?php echo htmlspecialchars($f['gambar'] ?? 'https://picsum.photos/seed/' . urlencode($f['nama'] ?? 'default-fasilitas') . '/500/400.jpg'); ?>" class="facility-img w-full h-[300px] object-cover transition-transform duration-500" alt="<?php echo htmlspecialchars($f['nama'] ?? ''); ?>">
                <div class="absolute inset-0 bg-brand-accent/80 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                  <div class="text-center text-white">
                    <iconify-icon icon="lucide:check-circle" class="text-3xl mb-2"></iconify-icon>
                    <h3 class="font-serif text-lg"><?php echo htmlspecialchars($f['nama'] ?? ''); ?></h3>
                    <?php if (!empty($f['deskripsi'])): ?>
                      <p class="text-xs mt-2"><?php echo htmlspecialchars($f['deskripsi']); ?></p>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 p-5" style="background:linear-gradient(to top, rgba(255,255,255,0.1), transparent);">
                  <h3 class="font-serif text-lg text-white"><?php echo htmlspecialchars($f['nama'] ?? ''); ?></h3>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

    </div> <!-- glass-content-wrapper end -->
  <?php include '../components/footer.php'; ?>
</body>
</html>
