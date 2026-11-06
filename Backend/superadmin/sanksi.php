<?php
session_start();

// ==========================================
// 1. KONEKSI DATABASE & PROTEKSI HALAMAN
// ==========================================
// Sesuaikan path ini dengan struktur folder Anda
require_once '../config/koneksi.php'; 

// Cek apakah user sudah login sebagai superadmin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit;
}

$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Super Admin';

// ==========================================
// 2. LOGIKA FILTER & PENCARIAN
// ==========================================
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_date = isset($_GET['date']) ? trim($_GET['date']) : '';

try {
    // Query dasar: Mengambil data sanksi, join dengan peminjam, 
    // dan menghitung total keterlambatan menggunakan subquery.
    $sql = "SELECT 
                p.no_identitas, 
                p.nama, 
                s.jenis_tanggungan, 
                s.tgl_mulai, 
                s.tgl_selesai,
                (SELECT COUNT(*) FROM peminjaman pm 
                 WHERE pm.id_peminjam = p.id_peminjam 
                 AND pm.status_keterlambatan = 'Terlambat') AS total_terlambat
            FROM sanksi s
            JOIN peminjam p ON s.id_peminjam = p.id_peminjam";
            
    $where_clauses = [];
    $params = [];

    // Filter pencarian (NIM atau Nama)
    if (!empty($search)) {
        $where_clauses[] = "(p.no_identitas ILIKE :search OR p.nama ILIKE :search)";
        $params[':search'] = "%$search%";
    }

    // Filter tanggal (Mengecek apakah tanggal yang dipilih berada di antara periode sanksi)
    if (!empty($filter_date)) {
        $where_clauses[] = ":filter_date BETWEEN s.tgl_mulai AND s.tgl_selesai";
        $params[':filter_date'] = $filter_date;
    }

    // Gabungkan klausa WHERE
    if (count($where_clauses) > 0) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }

    // Urutkan dari sanksi terbaru
    $sql .= " ORDER BY s.tgl_mulai DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $sanksi_list = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}

// ==========================================
// 3. FUNGSI BANTUAN FORMAT TANGGAL
// ==========================================
function formatTanggalIndo($dateString) {
    if (!$dateString) return '-';
    $bulan = [
        1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 
        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
    ];
    $date = new DateTime($dateString);
    return $date->format('d') . ' ' . $bulan[(int)$date->format('n')] . ' ' . $date->format('Y');
}

function formatPeriode($start, $end) {
    if (!$start || !$end) return '-';
    $bulan = [
        1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 
        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
    ];
    $d1 = new DateTime($start);
    $d2 = new DateTime($end);
    
    // Jika bulan dan tahun sama, gabungkan formatnya (Contoh: 03–06 Okt 2026)
    if ($d1->format('m Y') === $d2->format('m Y')) {
        return $d1->format('d') . '–' . $d2->format('d') . ' ' . $bulan[(int)$d2->format('n')] . ' ' . $d2->format('Y');
    }
    return $d1->format('d') . ' ' . $bulan[(int)$d1->format('n')] . ' - ' . $d2->format('d') . ' ' . $bulan[(int)$d2->format('n')] . ' ' . $d2->format('Y');
}
?>

<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sanksi · GOWES</title>
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
        <a href="unit.php" class="">Unit Go-bike</a>
        <a href="peminjaman.php" class="">Peminjaman</a>
        <a href="sanksi.php" class="on">Sanksi</a>
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
          <h1>Sanksi</h1>
          <p>Teguran 1: suspen 3 hari · Teguran 2: 1 minggu · Teguran 3: permanen</p>
        </div>
        <span class="pill"><?= htmlspecialchars($nama_admin) ?></span>
      </header>

      <section class="panel">
        <div class="panel-head">
          <h2>Rekap Sanksi</h2>
          
          <!-- FORM FILTER & PENCARIAN -->
          <form method="GET" action="" class="tools">
            <label class="icon-btn date" title="Filter tanggal">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                  fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                  aria-hidden="true">
                  <rect x="3.2" y="4.4" width="13.6" height="12.4" rx="2"/>
                  <path d="M3.2 8.4h13.6M7 2.8v3M13 2.8v3"/>
                </svg>
                <input type="date" name="date" aria-label="Filter tanggal" value="<?= htmlspecialchars($filter_date) ?>" onchange="this.form.submit()">
            </label>
            
            <label class="tool-search">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                  fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                  aria-hidden="true">
                  <circle cx="9" cy="9" r="5.4"/>
                  <path d="m13.2 13.2 3.6 3.6"/>
                </svg>
                <input type="search" name="search" placeholder="Cari NIM / Nama" aria-label="Cari" value="<?= htmlspecialchars($search) ?>">
            </label>
            <button type="submit" style="display:none;"></button>
          </form>

        </div>
        <div class="table-wrap">
          <table class="tbl">
            <thead>
              <tr>
                <th>Nomor Identitas</th>
                <th>Nama</th>
                <th>Status Terlambat</th>
                <th>Keterangan</th>
                <th>Periode Suspen</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($sanksi_list) > 0): ?>
                  <?php foreach ($sanksi_list as $row): ?>
                      <?php
                          // ==========================================
                          // LOGIKA STATUS SANKSI
                          // Membandingkan tanggal selesai dengan hari ini
                          // ==========================================
                          $hari_ini = date('Y-m-d');
                          $is_aktif = ($hari_ini <= $row['tgl_selesai']);
                          
                          // Tampilan Chip
                          if ($is_aktif) {
                              $chip_class = 'chip-bad';
                              $chip_text = htmlspecialchars($row['jenis_tanggungan']);
                          } else {
                              $chip_class = 'chip-ok';
                              $chip_text = 'Aktif'; // Sesuai desain, jika selesai kembali aktif
                          }
                      ?>
                      <tr>
                        <td><?= htmlspecialchars($row['no_identitas']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['total_terlambat']) ?> kali</td>
                        <td>
                            <span class="chip <?= $chip_class ?>"><?= $chip_text ?></span>
                        </td>
                        <td>
                            <?php if ($is_aktif): ?>
                                <?= formatPeriode($row['tgl_mulai'], $row['tgl_selesai']) ?>
                            <?php else: ?>
                                Selesai
                            <?php endif; ?>
                        </td>
                      </tr>
                  <?php endforeach; ?>
              <?php else: ?>
                  <tr>
                      <td colspan="5" style="text-align: center; padding: 20px; color: var(--muted);">
                          Tidak ada data sanksi ditemukan.
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