<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/supabase.php';

echo "Mulai memasukkan data siswa...\n";

// Data distribusi berdasarkan jenjang dan jenis kelamin
$siswaData = [];

// SDLB: 28 siswa (21 Laki-laki, 7 Perempuan)
$siswaData = array_merge($siswaData, generateSiswaData('SDLB', 21, 'Laki-laki', 1));
$siswaData = array_merge($siswaData, generateSiswaData('SDLB', 7, 'Perempuan', 22));

// SMPLB: 14 siswa (7 Laki-laki, 7 Perempuan)
$siswaData = array_merge($siswaData, generateSiswaData('SMPLB', 7, 'Laki-laki', 29));
$siswaData = array_merge($siswaData, generateSiswaData('SMPLB', 7, 'Perempuan', 36));

// SMALB: 19 siswa (11 Laki-laki, 8 Perempuan)
$siswaData = array_merge($siswaData, generateSiswaData('SMALB', 11, 'Laki-laki', 43));
$siswaData = array_merge($siswaData, generateSiswaData('SMALB', 8, 'Perempuan', 54));

// Distribusi pekerjaan orang tua: Buruh(6), Karyawan Swasta(10), Pedagang Kecil(1), Petani(11), ASN/TNI/Polri(28), Wiraswasta(4), Lainnya(1)
$pekerjaanList = array_merge(
    array_fill(0, 6, 'Buruh'),
    array_fill(0, 10, 'Karyawan Swasta'),
    array_fill(0, 1, 'Pedagang Kecil'),
    array_fill(0, 11, 'Petani'),
    array_fill(0, 28, 'ASN/TNI/Polri'),
    array_fill(0, 4, 'Wiraswasta'),
    array_fill(0, 1, 'Lainnya')
);

// Shuffle pekerjaan list for random distribution
shuffle($pekerjaanList);

// Insert each student
$successCount = 0;
$errorCount = 0;
foreach ($siswaData as $index => $siswa) {
    $no = $index + 1;
    $data = [
        'no_induk' => sprintf('%03d', $no),
        'nama' => "NAMA MURID " . $no,
        'jenis_kelamin' => $siswa['jenis_kelamin'],
        'usia' => rand(7, 18),
        'jenjang' => $siswa['jenjang'],
        'kelas' => generateKelas($siswa['jenjang']),
        'nama_ortu' => "NAMA ORANGTUA " . $no,
        'telpon_ortu' => '08' . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT),
        'pekerjaan_ortu' => $pekerjaanList[$index] ?? 'Lainnya',
        'alamat_ortu' => 'Sragen',
        'foto' => 'https://picsum.photos/seed/siswa-' . $no . '/300/400.jpg',
        'status' => 'Aktif'
    ];

    $result = supabaseInsert('siswa', $data);

    if ($result['success']) {
        echo "✅ Berhasil memasukkan: {$data['nama']}\n";
        $successCount++;
    } else {
        echo "❌ Gagal memasukkan: {$data['nama']} - " . ($result['error'] ?? 'Unknown error') . "\n";
        $errorCount++;
    }
}

echo "\nSelesai!\n";
echo "Berhasil: $successCount\n";
echo "Gagal: $errorCount\n";

function generateSiswaData($jenjang, $count, $jenisKelamin, $startNo) {
    $data = [];
    for ($i = 0; $i < $count; $i++) {
        $data[] = [
            'jenjang' => $jenjang,
            'jenis_kelamin' => $jenisKelamin,
            'no' => $startNo + $i
        ];
    }
    return $data;
}

function generateKelas($jenjang) {
    $kelasOptions = [
        'SDLB' => ['1', '2', '3', '4', '5', '6'],
        'SMPLB' => ['7', '8', '9'],
        'SMALB' => ['10', '11', '12']
    ];
    return $kelasOptions[$jenjang][array_rand($kelasOptions[$jenjang])];
}
?>