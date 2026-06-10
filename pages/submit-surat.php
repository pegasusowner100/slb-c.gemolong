<?php
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

$data = [];
$raw = $_POST ?: null;
if (!$raw) {
    // try JSON body
    $input = file_get_contents('php://input');
    $raw = json_decode($input, true) ?: [];
}

$nama = trim($raw['nama'] ?? '');
$email = trim($raw['email'] ?? '');
$no_hp = trim($raw['no_hp'] ?? '');
$jenis_surat = trim($raw['jenis_surat'] ?? '');
$jenis_surat_lainnya = trim($raw['jenis_surat_lainnya'] ?? '');
$keterangan = trim($raw['keterangan'] ?? '');

if (empty($nama) || empty($email) || empty($no_hp) || empty($jenis_surat)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Field wajib: nama, email, no_hp, jenis_surat']);
    exit;
}

$jenisFinal = $jenis_surat === 'Lainnya' && !empty($jenis_surat_lainnya) ? $jenis_surat_lainnya : $jenis_surat;

if (!$supabaseConnected) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Supabase tidak terhubung']);
    exit;
}

$insert = [
    'nama' => $nama,
    'email' => $email,
    'no_hp' => $no_hp,
    'jenis_surat' => $jenisFinal,
    'jenis_surat_lainnya' => $jenis_surat_lainnya ?: null,
    'keterangan' => $keterangan,
    'status' => 'belum_direspon'
];

$res = supabaseInsert('surat', $insert);
if ($res['success']) {
    echo json_encode(['success' => true, 'data' => $res['data'] ?? null]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $res['error'] ?? 'Insert gagal']);
}

exit;
