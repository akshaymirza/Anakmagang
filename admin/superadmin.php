<?php
require_once __DIR__ . '/../session.php';
require_superadmin();
require_once __DIR__ . '/../sop_helper.php';
$sop_data = get_sop_data();
require_once __DIR__ . '/../about_helper.php';
require_once __DIR__ . '/../legal_helper.php';
$privacy_data = get_legal_data('privacy');
$terms_data   = get_legal_data('terms');
$about_file = get_about_file_path();

// Database connection attempt
$conn = null;
if (file_exists(__DIR__ . '/../Login/koneksi.php')) {
    include_once __DIR__ . '/../Login/koneksi.php';
}
$about_data = get_about_content($conn, $about_file);

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

$footer_settings_file = __DIR__ . '/../uploads/footer_settings.json';

function get_superadmin_footer_settings($file) {
    $defaults = [
        'location'          => 'Kedayweb Software House & Digital Academy',
        'work_hours'        => 'Senin - Sabtu (08:00 - 16:00 WIB)',
        'whatsapp'          => '628123456789',
        'whatsapp_label'    => '+62 812-3456-789',
        'instagram'         => 'https://www.instagram.com/anak_magang.id?utm_source=ig_web_button_share_sheet&stkn=ZDNlZDc0MzIxNw==',
        'threads'           => 'https://www.threads.net/@anak_magang.id',
        'tiktok'            => 'https://www.tiktok.com/@anak_magang.id?is_from_webapp=1&sender_device=pc',
        'workspace_version' => 'Internship Workspace v2.0'
    ];
    if (file_exists($file)) {
        $c = json_decode(file_get_contents($file), true);
        if (is_array($c)) return array_merge($defaults, $c);
    }
    return $defaults;
}

// Handle POST actions
$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_footer_settings') {
        $footer_data = [
            'location'          => trim($_POST['footer_location'] ?? ''),
            'work_hours'        => trim($_POST['footer_work_hours'] ?? ''),
            'whatsapp'          => trim($_POST['footer_whatsapp'] ?? ''),
            'whatsapp_label'    => trim($_POST['footer_whatsapp_label'] ?? ''),
            'instagram'         => trim($_POST['footer_instagram'] ?? ''),
            'threads'           => trim($_POST['footer_threads'] ?? ''),
            'tiktok'            => trim($_POST['footer_tiktok'] ?? ''),
            'workspace_version' => trim($_POST['footer_workspace_version'] ?? 'Internship Workspace v2.0')
        ];
        file_put_contents($footer_settings_file, json_encode($footer_data, JSON_PRETTY_PRINT));
        $msg = "Pengaturan Footer berhasil disimpan dan diperbarui!";
        $msg_type = "success";
    } elseif ($action === 'save_sop_header') {
        $sop_data = get_sop_data();
        $sop_data['badge']    = trim($_POST['sop_badge'] ?? 'Standar Operasional Prosedur');
        $sop_data['title']    = trim($_POST['sop_title'] ?? 'SOP Peserta Magang Kedayweb');
        $sop_data['subtitle'] = trim($_POST['sop_subtitle'] ?? 'Kedayweb × Kulino House — wajib dibaca dan dipahami oleh seluruh peserta magang.');
        save_sop_data($sop_data);
        $msg = "Header halaman SOP berhasil diperbarui!";
        $msg_type = "success";
    } elseif ($action === 'add_sop_section') {
        $sop_data = get_sop_data();
        $title = trim($_POST['title'] ?? '');
        $nav_title = trim($_POST['nav_title'] ?? '') ?: $title;
        $style = trim($_POST['style'] ?? 'default');
        $content = trim($_POST['content'] ?? '');

        if (empty($title)) {
            $msg = "Judul pasal SOP tidak boleh kosong!";
            $msg_type = "danger";
        } else {
            $new_sec = [
                'id'        => 'sop-' . time(),
                'title'     => $title,
                'nav_title' => $nav_title,
                'style'     => $style,
                'content'   => $content
            ];
            $sop_data['sections'][] = $new_sec;
            save_sop_data($sop_data);
            $msg = "Pasal baru '" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "' berhasil ditambahkan!";
            $msg_type = "success";
        }
    } elseif ($action === 'edit_sop_section') {
        $sop_data = get_sop_data();
        $idx = isset($_POST['section_index']) ? (int)$_POST['section_index'] : -1;
        $title = trim($_POST['title'] ?? '');
        $nav_title = trim($_POST['nav_title'] ?? '') ?: $title;
        $style = trim($_POST['style'] ?? 'default');
        $content = trim($_POST['content'] ?? '');

        if ($idx >= 0 && isset($sop_data['sections'][$idx])) {
            $sop_data['sections'][$idx]['title']     = $title;
            $sop_data['sections'][$idx]['nav_title'] = $nav_title;
            $sop_data['sections'][$idx]['style']     = $style;
            $sop_data['sections'][$idx]['content']   = $content;
            save_sop_data($sop_data);
            $msg = "Perubahan pasal '" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "' berhasil disimpan!";
            $msg_type = "success";
        } else {
            $msg = "Gagal memperbarui pasal: data tidak ditemukan.";
            $msg_type = "danger";
        }
    } elseif ($action === 'delete_sop_section') {
        $sop_data = get_sop_data();
        $idx = isset($_POST['section_index']) ? (int)$_POST['section_index'] : -1;
        if ($idx >= 0 && isset($sop_data['sections'][$idx])) {
            $deleted_title = $sop_data['sections'][$idx]['title'] ?? 'Pasal';
            array_splice($sop_data['sections'], $idx, 1);
            save_sop_data($sop_data);
            $msg = "Pasal '" . htmlspecialchars($deleted_title, ENT_QUOTES, 'UTF-8') . "' berhasil dihapus dari SOP!";
            $msg_type = "success";
        } else {
            $msg = "Gagal menghapus pasal: data tidak ditemukan.";
            $msg_type = "danger";
        }
    } elseif ($action === 'move_sop_section') {
        $sop_data = get_sop_data();
        $idx = (int)($_POST['section_index'] ?? -1);
        $dir = (int)($_POST['direction'] ?? 0);
        $target = $idx + $dir;
        if (isset($sop_data['sections'][$idx]) && isset($sop_data['sections'][$target])) {
            $temp = $sop_data['sections'][$idx];
            $sop_data['sections'][$idx] = $sop_data['sections'][$target];
            $sop_data['sections'][$target] = $temp;
            save_sop_data($sop_data);
            $msg = "Urutan pasal SOP berhasil diubah!";
            $msg_type = "success";
        }
    } elseif ($action === 'reset_sop_data') {
        reset_sop_data();
        $sop_data = get_sop_data();
        $msg = "Standar Operasional Prosedur (SOP) berhasil dikembalikan ke standar awal!";
        $msg_type = "success";
    } elseif ($action === 'save_sop_data') {
        $raw_sections = $_POST['sections_json'] ?? '';
        $sections = json_decode($raw_sections, true);
        if (is_array($sections)) {
            $sop_data = [
                'badge'    => trim($_POST['sop_badge'] ?? 'Standar Operasional Prosedur'),
                'title'    => trim($_POST['sop_title'] ?? 'SOP Peserta Magang Kedayweb'),
                'subtitle' => trim($_POST['sop_subtitle'] ?? 'Kedayweb × Kulino House — wajib dibaca dan dipahami oleh seluruh peserta magang.'),
                'sections' => $sections
            ];
            save_sop_data($sop_data);
            $msg = "Pengaturan dan isi Standar Operasional Prosedur (SOP) berhasil disimpan!";
            $msg_type = "success";
        }
        } elseif ($action === 'add_about_section') {
        $about_data = get_about_content($conn, $about_file);
        $sec_title   = trim($_POST['section_title'] ?? '');
        $sec_badge   = trim($_POST['section_badge'] ?? 'Informasi');
        $sec_icon    = trim($_POST['section_icon'] ?? 'info');
        $sec_content = trim($_POST['section_content'] ?? '');
        $sec_style   = trim($_POST['section_style'] ?? 'two_column');
        $sec_image   = trim($_POST['section_image'] ?? '');

        if (isset($_FILES['section_image_file']) && $_FILES['section_image_file']['error'] === UPLOAD_ERR_OK) {
            $uploaded = handle_about_image_upload($_FILES['section_image_file'], 'section');
            if ($uploaded) {
                $sec_image = $uploaded;
            }
        }

        if (!empty($sec_title) && !empty($sec_content)) {
            if (!isset($about_data['extra_sections']) || !is_array($about_data['extra_sections'])) {
                $about_data['extra_sections'] = [];
            }
            $about_data['extra_sections'][] = [
                'id'      => 'sec_' . time() . '_' . mt_rand(100, 999),
                'title'   => $sec_title,
                'badge'   => $sec_badge,
                'icon'    => !empty($sec_icon) ? $sec_icon : 'info',
                'content' => $sec_content,
                'image'   => $sec_image,
                'style'   => in_array($sec_style, ['two_column', 'card_grid', 'full_banner']) ? $sec_style : 'two_column'
            ];
            save_about_content($conn, $about_file, $about_data);
            $msg = "Bagian baru '" . htmlspecialchars($sec_title, ENT_QUOTES, 'UTF-8') . "' berhasil ditambahkan ke halaman Tentang!";
            $msg_type = "success";
        } else {
            $msg = "Judul dan Konten bagian tidak boleh kosong!";
            $msg_type = "danger";
        }
    } elseif ($action === 'edit_about_section') {
        $about_data = get_about_content($conn, $about_file);
        $idx         = isset($_POST['section_index']) ? (int)$_POST['section_index'] : -1;
        $sec_title   = trim($_POST['section_title'] ?? '');
        $sec_badge   = trim($_POST['section_badge'] ?? 'Informasi');
        $sec_icon    = trim($_POST['section_icon'] ?? 'info');
        $sec_content = trim($_POST['section_content'] ?? '');
        $sec_style   = trim($_POST['section_style'] ?? 'two_column');
        $sec_image   = trim($_POST['section_image'] ?? '');
        $remove_img  = !empty($_POST['remove_section_image']);

        if ($idx >= 0 && isset($about_data['extra_sections'][$idx])) {
            $current_img = $about_data['extra_sections'][$idx]['image'] ?? '';
            if ($remove_img) {
                $current_img = '';
            } elseif (isset($_FILES['section_image_file']) && $_FILES['section_image_file']['error'] === UPLOAD_ERR_OK) {
                $uploaded = handle_about_image_upload($_FILES['section_image_file'], 'section');
                if ($uploaded) {
                    $current_img = $uploaded;
                }
            } elseif (!empty($sec_image)) {
                $current_img = $sec_image;
            }

            $about_data['extra_sections'][$idx]['title']   = $sec_title;
            $about_data['extra_sections'][$idx]['badge']   = $sec_badge;
            $about_data['extra_sections'][$idx]['icon']    = !empty($sec_icon) ? $sec_icon : 'info';
            $about_data['extra_sections'][$idx]['content'] = $sec_content;
            $about_data['extra_sections'][$idx]['style']   = in_array($sec_style, ['two_column', 'card_grid', 'full_banner']) ? $sec_style : 'two_column';
            $about_data['extra_sections'][$idx]['image']   = $current_img;

            save_about_content($conn, $about_file, $about_data);
            $msg = "Bagian '" . htmlspecialchars($sec_title, ENT_QUOTES, 'UTF-8') . "' berhasil diperbarui!";
            $msg_type = "success";
        } else {
            $msg = "Gagal memperbarui: bagian tidak ditemukan.";
            $msg_type = "danger";
        }
    } elseif ($action === 'delete_about_section') {
        $about_data = get_about_content($conn, $about_file);
        $idx = isset($_POST['section_index']) ? (int)$_POST['section_index'] : -1;
        if ($idx >= 0 && isset($about_data['extra_sections'][$idx])) {
            $deleted_title = $about_data['extra_sections'][$idx]['title'] ?? 'Bagian';
            array_splice($about_data['extra_sections'], $idx, 1);
            save_about_content($conn, $about_file, $about_data);
            $msg = "Bagian '" . htmlspecialchars($deleted_title, ENT_QUOTES, 'UTF-8') . "' berhasil dikurangi / dihapus dari halaman Tentang!";
            $msg_type = "success";
        } else {
            $msg = "Gagal menghapus: bagian tidak ditemukan.";
            $msg_type = "danger";
        }
    } elseif ($action === 'move_about_section') {
        $about_data = get_about_content($conn, $about_file);
        $idx = (int)($_POST['section_index'] ?? -1);
        $dir = (int)($_POST['direction'] ?? 0);
        $target = $idx + $dir;
        if (isset($about_data['extra_sections'][$idx]) && isset($about_data['extra_sections'][$target])) {
            $temp = $about_data['extra_sections'][$idx];
            $about_data['extra_sections'][$idx] = $about_data['extra_sections'][$target];
            $about_data['extra_sections'][$target] = $temp;
            save_about_content($conn, $about_file, $about_data);
            $msg = "Urutan bagian berhasil diubah!";
            $msg_type = "success";
        }
    } elseif ($action === 'reset_about_data') {
        $about_data = reset_about_to_default($conn, $about_file);
        $msg = "Bagian dan foto halaman Tentang berhasil dikembalikan ke standar awal!";
        $msg_type = "success";

        } elseif ($action === 'save_legal_header') {
        $doc_type = trim($_POST['doc_type'] ?? 'privacy');
        $title    = trim($_POST['title'] ?? '');
        $badge    = trim($_POST['badge'] ?? '');
        $intro    = trim($_POST['intro'] ?? '');
        if (!empty($title)) {
            update_legal_header($doc_type, $title, $badge, $intro);
            $doc_name = ($doc_type === 'terms') ? 'Syarat & Ketentuan' : 'Kebijakan Privasi';
            $msg = "Header $doc_name berhasil diperbarui!";
            $msg_type = "success";
        }
    } elseif ($action === 'add_legal_section') {
        $doc_type = trim($_POST['doc_type'] ?? 'privacy');
        $title    = trim($_POST['title'] ?? '');
        $icon     = trim($_POST['icon'] ?? '');
        $content_text = trim($_POST['content'] ?? '');
        if (!empty($title) && !empty($content_text)) {
            add_legal_section($doc_type, $title, $icon, $content_text);
            $doc_name = ($doc_type === 'terms') ? 'Syarat & Ketentuan' : 'Kebijakan Privasi';
            $msg = "Poin baru '" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "' berhasil ditambahkan ke $doc_name!";
            $msg_type = "success";
        } else {
            $msg = "Judul dan Isi poin tidak boleh kosong!";
            $msg_type = "danger";
        }
    } elseif ($action === 'edit_legal_section') {
        $doc_type = trim($_POST['doc_type'] ?? 'privacy');
        $idx      = (int)($_POST['section_index'] ?? -1);
        $title    = trim($_POST['title'] ?? '');
        $icon     = trim($_POST['icon'] ?? '');
        $content_text = trim($_POST['content'] ?? '');
        if ($idx >= 0 && !empty($title) && !empty($content_text)) {
            edit_legal_section($doc_type, $idx, $title, $icon, $content_text);
            $doc_name = ($doc_type === 'terms') ? 'Syarat & Ketentuan' : 'Kebijakan Privasi';
            $msg = "Poin '" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "' pada $doc_name berhasil diperbarui!";
            $msg_type = "success";
        } else {
            $msg = "Gagal memperbarui poin: data tidak lengkap.";
            $msg_type = "danger";
        }
    } elseif ($action === 'delete_legal_section') {
        $doc_type = trim($_POST['doc_type'] ?? 'privacy');
        $idx      = (int)($_POST['section_index'] ?? -1);
        $curr_data = get_legal_data($doc_type);
        $del_title = $curr_data['sections'][$idx]['title'] ?? 'Poin';
        if (delete_legal_section($doc_type, $idx)) {
            $doc_name = ($doc_type === 'terms') ? 'Syarat & Ketentuan' : 'Kebijakan Privasi';
            $msg = "Poin '" . htmlspecialchars($del_title, ENT_QUOTES, 'UTF-8') . "' berhasil dihapus dari $doc_name!";
            $msg_type = "success";
        } else {
            $msg = "Gagal menghapus poin.";
            $msg_type = "danger";
        }
    } elseif ($action === 'move_legal_section') {
        $doc_type = trim($_POST['doc_type'] ?? 'privacy');
        $idx      = (int)($_POST['section_index'] ?? -1);
        $dir      = (int)($_POST['direction'] ?? 0);
        if (move_legal_section($doc_type, $idx, $dir)) {
            $doc_name = ($doc_type === 'terms') ? 'Syarat & Ketentuan' : 'Kebijakan Privasi';
            $msg = "Urutan poin pada $doc_name berhasil diubah!";
            $msg_type = "success";
        }
    } elseif ($action === 'reset_legal_data') {
        $doc_type = trim($_POST['doc_type'] ?? 'privacy');
        reset_legal_data($doc_type);
        $doc_name = ($doc_type === 'terms') ? 'Syarat & Ketentuan' : 'Kebijakan Privasi';
        $msg = "Seluruh isi $doc_name berhasil dikembalikan ke standar awal!";
        $msg_type = "success";

    } elseif ($action === 'add_user') {
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
        $office_name      = trim($_POST['office_name'] ?? 'Kantor Kedayweb');
        $address          = trim($_POST['address'] ?? '');
        $latitude         = (float) ($_POST['latitude'] ?? -8.219321);
        $longitude        = (float) ($_POST['longitude'] ?? 114.369458);
        $radius_meters    = max(10, intval($_POST['radius_meters'] ?? 100));
        $is_strict        = isset($_POST['is_strict']) && $_POST['is_strict'] == '1' ? 1 : 0;
        $check_in_time          = trim($_POST['check_in_time']       ?? '08:00');
        $late_threshold_time    = trim($_POST['late_threshold_time'] ?? '09:00');
        $cutoff_time            = trim($_POST['cutoff_time']         ?? '14:00');
        $check_out_time         = trim($_POST['check_out_time']      ?? '16:00');
        $attendance_days_arr    = $_POST['attendance_days'] ?? [];
        $attendance_days        = implode(',', array_map('trim', (array)$attendance_days_arr));

        if ($conn) {
            // Auto-migrate kolom baru jika belum ada
            $att_cols = [
                'check_in_time'       => "VARCHAR(10) NOT NULL DEFAULT '08:00'",
                'late_threshold_time' => "VARCHAR(10) NOT NULL DEFAULT '09:00'",
                'cutoff_time'         => "VARCHAR(10) NOT NULL DEFAULT '14:00'",
                'check_out_time'      => "VARCHAR(10) NOT NULL DEFAULT '16:00'",
                'attendance_days'     => "VARCHAR(100) NOT NULL DEFAULT 'Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'"
            ];
            foreach ($att_cols as $col_name => $col_def) {
                $chk_col = @mysqli_query($conn, "SHOW COLUMNS FROM attendance_settings LIKE '$col_name'");
                if ($chk_col && mysqli_num_rows($chk_col) == 0) {
                    @mysqli_query($conn, "ALTER TABLE attendance_settings ADD COLUMN $col_name $col_def");
                }
            }

            $chk = mysqli_query($conn, "SELECT id FROM attendance_settings WHERE id = 1 LIMIT 1");
            if ($chk && mysqli_num_rows($chk) > 0) {
                $stmt = mysqli_prepare($conn, "UPDATE attendance_settings SET office_name=?, address=?, latitude=?, longitude=?, radius_meters=?, is_strict=?, check_in_time=?, late_threshold_time=?, cutoff_time=?, check_out_time=?, attendance_days=? WHERE id = 1");
                mysqli_stmt_bind_param($stmt, "ssddiisssss", $office_name, $address, $latitude, $longitude, $radius_meters, $is_strict, $check_in_time, $late_threshold_time, $cutoff_time, $check_out_time, $attendance_days);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } else {
                $stmt = mysqli_prepare($conn, "INSERT INTO attendance_settings (id, office_name, address, latitude, longitude, radius_meters, is_strict, check_in_time, late_threshold_time, cutoff_time, check_out_time, attendance_days) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "ssddiisssss", $office_name, $address, $latitude, $longitude, $radius_meters, $is_strict, $check_in_time, $late_threshold_time, $cutoff_time, $check_out_time, $attendance_days);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            // Sync work_hours ke footer_settings.json secara otomatis
            $days_label = !empty($attendance_days) ? $attendance_days : 'Senin - Sabtu';
            $time_label = date('H:i', strtotime($check_in_time));
            $out_label  = !empty($check_out_time) ? date('H:i', strtotime($check_out_time)) : null;
            $hours_text = $out_label ? "$time_label - $out_label WIB" : "Masuk: $time_label WIB";
            $new_work_hours = $days_label . ' (' . $hours_text . ')';
            $curr_footer = get_superadmin_footer_settings($footer_settings_file);
            $curr_footer['work_hours'] = $new_work_hours;
            file_put_contents($footer_settings_file, json_encode($curr_footer, JSON_PRETTY_PRINT));

            $msg = "Pengaturan absensi berhasil disimpan dan footer otomatis diperbarui!";
            $msg_type = "success";
        }
    }
}

// Fetch Database Users & Cohort Years
$users = [];
$cohort_years = [];
if ($conn) {
    // Auto migration: Ensure intern_position, university, major, intern_start_date columns exist in users table
    $cols_to_check = [
        'intern_position'  => 'VARCHAR(150) DEFAULT NULL',
        'university'       => 'VARCHAR(150) DEFAULT NULL',
        'major'            => 'VARCHAR(150) DEFAULT NULL',
        'intern_start_date'=> 'DATE DEFAULT NULL COMMENT \'Tanggal mulai wajib absen intern\''
    ];
    foreach ($cols_to_check as $col_name => $col_def) {
        $chk_col = @mysqli_query($conn, "SHOW COLUMNS FROM users LIKE '$col_name'");
        if ($chk_col && mysqli_num_rows($chk_col) == 0) {
            @mysqli_query($conn, "ALTER TABLE users ADD COLUMN $col_name $col_def");
        }
    }

    // Auto sync certificates for any intern users
    sync_all_intern_certificates($conn);

    // Ambil daftar tahun angkatan yang ada di certificates
    $qy = mysqli_query($conn, "SELECT DISTINCT CAST(COALESCE(NULLIF(REGEXP_SUBSTR(certificate_id, '[0-9]{4}'), ''), YEAR(start_date), YEAR(issue_date), YEAR(created_at)) AS UNSIGNED) as yr FROM certificates ORDER BY yr DESC");
    if ($qy) {
        while ($ry = mysqli_fetch_assoc($qy)) {
            if (!empty($ry['yr'])) $cohort_years[] = (int)$ry['yr'];
        }
    }

    $sql_users = "SELECT u.id, u.username, u.password, u.role, u.intern_position, u.university, u.major,
                         u.intern_start_date,
                         COALESCE(
                             (SELECT CAST(COALESCE(
                                 NULLIF(REGEXP_SUBSTR(c.certificate_id, '[0-9]{4}'), ''),
                                 YEAR(c.start_date),
                                 YEAR(c.issue_date),
                                 YEAR(c.created_at)
                             ) AS UNSIGNED) FROM certificates c WHERE c.user_id = u.id LIMIT 1),
                             CAST(YEAR(NOW()) AS UNSIGNED)
                         ) AS entry_year
                  FROM users u 
                  ORDER BY u.id ASC";
    $res = @mysqli_query($conn, $sql_users);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $users[] = $row;
        }
    }
}
$current_yr = (int)date('Y');
if (!in_array($current_yr, $cohort_years)) {
    array_unshift($cohort_years, $current_yr);
}
rsort($cohort_years);

