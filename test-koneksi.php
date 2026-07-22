<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/supabase.php';

echo "<h2>Test Koneksi Supabase</h2>";

echo "<h3>Konfigurasi:</h3>";
echo "SUPABASE_URL: " . SUPABASE_URL . "<br>";
echo "SUPABASE_KEY: " . (defined('SUPABASE_KEY') ? substr(SUPABASE_KEY, 0, 20) . "..." : "Tidak didefinisikan") . "<br>";
echo "SUPABASE_SERVICE_KEY: " . (defined('SUPABASE_SERVICE_KEY') ? substr(SUPABASE_SERVICE_KEY, 0, 20) . "..." : "Tidak didefinisikan") . "<br><br>";

echo "<h3>Test Select:</h3>";
$result = supabaseSelect('profil_sekolah', ['limit' => 1]);
echo "<pre>";
var_dump($result);
echo "</pre>";
?>