
<?php
if (!isset($hero) || empty($hero)) {
    if (!isset($supabaseConnected)) {
        require_once __DIR__ . '/../includes/db.php';
    }
    if ($supabaseConnected) {
        $heroResult = supabaseSelect('hero', ['id' => 'eq.1', 'limit' => 1]);
        if ($heroResult['success'] && !empty($heroResult['data'])) {
            $hero = array_merge($hero ?? [], $heroResult['data'][0]);
        }
    }
}
$mottoText = htmlspecialchars(trim($hero['motto'] ?? ''));
// Pastikan data profil sekolah tersedia (beberapa halaman mungkin sudah memuat db.php)
if (!isset($profilSekolah) || empty($profilSekolah)) {
  if (!isset($supabaseConnected)) {
    require_once __DIR__ . '/../includes/db.php';
  }
}
?>
  <!-- ========== FOOTER ========== -->
  <footer class="bg-[#f97316] pt-8 pb-8">
    <div class="max-w-7xl mx-auto px-6">
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
        <div class="lg:border-r lg:border-white/10 lg:pr-8">
          <?php
            $footerLogo = !empty($profilSekolah['logo_url']) ? $profilSekolah['logo_url'] : BASE_URL . '/assets/images/JATENG JR.jpg';
            $footerSchoolName = mb_strtoupper(trim($profilSekolah['nama_sekolah'] ?? SITE_NAME), 'UTF-8');
          ?>
          <div class="flex items-center gap-3 mb-6"><a href="<?= BASE_URL ?>/admin/index.php" class="flex items-center gap-3"><img src="<?php echo htmlspecialchars($footerLogo); ?>" alt="Logo Sekolah" class="w-14 h-14 rounded-full object-cover" onerror="this.src='https://picsum.photos/seed/logo/100/100'"><span class="font-serif text-lg font-semibold text-white"><?php echo htmlspecialchars($footerSchoolName); ?></span></a></div>
          <p class="text-white/90 text-sm font-light leading-relaxed mb-6"><?php echo $mottoText; ?></p>
          <div class="flex items-center gap-3 mb-8">
            <?php if (!empty($profilSekolah['instagram'])): ?>
              <a href="<?php echo htmlspecialchars($profilSekolah['instagram']); ?>" target="_blank" class="w-9 h-9 rounded-full border border-white/40 flex items-center justify-center text-white/90 hover:bg-white/20 hover:text-white transition-colors"><iconify-icon icon="lucide:instagram" class="text-xs"></iconify-icon></a>
            <?php endif; ?>
            <?php if (!empty($profilSekolah['facebook'])): ?>
              <a href="<?php echo htmlspecialchars($profilSekolah['facebook']); ?>" target="_blank" class="w-9 h-9 rounded-full border border-white/40 flex items-center justify-center text-white/90 hover:bg-white/20 hover:text-white transition-colors"><iconify-icon icon="lucide:facebook" class="text-xs"></iconify-icon></a>
            <?php endif; ?>
            <?php if (!empty($profilSekolah['youtube'])): ?>
              <a href="<?php echo htmlspecialchars($profilSekolah['youtube']); ?>" target="_blank" class="w-9 h-9 rounded-full border border-white/40 flex items-center justify-center text-white/90 hover:bg-white/20 hover:text-white transition-colors"><iconify-icon icon="lucide:youtube" class="text-xs"></iconify-icon></a>
            <?php endif; ?>
            <?php if (!empty($profilSekolah['tiktok'])): ?>
              <a href="<?php echo htmlspecialchars($profilSekolah['tiktok']); ?>" target="_blank" class="w-9 h-9 rounded-full border border-white/40 flex items-center justify-center text-white/90 hover:bg-white/20 hover:text-white transition-colors text-xs font-bold">T</a>
            <?php endif; ?>
          </div>
          <?php
            // Bangun URL peta dari beberapa sumber: maps_url (embed atau koordinat), latitude/longitude, atau alamat
            $footerMapUrl = '';
            $mapsUrl = trim($profilSekolah['maps_url'] ?? '');
            if (!empty($mapsUrl)) {
              if (strpos($mapsUrl, 'maps/embed') !== false || strpos($mapsUrl, 'output=embed') !== false) {
                $footerMapUrl = $mapsUrl;
              } elseif (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $mapsUrl, $coords)) {
                $footerMapUrl = 'https://maps.google.com/maps?q=' . urlencode($coords[1] . ',' . $coords[2]) . '&output=embed';
              } else {
                $address = !empty($profilSekolah['alamat']) ? $profilSekolah['alamat'] : 'SLB BC KARYA SEJAHTERA';
                $footerMapUrl = 'https://maps.google.com/maps?q=' . urlencode($address) . '&output=embed';
              }
            } elseif (!empty($profilSekolah['latitude']) && !empty($profilSekolah['longitude'])) {
              $footerMapUrl = 'https://maps.google.com/maps?q=' . urlencode($profilSekolah['latitude'] . ',' . $profilSekolah['longitude']) . '&output=embed';
            } else {
              $address = !empty($profilSekolah['alamat']) ? $profilSekolah['alamat'] : 'SLB BC KARYA SEJAHTERA';
              $footerMapUrl = 'https://maps.google.com/maps?q=' . urlencode($address) . '&output=embed';
            }
          ?>
          <?php
            // Jika latitude/longitude tersedia, tampilkan OpenStreetMap embed seperti di halaman Kontak
            $lat = isset($profilSekolah['latitude']) ? trim((string)$profilSekolah['latitude']) : '';
            $lon = isset($profilSekolah['longitude']) ? trim((string)$profilSekolah['longitude']) : '';
            $osmDisplayed = false;
            if ($lat !== '' && $lon !== '' && is_numeric($lat) && is_numeric($lon)) {
              $latf = floatval($lat);
              $lonf = floatval($lon);
              $delta = 0.01; // area sekitar marker
              $lon_min = $lonf - $delta; $lat_min = $latf - $delta; $lon_max = $lonf + $delta; $lat_max = $latf + $delta;
              $bbox = rawurlencode($lon_min) . '%2C' . rawurlencode($lat_min) . '%2C' . rawurlencode($lon_max) . '%2C' . rawurlencode($lat_max);
              $marker = rawurlencode($latf) . '%2C' . rawurlencode($lonf);
              $osmSrc = "https://www.openstreetmap.org/export/embed.html?bbox={$bbox}&layer=mapnik&marker={$marker}";
              ?>
              <div class="rounded-3xl overflow-hidden border border-white/10 bg-black/5" style="min-height:150px; height:150px;">
                <iframe src="<?php echo htmlspecialchars($osmSrc); ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
              </div>
              <?php
              $osmDisplayed = true;
            }

            // Jika OSM tidak bisa ditampilkan, gunakan footerMapUrl (Google Maps/embed/address)
            if (!$osmDisplayed && !empty($footerMapUrl)) {
          ?>
            <div class="rounded-3xl overflow-hidden border border-white/10 bg-black/5" style="min-height:150px; height:150px;">
              <iframe src="<?php echo htmlspecialchars($footerMapUrl); ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
          <?php } ?>
        </div>
        <div class="lg:border-r lg:border-white/20 lg:pr-8">
          <h4 class="text-white font-bold tracking-[0.2em] uppercase text-brand-label mb-6">Navigasi</h4>
          <div class="grid grid-cols-2 gap-4">
            <ul class="space-y-3">
              <li><a href="<?= BASE_URL ?>/index.php#profil" class="text-sm text-white/90 hover:text-[#fef3c7] transition-colors">Profil</a></li>
              <li><a href="<?= BASE_URL ?>/index.php#program" class="text-sm text-white/90 hover:text-[#fef3c7] transition-colors">Program</a></li>
              <li><a href="<?= BASE_URL ?>/index.php#fasilitas" class="text-sm text-white/90 hover:text-[#fef3c7] transition-colors">Fasilitas</a></li>
            </ul>
            <ul class="space-y-3">
              <li><a href="<?= BASE_URL ?>/index.php#prestasi" class="text-sm text-white/90 hover:text-[#fef3c7] transition-colors">Prestasi</a></li>
              <li><a href="<?= BASE_URL ?>/pages/galeri.php" class="text-sm text-white/90 hover:text-[#fef3c7] transition-colors">Galeri</a></li>
              <li><a href="<?= BASE_URL ?>/pages/pengumuman.php" class="text-sm text-white/90 hover:text-[#fef3c7] transition-colors">Pengumuman</a></li>
            </ul>
          </div>
          <div class="grid grid-cols-2 gap-4 mt-4">
            <ul class="space-y-3">
              <li><a href="<?= BASE_URL ?>/pages/download.php" class="text-sm text-white/90 hover:text-[#fef3c7] transition-colors">Download</a></li>
              <li><a href="<?= BASE_URL ?>/pages/anggaran.php" class="text-sm text-white/90 hover:text-[#fef3c7] transition-colors">Anggaran & Belanja</a></li>
            </ul>
            <ul class="space-y-3">
              <li><a href="<?= BASE_URL ?>/pages/faq.php" class="text-sm text-white/90 hover:text-[#fef3c7] transition-colors">FAQ</a></li>
              <li><a href="<?= BASE_URL ?>/pages/kontak.php" class="text-sm text-white/90 hover:text-[#fef3c7] transition-colors">Kontak</a></li>
            </ul>
          </div>
        </div>
        <div class="lg:border-r lg:border-white/20 lg:pr-8">
          <h4 class="text-white font-bold tracking-[0.2em] uppercase text-brand-label mb-6">Layanan</h4>
          <ul class="space-y-3">
            <li><a href="<?= BASE_URL ?>/pages/ppdb.php" class="text-sm text-white/90 hover:text-[#fef3c7] transition-colors">PPDB Online</a></li>
            <li><a href="<?= BASE_URL ?>/pages/layanan-online.php?service=surat" class="text-sm text-white/90 hover:text-[#fef3c7] transition-colors">E-Learning</a></li>
            <li><a href="<?= BASE_URL ?>/pages/layanan-online.php?service=surat" class="text-sm text-white/90 hover:text-[#fef3c7] transition-colors">Perpustakaan Digital</a></li>
            <li><a href="<?= BASE_URL ?>/pages/layanan-online.php?service=surat" class="text-sm text-white/90 hover:text-[#fef3c7] transition-colors">Portal Orang Tua</a></li>
            <li><a href="<?= BASE_URL ?>/pages/layanan-online.php?service=surat" class="text-sm text-white/90 hover:text-[#fef3c7] transition-colors">Alumni Connect</a></li>
          </ul>
        </div>
        <div>
          <h4 class="text-white font-bold tracking-[0.2em] uppercase text-brand-label mb-6">Kontak</h4>
          <ul class="space-y-3 mb-6">
            <li class="flex items-start gap-2"><iconify-icon icon="lucide:map-pin" class="text-[#fef3c7] text-sm mt-0.5"></iconify-icon><span class="text-sm text-white/90 font-light"><?php echo htmlspecialchars($profilSekolah['alamat']); ?></span></li>
            <li class="flex items-start gap-2"><iconify-icon icon="lucide:phone" class="text-[#fef3c7] text-sm mt-0.5"></iconify-icon><a href="tel:<?php echo preg_replace('/[^0-9]/', '', $profilSekolah['telepon']); ?>" class="text-sm text-white/90 font-light hover:text-[#fef3c7] transition-colors"><?php echo htmlspecialchars($profilSekolah['telepon']); ?></a></li>
            <li class="flex items-start gap-2"><iconify-icon icon="lucide:message-circle" class="text-[#fef3c7] text-sm mt-0.5"></iconify-icon><a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $profilSekolah['telepon']); ?>" target="_blank" class="text-sm text-white/90 font-light hover:text-[#fef3c7] transition-colors">WhatsApp <?php echo htmlspecialchars($profilSekolah['telepon']); ?></a></li>
            <li class="flex items-start gap-2"><iconify-icon icon="lucide:mail" class="text-[#fef3c7] text-sm mt-0.5"></iconify-icon><a href="mailto:<?php echo htmlspecialchars($profilSekolah['email']); ?>" class="text-sm text-white/90 font-light hover:text-[#fef3c7] transition-colors"><?php echo htmlspecialchars($profilSekolah['email']); ?></a></li>
          </ul>
          <!-- Developer Info Removed -->
        </div>
      </div>
      <div class="border-t border-white/20 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-xs text-white/70 font-light">© 2026 <?php echo htmlspecialchars(mb_strtoupper(trim($profilSekolah['nama_sekolah'] ?? SITE_NAME), 'UTF-8')); ?>. Seluruh hak cipta dilindungi.</p>
        <div class="flex items-center gap-6">
          <a href="#" onclick="openLegalModal('privacy'); return false;" class="text-xs text-white/70 hover:text-[#fef3c7] transition-colors">Kebijakan Privasi</a>
          <a href="#" onclick="openLegalModal('terms'); return false;" class="text-xs text-white/70 hover:text-[#fef3c7] transition-colors">Syarat & Ketentuan</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- BACK TO TOP -->
  <button id="backToTop" class="fixed bottom-8 right-8 w-12 h-12 bg-[#f97316] hover:bg-[#ea580c] text-white rounded-full shadow-lg flex items-center justify-center transition-all duration-300 opacity-0 translate-y-4 z-50">
    <iconify-icon icon="lucide:arrow-up" class="text-lg"></iconify-icon>
  </button>

  <!-- LEGAL MODALS -->
  <div id="modalLegalPrivacy" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden px-4 py-6">
    <div class="relative w-full max-w-2xl bg-white rounded-3xl overflow-hidden shadow-2xl">
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <div>
          <h3 class="text-xl font-semibold text-slate-900">Kebijakan Privasi</h3>
          <p class="text-sm text-slate-500">Informasi penggunaan data dan privasi pengguna.</p>
        </div>
        <button onclick="closeLegalModal('privacy')" class="text-slate-500 hover:text-slate-900 transition-colors">
          <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
        </button>
      </div>
      <div class="p-6 space-y-4 text-sm leading-relaxed text-slate-700">
        <p>SLB BC KARYA SEJAHTERA berkomitmen menjaga privasi pengunjung website. Data pribadi hanya digunakan untuk tujuan layanan, pendaftaran PPDB, dan komunikasi resmi.</p>
        <p>Informasi yang dikumpulkan dapat mencakup nama, alamat email, nomor telepon, dan data pendaftaran lainnya. Data tidak akan dibagikan ke pihak ketiga tanpa persetujuan pengguna kecuali diwajibkan oleh hukum.</p>
        <p>Untuk detail lebih lengkap, silakan hubungi kami melalui halaman <a href="<?= BASE_URL ?>/pages/kontak.php" class="text-[#f8d468] hover:underline">Kontak</a>.</p>
      </div>
      <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-slate-50">
        <button type="button" onclick="closeLegalModal('privacy')" class="px-5 py-3 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-all">Tutup</button>
      </div>
    </div>
  </div>

  <div id="modalLegalTerms" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden px-4 py-6">
    <div class="relative w-full max-w-2xl bg-white rounded-3xl overflow-hidden shadow-2xl">
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <div>
          <h3 class="text-xl font-semibold text-slate-900">Syarat & Ketentuan</h3>
          <p class="text-sm text-slate-500">Persyaratan penggunaan layanan website dan proses pendaftaran.</p>
        </div>
        <button onclick="closeLegalModal('terms')" class="text-slate-500 hover:text-slate-900 transition-colors">
          <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
        </button>
      </div>
      <div class="p-6 space-y-4 text-sm leading-relaxed text-slate-700">
        <p>Dengan menggunakan website dan layanan SLB BC KARYA SEJAHTERA, Anda menyetujui bahwa informasi yang diberikan harus akurat dan valid. Semua proses pendaftaran akan mengikuti ketentuan resmi sekolah.</p>
        <p>SLB BC KARYA SEJAHTERA berhak menolak pendaftaran apabila data tidak lengkap atau tidak memenuhi syarat. Penggunaan website harus sesuai dengan hukum dan norma yang berlaku.</p>
        <p>Lebih lanjut, silakan baca informasi resmi kami atau hubungi tim sekolah untuk klarifikasi.</p>
      </div>
      <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-slate-50">
        <button type="button" onclick="closeLegalModal('terms')" class="px-5 py-3 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-all">Tutup</button>
      </div>
    </div>
  </div>

  <!-- LIGHTBOX -->
  <div id="lightbox" class="fixed inset-0 z-[100] bg-black/90 hidden items-center justify-center p-6" onclick="closeLightbox()">
    <button class="absolute top-6 right-6 text-white/70 hover:text-white transition-colors" onclick="closeLightbox()"><iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon></button>
    <img id="lightboxImg" src="" class="max-w-full max-h-[85vh] object-contain rounded-lg" alt="">
  </div>

  <!-- TOAST -->
  <div id="toast" class="toast"></div>

  <!-- ========== JAVASCRIPT ========== -->
  <script>
    // ===== Navbar Scroll =====
    window.addEventListener('scroll', () => {
      const nb = document.getElementById('navbar');
      if(!nb) return;
      if (window.scrollY > 80) nb.classList.add('bg-brand-bg/95','shadow-md','border-b','border-brand-border/50');
      else nb.classList.remove('bg-brand-bg/95','shadow-md','border-b','border-brand-border/50');
    });

    // ===== Mobile Menu =====
    const menuBtn = document.getElementById('menuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    let menuOpen = false;
    if(menuBtn && mobileMenu) {
      menuBtn.addEventListener('click', () => {
        menuOpen = !menuOpen;
        mobileMenu.classList.toggle('hidden');
        document.getElementById('menuIcon').setAttribute('icon', menuOpen ? 'lucide:x' : 'lucide:menu');
      });
      document.querySelectorAll('.mobile-link').forEach(l => l.addEventListener('click', () => {
        mobileMenu.classList.add('hidden'); menuOpen = false;
        document.getElementById('menuIcon').setAttribute('icon', 'lucide:menu');
      }));
    }

    // ===== Back to Top =====
    window.addEventListener('scroll', () => {
      const b = document.getElementById('backToTop');
      if(!b) return;
      if (window.scrollY > 600) { b.classList.remove('opacity-0','translate-y-4'); b.classList.add('opacity-100','translate-y-0'); }
      else { b.classList.add('opacity-0','translate-y-4'); b.classList.remove('opacity-100','translate-y-0'); }
    });
    const btt = document.getElementById('backToTop');
    if(btt) btt.addEventListener('click', () => window.scrollTo({top:0,behavior:'smooth'}));

    // ===== Legal Modal =====
    function openLegalModal(type) {
      const modal = document.getElementById(type === 'terms' ? 'modalLegalTerms' : 'modalLegalPrivacy');
      if (modal) modal.classList.remove('hidden');
    }

    function closeLegalModal(type) {
      const modal = document.getElementById(type === 'terms' ? 'modalLegalTerms' : 'modalLegalPrivacy');
      if (modal) modal.classList.add('hidden');
    }

    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeLegalModal('privacy');
        closeLegalModal('terms');
      }
    });

    // ===== Counter =====
    function animateCounters() {
      document.querySelectorAll('.counter').forEach(el => {
        const t = +el.dataset.target; const d = 2000; const s = Date.now();
        const step = () => { const p = Math.min((Date.now()-s)/d,1); const e = 1-Math.pow(1-p,3);
          el.textContent = Math.floor(t*e).toLocaleString(); if(p<1) requestAnimationFrame(step); };
        step();
      });
    }
    const cObs = new IntersectionObserver(entries => {
      entries.forEach(e => { if(e.isIntersecting){animateCounters();cObs.disconnect();} });
    }, {threshold:0.3});
    const cEl = document.querySelector('.counter');
    if(cEl) cObs.observe(cEl.closest('section'));

    // ===== Testimoni =====
    const tData = [
      {text:'"SLB BC KARYA SEJAHTERA bukan sekadar tempat belajar, tapi rumah kedua yang membentuk karakter dan masa depan saya."',name:'Aisyah Putri',role:'Alumni 2023 — Mahasiswa UI',img:'https://picsum.photos/seed/alm1/80/80.jpg'},
      {text:'"Program olimpiade membawa saya sampai ke kompetisi internasional dan akhirnya mendapat beasiswa penuh di ITB."',name:'Rizky Pratama',role:'Alumni 2022 — Mahasiswa ITB',img:'https://picsum.photos/seed/alm2/80/80.jpg'},
      {text:'"Sebagai orang tua, saya sangat bersyukur anak saya bersekolah di sini. Komunikasi sekolah-orang tua sangat baik."',name:'Ibu Diana Sari',role:'Orang Tua Siswa Kelas XI',img:'https://picsum.photos/seed/par1/80/80.jpg'},
      {text:'"Fasilitas lengkap dan kegiatan ekstrakurikuler beragam membuat saya bisa mengembangkan minat di bidang robotika."',name:'Dimas Arya Putra',role:'Siswa Kelas XII MIPA 1',img:'https://picsum.photos/seed/std1/80/80.jpg'}
    ];
    let cT = 0;
    const ttText = document.getElementById('testimoniText');
    if(ttText) {
      function setT(i) {
        cT = i; const d = tData[i];
        ttText.style.opacity='0';
        setTimeout(()=>{
          ttText.textContent=d.text;
          const tName = document.getElementById('testimoniName');
          const tRole = document.getElementById('testimoniRole');
          const tImg = document.getElementById('testimoniImg');
          if(tName) tName.textContent=d.name;
          if(tRole) tRole.textContent=d.role;
          if(tImg) tImg.src=d.img;
          ttText.style.opacity='1';
        },300);
        renderDots();
      }
      function renderDots() {
        const c = document.getElementById('testimoniDots');
        if(!c) return;
        c.innerHTML='';
        tData.forEach((_,i)=>{const d=document.createElement('button');
          d.className=`w-2 h-2 rounded-full transition-all duration-300 ${i===cT?'bg-brand-accent w-6':'bg-white/30'}`;
          d.addEventListener('click',()=>setT(i));c.appendChild(d);});
      }
      function changeTestimoni(dir){let n=cT+dir;if(n<0)n=tData.length-1;if(n>=tData.length)n=0;setT(n);}
      ttText.style.transition='opacity 0.3s ease';
      setT(0);
      let tInt = setInterval(()=>changeTestimoni(1),6000);
      const tContainer = document.getElementById('testimoniContainer');
      if(tContainer) {
        tContainer.addEventListener('mouseenter',()=>clearInterval(tInt));
        tContainer.addEventListener('mouseleave',()=>{tInt=setInterval(()=>changeTestimoni(1),6000);});
      }
    }

    // ===== Lightbox =====
    function openLightbox(el) {
      const img = el.querySelector('img');
      const lbImg = document.getElementById('lightboxImg');
      const lb = document.getElementById('lightbox');
      if(lbImg && lb) {
        lbImg.src = img.src;
        lb.classList.remove('hidden'); lb.classList.add('flex');
      }
    }
    function closeLightbox() {
      const lb = document.getElementById('lightbox');
      if(lb) {
        lb.classList.add('hidden'); lb.classList.remove('flex');
      }
    }
    document.addEventListener('keydown', e => { if(e.key==='Escape') closeLightbox(); });

    // ===== FAQ =====
    function toggleFaq(btn) {
      const item = btn.closest('.faq-item');
      const answer = item.querySelector('.faq-answer');
      const wasOpen = item.classList.contains('active');
      document.querySelectorAll('.faq-item').forEach(i => { i.classList.remove('active'); i.querySelector('.faq-answer').classList.remove('open'); });
      if (!wasOpen) { item.classList.add('active'); answer.classList.add('open'); }
    }

    // ===== Prestasi Filter =====
    document.querySelectorAll('.prestasi-filter').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.prestasi-filter').forEach(b => {
          b.classList.remove('bg-brand-accent','text-white'); b.classList.add('bg-white/5','text-white/60','border','border-white/10');
        });
        btn.classList.add('bg-brand-accent','text-white'); btn.classList.remove('bg-white/5','text-white/60','border','border-white/10');
        const f = btn.dataset.filter;
        document.querySelectorAll('.prestasi-item').forEach(item => {
          if(f==='all' || item.dataset.cat===f) { item.style.display=''; item.style.opacity='0';
            setTimeout(()=>item.style.opacity='1',50); }
          else item.style.display='none';
        });
      });
    });

    // ===== Calendar =====
    let calMonth = 0, calYear = 2025;
    const events = [15,20,27]; // Event days for current month
    const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const calGrid = document.getElementById('calGrid');
    if(calGrid) {
      function renderCal() {
        const calTitle = document.getElementById('calTitle');
        if(calTitle) calTitle.textContent = months[calMonth]+' '+calYear;
        const first = new Date(calYear,calMonth,1).getDay();
        const days = new Date(calYear,calMonth+1,0).getDate();
        calGrid.innerHTML = '';
        for(let i=0;i<first;i++) calGrid.innerHTML += '<div></div>';
        for(let d=1;d<=days;d++) {
          const isToday = d===15 && calMonth===0 && calYear===2025;
          const hasEv = events.includes(d) && calMonth===0 && calYear===2025;
          calGrid.innerHTML += `<div class="calendar-day text-center py-2.5 rounded text-sm cursor-pointer transition-colors duration-150 relative ${isToday?'active':''} ${hasEv?'has-event':''}">${d}</div>`;
        }
      }
      const calPrev = document.getElementById('calPrev');
      const calNext = document.getElementById('calNext');
      if(calPrev) calPrev.addEventListener('click',()=>{calMonth--;if(calMonth<0){calMonth=11;calYear--;}renderCal();});
      if(calNext) calNext.addEventListener('click',()=>{calMonth++;if(calMonth>11){calMonth=0;calYear++;}renderCal();});
      renderCal();
    }

    // ===== PPDB Multi-Step =====
    function showStep(n) {
      document.querySelectorAll('.ppdb-panel').forEach(p=>p.classList.add('hidden'));
      const stepPanel = document.getElementById('ppdbStep'+n);
      if(stepPanel) stepPanel.classList.remove('hidden');
      document.querySelectorAll('.ppdb-step').forEach((s,i) => {
        s.classList.remove('completed','current','pending');
        if(i+1 < n) s.classList.add('completed');
        else if(i+1 === n) s.classList.add('current');
        else s.classList.add('pending');
      });
    }
    const ppdbF1 = document.getElementById('ppdbForm1');
    if(ppdbF1) ppdbF1.addEventListener('submit', e => { e.preventDefault(); showStep(2); });
    const ppdbF2 = document.getElementById('ppdbForm2');
    if(ppdbF2) ppdbF2.addEventListener('submit', e => { e.preventDefault(); showStep(3); });
    const submitPPDB = document.getElementById('submitPPDB');
    if(submitPPDB) {
      submitPPDB.addEventListener('click', () => {
        const agreeCheck = document.getElementById('agreeCheck');
        if(agreeCheck && !agreeCheck.checked) { showToast('Harap centang pernyataan persetujuan'); return; }
        const num = 'PPDB-2025-' + String(Math.floor(Math.random()*99999)).padStart(5,'0');
        const regNum = document.getElementById('regNumber');
        if(regNum) regNum.textContent = num;
        document.querySelectorAll('.ppdb-panel').forEach(p=>p.classList.add('hidden'));
        const ppdbSuccess = document.getElementById('ppdbSuccess');
        if(ppdbSuccess) ppdbSuccess.classList.remove('hidden');
        showToast('Pendaftaran berhasil dikirim!');
      });
    }

    // ===== Contact Form =====
    const contactForm = document.getElementById('contactForm');
    if(contactForm) {
      contactForm.addEventListener('submit', e => {
        e.preventDefault();
        const m = document.getElementById('formMsg');
        if(m) {
          m.textContent = '✓ Pesan berhasil dikirim! Kami akan segera merespon.';
          m.classList.remove('hidden');
          e.target.reset();
          setTimeout(()=>m.classList.add('hidden'),5000);
        }
      });
    }

    // ===== Toast =====
    function showToast(msg) {
      const t = document.getElementById('toast');
      if(t) {
        t.textContent = msg; t.classList.add('show');
        setTimeout(()=>t.classList.remove('show'),4000);
      }
    }

    // ===== Scroll Reveal =====
    const rObs = new IntersectionObserver(entries => {
      entries.forEach(e => { if(e.isIntersecting){e.target.style.opacity='1';e.target.style.transform='translateY(0)';} });
    }, {threshold:0.1});
    document.querySelectorAll('section > div').forEach(el => {
      el.style.opacity='0'; el.style.transform='translateY(20px)';
      el.style.transition='opacity 0.8s ease, transform 0.8s ease';
      rObs.observe(el);
    });

    // ===== Active Nav =====
    window.addEventListener('scroll', () => {
      let cur = '';
      document.querySelectorAll('section[id]').forEach(s => { if(window.scrollY >= s.offsetTop-120) cur=s.id; });
      document.querySelectorAll('nav a[href^="#"]').forEach(l => {
        l.classList.remove('text-brand-accent');
        if(l.getAttribute('href').includes('#'+cur)) l.classList.add('text-brand-accent');
      });
    });
  </script>
</body>
</html>
