<?php
require_once 'includes/db.php';

echo "<h1>Test Galeri Table</h1>";

if (!$supabaseConnected) {
    die("<p style='color: red;'>Supabase tidak terhubung!</p>");
}

echo "<p style='color: green;'>Supabase terhubung!</p>";

// Test insert data
$test_data = [
    'judul' => 'SIMULASI PHOTO 1',
    'slug' => 'simulasi-photo-1',
    'konten' => 'Test konten',
    'file_url' => 'https://picsum.photos/seed/test1/800/400',
    'jenis_galeri' => 'Photo',
    'tanggal_upload' => date('Y-m-d'),
    'status' => 'published'
];

echo "<h3>Mencoba memasukkan data test...</h3>";
$result = supabaseInsert('galeri', $test_data);

if ($result['success']) {
    echo "<p style='color: green;'>Data berhasil dimasukkan ke tabel `galeri`!</p>";
} else {
    echo "<p style='color: red;'>Gagal memasukkan data!</p>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
}
?>