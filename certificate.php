<?php
require_once __DIR__ . '/session.php';
require_login();
require_once __DIR__ . '/Login/koneksi.php';

// Auto-sync certificates for intern users
if ($conn) {
    sync_all_intern_certificates($conn);
}

// Master status check
$master_enabled = 1;
if ($conn) {
    $res_m = @mysqli_query($conn, "SELECT certificate_enabled FROM attendance_settings WHERE id = 1 LIMIT 1");
    if ($res_m && $r_m = mysqli_fetch_assoc($res_m)) {
        $master_enabled = intval($r_m['certificate_enabled'] ?? 1);
    }
}

$user_id  = intval($_SESSION['user_id'] ?? 0);
$username = (string)($_SESSION['username'] ?? '');

$cert = null;
if ($conn) {
    if ($user_id > 0) {
        $stmt = mysqli_prepare($conn, "SELECT *, DATE_FORMAT(start_date, '%d %M %Y') AS start_fmt, DATE_FORMAT(end_date, '%d %M %Y') AS end_fmt, DATE_FORMAT(issue_date, '%d %M %Y') AS issue_fmt FROM certificates WHERE user_id = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $cert = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);
        }
    }
    if (!$cert && !empty($username)) {
        $stmt = mysqli_prepare($conn, "SELECT *, DATE_FORMAT(start_date, '%d %M %Y') AS start_fmt, DATE_FORMAT(end_date, '%d %M %Y') AS end_fmt, DATE_FORMAT(issue_date, '%d %M %Y') AS issue_fmt FROM certificates WHERE intern_name = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $cert = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);
        }
    }
}

