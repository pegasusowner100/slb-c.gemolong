# SLB-C YPSLB Gemolong - Website Sekolah

Website resmi SLB-C YPSLB Gemolong dengan fitur lengkap untuk manajemen informasi sekolah.

## 🚀 Fitur Utama

- 📚 **Profil Sekolah** - Informasi lengkap sekolah
- 📰 **Berita & Pengumuman** - Update terbaru
- 🖼️ **Galeri** - Dokumentasi kegiatan
- 📋 **Program Unggulan** - Daftar program sekolah
- 📊 **Anggaran & Belanja** - Transparansi keuangan
- 🎯 **Rencana Program** - Rencana jangka pendek, menengah, panjang
- ⬇️ **Download** - Dokumen penting
- ❓ **FAQ** - Pertanyaan umum
- 📱 **PPDB Online** - Pendaftaran siswa baru
- 📊 **Statistik** - Data sekolah

## 🛠️ Tech Stack

- **Backend:** PHP 7.4+
- **Database:** Supabase (PostgreSQL)
- **Storage:** Cloudinary (Images/Videos)
- **Frontend:** HTML5, CSS3, JavaScript
- **Framework:** Tailwind CSS, Iconify Icons

## 📦 Prerequisites

- PHP 7.4 atau lebih tinggi
- XAMPP / Local Server
- Akun Supabase
- Akun Cloudinary
- Browser modern

## � Deploy ke Railway

Untuk deploy di Railway, gunakan `railway` CLI atau GitHub integration:

1. Push repo ke GitHub.
2. Buat project baru di Railway dan hubungkan repo `slb-c.gemolong`.
3. Set environment variables sesuai `railway.env.example`.   - Railway mungkin menyarankan nama `NEXT_PUBLIC_SUPABASE_URL` atau `NEXT_PUBLIC_SUPABASE_PUBLISHABLE_KEY`.
   - Aplikasi ini juga mendukung alias tersebut secara otomatis.4. Pastikan `BASE_URL=/` untuk root deployment.
5. Railway akan menjalankan container pada port yang disediakan oleh env `PORT`.

Jika Anda menggunakan Railway, cukup gunakan `railway.env.example` sebagai panduan variabel.

## �🔧 Setup Lokal

### 1. Clone Repository
```bash
git clone https://github.com/username/web_sekolah.git
cd web_sekolah
```

### 2. Konfigurasi Database
```bash
# Copy file example menjadi config.php
cp includes/config.example.php includes/config.php
```

### 3. Edit `includes/config.php`
Isi dengan credential Anda:

```php
// Supabase
define('SUPABASE_URL', 'https://your-project.supabase.co');
define('SUPABASE_KEY', 'your-publishable-key');
define('SUPABASE_SERVICE_KEY', 'your-service-key');

// Cloudinary
define('CLOUDINARY_CLOUD_NAME', 'your-cloud-name');
define('CLOUDINARY_API_KEY', 'your-api-key');
define('CLOUDINARY_API_SECRET', 'your-api-secret');

// Admin
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_SALT', 'your-salt');
define('ADMIN_PASSWORD_HASH', 'your-hashed-password');
```

### 4. Setup Database
Jalankan query SQL dari folder `sql/`:
```sql
-- Di Supabase SQL Editor, jalankan:
-- supabase.sql
-- all-tables-data.sql
-- pengumuman_tables.sql
-- dll
```

### 5. Akses Website
```
http://localhost/web_sekolah
```

### 6. Login Admin
```
URL: http://localhost/web_sekolah/admin/
Username: admin
Password: (sesuai config Anda)
```

## 📁 Struktur Folder

```
web_sekolah/
├── admin/                 # Panel admin
│   ├── kelola-anggaran.php
│   ├── kelola-berita.php
│   ├── kelola-galeri.php
│   ├── kelola-program.php
│   └── ...
├── pages/                 # Halaman publik
│   ├── anggaran.php
│   ├── berita.php
│   ├── galeri.php
│   ├── rencana-program.php
│   └── ...
├── includes/             # Include files
│   ├── config.php        # ⚠️ JANGAN UPLOAD (buat dari example)
│   ├── config.example.php
│   ├── db.php
│   ├── supabase.php
│   └── session.php
├── components/           # Component reusable
│   ├── navbar.php
│   ├── footer.php
│   ├── head.php
│   └── ...
├── assets/              # Static files
│   ├── css/
│   ├── js/
│   └── images/
├── sql/                 # Database scripts
│   ├── supabase.sql
│   └── ...
└── .gitignore          # Exclude sensitive files
```

## 🔐 Keamanan

### File JANGAN Upload ke GitHub:
```
includes/config.php      ← Berisi API Keys
includes/supabase.php    ← Service Key
.env                     ← Environment variables
.env.local
uploads/                 ← File upload lokal
```

### Sudah Diproteksi (di .gitignore):
- ✅ Semua file config
- ✅ Environment variables
- ✅ API credentials
- ✅ Private keys

### Setup di Production:
1. Update password admin
2. Generate salt baru
3. Set environment variables di server
4. Enable HTTPS
5. Setup firewall rules

## 🚀 Deploy ke Hosting

### Persiapan:
1. Push ke GitHub (file sensitif sudah di-exclude)
2. Pilih hosting dengan support PHP + PostgreSQL
3. Clone repository di hosting
4. Copy `config.example.php` → `config.php`
5. Isi credential di `config.php`
6. Setup database di Supabase
7. Konfigurasi domain

### Hosting Recommended:
- **Shared Hosting:** Niagahoster, Bluehost, Hostinger
- **Cloud:** Digital Ocean, AWS, Google Cloud
- **Containerized:** Docker + Vercel/Railway

## 📝 File Penting

| File | Deskripsi | Upload? |
|------|-----------|---------|
| config.php | Konfigurasi sensitif | ❌ NO |
| config.example.php | Template config | ✅ YES |
| supabase.php | Koneksi database | ❌ NO (atau remove keys) |
| .gitignore | Exclude files | ✅ YES |
| sql/ | Database schema | ✅ YES |

## 📚 Dokumentasi

- [Supabase Docs](https://supabase.com/docs)
- [Cloudinary Docs](https://cloudinary.com/documentation)
- [PHP Docs](https://www.php.net/docs.php)
- [Tailwind CSS](https://tailwindcss.com/docs)

## 🐛 Troubleshooting

### Halaman Putih / Error 500
- Cek `includes/config.php` sudah ada?
- Cek API keys benar?
- Cek PHP error log: `C:\xampp\php\logs\php_error_log`

### Koneksi Database Gagal
- Cek Supabase URL & keys
- Cek internet connection
- Cek Supabase project aktif

### Gambar Tidak Muncul
- Cek Cloudinary credentials
- Cek file upload berhasil ke Cloudinary
- Buka browser DevTools → Network tab

### Admin Login Gagal
- Cek username/password di `config.php`
- Clear browser cache
- Cek session di `includes/session.php`

## 👥 Kontribusi

1. Fork repository
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buka Pull Request

## 📄 License

MIT License - Bebas digunakan untuk komersial maupun non-komersial

## 👨‍💼 Support

Untuk bantuan atau pertanyaan:
- 📧 Email: slbc.gemolong@yahoo.com
- 📱 Telepon: 081 329 009 325
- 🗺️ Lokasi: Jl. Sukowati KM.2 Gemolong, Kab. Sragen

---

**Dibuat dengan ❤️ untuk SLB-C YPSLB Gemolong**

Last Updated: 2026-06-08
