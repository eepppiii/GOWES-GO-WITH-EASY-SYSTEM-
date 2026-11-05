-- 1. login | UNION
create view view_login as
select id_superadmin as id, username as identitas, password, 'superadmin' as role
from superadmin
union
select id_admin, username, password, 'admin'
from admin
union
select id_petugas, username, password, 'petugas'
from petugas
union
select id_peminjam, no_identitas, password, 'peminjam'
from peminjam;
--php
SELECT * FROM view_login WHERE identitas = $1;

-- QUERY TAMPILAN USER
-- 2. dashboard user (informasi shelter dan unit sepeda yang tersedia)
create view view_ketersediaan_shelter as
select 
    s.id_shelter,
    s.nama_shelter,
    s.lokasi,
    (select COUNT(*) from sepeda where id_shelter = s.id_shelter) as total_unit,
    (select COUNT(*) from sepeda where id_shelter = s.id_shelter and status_ketersediaan = 'Tersedia') as unit_tersedia
from shelter s;
--php
SELECT * FROM view_ketersediaan_shelter;

-- 4. hasil scan sepeda
create view view_detail_sepeda as
select 
    sp.id_sepeda,
    sp.kode_qr,
    sp.status_ketersediaan,
    s.id_shelter,
    s.nama_shelter,
    s.lokasi
from sepeda sp
join shelter s on sp.id_shelter = s.id_shelter;
--php
SELECT * FROM view_detail_sepeda WHERE kode_qr = $1;

-- 5. pilih durasi
insert into peminjaman (id_sepeda, id_peminjam, waktu_mulai, durasi_pinjam)
values ($1, $2, $3, $4)
returning id_peminjaman, batas_kembali;

-- 6. Cek status suspend (dari tabel sanksi, kalau ada sanksi aktif hari ini)
CREATE VIEW view_status_sanksi AS
SELECT 
    pm.id_peminjam,
    (SELECT COUNT(*) FROM peminjaman WHERE id_peminjam = pm.id_peminjam AND status_keterlambatan = 'Terlambat') AS jumlah_keterlambatan,
    (SELECT COUNT(*) FROM sanksi WHERE id_peminjam = pm.id_peminjam AND CURRENT_DATE BETWEEN tgl_mulai AND tgl_selesai) AS sedang_suspen,
    (SELECT tgl_mulai FROM sanksi WHERE id_peminjam = pm.id_peminjam ORDER BY tgl_mulai DESC LIMIT 1) AS suspen_terakhir_mulai,
    (SELECT tgl_selesai FROM sanksi WHERE id_peminjam = pm.id_peminjam ORDER BY tgl_mulai DESC LIMIT 1) AS suspen_terakhir_selesai
FROM peminjam pm;

select sedang_suspen
from view_status_sanksi
where id_peminjam = $1;

-- 7. Bukti peminjaman
create view view_bukti_peminjaman as
select 
    p.id_peminjaman,
    sp.id_sepeda,
    sp.kode_qr,
    s.nama_shelter,
    s.lokasi,
    pm.nama,
    pm.no_identitas,
    p.waktu_mulai,
    p.batas_kembali
from peminjaman p
join sepeda sp on p.id_sepeda = sp.id_sepeda
join shelter s on sp.id_shelter = s.id_shelter
join peminjam pm on p.id_peminjam = pm.id_peminjam;

SELECT * FROM view_bukti_peminjaman WHERE id_peminjaman = $1;

-- 8. peminjaman saya
CREATE VIEW view_peminjaman_aktif_admin AS
SELECT
    p.id_peminjaman,
    pm.no_identitas,
    sp.kode_qr,
    s.nama_shelter,
    p.waktu_mulai,
    p.batas_kembali,
    s.id_shelter
FROM peminjaman p
JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam
JOIN sepeda sp ON p.id_sepeda = sp.id_sepeda
JOIN shelter s ON sp.id_shelter = s.id_shelter
WHERE p.waktu_kembali IS NULL;
-- php
SELECT * FROM view_peminjaman_aktif WHERE id_peminjam = $1;

-- 9. update kembalikan go-bike
update peminjaman
set waktu_kembali = now()
where id_peminjaman = $1;

-- update sepeda tersedia
update sepeda
set status_ketersediaan = 'Tersedia'
where id_sepeda = (
    select id_sepeda from peminjaman where id_peminjaman = $1
);

-- 10. pengembalian berhasil
create view view_hasil_pengembalian as
select 
    p.id_peminjaman,
    sp.kode_qr,
    p.waktu_kembali,
    sp.status_ketersediaan
