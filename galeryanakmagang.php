<?php
require_once __DIR__ . '/session.php';

// Database connection attempt
$conn = null;
if (file_exists(__DIR__ . '/Login/koneksi.php')) {
    include_once __DIR__ . '/Login/koneksi.php';
}

$is_logged_in = is_logged_in();
$user_name = $is_logged_in ? current_user_name() : 'Anak Magang / Pengunjung';
$json_file = __DIR__ . '/uploads/gallery.json';
$upload_dir = __DIR__ . '/uploads/gallery/';

if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0777, true);
}

// Function to auto-create MySQL table if DB is available
function init_gallery_db_table($conn) {
    if (!$conn) return false;
    $sql = "CREATE TABLE IF NOT EXISTS `gallery_activities` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `category` VARCHAR(100) NOT NULL DEFAULT 'Kegiatan Magang',
        `author` VARCHAR(100) NOT NULL,
        `description` TEXT NOT NULL,
        `image_url` VARCHAR(500) NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    @mysqli_query($conn, $sql);
    return true;
}

// Seed initial gallery data
function get_seed_gallery() {
    return [
        [
            'id' => 1,
            'title' => 'Sesi Briefing Pagi & Standup Meeting Harian',
            'category' => 'Aktivitas Harian',
            'author' => 'Tim Dev Magang',
            'description' => 'Diskusi pembagian tugas sprint harian dan review progres fitur bersama tim mentor senior.',
            'image_url' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 days'))
        ],
        [
            'id' => 2,
            'title' => 'Workshop UI/UX Design & Prototyping',
            'category' => 'Belajar & Workshop',
            'author' => 'Rina - UI Intern',
            'description' => 'Pembelajaran langsung merancang design system berbasis Material Design 3 dan animasi mikro.',
            'image_url' => 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?auto=format&fit=crop&w=800&q=80',
            'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
        ],
        [
            'id' => 3,
            'title' => 'Sesi Coding Jam & Pair Programming',
            'category' => 'Coding & Dev',
            'author' => 'Budi - Dev Intern',
            'description' => 'Mengoptimalkan kueri database MySQL dan refactoring struktur partials PHP agar modular.',
            'image_url' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&w=800&q=80',
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
        ]
    ];
}

// Save to JSON helper
function save_gallery_json($json_file, $data) {
    file_put_contents($json_file, json_encode(array_values($data), JSON_PRETTY_PRINT));
}

// Fetch all gallery items
function get_all_gallery_items($conn, $json_file) {
    $items = [];
    if ($conn) {
        init_gallery_db_table($conn);
        $res = @mysqli_query($conn, "SELECT * FROM `gallery_activities` ORDER BY id DESC");
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $items[] = $row;
            }
        }
    }
    
    if (empty($items)) {
        if (file_exists($json_file)) {
            $items = json_decode(file_get_contents($json_file), true) ?: [];
        } else {
            $items = get_seed_gallery();
            save_gallery_json($json_file, $items);
        }
    }
    return $items;
}

$user_role = current_user_role();
$is_can_edit = $is_logged_in && ($user_role === 'admin' || $user_role === 'superadmin');

