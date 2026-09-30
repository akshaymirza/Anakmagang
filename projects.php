<?php 
require_once __DIR__ . '/session.php';
require_login();
$userName = current_user_name();

// --------------------------------------------------------------------------
// 1. KONEKSI DATABASE (mysqli)
// --------------------------------------------------------------------------
require_once __DIR__ . '/Login/koneksi.php';

// --------------------------------------------------------------------------
// 2. BACKEND API HANDLER (?action=api / ?action=tasks)
// --------------------------------------------------------------------------
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $method = $_SERVER['REQUEST_METHOD'];
    $userId = $_SESSION['user_id'] ?? 1;

    // =========================================================================
    // API FOR USERS / ASSIGNEES (?action=users)
    // =========================================================================
    if ($_GET['action'] === 'users' || $_GET['action'] === 'assignees') {
        if ($method === 'GET') {
            $sql = "SELECT id, username, role FROM users WHERE role NOT IN ('superadmin', 'super_admin') ORDER BY username ASC";
            $result = mysqli_query($conn, $sql);
            $users = [];
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $users[] = $row;
                }
            }
            echo json_encode($users);
            exit;
        }
    }

    // =========================================================================
    // API FOR PROJECTS (?action=api)
    // =========================================================================
    if ($_GET['action'] === 'api') {
        // READ (GET): Ambil semua data project
        if ($method === 'GET') {
            $sql = "SELECT * FROM projects ORDER BY created_at DESC";
            $result = mysqli_query($conn, $sql);
            
            $projects = [];
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $projects[] = $row;
                }
            }
            echo json_encode($projects);
            exit;
        }

        // CREATE (POST): Tambah project baru
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $title = trim($input['title'] ?? '');
            $description = trim($input['description'] ?? '');
            $status = $input['status'] ?? 'pending';

            if (empty($title)) {
                http_response_code(400);
                echo json_encode(['error' => 'Judul project wajib diisi']);
                exit;
            }

            $sql = "INSERT INTO projects (user_id, title, description, status) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "isss", $userId, $title, $description, $status);
            mysqli_stmt_execute($stmt);

            echo json_encode([
                'id' => mysqli_insert_id($conn), 
                'message' => 'Project berhasil ditambahkan'
            ]);
            exit;
        }

        // UPDATE (PUT): Update project
        if ($method === 'PUT') {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? null;
            $title = trim($input['title'] ?? '');
            $description = trim($input['description'] ?? '');
            $status = $input['status'] ?? 'pending';

            if (!$id || empty($title)) {
                http_response_code(400);
                echo json_encode(['error' => 'Data tidak lengkap']);
                exit;
            }

            $sql = "UPDATE projects SET title = ?, description = ?, status = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssi", $title, $description, $status, $id);
            mysqli_stmt_execute($stmt);

            echo json_encode(['message' => 'Project berhasil diperbarui']);
            exit;
        }

        // DELETE (DELETE): Hapus project
        if ($method === 'DELETE') {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID project tidak ditemukan']);
                exit;
            }

            $sql = "DELETE FROM projects WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);

            echo json_encode(['message' => 'Project berhasil dihapus']);
            exit;
        }
    }

    // =========================================================================
    // API FOR TASKS (?action=tasks)
    // =========================================================================
    if ($_GET['action'] === 'tasks') {
        // GET: Ambil task berdasarkan project_id
        if ($method === 'GET') {
            $projectId = $_GET['project_id'] ?? null;
            $sql = $projectId 
                ? "SELECT * FROM tasks WHERE project_id = ? ORDER BY id DESC"
                : "SELECT * FROM tasks ORDER BY id DESC";
            
            $stmt = mysqli_prepare($conn, $sql);
            if ($projectId) mysqli_stmt_bind_param($stmt, "i", $projectId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $tasks = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $tasks[] = $row;
            }
            echo json_encode($tasks);
            exit;
        }

        // POST: Tambah Task
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $projectId = $input['project_id'] ?? null;
            $title = trim($input['title'] ?? '');
            $desc = trim($input['description'] ?? '');
            $priority = $input['priority'] ?? 'Medium';
            $status = $input['status'] ?? 'todo';
            $assignee = trim($input['assignee'] ?? 'Alex Doe');
            $dueDate = !empty($input['due_date']) ? $input['due_date'] : null;

            if (!$projectId || empty($title)) {
                http_response_code(400);
                echo json_encode(['error' => 'Project ID dan Judul Task wajib diisi']);
                exit;
            }

            $sql = "INSERT INTO tasks (project_id, title, description, priority, status, assignee, due_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "issssss", $projectId, $title, $desc, $priority, $status, $assignee, $dueDate);
            mysqli_stmt_execute($stmt);

            echo json_encode(['id' => mysqli_insert_id($conn), 'message' => 'Task berhasil dibuat']);
            exit;
        }

        // PUT: Update Task / Pindah Status
        if ($method === 'PUT') {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Task ID wajib ada']);
                exit;
            }

            // Jika hanya update status (Drag and Drop / Quick Move)
            if (isset($input['status']) && count($input) <= 3) {
                $status = $input['status'];
                $sql = "UPDATE tasks SET status = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "si", $status, $id);
                mysqli_stmt_execute($stmt);
                echo json_encode(['message' => 'Status task diperbarui']);
                exit;
            }

            // Update Full Task
            $title = trim($input['title'] ?? '');
            $desc = trim($input['description'] ?? '');
            $priority = $input['priority'] ?? 'Medium';
            $status = $input['status'] ?? 'todo';
            $assignee = trim($input['assignee'] ?? 'Alex Doe');
            $dueDate = !empty($input['due_date']) ? $input['due_date'] : null;

            $sql = "UPDATE tasks SET title = ?, description = ?, priority = ?, status = ?, assignee = ?, due_date = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssi", $title, $desc, $priority, $status, $assignee, $dueDate, $id);
            mysqli_stmt_execute($stmt);

            echo json_encode(['message' => 'Task berhasil diperbarui']);
            exit;
        }

        // DELETE: Hapus Task
        if ($method === 'DELETE') {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID task tidak ditemukan']);
                exit;
            }

            $sql = "DELETE FROM tasks WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);

            echo json_encode(['message' => 'Task dihapus']);
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Kedayweb - Projects</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined:FILL@0..1" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script src="shared-config.js"></script>
  <style>
    body { font-family: Inter, sans-serif; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 500; }
    .modal { background: rgba(11, 28, 48, 0.45); }
    .card { transition: all 0.2s ease; }
    .card:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(30, 58, 138, 0.08); }
  </style>
