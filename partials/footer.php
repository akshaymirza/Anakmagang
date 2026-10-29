<?php
/**
 * partials/footer.php
 * Premium light-mode multi-column footer component for Kedayweb Platform
 */
$footer_root = '';
if (file_exists('verification.php')) {
    $footer_root = '';
} else if (file_exists('../verification.php')) {
    $footer_root = '../';
}

if (!function_exists('get_footer_settings')) {
    function get_footer_settings()
    {
        $settings_file = __DIR__ . '/../uploads/footer_settings.json';
        $defaults = [
            'location' => 'Kedayweb Software House & Digital Academy',
            'work_hours' => 'Senin - Sabtu (08:00 - 16:00 WIB)',
            'whatsapp' => '628123456789',
            'whatsapp_label' => '+62 812-3456-789',
            'instagram' => 'https://www.instagram.com/anak_magang.id?utm_source=ig_web_button_share_sheet&stkn=ZDNlZDc0MzIxNw==',
            'threads' => 'https://www.threads.net/@anak_magang.id',
            'tiktok' => 'https://www.tiktok.com/@anak_magang.id?is_from_webapp=1&sender_device=pc',
            'workspace_version' => 'Internship Workspace v2.0'
        ];
        if (file_exists($settings_file)) {
            $content = json_decode(file_get_contents($settings_file), true);
            if (is_array($content)) {
                return array_merge($defaults, $content);
            }
        }
        return $defaults;
    }
}

$footer_settings = get_footer_settings();
$wa_digits = preg_replace('/[^0-9]/', '', $footer_settings['whatsapp'] ?? '');

