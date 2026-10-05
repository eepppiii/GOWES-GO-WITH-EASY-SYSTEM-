# GOWES (Go With Easy System) 🚲
**Sistem Manajemen Peminjaman Sepeda Kampus Berbasis Web dan QR Code**

GOWES adalah platform web terintegrasi yang dirancang untuk mendigitalisasi proses peminjaman sepeda listrik (E-bike) di lingkungan kampus Politeknik Negeri Malang (Polinema). Sistem ini menggantikan alur pencatatan manual dan penitipan KTM di pos satpam menjadi sistem otomatis berbasis *QR Code*.

Proyek ini dikembangkan sebagai bagian dari tugas **Project Based Learning (PBL) Semester 3** Program Studi Sistem Informasi Bisnis (SIB).

## 🎯 Tujuan Proyek
Membangun platform peminjaman sepeda yang mengotomatisasi proses peminjaman mandiri via pemindaian QR Code, memastikan akurasi data peminjaman secara *real-time*, membatasi durasi peminjaman, serta menerapkan rekam jejak tanggungan dan sanksi secara otomatis.

## 👥 Hak Akses & Fitur Utama

Sistem ini memfasilitasi dua peran utama dalam operasional layanan E-bike kampus:

### 1. Mahasiswa (Peminjam)
*   **Pemindaian QR & Autentikasi:** Memindai QR Code di papan *shelter* untuk diarahkan ke portal *login* menggunakan NIM dan *password* akun.
*   **Validasi Kelayakan:** Sistem otomatis menolak transaksi jika dilakukan di luar jam operasional atau jika akun NIM sedang dalam masa suspensi (sanksi).
*   **Pemilihan Durasi:** Peminjam dapat memilih durasi peminjaman (1 jam, 2 jam, atau maksimal 3 jam) yang otomatis disesuaikan agar tidak melampaui jam tutup (15.30 WIB).
*   **Bukti Digital & *Countdown*:** Menampilkan tiket peminjaman digital untuk ditunjukkan kepada petugas saat mengambil kunci fisik, dilengkapi dengan *timer* hitung mundur sisa waktu pinjam.
*   **Laporan Kondisi Terintegrasi:** Saat pengembalian, mahasiswa diwajibkan mengisi formulir laporan kondisi fisik E-bike langsung di dalam web.

### 2. Admin / Petugas (P2M & Satpam)
*   **Monitoring Multi-Shelter:** Memantau status ketersediaan unit E-bike secara *real-time* di Shelter 1 (Gedung Direktorat AA) dan Shelter 2 (Gedung Teknik Sipil).
*   **Manajemen Tanggungan:** Melacak daftar peminjam aktif, lokasi *shelter* asal, durasi, dan estimasi waktu pengembalian.
*   **Sistem Sanksi Otomatis:** Mencatat pelanggaran batas waktu pengembalian dan memberikan sanksi suspensi akun secara otomatis.
*   **Rekapitulasi Laporan:** Melihat data kondisi E-bike pasca-pinjam untuk kebutuhan pemeliharaan (*maintenance*) oleh unit P2M.

## ⚖️ Aturan Peminjaman & Sanksi
Sistem dilengkapi dengan penegakan kedisiplinan otomatis bagi pengguna yang terlambat mengembalikan sepeda:
*   **Teguran 1 (Keterlambatan 1x):** Suspensi hak peminjaman selama 3 hari.
*   **Teguran 2 (Keterlambatan 2x):** Suspensi hak peminjaman selama 1 minggu (7 hari).
*   **Teguran 3 (Keterlambatan 3x):** Suspensi permanen (pembekuan akun).

## 🗄️ Struktur Basis Data
Sistem ini menggunakan *database* relasional yang mengelola entitas terintegrasi:
*   **Admin & Shelter:** Satu Admin bertanggung jawab atas satu *Shelter*, dan *Shelter* menaungi banyak unit sepeda.
*   **Peminjaman:** Menghubungkan entitas Mahasiswa dan Sepeda, mencatat waktu pinjam, batas waktu, dan waktu kembali.
*   **Laporan Kondisi (1-to-1):** Setiap transaksi peminjaman yang selesai diwajibkan memiliki satu laporan kondisi.
*   **Sanksi:** *Trigger* otomatis yang mencatat rentang masa suspensi jika waktu pengembalian melampaui durasi yang disepakati.

---

## 💻 Tim Pengembang (PBL Kelas SIB-2A)
Proyek ini dirancang dan dikembangkan oleh:

| Nama | NIM | Peran Utama |
| :--- | :--- | :--- |
| **Farrell Raissa Ermanto** | 254107060150 | Ketua Tim, System Analysis & UI/UX |
| **Callista Dinar Kushermyla** | 254107060087 | System Analysis & UI/UX |
| **Luthfiyanna Nuha Syahada** | 254107060077 | Database Architect |
| **Muhammad Fakhri Al Fawwaz** | 254107060099 | Frontend Developer |
| **Reffi Dwino Igmaiyoda** | 254107060003 | Backend Developer |

**Mata Kuliah Terintegrasi:**
1.  **Pemrograman Web** (Dosen: Dimas Wahyu Wibowo, S.T., M.T.)
2.  **Basis Data Lanjut** (Dosen: Moch. Zawaruddin Abdullah, S.ST., M.Kom.)
3.  **UI/UX** (Dosen: Anugrah Nur Rahmanto, Sn., M.Ds.)