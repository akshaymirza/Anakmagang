<?php
/**
 * partials/topnav-mobile.php
 * TopNavBar khusus tampilan mobile (md:hidden) untuk halaman intern.
 *
 * Variabel yang bisa di-set SEBELUM include ini:
 *   $mobile_title (string) -> teks brand yang ditampilkan (default: 'Kedayweb')
 */
if (!isset($mobile_title)) {
    $mobile_title = 'AnakMagang';
}

// Deteksi apakah file saat ini ada di dalam subfolder (mis. /admin/)
if (!isset($root_prefix)) {
    $script_path = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? $_SERVER['SCRIPT_NAME'] ?? '');
    $root_prefix = (basename(dirname($script_path)) === 'admin') ? '../' : '';
}
?>
<header class="md:hidden flex justify-between items-center px-md w-full h-16 bg-surface-container-lowest border-b border-outline-variant shadow-sm fixed top-0 z-30">
    <div class="flex items-center gap-2">
        <button onclick="toggleMobileSidebar()" type="button" class="p-2 text-on-surface hover:bg-surface-container-high rounded-lg shrink-0" aria-label="Buka Menu Sidebar">
            <span class="material-symbols-outlined text-2xl">menu</span>
        </button>
        <h1 class="font-headline-md text-headline-md font-bold text-primary font-geist">
            <a href="<?php echo $root_prefix; ?>index.php" class="hover:underline hover:opacity-80 transition-opacity" title="Kembali ke Beranda" aria-label="Kembali ke Beranda"><?php echo htmlspecialchars($mobile_title); ?></a>
        </h1>
    </div>
    <div class="flex items-center gap-sm">
        <span class="font-label-sm text-on-surface truncate max-w-[8rem]"><?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?></span>
        <a href="logout.php" class="text-error" aria-label="Keluar" title="Keluar">
            <span class="material-symbols-outlined">logout</span>
        </a>
    </div>
</header>
