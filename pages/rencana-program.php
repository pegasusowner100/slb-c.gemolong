<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$title = "Rencana Program — SLB BC KARYA SEJAHTERA " . SITE_NAME;

// Fetch rencana program dari tabel rencana_program
$rencana_programs = [];
if ($supabaseConnected) {
    $result = supabaseSelect('rencana_program', ['order' => 'urutan.asc']);
    if ($result['success']) {
        $rencana_programs = $result['data'];
    }
}

// Pisahkan data berdasarkan jenis
$program_pendek = array_filter($rencana_programs, function($p) {
    return ($p['jenis'] ?? 'pendek') === 'pendek';
});
$program_panjang = array_filter($rencana_programs, function($p) {
    return ($p['jenis'] ?? 'pendek') === 'panjang';
});

include '../components/head.php';
?>
<body class="text-brand-dark font-sans glass-body">
  <?php include '../components/navbar.php'; ?>
  <div class="glass-content-wrapper">

  <!-- Hero Section -->
  <section class="relative overflow-hidden bg-gradient-to-br from-brand-dark via-slate-800 to-slate-900 py-24">
    <div class="absolute inset-0 opacity-10">
      <div class="absolute top-0 left-0 w-96 h-96 bg-orange-500 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
      <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-500 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>
    </div>
    <div class="max-w-7xl mx-auto px-6 relative z-10">
      <div class="text-center">
        <span class="text-[10px] font-bold tracking-[0.3em] uppercase text-orange-400 mb-4 inline-block fade-in-up">Perencanaan Strategis</span>
        <h1 class="font-serif text-4xl md:text-5xl lg:text-6xl font-normal tracking-tight text-white leading-[1.1] mb-6 fade-in-up delay-100">
          Rencana <em class="text-orange-400">Program</em>
        </h1>
        <p class="text-slate-300 text-lg max-w-2xl mx-auto fade-in-up delay-200">
          Program unggulan sekolah untuk meningkatkan kualitas pendidikan dan pengembangan peserta didik.
        </p>
      </div>
    </div>
  </section>

  <!-- Rencana Program List -->
  <section id="rencana-program" class="py-24">
    <div class="max-w-7xl mx-auto px-6">
      <div class="grid lg:grid-cols-2 gap-12">
        <!-- Kolom 1: Program Jangka Pendek -->
        <div class="fade-in-up delay-100">
          <div class="flex items-center gap-4 mb-10">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-orange-500/30">
              <iconify-icon icon="lucide:clock" class="w-7 h-7 text-white"></iconify-icon>
            </div>
            <h3 class="font-serif text-3xl font-semibold text-brand-dark">Program Jangka Pendek</h3>
          </div>
          
          <?php if (empty($program_pendek)): ?>
            <div class="bg-gradient-to-br from-white to-slate-50 rounded-3xl shadow-xl border border-slate-100 p-16 text-center">
              <div class="w-24 h-24 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-6">
                <iconify-icon icon="lucide:inbox" class="text-5xl text-slate-300"></iconify-icon>
              </div>
              <h4 class="text-xl font-semibold text-slate-700 mb-2">Belum Ada Program</h4>
              <p class="text-slate-500">Program jangka pendek akan ditampilkan disini.</p>
            </div>
          <?php else: ?>
            <div class="space-y-6">
              <?php foreach ($program_pendek as $index => $program): ?>
                <div class="bg-gradient-to-br from-white to-slate-50 rounded-3xl shadow-lg border border-slate-100 overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 group relative">
                  <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-amber-500 to-orange-600 group-hover:h-2 transition-all duration-300"></div>
                  <div class="p-8">
                    <div class="flex items-start justify-between gap-4 mb-6">
                      <h4 class="font-serif text-2xl font-semibold text-brand-dark group-hover:text-orange-600 transition-colors">
                        <?php echo htmlspecialchars($program['nama'] ?? ''); ?>
                      </h4>
                      <?php if (!empty($program['durasi'])): ?>
                        <span class="flex-shrink-0 px-4 py-2 bg-gradient-to-r from-amber-100 to-orange-100 text-orange-700 rounded-xl text-xs font-bold uppercase tracking-wider shadow-sm">
                          <?php echo htmlspecialchars($program['durasi']); ?>
                        </span>
                      <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($program['deskripsi'])): ?>
                      <div class="text-slate-600 leading-relaxed mb-8">
                        <?php echo nl2br(htmlspecialchars($program['deskripsi'])); ?>
                      </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($program['target'])): ?>
                      <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-6">
                        <h5 class="text-xs font-bold text-orange-700 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                          <iconify-icon icon="lucide:target" class="w-4 h-4"></iconify-icon>
                          Target
                        </h5>
                        <div class="text-slate-700 space-y-3">
                          <?php 
                            $lines = array_filter(array_map('trim', explode("\n", $program['target'])));
                            foreach ($lines as $line):
                              if (!empty($line)):
                          ?>
                            <div class="flex items-start gap-3">
                              <div class="w-6 h-6 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <iconify-icon icon="lucide:check" class="w-3.5 h-3.5 text-white"></iconify-icon>
                              </div>
                              <span class="pt-0.5"><?php echo htmlspecialchars($line); ?></span>
                            </div>
                          <?php 
                              endif;
                            endforeach; 
                          ?>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Kolom 2: Program Jangka Panjang -->
        <div class="fade-in-up delay-200">
          <div class="flex items-center gap-4 mb-10">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center shadow-lg shadow-blue-500/30">
              <iconify-icon icon="lucide:trending-up" class="w-7 h-7 text-white"></iconify-icon>
            </div>
            <h3 class="font-serif text-3xl font-semibold text-brand-dark">Program Jangka Panjang</h3>
          </div>
          
          <?php if (empty($program_panjang)): ?>
            <div class="bg-gradient-to-br from-white to-slate-50 rounded-3xl shadow-xl border border-slate-100 p-16 text-center">
              <div class="w-24 h-24 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-6">
                <iconify-icon icon="lucide:inbox" class="text-5xl text-slate-300"></iconify-icon>
              </div>
              <h4 class="text-xl font-semibold text-slate-700 mb-2">Belum Ada Program</h4>
              <p class="text-slate-500">Program jangka panjang akan ditampilkan disini.</p>
            </div>
          <?php else: ?>
            <div class="space-y-6">
              <?php foreach ($program_panjang as $index => $program): ?>
                <div class="bg-gradient-to-br from-white to-slate-50 rounded-3xl shadow-lg border border-slate-100 overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 group relative">
                  <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-600 to-blue-800 group-hover:h-2 transition-all duration-300"></div>
                  <div class="p-8">
                    <div class="flex items-start justify-between gap-4 mb-6">
                      <h4 class="font-serif text-2xl font-semibold text-brand-dark group-hover:text-blue-600 transition-colors">
                        <?php echo htmlspecialchars($program['nama'] ?? ''); ?>
                      </h4>
                      <?php if (!empty($program['durasi'])): ?>
                        <span class="flex-shrink-0 px-4 py-2 bg-gradient-to-r from-blue-100 to-blue-200 text-blue-700 rounded-xl text-xs font-bold uppercase tracking-wider shadow-sm">
                          <?php echo htmlspecialchars($program['durasi']); ?>
                        </span>
                      <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($program['deskripsi'])): ?>
                      <div class="text-slate-600 leading-relaxed mb-8">
                        <?php echo nl2br(htmlspecialchars($program['deskripsi'])); ?>
                      </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($program['target'])): ?>
                      <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6">
                        <h5 class="text-xs font-bold text-blue-700 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                          <iconify-icon icon="lucide:target" class="w-4 h-4"></iconify-icon>
                          Target
                        </h5>
                        <div class="text-slate-700 space-y-3">
                          <?php 
                            $lines = array_filter(array_map('trim', explode("\n", $program['target'])));
                            foreach ($lines as $line):
                              if (!empty($line)):
                          ?>
                            <div class="flex items-start gap-3">
                              <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <iconify-icon icon="lucide:check" class="w-3.5 h-3.5 text-white"></iconify-icon>
                              </div>
                              <span class="pt-0.5"><?php echo htmlspecialchars($line); ?></span>
                            </div>
                          <?php 
                              endif;
                            endforeach; 
                          ?>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

    </div> <!-- glass-content-wrapper end -->
  <?php include '../components/footer.php'; ?>
</body>
</html>