// Fetch Attendance Zone Settings
$att_settings = [
    'office_name'           => 'Kantor Kedayweb Banyuwangi',
    'address'               => 'Jl. Tamansari, Tukangkayu, Banyuwangi, Jawa Timur',
    'latitude'              => -8.21932100,
    'longitude'             => 114.36945800,
    'radius_meters'         => 100,
    'is_strict'             => 1,
    'check_in_time'         => '08:00',
    'late_threshold_time'   => '09:00',
    'cutoff_time'           => '14:00',
    'check_out_time'        => '16:00',
    'attendance_days'       => 'Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'
];
if ($conn) {
    // Auto-migrate kolom baru jika belum ada (saat pertama load halaman)
    $att_cols_migrate = [
        'check_in_time'       => "VARCHAR(10) NOT NULL DEFAULT '08:00'",
        'late_threshold_time' => "VARCHAR(10) NOT NULL DEFAULT '09:00'",
        'cutoff_time'         => "VARCHAR(10) NOT NULL DEFAULT '14:00'",
        'check_out_time'      => "VARCHAR(10) NOT NULL DEFAULT '16:00'",
        'attendance_days'     => "VARCHAR(100) NOT NULL DEFAULT 'Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'"
    ];
    foreach ($att_cols_migrate as $col_name => $col_def) {
        $chk_col = @mysqli_query($conn, "SHOW COLUMNS FROM attendance_settings LIKE '$col_name'");
        if ($chk_col && mysqli_num_rows($chk_col) == 0) {
            @mysqli_query($conn, "ALTER TABLE attendance_settings ADD COLUMN $col_name $col_def");
        }
    }
    $resAtt = @mysqli_query($conn, "SELECT * FROM attendance_settings WHERE id = 1 LIMIT 1");
    if ($resAtt && $rAtt = mysqli_fetch_assoc($resAtt)) {
        $att_settings['office_name']           = $rAtt['office_name']           ?? $att_settings['office_name'];
        $att_settings['address']               = $rAtt['address']               ?? $att_settings['address'];
        $att_settings['latitude']              = (float) ($rAtt['latitude']        ?? $att_settings['latitude']);
        $att_settings['longitude']             = (float) ($rAtt['longitude']       ?? $att_settings['longitude']);
        $att_settings['radius_meters']         = (int)   ($rAtt['radius_meters']   ?? $att_settings['radius_meters']);
        $att_settings['is_strict']             = (int)   ($rAtt['is_strict']       ?? $att_settings['is_strict']);
        $att_settings['check_in_time']         = $rAtt['check_in_time']         ?? $att_settings['check_in_time'];
        $att_settings['late_threshold_time']   = $rAtt['late_threshold_time']   ?? $att_settings['late_threshold_time'];
        $att_settings['cutoff_time']           = $rAtt['cutoff_time']           ?? $att_settings['cutoff_time'];
        $att_settings['check_out_time']        = $rAtt['check_out_time']        ?? $att_settings['check_out_time'];
        $att_settings['attendance_days']       = $rAtt['attendance_days']       ?? $att_settings['attendance_days'];
    }
}
// Parse attendance_days ke array untuk tampilan checkbox
$att_days_arr = array_filter(array_map('trim', explode(',', $att_settings['attendance_days'])));

$positions = get_index_positions($positions_file);
$footer_settings = get_superadmin_footer_settings($footer_settings_file);
$current_active_role = current_user_role();

// Fetch certificates list for superadmin approval section
$certificates_list = [];
if ($conn) {
    // Auto-migrate is_approved and approved_at columns
    $cert_cols = [
        'is_approved' => "TINYINT(1) NOT NULL DEFAULT 0",
        'approved_at' => "DATETIME DEFAULT NULL"
    ];
    foreach ($cert_cols as $col_name => $col_def) {
        $chk_col = @mysqli_query($conn, "SHOW COLUMNS FROM certificates LIKE '$col_name'");
        if ($chk_col && mysqli_num_rows($chk_col) == 0) {
            @mysqli_query($conn, "ALTER TABLE certificates ADD COLUMN $col_name $col_def");
        }
    }
    $res_certs = @mysqli_query($conn, "SELECT c.id, c.certificate_id, c.intern_name, c.intern_position, c.university, c.status, c.is_approved, c.approved_at, DATE_FORMAT(c.issue_date, '%d %b %Y') AS issue_fmt, u.username FROM certificates c LEFT JOIN users u ON u.id = c.user_id ORDER BY c.is_approved ASC, c.id DESC");
    if ($res_certs) {
        while ($row_c = mysqli_fetch_assoc($res_certs)) {
            $certificates_list[] = $row_c;
        }
    }
}