from peminjaman p
join sepeda sp on p.id_sepeda = sp.id_sepeda;
--php
SELECT * FROM view_hasil_pengembalian WHERE id_peminjaman = $1;

-- 11. laporan kondisi (input nilai)
insert into laporan_kondisi (id_peminjaman, nilai_ban, nilai_rem, nilai_rantai, nilai_rangka_bodi, keterangan, tgl_lapor)
values ($1, $2, $3, $4, $5, $6, CURRENT_DATE);

-- 12. riwayat
create view view_riwayat_peminjaman as
select 
    p.id_peminjaman,
    p.id_peminjam,
    sp.kode_qr,
    s.nama_shelter,
    p.waktu_mulai,
    p.durasi_jam,
    p.waktu_kembali,
    p.status_keterlambatan
from peminjaman p
join sepeda sp on p.id_sepeda = sp.id_sepeda
join shelter s on sp.id_shelter = s.id_shelter
where p.waktu_kembali is not null;
-- php
SELECT * FROM view_riwayat_peminjaman WHERE id_peminjam = $1 ORDER BY waktu_mulai desc;

-- 13. status sanksi
create view view_status_sanksi as
select 
    pm.id_peminjam,
    (select COUNT(*) from peminjaman where id_peminjam = pm.id_peminjam AND status_keterlambatan = 'Terlambat') AS jumlah_keterlambatan,
    (select COUNT(*) from sanksi where id_peminjam = pm.id_peminjam AND CURRENT_DATE BETWEEN tgl_mulai AND tgl_selesai) AS sedang_suspen,
    (select tgl_mulai from sanksi where id_peminjam = pm.id_peminjam ORDER BY tgl_mulai DESC LIMIT 1) AS suspen_terakhir_mulai,
    (select tgl_selesai from sanksi where id_peminjam = pm.id_peminjam ORDER BY tgl_mulai DESC LIMIT 1) AS suspen_terakhir_selesai
from peminjam pm;
-- php
SELECT * FROM view_status_sanksi WHERE id_peminjam = $1

-- 14. profil peminjam
create view view_profil_peminjam as
select 
    id_peminjam,
    nama,
    no_identitas,
from peminjam;
-- php
SELECT * FROM view_profil_peminjam WHERE id_peminjam = $1;


-- TAMPILAN SUPERADMIN
--2. dashboard superadmin 
-- 6 kartu ringkasan
create view view_ringkasan_superadmin as
select
    (select count(*) from sepeda where status_ketersediaan = 'Tersedia') AS unit_tersedia,
    (select count(*) from sepeda where status_ketersediaan <> 'Tersedia') AS unit_tidak_tersedia,
    (select count(*) from peminjaman where waktu_kembali IS NULL AND batas_kembali < NOW()) AS pengembalian_terlambat,
    (select count(distinct id_peminjam) from sanksi WHERE CURRENT_DATE BETWEEN tgl_mulai AND tgl_selesai) AS suspen_aktif,
    (select count(*) from petugas) as petugas_aktif,
    (select count(*) from admin) as admin_aktif;
-- php
SELECT * FROM view_ringkasan_superadmin;
--(shelter pakai view_ketersediaan_shelter)
-- tabel peminjaman aktif
create view view_peminjaman_aktif_admin as
select
    p.id_peminjaman,
    pm.no_identitas,
    sp.kode_qr,
    s.nama_shelter,
    p.waktu_mulai,
    p.batas_kembali
from peminjaman p
join peminjam pm on p.id_peminjam = pm.id_peminjam
join sepeda sp on p.id_sepeda = sp.id_sepeda
join shelter s on sp.id_shelter = s.id_shelter
where p.waktu_kembali is null;
-- php
SELECT * FROM view_peminjaman_aktif_admin ORDER BY batas_kembali asc;

-- 3. unit go-bike
CREATE VIEW view_unit_gobike AS
SELECT 
    sp.id_sepeda,
    sp.kode_qr,
    s.nama_shelter,
    sp.status_ketersediaan,
    (SELECT lk.keterangan 
     FROM laporan_kondisi lk 
     JOIN peminjaman p ON lk.id_peminjaman = p.id_peminjaman 
     WHERE p.id_sepeda = sp.id_sepeda 
     ORDER BY lk.id_laporankondisi DESC LIMIT 1) AS kondisi_terakhir,
    s.lokasi,
    sp.catatan_perawatan
