<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

$raw = $_POST ?: [];
if (empty($raw)) {
    $input = file_get_contents('php://input');
    $raw = json_decode($input, true) ?: [];
}

$action = trim($raw['action'] ?? '');

if ($action === 'request_otp') {
    $nama = trim($raw['nama'] ?? '');
    $email = trim($raw['email'] ?? '');
    $no_hp = trim($raw['no_hp'] ?? '');
    $jenis_surat = trim($raw['jenis_surat'] ?? '');
    $jenis_surat_lainnya = trim($raw['jenis_surat_lainnya'] ?? '');
    $keterangan = trim($raw['keterangan'] ?? '');

    if (empty($nama) || empty($email) || empty($no_hp) || empty($jenis_surat)) {
        echo json_encode(['success' => false, 'message' => 'Field wajib: Nama, Email, No HP, dan Jenis Surat harus diisi!']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email tidak valid!']);
        exit;
    }

    $jenisFinal = $jenis_surat === 'Lainnya' && !empty($jenis_surat_lainnya) ? $jenis_surat_lainnya : $jenis_surat;

    // Generate 6-digit OTP
    $otp = rand(100000, 999999);
    $_SESSION['surat_otp'] = $otp;
    $_SESSION['surat_otp_time'] = time();
    $_SESSION['surat_pending_data'] = [
        'nama' => $nama,
        'email' => $email,
        'no_hp' => $no_hp,
        'jenis_surat' => $jenisFinal,
        'jenis_surat_lainnya' => $jenis_surat_lainnya ?: null,
        'keterangan' => $keterangan
    ];

    // Log OTP for local testing
    $otp_file = __DIR__ . '/../uploads/otp_log.txt';
    $log_dir = dirname($otp_file);
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0777, true);
    }
    @file_put_contents($otp_file, "[" . date('Y-m-d H:i:s') . "] Email (Surat): $email | OTP: $otp\n");

    // Attempt to send email
    $to = $email;
    $subject = "Kode Verifikasi Permohonan Surat - " . SITE_NAME;
    $message = "Halo $nama,\n\nKode verifikasi Anda untuk permohonan surat adalah: $otp\n\nKode ini berlaku selama 5 minutes.";
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

if ($action === 'verify_otp') {
    $user_otp = trim($raw['otp'] ?? '');

    if (empty($_SESSION['surat_otp']) || empty($_SESSION['surat_pending_data'])) {
        echo json_encode(['success' => false, 'message' => 'Sesi verifikasi habis. Silakan kirim ulang kode.']);
        exit;
    }

    // Check expiration (5 minutes)
    if (time() - $_SESSION['surat_otp_time'] > 300) {
        unset($_SESSION['surat_otp'], $_SESSION['surat_otp_time'], $_SESSION['surat_pending_data']);
        echo json_encode(['success' => false, 'message' => 'Kode verifikasi telah kadaluwarsa.']);
        exit;
    }

    if ($user_otp != $_SESSION['surat_otp']) {
        echo json_encode(['success' => false, 'message' => 'Kode verifikasi salah!']);
        exit;
    }

    if (!$supabaseConnected) {
        echo json_encode(['success' => false, 'message' => 'Koneksi database tidak tersedia.']);
        exit;
    }

    $pending = $_SESSION['surat_pending_data'];
    $insert = [
        'nama' => $pending['nama'],
        'email' => $pending['email'],
        'no_hp' => $pending['no_hp'],
        'jenis_surat' => $pending['jenis_surat'],
        'jenis_surat_lainnya' => $pending['jenis_surat_lainnya'],
        'keterangan' => $pending['keterangan'],
        'status' => 'belum_direspon'
    ];

    $res = supabaseInsert('surat', $insert);
    if ($res['success']) {
        unset($_SESSION['surat_otp'], $_SESSION['surat_otp_time'], $_SESSION['surat_pending_data']);
        echo json_encode(['success' => true, 'message' => 'Permohonan surat Anda berhasil dikirim!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database: ' . ($res['error'] ?? 'Unknown error')]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenal']);
exit;
