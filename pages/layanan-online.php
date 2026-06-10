<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$title = "Layanan Online — " . SITE_NAME;

// Get FAQs from database
$faqs = [];
if ($supabaseConnected) {
    $result = supabaseSelect('faq', ['status' => 'eq.published', 'order' => 'urutan.asc, created_at.desc', 'limit' => 1]);
    if ($result['success']) {
        $faqs = $result['data'] ?? [];
    }
}

include '../components/head.php';
?>
<body class="text-brand-dark font-sans glass-body">
  <?php include '../components/navbar.php'; ?>
  <div class="glass-content-wrapper">

    <!-- Layanan Online Section -->
    <section id="layanan" class="py-24 bg-brand-bg/50">
      <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
          <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label mb-4">Layanan</span>
          <h2 class="font-serif text-3xl md:text-4xl text-brand-dark mb-6">Layanan Online</h2>
          <div class="w-20 h-1 bg-brand-accent mx-auto"></div>
        </div>

        <div class="grid md:grid-cols-3 gap-8 mb-12">
          <!-- Tombol 1: Formulir Surat Menyurat -->
          <button onclick="openModal('modal-surat')" class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg border border-blue-700 px-8 py-3 text-center hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group">
            <div class="mb-2">
              <iconify-icon icon="lucide:mail" class="mx-auto group-hover:scale-110 transition-transform" style="font-size: 64px; color: white;"></iconify-icon>
            </div>
            <h3 class="font-serif text-lg text-white mb-1">Formulir Surat Menyurat</h3>
            <p class="text-blue-100 text-xs">Ajukan permohonan surat secara online</p>
          </button>

          <!-- Tombol 2: Layanan Pengaduan WhatsApp -->
          <button onclick="openModal('modal-whatsapp')" class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg border border-green-700 px-8 py-3 text-center hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group">
            <div class="mb-2">
              <iconify-icon icon="logos:whatsapp-icon" class="mx-auto group-hover:scale-110 transition-transform" style="font-size: 64px;"></iconify-icon>
            </div>
            <h3 class="font-serif text-lg text-white mb-1">Layanan Pengaduan WhatsApp</h3>
            <p class="text-green-100 text-xs">Sampaikan keluhan dan aspirasi</p>
          </button>

          <!-- Tombol 3: FAQ -->
          <a href="<?= BASE_URL ?>/pages/faq.php" class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg border border-purple-700 px-8 py-3 text-center hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group">
            <div class="mb-2">
              <iconify-icon icon="lucide:help-circle" class="mx-auto group-hover:scale-110 transition-transform" style="font-size: 64px; color: white;"></iconify-icon>
            </div>
            <h3 class="font-serif text-lg text-white mb-1">FAQ</h3>
            <p class="text-purple-100 text-xs">Pertanyaan yang sering diajukan</p>
          </a>
        </div>
      </div>
    </section>

    <!-- MODAL FORMULIR SURAT -->
    <div id="modal-surat" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
      <div class="bg-white rounded-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-brand-border/30 flex items-center justify-between">
          <h3 class="text-xl font-semibold text-brand-dark">Formulir Surat Menyurat</h3>
          <button onclick="closeModal('modal-surat')" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors">
            <iconify-icon icon="lucide:x" class="w-6 h-6"></iconify-icon>
          </button>
        </div>
        <div class="p-6">
          <form id="form-surat" class="space-y-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
              <input type="text" name="nama" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Masukkan nama lengkap Anda">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
              <input type="email" name="email" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="nama@email.com">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor HP/WA</label>
              <input type="tel" name="no_hp" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="081234567890">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Jenis Surat</label>
              <select id="jenis_surat" name="jenis_surat" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                <option value="">-- Pilih Jenis Surat --</option>
                <option value="Surat Keterangan">Surat Keterangan</option>
                <option value="Surat Permohonan Mutasi">Surat Permohonan Mutasi</option>
                <option value="Surat Kesediaan Menerima">Surat Kesediaan Menerima</option>
                <option value="Surat Rekomendasi">Surat Rekomendasi</option>
                <option value="Lainnya">Lainnya</option>
              </select>
            </div>

            <div id="lainnya_container" class="hidden">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Surat Lainnya</label>
              <input type="text" name="jenis_surat_lainnya" id="jenis_surat_lainnya" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Masukkan jenis surat lainnya">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan / Pesan</label>
              <textarea name="keterangan" rows="4" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Masukkan keterangan atau pesan Anda..."></textarea>
            </div>

            <div class="pt-4 flex gap-3">
              <button type="button" onclick="closeModal('modal-surat')" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-4 px-6 rounded-lg transition-all duration-300">
                Batal
              </button>
              <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-4 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2">
                <iconify-icon icon="lucide:send"></iconify-icon> Kirim
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- MODAL WHATSAPP -->
    <div id="modal-whatsapp" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
      <div class="bg-white rounded-xl w-full max-w-lg mx-4">
        <div class="p-6 border-b border-brand-border/30 flex items-center justify-between">
          <h3 class="text-xl font-semibold text-brand-dark">Layanan Pengaduan WhatsApp</h3>
          <button onclick="closeModal('modal-whatsapp')" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors">
            <iconify-icon icon="lucide:x" class="w-6 h-6"></iconify-icon>
          </button>
        </div>
        <div class="p-6">
          <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200 mb-6">
            <h4 class="font-bold text-green-800 mb-2">Nomor WhatsApp</h4>
            <p class="text-2xl font-bold text-green-700 mb-4">+62 813-4033-1431</p>
            <p class="text-sm text-green-600">Waktu Layanan: Senin - Jumat, 08.00 - 16.00 WIB</p>
          </div>
          <div class="text-center">
            <a href="https://wa.me/6281340331431?text=Halo%20admin%20SLB-C%20YPSLB%20Gemolong,%20saya%20ingin%20menyampaikan..." target="_blank" onclick="closeModal('modal-whatsapp')" class="inline-flex items-center gap-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-4 px-8 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300">
              <iconify-icon icon="lucide:message-circle" class="w-6 h-6"></iconify-icon> Hubungi Kami
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL FAQ -->
    <div id="modal-faq" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
      <div class="bg-white rounded-xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-brand-border/30 flex items-center justify-between">
          <h3 class="text-xl font-semibold text-brand-dark">Pertanyaan yang Sering Diajukan</h3>
          <button onclick="closeModal('modal-faq')" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors">
            <iconify-icon icon="lucide:x" class="w-6 h-6"></iconify-icon>
          </button>
        </div>
        <div class="p-6">
          <?php if (empty($faqs)): ?>
            <div class="text-center py-12">
              <iconify-icon icon="lucide:inbox" class="w-12 h-12 text-gray-400 mx-auto mb-4"></iconify-icon>
              <p class="text-gray-500">Belum ada FAQ yang tersedia.</p>
            </div>
          <?php else: ?>
            <div class="space-y-4" id="accordion-faq-modal">
              <?php foreach ($faqs as $index => $faq): ?>
                <div class="faq-item bg-white rounded-xl shadow-sm border border-brand-border/30 overflow-hidden">
                  <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between hover:bg-brand-bg/30 transition-colors">
                    <span class="font-semibold text-lg text-brand-dark"><?php echo htmlspecialchars($faq['pertanyaan']); ?></span>
                    <iconify-icon icon="lucide:chevron-down" class="faq-icon w-6 h-6 text-brand-accent transition-transform"></iconify-icon>
                  </button>
                  <div class="faq-content hidden px-6 pb-5 text-gray-600 bg-brand-bg/10">
                    <?php echo nl2br(htmlspecialchars($faq['jawaban'])); ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div> <!-- glass-content-wrapper end -->
  <?php include '../components/footer.php'; ?>

  <script>
    function openModal(id) {
      const modal = document.getElementById(id);
      if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
      }
    }

    function closeModal(id) {
      const modal = document.getElementById(id);
      if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
      }
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
      if (event.target.classList.contains('fixed') && event.target.classList.contains('inset-0')) {
        event.target.classList.add('hidden');
        document.body.style.overflow = 'auto';
      }
    });

    // FAQ Accordion Toggle
    document.querySelectorAll('.faq-toggle').forEach(button => {
      button.addEventListener('click', function() {
        const content = this.nextElementSibling;
        const icon = this.querySelector('.faq-icon');

        content.classList.toggle('hidden');
        icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
      });
    });

    // Surat Form - Show/Hide "Lainnya" field
    const jenisSuratSelect = document.getElementById('jenis_surat');
    if (jenisSuratSelect) {
      jenisSuratSelect.addEventListener('change', function() {
        const lainnyaContainer = document.getElementById('lainnya_container');
        if (this.value === 'Lainnya') {
          lainnyaContainer.classList.remove('hidden');
        } else {
          lainnyaContainer.classList.add('hidden');
        }
      });
    }

    // Surat Form Submit - save to Supabase via server endpoint
    const formSurat = document.getElementById('form-surat');
    if (formSurat) {
      formSurat.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        // Determine jenis surat final
        if (formData.get('jenis_surat') === 'Lainnya') {
          formData.set('jenis_surat', formData.get('jenis_surat_lainnya') || 'Lainnya');
        }

        fetch('<?= BASE_URL ?>/pages/submit-surat.php', {
          method: 'POST',
          body: formData,
        }).then(r => r.json()).then(resp => {
          if (resp.success) {
            alert('Permohonan surat berhasil dikirim. Terima kasih.');
            closeModal('modal-surat');
            formSurat.reset();
          } else {
            alert('Gagal mengirim: ' + (resp.message || 'Unknown'));
          }
        }).catch(err => {
          console.error(err);
          alert('Terjadi kesalahan, coba lagi.');
        });
      });
    }
  </script>
</body>
</html>
