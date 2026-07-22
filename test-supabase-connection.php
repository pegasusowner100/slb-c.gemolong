<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/supabase.php';

echo "<h1>Testing Supabase Connection</h1>";
echo "<h2>Configuration:</h2>";
echo "<p><strong>SUPABASE_URL:</strong> " . (defined('SUPABASE_URL') ? htmlspecialchars(SUPABASE_URL) : '<i>Not defined</i>') . "</p>";
echo "<p><strong>SUPABASE_KEY:</strong> " . (defined('SUPABASE_KEY') ? htmlspecialchars(substr(SUPABASE_KEY, 0, 20) . '...') : '<i>Not defined</i>') . "</p>";
echo "<p><strong>SUPABASE_SERVICE_KEY:</strong> " . (defined('SUPABASE_SERVICE_KEY') ? htmlspecialchars(substr(SUPABASE_SERVICE_KEY, 0, 20) . '...') : '<i>Not defined</i>') . "</p>";
echo "<hr>";

echo "<h2>Testing Select from profil_sekolah:</h2>";
$profilResult = supabaseSelect('profil_sekolah');
if ($profilResult['success']) {
    echo "<p style='color: green;'><strong>Success!</strong> Koneksi ke Supabase berhasil. " . (count($profilResult['data']) > 0 ? 'Ditemukan ' . count($profilResult['data']) . ' baris data.' : 'Tabel profil_sekolah kosong.') . "</p>";
    if (count($profilResult['data']) > 0) {
        echo "<pre>";
        print_r($profilResult['data'][0]);
        echo "</pre>";
    }
} else {
    echo "<p style='color: red;'><strong>Gagal!</strong> " . htmlspecialchars($profilResult['error'] ?? 'Unknown error') . "</p>";
    echo "<pre>";
    print_r($profilResult);
    echo "</pre>";
}

echo "<hr>";
echo "<h2>Testing Select from hero:</h2>";
$heroResult = supabaseSelect('hero');
if ($heroResult['success']) {
    echo "<p style='color: green;'><strong>Success!</strong> Tabel hero berhasil diakses. " . (count($heroResult['data']) > 0 ? 'Ditemukan ' . count($heroResult['data']) . ' baris data.' : 'Tabel hero kosong.') . "</p>";
} else {
    echo "<p style='color: red;'><strong>Gagal!</strong> " . htmlspecialchars($heroResult['error'] ?? 'Unknown error') . "</p>";
    echo "<pre>";
    print_r($heroResult);
    echo "</pre>";
}
?>