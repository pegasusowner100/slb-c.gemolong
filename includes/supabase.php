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
require_once __DIR__ . '/config.php';
define('SUPABASE_FUNCTIONS_LOADED', true);

// Fungsi untuk request ke Supabase
if (!function_exists('supabaseRequest')) {
function supabaseRequest($method, $table, $data = null, $filters = []) {
    $url = SUPABASE_URL . '/rest/v1/' . $table;

    // Tambahkan filter ke URL
    if (!empty($filters)) {
        $url .= '?' . http_build_query($filters);
    }

    // Decide initial auth key: prefer service key for all operations if available
    $useServiceKey = defined('SUPABASE_SERVICE_KEY') && !empty(SUPABASE_SERVICE_KEY);
    $initialAuthKey = $useServiceKey ? SUPABASE_SERVICE_KEY : (defined('SUPABASE_KEY') ? SUPABASE_KEY : '');

    // Helper to perform the HTTP request with a given key
    $doRequest = function($authKey) use ($url, $method, $data) {
        $ch = curl_init();
        $headers = [
            'apikey: ' . $authKey,
            'Authorization: Bearer ' . $authKey,
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
        $curlError = curl_error($ch);
        curl_close($ch);

        $decoded = json_decode($response, true);
        $errorMessage = $curlError;
        if (!$curlError && isset($decoded['message'])) {
            $errorMessage = $decoded['message'];
        } elseif (!$curlError && isset($decoded['msg'])) {
            $errorMessage = $decoded['msg'];
        } elseif (!$curlError && is_array($decoded) && !empty($decoded)) {
            $errorMessage = json_encode($decoded);
        }

        $isSuccessHttp = $httpCode >= 200 && $httpCode < 300;

        // If performing POST or PATCH and PostgREST returned an empty body/array,
        // this often means the operation was blocked by RLS or no rows were affected.
        $representationEmpty = ($decoded === null) || (is_array($decoded) && empty($decoded));
        if (($method === 'POST' || $method === 'PATCH') && $isSuccessHttp && $representationEmpty) {
            // Treat as failure so callers can detect and surface a clear error
            return [
                'success' => false,
                'data' => $decoded,
                'code' => $httpCode,
                'error' => 'Empty response from Supabase for ' . $method . ' — possible RLS restriction or no rows affected.'
            ];
        }

        return [
            'success' => $isSuccessHttp,
            'data' => $decoded,
            'code' => $httpCode,
            'error' => $errorMessage
        ];
    };

    // First attempt (likely service key for writes)
    $first = $doRequest($initialAuthKey);

    // If service key returned 401 (invalid), retry with anon/public key as fallback
    if ($first['code'] === 401 && $useServiceKey && defined('SUPABASE_KEY') && !empty(SUPABASE_KEY)) {
        $second = $doRequest(SUPABASE_KEY);
        // Return second attempt result (successful or failed)
        return $second;
    }

    return $first;
}

// Fungsi untuk mengambil data dari Supabase
function supabaseSelect($table, $filters = []) {
    $result = supabaseRequest('GET', $table, null, $filters);
    return $result;
}

function supabaseRemoveUnknownColumns($payload, $errorMessage) {
    $payload = is_array($payload) ? $payload : [];
    $errorMessage = strtolower((string) $errorMessage);
    if (strpos($errorMessage, 'column') === false || strpos($errorMessage, 'does not exist') === false) {
        return null;
    }

    preg_match_all('/column "([^"]+)"/', $errorMessage, $matches);
    $unknownColumns = array_unique($matches[1] ?? []);
    if (empty($unknownColumns)) {
        return null;
    }

    $cleanPayload = $payload;
    foreach ($unknownColumns as $column) {
        unset($cleanPayload[$column]);
    }

    return $cleanPayload;
}

// Fungsi untuk menambah data ke Supabase
function supabaseInsert($table, $data) {
    $payload = is_array($data) ? $data : [];
    $result = supabaseRequest('POST', $table, $payload);

    if ($result['success']) {
        return $result;
    }

    $errorText = strtolower((string)($result['error'] ?? ''));
    if ((strpos($errorText, 'row-level security') !== false || strpos($errorText, 'violates row-level security policy') !== false) && !(defined('SUPABASE_SERVICE_KEY') && SUPABASE_SERVICE_KEY)) {
        return [
            'success' => false,
            'data' => $result['data'],
            'code' => $result['code'],
            'error' => 'Supabase write failed because SUPABASE_SERVICE_KEY is not defined. Set the service role key in includes/config.php or SUPABASE_SERVICE_KEY environment variable.'
        ];
    }

    $cleanPayload = supabaseRemoveUnknownColumns($payload, $result['error'] ?? json_encode($result));
    if ($cleanPayload !== null && count($cleanPayload) !== count($payload)) {
        return supabaseRequest('POST', $table, $cleanPayload);
    }

    return $result;
}

// Fungsi untuk mengupdate data di Supabase
function supabaseUpdate($table, $data, $id) {
    $payload = is_array($data) ? $data : [];
    $result = supabaseRequest('PATCH', $table, $payload, ['id' => 'eq.' . $id]);

    if ($result['success']) {
        return $result;
    }

    $cleanPayload = supabaseRemoveUnknownColumns($payload, $result['error'] ?? json_encode($result));
    if ($cleanPayload !== null && count($cleanPayload) !== count($payload)) {
        $retry = supabaseRequest('PATCH', $table, $cleanPayload, ['id' => 'eq.' . $id]);
        if ($retry['success']) {
            return $retry;
        }
        $result = $retry;
    }

    // Some Supabase/PostgREST setups may return a successful HTTP 200 with an empty array
    // on PATCH even when the row exists. Verify the row by ID.
    if ($result['code'] >= 200 && $result['code'] < 300 && is_array($result['data']) && empty($result['data'])) {
        $verify = supabaseSelect($table, ['id' => 'eq.' . $id, 'limit' => 1]);
        if ($verify['success'] && !empty($verify['data'])) {
            return [
                'success' => true,
                'data' => $verify['data'],
                'code' => $result['code'],
                'error' => ''
            ];
        }
        return [
            'success' => false,
            'data' => $result['data'],
            'code' => $result['code'],
            'error' => 'PATCH returned empty response and the row could not be verified. Possible RLS restriction or missing row.'
        ];
    }

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

    $useAnonKey = defined('SUPABASE_KEY') && !empty(SUPABASE_KEY);
    $useServiceKey = defined('SUPABASE_SERVICE_KEY') && !empty(SUPABASE_SERVICE_KEY);

    $makeRequest = function($authKey) use ($url) {
        $ch = curl_init();
        $headers = [
            'apikey: ' . $authKey,
            'Authorization: Bearer ' . $authKey,
            'Content-Type: application/json',
            'Accept: application/json',
            'Prefer: count=exact',
            'Cache-Control: no-cache'
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

        return [
            'response' => $response,
            'code' => $httpCode,
            'headerSize' => $headerSize,
            'error' => $error
        ];
    };

    if ($useAnonKey) {
        $result = $makeRequest(SUPABASE_KEY);
    } elseif ($useServiceKey) {
        $result = $makeRequest(SUPABASE_SERVICE_KEY);
    } else {
        return [
            'success' => false,
            'count' => null,
            'data' => null,
            'code' => 0,
            'error' => 'No Supabase key defined'
        ];
    }

    if ($result['code'] === 401 && $useServiceKey && $useAnonKey) {
        $result = $makeRequest(SUPABASE_SERVICE_KEY);
    }

    $count = null;
    $decodedBody = null;
    if ($result['response'] !== false && $result['headerSize']) {
        $header = substr($result['response'], 0, $result['headerSize']);
        if (preg_match('/Content-Range:\s*\d+-\d+\/(\d+)/i', $header, $m)) {
            $count = intval($m[1]);
        }
        $body = substr($result['response'], $result['headerSize']);
        $decodedBody = json_decode($body, true);
        if ($count === null && is_array($decodedBody)) {
            $count = count($decodedBody);
        }
    }

    if ($count === null && $result['code'] >= 200 && $result['code'] < 300) {
        $fallback = supabaseSelect($table, $filters);
        if ($fallback['success'] && is_array($fallback['data'])) {
            $count = count($fallback['data']);
            $decodedBody = $fallback['data'];
        }
    }

    return [
        'success' => $result['code'] >= 200 && $result['code'] < 300,
        'count' => $count,
        'data' => $decodedBody,
        'code' => $result['code'],
        'error' => $result['error']
    ];
}

// Cek apakah sebuah kolom ada pada tabel (mengirim request select ke kolom)
if (!function_exists('supabaseHasColumn')) {
    function supabaseHasColumn($table, $column) {
        $res = supabaseSelect($table, ['select' => $column, 'limit' => 1]);
        if ($res['success']) return true;
        $err = strtolower($res['error'] ?? '');
        if (strpos($err, $column) !== false || strpos($err, 'column') !== false) return false;
        // Jika gagal tanpa pesan kolom, anggap kolom tidak ada untuk safety
        return false;
    }
}

}

