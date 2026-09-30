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
        html {
            scroll-behavior: smooth;
        }
        .custom-shadow-hover:hover {
            box-shadow: 0 10px 15px -3px rgba(30, 58, 138, 0.05), 0 4px 6px -2px rgba(30, 58, 138, 0.02);
        }
    </style>
</head>

<body class="bg-background text-on-surface font-body-md min-h-screen flex flex-col antialiased">
    <?php include 'partials/topnav-public.php'; ?>

    <main class="flex-grow">
        <!-- Hero Section -->
        <section class="w-full px-gutter py-3xl max-w-container-max mx-auto flex flex-col items-center text-center">
            <span
                class="inline-block px-md py-xs rounded-full bg-primary-fixed text-on-primary-fixed-variant font-label-sm text-label-sm mb-lg border border-primary-fixed-dim font-semibold"
                data-i18n="recruit_careers">Pendaftaran</span>
            <h1 class="font-headline-xl text-headline-xl text-on-surface mb-md max-w-3xl" data-i18n="recruit_title">
                Pendaftaran Anak Magang</h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mb-xl" data-i18n="recruit_subtitle">
                Mulai perjalanan karir Anda dengan pengalaman nyata, bimbingan mentor industri, dan proyek langsung di
                Kedayweb.
            </p>
            <div
                class="w-full h-64 md:h-[400px] rounded-[24px] overflow-hidden border border-outline-variant shadow-sm relative group mt-lg">
                <img class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-lg">
                <!-- Benefit 1 -->
                <div class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg flex flex-col items-start hover:-translate-y-1 transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-primary-container text-on-primary flex items-center justify-center mb-md">
                        <span class="material-symbols-outlined text-[26px]">work_history</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-xs font-bold">Pengalaman Proyek Nyata</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                        Terlibat langsung dalam pengerjaan proyek skala industri yang digunakan oleh klien dan pengguna nyata.
                    </p>
                </div>

                <!-- Benefit 2 -->
                <div class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg flex flex-col items-start hover:-translate-y-1 transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-secondary-container text-on-secondary-container flex items-center justify-center mb-md">
                        <span class="material-symbols-outlined text-[26px]">supervisor_account</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-xs font-bold">Mentorship Intensif</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                        Bimbingan langsung dari mentor berpengalaman yang siap mengarahkan dan memandu perkembangan Anda.
                    </p>
                </div>

                <!-- Benefit 3 -->
                <div class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg flex flex-col items-start hover:-translate-y-1 transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-surface-container-high text-primary flex items-center justify-center mb-md">
                        <span class="material-symbols-outlined text-[26px]">workspace_premium</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-xs font-bold">Sertifikat & Portofolio</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                        Mendapatkan sertifikat magang resmi serta hasil karya nyata yang siap dipamerkan dalam portofolio profesional Anda.
                    </p>
                </div>

                <!-- Benefit 4 -->
                <div class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg flex flex-col items-start hover:-translate-y-1 transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-surface-container-high text-primary flex items-center justify-center mb-md">
                        <span class="material-symbols-outlined text-[26px]">hub</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-xs font-bold">Jaringan & Networking</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                        Perluas jaringan kerja bersama para profesional industri, sesama talenta muda, dan ekosistem digital Kedayweb.
                    </p>
                </div>

                <!-- Benefit 5 -->
                <div class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg flex flex-col items-start hover:-translate-y-1 transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-primary-container text-on-primary flex items-center justify-center mb-md">
                        <span class="material-symbols-outlined text-[26px]">rocket_launch</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-xs font-bold">Peluang Karir Lanjutan</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                        Peserta magang dengan performa dan kontribusi terbaik berkesempatan direkrut langsung menjadi tim tetap.
                    </p>
                </div>

                <!-- Benefit 6 -->
                <div class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg flex flex-col items-start hover:-translate-y-1 transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-secondary-container text-on-secondary-container flex items-center justify-center mb-md">
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
                <?php foreach ($positions as $pos): ?>
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-lg custom-shadow-hover transition-all duration-300 flex flex-col h-full group">
                        <div class="flex justify-between items-start mb-md">
                            <div class="w-12 h-12 rounded-lg bg-surface-container-high flex items-center justify-center text-primary group-hover:bg-primary-container group-hover:text-on-primary transition-colors">
                                <span class="material-symbols-outlined"><?php echo htmlspecialchars($pos['icon'] ?? 'work', ENT_QUOTES, 'UTF-8'); ?></span>
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
                           class="partner-logo-item flex items-center justify-center p-3 transition-all duration-300 group hover:-translate-y-1.5 opacity-0 cursor-pointer">
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
                        <div class="flex flex-col gap-xs">
                            <label class="font-label-md text-label-md text-on-surface" data-i18n="recruit_role">Posisi
                                yang Dilamar</label>
                            <select id="role"
                                class="w-full bg-surface border border-outline-variant rounded-lg px-md py-sm font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary-container/20 transition-all appearance-none cursor-pointer">
                                <option value="" data-i18n="recruit_select">Pilih posisi magang...</option>
                                <?php foreach ($positions as $pos): ?>
                                    <option value="<?php echo htmlspecialchars($pos['title'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($pos['title'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
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
                                class="bg-primary text-on-primary rounded-lg px-xl py-sm font-label-md text-label-md hover:bg-primary-container transition-colors active:scale-95 shadow-md font-bold"
                                type="button" data-i18n="recruit_submit_btn">
                                Kirim Pendaftaran
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

        /* ─── Real-time Step 2: Posisi yang Dilamar ─── */
        function checkStep2() {
            const role = document.getElementById('role').value;
            if (role && role !== '') {
                activateStep(2);
            } else {
                deactivateStep(2);
            }
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

            // Step 2 listener
            const roleEl = document.getElementById('role');
            if (roleEl) roleEl.addEventListener('change', checkStep2);

            // Step 3 listener (sudah di-handle oleh handleFileUpload via onchange)
        });

        /* ─── Submit Application ─── */
        function submitApplication() {
            const firstName = document.getElementById('firstName').value.trim();
            const lastName  = document.getElementById('lastName').value.trim();
            const email     = document.getElementById('email').value.trim();
            const role      = document.getElementById('role').value;
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

                    // Reset form
                    document.getElementById('application-form-el').reset();
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
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }

        /* ─── Anime.js Scroll Animation for Partner Logos ─── */
        document.addEventListener('DOMContentLoaded', () => {
            const partnerSection = document.getElementById('partners');
            if (partnerSection) {
                let hasAnimated = false;
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting && !hasAnimated) {
                            hasAnimated = true;
                            if (window.anime) {
                                anime({
                                    targets: '.partner-logo-item',
                                    translateY: [45, 0],
                                    scale: [0.7, 1],
                                    opacity: [0, 1],
                                    rotate: [-4, 0],
                                    delay: anime.stagger(120, { start: 150 }),
                                    duration: 1100,
                                    easing: 'easeOutElastic(1, .65)'
                                });
                            } else {
                                document.querySelectorAll('.partner-logo-item').forEach(el => el.classList.remove('opacity-0'));
                            }
                        }
                    });
                }, { threshold: 0.2 });
                observer.observe(partnerSection);
            }
        });
    </script>
</body>

</html>