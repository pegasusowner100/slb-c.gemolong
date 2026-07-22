<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$title = "Siswa — SLB BC KARYA SEJAHTERA";

// Ambil data siswa (hanya yang aktif)
$all_siswa = [];
if ($supabaseConnected) {
    $result = supabaseSelect('siswa', ['status' => 'eq.Aktif', 'order' => 'no_induk.asc']);
    if ($result['success']) {
        $all_siswa = $result['data'];
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
      <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label fade-in-up">Peserta Didik</span>
      <h1 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1] fade-in-up delay-100">Siswa <em>Berprestasi</em></h1>
    </div>
  </section>

  <!-- SISWA -->
  <section id="siswa" class="py-24">
    <div class="max-w-7xl mx-auto px-6">
      <div class="glass-section">
        <div class="text-center mb-8 fade-in-up delay-100">
          <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Siswa</span>
            <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Siswa Berprestasi</h2>
          </div>
        </div>
        <?php if (empty($all_siswa)): ?>
          <div class="text-center py-16">
            <p class="text-brand-muted">Belum ada data siswa.</p>
          </div>
        <?php else: ?>
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($all_siswa as $index => $siswa): ?>
              <div class="group cursor-pointer text-center fade-in-up delay-<?= ($index % 5 + 1) * 100 ?>">
                <div class="overflow-hidden rounded-lg mb-4">
                  <img src="<?= htmlspecialchars($siswa['foto'] ?? 'https://picsum.photos/seed/default-siswa/300/360.jpg') ?>" 
                       class="w-full h-[280px] object-cover transition-transform duration-500 group-hover:scale-105" 
                       alt="<?= htmlspecialchars($siswa['nama']) ?>">
                </div>
                <h3 class="font-serif text-lg mb-1"><?= htmlspecialchars($siswa['nama']) ?></h3>
                <p class="text-xs text-brand-muted mb-1">No. Induk: <?= htmlspecialchars($siswa['no_induk']) ?></p>
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold <?= $siswa['status'] == 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                  <?= htmlspecialchars($siswa['status']) ?>
                </span>
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
