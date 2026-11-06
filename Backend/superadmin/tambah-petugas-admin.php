<?php
session_start();

// ==========================================
// 1. KONEKSI DATABASE & PROTEKSI HALAMAN
// ==========================================
require_once '../config/koneksi.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit;
}

$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Super Admin';
$id_superadmin = isset($_SESSION['id_superadmin']) ? $_SESSION['id_superadmin'] : 1; // Asumsi ID superadmin ada di session
$pesan = '';

// ==========================================
// 2. PROSES TAMBAH DATA (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add_petugas') {
            $id_shelter = $_POST['shelter'];
            $username   = trim($_POST['nim']);
            $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);

            $sql = "INSERT INTO petugas (id_superadmin, id_shelter, username, password) VALUES (:id_super, :id_shelter, :username, :password)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_super' => $id_superadmin,
                ':id_shelter' => $id_shelter,
                ':username' => $username,
                ':password' => $password
            ]);
            $pesan = "<div class='alert-success'>Petugas berhasil ditambahkan!</div>";
            
        } elseif ($action === 'add_admin') {
            $username   = trim($_POST['nim']);
            $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);

            $sql = "INSERT INTO admin (id_superadmin, username, password) VALUES (:id_super, :username, :password)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_super' => $id_superadmin,
                ':username' => $username,
                ':password' => $password
            ]);
            $pesan = "<div class='alert-success'>Admin berhasil ditambahkan!</div>";
        }
    } catch (PDOException $e) {
        $pesan = "<div class='alert-error'>Gagal menambahkan data: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// ==========================================
// 3. AMBIL DATA UNTUK DITAMPILKAN
// ==========================================
try {
    // Ambil data shelter untuk dropdown
    $shelters = $pdo->query("SELECT id_shelter, nama_shelter, lokasi FROM shelter ORDER BY nama_shelter ASC")->fetchAll();

    // Ambil data petugas (JOIN dengan shelter)
    $sql_petugas = "SELECT p.username, s.nama_shelter, s.lokasi 
                    FROM petugas p 
                    JOIN shelter s ON p.id_shelter = s.id_shelter 
                    ORDER BY s.nama_shelter ASC";
    $petugas_list = $pdo->query($sql_petugas)->fetchAll();

    // Ambil data admin
    $admin_list = $pdo->query("SELECT username FROM admin ORDER BY username ASC")->fetchAll();

} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
?>

<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tambah Petugas/Admin · GOWES</title>
  <link rel="icon" href="../img/logo.png">
  <link rel="stylesheet" href="../css/fonts.css">
  <link rel="stylesheet" href="../css/tokens.css">
  <link rel="stylesheet" href="../css/base.css">
  <link rel="stylesheet" href="../css/components.css">
  <link rel="stylesheet" href="../css/panel.css">
  <style>
      /* Sedikit tambahan style untuk alert */
      .alert-success { background: #e8f5e9; color: #2e7d32; padding: 10px 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #a5d6a7;}
      .alert-error { background: #ffebee; color: #c62828; padding: 10px 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #ef9a9a;}
  </style>
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
        <a href="dashboard.php">Dashboard</a>
        <a href="unit.php">Unit Go-bike</a>
        <a href="peminjaman.php">Peminjaman</a>
        <a href="sanksi.php">Sanksi</a>
        <a href="laporan.php">Laporan Kondisi</a>
        <a href="riwayat.php">Riwayat Peminjaman</a>
        <a href="tambah-petugas-admin.php" class="on">Tambah Petugas/Admin</a>
      </nav>

      <a class="menu-logout" href="../auth/login.php?logout=true">Keluar</a>
    </aside>

    <!-- ===== KONTEN UTAMA ===== -->
    <main class="main">
      <header class="topbar-a">
        <div>
          <h1>Tambah Petugas/Admin</h1>
          <p>Data Petugas/Admin Go-bike</p>
        </div>
        <span class="pill"><?= htmlspecialchars($nama_admin) ?></span>
      </header>

      <?= $pesan ?>

      <!-- ===== TABEL PETUGAS ===== -->
      <section class="panel" id="panel-petugas">
        <div class="panel-head">
          <h2>Data Petugas Go-bike</h2>
          <div class="tools">
            <button type="button" class="icon-btn solid" data-open="m-tambah-petugas" title="Tambah Petugas" aria-label="Tambah Petugas"><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M10 4v12M4 10h12"/></svg></button>
            <label class="tool-search"><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="9" cy="9" r="5.4"/><path d="m13.2 13.2 3.6 3.6"/></svg><input type="search" placeholder="Cari" aria-label="Cari petugas"></label>
          </div>
        </div>
        <div class="table-wrap">
          <table class="tbl">
            <thead>
              <tr>
                <th>Shelter</th>
                <th>Username (Nomor Identitas)</th>
              </tr>
            </thead>
            <tbody>
              <?php if(count($petugas_list) > 0): ?>
                  <?php foreach($petugas_list as $ptg): ?>
                      <tr>
                        <td><?= htmlspecialchars($ptg['nama_shelter']) ?> (<?= htmlspecialchars($ptg['lokasi']) ?>)</td>
                        <td><?= htmlspecialchars($ptg['username']) ?></td>
                      </tr>
                  <?php endforeach; ?>
              <?php else: ?>
                  <tr><td colspan="2" style="text-align:center;">Belum ada data petugas.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ===== TABEL ADMIN ===== -->
      <section class="panel" id="panel-admin">
        <div class="panel-head">
          <h2>Data Admin Go-bike</h2>
          <div class="tools">
            <button type="button" class="icon-btn solid" data-open="m-tambah-admin" title="Tambah Admin" aria-label="Tambah Admin"><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M10 4v12M4 10h12"/></svg></button>
            <label class="tool-search"><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="9" cy="9" r="5.4"/><path d="m13.2 13.2 3.6 3.6"/></svg><input type="search" placeholder="Cari" aria-label="Cari admin"></label>
          </div>
        </div>
        <div class="table-wrap">
          <table class="tbl">
            <thead>
              <tr>
                <th>Username (Nomor Identitas)</th>
              </tr>
            </thead>
            <tbody>
              <?php if(count($admin_list) > 0): ?>
                  <?php foreach($admin_list as $adm): ?>
                      <tr>
                        <td><?= htmlspecialchars($adm['username']) ?></td>
                      </tr>
                  <?php endforeach; ?>
              <?php else: ?>
                  <tr><td colspan="1" style="text-align:center;">Belum ada data admin.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ===== DIALOG TAMBAH PETUGAS ===== -->
      <div class="modal" id="m-tambah-petugas" role="dialog" aria-modal="true" aria-labelledby="m-tambah-petugas-t">
        <div class="modal-box">
          <h2 id="m-tambah-petugas-t">Tambah Petugas Go-bike?</h2>
          <form class="modal-form" method="POST" action="">
            <input type="hidden" name="action" value="add_petugas">
            
            <label class="fld">
              <span>Letak Shelter</span>
              <select name="shelter" required>
                <option value="" disabled selected>Pilih shelter</option>
                <?php foreach($shelters as $s): ?>
                    <option value="<?= $s['id_shelter'] ?>"><?= htmlspecialchars($s['nama_shelter']) ?> (<?= htmlspecialchars($s['lokasi']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </label>
            <label class="fld">
              <span>Nomor Identitas (Username)</span>
              <input type="text" name="nim" inputmode="numeric" required>
            </label>
            <label class="fld">
              <span>Password</span>
              <input type="password" name="password" autocomplete="new-password" required>
            </label>
            <button type="submit" class="modal-ok">Konfirmasi</button>
          </form>
        </div>
      </div>

      <!-- ===== DIALOG TAMBAH ADMIN ===== -->
      <div class="modal" id="m-tambah-admin" role="dialog" aria-modal="true" aria-labelledby="m-tambah-admin-t">
        <div class="modal-box">
          <h2 id="m-tambah-admin-t">Tambah Admin Go-bike?</h2>
          <form class="modal-form" method="POST" action="">
            <input type="hidden" name="action" value="add_admin">
            
            <label class="fld">
              <span>Nomor Identitas (Username)</span>
              <input type="text" name="nim" inputmode="numeric" required>
            </label>
            <label class="fld">
              <span>Password</span>
              <input type="password" name="password" autocomplete="new-password" required>
            </label>
            <button type="submit" class="modal-ok">Konfirmasi</button>
          </form>
        </div>
      </div>

    </main>
  </div>

  <script src="../js/common.js"></script>
  <script src="../js/tabel.js"></script>
  <script src="../js/modal.js"></script>
</body>

</html>