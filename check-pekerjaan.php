<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

echo "Checking database contents...\n";

if (!$supabaseConnected) {
    die("Supabase not connected!\n");
}

// Get all siswa
$result = supabaseSelect('siswa', ['order' => 'no_induk.asc']);
if (!$result['success']) {
    die("Error getting siswa: " . ($result['error'] ?? 'Unknown') . "\n");
}

echo "Total siswa in DB: " . count($result['data']) . "\n\n";

// Count pekerjaan
$pekerjaanCounts = [];
foreach ($result['data'] as $s) {
    $p = $s['pekerjaan_ortu'] ?? '(not set)';
    if (!isset($pekerjaanCounts[$p])) {
        $pekerjaanCounts[$p] = 0;
    }
    $pekerjaanCounts[$p]++;
}

echo "Pekerjaan counts:\n";
foreach ($pekerjaanCounts as $p => $c) {
    echo "  - \"$p\": $c\n";
}
?>