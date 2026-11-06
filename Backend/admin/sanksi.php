<?php
session_start();

// 1. Proteksi Halaman: Hanya Admin yang bisa masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); 
    exit;
}

// 2. PERBAIKAN PATH KONEKSI
// Menggunakan ../config/koneksi.php karena folder 'koneksi' sudah diganti menjadi 'config'
require_once '../config/koneksi.php';

// 3. Ambil Data Sanksi
try {
    $sql = "SELECT s.*, pm.nama, pm.no_identitas,
            (SELECT COUNT(*) FROM peminjaman p WHERE p.id_peminjam = pm.id_peminjam AND p.status_keterlambatan = 'Terlambat') as total_terlambat
            FROM sanksi s
            JOIN peminjam pm ON s.id_peminjam = pm.id_peminjam
            ORDER BY s.tgl_selesai DESC";
    $sanksi = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    die("Error mengambil data sanksi: " . $e->getMessage());
}

$bulan_indo = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sanksi · GOWES</title>
  <link rel="icon" href="../img/logo.png">
  
  <link rel="stylesheet" href="../assets/css/fonts.css">
  <link rel="stylesheet" href="../assets/css/tokens.css">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/components.css">
  <link rel="stylesheet" href="../assets/css/panel.css">
  <link rel="stylesheet" href="../assets/css/loader.css">
</head>
<body class="admin" data-peran="admin">
  <div class="admin-shell">
    
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <input type="checkbox" id="nav-toggle" class="nav-toggle" aria-label="Buka atau tutup menu">
      <div class="sidebar-header">
        <div>
            <a class="logo" href="dashboard.php"><img src="../img/logo-white.png" alt="GOWES"></a>
            <div class="role">Admin / RT POLINEMA</div>
        </div>
        <label class="hamburger" for="nav-toggle" aria-label="Buka menu">
            <span></span><span></span><span></span>
        </label>
      </div>
      <nav class="menu" aria-label="Menu Admin / RT POLINEMA">
        <a href="dashboard.php">Dashboard</a>
        <a href="unit.php">Unit Go-bike</a>
        <a href="peminjaman.php">Peminjaman</a>
        <a href="sanksi.php" class="on">Sanksi</a>
        <a href="laporan.php">Laporan Kondisi</a>
        <a href="riwayat.php">Riwayat Peminjaman</a>
      </nav>
      
      <!-- Logout Memanggil Modal -->
      <a class="menu-logout" href="javascript:void(0);" onclick="openLogoutModal()">Keluar</a>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main">
      <header class="topbar-a">
        <div>
          <h1>Sanksi</h1>
          <p>Teguran 1: suspen 3 hari · Teguran 2: 1 minggu · Teguran 3: permanen</p>
        </div>
        <span class="pill">Admin - RT POLINEMA</span>
      </header>

      <section class="panel">
        <div class="panel-head">
          <h2>Rekap Sanksi</h2>
        </div>
        <div class="table-wrap">
          <table class="tbl">
            <thead>
              <tr>
                <th>Nomor Identitas</th>
                <th>Nama</th>
                <th>Status Terlambat</th>
                <th>Periode Suspen</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($sanksi) > 0): ?>
                <?php foreach ($sanksi as $row): ?>
                  <?php 
                    $tgl_mulai = strtotime($row['tgl_mulai']);
                    $tgl_selesai = strtotime($row['tgl_selesai']);
                    $format_mulai = date('d', $tgl_mulai) . ' ' . $bulan_indo[(int)date('m', $tgl_mulai)];
                    $format_selesai = date('d', $tgl_selesai) . ' ' . $bulan_indo[(int)date('m', $tgl_selesai)];
                    
                    $is_aktif = ($tgl_selesai >= time());
                    $status_class = $is_aktif ? 'chip-bad' : 'chip-ok';
                    $status_text = $is_aktif ? 'Suspen Aktif' : 'Selesai';
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($row['no_identitas']) ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td>
                      <span style="font-size: 13px; margin-right: 8px;"><?= $row['total_terlambat'] ?> kali</span>
                      <span class="chip <?= $status_class ?>"><?= htmlspecialchars($row['jenis_tanggungan']) ?></span>
                    </td>
                    <td>
                      <?php if($is_aktif): ?>
                        <?= $format_mulai ?> – <?= $format_selesai ?> <?= date('Y') ?>
                      <?php else: ?>
                        Selesai (<?= $format_mulai ?> – <?= $format_selesai ?>)
                      <?php endif; ?>
                    </td>
                    <td><a class="btn-sm" href="#">Detail</a></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="5" style="text-align:center; color: var(--muted);">Belum ada data sanksi.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <!-- ========================================== -->
  <!-- MODAL POP-UP LOGOUT                        -->
  <!-- ========================================== -->
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
          <p>Apakah Anda yakin ingin keluar dari akun ini? Anda harus login kembali untuk mengakses halaman admin.</p>
          <div class="modal-actions">
              <button type="button" class="btn-cancel" onclick="closeLogoutModal()">Batal</button>
              <a href="../auth/login.php?logout=true" class="btn-confirm">Ya, Keluar</a>
          </div>
      </div>
  </div>

  <!-- JAVASCRIPT EKSTERNAL -->
  <script src="../assets/js/common.js"></script>
  <script src="../assets/js/logout-modal.js"></script>
  <script src="../assets/js/loader.js"></script>
</body>
</html>