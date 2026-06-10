<?php
require_once 'includes/db.php';

echo "<h1>UBAH DOKUMENTASI JADI BERITA</h1>";
echo "<p>Supabase Connected: " . ($supabaseConnected ? "✅ Ya" : "❌ Tidak") . "</p>";

if ($supabaseConnected) {
    // Get all data
    $result = supabaseSelect('berita', ['order' => 'tanggal.desc']);
    if ($result['success']) {
        $data = $result['data'];
        
        if (!empty($data)) {
            echo "<h2>Pilih data mana yang ingin diubah jadi BERITA:</h2>";
            echo "<form method='post'>";
            
            foreach ($data as $index => $item) {
                $isDocumentation = !empty($item['jenis_dokumentasi']);
                $isPublished = ($item['status'] ?? 'published') === 'published';
                
                $class = $isDocumentation ? 'style="background: #fff3e0; padding: 10px; margin: 5px 0; border-left: 4px solid #ff9800;"' : 'style="background: #e3f2fd; padding: 10px; margin: 5px 0; border-left: 4px solid #2196f3;"';
                
                echo "<div $class>";
                if ($isDocumentation) {
                    echo "<input type='checkbox' name='convert_ids[]' value='" . htmlspecialchars($item['id']) . "'> ";
                }
                echo "<strong>" . htmlspecialchars($item['judul']) . "</strong> | ";
                echo $isDocumentation ? "📷 Dokumentasi (" . htmlspecialchars($item['jenis_dokumentasi']) . ")" : "📰 Berita (" . htmlspecialchars($item['kategori'] ?? 'Umum') . ")";
                echo " | " . ($isPublished ? "✅ Published" : "❌ Draft");
                echo "</div>";
            }
            
            echo "<hr><button type='submit' name='convert' style='padding:10px 20px; background: #3b82f6; color: white; border:0; border-radius:4px; cursor:pointer;'>Ubah Jadi Berita!</button>";
            echo "</form>";
            
            // Handle conversion
            if (isset($_POST['convert']) && isset($_POST['convert_ids']) && !empty($_POST['convert_ids'])) {
                echo "<hr><h3>Hasil Konversi:</h3>";
                
                foreach ($_POST['convert_ids'] as $id) {
                    // Update: remove jenis_dokumentasi and set kategori to 'Umum'
                    $updateData = [
                        'jenis_dokumentasi' => null,
                        'kategori' => 'Umum'
                    ];
                    
                    $updateResult = supabaseUpdate('berita', $updateData, $id);
                    
                    echo "<p>ID $id: " . ($updateResult['success'] ? "✅ Berhasil diubah jadi Berita!" : "❌ Gagal - " . print_r($updateResult, true)) . "</p>";
                }
                
                echo "<p><a href='index.php'>Lihat Hasil di Index</a></p>";
            }
        }
    }
}

echo "<hr><a href='index.php'>Kembali</a> | <a href='debug-database.php'>Debug Database</a>";
?>