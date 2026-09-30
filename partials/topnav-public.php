<?php
/**
 * partials/topnav-public.php
 * TopNavBar untuk halaman publik (tanpa sidebar): index.php, verification.php, article.php
 */
$script_path = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? $_SERVER['SCRIPT_NAME'] ?? '');
$is_in_admin_dir = (basename(dirname($script_path)) === 'admin');
$root_prefix = $is_in_admin_dir ? '../' : '';

if (!isset($nav_icon)) {
    $nav_icon = 'work';
}
if (!isset($nav_home_href)) {
    $nav_home_href = $root_prefix . 'index.php';
}
if (!isset($nav_cta_label)) {
    $nav_cta_label = 'Masuk Portal';
}
if (!isset($nav_cta_href)) {
    $nav_cta_href = $root_prefix . 'Login/login.php';
}
?>
<header class="bg-surface-container-lowest border-b border-outline-variant shadow-sm sticky top-0 z-50">
    <div class="flex justify-between items-center px-gutter w-full max-w-container-max mx-auto h-16">
        <!-- Logo & Brand -->
        <div class="flex items-center gap-sm shrink-0">
            <a href="<?php echo htmlspecialchars($nav_home_href); ?>" class="flex items-center gap-sm group">
                <img src="<?php echo $root_prefix; ?>uploads/Icon/Anak_Magang_Icon.jpg.jpeg" alt="Logo AnakMagang" class="w-8 h-8 rounded-lg object-cover shadow-xs border border-outline-variant/30 shrink-0 group-hover:opacity-90 transition-opacity"/>
                <span class="font-headline-md text-headline-md font-bold text-primary" data-i18n="brand_name">AnakMagang</span>
            </a>
        </div>

        <!-- Desktop Navigation Links (Visible on xl+) -->
        <nav class="hidden xl:flex items-center gap-md">
            <a href="<?php echo $root_prefix; ?>about.php" class="px-3 py-2 rounded-lg font-label-md text-body-sm text-on-surface hover:text-primary hover:bg-surface-container-high transition-colors">Tentang</a>
            <a href="<?php echo $root_prefix; ?>event_history.php" class="px-3 py-2 rounded-lg font-label-md text-body-sm text-on-surface hover:text-primary hover:bg-surface-container-high transition-colors">Histori Event</a>
            <a href="<?php echo $root_prefix; ?>galeryanakmagang.php" class="px-3 py-2 rounded-lg font-label-md text-body-sm text-on-surface hover:text-primary hover:bg-surface-container-high transition-colors">Galeri Magang</a>
            <a href="<?php echo $root_prefix; ?>article.php" class="px-3 py-2 rounded-lg font-label-md text-body-sm text-on-surface hover:text-primary hover:bg-surface-container-high transition-colors">Artikel</a>
            <!-- <a href="<?php echo $root_prefix; ?>index.php#features" class="px-3 py-2 rounded-lg font-label-md text-body-sm text-on-surface hover:text-primary hover:bg-surface-container-high transition-colors" data-i18n="nav_features">Fitur</a>
            <a href="<?php echo $root_prefix; ?>index.php#faq" class="px-3 py-2 rounded-lg font-label-md text-body-sm text-on-surface hover:text-primary hover:bg-surface-container-high transition-colors" data-i18n="nav_faq">FAQ</a>
            <a href="<?php echo $root_prefix; ?>index.php#contact" class="px-3 py-2 rounded-lg font-label-md text-body-sm text-on-surface hover:text-primary hover:bg-surface-container-high transition-colors" data-i18n="nav_contact">Kontak</a> -->
        </nav>

        <!-- Desktop & Tablet CTA Actions -->
        <div class="hidden sm:flex items-center gap-2 shrink-0">
            <a href="<?php echo $root_prefix; ?>verification.php" class="border border-outline-variant text-primary hover:bg-primary hover:text-white px-3 py-2 rounded-lg font-label-md text-xs sm:text-sm transition-all flex items-center gap-1.5 font-bold shadow-xs hover:shadow-sm group" title="Verifikasi Keaslian Sertifikat">
                <span class="material-symbols-outlined text-[18px] text-primary group-hover:text-white" style="font-variation-settings: 'FILL' 1;">verified</span>
                <span class="hidden md:inline">Verifikasi Sertifikat</span>
                <span class="inline md:hidden">Verifikasi</span>
            </a>
            <a href="<?php echo htmlspecialchars($nav_cta_href); ?>" class="bg-primary-container text-on-primary px-3.5 py-2 rounded-lg font-label-md text-xs sm:text-sm hover:bg-primary transition-colors font-bold shadow-xs" data-i18n="btn_portal_login"><?php echo htmlspecialchars($nav_cta_label); ?></a>
        </div>

        <!-- Mobile Menu Toggle Button (Visible below xl) -->
        <button id="public-mobile-menu-btn" type="button" class="xl:hidden p-2 text-on-surface hover:bg-surface-container-high rounded-lg transition-colors focus:outline-none" aria-label="Buka Menu">
            <span id="public-mobile-menu-icon" class="material-symbols-outlined text-2xl">menu</span>
        </button>
    </div>

    <!-- Mobile Dropdown Navigation (Visible below xl when toggled) -->
    <div id="public-mobile-menu" class="hidden xl:hidden bg-surface-container-lowest border-t border-outline-variant px-gutter py-md space-y-1 shadow-lg transition-all duration-200">
        <a href="<?php echo $root_prefix; ?>about.php" class="block px-md py-sm rounded-lg font-label-md text-on-surface hover:bg-surface-container-high hover:text-primary transition-colors">Tentang</a>
        <a href="<?php echo $root_prefix; ?>event_history.php" class="block px-md py-sm rounded-lg font-label-md text-on-surface hover:bg-surface-container-high hover:text-primary transition-colors">Histori Event</a>
        <a href="<?php echo $root_prefix; ?>galeryanakmagang.php" class="block px-md py-sm rounded-lg font-label-md text-on-surface hover:bg-surface-container-high hover:text-primary transition-colors">Galeri Magang</a>
        <a href="<?php echo $root_prefix; ?>article.php" class="block px-md py-sm rounded-lg font-label-md text-on-surface hover:bg-surface-container-high hover:text-primary transition-colors">Artikel</a>
        <!-- <a href="<?php echo $root_prefix; ?>index.php#features" class="block px-md py-sm rounded-lg font-label-md text-on-surface hover:bg-surface-container-high hover:text-primary transition-colors" data-i18n="nav_features">Fitur</a>
        <a href="<?php echo $root_prefix; ?>index.php#faq" class="block px-md py-sm rounded-lg font-label-md text-on-surface hover:bg-surface-container-high hover:text-primary transition-colors" data-i18n="nav_faq">FAQ</a>
        <a href="<?php echo $root_prefix; ?>index.php#contact" class="block px-md py-sm rounded-lg font-label-md text-on-surface hover:bg-surface-container-high hover:text-primary transition-colors" data-i18n="nav_contact">Kontak</a> -->

        <div class="pt-sm mt-sm border-t border-outline-variant flex flex-col gap-2 sm:hidden">
            <a href="<?php echo $root_prefix; ?>verification.php" class="w-full justify-center border border-outline-variant text-primary hover:bg-primary hover:text-white px-3 py-2 rounded-lg font-label-md text-sm transition-all flex items-center gap-1.5 font-bold">
                <span class="material-symbols-outlined text-[18px]">verified</span>
                <span>Verifikasi Sertifikat</span>
            </a>
            <a href="<?php echo htmlspecialchars($nav_cta_href); ?>" class="w-full text-center bg-primary-container text-on-primary px-3.5 py-2 rounded-lg font-label-md text-sm hover:bg-primary transition-colors font-bold" data-i18n="btn_portal_login"><?php echo htmlspecialchars($nav_cta_label); ?></a>
        </div>
    </div>
</header>

<script>
(function() {
    const btn = document.getElementById('public-mobile-menu-btn');
    const menu = document.getElementById('public-mobile-menu');
    const icon = document.getElementById('public-mobile-menu-icon');

    if (btn && menu && icon) {
        btn.addEventListener('click', function() {
            const isHidden = menu.classList.contains('hidden');
            if (isHidden) {
                menu.classList.remove('hidden');
                icon.textContent = 'close';
            } else {
                menu.classList.add('hidden');
                icon.textContent = 'menu';
            }
        });

        menu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                menu.classList.add('hidden');
                icon.textContent = 'menu';
            });
        });
    }
})();
</script>
