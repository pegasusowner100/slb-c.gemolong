<?php
// Loader: prefer the private copy in TIDAK DIUPLOAD
$external = __DIR__ . '/TIDAK DIUPLOAD/reset_admin_password.php';
if (file_exists($external)) {
    require_once $external;
    return;
}

echo "Sensitive script not found in TIDAK DIUPLOAD.\n";
echo "To reset admin password, move reset_admin_password.php into TIDAK DIUPLOAD and run it from CLI.\n";
?>
