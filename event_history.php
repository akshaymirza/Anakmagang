<?php
require_once __DIR__ . '/session.php';

// Database connection attempt
$conn = null;
if (file_exists(__DIR__ . '/Login/koneksi.php')) {
    include_once __DIR__ . '/Login/koneksi.php';
}

$is_logged_in = is_logged_in();
$user_name = $is_logged_in ? current_user_name() : 'Pengunjung / Anak Magang';
$events_json = __DIR__ . '/uploads/events_history.json';
$upload_dir = __DIR__ . '/uploads/events/';

if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0777, true);
}

// Auto-create DB Table for Event History
function init_events_db_table($conn) {
    if (!$conn) return false;
    $sql = "CREATE TABLE IF NOT EXISTS `events_history` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `event_name` VARCHAR(255) NOT NULL,
        `event_type` VARCHAR(100) NOT NULL DEFAULT 'Event Magang',
        `event_date` DATE NOT NULL,
        `location` VARCHAR(255) NOT NULL,
        `organizer` VARCHAR(100) NOT NULL,
        `summary` TEXT NOT NULL,
        `image_url` VARCHAR(500) NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    @mysqli_query($conn, $sql);
    return true;
}

// Initial Seed Data for Event History & Photo Documentation
function get_seed_events_history() {
    return [
        [
            'id' => 1,
            'event_name' => 'Showcase Sprint & Grand Demo Day Batch 2026',
            'event_type' => 'Grand Showcase',
            'event_date' => '2026-09-10',
            'location' => 'Main Auditorium Kedayweb & Online Streaming',
            'organizer' => 'Dev & HR Management',
            'summary' => 'Ajang pameran akhir program magang tempat peserta mendemonstrasikan sistem Portal InternSpace, modul absensi berbasis lokasi, dan dashboard analitik di hadapan para mentor senior serta direksi perusahaan.',
            'image_url' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1000&q=80',
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
        ],
        [
            'id' => 2,
            'event_name' => 'Hackathon Internal 24-Hour Innovation Sprint',
            'event_type' => 'Hackathon',
            'event_date' => '2026-08-25',
            'location' => 'Kedayweb Innovation Lab',
            'organizer' => 'Panitia Hackathon Kedayweb',
            'summary' => 'Kompetisi merancang dan membangun prototipe aplikasi cepat dalam kurun waktu 24 jam nonstop. Tim gabungan anak magang memenangkan Juara Favorit kategori Solusi Alur Kerja Internal.',
            'image_url' => 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=1000&q=80',
            'created_at' => date('Y-m-d H:i:s', strtotime('-20 days'))
        ],
        [
            'id' => 3,
            'event_name' => 'Workshop Technical Architecture & Git Flow Best Practices',
            'event_type' => 'Workshop & Training',
            'event_date' => '2026-08-05',
            'location' => 'Training Center & Zoom Room',
            'organizer' => 'Tech Lead Team',
            'summary' => 'Sesi pelatihan mendalam mengenai pengelolaan repository bersama, teknik branching Git Flow, penanganan konflik koding, dan pengoptimalan kueri database MySQL.',
            'image_url' => 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?auto=format&fit=crop&w=1000&q=80',
            'created_at' => date('Y-m-d H:i:s', strtotime('-40 days'))
        ],
        [
            'item_id' => 4,
            'id' => 4,
            'event_name' => 'Welcoming Ceremony & Kickoff Orientation Batch 2026',
            'event_type' => 'Welcoming Party',
            'event_date' => '2026-07-15',
            'location' => 'Kedayweb Rooftop & Lounge',
            'organizer' => 'HR & Internship Committee',
            'summary' => 'Acara peresmian pembukaan magang gelombang baru, sesi pengenalan budaya kerja agile, penyerahan akun dan laptop kerja, diakhiri makan siang bersama tim direksi.',
            'image_url' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1000&q=80',
            'created_at' => date('Y-m-d H:i:s', strtotime('-60 days'))
        ]
    ];
}