$sop_data = get_sop_data();
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
    <!-- Quill.js WYSIWYG Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet"/>
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <style>
        .glass-card { background: rgba(255,255,255,0.85); backdrop-filter: blur(12px); }
        #geofence-map { height: 380px; width: 100%; border-radius: 1rem; z-index: 1; }
        .leaflet-pane { z-index: 10 !important; }
        .leaflet-top, .leaflet-bottom { z-index: 11 !important; }
        /* Quill theme refinement */
        .ql-toolbar.ql-snow {
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            border-color: #cbd5e1;
            background: #f8fafc;
            padding: 8px 12px;
        }
        .ql-container.ql-snow {
            border-bottom-left-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
            border-color: #cbd5e1;
            font-family: inherit;
            font-size: 0.875rem;
            background: #ffffff;
        }
        .ql-editor {
            min-height: 180px;
            line-height: 1.6;
            color: #1e293b;
        }
        .ql-editor.ql-blank::before {
            color: #94a3b8;
            font-style: normal;
        }
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

            <!-- Navigation Bar Menu: Set Account vs Set Location vs Set Footer -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-2.5 shadow-sm flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                    <button type="button" id="nav-btn-account" onclick="switchSuperadminTab('account')" class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2 cursor-pointer shadow-xs bg-primary text-on-primary">
                        <span class="material-symbols-outlined text-lg">manage_accounts</span>
                        <span>Set Account</span>
                    </button>
                    <button type="button" id="nav-btn-location" onclick="switchSuperadminTab('location')" class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2 cursor-pointer text-on-surface-variant hover:bg-surface-container-high">
                        <span class="material-symbols-outlined text-lg">event_available</span>
                        <span>Set Absen</span>
                    </button>
                    <button type="button" id="nav-btn-footer" onclick="switchSuperadminTab('footer')" class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2 cursor-pointer text-on-surface-variant hover:bg-surface-container-high">
                        <span class="material-symbols-outlined text-lg">table_rows</span>
                        <span>Set Footer</span>
                    </button>
                    <button type="button" id="nav-btn-sop" onclick="switchSuperadminTab('sop')" class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2 cursor-pointer text-on-surface-variant hover:bg-surface-container-high">
                        <span class="material-symbols-outlined text-lg">gavel</span>
                        <span>Set SOP</span>
                    </button>
                    <button type="button" id="nav-btn-about" onclick="switchSuperadminTab('about')" class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2 cursor-pointer text-on-surface-variant hover:bg-surface-container-high">
                        <span class="material-symbols-outlined text-lg">info</span>
                        <span>Set Tentang</span>
                    </button>
                    <button type="button" id="nav-btn-legal" onclick="switchSuperadminTab('legal')" class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2 cursor-pointer text-on-surface-variant hover:bg-surface-container-high">
                        <span class="material-symbols-outlined text-lg">policy</span>
                        <span>Set Kebijakan & Syarat</span>
                    </button>
                    <button type="button" id="nav-btn-approval" onclick="switchSuperadminTab('approval')" class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2 cursor-pointer text-on-surface-variant hover:bg-surface-container-high relative">
                        <span class="material-symbols-outlined text-lg">workspace_premium</span>
                        <span>Approval Sertifikat</span>
                        <?php $pending_count = count(array_filter($certificates_list, fn($c) => !$c['is_approved'] && $c['status'] === 'active')); if ($pending_count > 0): ?>
                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-amber-500 text-white text-[10px] font-black rounded-full flex items-center justify-center"><?php echo $pending_count; ?></span>
                        <?php endif; ?>
                    </button>
                    <button type="button" id="nav-btn-all" onclick="switchSuperadminTab('all')" class="hidden sm:flex px-4 py-2.5 rounded-xl font-bold text-sm transition-all items-center justify-center gap-2 cursor-pointer text-on-surface-variant hover:bg-surface-container-high">
                        <span class="material-symbols-outlined text-lg">grid_view</span>
                        <span>Tampilkan Semua</span>
                    </button>
                </div>
                <div class="hidden md:flex items-center gap-2 text-xs font-semibold text-on-surface-variant px-3 py-1.5 bg-surface-container-low rounded-xl border border-outline-variant">
                    <span class="material-symbols-outlined text-sm text-primary">tune</span>
                    <span id="tab-active-label">Mode: Set Account</span>
                </div>
            </div>

            <!-- Section 1: Pengaturan Absensi (Geofencing + Jam Masuk + Hari Hadir) -->
            <div class="glass-card rounded-2xl border border-outline-variant p-6 shadow-sm hidden" id="section-location">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-outline-variant mb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">event_available</span>
                            <h3 class="font-headline-md font-bold text-on-surface">Pengaturan Absensi Magang</h3>
                        </div>
                        <p class="text-sm text-on-surface-variant mt-1">Atur titik koordinat kantor, radius geofence, jam masuk, dan hari wajib hadir. Footer akan otomatis diperbarui.</p>
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
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-emerald-600">schedule</span>
                            Masuk: <?php echo htmlspecialchars($att_settings['check_in_time'], ENT_QUOTES, 'UTF-8'); ?> WIB
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-amber-600">timer_off</span>
                            Telat: <?php echo htmlspecialchars($att_settings['late_threshold_time'], ENT_QUOTES, 'UTF-8'); ?> WIB
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-rose-600">do_not_disturb_on</span>
                            Batas Absen: <?php echo htmlspecialchars($att_settings['cutoff_time'], ENT_QUOTES, 'UTF-8'); ?> WIB
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-red-800 border border-red-200 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-red-600">logout</span>
                            Pulang: <?php echo htmlspecialchars($att_settings['check_out_time'], ENT_QUOTES, 'UTF-8'); ?> WIB
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

                        <!-- Jam Masuk, Batas Telat & Hari Hadir -->
                        <div class="p-3.5 bg-surface-container-low rounded-xl border border-outline-variant">
                            <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-3">Jam Kerja, Batas Telat &amp; Hari Wajib Hadir</label>

                            <!-- Grid 4 Kolom untuk Jam & Batas Absen -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-4">
                                <!-- 1. Jam Masuk (Hijau) -->
                                <div class="bg-surface-container-lowest p-2.5 rounded-xl border border-emerald-300">
                                    <label class="block text-xs font-semibold text-emerald-900 mb-1 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm text-emerald-600">schedule</span>
                                        Jam Masuk
                                    </label>
                                    <input type="time" name="check_in_time" id="att-check-in-time"
                                        value="<?php echo htmlspecialchars($att_settings['check_in_time'], ENT_QUOTES, 'UTF-8'); ?>"
                                        class="w-full px-2.5 py-1.5 bg-emerald-50/50 border border-emerald-300 rounded-lg text-sm font-bold text-emerald-950 focus:ring-2 focus:ring-emerald-400 focus:outline-none"/>
                                    <p class="text-[10px] text-emerald-700/80 mt-1">Mulai jam kerja normal.</p>
                                </div>

                                <!-- 2. Batas Jam Telat (Kuning) -->
                                <div class="bg-surface-container-lowest p-2.5 rounded-xl border border-amber-300">
                                    <label class="block text-xs font-semibold text-amber-900 mb-1 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm text-amber-500">timer_off</span>
                                        Batas Telat
                                    </label>
                                    <input type="time" name="late_threshold_time" id="att-late-threshold-time"
                                        value="<?php echo htmlspecialchars($att_settings['late_threshold_time'], ENT_QUOTES, 'UTF-8'); ?>"
                                        class="w-full px-2.5 py-1.5 bg-amber-50/60 border border-amber-300 rounded-lg text-sm font-bold text-amber-950 focus:ring-2 focus:ring-amber-400 focus:outline-none"/>
                                    <p class="text-[10px] text-amber-700 mt-1">Lewat jam ini = <strong>Terlambat</strong>.</p>
                                </div>

                                <!-- 3. Batas Akhir Absen (Merah ke Pink / Rose) -->
                                <div class="bg-surface-container-lowest p-2.5 rounded-xl border border-rose-300">
                                    <label class="block text-xs font-semibold text-rose-900 mb-1 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm text-rose-500">do_not_disturb_on</span>
                                        Batas Akhir Absen
                                    </label>
                                    <input type="time" name="cutoff_time" id="att-cutoff-time"
                                        value="<?php echo htmlspecialchars($att_settings['cutoff_time'], ENT_QUOTES, 'UTF-8'); ?>"
                                        class="w-full px-2.5 py-1.5 bg-rose-50/60 border border-rose-300 rounded-lg text-sm font-bold text-rose-950 focus:ring-2 focus:ring-rose-400 focus:outline-none"/>
                                    <p class="text-[10px] text-rose-700 mt-1">Lewat jam ini = <strong>Terkunci &amp; Tidak Masuk</strong>.</p>
                                </div>

                                <!-- 4. Jam Clock Out (Merah) -->
                                <div class="bg-surface-container-lowest p-2.5 rounded-xl border border-red-300">
                                    <label class="block text-xs font-semibold text-red-900 mb-1 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm text-red-600">logout</span>
                                        Jam Clock Out
                                    </label>
                                    <input type="time" name="check_out_time" id="att-check-out-time"
                                        value="<?php echo htmlspecialchars($att_settings['check_out_time'], ENT_QUOTES, 'UTF-8'); ?>"
                                        class="w-full px-2.5 py-1.5 bg-red-50/60 border border-red-300 rounded-lg text-sm font-bold text-red-950 focus:ring-2 focus:ring-red-400 focus:outline-none"/>
                                    <p class="text-[10px] text-red-700 mt-1">Target jam pulang intern.</p>
                                </div>
                            </div>

                            <!-- Hari Hadir -->
                            <div>
                                <label class="block text-xs font-semibold text-on-surface mb-2 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-sm text-primary">calendar_today</span>
                                    Hari Wajib Hadir
                                </label>
                                <div class="grid grid-cols-3 sm:grid-cols-4 gap-1.5">
                                    <?php
                                    $all_days = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
                                    foreach ($all_days as $day):
                                        $checked = in_array($day, $att_days_arr) ? 'checked' : '';
                                    ?>
                                    <label class="flex items-center gap-1.5 cursor-pointer px-2 py-1.5 rounded-lg border border-outline-variant hover:bg-surface-container-high text-xs font-medium select-none">
                                        <input type="checkbox" name="attendance_days[]" value="<?php echo $day; ?>" <?php echo $checked; ?>
                                            class="text-primary focus:ring-primary rounded"/>
                                        <?php echo $day; ?>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1.5">Hari yang dipilih akan muncul di footer (misal: Senin,Selasa,Rabu,Kamis,Jumat,Sabtu).</p>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full py-3 px-5 bg-primary text-on-primary rounded-xl font-bold hover:bg-primary-container transition-all flex items-center justify-center gap-2 shadow-md hover:shadow-lg cursor-pointer">
                                <span class="material-symbols-outlined">save</span>
                                <span>Simpan Pengaturan Absensi</span>
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

            <!-- Section Footer: Pengaturan Content Footer -->
            <div class="glass-card rounded-2xl border border-outline-variant p-6 shadow-sm hidden" id="section-footer">
                <div class="flex items-center gap-2 pb-4 border-b border-outline-variant mb-6">
                    <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">table_rows</span>
                    <div>
                        <h3 class="font-headline-md font-bold text-on-surface">Kelola Konten Footer Website</h3>
                        <p class="text-sm text-on-surface-variant mt-0.5">Atur informasi lokasi, jam operasional, nomor WhatsApp, media sosial, dan versi workspace yang tampil di footer.</p>
                    </div>
                </div>

                <form action="superadmin.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <input type="hidden" name="action" value="save_footer_settings"/>

                    <div class="md:col-span-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-primary mb-3 flex items-center gap-1.5 font-geist">
                            <span class="material-symbols-outlined text-base">business</span> Informasi Hub & Operasional
                        </h4>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Nama Lokasi / Kantor</label>
                        <input type="text" name="footer_location" required value="<?php echo htmlspecialchars($footer_settings['location'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:outline-none"/>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Jam Operasional / Kerja</label>
                        <input type="text" name="footer_work_hours" required value="<?php echo htmlspecialchars($footer_settings['work_hours'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:outline-none"/>
                    </div>

                    <div class="md:col-span-2 pt-3 border-t border-outline-variant mt-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-primary mb-3 flex items-center gap-1.5 font-geist">
                            <span class="material-symbols-outlined text-base">share</span> Link Media Sosial Resmi
                        </h4>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Link Instagram</label>
                        <input type="url" name="footer_instagram" value="<?php echo htmlspecialchars($footer_settings['instagram'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:outline-none"/>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Link Threads</label>
                        <input type="url" name="footer_threads" value="<?php echo htmlspecialchars($footer_settings['threads'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:outline-none"/>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Link TikTok</label>
                        <input type="url" name="footer_tiktok" value="<?php echo htmlspecialchars($footer_settings['tiktok'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:outline-none"/>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">
                            <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-emerald-600 fill-current inline" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg> Nomor WhatsApp</span> <span class="text-slate-400 font-normal normal-case">(angka saja, ex: 628123456789)</span>
                        </label>
                        <input type="text" name="footer_whatsapp" placeholder="Contoh: 628123456789" value="<?php echo htmlspecialchars($footer_settings['whatsapp'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:outline-none"/>
                    </div>


                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Versi Workspace / Footer Tagline</label>
                        <input type="text" name="footer_workspace_version" value="<?php echo htmlspecialchars($footer_settings['workspace_version'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:outline-none"/>
                    </div>

                    <div class="md:col-span-2 pt-4 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-primary text-on-primary font-bold rounded-xl text-sm hover:bg-primary-container transition-colors shadow-md flex items-center gap-2 cursor-pointer">
                            <span class="material-symbols-outlined text-lg">save</span>
                            <span>Simpan Pengaturan Footer</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Section SOP: Manage SOP Content & Rules -->
            <div class="glass-card rounded-2xl border border-outline-variant p-6 shadow-sm hidden" id="section-sop">
                <!-- Section Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-outline-variant mb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">gavel</span>
                            <h3 class="font-headline-md font-bold text-on-surface">Manajemen Standar Operasional Prosedur (SOP)</h3>
                        </div>
                        <p class="text-sm text-on-surface-variant mt-1">Atur judul, pasal-pasal, etika kerja, larangan, serta seluruh isi peraturan magang yang tampil pada halaman <a href="../sop.php" target="_blank" class="text-primary font-bold hover:underline inline-flex items-center gap-0.5">sop.php <span class="material-symbols-outlined text-[13px]">open_in_new</span></a>.</p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" onclick="openAddSopModal()" class="px-4 py-2 bg-primary text-on-primary font-bold text-xs rounded-xl shadow-xs hover:bg-primary-container transition-all flex items-center gap-1.5 cursor-pointer">
                            <span class="material-symbols-outlined text-[18px]">add_circle</span>
                            <span>Tambah Pasal Baru</span>
                        </button>
                        <a href="../sop.php" target="_blank" class="px-3.5 py-2 bg-surface-container-high hover:bg-white border border-outline-variant text-on-surface text-xs font-bold rounded-xl shadow-2xs transition-all flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                            <span>Lihat Halaman SOP</span>
                        </a>
                        <button type="button" onclick="confirmResetSop()" class="px-3.5 py-2 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer" title="Kembalikan semua pasal SOP ke standar awal">
                            <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                            <span>Reset Default</span>
                        </button>
                    </div>
                </div>

                <div class="space-y-6">
                    <!-- Form 1: Header Meta Settings -->
                    <form method="POST" action="superadmin.php" class="p-5 bg-surface-container-low rounded-2xl border border-outline-variant space-y-4">
                        <input type="hidden" name="action" value="save_sop_header">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-primary">
                                <span class="material-symbols-outlined text-base">page_header</span>
                                <span>Pengaturan Header Halaman SOP</span>
                            </div>
                            <button type="submit" class="px-4 py-1.5 bg-primary text-on-primary font-bold text-xs rounded-xl hover:bg-primary-container transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs">
                                <span class="material-symbols-outlined text-[15px]">save</span>
                                <span>Simpan Header</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-3">
                                <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Badge Header</label>
                                <input type="text" name="sop_badge" value="<?php echo htmlspecialchars($sop_data['badge'] ?? 'Standar Operasional Prosedur', ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:outline-none" placeholder="Standar Operasional Prosedur" required/>
                            </div>
                            <div class="md:col-span-4">
                                <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Judul Utama Halaman</label>
                                <input type="text" name="sop_title" value="<?php echo htmlspecialchars($sop_data['title'] ?? 'SOP Peserta Magang Kedayweb', ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-bold focus:ring-2 focus:ring-primary focus:outline-none" placeholder="SOP Peserta Magang Kedayweb" required/>
                            </div>
                            <div class="md:col-span-5">
                                <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Subjudul / Keterangan</label>
                                <input type="text" name="sop_subtitle" value="<?php echo htmlspecialchars($sop_data['subtitle'] ?? 'Kedayweb × Kulino House — wajib dibaca dan dipahami oleh seluruh peserta magang.', ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:outline-none" placeholder="Keterangan singkat SOP"/>
                            </div>
                        </div>
                    </form>

                    <!-- Section 2: Daftar Pasal & Aturan SOP (PHP Rendered Cards) -->
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-xl">format_list_bulleted</span>
                                <h4 class="font-bold text-on-surface text-base">Daftar Pasal & Aturan SOP</h4>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-primary/10 text-primary">
                                    <?php echo count($sop_data['sections'] ?? []); ?> Pasal
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="relative w-48 sm:w-64">
                                    <span class="material-symbols-outlined text-sm text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2">search</span>
                                    <input type="text" oninput="filterSopTable(this.value)" placeholder="Cari pasal / isi..." class="w-full pl-8 pr-3 py-1.5 text-xs bg-surface-container-lowest border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:outline-none">
                                </div>
                                <button type="button" onclick="openAddSopModal()" class="px-3.5 py-2 bg-primary text-on-primary font-bold text-xs rounded-xl shadow-xs hover:bg-primary-container transition-all flex items-center gap-1.5 cursor-pointer shrink-0">
                                    <span class="material-symbols-outlined text-sm">add</span>
                                    <span>Tambah Pasal</span>
                                </button>
                            </div>
                        </div>

                        <?php if (empty($sop_data['sections'])): ?>
                            <div class="p-8 text-center bg-surface-container-low rounded-2xl border border-dashed border-outline-variant">
                                <span class="material-symbols-outlined text-4xl text-slate-400 mb-2">gavel</span>
                                <p class="text-sm font-bold text-on-surface">Belum ada pasal SOP yang terdaftar</p>
                                <p class="text-xs text-on-surface-variant mt-1 mb-4">Tambahkan pasal baru atau klik "Reset Default" untuk memuat template SOP standar.</p>
                                <button type="button" onclick="openAddSopModal()" class="px-4 py-2 bg-primary text-on-primary font-bold text-xs rounded-xl hover:bg-primary-container transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                    <span class="material-symbols-outlined text-sm">add_circle</span>
                                    <span>Tambah Pasal Pertama</span>
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="space-y-3" id="sop-list-container">
                                <?php 
                                $total_sections = count($sop_data['sections']);
                                foreach ($sop_data['sections'] as $idx => $sec): 
                                    $style = $sec['style'] ?? 'default';
                                    $badgeStyle = match($style) {
                                        'warning'  => 'bg-amber-100 text-amber-800 border-amber-300',
                                        'primary'  => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                                        'centered' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        default    => 'bg-slate-100 text-slate-700 border-slate-300'
                                    };
                                    $styleLabel = match($style) {
                                        'warning'  => 'Peringatan / Larangan',
                                        'primary'  => 'Ketentuan Utama',
                                        'centered' => 'Tengah (Center)',
                                        default    => 'Standar'
                                    };
                                    $raw_snippet = strip_tags($sec['content'] ?? '');
                                    $snippet = mb_strlen($raw_snippet) > 160 ? mb_substr($raw_snippet, 0, 160) . '...' : $raw_snippet;
                                ?>
                                    <div class="sop-section-item p-4 rounded-2xl border border-outline-variant bg-surface-container-lowest hover:border-primary/50 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                                        <!-- Left: Order & Meta -->
                                        <div class="flex items-start gap-3 flex-1 min-w-0">
                                            <!-- Up/Down Reorder Form -->
                                            <div class="flex flex-col items-center gap-1 pt-0.5 shrink-0">
                                                <form method="POST" action="superadmin.php" class="inline">
                                                    <input type="hidden" name="action" value="move_sop_section">
                                                    <input type="hidden" name="section_index" value="<?php echo $idx; ?>">
                                                    <input type="hidden" name="direction" value="-1">
                                                    <button type="submit" <?php echo ($idx === 0) ? 'disabled' : ''; ?> class="p-1 rounded-lg text-slate-400 hover:text-primary hover:bg-surface-container disabled:opacity-20 disabled:pointer-events-none transition cursor-pointer" title="Pindah ke Atas">
                                                        <span class="material-symbols-outlined text-[18px]">keyboard_arrow_up</span>
                                                    </button>
                                                </form>
                                                <span class="text-[11px] font-bold text-slate-400 font-mono">#<?php echo $idx + 1; ?></span>
                                                <form method="POST" action="superadmin.php" class="inline">
                                                    <input type="hidden" name="action" value="move_sop_section">
                                                    <input type="hidden" name="section_index" value="<?php echo $idx; ?>">
                                                    <input type="hidden" name="direction" value="1">
                                                    <button type="submit" <?php echo ($idx === $total_sections - 1) ? 'disabled' : ''; ?> class="p-1 rounded-lg text-slate-400 hover:text-primary hover:bg-surface-container disabled:opacity-20 disabled:pointer-events-none transition cursor-pointer" title="Pindah ke Bawah">
                                                        <span class="material-symbols-outlined text-[18px]">keyboard_arrow_down</span>
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Content Detail -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold border <?php echo $badgeStyle; ?>">
                                                        <?php echo $styleLabel; ?>
                                                    </span>
                                                    <?php if (!empty($sec['nav_title'])): ?>
                                                        <span class="text-[11px] text-slate-500 font-medium bg-slate-100 px-2 py-0.5 rounded-md">
                                                            Nav: <?php echo htmlspecialchars($sec['nav_title'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <h5 class="font-bold text-on-surface text-sm sm:text-base mb-1 truncate">
                                                    <?php echo htmlspecialchars($sec['title'] ?? 'Pasal SOP', ENT_QUOTES, 'UTF-8'); ?>
                                                </h5>
                                                <p class="text-xs text-on-surface-variant line-clamp-2 leading-relaxed">
                                                    <?php echo htmlspecialchars($snippet ?: '(Belum ada konten)', ENT_QUOTES, 'UTF-8'); ?>
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Right: Action Buttons (EDIT & DELETE) -->
                                        <div class="flex items-center gap-2 shrink-0 self-end md:self-center pt-2 md:pt-0 border-t md:border-t-0 border-outline-variant w-full md:w-auto justify-end">
                                            <!-- Tombol Edit -->
                                            <button type="button" onclick="openEditSopModal(<?php echo $idx; ?>)" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-xl border border-indigo-200 transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs" title="Edit isi pasal ini">
                                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                                <span>Edit Pasal</span>
                                            </button>

                                            <!-- Tombol Hapus -->
                                            <form method="POST" action="superadmin.php" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pasal: <?php echo addslashes(htmlspecialchars($sec['title'] ?? '', ENT_QUOTES, 'UTF-8')); ?>?');" class="inline">
                                                <input type="hidden" name="action" value="delete_sop_section">
                                                <input type="hidden" name="section_index" value="<?php echo $idx; ?>">
                                                <button type="submit" class="px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 font-bold text-xs rounded-xl border border-red-200 transition-all flex items-center gap-1 cursor-pointer shadow-2xs" title="Hapus pasal ini dari SOP">
                                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Bottom Action Bar -->
                    <div class="pt-4 border-t border-outline-variant flex flex-col sm:flex-row items-center justify-between gap-3 bg-surface-container-low p-4 rounded-2xl border">
                        <div class="text-xs text-slate-500 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Setiap perubahan pada pasal langsung tersimpan dan aktif di halaman SOP.</span>
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                            <a href="../sop.php" target="_blank" class="px-4 py-2 bg-surface-container-high border border-outline-variant text-on-surface font-bold text-xs rounded-xl hover:bg-white transition-all flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-sm">open_in_new</span>
                                <span>Preview SOP</span>
                            </a>
                            <button type="button" onclick="openAddSopModal()" class="px-5 py-2 bg-primary text-on-primary font-bold text-xs rounded-xl hover:bg-primary-container shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <span class="material-symbols-outlined text-base">add_circle</span>
                                <span>Tambah Pasal Baru</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Hidden Reset Form -->
                <form id="reset-sop-form" method="POST" action="superadmin.php" class="hidden">
                    <input type="hidden" name="action" value="reset_sop_data">
                </form>
            </div>

            <!-- Section 2: Manage Positions on index.php -->
            <div class="glass-card rounded-2xl border border-outline-variant p-6 shadow-sm" id="section-positions">
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
            <div class="glass-card rounded-2xl border border-outline-variant p-6 shadow-sm" id="section-account">
                <!-- Header & Toolbar Controls -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-4 border-b border-outline-variant mb-4">
                    <div>
                        <h3 class="font-headline-md font-bold text-on-surface">Manajemen User & Role Database</h3>
                        <p class="text-sm text-on-surface-variant">Kelola dan tambah pengguna baru serta ubah role akun di database.</p>
                    </div>

                    <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                        <!-- Filter Angkatan Dropdown -->
                        <div class="flex items-center bg-surface-bright border border-outline-variant rounded-xl px-3 py-2 shadow-2xs">
                            <label for="user-year-select" class="text-xs font-semibold text-on-surface-variant whitespace-nowrap mr-1">Angkatan:</label>
                            <select id="user-year-select" onchange="filterUsersByCohort(this.value)"
                                    class="bg-transparent border-none text-xs font-bold text-primary focus:outline-none focus:ring-0 p-0 pr-5 cursor-pointer">
                                <option value="all">Semua Tahun</option>
                                <?php foreach ($cohort_years as $yr): ?>
                                    <option value="<?php echo $yr; ?>">Angkatan <?php echo $yr; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filter Role Dropdown -->
                        <div class="flex items-center bg-surface-bright border border-outline-variant rounded-xl px-3 py-2 shadow-2xs">
                            <label for="user-role-select" class="text-xs font-semibold text-on-surface-variant whitespace-nowrap mr-1">Role:</label>
                            <select id="user-role-select" onchange="filterUsersByRole(this.value)"
                                    class="bg-transparent border-none text-xs font-bold text-primary focus:outline-none focus:ring-0 p-0 pr-5 cursor-pointer">
                                <option value="all">Semua Role</option>
                                <option value="intern">Intern</option>
                                <option value="admin">Admin</option>
                                <option value="superadmin">Superadmin</option>
                            </select>
                        </div>

                        <!-- Button Tambah User Baru -->
                        <button onclick="document.getElementById('add-user-modal').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all text-xs cursor-pointer whitespace-nowrap shadow-xs">
                            Tambah User Baru
                        </button>
                    </div>
                </div>

                <!-- Cohort Pills Bar (Quick Navigation) -->
                <div id="superadmin-cohort-pills-bar" class="flex items-center gap-2 px-4 py-2 bg-surface-container-low/40 rounded-xl border border-outline-variant mb-4 overflow-x-auto text-xs">
                    <span class="text-on-surface-variant font-medium shrink-0 mr-1">Filter Cepat:</span>
                    <button type="button" onclick="setSuperadminCohortPill('all')" data-cohort="all" class="superadmin-cohort-pill px-3 py-1 rounded-full text-xs font-bold transition-all bg-primary text-on-primary shadow-xs cursor-pointer">
                        Semua Angkatan
                    </button>
                    <?php foreach ($cohort_years as $yr): ?>
                        <button type="button" onclick="setSuperadminCohortPill('<?php echo $yr; ?>')" data-cohort="<?php echo $yr; ?>" class="superadmin-cohort-pill px-3 py-1 rounded-full text-xs font-semibold text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high border border-outline-variant/60 transition-all cursor-pointer">
                            Angkatan <?php echo $yr; ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-outline-variant bg-surface-container-low text-on-surface-variant">
                                <th class="py-3 px-4 font-semibold">ID</th>
                                <th class="py-3 px-4 font-semibold">Username / Email</th>
                                <th class="py-3 px-4 font-semibold">Password</th>
                                <th class="py-3 px-4 font-semibold">Role</th>
                                <th class="py-3 px-4 font-semibold">Angkatan</th>
                                <th class="py-3 px-4 font-semibold">Tgl Mulai Absen</th>
                                <th class="py-3 px-4 font-semibold">Posisi Magang</th>
                                <th class="py-3 px-4 font-semibold">Instansi / Universitas</th>
                                <th class="py-3 px-4 font-semibold">Jurusan</th>
                                <th class="py-3 px-4 font-semibold text-right">Kelola Akun</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <?php if (empty($users)): ?>
                                <tr id="no-superadmin-users-msg">
                                    <td colspan="9" class="text-center py-6 text-on-surface-variant">Tidak ada data user di database.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                    <tr class="superadmin-user-row hover:bg-surface-container-low transition-colors"
                                        data-year="<?php echo (int)($u['entry_year'] ?? date('Y')); ?>"
                                        data-role="<?php echo htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-search="<?php echo htmlspecialchars(strtolower($u['username'] . ' ' . $u['role'] . ' ' . ($u['intern_position'] ?? '') . ' ' . ($u['university'] ?? '') . ' ' . ($u['major'] ?? '') . ' ' . ($u['entry_year'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>">
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
                                        <td class="py-3 px-4">
                                            <span class="px-2.5 py-1 text-xs rounded-full font-bold bg-amber-50 text-amber-900 border border-amber-200/80 flex items-center gap-1 w-fit shadow-2xs">
                                                <span class="material-symbols-outlined text-[14px] text-amber-600">calendar_today</span>
                                                <span>Angkatan <?php echo (int)($u['entry_year'] ?? date('Y')); ?></span>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <?php if ($u['role'] === 'intern'): ?>
                                                <div class="flex items-center gap-1.5">
                                                    <?php if (!empty($u['intern_start_date'])): ?>
                                                        <span class="px-2 py-0.5 text-xs rounded-full font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                                            <span class="material-symbols-outlined text-[12px] text-emerald-600">event_available</span>
                                                            <?php echo date('d M Y', strtotime($u['intern_start_date'])); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="px-2 py-0.5 text-xs rounded-full font-semibold bg-slate-100 text-slate-500 border border-slate-200 italic">Belum diset</span>
                                                    <?php endif; ?>
                                                    <button type="button"
                                                        onclick="openStartDateModal(<?php echo (int)$u['id']; ?>, '<?php echo htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($u['intern_start_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>')"
                                                        class="text-primary hover:text-primary/80 p-1 rounded-lg hover:bg-primary/10 transition-colors cursor-pointer"
                                                        title="Set Tanggal Mulai Absen">
                                                        <span class="material-symbols-outlined text-[16px]">edit_calendar</span>
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-slate-400 italic text-xs">N/A</span>
                                            <?php endif; ?>
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
                                <tr id="no-superadmin-users-msg" class="hidden">
                                    <td colspan="9" class="text-center py-6 text-on-surface-variant font-medium">Tidak ada data user yang sesuai dengan filter angkatan/search.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section 4: Approval Sertifikat (Superadmin only) -->
            <div class="glass-card rounded-2xl border border-outline-variant p-6 shadow-sm hidden" id="section-approval">
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-outline-variant mb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">workspace_premium</span>
                            <h3 class="font-headline-md font-bold text-on-surface">Approval Tanda Tangan Sertifikat</h3>
                        </div>
                        <p class="text-sm text-on-surface-variant mt-1">Sebagai Super Admin, Anda harus menyetujui sertifikat intern agar tanda tangan dan stempel resmi muncul di sertifikat mereka.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-amber-600">pending</span>
                            Menunggu: <?php echo count(array_filter($certificates_list, fn($c) => !$c['is_approved'])); ?>
                        </span>
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-emerald-600">verified</span>
                            Disetujui: <?php echo count(array_filter($certificates_list, fn($c) => $c['is_approved'])); ?>
                        </span>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="mb-5 p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-3">
                    <span class="material-symbols-outlined text-amber-600 text-xl shrink-0 mt-0.5">info</span>
                    <div class="text-sm text-amber-900">
                        <p class="font-bold mb-1">Cara Kerja Approval Sertifikat</p>
                        <p>Ketika Super Admin menekan tombol <strong class="text-emerald-700">Setujui Tanda Tangan</strong>, tanda tangan supervisor dan stempel Kedayweb akan otomatis muncul di halaman sertifikat dan verifikasi intern tersebut. Jika dinonaktifkan, sertifikat akan menampilkan watermark <em>"Menunggu Persetujuan Super Admin"</em>.</p>
                    </div>
                </div>

                <!-- Certificates Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-outline-variant bg-surface-container-low text-on-surface-variant">
                                <th class="py-3 px-4 font-semibold">ID Sertifikat</th>
                                <th class="py-3 px-4 font-semibold">Nama Intern</th>
                                <th class="py-3 px-4 font-semibold">Posisi</th>
                                <th class="py-3 px-4 font-semibold">Universitas</th>
                                <th class="py-3 px-4 font-semibold text-center">Status Cert</th>
                                <th class="py-3 px-4 font-semibold text-center">Approval TTD</th>
                                <th class="py-3 px-4 font-semibold text-center">Tanggal Disetujui</th>
                                <th class="py-3 px-4 font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant" id="approval-cert-tbody">
                            <?php if (empty($certificates_list)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-8 text-on-surface-variant">Belum ada sertifikat intern di database.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($certificates_list as $cert_row): ?>
                                    <tr class="hover:bg-surface-container-low transition-colors <?php echo !$cert_row['is_approved'] ? 'bg-amber-50/30' : ''; ?>" id="cert-row-<?php echo (int)$cert_row['id']; ?>">
                                        <td class="py-3 px-4 font-mono text-xs font-bold text-primary"><?php echo htmlspecialchars($cert_row['certificate_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="py-3 px-4 font-medium text-on-surface">
                                            <div class="flex items-center gap-2">
                                                <div class="w-7 h-7 rounded-full bg-primary-container text-primary flex items-center justify-center text-xs font-bold shrink-0"><?php echo strtoupper(substr($cert_row['intern_name'], 0, 1)); ?></div>
                                                <div>
                                                    <p class="font-semibold"><?php echo htmlspecialchars($cert_row['intern_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                    <?php if (!empty($cert_row['username'])): ?>
                                                    <p class="text-[11px] text-on-surface-variant">@<?php echo htmlspecialchars($cert_row['username'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-on-surface-variant text-xs"><?php echo htmlspecialchars($cert_row['intern_position'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="py-3 px-4 text-on-surface-variant text-xs"><?php echo !empty($cert_row['university']) ? htmlspecialchars($cert_row['university'], ENT_QUOTES, 'UTF-8') : '<span class="text-slate-400">-</span>'; ?></td>
                                        <td class="py-3 px-4 text-center">
                                            <?php if ($cert_row['status'] === 'active'): ?>
                                                <span class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full text-[11px] font-bold">Aktif</span>
                                            <?php else: ?>
                                                <span class="bg-red-100 text-red-800 px-2 py-0.5 rounded-full text-[11px] font-bold">Dicabut</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <?php if ($cert_row['is_approved']): ?>
                                                <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-800 px-2.5 py-1 rounded-full text-[11px] font-bold">
                                                    <span class="material-symbols-outlined text-[13px] text-emerald-600">verified</span>
                                                    Disetujui
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-800 px-2.5 py-1 rounded-full text-[11px] font-bold">
                                                    <span class="material-symbols-outlined text-[13px] text-amber-600">pending</span>
                                                    Menunggu
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4 text-center text-[11px] text-on-surface-variant" id="approved-at-<?php echo (int)$cert_row['id']; ?>">
                                            <?php echo !empty($cert_row['approved_at']) ? date('d M Y H:i', strtotime($cert_row['approved_at'])) : '<span class="text-slate-400 italic">Belum disetujui</span>'; ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button type="button"
                                                    onclick="toggleCertApproval(<?php echo (int)$cert_row['id']; ?>, <?php echo $cert_row['is_approved'] ? 0 : 1; ?>)"
                                                    id="btn-approve-<?php echo (int)$cert_row['id']; ?>"
                                                    class="px-3 py-1.5 text-xs font-bold rounded-xl transition-all flex items-center gap-1 cursor-pointer shadow-xs <?php echo $cert_row['is_approved'] ? 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-600 hover:text-white' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-600 hover:text-white'; ?>">
                                                    <span class="material-symbols-outlined text-[14px]"><?php echo $cert_row['is_approved'] ? 'cancel' : 'verified'; ?></span>
                                                    <span class="btn-approve-label"><?php echo $cert_row['is_approved'] ? 'Batalkan' : 'Setujui TTD'; ?></span>
                                                </button>
                                                <a href="../verification.php?id=<?php echo urlencode($cert_row['certificate_id']); ?>" target="_blank"
                                                   class="p-1.5 rounded-lg hover:bg-primary-fixed text-primary transition-colors" title="Lihat Sertifikat">
                                                    <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section 6: Kelola Halaman Tentang (Menambah & Mengurangi Isi dan Gambar) -->
            <div class="glass-card rounded-2xl border border-outline-variant p-6 shadow-sm hidden space-y-8" id="section-about">
                <!-- Header Card -->
                <div class="flex flex-col md:flex-row md:items-center justify-between pb-5 border-b border-outline-variant gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-primary bg-primary-container/30 px-3 py-1 rounded-full border border-primary/20 mb-2">
                            <span class="material-symbols-outlined text-sm">tune</span>
                            <span>Fitur Tambah & Kurangi Konten / Gambar Tentang</span>
                        </div>
                        <h3 class="font-headline-md font-bold text-on-surface">Kelola Isi & Gambar Halaman Tentang</h3>
                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                            Di sini Super Admin dapat <strong>menambah</strong> dan <strong>mengurangi (menghapus)</strong> bagian isi konten baru beserta gambarnya pada halaman Tentang.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="../about.php" target="_blank" class="px-4 py-2.5 bg-surface-container-high hover:bg-surface-container border border-outline-variant text-on-surface font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                            <span class="material-symbols-outlined text-base">open_in_new</span>
                            <span>Lihat Halaman Tentang</span>
                        </a>
                        <button type="button" onclick="openAddAboutSectionModal()" class="px-4 py-2.5 bg-primary hover:bg-primary-container text-on-primary font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                            <span class="material-symbols-outlined text-base">add</span>
                            <span>+ Tambah Bagian Baru</span>
                        </button>
                        <button type="button" onclick="confirmResetAboutSuperadmin()" class="px-3 py-2.5 border border-outline-variant text-on-surface-variant hover:text-red-600 hover:bg-red-50 font-bold text-xs rounded-xl transition-all flex items-center gap-1 cursor-pointer" title="Reset Standar Default">
                            <span class="material-symbols-outlined text-base">restart_alt</span>
                        </button>
                    </div>
                </div>

                <!-- Bagian 1: Daftar Seksi / Bagian Tambahan (Menambah & Mengurangi Isi Konten) -->
                <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-outline-variant gap-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">layers</span>
                            <div>
                                <h4 class="font-headline-sm font-bold text-on-surface">Bagian / Seksi Konten Halaman Tentang</h4>
                                <p class="text-xs text-on-surface-variant">Tambah seksi informasi baru (seperti Nilai Budaya, Fasilitas, FAQ) atau kurangi/hapus seksi yang tidak diperlukan.</p>
                            </div>
                        </div>
                        <button type="button" onclick="openAddAboutSectionModal()" class="px-4 py-2 bg-primary text-on-primary font-bold text-xs rounded-xl shadow-xs hover:bg-primary-container transition flex items-center gap-1 cursor-pointer self-start sm:self-auto">
                            <span class="material-symbols-outlined text-base">add</span>
                            <span>+ Tambah Bagian Baru</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-outline-variant bg-surface-container-low text-on-surface-variant uppercase font-bold text-[11px]">
                                    <th class="py-3 px-3 w-12 text-center">Urutan</th>
                                    <th class="py-3 px-3 w-36">Kategori & Icon</th>
                                    <th class="py-3 px-3">Judul & Cuplikan Isi Konten</th>
                                    <th class="py-3 px-3 w-28 text-center">Foto</th>
                                    <th class="py-3 px-3 w-40 text-center">Aksi (Kurangi / Edit)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/60">
                                <?php 
                                $extra_sec_list = $about_data['extra_sections'] ?? [];
                                if (!empty($extra_sec_list)):
                                    foreach ($extra_sec_list as $s_idx => $sec): 
                                        $has_img = !empty($sec['image']);
                                ?>
                                    <tr class="hover:bg-surface-container-high/40 transition">
                                        <td class="py-3 px-3 text-center">
                                            <div class="flex flex-col items-center gap-0.5">
                                                <?php if ($s_idx > 0): ?>
                                                <form method="POST" action="superadmin.php" class="inline">
                                                    <input type="hidden" name="action" value="move_about_section"/>
                                                    <input type="hidden" name="section_index" value="<?php echo $s_idx; ?>"/>
                                                    <input type="hidden" name="direction" value="-1"/>
                                                    <button type="submit" title="Pindah ke Atas" class="p-0.5 rounded text-on-surface-variant hover:text-primary transition cursor-pointer">
                                                        <span class="material-symbols-outlined text-[16px]">arrow_upward</span>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                                <span class="font-bold text-xs text-on-surface"><?php echo ($s_idx + 1); ?></span>
                                                <?php if ($s_idx < count($extra_sec_list) - 1): ?>
                                                <form method="POST" action="superadmin.php" class="inline">
                                                    <input type="hidden" name="action" value="move_about_section"/>
                                                    <input type="hidden" name="section_index" value="<?php echo $s_idx; ?>"/>
                                                    <input type="hidden" name="direction" value="1"/>
                                                    <button type="submit" title="Pindah ke Bawah" class="p-0.5 rounded text-on-surface-variant hover:text-primary transition cursor-pointer">
                                                        <span class="material-symbols-outlined text-[16px]">arrow_downward</span>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-high text-on-surface text-[10px] font-bold border border-outline-variant">
                                                <span class="material-symbols-outlined text-[12px]"><?php echo htmlspecialchars($sec['icon'] ?? 'info', ENT_QUOTES, 'UTF-8'); ?></span>
                                                <span><?php echo htmlspecialchars($sec['badge'] ?? 'Informasi', ENT_QUOTES, 'UTF-8'); ?></span>
                                            </span>
                                            <div class="text-[10px] text-on-surface-variant mt-1 capitalize">Layout: <?php echo str_replace('_', ' ', htmlspecialchars($sec['style'] ?? 'two_column', ENT_QUOTES, 'UTF-8')); ?></div>
                                        </td>
                                        <td class="py-3 px-3">
                                            <div class="font-bold text-on-surface text-xs"><?php echo htmlspecialchars($sec['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="text-on-surface-variant text-[11px] line-clamp-2 mt-0.5 leading-relaxed"><?php echo htmlspecialchars(strip_tags($sec['content'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <?php if ($has_img): ?>
                                                <img src="<?php echo htmlspecialchars(strpos($sec['image'], 'http') === 0 ? $sec['image'] : '../' . $sec['image'], ENT_QUOTES, 'UTF-8'); ?>" class="w-14 h-10 object-cover rounded-md border border-outline-variant mx-auto"/>
                                            <?php else: ?>
                                                <span class="text-[11px] text-on-surface-variant italic">Tanpa foto</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button type="button" onclick="openEditAboutSectionModal(<?php echo $s_idx; ?>)" class="px-2.5 py-1.5 rounded-lg border border-outline-variant hover:bg-surface-container text-on-surface font-bold text-[11px] flex items-center gap-1 cursor-pointer" title="Edit Bagian">
                                                    <span class="material-symbols-outlined text-[14px]">edit</span>
                                                    <span>Edit</span>
                                                </button>
                                                <button type="button" onclick="confirmDeleteAboutSection(<?php echo $s_idx; ?>, '<?php echo htmlspecialchars(addslashes($sec['title'] ?? 'Bagian'), ENT_QUOTES, 'UTF-8'); ?>')" class="px-2.5 py-1.5 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 font-bold text-[11px] flex items-center gap-1 cursor-pointer" title="Kurangi / Hapus Bagian">
                                                    <span class="material-symbols-outlined text-[14px]">delete</span>
                                                    <span>Hapus</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php 
                                    endforeach; 
                                else: 
                                ?>
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-on-surface-variant text-xs">
                                            Belum ada bagian / seksi tambahan. Klik tombol "+ Tambah Bagian Baru" di atas untuk menambahkan.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                </div>
        <div class="mt-auto shrink-0 w-full">   
            <!-- Section Legal: Pengaturan Kebijakan Privasi & Syarat Ketentuan -->
            <div class="glass-card rounded-2xl border border-outline-variant p-6 shadow-sm hidden space-y-6" id="section-legal">
                <!-- Header Card -->
                <div class="flex flex-col md:flex-row md:items-center justify-between pb-5 border-b border-outline-variant gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-primary bg-primary-container/30 px-3 py-1 rounded-full border border-primary/20 mb-2">
                            <span class="material-symbols-outlined text-sm">policy</span>
                            <span>Manajemen Dokumen Legal Platform</span>
                        </div>
                        <h3 class="font-headline-md font-bold text-on-surface">Kelola Kebijakan Privasi & Syarat Ketentuan</h3>
                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                            Di sini Super Admin dapat <strong>mengedit</strong>, <strong>menambah</strong>, dan <strong>mengurangi (menghapus)</strong> pasal/poin isi Kebijakan Privasi serta Syarat & Ketentuan.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" onclick="openAddLegalSectionModal(currentLegalTab)" class="px-4 py-2.5 bg-primary hover:bg-primary-container text-on-primary font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                            <span class="material-symbols-outlined text-base">add_circle</span>
                            <span>+ Tambah Poin Baru</span>
                        </button>
                        <button type="button" onclick="openEditLegalHeaderModal(currentLegalTab)" class="px-4 py-2.5 bg-surface-container-high hover:bg-surface-container border border-outline-variant text-on-surface font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                            <span class="material-symbols-outlined text-base">edit_note</span>
                            <span>Edit Header Dokumen</span>
                        </button>
                        <button type="button" onclick="confirmResetLegal(currentLegalTab)" class="px-3 py-2.5 border border-outline-variant text-on-surface-variant hover:text-red-600 hover:bg-red-50 font-bold text-xs rounded-xl transition-all flex items-center gap-1 cursor-pointer" title="Kembalikan Dokumen ke Standar Awal">
                            <span class="material-symbols-outlined text-base">restart_alt</span>
                        </button>
                    </div>
                </div>

                <!-- Sub-tab Navigasi (Pilih Kebijakan Privasi vs Syarat & Ketentuan) -->
                <div class="flex items-center gap-2 border-b border-outline-variant pb-2">
                    <button type="button" id="subtab-legal-privacy" onclick="switchLegalSubtab('privacy')" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-primary text-on-primary shadow-xs cursor-pointer">
                        <span class="material-symbols-outlined text-base">security</span>
                        <span>Kebijakan Privasi (<?php echo count($privacy_data['sections'] ?? []); ?> Poin)</span>
                    </button>
                    <button type="button" id="subtab-legal-terms" onclick="switchLegalSubtab('terms')" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-on-surface-variant hover:bg-surface-container-high cursor-pointer">
                        <span class="material-symbols-outlined text-base">gavel</span>
                        <span>Syarat & Ketentuan (<?php echo count($terms_data['sections'] ?? []); ?> Poin)</span>
                    </button>
                </div>

                <!-- Panel 1: Kebijakan Privasi -->
                <div id="panel-legal-privacy" class="space-y-4">
                    <!-- Info Header Box -->
                    <div class="bg-surface-container-low p-4 rounded-xl border border-outline-variant flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                        <div>
                            <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200"><?php echo htmlspecialchars($privacy_data['badge'] ?? 'Privasi'); ?></span>
                            <h4 class="font-bold text-sm text-on-surface mt-1"><?php echo htmlspecialchars($privacy_data['title'] ?? 'Kebijakan Privasi'); ?></h4>
                            <p class="text-xs text-on-surface-variant mt-0.5"><?php echo htmlspecialchars($privacy_data['intro'] ?? ''); ?></p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="../privacy-policy.php" target="_blank" class="px-3 py-1.5 bg-surface-container-lowest border border-outline-variant text-on-surface hover:text-primary font-bold text-xs rounded-lg transition flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">open_in_new</span>
                                <span>Preview Halaman</span>
                            </a>
                            <button type="button" onclick="openEditLegalHeaderModal('privacy')" class="px-3 py-1.5 bg-surface-container-lowest border border-outline-variant text-on-surface hover:text-amber-600 font-bold text-xs rounded-lg transition flex items-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">edit</span>
                                <span>Edit Teks Header</span>
                            </button>
                        </div>
                    </div>

                    <!-- List Poin Kebijakan Privasi -->
                    <div class="overflow-x-auto bg-surface-container-lowest rounded-xl border border-outline-variant">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-outline-variant bg-surface-container-low text-on-surface-variant uppercase font-bold text-[11px]">
                                    <th class="py-3 px-3 w-14 text-center">Urutan</th>
                                    <th class="py-3 px-3 w-52">Ikon & Judul Poin</th>
                                    <th class="py-3 px-3">Isi Ketentuan / Penjelasan</th>
                                    <th class="py-3 px-3 w-36 text-center">Aksi (Kurangi / Edit)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/60">
                                <?php 
                                $priv_list = $privacy_data['sections'] ?? [];
                                if (!empty($priv_list)):
                                    foreach ($priv_list as $p_idx => $item): 
                                ?>
                                    <tr class="hover:bg-surface-container-high/40 transition">
                                        <td class="py-3 px-3 text-center">
                                            <div class="flex flex-col items-center gap-0.5">
                                                <?php if ($p_idx > 0): ?>
                                                <form method="POST" action="superadmin.php" class="inline">
                                                    <input type="hidden" name="action" value="move_legal_section"/>
                                                    <input type="hidden" name="doc_type" value="privacy"/>
                                                    <input type="hidden" name="section_index" value="<?php echo $p_idx; ?>"/>
                                                    <input type="hidden" name="direction" value="-1"/>
                                                    <button type="submit" title="Pindah ke Atas" class="p-0.5 rounded text-on-surface-variant hover:text-primary transition cursor-pointer">
                                                        <span class="material-symbols-outlined text-[16px]">arrow_upward</span>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                                <span class="font-bold text-xs text-on-surface">#<?php echo ($p_idx + 1); ?></span>
                                                <?php if ($p_idx < count($priv_list) - 1): ?>
                                                <form method="POST" action="superadmin.php" class="inline">
                                                    <input type="hidden" name="action" value="move_legal_section"/>
                                                    <input type="hidden" name="doc_type" value="privacy"/>
                                                    <input type="hidden" name="section_index" value="<?php echo $p_idx; ?>"/>
                                                    <input type="hidden" name="direction" value="1"/>
                                                    <button type="submit" title="Pindah ke Bawah" class="p-0.5 rounded text-on-surface-variant hover:text-primary transition cursor-pointer">
                                                        <span class="material-symbols-outlined text-[16px]">arrow_downward</span>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="py-3 px-3">
                                            <div class="flex items-center gap-1.5 font-bold text-on-surface">
                                                <span class="material-symbols-outlined text-blue-600 text-base"><?php echo htmlspecialchars($item['icon'] ?? 'security'); ?></span>
                                                <span><?php echo htmlspecialchars($item['title'] ?? ''); ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-3">
                                            <div class="text-on-surface-variant leading-relaxed"><?php echo nl2br(htmlspecialchars($item['content'] ?? '')); ?></div>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button type="button" onclick="openEditLegalSectionModal('privacy', <?php echo $p_idx; ?>)" class="px-2.5 py-1.5 rounded-lg border border-outline-variant hover:bg-surface-container text-on-surface font-bold text-[11px] flex items-center gap-1 cursor-pointer">
                                                    <span class="material-symbols-outlined text-[14px]">edit</span>
                                                    <span>Edit</span>
                                                </button>
                                                <button type="button" onclick="confirmDeleteLegalSection('privacy', <?php echo $p_idx; ?>, '<?php echo htmlspecialchars(addslashes($item['title'] ?? 'Poin'), ENT_QUOTES, 'UTF-8'); ?>')" class="px-2.5 py-1.5 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 font-bold text-[11px] flex items-center gap-1 cursor-pointer">
                                                    <span class="material-symbols-outlined text-[14px]">delete</span>
                                                    <span>Hapus</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php 
                                    endforeach; 
                                else: 
                                ?>
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-on-surface-variant text-xs">
                                            Belum ada poin kebijakan privasi. Klik tombol "+ Tambah Poin Baru" di atas untuk menambahkan.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Panel 2: Syarat & Ketentuan -->
                <div id="panel-legal-terms" class="hidden space-y-4">
                    <!-- Info Header Box -->
                    <div class="bg-surface-container-low p-4 rounded-xl border border-outline-variant flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                        <div>
                            <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-200"><?php echo htmlspecialchars($terms_data['badge'] ?? 'Ketentuan'); ?></span>
                            <h4 class="font-bold text-sm text-on-surface mt-1"><?php echo htmlspecialchars($terms_data['title'] ?? 'Syarat & Ketentuan'); ?></h4>
                            <p class="text-xs text-on-surface-variant mt-0.5"><?php echo htmlspecialchars($terms_data['intro'] ?? ''); ?></p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="../terms-conditions.php" target="_blank" class="px-3 py-1.5 bg-surface-container-lowest border border-outline-variant text-on-surface hover:text-indigo-600 font-bold text-xs rounded-lg transition flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">open_in_new</span>
                                <span>Preview Halaman</span>
                            </a>
                            <button type="button" onclick="openEditLegalHeaderModal('terms')" class="px-3 py-1.5 bg-surface-container-lowest border border-outline-variant text-on-surface hover:text-amber-600 font-bold text-xs rounded-lg transition flex items-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">edit</span>
                                <span>Edit Teks Header</span>
                            </button>
                        </div>
                    </div>

                    <!-- List Poin Syarat & Ketentuan -->
                    <div class="overflow-x-auto bg-surface-container-lowest rounded-xl border border-outline-variant">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-outline-variant bg-surface-container-low text-on-surface-variant uppercase font-bold text-[11px]">
                                    <th class="py-3 px-3 w-14 text-center">Urutan</th>
                                    <th class="py-3 px-3 w-52">Ikon & Judul Poin</th>
                                    <th class="py-3 px-3">Isi Ketentuan / Penjelasan</th>
                                    <th class="py-3 px-3 w-36 text-center">Aksi (Kurangi / Edit)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/60">
                                <?php 
                                $term_list = $terms_data['sections'] ?? [];
                                if (!empty($term_list)):
                                    foreach ($term_list as $t_idx => $t_item): 
                                ?>
                                    <tr class="hover:bg-surface-container-high/40 transition">
                                        <td class="py-3 px-3 text-center">
                                            <div class="flex flex-col items-center gap-0.5">
                                                <?php if ($t_idx > 0): ?>
                                                <form method="POST" action="superadmin.php" class="inline">
                                                    <input type="hidden" name="action" value="move_legal_section"/>
                                                    <input type="hidden" name="doc_type" value="terms"/>
                                                    <input type="hidden" name="section_index" value="<?php echo $t_idx; ?>"/>
                                                    <input type="hidden" name="direction" value="-1"/>
                                                    <button type="submit" title="Pindah ke Atas" class="p-0.5 rounded text-on-surface-variant hover:text-primary transition cursor-pointer">
                                                        <span class="material-symbols-outlined text-[16px]">arrow_upward</span>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                                <span class="font-bold text-xs text-on-surface">#<?php echo ($t_idx + 1); ?></span>
                                                <?php if ($t_idx < count($term_list) - 1): ?>
                                                <form method="POST" action="superadmin.php" class="inline">
                                                    <input type="hidden" name="action" value="move_legal_section"/>
                                                    <input type="hidden" name="doc_type" value="terms"/>
                                                    <input type="hidden" name="section_index" value="<?php echo $t_idx; ?>"/>
                                                    <input type="hidden" name="direction" value="1"/>
                                                    <button type="submit" title="Pindah ke Bawah" class="p-0.5 rounded text-on-surface-variant hover:text-primary transition cursor-pointer">
                                                        <span class="material-symbols-outlined text-[16px]">arrow_downward</span>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="py-3 px-3">
                                            <div class="flex items-center gap-1.5 font-bold text-on-surface">
                                                <span class="material-symbols-outlined text-indigo-600 text-base"><?php echo htmlspecialchars($t_item['icon'] ?? 'gavel'); ?></span>
                                                <span><?php echo htmlspecialchars($t_item['title'] ?? ''); ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-3">
                                            <div class="text-on-surface-variant leading-relaxed"><?php echo nl2br(htmlspecialchars($t_item['content'] ?? '')); ?></div>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button type="button" onclick="openEditLegalSectionModal('terms', <?php echo $t_idx; ?>)" class="px-2.5 py-1.5 rounded-lg border border-outline-variant hover:bg-surface-container text-on-surface font-bold text-[11px] flex items-center gap-1 cursor-pointer">
                                                    <span class="material-symbols-outlined text-[14px]">edit</span>
                                                    <span>Edit</span>
                                                </button>
                                                <button type="button" onclick="confirmDeleteLegalSection('terms', <?php echo $t_idx; ?>, '<?php echo htmlspecialchars(addslashes($t_item['title'] ?? 'Poin'), ENT_QUOTES, 'UTF-8'); ?>')" class="px-2.5 py-1.5 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 font-bold text-[11px] flex items-center gap-1 cursor-pointer">
                                                    <span class="material-symbols-outlined text-[14px]">delete</span>
                                                    <span>Hapus</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php 
                                    endforeach; 
                                else: 
                                ?>
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-on-surface-variant text-xs">
                                            Belum ada poin syarat & ketentuan. Klik tombol "+ Tambah Poin Baru" di atas untuk menambahkan.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


    <!-- ── Modal Tambah Seksi Tentang ── -->
    <div id="modal-add-about-section" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
        <div class="bg-surface-container-lowest rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-outline-variant max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3 border-b border-outline-variant mb-4 sticky top-0 bg-surface-container-lowest z-10">
                <h3 class="font-headline-md font-bold text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-xl">post_add</span>
                    <span>Tambah Bagian / Seksi Baru</span>
                </h3>
                <button type="button" onclick="closeAddAboutSectionModal()" class="text-on-surface-variant hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="superadmin.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" value="add_about_section"/>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1">Judul Bagian <span class="text-red-500">*</span></label>
                    <input type="text" name="section_title" placeholder="Contoh: Budaya & Nilai Kerja" required class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none"/>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1">Badge Kategori</label>
                        <input type="text" name="section_badge" value="Informasi" class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1">Icon Material Symbol</label>
                        <input type="text" name="section_icon" value="stars" placeholder="stars, apartment, info, groups" class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none"/>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1">Gaya Tampilan (Layout)</label>
                    <select name="section_style" class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none">
                        <option value="two_column">Dua Kolom (Teks & Foto Berdampingan)</option>
                        <option value="card_grid">Kartu Fitur (Feature Card)</option>
                        <option value="full_banner">Banner Sorotan Lebar (Full-Width Highlight)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1">Isi Konten / Deskripsi <span class="text-red-500">*</span></label>
                    <textarea name="section_content" rows="4" placeholder="Tuliskan isi teks atau rincian poin-poin..." required class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none leading-relaxed"></textarea>
                </div>
                <div class="p-3 bg-surface-container-low rounded-xl border border-outline-variant space-y-2">
                    <label class="block text-xs font-bold text-on-surface">Foto / Gambar Pendukung (Opsional)</label>
                    <div>
                        <span class="text-[11px] text-on-surface-variant block mb-1">Upload File Foto:</span>
                        <input type="file" name="section_image_file" accept="image/*" class="w-full px-3 py-1.5 text-xs border border-outline-variant rounded-lg bg-surface-container-lowest mb-1"/>
                    </div>
                    <div>
                        <span class="text-[11px] text-on-surface-variant block mb-1">Atau Masukkan URL Gambar:</span>
                        <input type="text" name="section_image" placeholder="https://..." class="w-full px-3.5 py-1.5 text-xs border border-outline-variant rounded-lg bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none"/>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-outline-variant">
                    <button type="button" onclick="closeAddAboutSectionModal()" class="px-4 py-2 border border-outline-variant rounded-xl text-xs font-bold hover:bg-surface-container transition">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-primary text-on-primary rounded-xl text-xs font-bold hover:bg-primary-container transition shadow-md">Simpan Bagian Baru</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Modal Edit Seksi Tentang ── -->
    <div id="modal-edit-about-section" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
        <div class="bg-surface-container-lowest rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-outline-variant max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3 border-b border-outline-variant mb-4 sticky top-0 bg-surface-container-lowest z-10">
                <h3 class="font-headline-md font-bold text-amber-600 flex items-center gap-2">
                    <span class="material-symbols-outlined text-xl">edit</span>
                    <span>Edit Bagian Konten</span>
                </h3>
                <button type="button" onclick="closeEditAboutSectionModal()" class="text-on-surface-variant hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="superadmin.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" value="edit_about_section"/>
                <input type="hidden" id="edit-about-sec-idx" name="section_index" value=""/>

                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1">Judul Bagian <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-about-sec-title" name="section_title" required class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none"/>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1">Badge Kategori</label>
                        <input type="text" id="edit-about-sec-badge" name="section_badge" class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1">Icon Material Symbol</label>
                        <input type="text" id="edit-about-sec-icon" name="section_icon" class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none"/>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1">Gaya Tampilan (Layout)</label>
                    <select id="edit-about-sec-style" name="section_style" class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none">
                        <option value="two_column">Dua Kolom (Teks & Foto Berdampingan)</option>
                        <option value="card_grid">Kartu Fitur (Feature Card)</option>
                        <option value="full_banner">Banner Sorotan Lebar (Full-Width Highlight)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1">Isi Konten / Deskripsi <span class="text-red-500">*</span></label>
                    <textarea id="edit-about-sec-content" name="section_content" rows="4" required class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none leading-relaxed"></textarea>
                </div>
                <div class="p-3 bg-surface-container-low rounded-xl border border-outline-variant space-y-2">
                    <label class="block text-xs font-bold text-on-surface">Foto / Gambar Pendukung</label>
                    <div id="edit-about-sec-preview-box" class="flex items-center gap-3 p-2 bg-surface-container-lowest rounded-lg border border-outline-variant mb-2">
                        <img id="edit-about-sec-preview-img" src="" class="w-12 h-10 object-cover rounded border border-outline-variant"/>
                        <div class="text-[11px] text-on-surface-variant flex-1 truncate">
                            <span class="font-bold text-on-surface block">Foto Saat Ini:</span>
                            <span id="edit-about-sec-url-text" class="text-slate-500 truncate block"></span>
                        </div>
                        <label class="flex items-center gap-1 text-[11px] text-red-600 font-bold cursor-pointer">
                            <input type="checkbox" name="remove_section_image" value="1" class="rounded text-red-600"/>
                            <span>Hapus Foto</span>
                        </label>
                    </div>
                    <div>
                        <span class="text-[11px] text-on-surface-variant block mb-1">Ganti File Baru:</span>
                        <input type="file" name="section_image_file" accept="image/*" class="w-full px-3 py-1.5 text-xs border border-outline-variant rounded-lg bg-surface-container-lowest mb-1"/>
                    </div>
                    <div>
                        <span class="text-[11px] text-on-surface-variant block mb-1">Atau Ubah URL Gambar:</span>
                        <input type="text" id="edit-about-sec-image" name="section_image" placeholder="https://..." class="w-full px-3.5 py-1.5 text-xs border border-outline-variant rounded-lg bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none"/>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-outline-variant">
                    <button type="button" onclick="closeEditAboutSectionModal()" class="px-4 py-2 border border-outline-variant rounded-xl text-xs font-bold hover:bg-surface-container transition">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 text-white rounded-xl text-xs font-bold hover:bg-amber-700 transition shadow-md">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Modal Konfirmasi Hapus Universal Tentang ── -->
    <div id="modal-delete-about" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
        <div class="bg-surface-container-lowest rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-outline-variant text-center space-y-4">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                <span class="material-symbols-outlined text-2xl">delete_forever</span>
            </div>
            <div>
                <h3 class="font-headline-sm font-bold text-on-surface" id="delete-about-modal-title">Hapus Item?</h3>
                <p class="text-xs text-on-surface-variant mt-1" id="delete-about-modal-desc">Apakah Anda yakin ingin menghapus item ini?</p>
            </div>
            <form id="delete-about-form" action="superadmin.php" method="POST" class="flex justify-center gap-2 pt-2">
                <input type="hidden" id="delete-about-action" name="action" value=""/>
                <input type="hidden" id="delete-about-idx" name="" value=""/>
                <button type="button" onclick="closeDeleteAboutModal()" class="px-4 py-2 border border-outline-variant rounded-xl text-xs font-bold hover:bg-surface-container transition">Batal</button>
                <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs transition shadow-md">Ya, Hapus</button>
            </form>
        </div>
    </div>

    <!-- Form Tersembunyi Reset Standar Tentang -->
    <form id="reset-about-form" action="superadmin.php" method="POST" class="hidden">
        <input type="hidden" name="action" value="reset_about_data"/>
    </form>

    <!-- Modal Set Tanggal Mulai Absen Intern -->
    <div id="start-date-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
        <div class="bg-surface-container-lowest rounded-2xl max-w-md w-full p-6 shadow-2xl border border-outline-variant">
            <div class="flex justify-between items-center pb-3 border-b border-outline-variant mb-5">
                <h3 class="font-headline-md font-bold text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">edit_calendar</span>
                    <span>Set Tanggal Mulai Absen</span>
                </h3>
                <button onclick="closeStartDateModal()" class="text-on-surface-variant hover:text-on-surface p-1 rounded-lg hover:bg-surface-container-high transition-colors cursor-pointer">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="mb-5 p-3.5 bg-blue-50 border border-blue-200 rounded-xl flex items-start gap-2.5">
                <span class="material-symbols-outlined text-blue-600 text-lg shrink-0 mt-0.5">info</span>
                <div class="text-xs text-blue-900">
                    <p class="font-bold mb-0.5">Informasi</p>
                    <p>Tanggal mulai absen menentukan kapan sistem mulai menghitung kehadiran intern. Hari sebelum tanggal ini <strong>tidak</strong> akan dihitung sebagai "tidak masuk".</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Intern</p>
                    <p id="start-date-intern-name" class="font-bold text-on-surface text-base"></p>
                </div>

                <div>
                    <label for="start-date-input" class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">
                        Tanggal Mulai Wajib Absen <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="start-date-input"
                        class="w-full px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-bold focus:ring-2 focus:ring-primary focus:outline-none cursor-pointer"/>
                    <p class="text-[11px] text-slate-400 mt-1">Kosongkan untuk menghapus tanggal mulai (semua hari akan dihitung).</p>
                </div>

                <div id="start-date-result-msg" class="hidden text-xs font-semibold p-3 rounded-xl"></div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeStartDateModal()" class="flex-1 py-2.5 px-4 bg-surface-container-low hover:bg-surface-container-high text-on-surface border border-outline-variant rounded-xl font-bold text-sm transition-all cursor-pointer">
                        Batal
                    </button>
                    <button type="button" id="start-date-save-btn" onclick="saveStartDate()"
                        class="flex-1 py-2.5 px-4 bg-primary text-on-primary rounded-xl font-bold text-sm hover:bg-primary/90 transition-all flex items-center justify-center gap-2 cursor-pointer shadow-sm">
                        <span class="material-symbols-outlined text-lg">save</span>
                        <span>Simpan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

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

    <!-- Modal Tambah Pasal SOP Baru -->
    <div id="add-sop-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
        <div class="bg-surface-container-lowest rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-outline-variant max-h-[92vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3 border-b border-outline-variant mb-4">
                <h3 class="font-headline-md font-bold text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">add_circle</span>
                    <span>Tambah Pasal SOP Baru</span>
                </h3>
                <button type="button" onclick="closeAddSopModal()" class="text-on-surface-variant hover:text-on-surface p-1 rounded-lg hover:bg-surface-container-high transition-colors cursor-pointer">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="superadmin.php" method="POST" id="add-sop-form" onsubmit="return handleSopAddSubmit(event)" class="space-y-4">
                <input type="hidden" name="action" value="add_sop_section"/>
                <input type="hidden" name="content" id="add-sop-content" value=""/>
                
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                    <div class="sm:col-span-8">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">
                            Judul Pasal <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" required placeholder="Contoh: 14. Hak Cuti dan Evaluasi" class="w-full px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-semibold focus:ring-2 focus:ring-primary focus:outline-none"/>
                    </div>

                    <div class="sm:col-span-4">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">
                            Gaya Tampilan Card
                        </label>
                        <select name="style" class="w-full px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:outline-none cursor-pointer">
                            <option value="default">Standar / Netral (Putih)</option>
                            <option value="warning">Peringatan / Larangan (Oranye)</option>
                            <option value="primary">Ketentuan Utama (Biru)</option>
                            <option value="centered">Tengah / Center (Penutup)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">
                        Judul di Menu Samping <span class="text-slate-400 font-normal normal-case">(Opsional, jika kosong otomatis sama dengan Judul Pasal)</span>
                    </label>
                    <input type="text" name="nav_title" placeholder="Contoh: 14. Hak Cuti" class="w-full px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:outline-none"/>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                            Isi Peraturan / Konten Pasal <span class="text-red-500">*</span>
                        </label>
                        <!-- Tab Editor Mode Switcher -->
                        <div class="flex items-center bg-surface-container rounded-lg p-0.5 border border-outline-variant text-[11px] font-bold">
                            <button type="button" id="tab-add-visual" onclick="setSopEditorMode('add', 'visual')" class="px-2.5 py-1 rounded-md bg-white text-primary shadow-xs transition cursor-pointer flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">edit_note</span>
                                <span>Editor Visual</span>
                            </button>
                            <button type="button" id="tab-add-html" onclick="setSopEditorMode('add', 'html')" class="px-2.5 py-1 rounded-md text-slate-500 hover:text-on-surface transition cursor-pointer flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">code</span>
                                <span>Kode HTML</span>
                            </button>
                        </div>
                    </div>

                    <!-- Visual Editor (Quill) -->
                    <div id="add-quill-wrapper" class="block">
                        <div id="add-quill-editor"></div>
                    </div>

                    <!-- HTML Raw Textarea (Hidden by default) -->
                    <div id="add-html-wrapper" class="hidden">
                        <textarea id="add-raw-html" rows="7" placeholder="Tuliskan kode HTML di sini..." class="w-full font-mono text-xs px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:outline-none leading-relaxed"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-outline-variant">
                    <button type="button" onclick="closeAddSopModal()" class="px-4 py-2.5 border border-outline-variant hover:bg-surface-container-high rounded-xl text-sm font-bold transition cursor-pointer">Batal</button>
                    <button type="submit" class="px-6 py-2.5 bg-primary text-on-primary rounded-xl text-sm font-bold hover:bg-primary-container transition shadow-md flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-lg">check</span>
                        <span>Simpan Pasal Baru</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Pasal SOP -->
    <div id="edit-sop-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
        <div class="bg-surface-container-lowest rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-outline-variant max-h-[92vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3 border-b border-outline-variant mb-4">
                <h3 class="font-headline-md font-bold text-indigo-700 flex items-center gap-2">
                    <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">edit_document</span>
                    <span>Edit Pasal SOP</span>
                </h3>
                <button type="button" onclick="closeEditSopModal()" class="text-on-surface-variant hover:text-on-surface p-1 rounded-lg hover:bg-surface-container-high transition-colors cursor-pointer">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="superadmin.php" method="POST" id="edit-sop-form" onsubmit="return handleSopEditSubmit(event)" class="space-y-4">
                <input type="hidden" name="action" value="edit_sop_section"/>
                <input type="hidden" name="section_index" id="edit-sop-index" value=""/>
                <input type="hidden" name="content" id="edit-sop-content" value=""/>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                    <div class="sm:col-span-8">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">
                            Judul Pasal <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" id="edit-sop-title" required class="w-full px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-bold focus:ring-2 focus:ring-primary focus:outline-none"/>
                    </div>

                    <div class="sm:col-span-4">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">
                            Gaya Tampilan Card
                        </label>
                        <select name="style" id="edit-sop-style" class="w-full px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:outline-none cursor-pointer">
                            <option value="default">Standar / Netral (Putih)</option>
                            <option value="warning">Peringatan / Larangan (Oranye)</option>
                            <option value="primary">Ketentuan Utama (Biru)</option>
                            <option value="centered">Tengah / Center (Penutup)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">
                        Judul di Menu Samping <span class="text-slate-400 font-normal normal-case">(Opsional, jika kosong otomatis sama dengan Judul Pasal)</span>
                    </label>
                    <input type="text" name="nav_title" id="edit-sop-nav-title" class="w-full px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:outline-none"/>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                            Isi Peraturan / Konten Pasal <span class="text-red-500">*</span>
                        </label>
                        <!-- Tab Editor Mode Switcher -->
                        <div class="flex items-center bg-surface-container rounded-lg p-0.5 border border-outline-variant text-[11px] font-bold">
                            <button type="button" id="tab-edit-visual" onclick="setSopEditorMode('edit', 'visual')" class="px-2.5 py-1 rounded-md bg-white text-indigo-700 shadow-xs transition cursor-pointer flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">edit_note</span>
                                <span>Editor Visual</span>
                            </button>
                            <button type="button" id="tab-edit-html" onclick="setSopEditorMode('edit', 'html')" class="px-2.5 py-1 rounded-md text-slate-500 hover:text-on-surface transition cursor-pointer flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">code</span>
                                <span>Kode HTML</span>
                            </button>
                        </div>
                    </div>

                    <!-- Visual Editor (Quill) -->
                    <div id="edit-quill-wrapper" class="block">
                        <div id="edit-quill-editor"></div>
                    </div>

                    <!-- HTML Raw Textarea (Hidden by default) -->
                    <div id="edit-html-wrapper" class="hidden">
                        <textarea id="edit-raw-html" rows="8" placeholder="Tuliskan kode HTML di sini..." class="w-full font-mono text-xs px-3.5 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:outline-none leading-relaxed"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-outline-variant">
                    <button type="button" onclick="closeEditSopModal()" class="px-4 py-2.5 border border-outline-variant hover:bg-surface-container-high rounded-xl text-sm font-bold transition cursor-pointer">Batal</button>
                    <button type="submit" class="px-6 py-2.5 bg-primary text-on-primary rounded-xl text-sm font-bold hover:bg-primary-container transition shadow-md flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-lg">save</span>
                        <span>Simpan Perubahan Pasal</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    
    <!-- ── Modal Tambah Poin Legal ── -->
    <div id="modal-add-legal-section" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
        <div class="bg-surface-container-lowest rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-outline-variant max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3 border-b border-outline-variant mb-4 sticky top-0 bg-surface-container-lowest z-10">
                <h3 class="font-headline-md font-bold text-primary flex items-center gap-2" id="add-legal-modal-title">
                    <span class="material-symbols-outlined text-xl">add_circle</span>
                    <span>Tambah Poin Baru</span>
                </h3>
                <button type="button" onclick="closeAddLegalSectionModal()" class="text-on-surface-variant hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="superadmin.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_legal_section"/>
                <input type="hidden" id="add-legal-doc-type" name="doc_type" value="privacy"/>

                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1">Target Dokumen</label>
                    <select id="add-legal-doc-type-select" onchange="document.getElementById('add-legal-doc-type').value = this.value" class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none">
                        <option value="privacy">Kebijakan Privasi</option>
                        <option value="terms">Syarat & Ketentuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1">Judul Poin / Pasal <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: 6. Batasan Penggunaan Data" class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1">Icon Material Symbol</label>
                    <input type="text" name="icon" value="security" placeholder="security, gavel, lock, verified_user, badge" class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1">Isi Ketentuan / Penjelasan <span class="text-red-500">*</span></label>
                    <textarea name="content" rows="4" required placeholder="Tuliskan isi aturan atau penjelasan detail..." class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none leading-relaxed"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-outline-variant">
                    <button type="button" onclick="closeAddLegalSectionModal()" class="px-4 py-2 border border-outline-variant rounded-xl text-xs font-bold hover:bg-surface-container transition">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-primary text-on-primary rounded-xl text-xs font-bold hover:bg-primary-container transition shadow-md">Simpan Poin</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Modal Edit Poin Legal ── -->
    <div id="modal-edit-legal-section" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
        <div class="bg-surface-container-lowest rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-outline-variant max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3 border-b border-outline-variant mb-4 sticky top-0 bg-surface-container-lowest z-10">
                <h3 class="font-headline-md font-bold text-amber-600 flex items-center gap-2">
                    <span class="material-symbols-outlined text-xl">edit</span>
                    <span>Edit Poin Dokumen</span>
                </h3>
                <button type="button" onclick="closeEditLegalSectionModal()" class="text-on-surface-variant hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="superadmin.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="edit_legal_section"/>
                <input type="hidden" id="edit-legal-doc-type" name="doc_type" value="privacy"/>
                <input type="hidden" id="edit-legal-section-idx" name="section_index" value=""/>

                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1">Judul Poin / Pasal <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-legal-title" name="title" required class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-amber-600 focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1">Icon Material Symbol</label>
                    <input type="text" id="edit-legal-icon" name="icon" class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-amber-600 focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1">Isi Ketentuan / Penjelasan <span class="text-red-500">*</span></label>
                    <textarea id="edit-legal-content" name="content" rows="4" required class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-amber-600 focus:outline-none leading-relaxed"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-outline-variant">
                    <button type="button" onclick="closeEditLegalSectionModal()" class="px-4 py-2 border border-outline-variant rounded-xl text-xs font-bold hover:bg-surface-container transition">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 text-white rounded-xl text-xs font-bold hover:bg-amber-700 transition shadow-md">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Modal Edit Header Legal ── -->
    <div id="modal-edit-legal-header" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
        <div class="bg-surface-container-lowest rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-outline-variant">
            <div class="flex justify-between items-center pb-3 border-b border-outline-variant mb-4 sticky top-0 bg-surface-container-lowest z-10">
                <h3 class="font-headline-md font-bold text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-xl">edit_note</span>
                    <span>Edit Header Dokumen</span>
                </h3>
                <button type="button" onclick="closeEditLegalHeaderModal()" class="text-on-surface-variant hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="superadmin.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="save_legal_header"/>
                <input type="hidden" id="edit-legal-hdr-doc-type" name="doc_type" value="privacy"/>

                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1">Label Badge Kategori</label>
                    <input type="text" id="edit-legal-hdr-badge" name="badge" class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1">Judul Utama Dokumen <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-legal-hdr-title" name="title" required class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1">Paragraf Pembuka / Intro</label>
                    <textarea id="edit-legal-hdr-intro" name="intro" rows="3" class="w-full px-3.5 py-2 text-xs border border-outline-variant rounded-xl bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none leading-relaxed"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-outline-variant">
                    <button type="button" onclick="closeEditLegalHeaderModal()" class="px-4 py-2 border border-outline-variant rounded-xl text-xs font-bold hover:bg-surface-container transition">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-primary text-on-primary rounded-xl text-xs font-bold hover:bg-primary-container transition shadow-md">Simpan Header</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Modal Konfirmasi Hapus Legal ── -->
    <div id="modal-delete-legal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden backdrop-blur-sm">
        <div class="bg-surface-container-lowest rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-outline-variant text-center space-y-4">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                <span class="material-symbols-outlined text-2xl">delete_forever</span>
            </div>
            <div>
                <h3 class="font-headline-sm font-bold text-on-surface" id="delete-legal-modal-title">Kurangi / Hapus Poin?</h3>
                <p class="text-xs text-on-surface-variant mt-1" id="delete-legal-modal-desc">Apakah Anda yakin ingin menghapus poin ini?</p>
            </div>
            <form id="delete-legal-form" action="superadmin.php" method="POST" class="flex justify-center gap-2 pt-2">
                <input type="hidden" name="action" value="delete_legal_section"/>
                <input type="hidden" id="delete-legal-doc-type" name="doc_type" value="privacy"/>
                <input type="hidden" id="delete-legal-idx" name="section_index" value=""/>
                <button type="button" onclick="closeDeleteLegalModal()" class="px-4 py-2 border border-outline-variant rounded-xl text-xs font-bold hover:bg-surface-container transition">Batal</button>
                <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs transition shadow-md">Ya, Hapus</button>
            </form>
        </div>
    </div>

    <!-- Hidden Reset Legal Form -->
    <form id="reset-legal-form" action="superadmin.php" method="POST" class="hidden">
        <input type="hidden" name="action" value="reset_legal_data"/>
        <input type="hidden" id="reset-legal-doc-type" name="doc_type" value="privacy"/>
    </form>


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

        // ============================================================
        // SUPERADMIN USER COHORT YEAR & ROLE FILTERING SCRIPT
        // ============================================================
        let currentSuperadminYear = 'all';
        let currentSuperadminRole = 'all';

        function setSuperadminCohortPill(yr) {
            currentSuperadminYear = String(yr);
            const select = document.getElementById('user-year-select');
            if (select) select.value = currentSuperadminYear;
            highlightSuperadminActivePill(currentSuperadminYear);
            applyUserFilters();
        }

        function filterUsersByCohort(yr) {
            currentSuperadminYear = String(yr);
            highlightSuperadminActivePill(currentSuperadminYear);
            applyUserFilters();
        }

        function filterUsersByRole(role) {
            currentSuperadminRole = String(role);
            applyUserFilters();
        }

        function highlightSuperadminActivePill(activeCohort) {
            document.querySelectorAll('.superadmin-cohort-pill').forEach(pill => {
                const cohort = pill.getAttribute('data-cohort');
                if (cohort === activeCohort) {
                    pill.className = 'superadmin-cohort-pill px-3 py-1 rounded-full text-xs font-bold transition-all bg-primary text-on-primary shadow-xs cursor-pointer';
                } else {
                    pill.className = 'superadmin-cohort-pill px-3 py-1 rounded-full text-xs font-semibold text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high border border-outline-variant/60 transition-all cursor-pointer';
                }
            });
        }

        function applyUserFilters() {
            const rows = document.querySelectorAll('.superadmin-user-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const rowYear = row.getAttribute('data-year') || '';
                const rowRole = (row.getAttribute('data-role') || '').toLowerCase();

                const matchesYear = (currentSuperadminYear === 'all' || rowYear === currentSuperadminYear);
                const matchesRole = (currentSuperadminRole === 'all' || rowRole === currentSuperadminRole);

                if (matchesYear && matchesRole) {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });

            const emptyMsg = document.getElementById('no-superadmin-users-msg');
            if (emptyMsg) {
                if (visibleCount === 0) {
                    emptyMsg.classList.remove('hidden');
                } else {
                    emptyMsg.classList.add('hidden');
                }
            }
        }

        // ── MANAJEMEN SOP BUILDER ──────────────────────────────────────────
        let sopSections = <?php echo json_encode($sop_data['sections'] ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>;
        if (!Array.isArray(sopSections)) sopSections = [];

        function escapeSopAttr(text) {
            return String(text || '').replace(/"/g, '&quot;');
        }

        function renderSopSections(filter = '') {
            const container = document.getElementById('sop-sections-container');
            const countBadge = document.getElementById('sop-count-badge');
            if (!container) return;

            const filterClean = (filter || '').toLowerCase().trim();
            let visibleCount = 0;

            if (sopSections.length === 0) {
                container.innerHTML = `
                    <div class="p-8 text-center bg-surface-container-low border border-dashed border-outline-variant rounded-2xl">
                        <span class="material-symbols-outlined text-4xl text-slate-400 mb-2">menu_book</span>
                        <p class="font-bold text-on-surface text-sm">Belum ada pasal SOP terdaftar</p>
                        <p class="text-xs text-on-surface-variant mt-1">Klik tombol "+ Tambah Pasal" untuk mulai membuat pasal atau aturan baru.</p>
                        <button type="button" onclick="addSopSection()" class="mt-4 px-4 py-2 bg-primary text-on-primary font-bold text-xs rounded-xl hover:bg-primary-container transition-all cursor-pointer">
                            + Tambah Pasal Pertama
                        </button>
                    </div>
                `;
                if (countBadge) countBadge.textContent = '0 Pasal';
                return;
            }

            let html = '';
            sopSections.forEach((sec, idx) => {
                const titleStr = sec.title || '';
                const navStr = sec.nav_title || '';
                const contentStr = sec.content || '';
                const styleStr = sec.style || 'default';
                const secId = sec.id || ('sop-' + (idx + 1));

                // Filter check
                if (filterClean) {
                    const matchTitle = titleStr.toLowerCase().includes(filterClean);
                    const matchNav = navStr.toLowerCase().includes(filterClean);
                    const matchContent = contentStr.toLowerCase().includes(filterClean);
                    if (!matchTitle && !matchNav && !matchContent) {
                        return;
                    }
                }
                visibleCount++;

                let styleBadge = '';
                let styleBorder = 'border-outline-variant';
                if (styleStr === 'warning') {
                    styleBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">Larangan (Merah)</span>';
                    styleBorder = 'border-rose-200';
                } else if (styleStr === 'primary') {
                    styleBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">Nilai Utama (Biru)</span>';
                    styleBorder = 'border-blue-200';
                } else if (styleStr === 'centered') {
                    styleBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">Penutup (Tengah)</span>';
                    styleBorder = 'border-purple-200';
                } else {
                    styleBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">Standar (Netral)</span>';
                }

                html += `
                <div class="glass-card bg-surface-container-lowest border ${styleBorder} rounded-2xl p-4 sm:p-5 shadow-xs transition-all" id="sop-card-${idx}">
                    <!-- Card Top Header -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-outline-variant mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-surface-container-high text-primary font-bold text-xs flex items-center justify-center font-mono">#${idx + 1}</span>
                            <span class="font-bold text-sm text-on-surface line-clamp-1">${escapeHtml(titleStr || 'Pasal Tanpa Judul')}</span>
                            ${styleBadge}
                            <span class="text-[11px] font-mono text-slate-400 bg-slate-50 px-2 py-0.5 rounded-md border border-slate-100 hidden sm:inline">id: ${escapeHtml(secId)}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" onclick="moveSopSection(${idx}, -1)" ${idx === 0 ? 'disabled' : ''} class="p-1.5 rounded-lg border border-outline-variant hover:bg-surface-container-high text-on-surface-variant disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer" title="Pindah ke Atas">
                                <span class="material-symbols-outlined text-base">arrow_upward</span>
                            </button>
                            <button type="button" onclick="moveSopSection(${idx}, 1)" ${idx === sopSections.length - 1 ? 'disabled' : ''} class="p-1.5 rounded-lg border border-outline-variant hover:bg-surface-container-high text-on-surface-variant disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer" title="Pindah ke Bawah">
                                <span class="material-symbols-outlined text-base">arrow_downward</span>
                            </button>
                            <button type="button" onclick="deleteSopSection(${idx})" class="p-1.5 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 transition-colors cursor-pointer" title="Hapus Pasal">
                                <span class="material-symbols-outlined text-base">delete</span>
                            </button>
                        </div>
                    </div>

                    <!-- Card Body Fields -->
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                            <div class="sm:col-span-5">
                                <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">Judul Pasal (Tampil di Halaman)</label>
                                <input type="text" oninput="updateSopSectionField(${idx}, 'title', this.value)" value="${escapeSopAttr(titleStr)}" class="w-full px-3 py-2 text-xs font-bold bg-surface-container-lowest border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:outline-none" placeholder="Contoh: 1. Tujuan"/>
                            </div>
                            <div class="sm:col-span-4">
                                <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">Label Daftar Isi (Sidebar)</label>
                                <input type="text" oninput="updateSopSectionField(${idx}, 'nav_title', this.value)" value="${escapeSopAttr(navStr)}" class="w-full px-3 py-2 text-xs font-medium bg-surface-container-lowest border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:outline-none" placeholder="Label menu navigasi"/>
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">Tipe / Gaya Kartu</label>
                                <select onchange="updateSopSectionField(${idx}, 'style', this.value)" class="w-full px-3 py-2 text-xs font-semibold bg-surface-container-lowest border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:outline-none">
                                    <option value="default" ${styleStr === 'default' ? 'selected' : ''}>Standar (Netral)</option>
                                    <option value="warning" ${styleStr === 'warning' ? 'selected' : ''}>Larangan (Merah)</option>
                                    <option value="primary" ${styleStr === 'primary' ? 'selected' : ''}>Nilai Utama (Biru)</option>
                                    <option value="centered" ${styleStr === 'centered' ? 'selected' : ''}>Penutup (Tengah)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Content Editor & Quick Tags Bar -->
                        <div>
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                                <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Isi Konten Pasal (Mendukung HTML & Teks)</label>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-[11px] text-slate-400 mr-1 hidden md:inline">Sisipkan:</span>
                                    <button type="button" onclick="insertQuickTag(${idx}, 'p')" class="px-2 py-1 text-[11px] font-semibold bg-surface-container-high hover:bg-slate-200 rounded-lg text-slate-700 transition-colors cursor-pointer">+ Paragraf</button>
                                    <button type="button" onclick="insertQuickTag(${idx}, 'h4')" class="px-2 py-1 text-[11px] font-semibold bg-surface-container-high hover:bg-slate-200 rounded-lg text-slate-700 transition-colors cursor-pointer">+ Sub-Judul</button>
                                    <button type="button" onclick="insertQuickTag(${idx}, 'ul')" class="px-2 py-1 text-[11px] font-semibold bg-surface-container-high hover:bg-slate-200 rounded-lg text-slate-700 transition-colors cursor-pointer">+ Daftar List</button>
                                    <button type="button" onclick="toggleSopSectionPreview(${idx})" id="sop-prev-btn-${idx}" class="px-2.5 py-1 text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 rounded-lg transition-colors flex items-center gap-1 cursor-pointer">
                                        <span class="material-symbols-outlined text-[13px]">visibility</span>
                                        <span>Preview</span>
                                    </button>
                                </div>
                            </div>
                            <textarea id="sop-content-${idx}" oninput="updateSopSectionField(${idx}, 'content', this.value)" rows="5" class="w-full p-3 font-mono text-xs leading-relaxed bg-surface-container-lowest border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:outline-none resize-y">${escapeHtml(contentStr)}</textarea>
                            <div id="sop-preview-box-${idx}" class="hidden p-4 rounded-xl border border-dashed border-outline-variant bg-surface-container-low text-xs text-on-surface space-y-2 max-h-80 overflow-y-auto"></div>
                        </div>
                    </div>
                </div>
                `;
            });

            container.innerHTML = html;
            if (countBadge) {
                countBadge.textContent = `${sopSections.length} Pasal${filterClean ? ` (${visibleCount} cocok)` : ''}`;
            }
        }

        function updateSopSectionField(index, field, value) {
            if (!sopSections[index]) return;
            sopSections[index][field] = value;
            if (field === 'style') {
                renderSopSections(document.getElementById('sop-search-input')?.value || '');
            }
        }

        function addSopSection() {
            const nextNum = sopSections.length + 1;
            const newSec = {
                id: 'sop-' + Date.now(),
                nav_title: nextNum + '. Judul Aturan',
                title: nextNum + '. Judul Aturan Baru',
                style: 'default',
                content: '<p class="font-body-md text-body-md text-on-surface-variant">Tuliskan penjelasan dan ketentuan aturan magang di sini...</p>'
            };
            sopSections.push(newSec);
            const searchInput = document.getElementById('sop-search-input');
            if (searchInput) searchInput.value = '';
            renderSopSections();

            // Scroll to the newly created section card
            setTimeout(() => {
                const newCard = document.getElementById(`sop-card-${sopSections.length - 1}`);
                if (newCard) {
                    newCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    newCard.classList.add('ring-2', 'ring-primary');
                    setTimeout(() => newCard.classList.remove('ring-2', 'ring-primary'), 1500);
                }
            }, 100);
        }

        function deleteSopSection(index) {
            const title = sopSections[index]?.title || 'pasal ini';
            if (!confirm(`Hapus "${title}" dari daftar SOP?`)) return;
            sopSections.splice(index, 1);
            renderSopSections(document.getElementById('sop-search-input')?.value || '');
        }

        function moveSopSection(index, direction) {
            const newIndex = index + direction;
            if (newIndex < 0 || newIndex >= sopSections.length) return;
            const temp = sopSections[index];
            sopSections[index] = sopSections[newIndex];
            sopSections[newIndex] = temp;
            renderSopSections(document.getElementById('sop-search-input')?.value || '');
            const targetCard = document.getElementById(`sop-card-${newIndex}`);
            if (targetCard) targetCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function insertQuickTag(index, tagType) {
            const textarea = document.getElementById(`sop-content-${index}`);
            if (!textarea) return;

            let snippet = '';
            if (tagType === 'p') {
                snippet = '\n<p class="font-body-md text-body-md text-on-surface-variant">Paragraf penjelasan baru...</p>\n';
            } else if (tagType === 'h4') {
                snippet = '\n<h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">Sub-Judul Aturan</h4>\n';
            } else if (tagType === 'ul') {
                snippet = '\n<ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant">\n    <li>Poin aturan pertama</li>\n    <li>Poin aturan kedua</li>\n</ul>\n';
            }

            const startPos = textarea.selectionStart;
            const endPos = textarea.selectionEnd;
            const val = textarea.value;
            textarea.value = val.substring(0, startPos) + snippet + val.substring(endPos);
            textarea.selectionStart = textarea.selectionEnd = startPos + snippet.length;
            textarea.focus();
            updateSopSectionField(index, 'content', textarea.value);

            // Update preview if open
            const previewBox = document.getElementById(`sop-preview-box-${index}`);
            if (previewBox && !previewBox.classList.contains('hidden')) {
                previewBox.innerHTML = textarea.value;
            }
        }

        function toggleSopSectionPreview(index) {
            const textarea = document.getElementById(`sop-content-${index}`);
            const previewBox = document.getElementById(`sop-preview-box-${index}`);
            const btn = document.getElementById(`sop-prev-btn-${index}`);
            if (!textarea || !previewBox) return;

            if (previewBox.classList.contains('hidden')) {
                previewBox.innerHTML = textarea.value || '<em class="text-slate-400">Konten kosong.</em>';
                previewBox.classList.remove('hidden');
                textarea.classList.add('hidden');
                if (btn) btn.innerHTML = '<span class="material-symbols-outlined text-[13px]">code</span><span>Edit HTML</span>';
            } else {
                previewBox.classList.add('hidden');
                textarea.classList.remove('hidden');
                textarea.focus();
                if (btn) btn.innerHTML = '<span class="material-symbols-outlined text-[13px]">visibility</span><span>Preview</span>';
            }
        }

        function filterSopSections(query) {
            renderSopSections(query);
        }

        function handleSopFormSubmit(event) {
            const jsonInput = document.getElementById('sections_json_input');
            if (jsonInput) {
                jsonInput.value = JSON.stringify(sopSections);
            }
            return true;
        }

        function confirmResetSop() {
            if (confirm('Apakah Anda yakin ingin mengembalikan seluruh isi SOP ke standar awal Kedayweb? Semua perubahan kustom yang pernah disimpan akan digantikan dengan teks standar.')) {
                document.getElementById('reset-sop-form').submit();
            }
        }

        function switchSuperadminTab(tab) {
            const sectionLocation = document.getElementById('section-location');
            const sectionFooter = document.getElementById('section-footer');
            const sectionSop = document.getElementById('section-sop');
            const sectionAccount = document.getElementById('section-account');
            const sectionPositions = document.getElementById('section-positions');
            const sectionApproval = document.getElementById('section-approval');
            const sectionAbout = document.getElementById('section-about');
            const sectionLegal = document.getElementById('section-legal');
            const btnLocation = document.getElementById('nav-btn-location');
            const btnFooter = document.getElementById('nav-btn-footer');
            const btnSop = document.getElementById('nav-btn-sop');
            const btnAccount = document.getElementById('nav-btn-account');
            const btnApproval = document.getElementById('nav-btn-approval');
            const btnAll = document.getElementById('nav-btn-all');
            const activeLabel = document.getElementById('tab-active-label');

            const btnAbout = document.getElementById('nav-btn-about');
            const btnLegal = document.getElementById('nav-btn-legal');
            const allBtns = [btnLocation, btnFooter, btnSop, btnAbout, btnLegal, btnAccount, btnApproval, btnAll];
            allBtns.forEach(btn => {
                if (!btn) return;
                btn.classList.remove('bg-primary', 'text-on-primary', 'shadow-xs');
                btn.classList.add('text-on-surface-variant', 'hover:bg-surface-container-high');
            });

            if (tab === 'location') {
                if (sectionLocation) sectionLocation.classList.remove('hidden');
                if (sectionFooter) sectionFooter.classList.add('hidden');
                if (sectionSop) sectionSop.classList.add('hidden');
                if (sectionAccount) sectionAccount.classList.add('hidden');
                if (sectionPositions) sectionPositions.classList.add('hidden');
                if (sectionApproval) sectionApproval.classList.add('hidden');

                if (sectionAbout) sectionAbout.classList.add('hidden');
                if (sectionLegal) sectionLegal.classList.add('hidden');

                if (btnLocation) {
                    btnLocation.classList.add('bg-primary', 'text-on-primary', 'shadow-xs');
                    btnLocation.classList.remove('text-on-surface-variant', 'hover:bg-surface-container-high');
                }
                if (activeLabel) activeLabel.textContent = 'Mode: Set Absen';

                if (typeof geofenceMap !== 'undefined' && geofenceMap) {
                    setTimeout(() => {
                        geofenceMap.invalidateSize();
                    }, 150);
                }
                history.replaceState(null, null, '#location');
            } else if (tab === 'footer') {
                if (sectionLocation) sectionLocation.classList.add('hidden');
                if (sectionFooter) sectionFooter.classList.remove('hidden');
                if (sectionSop) sectionSop.classList.add('hidden');
                if (sectionAccount) sectionAccount.classList.add('hidden');
                if (sectionPositions) sectionPositions.classList.add('hidden');
                if (sectionApproval) sectionApproval.classList.add('hidden');

                if (sectionAbout) sectionAbout.classList.add('hidden');
                if (sectionLegal) sectionLegal.classList.add('hidden');

                if (btnFooter) {
                    btnFooter.classList.add('bg-primary', 'text-on-primary', 'shadow-xs');
                    btnFooter.classList.remove('text-on-surface-variant', 'hover:bg-surface-container-high');
                }
                if (activeLabel) activeLabel.textContent = 'Mode: Set Footer';
                history.replaceState(null, null, '#footer');
            } else if (tab === 'sop') {
                if (sectionLocation) sectionLocation.classList.add('hidden');
                if (sectionFooter) sectionFooter.classList.add('hidden');
                if (sectionSop) sectionSop.classList.remove('hidden');
                if (sectionAccount) sectionAccount.classList.add('hidden');
                if (sectionPositions) sectionPositions.classList.add('hidden');
                if (sectionApproval) sectionApproval.classList.add('hidden');

                if (sectionAbout) sectionAbout.classList.add('hidden');
                if (sectionLegal) sectionLegal.classList.add('hidden');

                if (btnSop) {
                    btnSop.classList.add('bg-primary', 'text-on-primary', 'shadow-xs');
                    btnSop.classList.remove('text-on-surface-variant', 'hover:bg-surface-container-high');
                }
                if (activeLabel) activeLabel.textContent = 'Mode: Set SOP';
                history.replaceState(null, null, '#sop');
                        } else if (tab === 'about') {
                if (sectionLocation) sectionLocation.classList.add('hidden');
                if (sectionFooter) sectionFooter.classList.add('hidden');
                if (sectionSop) sectionSop.classList.add('hidden');
                if (sectionAccount) sectionAccount.classList.add('hidden');
                if (sectionPositions) sectionPositions.classList.add('hidden');
                if (sectionApproval) sectionApproval.classList.add('hidden');
                if (sectionAbout) sectionAbout.classList.remove('hidden');
                if (sectionLegal) sectionLegal.classList.remove('hidden');

                if (btnAbout) {
                    btnAbout.classList.add('bg-primary', 'text-on-primary', 'shadow-xs');
                    btnAbout.classList.remove('text-on-surface-variant', 'hover:bg-surface-container-high');
                }
                if (activeLabel) activeLabel.textContent = 'Mode: Set Tentang';
                history.replaceState(null, null, '#about');
            } else if (tab === 'legal') {
                if (sectionLocation) sectionLocation.classList.add('hidden');
                if (sectionFooter) sectionFooter.classList.add('hidden');
                if (sectionSop) sectionSop.classList.add('hidden');
                if (sectionAccount) sectionAccount.classList.add('hidden');
                if (sectionPositions) sectionPositions.classList.add('hidden');
                if (sectionApproval) sectionApproval.classList.add('hidden');
                if (sectionAbout) sectionAbout.classList.add('hidden');
                if (sectionLegal) sectionLegal.classList.add('hidden');
                if (sectionLegal) sectionLegal.classList.remove('hidden');

                if (btnLegal) {
                    btnLegal.classList.add('bg-primary', 'text-on-primary', 'shadow-xs');
                    btnLegal.classList.remove('text-on-surface-variant', 'hover:bg-surface-container-high');
                }
                if (activeLabel) activeLabel.textContent = 'Mode: Set Kebijakan & Syarat';
                history.replaceState(null, null, '#legal');
} else if (tab === 'approval') {
                if (sectionLocation) sectionLocation.classList.add('hidden');
                if (sectionFooter) sectionFooter.classList.add('hidden');
                if (sectionSop) sectionSop.classList.add('hidden');
                if (sectionAccount) sectionAccount.classList.add('hidden');
                if (sectionPositions) sectionPositions.classList.add('hidden');
                if (sectionApproval) sectionApproval.classList.remove('hidden');

                if (sectionAbout) sectionAbout.classList.add('hidden');
                if (sectionLegal) sectionLegal.classList.add('hidden');

                if (btnApproval) {
                    btnApproval.classList.add('bg-primary', 'text-on-primary', 'shadow-xs');
                    btnApproval.classList.remove('text-on-surface-variant', 'hover:bg-surface-container-high');
                }
                if (activeLabel) activeLabel.textContent = 'Mode: Approval Sertifikat';
                history.replaceState(null, null, '#approval');
            } else if (tab === 'all') {
                if (sectionLocation) sectionLocation.classList.remove('hidden');
                if (sectionFooter) sectionFooter.classList.remove('hidden');
                if (sectionSop) sectionSop.classList.remove('hidden');
                if (sectionAccount) sectionAccount.classList.remove('hidden');
                if (sectionPositions) sectionPositions.classList.remove('hidden');
                if (sectionApproval) sectionApproval.classList.remove('hidden');
                if (sectionAbout) sectionAbout.classList.remove('hidden');
                if (sectionLegal) sectionLegal.classList.remove('hidden');

                if (btnAll) {
                    btnAll.classList.add('bg-primary', 'text-on-primary', 'shadow-xs');
                    btnAll.classList.remove('text-on-surface-variant', 'hover:bg-surface-container-high');
                }
                if (activeLabel) activeLabel.textContent = 'Mode: Tampilkan Semua';

                if (typeof geofenceMap !== 'undefined' && geofenceMap) {
                    setTimeout(() => {
                        geofenceMap.invalidateSize();
                    }, 150);
                }
                history.replaceState(null, null, '#all');
            } else { // default 'account'
                if (sectionLocation) sectionLocation.classList.add('hidden');
                if (sectionFooter) sectionFooter.classList.add('hidden');
                if (sectionSop) sectionSop.classList.add('hidden');
                if (sectionAccount) sectionAccount.classList.remove('hidden');
                if (sectionPositions) sectionPositions.classList.remove('hidden');
                if (sectionApproval) sectionApproval.classList.add('hidden');
                if (sectionAbout) sectionAbout.classList.add('hidden');
                if (sectionLegal) sectionLegal.classList.add('hidden');

                if (btnAccount) {
                    btnAccount.classList.add('bg-primary', 'text-on-primary', 'shadow-xs');
                    btnAccount.classList.remove('text-on-surface-variant', 'hover:bg-surface-container-high');
                }
                if (activeLabel) activeLabel.textContent = 'Mode: Set Account';
                history.replaceState(null, null, '#account');
            }
        }

        // ── Toggle Certificate Approval (Superadmin) ──────────────────────
        async function toggleCertApproval(certDbId, approveVal) {
            const btn = document.getElementById('btn-approve-' + certDbId);
            if (btn) {
                btn.disabled = true;
                btn.style.opacity = '0.6';
                btn.innerHTML = '<span class="material-symbols-outlined text-[14px] animate-spin">refresh</span><span>Memproses…</span>';
            }

            try {
                const fd = new FormData();
                fd.append('action', 'toggle_approval');
                fd.append('id', certDbId);
                fd.append('approved', approveVal);

                const res = await fetch('../certificate-api.php', { method: 'POST', body: fd });
                const data = await res.json();

                if (data.success) {
                    const row = document.getElementById('cert-row-' + certDbId);
                    // Update approved_at cell
                    const approvedAtCell = document.getElementById('approved-at-' + certDbId);
                    if (approvedAtCell) {
                        if (approveVal == 1 && data.approved_at) {
                            const dt = new Date(data.approved_at.replace(' ', 'T'));
                            approvedAtCell.textContent = dt.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) + ' ' + dt.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
                        } else {
                            approvedAtCell.innerHTML = '<span class="text-slate-400 italic">Belum disetujui</span>';
                        }
                    }
                    // Reload page to reflect all changes (badge count, row color, etc)
                    setTimeout(() => location.reload(), 800);
                } else {
                    alert('Gagal: ' + (data.message || 'Terjadi kesalahan.'));
                    if (btn) { btn.disabled = false; btn.style.opacity = ''; }
                }
            } catch(e) {
                alert('Gagal terhubung ke server.');
                if (btn) { btn.disabled = false; btn.style.opacity = ''; }
            }
        }

        // ============================================================
        // SOP MANAGEMENT SCRIPT
        // ============================================================
        const sopSectionsData = <?php echo json_encode($sop_data['sections'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        let addQuill = null;
        let editQuill = null;
        let currentAddMode = 'visual';
        let currentEditMode = 'visual';

        const quillToolbarOptions = [
            [{ 'header': [false, 3, 4] }],
            ['bold', 'italic', 'underline'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['clean']
        ];

        function initQuillEditors() {
            if (typeof Quill === 'undefined') return;

            if (!addQuill && document.getElementById('add-quill-editor')) {
                addQuill = new Quill('#add-quill-editor', {
                    theme: 'snow',
                    placeholder: 'Tuliskan peraturan atau poin-poin pasal di sini...',
                    modules: { toolbar: quillToolbarOptions }
                });
            }

            if (!editQuill && document.getElementById('edit-quill-editor')) {
                editQuill = new Quill('#edit-quill-editor', {
                    theme: 'snow',
                    placeholder: 'Tuliskan peraturan atau poin-poin pasal di sini...',
                    modules: { toolbar: quillToolbarOptions }
                });
            }
        }

        function setSopEditorMode(target, mode) {
            initQuillEditors();
            const isAdd = (target === 'add');
            const quillWrapper = document.getElementById(isAdd ? 'add-quill-wrapper' : 'edit-quill-wrapper');
            const htmlWrapper = document.getElementById(isAdd ? 'add-html-wrapper' : 'edit-html-wrapper');
            const rawTextarea = document.getElementById(isAdd ? 'add-raw-html' : 'edit-raw-html');
            const quillInstance = isAdd ? addQuill : editQuill;
            const tabVisual = document.getElementById(isAdd ? 'tab-add-visual' : 'tab-edit-visual');
            const tabHtml = document.getElementById(isAdd ? 'tab-add-html' : 'tab-edit-html');

            if (mode === 'html') {
                if (quillInstance) {
                    rawTextarea.value = quillInstance.root.innerHTML;
                }
                quillWrapper.classList.add('hidden');
                htmlWrapper.classList.remove('hidden');

                tabVisual.className = 'px-2.5 py-1 rounded-md text-slate-500 hover:text-on-surface transition cursor-pointer flex items-center gap-1';
                tabHtml.className = isAdd 
                    ? 'px-2.5 py-1 rounded-md bg-white text-primary shadow-xs transition cursor-pointer flex items-center gap-1'
                    : 'px-2.5 py-1 rounded-md bg-white text-indigo-700 shadow-xs transition cursor-pointer flex items-center gap-1';
                
                if (isAdd) currentAddMode = 'html'; else currentEditMode = 'html';
            } else {
                if (quillInstance) {
                    quillInstance.root.innerHTML = rawTextarea.value;
                }
                htmlWrapper.classList.add('hidden');
                quillWrapper.classList.remove('hidden');

                tabHtml.className = 'px-2.5 py-1 rounded-md text-slate-500 hover:text-on-surface transition cursor-pointer flex items-center gap-1';
                tabVisual.className = isAdd 
                    ? 'px-2.5 py-1 rounded-md bg-white text-primary shadow-xs transition cursor-pointer flex items-center gap-1'
                    : 'px-2.5 py-1 rounded-md bg-white text-indigo-700 shadow-xs transition cursor-pointer flex items-center gap-1';

                if (isAdd) currentAddMode = 'visual'; else currentEditMode = 'visual';
            }
        }

        function handleSopAddSubmit(e) {
            initQuillEditors();
            let finalHtml = '';
            if (currentAddMode === 'visual' && addQuill) {
                finalHtml = addQuill.root.innerHTML;
                if (addQuill.getText().trim().length === 0 && !finalHtml.includes('<img')) {
                    finalHtml = '';
                }
            } else {
                finalHtml = document.getElementById('add-raw-html').value;
            }
            if (!finalHtml.trim()) {
                alert('Isi peraturan / konten pasal tidak boleh kosong.');
                e.preventDefault();
                return false;
            }
            document.getElementById('add-sop-content').value = finalHtml;
            return true;
        }

        function handleSopEditSubmit(e) {
            initQuillEditors();
            let finalHtml = '';
            if (currentEditMode === 'visual' && editQuill) {
                finalHtml = editQuill.root.innerHTML;
                if (editQuill.getText().trim().length === 0 && !finalHtml.includes('<img')) {
                    finalHtml = '';
                }
            } else {
                finalHtml = document.getElementById('edit-raw-html').value;
            }
            if (!finalHtml.trim()) {
                alert('Isi peraturan / konten pasal tidak boleh kosong.');
                e.preventDefault();
                return false;
            }
            document.getElementById('edit-sop-content').value = finalHtml;
            return true;
        }

        function openAddSopModal() {
            initQuillEditors();
            const modal = document.getElementById('add-sop-modal');
            if (!modal) return;
            const form = modal.querySelector('form');
            if (form) form.reset();
            if (addQuill) {
                addQuill.setContents([]);
            }
            document.getElementById('add-raw-html').value = '';
            setSopEditorMode('add', 'visual');
            modal.classList.remove('hidden');
        }

        function closeAddSopModal() {
            const modal = document.getElementById('add-sop-modal');
            if (modal) modal.classList.add('hidden');
        }

        function openEditSopModal(index) {
            initQuillEditors();
            const item = sopSectionsData[index];
            if (!item) {
                alert('Data pasal tidak ditemukan.');
                return;
            }
            document.getElementById('edit-sop-index').value = index;
            document.getElementById('edit-sop-title').value = item.title || '';
            document.getElementById('edit-sop-nav-title').value = item.nav_title || item.title || '';
            document.getElementById('edit-sop-style').value = item.style || 'default';
            
            const rawContent = item.content || '';
            if (editQuill) {
                editQuill.root.innerHTML = rawContent;
            }
            document.getElementById('edit-raw-html').value = rawContent;
            setSopEditorMode('edit', 'visual');
            
            const modal = document.getElementById('edit-sop-modal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeEditSopModal() {
            const modal = document.getElementById('edit-sop-modal');
            if (modal) modal.classList.add('hidden');
        }

        function filterSopTable(query) {
            const q = (query || '').toLowerCase().trim();
            const items = document.querySelectorAll('.sop-section-item');
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(q) ? '' : 'none';
            });
        }

        async function confirmResetSop() {
            if (typeof window.showConfirmModal === 'function') {
                const confirmed = await window.showConfirmModal({
                    title: 'Reset Seluruh SOP ke Standar Awal?',
                    message: 'Apakah Anda yakin ingin mengembalikan seluruh isi dan pasal SOP ke format awal? Semua pasal tambahan yang telah dibuat akan dihapus.',
                    confirmText: 'Ya, Reset SOP',
                    cancelText: 'Batal',
                    type: 'danger'
                });
                if (confirmed) {
                    document.getElementById('reset-sop-form').submit();
                }
            } else {
                if (confirm('Apakah Anda yakin ingin mengembalikan seluruh isi SOP ke standar awal?')) {
                    document.getElementById('reset-sop-form').submit();
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initGeofenceMap();
            initQuillEditors();

            // Backdrop click close for SOP modals
            document.getElementById('add-sop-modal')?.addEventListener('click', function(e) {
                if (e.target === this) closeAddSopModal();
            });
            document.getElementById('edit-sop-modal')?.addEventListener('click', function(e) {
                if (e.target === this) closeEditSopModal();
            });

            let defaultTab = 'account';
            const actionSubmitted = '<?php echo htmlspecialchars($action ?? '', ENT_QUOTES, 'UTF-8'); ?>';
            if (actionSubmitted === 'save_attendance_zone') {
                defaultTab = 'location';
            } else if (actionSubmitted === 'save_footer_settings') {
                defaultTab = 'footer';
            } else if (actionSubmitted.indexOf('sop') !== -1) {
                defaultTab = 'sop';
            } else if (actionSubmitted.indexOf('about') !== -1) {
                defaultTab = 'about';
            } else if (actionSubmitted.indexOf('legal') !== -1) {
                defaultTab = 'legal';
            } else if (window.location.hash === '#location') {
                defaultTab = 'location';
            } else if (window.location.hash === '#footer') {
                defaultTab = 'footer';
            } else if (window.location.hash === '#sop') {
                defaultTab = 'sop';
            } else if (window.location.hash === '#about') {
                defaultTab = 'about';
            } else if (window.location.hash === '#legal') {
                defaultTab = 'legal';
            } else if (window.location.hash === '#approval') {
                defaultTab = 'approval';
            } else if (window.location.hash === '#all') {
                defaultTab = 'all';
            } else if (window.location.hash === '#account') {
                defaultTab = 'account';
            }
            switchSuperadminTab(defaultTab);
        });

        // ===== SET TANGGAL MULAI ABSEN INTERN =====
        let _startDateUserId = null;

        function openStartDateModal(userId, username, currentDate) {
            _startDateUserId = userId;
            document.getElementById('start-date-intern-name').textContent = username;
            document.getElementById('start-date-input').value = currentDate || '';
            document.getElementById('start-date-result-msg').className = 'hidden text-xs font-semibold p-3 rounded-xl';
            document.getElementById('start-date-result-msg').textContent = '';
            document.getElementById('start-date-save-btn').disabled = false;
            document.getElementById('start-date-save-btn').style.opacity = '';
            document.getElementById('start-date-modal').classList.remove('hidden');
        }

        function closeStartDateModal() {
            document.getElementById('start-date-modal').classList.add('hidden');
            _startDateUserId = null;
        }

        async function saveStartDate() {
            if (!_startDateUserId) return;
            const startDate = document.getElementById('start-date-input').value.trim();
            const btn = document.getElementById('start-date-save-btn');
            const msgEl = document.getElementById('start-date-result-msg');

            btn.disabled = true;
            btn.style.opacity = '0.6';
            msgEl.className = 'text-xs font-semibold p-3 rounded-xl bg-blue-50 text-blue-800 border border-blue-200';
            msgEl.textContent = 'Menyimpan...';

            try {
                const res = await fetch('../attendance-api.php?action=update_start_date', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: _startDateUserId, start_date: startDate })
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    msgEl.className = 'text-xs font-semibold p-3 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200';
                    msgEl.textContent = '✓ ' + (data.message || 'Berhasil disimpan!');
                    setTimeout(() => location.reload(), 900);
                } else {
                    msgEl.className = 'text-xs font-semibold p-3 rounded-xl bg-red-50 text-red-800 border border-red-200';
                    msgEl.textContent = '✗ ' + (data.error || 'Gagal menyimpan.');
                    btn.disabled = false;
                    btn.style.opacity = '';
                }
            } catch(e) {
                msgEl.className = 'text-xs font-semibold p-3 rounded-xl bg-red-50 text-red-800 border border-red-200';
                msgEl.textContent = '✗ Gagal terhubung ke server.';
                btn.disabled = false;
                btn.style.opacity = '';
            }
        }

        // Tutup modal start-date jika klik di backdrop
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('start-date-modal')?.addEventListener('click', function(e) {
                if (e.target === this) closeStartDateModal();
            });
        });
    
        // ===== SET TENTANG (MENAMBAH & MENGURANGI ISI / GAMBAR) =====
        const aboutSectionsData = <?php echo json_encode($about_data['extra_sections'] ?? []); ?>;

        function openAddAboutSectionModal() {
            const modal = document.getElementById('modal-add-about-section');
            if (modal) {
                const form = modal.querySelector('form');
                if (form) form.reset();
                modal.classList.remove('hidden');
            }
        }

        function closeAddAboutSectionModal() {
            const modal = document.getElementById('modal-add-about-section');
            if (modal) modal.classList.add('hidden');
        }

        function openEditAboutSectionModal(index) {
            const item = aboutSectionsData[index];
            if (!item) return;

            document.getElementById('edit-about-sec-idx').value = index;
            document.getElementById('edit-about-sec-title').value = item.title || '';
            document.getElementById('edit-about-sec-badge').value = item.badge || 'Informasi';
            document.getElementById('edit-about-sec-icon').value = item.icon || 'info';
            document.getElementById('edit-about-sec-style').value = item.style || 'two_column';
            document.getElementById('edit-about-sec-content').value = item.content || '';
            document.getElementById('edit-about-sec-image').value = item.image || '';

            const previewBox = document.getElementById('edit-about-sec-preview-box');
            const previewImg = document.getElementById('edit-about-sec-preview-img');
            const urlText = document.getElementById('edit-about-sec-url-text');

            if (item.image) {
                let imgSrc = item.image.startsWith('http') ? item.image : ('../' + item.image);
                previewImg.src = imgSrc;
                urlText.textContent = item.image;
                previewBox.classList.remove('hidden');
            } else {
                previewBox.classList.add('hidden');
            }

            const modal = document.getElementById('modal-edit-about-section');
            if (modal) modal.classList.remove('hidden');
        }

        function closeEditAboutSectionModal() {
            const modal = document.getElementById('modal-edit-about-section');
            if (modal) modal.classList.add('hidden');
        }

        function confirmDeleteAboutSection(index, title) {
            const modal = document.getElementById('modal-delete-about');
            if (!modal) return;
            document.getElementById('delete-about-action').value = 'delete_about_section';
            const idxInput = document.getElementById('delete-about-idx');
            idxInput.name = 'section_index';
            idxInput.value = index;
            document.getElementById('delete-about-modal-title').textContent = 'Kurangi / Hapus Bagian?';
            document.getElementById('delete-about-modal-desc').textContent = 'Apakah Anda yakin ingin menghapus bagian "' + title + '" beserta gambarnya dari halaman Tentang?';
            modal.classList.remove('hidden');
        }

        function closeDeleteAboutModal() {
            const modal = document.getElementById('modal-delete-about');
            if (modal) modal.classList.add('hidden');
        }

        async function confirmResetAboutSuperadmin() {
            if (typeof window.showConfirmModal === 'function') {
                const confirmed = await window.showConfirmModal({
                    title: 'Kembalikan Konten Tambahan Tentang?',
                    message: 'Apakah Anda yakin ingin menghapus semua bagian tambahan dan mengembalikan halaman Tentang ke standar awal?',
                    confirmText: 'Ya, Reset',
                    cancelText: 'Batal',
                    type: 'danger'
                });
                if (confirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'superadmin.php';
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'action';
                    input.value = 'reset_about_data';
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            } else {
                if (confirm('Apakah Anda yakin ingin menghapus semua bagian tambahan dan mengembalikan ke standar awal?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'superadmin.php';
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'action';
                    input.value = 'reset_about_data';
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }

    
        // ===== SET KEBIJAKAN PRIVASI & SYARAT KETENTUAN =====
        let currentLegalTab = 'privacy';
        const legalPrivacyData = <?php echo json_encode($privacy_data ?? []); ?>;
        const legalTermsData   = <?php echo json_encode($terms_data ?? []); ?>;

        function switchLegalSubtab(tab) {
            currentLegalTab = tab;
            const pnlPriv = document.getElementById('panel-legal-privacy');
            const pnlTerms = document.getElementById('panel-legal-terms');
            const btnPriv = document.getElementById('subtab-legal-privacy');
            const btnTerms = document.getElementById('subtab-legal-terms');

            if (tab === 'privacy') {
                if (pnlPriv) pnlPriv.classList.remove('hidden');
                if (pnlTerms) pnlTerms.classList.add('hidden');
                if (btnPriv) {
                    btnPriv.className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-primary text-on-primary shadow-xs cursor-pointer';
                }
                if (btnTerms) {
                    btnTerms.className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-on-surface-variant hover:bg-surface-container-high cursor-pointer';
                }
            } else {
                if (pnlPriv) pnlPriv.classList.add('hidden');
                if (pnlTerms) pnlTerms.classList.remove('hidden');
                if (btnTerms) {
                    btnTerms.className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-primary text-on-primary shadow-xs cursor-pointer';
                }
                if (btnPriv) {
                    btnPriv.className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-on-surface-variant hover:bg-surface-container-high cursor-pointer';
                }
            }
        }

        function openAddLegalSectionModal(docType) {
            const dt = docType || currentLegalTab || 'privacy';
            document.getElementById('add-legal-doc-type').value = dt;
            const sel = document.getElementById('add-legal-doc-type-select');
            if (sel) sel.value = dt;

            const modal = document.getElementById('modal-add-legal-section');
            if (modal) {
                const titleEl = document.getElementById('add-legal-modal-title');
                if (titleEl) {
                    titleEl.innerHTML = '<span class="material-symbols-outlined text-xl">add_circle</span><span>Tambah Poin ' + (dt === 'terms' ? 'Syarat & Ketentuan' : 'Kebijakan Privasi') + '</span>';
                }
                modal.querySelector('form')?.reset();
                document.getElementById('add-legal-doc-type').value = dt;
                if (sel) sel.value = dt;
                modal.classList.remove('hidden');
            }
        }

        function closeAddLegalSectionModal() {
            const modal = document.getElementById('modal-add-legal-section');
            if (modal) modal.classList.add('hidden');
        }

        function openEditLegalSectionModal(docType, index) {
            const dt = docType || 'privacy';
            const dataObj = (dt === 'terms') ? legalTermsData : legalPrivacyData;
            const item = dataObj.sections ? dataObj.sections[index] : null;
            if (!item) return;

            document.getElementById('edit-legal-doc-type').value = dt;
            document.getElementById('edit-legal-section-idx').value = index;
            document.getElementById('edit-legal-title').value = item.title || '';
            document.getElementById('edit-legal-icon').value = item.icon || (dt === 'terms' ? 'gavel' : 'security');
            document.getElementById('edit-legal-content').value = item.content || '';

            const modal = document.getElementById('modal-edit-legal-section');
            if (modal) modal.classList.remove('hidden');
        }

        function closeEditLegalSectionModal() {
            const modal = document.getElementById('modal-edit-legal-section');
            if (modal) modal.classList.add('hidden');
        }

        function openEditLegalHeaderModal(docType) {
            const dt = docType || currentLegalTab || 'privacy';
            const dataObj = (dt === 'terms') ? legalTermsData : legalPrivacyData;

            document.getElementById('edit-legal-hdr-doc-type').value = dt;
            document.getElementById('edit-legal-hdr-badge').value = dataObj.badge || '';
            document.getElementById('edit-legal-hdr-title').value = dataObj.title || '';
            document.getElementById('edit-legal-hdr-intro').value = dataObj.intro || '';

            const modal = document.getElementById('modal-edit-legal-header');
            if (modal) modal.classList.remove('hidden');
        }

        function closeEditLegalHeaderModal() {
            const modal = document.getElementById('modal-edit-legal-header');
            if (modal) modal.classList.add('hidden');
        }

        function confirmDeleteLegalSection(docType, index, title) {
            const modal = document.getElementById('modal-delete-legal');
            if (!modal) return;
            const dt = docType || 'privacy';
            const docName = (dt === 'terms') ? 'Syarat & Ketentuan' : 'Kebijakan Privasi';

            document.getElementById('delete-legal-doc-type').value = dt;
            document.getElementById('delete-legal-idx').value = index;
            document.getElementById('delete-legal-modal-title').textContent = 'Hapus Poin dari ' + docName + '?';
            document.getElementById('delete-legal-modal-desc').textContent = 'Apakah Anda yakin ingin menghapus poin "' + title + '" dari ' + docName + '?';
            modal.classList.remove('hidden');
        }

        function closeDeleteLegalModal() {
            const modal = document.getElementById('modal-delete-legal');
            if (modal) modal.classList.add('hidden');
        }

        async function confirmResetLegal(docType) {
            const dt = docType || currentLegalTab || 'privacy';
            const docName = (dt === 'terms') ? 'Syarat & Ketentuan' : 'Kebijakan Privasi';

            if (typeof window.showConfirmModal === 'function') {
                const confirmed = await window.showConfirmModal({
                    title: 'Reset ' + docName + '?',
                    message: 'Apakah Anda yakin ingin mengembalikan seluruh isi ' + docName + ' ke standar awal Kedayweb? Seluruh poin kustom akan direset.',
                    confirmText: 'Ya, Reset',
                    cancelText: 'Batal',
                    type: 'danger'
                });
                if (confirmed) {
                    document.getElementById('reset-legal-doc-type').value = dt;
                    document.getElementById('reset-legal-form').submit();
                }
            } else {
                if (confirm('Apakah Anda yakin ingin mengembalikan seluruh isi ' + docName + ' ke standar awal?')) {
                    document.getElementById('reset-legal-doc-type').value = dt;
                    document.getElementById('reset-legal-form').submit();
                }
            }
        }

    </script>
</body>
</html>
