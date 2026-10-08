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

$is_tugas_active = in_array($active, ['tasks', 'internspace']);
$is_media_active = in_array($active, ['galeri', 'events', 'article', 'gallery', 'events_history']);
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

    <nav class="flex-1 space-y-xs overflow-y-auto pr-1">
        <!-- Dashboard -->
        <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md text-label-md transition-all duration-200 <?php echo ($active === 'dashboard') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $root_prefix; ?>dashboard.php">
            <span class="material-symbols-outlined"<?php echo ($active === 'dashboard') ? ' style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>dashboard</span>
            <span data-i18n="nav_dashboard">Dashboard</span>
        </a>

        <!-- Projects -->
        <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md text-label-md transition-all duration-200 <?php echo ($active === 'projects') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $root_prefix; ?>projects.php">
            <span class="material-symbols-outlined"<?php echo ($active === 'projects') ? ' style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>folder_open</span>
            <span data-i18n="nav_projects">Projects</span>
        </a>

        <!-- Attendance -->
        <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md text-label-md transition-all duration-200 <?php echo ($active === 'attendance') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $root_prefix; ?>attendance.php">
            <span class="material-symbols-outlined"<?php echo ($active === 'attendance') ? ' style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>event_available</span>
            <span data-i18n="nav_attendance">Attendance</span>
        </a>

        <!-- Dropdown: Tugas & Kanban -->
        <div>
            <button type="button" onclick="toggleSidebarDropdown('dropdown-tugas-intern')" class="w-full flex items-center justify-between px-md py-sm rounded-lg font-label-md text-label-md text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors cursor-pointer <?php echo $is_tugas_active ? 'font-bold text-primary' : ''; ?>">
                <div class="flex items-center gap-md">
                    <span class="material-symbols-outlined">assignment</span>
                    <span>Tugas & Kanban</span>
                </div>
                <span id="arrow-dropdown-tugas-intern" class="material-symbols-outlined text-[18px] transition-transform duration-200 <?php echo $is_tugas_active ? 'rotate-180' : ''; ?>">expand_more</span>
            </button>
            <div id="dropdown-tugas-intern" class="<?php echo $is_tugas_active ? '' : 'hidden'; ?> pl-sm space-y-xs pt-xs border-l-2 border-outline-variant/40 ml-md my-xs">
                <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md text-label-md transition-all duration-200 <?php echo ($active === 'tasks') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $root_prefix; ?>tasks.php">
                    <span class="material-symbols-outlined text-[20px]"<?php echo ($active === 'tasks') ? ' style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>view_kanban</span>
                    <span data-i18n="nav_tasks">Kanban Board</span>
                </a>
                <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md text-label-md transition-all duration-200 <?php echo ($active === 'internspace') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $root_prefix; ?>internspace.php">
                    <span class="material-symbols-outlined text-[20px]"<?php echo ($active === 'internspace') ? ' style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>history_edu</span>
                    <span data-i18n="nav_internspace">Riwayat Tugas</span>
                </a>
            </div>
        </div>

        <?php if ($show_cert_button): ?>
            <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md text-label-md transition-all duration-200 <?php echo ($active === 'certificate') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $cert_href; ?>">
                <span class="material-symbols-outlined"<?php echo ($active === 'certificate') ? ' style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>workspace_premium</span>
                <span data-i18n="nav_certificate">Sertifikat Saya</span>
            </a>
        <?php endif; ?>

        <!-- Dropdown: Media & Artikel -->
        <div>
            <button type="button" onclick="toggleSidebarDropdown('dropdown-media-intern')" class="w-full flex items-center justify-between px-md py-sm rounded-lg font-label-md text-label-md text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors cursor-pointer <?php echo $is_media_active ? 'font-bold text-primary' : ''; ?>">
                <div class="flex items-center gap-md">
                    <span class="material-symbols-outlined">collections</span>
                    <span>Media & Artikel</span>
                </div>
                <span id="arrow-dropdown-media-intern" class="material-symbols-outlined text-[18px] transition-transform duration-200 <?php echo $is_media_active ? 'rotate-180' : ''; ?>">expand_more</span>
            </button>
            <div id="dropdown-media-intern" class="<?php echo $is_media_active ? '' : 'hidden'; ?> pl-sm space-y-xs pt-xs border-l-2 border-outline-variant/40 ml-md my-xs">
                <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md text-label-md transition-all duration-200 <?php echo ($active === 'galeri' || $active === 'gallery') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $root_prefix; ?>galeryanakmagang.php">
                    <span class="material-symbols-outlined text-[20px]"<?php echo ($active === 'galeri' || $active === 'gallery') ? ' style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>photo_library</span>
                    <span data-i18n="nav_galeri">Galeri Foto</span>
                </a>
                <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md text-label-md transition-all duration-200 <?php echo ($active === 'events' || $active === 'events_history') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $root_prefix; ?>event_history.php">
                    <span class="material-symbols-outlined text-[20px]"<?php echo ($active === 'events' || $active === 'events_history') ? ' style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>event</span>
                    <span data-i18n="nav_events">Histori Event</span>
                </a>
                <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md text-label-md transition-all duration-200 <?php echo ($active === 'article') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $root_prefix; ?>article.php">
                    <span class="material-symbols-outlined text-[20px]"<?php echo ($active === 'article') ? ' style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>newspaper</span>
                    <span data-i18n="nav_article">Aktivitas & Artikel</span>
                </a>
            </div>
        </div>

        <!-- SOP -->
        <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md text-label-md transition-all duration-200 <?php echo ($active === 'sop') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $root_prefix; ?>sop.php">
            <span class="material-symbols-outlined"<?php echo ($active === 'sop') ? ' style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>description</span>
            <span data-i18n="nav_sop">SOP</span>
        </a>

        <!-- Tentang -->
        <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md text-label-md transition-all duration-200 <?php echo ($active === 'about') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $root_prefix; ?>about.php">
            <span class="material-symbols-outlined"<?php echo ($active === 'about') ? ' style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>info</span>
            <span data-i18n="nav_about">Tentang</span>
        </a>

        <?php if ($show_admin_link || current_user_role() === 'admin' || current_user_role() === 'superadmin'): ?>
            <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm bg-primary-container text-on-primary-container rounded-lg font-label-md text-label-md active:scale-[0.98] transition-transform" href="<?php echo $admin_prefix; ?>admin-dashboard.php">
                <span class="material-symbols-outlined">admin_panel_settings</span>
                <span data-i18n="nav_admin_dashboard">Admin Dashboard</span>
            </a>
        <?php endif; ?>
    </nav>
    <div class="mt-auto border-t border-outline-variant pt-md space-y-xs">
        <div class="flex items-center gap-md px-md py-sm rounded-lg font-label-md text-on-surface">
            <span class="material-symbols-outlined text-primary">account_circle</span>
            <span class="truncate font-semibold"><?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <a class="flex items-center gap-md px-md py-sm rounded-lg font-label-md text-label-md text-error hover:bg-error-container transition-colors" href="<?php echo $root_prefix; ?>logout.php">
            <span class="material-symbols-outlined">logout</span>
            <span>Keluar</span>
        </a>
    </div>
</aside>

<script>
function toggleSidebarDropdown(id) {
    const el = document.getElementById(id);
    const arrow = document.getElementById('arrow-' + id);
    if (!el) return;
    const isHidden = el.classList.contains('hidden');
    if (isHidden) {
        el.classList.remove('hidden');
        if (arrow) arrow.classList.add('rotate-180');
    } else {
        el.classList.add('hidden');
        if (arrow) arrow.classList.remove('rotate-180');
    }
}
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
