<?php
/**
 * partials/topnav-useradmin.php
 * Navbar area pengguna/admin. Menu sengaja belum memiliki tujuan halaman.
 */
$useradmin_name = isset($userName) && $userName !== '' ? $userName : 'Pengguna';
$useradmin_role = isset($userRole) && $userRole !== '' ? ucfirst($userRole) : 'Intern';
?>
<header class="sticky top-0 z-40 border-b border-outline-variant bg-surface-container-lowest/95 shadow-sm backdrop-blur">
	<div class="mx-auto flex min-h-16 w-full max-w-container-max items-center justify-between gap-4 px-gutter">
		<div class="flex min-w-0 items-center gap-3">
			<button type="button" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-primary md:hidden" aria-label="Buka menu" aria-disabled="true">
				<span class="material-symbols-outlined">menu</span>
			</button>

			<div class="flex min-w-0 items-center gap-3">
				<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary text-on-primary shadow-sm">
					<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">work_history</span>
				</div>
				<div class="min-w-0">
					<div class="truncate font-headline-md text-headline-md font-bold text-primary">Kedayweb</div>
					<div class="hidden text-[11px] font-semibold uppercase tracking-[0.12em] text-on-surface-variant sm:block">Internship Portal</div>
				</div>
			</div>
		</div>

		<nav class="hidden items-center gap-1 lg:flex" aria-label="Navigasi utama">
			<button type="button" class="inline-flex items-center gap-2 rounded-lg bg-primary-container px-3 py-2 text-sm font-semibold text-on-primary transition-colors" aria-current="page" aria-disabled="true">
				<span class="material-symbols-outlined text-[19px]">dashboard</span>
				<span>Dashboard</span>
			</button>
			<button type="button" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-primary" aria-disabled="true">
				<span class="material-symbols-outlined text-[19px]">assignment</span>
				<span>Tugas</span>
			</button>
			<button type="button" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-primary" aria-disabled="true">
				<span class="material-symbols-outlined text-[19px]">event_available</span>
				<span>Kehadiran</span>
			</button>
			<button type="button" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-primary" aria-disabled="true">
				<span class="material-symbols-outlined text-[19px]">folder_open</span>
				<span>Proyek</span>
			</button>
		</nav>

		<div class="flex shrink-0 items-center gap-2 sm:gap-3">
			<button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-primary" aria-label="Notifikasi" aria-disabled="true">
				<span class="material-symbols-outlined">notifications</span>
				<span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-tertiary" aria-hidden="true"></span>
			</button>
			<div class="hidden h-8 w-px bg-outline-variant sm:block" aria-hidden="true"></div>
			<button type="button" class="flex items-center gap-2 rounded-xl px-1.5 py-1.5 text-left transition-colors hover:bg-surface-container-high" aria-label="Profil pengguna" aria-disabled="true">
				<span class="flex h-9 w-9 items-center justify-center rounded-full bg-secondary-fixed text-primary">
					<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">account_circle</span>
				</span>
				<span class="hidden max-w-32 sm:block">
					<span class="block truncate text-sm font-bold text-on-surface"><?php echo htmlspecialchars($useradmin_name, ENT_QUOTES, 'UTF-8'); ?></span>
					<span class="block truncate text-xs text-on-surface-variant"><?php echo htmlspecialchars($useradmin_role, ENT_QUOTES, 'UTF-8'); ?></span>
				</span>
				<span class="material-symbols-outlined hidden text-on-surface-variant sm:block">expand_more</span>
			</button>
		</div>
	</div>
</header>
