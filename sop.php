<?php 
require_once __DIR__ . '/session.php'; 
require_login(); 
require_once __DIR__ . '/sop_helper.php';

$sop_data = get_sop_data();
$sop_badge = $sop_data['badge'] ?? 'Standar Operasional Prosedur';
$sop_title = $sop_data['title'] ?? 'SOP Peserta Magang Kedayweb';
$sop_subtitle = $sop_data['subtitle'] ?? 'Kedayweb × Kulino House — wajib dibaca dan dipahami oleh seluruh peserta magang.';
$sop_sections = $sop_data['sections'] ?? [];
$user_role = current_user_role();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Kedayweb - <?php echo htmlspecialchars($sop_title, ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&amp;family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="shared-config.js"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        .sop-content p { margin-bottom: 0.65rem; line-height: 1.65; color: #475569; }
        .sop-content p:last-child { margin-bottom: 0; }
        .sop-content ul { list-style-type: disc !important; padding-left: 1.35rem !important; margin-bottom: 0.85rem !important; }
        .sop-content ol { list-style-type: decimal !important; padding-left: 1.35rem !important; margin-bottom: 0.85rem !important; }
        .sop-content li { margin-bottom: 0.35rem; line-height: 1.6; color: #334155; }
        .sop-content strong { font-weight: 700; color: #0f172a; }
        .sop-content h3, .sop-content h4 { font-weight: 700; color: #0f172a; margin-top: 1rem; margin-bottom: 0.4rem; }
    </style>
</head>
<body class="bg-background text-on-surface font-body-md flex h-screen overflow-hidden">
<?php 
$active = 'sop';
if ($user_role === 'admin' || $user_role === 'superadmin') {
    include 'partials/sidebar-admin.php';
} else {
    include 'partials/sidebar-intern.php';
}
?>

<main class="flex-1 flex flex-col md:ml-[16.5rem] h-screen overflow-y-auto relative">
    <!-- TopNavBar -->
    <header class="w-full h-20 bg-surface-container-lowest border-b border-outline-variant sticky top-0 z-10 shrink-0">
        <div class="flex justify-between items-center px-gutter w-full max-w-container-max mx-auto h-full">
            <div class="flex-1 flex items-center">
                <button onclick="toggleMobileSidebar()" class="md:hidden mr-md text-on-surface hover:text-primary focus:outline-none flex items-center p-1 rounded-lg hover:bg-surface-container-high" aria-label="Toggle Sidebar">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">description</span>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold"><?php echo htmlspecialchars($sop_title, ENT_QUOTES, 'UTF-8'); ?></h2>
                </div>
            </div>
            <div class="flex items-center gap-sm">
                <div class="flex items-center gap-sm p-1.5 px-3 rounded-full border border-outline-variant bg-surface-bright shadow-2xs">
                    <div class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container overflow-hidden shrink-0">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">account_circle</span>
                    </div>
                    <span class="font-label-md text-label-md hidden sm:inline-block"><?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="logout.php" class="text-error hover:text-red-700 hover:bg-red-50 p-1.5 rounded-full transition-colors flex items-center justify-center" title="Keluar" aria-label="Keluar">
                        <span class="material-symbols-outlined text-[20px]">logout</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="w-full max-w-container-max mx-auto p-md md:p-gutter flex flex-col gap-xl flex-1">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-xs px-3 py-1 rounded-full bg-primary-container text-on-primary-container font-label-sm text-label-sm mb-sm font-semibold">
                    <span class="material-symbols-outlined text-[16px]">gavel</span>
                    <?php echo htmlspecialchars($sop_badge, ENT_QUOTES, 'UTF-8'); ?>
                </span>
                <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-surface font-bold"><?php echo htmlspecialchars($sop_title, ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-xs"><?php echo htmlspecialchars($sop_subtitle, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <?php if ($user_role === 'superadmin'): ?>
            <div class="shrink-0">
                <a href="admin/superadmin.php#sop" class="px-4 py-2 bg-surface-container-high hover:bg-white border border-outline-variant text-primary font-bold text-xs rounded-xl shadow-xs transition-all inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[18px]">edit_document</span>
                    Edit SOP (Superadmin)
                </a>
            </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-lg items-start">
            <!-- Daftar Isi (sticky di desktop) -->
            <aside class="lg:col-span-3 lg:sticky lg:top-[5.5rem]">
                <div class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-md">
                    <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider mb-sm font-bold">Daftar Isi</p>
                    <nav class="flex flex-col gap-1 text-body-sm font-body-sm">
                        <?php foreach ($sop_sections as $sec): 
                            $nav_id = htmlspecialchars($sec['id'] ?? '', ENT_QUOTES, 'UTF-8');
                            $nav_text = htmlspecialchars($sec['nav_title'] ?? $sec['title'] ?? '', ENT_QUOTES, 'UTF-8');
                        ?>
                            <a href="#<?php echo $nav_id; ?>" class="px-sm py-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors">
                                <?php echo $nav_text; ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>
            </aside>

            <!-- Konten SOP -->
            <div class="lg:col-span-9 flex flex-col gap-md">
                <?php foreach ($sop_sections as $sec): 
                    $sec_id = htmlspecialchars($sec['id'] ?? 'sop-' . uniqid(), ENT_QUOTES, 'UTF-8');
                    $sec_title = htmlspecialchars($sec['title'] ?? '', ENT_QUOTES, 'UTF-8');
                    $sec_style = $sec['style'] ?? 'default';

                    $card_class = "glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg scroll-mt-24";
                    $title_class = "font-headline-md text-headline-md text-on-surface font-bold mb-md";
                    
                    if ($sec_style === 'warning') {
                        $card_class = "glass-card bg-error-container/20 border border-error/30 rounded-2xl p-lg scroll-mt-24";
                        $title_class = "font-headline-md text-headline-md text-error font-bold mb-sm flex items-center gap-sm";
                    } elseif ($sec_style === 'primary') {
                        $card_class = "glass-card bg-primary-container/40 border border-primary/20 rounded-2xl p-lg scroll-mt-24";
                        $title_class = "font-headline-md text-headline-md text-on-surface font-bold mb-xs";
                    } elseif ($sec_style === 'centered') {
                        $card_class = "glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg scroll-mt-24 text-center";
                        $title_class = "font-headline-md text-headline-md text-on-surface font-bold mb-sm";
                    }
                ?>
                <section id="<?php echo $sec_id; ?>" class="<?php echo $card_class; ?>">
                    <?php if (!empty($sec_title)): ?>
                        <h3 class="<?php echo $title_class; ?>">
                            <?php if ($sec_style === 'warning'): ?>
                                <span class="material-symbols-outlined">block</span>
                            <?php endif; ?>
                            <?php echo $sec_title; ?>
                        </h3>
                    <?php endif; ?>
                    <div class="sop-content space-y-3">
                        <?php echo $sec['content'] ?? ''; ?>
                    </div>
                </section>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="h-md"></div>
    </div>
    <div class="mt-auto shrink-0 w-full">
        <?php include 'partials/footer.php'; ?>
    </div>
</main>
</body>
</html>