<?php
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
$today  = date('Y-m-d');

// Helper: Pastikan kolom ada di tabel attendance secara aman untuk semua versi MySQL
if (!function_exists('ensure_attendance_column')) {
    function ensure_attendance_column($conn, $col, $def) {
        if (!$conn) return;
        $check = @mysqli_query($conn, "SHOW COLUMNS FROM `attendance` LIKE '{$col}'");
        if ($check && mysqli_num_rows($check) === 0) {
            @mysqli_query($conn, "ALTER TABLE `attendance` ADD COLUMN `{$col}` {$def}");
        }
    }
}
ensure_attendance_column($conn, 'reason', 'TEXT DEFAULT NULL');
ensure_attendance_column($conn, 'clock_out', 'VARCHAR(5) DEFAULT NULL');
ensure_attendance_column($conn, 'location_in', 'TEXT DEFAULT NULL');
ensure_attendance_column($conn, 'photo_in', 'VARCHAR(255) DEFAULT NULL');
ensure_attendance_column($conn, 'lat_in', 'DECIMAL(11,8) DEFAULT NULL');
ensure_attendance_column($conn, 'lng_in', 'DECIMAL(11,8) DEFAULT NULL');

// Helper: Hitung jarak GPS (Haversine formula dalam meter)
function calculate_haversine_distance($lat1, $lon1, $lat2, $lon2) {
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
function get_attendance_settings($conn) {
    $defaults = [
        'office_name'   => 'Kantor Kedayweb Banyuwangi',
        'address'       => 'Jl. Tamansari, Tukangkayu, Banyuwangi, Jawa Timur',
        'latitude'      => -8.21932100,
        'longitude'     => 114.36945800,
        'radius_meters' => 100,
        'is_strict'     => 1
    ];
    if (!$conn) return $defaults;
    $res = @mysqli_query($conn, "SELECT * FROM attendance_settings WHERE id = 1 LIMIT 1");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        return [
            'office_name'   => $row['office_name'] ?? $defaults['office_name'],
            'address'       => $row['address'] ?? $defaults['address'],
            'latitude'      => (float) ($row['latitude'] ?? $defaults['latitude']),
            'longitude'     => (float) ($row['longitude'] ?? $defaults['longitude']),
            'radius_meters' => (int) ($row['radius_meters'] ?? $defaults['radius_meters']),
            'is_strict'     => (int) ($row['is_strict'] ?? $defaults['is_strict'])
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
        'config'  => $settings
    ]);
    exit;
}

// --------------------------------------------------------------
// GET RINGKASAN KEHADIRAN SEMUA INTERN PER TANGGAL (UNTUK KALENDER ADMIN)
// (?action=all_summary, GET)
// --------------------------------------------------------------
if ($action === 'all_summary' && $method === 'GET') {
    // 1. Ambil semua akun intern dari database
    $interns = [];
    $resUser = mysqli_query($conn, "SELECT id, username FROM users WHERE role = 'intern' ORDER BY id ASC");
    if ($resUser) {
        while ($u = mysqli_fetch_assoc($resUser)) {
            $interns[] = [
                'id' => $u['id'],
                'name' => $u['username'],
                'division' => 'Intern Kedayweb'
            ];
        }
    }

    // 2. Ambil semua data presensi di database
    $attendanceMap = [];
    $resAtt = mysqli_query($conn, "SELECT username, date, status, clock_in, clock_out, reason, location_in, photo_in, lat_in, lng_in FROM attendance");
    if ($resAtt) {
        while ($row = mysqli_fetch_assoc($resAtt)) {
            $uName = $row['username'];
            $dStr = $row['date'];
            if (!isset($attendanceMap[$uName])) {
                $attendanceMap[$uName] = [];
            }
            $attendanceMap[$uName][$dStr] = [
                'date' => $dStr,
                'status' => $row['status'],
                'clockIn' => $row['clock_in'],
                'clockOut' => $row['clock_out'],
                'reason' => $row['reason'],
                'location' => $row['location_in'],
                'photo' => $row['photo_in'],
                'lat' => isset($row['lat_in']) ? (float) $row['lat_in'] : null,
                'lng' => isset($row['lng_in']) ? (float) $row['lng_in'] : null,
            ];
        }
    }

    echo json_encode([
        'interns' => $interns,
        'attendanceMap' => $attendanceMap
    ]);
    exit;
}

// --------------------------------------------------------------
// 3. GET STATUS ABSEN HARI INI (?action=today)
// --------------------------------------------------------------
if ($action === 'today' && $method === 'GET') {
    $sql = "SELECT * FROM attendance WHERE username = ? AND date = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $today);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        echo json_encode(['exists' => false]);
        exit;
    }

    echo json_encode([
        'exists'      => true,
        'status'      => $row['status'],
        'clock_in'    => $row['clock_in'],
        'photo_in'    => $row['photo_in'] ? $row['photo_in'] : null,
        'location_in' => $row['location_in'],
        'lat_in'      => isset($row['lat_in']) ? (float) $row['lat_in'] : null,
        'lng_in'      => isset($row['lng_in']) ? (float) $row['lng_in'] : null,
    ]);
    exit;
}

