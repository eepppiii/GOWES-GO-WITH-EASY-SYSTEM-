<?php
session_start();
require_once '../config/koneksi.php'; 

$is_logged_in = isset($_SESSION['id_peminjam']);
if ($is_logged_in) {
    $nama_user = $_SESSION['nama'];
    $inisial   = strtoupper(substr($nama_user, 0, 1));
    $href_peminjaman = "peminjaman.php";
    $href_scan       = "scan.php";
    $href_profil     = "profil.php";
    $link_user_area  = "profil.php";
    $disabled_style = ""; $disabled_title = "";
} else {
    $nama_user = "Login"; $inisial = "?";
    $href_peminjaman = "#"; $href_scan = "#"; $href_profil = "#";
    $link_user_area  = "../auth/login.php"; 
    $disabled_style = "style='opacity: 0.4; pointer-events: none; cursor: not-allowed;'";
    $disabled_title = "title='Anda harus login terlebih dahulu'";
}

try {
    $sql = "SELECT s.nama_shelter, s.lokasi, COUNT(sp.id_sepeda) AS total_unit,
                SUM(CASE WHEN sp.status_ketersediaan = 'Tersedia' THEN 1 ELSE 0 END) AS unit_tersedia
            FROM shelter s LEFT JOIN sepeda sp ON s.id_shelter = sp.id_shelter
            GROUP BY s.id_shelter, s.nama_shelter, s.lokasi";
    $shelters = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) { die("Error: " . $e->getMessage()); }
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard · GOWES</title>
  <link rel="icon" href="../img/logo.png">
  <link rel="stylesheet" href="../assets/css/fonts.css"><link rel="stylesheet" href="../assets/css/tokens.css">
  <link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/components.css">
  <link rel="stylesheet" href="../assets/css/mahasiswa.css"><link rel="stylesheet" href="../assets/css/loader.css">
