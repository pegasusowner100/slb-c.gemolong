<?php
/**
 * Test Supabase Connection & iklan Table
 * Verifikasi insert/retrieve data iklan ke Supabase
 */

require_once 'includes/config.php';
require_once 'includes/supabase.php';

echo "<h1>Test Supabase iklan Table</h1>";
echo "<hr>";

// Test 1: Check connection
echo "<h2>1. Check Supabase Connection</h2>";
if (!defined('SUPABASE_URL') || !defined('SUPABASE_KEY')) {
    echo "<p style='color:red;'>❌ Config tidak lengkap. Periksa includes/config.php</p>";
    exit;
}
echo "<p><strong>URL:</strong> " . htmlspecialchars(SUPABASE_URL) . "</p>";
echo "<p><strong>Key:</strong> " . substr(SUPABASE_KEY, 0, 20) . "..." . "</p>";

// Test 2: Coba select dari tabel iklan
echo "<h2>2. Test SELECT dari tabel iklan</h2>";
$result = supabaseSelect('iklan', []);
if ($result['success']) {
    echo "<p style='color:green;'>✅ Koneksi berhasil</p>";
    echo "<p>Total records di tabel iklan: " . count($result['data'] ?? []) . "</p>";
    if (!empty($result['data'])) {
        echo "<pre>";
        echo json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        echo "</pre>";
    }
} else {
    echo "<p style='color:red;'>❌ SELECT gagal</p>";
    echo "<pre>";
    echo json_encode($result, JSON_PRETTY_PRINT);
    echo "</pre>";
    exit;
}

// Test 3: Insert test record
echo "<h2>3. Test INSERT iklan baru</h2>";
$testData = [
    'gambar_url' => 'https://via.placeholder.com/500',
    'teks' => 'Test Iklan dari PHP Script',
    'informasi' => 'Ini adalah test insert iklan ke Supabase',
    'tampilkan' => true
];
echo "<p><strong>Data yang akan diinsert:</strong></p>";
echo "<pre>";
echo json_encode($testData, JSON_PRETTY_PRINT);
echo "</pre>";

$insertResult = supabaseInsert('iklan', $testData);
if ($insertResult['success']) {
    echo "<p style='color:green;'>✅ INSERT berhasil</p>";
    if (!empty($insertResult['data'])) {
        echo "<pre>";
        echo json_encode($insertResult['data'], JSON_PRETTY_PRINT);
        echo "</pre>";
    }
} else {
    echo "<p style='color:red;'>❌ INSERT gagal</p>";
    echo "<pre>";
    echo json_encode($insertResult, JSON_PRETTY_PRINT);
    echo "</pre>";
}

// Test 4: Retrieve latest record
echo "<h2>4. Test SELECT iklan terbaru (tampilkan=true, order by id desc)</h2>";
$latestResult = supabaseSelect('iklan', ['tampilkan' => 'eq.true', 'order' => 'id.desc', 'limit' => 1]);
if ($latestResult['success'] && !empty($latestResult['data'])) {
    echo "<p style='color:green;'>✅ SELECT latest berhasil</p>";
    echo "<pre>";
    echo json_encode($latestResult['data'][0], JSON_PRETTY_PRINT);
    echo "</pre>";
} else {
    echo "<p style='color:red;'>❌ SELECT latest gagal atau tidak ada data</p>";
    echo "<pre>";
    echo json_encode($latestResult, JSON_PRETTY_PRINT);
    echo "</pre>";
}

echo "<hr>";
echo "<p><strong>Test Selesai</strong></p>";
echo "<p><a href='test-iklan-supabase.php'>Refresh Test</a> | <a href='admin/dashboard.php'>Kembali ke Dashboard</a></p>";
?>
