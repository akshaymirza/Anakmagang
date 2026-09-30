<?php
require_once __DIR__ . '/session.php';

// Database connection attempt
$conn = null;
if (file_exists(__DIR__ . '/Login/koneksi.php')) {
    include_once __DIR__ . '/Login/koneksi.php';
}

$is_logged_in = is_logged_in();
$is_superadmin = ($is_logged_in && is_superadmin());
$about_file = __DIR__ . '/uploads/about_content.json';

// Auto-create MySQL table for about content
function init_about_table($conn) {
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
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    @mysqli_query($conn, $sql);
    return true;
}

// Seed dummy data
function get_seed_about_data() {
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
        'updated_at' => date('Y-m-d H:i:s')
    ];
}

function save_about_json($about_file, $data) {
    file_put_contents($about_file, json_encode($data, JSON_PRETTY_PRINT));
}

function get_about_content($conn, $about_file) {
    $data = null;
    if ($conn) {
        init_about_table($conn);
        $res = @mysqli_query($conn, "SELECT * FROM `about_info` WHERE id = 1");
        if ($res && mysqli_num_rows($res) > 0) {
            $data = mysqli_fetch_assoc($res);
        }
    }

    if (!$data) {
        if (file_exists($about_file)) {
            $data = json_decode(file_get_contents($about_file), true);
        }
    }

    if (!$data || !is_array($data)) {
        $data = get_seed_about_data();
        save_about_json($about_file, $data);
    }

    return $data;
}

// POST Handler khusus Superadmin untuk edit data
$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_about') {
    if (!$is_superadmin) {
        $msg = 'Hanya Superadmin yang memiliki wewenang untuk memperbarui konten halaman ini!';
        $msg_type = 'danger';
    } else {
        $data = [
            'id' => 1,
            'kedayweb_title'       => trim($_POST['kedayweb_title'] ?? ''),
            'kedayweb_description' => trim($_POST['kedayweb_description'] ?? ''),
            'kedayweb_vision'      => trim($_POST['kedayweb_vision'] ?? ''),
            'kedayweb_mission'     => trim($_POST['kedayweb_mission'] ?? ''),
            'kedayweb_image'       => trim($_POST['kedayweb_image'] ?? ''),

            'intern_title'         => trim($_POST['intern_title'] ?? ''),
            'intern_description'   => trim($_POST['intern_description'] ?? ''),
            'intern_benefits'      => trim($_POST['intern_benefits'] ?? ''),
            'intern_workflow'      => trim($_POST['intern_workflow'] ?? ''),
            'intern_image'         => trim($_POST['intern_image'] ?? ''),
            'updated_at'           => date('Y-m-d H:i:s')
        ];

        // Handle Image Uploads if any
        $upload_dir = __DIR__ . '/uploads/about/';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0777, true);

        if (isset($_FILES['kedayweb_image_file']) && $_FILES['kedayweb_image_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['kedayweb_image_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $fname = 'kedayweb_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['kedayweb_image_file']['tmp_name'], $upload_dir . $fname)) {
                    $data['kedayweb_image'] = 'uploads/about/' . $fname;
                }
            }
        }

        if (isset($_FILES['intern_image_file']) && $_FILES['intern_image_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['intern_image_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $fname = 'intern_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['intern_image_file']['tmp_name'], $upload_dir . $fname)) {
                    $data['intern_image'] = 'uploads/about/' . $fname;
                }
            }
        }

        // Save to DB
        if ($conn) {
            init_about_table($conn);
            $stmt = mysqli_prepare($conn, "REPLACE INTO `about_info` (id, kedayweb_title, kedayweb_description, kedayweb_vision, kedayweb_mission, kedayweb_image, intern_title, intern_description, intern_benefits, intern_workflow, intern_image, updated_at) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssssssssss", 
                    $data['kedayweb_title'], $data['kedayweb_description'], $data['kedayweb_vision'], $data['kedayweb_mission'], $data['kedayweb_image'],
                    $data['intern_title'], $data['intern_description'], $data['intern_benefits'], $data['intern_workflow'], $data['intern_image']
                );
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }

        // Sync to JSON file
        save_about_json($about_file, $data);

        $msg = 'Konten Tentang Kedayweb & Anak Magang berhasil diperbarui oleh Superadmin!';
        $msg_type = 'success';
    }
}

