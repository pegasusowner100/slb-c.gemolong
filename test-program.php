<?php
require_once 'includes/db.php';

echo "=== TEST SUPABASE CONNECTION ===\n";
echo "Connected: " . ($supabaseConnected ? "YES" : "NO") . "\n";

if ($supabaseConnected) {
    // Try to ALTER TABLE to add gambar column
    echo "\n--- ATTEMPTING TO ADD 'gambar' COLUMN ---\n";
    // Wait, can't ALTER TABLE via REST API! Need to do in Supabase SQL Editor!
    echo "NOTE: Please run this SQL in your Supabase SQL Editor:\n";
    echo "ALTER TABLE program ADD COLUMN IF NOT EXISTS gambar VARCHAR(255);\n\n";
    
    // Now try inserting a test program!
    echo "--- INSERTING TEST PROGRAM ---\n";
    $data = [
        'icon' => 'lucide:book-open',
        'nama' => 'Test Program Akademik',
        'deskripsi' => 'Ini adalah test program untuk debugging!',
        'gambar' => 'https://picsum.photos/seed/program-test/600/400',
        'urutan' => 1
    ];
    $insertResult = supabaseInsert('program', $data);
    echo "Insert Result:\n";
    var_dump($insertResult);
    echo "\n";

    // Now get all programs!
    echo "--- GETTING ALL PROGRAMS ---\n";
    $programResult = supabaseSelect('program', ['order' => 'urutan.asc']);
    echo "Program Select Result:\n";
    var_dump($programResult);
    echo "\n";
    
    $programs = [];
    if ($programResult['success']) {
        $programs = $programResult['data'];
        echo "Found " . count($programs) . " programs:\n";
        foreach ($programs as $p) {
            echo "- " . ($p['nama'] ?? 'No Name') . " (ID: " . ($p['id'] ?? 'No ID') . ")\n";
            echo "  Gambar: " . ($p['gambar'] ?? 'No Gambar') . "\n";
        }
    }
}
?>