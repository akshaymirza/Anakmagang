<?php
require_once __DIR__ . '/../session.php';
require_superadmin();

// Database connection attempt
$conn = null;
if (file_exists(__DIR__ . '/../Login/koneksi.php')) {
    include_once __DIR__ . '/../Login/koneksi.php';
}

$positions_file = __DIR__ . '/../uploads/positions.json';
if (!file_exists(dirname($positions_file))) {
    @mkdir(dirname($positions_file), 0777, true);
}

// Function to fetch positions for index.php
function get_index_positions($positions_file) {
    if (!file_exists($positions_file)) {
        $default_positions = [
            [
                'id' => 1,
                'title' => 'Magang Web Developer',
                'category' => 'Teknik',
                'icon' => 'code',
                'description' => 'Bergabunglah dengan tim frontend kami untuk membangun antarmuka web modern, cepat, dan interaktif. Bekerja sama langsung dengan engineer senior.',
                'location' => 'Remote / Hybrid'
            ],
            [
                'id' => 2,
                'title' => 'Magang UI/UX Designer',
                'category' => 'Desain',
                'icon' => 'design_services',
                'description' => 'Bantu rancang pengalaman produk terbaik. Buat riset pengguna, wireframe, dan prototipe desain aplikasi berstandar industri.',
                'location' => 'Banyuwangi / Remote'
            ]
        ];
        file_put_contents($positions_file, json_encode($default_positions, JSON_PRETTY_PRINT));
        return $default_positions;
    }
    $data = json_decode(file_get_contents($positions_file), true);
    return is_array($data) ? $data : [];
}

// Save positions
function save_index_positions($positions_file, $positions) {
    file_put_contents($positions_file, json_encode($positions, JSON_PRETTY_PRINT));
}

