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
                            class="hover:text-blue-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-1 text-slate-600">
                            <span class="material-symbols-outlined text-[14px] text-slate-400">chevron_right</span>
                            Beranda
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $footer_root; ?>dashboard.php"
                            class="hover:text-blue-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-1 text-slate-600">
                            <span class="material-symbols-outlined text-[14px] text-slate-400">chevron_right</span>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $footer_root; ?>tasks.php"
                            class="hover:text-blue-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-1 text-slate-600">
                            <span class="material-symbols-outlined text-[14px] text-slate-400">chevron_right</span>
                            Kanban Board
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $footer_root; ?>projects.php"
                            class="hover:text-blue-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-1 text-slate-600">
                            <span class="material-symbols-outlined text-[14px] text-slate-400">chevron_right</span>
                            Project Intern
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $footer_root; ?>galeryanakmagang.php"
                            class="hover:text-blue-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-1 text-slate-600">
                            <span class="material-symbols-outlined text-[14px] text-slate-400">chevron_right</span>
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
                            class="hover:text-blue-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-1 text-slate-600">
                            <span class="material-symbols-outlined text-[14px] text-slate-400">chevron_right</span> SOP
                            & Rules
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $footer_root; ?>attendance.php"
                            class="hover:text-blue-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-1 text-slate-600">
                            <span class="material-symbols-outlined text-[14px] text-slate-400">chevron_right</span>
                            Absensi Harian
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $footer_root; ?>event_history.php"
                            class="hover:text-blue-600 hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-1 text-slate-600">
                            <span class="material-symbols-outlined text-[14px] text-slate-400">chevron_right</span>
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
                                <svg class="w-4 h-4 text-slate-900 fill-current group-hover:scale-110 transition-transform"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M12.186 24c-3.15 0-5.658-.87-7.46-2.587C2.923 19.7 2 17.14 2 13.78 2 6.786 6.84 0 14.156 0c3.784 0 6.88 1.408 8.953 3.966 1.83 2.258 2.502 5.166 1.944 8.423a10.978 10.978 0 0 1-3.69 6.643 11.236 11.236 0 0 1-7.142 2.766c-.347.018-.68.027-1.002.027-4.148 0-6.905-2.073-6.905-5.221 0-2.88 2.376-4.992 5.79-5.143l3.376-.149c.677-.03 1.252-.162 1.71-.393.39-.196.657-.492.775-.858.156-.484.094-1.047-.175-1.584-.523-1.045-1.782-1.637-3.238-1.522-1.928.153-3.266 1.401-3.328 3.106h-3.31c.074-3.376 2.723-5.918 6.55-6.223 3.18-.253 5.922.956 7.158 3.155.674 1.2.853 2.562.518 3.94-.367 1.51-1.34 2.87-2.742 3.829-1.396.955-3.146 1.442-4.928 1.371-.247-.01-.482-.016-.704-.016-2.127 0-3.568.966-3.568 2.39 0 1.26 1.205 2.115 3.037 2.115.185 0 .377-.006.574-.017 2.65-.157 4.708-1.077 5.952-2.66 1.155-1.47 1.634-3.36 1.35-5.32-.303-2.083-1.448-3.83-3.226-4.92-1.748-1.07-3.953-1.446-6.208-1.059-3.79.65-6.527 3.493-6.643 6.903v.284c0 2.502.663 4.398 1.97 5.633 1.3 1.229 3.153 1.854 5.508 1.854zm.76-11.442l-2.05.09c-1.666.074-2.734.962-2.734 2.277 0 1.18.91 1.95 2.27 1.95.11 0 .223-.004.336-.01 1.623-.096 2.906-.827 3.514-1.996.34-.654.437-1.354.274-1.972-.116-.44-.395-.705-.86-.777-.24-.038-.495-.038-.75-.038z" />
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
                                <svg class="w-4 h-4 text-emerald-600 fill-current group-hover:scale-110 transition-transform" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
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
                <button type="button" onclick="window.scrollTo({top: 0, behavior: 'smooth'})"
                    class="p-1.5 rounded-lg bg-slate-100 border border-slate-200 text-slate-500 hover:text-slate-800 hover:bg-slate-200 transition-all flex items-center justify-center"
                    title="Kembali ke atas">
                    <span class="material-symbols-outlined text-sm">arrow_upward</span>
                </button>
            </div>
        </div>
    </div>
</footer>

<!-- Modals for Privacy & Terms -->
<div id="privacyModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div
        class="bg-white border border-slate-200 text-slate-700 rounded-2xl max-w-lg w-full p-6 shadow-xl relative max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900 font-geist flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600">security</span> Kebijakan Privasi
            </h3>
            <button onclick="closePrivacyModal()"
                class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        <div class="py-4 space-y-3 text-xs text-slate-600 leading-relaxed overflow-y-auto pr-1">
            <p class="font-semibold text-slate-800">Privasi Anda sangat penting bagi Kedayweb Platform.</p>
            <p>1. <strong>Pengumpulkan Data:</strong> Data yang dikumpulkan pada platform ini hanya digunakan untuk
                kepentingan administrasi program magang, verifikasi sertifikat, dan presensi harian.</p>
            <p>2. <strong>Keamanan Data:</strong> Kedayweb berkomitmen menjaga kerahasiaan informasi personal peserta
                intern dan tidak membagikan data kepada pihak ketiga tanpa izin.</p>
            <p>3. <strong>Sertifikasi Digital:</strong> Data sertifikat yang diterbitkan bersifat publik untuk
                memfasilitasi verifikasi oleh pihak perekrut atau kampus mitra.</p>
        </div>
        <div class="pt-3 border-t border-slate-100 text-right">
            <button onclick="closePrivacyModal()"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs transition-colors">Tutup</button>
        </div>
    </div>
</div>

<div id="termsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div
        class="bg-white border border-slate-200 text-slate-700 rounded-2xl max-w-lg w-full p-6 shadow-xl relative max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900 font-geist flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600">gavel</span> Syarat & Ketentuan
            </h3>
            <button onclick="closeTermsModal()"
                class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        <div class="py-4 space-y-3 text-xs text-slate-600 leading-relaxed overflow-y-auto pr-1">
            <p class="font-semibold text-slate-800">Ketentuan Penggunaan Kedayweb Platform:</p>
            <p>1. <strong>Kode Etik Intern:</strong> Seluruh peserta magang wajib mematuhi SOP kerja, menjaga etika
                profesionalisme, dan memenuhi jam kerja yang ditentukan.</p>
            <p>2. <strong>Hak Cipta Hasil Kerja:</strong> Seluruh hasil karya, kode program, dan aset proyek yang dibuat
                selama masa magang merupakan hak milik Kedayweb Platform.</p>
            <p>3. <strong>Kerahasiaan:</strong> Peserta magang dilarang menyebarkan informasi sensitif atau kode sumber
                privat milik Kedayweb kepada pihak luar.</p>
        </div>
        <div class="pt-3 border-t border-slate-100 text-right">
            <button onclick="closeTermsModal()"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs transition-colors">Tutup</button>
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
</script>