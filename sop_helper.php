<?php
/**
 * sop_helper.php
 * Helper to get, set, and reset SOP data for Kedayweb Platform
 */

if (!function_exists('get_default_sop_data')) {
    function get_default_sop_data() {
        return [
            'badge' => 'Standar Operasional Prosedur',
            'title' => 'SOP Peserta Magang Kedayweb',
            'subtitle' => 'Kedayweb × Kulino House — wajib dibaca dan dipahami oleh seluruh peserta magang.',
            'sections' => [
                [
                    'id' => 'sop-1',
                    'nav_title' => '1. Tujuan',
                    'title' => '1. Tujuan',
                    'style' => 'default',
                    'content' => '<p class="font-body-md text-body-md text-on-surface-variant">SOP ini dibuat untuk menciptakan lingkungan kerja yang profesional, disiplin, nyaman, dan produktif bagi seluruh peserta magang di Kedayweb serta mendukung operasional dan pelayanan di lingkungan Kulino House.</p>'
                ],
                [
                    'id' => 'sop-2',
                    'nav_title' => '2. Jam Kerja & Kehadiran',
                    'title' => '2. Jam Kerja dan Kehadiran',
                    'style' => 'default',
                    'content' => '<h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">2.1 Jam Kerja</h4>
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
</ul>'
                ],
                [
                    'id' => 'sop-3',
                    'nav_title' => '3. Kebersihan & Kerapihan',
                    'title' => '3. Kebersihan dan Kerapihan Area Kerja',
                    'style' => 'default',
                    'content' => '<h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">3.1 Kebersihan Lingkungan</h4>
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
</ul>'
                ],
                [
                    'id' => 'sop-4',
                    'nav_title' => '4. Etika & Sikap Kerja',
                    'title' => '4. Etika dan Sikap Kerja',
                    'style' => 'default',
                    'content' => '<h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">4.1 Profesionalisme</h4>
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
</ul>'
                ],
                [
                    'id' => 'sop-5',
                    'nav_title' => '5. Prioritas Pelanggan',
                    'title' => '5. Prioritas Pelayanan Pelanggan Kulino House',
                    'style' => 'default',
                    'content' => '<h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">5.1 Mengutamakan Pelanggan</h4>
<ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant mb-md">
    <li>Peserta magang wajib menghormati dan mengutamakan kenyamanan pelanggan Kulino House.</li>
    <li>Apabila peserta magang menggunakan area atau kursi yang dibutuhkan pelanggan, peserta magang wajib memberikan tempat tersebut kepada pelanggan.</li>
</ul>

<h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">5.2 Penggunaan Area Bersama</h4>
<ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant">
    <li>Menjaga kenyamanan dan ketertiban area bersama.</li>
    <li>Tidak mengganggu aktivitas pelanggan atau operasional Kulino House.</li>
</ul>'
                ],
                [
                    'id' => 'sop-6',
                    'nav_title' => '6. Fasilitas & Barang',
                    'title' => '6. Penggunaan Fasilitas dan Barang',
                    'style' => 'default',
                    'content' => '<h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">6.1 Barang dan Inventaris</h4>
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
</ul>'
                ],
                [
                    'id' => 'sop-7',
                    'nav_title' => '7. Koordinasi & Komunikasi',
                    'title' => '7. Koordinasi dan Komunikasi',
                    'style' => 'default',
                    'content' => '<h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">7.1 Koordinasi dengan Senior</h4>
<ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant mb-md">
    <li>Seluruh aktivitas kerja wajib dikoordinasikan dengan Senior atau Pembimbing Magang.</li>
    <li>Jika mengalami kendala dalam pekerjaan, peserta magang wajib segera meminta arahan.</li>
</ul>

<h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">7.2 Pelaporan Tugas</h4>
<ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant">
    <li>Tugas yang telah selesai wajib dilaporkan kepada Senior/Pembimbing.</li>
    <li>Tidak diperkenankan mengambil keputusan penting yang berkaitan dengan operasional tanpa persetujuan Senior.</li>
</ul>'
                ],
                [
                    'id' => 'sop-8',
                    'nav_title' => '8. Ibadah & Nilai Kerja',
                    'title' => '8. Ibadah dan Nilai-Nilai Kerja',
                    'style' => 'default',
                    'content' => '<h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">8.1 Kewajiban Ibadah</h4>
<ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant mb-md">
    <li>Peserta magang yang beragama Islam wajib melaksanakan ibadah sholat sesuai waktu yang telah ditentukan.</li>
    <li>Kegiatan ibadah dilakukan dengan tetap menjaga tanggung jawab pekerjaan.</li>
</ul>

<h4 class="font-label-lg text-label-lg text-primary font-bold mb-sm">8.2 Toleransi Beragama</h4>
<ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant">
    <li>Menghormati seluruh pemeluk agama dan kepercayaan yang ada di lingkungan kerja.</li>
    <li>Menjaga suasana kerja yang harmonis dan saling menghargai.</li>
</ul>'
                ],
                [
                    'id' => 'sop-9',
                    'nav_title' => '9. Kerahasiaan & Keamanan Data',
                    'title' => '9. Kerahasiaan dan Keamanan Data',
                    'style' => 'default',
                    'content' => '<ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant">
    <li>Menjaga kerahasiaan data perusahaan, pelanggan, dan proyek yang sedang dikerjakan.</li>
    <li>Dilarang menyebarkan dokumen, file, akses akun, atau informasi internal kepada pihak luar tanpa izin.</li>
    <li>Menjaga keamanan perangkat kerja yang digunakan.</li>
</ul>'
                ],
                [
                    'id' => 'sop-10',
                    'nav_title' => '10. Larangan',
                    'title' => '10. Larangan',
                    'style' => 'warning',
                    'content' => '<p class="font-body-md text-body-md text-on-surface-variant mb-1">Peserta magang dilarang:</p>
<ul class="list-disc pl-lg space-y-1 font-body-md text-body-md text-on-surface-variant">
    <li>Merokok di area yang tidak diperbolehkan.</li>
    <li>Mengonsumsi minuman beralkohol atau zat terlarang.</li>
    <li>Membuat keributan yang mengganggu operasional.</li>
    <li>Menggunakan fasilitas kantor untuk kepentingan pribadi tanpa izin.</li>
    <li>Mengambil barang milik perusahaan, Kulino House, pelanggan, atau karyawan tanpa izin.</li>
    <li>Menyebarkan informasi internal perusahaan kepada pihak luar.</li>
</ul>'
                ],
                [
                    'id' => 'sop-11',
                    'nav_title' => '11. Evaluasi & Sanksi',
                    'title' => '11. Evaluasi dan Sanksi',
                    'style' => 'default',
                    'content' => '<p class="font-body-md text-body-md text-on-surface-variant mb-sm">Pelanggaran terhadap SOP ini dapat diberikan sanksi secara bertahap:</p>
<ol class="space-y-sm">
    <li class="flex items-center gap-sm"><span class="w-6 h-6 flex items-center justify-center rounded-full bg-surface-container-high text-on-surface font-label-sm text-label-sm font-bold">1</span><span class="font-body-md text-body-md text-on-surface-variant">Teguran lisan.</span></li>
    <li class="flex items-center gap-sm"><span class="w-6 h-6 flex items-center justify-center rounded-full bg-surface-container-high text-on-surface font-label-sm text-label-sm font-bold">2</span><span class="font-body-md text-body-md text-on-surface-variant">Teguran tertulis.</span></li>
    <li class="flex items-center gap-sm"><span class="w-6 h-6 flex items-center justify-center rounded-full bg-surface-container-high text-on-surface font-label-sm text-label-sm font-bold">3</span><span class="font-body-md text-body-md text-on-surface-variant">Evaluasi khusus oleh pembimbing magang.</span></li>
    <li class="flex items-center gap-sm"><span class="w-6 h-6 flex items-center justify-center rounded-full bg-error-container text-error font-label-sm text-label-sm font-bold">4</span><span class="font-body-md text-body-md text-on-surface font-semibold">Penghentian program magang apabila pelanggaran dianggap berat atau dilakukan berulang kali.</span></li>
</ol>'
                ],
                [
                    'id' => 'sop-nilai',
                    'nav_title' => 'Nilai Utama',
                    'title' => 'Nilai Utama Peserta Magang Kedayweb',
                    'style' => 'primary',
                    'content' => '<p class="font-body-md text-body-md text-on-surface-variant mb-md">Setiap peserta magang diharapkan menerapkan 5 nilai utama selama menjalankan program magang:</p>
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
<p class="font-body-md text-body-md text-on-surface italic border-l-4 border-primary pl-md">"Datang untuk belajar, bekerja dengan profesional, dan pulang meninggalkan lingkungan yang lebih rapi daripada saat datang."</p>'
                ],
                [
                    'id' => 'sop-penutup',
                    'nav_title' => 'Penutup',
                    'title' => 'Penutup',
                    'style' => 'centered',
                    'content' => '<p class="font-body-md text-body-md text-on-surface-variant max-w-2xl mx-auto">Setiap peserta magang Kedayweb wajib membaca, memahami, dan mematuhi seluruh ketentuan yang tercantum dalam SOP ini. Kepatuhan terhadap SOP merupakan bagian dari penilaian selama program magang berlangsung dan menjadi dasar pembentukan karakter kerja yang profesional, disiplin, bertanggung jawab, serta mampu beradaptasi di lingkungan kerja nyata.</p>
<div class="mt-md pt-md border-t border-outline-variant">
    <p class="font-headline-sm text-headline-sm text-primary font-bold">Kedayweb × Kulino House</p>
    <p class="font-body-sm text-body-sm text-on-surface-variant italic">"Belajar, Bertumbuh, dan Berkarya dengan Profesionalisme."</p>
</div>'
                ]
            ]
        ];
    }
}

if (!function_exists('get_sop_data')) {
    function get_sop_data() {
        $file = __DIR__ . '/uploads/sop_data.json';
        if (file_exists($file)) {
            $content = json_decode(file_get_contents($file), true);
            if (is_array($content) && !empty($content['sections'])) {
                return $content;
            }
        }
        return get_default_sop_data();
    }
}

if (!function_exists('save_sop_data')) {
    function save_sop_data($data) {
        $dir = __DIR__ . '/uploads';
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }
        $file = $dir . '/sop_data.json';
        return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

if (!function_exists('reset_sop_data')) {
    function reset_sop_data() {
        $file = __DIR__ . '/uploads/sop_data.json';
        if (file_exists($file)) {
            @unlink($file);
        }
        return true;
    }
}
