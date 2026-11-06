<?php
session_start();
if (!isset($_SESSION['id_peminjam'])) { header("Location: ../auth/login.php"); exit; }
require_once '../config/koneksi.php';

$id_peminjam = $_SESSION['id_peminjam'];
$nama_user   = $_SESSION['nama'];
$inisial     = strtoupper(substr($nama_user, 0, 1));

$stmtDelay = $pdo->prepare("SELECT COUNT(*) as total FROM peminjaman WHERE id_peminjam = :id AND status_keterlambatan = 'Terlambat'");
$stmtDelay->execute(['id' => $id_peminjam]);
$jml_terlambat = $stmtDelay->fetch()['total'];

$stmtSanksi = $pdo->prepare("SELECT * FROM sanksi WHERE id_peminjam = :id ORDER BY tgl_selesai DESC LIMIT 1");
$stmtSanksi->execute(['id' => $id_peminjam]);
$sanksi_terakhir = $stmtSanksi->fetch();

$is_suspen = false; $periode_suspen = "Tidak ada"; $teks_suspen_terakhir = "";
$bulan_indo = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];

if ($sanksi_terakhir) {
    $tgl_mulai_ts   = strtotime($sanksi_terakhir['tgl_mulai']);
    $tgl_selesai_ts = strtotime($sanksi_terakhir['tgl_selesai']);
    $hari_ini_ts    = strtotime(date('Y-m-d'));
    $fmt_mulai = date('d', $tgl_mulai_ts) . ' ' . $bulan_indo[(int)date('m', $tgl_mulai_ts)] . ' ' . date('Y', $tgl_mulai_ts);
    $fmt_selesai = date('d', $tgl_selesai_ts) . ' ' . $bulan_indo[(int)date('m', $tgl_selesai_ts)] . ' ' . date('Y', $tgl_selesai_ts);
    
    if ($tgl_selesai_ts >= $hari_ini_ts) {
        $is_suspen = true; $periode_suspen = "$fmt_mulai - $fmt_selesai";
    } else {
        $teks_suspen_terakhir = "Suspen terakhir: $fmt_mulai - $fmt_selesai (selesai).";
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Status Sanksi · GOWES</title>
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
    <div class="col sanksi-container">
      <a class="back d-only" href="profil.php" style="margin-bottom: 10px;">‹ Kembali ke Profil</a>
      <h1 class="title" style="margin-bottom: 20px;"><span class="m">Status sanksi</span><span class="d">Status Sanksi</span></h1>

      <section class="status-card">
        <div class="row">
          <span class="k">Status akun</span>
          <?php if ($is_suspen): ?><span class="chip chip-bad">Suspen</span><?php else: ?><span class="chip chip-ok">Aktif</span><?php endif; ?>
        </div>
        <div class="row"><span class="k">Jumlah keterlambatan</span><span class="v"><?= $jml_terlambat ?> kali</span></div>
        <div class="row">
          <span class="k">Status suspen</span>
          <?php if ($is_suspen): ?><span class="chip chip-bad">Sedang Suspen</span><?php else: ?><span class="chip chip-ok">Tidak suspen</span><?php endif; ?>
        </div>
        <div class="row"><span class="k">Periode suspen</span><span class="v"><?= $periode_suspen ?></span></div>
      </section>

      <h2 class="sec-title"><span class="m">Aturan sanksi keterlambatan</span><span class="d">Aturan Sanksi Keterlambatan</span></h2>
      
      <div class="rules">
        <div class="rule <?= ($jml_terlambat == 1) ? 'on' : '' ?>"><span class="k"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>Teguran 1</span><span class="v">Suspen 3 hari</span></div>
        <div class="rule <?= ($jml_terlambat == 2) ? 'on' : '' ?>"><span class="k"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>Teguran 2</span><span class="v">Suspen 1 minggu</span></div>
        <div class="rule <?= ($jml_terlambat >= 3) ? 'on' : '' ?>"><span class="k"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>Teguran 3</span><span class="v">Suspen permanen</span></div>
      </div>

      <?php if ($is_suspen): ?>
          <div class="note-alert note-danger">Anda sedang dalam masa suspen. Selesaikan masa suspen untuk dapat meminjam kembali.</div>
      <?php elseif (!empty($teks_suspen_terakhir)): ?>
          <div class="note-alert note-success"><?= $teks_suspen_terakhir ?></div>
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
  <script src="../assets/js/loader.js"></script>
</body>
</html>