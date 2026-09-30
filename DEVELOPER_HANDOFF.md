# InternSpace Management Portal – Technical Developer Handoff

Welcome to **InternSpace**, a modular, lightweight, and modern web-based internship management portal built with responsive Material Design 3 guidelines and clean JavaScript architecture.

This handoff document is crafted specifically to allow **future AI agents and human developers** to onboard rapidly, understand system contracts, add features, and connect backend services with ease.

---

## 🏛️ Architecture Overview

InternSpace is designed as a zero-build client-side web application leveraging:
- **HTML5 & Vanilla JavaScript (ES6+)**: Fast execution, zero framework lock-in, browser-native performance.
- **Tailwind CSS (CDN)**: Styled using a single source of truth configuration file ([shared-config.js](file:///e:/laragon/www/WebsiteAnakMagang/shared-config.js)).
- **Google Material Symbols & Fonts**: Geist (Headlines) and Inter (Body).
- **LocalStorage State Layer**: Fully functional offline/client-side storage layer for Kanban tasks and project management ([project-store.js](file:///e:/laragon/www/WebsiteAnakMagang/project-store.js)).
- **Bi-directional i18n Engine**: Seamless Indonesian / English language switcher ([lang.js](file:///e:/laragon/www/WebsiteAnakMagang/lang.js) and [language-ui.js](file:///e:/laragon/www/WebsiteAnakMagang/language-ui.js)).

---

## 📂 File Directory & Page Map

| File | Purpose | Core Features / Dependencies |
| :--- | :--- | :--- |
| [index.html](file:///e:/laragon/www/WebsiteAnakMagang/index.html) | Dashboard | Daily overview, live clock shift timer, dynamic `ProjectStore` stats counter, activity feed, achievement badges. |
| [projects.html](file:///e:/laragon/www/WebsiteAnakMagang/projects.html) | Project Management | Project list, create project modal, task breakdown, direct integration with `tasks.html?project=<id>` via `projects-ui.js`. |
| [tasks.html](file:///e:/laragon/www/WebsiteAnakMagang/tasks.html) | Active Sprint Kanban | 4-column dynamic drag-and-drop Kanban board, header project switcher dropdown, full CRUD task modal synced with `ProjectStore`. |
| [attendance.html](file:///e:/laragon/www/WebsiteAnakMagang/attendance.html) | Attendance & History | Attendance rate overview, weekly calendar breakdown, history table with mentor notes. |
| [applications.html](file:///e:/laragon/www/WebsiteAnakMagang/applications.html) | Internship Applications | Career center pipeline, application filter tabs (Interviewing, Under review, Offer). |
| [recruitment.html](file:///e:/laragon/www/WebsiteAnakMagang/recruitment.html) | Recruitment Portal | Public-facing job openings, application modal form, multi-step progress submission. |
| [verification.html](file:///e:/laragon/www/WebsiteAnakMagang/verification.html) | Certificate Verification | Certificate lookup by ID, interactive verification status feedback. |
| [profile.html](file:///e:/laragon/www/WebsiteAnakMagang/profile.html) | Digital Intern Profile | Intern bio, skills badges, mentor review summary, rank progress bar. |
| [settings.html](file:///e:/laragon/www/WebsiteAnakMagang/settings.html) | System Preferences | Language selection, reduced motion toggle, local data reset button. |
| [shared-config.js](file:///e:/laragon/www/WebsiteAnakMagang/shared-config.js) | Design Tokens | Single source of truth Tailwind configuration containing colors, typography, and spacing. |
| [project-store.js](file:///e:/laragon/www/WebsiteAnakMagang/project-store.js) | Data Management | LocalStorage store for projects & tasks with full CRUD operations, status normalization, and active project state. |
| [projects-ui.js](file:///e:/laragon/www/WebsiteAnakMagang/projects-ui.js) | Projects Controller | UI renderer and event handlers for `projects.html` with direct linking to `tasks.html`. |
| [lang.js](file:///e:/laragon/www/WebsiteAnakMagang/lang.js) | i18n Dictionary | Translation keys for English (`en`) and Indonesian (`id`). |
| [language-ui.js](file:///e:/laragon/www/WebsiteAnakMagang/language-ui.js) | i18n Observer | Auto-attaches `data-i18n` attributes, listens to language toggles, and re-renders UI text. |
| [performance.js](file:///e:/laragon/www/WebsiteAnakMagang/performance.js) | Optimization Utility | Image lazy-loading, reduced motion handling, device optimization. |

---

## 📊 Data Management Layer (`ProjectStore`)

`ProjectStore` is available globally window-wide when [project-store.js](file:///e:/laragon/www/WebsiteAnakMagang/project-store.js) is loaded.

### Data Schema
```json
{
  "id": "uuid-string",
  "name": "Website Anak Magang",
  "description": "Improve the intern portal experience.",
  "created_by": "Alex Doe",
  "created_at": "ISO-8601 Timestamp",
  "updated_at": "ISO-8601 Timestamp",
  "tasks": [
    {
      "id": "uuid-string",
      "project_id": "uuid-string",
      "title": "Build project dashboard",
      "description": "Create the project overview.",
      "status": "todo | inprogress | underreview | done",
      "priority": "Low | Medium | High",
      "assignee": "Alex Doe",
      "due_date": "YYYY-MM-DD",
      "created_at": "ISO-8601 Timestamp",
      "updated_at": "ISO-8601 Timestamp"
    }
  ]
}
```

### API Methods
- `ProjectStore.projects()`: Returns array of all projects (seeds default projects on first launch).
- `ProjectStore.get(id)`: Returns project by ID.
- `ProjectStore.getActiveProjectId()`: Gets currently active project ID (via URL `?project=` or localStorage).
- `ProjectStore.setActiveProjectId(id)`: Sets currently active project ID in localStorage.
- `ProjectStore.createProject({ name, description })`: Creates a new project.
- `ProjectStore.updateProject(id, { name, description })`: Updates an existing project.
- `ProjectStore.deleteProject(id)`: Deletes project.
- `ProjectStore.createTask(projectId, { title, description, status, priority, assignee, due_date })`: Creates task with normalized status.
- `ProjectStore.updateTask(projectId, taskId, updates)`: Updates task.
- `ProjectStore.deleteTask(projectId, taskId)`: Deletes task.
- `ProjectStore.moveTask(projectId, taskId, newStatus)`: Changes task status with normalization.
- `ProjectStore.getAllTasks()`: Returns aggregated tasks across all projects with project name.
- `ProjectStore.normalizeStatus(status)`: Normalizes legacy `progress`/`review` to `inprogress`/`underreview`.
- `ProjectStore.statusMatches(taskStatus, columnStatus)`: Checks status equivalence across legacy & modern tokens.

---

## 🌐 Internationalization (`I18n`) Framework

All bilingual text is handled automatically by [lang.js](file:///e:/laragon/www/WebsiteAnakMagang/lang.js) & [language-ui.js](file:///e:/laragon/www/WebsiteAnakMagang/language-ui.js).

1. **HTML Data Attribute**: To make any element auto-translatable, add `data-i18n="KEY_NAME"`.
2. **Dynamic UI Binding**: `language-ui.js` automatically indexes text nodes and translates them upon language toggle.
3. **Language Switcher & QoL**: A floating language switcher widget is rendered dynamically at bottom-left on pages loading `language-ui.js`. `language-ui.js` also injects a responsive mobile menu toggle button, handles mobile drawer navigation overlays, and listens for global keyboard shortcuts (`Ctrl+K` / `/` for search, `ESC` for modal closing).

---

## ⚡ Quality of Life (QoL) & Persistence Features

- **Persistent Clock Shift Timer**: Clock-in status and active timestamp are stored in `localStorage` under `internspace-clock-start`, `internspace-clock-status`, and `internspace-clock-accumulated`. Navigating between pages preserves shift timer state accurately.
- **Kanban Search & Priority Filters**: `tasks.html` provides live title/description filtering and priority chip buttons (`All`, `High`, `Medium`, `Low`).
- **Modal Dialog Editor**: Replaced primitive browser prompt popups with interactive modal dialogs for task creation and editing.
- **Dragover Target Highlighting**: Column containers dynamically add `.drag-target-active` highlighting when tasks are dragged over.

---

## 🎨 Design System Tokens

Defined in [shared-config.js](file:///e:/laragon/www/WebsiteAnakMagang/shared-config.js):
- **Brand Colors**:
  - `primary`: `#00236f` (Navy Blue)
  - `primary-container`: `#1e3a8a`
  - `background` / `canvas`: `#f8f9ff`
  - `surface-container-lowest`: `#ffffff`
  - `outline-variant` / `line`: `#c5c5d3`
- **Typography**:
  - Headlines: `font-headline-xl`, `font-headline-lg`, `font-headline-md` (Geist font)
  - Body: `font-body-lg`, `font-body-md`, `font-body-sm` (Inter font)

---

## 🚀 Guidelines for Future AI Agents & Developers

When making future edits or adding features:
1. **Always link `shared-config.js`** in `<head>` after Tailwind CDN script tag. Avoid writing inline `tailwind.config` blocks in HTML files.
2. **Preserve Sidebar Links**: Every main portal page should include navigation to Dashboard, Projects, Tasks, Attendance, Profile, Applications, Recruitment, Verification, and Settings.
3. **Connecting a Real REST API Backend**:
   - To replace LocalStorage with a REST backend, update `ProjectStore` methods in [project-store.js](file:///e:/laragon/www/WebsiteAnakMagang/project-store.js) to return `fetch()` promises or async data calls. The UI callers in `projects-ui.js` and `tasks.html` are decoupled from storage implementation details.
