<?php
/**
 * Diagnostic Script - Check Pengumuman Database
 * Untuk debug apakah PDF URL tersimpan di database
 */

require_once 'includes/session.php';
require_once 'includes/db.php';

// Hanya admin yang bisa akses
if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user'] !== ADMIN_USERNAME) {
    die('❌ Akses ditolak. Login sebagai admin terlebih dahulu.');
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Database Pengumuman</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">🔍 Database Pengumuman Diagnostic</h1>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <?php
            // Check Supabase connection
            $result = supabaseSelect('pengumuman', ['order' => 'created_at.desc', 'limit' => 10]);
            
            if (!$result['success']) {
                echo '<div class="bg-red-100 border border-red-400 text-red-700 p-4 rounded mb-6">';
                echo '❌ <strong>Database Error:</strong> ' . htmlspecialchars($result['error'] ?? 'Unknown error');
                echo '</div>';
            } else {
                $data = $result['data'] ?? [];
                
                if (empty($data)) {
                    echo '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 p-4 rounded mb-6">';
                    echo '⚠️ <strong>No Data:</strong> Tidak ada pengumuman di database.';
                    echo '</div>';
                } else {
                    echo '<div class="bg-green-100 border border-green-400 text-green-700 p-4 rounded mb-6">';
                    echo '✅ <strong>Connection Success:</strong> Database terhubung. ' . count($data) . ' pengumuman ditemukan.';
                    echo '</div>';
                    
                    // Display table with all fields
                    echo '<div class="overflow-x-auto mb-6">';
                    echo '<table class="w-full border border-gray-300">';
                    echo '<thead class="bg-gray-200">';
                    echo '<tr>';
                    echo '<th class="border p-2 text-left">ID</th>';
                    echo '<th class="border p-2 text-left">Nomor</th>';
                    echo '<th class="border p-2 text-left">Judul</th>';
                    echo '<th class="border p-2 text-left">PDF Field (DB)</th>';
                    echo '<th class="border p-2 text-left">Status</th>';
                    echo '<th class="border p-2 text-left">Created At</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    foreach ($data as $p) {
                        $pdf_value = $p['pdf'] ?? 'NULL';
                        $pdf_status = empty($pdf_value) ? '❌ KOSONG' : '✅ ADA';
                        
                        echo '<tr class="border hover:bg-gray-50">';
                        echo '<td class="border p-2 text-sm font-mono">' . htmlspecialchars($p['id'] ?? '-') . '</td>';
                        echo '<td class="border p-2 text-sm">' . htmlspecialchars($p['no'] ?? '-') . '</td>';
                        echo '<td class="border p-2 text-sm">' . htmlspecialchars($p['judul'] ?? '-') . '</td>';
                        echo '<td class="border p-2 text-sm">';
                        
                        if (empty($pdf_value)) {
                            echo '<span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">NULL/KOSONG</span>';
                        } else {
                            echo '<div class="space-y-1">';
                            echo '<div><strong>Value:</strong> ' . htmlspecialchars(substr($pdf_value, 0, 60)) . (strlen($pdf_value) > 60 ? '...' : '') . '</div>';
                            echo '<div><strong>Type:</strong> ' . (filter_var($pdf_value, FILTER_VALIDATE_URL) ? 'Valid URL' : 'Not a URL') . '</div>';
                            
                            // Check if URL is accessible
                            if (filter_var($pdf_value, FILTER_VALIDATE_URL)) {
                                echo '<a href="' . htmlspecialchars($pdf_value) . '" target="_blank" class="text-emerald-600 hover:underline text-xs">👁️ Preview</a>';
                            }
                            echo '</div>';
                        }
                        
                        echo '</td>';
                        echo '<td class="border p-2 text-sm">' . $pdf_status . '</td>';
                        echo '<td class="border p-2 text-sm font-mono text-xs">' . htmlspecialchars(substr($p['created_at'] ?? '-', 0, 19)) . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                    
                    // Summary
                    $with_pdf = count(array_filter($data, fn($p) => !empty($p['pdf'])));
                    $without_pdf = count($data) - $with_pdf;
                    
                    echo '<div class="grid grid-cols-2 gap-4">';
                    echo '<div class="bg-green-50 border border-green-200 rounded p-4">';
                    echo '<div class="text-2xl font-bold text-green-700">' . $with_pdf . '</div>';
                    echo '<div class="text-sm text-green-600">Pengumuman dengan PDF</div>';
                    echo '</div>';
                    echo '<div class="bg-red-50 border border-red-200 rounded p-4">';
                    echo '<div class="text-2xl font-bold text-red-700">' . $without_pdf . '</div>';
                    echo '<div class="text-sm text-red-600">Pengumuman tanpa PDF</div>';
                    echo '</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
            <h2 class="text-lg font-bold text-blue-900 mb-3">📋 Interpretasi Hasil:</h2>
            <ul class="space-y-2 text-sm text-blue-800">
                <li><strong>Jika PDF Field KOSONG:</strong> File tidak berhasil diupload atau upload gagal tapi form berhasil submit</li>
                <li><strong>Jika PDF Field ADA tapi link mati:</strong> URL disimpan tapi file tidak ada di cloud/storage</li>
                <li><strong>Jika PDF Field ADA dan link hidup:</strong> Upload berhasil, masalah mungkin di display logic</li>
            </ul>
        </div>
        
        <div class="mt-6">
            <a href="admin/kelola-pengumuman.php" class="bg-emerald-600 text-white px-6 py-2 rounded hover:bg-emerald-700 inline-block">← Kembali ke Kelola Pengumuman</a>
        </div>
    </div>
</body>
</html>
