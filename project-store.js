const ProjectStore = {
  _cache: [],
  _activeProjectId: localStorage.getItem('active_project_id') || null,

  async init() {
    try {
      // 1. Fetch Projects dari MySQL API
      const resProj = await fetch('projects.php?action=api');
      if (!resProj.ok) throw new Error('Gagal memuat projects');
      const projects = await resProj.json();

      // 2. Fetch Tasks secara terpisah agar project tetap tampil jika task gagal dimuat
      let tasks = [];
      try {
        const resTasks = await fetch('projects.php?action=tasks');
        if (resTasks.ok) {
          tasks = await resTasks.json();
        }
      } catch (e) {
        console.warn('Tabel tasks belum siap atau kosong:', e);
      }

      // 3. Gabungkan data project dan task
      this._cache = projects.map(p => ({
        ...p,
        id: String(p.id),
        name: p.title || p.name || 'Untitled Project',
        tasks: Array.isArray(tasks)
          ? tasks
              .filter(t => String(t.project_id) === String(p.id))
              .map(t => ({ ...t, id: String(t.id), project_id: String(t.project_id) }))
          : []
      }));
    } catch (err) {
      console.error('ProjectStore Init Error:', err);
      this._cache = [];
    }
  },

  projects() { 
    return this._cache; 
  },

  get(id) { 
    if (!id) return null;
    return this._cache.find(p => String(p.id) === String(id)) || null; 
  },

  getActiveProjectId() { 
    return this._activeProjectId; 
  },

  setActiveProjectId(id) {
    if (!id) return;
    this._activeProjectId = String(id);
    localStorage.setItem('active_project_id', String(id));
  },

  // CREATE Task
  async createTask(projectId, taskData) {
    try {
      await fetch('projects.php?action=tasks', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ project_id: projectId, ...taskData })
      });
      await this.init();
    } catch (err) { console.error(err); }
  },

  // UPDATE Task
  async updateTask(projectId, taskId, taskData) {
    try {
      await fetch('projects.php?action=tasks', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: taskId, ...taskData })
      });
      await this.init();
    } catch (err) { console.error(err); }
  },

  // MOVE Task (Quick Status Update)
  async moveTask(projectId, taskId, status) {
    try {
      await fetch('projects.php?action=tasks', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: taskId, status: status })
      });
      await this.init();
    } catch (err) { console.error(err); }
  },

  // DELETE Task
  async deleteTask(projectId, taskId) {
    try {
      await fetch(`projects.php?action=tasks&id=${taskId}`, { method: 'DELETE' });
      await this.init();
    } catch (err) { console.error(err); }
  },

  // CREATE Project
  async createProject(data) {
    try {
      await fetch('projects.php?action=api', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          title: data.name || data.title || '',
          description: data.description || '',
          status: data.status || 'pending'
        })
      });
      await this.init();
    } catch (err) { console.error(err); }
  },

  // UPDATE Project
  async updateProject(id, data) {
    try {
      await fetch('projects.php?action=api', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id,
          title: data.name || data.title || '',
          description: data.description || '',
          status: data.status || 'pending'
        })
      });
      await this.init();
    } catch (err) { console.error(err); }
  },

  // DELETE Project
  async deleteProject(id) {
    try {
      await fetch(`projects.php?action=api&id=${encodeURIComponent(id)}`, { method: 'DELETE' });
      await this.init();
    } catch (err) { console.error(err); }
  },

  normalizeStatus(s) {
    if (!s) return 'todo';
    const lower = String(s).toLowerCase().replace(/\s+/g, '');
    if (lower.includes('progress')) return 'inprogress';
    if (lower.includes('review')) return 'underreview';
    if (lower.includes('done')) return 'done';
    return 'todo';
  }
};

// Pastikan ProjectStore bisa diakses lewat window.ProjectStore
// (const/let di top-level tidak otomatis jadi properti window)
window.ProjectStore = ProjectStore;