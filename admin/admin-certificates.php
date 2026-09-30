<?php
require_once __DIR__ . '/../session.php';
require_admin();
require_once __DIR__ . '/../Login/koneksi.php';

// Auto sync certificates for all intern users
if ($conn) {
    sync_all_intern_certificates($conn);
}

// Ambil daftar user intern untuk dropdown
$users = [];
$q = mysqli_query($conn, "SELECT id, username FROM users WHERE role='intern' ORDER BY username ASC");
while ($row = mysqli_fetch_assoc($q)) $users[] = $row;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <title>Kelola Sertifikat – Admin Kedayweb</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="../shared-config.js"></script>
    <link rel="stylesheet" href="../style.css"/>
    <style>
        .filled-icon { font-variation-settings:'FILL' 1,'wght' 400,'GRAD' 0,'opsz' 24; }
        dialog::backdrop { background: rgba(0,0,0,.5); backdrop-filter: blur(4px); }
        dialog { border-radius: 1rem; border: none; box-shadow: 0 25px 50px -12px rgba(0,0,0,.25); }
        @keyframes fadeIn { from{opacity:0;transform:scale(.96)} to{opacity:1;transform:scale(1)} }
        dialog[open] { animation: fadeIn .2s ease; }
    </style>
</head>
<body class="bg-background text-on-surface font-body-md flex h-screen overflow-hidden">

<?php
$active           = 'certificates';
$sidebar_subtitle = 'Admin Panel';
include '../partials/sidebar-admin.php';
?>

<main class="flex-1 flex flex-col md:ml-[16.5rem] h-screen overflow-y-auto">
    <!-- Header -->
    <header class="w-full h-20 bg-surface-container-lowest border-b border-outline-variant sticky top-0 z-10 flex justify-between items-center px-6 shrink-0">
        <h2 class="font-headline-lg font-bold text-on-surface flex items-center gap-2">
            <button onclick="toggleMobileSidebar()" class="md:hidden text-on-surface hover:text-primary focus:outline-none flex items-center mr-1 p-1 rounded-lg hover:bg-surface-container-high" aria-label="Toggle Sidebar">
                <span class="material-symbols-outlined text-2xl">menu</span>
            </button>
            <span class="material-symbols-outlined filled-icon text-primary">workspace_premium</span>
            Kelola Sertifikat
        </h2>
        <div class="flex items-center gap-3">
            <button id="btn-add" onclick="openModal()"
                    class="bg-primary text-on-primary px-4 py-2 rounded-xl font-label-md flex items-center gap-2 hover:opacity-90 transition-all active:scale-95 shadow cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">add_circle</span>
                <span>Tambah Sertifikat</span>
            </button>
            <div class="flex items-center gap-sm p-1.5 px-3 rounded-full border border-outline-variant bg-surface-bright shadow-2xs">
                <div class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container overflow-hidden shrink-0">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">account_circle</span>
                </div>
                <span class="hidden sm:inline-block font-label-md"><?php echo htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="../logout.php" class="text-error hover:text-red-700 hover:bg-red-50 p-1.5 rounded-full transition-colors flex items-center justify-center" title="Keluar" aria-label="Keluar"><span class="material-symbols-outlined text-[20px]">logout</span></a>
            </div>
        </div>
    </header>

    <div class="p-6 space-y-6 flex-1">
        <!-- Stats Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-4">
                <p class="font-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Total</p>
                <p id="stat-total" class="font-headline-lg text-primary font-bold">—</p>
            </div>
            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-4">
                <p class="font-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Aktif</p>
                <p id="stat-active" class="font-headline-lg text-[#166534] font-bold">—</p>
            </div>
            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-4">
                <p class="font-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Dicabut</p>
                <p id="stat-revoked" class="font-headline-lg text-error font-bold">—</p>
            </div>
            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-4">
                <p class="font-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Link Verifikasi</p>
                <a href="../verification.php" target="_blank"
                   class="text-primary font-semibold text-sm flex items-center gap-1 hover:underline">
                    <span class="material-symbols-outlined text-[16px]">open_in_new</span> Buka Halaman
                </a>
            </div>
        </div>

        <!-- Search + Table -->
        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant overflow-hidden">
            <div class="p-4 border-b border-outline-variant flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                <h3 class="font-label-md text-on-surface uppercase tracking-wider font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">list_alt</span>
                    Daftar Sertifikat
                </h3>
                <div class="relative w-full sm:w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                    <input id="search-input" type="text" placeholder="Cari nama / ID / posisi…"
                           class="w-full pl-9 pr-4 py-2 rounded-xl border border-outline-variant bg-surface focus:ring-2 focus:ring-primary text-sm"
                           oninput="loadCertificates()"/>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-surface-container-low text-on-surface-variant font-label-sm uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">ID Sertifikat</th>
                            <th class="px-4 py-3 text-left">Nama Intern</th>
                            <th class="px-4 py-3 text-left">Posisi</th>
                            <th class="px-4 py-3 text-left">Universitas</th>
                            <th class="px-4 py-3 text-center">TOGGLE</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Terbit</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="cert-table-body" class="divide-y divide-outline-variant">
                        <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">Memuat data…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <div class="mt-auto shrink-0 w-full">
        <?php include '../partials/footer.php'; ?>
    </div>
