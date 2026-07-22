<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('web_sekolah_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

function is_logged_in() {
    return !empty($_SESSION['admin_id']) && !empty($_SESSION['admin_username']);
}

function require_login() {
    if (!is_logged_in()) {
        $loginUrl = (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/admin/index.php';
        header('Location: ' . $loginUrl);
        exit;
    }
}

function set_admin_session($admin) {
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_nama'] = $admin['nama'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['admin_login_time'] = time();
}

function logout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

// Backwards compatibility wrapper: some files call requireLogin() (camelCase)
if (!function_exists('requireLogin')) {
    function requireLogin() {
        return require_login();
    }
}
?>