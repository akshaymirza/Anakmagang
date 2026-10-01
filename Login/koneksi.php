<?php
// Nonaktifkan mysqli strict exception agar PHP 8.1+ tidak melempar Uncaught mysqli_sql_exception
mysqli_report(MYSQLI_REPORT_OFF);

$host     = "localhost";
$user     = "root";      // Username default XAMPP / Laragon
$password = "";          // Kosongkan jika default
$database = "db_internspace";

// 1. Coba koneksi langsung ke database
$conn = @mysqli_connect($host, $user, $password, $database);

// 2. Jika database belum ada atau koneksi gagal, coba buat database otomatis
if (!$conn) {
    // Coba koneksi ke MySQL server tanpa menentukan database
    $conn = @mysqli_connect($host, $user, $password);
    
    if ($conn) {
        // Buat database jika belum ada
        @mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        
        // Pilih database yang baru dibuat
        if (@mysqli_select_db($conn, $database)) {
            // Cek apakah tabel sudah ada
            $checkTables = @mysqli_query($conn, "SHOW TABLES");
            if ($checkTables && mysqli_num_rows($checkTables) === 0) {
                // Import file db_internspace.sql secara otomatis
                $sqlFile = __DIR__ . '/db_internspace.sql';
                if (file_exists($sqlFile)) {
                    $sqlContent = file_get_contents($sqlFile);
                    if (!empty($sqlContent)) {
                        // Kompatibilitas MariaDB / MySQL versi lama jika ada collation 0900
                        $serverInfo = @mysqli_get_server_info($conn);
                        if ($serverInfo && (strpos(strtolower($serverInfo), 'mariadb') !== false || version_compare($serverInfo, '8.0.0', '<'))) {
                            $sqlContent = str_replace('utf8mb4_0900_ai_ci', 'utf8mb4_general_ci', $sqlContent);
                        }
                        @mysqli_multi_query($conn, $sqlContent);
                        do {
                            if ($res = @mysqli_store_result($conn)) {
                                @mysqli_free_result($res);
                            }
                        } while (@mysqli_more_results($conn) && @mysqli_next_result($conn));
                    }
                }
            }
        } else {
            $conn = false;
        }
    }
}

// 3. Jika koneksi tetap gagal (misal server MySQL XAMPP/Laragon belum dinyalakan)
if (!$conn) {
    $errorMsg = mysqli_connect_error() ? mysqli_connect_error() : "Pastikan layanan MySQL (XAMPP / Laragon) sudah berjalan.";
    die("Koneksi database gagal: " . $errorMsg);
}
?>