<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/config.php';
require_once '../includes/db.php';

$title = "Form Pendaftaran PPDB — SLB BC KARYA SEJAHTERA " . SITE_NAME;
$page = 'ppdb';
$success = $_SESSION['ppdb_success'] ?? false;
$error = '';
$no_reg = $_SESSION['ppdb_no_reg'] ?? '';

// Clear session variables after reading
unset($_SESSION['ppdb_success'], $_SESSION['ppdb_no_reg']);

// Handle AJAX actions for OTP Email Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'request_otp') {
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $nisn = trim($_POST['nisn'] ?? '');
        $tempat_lahir = trim($_POST['tempat_lahir'] ?? '');
        $tanggal_lahir = trim($_POST['tanggal_lahir'] ?? '');
        $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
        $agama = trim($_POST['agama'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $sekolah_asal = trim($_POST['sekolah_asal'] ?? '');
        $tahun_lulusan = trim($_POST['tahun_lulusan'] ?? '');
        $nama_ayah = trim($_POST['nama_ayah'] ?? '');
        $pekerjaan_ayah = trim($_POST['pekerjaan_ayah'] ?? '');
        $nama_ibu = trim($_POST['nama_ibu'] ?? '');
        $pekerjaan_ibu = trim($_POST['pekerjaan_ibu'] ?? '');
        $no_hp_ortu = trim($_POST['no_hp_ortu'] ?? '');

        // Validation
        if (empty($nama_lengkap) || empty($email) || empty($tanggal_lahir) || empty($jenis_kelamin) || empty($alamat) || empty($sekolah_asal) || empty($nama_ayah) || empty($nama_ibu) || empty($no_hp_ortu)) {
            echo json_encode(['success' => false, 'message' => 'Semua field wajib (*) harus diisi!']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email tidak valid!']);
            exit;
        }
        if (!preg_match('/^(\+62|62|0)[0-9]{9,12}$/', $no_hp_ortu)) {
            echo json_encode(['success' => false, 'message' => 'Nomor HP tidak valid!']);
            exit;
        }

        // Generate 6-digit OTP
        $otp = rand(100000, 999999);
        $_SESSION['ppdb_otp'] = $otp;
        $_SESSION['ppdb_otp_time'] = time();
        $_SESSION['ppdb_pending_data'] = [
            'nama_lengkap' => $nama_lengkap,
            'email' => $email,
            'nisn' => $nisn ?: null,
            'tempat_lahir' => $tempat_lahir ?: null,
            'tanggal_lahir' => $tanggal_lahir,
            'jenis_kelamin' => $jenis_kelamin,
            'agama' => $agama ?: null,
            'alamat' => $alamat,
            'sekolah_asal' => $sekolah_asal,
            'tahun_lulusan' => $tahun_lulusan ?: null,
            'nama_ayah' => $nama_ayah,
            'pekerjaan_ayah' => $pekerjaan_ayah ?: null,
            'nama_ibu' => $nama_ibu,
            'pekerjaan_ibu' => $pekerjaan_ibu ?: null,
            'no_hp_ortu' => $no_hp_ortu
        ];

        // Log OTP
        $otp_file = __DIR__ . '/../uploads/otp_log.txt';
        $log_dir = dirname($otp_file);
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0777, true);
        }
        @file_put_contents($otp_file, "[" . date('Y-m-d H:i:s') . "] Email (PPDB): $email | OTP: $otp\n");

        // Send Email
        $to = $email;
        $subject = "Kode Verifikasi Pendaftaran PPDB - " . SITE_NAME;
        $message = "Halo $nama_lengkap,\n\nKode verifikasi Anda untuk pendaftaran PPDB adalah: $otp\n\nKode ini berlaku selama 5 menit.";
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

        if (empty($_SESSION['ppdb_otp']) || empty($_SESSION['ppdb_pending_data'])) {
            echo json_encode(['success' => false, 'message' => 'Sesi verifikasi habis. Silakan kirim ulang kode.']);
            exit;
        }

        if (time() - $_SESSION['ppdb_otp_time'] > 300) {
            unset($_SESSION['ppdb_otp'], $_SESSION['ppdb_otp_time'], $_SESSION['ppdb_pending_data']);
            echo json_encode(['success' => false, 'message' => 'Kode verifikasi telah kadaluwarsa.']);
            exit;
        }

        if ($user_otp != $_SESSION['ppdb_otp']) {
            echo json_encode(['success' => false, 'message' => 'Kode verifikasi salah!']);
            exit;
        }

        if (!$supabaseConnected) {
            echo json_encode(['success' => false, 'message' => 'Koneksi database tidak tersedia.']);
            exit;
        }

        $pending = $_SESSION['ppdb_pending_data'];
        $no_reg = 'PPDB-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $data = [
            'no_reg' => $no_reg,
            'nama_lengkap' => $pending['nama_lengkap'],
            'email' => $pending['email'],
            'nisn' => $pending['nisn'],
            'tempat_lahir' => $pending['tempat_lahir'],
            'tanggal_lahir' => $pending['tanggal_lahir'],
            'jenis_kelamin' => $pending['jenis_kelamin'],
            'agama' => $pending['agama'],
            'alamat' => $pending['alamat'],
            'sekolah_asal' => $pending['sekolah_asal'],
            'tahun_lulusan' => $pending['tahun_lulusan'],
            'nama_ayah' => $pending['nama_ayah'],
            'pekerjaan_ayah' => $pending['pekerjaan_ayah'],
            'nama_ibu' => $pending['nama_ibu'],
            'pekerjaan_ibu' => $pending['pekerjaan_ibu'],
            'no_hp_ortu' => $pending['no_hp_ortu'],
            'status' => 'pending'
        ];

        $result = supabaseInsert('ppdb', $data);

        if ($result['success']) {
            $_SESSION['ppdb_success'] = true;
            $_SESSION['ppdb_no_reg'] = $no_reg;
            unset($_SESSION['ppdb_otp'], $_SESSION['ppdb_otp_time'], $_SESSION['ppdb_pending_data']);
            echo json_encode(['success' => true, 'message' => 'Pendaftaran berhasil!', 'no_reg' => $no_reg]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data: ' . ($result['error'] ?? 'Unknown error')]);
        }
        exit;
    }
}

