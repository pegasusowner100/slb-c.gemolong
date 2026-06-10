<?php
require_once 'includes/db.php';

echo "<!DOCTYPE html><html><head><style>
    body { font-family: Arial; max-width: 1200px; margin: 20px auto; padding: 0 20px; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background-color: #f2f2f2; }
    .draft { background-color: #fff3cd; }
    .published { background-color: #d4edda; }
    .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; margin: 2px; }
    .btn-publish { background-color: #28a745; color: white; }
    .btn-draft { background-color: #ffc107; color: black; }
    .btn-berita { background-color: #007bff; color: white; }
    .btn-dokumentasi { background-color: #fd7e14; color: white; }
    .btn-edit { background-color: #17a2b8; color: white; }
    .form-inline { display: inline; }
</style></head><body>";

echo "<h1>MANAJEMEN BERITA & DOKUMENTASI</h1>";
echo "<p><a href='index.php'>← Kembali ke Index</a> | <a href='admin/kelola-berita.php'>Admin Panel</a></p>";

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['toggle_status'])) {
        // Toggle status between draft/published
        $newStatus = ($_POST['current_status'] == 'published') ? 'draft' : 'published';
        $result = supabaseUpdate('berita', ['status' => $newStatus], $_POST['id']);
        echo "<p style='color: green;'>✅ Status berhasil diubah jadi " . strtoupper($newStatus) . "!</p>";
    } elseif (isset($_POST['set_type'])) {
        // Set type (Berita/Dokumentasi)
        if ($_POST['type'] == 'berita') {
            $updateData = ['jenis_dokumentasi' => null, 'kategori' => $_POST['kategori'] ?? 'Umum'];
        } else {
            $updateData = ['jenis_dokumentasi' => $_POST['jenis_dokumentasi'] ?? 'Photo', 'kategori' => null];
        }
        $result = supabaseUpdate('berita', $updateData, $_POST['id']);
        echo "<p style='color: green;'>✅ Tipe berhasil diubah!</p>";
    } elseif (isset($_POST['update'])) {
        // Update data
        $updateData = [
            'judul' => $_POST['judul'],
            'konten' => $_POST['konten'],
            'gambar' => $_POST['gambar']
        ];
        $result = supabaseUpdate('berita', $updateData, $_POST['id']);
        echo "<p style='color: green;'>✅ Data berhasil diupdate!</p>";
    }
}

if ($supabaseConnected) {
    $result = supabaseSelect('berita', ['order' => 'tanggal.desc']);
    if ($result['success']) {
        $data = $result['data'];
        echo "<h2>Total Data: " . count($data) . "</h2>";
        
        echo "<table>";
        echo "<tr>
            <th>Judul</th>
            <th>Tipe</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Aksi</th>
        </tr>";
        
        foreach ($data as $item) {
            $isDocumentation = !empty($item['jenis_dokumentasi']);
            $status = $item['status'] ?? 'published';
            $rowClass = ($status == 'draft') ? 'draft' : 'published';
            $typeText = $isDocumentation ? "Dokumentasi (" . ($item['jenis_dokumentasi'] ?? 'Photo') . ")" : "Berita (" . ($item['kategori'] ?? 'Umum') . ")";
            
            echo "<tr class='$rowClass'>";
            echo "<td><strong>" . htmlspecialchars($item['judul']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($typeText) . "</td>";
            echo "<td style='font-weight:bold;'>" . strtoupper(htmlspecialchars($status)) . "</td>";
            echo "<td>" . htmlspecialchars($item['tanggal'] ?? $item['tanggal_upload'] ?? '-') . "</td>";
            echo "<td>";
            
            // Toggle Status
            echo "<form class='form-inline' method='post'>
                <input type='hidden' name='id' value='" . htmlspecialchars($item['id']) . "'>
                <input type='hidden' name='current_status' value='" . htmlspecialchars($status) . "'>
                <button type='submit' name='toggle_status' class='btn " . ($status == 'published' ? 'btn-draft' : 'btn-publish') . "'>
                    " . ($status == 'published' ? 'Jadikan Draft' : 'Publish') . "
                </button>
            </form> ";
            
            // Set Type
            echo "<form class='form-inline' method='post'>
                <input type='hidden' name='id' value='" . htmlspecialchars($item['id']) . "'>
                <select name='type'>
                    <option value='berita'>Berita</option>
                    <option value='dokumentasi'>Dokumentasi</option>
                </select>
                <select name='kategori' style='display:none;'>
                    <option>Umum</option><option>Pengumuman</option><option>PPDB</option><option>Prestasi</option><option>Kegiatan</option>
                </select>
                <select name='jenis_dokumentasi' style='display:none;'>
                    <option>Photo</option><option>Video</option>
                </select>
                <button type='submit' name='set_type' class='btn btn-dokumentasi'>Set Tipe</button>
            </form> ";
            
            // Edit
            echo "<button class='btn btn-edit' onclick='toggleEdit(\"" . htmlspecialchars($item['id']) . "\")'>Edit</button>";
            
            echo "</td>";
            echo "</tr>";
            
            // Edit Form (hidden by default)
            echo "<tr id='edit-" . htmlspecialchars($item['id']) . "' style='display:none;'>
                <td colspan='5'>
                    <form method='post'>
                        <input type='hidden' name='id' value='" . htmlspecialchars($item['id']) . "'>
                        <p><label>Judul:</label><br><input type='text' name='judul' value='" . htmlspecialchars($item['judul']) . "' style='width:100%; padding:8px;'></p>
                        <p><label>Konten:</label><br><textarea name='konten' style='width:100%; height:100px; padding:8px;'>" . htmlspecialchars($item['konten'] ?? '') . "</textarea></p>
                        <p><label>Gambar:</label><br><input type='text' name='gambar' value='" . htmlspecialchars($item['gambar'] ?? '') . "' style='width:100%; padding:8px;'></p>
                        <button type='submit' name='update' class='btn btn-edit'>Simpan Perubahan</button>
                    </form>
                </td>
            </tr>";
        }
        
        echo "</table>";
    }
}

echo "<script>
function toggleEdit(id) {
    var row = document.getElementById('edit-' + id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
</script>";

echo "</body></html>";
?>