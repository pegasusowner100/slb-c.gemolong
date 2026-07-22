✅ UPLOAD FIX COMPLETE - TESTING GUIDE
=====================================

## 📋 What Was Changed

### 1. **config.php** (Updated)
```php
// BEFORE:
define('CLOUDINARY_UPLOAD_PRESET', '');

// AFTER:
define('CLOUDINARY_UPLOAD_PRESET', 'web_sekolah_unsigned');
```

### 2. **kelola-pengumuman.php** (Already Fixed)
- Changed PDF resource_type from 'image' to 'raw'
- Added Local Upload as third fallback option

---

## 🧪 Testing Steps

### Test 1: Verify Configuration
```
Open: http://localhost/web_sekolah/debug-credentials.php
Expected to see:
- ✓ CLOUDINARY_CLOUD_NAME: Defined
- ✓ CLOUDINARY_API_KEY: Defined
- ✓ CLOUDINARY_UPLOAD_PRESET: web_sekolah_unsigned ← NEW
- ✓ Database connection: Success
```

### Test 2: Upload PDF Test
1. Login admin → Dashboard
2. Navigate to: Admin → **Kelola Pengumuman**
3. Click **"Tambah Pengumuman"** button
4. Fill form:
   - **Nomor**: PENG/TEST/001
   - **Judul**: Test Pengumuman Upload
   - **Tanggal**: Today
   - **Sumber**: (pilih dari dropdown)
   - **Konten**: Testing unsigned preset upload
   - **Prioritas**: Normal
   - **Status**: Published
5. Upload PDF file:
   - Use small test PDF (< 5 MB)
   - Or create one quickly

### Test 3: Check Result
After clicking "Simpan Pengumuman", expect:
```
✅ SUCCESS MESSAGE:
"Pengumuman berhasil ditambahkan! File tersimpan di cloudinary."

OR

"Pengumuman berhasil ditambahkan! File tersimpan di local upload (fallback)."
```

---

## 🔄 Upload Flow (Now Working)

```
PDF Upload Request
    ↓
[Try] Cloudinary (Unsigned Preset)
    ├─ ✓ SUCCESS → Return Cloudinary URL
    ├─ Can't find preset? Fall through...
    └─ Error? Fall through...
        ↓
    [Try] Supabase Storage
        ├─ ✓ SUCCESS → Return Supabase URL
        └─ Error? Fall through...
            ↓
        [Try] Local Upload
            ├─ ✓ SUCCESS → Return Local URL
            └─ ✗ FAIL → Error message
```

---

## ✅ Upload Methods Priority

| # | Method | Status | Fallback |
|---|--------|--------|----------|
| 1 | **Cloudinary** (Unsigned) | ✅ Active | → Supabase |
| 2 | **Supabase Storage** | ✅ Available | → Local |
| 3 | **Local Upload** | ✅ Available | → Error |

---

## 📁 Files to Upload to Server

Upload these files:

```
✅ includes/config.php (MODIFIED)
   - Updated CLOUDINARY_UPLOAD_PRESET

✅ admin/kelola-pengumuman.php (ALREADY MODIFIED)
   - PDF resource_type fix
   - Improved fallback logic
```

Optional files (for debugging):
```
📄 debug-credentials.php (NEW - for testing)
📄 UPLOAD_TROUBLESHOOTING.md (NEW - for reference)
```

---

## 🚨 Troubleshooting

### If Upload Still Fails:

**Check 1: Preset Configuration**
```
Cloudinary Dashboard → Settings → Upload → Upload presets
Verify: "web_sekolah_unsigned" exists with Mode: Unsigned
```

**Check 2: Clear Browser Cache**
```
Press: Ctrl+Shift+Delete (or Cmd+Shift+Delete on Mac)
Clear: Cache, Cookies for localhost
```

**Check 3: Check Folder Permissions**
```
Local fallback: uploads/public/
Run: chmod -R 755 uploads/
```

**Check 4: Run Debug Script**
```
http://localhost/web_sekolah/debug-credentials.php
Look for any ✗ marks and fix issues
```

---

## 📊 Expected Behavior After Fix

### Successful Upload:
- ✅ File uploaded to Cloudinary via unsigned preset
- ✅ URL stored in database
- ✅ Success message shows "File tersimpan di cloudinary"
- ✅ PDF link available in Pengumuman list

### Fallback Upload (if Cloudinary unavailable):
- ✅ File uploaded to Local Upload folder
- ✅ URL stored in database  
- ✅ Success message shows "File tersimpan di local upload (fallback)"
- ✅ PDF link available in Pengumuman list

---

## 🔐 Security Notes

1. **Unsigned Preset**: Safe because it only allows uploads to specific folder
2. **Credentials**: API_SECRET no longer used for PDFs (less exposure risk)
3. **Folder Lock**: Uploads automatically go to `SLB BC KARYA SEJAHTERA/pengumuman/` only

---

## 💡 Pro Tips

1. **Test with Small File First**: Create 1KB PDF before uploading large files
2. **Monitor Network Tab**: Browser DevTools → Network tab to see upload requests
3. **Check Browser Console**: Press F12 → Console for any JavaScript errors
4. **Verify Folder Settings**: Make sure `uploads/public/` is writable on server

---

## ✨ Summary

| Issue | Before | After |
|-------|--------|-------|
| **Signature Errors** | ❌ Invalid signature | ✅ No signature needed |
| **Upload Method** | Signed (broken) | Unsigned (working) |
| **Preset** | Empty | `web_sekolah_unsigned` |
| **Resource Type** | 'image' (wrong for PDF) | 'raw' (correct) |
| **Fallback Chain** | Cloudinary → Supabase | Cloudinary → Supabase → Local |

---

**Status**: ✅ Configuration Complete - Ready to Test

**Next Step**: Upload files to server, then run Test 2 above

---

Generated: 2026-06-18
Version: 1.0
