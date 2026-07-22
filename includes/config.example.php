<?php
/**
 * Configuration Example File
 * 
 * Copy file ini menjadi config.php dan isi dengan credential Anda
 * DO NOT upload config.php ke GitHub - gunakan file ini sebagai template
 */

// ==========================================
// Website Configuration
// ==========================================
define('SITE_NAME', 'SLB BC KARYA SEJAHTERA');
define('BASE_URL', '/'); // Tanpa trailing slash; gunakan '/' untuk root deployment

// ==========================================
// Supabase Configuration (Online Database)
// Dapatkan dari https://supabase.com
// ==========================================
define('SUPABASE_URL', 'https://YOUR_SUPABASE_URL.supabase.co');
define('SUPABASE_KEY', 'YOUR_SUPABASE_PUBLISHABLE_KEY');
define('SUPABASE_SERVICE_KEY', 'YOUR_SUPABASE_SERVICE_ROLE_KEY');

// ==========================================
// Cloudinary Configuration (Image/Video Storage)
// Dapatkan dari https://cloudinary.com
// ==========================================
define('CLOUDINARY_CLOUD_NAME', 'your_cloud_name');
define('CLOUDINARY_API_KEY', 'your_api_key');
define('CLOUDINARY_API_SECRET', 'your_api_secret');
define('CLOUDINARY_FOLDER', 'folder_name');
define('CLOUDINARY_UPLOAD_PRESET', 'your_upload_preset');


// ==========================================
// Supabase Storage Configuration
// ==========================================
define('SUPABASE_STORAGE_BUCKET', 'bucket_name');

define('LOCAL_UPLOAD_ENABLED', true);
define('LOCAL_UPLOAD_PUBLIC_DIR', __DIR__ . '/../uploads/public');
define('LOCAL_UPLOAD_PRIVATE_DIR', __DIR__ . '/../uploads/private');
define('LOCAL_UPLOAD_BASE_URL_PUBLIC', (BASE_URL === '' ? '' : rtrim(BASE_URL, '/')) . '/uploads/public');

define('DEFAULT_PUBLIC_AUDIO_URL', ''); // Optional: default public audio URL when admin has not set a custom audio

// ==========================================
// Admin Credentials
// GANTI SEBELUM PRODUCTION!
// ==========================================
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_SALT', 'your_unique_salt_here');
define('ADMIN_PASSWORD_HASH', 'your_hashed_password_here');

// ==========================================
// ASSETS_URL derived from BASE_URL
// ==========================================
if (!defined('ASSETS_URL')) {
    $base = BASE_URL;
    $base = rtrim($base, '/');
    if ($base === '') $base = '/';
    define('ASSETS_URL', ($base === '/' ? '' : $base) . '/assets');
}

require_once __DIR__ . '/local_upload.php';

// ==========================================
// Helper Functions
// ==========================================

/**
 * Process video links (YouTube, Vimeo, direct MP4)
 */
function getVideoEmbed($url, $width = '100%', $height = '100%') {
    if (empty($url)) return '';
    
    // YouTube
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
        $videoId = $matches[1];
        return '<iframe src="https://www.youtube.com/embed/' . $videoId . '?autoplay=1&mute=1&loop=1&playlist=' . $videoId . '" width="' . $width . '" height="' . $height . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="border-radius: 0.5rem; object-fit: cover;"></iframe>';
    }
    
    // Vimeo
    if (preg_match('/vimeo\.com\/(?:.*\/)?(\d+)/', $url, $matches)) {
        $videoId = $matches[1];
        return '<iframe src="https://player.vimeo.com/video/' . $videoId . '?autoplay=1&muted=1&loop=1&title=0&byline=0&portrait=0" width="' . $width . '" height="' . $height . '" frameborder="0" allow="autoplay; fullscreen" allowfullscreen style="border-radius: 0.5rem; object-fit: cover;"></iframe>';
    }
    
    // Direct video file (MP4, WebM, etc)
    return '<video autoplay muted loop playsinline src="' . htmlspecialchars($url) . '" style="width: ' . $width . '; height: ' . $height . '; object-fit: cover; border-radius: 0.5rem;"></video>';
}

if (!function_exists('resolveAbsoluteUrl')) {
    function resolveAbsoluteUrl($url) {
        if (empty($url)) {
            return '';
        }

        $url = trim($url);
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        if (strpos($url, '//') === 0) {
            return 'https:' . $url;
        }

        if (strpos($url, '/') === 0) {
            $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
            return ($base === '' ? '' : $base) . $url;
        }

        if (defined('LOCAL_UPLOAD_BASE_URL_PUBLIC') && LOCAL_UPLOAD_BASE_URL_PUBLIC !== '') {
            return rtrim(LOCAL_UPLOAD_BASE_URL_PUBLIC, '/') . '/' . ltrim($url, '/');
        }

        return $url;
    }
}
?>
