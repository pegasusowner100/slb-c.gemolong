<?php
require_once 'includes/db.php';

echo "<h1>Cek Struktur Tabel Galeri</h1>";

if (!$supabaseConnected) {
    die("<p style='color: red;'>❌ Supabase tidak terhubung!</p>");
}

echo "<p style='color: green;'>✅ Supabase terhubung!</p>";
echo "<hr>";

// Coba SELECT semua data dari galeri
echo "<h2>Data di tabel `galeri`:</h2>";
$result = supabaseSelect('galeri', ['limit' => 20]);

if (!$result['success']) {
    echo "<p style='color: red;'>❌ Gagal mengambil data!</p>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    exit;
}

if (count($result['data']) > 0) {
    echo "<h3>✅ Berhasil menemukan " . count($result['data']) . " data!</h3>";
    echo "<h4>Struktur data pertama:</h4>";
    echo "<pre style='background: #f0f0f0; padding: 15px; border-radius: 8px;'>";
    print_r($result['data'][0]);
    echo "</pre>";
    
    echo "<hr><h4>Semua data:</h4>";
    foreach ($result['data'] as $index => $item) {
        echo "<div style='background: #e8f4f8; padding: 10px; margin: 10px 0; border-radius: 4px;'>";
        echo "<h5>Data #" . ($index + 1) . "</h5>";
        echo "<pre>";
        print_r($item);
        echo "</pre>";
        echo "</div>";
    }
} else {
    echo "<p>⚠️ Tabel `galeri` ada tapi kosong!</p>";
}
?>