<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$title = "Dokumentasi & Galeri — " . SITE_NAME;

// Fetch published galeri
$all_galeri = [];
if ($supabaseConnected) {
    $galeriResult = supabaseSelect('galeri', ['order' => 'tanggal_upload.desc']);
    if ($galeriResult['success']) {
        $all_galeri = array_filter($galeriResult['data'], function($item) {
            return ($item['status'] ?? 'published') === 'published';
        });
    }
}

$galleryItems = array_values(array_filter($all_galeri, function($item) {
    return true;
}));

$photoItems = [];
$videoItems = [];
foreach ($galleryItems as $index => $item) {
    $fileUrl = $item['file_url'] ?? '';
    $extension = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION));
    $jenisLabel = strtolower(trim($item['jenis_galeri'] ?? ''));
    $isVideo = in_array($jenisLabel, ['video', 'vidio']) || in_array($extension, ['mp4','webm','ogg']) || strpos(strtolower($fileUrl), 'video') !== false;
    $item['_index'] = $index;
    if ($isVideo) {
        $videoItems[] = $item;
    } else {
        $photoItems[] = $item;
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
      <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label fade-in-up">Dokumentasi</span>
      <h1 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1] fade-in-up delay-100">Galeri <em>Kegiatan</em></h1>
    </div>
  </section>

  <!-- Galeri -->
  <section id="galeri" class="py-24">
    <div class="max-w-7xl mx-auto px-6">
      <div class="glass-section">
        <?php if(empty($all_galeri)): ?>
          <div class="text-center py-12">
            <iconify-icon icon="lucide:images" class="text-6xl text-brand-muted/30 mb-4"></iconify-icon>
            <p class="text-brand-muted">Belum ada galeri.</p>
          </div>
        <?php else: ?>
          <?php if (empty($galleryItems)): ?>
            <div class="text-center py-12">
              <iconify-icon icon="lucide:image-off" class="text-6xl text-brand-muted/30 mb-4"></iconify-icon>
              <p class="text-brand-muted">Belum ada foto atau video di galeri.</p>
            </div>
          <?php else: ?>
            <?php $showPhotoTab = !empty($photoItems); ?>
            <?php $showVideoTab = !empty($videoItems); ?>
            
            <div class="mb-8">
       
              
              <?php if ($showPhotoTab || $showVideoTab): ?>
              <div class="flex flex-wrap gap-3">
                <?php if ($showPhotoTab): ?>
                  <button type="button" id="tabFotoBtn" class="gallery-tab px-6 py-3 text-sm font-semibold text-white rounded-full bg-brand-accent transition-colors">Foto</button>
                <?php endif; ?>
                <?php if ($showVideoTab): ?>
                  <button type="button" id="tabVideoBtn" class="gallery-tab px-6 py-3 text-sm font-semibold text-brand-dark rounded-full bg-white border border-brand-border/30 transition-colors">Video</button>
                <?php endif; ?>
                <button type="button" id="tabSemuaBtn" class="gallery-tab px-6 py-3 text-sm font-semibold text-brand-dark rounded-full bg-white border border-brand-border/30 transition-colors">Lihat Semua</button>
              </div></br>
                       <p class="text-sm text-brand-muted">Klik thumbnail untuk melihat Detail.</p>

        

              <?php endif; ?>
            </div>
            
            <?php if ($showPhotoTab || $showVideoTab): ?>
              <div id="galleryPhotosSection" class="mb-12">
                <div class="flex items-center justify-between mb-6">
                  <div>
                    <h2 class="font-serif text-2xl md:text-3xl font-bold text-brand-dark">Foto</h2>
         
                  </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                  <?php foreach ($photoItems as $item): ?>
                    <?php
                      $fileUrl = $item['file_url'] ?? '';
                      $previewUrl = $fileUrl ?: 'https://picsum.photos/seed/' . urlencode($item['judul'] ?? 'photo') . '/900/600';
                      $tanggal = isset($item['tanggal_upload']) ? new DateTime($item['tanggal_upload']) : null;
                      $formattedDate = $tanggal ? strftime('%d %B %Y', $tanggal->getTimestamp()) : '';
                    ?>
                    <button type="button" data-galeri-index="<?php echo $item['_index']; ?>" class="group border border-brand-border/30 rounded-[28px] overflow-hidden shadow-sm bg-white hover:shadow-xl transition-shadow">
                      <div class="h-56 overflow-hidden bg-[#4c3900]">
                        <img src="<?php echo htmlspecialchars($previewUrl); ?>" alt="<?php echo htmlspecialchars($item['judul'] ?? 'Foto galeri'); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                      </div>
                      <div class="px-4 pt-3 pb-4 bg-brand-bg/90">
                        <h3 class="text-base font-semibold text-brand-dark leading-tight"><?php echo htmlspecialchars($item['judul'] ?: 'Tanpa Judul'); ?></h3>
                        <p class="mt-2 text-xs uppercase tracking-[0.24em] text-brand-muted"><?php echo htmlspecialchars($formattedDate); ?></p>
                      </div>
                    </button>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>

            <?php if (!empty($videoItems)): ?>
              <div id="galleryVideosSection" class="mb-12 <?php echo ($showPhotoTab && $showVideoTab) ? 'hidden' : ''; ?>">
                <div class="flex items-center justify-between mb-6">
                  <div>
                    <h2 class="font-serif text-2xl md:text-3xl font-bold text-brand-dark">Video</h2>
               
                  </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                  <?php foreach ($videoItems as $item): ?>
                    <?php
                      $fileUrl = $item['file_url'] ?? '';
                      $thumbnailUrl = $item['thumbnail_url'] ?? null;
                      $previewUrl = $thumbnailUrl ?: $fileUrl ?: 'https://picsum.photos/seed/video-' . urlencode($item['judul'] ?? 'video') . '/900/600';
                      $extension = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION));
                      $isVideoFile = in_array($extension, ['mp4','webm','ogg']) || strpos(strtolower($fileUrl), 'video') !== false;
                      $tanggal = isset($item['tanggal_upload']) ? new DateTime($item['tanggal_upload']) : null;
                      $formattedDate = $tanggal ? strftime('%d %B %Y', $tanggal->getTimestamp()) : '';
                    ?>
                    <button type="button" data-galeri-index="<?php echo $item['_index']; ?>" class="group border border-brand-border/30 rounded-[28px] overflow-hidden shadow-sm bg-white hover:shadow-xl transition-shadow">
                      <div class="relative h-56 overflow-hidden bg-[#4c3900]">
                        <?php if ($isVideoFile && !$thumbnailUrl): ?>
                          <video controls class="w-full h-full object-cover bg-[#4c3900]">
                            <source src="<?php echo htmlspecialchars($fileUrl); ?>" type="video/mp4">
                            Browser Anda tidak mendukung video tag.
                          </video>
                        <?php else: ?>
                          <img src="<?php echo htmlspecialchars($previewUrl); ?>" alt="<?php echo htmlspecialchars($item['judul'] ?? 'Video galeri'); ?>" class="w-full h-full object-cover">
                          <?php if ($isVideoFile): ?>
                            <div class="absolute inset-0 flex items-center justify-center">
                              <span class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-[#4c3900]/50 text-white text-2xl">
                                <iconify-icon icon="lucide:play" class="w-6 h-6"></iconify-icon>
                              </span>
                            </div>
                          <?php endif; ?>
                        <?php endif; ?>
                      </div>
                      <div class="px-4 pt-3 pb-4 bg-brand-bg/90">
                        <h3 class="text-base font-semibold text-brand-dark leading-tight"><?php echo htmlspecialchars($item['judul'] ?: 'Tanpa Judul'); ?></h3>
                        <p class="mt-2 text-xs uppercase tracking-[0.24em] text-brand-muted"><?php echo htmlspecialchars($formattedDate); ?></p>
                      </div>
                    </button>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>

          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <div id="galeriModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-[#4c3900]/60 px-4 py-8">
    <div class="max-w-5xl w-full overflow-hidden rounded-[32px] bg-white shadow-2xl">
      <div class="flex items-center justify-between border-b border-brand-border/40 px-6 py-4">
        <div>
          <h3 class="font-serif text-xl font-bold text-brand-dark" id="modalTitle">Detail Foto</h3>
          <p class="text-sm text-brand-muted" id="modalSubtitle">Semua field lengkap ditampilkan.</p>
        </div>
        <button type="button" id="modalCloseBtn" class="text-brand-muted hover:text-brand-dark rounded-full bg-brand-bg px-3 py-2">Tutup</button>
      </div>
      <div class="grid lg:grid-cols-[1.25fr_0.85fr] gap-6 p-6">
        <div>
          <div class="overflow-hidden rounded-[24px] bg-brand-bg">
            <div id="modalMediaContainer" class="w-full h-[520px] bg-brand-bg"></div>
          </div>
          <div class="mt-4">
            <h4 class="font-serif text-2xl font-bold text-brand-dark" id="modalHeading"></h4>
            <p class="mt-2 text-sm text-brand-muted" id="modalMeta"></p>
          </div>
        </div>
        <div class="space-y-4">
          <div class="rounded-[24px] border border-brand-border/30 bg-brand-bg/60 p-5">
            <h5 class="text-sm uppercase tracking-[0.32em] text-brand-muted mb-3">Informasi Lengkap</h5>
            <div id="modalFields" class="space-y-3 text-sm text-brand-dark"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    (function() {
      const galleryItems = <?php echo json_encode(array_map(function($item) {
        $fileUrl = $item['file_url'] ?? '';
        return [
          'judul' => $item['judul'] ?? '',
          'jenis_galeri' => $item['jenis_galeri'] ?? '',
          'konten' => $item['konten'] ?? '',
          'file_url' => $item['file_url'] ?? '',
          'tanggal_upload' => $item['tanggal_upload'] ?? '',
          'status' => $item['status'] ?? '',
          'id' => $item['id'] ?? '',
          'created_at' => $item['created_at'] ?? '',
          'updated_at' => $item['updated_at'] ?? '',
          'is_video' => in_array(strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION)), ['mp4','webm','ogg']) || strpos(strtolower($fileUrl), 'video') !== false,
        ];
      }, $galleryItems), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

      const modal = document.getElementById('galeriModal');
      const modalCloseBtn = document.getElementById('modalCloseBtn');
      const modalTitle = document.getElementById('modalTitle');
      const modalSubtitle = document.getElementById('modalSubtitle');
      const modalHeading = document.getElementById('modalHeading');
      const modalMeta = document.getElementById('modalMeta');
      const modalFields = document.getElementById('modalFields');
      const tabFotoBtn = document.getElementById('tabFotoBtn');
      const tabVideoBtn = document.getElementById('tabVideoBtn');
      const tabSemuaBtn = document.getElementById('tabSemuaBtn');
      const galleryPhotosSection = document.getElementById('galleryPhotosSection');
      const galleryVideosSection = document.getElementById('galleryVideosSection');

      function setGalleryTab(tab) {
        const tabs = [
          { btn: tabFotoBtn, name: 'foto' },
          { btn: tabVideoBtn, name: 'video' },
          { btn: tabSemuaBtn, name: 'semua' }
        ];
        
        tabs.forEach(({ btn, name }) => {
          if (btn) {
            btn.classList.toggle('bg-brand-accent', tab === name);
            btn.classList.toggle('text-white', tab === name);
            btn.classList.toggle('bg-white', tab !== name);
            btn.classList.toggle('text-brand-dark', tab !== name);
            btn.classList.toggle('border', tab !== name);
            btn.classList.toggle('border-brand-border/30', tab !== name);
          }
        });
        
        if (galleryPhotosSection) {
          galleryPhotosSection.classList.toggle('hidden', tab === 'video');
        }
        if (galleryVideosSection) {
          galleryVideosSection.classList.toggle('hidden', tab === 'foto');
        }
      }

      if (tabFotoBtn || tabVideoBtn || tabSemuaBtn) {
        const defaultTab = tabFotoBtn ? 'foto' : (tabVideoBtn ? 'video' : 'semua');
        setGalleryTab(defaultTab);
        if (tabFotoBtn) {
          tabFotoBtn.addEventListener('click', () => setGalleryTab('foto'));
        }
        if (tabVideoBtn) {
          tabVideoBtn.addEventListener('click', () => setGalleryTab('video'));
        }
        if (tabSemuaBtn) {
          tabSemuaBtn.addEventListener('click', () => setGalleryTab('semua'));
        }
      }

      function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
          day: '2-digit', month: 'long', year: 'numeric'
        });
      }

      function showModal(item) {
        modal.classList.remove('hidden');
        modalTitle.textContent = item.judul || 'Detail Galeri';
        modalSubtitle.textContent = item.jenis_galeri ? item.jenis_galeri.toUpperCase() : (item.is_video ? 'VIDEO' : 'PHOTO');
        modalHeading.textContent = item.judul || 'Tanpa Judul';
        modalMeta.textContent = 'Diunggah: ' + formatDate(item.tanggal_upload) + ' • Status: ' + (item.status || 'Published');

        const modalMediaContainer = document.getElementById('modalMediaContainer');
        const mediaUrl = item.file_url || 'https://picsum.photos/seed/default/1200/800';
        const isVideo = !!item.is_video;
        if (isVideo) {
          const extension = mediaUrl.split('.').pop().toLowerCase();
          let sourceType = 'video/mp4';
          if (extension === 'webm') sourceType = 'video/webm';
          else if (extension === 'ogg' || extension === 'ogv') sourceType = 'video/ogg';
          const poster = 'https://picsum.photos/seed/video-' + encodeURIComponent(item.judul || 'video') + '/1200/800';
          modalMediaContainer.innerHTML = `
            <video controls class="w-full h-full object-cover bg-[#4c3900]" poster="${poster}">
              <source src="${mediaUrl}" type="${sourceType}">
              Browser Anda tidak mendukung video tag.
            </video>`;
        } else {
          modalMediaContainer.innerHTML = `<img src="${mediaUrl}" alt="${item.judul || 'Detail Foto'}" class="w-full h-full object-cover">`;
        }

        const fields = {
          'Jenis Galeri': item.jenis_galeri || '-',
          'Tanggal Upload': formatDate(item.tanggal_upload),
          'Status': item.status || '-',
          'Judul': item.judul || '-',
          'Deskripsi': item.konten || '-',
          'Updated At': item.updated_at || '-',
        };

        modalFields.innerHTML = Object.entries(fields).map(([key, value]) => {
          return '<div class="space-y-1"><div class="text-[11px] uppercase tracking-[0.28em] text-brand-muted">' + key + '</div><div class="text-sm text-brand-dark">' + String(value).replace(/\n/g, '<br>') + '</div></div>';
        }).join('');
      }

      function closeModal() {
        modal.classList.add('hidden');
      }

      document.querySelectorAll('[data-galeri-index]').forEach(button => {
        button.addEventListener('click', () => {
          const index = Number(button.getAttribute('data-galeri-index'));
          if (!Number.isNaN(index) && galleryItems[index]) {
            showModal(galleryItems[index]);
          }
        });
      });

      modalCloseBtn.addEventListener('click', closeModal);
      modal.addEventListener('click', function(event) {
        if (event.target === modal) closeModal();
      });
    })();
  </script>

  </div> <!-- glass-content-wrapper end -->
  <?php include '../components/footer.php'; ?>
</body>
</html>