require_once __DIR__ . '/../legal_helper.php';
$footer_privacy_data = get_legal_data('privacy');
$footer_terms_data   = get_legal_data('terms');
$footer_is_superadmin = function_exists('is_superadmin') && is_superadmin();
?>
<!-- Premium Light Multi-Column Footer -->
<footer class="relative w-full bg-white text-slate-600 mt-auto border-t border-slate-200/80 shrink-0 z-20 font-inter">
    <div class="relative max-w-container-max mx-auto px-6 sm:px-8 lg:px-12 pt-12 pb-8">
        <!-- Top Section: 4-Column Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 pb-10 border-b border-slate-200/80">

            <!-- Col 1: Navigasi Utama -->
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-4 font-geist">Navigasi Utama
                </h4>
                <ul class="space-y-2.5 text-xs">
                    <li>
                        <a href="<?php echo $footer_root; ?>index.php"
                            class="hover:text-blue-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-2 text-slate-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-blue-600"></span>
                            Beranda
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $footer_root; ?>dashboard.php"
                            class="hover:text-blue-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-2 text-slate-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-blue-600"></span>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $footer_root; ?>tasks.php"
                            class="hover:text-blue-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-2 text-slate-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-blue-600"></span>
                            Kanban Board
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $footer_root; ?>projects.php"
                            class="hover:text-blue-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-2 text-slate-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-blue-600"></span>
                            Project Intern
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $footer_root; ?>galeryanakmagang.php"
                            class="hover:text-blue-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-2 text-slate-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-blue-600"></span>
                            Galeri Foto
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Col 2: Layanan Intern -->
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-4 font-geist">Fitur & Layanan
                </h4>
                <ul class="space-y-2.5 text-xs">
                    <li>
                        <a href="<?php echo $footer_root; ?>sop.php"
                            class="hover:text-blue-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-2 text-slate-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-blue-600"></span>
                            SOP & Rules
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $footer_root; ?>attendance.php"
                            class="hover:text-blue-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-2 text-slate-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-blue-600"></span>
                            Absensi Harian
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $footer_root; ?>event_history.php"
                            class="hover:text-blue-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-2 text-slate-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-blue-600"></span>
                            Histori Event
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $footer_root; ?>verification.php"
                            class="hover:text-blue-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-1 text-blue-600 font-semibold">
                            <span class="material-symbols-outlined text-[14px]">verified</span> Verifikasi Sertifikat
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Col 3: Media Sosial Resmi -->
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-4 font-geist">Media Sosial Resmi
                </h4>
                <ul class="space-y-2.5 text-xs">
                    <?php if (!empty($footer_settings['instagram'])): ?>
                        <li>
                            <a href="<?php echo htmlspecialchars($footer_settings['instagram'], ENT_QUOTES, 'UTF-8'); ?>"
                                target="_blank" rel="noopener noreferrer"
                                class="hover:text-pink-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-2 text-slate-600 font-medium group">
                                <svg class="w-4 h-4 text-pink-600 fill-current group-hover:scale-110 transition-transform"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                                </svg>
                                <span>Instagram</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($footer_settings['threads'])): ?>
                        <li>
                            <a href="<?php echo htmlspecialchars($footer_settings['threads'], ENT_QUOTES, 'UTF-8'); ?>"
                                target="_blank" rel="noopener noreferrer"
                                class="hover:text-slate-900 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-2 text-slate-600 font-medium group">
                                <svg class="w-4 h-4 group-hover:scale-110 transition-transform"
                                    xmlns="http://www.w3.org/2000/svg" shape-rendering="geometricPrecision"
                                    text-rendering="geometricPrecision" image-rendering="optimizeQuality"
                                    fill-rule="evenodd" clip-rule="evenodd" viewBox="0 0 512 512">
                                    <path
                                        d="M105 0h302c57.75 0 105 47.25 105 105v302c0 57.75-47.25 105-105 105H105C47.25 512 0 464.75 0 407V105C0 47.25 47.25 0 105 0z" />
                                    <path fill="#fff" fill-rule="nonzero"
                                        d="M337.36 243.58c-1.46-.7-2.95-1.38-4.46-2.02-2.62-48.36-29.04-76.05-73.41-76.33-25.6-.17-48.52 10.27-62.8 31.94l24.4 16.74c10.15-15.4 26.08-18.68 37.81-18.68h.4c14.61.09 25.64 4.34 32.77 12.62 5.19 6.04 8.67 14.37 10.39 24.89-12.96-2.2-26.96-2.88-41.94-2.02-42.18 2.43-69.3 27.03-67.48 61.21.92 17.35 9.56 32.26 24.32 42.01 12.48 8.24 28.56 12.27 45.26 11.35 22.07-1.2 39.37-9.62 51.45-25.01 9.17-11.69 14.97-26.84 17.53-45.92 10.51 6.34 18.3 14.69 22.61 24.73 7.31 17.06 7.74 45.1-15.14 67.96-20.04 20.03-44.14 28.69-80.55 28.96-40.4-.3-70.95-13.26-90.81-38.51-18.6-23.64-28.21-57.79-28.57-101.5.36-43.71 9.97-77.86 28.57-101.5 19.86-25.25 50.41-38.21 90.81-38.51 40.68.3 71.76 13.32 92.39 38.69 10.11 12.44 17.73 28.09 22.76 46.33l28.59-7.63c-6.09-22.45-15.67-41.8-28.72-57.85-26.44-32.53-65.1-49.19-114.92-49.54h-.2c-49.72.35-87.96 17.08-113.64 49.73-22.86 29.05-34.65 69.48-35.04 120.16v.24c.39 50.68 12.18 91.11 35.04 120.16 25.68 32.65 63.92 49.39 113.64 49.73h.2c44.2-.31 75.36-11.88 101.03-37.53 33.58-33.55 32.57-75.6 21.5-101.42-7.94-18.51-23.08-33.55-43.79-43.48zm-76.32 71.76c-18.48 1.04-37.69-7.26-38.64-25.03-.7-13.18 9.38-27.89 39.78-29.64 3.48-.2 6.9-.3 10.25-.3 11.04 0 21.37 1.07 30.76 3.13-3.5 43.74-24.04 50.84-42.15 51.84z" />
                                </svg>
                                <span>Threads</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($footer_settings['tiktok'])): ?>
                        <li>
                            <a href="<?php echo htmlspecialchars($footer_settings['tiktok'], ENT_QUOTES, 'UTF-8'); ?>"
                                target="_blank" rel="noopener noreferrer"
                                class="hover:text-slate-900 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-2 text-slate-600 font-medium group">
                                <svg class="w-4 h-4 text-slate-900 fill-current group-hover:scale-110 transition-transform"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.29 0 .56.05.82.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 3 15.68 6.33 6.33 0 0 0 9.33 22a6.33 6.33 0 0 0 6.33-6.33V9.5a8.16 8.16 0 0 0 4.93 1.62V7.67a4.85 4.85 0 0 1-1-.98z" />
                                </svg>
                                <span>TikTok</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($wa_digits)): ?>
                        <li>
                            <a href="https://wa.me/<?php echo $wa_digits; ?>" target="_blank" rel="noopener noreferrer"
                                class="hover:text-emerald-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-2 text-slate-600 font-medium group">
                                <svg class="w-4 h-4 text-emerald-600 fill-current group-hover:scale-110 transition-transform"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
                                </svg>
                                <span>WhatsApp</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Col 4: Informasi Operasional & Kontak -->
            <div>
                <h4
                    class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-4 font-geist flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-blue-600 text-base">business</span>
                    <span>Informasi Hub</span>
                </h4>
                <div class="space-y-2.5 text-xs text-slate-600">
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-sm text-slate-400 mt-0.5">location_on</span>
                        <span><?php echo htmlspecialchars($footer_settings['location'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm text-slate-400">schedule</span>
                        <span><?php echo htmlspecialchars($footer_settings['work_hours'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>

                </div>
                <div
                    class="pt-3 mt-4 border-t border-slate-200/80 flex items-center justify-between text-[11px] text-slate-500">
                    <span><?php echo htmlspecialchars($footer_settings['workspace_version'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500" title="System Active"></span>
                </div>
            </div>

        </div>

        <!-- Bottom Bar -->
        <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
            <div class="text-slate-500 text-center sm:text-left text-[11px]">
                © <?php echo date('Y'); ?> Kedayweb Platform. Hak cipta dilindungi.
            </div>

            <div class="flex items-center gap-4 text-slate-600 font-medium">
                <button type="button" onclick="openPrivacyModal()"
                    class="hover:text-blue-600 transition-colors cursor-pointer">Kebijakan Privasi</button>
                <span class="text-slate-300">•</span>
                <button type="button" onclick="openTermsModal()"
                    class="hover:text-blue-600 transition-colors cursor-pointer">Syarat & Ketentuan</button>
                <span class="text-slate-300">•</span>
                <button type="button" onclick="footerScrollToTop(this)"
                    class="p-1.5 rounded-lg bg-slate-100 border border-slate-200 text-slate-500 hover:text-slate-800 hover:bg-slate-200 transition-all flex items-center justify-center cursor-pointer"
                    title="Kembali ke atas">
                    <span class="material-symbols-outlined text-sm">arrow_upward</span>
                </button>
            </div>
        </div>
    </div>
</footer>

<!-- Modals for Privacy & Terms -->
<div id="privacyModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white border border-slate-200 text-slate-700 rounded-2xl max-w-lg w-full p-6 shadow-xl relative max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900 font-geist flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600">security</span> <?php echo htmlspecialchars($footer_privacy_data['title'] ?? 'Kebijakan Privasi', ENT_QUOTES, 'UTF-8'); ?>
            </h3>
            <button onclick="closePrivacyModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        <div class="py-4 space-y-3 text-xs text-slate-600 leading-relaxed overflow-y-auto pr-1">
            <?php if (!empty($footer_privacy_data['intro'])): ?>
                <p class="font-semibold text-slate-800 bg-slate-50 p-2.5 rounded-xl border border-slate-100"><?php echo htmlspecialchars($footer_privacy_data['intro'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <?php if (!empty($footer_privacy_data['sections'])): ?>
                <?php foreach ($footer_privacy_data['sections'] as $p_sec): ?>
                    <div class="p-3 bg-slate-50/70 rounded-xl border border-slate-100 space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600 text-sm"><?php echo htmlspecialchars($p_sec['icon'] ?? 'security', ENT_QUOTES, 'UTF-8'); ?></span>
                            <h4 class="font-bold text-slate-800 text-xs"><?php echo htmlspecialchars($p_sec['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h4>
                        </div>
                        <p class="text-slate-600 pl-6 leading-relaxed whitespace-pre-line"><?php echo nl2br(htmlspecialchars($p_sec['content'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-slate-400 italic text-center py-4">Belum ada ketentuan kebijakan privasi.</p>
            <?php endif; ?>
        </div>
        <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
            <?php if ($footer_is_superadmin): ?>
                <a href="<?php echo $footer_root; ?>admin/superadmin.php#legal" class="text-[11px] text-blue-600 hover:text-blue-800 font-bold hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">tune</span>
                    <span>Kelola di Super Admin</span>
                </a>
            <?php else: ?>
                <a href="<?php echo $footer_root; ?>privacy-policy.php" class="text-[11px] text-slate-500 hover:text-blue-600 font-medium hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">open_in_new</span>
                    <span>Halaman Penuh</span>
                </a>
            <?php endif; ?>
            <button onclick="closePrivacyModal()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs transition-colors">Tutup</button>
        </div>
    </div>
</div>

<div id="termsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white border border-slate-200 text-slate-700 rounded-2xl max-w-lg w-full p-6 shadow-xl relative max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900 font-geist flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600">gavel</span> <?php echo htmlspecialchars($footer_terms_data['title'] ?? 'Syarat & Ketentuan', ENT_QUOTES, 'UTF-8'); ?>
            </h3>
            <button onclick="closeTermsModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        <div class="py-4 space-y-3 text-xs text-slate-600 leading-relaxed overflow-y-auto pr-1">
            <?php if (!empty($footer_terms_data['intro'])): ?>
                <p class="font-semibold text-slate-800 bg-slate-50 p-2.5 rounded-xl border border-slate-100"><?php echo htmlspecialchars($footer_terms_data['intro'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <?php if (!empty($footer_terms_data['sections'])): ?>
                <?php foreach ($footer_terms_data['sections'] as $t_sec): ?>
                    <div class="p-3 bg-slate-50/70 rounded-xl border border-slate-100 space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600 text-sm"><?php echo htmlspecialchars($t_sec['icon'] ?? 'gavel', ENT_QUOTES, 'UTF-8'); ?></span>
                            <h4 class="font-bold text-slate-800 text-xs"><?php echo htmlspecialchars($t_sec['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h4>
                        </div>
                        <p class="text-slate-600 pl-6 leading-relaxed whitespace-pre-line"><?php echo nl2br(htmlspecialchars($t_sec['content'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-slate-400 italic text-center py-4">Belum ada syarat dan ketentuan.</p>
            <?php endif; ?>
        </div>
        <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
            <?php if ($footer_is_superadmin): ?>
                <a href="<?php echo $footer_root; ?>admin/superadmin.php#legal" class="text-[11px] text-blue-600 hover:text-blue-800 font-bold hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">tune</span>
                    <span>Kelola di Super Admin</span>
                </a>
            <?php else: ?>
                <a href="<?php echo $footer_root; ?>terms-conditions.php" class="text-[11px] text-slate-500 hover:text-blue-600 font-medium hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">open_in_new</span>
                    <span>Halaman Penuh</span>
                </a>
            <?php endif; ?>
            <button onclick="closeTermsModal()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs transition-colors">Tutup</button>
        </div>
    </div>
</div>

<script>
    if (typeof openPrivacyModal !== 'function') {
        function openPrivacyModal() {
            var m = document.getElementById('privacyModal');
            if (m) { m.classList.remove('hidden'); m.classList.add('flex'); }
        }
        function closePrivacyModal() {
            var m = document.getElementById('privacyModal');
            if (m) { m.classList.add('hidden'); m.classList.remove('flex'); }
        }
        function openTermsModal() {
            var m = document.getElementById('termsModal');
            if (m) { m.classList.remove('hidden'); m.classList.add('flex'); }
        }
        function closeTermsModal() {
            var m = document.getElementById('termsModal');
            if (m) { m.classList.add('hidden'); m.classList.remove('flex'); }
        }
    }

    if (typeof footerScrollToTop !== 'function') {
        function footerScrollToTop(btn) {
            var parent = btn ? btn.parentElement : null;
            while (parent && parent !== document.body && parent !== document.documentElement) {
                var style = window.getComputedStyle(parent);
                if ((style.overflowY === 'auto' || style.overflowY === 'scroll') && parent.scrollHeight > parent.clientHeight) {
                    parent.scrollTo({ top: 0, behavior: 'smooth' });
                    break;
                }
                parent = parent.parentElement;
            }
            var mainEl = document.querySelector('main');
            if (mainEl && mainEl.scrollTop > 0) {
                mainEl.scrollTo({ top: 0, behavior: 'smooth' });
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
            if (document.documentElement && document.documentElement.scrollTop > 0) {
                document.documentElement.scrollTo({ top: 0, behavior: 'smooth' });
            }
            if (document.body && document.body.scrollTop > 0) {
                document.body.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }
    }
</script>