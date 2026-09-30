<?php 
require_once __DIR__ . '/session.php';
require_login();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>InternSpace - Kanban Board</title>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="shared-config.js"></script>
    <script src="project-store.js"></script>
    <script src="intern-store.js"></script>
    <style>
        .kanban-scroll::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .kanban-scroll::-webkit-scrollbar-track {
            background: #f8f9ff;
            border-radius: 4px;
        }

        .kanban-scroll::-webkit-scrollbar-thumb {
            background: #c5c5d3;
            border-radius: 4px;
        }

        .kanban-scroll::-webkit-scrollbar-thumb:hover {
            background: #757682;
        }

        .kanban-scroll {
            scroll-behavior: smooth;
        }

        .hover-card-shadow:hover {
            box-shadow: 0 10px 15px -3px rgba(30, 58, 138, 0.08), 0 4px 6px -2px rgba(30, 58, 138, 0.04);
        }

        .drag-target-active {
            outline: 2px dashed #1e3a8a;
            background-color: rgba(220, 233, 255, 0.5) !important;
        }
    </style>
</head>

<body class="bg-surface text-on-surface font-body-md text-body-md h-screen overflow-hidden flex">

<?php 
$active = 'tasks'; 
if (is_admin()) {
    include 'partials/sidebar-admin.php';
} else {
    include 'partials/sidebar-intern.php';
}
?>

    <!-- Main Content Area -->
    <main class="flex-1 md:ml-[16.5rem] flex flex-col h-full bg-surface overflow-x-auto">
        <!-- Top Action Bar showing Active Project Name -->
        <header class="min-h-20 px-gutter py-3 flex flex-col md:flex-row items-start md:items-center justify-between border-b border-outline-variant bg-surface-container-lowest flex-shrink-0 gap-md min-w-max">
            <div class="flex items-center gap-md">
                <button onclick="toggleMobileSidebar()" type="button" class="md:hidden p-2 text-on-surface hover:bg-surface-container-high rounded-lg shrink-0" aria-label="Buka Menu Sidebar">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <div class="flex flex-col sm:flex-row sm:items-center gap-md">
                    <div>
                        <div class="flex items-center gap-sm">
                            <h2 id="project-title-display" data-no-i18n="true" class="font-headline-lg text-headline-lg text-on-surface font-bold">Memuat...</h2>
                            <span id="project-task-pill" class="bg-primary-fixed text-primary px-2.5 py-0.5 rounded-full text-xs font-bold">0 tasks</span>
                        </div>
                    <p id="project-desc-display" data-no-i18n="true" class="font-body-sm text-body-sm text-on-surface-variant max-w-md truncate">Sprint Kanban Project Management</p>
                </div>
                <!-- Project Dropdown Switcher -->
                <div class="flex items-center gap-2 bg-white border border-slate-200 px-3 py-1.5 rounded-xl shadow-sm">
                    <label for="project-selector" class="text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Project:</label>
                    <select id="project-selector" onchange="switchProject(this.value)" aria-label="Select Project" class="bg-transparent text-sm font-bold text-blue-900 py-1 pl-1 pr-8 border-none focus:ring-0 focus:outline-none cursor-pointer min-w-[160px] max-w-[240px] truncate">
                        <option value="">-- Pilih Project --</option>
                    </select>
                    <a href="projects.php" title="Kelola Semua Project" class="p-1 rounded-lg text-slate-400 hover:text-blue-900 hover:bg-slate-100 transition-colors flex items-center">
                        <span class="material-symbols-outlined text-[20px]">manage_accounts</span>
                    </a>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-sm w-full md:w-auto">
                <div class="relative flex-1 md:w-64">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                    <input id="kanban-search" oninput="filterKanban()" type="search" placeholder="Search tasks..." class="w-full pl-8 pr-3 py-1.5 rounded-lg bg-surface-bright border border-outline-variant font-body-sm text-body-sm focus:ring-2 focus:ring-primary focus:outline-none">
                </div>
                <div class="flex items-center gap-1 bg-surface-container-low p-1 rounded-lg border border-outline-variant text-xs">
                    <button type="button" onclick="setPriorityFilter('All')" id="filter-all" class="px-2.5 py-1 rounded-md font-semibold bg-white text-primary shadow-sm cursor-pointer">All</button>
                    <button type="button" onclick="setPriorityFilter('High')" id="filter-high" class="px-2.5 py-1 rounded-md text-on-surface-variant hover:text-primary cursor-pointer">High</button>
                    <button type="button" onclick="setPriorityFilter('Medium')" id="filter-medium" class="px-2.5 py-1 rounded-md text-on-surface-variant hover:text-primary cursor-pointer">Medium</button>
                    <button type="button" onclick="setPriorityFilter('Low')" id="filter-low" class="px-2.5 py-1 rounded-md text-on-surface-variant hover:text-primary cursor-pointer">Low</button>
                </div>
                <!-- Primary Add Task Button -->
                <button type="button" id="btn-add-task-header" onclick="openTaskModal()" class="bg-primary text-on-primary font-label-md text-label-md px-md py-sm rounded-lg flex items-center gap-xs hover:bg-primary-container transition-colors active:scale-95 shadow-sm cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    <span data-i18n="btn_new_task">New Task</span>
                </button>
            </div>
        </header>

        <!-- Kanban Board Container -->
        <div class="flex-1 overflow-x-auto overflow-y-hidden p-gutter kanban-scroll">
            <div class="flex gap-gutter h-full pb-sm items-start min-w-max pr-8">

                <!-- Column: To Do -->
                <div class="w-80 flex flex-col gap-md h-full flex-shrink-0">
                    <div class="flex items-center justify-between bg-surface-container-low px-md py-sm rounded-lg border border-outline-variant">
                        <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-sm font-semibold">
                            <span class="w-3 h-3 rounded-full bg-outline"></span>
                            <span data-i18n="col_todo">To Do</span>
                        </h3>
                        <div class="flex items-center gap-1.5">
                            <span id="todo-count" class="bg-surface-container-highest text-on-surface px-sm py-xs rounded-full font-label-sm text-label-sm font-bold">0</span>
                            <button type="button" onclick="openTaskModal(null, 'todo')" title="Tambah Task di To Do" class="p-0.5 rounded text-on-surface-variant hover:text-primary hover:bg-white transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-[18px]">add</span>
                            </button>
                        </div>
                    </div>
                    <div id="todo-col" class="flex-1 overflow-y-auto kanban-scroll flex flex-col gap-sm pr-xs pb-xl transition-all duration-150" ondragover="allowDrop(event, 'todo')" ondragleave="removeDragHighlight('todo')" ondrop="drop(event, 'todo')">
                    </div>
                    <button type="button" onclick="openTaskModal(null, 'todo')" class="w-full py-2 px-3 rounded-xl border border-dashed border-outline-variant text-primary font-semibold text-xs hover:bg-surface-container-high transition-colors flex items-center justify-center gap-1 cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">add</span> Tambah Task
                    </button>
                </div>

                <!-- Column: In Progress -->
                <div class="w-80 flex flex-col gap-md h-full flex-shrink-0">
                    <div class="flex items-center justify-between bg-primary-fixed border border-primary-fixed-dim px-md py-sm rounded-lg shadow-sm">
                        <h3 class="font-headline-md text-headline-md text-on-primary-fixed flex items-center gap-sm font-semibold">
                            <span class="w-3 h-3 rounded-full bg-primary animate-pulse"></span>
                            <span data-i18n="col_progress">In Progress</span>
                        </h3>
                        <div class="flex items-center gap-1.5">
                            <span id="inprogress-count" class="bg-primary text-on-primary px-sm py-xs rounded-full font-label-sm text-label-sm shadow-sm font-bold">0</span>
                            <button type="button" onclick="openTaskModal(null, 'inprogress')" title="Tambah Task di In Progress" class="p-0.5 rounded text-on-primary-fixed hover:text-white hover:bg-primary transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-[18px]">add</span>
                            </button>
                        </div>
                    </div>
                    <div id="inprogress-col" class="flex-1 overflow-y-auto kanban-scroll flex flex-col gap-sm pr-xs pb-xl transition-all duration-150" ondragover="allowDrop(event, 'inprogress')" ondragleave="removeDragHighlight('inprogress')" ondrop="drop(event, 'inprogress')">
                    </div>
                    <button type="button" onclick="openTaskModal(null, 'inprogress')" class="w-full py-2 px-3 rounded-xl border border-dashed border-outline-variant text-primary font-semibold text-xs hover:bg-surface-container-high transition-colors flex items-center justify-center gap-1 cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">add</span> Tambah Task
                    </button>
                </div>

                <!-- Column: Under Review -->
                <div class="w-80 flex flex-col gap-md h-full flex-shrink-0">
                    <div class="flex items-center justify-between bg-secondary-fixed border border-secondary-fixed-dim px-md py-sm rounded-lg">
                        <h3 class="font-headline-md text-headline-md text-on-secondary-fixed flex items-center gap-sm font-semibold">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                            <span data-i18n="col_review">Under Review</span>
                        </h3>
                        <div class="flex items-center gap-1.5">
                            <span id="underreview-count" class="bg-secondary-fixed-dim text-on-secondary-fixed px-sm py-xs rounded-full font-label-sm text-label-sm font-bold">0</span>
                            <button type="button" onclick="openTaskModal(null, 'underreview')" title="Tambah Task di Under Review" class="p-0.5 rounded text-on-secondary-fixed hover:text-primary hover:bg-white transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-[18px]">add</span>
                            </button>
                        </div>
                    </div>
                    <div id="underreview-col" class="flex-1 overflow-y-auto kanban-scroll flex flex-col gap-sm pr-xs pb-xl transition-all duration-150" ondragover="allowDrop(event, 'underreview')" ondragleave="removeDragHighlight('underreview')" ondrop="drop(event, 'underreview')">
                    </div>
                    <button type="button" onclick="openTaskModal(null, 'underreview')" class="w-full py-2 px-3 rounded-xl border border-dashed border-outline-variant text-primary font-semibold text-xs hover:bg-surface-container-high transition-colors flex items-center justify-center gap-1 cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">add</span> Tambah Task
                    </button>
                </div>

                <!-- Column: Done -->
                <div class="w-80 flex flex-col gap-md h-full flex-shrink-0">
                    <div class="flex items-center justify-between bg-surface-container-lowest border border-outline-variant px-md py-sm rounded-lg shadow-sm relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-surface-container-lowest to-secondary-fixed opacity-50 z-0"></div>
                        <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-sm relative z-10 font-semibold">
                            <span class="material-symbols-outlined text-green-700" style="font-variation-settings: 'FILL' 1;">task_alt</span>
                            <span data-i18n="col_done">Done</span>
                        </h3>
                        <div class="flex items-center gap-1.5 relative z-10">
                            <span id="done-count" class="bg-slate-200 text-on-surface px-sm py-xs rounded-full font-label-sm text-label-sm font-bold">0</span>
                            <button type="button" onclick="openTaskModal(null, 'done')" title="Tambah Task di Done" class="p-0.5 rounded text-on-surface-variant hover:text-primary hover:bg-white transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-[18px]">add</span>
                            </button>
                        </div>
                    </div>
                    <div id="done-col" class="flex-1 overflow-y-auto kanban-scroll flex flex-col gap-sm pr-xs pb-xl transition-all duration-150" ondragover="allowDrop(event, 'done')" ondragleave="removeDragHighlight('done')" ondrop="drop(event, 'done')">
                    </div>
                    <button type="button" onclick="openTaskModal(null, 'done')" class="w-full py-2 px-3 rounded-xl border border-dashed border-outline-variant text-primary font-semibold text-xs hover:bg-surface-container-high transition-colors flex items-center justify-center gap-1 cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">add</span> Tambah Task
                    </button>
                </div>

            </div>
        </div>
        <div class="shrink-0 mt-auto w-full">
            <?php include 'partials/footer.php'; ?>
        </div>
    </main>

    <!-- Task Creation & Edit Modal -->
    <div id="modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div class="bg-white rounded-2xl border border-outline-variant w-full max-w-lg p-md md:p-lg shadow-2xl animate-in fade-in zoom-in duration-200" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center mb-md border-b border-outline-variant pb-sm">
                <h3 class="font-headline-md text-headline-md text-on-surface font-bold" id="modal-title">New Task</h3>
                <button type="button" onclick="closeTaskModal()" class="text-on-surface-variant hover:text-primary p-1 rounded-full cursor-pointer"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form id="task-modal-form" onsubmit="saveTaskModal(event)" class="space-y-md">
                <input type="hidden" id="modal-task-id" />
                <div>
                    <label for="modal-task-title" class="block font-label-md text-label-md text-on-surface mb-xs">Title *</label>
                    <input required id="modal-task-title" type="text" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-md py-sm font-body-sm text-body-sm focus:ring-2 focus:ring-primary focus:outline-none" placeholder="Task title..." />
                </div>
                <div>
                    <label for="modal-task-desc" class="block font-label-md text-label-md text-on-surface mb-xs">Description</label>
                    <textarea id="modal-task-desc" rows="3" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-md py-sm font-body-sm text-body-sm focus:ring-2 focus:ring-primary focus:outline-none" placeholder="Task details..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-md">
                    <div>
                        <label for="modal-task-priority" class="block font-label-md text-label-md text-on-surface mb-xs">Priority</label>
                        <select id="modal-task-priority" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-md py-sm font-body-sm text-body-sm focus:ring-2 focus:ring-primary focus:outline-none cursor-pointer">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    <div>
                        <label for="modal-task-status" class="block font-label-md text-label-md text-on-surface mb-xs">Column</label>
                        <select id="modal-task-status" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-md py-sm font-body-sm text-body-sm focus:ring-2 focus:ring-primary focus:outline-none cursor-pointer">
                            <option value="todo">To Do</option>
                            <option value="inprogress">In Progress</option>
                            <option value="underreview">Under Review</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-md">
                    <div>
                        <label for="modal-task-assignee" class="block font-label-md text-label-md text-on-surface mb-xs">Assignee</label>
                        <select id="modal-task-assignee" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-md py-sm font-body-sm text-body-sm focus:ring-2 focus:ring-primary focus:outline-none cursor-pointer">
                            <option value="">-- Pilih Assignee --</option>
                        </select>
                    </div>
                    <div>
                        <label for="modal-task-due-date" class="block font-label-md text-label-md text-on-surface mb-xs">Due Date</label>
                        <input id="modal-task-due-date" type="date" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-md py-sm font-body-sm text-body-sm focus:ring-2 focus:ring-primary focus:outline-none cursor-pointer" />
                    </div>
                </div>
                <div class="flex justify-between items-center pt-sm border-t border-outline-variant">
                    <button type="button" id="modal-delete-btn" onclick="deleteTaskFromModal()" class="hidden text-sm font-semibold text-red-600 hover:text-red-700 flex items-center gap-1 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">delete</span> Delete Task
                    </button>
                    <div class="flex gap-sm ml-auto">
                        <button type="button" onclick="closeTaskModal()" class="px-md py-sm rounded-lg border border-outline-variant font-label-md text-label-md text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" id="modal-submit-btn" class="px-lg py-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:bg-primary-container transition-colors shadow-sm cursor-pointer">Save Task</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentProjectId = null;
        let currentPriorityFilter = 'All';

        const escapeHtml = str => String(str || '').replace(/[&<>'"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[c]));

        function autoScrollColumn(ev, colContainer) {
            const rect = colContainer.getBoundingClientRect();
            const threshold = 80;
            const pointerY = ev.clientY;

            if (pointerY < rect.top + threshold) {
                colContainer.scrollTop = Math.max(0, colContainer.scrollTop - 24);
            } else if (pointerY > rect.bottom - threshold) {
                colContainer.scrollTop = Math.min(colContainer.scrollHeight, colContainer.scrollTop + 24);
            }
        }

        function allowDrop(ev, colId) {
            ev.preventDefault();
            const colContainer = document.getElementById(colId + '-col');
            if (colContainer) {
                colContainer.classList.add('drag-target-active');
                autoScrollColumn(ev, colContainer);
            }
        }

        function removeDragHighlight(colId) {
            const colContainer = document.getElementById(colId + '-col');
            if (colContainer) colContainer.classList.remove('drag-target-active');
        }

        function drag(ev) {
            const taskCard = ev.target.closest('.task-card');
            const taskId = taskCard ? taskCard.dataset.id : ev.target.id;
            if (taskId) {
                ev.dataTransfer.setData("text", taskId);
                ev.dataTransfer.effectAllowed = 'move';
            }
        }

        async function drop(ev, colId) {
            ev.preventDefault();
            removeDragHighlight(colId);
            const taskId = ev.dataTransfer.getData("text");
            if (taskId) {
                await moveTask(taskId, colId);
            }
        }

        async function moveTask(taskId, targetColId) {
            if (!window.ProjectStore) return;
            if (!currentProjectId) {
                currentProjectId = ProjectStore.getActiveProjectId();
            }
            if (!currentProjectId) return;

            await ProjectStore.moveTask(currentProjectId, taskId, targetColId);
            renderKanban();
        }

        function switchProject(selectedId) {
            if (!selectedId) return;
            currentProjectId = selectedId;
            if (window.ProjectStore) {
                ProjectStore.setActiveProjectId(selectedId);
            }
            if (history.replaceState) {
                const url = new URL(location);
                url.searchParams.set('project', selectedId);
                history.replaceState(null, '', url);
            }
            renderKanban();
        }

        function renderKanban() {
            if (!window.ProjectStore) return;

            const projects = ProjectStore.projects();
            if (!projects.length) {
                const titleElem = document.getElementById('project-title-display');
                const descElem = document.getElementById('project-desc-display');
                if (titleElem) {
                    titleElem.removeAttribute('data-i18n');
                    titleElem.textContent = 'No Projects Found';
                }
                if (descElem) {
                    descElem.removeAttribute('data-i18n');
                    descElem.textContent = 'Please create a project in the Projects page first.';
                }
                return;
            }

            if (!currentProjectId || !projects.some(p => String(p.id) === String(currentProjectId))) {
                currentProjectId = ProjectStore.getActiveProjectId() || projects[0].id;
            }

            const project = ProjectStore.get(currentProjectId) || projects[0];
            currentProjectId = project.id;

            const selector = document.getElementById('project-selector');
            if (selector) {
                selector.innerHTML = projects.map(p => `
                    <option value="${p.id}" ${String(p.id) === String(currentProjectId) ? 'selected' : ''}>${escapeHtml(p.name)}</option>
                `).join('');
            }

            const titleElem = document.getElementById('project-title-display');
            const descElem = document.getElementById('project-desc-display');
            const pillElem = document.getElementById('project-task-pill');
            if (titleElem) {
                titleElem.removeAttribute('data-i18n');
                titleElem.textContent = project.name;
            }
            if (descElem) {
                descElem.removeAttribute('data-i18n');
                descElem.textContent = project.description || 'Sprint task management board';
            }
            if (pillElem) {
                pillElem.textContent = `${(project.tasks || []).length} tasks`;
            }

            ['todo', 'inprogress', 'underreview', 'done'].forEach(col => {
                const colElem = document.getElementById(col + '-col');
                if (colElem) colElem.innerHTML = '';
            });

            const tasks = project.tasks || [];
            const counts = { todo: 0, inprogress: 0, underreview: 0, done: 0 };

            tasks.forEach(t => {
                const status = ProjectStore.normalizeStatus ? ProjectStore.normalizeStatus(t.status) : t.status;
                if (counts[status] !== undefined) counts[status]++;

                const card = createCardElement(t, status);
                const targetCol = document.getElementById(status + '-col');
                if (targetCol) targetCol.appendChild(card);
            });

            ['todo', 'inprogress', 'underreview', 'done'].forEach(col => {
                const colElem = document.getElementById(col + '-col');
                if (colElem && counts[col] === 0) {
                    const label = col === 'todo' ? 'To Do' : col === 'inprogress' ? 'In Progress' : col === 'underreview' ? 'Under Review' : 'Done';
                    colElem.innerHTML = `<div class="rounded-xl border border-dashed border-outline-variant p-6 text-center text-xs text-on-surface-variant/60 empty-state">No tasks in ${label}</div>`;
                }
            });

            ['todo', 'inprogress', 'underreview', 'done'].forEach(col => {
                const countElem = document.getElementById(col + '-count');
                if (countElem) countElem.textContent = counts[col];
            });

            filterKanban();
            if (window.refreshLanguage) window.refreshLanguage();
        }

        function createCardElement(task, status) {
            const priority = task.priority || 'Medium';
            let badgeClass = 'bg-tertiary-fixed text-on-tertiary-fixed';
            let priorityIcon = '';

            if (priority === 'High') {
                badgeClass = 'bg-error-container text-on-error-container';
                priorityIcon = '<span class="material-symbols-outlined text-[14px]">local_fire_department</span>';
            } else if (priority === 'Low') {
                badgeClass = 'bg-surface-container-high text-on-surface-variant';
            }

            let actionHtml = '';
            if (status === 'todo') {
                actionHtml = `
                    <div class="flex justify-end pt-2 border-t border-outline-variant/30 mt-2">
                        <button type="button" onclick="event.stopPropagation(); moveTask('${task.id}', 'inprogress')" class="px-2.5 py-1 bg-surface-container-high rounded text-[11px] font-semibold hover:bg-primary hover:text-white transition-colors flex items-center gap-1 cursor-pointer">
                            Start Task <span class="material-symbols-outlined text-[12px]">arrow_forward</span>
                        </button>
                    </div>
                `;
            } else if (status === 'inprogress') {
                actionHtml = `
                    <div class="flex justify-between items-center pt-2 border-t border-outline-variant/30 mt-2">
                        <button type="button" onclick="event.stopPropagation(); moveTask('${task.id}', 'todo')" class="px-2 py-1 bg-surface-container-high rounded text-[11px] hover:bg-slate-300 transition-colors cursor-pointer">
                            Move Back
                        </button>
                        <button type="button" onclick="event.stopPropagation(); moveTask('${task.id}', 'underreview')" class="px-2.5 py-1 bg-primary text-white rounded text-[11px] font-semibold hover:bg-primary-container transition-colors shadow-xs flex items-center gap-1 cursor-pointer">
                            Submit Review <span class="material-symbols-outlined text-[12px]">arrow_forward</span>
                        </button>
                    </div>
                `;
            } else if (status === 'underreview') {
                actionHtml = `
                    <div class="flex justify-between items-center pt-2 border-t border-outline-variant/30 mt-2">
                        <button type="button" onclick="event.stopPropagation(); moveTask('${task.id}', 'inprogress')" class="px-2 py-1 bg-surface-container-high rounded text-[11px] hover:bg-slate-300 transition-colors cursor-pointer">
                            Request Changes
                        </button>
                        <button type="button" onclick="event.stopPropagation(); moveTask('${task.id}', 'done')" class="px-2.5 py-1 bg-green-600 text-white rounded text-[11px] font-semibold hover:bg-green-700 transition-colors shadow-xs flex items-center gap-1 cursor-pointer">
                            Approve & Done <span class="material-symbols-outlined text-[12px]">check</span>
                        </button>
                    </div>
                `;
            } else if (status === 'done') {
                actionHtml = `
                    <div class="flex justify-between items-center pt-2 border-t border-outline-variant/30 mt-2 text-xs text-green-700 font-semibold">
                        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">task_alt</span> Completed</span>
                        <button type="button" onclick="event.stopPropagation(); moveTask('${task.id}', 'inprogress')" class="text-[11px] text-on-surface-variant hover:text-primary hover:underline cursor-pointer">Reopen</button>
                    </div>
                `;
            }

            const card = document.createElement('div');
            card.id = task.id;
            card.dataset.id = task.id;
            card.dataset.priority = priority;
            card.draggable = true;
            card.ondragstart = drag;
            card.onclick = () => openTaskModal(task.id);
            card.className = "task-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-md hover-card-shadow transition-all duration-200 cursor-pointer group flex flex-col justify-between";

            card.innerHTML = `
                <div>
                    <div class="flex justify-between items-start mb-sm">
                        <span class="${badgeClass} px-sm py-xs rounded-full font-label-sm text-label-sm flex items-center gap-xs">
                            ${priorityIcon} ${priority}
                        </span>
                        <div class="flex items-center gap-1.5">
                            ${task.due_date ? `<span class="text-[11px] text-on-surface-variant flex items-center gap-0.5"><span class="material-symbols-outlined text-[13px]">event</span>${task.due_date}</span>` : ''}
                            <button type="button" title="Hapus task" onclick="event.stopPropagation(); quickDeleteTask('${task.id}')" class="opacity-0 group-hover:opacity-100 p-1 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all cursor-pointer">
                                <span class="material-symbols-outlined text-[16px]">delete</span>
                            </button>
                        </div>
                    </div>
                    <h4 class="font-headline-md text-headline-md text-on-surface mb-xs font-bold task-title leading-snug">${escapeHtml(task.title)}</h4>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mb-md line-clamp-2 task-desc">${escapeHtml(task.description || 'No description provided.')}</p>
                    <div class="flex items-center justify-between text-xs text-on-surface-variant mb-1">
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">person</span>
                            ${escapeHtml(task.assignee || 'Alex Doe')}
                        </span>
                        <span class="text-[10px] text-slate-400 group-hover:text-primary transition-colors font-medium">Click to edit</span>
                    </div>
                </div>
                ${actionHtml}
            `;

            return card;
        }

        async function quickDeleteTask(taskId) {
            if (!taskId || !window.ProjectStore) return;
            const confirmed = await showConfirmModal({
                title: 'Hapus Task',
                message: 'Apakah Anda yakin ingin menghapus tugas ini dari papan Kanban?',
                confirmText: 'Ya, Hapus',
                type: 'danger'
            });
            if (confirmed) {
                await ProjectStore.deleteTask(currentProjectId, taskId);
                renderKanban();
            }
        }

        function filterKanban() {
            const query = (document.getElementById('kanban-search')?.value || '').toLowerCase().trim();
            document.querySelectorAll('.task-card').forEach(card => {
                const title = (card.querySelector('.task-title')?.textContent || '').toLowerCase();
                const desc = (card.querySelector('.task-desc')?.textContent || '').toLowerCase();
                const priority = card.dataset.priority || 'Medium';

                const matchesQuery = !query || title.includes(query) || desc.includes(query);
                const matchesPriority = currentPriorityFilter === 'All' || priority === currentPriorityFilter;

                card.style.display = (matchesQuery && matchesPriority) ? 'flex' : 'none';
            });
        }

        function setPriorityFilter(priority) {
            currentPriorityFilter = priority;
            ['All', 'High', 'Medium', 'Low'].forEach(p => {
                const btn = document.getElementById('filter-' + p.toLowerCase());
                if (btn) {
                    if (p === priority) {
                        btn.className = 'px-2.5 py-1 rounded-md font-semibold bg-white text-primary shadow-sm cursor-pointer';
                    } else {
                        btn.className = 'px-2.5 py-1 rounded-md text-on-surface-variant hover:text-primary cursor-pointer';
                    }
                }
            });
            filterKanban();
        }

        let registeredUsersList = [];

        async function loadRegisteredUsers() {
            try {
                const res = await fetch('projects.php?action=users');
                if (res.ok) {
                    registeredUsersList = await res.json();
                }
            } catch (e) {
                console.warn('Gagal memuat list user:', e);
            }
        }

        function renderAssigneeOptions(selectedAssignee = '') {
            const assigneeSelect = document.getElementById('modal-task-assignee');
            if (!assigneeSelect) return;

            const namesSet = new Set();
            const options = [];

            // 1. From database users (excluding superadmin)
            if (Array.isArray(registeredUsersList)) {
                registeredUsersList.forEach(u => {
                    if (u.username && !namesSet.has(u.username)) {
                        namesSet.add(u.username);
                        const roleLabel = u.role ? ` (${u.role})` : '';
                        options.push({ value: u.username, label: `${u.username}${roleLabel}` });
                    }
                });
            }

            // 2. From InternStore if available
            if (window.InternStore && typeof InternStore.list === 'function') {
                const interns = InternStore.list();
                interns.forEach(i => {
                    if (i.name && !namesSet.has(i.name)) {
                        namesSet.add(i.name);
                        options.push({ value: i.name, label: `${i.name} (Intern)` });
                    }
                });
            }

            // 3. Preserve current selected assignee if not listed yet
            if (selectedAssignee && !namesSet.has(selectedAssignee)) {
                namesSet.add(selectedAssignee);
                options.push({ value: selectedAssignee, label: `${selectedAssignee}` });
            }

            let html = '<option value="">-- Pilih Assignee --</option>';
            options.forEach(opt => {
                const isSelected = String(opt.value) === String(selectedAssignee) ? 'selected' : '';
                html += `<option value="${escapeHtml(opt.value)}" ${isSelected}>${escapeHtml(opt.label)}</option>`;
            });

            assigneeSelect.innerHTML = html;
            if (selectedAssignee) {
                assigneeSelect.value = selectedAssignee;
            }
        }

        function openTaskModal(taskId = null, defaultStatus = 'todo') {
            const modal = document.getElementById('modal');
            if (!modal) return;

            if (!currentProjectId && window.ProjectStore) {
                currentProjectId = ProjectStore.getActiveProjectId();
            }

            const titleInput = document.getElementById('modal-task-title');
            const descInput = document.getElementById('modal-task-desc');
            const priorityInput = document.getElementById('modal-task-priority');
            const statusInput = document.getElementById('modal-task-status');
            const dueDateInput = document.getElementById('modal-task-due-date');
            const idInput = document.getElementById('modal-task-id');
            const modalTitle = document.getElementById('modal-title');
            const deleteBtn = document.getElementById('modal-delete-btn');

            let currentAssignee = '';

            if (taskId) {
                const project = window.ProjectStore ? ProjectStore.get(currentProjectId) : null;
                const task = project?.tasks?.find(t => String(t.id) === String(taskId));
                if (!task) return;

                modalTitle.textContent = 'Edit Task';
                idInput.value = task.id;
                titleInput.value = task.title || '';
                descInput.value = task.description || '';
                priorityInput.value = task.priority || 'Medium';
                statusInput.value = ProjectStore.normalizeStatus ? ProjectStore.normalizeStatus(task.status) : task.status;
                currentAssignee = task.assignee || '';
                dueDateInput.value = task.due_date || '';
                if (deleteBtn) deleteBtn.classList.remove('hidden');
            } else {
                modalTitle.textContent = 'New Task';
                idInput.value = '';
                titleInput.value = '';
                descInput.value = '';
                priorityInput.value = 'Medium';
                statusInput.value = defaultStatus || 'todo';
                currentAssignee = '';
                dueDateInput.value = '';
                if (deleteBtn) deleteBtn.classList.add('hidden');
            }

            renderAssigneeOptions(currentAssignee);

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => titleInput.focus(), 50);
        }

        function closeTaskModal() {
            const modal = document.getElementById('modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        async function saveTaskModal(e) {
            e.preventDefault();
            if (!window.ProjectStore) {
                alert('ProjectStore belum dimuat.');
                return;
            }

            if (!currentProjectId) {
                currentProjectId = ProjectStore.getActiveProjectId();
            }

            const id = document.getElementById('modal-task-id').value;
            const title = document.getElementById('modal-task-title').value.trim();
            const desc = document.getElementById('modal-task-desc').value.trim();
            const priority = document.getElementById('modal-task-priority').value;
            const status = document.getElementById('modal-task-status').value;
            const assignee = document.getElementById('modal-task-assignee').value.trim() || '';
            const due_date = document.getElementById('modal-task-due-date').value;

            if (!title) {
                document.getElementById('modal-task-title').focus();
                return;
            }

            const payload = { title, description: desc, priority, status, assignee, due_date };

            if (id) {
                await ProjectStore.updateTask(currentProjectId, id, payload);
            } else {
                await ProjectStore.createTask(currentProjectId, payload);
            }

            closeTaskModal();
            renderKanban();
        }

        async function deleteTaskFromModal() {
            const id = document.getElementById('modal-task-id').value;
            if (!id || !window.ProjectStore) return;

            const confirmed = await showConfirmModal({
                title: 'Hapus Task',
                message: 'Apakah Anda yakin ingin menghapus tugas ini dari papan Kanban?',
                confirmText: 'Ya, Hapus',
                type: 'danger'
            });

            if (confirmed) {
                await ProjectStore.deleteTask(currentProjectId, id);
                closeTaskModal();
                renderKanban();
            }
        }

        document.getElementById('modal')?.addEventListener('click', function (e) {
            if (e.target === this) closeTaskModal();
        });

        document.addEventListener('DOMContentLoaded', async () => {
            await loadRegisteredUsers();

            if (window.ProjectStore) {
                await ProjectStore.init();
            }

            const params = new URLSearchParams(location.search);
            const queryProject = params.get('project');
            const allProjects = window.ProjectStore ? ProjectStore.projects() : [];

            if (queryProject && window.ProjectStore && ProjectStore.get(queryProject)) {
                currentProjectId = String(queryProject);
                ProjectStore.setActiveProjectId(queryProject);
            } else if (
                window.ProjectStore &&
                ProjectStore.getActiveProjectId() &&
                ProjectStore.get(ProjectStore.getActiveProjectId())
            ) {
                currentProjectId = String(ProjectStore.getActiveProjectId());
            } else if (allProjects.length > 0) {
                currentProjectId = String(allProjects[0].id);
                ProjectStore.setActiveProjectId(currentProjectId);
            }

            renderKanban();
        });
    </script>
    <?php include 'partials/confirm-modal.php'; ?>
</body>

</html>