// Handle POST actions
$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_user') {
        $username        = trim($_POST['username'] ?? '');
        $password        = trim($_POST['password'] ?? '');
        $role            = trim($_POST['role'] ?? 'intern');
        $intern_position = trim($_POST['intern_position'] ?? '');
        $university      = trim($_POST['university'] ?? '');
        $major           = trim($_POST['major'] ?? '');

        if (empty($username) || empty($password)) {
            $msg = "Username dan Password wajib diisi!";
            $msg_type = "danger";
        } elseif ($conn) {
            // Check existing username
            $check_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
            mysqli_stmt_bind_param($check_stmt, "s", $username);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);

            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $msg = "Username '{$username}' sudah terdaftar!";
                $msg_type = "danger";
            } else {
                $insert_stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role, intern_position, university, major) VALUES (?, ?, ?, ?, ?, ?)");
                if ($insert_stmt) {
                    mysqli_stmt_bind_param($insert_stmt, "ssssss", $username, $password, $role, $intern_position, $university, $major);
                    if (mysqli_stmt_execute($insert_stmt)) {
                        $new_user_id = mysqli_insert_id($conn);
                        if ($role === 'intern' && $new_user_id > 0) {
                            ensure_intern_certificate($conn, $new_user_id, $username, $intern_position, $university, $major);
                        }
                        $msg = "User baru '{$username}' dengan role '{$role}' berhasil ditambahkan (Sertifikat dibuat otomatis)!";
                        $msg_type = "success";
                    } else {
                        $msg = "Gagal menambahkan user ke database: " . mysqli_error($conn);
                        $msg_type = "danger";
                    }
                    mysqli_stmt_close($insert_stmt);
                }
            }
            mysqli_stmt_close($check_stmt);
        }
    } elseif ($action === 'update_user') {
        $user_id         = intval($_POST['user_id'] ?? 0);
        $username        = trim($_POST['username'] ?? '');
        $password        = trim($_POST['password'] ?? '');
        $role            = trim($_POST['role'] ?? 'intern');
        $intern_position = trim($_POST['intern_position'] ?? '');
        $university      = trim($_POST['university'] ?? '');
        $major           = trim($_POST['major'] ?? '');

        if ($conn && $user_id > 0) {
            if (!empty($password)) {
                $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, password=?, role=?, intern_position=?, university=?, major=? WHERE id=?");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ssssssi", $username, $password, $role, $intern_position, $university, $major, $user_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            } else {
                $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, role=?, intern_position=?, university=?, major=? WHERE id=?");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "sssssi", $username, $role, $intern_position, $university, $major, $user_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }
            if ($role === 'intern') {
                ensure_intern_certificate($conn, $user_id, $username, $intern_position, $university, $major);
            }
            $msg = "Data user ID #{$user_id} berhasil diperbarui!";
            $msg_type = "success";
        }
    } elseif ($action === 'update_user_role') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $new_role = trim($_POST['role'] ?? 'intern');
        if ($conn && $user_id > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE users SET role=? WHERE id=?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "si", $new_role, $user_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                if ($new_role === 'intern') {
                    ensure_intern_certificate($conn, $user_id, '');
                }
                $msg = "Role user ID #{$user_id} berhasil diubah menjadi {$new_role}.";
                $msg_type = "success";
            }
        }
    } elseif ($action === 'delete_user') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $current_session_user_id = intval($_SESSION['user_id'] ?? 0);

        if ($conn && $user_id > 0) {
            if ($user_id === $current_session_user_id) {
                $msg = "Anda tidak dapat menghapus akun Anda sendiri yang sedang digunakan!";
                $msg_type = "danger";
            } else {
                $get_u = mysqli_prepare($conn, "SELECT username FROM users WHERE id = ?");
                mysqli_stmt_bind_param($get_u, "i", $user_id);
                mysqli_stmt_execute($get_u);
                $res_u = mysqli_stmt_get_result($get_u);
                $u_row = mysqli_fetch_assoc($res_u);
                $deleted_name = $u_row['username'] ?? "ID #{$user_id}";
                mysqli_stmt_close($get_u);

                $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "i", $user_id);
                    if (mysqli_stmt_execute($stmt)) {
                        $msg = "Akun user '{$deleted_name}' (ID #{$user_id}) berhasil dihapus permanen!";
                        $msg_type = "success";
                    } else {
                        $msg = "Gagal menghapus user dari database.";
                        $msg_type = "danger";
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }
    } elseif ($action === 'add_position') {
        $positions = get_index_positions($positions_file);
        $max_id = 0;
        foreach ($positions as $p) {
            if (($p['id'] ?? 0) > $max_id) $max_id = $p['id'];
        }
        $new_pos = [
            'id' => $max_id + 1,
            'title' => trim($_POST['title'] ?? ''),
            'category' => trim($_POST['category'] ?? 'Teknik'),
            'icon' => trim($_POST['icon'] ?? 'work'),
            'description' => trim($_POST['description'] ?? ''),
            'location' => trim($_POST['location'] ?? 'Remote / Hybrid')
        ];
        $positions[] = $new_pos;
        save_index_positions($positions_file, $positions);
        $msg = "Posisi baru berhasil ditambahkan ke index.php!";
        $msg_type = "success";
    } elseif ($action === 'delete_position') {
        $pos_id = intval($_POST['pos_id'] ?? 0);
        $positions = get_index_positions($positions_file);
        $positions = array_values(array_filter($positions, function($p) use ($pos_id) {
            return ($p['id'] ?? 0) != $pos_id;
        }));
        save_index_positions($positions_file, $positions);
        $msg = "Posisi berhasil dihapus dari index.php.";
        $msg_type = "danger";
    } elseif ($action === 'save_attendance_zone') {
        $office_name   = trim($_POST['office_name'] ?? 'Kantor Kedayweb');
        $address       = trim($_POST['address'] ?? '');
        $latitude      = (float) ($_POST['latitude'] ?? -8.219321);
        $longitude     = (float) ($_POST['longitude'] ?? 114.369458);
        $radius_meters = max(10, intval($_POST['radius_meters'] ?? 100));
        $is_strict     = isset($_POST['is_strict']) && $_POST['is_strict'] == '1' ? 1 : 0;

        if ($conn) {
            $chk = mysqli_query($conn, "SELECT id FROM attendance_settings WHERE id = 1 LIMIT 1");
            if ($chk && mysqli_num_rows($chk) > 0) {
                $stmt = mysqli_prepare($conn, "UPDATE attendance_settings SET office_name=?, address=?, latitude=?, longitude=?, radius_meters=?, is_strict=? WHERE id = 1");
                mysqli_stmt_bind_param($stmt, "ssddii", $office_name, $address, $latitude, $longitude, $radius_meters, $is_strict);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } else {
                $stmt = mysqli_prepare($conn, "INSERT INTO attendance_settings (id, office_name, address, latitude, longitude, radius_meters, is_strict) VALUES (1, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "ssddii", $office_name, $address, $latitude, $longitude, $radius_meters, $is_strict);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            $msg = "Pengaturan titik zona koordinat absensi berhasil disimpan!";
            $msg_type = "success";
        }
    }
}

// Fetch Database Users
$users = [];
if ($conn) {
    // Auto migration: Ensure intern_position, university, major columns exist in users table
    $cols_to_check = [
        'intern_position' => 'VARCHAR(150) DEFAULT NULL',
        'university'      => 'VARCHAR(150) DEFAULT NULL',
        'major'           => 'VARCHAR(150) DEFAULT NULL'
    ];
    foreach ($cols_to_check as $col_name => $col_def) {
        $chk_col = @mysqli_query($conn, "SHOW COLUMNS FROM users LIKE '$col_name'");
        if ($chk_col && mysqli_num_rows($chk_col) == 0) {
            @mysqli_query($conn, "ALTER TABLE users ADD COLUMN $col_name $col_def");
        }
    }

    // Auto sync certificates for any intern users
    sync_all_intern_certificates($conn);

    $res = @mysqli_query($conn, "SELECT id, username, password, role, intern_position, university, major FROM users ORDER BY id ASC");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $users[] = $row;
        }
    }
}

