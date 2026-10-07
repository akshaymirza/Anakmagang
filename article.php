<?php
require_once __DIR__ . '/session.php';

// Database connection attempt
$conn = null;
if (file_exists(__DIR__ . '/Login/koneksi.php')) {
    include_once __DIR__ . '/Login/koneksi.php';
}

$is_logged_in = is_logged_in();
$is_admin = ($is_logged_in && is_admin());
$json_file = __DIR__ . '/uploads/articles.json';
$upload_dir = __DIR__ . '/uploads/articles/';

if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0777, true);
}

// Function to auto-create MySQL table if DB is available
function init_db_table($conn) {
    if (!$conn) return false;
    $sql = "CREATE TABLE IF NOT EXISTS `articles` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `category` VARCHAR(50) NOT NULL DEFAULT 'Aktivitas Harian',
        `author` VARCHAR(100) NOT NULL,
        `excerpt` TEXT,
        `content` TEXT NOT NULL,
        `image_url` VARCHAR(500),
        `is_featured` TINYINT(1) DEFAULT 0,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    @mysqli_query($conn, $sql);
    return true;
}

// Initial sample data
function get_seed_articles() {
    return [
        [
            'id' => 1,
            'title' => 'Kickoff Meeting & Mentoring Perdana Magang Batch 2026 Kedayweb',
            'category' => 'Workshop & Mentoring',
            'author' => 'Super Admin',
            'excerpt' => 'Pembukaan resmi program magang batch 2026 sekaligus perkenalan dengan para mentor dev team dan arahan project utama.',
            'content' => '<p>Hari ini menandai dimulainya perjalanan baru bagi anak-anak magang Kedayweb Batch 2026. Acara dibuka langsung oleh Head of Technology dengan pemaparan visi perusahaan, standar kualifikasi koding, serta pengenalan lingkungan kerja modern.</p><p>Para peserta magang diberikan pembekalan awal mengenai Workflow Git, Agile Development, dan pembagian tim sprint. Setiap peserta dipasangkan dengan satu orang senior mentor yang akan mendampingi proses perkuliahan lapangan selama 3-6 bulan ke depan.</p><p>Acara diakhiri dengan sesi ramah tamah dan pembagian perlengkapan kerja (starter kit magang).</p>',
            'image_url' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1000&q=80',
            'is_featured' => 1,
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ],
        [
            'id' => 2,
            'title' => 'Showcase Sprint 1 - Demonstrasi Fitur Baru Portal InternSpace',
            'category' => 'Project & Coding',
            'author' => 'Tim Web Developer',
            'excerpt' => 'Anak-anak magang sukses mempresentasikan modul Kanban Board dan Realtime Shift Timer di depan manajemen.',
            'content' => '<p>Pada sesi Showcase Sprint 1, tim anak magang menunjukkan pencapaian luar biasa dalam mengembangkan portal InternSpace. Modul papan Kanban interaktif dan sistem absensi berbasis jam kerja berhasil diselesaikan tepat waktu.</p><p>Pengujian fitur dilakukan secara langsung dengan skenario beban kerja riil. Manajemen memberikan apresiasi tinggi atas kualitas UI/UX berbasis Material Design 3 yang terlihat sangat intuitif dan responsif.</p>',
            'image_url' => 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?auto=format&fit=crop&w=1000&q=80',
            'is_featured' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
        ],
        [
            'id' => 3,
            'title' => 'Workshop UI/UX Design System & Material Design 3 Implementation',
            'category' => 'Workshop & Mentoring',
            'author' => 'Lead UI Designer',
            'excerpt' => 'Sesi berbagai ilmu mengenai perancangan komponen UI konsisten, tata warna HSL, dan mikro-interaksi web modern.',
            'content' => '<p>Desain aplikasi yang memukau bukan hanya soal warna indah, melainkan juga hierarki visual dan aksesibilitas. Dalam workshop berdurasi 3 jam ini, peserta magang dipandu secara praktis untuk mengimplementasikan variabel Tailwind CSS dan token desain.</p><p>Hasil dari workshop ini langsung diterapkan dalam perbaikan antarmuka dashboard magang yang kini lebih rapi dan nyaman dipandang.</p>',
            'image_url' => 'https://images.unsplash.com/photo-1581291518633-83b4ebd1d83e?auto=format&fit=crop&w=1000&q=80',
            'is_featured' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-7 days'))
        ],
        [
            'id' => 4,
            'title' => 'Daily Standup Routine & Pair Programming Session',
            'category' => 'Aktivitas Harian',
            'author' => 'Alex Doe',
            'excerpt' => 'Setiap pagi jam 09.00 WIB, anak magang berkumpul untuk menyelaraskan target harian dan menyelesaikan blocker koding bersama.',
            'content' => '<p>Budaya kolaborasi merupakan kunci utama di Kedayweb. Melalui sesi Daily Standup 15 menit, setiap peserta menyampaikan apa yang dikerjakan kemarin, apa yang akan dibuat hari ini, dan kendala yang dihadapi.</p><p>Metode Pair Programming sering diterapkan jika menemukan bug yang rumit, sehingga pengetahuan antar anggota tim dapat saling melengkapi secara efisien.</p>',
            'image_url' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1000&q=80',
            'is_featured' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))
        ],
        [
            'id' => 5,
            'title' => 'Penghargaan Intern of the Month Periode Kuartal Pertama',
            'category' => 'Prestasi',
            'author' => 'Super Admin',
            'excerpt' => 'Apresiasi khusus atas dedikasi, inisiatif tinggi, dan kontribusi kode terbaik selama bulan pertama masa magang.',
            'content' => '<p>Selamat kepada peserta magang terpilih yang berhasil memenangkan sertifikat kriteria Intern of the Month. Penilaian dilakukan berdasar kedisiplinan absensi, penyelesaian tugas Kanban, serta keaktifan dalam sesi diskusi kelompok.</p><p>Semoga penghargaan ini menjadi pemacu semangat bagi seluruh peserta magang lainnya untuk terus berkembang dan memberikan karya terbaik!</p>',
            'image_url' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1000&q=80',
            'is_featured' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-14 days'))
        ]
    ];
}

