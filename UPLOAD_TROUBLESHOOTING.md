# 🔧 Troubleshooting Upload Errors - Cloudinary & Supabase

## ❌ Error yang Anda Alami

```
Gagal upload PDF ke Cloudinary dan Supabase Storage. 
Cloudinary: Invalid Signature a08b741589e8a72e46dac21e0e81a14ff91d8947. 
String to sign - 'folder=SLB BC KARYA SEJAHTERA/pengumuman&timestamp=1781715489'. 
| Supabase: signature verification failed
```

---

## 🔍 Root Cause Analysis

| Issue | Cause |
|-------|-------|
| **Cloudinary Signature Invalid** | API Secret tidak valid/lengkap atau tidak sesuai dengan Cloud Name |
| **Supabase Signature Failed** | Service Key tidak valid atau expired |
| **Keduanya Gagal** | Kredensial tidak dikonfigurasi dengan benar |

---

## ✅ Solusi Perbaikan

### **Opsi 1: Gunakan Unsigned Upload Preset (RECOMMENDED)**

#### Langkah 1: Di Cloudinary Dashboard
1. Buka https://cloudinary.com/console
2. Masuk ke Settings → Upload
3. Cari bagian **"Upload presets"**
4. Klik **"Add upload preset"**
5. Atur:
   - **Name**: `web_sekolah_unsigned` (atau nama pilihan Anda)
   - **Signing Mode**: **Unsigned**
   - **Folder**: `SLB BC KARYA SEJAHTERA/pengumuman`
   - Klik **"Save"**

#### Langkah 2: Update `includes/config.php`
```php
define('CLOUDINARY_UPLOAD_PRESET', 'web_sekolah_unsigned');
// Sekarang API_KEY dan API_SECRET tidak perlu valid (tidak digunakan)
```

#### Langkah 3: Hasil
✅ Upload akan menggunakan preset tanpa perlu menandatangani request

---

### **Opsi 2: Verifikasi & Gunakan Signed Upload**

#### Langkah 1: Dapatkan API Secret yang Benar
1. Buka https://cloudinary.com/console/settings
2. Lihat **Account** tab
3. Copy **API Secret** lengkap (bukan sebagian)
4. Update di `includes/config.php`:
```php
define('CLOUDINARY_API_SECRET', 'PASTE_FULL_API_SECRET_HERE');
```

#### Langkah 2: Verifikasi dengan Debug Script
```
Buka di browser: http://localhost/web_sekolah/debug-credentials.php
```
- Jika status API Secret: ✓ OK
- Jika Signature Test: ✓ Generated
- Maka signed upload seharusnya bekerja

---

### **Opsi 3: Perbaiki Supabase Storage**

#### Langkah 1: Verifikasi Service Key
1. Buka https://supabase.com/dashboard
2. Masuk project Anda: `cmmzoykcoiystbgndaif`
3. Settings → API
4. Copy **Service Role secret** lengkap (bukan hanya bagian akhir)
5. Update di `includes/config.php`:
```php
define('SUPABASE_SERVICE_KEY', 'PASTE_FULL_SERVICE_KEY_HERE');
```

#### Langkah 2: Verifikasi Storage Bucket
1. Settings → Storage
2. Pastikan bucket `sekolah-assets` ada
3. Pastikan permissions memungkinkan public access

---

## 🧪 Testing Steps

### Test 1: Jalankan Debug Script
```
http://localhost/web_sekolah/debug-credentials.php
```
Periksa:
- [ ] CLOUDINARY_CLOUD_NAME: ✓ Defined
- [ ] CLOUDINARY_API_KEY: ✓ Defined
- [ ] CLOUDINARY_UPLOAD_PRESET: ✓ Defined (jika Opsi 1)
- [ ] Signature Test: ✓ Generated
- [ ] Database Connection: ✓ Success

### Test 2: Upload Test File
1. Buka Admin → Kelola Pengumuman
2. Klik "Tambah Pengumuman"
3. Upload PDF kecil (< 5 MB)
4. Lihat pesan success/error

### Test 3: Check Upload Fallback
- Jika Cloudinary gagal, seharusnya fallback ke:
  1. Supabase Storage
  2. Local Upload (folder: `uploads/public/`)

---

## 📋 Configuration Checklist

### Untuk Unsigned Preset (Opsi 1)
- [ ] Preset dibuat di Cloudinary
- [ ] `CLOUDINARY_UPLOAD_PRESET` di-set di config.php
- [ ] Cloud Name ada & benar

### Untuk Signed Upload (Opsi 2)
- [ ] Full API Secret di config.php (minimal 30+ karakter)
- [ ] API Key ada & benar
- [ ] Cloud Name ada & benar

### Untuk Supabase Storage
- [ ] Full Service Key di config.php
- [ ] Supabase URL benar
- [ ] Storage bucket `sekolah-assets` ada
- [ ] Database connection test: ✓ Success

### Local Upload (Fallback)
- [ ] Folder `uploads/public/` writable (755 permissions)
- [ ] `LOCAL_UPLOAD_ENABLED` = true

---

## 🚀 Quick Fix Recommendations

### Best Practice: Hybrid Approach
1. **Gunakan Unsigned Preset** untuk Cloudinary (paling mudah & reliable)
2. **Aktifkan Local Upload** sebagai fallback
3. **Supabase Storage** sebagai backup jika keduanya gagal

### Update config.php ke:
```php
// Primary: Unsigned Cloudinary Upload Preset
define('CLOUDINARY_UPLOAD_PRESET', 'web_sekolah_unsigned');

// Fallback 1: Local Upload
define('LOCAL_UPLOAD_ENABLED', true);

// Fallback 2: Supabase Storage (verify credentials first)
define('SUPABASE_STORAGE_BUCKET', 'sekolah-assets');
```

---

## 🔗 Upload Flow (Setelah Fix)

```
PDF Upload Request
    ↓
[Try] Cloudinary (Unsigned Preset)
    ├─ ✓ Success → Simpan URL Cloudinary
    └─ ✗ Fail
        ↓
    [Try] Supabase Storage
        ├─ ✓ Success → Simpan URL Supabase
        └─ ✗ Fail
            ↓
        [Try] Local Upload (uploads/public/)
            ├─ ✓ Success → Simpan URL Local
            └─ ✗ Fail → Error message
```

---

## 📞 Support Resources

- **Cloudinary Docs**: https://cloudinary.com/documentation
- **Supabase Storage**: https://supabase.com/docs/guides/storage
- **Upload Presets**: https://cloudinary.com/documentation/upload_presets

---

## ⚠️ Important Notes

1. **Jangan share API Secret** - Config file sudah di-.gitignore
2. **Test dengan PDF kecil dulu** sebelum file besar
3. **Check browser console** untuk error details tambahan
4. **Bersihkan localStorage** jika ada masalah view toggle: `localStorage.clear()`

---

**Last Updated**: 2026-06-18
**Files Modified**: 
- `admin/kelola-pengumuman.php` - Fixed resource_type & added local fallback
- `debug-credentials.php` - New debug script
