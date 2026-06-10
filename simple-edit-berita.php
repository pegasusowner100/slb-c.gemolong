<?php
require_once 'includes/db.php';

echo "<!DOCTYPE html><html><head><style>
    body { font-family: Arial; max-width: 1000px; margin: 20px auto; padding: 0 20px; }
    .card { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 8px; }
    .berita { background: #e3f2fd; }
    .dokumentasi { background: #fff3e0; }
    button { padding: 8px 15px; margin: 5px; border: 0; border-radius: 4px; cursor: pointer; }
    .btn-berita { background: #2196f3; color: white; }
    .btn-dokumentasi { background: #ff9800; color: white; }
    .btn-delete { background: #f44336; color: white; }
    textarea { width: 100%; min-height: 80px; }
    input { padding: 8px; margin: 5px 0; }
</style></head><body>";

echo "<h1>EDIT DATA BERITA & DOKUMENTASI</h1>";
echo "<p><a href='index.php'>← Kembali ke Index</a> | <a href='debug-database.php'>Debug Database</a></p>";

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['set_berita'])) {
        // Set as Berita: remove jenis_dokumentasi, add kategori
        $updateData = [
            'jenis_dokumentasi' => null,
            'kategori' => $_POST['kategori'] ?? 'Umum'
        ];
        $result = supabaseUpdate('berita', $updateData, $_POST['id']);
        echo "<p style='color: green;'>✅ Berhasil diubah jadi Berita!</p>";
    } elseif (isset($_POST['set_dokumentasi'])) {
        // Set as Dokumentasi: set jenis_dokumentasi
        $updateData = [
            'jenis_dokumentasi' => $_POST['jenis_dokumentasi'] ?? 'Photo',
            'kategori' => null
        ];
        $result = supabaseUpdate('berita', $updateData, $_POST['id']);
        echo "<p style='color: green;'>✅ Berhasil diubah jadi Dokumentasi!</p>";
    } elseif (isset($_POST['update_data'])) {
        // Update basic data
        $updateData = [
            'judul' => $_POST['judul'],
            'konten' => $_POST['konten'],
            'gambar' => $_POST['gambar']
        ];
        $result = supabaseUpdate('berita', $updateData, $_POST['id']);
        echo "<p style='color: green;'>✅ Data berhasil diupdate!</p>";
    }
}

// Get all data
if ($supabaseConnected) {
    $result = supabaseSelect('berita', ['order' => 'tanggal.desc']);
    if ($result['success']) {
        $data = $result['data'];
        
        echo "<h2>Total Data: " . count($data) . "</h2>";
        
        foreach ($data as $item) {
            $isDocumentation = !empty($item['jenis_dokumentasi']);
            $class = $isDocumentation ? 'dokumentasi' : 'berita';
            $type = $isDocumentation ? "Dokumentasi (" . ($item['jenis_dokumentasi'] ?? 'Photo') . ")" : "Berita (" . ($item['kategori'] ?? 'Umum') . ")";
            
            echo "<div class='card $class'>";
            echo "<h3>" . htmlspecialchars($item['judul']) . " <small>($type)</small></h3>";
            echo "<p><strong>ID:</strong> " . htmlspecialchars($item['id']) . "</p>";
            
            // Edit Form
            echo "<form method='post'>";
            echo "<input type='hidden' name='id' value='" . htmlspecialchars($item['id']) . "'>";
            
            echo "<p><strong>Judul:</strong><br><input type='text' name='judul' value='" . htmlspecialchars($item['judul']) . "' style='width:100%;'></p>";
            echo "<p><strong>Konten:</strong><br><textarea name='konten'>" . htmlspecialchars($item['konten'] ?? '') . "</textarea></p>";
            echo "<p><strong>Gambar:</strong><br><input type='text' name='gambar' value='" . htmlspecialchars($item['gambar'] ?? '') . "' style='width:100%;'></p>";
            
            echo "<button type='submit' name='update_data' style='background:#4caf50;color:white;'>Update Data</button> ";
            
            echo "<hr><strong>Ubah Tipe:</strong><br>";
            echo "<select name='kategori'>
                <option value='Umum'>Umum</option>
                <option value='Pengumuman'>Pengumuman</option>
                <option value='PPDB'>PPDB</option>
                <option value='Prestasi'>Prestasi</option>
                <option value='Kegiatan'>Kegiatan</option>
            </select> ";
            echo "<button type='submit' name='set_berita' class='btn-berita'>Jadikan Berita</button> ";
            
            echo "<select name='jenis_dokumentasi'>
                <option value='Photo'>Photo</option>
                <option value='Video'>Video</option>
            </select> ";
            echo "<button type='submit' name='set_dokumentasi' class='btn-dokumentasi'>Jadikan Dokumentasi</button>";
            
            echo "</form>";
            
            // Show preview
            if (!empty($item['gambar'])) {
                echo "<br><br><img src='" . htmlspecialchars($item['gambar']) . "' style='max-width:300px; max-height:200px; object-cover; border-radius:4px;'>";
            }
            
            echo "</div>";
        }
    }
}

echo "</body></html>";
?>