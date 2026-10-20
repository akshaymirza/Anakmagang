<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/Login/koneksi.php';

// ── Hapus remember_me token dari database & cookie ──
if (!empty($_COOKIE['remember_me'])) {
	$rm_hash = hash('sha256', $_COOKIE['remember_me']);
	$del_stmt = mysqli_prepare($conn, "DELETE FROM remember_tokens WHERE token_hash = ?");
	if ($del_stmt) {
		mysqli_stmt_bind_param($del_stmt, "s", $rm_hash);
		mysqli_stmt_execute($del_stmt);
		mysqli_stmt_close($del_stmt);
	}
	// Expire cookie
	setcookie('remember_me', '', [
		'expires'  => time() - 3600,
		'path'     => '/',
		'httponly' => true,
		'samesite' => 'Lax',
	]);
} elseif (!empty($_SESSION['user_id'])) {
	// Fallback: hapus semua token user ini jika cookie sudah hilang
	$del_all = mysqli_prepare($conn, "DELETE FROM remember_tokens WHERE user_id = ?");
	if ($del_all) {
		mysqli_stmt_bind_param($del_all, "i", $_SESSION['user_id']);
		mysqli_stmt_execute($del_all);
		mysqli_stmt_close($del_all);
	}
}
// ── End Remember-Me cleanup ──

$_SESSION = [];

if (ini_get('session.use_cookies')) {
	$cookie_params = session_get_cookie_params();
	setcookie(
		session_name(),
		'',
		time() - 42000,
		$cookie_params['path'],
		$cookie_params['domain'],
		$cookie_params['secure'],
		$cookie_params['httponly']
	);
}

session_destroy();
header('Location: Login/login.php');
exit;
