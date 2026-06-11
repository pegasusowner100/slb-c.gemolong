
<?php
if (defined('SUPABASE_FUNCTIONS_LOADED')) {
    return;
}

// Loader: prefer TIDAK DIUPLOAD/supabase.php if present (private copy)
$external = __DIR__ . '/../TIDAK DIUPLOAD/supabase.php';
if (file_exists($external)) {
    require_once $external;
    return;
}

// Fallback to bundled implementation (uses constants from includes/config.php)
require_once 'config.php';
define('SUPABASE_FUNCTIONS_LOADED', true);

// Fungsi untuk request ke Supabase
if (!function_exists('supabaseRequest')) {
function supabaseRequest($method, $table, $data = null, $filters = []) {
    $url = SUPABASE_URL . '/rest/v1/' . $table;
    
    // Tambahkan filter ke URL
    if (!empty($filters)) {
        $url .= '?' . http_build_query($filters);
    }
    
    $ch = curl_init();
    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation',
        'Cache-Control: no-cache'
    ];
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    switch ($method) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case 'PATCH':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $decoded = json_decode($response, true);
    $errorMessage = $error;
    if (!$error && isset($decoded['message'])) {
        $errorMessage = $decoded['message'];
    } elseif (!$error && isset($decoded['msg'])) {
        $errorMessage = $decoded['msg'];
    } elseif (!$error && is_array($decoded) && !empty($decoded)) {
        $errorMessage = json_encode($decoded);
    }
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'data' => $decoded,
        'code' => $httpCode,
        'error' => $errorMessage
    ];
}

// Fungsi untuk mengambil data dari Supabase
function supabaseSelect($table, $filters = []) {
    $result = supabaseRequest('GET', $table, null, $filters);
    return $result;
}

// Fungsi untuk menambah data ke Supabase
function supabaseInsert($table, $data) {
    $result = supabaseRequest('POST', $table, $data);
    return $result;
}

// Fungsi untuk mengupdate data di Supabase
function supabaseUpdate($table, $data, $id) {
    $result = supabaseRequest('PATCH', $table, $data, ['id' => 'eq.' . $id]);
    return $result;
}

// Fungsi untuk menghapus data dari Supabase
function supabaseDelete($table, $id) {
    $result = supabaseRequest('DELETE', $table, null, ['id' => 'eq.' . $id]);
    return $result;
}

// Fungsi untuk mendapatkan jumlah baris yang akurat menggunakan header Content-Range
function supabaseCount($table, $filters = []) {
    $url = SUPABASE_URL . '/rest/v1/' . $table;
    if (!empty($filters)) {
        $url .= '?' . http_build_query($filters);
    }

    $ch = curl_init();
    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: count=exact'
    ];

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $error = curl_error($ch);
    curl_close($ch);

    $count = null;
    if ($response !== false && $headerSize) {
        $header = substr($response, 0, $headerSize);
        if (preg_match('/Content-Range:\s*\d+-\d+\/(\d+)/i', $header, $m)) {
            $count = intval($m[1]);
        }
    }

    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'count' => $count,
        'code' => $httpCode,
        'error' => $error
    ];
}
}
?>

