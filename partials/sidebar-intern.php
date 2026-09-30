<?php
/**
 * partials/sidebar-intern.php
 * Left sidebar (SideNavBar) yang dipakai bersama oleh semua halaman intern:
 * dashboard.php, projects.php, attendance.php, tasks.php, applications.php, article.php
 */

require_once __DIR__ . '/../session.php';

if (!isset($active)) {
    $active = '';
}
if (!isset($show_admin_link)) {
    $show_admin_link = false;
}

$script_path = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? $_SERVER['SCRIPT_NAME'] ?? '');
$is_in_admin_dir = (basename(dirname($script_path)) === 'admin');
$root_prefix = $is_in_admin_dir ? '../' : '';
$admin_prefix = $is_in_admin_dir ? '' : 'admin/';

// Lookup certificate ID & status untuk intern yang login
$cert_href = $root_prefix . 'certificate.php';
$show_cert_button = false;

if (file_exists(__DIR__ . '/../Login/koneksi.php')) {
    include_once __DIR__ . '/../Login/koneksi.php';
}

if (!empty($conn) && !empty($_SESSION['user_id'])) {
    if (function_exists('sync_all_intern_certificates')) {
        sync_all_intern_certificates($conn);
    }
    $uid = intval($_SESSION['user_id']);
    // Cek sertifikat intern ini — hanya tampilkan jika status = 'active'
    $q_cert = mysqli_prepare($conn, "SELECT certificate_id, status FROM certificates WHERE user_id = ? LIMIT 1");
    if ($q_cert) {
        mysqli_stmt_bind_param($q_cert, "i", $uid);
        mysqli_stmt_execute($q_cert);
        $res_cert = mysqli_stmt_get_result($q_cert);
        if ($row_cert = mysqli_fetch_assoc($res_cert)) {
            if ($row_cert['status'] === 'active') {
                $show_cert_button = true;
                $cert_href = $root_prefix . 'verification.php?id=' . urlencode($row_cert['certificate_id']);
            }
        }
        mysqli_stmt_close($q_cert);
    }
}

$intern_nav_items = [
    'dashboard'    => ['label' => 'Dashboard',           'icon' => 'dashboard',       'href' => $root_prefix . 'dashboard.php',    'i18n' => 'nav_dashboard'],
    'projects'     => ['label' => 'Projects',            'icon' => 'folder_open',     'href' => $root_prefix . 'projects.php',     'i18n' => 'nav_projects'],
    'attendance'   => ['label' => 'Attendance',          'icon' => 'event_available', 'href' => $root_prefix . 'attendance.php',   'i18n' => 'nav_attendance'],
    'tasks'        => ['label' => 'Tasks',               'icon' => 'view_kanban',     'href' => $root_prefix . 'tasks.php',        'i18n' => 'nav_tasks'],
    'internspace'  => ['label' => 'Riwayat Tugas',       'icon' => 'history_edu',     'href' => $root_prefix . 'internspace.php',  'i18n' => 'nav_internspace'],
];

if ($show_cert_button) {
    $intern_nav_items['certificate'] = ['label' => 'Sertifikat Saya', 'icon' => 'workspace_premium', 'href' => $cert_href, 'i18n' => 'nav_certificate'];
}

$intern_nav_items['galeri']  = ['label' => 'Galeri Foto',         'icon' => 'photo_library',   'href' => $root_prefix . 'galeryanakmagang.php', 'i18n' => 'nav_galeri'];
$intern_nav_items['events']  = ['label' => 'Histori Event',       'icon' => 'event',           'href' => $root_prefix . 'event_history.php', 'i18n' => 'nav_events'];
$intern_nav_items['article'] = ['label' => 'Aktivitas & Artikel', 'icon' => 'newspaper',       'href' => $root_prefix . 'article.php',      'i18n' => 'nav_article'];
$intern_nav_items['sop']     = ['label' => 'SOP',                 'icon' => 'description',     'href' => $root_prefix . 'sop.php',          'i18n' => 'nav_sop'];
$intern_nav_items['about']   = ['label' => 'Tentang',             'icon' => 'info',            'href' => $root_prefix . 'about.php',        'i18n' => 'nav_about'];

