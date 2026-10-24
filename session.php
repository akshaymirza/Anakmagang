<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

// ── Remember-Me: auto-login dari cookie token ──────────────────────────
if (empty($_SESSION['user_logged_in']) && !empty($_COOKIE['remember_me'])) {
	// Lazy-load koneksi database untuk validasi token
	$_rm_conn_file = __DIR__ . '/Login/koneksi.php';
	if (file_exists($_rm_conn_file)) {
		// Simpan $conn lama jika sudah ada, supaya tidak bentrok
		$_rm_conn_backup = isset($conn) ? $conn : null;
		if (!isset($conn) || !$conn) {
			require_once $_rm_conn_file;
		}

		if (isset($conn) && $conn) {
			// Auto-migration: buat tabel remember_tokens jika belum ada
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

			// Cari token yang masih valid (belum expired)
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
					// Restore session
					session_regenerate_id(true);
					$_SESSION['user_logged_in'] = true;
					$_SESSION['user_id']        = $_rm_user['id'];
					$_SESSION['username']       = $_rm_user['username'];
					$_SESSION['role']           = $_rm_user['role'];

					// Token rotation: hapus token lama, buat token baru (keamanan)
					$_rm_del = mysqli_prepare($conn, "DELETE FROM remember_tokens WHERE id = ?");
					if ($_rm_del) {
						mysqli_stmt_bind_param($_rm_del, "i", $_rm_user['token_id']);
						mysqli_stmt_execute($_rm_del);
						mysqli_stmt_close($_rm_del);
					}

					// Buat token baru
					$_rm_new_token = bin2hex(random_bytes(32));
					$_rm_new_hash  = hash('sha256', $_rm_new_token);
					$_rm_expires   = date('Y-m-d H:i:s', time() + 30 * 24 * 3600); // 30 hari
					$_rm_ins = mysqli_prepare($conn, "INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
					if ($_rm_ins) {
						mysqli_stmt_bind_param($_rm_ins, "iss", $_rm_user['id'], $_rm_new_hash, $_rm_expires);
						mysqli_stmt_execute($_rm_ins);
						mysqli_stmt_close($_rm_ins);
					}

					// Set cookie baru
					$_rm_cookie_path = '/';
					setcookie('remember_me', $_rm_new_token, [
						'expires'  => time() + 30 * 24 * 3600,
						'path'     => $_rm_cookie_path,
						'httponly' => true,
						'samesite' => 'Lax',
						'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
					]);
				} else {
					// Token tidak valid / expired → hapus cookie
					setcookie('remember_me', '', [
						'expires'  => time() - 3600,
						'path'     => '/',
						'httponly' => true,
						'samesite' => 'Lax',
					]);
				}
			}

			// Bersihkan token yang sudah expired (house-keeping, 1% chance)
			if (rand(1, 100) === 1) {
				@mysqli_query($conn, "DELETE FROM remember_tokens WHERE expires_at < NOW()");
			}
		}

		// Restore $conn jika sebelumnya null
		if ($_rm_conn_backup === null && isset($conn)) {
			// Biarkan $conn tetap ada untuk penggunaan selanjutnya
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

		// Auto migration: ensure is_approved column exists in certificates
		$chk_appr = @mysqli_query($conn, "SHOW COLUMNS FROM certificates LIKE 'is_approved'");
		if ($chk_appr && mysqli_num_rows($chk_appr) == 0) {
			@mysqli_query($conn, "ALTER TABLE certificates ADD COLUMN is_approved TINYINT(1) NOT NULL DEFAULT 0");
		}
		$chk_appr_at = @mysqli_query($conn, "SHOW COLUMNS FROM certificates LIKE 'approved_at'");
		if ($chk_appr_at && mysqli_num_rows($chk_appr_at) == 0) {
			@mysqli_query($conn, "ALTER TABLE certificates ADD COLUMN approved_at DATETIME DEFAULT NULL");
		}
		$chk_manual = @mysqli_query($conn, "SHOW COLUMNS FROM certificates LIKE 'is_score_manual'");
		if ($chk_manual && mysqli_num_rows($chk_manual) == 0) {
			@mysqli_query($conn, "ALTER TABLE certificates ADD COLUMN is_score_manual TINYINT(1) NOT NULL DEFAULT 0");
		}

		$ins = mysqli_prepare($conn, "INSERT INTO certificates 
			(certificate_id, user_id, intern_name, intern_position, university, major, start_date, end_date, issue_date, score_technical, score_discipline, score_attitude, final_grade, supervisor_name, status, is_approved) 
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 0, '-', 'M. Lutfi Nur Fauzi, S.Kom.', 'active', 0)");
		if ($ins) {
			mysqli_stmt_bind_param($ins, "sisssssss", $cert_code, $user_id, $username, $pos, $univ, $maj, $today, $end_date, $end_date);
			mysqli_stmt_execute($ins);
			mysqli_stmt_close($ins);

			// Hitung skor otomatis dari data presensi
			require_once __DIR__ . '/score-calculator.php';
			update_certificate_scores($conn, $user_id);
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