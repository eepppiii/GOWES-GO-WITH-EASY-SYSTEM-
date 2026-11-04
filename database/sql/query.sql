-- QUERY TAMPILAN USER
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
CREATE VIEW view_profil_peminjam AS
SELECT 
    id_peminjam,
    nama,
    no_identitas,
    jenis_peminjam
FROM peminjam;