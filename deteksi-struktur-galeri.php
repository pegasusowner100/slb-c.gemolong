<?php
require_once 'includes/db.php';

echo "<h1 style='text-align: center; color: #3E6B4E;'>🔍 Deteksi Kolom Tabel Galeri</h1>";

if (!$supabaseConnected) {
    die("<p style='color: red;'>❌ Supabase tidak terhubung!</p>");
}

echo "<p style='color: green;'>✅ Supabase terhubung!</p>";
echo "<hr>";

echo "<h2>1. Mengambil semua data dari tabel `galeri`...</h2>";
$result = supabaseSelect('galeri', ['limit' => 10, 'order' => 'created_at.desc']);

if (!$result['success']) {
    echo "<p style='color: red;'>❌ Gagal mengambil data!</p>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    exit;
}

if (empty($result['data'])) {
    echo "<p>⚠️ Tabel `galeri` kosong!</p>";
    echo "<p>Membuat data test untuk mendeteksi struktur kolom...</p>";
    // Coba insert dengan nama kolom umum
    $test_data = [
        'judul' => 'Test Detect Structure',
        'gambar' => 'https://picsum.photos/seed/test/800/400',
        'jenis' => 'Photo',
        'status' => 'published',
        'tanggal' => date('Y-m-d')
    ];
    echo "<p>Mencoba insert dengan kolom umum...</p>";
    $insert_test = supabaseInsert('galeri', $test_data);
    if ($insert_test['success']) {
        echo "<p style='color: green;'>✅ Berhasil insert! Mengulang...</p>";
        $result = supabaseSelect('galeri', ['limit' => 10]);
    } else {
        echo "<p style='color: red;'>Gagal insert test! Error: " . ($insert_test['error'] ?? '') . "</p>";
    }
}

if (isset($result['data']) && !empty($result['data'])) {
    echo "<h2 style='color: #3E6B4E;'>✅ Berhasil mendeteksi struktur!</h2>";
    echo "<h3>Struktur kolom:</h3>";
    $first_item = $result['data'][0];
    echo "<ul>";
    foreach (array_keys($first_item) as $col) {
        echo "<li><strong style='font-size: 18px;'>" . htmlspecialchars($col) . "</strong></li>";
    }
    echo "</ul>";

    echo "<h3>Data pertama:</h3>";
    echo "<pre style='background: #f0f0f0; padding: 15px; border-radius: 8px;'>";
    print_r($first_item);
    echo "</pre>";

    echo "<hr>";
    echo "<h3>Semua data:</h3>";
    foreach ($result['data'] as $index => $item) {
        echo "<div style='background: #e8f4f8; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 5px solid #3E6B4E;'>";
        echo "<h4 style='margin-top: 0;'>Data #" . ($index + 1) . "</h4>";
        echo "<pre style='margin: 0;'>";
        print_r($item);
        echo "</pre>";
        echo "</div>";
    }
}
?>