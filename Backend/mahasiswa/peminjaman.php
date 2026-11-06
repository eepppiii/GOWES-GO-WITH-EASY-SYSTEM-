<?php
session_start();
if (!isset($_SESSION['id_peminjam'])) { header("Location: ../auth/login.php"); exit; }
require_once '../config/koneksi.php';

$id_peminjam = $_SESSION['id_peminjam'];
$nama_user   = $_SESSION['nama'];
$inisial     = strtoupper(substr($nama_user, 0, 1));

try {
    $sql = "SELECT p.id_peminjaman, p.waktu_mulai, p.batas_kembali, s.kode_qr, sh.nama_shelter
            FROM peminjaman p JOIN sepeda s ON p.id_sepeda = s.id_sepeda JOIN shelter sh ON s.id_shelter = sh.id_shelter
            WHERE p.id_peminjam = :id_peminjam AND p.waktu_kembali IS NULL ORDER BY p.waktu_mulai DESC LIMIT 1";
    $stmt = $pdo->prepare($sql); $stmt->execute(['id_peminjam' => $id_peminjam]); $peminjaman = $stmt->fetch();

    if ($peminjaman) {
        $waktu_mulai = date('H.i', strtotime($peminjaman['waktu_mulai']));
        $batas_kembali = date('H.i', strtotime($peminjaman['batas_kembali']));
        $batas_kembali_iso = date('Y-m-d\TH:i:s', strtotime($peminjaman['batas_kembali']));
        $id_peminjaman = $peminjaman['id_peminjaman'];
    }
} catch (PDOException $e) { die("Error: " . $e->getMessage()); }
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Peminjaman Saya · GOWES</title>
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
      <a class="navitem on" href="peminjaman.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="4.8" cy="13" r="3" /><circle cx="15.2" cy="13" r="3" /><path d="M4.8 13 8 7.2h4.2l3 5.8M8 7.2 7 5H5M12.2 7.2 10.6 13" /></svg></span>Peminjaman</a>
      <a class="navitem" href="scan.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7V4.5A1.5 1.5 0 0 1 4.5 3H7M13 3h2.5A1.5 1.5 0 0 1 17 4.5V7M17 13v2.5a1.5 1.5 0 0 1-1.5 1.5H13M7 17H4.5A1.5 1.5 0 0 1 3 15.5V13M6.5 10h7" /></svg></span>Scan QR</a>
      <a class="navitem" href="profil.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="10" cy="6.6" r="3.2" /><path d="M3.8 17c.9-3.1 3.2-4.6 6.2-4.6s5.3 1.5 6.2 4.6" /></svg></span>Profil</a>
    </nav>
    <a href="profil.php" class="user-pill-btn logged-in" title="Buka Profil" style="text-decoration: none;"><span><?= htmlspecialchars($nama_user) ?></span><div class="avatar-sm"><?= $inisial ?></div></a>
  </header>

  <main class="page ">
    <div class="col ">
      <h1 class="title">Peminjaman Saya</h1>
      <?php if ($peminjaman): ?>
          <section class="countdown">
            <div class="lbl">Sisa Waktu Peminjaman</div>
            <div class="time" id="countdown" data-target="<?= $batas_kembali_iso ?>">00:00:00</div>
            <div class="foot">Kembalikan sebelum <span><?= $batas_kembali ?></span> WIB</div>
          </section>
          <section class="card">
            <div class="rows">
              <div class="row"><span class="k">ID Go-bike</span><span class="v"><?= htmlspecialchars($peminjaman['kode_qr']) ?></span></div>
              <div class="row"><span class="k">Shelter</span><span class="v"><?= htmlspecialchars($peminjaman['nama_shelter']) ?></span></div>
              <div class="row"><span class="k">Waktu mulai</span><span class="v"><?= $waktu_mulai ?> WIB</span></div>
              <div class="row"><span class="k">Batas pengembalian</span><span class="v"><?= $batas_kembali ?> WIB</span></div>
            </div>
          </section>
          <a class="btn btn-primary" href="kembali.php?id=<?= $id_peminjaman ?>">Kembalikan Go-bike</a>
      <?php else: ?>
          <section class="card" style="text-align: center; padding: 40px 20px;">
              <div style="font-size: 40px; margin-bottom: 15px;">🚲</div>
              <h2 style="font-size: 18px; margin-bottom: 10px; color: #1e293b;">Tidak Ada Peminjaman Aktif</h2>
              <p style="font-size: 14px; color: #64748b; line-height: 1.5; margin-bottom: 20px;">Anda belum meminjam sepeda atau sudah mengembalikannya.</p>
              <a href="scan.php" class="btn btn-primary">Scan QR Sekarang</a>
          </section>
      <?php endif; ?>
    </div>
  </main>

  <nav class="tabbar">
    <div class="tabbar-in">
      <a class="tab" href="dashboard.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9.6 10 3.5l7 6.1V16a1 1 0 0 1-1 1h-3.4v-4.6H7.4V17H4a1 1 0 0 1-1-1z" /></svg></span>Dashboard</a>
      <a class="tab on" href="peminjaman.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="4.8" cy="13" r="3" /><circle cx="15.2" cy="13" r="3" /><path d="M4.8 13 8 7.2h4.2l3 5.8M8 7.2 7 5H5M12.2 7.2 10.6 13" /></svg></span>Peminjaman</a>
      <a class="tab" href="scan.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7V4.5A1.5 1.5 0 0 1 4.5 3H7M13 3h2.5A1.5 1.5 0 0 1 17 4.5V7M17 13v2.5a1.5 1.5 0 0 1-1.5 1.5H13M7 17H4.5A1.5 1.5 0 0 1 3 15.5V13M6.5 10h7" /></svg></span>Scan QR</a>
      <a class="tab" href="profil.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="10" cy="6.6" r="3.2" /><path d="M3.8 17c.9-3.1 3.2-4.6 6.2-4.6s5.3 1.5 6.2 4.6" /></svg></span>Profil</a>
    </div>
  </nav>
  <script src="../assets/js/common.js"></script>
  <script src="../assets/js/countdown.js"></script>
  <script src="../assets/js/loader.js"></script>
</body>
</html>