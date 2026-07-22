<?php
require_once 'includes/db.php';

echo "<h1>Testing Berita & Supabase</h1>";
echo "<p>Supabase Connected: " . ($supabaseConnected ? "Ya" : "Tidak") . "</p>";

if ($supabaseConnected) {
    // Cek apakah sudah ada berita (tanpa jenis_dokumentasi)
    $allData = supabaseSelect('berita', ['order' => 'tanggal.desc']);
    $adaBerita = false;
    if ($allData['success']) {
        foreach ($allData['data'] as $item) {
            if (empty($item['jenis_dokumentasi'])) {
                $adaBerita = true;
                break;
            }
        }
    }

    if (!$adaBerita) {
        echo "<h3>Memasukkan Data Berita Contoh...</h3>";
        
        $beritaContoh = [
            [
                'judul' => 'Selamat Datang di Website SLB BC KARYA SEJAHTERA',
                'slug' => 'selamat-datang',
                'konten' => 'Selamat datang di website resmi SLB BC KARYA SEJAHTERA. Website ini merupakan portal informasi untuk seluruh civitas akademika dan masyarakat umum.',
                'gambar' => 'https://picsum.photos/seed/berita1/800/400.jpg',
                'kategori' => 'Pengumuman',
                'status' => 'published',
                'tanggal' => date('Y-m-d'),
                'tanggal_upload' => date('Y-m-d')
            ],
            [
                'judul' => 'Pendaftaran Siswa Baru Tahun 2025',
                'slug' => 'pendaftaran-2025',
                'konten' => 'Pendaftaran siswa baru tahun ajaran 2025/2026 telah dibuka! Silakan kunjungi halaman PPDB untuk informasi lebih lanjut.',
                'gambar' => 'https://picsum.photos/seed/berita2/800/400.jpg',
                'kategori' => 'PPDB',
                'status' => 'published',
                'tanggal' => date('Y-m-d'),
                'tanggal_upload' => date('Y-m-d')
            ],
            [
                'judul' => 'Prestasi Siswa di Tingkat Provinsi',
                'slug' => 'prestasi-provinsi',
                'konten' => 'Alhamdulillah, siswa-siswi SLB BC KARYA SEJAHTERA berhasil meraih prestasi gemilang di tingkat provinsi!',
                'gambar' => 'https://picsum.photos/seed/berita3/800/400.jpg',
                'kategori' => 'Prestasi',
                'status' => 'published',
                'tanggal' => date('Y-m-d'),
                'tanggal_upload' => date('Y-m-d')
            ],
            [
                'judul' => 'Kegiatan Outbound Siswa',
                'slug' => 'kegiatan-outbound',
                'konten' => 'Serunya kegiatan outbound yang diikuti oleh seluruh siswa SLB BC KARYA SEJAHTERA!',
                'gambar' => 'https://picsum.photos/seed/berita4/800/400.jpg',
                'kategori' => 'Kegiatan',
                'status' => 'published',
                'tanggal' => date('Y-m-d'),
                'tanggal_upload' => date('Y-m-d')
            ]
        ];
        
        foreach ($beritaContoh as $berita) {
            $insertResult = supabaseInsert('berita', $berita);
            echo "<p>Insert " . $berita['judul'] . ": " . ($insertResult['success'] ? "✅ Berhasil" : "❌ Gagal - " . print_r($insertResult, true)) . "</p>";
        }
        
        echo "<p><strong>Data berita berhasil ditambahkan!</strong></p>";
        echo "<p><a href='index.php'>Kembali ke Halaman Utama</a></p>";
    } else {
        echo "<h3>✅ Data Berita Sudah Ada!</h3>";
        echo "<p><a href='index.php'>Kembali ke Halaman Utama</a></p>";
    }
}
?>
