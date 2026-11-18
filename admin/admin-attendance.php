<?php
require_once __DIR__ . '/../session.php';
require_admin();
require_once __DIR__ . '/../Login/koneksi.php';

// Ambil data intern dari database MySQL db_internspace
$db_interns = [];
$db_query = mysqli_query($conn, "SELECT id, username, role FROM users WHERE role = 'intern' ORDER BY id ASC");
if ($db_query) {
    while ($row = mysqli_fetch_assoc($db_query)) {
        $db_interns[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <title>Kedayweb Admin - Kehadiran Semua Intern</title>
<?php include __DIR__ . '/../partials/favicon.php'; ?>
    <!-- Inject DB Interns ke JS -->
    <script>
        window.DB_INTERNS = <?php echo json_encode($db_interns, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="../shared-config.js?v=<?php echo time(); ?>"></script>
    <script src="../intern-store.js?v=<?php echo time(); ?>"></script>
    <link rel="stylesheet" href="../style.css?v=<?php echo time(); ?>" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .font-geist {
            font-family: 'Geist', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 500;
        }

        /* Calendar Responsive Cell (Identical to attendance.php) */
        .cal-cell {
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.15s ease;
            position: relative;
            font-size: 0.7rem;
            font-weight: 600;
            border: 1px solid transparent;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
            min-width: 0;
            width: 100%;
        }

        @media (min-width: 640px) {
            .cal-cell {
                font-size: 0.85rem;
                border-radius: 12px;
                border-width: 1.5px;
            }
        }

        .cal-cell:hover {
            transform: scale(1.05);
            border-color: var(--color-primary, #2563eb);
        }

        .cal-cell.today {
            box-shadow: 0 0 0 2px #2563eb;
        }

        .cal-cell.status-complete {
            background: #dcfce7;
            color: #166534;
        }

        .cal-cell.status-incomplete,
        .cal-cell.status-late {
            background: #fef9c3;
            color: #854d0e;
        }

        .cal-cell.status-empty,
        .cal-cell.status-pending {
            background: #ffffff;
            color: #334155;
            border-color: #e2e8f0;
        }

        /* Dark Mode Calendar Overrides */
        html.dark .cal-cell.status-empty,
        html.dark .cal-cell.status-pending {
            background: #17264c !important;
            color: #cbd5e1 !important;
            border-color: #263864 !important;
        }
        html.dark .cal-cell.status-complete {
            background: #064e3b !important;
            color: #6ee7b7 !important;
        }
        html.dark .cal-cell.status-incomplete,
        html.dark .cal-cell.status-late {
            background: #713f12 !important;
            color: #fde047 !important;
        }
        html.dark .cal-cell.weekend {
            background: #0d1630 !important;
            color: #64748b !important;
        }
        html.dark .cal-cell.future {
            background: #090f23 !important;
            color: #475569 !important;
        }

        .cal-cell.weekend {
            background: #f8fafc;
            color: #94a3b8;
            opacity: 0.7;
            cursor: default;
        }

        .cal-cell.weekend:hover {
            transform: none;
            border-color: transparent;
        }

        .cal-cell.empty {
            cursor: default;
        }

        .cal-cell.empty:hover {
            transform: none;
            border-color: transparent;
        }

        .cal-cell.future {
            background: #f1f5f9;
            color: #cbd5e1;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .cal-cell.future:hover {
            transform: none;
            border-color: transparent;
        }

        .status-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            position: absolute;
            bottom: 4px;
        }

        @media (min-width: 640px) {
            .status-dot {
                width: 6px;
                height: 6px;
                bottom: 5px;
            }
        }

        /* Stat card gradient (From attendance.php) */
        .stat-present {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        }

        .stat-late {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        }

        .stat-absent {
            background: linear-gradient(135deg, #fee2e2 0%, #fca5a5 100%);
        }

        .stat-rate {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        }

        .stat-empty {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .stat-holiday {
            background: linear-gradient(135deg, #ffe4e6 0%, #fecdd3 100%);
        }

        /* Dark mode stat card overrides (Fix Blinding Pastel Boxes) */
        html.dark .stat-present {
            background: linear-gradient(135deg, rgba(6, 78, 59, 0.45) 0%, rgba(6, 95, 70, 0.3) 100%) !important;
            border-color: rgba(5, 150, 105, 0.4) !important;
        }
        html.dark .stat-present,
        html.dark .stat-present * {
            color: #a7f3d0 !important;
        }
        html.dark .stat-present #stat-days-complete,
        html.dark .stat-present [id^="stat-"] {
            color: #ecfdf5 !important;
        }

        html.dark .stat-late {
            background: linear-gradient(135deg, rgba(113, 63, 18, 0.45) 0%, rgba(146, 64, 14, 0.3) 100%) !important;
            border-color: rgba(217, 119, 6, 0.4) !important;
        }
        html.dark .stat-late,
        html.dark .stat-late * {
            color: #fde68a !important;
        }
        html.dark .stat-late #stat-days-late,
        html.dark .stat-late [id^="stat-"] {
            color: #fefce8 !important;
        }

        html.dark .stat-absent {
            background: linear-gradient(135deg, rgba(127, 29, 29, 0.45) 0%, rgba(153, 27, 27, 0.3) 100%) !important;
            border-color: rgba(220, 38, 38, 0.4) !important;
        }
        html.dark .stat-absent,
        html.dark .stat-absent * {
            color: #fca5a5 !important;
        }
        html.dark .stat-absent #stat-days-empty,
        html.dark .stat-absent [id^="stat-"] {
            color: #fff1f2 !important;
        }

        html.dark .stat-rate {
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.45) 0%, rgba(29, 78, 216, 0.3) 100%) !important;
            border-color: rgba(37, 99, 235, 0.4) !important;
        }
        html.dark .stat-rate,
        html.dark .stat-rate * {
            color: #93c5fd !important;
        }
        html.dark .stat-rate #stat-total-interns,
        html.dark .stat-rate [id^="stat-"] {
            color: #eff6ff !important;
        }

        html.dark .stat-empty {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.45) 0%, rgba(51, 65, 85, 0.3) 100%) !important;
            border-color: rgba(100, 116, 139, 0.4) !important;
        }
        html.dark .stat-empty,
        html.dark .stat-empty * {
            color: #cbd5e1 !important;
        }
        html.dark .stat-empty #stat-days-empty,
        html.dark .stat-empty [id^="stat-"] {
            color: #f8fafc !important;
        }

        html.dark .stat-holiday {
            background: linear-gradient(135deg, rgba(136, 19, 55, 0.45) 0%, rgba(159, 18, 57, 0.3) 100%) !important;
            border-color: rgba(225, 29, 72, 0.4) !important;
        }
        html.dark .stat-holiday,
        html.dark .stat-holiday * {
            color: #fca5a5 !important;
        }
        html.dark .stat-holiday #stat-days-holiday,
        html.dark .stat-holiday [id^="stat-"] {
            color: #fff1f2 !important;
        }

        /* Table row hover */
        .att-row {
            transition: background 0.12s;
        }

        .att-row:hover {
            background: #f8fafc;
        }
        html.dark .att-row:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .modal-backdrop {
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
        }

        .badge-present {
            background: #dcfce7;
            color: #166534;
        }

        .badge-late {
            background: #fef9c3;
            color: #854d0e;
        }

        .badge-absent {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-sakit {
            background: #ede9fe;
            color: #6b21a8;
        }

        .badge-izin {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-dispen {
            background: #ede9fe;
            color: #5b21b6;
            border: 1px solid #ddd6fe;
        }

        .badge-permit {
            background: #ede9fe;
            color: #5b21b6;
            border: 1px solid #ddd6fe;
        }

        .badge-dinas {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .badge-pending {
            background: #f1f5f9;
            color: #475569;
        }
    </style>
</head>

<body class="bg-background text-on-surface font-body-md flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <?php $active = 'attendance';
    include '../partials/sidebar-admin.php'; ?>

    <!-- Main -->
    <main class="flex-1 flex flex-col md:ml-[16.5rem] h-screen overflow-y-auto">
        <header class="w-full h-20 bg-surface-container-lowest border-b border-outline-variant sticky top-0 flex justify-between items-center px-4 sm:px-6 z-10 shrink-0">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1 mr-2 sm:mr-4">
                <button onclick="toggleMobileSidebar()" class="md:hidden text-on-surface hover:text-primary focus:outline-none flex items-center p-1 rounded-lg hover:bg-surface-container-high shrink-0" aria-label="Toggle Sidebar">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <span class="material-symbols-outlined text-primary text-xl sm:text-2xl shrink-0" style="font-variation-settings: 'FILL' 1;">event_available</span>
                <h2 class="font-headline-sm sm:font-headline-lg font-bold text-on-surface truncate whitespace-nowrap">
                    Kehadiran Intern
                </h2>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <button onclick="exportAllAdminAttendanceCSV()"
                    class="px-3 py-2 sm:px-3.5 sm:py-2 bg-primary text-on-primary rounded-xl text-xs font-bold hover:opacity-90 flex items-center gap-1 shadow-sm active:scale-95 transition-all shrink-0 cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]">download</span>
                    <span class="hidden sm:inline">Export CSV</span>
                    <span class="sm:hidden font-semibold">CSV</span>
                </button>
                <a href="admin-dashboard.php" class="text-sm font-semibold text-primary hover:underline flex items-center gap-1 hidden sm:flex">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke Dashboard
                </a>
                <div class="flex items-center gap-1 sm:gap-sm p-1 sm:p-1.5 px-2 sm:px-3 rounded-full border border-outline-variant bg-surface-bright shadow-2xs shrink-0">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container overflow-hidden shrink-0">
                        <span class="material-symbols-outlined text-[18px] sm:text-[20px]" style="font-variation-settings: 'FILL' 1;">account_circle</span>
                    </div>
                    <span class="hidden sm:inline-block font-label-md"><?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="../Login/logout.php" class="text-error hover:text-red-700 hover:bg-red-50 p-1 sm:p-1.5 rounded-full transition-colors flex items-center justify-center" title="Keluar" aria-label="Keluar">
                        <span class="material-symbols-outlined text-[18px] sm:text-[20px]">logout</span>
                    </a>
                </div>
            </div>
        </header>
        <div class="flex-1 pb-10 px-2.5 sm:px-4 md:px-8 max-w-[1400px] mx-auto w-full min-w-0">

            <!-- Page Header (Matching attendance.php) -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-5 gap-3 mt-4 md:mt-8">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-primary mb-0.5" id="page-eyebrow">ADMINISTRATOR</p>
                    <h2 class="font-geist text-2xl sm:text-3xl md:text-4xl font-bold text-on-surface" id="page-title">
                        Kehadiran Seluruh Intern
                    </h2>
                    <p class="text-xs sm:text-sm text-on-surface-variant mt-0.5" id="page-subtitle">
                        Pantau presensi harian seluruh intern, status kedatangan, dan kelola absensi.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2 w-full sm:w-auto shrink-0 mt-1 sm:mt-0">
                    <button onclick="exportAllAdminAttendanceCSV()"
                        class="flex-1 sm:flex-none flex items-center justify-center gap-2 border border-outline-variant text-on-surface bg-surface-container-lowest px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold hover:bg-surface-container-low transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">download</span>
                        Export CSV
                    </button>
                </div>
            </div>

            <!-- Stats Row (5 cards matching Keterangan Kalender + Total Intern) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3 mb-6">
                <!-- Stat 1: Total Intern (stat-rate style) -->
                <div class="stat-rate rounded-2xl p-3 sm:p-4 border border-blue-200">
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <span class="material-symbols-outlined text-blue-700 text-[18px] sm:text-[20px]"
                            style="font-variation-settings:'FILL' 1;">groups</span>
                        <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-blue-700 truncate">Total Intern</span>
                    </div>
                    <div id="stat-total-interns" class="text-2xl sm:text-3xl font-geist font-bold text-blue-800">0</div>
                    <div class="text-[11px] sm:text-xs text-blue-700 mt-0.5 truncate">intern aktif</div>
                </div>

                <!-- Stat 2: Lengkap (stat-present style) -->
                <div class="stat-present rounded-2xl p-3 sm:p-4 border border-green-200">
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <span class="material-symbols-outlined text-green-700 text-[18px] sm:text-[20px]"
                            style="font-variation-settings:'FILL' 1;">check_circle</span>
                        <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-green-700 truncate">Lengkap</span>
                    </div>
                    <div id="stat-days-complete" class="text-2xl sm:text-3xl font-geist font-bold text-green-800">0</div>
                    <div class="text-[11px] sm:text-xs text-green-700 mt-0.5 truncate">semua hadir</div>
                </div>

                <!-- Stat 3: Tidak Lengkap (stat-late style) -->
                <div class="stat-late rounded-2xl p-3 sm:p-4 border border-amber-200">
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <span class="material-symbols-outlined text-amber-700 text-[18px] sm:text-[20px]"
                            style="font-variation-settings:'FILL' 1;">schedule</span>
                        <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-amber-700 truncate">Tidak Lengkap</span>
                    </div>
                    <div id="stat-days-late" class="text-2xl sm:text-3xl font-geist font-bold text-amber-800">0</div>
                    <div class="text-[11px] sm:text-xs text-amber-700 mt-0.5 truncate">sebagian belum</div>
                </div>

                <!-- Stat 4: Belum Ada Yang Mengisi (stat-empty style) -->
                <div class="stat-empty rounded-2xl p-3 sm:p-4 border border-slate-300 dark:border-slate-700">
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <span class="material-symbols-outlined text-slate-600 dark:text-slate-400 text-[18px] sm:text-[20px]">calendar_today</span>
                        <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 truncate" title="Belum Ada Yang Mengisi">Belum Terisi</span>
                    </div>
                    <div id="stat-days-empty" class="text-2xl sm:text-3xl font-geist font-bold text-slate-800 dark:text-slate-200">0</div>
                    <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">belum ada catatan</div>
                </div>

                <!-- Stat 5: Tanggal Merah / Libur (stat-holiday style) -->
                <div class="stat-holiday rounded-2xl p-3 sm:p-4 border border-rose-200 dark:border-rose-800 col-span-2 sm:col-span-1">
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <span class="material-symbols-outlined text-rose-700 dark:text-rose-300 text-[18px] sm:text-[20px]"
                            style="font-variation-settings:'FILL' 1;">event_busy</span>
                        <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-rose-700 dark:text-rose-300 truncate" title="Tanggal Merah / Libur">Tanggal Merah</span>
                    </div>
                    <div id="stat-days-holiday" class="text-2xl sm:text-3xl font-geist font-bold text-rose-800 dark:text-rose-200">0</div>
                    <div class="text-[11px] sm:text-xs text-rose-700 dark:text-rose-400 mt-0.5 truncate">libur resmi</div>
                </div>
            </div>

            <!-- Main Bento Grid (Prioritize Calendar on Mobile, matching attendance.php) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-6 sm:mb-8 min-w-0 w-full">

                <!-- Right: Calendar (First on Mobile screens for fast access) -->
                <div class="lg:col-span-8 order-1 lg:order-2 bg-surface-container-lowest rounded-2xl border border-outline-variant p-3 sm:p-5 min-w-0 w-full overflow-hidden">
                    <!-- Calendar Header -->
                    <div class="flex justify-between items-center mb-3 sm:mb-5">
                        <h3 id="cal-title" class="font-geist text-base sm:text-xl font-bold text-on-surface"></h3>
                        <div class="flex items-center gap-1">
                            <button id="cal-prev" onclick="changeMonth(-1)"
                                class="p-1.5 sm:p-2 rounded-lg hover:bg-surface-container-low transition-colors text-on-surface-variant hover:text-primary cursor-pointer">
                                <span class="material-symbols-outlined text-xl">chevron_left</span>
                            </button>
                            <button onclick="goToday()"
                                class="px-2.5 sm:px-3 py-1.5 rounded-lg text-[11px] sm:text-xs font-semibold bg-primary-container text-on-primary-container hover:opacity-80 transition-opacity cursor-pointer">
                                Hari Ini
                            </button>
                            <button id="cal-next" onclick="changeMonth(1)"
                                class="p-1.5 sm:p-2 rounded-lg hover:bg-surface-container-low transition-colors text-on-surface-variant hover:text-primary cursor-pointer">
                                <span class="material-symbols-outlined text-xl">chevron_right</span>
                            </button>
                        </div>
                    </div>
                    <!-- Day Headers (7 Columns Fit) -->
                    <div class="grid grid-cols-7 gap-1 sm:gap-1.5 mb-2 text-center w-full min-w-0">
                        <div class="text-[10px] sm:text-xs font-bold uppercase tracking-tight text-red-500 truncate">Min</div>
                        <div class="text-[10px] sm:text-xs font-bold uppercase tracking-tight text-on-surface-variant truncate">Sen</div>
                        <div class="text-[10px] sm:text-xs font-bold uppercase tracking-tight text-on-surface-variant truncate">Sel</div>
                        <div class="text-[10px] sm:text-xs font-bold uppercase tracking-tight text-on-surface-variant truncate">Rab</div>
                        <div class="text-[10px] sm:text-xs font-bold uppercase tracking-tight text-on-surface-variant truncate">Kam</div>
                        <div class="text-[10px] sm:text-xs font-bold uppercase tracking-tight text-on-surface-variant truncate">Jum</div>
                        <div class="text-[10px] sm:text-xs font-bold uppercase tracking-tight text-on-surface-variant truncate">Sab</div>
                    </div>
                    <!-- Calendar Grid -->
                    <div id="cal-grid" class="grid grid-cols-7 gap-1 sm:gap-1.5 w-full min-w-0"></div>
                </div>

                <!-- Left: Action + Legend + Guidance (Second on Mobile screens) -->
                <div class="lg:col-span-4 order-2 lg:order-1 flex flex-col gap-4">

                    <!-- Admin Action Card -->
                    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-4 sm:p-5 flex flex-col items-center text-center">
                        <span class="material-symbols-outlined text-[36px] sm:text-[44px] text-emerald-500 mb-1 sm:mb-2"
                            style="font-variation-settings:'FILL' 1;">edit_calendar</span>
                        <div class="font-geist text-lg sm:text-xl font-bold text-on-surface mb-0.5">Kelola Kehadiran</div>
                        <p class="text-xs text-on-surface-variant mb-3">Atur status hadir manual, dispensasi resmi instansi, atau izin sakit.</p>
                        <button onclick="openAdminSetAttendanceModal()"
                            class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs sm:text-sm font-bold transition flex items-center justify-center gap-2 shadow-sm cursor-pointer active:scale-95">
                            <span class="material-symbols-outlined text-[18px]">add_circle</span>
                            <span>+ Set Kehadiran Intern</span>
                        </button>

                    </div>

                    <!-- Legend Card (Exact replica of attendance.php) -->
                    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-4 sm:p-5">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-3">Keterangan Kalender</h3>
                        <div class="space-y-2.5">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-green-100 dark:bg-green-950/60 flex items-center justify-center text-green-700 dark:text-green-300 text-xs font-bold shrink-0">
                                    12
                                </div>
                                <div>
                                    <div class="text-xs sm:text-sm font-semibold text-on-surface">Lengkap</div>
                                    <div class="text-[11px] sm:text-xs text-on-surface-variant">Semua intern sudah mengisi absensi</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-amber-100 dark:bg-amber-950/60 flex items-center justify-center text-amber-700 dark:text-amber-300 text-xs font-bold shrink-0">
                                    12
                                </div>
                                <div>
                                    <div class="text-xs sm:text-sm font-semibold text-on-surface">Tidak Lengkap</div>
                                    <div class="text-[11px] sm:text-xs text-on-surface-variant">Sebagian intern sudah mengisi absensi</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white dark:bg-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-300 text-xs font-bold shrink-0 border border-slate-200 dark:border-slate-700 shadow-2xs">
                                    12
                                </div>
                                <div>
                                    <div class="text-xs sm:text-sm font-semibold text-on-surface">Belum Ada Yang Mengisi</div>
                                    <div class="text-[11px] sm:text-xs text-on-surface-variant">Belum ada catatan kehadiran</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-rose-100 dark:bg-rose-950/70 border border-rose-300 dark:border-rose-800 flex items-center justify-center text-rose-700 dark:text-rose-300 text-xs font-bold shrink-0">
                                    17
                                </div>
                                <div>
                                    <div class="text-xs sm:text-sm font-semibold text-on-surface">Tanggal Merah / Libur</div>
                                    <div class="text-[11px] sm:text-xs text-on-surface-variant">Libur nasional / cuti bersama resmi</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cara Pakai Card (Matches attendance.php styling) -->
                    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-4 sm:p-5">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2.5">Cara Pakai</h3>
                        <p class="text-xs sm:text-sm text-on-surface-variant leading-relaxed">
                            Klik salah satu tanggal pada kalender untuk melihat rincian kehadiran setiap intern pada hari itu &mdash; termasuk jam masuk, keterlambatan, dan alasan tidak masuk.
                        </p>
                    </div>
                </div>
            </div>

            <!-- History Table / Daftar Rekap Intern (Matches attendance.php table) -->
            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-outline-variant flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div>
                        <h3 class="font-geist text-base sm:text-lg font-bold text-on-surface">Daftar Kehadiran Seluruh Intern</h3>
                        <p class="text-xs text-on-surface-variant mt-0.5">Rekapitulasi total kehadiran per intern pada bulan aktif</p>
                    </div>
                    <div class="w-full sm:w-auto">
                        <div class="relative w-full sm:w-64">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                            <input type="text" id="roster-search" oninput="filterRosterTable()" placeholder="Cari nama atau divisi intern..."
                                class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-outline-variant bg-surface-container-low focus:outline-none focus:ring-2 focus:ring-primary font-medium" />
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-surface-container-low border-b border-outline-variant">
                                <th class="px-3.5 sm:px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-on-surface-variant whitespace-nowrap">Nama Intern</th>
                                <th class="px-3.5 sm:px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-on-surface-variant whitespace-nowrap">Divisi</th>
                                <th class="px-3.5 sm:px-5 py-3 text-center text-xs font-bold uppercase tracking-wider text-green-700 whitespace-nowrap">Hadir</th>
                                <th class="px-3.5 sm:px-5 py-3 text-center text-xs font-bold uppercase tracking-wider text-amber-700 whitespace-nowrap">Terlambat</th>
                                <th class="px-3.5 sm:px-5 py-3 text-center text-xs font-bold uppercase tracking-wider text-purple-700 whitespace-nowrap">Dispen / Izin</th>
                                <th class="px-3.5 sm:px-5 py-3 text-center text-xs font-bold uppercase tracking-wider text-red-700 whitespace-nowrap">Alpha</th>
                                <th class="px-3.5 sm:px-5 py-3 text-center text-xs font-bold uppercase tracking-wider text-on-surface-variant whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="intern-roster-body" class="divide-y divide-outline-variant"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-auto shrink-0 w-full">
            <?php include '../partials/footer.php'; ?>
        </div>
    </main>

    
    <!-- Modal Set / Override Kehadiran Intern (Admin) -->
    <div id="admin-set-attendance-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-backdrop">
        <div class="bg-surface-bright dark:bg-[#111a33] text-on-surface rounded-2xl w-full max-w-md shadow-2xl border border-outline-variant overflow-hidden max-h-[90vh] flex flex-col">
            <div class="p-5 border-b border-outline-variant flex justify-between items-center bg-slate-50 dark:bg-[#0c1326]">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]">edit_calendar</span>
                    </div>
                    <div>
                        <h3 class="font-headline-md font-bold text-on-surface text-base">Atur Kehadiran Intern</h3>
                        <p class="text-xs text-on-surface-variant">Atur status hadir, telat, dispen, atau alpha</p>
                    </div>
                </div>
                <button type="button" onclick="closeAdminSetAttendanceModal(true)" class="text-on-surface-variant hover:text-primary transition p-1 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <form id="admin-set-att-form" onsubmit="handleAdminSetAttendanceSubmit(event)" class="p-5 space-y-4 overflow-y-auto flex-1">
                <!-- Intern Selector -->
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wide mb-1.5">
                        Pilih Intern <span class="text-red-500">*</span>
                    </label>
                    <select id="admin-set-username" required class="w-full rounded-xl border border-outline-variant bg-surface-bright px-3 py-2.5 text-sm font-semibold focus:ring-2 focus:ring-primary focus:outline-none">
                        <option value="">-- Pilih Intern --</option>
                        <?php foreach ($db_interns as $di): ?>
                            <option value="<?php echo htmlspecialchars($di['username'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($di['username'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tanggal Kehadiran -->
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wide mb-1.5">
                        Tanggal Kehadiran <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="admin-set-date" required class="w-full rounded-xl border border-outline-variant bg-surface-bright px-3 py-2 text-sm font-semibold focus:ring-2 focus:ring-primary focus:outline-none" />
                </div>

                <!-- Status Kehadiran Selector (4 Radio Cards) -->
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wide mb-1.5">
                        Status Kehadiran <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <!-- Present -->
                        <label onclick="selectAdminStatus('present')" id="admin-status-card-present" class="flex items-center gap-2 border-2 border-emerald-400 bg-emerald-50/60 rounded-xl p-2.5 cursor-pointer transition select-none">
                            <input type="radio" name="admin_status" value="present" checked class="sr-only" />
                            <span class="material-symbols-outlined text-emerald-600 text-[20px]">check_circle</span>
                            <div>
                                <span class="block text-xs font-bold text-emerald-900">Hadir</span>
                                <span class="block text-[10px] text-emerald-700/80">Tepat Waktu</span>
                            </div>
                        </label>

                        <!-- Late -->
                        <label onclick="selectAdminStatus('late')" id="admin-status-card-late" class="flex items-center gap-2 border-2 border-outline-variant bg-surface-container-lowest rounded-xl p-2.5 cursor-pointer transition select-none">
                            <input type="radio" name="admin_status" value="late" class="sr-only" />
                            <span class="material-symbols-outlined text-amber-500 text-[20px]">schedule</span>
                            <div>
                                <span class="block text-xs font-bold text-amber-900">Terlambat</span>
                                <span class="block text-[10px] text-amber-700/80">Lewat batas</span>
                            </div>
                        </label>

                        <!-- Permit / Dispen -->
                        <label onclick="selectAdminStatus('permit')" id="admin-status-card-permit" class="flex items-center gap-2 border-2 border-outline-variant bg-surface-container-lowest rounded-xl p-2.5 cursor-pointer transition select-none">
                            <input type="radio" name="admin_status" value="permit" class="sr-only" />
                            <span class="material-symbols-outlined text-purple-600 text-[20px]">verified</span>
                            <div>
                                <span class="block text-xs font-bold text-purple-900">Izin</span>
                                <span class="block text-[10px] text-purple-700/80">Izin resmi</span>
                            </div>
                        </label>

                        <!-- Absent -->
                        <label onclick="selectAdminStatus('absent')" id="admin-status-card-absent" class="flex items-center gap-2 border-2 border-outline-variant bg-surface-container-lowest rounded-xl p-2.5 cursor-pointer transition select-none">
                            <input type="radio" name="admin_status" value="absent" class="sr-only" />
                            <span class="material-symbols-outlined text-red-500 text-[20px]">cancel</span>
                            <div>
                                <span class="block text-xs font-bold text-red-900">Alpha</span>
                                <span class="block text-[10px] text-red-700/80">Tidak Masuk</span>
                            </div>
                        </label>

                        <!-- Holiday (Libur / Tanggal Merah) -->
                        <label onclick="selectAdminStatus('holiday')" id="admin-status-card-holiday" class="col-span-2 flex items-center gap-2 border-2 border-outline-variant bg-surface-container-lowest rounded-xl p-2.5 cursor-pointer transition select-none">
                            <input type="radio" name="admin_status" value="holiday" class="sr-only" />
                            <span class="material-symbols-outlined text-rose-500 text-[20px]">event_busy</span>
                            <div>
                                <span class="block text-xs font-bold text-rose-900 dark:text-rose-300">Libur / Tanggal Merah</span>
                                <span class="block text-[10px] text-rose-700/80 dark:text-rose-400/80">Menetapkan hari libur resmi untuk semua intern pada tanggal ini</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Holiday Info Banner (Muncul saat status holiday dipilih) -->
                <div id="admin-holiday-info-wrap" class="hidden p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 space-y-1">
                    <div class="text-xs font-bold text-rose-900 dark:text-rose-200 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">info</span>
                        <span>Berlaku Untuk Seluruh Intern</span>
                    </div>
                    <p class="text-[11px] text-rose-700 dark:text-rose-300/80 leading-relaxed">
                        Menetapkan tanggal ini sebagai Hari Libur / Tanggal Merah otomatis membebaskan semua intern dari kewajiban presensi, sehingga tidak akan dihitung Alpha / Tidak Masuk.
                    </p>
                </div>

                <!-- Sub-type Dispensasi / Izin (Muncul saat status permit dipilih) -->
                <div id="admin-leave-type-wrap" class="hidden p-3 rounded-xl bg-purple-50 border border-purple-200 space-y-2">
                    <label class="block text-xs font-bold text-purple-900">
                        Kategori Dispensasi / Izin <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <label class="flex items-center gap-1.5 font-medium cursor-pointer">
                            <input type="radio" name="admin_leave_type" value="dispen" checked class="text-purple-600 focus:ring-purple-400" />
                            <span>Dispensasi instansi</span>
                        </label>
                        <label class="flex items-center gap-1.5 font-medium cursor-pointer">
                            <input type="radio" name="admin_leave_type" value="izin" class="text-purple-600 focus:ring-purple-400" />
                            <span>Izin Keperluan</span>
                        </label>
                        <label class="flex items-center gap-1.5 font-medium cursor-pointer">
                            <input type="radio" name="admin_leave_type" value="sakit" class="text-purple-600 focus:ring-purple-400" />
                            <span>Sakit</span>
                        </label>
                        <label class="flex items-center gap-1.5 font-medium cursor-pointer">
                            <input type="radio" name="admin_leave_type" value="dinas" class="text-purple-600 focus:ring-purple-400" />
                            <span>Tugas Luar / Dinas</span>
                        </label>
                    </div>
                </div>

                <!-- Jam Masuk & Jam Keluar (Untuk Present & Late) -->
                <div id="admin-time-fields" class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-on-surface uppercase tracking-wide mb-1">Jam Masuk</label>
                        <input type="time" id="admin-set-clock-in" value="08:00" class="w-full rounded-xl border border-outline-variant bg-surface-bright px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-primary focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface uppercase tracking-wide mb-1">Jam Keluar (Opsional)</label>
                        <input type="time" id="admin-set-clock-out" value="16:00" class="w-full rounded-xl border border-outline-variant bg-surface-bright px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-primary focus:outline-none" />
                    </div>
                </div>

                <!-- Alasan / Keterangan -->
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wide mb-1.5">
                        Alasan / Keterangan Dispensasi / Catatan
                    </label>
                    <textarea id="admin-set-reason" rows="2" placeholder="Contoh: Dispensasi kegiatan seminar kampus, atau lupa absen..." class="w-full rounded-xl border border-outline-variant bg-surface-bright px-3 py-2 text-xs leading-relaxed focus:ring-2 focus:ring-primary focus:outline-none resize-none"></textarea>
                </div>

                <div id="admin-set-msg" class="hidden text-xs font-semibold p-2.5 rounded-xl"></div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between gap-2 pt-3 border-t border-outline-variant">
                    <button type="button" id="admin-delete-att-btn" onclick="deleteCurrentDayAttendance()" class="px-3 py-2 border border-red-200 text-red-600 hover:bg-red-50 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer hidden">
                        <span class="material-symbols-outlined text-[15px]">delete</span>
                        <span>Hapus Catatan</span>
                    </button>
                    <div class="flex items-center gap-2 ml-auto">
                        <button type="button" onclick="closeAdminSetAttendanceModal(true)" class="px-3.5 py-2 border border-outline-variant rounded-xl text-xs font-semibold text-on-surface-variant hover:bg-slate-100 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" id="admin-set-submit-btn" class="px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl hover:opacity-90 transition shadow-sm flex items-center gap-1 cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">save</span>
                            <span>Simpan Kehadiran</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Detail modal: rincian kehadiran per-intern pada satu tanggal -->
    <div id="detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-backdrop">
        <div
            class="bg-surface-bright dark:bg-[#111a33] text-on-surface rounded-2xl w-full max-w-lg shadow-2xl border border-outline-variant overflow-hidden max-h-[85vh] flex flex-col">
            <div class="p-5 border-b border-outline-variant flex justify-between items-start bg-slate-50 dark:bg-[#0c1326]">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-0.5">Rincian Kehadiran</p>
                    <h3 id="detail-date-label" class="font-headline-md font-bold text-on-surface text-lg"></h3>
                </div>
                <button onclick="closeDetailModal()"
                    class="text-on-surface-variant hover:text-primary transition-colors p-1 rounded-lg hover:bg-surface-container-low">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div id="detail-list" class="p-5 space-y-3 overflow-y-auto"></div>
        </div>
    </div>

    <!-- Modal Kelola Hari Libur & Tanggal Merah -->
    <div id="holiday-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-backdrop">
        <div class="bg-surface-bright dark:bg-[#111a33] text-on-surface rounded-2xl w-full max-w-lg shadow-2xl border border-outline-variant overflow-hidden max-h-[90vh] flex flex-col">
            <div class="p-5 border-b border-outline-variant flex justify-between items-center bg-rose-50/70 dark:bg-rose-950/40">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]">event_busy</span>
                    </div>
                    <div>
                        <h3 class="font-headline-md font-bold text-on-surface text-base">Kelola Tanggal Merah & Hari Libur</h3>
                        <p class="text-xs text-on-surface-variant">Atur hari libur resmi agar intern tidak terhitung Alpha / Tidak Masuk</p>
                    </div>
                </div>
                <button type="button" onclick="closeHolidayModal(true)" class="text-on-surface-variant hover:text-primary transition p-1 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <div class="p-5 overflow-y-auto flex-1 space-y-5">
                <!-- Form Tambah Libur Baru -->
                <form id="add-holiday-form" onsubmit="handleSaveHolidaySubmit(event)" class="p-4 rounded-xl border border-rose-200 dark:border-rose-900/60 bg-rose-50/40 dark:bg-rose-950/20 space-y-3">
                    <div class="text-xs font-bold uppercase tracking-wide text-rose-900 dark:text-rose-300 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">add_circle</span>
                        <span>Tambah Hari Libur / Tanggal Merah</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-on-surface mb-1">Tanggal Libur <span class="text-red-500">*</span></label>
                            <input type="date" id="holiday-input-date" required class="w-full rounded-xl border border-outline-variant bg-surface-bright px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-rose-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-on-surface mb-1">Nama / Judul Libur <span class="text-red-500">*</span></label>
                            <input type="text" id="holiday-input-title" placeholder="Contoh: Hari Kemerdekaan RI" required class="w-full rounded-xl border border-outline-variant bg-surface-bright px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-rose-500 focus:outline-none" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface mb-1">Keterangan Tambahan (Opsional)</label>
                        <input type="text" id="holiday-input-desc" placeholder="Contoh: Libur nasional sesuai SKB 3 Menteri" class="w-full rounded-xl border border-outline-variant bg-surface-bright px-3 py-2 text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none" />
                    </div>
                    <div id="holiday-form-msg" class="hidden text-xs font-semibold p-2 rounded-lg"></div>
                    <div class="flex justify-end">
                        <button type="submit" id="holiday-submit-btn" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-sm cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">save</span>
                            <span>Simpan Hari Libur</span>
                        </button>
                    </div>
                </form>

                <!-- Daftar Hari Libur Terdaftar -->
                <div>
                    <div class="flex items-center justify-between mb-2.5">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Daftar Hari Libur Terdaftar</h4>
                        <span id="holiday-count-badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300">0 Hari</span>
                    </div>
                    <div id="holiday-list-container" class="space-y-2 max-h-56 overflow-y-auto pr-1">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-outline-variant flex justify-end bg-slate-50 dark:bg-[#0c1326]">
                <button type="button" onclick="closeHolidayModal(true)" class="px-4 py-2 border border-outline-variant rounded-xl text-xs font-semibold text-on-surface-variant hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        let calYear, calMonth;
        let dbInternsList = [];
        let dbAttendanceMap = {}; // { username: { date: record } }
        let dbHolidaysMap = {};   // { dateStr: { id, date, title, description } }

        // Zona Waktu: Paksa mengikuti WIB (Asia/Jakarta, UTC+7)
        function getNowWIB(d) {
            const base = (d instanceof Date) ? d : new Date();
            try {
                return new Date(base.toLocaleString('en-US', { timeZone: 'Asia/Jakarta' }));
            } catch (e) {
                return new Date(base.getTime() + (7 * 3600 * 1000) + (base.getTimezoneOffset() * 60000));
            }
        }

        function fmt(d) { return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`; }

        function formatDateDisplay(ds) {
            const [y, m, d] = ds.split('-');
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const dateObj = new Date(+y, +m - 1, +d);
            return `${days[dateObj.getDay()]}, ${+d} ${months[+m - 1]} ${y}`;
        }

        function formatTime12(t) {
            if (!t || t === '--:--') return '--:--';
            const [h, min] = t.split(':').map(Number);
            const ampm = h >= 12 ? 'PM' : 'AM';
            const h12 = h % 12 || 12;
            return `${String(h12).padStart(2, '0')}:${String(min).padStart(2, '0')} ${ampm}`;
        }

        function initCalendar() {
            const now = getNowWIB();
            calYear = now.getFullYear();
            calMonth = now.getMonth();
        }

        async function fetchDbAttendance() {
            try {
                // Ambil data langsung dari MySQL Database (db_internspace)
                const res = await fetch('../attendance-api.php?action=all_summary');
                if (res.ok) {
                    const data = await res.json();
                    dbInternsList = data.interns || [];
                    dbAttendanceMap = data.attendanceMap || {};
                    dbHolidaysMap = data.holidays || {};
                }
            } catch (err) {
                console.warn('Gagal memuat data presensi dari MySQL:', err);
            }
        }

        // Helper ambil catatan kehadiran intern dengan toleransi case-insensitive
        function getInternAttendanceMap(name) {
            if (!name) return {};
            if (dbAttendanceMap[name]) return dbAttendanceMap[name];
            const lower = String(name).toLowerCase();
            for (const k in dbAttendanceMap) {
                if (k.toLowerCase() === lower) return dbAttendanceMap[k];
            }
            return {};
        }

        // Ambil ringkasan kehadiran per tanggal HANYA dari database
        function getCombinedDaySummary(dateStr) {
            // Gunakan HANYA data dari DB MySQL
            let interns = dbInternsList;
            if (!interns.length && window.DB_INTERNS && window.DB_INTERNS.length) {
                interns = window.DB_INTERNS.map(u => ({ name: u.username, division: 'Intern Kedayweb' }));
            }
            if (!interns.length) return { entries: [], total: 0, present: 0, late: 0, absent: 0, unmarked: 0 };

            const entries = interns.map(intern => {
                let rec = null;
                const dbUserAtt = getInternAttendanceMap(intern.name);
                if (dbUserAtt && dbUserAtt[dateStr]) {
                    rec = dbUserAtt[dateStr];
                }
                return { intern, record: rec };
            });

            const present = entries.filter(e => e.record?.status === 'present').length;
            const late = entries.filter(e => e.record?.status === 'late').length;
            const permit = entries.filter(e => e.record?.status === 'permit').length;
            const absent = entries.filter(e => e.record?.status === 'absent').length;
            const unmarked = entries.filter(e => !e.record).length;

            return { entries, total: interns.length, present, late, permit, absent, unmarked };
        }

        function changeMonth(dir) {
            calMonth += dir;
            if (calMonth > 11) { calMonth = 0; calYear++; }
            if (calMonth < 0) { calMonth = 11; calYear--; }
            renderAll();
        }

        function goToday() {
            const now = getNowWIB();
            calYear = now.getFullYear();
            calMonth = now.getMonth();
            renderAll();
        }

        function dayClass(summary, isFuture, isToday = false, dateStr = '') {
            if (isFuture) return 'future';
            if (!summary || summary.total === 0) return 'empty';

            // Hitung berapa banyak intern yang sudah mengisi pada hari ini
            const filledCount = summary.entries.filter(e => e.record !== null).length;

            if (filledCount === 0) {
                // Belum ada yang mengisi sama sekali -> Putih
                return 'empty';
            } else if (filledCount >= summary.total || summary.unmarked === 0) {
                // Semua intern sudah mengisi -> Hijau (Lengkap)
                return 'complete';
            } else {
                // Baru sebagian yang mengisi -> Kuning (Tidak Lengkap)
                return 'incomplete';
            }
        }

        function renderCalendar() {
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            document.getElementById('cal-title').textContent = `${monthNames[calMonth]} ${calYear}`;

            const grid = document.getElementById('cal-grid');
            grid.innerHTML = '';

            const firstDay = new Date(calYear, calMonth, 1).getDay();
            const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
            const today = getNowWIB();
            const todayStr = fmt(today);

            for (let i = 0; i < firstDay; i++) {
                const cell = document.createElement('div');
                cell.className = 'cal-cell empty';
                grid.appendChild(cell);
            }

            let daysComplete = 0, daysIncomplete = 0, daysEmpty = 0, daysHoliday = 0;

            for (let d = 1; d <= daysInMonth; d++) {
                const dateObj = new Date(calYear, calMonth, d);
                const dateStr = fmt(dateObj);
                const dow = dateObj.getDay();
                const isFuture = dateObj > today && dateStr !== todayStr;
                const isToday = dateStr === todayStr;

                const cell = document.createElement('div');
                cell.classList.add('cal-cell');

                const summary = getCombinedDaySummary(dateStr);
                const hasRecords = summary.entries.some(e => e.record !== null);

                // Minggu (0) libur default
                const isSunday = (dow === 0);
                const isHoliday = dbHolidaysMap && dbHolidaysMap[dateStr];

                if (isHoliday) {
                    daysHoliday++;
                    cell.classList.add('holiday');
                    cell.title = `Libur: ${isHoliday.title || 'Tanggal Merah'}`;
                    const dot = document.createElement('div');
                    dot.className = 'status-dot';
                    dot.style.background = '#e11d48';
                    cell.appendChild(dot);
                } else if (isSunday && !hasRecords) {
                    cell.classList.add('weekend');
                } else {
                    const cls = dayClass(summary, isFuture, isToday, dateStr);
                    if (!isFuture && !isSunday) {
                        if (cls === 'complete') daysComplete++;
                        else if (cls === 'incomplete') daysIncomplete++;
                        else if (cls === 'empty') daysEmpty++;
                    }
                    cell.classList.add(`status-${cls}`);

                    if (isFuture) {
                        cell.classList.add('future');
                    }

                    // Tampilkan dot status: Hijau jika lengkap, Kuning jika tidak lengkap (tidak ada merah/putih)
                    if (!isFuture && (cls === 'complete' || cls === 'incomplete')) {
                        const dot = document.createElement('div');
                        dot.className = 'status-dot';
                        dot.style.background = (cls === 'complete') ? '#16a34a' : '#d97706';
                        cell.appendChild(dot);
                    }
                }

                // Semua tanggal dapat diklik oleh admin untuk rincian & set absensi
                cell.addEventListener('click', () => openDetailModal(dateStr));

                if (isToday) cell.classList.add('today');
                const span = document.createElement('span');
                span.textContent = d;
                cell.appendChild(span);
                grid.appendChild(cell);
            }

            if (document.getElementById('stat-days-complete')) document.getElementById('stat-days-complete').textContent = daysComplete;
            if (document.getElementById('stat-days-late')) document.getElementById('stat-days-late').textContent = daysIncomplete;
            if (document.getElementById('stat-days-empty')) document.getElementById('stat-days-empty').textContent = daysEmpty;
            if (document.getElementById('stat-days-holiday')) document.getElementById('stat-days-holiday').textContent = daysHoliday;
        }

        let lastOpenedDetailDate = null;
        let returnToDetailDate = null;

        function openDetailModal(dateStr) {
            lastOpenedDetailDate = dateStr;
            const summary = getCombinedDaySummary(dateStr);
            document.getElementById('detail-date-label').textContent = formatDateDisplay(dateStr);
            const list = document.getElementById('detail-list');
            list.innerHTML = '';

            const isHoliday = dbHolidaysMap && dbHolidaysMap[dateStr];
            if (isHoliday) {
                const holCard = document.createElement('div');
                holCard.className = 'p-3.5 mb-3 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900/60 flex items-center justify-between gap-2';
                holCard.innerHTML = `
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-300 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[19px]">event_busy</span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-rose-900 dark:text-rose-200">Hari Libur: ${escHtml(isHoliday.title || 'Tanggal Merah')}</div>
                            <div class="text-[11px] text-rose-700 dark:text-rose-300/80">${isHoliday.description ? escHtml(isHoliday.description) : 'Seluruh intern dibebaskan dari absensi (tidak dihitung Alpha).'}</div>
                        </div>
                    </div>
                    <button type="button" onclick="deleteHolidayByDate('${dateStr}')" class="px-2.5 py-1 text-[11px] font-bold text-rose-700 dark:text-rose-300 bg-white dark:bg-rose-900/80 hover:bg-rose-100 rounded-lg border border-rose-200 dark:border-rose-700 transition cursor-pointer shrink-0">
                        Hapus Libur
                    </button>
                `;
                list.appendChild(holCard);
            } else {
                const addHolWrap = document.createElement('div');
                addHolWrap.className = 'mb-2 flex items-center justify-end';
                addHolWrap.innerHTML = `
                    <button type="button" onclick="openHolidayModalWithDate('${dateStr}')" class="text-xs font-bold text-rose-600 dark:text-rose-400 hover:text-rose-700 flex items-center gap-1 cursor-pointer">
                        <span class="material-symbols-outlined text-[15px]">event_busy</span>
                        <span>+ Tandai Tanggal Ini Sebagai Libur</span>
                    </button>
                `;
                list.appendChild(addHolWrap);
            }

            if (summary.entries.length === 0) {
                list.innerHTML += '<p class="text-sm text-on-surface-variant italic">Belum ada intern terdaftar.</p>';
            } else {
                const todayStr = fmt(getNowWIB());
                const now = getNowWIB();
                const isTodayPastCutoff = (dateStr === todayStr) && (now.getHours() >= 14);
                const isPastDay = dateStr < todayStr;

                // Urutkan intern: Belum Absen / Tidak Masuk (paling atas) -> Telat -> Izin -> Masuk
                const sortedEntries = summary.entries.map(item => {
                    const { intern, record } = item;
                    let status = record ? record.status : 'pending';
                    if (!record) {
                        if (isHoliday) {
                            status = 'holiday';
                        } else if (isPastDay || isTodayPastCutoff) {
                            status = 'absent';
                        }
                    }
                    return { intern, record, status };
                });

                function getStatusSortPriority(s) {
                    if (s === 'pending') return 1; // Belum Absen (paling atas)
                    if (s === 'absent')  return 2; // Tidak Masuk / Alpha
                    if (s === 'late')    return 3; // Telat
                    if (s === 'permit')  return 4; // Izin / Sakit / Dispen
                    if (s === 'present') return 5; // Masuk (Hadir Tepat Waktu)
                    if (s === 'holiday') return 6; // Libur Resmi
                    return 7;
                }

                sortedEntries.sort((a, b) => {
                    const pA = getStatusSortPriority(a.status);
                    const pB = getStatusSortPriority(b.status);
                    if (pA !== pB) return pA - pB;
                    return (a.intern.name || '').localeCompare(b.intern.name || '');
                });

                sortedEntries.forEach(({ intern, record, status }) => {

                    let label = { present: 'Hadir Tepat Waktu', late: 'Terlambat', permit: 'Izin', absent: 'Tidak Masuk', holiday: 'Libur Resmi', pending: 'Belum Absen' }[status] || 'Belum Absen';
                    let badgeClass = { present: 'badge-present', late: 'badge-late', permit: 'badge-dispen', absent: 'badge-absent', holiday: 'badge-holiday', pending: 'badge-pending' }[status] || 'badge-pending';
                    let icon = { present: 'check_circle', late: 'schedule', permit: 'verified', absent: 'cancel', holiday: 'event_busy', pending: 'help' }[status] || 'help';

                    if (record && (record.status === 'permit' || record.status === 'absent') && record.leaveType) {
                        const lt = record.leaveType.toLowerCase();
                        if (lt === 'dispen' || lt === 'dispensasi') {
                            label = 'Dispensasi Instansi';
                            badgeClass = 'badge-dispen';
                            icon = 'verified';
                        } else if (lt === 'sakit') {
                            label = 'Izin (Sakit)';
                            badgeClass = 'badge-sakit';
                            icon = 'medication';
                        } else if (lt === 'izin') {
                            label = 'Izin Keperluan';
                            badgeClass = 'badge-izin';
                            icon = 'event_busy';
                        } else if (lt === 'dinas') {
                            label = 'Tugas Luar / Dinas';
                            badgeClass = 'badge-dinas';
                            icon = 'work';
                        } else {
                            label = `Dispen (${record.leaveType})`;
                            badgeClass = 'badge-dispen';
                            icon = 'verified';
                        }
                    }

                    const row = document.createElement('div');
                    row.className = 'border border-outline-variant rounded-xl p-3.5 bg-surface-container-lowest shadow-xs';
                    row.innerHTML = `
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-full bg-primary-container text-on-primary-container font-bold flex items-center justify-center text-sm">
                                        ${escHtml(intern.name.charAt(0).toUpperCase())}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="text-sm font-bold text-on-surface">${escHtml(intern.name)}</span>
                                            ${record && record.overrideBy ? `<span class="text-[9.5px] bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded border border-indigo-200 font-semibold">Diset Admin</span>` : ''}
                                        </div>
                                        <div class="text-xs text-on-surface-variant">${escHtml(intern.division || 'Intern Kedayweb')}</div>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold ${badgeClass}">
                                    <span class="material-symbols-outlined text-[14px]" style="font-variation-settings:'FILL' 1;">${icon}</span>${label}
                                </span>
                            </div>

                            ${record && status !== 'absent' ? `
                            <div class="grid grid-cols-2 gap-2 mt-2 text-xs">
                                <div class="bg-surface-container-low rounded-lg p-2 text-center">
                                    <div class="text-on-surface-variant text-[11px]">Jam Masuk</div>
                                    <div class="font-bold text-slate-900">${formatTime12(record.clockIn)}</div>
                                </div>
                                <div class="bg-surface-container-low rounded-lg p-2 text-center">
                                    <div class="text-on-surface-variant text-[11px]">Jam Keluar</div>
                                    <div class="font-bold text-slate-900">${formatTime12(record.clockOut)}</div>
                                </div>
                            </div>` : ''}

                            <!-- Bukti Foto & Lokasi Clock In -->
                            ${record && (record.photo || record.location) ? `
                            <div class="mt-2.5 bg-surface-container-low rounded-xl p-3 border border-outline-variant/60">
                                <div class="flex items-center gap-1.5 mb-1.5 text-xs font-bold text-primary uppercase">
                                    <span class="material-symbols-outlined text-[15px]">login</span>
                                    <span>Bukti Clock In</span>
                                </div>
                                ${record.photo ? `
                                <div class="rounded-lg overflow-hidden border border-outline-variant/60 mb-2">
                                    <img src="${resolvePhotoSrc(record.photo)}" class="w-full h-32 object-cover cursor-pointer hover:opacity-95" alt="Foto Clock In" onclick="window.open(this.src, '_blank')">
                                </div>` : ''}
                                ${record.location ? `
                                <div class="text-xs text-slate-600 flex items-start gap-1">
                                    <span class="material-symbols-outlined text-[14px] text-primary shrink-0 mt-0.5">location_on</span>
                                    <span class="line-clamp-2">${escHtml(record.location)}</span>
                                </div>` : ''}
                                ${record.lat && record.lng ? `
                                <div class="mt-1 text-[11px] text-indigo-700 font-mono flex items-center gap-1 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100 w-fit">
                                    <span class="material-symbols-outlined text-[13px]">pin_drop</span>
                                    <span>📍 ${record.lat.toFixed(6)}, ${record.lng.toFixed(6)}</span>
                                </div>` : ''}
                            </div>` : ''}

                            <!-- Bukti Foto & Lokasi Clock Out -->
                            ${record && (record.photoOut || record.locationOut) ? `
                            <div class="mt-2.5 bg-surface-container-low rounded-xl p-3 border border-outline-variant/60">
                                <div class="flex items-center gap-1.5 mb-1.5 text-xs font-bold text-rose-600 uppercase">
                                    <span class="material-symbols-outlined text-[15px]">logout</span>
                                    <span>Bukti Clock Out</span>
                                </div>
                                ${record.photoOut ? `
                                <div class="rounded-lg overflow-hidden border border-outline-variant/60 mb-2">
                                    <img src="${resolvePhotoSrc(record.photoOut)}" class="w-full h-32 object-cover cursor-pointer hover:opacity-95" alt="Foto Clock Out" onclick="window.open(this.src, '_blank')">
                                </div>` : ''}
                                ${record.locationOut ? `
                                <div class="text-xs text-slate-600 flex items-start gap-1">
                                    <span class="material-symbols-outlined text-[14px] text-rose-600 shrink-0 mt-0.5">location_on</span>
                                    <span class="line-clamp-2">${escHtml(record.locationOut)}</span>
                                </div>` : ''}
                                ${record.latOut && record.lngOut ? `
                                <div class="mt-1 text-[11px] text-indigo-700 font-mono flex items-center gap-1 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100 w-fit">
                                    <span class="material-symbols-outlined text-[13px]">pin_drop</span>
                                    <span>📍 ${record.latOut.toFixed(6)}, ${record.lngOut.toFixed(6)}</span>
                                </div>` : ''}
                            </div>` : ''}

                            ${record && record.reason ? `<div class="mt-2 bg-amber-50 border border-amber-200 rounded-lg p-2.5 text-xs text-amber-900"><strong>Alasan / Keterangan:</strong> ${escHtml(record.reason)}</div>` : ''}
                            ${!record ? '<p class="text-xs text-on-surface-variant italic mt-1.5">Intern ini belum melakukan absensi pada hari tersebut.</p>' : ''}
                            <div class="mt-2.5 flex items-center justify-between border-t border-outline-variant/40 pt-2 flex-wrap gap-2">
                                <button type="button" onclick="openAdminSetAttendanceModal('${escHtml(intern.name)}', '${dateStr}', true)" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold flex items-center gap-1 transition shadow-2xs cursor-pointer">
                                    <span class="material-symbols-outlined text-[14px]">edit_calendar</span>
                                    <span>Set / Ubah Kehadiran</span>
                                </button>
                                <a href="../attendance.php?intern=${encodeURIComponent(intern.name)}" class="text-xs text-primary font-bold hover:underline">Lihat kalender &rarr;</a>
                            </div>
                        `;
                    list.appendChild(row);
                });
            }

            const modal = document.getElementById('detail-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDetailModal() {
            document.getElementById('detail-modal').classList.add('hidden');
            document.getElementById('detail-modal').classList.remove('flex');
        }

        document.getElementById('detail-modal').addEventListener('click', function (e) {
            if (e.target === this) closeDetailModal();
        });

        function escHtml(str) {
            return String(str || '').replace(/[&<>'"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[c]));
        }

        // Resolve photo URL: base64 baru bisa langsung dipakai, path lama perlu prefix '../'
        function resolvePhotoSrc(val) {
            if (!val) return '';
            if (val.startsWith('data:')) return val;          // base64 langsung
            return '../' + val;                               // path lama (backward compat)
        }

        function filterRosterTable() {
            const q = (document.getElementById('roster-search')?.value || '').toLowerCase().trim();
            const rows = document.querySelectorAll('#intern-roster-body tr');
            rows.forEach(tr => {
                if (tr.dataset.internName) {
                    const match = tr.dataset.internName.toLowerCase().includes(q) ||
                                  (tr.dataset.division || '').toLowerCase().includes(q);
                    tr.style.display = match ? '' : 'none';
                }
            });
        }

        function renderRoster() {
            // Gunakan HANYA data intern dari database MySQL
            let interns = dbInternsList;
            if (!interns.length && window.DB_INTERNS && window.DB_INTERNS.length) {
                interns = window.DB_INTERNS.map(u => ({ name: u.username, division: 'Intern Kedayweb' }));
            }

            if (document.getElementById('stat-total-interns')) {
                document.getElementById('stat-total-interns').textContent = interns.length;
            }
            const tbody = document.getElementById('intern-roster-body');
            tbody.innerHTML = '';

            if (!interns.length) {
                tbody.innerHTML = '<tr><td colspan="7" class="px-5 py-8 text-center text-on-surface-variant text-sm italic">Belum ada intern terdaftar di database.</td></tr>';
                return;
            }

            const today = getNowWIB();
            const todayStr = fmt(today);
            const isTodayPastCutoff = today.getHours() >= 14;
            const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();

            interns.forEach(i => {
                const dbUserAtt = getInternAttendanceMap(i.name);
                let present = 0, late = 0, permit = 0, absent = 0;

                // Tanggal mulai & selesai magang intern
                const startDateStr = i.intern_start_date || null;
                const endDateStr = i.intern_end_date || null;

                // Hitung dari kalender bulan yang sedang aktif
                for (let d = 1; d <= daysInMonth; d++) {
                    const dateObj = new Date(calYear, calMonth, d);
                    const dateStr = fmt(dateObj);
                    const dow = dateObj.getDay();
                    const isSunday = (dow === 0); // Hanya Minggu yang libur
                    const isHoliday = (dbHolidaysMap && dbHolidaysMap[dateStr]);
                    const isFuture = dateObj > today && dateStr !== todayStr;
                    const isToday = dateStr === todayStr;
                    const isPastDay = !isFuture && !isToday;

                    // Skip hari sebelum tanggal mulai magang intern
                    if (startDateStr && dateStr < startDateStr) continue;

                    // Skip hari setelah tanggal selesai magang intern (tidak dianggap Alpha)
                    if (endDateStr && dateStr > endDateStr) continue;

                    const r = dbUserAtt[dateStr];

                    if (r) {
                        // Jika ada catatan kehadiran (termasuk pada hari Minggu atau Libur), tetap hitung!
                        if (r.status === 'present') present++;
                        else if (r.status === 'late') late++;
                        else if (r.status === 'permit') permit++;
                        else if (r.status === 'absent') absent++;
                    } else if (!isFuture && !isSunday && !isHoliday) {
                        // Jika hari kerja lalu atau hari ini sudah lewat batas jam 14:00 dan tidak ada absen -> dihitung tidak masuk (alpha)
                        if (isPastDay || isTodayPastCutoff) {
                            absent++;
                        }
                    }
                }

                // Tambahkan juga jika ada record explicit absent di luar rentang kalender aktif
                Object.values(dbUserAtt).forEach(r => {
                    const rMonth = parseInt(r.date.split('-')[1], 10) - 1;
                    const rYear = parseInt(r.date.split('-')[0], 10);
                    if (rMonth !== calMonth || rYear !== calYear) {
                        // Jika ada data di bulan lain, hanya hitung jika eksplisit
                    }
                });

                const isInternEnded = i.intern_end_date && (todayStr > i.intern_end_date);
                const statusBadge = isInternEnded 
                    ? `<span class="ml-1.5 px-2 py-0.5 text-[10px] bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 rounded-md font-bold border border-slate-200 dark:border-slate-700 inline-flex items-center gap-0.5"><span class="material-symbols-outlined text-[11px]">task_alt</span> Selesai</span>` 
                    : '';

                const tr = document.createElement('tr');
                tr.className = 'att-row border-b border-outline-variant/60';
                tr.dataset.internName = i.name;
                tr.dataset.division = i.division || 'Intern Kedayweb';
                tr.innerHTML = `
                    <td class="px-3.5 sm:px-5 py-3 font-semibold text-on-surface">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-primary-container text-on-primary-container font-bold flex items-center justify-center text-xs shrink-0">
                                ${escHtml(i.name.charAt(0).toUpperCase())}
                            </div>
                            <div class="flex items-center flex-wrap gap-1">
                                <span class="font-bold text-xs sm:text-sm">${escHtml(i.name)}</span>
                                ${statusBadge}
                            </div>
                        </div>
                    </td>
                    <td class="px-3.5 sm:px-5 py-3 text-on-surface-variant font-medium text-xs">${escHtml(i.division || 'Intern Kedayweb')}</td>
                    <td class="px-3.5 sm:px-5 py-3 text-center text-green-700 dark:text-green-400 font-bold text-sm">${present}</td>
                    <td class="px-3.5 sm:px-5 py-3 text-center text-amber-700 dark:text-amber-400 font-bold text-sm">${late}</td>
                    <td class="px-3.5 sm:px-5 py-3 text-center text-purple-700 dark:text-purple-400 font-bold text-sm">${permit}</td>
                    <td class="px-3.5 sm:px-5 py-3 text-center text-red-700 dark:text-red-400 font-bold text-sm">${absent}</td>
                    <td class="px-3.5 sm:px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="openAdminSetAttendanceModal('${escHtml(i.name)}')" class="px-2.5 py-1 bg-surface-container-high hover:bg-emerald-600 hover:text-white text-on-surface rounded-lg text-xs font-semibold transition flex items-center gap-1 cursor-pointer border border-outline-variant/60 shadow-2xs" title="Atur kehadiran intern ini">
                                <span class="material-symbols-outlined text-[15px]">edit_calendar</span>
                                <span>Set Absen</span>
                            </button>
                            <a href="../attendance.php?intern=${encodeURIComponent(i.name)}" class="px-2.5 py-1 bg-surface-container-low hover:bg-primary hover:text-white text-primary rounded-lg text-xs font-semibold transition flex items-center gap-1 border border-outline-variant/60 shadow-2xs" title="Lihat Kalender Personal">
                                <span class="material-symbols-outlined text-[15px]">calendar_month</span>
                                <span>Kalender</span>
                            </a>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            filterRosterTable();
        }

        function renderAll() {
            renderCalendar();
            renderRoster();
        }

        function exportAllAdminAttendanceCSV() {
            let interns = dbInternsList;
            if (!interns.length && window.DB_INTERNS && window.DB_INTERNS.length) {
                interns = window.DB_INTERNS.map(u => ({ name: u.username, division: 'Intern Kedayweb' }));
            }

            if (!interns.length) {
                alert('Tidak ada data intern.');
                return;
            }

            const today = getNowWIB();
            const todayStr = fmt(today);
            const isTodayPastCutoff = today.getHours() >= 14;
            const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
            const targetMonthPrefix = `${calYear}-${String(calMonth + 1).padStart(2, '0')}`;

            const statusLabel = {
                present: 'Hadir',
                late: 'Terlambat',
                absent: 'Tidak Masuk',
                permit: 'Izin / Dispensasi'
            };

            const allRecords = [];

            // 1. Kumpulkan data setiap hari dalam bulan aktif untuk semua intern
            for (let d = 1; d <= daysInMonth; d++) {
                const dateObj = new Date(calYear, calMonth, d);
                const dateStr = fmt(dateObj);
                const dow = dateObj.getDay();
                const isSunday = (dow === 0); // Minggu libur
                const isHoliday = (dbHolidaysMap && dbHolidaysMap[dateStr]);
                const isFuture = dateObj > today && dateStr !== todayStr;
                const isToday = dateStr === todayStr;
                const isPastDay = !isFuture && !isToday;

                interns.forEach(intern => {
                    const dbUserAtt = dbAttendanceMap[intern.name] || {};
                    const startDateStr = intern.intern_start_date || null;
                    const endDateStr = intern.intern_end_date || null;

                    if (startDateStr && dateStr < startDateStr) return;
                    if (endDateStr && dateStr > endDateStr) return;

                    const r = dbUserAtt[dateStr];

                    if (r) {
                        let stLabel = statusLabel[r.status] || r.status;
                        if (r.status === 'permit') {
                            const lt = (r.leaveType || '').toLowerCase();
                            if (lt === 'dispen' || lt === 'dispensasi') stLabel = 'Dispensasi Instansi';
                            else if (lt === 'sakit') stLabel = 'Sakit';
                            else if (lt === 'izin') stLabel = 'Izin Keperluan';
                            else if (lt === 'dinas') stLabel = 'Tugas Luar / Dinas';
                            else stLabel = 'Izin / Dispensasi';
                        }
                        allRecords.push({
                            internName: intern.name,
                            division: intern.division || 'Intern Kedayweb',
                            date: dateStr,
                            clockIn: r.clockIn || '--:--',
                            clockOut: r.clockOut || '--:--',
                            status: stLabel,
                            reason: r.reason || '-'
                        });
                    } else if (isHoliday) {
                        allRecords.push({
                            internName: intern.name,
                            division: intern.division || 'Intern Kedayweb',
                            date: dateStr,
                            clockIn: '--:--',
                            clockOut: '--:--',
                            status: `Libur (${isHoliday.title || 'Tanggal Merah'})`,
                            reason: isHoliday.description || 'Hari libur resmi'
                        });
                    } else if (!isSunday && !isFuture) {
                        // Hari kerja tanpa presensi (Alpha / Belum Absen)
                        if (isPastDay || isTodayPastCutoff) {
                            allRecords.push({
                                internName: intern.name,
                                division: intern.division || 'Intern Kedayweb',
                                date: dateStr,
                                clockIn: '--:--',
                                clockOut: '--:--',
                                status: 'Tidak Masuk',
                                reason: 'Tidak melakukan absensi (Alpha)'
                            });
                        } else if (isToday) {
                            allRecords.push({
                                internName: intern.name,
                                division: intern.division || 'Intern Kedayweb',
                                date: dateStr,
                                clockIn: '--:--',
                                clockOut: '--:--',
                                status: 'Belum Absen',
                                reason: 'Belum melakukan absensi hari ini'
                            });
                        }
                    }
                });
            }

            // Filter ketat HANYA data pada bulan dan tahun yang sedang aktif
            const exportRecords = allRecords.filter(item => item.date && item.date.startsWith(targetMonthPrefix));

            if (!exportRecords.length) {
                alert('Belum ada data kehadiran untuk bulan yang dipilih.');
                return;
            }

            // Urutkan berdasarkan tanggal descending, lalu nama intern
            exportRecords.sort((a, b) => {
                if (a.date !== b.date) return b.date.localeCompare(a.date);
                return a.internName.localeCompare(b.internName);
            });

            const rows = [['Nama Intern', 'Divisi', 'Tanggal', 'Jam Masuk', 'Jam Keluar', 'Status', 'Alasan']];

            exportRecords.forEach(r => {
                const [y, m, d] = r.date.split('-');
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const dateObj = new Date(+y, +m - 1, +d);
                const cleanDate = `${days[dateObj.getDay()]} ${String(d).padStart(2, '0')} ${months[+m - 1]} ${y}`;

                rows.push([
                    r.internName,
                    r.division,
                    cleanDate,
                    r.clockIn,
                    r.clockOut,
                    r.status,
                    r.reason
                ]);
            });

            const csvRows = rows.map(row =>
                row.map(val => {
                    const str = String(val || '').replace(/"/g, '""');
                    return `"${str}"`;
                }).join(';')
            );

            const csvContent = "sep=;\r\n" + csvRows.join('\r\n');

            const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `Kehadiran_Semua_Intern_${calYear}_${String(calMonth + 1).padStart(2, '0')}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }

        document.addEventListener('DOMContentLoaded', async () => {
            if (window.ProjectStore) ProjectStore.setRole('admin');
            initCalendar();

            // Pasang listener perubahan intern / tanggal pada modal set kehadiran
            document.getElementById('admin-set-username')?.addEventListener('change', refreshAdminSetModalFields);
            document.getElementById('admin-set-date')?.addEventListener('change', refreshAdminSetModalFields);

            // Render dulu kalender kosong, lalu setelah DB data masuk re-render
            renderAll();
            await fetchDbAttendance();
            renderAll(); // re-render setelah data DB tersedia
        });
    
        // ============================================================
        // FITUR ADMIN SET / OVERRIDE KEHADIRAN & DISPENSASI INTERN
        // ============================================================
        let currentAdminSelectedStatus = 'present';

        function selectAdminStatus(status) {
            currentAdminSelectedStatus = status;
            const cards = ['present', 'late', 'permit', 'absent', 'holiday'];
            cards.forEach(s => {
                const card = document.getElementById(`admin-status-card-${s}`);
                if (!card) return;
                const radio = card.querySelector('input[type="radio"]');
                const isColSpan2 = (s === 'holiday');
                const colPrefix = isColSpan2 ? 'col-span-2 ' : '';
                if (s === status) {
                    if (radio) radio.checked = true;
                    if (s === 'present') card.className = `${colPrefix}flex items-center gap-2 border-2 border-emerald-400 bg-emerald-50/70 dark:bg-emerald-950/40 rounded-xl p-2.5 cursor-pointer transition select-none`;
                    else if (s === 'late') card.className = `${colPrefix}flex items-center gap-2 border-2 border-amber-400 bg-amber-50/70 dark:bg-amber-950/40 rounded-xl p-2.5 cursor-pointer transition select-none`;
                    else if (s === 'permit') card.className = `${colPrefix}flex items-center gap-2 border-2 border-purple-400 bg-purple-50/70 dark:bg-purple-950/40 rounded-xl p-2.5 cursor-pointer transition select-none`;
                    else if (s === 'absent') card.className = `${colPrefix}flex items-center gap-2 border-2 border-red-400 bg-red-50/70 dark:bg-red-950/40 rounded-xl p-2.5 cursor-pointer transition select-none`;
                    else if (s === 'holiday') card.className = `${colPrefix}flex items-center gap-2 border-2 border-rose-400 bg-rose-50/70 dark:bg-rose-950/40 rounded-xl p-2.5 cursor-pointer transition select-none`;
                } else {
                    if (radio) radio.checked = false;
                    card.className = `${colPrefix}flex items-center gap-2 border-2 border-outline-variant bg-surface-container-lowest rounded-xl p-2.5 cursor-pointer transition select-none`;
                }
            });

            // Toggle sub-type dispensasi, jam, dan info libur
            const leaveWrap = document.getElementById('admin-leave-type-wrap');
            const timeFields = document.getElementById('admin-time-fields');
            const holidayWrap = document.getElementById('admin-holiday-info-wrap');
            const reasonInput = document.getElementById('admin-set-reason');
            const reasonLabel = reasonInput?.previousElementSibling;

            if (status === 'permit') {
                if (leaveWrap) leaveWrap.classList.remove('hidden');
                if (timeFields) timeFields.classList.add('hidden');
                if (holidayWrap) holidayWrap.classList.add('hidden');
                if (reasonLabel) reasonLabel.textContent = 'Alasan / Keterangan Dispensasi';
                if (reasonInput) reasonInput.placeholder = 'Contoh: Mengikuti kegiatan seminar kampus...';
            } else if (status === 'absent') {
                if (leaveWrap) leaveWrap.classList.add('hidden');
                if (timeFields) timeFields.classList.add('hidden');
                if (holidayWrap) holidayWrap.classList.add('hidden');
                if (reasonLabel) reasonLabel.textContent = 'Alasan Tidak Masuk / Alpha';
                if (reasonInput) reasonInput.placeholder = 'Contoh: Sakit tanpa surat dokter / kendala pribadi...';
            } else if (status === 'holiday') {
                if (leaveWrap) leaveWrap.classList.add('hidden');
                if (timeFields) timeFields.classList.add('hidden');
                if (holidayWrap) holidayWrap.classList.remove('hidden');
                if (reasonLabel) reasonLabel.textContent = 'Nama / Keterangan Libur (Contoh: Libur Nasional / Cuti Bersama)';
                if (reasonInput) reasonInput.placeholder = 'Contoh: Hari Kemerdekaan RI / Libur Bersama Idul Fitri...';
            } else {
                if (leaveWrap) leaveWrap.classList.add('hidden');
                if (timeFields) timeFields.classList.remove('hidden');
                if (holidayWrap) holidayWrap.classList.add('hidden');
                if (reasonLabel) reasonLabel.textContent = 'Alasan / Keterangan Dispensasi / Catatan';
                if (reasonInput) reasonInput.placeholder = 'Contoh: Dispensasi kegiatan seminar kampus, atau lupa absen...';
            }
        }

        function refreshAdminSetModalFields() {
            const userInput = document.getElementById('admin-set-username');
            const dateInput = document.getElementById('admin-set-date');
            const deleteBtn = document.getElementById('admin-delete-att-btn');
            if (!userInput || !dateInput) return;

            const selectedUser = userInput.value;
            const targetDate = dateInput.value;
            if (!selectedUser || !targetDate) return;

            const userAtt = getInternAttendanceMap(selectedUser);
            const existingRecord = userAtt[targetDate] || null;

            if (existingRecord) {
                selectAdminStatus(existingRecord.status || 'present');
                if (existingRecord.clockIn) document.getElementById('admin-set-clock-in').value = existingRecord.clockIn;
                else document.getElementById('admin-set-clock-in').value = (existingRecord.status === 'present' || existingRecord.status === 'late') ? '08:00' : '';
                if (existingRecord.clockOut) document.getElementById('admin-set-clock-out').value = existingRecord.clockOut;
                else document.getElementById('admin-set-clock-out').value = '';
                document.getElementById('admin-set-reason').value = existingRecord.reason || '';
                if (existingRecord.leaveType) {
                    const rdo = document.querySelector(`input[name="admin_leave_type"][value="${existingRecord.leaveType}"]`);
                    if (rdo) rdo.checked = true;
                }
                if (deleteBtn) deleteBtn.classList.remove('hidden');
            } else if (dbHolidaysMap && dbHolidaysMap[targetDate]) {
                selectAdminStatus('holiday');
                document.getElementById('admin-set-reason').value = dbHolidaysMap[targetDate].title || '';
                if (deleteBtn) deleteBtn.classList.add('hidden');
            } else {
                selectAdminStatus('present');
                document.getElementById('admin-set-clock-in').value = '08:00';
                document.getElementById('admin-set-clock-out').value = '';
                document.getElementById('admin-set-reason').value = '';
                if (deleteBtn) deleteBtn.classList.add('hidden');
            }
        }

        function openAdminSetAttendanceModal(optUsername = '', optDate = '', fromDetail = false) {
            const modal = document.getElementById('admin-set-attendance-modal');
            if (!modal) return;

            // Simpan tanggal agar setelah simpan atau batal otomatis kembali ke modal rincian
            returnToDetailDate = fromDetail ? (optDate || lastOpenedDetailDate || null) : null;
            if (fromDetail) {
                closeDetailModal();
            }

            const userInput = document.getElementById('admin-set-username');
            const dateInput = document.getElementById('admin-set-date');
            const msgEl = document.getElementById('admin-set-msg');
            if (msgEl) { msgEl.className = 'hidden'; msgEl.textContent = ''; }

            const todayStr = fmt(getNowWIB());
            const targetDate = optDate || todayStr;
            if (dateInput) dateInput.value = targetDate;

            if (userInput) {
                const targetUser = optUsername || (dbInternsList[0]?.name || '');
                let matched = false;
                for (let i = 0; i < userInput.options.length; i++) {
                    if (userInput.options[i].value.toLowerCase() === targetUser.toLowerCase()) {
                        userInput.selectedIndex = i;
                        matched = true;
                        break;
                    }
                }
                if (!matched) userInput.value = targetUser;
            }

            refreshAdminSetModalFields();

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeAdminSetAttendanceModal(cancelled = false) {
            const modal = document.getElementById('admin-set-attendance-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
            if (cancelled && returnToDetailDate) {
                const backDate = returnToDetailDate;
                returnToDetailDate = null;
                openDetailModal(backDate);
            }
        }

        async function handleAdminSetAttendanceSubmit(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('admin-set-submit-btn');
            const msgEl = document.getElementById('admin-set-msg');
            const username = document.getElementById('admin-set-username').value.trim();
            const date = document.getElementById('admin-set-date').value.trim();
            const status = currentAdminSelectedStatus;
            const clockIn = document.getElementById('admin-set-clock-in').value.trim();
            const clockOut = document.getElementById('admin-set-clock-out').value.trim();
            const reason = document.getElementById('admin-set-reason').value.trim();
            const leaveType = document.querySelector('input[name="admin_leave_type"]:checked')?.value || 'dispen';

            if (!username || !date) {
                alert('Nama intern dan tanggal kehadiran wajib diisi!');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="material-symbols-outlined text-[16px] animate-spin">refresh</span> Menyimpan...';

            try {
                const res = await fetch('../attendance-api.php?action=admin_set_attendance', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        username,
                        date,
                        status,
                        leave_type: (status === 'permit') ? leaveType : null,
                        clock_in: (status === 'present' || status === 'late') ? clockIn : null,
                        clock_out: (status === 'present' || status === 'late') ? clockOut : null,
                        reason
                    })
                });

                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data.error || 'Gagal menyimpan status kehadiran');

                // Tampilkan sukses
                msgEl.className = 'text-xs font-semibold p-2.5 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 block';
                msgEl.textContent = data.message || 'Status kehadiran berhasil diperbarui!';

                // Refresh data
                await fetchDbAttendance();
                renderAll();

                setTimeout(() => {
                    const targetDetailDate = returnToDetailDate || date;
                    const shouldReturn = Boolean(returnToDetailDate);
                    returnToDetailDate = null;
                    closeAdminSetAttendanceModal(false);

                    // Kembali ke modal Rincian Kehadiran untuk tanggal tersebut
                    if (shouldReturn && targetDetailDate) {
                        openDetailModal(targetDetailDate);
                    }
                }, 400);
            } catch (err) {
                msgEl.className = 'text-xs font-semibold p-2.5 rounded-xl bg-red-50 text-red-800 border border-red-200 block';
                msgEl.textContent = err.message || 'Terjadi kesalahan saat menyimpan.';
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span class="material-symbols-outlined text-[16px]">save</span> Simpan Kehadiran';
            }
        }

        async function deleteCurrentDayAttendance() {
            const username = document.getElementById('admin-set-username').value.trim();
            const date = document.getElementById('admin-set-date').value.trim();
            if (!username || !date) return;

            if (!confirm(`Hapus catatan kehadiran untuk ${username} pada tanggal ${date}?`)) return;

            try {
                const res = await fetch('../attendance-api.php?action=admin_delete_attendance', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, date })
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data.error || 'Gagal menghapus catatan');

                await fetchDbAttendance();
                renderAll();

                const targetDetailDate = returnToDetailDate || date;
                const shouldReturn = Boolean(returnToDetailDate);
                returnToDetailDate = null;
                closeAdminSetAttendanceModal(false);

                if (shouldReturn && targetDetailDate) {
                    openDetailModal(targetDetailDate);
                }
            } catch (err) {
                alert(err.message || 'Gagal menghapus catatan');
            }
        }

        // ============================================================
        // MODAL & FITUR KELOLA HARI LIBUR / TANGGAL MERAH
        // ============================================================
        function openHolidayModal(optDate = null) {
            const modal = document.getElementById('holiday-modal');
            if (!modal) return;
            const inputDate = document.getElementById('holiday-input-date');
            const inputTitle = document.getElementById('holiday-input-title');
            const inputDesc = document.getElementById('holiday-input-desc');
            const msgEl = document.getElementById('holiday-form-msg');
            if (msgEl) { msgEl.className = 'hidden'; msgEl.textContent = ''; }

            const todayStr = fmt(getNowWIB());
            const targetDate = optDate || todayStr;
            if (inputDate) inputDate.value = targetDate;
            if (inputTitle) inputTitle.value = '';
            if (inputDesc) inputDesc.value = '';

            renderHolidayList();

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        let returnToDetailHolidayDate = null;

        function openHolidayModalWithDate(dateStr) {
            returnToDetailHolidayDate = dateStr;
            closeDetailModal();
            openHolidayModal(dateStr);
        }

        function closeHolidayModal(cancelled = false) {
            const modal = document.getElementById('holiday-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
            if (cancelled && returnToDetailHolidayDate) {
                const backDate = returnToDetailHolidayDate;
                returnToDetailHolidayDate = null;
                openDetailModal(backDate);
            }
        }

        function renderHolidayList() {
            const container = document.getElementById('holiday-list-container');
            const badge = document.getElementById('holiday-count-badge');
            if (!container) return;

            const allHolidays = Object.values(dbHolidaysMap || {}).sort((a, b) => a.date.localeCompare(b.date));
            if (badge) badge.textContent = `${allHolidays.length} Hari`;

            if (allHolidays.length === 0) {
                container.innerHTML = '<p class="text-xs text-on-surface-variant italic py-3 text-center">Belum ada hari libur yang terdaftar.</p>';
                return;
            }

            container.innerHTML = allHolidays.map(h => `
                <div class="flex items-center justify-between gap-2 p-2.5 rounded-xl border border-outline-variant bg-surface-container-lowest">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-950/70 text-rose-600 dark:text-rose-300 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[18px]">event_busy</span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-on-surface truncate">${escHtml(h.title || 'Hari Libur')}</div>
                            <div class="text-[11px] text-rose-600 dark:text-rose-400 font-semibold">${formatDateDisplay(h.date)}</div>
                            ${h.description ? `<div class="text-[10px] text-on-surface-variant truncate">${escHtml(h.description)}</div>` : ''}
                        </div>
                    </div>
                    <button type="button" onclick="deleteHolidayByDate('${h.date}')" class="px-2.5 py-1 text-[11px] font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50 rounded-lg border border-red-200 dark:border-red-900 transition shrink-0 cursor-pointer" title="Hapus hari libur">
                        Hapus
                    </button>
                </div>
            `).join('');
        }

        async function handleSaveHolidaySubmit(e) {
            e.preventDefault();
            const btn = document.getElementById('holiday-submit-btn');
            const msgEl = document.getElementById('holiday-form-msg');
            const date = document.getElementById('holiday-input-date').value.trim();
            const title = document.getElementById('holiday-input-title').value.trim();
            const desc = document.getElementById('holiday-input-desc').value.trim();

            if (!date || !title) {
                alert('Tanggal libur dan judul wajib diisi!');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined text-[16px] animate-spin">refresh</span> Menyimpan...';

            try {
                const res = await fetch('../attendance-api.php?action=save_holiday', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ date, title, description: desc })
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data.error || 'Gagal menyimpan hari libur');

                dbHolidaysMap[date] = { date, title, description: desc };

                msgEl.className = 'text-xs font-semibold p-2 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 block';
                msgEl.textContent = data.message || 'Hari libur berhasil disimpan!';

                document.getElementById('holiday-input-title').value = '';
                document.getElementById('holiday-input-desc').value = '';

                await fetchDbAttendance();
                renderAll();
                renderHolidayList();

                if (returnToDetailHolidayDate) {
                    const backDate = returnToDetailHolidayDate;
                    returnToDetailHolidayDate = null;
                    closeHolidayModal(false);
                    openDetailModal(backDate);
                } else {
                    setTimeout(() => {
                        if (msgEl) msgEl.className = 'hidden';
                    }, 2500);
                }
            } catch (err) {
                msgEl.className = 'text-xs font-semibold p-2 rounded-lg bg-red-50 text-red-800 border border-red-200 block';
                msgEl.textContent = err.message || 'Terjadi kesalahan.';
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined text-[16px]">save</span> Simpan Hari Libur';
            }
        }

        async function deleteHolidayByDate(dateStr) {
            const h = dbHolidaysMap[dateStr];
            const name = h ? h.title : dateStr;
            if (!confirm(`Yakin ingin menghapus hari libur '${name}' (${dateStr})? Semua intern akan kembali mengikuti jadwal absensi normal.`)) {
                return;
            }

            try {
                const res = await fetch('../attendance-api.php?action=delete_holiday', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ date: dateStr })
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data.error || 'Gagal menghapus hari libur');

                delete dbHolidaysMap[dateStr];

                await fetchDbAttendance();
                renderAll();
                renderHolidayList();

                // Jika modal detail sedang terbuka, refresh kontennya
                const detailModal = document.getElementById('detail-modal');
                if (detailModal && !detailModal.classList.contains('hidden')) {
                    openDetailModal(dateStr);
                }
            } catch (err) {
                alert('Gagal menghapus: ' + err.message);
            }
        }

    </script>
</body>

</html>