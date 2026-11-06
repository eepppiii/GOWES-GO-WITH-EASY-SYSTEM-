<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
    header("Location: ../auth/login.php"); 
    exit;
}
require_once '../config/koneksi.php';

$unit_tersedia = $pdo->query("SELECT COUNT(*) FROM sepeda WHERE status_ketersediaan = 'Tersedia'")->fetchColumn();
$sedang_dipinjam = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE waktu_kembali IS NULL")->fetchColumn();
$terlambat = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE waktu_kembali IS NULL AND batas_kembali < CURRENT_TIMESTAMP")->fetchColumn();
$suspen_aktif = $pdo->query("SELECT COUNT(*) FROM sanksi WHERE tgl_selesai >= CURRENT_DATE")->fetchColumn();

$sql_pinjam = "SELECT p.id_peminjaman, pm.no_identitas, s.kode_qr, sh.nama_shelter, p.batas_kembali, p.status_keterlambatan
               FROM peminjaman p JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam JOIN sepeda s ON p.id_sepeda = s.id_sepeda JOIN shelter sh ON s.id_shelter = sh.id_shelter
               WHERE p.waktu_kembali IS NULL ORDER BY p.waktu_mulai DESC LIMIT 5";
$peminjaman_aktif = $pdo->query($sql_pinjam)->fetchAll();

$sql_shelter = "SELECT sh.nama_shelter, sh.lokasi, COUNT(s.id_sepeda) as total, SUM(CASE WHEN s.status_ketersediaan = 'Tersedia' THEN 1 ELSE 0 END) as tersedia
                FROM shelter sh LEFT JOIN sepeda s ON sh.id_shelter = s.id_shelter GROUP BY sh.id_shelter";
$shelters = $pdo->query($sql_shelter)->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Petugas · GOWES</title>
  <link rel="icon" href="../img/logo.png">
  <link rel="stylesheet" href="../assets/css/fonts.css"><link rel="stylesheet" href="../assets/css/tokens.css">
  <link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/components.css">
  <link rel="stylesheet" href="../assets/css/panel.css"><link rel="stylesheet" href="../assets/css/loader.css">
</head>
<body class="admin" data-peran="petugas">
  <div class="admin-shell">
    <aside class="sidebar">
      <input type="checkbox" id="nav-toggle" class="nav-toggle">
      <div class="sidebar-header">
        <div><a class="logo" href="dashboard.php"><img src="../img/logo-white.png" alt="GOWES"></a><div class="role">Petugas / Satpam</div></div>
        <label class="hamburger" for="nav-toggle"><span></span><span></span><span></span></label>
      </div>
      <nav class="menu">
        <a href="dashboard.php" class="on">Dashboard</a>
        <a href="unit.php">Unit Go-bike</a>
        <a href="peminjaman.php">Peminjaman</a>
        <a href="laporan.php">Laporan Kondisi</a>
        <a href="riwayat.php">Riwayat Peminjaman</a>
      </nav>
      <a class="menu-logout" href="javascript:void(0);" onclick="openLogoutModal()">Keluar</a>
    </aside>

    <main class="main">
      <header class="topbar-a">
        <div><h1>Dashboard Petugas</h1><p>Senin–Jumat, 08.00–15.30 WIB · Login sebagai: <b><?= htmlspecialchars($_SESSION['nama']) ?></b></p></div>
        <span class="pill">Petugas - Satpam</span>
      </header>
      <section class="stats">
        <div class="stat"><div class="k">Unit tersedia</div><div class="v" style="color:var(--primary)"><?= $unit_tersedia ?></div></div>
        <div class="stat"><div class="k">Sedang dipinjam</div><div class="v" style="color:var(--ink)"><?= $sedang_dipinjam ?></div></div>
        <div class="stat"><div class="k">Terlambat</div><div class="v" style="color:var(--orange)"><?= $terlambat ?></div></div>
        <div class="stat"><div class="k">Suspen aktif</div><div class="v" style="color:var(--red)"><?= $suspen_aktif ?></div></div>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Peminjaman aktif</h2><a class="link" href="peminjaman.php">Lihat semua</a></div>
        <div class="table-wrap">
          <table class="tbl">
            <thead><tr><th>Nomor Identitas</th><th>Unit</th><th>Shelter</th><th>Batas kembali</th><th>Status</th></tr></thead>
            <tbody>
              <?php if (count($peminjaman_aktif) > 0): ?>
                  <?php foreach ($peminjaman_aktif as $row): ?>
                      <?php
                          $jam_batas = date('H.i', strtotime($row['batas_kembali'])) . ' WIB';
                          $status = $row['status_keterlambatan'];
                          $chip_class = 'chip-ok'; 
                          if ($status == 'Terlambat') $chip_class = 'chip-bad';
                          else if (strtotime($row['batas_kembali']) - time() < 1800) $chip_class = 'chip-warn';
                      ?>
                      <tr><td><?= htmlspecialchars($row['no_identitas']) ?></td><td><?= htmlspecialchars($row['kode_qr']) ?></td><td><?= htmlspecialchars($row['nama_shelter']) ?></td><td><?= $jam_batas ?></td><td><span class="chip <?= $chip_class ?>"><?= $status ?></span></td></tr>
                  <?php endforeach; ?>
              <?php else: ?>
                  <tr><td colspan="5" style="text-align: center; color: var(--muted);">Tidak ada peminjaman aktif saat ini.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

      <section class="grid-2">
        <?php foreach ($shelters as $sh): ?>
            <div class="shelter-card"><h3><?= htmlspecialchars($sh['nama_shelter']) ?></h3><p><?= htmlspecialchars($sh['lokasi']) ?></p><b><?= $sh['tersedia'] ?> dari <?= $sh['total'] ?> unit tersedia</b></div>
        <?php endforeach; ?>
      </section>
    </main>
  </div>

  <div class="modal-overlay" id="logoutModal">
      <div class="modal-content">
          <div class="modal-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg></div>
          <h2>Konfirmasi Keluar</h2><p>Apakah Anda yakin ingin keluar dari akun ini? Anda harus login kembali untuk mengakses halaman petugas.</p>
          <div class="modal-actions">
              <button type="button" class="btn-cancel" onclick="closeLogoutModal()">Batal</button>
              <a href="../auth/login.php?logout=true" class="btn-confirm">Ya, Keluar</a>
          </div>
      </div>
  </div>

  <script src="../assets/js/common.js"></script>
  <script src="../assets/js/tabel.js"></script>
  <script src="../assets/js/logout-modal.js"></script>
  <script src="../assets/js/loader.js"></script>
</body>
</html>