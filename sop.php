<?php require_once __DIR__ . '/session.php'; require_login(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Kedayweb - SOP Peserta Magang</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&amp;family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="shared-config.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-background text-on-surface font-body-md flex h-screen overflow-hidden">
<?php $active = 'sop'; include 'partials/sidebar-intern.php'; ?>

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
                    <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">SOP Peserta Magang</h2>
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
        <div>
            <span class="inline-flex items-center gap-xs px-3 py-1 rounded-full bg-primary-container text-on-primary-container font-label-sm text-label-sm mb-sm">
                <span class="material-symbols-outlined text-[16px]">gavel</span>
                Standar Operasional Prosedur
            </span>
            <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-surface font-bold">SOP Peserta Magang Kedayweb</h2>
            <p class="font-body-md text-body-md text-on-surface-variant mt-xs">Kedayweb × Kulino House — wajib dibaca dan dipahami oleh seluruh peserta magang.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-lg items-start">
            <!-- Daftar Isi (sticky di desktop) -->
            <aside class="lg:col-span-3 lg:sticky lg:top-[5.5rem]">
                <div class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-md">
                    <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider mb-sm">Daftar Isi</p>
                    <nav class="flex flex-col gap-1 text-body-sm font-body-sm">
                        <a href="#sop-1" class="px-sm py-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors">1. Tujuan</a>
                        <a href="#sop-2" class="px-sm py-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors">2. Jam Kerja &amp; Kehadiran</a>
                        <a href="#sop-3" class="px-sm py-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors">3. Kebersihan &amp; Kerapihan</a>
                        <a href="#sop-4" class="px-sm py-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors">4. Etika &amp; Sikap Kerja</a>
                        <a href="#sop-5" class="px-sm py-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors">5. Prioritas Pelanggan</a>
                        <a href="#sop-6" class="px-sm py-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors">6. Fasilitas &amp; Barang</a>
                        <a href="#sop-7" class="px-sm py-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors">7. Koordinasi &amp; Komunikasi</a>
                        <a href="#sop-8" class="px-sm py-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors">8. Ibadah &amp; Nilai Kerja</a>
                        <a href="#sop-9" class="px-sm py-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors">9. Kerahasiaan &amp; Keamanan Data</a>
                        <a href="#sop-10" class="px-sm py-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors">10. Larangan</a>
                        <a href="#sop-11" class="px-sm py-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors">11. Evaluasi &amp; Sanksi</a>
                        <a href="#sop-nilai" class="px-sm py-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors">Nilai Utama</a>
                        <a href="#sop-penutup" class="px-sm py-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors">Penutup</a>
                    </nav>
                </div>
            </aside>

            <!-- Konten SOP -->
            <div class="lg:col-span-9 flex flex-col gap-md">

                <section id="sop-1" class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg scroll-mt-24">
                    <h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-sm">1. Tujuan</h3>
                    <p class="font-body-md text-body-md text-on-surface-variant">SOP ini dibuat untuk menciptakan lingkungan kerja yang profesional, disiplin, nyaman, dan produktif bagi seluruh peserta magang di Kedayweb serta mendukung operasional dan pelayanan di lingkungan Kulino House.</p>
                </section>

                <section id="sop-2" class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg scroll-mt-24">
                    <h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-md">2. Jam Kerja dan Kehadiran</h3>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">2.1 Jam Kerja</h4>
                    <div class="flex flex-wrap gap-sm mb-md">
                        <div class="flex items-center gap-sm px-md py-sm rounded-xl bg-surface-container-high">
                            <span class="material-symbols-outlined text-primary">login</span>
                            <div>
                                <p class="font-label-sm text-label-sm text-on-surface-variant">Masuk</p>
                                <p class="font-headline-sm text-headline-sm text-on-surface font-bold">08.00 WIB</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-sm px-md py-sm rounded-xl bg-surface-container-high">
                            <span class="material-symbols-outlined text-primary">logout</span>
                            <div>
                                <p class="font-label-sm text-label-sm text-on-surface-variant">Pulang</p>
                                <p class="font-headline-sm text-headline-sm text-on-surface font-bold">16.00 WIB</p>
                            </div>
                        </div>
                    </div>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant mb-md">
                        <li>Kehadiran harus tepat waktu.</li>
                        <li>Keterlambatan wajib dilaporkan kepada Senior/Pembimbing Magang.</li>
                    </ul>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">2.2 Absensi</h4>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant mb-md">
                        <li>Wajib melakukan absensi sesuai prosedur yang berlaku.</li>
                        <li>Ketidakhadiran harus disertai pemberitahuan dan alasan yang jelas kepada Senior/Pembimbing.</li>
                    </ul>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">2.3 Izin dan Keperluan Pribadi</h4>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant">
                        <li>Setiap izin keluar lokasi kerja, datang terlambat, atau pulang lebih awal wajib mendapatkan persetujuan dari Senior/Pembimbing terlebih dahulu.</li>
                        <li>Dilarang meninggalkan lokasi kerja tanpa pemberitahuan.</li>
                    </ul>
                </section>

                <section id="sop-3" class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg scroll-mt-24">
                    <h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-md">3. Kebersihan dan Kerapihan Area Kerja</h3>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">3.1 Kebersihan Lingkungan</h4>
                    <p class="font-body-md text-body-md text-on-surface-variant mb-1">Peserta magang wajib menjaga kebersihan area kerja, meliputi:</p>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant mb-md">
                        <li>Area dalam kantor Kedayweb.</li>
                        <li>Area luar kantor.</li>
                        <li>Area bersama di lingkungan Kulino House.</li>
                    </ul>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">3.2 Tanggung Jawab Kebersihan</h4>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant mb-md">
                        <li>Membersihkan meja kerja sebelum dan sesudah digunakan.</li>
                        <li>Membuang sampah pada tempatnya.</li>
                        <li>Menjaga kebersihan toilet, pantry, dan area umum.</li>
                        <li>Berpartisipasi dalam kegiatan kebersihan rutin yang ditentukan oleh perusahaan.</li>
                    </ul>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">3.3 Kerapian Barang</h4>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant mb-md">
                        <li>Setiap barang, alat, atau perlengkapan yang telah digunakan wajib dikembalikan ke tempat semula.</li>
                        <li>Menjaga kerapian meja kerja dan area sekitar.</li>
                        <li>Tidak meninggalkan peralatan kerja dalam kondisi berantakan.</li>
                    </ul>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">3.4 Kebersihan Setelah Makan dan Minum</h4>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant">
                        <li>Peserta magang wajib menjaga kebersihan setelah mengonsumsi makanan atau minuman.</li>
                        <li>Dilarang meninggalkan sampah, gelas, botol, kemasan makanan, tisu, atau peralatan makan bekas di area Kedayweb maupun Kulino House.</li>
                        <li>Setelah digunakan, seluruh sampah wajib dibuang pada tempat yang telah disediakan.</li>
                        <li>Area yang digunakan untuk makan atau minum harus dikembalikan dalam kondisi bersih dan rapi.</li>
                    </ul>
                </section>

                <section id="sop-4" class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg scroll-mt-24">
                    <h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-md">4. Etika dan Sikap Kerja</h3>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">4.1 Profesionalisme</h4>
                    <p class="font-body-md text-body-md text-on-surface-variant mb-1">Peserta magang wajib:</p>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant mb-md">
                        <li>Bersikap sopan, ramah, dan menghormati seluruh karyawan, pelanggan, tamu, serta rekan kerja.</li>
                        <li>Menjaga nama baik Kedayweb dan Kulino House.</li>
                        <li>Menggunakan bahasa yang santun dan profesional.</li>
                    </ul>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">4.2 Tanggung Jawab</h4>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant mb-md">
                        <li>Menyelesaikan tugas yang diberikan dengan penuh tanggung jawab.</li>
                        <li>Menjaga kejujuran dan integritas selama masa magang.</li>
                        <li>Bersedia menerima arahan, evaluasi, dan masukan dari Senior/Pembimbing.</li>
                    </ul>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">4.3 Penggunaan Gadget</h4>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant mb-md">
                        <li>Penggunaan ponsel pribadi diperbolehkan seperlunya dan tidak mengganggu pekerjaan.</li>
                        <li>Dilarang bermain game, menonton hiburan, atau aktivitas yang tidak berkaitan dengan pekerjaan selama jam kerja.</li>
                    </ul>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">4.4 Standar Berpakaian dan Penampilan</h4>
                    <p class="font-body-md text-body-md text-on-surface-variant mb-1">Untuk menjaga profesionalisme dan citra perusahaan, peserta magang wajib:</p>

                    <p class="font-label-md text-label-md text-on-surface font-semibold mt-sm mb-1">Ketentuan Pakaian</p>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant mb-sm">
                        <li>Menggunakan pakaian yang rapi, bersih, dan sopan.</li>
                        <li>Menggunakan kemeja, polo shirt berkerah, atau pakaian kerja yang pantas untuk lingkungan profesional.</li>
                        <li>Tidak diperbolehkan menggunakan kaos oblong, kaos tanpa kerah, atau pakaian yang terlalu santai selama jam kerja.</li>
                        <li>Tidak diperbolehkan menggunakan pakaian yang robek, lusuh, atau tidak pantas untuk lingkungan kerja.</li>
                    </ul>

                    <p class="font-label-md text-label-md text-on-surface font-semibold mt-sm mb-1">Ketentuan Alas Kaki</p>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant mb-sm">
                        <li>Wajib menggunakan sepatu selama berada di lingkungan kerja.</li>
                        <li>Tidak diperbolehkan menggunakan sandal jepit atau alas kaki yang tidak sesuai dengan lingkungan profesional, kecuali dalam kondisi tertentu yang telah mendapat izin.</li>
                    </ul>

                    <p class="font-label-md text-label-md text-on-surface font-semibold mt-sm mb-1">Penampilan Umum</p>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant">
                        <li>Menjaga kebersihan dan kerapian diri selama jam kerja.</li>
                        <li>Berpenampilan sopan dan mencerminkan profesionalisme Kedayweb serta Kulino House.</li>
                    </ul>
                </section>

                <section id="sop-5" class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg scroll-mt-24">
                    <h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-md">5. Prioritas Pelayanan Pelanggan Kulino House</h3>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">5.1 Mengutamakan Pelanggan</h4>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant mb-md">
                        <li>Peserta magang wajib menghormati dan mengutamakan kenyamanan pelanggan Kulino House.</li>
                        <li>Apabila peserta magang menggunakan area atau kursi yang dibutuhkan pelanggan, peserta magang wajib memberikan tempat tersebut kepada pelanggan.</li>
                    </ul>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">5.2 Penggunaan Area Bersama</h4>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant">
                        <li>Menjaga kenyamanan dan ketertiban area bersama.</li>
                        <li>Tidak mengganggu aktivitas pelanggan atau operasional Kulino House.</li>
                    </ul>
                </section>

                <section id="sop-6" class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg scroll-mt-24">
                    <h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-md">6. Penggunaan Fasilitas dan Barang</h3>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">6.1 Barang dan Inventaris</h4>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant mb-md">
                        <li>Seluruh inventaris kantor wajib digunakan dengan bijak dan sesuai kebutuhan pekerjaan.</li>
                        <li>Dilarang membawa pulang atau memindahkan barang tanpa izin.</li>
                    </ul>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">6.2 Area Dapur dan Bar Kulino House</h4>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant mb-md">
                        <li>Peserta magang tidak diperbolehkan mengambil makanan, minuman, bahan baku, perlengkapan, atau barang apa pun yang berada di area dapur maupun bar tanpa izin dari Crew Kulino House.</li>
                        <li>Segala kebutuhan yang berkaitan dengan area dapur dan bar harus melalui koordinasi dengan Crew Kulino House.</li>
                        <li>Peserta magang diperbolehkan meminjam peralatan dapur atau fasilitas tertentu milik Kulino House untuk kebutuhan yang diperbolehkan.</li>
                        <li>Setiap peminjaman wajib melalui izin dan koordinasi terlebih dahulu dengan Crew Kulino House.</li>
                        <li>Peserta magang bertanggung jawab penuh terhadap kebersihan, keamanan, dan kondisi peralatan yang dipinjam.</li>
                        <li>Setelah digunakan, peralatan wajib dibersihkan dan dikembalikan ke tempat semula dalam kondisi baik.</li>
                        <li>Jika terjadi kerusakan atau kehilangan akibat kelalaian pengguna, peserta magang wajib segera melaporkan kepada Crew Kulino House dan Senior/Pembimbing.</li>
                    </ul>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">6.3 Kerusakan Barang</h4>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant">
                        <li>Jika terjadi kerusakan atau kehilangan barang, peserta magang wajib segera melaporkannya kepada Senior/Pembimbing.</li>
                    </ul>
                </section>

                <section id="sop-7" class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg scroll-mt-24">
                    <h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-md">7. Koordinasi dan Komunikasi</h3>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">7.1 Koordinasi dengan Senior</h4>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant mb-md">
                        <li>Seluruh aktivitas kerja wajib dikoordinasikan dengan Senior atau Pembimbing Magang.</li>
                        <li>Jika mengalami kendala dalam pekerjaan, peserta magang wajib segera meminta arahan.</li>
                    </ul>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">7.2 Pelaporan Tugas</h4>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant">
                        <li>Tugas yang telah selesai wajib dilaporkan kepada Senior/Pembimbing.</li>
                        <li>Tidak diperkenankan mengambil keputusan penting yang berkaitan dengan operasional tanpa persetujuan Senior.</li>
                    </ul>
                </section>

                <section id="sop-8" class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg scroll-mt-24">
                    <h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-md">8. Ibadah dan Nilai-Nilai Kerja</h3>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">8.1 Kewajiban Ibadah</h4>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant mb-md">
                        <li>Peserta magang yang beragama Islam wajib melaksanakan ibadah sholat sesuai waktu yang telah ditentukan.</li>
                        <li>Kegiatan ibadah dilakukan dengan tetap menjaga tanggung jawab pekerjaan.</li>
                    </ul>

                    <h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">8.2 Toleransi Beragama</h4>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant">
                        <li>Menghormati seluruh pemeluk agama dan kepercayaan yang ada di lingkungan kerja.</li>
                        <li>Menjaga suasana kerja yang harmonis dan saling menghargai.</li>
                    </ul>
                </section>

                <section id="sop-9" class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg scroll-mt-24">
                    <h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-sm">9. Kerahasiaan dan Keamanan Data</h3>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant">
                        <li>Menjaga kerahasiaan data perusahaan, pelanggan, dan proyek yang sedang dikerjakan.</li>
                        <li>Dilarang menyebarkan dokumen, file, akses akun, atau informasi internal kepada pihak luar tanpa izin.</li>
                        <li>Menjaga keamanan perangkat kerja yang digunakan.</li>
                    </ul>
                </section>

                <section id="sop-10" class="glass-card bg-error-container/20 border border-error/30 rounded-2xl p-lg scroll-mt-24">
                    <h3 class="font-headline-md text-headline-md text-error font-bold mb-sm flex items-center gap-sm">
                        <span class="material-symbols-outlined">block</span>
                        10. Larangan
                    </h3>
                    <p class="font-body-md text-body-md text-on-surface-variant mb-1">Peserta magang dilarang:</p>
                    <ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant">
                        <li>Merokok di area yang tidak diperbolehkan.</li>
                        <li>Mengonsumsi minuman beralkohol atau zat terlarang.</li>
                        <li>Membuat keributan yang mengganggu operasional.</li>
                        <li>Menggunakan fasilitas kantor untuk kepentingan pribadi tanpa izin.</li>
                        <li>Mengambil barang milik perusahaan, Kulino House, pelanggan, atau karyawan tanpa izin.</li>
                        <li>Menyebarkan informasi internal perusahaan kepada pihak luar.</li>
                    </ul>
                </section>

                <section id="sop-11" class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg scroll-mt-24">
                    <h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-sm">11. Evaluasi dan Sanksi</h3>
                    <p class="font-body-md text-body-md text-on-surface-variant mb-sm">Pelanggaran terhadap SOP ini dapat diberikan sanksi secara bertahap:</p>
                    <ol class="space-y-sm">
                        <li class="flex items-center gap-sm"><span class="w-6 h-6 flex items-center justify-center rounded-full bg-surface-container-high text-on-surface font-label-sm text-label-sm font-bold">1</span><span class="font-body-md text-body-md text-on-surface-variant">Teguran lisan.</span></li>
                        <li class="flex items-center gap-sm"><span class="w-6 h-6 flex items-center justify-center rounded-full bg-surface-container-high text-on-surface font-label-sm text-label-sm font-bold">2</span><span class="font-body-md text-body-md text-on-surface-variant">Teguran tertulis.</span></li>
                        <li class="flex items-center gap-sm"><span class="w-6 h-6 flex items-center justify-center rounded-full bg-surface-container-high text-on-surface font-label-sm text-label-sm font-bold">3</span><span class="font-body-md text-body-md text-on-surface-variant">Evaluasi khusus oleh pembimbing magang.</span></li>
                        <li class="flex items-center gap-sm"><span class="w-6 h-6 flex items-center justify-center rounded-full bg-error-container text-error font-label-sm text-label-sm font-bold">4</span><span class="font-body-md text-body-md text-on-surface font-semibold">Penghentian program magang apabila pelanggaran dianggap berat atau dilakukan berulang kali.</span></li>
                    </ol>
                </section>

                <!-- Nilai Utama -->
                <section id="sop-nilai" class="glass-card bg-primary-container/40 border border-primary/20 rounded-2xl p-lg scroll-mt-24">
                    <h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-xs">Nilai Utama Peserta Magang Kedayweb</h3>
                    <p class="font-body-md text-body-md text-on-surface-variant mb-md">Setiap peserta magang diharapkan menerapkan 5 nilai utama selama menjalankan program magang:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-sm mb-md">
                        <div class="flex items-start gap-sm p-md rounded-xl bg-surface-container-lowest border border-outline-variant">
                            <span class="material-symbols-outlined text-primary">schedule</span>
                            <div>
                                <p class="font-label-md text-label-md text-on-surface font-bold">Disiplin</p>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">Datang tepat waktu dan mematuhi seluruh peraturan.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-sm p-md rounded-xl bg-surface-container-lowest border border-outline-variant">
                            <span class="material-symbols-outlined text-primary">task_alt</span>
                            <div>
                                <p class="font-label-md text-label-md text-on-surface font-bold">Bertanggung Jawab</p>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">Menyelesaikan tugas serta menjaga fasilitas yang digunakan.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-sm p-md rounded-xl bg-surface-container-lowest border border-outline-variant">
                            <span class="material-symbols-outlined text-primary">bolt</span>
                            <div>
                                <p class="font-label-md text-label-md text-on-surface font-bold">Inisiatif</p>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">Proaktif membantu pekerjaan dan menjaga lingkungan kerja.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-sm p-md rounded-xl bg-surface-container-lowest border border-outline-variant">
                            <span class="material-symbols-outlined text-primary">handshake</span>
                            <div>
                                <p class="font-label-md text-label-md text-on-surface font-bold">Sopan Santun</p>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">Menghormati pelanggan, karyawan, dan sesama peserta magang.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-sm p-md rounded-xl bg-surface-container-lowest border border-outline-variant sm:col-span-2">
                            <span class="material-symbols-outlined text-primary">cleaning_services</span>
                            <div>
                                <p class="font-label-md text-label-md text-on-surface font-bold">Menjaga Kebersihan</p>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">Menjaga kebersihan diri, area kerja, serta fasilitas bersama.</p>
                            </div>
                        </div>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface italic border-l-4 border-primary pl-md">"Datang untuk belajar, bekerja dengan profesional, dan pulang meninggalkan lingkungan yang lebih rapi daripada saat datang."</p>
                </section>

                <!-- Penutup -->
                <section id="sop-penutup" class="glass-card bg-surface-container-lowest border border-outline-variant rounded-2xl p-lg scroll-mt-24 text-center">
                    <h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-sm">Penutup</h3>
                    <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl mx-auto">Setiap peserta magang Kedayweb wajib membaca, memahami, dan mematuhi seluruh ketentuan yang tercantum dalam SOP ini. Kepatuhan terhadap SOP merupakan bagian dari penilaian selama program magang berlangsung dan menjadi dasar pembentukan karakter kerja yang profesional, disiplin, bertanggung jawab, serta mampu beradaptasi di lingkungan kerja nyata.</p>
                    <div class="mt-md pt-md border-t border-outline-variant">
                        <p class="font-headline-sm text-headline-sm text-primary font-bold">Kedayweb × Kulino House</p>
                        <p class="font-body-sm text-body-sm text-on-surface-variant italic">"Belajar, Bertumbuh, dan Berkarya dengan Profesionalisme."</p>
                    </div>
                </section>

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