function save_events_json($events_json, $data) {
    file_put_contents($events_json, json_encode(array_values($data), JSON_PRETTY_PRINT));
}

function get_all_events_history($conn, $events_json) {
    $events = [];
    if ($conn) {
        init_events_db_table($conn);
        $res = @mysqli_query($conn, "SELECT * FROM `events_history` ORDER BY event_date DESC, id DESC");
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $events[] = $row;
            }
        }
    }
    
    if (empty($events)) {
        if (file_exists($events_json)) {
            $events = json_decode(file_get_contents($events_json), true) ?: [];
        } else {
            $events = get_seed_events_history();
            save_events_json($events_json, $events);
        }
    }
    return $events;
}

$user_role = current_user_role();
$is_can_edit = $is_logged_in && ($user_role === 'admin' || $user_role === 'superadmin');

// Form Handlers (Hanya dapat dilakukan oleh Admin atau Superadmin)
$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$is_can_edit) {
        $msg = 'Akses ditolak. Hanya Admin atau Superadmin yang memiliki wewenang untuk menambah, mengedit, atau menghapus event!';
        $msg_type = 'danger';
    } else {
        $action = $_POST['action'] ?? '';

    if ($action === 'create_event') {
        $name      = trim($_POST['event_name'] ?? '');
        $type      = trim($_POST['event_type'] ?? 'Event Magang');
        $date      = trim($_POST['event_date'] ?? date('Y-m-d'));
        $location  = trim($_POST['location'] ?? 'Kedayweb Office');
        $organizer = trim($_POST['organizer'] ?? $user_name);
        $summary   = trim($_POST['summary'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');

        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $new_filename = time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $new_filename)) {
                    $image_url = 'uploads/events/' . $new_filename;
                }
            }
        }

        if (empty($image_url)) {
            $image_url = 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1000&q=80';
        }

        if (!empty($name) && !empty($summary)) {
            $now = date('Y-m-d H:i:s');
            if ($conn) {
                init_events_db_table($conn);
                $stmt = mysqli_prepare($conn, "INSERT INTO `events_history` (event_name, event_type, event_date, location, organizer, summary, image_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ssssssss", $name, $type, $date, $location, $organizer, $summary, $image_url, $now);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }

            // Sync to JSON
            $events = get_all_events_history(null, $events_json);
            $max_id = 0;
            foreach ($events as $ev) {
                if (($ev['id'] ?? 0) > $max_id) $max_id = intval($ev['id']);
            }
            $new_event = [
                'id' => $max_id + 1,
                'event_name' => $name,
                'event_type' => $type,
                'event_date' => $date,
                'location' => $location,
                'organizer' => $organizer,
                'summary' => $summary,
                'image_url' => $image_url,
                'created_at' => $now
            ];
            array_unshift($events, $new_event);
            save_events_json($events_json, $events);

            $msg = 'Event baru berhasil ditambahkan ke histori!';
            $msg_type = 'success';
        } else {
            $msg = 'Nama Event dan Ringkasan wajib diisi!';
            $msg_type = 'danger';
        }
    } elseif ($action === 'update_event') {
        $id        = intval($_POST['id'] ?? 0);
        $name      = trim($_POST['event_name'] ?? '');
        $type      = trim($_POST['event_type'] ?? 'Event Magang');
        $date      = trim($_POST['event_date'] ?? date('Y-m-d'));
        $location  = trim($_POST['location'] ?? 'Kedayweb Office');
        $organizer = trim($_POST['organizer'] ?? $user_name);
        $summary   = trim($_POST['summary'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');

        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $new_filename = time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $new_filename)) {
                    $image_url = 'uploads/events/' . $new_filename;
                }
            }
        }

        if ($id > 0 && !empty($name) && !empty($summary)) {
            if ($conn) {
                init_events_db_table($conn);
                if (!empty($image_url)) {
                    $stmt = mysqli_prepare($conn, "UPDATE `events_history` SET event_name=?, event_type=?, event_date=?, location=?, organizer=?, summary=?, image_url=? WHERE id=?");
                    mysqli_stmt_bind_param($stmt, "sssssssi", $name, $type, $date, $location, $organizer, $summary, $image_url, $id);
                } else {
                    $stmt = mysqli_prepare($conn, "UPDATE `events_history` SET event_name=?, event_type=?, event_date=?, location=?, organizer=?, summary=? WHERE id=?");
                    mysqli_stmt_bind_param($stmt, "ssssssi", $name, $type, $date, $location, $organizer, $summary, $id);
                }
                if ($stmt) {
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }

            // Sync to JSON
            $events = get_all_events_history(null, $events_json);
            foreach ($events as &$ev) {
                if (intval($ev['id'] ?? 0) === $id) {
                    $ev['event_name'] = $name;
                    $ev['event_type'] = $type;
                    $ev['event_date'] = $date;
                    $ev['location']   = $location;
                    $ev['organizer']  = $organizer;
                    $ev['summary']    = $summary;
                    if (!empty($image_url)) {
                        $ev['image_url'] = $image_url;
                    }
                }
            }
            save_events_json($events_json, $events);

            $msg = 'Data histori event berhasil diperbarui!';
            $msg_type = 'success';
        }
    } elseif ($action === 'delete_event') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            if ($conn) {
                init_events_db_table($conn);
                $stmt = mysqli_prepare($conn, "DELETE FROM `events_history` WHERE id=?");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "i", $id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }

            $events = get_all_events_history(null, $events_json);
            $events = array_values(array_filter($events, fn($ev) => intval($ev['id'] ?? 0) !== $id));
            save_events_json($events_json, $events);

            $msg = 'Event berhasil dihapus dari histori.';
            $msg_type = 'info';
        }
    }
}
}

