<?php
/**
 * tabel-nilai-viewer.php
 * Dirender sebagai iframe src pada slide-2 sertifikat (verification.php & certificate.php).
 * Menerima ?id=CERT_ID -> ambil dari DB -> render Tabel Nilai PKL full-size dengan autoscale.
 */
require_once __DIR__ . '/Login/koneksi.php';

$cert_id = trim($_GET['id'] ?? '');
$cert = null;

if ($cert_id && $conn) {
    $stmt = $conn->prepare(
        "SELECT intern_name, major, score_technical, score_discipline, score_attitude,
                supervisor_name, issue_date,
                DATE_FORMAT(start_date,'%d %M %Y') AS start_fmt,
                DATE_FORMAT(end_date,  '%d %M %Y') AS end_fmt
         FROM certificates WHERE certificate_id = ? LIMIT 1"
    );
    $stmt->bind_param('s', $cert_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cert   = $result->fetch_assoc();
    $stmt->close();
}

$nama        = htmlspecialchars($cert['intern_name']  ?? '');
$jurusan     = htmlspecialchars($cert['major']         ?? '-');
$start       = $cert['start_fmt'] ?? '';
$end_d       = $cert['end_fmt']   ?? '';
$waktu       = ($start && $end_d) ? "$start sampai $end_d" : '';
$supervisor  = $cert['supervisor_name'] ?? 'M. Lutfi Nur Fauzi, S.Kom.';
$issue_date  = $cert['issue_date']      ?? date('Y-m-d');
$tgl_bwi     = 'Banyuwangi, ' . date('d F Y', strtotime($issue_date));

$skor_teknis   = (int)($cert['score_technical']  ?? 0);
$skor_disiplin = (int)($cert['score_discipline'] ?? 0);
$skor_sikap    = (int)($cert['score_attitude']   ?? 0);

$nt  = [$skor_disiplin, $skor_disiplin, $skor_disiplin, $skor_sikap, $skor_sikap, $skor_sikap];
$tek = [$skor_teknis,   $skor_teknis,   $skor_teknis,   $skor_teknis, $skor_teknis, $skor_teknis];

$rata_nt  = round(array_sum($nt)  / count($nt),  2);
$rata_tek = round(array_sum($tek) / count($tek), 2);
$kumul    = round(($rata_nt + $rata_tek) / 2,    2);

$label_nt = ['Kedisiplinan','Kemauan Kerja Dan Motivasi','Kerajinan',
             'Inisiatif Dan Kreatifitas','Kerjasama Dan Tanggung Jawab','Sikap Dan Perilaku'];
$label_t  = ['Penguasaan Tools Dan Perangkat Kerja Utama','Kualitas &amp; Ketepatan Hasil Pekerjaan',
             'Pemahaman Brief &amp; Penerapan Spesifikasi Tugas','Kecepatan &amp; Ketepatan Waktu Penyelesaian Tugas',
             'Kemampuan Penyelesaian Masalah','Laporan Hasil Kerja'];

function fmt($n) { return number_format($n, 2, ',', '.'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Daftar Nilai PKL</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
<style>
  @page { size: A4 landscape; margin: 0; }
  :root { --garis: 1px solid #000; }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html, body {
    width: 100%; height: 100%; margin: 0; padding: 0; overflow: hidden;
    background: transparent;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Poppins', sans-serif;
    color: #000;
    -webkit-print-color-adjust: exact; print-color-adjust: exact;
  }
  .page-scaler {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
  }
  .page {
    width: 297mm;
    height: 210mm;
    padding: 31pt 51pt 0;
    position: relative; overflow: hidden; flex-shrink: 0;
    background: #f5f3e7 url('uploads/Certificate/Sertifikat_Belakang.png') center / 100% 100% no-repeat;
    font-size: 11pt; line-height: 17pt;
    transform-origin: center center;
  }
  .judul { font-size: 20pt; line-height: 30pt; font-weight: 700; text-align: center; }
  .data-siswa { margin-top: 20pt; display: grid; row-gap: 1pt; }
  .data-siswa .baris { display: grid; grid-template-columns: 220pt 20pt 1fr; min-height: 17pt; }
  .data-siswa .label { font-weight: 500; }
  .nilai { width: 100%; margin-top: 14pt; border-collapse: collapse; table-layout: fixed; border: var(--garis); }
  .nilai col.no { width: 28pt; }
  .nilai col.skor { width: 41pt; }
  .nilai th, .nilai td { border: var(--garis); padding: 0 6pt; vertical-align: middle; }
  .nilai th { font-weight: 500; text-align: center; }
  .nilai td { height: 21pt; }
  .nilai td.no, .nilai td.skor { text-align: center; padding: 0; }
  .nilai thead tr.judul-tabel th { height: 23pt; }
  .nilai thead tr.aspek th { height: 22pt; }
  .nilai tfoot td.rata { height: 22pt; text-align: center; }
  .nilai tfoot td.kumulatif { height: 71pt; padding: 0; }
  .hasil { font-weight: 500; }
  .rumus { display: flex; justify-content: center; align-items: flex-start; gap: 4pt; font-weight: 500; }
  .pecahan { display: flex; flex-direction: column; align-items: center; font-weight: 400; }
  .pembilang { border-bottom: var(--garis); padding: 0 2pt; }
  .bawah { margin-top: 14pt; padding-right: 24pt; display: flex; justify-content: space-between; align-items: flex-start; }
  .judul-ketentuan { font-weight: 500; margin-bottom: 4pt; }
  .predikat { width: 190pt; border-collapse: collapse; table-layout: fixed; font-size: 9pt; line-height: 12pt; }
  .predikat th { font-weight: 500; padding: 1pt 0; border-top: var(--garis); border-bottom: var(--garis); }
  .predikat td { padding: 0; text-align: center; }
  .predikat tbody tr:first-child td { padding-top: 1pt; }
  .ttd p { line-height: 17pt; }
  .ttd .ruang { height: 44pt; }
  .ttd .nama { font-weight: 500; text-decoration: underline; }
</style>
</head>
<body>
<div class="page-scaler">
<main class="page" id="page">
  <h1 class="judul">DAFTAR NILAI PRAKTIK KERJA LAPANGAN (PKL)</h1>
  <section class="data-siswa">
    <div class="baris"><span class="label">NAMA SISWA</span><span>:</span><span><?php echo $nama; ?></span></div>
    <div class="baris"><span class="label">JURUSAN</span><span>:</span><span><?php echo $jurusan; ?></span></div>
    <div class="baris"><span class="label">TEMPAT PRAKERIN</span><span>:</span><span>CV. Kedayweb (IT &amp; Multimedia Agency)</span></div>
    <div class="baris"><span class="label">ALAMAT TEMPAT PRAKERIN</span><span>:</span><span>Banyuwangi, Jawa Timur</span></div>
    <div class="baris"><span class="label">WAKTU PELAKSANAAN</span><span>:</span><span><?php echo htmlspecialchars($waktu); ?></span></div>
  </section>
  <table class="nilai">
    <colgroup>
      <col class="no"><col><col class="skor">
      <col class="no"><col><col class="skor">
    </colgroup>
    <thead>
      <tr class="judul-tabel"><th colspan="6">KOMPONEN YANG DINILAI</th></tr>
      <tr class="aspek">
        <th colspan="3">ASPEK NON TEKNIS</th>
        <th colspan="3">ASPEK TEKNIS</th>
      </tr>
    </thead>
    <tbody>
      <?php for ($i = 0; $i < 6; $i++): ?>
      <tr>
        <td class="no"><?php echo ($i+1); ?>.</td>
        <td><?php echo htmlspecialchars($label_nt[$i]); ?></td>
        <td class="skor"><?php echo $cert ? $nt[$i] : ''; ?></td>
        <td class="no"><?php echo ($i+1); ?>.</td>
        <td><?php echo $label_t[$i]; ?></td>
        <td class="skor"><?php echo $cert ? $tek[$i] : ''; ?></td>
      </tr>
      <?php endfor; ?>
    </tbody>
    <tfoot>
      <tr>
        <td class="rata" colspan="3">Nilai Rata-Rata Aspek Non Teknis = <span class="hasil"><?php echo $cert ? fmt($rata_nt) : ''; ?></span></td>
        <td class="rata" colspan="3">Nilai Rata-Rata Aspek Teknis = <span class="hasil"><?php echo $cert ? fmt($rata_tek) : ''; ?></span></td>
      </tr>
      <tr>
        <td class="kumulatif" colspan="6">
          <div class="rumus">
            <span>Nilai Kumulatif =</span>
            <span class="pecahan">
              <span class="pembilang">(Nilai Rata-Rata Non Teknis + Nilai Rata-Rata Teknis)</span>
              <span>2</span>
            </span>
            <span>= <span class="hasil"><?php echo $cert ? fmt($kumul) : ''; ?></span></span>
          </div>
        </td>
      </tr>
    </tfoot>
  </table>
  <section class="bawah">
    <div>
      <p class="judul-ketentuan">KETENTUAN:</p>
      <table class="predikat">
        <colgroup><col style="width:60pt"><col style="width:60pt"><col style="width:70pt"></colgroup>
        <thead><tr><th>Nilai</th><th>Kategori</th><th>Keterangan</th></tr></thead>
        <tbody>
          <tr><td>90-100</td><td>A</td><td>Sangat Baik</td></tr>
          <tr><td>80-89</td><td>B</td><td>Baik</td></tr>
          <tr><td>75-79</td><td>C</td><td>Cukup</td></tr>
          <tr><td>&lt;=74</td><td>D</td><td>Kurang</td></tr>
        </tbody>
      </table>
    </div>
    <div class="ttd">
      <p><?php echo htmlspecialchars($tgl_bwi); ?></p>
      <p>Founder &amp; Director Kedayweb</p>
      <div class="ruang"></div>
      <p class="nama"><?php echo htmlspecialchars($supervisor); ?></p>
    </div>
  </section>
</main>
</div>
<script>
function autoScale() {
  const page = document.getElementById('page');
  if (!page) return;
  const winW = window.innerWidth;
  const winH = window.innerHeight;
  const baseW = page.offsetWidth;  
  const baseH = page.offsetHeight; 
  if (baseW && baseH) {
    const scale = Math.min(winW / baseW, winH / baseH);
    page.style.transform = `scale(${scale})`;
  }
}
window.addEventListener('resize', autoScale);
window.addEventListener('load', autoScale);
autoScale();
</script>
</body>
</html>
