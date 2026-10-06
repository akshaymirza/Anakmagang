<?php
require_once __DIR__ . '/session.php';
$positions_file = __DIR__ . '/uploads/positions.json';
$positions = [];
if (file_exists($positions_file)) {
    $positions = json_decode(file_get_contents($positions_file), true);
}
if (!is_array($positions) || empty($positions)) {
    $positions = [
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
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Kedayweb - Pendaftaran Anak Magang</title>
    <!-- Material Symbols -->
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800&amp;family=Inter:wght@400;500;600&amp;display=swap"
        rel="stylesheet" />
    <!-- Anime.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="shared-config.js"></script>
    <style>
        html { scroll-behavior: smooth; }

        /* ── Parallax hero ── */
        #hero-parallax-img {
            will-change: transform;
            transform: translateY(0px) scale(1.08);
        }

        /* ── Card hover lift (no !important — Anime.js manages transform) ── */
        .benefit-card-left, .benefit-card-top, .benefit-card-bottom,
        .benefit-card-right, .position-card {
            cursor: pointer;
            transition: box-shadow 0.35s cubic-bezier(0.16, 1, 0.3, 1),
                        border-color 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .benefit-card-left:hover, .benefit-card-top:hover,
        .benefit-card-bottom:hover, .benefit-card-right:hover,
        .position-card:hover {
            box-shadow: 0 24px 48px -8px rgba(30, 58, 138, 0.18),
                        0 8px 16px -4px rgba(30, 58, 138, 0.08);
            border-color: rgba(30, 58, 138, 0.45);
        }

        /* ── Cinematic reveal: all animated elements start invisible ── */
        .anim-target {
            opacity: 0;
            will-change: opacity, transform, filter;
        }

        /* ── Section heading reveal line ── */
        .section-reveal-line {
            display: block;
            overflow: hidden;
        }
        .section-reveal-line > span {
            display: block;
            transform: translateY(110%);
            opacity: 0;
        }

        /* ── Shimmer on hover for icon boxes ── */
        .icon-box {
            position: relative;
            overflow: hidden;
        }
        .icon-box::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,0.25) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: none;
        }
        .group:hover .icon-box::after {
            transform: translateX(100%);
            transition: transform 0.55s cubic-bezier(0.4,0,0.2,1);
        }

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
            /* PNG with transparent */
            background-image: url('uploads/Icon/batik-gajah-oling.png');
            background-size: contain;
            background-repeat: no-repeat;
        }

        /* ── Responsive Mobile Adjustment for Floating Batik ── */
        @media (max-width: 768px) {
            .batik-float {
                max-width: 110px !important;
                max-height: 110px !important;
                opacity: 0.04 !important;
            }
            .batik-desktop-only {
                display: none !important;
            }
        }
    </style>
</head>

