<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
    header("Location: ../auth/login.php"); 
    exit;
}
require_once '../config/koneksi.php';

if (isset($_GET['kembali'])) {
    $id_peminjaman = $_GET['kembali'];
    try {
        $pdo->prepare("UPDATE peminjaman SET waktu_kembali = CURRENT_TIMESTAMP WHERE id_peminjaman = :id")->execute(['id' => $id_peminjaman]);
        $id_sepeda = $pdo->query("SELECT id_sepeda FROM peminjaman WHERE id_peminjaman = $id_peminjaman")->fetchColumn();
        $pdo->prepare("UPDATE sepeda SET status_ketersediaan = 'Tersedia' WHERE id_sepeda = :id_sep")->execute(['id_sep' => $id_sepeda]);
        echo "<script>alert('Sepeda berhasil dikembalikan!'); window.location='peminjaman.php';</script>";
    } catch (PDOException $e) { echo "<script>alert('Gagal: " . $e->getMessage() . "');</script>"; }
}

$search = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$where = ""; $params = [];
if (!empty($search)) {
    $where = " AND (pm.no_identitas ILIKE :search OR s.kode_qr ILIKE :search)";
    $params['search'] = "%$search%";
}

$sql = "SELECT p.*, pm.no_identitas, s.kode_qr FROM peminjaman p JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam JOIN sepeda s ON p.id_sepeda = s.id_sepeda WHERE p.waktu_kembali IS NULL $where ORDER BY p.waktu_mulai DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $peminjaman = $stmt->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Peminjaman · GOWES</title>
  <link rel="icon" href="../img/logo.png">
  <link rel="stylesheet" href="../assets/css/fonts.css"><link rel="stylesheet" href="../assets/css/tokens.css">
  <link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/components.css">
  <link rel="stylesheet" href="../assets/css/panel.css"><link rel="stylesheet" href="../assets/css/loader.css">
  <style>
    .search-row { display: flex; align-items: center; gap: 12px; }
    .search-row form { display: flex; flex: 1; gap: 8px; }
    .search-row .inp { flex: 1; height: 38px; border: 0; border-radius: 8px; background: #f4f5f7; padding: 0 16px; font-family: inherit; font-size: 13px; outline: none; }
    .search-row .inp:focus { box-shadow: 0 0 0 2px var(--primary); }
    .btn-cari { background: var(--primary); color: white; border: none; padding: 0 20px; height: 38px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; }
    .tbl tbody tr { transition: background 0.2s; }
    .tbl tbody tr:hover { background-color: #f8fafc; }
    .actions { display: flex; gap: 8px; }
  </style>
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
        <a href="peminjaman.php" class="on">Peminjaman</a>
        <a href="laporan.php">Laporan Kondisi</a>
        <a href="riwayat.php">Riwayat Peminjaman</a>
      </nav>
      <a class="menu-logout" href="javascript:void(0);" onclick="openLogoutModal()">Keluar</a>
    </aside>

    <main class="main">
      <header class="topbar-a">
        <div><h1>Peminjaman</h1><p>Verifikasi bukti digital dan konfirmasi pengembalian</p></div>
        <span class="pill">Petugas - Satpam</span>
      </header>

      <section class="panel" style="padding: 16px 24px;">
        <div class="search-row">
          <h2>Verifikasi Bukti</h2>
          <form method="GET" action="">
            <input class="inp" type="search" name="cari" placeholder="Cari Nomor Identitas atau ID Go-bike" value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn-cari">Cari</button>
          </form>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Peminjaman Aktif</h2></div>
        <div class="table-wrap">
          <table class="tbl">
            <thead><tr><th>Nomor Identitas</th><th>Unit</th><th>Mulai</th><th>Batas</th><th>Sisa waktu</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
              <?php if (count($peminjaman) > 0): ?>
                <?php foreach ($peminjaman as $row): ?>
                  <?php 
                    $jam_mulai = date('H.i', strtotime($row['waktu_mulai']));
                    $jam_batas = date('H.i', strtotime($row['batas_kembali']));
                    $status = $row['status_keterlambatan'];
                    $chip_class = ($status === 'Terlambat') ? 'chip-bad' : ((strtotime($row['batas_kembali']) - time() < 1800) ? 'chip-warn' : 'chip-ok');
                    $batas_iso = date('Y-m-d\TH:i:s', strtotime($row['batas_kembali']));
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($row['no_identitas']) ?></td>
                    <td><?= htmlspecialchars($row['kode_qr']) ?></td>
                    <td><?= $jam_mulai ?></td>
                    <td><?= $jam_batas ?></td>
                    <td><span class="countdown" data-target="<?= $batas_iso ?>">Menghitung...</span></td>
                    <td><span class="chip <?= $chip_class ?>"><?= $status ?></span></td>
                    <td><div class="actions"><a class="btn-sm solid" href="?kembali=<?= $row['id_peminjaman'] ?>" onclick="return confirm('Konfirmasi sepeda telah dikembalikan?')">Konfirmasi Kembali</a></div></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="7" style="text-align: center; color: var(--muted);">Tidak ada peminjaman aktif.</td></tr>
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
  <script src="../assets/js/countdown.js"></script>
  <script src="../assets/js/loader.js"></script>
  <script src="../assets/js/logout-modal.js"></script>
</body>
</html>