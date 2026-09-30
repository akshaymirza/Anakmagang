<?php require_once __DIR__ . '/session.php';
require_login(); ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Kedayweb - Kehadiran PKL</title>
    <meta name="description"
        content="Pantau kehadiran PKL Anda — kalender interaktif, status kehadiran, dan riwayat lengkap." />
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="shared-config.js"></script>
    <script src="intern-store.js"></script>
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

        /* Calendar */
        .cal-cell {
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.15s ease;
            position: relative;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1.5px solid transparent;
        }

        .cal-cell:hover {
            transform: scale(1.07);
            border-color: var(--color-primary, #2563eb);
        }

        .cal-cell.today {
            box-shadow: 0 0 0 2px #2563eb;
        }

        .cal-cell.status-present {
            background: #dcfce7;
            color: #166534;
        }

        .cal-cell.status-late {
            background: #fef9c3;
            color: #854d0e;
        }

        .cal-cell.status-absent {
            background: #fee2e2;
            color: #991b1b;
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
        }

        .cal-cell.future:hover {
            transform: none;
            border-color: transparent;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            position: absolute;
            bottom: 5px;
        }

        /* Badge */
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

        /* Modal backdrop */
        .modal-backdrop {
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
        }

        /* Stat card gradient */
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

        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Table row hover */
        .att-row {
            transition: background 0.12s;
        }

        .att-row:hover {
            background: #f8fafc;
        }

        /* Toast */
        #att-toast {
            transition: opacity 0.3s, transform 0.3s;
        }

        #att-toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        #att-toast.hide {
            opacity: 0;
            transform: translateY(12px);
        }

        /* Streak badge */
        .streak-badge {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>

<body class="bg-surface text-on-surface font-body-md min-h-screen flex">

    <!-- SideNavBar -->
    <?php $active = 'attendance';
    include 'partials/sidebar-intern.php'; ?>

    <?php include 'partials/topnav-mobile.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 md:ml-[16.5rem] pt-20 md:pt-0 min-h-screen flex flex-col">
        <div class="flex-1 pb-10 px-4 md:px-8 max-w-[1400px] mx-auto w-full">

        <!-- Page Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 mt-6 md:mt-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-primary mb-1" id="page-eyebrow">PKL Tracker
                </p>
                <h2 class="font-geist text-3xl md:text-4xl font-bold text-on-surface" id="page-title">Kehadiran Saya
                </h2>
                <p class="text-sm text-on-surface-variant mt-1" id="page-subtitle">Pantau presensi, status kedatangan,
                    dan riwayat selama masa PKL.</p>
            </div>
            <div class="flex gap-2 w-full md:w-auto">
                <button id="main-input-btn" onclick="attStartCapture()"
                    class="flex-1 md:flex-none flex items-center justify-center gap-2 bg-primary text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:opacity-90 transition-all shadow-sm active:scale-95">
                    <span class="material-symbols-outlined text-[18px]"
                        style="font-variation-settings:'FILL' 1;">photo_camera</span>
                    Clock In
                </button>
                <button onclick="exportAttendanceCSV()"
                    class="flex-1 md:flex-none flex items-center justify-center gap-2 border border-outline-variant text-on-surface bg-surface-container-lowest px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-surface-container-low transition-colors">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    Export CSV
                </button>
            </div>
        </div>

        <!-- Tracker mode info banner -->
        <div
            class="mb-6 flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-900 px-4 py-3 rounded-xl text-sm shadow-sm">
            <span class="material-symbols-outlined text-blue-600 text-[20px]">info</span>
            <span>Halaman ini berfungsi sebagai <strong>Tracker Kehadiran</strong>. Pengeditan dan pencatatan data
                kehadiran dilakukan oleh <strong>Admin</strong>.</span>
        </div>

        <!-- Admin preview banner (hanya tampil jika dibuka via ?intern=Nama oleh Admin) -->
        <div id="admin-preview-banner"
            class="hidden mb-6 flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3 rounded-xl text-sm">
            <span class="material-symbols-outlined text-amber-600">visibility</span>
            <span>Anda (Admin) sedang melihat kehadiran milik <strong id="admin-preview-name"></strong> dalam mode
                baca-saja.</span>
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
            <div class="stat-present rounded-2xl p-4 border border-green-200">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-green-700 text-[20px]"
                        style="font-variation-settings:'FILL' 1;">check_circle</span>
                    <span class="text-xs font-bold uppercase tracking-wider text-green-700">Hadir</span>
                </div>
                <div id="stat-present" class="text-3xl font-geist font-bold text-green-800">0</div>
                <div class="text-xs text-green-700 mt-0.5">hari</div>
            </div>
            <div class="stat-late rounded-2xl p-4 border border-amber-200">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-amber-700 text-[20px]"
                        style="font-variation-settings:'FILL' 1;">schedule</span>
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-700">Terlambat</span>
                </div>
                <div id="stat-late" class="text-3xl font-geist font-bold text-amber-800">0</div>
                <div class="text-xs text-amber-700 mt-0.5">hari</div>
            </div>
            <div class="stat-absent rounded-2xl p-4 border border-red-200">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-red-700 text-[20px]"
                        style="font-variation-settings:'FILL' 1;">cancel</span>
                    <span class="text-xs font-bold uppercase tracking-wider text-red-700">Tidak Masuk</span>
                </div>
                <div id="stat-absent" class="text-3xl font-geist font-bold text-red-800">0</div>
                <div class="text-xs text-red-700 mt-0.5">hari</div>
            </div>
            <div class="stat-rate rounded-2xl p-4 border border-blue-200">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-blue-700 text-[20px]"
                        style="font-variation-settings:'FILL' 1;">insights</span>
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-700">Kehadiran</span>
                </div>
                <div id="stat-rate" class="text-3xl font-geist font-bold text-blue-800">0%</div>
                <div class="text-xs text-blue-700 mt-0.5">tingkat hadir</div>
            </div>
        </div>

        <!-- Main Bento Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">

            <!-- Left: Streak + Legend -->
            <div class="lg:col-span-4 flex flex-col gap-4">

                <!-- Streak Card -->
                <div
                    class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-5 flex flex-col items-center text-center">
                    <span class="material-symbols-outlined text-[44px] text-amber-400 mb-2"
                        style="font-variation-settings:'FILL' 1;">local_fire_department</span>
                    <div id="streak-count" class="font-geist text-5xl font-black streak-badge mb-1">0</div>
                    <p class="font-semibold text-on-surface mb-0.5">Hari Berturut-turut</p>
                    <p id="streak-desc" class="text-xs text-on-surface-variant">Belum ada data kehadiran</p>
                </div>

                <!-- Legend Card -->
                <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-5">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-3">Keterangan
                        Kalender</h3>
                    <div class="space-y-2.5">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center text-green-700 text-xs font-bold">
                                12</div>
                            <div>
                                <div class="text-sm font-semibold text-on-surface">Hadir Tepat Waktu</div>
                                <div class="text-xs text-on-surface-variant">Masuk sebelum pukul 09:00</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-700 text-xs font-bold">
                                12</div>
                            <div>
                                <div class="text-sm font-semibold text-on-surface">Terlambat</div>
                                <div class="text-xs text-on-surface-variant">Masuk setelah pukul 09:00</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center text-red-700 text-xs font-bold">
                                12</div>
                            <div>
                                <div class="text-sm font-semibold text-on-surface">Tidak Masuk</div>
                                <div class="text-xs text-on-surface-variant">Izin / Sakit / Alpha</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 text-xs font-bold">
                                12</div>
                            <div>
                                <div class="text-sm font-semibold text-on-surface">Akhir Pekan / Kosong</div>
                                <div class="text-xs text-on-surface-variant">Weekend atau belum diisi</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick summary for current month -->
                <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-5">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-3">Bulan Ini</h3>
                    <div class="space-y-2" id="monthly-summary">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-on-surface-variant">Hadir</span>
                            <span id="month-present" class="font-bold text-green-700">0 hari</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-on-surface-variant">Terlambat</span>
                            <span id="month-late" class="font-bold text-amber-700">0 hari</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-on-surface-variant">Tidak Masuk</span>
                            <span id="month-absent" class="font-bold text-red-700">0 hari</span>
                        </div>
                        <div class="h-px bg-outline-variant my-1"></div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-on-surface-variant font-semibold">Total Hari Kerja</span>
                            <span id="month-total" class="font-bold text-on-surface">0 hari</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Calendar -->
            <div class="lg:col-span-8 bg-surface-container-lowest rounded-2xl border border-outline-variant p-5">
                <!-- Calendar Header -->
                <div class="flex justify-between items-center mb-5">
                    <h3 id="cal-title" class="font-geist text-xl font-bold text-on-surface">September 2026</h3>
                    <div class="flex items-center gap-1">
                        <button id="cal-prev" onclick="changeMonth(-1)"
                            class="p-2 rounded-lg hover:bg-surface-container-low transition-colors text-on-surface-variant hover:text-primary">
                            <span class="material-symbols-outlined">chevron_left</span>
                        </button>
                        <button onclick="goToday()"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary-container text-on-primary-container hover:opacity-80 transition-opacity">
                            Hari Ini
                        </button>
                        <button id="cal-next" onclick="changeMonth(1)"
                            class="p-2 rounded-lg hover:bg-surface-container-low transition-colors text-on-surface-variant hover:text-primary">
                            <span class="material-symbols-outlined">chevron_right</span>
                        </button>
                    </div>
                </div>
                <!-- Day Headers -->
                <div class="grid grid-cols-7 gap-1.5 mb-2 text-center">
                    <div class="text-xs font-bold uppercase tracking-wider text-red-400">Min</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Sen</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Sel</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Rab</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Kam</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Jum</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Sab</div>
                </div>
                <!-- Calendar Grid -->
                <div id="cal-grid" class="grid grid-cols-7 gap-1.5"></div>
            </div>
        </div>

        <!-- History Table -->
        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant overflow-hidden">
            <div
                class="p-5 border-b border-outline-variant flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div>
                    <h3 class="font-geist text-lg font-bold text-on-surface">Riwayat Kehadiran</h3>
                    <p class="text-xs text-on-surface-variant mt-0.5">Semua catatan kehadiran selama PKL</p>
                </div>
                <!-- Filter -->
                <div
                    class="flex items-center gap-1.5 bg-surface-container-low rounded-xl p-1 border border-outline-variant text-xs">
                    <button onclick="setFilter('all')" id="filter-all"
                        class="px-3 py-1.5 rounded-lg font-semibold bg-white text-primary shadow-sm">Semua</button>
                    <button onclick="setFilter('present')" id="filter-present"
                        class="px-3 py-1.5 rounded-lg font-semibold text-on-surface-variant hover:text-green-700">Hadir</button>
                    <button onclick="setFilter('late')" id="filter-late"
                        class="px-3 py-1.5 rounded-lg font-semibold text-on-surface-variant hover:text-amber-700">Terlambat</button>
                    <button onclick="setFilter('absent')" id="filter-absent"
                        class="px-3 py-1.5 rounded-lg font-semibold text-on-surface-variant hover:text-red-700">Tidak
                        Masuk</button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-surface-container-low border-b border-outline-variant">
                            <th
                                class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                Tanggal</th>
                            <th
                                class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                Jam Masuk</th>
                            <th
                                class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                Jam Keluar</th>
                            <th
                                class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                Status</th>
                            <th
                                class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                Alasan / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="att-table-body" class="divide-y divide-outline-variant">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
                <div id="att-empty" class="hidden text-center py-16">
                    <span class="material-symbols-outlined text-5xl text-on-surface-variant mb-3">event_busy</span>
                    <p class="font-semibold text-on-surface">Belum ada catatan kehadiran</p>
                    <p class="text-xs text-on-surface-variant mt-1">Data kehadiran diperbarui oleh Admin</p>
                </div>
            </div>
        </div>
        </div>
        <div class="mt-auto shrink-0 w-full">
            <?php include 'partials/footer.php'; ?>
        </div>
    </main>

    <!-- ============================================================
     INPUT MODAL (Tambah Kehadiran)
     ============================================================ -->
    <div id="input-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-backdrop">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl border border-outline-variant overflow-hidden">
            <div
                class="p-5 border-b border-outline-variant flex justify-between items-center bg-surface-container-lowest">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary"
                        style="font-variation-settings:'FILL' 1;">edit_calendar</span>
                    <h3 class="font-geist font-bold text-on-surface" id="input-modal-title">Catat Kehadiran</h3>
                </div>
                <button onclick="closeInputModal()"
                    class="text-on-surface-variant hover:text-primary transition-colors p-1 rounded-lg hover:bg-surface-container-low">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="att-form" onsubmit="saveAttendance(event)" class="p-5 space-y-4">
                <!-- Date -->
                <div>
                    <label
                        class="block text-xs font-bold text-on-surface uppercase tracking-wide mb-1.5">Tanggal</label>
                    <input type="date" id="att-date" required
                        class="w-full rounded-xl border border-outline-variant bg-surface-bright px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:outline-none" />
                </div>
                <!-- Status -->
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wide mb-1.5">Status
                        Kehadiran</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label id="status-btn-present" onclick="selectStatus('present')"
                            class="flex flex-col items-center gap-1 border-2 border-outline-variant rounded-xl p-3 cursor-pointer hover:border-green-400 transition-all">
                            <span class="material-symbols-outlined text-green-600 text-[22px]"
                                style="font-variation-settings:'FILL' 1;">check_circle</span>
                            <span class="text-xs font-bold text-green-700">Hadir</span>
                            <input type="radio" name="status" value="present" class="sr-only" required />
                        </label>
                        <label id="status-btn-late" onclick="selectStatus('late')"
                            class="flex flex-col items-center gap-1 border-2 border-outline-variant rounded-xl p-3 cursor-pointer hover:border-amber-400 transition-all">
                            <span class="material-symbols-outlined text-amber-600 text-[22px]"
                                style="font-variation-settings:'FILL' 1;">schedule</span>
                            <span class="text-xs font-bold text-amber-700">Terlambat</span>
                            <input type="radio" name="status" value="late" class="sr-only" />
                        </label>
                        <label id="status-btn-absent" onclick="selectStatus('absent')"
                            class="flex flex-col items-center gap-1 border-2 border-outline-variant rounded-xl p-3 cursor-pointer hover:border-red-400 transition-all">
                            <span class="material-symbols-outlined text-red-600 text-[22px]"
                                style="font-variation-settings:'FILL' 1;">cancel</span>
                            <span class="text-xs font-bold text-red-700">Tidak Masuk</span>
                            <input type="radio" name="status" value="absent" class="sr-only" />
                        </label>
                    </div>
                    <input type="hidden" id="att-status-hidden" name="status-val" />
                </div>
                <!-- Clock In & Out -->
                <div id="clock-fields" class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-on-surface uppercase tracking-wide mb-1.5">Jam
                            Masuk</label>
                        <input type="time" id="att-clock-in"
                            class="w-full rounded-xl border border-outline-variant bg-surface-bright px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface uppercase tracking-wide mb-1.5">Jam
                            Keluar</label>
                        <input type="time" id="att-clock-out"
                            class="w-full rounded-xl border border-outline-variant bg-surface-bright px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:outline-none" />
                    </div>
                </div>
                <!-- Reason -->
                <div id="reason-field">
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wide mb-1.5">
                        Alasan / Keterangan
                        <span id="reason-required-mark" class="text-red-500 ml-0.5 hidden">*</span>
                    </label>
                    <textarea id="att-reason" rows="3" placeholder="Contoh: Sakit demam, sudah izin ke mentor..."
                        class="w-full rounded-xl border border-outline-variant bg-surface-bright px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:outline-none resize-none"></textarea>
                    <p id="reason-hint" class="text-xs text-on-surface-variant mt-1 hidden">Wajib diisi jika status
                        Terlambat atau Tidak Masuk.</p>
                </div>
                <!-- Actions -->
                <div class="flex justify-end gap-2 pt-2 border-t border-outline-variant">
                    <button type="button" onclick="closeInputModal()"
                        class="px-4 py-2.5 rounded-xl border border-outline-variant text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-bold hover:opacity-90 transition-opacity shadow-sm">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================
     DETAIL MODAL (Lihat Detail Hari)
     ============================================================ -->
    <div id="detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-backdrop">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl border border-outline-variant overflow-hidden">
            <div id="detail-header" class="p-5 border-b border-outline-variant flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-0.5">Detail
                        Kehadiran</p>
                    <h3 id="detail-date-label" class="font-geist font-bold text-on-surface text-lg"></h3>
                </div>
                <button onclick="closeDetailModal()"
                    class="text-on-surface-variant hover:text-primary transition-colors p-1 rounded-lg hover:bg-surface-container-low">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="p-5 space-y-3">
                <div class="flex justify-center mb-2">
                    <span id="detail-badge"
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-bold"></span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-surface-container-low rounded-xl p-3 text-center">
                        <p class="text-xs text-on-surface-variant mb-1">Jam Masuk</p>
                        <p id="detail-clock-in" class="font-geist font-bold text-on-surface">--:--</p>
                    </div>
                    <div class="bg-surface-container-low rounded-xl p-3 text-center">
                        <p class="text-xs text-on-surface-variant mb-1">Jam Keluar</p>
                        <p id="detail-clock-out" class="font-geist font-bold text-on-surface">--:--</p>
                    </div>
                </div>
                <div id="detail-reason-wrap" class="bg-amber-50 border border-amber-200 rounded-xl p-3 hidden">
                    <p class="text-xs font-bold text-amber-700 uppercase mb-1">Alasan</p>
                    <p id="detail-reason" class="text-sm text-amber-900"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden fallback camera input -->
    <input type="file" accept="image/*" capture="environment" id="att-camera-fallback" class="hidden">

    <!-- ============================================================
     CLOCK IN CONFIRMATION MODAL (Live Camera Stream)
     ============================================================ -->
    <div id="att-modal" class="hidden fixed inset-0 z-[60] bg-black/80 flex items-center justify-center p-4">
        <div class="bg-surface-container-lowest rounded-2xl p-5 max-w-sm w-full shadow-2xl">
            <h4 class="font-geist font-bold text-on-surface text-lg mb-3 text-center" id="att-modal-title">Clock In -
                Ambil Foto</h4>

            <!-- Geofence Status Badge -->
            <div id="att-geofence-badge" class="mb-3 px-3 py-2 rounded-xl text-xs flex items-center gap-2 border hidden">
                <span id="att-geofence-icon" class="material-symbols-outlined text-sm">near_me</span>
                <span id="att-geofence-text" class="font-medium">Memeriksa zona lokasi kantor...</span>
            </div>

            <div id="att-video-wrap"
                class="relative rounded-xl overflow-hidden border border-outline-variant mb-4 bg-black aspect-[3/4] flex items-center justify-center">
                <video id="att-video" autoplay playsinline class="w-full h-full object-cover"></video>
                <canvas id="att-canvas" class="hidden w-full h-full object-cover"></canvas>
            </div>

            <p class="font-body-sm text-body-sm text-on-surface-variant text-center mb-4" id="att-modal-status">Membuka
                kamera...</p>

            <div id="att-cam-actions" class="grid grid-cols-2 gap-3">
                <button onclick="attCloseModal()"
                    class="w-full bg-surface-container-high text-on-surface rounded-xl py-2.5 font-label-md text-label-md hover:bg-surface-container-highest">Batal</button>
                <button id="att-capture-btn" onclick="attCapture()"
                    class="w-full bg-primary text-on-primary rounded-xl py-2.5 font-label-md text-label-md flex items-center justify-center gap-2 hover:opacity-90">
                    <span class="material-symbols-outlined text-[18px]">photo_camera</span>
                    Ambil Foto
                </button>
            </div>
            <div id="att-confirm-actions" class="grid grid-cols-2 gap-3 hidden">
                <button onclick="attRetake()"
                    class="w-full bg-surface-container-high text-on-surface rounded-xl py-2.5 font-label-md text-label-md hover:bg-surface-container-highest">Ulangi</button>
                <button id="att-confirm-btn" onclick="attConfirm()"
                    class="w-full bg-primary text-on-primary rounded-xl py-2.5 font-label-md text-label-md disabled:opacity-40 disabled:cursor-not-allowed hover:opacity-90"
                    disabled>Simpan</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="att-toast"
        class="fixed bottom-6 right-6 z-[100] flex items-center gap-3 bg-slate-900 text-white px-5 py-3 rounded-xl shadow-xl hide pointer-events-none text-sm font-semibold">
        <span id="att-toast-icon" class="material-symbols-outlined text-[18px]">check_circle</span>
        <span id="att-toast-msg">Tersimpan!</span>
    </div>

    <script>
        // ============================================================
        // DATA STORE
        // ============================================================
        const CUTOFF_HOUR = 9; // 09:00 = batas tepat waktu

        // Tentukan intern mana yang sedang dilihat: kalau ada ?intern= (dibuka Admin dari
        // admin-dashboard/admin-attendance), tampilkan punya intern itu dalam mode baca-saja.
        // Selain itu pakai "current user" (demo login) agar konsisten dengan halaman lain.
        const attParams = new URLSearchParams(window.location.search);
        const attInternParam = attParams.get('intern') ? decodeURIComponent(attParams.get('intern')) : null;
        const isAdminPreview = !!attInternParam;
        const activeInternName = attInternParam || (window.InternStore ? InternStore.getCurrentUser() : 'Alex Doe');

        // Sumber data sebenarnya adalah tabel `attendance` di database (lewat
        // attendance-api.php), BUKAN localStorage. Sebelumnya halaman ini membaca
        // dari localStorage lewat InternStore, padahal tidak pernah diisi saat
        // clock-in disimpan ke database -> makanya tanggal tampak "hilang" (kosong)
        // terutama saat dibuka Admin (localStorage Admin memang tidak pernah punya
        // data intern tsb, karena localStorage tidak dibagikan antar user/browser).
        let attendanceData = [];

        async function fetchServerData() {
            try {
                const endpoint = isAdminPreview ? 'attendance-api.php?action=all_summary' : 'attendance-api.php?action=history';
                const res = await fetch(endpoint);
                const text = await res.text();
                let json;
                try {
                    json = JSON.parse(text);
                } catch (parseErr) {
                    console.error('Server returned non-JSON output:', text);
                    throw new Error('Respon tidak valid: ' + text.substring(0, 50));
                }

                if (!res.ok) throw new Error((json && json.error) || 'Gagal memuat data (HTTP ' + res.status + ')');

                if (isAdminPreview) {
                    const map = (json.attendanceMap && json.attendanceMap[activeInternName]) || {};
                    attendanceData = Object.values(map);
                } else {
                    const rows = Array.isArray(json) ? json : [];
                    attendanceData = rows.map(r => ({
                        date: r.date,
                        status: r.status,
                        clockIn: r.clock_in || '',
                        clockOut: r.clock_out || '',
                        reason: r.reason || ''
                    }));
                }
            } catch (err) {
                console.error(err);
                attendanceData = [];
                showToast('Error Server: ' + err.message, 'warning');
            }
        }

        function loadData() {
            return attendanceData;
        }

        function getRecord(dateStr) {
            return loadData().find(r => r.date === dateStr) || null;
        }

        function applyAdminPreviewMode() {
            if (!isAdminPreview) return;
            document.getElementById('page-eyebrow').textContent = 'Admin \u2013 Mode Lihat';
            document.getElementById('page-title').textContent = `Kehadiran ${activeInternName}`;
            document.getElementById('page-subtitle').textContent = 'Data kehadiran intern ini, dilihat oleh Admin.';
            document.getElementById('admin-preview-banner').classList.remove('hidden');
            document.getElementById('admin-preview-name').textContent = activeInternName;
            document.getElementById('main-input-btn')?.classList.add('hidden');
            document.querySelectorAll('.att-add-trigger').forEach(el => el.classList.add('hidden'));
        }

        // ============================================================
        // CALENDAR STATE
        // ============================================================
        let calYear, calMonth;
        let currentFilter = 'all';
        let detailDate = null;

        function initCalendar() {
            const now = new Date();
            calYear = now.getFullYear();
            calMonth = now.getMonth(); // 0-indexed
        }

        function changeMonth(dir) {
            calMonth += dir;
            if (calMonth > 11) { calMonth = 0; calYear++; }
            if (calMonth < 0) { calMonth = 11; calYear--; }
            renderCalendar();
            renderMonthlySummary();
        }

        function goToday() {
            const now = new Date();
            calYear = now.getFullYear();
            calMonth = now.getMonth();
            renderCalendar();
            renderMonthlySummary();
        }

        // ============================================================
        // RENDER CALENDAR
        // ============================================================
        function renderCalendar() {
            const data = loadData();
            const dataMap = {};
            data.forEach(r => { dataMap[r.date] = r; });

            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            document.getElementById('cal-title').textContent = `${monthNames[calMonth]} ${calYear}`;

            const grid = document.getElementById('cal-grid');
            grid.innerHTML = '';

            const firstDay = new Date(calYear, calMonth, 1).getDay(); // 0=Sun
            const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
            const today = new Date();
            const todayStr = formatDate(today);

            // Empty cells before first day
            for (let i = 0; i < firstDay; i++) {
                const cell = document.createElement('div');
                cell.className = 'cal-cell empty';
                grid.appendChild(cell);
            }

            for (let d = 1; d <= daysInMonth; d++) {
                const dateObj = new Date(calYear, calMonth, d);
                const dateStr = formatDate(dateObj);
                const dayOfWeek = dateObj.getDay(); // 0=Sun, 6=Sat
                const isWeekend = (dayOfWeek === 0);
                const isFuture = dateObj > today && dateStr !== todayStr;
                const isToday = dateStr === todayStr;
                const rec = dataMap[dateStr];

                const cell = document.createElement('div');
                cell.classList.add('cal-cell');

                const now = new Date();
                const isTodayPastCutoff = isToday && (now.getHours() >= 14);
                const isPastDay = !isFuture && !isToday;

                if (isWeekend) {
                    cell.classList.add('weekend');
                } else if (isFuture) {
                    cell.classList.add('future');
                } else if (rec) {
                    cell.classList.add(`status-${rec.status}`);
                    cell.addEventListener('click', () => openDetailModal(dateStr));
                } else if (isPastDay || isTodayPastCutoff) {
                    // Belum absen dan sudah lewat jam 14:00 atau hari lalu -> Merah (absent)
                    cell.classList.add('status-absent');
                    cell.addEventListener('click', () => openDetailModal(dateStr));
                } else {
                    cell.style.background = '#f8fafc';
                    cell.style.color = '#64748b';
                    cell.style.cursor = 'default';
                }

                if (isToday) cell.classList.add('today');

                cell.innerHTML = `<span>${d}</span>`;
                const currentStatus = rec ? rec.status : ((isPastDay || isTodayPastCutoff) ? 'absent' : null);
                if (currentStatus && !isWeekend) {
                    const dot = document.createElement('div');
                    dot.className = 'status-dot';
                    dot.style.background = currentStatus === 'present' ? '#16a34a' : currentStatus === 'late' ? '#d97706' : '#dc2626';
                    cell.appendChild(dot);
                }
                grid.appendChild(cell);
            }
        }

        // ============================================================
        // RENDER STATS
        // ============================================================
        function renderStats() {
            const data = loadData();
            const present = data.filter(r => r.status === 'present').length;
            const late = data.filter(r => r.status === 'late').length;
            const absent = data.filter(r => r.status === 'absent').length;
            const total = present + late + absent;
            const rate = total > 0 ? Math.round(((present + late) / total) * 100) : 0;

            document.getElementById('stat-present').textContent = present;
            document.getElementById('stat-late').textContent = late;
            document.getElementById('stat-absent').textContent = absent;
            document.getElementById('stat-rate').textContent = rate + '%';

            // Streak
            const streak = calcStreak(data);
            document.getElementById('streak-count').textContent = streak;
            document.getElementById('streak-desc').textContent = streak > 0
                ? `Anda hadir ${streak} hari berturut-turut!`
                : 'Belum ada streak kehadiran';
        }

        function calcStreak(data) {
            const workdays = data
                .filter(r => r.status === 'present' || r.status === 'late')
                .map(r => r.date)
                .sort()
                .reverse();

            if (!workdays.length) return 0;

            let streak = 0;
            let checkDate = new Date();
            checkDate.setHours(0, 0, 0, 0);

            for (let i = 0; i < 60; i++) {
                const ds = formatDate(checkDate);
                const dow = checkDate.getDay();
                // Skip weekends
                if (dow === 0) {
                    checkDate.setDate(checkDate.getDate() - 1);
                    continue;
                }
                if (workdays.includes(ds)) {
                    streak++;
                } else {
                    break;
                }
                checkDate.setDate(checkDate.getDate() - 1);
            }
            return streak;
        }

        function renderMonthlySummary() {
            const data = loadData();
            const prefix = `${calYear}-${String(calMonth + 1).padStart(2, '0')}`;
            const monthly = data.filter(r => r.date.startsWith(prefix));
            const p = monthly.filter(r => r.status === 'present').length;
            const l = monthly.filter(r => r.status === 'late').length;
            const a = monthly.filter(r => r.status === 'absent').length;
            document.getElementById('month-present').textContent = `${p} hari`;
            document.getElementById('month-late').textContent = `${l} hari`;
            document.getElementById('month-absent').textContent = `${a} hari`;
            document.getElementById('month-total').textContent = `${p + l + a} hari`;
        }

        // ============================================================
        // RENDER TABLE
        // ============================================================
        function renderTable(filter = 'all') {
            const all = loadData().sort((a, b) => b.date.localeCompare(a.date));
            const data = filter === 'all' ? all : all.filter(r => r.status === filter);

            const tbody = document.getElementById('att-table-body');
            const empty = document.getElementById('att-empty');
            tbody.innerHTML = '';

            if (!data.length) {
                empty.classList.remove('hidden');
                return;
            }
            empty.classList.add('hidden');

            data.forEach(rec => {
                const tr = document.createElement('tr');
                tr.className = 'att-row';
                const statusHtml = {
                    present: `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold badge-present"><span class="material-symbols-outlined text-[13px]" style="font-variation-settings:'FILL' 1;">check_circle</span>Hadir</span>`,
                    late: `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold badge-late"><span class="material-symbols-outlined text-[13px]" style="font-variation-settings:'FILL' 1;">schedule</span>Terlambat</span>`,
                    absent: `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold badge-absent"><span class="material-symbols-outlined text-[13px]" style="font-variation-settings:'FILL' 1;">cancel</span>Tidak Masuk</span>`,
                }[rec.status];

                tr.innerHTML = `
            <td class="px-5 py-3.5 font-semibold text-on-surface text-sm whitespace-nowrap">${formatDateDisplay(rec.date)}</td>
            <td class="px-5 py-3.5 text-on-surface-variant text-sm">${rec.clockIn ? formatTime12(rec.clockIn) : '<span class="text-slate-400">--:--</span>'}</td>
            <td class="px-5 py-3.5 text-on-surface-variant text-sm">${rec.clockOut ? formatTime12(rec.clockOut) : '<span class="text-slate-400">--:--</span>'}</td>
            <td class="px-5 py-3.5">${statusHtml}</td>
            <td class="px-5 py-3.5 text-sm text-on-surface-variant max-w-[220px]">
                ${rec.reason ? `<span class="truncate block" title="${escHtml(rec.reason)}">${escHtml(rec.reason)}</span>` : '<span class="text-slate-400 italic">–</span>'}
            </td>`;
                tbody.appendChild(tr);
            });
        }

        // ============================================================
        // FILTER
        // ============================================================
        function setFilter(f) {
            currentFilter = f;
            ['all', 'present', 'late', 'absent'].forEach(id => {
                const btn = document.getElementById(`filter-${id}`);
                if (!btn) return;
                btn.className = f === id
                    ? 'px-3 py-1.5 rounded-lg font-semibold bg-white text-primary shadow-sm'
                    : 'px-3 py-1.5 rounded-lg font-semibold text-on-surface-variant hover:text-primary';
            });
            renderTable(f);
        }

        // ============================================================
        // INPUT MODAL
        // ============================================================
        let editingDate = null;

        function openInputModal(preDate = null) {
            editingDate = null;
            const modal = document.getElementById('input-modal');
            const form = document.getElementById('att-form');
            form.reset();
            clearStatusSelection();

            const today = formatDate(new Date());
            document.getElementById('att-date').value = preDate || today;
            document.getElementById('input-modal-title').textContent = 'Catat Kehadiran';

            // Pre-fill time
            const now = new Date();
            document.getElementById('att-clock-in').value = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
            document.getElementById('att-clock-out').value = '';
            document.getElementById('att-reason').value = '';
            document.getElementById('reason-required-mark').classList.add('hidden');
            document.getElementById('reason-hint').classList.add('hidden');
            document.getElementById('clock-fields').classList.remove('hidden');

            // If date already has a record → pre-fill
            if (preDate) {
                const rec = getRecord(preDate);
                if (rec) {
                    editingDate = preDate;
                    document.getElementById('input-modal-title').textContent = 'Edit Kehadiran';
                    selectStatus(rec.status);
                    document.getElementById('att-clock-in').value = rec.clockIn || '';
                    document.getElementById('att-clock-out').value = rec.clockOut || '';
                    document.getElementById('att-reason').value = rec.reason || '';
                    if (rec.status === 'absent') {
                        document.getElementById('clock-fields').classList.add('hidden');
                    }
                }
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeInputModal() {
            const modal = document.getElementById('input-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            editingDate = null;
        }

        let selectedStatus = null;
        function selectStatus(status) {
            selectedStatus = status;
            ['present', 'late', 'absent'].forEach(s => {
                const btn = document.getElementById(`status-btn-${s}`);
                btn.classList.remove('border-green-500', 'border-amber-500', 'border-red-500', 'bg-green-50', 'bg-amber-50', 'bg-red-50');
                btn.classList.add('border-outline-variant');
            });
            const colors = { present: ['border-green-500', 'bg-green-50'], late: ['border-amber-500', 'bg-amber-50'], absent: ['border-red-500', 'bg-red-50'] };
            const btn = document.getElementById(`status-btn-${status}`);
            btn.classList.remove('border-outline-variant');
            colors[status].forEach(c => btn.classList.add(c));
            // Also check radio
            document.querySelector(`input[name="status"][value="${status}"]`).checked = true;
            document.getElementById('att-status-hidden').value = status;

            // Toggle clock fields & reason required
            const clockFields = document.getElementById('clock-fields');
            const reasonMark = document.getElementById('reason-required-mark');
            const reasonHint = document.getElementById('reason-hint');
            if (status === 'absent') {
                clockFields.classList.add('hidden');
                reasonMark.classList.remove('hidden');
                reasonHint.classList.remove('hidden');
            } else if (status === 'late') {
                clockFields.classList.remove('hidden');
                reasonMark.classList.remove('hidden');
                reasonHint.classList.remove('hidden');
            } else {
                clockFields.classList.remove('hidden');
                reasonMark.classList.add('hidden');
                reasonHint.classList.add('hidden');
            }
        }

        function clearStatusSelection() {
            selectedStatus = null;
            ['present', 'late', 'absent'].forEach(s => {
                const btn = document.getElementById(`status-btn-${s}`);
                btn.classList.remove('border-green-500', 'border-amber-500', 'border-red-500', 'bg-green-50', 'bg-amber-50', 'bg-red-50');
                btn.classList.add('border-outline-variant');
                document.querySelector(`input[name="status"][value="${s}"]`).checked = false;
            });
        }

        async function saveAttendance(e) {
            e.preventDefault();
            if (isAdminPreview) { showToast('Mode Admin hanya untuk melihat.', 'warning'); return; }
            const date = document.getElementById('att-date').value;
            const clockIn = document.getElementById('att-clock-in').value;
            const clockOut = document.getElementById('att-clock-out').value;
            const reason = document.getElementById('att-reason').value.trim();
            const status = selectedStatus;

            if (!status) { showToast('Pilih status kehadiran!', 'warning'); return; }
            if (!date) { showToast('Pilih tanggal!', 'warning'); return; }
            if ((status === 'late' || status === 'absent') && !reason) {
                showToast('Alasan wajib diisi untuk status ini!', 'warning');
                document.getElementById('att-reason').focus();
                return;
            }

            try {
                const res = await fetch('attendance-api.php?action=upsert', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        date, status,
                        clockIn: status === 'absent' ? '' : (clockIn || ''),
                        clockOut: status === 'absent' ? '' : (clockOut || ''),
                        reason
                    })
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data.error || 'Gagal menyimpan');
                await fetchServerData();
                closeInputModal();
                refreshAll();
                showToast('Kehadiran berhasil disimpan!', 'success');
            } catch (err) {
                showToast(err.message || 'Gagal menyimpan kehadiran.', 'warning');
            }
        }

        // ============================================================
        // DETAIL MODAL
        // ============================================================
        function openDetailModal(dateStr) {
            let rec = getRecord(dateStr);
            if (!rec) {
                const todayStr = formatDate(new Date());
                const now = new Date();
                const isTodayPastCutoff = (dateStr === todayStr) && (now.getHours() >= 14);
                const isPastDay = dateStr < todayStr;
                if (isPastDay || isTodayPastCutoff) {
                    rec = { date: dateStr, status: 'absent', clockIn: '', clockOut: '', reason: 'Tidak melakukan absensi hingga batas waktu (14:00)' };
                } else {
                    return;
                }
            }
            detailDate = dateStr;

            const statusLabel = { present: 'Hadir', late: 'Terlambat', absent: 'Tidak Masuk' }[rec.status];
            const badgeClass = { present: 'badge-present', late: 'badge-late', absent: 'badge-absent' }[rec.status];
            const iconName = { present: 'check_circle', late: 'schedule', absent: 'cancel' }[rec.status];

            document.getElementById('detail-date-label').textContent = formatDateDisplay(dateStr);
            const badge = document.getElementById('detail-badge');
            badge.className = `inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-bold ${badgeClass}`;
            badge.innerHTML = `<span class="material-symbols-outlined text-[15px]" style="font-variation-settings:'FILL' 1;">${iconName}</span>${statusLabel}`;

            document.getElementById('detail-clock-in').textContent = rec.clockIn ? formatTime12(rec.clockIn) : '--:--';
            document.getElementById('detail-clock-out').textContent = rec.clockOut ? formatTime12(rec.clockOut) : '--:--';

            const reasonWrap = document.getElementById('detail-reason-wrap');
            if (rec.reason) {
                reasonWrap.classList.remove('hidden');
                document.getElementById('detail-reason').textContent = rec.reason;
                // Different color for absent vs late
                if (rec.status === 'absent') {
                    reasonWrap.className = 'bg-red-50 border border-red-200 rounded-xl p-3';
                    document.getElementById('detail-reason').className = 'text-sm text-red-900';
                    reasonWrap.querySelector('p').className = 'text-xs font-bold text-red-700 uppercase mb-1';
                } else {
                    reasonWrap.className = 'bg-amber-50 border border-amber-200 rounded-xl p-3';
                    document.getElementById('detail-reason').className = 'text-sm text-amber-900';
                    reasonWrap.querySelector('p').className = 'text-xs font-bold text-amber-700 uppercase mb-1';
                }
            } else {
                reasonWrap.classList.add('hidden');
            }

            const modal = document.getElementById('detail-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDetailModal() {
            document.getElementById('detail-modal').classList.add('hidden');
            document.getElementById('detail-modal').classList.remove('flex');
            detailDate = null;
        }

        function editFromDetail() {
            if (isAdminPreview) { showToast('Mode Admin hanya untuk melihat.', 'warning'); return; }
            const d = detailDate;
            closeDetailModal();
            openInputModal(d);
        }

        async function deleteAttendanceOnServer(dateStr) {
            const res = await fetch('attendance-api.php?action=delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ date: dateStr })
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.error || 'Gagal menghapus data');
        }

        async function deleteFromDetail() {
            if (!detailDate) return;
            if (isAdminPreview) { showToast('Mode Admin hanya untuk melihat.', 'warning'); return; }
            if (!confirm(`Hapus catatan kehadiran tanggal ${formatDateDisplay(detailDate)}?`)) return;
            try {
                await deleteAttendanceOnServer(detailDate);
                await fetchServerData();
                closeDetailModal();
                refreshAll();
                showToast('Catatan berhasil dihapus.', 'success');
            } catch (err) {
                showToast(err.message || 'Gagal menghapus catatan.', 'warning');
            }
        }

        // ============================================================
        // TABLE ROW ACTIONS
        // ============================================================
        function handleEditRow(dateStr) {
            if (isAdminPreview) { showToast('Mode Admin hanya untuk melihat.', 'warning'); return; }
            openInputModal(dateStr);
        }

        async function handleDeleteRow(dateStr) {
            if (isAdminPreview) { showToast('Mode Admin hanya untuk melihat.', 'warning'); return; }
            if (!confirm(`Hapus catatan kehadiran tanggal ${formatDateDisplay(dateStr)}?`)) return;
            try {
                await deleteAttendanceOnServer(dateStr);
                await fetchServerData();
                refreshAll();
                showToast('Catatan berhasil dihapus.', 'success');
            } catch (err) {
                showToast(err.message || 'Gagal menghapus catatan.', 'warning');
            }
        }

        // ============================================================
        // EXPORT CSV
        // ============================================================
        function exportAttendanceCSV() {
            const data = loadData().sort((a, b) => a.date.localeCompare(b.date));
            if (!data.length) { showToast('Belum ada data untuk diekspor.', 'warning'); return; }

            const statusLabel = { present: 'Hadir', late: 'Terlambat', absent: 'Tidak Masuk' };

            // Format tanggal yang bersih tanpa koma di dalam teks (misal: "Selasa 01 Sep 2026")
            function cleanDateDisplay(ds) {
                const [y, m, d] = ds.split('-');
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const dateObj = new Date(+y, +m - 1, +d);
                return `${days[dateObj.getDay()]} ${String(d).padStart(2, '0')} ${months[+m - 1]} ${y}`;
            }

            const rows = [['Tanggal', 'Jam Masuk', 'Jam Keluar', 'Status', 'Alasan']];
            data.forEach(r => {
                rows.push([
                    cleanDateDisplay(r.date),
                    r.clockIn || '--:--',
                    r.clockOut || '--:--',
                    statusLabel[r.status] || r.status,
                    r.reason || '-'
                ]);
            });

            // Pemisah koma dengan pembungkus tanda petik ganda (") untuk menjaga isi teks tetap rapi
            const csvContent = rows.map(row =>
                row.map(val => {
                    const str = String(val || '').replace(/"/g, '""');
                    return `"${str}"`;
                }).join(',')
            ).join('\r\n');

            const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `Kehadiran_PKL_${new Date().toISOString().slice(0, 10)}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            showToast('Data berhasil diekspor!', 'success');
        }

        // ============================================================
        // TOAST
        // ============================================================
        function showToast(msg, type = 'success') {
            const toast = document.getElementById('att-toast');
            const icon = document.getElementById('att-toast-icon');
            const text = document.getElementById('att-toast-msg');
            text.textContent = msg;
            icon.textContent = type === 'success' ? 'check_circle' : type === 'warning' ? 'warning' : 'info';
            toast.classList.remove('hide');
            toast.classList.add('show');
            toast.style.pointerEvents = 'none';
            setTimeout(() => {
                toast.classList.remove('show');
                toast.classList.add('hide');
            }, 2800);
        }

        // ============================================================
        // HELPERS
        // ============================================================
        function formatDate(d) {
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        }

        function formatDateDisplay(ds) {
            const [y, m, d] = ds.split('-');
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const dateObj = new Date(+y, +m - 1, +d);
            return `${days[dateObj.getDay()]}, ${+d} ${months[+m - 1]} ${y}`;
        }

        function formatTime12(t) {
            if (!t) return '--:--';
            const [h, min] = t.split(':').map(Number);
            const ampm = h >= 12 ? 'PM' : 'AM';
            const h12 = h % 12 || 12;
            return `${String(h12).padStart(2, '0')}:${String(min).padStart(2, '0')} ${ampm}`;
        }

        function escHtml(str) {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // Close modals on backdrop click
        document.getElementById('input-modal').addEventListener('click', function (e) {
            if (e.target === this) closeInputModal();
        });
        document.getElementById('detail-modal').addEventListener('click', function (e) {
            if (e.target === this) closeDetailModal();
        });
        document.getElementById('att-modal').addEventListener('click', function (e) {
            if (e.target === this) { document.getElementById('att-modal').classList.add('hidden'); attPendingDataUrl = null; }
        });

        // ---------------- Kamera + Geotag (Live Camera Feed) ----------------
        let attStream = null;
        let attPendingDataUrl = null;
        let attPendingAddress = '';
        let attPendingLat = null;
        let attPendingLng = null;
        let officeGeofenceConfig = null;

        function attCalculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000; // Radius bumi dalam meter
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return Math.round(R * c);
        }

        async function fetchGeofenceConfig() {
            try {
                const res = await fetch('attendance-api.php?action=get_location_config');
                if (res.ok) {
                    const data = await res.json();
                    if (data && data.config) {
                        officeGeofenceConfig = data.config;
                    }
                }
            } catch (e) {
                console.warn('Gagal memuat konfigurasi zona geofence:', e);
            }
        }

        function attPad(n) { return String(n).padStart(2, '0'); }

        async function attStartCapture() {
            if (isAdminPreview) {
                showToast('Admin tidak bisa Clock In.', 'warning');
                return;
            }

            const now = new Date();
            if (now.getHours() >= 14) {
                showToast('Batas waktu Clock In (14:00) telah lewat. Anda dianggap Tidak Masuk.', 'warning');
                return;
            }

            const modal = document.getElementById('att-modal');
            const video = document.getElementById('att-video');
            const canvas = document.getElementById('att-canvas');
            const statusEl = document.getElementById('att-modal-status');
            const titleEl = document.getElementById('att-modal-title');
            const camActions = document.getElementById('att-cam-actions');
            const confirmActions = document.getElementById('att-confirm-actions');
            const geofenceBadge = document.getElementById('att-geofence-badge');
            const geofenceText = document.getElementById('att-geofence-text');
            const geofenceIcon = document.getElementById('att-geofence-icon');

            // Check navigator.mediaDevices support
            if (!modal || !video) return;

            if (geofenceBadge) {
                geofenceBadge.className = 'mb-3 px-3 py-2 rounded-xl text-xs flex items-center gap-2 border bg-blue-50 text-blue-800 border-blue-200';
                if (geofenceIcon) geofenceIcon.textContent = 'radar';
                if (geofenceText) geofenceText.textContent = officeGeofenceConfig ? `Target: ${officeGeofenceConfig.office_name} (Radius: ${officeGeofenceConfig.radius_meters}m)` : 'Memeriksa zona lokasi kantor...';
                geofenceBadge.classList.remove('hidden');
            }

            titleEl.textContent = 'Clock In - Ambil Foto';
            statusEl.textContent = 'Membuka kamera...';
            video.classList.remove('hidden');
            canvas.classList.add('hidden');
            camActions.classList.remove('hidden');
            confirmActions.classList.add('hidden');
            modal.classList.remove('hidden');

            try {
                attStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'user' }, width: { ideal: 1280 }, height: { ideal: 720 } },
                    audio: false
                });
                video.srcObject = attStream;
                statusEl.textContent = 'Posisikan wajah Anda lalu tekan Ambil Foto.';
            } catch (err) {
                console.warn('Camera live feed failed:', err);
                statusEl.textContent = 'Tidak bisa mengakses kamera. Pastikan izin kamera telah diberikan dan kamera tidak sedang digunakan aplikasi lain.';
                if (window.showToast) {
                    showToast('Gagal membuka kamera. Periksa izin kamera browser Anda.', 'warning');
                }
            }
        }

        function attStopCamera() {
            if (attStream) {
                attStream.getTracks().forEach(track => track.stop());
                attStream = null;
            }
        }

        function attCloseModal() {
            attStopCamera();
            document.getElementById('att-modal')?.classList.add('hidden');
        }

        function attCapture() {
            const video = document.getElementById('att-video');
            const canvas = document.getElementById('att-canvas');
            if (!video || !canvas || !video.videoWidth) return;

            const vW = video.videoWidth;
            const vH = video.videoHeight;

            // Draw video frame to temp canvas
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = vW;
            tempCanvas.height = vH;
            const tempCtx = tempCanvas.getContext('2d');
            tempCtx.drawImage(video, 0, 0, vW, vH);

            // Stop stream & switch to canvas view
            attStopCamera();
            video.classList.add('hidden');
            canvas.classList.remove('hidden');

            const camActions = document.getElementById('att-cam-actions');
            const confirmActions = document.getElementById('att-confirm-actions');
            camActions.classList.add('hidden');
            confirmActions.classList.remove('hidden');

            // Create HTML Image element from captured video frame
            const img = new Image();
            img.onload = () => attComposeAndShow(img);
            img.src = tempCanvas.toDataURL('image/jpeg');
        }

        function attWrapAddress(addr, maxLen) {
            maxLen = maxLen || 42;
            const words = addr.split(' ');
            const lines = [];
            let line = '';
            words.forEach(w => {
                if ((line + ' ' + w).trim().length > maxLen) {
                    lines.push(line.trim());
                    line = w;
                } else {
                    line += ' ' + w;
                }
            });
            if (line.trim()) lines.push(line.trim());
            return lines.slice(0, 3);
        }

        function attComposeAndShow(img) {
            const modal = document.getElementById('att-modal');
            const canvas = document.getElementById('att-canvas');
            const statusEl = document.getElementById('att-modal-status');
            const confirmBtn = document.getElementById('att-confirm-btn');
            if (!modal || !canvas) return;

            modal.classList.remove('hidden');
            if (confirmBtn) confirmBtn.disabled = true;
            if (statusEl) statusEl.textContent = 'Mengambil lokasi...';

            const maxW = 900;
            const scale = Math.min(1, maxW / img.width);
            canvas.width = Math.round(img.width * scale);
            canvas.height = Math.round(img.height * scale);
            const ctx = canvas.getContext('2d');

            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const timeStr = `${attPad(now.getHours())}:${attPad(now.getMinutes())}`;
            const dateStr = `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
            const dayStr = days[now.getDay()];

            function checkGeofenceStatus() {
                const badge = document.getElementById('att-geofence-badge');
                const badgeIcon = document.getElementById('att-geofence-icon');
                const badgeText = document.getElementById('att-geofence-text');
                if (badge) badge.classList.remove('hidden');

                if (!attPendingLat || !attPendingLng) {
                    if (badge) {
                        badge.className = 'mb-3 px-3 py-2 rounded-xl text-xs flex items-center gap-2 border bg-amber-50 text-amber-800 border-amber-200';
                        if (badgeIcon) badgeIcon.textContent = 'location_off';
                        if (badgeText) badgeText.textContent = 'Lokasi GPS tidak terdeteksi.';
                    }
                    if (officeGeofenceConfig && officeGeofenceConfig.is_strict) {
                        if (confirmBtn) confirmBtn.disabled = true;
                        if (statusEl) statusEl.textContent = 'Izin lokasi wajib diaktifkan untuk absensi.';
                    }
                    return;
                }

                if (officeGeofenceConfig) {
                    const dist = attCalculateDistance(
                        attPendingLat, attPendingLng,
                        officeGeofenceConfig.latitude, officeGeofenceConfig.longitude
                    );
                    const isInside = dist <= officeGeofenceConfig.radius_meters;

                    if (isInside) {
                        if (badge) {
                            badge.className = 'mb-3 px-3 py-2 rounded-xl text-xs flex items-center gap-2 border bg-emerald-50 text-emerald-800 border-emerald-200';
                            if (badgeIcon) badgeIcon.textContent = 'verified';
                            if (badgeText) badgeText.textContent = `Dalam Zona Kantor (${dist}m dari kantor | Batas: ${officeGeofenceConfig.radius_meters}m)`;
                        }
                        if (confirmBtn) confirmBtn.disabled = false;
                        if (statusEl) statusEl.textContent = 'Foto siap — Anda berada di dalam zona kantor.';
                    } else {
                        if (badge) {
                            badge.className = 'mb-3 px-3 py-2 rounded-xl text-xs flex items-center gap-2 border bg-red-50 text-red-800 border-red-200';
                            if (badgeIcon) badgeIcon.textContent = 'gpp_bad';
                            if (badgeText) badgeText.textContent = `Di Luar Zona Kantor (${dist}m | Maks: ${officeGeofenceConfig.radius_meters}m)`;
                        }
                        if (officeGeofenceConfig.is_strict) {
                            if (confirmBtn) confirmBtn.disabled = true;
                            if (statusEl) statusEl.textContent = `Absensi ditolak: Anda berada di luar radius kantor (${dist}m > ${officeGeofenceConfig.radius_meters}m).`;
                        } else {
                            if (confirmBtn) confirmBtn.disabled = false;
                            if (statusEl) statusEl.textContent = `Peringatan: Berada ${dist}m di luar radius kantor.`;
                        }
                    }
                }
            }

            function draw(addressLines, coordStr) {
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                const extraLines = (coordStr ? 1 : 0) + addressLines.length;
                const padX = Math.round(canvas.width * 0.05);
                const lineH = Math.round(canvas.height * 0.035);
                const boxH = Math.round(canvas.height * 0.25 + (extraLines * lineH));

                const grad = ctx.createLinearGradient(0, canvas.height - boxH, 0, canvas.height);
                grad.addColorStop(0, 'rgba(0,0,0,0)');
                grad.addColorStop(0.35, 'rgba(0,0,0,0.60)');
                grad.addColorStop(1, 'rgba(0,0,0,0.92)');
                ctx.fillStyle = grad;
                ctx.fillRect(0, canvas.height - boxH, canvas.width, boxH);

                ctx.fillStyle = '#ffffff';
                ctx.textBaseline = 'alphabetic';

                const timeFontSize = Math.round(canvas.width * 0.065);
                ctx.font = `800 ${timeFontSize}px sans-serif`;

                const yBase = canvas.height - (extraLines * lineH) - Math.round(canvas.height * 0.04);
                ctx.fillText(timeStr, padX, yBase);
                const timeWidth = ctx.measureText(timeStr).width;

                ctx.strokeStyle = 'rgba(255,255,255,0.7)';
                ctx.lineWidth = 1.5;
                ctx.beginPath();
                ctx.moveTo(padX + timeWidth + 10, yBase - timeFontSize * 0.75);
                ctx.lineTo(padX + timeWidth + 10, yBase);
                ctx.stroke();

                const dateFontSize = Math.round(timeFontSize * 0.35);
                ctx.font = `600 ${dateFontSize}px sans-serif`;
                ctx.fillText(dateStr, padX + timeWidth + 18, yBase - dateFontSize * 1.1);
                ctx.fillText(dayStr, padX + timeWidth + 18, yBase);

                const addrFontSize = Math.round(canvas.width * 0.030);
                ctx.font = `500 ${addrFontSize}px sans-serif`;
                let ay = yBase + Math.round(lineH * 1.1);
                addressLines.forEach(line => {
                    ctx.fillText(line, padX, ay);
                    ay += lineH;
                });

                if (coordStr) {
                    const coordFontSize = Math.round(canvas.width * 0.026);
                    ctx.font = `600 ${coordFontSize}px monospace`;
                    ctx.fillStyle = '#93c5fd'; // Warna aksen biru muda untuk koordinat
                    ctx.fillText(coordStr, padX, ay);
                }

                attPendingDataUrl = canvas.toDataURL('image/jpeg', 0.85);
                checkGeofenceStatus();
            }

            attPendingAddress = '';
            attPendingLat = null;
            attPendingLng = null;

            if (!navigator.geolocation) {
                if (statusEl) statusEl.textContent = 'Lokasi tidak tersedia di perangkat ini.';
                draw(['Lokasi tidak tersedia'], null);
                return;
            }

            navigator.geolocation.getCurrentPosition(
                pos => {
                    const { latitude, longitude } = pos.coords;
                    attPendingLat = latitude;
                    attPendingLng = longitude;
                    let coordStr = `📍 ${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;

                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`)
                        .then(r => r.json())
                        .then(data => {
                            const addr = data && data.display_name ? data.display_name : `${latitude.toFixed(5)}, ${longitude.toFixed(5)}`;
                            attPendingAddress = addr;
                            draw(attWrapAddress(addr), coordStr);
                        })
                        .catch(() => {
                            attPendingAddress = `${latitude.toFixed(5)}, ${longitude.toFixed(5)}`;
                            draw([attPendingAddress], coordStr);
                        });
                },
                () => {
                    if (statusEl) statusEl.textContent = 'Izin lokasi ditolak — foto disimpan tanpa lokasi.';
                    attPendingAddress = 'Lokasi tidak diizinkan';
                    draw(['Lokasi tidak diizinkan'], null);
                },
                { enableHighAccuracy: true, timeout: 8000 }
            );
        }

        function attRetake() {
            document.getElementById('att-modal')?.classList.add('hidden');
            attPendingDataUrl = null;
            attStartCapture();
        }

        function attConfirm() {
            if (!attPendingDataUrl) return;
            const confirmBtn = document.getElementById('att-confirm-btn');
            const statusEl = document.getElementById('att-modal-status');
            const now = new Date();
            const timeStr = `${attPad(now.getHours())}:${attPad(now.getMinutes())}`;
            const status = now.getHours() >= CUTOFF_HOUR ? 'late' : 'present';

            if (confirmBtn) confirmBtn.disabled = true;
            if (statusEl) statusEl.textContent = 'Menyimpan ke database...';

            fetch('attendance-api.php?action=save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    time: timeStr,
                    status: status,
                    photo: attPendingDataUrl,
                    location: attPendingAddress,
                    lat: attPendingLat,
                    lng: attPendingLng
                })
            })
                .then(async r => {
                    const data = await r.json().catch(() => ({}));
                    if (!r.ok) throw new Error(data.error || 'Gagal menyimpan absensi');
                    return data;
                })
                .then(async () => {
                    document.getElementById('att-modal')?.classList.add('hidden');
                    attPendingDataUrl = null;
                    const label = status === 'present' ? 'Hadir Tepat Waktu' : 'Terlambat';
                    showToast(`Clock In berhasil! ${timeStr} — ${label}`, 'success');
                    await fetchServerData();
                    refreshAll();
                })
                .catch(err => {
                    if (statusEl) statusEl.textContent = err.message || 'Gagal menyimpan, coba lagi.';
                    if (confirmBtn) confirmBtn.disabled = false;
                });
        }

        // ============================================================
        // REFRESH ALL
        // ============================================================
        function refreshAll() {
            renderCalendar();
            renderStats();
            renderMonthlySummary();
            renderTable(currentFilter);
        }

        // ============================================================
        // INIT
        // ============================================================
        document.addEventListener('DOMContentLoaded', async () => {
            applyAdminPreviewMode();
            initCalendar();
            await fetchGeofenceConfig();
            await fetchServerData();
            refreshAll();
        });
    </script>
</body>

</html>