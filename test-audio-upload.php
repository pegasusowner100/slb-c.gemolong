<?php
/**
 * TEST: Audio Upload & Config Save
 * Gunakan file ini untuk memverifikasi bahwa upload audio berfungsi dengan benar
 */

require_once 'includes/config.php';
require_once 'includes/public_audio.php';
require_once 'includes/local_upload.php';

$results = [];

// Check 1: Folder upload public exists
$uploadDir = LOCAL_UPLOAD_PUBLIC_DIR;
$results['upload_dir_exists'] = is_dir($uploadDir);
$results['upload_dir_writable'] = is_writable($uploadDir);
$results['upload_dir_path'] = $uploadDir;

// Check 2: Config file
$configPath = getPublicAudioConfigPath();
$results['config_path'] = $configPath;
$results['config_exists'] = file_exists($configPath);
if (file_exists($configPath)) {
    $config = loadPublicAudioConfig();
    $results['config_data'] = $config;
}

// Check 3: Current audio URL
$currentUrl = getPublicAudioUrl();
$results['current_audio_url'] = $currentUrl;
$results['audio_enabled'] = isPublicAudioEnabled();

// Check 4: List files in upload dir
$files = [];
if (is_dir($uploadDir)) {
    $files = array_diff(scandir($uploadDir), ['.', '..']);
}
$results['files_in_upload_dir'] = $files;

// Check 5: Test URL generation
if (!empty($currentUrl)) {
    $results['url_scheme'] = parse_url($currentUrl, PHP_URL_SCHEME);
    $results['url_path'] = parse_url($currentUrl, PHP_URL_PATH);
}

// Check 6: BASE_URL config
$results['base_url'] = BASE_URL;
$results['local_upload_base_url_public'] = LOCAL_UPLOAD_BASE_URL_PUBLIC;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Audio Upload Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; }
        .result { margin: 15px 0; padding: 10px; border-left: 4px solid #ccc; background: #f9f9f9; }
        .result.success { border-left-color: #4CAF50; background: #e8f5e9; }
        .result.error { border-left-color: #f44336; background: #ffebee; }
        .result.warning { border-left-color: #ff9800; background: #fff3e0; }
        .key { font-weight: bold; color: #555; }
        .value { color: #666; word-break: break-all; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .test-status { font-size: 18px; font-weight: bold; margin: 20px 0; }
        .audio-player { margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔊 Audio Upload & Configuration Test</h1>
        
        <?php if ($results['audio_enabled']): ?>
            <div class="test-status" style="color: #4CAF50;">✅ Audio Diaktifkan</div>
            <div class="audio-player">
                <p>Audio yang sedang aktif:</p>
                <audio controls style="width: 100%; margin: 10px 0;">
                    <source src="<?php echo htmlspecialchars($currentUrl); ?>" type="audio/mpeg">
                    Browser Anda tidak mendukung audio player
                </audio>
                <p><strong>URL:</strong> <?php echo htmlspecialchars($currentUrl); ?></p>
            </div>
        <?php else: ?>
            <div class="test-status" style="color: #ff9800;">⚠️ Audio Tidak Diaktifkan / Kosong</div>
        <?php endif; ?>

        <h2>Hasil Pemeriksaan</h2>

        <div class="result <?php echo $results['upload_dir_exists'] ? 'success' : 'error'; ?>">
            <span class="key">Folder Upload Publik Exists:</span>
            <span class="value"><?php echo $results['upload_dir_exists'] ? '✅ YES' : '❌ NO'; ?></span>
            <br><span class="key">Path:</span> <span class="value"><?php echo htmlspecialchars($results['upload_dir_path']); ?></span>
        </div>

        <div class="result <?php echo $results['upload_dir_writable'] ? 'success' : 'error'; ?>">
            <span class="key">Folder Upload Writable:</span>
            <span class="value"><?php echo $results['upload_dir_writable'] ? '✅ YES' : '❌ NO'; ?></span>
            <?php if (!$results['upload_dir_writable'] && $results['upload_dir_exists']): ?>
                <br><span style="color: red; font-size: 12px;">Folder ada tapi tidak bisa ditulis. Cek permission folder uploads/public</span>
            <?php endif; ?>
        </div>

        <div class="result <?php echo $results['config_exists'] ? 'success' : 'warning'; ?>">
            <span class="key">Config File Exists:</span>
            <span class="value"><?php echo $results['config_exists'] ? '✅ YES' : '⚠️ NO (akan dibuat saat upload)'; ?></span>
            <br><span class="key">Path:</span> <span class="value"><?php echo htmlspecialchars($results['config_path']); ?></span>
        </div>

        <?php if ($results['config_exists'] && !empty($results['config_data'])): ?>
            <div class="result">
                <span class="key">Config Data:</span>
                <pre><?php echo json_encode($results['config_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?></pre>
            </div>
        <?php endif; ?>

        <div class="result <?php echo $results['audio_enabled'] ? 'success' : 'warning'; ?>">
            <span class="key">Audio Status:</span>
            <span class="value"><?php echo $results['audio_enabled'] ? '✅ ENABLED' : '⚠️ DISABLED/EMPTY'; ?></span>
            <br><span class="key">URL:</span> <span class="value"><?php echo !empty($results['current_audio_url']) ? htmlspecialchars($results['current_audio_url']) : '(kosong)'; ?></span>
        </div>

        <div class="result">
            <span class="key">BASE_URL Config:</span>
            <span class="value"><?php echo htmlspecialchars($results['base_url']); ?></span>
            <br><span class="key">Upload Base URL:</span> <span class="value"><?php echo htmlspecialchars($results['local_upload_base_url_public']); ?></span>
        </div>

        <?php if (!empty($results['files_in_upload_dir'])): ?>
            <div class="result">
                <span class="key">Files dalam folder uploads/public:</span>
                <pre><?php echo htmlspecialchars(implode("\n", $results['files_in_upload_dir'])); ?></pre>
            </div>
        <?php endif; ?>

        <h2>Instruksi Upload Audio</h2>
        <ol>
            <li>Login ke admin → Edit Beranda</li>
            <li>Scroll ke bagian "Audio Publik MP3"</li>
            <li>Pilih file audio MP3 atau masukkan URL audio</li>
            <li>Klik "Simpan Perubahan"</li>
            <li>File audio akan disimpan ke: <code><?php echo htmlspecialchars($results['upload_dir_path']); ?></code></li>
            <li>URL akan otomatis tersimpan di: <code><?php echo htmlspecialchars($results['config_path']); ?></code></li>
            <li>Audio akan diputar otomatis di semua halaman publik (Beranda, dll)</li>
        </ol>

        <h2>Troubleshooting</h2>
        <ul>
            <li><strong>Folder tidak ada:</strong> Cek permission folder root website atau jalankan: <code>mkdir uploads/public</code></li>
            <li><strong>Folder tidak writable:</strong> Ubah permission dengan: <code>chmod 755 uploads/public</code></li>
            <li><strong>Audio tidak diputar:</strong> Lihat console browser untuk error, atau check URL apakah valid</li>
            <li><strong>URL tidak berfungsi:</strong> Pastikan BASE_URL di config.php sudah benar</li>
        </ul>

        <hr>
        <p style="color: #999; font-size: 12px;">
            Test page last generated: <?php echo date('Y-m-d H:i:s'); ?>
        </p>
    </div>
</body>
</html>
