<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';
require_once 'includes/cloudinary-on.php';

echo "=== Test Pengumuman PDF Upload ===\n\n";

// Check if Cloudinary is configured
echo "1. Check Cloudinary Config:\n";
echo "   CLOUDINARY_CLOUD_NAME: " . (defined('CLOUDINARY_CLOUD_NAME') ? CLOUDINARY_CLOUD_NAME : 'NOT SET') . "\n";
echo "   CLOUDINARY_UPLOAD_PRESET: " . (defined('CLOUDINARY_UPLOAD_PRESET') ? CLOUDINARY_UPLOAD_PRESET : 'NOT SET') . "\n";
echo "   CLOUDINARY_API_KEY: " . (defined('CLOUDINARY_API_KEY') ? '***' : 'NOT SET') . "\n";
echo "   CLOUDINARY_FOLDER: " . (defined('CLOUDINARY_FOLDER') ? CLOUDINARY_FOLDER : 'NOT SET') . "\n\n";

// Check if function exists
echo "2. Check uploadToCloudinary function:\n";
echo "   Function exists: " . (function_exists('uploadToCloudinary') ? 'YES' : 'NO') . "\n\n";

// Test with a dummy PDF file
echo "3. Create dummy PDF and test upload:\n";
$testPdfPath = sys_get_temp_dir() . '/test-pengumuman-' . uniqid() . '.pdf';
file_put_contents($testPdfPath, '%PDF-1.4 test pdf content');

$file = [
    'name' => 'test-pengumuman.pdf',
    'type' => 'application/pdf',
    'tmp_name' => $testPdfPath,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($testPdfPath)
];

echo "   Test file: $testPdfPath\n";
echo "   File size: " . $file['size'] . " bytes\n";

if (function_exists('uploadToCloudinary')) {
    $result = uploadToCloudinary($file, 'pengumuman-test');
    echo "\n   Upload Result:\n";
    echo "   - Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
    echo "   - URL: " . ($result['url'] ?? 'N/A') . "\n";
    echo "   - Error: " . ($result['error'] ?? 'N/A') . "\n";
    if (isset($result['public_id'])) {
        echo "   - Public ID: " . $result['public_id'] . "\n";
    }
} else {
    echo "   ERROR: uploadToCloudinary function not found!\n";
}

// Cleanup
if (file_exists($testPdfPath)) {
    unlink($testPdfPath);
    echo "\n4. Cleanup: Test file deleted.\n";
}
?>
