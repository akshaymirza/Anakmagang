<?php
// Paksa zona waktu PHP ke Asia/Jakarta (WIB / UTC+7)
date_default_timezone_set('Asia/Jakarta');

// ============================================================
// InternSpace - Attendance API (Clock In saja + Foto Geotag)
// Pola sama seperti projects.php: ?action=... , JSON in/out, mysqli
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// --------------------------------------------------------------
// 1. AUTH CHECK (tanpa redirect, karena ini endpoint JSON)
// --------------------------------------------------------------
if (empty($_SESSION['user_logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Belum login']);
    exit;
}
$username = $_SESSION['username'] ?? $_SESSION['user_id'] ?? null;
if (!$username) {
    http_response_code(401);
    echo json_encode(['error' => 'Sesi tidak valid']);
    exit;
}

// --------------------------------------------------------------
// 2. KONEKSI DATABASE
// --------------------------------------------------------------
require_once __DIR__ . '/Login/koneksi.php';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$today = date('Y-m-d');

// Helper: Pastikan kolom ada di tabel attendance secara aman untuk semua versi MySQL
if (!function_exists('ensure_attendance_column')) {
    function ensure_attendance_column($conn, $col, $def)
    {
        if (!$conn)
            return;
        $check = @mysqli_query($conn, "SHOW COLUMNS FROM `attendance` LIKE '{$col}'");
        if ($check && mysqli_num_rows($check) === 0) {
            @mysqli_query($conn, "ALTER TABLE `attendance` ADD COLUMN `{$col}` {$def}");
        }
    }
}
ensure_attendance_column($conn, 'reason', 'TEXT DEFAULT NULL');
ensure_attendance_column($conn, 'clock_out', 'VARCHAR(5) DEFAULT NULL');
ensure_attendance_column($conn, 'location_in', 'TEXT DEFAULT NULL');
ensure_attendance_column($conn, 'photo_in', 'LONGTEXT DEFAULT NULL');
ensure_attendance_column($conn, 'lat_in', 'DECIMAL(11,8) DEFAULT NULL');
ensure_attendance_column($conn, 'lng_in', 'DECIMAL(11,8) DEFAULT NULL');
ensure_attendance_column($conn, 'location_out', 'TEXT DEFAULT NULL');
ensure_attendance_column($conn, 'photo_out', 'LONGTEXT DEFAULT NULL');
ensure_attendance_column($conn, 'lat_out', 'DECIMAL(11,8) DEFAULT NULL');
ensure_attendance_column($conn, 'lng_out', 'DECIMAL(11,8) DEFAULT NULL');
ensure_attendance_column($conn, 'leave_type', 'VARCHAR(50) DEFAULT NULL');
ensure_attendance_column($conn, 'override_by', 'VARCHAR(50) DEFAULT NULL');

// Auto-migrate kolom photo_in & photo_out dari VARCHAR(255) ke LONGTEXT jika masih kecil
// Railway ephemeral filesystem: simpan base64 langsung di DB agar tidak hilang saat container restart
if ($conn) {
    $cols = @mysqli_query($conn, "SHOW COLUMNS FROM `attendance` WHERE Field IN ('photo_in','photo_out')");
    if ($cols) {
        while ($col = mysqli_fetch_assoc($cols)) {
            $colType = strtolower($col['Type'] ?? '');
            if (strpos($colType, 'longtext') === false) {
                @mysqli_query($conn, "ALTER TABLE `attendance` MODIFY COLUMN `{$col['Field']}` LONGTEXT DEFAULT NULL");
            }
        }
    }
}

// Auto-migrate kolom intern_start_date & intern_end_date di tabel users jika belum ada
if ($conn) {
    $chk_isd = @mysqli_query($conn, "SHOW COLUMNS FROM `users` LIKE 'intern_start_date'");
    if ($chk_isd && mysqli_num_rows($chk_isd) === 0) {
        @mysqli_query($conn, "ALTER TABLE `users` ADD COLUMN `intern_start_date` DATE DEFAULT NULL COMMENT 'Tanggal mulai wajib absen intern'");
    }
    $chk_ied = @mysqli_query($conn, "SHOW COLUMNS FROM `users` LIKE 'intern_end_date'");
    if ($chk_ied && mysqli_num_rows($chk_ied) === 0) {
        @mysqli_query($conn, "ALTER TABLE `users` ADD COLUMN `intern_end_date` DATE DEFAULT NULL COMMENT 'Tanggal selesai magang intern'");
    }
}

// Helper: Hitung jarak GPS (Haversine formula dalam meter)
function calculate_haversine_distance($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 6371000; // Radius bumi dalam meter
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return round($earthRadius * $c, 1);
}