// Function to fetch articles
function get_articles($conn, $json_file) {
    if ($conn) {
        init_db_table($conn);
        $res = @mysqli_query($conn, "SELECT * FROM articles ORDER BY is_featured DESC, created_at DESC");
        if ($res && mysqli_num_rows($res) > 0) {
            $articles = [];
            while ($row = mysqli_fetch_assoc($res)) {
                $articles[] = $row;
            }
            return $articles;
        }
    }
    
    // JSON Fallback
    if (!file_exists($json_file)) {
        $seeds = get_seed_articles();
        file_put_contents($json_file, json_encode($seeds, JSON_PRETTY_PRINT));
        return $seeds;
    }

    $json_data = json_decode(file_get_contents($json_file), true);
    if (!is_array($json_data) || empty($json_data)) {
        $seeds = get_seed_articles();
        file_put_contents($json_file, json_encode($seeds, JSON_PRETTY_PRINT));
        return $seeds;
    }

    // Sort featured first, then date desc
    usort($json_data, function($a, $b) {
        if (($b['is_featured'] ?? 0) != ($a['is_featured'] ?? 0)) {
            return ($b['is_featured'] ?? 0) <=> ($a['is_featured'] ?? 0);
        }
        return strtotime($b['created_at'] ?? 0) <=> strtotime($a['created_at'] ?? 0);
    });

    return $json_data;
}

