<?php
session_start();

// ==========================================
// 1. KONEKSI DATABASE & PROTEKSI HALAMAN
// ==========================================
// Sesuaikan path koneksi ini dengan struktur folder Anda
require_once '../config/koneksi.php'; 

// Cek apakah user sudah login sebagai superadmin
// (Asumsi: sistem login menyimpan role 'superadmin' di session)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit;
}

$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Super Admin';

// ==========================================
// 2. LOGIKA PENGAMBILAN DATA STATISTIK
// ==========================================
try {
    // Unit tersedia
    $stmt = $pdo->query("SELECT COUNT(*) FROM sepeda WHERE status_ketersediaan = 'Tersedia'");
    $unit_tersedia = $stmt->fetchColumn();

    // Unit tidak tersedia (Dipinjam / Perawatan)
    $stmt = $pdo->query("SELECT COUNT(*) FROM sepeda WHERE status_ketersediaan != 'Tersedia'");
    $unit_tidak_tersedia = $stmt->fetchColumn();

    // Peminjaman terlambat aktif (Waktu sekarang > batas_kembali dan belum dikembalikan)
    $stmt = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE waktu_kembali IS NULL AND CURRENT_TIMESTAMP > batas_kembali");
    $peminjaman_terlambat = $stmt->fetchColumn();

    // User suspen aktif (Tanggal sekarang di antara tgl_mulai dan tgl_selesai)
    $stmt = $pdo->query("SELECT COUNT(*) FROM sanksi WHERE CURRENT_DATE BETWEEN tgl_mulai AND tgl_selesai");
    $user_suspen = $stmt->fetchColumn();

    // Petugas aktif
    $stmt = $pdo->query("SELECT COUNT(*) FROM petugas");
    $petugas_aktif = $stmt->fetchColumn();

    // Admin aktif
    $stmt = $pdo->query("SELECT COUNT(*) FROM admin");
    $admin_aktif = $stmt->fetchColumn();

    // ==========================================
    // 3. LOGIKA PENGAMBILAN DATA SHELTER
    // ==========================================
    $sql_shelter = "SELECT 
                        s.nama_shelter, 
                        s.lokasi, 
                        COUNT(sp.id_sepeda) AS total_unit,
                        SUM(CASE WHEN sp.status_ketersediaan = 'Tersedia' THEN 1 ELSE 0 END) AS unit_tersedia
                    FROM shelter s
                    LEFT JOIN sepeda sp ON s.id_shelter = sp.id_shelter
                    GROUP BY s.id_shelter, s.nama_shelter, s.lokasi";
    $stmt = $pdo->query($sql_shelter);
    $shelters = $stmt->fetchAll();

    // ==========================================
    // 4. LOGIKA PENGAMBILAN DATA PEMINJAMAN AKTIF
    // ==========================================
    $sql_peminjaman = "SELECT 
                            p.no_identitas, 
                            sp.kode_qr, 
                            s.nama_shelter, 
                            pm.batas_kembali 
                       FROM peminjaman pm
                       JOIN peminjam p ON pm.id_peminjam = p.id_peminjam
                       JOIN sepeda sp ON pm.id_sepeda = sp.id_sepeda
                       JOIN shelter s ON sp.id_shelter = s.id_shelter
                       WHERE pm.waktu_kembali IS NULL
                       ORDER BY pm.batas_kembali ASC
                       LIMIT 5";
    $stmt = $pdo->query($sql_peminjaman);
    $peminjaman_aktif = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
?>

<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Super Admin · GOWES</title>
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
        <a href="dashboard.php" class="on">Dashboard</a>
        <a href="unit.php" class="">Unit Go-bike</a>
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
          <h1>Dashboard Super Admin</h1>
          <p>Senin–Jumat, 08.00–15.30 WIB</p>
        </div>
        <span class="pill"><?= htmlspecialchars($nama_admin) ?></span>
      </header>

      <section class="stats">
        <div class="stat">
          <div class="k">Unit sepeda tersedia</div>
          <div class="v" style="color:var(--primary)"><?= $unit_tersedia ?></div>
        </div>
        <div class="stat">
          <div class="k">Unit sepeda tidak tersedia</div>
          <div class="v" style="color:var(--ink)"><?= $unit_tidak_tersedia ?></div>
        </div>
        <div class="stat">
          <div class="k">Pengembalian Terlambat</div>
          <div class="v" style="color:var(--orange)"><?= $peminjaman_terlambat ?></div>
        </div>
        <div class="stat">
          <div class="k">User suspen aktif</div>
          <div class="v" style="color:var(--red)"><?= $user_suspen ?></div>
        </div>
        <div class="stat">
          <div class="k">Petugas Aktif</div>
          <div class="v" style="color:var(--primary)"><?= $petugas_aktif ?></div>
        </div>
        <div class="stat">
          <div class="k">Admin Aktif</div>
          <div class="v" style="color:var(--primary)"><?= $admin_aktif ?></div>
        </div>
      </section>

      <section class="grid-2">
        <?php if(count($shelters) > 0): ?>
            <?php foreach($shelters as $shelter): ?>
                <div class="shelter-card">
                  <h3><?= htmlspecialchars($shelter['nama_shelter']) ?></h3>
                  <p><?= htmlspecialchars($shelter['lokasi']) ?></p>
                  <b><?= $shelter['unit_tersedia'] ?> dari <?= $shelter['total_unit'] ?> unit tersedia</b>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="shelter-card"><p>Belum ada data shelter.</p></div>
        <?php endif; ?>
      </section>

      <section class="panel">
        <div class="panel-head">
          <h2>Peminjaman Aktif</h2>
          <a class="link" href="peminjaman.php">Lihat semua</a>
        </div>
        <div class="table-wrap">
          <table class="tbl">
            <thead>
              <tr>
                <th>Nomor Identitas</th>
                <th>Unit</th>
                <th>Shelter</th>
                <th>Batas Kembali</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if(count($peminjaman_aktif) > 0): ?>
                  <?php foreach($peminjaman_aktif as $row): ?>
                      <tr>
                        <td><?= htmlspecialchars($row['no_identitas']) ?></td>
                        <td><?= htmlspecialchars($row['kode_qr']) ?></td>
                        <td><?= htmlspecialchars($row['nama_shelter']) ?></td>
                        <td><?= date('H:i', strtotime($row['batas_kembali'])) ?> WIB</td>
                        
                        <!-- Logika Penentuan Status Berdasarkan Waktu Saat Ini -->
                        <?php 
                            $sekarang = new DateTime();
                            $batas = new DateTime($row['batas_kembali']);
                            $selisih = $sekarang->diff($batas);
                            
                            if ($sekarang > $batas) {
                                echo '<td><span class="chip chip-bad">Terlambat</span></td>';
                            } elseif ($selisih->h == 0 && $selisih->i <= 30 && $sekarang < $batas) {
                                // Jika sisa waktu <= 30 menit
                                echo '<td><span class="chip chip-warn">Mendekati batas</span></td>';
                            } else {
                                echo '<td><span class="chip chip-ok">Aktif</span></td>';
                            }
                        ?>
                      </tr>
                  <?php endforeach; ?>
              <?php else: ?>
                  <tr>
                      <td colspan="5" style="text-align: center;">Tidak ada peminjaman aktif saat ini.</td>
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