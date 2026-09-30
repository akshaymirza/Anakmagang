<?php
require_once __DIR__ . '/../session.php';
require_admin();
require_once __DIR__ . '/../Login/koneksi.php';

$users = [];
$users_query = mysqli_query($conn, 'SELECT id, username, password, role, intern_position, university, major FROM users ORDER BY id ASC');
if ($users_query) {
    while ($user = mysqli_fetch_assoc($users_query)) {
        $users[] = $user;
    }
}

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
        <header class="w-full h-20 bg-surface-container-lowest border-b border-outline-variant sticky top-0 flex justify-between items-center px-6 z-10 shrink-0">
            <h2 class="font-headline-lg font-bold text-on-surface flex items-center gap-2">
                <button onclick="toggleMobileSidebar()" class="md:hidden text-on-surface hover:text-primary focus:outline-none flex items-center mr-1 p-1 rounded-lg hover:bg-surface-container-high" aria-label="Toggle Sidebar">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">people</span>
                <span>Manajemen Users</span>
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
            <div class="glass-card rounded-xl border border-outline-variant p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-headline-md">Daftar Intern</h3>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-2 top-2 text-on-surface-variant text-lg">search</span>
                        <input type="text" id="search-user" placeholder="Cari intern..." class="pl-8 pr-3 py-1.5 text-sm border border-outline-variant rounded-lg focus:outline-none focus:border-primary bg-surface-container-low"/>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-outline-variant text-on-surface-variant text-left">
                                <th class="pb-2 pr-4 font-semibold">ID</th>
                                <th class="pb-2 pr-4 font-semibold">Username / Email</th>
                                <?php if (current_user_role() === 'superadmin'): ?>
                                    <th class="pb-2 pr-4 font-semibold">Password</th>
                                <?php endif; ?>
                                <th class="pb-2 pr-4 font-semibold">Role</th>
                                <th class="pb-2 pr-4 font-semibold">Posisi Magang</th>
                                <th class="pb-2 pr-4 font-semibold">Instansi / Universitas</th>
                                <th class="pb-2 pr-4 font-semibold">Jurusan</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body" class="divide-y divide-outline-variant">
                            <?php foreach ($users as $user): ?>
                                <tr class="user-row hover:bg-surface-container-low transition-colors">
                                    <td class="py-2.5 pr-4 font-medium"><?php echo (int) $user['id']; ?></td>
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
                    <p id="no-users-msg" class="hidden text-center py-8 text-on-surface-variant">Tidak ada intern yang ditemukan.</p>
                </div>
            </div>
        </div>
        <div class="mt-auto shrink-0 w-full">
            <?php include '../partials/footer.php'; ?>
        </div>
    </main>

    <script>
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

        document.getElementById('search-user').addEventListener('input', event => {
            const filter = event.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.user-row');
            let visibleRows = 0;

            rows.forEach(row => {
                const visible = row.textContent.toLowerCase().includes(filter);
                row.classList.toggle('hidden', !visible);
                if (visible) visibleRows++;
            });

            document.getElementById('no-users-msg').classList.toggle('hidden', visibleRows > 0);
        });
    </script>
</body>
</html>
