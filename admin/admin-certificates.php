<?php
require_once __DIR__ . '/../session.php';
require_admin();
require_once __DIR__ . '/../Login/koneksi.php';

// Auto sync certificates for all intern users
if ($conn) {
    sync_all_intern_certificates($conn);
}

// Ambil daftar user intern untuk dropdown
$users = [];
$q = mysqli_query($conn, "SELECT id, username FROM users WHERE role='intern' ORDER BY username ASC");
while ($row = mysqli_fetch_assoc($q)) $users[] = $row;

// Ambil daftar tahun angkatan yang ada di certificates (bisa dari format ID seperti IS-2024-001 atau tanggal)
$cohort_years = [];
$qy = mysqli_query($conn, "SELECT DISTINCT CAST(COALESCE(NULLIF(REGEXP_SUBSTR(certificate_id, '[0-9]{4}'), ''), YEAR(start_date), YEAR(issue_date), YEAR(created_at)) AS UNSIGNED) as yr FROM certificates ORDER BY yr DESC");
if ($qy) {
    while ($ry = mysqli_fetch_assoc($qy)) {
        if (!empty($ry['yr'])) $cohort_years[] = (int)$ry['yr'];
    }
}
$current_yr = (int)date('Y');
if (!in_array($current_yr, $cohort_years)) {
    array_unshift($cohort_years, $current_yr);
}
rsort($cohort_years);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <title>Kelola Sertifikat – Admin Kedayweb</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="../shared-config.js"></script>
    <link rel="stylesheet" href="../style.css"/>
    <style>
        .filled-icon { font-variation-settings:'FILL' 1,'wght' 400,'GRAD' 0,'opsz' 24; }
        dialog::backdrop { background: rgba(0,0,0,.5); backdrop-filter: blur(4px); }
        dialog { border-radius: 1rem; border: none; box-shadow: 0 25px 50px -12px rgba(0,0,0,.25); }
        @keyframes fadeIn { from{opacity:0;transform:scale(.96)} to{opacity:1;transform:scale(1)} }
        dialog[open] { animation: fadeIn .2s ease; }
    </style>
</head>
<body class="bg-background text-on-surface font-body-md flex h-screen overflow-hidden">

<?php
$active           = 'certificates';
$sidebar_subtitle = 'Admin Panel';
include '../partials/sidebar-admin.php';
?>

