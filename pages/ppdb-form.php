<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

$title = "Form Pendaftaran PPDB — " . SITE_NAME;
$page = 'ppdb';
$success = false;
$error = '';
$no_reg = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi input
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $nisn = trim($_POST['nisn'] ?? '');
    $tempat_lahir = trim($_POST['tempat_lahir'] ?? '');
    $tanggal_lahir = trim($_POST['tanggal_lahir'] ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
    $agama = trim($_POST['agama'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $sekolah_asal = trim($_POST['sekolah_asal'] ?? '');
    $tahun_lulusan = trim($_POST['tahun_lulusan'] ?? '');
    $nama_ayah = trim($_POST['nama_ayah'] ?? '');
    $pekerjaan_ayah = trim($_POST['pekerjaan_ayah'] ?? '');
    $nama_ibu = trim($_POST['nama_ibu'] ?? '');
    $pekerjaan_ibu = trim($_POST['pekerjaan_ibu'] ?? '');
    $no_hp_ortu = trim($_POST['no_hp_ortu'] ?? '');

    // Validasi field wajib
    if (empty($nama_lengkap)) {
        $error = 'Nama lengkap harus diisi!';
    } elseif (empty($tanggal_lahir)) {
        $error = 'Tanggal lahir harus diisi!';
    } elseif (empty($jenis_kelamin)) {
        $error = 'Jenis kelamin harus dipilih!';
    } elseif (empty($alamat)) {
        $error = 'Alamat harus diisi!';
    } elseif (empty($sekolah_asal)) {
        $error = 'Sekolah asal harus diisi!';
    } elseif (empty($nama_ayah)) {
        $error = 'Nama ayah harus diisi!';
    } elseif (empty($nama_ibu)) {
        $error = 'Nama ibu harus diisi!';
    } elseif (empty($no_hp_ortu)) {
        $error = 'Nomor HP orang tua harus diisi!';
    } elseif (!preg_match('/^(\+62|62|0)[0-9]{9,12}$/', $no_hp_ortu)) {
        $error = 'Nomor HP tidak valid!';
    } else {
        // Generate nomor registrasi
        $no_reg = 'PPDB-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Insert ke database
        if ($supabaseConnected) {
            $data = [
                'no_reg' => $no_reg,
                'nama_lengkap' => $nama_lengkap,
                'nisn' => $nisn ?: null,
                'tempat_lahir' => $tempat_lahir ?: null,
                'tanggal_lahir' => $tanggal_lahir,
                'jenis_kelamin' => $jenis_kelamin,
                'agama' => $agama ?: null,
                'alamat' => $alamat,
                'sekolah_asal' => $sekolah_asal,
                'tahun_lulusan' => $tahun_lulusan ?: null,
                'nama_ayah' => $nama_ayah,
                'pekerjaan_ayah' => $pekerjaan_ayah ?: null,
                'nama_ibu' => $nama_ibu,
                'pekerjaan_ibu' => $pekerjaan_ibu ?: null,
                'no_hp_ortu' => $no_hp_ortu,
                'status' => 'pending'
            ];

            $result = supabaseInsert('ppdb', $data);

            if ($result['success']) {
                $success = true;
            } else {
                $error = 'Gagal menyimpan data. Silakan coba lagi.';
            }
        } else {
            $error = 'Koneksi database tidak tersedia.';
        }
    }
}

include '../components/head.php';
?>
<body class="bg-brand-bg text-brand-dark font-sans">
    <?php include '../components/navbar.php'; ?>

    <!-- Page Header -->
    <section class="page-hero bg-brand-dark">
        <div class="max-w-7xl mx-auto px-6">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-brand-label fade-in-up block">Pendaftaran</span>
            <h1 class="font-serif text-3xl md:text-4xl font-normal tracking-tight text-white leading-[1.1] fade-in-up delay-100 -mt-2">Form Pendaftaran PPDB</h1>
        </div>
    </section>

    <!-- Form Section -->
    <section class="py-12 md:py-24 bg-white">
        <div class="max-w-4xl mx-auto px-6">
            <?php if ($success): ?>
                <!-- Success Message -->
                <div class="bg-green-50 border-2 border-green-500 rounded-xl p-8 mb-8">
                    <div class="flex items-start gap-4">
                        <iconify-icon icon="lucide:check-circle" class="w-6 h-6 text-green-600 flex-shrink-0 mt-1"></iconify-icon>
                        <div>
                            <h2 class="text-2xl font-bold text-green-700 mb-2">Pendaftaran Berhasil!</h2>
                            <p class="text-green-700 mb-4">Terima kasih telah mendaftar di SLB-C YPSLB Gemolong. Data Anda telah kami terima.</p>
                            <div class="bg-white rounded-lg p-4 mb-4 border border-green-300">
                                <p class="text-sm text-gray-600 mb-1">Nomor Registrasi Anda:</p>
                                <p class="text-2xl font-bold text-green-700"><?php echo htmlspecialchars($no_reg); ?></p>
                                <p class="text-sm text-gray-600 mt-2">Simpan nomor ini untuk keperluan verifikasi</p>
                            </div>
                            <p class="text-sm text-green-700">
                                Tim kami akan menghubungi Anda melalui nomor yang Anda daftarkan untuk tahap selanjutnya.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <a href="ppdb.php" class="inline-flex items-center gap-2 px-8 py-3 bg-brand-accent text-white font-bold text-sm uppercase tracking-widest rounded-lg hover:bg-brand-secondary transition-colors">
                        Kembali ke Halaman PPDB
                        <iconify-icon icon="lucide:arrow-right" class="w-5 h-5"></iconify-icon>
                    </a>
                </div>
            <?php else: ?>
                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="bg-red-50 border-2 border-red-500 rounded-xl p-4 mb-8 flex items-start gap-3">
                        <iconify-icon icon="lucide:alert-circle" class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5"></iconify-icon>
                        <div>
                            <p class="text-red-700 font-semibold"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <form method="POST" action="" class="space-y-8">
                    <!-- Data Pribadi -->
                    <div class="bg-gray-50 rounded-xl p-6 md:p-8">
                        <h2 class="font-bold text-xl text-gray-800 mb-6 flex items-center gap-2">
                            <iconify-icon icon="lucide:user" class="w-5 h-5"></iconify-icon>
                            Data Pribadi
                        </h2>

                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Nama Lengkap -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($_POST['nama_lengkap'] ?? ''); ?>" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="Masukkan nama lengkap Anda">
                            </div>

                            <!-- NISN -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">NISN</label>
                                <input type="text" name="nisn" value="<?php echo htmlspecialchars($_POST['nisn'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="10 digit NISN">
                            </div>

                            <!-- Jenis Kelamin -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Jenis Kelamin <span class="text-red-500">*</span>
                                </label>
                                <select name="jenis_kelamin" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20">
                                    <option value="">-- Pilih Jenis Kelamin --</option>
                                    <option value="Laki-laki" <?php echo ($_POST['jenis_kelamin'] ?? '') === 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                                    <option value="Perempuan" <?php echo ($_POST['jenis_kelamin'] ?? '') === 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                                </select>
                            </div>

                            <!-- Tempat Lahir -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" value="<?php echo htmlspecialchars($_POST['tempat_lahir'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="Kota/Kabupaten">
                            </div>

                            <!-- Tanggal Lahir -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Tanggal Lahir <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="tanggal_lahir" value="<?php echo htmlspecialchars($_POST['tanggal_lahir'] ?? ''); ?>" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20">
                            </div>

                            <!-- Agama -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Agama</label>
                                <select name="agama"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20">
                                    <option value="">-- Pilih Agama --</option>
                                    <option value="Islam" <?php echo ($_POST['agama'] ?? '') === 'Islam' ? 'selected' : ''; ?>>Islam</option>
                                    <option value="Kristen" <?php echo ($_POST['agama'] ?? '') === 'Kristen' ? 'selected' : ''; ?>>Kristen</option>
                                    <option value="Katolik" <?php echo ($_POST['agama'] ?? '') === 'Katolik' ? 'selected' : ''; ?>>Katolik</option>
                                    <option value="Hindu" <?php echo ($_POST['agama'] ?? '') === 'Hindu' ? 'selected' : ''; ?>>Hindu</option>
                                    <option value="Buddha" <?php echo ($_POST['agama'] ?? '') === 'Buddha' ? 'selected' : ''; ?>>Buddha</option>
                                    <option value="Konghucu" <?php echo ($_POST['agama'] ?? '') === 'Konghucu' ? 'selected' : ''; ?>>Konghucu</option>
                                </select>
                            </div>

                            <!-- Alamat -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Alamat <span class="text-red-500">*</span>
                                </label>
                                <textarea name="alamat" required rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                          placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten"><?php echo htmlspecialchars($_POST['alamat'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Data Pendidikan -->
                    <div class="bg-gray-50 rounded-xl p-6 md:p-8">
                        <h2 class="font-bold text-xl text-gray-800 mb-6 flex items-center gap-2">
                            <iconify-icon icon="lucide:book-open" class="w-5 h-5"></iconify-icon>
                            Data Pendidikan
                        </h2>

                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Sekolah Asal -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Sekolah Asal <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="sekolah_asal" value="<?php echo htmlspecialchars($_POST['sekolah_asal'] ?? ''); ?>" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="Nama sekolah asal">
                            </div>

                            <!-- Tahun Lulusan -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Lulusan</label>
                                <input type="text" name="tahun_lulusan" value="<?php echo htmlspecialchars($_POST['tahun_lulusan'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="Contoh: 2023" maxlength="4">
                            </div>
                        </div>
                    </div>

                    <!-- Data Orang Tua/Wali -->
                    <div class="bg-gray-50 rounded-xl p-6 md:p-8">
                        <h2 class="font-bold text-xl text-gray-800 mb-6 flex items-center gap-2">
                            <iconify-icon icon="lucide:users" class="w-5 h-5"></iconify-icon>
                            Data Orang Tua/Wali
                        </h2>

                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Nama Ayah -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nama Ayah <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nama_ayah" value="<?php echo htmlspecialchars($_POST['nama_ayah'] ?? ''); ?>" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="Nama ayah kandung">
                            </div>

                            <!-- Pekerjaan Ayah -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Pekerjaan Ayah</label>
                                <input type="text" name="pekerjaan_ayah" value="<?php echo htmlspecialchars($_POST['pekerjaan_ayah'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="Pekerjaan ayah">
                            </div>

                            <!-- Nama Ibu -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nama Ibu <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nama_ibu" value="<?php echo htmlspecialchars($_POST['nama_ibu'] ?? ''); ?>" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="Nama ibu kandung">
                            </div>

                            <!-- Pekerjaan Ibu -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Pekerjaan Ibu</label>
                                <input type="text" name="pekerjaan_ibu" value="<?php echo htmlspecialchars($_POST['pekerjaan_ibu'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="Pekerjaan ibu">
                            </div>

                            <!-- No HP Orang Tua -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nomor HP Orang Tua <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" name="no_hp_ortu" value="<?php echo htmlspecialchars($_POST['no_hp_ortu'] ?? ''); ?>" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/20"
                                       placeholder="Contoh: 0812345678 atau +6212345678">
                                <p class="text-xs text-gray-600 mt-1">Format: 0812... atau +6212...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4 justify-center">
                        <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 bg-brand-accent text-white font-bold text-sm uppercase tracking-widest rounded-lg hover:bg-brand-secondary transition-colors shadow-lg hover:shadow-xl">
                            <iconify-icon icon="lucide:send" class="w-5 h-5"></iconify-icon>
                            Kirim Pendaftaran
                        </button>
                        <a href="ppdb.php" class="inline-flex items-center gap-2 px-8 py-3 bg-gray-300 text-gray-700 font-bold text-sm uppercase tracking-widest rounded-lg hover:bg-gray-400 transition-colors">
                            Batal
                        </a>
                    </div>

                    <p class="text-xs text-gray-600 text-center">
                        <span class="text-red-500">*</span> Bidang wajib diisi
                    </p>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <?php include '../components/footer.php'; ?>
</body>
</html>