FROM sepeda sp
JOIN shelter s ON sp.id_shelter = s.id_shelter;
-- php
SELECT * FROM view_unit_gobike ORDER BY kode_qr;

-- 4. peminjaman aktif
--php
SELECT * FROM view_peminjaman_aktif_admin ORDER BY batas_kembali ASC;
-- tombol search di php
SELECT * FROM view_peminjaman_aktif_admin
WHERE no_identitas LIKE $1 OR kode_qr LIKE $1
ORDER BY batas_kembali ASC;

-- 5. Rekap Sanksi
CREATE VIEW view_sanksi_admin AS
SELECT
    sk.id_sanksi,
    pm.no_identitas,
    pm.nama,
    (SELECT COUNT(*) FROM peminjaman
     WHERE id_peminjam = pm.id_peminjam AND status_keterlambatan = 'Terlambat') AS jumlah_terlambat,
    sk.jenis_tanggungan,
    sk.tgl_mulai,
    sk.tgl_selesai
FROM sanksi sk
JOIN peminjam pm ON sk.id_peminjam = pm.id_peminjam;
--php
SELECT * FROM view_sanksi_admin ORDER BY tgl_mulai DESC;
-- search di php
SELECT * FROM view_sanksi_admin
WHERE no_identitas LIKE $1 OR nama LIKE $1
ORDER BY tgl_mulai DESC;

-- 6. Laporan Kondisi
CREATE VIEW view_laporan_kondisi AS
SELECT
    lk.id_laporankondisi,
    lk.tgl_lapor,
    sp.kode_qr,
    pm.no_identitas,
    lk.nilai_ban,
    lk.nilai_rem,
    lk.nilai_rantai,
    lk.nilai_rangka_bodi,
    LEAST(lk.nilai_ban, lk.nilai_rem, lk.nilai_rantai, lk.nilai_rangka_bodi) AS nilai_terendah,
    lk.keterangan,
    s.id_shelter
FROM laporan_kondisi lk
JOIN peminjaman p ON lk.id_peminjaman = p.id_peminjaman
JOIN sepeda sp ON p.id_sepeda = sp.id_sepeda
JOIN shelter s ON sp.id_shelter = s.id_shelter
JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam;
--php
SELECT * FROM view_laporan_kondisi ORDER BY tgl_lapor DESC, id_laporankondisi DESC;
-- search di php
SELECT * FROM view_laporan_kondisi
WHERE no_identitas ILIKE $1 OR kode_qr ILIKE $1
ORDER BY tgl_lapor DESC, id_laporankondisi DESC;

--7. Riwayat peminjaman
CREATE VIEW view_riwayat_semua_peminjaman AS
SELECT
    p.id_peminjaman,
    p.waktu_mulai,
    pm.no_identitas,
    sp.kode_qr,
    p.durasi_pinjam,
    p.batas_kembali,
    p.waktu_kembali,
    p.status_keterlambatan
FROM peminjaman p
JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam
JOIN sepeda sp ON p.id_sepeda = sp.id_sepeda;
-- php
SELECT * FROM view_riwayat_semua_peminjaman ORDER BY waktu_mulai DESC;
-- search di php
SELECT * FROM view_riwayat_semua_peminjaman
WHERE no_identitas LIKE $1 OR kode_qr LIKE $1
ORDER BY waktu_mulai DESC;

-- 8. view data admin
CREATE VIEW view_data_petugas AS
SELECT pt.id_petugas, s.nama_shelter, pt.username, pt.status_akun
FROM petugas pt
JOIN shelter s ON pt.id_shelter = s.id_shelter;
-- php
SELECT * FROM view_data_petugas ORDER BY nama_shelter;

CREATE VIEW view_data_admin AS
SELECT id_admin, username, status_akun
FROM admin;
--php
SELECT * FROM view_data_admin ORDER BY username;

-- search php
SELECT * FROM view_data_petugas
WHERE username LIKE $1 OR nama_shelter LIKE $1;

SELECT * FROM view_data_admin
WHERE username LIKE $1;

-- tombol add (insert) di php
INSERT INTO petugas (id_superadmin, id_shelter, username, password)
VALUES ($1, $2, $3, $4);

INSERT INTO admin (id_superadmin, username, password)
VALUES ($1, $2, $3);

-- 9. Tambah petugas/admin (php)
INSERT INTO petugas (id_superadmin, id_shelter, username, password)
VALUES ($1, $2, $3, $4);

INSERT INTO admin (id_superadmin, username, password)
VALUES ($1, $2, $3);