<main class="flex-1 flex flex-col md:ml-[16.5rem] h-screen overflow-y-auto">
    <!-- Header -->
    <header class="w-full h-20 bg-surface-container-lowest border-b border-outline-variant sticky top-0 z-10 flex justify-between items-center px-4 sm:px-6 shrink-0">
        <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1 mr-2 sm:mr-4">
            <button onclick="toggleMobileSidebar()" class="md:hidden text-on-surface hover:text-primary focus:outline-none flex items-center p-1 rounded-lg hover:bg-surface-container-high shrink-0" aria-label="Toggle Sidebar">
                <span class="material-symbols-outlined text-2xl">menu</span>
            </button>
            <span class="material-symbols-outlined filled-icon text-primary text-2xl shrink-0">workspace_premium</span>
            <h2 class="font-headline-sm sm:font-headline-lg font-bold text-on-surface truncate whitespace-nowrap">
                Kelola Sertifikat
            </h2>
        </div>
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <button id="btn-add" onclick="openModal()"
                    class="bg-primary text-on-primary px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl text-xs sm:font-label-md flex items-center gap-1.5 sm:gap-2 hover:opacity-90 transition-all active:scale-95 shadow cursor-pointer shrink-0">
                <span class="material-symbols-outlined text-[18px] sm:text-[20px]">add_circle</span>
                <span class="hidden sm:inline">Tambah Sertifikat</span>
                <span class="sm:hidden font-semibold">Tambah</span>
            </button>
            <div class="flex items-center gap-1 sm:gap-sm p-1 sm:p-1.5 px-2 sm:px-3 rounded-full border border-outline-variant bg-surface-bright shadow-2xs shrink-0">
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container overflow-hidden shrink-0">
                    <span class="material-symbols-outlined text-[18px] sm:text-[20px]" style="font-variation-settings: 'FILL' 1;">account_circle</span>
                </div>
                <span class="hidden sm:inline-block font-label-md"><?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="../logout.php" class="text-error hover:text-red-700 hover:bg-red-50 p-1 sm:p-1.5 rounded-full transition-colors flex items-center justify-center" title="Keluar" aria-label="Keluar"><span class="material-symbols-outlined text-[18px] sm:text-[20px]">logout</span></a>
            </div>
        </div>
    </header>

    <div class="p-4 sm:p-6 space-y-6 flex-1">

        <!-- Search, Angkatan Filter + Table -->
        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-xs overflow-hidden">
            <!-- Header & Toolbar -->
            <div class="p-5 border-b border-outline-variant flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between bg-surface-container-low/40">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-primary text-xl" style="font-variation-settings: 'FILL' 1;">workspace_premium</span>
                        <h3 class="font-headline-md font-bold text-on-surface">Daftar Sertifikat Intern</h3>
                    </div>
                    <p class="text-xs text-on-surface-variant mt-0.5">Kelola verifikasi, status terbit, dan arsip sertifikat berdasarkan angkatan tahun masuk.</p>
                </div>

                <!-- Action Controls: Year Tabs, Sort Control & Search -->
                <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
                    <!-- Filter Angkatan -->
                    <div class="flex items-center gap-1.5 bg-surface-bright border border-outline-variant rounded-xl px-2.5 py-1 shadow-2xs">
                        <span class="material-symbols-outlined text-primary text-[18px]">calendar_today</span>
                        <label for="year-select" class="text-xs font-semibold text-on-surface-variant whitespace-nowrap">Angkatan:</label>
                        <select id="year-select" onchange="filterCohortYear(this.value)"
                                class="bg-transparent border-none text-xs font-bold text-primary focus:outline-none focus:ring-0 py-1 pl-1 pr-6 cursor-pointer">
                            <option value="all">Semua Tahun</option>
                            <?php foreach ($cohort_years as $yr): ?>
                                <option value="<?php echo $yr; ?>" <?php echo ($yr == date('Y')) ? 'selected' : ''; ?>>Angkatan <?php echo $yr; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Sort Control -->
                    <div class="flex items-center gap-1 bg-surface-bright border border-outline-variant rounded-xl px-2 py-1 shadow-2xs">
                        <span class="material-symbols-outlined text-primary text-[18px]">sort</span>
                        <label for="sort-by-select" class="text-xs font-semibold text-on-surface-variant whitespace-nowrap">Urut:</label>
                        <select id="sort-by-select" onchange="changeSortBy(this.value)"
                                class="bg-transparent border-none text-xs font-bold text-primary focus:outline-none focus:ring-0 py-1 pl-1 pr-5 cursor-pointer">
                            <option value="year">Tahun Angkatan (ID)</option>
                            <option value="id">ID Sertifikat</option>
                            <option value="name">Nama Intern</option>
                            <option value="created_at">Waktu Dibuat</option>
                        </select>
                        <button type="button" id="sort-dir-btn" onclick="toggleSortDir()" class="p-1 rounded-lg hover:bg-surface-container-high text-primary flex items-center justify-center transition-colors" title="Ubah Arah Urutan">
                            <span id="sort-dir-icon" class="material-symbols-outlined text-[16px]">arrow_downward</span>
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative flex-1 sm:w-60 min-w-[180px]">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                        <input id="search-input" type="text" placeholder="Cari nama, ID, posisi..."
                               class="w-full pl-9 pr-4 py-1.5 rounded-xl border border-outline-variant bg-surface-bright focus:outline-none focus:ring-2 focus:ring-primary text-xs font-medium transition-colors shadow-2xs"
                               oninput="loadCertificates()"/>
                    </div>
                </div>
            </div>

            <!-- Cohort Pills Bar (Quick Navigation) — rebuilt dynamically via JS -->
            <div id="cohort-pills-bar" class="flex items-center gap-2 px-5 py-2.5 bg-surface-container-low/20 border-b border-outline-variant overflow-x-auto text-xs">
                <span class="text-on-surface-variant font-medium shrink-0 mr-1">Filter Cepat:</span>
                <!-- Pills diisi otomatis oleh rebuildCohortPills() -->
                <button type="button" onclick="setCohortPill('all')" data-cohort="all" class="cohort-pill px-3 py-1 rounded-full text-xs font-bold transition-all bg-primary text-on-primary shadow-xs">
                    Semua Angkatan
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-surface-container-low text-on-surface-variant font-label-sm uppercase tracking-wider text-xs select-none">
                        <tr>
                            <th onclick="applyHeaderSort('id')" class="px-4 py-3 text-left cursor-pointer hover:text-primary transition-colors">
                                <div class="flex items-center gap-1">
                                    <span>ID Sertifikat</span>
                                    <span id="th-sort-id" class="material-symbols-outlined text-[14px] opacity-40">unfold_more</span>
                                </div>
                            </th>
                            <th onclick="applyHeaderSort('name')" class="px-4 py-3 text-left cursor-pointer hover:text-primary transition-colors">
                                <div class="flex items-center gap-1">
                                    <span>Nama Intern</span>
                                    <span id="th-sort-name" class="material-symbols-outlined text-[14px] opacity-40">unfold_more</span>
                                </div>
                            </th>
                            <th onclick="applyHeaderSort('year')" class="px-4 py-3 text-center cursor-pointer hover:text-primary transition-colors">
                                <div class="flex items-center justify-center gap-1">
                                    <span>Angkatan (Tahun)</span>
                                    <span id="th-sort-year" class="material-symbols-outlined text-[14px] text-primary">arrow_downward</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left">Posisi</th>
                            <th class="px-4 py-3 text-left">Universitas</th>
                            <th class="px-4 py-3 text-center">TOGGLE</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Terbit</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="cert-table-body" class="divide-y divide-outline-variant">
                        <tr><td colspan="9" class="px-4 py-8 text-center text-on-surface-variant">Memuat data…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <div class="mt-auto shrink-0 w-full">
        <?php include '../partials/footer.php'; ?>
    </div>
