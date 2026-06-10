<?php
require_once 'config.php';

/**
 * Upload file to Supabase Storage
 * @param array $file The $_FILES array entry
 * @param string $folder Optional folder path in the bucket
 * @return array
 */
function uploadToSupabaseStorage($file, $folder = '') {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'url' => null,
            'error' => 'Upload error: ' . $file['error']
        ];
    }

    $bucket = SUPABASE_STORAGE_BUCKET;
    $fileName = uniqid() . '_' . $file['name'];
    $filePath = $folder ? $folder . '/' . $fileName : $fileName;

    $url = SUPABASE_URL . '/storage/v1/object/' . $bucket . '/' . $filePath;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_INFILE, fopen($file['tmp_name'], 'r'));
    curl_setopt($ch, CURLOPT_INFILESIZE, $file['size']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
        'Content-Type: ' . $file['type'],
        'Content-Length: ' . $file['size']
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    // Debug data
    $debug = [
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'response' => $response,
        'result' => $result,
        'url' => $url
    ];

    if ($httpCode === 200 || $httpCode === 201) {
        $publicUrl = SUPABASE_URL . '/storage/v1/object/public/' . $bucket . '/' . $filePath;
        return [
            'success' => true,
            'url' => $publicUrl,
            'data' => $debug
        ];
    }

    return [
        'success' => false,
        'url' => null,
        'error' => $result['message'] ?? $curlError ?? 'Upload failed',
        'data' => $debug
    ];
}
?>