<?php
require_once __DIR__ . '/../session.php';
require_admin();
require_once __DIR__ . '/../Login/koneksi.php';

$users = [];
$cohort_years = [];

if ($conn) {
    if (function_exists('sync_all_intern_certificates')) {
        sync_all_intern_certificates($conn);
    }

    $qy = mysqli_query($conn, "SELECT DISTINCT CAST(COALESCE(NULLIF(REGEXP_SUBSTR(certificate_id, '[0-9]{4}'), ''), YEAR(start_date), YEAR(issue_date), YEAR(created_at)) AS UNSIGNED) as yr FROM certificates ORDER BY yr DESC");
    if ($qy) {
        while ($ry = mysqli_fetch_assoc($qy)) {
            if (!empty($ry['yr'])) $cohort_years[] = (int)$ry['yr'];
        }
    }

    $sql_users = "SELECT u.id, u.username, u.password, u.role, u.intern_position, u.university, u.major,
                         COALESCE(
                             (SELECT CAST(COALESCE(
                                 NULLIF(REGEXP_SUBSTR(c.certificate_id, '[0-9]{4}'), ''),
                                 YEAR(c.start_date),
                                 YEAR(c.issue_date),
                                 YEAR(c.created_at)
                             ) AS UNSIGNED) FROM certificates c WHERE c.user_id = u.id LIMIT 1),
                             CAST(YEAR(NOW()) AS UNSIGNED)
                         ) AS entry_year
                  FROM users u 
                  ORDER BY u.id ASC";
    $users_query = mysqli_query($conn, $sql_users);
    if ($users_query) {
        while ($user = mysqli_fetch_assoc($users_query)) {
            $users[] = $user;
        }
    }
}

$current_yr = (int)date('Y');
if (!in_array($current_yr, $cohort_years)) {
    array_unshift($cohort_years, $current_yr);
}
rsort($cohort_years);

$total_users = count($users);
$total_interns = count(array_filter($users, static function (array $user): bool {
    return $user['role'] === 'intern';
}));
$total_admins = count(array_filter($users, static function (array $user): bool {
    return $user['role'] === 'admin';
}));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <title>Kedayweb Admin - Users</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="../shared-config.js"></script>
    <link rel="stylesheet" href="../style.css"/>
    <style>
        .glass-card { background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); }
    </style>
</head>
<body class="bg-background text-on-surface font-body-md flex h-screen overflow-hidden">
    <!-- Sidebar -->