$avg_score = 0;
$is_approved = 0;
if ($cert) {
    $avg_score = round(($cert['score_technical'] + $cert['score_discipline'] + $cert['score_attitude']) / 3);
    $is_approved = intval($cert['is_approved'] ?? 0);
    if ($master_enabled && !empty($cert['certificate_id'])) {
        header('Location: verification.php?id=' . urlencode($cert['certificate_id']));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <title>Sertifikat Magang Saya – Kedayweb</title>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&family=Cinzel:wght@700&display=swap" rel="stylesheet"/>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="shared-config.js"></script>
    <link rel="stylesheet" href="style.css"/>
    <style>
        .filled-icon { font-variation-settings:'FILL' 1,'wght' 400,'GRAD' 0,'opsz' 24; }
        .cert-border {
            border: 12px double #1e3a8a;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        /* ── Slider wrapper ── */
        .cert-slider-outer {
            position: relative;
            overflow: hidden;
            width: 100%;
            max-width: 56rem;
            margin: 0 auto;
        }
        .cert-slider-track {
            display: flex;
            transition: transform .45s cubic-bezier(.4,0,.2,1);
            will-change: transform;
        }
        .cert-slide {
            flex: 0 0 100%;
            width: 100%;
        }

        /* ── Dot indicators ── */
        .slider-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 12px;
        }
        .slider-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #cbd5e1;
            cursor: pointer;
            transition: background .3s, transform .3s;
        }
        .slider-dot.active {
            background: #1e3a8a;
            transform: scale(1.3);
        }

        /* ── Tabel Nilai (slide-2) ── */
        .cert-back-canvas {
            width: 100%;
            aspect-ratio: 297 / 210;
            position: relative;
            overflow: hidden;
            background: #f5f3e7 url('uploads/Certificate/Sertifikat_Belakang.png') center / 100% 100% no-repeat;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,.25);
            container-type: inline-size;
            font-family: 'Poppins', sans-serif;
            color: #000;
        }
        .cert-back-inner {
            position: absolute;
            inset: 0;
            padding: 3.3cqw 5.4cqw 0;
            display: flex;
            flex-direction: column;
        }
        .cb-judul {
            font-size: 2.1cqw;
            font-weight: 700;
            text-align: center;
            line-height: 1.35;
        }
        .cb-data {
            margin-top: 2cqw;
            display: grid;
            row-gap: .1cqw;
            font-size: 1.08cqw;
            line-height: 1.65cqw;
        }
        .cb-baris {
            display: grid;
            grid-template-columns: 23cqw 2cqw 1fr;
        }
        .cb-label { font-weight: 500; }
        .cb-table {
            width: 100%;
            margin-top: 1.4cqw;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid #000;
            font-size: 1.06cqw;
            line-height: 1.4;
        }
        .cb-table col.no   { width: 2.7cqw; }
        .cb-table col.skor { width: 3.9cqw; }
        .cb-table th, .cb-table td {
            border: 1px solid #000;
            padding: 0 .6cqw;
            vertical-align: middle;
        }
        .cb-table th { font-weight: 500; text-align: center; }
        .cb-table td { height: 2.05cqw; }
        .cb-table td.no, .cb-table td.skor { text-align: center; padding: 0; }
        .cb-table thead tr.judul-tb th { height: 2.2cqw; }
        .cb-table thead tr.aspek  th { height: 2.1cqw; }
        .cb-tfoot-rata td { height: 2.1cqw; text-align: center; }
        .cb-tfoot-kum td  { height: 6.8cqw; padding: 0; }
        .cb-hasil { font-weight: 500; }
        .cb-rumus {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: .4cqw;
            font-weight: 500;
            font-size: 1.05cqw;
        }
        .cb-pecahan {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-weight: 400;
        }
        .cb-pembilang { border-bottom: 1px solid #000; padding: 0 .2cqw; }
        .cb-bawah {
            margin-top: 1.4cqw;
            padding-right: 2.4cqw;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 1.05cqw;
        }
        .cb-predikat {
            width: 19cqw;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: .87cqw;
            line-height: 1.15cqw;
        }
        .cb-predikat th {
            font-weight: 500;
            padding: .1cqw 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }
        .cb-predikat td { padding: 0; text-align: center; }
        .cb-ttd p { line-height: 1.65cqw; font-size: 1.05cqw; }
        .cb-ttd .ruang { height: 4.2cqw; }
        .cb-ttd .nama { font-weight: 500; text-decoration: underline; }

        @media print {
            body { background: white !important; color: black !important; }
            aside, header, footer, .no-print { display: none !important; }
            main { margin: 0 !important; padding: 0 !important; width: 100% !important; }
            .cert-container { border: 8px double #1e3a8a !important; box-shadow: none !important; }
            .cert-slider-outer { max-width: 100%; }
            .cert-slider-track { transform: none !important; }
            .cert-slide { display: block; page-break-after: always; }
            .slider-nav, .slider-dots, .no-print { display: none !important; }
            .cert-back-canvas { box-shadow: none; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet"/>
</head>
<body class="bg-background text-on-surface font-body-md flex h-screen overflow-hidden">

<?php
$active = 'certificate';
include 'partials/sidebar-intern.php';
?>

<main class="flex-1 flex flex-col md:ml-[16.5rem] h-screen overflow-y-auto">
    <!-- Top Header -->
    <header class="w-full h-20 bg-surface-container-lowest border-b border-outline-variant sticky top-0 flex justify-between items-center px-6 z-10 shrink-0 no-print">
        <h2 class="font-headline-lg font-bold text-on-surface flex items-center gap-2">
            <button onclick="toggleMobileSidebar()" type="button" class="md:hidden text-on-surface hover:text-primary p-1 rounded-lg">
                <span class="material-symbols-outlined text-2xl">menu</span>
            </button>
            <span class="material-symbols-outlined text-primary filled-icon">workspace_premium</span>
            <span>Sertifikat Magang Saya</span>
        </h2>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-sm p-1.5 px-3 rounded-full border border-outline-variant bg-surface-bright shadow-2xs">
                <div class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container overflow-hidden shrink-0">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">account_circle</span>
                </div>
                <span class="hidden sm:inline-block font-label-md"><?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="logout.php" class="text-error hover:text-red-700 hover:bg-red-50 p-1.5 rounded-full transition-colors flex items-center justify-center" title="Keluar">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                </a>
            </div>
        </div>
    </header>

    <div class="p-6 space-y-6 flex-1">
        <?php if (!$master_enabled): ?>
            <!-- Master System Disabled Alert -->
            <div class="bg-amber-50 border border-amber-200 text-amber-900 p-6 rounded-2xl shadow-sm flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-700 shrink-0">
                    <span class="material-symbols-outlined text-2xl">error_outline</span>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Sistem Sertifikat Nonaktif</h3>
                    <p class="text-sm text-amber-800 mt-1">Sistem penerbitan dan verifikasi sertifikat magang saat ini sedang nonaktif sementara oleh Administrator. Silakan hubungi pembimbing atau admin jika membutuhkan bantuan.</p>
                </div>
            </div>
        <?php elseif (!$cert): ?>
            <!-- No Certificate Found -->
            <div class="bg-surface-container-lowest border border-outline-variant p-10 rounded-2xl text-center space-y-4 max-w-lg mx-auto shadow-sm">
                <div class="w-20 h-20 rounded-full bg-primary-container text-primary flex items-center justify-center mx-auto">
                    <span class="material-symbols-outlined text-4xl filled-icon">pending_actions</span>
                </div>
                <h3 class="font-bold text-xl text-on-surface">Sertifikat Belum Diterbitkan</h3>
                <p class="text-sm text-on-surface-variant">Sertifikat magang Anda sedang dalam proses verifikasi atau pembuatan oleh tim admin. Silakan periksa kembali secara berkala.</p>
            </div>
        <?php else: ?>
            <!-- Action Toolbar -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-surface-container-lowest p-4 rounded-2xl border border-outline-variant shadow-xs no-print">
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $cert['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'; ?>">
                        Status: <?php echo $cert['status'] === 'active' ? 'AKTIF / TERDAFTAR' : 'DICABUT / TIDAK BERLAKU'; ?>
                    </span>
                    <span class="text-xs text-on-surface-variant font-medium">ID: <strong class="font-mono text-primary"><?php echo htmlspecialchars($cert['certificate_id']); ?></strong></span>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <a href="verification.php?id=<?php echo urlencode($cert['certificate_id']); ?>" target="_blank"
                       class="flex-1 sm:flex-none px-4 py-2 bg-surface-container-high hover:bg-surface-container-highest text-primary border border-outline-variant rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-xs">
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                        <span>Verifikasi Publik</span>
                    </a>
                    <button onclick="window.print()"
                            class="flex-1 sm:flex-none px-4 py-2 bg-primary text-on-primary rounded-xl text-xs font-bold hover:bg-primary-container transition-all flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
                        <span class="material-symbols-outlined text-sm">print</span>
                        <span>Cetak / Simpan PDF</span>
                    </button>
                </div>
            </div>

            <!-- ═══ SLIDER WRAPPER ═══ -->
            <div class="cert-slider-outer" id="certSliderOuter">

                <!-- Slide navigation label -->
                <div class="flex items-center justify-between mb-3 no-print">
                    <p class="text-xs text-on-surface-variant font-semibold uppercase tracking-wide flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base filled-icon text-primary">workspace_premium</span>
                        <span id="slideLabel">Sertifikat Depan</span>
                    </p>
                    <div class="flex items-center gap-2">
                        <button id="btnPrev" onclick="certSlide(-1)"
                                disabled
                                class="p-2 rounded-xl border border-outline-variant bg-surface-container-low text-on-surface-variant hover:bg-primary hover:text-on-primary hover:border-primary transition-all disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer"
                                title="Sertifikat Depan">
                            <span class="material-symbols-outlined text-xl">chevron_left</span>
                        </button>
                        <button id="btnNext" onclick="certSlide(1)"
                                class="p-2 rounded-xl border border-outline-variant bg-surface-container-low text-on-surface-variant hover:bg-primary hover:text-on-primary hover:border-primary transition-all disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer"
                                title="Tabel Nilai / Sertifikat Belakang">
                            <span class="material-symbols-outlined text-xl">chevron_right</span>
                        </button>
                    </div>
                </div>

                <!-- Track -->
                <div class="cert-slider-track" id="certSliderTrack">

                    <!-- ── SLIDE 1: Sertifikat Depan ── -->
                    <div class="cert-slide" id="certSlide0">
                        <div class="cert-container cert-border rounded-3xl p-8 md:p-12 shadow-xl relative overflow-hidden">
                            <!-- Watermark -->
                            <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none select-none">
                                <span class="material-symbols-outlined text-[400px]">workspace_premium</span>
                            </div>

                            <div class="relative z-10 text-center space-y-6">
                                <!-- Brand Header -->
                                <div class="flex flex-col items-center justify-center gap-1">
                                    <div class="w-14 h-14 rounded-2xl bg-blue-900 text-amber-400 flex items-center justify-center shadow-md mb-2">
                                        <span class="material-symbols-outlined text-3xl filled-icon">school</span>
                                    </div>
                                    <h2 class="text-2xl font-black tracking-wider text-blue-950 uppercase font-headline-lg">KEDAYWEB INTERNSHIP</h2>
                                    <p class="text-xs tracking-widest text-slate-500 uppercase font-semibold">Teknologi Informasi &amp; Pengembang Software</p>
                                </div>
                                <div class="w-24 h-1 bg-gradient-to-r from-amber-400 via-blue-900 to-amber-400 mx-auto rounded-full my-2"></div>
                                <!-- Certificate Title -->
                                <div class="space-y-1">
                                    <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 font-headline-xl tracking-tight uppercase" style="font-family:'Cinzel',serif">SERTIFIKAT MAGANG</h1>
                                    <p class="text-xs md:text-sm text-slate-500 italic font-medium">Certificate of Internship Completion</p>
                                    <p class="text-xs font-mono text-blue-800 font-bold mt-1">No: <?php echo htmlspecialchars($cert['certificate_id']); ?></p>
                                </div>
                                <p class="text-xs text-slate-600 font-medium">Diberikan secara resmi kepada:</p>
                                <!-- Recipient Name -->
                                <div class="py-2 border-b-2 border-slate-300 max-w-lg mx-auto">
                                    <h3 class="text-2xl md:text-3xl font-extrabold text-blue-950 uppercase tracking-wide"><?php echo htmlspecialchars($cert['intern_name']); ?></h3>
                                </div>
                                <!-- Details -->
                                <p class="text-xs md:text-sm text-slate-700 max-w-2xl mx-auto leading-relaxed">
                                    Telah dengan sukses menyelesaikan Program Magang Kerja Industri sebagai
                                    <strong class="text-blue-900 font-bold"><?php echo htmlspecialchars($cert['intern_position']); ?></strong>
                                    <?php if (!empty($cert['university']) && $cert['university'] !== '-'): ?>
                                        utusan dari <strong class="text-slate-900"><?php echo htmlspecialchars($cert['university']); ?></strong>
                                        <?php if (!empty($cert['major']) && $cert['major'] !== '-'): ?> (Jurusan <?php echo htmlspecialchars($cert['major']); ?>)<?php endif; ?>
                                    <?php endif; ?>
                                    terhitung mulai tanggal <span class="font-bold text-slate-900"><?php echo htmlspecialchars($cert['start_fmt']); ?></span>
                                    s/d <span class="font-bold text-slate-900"><?php echo htmlspecialchars($cert['end_fmt']); ?></span>.
                                </p>
                                <!-- Grades Grid -->
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-200 max-w-2xl mx-auto my-4 text-left">
                                    <div class="p-2.5 bg-white rounded-xl border border-slate-100 shadow-2xs">
                                        <p class="text-[10px] uppercase font-bold text-slate-400">Teknis</p>
                                        <p class="text-lg font-extrabold text-slate-800"><?php echo (int)$cert['score_technical']; ?> <span class="text-[11px] text-slate-400 font-normal">/100</span></p>
                                    </div>
                                    <div class="p-2.5 bg-white rounded-xl border border-slate-100 shadow-2xs">
                                        <p class="text-[10px] uppercase font-bold text-slate-400">Disiplin</p>
                                        <p class="text-lg font-extrabold text-slate-800"><?php echo (int)$cert['score_discipline']; ?> <span class="text-[11px] text-slate-400 font-normal">/100</span></p>
                                    </div>
                                    <div class="p-2.5 bg-white rounded-xl border border-slate-100 shadow-2xs">
                                        <p class="text-[10px] uppercase font-bold text-slate-400">Sikap</p>
                                        <p class="text-lg font-extrabold text-slate-800"><?php echo (int)$cert['score_attitude']; ?> <span class="text-[11px] text-slate-400 font-normal">/100</span></p>
                                    </div>
                                    <div class="p-2.5 bg-blue-900 text-white rounded-xl shadow-xs">
                                        <p class="text-[10px] uppercase font-bold text-amber-300">Grade Akhir</p>
                                        <p class="text-lg font-black text-white"><?php echo htmlspecialchars($cert['final_grade'] ?: 'A'); ?> <span class="text-[10px] text-amber-300 font-bold">(Rata-rata <?php echo $avg_score; ?>)</span></p>
                                    </div>
                                </div>
                                <!-- Signatures -->
                                <?php if ($is_approved): ?>
                                <div class="grid grid-cols-2 gap-6 pt-6 border-t border-slate-200 max-w-2xl mx-auto items-end text-xs">
                                    <div class="text-center">
                                        <p class="text-slate-500 mb-8">Diterbitkan Pada: <span class="font-bold text-slate-800"><?php echo htmlspecialchars($cert['issue_fmt']); ?></span></p>
                                        <div class="w-24 h-12 mx-auto flex items-center justify-center opacity-80">
                                            <span class="font-bold text-blue-900 text-lg border-b-2 border-blue-900 pb-0.5">KEDAYWEB</span>
                                        </div>
                                        <p class="font-bold text-slate-900 mt-1">Tim Admin Kedayweb</p>
                                        <p class="text-[11px] text-slate-500">Penyelenggara Program</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-slate-500 mb-8">Pembimbing Magang</p>
                                        <div class="w-28 h-12 mx-auto flex items-center justify-center">
                                            <span class="font-serif italic font-extrabold text-blue-950 text-xl border-b border-slate-400 px-3 pb-1"><?php echo htmlspecialchars($cert['supervisor_name'] ?: 'M. Lutfi Nur Fauzi, S.Kom.'); ?></span>
                                        </div>
                                        <p class="font-bold text-slate-900 mt-1"><?php echo htmlspecialchars($cert['supervisor_name'] ?: 'M. Lutfi Nur Fauzi, S.Kom.'); ?></p>
                                        <p class="text-[11px] text-slate-500">Supervisor / Evaluator</p>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="pt-6 border-t border-amber-200 max-w-2xl mx-auto">
                                    <div class="flex items-center justify-center gap-3 p-4 rounded-xl bg-amber-50 border border-amber-200">
                                        <span class="material-symbols-outlined text-amber-600 text-2xl shrink-0">pending_actions</span>
                                        <div class="text-center">
                                            <p class="font-bold text-amber-900 text-sm">Menunggu Persetujuan Super Admin</p>
                                            <p class="text-amber-700 text-xs mt-0.5">Tanda tangan dan stempel resmi akan muncul setelah Super Admin menyetujui sertifikat ini.</p>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div><!-- /slide-1 -->

                    <!-- ── SLIDE 2: Sertifikat Belakang + Tabel Nilai ── -->
                    <?php
                        // Skor yang tersedia di DB
                        $skor_disiplin  = (int)$cert['score_discipline'];
                        $skor_teknis    = (int)$cert['score_technical'];
                        $skor_sikap     = (int)$cert['score_attitude'];

                        // Distribusi skor ke 6 komponen non-teknis & 6 teknis
                        $nonTeknis = [$skor_disiplin, $skor_disiplin, $skor_disiplin, $skor_sikap, $skor_sikap, $skor_sikap];
                        $teknis    = [$skor_teknis,   $skor_teknis,   $skor_teknis,   $skor_teknis, $skor_teknis, $skor_teknis];

                        $labelNonTeknis = ['Kedisiplinan','Kemauan Kerja Dan Motivasi','Kerajinan','Inisiatif Dan Kreatifitas','Kerjasama Dan Tanggung Jawab','Sikap Dan Perilaku'];
                        $labelTeknis    = ['Penguasaan Tools Dan Perangkat Kerja Utama','Kualitas & Ketepatan Hasil Pekerjaan','Pemahaman Brief & Penerapan Spesifikasi Tugas','Kecepatan & Ketepatan Waktu Penyelesaian Tugas','Kemampuan Penyelesaian Masalah','Laporan Hasil Kerja'];

                        $rataaNonTeknis = round(array_sum($nonTeknis) / count($nonTeknis), 2);
                        $rataaTeknis    = round(array_sum($teknis) / count($teknis), 2);
                        $kumulatif      = round(($rataaNonTeknis + $rataaTeknis) / 2, 2);

                        $waktu = $cert['start_fmt'] . ' sampai ' . $cert['end_fmt'];
                        $tempatTanggal = 'Banyuwangi, ' . (date('d F Y', strtotime($cert['issue_date'] ?? $cert['end_date'] ?? 'now')));
                        $penandatangan = htmlspecialchars($cert['supervisor_name'] ?: 'M. Lutfi Nur Fauzi, S.Kom.');
                        $jabatan = 'Founder &amp; Director Kedayweb';

                        $ketentuan = [
                            ['90–100', 'A', 'Sangat Baik'],
                            ['80–89',  'B', 'Baik'],
                            ['75–79',  'C', 'Cukup'],
                            ['&le;74', 'D', 'Kurang'],
                        ];
                    ?>
                    <div class="cert-slide" id="certSlide1">
                        <div style="width: 100%; aspect-ratio: 1.414; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                            <iframe src="tabel-nilai-viewer.php?id=<?php echo urlencode($cert['certificate_id']); ?>" style="width: 100%; height: 100%; border: none; display: block;" title="Tabel Nilai Sertifikat"></iframe>
                        </div>
                    </div><!-- /slide-2 -->

                </div><!-- /cert-slider-track -->

                <!-- Dot indicators -->
                <div class="slider-dots no-print" id="certDots">
                    <span class="slider-dot active" id="dot0" onclick="certGoTo(0)" title="Sertifikat Depan"></span>
                    <span class="slider-dot" id="dot1" onclick="certGoTo(1)" title="Tabel Nilai"></span>
                </div>

            </div><!-- /cert-slider-outer -->

            <script>
            (function(){
                const TOTAL = 2;
                let cur = 0;
                const track  = document.getElementById('certSliderTrack');
                const btnP   = document.getElementById('btnPrev');
                const btnN   = document.getElementById('btnNext');
                const labels = ['Sertifikat Depan', 'Tabel Nilai (Halaman Belakang)'];

                function update() {
                    track.style.transform = 'translateX(-' + (cur * 100) + '%)';
                    document.getElementById('slideLabel').textContent = labels[cur];
                    btnP.disabled = (cur === 0);
                    btnN.disabled = (cur === TOTAL - 1);
                    for (let i = 0; i < TOTAL; i++) {
                        const d = document.getElementById('dot' + i);
                        if (d) d.classList.toggle('active', i === cur);
                    }
                }

                window.certSlide = function(dir) {
                    cur = Math.max(0, Math.min(TOTAL - 1, cur + dir));
                    update();
                };
                window.certGoTo = function(idx) {
                    cur = idx;
                    update();
                };

                update();
            })();
            </script>

        <?php endif; ?>
    </div>

    <div class="mt-auto shrink-0 w-full no-print">
        <?php include 'partials/footer.php'; ?>
    </div>
</main>
</body>
</html>
