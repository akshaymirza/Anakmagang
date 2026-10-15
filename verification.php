<!DOCTYPE html>
<html class="h-full" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Kedayweb – Verifikasi Sertifikat Magang</title>
    <meta name="description" content="Verifikasi keaslian sertifikat magang dari Kedayweb secara online menggunakan ID sertifikat."/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Geist:wght@400;500;600;700;800;900&family=Montserrat:wght@800&display=swap" rel="stylesheet"/>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="shared-config.js"></script>
    <style>
        .filled-icon { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .soft-shadow  { box-shadow: 0 10px 15px -3px rgba(30,58,138,.05), 0 4px 6px -2px rgba(30,58,138,.02); }
        #search-section { display: flex; }
        #result-section,#error-section { display: none; }
        .bar-fill { transition: width 1.2s cubic-bezier(.4,0,.2,1); }
        @keyframes fadeSlideUp { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }
        .animate-in { animation: fadeSlideUp .5s ease both; }
        .spin { animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Sertifikat visual (template: uploads/Certificate/) ──────────
           Semua posisi memakai % / cqw agar tetap proporsional di layar apa pun.
           Angka posisi diambil dari sertifikat_pkl_kedayweb.php:
             nama  → top 45%, tengah, lebar 50%, font 26px pada lebar 1000px (= 2.6cqw)
             QR    → left 43.7%, bottom 11.6%, 12.5% x 17.7% (persegi)          */
        .cert-canvas {
            position: relative; width: 100%; aspect-ratio: 297 / 210;
            container-type: inline-size; overflow: hidden; background: #f5f3e7;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,.25);
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .cert-bg { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: fill;
                   user-select: none; -webkit-user-drag: none; pointer-events: none; }
        .cert-name {
            position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%);
            width: 50%; text-align: center; white-space: nowrap; z-index: 10;
            font-family: 'Montserrat', sans-serif; font-weight: 800; color: #0f172a;
            font-size: calc(var(--cert-fs, 2.6) * 1cqw); line-height: 1.25;
            text-transform: capitalize;
        }
        .cert-qr {
            position: absolute; left: 43.7%; bottom: 11.6%; width: 12.5%; height: 17.7%;
            display: flex; align-items: center; justify-content: center; z-index: 20;
            background: #fff; border-radius: .8cqw; padding: .9cqw; box-sizing: border-box;
        }
        .cert-qr img { width: 100%; height: 100%; object-fit: contain; display: block; }
        .cert-approval-watermark {
            position: absolute; inset: 0; z-index: 25; pointer-events: none;
            display: flex; align-items: flex-end; justify-content: space-between;
            padding: 4% 6%;
        }
        .cert-stamp-cover {
            position: absolute; bottom: 7.5%; width: 29%; height: 23%;
            background: rgba(245, 243, 231, 0.95); border: 0.22cqw dashed #f59e0b;
            border-radius: 0.8cqw; display: flex; flex-direction: column;
            align-items: center; justify-content: center; text-align: center;
            padding: 0.5cqw 0.8cqw; box-sizing: border-box; overflow: hidden;
            z-index: 25; pointer-events: none;
        }
        .cert-stamp-cover.left { left: 7.5%; }
        .cert-stamp-cover.right { right: 7.5%; }
        .cert-stamp-cover .cover-icon {
            font-size: 2cqw !important; line-height: 1 !important; color: #d97706; margin-bottom: 0.25cqw;
        }
        .cert-stamp-cover .cover-title {
            font-size: 1.18cqw; font-weight: 700; line-height: 1.2; color: #92400e;
            white-space: nowrap; max-width: 100%; text-overflow: ellipsis; overflow: hidden;
        }
        .cert-stamp-cover .cover-desc {
            font-size: 0.88cqw; font-weight: 500; line-height: 1.2; color: #b45309;
            white-space: nowrap; max-width: 100%; text-overflow: ellipsis; overflow: hidden;
            margin-top: 0.15cqw;
        }

        /* ── Slider ── */
        .vf-slider-outer { overflow: hidden; width: 100%; }
        .vf-slider-track {
            display: flex;
            transition: transform .45s cubic-bezier(.4,0,.2,1);
            will-change: transform;
        }
        .vf-slide { flex: 0 0 100%; width: 100%; }
        .vf-slider-dots { display:flex; justify-content:center; gap:7px; padding:10px 0 4px; }
        .vf-dot {
            width:7px; height:7px; border-radius:50%;
            background:#cbd5e1; cursor:pointer;
            transition: background .25s, transform .25s;
        }
        .vf-dot.active { background:#1e3a8a; transform:scale(1.35); }

        /* ── Slide 2: halaman belakang tabel nilai ── */
        .cert-back-canvas {
            width: 100%; aspect-ratio: 297 / 210;
            position: relative; overflow: hidden;
            background: #f5f3e7 url('uploads/Certificate/Sertifikat_Belakang.png') center / 100% 100% no-repeat;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
            container-type: inline-size;
            font-family: 'Poppins', sans-serif; color: #000;
        }
        .cert-back-inner {
            position: absolute; inset: 0;
            padding: 3.3cqw 5.4cqw 0;
            display: flex; flex-direction: column;
        }
        .cb-judul { font-size:2.1cqw; font-weight:700; text-align:center; line-height:1.35; }
        .cb-data { margin-top:2cqw; display:grid; row-gap:.1cqw; font-size:1.08cqw; line-height:1.65cqw; }
        .cb-baris { display:grid; grid-template-columns:23cqw 2cqw 1fr; }
        .cb-label { font-weight:500; }
        .cb-table {
            width:100%; margin-top:1.4cqw; border-collapse:collapse;
            table-layout:fixed; border:1px solid #000; font-size:1.06cqw; line-height:1.4;
        }
        .cb-table col.no   { width:2.7cqw; }
        .cb-table col.skor { width:3.9cqw; }
        .cb-table th,.cb-table td { border:1px solid #000; padding:0 .6cqw; vertical-align:middle; }
        .cb-table th { font-weight:500; text-align:center; }
        .cb-table td { height:2.05cqw; }
        .cb-table td.no,.cb-table td.skor { text-align:center; padding:0; }
        .cb-table thead tr.judul-tb th { height:2.2cqw; }
        .cb-table thead tr.aspek  th   { height:2.1cqw; }
        .cb-tfoot-rata { height:2.1cqw; text-align:center; }
        .cb-tfoot-kum  { height:6.8cqw; padding:0 !important; }
        .cb-hasil { font-weight:500; }
        .cb-rumus {
            display:flex; justify-content:center; align-items:flex-start;
            gap:.4cqw; font-weight:500; font-size:1.05cqw;
        }
        .cb-pecahan { display:flex; flex-direction:column; align-items:center; font-weight:400; }
        .cb-pembilang { border-bottom:1px solid #000; padding:0 .2cqw; }
        .cb-bawah {
            margin-top:1.4cqw; padding-right:2.4cqw;
            display:flex; justify-content:space-between; align-items:flex-start;
            font-size:1.05cqw;
        }
        .cb-predikat {
            width:19cqw; border-collapse:collapse; table-layout:fixed;
            font-size:.87cqw; line-height:1.15cqw;
        }
        .cb-predikat th { font-weight:500; padding:.1cqw 0; border-top:1px solid #000; border-bottom:1px solid #000; }
        .cb-predikat td { padding:0; text-align:center; }
        .cb-ttd p { line-height:1.65cqw; font-size:1.05cqw; }
        .cb-ttd-ruang { height:4.2cqw; }
        .cb-ttd-nama { font-weight:500; text-decoration:underline; }

        /* ── Cetak / Simpan PDF: 297mm x 210mm (A4 Landscape) ── */
        @page {
            size: 297mm 210mm;
            margin: 0;
        }
        @media screen {
            #print-area { display: none !important; }
        }
        @media print {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                width: 297mm !important;
                height: auto !important;
                overflow: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            body > *:not(#print-area) {
                display: none !important;
            }
            #print-area {
                display: block !important;
                width: 297mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: transparent !important;
                position: static !important;
            }
            .print-page {
                width: 297mm !important;
                height: 210mm !important;
                page-break-after: always !important;
                break-after: page !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                position: relative !important;
                overflow: hidden !important;
                box-sizing: border-box !important;
                margin: 0 !important;
                background-color: #f5f3e7 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-page:last-child {
                page-break-after: avoid !important;
                break-after: avoid !important;
            }
            .print-page-front {
                position: relative !important;
            }
            .print-bg {
                position: absolute; inset: 0; width: 100%; height: 100%;
                object-fit: fill; z-index: 1; pointer-events: none;
            }
            .print-name {
                position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%);
                width: 50%; text-align: center; white-space: nowrap; z-index: 10;
                font-family: 'Montserrat', sans-serif; font-weight: 800; color: #0f172a;
                font-size: 26pt; line-height: 1.25; text-transform: capitalize;
            }
            .print-qr {
                position: absolute; left: 43.7%; bottom: 11.6%; width: 12.5%; height: 17.7%;
                display: flex; align-items: center; justify-content: center; z-index: 20;
                background: #fff; border-radius: 4mm; padding: 3mm; box-sizing: border-box;
            }
            .print-qr img {
                width: 100%; height: 100%; object-fit: contain; display: block;
            }
            .print-stamp-cover {
                position: absolute; bottom: 8%; width: 28%; height: 22%;
                background: rgba(245, 243, 231, 0.94); border: 2px dashed #f59e0b;
                border-radius: 3mm; display: flex; flex-direction: column;
                align-items: center; justify-content: center; text-align: center;
                padding: 2%; box-sizing: border-box; z-index: 25;
            }
            .print-stamp-cover.left { left: 8%; }
            .print-stamp-cover.right { right: 8%; }

            /* Back Page Print (Tabel Nilai) */
            .print-page-back {
                padding: 31pt 51pt 0 !important;
                background: #f5f3e7 url('uploads/Certificate/Sertifikat_Belakang.png') center / 100% 100% no-repeat !important;
                font-family: 'Poppins', sans-serif !important;
                font-size: 11pt !important;
                line-height: 17pt !important;
                color: #000 !important;
            }
            .print-page-back .judul { font-size: 20pt; line-height: 30pt; font-weight: 700; text-align: center; }
            .print-page-back .data-siswa { margin-top: 20pt; display: grid; row-gap: 1pt; }
            .print-page-back .data-siswa .baris { display: grid; grid-template-columns: 220pt 20pt 1fr; min-height: 17pt; }
            .print-page-back .data-siswa .label { font-weight: 500; }
            .print-page-back .nilai { width: 100%; margin-top: 14pt; border-collapse: collapse; table-layout: fixed; border: 1px solid #000; }
            .print-page-back .nilai col.no { width: 28pt; }
            .print-page-back .nilai col.skor { width: 41pt; }
            .print-page-back .nilai th, .print-page-back .nilai td { border: 1px solid #000; padding: 0 6pt; vertical-align: middle; }
            .print-page-back .nilai th { font-weight: 500; text-align: center; }
            .print-page-back .nilai td { height: 21pt; }
            .print-page-back .nilai td.no, .print-page-back .nilai td.skor { text-align: center; padding: 0; }
            .print-page-back .nilai thead tr.judul-tabel th { height: 23pt; }
            .print-page-back .nilai thead tr.aspek th { height: 22pt; }
            .print-page-back .nilai tfoot td.rata { height: 22pt; text-align: center; }
            .print-page-back .nilai tfoot td.kumulatif { height: 71pt; padding: 0; }
            .print-page-back .hasil { font-weight: 500; }
            .print-page-back .rumus { display: flex; justify-content: center; align-items: flex-start; gap: 4pt; font-weight: 500; }
            .print-page-back .pecahan { display: flex; flex-direction: column; align-items: center; font-weight: 400; }
            .print-page-back .pembilang { border-bottom: 1px solid #000; padding: 0 2pt; }
            .print-page-back .bawah { margin-top: 14pt; padding-right: 24pt; display: flex; justify-content: space-between; align-items: flex-start; }
            .print-page-back .judul-ketentuan { font-weight: 500; margin-bottom: 4pt; }
            .print-page-back .predikat { width: 190pt; border-collapse: collapse; table-layout: fixed; font-size: 9pt; line-height: 12pt; }
            .print-page-back .predikat th { font-weight: 500; padding: 1pt 0; border-top: 1px solid #000; border-bottom: 1px solid #000; }
            .print-page-back .predikat td { padding: 0; text-align: center; }
            .print-page-back .predikat tbody tr:first-child td { padding-top: 1pt; }
            .print-page-back .ttd p { line-height: 17pt; }
            .print-page-back .ttd .ruang { height: 44pt; }
            .print-page-back .ttd .nama { font-weight: 500; text-decoration: underline; }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet"/>
</head>
<body class="bg-background min-h-screen flex flex-col font-body-md text-body-md text-on-surface">
<?php
$nav_icon      = 'school';
$nav_cta_label = 'Portal Login';
$nav_cta_href  = 'Login/login.php';
include 'partials/topnav-public.php';
?>

<!-- Main Content -->
<main id="main-content" class="flex-1 w-full max-w-container-max mx-auto px-md md:px-gutter py-xl md:py-3xl">

    <!-- ── SEARCH SECTION (initial) ── -->
    <div id="search-section" class="flex flex-col items-center justify-center min-h-[60vh]">
        <div class="w-20 h-20 rounded-full bg-primary-fixed flex items-center justify-center text-primary mb-lg">
            <span class="material-symbols-outlined filled-icon text-4xl">verified</span>
        </div>
        <h1 class="font-headline-xl text-headline-xl text-on-surface mb-md text-center">Verifikasi Sertifikat</h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant max-w-lg text-center mb-xl">
            Masukkan ID sertifikat untuk memverifikasi keasliannya melalui sistem Kedayweb.
        </p>
        <div class="w-full max-w-md flex gap-sm">
            <div class="flex-1 relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
                <input id="cert-id-input"
                       class="w-full bg-surface-container-lowest border border-outline-variant pl-10 pr-4 py-3 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all font-body-md text-body-md text-on-surface uppercase"
                       placeholder="Contoh: IS-2024-001" type="text" autocomplete="off"/>
            </div>
            <button id="verify-btn" onclick="verifyCertificate()"
                    class="bg-primary text-on-primary px-xl py-3 rounded-xl font-label-md text-label-md hover:opacity-90 transition-all active:scale-95 shadow-sm flex items-center gap-2 min-w-[100px] justify-center">
                <span id="verify-icon" class="material-symbols-outlined text-[20px]">verified_user</span>
                <span id="verify-label">Verifikasi</span>
            </button>
        </div>
        <p class="font-body-sm text-body-sm text-on-surface-variant mt-md">
            Coba ID demo: <code class="bg-surface-container-high px-2 py-1 rounded font-mono text-primary font-semibold">IS-2024-001</code>
        </p>
    </div>

    <!-- ── ERROR SECTION ── -->
    <div id="error-section" class="flex flex-col items-center justify-center min-h-[50vh] animate-in">
        <div class="w-20 h-20 rounded-full bg-error-container flex items-center justify-center mb-lg">
            <span class="material-symbols-outlined text-error text-4xl filled-icon">cancel</span>
        </div>
        <h2 class="font-headline-md text-on-surface font-bold mb-sm text-center" id="error-title">Sertifikat Tidak Ditemukan</h2>
        <p class="font-body-md text-on-surface-variant text-center max-w-sm mb-xl" id="error-msg">ID sertifikat yang Anda masukkan tidak ada dalam sistem kami.</p>
        <button onclick="resetVerification()"
                class="bg-primary text-on-primary px-xl py-3 rounded-xl font-label-md hover:opacity-90 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined">arrow_back</span> Coba Lagi
        </button>
    </div>

    <!-- ── RESULT SECTION (verified) ── -->
    <div id="result-section" class="hidden animate-in">
        <!-- Trust Badge -->
        <div class="w-full bg-surface-container-lowest rounded-2xl border border-outline-variant p-md md:p-lg mb-xl flex flex-col md:flex-row items-center justify-between gap-md soft-shadow">
            <div class="flex items-center gap-md">
                <div id="badge-icon-box" class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center">
                    <span id="badge-icon" class="material-symbols-outlined filled-icon text-primary text-3xl">verified</span>
                </div>
                <div>
                    <h1 id="badge-title" class="font-headline-lg text-primary font-bold">Sertifikat Valid</h1>
                    <p id="badge-subtitle" class="text-on-surface-variant font-body-sm mt-1">Dokumen ini terverifikasi melalui sistem Kedayweb.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-sm" id="badge-status-container">
                <span id="status-chip" class="bg-[#dcfce7] text-[#166534] px-4 py-2 rounded-full flex items-center gap-2 font-bold text-sm shadow-sm">
                    <span class="material-symbols-outlined text-[20px] filled-icon">check_circle</span>
                    <span id="status-chip-text">Status: Aktif</span>
                </span>
                <span id="approval-chip" class="bg-amber-100 text-amber-800 px-4 py-2 rounded-full flex items-center gap-2 font-bold text-sm shadow-sm hidden">
                    <span class="material-symbols-outlined text-[20px]">pending</span>
                    <span>Menunggu Approval Super Admin</span>
                </span>
            </div>
        </div>

        <!-- Bento Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
            <!-- Certificate Preview (Left) - Slider -->
            <div class="lg:col-span-7 bg-surface-container-lowest rounded-2xl border border-outline-variant overflow-hidden hover:soft-shadow transition-shadow duration-300 self-start">
                <!-- Card Header -->
                <div class="px-3 sm:px-4 py-2.5 sm:py-3 bg-surface-container-low border-b border-outline-variant flex justify-between items-center gap-2">
                    <div class="flex items-center gap-1.5 sm:gap-2 min-w-0">
                        <span id="vf-slide-label" class="text-xs sm:text-sm font-bold text-on-surface-variant uppercase tracking-wider whitespace-nowrap">Preview Sertifikat</span>
                        <!-- Dot navigation -->
                        <div class="vf-slider-dots shrink-0" style="padding:0;margin-left:4px">
                            <span class="vf-dot active" id="vf-dot0" onclick="vfGoTo(0)" title="Sertifikat Depan"></span>
                            <span class="vf-dot" id="vf-dot1" onclick="vfGoTo(1)" title="Tabel Nilai"></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 sm:gap-1.5 shrink-0">
                        <button onclick="vfSlide(-1)" id="vf-btn-prev" disabled
                                class="p-1 sm:p-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-primary hover:text-on-primary hover:border-primary transition-all disabled:opacity-25 disabled:cursor-not-allowed cursor-pointer"
                                title="Sertifikat Depan">
                            <span class="material-symbols-outlined text-[16px] sm:text-[18px]">chevron_left</span>
                        </button>
                        <button onclick="vfSlide(1)" id="vf-btn-next"
                                class="p-1 sm:p-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-primary hover:text-on-primary hover:border-primary transition-all disabled:opacity-25 disabled:cursor-not-allowed cursor-pointer"
                                title="Tabel Nilai">
                            <span class="material-symbols-outlined text-[16px] sm:text-[18px]">chevron_right</span>
                        </button>
                        <button onclick="openDownloadModal()"
                                class="text-primary hover:bg-primary-container hover:text-on-primary-container px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg transition-colors flex items-center gap-1 text-xs sm:text-sm font-semibold ml-0.5 sm:ml-1 whitespace-nowrap cursor-pointer" title="Cetak / Simpan PDF">
                            <span class="material-symbols-outlined text-[18px] sm:text-[20px]">download</span> <span class="hidden sm:inline">Unduh </span><span>PDF</span>
                        </button>
                    </div>
                </div>

                <!-- Slider track -->
                <div class="vf-slider-outer">
                    <div class="vf-slider-track" id="vfSliderTrack">

                        <!-- Slide 1: Sertifikat Depan -->
                        <div class="vf-slide">
                            <div class="p-3 md:p-lg bg-surface">
                                <div id="cert-preview" class="cert-canvas" role="img" aria-label="Preview sertifikat magang">
                                    <img class="cert-bg" src="uploads/Certificate/SertifikatNew.png" alt="" draggable="false"/>
                                    <div id="cert-name" class="cert-name">—</div>
                                    <div id="cert-qr" class="cert-qr" style="display:none">
                                        <img id="cert-qr-img" alt="QR verifikasi sertifikat"/>
                                    </div>
                                    <!-- Watermark pelindung tanda tangan jika belum disetujui Super Admin -->
                                    <div id="cert-unapproved-covers" style="display:none;">
                                        <div class="cert-stamp-cover right">
                                            <span class="material-symbols-outlined cover-icon">lock</span>
                                            <span class="cover-title">Menunggu Approval</span>
                                            <span class="cover-desc">Super Admin</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- /slide-1 -->

                        <!-- Slide 2: Tabel Nilai / Halaman Belakang -->
                        <div class="vf-slide">
                            <div class="p-3 md:p-lg bg-surface">
                                <div style="width: 100%; aspect-ratio: 1.414; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                                    <iframe id="cert-back-iframe" src="about:blank" style="width: 100%; height: 100%; border: none; display: block;" title="Tabel Nilai Sertifikat"></iframe>
                                </div>
                            </div>
                        </div><!-- /slide-2 -->

                    </div><!-- /vfSliderTrack -->
                </div><!-- /vf-slider-outer -->

                <div class="px-4 py-3 bg-surface-container-low border-t border-outline-variant flex flex-wrap justify-between items-center gap-2 text-xs text-on-surface-variant">
                    <span>ID Sertifikat: <b id="cert-id-display" class="font-mono text-on-surface">—</b>
                        <span class="text-emerald-600 font-semibold ml-1">✓ Terverifikasi</span></span>
                    <span id="cert-issue-display">—</span>
                </div>
            </div>

            <!-- Right Column -->
            <div class="lg:col-span-5 flex flex-col gap-gutter">
                <!-- Profile Card -->
                <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-lg hover:soft-shadow transition-shadow duration-300 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-primary-fixed rounded-bl-full opacity-20 -z-10"></div>
                    <div class="flex items-start gap-md mb-lg">
                        <div id="avatar-circle" class="w-16 h-16 rounded-xl bg-primary flex items-center justify-center text-on-primary text-2xl font-bold flex-shrink-0">?</div>
                        <div>
                            <h2 id="res-name" class="font-headline-md text-on-surface font-bold">—</h2>
                            <p class="font-body-md text-on-surface-variant flex items-center gap-1 mt-1">
                                <span class="material-symbols-outlined text-[16px]">school</span>
                                <span id="res-university">—</span>
                            </p>
                            <p id="res-major" class="font-body-sm text-secondary mt-1">—</p>
                        </div>
                    </div>
                    <div class="border-t border-outline-variant pt-md grid grid-cols-2 gap-md">
                        <div>
                            <span class="block font-label-sm text-on-surface-variant uppercase mb-1 text-[11px]">Posisi Magang</span>
                            <span id="res-position" class="font-body-md text-on-surface font-bold">—</span>
                        </div>
                        <div class="text-right">
                            <span class="block font-label-sm text-on-surface-variant uppercase mb-1 text-[11px]">Nilai Akhir</span>
                            <span id="res-grade" class="font-headline-lg text-primary font-bold">—</span>
                        </div>
                        <div>
                            <span class="block font-label-sm text-on-surface-variant uppercase mb-1 text-[11px]">Periode Magang</span>
                            <span id="res-period" class="font-body-sm text-on-surface">—</span>
                        </div>
                        <div class="text-right">
                            <span class="block font-label-sm text-on-surface-variant uppercase mb-1 text-[11px]">Tanggal Terbit</span>
                            <span id="res-issue" class="font-body-sm text-on-surface">—</span>
                        </div>
                        <div class="col-span-2 pt-2 border-t border-outline-variant/60 flex items-center justify-between">
                            <span class="block font-label-sm text-on-surface-variant uppercase text-[11px]">Status Persetujuan TTD</span>
                            <span id="res-approval-status" class="text-xs font-bold px-2.5 py-1 rounded-full">—</span>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics Card -->
                <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-lg hover:soft-shadow transition-shadow duration-300 flex-1">
                    <h3 class="font-label-md text-on-surface uppercase tracking-wider mb-lg flex items-center gap-2 font-bold">
                        <span class="material-symbols-outlined text-primary">analytics</span>
                        Metrik Performa
                    </h3>
                    <div class="space-y-lg">
                        <!-- Technical -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-body-md text-on-surface font-medium flex items-center gap-2">
                                    <span class="material-symbols-outlined text-secondary text-[18px]">code</span>
                                    Kemampuan Teknis
                                </span>
                                <span id="score-tech-label" class="font-label-md text-primary bg-primary-fixed px-2 py-1 rounded-md font-bold">0/100</span>
                            </div>
                            <div class="w-full h-3 bg-slate-200 rounded-full overflow-hidden">
                                <div id="score-tech-bar" class="h-full bg-gradient-to-r from-primary-container to-primary rounded-full bar-fill" style="width:0%"></div>
                            </div>
                        </div>
                        <!-- Discipline -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-body-md text-on-surface font-medium flex items-center gap-2">
                                    <span class="material-symbols-outlined text-secondary text-[18px]">schedule</span>
                                    Kedisiplinan
                                </span>
                                <span id="score-disc-label" class="font-label-md text-primary bg-primary-fixed px-2 py-1 rounded-md font-bold">0/100</span>
                            </div>
                            <div class="w-full h-3 bg-slate-200 rounded-full overflow-hidden">
                                <div id="score-disc-bar" class="h-full bg-gradient-to-r from-primary-container to-primary rounded-full bar-fill" style="width:0%"></div>
                            </div>
                        </div>
                        <!-- Attitude -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-body-md text-on-surface font-medium flex items-center gap-2">
                                    <span class="material-symbols-outlined text-secondary text-[18px]">groups</span>
                                    Sikap & Kerjasama
                                </span>
                                <span id="score-att-label" class="font-label-md text-primary bg-primary-fixed px-2 py-1 rounded-md font-bold">0/100</span>
                            </div>
                            <div class="w-full h-3 bg-slate-200 rounded-full overflow-hidden">
                                <div id="score-att-bar" class="h-full bg-gradient-to-r from-primary-container to-primary rounded-full bar-fill" style="width:0%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-xl pt-md border-t border-outline-variant">
                        <button onclick="resetVerification()"
                                class="w-full bg-surface-bright border border-outline hover:border-primary hover:bg-surface-container-low text-primary font-label-md py-3 rounded-lg transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">search</span>
                            Verifikasi Sertifikat Lain
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'partials/footer.php'; ?>

<script src="qrcode.js"></script>
<script>
// Data sertifikat aktif (diisi setelah fetch)
let activeCert = null;

async function verifyCertificate() {
    const certId = document.getElementById('cert-id-input').value.trim().toUpperCase();
    if (!certId) {
        document.getElementById('cert-id-input').focus();
        return;
    }

    // Loading state
    setVerifyLoading(true);

    try {
        const res = await fetch(`certificate-api.php?action=verify&id=${encodeURIComponent(certId)}`);
        const data = await res.json();

        if (data.success) {
            activeCert = data;
            showResult(data);
        } else if (data.revoked) {
            showError('Sertifikat Dicabut', data.message);
        } else {
            showError('Sertifikat Tidak Ditemukan', data.message || 'ID sertifikat tidak ada dalam sistem.');
        }
    } catch (err) {
        showError('Terjadi Kesalahan', 'Gagal menghubungi server. Periksa koneksi internet Anda.');
    } finally {
        setVerifyLoading(false);
    }
}

function setVerifyLoading(loading) {
    const btn   = document.getElementById('verify-btn');
    const icon  = document.getElementById('verify-icon');
    const label = document.getElementById('verify-label');
    btn.disabled = loading;
    icon.textContent  = loading ? 'refresh' : 'verified_user';
    label.textContent = loading ? 'Memeriksa…' : 'Verifikasi';
    if (loading) icon.classList.add('spin'); else icon.classList.remove('spin');
}

function showResult(d) {
    // Fill cert preview (gambar latar + nama + QR)
    document.getElementById('cert-name').textContent       = d.intern_name;
    document.getElementById('cert-id-display').textContent = d.certificate_id;
    document.getElementById('cert-issue-display').textContent = 'Diterbitkan: ' + d.issue_date;
    renderCertQr(d.certificate_id);

    // Fill right panel
    document.getElementById('res-name').textContent       = d.intern_name;
    document.getElementById('res-university').textContent = d.university || 'Universitas';
    document.getElementById('res-major').textContent      = d.major || '';
    document.getElementById('res-position').textContent   = d.intern_position;
    document.getElementById('res-grade').textContent      = d.final_grade || '—';
    document.getElementById('res-period').textContent     = d.start_date + ' – ' + d.end_date;
    document.getElementById('res-issue').textContent      = d.issue_date;

    // Approval Status UI
    const isApproved = !!d.is_approved;
    const approvalChip = document.getElementById('approval-chip');
    const covers = document.getElementById('cert-unapproved-covers');
    const resApprovalStatus = document.getElementById('res-approval-status');

    if (isApproved) {
        if (approvalChip) approvalChip.classList.add('hidden');
        if (covers) covers.style.display = 'none';
        if (resApprovalStatus) {
            resApprovalStatus.className = 'text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 flex items-center gap-1';
            resApprovalStatus.innerHTML = '<span class="material-symbols-outlined text-sm">verified</span> Disetujui Super Admin';
        }
    } else {
        if (approvalChip) {
            approvalChip.classList.remove('hidden');
            approvalChip.innerHTML = '<span class="material-symbols-outlined text-[18px]">pending</span> Menunggu Approval Super Admin';
        }
        if (covers) covers.style.display = 'block';
        if (resApprovalStatus) {
            resApprovalStatus.className = 'text-xs font-bold px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 flex items-center gap-1';
            resApprovalStatus.innerHTML = '<span class="material-symbols-outlined text-sm">hourglass_empty</span> Belum Disetujui Super Admin';
        }
    }

    // Avatar initials
    const initials = d.intern_name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
    document.getElementById('avatar-circle').textContent = initials;

    // Score bars (animate after tiny delay so CSS transition kicks in)
    setTimeout(() => {
        setBar('tech',  d.score_technical);
        setBar('disc',  d.score_discipline);
        setBar('att',   d.score_attitude);
    }, 100);

    // Switch sections
    document.getElementById('search-section').style.display = 'none';
    document.getElementById('error-section').style.display  = 'none';
    document.getElementById('result-section').style.display = 'block';
    fitCertName();
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(fitCertName);
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // ── Render slide 2: Tabel Nilai / Halaman Belakang ──
    renderCertBack(d);

    // ── Siapkan data cetak / PDF (2 Halaman) ──
    populatePrintArea(d);
}

/* Perkecil font nama otomatis jika terlalu panjang (maks. 50% lebar sertifikat) */
function fitCertName() {
    const box = document.getElementById('cert-preview');
    const el  = document.getElementById('cert-name');
    let fs = 2.6;
    box.style.setProperty('--cert-fs', fs);
    while (el.scrollWidth > el.clientWidth + 1 && fs > 1.2) {
        fs = Math.round((fs - 0.1) * 10) / 10;
        box.style.setProperty('--cert-fs', fs);
    }
}

/* QR code berisi tautan verifikasi: verification.php?id=<ID> */
function renderCertQr(certId) {
    const wrap = document.getElementById('cert-qr');
    const img  = document.getElementById('cert-qr-img');
    try {
        const u = new URL(window.location.href);
        u.hash = ''; u.search = '';
        u.searchParams.set('id', certId);
        const qr = qrcode(0, 'M');
        qr.addData(u.toString());
        qr.make();
        img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(qr.createSvgTag({ cellSize: 1, margin: 0, scalable: true }));
        wrap.style.display = 'flex';
    } catch (e) {
        wrap.style.display = 'none';   // library QR gagal dimuat → sertifikat tetap tampil tanpa QR
    }
}

function setBar(key, value) {
    const short = { tech: 'tech', disc: 'disc', att: 'att' };
    document.getElementById(`score-${key}-label`).textContent = value + '/100';
    document.getElementById(`score-${key}-bar`).style.width   = value + '%';
}

function showError(title, msg) {
    document.getElementById('error-title').textContent = title;
    document.getElementById('error-msg').textContent   = msg;
    document.getElementById('search-section').style.display = 'none';
    document.getElementById('result-section').style.display = 'none';
    document.getElementById('error-section').style.display  = 'flex';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetVerification() {
    document.getElementById('search-section').style.display = 'flex';
    document.getElementById('result-section').style.display = 'none';
    document.getElementById('error-section').style.display  = 'none';
    document.getElementById('cert-id-input').value = '';
    document.getElementById('cert-id-input').focus();
    activeCert = null;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Enter key support
document.getElementById('cert-id-input').addEventListener('keydown', e => {
    if (e.key === 'Enter') verifyCertificate();
});

// Auto-verify from URL param  ?id=IS-2024-001
(function() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');
    if (id) {
        document.getElementById('cert-id-input').value = id;
        verifyCertificate();
        if (params.get('print') === '1') {
            setTimeout(() => { if (typeof openDownloadModal === 'function') openDownloadModal(); }, 800);
        }
    }
})();

// ── Modal Unduh & Cetak PDF ──
function openDownloadModal() {
    if (!activeCert) return;
    document.getElementById('download-modal').classList.remove('hidden');
}
function closeDownloadModal() {
    document.getElementById('download-modal').classList.add('hidden');
}
window.downloadCertificate = openDownloadModal;

function executePrint(mode) {
    if (!activeCert) return;
    closeDownloadModal();
    const pFront = document.getElementById('print-page-front');
    const pBack  = document.getElementById('print-page-back');
    if (!pFront || !pBack) return;

    if (mode === 'front') {
        pFront.style.display = 'block';
        pBack.style.display  = 'none';
        document.title = 'Sertifikat_Depan_' + activeCert.certificate_id;
    } else if (mode === 'back') {
        pFront.style.display = 'none';
        pBack.style.display  = 'block';
        document.title = 'Tabel_Nilai_' + activeCert.certificate_id;
    } else {
        pFront.style.display = 'block';
        pBack.style.display  = 'block';
        document.title = 'Sertifikat_Lengkap_' + activeCert.certificate_id;
    }

    setTimeout(() => {
        window.print();
        setTimeout(() => {
            pFront.style.display = '';
            pBack.style.display  = '';
            document.title = 'Sertifikat_' + activeCert.certificate_id;
        }, 500);
    }, 150);
}

// Mengisi template cetak dengan data sertifikat
function populatePrintArea(d) {
    if (!d) return;

    // Halaman Depan
    const pName = document.getElementById('print-cert-name');
    if (pName) {
        pName.textContent = d.intern_name;
        let fs = 26;
        if (d.intern_name.length > 25) fs = 20;
        if (d.intern_name.length > 35) fs = 16;
        pName.style.fontSize = fs + 'pt';
    }

    // QR Code
    const pQrImg = document.getElementById('print-cert-qr-img');
    const scQrImg = document.getElementById('cert-qr-img');
    if (pQrImg && scQrImg) {
        pQrImg.src = scQrImg.src;
    }

    // Covers
    const pCovers = document.getElementById('print-cert-covers');
    if (pCovers) {
        pCovers.style.display = d.is_approved ? 'none' : 'block';
    }

    // Halaman Belakang (Tabel Nilai)
    document.getElementById('p-nama').textContent       = d.intern_name || '';
    document.getElementById('p-jurusan').textContent    = d.major || '-';
    document.getElementById('p-waktu').textContent      = (d.start_date || '') + ' sampai ' + (d.end_date || '');
    document.getElementById('p-tgl-bwi').textContent    = 'Banyuwangi, ' + (d.issue_date || '');
    document.getElementById('p-supervisor').textContent = d.supervisor_name || 'M. Lutfi Nur Fauzi, S.Kom.';

    const nt = [d.score_discipline, d.score_discipline, d.score_discipline, d.score_attitude, d.score_attitude, d.score_attitude];
    const tek = [d.score_technical, d.score_technical, d.score_technical, d.score_technical, d.score_technical, d.score_technical];
    const labelNT = ['Kedisiplinan','Kemauan Kerja Dan Motivasi','Kerajinan','Inisiatif Dan Kreatifitas','Kerjasama Dan Tanggung Jawab','Sikap Dan Perilaku'];
    const labelT = ['Penguasaan Tools Dan Perangkat Kerja Utama','Kualitas & Ketepatan Hasil Pekerjaan','Pemahaman Brief & Penerapan Spesifikasi Tugas','Kecepatan & Ketepatan Waktu Penyelesaian Tugas','Kemampuan Penyelesaian Masalah','Laporan Hasil Kerja'];

    const fmt = n => n == null ? '' : new Intl.NumberFormat('id-ID', {maximumFractionDigits:2}).format(n);
    const rataNT = nt.reduce((a,b)=>a+b,0) / nt.length;
    const rataTek = tek.reduce((a,b)=>a+b,0) / tek.length;
    const kumul = (rataNT + rataTek) / 2;

    let rowsHtml = '';
    for (let i = 0; i < 6; i++) {
        rowsHtml += `<tr>
            <td class="no">${i+1}.</td><td>${labelNT[i]}</td><td class="skor">${nt[i]}</td>
            <td class="no">${i+1}.</td><td>${labelT[i]}</td><td class="skor">${tek[i]}</td>
        </tr>`;
    }
    document.getElementById('p-isi-nilai').innerHTML = rowsHtml;
    document.getElementById('p-rata-non').textContent = fmt(rataNT);
    document.getElementById('p-rata-tek').textContent = fmt(rataTek);
    document.getElementById('p-kumulatif').textContent = fmt(kumul);
}

/* ── Slider ── */
(function() {
    const TOTAL = 2;
    let cur = 0;
    const SLIDES = [
        { title: 'Preview Sertifikat', extra: '' },
        { title: 'Tabel Nilai', extra: '(Halaman Belakang)' }
    ];

    function vfUpdate() {
        const track = document.getElementById('vfSliderTrack');
        const lbl   = document.getElementById('vf-slide-label');
        const bp    = document.getElementById('vf-btn-prev');
        const bn    = document.getElementById('vf-btn-next');
        if (!track) return;
        track.style.transform = 'translateX(-' + (cur * 100) + '%)';
        if (lbl) {
            const s = SLIDES[cur];
            lbl.innerHTML = s.extra
                ? `${s.title}<span class="hidden sm:inline normal-case text-slate-500 font-normal text-xs ml-1">${s.extra}</span>`
                : s.title;
        }
        if (bp) bp.disabled = cur === 0;
        if (bn) bn.disabled = cur === TOTAL - 1;
        for (let i = 0; i < TOTAL; i++) {
            const d = document.getElementById('vf-dot' + i);
            if (d) d.classList.toggle('active', i === cur);
        }
    }

    window.vfSlide = function(dir) { cur = Math.max(0, Math.min(TOTAL - 1, cur + dir)); vfUpdate(); };
    window.vfGoTo  = function(idx) { cur = idx; vfUpdate(); };
    vfUpdate();
})();

/* ── Render halaman belakang sertifikat (Tabel Nilai PKL) ── */
function renderCertBack(d) {
    const iframe = document.getElementById('cert-back-iframe');
    if (iframe && d && d.certificate_id) {
        iframe.src = 'tabel-nilai-viewer.php?id=' + encodeURIComponent(d.certificate_id);
    }
}
</script>

<!-- Modal Pilihan Unduh/Cetak -->
<div id="download-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs hidden no-print" onclick="if(event.target===this)closeDownloadModal()">
    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-md w-full p-6 shadow-2xl relative animate-in fade-in duration-200">
        <button onclick="closeDownloadModal()" class="absolute top-4 right-4 text-on-surface-variant hover:text-on-surface p-1 rounded-lg cursor-pointer">
            <span class="material-symbols-outlined text-xl">close</span>
        </button>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-primary-container text-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">picture_as_pdf</span>
            </div>
            <div>
                <h3 class="font-headline-sm font-bold text-on-surface text-base md:text-lg">Unduh / Cetak Sertifikat</h3>
                <p class="text-xs text-on-surface-variant">Pilih format halaman yang ingin dicetak atau disimpan ke PDF</p>
            </div>
        </div>

        <div class="space-y-2.5 my-4">
            <button onclick="executePrint('all')"
                    class="w-full text-left p-3.5 rounded-xl border border-primary/30 bg-primary/5 hover:bg-primary/10 transition-all flex items-center justify-between group cursor-pointer">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary text-2xl">auto_stories</span>
                    <div>
                        <div class="font-bold text-sm text-primary flex items-center gap-1.5">
                            <span>Lengkap (2 Halaman)</span>
                            <span class="text-[10px] bg-primary text-white px-2 py-0.5 rounded-full font-semibold uppercase tracking-wider">Rekomendasi</span>
                        </div>
                        <p class="text-xs text-on-surface-variant">Halaman Depan (Sertifikat) + Halaman Belakang (Tabel Nilai)</p>
                    </div>
                </div>
                <span class="material-symbols-outlined text-primary text-lg group-hover:translate-x-1 transition-transform">chevron_right</span>
            </button>

            <button onclick="executePrint('front')"
                    class="w-full text-left p-3.5 rounded-xl border border-outline-variant hover:bg-surface-container-high transition-all flex items-center justify-between group cursor-pointer">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-on-surface-variant text-2xl">badge</span>
                    <div>
                        <div class="font-bold text-sm text-on-surface">Halaman Depan Saja</div>
                        <p class="text-xs text-on-surface-variant">Hanya lembar utama sertifikat magang (1 halaman)</p>
                    </div>
                </div>
                <span class="material-symbols-outlined text-on-surface-variant text-lg group-hover:translate-x-1 transition-transform">chevron_right</span>
            </button>

            <button onclick="executePrint('back')"
                    class="w-full text-left p-3.5 rounded-xl border border-outline-variant hover:bg-surface-container-high transition-all flex items-center justify-between group cursor-pointer">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-on-surface-variant text-2xl">table_chart</span>
                    <div>
                        <div class="font-bold text-sm text-on-surface">Halaman Belakang Saja (Tabel Nilai)</div>
                        <p class="text-xs text-on-surface-variant">Hanya lembar daftar nilai PKL lengkap (1 halaman)</p>
                    </div>
                </div>
                <span class="material-symbols-outlined text-on-surface-variant text-lg group-hover:translate-x-1 transition-transform">chevron_right</span>
            </button>
        </div>

        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-start gap-2">
            <span class="material-symbols-outlined text-amber-600 text-base shrink-0 mt-0.5">info</span>
            <div>
                <strong>Tips Cetak PDF:</strong> Pada jendela print browser, pilih <em>Destination / Printer: <strong>Save as PDF</strong></em> dan <em>Layout: <strong>Landscape</strong></em>.
            </div>
        </div>
    </div>
</div>

<!-- ═══ ELEMEN CETAK A4 LANDSCAPE (HANYA TAMPIL SAAT PRINT) ═══ -->
<div id="print-area">
    <!-- Halaman 1: Sertifikat Depan -->
    <div class="print-page print-page-front" id="print-page-front">
        <img class="print-bg" src="uploads/Certificate/SertifikatNew.png" alt="" />
        <div id="print-cert-name" class="print-name">—</div>
        <div class="print-qr">
            <img id="print-cert-qr-img" alt="QR Code" />
        </div>
        <div id="print-cert-covers" style="display:none;">
            <div class="print-stamp-cover left">
                <span class="material-symbols-outlined" style="font-size:24pt;color:#d97706">lock</span>
                <span style="font-size:12pt;font-weight:bold;color:#92400e">Menunggu Approval</span>
                <span style="font-size:9pt;color:#b45309">Tanda Tangan Belum Sah</span>
            </div>
            <div class="print-stamp-cover right">
                <span class="material-symbols-outlined" style="font-size:24pt;color:#d97706">lock</span>
                <span style="font-size:12pt;font-weight:bold;color:#92400e">Menunggu Approval</span>
                <span style="font-size:9pt;color:#b45309">Super Admin</span>
            </div>
        </div>
    </div>

    <!-- Halaman 2: Tabel Nilai PKL -->
    <div class="print-page print-page-back" id="print-page-back">
        <h1 class="judul">DAFTAR NILAI PRAKTIK KERJA LAPANGAN (PKL)</h1>

        <section class="data-siswa">
            <div class="baris"><span class="label">NAMA SISWA</span><span>:</span><span id="p-nama"></span></div>
            <div class="baris"><span class="label">JURUSAN</span><span>:</span><span id="p-jurusan"></span></div>
            <div class="baris"><span class="label">TEMPAT PRAKERIN</span><span>:</span><span>CV. Kedayweb (IT &amp; Multimedia Agency)</span></div>
            <div class="baris"><span class="label">ALAMAT TEMPAT PRAKERIN</span><span>:</span><span>Banyuwangi, Jawa Timur</span></div>
            <div class="baris"><span class="label">WAKTU PELAKSANAAN</span><span>:</span><span id="p-waktu"></span></div>
        </section>

        <table class="nilai">
            <colgroup>
                <col class="no"><col><col class="skor">
                <col class="no"><col><col class="skor">
            </colgroup>
            <thead>
                <tr class="judul-tabel"><th colspan="6">KOMPONEN YANG DINILAI</th></tr>
                <tr class="aspek">
                    <th colspan="3">ASPEK NON TEKNIS</th>
                    <th colspan="3">ASPEK TEKNIS</th>
                </tr>
            </thead>
            <tbody id="p-isi-nilai"></tbody>
            <tfoot>
                <tr>
                    <td class="rata" colspan="3">Nilai Rata-Rata Aspek Non Teknis = <span class="hasil" id="p-rata-non"></span></td>
                    <td class="rata" colspan="3">Nilai Rata-Rata Aspek Teknis = <span class="hasil" id="p-rata-tek"></span></td>
                </tr>
                <tr>
                    <td class="kumulatif" colspan="6">
                        <div class="rumus">
                            <span>Nilai Kumulatif =</span>
                            <span class="pecahan">
                                <span class="pembilang">(Nilai Rata-Rata Non Teknis + Nilai Rata-Rata Teknis)</span>
                                <span>2</span>
                            </span>
                            <span>= <span class="hasil" id="p-kumulatif"></span></span>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>

        <section class="bawah">
            <div>
                <p class="judul-ketentuan">KETENTUAN:</p>
                <table class="predikat">
                    <colgroup><col style="width:60pt"><col style="width:60pt"><col style="width:70pt"></colgroup>
                    <thead><tr><th>Nilai</th><th>Kategori</th><th>Keterangan</th></tr></thead>
                    <tbody>
                        <tr><td>90-100</td><td>A</td><td>Sangat Baik</td></tr>
                        <tr><td>80-89</td><td>B</td><td>Baik</td></tr>
                        <tr><td>75-79</td><td>C</td><td>Cukup</td></tr>
                        <tr><td>&lt;=74</td><td>D</td><td>Kurang</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="ttd">
                <p id="p-tgl-bwi"></p>
                <p>Founder &amp; Director Kedayweb</p>
                <div class="ruang"></div>
                <p class="nama" id="p-supervisor"></p>
            </div>
        </section>
    </div>
</div>
</body>
</html>