// Helper: Ambil konfigurasi titik zona absensi
function get_attendance_settings($conn)
{
    $defaults = [
        'office_name'         => 'Kantor Kedayweb Banyuwangi',
        'address'             => 'Jl. Tamansari, Tukangkayu, Banyuwangi, Jawa Timur',
        'latitude'            => -8.21932100,
        'longitude'           => 114.36945800,
        'radius_meters'       => 100,
        'is_strict'           => 1,
        'check_in_time'       => '08:00',
        'late_threshold_time' => '09:00',
        'cutoff_time'         => '14:00',
        'check_out_time'      => '16:00',
        'attendance_days'     => 'Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'
    ];
    if (!$conn)
        return $defaults;

    // Auto-migrate kolom baru jika belum ada
    $cols = [
        'check_in_time'       => "VARCHAR(10) NOT NULL DEFAULT '08:00'",
        'late_threshold_time' => "VARCHAR(10) NOT NULL DEFAULT '09:00'",
        'cutoff_time'         => "VARCHAR(10) NOT NULL DEFAULT '14:00'",
        'check_out_time'      => "VARCHAR(10) NOT NULL DEFAULT '16:00'",
        'attendance_days'     => "VARCHAR(100) NOT NULL DEFAULT 'Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'"
    ];
    foreach ($cols as $col_name => $col_def) {
        $chk = @mysqli_query($conn, "SHOW COLUMNS FROM `attendance_settings` LIKE '$col_name'");
        if ($chk && mysqli_num_rows($chk) === 0) {
            @mysqli_query($conn, "ALTER TABLE `attendance_settings` ADD COLUMN `$col_name` $col_def");
        }
    }

    $res = @mysqli_query($conn, "SELECT * FROM attendance_settings WHERE id = 1 LIMIT 1");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        return [
            'office_name'         => $row['office_name']         ?? $defaults['office_name'],
            'address'             => $row['address']             ?? $defaults['address'],
            'latitude'            => (float) ($row['latitude']        ?? $defaults['latitude']),
            'longitude'           => (float) ($row['longitude']       ?? $defaults['longitude']),
            'radius_meters'       => (int)   ($row['radius_meters']   ?? $defaults['radius_meters']),
            'is_strict'           => (int)   ($row['is_strict']       ?? $defaults['is_strict']),
            'check_in_time'       => $row['check_in_time']         ?? $defaults['check_in_time'],
            'late_threshold_time' => $row['late_threshold_time']   ?? $defaults['late_threshold_time'],
            'cutoff_time'         => $row['cutoff_time']           ?? $defaults['cutoff_time'],
            'check_out_time'      => $row['check_out_time']        ?? $defaults['check_out_time'],
            'attendance_days'     => $row['attendance_days']       ?? $defaults['attendance_days']
        ];
    }
    return $defaults;
}

// --------------------------------------------------------------
// GET KONFIGURASI TITIK ZONA ABSENSI (?action=get_location_config, GET)
// --------------------------------------------------------------
if ($action === 'get_location_config' && $method === 'GET') {
    $settings = get_attendance_settings($conn);
    echo json_encode([
        'success' => true,
        'config' => $settings
    ]);
    exit;
}

// --------------------------------------------------------------
// GET RINGKASAN KEHADIRAN SEMUA INTERN PER TANGGAL (UNTUK KALENDER ADMIN)
// (?action=all_summary, GET)
// --------------------------------------------------------------
if ($action === 'all_summary' && $method === 'GET') {
    // 1. Ambil semua akun intern dari database (termasuk intern_start_date & intern_end_date)
    $interns = [];
    $resUser = mysqli_query($conn, "SELECT id, username, intern_start_date, intern_end_date FROM users WHERE role = 'intern' ORDER BY id ASC");
    if ($resUser) {
        while ($u = mysqli_fetch_assoc($resUser)) {
            $interns[] = [
                'id'             => $u['id'],
                'name'           => $u['username'],
                'division'       => 'Intern Kedayweb',
                'intern_start_date' => $u['intern_start_date'] ?? null,
                'intern_end_date'   => $u['intern_end_date'] ?? null
            ];
        }
    }

    // 2. Ambil semua data presensi di database
    $attendanceMap = [];
    $resAtt = mysqli_query($conn, "SELECT username, date, status, clock_in, clock_out, reason, location_in, photo_in, lat_in, lng_in, location_out, photo_out, lat_out, lng_out, leave_type, override_by FROM attendance");
    if ($resAtt) {
        while ($row = mysqli_fetch_assoc($resAtt)) {
            $uName = $row['username'];
            $dStr = $row['date'];
            if (!isset($attendanceMap[$uName])) {
                $attendanceMap[$uName] = [];
            }
            $attendanceMap[$uName][$dStr] = [
                'date'        => $dStr,
                'status'      => $row['status'],
                'clockIn'     => $row['clock_in'],
                'clockOut'    => $row['clock_out'],
                'reason'      => $row['reason'],
                'location'    => $row['location_in'],
                'photo'       => $row['photo_in'],
                'lat'         => isset($row['lat_in']) ? (float) $row['lat_in'] : null,
                'lng'         => isset($row['lng_in']) ? (float) $row['lng_in'] : null,
                'locationOut' => $row['location_out'] ?? null,
                'photoOut'    => $row['photo_out'] ?? null,
                'latOut'      => isset($row['lat_out']) ? (float) $row['lat_out'] : null,
                'lngOut'      => isset($row['lng_out']) ? (float) $row['lng_out'] : null,
                'leaveType'   => $row['leave_type'] ?? null,
                'overrideBy'  => $row['override_by'] ?? null,
            ];
        }
    }

    echo json_encode([
        'interns'       => $interns,
        'attendanceMap' => $attendanceMap
    ]);
    exit;
}

