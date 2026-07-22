
<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/track-visitor.php';
trackVisitor('/pages/kontak');
$title = "Hubungi Kami — SLB BC KARYA SEJAHTERA";
include '../components/head.php';
?>
<body class="text-brand-dark font-sans glass-body">
  <?php include '../components/navbar.php'; ?>
  <div class="glass-content-wrapper">

  <!-- Header -->
  <section class="page-hero bg-brand-dark">
    <div class="max-w-7xl mx-auto px-6">
      <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label fade-in-up">Hubungi Kami</span>
      <h1 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1] fade-in-up delay-100">Kami Siap <em>Membantu</em></h1>
    </div>
  </section>

  <!-- KONTAK -->
  <section id="kontak" class="py-24">
    <div class="max-w-7xl mx-auto px-6">
      <div class="glass-section">
        <div class="text-center mb-8 fade-in-up delay-100">
          <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Kontak</span>
            <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Hubungi Kami</h2>
          </div>
        </div>
        <div class="grid lg:grid-cols-2 gap-16">
          <div class="fade-in-left delay-200">
            <h2 class="font-serif text-3xl md:text-4xl font-normal tracking-tight mb-6">Informasi <em>Kontak</em></h2>
            <p class="text-brand-muted text-sm font-light leading-relaxed mb-10">Jangan ragu untuk menghubungi kami jika Anda memiliki pertanyaan seputar pendaftaran atau informasi sekolah lainnya.</p>
            <div class="space-y-6">
              <div class="flex items-start gap-4"><div class="w-10 h-10 rounded-full bg-brand-accent/10 flex items-center justify-center flex-shrink-0"><iconify-icon icon="lucide:map-pin" class="text-brand-accent"></iconify-icon></div><div><h4 class="text-sm font-semibold mb-1">Alamat</h4><p class="text-sm text-brand-muted font-light"><?php echo htmlspecialchars($profilSekolah['alamat']); ?></p></div></div>
              <div class="flex items-start gap-4"><div class="w-10 h-10 rounded-full bg-brand-accent/10 flex items-center justify-center flex-shrink-0"><iconify-icon icon="lucide:phone" class="text-brand-accent"></iconify-icon></div><div><h4 class="text-sm font-semibold mb-1">Telepon</h4><p class="text-sm text-brand-muted font-light"><a href="tel:<?php echo preg_replace('/[^0-9]/', '', $profilSekolah['telepon']); ?>" class="hover:text-brand-accent transition-colors"><?php echo htmlspecialchars($profilSekolah['telepon']); ?></a></p></div></div>
              <div class="flex items-start gap-4"><div class="w-10 h-10 rounded-full bg-brand-accent/10 flex items-center justify-center flex-shrink-0"><iconify-icon icon="lucide:message-circle" class="text-brand-accent"></iconify-icon></div><div><h4 class="text-sm font-semibold mb-1">WhatsApp</h4><p class="text-sm text-brand-muted font-light"><a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $profilSekolah['telepon']); ?>" target="_blank" class="hover:text-brand-accent transition-colors flex items-center gap-1"><iconify-icon icon="lucide:external-link" class="text-xs"></iconify-icon> <?php echo htmlspecialchars($profilSekolah['telepon']); ?></a></p></div></div>
              <div class="flex items-start gap-4"><div class="w-10 h-10 rounded-full bg-brand-accent/10 flex items-center justify-center flex-shrink-0"><iconify-icon icon="lucide:mail" class="text-brand-accent"></iconify-icon></div><div><h4 class="text-sm font-semibold mb-1">Email</h4><p class="text-sm text-brand-muted font-light"><a href="mailto:<?php echo htmlspecialchars($profilSekolah['email']); ?>" class="hover:text-brand-accent transition-colors"><?php echo htmlspecialchars($profilSekolah['email']); ?></a></p></div></div>
              
              <!-- Website -->
              <?php if (!empty($profilSekolah['website'])): ?>
                <div class="flex items-start gap-4"><div class="w-10 h-10 rounded-full bg-brand-accent/10 flex items-center justify-center flex-shrink-0"><iconify-icon icon="lucide:globe" class="text-brand-accent"></iconify-icon></div><div><h4 class="text-sm font-semibold mb-1">Website</h4><p class="text-sm text-brand-muted font-light"><a href="<?php echo htmlspecialchars($profilSekolah['website']); ?>" target="_blank" class="hover:text-brand-accent transition-colors"><?php echo htmlspecialchars($profilSekolah['website']); ?></a></p></div></div>
              <?php endif; ?>
              
              <!-- Sosial Media -->
              <div class="pt-6 border-t border-brand-border/30">
                <h4 class="text-sm font-semibold mb-4">Ikuti Kami</h4>
                <div class="grid grid-cols-4 gap-3">
                  <?php if (!empty($profilSekolah['instagram'])): ?>
                    <a href="<?php echo htmlspecialchars($profilSekolah['instagram']); ?>" target="_blank" class="flex flex-col items-center gap-2 relative group">
                      <div class="w-12 h-12 rounded-full bg-gradient-to-r from-pink-500 to-purple-600 flex items-center justify-center text-white hover:shadow-lg hover:scale-110 transition-all duration-300">
                        <iconify-icon icon="lucide:instagram" class="text-xl"></iconify-icon>
                      </div>
                      <span class="text-xs text-brand-muted">Instagram</span>
                    </a>
                  <?php endif; ?>
                  <?php if (!empty($profilSekolah['facebook'])): ?>
                    <a href="<?php echo htmlspecialchars($profilSekolah['facebook']); ?>" target="_blank" class="flex flex-col items-center gap-2 relative group">
                      <div class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-600 to-blue-800 flex items-center justify-center text-white hover:shadow-lg hover:scale-110 transition-all duration-300">
                        <iconify-icon icon="lucide:facebook" class="text-xl"></iconify-icon>
                      </div>
                      <span class="text-xs text-brand-muted">Facebook</span>
                    </a>
                  <?php endif; ?>
                  <?php if (!empty($profilSekolah['youtube'])): ?>
                    <a href="<?php echo htmlspecialchars($profilSekolah['youtube']); ?>" target="_blank" class="flex flex-col items-center gap-2 relative group">
                      <div class="w-12 h-12 rounded-full bg-gradient-to-r from-red-600 to-red-800 flex items-center justify-center text-white hover:shadow-lg hover:scale-110 transition-all duration-300">
                        <iconify-icon icon="lucide:youtube" class="text-xl"></iconify-icon>
                      </div>
                      <span class="text-xs text-brand-muted">YouTube</span>
                    </a>
                  <?php endif; ?>
                  <?php if (!empty($profilSekolah['tiktok'])): ?>
                    <a href="<?php echo htmlspecialchars($profilSekolah['tiktok']); ?>" target="_blank" class="flex flex-col items-center gap-2 relative group">
                      <div class="w-12 h-12 rounded-full bg-gradient-to-r from-black to-gray-800 flex items-center justify-center text-white hover:shadow-lg hover:scale-110 transition-all duration-300">
                        <iconify-icon icon="lucide:music" class="text-xl"></iconify-icon>
                      </div>
                      <span class="text-xs text-brand-muted">TikTok</span>
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <div class="space-y-6 fade-in-right delay-300 pt-12">
            <!-- Peta Lokasi Sekolah -->
            <h3 class="font-serif text-2xl font-normal tracking-tight">Map Lokasi</h3>
            <div class="bg-white border border-brand-border rounded-lg shadow-sm overflow-hidden">
              <div class="aspect-video w-full">
                <?php
                  // Bangun URL peta dari database (maps_url preferensi), lalu koordinat, lalu alamat
                  $mapSrc = '';
                  $mapsUrl = trim($profilSekolah['maps_url'] ?? '');
                  if (!empty($mapsUrl)) {
                    if (strpos($mapsUrl, '/embed') !== false || strpos($mapsUrl, 'output=embed') !== false) {
                      $mapSrc = $mapsUrl;
                    } elseif (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $mapsUrl, $mcoords)) {
                      $mapSrc = 'https://www.google.com/maps?q=' . urlencode($mcoords[1] . ',' . $mcoords[2]) . '&output=embed';
                    } else {
                      $address = !empty($profilSekolah['alamat']) ? $profilSekolah['alamat'] : SITE_NAME;
                      $mapSrc = 'https://www.google.com/maps?q=' . rawurlencode($address) . '&output=embed';
                    }
                  } elseif (!empty($profilSekolah['latitude']) && !empty($profilSekolah['longitude'])) {
                    $mapSrc = 'https://www.google.com/maps?q=' . urlencode($profilSekolah['latitude'] . ',' . $profilSekolah['longitude']) . '&output=embed';
                  } else {
                    // Fallback: small OSM bbox centered on default coords
                    $mapSrc = 'https://www.openstreetmap.org/export/embed.html?bbox=110.83%2C-7.41%2C110.85%2C-7.39&layer=mapnik&marker=-7.400964%2C110.838884';
                  }
                ?>
                <iframe src="<?php echo htmlspecialchars($mapSrc); ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
              </div>
              <div class="p-3 border-t border-brand-border/30 bg-white/80 flex items-center justify-end">
                <?php
                  // Buat link buka di Google Maps (non-embed) jika memungkinkan
                  $openUrl = '';
                  if (!empty($mapsUrl)) {
                    if (strpos($mapsUrl, 'google.com') !== false) {
                      $openUrl = $mapsUrl;
                    } elseif (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $mapsUrl, $mcoords)) {
                      $openUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($mcoords[1] . ',' . $mcoords[2]);
                    }
                  }
                  if (empty($openUrl) && !empty($profilSekolah['latitude']) && !empty($profilSekolah['longitude'])) {
                    $openUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($profilSekolah['latitude'] . ',' . $profilSekolah['longitude']);
                  }
                  if (empty($openUrl)) {
                    $openUrl = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($profilSekolah['alamat'] ?? SITE_NAME);
                  }
                ?>
                <a href="<?php echo htmlspecialchars($openUrl); ?>" target="_blank" class="text-sm font-semibold text-brand-accent hover:underline">Buka di Google Maps</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

    </div> <!-- glass-content-wrapper end -->
  <?php include '../components/footer.php'; ?>
</body>
</html>
