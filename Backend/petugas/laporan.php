<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
    header("Location: ../auth/login.php"); 
    exit;
}
require_once '../config/koneksi.php';

$sql = "SELECT lk.*, s.kode_qr, pm.no_identitas FROM laporan_kondisi lk JOIN peminjaman p ON lk.id_peminjaman = p.id_peminjaman JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam JOIN sepeda s ON p.id_sepeda = s.id_sepeda ORDER BY lk.tgl_lapor DESC";
$laporan = $pdo->query($sql)->fetchAll();
$bulan_indo = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laporan kondisi · GOWES</title>
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
        <a href="unit.php">Unit Go-bike</a>
        <a href="peminjaman.php">Peminjaman</a>
        <a href="laporan.php" class="on">Laporan Kondisi</a>
        <a href="riwayat.php">Riwayat Peminjaman</a>
      </nav>
      <a class="menu-logout" href="javascript:void(0);" onclick="openLogoutModal()">Keluar</a>
    </aside>

    <main class="main">
      <header class="topbar-a">
        <div><h1>Laporan Kondisi</h1><p>Laporan yang dikirim mahasiswa setelah pengembalian</p></div>
        <span class="pill">Petugas - Satpam</span>
      </header>

      <section class="panel">
        <div class="panel-head"><h2>Laporan Terbaru</h2></div>
        <div class="table-wrap">
          <table class="tbl">
            <thead><tr><th>Tanggal</th><th>Unit</th><th>Nomor Identitas</th><th>Kondisi</th><th>Keterangan</th></tr></thead>
            <tbody>
              <?php if (count($laporan) > 0): ?>
                <?php foreach ($laporan as $row): ?>
                  <?php 
                    $ts = strtotime($row['tgl_lapor']);
                    $tgl = date('d', $ts) . ' ' . $bulan_indo[(int)date('m', $ts)] . ' ' . date('Y', $ts);
                    $rata = ($row['nilai_ban'] + $row['nilai_rem'] + $row['nilai_rantai'] + $row['nilai_rangka_bodi']) / 4;
                    
                    if ($rata >= 4) { $chip_class = 'chip-ok'; $status_text = 'Baik'; }
                    elseif ($rata >= 2.5) { $chip_class = 'chip-warn'; $status_text = 'Perlu perawatan'; }
                    else { $chip_class = 'chip-bad'; $status_text = 'Rusak'; }
                  ?>
                  <tr>
                    <td><?= $tgl ?></td>
                    <td><strong><?= htmlspecialchars($row['kode_qr']) ?></strong></td>
                    <td><?= htmlspecialchars($row['no_identitas']) ?></td>
                    <td><span class="chip <?= $chip_class ?>"><?= $status_text ?></span></td>
                    <td><?= !empty($row['keterangan']) ? htmlspecialchars($row['keterangan']) : '-' ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="5" style="text-align:center; color: var(--muted); padding: 30px;">Belum ada laporan kondisi.</td></tr>
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