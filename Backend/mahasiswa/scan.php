<?php
session_start();
if (!isset($_SESSION['id_peminjam'])) { header("Location: ../auth/login.php"); exit; }
require_once '../config/koneksi.php';

$id_peminjam = $_SESSION['id_peminjam'];
$nama_user   = $_SESSION['nama'];
$inisial     = strtoupper(substr($nama_user, 0, 1));

$stmtCheck = $pdo->prepare("SELECT id_peminjaman FROM peminjaman WHERE id_peminjam = :id AND waktu_kembali IS NULL");
$stmtCheck->execute(['id' => $id_peminjam]);
$active_loan = $stmtCheck->fetch();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Scan QR · GOWES</title>
  <link rel="icon" href="../img/logo.png">
  <link rel="stylesheet" href="../assets/css/fonts.css"><link rel="stylesheet" href="../assets/css/tokens.css">
  <link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/components.css">
  <link rel="stylesheet" href="../assets/css/mahasiswa.css"><link rel="stylesheet" href="../assets/css/loader.css">
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body class="user scan-page">
  <header class="topbar">
    <a class="brand" href="dashboard.php"><img src="../img/logo.png" alt="GOWES"></a>
    <nav class="topnav">
      <a class="navitem" href="dashboard.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9.6 10 3.5l7 6.1V16a1 1 0 0 1-1 1h-3.4v-4.6H7.4V17H4a1 1 0 0 1-1-1z" /></svg></span>Dashboard</a>
      <a class="navitem" href="peminjaman.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="4.8" cy="13" r="3" /><circle cx="15.2" cy="13" r="3" /><path d="M4.8 13 8 7.2h4.2l3 5.8M8 7.2 7 5H5M12.2 7.2 10.6 13" /></svg></span>Peminjaman</a>
      <a class="navitem on" href="scan.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7V4.5A1.5 1.5 0 0 1 4.5 3H7M13 3h2.5A1.5 1.5 0 0 1 17 4.5V7M17 13v2.5a1.5 1.5 0 0 1-1.5 1.5H13M7 17H4.5A1.5 1.5 0 0 1 3 15.5V13M6.5 10h7" /></svg></span>Scan QR</a>
      <a class="navitem" href="profil.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="10" cy="6.6" r="3.2" /><path d="M3.8 17c.9-3.1 3.2-4.6 6.2-4.6s5.3 1.5 6.2 4.6" /></svg></span>Profil</a>
    </nav>
    <a href="profil.php" class="user-pill-btn logged-in" title="Buka Profil" style="text-decoration: none;"><span><?= htmlspecialchars($nama_user) ?></span><div class="avatar-sm"><?= $inisial ?></div></a>
  </header>

  <main class="page">
    <div class="scan-container">
      <h1 class="title">Scan QR Go-bike</h1>
      <p class="lead">Arahkan kamera ke kode QR pada slot Go-bike di shelter.</p>
      <?php if ($active_loan): ?>
          <div class="alert-error" style="background-color: #fff3e0; color: #e65100; border-color: #ffe0b2; margin-top: 20px;">Anda masih memiliki peminjaman aktif.<br>Kembalikan sepeda terlebih dahulu.</div>
          <a href="peminjaman.php" class="btn btn-primary" style="text-decoration: none;">Lihat Peminjaman</a>
      <?php else: ?>
          <div class="scanner" id="scanner-placeholder" onclick="startCamera()" title="Klik untuk membuka kamera">
            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle>
            </svg>
            <p style="margin-top: 15px; font-size: 16px; font-weight: 700; color: var(--primary);">Buka Kamera</p>
          </div>
          <div id="reader" style="display: none;"></div>
          <p class="scan-hint-d" id="hint-text">Klik kotak di atas untuk membuka kamera.</p>
          <button type="button" id="btn-close-camera" class="btn-close-camera" style="display: none;" onclick="stopCamera()">Tutup Kamera</button>
      <?php endif; ?>
    </div>
  </main>

  <nav class="tabbar">
    <div class="tabbar-in">
      <a class="tab" href="dashboard.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9.6 10 3.5l7 6.1V16a1 1 0 0 1-1 1h-3.4v-4.6H7.4V17H4a1 1 0 0 1-1-1z" /></svg></span>Dashboard</a>
      <a class="tab" href="peminjaman.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="4.8" cy="13" r="3" /><circle cx="15.2" cy="13" r="3" /><path d="M4.8 13 8 7.2h4.2l3 5.8M8 7.2 7 5H5M12.2 7.2 10.6 13" /></svg></span>Peminjaman</a>
      <a class="tab on" href="scan.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7V4.5A1.5 1.5 0 0 1 4.5 3H7M13 3h2.5A1.5 1.5 0 0 1 17 4.5V7M17 13v2.5a1.5 1.5 0 0 1-1.5 1.5H13M7 17H4.5A1.5 1.5 0 0 1 3 15.5V13M6.5 10h7" /></svg></span>Scan QR</a>
      <a class="tab" href="profil.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="10" cy="6.6" r="3.2" /><path d="M3.8 17c.9-3.1 3.2-4.6 6.2-4.6s5.3 1.5 6.2 4.6" /></svg></span>Profil</a>
    </div>
  </nav>
  <script src="../assets/js/common.js"></script>
  <script src="../assets/js/scan.js"></script>
  <script src="../assets/js/loader.js"></script>
</body>
</html>