<?php 
require_once __DIR__ . '/session.php'; 
require_login(); 
$userName = current_user_name();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Kedayweb Dashboard - Portal PKL</title>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&amp;family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="shared-config.js"></script>
    <script src="project-store.js"></script>
    <script src="intern-store.js"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: Inter, sans-serif; }
        .font-geist { font-family: Geist, sans-serif; }
        .glass-card { background: rgba(255, 255, 255, 0.95); }
    </style>
</head>
<body class="bg-background text-on-surface font-body-md flex h-screen overflow-hidden">
<!-- SideNavBar (Desktop) -->
<?php $active = 'dashboard'; include 'partials/sidebar-intern.php'; ?>

<!-- Main Content Canvas -->
<main class="flex-1 flex flex-col md:ml-[16.5rem] h-screen overflow-y-auto relative">
    <header class="w-full h-20 bg-surface-container-lowest border-b border-outline-variant sticky top-0 z-10 shrink-0">
        <div class="flex justify-between items-center px-gutter w-full max-w-container-max mx-auto h-full gap-2">
            <button onclick="toggleMobileSidebar()" type="button" class="md:hidden p-2 text-on-surface hover:bg-surface-container-high rounded-lg shrink-0" aria-label="Buka Menu Sidebar">
                <span class="material-symbols-outlined text-2xl">menu</span>
            </button>
            <div class="flex-1 flex items-center">
                <div class="relative w-full max-w-md">
                    <span class="material-symbols-outlined absolute left-sm top-1/2 -translate-y-1/2 text-outline">search</span>
                    <input id="dash-search-input" oninput="filterDashboardSearch(this.value)" class="w-full pl-xl pr-md py-sm rounded-lg bg-surface-bright border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary-fixed focus:outline-none font-body-sm text-body-sm transition-all" placeholder="Cari tugas, project..." type="text"/>
                </div>
            </div>
            <!-- Trailing Actions -->
            <div class="flex items-center gap-sm">
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
        </div>
    </header>

    <!-- Dashboard Content -->
    <div class="w-full max-w-container-max mx-auto p-md md:p-gutter flex flex-col gap-xl flex-1">
        
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-surface font-bold">
                    Selamat Datang, <span id="intern-name"><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></span>! 👋
                </h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-xs">Berikut ringkasan aktivitas project dan tugas Kanban Anda hari ini.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="tasks.php" class="px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl hover:bg-primary-container transition-all flex items-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">view_kanban</span> Papan Kanban
                </a>
                <a href="internspace.php" class="px-4 py-2 bg-surface-container-high border border-outline-variant text-primary text-xs font-bold rounded-xl hover:bg-white transition-all flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">history_edu</span> Riwayat Tugas
                </a>
            </div>
        </div>

        <!-- Functional Top Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-md">
            <!-- Stat 1: Tasks Done -->
            <a href="internspace.php" class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-md flex flex-col justify-between h-36 block hover:shadow-md transition-all">
                <div class="flex justify-between items-start">
                    <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider font-bold">Tugas Selesai</span>
                    <div class="p-2 rounded-xl bg-green-100 text-green-700">
                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">task_alt</span>
                    </div>
                </div>
                <div>
                    <div class="font-headline-xl text-headline-xl text-slate-900 font-bold font-geist" id="stat-tasks-done-val">0</div>
                    <p class="font-body-sm text-body-sm text-green-600 font-semibold mt-1 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">check_circle</span> Tugas Kanban Selesai
                    </p>
                </div>
            </a>

            <!-- Stat 2: Tasks In Progress -->
            <a href="tasks.php" class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-md flex flex-col justify-between h-36 block hover:shadow-md transition-all">
                <div class="flex justify-between items-start">
                    <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider font-bold">Sedang Dikerjakan</span>
                    <div class="p-2 rounded-xl bg-blue-100 text-blue-700">
                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">sync</span>
                    </div>
                </div>
                <div>
                    <div class="font-headline-xl text-headline-xl text-slate-900 font-bold font-geist" id="stat-tasks-progress-val">0</div>
                    <p class="font-body-sm text-body-sm text-blue-600 font-semibold mt-1 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">pending</span> In Progress & Review
                    </p>
                </div>
            </a>

            <!-- Stat 3: Total Projects -->
            <a href="projects.php" class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-md flex flex-col justify-between h-36 block hover:shadow-md transition-all">
                <div class="flex justify-between items-start">
                    <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider font-bold">Total Proyek</span>
                    <div class="p-2 rounded-xl bg-indigo-100 text-indigo-700">
                        <span class="material-symbols-outlined text-[20px]">folder_open</span>
                    </div>
                </div>
                <div>
                    <div class="font-headline-xl text-headline-xl text-slate-900 font-bold font-geist" id="stat-total-proj-val">0</div>
                    <p class="font-body-sm text-body-sm text-indigo-600 font-semibold mt-1">Proyek aktif di portal</p>
                </div>
            </a>

            <!-- Stat 4: Completed Projects -->
            <a href="projects.php" class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-md flex flex-col justify-between h-36 block hover:shadow-md transition-all">
                <div class="flex justify-between items-start">
                    <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider font-bold">Proyek Selesai</span>
                    <div class="p-2 rounded-xl bg-purple-100 text-purple-700">
                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">verified</span>
                    </div>
                </div>
                <div>
                    <div class="font-headline-xl text-headline-xl text-slate-900 font-bold font-geist" id="stat-completed-proj-val">0</div>
                    <p class="font-body-sm text-body-sm text-purple-600 font-semibold mt-1">Semua task rampung</p>
                </div>
            </a>
        </div>

        <!-- Main Dynamic Content (Active Projects & Recent Tasks Grid) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Active Projects Section (7 cols) -->
            <div class="lg:col-span-7 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]" style="font-variation-settings: 'FILL' 1;">folder</span>
                        Daftar Proyek Aktif
                    </h3>
                    <a href="projects.php" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                        Kelola Proyek <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                </div>
                <div id="dash-projects-list" class="space-y-3">
                    <!-- Loaded dynamically via JavaScript -->
                </div>
            </div>

            <!-- Recent Tasks Section (5 cols) -->
            <div class="lg:col-span-5 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">view_kanban</span>
                        Tugas Terkini Anda
                    </h3>
                    <a href="tasks.php" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                        Buka Kanban <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                </div>
                <div id="dash-tasks-list" class="space-y-3">
                    <!-- Loaded dynamically via JavaScript -->
                </div>
            </div>

        </div>

        <div class="h-md"></div>
    </div>
    <div class="mt-auto shrink-0 w-full">
        <?php include 'partials/footer.php'; ?>
    </div>
