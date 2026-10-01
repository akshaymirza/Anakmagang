<?php 
require_once __DIR__ . '/../session.php';
require_admin();
$userName = current_user_name();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Kedayweb Admin - Monitoring Riwayat Pekerjaan Intern</title>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800&amp;family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="../shared-config.js"></script>
    <script src="../project-store.js"></script>
    <script src="../intern-store.js"></script>
    <style>
        body { font-family: Inter, sans-serif; }
        .font-geist { font-family: Geist, sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 500; }
        .card-shadow { box-shadow: 0 4px 20px -2px rgba(30, 58, 138, 0.05); }
        .card-shadow:hover { box-shadow: 0 10px 25px -4px rgba(30, 58, 138, 0.1); transform: translateY(-2px); }

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
include '../partials/sidebar-admin.php';
?>

    <!-- Main Content Area -->
    <main class="flex-1 md:ml-[16.5rem] flex flex-col h-full bg-surface overflow-y-auto">
        <!-- Top Navigation Header -->
        <header class="w-full h-20 bg-surface-container-lowest border-b border-outline-variant sticky top-0 flex items-center justify-between px-gutter z-10 shrink-0 no-print">
            <div class="flex items-center gap-3 min-w-0 flex-1">
                <button onclick="toggleMobileSidebar()" class="md:hidden text-on-surface hover:text-primary focus:outline-none flex items-center shrink-0 p-1 rounded-lg hover:bg-surface-container-high" aria-label="Toggle Sidebar">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <span class="material-symbols-outlined text-primary text-2xl shrink-0" style="font-variation-settings: 'FILL' 1;">history_edu</span>
                <div class="min-w-0">
                    <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface truncate">Monitoring Pekerjaan Magang</h2>
                    <p class="text-xs text-on-surface-variant hidden sm:block truncate">Pantau seluruh pencapaian dan riwayat tugas Kanban anak magang</p>
                </div>
            </div>
            <div class="flex items-center gap-sm shrink-0">
                <button type="button" onclick="window.print()" class="px-3 py-1.5 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-container transition-colors shadow-xs flex items-center gap-1 cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]">print</span>
                    <span class="hidden sm:inline">Cetak PDF</span>
                </button>
                <div class="flex items-center gap-sm p-1.5 px-3 rounded-full border border-outline-variant bg-surface-bright shadow-2xs">
                    <div class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container overflow-hidden shrink-0">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">account_circle</span>
                    </div>
                    <span class="hidden sm:inline-block font-label-md"><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="../logout.php" class="text-error hover:text-red-700 hover:bg-red-50 p-1.5 rounded-full transition-colors flex items-center justify-center" title="Keluar" aria-label="Keluar"><span class="material-symbols-outlined text-[20px]">logout</span></a>
                </div>
            </div>
        </header>

        <!-- Print Header Only visible when printing (Page 1) -->
        <div class="hidden print-header p-6 border-b border-slate-300 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Kedayweb InternSpace - Laporan Pekerjaan Intern</h1>
                    <p class="text-xs text-slate-500">Tanggal Cetak: <?php echo date('d F Y, H:i'); ?> WIB</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold text-slate-700">Dicetak oleh: Admin (<?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?>)</p>
                </div>
            </div>
        </div>

        <div class="p-gutter max-w-7xl w-full mx-auto space-y-6 pb-12">
            
            <!-- PAGE 1: Summary Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Stat 1: Tasks Done -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-5 card-shadow transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tugas Selesai (Done)</span>
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
                        <span class="text-xs font-semibold text-slate-500">keseluruhan</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div id="stat-rate-bar" class="bg-indigo-600 h-full rounded-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Toolbar (Screen Only) -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-4 shadow-sm space-y-3 no-print">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                    <!-- Filter Assignee -->
                    <div>
                        <label for="filter-assignee" class="block text-xs font-bold text-slate-500 uppercase mb-1">Filter Anak Magang:</label>
                        <select id="filter-assignee" onchange="renderTaskHistory()" class="w-full bg-surface-bright border border-outline-variant rounded-xl px-3 py-2 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-primary focus:outline-none cursor-pointer">
                            <option value="ALL">-- Semua Anak Magang --</option>
                        </select>
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

                    <!-- Filter Project -->
                    <div>
                        <label for="filter-project" class="block text-xs font-bold text-slate-500 uppercase mb-1">Project:</label>
                        <select id="filter-project" onchange="renderTaskHistory()" class="w-full bg-surface-bright border border-outline-variant rounded-xl px-3 py-2 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-primary focus:outline-none cursor-pointer">
                            <option value="ALL">Semua Project</option>
                        </select>
                    </div>

                    <!-- Search Input -->
                    <div>
                        <label for="search-task" class="block text-xs font-bold text-slate-500 uppercase mb-1">Pencarian Tasks:</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                            <input id="search-task" oninput="renderTaskHistory()" type="text" placeholder="Cari judul / deskripsi..." class="w-full pl-9 pr-3 py-2 bg-surface-bright border border-outline-variant rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:outline-none">
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
                    <a href="../tasks.php" class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline no-print">
                        Kelola di Papan Kanban <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
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
                    <p class="text-xs text-slate-500 max-w-sm mx-auto mb-4">Belum ada tugas di papan Kanban yang sesuai dengan filter yang dipilih.</p>
                </div>
            </div>

        </div>
        <div class="mt-auto shrink-0 w-full no-print">
            <?php include '../partials/footer.php'; ?>
        </div>
    </main>

    <script>
        let registeredUsersList = [];
        let allTasksWithProject = [];

        const escapeHtml = str => String(str || '').replace(/[&<>'"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[c]));

        async function initPage() {
            try {
                const res = await fetch('../projects.php?action=users');
                if (res.ok) {
                    registeredUsersList = await res.json();
                }
            } catch (e) {
                console.warn('Gagal memuat list user:', e);
            }

            if (window.ProjectStore) {
                await ProjectStore.init();
            }

            loadAllTasks();
            populateDropdowns();
            renderTaskHistory();
        }

        function loadAllTasks() {
            if (!window.ProjectStore) return;
            const projects = ProjectStore.projects();
            allTasksWithProject = [];

            projects.forEach(p => {
                (p.tasks || []).forEach(t => {
                    allTasksWithProject.push({
                        ...t,
                        projectName: p.name || 'Untitled Project',
                        projectId: p.id
                    });
                });
            });
        }

        function populateDropdowns() {
            const assigneeSelect = document.getElementById('filter-assignee');
            const currentSelected = assigneeSelect ? assigneeSelect.value : 'ALL';
            const namesSet = new Set();
            const options = [];

            // 1. From database users (excluding superadmin)
            if (Array.isArray(registeredUsersList)) {
                registeredUsersList.forEach(u => {
                    if (u.username && !namesSet.has(u.username)) {
                        namesSet.add(u.username);
                        options.push({ value: u.username, label: u.username });
                    }
                });
            }

            // 2. From InternStore if available
            if (window.InternStore && typeof InternStore.list === 'function') {
                InternStore.list().forEach(i => {
                    if (i.name && !namesSet.has(i.name)) {
                        namesSet.add(i.name);
                        options.push({ value: i.name, label: i.name });
                    }
                });
            }

            // 3. Extract ALL assignees actually present on Kanban tasks
            allTasksWithProject.forEach(t => {
                const name = (t.assignee || '').trim();
                if (name && !namesSet.has(name)) {
                    namesSet.add(name);
                    options.push({ value: name, label: name });
                }
            });

            let assigneeHtml = '<option value="ALL">-- Semua Anak Magang --</option>';
            options.forEach(opt => {
                const isSelected = String(opt.value) === String(currentSelected) ? 'selected' : '';
                assigneeHtml += `<option value="${escapeHtml(opt.value)}" ${isSelected}>${escapeHtml(opt.label)}</option>`;
            });
            if (assigneeSelect) assigneeSelect.innerHTML = assigneeHtml;

            const projectSelect = document.getElementById('filter-project');
            const currentProjectSelected = projectSelect ? projectSelect.value : 'ALL';
            const projects = window.ProjectStore ? ProjectStore.projects() : [];
            let projectHtml = '<option value="ALL">Semua Project</option>';
            projects.forEach(p => {
                const isSelected = String(p.id) === String(currentProjectSelected) ? 'selected' : '';
                projectHtml += `<option value="${p.id}" ${isSelected}>${escapeHtml(p.name)}</option>`;
            });
            if (projectSelect) projectSelect.innerHTML = projectHtml;
        }

        function renderTaskHistory() {
            if (!window.ProjectStore) return;
            loadAllTasks();

            const selectedAssignee = document.getElementById('filter-assignee').value;
            const selectedStatus = document.getElementById('filter-status').value;
            const selectedProject = document.getElementById('filter-project').value;
            const searchQuery = (document.getElementById('search-task').value || '').toLowerCase().trim();

            const filteredTasks = allTasksWithProject.filter(t => {
                if (selectedAssignee !== 'ALL') {
                    if (String(t.assignee || '').toLowerCase() !== String(selectedAssignee).toLowerCase()) {
                        return false;
                    }
                }

                if (selectedStatus !== 'ALL') {
                    const normStatus = ProjectStore.normalizeStatus ? ProjectStore.normalizeStatus(t.status) : t.status;
                    if (normStatus !== selectedStatus) return false;
                }

                if (selectedProject !== 'ALL') {
                    if (String(t.projectId) !== String(selectedProject)) return false;
                }

                if (searchQuery) {
                    const titleMatch = (t.title || '').toLowerCase().includes(searchQuery);
                    const descMatch = (t.description || '').toLowerCase().includes(searchQuery);
                    if (!titleMatch && !descMatch) return false;
                }

                return true;
            });

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
                        <div class="flex items-center justify-between gap-2 mb-3">
                            ${statusBadge}
                            ${priorityBadge}
                        </div>

                        <!-- Project Name Badge -->
                        <div class="bg-blue-50/80 border border-blue-200 text-blue-900 px-3 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5 mb-3">
                            <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">folder</span>
                            <span>Project: <strong class="text-blue-950 font-black">${escapeHtml(task.projectName)}</strong></span>
                        </div>

                        <h4 class="font-bold text-slate-900 text-base mb-2 leading-snug">${escapeHtml(task.title)}</h4>

                        <p class="text-xs text-slate-600 mb-4 line-clamp-3 leading-relaxed">${escapeHtml(task.description || 'Tidak ada deskripsi rinci.')}</p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span class="flex items-center gap-1 font-medium text-slate-700">
                            <span class="material-symbols-outlined text-[15px] text-slate-400">person</span>
                            ${escapeHtml(task.assignee || 'Belum Ditugaskan')}
                        </span>
                        <a href="../tasks.php?project=${encodeURIComponent(task.projectId)}" class="text-xs font-bold text-primary hover:underline flex items-center gap-0.5 no-print">
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