</main>

<!-- ── MODAL Add / Edit ── -->
<dialog id="cert-modal" class="w-full max-w-2xl p-0">
    <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant bg-surface-container-low">
        <h3 id="modal-title" class="font-headline-md font-bold">Tambah Sertifikat</h3>
        <button onclick="closeModal()" class="p-2 rounded-full hover:bg-surface-container-high transition-colors">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    <form id="cert-form" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto bg-surface" onsubmit="saveCertificate(event)">
        <input type="hidden" id="f-id" name="id"/>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">ID Sertifikat <span class="text-error">*</span></label>
                <div class="flex gap-2 items-center">
                    <input id="f-cert-id" name="certificate_id" type="text" required placeholder="IS-2026-001"
                           class="input-field flex-1 rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm font-mono"/>
                    <button type="button" id="btn-regen-id" onclick="fetchNextCertId()" title="Generate ID otomatis"
                            class="shrink-0 p-2 rounded-xl border border-outline-variant bg-surface-container-low hover:bg-primary hover:text-on-primary hover:border-primary transition-all text-on-surface-variant flex items-center gap-1">
                        <span class="material-symbols-outlined text-[18px]">auto_awesome</span>
                    </button>
                </div>
                <p id="cert-id-hint" class="text-[10px] text-on-surface-variant mt-0.5 ml-0.5">Auto-generate dari tahun aktif. Bisa diedit manual.</p>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">User Intern (opsional)</label>
                <select id="f-user-id" name="user_id"
                        class="input-field w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm">
                    <option value="">— Tidak terhubung ke user —</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Nama Intern <span class="text-error">*</span></label>
                <input id="f-name" name="intern_name" type="text" required
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Posisi Magang <span class="text-error">*</span></label>
                <input id="f-position" name="intern_position" type="text" required
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Universitas</label>
                <input id="f-university" name="university" type="text"
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Jurusan</label>
                <input id="f-major" name="major" type="text"
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Tanggal Mulai <span class="text-error">*</span></label>
                <input id="f-start" name="start_date" type="date" required
                       oninput="if(!document.getElementById('f-cert-id').readOnly) fetchNextCertId(new Date(this.value).getFullYear())"
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Tanggal Selesai <span class="text-error">*</span></label>
                <input id="f-end" name="end_date" type="date" required
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Tanggal Terbit <span class="text-error">*</span></label>
                <input id="f-issue" name="issue_date" type="date" required
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Nilai Akhir</label>
                <input id="f-grade" name="final_grade" type="text" placeholder="A, A+, B+"
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
        </div>

        <!-- Scores -->
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Skor Teknis (0-100)</label>
                <input id="f-tech" name="score_technical" type="number" min="0" max="100" value="0"
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Skor Disiplin (0-100)</label>
                <input id="f-disc" name="score_discipline" type="number" min="0" max="100" value="0"
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Skor Sikap (0-100)</label>
                <input id="f-att" name="score_attitude" type="number" min="0" max="100" value="0"
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Nama Pembimbing</label>
                <input id="f-supervisor" name="supervisor_name" type="text"
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Status</label>
                <select id="f-status" name="status"
                        class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm">
                    <option value="active">Aktif</option>
                    <option value="revoked">Dicabut</option>
                </select>
            </div>
        </div>
        <div>
            <label class="block font-label-sm text-on-surface-variant mb-1">Catatan</label>
            <textarea id="f-notes" name="notes" rows="2"
                      class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm resize-none"></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2 border-t border-outline-variant">
            <button type="button" onclick="closeModal()"
                    class="px-4 py-2 rounded-xl border border-outline hover:bg-surface-container-low font-label-md transition-colors">
                Batal
            </button>
            <button type="submit" id="save-btn"
                    class="px-6 py-2 rounded-xl bg-primary text-on-primary font-label-md hover:opacity-90 transition-all flex items-center gap-2 shadow">
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span id="save-label">Simpan</span>
            </button>
        </div>
    </form>
