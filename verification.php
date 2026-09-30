<!DOCTYPE html>
<html class="h-full" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Kedayweb – Verifikasi Sertifikat Magang</title>
    <meta name="description" content="Verifikasi keaslian sertifikat magang dari Kedayweb secara online menggunakan ID sertifikat."/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Geist:wght@400;500;600;700;800;900&family=Montserrat:wght@800&display=swap" rel="stylesheet"/>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="shared-config.js"></script>
    <style>
        .filled-icon { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .soft-shadow  { box-shadow: 0 10px 15px -3px rgba(30,58,138,.05), 0 4px 6px -2px rgba(30,58,138,.02); }
        #search-section { display: flex; }
        #result-section,#error-section { display: none; }
        .bar-fill { transition: width 1.2s cubic-bezier(.4,0,.2,1); }
        @keyframes fadeSlideUp { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }
        .animate-in { animation: fadeSlideUp .5s ease both; }
        .spin { animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Sertifikat visual (template: uploads/Certificate/) ──────────
           Semua posisi memakai % / cqw agar tetap proporsional di layar apa pun.
           Angka posisi diambil dari sertifikat_pkl_kedayweb.php:
             nama  → top 45%, tengah, lebar 50%, font 26px pada lebar 1000px (= 2.6cqw)
             QR    → left 43.7%, bottom 11.6%, 12.5% x 17.7% (persegi)          */
        .cert-canvas {
            position: relative; width: 100%; aspect-ratio: 297 / 210;
            container-type: inline-size; overflow: hidden; background: #f5f3e7;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,.25);
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .cert-bg { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: fill;
                   user-select: none; -webkit-user-drag: none; pointer-events: none; }
        .cert-name {
            position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%);
            width: 50%; text-align: center; white-space: nowrap; z-index: 10;
            font-family: 'Montserrat', sans-serif; font-weight: 800; color: #0f172a;
            font-size: calc(var(--cert-fs, 2.6) * 1cqw); line-height: 1.25;
            text-transform: capitalize;
        }
        .cert-qr {
            position: absolute; left: 43.7%; bottom: 11.6%; width: 12.5%; height: 17.7%;
            display: flex; align-items: center; justify-content: center; z-index: 20;
            background: #fff; border-radius: .8cqw; padding: .9cqw; box-sizing: border-box;
        }
        .cert-qr img { width: 100%; height: 100%; object-fit: contain; display: block; }

        /* ── Cetak / Simpan PDF: hanya sertifikat, A4 landscape, tanpa margin ── */
        @page { size: A4 landscape; margin: 0; }
        @media print {
            html, body { height: 210mm !important; overflow: hidden !important; margin: 0 !important; background: #fff !important; }
            body * { visibility: hidden !important; }
            #result-section { animation: none !important; transform: none !important; }
            #cert-preview, #cert-preview * { visibility: visible !important; }
            #cert-preview {
                position: fixed !important; left: 0; top: 0; z-index: 9999;
                width: 297mm !important; height: 210mm !important; max-width: none !important;
                aspect-ratio: auto !important; margin: 0 !important; box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="bg-background min-h-screen flex flex-col font-body-md text-body-md text-on-surface">
<?php
$nav_icon      = 'school';
$nav_cta_label = 'Portal Login';
$nav_cta_href  = 'Login/login.php';
include 'partials/topnav-public.php';
?>

<!-- Main Content -->
<main id="main-content" class="flex-1 w-full max-w-container-max mx-auto px-md md:px-gutter py-xl md:py-3xl">

    <!-- ── SEARCH SECTION (initial) ── -->
    <div id="search-section" class="flex flex-col items-center justify-center min-h-[60vh]">
        <div class="w-20 h-20 rounded-full bg-primary-fixed flex items-center justify-center text-primary mb-lg">
            <span class="material-symbols-outlined filled-icon text-4xl">verified</span>
        </div>
        <h1 class="font-headline-xl text-headline-xl text-on-surface mb-md text-center">Verifikasi Sertifikat</h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant max-w-lg text-center mb-xl">
            Masukkan ID sertifikat untuk memverifikasi keasliannya melalui sistem Kedayweb.
        </p>
        <div class="w-full max-w-md flex gap-sm">
            <div class="flex-1 relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
                <input id="cert-id-input"
                       class="w-full bg-surface-container-lowest border border-outline-variant pl-10 pr-4 py-3 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all font-body-md text-body-md text-on-surface uppercase"
                       placeholder="Contoh: IS-2024-001" type="text" autocomplete="off"/>
            </div>
            <button id="verify-btn" onclick="verifyCertificate()"
                    class="bg-primary text-on-primary px-xl py-3 rounded-xl font-label-md text-label-md hover:opacity-90 transition-all active:scale-95 shadow-sm flex items-center gap-2 min-w-[100px] justify-center">
                <span id="verify-icon" class="material-symbols-outlined text-[20px]">verified_user</span>
                <span id="verify-label">Verifikasi</span>
            </button>
        </div>
        <p class="font-body-sm text-body-sm text-on-surface-variant mt-md">
            Coba ID demo: <code class="bg-surface-container-high px-2 py-1 rounded font-mono text-primary font-semibold">IS-2024-001</code>
        </p>
    </div>

    <!-- ── ERROR SECTION ── -->
    <div id="error-section" class="flex flex-col items-center justify-center min-h-[50vh] animate-in">
        <div class="w-20 h-20 rounded-full bg-error-container flex items-center justify-center mb-lg">
            <span class="material-symbols-outlined text-error text-4xl filled-icon">cancel</span>
        </div>
        <h2 class="font-headline-md text-on-surface font-bold mb-sm text-center" id="error-title">Sertifikat Tidak Ditemukan</h2>
        <p class="font-body-md text-on-surface-variant text-center max-w-sm mb-xl" id="error-msg">ID sertifikat yang Anda masukkan tidak ada dalam sistem kami.</p>
        <button onclick="resetVerification()"
                class="bg-primary text-on-primary px-xl py-3 rounded-xl font-label-md hover:opacity-90 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined">arrow_back</span> Coba Lagi
        </button>
    </div>

    <!-- ── RESULT SECTION (verified) ── -->
    <div id="result-section" class="hidden animate-in">
        <!-- Trust Badge -->
        <div class="w-full bg-surface-container-lowest rounded-2xl border border-outline-variant p-md md:p-lg mb-xl flex flex-col md:flex-row items-center justify-between gap-md soft-shadow">
            <div class="flex items-center gap-md">
                <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center">
                    <span class="material-symbols-outlined filled-icon text-primary text-3xl">verified</span>
                </div>
                <div>
                    <h1 class="font-headline-lg text-primary font-bold">Sertifikat Valid</h1>
                    <p class="text-on-surface-variant font-body-sm mt-1">Dokumen ini terverifikasi melalui sistem Kedayweb.</p>
                </div>
            </div>
            <div class="flex items-center gap-sm">
                <span class="bg-[#dcfce7] text-[#166534] px-4 py-2 rounded-full flex items-center gap-2 font-bold text-sm shadow-sm">
                    <span class="material-symbols-outlined text-[20px] filled-icon">check_circle</span>
                    Status: Aktif
                </span>
            </div>
        </div>

        <!-- Bento Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
            <!-- Certificate Preview (Left) -->
            <div class="lg:col-span-7 bg-surface-container-lowest rounded-2xl border border-outline-variant overflow-hidden hover:soft-shadow transition-shadow duration-300 self-start">
                <div class="p-4 bg-surface-container-low border-b border-outline-variant flex justify-between items-center">
                    <span class="font-label-md text-on-surface-variant uppercase tracking-wider">Preview Sertifikat</span>
                    <button onclick="downloadCertificate()"
                            class="text-primary hover:bg-primary-container hover:text-on-primary-container px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5 text-sm font-semibold" title="Cetak / Simpan PDF">
                        <span class="material-symbols-outlined text-[20px]">download</span> Unduh PDF
                    </button>
                </div>
                <!-- Certificate Design: gambar latar + nama + QR (lihat uploads/Certificate/) -->
                <div class="p-3 md:p-lg bg-surface">
                    <div id="cert-preview" class="cert-canvas" role="img" aria-label="Preview sertifikat magang">
                        <img class="cert-bg" src="uploads/Certificate/Sertifikat_Ril.png" alt="" draggable="false"/>
                        <div id="cert-name" class="cert-name">—</div>
                        <div id="cert-qr" class="cert-qr" style="display:none">
                            <img id="cert-qr-img" alt="QR verifikasi sertifikat"/>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 bg-surface-container-low border-t border-outline-variant flex flex-wrap justify-between items-center gap-2 text-xs text-on-surface-variant">
                    <span>ID Sertifikat: <b id="cert-id-display" class="font-mono text-on-surface">—</b>
                        <span class="text-emerald-600 font-semibold ml-1">✓ Terverifikasi</span></span>
                    <span id="cert-issue-display">—</span>
                </div>
            </div>

            <!-- Right Column -->
            <div class="lg:col-span-5 flex flex-col gap-gutter">
                <!-- Profile Card -->
                <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-lg hover:soft-shadow transition-shadow duration-300 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-primary-fixed rounded-bl-full opacity-20 -z-10"></div>
                    <div class="flex items-start gap-md mb-lg">
                        <div id="avatar-circle" class="w-16 h-16 rounded-xl bg-primary flex items-center justify-center text-on-primary text-2xl font-bold flex-shrink-0">?</div>
                        <div>
                            <h2 id="res-name" class="font-headline-md text-on-surface font-bold">—</h2>
                            <p class="font-body-md text-on-surface-variant flex items-center gap-1 mt-1">
                                <span class="material-symbols-outlined text-[16px]">school</span>
                                <span id="res-university">—</span>
                            </p>
                            <p id="res-major" class="font-body-sm text-secondary mt-1">—</p>
                        </div>
                    </div>
                    <div class="border-t border-outline-variant pt-md grid grid-cols-2 gap-md">
                        <div>
                            <span class="block font-label-sm text-on-surface-variant uppercase mb-1 text-[11px]">Posisi Magang</span>
                            <span id="res-position" class="font-body-md text-on-surface font-bold">—</span>
                        </div>
                        <div class="text-right">
                            <span class="block font-label-sm text-on-surface-variant uppercase mb-1 text-[11px]">Nilai Akhir</span>
                            <span id="res-grade" class="font-headline-lg text-primary font-bold">—</span>
                        </div>
                        <div>
                            <span class="block font-label-sm text-on-surface-variant uppercase mb-1 text-[11px]">Periode Magang</span>
                            <span id="res-period" class="font-body-sm text-on-surface">—</span>
                        </div>
                        <div class="text-right">
                            <span class="block font-label-sm text-on-surface-variant uppercase mb-1 text-[11px]">Tanggal Terbit</span>
                            <span id="res-issue" class="font-body-sm text-on-surface">—</span>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics Card -->
                <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-lg hover:soft-shadow transition-shadow duration-300 flex-1">
                    <h3 class="font-label-md text-on-surface uppercase tracking-wider mb-lg flex items-center gap-2 font-bold">
                        <span class="material-symbols-outlined text-primary">analytics</span>
                        Metrik Performa
                    </h3>
                    <div class="space-y-lg">
                        <!-- Technical -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-body-md text-on-surface font-medium flex items-center gap-2">
                                    <span class="material-symbols-outlined text-secondary text-[18px]">code</span>
                                    Kemampuan Teknis
                                </span>
                                <span id="score-tech-label" class="font-label-md text-primary bg-primary-fixed px-2 py-1 rounded-md font-bold">0/100</span>
                            </div>
                            <div class="w-full h-3 bg-slate-200 rounded-full overflow-hidden">
                                <div id="score-tech-bar" class="h-full bg-gradient-to-r from-primary-container to-primary rounded-full bar-fill" style="width:0%"></div>
                            </div>
                        </div>
                        <!-- Discipline -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-body-md text-on-surface font-medium flex items-center gap-2">
                                    <span class="material-symbols-outlined text-secondary text-[18px]">schedule</span>
                                    Kedisiplinan
                                </span>
                                <span id="score-disc-label" class="font-label-md text-primary bg-primary-fixed px-2 py-1 rounded-md font-bold">0/100</span>
                            </div>
                            <div class="w-full h-3 bg-slate-200 rounded-full overflow-hidden">
                                <div id="score-disc-bar" class="h-full bg-gradient-to-r from-primary-container to-primary rounded-full bar-fill" style="width:0%"></div>
                            </div>
                        </div>
                        <!-- Attitude -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-body-md text-on-surface font-medium flex items-center gap-2">
                                    <span class="material-symbols-outlined text-secondary text-[18px]">groups</span>
                                    Sikap & Kerjasama
                                </span>
                                <span id="score-att-label" class="font-label-md text-primary bg-primary-fixed px-2 py-1 rounded-md font-bold">0/100</span>
                            </div>
                            <div class="w-full h-3 bg-slate-200 rounded-full overflow-hidden">
                                <div id="score-att-bar" class="h-full bg-gradient-to-r from-primary-container to-primary rounded-full bar-fill" style="width:0%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-xl pt-md border-t border-outline-variant">
                        <button onclick="resetVerification()"
                                class="w-full bg-surface-bright border border-outline hover:border-primary hover:bg-surface-container-low text-primary font-label-md py-3 rounded-lg transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">search</span>
                            Verifikasi Sertifikat Lain
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'partials/footer.php'; ?>

<script src="qrcode.js"></script>
<script>
// Data sertifikat aktif (diisi setelah fetch)
let activeCert = null;

async function verifyCertificate() {
    const certId = document.getElementById('cert-id-input').value.trim().toUpperCase();
    if (!certId) {
        document.getElementById('cert-id-input').focus();
        return;
    }

    // Loading state
    setVerifyLoading(true);

    try {
        const res = await fetch(`certificate-api.php?action=verify&id=${encodeURIComponent(certId)}`);
        const data = await res.json();

        if (data.success) {
            activeCert = data;
            showResult(data);
        } else if (data.revoked) {
            showError('Sertifikat Dicabut', data.message);
        } else {
            showError('Sertifikat Tidak Ditemukan', data.message || 'ID sertifikat tidak ada dalam sistem.');
        }
    } catch (err) {
        showError('Terjadi Kesalahan', 'Gagal menghubungi server. Periksa koneksi internet Anda.');
    } finally {
        setVerifyLoading(false);
    }
}

function setVerifyLoading(loading) {
    const btn   = document.getElementById('verify-btn');
    const icon  = document.getElementById('verify-icon');
    const label = document.getElementById('verify-label');
    btn.disabled = loading;
    icon.textContent  = loading ? 'refresh' : 'verified_user';
    label.textContent = loading ? 'Memeriksa…' : 'Verifikasi';
    if (loading) icon.classList.add('spin'); else icon.classList.remove('spin');
}

function showResult(d) {
    // Fill cert preview (gambar latar + nama + QR)
    document.getElementById('cert-name').textContent       = d.intern_name;
    document.getElementById('cert-id-display').textContent = d.certificate_id;
    document.getElementById('cert-issue-display').textContent = 'Diterbitkan: ' + d.issue_date;
    renderCertQr(d.certificate_id);

    // Fill right panel
    document.getElementById('res-name').textContent       = d.intern_name;
    document.getElementById('res-university').textContent = d.university || 'Universitas';
    document.getElementById('res-major').textContent      = d.major || '';
    document.getElementById('res-position').textContent   = d.intern_position;
    document.getElementById('res-grade').textContent      = d.final_grade || '—';
    document.getElementById('res-period').textContent     = d.start_date + ' – ' + d.end_date;
    document.getElementById('res-issue').textContent      = d.issue_date;

    // Avatar initials
    const initials = d.intern_name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
    document.getElementById('avatar-circle').textContent = initials;

    // Score bars (animate after tiny delay so CSS transition kicks in)
    setTimeout(() => {
        setBar('tech',  d.score_technical);
        setBar('disc',  d.score_discipline);
        setBar('att',   d.score_attitude);
    }, 100);

    // Switch sections
    document.getElementById('search-section').style.display = 'none';
    document.getElementById('error-section').style.display  = 'none';
    document.getElementById('result-section').style.display = 'block';
    fitCertName();
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(fitCertName);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* Perkecil font nama otomatis jika terlalu panjang (maks. 50% lebar sertifikat) */
function fitCertName() {
    const box = document.getElementById('cert-preview');
    const el  = document.getElementById('cert-name');
    let fs = 2.6;
    box.style.setProperty('--cert-fs', fs);
    while (el.scrollWidth > el.clientWidth + 1 && fs > 1.2) {
        fs = Math.round((fs - 0.1) * 10) / 10;
        box.style.setProperty('--cert-fs', fs);
    }
}

/* QR code berisi tautan verifikasi: verification.php?id=<ID> */
function renderCertQr(certId) {
    const wrap = document.getElementById('cert-qr');
    const img  = document.getElementById('cert-qr-img');
    try {
        const u = new URL(window.location.href);
        u.hash = ''; u.search = '';
        u.searchParams.set('id', certId);
        const qr = qrcode(0, 'M');
        qr.addData(u.toString());
        qr.make();
        img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(qr.createSvgTag({ cellSize: 1, margin: 0, scalable: true }));
        wrap.style.display = 'flex';
    } catch (e) {
        wrap.style.display = 'none';   // library QR gagal dimuat → sertifikat tetap tampil tanpa QR
    }
}

function setBar(key, value) {
    const short = { tech: 'tech', disc: 'disc', att: 'att' };
    document.getElementById(`score-${key}-label`).textContent = value + '/100';
    document.getElementById(`score-${key}-bar`).style.width   = value + '%';
}

function showError(title, msg) {
    document.getElementById('error-title').textContent = title;
    document.getElementById('error-msg').textContent   = msg;
    document.getElementById('search-section').style.display = 'none';
    document.getElementById('result-section').style.display = 'none';
    document.getElementById('error-section').style.display  = 'flex';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetVerification() {
    document.getElementById('search-section').style.display = 'flex';
    document.getElementById('result-section').style.display = 'none';
    document.getElementById('error-section').style.display  = 'none';
    document.getElementById('cert-id-input').value = '';
    document.getElementById('cert-id-input').focus();
    activeCert = null;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Enter key support
document.getElementById('cert-id-input').addEventListener('keydown', e => {
    if (e.key === 'Enter') verifyCertificate();
});

// Auto-verify from URL param  ?id=IS-2024-001
(function() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');
    if (id) {
        document.getElementById('cert-id-input').value = id;
        verifyCertificate();
    }
})();

// Download / Print certificate → gunakan dialog cetak browser (pilih "Simpan sebagai PDF")
let _titleBackup = null;
function downloadCertificate() {
    if (!activeCert) return;
    _titleBackup = document.title;
    document.title = 'Sertifikat_' + activeCert.certificate_id;   // jadi nama file PDF default
    window.print();
}
window.addEventListener('afterprint', () => {
    if (_titleBackup !== null) { document.title = _titleBackup; _titleBackup = null; }
});
</script>
</body>
</html>
