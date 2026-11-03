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