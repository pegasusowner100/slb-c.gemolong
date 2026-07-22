<?php
define('ADMIN_PAGE', true);
require_once '../includes/session.php';
require_once '../includes/db.php';
require_login();
require_once '../includes/supabase_storage.php';
require_once '../includes/cloudinary-on.php';


// Temporary: enable error display to debug white page (remove after fix)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$title = "Kelola Anggaran & Belanja - SLB BC KARYA SEJAHTERA";
$page_title = "Kelola Anggaran & Belanja";
$success = $_SESSION['success_notif'] ?? '';
$error = $_SESSION['error_notif'] ?? '';
unset($_SESSION['success_notif'], $_SESSION['error_notif']);

function anggaranUploadErrorMessage($code) {
    switch ((int) $code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'Ukuran file melebihi batas upload server.';
        case UPLOAD_ERR_PARTIAL:
            return 'File hanya terupload sebagian. Coba unggah ulang.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Folder sementara upload tidak tersedia di server.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Server gagal menulis file upload.';
        case UPLOAD_ERR_EXTENSION:
            return 'Upload dihentikan oleh ekstensi PHP.';
        default:
            return 'Upload file gagal: kode error ' . $code;
    }
}

function getMonthNumberForSemester($bulan) {
    $semester1 = ['Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4, 'Mei' => 5, 'Juni' => 6];
    $semester2 = ['Juli' => 1, 'Agustus' => 2, 'September' => 3, 'Oktober' => 4, 'November' => 5, 'Desember' => 6];
    if (isset($semester1[$bulan])) {
        return $semester1[$bulan];
    } elseif (isset($semester2[$bulan])) {
        return $semester2[$bulan];
    }
    return 0;
}

function updateAnggaranSemesterBulanan($tahun, $semester, $bulan, $anggaran, $realisasi) {
    $monthNum = getMonthNumberForSemester($bulan);
    if ($monthNum < 1 || $monthNum > 6) return false;
    
    $anggaranField = 'anggaran_m' . $monthNum;
    $realisasiField = 'realisasi_m' . $monthNum;
    
    // Get existing anggaran_semester
    $existingResult = supabaseSelect('anggaran_semester', [
        'tahun' => 'eq.' . $tahun,
        'semester' => 'eq.' . $semester
    ]);
    
    if (!$existingResult['success'] || empty($existingResult['data'])) {
        return false;
    }
    
    $id = $existingResult['data'][0]['id'];
    $existingData = $existingResult['data'][0];
    
    // Prepare data to update
    $updateData = [
        $anggaranField => (int)$anggaran,
        $realisasiField => (int)$realisasi
    ];
    
    // Update it!
    return supabaseUpdate('anggaran_semester', $updateData, $id);
}

function resetAnggaranSemesterBulanan($tahun, $semester, $bulan) {
    $monthNum = getMonthNumberForSemester($bulan);
    if ($monthNum < 1 || $monthNum > 6) return false;
    
    $anggaranField = 'anggaran_m' . $monthNum;
    $realisasiField = 'realisasi_m' . $monthNum;
    
    $existingResult = supabaseSelect('anggaran_semester', [
        'tahun' => 'eq.' . $tahun,
        'semester' => 'eq.' . $semester
    ]);
    
    if (!$existingResult['success'] || empty($existingResult['data'])) {
        return false;
    }
    
    $id = $existingResult['data'][0]['id'];
    $updateData = [
        $anggaranField => 0,
        $realisasiField => 0
    ];
    
    return supabaseUpdate('anggaran_semester', $updateData, $id);
}

function anggaranUploadPdf($file, $folder = 'anggaran') {
    if (!isset($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [
            'success' => true,
            'url' => '',
            'no_file' => true
        ];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'error' => anggaranUploadErrorMessage($file['error'] ?? 'unknown')
        ];
    }

    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if ($extension !== 'pdf') {
        return [
            'success' => false,
            'error' => 'File harus berformat PDF.'
        ];
    }

    if (!function_exists('uploadToSupabaseStorage')) {
        return [
            'success' => false,
            'error' => 'Supabase Storage tidak tersedia.'
        ];
    }

    $uploadResult = uploadToSupabaseStorage($file, $folder);
    if ($uploadResult['success']) {
        return [
            'success' => true,
            'url' => $uploadResult['url'],
            'storage' => 'supabase'
        ];
    }

    return [
        'success' => false,
        'error' => 'Supabase Storage: ' . ($uploadResult['error'] ?? 'Upload gagal.')
    ];
}

// --- ANGGARAN BOSN ---
// Yearly budget is calculated dynamically from semester budget accumulation. Manual CRUD is removed.

// --- ANGGARAN SEMESTER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_anggaran_semester'])) {
    if (!$supabaseConnected) {
        $_SESSION['error_notif'] = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $tahun = trim($_POST['tahun_semester']);
        $semester = trim($_POST['semester']);
        $total_anggaran = (int)preg_replace('/\D/', '', $_POST['total_anggaran'] ?? '0');

        $data = [
            'tahun' => (int)$tahun,
            'semester' => (int)$semester,
            'total_anggaran' => $total_anggaran,
            'total_realisasi' => 0,
            'anggaran_m1' => 0,
            'realisasi_m1' => 0,
            'anggaran_m2' => 0,
            'realisasi_m2' => 0,
            'anggaran_m3' => 0,
            'realisasi_m3' => 0,
            'anggaran_m4' => 0,
            'realisasi_m4' => 0,
            'anggaran_m5' => 0,
            'realisasi_m5' => 0,
            'anggaran_m6' => 0,
            'realisasi_m6' => 0
        ];
        
        $response = supabaseInsert('anggaran_semester', $data);
        if ($response['success']) {
            $_SESSION['success_notif'] = 'Anggaran semester berhasil ditambahkan!';
        } else {
            $_SESSION['error_notif'] = 'Gagal menyimpan anggaran semester: ' . ($response['error'] ?? json_encode($response['data'] ?? $response));
        }
    }
    header('Location: kelola-anggaran.php?tab=anggaran_semester');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_anggaran_semester'])) {
    if (!$supabaseConnected) {
        $_SESSION['error_notif'] = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $id = trim($_POST['id_anggaran_semester']);
        $tahun = trim($_POST['tahun_semester']);
        $semester = trim($_POST['semester']);
        $total_anggaran = (int)preg_replace('/\D/', '', $_POST['total_anggaran'] ?? '0');

        $existing = [];
        $existingResult = supabaseSelect('anggaran_semester', ['id' => 'eq.' . $id]);
        if ($existingResult['success'] && !empty($existingResult['data'])) {
            $existing = $existingResult['data'][0];
        }

        $data = [
            'tahun' => (int)$tahun,
            'semester' => (int)$semester,
            'total_anggaran' => $total_anggaran,
            'total_realisasi' => isset($existing['total_realisasi']) ? (int)$existing['total_realisasi'] : 0,
            'anggaran_m1' => isset($existing['anggaran_m1']) ? (int)$existing['anggaran_m1'] : 0,
            'realisasi_m1' => isset($existing['realisasi_m1']) ? (int)$existing['realisasi_m1'] : 0,
            'anggaran_m2' => isset($existing['anggaran_m2']) ? (int)$existing['anggaran_m2'] : 0,
            'realisasi_m2' => isset($existing['realisasi_m2']) ? (int)$existing['realisasi_m2'] : 0,
            'anggaran_m3' => isset($existing['anggaran_m3']) ? (int)$existing['anggaran_m3'] : 0,
            'realisasi_m3' => isset($existing['realisasi_m3']) ? (int)$existing['realisasi_m3'] : 0,
            'anggaran_m4' => isset($existing['anggaran_m4']) ? (int)$existing['anggaran_m4'] : 0,
            'realisasi_m4' => isset($existing['realisasi_m4']) ? (int)$existing['realisasi_m4'] : 0,
            'anggaran_m5' => isset($existing['anggaran_m5']) ? (int)$existing['anggaran_m5'] : 0,
            'realisasi_m5' => isset($existing['realisasi_m5']) ? (int)$existing['realisasi_m5'] : 0,
            'anggaran_m6' => isset($existing['anggaran_m6']) ? (int)$existing['anggaran_m6'] : 0,
            'realisasi_m6' => isset($existing['realisasi_m6']) ? (int)$existing['realisasi_m6'] : 0
        ];
        
        $response = supabaseUpdate('anggaran_semester', $data, $id);
        if ($response['success']) {
            $_SESSION['success_notif'] = 'Anggaran semester berhasil diperbarui!';
        } else {
            $_SESSION['error_notif'] = 'Gagal memperbarui anggaran semester: ' . ($response['error'] ?? json_encode($response['data'] ?? $response));
        }
    }
    header('Location: kelola-anggaran.php?tab=anggaran_semester');
    exit;
}

if (isset($_GET['hapus_anggaran_semester']) && !empty($_GET['hapus_anggaran_semester'])) {
    if (!$supabaseConnected) {
        $_SESSION['error_notif'] = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        $response = supabaseDelete('anggaran_semester', $_GET['hapus_anggaran_semester']);
        if ($response['success']) {
            $_SESSION['success_notif'] = 'Anggaran semester berhasil dihapus!';
        } else {
            $_SESSION['error_notif'] = 'Gagal menghapus anggaran semester: ' . ($response['error'] ?? json_encode($response['data'] ?? $response));
        }
    }
    header('Location: kelola-anggaran.php?tab=anggaran_semester');
    exit;
}

