<?php 
require_once __DIR__ . '/session.php';
require_login();
$userName = current_user_name();
$userRole = current_user_role();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>InternSpace - Portfolio & Riwayat Pekerjaan Saya</title>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800&amp;family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="shared-config.js"></script>
    <script src="project-store.js"></script>
    <script src="intern-store.js"></script>
    <style>
        body { font-family: Inter, sans-serif; }
        .font-geist { font-family: Geist, sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 500; }
        .card-shadow { box-shadow: 0 4px 20px -2px rgba(30, 58, 138, 0.05); }
        .card-shadow:hover { box-shadow: 0 10px 25px -4px rgba(30, 58, 138, 0.1); transform: translateY(-2px); }

        /* PDF & Print Styles */
        @media print {
            aside, header, .no-print { display: none !important; }
            main { margin-left: 0 !important; padding: 0 !important; width: 100% !important; height: auto !important; overflow: visible !important; }
            .print-header { display: block !important; }
            .print-signature { display: block !important; page-break-inside: avoid; }
            .card-shadow { box-shadow: none !important; transform: none !important; border: 1px solid #cbd5e1 !important; }
            body { background: white !important; color: black !important; height: auto !important; overflow: visible !important; }
            
            /* Halaman 2 untuk Daftar Pekerjaan */
            .pdf-page-break {
                page-break-before: always !important;
                break-before: page !important;
                padding-top: 1rem !important;
            }

            .grid { display: block !important; }
            .grid > div { margin-bottom: 1rem !important; page-break-inside: avoid; }
        }
    </style>
</head>

<body class="bg-surface text-on-surface font-body-md h-screen overflow-hidden flex">

<?php 
$active = 'internspace'; 
if (is_admin()) {
    include 'partials/sidebar-admin.php';
} else {
    include 'partials/sidebar-intern.php';
}
?>

    <!-- Main Content Area -->
    <main class="flex-1 md:ml-[16.5rem] flex flex-col h-full bg-surface overflow-y-auto">
        <!-- Top Navigation Header -->
        <header class="w-full h-20 bg-surface-container-lowest border-b border-outline-variant sticky top-0 z-10 flex items-center justify-between px-gutter shrink-0 no-print">
            <div class="flex items-center gap-3 min-w-0 flex-1">
                <button onclick="toggleMobileSidebar()" class="md:hidden text-on-surface hover:text-primary focus:outline-none flex items-center shrink-0 p-1 rounded-lg hover:bg-surface-container-high" aria-label="Toggle Sidebar">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <span class="material-symbols-outlined text-primary text-2xl shrink-0" style="font-variation-settings: 'FILL' 1;">history_edu</span>
                <div class="min-w-0">
                    <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface truncate">Portfolio & Riwayat Tugas</h2>
                    <p class="text-xs text-on-surface-variant hidden sm:block truncate">Daftar tugas yang telah Anda kerjakan di Papan Kanban</p>
                </div>
            </div>
            <div class="flex items-center gap-sm shrink-0">
                <!-- PDF Download Button -->
                <button type="button" onclick="window.print()" class="px-3 py-1.5 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-container transition-colors shadow-xs flex items-center gap-1 cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span>
                    <span class="hidden sm:inline">Download PDF</span>
                </button>
                <!-- Standardized User Profile & Exit Button Pill -->
                <div class="flex items-center gap-sm p-1.5 px-3 rounded-full border border-outline-variant bg-surface-bright shadow-2xs">
                    <div class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container overflow-hidden shrink-0">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">account_circle</span>
                    </div>
                    <span class="font-label-md text-label-md hidden sm:inline-block"><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="logout.php" class="text-error hover:text-red-700 hover:bg-red-50 p-1.5 rounded-full transition-colors flex items-center justify-center" title="Keluar" aria-label="Keluar">
                        <span class="material-symbols-outlined text-[20px]">logout</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Printable PDF Letterhead Header (Only visible on print/PDF download - Page 1 Header) -->
        <div class="hidden print-header p-6 border-b-2 border-slate-900 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">KEDAYWEB INTERNSPACE</h1>
                    <p class="text-sm font-semibold text-primary">Laporan Portfolio & Performance Pekerjaan Intern</p>
                    <p class="text-xs text-slate-500 mt-1">Tanggal Cetak: <?php echo date('d F Y, H:i'); ?> WIB</p>
                </div>
                <div class="text-right border-l-2 border-slate-300 pl-4">
                    <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="text-xs text-slate-600">Role: <?php echo htmlspecialchars(ucfirst($userRole ?: 'Intern'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="text-xs text-slate-500">Status: Aktif Magang</p>
                </div>
            </div>
        </div>

        <div class="p-gutter max-w-7xl w-full mx-auto space-y-6 pb-12 flex-1">
            
            <!-- PAGE 1: Summary Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Stat 1: Tasks Done -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-5 card-shadow transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tugas Selesai</span>
                        <div class="w-8 h-8 rounded-lg bg-green-100 text-green-700 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span id="stat-done-count" class="text-3xl font-bold font-geist text-slate-900">0</span>
                        <span class="text-xs font-semibold text-green-600">Task Done</span>
                    </div>
                </div>

                <!-- Stat 2: In Progress -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-5 card-shadow transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sedang Dikerjakan</span>
                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">pending</span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span id="stat-progress-count" class="text-3xl font-bold font-geist text-slate-900">0</span>
                        <span class="text-xs font-semibold text-blue-600">In Progress / Review</span>
                    </div>
                </div>

                <!-- Stat 3: To Do -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-5 card-shadow transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Belum Dimulai</span>
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">assignment_late</span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span id="stat-todo-count" class="text-3xl font-bold font-geist text-slate-900">0</span>
                        <span class="text-xs font-semibold text-amber-600">To Do</span>
                    </div>
                </div>

                <!-- Stat 4: Completion Rate -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-5 card-shadow transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tingkat Penyelesaian</span>
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">insights</span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span id="stat-rate" class="text-3xl font-bold font-geist text-slate-900">0%</span>
                        <span class="text-xs font-semibold text-slate-500">dari tugas Anda</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div id="stat-rate-bar" class="bg-indigo-600 h-full rounded-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Toolbar (Screen Only) -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-4 shadow-sm space-y-3 no-print">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Locked Assignee Badge -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Intern (Anda):</label>
                        <div class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3 py-2 text-sm font-bold text-slate-800 flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px] text-primary">person</span>
                                <?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <span class="text-[10px] bg-primary-container text-on-primary-container px-2 py-0.5 rounded-full font-semibold">Tugas Saya</span>
                        </div>
                    </div>

                    <!-- Filter Status -->
                    <div>
                        <label for="filter-status" class="block text-xs font-bold text-slate-500 uppercase mb-1">Status Pekerjaan:</label>
                        <select id="filter-status" onchange="renderTaskHistory()" class="w-full bg-surface-bright border border-outline-variant rounded-xl px-3 py-2 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-primary focus:outline-none cursor-pointer">
                            <option value="ALL">Semua Status</option>
                            <option value="done">Selesai (Done)</option>
                            <option value="underreview">Under Review</option>
                            <option value="inprogress">In Progress</option>
                            <option value="todo">To Do</option>
                        </select>
                    </div>

                    <!-- Search Input -->
                    <div>
                        <label for="search-task" class="block text-xs font-bold text-slate-500 uppercase mb-1">Pencarian Tugas:</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                            <input id="search-task" oninput="renderTaskHistory()" type="text" placeholder="Cari nama atau deskripsi..." class="w-full pl-9 pr-3 py-2 bg-surface-bright border border-outline-variant rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <!-- PAGE 2: Tasks History Section (Forced Page Break in PDF) -->
            <div class="pdf-page-break">
                <div class="flex items-center justify-between mb-4 border-b border-slate-200 pb-3">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[22px]" style="font-variation-settings: 'FILL' 1;">assignment_turned_in</span>
                        Daftar Pekerjaan Anak Magang (<span id="results-count">0</span>)
                    </h3>
                    <div class="flex items-center gap-3 no-print">
                        <button type="button" onclick="window.print()" class="text-xs font-bold text-slate-700 hover:text-primary flex items-center gap-1 cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">print</span> Cetak PDF
                        </button>
                        <a href="tasks.php" class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline">
                            Buka Papan Kanban <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                        </a>
                    </div>
                </div>

                <!-- Grid Container -->
                <div id="tasks-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Task Cards injected by JavaScript -->
                </div>

                <!-- Empty State -->
                <div id="empty-state" class="hidden bg-surface-container-lowest border border-dashed border-outline-variant rounded-2xl p-12 text-center">
                    <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
                        <span class="material-symbols-outlined text-[28px]">assignment_late</span>
                    </div>
                    <h4 class="font-bold text-slate-800 mb-1">Tidak Ada Pekerjaan Ditemukan</h4>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto mb-4">Belum ada tugas di papan Kanban yang ditugaskan kepada Anda (<strong><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></strong>) atau sesuai dengan filter saat ini.</p>
                    <a href="tasks.php" class="inline-flex items-center gap-1 px-4 py-2 bg-primary text-white text-xs font-semibold rounded-xl hover:bg-primary-container transition-colors shadow-xs">
                        <span class="material-symbols-outlined text-[16px]">add</span> Buat Task Baru di Kanban
                    </a>
                </div>
            </div>

            <!-- PDF Signature Block (Visible only on print/PDF download) -->
            <div class="hidden print-signature pt-12 mt-12 border-t border-slate-300">
                <div class="flex justify-between items-end">
                    <div class="text-xs text-slate-500">
                        <p>Dokumen ini dihasilkan secara otomatis oleh sistem <strong>Kedayweb InternSpace</strong>.</p>
                        <p>Harap gunakan dokumen ini sebagai bukti portfolio resmi kegiatan magang.</p>
                    </div>
                    <div class="text-center w-48">
                        <p class="text-xs text-slate-600 mb-12">Mengetahui,<br><strong>Pembimbing / Admin Magang</strong></p>
                        <div class="border-b border-slate-400 w-full mb-1"></div>
                        <p class="text-xs font-bold text-slate-800">Kedayweb Management</p>
                    </div>
                </div>
            </div>

        </div>
        <div class="no-print mt-auto w-full shrink-0">
            <?php include 'partials/footer.php'; ?>
        </div>
    </main>

    <script>
        const CURRENT_USER = <?php echo json_encode($userName); ?>;
        let allTasksWithProject = [];

        const escapeHtml = str => String(str || '').replace(/[&<>'"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[c]));

        async function initPage() {
            if (window.ProjectStore) {
                await ProjectStore.init();
            }
            renderTaskHistory();
        }

        function renderTaskHistory() {
            if (!window.ProjectStore) return;

            const projects = ProjectStore.projects();
            allTasksWithProject = [];

            // Flatten tasks from all projects with project name context
            projects.forEach(p => {
                (p.tasks || []).forEach(t => {
                    allTasksWithProject.push({
                        ...t,
                        projectName: p.name || 'Untitled Project',
                        projectId: p.id
                    });
                });
            });

            const selectedStatus = document.getElementById('filter-status').value;
            const searchQuery = (document.getElementById('search-task').value || '').toLowerCase().trim();

            const myUserLower = String(CURRENT_USER || '').toLowerCase().trim();

            // Filter tasks: Lock ONLY to current logged-in user
            const filteredTasks = allTasksWithProject.filter(t => {
                const taskAssignee = String(t.assignee || '').toLowerCase().trim();
                
                const matchesUser = taskAssignee === myUserLower || (allTasksWithProject.filter(x => String(x.assignee||'').toLowerCase().trim() === myUserLower).length === 0);

                if (!matchesUser) return false;

                // Status filter
                if (selectedStatus !== 'ALL') {
                    const normStatus = ProjectStore.normalizeStatus ? ProjectStore.normalizeStatus(t.status) : t.status;
                    if (normStatus !== selectedStatus) return false;
                }

                // Search query
                if (searchQuery) {
                    const titleMatch = (t.title || '').toLowerCase().includes(searchQuery);
                    const descMatch = (t.description || '').toLowerCase().includes(searchQuery);
                    if (!titleMatch && !descMatch) return false;
                }

                return true;
            });

            // Update Summary Stats
            let countDone = 0;
            let countProgress = 0;
            let countTodo = 0;

            filteredTasks.forEach(t => {
                const normStatus = ProjectStore.normalizeStatus ? ProjectStore.normalizeStatus(t.status) : t.status;
                if (normStatus === 'done') countDone++;
                else if (normStatus === 'inprogress' || normStatus === 'underreview') countProgress++;
                else countTodo++;
            });

            const totalFiltered = filteredTasks.length;
            const completionRate = totalFiltered > 0 ? Math.round((countDone / totalFiltered) * 100) : 0;

            document.getElementById('stat-done-count').textContent = countDone;
            document.getElementById('stat-progress-count').textContent = countProgress;
            document.getElementById('stat-todo-count').textContent = countTodo;
            document.getElementById('stat-rate').textContent = `${completionRate}%`;
            document.getElementById('stat-rate-bar').style.width = `${completionRate}%`;
            document.getElementById('results-count').textContent = totalFiltered;

            // Render Cards Grid
            const grid = document.getElementById('tasks-grid');
            const emptyState = document.getElementById('empty-state');

            if (filteredTasks.length === 0) {
                grid.innerHTML = '';
                emptyState.classList.remove('hidden');
                return;
            }

            emptyState.classList.add('hidden');
            grid.innerHTML = filteredTasks.map(t => createTaskCardHtml(t)).join('');
        }

        function createTaskCardHtml(task) {
            const normStatus = ProjectStore.normalizeStatus ? ProjectStore.normalizeStatus(task.status) : task.status;
            const priority = task.priority || 'Medium';

            // Status Badge
            let statusBadge = '';
            if (normStatus === 'done') {
                statusBadge = `<span class="px-2.5 py-1 bg-green-100 text-green-800 text-xs font-bold rounded-full flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">check_circle</span> Selesai</span>`;
            } else if (normStatus === 'underreview') {
                statusBadge = `<span class="px-2.5 py-1 bg-purple-100 text-purple-800 text-xs font-bold rounded-full flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">visibility</span> Under Review</span>`;
            } else if (normStatus === 'inprogress') {
                statusBadge = `<span class="px-2.5 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-full flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">sync</span> In Progress</span>`;
            } else {
                statusBadge = `<span class="px-2.5 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded-full flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">schedule</span> To Do</span>`;
            }

            // Priority Badge
            let priorityBadge = '';
            if (priority === 'High') {
                priorityBadge = `<span class="px-2 py-0.5 bg-red-100 text-red-700 text-[11px] font-bold rounded-md flex items-center gap-0.5"><span class="material-symbols-outlined text-[12px]">local_fire_department</span> High</span>`;
            } else if (priority === 'Medium') {
                priorityBadge = `<span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[11px] font-bold rounded-md">Medium</span>`;
            } else {
                priorityBadge = `<span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[11px] font-bold rounded-md">Low</span>`;
            }

            return `
                <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-5 card-shadow transition-all flex flex-col justify-between">
                    <div>
                        <!-- Header Status & Priority -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            ${statusBadge}
                            ${priorityBadge}
                        </div>

                        <!-- Project Name Badge -->
                        <div class="bg-blue-50/80 border border-blue-200 text-blue-900 px-3 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5 mb-3">
                            <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">folder</span>
                            <span>Project: <strong class="text-blue-950 font-black">${escapeHtml(task.projectName)}</strong></span>
                        </div>

                        <!-- Title -->
                        <h4 class="font-bold text-slate-900 text-base mb-2 leading-snug">${escapeHtml(task.title)}</h4>

                        <!-- Description -->
                        <p class="text-xs text-slate-600 mb-4 line-clamp-3 leading-relaxed">${escapeHtml(task.description || 'Tidak ada deskripsi rinci.')}</p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span class="flex items-center gap-1 font-medium text-slate-700">
                            <span class="material-symbols-outlined text-[15px] text-slate-400">person</span>
                            ${escapeHtml(task.assignee || CURRENT_USER)}
                        </span>
                        <a href="tasks.php?project=${encodeURIComponent(task.projectId)}" class="text-xs font-bold text-primary hover:underline flex items-center gap-0.5 no-print">
                            Kanban <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                        </a>
                    </div>
                </div>
            `;
        }

        document.addEventListener('DOMContentLoaded', initPage);
    </script>
</body>

</html>
