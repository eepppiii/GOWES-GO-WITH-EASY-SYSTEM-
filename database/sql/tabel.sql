-- create database db_gowes;

-- Tabel Superadmin
create table superadmin ( 
	id_superadmin serial primary key,
	username varchar(30) not null unique,
	password varchar(255) not null
);

-- Tabel Admin
create table admin (
	id_admin serial primary key,
	id_superadmin int not null,
	username varchar(50) not null,
	password varchar(100) not null,
	
	foreign key(id_superadmin) references superadmin(id_superadmin)
);

-- Tabel Shelter
create table shelter (
	id_shelter serial primary key,
	id_superadmin int not null,
	nama_shelter varchar(50) not null,
	lokasi varchar(100) not null,
	
	foreign key (id_superadmin) references superadmin(id_superadmin)
);

-- Tabel Petugas
create table petugas(
	id_petugas serial primary key,
	id_superadmin int not null,
	id_shelter int not null unique,
	username varchar(30) not null unique,
	password varchar(255)not null,
	foreign key (id_superadmin) references superadmin(id_superadmin)
	foreign key (id_shelter) references shelter(id_shelter)
);

-- Tabel Sepeda
create table sepeda (
	id_sepeda serial primary key,
	id_shelter int not null,
	id_admin int not null,
	kode_qr varchar(100) not null,
	status_ketersediaan varchar(20) not null default 'Tersedia',
	
	foreign key(id_shelter) references shelter (id_shelter),
	foreign key(id_admin) references admin(id_admin)
);

-- Tabel Peminjam
create table peminjam(
	id_peminjam serial primary key,
	no_identitas varchar(20) not null unique,
	nama varchar(50) not null,
	password varchar(255) not null
);

-- Tabel peminjaman
create table peminjaman(
	id_peminjaman serial primary key,
	id_sepeda int not null,
	id_peminjam int not null,
	id_petugas int,
	waktu_mulai timestamp not null, 
	durasi_pinjam int not null,
	waktu_kembali timestamp,
	batas_kembali timestamp not null,
	status_keterlambatan varchar(20) not null default 'Tepat Waktu',
	
	foreign key(id_sepeda) references sepeda(id_sepeda),
	foreign key (id_peminjam) references peminjam(id_peminjam),
	foreign key(id_petugas) references petugas(id_petugas)
);

-- Tabel laporan_kondisi
create table laporan_kondisi(
	id_laporankondisi serial primary key,
	id_peminjaman int unique not null,
	nilai_ban int not null,
	nilai_rem int not null,
	nilai_rantai int not null,
	nilai_rangka_bodi int not null,
	keterangan varchar(255),
	tgl_lapor date not null,
	
	foreign key(id_peminjaman) references peminjaman(id_peminjaman),
);

-- Tabel sanksi
create table sanksi (
	id_sanksi serial primary key, 
	id_peminjam int not null,
	id_admin int not null,
	jenis_tanggungan varchar(50) not null,
	tgl_mulai date not null,
	tgl_selesai date not null,
	
	foreign key(id_peminjam) references peminjam(id_peminjam),
	foreign key(id_admin) references admin(id_admin)
);
