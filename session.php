<?php
// Paksa zona waktu PHP ke Asia/Jakarta (WIB / UTC+7)
date_default_timezone_set('Asia/Jakarta');

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

// ── Bersihkan session impersonasi jika sebelumnya tersimpan ─────────────
if (isset($_SESSION['impersonate_user'])) {
	unset($_SESSION['impersonate_user']);
	unset($_SESSION['impersonate_role']);
}
if (isset($_SESSION['real_admin_username'])) {
	$_SESSION['username'] = $_SESSION['real_admin_username'];
	$_SESSION['role'] = $_SESSION['real_admin_role'];
	unset($_SESSION['real_admin_username']);
	unset($_SESSION['real_admin_role']);
}

// ── Remember-Me: auto-login dari cookie token ──────────────────────────
if (empty($_SESSION['user_logged_in']) && !empty($_COOKIE['remember_me'])) {
	$_rm_conn_file = __DIR__ . '/Login/koneksi.php';
	if (file_exists($_rm_conn_file)) {
		$_rm_conn_backup = isset($conn) ? $conn : null;
		if (!isset($conn) || !$conn) {
			require_once $_rm_conn_file;
		}

		if (isset($conn) && $conn) {
			@mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `remember_tokens` (
				`id` INT AUTO_INCREMENT PRIMARY KEY,
				`user_id` INT NOT NULL,
				`token_hash` VARCHAR(255) NOT NULL,
				`expires_at` DATETIME NOT NULL,
				`created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
				INDEX `idx_token_hash` (`token_hash`),
				INDEX `idx_user_id` (`user_id`),
				INDEX `idx_expires` (`expires_at`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

			$_rm_token = $_COOKIE['remember_me'];
			$_rm_hash  = hash('sha256', $_rm_token);

			$_rm_stmt = mysqli_prepare($conn, "SELECT rt.id AS token_id, u.id, u.username, u.role
				FROM remember_tokens rt
				JOIN users u ON u.id = rt.user_id
				WHERE rt.token_hash = ? AND rt.expires_at > NOW()
				LIMIT 1");
			if ($_rm_stmt) {
				mysqli_stmt_bind_param($_rm_stmt, "s", $_rm_hash);
				mysqli_stmt_execute($_rm_stmt);
				$_rm_result = mysqli_stmt_get_result($_rm_stmt);
				$_rm_user   = mysqli_fetch_assoc($_rm_result);
				mysqli_stmt_close($_rm_stmt);

				if ($_rm_user) {
					session_regenerate_id(true);
					$_SESSION['user_logged_in'] = true;
					$_SESSION['user_id']        = $_rm_user['id'];
					$_SESSION['username']       = $_rm_user['username'];
					$_SESSION['role']           = $_rm_user['role'];

					$_rm_del = mysqli_prepare($conn, "DELETE FROM remember_tokens WHERE id = ?");
					if ($_rm_del) {
						mysqli_stmt_bind_param($_rm_del, "i", $_rm_user['token_id']);
						mysqli_stmt_execute($_rm_del);
						mysqli_stmt_close($_rm_del);
					}

					$_rm_new_token = bin2hex(random_bytes(32));
					$_rm_new_hash  = hash('sha256', $_rm_new_token);
					$_rm_expires   = date('Y-m-d H:i:s', time() + 30 * 24 * 3600);
					$_rm_ins = mysqli_prepare($conn, "INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
					if ($_rm_ins) {
						mysqli_stmt_bind_param($_rm_ins, "iss", $_rm_user['id'], $_rm_new_hash, $_rm_expires);
						mysqli_stmt_execute($_rm_ins);
						mysqli_stmt_close($_rm_ins);
					}

					$_rm_cookie_path = '/';
					setcookie('remember_me', $_rm_new_token, [
						'expires'  => time() + 30 * 24 * 3600,
						'path'     => $_rm_cookie_path,
						'httponly' => true,
						'samesite' => 'Lax',
						'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
					]);
				} else {
					setcookie('remember_me', '', [
						'expires'  => time() - 3600,
						'path'     => '/',
						'httponly' => true,
						'samesite' => 'Lax',
					]);
				}
			}

			if (rand(1, 100) === 1) {
				@mysqli_query($conn, "DELETE FROM remember_tokens WHERE expires_at < NOW()");
			}
		}

		if ($_rm_conn_backup === null && isset($conn)) {
		}
	}
}
// ── End Remember-Me ─────────────────────────────────────────────────────

if (!function_exists('is_logged_in')) {
	function is_logged_in(): bool
	{
		return !empty($_SESSION['user_logged_in']);
	}
}

if (!function_exists('require_login')) {
	function require_login(): void
	{
		if (!is_logged_in()) {
			if (isset($_GET['action'])) {
				http_response_code(401);
				header('Content-Type: application/json; charset=utf-8');
				echo json_encode(['error' => 'Unauthenticated']);
				exit;
			}
			$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
			$is_admin = strpos($script_name, '/admin/') !== false;
			$login_url = $is_admin ? '../Login/login.php' : 'Login/login.php';
			header('Location: ' . $login_url);
			exit;
		}
	}
}

if (!function_exists('current_user_name')) {
	function current_user_name(): string
	{
		$name = (string) ($_SESSION['username'] ?? $_SESSION['user_id'] ?? 'Pengguna');
		if ($name !== '' && !is_numeric($name)) {
			return ucfirst($name);
		}
		return $name;
	}
}

if (!function_exists('current_user_role')) {
	function current_user_role(): string
	{
		return (string) ($_SESSION['role'] ?? '');
	}
}

if (!function_exists('original_user_role')) {
	function original_user_role(): string
	{
		return (string) ($_SESSION['role'] ?? '');
	}
}

if (!function_exists('is_superadmin')) {
	function is_superadmin(): bool
	{
		return current_user_role() === 'superadmin';
	}
}

if (!function_exists('is_admin')) {
	function is_admin(): bool
	{
		$role = current_user_role();
		return $role === 'admin' || $role === 'superadmin';
	}
}

if (!function_exists('require_admin')) {
	function require_admin(): void
	{
		require_login();
		if (!is_admin()) {
			if (isset($_GET['action'])) {
				http_response_code(403);
				header('Content-Type: application/json; charset=utf-8');
				echo json_encode(['error' => 'Forbidden']);
				exit;
			}
			$dashboard_url = file_exists(__DIR__ . '/dashboard.php') ? 'dashboard.php' : '../dashboard.php';
			header('Location: ' . $dashboard_url);
			exit;
		}
	}
}

if (!function_exists('require_superadmin')) {
	function require_superadmin(): void
	{
		require_login();
		if (!is_superadmin()) {
			if (isset($_GET['action'])) {
				http_response_code(403);
				header('Content-Type: application/json; charset=utf-8');
				echo json_encode(['error' => 'Forbidden']);
				exit;
			}
			$admin_url = file_exists(__DIR__ . '/admin/admin-dashboard.php') ? 'admin/admin-dashboard.php' : 'admin-dashboard.php';
			header('Location: ' . $admin_url);
			exit;
		}
	}
}