</main>

<!-- ── MODAL Add / Edit ── -->
<dialog id="cert-modal" class="w-full max-w-2xl p-0">
    <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant bg-surface-container-low">
        <h3 id="modal-title" class="font-headline-md font-bold">Tambah Sertifikat</h3>
        <button onclick="closeModal()" class="p-2 rounded-full hover:bg-surface-container-high transition-colors">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    <form id="cert-form" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto bg-surface" onsubmit="saveCertificate(event)">
        <input type="hidden" id="f-id" name="id"/>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">ID Sertifikat <span class="text-error">*</span></label>
                <input id="f-cert-id" name="certificate_id" type="text" required placeholder="IS-2024-001"
                       class="input-field w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">User Intern (opsional)</label>
                <select id="f-user-id" name="user_id"
                        class="input-field w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm">
                    <option value="">— Tidak terhubung ke user —</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Nama Intern <span class="text-error">*</span></label>
                <input id="f-name" name="intern_name" type="text" required
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Posisi Magang <span class="text-error">*</span></label>
                <input id="f-position" name="intern_position" type="text" required
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Universitas</label>
                <input id="f-university" name="university" type="text"
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Jurusan</label>
                <input id="f-major" name="major" type="text"
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Tanggal Mulai <span class="text-error">*</span></label>
                <input id="f-start" name="start_date" type="date" required
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Tanggal Selesai <span class="text-error">*</span></label>
                <input id="f-end" name="end_date" type="date" required
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Tanggal Terbit <span class="text-error">*</span></label>
                <input id="f-issue" name="issue_date" type="date" required
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Nilai Akhir</label>
                <input id="f-grade" name="final_grade" type="text" placeholder="A, A+, B+"
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
        </div>

        <!-- Scores -->
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Skor Teknis (0-100)</label>
                <input id="f-tech" name="score_technical" type="number" min="0" max="100" value="0"
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Skor Disiplin (0-100)</label>
                <input id="f-disc" name="score_discipline" type="number" min="0" max="100" value="0"
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Skor Sikap (0-100)</label>
                <input id="f-att" name="score_attitude" type="number" min="0" max="100" value="0"
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Nama Pembimbing</label>
                <input id="f-supervisor" name="supervisor_name" type="text"
                       class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm"/>
            </div>
            <div>
                <label class="block font-label-sm text-on-surface-variant mb-1">Status</label>
                <select id="f-status" name="status"
                        class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm">
                    <option value="active">Aktif</option>
                    <option value="revoked">Dicabut</option>
                </select>
            </div>
        </div>
        <div>
            <label class="block font-label-sm text-on-surface-variant mb-1">Catatan</label>
            <textarea id="f-notes" name="notes" rows="2"
                      class="w-full rounded-xl border border-outline-variant px-3 py-2 bg-surface-container-lowest focus:ring-2 focus:ring-primary text-sm resize-none"></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2 border-t border-outline-variant">
            <button type="button" onclick="closeModal()"
                    class="px-4 py-2 rounded-xl border border-outline hover:bg-surface-container-low font-label-md transition-colors">
                Batal
            </button>
            <button type="submit" id="save-btn"
                    class="px-6 py-2 rounded-xl bg-primary text-on-primary font-label-md hover:opacity-90 transition-all flex items-center gap-2 shadow">
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span id="save-label">Simpan</span>
            </button>
        </div>
    </form>