// Fetch Attendance Zone Settings
$att_settings = [
    'office_name'   => 'Kantor Kedayweb Banyuwangi',
    'address'       => 'Jl. Tamansari, Tukangkayu, Banyuwangi, Jawa Timur',
    'latitude'      => -8.21932100,
    'longitude'     => 114.36945800,
    'radius_meters' => 100,
    'is_strict'     => 1
];
if ($conn) {
    $resAtt = @mysqli_query($conn, "SELECT * FROM attendance_settings WHERE id = 1 LIMIT 1");
    if ($resAtt && $rAtt = mysqli_fetch_assoc($resAtt)) {
        $att_settings['office_name']   = $rAtt['office_name'] ?? $att_settings['office_name'];
        $att_settings['address']       = $rAtt['address'] ?? $att_settings['address'];
        $att_settings['latitude']      = (float) ($rAtt['latitude'] ?? $att_settings['latitude']);
        $att_settings['longitude']     = (float) ($rAtt['longitude'] ?? $att_settings['longitude']);
        $att_settings['radius_meters'] = (int) ($rAtt['radius_meters'] ?? $att_settings['radius_meters']);
        $att_settings['is_strict']     = (int) ($rAtt['is_strict'] ?? $att_settings['is_strict']);
    }
}

$positions = get_index_positions($positions_file);
$current_active_role = current_user_role();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <title>Kedayweb Superadmin - Role & Position Control</title>
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
    <!-- Leaflet Map CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .glass-card { background: rgba(255,255,255,0.85); backdrop-filter: blur(12px); }
        #geofence-map { height: 380px; width: 100%; border-radius: 1rem; z-index: 1; }
        .leaflet-pane { z-index: 10 !important; }
        .leaflet-top, .leaflet-bottom { z-index: 11 !important; }
    </style>