// --------------------------------------------------------------
// UPDATE PERIODE MAGANG INTERN (?action=update_start_date / ?action=update_period, POST)
// Hanya admin/superadmin yang boleh mengakses
// --------------------------------------------------------------
if (($action === 'update_start_date' || $action === 'update_period') && $method === 'POST') {
    // Hanya admin atau superadmin yang boleh
    $allowedRoles = ['admin', 'superadmin'];
    $currentRole  = $_SESSION['role'] ?? '';
    if (!in_array($currentRole, $allowedRoles, true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Akses ditolak']);
        exit;
    }

    $input      = json_decode(file_get_contents('php://input'), true);
    $user_id    = intval($input['user_id'] ?? 0);
    $start_date = trim($input['start_date'] ?? '');
    $end_date   = trim($input['end_date'] ?? '');

    if ($user_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'user_id tidak valid']);
        exit;
    }

    // Validasi format tanggal (YYYY-MM-DD) atau kosong (hapus tanggal)
    if (!empty($start_date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
        http_response_code(400);
        echo json_encode(['error' => 'Format tanggal mulai tidak valid (harus YYYY-MM-DD)']);
        exit;
    }
    if (!empty($end_date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
        http_response_code(400);
        echo json_encode(['error' => 'Format tanggal selesai tidak valid (harus YYYY-MM-DD)']);
        exit;
    }
    if (!empty($start_date) && !empty($end_date) && $start_date > $end_date) {
        http_response_code(400);
        echo json_encode(['error' => 'Tanggal mulai magang tidak boleh lebih akhir daripada tanggal selesai magang']);
        exit;
    }

    $setStart = !empty($start_date) ? $start_date : null;
    $setEnd   = !empty($end_date) ? $end_date : null;
    $stmt = mysqli_prepare($conn, "UPDATE users SET intern_start_date = ?, intern_end_date = ? WHERE id = ? AND role = 'intern'");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menyiapkan query: ' . mysqli_error($conn)]);
        exit;
    }
    mysqli_stmt_bind_param($stmt, "ssi", $setStart, $setEnd, $user_id);
    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menyimpan: ' . mysqli_error($conn)]);
        exit;
    }
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    // Sinkronkan juga rentang tanggal ke certificates jika ada
    if ($setStart || $setEnd) {
        $cUp = [];
        if ($setStart) $cUp[] = "start_date = '$setStart'";
        if ($setEnd) $cUp[] = "end_date = '$setEnd', issue_date = '$setEnd'";
        if (!empty($cUp)) {
            $cSql = "UPDATE certificates SET " . implode(', ', $cUp) . " WHERE user_id = $user_id";
            @mysqli_query($conn, $cSql);
        }
    }

    echo json_encode([
        'success'    => true,
        'message'    => 'Periode magang berhasil disimpan',
        'user_id'    => $user_id,
        'start_date' => $setStart,
        'end_date'   => $setEnd
    ]);
    exit;
}

