
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "1. Memulai file...<br>";
try {
    require_once 'includes/config.php';
    echo "2. config.php berhasil dimuat!<br>";
    
    require_once 'includes/db.php';
    echo "3. db.php berhasil dimuat!<br>";
} catch (Exception $e) {
    echo "ERROR memuat file: " . $e->getMessage() . "<br>";
    die();
}

echo "4. Semua file berhasil dimuat!<br>";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Index</title>
</head>
<body>
    <h1>Berhasil sampai sini!</h1>
</body>
</html>