</main>

<script>
    const CURRENT_USERNAME = <?php echo json_encode($userName); ?>;
    const params = new URLSearchParams(window.location.search);
    const internParam = params.get('intern');

    const escapeHtml = str => String(str || '').replace(/[&<>'"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[c]));

    document.addEventListener('DOMContentLoaded', async () => {
        if (internParam) {
            const nameElem = document.getElementById('intern-name');
            if (nameElem) nameElem.textContent = decodeURIComponent(internParam);
        }

        if (window.ProjectStore) {
            await ProjectStore.init();
            renderDashboardData();
        }
    });

    function renderDashboardData(filterQuery = '') {
        if (!window.ProjectStore) return;

        const myUsernameLower = String(CURRENT_USERNAME || '').toLowerCase().trim();
        const selectedIntern = internParam ? decodeURIComponent(internParam).toLowerCase().trim() : myUsernameLower;
        let projects = ProjectStore.projects() || [];

        let completedTasksCount = 0;
        let progressTasksCount = 0;
        let completedProjectsCount = 0;
        let allUserTasks = [];

        projects.forEach(p => {
            const tasks = p.tasks || [];
            let pDoneCount = 0;

            tasks.forEach(t => {
                const assigneeLower = String(t.assignee || '').toLowerCase().trim();
                const normStatus = ProjectStore.normalizeStatus ? ProjectStore.normalizeStatus(t.status) : t.status;

                // Match assigned tasks or all if none assigned yet
                const matchesUser = !selectedIntern || assigneeLower === selectedIntern || tasks.filter(x => String(x.assignee || '').toLowerCase().trim() === selectedIntern).length === 0;

                if (matchesUser) {
                    if (normStatus === 'done') {
                        completedTasksCount++;
                        pDoneCount++;
                    } else if (normStatus === 'inprogress' || normStatus === 'underreview') {
                        progressTasksCount++;
                    }

                    allUserTasks.push({
                        ...t,
                        projectName: p.name || 'Untitled Project',
                        projectId: p.id
                    });
                }
            });

            if (tasks.length > 0 && tasks.every(t => (ProjectStore.normalizeStatus ? ProjectStore.normalizeStatus(t.status) : t.status) === 'done')) {
                completedProjectsCount++;
            }
        });

        // Filter search if query provided
        const q = (filterQuery || '').toLowerCase().trim();
        let displayProjects = projects;
        let displayTasks = allUserTasks;

        if (q) {
            displayProjects = projects.filter(p => (p.name || '').toLowerCase().includes(q) || (p.description || '').toLowerCase().includes(q));
            displayTasks = allUserTasks.filter(t => (t.title || '').toLowerCase().includes(q) || (t.description || '').toLowerCase().includes(q));
        }

        // Update Stat Cards
        document.getElementById('stat-tasks-done-val').textContent = completedTasksCount;
        document.getElementById('stat-tasks-progress-val').textContent = progressTasksCount;
        document.getElementById('stat-total-proj-val').textContent = projects.length;
        document.getElementById('stat-completed-proj-val').textContent = completedProjectsCount;

        // Render Active Projects List
        const projectsContainer = document.getElementById('dash-projects-list');
        if (projectsContainer) {
            if (displayProjects.length === 0) {
                projectsContainer.innerHTML = `
                    <div class="bg-surface-container-lowest border border-dashed border-outline-variant rounded-2xl p-6 text-center text-xs text-slate-500">
                        Tidak ada proyek yang sesuai dengan pencarian.
                    </div>
                `;
            } else {
                projectsContainer.innerHTML = displayProjects.map(p => {
                    const tasks = p.tasks || [];
                    const doneCount = tasks.filter(t => (ProjectStore.normalizeStatus ? ProjectStore.normalizeStatus(t.status) : t.status) === 'done').length;
                    const percent = tasks.length > 0 ? Math.round((doneCount / tasks.length) * 100) : 0;

                    return `
                        <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-4 shadow-xs hover:border-primary transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary text-[20px]" style="font-variation-settings: 'FILL' 1;">folder</span>
                                    <h4 class="font-bold text-slate-900 text-sm">${escapeHtml(p.name)}</h4>
                                </div>
                                <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full">${tasks.length} tasks</span>
                            </div>
                            <p class="text-xs text-slate-500 mb-3 line-clamp-2">${escapeHtml(p.description || 'Proyek aktif Kanban')}</p>
                            <div class="space-y-1">
                                <div class="flex justify-between text-[11px] font-semibold text-slate-600">
                                    <span>Progress Selesai</span>
                                    <span>${percent}% (${doneCount}/${tasks.length})</span>
                                </div>
                                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                    <div class="bg-primary h-full rounded-full transition-all duration-500" style="width: ${percent}%"></div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        // Render Recent Tasks List
        const tasksContainer = document.getElementById('dash-tasks-list');
        if (tasksContainer) {
            if (displayTasks.length === 0) {
                tasksContainer.innerHTML = `
                    <div class="bg-surface-container-lowest border border-dashed border-outline-variant rounded-2xl p-6 text-center text-xs text-slate-500">
                        Tidak ada tugas yang sesuai.
                    </div>
                `;
            } else {
                const recentTasks = displayTasks.slice(0, 5);
                tasksContainer.innerHTML = recentTasks.map(t => {
                    const normStatus = ProjectStore.normalizeStatus ? ProjectStore.normalizeStatus(t.status) : t.status;
                    let statusBadge = '';
                    if (normStatus === 'done') {
                        statusBadge = `<span class="px-2 py-0.5 bg-green-100 text-green-800 text-[11px] font-bold rounded-full">Selesai</span>`;
                    } else if (normStatus === 'underreview') {
                        statusBadge = `<span class="px-2 py-0.5 bg-purple-100 text-purple-800 text-[11px] font-bold rounded-full">Review</span>`;
                    } else if (normStatus === 'inprogress') {
                        statusBadge = `<span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-[11px] font-bold rounded-full">In Progress</span>`;
                    } else {
                        statusBadge = `<span class="px-2 py-0.5 bg-slate-100 text-slate-700 text-[11px] font-bold rounded-full">To Do</span>`;
                    }

                    return `
                        <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-4 shadow-xs hover:border-primary transition-colors flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-[11px] font-bold text-primary flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[13px]">folder</span> ${escapeHtml(t.projectName)}
                                    </span>
                                    ${statusBadge}
                                </div>
                                <h5 class="font-bold text-slate-900 text-sm mb-1">${escapeHtml(t.title)}</h5>
                                <p class="text-xs text-slate-500 line-clamp-2 mb-2">${escapeHtml(t.description || 'Tidak ada deskripsi.')}</p>
                            </div>
                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                                <span>Assignee: <strong>${escapeHtml(t.assignee || CURRENT_USERNAME)}</strong></span>
                                <a href="tasks.php?project=${encodeURIComponent(t.projectId)}" class="font-bold text-primary hover:underline flex items-center gap-0.5">
                                    Buka <span class="material-symbols-outlined text-[12px]">open_in_new</span>
                                </a>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }
    }

    function filterDashboardSearch(query) {
        renderDashboardData(query);
    }
</script>
</body>
</html>