</head>
<body class="min-h-screen bg-canvas text-slate-900">
<?php 
$active = 'projects'; 
if (is_admin()) {
    include 'partials/sidebar-admin.php';
} else {
    include 'partials/sidebar-intern.php';
}
?>
  <main class="md:ml-[16.5rem] min-h-screen flex flex-col">
    <!-- TopNavBar -->
    <header class="w-full h-20 bg-surface-container-lowest border-b border-outline-variant sticky top-0 z-10 shrink-0">
      <div class="flex justify-between items-center px-gutter w-full max-w-container-max mx-auto h-full gap-2">
        <button onclick="toggleMobileSidebar()" type="button" class="md:hidden p-2 text-on-surface hover:bg-surface-container-high rounded-lg shrink-0" aria-label="Buka Menu Sidebar">
          <span class="material-symbols-outlined text-2xl">menu</span>
        </button>
        <div class="flex-1 flex items-center gap-md">
          <!-- Page Title Badge -->
          <div class="flex items-center gap-2 pr-4 border-r border-outline-variant hidden sm:flex">
            <span class="material-symbols-outlined text-primary text-[22px]" style="font-variation-settings: 'FILL' 1;">folder_open</span>
            <span class="font-headline-md text-headline-md font-bold text-primary" data-i18n="nav_projects">Projects</span>
          </div>
          <!-- Search Input -->
          <div class="relative w-full max-w-md">
            <span class="material-symbols-outlined absolute left-sm top-1/2 -translate-y-1/2 text-outline">search</span>
            <input id="proj-search-input" oninput="window.filterProjectSearch(this.value)" class="w-full pl-xl pr-md py-sm rounded-lg bg-surface-bright border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary-fixed focus:outline-none font-body-sm text-body-sm transition-all" placeholder="Cari project..." type="text"/>
          </div>
        </div>
        <!-- Trailing Actions -->
        <div class="flex items-center gap-sm">
          <a href="tasks.php" class="hidden md:flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-primary bg-primary-fixed hover:bg-primary hover:text-white rounded-lg transition-all shadow-xs" title="Lihat Papan Kanban">
            <span class="material-symbols-outlined text-[16px]">view_kanban</span>
            <span>Papan Kanban</span>
          </a>
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
    <div id="app" class="mx-auto max-w-7xl p-5 md:p-8 flex-1 w-full"></div>
    <div class="mt-auto shrink-0 w-full">
      <?php include 'partials/footer.php'; ?>
    </div>
  </main>
  <div id="modal" class="modal fixed inset-0 z-50 hidden items-center justify-center p-4"></div>
  <div id="toast" class="fixed bottom-5 right-5 z-[60] hidden rounded-lg bg-slate-900 px-4 py-3 text-sm font-semibold text-white"></div>
  <?php include 'partials/confirm-modal.php'; ?>
  <script src="project-store.js"></script>
  <script src="projects-ui.js"></script>
</body>
</html>