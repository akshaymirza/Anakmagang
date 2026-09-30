<?php
require_once __DIR__ . '/../session.php';

// Hapus semua data session
$_SESSION = [];

// Hapus cookie session juga (kalau browser pakai cookie untuk session)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

// Kembali ke halaman login
header('Location: login.php');
exit;
