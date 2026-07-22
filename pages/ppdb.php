<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/track-visitor.php';
trackVisitor('/pages/ppdb');
$title = "Pendaftaran Siswa Baru — SLB BC KARYA SEJAHTERA";
include '../components/head.php';
?>
<body class="text-brand-dark font-sans glass-body">
  <?php include '../components/navbar.php'; ?>
  <div class="glass-content-wrapper">

  <!-- Header -->
  <section class="page-hero bg-brand-dark">
    <div class="max-w-7xl mx-auto px-6">
      <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label fade-in-up">PPDB</span>
      <h1 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1] fade-in-up delay-100">Formulir <em>Pendaftaran</em></h1>
    </div>
  </section>

  <!-- PPDB FORM -->
  <section id="ppdb" class="py-24">
    <div class="max-w-3xl mx-auto px-6">
      <div class="glass-section">
        <div class="text-center mb-8 fade-in-up delay-100">
          <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">PPDB</span>
            <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Pendaftaran Siswa Baru</h2>
          </div>
        </div>
        <!-- Steps -->
        <div class="flex items-center justify-center gap-4 mb-12 fade-in-up delay-200">
          <div class="ppdb-step current flex items-center gap-2">
            <div class="step-circle w-8 h-8 rounded-full border-2 border-brand-accent flex items-center justify-center text-xs font-bold text-brand-accent">1</div>
            <span class="text-xs text-brand-dark font-medium hidden sm:inline">Data Diri</span>
          </div>
          <div class="w-8 h-px bg-brand-border"></div>
          <div class="ppdb-step pending flex items-center gap-2">
            <div class="step-circle w-8 h-8 rounded-full border-2 border-brand-border flex items-center justify-center text-xs font-bold text-brand-muted">2</div>
            <span class="text-xs text-brand-muted font-medium hidden sm:inline">Pendidikan</span>
          </div>
          <div class="w-8 h-px bg-brand-border"></div>
          <div class="ppdb-step pending flex items-center gap-2">
            <div class="step-circle w-8 h-8 rounded-full border-2 border-brand-border flex items-center justify-center text-xs font-bold text-brand-muted">3</div>
            <span class="text-xs text-brand-muted font-medium hidden sm:inline">Orang Tua</span>
          </div>
        </div>

        <div class="bg-white border border-brand-border rounded-lg p-8 md:p-10 shadow-sm fade-in-up delay-300">
          <h3 class="font-serif text-2xl text-brand-dark mb-4">Informasi Pendaftaran</h3>
          <p class="text-brand-muted mb-6 text-sm leading-relaxed">
            Silakan isi formulir pendaftaran dengan data yang valid dan lengkap. Pastikan semua informasi yang Anda masukkan benar, karena data ini akan digunakan untuk proses verifikasi selanjutnya.
          </p>

          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-blue-800">
              <iconify-icon icon="lucide:info" class="inline-block mr-2"></iconify-icon>
              Pendaftaran akan memerlukan nomor HP orang tua untuk verifikasi lebih lanjut.
            </p>
          </div>

          <div class="flex justify-center">
            <a href="ppdb-form.php" class="bg-brand-accent hover:bg-brand-secondary text-white text-xs font-semibold tracking-widest uppercase px-8 py-4 rounded transition-colors duration-150 inline-flex items-center gap-2">
              <iconify-icon icon="lucide:file-text" class="text-sm"></iconify-icon>
              Buka Form Pendaftaran
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

    </div> <!-- glass-content-wrapper end -->
  <?php include '../components/footer.php'; ?>
</body>
</html>