-- TAMPILAN ADMIN
--2. dashboard admin
-- 4 kartu statistik
CREATE VIEW view_ringkasan_admin AS
SELECT
    (SELECT COUNT(*) FROM sepeda WHERE status_ketersediaan = 'Tersedia') AS unit_tersedia,
    (SELECT COUNT(*) FROM peminjaman WHERE waktu_kembali IS NULL) AS sedang_dipinjam,
    (SELECT COUNT(*) FROM peminjaman WHERE waktu_kembali IS NULL AND batas_kembali < NOW()) AS terlambat,
    (SELECT COUNT(DISTINCT id_peminjam) FROM sanksi WHERE CURRENT_DATE BETWEEN tgl_mulai AND tgl_selesai) AS suspen_aktif;
-- php
SELECT * FROM view_ringkasan_admin;
-- shelter pakai view_ketersediaan_shelter 
SELECT * FROM view_ketersediaan_shelter;
-- tabel peminjaman aktif
CREATE VIEW view_peminjaman_aktif_admin AS
SELECT
    p.id_peminjaman,
    pm.no_identitas,
    sp.kode_qr,
    s.nama_shelter,
    p.waktu_mulai,
    p.batas_kembali
FROM peminjaman p
JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam
JOIN sepeda sp ON p.id_sepeda = sp.id_sepeda
JOIN shelter s ON sp.id_shelter = s.id_shelter
WHERE p.waktu_kembali IS NULL;
--php
SELECT * FROM view_peminjaman_aktif_admin ORDER BY batas_kembali ASC;
-- view ketersediaan sepeda per shelter
CREATE VIEW view_ketersediaan_shelter AS
SELECT 
    s.id_shelter, s.nama_shelter, s.lokasi,
    (SELECT COUNT(*) FROM sepeda WHERE id_shelter = s.id_shelter) AS total_unit,
    (SELECT COUNT(*) FROM sepeda WHERE id_shelter = s.id_shelter AND status_ketersediaan = 'Tersedia') AS unit_tersedia
FROM shelter s;
--php
SELECT * FROM view_ketersediaan_shelter;

-- 3. Unit go-bike (php)
SELECT * FROM view_unit_gobike ORDER BY kode_qr;

-- dropdown shelter tambah unit 
CREATE VIEW view_daftar_shelter AS
SELECT id_shelter, nama_shelter
FROM shelter;
-- php
SELECT * FROM view_daftar_shelter
-- tombol tambah unit (insert php)
INSERT INTO sepeda (id_shelter, id_admin, kode_qr, status_ketersediaan)
VALUES ($1, $2, $3, 'Tersedia');
-- tombol ubah unit (php)
SELECT * FROM view_unit_gobike WHERE id_sepeda = $1;
-- simpan tombol konfirmasi (php)
UPDATE sepeda
SET status_ketersediaan = $1,
    catatan_perawatan = $2
WHERE id_sepeda = $3 AND status_ketersediaan <> 'Dipinjam';

-- Tambah unit go bike
-- BLM FIX KARENA ADA MERK

-- 4. peminjaman aktif (php)
SELECT * FROM view_peminjaman_aktif_admin ORDER BY batas_kembali ASC;
-- kotak Verifikasi Bukti (php)
SELECT * FROM view_peminjaman_aktif_admin
WHERE no_identitas LIKE $1 OR kode_qr LIKE $1
ORDER BY batas_kembali ASC;

-- 5. sanksi (php)
SELECT * FROM view_sanksi_admin ORDER BY tgl_mulai DESC;
-- kotak search (php)
SELECT * FROM view_sanksi_admin
WHERE no_identitas LIKE $1 OR nama LIKE $1
ORDER BY tgl_mulai DESC;

-- 6. laporan kondisi (php)
SELECT * FROM view_laporan_kondisi
ORDER BY tgl_lapor DESC, id_laporankondisi DESC;
-- kotak search (php)
SELECT * FROM view_laporan_kondisi
WHERE no_identitas LIKE $1 OR kode_qr LIKE $1
ORDER BY tgl_lapor DESC, id_laporankondisi DESC;

-- 7. riwayat peminjaman (php)
SELECT * FROM view_riwayat_semua_peminjaman ORDER BY waktu_mulai DESC;
-- kotak search (php)
SELECT * FROM view_riwayat_semua_peminjaman
WHERE no_identitas LIKE $1 OR kode_qr LIKE $1
ORDER BY waktu_mulai DESC;