include '../components/head.php';
?>
<body class="bg-brand-bg text-brand-dark font-sans">
    <?php include '../components/navbar.php'; ?>

    <!-- Page Header -->
    <section class="page-hero bg-brand-dark">
        <div class="max-w-7xl mx-auto px-6">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label fade-in-up block">Pendaftaran</span>
            <h1 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1] fade-in-up delay-100 -mt-2">Form Pendaftaran PPDB</h1>
        </div>
    </section>

    <!-- Form Section -->
    <section class="py-12 md:py-24 bg-white">
        <div class="max-w-4xl mx-auto px-6">
            <?php if ($success): ?>
                <!-- Success Message -->
                <div class="bg-green-50 border-2 border-green-500 rounded-xl p-8 mb-8">
                    <div class="flex items-start gap-4">
                        <iconify-icon icon="lucide:check-circle" class="w-6 h-6 text-green-600 flex-shrink-0 mt-1"></iconify-icon>
                        <div>
                            <h2 class="text-2xl font-bold text-green-700 mb-2">Pendaftaran Berhasil!</h2>
                            <p class="text-green-700 mb-4">Terima kasih telah mendaftar di SLB BC KARYA SEJAHTERA. Data Anda telah kami terima.</p>
                            <div class="bg-white rounded-lg p-4 mb-4 border border-green-300">
                                <p class="text-sm text-gray-600 mb-1">Nomor Registrasi Anda:</p>
                                <p class="text-2xl font-bold text-green-700"><?php echo htmlspecialchars($no_reg); ?></p>
                                <p class="text-sm text-gray-600 mt-2">Simpan nomor ini untuk keperluan verifikasi</p>
                            </div>
                            <p class="text-sm text-green-700">
                                Tim kami akan menghubungi Anda melalui nomor yang Anda daftarkan untuk tahap selanjutnya.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <a href="ppdb.php" class="inline-flex items-center gap-2 px-8 py-3 bg-brand-accent text-white font-bold text-sm uppercase tracking-widest rounded-lg hover:bg-brand-secondary transition-colors">
                        Kembali ke Halaman PPDB
                        <iconify-icon icon="lucide:arrow-right" class="w-5 h-5"></iconify-icon>
                    </a>
                </div>
            <?php else: ?>
                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="bg-red-50 border-2 border-red-500 rounded-xl p-4 mb-8 flex items-start gap-3">
                        <iconify-icon icon="lucide:alert-circle" class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5"></iconify-icon>
                        <div>
                            <p class="text-red-700 font-semibold"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <form id="ppdbRegistrationForm" class="space-y-8">
                    <!-- Data Pribadi -->
                    <div class="bg-gray-50 rounded-xl p-6 md:p-8">
                        <h2 class="font-bold text-xl text-gray-800 mb-6 flex items-center gap-2">
                            <iconify-icon icon="lucide:user" class="w-5 h-5"></iconify-icon>
                            Data Pribadi
                        </h2>

                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Nama Lengkap -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nama_lengkap" id="ppdb_nama_lengkap" value="<?php echo htmlspecialchars($_POST['nama_lengkap'] ?? ''); ?>" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="Masukkan nama lengkap Anda">
                            </div>

                            <!-- Email -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Email Aktif <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="email" id="ppdb_email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="nama@email.com untuk menerima kode verifikasi OTP">
                            </div>

                            <!-- NISN -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">NISN</label>
                                <input type="text" name="nisn" value="<?php echo htmlspecialchars($_POST['nisn'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="10 digit NISN">
                            </div>

                            <!-- Jenis Kelamin -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Jenis Kelamin <span class="text-red-500">*</span>
                                </label>
                                <select name="jenis_kelamin" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20">
                                    <option value="">-- Pilih Jenis Kelamin --</option>
                                    <option value="Laki-laki" <?php echo ($_POST['jenis_kelamin'] ?? '') === 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                                    <option value="Perempuan" <?php echo ($_POST['jenis_kelamin'] ?? '') === 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                                </select>
                            </div>

                            <!-- Tempat Lahir -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" value="<?php echo htmlspecialchars($_POST['tempat_lahir'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="Kota/Kabupaten">
                            </div>

                            <!-- Tanggal Lahir -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Tanggal Lahir <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="tanggal_lahir" value="<?php echo htmlspecialchars($_POST['tanggal_lahir'] ?? ''); ?>" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20">
                            </div>

                            <!-- Agama -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Agama</label>
                                <select name="agama"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20">
                                    <option value="">-- Pilih Agama --</option>
                                    <option value="Islam" <?php echo ($_POST['agama'] ?? '') === 'Islam' ? 'selected' : ''; ?>>Islam</option>
                                    <option value="Kristen" <?php echo ($_POST['agama'] ?? '') === 'Kristen' ? 'selected' : ''; ?>>Kristen</option>
                                    <option value="Katolik" <?php echo ($_POST['agama'] ?? '') === 'Katolik' ? 'selected' : ''; ?>>Katolik</option>
                                    <option value="Hindu" <?php echo ($_POST['agama'] ?? '') === 'Hindu' ? 'selected' : ''; ?>>Hindu</option>
                                    <option value="Buddha" <?php echo ($_POST['agama'] ?? '') === 'Buddha' ? 'selected' : ''; ?>>Buddha</option>
                                    <option value="Konghucu" <?php echo ($_POST['agama'] ?? '') === 'Konghucu' ? 'selected' : ''; ?>>Konghucu</option>
                                </select>
                            </div>

                            <!-- Alamat -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Alamat <span class="text-red-500">*</span>
                                </label>
                                <textarea name="alamat" required rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                          placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten"><?php echo htmlspecialchars($_POST['alamat'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Data Pendidikan -->
                    <div class="bg-gray-50 rounded-xl p-6 md:p-8">
                        <h2 class="font-bold text-xl text-gray-800 mb-6 flex items-center gap-2">
                            <iconify-icon icon="lucide:book-open" class="w-5 h-5"></iconify-icon>
                            Data Pendidikan
                        </h2>

                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Sekolah Asal -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Sekolah Asal <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="sekolah_asal" value="<?php echo htmlspecialchars($_POST['sekolah_asal'] ?? ''); ?>" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="Nama sekolah asal">
                            </div>

                            <!-- Tahun Lulusan -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Lulusan</label>
                                <input type="text" name="tahun_lulusan" value="<?php echo htmlspecialchars($_POST['tahun_lulusan'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="Contoh: 2023" maxlength="4">
                            </div>
                        </div>
                    </div>

                    <!-- Data Orang Tua/Wali -->
                    <div class="bg-gray-50 rounded-xl p-6 md:p-8">
                        <h2 class="font-bold text-xl text-gray-800 mb-6 flex items-center gap-2">
                            <iconify-icon icon="lucide:users" class="w-5 h-5"></iconify-icon>
                            Data Orang Tua/Wali
                        </h2>

                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Nama Ayah -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nama Ayah <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nama_ayah" value="<?php echo htmlspecialchars($_POST['nama_ayah'] ?? ''); ?>" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="Nama ayah kandung">
                            </div>

                            <!-- Pekerjaan Ayah -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Pekerjaan Ayah</label>
                                <input type="text" name="pekerjaan_ayah" value="<?php echo htmlspecialchars($_POST['pekerjaan_ayah'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="Pekerjaan ayah">
                            </div>

                            <!-- Nama Ibu -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nama Ibu <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nama_ibu" value="<?php echo htmlspecialchars($_POST['nama_ibu'] ?? ''); ?>" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="Nama ibu kandung">
                            </div>

                            <!-- Pekerjaan Ibu -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Pekerjaan Ibu</label>
                                <input type="text" name="pekerjaan_ibu" value="<?php echo htmlspecialchars($_POST['pekerjaan_ibu'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="Pekerjaan ibu">
                            </div>

                            <!-- No HP Orang Tua -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nomor HP Orang Tua <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" name="no_hp_ortu" value="<?php echo htmlspecialchars($_POST['no_hp_ortu'] ?? ''); ?>" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="Contoh: 0812345678 atau +6212345678">
                                <p class="text-xs text-gray-600 mt-1">Format: 0812... atau +6212...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4 justify-center">
                        <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 bg-brand-accent text-white font-bold text-sm uppercase tracking-widest rounded-lg hover:bg-brand-secondary transition-colors shadow-lg hover:shadow-xl">
                            <iconify-icon icon="lucide:send" class="w-5 h-5"></iconify-icon>
                            Kirim Pendaftaran
                        </button>
                        <a href="ppdb.php" class="inline-flex items-center gap-2 px-8 py-3 bg-gray-300 text-gray-700 font-bold text-sm uppercase tracking-widest rounded-lg hover:bg-gray-400 transition-colors">
                            Batal
                        </a>
                    </div>

                    <p class="text-xs text-gray-600 text-center">
                        <span class="text-red-500">*</span> Bidang wajib diisi
                    </p>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <!-- OTP VERIFICATION MODAL -->
    <div id="ppdbOtpModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-brand-accent to-brand-accent/80 px-6 py-5 flex items-center justify-between border-b-4 border-brand-accent/20">
                <h2 class="font-serif text-xl text-white flex items-center gap-3">
                    <iconify-icon icon="lucide:mail" class="text-2xl"></iconify-icon>
                    Verifikasi Pendaftaran
                </h2>
                <button type="button" onclick="closePpdbOtpModal()" class="text-white hover:bg-white/20 p-2 rounded-lg transition-colors">
                    <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-6">
                <!-- Alert Box -->
                <div id="ppdbAlert" class="hidden rounded-lg p-4 flex items-start gap-3 border-2">
                    <iconify-icon id="ppdbAlertIcon" icon="lucide:alert-circle" class="w-6 h-6 flex-shrink-0 mt-0.5"></iconify-icon>
                    <div>
                        <p id="ppdbAlertText" class="font-semibold text-sm"></p>
                    </div>
                </div>

                <!-- OTP Input Form -->
                <form id="ppdbOtpForm" class="space-y-5">
                    <div class="text-center py-2">
                        <iconify-icon icon="lucide:mail-check" class="text-5xl text-brand-accent mb-3"></iconify-icon>
                        <p class="text-sm text-gray-600">Kode verifikasi OTP 6 digit telah dikirim ke email Anda. Silakan periksa kotak masuk atau spam.</p>
                        
                        <!-- Local testing preview -->
                        <div id="otpPpdbDeveloperPreview" class="hidden mt-2 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-xs font-mono">
                            [DEVELOPER ONLY] OTP: <span id="developerPpdbOtpCode" class="font-bold text-sm"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide text-center">Kode Verifikasi</label>
                        <input
                            type="text"
                            id="input_ppdb_otp"
                            placeholder="123456"
                            maxlength="6"
                            pattern="[0-9]{6}"
                            class="w-full px-5 py-3 border-2 border-gray-300 rounded-lg text-center font-mono text-2xl tracking-[0.5em] focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition-all"
                            required>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closePpdbOtpModal()" class="flex-1 px-6 py-3 bg-gray-150 hover:bg-gray-200 text-gray-755 font-bold text-sm uppercase tracking-wider rounded-lg transition-colors">
                            Batal
                        </button>
                        <button type="submit" id="btnVerifyPpdbOtp" class="flex-1 px-6 py-3 bg-gradient-to-r from-brand-accent to-brand-accent/80 hover:from-brand-secondary hover:to-brand-secondary/80 text-white font-bold text-sm uppercase tracking-wider rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                            <iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon>
                            Verifikasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>

    <script>
        function openPpdbOtpModal() {
            document.getElementById('ppdbOtpModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            hidePpdbAlert();
        }

        function closePpdbOtpModal() {
            document.getElementById('ppdbOtpModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function showPpdbAlert(type, message) {
            const alertBox = document.getElementById('ppdbAlert');
            const alertIcon = document.getElementById('ppdbAlertIcon');
            const alertText = document.getElementById('ppdbAlertText');

            alertText.textContent = message;
            if (type === 'success') {
                alertBox.className = "rounded-lg p-4 flex items-start gap-3 border-2 bg-green-50 border-green-500 text-green-700";
                alertIcon.setAttribute('icon', 'lucide:check-circle');
            } else {
                alertBox.className = "rounded-lg p-4 flex items-start gap-3 border-2 bg-red-50 border-red-500 text-red-700";
                alertIcon.setAttribute('icon', 'lucide:alert-circle');
            }
            alertBox.classList.remove('hidden');
        }

        function hidePpdbAlert() {
            document.getElementById('ppdbAlert').classList.add('hidden');
        }

        // Form Submit: Request OTP
        const regForm = document.getElementById('ppdbRegistrationForm');
        if (regForm) {
            regForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const btn = regForm.querySelector('button[type="submit"]');
                const origHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<iconify-icon icon="lucide:loader-2" class="animate-spin text-base"></iconify-icon> Memproses...';

                const formData = new FormData(regForm);
                formData.append('action', 'request_otp');

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;

                    if (data.success) {
                        openPpdbOtpModal();
                        showPpdbAlert('success', data.message);
                        if (data.otp_preview) {
                            document.getElementById('developerPpdbOtpCode').textContent = data.otp_preview;
                            document.getElementById('otpPpdbDeveloperPreview').classList.remove('hidden');
                        }
                    } else {
                        alert('Gagal mengirim verifikasi: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                    alert('Terjadi kesalahan koneksi, silakan coba lagi.');
                });
            });
        }

        // OTP Verify Form Submit
        document.getElementById('ppdbOtpForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const otp = document.getElementById('input_ppdb_otp').value;
            const btn = document.getElementById('btnVerifyPpdbOtp');

            btn.disabled = true;
            btn.innerHTML = '<iconify-icon icon="lucide:loader-2" class="animate-spin text-base"></iconify-icon> Memverifikasi...';
            hidePpdbAlert();

            const formData = new FormData();
            formData.append('action', 'verify_otp');
            formData.append('otp', otp);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon> Verifikasi';

                if (data.success) {
                    showPpdbAlert('success', data.message + ' Halaman akan dimuat ulang...');
                    setTimeout(() => {
                        closePpdbOtpModal();
                        window.location.reload();
                    }, 2000);
                } else {
                    showPpdbAlert('error', data.message);
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.innerHTML = '<iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon> Verifikasi';
                showPpdbAlert('error', 'Terjadi kesalahan sistem, silakan coba lagi.');
            });
        });
    </script>
</body>
</html>
