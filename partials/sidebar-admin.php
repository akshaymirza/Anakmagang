<?php
/**
 * partials/sidebar-admin.php
 * Left sidebar (Sidebar) yang dipakai bersama oleh semua halaman admin:
 * admin-dashboard.php, admin-users.php, admin-attendance.php, admin-projects.php, admin-stats.php, admin-articles.php, superadmin.php
 */
if (!isset($active)) {
    $active = '';
}
if (!isset($sidebar_title)) {
    $sidebar_title = 'AnakMagang';
}
if (!isset($sidebar_subtitle)) {
    $sidebar_subtitle = 'Admin Panel';
}

$script_path = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? $_SERVER['SCRIPT_NAME'] ?? '');
$is_in_admin_dir = (basename(dirname($script_path)) === 'admin');
$admin_prefix = $is_in_admin_dir ? '' : 'admin/';
$root_prefix = $is_in_admin_dir ? '../' : '';

// Cek status aktif untuk masing-masing grup dropdown
$is_tugas_active = in_array($active, ['tasks', 'internspace']);
$is_admin_menu_active = in_array($active, ['applications', 'users', 'superadmin']);
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
                <?php if (isset($sidebar_title_html)): ?>
                    <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-surface font-bold group-hover:underline"><?php echo $sidebar_title_html; ?></h2>
                <?php else: ?>
                    <h2 class="font-headline-lg text-on-surface font-bold group-hover:underline"><?php echo htmlspecialchars($sidebar_title); ?></h2>
                <?php endif; ?>
                <p class="font-label-sm text-label-sm text-on-surface-variant"><?php echo htmlspecialchars($sidebar_subtitle); ?></p>
            </div>
        </a>
        <button onclick="closeMobileSidebar()" type="button" class="md:hidden text-on-surface-variant hover:text-primary p-1 rounded-lg">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>

    <nav class="flex-1 space-y-xs overflow-y-auto pr-1">
        <!-- Dashboard -->
        <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md transition-all duration-200 <?php echo ($active === 'dashboard') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $admin_prefix; ?>admin-dashboard.php">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Dashboard</span>
        </a>

        <!-- Dropdown 1: Tugas & Kanban -->
        <div>
            <button type="button" onclick="toggleSidebarDropdown('dropdown-tugas')" class="w-full flex items-center justify-between px-md py-sm rounded-lg font-label-md text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors cursor-pointer <?php echo $is_tugas_active ? 'font-bold text-primary' : ''; ?>">
                <div class="flex items-center gap-md">
                    <span class="material-symbols-outlined">assignment</span>
                    <span>Tugas & Kanban</span>
                </div>
                <span id="arrow-dropdown-tugas" class="material-symbols-outlined text-[18px] transition-transform duration-200 <?php echo $is_tugas_active ? 'rotate-180' : ''; ?>">expand_more</span>
            </button>
            <div id="dropdown-tugas" class="<?php echo $is_tugas_active ? '' : 'hidden'; ?> pl-sm space-y-xs pt-xs border-l-2 border-outline-variant/40 ml-md my-xs">
                <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md transition-all duration-200 <?php echo ($active === 'tasks') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $root_prefix; ?>tasks.php">
                    <span class="material-symbols-outlined text-[20px]">view_kanban</span>
                    <span>Kanban Board</span>
                </a>
                <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md transition-all duration-200 <?php echo ($active === 'internspace') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $admin_prefix; ?>admin-internspace.php">
                    <span class="material-symbols-outlined text-[20px]">history_edu</span>
                    <span>Tugas Intern</span>
                </a>
            </div>
        </div>

        <!-- Dropdown 2: Menu Admin (Pendaftaran & Users) -->
        <div>
            <button type="button" onclick="toggleSidebarDropdown('dropdown-admin-menu')" class="w-full flex items-center justify-between px-md py-sm rounded-lg font-label-md text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors cursor-pointer <?php echo $is_admin_menu_active ? 'font-bold text-primary' : ''; ?>">
                <div class="flex items-center gap-md">
                    <span class="material-symbols-outlined">admin_panel_settings</span>
                    <span>Menu Admin</span>
                </div>
                <span id="arrow-dropdown-admin-menu" class="material-symbols-outlined text-[18px] transition-transform duration-200 <?php echo $is_admin_menu_active ? 'rotate-180' : ''; ?>">expand_more</span>
            </button>
            <div id="dropdown-admin-menu" class="<?php echo $is_admin_menu_active ? '' : 'hidden'; ?> pl-sm space-y-xs pt-xs border-l-2 border-outline-variant/40 ml-md my-xs">
                <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md transition-all duration-200 <?php echo ($active === 'applications') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $root_prefix; ?>applications.php">
                    <span class="material-symbols-outlined text-[20px]">description</span>
                    <span>Pendaftaran</span>
                </a>
                <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md transition-all duration-200 <?php echo ($active === 'users') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $admin_prefix; ?>admin-users.php">
                    <span class="material-symbols-outlined text-[20px]">people</span>
                    <span>Users</span>
                </a>
                <?php if (function_exists('is_superadmin') && is_superadmin()): ?>
                    <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md transition-all duration-200 <?php echo ($active === 'superadmin') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $admin_prefix; ?>superadmin.php">
                        <span class="material-symbols-outlined text-[20px]">verified_user</span>
                        <span>Superadmin Panel</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Kehadiran Intern -->
        <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md transition-all duration-200 <?php echo ($active === 'attendance') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $admin_prefix; ?>admin-attendance.php">
            <span class="material-symbols-outlined">event_available</span>
            <span>Kehadiran Intern</span>
        </a>

        <!-- Projects -->
        <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md transition-all duration-200 <?php echo ($active === 'projects') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $root_prefix; ?>projects.php">
            <span class="material-symbols-outlined">folder_open</span>
            <span>Projects</span>
        </a>

        <!-- Dropdown 3: Media & Artikel (Galeri, Event, Artikel) -->
        <div>
            <button type="button" onclick="toggleSidebarDropdown('dropdown-media')" class="w-full flex items-center justify-between px-md py-sm rounded-lg font-label-md text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors cursor-pointer <?php echo $is_media_active ? 'font-bold text-primary' : ''; ?>">
                <div class="flex items-center gap-md">
                    <span class="material-symbols-outlined">collections</span>
                    <span>Media & Artikel</span>
                </div>
                <span id="arrow-dropdown-media" class="material-symbols-outlined text-[18px] transition-transform duration-200 <?php echo $is_media_active ? 'rotate-180' : ''; ?>">expand_more</span>
            </button>
            <div id="dropdown-media" class="<?php echo $is_media_active ? '' : 'hidden'; ?> pl-sm space-y-xs pt-xs border-l-2 border-outline-variant/40 ml-md my-xs">
                <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md transition-all duration-200 <?php echo ($active === 'galeri' || $active === 'gallery') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $root_prefix; ?>galeryanakmagang.php">
                    <span class="material-symbols-outlined text-[20px]">photo_library</span>
                    <span>Galeri Foto</span>
                </a>
                <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md transition-all duration-200 <?php echo ($active === 'events' || $active === 'events_history') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $root_prefix; ?>event_history.php">
                    <span class="material-symbols-outlined text-[20px]">event</span>
                    <span>Histori Event</span>
                </a>
                <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md transition-all duration-200 <?php echo ($active === 'article') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $admin_prefix; ?>admin-articles.php">
                    <span class="material-symbols-outlined text-[20px]">newspaper</span>
                    <span>Kelola Artikel</span>
                </a>
            </div>
        </div>

        <!-- Sertifikat -->
        <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md transition-all duration-200 <?php echo ($active === 'certificates') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $admin_prefix; ?>admin-certificates.php">
            <span class="material-symbols-outlined">workspace_premium</span>
            <span>Sertifikat</span>
        </a>

        <!-- SOP -->
        <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md transition-all duration-200 <?php echo ($active === 'sop') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $root_prefix; ?>sop.php">
            <span class="material-symbols-outlined">description</span>
            <span>SOP</span>
        </a>

        <!-- Tentang -->
        <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md transition-all duration-200 <?php echo ($active === 'about') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $root_prefix; ?>about.php">
            <span class="material-symbols-outlined">info</span>
            <span>Tentang</span>
        </a>
    </nav>
    <div class="mt-auto border-t border-outline-variant pt-md space-y-xs">
        <div class="flex items-center gap-md px-md py-sm rounded-lg font-label-md text-on-surface">
            <span class="material-symbols-outlined text-primary">account_circle</span>
            <span class="truncate font-semibold"><?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <a class="flex items-center gap-md px-md py-sm rounded-lg font-label-md text-error hover:bg-error-container transition-colors" href="<?php echo $root_prefix; ?>logout.php">
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