if (current_user_role() === 'admin' || current_user_role() === 'superadmin') {
    $intern_nav_items['applications'] = ['label' => 'Applications', 'icon' => 'description', 'href' => $root_prefix . 'applications.php', 'i18n' => 'nav_applications'];
}
?>
<!-- Mobile Sidebar Backdrop Overlay -->
<div id="sidebar-backdrop" onclick="closeMobileSidebar()" class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity md:hidden"></div>

<!-- Sidebar Navigation -->
<aside id="app-sidebar" class="fixed left-0 top-0 bottom-0 z-50 flex flex-col h-full w-[16.5rem] bg-surface-container-lowest border-r border-outline-variant p-md transform -translate-x-full md:translate-x-0 transition-transform duration-300 shadow-2xl md:shadow-none">
    <div class="flex items-center justify-between mb-xl px-sm">
        <a href="<?php echo $root_prefix; ?>index.php" class="flex items-center gap-sm rounded-lg transition-all duration-200 hover:bg-surface-container-high group" title="Kembali ke Beranda" aria-label="Kembali ke Beranda">
            <img src="<?php echo $root_prefix; ?>uploads/Icon/Anak_Magang_Icon.jpg.jpeg" alt="Logo Anak Magang" class="w-8 h-8 rounded-lg object-cover shadow-xs border border-outline-variant/30 shrink-0 group-hover:opacity-90 transition-opacity"/>
            <div>
                <h1 class="font-headline-md text-headline-md text-primary font-bold group-hover:underline" data-i18n="brand_name">AnakMagang</h1>
                <p class="font-label-sm text-label-sm text-on-surface-variant" data-i18n="brand_subtitle">Portal PKL</p>
            </div>
        </a>
        <button onclick="closeMobileSidebar()" type="button" class="md:hidden text-on-surface-variant hover:text-primary p-1 rounded-lg">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>

    <nav class="flex-1 space-y-sm overflow-y-auto pr-1">
        <?php foreach ($intern_nav_items as $key => $item): ?>
            <?php $is_active = ($active === $key) || ($key === 'galeri' && $active === 'gallery') || ($key === 'events' && $active === 'events_history'); ?>
            <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md text-label-md transition-all duration-200 <?php echo $is_active ? 'bg-primary-container text-on-primary-container active:scale-[0.98] transition-transform' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $item['href']; ?>">
                <span class="material-symbols-outlined"<?php echo $is_active ? ' style="font-variation-settings: \'FILL\' 1;"' : ''; ?>><?php echo $item['icon']; ?></span>
                <span data-i18n="<?php echo $item['i18n']; ?>"><?php echo $item['label']; ?></span>
            </a>
        <?php endforeach; ?>
        <?php if ($show_admin_link || current_user_role() === 'admin' || current_user_role() === 'superadmin'): ?>
            <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm bg-primary-container text-on-primary-container rounded-lg font-label-md text-label-md active:scale-[0.98] transition-transform" href="<?php echo $admin_prefix; ?>admin-dashboard.php">
                <span class="material-symbols-outlined">admin_panel_settings</span>
                <span data-i18n="nav_admin_dashboard">Admin Dashboard</span>
            </a>
        <?php endif; ?>
    </nav>
    <div class="mt-auto border-t border-outline-variant pt-md">
        <div class="flex items-center gap-sm px-sm mb-sm">
            <span class="material-symbols-outlined text-primary">account_circle</span>
            <span class="font-label-sm text-on-surface truncate"><?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <a class="flex items-center gap-md px-md py-sm rounded-lg font-label-md text-label-md text-error hover:bg-error-container" href="<?php echo $root_prefix; ?>logout.php">
            <span class="material-symbols-outlined">logout</span>
            <span>Keluar</span>
        </a>
    </div>
</aside>

<script>
function toggleMobileSidebar() {
    const sidebar = document.getElementById('app-sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    if (!sidebar) return;
    const isClosed = sidebar.classList.contains('-translate-x-full');
    if (isClosed) {
        sidebar.classList.remove('-translate-x-full');
        if (backdrop) backdrop.classList.remove('hidden');
    } else {
        sidebar.classList.add('-translate-x-full');
        if (backdrop) backdrop.classList.add('hidden');
    }
}
function closeMobileSidebar() {
    const sidebar = document.getElementById('app-sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    if (sidebar) sidebar.classList.add('-translate-x-full');
    if (backdrop) backdrop.classList.add('hidden');
}
</script>
