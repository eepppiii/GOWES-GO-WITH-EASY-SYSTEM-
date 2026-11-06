<?php
session_start();

// 1. Proteksi Halaman: Hanya Admin yang bisa masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
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
    $where_clauses[] = "DATE(lk.tgl_lapor) = :tanggal";
    $params['tanggal'] = $tgl_filter;
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// 4. Ambil Data Laporan
try {
    $sql = "SELECT lk.*, s.kode_qr, pm.no_identitas 
            FROM laporan_kondisi lk
            JOIN peminjaman p ON lk.id_peminjaman = p.id_peminjaman
            JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam
            JOIN sepeda s ON p.id_sepeda = s.id_sepeda
            $where_sql
            ORDER BY lk.tgl_lapor DESC";
    $stmt = $pdo->prepare($sql); 
    $stmt->execute($params); 
    $laporan = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error mengambil data laporan: " . $e->getMessage());
}

$bulan_indo = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laporan kondisi · GOWES</title>
  <link rel="icon" href="../img/logo.png">
  
  <link rel="stylesheet" href="../assets/css/fonts.css">
  <link rel="stylesheet" href="../assets/css/tokens.css">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/components.css">
  <link rel="stylesheet" href="../assets/css/panel.css">
  <link rel="stylesheet" href="../assets/css/loader.css">

  <!-- CUSTOM CSS UNTUK TOOLBAR PENCARIAN & KALENDER -->
  <style>
    .panel-head-tools { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
    .panel-head-tools h2 { font-size: 17px; font-weight: 700; margin: 0; }
    .toolbar-group { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    
    /* Desain Input Pencarian */
    .input-wrapper { display: flex; align-items: center; border: 1.5px solid var(--primary); border-radius: 8px; overflow: hidden; background: #fff; height: 36px; }
    .inp-cari { border: none; padding: 0 12px; font-family: inherit; font-size: 12px; outline: none; min-width: 180px; color: var(--ink); height: 100%; }
    .inp-cari::placeholder { color: var(--muted); }
    .btn-cari { background: var(--primary); color: white; border: none; padding: 0 14px; height: 100%; font-weight: 600; font-size: 12px; cursor: pointer; transition: 0.2s; }
    .btn-cari:hover { background: var(--primary-dark); }
    
    /* Desain Tombol Tanggal */
    .btn-filter { display: inline-flex; align-items: center; gap: 6px; background: var(--primary); color: white; border: none; padding: 0 14px; height: 36px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; transition: 0.2s; text-decoration: none; }
    .btn-filter:hover { background: var(--primary-dark); }
    .btn-filter svg { width: 14px; height: 14px; }
    
    /* Sembunyikan input date asli */
    #hiddenDateInput { position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none; }
    
    /* Hover Tabel */
    .tbl tbody tr { transition: background 0.2s; }
    .tbl tbody tr:hover { background-color: #f8fafc; }
  </style>
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
        <a href="sanksi.php">Sanksi</a>
        <a href="laporan.php" class="on">Laporan Kondisi</a>
        <a href="riwayat.php">Riwayat Peminjaman</a>
      </nav>
      
      <!-- Logout Memanggil Modal -->
      <a class="menu-logout" href="javascript:void(0);" onclick="openLogoutModal()">Keluar</a>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main">
      <header class="topbar-a">
        <div>
          <h1>Laporan Kondisi</h1>
          <p>Laporan yang dikirim mahasiswa setelah pengembalian</p>
        </div>
        <span class="pill">Admin - RT POLINEMA</span>
      </header>

      <section class="panel">
        
        <!-- HEADER PANEL DENGAN TOMBOL FILTER -->
        <div class="panel-head-tools">
          <h2>Laporan Terbaru</h2>
          <div class="toolbar-group">
            <form method="GET" action="laporan.php" class="input-wrapper">
                <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tgl_filter) ?>">
                <input type="text" name="cari" class="inp-cari" placeholder="Cari NIM / Unit..." value="<?= htmlspecialchars($search) ?>" oninput="if(this.value.trim() === '') this.form.submit()">
                <button type="submit" class="btn-cari">Cari</button>
            </form>
            <button type="button" class="btn-filter" onclick="openDatePicker()">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
              Tanggal
            </button>
            <form id="dateForm" method="GET" action="laporan.php">
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
                <th>Unit</th>
                <th>Nomor Identitas</th>
                <th>Kondisi</th>
                <th>Keterangan</th>
              </tr>
            </thead>
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
  <script src="../assets/js/datepicker.js"></script>
  <script src="../assets/js/logout-modal.js"></script>
  <script src="../assets/js/loader.js"></script>
</body>
</html>