// Form Handlers (Hanya dapat dilakukan oleh Admin atau Superadmin)
$action_msg = '';
$action_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$is_can_edit) {
        $action_msg = 'Akses ditolak. Hanya Admin atau Superadmin yang memiliki wewenang untuk menambah, mengedit, atau menghapus kegiatan!';
        $action_type = 'danger';
    } else {
        $action = $_POST['action'] ?? '';
    
    if ($action === 'create_gallery') {
        $title       = trim($_POST['title'] ?? '');
        $category    = trim($_POST['category'] ?? 'Kegiatan Magang');
        $author      = trim($_POST['author'] ?? $user_name);
        $description = trim($_POST['description'] ?? '');
        $image_url   = trim($_POST['image_url'] ?? '');

        // Upload gambar jika ada
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $new_filename = time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $new_filename)) {
                    $image_url = 'uploads/gallery/' . $new_filename;
                }
            }
        }

        if (empty($image_url)) {
            $image_url = 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80';
        }

        if (!empty($title) && !empty($description)) {
            $now = date('Y-m-d H:i:s');
            $saved_db = false;

            if ($conn) {
                init_gallery_db_table($conn);
                $stmt = mysqli_prepare($conn, "INSERT INTO `gallery_activities` (title, category, author, description, image_url, created_at) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ssssss", $title, $category, $author, $description, $image_url, $now);
                    if (mysqli_stmt_execute($stmt)) {
                        $saved_db = true;
                    }
                    mysqli_stmt_close($stmt);
                }
            }

            // Sync to JSON
            $items = get_all_gallery_items(null, $json_file);
            $max_id = 0;
            foreach ($items as $it) {
                if (($it['id'] ?? 0) > $max_id) $max_id = intval($it['id']);
            }
            $new_item = [
                'id' => $max_id + 1,
                'title' => $title,
                'category' => $category,
                'author' => $author,
                'description' => $description,
                'image_url' => $image_url,
                'created_at' => $now
            ];
            array_unshift($items, $new_item);
            save_gallery_json($json_file, $items);

            $action_msg = 'Kegiatan magang baru berhasil ditambahkan!';
            $action_type = 'success';
        } else {
            $action_msg = 'Judul dan Deskripsi kegiatan wajib diisi!';
            $action_type = 'danger';
        }
    } elseif ($action === 'update_gallery') {
        $id          = intval($_POST['id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $category    = trim($_POST['category'] ?? 'Kegiatan Magang');
        $author      = trim($_POST['author'] ?? $user_name);
        $description = trim($_POST['description'] ?? '');
        $image_url   = trim($_POST['image_url'] ?? '');

        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $new_filename = time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $new_filename)) {
                    $image_url = 'uploads/gallery/' . $new_filename;
                }
            }
        }

        if ($id > 0 && !empty($title) && !empty($description)) {
            if ($conn) {
                init_gallery_db_table($conn);
                if (!empty($image_url)) {
                    $stmt = mysqli_prepare($conn, "UPDATE `gallery_activities` SET title=?, category=?, author=?, description=?, image_url=? WHERE id=?");
                    mysqli_stmt_bind_param($stmt, "sssssi", $title, $category, $author, $description, $image_url, $id);
                } else {
                    $stmt = mysqli_prepare($conn, "UPDATE `gallery_activities` SET title=?, category=?, author=?, description=? WHERE id=?");
                    mysqli_stmt_bind_param($stmt, "ssssi", $title, $category, $author, $description, $id);
                }
                if ($stmt) {
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }

            // Sync to JSON
            $items = get_all_gallery_items(null, $json_file);
            foreach ($items as &$it) {
                if (intval($it['id'] ?? 0) === $id) {
                    $it['title'] = $title;
                    $it['category'] = $category;
                    $it['author'] = $author;
                    $it['description'] = $description;
                    if (!empty($image_url)) {
                        $it['image_url'] = $image_url;
                    }
                }
            }
            save_gallery_json($json_file, $items);

            $action_msg = 'Data kegiatan magang berhasil diperbarui!';
            $action_type = 'success';
        }
    } elseif ($action === 'delete_gallery') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            if ($conn) {
                init_gallery_db_table($conn);
                $stmt = mysqli_prepare($conn, "DELETE FROM `gallery_activities` WHERE id=?");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "i", $id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }
            // Sync JSON
            $items = get_all_gallery_items(null, $json_file);
            $items = array_values(array_filter($items, fn($it) => intval($it['id'] ?? 0) !== $id));
            save_gallery_json($json_file, $items);

            $action_msg = 'Kegiatan berhasil dihapus dari galeri.';
            $action_type = 'info';
        }
    }
}
}

