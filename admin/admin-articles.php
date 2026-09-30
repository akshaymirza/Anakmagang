<?php
require_once __DIR__ . '/../session.php';
require_admin();

// Database connection attempt
$conn = null;
if (file_exists(__DIR__ . '/../Login/koneksi.php')) {
    include_once __DIR__ . '/../Login/koneksi.php';
}

$json_file = __DIR__ . '/../uploads/articles.json';
$upload_dir = __DIR__ . '/../uploads/articles/';

if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0777, true);
}

// Function to auto-create MySQL table if DB is available
function init_admin_articles_db($conn) {
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

// Sample seed data if no articles exist
function get_seed_admin_articles() {
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
        ]
    ];
}

// Fetch articles
function fetch_admin_articles($conn, $json_file) {
    if ($conn) {
        init_admin_articles_db($conn);
        $res = @mysqli_query($conn, "SELECT * FROM articles ORDER BY created_at DESC");
        if ($res && mysqli_num_rows($res) > 0) {
            $articles = [];
            while ($row = mysqli_fetch_assoc($res)) {
                $articles[] = $row;
            }
            return $articles;
        }
    }
    
    if (!file_exists($json_file)) {
        $seeds = get_seed_admin_articles();
        file_put_contents($json_file, json_encode($seeds, JSON_PRETTY_PRINT));
        return $seeds;
    }

    $json_data = json_decode(file_get_contents($json_file), true);
    if (!is_array($json_data) || empty($json_data)) {
        $seeds = get_seed_admin_articles();
        file_put_contents($json_file, json_encode($seeds, JSON_PRETTY_PRINT));
        return $seeds;
    }

    usort($json_data, function($a, $b) {
        return strtotime($b['created_at'] ?? 0) <=> strtotime($a['created_at'] ?? 0);
    });

    return $json_data;
}