// --------------------------------------------------------------
// 3. GET STATUS ABSEN HARI INI (?action=today)
// --------------------------------------------------------------
if ($action === 'today' && $method === 'GET') {
    // Ambil data periode magang user ini
    $uStart = null;
    $uEnd = null;
    $uStmt = mysqli_prepare($conn, "SELECT intern_start_date, intern_end_date FROM users WHERE username = ? LIMIT 1");
    if ($uStmt) {
        mysqli_stmt_bind_param($uStmt, "s", $username);
        mysqli_stmt_execute($uStmt);
        $uRes = mysqli_stmt_get_result($uStmt);
        if ($uRow = mysqli_fetch_assoc($uRes)) {
            $uStart = $uRow['intern_start_date'];
            $uEnd   = $uRow['intern_end_date'];
        }
        mysqli_stmt_close($uStmt);
    }
    $isEnded = ($uEnd && $today > $uEnd);
    $isNotStarted = ($uStart && $today < $uStart);

    $sql = "SELECT * FROM attendance WHERE username = ? AND date = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $today);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        echo json_encode([
            'exists' => false,
            'intern_start_date' => $uStart,
            'intern_end_date'   => $uEnd,
            'is_ended'          => $isEnded,
            'is_not_started'    => $isNotStarted
        ]);
        exit;
    }

    echo json_encode([
        'exists' => true,
        'status' => $row['status'],
        'clock_in' => $row['clock_in'],
        'photo_in' => $row['photo_in'] ? $row['photo_in'] : null,
        'location_in' => $row['location_in'],
        'lat_in' => isset($row['lat_in']) ? (float) $row['lat_in'] : null,
        'lng_in' => isset($row['lng_in']) ? (float) $row['lng_in'] : null,
        'clock_out' => $row['clock_out'],
        'photo_out' => $row['photo_out'] ? $row['photo_out'] : null,
        'location_out' => $row['location_out'],
        'lat_out' => isset($row['lat_out']) ? (float) $row['lat_out'] : null,
        'lng_out' => isset($row['lng_out']) ? (float) $row['lng_out'] : null,
        'intern_start_date' => $uStart,
        'intern_end_date'   => $uEnd,
        'is_ended'          => $isEnded,
        'is_not_started'    => $isNotStarted
    ]);
    exit;
}

