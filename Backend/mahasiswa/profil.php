<?php
session_start();

// 1. LOGIKA LOGOUT
if (isset($_GET['logout'])) {
    $_SESSION = array(); 
    session_destroy();   
    header("Location: ../auth/login.php"); 
    exit;
}

// 2. CEK STATUS LOGIN
if (!isset($_SESSION['id_peminjam'])) {
    header("Location: ../auth/login.php");
    exit;
}

// 3. KONEKSI & AMBIL DATA
require_once '../config/koneksi.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM peminjam WHERE id_peminjam = :id");
    $stmt->execute(['id' => $_SESSION['id_peminjam']]);
    $user = $stmt->fetch();

    if (!$user) {
        session_destroy();
        header("Location: ../auth/login.php");
        exit;
    }

    $inisial = strtoupper(substr($user['nama'], 0, 1));
} catch (PDOException $e) {
    die("Error mengambil data profil: " . $e->getMessage());
}
?>

<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profil · GOWES</title>
  <link rel="icon" href="../img/logo.png">
  
  <link rel="stylesheet" href="../assets/css/fonts.css">
  <link rel="stylesheet" href="../assets/css/tokens.css">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/components.css">
  <link rel="stylesheet" href="../assets/css/mahasiswa.css">
  <link rel="stylesheet" href="../assets/css/loader.css">
</head>

<body class="user ">

  <header class="topbar">
    <a class="brand" href="dashboard.php"><img src="../img/logo.png" alt="GOWES"></a>
    
    <nav class="topnav" aria-label="Menu utama">
      <a class="navitem" href="dashboard.php">
        <span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9.6 10 3.5l7 6.1V16a1 1 0 0 1-1 1h-3.4v-4.6H7.4V17H4a1 1 0 0 1-1-1z" /></svg></span>Dashboard</a>
      <a class="navitem" href="peminjaman.php">
        <span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="4.8" cy="13" r="3" /><circle cx="15.2" cy="13" r="3" /><path d="M4.8 13 8 7.2h4.2l3 5.8M8 7.2 7 5H5M12.2 7.2 10.6 13" /></svg></span>Peminjaman</a>
      <a class="navitem" href="scan.php">
        <span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7V4.5A1.5 1.5 0 0 1 4.5 3H7M13 3h2.5A1.5 1.5 0 0 1 17 4.5V7M17 13v2.5a1.5 1.5 0 0 1-1.5 1.5H13M7 17H4.5A1.5 1.5 0 0 1 3 15.5V13M6.5 10h7" /></svg></span>Scan QR</a>
      <a class="navitem on" href="profil.php">
        <span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="10" cy="6.6" r="3.2" /><path d="M3.8 17c.9-3.1 3.2-4.6 6.2-4.6s5.3 1.5 6.2 4.6" /></svg></span>Profil</a>
    </nav>
    
    <a href="profil.php" class="user-pill-btn logged-in" title="Buka Profil" style="text-decoration: none;">
        <span><?= htmlspecialchars($user['nama']) ?></span>
        <div class="avatar-sm"><?= $inisial ?></div>
    </a>
  </header>

  <main class="page ">
    <div class="col ">

      <section class="profile-card">
        <div class="avatar-lg"><?= $inisial ?></div>
        <h1 class="title"><?= htmlspecialchars($user['nama']) ?></h1>
      </section>

      <section class="card profile-data">
        <div class="rows-box">
          <div class="rows">
            <div class="row"><span class="k">NIM</span><span class="v"><?= htmlspecialchars($user['no_identitas']) ?></span></div>
            <div class="row"><span class="k">Nama</span><span class="v"><?= htmlspecialchars($user['nama']) ?></span></div>
            <div class="row"><span class="k">Peran</span><span class="v"><?= htmlspecialchars($user['jenis_peminjam']) ?></span></div>
          </div>
        </div>
        
        <div class="menu-list">
          <a class="menu-btn" href="riwayat.php">
            <span class="menu-ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3.4 10a6.6 6.6 0 1 0 2-4.7L3.4 7.3" /><path d="M3.4 3.6v3.7h3.7M10 6.6V10l2.6 1.6" /></svg></span>Riwayat Peminjaman</a>
          <a class="menu-btn" href="sanksi.php">
            <span class="menu-ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 2.6 16 5v4.6c0 3.9-2.5 6.5-6 7.8-3.5-1.3-6-3.9-6-7.8V5z" /><path d="M10 7.2v3.4M10 13.1v.01" /></svg></span>Status Sanksi</a>
        </div>
      </section>

      <!-- TOMBOL KELUAR (Ditambahkan Flexbox agar teks di tengah) -->
      <button type="button" class="btn btn-danger" onclick="openLogoutModal()" style="display: flex; justify-content: center; align-items: center; width: 100%; text-decoration: none; border: none; cursor: pointer; font-family: inherit; font-size: 15px; padding: 16px; border-radius: 14px;">
          Keluar
      </button>

    </div>
  </main>

  <nav class="tabbar" aria-label="Menu utama">
    <div class="tabbar-in">
      <a class="tab" href="dashboard.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9.6 10 3.5l7 6.1V16a1 1 0 0 1-1 1h-3.4v-4.6H7.4V17H4a1 1 0 0 1-1-1z" /></svg></span>Dashboard</a>
      <a class="tab" href="peminjaman.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="4.8" cy="13" r="3" /><circle cx="15.2" cy="13" r="3" /><path d="M4.8 13 8 7.2h4.2l3 5.8M8 7.2 7 5H5M12.2 7.2 10.6 13" /></svg></span>Peminjaman</a>
      <a class="tab" href="scan.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7V4.5A1.5 1.5 0 0 1 4.5 3H7M13 3h2.5A1.5 1.5 0 0 1 17 4.5V7M17 13v2.5a1.5 1.5 0 0 1-1.5 1.5H13M7 17H4.5A1.5 1.5 0 0 1 3 15.5V13M6.5 10h7" /></svg></span>Scan QR</a>
      <a class="tab on" href="profil.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="10" cy="6.6" r="3.2" /><path d="M3.8 17c.9-3.1 3.2-4.6 6.2-4.6s5.3 1.5 6.2 4.6" /></svg></span>Profil</a>
    </div>
  </nav>

  <!-- MODAL POP-UP LOGOUT -->
  <div class="modal-overlay" id="logoutModal">
      <div class="modal-content">
          <div class="modal-icon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                  <polyline points="16 17 21 12 16 7"></polyline>
                  <line x1="21" y1="12" x2="9" y2="12"></line>
              </svg>
          </div>
          <h2>Konfirmasi Keluar</h2>
          <p>Apakah Anda yakin ingin keluar dari akun ini? Anda harus login kembali untuk meminjam sepeda.</p>
          
          <div class="modal-actions">
              <button type="button" class="btn-cancel" onclick="closeLogoutModal()">Batal</button>
              <a href="?logout=true" class="btn-confirm">Ya, Keluar</a>
          </div>
      </div>
  </div>

  <script src="../assets/js/common.js"></script>
  <script src="../assets/js/profil.js"></script>
  <script src="../assets/js/loader.js"></script>
</body>
</html>