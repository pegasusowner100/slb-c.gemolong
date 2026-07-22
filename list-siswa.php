<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/supabase.php';

$siswa = supabaseSelect('siswa', ['order' => 'no_induk.asc']);
if ($siswa['success']) {
    echo "Total siswa: " . count($siswa['data']) . "\n";
    foreach ($siswa['data'] as $s) {
        echo "{$s['no_induk']} - {$s['nama']} ({$s['jenjang']}, {$s['jenis_kelamin']}, {$s['pekerjaan_ortu']})\n";
    }
}
?>