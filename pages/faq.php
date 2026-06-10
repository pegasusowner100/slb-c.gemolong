<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$title = "FAQ — " . SITE_NAME;

// Handle pertanyaan baru
$error_msg = '';
$success_msg = '';

// Check if redirected from form submit
if (isset($_GET['success'])) {
    $success_msg = 'Terima kasih! Pertanyaan Anda telah kami terima dan akan dijawab segera.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_question') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pertanyaan = trim($_POST['pertanyaan'] ?? '');

    if (empty($nama) || empty($email) || empty($pertanyaan)) {
        $error_msg = 'Semua field harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'Email tidak valid!';
    } else {
        if ($supabaseConnected) {
            $data = [
                'pertanyaan' => $pertanyaan,
                'jawaban' => '',
                'status' => 'published',
                'urutan' => 999,
                'nama_penanya' => $nama,
                'email_penanya' => $email
            ];

            // Debug log
            error_log('DEBUG FAQ: Inserting data - ' . json_encode($data));

            $result = supabaseInsert('faq', $data);

            // Debug log result
            error_log('DEBUG FAQ: Insert result - ' . json_encode($result));

            if ($result['success']) {
                $success_msg = 'Terima kasih! Pertanyaan Anda telah kami terima dan akan dijawab segera.';
                // Clear form by reloading page
                header('Location: faq.php?success=1');
                exit;
            } else {
                $error_msg = 'Gagal mengirim pertanyaan: ' . (isset($result['error']) ? $result['error'] : 'Unknown error');
            }
        } else {
            $error_msg = 'Koneksi database tidak tersedia.';
        }
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
        <?php if ($success_msg): ?>
          <div class="bg-green-50 border-2 border-green-500 rounded-lg p-4 flex items-start gap-3">
            <iconify-icon icon="lucide:check-circle" class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5"></iconify-icon>
            <div>
              <p class="text-green-700 font-semibold text-sm"><?php echo htmlspecialchars($success_msg); ?></p>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
          <div class="bg-red-50 border-2 border-red-500 rounded-lg p-4 flex items-start gap-3">
            <iconify-icon icon="lucide:alert-circle" class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5"></iconify-icon>
            <div>
              <p class="text-red-700 font-semibold text-sm"><?php echo htmlspecialchars($error_msg); ?></p>
            </div>
          </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-5">
          <input type="hidden" name="action" value="submit_question">

          <!-- Nama -->
          <div>
            <label class="block text-xs font-bold text-brand-dark mb-3 uppercase tracking-wide">Nama Lengkap</label>
            <input
              type="text"
              name="nama"
              placeholder="Masukkan nama Anda..."
              class="w-full px-5 py-3 border-2 border-brand-border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition-all text-sm"
              required>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-xs font-bold text-brand-dark mb-3 uppercase tracking-wide">Email</label>
            <input
              type="email"
              name="email"
              placeholder="Masukkan email Anda..."
              class="w-full px-5 py-3 border-2 border-brand-border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition-all text-sm"
              required>
          </div>

          <!-- Pertanyaan -->
          <div>
            <label class="block text-xs font-bold text-brand-dark mb-3 uppercase tracking-wide">Pertanyaan Anda</label>
            <textarea
              name="pertanyaan"
              placeholder="Tulis pertanyaan Anda dengan detail..."
              rows="6"
              class="w-full px-5 py-3 border-2 border-brand-border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition-all resize-none text-sm"
              required></textarea>
            <p class="text-xs text-brand-muted mt-2">Minimalkan spasi, usahakan pertanyaan jelas dan spesifik agar mudah dijawab.</p>
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
              class="flex-1 px-6 py-3 bg-gradient-to-r from-brand-accent to-brand-accent/80 hover:from-brand-secondary hover:to-brand-secondary/80 text-white font-bold text-sm uppercase tracking-widest rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
              <iconify-icon icon="lucide:send" class="text-base"></iconify-icon>
              Kirim Pertanyaan
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
    }

    function closeQuestionModal() {
      document.getElementById('questionModal').classList.add('hidden');
      document.body.style.overflow = 'auto';
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
  </script>

    </div> <!-- glass-content-wrapper end -->
  <?php include '../components/footer.php'; ?>
</body>
</html>
