<?php
require_once __DIR__ . '/session.php';
require_admin(); // Memastikan hanya Admin dan Superadmin yang bisa mengakses halaman ini

// Database connection attempt
$conn = null;
if (file_exists(__DIR__ . '/Login/koneksi.php')) {
    include_once __DIR__ . '/Login/koneksi.php';
}

// Action: Update status or delete application
$action_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_id = intval($_POST['app_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    
    if ($app_id > 0) {
        if ($action === 'delete') {
            if ($conn) {
                $stmt = mysqli_prepare($conn, "DELETE FROM `applications` WHERE id = ?");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "i", $app_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }
            // Delete from JSON fallback if exists
            $json_file = __DIR__ . '/uploads/applications.json';
            if (file_exists($json_file)) {
                $apps_json = json_decode(file_get_contents($json_file), true) ?: [];
                $apps_json = array_filter($apps_json, function($a) use ($app_id) {
                    return intval($a['id'] ?? 0) !== $app_id;
                });
                file_put_contents($json_file, json_encode(array_values($apps_json), JSON_PRETTY_PRINT));
            }
            $action_msg = 'Pendaftaran berhasil dihapus.';
        } elseif (in_array($action, ['review', 'interview', 'offer', 'rejected'])) {
            if ($conn) {
                $stmt = mysqli_prepare($conn, "UPDATE `applications` SET status = ? WHERE id = ?");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "si", $action, $app_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }
            // Update JSON fallback
            $json_file = __DIR__ . '/uploads/applications.json';
            if (file_exists($json_file)) {
                $apps_json = json_decode(file_get_contents($json_file), true) ?: [];
                foreach ($apps_json as &$a) {
                    if (intval($a['id'] ?? 0) === $app_id) {
                        $a['status'] = $action;
                    }
                }
                file_put_contents($json_file, json_encode($apps_json, JSON_PRETTY_PRINT));
            }
            $action_msg = 'Status pendaftaran berhasil diperbarui.';
        }
    }
}

// Fetch applications
$applications = [];

// 1. Try DB first
if ($conn) {
    // Check if table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'applications'");
    if (mysqli_num_rows($table_check) > 0) {
        $result = mysqli_query($conn, "SELECT * FROM `applications` ORDER BY id DESC");
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $applications[] = $row;
            }
        }
    }
}

// 2. Fallback to JSON if DB returned empty or no DB
if (empty($applications)) {
    $json_file = __DIR__ . '/uploads/applications.json';
    if (file_exists($json_file)) {
        $applications = json_decode(file_get_contents($json_file), true) ?: [];
    }
}

