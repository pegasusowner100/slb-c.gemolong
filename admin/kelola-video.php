<?php
define('ADMIN_PAGE', true);
require_once '../includes/session.php';
require_once '../includes/supabase.php';
require_once '../includes/cloudinary-on.php';
require_login();

$title = "Kelola Video — SLB BC KARYA SEJAHTERA " . SITE_NAME;
$page_title = "Kelola Video";
$success = '';
$error = '';

// Handle form submission for adding video
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_video'])) {
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $kategori = $_POST['kategori'];
    $thumbnail = "https://picsum.photos/seed/" . time() . "/800/450.jpg";
    
    $videoUrl = '';
    
    // Upload video to Cloudinary if file is uploaded
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadToCloudinary($_FILES['video'], CLOUDINARY_FOLDER);
        if ($uploadResult['success']) {
            $videoUrl = $uploadResult['url'];
        } else {
            $error = 'Gagal mengupload video: ' . ($uploadResult['data']['error']['message'] ?? 'Unknown error');
        }
    }
    
    if (empty($error)) {
        // Insert into Supabase
        $data = [
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'url_video' => $videoUrl,
            'thumbnail' => $thumbnail,
            'kategori' => $kategori
        ];
        
        $response = supabaseInsert('video', $data);
        
        if ($response['success']) {
            $success = 'Video berhasil ditambahkan!';
        } else {
            $error = 'Gagal menyimpan video ke database.';
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $response = supabaseDelete('video', $_GET['delete']);
    if ($response['success']) {
        $success = 'Video berhasil dihapus!';
    } else {
        $error = 'Gagal menghapus video.';
    }
}

// Get all videos
$response = supabaseSelect('video', ['order' => 'tanggal.desc']);
$videos = $response['success'] ? $response['data'] : [];

include 'components/head.php';
include 'components/sidebar.php';
?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php include 'components/header.php'; ?>

    <div class="flex-1 overflow-y-auto p-8">
        <?php if ($success): ?>
            <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg flex items-center gap-3">
                <iconify-icon icon="lucide:check-circle"></iconify-icon>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg flex items-center gap-3">
                <iconify-icon icon="lucide:alert-circle"></iconify-icon>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="flex items-center justify-between mb-8">
            <h3 class="text-xl font-semibold text-[#1F2D26]">Daftar Video</h3>
            <button onclick="document.getElementById('modalVideo').classList.remove('hidden')" class="bg-[#3E6B4E] text-white text-xs font-bold px-6 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest flex items-center gap-2">
                <iconify-icon icon="lucide:plus"></iconify-icon>
                Tambah Video Baru
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($videos)): ?>
                <div class="col-span-full text-center py-12">
                    <p class="text-[#5F6F65]">Belum ada video.</p>
                </div>
            <?php else: ?>
                <?php foreach ($videos as $video): ?>
                    <div class="bg-white rounded-lg border border-[#E8E4D9] shadow-sm overflow-hidden">
                        <div class="aspect-video bg-gray-100 relative">
                            <img src="<?php echo htmlspecialchars($video['thumbnail']); ?>" alt="Thumbnail" class="w-full h-full object-cover">
                            <button onclick="window.open('<?php echo htmlspecialchars($video['url_video']); ?>', '_blank')" class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 hover:opacity-100 transition-opacity">
                                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                                    <iconify-icon icon="lucide:play" class="text-[#3E6B4E] text-2xl ml-1"></iconify-icon>
                                </div>
                            </button>
                        </div>
                        <div class="p-4">
                            <h4 class="font-semibold text-[#1F2D26] mb-1"><?php echo htmlspecialchars($video['judul']); ?></h4>
                            <span class="text-xs text-[#9FB5A5] uppercase"><?php echo htmlspecialchars($video['kategori']); ?></span>
                            <p class="text-sm text-[#5F6F65] mt-2 line-clamp-2"><?php echo htmlspecialchars($video['deskripsi'] ?? ''); ?></p>
                            <div class="mt-4 flex gap-2">
                                <a href="?delete=<?php echo $video['id']; ?>" onclick="return confirm('Yakin ingin menghapus video ini?')" class="p-2 text-red-500 hover:bg-red-50 rounded transition-colors">
                                    <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Tambah Video -->
<div id="modalVideo" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] flex items-center justify-center p-6">
    <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-[#E8E4D9] flex items-center justify-between">
            <h3 class="font-semibold text-[#1F2D26]">Tambah Video Baru</h3>
            <button onclick="document.getElementById('modalVideo').classList.add('hidden')" class="text-[#5F6F65] hover:text-[#1F2D26]">
                <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Judul Video</label>
                <input type="text" name="judul" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Deskripsi</label>
                <textarea name="deskripsi" rows="3" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Kategori</label>
                <select name="kategori" class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
                    <option value="Profil">Profil</option>
                    <option value="Kegiatan">Kegiatan</option>
                    <option value="Pembelajaran">Pembelajaran</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-[#9FB5A5] uppercase mb-2">Upload Video</label>
                <input type="file" name="video" accept="video/*" required class="w-full px-4 py-3 bg-[#F9F8F4] border border-[#E8E4D9] rounded focus:outline-none focus:border-[#3E6B4E] transition-colors text-sm">
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="document.getElementById('modalVideo').classList.add('hidden')" class="px-6 py-3 text-xs font-bold text-[#5F6F65] uppercase tracking-widest">Batal</button>
                <button type="submit" name="tambah_video" class="bg-[#3E6B4E] text-white text-xs font-bold px-8 py-3 rounded hover:bg-[#2F5B41] transition-colors uppercase tracking-widest">Simpan Video</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
