-- ====================================================================
-- SISTEM INFORMASI PERPUSTAKAAN SMP NEGERI 1 KOTA GAJAH
-- Skema Database Tingkat Lanjut (Advanced Database)
-- Memenuhi Standar Basis Data Lanjut:
--   1. Relasi Formal Foreign Key (id_user, id_buku, id_kategori, id_penerbit)
--   2. Integritas Referensial (ON UPDATE CASCADE, ON DELETE RESTRICT)
--   3. Indeks Optimasi Kueri (Performance Indexing)
--   4. Triggers Otomatisasi Stok Buku (Borrow, Return, Stock Validation)
--   5. Status Transaksi Realtime (Menunggu Konfirmasi, Sedang Dipinjam, Selesai)
--   6. Database Views (v_buku_detail, v_peminjaman_detail)
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `db_perpustakaan` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_perpustakaan`;

-- Disable Foreign Key checks for clean reconstruction
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- 1. Struktur Tabel `user` (Tabel 12 Master User & Anggota)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `kode_user` varchar(25) NOT NULL,
  `nis` char(25) NOT NULL DEFAULT '-',
  `fullname` varchar(125) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `kelas` varchar(50) NOT NULL DEFAULT '-',
  `alamat` varchar(225) NOT NULL DEFAULT '-',
  `no_whatsapp` varchar(25) NOT NULL DEFAULT '087767499895',
  `verif` varchar(50) NOT NULL DEFAULT 'Akun Terverifikasi',
  `role` varchar(50) NOT NULL DEFAULT 'Siswa',
  `jam_aktif` varchar(100) NOT NULL DEFAULT '07:30 - 15:30 WIB',
  `join_date` varchar(125) NOT NULL,
  `terakhir_login` varchar(125) NOT NULL DEFAULT '-',
  PRIMARY KEY (`id_user`),
  INDEX `idx_user_role` (`role`),
  INDEX `idx_user_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data Seed `user` (Lengkap untuk Demo Presentasi)
INSERT INTO `user` (`id_user`, `kode_user`, `nis`, `fullname`, `username`, `password`, `kelas`, `alamat`, `no_whatsapp`, `verif`, `role`, `jam_aktif`, `join_date`, `terakhir_login`) VALUES
(1, 'ADM001', '-', 'Administrator Utama', 'admin', 'admin', '-', 'SMP Negeri 1 Kota Gajah', '082385495700', 'Akun Terverifikasi', 'Administrator', '07:00 - 16:00 WIB', '2023-08-01', '26-08-2023 ( 10:09:39 )'),
(2, 'AP001', '1230064', 'Sidik Prastio Utomo', 'sidik', 'sidik', 'IX-A', 'Sumbersari Bantul, Kec. Metro Barat', '087767499895', 'Akun Terverifikasi', 'Siswa', '07:30 - 15:30 WIB', '2023-08-01', '26-08-2023 ( 11:15:20 )'),
(3, 'AP002', '1230065', 'Anisa Rahmawati', 'anisa', 'anisa', 'VIII-B', 'Jl. Merdeka No. 12, Sritejokencono, Kota Gajah', '087767499895', 'Akun Terverifikasi', 'Siswa', '07:30 - 15:30 WIB', '2023-08-05', '01-11-2023 ( 09:20:15 )'),
(4, 'AP003', '1230066', 'Bayu Pratama', 'bayu', 'bayu', 'VII-A', 'Desa Kotagajah Timur, Lampung Tengah', '087767499895', 'Akun Terverifikasi', 'Siswa', '07:30 - 15:30 WIB', '2023-08-10', '02-11-2023 ( 14:05:42 )'),
(5, 'AP004', '1230067', 'Dewi Lestari', 'dewi', 'dewi', 'IX-C', 'Kecamatan Seputih Raman, Lampung Tengah', '087767499895', 'Akun Terverifikasi', 'Siswa', '07:30 - 15:30 WIB', '2023-08-12', '03-11-2023 ( 08:40:11 )'),
(6, 'AP005', '1230068', 'Fajar Nugraha', 'fajar', 'fajar', 'VIII-A', 'Kecamatan Punggur, Lampung Tengah', '087767499895', 'Akun Terverifikasi', 'Siswa', '07:30 - 15:30 WIB', '2023-08-15', '03-11-2023 ( 13:12:00 )'),
(7, 'ADM002', '-', 'Ratna Sari Dewi, S.Pd.', 'ratna', 'ratna', '-', 'Jl. Mansyur No. 05 Kota Gajah', '082385495700', 'Akun Terverifikasi', 'Administrator', '07:00 - 16:00 WIB', '2023-08-01', '03-11-2023 ( 07:55:10 )');

