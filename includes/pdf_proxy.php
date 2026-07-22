<?php
// PHP PDF proxy to serve Cloudinary / remote PDFs inline with correct content-type.
// Usage: includes/pdf_proxy.php?url=<cloudinary-pdf-url>

if (php_sapi_name() === 'cli') exit("No CLI");

$url = $_GET['url'] ?? '';
if (empty($url)) {
    http_response_code(400);
    echo 'Missing url';
    exit;
}

// Validation: Only allow http(s) URLs
if (!preg_match('#^https?://#i', $url)) {
    http_response_code(400);
    echo 'Invalid url';
    exit;
}

// Security check: only allow Cloudinary URLs or local uploads
$allowed_domains = ['res.cloudinary.com', $_SERVER['HTTP_HOST'] ?? 'localhost'];
$parsed_url = parse_url($url);
$host = $parsed_url['host'] ?? '';

$is_allowed = false;
foreach ($allowed_domains as $domain) {
    if ($host === $domain || (strlen($host) > strlen($domain) && substr($host, -strlen($domain) - 1) === '.' . $domain)) {
        $is_allowed = true;
        break;
    }
}

if (!$is_allowed) {
    http_response_code(403);
    echo 'Forbidden: URL domain not allowed';
    exit;
}

// Download and stream the PDF
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$pdfData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && $pdfData !== false) {
    // Set headers to display inline as PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="document.pdf"');
    header('Cache-Control: public, max-age=86400');
    echo $pdfData;
    exit;
}

// Fallback if proxy failed
http_response_code(502);
echo 'Failed to load PDF from source';
exit;