// Function to save/update article
function save_article($conn, $json_file, $data) {
    $title = trim($data['title']);
    $category = trim($data['category']);
    $author = trim($data['author']);
    $excerpt = trim($data['excerpt']);
    $content = trim($data['content']);
    $image_url = trim($data['image_url']);
    $is_featured = !empty($data['is_featured']) ? 1 : 0;
    $id = !empty($data['id']) ? intval($data['id']) : null;

    if (empty($image_url)) {
        $image_url = 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1000&q=80';
    }

    if ($conn) {
        init_db_table($conn);
        if ($is_featured === 1) {
            @mysqli_query($conn, "UPDATE articles SET is_featured = 0");
        }
        if ($id) {
            $stmt = mysqli_prepare($conn, "UPDATE articles SET title=?, category=?, author=?, excerpt=?, content=?, image_url=?, is_featured=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssssssii", $title, $category, $author, $excerpt, $content, $image_url, $is_featured, $id);
            mysqli_stmt_execute($stmt);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO articles (title, category, author, excerpt, content, image_url, is_featured, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            mysqli_stmt_bind_param($stmt, "ssssssi", $title, $category, $author, $excerpt, $content, $image_url, $is_featured);
            mysqli_stmt_execute($stmt);
        }
    }

    // Always update JSON as sync / fallback
    $articles = get_articles(null, $json_file);
    if ($is_featured === 1) {
        foreach ($articles as &$art) {
            $art['is_featured'] = 0;
        }
    }

    if ($id) {
        $found = false;
        foreach ($articles as &$art) {
            if (intval($art['id']) === $id) {
                $art['title'] = $title;
                $art['category'] = $category;
                $art['author'] = $author;
                $art['excerpt'] = $excerpt;
                $art['content'] = $content;
                $art['image_url'] = $image_url;
                $art['is_featured'] = $is_featured;
                $art['updated_at'] = date('Y-m-d H:i:s');
                $found = true;
                break;
            }
        }
        if (!$found) {
            $articles[] = [
                'id' => $id,
                'title' => $title,
                'category' => $category,
                'author' => $author,
                'excerpt' => $excerpt,
                'content' => $content,
                'image_url' => $image_url,
                'is_featured' => $is_featured,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
    } else {
        $max_id = 0;
        foreach ($articles as $art) {
            if (intval($art['id']) > $max_id) $max_id = intval($art['id']);
        }
        $articles[] = [
            'id' => $max_id + 1,
            'title' => $title,
            'category' => $category,
            'author' => $author,
            'excerpt' => $excerpt,
            'content' => $content,
            'image_url' => $image_url,
            'is_featured' => $is_featured,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
    file_put_contents($json_file, json_encode($articles, JSON_PRETTY_PRINT));
}

// Function to delete article
function delete_article($conn, $json_file, $id) {
    $id = intval($id);
    if ($conn) {
        init_db_table($conn);
        $stmt = mysqli_prepare($conn, "DELETE FROM articles WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
    }
    // Update JSON
    $articles = get_articles(null, $json_file);
    $articles = array_values(array_filter($articles, function($art) use ($id) {
        return intval($art['id']) !== $id;
    }));
    file_put_contents($json_file, json_encode($articles, JSON_PRETTY_PRINT));
}

// Handle Admin POST Requests
$msg = '';
$msg_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    $action = $_POST['action'] ?? '';

    // Handle Image Upload if any
    $image_url = $_POST['image_url'] ?? '';
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image_file']['tmp_name'];
        $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '', $_FILES['image_file']['name']);
        if (move_uploaded_file($tmp_name, $upload_dir . $file_name)) {
            $image_url = 'uploads/articles/' . $file_name;
        }
    }

    if ($action === 'save') {
        save_article($conn, $json_file, [
            'id' => $_POST['id'] ?? '',
            'title' => $_POST['title'] ?? '',
            'category' => $_POST['category'] ?? 'Aktivitas Harian',
            'author' => $_POST['author'] ?? current_user_name(),
            'excerpt' => $_POST['excerpt'] ?? '',
            'content' => $_POST['content'] ?? '',
            'image_url' => $image_url,
            'is_featured' => $_POST['is_featured'] ?? 0
        ]);
        header('Location: article.php?status=saved');
        exit;
    } elseif ($action === 'delete') {
        delete_article($conn, $json_file, $_POST['id'] ?? 0);
        header('Location: article.php?status=deleted');
        exit;
    }
}

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'saved') {
        $msg = 'Artikel berhasil disimpan dan dipublikasikan!';
        $msg_type = 'success';
    } elseif ($_GET['status'] === 'deleted') {
        $msg = 'Artikel telah berhasil dihapus.';
        $msg_type = 'danger';
    }
}

// Fetch all articles
$articles = get_articles($conn, $json_file);

// Separate featured article if exists
$featured_article = null;
$regular_articles = [];

foreach ($articles as $art) {
    if (!$featured_article && !empty($art['is_featured'])) {
        $featured_article = $art;
    } else {
        $regular_articles[] = $art;
    }
}

if (!$featured_article && count($articles) > 0) {
    $featured_article = array_shift($regular_articles);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Aktivitas & Artikel Anak Magang | InternSpace</title>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <!-- Anime.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="shared-config.js"></script>
    <link rel="stylesheet" href="style.css"/>
    <style>
        .article-card {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .article-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -6px rgba(0, 35, 111, 0.12);
        }
        .glass-badge {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .category-chip,
        .category-chip:focus,
        .category-chip:active,
        .category-chip:focus-visible {
            outline: none !important;
            box-shadow: none !important;
            -webkit-tap-highlight-color: transparent;
        }
        /* ── Floating Batik Background Layer ── */
        #batik-layer {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }
        .batik-float {
            position: absolute;
            will-change: transform;
            background-image: url('uploads/Icon/batik-gajah-oling.png');
            background-size: contain;
            background-repeat: no-repeat;
        }
        @media (max-width: 768px) {
            .batik-float {
                max-width: 140px !important;
                max-height: 140px !important;
                opacity: 0.08 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col overflow-x-hidden relative">
    <?php if (!$is_logged_in): ?>
    <!-- ═══ Floating Batik Gajah Oling Background Layer ═══ -->
    <div id="batik-layer" aria-hidden="true">
        <div class="batik-float" style="width:260px;height:260px;top:4%;left:-4%;opacity:0.07;transform:rotate(-15deg)"></div>
        <div class="batik-float" style="width:180px;height:180px;top:12%;right:-2%;opacity:0.05;transform:rotate(22deg)"></div>
        <div class="batik-float" style="width:200px;height:200px;top:52%;left:-3%;opacity:0.07;transform:rotate(-30deg)"></div>
        <div class="batik-float" style="width:240px;height:240px;top:70%;right:-4%;opacity:0.05;transform:rotate(40deg)"></div>
        <div class="batik-float" style="width:150px;height:150px;top:85%;left:25%;opacity:0.06;transform:rotate(-5deg)"></div>
        <div class="batik-float" style="width:320px;height:320px;top:38%;left:55%;opacity:0.06;transform:rotate(8deg)"></div>
        <div class="batik-float" style="width:280px;height:280px;top:25%;left:20%;opacity:0.04;transform:rotate(55deg)"></div>
        <div class="batik-float" style="width:170px;height:170px;top:60%;right:25%;opacity:0.05;transform:rotate(-18deg)"></div>
    </div>
    <?php endif; ?>

    <?php if ($is_logged_in): ?>
        <!-- Sidebar Navigation for Logged-in Users -->
        <?php
        $active = 'article';
        if ($is_admin) {
            include 'partials/sidebar-admin.php';
        } else {
            include 'partials/sidebar-intern.php';
        }
        ?>
    <?php else: ?>
        <!-- Top Navigation for Public Visitors -->
        <?php
        $nav_icon = 'newspaper';
        $nav_home_href = 'index.php';
        include 'partials/topnav-public.php';
        ?>
    <?php endif; ?>

    <!-- Main Canvas -->
    <main class="<?php echo $is_logged_in ? 'md:ml-[16.5rem]' : ''; ?> flex-1 flex flex-col">
        <!-- Hero Header (Full Width Left to Right) -->
        <section class="w-full bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-900 text-white py-12 px-6 shadow-md relative overflow-hidden">
            <div class="max-w-7xl mx-auto flex flex-col justify-between gap-6 relative z-10">
                <?php if ($is_logged_in): ?>
                    <div class="md:hidden mb-2">
                        <button onclick="toggleMobileSidebar()" class="px-3 py-2 rounded-lg bg-white/20 text-white flex items-center gap-2 text-sm font-semibold border border-white/30 hover:bg-white/30 transition-all">
                            <span class="material-symbols-outlined">menu</span>
                            <span>Menu Sidebar</span>
                        </button>
                    </div>
                <?php endif; ?>
                <div class="space-y-3 max-w-3xl">
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-blue-500/20 text-blue-300 text-xs font-bold border border-blue-400/30 backdrop-blur-md">
                        <span class="material-symbols-outlined text-sm">newspaper</span>
                        <span>Portal Jurnal & Portal Artikel Magang</span>
                    </span>
                    <h1 class="font-geist text-2xl md:text-4xl font-extrabold tracking-tight text-white">Jurnal & Activity Log Anak Magang</h1>
                    <p class="mt-2 text-slate-300 text-sm leading-relaxed max-w-2xl">
                        Dokumentasi lengkap mengenai rilis fitur, jurnal harian, artikel teknis coding, serta pencapaian prestasi peserta magang Kedayweb.
                    </p>
                </div>
            </div>
        </section>

        <!-- Main Body Content -->
        <div class="w-full max-w-container-max mx-auto p-md md:p-gutter flex flex-col gap-xl">

            <!-- Category Filter Chips (Below Hero Section) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
                <button onclick="filterCategory('all', this)" class="category-chip active flex items-center gap-1.5 px-4 py-2 rounded-xl font-geist text-xs font-bold bg-blue-600 text-white shadow-sm transition-all cursor-pointer">
                    <span>Semua Artikel</span>
                </button>
                <button onclick="filterCategory('Aktivitas Harian', this)" class="category-chip flex items-center gap-1.5 px-4 py-2 rounded-xl font-geist text-xs font-semibold bg-white text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-slate-200/80 shadow-xs transition-all cursor-pointer">
                    <span>Aktivitas Harian</span>
                </button>
                <button onclick="filterCategory('Project & Coding', this)" class="category-chip flex items-center gap-1.5 px-4 py-2 rounded-xl font-geist text-xs font-semibold bg-white text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-slate-200/80 shadow-xs transition-all cursor-pointer">
                    <span>Project & Coding</span>
                </button>
                <button onclick="filterCategory('Workshop & Mentoring', this)" class="category-chip flex items-center gap-1.5 px-4 py-2 rounded-xl font-geist text-xs font-semibold bg-white text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-slate-200/80 shadow-xs transition-all cursor-pointer">
                    <span>Workshop</span>
                </button>
                <button onclick="filterCategory('Prestasi', this)" class="category-chip flex items-center gap-1.5 px-4 py-2 rounded-xl font-geist text-xs font-semibold bg-white text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-slate-200/80 shadow-xs transition-all cursor-pointer">
                    <span>Prestasi</span>
                </button>
            </div>

            <!-- Featured Hero Article (if available) -->
            <?php if ($featured_article): ?>
                <div id="featured-banner" class="article-item group relative w-full rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300" data-category="<?php echo htmlspecialchars($featured_article['category'], ENT_QUOTES, 'UTF-8'); ?>" data-title="<?php echo htmlspecialchars(strtolower($featured_article['title']), ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="grid grid-cols-1 lg:grid-cols-12 items-stretch min-h-[340px]">
                        <!-- Hero Image -->
                        <div class="lg:col-span-7 relative overflow-hidden min-h-[260px] lg:min-h-full bg-slate-100">
                            <img src="<?php echo htmlspecialchars($featured_article['image_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($featured_article['title'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent lg:hidden"></div>
                            <span class="absolute top-4 left-4 bg-black/60 backdrop-blur-md text-amber-300 border border-amber-400/30 text-xs font-bold px-3.5 py-1 rounded-full uppercase tracking-wider flex items-center gap-1 shadow-sm">
                                <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                                Featured Article
                            </span>
                        </div>
                        
                        <!-- Hero Details -->
                        <div class="lg:col-span-5 p-6 md:p-8 flex flex-col justify-between bg-white">
                            <div>
                                <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold mb-2">
                                    <span class="bg-blue-100 text-blue-800 px-2.5 py-0.5 rounded-full font-bold"><?php echo htmlspecialchars($featured_article['category'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span>•</span>
                                    <span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">calendar_today</span><?php echo date('d M Y', strtotime($featured_article['created_at'])); ?></span>
                                </div>
                                <h3 class="font-geist font-bold text-xl md:text-2xl text-slate-900 hover:text-blue-600 transition-colors cursor-pointer line-clamp-2 leading-snug" onclick="openDetailModal(<?php echo htmlspecialchars(json_encode($featured_article), ENT_QUOTES, 'UTF-8'); ?>)">
                                    <?php echo htmlspecialchars($featured_article['title'], ENT_QUOTES, 'UTF-8'); ?>
                                </h3>
                                <p class="text-slate-600 text-xs md:text-sm mt-3 line-clamp-3 leading-relaxed">
                                    <?php echo htmlspecialchars($featured_article['excerpt'], ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>

                            <div class="flex items-center justify-between mt-6 pt-4 border-t border-slate-100">
                                <div class="flex items-center gap-1.5 text-xs text-slate-700 font-semibold">
                                    <span class="material-symbols-outlined text-blue-600 text-base">person</span>
                                    <span><?php echo htmlspecialchars($featured_article['author'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button onclick="openDetailModal(<?php echo htmlspecialchars(json_encode($featured_article), ENT_QUOTES, 'UTF-8'); ?>)" class="flex items-center gap-1 text-blue-600 font-bold text-xs hover:underline cursor-pointer">
                                        <span>Baca Selengkapnya</span>
                                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                    </button>

                                    <?php if ($is_admin): ?>
                                        <div class="flex items-center gap-1 ml-2 border-l border-slate-200 pl-2">
                                            <button onclick="openFormModal(<?php echo htmlspecialchars(json_encode($featured_article), ENT_QUOTES, 'UTF-8'); ?>)" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit Artikel">
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </button>
                                            <button onclick="confirmDelete(<?php echo intval($featured_article['id']); ?>, '<?php echo htmlspecialchars(addslashes($featured_article['title']), ENT_QUOTES, 'UTF-8'); ?>')" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Artikel">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Articles Grid Section -->
            <div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 border-b border-slate-200 pb-4">
                    <div>
                        <h3 class="font-geist font-bold text-xl text-slate-900">Semua Aktivitas & Artikel</h3>
                        <span id="article-count" class="text-xs font-semibold text-slate-500"><?php echo count($articles); ?> Aktivitas Ditemukan</span>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <!-- Global Search Bar -->
                        <div class="relative w-full sm:w-64">
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-sm">search</span>
                            <input id="article-search" oninput="filterArticles()" class="w-full pl-9 pr-4 py-2 rounded-xl bg-white border border-slate-200 focus:border-blue-600 focus:outline-none text-xs transition-colors" placeholder="Cari aktivitas atau artikel..." type="text"/>
                        </div>

                        <?php if ($is_admin): ?>
                            <!-- Admin Add Article Button -->
                            <button onclick="openFormModal()" class="shrink-0 flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95">
                                <span class="material-symbols-outlined text-sm">add_circle</span>
                                <span>Tambah Artikel</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="articles-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php if (empty($articles)): ?>
                        <div class="col-span-full py-16 text-center flex flex-col items-center justify-center bg-white rounded-2xl border border-dashed border-slate-200 p-8">
                            <span class="material-symbols-outlined text-5xl text-slate-300 mb-2">article_off</span>
                            <p class="font-geist font-bold text-slate-700 text-base">Belum ada artikel aktivitas</p>
                            <p class="text-xs text-slate-500 mt-1">Super Admin dapat menambahkan artikel aktivitas anak magang baru dengan menekan tombol diatas.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($articles as $item): ?>
                            <div class="article-item article-card bg-white rounded-2xl border border-slate-200 overflow-hidden flex flex-col justify-between shadow-sm hover:shadow-md transition-all group" data-category="<?php echo htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8'); ?>" data-title="<?php echo htmlspecialchars(strtolower($item['title']), ENT_QUOTES, 'UTF-8'); ?>">
                                <div>
                                    <!-- Card Header Image -->
                                    <div class="relative h-48 w-full overflow-hidden bg-slate-100 cursor-pointer" onclick="openDetailModal(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>)">
                                        <img src="<?php echo htmlspecialchars($item['image_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"/>
                                        <span class="absolute top-3 left-3 bg-black/60 backdrop-blur-md text-white px-3 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                            <?php echo htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <?php if (!empty($item['is_featured'])): ?>
                                            <span class="absolute top-3 right-3 bg-amber-500 text-white px-2.5 py-0.5 rounded-full text-[10px] font-bold flex items-center gap-1 shadow-sm">
                                                <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">star</span>
                                                Featured
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Card Content Body -->
                                    <div class="p-5 flex flex-col gap-2">
                                        <div class="flex items-center gap-2 text-slate-500 text-xs font-medium">
                                            <span class="material-symbols-outlined text-xs text-blue-600">calendar_today</span>
                                            <span><?php echo date('d M Y', strtotime($item['created_at'])); ?></span>
                                            <span>•</span>
                                            <span class="material-symbols-outlined text-xs text-blue-600">person</span>
                                            <span class="truncate max-w-[120px] font-semibold text-slate-700"><?php echo htmlspecialchars($item['author'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>

                                        <h4 class="font-geist font-bold text-base text-slate-900 hover:text-blue-600 transition-colors cursor-pointer line-clamp-2 leading-snug" onclick="openDetailModal(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>)">
                                            <?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>
                                        </h4>

                                        <p class="text-slate-600 text-xs line-clamp-3 leading-relaxed">
                                            <?php echo htmlspecialchars($item['excerpt'], ENT_QUOTES, 'UTF-8'); ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Card Footer Actions -->
                                <div class="p-5 pt-0 border-t border-slate-100 mt-3 pt-3 flex items-center justify-between">
                                    <button onclick="openDetailModal(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>)" class="text-blue-600 font-bold text-xs hover:underline flex items-center gap-1 cursor-pointer">
                                        <span>Selengkapnya</span>
                                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                    </button>

                                    <?php if ($is_admin): ?>
                                        <div class="flex items-center gap-1">
                                            <button onclick="openFormModal(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>)" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit Artikel">
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </button>
                                            <button onclick="confirmDelete(<?php echo intval($item['id']); ?>, '<?php echo htmlspecialchars(addslashes($item['title']), ENT_QUOTES, 'UTF-8'); ?>')" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Artikel">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <div class="shrink-0 mt-auto w-full">
            <?php include 'partials/footer.php'; ?>
        </div>
    </main>

    <!-- Modal View Detail Artikel -->
    <div id="detail-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-md overflow-y-auto">
        <div class="bg-surface-container-lowest w-full max-w-3xl rounded-2xl shadow-2xl overflow-hidden my-8 flex flex-col max-h-[90vh]">
            <!-- Modal Header Image -->
            <div class="relative h-64 sm:h-80 w-full overflow-hidden bg-surface-container-high">
                <img id="detail-image" src="" alt="" class="w-full h-full object-cover"/>
                <button onclick="closeDetailModal()" class="absolute top-md right-md bg-black/50 hover:bg-black/80 text-white rounded-full p-xs backdrop-blur-md transition-colors cursor-pointer">
                    <span class="material-symbols-outlined">close</span>
                </button>
                <div class="absolute bottom-md left-md right-md flex items-center justify-between gap-sm">
                    <span id="detail-category" class="glass-badge px-md py-xs rounded-full font-label-md text-primary font-bold shadow-md"></span>
                    <span id="detail-date" class="bg-black/60 text-white px-md py-xs rounded-full font-label-sm backdrop-blur-md"></span>
                </div>
            </div>

            <!-- Modal Content Body -->
            <div class="p-md sm:p-xl overflow-y-auto flex-1">
                <h2 id="detail-title" class="font-headline-lg font-bold text-on-surface mb-sm"></h2>
                
                <div class="flex items-center gap-md pb-md mb-md border-b border-outline-variant text-on-surface-variant font-label-md">
                    <div class="flex items-center gap-xs">
                        <span class="material-symbols-outlined text-primary">account_circle</span>
                        <span id="detail-author" class="font-semibold text-on-surface"></span>
                    </div>
                </div>

                <div id="detail-content" class="prose max-w-none text-on-surface font-body-md space-y-md leading-relaxed"></div>
            </div>

            <!-- Modal Footer -->
            <div class="p-md bg-surface-container-low border-t border-outline-variant flex justify-end">
                <button onclick="closeDetailModal()" class="px-xl py-sm bg-surface-container-high hover:bg-outline-variant/50 text-on-surface font-label-md rounded-full transition-colors cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <?php if ($is_admin): ?>
    <!-- Modal Add/Edit Article Form (Super Admin Only) -->
    <div id="form-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-md overflow-y-auto">
        <div class="bg-surface-container-lowest w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden my-8">
            <form action="article.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save"/>
                <input type="hidden" id="form-id" name="id" value=""/>

                <div class="p-md sm:p-lg border-b border-outline-variant flex items-center justify-between bg-surface-container-low">
                    <div class="flex items-center gap-xs">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">edit_document</span>
                        <h3 id="form-modal-title" class="font-headline-md font-bold text-on-surface">Tambah Artikel Aktivitas</h3>
                    </div>
                    <button type="button" onclick="closeFormModal()" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="p-md sm:p-lg space-y-md max-h-[75vh] overflow-y-auto">
                    <!-- Title -->
                    <div>
                        <label class="block font-label-md font-semibold text-on-surface mb-xs">Judul Artikel Aktivitas *</label>
                        <input type="text" id="form-title" name="title" required placeholder="Contoh: Workshop Laravel 11 & API Security" class="w-full px-md py-sm rounded-lg border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary-fixed focus:outline-none font-body-md text-body-md"/>
                    </div>

                    <!-- Category & Author Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
                        <div>
                            <label class="block font-label-md font-semibold text-on-surface mb-xs">Kategori Aktivitas *</label>
                            <select id="form-category" name="category" required class="w-full px-md py-sm rounded-lg border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary-fixed focus:outline-none font-body-md text-body-md bg-surface-container-lowest">
                                <option value="Aktivitas Harian">Aktivitas Harian</option>
                                <option value="Project & Coding">Project & Coding</option>
                                <option value="Workshop & Mentoring">Workshop & Mentoring</option>
                                <option value="Prestasi">Prestasi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-label-md font-semibold text-on-surface mb-xs">Penulis / Pembuat *</label>
                            <input type="text" id="form-author" name="author" required value="<?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-md py-sm rounded-lg border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary-fixed focus:outline-none font-body-md text-body-md"/>
                        </div>
                    </div>

                    <!-- Image Upload or URL -->
                    <div class="space-y-xs">
                        <label class="block font-label-md font-semibold text-on-surface">Gambar Sampul (Image Cover)</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
                            <div>
                                <span class="block font-label-sm text-on-surface-variant mb-1">URL Gambar Online:</span>
                                <input type="url" id="form-image-url" name="image_url" placeholder="https://images.unsplash.com/..." class="w-full px-md py-sm rounded-lg border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary-fixed focus:outline-none font-body-sm text-body-sm"/>
                            </div>
                            <div>
                                <span class="block font-label-sm text-on-surface-variant mb-1">Atau Unggah File Gambar:</span>
                                <input type="file" id="form-image-file" name="image_file" accept="image/*" class="w-full px-xs py-1 rounded-lg border border-outline-variant text-body-sm bg-surface-container-lowest cursor-pointer"/>
                            </div>
                        </div>
                    </div>

                    <!-- Excerpt / Ringkasan -->
                    <div>
                        <label class="block font-label-md font-semibold text-on-surface mb-xs">Ringkasan Artikel (Excerpt) *</label>
                        <textarea id="form-excerpt" name="excerpt" rows="2" required placeholder="Tuliskan 2-3 kalimat ringkasan singkat kegiatan ini..." class="w-full px-md py-sm rounded-lg border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary-fixed focus:outline-none font-body-md text-body-md"></textarea>
                    </div>

                    <!-- Content -->
                    <div>
                        <label class="block font-label-md font-semibold text-on-surface mb-xs">Isi Lengkap Artikel *</label>
                        <textarea id="form-content" name="content" rows="6" required placeholder="Tuliskan detail aktivitas lengkap (dukung format paragraf HTML atau teks biasa)..." class="w-full px-md py-sm rounded-lg border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary-fixed focus:outline-none font-body-md text-body-md"></textarea>
                    </div>

                    <!-- Is Featured Checkbox -->
                    <div class="flex items-center gap-xs pt-xs">
                        <input type="checkbox" id="form-is-featured" name="is_featured" value="1" class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary"/>
                        <label for="form-is-featured" class="font-label-md text-on-surface font-medium cursor-pointer">Sematkan sebagai Artikel Utama (Featured Hero Banner)</label>
                    </div>
                </div>

                <div class="p-md bg-surface-container-low border-t border-outline-variant flex items-center justify-end gap-sm">
                    <button type="button" onclick="closeFormModal()" class="px-lg py-sm bg-surface-container-high hover:bg-outline-variant/40 text-on-surface font-label-md rounded-full transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-xl py-sm bg-primary text-on-primary hover:bg-primary-container hover:text-on-primary-container font-label-md rounded-full shadow-sm hover:shadow-md transition-all cursor-pointer">
                        Simpan & Publikasikan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirm Delete Modal (Super Admin Only) -->
    <div id="delete-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-md">
        <div class="bg-surface-container-lowest w-full max-w-md rounded-2xl shadow-2xl overflow-hidden p-lg flex flex-col gap-md">
            <div class="flex items-center gap-sm text-error">
                <span class="material-symbols-outlined text-3xl">warning</span>
                <h3 class="font-headline-md font-bold">Konfirmasi Hapus</h3>
            </div>
            <p class="font-body-md text-on-surface-variant">Apakah Anda yakin ingin menghapus artikel <strong id="delete-article-title" class="text-on-surface"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
            <form action="article.php" method="POST" class="flex justify-end gap-sm mt-sm">
                <input type="hidden" name="action" value="delete"/>
                <input type="hidden" id="delete-article-id" name="id" value=""/>
                <button type="button" onclick="closeDeleteModal()" class="px-lg py-sm bg-surface-container-high text-on-surface font-label-md rounded-full cursor-pointer">Batal</button>
                <button type="submit" class="px-lg py-sm bg-error text-on-error hover:bg-red-700 font-label-md rounded-full cursor-pointer shadow-sm">Hapus Artikel</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- JavaScript Handlers -->
    <script>
        // Category & Search filtering
        let activeCategory = 'all';

        function filterCategory(category, element) {
            activeCategory = category;
            document.querySelectorAll('.category-chip').forEach(chip => {
                chip.classList.remove('active', 'bg-blue-600', 'text-white', 'shadow-sm', 'shadow-md', 'font-bold', 'border-transparent');
                chip.classList.add('bg-white', 'text-slate-700', 'hover:bg-slate-100', 'hover:text-slate-900', 'border', 'border-slate-200/80', 'font-semibold', 'shadow-xs');
            });
            if (element) {
                element.classList.remove('bg-white', 'text-slate-700', 'hover:bg-slate-100', 'hover:text-slate-900', 'border-slate-200/80', 'font-semibold', 'shadow-xs');
                element.classList.add('active', 'bg-blue-600', 'text-white', 'shadow-sm', 'font-bold', 'border-transparent');
            }
            applyFilters();
        }

        function filterArticles() {
            applyFilters();
        }

        function applyFilters() {
            const query = (document.getElementById('article-search')?.value || '').toLowerCase().trim();
            const items = document.querySelectorAll('.article-item');
            let visibleCount = 0;

            items.forEach(item => {
                const itemCat = item.getAttribute('data-category');
                const title = (item.getAttribute('data-title') || '').toLowerCase();

                const matchCategory = (activeCategory === 'all' || itemCat === activeCategory);
                const matchSearch = (!query || title.includes(query));

                if (matchCategory && matchSearch) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            const countEl = document.getElementById('article-count');
            if (countEl) {
                countEl.innerText = visibleCount + ' Aktivitas Ditemukan';
            }
        }

        // Modal Detail View
        function openDetailModal(data) {
            document.getElementById('detail-image').src = data.image_url;
            document.getElementById('detail-category').innerText = data.category;
            document.getElementById('detail-date').innerText = new Date(data.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            document.getElementById('detail-title').innerText = data.title;
            document.getElementById('detail-author').innerText = data.author;
            
            // Format HTML or line breaks
            let contentHtml = data.content;
            if (!contentHtml.includes('<p>')) {
                contentHtml = data.content.split('\n\n').map(p => `<p>${p.replace(/\n/g, '<br>')}</p>`).join('');
            }
            document.getElementById('detail-content').innerHTML = contentHtml;

            document.getElementById('detail-modal').classList.remove('hidden');
        }

        function closeDetailModal() {
            document.getElementById('detail-modal').classList.add('hidden');
        }

        <?php if ($is_admin): ?>
        // Form Modal Handlers for Super Admin
        function openFormModal(data = null) {
            if (data) {
                document.getElementById('form-modal-title').innerText = 'Edit Artikel Aktivitas';
                document.getElementById('form-id').value = data.id || '';
                document.getElementById('form-title').value = data.title || '';
                document.getElementById('form-category').value = data.category || 'Aktivitas Harian';
                document.getElementById('form-author').value = data.author || '';
                document.getElementById('form-image-url').value = data.image_url || '';
                document.getElementById('form-excerpt').value = data.excerpt || '';
                document.getElementById('form-content').value = data.content || '';
                document.getElementById('form-is-featured').checked = (data.is_featured == 1);
            } else {
                document.getElementById('form-modal-title').innerText = 'Tambah Artikel Aktivitas';
                document.getElementById('form-id').value = '';
                document.getElementById('form-title').value = '';
                document.getElementById('form-category').value = 'Aktivitas Harian';
                document.getElementById('form-author').value = '<?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, "UTF-8"); ?>';
                document.getElementById('form-image-url').value = '';
                document.getElementById('form-image-file').value = '';
                document.getElementById('form-excerpt').value = '';
                document.getElementById('form-content').value = '';
                document.getElementById('form-is-featured').checked = false;
            }
            document.getElementById('form-modal').classList.remove('hidden');
        }

        function closeFormModal() {
            document.getElementById('form-modal').classList.add('hidden');
        }

        function confirmDelete(id, title) {
            document.getElementById('delete-article-id').value = id;
            document.getElementById('delete-article-title').innerText = title;
            document.getElementById('delete-modal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.add('hidden');
        }
        <?php endif; ?>

        // Keyboard navigation (Escape to close modals)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDetailModal();
                <?php if ($is_admin): ?>
                closeFormModal();
                closeDeleteModal();
                <?php endif; ?>
            }
        });
    </script>
    <?php if (!$is_logged_in): ?>
    <script>
        (function() {
            if (!window.anime) return;
            const floats = document.querySelectorAll('.batik-float');
            if (!floats.length) return;
            const configs = [
                { x: 28, y: 22, r: 8,  dur: 9000  },
                { x: -18, y: 30, r: -6, dur: 11500 },
                { x: 22, y: -18, r: 5,  dur: 8500  },
                { x: -30, y: 20, r: -9, dur: 13000 },
                { x: 25, y: 25, r: 7,   dur: 10000 },
                { x: -20, y: -22, r: -5,dur: 7800  },
                { x: 18, y: 28, r: 10,  dur: 12500 },
                { x: -24, y: -16, r: -7,dur: 9800  }
            ];
            floats.forEach((el, i) => {
                const cfg = configs[i % configs.length];
                const baseRotate = parseFloat(el.style.transform.match(/rotate\(([^)]+)deg\)/)?.[1] || 0);
                anime({
                    targets: el,
                    translateX: [
                        { value:  cfg.x,      duration: cfg.dur * 0.5, easing: 'easeInOutSine' },
                        { value: -cfg.x * 0.6,duration: cfg.dur * 0.5, easing: 'easeInOutSine' }
                    ],
                    translateY: [
                        { value:  cfg.y,      duration: cfg.dur * 0.6, easing: 'easeInOutSine' },
                        { value: -cfg.y * 0.7,duration: cfg.dur * 0.4, easing: 'easeInOutSine' }
                    ],
                    rotate: [
                        { value: baseRotate + cfg.r,       duration: cfg.dur * 0.55, easing: 'easeInOutSine' },
                        { value: baseRotate - cfg.r * 0.5, duration: cfg.dur * 0.45, easing: 'easeInOutSine' }
                    ],
                    loop: true,
                    direction: 'alternate'
                });
            });
        })();
    </script>
    <?php endif; ?>
</body>
</html>
