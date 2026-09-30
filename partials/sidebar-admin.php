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

$admin_nav_items = [
    'dashboard'    => ['label' => 'Dashboard',            'icon' => 'dashboard',        'href' => $admin_prefix . 'admin-dashboard.php'],
    'tasks'        => ['label' => 'Kanban Board',         'icon' => 'view_kanban',      'href' => $root_prefix . 'tasks.php'],
    'internspace'  => ['label' => 'Riwayat Tugas Intern', 'icon' => 'history_edu',     'href' => $admin_prefix . 'admin-internspace.php'],
    'applications' => ['label' => 'Pendaftaran',          'icon' => 'description',      'href' => $root_prefix . 'applications.php'],
    'users'        => ['label' => 'Users',                'icon' => 'people',           'href' => $admin_prefix . 'admin-users.php'],
    'attendance'   => ['label' => 'Kehadiran Intern',     'icon' => 'event_available',  'href' => $admin_prefix . 'admin-attendance.php'],
    'projects'     => ['label' => 'Projects',             'icon' => 'folder_open',      'href' => $root_prefix . 'projects.php'],
    'galeri'       => ['label' => 'Galeri Foto',         'icon' => 'photo_library',   'href' => $root_prefix . 'galeryanakmagang.php'],
    'events'       => ['label' => 'Histori Event',       'icon' => 'event',           'href' => $root_prefix . 'event_history.php'],
    'article'      => ['label' => 'Kelola Artikel',       'icon' => 'newspaper',        'href' => $admin_prefix . 'admin-articles.php'],
    'certificates' => ['label' => 'Sertifikat',           'icon' => 'workspace_premium', 'href' => $admin_prefix . 'admin-certificates.php'],
    // 'stats'        => ['label' => 'Statistics',           'icon' => 'insights',         'href' => $admin_prefix . 'admin-stats.php'],
];

// Superadmin Panel hanya muncul untuk role superadmin
if (is_superadmin()) {
    $admin_nav_items['superadmin'] = ['label' => 'Superadmin Panel', 'icon' => 'verified_user', 'href' => $admin_prefix . 'superadmin.php'];
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

    <nav class="flex-1 space-y-sm overflow-y-auto pr-1">
        <?php foreach ($admin_nav_items as $key => $item): ?>
            <?php $is_active = ($active === $key); ?>
            <a onclick="closeMobileSidebar()" class="flex items-center gap-md px-md py-sm rounded-lg font-label-md <?php echo $is_active ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high'; ?>" href="<?php echo $item['href']; ?>">
                <span class="material-symbols-outlined"><?php echo $item['icon']; ?></span>
                <span><?php echo $item['label']; ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="mt-auto border-t border-outline-variant pt-md">
        <div class="flex items-center gap-sm px-sm mb-sm">
            <span class="material-symbols-outlined text-primary">account_circle</span>
            <span class="font-label-sm text-on-surface truncate"><?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <a class="flex items-center gap-md px-md py-sm rounded-lg font-label-md text-error hover:bg-error-container" href="<?php echo $root_prefix; ?>logout.php">
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
