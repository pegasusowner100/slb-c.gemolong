<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/supabase.php';

$siswa = [
    'no_induk' => '061',
    'nama' => 'NAMA MURID 61',
    'jenis_kelamin' => 'Perempuan',
    'usia' => 18,
    'jenjang' => 'SMALB',
    'kelas' => '12',
    'nama_ortu' => 'NAMA ORANGTUA 61',
    'telpon_ortu' => '081234567861',
    'pekerjaan_ortu' => 'Lainnya',
    'alamat_ortu' => 'Sragen',
    'foto' => 'https://picsum.photos/seed/siswa-61/300/400.jpg',
    'status' => 'Aktif'
];

$result = supabaseInsert('siswa', $siswa);
if ($result['success']) {
    echo "Berhasil menambahkan NAMA MURID 61\n";
} else {
    echo "Gagal: " . ($result['error'] ?? 'Unknown') . "\n";
}
?>