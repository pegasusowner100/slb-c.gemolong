<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/supabase.php';

// Cek koneksi Supabase dengan beberapa tabel yang umum ada
$tablesToCheck = ['profil_sekolah', 'admin', 'berita', 'pengumuman', 'faq', 'program'];
$supabaseConnected = false;
$testConn = null;
foreach ($tablesToCheck as $table) {
    $testConn = supabaseSelect($table, ['limit' => 1]);
    if (!empty($testConn['success'])) {
        $supabaseConnected = true;
        break;
    }
}
$dbConnected = $supabaseConnected; // Alias untuk kompatibilitas

// Default profil data
$defaultProfil = [
    'nama_sekolah' => '',
    'alamat' => '',
    'telepon' => '',
    'email' => '',
    'website' => '',
    'instagram' => '',
    'facebook' => '',
    'youtube' => '',
    'tiktok' => '',
    'maps_url' => '',
    'nama_kepala_sekolah' => '',
    'foto_kepala_sekolah' => '',
    'logo_url' => ''
];

// Ambil profil sekolah dari Supabase
// By default, show DB values when Supabase is connected. Only use defaults when Supabase is not available.
$profilSekolah = $defaultProfil;
if ($supabaseConnected) {
    $profilResult = supabaseSelect('profil_sekolah', ['id' => 'eq.1', 'limit' => 1]);
    if ((!$profilResult['success'] || empty($profilResult['data'])) && $profilResult['success']) {
        // Try the most recent row if id=1 is not found.
        $profilResult = supabaseSelect('profil_sekolah', ['order' => 'created_at.desc', 'limit' => 1]);
    }

    if ($profilResult['success'] && !empty($profilResult['data'])) {
        $dbProfil = $profilResult['data'][0];
        foreach ($defaultProfil as $key => $value) {
            if (isset($dbProfil[$key]) && trim((string)$dbProfil[$key]) !== '') {
                $profilSekolah[$key] = $dbProfil[$key];
            }
        }
    }
}