-- --------------------------------------------------------
-- 2. Struktur Tabel `penerbit` (Tabel 13 Master Penerbit)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `penerbit`;
CREATE TABLE `penerbit` (
  `id_penerbit` int(11) NOT NULL AUTO_INCREMENT,
  `kode_penerbit` varchar(125) NOT NULL,
  `nama_penerbit` varchar(125) NOT NULL,
  `verif_penerbit` varchar(25) NOT NULL DEFAULT 'Penerbit Terverifikasi',
  PRIMARY KEY (`id_penerbit`),
  INDEX `idx_kode_penerbit` (`kode_penerbit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data Seed `penerbit`
INSERT INTO `penerbit` (`id_penerbit`, `kode_penerbit`, `nama_penerbit`, `verif_penerbit`) VALUES
(1, 'P001', 'Gramedia Pustaka Utama', 'Penerbit Terverifikasi'),
(2, 'P002', 'Mizan Pustaka', 'Penerbit Terverifikasi'),
(3, 'P003', 'Bentang Pustaka', 'Penerbit Terverifikasi'),
(4, 'P004', 'Erlangga', 'Penerbit Terverifikasi'),
(5, 'P005', 'Republika', 'Penerbit Terverifikasi');

-- --------------------------------------------------------
-- 3. Struktur Tabel `kategori` (Tabel 16 Master Kategori)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `kategori`;
CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL AUTO_INCREMENT,
  `kode_kategori` varchar(50) NOT NULL,
  `nama_kategori` varchar(125) NOT NULL,
  PRIMARY KEY (`id_kategori`),
  INDEX `idx_kode_kategori` (`kode_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data Seed `kategori`
INSERT INTO `kategori` (`id_kategori`, `kode_kategori`, `nama_kategori`) VALUES
(1, 'K001', 'Novel'),
(2, 'K002', 'Cergam'),
(3, 'K003', 'Ensiklopedi'),
(4, 'K004', 'Biografi'),
(5, 'K005', 'Catatan Harian'),
(6, 'K006', 'Karya Ilmiah'),
(7, 'K007', 'Tafsir'),
(8, 'K008', 'Panduan (how to)'),
(9, 'K009', 'Majalah');

-- --------------------------------------------------------
-- 3b. Struktur Tabel `pengarang` (Tabel Master Pengarang)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `pengarang`;
CREATE TABLE `pengarang` (
  `id_pengarang` int(11) NOT NULL AUTO_INCREMENT,
  `kode_pengarang` varchar(50) NOT NULL,
  `nama_pengarang` varchar(150) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Aktif',
  `biografi` text NULL,
  PRIMARY KEY (`id_pengarang`),
  UNIQUE KEY `idx_kode_pengarang` (`kode_pengarang`),
  INDEX `idx_nama_pengarang` (`nama_pengarang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data Seed `pengarang`
INSERT INTO `pengarang` (`id_pengarang`, `kode_pengarang`, `nama_pengarang`, `status`, `biografi`) VALUES
(1, 'AUT001', 'Andrea Hirata', 'Aktif', 'Novelis ternama Indonesia asal Belitung Timur, penulis tetralogi Laskar Pelangi yang mendunia.'),
(2, 'AUT002', 'A. Fuadi', 'Aktif', 'Novelis dan jurnalis Indonesia, penulis trilogi Negeri 5 Menara dan 131 Pintu Cahaya dari Timur.'),
(3, 'AUT003', 'Tere Liye', 'Aktif', 'Penulis produktif fiksi Indonesia, pencipta serial dunia paralel Bumi, Bulan, Matahari, dan novel Hujan.'),
(4, 'AUT004', 'Pramoedya Ananta Toer', 'Aktif', 'Sastrawan besar Indonesia, pengarang Tetralogi Buru yang diakui secara internasional.'),
(5, 'AUT005', 'Eka Kurniawan', 'Aktif', 'Penulis novel Cantik Itu Luka dan Lelaki Harimau, penerima penghargaan sastra internasional.'),
(6, 'AUT006', 'Almira Bastari', 'Aktif', 'Penulis novel populer Home Sweet Loan dan Resign! yang mengulas dinamika generasi muda.'),
(7, 'AUT007', 'Ika Natassa', 'Aktif', 'Penulis fiksi urban dan romansa metropolitan terkemuka, pengarang Heartbreak Motel dan Antologi Rasa.'),
(8, 'AUT008', 'A. Makmur Makka', 'Aktif', 'Penulis dan biografer terpercaya Presiden RI ke-3 Prof. Dr. Ing. B.J. Habibie.'),
(9, 'AUT009', 'Sukino', 'Aktif', 'Pakar dan pendidik matematika, penyusun buku teks dan panduan ujian matematika SMP.'),
(10, 'AUT010', 'Kak Dicky', 'Aktif', 'Kreator komik edukasi dan cerita anak nusantara bergambar.'),
(11, 'AUT011', 'Tim Redaksi Erlangga', 'Aktif', 'Tim kurasi editor dan akademisi penerbitan buku teks serta ensiklopedia ilmiah.'),
(12, 'AUT012', 'Dee Lestari', 'Aktif', 'Sastrawan dan musisi Indonesia, penulis serial Supernova, Perahu Kertas, dan Filosofi Kopi.'),
(13, 'AUT013', 'Habiburrahman El Shirazy', 'Aktif', 'Sastrawan religius Indonesia lulusan Al-Azhar Kairo, penulis Ayat-Ayat Cinta.'),
(14, 'AUT014', 'Leila S. Chudori', 'Aktif', 'Wartawan dan sastrawan pengarang Laut Bercerita dan Pulang.'),
(15, 'AUT015', 'Ahmad Tohari', 'Aktif', 'Sastrawan pengarang novel Ronggeng Dukuh Paruk dan trilogi Dukuh Paruk.');

-- --------------------------------------------------------
-- 4. Struktur Tabel `buku` (Tabel 15 Master Buku dengan FK)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `buku`;
CREATE TABLE `buku` (
  `id_buku` int(11) NOT NULL AUTO_INCREMENT,
  `id_kategori` int(11) NOT NULL,
  `id_penerbit` int(11) NOT NULL,
  `id_pengarang` int(11) NOT NULL,
  `judul_buku` varchar(125) NOT NULL,
  `kategori_buku` varchar(125) NOT NULL DEFAULT '',
  `penerbit_buku` varchar(125) NOT NULL DEFAULT '',
  `pengarang` varchar(125) NOT NULL,
  `tahun_terbit` varchar(125) NOT NULL,
  `isbn` varchar(50) NOT NULL DEFAULT '',
  `j_buku_baik` int(11) NOT NULL DEFAULT 0,
  `j_buku_rusak` int(11) NOT NULL DEFAULT 0,
  `j_buku_hilang` int(11) NOT NULL DEFAULT 0,
  `sinopsis` text DEFAULT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `harga` int(11) NOT NULL DEFAULT 65000,
  PRIMARY KEY (`id_buku`),
  INDEX `idx_buku_kategori` (`id_kategori`),
  INDEX `idx_buku_penerbit` (`id_penerbit`),
  INDEX `idx_buku_pengarang` (`id_pengarang`),
  INDEX `idx_buku_judul` (`judul_buku`),
  INDEX `idx_buku_isbn` (`isbn`),
  CONSTRAINT `fk_buku_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_buku_penerbit` FOREIGN KEY (`id_penerbit`) REFERENCES `penerbit` (`id_penerbit`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_buku_pengarang` FOREIGN KEY (`id_pengarang`) REFERENCES `pengarang` (`id_pengarang`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data Seed `buku` (Koleksi Beragam Kategori, Penerbit, & Pengarang dengan Sinopsis & Harga)
INSERT INTO `buku` (`id_buku`, `id_kategori`, `id_penerbit`, `id_pengarang`, `judul_buku`, `kategori_buku`, `penerbit_buku`, `pengarang`, `tahun_terbit`, `isbn`, `j_buku_baik`, `j_buku_rusak`, `j_buku_hilang`, `sinopsis`, `cover`, `harga`) VALUES
(1, 1, 1, 5, 'Cantik Itu Luka', 'Novel', 'Gramedia Pustaka Utama', 'Eka Kurniawan', '2002', '9786020312583', 38, 2, 0, 'Kisah magis dan berlatar sejarah tentang Dewi Ayu, seorang perempuan berparas jelita di masa kolonial Halimunda, yang bangkit dari kuburnya setelah dua puluh satu tahun mati. Melalui kisah hidupnya dan anak-anak perempuannya, novel ini menyajikan perpaduan epik antara realisme magis, luka sejarah Indonesia, dan kritik sosial yang mendalam.', NULL, 98000),
(2, 1, 1, 6, 'Home Sweet Loan', 'Novel', 'Gramedia Pustaka Utama', 'Almira Bastari', '2022', '9786020658049', 39, 1, 0, 'Menceritakan perjuangan Kaluna, seorang pekerja kantoran yang bermimpi memiliki rumah sendiri di tengah kerasnya tuntutan menjadi sandwich generation. Kisah realistis dan mengharukan tentang impian, pengorbanan finansial keluarga, persahabatan, dan perjuangan menemukan tempat yang benar-benar bisa disebut sebagai rumah.', NULL, 85000),
(3, 1, 1, 7, 'Heartbreak Motel', 'Novel', 'Gramedia Pustaka Utama', 'Ika Natassa', '2022', '9786020660424', 40, 0, 0, 'Mengikuti perjalanan batin Ava Alessandra, seorang aktris ternama yang melarikan diri ke sebuah motel terpencil seusai menyelesaikan peran akting yang melelahkan. Di sana ia mencari ketenangan dan jawaban atas kerapuhan hidup, trauma masa lalu, serta arti cinta sejati di balik sorotan gemerlap dunia panggung.', NULL, 99000),
(4, 1, 3, 1, 'Laskar Pelangi', 'Novel', 'Bentang Pustaka', 'Andrea Hirata', '2005', '9789793062792', 24, 1, 0, 'Kisah inspiratif tentang sepuluh anak laskar pelangi di Desa Gantung, Belitung Timur, yang menimba ilmu di sekolah Muhammadiyah yang serba terbatas. Di bawah bimbingan guru penuh dedikasi, Bu Muslimah dan Pak Harfan, mereka membuktikan bahwa kemiskinan dan keterbatasan bukanlah penghalang untuk bermimpi setinggi langit.', NULL, 89000),
(5, 1, 1, 4, 'Bumi Manusia', 'Novel', 'Gramedia Pustaka Utama', 'Pramoedya Ananta Toer', '2005', '9789799731234', 15, 0, 1, 'Roman sejarah berlatar akhir abad ke-19 tentang Minke, pemuda pribumi cerdas berpendidikan Belanda yang jatuh cinta pada Annelies Mellema, putri dari Nyai Ontosoroh. Kisah perjuangan martabat, keadilan hukum, dan perlawanan terhadap penindasan kolonialisme yang menjadi mahakarya sastra Indonesia.', NULL, 125000),
(6, 3, 4, 11, 'Ensiklopedia Sains & Teknologi Populer', 'Ensiklopedi', 'Erlangga', 'Tim Redaksi Erlangga', '2021', '9786024341123', 20, 0, 0, 'Buku referensi lengkap yang menyajikan berbagai konsep ilmiah, keajaiban alam semesta, fenomena fisika, biologi modern, hingga perkembangan kecerdasan buatan dan teknologi antariksa terbaru yang dikemas dengan bahasa visual dan penjelasan ringkas yang mudah dipahami siswa.', NULL, 75000),
(7, 4, 5, 8, 'Biografi B.J. Habibie: Guru Bangsa', 'Biografi', 'Republika', 'A. Makmur Makka', '2018', '9786020822123', 12, 1, 0, 'Perjalanan hidup Prof. Dr. Ing. B.J. Habibie, dari masa kecil di Parepare, perjuangan menuntut ilmu di Jerman hingga menjadi pakar aeronautika dunia, memimpin industri dirgantara nasional, menjabat Presiden RI ke-3, hingga menjadi teladan cinta sejati dan inspirator abadi generasi muda Indonesia.', NULL, 95000),
(8, 8, 4, 9, 'Panduan Sukses Ujian Matematika SMP', 'Panduan (how to)', 'Erlangga', 'Sukino', '2023', '9786024840019', 30, 0, 0, 'Buku panduan belajar komprehensif bagi siswa SMP yang memuat ringkasan materi esensial aljabar, geometri, statistika, dan peluang. Dilengkapi dengan ratusan contoh soal teruji, metode pengerjaan cepat dan terstruktur, serta pembahasan bertahap untuk meraih nilai ujian terbaik.', NULL, 75000),
(9, 2, 2, 10, 'Komik Edukasi Si Kancil Modern', 'Cergam', 'Mizan Pustaka', 'Kak Dicky', '2020', '9786027891234', 18, 2, 0, 'Kisah fabel nusantara Si Kancil yang diadaptasi secara modern dan jenaka dalam format komik bergambar penuh warna. Mengajarkan nilai-nilai kejujuran, kepedulian lingkungan, kerja sama tim, dan pemanfaatan akal budi untuk kebaikan dalam menghadapi berbagai petualangan seru di rimba.', NULL, 103000);

-- --------------------------------------------------------
-- 5. Struktur Tabel `peminjaman` (Tabel 14 Transaksi dengan FK & Realtime Status)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `peminjaman`;
CREATE TABLE `peminjaman` (
  `id_peminjaman` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_admin` int(11) NULL DEFAULT 1,
  `id_buku` int(11) NOT NULL,
  `nama_anggota` varchar(125) NOT NULL DEFAULT '',
  `judul_buku` varchar(125) NOT NULL DEFAULT '',
  `tanggal_peminjaman` varchar(100) NOT NULL,
  `tanggal_pengembalian` varchar(50) NOT NULL DEFAULT '',
  `status_transaksi` varchar(50) NOT NULL DEFAULT 'Sedang Dipinjam',
  `kondisi_buku_saat_dipinjam` varchar(125) NOT NULL DEFAULT 'Baik',
  `kondisi_buku_saat_dikembalikan` varchar(125) NOT NULL DEFAULT '',
  `denda` varchar(125) NOT NULL DEFAULT 'Tidak ada',
  PRIMARY KEY (`id_peminjaman`),
  INDEX `idx_pinjam_user` (`id_user`),
  INDEX `idx_pinjam_admin` (`id_admin`),
  INDEX `idx_pinjam_buku` (`id_buku`),
  INDEX `idx_pinjam_status` (`status_transaksi`),
  INDEX `idx_pinjam_tanggal` (`tanggal_peminjaman`, `tanggal_pengembalian`),
  CONSTRAINT `fk_pinjam_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_pinjam_admin` FOREIGN KEY (`id_admin`) REFERENCES `user` (`id_user`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_pinjam_buku` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data Seed `peminjaman` (Transaksi Sirkulasi Bulanan Lengkap)
INSERT INTO `peminjaman` (`id_peminjaman`, `id_user`, `id_admin`, `id_buku`, `nama_anggota`, `judul_buku`, `tanggal_peminjaman`, `tanggal_pengembalian`, `status_transaksi`, `kondisi_buku_saat_dipinjam`, `kondisi_buku_saat_dikembalikan`, `denda`) VALUES
(1, 2, 1, 1, 'sidik', 'Cantik Itu Luka', '10-01-2023', '17-01-2023', 'Selesai', 'Baik', 'Baik', 'Tidak ada'),
(2, 3, 7, 4, 'anisa', 'Laskar Pelangi', '14-02-2023', '21-02-2023', 'Selesai', 'Baik', 'Baik', 'Tidak ada'),
(3, 4, 1, 6, 'bayu', 'Ensiklopedia Sains & Teknologi Populer', '05-03-2023', '15-03-2023', 'Selesai', 'Baik', 'Baik', 'Rp 15.000 (Terlambat 3 Hari)'),
(4, 5, 7, 2, 'dewi', 'Home Sweet Loan', '12-04-2023', '19-04-2023', 'Selesai', 'Baik', 'Baik', 'Tidak ada'),
(5, 6, 1, 9, 'fajar', 'Komik Edukasi Si Kancil Modern', '02-05-2023', '09-05-2023', 'Selesai', 'Baik', 'Baik', 'Tidak ada'),
(6, 3, 7, 8, 'anisa', 'Panduan Sukses Ujian Matematika SMP', '18-05-2023', '25-05-2023', 'Selesai', 'Baik', 'Baik', 'Tidak ada'),
(7, 4, 1, 7, 'bayu', 'Biografi B.J. Habibie: Guru Bangsa', '10-06-2023', '17-06-2023', 'Selesai', 'Baik', 'Baik', 'Tidak ada'),
(8, 2, 7, 5, 'sidik', 'Bumi Manusia', '04-07-2023', '11-07-2023', 'Selesai', 'Baik', 'Baik', 'Tidak ada'),
(9, 5, 1, 3, 'dewi', 'Heartbreak Motel', '20-07-2023', '27-07-2023', 'Selesai', 'Baik', 'Baik', 'Tidak ada'),
(10, 2, 7, 1, 'sidik', 'Cantik Itu Luka', '01-08-2023', '08-08-2023', 'Selesai', 'Baik', 'Baik', 'Tidak ada'),
(11, 3, 1, 7, 'anisa', 'Biografi B.J. Habibie: Guru Bangsa', '20-08-2023', '29-08-2023', 'Selesai', 'Baik', 'Rusak', 'Rp 20.000 (Kerusakan Halaman)'),
(12, 4, 7, 6, 'bayu', 'Ensiklopedia Sains & Teknologi Populer', '22-08-2023', '31-08-2023', 'Selesai', 'Baik', 'Baik', 'Rp 5.000 (Terlambat 2 Hari)'),
(13, 5, 1, 2, 'dewi', 'Home Sweet Loan', '05-09-2023', '12-09-2023', 'Selesai', 'Baik', 'Baik', 'Tidak ada'),
(14, 6, 7, 9, 'fajar', 'Komik Edukasi Si Kancil Modern', '18-09-2023', '25-09-2023', 'Selesai', 'Baik', 'Baik', 'Tidak ada'),
(15, 3, 1, 5, 'anisa', 'Bumi Manusia', '08-10-2023', '22-10-2023', 'Selesai', 'Baik', 'Hilang', 'Rp 50.000 (Penggantian Buku Hilang)'),
(16, 4, 7, 8, 'bayu', 'Panduan Sukses Ujian Matematika SMP', '05-11-2023', '12-11-2023', 'Selesai', 'Baik', 'Baik', 'Tidak ada'),
(17, 2, 1, 4, 'sidik', 'Laskar Pelangi', '15-11-2023', '', 'Sedang Dipinjam', 'Baik', '', ''),
(18, 5, 7, 5, 'dewi', 'Bumi Manusia', '02-12-2023', '', 'Sedang Dipinjam', 'Baik', '', ''),
(19, 4, 1, 1, 'bayu', 'Cantik Itu Luka', '10-12-2023', '', 'Sedang Dipinjam', 'Baik', '', ''),
(20, 6, 7, 8, 'fajar', 'Panduan Sukses Ujian Matematika SMP', '15-12-2023', '', 'Sedang Dipinjam', 'Baik', '', '');

-- --------------------------------------------------------
-- 6. Struktur Tabel `identitas` (Tabel Master Identitas Sekolah)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `identitas`;
CREATE TABLE `identitas` (
  `id_identitas` int(11) NOT NULL AUTO_INCREMENT,
  `nama_app` varchar(50) NOT NULL,
  `alamat_app` text NOT NULL,
  `email_app` varchar(125) NOT NULL,
  `nomor_hp` char(50) NOT NULL,
  `latitude` varchar(50) DEFAULT '-4.9754',
  `longitude` varchar(50) DEFAULT '105.3188',
  PRIMARY KEY (`id_identitas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data Seed `identitas`
INSERT INTO `identitas` (`id_identitas`, `nama_app`, `alamat_app`, `email_app`, `nomor_hp`, `latitude`, `longitude`) VALUES
(1, 'Perpustakaan SMP Negeri 1 Kota Gajah', 'Jl. Mansyur No.02, Sritejokencono, Kec. Kota Gajah, Kab. Lampung Tengah Prov. Lampung', 'smpn1kotagajah@gmail.com', '082385495700', '-4.9754', '105.3188');

-- Enable Foreign Key checks
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- 7. Database Views (Abstraksi Kompatibilitas Query & Relasi)
-- --------------------------------------------------------
CREATE OR REPLACE VIEW `v_buku_detail` AS
SELECT 
    b.id_buku,
    b.id_kategori,
    b.id_penerbit,
    b.id_pengarang,
    b.judul_buku,
    k.nama_kategori AS kategori_buku,
    p.nama_penerbit AS penerbit_buku,
    b.pengarang,
    pg.nama_pengarang AS nama_pengarang_relasi,
    pg.kode_pengarang,
    b.tahun_terbit,
    b.isbn,
    b.j_buku_baik,
    b.j_buku_rusak,
    b.j_buku_hilang,
    b.sinopsis,
    b.cover,
    b.harga,
    (b.j_buku_baik + b.j_buku_rusak + b.j_buku_hilang) AS total_buku
FROM `buku` b
JOIN `kategori` k ON b.id_kategori = k.id_kategori
JOIN `penerbit` p ON b.id_penerbit = p.id_penerbit
LEFT JOIN `pengarang` pg ON b.id_pengarang = pg.id_pengarang;

CREATE OR REPLACE VIEW `v_peminjaman_detail` AS
SELECT 
    p.id_peminjaman,
    p.id_user,
    p.id_admin,
    p.id_buku,
    u.kode_user,
    u.nis,
    u.fullname,
    u.username AS nama_anggota,
    u.kelas,
    adm.fullname AS nama_petugas,
    b.judul_buku,
    b.pengarang,
    b.id_kategori,
    b.id_penerbit,
    b.id_pengarang,
    b.harga,
    k.nama_kategori AS kategori_buku,
    pen.nama_penerbit AS penerbit_buku,
    p.tanggal_peminjaman,
    p.tanggal_pengembalian,
    p.status_transaksi,
    p.kondisi_buku_saat_dipinjam,
    p.kondisi_buku_saat_dikembalikan,
    p.denda
FROM `peminjaman` p
JOIN `user` u ON p.id_user = u.id_user
JOIN `buku` b ON p.id_buku = b.id_buku
LEFT JOIN `user` adm ON p.id_admin = adm.id_user
LEFT JOIN `kategori` k ON b.id_kategori = k.id_kategori
LEFT JOIN `penerbit` pen ON b.id_penerbit = pen.id_penerbit;

-- --------------------------------------------------------
-- 8. Triggers Otomasi Mutasi Stok Buku (MariaDB / MySQL)
-- --------------------------------------------------------

DROP TRIGGER IF EXISTS `trg_sebelum_pinjam_cek_stok`;
DROP TRIGGER IF EXISTS `trg_setelah_pinjam_kurang_stok`;
DROP TRIGGER IF EXISTS `trg_setelah_kembali_tambah_stok`;
DROP TRIGGER IF EXISTS `trg_setelah_batal_pinjam`;

DELIMITER $$

-- Trigger 1: Validasi stok sebelum insert peminjaman baru
CREATE TRIGGER `trg_sebelum_pinjam_cek_stok`
BEFORE INSERT ON `peminjaman`
FOR EACH ROW
BEGIN
    DECLARE stok_baik INT DEFAULT 0;
    IF NEW.id_buku IS NOT NULL THEN
        SELECT j_buku_baik INTO stok_baik FROM `buku` WHERE `id_buku` = NEW.id_buku;
        IF stok_baik <= 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Gagal Meminjam: Stok fisik buku baik sedang kosong (0).';
        END IF;
    END IF;
END$$

-- Trigger 2: Pengurangan otomatis stok buku baik setelah peminjaman berhasil
CREATE TRIGGER `trg_setelah_pinjam_kurang_stok`
AFTER INSERT ON `peminjaman`
FOR EACH ROW
BEGIN
    IF NEW.id_buku IS NOT NULL THEN
        UPDATE `buku`
        SET `j_buku_baik` = GREATEST(0, `j_buku_baik` - 1)
        WHERE `id_buku` = NEW.id_buku;
    END IF;
END$$

-- Trigger 3: Penambahan otomatis stok buku saat transaksi pengembalian diselesaikan
CREATE TRIGGER `trg_setelah_kembali_tambah_stok`
AFTER UPDATE ON `peminjaman`
FOR EACH ROW
BEGIN
    -- Cek jika status berubah menjadi Selesai atau tanggal pengembalian baru saja diisi
    IF (OLD.tanggal_pengembalian IS NULL OR OLD.tanggal_pengembalian = '' OR OLD.tanggal_pengembalian = '0000-00-00')
       AND (NEW.tanggal_pengembalian IS NOT NULL AND NEW.tanggal_pengembalian != '' AND NEW.tanggal_pengembalian != '0000-00-00') THEN
        
        IF NEW.kondisi_buku_saat_dikembalikan = 'Rusak' THEN
            UPDATE `buku`
            SET `j_buku_rusak` = `j_buku_rusak` + 1
            WHERE `id_buku` = NEW.id_buku;
        ELSEIF NEW.kondisi_buku_saat_dikembalikan = 'Hilang' THEN
            UPDATE `buku`
            SET `j_buku_hilang` = `j_buku_hilang` + 1
            WHERE `id_buku` = NEW.id_buku;
        ELSE
            UPDATE `buku`
            SET `j_buku_baik` = `j_buku_baik` + 1
            WHERE `id_buku` = NEW.id_buku;
        END IF;
    END IF;
END$$

-- Trigger 4: Pengembalian stok buku jika catatan peminjaman aktif dibatalkan/dihapus
CREATE TRIGGER `trg_setelah_batal_pinjam`
AFTER DELETE ON `peminjaman`
FOR EACH ROW
BEGIN
    IF (OLD.tanggal_pengembalian IS NULL OR OLD.tanggal_pengembalian = '' OR OLD.tanggal_pengembalian = '0000-00-00') THEN
        UPDATE `buku`
        SET `j_buku_baik` = `j_buku_baik` + 1
        WHERE `id_buku` = OLD.id_buku;
    END IF;
END$$

DELIMITER ;
