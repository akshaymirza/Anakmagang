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
        `gallery_images` TEXT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    @mysqli_query($conn, $sql);

    // Pastikan kolom gallery_images tersedia jika tabel sudah ada sebelumnya
    $colCheck = @mysqli_query($conn, "SHOW COLUMNS FROM `events_history` LIKE 'gallery_images'");
    if ($colCheck && mysqli_num_rows($colCheck) === 0) {
        @mysqli_query($conn, "ALTER TABLE `events_history` ADD COLUMN `gallery_images` TEXT NULL AFTER `image_url`");
    }
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
            'image_url' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1200&q=80',
            'gallery_images' => [
                'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1515187029135-18ee286d815b?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=80'
            ],
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
            'image_url' => 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=1200&q=80',
            'gallery_images' => [
                'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1528605248644-14dd04022da1?auto=format&fit=crop&w=1200&q=80'
            ],
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
            'image_url' => 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?auto=format&fit=crop&w=1200&q=80',
            'gallery_images' => [
                'https://images.unsplash.com/photo-1531403009284-440f080d1e12?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1200&q=80'
            ],
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
            'image_url' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80',
            'gallery_images' => [
                'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1200&q=80'
            ],
            'created_at' => date('Y-m-d H:i:s', strtotime('-60 days'))
        ]
    ];
}

