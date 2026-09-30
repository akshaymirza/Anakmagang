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
        'notes'            => $cert['notes'],
    ]);
    exit;
}

// ── LIST semua sertifikat (admin only) ─────────────────────────────────────
if ($action === 'list') {
    check_admin_api();

    $search = '%' . trim($_GET['search'] ?? '') . '%';
    $stmt = $conn->prepare(
        "SELECT id, certificate_id, intern_name, intern_position, university,
                final_grade, status,
                DATE_FORMAT(issue_date, '%d %b %Y') AS issue_fmt
         FROM certificates
         WHERE certificate_id LIKE ? OR intern_name LIKE ? OR intern_position LIKE ?
         ORDER BY created_at DESC"
    );
    $stmt->bind_param('sss', $search, $search, $search);
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

    $stmt = $conn->prepare(
        "UPDATE certificates SET
            intern_name=?, intern_position=?, university=?, major=?,
            start_date=?, end_date=?, issue_date=?,
            score_technical=?, score_discipline=?, score_attitude=?,
            final_grade=?, supervisor_name=?, status=?, notes=?
         WHERE id=?"
    );
    $stmt->bind_param(
        'sssssssiiisssi',
        $_POST['intern_name'], $_POST['intern_position'], $_POST['university'], $_POST['major'],
        $_POST['start_date'], $_POST['end_date'], $_POST['issue_date'],
        $_POST['score_technical'], $_POST['score_discipline'], $_POST['score_attitude'],
        $_POST['final_grade'], $_POST['supervisor_name'], $_POST['status'], $_POST['notes'],
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