</head>
<body class="user ">
  <header class="topbar">
    <a class="brand" href="dashboard.php"><img src="../img/logo.png" alt="GOWES"></a>
    <nav class="topnav">
      <a class="navitem on" href="dashboard.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9.6 10 3.5l7 6.1V16a1 1 0 0 1-1 1h-3.4v-4.6H7.4V17H4a1 1 0 0 1-1-1z" /></svg></span>Dashboard</a>
      <a class="navitem" href="<?= $href_peminjaman ?>" <?= $disabled_style ?> <?= $disabled_title ?>><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="4.8" cy="13" r="3" /><circle cx="15.2" cy="13" r="3" /><path d="M4.8 13 8 7.2h4.2l3 5.8M8 7.2 7 5H5M12.2 7.2 10.6 13" /></svg></span>Peminjaman</a>
      <a class="navitem" href="<?= $href_scan ?>" <?= $disabled_style ?> <?= $disabled_title ?>><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7V4.5A1.5 1.5 0 0 1 4.5 3H7M13 3h2.5A1.5 1.5 0 0 1 17 4.5V7M17 13v2.5a1.5 1.5 0 0 1-1.5 1.5H13M7 17H4.5A1.5 1.5 0 0 1 3 15.5V13M6.5 10h7" /></svg></span>Scan QR</a>
      <a class="navitem" href="<?= $href_profil ?>" <?= $disabled_style ?> <?= $disabled_title ?>><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="10" cy="6.6" r="3.2" /><path d="M3.8 17c.9-3.1 3.2-4.6 6.2-4.6s5.3 1.5 6.2 4.6" /></svg></span>Profil</a>
    </nav>
    <?php if ($is_logged_in): ?>
        <a href="<?= $link_user_area ?>" class="user-pill-btn logged-in" title="Buka Profil"><span><?= htmlspecialchars($nama_user) ?></span><div class="avatar-sm"><?= $inisial ?></div></a>
    <?php else: ?>
        <a href="<?= $link_user_area ?>" class="user-pill-btn guest" title="Klik untuk Masuk"><span>Login</span><div class="avatar-sm">?</div></a>
    <?php endif; ?>
  </header>

  <main class="page ">
    <div class="col wide">
      <p class="greet"><span class="m">Halo, <?= htmlspecialchars($nama_user) ?>!</span><span class="d">Halo, <?= htmlspecialchars($nama_user) ?>!</span></p>
      <h1 class="title">Mau <img class="logo-inline" src="../img/logo.png" alt="GOWES"> kemana?</h1>
      <div class="dash-grid">
        <div class="dash-left">
          <section class="hero">
            <h2>Go-bike Kampus POLINEMA</h2>
            <p>Sepeda listrik untuk mobilitas mahasiswa di area kampus. Pinjam mandiri lewat QR Code, tanpa titip KTM.</p>
            <a class="btn btn-white" href="<?= $href_scan ?>" <?= $disabled_style ?>><span class="m">Scan QR sekarang</span><span class="d">Scan QR Sekarang!</span></a>
          </section>
          <section class="card cara">
            <h2 class="sec-title"><span class="m">Cara pakai Go-bike</span><span class="d">Cara Pakai Go-bike</span></h2>
            <div class="steps">
              <div class="step"><span class="n">1</span><span>Scan QR Code di slot Go-bike.</span></div>
              <div class="step"><span class="n">2</span><span>Pilih durasi 1, 2, atau 3 jam lalu konfirmasi.</span></div>
              <div class="step"><span class="n">3</span><span>Tunjukkan bukti peminjaman ke petugas, ambil kunci.</span></div>
              <div class="step"><span class="n">4</span><span>Kembalikan Go-bike, tekan tombol pengembalian, isi laporan kondisi.</span></div>
            </div>
          </section>
        </div>
        <div class="dash-right">
          <section class="card jam">
            <div class="card-head"><span class="card-title"><span class="m">Jam operasional</span><span class="d">Jam Operasional</span></span><span class="chip chip-ok">Buka</span></div>
            <p class="card-note big"><span class="m">Senin–Jumat, 08.00–15.30 WIB</span><span class="d">Senin–Jumat, pukul 08.00–15.30 WIB</span></p>
          </section>
          <section class="card lokasi">
            <h2 class="sec-title">Lokasi Shelter dan Ketersediaan</h2>
            <?php if (count($shelters) > 0): ?>
                <?php foreach ($shelters as $shelter): ?>
                    <div class="shelter">
                      <div class="card-head">
                        <span class="card-title lg"><?= htmlspecialchars($shelter['nama_shelter']) ?></span>
                        <?php if ($shelter['unit_tersedia'] > 0): ?><span class="chip chip-ok">Tersedia</span>
                        <?php else: ?><span class="chip" style="background:#ffebee; color:#c62828;">Habis</span><?php endif; ?>
                      </div>
                      <p class="card-note"><?= htmlspecialchars($shelter['lokasi']) ?></p>
                      <span class="card-link"><?= $shelter['unit_tersedia'] ?> dari <?= $shelter['total_unit'] ?> unit tersedia</span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?><p class="card-note">Belum ada data shelter.</p><?php endif; ?>
          </section>
        </div>
      </div>
    </div>
  </main>

  <nav class="tabbar">
    <div class="tabbar-in">
      <a class="tab on" href="dashboard.php"><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9.6 10 3.5l7 6.1V16a1 1 0 0 1-1 1h-3.4v-4.6H7.4V17H4a1 1 0 0 1-1-1z" /></svg></span>Dashboard</a>
      <a class="tab" href="<?= $href_peminjaman ?>" <?= $disabled_style ?>><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="4.8" cy="13" r="3" /><circle cx="15.2" cy="13" r="3" /><path d="M4.8 13 8 7.2h4.2l3 5.8M8 7.2 7 5H5M12.2 7.2 10.6 13" /></svg></span>Peminjaman</a>
      <a class="tab" href="<?= $href_scan ?>" <?= $disabled_style ?>><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7V4.5A1.5 1.5 0 0 1 4.5 3H7M13 3h2.5A1.5 1.5 0 0 1 17 4.5V7M17 13v2.5a1.5 1.5 0 0 1-1.5 1.5H13M7 17H4.5A1.5 1.5 0 0 1 3 15.5V13M6.5 10h7" /></svg></span>Scan QR</a>
      <a class="tab" href="<?= $href_profil ?>" <?= $disabled_style ?>><span class="ico"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="10" cy="6.6" r="3.2" /><path d="M3.8 17c.9-3.1 3.2-4.6 6.2-4.6s5.3 1.5 6.2 4.6" /></svg></span>Profil</a>
    </div>
  </nav>
  <script src="../assets/js/loader.js"></script>
</body>
</html>