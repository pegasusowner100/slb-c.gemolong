<?php
require_once 'config.php';

function supabaseStorageSafeFileName($name) {
    $name = basename((string) $name);
    $extension = pathinfo($name, PATHINFO_EXTENSION);
    $baseName = pathinfo($name, PATHINFO_FILENAME);
    $baseName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $baseName);
    $baseName = trim($baseName, '-_');

    if ($baseName === '') {
        $baseName = 'file';
    }

    $extension = preg_replace('/[^A-Za-z0-9]+/', '', $extension);
    return $extension === '' ? $baseName : $baseName . '.' . strtolower($extension);
}

function supabaseStorageEncodePath($path) {
    $segments = explode('/', str_replace('\\', '/', (string) $path));
    $segments = array_filter($segments, function ($segment) {
        return $segment !== '';
    });

    return implode('/', array_map('rawurlencode', $segments));
}

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
    $fileName = uniqid('', true) . '_' . supabaseStorageSafeFileName($file['name'] ?? 'file');
    $filePath = $folder ? $folder . '/' . $fileName : $fileName;
    $encodedBucket = rawurlencode($bucket);
    $encodedPath = supabaseStorageEncodePath($filePath);

    $url = rtrim(SUPABASE_URL, '/') . '/storage/v1/object/' . $encodedBucket . '/' . $encodedPath;
    $fileContent = file_get_contents($file['tmp_name']);
    if ($fileContent === false) {
        return [
            'success' => false,
            'url' => null,
            'error' => 'Gagal membaca file upload.'
        ];
    }

    $storageKey = defined('SUPABASE_SERVICE_KEY') && !empty(SUPABASE_SERVICE_KEY) ? SUPABASE_SERVICE_KEY : SUPABASE_KEY;
    $apiKeyHeader = $storageKey;

    $contentType = !empty($file['type']) ? $file['type'] : 'application/octet-stream';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $storageKey,
        'apikey: ' . $apiKeyHeader,
        'Content-Type: ' . $contentType,
        'Content-Length: ' . $file['size']
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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
        $publicUrl = rtrim(SUPABASE_URL, '/') . '/storage/v1/object/public/' . $encodedBucket . '/' . $encodedPath;
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
