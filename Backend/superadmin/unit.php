<?php
session_start();

// ==========================================
// 1. KONEKSI DATABASE & PROTEKSI HALAMAN
// ==========================================
require_once '../config/koneksi.php'; 

// Cek apakah user sudah login sebagai superadmin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit;
}

$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Super Admin';

// ==========================================
// 2. AMBIL DATA UNIT SEPEDA
// ==========================================
try {
    // Query mengambil data sepeda dan join ke shelter
    $sql = "SELECT 
                sp.kode_qr, 
                sh.nama_shelter, 
                sp.status_ketersediaan, 
                sp.catatan_perawatan
            FROM sepeda sp
            JOIN shelter sh ON sp.id_shelter = sh.id_shelter
            ORDER BY sp.kode_qr ASC";
            
    $stmt = $pdo->query($sql);
    $units = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
?>

<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Unit Go-bike · GOWES</title>
  <link rel="icon" href="../img/logo.png">
  <link rel="stylesheet" href="../css/fonts.css">
  <link rel="stylesheet" href="../css/tokens.css">
  <link rel="stylesheet" href="../css/base.css">
  <link rel="stylesheet" href="../css/components.css">
  <link rel="stylesheet" href="../css/panel.css">
</head>

<body class="admin" data-peran="superadmin">

  <div class="admin-shell">

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar">
      <input type="checkbox" id="nav-toggle" class="nav-toggle" aria-label="Buka atau tutup menu">

      <div>
        <a class="logo" href="dashboard.php"><img src="../img/logo-white.png" alt="GOWES"></a>
        <div class="role">Super Admin</div>
      </div>

      <label class="hamburger" for="nav-toggle" aria-label="Buka menu"><span></span><span></span><span></span></label>

      <nav class="menu" aria-label="Menu Super Admin">
        <a href="dashboard.php" class="">Dashboard</a>
        <a href="unit.php" class="on">Unit Go-bike</a>
        <a href="peminjaman.php" class="">Peminjaman</a>
        <a href="sanksi.php" class="">Sanksi</a>
        <a href="laporan.php" class="">Laporan Kondisi</a>
        <a href="riwayat.php" class="">Riwayat Peminjaman</a>
        <a href="tambah-petugas-admin.php" class="">Tambah Petugas/Admin</a>
      </nav>

      <a class="menu-logout" href="../auth/login.php?logout=true">Keluar</a>
    </aside>

    <!-- ===== KONTEN UTAMA ===== -->
    <main class="main">
      <header class="topbar-a">
        <div>
          <h1>Unit Go-bike</h1>
          <p>Kelola data unit dan status ketersediaan</p>
        </div>
        <span class="pill"><?= htmlspecialchars($nama_admin) ?></span>
      </header>

      <section class="panel">
        <div class="panel-head">
          <h2>Daftar Unit</h2>
        </div>
        <div class="table-wrap">
          <table class="tbl">
            <thead>
              <tr>
                <th>ID Go-bike</th>
                <th>Shelter</th>
                <th>Status</th>
                <th>Kondisi terakhir</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($units) > 0): ?>
                  <?php foreach ($units as $unit): ?>
                      <tr>
                        <td><?= htmlspecialchars($unit['kode_qr']) ?></td>
                        <td><?= htmlspecialchars($unit['nama_shelter']) ?></td>
                        
                        <!-- Logika Penentuan Warna Status -->
                        <?php 
                            $status = $unit['status_ketersediaan'];
                            $chip_class = 'chip-ok'; // Default Tersedia
                            
                            if ($status === 'Dipinjam') {
                                $chip_class = 'chip-info';
                            } elseif ($status === 'Perawatan' || $status === 'Rusak') {
                                $chip_class = 'chip-warn';
                            }
                        ?>
                        <td><span class="chip <?= $chip_class ?>"><?= htmlspecialchars($status) ?></span></td>
                        
                        <!-- Menampilkan catatan perawatan, jika kosong tampilkan 'Baik' -->
                        <td><?= !empty($unit['catatan_perawatan']) ? htmlspecialchars($unit['catatan_perawatan']) : 'Baik' ?></td>
                      </tr>
                  <?php endforeach; ?>
              <?php else: ?>
                  <tr>
                      <td colspan="4" style="text-align: center; padding: 20px; color: var(--muted);">
                          Belum ada data unit sepeda.
                      </td>
                  </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

    </main>
  </div>

  <script src="../js/common.js"></script>
  <script src="../js/tabel.js"></script>
  <script src="../js/modal.js"></script>
</body>

</html>