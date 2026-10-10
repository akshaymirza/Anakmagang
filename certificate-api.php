<?php
/**
 * certificate-api.php
 * API endpoint untuk verifikasi keaslian sertifikat magang
 * Dipanggil dari verification.php via fetch()
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/Login/koneksi.php';

function check_admin_api(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $is_logged = !empty($_SESSION['user_logged_in']);
    $role = (string)($_SESSION['role'] ?? '');
    if (!$is_logged || !in_array($role, ['admin', 'superadmin'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Akses ditolak. Sesi Admin tidak valid.']);
        exit;
    }
}

// Auto migration: Ensure certificate_enabled column exists in attendance_settings
if ($conn) {
    $chk_col = @mysqli_query($conn, "SHOW COLUMNS FROM attendance_settings LIKE 'certificate_enabled'");
    if ($chk_col && mysqli_num_rows($chk_col) == 0) {
        @mysqli_query($conn, "ALTER TABLE attendance_settings ADD COLUMN certificate_enabled TINYINT(1) NOT NULL DEFAULT 1");
    }

    // Auto migration: Ensure is_approved column exists in certificates table
    $chk_appr = @mysqli_query($conn, "SHOW COLUMNS FROM certificates LIKE 'is_approved'");
    if ($chk_appr && mysqli_num_rows($chk_appr) == 0) {
        @mysqli_query($conn, "ALTER TABLE certificates ADD COLUMN is_approved TINYINT(1) NOT NULL DEFAULT 0");
    }
    $chk_appr_at = @mysqli_query($conn, "SHOW COLUMNS FROM certificates LIKE 'approved_at'");
    if ($chk_appr_at && mysqli_num_rows($chk_appr_at) == 0) {
        @mysqli_query($conn, "ALTER TABLE certificates ADD COLUMN approved_at DATETIME DEFAULT NULL");
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'verify';

// ── GET MASTER STATUS (publik / admin) ───────────────────────────────────────
if ($action === 'get_master_status') {
    $enabled = 1;
    if ($conn) {
        $res = @mysqli_query($conn, "SELECT certificate_enabled FROM attendance_settings WHERE id = 1 LIMIT 1");
        if ($res && $r = mysqli_fetch_assoc($res)) {
            $enabled = intval($r['certificate_enabled'] ?? 1);
        }
    }
    echo json_encode(['success' => true, 'enabled' => (bool)$enabled]);
    exit;
}

// ── TOGGLE MASTER STATUS (admin only) ───────────────────────────────────────
if ($action === 'toggle_master_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_admin_api();

    $enabled = isset($_POST['enabled']) && ($_POST['enabled'] == '1' || $_POST['enabled'] == 'true' || $_POST['enabled'] === true) ? 1 : 0;
    
    if ($conn) {
        $chk = mysqli_query($conn, "SELECT id FROM attendance_settings WHERE id = 1 LIMIT 1");
        if ($chk && mysqli_num_rows($chk) > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE attendance_settings SET certificate_enabled = ? WHERE id = 1");
            mysqli_stmt_bind_param($stmt, "i", $enabled);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO attendance_settings (id, certificate_enabled) VALUES (1, ?)");
            mysqli_stmt_bind_param($stmt, "i", $enabled);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    $msg = $enabled ? 'Tombol Sertifikat di Panel Intern BERHASIL DITAMPILKAN!' : 'Tombol Sertifikat di Panel Intern BERHASIL DISEMBUNYIKAN!';
    echo json_encode(['success' => true, 'message' => $msg, 'enabled' => (bool)$enabled]);
    exit;
}

function check_superadmin_api(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $is_logged = !empty($_SESSION['user_logged_in']);
    $role = (string)($_SESSION['role'] ?? '');
    if (!$is_logged || $role !== 'superadmin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Akses ditolak. Fitur ini hanya dapat diakses oleh Super Admin.']);
        exit;
    }
}

// ── APPROVE / UNAPPROVE CERTIFICATE FOR SIGNATURE (superadmin only) ─────────
if ($action === 'toggle_approval' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_superadmin_api();

    $id = (int)($_POST['id'] ?? 0);
    $approved = isset($_POST['approved']) && ($_POST['approved'] == '1' || $_POST['approved'] === 'true' || $_POST['approved'] === true) ? 1 : 0;

    if ($conn && $id > 0) {
        $approved_at = $approved ? date('Y-m-d H:i:s') : null;
        $stmt = mysqli_prepare($conn, "UPDATE certificates SET is_approved = ?, approved_at = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "isi", $approved, $approved_at, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $msg = $approved 
            ? 'Sertifikat BERHASIL DISETUJUI oleh Super Admin! Tanda tangan dan stempel resmi kini aktif.' 
            : 'Persetujuan sertifikat DIBATALKAN oleh Super Admin. Tanda tangan dinonaktifkan kembali.';
        echo json_encode(['success' => true, 'message' => $msg, 'is_approved' => (bool)$approved, 'approved_at' => $approved_at]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ID sertifikat tidak valid.']);
    }
    exit;
}

// ── TOGGLE SINGLE CERTIFICATE STATUS (admin only) ───────────────────────────
if ($action === 'toggle_single_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_admin_api();

    $id = (int)($_POST['id'] ?? 0);
    $status = trim($_POST['status'] ?? 'active');
    if (!in_array($status, ['active', 'revoked'])) {
        $status = 'active';
    }

    if ($conn && $id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE certificates SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $msg = $status === 'active' ? 'Status sertifikat berhasil DIAKTIFKAN!' : 'Status sertifikat berhasil DICABUT / DINONAKTIFKAN!';
        echo json_encode(['success' => true, 'message' => $msg, 'status' => $status]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ID sertifikat tidak valid.']);
    }
    exit;
}

// ── VERIFY sertifikat (publik) ──────────────────────────────────────────────
if ($action === 'verify') {
    $cert_id = trim($_GET['id'] ?? '');
    if (!$cert_id) {
        echo json_encode(['success' => false, 'message' => 'ID sertifikat tidak boleh kosong.']);
        exit;
    }

    $stmt = $conn->prepare(
        "SELECT c.*, 
                DATE_FORMAT(c.start_date, '%d %M %Y') AS start_fmt,
                DATE_FORMAT(c.end_date,   '%d %M %Y') AS end_fmt,
                DATE_FORMAT(c.issue_date, '%d %M %Y') AS issue_fmt
         FROM certificates c
         WHERE c.certificate_id = ? LIMIT 1"
    );
    $stmt->bind_param('s', $cert_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Sertifikat tidak ditemukan. Pastikan ID sudah benar.']);
        exit;
    }

    $cert = $result->fetch_assoc();

    if ($cert['status'] === 'revoked') {
        echo json_encode([
            'success' => false,
            'revoked' => true,
            'message' => 'Sertifikat ini telah dicabut / tidak berlaku.',
            'certificate_id' => $cert['certificate_id'],
        ]);
        exit;
    }

    // Hitung rata-rata skor
    $avg_score = round(($cert['score_technical'] + $cert['score_discipline'] + $cert['score_attitude']) / 3);

    echo json_encode([
        'success'          => true,
        'certificate_id'   => $cert['certificate_id'],
        'intern_name'      => $cert['intern_name'],
        'intern_position'  => $cert['intern_position'],
        'university'       => $cert['university'],
        'major'            => $cert['major'],
        'start_date'       => $cert['start_fmt'],
        'end_date'         => $cert['end_fmt'],
        'issue_date'       => $cert['issue_fmt'],
        'score_technical'  => (int)$cert['score_technical'],
        'score_discipline' => (int)$cert['score_discipline'],
        'score_attitude'   => (int)$cert['score_attitude'],
        'avg_score'        => $avg_score,
        'final_grade'      => $cert['final_grade'],
        'supervisor_name'  => $cert['supervisor_name'],
        'status'           => $cert['status'],
        'is_approved'      => (int)($cert['is_approved'] ?? 0),
        'approved_at'      => $cert['approved_at'] ?? null,
        'notes'            => $cert['notes'],
    ]);
    exit;
}

// ── LIST semua sertifikat (admin only) ─────────────────────────────────────
if ($action === 'list') {
    check_admin_api();

    $search   = '%' . trim($_GET['search'] ?? '') . '%';
    $year     = trim($_GET['year'] ?? '');
    $sort_by  = trim($_GET['sort_by'] ?? 'year'); // 'year', 'id', 'name', 'created_at'
    $sort_dir = strtolower(trim($_GET['sort_dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';

    // Ekstrak tahun: prioritaskan format dari certificate_id (misal IS-2024-001 -> 2024), fallback ke start_date/issue_date/created_at
    $year_expr = "COALESCE(
        NULLIF(REGEXP_SUBSTR(certificate_id, '[0-9]{4}'), ''),
        YEAR(start_date),
        YEAR(issue_date),
        YEAR(created_at)
    )";

    $sql = "SELECT id, certificate_id, intern_name, intern_position, university,
                   final_grade, status, is_approved, approved_at,
                   DATE_FORMAT(issue_date, '%d %b %Y') AS issue_fmt,
                   CAST($year_expr AS UNSIGNED) AS entry_year
            FROM certificates
            WHERE (certificate_id LIKE ? OR intern_name LIKE ? OR intern_position LIKE ?)";
    
    $params = [$search, $search, $search];
    $types  = 'sss';

    if (!empty($year) && $year !== 'all') {
        $sql .= " AND CAST($year_expr AS UNSIGNED) = ?";
        $params[] = (int)$year;
        $types   .= 'i';
    }

    // Tentukan Order By
    if ($sort_by === 'year') {
        $sql .= " ORDER BY entry_year $sort_dir, id $sort_dir";
    } elseif ($sort_by === 'id') {
        $sql .= " ORDER BY certificate_id $sort_dir";
    } elseif ($sort_by === 'name') {
        $sql .= " ORDER BY intern_name $sort_dir";
    } else {
        $sql .= " ORDER BY created_at $sort_dir";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

// ── CREATE sertifikat (admin only) ─────────────────────────────────────────
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_admin_api();

    $fields = ['certificate_id','intern_name','intern_position','university','major',
               'start_date','end_date','issue_date','score_technical','score_discipline',
               'score_attitude','final_grade','supervisor_name','status','user_id','notes'];
    $data = [];
    foreach ($fields as $f) {
        $data[$f] = $_POST[$f] ?? null;
    }

    // Validasi wajib
    if (!$data['certificate_id'] || !$data['intern_name'] || !$data['intern_position'] ||
        !$data['start_date']     || !$data['end_date']     || !$data['issue_date']) {
        echo json_encode(['success' => false, 'message' => 'Field wajib belum diisi.']);
        exit;
    }

    $stmt = $conn->prepare(
        "INSERT INTO certificates
         (certificate_id, user_id, intern_name, intern_position, university, major,
          start_date, end_date, issue_date, score_technical, score_discipline,
          score_attitude, final_grade, supervisor_name, status, notes)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
    );
    $uid = $data['user_id'] ?: null;
    $stmt->bind_param(
        'sissssssssiiisss',
        $data['certificate_id'], $uid, $data['intern_name'], $data['intern_position'],
        $data['university'], $data['major'], $data['start_date'], $data['end_date'],
        $data['issue_date'], $data['score_technical'], $data['score_discipline'],
        $data['score_attitude'], $data['final_grade'], $data['supervisor_name'],
        $data['status'], $data['notes']
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Sertifikat berhasil ditambahkan.', 'insert_id' => $stmt->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $conn->error]);
    }
    exit;
}

// ── UPDATE sertifikat (admin only) ─────────────────────────────────────────
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_admin_api();

    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid.']);
        exit;
    }

    $intern_name     = trim($_POST['intern_name'] ?? '');
    $intern_position = trim($_POST['intern_position'] ?? '');
    $university      = trim($_POST['university'] ?? '');
    $major           = trim($_POST['major'] ?? '');
    $start_date      = trim($_POST['start_date'] ?? '');
    $end_date        = trim($_POST['end_date'] ?? '');
    $issue_date      = trim($_POST['issue_date'] ?? '');
    $score_technical = intval($_POST['score_technical'] ?? 0);
    $score_discipline= intval($_POST['score_discipline'] ?? 0);
    $score_attitude  = intval($_POST['score_attitude'] ?? 0);
    $final_grade     = trim($_POST['final_grade'] ?? '');
    $supervisor_name = trim($_POST['supervisor_name'] ?? '');
    $status          = trim($_POST['status'] ?? 'active');
    $notes           = trim($_POST['notes'] ?? '');

    $stmt = $conn->prepare(
        "UPDATE certificates SET
            intern_name=?, intern_position=?, university=?, major=?,
            start_date=?, end_date=?, issue_date=?,
            score_technical=?, score_discipline=?, score_attitude=?,
            final_grade=?, supervisor_name=?, status=?, notes=?
         WHERE id=?"
    );
    $stmt->bind_param(
        'sssssssiiissssi',
        $intern_name, $intern_position, $university, $major,
        $start_date, $end_date, $issue_date,
        $score_technical, $score_discipline, $score_attitude,
        $final_grade, $supervisor_name, $status, $notes,
        $id
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Sertifikat berhasil diperbarui.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal update: ' . $conn->error]);
    }
    exit;
}

// ── DELETE sertifikat (admin only) ─────────────────────────────────────────
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_admin_api();

    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM certificates WHERE id=?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Sertifikat berhasil dihapus.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal hapus: ' . $conn->error]);
    }
    exit;
}

// ── NEXT ID (admin only) ─────────────────────────────────────────────────────
if ($action === 'next_id') {
    check_admin_api();

    $year   = intval($_GET['year'] ?? date('Y'));
    $prefix = 'IS-' . $year . '-';

    // Ambil semua certificate_id yang punya prefix tahun ini, lalu cari nomor urut max
    $stmt = $conn->prepare(
        "SELECT certificate_id FROM certificates WHERE certificate_id LIKE ? ORDER BY certificate_id DESC"
    );
    $like = $prefix . '%';
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $maxSeq = 0;
    foreach ($rows as $row) {
        // Ambil angka setelah prefix, misal IS-2026-007 → 7
        $suffix = substr($row['certificate_id'], strlen($prefix));
        if (is_numeric($suffix)) {
            $maxSeq = max($maxSeq, (int)$suffix);
        }
    }

    $nextSeq  = $maxSeq + 1;
    $nextId   = $prefix . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);

    echo json_encode(['success' => true, 'next_id' => $nextId, 'year' => $year, 'seq' => $nextSeq]);
    exit;
}

// ── GET single (admin edit form) ────────────────────────────────────────────
if ($action === 'get') {
    check_admin_api();
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $conn->prepare("SELECT * FROM certificates WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Tidak ditemukan.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action tidak dikenal.']);
