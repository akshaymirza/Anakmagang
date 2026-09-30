<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

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
			$login_url = file_exists(__DIR__ . '/Login/login.php') ? 'Login/login.php' : '../Login/login.php';
			header('Location: ' . $login_url);
			exit;
		}
	}
}

if (!function_exists('current_user_name')) {
	function current_user_name(): string
	{
		return (string) ($_SESSION['username'] ?? $_SESSION['user_id'] ?? 'Pengguna');
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
			$admin_url = file_exists(__DIR__ . '/admin/admin-dashboard.php') ? 'admin/admin-dashboard.php' : 'admin-dashboard.php';
			header('Location: ' . $admin_url);
			exit;
		}
	}
}

if (!function_exists('ensure_intern_certificate')) {
	function ensure_intern_certificate($conn, int $user_id, string $username, string $intern_position = '', string $university = '', string $major = ''): void
	{
		if (!$conn || $user_id <= 0) return;

		$chk_user = mysqli_prepare($conn, "SELECT role, username, intern_position, university, major FROM users WHERE id = ? LIMIT 1");
		if (!$chk_user) return;
		mysqli_stmt_bind_param($chk_user, "i", $user_id);
		mysqli_stmt_execute($chk_user);
		$res_u = mysqli_stmt_get_result($chk_user);
		$u_data = mysqli_fetch_assoc($res_u);
		mysqli_stmt_close($chk_user);

		if (!$u_data || $u_data['role'] !== 'intern') return;

		$username = !empty($username) ? $username : ($u_data['username'] ?? 'Intern');
		$intern_position = !empty($intern_position) ? $intern_position : ($u_data['intern_position'] ?? '');
		$university = !empty($university) ? $university : ($u_data['university'] ?? '');
		$major = !empty($major) ? $major : ($u_data['major'] ?? '');

		$chk_cert = mysqli_prepare($conn, "SELECT id FROM certificates WHERE user_id = ? LIMIT 1");
		if ($chk_cert) {
			mysqli_stmt_bind_param($chk_cert, "i", $user_id);
			mysqli_stmt_execute($chk_cert);
			$res_c = mysqli_stmt_get_result($chk_cert);
			if ($c_row = mysqli_fetch_assoc($res_c)) {
				mysqli_stmt_close($chk_cert);
				$upd = mysqli_prepare($conn, "UPDATE certificates SET intern_name = ?, intern_position = IF(? != '', ?, intern_position), university = IF(? != '', ?, university), major = IF(? != '', ?, major) WHERE user_id = ?");
				if ($upd) {
					mysqli_stmt_bind_param($upd, "sssssssi", $username, $intern_position, $intern_position, $university, $university, $major, $major, $user_id);
					mysqli_stmt_execute($upd);
					mysqli_stmt_close($upd);
				}
				return;
			}
			mysqli_stmt_close($chk_cert);
		}

		$cert_code = 'IS-' . date('Y') . '-' . sprintf('%03d', $user_id);
		$c_chk = mysqli_query($conn, "SELECT id FROM certificates WHERE certificate_id = '" . mysqli_real_escape_string($conn, $cert_code) . "'");
		if ($c_chk && mysqli_num_rows($c_chk) > 0) {
			$cert_code = 'IS-' . date('Y') . '-' . sprintf('%03d', $user_id) . '-' . rand(10, 99);
		}

		$pos  = !empty($intern_position) ? $intern_position : 'Magang Web Developer';
		$univ = !empty($university) ? $university : '-';
		$maj  = !empty($major) ? $major : '-';
		$today = date('Y-m-d');
		$end_date = date('Y-m-d', strtotime('+3 months'));

		$ins = mysqli_prepare($conn, "INSERT INTO certificates 
			(certificate_id, user_id, intern_name, intern_position, university, major, start_date, end_date, issue_date, score_technical, score_discipline, score_attitude, final_grade, supervisor_name, status) 
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 85, 85, 85, 'A', 'Shaliza Mirza', 'active')");
		if ($ins) {
			mysqli_stmt_bind_param($ins, "sisssssss", $cert_code, $user_id, $username, $pos, $univ, $maj, $today, $end_date, $end_date);
			mysqli_stmt_execute($ins);
			mysqli_stmt_close($ins);
		}
	}
}

if (!function_exists('sync_all_intern_certificates')) {
	function sync_all_intern_certificates($conn): void
	{
		if (!$conn) return;
		$res = @mysqli_query($conn, "SELECT id, username, intern_position, university, major FROM users WHERE role = 'intern'");
		if ($res) {
			while ($u = mysqli_fetch_assoc($res)) {
				ensure_intern_certificate($conn, (int)$u['id'], (string)$u['username'], (string)($u['intern_position'] ?? ''), (string)($u['university'] ?? ''), (string)($u['major'] ?? ''));
			}
		}
	}
}