// --------------------------------------------------------------
// 4. SIMPAN CLOCK IN (?action=save, POST)
// --------------------------------------------------------------
if ($action === 'save' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $time = trim($input['time'] ?? '');     // 'HH:MM'
    $status = $input['status'] ?? 'present';
    $location = trim($input['location'] ?? '');
    $lat = isset($input['lat']) ? (float) $input['lat'] : null;
    $lng = isset($input['lng']) ? (float) $input['lng'] : null;
    $photoData = $input['photo'] ?? '';          // data:image/jpeg;base64,....

    if (empty($time)) {
        http_response_code(400);
        echo json_encode(['error' => 'Data tidak lengkap (time)']);
        exit;
    }

    // Cek periode magang intern (tidak boleh absen sebelum mulai atau setelah selesai)
    $uChk = @mysqli_query($conn, "SELECT intern_start_date, intern_end_date FROM users WHERE username = '" . mysqli_real_escape_string($conn, $username) . "' LIMIT 1");
    if ($uChk && $uRow = mysqli_fetch_assoc($uChk)) {
        if (!empty($uRow['intern_end_date']) && $today > $uRow['intern_end_date']) {
            http_response_code(400);
            echo json_encode(['error' => 'Masa magang Anda telah selesai pada ' . date('d M Y', strtotime($uRow['intern_end_date'])) . '. Anda tidak perlu melakukan presensi lagi.']);
            exit;
        }
        if (!empty($uRow['intern_start_date']) && $today < $uRow['intern_start_date']) {
            http_response_code(400);
            echo json_encode(['error' => 'Masa magang Anda baru dimulai pada ' . date('d M Y', strtotime($uRow['intern_start_date'])) . '. Anda belum perlu melakukan presensi.']);
            exit;
        }
    }

    // Ambil pengaturan absensi & geofence dari database
    $zoneSettings = get_attendance_settings($conn);

    // -------- Aturan: lewat batas cutoff_time dianggap tidak masuk --------
    $cutoffTime = !empty($zoneSettings['cutoff_time']) ? $zoneSettings['cutoff_time'] : '14:00';
    $nowTime = date('H:i'); // Jam server saat ini (format HH:MM)

    if (strcmp($nowTime, $cutoffTime) >= 0) {
        // Sudah lewat batas akhir — catat sebagai absent otomatis (tanpa foto)
        $status = 'absent';
        $sqlChk = "SELECT id FROM attendance WHERE username = ? AND date = ? LIMIT 1";
        $stmChk = mysqli_prepare($conn, $sqlChk);
        mysqli_stmt_bind_param($stmChk, "ss", $username, $today);
        mysqli_stmt_execute($stmChk);
        $existRec = mysqli_fetch_assoc(mysqli_stmt_get_result($stmChk));

        if (!$existRec) {
            $sqlAbs = "INSERT INTO attendance (username, date, status, reason) VALUES (?, ?, 'absent', ?)";
            $reason = "Tidak melakukan absensi sebelum batas akhir (pukul {$cutoffTime} WIB)";
            $stmAbs = mysqli_prepare($conn, $sqlAbs);
            mysqli_stmt_bind_param($stmAbs, "sss", $username, $today, $reason);
            mysqli_stmt_execute($stmAbs);
        }

        http_response_code(403);
        echo json_encode([
            'error'       => "Sudah melewati batas akhir absen ({$cutoffTime} WIB). Anda tidak dapat melakukan Clock In dan otomatis tercatat Tidak Masuk hari ini.",
            'status'      => 'absent',
            'cutoff_time' => $cutoffTime
        ]);
        exit;
    }
    if (empty($photoData)) {
        http_response_code(400);
        echo json_encode(['error' => 'Foto wajib disertakan']);
        exit;
    }

    // -------- Geofence Check (Validasi Titik Zona Koordinat Kantor) --------
    $internDist = null;

    // Evaluasi status terlambat secara otomatis berdasarkan batas waktu yang diatur Super Admin
    if ($status !== 'absent' && $status !== 'permit') {
        $lateThreshold = !empty($zoneSettings['late_threshold_time']) ? $zoneSettings['late_threshold_time'] : '09:00';
        if (!empty($time) && strcmp($time, $lateThreshold) > 0) {
            $status = 'late';
        } else {
            $status = 'present';
        }
    }

    if ($lat !== null && $lng !== null) {
        $internDist = calculate_haversine_distance($lat, $lng, $zoneSettings['latitude'], $zoneSettings['longitude']);
    }

    if ($zoneSettings['is_strict'] == 1) {
        if ($lat === null || $lng === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Izin lokasi GPS wajib diaktifkan untuk melakukan absensi kantor!']);
            exit;
        }

        if ($internDist !== null && $internDist > $zoneSettings['radius_meters']) {
            http_response_code(403);
            echo json_encode([
                'error' => "Anda berada di luar zona absensi kantor! Jarak Anda: {$internDist} m (Batas toleransi: {$zoneSettings['radius_meters']} m). Silakan mendekat ke lokasi kantor.",
                'distance' => $internDist,
                'radius' => $zoneSettings['radius_meters']
            ]);
            exit;
        }
    }

    // -------- Validasi format foto --------
    if (!preg_match('/^data:image\/(\w+);base64,/', $photoData)) {
        http_response_code(400);
        echo json_encode(['error' => 'Format foto tidak valid']);
        exit;
    }
    // Simpan base64 langsung ke database (tidak ke filesystem)
    // Solusi Railway ephemeral storage: file di filesystem hilang saat container restart
    $photoBase64 = $photoData; // simpan string data:image/... utuh

    // -------- Cek record hari ini sudah ada atau belum --------
    $sqlCheck = "SELECT id FROM attendance WHERE username = ? AND date = ? LIMIT 1";
    $stmtCheck = mysqli_prepare($conn, $sqlCheck);
    mysqli_stmt_bind_param($stmtCheck, "ss", $username, $today);
    mysqli_stmt_execute($stmtCheck);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCheck));

    if ($existing) {
        $sql = "UPDATE attendance SET status=?, clock_in=?, photo_in=?, location_in=?, lat_in=?, lng_in=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssddi", $status, $time, $photoBase64, $location, $lat, $lng, $existing['id']);
    } else {
        $sql = "INSERT INTO attendance (username, date, status, clock_in, photo_in, location_in, lat_in, lng_in) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssdd", $username, $today, $status, $time, $photoBase64, $location, $lat, $lng);
    }

    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menyimpan ke database: ' . mysqli_error($conn)]);
        exit;
    }

    echo json_encode([
        'message' => 'Clock In berhasil disimpan',
        'photo'   => $photoBase64,
        'time'    => $time,
        'location'=> $location,
        'lat'     => $lat,
        'lng'     => $lng,
        'distance'=> $internDist,
        'in_radius' => ($internDist === null || $internDist <= $zoneSettings['radius_meters']),
    ]);
    exit;
}

