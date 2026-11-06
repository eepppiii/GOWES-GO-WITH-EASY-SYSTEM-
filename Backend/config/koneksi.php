<?php
// Konfigurasi Database (Sesuaikan dengan pengaturan di DBeaver/Laragon Anda)
$host   = 'localhost'; // Biasanya localhost atau 127.0.0.1
$port   = '5432';      // Port default PostgreSQL (MySQL biasanya 3306)
$dbname = 'db_gowes';
$user   = 'postgres';  // Username default PostgreSQL di Laragon
$pass   = 'Igmaida133';          // Password default PostgreSQL di Laragon biasanya kosong, sesuaikan jika Anda mengubahnya

// DSN (Data Source Name) untuk PostgreSQL
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

// Opsi PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Menampilkan error jika ada masalah
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Mengambil data sebagai array asosiatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Mematikan emulasi prepare statement
];

try {
    // Membuat koneksi
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "Koneksi Berhasil!"; // Hapus tanda // jika ingin mengetes koneksi
} catch (\PDOException $e) {
    // Jika koneksi gagal, tampilkan pesan error
    die("Koneksi database gagal: " . $e->getMessage());
}
?>