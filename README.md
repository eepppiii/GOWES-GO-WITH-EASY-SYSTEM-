<div align="center">
  <h1>🚲 GOWES (Go With Easy System)</h1>
  <p><strong>Sistem Manajemen Peminjaman Sepeda Kampus Berbasis Web dan QR Code</strong></p>
  
  [![PBL Semester 3](https://img.shields.io/badge/PBL-Semester_3-blue.svg)](#)
  [![Prodi SIB](https://img.shields.io/badge/Prodi-Sistem_Informasi_Bisnis-orange.svg)](#)
  [![Politeknik Negeri Malang](https://img.shields.io/badge/Kampus-Politeknik_Negeri_Malang-navy.svg)](#)
</div>

<br>

**GOWES** adalah platform web terintegrasi yang dirancang untuk mendigitalisasi proses peminjaman sepeda listrik (E-bike) di lingkungan kampus Politeknik Negeri Malang (Polinema). Sistem ini menggantikan alur pencatatan manual dan penitipan KTM di pos satpam menjadi sistem otomatis berbasis *QR Code*. Proyek ini dikembangkan sebagai bagian dari tugas **Project Based Learning (PBL)**.

---

## 📁 Navigasi Repositori
Seluruh aset proyek telah dikelompokkan agar mudah ditelusuri. Silakan klik direktori di bawah ini untuk melihat detail masing-masing bagian:

*   📄 **[Proposal & Dokumentasi](./Proposal)** — Berisi file Proposal PBL, perancangan sistem, dan laporan akhir.
*   🎨 **[UI/UX & Desain](./UI-UX)** — Berisi tautan Figma, *wireframe*, dan aset visual antarmuka pengguna.
*   🗄️ **[Database](./Database)** — Berisi rancangan ERD, skema tabel, dan file *export* `.sql` untuk *database*.
*   💻 **[Source Code](./Source-Code)** — Berisi kumpulan *codingan* aplikasi web secara keseluruhan (Frontend & Backend).

---

## 🎯 Tujuan Proyek
Membangun platform peminjaman sepeda yang mengotomatisasi proses peminjaman mandiri via pemindaian QR Code, memastikan akurasi data peminjaman secara *real-time*, membatasi durasi peminjaman, serta menerapkan rekam jejak tanggungan dan sanksi secara otomatis.

## 👥 Fitur & Hak Akses Utama

<details>
<summary><strong>🎓 Mahasiswa (Peminjam)</strong></summary>
<br>

- **Pemindaian QR & Autentikasi:** Memindai QR Code di papan *shelter* untuk diarahkan ke portal *login* menggunakan NIM.
- **Validasi Kelayakan:** Sistem otomatis menolak transaksi di luar jam operasional atau jika akun sedang disuspensi.
- **Pemilihan Durasi:** Opsi durasi peminjaman (1 - 3 jam) yang otomatis disesuaikan agar tidak melampaui jam tutup (15.30 WIB).
- **Bukti Digital & *Countdown*:** Tiket peminjaman digital dengan *timer* hitung mundur sisa waktu pinjam.
- **Laporan Kondisi Terintegrasi:** Kewajiban mengisi formulir kondisi fisik E-bike langsung di dalam web saat pengembalian.
</details>

<details>
<summary><strong>🛡️ Admin / Petugas (P2M & Satpam)</strong></summary>
<br>

- **Monitoring Multi-Shelter:** Memantau status ketersediaan E-bike secara *real-time* di Shelter Direktorat AA & Shelter Teknik Sipil.
- **Manajemen Tanggungan:** Melacak daftar peminjam aktif, lokasi *shelter*, durasi, dan estimasi waktu pengembalian.
- **Sistem Sanksi Otomatis:** Mencatat pelanggaran batas waktu pengembalian dan menangguhkan akun secara otomatis.
- **Rekapitulasi Laporan:** Melihat data laporan kondisi E-bike dari mahasiswa untuk kebutuhan pemeliharaan.
</details>

---

## ⚖️ Aturan Peminjaman & Sanksi
Sistem dilengkapi dengan penegakan kedisiplinan otomatis bagi pengguna yang terlambat mengembalikan sepeda:
1.  ⚠️ **Teguran 1 (Terlambat 1x):** Suspensi hak peminjaman selama **3 hari**.
2.  🚫 **Teguran 2 (Terlambat 2x):** Suspensi hak peminjaman selama **1 minggu (7 hari)**.
3.  ⛔ **Teguran 3 (Terlambat 3x):** Suspensi **permanen** (pembekuan akun).

---

## 💻 Tim Pengembang (PBL SIB-2A)

| Nama | NIM | Peran Utama |
| :--- | :--- | :--- |
| **Farrell Raissa Ermanto** | `254107060150` | Ketua Tim, System Analysis & UI/UX |
| **Callista Dinar Kushermyla** | `254107060087` | System Analysis & UI/UX |
| **Luthfiyanna Nuha Syahada** | `254107060077` | Database Architect |
| **Muhammad Fakhri Al Fawwaz** | `254107060099` | Frontend Developer |
| **Reffi Dwino Igmaiyoda** | `254107060003` | Backend Developer |

<br>

**Mata Kuliah Terintegrasi:**
*   **Pemrograman Web** — Dimas Wahyu Wibowo, S.T., M.T.
*   **Basis Data Lanjut** — Moch. Zawaruddin Abdullah, S.ST., M.Kom.
*   **UI/UX** — Anugrah Nur Rahmanto, Sn., M.Ds.