</dialog>

<!-- ── Confirm Delete Modal ── -->
<dialog id="del-modal" class="w-full max-w-sm p-6 text-center bg-surface">
    <div class="w-16 h-16 rounded-full bg-error-container flex items-center justify-center mx-auto mb-4">
        <span class="material-symbols-outlined text-error text-3xl filled-icon">delete_forever</span>
    </div>
    <h3 class="font-headline-md font-bold mb-2">Hapus Sertifikat?</h3>
    <p class="text-on-surface-variant text-sm mb-6">Tindakan ini tidak dapat dibatalkan.</p>
    <div class="flex gap-3 justify-center">
        <button onclick="document.getElementById('del-modal').close()"
                class="px-4 py-2 rounded-xl border border-outline font-label-md hover:bg-surface-container-low">Batal</button>
        <button id="confirm-del-btn"
                class="px-4 py-2 rounded-xl bg-error text-on-error font-label-md hover:opacity-90">Hapus</button>
    </div>
</dialog>

<script>
let editingId = null;

// ── Load certificates ──────────────────────────────────────────────────────
async function loadCertificates() {
    const search = document.getElementById('search-input').value;
    const res    = await fetch(`../certificate-api.php?action=list&search=${encodeURIComponent(search)}`);
    const json   = await res.json();
    const tbody  = document.getElementById('cert-table-body');

    if (!json.success) {
        tbody.innerHTML = `<tr><td colspan="8" class="px-4 py-8 text-center text-error">${json.message}</td></tr>`;
        return;
    }

    const data = json.data;

    // Stats
    document.getElementById('stat-total').textContent   = data.length;
    document.getElementById('stat-active').textContent  = data.filter(d => d.status === 'active').length;
    document.getElementById('stat-revoked').textContent = data.filter(d => d.status === 'revoked').length;

    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">Tidak ada sertifikat ditemukan.</td></tr>`;
        return;
    }

    tbody.innerHTML = data.map(c => `
        <tr class="hover:bg-surface-container-low transition-colors">
            <td class="px-4 py-3 font-mono text-sm font-bold text-primary">${c.certificate_id}</td>
            <td class="px-4 py-3 font-medium">${c.intern_name}</td>
            <td class="px-4 py-3 text-on-surface-variant">${c.intern_position}</td>
            <td class="px-4 py-3 text-on-surface-variant text-xs">${c.university || '—'}</td>
            <td class="px-4 py-3 text-center">
                <label class="relative inline-flex items-center cursor-pointer select-none">
                    <input type="checkbox" class="sr-only peer" ${c.status === 'active' ? 'checked' : ''} onchange="toggleSingleCertStatus(${c.id}, this.checked ? 'active' : 'revoked')"/>
                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                </label>
            </td>
            <td class="px-4 py-3 text-center">
                ${c.status === 'active'
                    ? '<span class="bg-[#dcfce7] text-[#166534] px-2.5 py-1 rounded-full text-xs font-bold">Aktif</span>'
                    : '<span class="bg-error-container text-error px-2.5 py-1 rounded-full text-xs font-bold">Dicabut</span>'}
            </td>
            <td class="px-4 py-3 text-center text-xs text-on-surface-variant">${c.issue_fmt}</td>
            <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center gap-1">
                    <a href="../verification.php?id=${encodeURIComponent(c.certificate_id)}" target="_blank"
                       class="p-1.5 rounded-lg hover:bg-primary-fixed text-primary transition-colors" title="Lihat">
                        <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                    </a>
                    <button onclick="editCertificate(${c.id})"
                            class="p-1.5 rounded-lg hover:bg-surface-container-high text-on-surface-variant transition-colors" title="Edit">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                    </button>
                    <button onclick="confirmDelete(${c.id})"
                            class="p-1.5 rounded-lg hover:bg-error-container text-error transition-colors" title="Hapus">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ── Modal helpers ──────────────────────────────────────────────────────────
function openModal(data = null) {
    editingId = data ? data.id : null;
    document.getElementById('modal-title').textContent = data ? 'Edit Sertifikat' : 'Tambah Sertifikat';
    document.getElementById('save-label').textContent  = data ? 'Perbarui' : 'Simpan';
    const form = document.getElementById('cert-form');
    form.reset();

    // Map fields
    const map = {
        'f-id':         data?.id ?? '',
        'f-cert-id':    data?.certificate_id ?? '',
        'f-user-id':    data?.user_id ?? '',
        'f-name':       data?.intern_name ?? '',
        'f-position':   data?.intern_position ?? '',
        'f-university': data?.university ?? '',
        'f-major':      data?.major ?? '',
        'f-start':      data?.start_date ?? '',
        'f-end':        data?.end_date ?? '',
        'f-issue':      data?.issue_date ?? '',
        'f-grade':      data?.final_grade ?? '',
        'f-tech':       data?.score_technical ?? 0,
        'f-disc':       data?.score_discipline ?? 0,
        'f-att':        data?.score_attitude ?? 0,
        'f-supervisor': data?.supervisor_name ?? '',
        'f-status':     data?.status ?? 'active',
        'f-notes':      data?.notes ?? '',
    };
    for (const [id, val] of Object.entries(map)) {
        const el = document.getElementById(id);
        if (el) el.value = val ?? '';
    }
    // Disable cert-id field on edit
    document.getElementById('f-cert-id').readOnly = !!data;

    document.getElementById('cert-modal').showModal();
}

function closeModal() {
    document.getElementById('cert-modal').close();
}

async function editCertificate(id) {
    const res  = await fetch(`../certificate-api.php?action=get&id=${id}`);
    const json = await res.json();
    if (json.success) openModal(json.data);
}

// ── Save (create / update) ─────────────────────────────────────────────────
async function saveCertificate(e) {
    e.preventDefault();
    const btn = document.getElementById('save-btn');
    btn.disabled = true;

    const action   = editingId ? 'update' : 'create';
    const formData = new FormData(document.getElementById('cert-form'));
    formData.set('action', action);
    if (!editingId) formData.delete('id');

    const res  = await fetch('../certificate-api.php', { method: 'POST', body: formData });
    const json = await res.json();

    btn.disabled = false;
    if (json.success) {
        closeModal();
        loadCertificates();
        showToast(json.message, 'success');
    } else {
        showToast(json.message, 'error');
    }
}

// ── Delete ─────────────────────────────────────────────────────────────────
function confirmDelete(id) {
    document.getElementById('del-modal').showModal();
    document.getElementById('confirm-del-btn').onclick = async () => {
        document.getElementById('del-modal').close();
        const fd = new FormData();
        fd.set('action', 'delete');
        fd.set('id', id);
        const res  = await fetch('../certificate-api.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
            loadCertificates();
            showToast(json.message, 'success');
        } else {
            showToast(json.message, 'error');
        }
    };
}


// ── Single Certificate Status Toggle ───────────────────────────────────────
async function toggleSingleCertStatus(id, newStatus) {
    try {
        const fd = new FormData();
        fd.set('action', 'toggle_single_status');
        fd.set('id', id);
        fd.set('status', newStatus);
        const res = await fetch('../certificate-api.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
            loadCertificates();
            showToast(json.message, newStatus === 'active' ? 'success' : 'error');
        } else {
            showToast(json.message || 'Gagal memperbarui status sertifikat', 'error');
        }
    } catch (e) {
        showToast('Terjadi kesalahan server', 'error');
    }
}

// ── Toast notification ─────────────────────────────────────────────────────
function showToast(msg, type = 'success') {
    const t = document.createElement('div');
    const bg = type === 'success' ? 'bg-[#166534] text-white' : 'bg-error text-on-error';
    const icon = type === 'success' ? 'check_circle' : 'error';
    t.className = `fixed bottom-6 right-6 z-[9999] flex items-center gap-2 px-4 py-3 rounded-xl shadow-xl text-sm font-semibold ${bg} transition-all`;
    t.innerHTML = `<span class="material-symbols-outlined text-[20px] filled-icon">${icon}</span>${msg}`;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 400); }, 3000);
}

// ── Init ───────────────────────────────────────────────────────────────────
loadCertificates();
</script>
</body>
</html>
