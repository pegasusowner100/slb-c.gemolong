<?php
/**
 * Script untuk memeriksa struktur database Supabase dan membandingkannya dengan kode PHP
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/supabase.php';

echo "<h1>Pemeriksaan Struktur Database</h1>";

// Daftar tabel yang digunakan di kode PHP (dari analisis Grep)
$tables = [
    'admin', 'anggaran_semester', 'berita', 'download', 'faq',
    'fasilitas', 'galeri', 'guru', 'hero', 'pengumuman', 'ppdb',
    'prestasi', 'program', 'profil_sekolah', 'rencana_anggaran',
    'rencana_program', 'siswa', 'surat', 'testimoni', 'video'
];

// Fungsi untuk mendapatkan semua kolom dari tabel di Supabase
function getSupabaseTableColumns($tableName) {
    $url = SUPABASE_URL . '/rest/v1/' . $tableName;
    $useServiceKey = defined('SUPABASE_SERVICE_KEY') && !empty(SUPABASE_SERVICE_KEY);
    $authKey = $useServiceKey ? SUPABASE_SERVICE_KEY : (defined('SUPABASE_KEY') ? SUPABASE_KEY : '');
    
    $ch = curl_init();
    $headers = [
        'apikey: ' . $authKey,
        'Authorization: Bearer ' . $authKey,
        'Accept: application/vnd.pgrst.schema+json'
    ];
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $schema = json_decode($response, true);
        $columns = [];
        if (isset($schema['properties'])) {
            foreach ($schema['properties'] as $col => $details) {
                $columns[] = $col;
            }
        }
        return ['success' => true, 'columns' => $columns];
    } else {
        return ['success' => false, 'error' => 'HTTP ' . $httpCode . ': ' . $response];
    }
}

// Fungsi untuk mendapatkan kolom yang diharapkan dari tabel dengan melihat file PHP yang menggunakannya
function getExpectedColumns($tableName) {
    $expected = [];
    
    switch ($tableName) {
        case 'profil_sekolah':
            $expected = ['id', 'nama_sekolah', 'alamat', 'telepon', 'email', 'website', 'instagram', 'facebook', 'youtube', 'tiktok', 'maps_url', 'nama_kepala_sekolah', 'foto_kepala_sekolah', 'logo_url', 'created_at', 'updated_at'];
            break;
        case 'hero':
            $expected = ['id', 'judul', 'subjudul', 'foto_url', 'video_url', 'created_at', 'updated_at'];
            break;
        case 'berita':
            $expected = ['id', 'judul', 'isi', 'tanggal', 'foto_url', 'slug', 'ringkasan', 'status', 'created_at', 'updated_at'];
            break;
        case 'pengumuman':
            $expected = ['id', 'judul', 'isi', 'tanggal', 'foto_url', 'file_url', 'status', 'created_at', 'updated_at'];
            break;
        case 'guru':
            $expected = ['id', 'nama', 'jabatan', 'foto_url', 'urutan', 'created_at', 'updated_at'];
            break;
        case 'siswa':
            $expected = ['id', 'nama', 'no_induk', 'status', 'foto_url', 'kelas', 'created_at', 'updated_at'];
            break;
        case 'program':
            $expected = ['id', 'nama', 'deskripsi', 'foto_url', 'urutan', 'status', 'created_at', 'updated_at'];
            break;
        case 'fasilitas':
            $expected = ['id', 'nama', 'deskripsi', 'foto_url', 'urutan', 'status', 'created_at', 'updated_at'];
            break;
        case 'galeri':
            $expected = ['id', 'judul', 'deskripsi', 'foto_url', 'tanggal_upload', 'urutan', 'status', 'created_at', 'updated_at'];
            break;
        case 'prestasi':
            $expected = ['id', 'nama', 'deskripsi', 'tahun', 'foto_url', 'created_at', 'updated_at'];
            break;
        case 'faq':
            $expected = ['id', 'pertanyaan', 'jawaban', 'urutan', 'status', 'created_at', 'updated_at'];
            break;
        case 'download':
            $expected = ['id', 'nama', 'deskripsi', 'file_url', 'urutan', 'status', 'created_at', 'updated_at'];
            break;
        case 'video':
            $expected = ['id', 'judul', 'url', 'tanggal', 'urutan', 'status', 'created_at', 'updated_at'];
            break;
        case 'surat':
            $expected = ['id', 'nama_pengirim', 'email', 'perihal', 'isi', 'file_url', 'status', 'respon', 'created_at', 'updated_at'];
            break;
        case 'ppdb':
            $expected = ['id', 'nama_lengkap', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'alamat', 'no_telepon', 'email', 'nama_ortu', 'asal_sekolah', 'status', 'created_at', 'updated_at'];
            break;
        case 'anggaran_semester':
            $expected = ['id', 'tahun', 'semester', 'judul', 'file_url', 'urutan', 'status', 'created_at', 'updated_at'];
            break;
        case 'rencana_anggaran':
            $expected = ['id', 'judul', 'file_url', 'status', 'created_at', 'updated_at'];
            break;
        case 'rencana_program':
            $expected = ['id', 'nama', 'deskripsi', 'file_url', 'urutan', 'status', 'created_at', 'updated_at'];
            break;
        case 'testimoni':
            $expected = ['id', 'nama', 'isi', 'foto_url', 'urutan', 'status', 'created_at', 'updated_at'];
            break;
        case 'admin':
            $expected = ['id', 'username', 'password_hash', 'password_salt', 'created_at', 'updated_at'];
            break;
    }
    
    return $expected;
}

// Periksa setiap tabel
foreach ($tables as $table) {
    echo "<h2>Tabel: <strong>" . htmlspecialchars($table) . "</strong></h2>";
    
    $dbResult = getSupabaseTableColumns($table);
    $expectedColumns = getExpectedColumns($table);
    
    if ($dbResult['success']) {
        $dbColumns = $dbResult['columns'];
        echo "<p><strong>Kolom di Database:</strong> " . implode(', ', $dbColumns) . "</p>";
        echo "<p><strong>Kolom yang Diharapkan:</strong> " . implode(', ', $expectedColumns) . "</p>";
        
        $missing = array_diff($expectedColumns, $dbColumns);
        $extra = array_diff($dbColumns, $expectedColumns);
        
        if (!empty($missing)) {
            echo "<p style='color: red;'><strong>Kolom yang Hilang di Database:</strong> " . implode(', ', $missing) . "</p>";
        }
        if (!empty($extra)) {
            echo "<p style='color: blue;'><strong>Kolom Tambahan di Database:</strong> " . implode(', ', $extra) . "</p>";
        }
        if (empty($missing) && empty($extra)) {
            echo "<p style='color: green;'><strong>✓ Struktur tabel sesuai!</strong></p>";
        }
    } else {
        echo "<p style='color: red;'><strong>Gagal mengambil struktur tabel:</strong> " . htmlspecialchars($dbResult['error']) . "</p>";
    }
    echo "<hr>";
}

echo "<h1>Selesai</h1>";
?>