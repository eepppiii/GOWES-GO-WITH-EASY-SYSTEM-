<?php
session_start();
if (!isset($_SESSION['id_peminjam'])) { header("Location: ../auth/login.php"); exit; }
require_once '../config/koneksi.php';

$id_peminjam = $_SESSION['id_peminjam'];
$nama_user   = $_SESSION['nama'];
$inisial     = strtoupper(substr($nama_user, 0, 1));

try {
    $sql = "SELECT p.*, s.kode_qr, sh.nama_shelter FROM peminjaman p
            JOIN sepeda s ON p.id_sepeda = s.id_sepeda JOIN shelter sh ON s.id_shelter = sh.id_shelter
            WHERE p.id_peminjam = :id_peminjam AND p.waktu_kembali IS NOT NULL ORDER BY p.waktu_mulai DESC";
    $stmt = $pdo->prepare($sql); $stmt->execute(['id_peminjam' => $id_peminjam]); $riwayat = $stmt->fetchAll();
} catch (PDOException $e) { die("Error: " . $e->getMessage()); }

$bulan_indo = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Riwayat · GOWES</title>
  <link rel="icon" href="../img/logo.png">
  <link rel="stylesheet" href="../assets/css/fonts.css"><link rel="stylesheet" href="../assets/css/tokens.css">
  <link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/components.css">
  <link rel="stylesheet" href="../assets/css/mahasiswa.css"><link rel="stylesheet" href="../assets/css/loader.css">
</head>
<body class="user ">
  <header class="topbar">
    <a class="brand" href="dashboard.php"><img src="../img/logo.png" alt="GOWES"></a>
    <nav class="topnav">
      <a class="navitem" href="dashboard.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9.6 10 3.5l7 6.1V16a1 1 0 0 1-1 1h-3.4v-4.6H7.4V17H4a1 1 0 0 1-1-1z" /></svg></span>Dashboard</a>
      <a class="navitem" href="peminjaman.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="4.8" cy="13" r="3" /><circle cx="15.2" cy="13" r="3" /><path d="M4.8 13 8 7.2h4.2l3 5.8M8 7.2 7 5H5M12.2 7.2 10.6 13" /></svg></span>Peminjaman</a>
      <a class="navitem" href="scan.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7V4.5A1.5 1.5 0 0 1 4.5 3H7M13 3h2.5A1.5 1.5 0 0 1 17 4.5V7M17 13v2.5a1.5 1.5 0 0 1-1.5 1.5H13M7 17H4.5A1.5 1.5 0 0 1 3 15.5V13M6.5 10h7" /></svg></span>Scan QR</a>
      <a class="navitem on" href="profil.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="10" cy="6.6" r="3.2" /><path d="M3.8 17c.9-3.1 3.2-4.6 6.2-4.6s5.3 1.5 6.2 4.6" /></svg></span>Profil</a>
    </nav>
    <a href="profil.php" class="user-pill-btn logged-in" title="Buka Profil" style="text-decoration: none;"><span><?= htmlspecialchars($nama_user) ?></span><div class="avatar-sm"><?= $inisial ?></div></a>
  </header>

  <main class="page ">
    <div class="col ">
      <a class="back d-only" href="profil.php">‹ Kembali</a>
      <h1 class="title" style="margin-top: 0;">Riwayat Peminjaman</h1>
      
      <?php if (count($riwayat) > 0): ?>
          <?php foreach ($riwayat as $row): ?>
              <?php 
                  $timestamp = strtotime($row['waktu_mulai']);
                  $tanggal = date('d', $timestamp) . ' ' . $bulan_indo[(int)date('m', $timestamp)] . ' ' . date('Y', $timestamp);
                  $jam_kembali = date('H.i', strtotime($row['waktu_kembali'])) . ' WIB';
                  $is_terlambat = ($row['status_keterlambatan'] === 'Terlambat');
                  $chip_class = $is_terlambat ? 'chip-bad' : 'chip-ok';
                  $chip_text  = $is_terlambat ? 'Terlambat' : 'Tepat waktu';
              ?>
              <section class="card hist">
                <div class="hist-head">
                  <span class="hist-title"><?= htmlspecialchars($row['kode_qr']) ?> · <?= htmlspecialchars($row['nama_shelter']) ?></span>
                  <span class="chip <?= $chip_class ?>"><?= $chip_text ?></span>
                </div>
                <div class="rows">
                  <div class="row"><span class="k">Tanggal</span><span class="v"><?= $tanggal ?></span></div>
                  <div class="row"><span class="k">Durasi</span><span class="v"><?= htmlspecialchars($row['durasi_pinjam']) ?> jam</span></div>
                  <div class="row"><span class="k">Waktu kembali</span><span class="v"><?= $jam_kembali ?></span></div>
                </div>
              </section>
          <?php endforeach; ?>
      <?php else: ?>
          <section class="card" style="text-align: center; padding: 40px 20px;">
              <div style="font-size: 40px; margin-bottom: 15px;">📋</div>
              <h2 style="font-size: 18px; margin-bottom: 10px; color: #1e293b;">Belum Ada Riwayat</h2>
              <p style="font-size: 14px; color: #64748b; line-height: 1.5;">Anda belum pernah menyelesaikan peminjaman sepeda.</p>
          </section>
      <?php endif; ?>
    </div>
  </main>

  <nav class="tabbar">
    <div class="tabbar-in">
      <a class="tab" href="dashboard.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9.6 10 3.5l7 6.1V16a1 1 0 0 1-1 1h-3.4v-4.6H7.4V17H4a1 1 0 0 1-1-1z" /></svg></span>Dashboard</a>
      <a class="tab" href="peminjaman.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="4.8" cy="13" r="3" /><circle cx="15.2" cy="13" r="3" /><path d="M4.8 13 8 7.2h4.2l3 5.8M8 7.2 7 5H5M12.2 7.2 10.6 13" /></svg></span>Peminjaman</a>
      <a class="tab" href="scan.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7V4.5A1.5 1.5 0 0 1 4.5 3H7M13 3h2.5A1.5 1.5 0 0 1 17 4.5V7M17 13v2.5a1.5 1.5 0 0 1-1.5 1.5H13M7 17H4.5A1.5 1.5 0 0 1 3 15.5V13M6.5 10h7" /></svg></span>Scan QR</a>
      <a class="tab on" href="profil.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="10" cy="6.6" r="3.2" /><path d="M3.8 17c.9-3.1 3.2-4.6 6.2-4.6s5.3 1.5 6.2 4.6" /></svg></span>Profil</a>
    </div>
  </nav>
  <script src="../assets/js/common.js"></script>
  <script src="../assets/js/loader.js"></script>
</body>
</html>