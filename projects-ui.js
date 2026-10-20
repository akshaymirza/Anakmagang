const app = document.getElementById('app'), modal = document.getElementById('modal'), toast = document.getElementById('toast');
const params = new URLSearchParams(location.search), projectId = params.get('project');
const escape = value => String(value || '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[char]));

function notify(message) {
  if (!toast) return;
  toast.textContent = message;
  toast.classList.remove('hidden');
  setTimeout(() => toast.classList.add('hidden'), 2800);
}

function closeModal() {
  if (!modal) return;
  modal.classList.add('hidden');
  modal.innerHTML = '';
}

function formModal(title, body, onSubmit) {
  if (!modal) return;
  modal.innerHTML = `
    <form id="editor" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl animate-in fade-in zoom-in duration-200">
      <div class="mb-5 flex items-center justify-between border-b border-line pb-3">
        <h2 class="font-geist text-xl font-bold text-primary">${title}</h2>
        <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-700 transition-colors">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      ${body}
      <div class="mt-6 flex justify-end gap-3 border-t border-line pt-4">
        <button type="button" onclick="closeModal()" class="rounded-lg border border-line px-4 py-2 font-semibold text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
        <button type="submit" class="rounded-lg bg-primary px-5 py-2 font-semibold text-white hover:bg-primary-container transition-colors shadow-sm">Simpan</button>
      </div>
    </form>
  `;
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  document.getElementById('editor').onsubmit = async event => {
    event.preventDefault();
    await onSubmit(new FormData(event.currentTarget));
  };
}

function projectForm(project = {}) {
  const nameVal = project.title || project.name || '';
  return `
    <label class="block text-sm font-semibold text-slate-700">Nama Project
      <input required name="name" value="${escape(nameVal)}" placeholder="Contoh: Website Anak Magang" class="mt-1 w-full rounded-lg border border-line px-3 py-2 font-normal focus:outline-none focus:ring-2 focus:ring-primary" maxlength="80">
    </label>
    <label class="mt-4 block text-sm font-semibold text-slate-700">Deskripsi
      <textarea name="description" placeholder="Jelaskan tujuan dan cakupan project..." class="mt-1 min-h-24 w-full rounded-lg border border-line px-3 py-2 font-normal focus:outline-none focus:ring-2 focus:ring-primary" maxlength="500">${escape(project.description)}</textarea>
    </label>
  `;
}

let currentSearchQuery = '';

function getSearchQuery(overrideQuery) {
  if (overrideQuery !== undefined && overrideQuery !== null && overrideQuery !== '') {
    return String(overrideQuery).toLowerCase().trim();
  }
  const input = document.getElementById('proj-search-input');
  if (input && input.value !== undefined && input.value !== null) {
    return String(input.value).toLowerCase().trim();
  }
  return String(currentSearchQuery || '').toLowerCase().trim();
}

function renderProjects(filterQuery) {
  const allProjects = ProjectStore.projects() || [];
  const q = getSearchQuery(filterQuery);

  const projects = q ? allProjects.filter(p => {
    const title = String(p.title || p.name || '').toLowerCase();
    const desc = String(p.description || '').toLowerCase();
    const tasks = p.tasks || [];
    const hasMatchingTask = tasks.some(t =>
      String(t.title || '').toLowerCase().includes(q) ||
      String(t.description || '').toLowerCase().includes(q) ||
      String(t.assignee || '').toLowerCase().includes(q) ||
      String(t.status || '').toLowerCase().includes(q) ||
      String(t.priority || '').toLowerCase().includes(q)
    );
    return title.includes(q) || desc.includes(q) || hasMatchingTask;
  }) : allProjects;

  const activeProjectId = ProjectStore.getActiveProjectId();
  const kanbanLink = activeProjectId ? `tasks.php?project=${encodeURIComponent(activeProjectId)}` : 'tasks.php';

  app.innerHTML = `
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h2 class="font-geist text-3xl font-bold text-on-surface">Daftar Project</h2>
        <p class="mt-1.5 text-slate-600">Kelola project dan pekerjaan tim dalam satu tempat yang terhubung langsung dengan Active Sprint Kanban.</p>
      </div>
      <div class="flex items-center gap-3 shrink-0">
        <a href="${kanbanLink}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-outline-variant bg-white px-4 py-2.5 text-sm font-semibold text-primary hover:bg-surface-container-high transition-colors shadow-xs">
          <span class="material-symbols-outlined text-[20px]">view_kanban</span>
          Lihat Papan Kanban
        </a>
        <button onclick="newProject()" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-container transition-colors shadow-xs">
          <span class="material-symbols-outlined text-[20px]">add</span>
          Tambah Project
        </button>
      </div>
    </div>
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
      ${projects.length ? projects.map(p => {
        const title = p.title || p.name || 'Untitled';
        const prog = ProjectStore.getProgress ? ProjectStore.getProgress(p) : { percent: 0, done: 0, total: (p.tasks || []).length };
        const percent = prog.percent;
        const completedTasks = prog.done;
        const totalTasks = prog.total;

        let badgeColor = 'bg-slate-100 text-slate-700 border-slate-200';
        let barColor = 'bg-primary';
        if (percent === 100) {
          badgeColor = 'bg-emerald-50 text-emerald-700 border-emerald-200';
          barColor = 'bg-emerald-500';
        } else if (percent > 0) {
          badgeColor = 'bg-blue-50 text-blue-700 border-blue-200';
        }

        return `
          <article class="card rounded-2xl border border-line bg-white p-5 hover-card-shadow flex flex-col justify-between">
            <div>
              <div class="flex items-start justify-between">
                <span class="material-symbols-outlined rounded-xl bg-blue-100 p-3 text-primary">folder</span>
                <div class="flex items-center gap-2">
                  <span class="rounded-full border px-2.5 py-1 text-xs font-bold ${badgeColor} flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">${percent === 100 ? 'check_circle' : 'timelapse'}</span>
                    <span>${percent}%</span>
                  </span>
                </div>
              </div>
              <h3 class="mt-4 font-geist text-xl font-bold text-on-surface">${escape(title)}</h3>
              <p class="mt-2 min-h-12 text-sm text-slate-600">${escape(p.description || 'Belum ada deskripsi project.')}</p>

              <!-- Progress Bar & Persentase -->
              <div class="mt-4 pt-3 border-t border-slate-100 space-y-1.5">
                <div class="flex items-center justify-between text-xs font-semibold">
                  <span class="text-slate-500 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[15px] text-primary">analytics</span>
                    Progress Selesai
                  </span>
                  <span class="${percent === 100 ? 'text-emerald-700 font-extrabold' : 'text-slate-800 font-bold'}">${percent}% (${completedTasks}/${totalTasks} selesai)</span>
                </div>
                <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden p-0.5">
                  <div class="${barColor} h-full rounded-full transition-all duration-500" style="width: ${percent}%"></div>
                </div>
              </div>

              <p class="mt-3 text-xs text-slate-400">Dibuat tanggal ${new Date(p.created_at || Date.now()).toLocaleDateString('id-ID')}</p>
            </div>
            <div class="mt-5 flex items-center gap-2 border-t border-line pt-4">
              <a href="tasks.php?project=${encodeURIComponent(p.id)}" onclick="ProjectStore.setActiveProjectId('${p.id}')" class="flex-1 h-9 rounded-lg bg-primary px-3 text-sm font-bold text-white hover:bg-primary-container transition-colors flex items-center justify-center gap-1.5 shadow-xs whitespace-nowrap">
                <span class="material-symbols-outlined text-[18px]">view_kanban</span>
                <span>Buka Kanban</span>
              </a>
              <a href="projects.php?project=${encodeURIComponent(p.id)}" class="h-9 w-9 rounded-lg border border-line text-primary hover:bg-slate-50 flex items-center justify-center transition-colors shrink-0" title="Kelola Project">
                <span class="material-symbols-outlined text-[18px]">folder_open</span>
              </a>
              <button onclick="editProject('${p.id}')" aria-label="Edit" class="h-9 w-9 rounded-lg border border-line text-primary hover:bg-slate-50 flex items-center justify-center transition-colors shrink-0">
                <span class="material-symbols-outlined text-[18px]">edit</span>
              </button>
              <button onclick="removeProject('${p.id}')" aria-label="Hapus" class="h-9 w-9 rounded-lg border border-line text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors shrink-0">
                <span class="material-symbols-outlined text-[18px]">delete</span>
              </button>
            </div>
          </article>
        `;
      }).join('') : `
        <div class="col-span-full rounded-2xl border border-dashed border-line bg-white p-14 text-center">
          <span class="material-symbols-outlined text-4xl text-primary">${q ? 'search_off' : 'folder_off'}</span>
          <h3 class="mt-3 font-geist text-xl font-bold">${q ? 'Project tidak ditemukan' : 'Belum ada project'}</h3>
          <p class="mt-2 text-slate-600">${q ? `Tidak ada project yang cocok dengan kata kunci "${escape(q)}".` : 'Buat project pertama Anda untuk disimpan ke database MySQL.'}</p>
        </div>
      `}
    </div>
  `;
}

function filterProjectSearch(query) {
  const input = document.getElementById('proj-search-input');
  currentSearchQuery = query !== undefined ? query : (input ? input.value : '');
  if (projectId) {
    renderBoard(currentSearchQuery);
  } else {
    renderProjects(currentSearchQuery);
  }
}
window.filterProjectSearch = filterProjectSearch;

function renderBoard(filterQuery) {
  const project = ProjectStore.get(projectId);
  if (!project) {
    location.href = 'projects.php';
    return;
  }
  const title = project.title || project.name || 'Untitled';
  const q = getSearchQuery(filterQuery);
  const allTasks = project.tasks || [];
  const tasks = q ? allTasks.filter(t =>
    String(t.title || '').toLowerCase().includes(q) ||
    String(t.description || '').toLowerCase().includes(q) ||
    String(t.assignee || '').toLowerCase().includes(q) ||
    String(t.priority || '').toLowerCase().includes(q) ||
    String(t.status || '').toLowerCase().includes(q)
  ) : allTasks;

  const prog = ProjectStore.getProgress ? ProjectStore.getProgress(project) : { percent: 0, done: 0, total: allTasks.length, inprogress: 0, todo: 0 };
  const percent = prog.percent;
  const completedTasks = prog.done;
  const inProgressTasks = prog.inprogress;
  const todoTasks = prog.todo;
  const totalTasks = prog.total;

  app.innerHTML = `
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <a href="projects.php" class="inline-flex items-center gap-1 text-sm font-semibold text-primary hover:underline mb-2">
          <span class="material-symbols-outlined text-lg">arrow_back</span>
          Semua Project
        </a>
        <h2 class="font-geist text-3xl font-bold text-on-surface">${escape(title)}</h2>
        <p class="mt-1 text-slate-600">${escape(project.description || 'Project terhubung ke MySQL.')}</p>
      </div>
      <div class="flex items-center gap-3 shrink-0">
        <a href="tasks.php?project=${encodeURIComponent(project.id)}" onclick="ProjectStore.setActiveProjectId('${project.id}')" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-primary px-3.5 py-2 text-xs font-bold text-white hover:bg-primary-container transition-colors shadow-xs">
          <span class="material-symbols-outlined text-[18px]">view_kanban</span>
          Buka Papan Kanban
        </a>
      </div>
    </div>

    <!-- Project Progress Summary Card -->
    <div class="mb-6 rounded-2xl border border-line bg-white p-5 shadow-xs">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-3">
        <div>
          <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">
            <span class="material-symbols-outlined text-primary text-[18px]">trending_up</span>
            <span>Persentase Progress Proyek</span>
          </div>
          <div class="flex items-baseline gap-2">
            <span class="font-geist text-3xl font-extrabold ${percent === 100 ? 'text-emerald-600' : 'text-slate-900'}">${percent}%</span>
            <span class="text-xs text-slate-500 font-medium">(${completedTasks} dari ${totalTasks} tugas telah selesai)</span>
          </div>
        </div>
        <!-- Mini Stats Grid -->
        <div class="flex items-center gap-2 flex-wrap">
          <div class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-center min-w-[70px]">
            <span class="block text-[10px] font-bold text-slate-400 uppercase">To Do</span>
            <span class="text-sm font-bold text-slate-700">${todoTasks}</span>
          </div>
          <div class="px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-200 text-center min-w-[70px]">
            <span class="block text-[10px] font-bold text-blue-500 uppercase">Proses</span>
            <span class="text-sm font-bold text-blue-700">${inProgressTasks}</span>
          </div>
          <div class="px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-center min-w-[70px]">
            <span class="block text-[10px] font-bold text-emerald-500 uppercase">Selesai</span>
            <span class="text-sm font-bold text-emerald-700">${completedTasks}</span>
          </div>
        </div>
      </div>
      <!-- Progress Bar -->
      <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden p-0.5">
        <div class="${percent === 100 ? 'bg-emerald-500' : 'bg-primary'} h-full rounded-full transition-all duration-500" style="width: ${percent}%"></div>
      </div>
    </div>

    <!-- Task List for this Project -->
    <div class="rounded-2xl border border-line bg-white p-6 shadow-xs">
      <div class="flex items-center justify-between mb-4 border-b border-line pb-3">
        <h3 class="font-geist text-lg font-bold text-slate-900 flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">task</span>
          Daftar Tugas Project (${tasks.length}/${allTasks.length})
        </h3>
        <span class="rounded-full ${percent === 100 ? 'bg-emerald-100 text-emerald-700' : 'bg-green-100 text-green-700'} px-3 py-1 text-xs font-bold">${percent}% Selesai (${completedTasks}/${totalTasks})</span>
      </div>

      ${tasks.length ? `
        <div class="grid gap-4 md:grid-cols-2">
          ${tasks.map(t => {
            let statusBadge = '';
            const status = (t.status || 'todo').toLowerCase();
            if (status === 'done') {
              statusBadge = '<span class="px-2.5 py-0.5 bg-green-100 text-green-800 text-xs font-bold rounded-full">Done</span>';
            } else if (status === 'inprogress') {
              statusBadge = '<span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 text-xs font-bold rounded-full">In Progress</span>';
            } else if (status === 'underreview') {
              statusBadge = '<span class="px-2.5 py-0.5 bg-purple-100 text-purple-800 text-xs font-bold rounded-full">Under Review</span>';
            } else {
              statusBadge = '<span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 text-xs font-bold rounded-full">To Do</span>';
            }

            return `
              <div class="p-4 rounded-xl border border-line bg-surface-bright flex flex-col justify-between">
                <div>
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase text-slate-400">Priority: ${escape(t.priority || 'Medium')}</span>
                    ${statusBadge}
                  </div>
                  <h4 class="font-bold text-slate-900 text-base mb-1">${escape(t.title)}</h4>
                  <p class="text-xs text-slate-600 line-clamp-2 mb-3">${escape(t.description || 'Tidak ada deskripsi.')}</p>
                </div>
                <div class="pt-2 border-t border-line flex items-center justify-between text-xs text-slate-500">
                  <span>Assignee: <strong class="text-slate-700">${escape(t.assignee || 'Belum diassign')}</strong></span>
                  <a href="tasks.php?project=${encodeURIComponent(project.id)}" class="text-primary font-bold hover:underline flex items-center gap-1">
                    Buka di Kanban <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                  </a>
                </div>
              </div>
            `;
          }).join('')}
        </div>
      ` : `
        <div class="rounded-xl border border-dashed border-line p-8 text-center text-slate-500">
          <span class="material-symbols-outlined text-3xl text-slate-400 mb-1">search_off</span>
          <p class="text-sm font-semibold">${q ? `Tugas tidak ditemukan untuk kata kunci "${escape(q)}"` : 'Belum ada tugas pada project ini.'}</p>
        </div>
      `}
    </div>
  `;
}

function render() {
  projectId ? renderBoard(currentSearchQuery) : renderProjects(currentSearchQuery);
  window.refreshLanguage?.();
}

function newProject() {
  formModal('Tambah Project', projectForm(), async data => {
    await ProjectStore.createProject(Object.fromEntries(data));
    closeModal();
    render();
    notify('Project berhasil disimpan ke database.');
  });
}

function editProject(id) {
  const project = ProjectStore.get(id);
  if (!project) return;
  formModal('Edit Project', projectForm(project), async data => {
    await ProjectStore.updateProject(id, Object.fromEntries(data));
    closeModal();
    render();
    notify('Project diperbarui di database.');
  });
}

async function removeProject(id) {
  let confirmed = false;
  if (typeof window.showConfirmModal === 'function') {
    confirmed = await window.showConfirmModal({
      title: 'Hapus Project',
      message: 'Apakah Anda yakin ingin menghapus project ini dari database?',
      confirmText: 'Ya, Hapus',
      cancelText: 'Batal',
      type: 'danger'
    });
  } else {
    confirmed = confirm('Hapus project ini dari database?');
  }

  if (confirmed) {
    await ProjectStore.deleteProject(id);
    render();
    notify('Project berhasil dihapus.');
  }
}

// Inisialisasi utama: Ambil data dari API PHP terlebih dahulu baru render
(async function initApp() {
  app.innerHTML = '<div class="p-8 text-center text-slate-500">Memuat data dari database...</div>';
  await ProjectStore.init();
  render();

  const searchInput = document.getElementById('proj-search-input');
  if (searchInput) {
    ['input', 'keyup', 'change', 'search'].forEach(evt => {
      searchInput.addEventListener(evt, (e) => {
        window.filterProjectSearch(e.target.value);
      });
    });
    if (searchInput.value) {
      window.filterProjectSearch(searchInput.value);
    }
  }
})();