// Count stats
$total_apps = count($applications);
$count_review = count(array_filter($applications, fn($a) => ($a['status'] ?? 'review') === 'review'));
$count_interview = count(array_filter($applications, fn($a) => ($a['status'] ?? '') === 'interview'));
$count_offer = count(array_filter($applications, fn($a) => ($a['status'] ?? '') === 'offer'));
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Kelola Pendaftaran Magang - Admin Panel</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined:FILL@0..1" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script src="shared-config.js"></script>
  <style>
    body { font-family: Inter, sans-serif; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 500; }
    .tab.active { color: #00236f; border-color: #00236f; }
  </style>
</head>
<body class="bg-background text-on-surface font-body-md flex h-screen overflow-hidden">
<?php $active = 'applications'; include 'partials/sidebar-admin.php'; ?>
  <main class="flex-1 md:ml-[16.5rem] flex flex-col h-screen overflow-y-auto">
    <!-- Top Navigation Header -->
    <header class="w-full h-20 bg-surface-container-lowest border-b border-outline-variant sticky top-0 flex items-center justify-between px-gutter z-10 shrink-0">
      <div class="flex items-center gap-3 min-w-0 flex-1">
        <button onclick="toggleMobileSidebar()" class="md:hidden text-on-surface hover:text-primary focus:outline-none flex items-center shrink-0 p-1 rounded-lg hover:bg-surface-container-high" aria-label="Toggle Sidebar">
            <span class="material-symbols-outlined text-2xl">menu</span>
        </button>
        <span class="material-symbols-outlined text-primary text-2xl shrink-0" style="font-variation-settings: 'FILL' 1;">description</span>
        <div class="min-w-0">
            <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface truncate">Pendaftaran</h2>
            <p class="text-xs text-on-surface-variant hidden sm:block truncate">Kelola pendaftaran masuk dari form dashboard utama</p>
        </div>
      </div>
      <div class="flex items-center gap-sm shrink-0">
        <div class="flex items-center gap-sm p-1.5 px-3 rounded-full border border-outline-variant bg-surface-bright shadow-2xs">
          <div class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container overflow-hidden shrink-0">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">account_circle</span>
          </div>
          <span class="hidden sm:inline-block font-label-md"><?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?></span>
          <a href="logout.php" class="text-error hover:text-red-700 hover:bg-red-50 p-1.5 rounded-full transition-colors flex items-center justify-center" title="Keluar" aria-label="Keluar"><span class="material-symbols-outlined text-[20px]">logout</span></a>
        </div>
      </div>
    </header>

    <div class="mx-auto max-w-7xl p-5 md:p-8 flex-1 w-full">
      <?php if (!empty($action_msg)): ?>
        <div class="mb-6 rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 border border-emerald-200 flex items-center gap-2">
          <span class="material-symbols-outlined text-emerald-600">check_circle</span>
          <?php echo htmlspecialchars($action_msg, ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endif; ?>


      <!-- Main Applications Pipeline Table -->
      <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col justify-between gap-4 border-b border-slate-200 p-5 sm:flex-row sm:items-center">
          <div>
            <h2 class="font-geist text-lg font-bold">Daftar Pendaftar Magang</h2>
            <p class="text-xs text-slate-500 mt-0.5">Semua aplikasi yang dikirimkan via form magang di index page.</p>
          </div>
          <!-- Search Input -->
          <div class="relative w-full sm:w-72">
            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
            <input id="searchInput" onkeyup="filterApplications()" class="w-full pl-8 pr-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium focus:border-blue-600 focus:bg-white outline-none transition-colors" placeholder="Cari pelamar (nama, email, posisi)..." type="search">
          </div>
        </div>

        <!-- Filters Tabs -->
        <div class="flex gap-6 overflow-x-auto border-b border-slate-200 px-5">
          <button data-filter="all" onclick="filterStatus('all', this)" class="tab active shrink-0 border-b-2 border-blue-700 py-3.5 text-sm font-semibold">Semua (<?php echo $total_apps; ?>)</button>
          <button data-filter="review" onclick="filterStatus('review', this)" class="tab shrink-0 border-b-2 border-transparent py-3.5 text-sm font-semibold text-slate-500 hover:text-slate-900">Perlu Review (<?php echo $count_review; ?>)</button>
          <button data-filter="interview" onclick="filterStatus('interview', this)" class="tab shrink-0 border-b-2 border-transparent py-3.5 text-sm font-semibold text-slate-500 hover:text-slate-900">Interview (<?php echo $count_interview; ?>)</button>
          <button data-filter="offer" onclick="filterStatus('offer', this)" class="tab shrink-0 border-b-2 border-transparent py-3.5 text-sm font-semibold text-slate-500 hover:text-slate-900">Offer (<?php echo $count_offer; ?>)</button>
        </div>

        <div id="apps-container" class="divide-y divide-slate-100">
          <?php if (empty($applications)): ?>
            <div class="p-12 text-center text-slate-500">
              <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">folder_off</span>
              <p class="font-semibold text-slate-700">Belum ada data pendaftaran</p>
              <p class="text-xs text-slate-500 mt-1">Pendaftaran yang dikirim melalui form landing page akan muncul di sini secara otomatis.</p>
            </div>
          <?php else: ?>
            <?php foreach ($applications as $app): 
              $id = intval($app['id'] ?? 0);
              $fname = htmlspecialchars($app['first_name'] ?? '', ENT_QUOTES, 'UTF-8');
              $lname = htmlspecialchars($app['last_name'] ?? '', ENT_QUOTES, 'UTF-8');
              $full_name = trim($fname . ' ' . $lname);
              $email = htmlspecialchars($app['email'] ?? '', ENT_QUOTES, 'UTF-8');
              $pos = htmlspecialchars($app['position'] ?? '-', ENT_QUOTES, 'UTF-8');
              $portfolio = htmlspecialchars($app['portfolio'] ?? '', ENT_QUOTES, 'UTF-8');
              $cv = htmlspecialchars($app['cv_file'] ?? '', ENT_QUOTES, 'UTF-8');
              $status = strtolower($app['status'] ?? 'review');
              $created = htmlspecialchars($app['created_at'] ?? '', ENT_QUOTES, 'UTF-8');
              
              // Status Badge Config
              $badge_class = 'bg-blue-100 text-blue-800';
              $status_label = 'Under Review';
              if ($status === 'interview') {
                  $badge_class = 'bg-amber-100 text-amber-800';
                  $status_label = 'Interview Scheduled';
              } elseif ($status === 'offer') {
                  $badge_class = 'bg-emerald-100 text-emerald-800';
                  $status_label = 'Offer Received';
              } elseif ($status === 'rejected') {
                  $badge_class = 'bg-rose-100 text-rose-800';
                  $status_label = 'Ditolak';
              }
            ?>
              <article data-status="<?php echo $status; ?>" class="app-item grid gap-4 p-5 md:grid-cols-[auto_1fr_auto] md:items-center hover:bg-slate-50/50 transition-colors">
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-blue-50 font-geist text-base font-bold text-blue-700 uppercase">
                  <?php echo substr($fname, 0, 1) . substr($lname, 0, 1); ?>
                </div>
                <div>
                  <div class="flex flex-wrap items-center gap-2">
                    <h3 class="font-geist text-base font-bold text-slate-900"><?php echo $full_name; ?></h3>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-bold <?php echo $badge_class; ?>">
                      <?php echo $status_label; ?>
                    </span>
                  </div>
                  <p class="mt-1 text-xs text-slate-500">
                    <span class="font-medium text-slate-700"><?php echo $pos; ?></span> · <?php echo $email; ?> · Didaftarkan: <?php echo $created; ?>
                  </p>
                  
                  <div class="mt-2 flex flex-wrap gap-4 text-xs">
                    <?php if (!empty($portfolio)): ?>
                      <a href="<?php echo $portfolio; ?>" target="_blank" class="inline-flex items-center gap-1 font-semibold text-blue-600 hover:underline">
                        <span class="material-symbols-outlined text-[14px]">link</span> Portofolio: <?php echo $portfolio; ?>
                      </a>
                    <?php endif; ?>

                    <?php if (!empty($cv)): ?>
                      <a href="<?php echo $cv; ?>" target="_blank" class="inline-flex items-center gap-1 font-semibold text-emerald-600 hover:underline">
                        <span class="material-symbols-outlined text-[14px]">download</span> Lihat Resume CV
                      </a>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Admin Action Buttons -->
                <div class="flex items-center gap-2">
                  <form method="POST" class="inline-flex gap-1">
                    <input type="hidden" name="app_id" value="<?php echo $id; ?>">
                    <select name="action" onchange="this.form.submit()" class="rounded-lg border border-slate-200 bg-white py-1.5 px-3 text-xs font-semibold text-slate-700 focus:border-blue-600 outline-none shadow-sm cursor-pointer">
                      <option value="">Ubah Status...</option>
                      <option value="review" <?php echo $status === 'review' ? 'selected' : ''; ?>>Review</option>
                      <option value="interview" <?php echo $status === 'interview' ? 'selected' : ''; ?>>Interview</option>
                      <option value="offer" <?php echo $status === 'offer' ? 'selected' : ''; ?>>Offer (Diterima)</option>
                      <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                  </form>

                  <form method="POST" class="inline confirm-action-form" data-confirm-title="Hapus Pendaftaran Magang" data-confirm-message="Apakah Anda yakin ingin menghapus pendaftaran magang ini?">
                    <input type="hidden" name="app_id" value="<?php echo $id; ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="rounded-lg border border-slate-200 bg-white p-1.5 text-slate-400 hover:border-red-200 hover:bg-red-50 hover:text-red-600 transition-colors" title="Hapus">
                      <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                  </form>
                </div>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <p id="empty-search" class="hidden p-10 text-center text-sm text-slate-500">Tidak ada pendaftaran yang sesuai dengan pencarian/filter.</p>
      </section>
    </div>
    <div class="mt-auto shrink-0 w-full">
      <?php include 'partials/footer.php'; ?>
    </div>
  </main>

  <?php include 'partials/confirm-modal.php'; ?>

  <script>
    let activeFilter = 'all';

    function filterStatus(filter, btn) {
      activeFilter = filter;
      document.querySelectorAll('.tab').forEach(b => {
        b.classList.remove('active', 'border-blue-700');
        b.classList.add('border-transparent', 'text-slate-500');
      });
      btn.classList.add('active', 'border-blue-700');
      btn.classList.remove('border-transparent', 'text-slate-500');
      filterApplications();
    }

    function filterApplications() {
      const query = (document.getElementById('searchInput').value || '').toLowerCase();
      const items = document.querySelectorAll('.app-item');
      let visibleCount = 0;

      items.forEach(item => {
        const text = item.innerText.toLowerCase();
        const status = item.dataset.status;

        const matchesFilter = (activeFilter === 'all' || status === activeFilter);
        const matchesQuery  = text.includes(query);

        if (matchesFilter && matchesQuery) {
          item.classList.remove('hidden');
          visibleCount++;
        } else {
          item.classList.add('hidden');
        }
      });

      const emptyEl = document.getElementById('empty-search');
      if (emptyEl) {
        emptyEl.classList.toggle('hidden', visibleCount > 0 || items.length === 0);
      }
    }

    document.querySelectorAll('.confirm-action-form').forEach(form => {
      form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const title = this.getAttribute('data-confirm-title') || 'Konfirmasi';
        const message = this.getAttribute('data-confirm-message') || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
        
        const confirmed = await window.showConfirmModal({
          title: title,
          message: message,
          confirmText: 'Ya, Hapus',
          cancelText: 'Batal',
          type: 'danger'
        });

        if (confirmed) {
          this.submit();
        }
      });
    });
  </script>
</body>
</html>