// --------------------------------------------------------------
// 4b. SIMPAN CLOCK OUT (?action=save_out, POST)
// --------------------------------------------------------------
if ($action === 'save_out' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $time = trim($input['time'] ?? '');     // 'HH:MM'
    $location = trim($input['location'] ?? '');
    $lat = isset($input['lat']) ? (float) $input['lat'] : null;
    $lng = isset($input['lng']) ? (float) $input['lng'] : null;
    $photoData = $input['photo'] ?? '';          // data:image/jpeg;base64,....

    if (empty($time)) {
        http_response_code(400);
        echo json_encode(['error' => 'Data tidak lengkap (time)']);
        exit;
    }

    // Cek periode magang intern (tidak boleh absen sebelum mulai atau setelah selesai)
    $uChk = @mysqli_query($conn, "SELECT intern_start_date, intern_end_date FROM users WHERE username = '" . mysqli_real_escape_string($conn, $username) . "' LIMIT 1");
    if ($uChk && $uRow = mysqli_fetch_assoc($uChk)) {
        if (!empty($uRow['intern_end_date']) && $today > $uRow['intern_end_date']) {
            http_response_code(400);
            echo json_encode(['error' => 'Masa magang Anda telah selesai pada ' . date('d M Y', strtotime($uRow['intern_end_date'])) . '. Anda tidak perlu melakukan presensi lagi.']);
            exit;
        }
        if (!empty($uRow['intern_start_date']) && $today < $uRow['intern_start_date']) {
            http_response_code(400);
            echo json_encode(['error' => 'Masa magang Anda baru dimulai pada ' . date('d M Y', strtotime($uRow['intern_start_date'])) . '. Anda belum perlu melakukan presensi.']);
            exit;
        }
    }
    if (empty($photoData)) {
        http_response_code(400);
        echo json_encode(['error' => 'Foto Clock Out wajib disertakan']);
        exit;
    }

    // Geofence check
    $zoneSettings = get_attendance_settings($conn);
    $internDist = null;

    if ($lat !== null && $lng !== null) {
        $internDist = calculate_haversine_distance($lat, $lng, $zoneSettings['latitude'], $zoneSettings['longitude']);
    }

    if ($zoneSettings['is_strict'] == 1) {
        if ($lat === null || $lng === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Izin lokasi GPS wajib diaktifkan untuk Clock Out!']);
            exit;
        }

        if ($internDist !== null && $internDist > $zoneSettings['radius_meters']) {
            http_response_code(403);
            echo json_encode([
                'error' => "Anda berada di luar zona absensi kantor! Jarak Anda: {$internDist} m (Batas toleransi: {$zoneSettings['radius_meters']} m). Silakan mendekat ke lokasi kantor.",
                'distance' => $internDist,
                'radius' => $zoneSettings['radius_meters']
            ]);
            exit;
        }
    }

    // Validasi format foto
    if (!preg_match('/^data:image\/(\w+);base64,/', $photoData)) {
        http_response_code(400);
        echo json_encode(['error' => 'Format foto tidak valid']);
        exit;
    }
    // Simpan base64 langsung ke database (tidak ke filesystem)
    $photoBase64 = $photoData;

    // Cek record hari ini sudah ada atau belum
    $sqlCheck = "SELECT id, clock_in, clock_out FROM attendance WHERE username = ? AND date = ? LIMIT 1";
    $stmtCheck = mysqli_prepare($conn, $sqlCheck);
    mysqli_stmt_bind_param($stmtCheck, "ss", $username, $today);
    mysqli_stmt_execute($stmtCheck);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCheck));

    // Wajib sudah Clock In terlebih dahulu
    if (!$existing || empty($existing['clock_in'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Anda harus melakukan Clock In terlebih dahulu hari ini sebelum dapat Clock Out!']);
        exit;
    }

    // Jika sudah pernah Clock Out hari ini
    if (!empty($existing['clock_out'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Anda sudah melakukan Clock Out hari ini (' . $existing['clock_out'] . ').']);
        exit;
    }

    $sql = "UPDATE attendance SET clock_out=?, photo_out=?, location_out=?, lat_out=?, lng_out=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssddi", $time, $photoBase64, $location, $lat, $lng, $existing['id']);

    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menyimpan ke database: ' . mysqli_error($conn)]);
        exit;
    }

    echo json_encode([
        'message'   => 'Clock Out berhasil disimpan',
        'photo'     => $photoBase64,
        'time'      => $time,
        'location'  => $location,
        'lat'       => $lat,
        'lng'       => $lng,
        'distance'  => $internDist,
        'in_radius' => ($internDist === null || $internDist <= $zoneSettings['radius_meters']),
    ]);
    exit;
}

// --------------------------------------------------------------
// 5. RIWAYAT KEHADIRAN UNTUK KALENDER (?action=history, GET)
//    Dipakai oleh attendance.php (kalender + tabel riwayat milik intern sendiri)
// --------------------------------------------------------------
if ($action === 'history' && $method === 'GET') {
    $sql = "SELECT date, status, clock_in, clock_out, reason, location_in, photo_in, lat_in, lng_in, location_out, photo_out, lat_out, lng_out, leave_type, override_by
            FROM attendance WHERE username = ? ORDER BY date DESC";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menyiapkan query: ' . mysqli_error($conn)]);
        exit;
    }
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $rows = [];
    while ($r = mysqli_fetch_assoc($result)) {
        $rows[] = [
            'date' => $r['date'],
            'status' => $r['status'],
            'clock_in' => $r['clock_in'],
            'clock_out' => $r['clock_out'],
            'reason' => $r['reason'],
            'location_in' => $r['location_in'],
            'photo_in' => $r['photo_in'],
            'lat_in' => $r['lat_in'] !== null ? (float) $r['lat_in'] : null,
            'lng_in' => $r['lng_in'] !== null ? (float) $r['lng_in'] : null,
            'location_out' => $r['location_out'] ?? null,
            'photo_out' => $r['photo_out'] ?? null,
            'lat_out' => $r['lat_out'] !== null ? (float) $r['lat_out'] : null,
            'lng_out' => $r['lng_out'] !== null ? (float) $r['lng_out'] : null,
        ];
    }
    echo json_encode($rows);
    exit;
}

