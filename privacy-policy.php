<?php
/**
 * privacy-policy.php
 * Official Privacy Policy Page for Kedayweb Platform
 */
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/legal_helper.php';

$privacy_data = get_legal_data('privacy');
$is_logged_in = is_logged_in();
$user_role    = current_user_role();
$is_superadmin = ($user_role === 'superadmin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Kedayweb - <?php echo htmlspecialchars($privacy_data['title'] ?? 'Kebijakan Privasi', ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&amp;family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="shared-config.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-background text-on-surface font-body-md flex min-h-screen flex-col">

    <?php 
    if ($is_logged_in) {
        $active = 'privacy';
        if ($user_role === 'admin' || $user_role === 'superadmin') {
            include 'partials/sidebar-admin.php';
        } else {
            include 'partials/sidebar-intern.php';
        }
    }
    ?>

    <main class="<?php echo $is_logged_in ? 'md:ml-[16.5rem]' : ''; ?> flex-1 flex flex-col">
        <!-- Hero Header -->
        <section class="w-full bg-gradient-to-r from-blue-950 via-slate-900 to-indigo-950 text-white py-12 px-6 shadow-md relative overflow-hidden">
            <div class="max-w-5xl mx-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative z-10">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-500/20 text-blue-200 text-xs font-bold border border-blue-400/30 mb-3">
                        <span class="material-symbols-outlined text-sm">security</span>
                        <span><?php echo htmlspecialchars($privacy_data['badge'] ?? 'Privasi & Keamanan Data', ENT_QUOTES, 'UTF-8'); ?></span>
                    </span>
                    <h1 class="font-geist text-2xl md:text-4xl font-extrabold tracking-tight">
                        <?php echo htmlspecialchars($privacy_data['title'] ?? 'Kebijakan Privasi', ENT_QUOTES, 'UTF-8'); ?>
                    </h1>
                    <p class="mt-2 text-slate-300 max-w-2xl text-sm leading-relaxed">
                        <?php echo htmlspecialchars($privacy_data['intro'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <p class="mt-2 text-slate-400 text-xs">
                        Terakhir Diperbarui: <?php echo date('d F Y', strtotime($privacy_data['updated_at'] ?? 'now')); ?>
                    </p>
                </div>
                
                <?php if ($is_superadmin): ?>
                    <a href="admin/superadmin.php#legal" class="shrink-0 px-5 py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-xl transition-all shadow-md flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">tune</span>
                        <span>Kelola di Super Admin</span>
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <!-- Main Content -->
        <div class="w-full max-w-5xl mx-auto p-6 md:p-10 space-y-6 flex-1">
            <?php if (!empty($privacy_data['sections'])): ?>
                <div class="grid grid-cols-1 gap-4">
                    <?php foreach ($privacy_data['sections'] as $idx => $sec): ?>
                        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                                    <span class="material-symbols-outlined text-lg"><?php echo htmlspecialchars($sec['icon'] ?? 'security', ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <h3 class="font-geist font-bold text-slate-900 text-base md:text-lg">
                                    <?php echo htmlspecialchars($sec['title'] ?? 'Poin Privasi', ENT_QUOTES, 'UTF-8'); ?>
                                </h3>
                            </div>
                            <div class="text-slate-600 text-sm leading-relaxed pl-12 whitespace-pre-line">
                                <?php echo nl2br(htmlspecialchars($sec['content'] ?? '', ENT_QUOTES, 'UTF-8')); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="p-8 text-center bg-slate-50 rounded-2xl border border-slate-200 text-slate-400 text-sm">
                    Belum ada pasal kebijakan privasi yang terdaftar.
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-auto shrink-0 w-full">
            <?php include 'partials/footer.php'; ?>
        </div>
    </main>

</body>
</html>