// Save/Update Article
function save_admin_article($conn, $json_file, $data) {
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
        init_admin_articles_db($conn);
        if ($id) {
            $stmt = mysqli_prepare($conn, "UPDATE articles SET title=?, category=?, author=?, excerpt=?, content=?, image_url=?, is_featured=? WHERE id=?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssssssii", $title, $category, $author, $excerpt, $content, $image_url, $is_featured, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO articles (title, category, author, excerpt, content, image_url, is_featured, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssssssi", $title, $category, $author, $excerpt, $content, $image_url, $is_featured);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }

    // JSON Sync
    $articles = fetch_admin_articles(null, $json_file);
    if ($id) {
        foreach ($articles as &$art) {
            if ($art['id'] == $id) {
                $art['title'] = $title;
                $art['category'] = $category;
                $art['author'] = $author;
                $art['excerpt'] = $excerpt;
                $art['content'] = $content;
                $art['image_url'] = $image_url;
                $art['is_featured'] = $is_featured;
                $art['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
    } else {
        $max_id = 0;
        foreach ($articles as $art) {
            if ($art['id'] > $max_id) $max_id = $art['id'];
        }
        $new_art = [
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
        array_unshift($articles, $new_art);
    }
    file_put_contents($json_file, json_encode($articles, JSON_PRETTY_PRINT));
}

// Delete Article
function delete_admin_article($conn, $json_file, $id) {
    $id = intval($id);
    if ($conn) {
        init_admin_articles_db($conn);
        $stmt = mysqli_prepare($conn, "DELETE FROM articles WHERE id=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    $articles = fetch_admin_articles(null, $json_file);
    $articles = array_values(array_filter($articles, function($art) use ($id) {
        return $art['id'] != $id;
    }));
    file_put_contents($json_file, json_encode($articles, JSON_PRETTY_PRINT));
}

// Handle POST actions
$msg = '';
$msg_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    $image_url = $_POST['image_url'] ?? '';
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image_file']['tmp_name'];
        $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '', $_FILES['image_file']['name']);
        if (move_uploaded_file($tmp_name, $upload_dir . $file_name)) {
            $image_url = 'uploads/articles/' . $file_name;
        }
    }

    if ($action === 'save') {
        save_admin_article($conn, $json_file, [
            'id' => $_POST['id'] ?? '',
            'title' => $_POST['title'] ?? '',
            'category' => $_POST['category'] ?? 'Aktivitas Harian',
            'author' => $_POST['author'] ?? current_user_name(),
            'excerpt' => $_POST['excerpt'] ?? '',
            'content' => $_POST['content'] ?? '',
            'image_url' => $image_url,
            'is_featured' => $_POST['is_featured'] ?? 0
        ]);
        header('Location: admin-articles.php?status=saved');
        exit;
    } elseif ($action === 'delete') {
        delete_admin_article($conn, $json_file, $_POST['id'] ?? 0);
        header('Location: admin-articles.php?status=deleted');
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

$articles = fetch_admin_articles($conn, $json_file);
$total_articles = count($articles);
$featured_count = count(array_filter($articles, function($art) { return !empty($art['is_featured']); }));
$categories = array_unique(array_column($articles, 'category'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <title>Kedayweb Admin - Kelola Artikel & Aktivitas</title>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="../shared-config.js"></script>
    <link rel="stylesheet" href="../style.css"/>
    <style>
        .glass-card { background: rgba(255,255,255,0.85); backdrop-filter: blur(12px); }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-background text-on-surface font-body-md flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <?php $active = 'article'; include '../partials/sidebar-admin.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col md:ml-[16.5rem] h-screen overflow-y-auto">
        <!-- Top Header -->
        <header class="w-full h-20 bg-surface-container-lowest border-b border-outline-variant sticky top-0 flex justify-between items-center px-6 z-10 shrink-0">
            <h2 class="font-headline-lg font-bold text-on-surface flex items-center gap-2">
                <button onclick="toggleMobileSidebar()" class="md:hidden text-on-surface hover:text-primary focus:outline-none flex items-center mr-1 p-1 rounded-lg hover:bg-surface-container-high" aria-label="Toggle Sidebar">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">newspaper</span>
                <span>Kelola Artikel & Aktivitas</span>
            </h2>
            <div class="flex items-center gap-3">
                <button onclick="openFormModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary rounded-xl font-label-md hover:bg-primary-container hover:text-on-primary-container transition-all shadow-sm active:scale-95 cursor-pointer">
                    <span class="material-symbols-outlined">add_circle</span>
                    <span>Tambah Artikel Baru</span>
                </button>
                <div class="flex items-center gap-sm p-1.5 px-3 rounded-full border border-outline-variant bg-surface-bright shadow-2xs">
                    <div class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container overflow-hidden shrink-0">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">account_circle</span>
                    </div>
                    <span class="hidden sm:inline-block font-label-md"><?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="../logout.php" class="text-error hover:text-red-700 hover:bg-red-50 p-1.5 rounded-full transition-colors flex items-center justify-center" title="Keluar" aria-label="Keluar"><span class="material-symbols-outlined text-[20px]">logout</span></a>
                </div>
            </div>
        </header>

        <div class="p-6 flex flex-col gap-6 flex-1">

            <!-- Alert Notification -->
            <?php if (!empty($msg)): ?>
                <div class="flex items-center justify-between p-4 rounded-xl <?php echo $msg_type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'; ?> shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined"><?php echo $msg_type === 'success' ? 'check_circle' : 'info'; ?></span>
                        <span class="font-label-md"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Stats Row -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="glass-card rounded-xl border border-outline-variant p-4 flex items-center gap-4 shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">newspaper</span>
                    </div>
                    <div>
                        <p class="text-sm text-on-surface-variant">Total Artikel</p>
                        <p class="text-2xl font-bold text-on-surface"><?php echo $total_articles; ?></p>
                    </div>
                </div>

                <div class="glass-card rounded-xl border border-outline-variant p-4 flex items-center gap-4 shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-700">
                        <span class="material-symbols-outlined">star</span>
                    </div>
                    <div>
                        <p class="text-sm text-on-surface-variant">Artikel Unggulan (Featured)</p>
                        <p class="text-2xl font-bold text-on-surface"><?php echo $featured_count; ?></p>
                    </div>
                </div>

                <div class="glass-card rounded-xl border border-outline-variant p-4 flex items-center gap-4 shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container">
                        <span class="material-symbols-outlined">category</span>
                    </div>
                    <div>
                        <p class="text-sm text-on-surface-variant">Kategori Aktif</p>
                        <p class="text-2xl font-bold text-on-surface"><?php echo count($categories); ?></p>
                    </div>
                </div>
            </div>

            <!-- Articles Management Table Card -->
            <div class="glass-card rounded-xl border border-outline-variant p-5 shadow-sm">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                    <div>
                        <h3 class="font-headline-md font-bold text-on-surface">Daftar Artikel & Kegiatan</h3>
                        <p class="text-sm text-on-surface-variant">Kelola publikasi berita, dokumentasi workshop, dan jurnal harian magang.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                        <!-- Category Filter -->
                        <select id="filter-category" onchange="filterAdminArticles()" class="px-3 py-1.5 text-sm border border-outline-variant rounded-lg focus:outline-none focus:border-primary bg-surface-container-lowest">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Search Bar -->
                        <div class="relative flex-1 md:w-64">
                            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
                            <input type="text" id="search-article" oninput="filterAdminArticles()" placeholder="Cari artikel..." class="w-full pl-9 pr-3 py-1.5 text-sm border border-outline-variant rounded-lg focus:outline-none focus:border-primary bg-surface-container-lowest"/>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-outline-variant text-on-surface-variant bg-surface-container-low">
                                <th class="py-3 px-4 font-semibold">Cover</th>
                                <th class="py-3 px-4 font-semibold">Judul Artikel</th>
                                <th class="py-3 px-4 font-semibold">Kategori</th>
                                <th class="py-3 px-4 font-semibold">Penulis</th>
                                <th class="py-3 px-4 font-semibold">Status</th>
                                <th class="py-3 px-4 font-semibold">Tanggal</th>
                                <th class="py-3 px-4 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="articles-table-body" class="divide-y divide-outline-variant">
                            <?php if (empty($articles)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-on-surface-variant">Belum ada artikel yang dipublikasikan.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($articles as $art): ?>
                                    <tr class="article-row hover:bg-surface-container-low transition-colors" data-title="<?php echo htmlspecialchars(strtolower($art['title']), ENT_QUOTES, 'UTF-8'); ?>" data-category="<?php echo htmlspecialchars($art['category'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <td class="py-3 px-4">
                                            <img src="<?php echo htmlspecialchars($art['image_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="Cover" class="w-12 h-12 rounded-lg object-cover border border-outline-variant"/>
                                        </td>
                                        <td class="py-3 px-4 font-medium text-on-surface max-w-xs">
                                            <div class="line-clamp-2 font-semibold"><?php echo htmlspecialchars($art['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="text-xs text-on-surface-variant line-clamp-1 mt-0.5"><?php echo htmlspecialchars($art['excerpt'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="inline-block px-2.5 py-1 text-xs rounded-full bg-secondary-container text-on-secondary-container font-medium">
                                                <?php echo htmlspecialchars($art['category'], ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-on-surface-variant font-medium">
                                            <?php echo htmlspecialchars($art['author'], ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td class="py-3 px-4">
                                            <?php if (!empty($art['is_featured'])): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-amber-100 text-amber-800 font-bold">
                                                    <span class="material-symbols-outlined text-xs">star</span> Featured
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-block px-2.5 py-1 text-xs rounded-full bg-slate-100 text-slate-600 font-medium">
                                                    Standard
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4 text-xs text-on-surface-variant whitespace-nowrap">
                                            <?php echo date('d M Y', strtotime($art['created_at'])); ?>
                                        </td>
                                        <td class="py-3 px-4 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-2">
                                                <!-- View Preview Button -->
                                                <button onclick="openDetailModal(<?php echo htmlspecialchars(json_encode($art, JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8'); ?>)" class="p-1.5 text-primary hover:bg-primary-container rounded-lg transition-colors" title="Lihat Artikel">
                                                    <span class="material-symbols-outlined text-lg">visibility</span>
                                                </button>
                                                <!-- Edit Button -->
                                                <button onclick="openFormModal(<?php echo htmlspecialchars(json_encode($art, JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8'); ?>)" class="p-1.5 text-amber-700 hover:bg-amber-100 rounded-lg transition-colors" title="Edit Artikel">
                                                    <span class="material-symbols-outlined text-lg">edit</span>
                                                </button>
                                                <!-- Delete Button -->
                                                <button onclick="confirmDelete(<?php echo (int)$art['id']; ?>, '<?php echo htmlspecialchars(addslashes($art['title']), ENT_QUOTES, 'UTF-8'); ?>')" class="p-1.5 text-error hover:bg-red-100 rounded-lg transition-colors" title="Hapus Artikel">
                                                    <span class="material-symbols-outlined text-lg">delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <p id="no-articles-msg" class="hidden text-center py-8 text-on-surface-variant">Tidak ada artikel yang cocok dengan pencarian.</p>
                </div>
            </div>
        </div>
        <div class="mt-auto shrink-0 w-full">
            <?php include '../partials/footer.php'; ?>
        </div>
    </main>

    <!-- Modal Form (Tambah / Edit Artikel) -->
    <div id="form-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
        <div class="bg-surface-container-lowest rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 shadow-2xl border border-outline-variant">
            <div class="flex justify-between items-center pb-4 border-b border-outline-variant mb-4">
                <h3 id="form-modal-title" class="font-headline-md font-bold text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined">article</span>
                    <span>Tambah Artikel Baru</span>
                </h3>
                <button onclick="closeFormModal()" class="text-on-surface-variant hover:text-on-surface p-1 rounded-full hover:bg-surface-container-high">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="admin-articles.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" value="save"/>
                <input type="hidden" id="form-id" name="id" value=""/>

                <div>
                    <label class="block font-label-md text-on-surface font-semibold mb-1">Judul Artikel <span class="text-error">*</span></label>
                    <input type="text" id="form-title" name="title" required placeholder="Contoh: Showcase Sprint 1 Magang Batch 2026" class="w-full px-4 py-2 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary-fixed bg-surface-container-lowest"/>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-label-md text-on-surface font-semibold mb-1">Kategori <span class="text-error">*</span></label>
                        <select id="form-category" name="category" required class="w-full px-4 py-2 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary-fixed bg-surface-container-lowest">
                            <option value="Aktivitas Harian">Aktivitas Harian</option>
                            <option value="Workshop & Mentoring">Workshop & Mentoring</option>
                            <option value="Project & Coding">Project & Coding</option>
                            <option value="Prestasi">Prestasi</option>
                            <option value="Pengumuman">Pengumuman</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-label-md text-on-surface font-semibold mb-1">Penulis / Author <span class="text-error">*</span></label>
                        <input type="text" id="form-author" name="author" required placeholder="Nama Penulis" value="<?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-4 py-2 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary-fixed bg-surface-container-lowest"/>
                    </div>
                </div>

                <div>
                    <label class="block font-label-md text-on-surface font-semibold mb-1">Gambar Cover (File Upload atau URL)</label>
                    <div class="space-y-2">
                        <input type="file" id="form-image-file" name="image_file" accept="image/*" class="w-full text-sm text-on-surface-variant file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-container file:text-on-primary-container hover:file:bg-primary"/>
                        <p class="text-xs text-on-surface-variant font-medium">Atau masukkan URL Gambar eksternal:</p>
                        <input type="url" id="form-image-url" name="image_url" placeholder="https://images.unsplash.com/..." class="w-full px-4 py-2 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary-fixed bg-surface-container-lowest text-sm"/>
                    </div>
                </div>

                <div>
                    <label class="block font-label-md text-on-surface font-semibold mb-1">Ringkasan Singkat (Excerpt)</label>
                    <textarea id="form-excerpt" name="excerpt" rows="2" placeholder="Tuliskan ringkasan 1-2 kalimat..." class="w-full px-4 py-2 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary-fixed bg-surface-container-lowest text-sm"></textarea>
                </div>

                <div>
                    <label class="block font-label-md text-on-surface font-semibold mb-1">Isi Konten Artikel <span class="text-error">*</span></label>
                    <textarea id="form-content" name="content" rows="6" required placeholder="Tulis konten lengkap (mendukung tag HTML <p>, <b>, <ul>, dsb)..." class="w-full px-4 py-2 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary-fixed bg-surface-container-lowest text-sm font-mono"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="form-is-featured" name="is_featured" value="1" class="w-4 h-4 text-primary rounded border-outline-variant focus:ring-primary"/>
                    <label for="form-is-featured" class="font-label-md text-on-surface font-semibold cursor-pointer">Tandai sebagai Artikel Unggulan (Featured)</label>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant">
                    <button type="button" onclick="closeFormModal()" class="px-5 py-2.5 rounded-xl border border-outline-variant font-label-md hover:bg-surface-container-high transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary text-on-primary font-label-md hover:bg-primary-container transition-colors shadow-sm">Simpan Artikel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus Confirmation -->
    <div id="delete-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
        <div class="bg-surface-container-lowest rounded-2xl max-w-md w-full p-6 shadow-2xl border border-outline-variant text-center">
            <div class="w-16 h-16 rounded-full bg-red-100 text-error flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-3xl">warning</span>
            </div>
            <h3 class="font-headline-md font-bold text-on-surface mb-2">Hapus Artikel Ini?</h3>
            <p class="text-sm text-on-surface-variant mb-6">Artikel "<span id="delete-article-title" class="font-semibold text-on-surface"></span>" akan dihapus secara permanen.</p>

            <form action="admin-articles.php" method="POST" class="flex justify-center gap-3">
                <input type="hidden" name="action" value="delete"/>
                <input type="hidden" id="delete-article-id" name="id" value=""/>
                <button type="button" onclick="closeDeleteModal()" class="px-5 py-2 rounded-xl border border-outline-variant font-label-md hover:bg-surface-container-high">Batal</button>
                <button type="submit" class="px-6 py-2 rounded-xl bg-error text-on-error font-label-md hover:bg-red-700 shadow-sm">Ya, Hapus</button>
            </form>
        </div>
    </div>

    <!-- Modal Detail / Read Artikel -->
    <div id="detail-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 hidden backdrop-blur-md">
        <div class="bg-surface-container-lowest rounded-3xl max-w-3xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-outline-variant overflow-hidden relative">
            <button onclick="closeDetailModal()" class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-black/40 text-white flex items-center justify-center hover:bg-black/70 backdrop-blur-sm transition-all">
                <span class="material-symbols-outlined">close</span>
            </button>

            <div class="relative h-64 sm:h-80 w-full overflow-hidden">
                <img id="detail-image" src="" alt="Cover" class="w-full h-full object-cover"/>
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="absolute bottom-6 left-6 right-6 text-white">
                    <span id="detail-category" class="inline-block px-3 py-1 rounded-full bg-primary text-on-primary font-label-sm font-bold mb-3"></span>
                    <h2 id="detail-title" class="font-headline-lg font-extrabold leading-tight"></h2>
                </div>
            </div>

            <div class="p-6 sm:p-8 space-y-6">
                <div class="flex items-center gap-4 text-sm text-on-surface-variant border-b border-outline-variant pb-4">
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base">person</span>
                        <span id="detail-author" class="font-medium text-on-surface"></span>
                    </div>
                    <span>•</span>
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base">calendar_today</span>
                        <span id="detail-date"></span>
                    </div>
                </div>

                <div id="detail-content" class="prose max-w-none text-on-surface leading-relaxed font-body-md"></div>
            </div>
        </div>
    </div>

    <script>
        function filterAdminArticles() {
            const searchVal = document.getElementById('search-article').value.toLowerCase().trim();
            const catVal = document.getElementById('filter-category').value;
            const rows = document.querySelectorAll('.article-row');
            let count = 0;

            rows.forEach(row => {
                const title = row.getAttribute('data-title') || '';
                const cat = row.getAttribute('data-category') || '';
                const matchSearch = title.includes(searchVal);
                const matchCat = !catVal || cat === catVal;

                if (matchSearch && matchCat) {
                    row.classList.remove('hidden');
                    count++;
                } else {
                    row.classList.add('hidden');
                }
            });

            document.getElementById('no-articles-msg').classList.toggle('hidden', count > 0);
        }

        function openFormModal(data = null) {
            if (data) {
                document.getElementById('form-modal-title').innerText = 'Edit Artikel';
                document.getElementById('form-id').value = data.id || '';
                document.getElementById('form-title').value = data.title || '';
                document.getElementById('form-category').value = data.category || 'Aktivitas Harian';
                document.getElementById('form-author').value = data.author || '';
                document.getElementById('form-image-url').value = data.image_url || '';
                document.getElementById('form-excerpt').value = data.excerpt || '';
                document.getElementById('form-content').value = data.content || '';
                document.getElementById('form-is-featured').checked = (data.is_featured == 1);
            } else {
                document.getElementById('form-modal-title').innerText = 'Tambah Artikel Baru';
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

        function openDetailModal(data) {
            document.getElementById('detail-image').src = data.image_url || 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1000&q=80';
            document.getElementById('detail-category').innerText = data.category || 'Aktivitas Harian';
            document.getElementById('detail-title').innerText = data.title || '';
            document.getElementById('detail-author').innerText = data.author || 'Admin';
            document.getElementById('detail-date').innerText = data.created_at ? new Date(data.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '';
            document.getElementById('detail-content').innerHTML = data.content || '';
            document.getElementById('detail-modal').classList.remove('hidden');
        }

        function closeDetailModal() {
            document.getElementById('detail-modal').classList.add('hidden');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeFormModal();
                closeDeleteModal();
                closeDetailModal();
            }
        });
    </script>
</body>
</html>
