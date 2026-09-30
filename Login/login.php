<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/koneksi.php'; // Memanggil koneksi database

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = mysqli_real_escape_string($conn, $_POST['role'] ?? 'intern');

    // Cek ke database berdasarkan username dan role
    // Jika login sebagai admin, cek juga role superadmin
    if ($role === 'admin') {
        $query = "SELECT * FROM users WHERE username = '$username' AND (role = 'admin' OR role = 'superadmin')";
    } else {
        $query = "SELECT * FROM users WHERE username = '$username' AND role = '$role'";
    }
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user_data = mysqli_fetch_assoc($result);
        
        // Cek kecocokan password
        if ($password === $user_data['password']) {
            // Jika berhasil, set Session
            session_regenerate_id(true);
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['role'] = $user_data['role'];
            
            // Arahkan ke halaman utama/dashboard
            $redirect = '../dashboard.php';
            if ($user_data['role'] === 'admin' || $user_data['role'] === 'superadmin') {
                $redirect = '../admin/admin-dashboard.php';
            }
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Kata sandi yang Anda masukkan salah!';
        }
    } else {
        $error = 'ID/Email tidak ditemukan untuk peran ini!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  <script src="../shared-config.js"></script>
  <style>
    .bg-grid {
      background-image: radial-gradient(rgba(255, 255, 255, 0.15) 1px, transparent 0);
      background-size: 16px 16px;
    }
  </style>
</head>
<body class="bg-slate-100 font-sans antialiased min-h-screen flex flex-col justify-between">
  <!-- Navbar Header -->
  <header class="w-full px-6 md:px-10 py-4 flex justify-between items-center text-slate-700 shrink-0">
    <div class="flex items-center gap-2 font-bold text-xl text-blue-900">
       <img src="../uploads/Icon/Anak_Magang_Icon.jpg.jpeg" alt="Logo AnakMagang" class="w-8 h-8 rounded-lg object-cover shadow-xs border border-outline-variant/30 shrink-0"/>
                <span class="font-headline-md text-headline-md font-bold text-primary" data-i18n="brand_name">AnakMagang</span>
    </div>
    <a href="#" class="text-sm font-medium hover:underline text-slate-600 flex items-center gap-1.5">
      <i class="fa-regular fa-circle-question"></i> Support
    </a>
  </header>

  <!-- Main Container -->
  <main class="flex-grow flex items-center justify-center p-4">
    <div class="max-w-4xl w-full flex flex-col gap-3">
      
      <!-- Button Kembali ke Beranda (Pojok Atas Kiri Container) -->
      <div class="flex justify-start">
        <a href="../index.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-blue-900 hover:text-white hover:border-blue-900 text-xs font-bold shadow-sm hover:shadow-md transition-all active:scale-95 group" title="Kembali ke Beranda">
          <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
          <span>Kembali ke Beranda</span>
        </a>
      </div>

      <!-- Card Big Container (Menampung Selamat Datang & Form Login) -->
      <div class="bg-white rounded-2xl shadow-2xl flex flex-col md:flex-row w-full overflow-hidden border border-slate-200">
        
        <!-- Panel Kiri: Selamat Datang Anak Magang -->
        <div class="md:w-5/12 bg-blue-900 bg-grid text-white p-8 flex flex-col justify-between relative">
          <div>
            <span class="inline-block bg-blue-800/80 text-blue-200 text-xs px-3 py-1 rounded-full font-semibold mb-6 border border-blue-700">
              <i class="fa-solid fa-shield-halved mr-1"></i> Sistem Karir Terintegrasi v3.4
            </span>
            <h1 class="text-2xl font-bold leading-tight mb-3">Selamat Datang Anak Magang</h1>
            <p class="text-xs text-blue-200 leading-relaxed mb-8">
              Platform Terpadu Manajemen Magang, Presensi & Gamifikasi Portofolio Talenta Digital Indonesia.
            </p>

            <div class="space-y-3">
              <div class="bg-blue-800/40 border border-blue-700/50 rounded-xl p-3 flex items-start gap-3 backdrop-blur-sm">
                <i class="fa-solid fa-location-dot mt-1 text-blue-300"></i>
                <div>
                  <h4 class="text-xs font-semibold">Presensi Geofencing & Real-Time</h4>
                  <p class="text-[10px] text-blue-200">Verifikasi lokasi GPS instan & rekap otomatis.</p>
                </div>
              </div>
              <div class="bg-blue-800/40 border border-blue-700/50 rounded-xl p-3 flex items-start gap-3 backdrop-blur-sm">
                <i class="fa-solid fa-gamepad mt-1 text-blue-300"></i>
                <div>
                  <h4 class="text-xs font-semibold">Kanban XP & Gamifikasi Tugas</h4>
                  <p class="text-[10px] text-blue-200">Kumpulkan poin pengalaman dan buka badge.</p>
                </div>
              </div>
              <div class="bg-blue-800/40 border border-blue-700/50 rounded-xl p-3 flex items-start gap-3 backdrop-blur-sm">
                <i class="fa-solid fa-certificate mt-1 text-blue-300"></i>
                <div>
                  <h4 class="text-xs font-semibold">Sertifikat Digital Terverifikasi</h4>
                  <p class="text-[10px] text-blue-200">QR terenkripsi yang diakui 200+ mitra industri.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Panel Kanan: Masuk ke Akun Anda -->
        <div class="md:w-7/12 p-8 flex flex-col justify-between bg-white">
          <div>
            <div class="flex justify-between items-center mb-4">
              <span class="text-xs font-bold tracking-wider text-blue-900 flex items-center gap-1">
                <i class="fa-solid fa-id-card-clip"></i> PORTAL AKSES
              </span>
            </div>

            <h2 class="text-2xl font-bold text-slate-800">Masuk ke Akun Anda</h2>
            <p class="text-xs text-slate-500 mb-6">Pilih peran Anda dan masukkan kredensial resmi untuk mengakses dasbor.</p>

            <!-- Menampilkan Error Login -->
            <?php if (!empty($error)): ?>
              <div class="bg-red-100 border border-red-300 text-red-700 px-3 py-2 rounded-lg text-xs mb-4 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
            <?php endif; ?>

            <div class="bg-slate-100 p-1 rounded-lg flex mb-6">
              <button type="button" id="tab-intern" onclick="switchRole('intern')" class="flex-1 py-2 text-xs font-semibold rounded-md bg-blue-900 text-white transition-all shadow-sm">
                <i class="fa-solid fa-user-graduate mr-1"></i> Peserta Magang / Intern
              </button>
              <button type="button" id="tab-admin" onclick="switchRole('admin')" class="flex-1 py-2 text-xs font-semibold rounded-md text-slate-600 hover:text-slate-900 transition-all">
                <i class="fa-solid fa-user-shield mr-1"></i> Admin
              </button>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 flex gap-3 mb-5 text-blue-900 text-xs">
              <i class="fa-solid fa-shield-cat text-blue-600 mt-0.5"></i>
              <div>
                <span class="font-semibold block">Autentifikasi Kredensial Resmi</span>
                <span class="text-[11px] text-blue-700">Hanya menggunakan akun resmi yang diterbitkan oleh Admin.</span>
              </div>
            </div>

            <!-- Form PHP -->
            <form action="" method="POST" class="space-y-4">
              <input type="hidden" name="role" id="roleInput" value="intern">

              <div>
                <label id="idLabel" class="block text-xs font-semibold text-slate-700 mb-1">ID Magang</label>
                <div class="relative">
                  <i class="fa-solid fa-id-badge absolute left-3 top-3 text-slate-400 text-sm"></i>
                  <input type="text" name="username" id="username" placeholder="Masukkan ID Magang Anda (misal: INT-2024-001)" required
                         class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-xs focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
                </div>
              </div>

              <div>
                <div class="relative">
                  <i class="fa-solid fa-lock absolute left-3 top-3 text-slate-400 text-sm"></i>
                  <input type="password" name="password" id="password" placeholder="••••••••" required
                         class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-9 text-xs focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
                  <button type="button" onclick="togglePassword()" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                    <i id="eyeIcon" class="fa-regular fa-eye text-xs"></i>
                  </button>
                </div>
              </div>

              <button type="submit" class="w-full bg-blue-900 hover:bg-blue-950 text-white py-2.5 rounded-lg text-xs font-semibold transition-all flex items-center justify-center gap-2 shadow-md active:scale-[0.99]">
                Masuk ke Dasbor <i class="fa-solid fa-arrow-right"></i>
              </button>
            </form>
          </div>
        </div>

      </div>

    </div>
  </main>
  <?php include '../partials/footer.php'; ?>

  <script>
    function switchRole(role) {
      document.getElementById('roleInput').value = role;
      const tabIntern = document.getElementById('tab-intern');
      const tabAdmin = document.getElementById('tab-admin');
      const idLabel = document.getElementById('idLabel');
      const usernameInput = document.getElementById('username');

      if (role === 'intern') {
        tabIntern.className = "flex-1 py-2 text-xs font-semibold rounded-md bg-blue-900 text-white transition-all shadow-sm";
        tabAdmin.className = "flex-1 py-2 text-xs font-semibold rounded-md text-slate-600 hover:text-slate-900 transition-all";
        idLabel.textContent = "ID Magang";
        usernameInput.placeholder = "Masukkan ID Magang Anda (misal: INT-2024-001)";
      } else {
        tabAdmin.className = "flex-1 py-2 text-xs font-semibold rounded-md bg-blue-900 text-white transition-all shadow-sm";
        tabIntern.className = "flex-1 py-2 text-xs font-semibold rounded-md text-slate-600 hover:text-slate-900 transition-all";
        idLabel.textContent = "Email / NIK Admin";
        usernameInput.placeholder = "Masukkan Email atau NIK Admin";
      }
    }

    function togglePassword() {
      const passInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eyeIcon');
      if (passInput.type === 'password') {
        passInput.type = 'text';
        eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        passInput.type = 'password';
        eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
      }
    }
  </script>
</body>
</html>