</dialog>

<!-- ── Confirm Delete Modal ── -->
<dialog id="del-modal" class="w-full max-w-sm p-6 text-center bg-surface">
    <div class="w-16 h-16 rounded-full bg-error-container flex items-center justify-center mx-auto mb-4">
        <span class="material-symbols-outlined text-error text-3xl filled-icon">delete_forever</span>
    </div>
    <h3 class="font-headline-md font-bold mb-2">Hapus Sertifikat?</h3>
    <p class="text-on-surface-variant text-sm mb-6">Tindakan ini tidak dapat dibatalkan.</p>
    <div class="flex gap-3 justify-center">
        <button onclick="document.getElementById('del-modal').close()"
                class="px-4 py-2 rounded-xl border border-outline font-label-md hover:bg-surface-container-low">Batal</button>
        <button id="confirm-del-btn"
                class="px-4 py-2 rounded-xl bg-error text-on-error font-label-md hover:opacity-90">Hapus</button>
    </div>
</dialog>

<script>
let editingId = null;

// ── Cohort Year Filters & Sort State ─────────────────────────────────────────
let currentCohortYear = 'all';
let currentSortBy     = 'year';
let currentSortDir    = 'desc';

function filterCohortYear(year) {
    currentCohortYear = year;
    // Sync dropdown
    const select = document.getElementById('year-select');
    if (select && select.value !== year) select.value = year;

    highlightActivePill(year);
    loadCertificates();
}

