<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/koneksi.php'; // Memanggil koneksi database

// Jika sudah login, redirect langsung ke dashboard
if (is_logged_in()) {
    $role = current_user_role();
    if ($role === 'admin' || $role === 'superadmin') {
        header('Location: ../admin/admin-dashboard.php');
    } else {
        header('Location: ../dashboard.php');
    }
    exit;
}

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

            // ── Remember-Me: buat token persistent login ──
            // Auto-migration: buat tabel jika belum ada
            @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `remember_tokens` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `token_hash` VARCHAR(255) NOT NULL,
                `expires_at` DATETIME NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_token_hash` (`token_hash`),
                INDEX `idx_user_id` (`user_id`),
                INDEX `idx_expires` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Hapus token lama user ini (opsional: biar ga numpuk)
            $del_old = mysqli_prepare($conn, "DELETE FROM remember_tokens WHERE user_id = ?");
            if ($del_old) {
                mysqli_stmt_bind_param($del_old, "i", $user_data['id']);
                mysqli_stmt_execute($del_old);
                mysqli_stmt_close($del_old);
            }

            // Buat token baru
            $rm_token   = bin2hex(random_bytes(32));
            $rm_hash    = hash('sha256', $rm_token);
            $rm_expires = date('Y-m-d H:i:s', time() + 30 * 24 * 3600); // 30 hari

            $ins_token = mysqli_prepare($conn, "INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
            if ($ins_token) {
                mysqli_stmt_bind_param($ins_token, "iss", $user_data['id'], $rm_hash, $rm_expires);
                mysqli_stmt_execute($ins_token);
                mysqli_stmt_close($ins_token);
            }

            // Set cookie 30 hari
            setcookie('remember_me', $rm_token, [
                'expires'  => time() + 30 * 24 * 3600,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            ]);
            // ── End Remember-Me ──
            
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
  <title>AnakMagang - Portal Masuk Admin</title>
<?php include __DIR__ . '/../../partials/favicon.php'; ?>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  <script src="../../shared-config.js?v=<?php echo time(); ?>"></script>
  <link rel="stylesheet" href="../../style.css?v=<?php echo time(); ?>">
  <style>
    .bg-grid {
      background-image: radial-gradient(rgba(255, 255, 255, 0.15) 1px, transparent 0);
      background-size: 16px 16px;
    }
  </style>
</head>
<body class="bg-slate-100 dark:bg-[#0b1329] font-sans antialiased min-h-screen flex flex-col justify-between text-slate-900 dark:text-slate-100 transition-colors">
  <!-- Navbar Header -->
  <header class="w-full px-6 md:px-10 py-4 flex justify-between items-center text-slate-700 dark:text-slate-200 shrink-0">
    <div class="flex items-center gap-2 font-bold text-xl text-blue-900 dark:text-blue-400">
       <img src="../../uploads/Icon/Anak_Magang_Icon.jpg.jpeg" alt="Logo AnakMagang" class="w-8 h-8 rounded-lg object-cover shadow-xs border border-outline-variant/30 shrink-0"/>
       <span class="font-headline-md text-headline-md font-bold text-primary dark:text-blue-400" data-i18n="brand_name">AnakMagang</span>
    </div>
    <div class="flex items-center gap-2">
      <!-- Dark Mode Toggle Button -->
      <button type="button" onclick="toggleDarkMode()" class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#121e3d] text-slate-700 dark:text-slate-200 hover:text-primary hover:bg-slate-50 dark:hover:bg-[#1a274e] transition-colors flex items-center justify-center cursor-pointer shadow-xs" title="Mode Gelap / Terang" aria-label="Toggle Dark Mode">
        <span class="material-symbols-outlined text-[20px] dark-icon-sun hidden text-amber-400">light_mode</span>
        <span class="material-symbols-outlined text-[20px] dark-icon-moon text-slate-600 dark:text-slate-300">dark_mode</span>
      </button>
    </div>
  </header>

  <!-- Main Container -->
  <main class="flex-grow flex items-center justify-center p-4">
    <div class="max-w-4xl w-full flex flex-col gap-3">
      
      <!-- Button Kembali ke Beranda (Pojok Atas Kiri Container) -->
      <div class="flex justify-start">
        <a href="../../index.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-[#121e3d] border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-blue-900 hover:text-white dark:hover:bg-blue-600 dark:hover:border-blue-600 dark:hover:text-white text-xs font-bold shadow-xs hover:shadow-md transition-all active:scale-95 group" title="Kembali ke Beranda">
          <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
          <span>Kembali ke Beranda</span>
        </a>
      </div>

      <!-- Card Big Container (Menampung Selamat Datang & Form Login) -->
      <div class="bg-white dark:bg-[#121e3d] rounded-2xl shadow-2xl flex flex-col md:flex-row w-full overflow-hidden border border-slate-200 dark:border-slate-700">
        
        <!-- Panel Kiri: Selamat Datang Anak Magang -->
        <div class="md:w-5/12 bg-blue-900 dark:bg-[#0f1b38] bg-grid text-white p-4 md:p-8 flex flex-col justify-between relative border-b md:border-b-0 md:border-r border-blue-800/60 dark:border-slate-800">
          <div>
            <span class="hidden md:inline-block bg-blue-800/80 dark:bg-blue-950/80 text-blue-200 text-[10px] md:text-xs px-2.5 py-0.5 rounded-full font-semibold mb-2 md:mb-6 border border-blue-700 dark:border-blue-800">
              <i class="fa-solid fa-shield-halved mr-1"></i> Sistem Karir Terintegrasi v3.4
            </span>
            <h1 class="text-base md:text-2xl font-bold leading-tight mb-0 md:mb-3 text-center md:text-left">Selamat Datang Anak Magang</h1>
            <p class="hidden md:block text-[11px] md:text-xs text-blue-200 leading-tight mb-3 md:mb-8">
              Platform Terpadu Manajemen Magang, Presensi & Gamifikasi Portofolio Talenta Digital Indonesia.
            </p>

            <div class="hidden md:flex md:flex-col md:gap-0 md:space-y-3">
              <div class="bg-blue-800/40 dark:bg-blue-950/50 border border-blue-700/50 dark:border-blue-800/40 rounded-lg md:rounded-xl p-2 md:p-3 flex items-start gap-2 md:gap-3 backdrop-blur-sm">
                <i class="fa-solid fa-location-dot mt-0.5 text-xs md:text-sm text-blue-300 flex-shrink-0"></i>
                <div>
                  <h4 class="text-[11px] md:text-xs font-semibold leading-snug">Presensi Geofencing & Real-Time</h4>
                  <p class="text-[9px] md:text-[10px] text-blue-200 leading-tight">Verifikasi lokasi GPS instan & rekap otomatis.</p>
                </div>
              </div>
              <div class="bg-blue-800/40 dark:bg-blue-950/50 border border-blue-700/50 dark:border-blue-800/40 rounded-lg md:rounded-xl p-2 md:p-3 flex items-start gap-2 md:gap-3 backdrop-blur-sm">
                <i class="fa-solid fa-gamepad mt-0.5 text-xs md:text-sm text-blue-300 flex-shrink-0"></i>
                <div>
                  <h4 class="text-[11px] md:text-xs font-semibold leading-snug">Kanban XP & Gamifikasi Tugas</h4>
                  <p class="text-[9px] md:text-[10px] text-blue-200 leading-tight">Kumpulkan poin pengalaman dan buka badge.</p>
                </div>
              </div>
              <div class="bg-blue-800/40 dark:bg-blue-950/50 border border-blue-700/50 dark:border-blue-800/40 rounded-xl p-2 md:p-3 flex items-start gap-2 md:gap-3 backdrop-blur-sm">
                <i class="fa-solid fa-certificate mt-0.5 text-xs md:text-sm text-blue-300 flex-shrink-0"></i>
                <div>
                  <h4 class="text-[11px] md:text-xs font-semibold leading-snug">Sertifikat Digital Terverifikasi</h4>
                  <p class="text-[9px] md:text-[10px] text-blue-200 leading-tight">QR terenkripsi yang diakui 200+ mitra industri.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Panel Kanan: Masuk ke Akun Anda -->
        <div class="md:w-7/12 p-6 sm:p-8 flex flex-col justify-between bg-white dark:bg-[#121e3d]">
          <div>
            <div class="flex justify-between items-center mb-4">
              <span class="text-xs font-bold tracking-wider text-blue-900 dark:text-blue-400 flex items-center gap-1">
                <i class="fa-solid fa-id-card-clip"></i> PORTAL AKSES
              </span>
            </div>

            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Masuk ke Akun Anda</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Pilih peran Anda dan masukkan kredensial resmi untuk mengakses dasbor.</p>

            <!-- Menampilkan Error Login -->
            <?php if (!empty($error)): ?>
              <div class="bg-red-100 dark:bg-red-950/60 border border-red-300 dark:border-red-800 text-red-700 dark:text-red-300 px-3 py-2 rounded-xl text-xs mb-4 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
            <?php endif; ?>

            <div class="bg-slate-100 dark:bg-[#0b1329] p-1 rounded-xl flex mb-6 border border-slate-200/60 dark:border-slate-800">
              <button type="button" id="tab-intern" onclick="switchRole('intern')" class="flex-1 py-2 text-xs font-semibold rounded-lg bg-blue-900 dark:bg-blue-600 text-white transition-all shadow-sm">
                <i class="fa-solid fa-user-graduate mr-1"></i>Intern
              </button>
              <button type="button" id="tab-admin" onclick="switchRole('admin')" class="flex-1 py-2 text-xs font-semibold rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-all">
                <i class="fa-solid fa-user-shield mr-1"></i> Admin
              </button>
            </div>

            <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 rounded-xl p-3 flex gap-3 mb-5 text-blue-900 dark:text-blue-200 text-xs">
              <i class="fa-solid fa-shield-cat text-blue-600 dark:text-blue-400 mt-0.5"></i>
              <div>
                <span class="font-semibold block">Autentifikasi Kredensial Resmi</span>
                <span class="text-[11px] text-blue-700 dark:text-blue-300">Hanya menggunakan akun resmi yang diterbitkan oleh Admin.</span>
              </div>
            </div>

            <!-- Form PHP -->
            <form action="" method="POST" class="space-y-4">
              <input type="hidden" name="role" id="roleInput" value="intern">

              <div>
                <label id="idLabel" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">ID Magang</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 text-sm">
                    <i class="fa-solid fa-id-badge"></i>
                  </div>
                  <input type="text" name="username" id="username" placeholder="Masukkan ID Magang Anda (misal: INT-2024-001)" required
                         class="w-full bg-slate-50 dark:bg-[#0b1329] border border-slate-200 dark:border-slate-700 rounded-xl py-2.5 pl-9 pr-3 text-xs text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white dark:focus:bg-[#0b1329] transition-all">
                </div>
              </div>

              <div>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 text-sm">
                    <i class="fa-solid fa-lock"></i>
                  </div>
                  <input type="password" name="password" id="password" placeholder="••••••••" required
                         class="w-full bg-slate-50 dark:bg-[#0b1329] border border-slate-200 dark:border-slate-700 rounded-xl py-2.5 pl-9 pr-9 text-xs text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white dark:focus:bg-[#0b1329] transition-all">
                  <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i id="eyeIcon" class="fa-regular fa-eye text-xs"></i>
                  </button>
                </div>
              </div>

              <button type="submit" class="w-full bg-blue-900 hover:bg-blue-950 dark:bg-blue-600 dark:hover:bg-blue-700 text-white py-2.5 rounded-xl text-xs font-semibold transition-all flex items-center justify-center gap-2 shadow-md active:scale-[0.99]">
                Masuk ke Dasbor <i class="fa-solid fa-arrow-right"></i>
              </button>
            </form>
          </div>
        </div>

      </div>

    </div>
  </main>
  <?php include '../../partials/footer.php'; ?>

  <script>
    function switchRole(role) {
      document.getElementById('roleInput').value = role;
      const tabIntern = document.getElementById('tab-intern');
      const tabAdmin = document.getElementById('tab-admin');
      const idLabel = document.getElementById('idLabel');
      const usernameInput = document.getElementById('username');

      if (role === 'intern') {
        tabIntern.className = "flex-1 py-2 text-xs font-semibold rounded-lg bg-blue-900 dark:bg-blue-600 text-white transition-all shadow-sm";
        tabAdmin.className = "flex-1 py-2 text-xs font-semibold rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-all";
        idLabel.textContent = "ID Magang";
        usernameInput.placeholder = "Masukkan ID Magang Anda (misal: INT-2024-001)";
      } else {
        tabAdmin.className = "flex-1 py-2 text-xs font-semibold rounded-lg bg-blue-900 dark:bg-blue-600 text-white transition-all shadow-sm";
        tabIntern.className = "flex-1 py-2 text-xs font-semibold rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-all";
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
