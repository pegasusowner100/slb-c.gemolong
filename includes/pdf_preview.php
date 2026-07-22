<?php
// Simple PDF -> JPEG preview generator (cached).
// Usage: pdf_preview.php?url=<url-to-pdf>

if (php_sapi_name() === 'cli') exit("No CLI");

// Basic input
$url = $_GET['url'] ?? '';
if (empty($url)) {
    http_response_code(400);
    echo 'Missing url';
    exit;
}

// allow only http(s)
if (!preg_match('#^https?://#i', $url)) {
    http_response_code(400);
    echo 'Invalid url';
    exit;
}

$cacheDir = __DIR__ . '/../uploads/pdf_previews';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);

$hash = md5($url);
$outPath = $cacheDir . '/' . $hash . '.jpg';

// If cached, serve directly
if (file_exists($outPath) && filesize($outPath) > 0) {
    header('Content-Type: image/jpeg');
    header('Cache-Control: public, max-age=86400');
    readfile($outPath);
    exit;
}

// Download PDF to temp file
$tmp = tempnam(sys_get_temp_dir(), 'pdf');
$ctx = stream_context_create(['http' => ['timeout' => 10], 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
$pdfData = @file_get_contents($url, false, $ctx);
if ($pdfData === false) {
    http_response_code(502);
    echo 'Failed to download PDF';
    exit;
}
file_put_contents($tmp, $pdfData);

$generated = false;

// Try Imagick if available
if (class_exists('Imagick')) {
    try {
        $im = new Imagick();
        // set resolution for better quality
        $im->setResolution(150, 150);
        $im->readImage($tmp . '[0]');
        $im->setImageFormat('jpeg');
        $im->setImageCompression(Imagick::COMPRESSION_JPEG);
        $im->setImageCompressionQuality(85);
        $im->writeImage($outPath);
        $im->clear();
        $im->destroy();
        $generated = file_exists($outPath);
    } catch (Exception $e) {
        // ignore, fallback later
    }
}

// If Imagick not available or failed, try using Ghostscript via exec (Windows may have gswin64c)
if (!$generated) {
    $gs = trim(shell_exec('where gswin64c 2>NUL')) ?: trim(shell_exec('where gs 2>NUL')) ?: null;
    if ($gs) {
        $cmd = escapeshellcmd($gs) . " -dBATCH -dNOPAUSE -sDEVICE=jpeg -r150 -dFirstPage=1 -dLastPage=1 -sOutputFile=" . escapeshellarg($outPath) . ' ' . escapeshellarg($tmp);
        @exec($cmd, $o, $rc);
        if ($rc === 0 && file_exists($outPath)) $generated = true;
    }
}

// Cleanup temp
@unlink($tmp);

if ($generated && file_exists($outPath)) {
    header('Content-Type: image/jpeg');
    header('Cache-Control: public, max-age=86400');
    readfile($outPath);
    exit;
}

// Fallback: return 1x1 transparent GIF so image tag doesn't break layout
header('Content-Type: image/gif');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
exit;