// --------------------------------------------------------------
// 6. CATAT / EDIT KEHADIRAN MANUAL DARI KALENDER (?action=upsert, POST)
// --------------------------------------------------------------
if ($action === 'upsert' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $date = trim($input['date'] ?? '');
    $status = $input['status'] ?? '';
    $clockIn = trim($input['clockIn'] ?? '');
    $clockOut = trim($input['clockOut'] ?? '');
    $reason = trim($input['reason'] ?? '');

    if (!$date || !in_array($status, ['present', 'late', 'absent'], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Data tidak lengkap (date/status)']);
        exit;
    }
    if ($status === 'absent') {
        $clockIn = '';
        $clockOut = '';
    }

    $sqlCheck = "SELECT id FROM attendance WHERE username = ? AND date = ? LIMIT 1";
    $stmtCheck = mysqli_prepare($conn, $sqlCheck);
    mysqli_stmt_bind_param($stmtCheck, "ss", $username, $date);
    mysqli_stmt_execute($stmtCheck);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCheck));

    if ($existing) {
        $sql = "UPDATE attendance SET status=?, clock_in=?, clock_out=?, reason=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $status, $clockIn, $clockOut, $reason, $existing['id']);
    } else {
        $sql = "INSERT INTO attendance (username, date, status, clock_in, clock_out, reason) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssss", $username, $date, $status, $clockIn, $clockOut, $reason);
    }

    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menyimpan ke database: ' . mysqli_error($conn)]);
        exit;
    }

    echo json_encode(['message' => 'Kehadiran berhasil disimpan']);
    exit;
}

// --------------------------------------------------------------
// 7. HAPUS CATATAN KEHADIRAN (?action=delete, POST)
// --------------------------------------------------------------
if ($action === 'delete' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $date = trim($input['date'] ?? '');

    if (!$date) {
        http_response_code(400);
        echo json_encode(['error' => 'Tanggal wajib diisi']);
        exit;
    }

    $sql = "DELETE FROM attendance WHERE username = ? AND date = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $date);

    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menghapus data: ' . mysqli_error($conn)]);
        exit;
    }

    echo json_encode(['message' => 'Catatan berhasil dihapus']);
    exit;
}

// --------------------------------------------------------------

// --------------------------------------------------------------
// SET KEHADIRAN / OVERRIDE STATUS INTERN OLEH ADMIN
// (?action=admin_set_attendance, POST)
// --------------------------------------------------------------
if ($action === 'admin_set_attendance' && $method === 'POST') {
    $userRole = $_SESSION['role'] ?? 'intern';
    if (!in_array($userRole, ['admin', 'superadmin'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Akses ditolak: Hanya Admin/Superadmin yang dapat mengatur status kehadiran']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $targetUsername = trim($input['username'] ?? $input['target_username'] ?? '');
    $date           = trim($input['date'] ?? '');
    $status         = trim($input['status'] ?? 'present');
    $leaveType      = trim($input['leave_type'] ?? '');
    $clockIn        = trim($input['clock_in'] ?? $input['clockIn'] ?? '');
    $clockOut       = trim($input['clock_out'] ?? $input['clockOut'] ?? '');
    $reason         = trim($input['reason'] ?? '');

    if (empty($targetUsername) || empty($date)) {
        http_response_code(400);
        echo json_encode(['error' => 'Nama intern dan tanggal kehadiran wajib diisi']);
        exit;
    }

    $allowedStatuses = ['present', 'late', 'permit', 'absent'];
    if (!in_array($status, $allowedStatuses, true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Status tidak valid (pilih: present, late, permit, absent)']);
        exit;
    }

    // Default jam jika hadir/telat
    if ($status === 'present' && empty($clockIn)) {
        $clockIn = '08:00';
    } elseif ($status === 'late' && empty($clockIn)) {
        $clockIn = '09:15';
    } elseif ($status === 'permit' || $status === 'absent') {
        if (empty($clockIn)) $clockIn = null;
        if (empty($clockOut)) $clockOut = null;
    }

    if ($status === 'permit' && empty($leaveType)) {
        $leaveType = 'dispen';
    }

    $adminActor = $_SESSION['username'] ?? 'admin';

    $sqlChk = "SELECT id FROM attendance WHERE username = ? AND date = ? LIMIT 1";
    $stmtChk = mysqli_prepare($conn, $sqlChk);
    mysqli_stmt_bind_param($stmtChk, "ss", $targetUsername, $date);
    mysqli_stmt_execute($stmtChk);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtChk));

    if ($existing) {
        $sqlUpd = "UPDATE attendance SET status = ?, leave_type = ?, clock_in = ?, clock_out = ?, reason = ?, override_by = ? WHERE id = ?";
        $stmtUpd = mysqli_prepare($conn, $sqlUpd);
        mysqli_stmt_bind_param($stmtUpd, "ssssssi", $status, $leaveType, $clockIn, $clockOut, $reason, $adminActor, $existing['id']);
        $ok = mysqli_stmt_execute($stmtUpd);
    } else {
        $sqlIns = "INSERT INTO attendance (username, date, status, leave_type, clock_in, clock_out, reason, override_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtIns = mysqli_prepare($conn, $sqlIns);
        mysqli_stmt_bind_param($stmtIns, "ssssssss", $targetUsername, $date, $status, $leaveType, $clockIn, $clockOut, $reason, $adminActor);
        $ok = mysqli_stmt_execute($stmtIns);
    }

    if (!$ok) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menyimpan status kehadiran ke database: ' . mysqli_error($conn)]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => "Status kehadiran {$targetUsername} pada {$date} berhasil diatur menjadi {$status}"
    ]);
    exit;
}

