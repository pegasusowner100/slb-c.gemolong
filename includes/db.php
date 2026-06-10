
<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/supabase.php';

// Cek koneksi Supabase
$supabaseConnected = false;
$testConn = supabaseSelect('profil_sekolah', ['limit' => 1]);
if ($testConn['success']) {
    $supabaseConnected = true;
}

// Default profil data
$defaultProfil = [
    'nama_sekolah' => SITE_NAME,
    'alamat' => 'Jl. Sukowati KM.2 Gemolong, Kec. Gemolong, Kab. Sragen, Prov. Jawa Tengah',
    'telepon' => '081 329 009 325',
    'email' => 'slbc.gemolong@yahoo.com',
    'website' => '',
    'instagram' => '',
    'facebook' => '',
    'youtube' => '',
    'tiktok' => '',
    'maps_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.5791243516182!2d110.83888377588161!3d-7.400964072874847!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a0e565247cb35%3A0xe39650dfb842d995!2sSLB-B%20YPSLB%20Gemolong!5e0!3m2!1sid!2sid!4v1780726871503!5m2!1sid!2sid',
    'nama_kepala_sekolah' => 'Drs. Ahmad Sudrajat, M.Pd',
    'foto_kepala_sekolah' => 'https://picsum.photos/seed/kepsek-portrait/480/600.jpg'
    ,
    'logo_url' => ''
];

// Ambil profil sekolah dari database
$profilSekolah = $defaultProfil;
if ($supabaseConnected) {
    $profilResult = supabaseSelect('profil_sekolah', ['id' => 'eq.1', 'limit' => 1]);
    if ($profilResult['success'] && !empty($profilResult['data'])) {
        $dbProfil = $profilResult['data'][0];
        // Merge database data with defaults, but keep defaults for empty fields like maps_url
        foreach ($defaultProfil as $key => $value) {
            if (isset($dbProfil[$key]) && trim((string) $dbProfil[$key]) !== '') {
                $profilSekolah[$key] = $dbProfil[$key];
            } else {
                $profilSekolah[$key] = $defaultProfil[$key];
            }
        }
    }
}

// Fungsi global untuk eksekusi query (Supabase sebagai primary)
if (!function_exists('query')) {
    function query($sql, $params = []) {
        global $supabaseConnected;
        
        // Untuk kebutuhan backward compatibility, kita bisa handle sederhana
        // Atau, lebih baik menggunakan fungsi supabaseSelect, supabaseInsert, dll secara langsung
        return false;
    }
}
?>

