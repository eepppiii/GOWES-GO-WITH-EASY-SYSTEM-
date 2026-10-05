# GOWES (Go With Easy System)
**Sistem Manajemen Peminjaman Sepeda Kampus Berbasis Web dan QR Code**

GOWES adalah platform web yang dirancang untuk memfasilitasi dan mengelola peminjaman sepeda di lingkungan kampus secara terintegrasi. Sistem ini memanfaatkan teknologi pemindaian QR Code untuk mempercepat dan mempermudah alur peminjaman oleh mahasiswa, serta menyederhanakan pemantauan oleh administrator. 

Sistem ini terhubung dengan *database* terpusat untuk mengelola data unit sepeda, ketersediaan, autentikasi pengguna, dan riwayat transaksi secara dinamis dan *real-time*.

## 🚀 Panduan Deployment & Penggunaan
Aplikasi ini dirancang untuk di-hosting di *server* publik agar dapat diakses secara *online* oleh seluruh pengguna kampus. Berikut adalah panduan penyiapannya:

1. Siapkan layanan *hosting* dan nama domain (atau subdomain) yang akan digunakan.
2. Unggah (*upload*) seluruh file dan folder proyek ini ke dalam direktori publik pada panel *hosting* Anda (biasanya di dalam folder `public_html` atau `htdocs`).
3. Buat *database* baru (misalnya dengan nama `db_gowes`) melalui panel kontrol *hosting* (seperti cPanel, Plesk, dll).
4. Lakukan *import* file *database* (berformat `.sql`) yang telah disediakan ke dalam *database* yang baru saja Anda buat di *hosting*.
5. Buka file konfigurasi koneksi *database* proyek Anda melalui *File Manager* di *hosting*, lalu perbarui parameter koneksi (*host*, *username*, *password*, dan nama *database*) agar sesuai dengan kredensial *hosting* Anda.
6. Simpan perubahan. Aplikasi GOWES kini sudah *live* dan dapat diakses kapan saja melalui URL domain Anda.

## 📂 Peta Halaman Aplikasi
Aplikasi ini terbagi menjadi dua antarmuka utama yang menyesuaikan peran pengguna:

### 🎓 Antarmuka Mahasiswa
- `login` : Halaman masuk pengguna.
- `dashboard` : Beranda utama mahasiswa.
- `scan` : Antarmuka pemindaian QR Code sepeda.
- `hasil` : Menampilkan hasil pindai sepeda.
- `durasi` : Pemilihan estimasi waktu peminjaman.
- `konfirmasi` : Halaman validasi sebelum persetujuan.
- `bukti` : Bukti digital peminjaman aktif.
- `peminjaman` : Detail status peminjaman yang sedang berjalan.
- `kembali` : Proses pengembalian sepeda.
- `selesai` : Notifikasi peminjaman telah tuntas.
- `laporan` : Formulir pelaporan masalah atau kondisi sepeda.
- `riwayat` : Catatan aktivitas peminjaman mahasiswa.
- `sanksi` : Informasi teguran atau sanksi keterlambatan.
- `profil` : Pengaturan akun mahasiswa.

### 🛡️ Antarmuka Administrator
- `admin-login` : Halaman masuk khusus pengelola.
- `admin-dashboard` : Dasbor ringkasan sistem.
- `admin-unit` : Manajemen inventaris unit sepeda kampus.
- `admin-peminjaman` : Pemantauan sirkulasi peminjaman aktif.
- `admin-sanksi` : Pengelolaan data sanksi mahasiswa.
- `admin-laporan` : Daftar laporan kondisi unit dari pengguna.
- `admin-riwayat` : Log riwayat seluruh transaksi peminjaman.

## 📁 Struktur Direktori
- `css/gowes.css` : Berisi seluruh baris kode gaya (CSS) untuk tata letak dan visual UI.
- `js/gowes.js` : Skrip JavaScript yang mengatur interaksi antarmuka pengguna di sisi *client*.
- `img/icons/` : Folder penyimpanan aset gambar, termasuk ikon untuk navigasi (*navbar*).

## 📝 Catatan Pengembang
- **Integrasi Data & Keamanan:** Karena sistem di-hosting secara *online*, pilihan durasi, profil, dan log aktivitas diproses dan divalidasi langsung melalui *server* (*backend*) dan disimpan secara aman ke dalam *database*, menggantikan metode penyimpanan browser sementara.
- **Aset Ikon Navbar:** Ikon yang berada di direktori `img/icons/*.svg` masih bersifat sementara. Untuk hasil akhir yang presisi, pastikan Anda mengekspor 4 *glyph* asli dari desain Figma dan menimpanya ke dalam folder tersebut dengan nama file yang identik.
- **Tipografi:** Aplikasi menggunakan jenis huruf **Albert Sans** yang dimuat melalui Google Fonts. Karena aplikasi diakses secara *online*, perenderan font akan berjalan secara otomatis di perangkat pengguna.
- **Penyesuaian UI Admin:** Desain antarmuka untuk "Laporan Kondisi" dan "Riwayat" di panel admin direka ulang dan disusun berdasarkan relasi *database* yang tersedia, dikarenakan bagian referensi visual pada Figma asli terpotong.