<div align="center">
  <h1>🚲 GOWES (Go With Easy System)</h1>
  <p><strong>Sistem Manajemen Peminjaman Sepeda Kampus Berbasis Web dan QR Code</strong></p>

  [![PBL Semester 3](https://img.shields.io/badge/PBL-Semester_3-blue.svg)](#)
  [![Prodi SIB](https://img.shields.io/badge/Prodi-Sistem_Informasi_Bisnis-orange.svg)](#)
  [![Politeknik Negeri Malang](https://img.shields.io/badge/Kampus-Politeknik_Negeri_Malang-navy.svg)](#)
</div>

<br>

**GOWES** adalah platform web untuk mencatat dan mengatur peminjaman sepeda di dua shelter kampus Politeknik Negeri Malang (Polinema). Sistem ini menggantikan alur pencatatan manual dan penitipan KTM di pos satpam menjadi sistem otomatis berbasis *QR Code*. Peminjam memindai QR pada sepeda/slot, memilih jenis peminjam, login dengan NIM/NIP, memilih durasi, lalu menerima tiket digital. Seluruh proses dari waktu pinjam sampai kondisi sepeda saat kembali tercatat otomatis di sistem.

Proyek ini dikembangkan sebagai bagian dari tugas **Project Based Learning (PBL)** D-IV Sistem Informasi Bisnis, Jurusan Teknologi Informasi, Politeknik Negeri Malang.

---

## 📁 Navigasi Repositori

Seluruh aset proyek telah dikelompokkan agar mudah ditelusuri. Silakan klik direktori di bawah ini untuk melihat detail masing-masing bagian:

*   📄 **[Proposal & Dokumentasi](./Proposal)** — Berisi file Proposal PBL, perancangan sistem, dan laporan akhir.
*   🎨 **[UI/UX & Desain](./UI-UX)** — Berisi tautan Figma, *wireframe*, dan aset visual antarmuka pengguna.
*   🗄️ **[Database](./Database)** — Berisi rancangan ERD, skema tabel, dan file *export* `.sql` untuk *database*.
*   💻 **[Source Code](./Source-Code)** — Berisi kumpulan *codingan* aplikasi web secara keseluruhan (Frontend & Backend).

---

## 🎯 Tujuan Proyek

Membangun **GOWES** sebagai platform peminjaman sepeda mandiri melalui pemindaian QR yang:

*   mencatat seluruh transaksi peminjaman secara otomatis,
*   menerapkan aturan durasi dan sanksi secara konsisten,
*   membatasi durasi peminjaman maksimal 3 jam dan tidak melewati jam tutup,
*   menyimpan laporan kondisi sepeda di dalam sistem,
*   serta menyediakan pemantauan ketersediaan sepeda di semua shelter.

---

## 👥 Fitur & Hak Akses Utama

Sistem memiliki **empat peran**: Peminjam, Petugas, Admin, dan Super Admin.

<details>
<summary><strong>🎓 Peminjam (Mahasiswa / Pegawai)</strong></summary>
<br>

- **Pemindaian QR & Autentikasi:** Memindai QR pada sepeda/slot, memilih jenis peminjam (Mahasiswa atau Pegawai), lalu login menggunakan NIM/NIP dan password.
- **Validasi Kelayakan:** Sistem memeriksa status sanksi, jam operasional, dan status sepeda. Peminjaman ditolak jika peminjam sedang disanksi, di luar jam operasional, atau sepeda tidak tersedia.
- **Pemilihan Durasi:** Opsi durasi 1 jam, 2 jam, atau maksimal 3 jam, dengan penyesuaian agar tidak melewati jam tutup 15.30 WIB.
- **Tiket Digital & Countdown:** Tiket berisi ID sepeda, lokasi shelter, batas waktu kembali, dan hitung mundur sisa waktu. Status sepeda berubah menjadi tidak tersedia.
- **Pengembalian & Laporan Kondisi:** Peminjam mengonfirmasi pengembalian dan mengisi laporan kondisi sepeda di dalam sistem (berupa isian teks atau ceklis).
- **Riwayat & Sanksi:** Melihat riwayat peminjaman dan status sanksi.
</details>

<details>
<summary><strong>🛡️ Petugas (Operasional Shelter)</strong></summary>
<br>

- **Terikat pada Satu Shelter:** Petugas bertugas di shelter yang ditetapkan.
- **Verifikasi Tiket Digital:** Memverifikasi tiket digital sebelum kunci diserahkan.
- **Konfirmasi Serah Kunci:** Mengonfirmasi kunci keluar dan kunci masuk.
- **Daftar Peminjam Aktif:** Melihat daftar peminjam aktif di shelter-nya saja, termasuk unit, durasi, dan estimasi waktu pengembalian.
- **Waktu Pengembalian Resmi:** Waktu pengembalian resmi dihitung saat Petugas mengonfirmasi kunci masuk.
</details>

<details>
<summary><strong>🧑‍💼 Admin (Kebijakan & Monitoring)</strong></summary>
<br>

- **Manajemen Unit Sepeda:** Menambah, mengubah, menghapus sepeda, mengubah status tersedia/dipinjam/rusak/maintenance, dan penempatan ke shelter.
- **Pengaturan Parameter Kebijakan:** Mengatur durasi maksimal, jam operasional, dan aturan sanksi.
- **Dashboard Semua Shelter:** Memantau ketersediaan sepeda di seluruh shelter.
- **Rekap Laporan Kondisi:** Melihat data laporan kondisi sepeda dari Peminjam untuk kebutuhan pemeliharaan.
- **Keputusan Banding:** Memutuskan banding atau pencabutan sanksi.
- **Laporan Bulanan:** Mengunduh laporan bulanan tanpa rekap manual.
</details>

<details>
<summary><strong>⚙️ Super Admin (Pengelolaan Sistem)</strong></summary>
<br>

- **Pengelolaan Akun:** Membuat, menonaktifkan, dan mereset password akun Admin dan Petugas.
- **Pengelolaan Shelter & QR Code:** Mengelola data shelter dan QR code.
- **Audit Trail & Backup:** Melihat log aktivitas penting dan backup data. Audit trail dan backup berstatus prioritas tambahan dan dikerjakan bila waktu pengembangan mencukupi.
- **Tidak Memproses Peminjaman Harian:** Super Admin tidak ikut memproses peminjaman sehari-hari.
</details>

---

## 🔄 Alur Peminjaman Baru

1.  **Scan dan Login** — Peminjam memindai QR pada sepeda/slot, memilih jenis peminjam (Mahasiswa/Pegawai), lalu masuk dengan NIM/NIP dan password.
2.  **Pengecekan Otomatis** — Sistem memeriksa status sanksi, jam operasional, dan status sepeda. Jika peminjam disanksi, di luar jam operasional, atau sepeda tidak tersedia, peminjaman ditolak.
3.  **Pilih Durasi** — Peminjam memilih durasi 1 jam, 2 jam, atau maksimal 3 jam yang tidak melewati jam tutup 15.30 WIB.
4.  **Tiket Digital** — Sistem menampilkan tiket berisi ID sepeda, lokasi shelter, batas waktu kembali, dan hitung mundur. Status sepeda menjadi tidak tersedia.
5.  **Verifikasi dan Serah Kunci** — Petugas memverifikasi tiket digital, lalu menyerahkan kunci.
6.  **Pengembalian** — Peminjam memarkirkan sepeda di shelter, menekan tombol konfirmasi pengembalian, dan mengisi laporan kondisi sepeda di sistem.
7.  **Konfirmasi Petugas** — Peminjam mengembalikan kunci. Petugas mengonfirmasi kunci masuk. Waktu pengembalian resmi dihitung saat Petugas mengonfirmasi kunci masuk.
8.  **Sanksi Otomatis** — Jika pengembalian melewati batas, sistem mencatat sanksi sesuai tingkat teguran. Admin dapat memutuskan banding atau pencabutan sanksi.

---

## ⚖️ Aturan Peminjaman & Sanksi

| Aturan | Nilai Awal |
| :--- | :--- |
| Durasi peminjaman | Pilihan 1 jam, 2 jam, maksimal 3 jam, tidak melewati jam tutup |
| Jam operasional | Senin sampai Jumat, 08.00 sampai 15.30 WIB |
| Lokasi | Shelter 1: Pos Satpam Gedung Direktorat AA; Shelter 2: Pos Satpam Depan Gedung Teknik Sipil |
| Sanksi keterlambatan | Teguran 1: suspensi 3 hari. Teguran 2: suspensi 1 minggu (7 hari). Teguran 3: suspensi permanen |
| Pihak yang tunduk pada aturan | Mahasiswa (NIM) dan Pegawai (NIP) |
| QR Code | Satu QR per sepeda |

---

## 📌 Ruang Lingkup dan Batasan

### Yang Dikerjakan

- Login Peminjam (NIM/NIP) dan login pengelola sesuai peran.
- Scan QR per sepeda/slot, pemilihan durasi, tiket digital, dan hitung mundur.
- Verifikasi dan serah kunci oleh Petugas, pengembalian, serta pembaruan status sepeda.
- Laporan kondisi sepeda yang diisi Peminjam langsung di dalam sistem.
- Sanksi bertingkat otomatis dan keputusan banding oleh Admin.
- Manajemen Unit Sepeda, parameter kebijakan, dashboard, dan laporan bulanan.
- Pengelolaan akun, shelter, dan QR oleh Super Admin.

### Yang Tidak Dikerjakan

- Unggah foto kondisi sepeda (laporan berupa isian teks atau ceklis).
- Perangkat pelacak GPS atau kunci pintar (IoT) pada sepeda.
- Notifikasi pengingat melalui WhatsApp atau email.
- Integrasi dengan database akademik atau SSO kampus.
- Riwayat perawatan sepeda sebagai fitur terpisah.

### Asumsi dan Batasan

- Peminjam memiliki ponsel dengan kamera berfungsi dan koneksi internet di lokasi.
- Petugas dan Admin menggunakan GOWES sebagai satu-satunya sistem pencatatan peminjaman.
- Proyek dikerjakan dalam 16 minggu dengan teknologi PostgreSQL, PHP, HTML, dan JavaScript.
- Karena belum terhubung dengan database kampus, uji coba memakai data pengguna tiruan atau data teman sekelas, dan data unit sepeda yang sudah diperoleh. Data resmi akan digunakan setelah dikonfirmasi.
- Aplikasi di-deploy pada layanan hosting gratis.

---

## 🧰 Teknologi

- **Database:** PostgreSQL
- **Backend:** PHP
- **Frontend:** HTML, JavaScript
- **Desain:** Figma
- **Deployment:** Hosting gratis

---

## 🗓️ Rencana Pelaksanaan Ringkas

| Minggu | Kegiatan |
| :--- | :--- |
| 1–8 | Analisis kebutuhan, wawancara, proposal awal, ERD, desain Figma awal, Milestone 1 |
| 9 | Finalisasi desain tampilan (UI/UX dan frontend) |
| 10 | Penyempurnaan struktur dan relasi database |
| 11–12 | Backend alur inti (login, scan QR, peminjaman, pengembalian, konfirmasi Petugas), integrasi database, uji coba alur inti, Milestone 2 |
| 13–14 | Sanksi otomatis, banding, dashboard lengkap, fitur Super Admin, audit trail dan backup bila waktu cukup, integrasi dan pengujian |
| 15–16 | Perbaikan, pengujian akhir bersama Petugas, dokumentasi, demo dan serah terima, Final |

---

## 💻 Tim Pengembang (PBL SIB-2A)

| Nama | NIM | Peran Utama |
| :--- | :--- | :--- |
| **Farrell Raissa Ermanto** | `254107060150` | Ketua Tim dan Pengembang Utama |
| **Callista Dinar Kushermyla** | `254107060087` | UI/UX |
| **Luthfiyanna Nuha Syahada** | `254107060077` | Database |
| **Muhammad Fakhri Al Fawwaz** | `254107060099` | Frontend |
| **Reffi Dwino Igmaiyoda** | `254107060003` | Backend |

Catatan: Farrell dan Callista juga menangani analisis kebutuhan, alur, wireframe, dan desain antarmuka. Seluruh anggota terlibat dalam integrasi, pengujian, dan dokumentasi.

<br>

**Mata Kuliah Terintegrasi:**
*   **Pemrograman Web** — Dimas Wahyu Wibowo, S.T., M.T.
*   **Basis Data Lanjut** — Moch. Zawaruddin Abdullah, S.ST., M.Kom.
*   **UI/UX** — Anugrah Nur Rahmanto, Sn., M.Ds.

---

## 📞 0823-3420-6401

**Farrell Raissa Ermanto** — Ketua Tim GOWES
