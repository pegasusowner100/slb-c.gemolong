<?php
require_once 'includes/db.php';

echo "<h1 style='text-align: center; color: #3E6B4E;'>Memasukkan FAQ ke Database</h1>";

if (!$supabaseConnected) {
    die("<p style='color: red; text-align: center;'>❌ Gagal terhubung ke database!</p>");
}

echo "<p style='color: green; text-align: center;'>✅ Database terhubung!</p>";
echo "<hr>";

// Data FAQ dari index.php
$faq_data = [
    [
        'pertanyaan' => 'Apa saja persyaratan untuk mendaftar di SLB BC KARYA SEJAHTERA?',
        'jawaban' => 'Persyaratan pendaftaran antara lain: fotokopi KK, fotokopi akta kelahiran, surat keterangan sehat, dan pas foto. Untuk informasi lebih lengkap, silakan hubungi kami via WhatsApp.',
        'urutan' => 1
    ],
    [
        'pertanyaan' => 'Apakah ada biaya pendidikan di SLB BC KARYA SEJAHTERA?',
        'jawaban' => 'SLB BC KARYA SEJAHTERA adalah sekolah negeri yang mendapatkan bantuan dari pemerintah, sehingga biaya pendidikan sangat terjangkau dan bahkan ada yang gratis untuk siswa yang memenuhi syarat.',
        'urutan' => 2
    ],
    [
        'pertanyaan' => 'Jam berapa operasional sekolah?',
        'jawaban' => 'Jam operasional sekolah adalah Senin - Jumat, pukul 07.00 - 15.00 WIB. Untuk hari Sabtu, sekolah hanya buka untuk kegiatan ekstrakurikuler tertentu.',
        'urutan' => 3
    ],
    [
        'pertanyaan' => 'Bagaimana cara mengajukan surat keterangan?',
        'jawaban' => 'Anda bisa mengajukan surat keterangan melalui formulir surat menyurat di website, atau langsung datang ke kantor sekolah pada jam operasional.',
        'urutan' => 4
    ],
    [
        'pertanyaan' => 'Apakah ada ekstrakurikuler di sekolah?',
        'jawaban' => 'Ya! Ada berbagai ekstrakurikuler seperti olahraga, seni musik, tari, dan keterampilan lainnya untuk mendukung pengembangan potensi siswa.',
        'urutan' => 5
    ],
    [
        'pertanyaan' => 'Bagaimana cara mendapatkan informasi terbaru tentang sekolah?',
        'jawaban' => 'Anda bisa mendapatkan informasi terbaru melalui website resmi sekolah, Instagram, Facebook, atau langsung menghubungi kami via WhatsApp.',
        'urutan' => 6
    ]
];

$success_count = 0;
$error_count = 0;

echo "<h2>Proses Memasukkan FAQ:</h2>";
echo "<ol style='background: white; padding: 20px; border-radius: 8px; list-style-position: inside;'>";

foreach ($faq_data as $faq) {
    // Cek apakah FAQ sudah ada
    $check_result = supabaseSelect('faq', ['pertanyaan' => 'eq.' . urlencode($faq['pertanyaan'])]);
    $exists = false;
    if ($check_result['success'] && !empty($check_result['data'])) {
        $exists = true;
    }

    if ($exists) {
        echo "<li style='color: #F59E0B; padding: 10px 0;'>⚠️ FAQ sudah ada: <strong>" . htmlspecialchars($faq['pertanyaan']) . "</strong></li>";
        $error_count++;
        continue;
    }

    // Masukkan ke database
    $data = [
        'pertanyaan' => $faq['pertanyaan'],
        'jawaban' => $faq['jawaban'],
        'urutan' => $faq['urutan'],
        'status' => 'published'
    ];

    $result = supabaseInsert('faq', $data);

    if ($result['success']) {
        echo "<li style='color: #10B981; padding: 10px 0;'>✅ Berhasil: <strong>" . htmlspecialchars($faq['pertanyaan']) . "</strong></li>";
        $success_count++;
    } else {
        echo "<li style='color: #EF4444; padding: 10px 0;'>❌ Gagal: <strong>" . htmlspecialchars($faq['pertanyaan']) . "</strong><br>Error: " . htmlspecialchars($result['error'] ?? 'Unknown error') . "</li>";
        $error_count++;
    }
}

echo "</ol>";
echo "<hr>";
echo "<h2 style='text-align: center;'>Hasil:</h2>";
echo "<div style='display: flex; justify-content: center; gap: 20px; max-width: 400px; margin: 0 auto;'>";
echo "<div style='background: #10B981; color: white; padding: 20px; border-radius: 8px; flex: 1; text-align: center;'>";
echo "<strong style='font-size: 20px;'>✅ Berhasil</strong><br>";
echo "<span style='font-size: 30px; font-weight: bold;'>" . $success_count . "</span>";
echo "</div>";
echo "<div style='background: #EF4444; color: white; padding: 20px; border-radius: 8px; flex: 1; text-align: center;'>";
echo "<strong style='font-size: 20px;'>⚠️ Sudah Ada/Gagal</strong><br>";
echo "<span style='font-size: 30px; font-weight: bold;'>" . $error_count . "</span>";
echo "</div>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='index.php' style='display: inline-block; background: #3B82F6; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: bold;'>Lihat Hasil di Halaman Utama</a>";
echo " &nbsp; ";
echo "<a href='admin/kelola-faq.php' style='display: inline-block; background: #3E6B4E; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: bold;'>Kelola FAQ di Admin</a>";
echo "</div>";
?>
