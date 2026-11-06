<?php
session_start();

// 1. Wajib login sebagai peminjam
if (!isset($_SESSION['id_peminjam'])) {
    // Simpan tujuan supaya setelah login bisa kembali ke halaman ini
    // (login.php perlu memproses parameter ?redirect=)
    header("Location: ../auth/login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
require_once '../config/koneksi.php';

$id_peminjam = $_SESSION['id_peminjam'];
$nama_user   = $_SESSION['nama'];
$inisial     = strtoupper(substr($nama_user, 0, 1));

// 2. Ambil kode sepeda: terima ?qr=GB-03 (atau ?kode=GB-03)
$kode_qr = trim($_GET['qr'] ?? $_GET['kode'] ?? '');

// Jika isinya URL lengkap (hasil scan mentah), ambil kodenya saja
if (filter_var($kode_qr, FILTER_VALIDATE_URL)) {
    parse_str(parse_url($kode_qr, PHP_URL_QUERY) ?? '', $q);
    $kode_qr = trim($q['qr'] ?? $q['kode'] ?? '');
}

$error  = '';
$sepeda = null;

// 3. Cari sepeda
if ($kode_qr === '') {
    $error = "Kode QR tidak valid.";
} else {
    try {
        $stmt = $pdo->prepare("SELECT s.id_sepeda, s.kode_qr, s.status_ketersediaan, sh.nama_shelter, sh.lokasi
                               FROM sepeda s
                               JOIN shelter sh ON s.id_shelter = sh.id_shelter
                               WHERE s.kode_qr = :qr");
        $stmt->execute(['qr' => $kode_qr]);
        $sepeda = $stmt->fetch();

        if (!$sepeda) {
            $error = "Sepeda tidak ditemukan.";
        } elseif ($sepeda['status_ketersediaan'] !== 'Tersedia') {
            $error = "Sepeda sedang tidak tersedia.";
        }
    } catch (PDOException $e) {
        $error = "Terjadi kesalahan saat mengambil data sepeda.";
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hasil Scan · GOWES</title>
  <link rel="icon" href="../img/logo.png">
  <link rel="stylesheet" href="../assets/css/fonts.css">
  <link rel="stylesheet" href="../assets/css/tokens.css">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/components.css">
  <link rel="stylesheet" href="../assets/css/mahasiswa.css">
  <link rel="stylesheet" href="../assets/css/loader.css">
</head>
<body class="user">
  <header class="topbar">
    <a class="brand" href="dashboard.php"><img src="../img/logo.png" alt="GOWES"></a>
    <nav class="topnav">
      <a class="navitem on" href="scan.php">
        <span class="ico">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7V4.5A1.5 1.5 0 0 1 4.5 3H7M13 3h2.5A1.5 1.5 0 0 1 17 4.5V7M17 13v2.5a1.5 1.5 0 0 1-1.5 1.5H13M7 17H4.5A1.5 1.5 0 0 1 3 15.5V13M6.5 10h7" /></svg>
        </span>Scan QR
      </a>
    </nav>
    <a href="profil.php" class="user-pill-btn logged-in" style="text-decoration: none;">
      <span><?= htmlspecialchars($nama_user) ?></span>
      <div class="avatar-sm"><?= htmlspecialchars($inisial) ?></div>
    </a>
  </header>

  <main class="page">
    <div class="col">
      <a class="back d-only" href="scan.php">‹ Kembali</a>

      <?php if ($error): ?>
          <div class="alert-error" style="margin-top: 20px;"><?= htmlspecialchars($error) ?></div>
          <a href="scan.php" class="btn btn-primary" style="text-decoration: none; margin-top: 10px;">Scan Ulang</a>
      <?php else: ?>
          <h1 class="title" style="margin-top: 0;">Go-bike Ditemukan</h1>
          <p class="lead">Pastikan data di bawah sesuai dengan unit di depannya.</p>

          <section class="card" style="padding: 24px; margin-top: 10px;">
              <h2 style="font-size: 32px; color: var(--primary); font-weight: 800; margin-bottom: 20px;"><?= htmlspecialchars($sepeda['kode_qr']) ?></h2>
              <div class="rows">
                  <div class="row"><span class="k">ID Go-bike</span><span class="v"><?= htmlspecialchars($sepeda['kode_qr']) ?></span></div>
                  <div class="row"><span class="k">Shelter</span><span class="v"><?= htmlspecialchars($sepeda['nama_shelter']) ?></span></div>
                  <div class="row"><span class="k">Lokasi</span><span class="v"><?= htmlspecialchars($sepeda['lokasi']) ?></span></div>
                  <div class="row"><span class="k">Status</span><span class="chip chip-ok">Tersedia</span></div>
              </div>
              <a href="durasi.php?id=<?= (int)$sepeda['id_sepeda'] ?>" class="btn btn-primary" style="display: flex; justify-content: center; margin-top: 24px; text-decoration: none;">Pilih Durasi</a>
          </section>
      <?php endif; ?>
    </div>
  </main>

  <script src="../assets/js/loader.js"></script>
</body>
</html>