<?php $active = 'users'; include '../partials/sidebar-admin.php'; ?>

    <!-- Main -->
    <main class="flex-1 flex flex-col md:ml-[16.5rem] h-screen overflow-y-auto">
        <header class="w-full h-20 bg-surface-container-lowest border-b border-outline-variant sticky top-0 flex justify-between items-center px-4 sm:px-6 z-10 shrink-0">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1 mr-2 sm:mr-4">
                <button onclick="toggleMobileSidebar()" class="md:hidden text-on-surface hover:text-primary focus:outline-none flex items-center p-1 rounded-lg hover:bg-surface-container-high shrink-0" aria-label="Toggle Sidebar">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <span class="material-symbols-outlined text-primary text-xl sm:text-2xl shrink-0" style="font-variation-settings: 'FILL' 1;">people</span>
                <h2 class="font-headline-sm sm:font-headline-lg font-bold text-on-surface truncate whitespace-nowrap">
                    Kelola Users
                </h2>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <div class="flex items-center gap-1 sm:gap-sm p-1 sm:p-1.5 px-2 sm:px-3 rounded-full border border-outline-variant bg-surface-bright shadow-2xs shrink-0">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container overflow-hidden shrink-0">
                        <span class="material-symbols-outlined text-[18px] sm:text-[20px]" style="font-variation-settings: 'FILL' 1;">account_circle</span>
                    </div>
                    <span class="hidden sm:inline-block font-label-md"><?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="../Login/logout.php" class="text-error hover:text-red-700 hover:bg-red-50 p-1 sm:p-1.5 rounded-full transition-colors flex items-center justify-center" title="Keluar" aria-label="Keluar"><span class="material-symbols-outlined text-[18px] sm:text-[20px]">logout</span></a>
                </div>
            </div>
        </header>

        <div class="p-6 flex flex-col gap-6">
            <!-- Stats Row -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="glass-card rounded-xl border border-outline-variant p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">group</span>
                    </div>
                    <div>
                        <p class="text-sm text-on-surface-variant">Total Intern</p>
                        <p class="text-2xl font-bold text-on-surface"><?php echo $total_interns; ?></p>
                    </div>
                </div>
                <div class="glass-card rounded-xl border border-outline-variant p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-700">
                        <span class="material-symbols-outlined">check_circle</span>
                    </div>
                    <div>
                        <p class="text-sm text-on-surface-variant">Total User</p>
                        <p class="text-2xl font-bold text-on-surface"><?php echo $total_users; ?></p>
                    </div>
                </div>
                <div class="glass-card rounded-xl border border-outline-variant p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container">
                        <span class="material-symbols-outlined">admin_panel_settings</span>
                    </div>
                    <div>
                        <p class="text-sm text-on-surface-variant">Total Admin</p>
                        <p class="text-2xl font-bold text-on-surface"><?php echo $total_admins; ?></p>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="glass-card rounded-xl border border-outline-variant p-5 shadow-xs">
                <!-- Header & Toolbar Controls -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4 pb-3 border-b border-outline-variant">
                    <div>
                        <h3 class="font-headline-md font-bold text-on-surface">Daftar User & Intern</h3>
                        <p class="text-xs text-on-surface-variant mt-0.5">Kelola data pengguna dan filter berdasarkan angkatan tahun masuk.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                        <!-- Filter Angkatan Dropdown -->
                        <div class="flex items-center gap-1.5 bg-surface-bright border border-outline-variant rounded-xl px-2.5 py-1.5 shadow-2xs">
                            <span class="material-symbols-outlined text-primary text-[18px]">calendar_today</span>
                            <label for="admin-year-select" class="text-xs font-semibold text-on-surface-variant whitespace-nowrap">Angkatan:</label>
                            <select id="admin-year-select" onchange="filterAdminUsersByCohort(this.value)"
                                    class="bg-transparent border-none text-xs font-bold text-primary focus:outline-none focus:ring-0 py-0.5 pl-1 pr-6 cursor-pointer">
                                <option value="all">Semua Tahun</option>
                                <?php foreach ($cohort_years as $yr): ?>
                                    <option value="<?php echo $yr; ?>">Angkatan <?php echo $yr; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Search Input -->
                        <div class="relative flex-1 sm:w-64 min-w-[180px]">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                            <input type="text" id="search-user" placeholder="Cari user, email, posisi..." class="w-full pl-9 pr-3 py-1.5 text-xs border border-outline-variant rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary bg-surface-container-low transition-colors" oninput="applyAdminUserFilters()"/>
                        </div>
                    </div>
                </div>

                <!-- Cohort Pills Bar (Quick Navigation) -->
                <div id="admin-cohort-pills-bar" class="flex items-center gap-2 px-4 py-2 bg-surface-container-low/40 rounded-xl border border-outline-variant mb-4 overflow-x-auto text-xs">
                    <span class="text-on-surface-variant font-medium shrink-0 mr-1 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-primary">filter_alt</span>
                        <span>Filter Cepat:</span>
                    </span>
                    <button type="button" onclick="setAdminCohortPill('all')" data-cohort="all" class="admin-cohort-pill px-3 py-1 rounded-full text-xs font-bold transition-all bg-primary text-on-primary shadow-xs cursor-pointer">
                        Semua Angkatan
                    </button>
                    <?php foreach ($cohort_years as $yr): ?>
                        <button type="button" onclick="setAdminCohortPill('<?php echo $yr; ?>')" data-cohort="<?php echo $yr; ?>" class="admin-cohort-pill px-3 py-1 rounded-full text-xs font-semibold text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high border border-outline-variant/60 transition-all cursor-pointer">
                            Angkatan <?php echo $yr; ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-outline-variant text-on-surface-variant text-left">
                                <th class="pb-2.5 pr-4 font-semibold">ID</th>
                                <th class="pb-2.5 pr-4 font-semibold">Username / Email</th>
                                <?php if (current_user_role() === 'superadmin'): ?>
                                    <th class="pb-2.5 pr-4 font-semibold">Password</th>
                                <?php endif; ?>
                                <th class="pb-2.5 pr-4 font-semibold">Role</th>
                                <th class="pb-2.5 pr-4 font-semibold">Angkatan</th>
                                <th class="pb-2.5 pr-4 font-semibold">Posisi Magang</th>
                                <th class="pb-2.5 pr-4 font-semibold">Instansi / Universitas</th>
                                <th class="pb-2.5 pr-4 font-semibold">Jurusan</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body" class="divide-y divide-outline-variant">
                            <?php foreach ($users as $user): ?>
                                <tr class="user-row hover:bg-surface-container-low transition-colors"
                                    data-year="<?php echo (int)($user['entry_year'] ?? date('Y')); ?>"
                                    data-search="<?php echo htmlspecialchars(strtolower($user['username'] . ' ' . $user['role'] . ' ' . ($user['intern_position'] ?? '') . ' ' . ($user['university'] ?? '') . ' ' . ($user['major'] ?? '') . ' ' . ($user['entry_year'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>">
                                    <td class="py-2.5 pr-4 font-medium">#<?php echo (int) $user['id']; ?></td>
                                    <td class="py-2.5 pr-4 font-semibold text-on-surface"><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <?php if (current_user_role() === 'superadmin'): ?>
                                        <td class="py-2.5 pr-4 font-mono text-sm">
                                            <div class="flex items-center gap-2">
                                                <span class="password-text hidden bg-surface-container-high px-2 py-0.5 rounded text-xs text-primary font-bold select-all"><?php echo htmlspecialchars($user['password'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                                                <span class="password-hidden text-xs text-on-surface-variant font-bold">••••••••</span>
                                                <button type="button" onclick="togglePassword(this)" class="text-on-surface-variant hover:text-primary transition-colors cursor-pointer p-1 rounded-lg hover:bg-surface-container-high" title="Lihat/Sembunyikan Password">
                                                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                                                </button>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                    <td class="py-2.5 pr-4 text-on-surface-variant">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?php echo $user['role'] === 'superadmin' ? 'bg-purple-100 text-purple-700' : ($user['role'] === 'admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'); ?>">
                                            <?php echo strtoupper(htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8')); ?>
                                        </span>
                                    </td>
                                    <td class="py-2.5 pr-4">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-900 border border-amber-200/80 flex items-center gap-1 w-fit">
                                            <span class="material-symbols-outlined text-[13px] text-amber-600">calendar_today</span>
                                            <span>Angkatan <?php echo (int)($user['entry_year'] ?? date('Y')); ?></span>
                                        </span>
                                    </td>
                                    <td class="py-2.5 pr-4 font-medium text-on-surface">
                                        <?php echo !empty($user['intern_position']) ? htmlspecialchars($user['intern_position'], ENT_QUOTES, 'UTF-8') : '<span class="text-slate-400 italic text-xs">-</span>'; ?>
                                    </td>
                                    <td class="py-2.5 pr-4 font-medium text-on-surface">
                                        <?php echo !empty($user['university']) ? htmlspecialchars($user['university'], ENT_QUOTES, 'UTF-8') : '<span class="text-slate-400 italic text-xs">-</span>'; ?>
                                    </td>
                                    <td class="py-2.5 pr-4 font-medium text-on-surface">
                                        <?php echo !empty($user['major']) ? htmlspecialchars($user['major'], ENT_QUOTES, 'UTF-8') : '<span class="text-slate-400 italic text-xs">-</span>'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p id="no-users-msg" class="hidden text-center py-8 text-on-surface-variant font-medium">Tidak ada user yang sesuai dengan filter angkatan/search.</p>
                </div>
            </div>
        </div>
        <div class="mt-auto shrink-0 w-full">
            <?php include '../partials/footer.php'; ?>
        </div>
    </main>

    <script>
        let currentAdminYear = 'all';

        function setAdminCohortPill(yr) {
            currentAdminYear = String(yr);
            const select = document.getElementById('admin-year-select');
            if (select) select.value = currentAdminYear;
            highlightAdminActivePill(currentAdminYear);
            applyAdminUserFilters();
        }

        function filterAdminUsersByCohort(yr) {
            currentAdminYear = String(yr);
            highlightAdminActivePill(currentAdminYear);
            applyAdminUserFilters();
        }

        function highlightAdminActivePill(activeCohort) {
            document.querySelectorAll('.admin-cohort-pill').forEach(pill => {
                const cohort = pill.getAttribute('data-cohort');
                if (cohort === activeCohort) {
                    pill.className = 'admin-cohort-pill px-3 py-1 rounded-full text-xs font-bold transition-all bg-primary text-on-primary shadow-xs cursor-pointer';
                } else {
                    pill.className = 'admin-cohort-pill px-3 py-1 rounded-full text-xs font-semibold text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high border border-outline-variant/60 transition-all cursor-pointer';
                }
            });
        }

        function applyAdminUserFilters() {
            const filter = (document.getElementById('search-user')?.value || '').toLowerCase().trim();
            const rows = document.querySelectorAll('.user-row');
            let visibleRows = 0;

            rows.forEach(row => {
                const rowYear = row.getAttribute('data-year') || '';
                const rowSearch = row.getAttribute('data-search') || row.textContent.toLowerCase();

                const matchesYear = (currentAdminYear === 'all' || rowYear === currentAdminYear);
                const matchesSearch = (!filter || rowSearch.includes(filter));

                const visible = matchesYear && matchesSearch;
                row.classList.toggle('hidden', !visible);
                if (visible) visibleRows++;
            });

            document.getElementById('no-users-msg').classList.toggle('hidden', visibleRows > 0);
        }

        function togglePassword(btn) {
            const parent = btn.parentElement;
            const textSpan = parent.querySelector('.password-text');
            const hiddenSpan = parent.querySelector('.password-hidden');
            const icon = btn.querySelector('.material-symbols-outlined');
            
            if (textSpan.classList.contains('hidden')) {
                textSpan.classList.remove('hidden');
                hiddenSpan.classList.add('hidden');
                icon.textContent = 'visibility_off';
            } else {
                textSpan.classList.add('hidden');
                hiddenSpan.classList.remove('hidden');
                icon.textContent = 'visibility';
            }
        }
    </script>
</body>
</html>
