<?php
// ==========================================
// 1. KONFIGURASI SESSION (HARDENING)
// ==========================================
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_set_cookie_params(['samesite' => 'Strict']);
session_start();

// Fitur Logout
if (isset($_GET['logout'])) {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    header("Location: login.php");
    exit;
}

// Jika sudah login, arahkan ke halaman yang sesuai
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard.php"); 
    } elseif ($_SESSION['role'] === 'petugas') {
        header("Location: ../petugas/dashboard.php"); 
    } else {
        header("Location: ../mahasiswa/peminjaman.php"); 
    }
    exit;
}

require_once '../config/koneksi.php'; 

// ==========================================
// 2. GENERATE CSRF TOKEN
// ==========================================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

// ==========================================
// 3. PROSES LOGIN
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verifikasi CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token. Silakan refresh halaman.");
    }

    $no_identitas = trim($_POST['nim']);
    $password     = $_POST['password'];
    
    // Validasi Panjang Input
    if (strlen($no_identitas) > 50 || strlen($password) > 100) {
        $error = "Input terlalu panjang!";
    } elseif (!empty($no_identitas) && !empty($password)) {
        try {
            // 1. Cek ke tabel Peminjam (Mahasiswa)
            $stmt = $pdo->prepare("SELECT * FROM peminjam WHERE no_identitas = :id");
            $stmt->execute(['id' => $no_identitas]);
            $peminjam = $stmt->fetch();

            if ($peminjam && (password_verify($password, $peminjam['password']) || $password === $peminjam['password'])) {
                session_regenerate_id(true); 
                $_SESSION['role']           = 'peminjam';
                $_SESSION['id_peminjam']    = $peminjam['id_peminjam'];
                $_SESSION['nama']           = $peminjam['nama']; 
                $_SESSION['no_identitas']   = $peminjam['no_identitas'];
                $_SESSION['jenis_peminjam'] = $peminjam['jenis_peminjam'];
                header("Location: ../mahasiswa/peminjaman.php"); 
                exit;
            }

            // 2. Cek ke tabel Petugas
            $stmt2 = $pdo->prepare("SELECT * FROM petugas WHERE username = :id");
            $stmt2->execute(['id' => $no_identitas]);
            $petugas = $stmt2->fetch();

            if ($petugas && (password_verify($password, $petugas['password']) || $password === $petugas['password'])) {
                session_regenerate_id(true);
                $_SESSION['role']        = 'petugas';
                $_SESSION['id_petugas']  = $petugas['id_petugas'];
                $_SESSION['nama']        = $petugas['username']; 
                $_SESSION['username']    = $petugas['username'];
                $_SESSION['id_shelter']  = $petugas['id_shelter'];
                header("Location: ../petugas/dashboard.php"); 
                exit;
            }

            // 3. Cek ke tabel Admin
            $stmt3 = $pdo->prepare("SELECT * FROM admin WHERE username = :id");
            $stmt3->execute(['id' => $no_identitas]);
            $admin = $stmt3->fetch();

            if ($admin && (password_verify($password, $admin['password']) || $password === $admin['password'])) {
                session_regenerate_id(true);
                $_SESSION['role']      = 'admin';
                $_SESSION['id_admin']  = $admin['id_admin'];
                $_SESSION['nama']      = $admin['username']; 
                $_SESSION['username']  = $admin['username'];
                header("Location: ../admin/dashboard.php"); 
                exit;
            }

            $error = "Nomor Identitas / Username atau Password salah!";

        } catch (PDOException $e) {
            error_log($e->getMessage()); 
            $error = "Terjadi kesalahan pada sistem. Silakan coba beberapa saat lagi.";
        }
    } else {
        $error = "Kolom identitas dan password wajib diisi!";
    }
}
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk · GOWES</title>
  <link rel="icon" href="../img/logo.png">

  <link rel="stylesheet" href="../assets/css/loader.css">
  <link rel="stylesheet" href="../assets/css/fonts.css">
  <link rel="stylesheet" href="../assets/css/tokens.css">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/components.css">
  <link rel="stylesheet" href="../assets/css/login-mahasiswa.css">
</head>
<body class="login">
  
  <div class="login-wrap">
    
    <aside class="login-brand">
      <div class="brand-content">
        <img src="../img/logo-white.png" alt="GOWES" class="brand-logo">
        <p class="tag">Pinjam Go-bike, Jelajahi Kampus!</p>
      </div>
      <p class="about">
        Nikmati perjalanan di kampus yang lebih praktis dan ramah lingkungan bersama GOWES.
        Pinjam Sepeda dengan mudah tanpa perlu kartu identitas fisik.
        Cukup scan QR Code, pilih durasi peminjaman, dan siap berkeliling kampus.
      </p>
    </aside>
    
    <section class="login-form">
      
      <div class="mobile-logo">
        <img src="../img/logo.png" alt="GOWES">
        <p class="mobile-sub">Masuk untuk meminjam Go-bike POLINEMA</p>
      </div>

      <div class="login-form-in">
        
        <h1 class="desktop-title">LOGIN</h1>
        <p class="desktop-sub">Masuk untuk meminjam Go-bike POLINEMA</p>

        <?php if (!empty($error)): ?>
            <div class="alert-error" style="text-align: center; margin-bottom: 15px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
          <!-- HIDDEN CSRF TOKEN -->
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

          <input class="input" type="text" name="nim" id="inputNim" placeholder="Nomor Identitas / Username" autocomplete="username" maxlength="50" value="<?= htmlspecialchars($_POST['nim'] ?? '') ?>" required>
          
          <div class="password-wrapper">
              <input class="input" type="password" name="password" id="passwordInput" placeholder="Password" autocomplete="current-password" maxlength="100" required>
              
              <!-- Ikon Mata dipanggil lewat onclick yang ada di login.js -->
              <span class="toggle-password" onclick="togglePassword()" title="Lihat/Sembunyikan Password">
                  <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                      <circle cx="12" cy="12" r="3"></circle>
                  </svg>
              </span>
          </div>

          <button class="btn btn-primary" type="submit" id="submitBtn">Masuk</button>
          
          <div class="back-wrapper">
              <a href="../public/dashboard.php" class="back-to-dashboard">
                  Kembali ke Dashboard Utama
              </a>
          </div>
        </form>

      </div>
      
      <div class="login-footer">
          <p>GOWES siap menjelajahi kampus dengan lebih praktis dan nyaman!</p>
      </div>

    </section>
  </div>

  <!-- HANYA MEMANGGIL FILE JS EKSTERNAL -->
  <script src="../assets/js/login.js"></script>
  <script src="../assets/js/loader.js"></script>
</body>
</html>