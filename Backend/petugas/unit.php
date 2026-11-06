<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
    header("Location: ../auth/login.php"); 
    exit;
}
require_once '../config/koneksi.php';

$sql = "SELECT s.*, sh.nama_shelter,
        (SELECT keterangan FROM laporan_kondisi lk JOIN peminjaman p ON lk.id_peminjaman = p.id_peminjaman WHERE p.id_sepeda = s.id_sepeda ORDER BY lk.tgl_lapor DESC LIMIT 1) as kondisi_terakhir
        FROM sepeda s JOIN shelter sh ON s.id_shelter = sh.id_shelter ORDER BY s.kode_qr ASC";
$units = $pdo->query($sql)->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Unit Go-bike · GOWES</title>
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
        <a href="dashboard.php">Dashboard</a>
        <a href="unit.php" class="on">Unit Go-bike</a>
        <a href="peminjaman.php">Peminjaman</a>
        <a href="laporan.php">Laporan Kondisi</a>
        <a href="riwayat.php">Riwayat Peminjaman</a>
      </nav>
      <a class="menu-logout" href="javascript:void(0);" onclick="openLogoutModal()">Keluar</a>
    </aside>

    <main class="main">
      <header class="topbar-a">
        <div><h1>Unit Go-bike</h1><p>Kelola data unit dan status ketersediaan</p></div>
        <span class="pill">Petugas - Satpam</span>
      </header>
      <section class="panel">
        <div class="panel-head"><h2>Daftar Unit</h2></div>
        <div class="table-wrap">
          <table class="tbl">
            <thead><tr><th>ID Go-bike</th><th>Shelter</th><th>Status</th><th>Kondisi terakhir</th></tr></thead>
            <tbody>
              <?php if (count($units) > 0): ?>
                <?php foreach ($units as $row): ?>
                  <?php 
                    $status_class = ($row['status_ketersediaan'] === 'Dipinjam') ? 'chip-info' : (($row['status_ketersediaan'] === 'Perawatan') ? 'chip-warn' : 'chip-ok');
                  ?>
                  <tr>
                    <td><strong><?= htmlspecialchars($row['kode_qr']) ?></strong></td>
                    <td><?= htmlspecialchars($row['nama_shelter']) ?></td>
                    <td><span class="chip <?= $status_class ?>"><?= htmlspecialchars($row['status_ketersediaan']) ?></span></td>
                    <td><?= !empty($row['kondisi_terakhir']) ? htmlspecialchars($row['kondisi_terakhir']) : 'Baik' ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="4" style="text-align:center;">Belum ada data unit sepeda.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <div class="modal-overlay" id="logoutModal">
      <div class="modal-content">
          <div class="modal-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg></div>
          <h2>Konfirmasi Keluar</h2><p>Apakah Anda yakin ingin keluar dari akun ini? Anda harus login kembali untuk mengakses halaman petugas.</p>
          <div class="modal-actions"><button type="button" class="btn-cancel" onclick="closeLogoutModal()">Batal</button><a href="../auth/login.php?logout=true" class="btn-confirm">Ya, Keluar</a></div>
      </div>
  </div>
  <script src="../assets/js/common.js"></script>
  <script src="../assets/js/logout-modal.js"></script>
  <script src="../assets/js/loader.js"></script>
</body>
</html>