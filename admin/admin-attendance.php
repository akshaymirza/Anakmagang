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
    <script src="../shared-config.js"></script>
    <script src="../intern-store.js"></script>
    <link rel="stylesheet" href="../style.css" />
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
        }

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
            border-color: #2563eb;
        }

        .cal-cell.today {
            box-shadow: 0 0 0 2px #2563eb;
        }

        .cal-cell.status-complete {
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

        .cal-cell.status-pending {
            background: #f1f5f9;
            color: #475569;
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
            background: #f8fafc;
            color: #cbd5e1;
            cursor: not-allowed;
            opacity: 0.6;
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
        <header class="w-full h-20 bg-surface-container-lowest border-b border-outline-variant sticky top-0 flex justify-between items-center px-6 z-10 shrink-0">
            <div class="flex items-center gap-2">
                <button onclick="toggleMobileSidebar()" class="md:hidden text-on-surface hover:text-primary focus:outline-none flex items-center mr-2 p-1 rounded-lg hover:bg-surface-container-high" aria-label="Toggle Sidebar">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">event_available</span>
                    <h2 class="font-headline-lg font-bold text-on-surface">Kehadiran Semua Intern</h2>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="exportAllAdminAttendanceCSV()"
                    class="px-3.5 py-2 bg-primary text-on-primary rounded-xl text-xs font-bold hover:opacity-90 flex items-center gap-1 shadow-sm active:scale-95 transition-all">
                    <span class="material-symbols-outlined text-[16px]">download</span>
                    <span>Export CSV</span>
                </button>
                <a href="admin-dashboard.php" class="text-sm font-semibold text-primary hover:underline flex items-center gap-1 hidden sm:flex">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke Dashboard
                </a>
                <div class="flex items-center gap-sm p-1.5 px-3 rounded-full border border-outline-variant bg-surface-bright shadow-2xs">
                    <div class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container overflow-hidden shrink-0">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">account_circle</span>
                    </div>
                    <span class="hidden sm:inline-block font-label-md"><?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="../Login/logout.php" class="text-error hover:text-red-700 hover:bg-red-50 p-1.5 rounded-full transition-colors flex items-center justify-center" title="Keluar" aria-label="Keluar">
                        <span class="material-symbols-outlined text-[20px]">logout</span>
                    </a>
                </div>
            </div>
        </header>
        <div class="p-5 md:p-10 flex-1 flex flex-col gap-6">
        <!-- Stats -->
        <section class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="p-3 bg-surface-container-low rounded-lg border border-outline-variant/60">
                <span class="text-xs font-bold uppercase tracking-wider text-primary">Total Intern</span>
                <div class="text-2xl font-bold mt-1" id="stat-total-interns">0</div>
            </div>
            <div class="p-3 rounded-lg border border-green-200 bg-green-50">
                <span class="text-xs font-bold uppercase tracking-wider text-green-700">Hari Lengkap</span>
                <div class="text-2xl font-bold mt-1 text-green-800" id="stat-days-complete">0</div>
            </div>
            <div class="p-3 rounded-lg border border-amber-200 bg-amber-50">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-700">Hari Ada Keterlambatan</span>
                <div class="text-2xl font-bold mt-1 text-amber-800" id="stat-days-late">0</div>
            </div>
            <div class="p-3 rounded-lg border border-red-200 bg-red-50">
                <span class="text-xs font-bold uppercase tracking-wider text-red-700">Hari Ada Yang Absen</span>
                <div class="text-2xl font-bold mt-1 text-red-800" id="stat-days-absent">0</div>
            </div>
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Legend -->
            <div class="lg:col-span-4 flex flex-col gap-4">
                <div class="glass-card rounded-2xl border border-outline-variant p-5">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-3">Keterangan
                        Warna</h3>
                    <div class="space-y-2.5">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center text-green-700 text-xs font-bold">
                                12</div>
                            <div>
                                <div class="text-sm font-semibold text-on-surface">Lengkap</div>
                                <div class="text-xs text-on-surface-variant">Semua intern hadir tepat waktu</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-700 text-xs font-bold">
                                12</div>
                            <div>
                                <div class="text-sm font-semibold text-on-surface">Ada Keterlambatan</div>
                                <div class="text-xs text-on-surface-variant">Semua tercatat, namun ada yang telat</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center text-red-700 text-xs font-bold">
                                12</div>
                            <div>
                                <div class="text-sm font-semibold text-on-surface">Ada Yang Tidak Masuk</div>
                                <div class="text-xs text-on-surface-variant">Minimal satu intern absen</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 text-xs font-bold">
                                12</div>
                            <div>
                                <div class="text-sm font-semibold text-on-surface">Belum Lengkap Diisi</div>
                                <div class="text-xs text-on-surface-variant">Masih ada intern yang belum absen</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="glass-card rounded-2xl border border-outline-variant p-5">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-3">Cara Pakai</h3>
                    <p class="text-sm text-on-surface-variant">Klik salah satu tanggal pada kalender untuk melihat
                        rincian kehadiran setiap intern pada hari itu &mdash; termasuk jam masuk, keterlambatan, dan
                        alasan tidak masuk.</p>
                </div>
            </div>

            <!-- Calendar -->
            <div class="lg:col-span-8 glass-card rounded-2xl border border-outline-variant p-5">
                <div class="flex justify-between items-center mb-5">
                    <h3 id="cal-title" class="font-headline-md font-bold text-on-surface"></h3>
                    <div class="flex items-center gap-1">
                        <button onclick="changeMonth(-1)"
                            class="p-2 rounded-lg hover:bg-surface-container-low transition-colors text-on-surface-variant hover:text-primary">
                            <span class="material-symbols-outlined">chevron_left</span>
                        </button>
                        <button onclick="goToday()"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary-container text-on-primary-container hover:opacity-80 transition-opacity">Hari
                            Ini</button>
                        <button onclick="changeMonth(1)"
                            class="p-2 rounded-lg hover:bg-surface-container-low transition-colors text-on-surface-variant hover:text-primary">
                            <span class="material-symbols-outlined">chevron_right</span>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-7 gap-1.5 mb-2 text-center">
                    <div class="text-xs font-bold uppercase tracking-wider text-red-400">Min</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Sen</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Sel</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Rab</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Kam</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Jum</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Sab</div>
                </div>
                <div id="cal-grid" class="grid grid-cols-7 gap-1.5"></div>
            </div>
        </section>

        <!-- Intern roster quick table -->
        <section class="glass-card p-5 rounded-2xl border border-outline-variant mt-6">
            <h3 class="font-headline-md mb-3">Daftar Intern</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-surface-container-lowest">
                        <tr>
                            <th class="px-3 py-2 text-left">Nama</th>
                            <th class="px-3 py-2 text-left">Divisi</th>
                            <th class="px-3 py-2 text-center">Hadir</th>
                            <th class="px-3 py-2 text-center">Telat</th>
                            <th class="px-3 py-2 text-center">Tidak Masuk</th>
                            <th class="px-3 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="intern-roster-body" class="divide-y divide-outline-variant"></tbody>
                </table>
            </div>
        </section>
        </div>
        <div class="mt-auto shrink-0 w-full">
            <?php include '../partials/footer.php'; ?>
        </div>
    </main>

    <!-- Detail modal: rincian kehadiran per-intern pada satu tanggal -->
    <div id="detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-backdrop">
        <div
            class="bg-white rounded-2xl w-full max-w-lg shadow-2xl border border-outline-variant overflow-hidden max-h-[85vh] flex flex-col">
            <div class="p-5 border-b border-outline-variant flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-0.5">Rincian
                        Kehadiran</p>
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

    <script>
        let calYear, calMonth;
        let dbInternsList = [];
        let dbAttendanceMap = {}; // { username: { date: record } }

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
            const now = new Date();
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
                }
            } catch (err) {
                console.warn('Gagal memuat data presensi dari MySQL:', err);
            }
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
                const dbUserAtt = dbAttendanceMap[intern.name];
                if (dbUserAtt && dbUserAtt[dateStr]) {
                    rec = dbUserAtt[dateStr];
                }
                return { intern, record: rec };
            });

            const present = entries.filter(e => e.record?.status === 'present').length;
            const late = entries.filter(e => e.record?.status === 'late').length;
            const absent = entries.filter(e => e.record?.status === 'absent').length;
            const unmarked = entries.filter(e => !e.record).length;

            return { entries, total: interns.length, present, late, absent, unmarked };
        }

        function changeMonth(dir) {
            calMonth += dir;
            if (calMonth > 11) { calMonth = 0; calYear++; }
            if (calMonth < 0) { calMonth = 11; calYear--; }
            renderAll();
        }

        function goToday() {
            const now = new Date();
            calYear = now.getFullYear();
            calMonth = now.getMonth();
            renderAll();
        }

        function dayClass(summary, isFuture, isToday = false, dateStr = '') {
            if (isFuture) return 'future';
            if (summary.total === 0) return 'pending';

            // Pengecekan apakah hari ini sudah lewat jam 14:00
            const now = new Date();
            const isTodayPastCutoff = isToday && (now.getHours() >= 14);
            const isPastDay = !isFuture && !isToday;

            // Jika ada yang absen langsung MERAH, ATAU jika hari lalu / hari ini sudah lewat jam 14:00 dan masih ada yang belum absen (unmarked) -> MERAH
            if (summary.absent > 0 || ((isPastDay || isTodayPastCutoff) && summary.unmarked > 0)) {
                return 'absent';
            }

            if (summary.late > 0) return 'late';
            if (summary.unmarked > 0) return 'pending';
            return 'complete';
        }

        function renderCalendar() {
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            document.getElementById('cal-title').textContent = `${monthNames[calMonth]} ${calYear}`;

            const grid = document.getElementById('cal-grid');
            grid.innerHTML = '';

            const firstDay = new Date(calYear, calMonth, 1).getDay();
            const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
            const today = new Date();
            const todayStr = fmt(today);

            for (let i = 0; i < firstDay; i++) {
                const cell = document.createElement('div');
                cell.className = 'cal-cell empty';
                grid.appendChild(cell);
            }

            let daysComplete = 0, daysLate = 0, daysAbsent = 0;

            for (let d = 1; d <= daysInMonth; d++) {
                const dateObj = new Date(calYear, calMonth, d);
                const dateStr = fmt(dateObj);
                const dow = dateObj.getDay();
                const isWeekend = (dow === 0 || dow === 6);
                const isFuture = dateObj > today && dateStr !== todayStr;
                const isToday = dateStr === todayStr;

                const cell = document.createElement('div');
                cell.classList.add('cal-cell');

                const summary = getCombinedDaySummary(dateStr);
                const hasRecords = summary.entries.some(e => e.record !== null);

                // Sabtu (6) dan Minggu (0) keduanya abu-abu (weekend)
                const isSunday = (dow === 0);

                if ((isSunday) && !hasRecords) {
                    cell.classList.add('weekend');
                } else if ((isSunday) && hasRecords) {
                    // Akhir pekan tapi ada data absensi (lembur/khusus)
                    const cls = dayClass(summary, isFuture, isToday, dateStr);
                    cell.classList.add(`status-${cls}`);
                    cell.addEventListener('click', () => openDetailModal(dateStr));
                } else {
                    const cls = dayClass(summary, isFuture, isToday, dateStr);
                    if (!isFuture) {
                        if (cls === 'absent') daysAbsent++;
                        else if (summary.late > 0) daysLate++;
                        else if (summary.unmarked === 0 && summary.present > 0) daysComplete++;
                    }
                    cell.classList.add(`status-${cls}`);

                    if (!isFuture && summary.total > 0) {
                        cell.addEventListener('click', () => openDetailModal(dateStr));
                    } else if (isFuture) {
                        cell.classList.add('future');
                    }

                    // Tampilkan dot status (hanya jika ada record yang tercatat)
                    if (!isFuture && hasRecords && cls !== 'pending') {
                        const dot = document.createElement('div');
                        dot.className = 'status-dot';
                        dot.style.background = cls === 'complete' ? '#16a34a' : cls === 'late' ? '#d97706' : '#dc2626';
                        cell.appendChild(dot);
                    }
                }

                if (isToday) cell.classList.add('today');
                const span = document.createElement('span');
                span.textContent = d;
                cell.appendChild(span);
                grid.appendChild(cell);
            }

            document.getElementById('stat-days-complete').textContent = daysComplete;
            document.getElementById('stat-days-late').textContent = daysLate;
            document.getElementById('stat-days-absent').textContent = daysAbsent;
        }

        function openDetailModal(dateStr) {
            const summary = getCombinedDaySummary(dateStr);
            document.getElementById('detail-date-label').textContent = formatDateDisplay(dateStr);
            const list = document.getElementById('detail-list');
            list.innerHTML = '';

            if (summary.entries.length === 0) {
                list.innerHTML = '<p class="text-sm text-on-surface-variant italic">Belum ada intern terdaftar.</p>';
            } else {
                const todayStr = fmt(new Date());
                const now = new Date();
                const isTodayPastCutoff = (dateStr === todayStr) && (now.getHours() >= 14);
                const isPastDay = dateStr < todayStr;

                summary.entries.forEach(({ intern, record }) => {
                    let status = record ? record.status : 'pending';
                    if (!record && (isPastDay || isTodayPastCutoff)) {
                        status = 'absent';
                    }

                    const label = { present: 'Hadir Tepat Waktu', late: 'Terlambat', absent: 'Tidak Masuk', pending: 'Belum Absen' }[status] || 'Belum Absen';
                    const badgeClass = { present: 'badge-present', late: 'badge-late', absent: 'badge-absent', pending: 'badge-pending' }[status] || 'badge-pending';
                    const icon = { present: 'check_circle', late: 'schedule', absent: 'cancel', pending: 'help' }[status] || 'help';

                    const row = document.createElement('div');
                    row.className = 'border border-outline-variant rounded-xl p-3.5 bg-surface-container-lowest shadow-xs';
                    row.innerHTML = `
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-full bg-primary-container text-on-primary-container font-bold flex items-center justify-center text-sm">
                                        ${escHtml(intern.name.charAt(0).toUpperCase())}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-on-surface">${escHtml(intern.name)}</div>
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

                            ${record && record.photo ? `
                            <div class="mt-2 rounded-lg overflow-hidden border border-outline-variant/60">
                                <img src="../${escHtml(record.photo)}" class="w-full h-32 object-cover" alt="Foto Absen">
                            </div>` : ''}

                            ${record && record.location ? `
                            <div class="mt-2 text-xs text-slate-600 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px] text-primary">location_on</span>
                                <span class="truncate">${escHtml(record.location)}</span>
                            </div>` : ''}

                            ${record && record.lat && record.lng ? `
                            <div class="mt-1 text-[11px] text-indigo-700 font-mono flex items-center gap-1 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100 w-fit">
                                <span class="material-symbols-outlined text-[13px]">pin_drop</span>
                                <span>📍 ${record.lat.toFixed(6)}, ${record.lng.toFixed(6)}</span>
                            </div>` : ''}

                            ${record && record.reason ? `<div class="mt-2 bg-amber-50 border border-amber-200 rounded-lg p-2.5 text-xs text-amber-900"><strong>Alasan / Keterangan:</strong> ${escHtml(record.reason)}</div>` : ''}
                            ${!record ? '<p class="text-xs text-on-surface-variant italic mt-1.5">Intern ini belum melakukan absensi pada hari tersebut.</p>' : ''}
                            <div class="mt-2 text-right border-t border-outline-variant/40 pt-2">
                                <a href="../attendance.php?intern=${encodeURIComponent(intern.name)}" class="text-xs text-primary font-bold hover:underline">Lihat kalender intern &rarr;</a>
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

        function renderRoster() {
            // Gunakan HANYA data intern dari database MySQL
            let interns = dbInternsList;
            if (!interns.length && window.DB_INTERNS && window.DB_INTERNS.length) {
                interns = window.DB_INTERNS.map(u => ({ name: u.username, division: 'Intern Kedayweb' }));
            }

            document.getElementById('stat-total-interns').textContent = interns.length;
            const tbody = document.getElementById('intern-roster-body');
            tbody.innerHTML = '';

            if (!interns.length) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-3 py-6 text-center text-on-surface-variant text-sm italic">Belum ada intern terdaftar di database.</td></tr>';
                return;
            }

            interns.forEach(i => {
                // Hitung HANYA dari DB map
                const dbUserAtt = dbAttendanceMap[i.name] || {};
                let present = 0, late = 0, absent = 0;

                Object.values(dbUserAtt).forEach(r => {
                    if (r.status === 'present') present++;
                    else if (r.status === 'late') late++;
                    else if (r.status === 'absent') absent++;
                });

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-3 py-2 font-semibold">${escHtml(i.name)}</td>
                    <td class="px-3 py-2 text-on-surface-variant">${escHtml(i.division || 'Intern Kedayweb')}</td>
                    <td class="px-3 py-2 text-center text-green-700 font-bold">${present}</td>
                    <td class="px-3 py-2 text-center text-amber-700 font-bold">${late}</td>
                    <td class="px-3 py-2 text-center text-red-700 font-bold">${absent}</td>
                    <td class="px-3 py-2 text-center">
                        <a href="../attendance.php?intern=${encodeURIComponent(i.name)}" class="text-primary hover:underline text-xs font-semibold">Lihat Kalender</a>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function renderAll() {
            renderCalendar();
            renderRoster();
        }

        function exportAllAdminAttendanceCSV() {
            // Gunakan HANYA data dari database MySQL
            const interns = dbInternsList;

            if (!interns.length) {
                alert('Tidak ada data intern.');
                return;
            }

            const statusLabel = { present: 'Hadir', late: 'Terlambat', absent: 'Tidak Masuk' };
            const allRecords = [];

            // Kumpulkan data dari DB MySQL dan Store
            interns.forEach(intern => {
                const dbUserAtt = dbAttendanceMap[intern.name] || {};
                Object.values(dbUserAtt).forEach(r => {
                    allRecords.push({
                        internName: intern.name,
                        division: intern.division || 'Intern Kedayweb',
                        date: r.date,
                        clockIn: r.clockIn || '--:--',
                        clockOut: r.clockOut || '--:--',
                        status: r.status,
                        reason: r.reason || '-'
                    });
                });
            });

            if (!allRecords.length) {
                alert('Belum ada data kehadiran intern yang tercatat.');
                return;
            }

            allRecords.sort((a, b) => {
                const dtA = `${a.date} ${a.clockIn !== '--:--' ? a.clockIn : '00:00'}`;
                const dtB = `${b.date} ${b.clockIn !== '--:--' ? b.clockIn : '00:00'}`;
                return dtB.localeCompare(dtA);
            });

            const rows = [['Nama Intern', 'Divisi', 'Tanggal', 'Jam Masuk', 'Jam Keluar', 'Status', 'Alasan']];

            allRecords.forEach(r => {
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
                    statusLabel[r.status] || r.status,
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
            link.download = `Kehadiran_Semua_Intern_${new Date().toISOString().slice(0, 10)}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }

        document.addEventListener('DOMContentLoaded', async () => {
            if (window.ProjectStore) ProjectStore.setRole('admin');
            initCalendar();
            // Render dulu kalender kosong, lalu setelah DB data masuk re-render
            renderAll();
            await fetchDbAttendance();
            renderAll(); // re-render setelah data DB tersedia
        });
    </script>
</body>

</html>