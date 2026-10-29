<?php
/**
 * about_helper.php
 * Helper functions to manage About Page content (Add / Remove / Edit sections and section images)
 * Synchronizes with MySQL `about_info` table and `uploads/about_content.json`
 */

if (!function_exists('get_about_file_path')) {
    function get_about_file_path(): string {
        return __DIR__ . '/uploads/about_content.json';
    }
}

if (!function_exists('init_about_table')) {
    function init_about_table($conn): bool {
        if (!$conn) return false;
        $sql = "CREATE TABLE IF NOT EXISTS `about_info` (
            `id` INT PRIMARY KEY DEFAULT 1,
            `kedayweb_title` VARCHAR(255) NOT NULL,
            `kedayweb_description` TEXT NOT NULL,
            `kedayweb_vision` TEXT NOT NULL,
            `kedayweb_mission` TEXT NOT NULL,
            `kedayweb_image` VARCHAR(500) NOT NULL,
            `intern_title` VARCHAR(255) NOT NULL,
            `intern_description` TEXT NOT NULL,
            `intern_benefits` TEXT NOT NULL,
            `intern_workflow` TEXT NOT NULL,
            `intern_image` VARCHAR(500) NOT NULL,
            `extra_sections` LONGTEXT NULL,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        @mysqli_query($conn, $sql);

        // Auto migration for existing table
        $chk_extra = @mysqli_query($conn, "SHOW COLUMNS FROM `about_info` LIKE 'extra_sections'");
        if ($chk_extra && mysqli_num_rows($chk_extra) === 0) {
            @mysqli_query($conn, "ALTER TABLE `about_info` ADD COLUMN `extra_sections` LONGTEXT NULL");
        }
        return true;
    }
}

if (!function_exists('get_seed_about_data')) {
    function get_seed_about_data(): array {
        return [
            'id' => 1,
            'kedayweb_title' => 'Tentang Kedayweb Technology',
            'kedayweb_description' => 'Kedayweb adalah perusahaan konsultan teknologi dan studio pengembangan perangkat lunak modern yang berfokus pada transformasi digital, pembuatan aplikasi web enterprise, sistem informasi manajemen, dan solusi cloud modern.',
            'kedayweb_vision' => 'Menjadi pusat inovasi teknologi terdepan yang memberdayakan talenta digital muda Indonesia untuk menciptakan produk perangkat lunak berstandar global.',
            'kedayweb_mission' => "1. Mengembangkan produk teknologi yang cepat, aman, dan intuitif.\n2. Menjadi wadah inkubasi talenta magang terbaik melalui bimbingan praktis dari praktisi berpengalaman.\n3. Membangun ekosistem kolaborasi developer yang terbuka dan progresif.",
            'kedayweb_image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80',

            'intern_title' => 'Program Magang (Internship) Kedayweb',
            'intern_description' => 'Program magang Kedayweb dirancang khusus untuk mahasiswa dan pelajar berbakat yang ingin merasakan pengalaman kerja nyata di industri perangkat lunak. Peserta magang dilatih secara langsung dalam sprint project riil, arsitektur koding modern, dan kerja sama tim agile.',
            'intern_benefits' => "• Pengalaman mengerjakan Real-World Enterprise Projects.\n• Mentoring eksklusif 1-on-1 bersama Senior Web Engineer & UI/UX Specialist.\n• Sertifikat Magang Resmi & Surat Rekomendasi Karir.\n• Lingkungan kerja hybrid yang fleksibel dan seru.",
            'intern_workflow' => "1. Orientation & Onboarding Bootcamp (Minggu 1)\n2. Sprint Development & Coding Tasks (Minggu 2 - 10)\n3. Showcase Demo Day & Certification (Minggu Akhir)",
            'intern_image' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=80',

            'extra_sections' => [],
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }
}

if (!function_exists('handle_about_image_upload')) {
    function handle_about_image_upload($file_array, $prefix = 'about'): ?string {
        if (!isset($file_array) || !is_array($file_array) || $file_array['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($file_array['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_exts)) {
            return null;
        }

        $upload_dir = __DIR__ . '/uploads/about/';
        if (!is_dir($upload_dir)) {
            @mkdir($upload_dir, 0777, true);
        }

        $safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $prefix);
        $filename = $safe_name . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $upload_dir . $filename;

        if (move_uploaded_file($file_array['tmp_name'], $dest)) {
            return 'uploads/about/' . $filename;
        }
        return null;
    }
}

if (!function_exists('save_about_content')) {
    function save_about_content($conn, $about_file, array $data): bool {
        $data['updated_at'] = date('Y-m-d H:i:s');
        unset($data['gallery_images']); // Pastikan tidak ada galeri di data tentang

        $upload_dir = dirname($about_file);
        if (!is_dir($upload_dir)) {
            @mkdir($upload_dir, 0777, true);
        }

        // 1. Simpan ke JSON file
        @file_put_contents($about_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        // 2. Simpan ke Database MySQL jika terkoneksi
        if ($conn) {
            init_about_table($conn);
            $extra_json = json_encode($data['extra_sections'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $stmt = mysqli_prepare($conn, "REPLACE INTO `about_info` 
                (id, kedayweb_title, kedayweb_description, kedayweb_vision, kedayweb_mission, kedayweb_image, intern_title, intern_description, intern_benefits, intern_workflow, intern_image, extra_sections, updated_at) 
                VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssssssssss",
                    $data['kedayweb_title'],
                    $data['kedayweb_description'],
                    $data['kedayweb_vision'],
                    $data['kedayweb_mission'],
                    $data['kedayweb_image'],
                    $data['intern_title'],
                    $data['intern_description'],
                    $data['intern_benefits'],
                    $data['intern_workflow'],
                    $data['intern_image'],
                    $extra_json
                );
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }

        return true;
    }
}

if (!function_exists('get_about_content')) {
    function get_about_content($conn, $about_file): array {
        $data = null;
        if ($conn) {
            init_about_table($conn);
            $res = @mysqli_query($conn, "SELECT * FROM `about_info` WHERE id = 1");
            if ($res && mysqli_num_rows($res) > 0) {
                $data = mysqli_fetch_assoc($res);
                if (!empty($data['extra_sections'])) {
                    $dec = json_decode($data['extra_sections'], true);
                    $data['extra_sections'] = is_array($dec) ? $dec : [];
                } else {
                    $data['extra_sections'] = [];
                }
            }
        }

        if (!$data) {
            if (file_exists($about_file)) {
                $raw = @file_get_contents($about_file);
                if ($raw) {
                    $data = json_decode($raw, true);
                }
            }
        }

        $seed = get_seed_about_data();
        if (!$data || !is_array($data)) {
            $data = $seed;
            save_about_content($conn, $about_file, $data);
        } else {
            if (!isset($data['extra_sections']) || !is_array($data['extra_sections'])) {
                $data['extra_sections'] = [];
            }
            unset($data['gallery_images']);
        }

        return $data;
    }
}

if (!function_exists('reset_about_to_default')) {
    function reset_about_to_default($conn, $about_file): array {
        $seed = get_seed_about_data();
        save_about_content($conn, $about_file, $seed);
        return $seed;
    }
}
