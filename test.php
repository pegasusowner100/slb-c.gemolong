
<?php
echo "HALLO! PHP BERJALAN!<br>";
echo "Path direktori: " . __DIR__ . "<br>";
echo "Versi PHP: " . phpversion() . "<br>";
echo "Daftar file di direktori ini:<br>";
print_r(scandir(__DIR__));
?>
