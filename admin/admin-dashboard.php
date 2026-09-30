<?php 
require_once __DIR__ . '/../session.php'; 
require_admin(); 
require_once __DIR__ . '/../Login/koneksi.php';

// Ambil data user intern dari database MySQL db_internspace
$db_interns = [];
$db_query = mysqli_query($conn, "SELECT id, username, role FROM users WHERE role = 'intern' ORDER BY id ASC");
if ($db_query) {
    while ($row = mysqli_fetch_assoc($db_query)) {
        $db_interns[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <title>Kedayweb Admin Dashboard</title>
    <!-- Inject DB Interns to JS -->
    <script>
        window.DB_INTERNS = <?php echo json_encode($db_interns, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    </script>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="../shared-config.js"></script>
    <script src="../project-store.js"></script>
    <script src="../intern-store.js"></script>
    <link rel="stylesheet" href="../style.css"/>
    <style>
        .glass-card { background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); }
    </style>
</head>
<body class="bg-background text-on-surface font-body-md flex h-screen overflow-hidden">
    <!-- Sidebar (desktop) -->
<?php
$active = 'dashboard';

$sidebar_subtitle = 'Admin Panel';
include '../partials/sidebar-admin.php';
?>
    <!-- Main Content -->
    <main class="flex-1 flex flex-col md:ml-[16.5rem] h-screen overflow-y-auto">
        <header class="w-full h-20 bg-surface-container-lowest border-b border-outline-variant sticky top-0 z-10 flex justify-between items-center px-6 gap-2 shrink-0">
            <div class="flex items-center gap-2">
                <button onclick="toggleMobileSidebar()" type="button" class="md:hidden p-2 text-on-surface hover:bg-surface-container-high rounded-lg shrink-0" aria-label="Buka Menu Sidebar">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <h2 class="font-headline-lg font-bold text-on-surface">Admin Dashboard</h2>
            </div>
            <div class="flex items-center gap-3">
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
        <section class="p-5 md:p-6 grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 flex-1">

            <!-- Card: Memilih & Melihat Dashboard Intern (Admin Feature) -->
            <div class="glass-card p-5 rounded-xl border border-outline-variant lg:col-span-2 xl:col-span-3">
                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 mb-4 pb-3 border-b border-outline-variant">
                    <div>
                        <h3 class="font-headline-md font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">badge</span>
                            <span>Dashboard Intern (Kedayweb)</span>
                        </h3>
                        <p class="text-sm text-on-surface-variant">Pilih intern di bawah ini untuk melihat pratinjau dashboard personal & statistik mereka secara langsung.</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4" id="admin-intern-cards-list">
                    <?php if (empty($db_interns)): ?>
                        <!-- Fallback jika belum ada intern di DB MySQL, render via JS default -->
                    <?php else: ?>
                        <?php foreach ($db_interns as $intern): ?>
                            <div class="p-4 rounded-xl border border-outline-variant bg-surface-container-low flex flex-col justify-between hover:shadow-md transition-all">
                                <div>
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold text-lg">
                                            <?php echo strtoupper(substr($intern['username'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-on-surface text-base line-clamp-1"><?php echo htmlspecialchars($intern['username'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                            <p class="text-xs text-on-surface-variant">Intern Kedayweb</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-3 gap-2 my-3 text-center text-xs">
                                        <div class="p-2 bg-white/60 rounded border border-outline-variant/40">
                                            <span class="block font-bold text-primary">0</span>
                                            <span class="text-[10px] text-on-surface-variant">Selesai</span>
                                        </div>
                                        <div class="p-2 bg-white/60 rounded border border-outline-variant/40">
                                            <span class="block font-bold text-amber-700">0</span>
                                            <span class="text-[10px] text-on-surface-variant">Pending</span>
                                        </div>
                                        <div class="p-2 bg-white/60 rounded border border-outline-variant/40">
                                            <span class="block font-bold text-green-700">0</span>
                                            <span class="text-[10px] text-on-surface-variant">Hadir</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-outline-variant/60 flex items-center gap-2">
                                    <a href="../dashboard.php?intern=<?php echo urlencode($intern['username']); ?>" target="_blank" class="flex-1 px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-bold hover:bg-primary-container text-center flex items-center justify-center gap-1 shadow-xs transition-colors">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                        <span>Lihat Dashboard</span>
                                    </a>
                                    <a href="admin-attendance.php?intern=<?php echo urlencode($intern['username']); ?>" class="px-2.5 py-1.5 border border-outline-variant text-on-surface hover:bg-surface-container-high rounded-lg text-xs font-semibold text-center flex items-center justify-center" title="Absensi Intern">
                                        <span class="material-symbols-outlined text-[16px]">calendar_month</span>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Project & Task Management Card (Admin Feature) -->
            <div class="glass-card p-5 rounded-xl border border-outline-variant lg:col-span-2 xl:col-span-3">
                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 mb-4 pb-3 border-b border-outline-variant">
                    <div>
                        <h3 class="font-headline-md font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">folder_managed</span>
                            <span>Manajemen Project &amp; Tugas (Admin)</span>
                        </h3>
                        <p class="text-sm text-on-surface-variant">Admin dapat menambah project, menambahkan tugas intern, dan menghapus project.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button onclick="openAdminNewProjectModal()" class="px-3.5 py-2 bg-primary text-on-primary rounded-lg text-xs font-bold hover:bg-primary-container flex items-center gap-1 shadow-sm">
                            <span class="material-symbols-outlined text-[16px]">create_new_folder</span>
                            <span>Tambah Project</span>
                        </button>
                        <button onclick="openAdminNewTaskModal()" class="px-3.5 py-2 bg-surface-container-high text-primary rounded-lg text-xs font-bold hover:bg-primary hover:text-white transition-colors flex items-center gap-1 border border-outline-variant">
                            <span class="material-symbols-outlined text-[16px]">add_task</span>
                            <span>Tambah Tugas</span>
                        </button>
                        <a href="../tasks.php" class="px-3 py-2 bg-surface-container-low text-on-surface rounded-lg text-xs font-semibold hover:bg-surface-container-high transition-colors flex items-center gap-1 border border-outline-variant">
                            <span class="material-symbols-outlined text-[16px]">view_kanban</span>
                            <span>Buka Kanban Board</span>
                        </a>
                    </div>
                </div>

                <!-- Metrics Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
                    <div class="p-3 bg-surface-container-low rounded-lg border border-outline-variant/60">
                        <span class="text-xs font-bold uppercase tracking-wider text-primary">Total Projects</span>
                        <div class="text-2xl font-bold mt-1" id="admin-active-projects">0</div>
                    </div>
                    <div class="p-3 bg-surface-container-low rounded-lg border border-outline-variant/60">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-700">Pending Tasks</span>
                        <div class="text-2xl font-bold mt-1 text-amber-700" id="admin-pending-tasks">0</div>
                    </div>
                    <div class="p-3 bg-surface-container-low rounded-lg border border-outline-variant/60">
                        <span class="text-xs font-bold uppercase tracking-wider text-green-700">Completed Tasks</span>
                        <div class="text-2xl font-bold mt-1 text-green-700" id="admin-completed-tasks">0</div>
                    </div>
                    <div class="p-3 bg-surface-container-low rounded-lg border border-outline-variant/60">
                        <span class="text-xs font-bold uppercase tracking-wider text-primary">Total Tasks</span>
                        <div class="text-2xl font-bold mt-1" id="admin-overdue-tasks">0</div>
                    </div>
                </div>

                <!-- Projects & Tasks Table -->
                <div class="overflow-x-auto rounded-lg border border-outline-variant">
                    <table class="min-w-full text-sm divide-y divide-outline-variant">
                        <thead class="bg-surface-container-low">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold text-on-surface">Nama Project</th>
                                <th class="px-3 py-2 text-left font-semibold text-on-surface">Deskripsi</th>
                                <th class="px-3 py-2 text-center font-semibold text-on-surface">Jumlah Tugas</th>
                                <th class="px-3 py-2 text-left font-semibold text-on-surface">Dibuat Oleh</th>
                                <th class="px-3 py-2 text-center font-semibold text-on-surface">Aksi Admin</th>
                            </tr>
                        </thead>
                        <tbody id="admin-project-list" class="divide-y divide-outline-variant bg-white">
                            <!-- Dynamic rows -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Attendance Overview Card -->
            <div class="glass-card p-4 rounded-xl border border-outline-variant">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-headline-md">Kehadiran Intern</h3>
                    <a href="admin-attendance.php" class="text-xs text-primary hover:underline flex items-center gap-1">
                        <span>Kalender Lengkap</span><span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                </div>
                <p class="text-sm text-on-surface-variant mb-3">Total kehadiran hari ini (semua intern).</p>
                <div class="flex items-center gap-2">
                    <span class="font-label-md text-label-md text-primary">Hadir/Telat Hari Ini</span>
                    <div class="text-2xl font-bold" id="admin-attendance-today">0</div>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <span class="font-label-md text-label-md text-primary">Tidak Masuk Hari Ini</span>
                    <div class="text-2xl font-bold text-error" id="admin-attendance-absent-today">0</div>
                </div>
            </div>
            <!-- Completed Tasks Overview Card -->
            <div class="glass-card p-4 rounded-xl border border-outline-variant">
                <h3 class="font-headline-md mb-2">Tugas Selesai</h3>
                <p class="text-sm text-on-surface-variant mb-3">Jumlah tugas yang telah diselesaikan oleh semua intern.</p>
                <div class="text-2xl font-bold" id="admin-total-completed-tasks">0</div>
            </div>
            <!-- Attendance per Intern Card -->
            <div class="glass-card p-4 rounded-xl border border-outline-variant">
                <h3 class="font-headline-md mb-2">Kehadiran per Intern</h3>
                <p class="text-sm text-on-surface-variant mb-3">Pilih intern untuk melihat total kehadiran bulan ini.</p>
                <select id="admin-attendance-intern-select" class="w-full mb-2 p-2 border border-outline-variant rounded">
                    <option value="" disabled selected>Pilih intern...</option>
                </select>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="bg-green-50 rounded-lg p-2">
                        <div class="text-xs text-green-700 font-bold">Hadir</div>
                        <div class="text-xl font-bold text-green-800" id="admin-attendance-intern-present">0</div>
                    </div>
                    <div class="bg-amber-50 rounded-lg p-2">
                        <div class="text-xs text-amber-700 font-bold">Telat</div>
                        <div class="text-xl font-bold text-amber-800" id="admin-attendance-intern-late">0</div>
                    </div>
                    <div class="bg-red-50 rounded-lg p-2">
                        <div class="text-xs text-red-700 font-bold">Absen</div>
                        <div class="text-xl font-bold text-red-800" id="admin-attendance-intern-absent">0</div>
                    </div>
                </div>
            </div>
            <!-- News & Lessons Card -->
        </section>
        <div class="mt-auto shrink-0 w-full">
            <?php include '../partials/footer.php'; ?>
        </div>
    </main>
    <script>
        // Determine intern name from URL parameter (admin view)
        const params = new URLSearchParams(window.location.search);
        const internParam = params.get('intern');
        if (internParam) {
            const nameElem = document.getElementById('intern-name');
            if (nameElem) nameElem.textContent = decodeURIComponent(internParam);
        }
        // Existing dashboard script continues below
        document.getElementById('maintenance-toggle')?.addEventListener('change', e => {
            const label = e.target.nextElementSibling.nextElementSibling;
            if (label) label.textContent = e.target.checked ? 'On' : 'Off';
        });
        document.getElementById('guest-toggle')?.addEventListener('change', e => {
            const label = e.target.nextElementSibling.nextElementSibling;
            if (label) label.textContent = e.target.checked ? 'On' : 'Off';
        });
        document.getElementById('save-settings')?.addEventListener('click', () => {
            alert('System settings saved (demo).');
        });

        /* ================= ADMIN PROJECT & TASK CONTROLLER ================= */
        function renderAdminProjects() {
            if (!window.ProjectStore) return;
            const projects = ProjectStore.projects();
            const tbody = document.getElementById('admin-project-list');
            if (!tbody) return;

            let totalTasks = 0;
            let pendingTasks = 0;
            let completedTasks = 0;

            tbody.innerHTML = '';

            if (projects.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500 italic">
                            Belum ada project aktif. Klik tombol "+ Tambah Project" di atas untuk membuat project baru.
                        </td>
                    </tr>
                `;
            } else {
                projects.forEach(p => {
                    const tasks = p.tasks || [];
                    totalTasks += tasks.length;
                    tasks.forEach(t => {
                        if (t.status === 'done') completedTasks++;
                        else pendingTasks++;
                    });

                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50 transition-colors';
                    tr.innerHTML = `
                        <td class="px-3 py-3 font-semibold text-primary">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">folder</span>
                                <span>${escapeHtml(p.name)}</span>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-slate-600 max-w-xs truncate">${escapeHtml(p.description || 'Tidak ada deskripsi')}</td>
                        <td class="px-3 py-3 text-center">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary-container text-on-primary-container">
                                ${tasks.length} task
                            </span>
                        </td>
                        <td class="px-3 py-3 text-slate-500 text-xs">${escapeHtml(p.created_by || 'Admin')}</td>
                        <td class="px-3 py-3 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                <button onclick="openAdminNewTaskModal('${p.id}')" class="px-2.5 py-1 text-xs font-semibold bg-primary text-white rounded hover:bg-primary-container transition-colors flex items-center gap-1" title="Tambah Tugas ke Project ini">
                                    <span class="material-symbols-outlined text-[14px]">add</span>
                                    <span>Tugas</span>
                                </button>
                                <a href="../tasks.php?project=${p.id}" class="px-2.5 py-1 text-xs font-semibold border border-outline-variant rounded hover:bg-slate-100 transition-colors flex items-center gap-1" title="Lihat di Kanban">
                                    <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                                    <span>Kanban</span>
                                </a>
                                <button onclick="adminDeleteProject('${p.id}', '${escapeHtml(p.name)}')" class="px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 rounded transition-colors" title="Hapus Project (Hanya Admin)">
                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                </button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            document.getElementById('admin-active-projects').textContent = projects.length;
            document.getElementById('admin-pending-tasks').textContent = pendingTasks;
            document.getElementById('admin-completed-tasks').textContent = completedTasks;
            document.getElementById('admin-overdue-tasks').textContent = totalTasks;
        }

        function escapeHtml(str) {
            return String(str || '').replace(/[&<>'"]/g, char => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
            }[char]));
        }

        function openAdminNewProjectModal() {
            document.getElementById('admin-new-proj-name').value = '';
            document.getElementById('admin-new-proj-desc').value = '';
            document.getElementById('admin-proj-modal').classList.remove('hidden');
            document.getElementById('admin-new-proj-name').focus();
        }

        function closeAdminNewProjectModal() {
            document.getElementById('admin-proj-modal').classList.add('hidden');
        }

        function saveAdminNewProject(e) {
            e.preventDefault();
            const name = document.getElementById('admin-new-proj-name').value.trim();
            const desc = document.getElementById('admin-new-proj-desc').value.trim();
            if (!name) return;

            ProjectStore.createProject({
                name,
                description: desc,
                created_by: 'Admin Alex'
            });

            closeAdminNewProjectModal();
            renderAdminProjects();
            alert('Project baru berhasil ditambahkan oleh Admin!');
        }

        function openAdminNewTaskModal(preSelectedProjectId) {
            const select = document.getElementById('admin-task-project-select');
            select.innerHTML = '';
            const projects = ProjectStore.projects();

            if (projects.length === 0) {
                alert('Silakan buat project terlebih dahulu sebelum menambahkan tugas!');
                return;
            }

            projects.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.name;
                if (preSelectedProjectId && p.id === preSelectedProjectId) {
                    opt.selected = true;
                }
                select.appendChild(opt);
            });

            const assigneeSelect = document.getElementById('admin-task-assignee');
            assigneeSelect.innerHTML = '';
            InternStore.list().forEach(i => {
                const opt = document.createElement('option');
                opt.value = i.name;
                opt.textContent = `${i.name} (Intern)`;
                assigneeSelect.appendChild(opt);
            });

            document.getElementById('admin-task-title').value = '';
            document.getElementById('admin-task-desc').value = '';
            document.getElementById('admin-task-due').value = '';
            document.getElementById('admin-task-priority').value = 'Medium';
            document.getElementById('admin-task-status').value = 'todo';

            document.getElementById('admin-task-modal').classList.remove('hidden');
            document.getElementById('admin-task-title').focus();
        }

        function closeAdminNewTaskModal() {
            document.getElementById('admin-task-modal').classList.add('hidden');
        }

        function saveAdminNewTask(e) {
            e.preventDefault();
            const projectId = document.getElementById('admin-task-project-select').value;
            const title = document.getElementById('admin-task-title').value.trim();
            const desc = document.getElementById('admin-task-desc').value.trim();
            const assignee = document.getElementById('admin-task-assignee').value.trim();
            const due_date = document.getElementById('admin-task-due').value;
            const priority = document.getElementById('admin-task-priority').value;
            const status = document.getElementById('admin-task-status').value;

            if (!projectId || !title) return;

            ProjectStore.createTask(projectId, {
                title,
                description: desc,
                assignee,
                due_date,
                priority,
                status
            });

            closeAdminNewTaskModal();
            renderAdminProjects();
            alert(`Tugas "${title}" berhasil ditambahkan dan ditugaskan ke ${assignee || 'Intern'}!`);
        }

        function adminDeleteProject(projectId, projectName) {
            if (confirm(`PERINGATAN ADMIN:\nApakah Anda yakin ingin menghapus project "${projectName}"?\nSemua tugas di dalamnya akan dihapus secara permanen.`)) {
                const deleted = ProjectStore.deleteProject(projectId);
                if (deleted) {
                    renderAdminProjects();
                    alert(`Project "${projectName}" berhasil dihapus oleh Admin.`);
                }
            }
        }



        function renderInternTasksForGrading() {
            const internName = document.getElementById('grade-intern-select').value;
            const table = document.getElementById('intern-tasks-table');
            const body = document.getElementById('intern-tasks-body');
            const empty = document.getElementById('intern-tasks-empty');
            if (!internName) { table.style.display = 'none'; empty.classList.add('hidden'); return; }

            const tasks = ProjectStore.tasksByAssignee(internName);
            body.innerHTML = '';

            if (tasks.length === 0) {
                table.style.display = 'none';
                empty.classList.remove('hidden');
                return;
            }
            empty.classList.add('hidden');
            table.style.display = 'table';

            tasks.forEach(t => {
                const tr = document.createElement('tr');
                tr.dataset.taskId = t.id;
                tr.innerHTML = `
                    <td class="px-2 py-1.5">
                        <div class="font-semibold">${escapeHtml(t.title)}</div>
                        <div class="text-xs text-on-surface-variant">${escapeHtml(t.project_name)} &middot; ${escapeHtml(t.status)}</div>
                    </td>
                    <td class="px-2 py-1.5">
                        <select class="rating-select w-full border border-outline-variant rounded px-1 py-1 text-xs" data-project-id="${t.project_id}" data-task-id="${t.id}">
                            <option value="">-- Pilih --</option>
                            <option value="1" ${t.rating == 1 ? 'selected' : ''}>1 ⭐</option>
                            <option value="2" ${t.rating == 2 ? 'selected' : ''}>2 ⭐⭐</option>
                            <option value="3" ${t.rating == 3 ? 'selected' : ''}>3 ⭐⭐⭐</option>
                            <option value="4" ${t.rating == 4 ? 'selected' : ''}>4 ⭐⭐⭐⭐</option>
                            <option value="5" ${t.rating == 5 ? 'selected' : ''}>5 ⭐⭐⭐⭐⭐</option>
                        </select>
                    </td>
                    <td class="px-2 py-1.5 text-center">
                        <button class="text-error delete-task" data-project-id="${t.project_id}" data-task-id="${t.id}" title="Hapus"><span class="material-symbols-outlined">delete</span></button>
                    </td>
                `;
                body.appendChild(tr);
            });

            body.querySelectorAll('.rating-select').forEach(select => {
                select.addEventListener('change', e => {
                    const { projectId, taskId } = e.target.dataset;
                    const rating = e.target.value;
                    if (!rating) return;
                    ProjectStore.rateTask(projectId, taskId, Number(rating));
                });
            });
            body.querySelectorAll('.delete-task').forEach(btn => {
                btn.addEventListener('click', e => {
                    const { projectId, taskId } = btn.dataset;
                    if (!confirm('Hapus pekerjaan ini secara permanen?')) return;
                    ProjectStore.deleteTask(projectId, taskId);
                    renderInternTasksForGrading();
                    renderAdminProjects();
                });
            });
        }

 

        function deleteInternUser(id, name) {
            if (!confirm(`Hapus intern "${name}"? Data kehadirannya juga akan dihapus.`)) return;
            InternStore.removeIntern(id);
            renderEverything();
        }

        function openAddInternModal() {
            document.getElementById('new-intern-name').value = '';
            document.getElementById('new-intern-email').value = '';
            document.getElementById('new-intern-division').value = '';
            document.getElementById('add-intern-modal').classList.remove('hidden');
            document.getElementById('add-intern-modal').classList.add('flex');
            document.getElementById('new-intern-name').focus();
        }

        function closeAddInternModal() {
            document.getElementById('add-intern-modal').classList.add('hidden');
            document.getElementById('add-intern-modal').classList.remove('flex');
        }

        function saveNewIntern(e) {
            e.preventDefault();
            const name = document.getElementById('new-intern-name').value.trim();
            const email = document.getElementById('new-intern-email').value.trim();
            const division = document.getElementById('new-intern-division').value.trim();
            if (!name) return;
            InternStore.addIntern({ name, email, division });
            closeAddInternModal();
            renderEverything();
        }

        /* ================= INTERN DASHBOARD VIEWER ================= */
        function renderInternDashboardList() {
            const container = document.getElementById('admin-intern-cards-list');
            if (!container) return;
            
            let interns = window.InternStore ? InternStore.list() : [];
            
            // Gabungkan akun intern dari Database MySQL (db_internspace.users) jika belum ada di InternStore
            if (window.DB_INTERNS && Array.isArray(window.DB_INTERNS)) {
                const existingNames = new Set(interns.map(i => i.name.toLowerCase()));
                window.DB_INTERNS.forEach(dbUser => {
                    const uname = dbUser.username;
                    if (!existingNames.has(uname.toLowerCase())) {
                        interns.push({
                            id: 'db-' + dbUser.id,
                            name: uname,
                            email: uname,
                            role: 'Intern',
                            division: 'Intern Kedayweb'
                        });
                        existingNames.add(uname.toLowerCase());
                    }
                });
            }

            container.innerHTML = '';
            
            if (interns.length === 0) {
                container.innerHTML = `<div class="col-span-3 text-center py-6 text-on-surface-variant italic">Belum ada intern terdaftar di database.</div>`;
                return;
            }

            interns.forEach(intern => {
                const tasks = window.ProjectStore ? ProjectStore.tasksByAssignee(intern.name) : [];
                const completedTasks = tasks.filter(t => t.status === 'done').length;
                const pendingTasks = tasks.filter(t => t.status !== 'done').length;
                
                const dbUserAtt = dbAttendanceMap[intern.name] || {};
                let presentCount = Object.values(dbUserAtt).filter(r => r.status === 'present' || r.status === 'late').length;
                if (presentCount === 0) {
                    const attendance = InternStore.getAttendance(intern.name);
                    presentCount = attendance.filter(r => r.status === 'present' || r.status === 'late').length;
                }

                const card = document.createElement('div');
                card.className = 'p-4 rounded-xl border border-outline-variant bg-surface-container-low flex flex-col justify-between hover:shadow-md transition-all';
                card.innerHTML = `
                    <div>
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold text-lg">
                                ${escapeHtml(intern.name.charAt(0).toUpperCase())}
                            </div>
                            <div>
                                <h4 class="font-bold text-on-surface text-base line-clamp-1">${escapeHtml(intern.name)}</h4>
                                <p class="text-xs text-on-surface-variant">${escapeHtml(intern.division || 'Intern Kedayweb')}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2 my-3 text-center text-xs">
                            <div class="p-2 bg-white/60 rounded border border-outline-variant/40">
                                <span class="block font-bold text-primary">${completedTasks}</span>
                                <span class="text-[10px] text-on-surface-variant">Selesai</span>
                            </div>
                            <div class="p-2 bg-white/60 rounded border border-outline-variant/40">
                                <span class="block font-bold text-amber-700">${pendingTasks}</span>
                                <span class="text-[10px] text-on-surface-variant">Pending</span>
                            </div>
                            <div class="p-2 bg-white/60 rounded border border-outline-variant/40">
                                <span class="block font-bold text-green-700">${presentCount}</span>
                                <span class="text-[10px] text-on-surface-variant">Hadir</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-outline-variant/60 flex items-center gap-2">
                        <a href="../dashboard.php?intern=${encodeURIComponent(intern.name)}" target="_blank" class="flex-1 px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-bold hover:bg-primary-container text-center flex items-center justify-center gap-1 shadow-xs transition-colors">
                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                            <span>Lihat Dashboard</span>
                        </a>
                        <a href="admin-attendance.php?intern=${encodeURIComponent(intern.name)}" class="px-2.5 py-1.5 border border-outline-variant text-on-surface hover:bg-surface-container-high rounded-lg text-xs font-semibold text-center flex items-center justify-center" title="Absensi Intern">
                            <span class="material-symbols-outlined text-[16px]">calendar_month</span>
                        </a>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        let dbAttendanceMap = {};

        async function fetchDbAttendanceForDashboard() {
            try {
                const res = await fetch('../attendance-api.php?action=all_summary');
                if (res.ok) {
                    const data = await res.json();
                    dbAttendanceMap = data.attendanceMap || {};
                }
            } catch (err) {
                console.warn('Gagal memuat absensi DB:', err);
            }
        }

        /* ================= 1D. ATTENDANCE OVERVIEW ================= */
        function todayStr() {
            const d = new Date();
            return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
        }

        function renderAttendanceOverview() {
            const tDate = todayStr();
            let totalPresentToday = 0;
            let totalAbsentToday = 0;

            // Hitung absensi hari ini dari data MySQL DB
            Object.values(dbAttendanceMap).forEach(userAtt => {
                const rec = userAtt[tDate];
                if (rec) {
                    if (rec.status === 'present' || rec.status === 'late') totalPresentToday++;
                    else if (rec.status === 'absent') totalAbsentToday++;
                }
            });

            const presentTodayEl = document.getElementById('admin-attendance-today');
            const absentTodayEl = document.getElementById('admin-attendance-absent-today');
            if (presentTodayEl) presentTodayEl.textContent = totalPresentToday;
            if (absentTodayEl) absentTodayEl.textContent = totalAbsentToday;

            let totalCompleted = 0;
            ProjectStore.projects().forEach(p => (p.tasks || []).forEach(t => { if (t.status === 'done') totalCompleted++; }));
            const completedEl = document.getElementById('admin-total-completed-tasks');
            if (completedEl) completedEl.textContent = totalCompleted;

            const select = document.getElementById('admin-attendance-intern-select');
            if (select) {
                const current = select.value;
                let listInterns = InternStore.list();
                if (window.DB_INTERNS && Array.isArray(window.DB_INTERNS)) {
                    const existingNames = new Set(listInterns.map(i => i.name.toLowerCase()));
                    window.DB_INTERNS.forEach(u => {
                        if (!existingNames.has(u.username.toLowerCase())) {
                            listInterns.push({ name: u.username });
                        }
                    });
                }
                select.innerHTML = '<option value="" disabled ' + (current ? '' : 'selected') + '>Pilih intern...</option>' +
                    listInterns.map(i => `<option value="${escapeHtml(i.name)}" ${i.name === current ? 'selected' : ''}>${escapeHtml(i.name)}</option>`).join('');
                if (current) renderInternAttendanceCounts(current);
            }
        }

        function renderInternAttendanceCounts(name) {
            const dbUserAtt = dbAttendanceMap[name] || {};
            let present = 0, late = 0, absent = 0;
            Object.values(dbUserAtt).forEach(r => {
                if (r.status === 'present') present++;
                else if (r.status === 'late') late++;
                else if (r.status === 'absent') absent++;
            });

            // Fallback ke InternStore jika di DB map belum ada
            if (present === 0 && late === 0 && absent === 0) {
                const records = InternStore.getAttendance(name);
                present = records.filter(r => r.status === 'present').length;
                late = records.filter(r => r.status === 'late').length;
                absent = records.filter(r => r.status === 'absent').length;
            }

            document.getElementById('admin-attendance-intern-present').textContent = present;
            document.getElementById('admin-attendance-intern-late').textContent = late;
            document.getElementById('admin-attendance-intern-absent').textContent = absent;
        }

        /* ================= INIT ================= */
        async function renderEverything() {
            if (window.InternStore && typeof InternStore.seedAllAttendance === 'function') {
                InternStore.seedAllAttendance();
            }
            await fetchDbAttendanceForDashboard();
            renderInternDashboardList();
            renderAdminProjects();
            renderAttendanceOverview();
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Set role to admin explicitly on admin dashboard
            if (window.ProjectStore) {
                ProjectStore.setRole('admin');
            }
            renderEverything();

            // Jika dibuka lewat link "Pilih Intern" (dashboard.php?intern=Nama), pre-select di dropdown terkait
            if (internParam) {
                const decoded = decodeURIComponent(internParam);
                const attSelect = document.getElementById('admin-attendance-intern-select');
                if (attSelect) { attSelect.value = decoded; renderInternAttendanceCounts(decoded); }
            }

            document.getElementById('admin-attendance-intern-select')?.addEventListener('change', e => renderInternAttendanceCounts(e.target.value));
        });
    </script>



    <!-- Modal Tambah Project (Khusus Admin) -->
    <div id="admin-proj-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs flex">
        <div class="bg-white rounded-2xl border border-outline-variant w-full max-w-md p-6 shadow-2xl">
            <div class="flex justify-between items-center mb-4 border-b border-outline-variant pb-2">
                <h3 class="font-headline-md font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">create_new_folder</span>
                    <span>Tambah Project Baru (Admin)</span>
                </h3>
                <button type="button" onclick="closeAdminNewProjectModal()" class="text-on-surface-variant hover:text-primary">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form onsubmit="saveAdminNewProject(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase mb-1">Nama Project</label>
                    <input required id="admin-new-proj-name" type="text" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none" placeholder="Contoh: Redesign Portal Magang"/>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase mb-1">Deskripsi Project</label>
                    <textarea id="admin-new-proj-desc" rows="3" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none" placeholder="Tujuan dan ruang lingkup project..."></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-outline-variant">
                    <button type="button" onclick="closeAdminNewProjectModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-primary text-white text-xs font-bold hover:bg-primary-container">Simpan Project</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Tugas (Khusus Admin) -->
    <div id="admin-task-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs flex">
        <div class="bg-white rounded-2xl border border-outline-variant w-full max-w-lg p-6 shadow-2xl">
            <div class="flex justify-between items-center mb-4 border-b border-outline-variant pb-2">
                <h3 class="font-headline-md font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">add_task</span>
                    <span>Tambah Tugas Baru (Admin)</span>
                </h3>
                <button type="button" onclick="closeAdminNewTaskModal()" class="text-on-surface-variant hover:text-primary">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form onsubmit="saveAdminNewTask(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase mb-1">Pilih Project</label>
                    <select id="admin-task-project-select" required class="w-full bg-surface-bright border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none">
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase mb-1">Judul Tugas</label>
                    <input required id="admin-task-title" type="text" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none" placeholder="Judul tugas..."/>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase mb-1">Deskripsi Tugas</label>
                    <textarea id="admin-task-desc" rows="3" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none" placeholder="Detail instruksi penugasan..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-on-surface uppercase mb-1">Ditugaskan Kepada (Assignee)</label>
                        <select id="admin-task-assignee" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none">
                            <option value="John Doe">John Doe (Intern)</option>
                            <option value="Jane Smith">Jane Smith (Intern)</option>
                            <option value="Alex Doe">Alex Doe (Intern)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface uppercase mb-1">Tenggat Waktu (Due Date)</label>
                        <input id="admin-task-due" type="date" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none"/>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-on-surface uppercase mb-1">Prioritas</label>
                        <select id="admin-task-priority" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface uppercase mb-1">Status Awal</label>
                        <select id="admin-task-status" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none">
                            <option value="todo" selected>To Do</option>
                            <option value="progress">In Progress</option>
                            <option value="review">Review</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-outline-variant">
                    <button type="button" onclick="closeAdminNewTaskModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-primary text-white text-xs font-bold hover:bg-primary-container">Simpan Tugas</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
