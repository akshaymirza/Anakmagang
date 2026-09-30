<?php require_once __DIR__ . '/../session.php'; require_admin(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <title>Kedayweb Admin - Statistics</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="../shared-config.js"></script>
    <script src="../project-store.js"></script>
    <script src="../intern-store.js"></script>
    <link rel="stylesheet" href="../style.css"/>
    <style>
        .glass-card { background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); }
        .bar {
            transition: height 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
    </style>
</head>
<body class="bg-background text-on-surface font-body-md flex h-screen overflow-hidden">
    <!-- Sidebar -->
<?php $active = 'stats'; include '../partials/sidebar-admin.php'; ?>

    <!-- Main -->
    <main class="flex-1 flex flex-col md:ml-[16.5rem] h-screen overflow-y-auto">
        <header class="w-full h-20 bg-surface-container-lowest border-b border-outline-variant sticky top-0 flex justify-between items-center px-6 z-10 shrink-0">
            <h2 class="font-headline-lg font-bold text-on-surface flex items-center gap-2">
                <button onclick="toggleMobileSidebar()" class="md:hidden text-on-surface hover:text-primary focus:outline-none flex items-center mr-1 p-1 rounded-lg hover:bg-surface-container-high" aria-label="Toggle Sidebar">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">insights</span>
                <span>Statistik Portal</span>
            </h2>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-sm p-1.5 px-3 rounded-full border border-outline-variant bg-surface-bright shadow-2xs">
                    <div class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container overflow-hidden shrink-0">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">account_circle</span>
                    </div>
                    <span class="hidden sm:inline-block font-label-md"><?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="../Login/logout.php" class="text-error hover:text-red-700 hover:bg-red-50 p-1.5 rounded-full transition-colors flex items-center justify-center" title="Keluar" aria-label="Keluar"><span class="material-symbols-outlined text-[20px]">logout</span></a>
                </div>
            </div>
        </header>

        <div class="p-6 flex flex-col gap-6 flex-1">
            <!-- Key Metrics -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="glass-card rounded-xl border border-outline-variant p-4">
                    <p class="text-xs text-on-surface-variant uppercase tracking-wider mb-1">Total Intern</p>
                    <p class="text-3xl font-bold text-primary" id="stat-total-intern">0</p>
                </div>
                <div class="glass-card rounded-xl border border-outline-variant p-4">
                    <p class="text-xs text-on-surface-variant uppercase tracking-wider mb-1">Total Projects</p>
                    <p class="text-3xl font-bold text-primary" id="stat-total-projects">0</p>
                </div>
                <div class="glass-card rounded-xl border border-outline-variant p-4">
                    <p class="text-xs text-on-surface-variant uppercase tracking-wider mb-1">Total Tasks</p>
                    <p class="text-3xl font-bold text-primary" id="stat-total-tasks">0</p>
                </div>
                <div class="glass-card rounded-xl border border-outline-variant p-4">
                    <p class="text-xs text-on-surface-variant uppercase tracking-wider mb-1">Completion Rate</p>
                    <p class="text-3xl font-bold text-green-600" id="stat-completion">0%</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Task Status Breakdown -->
                <div class="glass-card rounded-xl border border-outline-variant p-5">
                    <h3 class="font-headline-md mb-4">Status Tasks</h3>
                    <div class="space-y-3" id="task-status-list">
                        <!-- Diisi dinamis -->
                    </div>
                </div>

                <!-- Tasks per Project -->
                <div class="glass-card rounded-xl border border-outline-variant p-5">
                    <h3 class="font-headline-md mb-4">Tasks per Project</h3>
                    <div id="project-bars" class="flex items-end gap-3 h-40">
                        <!-- Bar chart dinamis -->
                    </div>
                    <div id="project-labels" class="flex gap-3 mt-2">
                    </div>
                </div>

                <!-- Task Priority Distribution -->
                <div class="glass-card rounded-xl border border-outline-variant p-5">
                    <h3 class="font-headline-md mb-4">Distribusi Prioritas</h3>
                    <div class="space-y-3" id="priority-list">
                        <!-- Diisi dinamis -->
                    </div>
                </div>

                <!-- Intern Performance -->
                <div class="glass-card rounded-xl border border-outline-variant p-5">
                    <h3 class="font-headline-md mb-4">Performa Intern</h3>
                    <div class="overflow-y-auto max-h-48 space-y-2" id="intern-perf-list">
                        <!-- Diisi dinamis -->
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-auto shrink-0 w-full">
            <?php include '../partials/footer.php'; ?>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const projects = window.ProjectStore ? ProjectStore.projects() : [];
            const interns  = window.InternStore  ? InternStore.all()       : [];

            // Key metrics
            let totalTasks = 0, doneTasks = 0;
            const statusCount = { todo: 0, progress: 0, review: 0, done: 0 };
            const priorityCount = { High: 0, Medium: 0, Low: 0 };
            const assigneeDone = {};

            projects.forEach(p => {
                (p.tasks || []).forEach(t => {
                    totalTasks++;
                    statusCount[t.status] = (statusCount[t.status] || 0) + 1;
                    priorityCount[t.priority] = (priorityCount[t.priority] || 0) + 1;
                    if (t.status === 'done') {
                        doneTasks++;
                        assigneeDone[t.assignee] = (assigneeDone[t.assignee] || 0) + 1;
                    }
                });
            });

            document.getElementById('stat-total-intern').textContent = interns.length || '3';
            document.getElementById('stat-total-projects').textContent = projects.length;
            document.getElementById('stat-total-tasks').textContent = totalTasks;
            const pct = totalTasks ? Math.round((doneTasks / totalTasks) * 100) : 0;
            document.getElementById('stat-completion').textContent = pct + '%';

            // Task Status
            const statusLabels = { todo: 'To Do', progress: 'In Progress', review: 'In Review', done: 'Done' };
            const statusColors = { todo: 'bg-slate-400', progress: 'bg-blue-500', review: 'bg-yellow-500', done: 'bg-green-500' };
            document.getElementById('task-status-list').innerHTML = Object.entries(statusCount).map(([k, v]) => `
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full ${statusColors[k]}"></div>
                    <span class="flex-1 text-sm">${statusLabels[k]}</span>
                    <div class="flex-[3] h-2 bg-outline-variant rounded-full overflow-hidden">
                        <div class="h-full ${statusColors[k]} rounded-full" style="width:${totalTasks ? Math.round(v/totalTasks*100) : 0}%"></div>
                    </div>
                    <span class="text-sm font-semibold w-8 text-right">${v}</span>
                </div>
            `).join('');

            // Bar chart per project
            const maxTasks = Math.max(...projects.map(p => (p.tasks||[]).length), 1);
            document.getElementById('project-bars').innerHTML = projects.slice(0, 6).map(p => {
                const t = (p.tasks||[]).length;
                const h = Math.round((t / maxTasks) * 100);
                return `<div class="flex flex-col items-center gap-1 flex-1">
                    <span class="text-xs font-bold text-primary">${t}</span>
                    <div class="w-full bar rounded-t-md bg-primary" style="height:${h}%"></div>
                </div>`;
            }).join('');
            document.getElementById('project-labels').innerHTML = projects.slice(0, 6).map(p =>
                `<div class="flex-1 text-center text-xs text-on-surface-variant truncate">${(p.name||'').split(' ')[0]}</div>`
            ).join('');

            // Priority
            const prioColors = { High: 'bg-red-500', Medium: 'bg-yellow-500', Low: 'bg-green-500' };
            document.getElementById('priority-list').innerHTML = Object.entries(priorityCount).map(([k, v]) => `
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full ${prioColors[k]}"></div>
                    <span class="flex-1 text-sm">${k}</span>
                    <div class="flex-[3] h-2 bg-outline-variant rounded-full overflow-hidden">
                        <div class="h-full ${prioColors[k]} rounded-full" style="width:${totalTasks ? Math.round(v/totalTasks*100) : 0}%"></div>
                    </div>
                    <span class="text-sm font-semibold w-8 text-right">${v}</span>
                </div>
            `).join('');

            // Intern performance
            const sorted = Object.entries(assigneeDone).sort((a,b) => b[1]-a[1]);
            document.getElementById('intern-perf-list').innerHTML = sorted.length
                ? sorted.map(([name, count]) => `
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full bg-primary-container text-primary flex items-center justify-center text-xs font-bold">${(name||'?')[0]}</div>
                        <span class="flex-1 text-sm">${name}</span>
                        <span class="text-sm font-bold text-green-600">${count} done</span>
                    </div>
                  `).join('')
                : '<p class="text-sm text-on-surface-variant">Belum ada data performa.</p>';
        });
    </script>
</body>
</html>
