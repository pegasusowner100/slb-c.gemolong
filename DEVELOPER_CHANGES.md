Developer Changes Log

Date: 2026-06-13

Summary:
- Fixed Supabase write fallback: `includes/supabase.php` will retry with `SUPABASE_KEY` when `SUPABASE_SERVICE_KEY` returns 401.
- Improved UI error messages in `admin/kelola-fasilitas.php` and `admin/kelola-guru.php` to show Supabase error details.
- Implemented Cloudinary helper files and standardized includes:
  - `includes/cloudinary.php` (Cloudinary upload implementation)
  - `includes/cloudinary-on.php` (thin loader to `cloudinary.php`)
  - `includes/cloudinary2.php` (exists and forwards to `cloudinary-on.php`)
- Updated all admin pages to require `includes/cloudinary-on.php` instead of old path.

Files modified/created in this session:
- created: `includes/cloudinary-on.php`
- modified: `includes/cloudinary.php`
- created: `includes/cloudinary2.php` (forwarder)
- modified: `includes/supabase.php`
- modified: `admin/kelola-guru.php`
- modified: `admin/kelola-fasilitas.php`
- modified: admin pages: `admin/edit-beranda.php`, `admin/edit-profil.php`, `admin/kelola-berita.php`, `admin/kelola-download.php`, `admin/kelola-fasilitas.php`, `admin/kelola-galeri.php`, `admin/kelola-prestasi.php`, `admin/kelola-program.php`, `admin/kelola-siswa.php`, `admin/kelola-video.php`

Files you must update when changing configuration or credentials:
- `includes/config.php` — update `SUPABASE_URL`, `SUPABASE_KEY`, `SUPABASE_SERVICE_KEY`, and Cloudinary constants (`CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, `CLOUDINARY_API_SECRET`, `CLOUDINARY_UPLOAD_PRESET`, `CLOUDINARY_FOLDER`).

Files to check when changing upload behavior:
- `includes/cloudinary.php` and `includes/cloudinary-on.php`
- Admin pages that handle uploads: any file that includes `includes/cloudinary-on.php` (see list above).

Files to check when changing Supabase/API behavior:
- `includes/supabase.php` (auth/fallback logic)
- Any admin page that performs inserts/updates (e.g., `admin/kelola-guru.php`, `admin/kelola-fasilitas.php`)

How I'll notify you on future changes:
- I will update `DEVELOPER_CHANGES.md` with a timestamped entry for each script change and list the specific files changed. I'll also explicitly mention which files you should edit if you need to update credentials.

Quick verification steps you can run locally:
- Test Supabase read: open `admin/kelola-guru.php` in the browser and confirm the list loads.
- Test adding a guru via the admin UI (requires `includes/config.php` credentials).

2026-06-18: Pengumuman PDF storage via Cloudinary
- Modified: `admin/kelola-pengumuman.php` — Changed `uploadPengumumanPdf()` to use `uploadToCloudinary()` instead of `uploadToSupabaseStorage()` (Supabase Storage had signature verification errors; Cloudinary is proven working for other file types).
- Now uses same unsigned preset as image uploads: `CLOUDINARY_UPLOAD_PRESET` set to "web_sekolah_unsigned".
- PDF files stored in Cloudinary folder `pengumuman` and URL saved to `pengumuman.pdf` column in DB.
- Modified: `pages/pengumuman.php` — PDF embedding now uses Cloudinary URL, checks PDF Content-Type before embedding, otherwise shows fallback links (open/download/Google Docs).
- Also modified: Modal forms scrollable, PDF preview containers limited to `max-height:50vh`.

Notes:
- Cloudinary credentials in `includes/config.php`: `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, `CLOUDINARY_API_SECRET`, `CLOUDINARY_UPLOAD_PRESET`.
- PDF now stored alongside images/photo in Cloudinary (consistent with app architecture).

If you want, I can now:
- revert any specific change, or
- run an end-to-end manual test flow and report exact actions to perform in browser.
