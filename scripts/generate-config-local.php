<?php
// scripts/generate-config-local.php
// Usage: php scripts/generate-config-local.php [path/to/.env]
// Reads .env (or .env.example) and generates includes/config.php from includes/config.example.php

$cwd = dirname(__DIR__);
$envPath = $argv[1] ?? $cwd . '/.env';
if (!file_exists($envPath)) {
    $envPath = $cwd . '/.env.example';
}

if (!file_exists($envPath)) {
    fwrite(STDERR, "No .env or .env.example found. Create one based on .env.example template.\n");
    exit(1);
}

$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (!strpos($line, '=')) continue;
    list($k, $v) = explode('=', $line, 2);
    $k = trim($k);
    $v = trim($v);
    // remove surrounding quotes
    $v = preg_replace('/^"(.*)"$/', '$1', $v);
    $v = preg_replace("/^'(.*)'$/", '$1', $v);
    $env[$k] = $v;
}

$examplePath = $cwd . '/includes/config.example.php';
if (!file_exists($examplePath)) {
    fwrite(STDERR, "includes/config.example.php not found.\n");
    exit(1);
}
$template = file_get_contents($examplePath);

$replacements = [
    'SITE_NAME' => $env['SITE_NAME'] ?? null,
    'BASE_URL' => $env['BASE_URL'] ?? ($env['BASE_PATH'] ?? null),
    'SUPABASE_URL' => $env['NEXT_PUBLIC_SUPABASE_URL'] ?? $env['SUPABASE_URL'] ?? null,
    'SUPABASE_KEY' => $env['NEXT_PUBLIC_SUPABASE_PUBLISHABLE_KEY'] ?? $env['SUPABASE_KEY'] ?? null,
    'SUPABASE_SERVICE_KEY' => $env['SUPABASE_SERVICE_KEY'] ?? null,
    'CLOUDINARY_CLOUD_NAME' => $env['CLOUDINARY_CLOUD_NAME'] ?? null,
    'CLOUDINARY_API_KEY' => $env['CLOUDINARY_API_KEY'] ?? null,
    'CLOUDINARY_API_SECRET' => $env['CLOUDINARY_API_SECRET'] ?? null,
    'CLOUDINARY_FOLDER' => $env['CLOUDINARY_FOLDER'] ?? null,
    'CLOUDINARY_UPLOAD_PRESET' => $env['CLOUDINARY_UPLOAD_PRESET'] ?? null,
    'SUPABASE_STORAGE_BUCKET' => $env['SUPABASE_STORAGE_BUCKET'] ?? null,
    'ADMIN_USERNAME' => $env['ADMIN_USERNAME'] ?? null,
    'ADMIN_PASSWORD_SALT' => $env['ADMIN_PASSWORD_SALT'] ?? null,
    'ADMIN_PASSWORD_HASH' => $env['ADMIN_PASSWORD_HASH'] ?? null,
];

foreach ($replacements as $const => $val) {
    if ($val === null) continue; // keep example default
    // Use regex to replace define('CONST', '...') lines
    $pattern = "/define\('\"?" . preg_quote($const, '/') . "\"?'?,\\s*'[^']*'\)/";
    $replacement = "define('" . $const . "', '" . addslashes($val) . "')";
    $template = preg_replace($pattern, $replacement, $template, 1);
}

$outPath = $cwd . '/includes/config.php';
if (file_put_contents($outPath, $template) === false) {
    fwrite(STDERR, "Failed to write $outPath\n");
    exit(1);
}

if (function_exists('posix_getuid')) {
    @chown($outPath, posix_getuid());
}
@chmod($outPath, 0640);

fwrite(STDOUT, "Generated includes/config.php from $envPath\n");
fwrite(STDOUT, "Please DO NOT commit includes/config.php to git.\n");
exit(0);