<body class="bg-background text-on-surface font-body-md min-h-screen flex flex-col antialiased">

    <!-- ═══ Floating Batik Gajah Oling Background Layer ═══
         Export Motif dari Figma ke uploads/icon/batik-gajah-oling.png
    ═════════════════════════════════════════════════════ -->
    <div id="batik-layer" aria-hidden="true">
        <div class="batik-float" style="width:260px;height:260px;top:4%;left:-4%;opacity:0.07;transform:rotate(-15deg)"></div>
        <div class="batik-float" style="width:180px;height:180px;top:12%;right:-2%;opacity:0.05;transform:rotate(22deg)"></div>
        <div class="batik-float batik-desktop-only" style="width:320px;height:320px;top:38%;left:55%;opacity:0.06;transform:rotate(8deg)"></div>
        <div class="batik-float" style="width:200px;height:200px;top:52%;left:-3%;opacity:0.07;transform:rotate(-30deg)"></div>
        <div class="batik-float" style="width:240px;height:240px;top:70%;right:-4%;opacity:0.05;transform:rotate(40deg)"></div>
        <div class="batik-float" style="width:150px;height:150px;top:85%;left:25%;opacity:0.06;transform:rotate(-5deg)"></div>
        <div class="batik-float batik-desktop-only" style="width:280px;height:280px;top:25%;left:20%;opacity:0.04;transform:rotate(55deg)"></div>
        <div class="batik-float batik-desktop-only" style="width:170px;height:170px;top:60%;right:25%;opacity:0.05;transform:rotate(-18deg)"></div>
    </div>

    <?php include 'partials/topnav-public.php'; ?>

    <main class="flex-grow">
        <!-- Hero Section -->
        <section class="w-full px-gutter py-3xl max-w-container-max mx-auto flex flex-col items-center text-center relative">

            <span id="hero-badge"
                class="inline-block px-md py-xs rounded-full bg-primary-fixed text-on-primary-fixed-variant font-label-sm text-label-sm mb-lg border border-primary-fixed-dim font-semibold opacity-0"
                data-i18n="recruit_careers">Pendaftaran</span>
            <h1 id="hero-title" class="font-headline-xl text-headline-xl text-on-surface mb-md max-w-3xl font-extrabold opacity-0" data-i18n="recruit_title">
                Pendaftaran Anak Magang</h1>
            <p id="hero-subtitle" class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mb-xl opacity-0" data-i18n="recruit_subtitle">
                Mulai perjalanan karir Anda dengan pengalaman nyata, bimbingan mentor industri, dan proyek langsung di
                Kedayweb.
            </p>
            <div id="hero-img-box"
                class="w-full h-64 md:h-[400px] rounded-[24px] overflow-hidden border border-outline-variant shadow-md relative group mt-lg opacity-0">
                <img id="hero-parallax-img" class="w-full h-full object-cover"
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuAf2c_JxDXo1KbLpFVJDn67XVBHdj1mnsNi7Qu-7UAr2hB9SmuW7CS49bF7djDMStwPzGjvpclkK49Gm3gICdZjnkqfw8eMc1C9LlUFSkErmQKK3ET0KGrdDyMNWDu08Q2u1EtP56g_-1VLvNfqAP18yuc1d9zVmLfS0atyrhngTBfqKaeYzexH-xYF_I88LmVkG9rqfcd2ezl-1Rc9TgUKQMA3dRkKV7M9c7e_8_88cJC15aMskLXb"
                    alt="Modern collaborative workspace" />
                <div class="absolute inset-0 bg-gradient-to-t from-inverse-surface/40 to-transparent"></div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="w-full px-gutter py-2xl max-w-container-max mx-auto border-b border-outline-variant/50">
            <div class="text-center max-w-2xl mx-auto mb-xl">
                <span class="inline-block px-md py-xs rounded-full bg-surface-container-high text-primary font-label-sm text-label-sm mb-sm border border-outline-variant font-semibold">
                    Keunggulan Program
                </span>
                <h2 class="font-headline-lg text-headline-lg text-on-surface mb-xs font-bold">
                    Manfaat Mengikuti Program Magang
                </h2>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Dapatkan pengalaman berharga yang siap mengakselerasi karir dan kemampuan teknis Anda di dunia industri.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-lg" id="benefits-grid">
                <!-- Benefit 1 — Left column, row 1 —  slide from left -->
                <div id="benefit-card-1" class="benefit-card-left anim-target group glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg flex flex-col items-start">
                    <div class="icon-box w-12 h-12 rounded-xl bg-primary-container text-on-primary flex items-center justify-center mb-md group-hover:scale-110 transition-transform duration-300 shadow-sm">
                        <span class="material-symbols-outlined text-[26px]">work_history</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-xs font-bold">Pengalaman Proyek Nyata</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                        Terlibat langsung dalam pengerjaan proyek skala industri yang digunakan oleh klien dan pengguna nyata.
                    </p>
                </div>

                <!-- Benefit 2 — Middle column, row 1 — slide from top -->
                <div id="benefit-card-2" class="benefit-card-top anim-target group glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg flex flex-col items-start">
                    <div class="icon-box w-12 h-12 rounded-xl bg-primary-container text-on-primary flex items-center justify-center mb-md group-hover:scale-110 transition-transform duration-300 shadow-sm">
                        <span class="material-symbols-outlined text-[26px]">supervisor_account</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-xs font-bold">Mentorship Intensif</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                        Bimbingan langsung dari mentor berpengalaman yang siap mengarahkan dan memandu perkembangan Anda.
                    </p>
                </div>

                <!-- Benefit 3 — Right column, row 1 — slide from right -->
                <div id="benefit-card-3" class="benefit-card-right anim-target group glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg flex flex-col items-start">
                    <div class="icon-box w-12 h-12 rounded-xl bg-primary-container text-on-primary flex items-center justify-center mb-md group-hover:scale-110 transition-transform duration-300 shadow-sm">
                        <span class="material-symbols-outlined text-[26px]">workspace_premium</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-xs font-bold">Sertifikat & Portofolio</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                        Mendapatkan sertifikat magang resmi serta hasil karya nyata yang siap dipamerkan dalam portofolio profesional Anda.
                    </p>
                </div>

                <!-- Benefit 4 — Left column, row 2 — slide from left -->
                <div id="benefit-card-4" class="benefit-card-left anim-target group glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg flex flex-col items-start">
                    <div class="icon-box w-12 h-12 rounded-xl bg-primary-container text-on-primary flex items-center justify-center mb-md group-hover:scale-110 transition-transform duration-300 shadow-sm">
                        <span class="material-symbols-outlined text-[26px]">hub</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-xs font-bold">Jaringan & Networking</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                        Perluas jaringan kerja bersama para profesional industri, sesama talenta muda, dan ekosistem digital Kedayweb.
                    </p>
                </div>

                <!-- Benefit 5 — Middle column, row 2 — slide from bottom -->
                <div id="benefit-card-5" class="benefit-card-bottom anim-target group glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg flex flex-col items-start">
                    <div class="icon-box w-12 h-12 rounded-xl bg-primary-container text-on-primary flex items-center justify-center mb-md group-hover:scale-110 transition-transform duration-300 shadow-sm">
                        <span class="material-symbols-outlined text-[26px]">rocket_launch</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-xs font-bold">Peluang Karir Lanjutan</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                        Peserta magang dengan performa dan kontribusi terbaik berkesempatan direkrut langsung menjadi tim tetap.
                    </p>
                </div>

                <!-- Benefit 6 — Right column, row 2 — slide from right -->
                <div id="benefit-card-6" class="benefit-card-right anim-target group glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg flex flex-col items-start">
                    <div class="icon-box w-12 h-12 rounded-xl bg-primary-container text-on-primary flex items-center justify-center mb-md group-hover:scale-110 transition-transform duration-300 shadow-sm">
                        <span class="material-symbols-outlined text-[26px]">schedule</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-xs font-bold">Sistem Kerja Modern</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                        Didukung sistem manajemen kerja digital, absensi geotagging, serta jam kerja fleksibel yang terstruktur.
                    </p>
                </div>
            </div>
        </section>

        <!-- Available Positions Section -->
        <section class="w-full px-gutter py-2xl max-w-container-max mx-auto" id="positions">
            <div class="flex flex-col md:flex-row justify-between items-end mb-xl">
                <div>
                    <h2 class="font-headline-lg text-headline-lg text-on-surface mb-xs font-bold"
                        data-i18n="recruit_positions">Pilihan Posisi Magang</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant" data-i18n="recruit_pos_d">Pilih bidang
                        yang sesuai dengan minat dan keahlian Anda.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-lg" id="positions-grid">
                <?php foreach ($positions as $pos): ?>
                    <div class="position-card anim-target group glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg flex flex-col h-full">
                        <div class="flex justify-between items-start mb-md">
                            <div class="icon-box w-12 h-12 rounded-xl bg-surface-container-high text-primary flex items-center justify-center group-hover:bg-primary-container group-hover:text-on-primary group-hover:scale-110 transition-all duration-300 shadow-xs">
                                <span class="material-symbols-outlined text-[26px]"><?php echo htmlspecialchars($pos['icon'] ?? 'work', ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-surface-container text-on-surface-variant font-label-sm text-label-sm"><?php echo htmlspecialchars($pos['category'] ?? 'Umum', ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <h3 class="font-headline-md text-headline-md text-on-surface mb-xs font-bold"><?php echo htmlspecialchars($pos['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="font-body-sm text-body-sm text-on-surface-variant flex-grow mb-xl">
                            <?php echo htmlspecialchars($pos['description'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <div class="flex items-center gap-md border-t border-outline-variant pt-md mt-auto">
                            <a href="#application-form" class="bg-primary-container text-on-primary rounded-lg px-md py-sm font-label-md text-label-md hover:bg-primary transition-colors flex items-center gap-2">
                                <span data-i18n="btn_apply_now">Daftar Sekarang</span>
                                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                            </a>
                            <div class="flex items-center text-on-surface-variant font-body-sm text-body-sm gap-1">
                                <span class="material-symbols-outlined text-[16px]">location_on</span>
                                <span><?php echo htmlspecialchars($pos['location'] ?? 'Remote / Hybrid', ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Timeline Section: Alur Magang -->
        <section class="w-full px-gutter py-2xl max-w-container-max mx-auto border-t border-outline-variant/50" id="timeline">
            <div class="text-center max-w-2xl mx-auto mb-2xl">
                <span class="inline-block px-md py-xs rounded-full bg-surface-container-high text-primary font-label-sm text-label-sm mb-sm border border-outline-variant font-semibold">
                    Tahapan Program
                </span>
                <h2 class="font-headline-lg text-headline-lg text-on-surface mb-xs font-bold">
                    Alur Program Magang
                </h2>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Proses transparan dan terstruktur dari pendaftaran hingga penyelesaian program magang.
                </p>
            </div>

            <div class="relative max-w-4xl mx-auto px-4" id="timeline-container">
                <!-- Vertical line: Left-aligned on mobile, center-aligned on desktop -->
                <div class="absolute left-6 md:left-1/2 top-4 bottom-4 w-0.5 bg-gradient-to-b from-primary via-primary/40 to-transparent -translate-x-1/2"></div>

                <div class="space-y-6 md:space-y-12">
                    <!-- Step 1 -->
                    <div class="timeline-step anim-target flex flex-row items-start md:items-center gap-4 md:gap-6">
                        <div class="w-12 h-12 rounded-full bg-primary text-on-primary flex items-center justify-center font-bold text-lg shrink-0 shadow-md z-10 ring-4 ring-background md:hidden">
                            <span class="material-symbols-outlined text-[24px]">app_registration</span>
                        </div>
                        <div class="flex-1 md:w-1/2 md:text-right pr-0 md:pr-8 bg-surface-container-lowest md:bg-transparent p-4 md:p-0 rounded-2xl border border-outline-variant md:border-none shadow-xs md:shadow-none">
                            <span class="inline-block px-3 py-1 rounded-full bg-primary/10 text-primary font-bold text-xs mb-1">Langkah 01</span>
                            <h3 class="font-headline-sm font-bold text-on-surface text-lg">Pendaftaran Online</h3>
                            <p class="font-body-sm text-on-surface-variant mt-1 text-sm">Isi formulir data diri dan unggah berkas CV serta portofolio terbaru melalui halaman ini.</p>
                        </div>
                        <div class="hidden md:flex w-12 h-12 rounded-full bg-primary text-on-primary items-center justify-center font-bold text-lg shrink-0 shadow-md z-10 ring-4 ring-background">
                            <span class="material-symbols-outlined text-[24px]">app_registration</span>
                        </div>
                        <div class="hidden md:block md:w-1/2 pl-8"></div>
                    </div>

                    <!-- Step 2 -->
                    <div class="timeline-step anim-target flex flex-row items-start md:items-center gap-4 md:gap-6">
                        <div class="w-12 h-12 rounded-full bg-primary text-on-primary flex items-center justify-center font-bold text-lg shrink-0 shadow-md z-10 ring-4 ring-background md:hidden">
                            <span class="material-symbols-outlined text-[24px]">quick_reference_all</span>
                        </div>
                        <div class="hidden md:block md:w-1/2 pr-8"></div>
                        <div class="hidden md:flex w-12 h-12 rounded-full bg-primary text-on-primary items-center justify-center font-bold text-lg shrink-0 shadow-md z-10 ring-4 ring-background">
                            <span class="material-symbols-outlined text-[24px]">quick_reference_all</span>
                        </div>
                        <div class="flex-1 md:w-1/2 pl-0 md:pl-8 bg-surface-container-lowest md:bg-transparent p-4 md:p-0 rounded-2xl border border-outline-variant md:border-none shadow-xs md:shadow-none">
                            <span class="inline-block px-3 py-1 rounded-full bg-primary/10 text-primary font-bold text-xs mb-1">Langkah 02</span>
                            <h3 class="font-headline-sm font-bold text-on-surface text-lg">Seleksi Berkas & Wawancara</h3>
                            <p class="font-body-sm text-on-surface-variant mt-1 text-sm">Tim Kedayweb meninjau aplikasi Anda dan mengundang ke sesi diskusi & wawancara online.</p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="timeline-step anim-target flex flex-row items-start md:items-center gap-4 md:gap-6">
                        <div class="w-12 h-12 rounded-full bg-primary text-on-primary flex items-center justify-center font-bold text-lg shrink-0 shadow-md z-10 ring-4 ring-background md:hidden">
                            <span class="material-symbols-outlined text-[24px]">rocket_launch</span>
                        </div>
                        <div class="flex-1 md:w-1/2 md:text-right pr-0 md:pr-8 bg-surface-container-lowest md:bg-transparent p-4 md:p-0 rounded-2xl border border-outline-variant md:border-none shadow-xs md:shadow-none">
                            <span class="inline-block px-3 py-1 rounded-full bg-primary/10 text-primary font-bold text-xs mb-1">Langkah 03</span>
                            <h3 class="font-headline-sm font-bold text-on-surface text-lg">Onboarding & Mentorship</h3>
                            <p class="font-body-sm text-on-surface-variant mt-1 text-sm">Pengenalan tim, alur kerja proyek, serta penetapan mentor profesional pendamping.</p>
                        </div>
                        <div class="hidden md:flex w-12 h-12 rounded-full bg-primary text-on-primary items-center justify-center font-bold text-lg shrink-0 shadow-md z-10 ring-4 ring-background">
                            <span class="material-symbols-outlined text-[24px]">rocket_launch</span>
                        </div>
                        <div class="hidden md:block md:w-1/2 pl-8"></div>
                    </div>

                    <!-- Step 4 -->
                    <div class="timeline-step anim-target flex flex-row items-start md:items-center gap-4 md:gap-6">
                        <div class="w-12 h-12 rounded-full bg-primary text-on-primary flex items-center justify-center font-bold text-lg shrink-0 shadow-md z-10 ring-4 ring-background md:hidden">
                            <span class="material-symbols-outlined text-[24px]">workspace_premium</span>
                        </div>
                        <div class="hidden md:block md:w-1/2 pr-8"></div>
                        <div class="hidden md:flex w-12 h-12 rounded-full bg-primary text-on-primary items-center justify-center font-bold text-lg shrink-0 shadow-md z-10 ring-4 ring-background">
                            <span class="material-symbols-outlined text-[24px]">workspace_premium</span>
                        </div>
                        <div class="flex-1 md:w-1/2 pl-0 md:pl-8 bg-surface-container-lowest md:bg-transparent p-4 md:p-0 rounded-2xl border border-outline-variant md:border-none shadow-xs md:shadow-none">
                            <span class="inline-block px-3 py-1 rounded-full bg-primary/10 text-primary font-bold text-xs mb-1">Langkah 04</span>
                            <h3 class="font-headline-sm font-bold text-on-surface text-lg">Sertifikat & Karir</h3>
                            <p class="font-body-sm text-on-surface-variant mt-1 text-sm">Penerbitan sertifikat resmi magang dan kesempatan direkrut menjadi tim profesional.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Partners Section -->
        <section class="w-full px-gutter py-2xl max-w-container-max mx-auto border-t border-outline-variant/50" id="partners">
            <div class="text-center max-w-2xl mx-auto mb-xl">
                <span class="inline-block px-md py-xs rounded-full bg-surface-container-high text-primary font-label-sm text-label-sm mb-sm border border-outline-variant font-semibold">
                    Kemitraan
                </span>
                <h2 class="font-headline-lg text-headline-lg text-on-surface mb-xs font-bold">
                    Partner Kami
                </h2>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Sekolah dan Perguruan Tinggi mitra yang telah menjalin kerja sama dengan program magang Kedayweb.
                </p>
            </div>
            
            <div class="py-4">
                <div class="flex flex-wrap items-center justify-center gap-8 md:gap-14 lg:gap-16">
                    <?php 
                    $partnerDir = __DIR__ . '/uploads/PartnerIcon';
                    $partnerLogos = [];
                    $partnerWebsiteMap = [
                        'Polinema' => 'https://www.polinema.ac.id/',
                        'Poliwangi' => 'https://poliwangi.ac.id/',
                        'SMKN 1 Banyuwangi' => 'https://smkn1banyuwangi.sch.id/',
                        'SMKN 1 Tegalsari' => 'https://smkn1tegalsari.sch.id/',
                        'SMK_Rogojampi' => 'https://www.smkpro.id/',
                    ];
                    if (is_dir($partnerDir)) {
                        $files = scandir($partnerDir);
                        foreach ($files as $file) {
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            if (in_array($ext, ['png', 'jpg', 'jpeg', 'svg', 'webp'])) {
                                $partnerLogos[] = $file;
                            }
                        }
                    }
                    if (!empty($partnerLogos)):
                        foreach ($partnerLogos as $logo):
                            $partnerName = pathinfo($logo, PATHINFO_FILENAME);
                            $partnerUrl = $partnerWebsiteMap[$partnerName] ?? '#';
                    ?>
                        <a href="<?php echo htmlspecialchars($partnerUrl, ENT_QUOTES, 'UTF-8'); ?>" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           title="Kunjungi Website Resmi <?php echo htmlspecialchars($partnerName, ENT_QUOTES, 'UTF-8'); ?>"
                           class="partner-logo-item anim-target flex items-center justify-center p-3 transition-all duration-300 group hover:-translate-y-1.5 cursor-pointer">
                            <img src="uploads/PartnerIcon/<?php echo rawurlencode($logo); ?>" 
                                 alt="<?php echo htmlspecialchars($partnerName, ENT_QUOTES, 'UTF-8'); ?>" 
                                 class="max-h-16 md:max-h-20 w-auto max-w-[180px] object-contain opacity-80 group-hover:opacity-100 group-hover:scale-110 group-hover:saturate-125 transition-all duration-300 filter drop-shadow-sm group-hover:drop-shadow-lg">
                        </a>
                    <?php 
                        endforeach; 
                    else: 
                    ?>
                        <p class="text-on-surface-variant font-body-sm">Belum ada mitra yang ditampilkan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Application Form Section -->
        <section class="w-full px-gutter py-2xl max-w-container-max mx-auto" id="application-form">
            <div
                class="bg-surface-container-lowest border border-outline-variant rounded-[24px] shadow-sm overflow-hidden flex flex-col md:flex-row">
                <!-- Left Side: Stepper -->
                <div
                    class="w-full md:w-1/3 bg-surface-container-low p-xl border-r border-outline-variant flex flex-col">
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-sm font-bold"
                        data-i18n="recruit_submit">Formulir Pendaftaran Magang</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mb-xl" data-i18n="recruit_submit_d">
                        Lengkapi formulir di bawah ini untuk mendaftar program magang.</p>
                    <div class="flex flex-col gap-lg relative">
                        <div class="absolute left-4 top-4 bottom-4 w-px bg-outline-variant -z-10"></div>
                        <!-- Step 1 -->
                        <div class="flex items-start gap-md relative">
                            <div id="step1-dot"
                                class="w-8 h-8 rounded-full bg-surface-container-highest text-on-surface-variant flex items-center justify-center font-label-sm text-label-sm shrink-0 z-10 border-[3px] border-surface-container-lowest font-bold transition-all duration-300">
                                1</div>
                            <div class="pt-1">
                                <h4 id="step1-label" class="font-label-md text-label-md text-on-surface-variant transition-colors duration-300" data-i18n="recruit_step1">Data
                                    Diri</h4>
                                <p id="step1-desc" class="font-body-sm text-body-sm text-outline transition-colors duration-300"
                                    data-i18n="recruit_step1_d">Informasi kontak dasar</p>
                            </div>
                        </div>
                        <!-- Step 2 -->
                        <div class="flex items-start gap-md relative">
                            <div id="step2-dot"
                                class="w-8 h-8 rounded-full bg-surface-container-highest text-on-surface-variant flex items-center justify-center font-label-sm text-label-sm shrink-0 z-10 border-[3px] border-surface-container-lowest font-bold transition-all duration-300">
                                2</div>
                            <div class="pt-1">
                                <h4 id="step2-label" class="font-label-md text-label-md text-on-surface-variant transition-colors duration-300"
                                    data-i18n="recruit_step2">Pendidikan &amp; Posisi</h4>
                                <p id="step2-desc" class="font-body-sm text-body-sm text-outline transition-colors duration-300" data-i18n="recruit_step2_d">Asal
                                    kampus dan pilihan posisi</p>
                            </div>
                        </div>
                        <!-- Step 3 -->
                        <div class="flex items-start gap-md relative">
                            <div id="step3-dot"
                                class="w-8 h-8 rounded-full bg-surface-container-highest text-on-surface-variant flex items-center justify-center font-label-sm text-label-sm shrink-0 z-10 border-[3px] border-surface-container-lowest font-bold transition-all duration-300">
                                3</div>
                            <div class="pt-1">
                                <h4 id="step3-label" class="font-label-md text-label-md text-on-surface-variant transition-colors duration-300"
                                    data-i18n="recruit_step3">Berkas</h4>
                                <p id="step3-desc" class="font-body-sm text-body-sm text-outline transition-colors duration-300" data-i18n="recruit_step3_d">CV &amp;
                                    Tautan Portofolio</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Right Side: Form -->
                <div class="w-full md:w-2/3 p-xl">
                    <form id="application-form-el" class="flex flex-col gap-lg" onsubmit="return false;">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                            <div class="flex flex-col gap-xs">
                                <label class="font-label-md text-label-md text-on-surface"
                                    data-i18n="recruit_fname">Nama Depan</label>
                                <input id="firstName"
                                    class="w-full bg-surface border border-outline-variant rounded-lg px-md py-sm font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary-container/20 transition-all"
                                    placeholder="Alex" type="text" />
                            </div>
                            <div class="flex flex-col gap-xs">
                                <label class="font-label-md text-label-md text-on-surface"
                                    data-i18n="recruit_lname">Nama Belakang</label>
                                <input id="lastName"
                                    class="w-full bg-surface border border-outline-variant rounded-lg px-md py-sm font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary-container/20 transition-all"
                                    placeholder="Chen" type="text" />
                            </div>
                        </div>
                        <div class="flex flex-col gap-xs">
                            <label class="font-label-md text-label-md text-on-surface" data-i18n="recruit_email">Alamat
                                Email</label>
                            <input id="email"
                                class="w-full bg-surface border border-outline-variant rounded-lg px-md py-sm font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary-container/20 transition-all"
                                placeholder="alex.chen@kampus.ac.id" type="email" />
                        </div>
                        <div class="flex flex-col gap-xs relative" id="customSelectContainer">
                            <label class="font-label-md text-label-md text-on-surface" data-i18n="recruit_role">Posisi yang Dilamar</label>

                            <!-- Hidden standard select element for 100% JS/Form compatibility -->
                            <select id="role" class="hidden">
                                <option value="" data-i18n="recruit_select">Pilih posisi magang...</option>
                                <?php foreach ($positions as $pos): ?>
                                    <option value="<?php echo htmlspecialchars($pos['title'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($pos['title'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                                <option value="Lainnya" data-i18n="recruit_role_other">Posisi Lainnya (Ketik Sendiri)...</option>
                            </select>

                            <!-- Custom Styled Trigger Button (Beautiful rounded corners: rounded-2xl) -->
                            <div id="role-wrapper" class="relative group">
                                <button type="button" id="customSelectBtn"
                                    class="w-full bg-surface border border-outline-variant rounded-2xl px-md py-3 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary-container/30 transition-all duration-300 shadow-sm hover:border-primary/60 flex items-center justify-between cursor-pointer">
                                    <span id="customSelectLabel" class="text-on-surface-variant font-medium select-none">Pilih posisi magang...</span>
                                    <span id="roleArrow" class="material-symbols-outlined text-[22px] text-on-surface-variant transition-transform duration-300">expand_more</span>
                                </button>

                                <!-- Custom Floating Dropdown Menu Overlay (Rounded-2xl, soft shadow, backdrop blur, animated with anime.js) -->
                                <div id="customSelectMenu"
                                    class="absolute left-0 right-0 top-full mt-2 bg-surface/95 backdrop-blur-md border border-outline-variant/80 rounded-2xl p-2 shadow-2xl z-50 hidden opacity-0 origin-top overflow-hidden space-y-1">
                                    <div class="custom-option rounded-xl px-4 py-2.5 text-body-md text-on-surface-variant hover:bg-primary-fixed/40 hover:text-primary transition-all cursor-pointer flex items-center justify-between font-medium group"
                                        data-value="" data-label="Pilih posisi magang...">
                                        <span>Pilih posisi magang...</span>
                                    </div>
                                    <?php foreach ($positions as $pos): ?>
                                        <div class="custom-option rounded-xl px-4 py-2.5 text-body-md text-on-surface hover:bg-primary-fixed/40 hover:text-primary transition-all cursor-pointer flex items-center justify-between font-medium group"
                                            data-value="<?php echo htmlspecialchars($pos['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-label="<?php echo htmlspecialchars($pos['title'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <span><?php echo htmlspecialchars($pos['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            <span class="material-symbols-outlined text-[18px] opacity-0 group-hover:opacity-100 transition-opacity text-primary">check</span>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="border-t border-outline-variant/40 my-1"></div>
                                    <div class="custom-option rounded-xl px-4 py-2.5 text-body-md text-primary bg-primary/5 hover:bg-primary/10 transition-all cursor-pointer flex items-center justify-between font-semibold group"
                                        data-value="Lainnya" data-label="Posisi Lainnya (Ketik Sendiri)...">
                                        <span class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-[18px]">edit_note</span>
                                            Posisi Lainnya (Ketik Sendiri)...
                                        </span>
                                        <span class="material-symbols-outlined text-[18px] opacity-0 group-hover:opacity-100 transition-opacity">add</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Container Input Posisi Custom (Hidden by default, animated via anime.js) -->
                            <div id="customRoleContainer" class="hidden opacity-0 origin-top mt-xs">
                                <div class="relative flex items-center">
                                    <span class="absolute left-md text-primary material-symbols-outlined text-[20px]">edit_note</span>
                                    <input id="customRole"
                                        class="w-full bg-surface border border-primary/60 rounded-2xl pl-10 pr-md py-sm font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary-container/30 transition-all duration-300 shadow-sm"
                                        placeholder="Ketikkan posisi yang ingin Anda lamar..." type="text" />
                                </div>
                                <span class="text-body-sm text-outline text-[12px] pl-xs mt-1 block">Silakan ketikkan nama posisi magang yang ingin Anda tuju.</span>
                            </div>
                        </div>
                        <!-- Upload area -->
                        <div class="flex flex-col gap-xs mt-md">
                            <label class="font-label-md text-label-md text-on-surface" data-i18n="recruit_cv">Resume /
                                CV</label>
                            <div id="upload-area" onclick="document.getElementById('fileInput').click()"
                                class="w-full border-2 border-dashed border-outline-variant rounded-xl p-lg flex flex-col items-center justify-center bg-surface hover:bg-surface-container-low transition-colors cursor-pointer group">
                                <input type="file" id="fileInput" accept=".pdf,.docx" class="hidden"
                                    onchange="handleFileUpload(this)" />
                                <div
                                    class="w-12 h-12 rounded-full bg-surface-container-highest flex items-center justify-center text-on-surface-variant mb-sm group-hover:bg-primary-fixed group-hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined">upload_file</span>
                                </div>
                                <span id="upload-text" class="font-label-md text-label-md text-on-surface text-center"
                                    data-i18n="recruit_upload">Klik untuk mengunggah atau seret file ke sini</span>
                                <span class="font-body-sm text-body-sm text-on-surface-variant text-center"
                                    data-i18n="recruit_upload_f">PDF, DOCX maksimal 10MB</span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-xs">
                            <label class="font-label-md text-label-md text-on-surface"
                                data-i18n="recruit_portfolio">Tautan Portofolio (Opsional)</label>
                            <div class="relative">
                                <span
                                    class="absolute left-md top-1/2 -translate-y-1/2 text-outline material-symbols-outlined text-[20px]">link</span>
                                <input id="portfolio"
                                    class="w-full bg-surface border border-outline-variant rounded-lg pl-10 pr-md py-sm font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary-container/20 transition-all"
                                    placeholder="https://github.com/username" type="url" />
                            </div>
                        </div>
                        <div class="flex justify-end pt-md mt-sm border-t border-outline-variant">
                            <button onclick="submitApplication()"
                                class="bg-primary text-on-primary rounded-lg px-xl py-sm font-label-md text-label-md hover:bg-primary-container transition-colors active:scale-95 shadow-md font-bold flex items-center gap-2"
                                type="button" data-i18n="recruit_submit_btn">
                                <span>Kirim Pendaftaran</span>
                                <span class="material-symbols-outlined text-[18px]">send</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-md backdrop-blur-sm transition-all duration-300">
        <div class="w-full max-w-md bg-surface rounded-2xl p-xl shadow-2xl border border-outline-variant transform transition-all scale-100 flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 mb-md">
                <span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            </div>
            <h3 class="font-headline-md text-headline-md font-bold text-on-surface mb-xs" data-i18n="recruit_modal_title">Pendaftaran Berhasil!</h3>
            <p class="font-body-sm text-body-sm text-on-surface-variant mb-lg" data-i18n="recruit_modal_desc">
                Terima kasih telah mendaftar. Data pendaftaran Anda telah kami terima dan tersimpan dalam sistem. Tim Kedayweb akan segera meninjau aplikasi Anda.
            </p>

            <div class="w-full bg-surface-container-low rounded-xl p-md text-left mb-lg border border-outline-variant space-y-xs">
                <div class="flex justify-between text-xs">
                    <span class="text-on-surface-variant font-medium">Nama:</span>
                    <span id="applicant-name" class="font-bold text-on-surface"></span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-on-surface-variant font-medium">Email:</span>
                    <span id="applicant-email" class="font-bold text-on-surface"></span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-on-surface-variant font-medium">Posisi:</span>
                    <span id="applicant-role" class="font-bold text-primary"></span>
                </div>
            </div>

            <button onclick="closeModal()" class="w-full bg-primary text-on-primary rounded-xl py-sm font-label-md font-bold hover:bg-primary-container transition-colors shadow-md active:scale-95">
                Tutup & Kembali
            </button>
        </div>
    </div>

    <?php include 'partials/footer.php'; ?>

    <script>
        /* ─── Step Indicator Helpers ─── */

        /**
         * Aktifkan indikator step (lingkaran berwarna primary + centang).
         * @param {number} step - nomor step (1, 2, atau 3)
         */
        function activateStep(step) {
            const dot = document.getElementById('step' + step + '-dot');
            if (!dot) return;
            // Hapus state tidak aktif
            dot.classList.remove(
                'bg-surface-container-highest', 'text-on-surface-variant',
                'bg-primary-container'
            );
            // Tambahkan state aktif
            dot.classList.add('bg-primary', 'text-on-primary', 'scale-110', 'shadow-md');
            dot.innerHTML = '<span class="material-symbols-outlined text-[16px]" style="font-variation-settings:\'FILL\' 1">check</span>';

            // Aktifkan juga teks label di samping dot
            const stepLabel = document.getElementById('step' + step + '-label');
            if (stepLabel) stepLabel.classList.replace('text-on-surface-variant', 'text-on-surface');
            const stepDesc = document.getElementById('step' + step + '-desc');
            if (stepDesc) stepDesc.classList.replace('text-outline', 'text-on-surface-variant');
        }

        /**
         * Nonaktifkan indikator step (kembali ke state abu-abu).
         * @param {number} step - nomor step (1, 2, atau 3)
         */
        function deactivateStep(step) {
            const dot = document.getElementById('step' + step + '-dot');
            if (!dot) return;
            dot.classList.remove('bg-primary', 'text-on-primary', 'bg-green-600', 'text-white', 'scale-110', 'shadow-md');
            dot.classList.add('bg-surface-container-highest', 'text-on-surface-variant');
            dot.innerHTML = step;
        }

        /* ─── Real-time Step 1: Nama Depan + Nama Belakang + Email ─── */
        function checkStep1() {
            const firstName = document.getElementById('firstName').value.trim();
            const lastName  = document.getElementById('lastName').value.trim();
            const email     = document.getElementById('email').value.trim();
            const emailOk   = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

            if (firstName && lastName && email && emailOk) {
                activateStep(1);
            } else {
                deactivateStep(1);
            }
        }

        /* ─── Custom Select Component Controls ─── */
        let isCustomSelectOpen = false;

        function toggleCustomSelect() {
            if (isCustomSelectOpen) {
                closeCustomSelect();
            } else {
                openCustomSelect();
            }
        }

        function openCustomSelect() {
            const menu = document.getElementById('customSelectMenu');
            const arrow = document.getElementById('roleArrow');
            const btn = document.getElementById('customSelectBtn');

            if (!menu || isCustomSelectOpen) return;
            isCustomSelectOpen = true;

            menu.classList.remove('hidden');
            if (btn) btn.classList.add('border-primary', 'ring-2', 'ring-primary-container/30');

            if (arrow) {
                if (window.anime) {
                    anime({
                        targets: arrow,
                        rotate: 180,
                        duration: 300,
                        easing: 'easeOutCubic'
                    });
                } else {
                    arrow.classList.add('rotate-180');
                }
            }

            if (window.anime) {
                anime({
                    targets: menu,
                    opacity: [0, 1],
                    translateY: [-12, 0],
                    scale: [0.96, 1],
                    duration: 300,
                    easing: 'easeOutCubic'
                });
            } else {
                menu.classList.remove('opacity-0');
            }
        }

        function closeCustomSelect() {
            const menu = document.getElementById('customSelectMenu');
            const arrow = document.getElementById('roleArrow');
            const btn = document.getElementById('customSelectBtn');

            if (!menu || !isCustomSelectOpen) return;
            isCustomSelectOpen = false;

            if (btn) btn.classList.remove('border-primary', 'ring-2', 'ring-primary-container/30');

            if (arrow) {
                if (window.anime) {
                    anime({
                        targets: arrow,
                        rotate: 0,
                        duration: 300,
                        easing: 'easeOutCubic'
                    });
                } else {
                    arrow.classList.remove('rotate-180');
                }
            }

            if (window.anime) {
                anime({
                    targets: menu,
                    opacity: [1, 0],
                    translateY: [0, -8],
                    scale: [1, 0.96],
                    duration: 220,
                    easing: 'easeInCubic',
                    complete: function () {
                        menu.classList.add('hidden');
                    }
                });
            } else {
                menu.classList.add('hidden', 'opacity-0');
            }
        }

        function selectRoleValue(val, labelText) {
            const roleSelect = document.getElementById('role');
            const customSelectLabel = document.getElementById('customSelectLabel');

            if (roleSelect) {
                roleSelect.value = val;
            }

            if (customSelectLabel) {
                customSelectLabel.textContent = labelText;
                if (val === '') {
                    customSelectLabel.classList.add('text-on-surface-variant');
                    customSelectLabel.classList.remove('text-on-surface', 'font-semibold');
                } else {
                    customSelectLabel.classList.remove('text-on-surface-variant');
                    customSelectLabel.classList.add('text-on-surface', 'font-semibold');
                }
            }

            // Animasikan tombol saat pilihan diklik dengan Anime.js
            const btn = document.getElementById('customSelectBtn');
            if (window.anime && btn) {
                anime({
                    targets: btn,
                    scale: [1, 1.015, 1],
                    duration: 300,
                    easing: 'easeOutQuad'
                });
            }

            closeCustomSelect();
            handleRoleChange();
        }

        /* ─── Real-time Step 2: Posisi yang Dilamar ─── */
        function checkStep2() {
            const roleVal = document.getElementById('role') ? document.getElementById('role').value : '';
            let isValid = false;
            if (roleVal === 'Lainnya') {
                const customVal = document.getElementById('customRole') ? document.getElementById('customRole').value.trim() : '';
                isValid = customVal !== '';
            } else if (roleVal && roleVal !== '') {
                isValid = true;
            }

            if (isValid) {
                activateStep(2);
            } else {
                deactivateStep(2);
            }
        }

        /* ─── Handler Perubahan Posisi Dropdown (dengan Animasi Anime.js) ─── */
        function handleRoleChange() {
            const roleSelect = document.getElementById('role');
            const container = document.getElementById('customRoleContainer');

            if (roleSelect && roleSelect.value === 'Lainnya') {
                if (container && container.classList.contains('hidden')) {
                    container.classList.remove('hidden');
                    if (window.anime) {
                        anime({
                            targets: container,
                            opacity: [0, 1],
                            translateY: [-14, 0],
                            scale: [0.96, 1],
                            duration: 400,
                            easing: 'easeOutCubic'
                        });
                    } else {
                        container.classList.remove('opacity-0');
                    }
                }
                const customInput = document.getElementById('customRole');
                if (customInput) customInput.focus();
            } else if (container) {
                if (!container.classList.contains('hidden')) {
                    if (window.anime) {
                        anime({
                            targets: container,
                            opacity: [1, 0],
                            translateY: [0, -10],
                            duration: 250,
                            easing: 'easeInCubic',
                            complete: function () {
                                container.classList.add('hidden');
                                const customInput = document.getElementById('customRole');
                                if (customInput) customInput.value = '';
                                checkStep2();
                            }
                        });
                    } else {
                        container.classList.add('hidden', 'opacity-0');
                        const customInput = document.getElementById('customRole');
                        if (customInput) customInput.value = '';
                    }
                }
            }
            checkStep2();
        }

        /* ─── Real-time Step 3: Upload CV ─── */
        function checkStep3() {
            const fileInput = document.getElementById('fileInput');
            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                activateStep(3);
            } else {
                deactivateStep(3);
            }
        }

        /* ─── File Upload Handler ─── */
        function handleFileUpload(input) {
            if (input.files && input.files[0]) {
                document.getElementById('upload-text').textContent = '✓ ' + input.files[0].name;
                document.getElementById('upload-area').classList.add('border-primary', 'bg-primary-fixed');
            } else {
                document.getElementById('upload-text').textContent = 'Klik untuk mengunggah atau seret file ke sini';
                document.getElementById('upload-area').classList.remove('border-primary', 'bg-primary-fixed');
            }
            checkStep3();
        }

        /* ─── Pasang Event Listeners saat DOM siap ─── */
        document.addEventListener('DOMContentLoaded', function () {
            // Step 1 listeners
            ['firstName', 'lastName', 'email'].forEach(function (id) {
                const el = document.getElementById(id);
                if (el) el.addEventListener('input', checkStep1);
            });

            // Trigger custom dropdown menu
            const btn = document.getElementById('customSelectBtn');
            if (btn) {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    toggleCustomSelect();
                });
            }

            // Option items click handler
            document.querySelectorAll('.custom-option').forEach(function (opt) {
                opt.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const val = this.getAttribute('data-value');
                    const label = this.getAttribute('data-label') || this.innerText.trim();
                    selectRoleValue(val, label);
                });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function (e) {
                const container = document.getElementById('customSelectContainer');
                if (container && !container.contains(e.target)) {
                    closeCustomSelect();
                }
            });

            // Custom Role input listener & focus animation
            const customRoleInput = document.getElementById('customRole');
            if (customRoleInput) {
                customRoleInput.addEventListener('input', checkStep2);
                customRoleInput.addEventListener('focus', function () {
                    if (window.anime) {
                        anime({
                            targets: '#customRoleContainer',
                            scale: [1, 1.01, 1],
                            duration: 300,
                            easing: 'easeOutQuad'
                        });
                    }
                });
            }
        });

        /* ─── Submit Application ─── */
        function submitApplication() {
            const firstName = document.getElementById('firstName').value.trim();
            const lastName  = document.getElementById('lastName').value.trim();
            const email     = document.getElementById('email').value.trim();
            const roleSelect = document.getElementById('role').value;
            let role = roleSelect;
            if (roleSelect === 'Lainnya') {
                const customVal = document.getElementById('customRole') ? document.getElementById('customRole').value.trim() : '';
                role = customVal;
            }

            const portfolio = document.getElementById('portfolio') ? document.getElementById('portfolio').value.trim() : '';
            const fileInput = document.getElementById('fileInput');

            const fillMsg = (typeof t === 'function' ? t('recruit_fill') : null) || 'Silakan lengkapi semua kolom wajib.';

            if (!firstName || !lastName || !email || !role) {
                alert(fillMsg);
                return;
            }

            const formData = new FormData();
            formData.append('first_name', firstName);
            formData.append('last_name', lastName);
            formData.append('email', email);
            formData.append('position', role);
            formData.append('portfolio', portfolio);
            if (fileInput && fileInput.files[0]) {
                formData.append('cv_file', fileInput.files[0]);
            }

            fetch('api_submit_application.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                setTimeout(() => {
                    // Tampilkan modal
                    document.getElementById('applicant-name').textContent = `${firstName} ${lastName}`;
                    document.getElementById('applicant-email').textContent = email;
                    document.getElementById('applicant-role').textContent = role;

                    const modal = document.getElementById('successModal');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');

                    // Reset form & custom role container & select label
                    document.getElementById('application-form-el').reset();
                    const customSelectLabel = document.getElementById('customSelectLabel');
                    if (customSelectLabel) {
                        customSelectLabel.textContent = 'Pilih posisi magang...';
                        customSelectLabel.classList.add('text-on-surface-variant');
                        customSelectLabel.classList.remove('text-on-surface', 'font-semibold');
                    }
                    const container = document.getElementById('customRoleContainer');
                    if (container) container.classList.add('hidden', 'opacity-0');
                    document.getElementById('upload-text').textContent = 'Klik untuk mengunggah atau seret file ke sini';
                    document.getElementById('upload-area').classList.remove('border-primary', 'bg-primary-fixed');

                    // Reset semua dot ke state awal
                    [1, 2, 3].forEach(function (s) { deactivateStep(s); });
                }, 600);
            })
            .catch(() => {
                alert('Terjadi kesalahan saat mengirim pendaftaran. Silakan coba lagi.');
            });
        }

        /* ─── Close Modal ─── */
        function closeModal() {
            const modal = document.getElementById('successModal');
            if (modal) {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }
        }

        /* ═══════════════════════════════════════════════════
           CINEMATIC ANIMATION SYSTEM — Awwwards-grade
           ═══════════════════════════════════════════════════ */

        /* ─── Hero Entrance: blur-dissolve + scale + cinematic drift ─── */
        (function() {
            if (!window.anime) {
                ['hero-badge','hero-title','hero-subtitle','hero-img-box'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.style.opacity = '1';
                });
                return;
            }
            anime.timeline({ easing: 'easeOutExpo' })
                .add({
                    targets: '#hero-badge',
                    opacity: [0, 1],
                    translateY: [-18, 0],
                    scale: [0.85, 1],
                    filter: ['blur(8px)', 'blur(0px)'],
                    duration: 820
                })
                .add({
                    targets: '#hero-title',
                    opacity: [0, 1],
                    translateY: [44, 0],
                    scale: [0.92, 1],
                    filter: ['blur(14px)', 'blur(0px)'],
                    duration: 950
                }, '-=540')
                .add({
                    targets: '#hero-subtitle',
                    opacity: [0, 1],
                    translateY: [28, 0],
                    filter: ['blur(8px)', 'blur(0px)'],
                    duration: 820
                }, '-=620')
                .add({
                    targets: '#hero-img-box',
                    opacity: [0, 1],
                    scale: [0.92, 1],
                    translateY: [56, 0],
                    filter: ['blur(18px)', 'blur(0px)'],
                    duration: 1150,
                    easing: 'easeOutCubic'
                }, '-=580');
        })();

        /* ─── Parallax: hero image drifts slowly as you scroll ─── */
        (function() {
            const img = document.getElementById('hero-parallax-img');
            const box = document.getElementById('hero-img-box');
            if (!img || !box) return;
            let ticking = false;
            window.addEventListener('scroll', () => {
                if (!ticking) {
                    requestAnimationFrame(() => {
                        const rect = box.getBoundingClientRect();
                        if (rect.bottom > 0 && rect.top < window.innerHeight) {
                            const progress = (window.innerHeight / 2 - rect.top - rect.height / 2) / window.innerHeight;
                            img.style.transform = `translateY(${progress * 55}px) scale(1.12)`;
                        }
                        ticking = false;
                    });
                    ticking = true;
                }
            }, { passive: true });
        })();

        /* ─── Magnetic Card Hover: lift + elastic icon bounce ─── */
        (function() {
            document.querySelectorAll('.anim-target.group').forEach(card => {
                const icon = card.querySelector('.material-symbols-outlined');
                card.addEventListener('mouseenter', () => {
                    if (!window.anime) return;
                    anime({ targets: card, translateY: -11, duration: 360, easing: 'easeOutCubic' });
                    if (icon) anime({
                        targets: icon,
                        translateY: [-10, 0], rotate: [-5, 0], scale: [1.22, 1],
                        duration: 680, easing: 'easeOutElastic(1, .5)'
                    });
                });
                card.addEventListener('mouseleave', () => {
                    if (!window.anime) return;
                    anime({ targets: card, translateY: 0, duration: 520, easing: 'easeOutElastic(1, .6)' });
                });
            });
        })();

        /* ─── Observe-once helper ─── */
        function onEnterOnce(el, cb, opts) {
            const obs = new IntersectionObserver(entries => {
                entries.forEach(e => { if (e.isIntersecting) { cb(); obs.disconnect(); } });
            }, opts || { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });
            obs.observe(el);
        }

        /* ─── Benefit Cards: blur+rotation+scale choreography ─── */
        (function() {
            const grid = document.getElementById('benefits-grid');
            if (!grid || !window.anime) return;

            const leftCards  = grid.querySelectorAll('.benefit-card-left');
            const rightCards = grid.querySelectorAll('.benefit-card-right');
            const topCard    = document.getElementById('benefit-card-2');
            const bottomCard = document.getElementById('benefit-card-5');

            anime.set(leftCards,  { translateX: -92, rotate: -2.8, scale: 0.87, opacity: 0, filter: 'blur(10px)' });
            anime.set(rightCards, { translateX:  92, rotate:  2.8, scale: 0.87, opacity: 0, filter: 'blur(10px)' });
            if (topCard)    anime.set(topCard,    { translateY: -82, rotate:  1.8, scale: 0.87, opacity: 0, filter: 'blur(10px)' });
            if (bottomCard) anime.set(bottomCard, { translateY:  82, rotate: -1.8, scale: 0.87, opacity: 0, filter: 'blur(10px)' });

            onEnterOnce(grid, () => {
                anime({ targets: leftCards, translateX: [null,0], rotate: [null,0], scale: [null,1],
                        opacity: [null,1], filter: [null,'blur(0px)'], duration: 980,
                        delay: anime.stagger(190, { start: 0 }), easing: 'easeOutExpo' });
                anime({ targets: rightCards, translateX: [null,0], rotate: [null,0], scale: [null,1],
                        opacity: [null,1], filter: [null,'blur(0px)'], duration: 980,
                        delay: anime.stagger(190, { start: 90 }), easing: 'easeOutExpo' });
                if (topCard) anime({ targets: topCard, translateY: [null,0], rotate: [null,0], scale: [null,1],
                        opacity: [null,1], filter: [null,'blur(0px)'], duration: 1080,
                        delay: 130, easing: 'easeOutElastic(1, .65)' });
                if (bottomCard) anime({ targets: bottomCard, translateY: [null,0], rotate: [null,0], scale: [null,1],
                        opacity: [null,1], filter: [null,'blur(0px)'], duration: 1080,
                        delay: 290, easing: 'easeOutElastic(1, .65)' });
            }, { threshold: 0.09, rootMargin: '0px 0px -80px 0px' });
        })();

        /* ─── Position Cards: rise + blur + rotate ─── */
        (function() {
            const grid = document.getElementById('positions-grid');
            if (!grid || !window.anime) return;
            const cards = grid.querySelectorAll('.position-card');
            anime.set(cards, { translateY: 72, scale: 0.89, opacity: 0, filter: 'blur(12px)', rotate: 1.8 });
            onEnterOnce(grid, () => {
                anime({ targets: cards, translateY: [null,0], scale: [null,1], opacity: [null,1],
                        filter: [null,'blur(0px)'], rotate: [null,0], duration: 1020,
                        delay: anime.stagger(170, { start: 60 }), easing: 'easeOutExpo' });
            });
        })();

        /* ─── Timeline Steps: alternate left / right with blur ─── */
        (function() {
            const container = document.getElementById('timeline-container');
            if (!container || !window.anime) return;
            const steps = container.querySelectorAll('.timeline-step');
            steps.forEach((step, i) => {
                anime.set(step, { translateX: (i % 2 === 0 ? -82 : 82), opacity: 0, scale: 0.91, filter: 'blur(9px)' });
            });
            onEnterOnce(container, () => {
                anime({ targets: steps, translateX: [null,0], opacity: [null,1], scale: [null,1],
                        filter: [null,'blur(0px)'], duration: 920,
                        delay: anime.stagger(250, { start: 100 }), easing: 'easeOutExpo' });
            }, { threshold: 0.1, rootMargin: '0px 0px -80px 0px' });
        })();

        /* ─── Partner Logos: scatter fly-in with rotation ─── */
        (function() {
            const section = document.getElementById('partners');
            if (!section || !window.anime) return;
            const logos = section.querySelectorAll('.partner-logo-item');
            logos.forEach((logo, i) => {
                const angle = (i / Math.max(logos.length, 1)) * Math.PI * 2;
                anime.set(logo, {
                    translateX: Math.cos(angle) * 64,
                    translateY: Math.sin(angle) * 44 + 44,
                    scale: 0.48, opacity: 0,
                    rotate: (i % 2 === 0 ? 1 : -1) * (8 + i * 3)
                });
            });
            onEnterOnce(section, () => {
                anime({ targets: logos, translateX: [null,0], translateY: [null,0],
                        scale: [null,1], opacity: [null,1], rotate: [null,0],
                        duration: 1100, delay: anime.stagger(65, { start: 20 }),
                        easing: 'easeOutElastic(1, .58)' });
            }, { threshold: 0.05, rootMargin: '0px 0px 60px 0px' });
        })();

        /* ═══════════════════════════════════════════════════
           FLOATING BATIK GAJAH OLING BACKGROUND ANIMATION
           ─ Infinite organic drift, tiap elemen jalur unik
           ─ Scroll parallax: elemen kecil/jauh vs besar/dekat
           ═══════════════════════════════════════════════════ */
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
</body>

</html>