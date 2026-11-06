<?php
// file: frontend/index.php
session_start();

// Jika user sudah login, arahkan ke dashboard sesuai role
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } elseif ($_SESSION['role'] === 'petugas') {
        header("Location: petugas/dashboard.php");
    } else {
        header("Location: mahasiswa/dashboard.php");
    }
    exit;
} else {
    // Jika belum login, arahkan ke halaman publik (guest)
    header("Location: public/dashboard.php");
    exit;
}
?>