// --- REALISASI BULANAN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_realisasi'])) {
    if (!$supabaseConnected) {
        $_SESSION['error_notif'] = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $tahun = trim($_POST['tahun_realisasi']);
        $semester = trim($_POST['semester']);
        $bulan = trim($_POST['bulan']);
        $anggaran = preg_replace('/\\D/', '', $_POST['anggaran_bulan']);
        $realisasi = preg_replace('/\\D/', '', $_POST['realisasi_bulan']);
        
        if ((int)$realisasi > (int)$anggaran) {
            $_SESSION['error_notif'] = 'Gagal menyimpan: Realisasi bulanan melebihi anggaran bulanan, tidak bisa di proses!';
        } else {
            $uploadResult = anggaranUploadPdf($_FILES['file_pdf_realisasi'] ?? null, 'realisasi');
            if (!$uploadResult['success']) {
                $_SESSION['error_notif'] = $uploadResult['error'];
            }
        }

        $file_pdf = $uploadResult['url'] ?? '';

        if (empty($_SESSION['error_notif'])) {
            // Tentukan semester berdasarkan bulan jika tidak konsisten
            $semester_num = in_array($bulan, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']) ? 1 : 2;
            $semester = ($semester === '1' || $semester === '2') ? $semester : $semester_num;
            
            // Ambil pagu anggaran semester ini
            $sem_budget = 0;
            $compResult = supabaseSelect('anggaran_semester', [
                'tahun' => 'eq.' . $tahun,
                'semester' => 'eq.' . $semester
            ]);
            if ($compResult['success'] && !empty($compResult['data'])) {
                $sem_budget = (int)$compResult['data'][0]['total_anggaran'];
            }
            
            // Hitung total anggaran bulanan yang ada pada semester tersebut
            $sem_months = ($semester === '1') 
                ? ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']
                : ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                
            $existing_sum = 0;
            $allRealisasi = supabaseSelect('realisasi_bulanan', ['tahun' => 'eq.' . $tahun]);
            if ($allRealisasi['success'] && !empty($allRealisasi['data'])) {
                foreach ($allRealisasi['data'] as $r) {
                    if (in_array($r['bulan'], $sem_months)) {
                        $existing_sum += (int)$r['anggaran'];
                    }
                }
            }
            
            $total_new = $existing_sum + (int)$anggaran;
            if ($sem_budget > 0 && $total_new > $sem_budget) {
                $_SESSION['warning_notif'] = "Peringatan: Jumlah anggaran bulanan Semester {$semester} tahun {$tahun} (Rp " . number_format($total_new, 0, ',', '.') . ") melebihi pagu Anggaran Semester ini (Rp " . number_format($sem_budget, 0, ',', '.') . ")!";
            }

            $data = [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'anggaran' => (int)$anggaran,
                'realisasi' => (int)$realisasi,
                'file_pdf' => $file_pdf
            ];
            
            $response = supabaseInsert('realisasi_bulanan', $data);
            if ($response['success']) {
                // Now update the anggaran_semester
                updateAnggaranSemesterBulanan($tahun, $semester, $bulan, $anggaran, $realisasi);
                $_SESSION['success_notif'] = 'Realisasi bulanan berhasil ditambahkan!';
            } else {
                $_SESSION['error_notif'] = 'Gagal menyimpan realisasi: ' . ($response['error'] ?? json_encode($response['data'] ?? $response));
            }
        }
    }
    header('Location: kelola-anggaran.php?tab=realisasi');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_realisasi'])) {
    if (!$supabaseConnected) {
        $_SESSION['error_notif'] = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $id = trim($_POST['id']);
        $tahun = trim($_POST['tahun_realisasi']);
        $semester = trim($_POST['semester']);
        $bulan = trim($_POST['bulan']);
        $anggaran = preg_replace('/\\D/', '', $_POST['anggaran_bulan']);
        $realisasi = preg_replace('/\\D/', '', $_POST['realisasi_bulan']);
        
        if ((int)$realisasi > (int)$anggaran) {
            $_SESSION['error_notif'] = 'Gagal menyimpan: Realisasi bulanan melebihi anggaran bulanan, tidak bisa di proses!';
        } else {
            $uploadResult = anggaranUploadPdf($_FILES['file_pdf_realisasi'] ?? null, 'realisasi');
            if (!$uploadResult['success']) {
                $_SESSION['error_notif'] = $uploadResult['error'];
            }
        }

        $file_pdf = $uploadResult['url'] ?? '';
        if (empty($file_pdf) && isset($_POST['existing_pdf']) && !empty($_POST['existing_pdf'])) {
            $file_pdf = $_POST['existing_pdf'];
        }

        if (empty($_SESSION['error_notif'])) {
            // Tentukan semester berdasarkan bulan jika tidak konsisten
            $semester_num = in_array($bulan, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']) ? 1 : 2;
            $semester = ($semester === '1' || $semester === '2') ? $semester : $semester_num;
            
            // Ambil pagu anggaran semester ini
            $sem_budget = 0;
            $compResult = supabaseSelect('anggaran_semester', [
                'tahun' => 'eq.' . $tahun,
                'semester' => 'eq.' . $semester
            ]);
            if ($compResult['success'] && !empty($compResult['data'])) {
                $sem_budget = (int)$compResult['data'][0]['total_anggaran'];
            }
            
            // Hitung total anggaran bulanan yang ada pada semester tersebut (exclude current month being edited)
            $sem_months = ($semester === '1') 
                ? ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']
                : ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                
            $existing_sum = 0;
            $allRealisasi = supabaseSelect('realisasi_bulanan', ['tahun' => 'eq.' . $tahun]);
            if ($allRealisasi['success'] && !empty($allRealisasi['data'])) {
                foreach ($allRealisasi['data'] as $r) {
                    if ($r['id'] !== $id && in_array($r['bulan'], $sem_months)) {
                        $existing_sum += (int)$r['anggaran'];
                    }
                }
            }
            
            $total_new = $existing_sum + (int)$anggaran;
            if ($sem_budget > 0 && $total_new > $sem_budget) {
                $_SESSION['warning_notif'] = "Peringatan: Jumlah anggaran bulanan Semester {$semester} tahun {$tahun} (Rp " . number_format($total_new, 0, ',', '.') . ") melebihi pagu Anggaran Semester ini (Rp " . number_format($sem_budget, 0, ',', '.') . ")!";
            }

            $data = [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'anggaran' => (int)$anggaran,
                'realisasi' => (int)$realisasi,
                'file_pdf' => $file_pdf
            ];
            
            $response = supabaseUpdate('realisasi_bulanan', $data, $id);
            if ($response['success']) {
                // Now update the anggaran_semester
                updateAnggaranSemesterBulanan($tahun, $semester, $bulan, $anggaran, $realisasi);
                $_SESSION['success_notif'] = 'Realisasi berhasil diperbarui!';
            } else {
                $_SESSION['error_notif'] = 'Gagal memperbarui realisasi: ' . ($response['error'] ?? json_encode($response['data'] ?? $response));
            }
        }
    }
    header('Location: kelola-anggaran.php?tab=realisasi');
    exit;
}

if (isset($_GET['hapus_realisasi']) && !empty($_GET['hapus_realisasi'])) {
    if (!$supabaseConnected) {
        $_SESSION['error_notif'] = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        // First, get the data we're deleting to reset the semester budget
        $id = $_GET['hapus_realisasi'];
        $getResult = supabaseSelect('realisasi_bulanan', ['id' => 'eq.' . $id]);
        if ($getResult['success'] && !empty($getResult['data'])) {
            $realisasiData = $getResult['data'][0];
            // Reset the anggaran_semester
            $semester = in_array($realisasiData['bulan'], ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']) ? '1' : '2';
            resetAnggaranSemesterBulanan($realisasiData['tahun'], $semester, $realisasiData['bulan']);
        }
        
        $response = supabaseDelete('realisasi_bulanan', $id);
        if ($response['success']) {
            $_SESSION['success_notif'] = 'Realisasi berhasil dihapus!';
        } else {
            $_SESSION['error_notif'] = 'Gagal menghapus realisasi: ' . ($response['error'] ?? json_encode($response['data'] ?? $response));
        }
    }
    header('Location: kelola-anggaran.php?tab=realisasi');
    exit;
}

// --- RENCANA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_rencana'])) {
    if (!$supabaseConnected) {
        $_SESSION['error_notif'] = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $jenis = trim($_POST['jenis_rencana']);
        $judul = trim($_POST['judul']);
        $deskripsi = trim($_POST['deskripsi']);
        
        $uploadResult = anggaranUploadPdf($_FILES['file_pdf_rencana'] ?? null, 'rencana');
        if (!$uploadResult['success']) {
            $_SESSION['error_notif'] = $uploadResult['error'];
        }

        $file_pdf = $uploadResult['url'] ?? '';

        if (empty($_SESSION['error_notif'])) {
            $data = [
                'jenis_rencana' => $jenis,
                'judul' => $judul,
                'deskripsi' => $deskripsi,
                'file_pdf' => $file_pdf
            ];
            
            $response = supabaseInsert('rencana_anggaran', $data);
            if ($response['success']) {
                $_SESSION['success_notif'] = 'Rencana berhasil ditambahkan!';
            } else {
                $_SESSION['error_notif'] = 'Gagal menyimpan rencana: ' . ($response['error'] ?? json_encode($response['data'] ?? $response));
            }
        }
    }
    header('Location: kelola-anggaran.php?tab=rencana');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_rencana'])) {
    if (!$supabaseConnected) {
        $_SESSION['error_notif'] = 'Gagal menyimpan: Supabase tidak terhubung!';
    } else {
        $id = trim($_POST['id']);
        $jenis = trim($_POST['jenis_rencana']);
        $judul = trim($_POST['judul']);
        $deskripsi = trim($_POST['deskripsi']);
        
        $uploadResult = anggaranUploadPdf($_FILES['file_pdf_rencana'] ?? null, 'rencana');
        if (!$uploadResult['success']) {
            $_SESSION['error_notif'] = $uploadResult['error'];
        }

        $file_pdf = $uploadResult['url'] ?? '';
        if (empty($file_pdf) && isset($_POST['existing_pdf']) && !empty($_POST['existing_pdf'])) {
            $file_pdf = $_POST['existing_pdf'];
        }

        if (empty($_SESSION['error_notif'])) {
            $data = [
                'jenis_rencana' => $jenis,
                'judul' => $judul,
                'deskripsi' => $deskripsi,
                'file_pdf' => $file_pdf
            ];
            
            $response = supabaseUpdate('rencana_anggaran', $data, $id);
            if ($response['success']) {
                $_SESSION['success_notif'] = 'Rencana berhasil diperbarui!';
            } else {
                $_SESSION['error_notif'] = 'Gagal memperbarui rencana: ' . ($response['error'] ?? json_encode($response['data'] ?? $response));
            }
        }
    }
    header('Location: kelola-anggaran.php?tab=rencana');
    exit;
}

if (isset($_GET['hapus_rencana']) && !empty($_GET['hapus_rencana'])) {
    if (!$supabaseConnected) {
        $_SESSION['error_notif'] = 'Gagal menghapus: Supabase tidak terhubung!';
    } else {
        $response = supabaseDelete('rencana_anggaran', $_GET['hapus_rencana']);
        if ($response['success']) {
            $_SESSION['success_notif'] = 'Rencana berhasil dihapus!';
        } else {
            $_SESSION['error_notif'] = 'Gagal menghapus rencana: ' . ($response['error'] ?? json_encode($response['data'] ?? $response));
        }
    }
    header('Location: kelola-anggaran.php?tab=rencana');
    exit;
}

// --- AMBIL DATA ---
$realisasi = [];
if ($supabaseConnected) {
    $realisasiResult = supabaseSelect('realisasi_bulanan', ['order' => 'tahun.desc']);
    if ($realisasiResult['success']) {
        $realisasi = $realisasiResult['data'];
        
        // Backfill existing realisasi to anggaran_semester
        foreach ($realisasi as $r) {
            $semester = in_array($r['bulan'], ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']) ? '1' : '2';
            updateAnggaranSemesterBulanan($r['tahun'], $semester, $r['bulan'], $r['anggaran'], $r['realisasi']);
        }
        
        if (!function_exists('getMonthNumber')) {
            function getMonthNumber($monthName) {
                $months = [
                    'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4, 'Mei' => 5, 'Juni' => 6,
                    'Juli' => 7, 'Agustus' => 8, 'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12
                ];
                return $months[$monthName] ?? 0;
            }
        }
        
        usort($realisasi, function($a, $b) {
            if ($a['tahun'] != $b['tahun']) {
                return $b['tahun'] - $a['tahun'];
            }
            return getMonthNumber($a['bulan']) - getMonthNumber($b['bulan']);
        });
    }
}

// --- ANGGARAN SEMESTER DATA ---
$anggaran_semester = [];
if ($supabaseConnected) {
    $anggaranSemesterResult = supabaseSelect('anggaran_semester', ['order' => 'tahun.desc,semester.desc']);
    if ($anggaranSemesterResult['success']) {
        $anggaran_semester = $anggaranSemesterResult['data'];
        
        // Hitung realisasi semester secara dinamis dari jumlah realisasi bulanan pada semester tersebut
        foreach ($anggaran_semester as &$as) {
            $sem_months = ($as['semester'] == 1) 
                ? ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']
                : ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $sum_real = 0;
            foreach ($realisasi as $r) {
                if ($r['tahun'] == $as['tahun'] && in_array($r['bulan'], $sem_months)) {
                    $sum_real += (float)($r['realisasi'] ?? 0);
                }
            }
            $as['total_realisasi'] = $sum_real;
        }
        unset($as);
    }
}

// Agregasikan anggaran tahunan dari anggaran_semester (tanpa upload file)
$anggaran = [];
$yearly = [];
foreach ($anggaran_semester as $as) {
    $tahun = $as['tahun'];
    if (!isset($yearly[$tahun])) {
        $yearly[$tahun] = [
            'tahun' => $tahun,
            'total_anggaran' => 0,
            'realisasi' => 0,
            'file_pdf' => ''
        ];
    }
    $yearly[$tahun]['total_anggaran'] += $as['total_anggaran'];
    $yearly[$tahun]['realisasi'] += ($as['total_realisasi'] ?? 0);
}
$anggaran = array_values($yearly);
usort($anggaran, function($a, $b) {
    return $b['tahun'] - $a['tahun'];
});

$rencana = [];
if ($supabaseConnected) {
    $rencanaResult = supabaseSelect('rencana_anggaran', ['order' => 'created_at.desc']);
    if ($rencanaResult['success']) {
        $rencana = $rencanaResult['data'];
    }
}

include 'components/head.php';
include 'components/sidebar.php';
?>
<!-- Main Content -->
<main class="flex-1 flex flex-col overflow-hidden">
    <?php include 'components/header.php'; ?>
    <div class="flex-1 overflow-y-auto p-8">
        <div class="max-w-7xl space-y-8">
            <?php if ($success): ?>
                <div class="p-4 bg-green-100 border border-green-200 text-green-700 rounded-xl flex items-center gap-3 animate-fade-in">
                    <iconify-icon icon="lucide:check-circle" class="text-xl"></iconify-icon>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            <?php 
            $warning = $_SESSION['warning_notif'] ?? '';
            unset($_SESSION['warning_notif']);
            if ($warning): 
            ?>
                <div class="p-4 bg-yellow-100 border border-yellow-200 text-yellow-800 rounded-xl flex items-center gap-3 shadow-sm animate-pulse">
                    <iconify-icon icon="lucide:alert-triangle" class="text-xl text-yellow-600"></iconify-icon>
                    <span class="font-medium"><?php echo $warning; ?></span>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="p-4 bg-red-100 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
                    <iconify-icon icon="lucide:alert-circle" class="text-xl"></iconify-icon>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (!$supabaseConnected): ?>
                <div class="p-4 bg-yellow-50 text-yellow-800 border border-yellow-200 rounded-xl">
                    <iconify-icon icon="lucide:alert-triangle" class="inline mr-2"></iconify-icon>
                    PERINGATAN: Supabase tidak terhubung! Perubahan tidak dapat disimpan.
                </div>
            <?php endif; ?>

            <!-- PANDUAN PENGISIAN ANGGARAN -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-6 shadow-sm flex items-start gap-4">
                <div class="p-3 bg-blue-500 text-white rounded-xl shadow-sm">
                    <iconify-icon icon="lucide:info" class="text-2xl flex-shrink-0"></iconify-icon>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-gray-800 text-base mb-1">Panduan Pengisian Anggaran & Realisasi Dana BOSN</h4>
                    <p class="text-sm text-gray-600 mb-3">Ikuti langkah-langkah berikut untuk menginput data secara berurutan agar sistem dapat mengakumulasikan data dengan benar:</p>
                    <ol class="list-decimal list-inside text-sm text-gray-700 space-y-2">
                        <li><strong>Input Anggaran Semester Terlebih Dahulu:</strong> Buka tab <span class="font-semibold text-blue-700">Anggaran BOSN (Semester)</span>, lalu tambahkan pagu total untuk semester yang bersangkutan (misal Semester 1 atau Semester 2 pada tahun tertentu).</li>
                        <li><strong>Input Target & Realisasi Bulanan:</strong> Buka tab <span class="font-semibold text-blue-700">Realisasi Bulanan</span>, lalu input target anggaran dan pengeluaran aktual bulanan sesuai dengan bulan-bulan di semester tersebut.</li>
                        <li><strong>Akumulasi Otomatis:</strong> Nilai **Anggaran per Tahun** di bagian atas tabel secara otomatis akan terbentuk dari penjumlahan anggaran semester yang Anda inputkan.</li>
                    </ol>
                </div>
            </div>

            <!-- TAB NAVIGATION -->
            <div class="flex gap-4 border-b border-gray-200 pb-2">
                <button onclick="showTab('anggaran_semester')" id="tab-anggaran_semester" class="px-6 py-3 text-sm font-semibold border-b-2 border-blue-500 text-blue-600 transition-all">
                    <iconify-icon icon="lucide:wallet" class="inline mr-2"></iconify-icon>Anggaran BOSN (Semester)
                </button>
                <button onclick="showTab('realisasi')" id="tab-realisasi" class="px-6 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all">
                    <iconify-icon icon="lucide:calendar" class="inline mr-2"></iconify-icon>Realisasi Bulanan
                </button>
                <button onclick="showTab('rencana')" id="tab-rencana" class="px-6 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all">
                    <iconify-icon icon="lucide:clipboard-list" class="inline mr-2"></iconify-icon>Rencana Anggaran
                </button>
            </div>

            <!-- ANGGARAN BOSN -->
            <div id="content-anggaran">
                <section>
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">Total Anggaran Dana BOSN</h3>
                    </div>
                    <div class="flex justify-end mb-4">
                        <div class="flex bg-white border border-gray-200 rounded-xl p-1">
                            <button onclick="setAnggaranView('table')" id="btn-anggaran-table" class="px-4 py-2 rounded-lg transition-all font-medium flex items-center gap-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md">
                                <iconify-icon icon="lucide:table"></iconify-icon> Tabel
                            </button>
                            <button onclick="setAnggaranView('grid')" id="btn-anggaran-grid" class="px-4 py-2 rounded-lg transition-all font-medium flex items-center gap-2">
                                <iconify-icon icon="lucide:grid"></iconify-icon> Grid
                            </button>
                        </div>
                    </div>
                    
                    <!-- Tabel View -->
                    <div id="anggaran-table-view" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Tahun</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Total Anggaran</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Realisasi</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Capaian</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($anggaran as $a): 
                                        $persen = $a['total_anggaran'] > 0 ? round(($a['realisasi'] / $a['total_anggaran']) * 100, 1) : 0;
                                    ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($a['tahun']); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-600">Rp <?php echo number_format($a['total_anggaran'], 0, ',', '.'); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-600">Rp <?php echo number_format($a['realisasi'], 0, ',', '.'); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-32 bg-gray-200 rounded-full h-3 overflow-hidden">
                                                    <div class="h-full rounded-full transition-all duration-1000 <?php echo $persen >=80 ? 'bg-green-500' : ($persen >=50 ? 'bg-yellow-500' : 'bg-red-500'); ?>" style="width: <?php echo $persen; ?>%"></div>
                                                </div>
                                                <span class="font-semibold text-sm <?php echo $persen >= 80 ? 'text-green-600' : ($persen >=50 ? 'text-yellow-600' : 'text-red-600'); ?>"><?php echo $persen; ?>%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
 
                    <!-- Grid View -->
                    <div id="anggaran-grid-view" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
                        <?php foreach ($anggaran as $a): 
                            $persen = $a['total_anggaran'] > 0 ? round(($a['realisasi'] / $a['total_anggaran']) * 100, 1) : 0;
                        ?>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                            <div class="p-6">
                                <h4 class="text-xl font-bold text-gray-800 mb-3">Tahun <?php echo $a['tahun']; ?></h4>
                                <div class="space-y-2 text-sm text-gray-600 mb-4">
                                    <p><span class="font-semibold">Anggaran:</span> Rp <?php echo number_format($a['total_anggaran'], 0, ',', '.'); ?></p>
                                    <p><span class="font-semibold">Realisasi:</span> Rp <?php echo number_format($a['realisasi'], 0, ',', '.'); ?></p>
                                </div>
                                <div class="mb-4">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span>Capaian</span>
                                        <span class="font-bold <?php echo $persen >= 80 ? 'text-green-600' : ($persen >=50 ? 'text-yellow-600' : 'text-red-600'); ?>"><?php echo $persen; ?>%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-1000 <?php echo $persen >=80 ? 'bg-green-500' : ($persen >=50 ? 'bg-yellow-500' : 'bg-red-500'); ?>" style="width: <?php echo $persen; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <!-- REALISASI BULANAN -->
            <div id="content-realisasi" class="hidden">
                <section>
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">Realisasi Bulanan</h3>
                        <button onclick="document.getElementById('modalRealisasi').classList.remove('hidden')" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-gradient-to-r from-purple-500 to-purple-600 text-white text-sm font-semibold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-2 disabled:opacity-50">
                            <iconify-icon icon="lucide:plus"></iconify-icon>
                            Tambah Realisasi
                        </button>
                    </div>
                    <div class="flex justify-end mb-4">
                        <div class="flex bg-white border border-gray-200 rounded-xl p-1">
                            <button onclick="setRealisasiView('table')" id="btn-realisasi-table" class="px-4 py-2 rounded-lg transition-all font-medium flex items-center gap-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-md">
                                <iconify-icon icon="lucide:table"></iconify-icon> Tabel
                            </button>
                            <button onclick="setRealisasiView('grid')" id="btn-realisasi-grid" class="px-4 py-2 rounded-lg transition-all font-medium flex items-center gap-2">
                                <iconify-icon icon="lucide:grid"></iconify-icon> Grid
                            </button>
                        </div>
                    </div>
                    
                    <!-- Tabel View -->
                    <div id="realisasi-table-view" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gradient-to-r from-purple-500 to-purple-600 text-white">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Bulan</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Tahun</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Anggaran</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Realisasi</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Capaian</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($realisasi as $r): 
                                        $persen = $r['anggaran'] > 0 ? round(($r['realisasi'] / $r['anggaran']) * 100, 1) : 0;
                                    ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($r['bulan']); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-600"><?php echo htmlspecialchars($r['tahun']); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-600">Rp <?php echo number_format($r['anggaran'], 0, ',', '.'); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-600">Rp <?php echo number_format($r['realisasi'], 0, ',', '.'); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-32 bg-gray-200 rounded-full h-3 overflow-hidden">
                                                    <div class="h-full rounded-full transition-all duration-1000 <?php echo $persen >=80 ? 'bg-green-500' : ($persen >=50 ? 'bg-yellow-500' : 'bg-red-500'); ?>" style="width: <?php echo $persen; ?>%"></div>
                                                </div>
                                                <span class="font-semibold text-sm <?php echo $persen >= 80 ? 'text-green-600' : ($persen >=50 ? 'text-yellow-600' : 'text-red-600'); ?>"><?php echo $persen; ?>%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <?php if ($r['file_pdf']): ?>
                                                    <a href="<?php echo $r['file_pdf']; ?>" target="_blank" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-all">
                                                        <iconify-icon icon="lucide:file-text"></iconify-icon>
                                                    </a>
                                                <?php endif; ?>
                                                <button onclick="openEditRealisasi('<?php echo htmlspecialchars($r['id']); ?>', '<?php echo htmlspecialchars($r['tahun']); ?>', '<?php echo htmlspecialchars($r['bulan']); ?>', '<?php echo $r['semester'] ?? ''; ?>', '<?php echo $r['anggaran']; ?>', '<?php echo $r['realisasi']; ?>', '<?php echo htmlspecialchars($r['file_pdf'] ?? ''); ?>')" class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-all">
                                                    <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                                                </button>
                                                <a href="?hapus_realisasi=<?php echo $r['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                                    <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Grid View -->
                    <div id="realisasi-grid-view" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
                        <?php foreach ($realisasi as $r): 
                            $persen = $r['anggaran'] > 0 ? round(($r['realisasi'] / $r['anggaran']) * 100, 1) : 0;
                        ?>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                            <div class="p-6">
                                <h4 class="text-xl font-bold text-gray-800 mb-3"><?php echo $r['bulan']; ?> <?php echo $r['tahun']; ?></h4>
                                <div class="space-y-2 text-sm text-gray-600 mb-4">
                                    <p><span class="font-semibold">Anggaran:</span> Rp <?php echo number_format($r['anggaran'], 0, ',', '.'); ?></p>
                                    <p><span class="font-semibold">Realisasi:</span> Rp <?php echo number_format($r['realisasi'], 0, ',', '.'); ?></p>
                                </div>
                                <div class="mb-4">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span>Capaian</span>
                                        <span class="font-bold <?php echo $persen >= 80 ? 'text-green-600' : ($persen >=50 ? 'text-yellow-600' : 'text-red-600'); ?>"><?php echo $persen; ?>%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-1000 <?php echo $persen >=80 ? 'bg-green-500' : ($persen >=50 ? 'bg-yellow-500' : 'bg-red-500'); ?>" style="width: <?php echo $persen; ?>%"></div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <?php if ($r['file_pdf']): ?>
                                            <a href="<?php echo $r['file_pdf']; ?>" target="_blank" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-all">
                                                <iconify-icon icon="lucide:file-text"></iconify-icon>
                                            </a>
                                        <?php endif; ?>
                                        <button onclick="openEditRealisasi('<?php echo htmlspecialchars($r['id']); ?>', '<?php echo htmlspecialchars($r['tahun']); ?>', '<?php echo htmlspecialchars($r['bulan']); ?>', '<?php echo $r['semester'] ?? ''; ?>', '<?php echo $r['anggaran']; ?>', '<?php echo $r['realisasi']; ?>', '<?php echo htmlspecialchars($r['file_pdf'] ?? ''); ?>')" class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-all">
                                            <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                                        </button>
                                        <a href="?hapus_realisasi=<?php echo $r['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                            <iconify-icon icon="lucide:trash-2" class="w-5 h-5"></iconify-icon>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <!-- RENCANA -->
            <div id="content-rencana" class="hidden">
                <section>
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">Rencana Anggaran</h3>
                        <button onclick="document.getElementById('modalRencana').classList.remove('hidden')" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-gradient-to-r from-green-500 to-green-600 text-white text-sm font-semibold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-2 disabled:opacity-50">
                            <iconify-icon icon="lucide:plus"></iconify-icon>
                            Tambah Rencana
                        </button>
                    </div>
                    <div class="flex justify-end mb-4">
                        <div class="flex bg-white border border-gray-200 rounded-xl p-1">
                            <button onclick="setRencanaView('table')" id="btn-rencana-table" class="px-4 py-2 rounded-lg transition-all font-medium flex items-center gap-2 bg-gradient-to-r from-green-500 to-green-600 text-white shadow-md">
                                <iconify-icon icon="lucide:table"></iconify-icon> Tabel
                            </button>
                            <button onclick="setRencanaView('grid')" id="btn-rencana-grid" class="px-4 py-2 rounded-lg transition-all font-medium flex items-center gap-2">
                                <iconify-icon icon="lucide:grid"></iconify-icon> Grid
                            </button>
                        </div>
                    </div>
                    
                    <?php 
                        $jenis_label = [
                            'pendek' => 'Rencana Jangka Pendek',
                            'menengah' => 'Rencana Jangka Menengah',
                            'panjang' => 'Rencana Jangka Panjang'
                        ];
                    ?>

                    <!-- Tabel View -->
                    <div id="rencana-table-view" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gradient-to-r from-green-500 to-green-600 text-white">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Jenis Rencana</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Judul</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Deskripsi</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($rencana as $rc): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                                <?php echo htmlspecialchars(strtoupper($jenis_label[$rc['jenis_rencana']] ?? $rc['jenis_rencana'])); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($rc['judul']); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-500 text-sm line-clamp-2"><?php echo htmlspecialchars($rc['deskripsi'] ?? '-'); ?></span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <?php if ($rc['file_pdf']): ?>
                                                    <a href="<?php echo $rc['file_pdf']; ?>" target="_blank" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-all">
                                                        <iconify-icon icon="lucide:file-text"></iconify-icon>
                                                    </a>
                                                <?php endif; ?>
                                                <button onclick='openEditRencana(<?php echo json_encode($rc['id']); ?>, <?php echo json_encode($rc['jenis_rencana']); ?>, <?php echo json_encode($rc['judul']); ?>, <?php echo json_encode($rc['deskripsi'] ?? ''); ?>, <?php echo json_encode($rc['file_pdf'] ?? ''); ?>)' class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-all">
                                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                                </button>
                                                <a href="?hapus_rencana=<?php echo $rc['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                                    <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Grid View -->
                    <div id="rencana-grid-view" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
                        <?php foreach ($rencana as $rc): ?>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                            <div class="p-6">
                                <div class="text-xs uppercase font-bold text-gray-500 mb-2"><?php echo $jenis_label[$rc['jenis_rencana']] ?? $rc['jenis_rencana']; ?></div>
                                <h4 class="text-lg font-bold text-gray-800 mb-2"><?php echo $rc['judul']; ?></h4>
                                <?php if ($rc['deskripsi']): ?>
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-3"><?php echo htmlspecialchars($rc['deskripsi']); ?></p>
                                <?php endif; ?>
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <?php if ($rc['file_pdf']): ?>
                                            <a href="<?php echo $rc['file_pdf']; ?>" target="_blank" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-all">
                                                <iconify-icon icon="lucide:file-text"></iconify-icon>
                                            </a>
                                        <?php endif; ?>
                                        <button onclick='openEditRencana(<?php echo json_encode($rc['id']); ?>, <?php echo json_encode($rc['jenis_rencana']); ?>, <?php echo json_encode($rc['judul']); ?>, <?php echo json_encode($rc['deskripsi'] ?? ''); ?>, <?php echo json_encode($rc['file_pdf'] ?? ''); ?>)' class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-all">
                                            <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                                        </button>
                                        <a href="?hapus_rencana=<?php echo $rc['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                            <iconify-icon icon="lucide:trash-2" class="w-5 h-5"></iconify-icon>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <!-- ANGGARAN SEMESTER -->
            <div id="content-anggaran_semester">
                <section>
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">Anggaran per Semester</h3>
                        <button onclick="document.getElementById('modalAnggaranSemester').classList.remove('hidden')" <?php echo !$supabaseConnected ? 'disabled' : ''; ?> class="bg-gradient-to-r from-amber-500 to-amber-600 text-white text-sm font-semibold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-2 disabled:opacity-50">
                            <iconify-icon icon="lucide:plus"></iconify-icon>
                            Tambah Anggaran Semester
                        </button>
                    </div>
                    <div class="flex justify-end mb-4">
                        <div class="flex bg-white border border-gray-200 rounded-xl p-1">
                            <button onclick="setAnggaranSemesterView('table')" id="btn-anggaran_semester-table" class="px-4 py-2 rounded-lg transition-all font-medium flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 text-white shadow-md">
                                <iconify-icon icon="lucide:table"></iconify-icon> Tabel
                            </button>
                            <button onclick="setAnggaranSemesterView('grid')" id="btn-anggaran_semester-grid" class="px-4 py-2 rounded-lg transition-all font-medium flex items-center gap-2">
                                <iconify-icon icon="lucide:grid"></iconify-icon> Grid
                            </button>
                        </div>
                    </div>
                    
                    <!-- Tabel View -->
                    <div id="anggaran_semester-table-view" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gradient-to-r from-amber-500 to-amber-600 text-white">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Detail</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Tahun</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Semester</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Total Anggaran</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Total Realisasi</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Sisa Anggaran</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Capaian</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($anggaran_semester as $as): 
                                        $realisasi_tot = $as['total_realisasi'] ?? 0;
                                        $sisa = $as['total_anggaran'] - $realisasi_tot;
                                        $persen = $as['total_anggaran'] > 0 ? round(($realisasi_tot / $as['total_anggaran']) * 100, 1) : 0;
                                    ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <button onclick="toggleBreakdown('breakdown-<?php echo $as['id']; ?>')" class="p-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-all flex items-center justify-center">
                                                <iconify-icon icon="lucide:chevron-down" id="icon-breakdown-<?php echo $as['id']; ?>" class="transition-transform duration-200"></iconify-icon>
                                            </button>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($as['tahun']); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Semester <?php echo htmlspecialchars($as['semester']); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-600">Rp <?php echo number_format($as['total_anggaran'], 0, ',', '.'); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-600">Rp <?php echo number_format($realisasi_tot, 0, ',', '.'); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="font-semibold <?php echo $sisa >= 0 ? 'text-green-600' : 'text-red-600'; ?>">Rp <?php echo number_format($sisa, 0, ',', '.'); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-24 bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                                    <div class="h-full rounded-full transition-all duration-1000 <?php echo $persen >=80 ? 'bg-green-500' : ($persen >=50 ? 'bg-yellow-500' : 'bg-red-500'); ?>" style="width: <?php echo $persen; ?>%"></div>
                                                </div>
                                                <span class="font-bold text-xs <?php echo $persen >= 80 ? 'text-green-600' : ($persen >=50 ? 'text-yellow-600' : 'text-red-600'); ?>"><?php echo $persen; ?>%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <button onclick="openEditAnggaranSemester(this)" 
                                                    data-id="<?php echo htmlspecialchars($as['id']); ?>"
                                                    data-tahun="<?php echo htmlspecialchars($as['tahun']); ?>"
                                                    data-semester="<?php echo htmlspecialchars($as['semester']); ?>"
                                                    data-total_anggaran="<?php echo htmlspecialchars($as['total_anggaran']); ?>"
                                                    class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-all">
                                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                                </button>
                                                <a href="?hapus_anggaran_semester=<?php echo $as['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                                    <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Accordion Breakdown Row -->
                                    <tr id="breakdown-<?php echo $as['id']; ?>" class="hidden bg-slate-50/70 border-b border-gray-100">
                                        <td colspan="8" class="px-8 py-5">
                                            <div class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
                                                <iconify-icon icon="lucide:align-left" class="text-amber-500"></iconify-icon> Detail Breakdown Bulanan (Semester <?php echo $as['semester']; ?>)
                                            </div>
                                            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                                                <?php 
                                                    $months = ($as['semester'] == 1) 
                                                        ? ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']
                                                        : ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                                    for ($i=1; $i<=6; $i++):
                                                        $ang_m = $as['anggaran_m'.$i] ?? 0;
                                                        $real_m = $as['realisasi_m'.$i] ?? 0;
                                                        $diff_m = $ang_m - $real_m;
                                                ?>
                                                <div class="p-3 bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
                                                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider"><?php echo $months[$i-1]; ?></span>
                                                    <div class="mt-2 space-y-1.5 text-xs text-gray-600">
                                                        <div class="flex justify-between"><span class="text-gray-400">Anggaran:</span> <strong>Rp <?= number_format($ang_m, 0, ',', '.') ?></strong></div>
                                                        <div class="flex justify-between"><span class="text-gray-400">Realisasi:</span> <strong>Rp <?= number_format($real_m, 0, ',', '.') ?></strong></div>
                                                        <div class="flex justify-between border-t pt-1 mt-1 border-gray-100">
                                                            <span class="text-gray-400">Sisa:</span> 
                                                            <strong class="<?= $diff_m >= 0 ? 'text-green-600' : 'text-red-600' ?>">Rp <?= number_format($diff_m, 0, ',', '.') ?></strong>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endfor; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Grid View -->
                    <div id="anggaran_semester-grid-view" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
                        <?php foreach ($anggaran_semester as $as): 
                            $realisasi_tot = $as['total_realisasi'] ?? 0;
                            $sisa = $as['total_anggaran'] - $realisasi_tot;
                            $persen = $as['total_anggaran'] > 0 ? round(($realisasi_tot / $as['total_anggaran']) * 100, 1) : 0;
                        ?>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <div class="text-xs uppercase font-bold text-gray-500">Tahun <?php echo htmlspecialchars($as['tahun']); ?></div>
                                        <h4 class="text-lg font-bold text-gray-800">Semester <?php echo htmlspecialchars($as['semester']); ?></h4>
                                    </div>
                                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-full bg-amber-100 text-amber-700">BOSN</span>
                                </div>
                                <div class="space-y-2 text-sm text-gray-600 mb-4 border-t pt-3">
                                    <p class="flex justify-between"><span class="font-medium">Total Anggaran:</span> <span class="font-bold text-gray-800">Rp <?php echo number_format($as['total_anggaran'], 0, ',', '.'); ?></span></p>
                                    <p class="flex justify-between"><span class="font-medium">Total Realisasi:</span> <span class="font-bold text-gray-800">Rp <?php echo number_format($realisasi_tot, 0, ',', '.'); ?></span></p>
                                    <p class="flex justify-between"><span class="font-medium">Sisa:</span> <span class="font-bold <?php echo $sisa >= 0 ? 'text-green-600' : 'text-red-600'; ?>">Rp <?php echo number_format($sisa, 0, ',', '.'); ?></span></p>
                                </div>
                                <div class="mb-4">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span>Capaian</span>
                                        <span class="font-bold <?php echo $persen >= 80 ? 'text-green-600' : ($persen >=50 ? 'text-yellow-600' : 'text-red-600'); ?>"><?php echo $persen; ?>%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-1000 <?php echo $persen >=80 ? 'bg-green-500' : ($persen >=50 ? 'bg-yellow-500' : 'bg-red-500'); ?>" style="width: <?php echo $persen; ?>%"></div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <button onclick="openEditAnggaranSemester(this)" 
                                            data-id="<?php echo htmlspecialchars($as['id']); ?>"
                                            data-tahun="<?php echo htmlspecialchars($as['tahun']); ?>"
                                            data-semester="<?php echo htmlspecialchars($as['semester']); ?>"
                                            data-total_anggaran="<?php echo htmlspecialchars($as['total_anggaran']); ?>"
                                            class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-all">
                                            <iconify-icon icon="lucide:edit" class="w-5 h-5"></iconify-icon>
                                        </button>
                                        <a href="?hapus_anggaran_semester=<?php echo $as['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                            <iconify-icon icon="lucide:trash-2" class="w-5 h-5"></iconify-icon>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

        </div>
    </div>
</main>

<!-- MODAL TAMBAH REALISASI -->
<div id="modalRealisasi" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-purple-500 to-purple-600">
            <h3 class="font-semibold text-lg text-white">Tambah Realisasi Bulanan</h3>
            <button onclick="document.getElementById('modalRealisasi').classList.add('hidden')" class="p-2 text-white/80 hover:bg-white/20 rounded-xl transition-all">
                <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-5" onsubmit="return validateRealisasiForm(this);">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun</label>
                <input type="text" name="tahun_realisasi" id="tambah_realisasi_tahun" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all" placeholder="2024">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Semester</label>
                <select name="semester" id="tambah_realisasi_semester" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all">
                    <option value="1">1 (Januari - Juni)</option>
                    <option value="2">2 (Juli - Desember)</option>
                </select>
            </div>
            <div>
                <!-- Info Box for Semester Budget, Target and Realisasi -->
                <div id="tambah_sem_info_box" class="p-3 bg-purple-50 rounded-xl text-xs text-purple-900 border border-purple-100 space-y-1 mb-2 hidden">
                    <div><strong>Pagu Anggaran Semester:</strong> <span id="tambah_sem_info_total">Rp 0</span></div>
                    <div><strong>Total Target Bulanan:</strong> <span id="tambah_sem_info_target">Rp 0</span></div>
                    <div><strong>Total Realisasi Bulanan:</strong> <span id="tambah_sem_info_realisasi">Rp 0</span></div>
                </div>
                
                <label class="block text-sm font-semibold text-gray-700 mb-2">Bulan</label>
                <select name="bulan" id="tambah_realisasi_bulan" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all">
                    <option>Januari</option>
                    <option>Februari</option>
                    <option>Maret</option>
                    <option>April</option>
                    <option>Mei</option>
                    <option>Juni</option>
                    <option>Juli</option>
                    <option>Agustus</option>
                    <option>September</option>
                    <option>Oktober</option>
                    <option>November</option>
                    <option>Desember</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Anggaran Bulanan</label>
                <input type="text" name="anggaran_bulan" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all uang" placeholder="Rp 0">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Realisasi Bulanan</label>
                <input type="text" name="realisasi_bulan" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all uang" placeholder="Rp 0">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload PDF (Opsional)</label>
                <input type="file" name="file_pdf_realisasi" accept=".pdf" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all">
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modalRealisasi').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                    Batal
                </button>
                <button type="submit" name="tambah_realisasi" class="px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl hover:from-purple-600 hover:to-purple-700 transition-all shadow-md">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT REALISASI -->
<div id="modalEditRealisasi" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-purple-500 to-purple-600">
            <h3 class="font-semibold text-lg text-white">Edit Realisasi Bulanan</h3>
            <button onclick="document.getElementById('modalEditRealisasi').classList.add('hidden')" class="p-2 text-white/80 hover:bg-white/20 rounded-xl transition-all">
                <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-5" onsubmit="return validateRealisasiForm(this);">
            <input type="hidden" name="id" id="edit_realisasi_id">
            <input type="hidden" name="existing_pdf" id="edit_realisasi_existing_pdf">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun</label>
                <input type="text" name="tahun_realisasi" id="edit_realisasi_tahun" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all" placeholder="2024">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Semester</label>
                <select name="semester" id="edit_realisasi_semester" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all">
                    <option value="1">1 (Januari - Juni)</option>
                    <option value="2">2 (Juli - Desember)</option>
                </select>
            </div>
            <div>
                <!-- Info Box for Semester Budget, Target and Realisasi -->
                <div id="edit_sem_info_box" class="p-3 bg-purple-50 rounded-xl text-xs text-purple-900 border border-purple-100 space-y-1 mb-2 hidden">
                    <div><strong>Pagu Anggaran Semester:</strong> <span id="edit_sem_info_total">Rp 0</span></div>
                    <div><strong>Total Target Bulanan:</strong> <span id="edit_sem_info_target">Rp 0</span></div>
                    <div><strong>Total Realisasi Bulanan:</strong> <span id="edit_sem_info_realisasi">Rp 0</span></div>
                </div>
                
                <label class="block text-sm font-semibold text-gray-700 mb-2">Bulan</label>
                <select name="bulan" id="edit_realisasi_bulan" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all">
                    <option>Januari</option>
                    <option>Februari</option>
                    <option>Maret</option>
                    <option>April</option>
                    <option>Mei</option>
                    <option>Juni</option>
                    <option>Juli</option>
                    <option>Agustus</option>
                    <option>September</option>
                    <option>Oktober</option>
                    <option>November</option>
                    <option>Desember</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Anggaran Bulanan</label>
                <input type="text" name="anggaran_bulan" id="edit_realisasi_anggaran" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all uang" placeholder="Rp 0">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Realisasi Bulanan</label>
                <input type="text" name="realisasi_bulan" id="edit_realisasi_realisasi" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all uang" placeholder="Rp 0">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload PDF Baru (Opsional)</label>
                <input type="file" name="file_pdf_realisasi" accept=".pdf" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all">
                <p id="edit_realisasi_pdf_info" class="text-sm text-gray-500 mt-1"></p>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modalEditRealisasi').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                    Batal
                </button>
                <button type="submit" name="edit_realisasi" class="px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl hover:from-purple-600 hover:to-purple-700 transition-all shadow-md">
                    Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH RENCANA -->
<div id="modalRencana" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-green-500 to-green-600">
            <h3 class="font-semibold text-lg text-white">Tambah Rencana</h3>
            <button onclick="document.getElementById('modalRencana').classList.add('hidden')" class="p-2 text-white/80 hover:bg-white/20 rounded-xl transition-all">
                <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Rencana</label>
                <select name="jenis_rencana" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all">
                    <option value="pendek">Rencana Jangka Pendek</option>
                    <option value="menengah">Rencana Jangka Menengah</option>
                    <option value="panjang">Rencana Jangka Panjang</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Judul</label>
                <input type="text" name="judul" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all" placeholder="Judul Rencana">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                <textarea name="deskripsi" rows="4" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all" placeholder="Deskripsi rencana..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload PDF (Opsional)</label>
                <input type="file" name="file_pdf_rencana" accept=".pdf" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all">
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modalRencana').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                    Batal
                </button>
                <button type="submit" name="tambah_rencana" class="px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 transition-all shadow-md">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT RENCANA -->
<div id="modalEditRencana" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-green-500 to-green-600">
            <h3 class="font-semibold text-lg text-white">Edit Rencana</h3>
            <button onclick="document.getElementById('modalEditRencana').classList.add('hidden')" class="p-2 text-white/80 hover:bg-white/20 rounded-xl transition-all">
                <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            <input type="hidden" name="id" id="edit_rencana_id">
            <input type="hidden" name="existing_pdf" id="edit_rencana_existing_pdf">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Rencana</label>
                <select name="jenis_rencana" id="edit_rencana_jenis" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all">
                    <option value="pendek">Rencana Jangka Pendek</option>
                    <option value="menengah">Rencana Jangka Menengah</option>
                    <option value="panjang">Rencana Jangka Panjang</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Judul</label>
                <input type="text" name="judul" id="edit_rencana_judul" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all" placeholder="Judul Rencana">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                <textarea name="deskripsi" id="edit_rencana_deskripsi" rows="4" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all" placeholder="Deskripsi rencana..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload PDF Baru (Opsional)</label>
                <input type="file" name="file_pdf_rencana" accept=".pdf" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all">
                <p id="edit_rencana_pdf_info" class="text-sm text-gray-500 mt-1"></p>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modalEditRencana').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                    Batal
                </button>
                <button type="submit" name="edit_rencana" class="px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 transition-all shadow-md">
                    Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH ANGGARAN SEMESTER -->
<div id="modalAnggaranSemester" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-amber-500 to-amber-600">
            <h3 class="font-semibold text-lg text-white">Tambah Anggaran Semester</h3>
            <button onclick="document.getElementById('modalAnggaranSemester').classList.add('hidden')" class="p-2 text-white/80 hover:bg-white/20 rounded-xl transition-all">
                <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-5" onsubmit="return validateTambahSemesterForm(this);">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun</label>
                <input type="text" name="tahun_semester" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all" placeholder="2024">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Semester</label>
                <select name="semester" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all">
                    <option value="1">1 (Januari - Juni)</option>
                    <option value="2">2 (Juli - Desember)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Total Anggaran Semester</label>
                <input type="text" name="total_anggaran" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all uang" placeholder="Rp 0">
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modalAnggaranSemester').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                    Batal
                </button>
                <button type="submit" name="tambah_anggaran_semester" class="px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-xl hover:from-amber-600 hover:to-amber-700 transition-all shadow-md">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT ANGGARAN SEMESTER -->
<div id="modalEditAnggaranSemester" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-amber-500 to-amber-600">
            <h3 class="font-semibold text-lg text-white">Edit Anggaran Semester</h3>
            <button onclick="document.getElementById('modalEditAnggaranSemester').classList.add('hidden')" class="p-2 text-white/80 hover:bg-white/20 rounded-xl transition-all">
                <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-5" onsubmit="return validateEditSemesterForm(this);">
            <input type="hidden" name="id_anggaran_semester" id="edit_anggaran_semester_id">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun</label>
                <input type="text" name="tahun_semester" id="edit_anggaran_semester_tahun" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all" placeholder="2024">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Semester</label>
                <select name="semester" id="edit_anggaran_semester_semester" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all">
                    <option value="1">1 (Januari - Juni)</option>
                    <option value="2">2 (Juli - Desember)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Total Anggaran Semester</label>
                <input type="text" name="total_anggaran" id="edit_anggaran_semester_total_anggaran" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all uang" placeholder="Rp 0">
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modalEditAnggaranSemester').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                    Batal
                </button>
                <button type="submit" name="edit_anggaran_semester" class="px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-xl hover:from-amber-600 hover:to-amber-700 transition-all shadow-md">
                    Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Tab Navigation
    function showTab(tab) {
        // Hide all tab content
        document.querySelectorAll('#content-anggaran, #content-anggaran_semester, #content-realisasi, #content-rencana').forEach(el => {
            el.classList.add('hidden');
        });

        // Remove active state from all tabs
        document.querySelectorAll('#tab-anggaran_semester, #tab-realisasi, #tab-rencana').forEach(el => {
            el.classList.remove('border-blue-500', 'text-blue-600', 'border-purple-500', 'text-purple-600', 'border-green-500', 'text-green-600');
            el.classList.add('border-transparent', 'text-gray-500');
        });

        // Add active state and show selected tab content
        const activeTab = document.getElementById('tab-' + tab);
        activeTab.classList.remove('border-transparent', 'text-gray-500');
        
        if (tab === 'anggaran_semester') {
            activeTab.classList.add('border-blue-500', 'text-blue-600');
            document.getElementById('content-anggaran').classList.remove('hidden');
            document.getElementById('content-anggaran_semester').classList.remove('hidden');
        } else {
            document.getElementById('content-' + tab).classList.remove('hidden');
            if (tab === 'realisasi') {
                activeTab.classList.add('border-purple-500', 'text-purple-600');
            } else if (tab === 'rencana') {
                activeTab.classList.add('border-green-500', 'text-green-600');
            }
        }
    }

    // Toggle Accordion Breakdown Row
    function toggleBreakdown(id) {
        const row = document.getElementById(id);
        const icon = document.getElementById('icon-' + id);
        if (row.classList.contains('hidden')) {
            row.classList.remove('hidden');
            icon.style.transform = 'rotate(180deg)';
        } else {
            row.classList.add('hidden');
            icon.style.transform = 'rotate(0deg)';
        }
    }

    // Format rupiah otomatis
    document.querySelectorAll('.uang').forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/[^0-9]/g, '');
            if (!value) value = '0';
            this.value = 'Rp ' + parseInt(value).toLocaleString('id-ID');
        });
        // Format saat load
        if (input.value && input.value.startsWith('Rp')) {
            // Do nothing
        } else if (input.value) {
            input.value = 'Rp ' + parseInt(input.value).toLocaleString('id-ID');
        }
    });


    function openEditAnggaranSemester(btn) {
        const id = btn.getAttribute('data-id');
        const tahun = btn.getAttribute('data-tahun');
        const semester = btn.getAttribute('data-semester');
        const totalAnggaran = btn.getAttribute('data-total_anggaran') || '0';

        document.getElementById('edit_anggaran_semester_id').value = id;
        document.getElementById('edit_anggaran_semester_tahun').value = tahun;
        document.getElementById('edit_anggaran_semester_semester').value = semester;
        document.getElementById('edit_anggaran_semester_total_anggaran').value = 'Rp ' + parseInt(totalAnggaran).toLocaleString('id-ID');

        document.getElementById('modalEditAnggaranSemester').classList.remove('hidden');
    }

    function openEditRencana(id, jenis, judul, deskripsi, pdf) {
        document.getElementById('edit_rencana_id').value = id;
        document.getElementById('edit_rencana_jenis').value = jenis;
        document.getElementById('edit_rencana_judul').value = judul;
        document.getElementById('edit_rencana_deskripsi').value = deskripsi;
        document.getElementById('edit_rencana_existing_pdf').value = pdf;
        
        let pdfInfo = document.getElementById('edit_rencana_pdf_info');
        if (pdf) {
            pdfInfo.innerHTML = '<a href="' + pdf + '" target="_blank" class="text-blue-600 hover:underline">Lihat PDF saat ini</a>';
        } else {
            pdfInfo.innerHTML = '';
        }
        
        document.getElementById('modalEditRencana').classList.remove('hidden');
    }

    function setAnggaranSemesterView(view) {
        const gridView = document.getElementById('anggaran_semester-grid-view');
        const tableView = document.getElementById('anggaran_semester-table-view');
        const btnGrid = document.getElementById('btn-anggaran_semester-grid');
        const btnTable = document.getElementById('btn-anggaran_semester-table');

        if (view === 'table') {
            gridView.classList.add('hidden');
            tableView.classList.remove('hidden');
            btnTable.classList.add('bg-gradient-to-r', 'from-amber-500', 'to-amber-600', 'text-white', 'shadow-md');
            btnGrid.classList.remove('bg-gradient-to-r', 'from-amber-500', 'to-amber-600', 'text-white', 'shadow-md');
        } else {
            gridView.classList.remove('hidden');
            tableView.classList.add('hidden');
            btnGrid.classList.add('bg-gradient-to-r', 'from-amber-500', 'to-amber-600', 'text-white', 'shadow-md');
            btnTable.classList.remove('bg-gradient-to-r', 'from-amber-500', 'to-amber-600', 'text-white', 'shadow-md');
        }
    }

    function setRencanaView(view) {
        const gridView = document.getElementById('rencana-grid-view');
        const tableView = document.getElementById('rencana-table-view');
        const btnGrid = document.getElementById('btn-rencana-grid');
        const btnTable = document.getElementById('btn-rencana-table');

        if (view === 'table') {
            gridView.classList.add('hidden');
            tableView.classList.remove('hidden');
            btnTable.classList.add('bg-gradient-to-r', 'from-green-500', 'to-green-600', 'text-white', 'shadow-md');
            btnGrid.classList.remove('bg-gradient-to-r', 'from-green-500', 'to-green-600', 'text-white', 'shadow-md');
        } else {
            gridView.classList.remove('hidden');
            tableView.classList.add('hidden');
            btnGrid.classList.add('bg-gradient-to-r', 'from-green-500', 'to-green-600', 'text-white', 'shadow-md');
            btnTable.classList.remove('bg-gradient-to-r', 'from-green-500', 'to-green-600', 'text-white', 'shadow-md');
        }
    }


    function openEditRealisasi(id, tahun, bulan, semester, anggaran, realisasi, pdf) {
        document.getElementById('edit_realisasi_id').value = id;
        document.getElementById('edit_realisasi_tahun').value = tahun;
        const semesterValue = semester || (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'].includes(bulan) ? '1' : '2');
        document.getElementById('edit_realisasi_semester').value = semesterValue;
        updateMonthOptions('edit', bulan);
        document.getElementById('edit_realisasi_anggaran').value = 'Rp ' + parseInt(anggaran).toLocaleString('id-ID');
        document.getElementById('edit_realisasi_realisasi').value = 'Rp ' + parseInt(realisasi).toLocaleString('id-ID');
        document.getElementById('edit_realisasi_existing_pdf').value = pdf;
        
        let pdfInfo = document.getElementById('edit_realisasi_pdf_info');
        if (pdf) {
            pdfInfo.innerHTML = '<a href="' + pdf + '" target="_blank" class="text-blue-600 hover:underline">Lihat PDF saat ini</a>';
        } else {
            pdfInfo.innerHTML = '';
        }
        
        updateSemesterInfo('edit', id);
        document.getElementById('modalEditRealisasi').classList.remove('hidden');
    }

    function setAnggaranView(view) {
        const gridView = document.getElementById('anggaran-grid-view');
        const tableView = document.getElementById('anggaran-table-view');
        const btnGrid = document.getElementById('btn-anggaran-grid');
        const btnTable = document.getElementById('btn-anggaran-table');

        if (view === 'table') {
            gridView.classList.add('hidden');
            tableView.classList.remove('hidden');
            btnTable.classList.add('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white', 'shadow-md');
            btnGrid.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white', 'shadow-md');
        } else {
            gridView.classList.remove('hidden');
            tableView.classList.add('hidden');
            btnGrid.classList.add('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white', 'shadow-md');
            btnTable.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white', 'shadow-md');
        }
    }

    function setRealisasiView(view) {
        const gridView = document.getElementById('realisasi-grid-view');
        const tableView = document.getElementById('realisasi-table-view');
        const btnGrid = document.getElementById('btn-realisasi-grid');
        const btnTable = document.getElementById('btn-realisasi-table');

        if (view === 'table') {
            gridView.classList.add('hidden');
            tableView.classList.remove('hidden');
            btnTable.classList.add('bg-gradient-to-r', 'from-purple-500', 'to-purple-600', 'text-white', 'shadow-md');
            btnGrid.classList.remove('bg-gradient-to-r', 'from-purple-500', 'to-purple-600', 'text-white', 'shadow-md');
        } else {
            gridView.classList.remove('hidden');
            tableView.classList.add('hidden');
            btnGrid.classList.add('bg-gradient-to-r', 'from-purple-500', 'to-purple-600', 'text-white', 'shadow-md');
            btnTable.classList.remove('bg-gradient-to-r', 'from-purple-500', 'to-purple-600', 'text-white', 'shadow-md');
        }
    }

    // Dynamic Months Combolist and Semester Info Box
    const rawSemesters = <?php echo json_encode($anggaran_semester); ?>;
    const rawRealisasi = <?php echo json_encode($realisasi); ?>;

    const monthsSemester1 = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];
    const monthsSemester2 = ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    function updateMonthOptions(modalPrefix, currentSelectedMonth = '') {
        const semSelect = document.getElementById(modalPrefix + '_realisasi_semester') || document.getElementById(modalPrefix + '_semester');
        const monthSelect = document.getElementById(modalPrefix + '_realisasi_bulan') || document.getElementById(modalPrefix + '_bulan');
        
        if (!semSelect || !monthSelect) return;
        
        const sem = semSelect.value;
        const months = (sem === '1') ? monthsSemester1 : monthsSemester2;
        
        monthSelect.innerHTML = '';
        months.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m;
            opt.textContent = m;
            if (m === currentSelectedMonth) {
                opt.selected = true;
            }
            monthSelect.appendChild(opt);
        });
    }

    function updateSemesterInfo(modalPrefix, excludeId = '') {
        const tahunInput = document.getElementById(modalPrefix + '_realisasi_tahun');
        const semSelect = document.getElementById(modalPrefix + '_realisasi_semester');
        const infoBox = document.getElementById(modalPrefix + '_sem_info_box');
        
        if (!tahunInput || !semSelect || !infoBox) return;
        
        const tahun = parseInt(tahunInput.value);
        const sem = parseInt(semSelect.value);
        
        if (isNaN(tahun) || isNaN(sem)) {
            infoBox.classList.add('hidden');
            return;
        }
        
        const semesterRecord = rawSemesters.find(item => parseInt(item.tahun) === tahun && parseInt(item.semester) === sem);
        const semMonths = (sem === 1) ? monthsSemester1 : monthsSemester2;
        let totalTarget = 0;
        let totalRealisasi = 0;
        
        rawRealisasi.forEach(r => {
            if (excludeId && String(r.id) === String(excludeId)) {
                return;
            }
            if (parseInt(r.tahun) === tahun && semMonths.includes(r.bulan)) {
                totalTarget += parseFloat(r.anggaran || 0);
                totalRealisasi += parseFloat(r.realisasi || 0);
            }
        });
        
        const pagu = semesterRecord ? parseFloat(semesterRecord.total_anggaran || 0) : 0;
        
        document.getElementById(modalPrefix + '_sem_info_total').textContent = 'Rp ' + pagu.toLocaleString('id-ID');
        document.getElementById(modalPrefix + '_sem_info_target').textContent = 'Rp ' + totalTarget.toLocaleString('id-ID');
        document.getElementById(modalPrefix + '_sem_info_realisasi').textContent = 'Rp ' + totalRealisasi.toLocaleString('id-ID');
        
        infoBox.classList.remove('hidden');
    }

    function validateRealisasiForm(form) {
        const anggaranInput = form.querySelector('[name="anggaran_bulan"]');
        const realisasiInput = form.querySelector('[name="realisasi_bulan"]');
        
        if (!anggaranInput || !realisasiInput) return true;
        
        const anggaranVal = parseInt(anggaranInput.value.replace(/[^0-9]/g, '')) || 0;
        const realisasiVal = parseInt(realisasiInput.value.replace(/[^0-9]/g, '')) || 0;
        
        if (realisasiVal > anggaranVal) {
            alert('Gagal menyimpan: Realisasi bulanan melebihi anggaran bulanan, tidak bisa di proses!');
            return false;
        }
        return true;
    }

    function validateTambahSemesterForm(form) {
        const tahunInput = form.querySelector('[name="tahun_semester"]');
        const semesterSelect = form.querySelector('[name="semester"]');
        
        if (!tahunInput || !semesterSelect) return true;
        
        const tahunVal = parseInt(tahunInput.value) || 0;
        const semesterVal = parseInt(semesterSelect.value) || 0;
        
        const exists = rawSemesters.some(item => parseInt(item.tahun) === tahunVal && parseInt(item.semester) === semesterVal);
        
        if (exists) {
            return confirm('Data semester ini sudah ada. Apakah anda akan melanjutkan?');
        }
        return true;
    }

    function validateEditSemesterForm(form) {
        const idInput = form.querySelector('[name="id_anggaran_semester"]');
        const tahunInput = form.querySelector('[name="tahun_semester"]');
        const semesterSelect = form.querySelector('[name="semester"]');
        
        if (!tahunInput || !semesterSelect) return true;
        
        const idVal = idInput ? idInput.value : '';
        const tahunVal = parseInt(tahunInput.value) || 0;
        const semesterVal = parseInt(semesterSelect.value) || 0;
        
        const exists = rawSemesters.some(item => String(item.id) !== String(idVal) && parseInt(item.tahun) === tahunVal && parseInt(item.semester) === semesterVal);
        
        if (exists) {
            return confirm('Data semester ini sudah ada. Apakah anda akan melanjutkan?');
        }
        return true;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const tambahTahun = document.getElementById('tambah_realisasi_tahun');
        const tambahSem = document.getElementById('tambah_realisasi_semester');
        
        if (tambahTahun && tambahSem) {
            updateMonthOptions('tambah');
            
            tambahSem.addEventListener('change', () => {
                updateMonthOptions('tambah');
                updateSemesterInfo('tambah');
            });
            
            tambahTahun.addEventListener('input', () => {
                updateSemesterInfo('tambah');
            });
        }
        
        const editTahun = document.getElementById('edit_realisasi_tahun');
        const editSem = document.getElementById('edit_realisasi_semester');
        
        if (editTahun && editSem) {
            editSem.addEventListener('change', () => {
                updateMonthOptions('edit');
                const editId = document.getElementById('edit_realisasi_id').value;
                updateSemesterInfo('edit', editId);
            });
            
            editTahun.addEventListener('input', () => {
                const editId = document.getElementById('edit_realisasi_id').value;
                updateSemesterInfo('edit', editId);
            });
        }

        // Activate tab from URL parameter if present, default to anggaran_semester
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if (tabParam) {
            showTab(tabParam);
        } else {
            showTab('anggaran_semester');
        }
    });
</script>
</body>
</html>
