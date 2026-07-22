<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/track-visitor.php';
trackVisitor('/pages/faq');
$title = "FAQ — SLB BC KARYA SEJAHTERA " . SITE_NAME;

// Handle AJAX actions for Email Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'request_otp') {
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pertanyaan = trim($_POST['pertanyaan'] ?? '');

        if (empty($nama) || empty($email) || empty($pertanyaan)) {
            echo json_encode(['success' => false, 'message' => 'Semua field harus diisi!']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email tidak valid!']);
            exit;
        }

        // Generate 6-digit OTP
        $otp = rand(100000, 999999);
        $_SESSION['faq_otp'] = $otp;
        $_SESSION['faq_otp_time'] = time();
        $_SESSION['faq_pending_question'] = [
            'nama' => $nama,
            'email' => $email,
            'pertanyaan' => $pertanyaan
        ];

        // Write OTP to log file for easy local testing
        $otp_file = __DIR__ . '/../uploads/otp_log.txt';
        $log_dir = dirname($otp_file);
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0777, true);
        }
        @file_put_contents($otp_file, "[" . date('Y-m-d H:i:s') . "] Email: $email | OTP: $otp\n");

        // Attempt to send email
        $to = $email;
        $subject = "Kode Verifikasi FAQ - " . SITE_NAME;
        $message = "Halo $nama,\n\nKode verifikasi Anda untuk mengirim FAQ adalah: $otp\n\nKode ini berlaku selama 5 menit.";
        $headers = "From: no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n" .
                   "Reply-To: no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();
        
        @mail($to, $subject, $message, $headers);

        echo json_encode([
            'success' => true, 
            'message' => 'Kode verifikasi telah dikirim ke email Anda.',
            'otp_preview' => $otp
        ]);
        exit;
    }

    if ($_POST['action'] === 'verify_otp') {
        $user_otp = trim($_POST['otp'] ?? '');
        
        if (empty($_SESSION['faq_otp']) || empty($_SESSION['faq_pending_question'])) {
            echo json_encode(['success' => false, 'message' => 'Sesi verifikasi habis. Silakan kirim ulang kode.']);
            exit;
        }

        // Check expiration (5 minutes)
        if (time() - $_SESSION['faq_otp_time'] > 300) {
            unset($_SESSION['faq_otp'], $_SESSION['faq_otp_time'], $_SESSION['faq_pending_question']);
            echo json_encode(['success' => false, 'message' => 'Kode verifikasi telah kadaluwarsa (lebih dari 5 menit).']);
            exit;
        }

        if ($user_otp != $_SESSION['faq_otp']) {
            echo json_encode(['success' => false, 'message' => 'Kode verifikasi salah!']);
            exit;
        }

        // Insert into Supabase
        if ($supabaseConnected) {
            $pending = $_SESSION['faq_pending_question'];
            $data = [
                'pertanyaan' => $pending['pertanyaan'],
                'jawaban' => '',
                'status' => 'published',
                'urutan' => 999,
                'nama_penanya' => $pending['nama'],
                'email_penanya' => $pending['email']
            ];

            $result = supabaseInsert('faq', $data);

            if ($result['success']) {
                unset($_SESSION['faq_otp'], $_SESSION['faq_otp_time'], $_SESSION['faq_pending_question']);
                echo json_encode(['success' => true, 'message' => 'Pertanyaan Anda berhasil diverifikasi dan dikirim!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database: ' . ($result['error'] ?? 'Unknown error')]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Koneksi database tidak tersedia.']);
        }
        exit;
    }
}

// Fetch published FAQ
$faqs = [];
if ($supabaseConnected) {
    $faqResult = supabaseSelect('faq', ['status' => 'eq.published', 'order' => 'urutan.asc, created_at.asc']);
    if ($faqResult['success']) {
        $faqs = $faqResult['data'] ?? [];
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
      <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label fade-in-up">Tanya Jawab</span>
      <h1 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1] fade-in-up delay-100">Pertanyaan <em>Umum</em></h1>
    </div>
  </section>

  <!-- FAQ -->
  <section id="faq" class="py-24">
    <div class="max-w-3xl mx-auto px-6">
      <div class="glass-section">
        <div class="text-center mb-8 fade-in-up delay-100">
          <div class="mx-auto mb-6 max-w-[600px] px-6 py-4 text-center" style="background-image:url('<?php echo ASSETS_URL; ?>/images/papan_halaman.png'); background-size:cover; background-position:center; background-repeat:no-repeat;">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-4 inline-block">FAQ</span>
            <h2 class="font-serif text-3xl md:text-4xl text-white mb-6">Pertanyaan Umum</h2>
          </div>
        </div>
        <?php if (empty($faqs)): ?>
          <div class="text-center py-12">
            <iconify-icon icon="lucide:help-circle" class="text-6xl text-brand-muted/30 mb-4"></iconify-icon>
            <p class="text-brand-muted">Belum ada FAQ.</p>
          </div>
        <?php else: ?>
          <div class="space-y-3" id="faqList">
            <?php foreach ($faqs as $index => $faq): ?>
              <div class="faq-item bg-white border border-brand-border rounded-lg overflow-hidden fade-in-up delay-<?= ($index % 5 + 1) * 100 ?>">
                <button 
                  type="button" 
                  class="faq-question w-full flex items-center justify-between p-5 text-left"
                  onclick="toggleFAQ(this)"
                >
                  <h3 class="text-sm font-semibold flex-1"><?php echo htmlspecialchars($faq['pertanyaan']); ?></h3>
                  <iconify-icon icon="lucide:plus" class="w-5 h-5 text-brand-accent transition-transform duration-300"></iconify-icon>
                </button>
                <div class="faq-answer px-5">
                  <p class="text-sm text-brand-muted font-light leading-relaxed break-words whitespace-normal pb-5 pt-2"><?php echo nl2br(htmlspecialchars($faq['jawaban'] ?? '')); ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          
          <script>
            function toggleFAQ(button) {
              const icon = button.querySelector('iconify-icon');
              const answer = button.nextElementSibling;
              
              // Toggle jawaban
              answer.classList.toggle('open');
              
              // Rotasi icon
              if (!answer.classList.contains('open')) {
                icon.style.transform = 'rotate(0deg)';
                icon.setAttribute('icon', 'lucide:plus');
              } else {
                icon.style.transform = 'rotate(45deg)';
                icon.setAttribute('icon', 'lucide:x');
              }
            }
          </script>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Form Tanya Pertanyaan Baru - SECTION DIGANTI DENGAN BUTTON -->
  <section class="py-0 -mt-4 bg-transparent">
    <div class="max-w-3xl mx-auto px-6 text-center py-6">
      <h2 class="font-serif text-2xl text-brand-dark mb-3">Tidak menemukan jawaban?</h2>
      <p class="text-brand-muted text-sm mb-8">Ajukan pertanyaan Anda dan tim kami akan menjawab secepatnya.</p>
      <button
        onclick="openQuestionModal()"
        class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-brand-accent to-brand-accent/80 hover:from-brand-secondary hover:to-brand-secondary/80 text-white font-bold text-sm uppercase tracking-widest rounded-lg transition-all duration-300 shadow-md hover:shadow-lg">
        <iconify-icon icon="lucide:message-square" class="text-lg"></iconify-icon>
        Ajukan Pertanyaan
      </button>
    </div>
  </section>

  <!-- MODAL FORM PERTANYAAN -->
  <div id="questionModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-brand-accent to-brand-accent/80 px-6 md:px-8 py-6 flex items-center justify-between border-b-4 border-brand-accent/20">
        <h2 class="font-serif text-2xl text-white flex items-center gap-3">
          <iconify-icon icon="lucide:message-square" class="text-2xl"></iconify-icon>
          Ajukan Pertanyaan
        </h2>
        <button
          onclick="closeQuestionModal()"
          class="text-white hover:bg-white/20 p-2 rounded-lg transition-colors">
          <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 md:p-8 space-y-6">
        <!-- Alert Box -->
        <div id="modalAlert" class="hidden rounded-lg p-4 flex items-start gap-3 border-2">
          <iconify-icon id="modalAlertIcon" icon="lucide:alert-circle" class="w-6 h-6 flex-shrink-0 mt-0.5"></iconify-icon>
          <div>
            <p id="modalAlertText" class="font-semibold text-sm"></p>
          </div>
        </div>

        <!-- Step 1: Input Form -->
        <form id="formRequestOtp" class="space-y-5">
          <!-- Nama -->
          <div>
            <label class="block text-xs font-bold text-brand-dark mb-3 uppercase tracking-wide">Nama Lengkap</label>
            <input
              type="text"
              id="input_nama"
              placeholder="Masukkan nama Anda..."
              class="w-full px-5 py-3 border-2 border-brand-border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition-all text-sm"
              required>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-xs font-bold text-brand-dark mb-3 uppercase tracking-wide">Email</label>
            <input
              type="email"
              id="input_email"
              placeholder="Masukkan email Anda..."
              class="w-full px-5 py-3 border-2 border-brand-border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition-all text-sm"
              required>
          </div>

          <!-- Pertanyaan -->
          <div>
            <label class="block text-xs font-bold text-brand-dark mb-3 uppercase tracking-wide">Pertanyaan Anda</label>
            <textarea
              id="input_pertanyaan"
              placeholder="Tulis pertanyaan Anda dengan detail..."
              rows="6"
              class="w-full px-5 py-3 border-2 border-brand-border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition-all resize-none text-sm"
              required></textarea>
            <p class="text-xs text-brand-muted mt-2">Usahakan pertanyaan jelas dan spesifik agar mudah dijawab.</p>
          </div>

          <!-- Buttons -->
          <div class="flex gap-3 pt-4 border-t border-brand-border">
            <button
              type="button"
              onclick="closeQuestionModal()"
              class="flex-1 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-brand-dark font-bold text-sm uppercase tracking-widest rounded-lg transition-colors">
              Batal
            </button>
            <button
              type="submit"
              id="btnRequestOtp"
              class="flex-1 px-6 py-3 bg-gradient-to-r from-brand-accent to-brand-accent/80 hover:from-brand-secondary hover:to-brand-secondary/80 text-white font-bold text-sm uppercase tracking-widest rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
              <iconify-icon icon="lucide:send" class="text-base"></iconify-icon>
              Kirim Kode Verifikasi
            </button>
          </div>
        </form>

        <!-- Step 2: OTP Verification Form (Hidden Initially) -->
        <form id="formVerifyOtp" class="hidden space-y-5">
          <div class="text-center py-2">
            <iconify-icon icon="lucide:mail-check" class="text-5xl text-brand-accent mb-3"></iconify-icon>
            <p class="text-sm text-brand-muted">Masukkan 6 digit kode verifikasi yang telah dikirim ke email Anda.</p>
          </div>

          <div>
            <label class="block text-xs font-bold text-brand-dark mb-3 uppercase tracking-wide text-center">Kode Verifikasi</label>
            <input
              type="text"
              id="input_otp"
              placeholder="123456"
              maxlength="6"
              pattern="[0-9]{6}"
              class="w-full max-w-[200px] mx-auto block px-5 py-3 border-2 border-brand-border rounded-lg text-center font-mono text-2xl tracking-widest focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition-all"
              required>
          </div>

          <!-- Local testing OTP preview -->
          <div id="otpDeveloperPreview" class="hidden text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg p-3 text-center">
            <strong>Mode Developer:</strong> Kode verifikasi Anda adalah <span id="developerOtpCode" class="font-bold underline"></span>.
          </div>

          <!-- Buttons -->
          <div class="flex gap-3 pt-4 border-t border-brand-border">
            <button
              type="button"
              onclick="showStep(1)"
              class="flex-1 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-brand-dark font-bold text-sm uppercase tracking-widest rounded-lg transition-colors">
              Kembali
            </button>
            <button
              type="submit"
              id="btnVerifyOtp"
              class="flex-1 px-6 py-3 bg-gradient-to-r from-brand-accent to-brand-accent/80 hover:from-brand-secondary hover:to-brand-secondary/80 text-white font-bold text-sm uppercase tracking-widest rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
              <iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon>
              Verifikasi & Kirim
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Script untuk Modal -->
  <script>
    function openQuestionModal() {
      document.getElementById('questionModal').classList.remove('hidden');
      document.body.style.overflow = 'hidden';
      showStep(1);
    }

    function closeQuestionModal() {
      document.getElementById('questionModal').classList.add('hidden');
      document.body.style.overflow = 'auto';
      // Reset forms
      document.getElementById('formRequestOtp').reset();
      document.getElementById('formVerifyOtp').reset();
      hideAlert();
    }

    // Close modal when clicking outside
    document.getElementById('questionModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeQuestionModal();
      }
    });

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeQuestionModal();
      }
    });

    // Control Wizard Steps
    function showStep(step) {
      if (step === 1) {
        document.getElementById('formRequestOtp').classList.remove('hidden');
        document.getElementById('formVerifyOtp').classList.add('hidden');
      } else if (step === 2) {
        document.getElementById('formRequestOtp').classList.add('hidden');
        document.getElementById('formVerifyOtp').classList.remove('hidden');
      }
    }

    // Alert functions
    function showAlert(type, message) {
      const alertBox = document.getElementById('modalAlert');
      const icon = document.getElementById('modalAlertIcon');
      const text = document.getElementById('modalAlertText');

      alertBox.className = 'rounded-lg p-4 flex items-start gap-3 border-2';
      if (type === 'success') {
        alertBox.classList.add('bg-green-50', 'border-green-500', 'text-green-700');
        icon.className = 'w-6 h-6 flex-shrink-0 mt-0.5 text-green-600';
        icon.setAttribute('icon', 'lucide:check-circle');
      } else {
        alertBox.classList.add('bg-red-50', 'border-red-500', 'text-red-700');
        icon.className = 'w-6 h-6 flex-shrink-0 mt-0.5 text-red-600';
        icon.setAttribute('icon', 'lucide:alert-circle');
      }
      text.textContent = message;
      alertBox.classList.remove('hidden');
    }

    function hideAlert() {
      document.getElementById('modalAlert').classList.add('hidden');
    }

    // AJAX Form submissions
    document.getElementById('formRequestOtp').addEventListener('submit', function(e) {
      e.preventDefault();
      const nama = document.getElementById('input_nama').value;
      const email = document.getElementById('input_email').value;
      const pertanyaan = document.getElementById('input_pertanyaan').value;
      const btn = document.getElementById('btnRequestOtp');

      btn.disabled = true;
      btn.innerHTML = '<iconify-icon icon="lucide:loader-2" class="animate-spin text-base"></iconify-icon> Memproses...';
      hideAlert();

      const formData = new FormData();
      formData.append('action', 'request_otp');
      formData.append('nama', nama);
      formData.append('email', email);
      formData.append('pertanyaan', pertanyaan);

      fetch('', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="lucide:send" class="text-base"></iconify-icon> Kirim Kode Verifikasi';
        
        if (data.success) {
          showAlert('success', data.message);
          showStep(2);
          if (data.otp_preview) {
            document.getElementById('developerOtpCode').textContent = data.otp_preview;
            document.getElementById('otpDeveloperPreview').classList.remove('hidden');
          }
        } else {
          showAlert('error', data.message);
        }
      })
      .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="lucide:send" class="text-base"></iconify-icon> Kirim Kode Verifikasi';
        showAlert('error', 'Terjadi kesalahan sistem, silakan coba lagi.');
      });
    });

    document.getElementById('formVerifyOtp').addEventListener('submit', function(e) {
      e.preventDefault();
      const otp = document.getElementById('input_otp').value;
      const btn = document.getElementById('btnVerifyOtp');

      btn.disabled = true;
      btn.innerHTML = '<iconify-icon icon="lucide:loader-2" class="animate-spin text-base"></iconify-icon> Memverifikasi...';
      hideAlert();

      const formData = new FormData();
      formData.append('action', 'verify_otp');
      formData.append('otp', otp);

      fetch('', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon> Verifikasi & Kirim';

        if (data.success) {
          showAlert('success', data.message);
          setTimeout(() => {
            closeQuestionModal();
            window.location.reload();
          }, 2000);
        } else {
          showAlert('error', data.message);
        }
      })
      .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon> Verifikasi & Kirim';
        showAlert('error', 'Terjadi kesalahan sistem, silakan coba lagi.');
      });
    });
  </script>

    </div> <!-- glass-content-wrapper end -->
  <?php include '../components/footer.php'; ?>
</body>
</html>