function highlightActivePill(year) {
    document.querySelectorAll('.cohort-pill').forEach(pill => {
        const pYear = pill.getAttribute('data-cohort');
        if (pYear === year) {
            pill.className = 'cohort-pill px-3 py-1 rounded-full text-xs font-bold transition-all bg-primary text-on-primary shadow-xs';
        } else {
            pill.className = 'cohort-pill px-3 py-1 rounded-full text-xs font-semibold text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high border border-outline-variant/60 transition-all';
        }
    });
}

function setCohortPill(year) {
    filterCohortYear(year);
}

function changeSortBy(sortBy) {
    currentSortBy = sortBy;
    updateSortIcons();
    loadCertificates();
}

function toggleSortDir() {
    currentSortDir = (currentSortDir === 'desc') ? 'asc' : 'desc';
    updateSortIcons();
    loadCertificates();
}

function applyHeaderSort(sortBy) {
    if (currentSortBy === sortBy) {
        currentSortDir = (currentSortDir === 'desc') ? 'asc' : 'desc';
    } else {
        currentSortBy = sortBy;
        currentSortDir = (sortBy === 'name') ? 'asc' : 'desc';
    }
    const select = document.getElementById('sort-by-select');
    if (select) select.value = currentSortBy;
    updateSortIcons();
    loadCertificates();
}

function updateSortIcons() {
    const dirIcon = document.getElementById('sort-dir-icon');
    if (dirIcon) {
        dirIcon.textContent = currentSortDir === 'desc' ? 'arrow_downward' : 'arrow_upward';
    }

    ['id', 'name', 'year'].forEach(key => {
        const icon = document.getElementById(`th-sort-${key}`);
        if (!icon) return;
        if (currentSortBy === key) {
            icon.textContent = currentSortDir === 'desc' ? 'arrow_downward' : 'arrow_upward';
            icon.className = 'material-symbols-outlined text-[14px] text-primary font-bold';
        } else {
            icon.textContent = 'unfold_more';
            icon.className = 'material-symbols-outlined text-[14px] opacity-40';
        }
    });
}

// ── Rebuild cohort pills dynamically based on all data (fetch without year filter) ─
let _allKnownYears = new Set();

async function rebuildCohortPills() {
    // Ambil semua data (tanpa filter tahun) untuk mengetahui angkatan yang ada
    const search = document.getElementById('search-input').value;
    const url = `../certificate-api.php?action=list&search=${encodeURIComponent(search)}&year=all&sort_by=year&sort_dir=desc`;
    try {
        const res  = await fetch(url);
        const json = await res.json();
        if (!json.success) return;

        // Kumpulkan semua tahun unik dari data
        const years = [...new Set(
            json.data
                .map(c => c.entry_year)
                .filter(y => y && !isNaN(parseInt(y)))
                .map(y => parseInt(y))
        )].sort((a, b) => b - a); // descending

        // Cek apakah perlu rebuild (jika set tahun berubah)
        const newSet = years.join(',');
        const oldSet = [..._allKnownYears].sort((a, b) => b - a).join(',');
        if (newSet === oldSet) return; // tidak ada perubahan, skip rebuild

        _allKnownYears = new Set(years);

        // Rebuild pills
        const bar = document.getElementById('cohort-pills-bar');
        if (!bar) return;

        // Hapus semua pill lama kecuali label teks
        bar.querySelectorAll('.cohort-pill').forEach(p => p.remove());

        // Tambahkan pill "Semua Angkatan"
        bar.appendChild(makePill('all', 'Semua Angkatan'));

        // Tambahkan pill per tahun angkatan
        years.forEach(yr => {
            bar.appendChild(makePill(String(yr), `Angkatan ${yr}`));
        });

        // Sync dropdown angkatan juga
        syncYearSelectOptions(years);

        // Terapkan highlight sesuai currentCohortYear
        highlightActivePill(currentCohortYear);
    } catch (e) {
        console.warn('rebuildCohortPills error:', e);
    }
}

