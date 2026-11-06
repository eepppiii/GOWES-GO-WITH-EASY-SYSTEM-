<?php
session_start();

// 1. Proteksi Halaman: Hanya Petugas yang bisa masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
    header("Location: ../auth/login.php"); 
    exit;
}

// 2. PERBAIKAN PATH KONEKSI
require_once '../config/koneksi.php';

// 3. Logika Filter Pencarian & Tanggal
$search = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$tgl_filter = isset($_GET['tanggal']) ? trim($_GET['tanggal']) : '';
$where_clauses = []; 
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(pm.no_identitas ILIKE :search OR s.kode_qr ILIKE :search)";
    $params['search'] = "%$search%";
}
if (!empty($tgl_filter)) {
    $where_clauses[] = "DATE(p.waktu_mulai) = :tanggal";
    $params['tanggal'] = $tgl_filter;
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// 4. Ambil Data Riwayat
try {
    $sql = "SELECT p.*, pm.no_identitas, s.kode_qr 
            FROM peminjaman p 
            JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam 
            JOIN sepeda s ON p.id_sepeda = s.id_sepeda 
            $where_sql 
            ORDER BY p.waktu_mulai DESC";
    $stmt = $pdo->prepare($sql); 
    $stmt->execute($params); 
    $riwayat = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error mengambil data riwayat: " . $e->getMessage());
}

$bulan_indo = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Riwayat Peminjaman · GOWES</title>
  <link rel="icon" href="../img/logo.png">
  
  <link rel="stylesheet" href="../assets/css/fonts.css">
  <link rel="stylesheet" href="../assets/css/tokens.css">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/components.css">
  <link rel="stylesheet" href="../assets/css/panel.css">
  <link rel="stylesheet" href="../assets/css/loader.css">
</head>
<body class="admin" data-peran="petugas">
  <div class="admin-shell">
    
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <input type="checkbox" id="nav-toggle" class="nav-toggle" aria-label="Buka atau tutup menu">
      <div class="sidebar-header">
        <div>
            <a class="logo" href="dashboard.php"><img src="../img/logo-white.png" alt="GOWES"></a>
            <div class="role">Petugas / Satpam</div>
        </div>
        <label class="hamburger" for="nav-toggle" aria-label="Buka menu">
            <span></span><span></span><span></span>
        </label>
      </div>
      <nav class="menu" aria-label="Menu Petugas / Satpam">
        <a href="dashboard.php">Dashboard</a>
        <a href="unit.php">Unit Go-bike</a>
        <a href="peminjaman.php">Peminjaman</a>
        <a href="laporan.php">Laporan Kondisi</a>
        <a href="riwayat.php" class="on">Riwayat Peminjaman</a>
      </nav>
      
      <!-- LOGOUT MEMANGGIL MODAL -->
      <a class="menu-logout" href="javascript:void(0);" onclick="openLogoutModal()">Keluar</a>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main">
      <header class="topbar-a">
        <div>
          <h1>Riwayat Peminjaman</h1>
          <p>Rekap seluruh peminjaman Go-bike</p>
        </div>
        <span class="pill">Petugas - Satpam</span>
      </header>

      <section class="panel">
        
        <!-- HEADER PANEL DENGAN TOMBOL FILTER -->
        <div class="panel-head-tools">
          <h2>Semua Peminjaman</h2>
          <div class="toolbar-group">
            <form method="GET" action="riwayat.php" class="input-wrapper">
                <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tgl_filter) ?>">
                <input type="text" name="cari" class="inp-cari" placeholder="Cari NIM / Unit..." value="<?= htmlspecialchars($search) ?>" oninput="if(this.value.trim() === '') this.form.submit()">
                <button type="submit" class="btn-cari">Cari</button>
            </form>
            <button type="button" class="btn-filter" onclick="openDatePicker()">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
              Tanggal
            </button>
            <form id="dateForm" method="GET" action="riwayat.php">
                <input type="hidden" name="cari" value="<?= htmlspecialchars($search) ?>">
                <input type="date" id="hiddenDateInput" name="tanggal" value="<?= htmlspecialchars($tgl_filter) ?>" onchange="document.getElementById('dateForm').submit()">
            </form>
          </div>
        </div>

        <!-- TABEL -->
        <div class="table-wrap">
          <table class="tbl">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Nomor Identitas</th>
                <th>Unit</th>
                <th>Durasi</th>
                <th>Kembali</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($riwayat) > 0): ?>
                <?php foreach ($riwayat as $row): ?>
                  <?php 
                    $ts_mulai = strtotime($row['waktu_mulai']);
                    $tgl = date('d', $ts_mulai) . ' ' . $bulan_indo[(int)date('m', $ts_mulai)] . ' ' . date('Y', $ts_mulai);
                    $jam_mulai = date('H.i', $ts_mulai);
                    
                    $jam_kembali = "Belum kembali";
                    if ($row['waktu_kembali']) {
                        $jam_kembali = date('H.i', strtotime($row['waktu_kembali'])) . ' WIB';
                    }

                    $status = $row['status_keterlambatan'];
                    $chip_class = 'chip-ok';
                    
                    if ($status === 'Terlambat') {
                        $chip_class = 'chip-bad';
                    } else if ($status === 'Mendekati Batas' || $row['waktu_kembali'] === null) {
                        $sisa = strtotime($row['batas_kembali']) - time();
                        if ($sisa < 1800 && $sisa > 0) {
                            $status = 'Mendekati batas';
                            $chip_class = 'chip-warn';
                        } else if ($sisa <= 0) {
                            $status = 'Terlambat';
                            $chip_class = 'chip-bad';
                        } else {
                            $status = 'Tepat waktu';
                            $chip_class = 'chip-ok';
                        }
                    } else {
                        $status = 'Tepat waktu';
                        $chip_class = 'chip-ok';
                    }
                  ?>
                  <tr>
                    <td><?= $tgl ?></td>
                    <td><?= $jam_mulai ?></td>
                    <td><?= htmlspecialchars($row['no_identitas']) ?></td>
                    <td><?= htmlspecialchars($row['kode_qr']) ?></td>
                    <td><?= htmlspecialchars($row['durasi_pinjam']) ?> jam</td>
                    <td><?= $jam_kembali ?></td>
                    <td><span class="chip <?= $chip_class ?>"><?= $status ?></span></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="7" style="text-align:center; color: var(--muted); padding: 30px;">Tidak ada riwayat peminjaman yang sesuai.</td></tr>
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
          <p>Apakah Anda yakin ingin keluar dari akun ini? Anda harus login kembali untuk mengakses halaman petugas.</p>
          <div class="modal-actions">
              <button type="button" class="btn-cancel" onclick="closeLogoutModal()">Batal</button>
              <a href="../auth/login.php?logout=true" class="btn-confirm">Ya, Keluar</a>
          </div>
      </div>
  </div>

  <!-- JAVASCRIPT EKSTERNAL -->
  <script src="../assets/js/common.js"></script>
  <script src="../assets/js/datepicker.js"></script>
  <script src="../assets/js/logout-modal.js"></script>
  <script src="../assets/js/loader.js"></script>
</body>
</html>