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
          <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">Layanan</span>
            <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Layanan Online</h2>
          </div>
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
            <p class="text-purple-100 text-xs">Pertanyaan Sudah Diajukan</p>
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
          <!-- Alert Box -->
          <div id="suratAlert" class="hidden rounded-lg p-4 flex items-start gap-3 border mb-6">
            <iconify-icon id="suratAlertIcon" icon="lucide:alert-circle" class="w-6 h-6 flex-shrink-0 mt-0.5"></iconify-icon>
            <div>
              <p id="suratAlertText" class="font-semibold text-sm"></p>
            </div>
          </div>

          <!-- Step 1: Request OTP -->
          <form id="form-surat-request-otp" class="space-y-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
              <input type="text" id="surat_nama" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Masukkan nama lengkap Anda">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
              <input type="email" id="surat_email" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="nama@email.com">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor HP/WA</label>
              <input type="tel" id="surat_no_hp" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="081234567890">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Jenis Surat</label>
              <select id="jenis_surat" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
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
              <input type="text" id="jenis_surat_lainnya" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Masukkan jenis surat lainnya">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan / Pesan</label>
              <textarea id="surat_keterangan" rows="4" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Masukkan keterangan atau pesan Anda..."></textarea>
            </div>

            <div class="pt-4 flex gap-3">
              <button type="button" onclick="closeModal('modal-surat')" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-4 px-6 rounded-lg transition-all duration-300">
                Batal
              </button>
              <button type="submit" id="btnSuratRequestOtp" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-4 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2">
                <iconify-icon icon="lucide:send"></iconify-icon> Kirim Kode Verifikasi
              </button>
            </div>
          </form>

          <!-- Step 2: Verify OTP -->
          <form id="form-surat-verify-otp" class="hidden space-y-6">
            <div class="text-center py-2">
              <iconify-icon icon="lucide:mail-check" class="text-5xl text-blue-600 mb-3"></iconify-icon>
              <p class="text-sm text-gray-600">Masukkan 6 digit kode verifikasi yang telah dikirim ke email Anda.</p>
              
              <!-- Local testing preview -->
              <div id="otpSuratDeveloperPreview" class="hidden mt-2 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-xs font-mono">
                [DEVELOPER ONLY] OTP: <span id="developerSuratOtpCode" class="font-bold text-sm"></span>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide text-center">Kode Verifikasi</label>
              <input
                type="text"
                id="input_surat_otp"
                placeholder="123456"
                maxlength="6"
                pattern="[0-9]{6}"
                class="w-full px-5 py-3 border-2 border-gray-300 rounded-lg text-center font-mono text-2xl tracking-[0.5em] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                required>
            </div>

            <div class="pt-4 flex gap-3">
              <button type="button" id="btnSuratBackToStep1" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-4 px-6 rounded-lg transition-all duration-300">
                Kembali
              </button>
              <button type="submit" id="btnSuratVerifyOtp" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-4 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2">
                <iconify-icon icon="lucide:check-circle"></iconify-icon> Verifikasi & Kirim
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
          <h3 class="text-xl font-semibold text-brand-dark">Pertanyaan Sudah Diajukan</h3>
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

    // Helper functions for Surat OTP steps
    function showSuratStep(stepNum) {
      if (stepNum === 1) {
        document.getElementById('form-surat-request-otp').classList.remove('hidden');
        document.getElementById('form-surat-verify-otp').classList.add('hidden');
      } else {
        document.getElementById('form-surat-request-otp').classList.add('hidden');
        document.getElementById('form-surat-verify-otp').classList.remove('hidden');
        document.getElementById('input_surat_otp').value = '';
      }
    }

    function showSuratAlert(type, message) {
      const alertBox = document.getElementById('suratAlert');
      const alertIcon = document.getElementById('suratAlertIcon');
      const alertText = document.getElementById('suratAlertText');

      alertText.textContent = message;
      if (type === 'success') {
        alertBox.className = "rounded-lg p-4 flex items-start gap-3 border bg-green-50 border-green-500 text-green-700 mb-6";
        alertIcon.setAttribute('icon', 'lucide:check-circle');
      } else {
        alertBox.className = "rounded-lg p-4 flex items-start gap-3 border bg-red-50 border-red-500 text-red-700 mb-6";
        alertIcon.setAttribute('icon', 'lucide:alert-circle');
      }
      alertBox.classList.remove('hidden');
    }

    function hideSuratAlert() {
      document.getElementById('suratAlert').classList.add('hidden');
    }

    // Modal Close Wrapper
    const origCloseModal = closeModal;
    closeModal = function(id) {
      origCloseModal(id);
      if (id === 'modal-surat') {
        document.getElementById('form-surat-request-otp').reset();
        document.getElementById('form-surat-verify-otp').reset();
        showSuratStep(1);
        hideSuratAlert();
      }
    }

    // Go back to step 1
    document.getElementById('btnSuratBackToStep1')?.addEventListener('click', function() {
      showSuratStep(1);
      hideSuratAlert();
    });

    // Step 1 Submit: Request OTP
    document.getElementById('form-surat-request-otp')?.addEventListener('submit', function(e) {
      e.preventDefault();
      const nama = document.getElementById('surat_nama').value;
      const email = document.getElementById('surat_email').value;
      const no_hp = document.getElementById('surat_no_hp').value;
      const jenis_surat = document.getElementById('jenis_surat').value;
      const jenis_surat_lainnya = document.getElementById('jenis_surat_lainnya').value;
      const keterangan = document.getElementById('surat_keterangan').value;
      const btn = document.getElementById('btnSuratRequestOtp');

      btn.disabled = true;
      btn.innerHTML = '<iconify-icon icon="lucide:loader-2" class="animate-spin text-base"></iconify-icon> Memproses...';
      hideSuratAlert();

      const formData = new FormData();
      formData.append('action', 'request_otp');
      formData.append('nama', nama);
      formData.append('email', email);
      formData.append('no_hp', no_hp);
      formData.append('jenis_surat', jenis_surat);
      formData.append('jenis_surat_lainnya', jenis_surat_lainnya);
      formData.append('keterangan', keterangan);

      fetch('<?= BASE_URL ?>/pages/submit-surat.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="lucide:send" class="text-base"></iconify-icon> Kirim Kode Verifikasi';

        if (data.success) {
          showSuratAlert('success', data.message);
          showSuratStep(2);
          if (data.otp_preview) {
            document.getElementById('developerSuratOtpCode').textContent = data.otp_preview;
            document.getElementById('otpSuratDeveloperPreview').classList.remove('hidden');
          }
        } else {
          showSuratAlert('error', data.message);
        }
      })
      .catch(error => {
        console.error(error);
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="lucide:send" class="text-base"></iconify-icon> Kirim Kode Verifikasi';
        showSuratAlert('error', 'Terjadi kesalahan sistem, silakan coba lagi.');
      });
    });

    // Step 2 Submit: Verify OTP & Save
    document.getElementById('form-surat-verify-otp')?.addEventListener('submit', function(e) {
      e.preventDefault();
      const otp = document.getElementById('input_surat_otp').value;
      const btn = document.getElementById('btnSuratVerifyOtp');

      btn.disabled = true;
      btn.innerHTML = '<iconify-icon icon="lucide:loader-2" class="animate-spin text-base"></iconify-icon> Memverifikasi...';
      hideSuratAlert();

      const formData = new FormData();
      formData.append('action', 'verify_otp');
      formData.append('otp', otp);

      fetch('<?= BASE_URL ?>/pages/submit-surat.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon> Verifikasi & Kirim';

        if (data.success) {
          showSuratAlert('success', data.message);
          setTimeout(() => {
            closeModal('modal-surat');
          }, 2000);
        } else {
          showSuratAlert('error', data.message);
        }
      })
      .catch(error => {
        console.error(error);
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon> Verifikasi & Kirim';
        showSuratAlert('error', 'Terjadi kesalahan sistem, silakan coba lagi.');
      });
    });
  </script>
</body>
</html>