function makePill(cohortValue, label) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.setAttribute('data-cohort', cohortValue);
    btn.className = 'cohort-pill px-3 py-1 rounded-full text-xs font-semibold text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high border border-outline-variant/60 transition-all';
    btn.textContent = label;
    btn.onclick = () => setCohortPill(cohortValue);
    return btn;
}

function syncYearSelectOptions(years) {
    const select = document.getElementById('year-select');
    if (!select) return;
    const currentVal = select.value;
    // Simpan option pertama (Semua Tahun)
    select.innerHTML = '<option value="all">Semua Tahun</option>';
    years.forEach(yr => {
        const opt = document.createElement('option');
        opt.value = yr;
        opt.textContent = `Angkatan ${yr}`;
        select.appendChild(opt);
    });
    // Kembalikan nilai yang dipilih jika masih ada
    if ([...select.options].some(o => o.value === currentVal)) {
        select.value = currentVal;
    } else {
        select.value = 'all';
        if (currentCohortYear !== 'all') {
            currentCohortYear = 'all';
        }
    }
}

// ── Load certificates ──────────────────────────────────────────────────────
async function loadCertificates() {
    const search = document.getElementById('search-input').value;
    const url = `../certificate-api.php?action=list&search=${encodeURIComponent(search)}&year=${encodeURIComponent(currentCohortYear)}&sort_by=${encodeURIComponent(currentSortBy)}&sort_dir=${encodeURIComponent(currentSortDir)}`;
    const res    = await fetch(url);
    const json   = await res.json();
    const tbody  = document.getElementById('cert-table-body');

    if (!json.success) {
        tbody.innerHTML = `<tr><td colspan="9" class="px-4 py-8 text-center text-error">${json.message}</td></tr>`;
        return;
    }

    const data = json.data;

    if (document.getElementById('stat-total')) document.getElementById('stat-total').textContent = data.length;
    if (document.getElementById('stat-active')) document.getElementById('stat-active').textContent = data.filter(d => d.status === 'active').length;
    if (document.getElementById('stat-revoked')) document.getElementById('stat-revoked').textContent = data.filter(d => d.status === 'revoked').length;

    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="px-4 py-8 text-center text-on-surface-variant">Tidak ada sertifikat ditemukan untuk kriteria ini.</td></tr>`;
        return;
    }

    tbody.innerHTML = data.map(c => `
        <tr class="hover:bg-surface-container-low transition-colors">
            <td class="px-4 py-3 font-mono text-sm font-bold text-primary">${c.certificate_id}</td>
            <td class="px-4 py-3 font-medium">${c.intern_name}</td>
            <td class="px-4 py-3 text-center">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-800 border border-blue-200">
                    <span class="material-symbols-outlined text-[13px] text-blue-600">calendar_month</span>
                    ${c.entry_year || '—'}
                </span>
            </td>
            <td class="px-4 py-3 text-on-surface-variant">${c.intern_position}</td>
            <td class="px-4 py-3 text-on-surface-variant text-xs">${c.university || '—'}</td>
            <td class="px-4 py-3 text-center">
                <label class="relative inline-flex items-center cursor-pointer select-none">
                    <input type="checkbox" class="sr-only peer" ${c.status === 'active' ? 'checked' : ''} onchange="toggleSingleCertStatus(${c.id}, this.checked ? 'active' : 'revoked')"/>
                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                </label>
            </td>
            <td class="px-4 py-3 text-center">
                ${c.status === 'active'
                    ? '<span class="bg-[#dcfce7] text-[#166534] px-2.5 py-1 rounded-full text-xs font-bold">Aktif</span>'
                    : '<span class="bg-error-container text-error px-2.5 py-1 rounded-full text-xs font-bold">Dicabut</span>'}
            </td>
            <td class="px-4 py-3 text-center text-xs text-on-surface-variant">${c.issue_fmt}</td>
            <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center gap-1">
                    <a href="../verification.php?id=${encodeURIComponent(c.certificate_id)}" target="_blank"
                       class="p-1.5 rounded-lg hover:bg-primary-fixed text-primary transition-colors" title="Lihat">
                        <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                    </a>
                    <button onclick="editCertificate(${c.id})"
                            class="p-1.5 rounded-lg hover:bg-surface-container-high text-on-surface-variant transition-colors" title="Edit">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                    </button>
                    <button onclick="confirmDelete(${c.id})"
                            class="p-1.5 rounded-lg hover:bg-error-container text-error transition-colors" title="Hapus">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ── Auto-generate Certificate ID ──────────────────────────────────────────
async function fetchNextCertId(year = null) {
    const btn = document.getElementById('btn-regen-id');
    if (btn) { btn.disabled = true; btn.querySelector('span').textContent = 'hourglass_empty'; }

    // Pakai tahun dari start_date jika sudah diisi, fallback ke tahun sekarang
    if (!year) {
        const startVal = document.getElementById('f-start')?.value;
        year = startVal ? new Date(startVal).getFullYear() : new Date().getFullYear();
    }

    try {
        const res  = await fetch(`../certificate-api.php?action=next_id&year=${year}`);
        const json = await res.json();
        if (json.success) {
            const field = document.getElementById('f-cert-id');
            if (field && !field.readOnly) {
                field.value = json.next_id;
                field.classList.add('ring-2', 'ring-primary');
                setTimeout(() => field.classList.remove('ring-2', 'ring-primary'), 1200);
            }
            const hint = document.getElementById('cert-id-hint');
            if (hint) hint.textContent = `Urutan ke-${json.seq} untuk angkatan ${json.year}. Bisa diedit manual.`;
        }
    } catch(e) {
        console.warn('fetchNextCertId error:', e);
    } finally {
        if (btn) { btn.disabled = false; btn.querySelector('span').textContent = 'auto_awesome'; }
    }
}

// ── Modal helpers ──────────────────────────────────────────────────────────
function openModal(data = null) {
    editingId = data ? data.id : null;
    document.getElementById('modal-title').textContent = data ? 'Edit Sertifikat' : 'Tambah Sertifikat';
    document.getElementById('save-label').textContent  = data ? 'Perbarui' : 'Simpan';
    const form = document.getElementById('cert-form');
    form.reset();

    // Map fields
    const map = {
        'f-id':         data?.id ?? '',
        'f-cert-id':    data?.certificate_id ?? '',
        'f-user-id':    data?.user_id ?? '',
        'f-name':       data?.intern_name ?? '',
        'f-position':   data?.intern_position ?? '',
        'f-university': data?.university ?? '',
        'f-major':      data?.major ?? '',
        'f-start':      data?.start_date ?? '',
        'f-end':        data?.end_date ?? '',
        'f-issue':      data?.issue_date ?? '',
        'f-grade':      data?.final_grade ?? '',
        'f-tech':       data?.score_technical ?? 0,
        'f-disc':       data?.score_discipline ?? 0,
        'f-att':        data?.score_attitude ?? 0,
        'f-supervisor': data?.supervisor_name ?? '',
        'f-status':     data?.status ?? 'active',
        'f-notes':      data?.notes ?? '',
    };
    for (const [id, val] of Object.entries(map)) {
        const el = document.getElementById(id);
        if (el) el.value = val ?? '';
    }
    // Disable cert-id field on edit
    const certIdField = document.getElementById('f-cert-id');
    const regenBtn    = document.getElementById('btn-regen-id');
    certIdField.readOnly = !!data;
    if (regenBtn) regenBtn.style.display = data ? 'none' : '';

    // Auto-generate ID jika mode tambah
    if (!data) fetchNextCertId();

    document.getElementById('cert-modal').showModal();
}

function closeModal() {
    document.getElementById('cert-modal').close();
}

async function editCertificate(id) {
    const res  = await fetch(`../certificate-api.php?action=get&id=${id}`);
    const json = await res.json();
    if (json.success) openModal(json.data);
}

// ── Save (create / update) ─────────────────────────────────────────────────
async function saveCertificate(e) {
    e.preventDefault();
    const btn = document.getElementById('save-btn');
    if (btn) btn.disabled = true;

    try {
        const action   = editingId ? 'update' : 'create';
        const formEl   = document.getElementById('cert-form');
        const formData = new FormData(formEl);
        formData.set('action', action);
        
        // Pastikan ID ada jika update
        if (editingId) {
            formData.set('id', editingId);
        } else {
            formData.delete('id');
        }

        const res  = await fetch('../certificate-api.php', { method: 'POST', body: formData });
        const text = await res.text();
        let json;
        try {
            json = JSON.parse(text);
        } catch (err) {
            console.error('API response error:', text);
            // Tampilkan cuplikan pesan respon server jika bukan JSON
            const cleanErr = text.replace(/<[^>]*>?/gm, ' ').trim().substring(0, 120);
            throw new Error(cleanErr || 'Respon server tidak valid');
        }

        if (json.success) {
            closeModal();
            await rebuildCohortPills(); // refresh pills jika ada angkatan baru
            loadCertificates();
            showToast(json.message || 'Sertifikat berhasil disimpan', 'success');
        } else {
            showToast(json.message || 'Gagal menyimpan sertifikat', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast(err.message || 'Terjadi kesalahan saat memproses data', 'error');
    } finally {
        if (btn) btn.disabled = false;
    }
}

// ── Delete ─────────────────────────────────────────────────────────────────
function confirmDelete(id) {
    document.getElementById('del-modal').showModal();
    document.getElementById('confirm-del-btn').onclick = async () => {
        document.getElementById('del-modal').close();
        const fd = new FormData();
        fd.set('action', 'delete');
        fd.set('id', id);
        const res  = await fetch('../certificate-api.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
            loadCertificates();
            showToast(json.message, 'success');
        } else {
            showToast(json.message, 'error');
        }
    };
}


// ── Single Certificate Status Toggle ───────────────────────────────────────
async function toggleSingleCertStatus(id, newStatus) {
    try {
        const fd = new FormData();
        fd.set('action', 'toggle_single_status');
        fd.set('id', id);
        fd.set('status', newStatus);
        const res = await fetch('../certificate-api.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
            loadCertificates();
            showToast(json.message, newStatus === 'active' ? 'success' : 'error');
        } else {
            showToast(json.message || 'Gagal memperbarui status sertifikat', 'error');
        }
    } catch (e) {
        showToast('Terjadi kesalahan server', 'error');
    }
}

// ── Toast notification ─────────────────────────────────────────────────────
function showToast(msg, type = 'success') {
    const t = document.createElement('div');
    const bg = type === 'success' ? 'bg-[#166534] text-white' : 'bg-error text-on-error';
    const icon = type === 'success' ? 'check_circle' : 'error';
    t.className = `fixed bottom-6 right-6 z-[9999] flex items-center gap-2 px-4 py-3 rounded-xl shadow-xl text-sm font-semibold ${bg} transition-all`;
    t.innerHTML = `<span class="material-symbols-outlined text-[20px] filled-icon">${icon}</span>${msg}`;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 400); }, 3000);
}

// ── Init ───────────────────────────────────────────────────────────────────
// Jalankan rebuild pills dulu (sekaligus load awal), lalu load tabel
rebuildCohortPills().then(() => loadCertificates());
</script>
</body>
</html>