function get_event_gallery_photos($ev) {
    $photos = [];
    if (!empty($ev['gallery_images'])) {
        if (is_array($ev['gallery_images'])) {
            $photos = $ev['gallery_images'];
        } elseif (is_string($ev['gallery_images'])) {
            $decoded = json_decode($ev['gallery_images'], true);
            if (is_array($decoded)) {
                $photos = $decoded;
            } else {
                $parts = preg_split('/[\r\n,]+/', $ev['gallery_images']);
                $photos = array_filter(array_map('trim', $parts));
            }
        }
    }

    $main = trim($ev['image_url'] ?? '');
    if (!empty($main) && !in_array($main, $photos)) {
        array_unshift($photos, $main);
    }

    // Jika hanya ada 1 foto, tambahkan foto dokumentasi pelengkap sesuai tipe event agar pengguna dapat menggeser beberapa foto
    if (count($photos) <= 1) {
        $type = strtolower($ev['event_type'] ?? '');
        if (strpos($type, 'hackathon') !== false) {
            $photos[] = 'https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=1200&q=80';
            $photos[] = 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1200&q=80';
            $photos[] = 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?auto=format&fit=crop&w=1200&q=80';
        } elseif (strpos($type, 'workshop') !== false || strpos($type, 'training') !== false) {
            $photos[] = 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80';
            $photos[] = 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80';
            $photos[] = 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1200&q=80';
        } elseif (strpos($type, 'welcome') !== false || strpos($type, 'party') !== false) {
            $photos[] = 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1200&q=80';
            $photos[] = 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=1200&q=80';
            $photos[] = 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1200&q=80';
        } else {
            $photos[] = 'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?auto=format&fit=crop&w=1200&q=80';
            $photos[] = 'https://images.unsplash.com/photo-1515187029135-18ee286d815b?auto=format&fit=crop&w=1200&q=80';
            $photos[] = 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=80';
        }
    }
    return array_values($photos);
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
                if (!empty($row['gallery_images'])) {
                    $row['gallery_images'] = is_array($row['gallery_images']) ? $row['gallery_images'] : json_decode($row['gallery_images'], true);
                }
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
            $image_url = 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1200&q=80';
        }

        // Kumpulkan foto galeri tambahan
        $gallery_images = [];
        $urls_input = trim($_POST['gallery_urls'] ?? '');
        if (!empty($urls_input)) {
            $lines = preg_split('/[\r\n,]+/', $urls_input);
            foreach ($lines as $line) {
                $u = trim($line);
                if (!empty($u)) {
                    $gallery_images[] = $u;
                }
            }
        }

        if (isset($_FILES['gallery_files']) && is_array($_FILES['gallery_files']['name'])) {
            foreach ($_FILES['gallery_files']['name'] as $g_idx => $g_name) {
                if (isset($_FILES['gallery_files']['error'][$g_idx]) && $_FILES['gallery_files']['error'][$g_idx] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($g_name, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        $new_filename = time() . '_' . uniqid() . '_' . $g_idx . '.' . $ext;
                        if (move_uploaded_file($_FILES['gallery_files']['tmp_name'][$g_idx], $upload_dir . $new_filename)) {
                            $gallery_images[] = 'uploads/events/' . $new_filename;
                        }
                    }
                }
            }
        }

        if (!empty($image_url) && !in_array($image_url, $gallery_images)) {
            array_unshift($gallery_images, $image_url);
        }
        $gallery_json = json_encode(array_values($gallery_images));

        if (!empty($name) && !empty($summary)) {
            $now = date('Y-m-d H:i:s');
            if ($conn) {
                init_events_db_table($conn);
                $stmt = mysqli_prepare($conn, "INSERT INTO `events_history` (event_name, event_type, event_date, location, organizer, summary, image_url, gallery_images, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "sssssssss", $name, $type, $date, $location, $organizer, $summary, $image_url, $gallery_json, $now);
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
                'gallery_images' => array_values($gallery_images),
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

        // Kumpulkan foto galeri tambahan
        $gallery_images = [];
        $urls_input = trim($_POST['gallery_urls'] ?? '');
        if (!empty($urls_input)) {
            $lines = preg_split('/[\r\n,]+/', $urls_input);
            foreach ($lines as $line) {
                $u = trim($line);
                if (!empty($u)) {
                    $gallery_images[] = $u;
                }
            }
        }

        if (isset($_FILES['gallery_files']) && is_array($_FILES['gallery_files']['name'])) {
            foreach ($_FILES['gallery_files']['name'] as $g_idx => $g_name) {
                if (isset($_FILES['gallery_files']['error'][$g_idx]) && $_FILES['gallery_files']['error'][$g_idx] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($g_name, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        $new_filename = time() . '_' . uniqid() . '_' . $g_idx . '.' . $ext;
                        if (move_uploaded_file($_FILES['gallery_files']['tmp_name'][$g_idx], $upload_dir . $new_filename)) {
                            $gallery_images[] = 'uploads/events/' . $new_filename;
                        }
                    }
                }
            }
        }

        $events = get_all_events_history(null, $events_json);
        $existing_event = null;
        foreach ($events as $ev) {
            if (intval($ev['id'] ?? 0) === $id) {
                $existing_event = $ev;
                break;
            }
        }

        if (empty($gallery_images) && !empty($existing_event['gallery_images'])) {
            $gallery_images = is_array($existing_event['gallery_images']) ? $existing_event['gallery_images'] : json_decode($existing_event['gallery_images'], true);
        }

        $effective_main_img = !empty($image_url) ? $image_url : ($existing_event['image_url'] ?? '');
        if (!empty($effective_main_img) && !in_array($effective_main_img, $gallery_images)) {
            array_unshift($gallery_images, $effective_main_img);
        }
        $gallery_json = json_encode(array_values($gallery_images));

        if ($id > 0 && !empty($name) && !empty($summary)) {
            if ($conn) {
                init_events_db_table($conn);
                if (!empty($image_url)) {
                    $stmt = mysqli_prepare($conn, "UPDATE `events_history` SET event_name=?, event_type=?, event_date=?, location=?, organizer=?, summary=?, image_url=?, gallery_images=? WHERE id=?");
                    mysqli_stmt_bind_param($stmt, "ssssssssi", $name, $type, $date, $location, $organizer, $summary, $image_url, $gallery_json, $id);
                } else {
                    $stmt = mysqli_prepare($conn, "UPDATE `events_history` SET event_name=?, event_type=?, event_date=?, location=?, organizer=?, summary=?, gallery_images=? WHERE id=?");
                    mysqli_stmt_bind_param($stmt, "sssssssi", $name, $type, $date, $location, $organizer, $summary, $gallery_json, $id);
                }
                if ($stmt) {
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }

            // Sync to JSON
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
                    $ev['gallery_images'] = array_values($gallery_images);
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

$events_list_raw = get_all_events_history($conn, $events_json);
$events_list = [];
foreach ($events_list_raw as $ev) {
    $ev['gallery_images'] = get_event_gallery_photos($ev);
    $events_list[] = $ev;
}
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
    <!-- Anime.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="shared-config.js"></script>
    <style>
        body { font-family: Inter, sans-serif; }
        .font-geist { font-family: Geist, sans-serif; }
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
        <section class="bg-gradient-to-r from-amber-600 via-indigo-900 to-slate-900 text-white py-12 px-6 shadow-md relative overflow-hidden">
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
                    <h1 class="font-geist text-2xl md:text-4xl font-extrabold tracking-tight">Histori & Galeri Event Magang</h1>
                    <p class="mt-3 text-slate-200 max-w-2xl text-sm leading-relaxed">
                        Arsip lengkap seluruh event besar, hackathon, workshop, dan momen berharga yang pernah diselenggarakan oleh Kedayweb untuk anak magang.
                    </p>
                </div>
                
                <?php if ($is_can_edit): ?>
                    <button onclick="openCreateEventModal()" class="shrink-0 px-6 py-3.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-sm rounded-xl transition-all shadow-xl flex items-center gap-2 active:scale-95">
                        <span class="material-symbols-outlined text-lg">add_circle</span>
                        <span>Tambah Event Baru</span>
                    </button>
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
                        $gallery_photos = $ev['gallery_images'] ?? [$img];
                    ?>
                        <article data-type="<?php echo $type; ?>" class="event-card bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl transition-all overflow-hidden grid grid-cols-1 md:grid-cols-12 gap-0 group">
                            <!-- Image Column (Interactive Focus Gallery Trigger) -->
                            <div onclick="openFocusGallery(<?php echo $index; ?>)" class="md:col-span-5 relative h-64 md:h-full min-h-[240px] bg-slate-900 overflow-hidden cursor-pointer group/img select-none" title="Klik untuk membuka Galeri Foto Event">
                                <img src="<?php echo $img; ?>" alt="<?php echo $name; ?>" class="w-full h-full object-cover group-hover/img:scale-105 transition-transform duration-500 ease-out" onerror="this.src='https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1200&q=80';"/>
                                
                                <!-- Event Type Badge (Top-Left) -->
                                <span class="absolute top-4 left-4 z-10 bg-slate-950/80 backdrop-blur-md text-amber-400 border border-amber-400/30 text-[11px] font-bold px-3 py-1 rounded-full uppercase tracking-wider flex items-center gap-1 shadow-md">
                                    <span class="material-symbols-outlined text-xs">workspace_premium</span>
                                    <?php echo $type; ?>
                                </span>

                                <!-- Photo Count Tag (Bottom-Right, Always Visible) -->
                                <span class="absolute bottom-3 right-3 z-10 bg-slate-950/85 backdrop-blur-md text-white text-[11px] font-bold px-3 py-1 rounded-lg border border-white/20 flex items-center gap-1.5 shadow-lg group-hover/img:scale-105 transition-all">
                                    <span class="material-symbols-outlined text-sm text-amber-400">photo_library</span>
                                    <span><?php echo count($gallery_photos); ?> Foto</span>
                                </span>

                                <!-- Interactive Focus Mode Hover Overlay -->
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/30 to-transparent backdrop-blur-[1px] opacity-0 group-hover/img:opacity-100 transition-all duration-300 flex flex-col items-center justify-center p-4 z-10">
                                    <div class="w-12 h-12 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center shadow-2xl transform scale-90 group-hover/img:scale-100 transition-all mb-2">
                                        <span class="material-symbols-outlined text-2xl font-bold">fullscreen</span>
                                    </div>
                                    <span class="px-4 py-1.5 rounded-full bg-white/95 text-slate-950 text-xs font-extrabold shadow-lg flex items-center gap-1.5 transform translate-y-2 group-hover/img:translate-y-0 transition-transform">
                                        <span class="material-symbols-outlined text-sm text-amber-600">collections</span>
                                        <span>Buka Galeri Foto (<?php echo count($gallery_photos); ?>)</span>
                                    </span>
                                    <span class="text-[10px] text-amber-200 mt-1 font-medium">Klik untuk masuk mode fokus</span>
                                </div>
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

                                    <p class="text-xs font-semibold text-slate-500 mb-3 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs text-red-500">location_on</span>
                                        <span><?php echo $location; ?></span>
                                    </p>

                                    <p class="text-slate-600 text-xs md:text-sm leading-relaxed mb-4">
                                        <?php echo $summary; ?>
                                    </p>

                                    <!-- Quick Action to Open Gallery Carousel -->
                                    <div class="mb-5">
                                        <button type="button" onclick="openFocusGallery(<?php echo $index; ?>)" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-900 font-bold text-xs border border-amber-200 hover:border-amber-300 transition-all active:scale-95 shadow-sm">
                                            <span class="material-symbols-outlined text-sm text-amber-600">photo_library</span>
                                            <span>Lihat Dokumentasi Foto (<?php echo count($gallery_photos); ?>)</span>
                                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Card Footer Actions -->
                                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                                    <span class="text-[11px] text-slate-400">ID Event: #EVT-<?php echo sprintf('%03d', $id); ?></span>

                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="openFocusGallery(<?php echo $index; ?>)" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">visibility</span>
                                            <span>Lihat Foto</span>
                                        </button>

                                        <?php if ($is_can_edit): ?>
                                            <!-- Action Buttons (Admin & Superadmin) -->
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
                                        <?php endif; ?>
                                    </div>
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

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- MODE FOKUS FOTO / FOCUS LIGHTBOX CAROUSEL MODAL (DARK CINEMA)   -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div id="focusGalleryModal" class="fixed inset-0 z-[100] hidden bg-slate-950/98 backdrop-blur-2xl flex flex-col justify-between overflow-hidden select-none" role="dialog" aria-modal="true" aria-label="Mode Fokus Dokumentasi Foto Event">
        
        <!-- Header Bar (Adaptive Mobile & Desktop) -->
        <header class="shrink-0 px-3 sm:px-6 md:px-8 py-2 sm:py-3.5 bg-slate-950/90 border-b border-white/10 backdrop-blur-md z-30 flex flex-col gap-1">
            <!-- Row 1: Event Badge + Title + Controls -->
            <div class="flex items-center justify-between gap-2">
                <!-- Left: Event Badge & Title -->
                <div class="min-w-0 flex items-center gap-2 flex-1">
                    <span id="focusEventBadge" class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-extrabold bg-amber-500/20 text-amber-300 border border-amber-400/30 uppercase tracking-wider shrink-0 shadow-sm">
                        Showcase
                    </span>
                    <h3 id="focusEventTitle" class="font-geist font-bold text-xs sm:text-sm md:text-base text-white truncate drop-shadow">
                        Nama Event
                    </h3>
                </div>

                <!-- Right: Counter Pill, Info Toggle, Autoplay & Exit Button -->
                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                    <!-- Photo Counter Pill -->
                    <div class="px-2 py-1 rounded-full bg-white/10 border border-white/15 text-[11px] sm:text-xs text-white flex items-center gap-1 font-medium shadow-inner">
                        <span class="material-symbols-outlined text-xs sm:text-sm text-amber-400">photo</span>
                        <span><strong id="focusCurrentNumber" class="text-amber-400 font-bold">1</strong>/<span id="focusTotalNumber" class="text-slate-300">4</span></span>
                    </div>

                    <!-- Autoplay Slideshow Toggle (Desktop / Tablet) -->
                    <button id="focusAutoplayBtn" onclick="toggleGalleryAutoplay()" class="hidden sm:flex items-center gap-1 px-2.5 py-1 rounded-full bg-white/10 hover:bg-white/20 text-white text-xs border border-white/15 transition-all active:scale-95 cursor-pointer" title="Slide Otomatis">
                        <span id="focusAutoplayIcon" class="material-symbols-outlined text-sm">play_arrow</span>
                        <span id="focusAutoplayLabel" class="text-[10px] font-semibold">Auto</span>
                    </button>

                    <!-- Info Toggle Button (Shows / Hides caption card on mobile) -->
                    <button onclick="toggleFocusInfoCard()" id="focusInfoToggleBtn" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center border border-white/15 transition-all active:scale-95 cursor-pointer" title="Lihat Ringkasan Acara">
                        <span class="material-symbols-outlined text-base">info</span>
                    </button>

                    <!-- Prominent Close Button 'X' -->
                    <button onclick="closeFocusGallery()" id="focusCloseBtn" class="group h-8 sm:h-9 px-2.5 sm:px-3.5 rounded-full bg-rose-500/25 hover:bg-rose-600 text-rose-200 hover:text-white font-bold text-xs transition-all border border-rose-500/40 hover:border-rose-500 shadow-xl flex items-center justify-center gap-1 active:scale-95 cursor-pointer" title="Keluar dari Mode Fokus (Esc)">
                        <span class="material-symbols-outlined text-lg leading-none group-hover:rotate-90 transition-transform">close</span>
                        <span class="hidden sm:inline font-bold">Tutup</span>
                    </button>
                </div>
            </div>

            <!-- Row 2: Date & Location (Clean, never crammed into 3 lines) -->
            <div class="flex items-center gap-2 text-[10px] sm:text-[11px] text-slate-300/80 truncate">
                <span id="focusEventDate" class="flex items-center gap-1 shrink-0">
                    <span class="material-symbols-outlined text-xs text-amber-400">calendar_month</span>
                    <span>-</span>
                </span>
                <span class="text-slate-500">•</span>
                <span id="focusEventLocation" class="flex items-center gap-1 truncate">
                    <span class="material-symbols-outlined text-xs text-rose-400">location_on</span>
                    <span>-</span>
                </span>
            </div>
        </header>

        <!-- Center Stage: Photo Display with Live Swipe Physics & Zoom -->
        <div id="focusStage" class="flex-1 relative flex flex-col items-center justify-center p-1 sm:p-4 overflow-hidden touch-none cursor-zoom-out">
            
            <!-- Dark Backdrop Click to Close -->
            <div onclick="closeFocusGallery()" class="absolute inset-0 z-0 cursor-zoom-out" title="Klik di mana saja selain foto untuk keluar"></div>

            <!-- Side Chevrons (Desktop & Tablet) -->
            <button onclick="prevFocusPhoto(event)" class="hidden md:flex absolute left-4 lg:left-8 z-30 w-12 h-12 rounded-full bg-slate-900/80 hover:bg-amber-500 text-white hover:text-slate-950 backdrop-blur-md border border-white/20 hover:border-amber-400 items-center justify-center transition-all shadow-[0_10px_25px_rgba(0,0,0,0.5)] active:scale-90 cursor-pointer" title="Foto Sebelumnya (Panah Kiri)">
                <span class="material-symbols-outlined text-3xl">chevron_left</span>
            </button>

            <button onclick="nextFocusPhoto(event)" class="hidden md:flex absolute right-4 lg:right-8 z-30 w-12 h-12 rounded-full bg-slate-900/80 hover:bg-amber-500 text-white hover:text-slate-950 backdrop-blur-md border border-white/20 hover:border-amber-400 items-center justify-center transition-all shadow-[0_10px_25px_rgba(0,0,0,0.5)] active:scale-90 cursor-pointer" title="Foto Selanjutnya (Panah Kanan)">
                <span class="material-symbols-outlined text-3xl">chevron_right</span>
            </button>

            <!-- Main Interactive Photo Display -->
            <div id="focusImageContainer" class="relative z-10 max-w-full flex flex-col items-center justify-center pointer-events-none">
                <div class="relative overflow-hidden rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.9)] max-w-full flex items-center justify-center pointer-events-auto">
                    <img id="focusMainImage" src="" alt="Dokumentasi Event" class="max-h-[58vh] sm:max-h-[64vh] md:max-h-[70vh] w-auto max-w-[96vw] sm:max-w-[90vw] md:max-w-4xl object-contain transition-transform duration-200 select-none cursor-grab active:cursor-grabbing will-change-transform"/>
                    
                    <!-- Zoom badge indicator -->
                    <div id="focusZoomHint" class="absolute bottom-2 right-2 px-2 py-0.5 rounded-md bg-black/60 text-white/80 text-[9px] font-semibold backdrop-blur-sm pointer-events-none opacity-0 transition-opacity flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs">zoom_in</span>
                        <span id="focusZoomText">Ketuk 2x untuk zoom</span>
                    </div>
                </div>

                <!-- Dynamic Animated Pagination Dots Pill (Directly below photo) -->
                <div id="focusDotsPagination" class="flex items-center justify-center gap-1.5 mt-2.5 z-20 pointer-events-auto">
                    <!-- Dots injected via JS -->
                </div>
            </div>

            <!-- Expandable Floating Info Card (Bottom Sheet on Mobile) -->
            <div id="focusInfoCard" class="absolute bottom-2 left-3 right-3 sm:left-auto sm:right-auto sm:max-w-xl z-30 bg-slate-900/95 border border-white/20 backdrop-blur-xl rounded-2xl p-4 shadow-2xl transition-all duration-300 transform translate-y-full opacity-0 pointer-events-none">
                <div class="flex items-start justify-between gap-3 mb-2 pb-2 border-b border-white/10">
                    <div>
                        <span id="focusInfoBadge" class="text-[10px] font-bold text-amber-400 uppercase tracking-wider">Showcase</span>
                        <h4 id="focusInfoTitle" class="font-geist font-bold text-sm text-white leading-tight">Detail Acara</h4>
                    </div>
                    <button onclick="toggleFocusInfoCard(false)" class="w-6 h-6 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
                <p id="focusInfoSummary" class="text-xs text-slate-300 leading-relaxed max-h-36 overflow-y-auto pr-1"></p>
            </div>
        </div>

        <!-- Bottom Footer: Thumbnails Tray & Quick Gestures -->
        <footer class="shrink-0 px-2 sm:px-4 py-2 sm:py-3 bg-slate-950/90 border-t border-white/10 backdrop-blur-md flex flex-col items-center justify-center gap-1.5 z-30 cursor-zoom-out">
            <!-- Mobile Chevrons + Thumbnails Row -->
            <div class="w-full flex items-center justify-between sm:justify-center gap-2 max-w-2xl px-1">
                <!-- Mobile Prev Mini Button -->
                <button onclick="prevFocusPhoto(event)" class="sm:hidden w-8 h-8 rounded-full bg-white/10 active:bg-amber-500 text-white active:text-slate-950 flex items-center justify-center shrink-0 border border-white/15 active:scale-95 cursor-pointer" title="Sebelumnya">
                    <span class="material-symbols-outlined text-lg">chevron_left</span>
                </button>

                <!-- Scrollable Thumbnails Strip -->
                <div id="focusThumbnailsStrip" class="flex items-center gap-2 overflow-x-auto max-w-full py-0.5 px-1 no-scrollbar scroll-smooth flex-1 justify-center">
                    <!-- Dynamically populated via JS -->
                </div>

                <!-- Mobile Next Mini Button -->
                <button onclick="nextFocusPhoto(event)" class="sm:hidden w-8 h-8 rounded-full bg-white/10 active:bg-amber-500 text-white active:text-slate-950 flex items-center justify-center shrink-0 border border-white/15 active:scale-95 cursor-pointer" title="Selanjutnya">
                    <span class="material-symbols-outlined text-lg">chevron_right</span>
                </button>
            </div>

            <!-- Dynamic Guide Hint -->
            <div class="text-[10px] sm:text-[11px] text-slate-400 flex items-center gap-2 pointer-events-none">
                <span class="sm:hidden text-amber-200/90 font-medium flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">touch_app</span>
                    <span>Ketuk di mana saja di luar foto untuk keluar</span>
                </span>
                <span class="hidden sm:inline">Klik di mana saja selain di foto untuk keluar, atau tekan tombol <kbd class="px-1.5 py-0.5 rounded bg-rose-500/30 text-rose-200 font-bold text-[10px]">X</kbd> / <kbd class="px-1.5 py-0.5 rounded bg-white/10 text-white font-mono text-[10px]">Esc</kbd></span>
            </div>
        </footer>
    </div>

    <!-- Modal Form (Tambah / Edit Event) -->
    <div id="eventModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 max-h-[92vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4 sticky top-0 bg-white z-10">
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
                    <label class="block text-xs font-bold text-slate-700 mb-1">Upload Foto Utama Event</label>
                    <input type="file" name="image_file" accept="image/*" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50"/>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Atau Gunakan URL Foto Utama (Opsional)</label>
                    <input type="url" name="image_url" id="eventImageUrl" placeholder="https://images.unsplash.com/..." class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl outline-none focus:border-amber-500"/>
                </div>

                <!-- Bagian Upload Beberapa Foto Galeri Tambahan -->
                <div class="p-3 bg-amber-50/70 border border-amber-200/80 rounded-xl space-y-3">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-amber-900">
                        <span class="material-symbols-outlined text-sm text-amber-600">collections</span>
                        <span>Galeri Foto Dokumentasi Tambahan (Bisa Digeser)</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Upload Beberapa Foto Sekaligus</label>
                        <input type="file" name="gallery_files[]" accept="image/*" multiple class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-white"/>
                        <p class="text-[10px] text-slate-500 mt-1">Dapat memilih lebih dari 1 file foto dokumentasi.</p>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Atau Masukkan URL Foto Tambahan (Satu per baris)</label>
                        <textarea name="gallery_urls" id="eventGalleryUrls" rows="2" placeholder="https://images.unsplash.com/...&#10;https://images.unsplash.com/..." class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg outline-none focus:border-amber-500 bg-white font-mono"></textarea>
                    </div>
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
        const allEventsData = <?php echo json_encode($events_list, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        let currentEventIdx = 0;
        let currentPhotoIdx = 0;
        let autoplayTimer = null;
        
        // Dynamic Touch, Drag & Backdrop Variables
        let touchStartX = 0;
        let touchStartY = 0;
        let touchCurrentX = 0;
        let isTouching = false;
        let isZoomed = false;
        let lastTapTime = 0;
        let isInfoCardOpen = false;
        let focusModalOpenedAt = 0;
        let hasTouchMoved = false;
        let hasMouseMoved = false;

        // ═══ FOCUS LIGHTBOX CONTROLS ═══
        function openFocusGallery(eventIdx, photoIdx = 0) {
            if (!allEventsData[eventIdx]) return;
            currentEventIdx = eventIdx;
            currentPhotoIdx = photoIdx;
            isZoomed = false;
            isInfoCardOpen = false;
            focusModalOpenedAt = Date.now();
            hasTouchMoved = false;
            hasMouseMoved = false;

            const modal = document.getElementById('focusGalleryModal');
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            renderFocusModal();
            toggleFocusInfoCard(false);

            if (window.anime) {
                anime({
                    targets: modal,
                    opacity: [0, 1],
                    duration: 250,
                    easing: 'easeOutQuad'
                });
                anime({
                    targets: '#focusImageContainer',
                    scale: [0.93, 1],
                    opacity: [0.3, 1],
                    duration: 320,
                    easing: 'easeOutCubic'
                });
            }
        }

        function closeFocusGallery() {
            stopGalleryAutoplay();
            isZoomed = false;
            resetImageTransform();

            const modal = document.getElementById('focusGalleryModal');
            if (!modal || modal.classList.contains('hidden')) return;

            if (window.anime) {
                anime({
                    targets: modal,
                    opacity: [1, 0],
                    duration: 200,
                    easing: 'easeInQuad',
                    complete: function() {
                        modal.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    }
                });
            } else {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        function resetImageTransform() {
            const img = document.getElementById('focusMainImage');
            if (img) {
                img.style.transition = 'none';
                img.style.transform = 'translateX(0px) rotate(0deg) scale(1)';
                img.style.cursor = 'grab';
            }
        }

        function renderFocusModal() {
            const ev = allEventsData[currentEventIdx];
            if (!ev) return;

            const photos = ev.gallery_images && ev.gallery_images.length ? ev.gallery_images : [ev.image_url];
            if (currentPhotoIdx >= photos.length) currentPhotoIdx = 0;

            // Header Texts
            document.getElementById('focusEventBadge').innerText = ev.event_type || 'Event';
            document.getElementById('focusEventTitle').innerText = ev.event_name || 'Event Histori';
            
            // Format Date
            let dateStr = ev.event_date || '';
            if (dateStr) {
                const parts = dateStr.split('-');
                if (parts.length === 3) {
                    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                    const mIdx = parseInt(parts[1], 10) - 1;
                    dateStr = parseInt(parts[2], 10) + ' ' + (months[mIdx] || parts[1]) + ' ' + parts[0];
                }
            }
            document.getElementById('focusEventDate').innerHTML = `<span class="material-symbols-outlined text-xs text-amber-400">calendar_month</span><span>${dateStr}</span>`;
            document.getElementById('focusEventLocation').innerHTML = `<span class="material-symbols-outlined text-xs text-rose-400">location_on</span><span>${ev.location || 'Kedayweb'}</span>`;

            // Info Card texts
            document.getElementById('focusInfoBadge').innerText = ev.event_type || 'Event Magang';
            document.getElementById('focusInfoTitle').innerText = ev.event_name || 'Detail Acara';
            document.getElementById('focusInfoSummary').innerText = ev.summary || 'Tidak ada deskripsi tambahan.';

            // Photo & Counter
            document.getElementById('focusCurrentNumber').innerText = (currentPhotoIdx + 1);
            document.getElementById('focusTotalNumber').innerText = photos.length;
            
            const img = document.getElementById('focusMainImage');
            img.src = photos[currentPhotoIdx];
            resetImageTransform();

            renderFocusDots();
            renderFocusThumbnails();
        }

        function updateFocusDetails() {
            const ev = allEventsData[currentEventIdx];
            if (!ev) return;
            document.getElementById('focusCurrentNumber').innerText = (currentPhotoIdx + 1);
            renderFocusDots();
            renderFocusThumbnails();
        }

        function renderFocusDots() {
            const container = document.getElementById('focusDotsPagination');
            const ev = allEventsData[currentEventIdx];
            if (!container || !ev) return;
            const photos = ev.gallery_images && ev.gallery_images.length ? ev.gallery_images : [ev.image_url];

            container.innerHTML = '';
            if (photos.length <= 1) return;

            photos.forEach((_, idx) => {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.onclick = () => goToFocusPhoto(idx);
                dot.setAttribute('aria-label', `Foto ${idx + 1}`);
                dot.className = idx === currentPhotoIdx
                    ? 'w-6 h-1.5 sm:w-7 sm:h-2 bg-amber-400 rounded-full shadow-[0_0_8px_rgba(251,191,36,0.7)] transition-all duration-300'
                    : 'w-1.5 h-1.5 sm:w-2 sm:h-2 bg-white/30 hover:bg-white/60 rounded-full transition-all duration-200 cursor-pointer';
                container.appendChild(dot);
            });
        }

        function toggleFocusInfoCard(forceState) {
            const card = document.getElementById('focusInfoCard');
            const btn = document.getElementById('focusInfoToggleBtn');
            if (!card) return;

            isInfoCardOpen = (typeof forceState === 'boolean') ? forceState : !isInfoCardOpen;

            if (isInfoCardOpen) {
                card.classList.remove('translate-y-full', 'opacity-0', 'pointer-events-none');
                card.classList.add('translate-y-0', 'opacity-100', 'pointer-events-auto');
                if (btn) {
                    btn.classList.add('bg-amber-500', 'text-slate-950', 'border-amber-400');
                    btn.classList.remove('bg-white/10', 'text-white', 'border-white/15');
                }
            } else {
                card.classList.add('translate-y-full', 'opacity-0', 'pointer-events-none');
                card.classList.remove('translate-y-0', 'opacity-100', 'pointer-events-auto');
                if (btn) {
                    btn.classList.remove('bg-amber-500', 'text-slate-950', 'border-amber-400');
                    btn.classList.add('bg-white/10', 'text-white', 'border-white/15');
                }
            }
        }

        function togglePhotoZoom(e) {
            const img = document.getElementById('focusMainImage');
            const hint = document.getElementById('focusZoomHint');
            const text = document.getElementById('focusZoomText');
            if (!img) return;

            isZoomed = !isZoomed;
            if (isZoomed) {
                img.style.transition = 'transform 0.25s cubic-bezier(0.2, 0.9, 0.3, 1)';
                img.style.transform = 'scale(1.85)';
                img.style.cursor = 'zoom-out';
                if (hint && text) {
                    text.innerText = 'Zoom Aktif (Ketuk 2x reset)';
                    hint.classList.remove('opacity-0');
                    setTimeout(() => hint && hint.classList.add('opacity-0'), 1500);
                }
            } else {
                img.style.transition = 'transform 0.25s cubic-bezier(0.2, 0.9, 0.3, 1)';
                img.style.transform = 'scale(1)';
                img.style.cursor = 'grab';
            }
        }

        function nextFocusPhoto(e) {
            if (e && e.stopPropagation) e.stopPropagation();
            const ev = allEventsData[currentEventIdx];
            if (!ev) return;
            const photos = ev.gallery_images && ev.gallery_images.length ? ev.gallery_images : [ev.image_url];
            if (photos.length <= 1) return;
            isZoomed = false;
            currentPhotoIdx = (currentPhotoIdx + 1) % photos.length;
            animatePhotoSlide('next');
        }

        function prevFocusPhoto(e) {
            if (e && e.stopPropagation) e.stopPropagation();
            const ev = allEventsData[currentEventIdx];
            if (!ev) return;
            const photos = ev.gallery_images && ev.gallery_images.length ? ev.gallery_images : [ev.image_url];
            if (photos.length <= 1) return;
            isZoomed = false;
            currentPhotoIdx = (currentPhotoIdx - 1 + photos.length) % photos.length;
            animatePhotoSlide('prev');
        }

        function goToFocusPhoto(idx) {
            if (idx === currentPhotoIdx) return;
            const direction = idx > currentPhotoIdx ? 'next' : 'prev';
            isZoomed = false;
            currentPhotoIdx = idx;
            animatePhotoSlide(direction);
        }

        function animatePhotoSlide(direction) {
            const ev = allEventsData[currentEventIdx];
            const photos = ev.gallery_images && ev.gallery_images.length ? ev.gallery_images : [ev.image_url];
            const img = document.getElementById('focusMainImage');
            const newSrc = photos[currentPhotoIdx];
            const offset = direction === 'next' ? 40 : -40;

            if (window.anime) {
                anime({
                    targets: img,
                    translateX: [0, -offset],
                    opacity: [1, 0.3],
                    scale: [1, 0.94],
                    duration: 130,
                    easing: 'easeInQuad',
                    complete: function() {
                        img.src = newSrc;
                        updateFocusDetails();
                        anime({
                            targets: img,
                            translateX: [offset, 0],
                            opacity: [0.3, 1],
                            scale: [0.94, 1],
                            duration: 220,
                            easing: 'easeOutCubic',
                            complete: function() {
                                resetImageTransform();
                            }
                        });
                    }
                });
            } else {
                img.src = newSrc;
                resetImageTransform();
                updateFocusDetails();
            }
        }

        function renderFocusThumbnails() {
            const container = document.getElementById('focusThumbnailsStrip');
            const ev = allEventsData[currentEventIdx];
            if (!container || !ev) return;
            const photos = ev.gallery_images && ev.gallery_images.length ? ev.gallery_images : [ev.image_url];

            container.innerHTML = '';
            photos.forEach((src, idx) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.onclick = () => goToFocusPhoto(idx);
                btn.title = `Foto ${idx + 1}`;
                btn.className = `relative shrink-0 w-11 h-9 sm:w-14 sm:h-11 md:w-16 md:h-12 rounded-lg sm:rounded-xl overflow-hidden border transition-all duration-200 cursor-pointer ${
                    idx === currentPhotoIdx
                        ? 'border-amber-400 ring-2 ring-amber-400/60 scale-105 shadow-lg opacity-100'
                        : 'border-white/20 opacity-40 hover:opacity-90 hover:border-white/50'
                }`;
                btn.innerHTML = `<img src="${src}" alt="Thumb ${idx + 1}" class="w-full h-full object-cover"/>`;
                container.appendChild(btn);
            });

            const activeBtn = container.children[currentPhotoIdx];
            if (activeBtn) {
                activeBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
        }

        function toggleGalleryAutoplay() {
            if (autoplayTimer) {
                stopGalleryAutoplay();
            } else {
                startGalleryAutoplay();
            }
        }

        function startGalleryAutoplay() {
            stopGalleryAutoplay();
            const icon = document.getElementById('focusAutoplayIcon');
            const label = document.getElementById('focusAutoplayLabel');
            const btn = document.getElementById('focusAutoplayBtn');
            if (icon) icon.innerText = 'pause';
            if (label) label.innerText = 'Jeda';
            if (btn) {
                btn.classList.add('bg-amber-500', 'text-slate-950', 'border-amber-400');
                btn.classList.remove('bg-white/10', 'text-white', 'border-white/15');
            }
            autoplayTimer = setInterval(() => {
                nextFocusPhoto();
            }, 3500);
        }

        function stopGalleryAutoplay() {
            if (autoplayTimer) {
                clearInterval(autoplayTimer);
                autoplayTimer = null;
            }
            const icon = document.getElementById('focusAutoplayIcon');
            const label = document.getElementById('focusAutoplayLabel');
            const btn = document.getElementById('focusAutoplayBtn');
            if (icon) icon.innerText = 'play_arrow';
            if (label) label.innerText = 'Auto';
            if (btn) {
                btn.classList.remove('bg-amber-500', 'text-slate-950', 'border-amber-400');
                btn.classList.add('bg-white/10', 'text-white', 'border-white/15');
            }
        }

        // ═══ DYNAMIC REALTIME TOUCH & SWIPE PHYSICS + CLICK ANYWHERE TO CLOSE ═══
        (function initDynamicMobileGestures() {
            const stage = document.getElementById('focusStage');
            const img = document.getElementById('focusMainImage');
            const modal = document.getElementById('focusGalleryModal');
            if (!stage || !img) return;

            // Touch events on mobile
            stage.addEventListener('touchstart', e => {
                if (e.touches.length === 1 && !isZoomed) {
                    touchStartX = e.touches[0].clientX;
                    touchStartY = e.touches[0].clientY;
                    touchCurrentX = touchStartX;
                    isTouching = true;
                    hasTouchMoved = false;
                    img.style.transition = 'none';
                }
            }, { passive: true });

            stage.addEventListener('touchmove', e => {
                if (!isTouching || isZoomed) return;
                touchCurrentX = e.touches[0].clientX;
                const deltaX = touchCurrentX - touchStartX;
                const deltaY = e.touches[0].clientY - touchStartY;

                if (Math.abs(deltaX) > 8 || Math.abs(deltaY) > 8) {
                    hasTouchMoved = true;
                }

                // Only track horizontal drag if greater than vertical movement
                if (Math.abs(deltaX) > Math.abs(deltaY)) {
                    const tilt = deltaX * 0.02; // subtle tilt angle
                    const scale = Math.max(0.92, 1 - Math.abs(deltaX) * 0.0006);
                    img.style.transform = `translateX(${deltaX * 0.85}px) rotate(${tilt}deg) scale(${scale})`;
                }
            }, { passive: true });

            stage.addEventListener('touchend', e => {
                if (!isTouching || isZoomed) return;
                isTouching = false;
                const deltaX = touchCurrentX - touchStartX;
                img.style.transition = 'transform 0.28s cubic-bezier(0.2, 0.9, 0.3, 1)';

                if (deltaX < -45) {
                    // Pull left -> Next Photo
                    nextFocusPhoto();
                } else if (deltaX > 45) {
                    // Pull right -> Prev Photo
                    prevFocusPhoto();
                } else {
                    // Spring bounce back to center
                    img.style.transform = 'translateX(0px) rotate(0deg) scale(1)';
                }

                // Keep hasTouchMoved flag for a moment so following click is not misidentified
                if (hasTouchMoved) {
                    setTimeout(() => {
                        hasTouchMoved = false;
                    }, 250);
                }
            });

            // Double tap on mobile to zoom
            img.addEventListener('click', e => {
                const now = new Date().getTime();
                const tapDiff = now - lastTapTime;
                if (tapDiff < 300 && tapDiff > 0) {
                    e.stopPropagation();
                    togglePhotoZoom(e);
                }
                lastTapTime = now;
            });

            // Mouse drag support for desktop (attached specifically to the image)
            let isMouseDragging = false;
            let mouseOriginX = 0;

            img.addEventListener('mousedown', e => {
                if (isZoomed) return;
                isMouseDragging = true;
                hasMouseMoved = false;
                mouseOriginX = e.clientX;
                img.style.transition = 'none';
            });

            window.addEventListener('mousemove', e => {
                if (!isMouseDragging || isZoomed) return;
                const delta = e.clientX - mouseOriginX;
                if (Math.abs(delta) > 6) {
                    hasMouseMoved = true;
                }
                const tilt = delta * 0.015;
                img.style.transform = `translateX(${delta * 0.75}px) rotate(${tilt}deg) scale(0.98)`;
            });

            window.addEventListener('mouseup', e => {
                if (!isMouseDragging) return;
                isMouseDragging = false;
                img.style.transition = 'transform 0.25s cubic-bezier(0.2, 0.9, 0.3, 1)';
                const delta = e.clientX - mouseOriginX;
                if (delta < -50) {
                    nextFocusPhoto();
                } else if (delta > 50) {
                    prevFocusPhoto();
                } else {
                    img.style.transform = 'translateX(0px) rotate(0deg) scale(1)';
                }
                if (hasMouseMoved) {
                    setTimeout(() => {
                        hasMouseMoved = false;
                    }, 200);
                }
            });

            // ═══ KLIK / KETUK DI MANA SAJA SELAIN DI FOTO UNTUK KELUAR ═══
            if (modal) {
                modal.addEventListener('click', e => {
                    // Abaikan jika modal baru saja dibuka (< 200ms) untuk mencegah ghost-click
                    if (Date.now() - focusModalOpenedAt < 200) return;

                    // Abaikan jika interaksi merupakan hasil geser foto (swipe / drag)
                    if (hasTouchMoved || hasMouseMoved) return;

                    // JANGAN tutup jika yang diklik adalah foto utama itu sendiri
                    if (e.target.id === 'focusMainImage' || e.target.closest('#focusMainImage')) {
                        return;
                    }

                    // JANGAN tutup jika yang diklik adalah tombol kontrol interaktif (panah, dots, thumbnail, atau drawer info)
                    if (e.target.closest('button') || 
                        e.target.closest('#focusDotsPagination') || 
                        e.target.closest('#focusThumbnailsStrip') || 
                        e.target.closest('#focusInfoCard')) {
                        return;
                    }

                    // Klik di mana saja selain foto dan tombol kontrol -> Keluar dari Mode Fokus!
                    closeFocusGallery();
                });
            }
        })();

        // Keyboard navigation (Arrow keys + Esc)
        document.addEventListener('keydown', e => {
            const modal = document.getElementById('focusGalleryModal');
            if (!modal || modal.classList.contains('hidden')) return;

            if (e.key === 'Escape') {
                closeFocusGallery();
            } else if (e.key === 'ArrowRight') {
                nextFocusPhoto();
            } else if (e.key === 'ArrowLeft') {
                prevFocusPhoto();
            }
        });

        // ═══ EVENT FORM MODAL (TAMBAH / EDIT) ═══
        function openCreateEventModal() {
            document.getElementById('eventFormAction').value = 'create_event';
            document.getElementById('eventId').value = '';
            document.getElementById('eventModalHeading').innerHTML = '<span class="material-symbols-outlined text-amber-500">stars</span><span>Tambah Event Baru</span>';
            document.getElementById('eventForm').reset();
            const gUrls = document.getElementById('eventGalleryUrls');
            if (gUrls) gUrls.value = '';
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

            const gUrls = document.getElementById('eventGalleryUrls');
            if (gUrls) {
                if (Array.isArray(ev.gallery_images)) {
                    gUrls.value = ev.gallery_images.filter(u => u !== ev.image_url).join('\n');
                } else if (typeof ev.gallery_images === 'string') {
                    try {
                        const parsed = JSON.parse(ev.gallery_images);
                        gUrls.value = Array.isArray(parsed) ? parsed.filter(u => u !== ev.image_url).join('\n') : ev.gallery_images;
                    } catch(e) {
                        gUrls.value = ev.gallery_images;
                    }
                } else {
                    gUrls.value = '';
                }
            }

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
