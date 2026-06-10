<?php
require_once 'includes/db.php';

echo "<h1>FORCE ADD BERITA!</h1>";
echo "<p>Supabase Connected: " . ($supabaseConnected ? 'Ya' : 'Tidak') . "</p>";

if ($supabaseConnected) {
    $beritaContoh = [
        [
            'judul' => 'Selamat Datang di Website SLB-C YPSLB Gemolong',
            'slug' => 'selamat-datang-' . time(),
            'konten' => 'Selamat datang di website resmi SLB-C YPSLB Gemolong. Website ini merupakan portal informasi untuk seluruh civitas akademika dan masyarakat umum.',
            'gambar' => 'https://picsum.photos/seed/berita1/800/400.jpg',
            'kategori' => 'Pengumuman',
            'status' => 'published',
            'tanggal' => date('Y-m-d'),
            'tanggal_upload' => date('Y-m-d')
        ],
        [
            'judul' => 'Pendaftaran Siswa Baru Tahun 2025',
            'slug' => 'pendaftaran-2025-' . time(),
            'konten' => 'Pendaftaran siswa baru tahun ajaran 2025/2026 telah dibuka! Silakan kunjungi halaman PPDB untuk informasi lebih lanjut.',
            'gambar' => 'https://picsum.photos/seed/berita2/800/400.jpg',
            'kategori' => 'PPDB',
            'status' => 'published',
            'tanggal' => date('Y-m-d'),
            'tanggal_upload' => date('Y-m-d')
        ]
    ];

    foreach ($beritaContoh as $index => $berita) {
        $insertResult = supabaseInsert('berita', $berita);
        echo "<p>Insert Berita " . ($index + 1) . ": " . ($insertResult['success'] ? "✅ BERHASIL" : "❌ GAGAL - " . print_r($insertResult, true)) . "</p>";
        error_log("Insert Result " . ($index + 1) . ": " . print_r($insertResult, true));
    }

    // Now fetch them to verify
    echo "<hr><h2>Verifikasi Data di Database:</h2>";
    $beritaResult = supabaseSelect('berita', ['order' => 'tanggal.desc']);
    if ($beritaResult['success']) {
        echo "<p>Total data di tabel berita: " . count($beritaResult['data']) . "</p>";
        echo "<pre>" . htmlspecialchars(print_r($beritaResult['data'], true)) . "</pre>";
    }

    echo "<p><a href='index.php'>Kembali ke Halaman Utama</a></p>";
}
?>