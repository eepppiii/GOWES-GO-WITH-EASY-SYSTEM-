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

-- 5. pilih durasi
insert into peminjaman (id_sepeda, id_peminjam, waktu_mulai, durasi_pinjam)
values ($1, $2, $3, $4)
returning id_peminjaman, batas_kembali;

-- 6. Cek status suspend (dari tabel sanksi, kalau ada sanksi aktif hari ini)
select COUNT(*) as jumlah_sanksi_aktif
from sanksi
where id_peminjam = $1
  and CURRENT_DATE between tgl_mulai and tgl_selesai;

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

-- 8. peminjaman saya
create view view_peminjaman_aktif as
select 
    p.id_peminjaman,
    p.id_peminjam,
    sp.kode_qr,
    s.nama_shelter,
    p.waktu_mulai,
    p.batas_kembali
from peminjaman p
join sepeda sp on p.id_sepeda = sp.id_sepeda
join shelter s on sp.id_shelter = s.id_shelter
where p.waktu_kembali is null;

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

-- 13. status sanksi
create view view_status_sanksi as
select 
    pm.id_peminjam,
    (select COUNT(*) from peminjaman where id_peminjam = pm.id_peminjam AND status_keterlambatan = 'Terlambat') AS jumlah_keterlambatan,
    (select COUNT(*) from sanksi where id_peminjam = pm.id_peminjam AND CURRENT_DATE BETWEEN tgl_mulai AND tgl_selesai) AS sedang_suspen,
    (select tgl_mulai from sanksi where id_peminjam = pm.id_peminjam ORDER BY tgl_mulai DESC LIMIT 1) AS suspen_terakhir_mulai,
    (select tgl_selesai from sanksi where id_peminjam = pm.id_peminjam ORDER BY tgl_mulai DESC LIMIT 1) AS suspen_terakhir_selesai
from peminjam pm;

-- 14. profil peminjam
create view view_profil_peminjam as
select 
    id_peminjam,
    nama,
    no_identitas,
    jenis_peminjam
from peminjam;

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
     ORDER BY lk.id_laporankondisi DESC LIMIT 1) AS kondisi_terakhir
FROM sepeda sp
JOIN shelter s ON sp.id_shelter = s.id_shelter;

-- 4. peminjaman aktif
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
-- search di php

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
-- search di php

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
-- search di php

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