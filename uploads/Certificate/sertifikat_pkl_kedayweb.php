<!DOCTYPE html>

<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

```
<title>Sertifikat PKL - CV. Kedayweb</title>

<script src="https://cdn.tailwindcss.com"></script>

<link
    href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,600;0,700;0,800;1,400&display=swap"
    rel="stylesheet"
>

<script
    src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"
    crossorigin="anonymous"
></script>

<style>
    * {
        box-sizing: border-box;
    }

    html,
    body {
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Montserrat', sans-serif;
        background-color: #0f172a;

        display: flex;
        flex-direction: column;
        align-items: center;

        min-height: 100vh;
        padding: 32px 16px;
    }


    /* =========================================================
       CERTIFICATE
       Rasio A4 Landscape = 297 / 210
       ========================================================= */

    .certificate-wrapper {
        width: min(1000px, 100%);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .certificate-container {
        position: relative;

        /*
         * Rasio A4 Landscape
         * 297 : 210
         */
        width: 1000px;
        aspect-ratio: 297 / 210;

        background-image: url('Sertifikat.png');
        background-size: 100% 100%;
        background-repeat: no-repeat;
        background-position: center;

        box-shadow:
            0 20px 25px -5px rgba(0, 0, 0, 0.5),
            0 8px 10px -6px rgba(0, 0, 0, 0.5);

        flex-shrink: 0;
        overflow: hidden;
    }


    /* =========================================================
       NAMA SISWA
       ========================================================= */

    .name-overlay {
        position: absolute;

        /*
         * Tinggi nama
         */
        top: 45%;

        /*
         * Posisi horizontal
         */
        left: 50%;

        transform: translate(-50%, -50%);

        /*
         * Lebar area nama
         */
        width: 50%;

        text-align: center;

        z-index: 10;
    }

    .name-input {
        width: 100%;

        background: transparent;
        border: none;
        outline: none;

        text-align: center;

        font-weight: 800;
        color: #0f172a;

        font-family: 'Montserrat', sans-serif;

        transition: all 0.2s ease;
    }

    .name-input::placeholder {
        color: #94a3b8;

        font-weight: 600;
        font-style: italic;

        opacity: 0.8;
    }

    .name-input:hover,
    .name-input:focus {
        background-color: rgba(255, 255, 255, 0.45);

        border-radius: 6px;

        box-shadow: 0 0 0 2px #3b82f6;
    }


    /* =========================================================
       QR CODE / LOGO
       ========================================================= */

    .qr-overlay {
        position: absolute;

        /*
         * Semua posisi menggunakan %
         * agar tetap mengikuti ukuran A4
         */

        left: 43.7%;
        bottom: 11.6%;

        /*
         * Ukuran QR
         */
        width: 12.5%;
        height: 17.7%;

        display: flex;
        align-items: center;
        justify-content: center;

        pointer-events: auto;

        border-radius: 8px;

        overflow: hidden;

        z-index: 20;
    }

    .qr-overlay label {
        width: 100%;
        height: 100%;

        display: flex;
        align-items: center;
        justify-content: center;

        text-align: center;
    }

    #qrPreview {
        width: 100%;
        height: 100%;

        object-fit: contain;
    }


    /* =========================================================
       PRINT / PDF
       ========================================================= */

    @media print {

        html,
        body {
            width: 297mm;
            height: 210mm;

            margin: 0 !important;
            padding: 0 !important;

            background: none !important;
        }

        body {
            display: block;
        }

        .no-print {
            display: none !important;
        }

        .certificate-wrapper {
            width: 297mm !important;
            height: 210mm !important;

            margin: 0 !important;
            padding: 0 !important;

            display: block !important;

            transform: none !important;
            scale: 1 !important;
        }

        .certificate-container {
            width: 297mm !important;
            height: 210mm !important;

            aspect-ratio: auto !important;

            margin: 0 !important;
            padding: 0 !important;

            box-shadow: none !important;

            /*
             * Pastikan gambar background
             * ikut tercetak
             */
            background-size: 100% 100% !important;

            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /*
         * Hilangkan efek input ketika print
         */
        .name-input:hover,
        .name-input:focus {
            background: transparent !important;
            box-shadow: none !important;
        }

        /*
         * QR tetap menggunakan posisi
         * relatif terhadap sertifikat
         */
        .qr-overlay {
            left: 43.7% !important;
            bottom: 11.6% !important;

            width: 12.5% !important;
            height: 17.7% !important;
        }

        @page {
            size: A4 landscape;
            margin: 0;
        }
    }


    /* =========================================================
       RESPONSIVE SCREEN
       ========================================================= */

    @media (max-width: 1050px) {

        .certificate-wrapper {
            transform: scale(0.85);
            transform-origin: top center;

            margin-bottom: -106px;
        }
    }

    @media (max-width: 880px) {

        .certificate-wrapper {
            transform: scale(0.65);
            transform-origin: top center;

            margin-bottom: -247px;
        }
    }

    @media (max-width: 680px) {

        .certificate-wrapper {
            transform: scale(0.45);
            transform-origin: top center;

            margin-bottom: -389px;
        }
    }

    @media (max-width: 480px) {

        .certificate-wrapper {
            transform: scale(0.33);
            transform-origin: top center;

            margin-bottom: -475px;
        }
    }
</style>
```

