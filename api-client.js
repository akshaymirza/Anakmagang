/* InternSpace – API Client Abstraction Layer
   Provides a unified interface for UI components. Uses LocalStorage / ProjectStore
   by default, and easily switches to REST endpoints when USE_BACKEND_API is enabled.
*/
const ApiClient = (() => {
  const CONFIG = {
    USE_BACKEND_API: false, // Set to true when backend REST API server is running
    BASE_URL: 'http://localhost:3000/api/v1',
    HEADERS: { 'Content-Type': 'application/json' }
  };

  async function request(endpoint, options = {}) {
    if (!CONFIG.USE_BACKEND_API) {
      throw new Error('Backend API mode disabled. Falling back to local store.');
    }
    const token = localStorage.getItem('internspace-auth-token');
    const headers = { ...CONFIG.HEADERS, ...(token ? { Authorization: `Bearer ${token}` } : {}) };
    const response = await fetch(`${CONFIG.BASE_URL}${endpoint}`, { ...options, headers });
    if (!response.ok) {
      const err = await response.json().catch(() => ({ message: 'API request failed' }));
      throw new Error(err.message || `HTTP ${response.status}`);
    }
    return response.json();
  }

  /* ── Projects & Tasks ────────────────────────── */
  async function getProjects() {
    if (!CONFIG.USE_BACKEND_API && window.ProjectStore) {
      return Promise.resolve(window.ProjectStore.projects());
    }
    return request('/projects');
  }

  async function getProject(id) {
    if (!CONFIG.USE_BACKEND_API && window.ProjectStore) {
      return Promise.resolve(window.ProjectStore.get(id));
    }
    return request(`/projects/${id}`);
  }

  async function createProject(data) {
    if (!CONFIG.USE_BACKEND_API && window.ProjectStore) {
      return Promise.resolve(window.ProjectStore.createProject(data));
    }
    return request('/projects', { method: 'POST', body: JSON.stringify(data) });
  }

  async function createTask(projectId, taskData) {
    if (!CONFIG.USE_BACKEND_API && window.ProjectStore) {
      return Promise.resolve(window.ProjectStore.createTask(projectId, taskData));
    }
    return request(`/projects/${projectId}/tasks`, { method: 'POST', body: JSON.stringify(taskData) });
  }

  async function moveTask(projectId, taskId, status) {
    if (!CONFIG.USE_BACKEND_API && window.ProjectStore) {
      window.ProjectStore.moveTask(projectId, taskId, status);
      return Promise.resolve({ success: true, taskId, status });
    }
    return request(`/projects/${projectId}/tasks/${taskId}/status`, { method: 'PATCH', body: JSON.stringify({ status }) });
  }

  /* ── Attendance / Shift ─────────────────────── */
  async function getClockStatus() {
    const status = localStorage.getItem('internspace-clock-status') || 'active';
    const start = localStorage.getItem('internspace-clock-start');
    return Promise.resolve({ status, start: start ? parseInt(start, 10) : null });
  }

  async function setClockStatus(status) {
    localStorage.setItem('internspace-clock-status', status);
    if (status === 'active') {
      localStorage.setItem('internspace-clock-start', Date.now());
    }
    return Promise.resolve({ status, timestamp: Date.now() });
  }

  /* ── Verification ────────────────────────────── */
  async function verifyCertificate(certificateId) {
    if (!CONFIG.USE_BACKEND_API) {
      // Local mock verification
      const isValid = certificateId.toUpperCase().includes('IS-2024');
      return Promise.resolve({
        id: certificateId,
        valid: isValid,
        recipient: 'Alex Doe',
        issue_date: '2024-10-15',
        program: 'Full-Stack Software Engineering Intern'
      });
    }
    return request(`/verify/${certificateId}`);
  }

  return {
    CONFIG,
    getProjects,
    getProject,
    createProject,
    createTask,
    moveTask,
    getClockStatus,
    setClockStatus,
    verifyCertificate
  };
})();

if (typeof module !== 'undefined' && module.exports) {
  module.exports = ApiClient;
}