// --------------------------------------------------------------
// HAPUS CATATAN KEHADIRAN OLEH ADMIN
// (?action=admin_delete_attendance, POST)
// --------------------------------------------------------------
if ($action === 'admin_delete_attendance' && $method === 'POST') {
    $userRole = $_SESSION['role'] ?? 'intern';
    if (!in_array($userRole, ['admin', 'superadmin'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Akses ditolak: Hanya Admin yang dapat menghapus catatan presensi']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $targetUsername = trim($input['username'] ?? '');
    $date = trim($input['date'] ?? '');

    if (empty($targetUsername) || empty($date)) {
        http_response_code(400);
        echo json_encode(['error' => 'Nama intern dan tanggal wajib diisi']);
        exit;
    }

    $sql = "DELETE FROM attendance WHERE username = ? AND date = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $targetUsername, $date);
    mysqli_stmt_execute($stmt);

    echo json_encode(['success' => true, 'message' => "Catatan kehadiran {$targetUsername} pada {$date} berhasil dihapus"]);
    exit;
}

// --------------------------------------------------------------
// KIRIM PENGAJUAN IZIN / DISPENSASI OLEH INTERN
// (?action=submit_leave, POST)
// --------------------------------------------------------------
if ($action === 'submit_leave' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $date = trim($input['date'] ?? '');
    $leaveType = trim($input['leave_type'] ?? 'izin');
    $reason = trim($input['reason'] ?? '');
    $photoBase64 = $input['photo'] ?? null;

    if (empty($date) || empty($reason)) {
        http_response_code(400);
        echo json_encode(['error' => 'Tanggal dan alasan izin wajib diisi']);
        exit;
    }

    // Cek periode magang intern
    $uChk = @mysqli_query($conn, "SELECT intern_start_date, intern_end_date FROM users WHERE username = '" . mysqli_real_escape_string($conn, $username) . "' LIMIT 1");
    if ($uChk && $uRow = mysqli_fetch_assoc($uChk)) {
        if (!empty($uRow['intern_end_date']) && $date > $uRow['intern_end_date']) {
            http_response_code(400);
            echo json_encode(['error' => 'Masa magang Anda telah selesai pada ' . date('d M Y', strtotime($uRow['intern_end_date'])) . '. Anda tidak perlu mengajukan izin lagi.']);
            exit;
        }
        if (!empty($uRow['intern_start_date']) && $date < $uRow['intern_start_date']) {
            http_response_code(400);
            echo json_encode(['error' => 'Masa magang Anda baru dimulai pada ' . date('d M Y', strtotime($uRow['intern_start_date'])) . '.']);
            exit;
        }
    }

    $status = 'permit';

    $sqlCheck = "SELECT id FROM attendance WHERE username = ? AND date = ? LIMIT 1";
    $stmtCheck = mysqli_prepare($conn, $sqlCheck);
    mysqli_stmt_bind_param($stmtCheck, "ss", $username, $date);
    mysqli_stmt_execute($stmtCheck);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCheck));

    if ($existing) {
        $sql = "UPDATE attendance SET status=?, leave_type=?, reason=?, photo_in=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $status, $leaveType, $reason, $photoBase64, $existing['id']);
    } else {
        $sql = "INSERT INTO attendance (username, date, status, leave_type, reason, photo_in) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssss", $username, $date, $status, $leaveType, $reason, $photoBase64);
    }

    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengirim permohonan izin ke database: ' . mysqli_error($conn)]);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Permohonan izin/dispensasi berhasil dicatat']);
    exit;
}

// Fallback
// --------------------------------------------------------------
http_response_code(404);
echo json_encode(['error' => 'Action tidak ditemukan']);