$events_list = get_all_events_history($conn, $events_json);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <title>Histori & Galeri Event Magang - Kedayweb</title>
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
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col">
    <?php 
    if ($is_logged_in) {
        $active = 'events';
        $user_role = current_user_role();
        if ($user_role === 'admin' || $user_role === 'superadmin') {
            include 'partials/sidebar-admin.php';
        } else {
            include 'partials/sidebar-intern.php';
        }
    } else {
        $nav_icon = 'stars';
        $nav_cta_label = 'Masuk Portal';
        include 'partials/topnav-public.php';
    }
    ?>

    <!-- Main Container -->
    <main class="<?php echo $is_logged_in ? 'md:ml-[16.5rem]' : ''; ?> flex-1 flex flex-col">
        <!-- Hero Header -->
        <section class="bg-gradient-to-r from-amber-600 via-indigo-900 to-slate-900 text-white py-14 px-6 shadow-lg relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 opacity-10 text-white pointer-events-none">
                <span class="material-symbols-outlined text-[280px]">workspace_premium</span>
            </div>
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative z-10">
                <?php if ($is_logged_in): ?>
                    <div class="md:hidden">
                        <button onclick="toggleMobileSidebar()" class="px-3 py-2 rounded-lg bg-white/20 text-white flex items-center gap-2 text-sm font-semibold border border-white/30 hover:bg-white/30 transition-all">
                            <span class="material-symbols-outlined">menu</span>
                            <span>Menu Sidebar</span>
                        </button>
                    </div>
                <?php endif; ?>
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-400/20 text-amber-200 text-xs font-bold border border-amber-400/30 mb-3">
                        <span class="material-symbols-outlined text-sm">history_edu</span>
                        <span>Official Event History & Photo Archives</span>
                    </span>
                    <h1 class="font-geist text-3xl md:text-5xl font-extrabold tracking-tight">Histori & Galeri Event Magang</h1>
                    <p class="mt-3 text-slate-200 max-w-2xl text-sm leading-relaxed">
                        Arsip lengkap seluruh event besar, hackathon, workshop, dan momen berharga yang pernah diselenggarakan oleh Kedayweb untuk anak magang.
                    </p>
                </div>
                
                <?php if ($is_can_edit): ?>
                    <button onclick="openCreateEventModal()" class="shrink-0 px-6 py-3.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-sm rounded-xl transition-all shadow-xl flex items-center gap-2 active:scale-95">
                        <span class="material-symbols-outlined text-lg">add_circle</span>
                        <span>Tambah Event Baru</span>
                    </button>
                <?php elseif (!$is_logged_in): ?>
                    <a href="Login/login.php" class="shrink-0 px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-xl font-bold text-xs transition-all backdrop-blur-md flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">lock</span>
                        <span>Login untuk Menambah & Edit</span>
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto p-6 md:p-8">
            <!-- Alert Notification -->
            <?php if (!empty($msg)): ?>
                <div class="mb-6 p-4 rounded-xl <?php echo $msg_type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-blue-50 text-blue-800 border border-blue-200'; ?> flex justify-between items-center text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined"><?php echo $msg_type === 'success' ? 'check_circle' : 'info'; ?></span>
                        <span><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-slate-500 hover:text-slate-800">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Filter & Search Bar -->
            <div class="mb-8 flex flex-col md:flex-row justify-between items-center gap-4 border-b border-slate-200 pb-4">
                <div class="flex flex-wrap gap-2">
                    <button onclick="filterType('all', this)" class="type-btn active px-4 py-2 rounded-xl text-xs font-bold bg-amber-500 text-slate-950 shadow-sm">Semua Event (<?php echo count($events_list); ?>)</button>
                    <button onclick="filterType('Grand Showcase', this)" class="type-btn px-4 py-2 rounded-xl text-xs font-semibold bg-white text-slate-600 hover:bg-slate-100 border border-slate-200">Grand Showcase</button>
                    <button onclick="filterType('Hackathon', this)" class="type-btn px-4 py-2 rounded-xl text-xs font-semibold bg-white text-slate-600 hover:bg-slate-100 border border-slate-200">Hackathon</button>
                    <button onclick="filterType('Workshop & Training', this)" class="type-btn px-4 py-2 rounded-xl text-xs font-semibold bg-white text-slate-600 hover:bg-slate-100 border border-slate-200">Workshop & Training</button>
                    <button onclick="filterType('Welcoming Party', this)" class="type-btn px-4 py-2 rounded-xl text-xs font-semibold bg-white text-slate-600 hover:bg-slate-100 border border-slate-200">Welcoming Party</button>
                </div>

                <div class="w-full md:w-72 relative">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-sm">search</span>
                    <input id="eventSearch" onkeyup="searchEvents()" placeholder="Cari event, lokasi, deskripsi..." class="w-full pl-9 pr-4 py-2 text-xs border border-slate-200 rounded-xl bg-white outline-none focus:border-amber-500 transition-colors"/>
                </div>
            </div>

            <!-- Timeline & Gallery Cards Container -->
            <div id="eventsContainer" class="space-y-8">
                <?php if (empty($events_list)): ?>
                    <div class="p-16 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
                        <span class="material-symbols-outlined text-6xl text-slate-300 mb-2">event_busy</span>
                        <h3 class="font-bold text-slate-700 text-base">Belum Ada Histori Event</h3>
                        <p class="text-xs text-slate-500 mt-1">Klik tombol 'Tambah Event Baru' untuk mencatatkan event pertama!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($events_list as $index => $ev):
                        $id        = (int) $ev['id'];
                        $name      = htmlspecialchars($ev['event_name'] ?? '', ENT_QUOTES, 'UTF-8');
                        $type      = htmlspecialchars($ev['event_type'] ?? 'Event Magang', ENT_QUOTES, 'UTF-8');
                        $date      = htmlspecialchars($ev['event_date'] ?? '', ENT_QUOTES, 'UTF-8');
                        $location  = htmlspecialchars($ev['location'] ?? '', ENT_QUOTES, 'UTF-8');
                        $organizer = htmlspecialchars($ev['organizer'] ?? 'Kedayweb', ENT_QUOTES, 'UTF-8');
                        $summary   = htmlspecialchars($ev['summary'] ?? '', ENT_QUOTES, 'UTF-8');
                        $img       = htmlspecialchars($ev['image_url'] ?? '', ENT_QUOTES, 'UTF-8');
                    ?>
                        <article data-type="<?php echo $type; ?>" class="event-card bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg transition-all overflow-hidden grid grid-cols-1 md:grid-cols-12 gap-0 group">
                            <!-- Image Column -->
                            <div class="md:col-span-5 relative h-64 md:h-full min-h-[220px] bg-slate-100 overflow-hidden">
                                <img src="<?php echo $img; ?>" alt="<?php echo $name; ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" onerror="this.src='https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1000&q=80';"/>
                                <span class="absolute top-4 left-4 bg-slate-900/80 backdrop-blur-md text-amber-400 border border-amber-400/30 text-[11px] font-bold px-3 py-1 rounded-full uppercase tracking-wider flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs">workspace_premium</span>
                                    <?php echo $type; ?>
                                </span>
                            </div>

                            <!-- Details Column -->
                            <div class="md:col-span-7 p-6 md:p-8 flex flex-col justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold">
                                            <span class="material-symbols-outlined text-sm">calendar_month</span>
                                            <span>Tanggal Event: <?php echo date('d F Y', strtotime($date)); ?></span>
                                        </span>

                                        <span class="inline-flex items-center gap-1 text-xs text-slate-500">
                                            <span class="material-symbols-outlined text-sm text-blue-600">person</span>
                                            <span>Penyelenggara: <strong><?php echo $organizer; ?></strong></span>
                                        </span>
                                    </div>

                                    <h2 class="font-geist font-bold text-xl md:text-2xl text-slate-900 mb-2 leading-tight group-hover:text-amber-600 transition-colors">
                                        <?php echo $name; ?>
                                    </h2>

                                    <p class="text-xs font-semibold text-slate-500 mb-4 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs text-red-500">location_on</span>
                                        <span><?php echo $location; ?></span>
                                    </p>

                                    <p class="text-slate-600 text-xs md:text-sm leading-relaxed mb-6">
                                        <?php echo $summary; ?>
                                    </p>
                                </div>

                                <!-- Card Footer Actions -->
                                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                                    <span class="text-[11px] text-slate-400">ID Event: #EVT-<?php echo sprintf('%03d', $id); ?></span>

                                    <?php if ($is_can_edit): ?>
                                        <!-- Action Buttons (Khusus Intern, Admin, Superadmin) -->
                                        <div class="flex items-center gap-2">
                                            <button onclick="openEditEventModal(<?php echo htmlspecialchars(json_encode($ev), ENT_QUOTES, 'UTF-8'); ?>)" class="px-3.5 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-bold transition-colors flex items-center gap-1">
                                                <span class="material-symbols-outlined text-sm">edit</span>
                                                <span>Edit Event</span>
                                            </button>

                                            <form method="POST" action="event_history.php" class="inline confirm-action-form" data-confirm-title="Hapus Event Histori" data-confirm-message="Apakah Anda yakin ingin menghapus event ini dari histori?">
                                                <input type="hidden" name="action" value="delete_event"/>
                                                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                                                <button type="submit" class="px-3.5 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-xs font-bold transition-colors flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-sm">delete</span>
                                                    <span>Hapus</span>
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
            <p id="noEventSearch" class="hidden text-center text-slate-500 py-12 text-sm">Tidak ada histori event yang sesuai dengan kueri pencarian.</p>
        </div>
        <div class="shrink-0 mt-auto w-full">
            <?php include 'partials/footer.php'; ?>
        </div>
    </main>

    <!-- Modal Form (Tambah / Edit Event) -->
    <div id="eventModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                <h3 id="eventModalHeading" class="font-geist font-bold text-lg text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-500">stars</span>
                    <span>Tambah Event Baru</span>
                </h3>
                <button onclick="closeEventModal()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="eventForm" action="event_history.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" id="eventFormAction" value="create_event"/>
                <input type="hidden" name="id" id="eventId" value=""/>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama / Judul Event</label>
                    <input type="text" name="event_name" id="eventName" required placeholder="Contoh: Showcase Sprint Batch 2026" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl outline-none focus:border-amber-500"/>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Event</label>
                        <select name="event_type" id="eventType" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl outline-none focus:border-amber-500">
                            <option value="Grand Showcase">Grand Showcase</option>
                            <option value="Hackathon">Hackathon</option>
                            <option value="Workshop & Training">Workshop & Training</option>
                            <option value="Welcoming Party">Welcoming Party</option>
                            <option value="Event Magang">Event Magang Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Event</label>
                        <input type="date" name="event_date" id="eventDate" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl outline-none focus:border-amber-500"/>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Lokasi Pelaksanaan</label>
                        <input type="text" name="location" id="eventLocation" required placeholder="Auditorium Kedayweb / Online" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl outline-none focus:border-amber-500"/>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Penyelenggara / Organizer</label>
                        <input type="text" name="organizer" id="eventOrganizer" value="<?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?>" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl outline-none focus:border-amber-500"/>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Upload Foto Event</label>
                    <input type="file" name="image_file" accept="image/*" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50"/>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Atau Gunakan URL Foto (Opsional)</label>
                    <input type="url" name="image_url" id="eventImageUrl" placeholder="https://images.unsplash.com/..." class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl outline-none focus:border-amber-500"/>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ringkasan & Catatan Event</label>
                    <textarea name="summary" id="eventSummary" rows="3" required placeholder="Jelaskan alur acara, keikutsertaan, dan pencapaian event..." class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl outline-none focus:border-amber-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeEventModal()" class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold rounded-xl text-xs transition-all shadow-md">Simpan Event</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateEventModal() {
            document.getElementById('eventFormAction').value = 'create_event';
            document.getElementById('eventId').value = '';
            document.getElementById('eventModalHeading').innerHTML = '<span class="material-symbols-outlined text-amber-500">stars</span><span>Tambah Event Baru</span>';
            document.getElementById('eventForm').reset();
            document.getElementById('eventModal').classList.remove('hidden');
        }

        function openEditEventModal(ev) {
            document.getElementById('eventFormAction').value = 'update_event';
            document.getElementById('eventId').value = ev.id;
            document.getElementById('eventName').value = ev.event_name;
            document.getElementById('eventType').value = ev.event_type || 'Grand Showcase';
            document.getElementById('eventDate').value = ev.event_date || '';
            document.getElementById('eventLocation').value = ev.location || '';
            document.getElementById('eventOrganizer').value = ev.organizer || '';
            document.getElementById('eventImageUrl').value = ev.image_url || '';
            document.getElementById('eventSummary').value = ev.summary || '';
            document.getElementById('eventModalHeading').innerHTML = '<span class="material-symbols-outlined text-amber-500">edit</span><span>Edit Event</span>';
            document.getElementById('eventModal').classList.remove('hidden');
        }

        function closeEventModal() {
            document.getElementById('eventModal').classList.add('hidden');
        }

        let activeType = 'all';

        function filterType(type, btn) {
            activeType = type;
            document.querySelectorAll('.type-btn').forEach(b => {
                b.classList.remove('active', 'bg-amber-500', 'text-slate-950', 'font-bold');
                b.classList.add('bg-white', 'text-slate-600', 'border', 'border-slate-200', 'font-semibold');
            });
            btn.classList.add('active', 'bg-amber-500', 'text-slate-950', 'font-bold');
            btn.classList.remove('bg-white', 'text-slate-600', 'border', 'border-slate-200', 'font-semibold');
            searchEvents();
        }

        function searchEvents() {
            const query = (document.getElementById('eventSearch').value || '').toLowerCase();
            const cards = document.querySelectorAll('.event-card');
            let visible = 0;

            cards.forEach(card => {
                const text = card.innerText.toLowerCase();
                const type = card.dataset.type;

                const matchType = (activeType === 'all' || type === activeType);
                const matchQuery = text.includes(query);

                if (matchType && matchQuery) {
                    card.classList.remove('hidden');
                    visible++;
                } else {
                    card.classList.add('hidden');
                }
            });

            const noResult = document.getElementById('noEventSearch');
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
