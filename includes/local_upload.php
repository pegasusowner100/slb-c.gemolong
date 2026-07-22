<?php

if (!defined('LOCAL_UPLOAD_ENABLED') || !LOCAL_UPLOAD_ENABLED) {
    return;
}

function ensureDirectoryExists($path) {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

ensureDirectoryExists(LOCAL_UPLOAD_PUBLIC_DIR);
ensureDirectoryExists(LOCAL_UPLOAD_PRIVATE_DIR);

function localUploadPath($file, $visibility = 'public') {
    $dir = $visibility === 'private' ? LOCAL_UPLOAD_PRIVATE_DIR : LOCAL_UPLOAD_PUBLIC_DIR;
    $fileName = uniqid('', true) . '_' . basename($file['name']);
    return $dir . '/' . $fileName;
}

function localUploadUrl($path) {
    if (strpos($path, LOCAL_UPLOAD_PUBLIC_DIR) === 0) {
        $relative = substr($path, strlen(LOCAL_UPLOAD_PUBLIC_DIR));
        $relative = ltrim($relative, '/');
        $segments = explode('/', $relative);
        $encodedSegments = array_map('rawurlencode', $segments);
        return rtrim(LOCAL_UPLOAD_BASE_URL_PUBLIC, '/') . '/' . implode('/', $encodedSegments);
    }
    return null;
}

function uploadToLocal($file, $visibility = 'public') {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'url' => null,
            'error' => 'Upload file gagal: kode error ' . $file['error']
        ];
    }

    $allowedPublicExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'mp4', 'webm', 'pdf', 'mp3', 'wav', 'ogg', 'm4a', 'aac'];
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($visibility === 'public' && !in_array($fileExt, $allowedPublicExtensions, true)) {
        return [
            'success' => false,
            'url' => null,
            'error' => 'Tipe file tidak diizinkan untuk upload publik.'
        ];
    }

    $targetPath = localUploadPath($file, $visibility);
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [
            'success' => false,
            'url' => null,
            'error' => 'Gagal memindahkan file ke folder upload lokal.'
        ];
    }

    $url = null;
    if ($visibility === 'public') {
        $url = localUploadUrl($targetPath);
    }

    return [
        'success' => true,
        'url' => $url,
        'path' => $targetPath,
        'visibility' => $visibility
    ];
}

function getLocalUploadFile($fileName) {
    $privatePath = LOCAL_UPLOAD_PRIVATE_DIR . '/' . basename($fileName);
    $publicPath = LOCAL_UPLOAD_PUBLIC_DIR . '/' . basename($fileName);

    if (file_exists($privatePath)) {
        return ['path' => $privatePath, 'visibility' => 'private'];
    }
    if (file_exists($publicPath)) {
        return ['path' => $publicPath, 'visibility' => 'public'];
    }
    return null;
}

?>