// Fetch all items for rendering
$gallery_list = get_all_gallery_items($conn, $json_file);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <title>Galeri Kegiatan Anak Magang - Kedayweb</title>
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
        .glass-card { background: rgba(255,255,255,0.9); backdrop-filter: blur(12px); }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col">
    <?php 
    if ($is_logged_in) {
        $active = 'galeri';
        $user_role = current_user_role();
        if ($user_role === 'admin' || $user_role === 'superadmin') {
            include 'partials/sidebar-admin.php';
        } else {
            include 'partials/sidebar-intern.php';
        }
    } else {
        $nav_icon = 'collections';
        $nav_cta_label = 'Masuk Portal';
        include 'partials/topnav-public.php';
    }
    ?>

    <!-- Main Content Area -->
    <main class="<?php echo $is_logged_in ? 'md:ml-[16.5rem]' : ''; ?> flex-1 flex flex-col">
        <!-- Hero Header -->
        <section class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white py-12 px-6 shadow-md">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <?php if ($is_logged_in): ?>
                    <div class="md:hidden">
                        <button onclick="toggleMobileSidebar()" class="px-3 py-2 rounded-lg bg-white/20 text-white flex items-center gap-2 text-sm font-semibold border border-white/30 hover:bg-white/30 transition-all">
                            <span class="material-symbols-outlined">menu</span>
                            <span>Menu Sidebar</span>
                        </button>
                    </div>
                <?php endif; ?>
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-500/20 text-blue-200 text-xs font-bold border border-blue-400/30 mb-3">
                        <span class="material-symbols-outlined text-sm">photo_camera</span>
                        <span>Galeri Terbuka Publik & Anak Magang</span>
                    </span>
                    <h1 class="font-geist text-3xl md:text-4xl font-extrabold tracking-tight">Galeri Kegiatan Anak Magang</h1>
                    <p class="mt-2 text-slate-300 max-w-2xl text-sm leading-relaxed">
                        Dokumentasi seluruh aktivitas harian, keseruan coding, workshop, dan pencapaian project anak magang Kedayweb.
                    </p>
                </div>
                
                <?php if ($is_can_edit): ?>
                    <button onclick="openCreateModal()" class="shrink-0 px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-bold text-sm transition-all shadow-lg flex items-center gap-2 active:scale-95">
                        <span class="material-symbols-outlined">add_photo_alternate</span>
                        <span>Tambah Kegiatan Baru</span>
                    </button>
                <?php elseif (!$is_logged_in): ?>
                    <a href="Login/login.php" class="shrink-0 px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-xl font-bold text-xs transition-all backdrop-blur-md flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">lock</span>
                        <span>Login untuk Menambah & Edit</span>
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <!-- Container -->
        <div class="max-w-7xl mx-auto p-6 md:p-8">
            <!-- Alert Notification -->
            <?php if (!empty($action_msg)): ?>
                <div class="mb-6 p-4 rounded-xl <?php echo $action_type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-blue-50 text-blue-800 border border-blue-200'; ?> flex justify-between items-center text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined"><?php echo $action_type === 'success' ? 'check_circle' : 'info'; ?></span>
                        <span><?php echo htmlspecialchars($action_msg, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-slate-500 hover:text-slate-800">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Filter Controls -->
            <div class="mb-8 flex flex-col sm:flex-row justify-between items-center gap-4 border-b border-slate-200 pb-4">
                <div class="flex flex-wrap gap-2">
                    <button onclick="filterCategory('all', this)" class="cat-btn active px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 text-white shadow-sm">Semua Kegiatan (<?php echo count($gallery_list); ?>)</button>
                    <button onclick="filterCategory('Aktivitas Harian', this)" class="cat-btn px-4 py-2 rounded-xl text-xs font-semibold bg-white text-slate-600 hover:bg-slate-100 border border-slate-200">Aktivitas Harian</button>
                    <button onclick="filterCategory('Belajar & Workshop', this)" class="cat-btn px-4 py-2 rounded-xl text-xs font-semibold bg-white text-slate-600 hover:bg-slate-100 border border-slate-200">Belajar & Workshop</button>
                    <button onclick="filterCategory('Coding & Dev', this)" class="cat-btn px-4 py-2 rounded-xl text-xs font-semibold bg-white text-slate-600 hover:bg-slate-100 border border-slate-200">Coding & Dev</button>
                </div>

                <div class="w-full sm:w-64 relative">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-sm">search</span>
                    <input id="gallerySearch" onkeyup="searchGallery()" placeholder="Cari kegiatan..." class="w-full pl-9 pr-4 py-2 text-xs border border-slate-200 rounded-xl bg-white outline-none focus:border-blue-600"/>
                </div>
            </div>

            <!-- Gallery Grid -->
            <div id="galleryGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($gallery_list)): ?>
                    <div class="col-span-full py-16 text-center text-slate-400">
                        <span class="material-symbols-outlined text-5xl mb-2 text-slate-300">collections</span>
                        <p class="font-bold text-slate-700">Belum Ada Foto Kegiatan</p>
                        <p class="text-xs text-slate-500 mt-1">Jadilah yang pertama menambahkan kegiatan magang ke galeri!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($gallery_list as $item): 
                        $id = (int) $item['id'];
                        $title = htmlspecialchars($item['title'] ?? '', ENT_QUOTES, 'UTF-8');
                        $cat = htmlspecialchars($item['category'] ?? 'Kegiatan Magang', ENT_QUOTES, 'UTF-8');
                        $author = htmlspecialchars($item['author'] ?? 'Anonim', ENT_QUOTES, 'UTF-8');
                        $desc = htmlspecialchars($item['description'] ?? '', ENT_QUOTES, 'UTF-8');
                        $img = htmlspecialchars($item['image_url'] ?? '', ENT_QUOTES, 'UTF-8');
                        $date = htmlspecialchars($item['created_at'] ?? '', ENT_QUOTES, 'UTF-8');
                    ?>
                        <article data-category="<?php echo $cat; ?>" class="gallery-card bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition-all flex flex-col group">
                            <div class="relative h-52 overflow-hidden bg-slate-100">
                                <img src="<?php echo $img; ?>" alt="<?php echo $title; ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" onerror="this.src='https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80';"/>
                                <span class="absolute top-3 left-3 bg-black/60 backdrop-blur-md text-white text-[11px] font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                                    <?php echo $cat; ?>
                                </span>
                            </div>

                            <div class="p-5 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="font-geist font-bold text-base text-slate-900 line-clamp-2 leading-snug mb-2"><?php echo $title; ?></h3>
                                    <p class="text-slate-600 text-xs line-clamp-3 leading-relaxed mb-4"><?php echo $desc; ?></p>
                                </div>

                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                                    <div class="flex items-center gap-1.5 truncate max-w-[60%]">
                                        <span class="material-symbols-outlined text-sm text-blue-600">account_circle</span>
                                        <span class="truncate font-semibold text-slate-700"><?php echo $author; ?></span>
                                    </div>
                                    
                                    <?php if ($is_can_edit): ?>
                                        <!-- Edit & Delete Action Buttons (Khusus Intern, Admin, Superadmin) -->
                                        <div class="flex items-center gap-1">
                                            <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>)" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit Kegiatan">
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </button>
                                            <form method="POST" action="galeryanakmagang.php" class="inline confirm-action-form" data-confirm-title="Hapus Foto Kegiatan" data-confirm-message="Apakah Anda yakin ingin menghapus foto kegiatan magang ini?">
                                                <input type="hidden" name="action" value="delete_gallery"/>
                                                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                                                <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <p id="noSearchResult" class="hidden text-center text-slate-500 py-12 text-sm">Tidak ada foto kegiatan yang cocok dengan pencarian.</p>
        </div>
        <div class="shrink-0 mt-auto w-full">
            <?php include 'partials/footer.php'; ?>
        </div>
    </main>

    <!-- Modal Form (Tambah / Edit Kegiatan Magang) -->
    <div id="galleryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                <h3 id="modalHeading" class="font-geist font-bold text-lg text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-600">add_photo_alternate</span>
                    <span>Tambah Kegiatan Magang</span>
                </h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="galleryForm" action="galeryanakmagang.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" id="formAction" value="create_gallery"/>
                <input type="hidden" name="id" id="itemId" value=""/>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Judul Kegiatan / Momen</label>
                    <input type="text" name="title" id="itemTitle" required placeholder="Contoh: Belajar Framework Next.js Bersama Team Senior" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl outline-none focus:border-blue-600"/>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kategori</label>
                        <select name="category" id="itemCategory" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl outline-none focus:border-blue-600">
                            <option value="Aktivitas Harian">Aktivitas Harian</option>
                            <option value="Belajar & Workshop">Belajar & Workshop</option>
                            <option value="Coding & Dev">Coding & Dev</option>
                            <option value="Acara & Rekreasi">Acara & Rekreasi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pembuat / Pengunggah</label>
                        <input type="text" name="author" id="itemAuthor" value="<?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?>" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl outline-none focus:border-blue-600"/>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Upload File Foto / Gambar</label>
                    <input type="file" name="image_file" accept="image/*" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50"/>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Atau Gunakan URL Gambar (Opsional)</label>
                    <input type="url" name="image_url" id="itemImageUrl" placeholder="https://images.unsplash.com/..." class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl outline-none focus:border-blue-600"/>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Kegiatan</label>
                    <textarea name="description" id="itemDescription" rows="3" required placeholder="Ceritakan keseruan atau ringkasan aktivitas magang..." class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl outline-none focus:border-blue-600"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">Simpan Foto Kegiatan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('formAction').value = 'create_gallery';
            document.getElementById('itemId').value = '';
            document.getElementById('modalHeading').innerHTML = '<span class="material-symbols-outlined text-blue-600">add_photo_alternate</span><span>Tambah Kegiatan Magang</span>';
            document.getElementById('galleryForm').reset();
            document.getElementById('galleryModal').classList.remove('hidden');
        }

        function openEditModal(item) {
            document.getElementById('formAction').value = 'update_gallery';
            document.getElementById('itemId').value = item.id;
            document.getElementById('itemTitle').value = item.title;
            document.getElementById('itemCategory').value = item.category || 'Aktivitas Harian';
            document.getElementById('itemAuthor').value = item.author || '';
            document.getElementById('itemImageUrl').value = item.image_url || '';
            document.getElementById('itemDescription').value = item.description || '';
            document.getElementById('modalHeading').innerHTML = '<span class="material-symbols-outlined text-blue-600">edit</span><span>Edit Foto Kegiatan</span>';
            document.getElementById('galleryModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('galleryModal').classList.add('hidden');
        }

        let activeCat = 'all';

        function filterCategory(cat, btn) {
            activeCat = cat;
            document.querySelectorAll('.cat-btn').forEach(b => {
                b.classList.remove('active', 'bg-blue-600', 'text-white');
                b.classList.add('bg-white', 'text-slate-600', 'border', 'border-slate-200');
            });
            btn.classList.add('active', 'bg-blue-600', 'text-white');
            btn.classList.remove('bg-white', 'text-slate-600', 'border', 'border-slate-200');
            searchGallery();
        }

        function searchGallery() {
            const query = (document.getElementById('gallerySearch').value || '').toLowerCase();
            const cards = document.querySelectorAll('.gallery-card');
            let visible = 0;

            cards.forEach(card => {
                const text = card.innerText.toLowerCase();
                const cat = card.dataset.category;

                const matchCat = (activeCat === 'all' || cat === activeCat);
                const matchQuery = text.includes(query);

                if (matchCat && matchQuery) {
                    card.classList.remove('hidden');
                    visible++;
                } else {
                    card.classList.add('hidden');
                }
            });

            const noResult = document.getElementById('noSearchResult');
            if (noResult) {
                noResult.classList.toggle('hidden', visible > 0 || cards.length === 0);
            }
        }

        document.querySelectorAll('.confirm-action-form').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const title = this.getAttribute('data-confirm-title') || 'Konfirmasi';
                const message = this.getAttribute('data-confirm-message') || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
                
                const confirmed = await window.showConfirmModal({
                    title: title,
                    message: message,
                    confirmText: 'Ya, Hapus',
                    cancelText: 'Batal',
                    type: 'danger'
                });

                if (confirmed) {
                    this.submit();
                }
            });
        });
    </script>

    <?php include 'partials/confirm-modal.php'; ?>
</body>
</html>
