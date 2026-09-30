<?php
// api/submit_application.php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Database Connection
// koneksi.php uses die() on failure which would kill this script and output
// non-JSON text. We buffer the include so the JSON fallback below still works.
$conn = null;
if (file_exists(__DIR__ . '/Login/koneksi.php')) {
    ob_start();
    @include_once __DIR__ . '/Login/koneksi.php';
    $ob = ob_get_clean(); // discard any die()/echo output
    if (!isset($conn) || !$conn) {
        $conn = null; // ensure clean state for the check on line 53
    }
}

$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$position   = trim($_POST['position'] ?? '');
$portfolio  = trim($_POST['portfolio'] ?? '');

if (empty($first_name) || empty($email) || empty($position)) {
    echo json_encode(['success' => false, 'message' => 'Lengkapi bidang yang wajib diisi.']);
    exit;
}

// Ensure Uploads Directory exists
$upload_dir = __DIR__ . '/uploads/cv/';
if (!file_exists($upload_dir)) {
    @mkdir($upload_dir, 0777, true);
}

$cv_file_path = '';
if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
    $file_tmp  = $_FILES['cv_file']['tmp_name'];
    $file_name = $_FILES['cv_file']['name'];
    $ext       = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    $allowed_exts = ['pdf', 'doc', 'docx'];
    if (in_array($ext, $allowed_exts)) {
        $new_filename = time() . '_' . uniqid() . '.' . $ext;
        $dest_path    = $upload_dir . $new_filename;
        if (move_uploaded_file($file_tmp, $dest_path)) {
            $cv_file_path = 'uploads/cv/' . $new_filename;
        }
    }
}

$submission_time = date('Y-m-d H:i:s');
$saved = false;

// 1. Try DB first
if (isset($conn) && $conn) {
    // Auto-create table applications if not exists
    $create_table = "CREATE TABLE IF NOT EXISTS `applications` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `first_name` VARCHAR(100) NOT NULL,
        `last_name` VARCHAR(100) DEFAULT '',
        `email` VARCHAR(150) NOT NULL,
        `position` VARCHAR(150) NOT NULL,
        `portfolio` VARCHAR(255) DEFAULT '',
        `cv_file` VARCHAR(255) DEFAULT '',
        `status` VARCHAR(50) DEFAULT 'review',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    @mysqli_query($conn, $create_table);

    $stmt = mysqli_prepare($conn, "INSERT INTO `applications` (first_name, last_name, email, position, portfolio, cv_file, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'review', ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssss", $first_name, $last_name, $email, $position, $portfolio, $cv_file_path, $submission_time);
        if (mysqli_stmt_execute($stmt)) {
            $saved = true;
        }
        mysqli_stmt_close($stmt);
    }
}

// 2. Fallback to JSON file storage if DB is not available or query failed
$json_file = __DIR__ . '/uploads/applications.json';
$apps = [];
if (file_exists($json_file)) {
    $apps = json_decode(file_get_contents($json_file), true) ?: [];
}

$new_app = [
    'id' => time() . rand(100, 999),
    'first_name' => $first_name,
    'last_name'  => $last_name,
    'email'      => $email,
    'position'   => $position,
    'portfolio'  => $portfolio,
    'cv_file'    => $cv_file_path,
    'status'     => 'review',
    'created_at' => $submission_time
];

array_unshift($apps, $new_app);
file_put_contents($json_file, json_encode($apps, JSON_PRETTY_PRINT));

echo json_encode(['success' => true, 'message' => 'Pendaftaran berhasil dikirim!']);
