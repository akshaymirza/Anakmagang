<?php
require_once __DIR__ . '/../session.php';

$active = 'dashboard';
$show_admin_link = true;
$sidebar_title = 'Kedayweb';
$sidebar_subtitle = 'Admin Panel';
$mobile_title = 'Kedayweb';
$nav_icon = 'work_history';
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Partial Preview - Kedayweb</title>
	<link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="../style.css">
	<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
	<script src="../shared-config.js"></script>
	<style>
		body { font-family: Inter, sans-serif; }
		.partial-preview-block { position: relative; overflow: hidden; }
		.partial-preview-block > header { position: static !important; }
		.partial-preview-block > aside { position: static !important; display: flex !important; width: 100% !important; height: auto !important; min-height: 18rem; }
		.partial-preview-block > aside nav { max-height: 20rem; }
	</style>
</head>
<body class="min-h-screen bg-background text-on-surface">
	<main class="mx-auto max-w-container-max space-y-8 px-gutter py-8">
		<section>
			<p class="mb-1 text-xs font-bold uppercase tracking-[0.14em] text-primary">Kedayweb InternSpace</p>
			<h1 class="font-headline-lg text-on-surface">Preview Semua Partial</h1>
			<p class="mt-2 max-w-2xl text-sm text-on-surface-variant">Halaman ini hanya untuk melihat seluruh komponen partial secara bersamaan.</p>
		</section>

		<section class="space-y-3">
			<h2 class="text-lg font-bold text-on-surface">Topnav Public</h2>
			<div class="partial-preview-block rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
				<?php include __DIR__ . '/topnav-public.php'; ?>
			</div>
		</section>

		<section class="space-y-3">
			<h2 class="text-lg font-bold text-on-surface">Topnav User/Admin</h2>
			<div class="partial-preview-block rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
				<?php include __DIR__ . '/topnav-useradmin.php'; ?>
			</div>
		</section>

		<section class="space-y-3">
			<h2 class="text-lg font-bold text-on-surface">Topnav Mobile</h2>
			<div class="partial-preview-block rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
				<?php include __DIR__ . '/topnav-mobile.php'; ?>
			</div>
		</section>

		<section class="space-y-3">
			<h2 class="text-lg font-bold text-on-surface">Sidebar Intern</h2>
			<div class="partial-preview-block rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
				<?php include __DIR__ . '/sidebar-intern.php'; ?>
			</div>
		</section>

		<section class="space-y-3">
			<h2 class="text-lg font-bold text-on-surface">Sidebar Admin</h2>
			<div class="partial-preview-block rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
				<?php include __DIR__ . '/sidebar-admin.php'; ?>
			</div>
		</section>

		<section class="space-y-3">
			<h2 class="text-lg font-bold text-on-surface">Footer</h2>
			<div class="partial-preview-block rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
				<?php include __DIR__ . '/footer.php'; ?>
			</div>
		</section>

		<section class="space-y-3">
			<h2 class="text-lg font-bold text-on-surface">Confirmation Modal</h2>
			<div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm">
				<button type="button" onclick="showConfirmModal({ type: 'info', title: 'Preview Modal', message: 'Partial confirmation modal berhasil dipanggil.', confirmText: 'Tutup' })" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-on-primary transition-colors hover:bg-primary-container">
					<span class="material-symbols-outlined text-[18px]">visibility</span>
					Tampilkan Modal
				</button>
			</div>
			<?php include __DIR__ . '/confirm-modal.php'; ?>
		</section>
	</main>
</body>
</html>
