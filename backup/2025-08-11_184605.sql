DROP TABLE kata_kunci_kategori;

CREATE TABLE `kata_kunci_kategori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kategori_id` int(11) DEFAULT NULL,
  `keyword` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kategori_id` (`kategori_id`),
  CONSTRAINT `kata_kunci_kategori_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_surat` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO kata_kunci_kategori VALUES("86","17","menugaskan");
INSERT INTO kata_kunci_kategori VALUES("87","17","diberikan tugas");
INSERT INTO kata_kunci_kategori VALUES("88","17","melaksanakan tugas");
INSERT INTO kata_kunci_kategori VALUES("89","17","perintah");
INSERT INTO kata_kunci_kategori VALUES("93","19","edaran");
INSERT INTO kata_kunci_kategori VALUES("94","19","beredar");
INSERT INTO kata_kunci_kategori VALUES("95","16","mengundang");
INSERT INTO kata_kunci_kategori VALUES("96","16","undangan rapat");
INSERT INTO kata_kunci_kategori VALUES("97","16","seminar");
INSERT INTO kata_kunci_kategori VALUES("98","16","undangan");
INSERT INTO kata_kunci_kategori VALUES("104","18","laporan");
INSERT INTO kata_kunci_kategori VALUES("105","18","izin");
INSERT INTO kata_kunci_kategori VALUES("106","18","keterangan");
INSERT INTO kata_kunci_kategori VALUES("112","15","permohonan");
INSERT INTO kata_kunci_kategori VALUES("113","15","mohon");
INSERT INTO kata_kunci_kategori VALUES("114","15","dimohon");
INSERT INTO kata_kunci_kategori VALUES("115","15","mengajukan");



DROP TABLE kategori_surat;

CREATE TABLE `kategori_surat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO kategori_surat VALUES("15","Surat Permohonan");
INSERT INTO kategori_surat VALUES("16","Surat Undangan");
INSERT INTO kategori_surat VALUES("17","Surat Perintah Tugas");
INSERT INTO kategori_surat VALUES("18","Surat Keterangan (Ijin, laporan)");
INSERT INTO kategori_surat VALUES("19","Surat Edaran");



DROP TABLE tbl_disposisi;

CREATE TABLE `tbl_disposisi` (
  `id_disposisi` int(10) NOT NULL AUTO_INCREMENT,
  `tujuan` varchar(250) NOT NULL,
  `isi_disposisi` mediumtext NOT NULL,
  `sifat` varchar(100) NOT NULL,
  `batas_waktu` date NOT NULL,
  `catatan` varchar(250) NOT NULL,
  `id_surat` int(10) NOT NULL,
  `id_user` tinyint(2) NOT NULL,
  PRIMARY KEY (`id_disposisi`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;




DROP TABLE tbl_instansi;

CREATE TABLE `tbl_instansi` (
  `id_instansi` tinyint(1) NOT NULL,
  `institusi` varchar(150) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `status` varchar(150) NOT NULL,
  `alamat` varchar(150) NOT NULL,
  `kepsek` varchar(50) NOT NULL,
  `nip` varchar(25) NOT NULL,
  `website` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `logo` varchar(250) NOT NULL,
  `id_user` tinyint(2) NOT NULL,
  PRIMARY KEY (`id_instansi`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO tbl_instansi VALUES("1","PEMERINTAHAN KOTA TOMOHON","IMPLEMENTASI ALGORITMA RULE-BASED CLASSIFICATION","DAERAH TOMOHON","Telephone 08123456789 Kode Pos 95423 JL.RAYA TOMOHON KOTA TOMOHON","REVI RUNTU S.KOM","010302445361","https://www.instagram.com/reviruntu/","rafaelruntu25@gmail.com","download.jpeg","1");



DROP TABLE tbl_klasifikasi;

CREATE TABLE `tbl_klasifikasi` (
  `id_klasifikasi` int(5) NOT NULL AUTO_INCREMENT,
  `kode` varchar(30) NOT NULL,
  `nama` varchar(250) NOT NULL,
  `uraian` mediumtext NOT NULL,
  `id_user` tinyint(2) NOT NULL,
  PRIMARY KEY (`id_klasifikasi`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;




DROP TABLE tbl_sett;

CREATE TABLE `tbl_sett` (
  `id_sett` tinyint(1) NOT NULL,
  `surat_masuk` tinyint(2) NOT NULL,
  `surat_keluar` tinyint(2) NOT NULL,
  `referensi` tinyint(2) NOT NULL,
  `id_user` tinyint(2) NOT NULL,
  PRIMARY KEY (`id_sett`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO tbl_sett VALUES("1","100","10","10","1");



DROP TABLE tbl_surat_keluar;

CREATE TABLE `tbl_surat_keluar` (
  `id_surat` int(10) NOT NULL AUTO_INCREMENT,
  `no_agenda` int(10) NOT NULL,
  `tujuan` varchar(250) NOT NULL,
  `no_surat` varchar(50) NOT NULL,
  `isi` mediumtext NOT NULL,
  `kode` varchar(30) NOT NULL,
  `tgl_surat` date NOT NULL,
  `tgl_catat` date NOT NULL,
  `file` varchar(250) NOT NULL,
  `keterangan` varchar(250) NOT NULL,
  `kategori_surat` varchar(255) NOT NULL,
  `id_user` tinyint(2) NOT NULL,
  PRIMARY KEY (`id_surat`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO tbl_surat_keluar VALUES("28","1","Yth. Sdr. Yohanis Tumbel","123","surat tgs","","2025-06-18","2025-06-18","5407-Surat k_Perintah_Tugas.docx","","Surat Perintah Tugas","1");



DROP TABLE tbl_surat_masuk;

CREATE TABLE `tbl_surat_masuk` (
  `id_surat` int(10) NOT NULL AUTO_INCREMENT,
  `no_agenda` int(10) NOT NULL,
  `no_surat` varchar(50) NOT NULL,
  `asal_surat` varchar(250) NOT NULL,
  `isi` mediumtext NOT NULL,
  `kode` varchar(30) NOT NULL,
  `indeks` varchar(30) NOT NULL,
  `tgl_surat` date NOT NULL,
  `tgl_diterima` date NOT NULL,
  `file` varchar(250) NOT NULL,
  `keterangan` varchar(250) NOT NULL,
  `id_user` tinyint(2) NOT NULL,
  `kategori_surat` varchar(50) NOT NULL,
  PRIMARY KEY (`id_surat`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;




DROP TABLE tbl_user;

CREATE TABLE `tbl_user` (
  `id_user` tinyint(2) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `password` varchar(35) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `nip` varchar(25) NOT NULL,
  `admin` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO tbl_user VALUES("1","admin","21232f297a57a5a743894a0e4a801fc3","R.Runtu","-","1");
INSERT INTO tbl_user VALUES("6","REVII","c79e2c13e91998f5d5694f13b8e7b379","REVI","20210132","1");