</head>

<body>

```
<!-- =========================================================
     CONTROL HEADER
     ========================================================= -->

<div
    class="no-print bg-slate-800 text-white p-4 rounded-xl shadow-lg mb-6 w-full max-w-4xl flex flex-wrap justify-between items-center gap-4 border border-slate-700"
>

    <div class="flex items-center gap-3">

        <div class="p-2.5 bg-blue-600 rounded-lg">

            <i class="fa-solid fa-id-card text-xl"></i>

        </div>

        <div>

            <h1 class="font-bold text-lg leading-tight">
                Generator Sertifikat Kedayweb
            </h1>

            <p class="text-xs text-slate-300">
                Ketik nama pada kolom di bawah, lalu cetak / simpan ke PDF.
            </p>

        </div>

    </div>


    <div class="flex flex-wrap items-center gap-3">

        <!-- FONT SIZE -->

        <div
            class="flex items-center bg-slate-700 rounded-lg p-1 text-xs gap-1 border border-slate-600"
        >

            <span class="px-2 text-slate-300 font-medium">
                Ukuran Teks:
            </span>

            <button
                onclick="changeFontSize(-2)"
                class="px-2 py-1 bg-slate-800 hover:bg-slate-600 rounded font-bold"
                title="Kecilkan Font"
            >
                -
            </button>

            <span
                id="fontSizeDisplay"
                class="px-1.5 font-semibold text-blue-400"
            >
                26px
            </span>

            <button
                onclick="changeFontSize(2)"
                class="px-2 py-1 bg-slate-800 hover:bg-slate-600 rounded font-bold"
                title="Besarkan Font"
            >
                +
            </button>

        </div>


        <!-- PRINT -->

        <button
            onclick="window.print()"
            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold rounded-lg shadow transition duration-200 flex items-center gap-2 text-sm"
        >

            <i class="fa-solid fa-print"></i>

            Cetak / PDF

        </button>

    </div>

</div>


<!-- =========================================================
     CERTIFICATE
     ========================================================= -->

<div class="certificate-wrapper">

    <div
        class="certificate-container"
        id="certificate"
    >


        <!-- =====================================================
             NAMA SISWA
             ===================================================== -->

        <div class="name-overlay">

            <input
                type="text"
                id="studentName"
                class="name-input"
                placeholder="Nama Siswa / Peserta"
                value="Nama Siswa"
                style="font-size: 26px;"
                autocomplete="off"
            />

        </div>


        <!-- =====================================================
             QR CODE
             ===================================================== -->

        <div
            class="qr-overlay"
            id="qrContainer"
            title="Klik untuk mengunggah logo/QR"
        >

            <label
                class="w-full h-full cursor-pointer flex flex-col items-center justify-center text-slate-400 hover:text-blue-500 hover:bg-slate-100/50 transition text-center p-1"
            >

                <input
                    type="file"
                    accept="image/*"
                    class="hidden"
                    onchange="previewQR(event)"
                >

                <i
                    class="fa-solid fa-qrcode text-2xl mb-1 no-print"
                ></i>

                <span
                    class="text-[9px] font-semibold leading-tight no-print"
                >
                    Upload QR / Logo
                </span>

                <img
                    id="qrPreview"
                    class="hidden w-full h-full object-contain"
                    alt="QR Code"
                >

            </label>

        </div>

    </div>

</div>


<!-- =========================================================
     JAVASCRIPT
     ========================================================= -->

<script>

    let currentFontSize = 26;


    /* =========================================================
       FONT SIZE
       ========================================================= */

    function changeFontSize(delta) {

        currentFontSize += delta;

        /*
         * Batas font:
         * minimum 14px
         * maksimum 48px
         */
        currentFontSize = Math.max(
            14,
            Math.min(48, currentFontSize)
        );


        const input =
            document.getElementById('studentName');


        input.style.fontSize =
            currentFontSize + 'px';


        document.getElementById(
            'fontSizeDisplay'
        ).innerText =
            currentFontSize + 'px';
    }


    /* =========================================================
       QR PREVIEW
       ========================================================= */

    function previewQR(event) {

        const file =
            event.target.files[0];


        if (!file) {
            return;
        }


        /*
         * Pastikan file merupakan gambar
         */
        if (!file.type.startsWith('image/')) {

            alert('Silakan pilih file gambar.');

            return;
        }


        const reader =
            new FileReader();


        reader.onload = function(e) {

            const img =
                document.getElementById('qrPreview');


            img.src =
                e.target.result;


            img.classList.remove('hidden');

        };


        reader.readAsDataURL(file);
    }

</script>
```

</body>
</html>