</head>
<body class="bg-background text-on-surface font-body-md flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <?php $active = 'superadmin'; include '../partials/sidebar-admin.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col md:ml-[16.5rem] h-screen overflow-y-auto">
        <!-- Top Header -->
        <header class="w-full h-20 bg-surface-container-lowest border-b border-outline-variant sticky top-0 flex justify-between items-center px-6 z-10 gap-2 shrink-0">
            <div class="flex items-center gap-2">
                <button onclick="toggleMobileSidebar()" type="button" class="md:hidden p-2 text-on-surface hover:bg-surface-container-high rounded-lg shrink-0" aria-label="Buka Menu Sidebar">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <h2 class="font-headline-lg flex items-center gap-2 text-primary font-bold">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">verified_user</span>
                    <span>Superadmin Control Center</span>
                </h2>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-sm p-1.5 px-3 rounded-full border border-outline-variant bg-surface-bright shadow-2xs">
                    <div class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container overflow-hidden shrink-0">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">account_circle</span>
                    </div>
                    <span class="hidden sm:inline-block font-label-md"><?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="../logout.php" class="text-error hover:text-red-700 hover:bg-red-50 p-1.5 rounded-full transition-colors flex items-center justify-center" title="Keluar" aria-label="Keluar">
                        <span class="material-symbols-outlined text-[20px]">logout</span>
                    </a>
                </div>
            </div>
        </header>

        <div class="p-6 flex flex-col gap-6">

            <!-- Alert Notification -->
            <?php if (!empty($msg)): ?>
                <div class="flex items-center justify-between p-4 rounded-xl <?php echo $msg_type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : ($msg_type === 'danger' ? 'bg-red-50 text-red-800 border border-red-200' : 'bg-blue-50 text-blue-800 border border-blue-200'); ?> shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined"><?php echo $msg_type === 'success' ? 'check_circle' : 'info'; ?></span>
                        <span class="font-label-md font-semibold"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Section 1: Pengaturan Titik Zona Koordinat Absensi (Geofencing) -->
            <div class="glass-card rounded-2xl border border-outline-variant p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-outline-variant mb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">near_me</span>
                            <h3 class="font-headline-md font-bold text-on-surface">Titik Zona Koordinat Absensi (Geofencing)</h3>
                        </div>
                        <p class="text-sm text-on-surface-variant mt-1">Tentukan titik koordinat kantor dan radius toleransi jarak absensi untuk anak magang.</p>
                    </div>
                    <!-- Status Badges -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-blue-600">business</span>
                            <span id="badge-office-name"><?php echo htmlspecialchars($att_settings['office_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-800 border border-indigo-200 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-indigo-600">radar</span>
                            Radius: <span id="badge-radius"><?php echo (int) $att_settings['radius_meters']; ?></span> m
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $att_settings['is_strict'] ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200'; ?> flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm <?php echo $att_settings['is_strict'] ? 'text-emerald-600' : 'text-amber-600'; ?>">
                                <?php echo $att_settings['is_strict'] ? 'lock' : 'lock_open'; ?>
                            </span>
                            <span id="badge-strict-mode"><?php echo $att_settings['is_strict'] ? 'Wajib di Kantor (Ketat)' : 'Fleksibel'; ?></span>
                        </span>
                    </div>
                </div>

                <form action="superadmin.php" method="POST" id="form-attendance-zone" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <input type="hidden" name="action" value="save_attendance_zone"/>
                    
                    <!-- Left: Form inputs (5 cols on lg) -->
                    <div class="lg:col-span-5 flex flex-col gap-4">
                        <div>
                            <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Nama Lokasi / Kantor</label>
                            <input type="text" name="office_name" id="zone-office-name" required value="<?php echo htmlspecialchars($att_settings['office_name'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:outline-none"/>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Alamat Patokan</label>
                            <textarea name="address" id="zone-address" rows="2" placeholder="Contoh: Jl. Tamansari, Tukangkayu, Banyuwangi..." class="w-full px-3.5 py-2 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:outline-none"><?php echo htmlspecialchars($att_settings['address'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Latitude (Lintang)</label>
                                <input type="number" step="any" name="latitude" id="zone-lat" required value="<?php echo htmlspecialchars($att_settings['latitude'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-mono font-medium focus:ring-2 focus:ring-primary focus:outline-none"/>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Longitude (Bujur)</label>
                                <input type="number" step="any" name="longitude" id="zone-lng" required value="<?php echo htmlspecialchars($att_settings['longitude'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-mono font-medium focus:ring-2 focus:ring-primary focus:outline-none"/>
                            </div>
                        </div>

                        <!-- GPS Current Location Button -->
                        <div>
                            <button type="button" id="btn-detect-my-location" onclick="detectAdminCurrentLocation()" class="w-full py-2.5 px-4 bg-surface-container-low hover:bg-surface-container-high text-primary border border-outline-variant rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer shadow-xs">
                                <span class="material-symbols-outlined text-sm">my_location</span>
                                <span>📍 Ambil Titik Lokasi Saya Sekarang (GPS)</span>
                            </button>
                            <p id="geo-status-msg" class="text-[11px] text-slate-500 mt-1 hidden"></p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Radius Toleransi (Meter)</label>
                            <div class="flex items-center gap-2 mb-2">
                                <input type="number" min="10" max="5000" name="radius_meters" id="zone-radius" required value="<?php echo (int) $att_settings['radius_meters']; ?>" class="w-28 px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-bold focus:ring-2 focus:ring-primary focus:outline-none"/>
                                <span class="text-xs text-on-surface-variant font-semibold">meter</span>
                            </div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-[11px] text-slate-400 font-semibold mr-1">Cepat:</span>
                                <button type="button" onclick="setQuickRadius(50)" class="px-2.5 py-1 text-xs rounded-lg border border-outline-variant hover:bg-slate-100 font-medium cursor-pointer">50m</button>
                                <button type="button" onclick="setQuickRadius(100)" class="px-2.5 py-1 text-xs rounded-lg border border-outline-variant hover:bg-slate-100 font-medium cursor-pointer">100m</button>
                                <button type="button" onclick="setQuickRadius(200)" class="px-2.5 py-1 text-xs rounded-lg border border-outline-variant hover:bg-slate-100 font-medium cursor-pointer">200m</button>
                                <button type="button" onclick="setQuickRadius(500)" class="px-2.5 py-1 text-xs rounded-lg border border-outline-variant hover:bg-slate-100 font-medium cursor-pointer">500m</button>
                                <button type="button" onclick="setQuickRadius(1000)" class="px-2.5 py-1 text-xs rounded-lg border border-outline-variant hover:bg-slate-100 font-medium cursor-pointer">1 km</button>
                            </div>
                        </div>

                        <div class="p-3.5 bg-surface-container-low rounded-xl border border-outline-variant">
                            <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Aturan Validasi Geofence</label>
                            <div class="space-y-2 text-xs">
                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input type="radio" name="is_strict" value="1" <?php echo $att_settings['is_strict'] ? 'checked' : ''; ?> class="mt-0.5 text-primary focus:ring-primary"/>
                                    <div>
                                        <span class="font-bold text-on-surface">Validasi Ketat (Wajib di Kantor)</span>
                                        <p class="text-slate-500 text-[11px] mt-0.5">Anak magang hanya bisa clock in jika posisi GPS berada di dalam radius kantor. Absensi ditolak jika di luar radius.</p>
                                    </div>
                                </label>
                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input type="radio" name="is_strict" value="0" <?php echo !$att_settings['is_strict'] ? 'checked' : ''; ?> class="mt-0.5 text-primary focus:ring-primary"/>
                                    <div>
                                        <span class="font-bold text-on-surface">Fleksibel (Peringatan Saja)</span>
                                        <p class="text-slate-500 text-[11px] mt-0.5">Anak magang tetap bisa clock in jika di luar kantor, namun sistem mencatat status jarak dari kantor.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full py-3 px-5 bg-primary text-on-primary rounded-xl font-bold hover:bg-primary-container transition-all flex items-center justify-center gap-2 shadow-md hover:shadow-lg cursor-pointer">
                                <span class="material-symbols-outlined">save</span>
                                <span>Simpan Pengaturan Titik Zona</span>
                            </button>
                        </div>
                    </div>

                    <!-- Right: Interactive Leaflet Map (7 cols on lg) -->
                    <div class="lg:col-span-7 flex flex-col">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm text-primary">map</span>
                                <span>Peta Interaktif Lokasi Kantor</span>
                            </label>
                            <span class="text-[11px] text-slate-500">Klik peta atau geser pin merah</span>
                        </div>
                        <div class="relative w-full rounded-2xl overflow-hidden border border-outline-variant shadow-inner">
                            <div id="geofence-map"></div>
                        </div>
                        <div class="mt-2.5 flex items-start gap-2 text-xs text-slate-500 bg-surface-container-low p-2.5 rounded-xl border border-outline-variant">
                            <span class="material-symbols-outlined text-sm text-primary flex-shrink-0 mt-0.5">info</span>
                            <span>Lingkaran biru menunjukkan area zona toleransi absensi (<strong id="map-radius-label"><?php echo (int) $att_settings['radius_meters']; ?> meter</strong>). Anda dapat menggeser marker pin untuk menyesuaikan titik pusat lokasi kantor.</span>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Section 2: Manage Positions on index.php -->
            <div class="glass-card rounded-2xl border border-outline-variant p-6 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="font-headline-md font-bold text-on-surface">Kontrol Posisi Magang di index.php</h3>
                        <p class="text-sm text-on-surface-variant">Tambah atau hapus posisi magang yang tampil pada landing page pendaftaran (`index.php`).</p>
                    </div>
                    <button onclick="document.getElementById('add-pos-modal').classList.remove('hidden')" class="px-4 py-2 bg-primary text-on-primary rounded-xl font-label-md hover:bg-primary-container transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined">add_circle</span>
                        <span>Tambah Posisi</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <?php foreach ($positions as $pos): ?>
                        <div class="p-4 rounded-xl border border-outline-variant bg-surface-container-lowest flex justify-between items-start">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary"><?php echo htmlspecialchars($pos['icon'] ?? 'work', ENT_QUOTES, 'UTF-8'); ?></span>
                                    <h4 class="font-headline-sm font-bold text-on-surface"><?php echo htmlspecialchars($pos['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-semibold"><?php echo htmlspecialchars($pos['category'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <p class="text-xs text-on-surface-variant"><?php echo htmlspecialchars($pos['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-xs text-primary font-semibold flex items-center gap-1 mt-1">
                                    <span class="material-symbols-outlined text-xs">location_on</span>
                                    <span><?php echo htmlspecialchars($pos['location'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </p>
                            </div>

                            <form action="superadmin.php" method="POST" class="confirm-action-form" data-confirm-title="Hapus Posisi Magang" data-confirm-message="Apakah Anda yakin ingin menghapus posisi ini dari index.php?">
                                <input type="hidden" name="action" value="delete_position"/>
                                <input type="hidden" name="pos_id" value="<?php echo (int) $pos['id']; ?>"/>
                                <button type="submit" class="text-error hover:bg-red-50 p-2 rounded-lg transition-colors" title="Hapus Posisi">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Section 3: Manage Database User Roles -->
            <div class="glass-card rounded-2xl border border-outline-variant p-6 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="font-headline-md font-bold text-on-surface">Manajemen User & Role Database</h3>
                        <p class="text-sm text-on-surface-variant">Kelola dan tambah pengguna baru serta ubah role akun di database.</p>
                    </div>
                    <button onclick="document.getElementById('add-user-modal').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 text-white rounded-xl font-label-md hover:bg-indigo-700 transition-all flex items-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined">person_add</span>
                        <span>Tambah User Baru</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-outline-variant bg-surface-container-low text-on-surface-variant">
                                <th class="py-3 px-4 font-semibold">ID</th>
                                <th class="py-3 px-4 font-semibold">Username / Email</th>
                                <th class="py-3 px-4 font-semibold">Password</th>
                                <th class="py-3 px-4 font-semibold">Role</th>
                                <th class="py-3 px-4 font-semibold">Posisi Magang</th>
                                <th class="py-3 px-4 font-semibold">Instansi / Universitas</th>
                                <th class="py-3 px-4 font-semibold">Jurusan</th>
                                <th class="py-3 px-4 font-semibold text-right">Kelola Akun</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-6 text-on-surface-variant">Tidak ada data user di database.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                    <tr class="hover:bg-surface-container-low">
                                        <td class="py-3 px-4 font-bold">#<?php echo (int) $u['id']; ?></td>
                                        <td class="py-3 px-4 font-medium text-on-surface"><?php echo htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="py-3 px-4 font-mono text-sm">
                                            <div class="flex items-center gap-2">
                                                <span class="password-text hidden bg-surface-container-high px-2 py-0.5 rounded text-xs text-primary font-bold select-all"><?php echo htmlspecialchars($u['password'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                                                <span class="password-hidden text-xs text-on-surface-variant font-bold">••••••••</span>
                                                <button type="button" onclick="togglePassword(this)" class="text-on-surface-variant hover:text-primary transition-colors cursor-pointer p-1 rounded-lg hover:bg-surface-container-high" title="Lihat/Sembunyikan Password">
                                                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="px-2.5 py-1 text-xs rounded-full font-bold <?php echo $u['role'] === 'admin' || $u['role'] === 'superadmin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'; ?>">
                                                <?php echo strtoupper(htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8')); ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 font-medium text-on-surface">
                                            <?php echo !empty($u['intern_position']) ? htmlspecialchars($u['intern_position'], ENT_QUOTES, 'UTF-8') : '<span class="text-slate-400 italic text-xs">-</span>'; ?>
                                        </td>
                                        <td class="py-3 px-4 font-medium text-on-surface">
                                            <?php echo !empty($u['university']) ? htmlspecialchars($u['university'], ENT_QUOTES, 'UTF-8') : '<span class="text-slate-400 italic text-xs">-</span>'; ?>
                                        </td>
                                        <td class="py-3 px-4 font-medium text-on-surface">
                                            <?php echo !empty($u['major']) ? htmlspecialchars($u['major'], ENT_QUOTES, 'UTF-8') : '<span class="text-slate-400 italic text-xs">-</span>'; ?>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                <!-- Tombol Edit User -->
                                                <button type="button" onclick='openEditUserModal(<?php echo json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="px-2.5 py-1 text-xs bg-indigo-50 text-indigo-700 hover:bg-indigo-600 hover:text-white border border-indigo-200 rounded-lg font-bold transition-all flex items-center gap-1 cursor-pointer" title="Edit Detail User">
                                                    <span class="material-symbols-outlined text-[14px]">edit</span> Edit
                                                </button>

                                                <!-- Form Hapus User -->
                                                <form action="superadmin.php" method="POST" class="inline-block confirm-action-form" data-confirm-title="Hapus Akun User" data-confirm-message="PERINGATAN SUPERADMIN: Apakah Anda yakin ingin menghapus akun '<?php echo htmlspecialchars(addslashes($u['username']), ENT_QUOTES, 'UTF-8'); ?>' (ID #<?php echo (int) $u['id']; ?>)? Tindakan ini akan menghapus akun secara permanen!">
                                                    <input type="hidden" name="action" value="delete_user"/>
                                                    <input type="hidden" name="user_id" value="<?php echo (int) $u['id']; ?>"/>
                                                    <button type="submit" class="px-2.5 py-1 text-xs bg-red-50 text-red-700 hover:bg-red-600 hover:text-white border border-red-200 rounded-lg font-bold transition-all flex items-center gap-1 cursor-pointer" title="Hapus Akun">
                                                        <span class="material-symbols-outlined text-[14px]">delete</span> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="mt-auto shrink-0 w-full">
            <?php include '../partials/footer.php'; ?>
        </div>
    </main>

    <!-- Modal Tambah User Baru -->
    <div id="add-user-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
        <div class="bg-surface-container-lowest rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-outline-variant max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3 border-b border-outline-variant mb-4">
                <h3 class="font-headline-md font-bold text-indigo-700 flex items-center gap-2">
                    <span class="material-symbols-outlined">person_add</span>
                    <span>Tambah User Baru</span>
                </h3>
                <button onclick="document.getElementById('add-user-modal').classList.add('hidden')" class="text-on-surface-variant hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="superadmin.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_user"/>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Username / Email <span class="text-red-500">*</span></label>
                        <input type="text" name="username" required placeholder="Contoh: user@kedayweb.com" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-sm outline-none focus:border-indigo-600"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required placeholder="Masukkan password" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-sm outline-none focus:border-indigo-600"/>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Role Akun</label>
                    <select name="role" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-sm outline-none focus:border-indigo-600">
                        <option value="intern">INTERN (Anak Magang)</option>
                        <option value="admin">ADMIN</option>
                        <option value="superadmin">SUPERADMIN</option>
                    </select>
                </div>

                <div class="pt-2 border-t border-outline-variant">
                    <p class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-2">Detail Anak Magang (Intern Info)</p>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Posisi Magang (intern_position)</label>
                            <input type="text" name="intern_position" placeholder="Contoh: Magang Web Developer" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-sm outline-none focus:border-indigo-600"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Asal Instansi / Universitas (university)</label>
                            <input type="text" name="university" placeholder="Contoh: Universitas Gadjah Mada" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-sm outline-none focus:border-indigo-600"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Jurusan (major)</label>
                            <input type="text" name="major" placeholder="Contoh: Informatika / Sistem Informasi" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-sm outline-none focus:border-indigo-600"/>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('add-user-modal').classList.add('hidden')" class="px-4 py-2 border rounded-xl text-sm">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-colors shadow-sm">Simpan User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div id="edit-user-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
        <div class="bg-surface-container-lowest rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-outline-variant max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3 border-b border-outline-variant mb-4">
                <h3 class="font-headline-md font-bold text-indigo-700 flex items-center gap-2">
                    <span class="material-symbols-outlined">edit</span>
                    <span>Edit User & Detail Intern</span>
                </h3>
                <button onclick="document.getElementById('edit-user-modal').classList.add('hidden')" class="text-on-surface-variant hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="superadmin.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="update_user"/>
                <input type="hidden" name="user_id" id="edit-user-id"/>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Username / Email <span class="text-red-500">*</span></label>
                        <input type="text" name="username" id="edit-username" required class="w-full px-3 py-2 border border-outline-variant rounded-xl text-sm outline-none focus:border-indigo-600"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Password Baru (opsional)</label>
                        <input type="password" name="password" id="edit-password" placeholder="Biarkan kosong jika tidak diubah" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-sm outline-none focus:border-indigo-600"/>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1">Role Akun</label>
                    <select name="role" id="edit-role" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-sm outline-none focus:border-indigo-600">
                        <option value="intern">INTERN (Anak Magang)</option>
                        <option value="admin">ADMIN</option>
                        <option value="superadmin">SUPERADMIN</option>
                    </select>
                </div>

                <div class="pt-2 border-t border-outline-variant">
                    <p class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-2">Detail Anak Magang (Intern Info)</p>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Posisi Magang (intern_position)</label>
                            <input type="text" name="intern_position" id="edit-intern-position" placeholder="Contoh: Magang Web Developer" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-sm outline-none focus:border-indigo-600"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Asal Instansi / Universitas (university)</label>
                            <input type="text" name="university" id="edit-university" placeholder="Contoh: Universitas Gadjah Mada" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-sm outline-none focus:border-indigo-600"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Jurusan (major)</label>
                            <input type="text" name="major" id="edit-major" placeholder="Contoh: Informatika / Sistem Informasi" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-sm outline-none focus:border-indigo-600"/>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('edit-user-modal').classList.add('hidden')" class="px-4 py-2 border rounded-xl text-sm">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-colors shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Posisi -->
    <div id="add-pos-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
        <div class="bg-surface-container-lowest rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-outline-variant">
            <div class="flex justify-between items-center pb-3 border-b border-outline-variant mb-4">
                <h3 class="font-headline-md font-bold text-primary">Tambah Posisi Magang (index.php)</h3>
                <button onclick="document.getElementById('add-pos-modal').classList.add('hidden')" class="text-on-surface-variant hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="superadmin.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_position"/>
                <div>
                    <label class="block text-xs font-semibold mb-1">Nama Posisi Magang</label>
                    <input type="text" name="title" required placeholder="Contoh: Magang Data Analyst" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-sm"/>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Kategori</label>
                        <select name="category" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-sm">
                            <option value="Teknik">Teknik</option>
                            <option value="Desain">Desain</option>
                            <option value="Pemasaran">Pemasaran</option>
                            <option value="Data">Data</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Icon Material Symbol</label>
                        <input type="text" name="icon" value="work" placeholder="code, design_services, analytics" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-sm"/>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Lokasi</label>
                    <input type="text" name="location" value="Remote / Hybrid" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-sm"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Deskripsi Singkat</label>
                    <textarea name="description" rows="3" required placeholder="Deskripsi tugas dan tanggung jawab..." class="w-full px-3 py-2 border border-outline-variant rounded-xl text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('add-pos-modal').classList.add('hidden')" class="px-4 py-2 border rounded-xl text-sm">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-primary text-on-primary rounded-xl text-sm font-bold">Simpan Posisi</button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../partials/confirm-modal.php'; ?>

    <script>
        function openEditUserModal(user) {
            document.getElementById('edit-user-id').value = user.id || '';
            document.getElementById('edit-username').value = user.username || '';
            document.getElementById('edit-password').value = '';
            document.getElementById('edit-role').value = user.role || 'intern';
            document.getElementById('edit-intern-position').value = user.intern_position || '';
            document.getElementById('edit-university').value = user.university || '';
            document.getElementById('edit-major').value = user.major || '';
            document.getElementById('edit-user-modal').classList.remove('hidden');
        }

        function togglePassword(btn) {
            const parent = btn.parentElement;
            const textSpan = parent.querySelector('.password-text');
            const hiddenSpan = parent.querySelector('.password-hidden');
            const icon = btn.querySelector('.material-symbols-outlined');
            
            if (textSpan.classList.contains('hidden')) {
                textSpan.classList.remove('hidden');
                hiddenSpan.classList.add('hidden');
                icon.textContent = 'visibility_off';
            } else {
                textSpan.classList.add('hidden');
                hiddenSpan.classList.remove('hidden');
                icon.textContent = 'visibility';
            }
        }

        document.querySelectorAll('.confirm-action-form').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const title = this.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
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

        // ============================================================
        // GEOFENCE MAP SCRIPT (LEAFLET.JS)
        // ============================================================
        let geofenceMap = null;
        let geofenceMarker = null;
        let geofenceCircle = null;

        function initGeofenceMap() {
            const latInput = document.getElementById('zone-lat');
            const lngInput = document.getElementById('zone-lng');
            const radiusInput = document.getElementById('zone-radius');
            const mapEl = document.getElementById('geofence-map');
            if (!mapEl || !latInput || !lngInput) return;

            let initialLat = parseFloat(latInput.value) || -8.219321;
            let initialLng = parseFloat(lngInput.value) || 114.369458;
            let initialRadius = parseInt(radiusInput.value) || 100;

            geofenceMap = L.map('geofence-map', {
                center: [initialLat, initialLng],
                zoom: 16,
                scrollWheelZoom: true
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(geofenceMap);

            // Draggable Marker
            geofenceMarker = L.marker([initialLat, initialLng], {
                draggable: true,
                title: 'Titik Kantor Kedayweb'
            }).addTo(geofenceMap);

            // Geofence Circle
            geofenceCircle = L.circle([initialLat, initialLng], {
                radius: initialRadius,
                color: '#2563eb',
                fillColor: '#3b82f6',
                fillOpacity: 0.22,
                weight: 2
            }).addTo(geofenceMap);

            // Marker Drag events
            geofenceMarker.on('drag', function(e) {
                const pos = e.target.getLatLng();
                geofenceCircle.setLatLng(pos);
                latInput.value = pos.lat.toFixed(7);
                lngInput.value = pos.lng.toFixed(7);
            });

            geofenceMarker.on('dragend', function(e) {
                const pos = e.target.getLatLng();
                reverseGeocode(pos.lat, pos.lng);
            });

            // Map Click event
            geofenceMap.on('click', function(e) {
                geofenceMarker.setLatLng(e.latlng);
                geofenceCircle.setLatLng(e.latlng);
                latInput.value = e.latlng.lat.toFixed(7);
                lngInput.value = e.latlng.lng.toFixed(7);
                reverseGeocode(e.latlng.lat, e.latlng.lng);
            });

            // Inputs change listener
            latInput.addEventListener('input', syncMapFromInputs);
            lngInput.addEventListener('input', syncMapFromInputs);
            radiusInput.addEventListener('input', function() {
                const r = parseInt(this.value) || 50;
                geofenceCircle.setRadius(r);
                const radiusLbl = document.getElementById('map-radius-label');
                if (radiusLbl) radiusLbl.textContent = r + ' meter';
                const badgeRadius = document.getElementById('badge-radius');
                if (badgeRadius) badgeRadius.textContent = r;
            });
        }

        function syncMapFromInputs() {
            const lat = parseFloat(document.getElementById('zone-lat').value);
            const lng = parseFloat(document.getElementById('zone-lng').value);
            const radius = parseInt(document.getElementById('zone-radius').value) || 100;
            if (!isNaN(lat) && !isNaN(lng) && geofenceMap && geofenceMarker && geofenceCircle) {
                const pos = [lat, lng];
                geofenceMarker.setLatLng(pos);
                geofenceCircle.setLatLng(pos);
                geofenceCircle.setRadius(radius);
                geofenceMap.panTo(pos);
            }
        }

        function setQuickRadius(r) {
            const radiusInput = document.getElementById('zone-radius');
            if (radiusInput) {
                radiusInput.value = r;
                if (geofenceCircle) geofenceCircle.setRadius(r);
                const radiusLbl = document.getElementById('map-radius-label');
                if (radiusLbl) radiusLbl.textContent = r + ' meter';
                const badgeRadius = document.getElementById('badge-radius');
                if (badgeRadius) badgeRadius.textContent = r;
            }
        }

        function reverseGeocode(lat, lng) {
            const addrInput = document.getElementById('zone-address');
            if (!addrInput) return;
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(r => r.json())
                .then(data => {
                    if (data && data.display_name) {
                        addrInput.value = data.display_name;
                    }
                })
                .catch(() => {});
        }

        function detectAdminCurrentLocation() {
            const statusMsg = document.getElementById('geo-status-msg');
            const btn = document.getElementById('btn-detect-my-location');
            if (statusMsg) {
                statusMsg.classList.remove('hidden');
                statusMsg.textContent = 'Mendeteksi posisi GPS Anda...';
                statusMsg.className = 'text-[11px] text-blue-600 mt-1';
            }
            if (!navigator.geolocation) {
                if (statusMsg) {
                    statusMsg.textContent = 'Geolokasi tidak didukung oleh browser ini.';
                    statusMsg.className = 'text-[11px] text-red-600 mt-1';
                }
                return;
            }
            btn.disabled = true;
            navigator.geolocation.getCurrentPosition(
                pos => {
                    btn.disabled = false;
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    document.getElementById('zone-lat').value = lat.toFixed(7);
                    document.getElementById('zone-lng').value = lng.toFixed(7);
                    if (geofenceMap && geofenceMarker && geofenceCircle) {
                        const newPos = [lat, lng];
                        geofenceMarker.setLatLng(newPos);
                        geofenceCircle.setLatLng(newPos);
                        geofenceMap.setView(newPos, 17);
                    }
                    reverseGeocode(lat, lng);
                    if (statusMsg) {
                        statusMsg.textContent = `Titik berhasil disetel ke lokasi GPS Anda: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                        statusMsg.className = 'text-[11px] text-emerald-600 font-semibold mt-1';
                    }
                },
                err => {
                    btn.disabled = false;
                    if (statusMsg) {
                        statusMsg.textContent = 'Gagal mendapatkan lokasi GPS: ' + (err.message || 'Izin ditolak.');
                        statusMsg.className = 'text-[11px] text-red-600 mt-1';
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }

        document.addEventListener('DOMContentLoaded', () => {
            initGeofenceMap();
        });
    </script>
</body>
</html>