$about = get_about_content($conn, $about_file);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <title>Tentang Kedayweb & Anak Magang</title>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="shared-config.js"></script>
    <style>
        body { font-family: Inter, sans-serif; }
        .font-geist { font-family: Geist, sans-serif; }
    </style>
</head>
<body class="<?php echo $is_logged_in ? 'bg-background text-on-surface font-body-md flex h-screen overflow-hidden' : 'bg-slate-50 text-slate-900 min-h-screen flex flex-col'; ?>">
    <?php 
    if ($is_logged_in) {
        $active = 'about';
        include 'partials/sidebar-intern.php';
    } else {
        $nav_icon = 'info';
        $nav_cta_label = 'Masuk Portal';
        include 'partials/topnav-public.php';
    }
    ?>

    <!-- Main Content Area -->
    <main class="<?php echo $is_logged_in ? 'md:ml-[16.5rem] h-screen overflow-y-auto' : 'min-h-screen'; ?> flex-1 flex flex-col">
        <?php if ($is_logged_in): ?>
        <!-- Sticky TopBar for Logged-in Users -->
        <header class="w-full h-20 bg-surface-container-lowest border-b border-outline-variant sticky top-0 z-10 flex items-center justify-between px-gutter shrink-0">
            <div class="flex items-center gap-3">
                <button onclick="toggleMobileSidebar()" class="md:hidden text-on-surface hover:text-primary focus:outline-none flex items-center p-1 rounded-lg hover:bg-surface-container-high" aria-label="Toggle Sidebar">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">info</span>
                    <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Tentang Kedayweb</h2>
                </div>
            </div>
            <div class="flex items-center gap-sm">
                <div class="flex items-center gap-sm p-1.5 px-3 rounded-full border border-outline-variant bg-surface-bright shadow-2xs">
                    <div class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container overflow-hidden shrink-0">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">account_circle</span>
                    </div>
                    <span class="font-label-md text-label-md hidden sm:inline-block"><?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="logout.php" class="text-error hover:text-red-700 hover:bg-red-50 p-1.5 rounded-full transition-colors flex items-center justify-center" title="Keluar" aria-label="Keluar">
                        <span class="material-symbols-outlined text-[20px]">logout</span>
                    </a>
                </div>
            </div>
        </header>
        <?php endif; ?>

        <!-- Main Body Content -->
        <div class="w-full max-w-7xl mx-auto p-4 md:p-8 space-y-8 flex-1">
            <!-- Hero Header Card -->
            <section class="relative w-full rounded-3xl overflow-hidden bg-gradient-to-r from-blue-950 via-slate-900 to-indigo-950 text-white p-6 md:p-10 shadow-xl border border-indigo-900/50">
                <!-- Ambient decorative icon -->
                <div class="absolute -right-6 -bottom-6 opacity-10 text-white pointer-events-none">
                    <span class="material-symbols-outlined text-[200px] md:text-[260px]">info</span>
                </div>

                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="space-y-3 max-w-3xl">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-blue-500/20 text-blue-300 text-xs font-bold border border-blue-400/30 backdrop-blur-md">
                            <span class="material-symbols-outlined text-sm">verified_user</span>
                            <span>Official Information & Company Profile</span>
                        </span>
                        <h1 class="font-geist text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight text-white">
                            Tentang Kedayweb & Anak Magang
                        </h1>
                        <p class="text-slate-300 text-xs sm:text-sm md:text-base leading-relaxed max-w-2xl">
                            Mengenal lebih dekat profil Kedayweb Technology dan ekosistem program magang yang memberdayakan talenta digital muda.
                        </p>
                    </div>
                    
                    <?php if ($is_superadmin): ?>
                        <button onclick="openEditAboutModal()" class="shrink-0 px-6 py-3.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-sm rounded-xl transition-all shadow-xl flex items-center gap-2 active:scale-95">
                            <span class="material-symbols-outlined text-lg">edit_note</span>
                            <span>Edit Konten (Superadmin)</span>
                        </button>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Alert Notification -->
            <?php if (!empty($msg)): ?>
                <div class="p-4 rounded-xl <?php echo $msg_type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'; ?> flex justify-between items-center text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined"><?php echo $msg_type === 'success' ? 'check_circle' : 'info'; ?></span>
                        <span><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-slate-500 hover:text-slate-800">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Section 1: Tentang Kedayweb -->
            <section class="bg-white rounded-3xl border border-slate-200 p-6 md:p-10 shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-6 space-y-5">
                    <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-100">
                        <span class="material-symbols-outlined text-sm">domain</span>
                        <span>Profil Perusahaan</span>
                    </div>
                    <h2 class="font-geist text-2xl md:text-3xl font-extrabold text-slate-900 leading-tight">
                        <?php echo htmlspecialchars($about['kedayweb_title'], ENT_QUOTES, 'UTF-8'); ?>
                    </h2>
                    <p class="text-slate-600 text-sm md:text-base leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($about['kedayweb_description'], ENT_QUOTES, 'UTF-8')); ?>
                    </p>

                    <div class="pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/60">
                            <h4 class="font-geist font-bold text-xs uppercase tracking-wider text-blue-700 mb-1 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">visibility</span>
                                <span>Visi Utama</span>
                            </h4>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                <?php echo htmlspecialchars($about['kedayweb_vision'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/60">
                            <h4 class="font-geist font-bold text-xs uppercase tracking-wider text-blue-700 mb-1 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">flag</span>
                                <span>Misi Utama</span>
                            </h4>
                            <div class="text-xs text-slate-600 leading-relaxed whitespace-pre-line">
                                <?php echo htmlspecialchars($about['kedayweb_mission'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-6 h-80 md:h-[420px] rounded-2xl overflow-hidden shadow-md relative bg-slate-100 group">
                    <img src="<?php echo htmlspecialchars($about['kedayweb_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Profil Kedayweb" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                    <span class="absolute bottom-4 left-4 bg-black/60 backdrop-blur-md text-white font-geist text-xs font-bold px-3 py-1.5 rounded-xl border border-white/20">
                        Kedayweb Technology Workspace
                    </span>
                </div>
            </section>

            <!-- Section 2: Tentang Anak Magang -->
            <section class="bg-white rounded-3xl border border-slate-200 p-6 md:p-10 shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-6 order-2 lg:order-1 h-80 md:h-[420px] rounded-2xl overflow-hidden shadow-md relative bg-slate-100 group">
                    <img src="<?php echo htmlspecialchars($about['intern_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Anak Magang Kedayweb" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                    <span class="absolute bottom-4 left-4 bg-black/60 backdrop-blur-md text-white font-geist text-xs font-bold px-3 py-1.5 rounded-xl border border-white/20">
                        Internship Culture & Community
                    </span>
                </div>

                <div class="lg:col-span-6 order-1 lg:order-2 space-y-5">
                    <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                        <span class="material-symbols-outlined text-sm">groups</span>
                        <span>Ekosistem Intern</span>
                    </div>
                    <h2 class="font-geist text-2xl md:text-3xl font-extrabold text-slate-900 leading-tight">
                        <?php echo htmlspecialchars($about['intern_title'], ENT_QUOTES, 'UTF-8'); ?>
                    </h2>
                    <p class="text-slate-600 text-sm md:text-base leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($about['intern_description'], ENT_QUOTES, 'UTF-8')); ?>
                    </p>

                    <div class="pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-emerald-50/60 p-4 rounded-2xl border border-emerald-100">
                            <h4 class="font-geist font-bold text-xs uppercase tracking-wider text-emerald-800 mb-1 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">workspace_premium</span>
                                <span>Benefit Magang</span>
                            </h4>
                            <div class="text-xs text-slate-700 leading-relaxed whitespace-pre-line">
                                <?php echo htmlspecialchars($about['intern_benefits'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>
                        <div class="bg-indigo-50/60 p-4 rounded-2xl border border-indigo-100">
                            <h4 class="font-geist font-bold text-xs uppercase tracking-wider text-indigo-800 mb-1 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">account_tree</span>
                                <span>Tahapan Alur Kerja</span>
                            </h4>
                            <div class="text-xs text-slate-700 leading-relaxed whitespace-pre-line">
                                <?php echo htmlspecialchars($about['intern_workflow'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="shrink-0 mt-auto w-full">
            <?php include 'partials/footer.php'; ?>
        </div>
    </main>

    <!-- Modal Form Edit Konten (Khusus Superadmin) -->
    <?php if ($is_superadmin): ?>
        <div id="editAboutModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4 sticky top-0 bg-white z-10">
                    <h3 class="font-geist font-bold text-lg text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-500">edit_note</span>
                        <span>Edit Konten Tentang Kedayweb & Anak Magang</span>
                    </h3>
                    <button onclick="closeEditAboutModal()" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form action="about.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="update_about"/>

                    <!-- Part 1: Kedayweb -->
                    <div class="space-y-3 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <h4 class="font-bold text-sm text-blue-700 flex items-center gap-1">
                            <span class="material-symbols-outlined text-base">domain</span>
                            <span>Bagian 1: Profil Kedayweb Technology</span>
                        </h4>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Judul Profil Perusahaan</label>
                            <input type="text" name="kedayweb_title" value="<?php echo htmlspecialchars($about['kedayweb_title'], ENT_QUOTES, 'UTF-8'); ?>" required class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl outline-none focus:border-blue-600"/>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Kedayweb</label>
                            <textarea name="kedayweb_description" rows="3" required class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl outline-none focus:border-blue-600"><?php echo htmlspecialchars($about['kedayweb_description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Visi Perusahaan</label>
                                <textarea name="kedayweb_vision" rows="3" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl outline-none focus:border-blue-600"><?php echo htmlspecialchars($about['kedayweb_vision'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Misi Perusahaan</label>
                                <textarea name="kedayweb_mission" rows="3" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl outline-none focus:border-blue-600"><?php echo htmlspecialchars($about['kedayweb_mission'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Upload Foto Kedayweb / Masukkan URL Foto</label>
                            <input type="file" name="kedayweb_image_file" accept="image/*" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-xl bg-white mb-2"/>
                            <input type="url" name="kedayweb_image" value="<?php echo htmlspecialchars($about['kedayweb_image'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://..." class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl outline-none focus:border-blue-600"/>
                        </div>
                    </div>

                    <!-- Part 2: Anak Magang -->
                    <div class="space-y-3 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <h4 class="font-bold text-sm text-emerald-700 flex items-center gap-1">
                            <span class="material-symbols-outlined text-base">groups</span>
                            <span>Bagian 2: Profil & Ekosistem Anak Magang</span>
                        </h4>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Judul Program Magang</label>
                            <input type="text" name="intern_title" value="<?php echo htmlspecialchars($about['intern_title'], ENT_QUOTES, 'UTF-8'); ?>" required class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl outline-none focus:border-emerald-600"/>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Program Magang</label>
                            <textarea name="intern_description" rows="3" required class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl outline-none focus:border-emerald-600"><?php echo htmlspecialchars($about['intern_description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Benefit Magang</label>
                                <textarea name="intern_benefits" rows="3" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl outline-none focus:border-emerald-600"><?php echo htmlspecialchars($about['intern_benefits'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tahapan Alur Kerja Magang</label>
                                <textarea name="intern_workflow" rows="3" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl outline-none focus:border-emerald-600"><?php echo htmlspecialchars($about['intern_workflow'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Upload Foto Anak Magang / Masukkan URL Foto</label>
                            <input type="file" name="intern_image_file" accept="image/*" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-xl bg-white mb-2"/>
                            <input type="url" name="intern_image" value="<?php echo htmlspecialchars($about['intern_image'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://..." class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl outline-none focus:border-emerald-600"/>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 sticky bottom-0 bg-white py-2">
                        <button type="button" onclick="closeEditAboutModal()" class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold rounded-xl text-xs transition-all shadow-md">Simpan Perubahan (Superadmin)</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openEditAboutModal() {
                document.getElementById('editAboutModal').classList.remove('hidden');
            }
            function closeEditAboutModal() {
                document.getElementById('editAboutModal').classList.add('hidden');
            }
        </script>
    <?php endif; ?>
</body>
</html>
