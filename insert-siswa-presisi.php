<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/supabase.php';

echo "Mulai memasukkan data murid secara presisi...\n";

// Step 1: Get all existing siswa and delete them
echo "Menghapus data lama...\n";
$existingSiswa = supabaseSelect('siswa');
if ($existingSiswa['success'] && !empty($existingSiswa['data'])) {
    foreach ($existingSiswa['data'] as $s) {
        $delResult = supabaseDelete('siswa', $s['id']);
        if ($delResult['success']) {
            echo "  - Menghapus: {$s['nama']}\n";
        }
    }
}

// Define the exact data distribution
$siswaList = [];
$no = 1;

// SDLB: 21 L, 7 P
for ($i = 0; $i < 21; $i++) {
    $siswaList[] = [
        'no_induk' => sprintf('%03d', $no++),
        'nama' => "NAMA MURID " . ($no - 1),
        'jenis_kelamin' => 'Laki-laki',
        'usia' => rand(7, 12),
        'jenjang' => 'SDLB',
        'kelas' => (string)rand(1, 6),
        'nama_ortu' => "NAMA ORANGTUA " . ($no - 1),
        'telpon_ortu' => '08' . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT),
        'alamat_ortu' => 'Sragen',
        'status' => 'Aktif'
    ];
}
for ($i = 0; $i < 7; $i++) {
    $siswaList[] = [
        'no_induk' => sprintf('%03d', $no++),
        'nama' => "NAMA MURID " . ($no - 1),
        'jenis_kelamin' => 'Perempuan',
        'usia' => rand(7, 12),
        'jenjang' => 'SDLB',
        'kelas' => (string)rand(1, 6),
        'nama_ortu' => "NAMA ORANGTUA " . ($no - 1),
        'telpon_ortu' => '08' . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT),
        'alamat_ortu' => 'Sragen',
        'status' => 'Aktif'
    ];
}

// SMPLB: 7 L, 7 P
for ($i = 0; $i < 7; $i++) {
    $siswaList[] = [
        'no_induk' => sprintf('%03d', $no++),
        'nama' => "NAMA MURID " . ($no - 1),
        'jenis_kelamin' => 'Laki-laki',
        'usia' => rand(13, 15),
        'jenjang' => 'SMPLB',
        'kelas' => (string)rand(7, 9),
        'nama_ortu' => "NAMA ORANGTUA " . ($no - 1),
        'telpon_ortu' => '08' . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT),
        'alamat_ortu' => 'Sragen',
        'status' => 'Aktif'
    ];
}
for ($i = 0; $i < 7; $i++) {
    $siswaList[] = [
        'no_induk' => sprintf('%03d', $no++),
        'nama' => "NAMA MURID " . ($no - 1),
        'jenis_kelamin' => 'Perempuan',
        'usia' => rand(13, 15),
        'jenjang' => 'SMPLB',
        'kelas' => (string)rand(7, 9),
        'nama_ortu' => "NAMA ORANGTUA " . ($no - 1),
        'telpon_ortu' => '08' . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT),
        'alamat_ortu' => 'Sragen',
        'status' => 'Aktif'
    ];
}

// SMALB: 11 L, 8 P
for ($i = 0; $i < 11; $i++) {
    $siswaList[] = [
        'no_induk' => sprintf('%03d', $no++),
        'nama' => "NAMA MURID " . ($no - 1),
        'jenis_kelamin' => 'Laki-laki',
        'usia' => rand(16, 18),
        'jenjang' => 'SMALB',
        'kelas' => (string)rand(10, 12),
        'nama_ortu' => "NAMA ORANGTUA " . ($no - 1),
        'telpon_ortu' => '08' . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT),
        'alamat_ortu' => 'Sragen',
        'status' => 'Aktif'
    ];
}
for ($i = 0; $i < 8; $i++) {
    $siswaList[] = [
        'no_induk' => sprintf('%03d', $no++),
        'nama' => "NAMA MURID " . ($no - 1),
        'jenis_kelamin' => 'Perempuan',
        'usia' => rand(16, 18),
        'jenjang' => 'SMALB',
        'kelas' => (string)rand(10, 12),
        'nama_ortu' => "NAMA ORANGTUA " . ($no - 1),
        'telpon_ortu' => '08' . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT),
        'alamat_ortu' => 'Sragen',
        'status' => 'Aktif'
    ];
}

// Exact pekerjaan distribution
$pekerjaanList = array_merge(
    array_fill(0, 6, 'Buruh'),
    array_fill(0, 10, 'Karyawan Swasta'),
    array_fill(0, 1, 'Pedagang Kecil'),
    array_fill(0, 11, 'Petani'),
    array_fill(0, 28, 'ASN/TNI/Polri'),
    array_fill(0, 4, 'Wiraswasta'),
    array_fill(0, 1, 'Lainnya')
);

// Assign pekerjaan
foreach ($siswaList as $index => &$siswa) {
    $siswa['pekerjaan_ortu'] = $pekerjaanList[$index];
    $siswa['foto'] = 'https://picsum.photos/seed/siswa-' . ($index + 1) . '/300/400.jpg';
}

// Insert all
$successCount = 0;
$errorCount = 0;
foreach ($siswaList as $siswa) {
    $result = supabaseInsert('siswa', $siswa);
    if ($result['success']) {
        echo "✅ Berhasil: {$siswa['nama']} ({$siswa['jenjang']}, {$siswa['jenis_kelamin']}, {$siswa['pekerjaan_ortu']})\n";
        $successCount++;
    } else {
        echo "❌ Gagal: {$siswa['nama']} - " . ($result['error'] ?? 'Unknown') . "\n";
        $errorCount++;
    }
}

echo "\nSelesai! Total: " . count($siswaList) . ", Berhasil: $successCount, Gagal: $errorCount\n";
?>