// --------------------------------------------------------------
// 4. SIMPAN CLOCK IN (?action=save, POST)
// --------------------------------------------------------------
if ($action === 'save' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $time     = trim($input['time'] ?? '');     // 'HH:MM'
    $status   = $input['status'] ?? 'present';
    $location = trim($input['location'] ?? '');
    $lat      = isset($input['lat']) ? (float) $input['lat'] : null;
    $lng      = isset($input['lng']) ? (float) $input['lng'] : null;
    $photoData= $input['photo'] ?? '';          // data:image/jpeg;base64,....

    if (empty($time)) {
        http_response_code(400);
        echo json_encode(['error' => 'Data tidak lengkap (time)']);
        exit;
    }

    // -------- Aturan: lewat jam 14:00 dianggap tidak masuk --------
    $nowHour = (int) date('H'); // Jam server saat ini
    $CUTOFF_HOUR = 14;           // 14:00 = batas akhir absen

    if ($nowHour >= $CUTOFF_HOUR) {
        // Sudah lewat batas — catat sebagai absent otomatis (tanpa foto)
        $status = 'absent';
        // Cek apakah sudah ada record hari ini
        $sqlChk = "SELECT id FROM attendance WHERE username = ? AND date = ? LIMIT 1";
        $stmChk = mysqli_prepare($conn, $sqlChk);
        mysqli_stmt_bind_param($stmChk, "ss", $username, $today);
        mysqli_stmt_execute($stmChk);
        $existRec = mysqli_fetch_assoc(mysqli_stmt_get_result($stmChk));

        if (!$existRec) {
            $sqlAbs = "INSERT INTO attendance (username, date, status, reason) VALUES (?, ?, 'absent', 'Tidak melakukan absensi sebelum pukul 14:00')";
            $stmAbs = mysqli_prepare($conn, $sqlAbs);
            mysqli_stmt_bind_param($stmAbs, "ss", $username, $today);
            mysqli_stmt_execute($stmAbs);
        }

        http_response_code(403);
        echo json_encode(['error' => 'Sudah melewati batas absen (14:00). Anda tercatat tidak masuk hari ini.', 'status' => 'absent']);
        exit;
    }
    if (empty($photoData)) {
        http_response_code(400);
        echo json_encode(['error' => 'Foto wajib disertakan']);
        exit;
    }

    // -------- Geofence Check (Validasi Titik Zona Koordinat Kantor) --------
    $zoneSettings = get_attendance_settings($conn);
    $internDist = null;

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

    // -------- Simpan foto base64 ke file --------
    if (!preg_match('/^data:image\/(\w+);base64,/', $photoData, $m)) {
        http_response_code(400);
        echo json_encode(['error' => 'Format foto tidak valid']);
        exit;
    }
    $ext       = $m[1] === 'jpeg' ? 'jpg' : $m[1];
    $base64Raw = substr($photoData, strpos($photoData, ',') + 1);
    $binary    = base64_decode($base64Raw);
    if ($binary === false) {
        http_response_code(400);
        echo json_encode(['error' => 'Gagal decode foto']);
        exit;
    }

    $uploadDir = __DIR__ . '/uploads/attendance/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $safeUsername = preg_replace('/[^A-Za-z0-9_\-]/', '_', $username);
    $fileName     = $safeUsername . '_' . $today . '_in_' . time() . '.' . $ext;
    $filePath     = $uploadDir . $fileName;
    $relativePath = 'uploads/attendance/' . $fileName;

    if (file_put_contents($filePath, $binary) === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menyimpan file foto']);
        exit;
    }

    // -------- Cek record hari ini sudah ada atau belum --------
    $sqlCheck = "SELECT id FROM attendance WHERE username = ? AND date = ? LIMIT 1";
    $stmtCheck = mysqli_prepare($conn, $sqlCheck);
    mysqli_stmt_bind_param($stmtCheck, "ss", $username, $today);
    mysqli_stmt_execute($stmtCheck);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCheck));

    if ($existing) {
        $sql = "UPDATE attendance SET status=?, clock_in=?, photo_in=?, location_in=?, lat_in=?, lng_in=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssddi", $status, $time, $relativePath, $location, $lat, $lng, $existing['id']);
    } else {
        $sql = "INSERT INTO attendance (username, date, status, clock_in, photo_in, location_in, lat_in, lng_in) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssdd", $username, $today, $status, $time, $relativePath, $location, $lat, $lng);
    }

    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menyimpan ke database: ' . mysqli_error($conn)]);
        exit;
    }

    echo json_encode([
        'message'   => 'Clock In berhasil disimpan',
        'photo'     => $relativePath,
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
    $sql = "SELECT date, status, clock_in, clock_out, reason, location_in, lat_in, lng_in
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
            'date'        => $r['date'],
            'status'      => $r['status'],
            'clock_in'    => $r['clock_in'],
            'clock_out'   => $r['clock_out'],
            'reason'      => $r['reason'],
            'location_in' => $r['location_in'],
            'lat_in'      => $r['lat_in'] !== null ? (float) $r['lat_in'] : null,
            'lng_in'      => $r['lng_in'] !== null ? (float) $r['lng_in'] : null,
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

    $date     = trim($input['date'] ?? '');
    $status   = $input['status'] ?? '';
    $clockIn  = trim($input['clockIn'] ?? '');
    $clockOut = trim($input['clockOut'] ?? '');
    $reason   = trim($input['reason'] ?? '');

    if (!$date || !in_array($status, ['present', 'late', 'absent'], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Data tidak lengkap (date/status)']);
        exit;
    }
    if ($status === 'absent') { $clockIn = ''; $clockOut = ''; }

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
    $date  = trim($input['date'] ?? '');

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
// Fallback
// --------------------------------------------------------------
http_response_code(404);
echo json_encode(['error' => 'Action tidak ditemukan']);