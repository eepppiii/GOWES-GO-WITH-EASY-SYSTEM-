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
INSERT INTO peminjaman (id_sepeda, id_peminjam, waktu_mulai, durasi_pinjam)
VALUES ($1, $2, $3, $4)
RETURNING id_peminjaman, batas_kembali;

-- 6. Cek status suspend (dari tabel sanksi, kalau ada sanksi aktif hari ini)
SELECT COUNT(*) AS jumlah_sanksi_aktif
FROM sanksi
WHERE id_peminjam = $1
  AND CURRENT_DATE BETWEEN tgl_mulai AND tgl_selesai;

-- 7. Bukti peminjaman
CREATE VIEW view_bukti_peminjaman AS
SELECT 
    p.id_peminjaman,
    sp.id_sepeda,
    sp.kode_qr,
    s.nama_shelter,
    s.lokasi,
    pm.nama,
    pm.no_identitas,
    p.waktu_mulai,
    p.batas_kembali
FROM peminjaman p
JOIN sepeda sp ON p.id_sepeda = sp.id_sepeda
JOIN shelter s ON sp.id_shelter = s.id_shelter
JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam;