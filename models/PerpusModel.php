<?php
// Main Data Model (Advanced Database Edition)
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
// Mendukung Relasi Foreign Key, Integritas Referensial, Index, View, dan Trigger

require_once __DIR__ . '/../core/Database.php';

class PerpusModel {
    private ?PDO $db;
    private ?string $lastError = null;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->checkAndMigrateColumns();
    }

    private function checkAndMigrateColumns(): void {
        try {
            if ($this->db) {
                // Migrasi status_transaksi pada peminjaman jika belum ada
                $stmt = $this->db->query("SHOW COLUMNS FROM `peminjaman` LIKE 'status_transaksi'");
                if (!$stmt->fetch()) {
                    $this->db->exec("ALTER TABLE `peminjaman` ADD COLUMN `status_transaksi` VARCHAR(50) NOT NULL DEFAULT 'Sedang Dipinjam' AFTER `tanggal_pengembalian`");
                    $this->db->exec("UPDATE `peminjaman` SET `status_transaksi` = 'Selesai' WHERE `tanggal_pengembalian` != '' AND `tanggal_pengembalian` IS NOT NULL AND `tanggal_pengembalian` != '0000-00-00'");
                }

                // Migrasi koordinat peta latitude & longitude pada identitas jika belum ada
                $stmtLat = $this->db->query("SHOW COLUMNS FROM `identitas` LIKE 'latitude'");
                if (!$stmtLat->fetch()) {
                    $this->db->exec("ALTER TABLE `identitas` ADD COLUMN `latitude` VARCHAR(50) NULL DEFAULT '-4.9754' AFTER `nomor_hp`, ADD COLUMN `longitude` VARCHAR(50) NULL DEFAULT '105.3188' AFTER `latitude`");
                }

                // Migrasi sinopsis buku jika belum ada
                $stmtSin = $this->db->query("SHOW COLUMNS FROM `buku` LIKE 'sinopsis'");
                if (!$stmtSin->fetch()) {
                    $this->db->exec("ALTER TABLE `buku` ADD COLUMN `sinopsis` TEXT NULL AFTER `j_buku_rusak`");
                    $synopses = [
                        1 => 'Kisah magis dan berlatar sejarah tentang Dewi Ayu, seorang perempuan berparas jelita di masa kolonial Halimunda, yang bangkit dari kuburnya setelah dua puluh satu tahun mati. Melalui kisah hidupnya dan anak-anak perempuannya, novel ini menyajikan perpaduan epik antara realisme magis, luka sejarah Indonesia, dan kritik sosial yang mendalam.',
                        2 => 'Menceritakan perjuangan Kaluna, seorang pekerja kantoran yang bermimpi memiliki rumah sendiri di tengah kerasnya tuntutan menjadi sandwich generation. Kisah realistis dan mengharukan tentang impian, pengorbanan finansial keluarga, persahabatan, dan perjuangan menemukan tempat yang benar-benar bisa disebut sebagai rumah.',
                        3 => 'Mengikuti perjalanan batin Ava Alessandra, seorang aktris ternama yang melarikan diri ke sebuah motel terpencil seusai menyelesaikan peran akting yang melelahkan. Di sana ia mencari ketenangan dan jawaban atas kerapuhan hidup, trauma masa lalu, serta arti cinta sejati di balik sorotan gemerlap dunia panggung.',
                        4 => 'Kisah inspiratif tentang sepuluh anak laskar pelangi di Desa Gantung, Belitung Timur, yang menimba ilmu di sekolah Muhammadiyah yang serba terbatas. Di bawah bimbingan guru penuh dedikasi, Bu Muslimah dan Pak Harfan, mereka membuktikan bahwa kemiskinan dan keterbatasan bukanlah penghalang untuk bermimpi setinggi langit.',
                        5 => 'Roman sejarah berlatar akhir abad ke-19 tentang Minke, pemuda pribumi cerdas berpendidikan Belanda yang jatuh cinta pada Annelies Mellema, putri dari Nyai Ontosoroh. Kisah perjuangan martabat, keadilan hukum, dan perlawanan terhadap penindasan kolonialisme yang menjadi mahakarya sastra Indonesia.',
                        6 => 'Buku referensi lengkap yang menyajikan berbagai konsep ilmiah, keajaiban alam semesta, fenomena fisika, biologi modern, hingga perkembangan kecerdasan buatan dan teknologi antariksa terbaru yang dikemas dengan bahasa visual dan penjelasan ringkas yang mudah dipahami siswa.',
                        7 => 'Perjalanan hidup Prof. Dr. Ing. B.J. Habibie, dari masa kecil di Parepare, perjuangan menuntut ilmu di Jerman hingga menjadi pakar aeronautika dunia, memimpin industri dirgantara nasional, menjabat Presiden RI ke-3, hingga menjadi teladan cinta sejati dan inspirator abadi generasi muda Indonesia.',
                        8 => 'Buku panduan belajar komprehensif bagi siswa SMP yang memuat ringkasan materi esensial aljabar, geometri, statistika, dan peluang. Dilengkapi dengan ratusan contoh soal teruji, metode pengerjaan cepat dan terstruktur, serta pembahasan bertahap untuk meraih nilai ujian terbaik.',
                        9 => 'Kisah fabel nusantara Si Kancil yang diadaptasi secara modern dan jenaka dalam format komik bergambar penuh warna. Mengajarkan nilai-nilai kejujuran, kepedulian lingkungan, kerja sama tim, dan pemanfaatan akal budi untuk kebaikan dalam menghadapi berbagai petualangan seru di rimba.'
                    ];
                    $stmtUp = $this->db->prepare("UPDATE `buku` SET `sinopsis` = ? WHERE `id_buku` = ? AND (`sinopsis` IS NULL OR `sinopsis` = '')");
                    foreach ($synopses as $idB => $syn) {
                        $stmtUp->execute([$syn, $idB]);
                    }
                }

                // Migrasi j_buku_hilang pada buku jika belum ada
                $stmtHilang = $this->db->query("SHOW COLUMNS FROM `buku` LIKE 'j_buku_hilang'");
                if (!$stmtHilang->fetch()) {
                    $this->db->exec("ALTER TABLE `buku` ADD COLUMN `j_buku_hilang` INT(11) NOT NULL DEFAULT 0 AFTER `j_buku_rusak`");
                }

                // Migrasi kolom harga pada buku jika belum ada
                $stmtHarga = $this->db->query("SHOW COLUMNS FROM `buku` LIKE 'harga'");
                if (!$stmtHarga->fetch()) {
                    $this->db->exec("ALTER TABLE `buku` ADD COLUMN `harga` INT(11) NOT NULL DEFAULT 65000 AFTER `sinopsis`");
                    $prices = [
                        1 => 98000,
                        2 => 85000,
                        3 => 99000,
                        4 => 89000,
                        5 => 125000,
                        6 => 75000,
                        7 => 95000,
                        8 => 75000,
                        9 => 103000,
                        12 => 68000,
                        15 => 88000
                    ];
                    $stmtUpH = $this->db->prepare("UPDATE `buku` SET `harga` = ? WHERE `id_buku` = ?");
                    foreach ($prices as $idB => $h) {
                        $stmtUpH->execute([$h, $idB]);
                    }
                }

                // Migrasi no_whatsapp pada user jika belum ada
                $stmtWa = $this->db->query("SHOW COLUMNS FROM `user` LIKE 'no_whatsapp'");
                if (!$stmtWa->fetch()) {
                    $this->db->exec("ALTER TABLE `user` ADD COLUMN `no_whatsapp` VARCHAR(25) NOT NULL DEFAULT '087767499895' AFTER `alamat`");
                    $this->db->exec("UPDATE `user` SET `no_whatsapp` = '082385495700' WHERE `role` = 'Administrator'");
                    $this->db->exec("UPDATE `user` SET `no_whatsapp` = '087767499895' WHERE `role` = 'Siswa'");
                }

                // Update nomor admin di identitas
                $this->db->exec("UPDATE `identitas` SET `nomor_hp` = '082385495700' WHERE `id_identitas` = 1 AND (`nomor_hp` LIKE '0725%' OR `nomor_hp` = '' OR `nomor_hp` = '085381768123')");

                // Migrasi tabel pengarang jika belum ada
                $stmtPengarang = $this->db->query("SHOW TABLES LIKE 'pengarang'");
                if (!$stmtPengarang->fetch()) {
                    $this->db->exec("CREATE TABLE IF NOT EXISTS `pengarang` (
                      `id_pengarang` int(11) NOT NULL AUTO_INCREMENT,
                      `kode_pengarang` varchar(50) NOT NULL,
                      `nama_pengarang` varchar(150) NOT NULL,
                      `status` varchar(50) NOT NULL DEFAULT 'Aktif',
                      `biografi` text NULL,
                      PRIMARY KEY (`id_pengarang`),
                      UNIQUE KEY `idx_kode_pengarang` (`kode_pengarang`),
                      INDEX `idx_nama_pengarang` (`nama_pengarang`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

                    $seedAuthors = [
                        ['AUT001', 'Andrea Hirata', 'Aktif', 'Novelis ternama Indonesia asal Belitung Timur, penulis tetralogi Laskar Pelangi yang mendunia.'],
                        ['AUT002', 'A. Fuadi', 'Aktif', 'Novelis dan jurnalis Indonesia, penulis trilogi Negeri 5 Menara dan 131 Pintu Cahaya dari Timur.'],
                        ['AUT003', 'Tere Liye', 'Aktif', 'Penulis produktif fiksi Indonesia, pencipta serial dunia paralel Bumi, Bulan, Matahari, dan novel Hujan.'],
                        ['AUT004', 'Pramoedya Ananta Toer', 'Aktif', 'Sastrawan besar Indonesia, pengarang Tetralogi Buru yang diakui secara internasional.'],
                        ['AUT005', 'Eka Kurniawan', 'Aktif', 'Penulis novel Cantik Itu Luka dan Lelaki Harimau, penerima penghargaan sastra internasional.'],
                        ['AUT006', 'Almira Bastari', 'Aktif', 'Penulis novel populer Home Sweet Loan dan Resign! yang mengulas dinamika generasi muda.'],
                        ['AUT007', 'Ika Natassa', 'Aktif', 'Penulis fiksi urban dan romansa metropolitan terkemuka, pengarang Heartbreak Motel dan Antologi Rasa.'],
                        ['AUT008', 'A. Makmur Makka', 'Aktif', 'Penulis dan biografer terpercaya Presiden RI ke-3 Prof. Dr. Ing. B.J. Habibie.'],
                        ['AUT009', 'Sukino', 'Aktif', 'Pakar dan pendidik matematika, penyusun buku teks dan panduan ujian matematika SMP.'],
                        ['AUT010', 'Kak Dicky', 'Aktif', 'Kreator komik edukasi dan cerita anak nusantara bergambar.'],
                        ['AUT011', 'Tim Redaksi Erlangga', 'Aktif', 'Tim kurasi editor dan akademisi penerbitan buku teks serta ensiklopedia ilmiah.'],
                        ['AUT012', 'Dee Lestari', 'Aktif', 'Sastrawan dan musisi Indonesia, penulis serial Supernova, Perahu Kertas, dan Filosofi Kopi.'],
                        ['AUT013', 'Habiburrahman El Shirazy', 'Aktif', 'Sastrawan religius Indonesia lulusan Al-Azhar Kairo, penulis Ayat-Ayat Cinta.'],
                        ['AUT014', 'Leila S. Chudori', 'Aktif', 'Wartawan dan sastrawan pengarang Laut Bercerita dan Pulang.'],
                        ['AUT015', 'Ahmad Tohari', 'Aktif', 'Sastrawan pengarang novel Ronggeng Dukuh Paruk dan trilogi Dukuh Paruk.']
                    ];
                    $insA = $this->db->prepare("INSERT INTO `pengarang` (`kode_pengarang`, `nama_pengarang`, `status`, `biografi`) VALUES (?, ?, ?, ?)");
                    foreach ($seedAuthors as $sa) {
                        $insA->execute($sa);
                    }
                }

                // Migrasi kolom id_admin pada peminjaman jika belum ada
                $stmtAdmin = $this->db->query("SHOW COLUMNS FROM `peminjaman` LIKE 'id_admin'");
                if (!$stmtAdmin->fetch()) {
                    $this->db->exec("ALTER TABLE `peminjaman` ADD COLUMN `id_admin` INT(11) NULL DEFAULT 1 AFTER `id_user`");
                    $stmtR = $this->db->query("SELECT id_user FROM `user` WHERE `username` = 'ratna' LIMIT 1");
                    $ratnaId = $stmtR ? $stmtR->fetchColumn() : null;
                    if ($ratnaId) {
                        $this->db->exec("UPDATE `peminjaman` SET `id_admin` = " . (int)$ratnaId . " WHERE `id_peminjaman` % 2 = 0");
                    }
                }

                // Migrasi kolom jam_aktif pada user jika belum ada
                $stmtJam = $this->db->query("SHOW COLUMNS FROM `user` LIKE 'jam_aktif'");
                if (!$stmtJam->fetch()) {
                    $this->db->exec("ALTER TABLE `user` ADD COLUMN `jam_aktif` VARCHAR(100) NOT NULL DEFAULT '07:30 - 15:30 WIB' AFTER `role`");
                }

                // Otomatis bersihkan pengarang & penerbit dengan 0 judul buku di perpustakaan
                $this->cleanOrphanPengarang();
                $this->cleanOrphanPenerbit();
            }
        } catch (Exception $e) {
            // Silently ignore if table doesn't exist yet
        }
    }

    public function getLastError(): ?string {
        return $this->lastError;
    }

    // ==========================================
    // USER TABLE METHODS
    // ==========================================
    public function getUserByUsername(string $username): ?array {
        if (!$this->db) return null;
        $stmt = $this->db->prepare("SELECT * FROM `user` WHERE `username` = ? LIMIT 1");
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getUserById(int $id): ?array {
        if (!$this->db) return null;
        $stmt = $this->db->prepare("SELECT * FROM `user` WHERE `id_user` = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getUsersByRole(string $role): array {
        if (!$this->db) return [];
        if ($role === 'Administrator') {
            $stmt = $this->db->prepare("SELECT * FROM `user` WHERE `role` = 'Administrator' OR `role` = 'Admin' ORDER BY `id_user` ASC");
        } else {
            $stmt = $this->db->prepare("SELECT * FROM `user` WHERE `role` = 'Siswa' OR `role` = 'Member' ORDER BY `id_user` ASC");
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createUser(array $data): bool {
        try {
            $hasJamAktif = false;
            if ($this->db) {
                $chk = $this->db->query("SHOW COLUMNS FROM `user` LIKE 'jam_aktif'");
                $hasJamAktif = (bool)$chk->fetch();
            }

            if ($hasJamAktif) {
                $stmt = $this->db->prepare("INSERT INTO `user` 
                    (`kode_user`, `nis`, `fullname`, `username`, `password`, `kelas`, `alamat`, `no_whatsapp`, `verif`, `role`, `jam_aktif`, `join_date`, `terakhir_login`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                return $stmt->execute([
                    $data['kode_user'] ?? '',
                    $data['nis'] ?? '-',
                    $data['fullname'] ?? '',
                    $data['username'] ?? '',
                    $data['password'] ?? '',
                    $data['kelas'] ?? '-',
                    $data['alamat'] ?? '-',
                    $data['no_whatsapp'] ?? '087767499895',
                    $data['verif'] ?? 'Akun Terverifikasi',
                    $data['role'] ?? 'Siswa',
                    $data['jam_aktif'] ?? '07:30 - 15:30 WIB',
                    $data['join_date'] ?? date('Y-m-d'),
                    $data['terakhir_login'] ?? '-'
                ]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO `user` 
                    (`kode_user`, `nis`, `fullname`, `username`, `password`, `kelas`, `alamat`, `no_whatsapp`, `verif`, `role`, `join_date`, `terakhir_login`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                return $stmt->execute([
                    $data['kode_user'] ?? '',
                    $data['nis'] ?? '-',
                    $data['fullname'] ?? '',
                    $data['username'] ?? '',
                    $data['password'] ?? '',
                    $data['kelas'] ?? '-',
                    $data['alamat'] ?? '-',
                    $data['no_whatsapp'] ?? '087767499895',
                    $data['verif'] ?? 'Akun Terverifikasi',
                    $data['role'] ?? 'Siswa',
                    $data['join_date'] ?? date('Y-m-d'),
                    $data['terakhir_login'] ?? '-'
                ]);
            }
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function updateAdmin(int $id, array $data): bool {
        try {
            $hasJamAktif = false;
            if ($this->db) {
                $chk = $this->db->query("SHOW COLUMNS FROM `user` LIKE 'jam_aktif'");
                $hasJamAktif = (bool)$chk->fetch();
            }

            if ($hasJamAktif) {
                $stmt = $this->db->prepare("UPDATE `user` SET 
                    `fullname` = ?, `username` = ?, `password` = ?, `jam_aktif` = ? 
                    WHERE `id_user` = ?");
                return $stmt->execute([
                    $data['fullname'],
                    $data['username'],
                    $data['password'],
                    $data['jam_aktif'] ?? '07:30 - 15:30 WIB',
                    $id
                ]);
            } else {
                $stmt = $this->db->prepare("UPDATE `user` SET 
                    `fullname` = ?, `username` = ?, `password` = ? 
                    WHERE `id_user` = ?");
                return $stmt->execute([
                    $data['fullname'],
                    $data['username'],
                    $data['password'],
                    $id
                ]);
            }
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Mengambil Data Administrator Beserta Jam Aktif & Total Aktivitas Sirkulasi Sukses
     */
    public function getAdministratorsWithActivityStats(): array {
        if (!$this->db) {
            return [
                'list' => [],
                'summary' => [
                    'total_admin' => 0,
                    'total_pinjam_sukses' => 0,
                    'total_kembali_sukses' => 0,
                    'total_aktivitas' => 0,
                    'jam_operasional' => '07:30 - 15:30 WIB'
                ]
            ];
        }

        try {
            $admins = $this->getUsersByRole('Administrator');

            // Cek apakah kolom id_admin ada di tabel peminjaman
            $chkAdmin = $this->db->query("SHOW COLUMNS FROM `peminjaman` LIKE 'id_admin'");
            $hasIdAdmin = (bool)$chkAdmin->fetch();

            $totalPinjamAll = (int)$this->db->query("SELECT COUNT(*) FROM `peminjaman`")->fetchColumn();
            $totalKembaliAll = (int)$this->db->query("SELECT COUNT(*) FROM `peminjaman` WHERE `status_transaksi` = 'Selesai' OR (`tanggal_pengembalian` != '' AND `tanggal_pengembalian` IS NOT NULL AND `tanggal_pengembalian` != '0000-00-00')")->fetchColumn();

            foreach ($admins as &$a) {
                $adminId = (int)$a['id_user'];

                if ($hasIdAdmin) {
                    $stmtP = $this->db->prepare("SELECT COUNT(*) FROM `peminjaman` WHERE `id_admin` = ?");
                    $stmtP->execute([$adminId]);
                    $pinjamCount = (int)$stmtP->fetchColumn();

                    $stmtK = $this->db->prepare("SELECT COUNT(*) FROM `peminjaman` WHERE `id_admin` = ? AND (`status_transaksi` = 'Selesai' OR (`tanggal_pengembalian` != '' AND `tanggal_pengembalian` IS NOT NULL AND `tanggal_pengembalian` != '0000-00-00'))");
                    $stmtK->execute([$adminId]);
                    $kembaliCount = (int)$stmtK->fetchColumn();
                } else {
                    $pinjamCount = $totalPinjamAll;
                    $kembaliCount = $totalKembaliAll;
                }

                $a['jam_aktif'] = !empty($a['jam_aktif']) ? $a['jam_aktif'] : '07:30 - 15:30 WIB';
                $a['total_pinjam_sukses'] = $pinjamCount;
                $a['total_kembali_sukses'] = $kembaliCount;
                $a['total_aktivitas'] = $pinjamCount + $kembaliCount;
                $a['status_dinas'] = 'Aktif Bertugas';
            }
            unset($a);

            return [
                'list' => $admins,
                'summary' => [
                    'total_admin' => count($admins),
                    'total_pinjam_sukses' => $totalPinjamAll,
                    'total_kembali_sukses' => $totalKembaliAll,
                    'total_aktivitas' => $totalPinjamAll + $totalKembaliAll,
                    'jam_operasional' => '07:30 - 15:30 WIB (Senin - Jumat)'
                ]
            ];
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [
                'list' => [],
                'summary' => [
                    'total_admin' => 0,
                    'total_pinjam_sukses' => 0,
                    'total_kembali_sukses' => 0,
                    'total_aktivitas' => 0,
                    'jam_operasional' => '07:30 - 15:30 WIB'
                ]
            ];
        }
    }

    public function updateUser(int $id, array $data): bool {
        try {
            $stmt = $this->db->prepare("UPDATE `user` SET 
                `nis` = ?, `fullname` = ?, `username` = ?, `password` = ?, `kelas` = ?, `alamat` = ?, `no_whatsapp` = ? 
                WHERE `id_user` = ?");
            return $stmt->execute([
                $data['nis'],
                $data['fullname'],
                $data['username'],
                $data['password'],
                $data['kelas'],
                $data['alamat'],
                $data['no_whatsapp'] ?? '087767499895',
                $id
            ]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function deleteUser(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM `user` WHERE `id_user` = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            $this->lastError = "Tidak dapat menghapus user karena masih memiliki data transaksi peminjaman (Integritas Referensial).";
            return false;
        }
    }

    public function updateLastLogin(int $id): void {
        $now = date('d-m-Y ( H:i:s )');
        $stmt = $this->db->prepare("UPDATE `user` SET `terakhir_login` = ? WHERE `id_user` = ?");
        $stmt->execute([$now, $id]);
    }

    public function getNextKodeAnggota(): string {
        $stmt = $this->db->query("SELECT `kode_user` FROM `user` WHERE `role` = 'Siswa' OR `role` = 'Member' ORDER BY `id_user` DESC LIMIT 1");
        $row = $stmt->fetch();
        if ($row && preg_match('/AP(\d+)/', $row['kode_user'], $matches)) {
            $num = (int)$matches[1] + 1;
            return sprintf("AP%03d", $num);
        }
        return 'AP001';
    }

    public function countAnggota(): int {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM `user` WHERE `role` = 'Siswa' OR `role` = 'Member'");
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Mengambil Data Anggota (Siswa) Lengkap dengan Status dan Statistik Sirkulasi:
     * - Status Buku Sedang Dipinjam (0/2, 1/2, 2/2)
     * - Detail Buku yang Sedang Dipinjam (Judul, Tanggal Pinjam, Jatuh Tempo, Terlambat, Denda)
     * - Total Peminjaman yang Pernah Dilakukan
     * - Total Dikembalikan
     * - Total Denda (Rp)
     * - Total Dikembalikan Kondisi Baik vs Rusak
     * - Nomor WhatsApp Siswa (087767499895)
     */
    public function getAnggotaListWithCirculation(): array {
        $siswaList = $this->getUsersByRole('Siswa');
        if (empty($siswaList)) return [];

        $sql = "SELECT p.*, b.judul_buku, b.isbn, b.pengarang 
                FROM `peminjaman` p
                LEFT JOIN `buku` b ON p.id_buku = b.id_buku
                ORDER BY p.id_peminjaman DESC";
        $allLoans = $this->db ? $this->db->query($sql)->fetchAll() : [];

        $userLoans = [];
        foreach ($allLoans as $loan) {
            $uId = (int)$loan['id_user'];
            $userLoans[$uId][] = $loan;
        }

        $result = [];
        foreach ($siswaList as $siswa) {
            $uId = (int)$siswa['id_user'];
            $loans = $userLoans[$uId] ?? [];

            $activeLoans = [];
            $allHistory  = [];
            $totalPinjam = count($loans);
            $totalKembali = 0;
            $totalDendaRp = 0;
            $totalBaik = 0;
            $totalRusak = 0;

            foreach ($loans as $l) {
                $isReturned = (!empty($l['tanggal_pengembalian']) && $l['tanggal_pengembalian'] !== '0000-00-00' && ($l['status_transaksi'] === 'Selesai' || !empty($l['tanggal_pengembalian'])));
                
                $dendaVal = 0;
                $rawDenda = $l['denda'] ?? '';
                if (!empty($rawDenda) && $rawDenda !== 'Tidak ada') {
                    $clean = preg_replace('/[^0-9]/', '', explode('(', $rawDenda)[0] ?? $rawDenda);
                    $dendaVal = is_numeric($clean) ? (int)$clean : 0;
                    $totalDendaRp += $dendaVal;
                }

                if ($isReturned) {
                    $totalKembali++;
                    if (stripos($l['kondisi_buku_saat_dikembalikan'] ?? '', 'rusak') !== false || stripos($l['kondisi_buku_saat_dikembalikan'] ?? '', 'hilang') !== false) {
                        $totalRusak++;
                    } else {
                        $totalBaik++;
                    }
                } else {
                    $tglPinjamObj = DateTime::createFromFormat('d-m-Y', $l['tanggal_peminjaman']);
                    $terlambatHari = 0;
                    $estimasiDenda = 0;
                    $tglJatuhTempo = '-';
                    if ($tglPinjamObj) {
                        $tempo = clone $tglPinjamObj;
                        $tempo->modify('+7 days');
                        $tglJatuhTempo = $tempo->format('d-m-Y');
                        $today = new DateTime();
                        $today->setTime(0, 0, 0);
                        $tempo->setTime(0, 0, 0);
                        if ($today > $tempo) {
                            $diff = $tempo->diff($today);
                            $terlambatHari = (int)$diff->days;
                            $estimasiDenda = $terlambatHari * 5000;
                        }
                    }

                    $l['tgl_jatuh_tempo'] = $tglJatuhTempo;
                    $l['terlambat_hari']  = $terlambatHari;
                    $l['estimasi_denda']  = $estimasiDenda;
                    $activeLoans[] = $l;
                }

                $allHistory[] = $l;
            }

            $siswa['no_whatsapp']        = !empty($siswa['no_whatsapp']) ? $siswa['no_whatsapp'] : '087767499895';
            $siswa['active_loans']       = $activeLoans;
            $siswa['active_loan_count']  = count($activeLoans);
            $siswa['all_loans']          = $allHistory;
            $siswa['total_pinjam']       = $totalPinjam;
            $siswa['total_kembali']      = $totalKembali;
            $siswa['total_denda_rp']     = $totalDendaRp;
            $siswa['total_kembali_baik'] = $totalBaik;
            $siswa['total_kembali_rusak']= $totalRusak;

            $result[] = $siswa;
        }

        return $result;
    }

    public function findUserId(string $usernameOrId): int {
        if (is_numeric($usernameOrId) && (int)$usernameOrId > 0) {
            return (int)$usernameOrId;
        }
        $user = $this->getUserByUsername($usernameOrId);
        if ($user) {
            return (int)$user['id_user'];
        }
        // Fallback default student (id 2)
        return 2;
    }

    // ==========================================
    // PENERBIT TABLE METHODS
    // ==========================================
    public function getAllPenerbit(): array {
        if (!$this->db) return [];
        $stmt = $this->db->query("SELECT * FROM `penerbit` ORDER BY `id_penerbit` ASC");
        return $stmt->fetchAll();
    }

    public function getAllPenerbitWithStats(): array {
        if (!$this->db) return [];
        // Otomatis hapus penerbit yang tidak memiliki judul buku di perpustakaan (0 judul)
        $this->cleanOrphanPenerbit();

        $sql = "SELECT p.*,
                COUNT(DISTINCT b.id_buku) AS total_judul,
                COALESCE(SUM(b.j_buku_baik), 0) AS total_stok_tersedia,
                COALESCE(SUM(b.j_buku_baik + b.j_buku_rusak + b.j_buku_hilang), 0) AS total_stok_fisik,
                COALESCE(SUM(b.j_buku_rusak), 0) AS total_stok_rusak,
                COALESCE(SUM(b.j_buku_hilang), 0) AS total_stok_hilang,
                (
                    SELECT COUNT(pm.id_peminjaman)
                    FROM `peminjaman` pm
                    JOIN `buku` bk ON pm.id_buku = bk.id_buku
                    WHERE (bk.id_penerbit = p.id_penerbit OR bk.penerbit_buku = p.nama_penerbit)
                      AND (pm.status_transaksi = 'Sedang Dipinjam' OR pm.tanggal_pengembalian IS NULL OR pm.tanggal_pengembalian = '0000-00-00' OR pm.tanggal_pengembalian = '')
                ) AS total_terpinjam
                FROM `penerbit` p
                LEFT JOIN `buku` b ON (b.id_penerbit = p.id_penerbit OR b.penerbit_buku = p.nama_penerbit)
                GROUP BY p.id_penerbit
                ORDER BY p.id_penerbit ASC";
        $stmt = $this->db->query($sql);
        $list = $stmt->fetchAll();

        // Sertakan daftar buku per penerbit untuk pop up detail
        foreach ($list as &$row) {
            $pId = (int)$row['id_penerbit'];
            $pName = $row['nama_penerbit'];
            $bStmt = $this->db->prepare("
                SELECT b.*,
                (
                    SELECT COUNT(pm.id_peminjaman)
                    FROM `peminjaman` pm
                    WHERE pm.id_buku = b.id_buku
                      AND (pm.status_transaksi = 'Sedang Dipinjam' OR pm.tanggal_pengembalian IS NULL OR pm.tanggal_pengembalian = '0000-00-00' OR pm.tanggal_pengembalian = '')
                ) AS sedang_dipinjam
                FROM `buku` b
                WHERE b.id_penerbit = ? OR b.penerbit_buku = ?
                ORDER BY b.judul_buku ASC
            ");
            $bStmt->execute([$pId, $pName]);
            $row['buku_list'] = $bStmt->fetchAll();
        }
        unset($row);

        return $list;
    }

    public function getPenerbitById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `penerbit` WHERE `id_penerbit` = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createPenerbit(array $data): bool {
        try {
            $stmt = $this->db->prepare("INSERT INTO `penerbit` (`kode_penerbit`, `nama_penerbit`, `verif_penerbit`) VALUES (?, ?, ?)");
            return $stmt->execute([
                $data['kode_penerbit'] ?? $this->getNextKodePenerbit(),
                $data['nama_penerbit'] ?? '',
                $data['verif_penerbit'] ?? 'Penerbit Terverifikasi'
            ]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function updatePenerbit(int $id, array $data): bool {
        try {
            $stmt = $this->db->prepare("UPDATE `penerbit` SET `nama_penerbit` = ?, `verif_penerbit` = ? WHERE `id_penerbit` = ?");
            return $stmt->execute([
                $data['nama_penerbit'],
                $data['verif_penerbit'] ?? 'Penerbit Terverifikasi',
                $id
            ]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function deletePenerbit(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM `penerbit` WHERE `id_penerbit` = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            $this->lastError = "Tidak dapat menghapus penerbit karena masih berelasi dengan data buku aktif (Integritas Referensial).";
            return false;
        }
    }

    public function getNextKodePenerbit(): string {
        $stmt = $this->db->query("SELECT `kode_penerbit` FROM `penerbit` ORDER BY `id_penerbit` DESC LIMIT 1");
        $row = $stmt->fetch();
        if ($row && preg_match('/P(\d+)/', $row['kode_penerbit'], $matches)) {
            $num = (int)$matches[1] + 1;
            return sprintf("P%03d", $num);
        }
        return 'P001';
    }

    public function findPenerbitId(string $nameOrId): int {
        if (is_numeric($nameOrId) && (int)$nameOrId > 0) {
            return (int)$nameOrId;
        }
        $stmt = $this->db->prepare("SELECT id_penerbit FROM `penerbit` WHERE `nama_penerbit` = ? LIMIT 1");
        $stmt->execute([$nameOrId]);
        $val = $stmt->fetchColumn();
        return $val ? (int)$val : 1;
    }

    /**
     * Otomatis Hapus Penerbit dengan 0 Judul Buku di Perpustakaan
     */
    public function cleanOrphanPenerbit(): int {
        if (!$this->db) return 0;
        try {
            $stmt = $this->db->query("SELECT id_penerbit, nama_penerbit FROM `penerbit`");
            $all = $stmt->fetchAll();
            $deleted = 0;
            foreach ($all as $p) {
                $id = (int)$p['id_penerbit'];
                $name = trim($p['nama_penerbit']);
                $chk = $this->db->prepare("SELECT COUNT(*) FROM `buku` WHERE id_penerbit = ? OR LOWER(TRIM(penerbit_buku)) = LOWER(TRIM(?))");
                $chk->execute([$id, $name]);
                if ((int)$chk->fetchColumn() === 0) {
                    try {
                        $del = $this->db->prepare("DELETE FROM `penerbit` WHERE id_penerbit = ?");
                        $del->execute([$id]);
                        $deleted++;
                    } catch (Exception $ex) {
                        // Jika terikat referensi FK, abaikan
                    }
                }
            }
            return $deleted;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return 0;
        }
    }

    // ==========================================
    // PENGARANG TABLE METHODS (MASTER DATA PENGARANG)
    // ==========================================
    public function getAllPengarang(): array {
        if (!$this->db) return [];
        $stmt = $this->db->query("SELECT * FROM `pengarang` ORDER BY `id_pengarang` ASC");
        return $stmt->fetchAll();
    }

    public function getAllPengarangWithStats(): array {
        if (!$this->db) return [];
        try {
            // Otomatis hapus pengarang yang tidak memiliki judul buku di perpustakaan (0 judul)
            $this->cleanOrphanPengarang();

            $sql = "SELECT p.*,
                    COUNT(DISTINCT b.id_buku) AS total_judul,
                    COALESCE(SUM(b.j_buku_baik), 0) AS total_stok_tersedia,
                    COALESCE(SUM(b.j_buku_baik + b.j_buku_rusak + b.j_buku_hilang), 0) AS total_stok_fisik,
                    COALESCE(SUM(b.j_buku_rusak), 0) AS total_stok_rusak,
                    COALESCE(SUM(b.j_buku_hilang), 0) AS total_stok_hilang,
                    (
                        SELECT COUNT(pm.id_peminjaman)
                        FROM `peminjaman` pm
                        JOIN `buku` bk ON pm.id_buku = bk.id_buku
                        WHERE (bk.pengarang LIKE CONCAT('%', p.nama_pengarang, '%') OR p.nama_pengarang LIKE CONCAT('%', bk.pengarang, '%'))
                          AND (pm.status_transaksi = 'Sedang Dipinjam' OR pm.tanggal_pengembalian IS NULL OR pm.tanggal_pengembalian = '0000-00-00' OR pm.tanggal_pengembalian = '')
                    ) AS total_terpinjam,
                    (
                        SELECT COUNT(pm2.id_peminjaman)
                        FROM `peminjaman` pm2
                        JOIN `buku` bk2 ON pm2.id_buku = bk2.id_buku
                        WHERE (bk2.pengarang LIKE CONCAT('%', p.nama_pengarang, '%') OR p.nama_pengarang LIKE CONCAT('%', bk2.pengarang, '%'))
                    ) AS total_riwayat_pinjam
                    FROM `pengarang` p
                    LEFT JOIN `buku` b ON (b.pengarang LIKE CONCAT('%', p.nama_pengarang, '%') OR p.nama_pengarang LIKE CONCAT('%', b.pengarang, '%'))
                    GROUP BY p.id_pengarang
                    ORDER BY p.id_pengarang ASC";
            $stmt = $this->db->query($sql);
            $list = $stmt->fetchAll();

            // Sertakan daftar buku per pengarang untuk pop up detail
            foreach ($list as &$row) {
                $pName = $row['nama_pengarang'];
                $bStmt = $this->db->prepare("
                    SELECT b.*,
                    (
                        SELECT COUNT(pm.id_peminjaman)
                        FROM `peminjaman` pm
                        WHERE pm.id_buku = b.id_buku
                          AND (pm.status_transaksi = 'Sedang Dipinjam' OR pm.tanggal_pengembalian IS NULL OR pm.tanggal_pengembalian = '0000-00-00' OR pm.tanggal_pengembalian = '')
                    ) AS sedang_dipinjam,
                    (
                        SELECT COUNT(pm_all.id_peminjaman)
                        FROM `peminjaman` pm_all
                        WHERE pm_all.id_buku = b.id_buku
                    ) AS kali_dipinjam
                    FROM `buku` b
                    WHERE b.pengarang LIKE CONCAT('%', ?, '%') OR ? LIKE CONCAT('%', b.pengarang, '%')
                    ORDER BY b.judul_buku ASC
                ");
                $bStmt->execute([$pName, $pName]);
                $row['buku_list'] = $bStmt->fetchAll();
            }
            unset($row);

            return $list;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    public function getPengarangById(int $id): ?array {
        if (!$this->db) return null;
        try {
            $stmt = $this->db->prepare("SELECT * FROM `pengarang` WHERE `id_pengarang` = ? LIMIT 1");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return null;
        }
    }

    public function createPengarang(array $data): bool {
        if (!$this->db) return false;
        try {
            $stmt = $this->db->prepare("INSERT INTO `pengarang` (`kode_pengarang`, `nama_pengarang`, `status`, `biografi`) VALUES (?, ?, ?, ?)");
            return $stmt->execute([
                $data['kode_pengarang'] ?? $this->getNextKodePengarang(),
                $data['nama_pengarang'] ?? '',
                $data['status'] ?? 'Aktif',
                $data['biografi'] ?? ''
            ]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function updatePengarang(int $id, array $data): bool {
        if (!$this->db) return false;
        try {
            $stmt = $this->db->prepare("UPDATE `pengarang` SET `nama_pengarang` = ?, `status` = ?, `biografi` = ? WHERE `id_pengarang` = ?");
            return $stmt->execute([
                $data['nama_pengarang'],
                $data['status'] ?? 'Aktif',
                $data['biografi'] ?? '',
                $id
            ]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function deletePengarang(int $id): bool {
        if (!$this->db) return false;
        try {
            $stmtP = $this->db->prepare("SELECT nama_pengarang FROM `pengarang` WHERE `id_pengarang` = ?");
            $stmtP->execute([$id]);
            $name = $stmtP->fetchColumn();
            if ($name) {
                $stmtC = $this->db->prepare("SELECT COUNT(*) FROM `buku` WHERE `pengarang` LIKE CONCAT('%', ?, '%')");
                $stmtC->execute([$name]);
                if ((int)$stmtC->fetchColumn() > 0) {
                    $this->lastError = "Tidak dapat menghapus data pengarang karena masih ada buku yang terdaftar dengan nama pengarang ini.";
                    return false;
                }
            }
            $stmt = $this->db->prepare("DELETE FROM `pengarang` WHERE `id_pengarang` = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function getNextKodePengarang(): string {
        if (!$this->db) return 'AUT001';
        try {
            $stmt = $this->db->query("SELECT `kode_pengarang` FROM `pengarang` ORDER BY `id_pengarang` DESC LIMIT 1");
            $row = $stmt->fetch();
            if ($row && preg_match('/AUT(\d+)/i', $row['kode_pengarang'], $matches)) {
                $num = (int)$matches[1] + 1;
                return sprintf("AUT%03d", $num);
            }
            $count = (int)$this->db->query("SELECT COUNT(*) FROM `pengarang`")->fetchColumn();
            return sprintf("AUT%03d", $count + 1);
        } catch (PDOException $e) {
            return 'AUT001';
        }
    }

    /**
     * Otomatis Hapus Pengarang dengan 0 Judul Buku di Perpustakaan
     */
    public function cleanOrphanPengarang(): int {
        if (!$this->db) return 0;
        try {
            $stmt = $this->db->query("SELECT id_pengarang, nama_pengarang FROM `pengarang`");
            $all = $stmt->fetchAll();
            $deleted = 0;
            foreach ($all as $p) {
                $name = trim($p['nama_pengarang']);
                if (empty($name)) {
                    $this->db->exec("DELETE FROM `pengarang` WHERE id_pengarang = " . (int)$p['id_pengarang']);
                    $deleted++;
                    continue;
                }
                $chk = $this->db->prepare("SELECT COUNT(*) FROM `buku` WHERE LOWER(`pengarang`) LIKE CONCAT('%', LOWER(?), '%') OR LOWER(?) LIKE CONCAT('%', LOWER(`pengarang`), '%')");
                $chk->execute([$name, $name]);
                if ((int)$chk->fetchColumn() === 0) {
                    $del = $this->db->prepare("DELETE FROM `pengarang` WHERE id_pengarang = ?");
                    $del->execute([$p['id_pengarang']]);
                    $deleted++;
                }
            }
            return $deleted;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return 0;
        }
    }

    public function getTopLarisPengarang(int $limit = 7): array {
        if (!$this->db) return [];
        try {
            $sql = "SELECT 
                        p.id_pengarang,
                        p.kode_pengarang,
                        p.nama_pengarang,
                        COUNT(DISTINCT bk.id_buku) AS total_judul,
                        COALESCE(SUM(bk.j_buku_baik), 0) AS total_stok,
                        (
                            SELECT COUNT(pm.id_peminjaman)
                            FROM `peminjaman` pm
                            JOIN `buku` b2 ON pm.id_buku = b2.id_buku
                            WHERE (b2.pengarang LIKE CONCAT('%', p.nama_pengarang, '%') OR p.nama_pengarang LIKE CONCAT('%', b2.pengarang, '%'))
                        ) AS total_dipinjam,
                        (
                            SELECT COUNT(pm_act.id_peminjaman)
                            FROM `peminjaman` pm_act
                            JOIN `buku` b_act ON pm_act.id_buku = b_act.id_buku
                            WHERE (b_act.pengarang LIKE CONCAT('%', p.nama_pengarang, '%') OR p.nama_pengarang LIKE CONCAT('%', b_act.pengarang, '%'))
                              AND (pm_act.status_transaksi = 'Sedang Dipinjam' OR pm_act.tanggal_pengembalian IS NULL OR pm_act.tanggal_pengembalian = '0000-00-00' OR pm_act.tanggal_pengembalian = '')
                        ) AS terpinjam_aktif
                    FROM `pengarang` p
                    LEFT JOIN `buku` bk ON (bk.pengarang LIKE CONCAT('%', p.nama_pengarang, '%') OR p.nama_pengarang LIKE CONCAT('%', bk.pengarang, '%'))
                    GROUP BY p.id_pengarang, p.kode_pengarang, p.nama_pengarang
                    ORDER BY total_dipinjam DESC, total_judul DESC, p.id_pengarang ASC
                    LIMIT " . (int)$limit;
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // ==========================================
    // KATEGORI TABLE METHODS
    // ==========================================
    public function getAllKategori(): array {
        $sql = "SELECT 
                    k.id_kategori,
                    k.kode_kategori,
                    k.nama_kategori,
                    (SELECT COUNT(*) FROM `buku` WHERE id_kategori = k.id_kategori) AS total_judul,
                    (SELECT COALESCE(SUM(j_buku_baik + j_buku_rusak + COALESCE(j_buku_hilang, 0)), 0) FROM `buku` WHERE id_kategori = k.id_kategori) AS total_stok,
                    (SELECT COALESCE(SUM(j_buku_baik), 0) FROM `buku` WHERE id_kategori = k.id_kategori) AS total_baik,
                    (SELECT COALESCE(SUM(j_buku_rusak), 0) FROM `buku` WHERE id_kategori = k.id_kategori) AS total_rusak,
                    (SELECT COALESCE(SUM(COALESCE(j_buku_hilang, 0)), 0) FROM `buku` WHERE id_kategori = k.id_kategori) AS total_hilang,
                    (SELECT COUNT(*) FROM `peminjaman` p JOIN `buku` b ON p.id_buku = b.id_buku WHERE b.id_kategori = k.id_kategori AND (p.status_transaksi = 'Sedang Dipinjam' OR p.kondisi_buku_saat_dikembalikan IS NULL OR p.kondisi_buku_saat_dikembalikan = '')) AS total_dipinjam,
                    (SELECT COUNT(*) FROM `peminjaman` p JOIN `buku` b ON p.id_buku = b.id_buku WHERE b.id_kategori = k.id_kategori) AS total_riwayat_pinjam
                FROM `kategori` k 
                ORDER BY k.`id_kategori` ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getBukuByKategoriId(int $idKategori): array {
        $sql = "SELECT 
                    b.id_buku,
                    b.judul_buku,
                    b.pengarang,
                    b.penerbit_buku,
                    b.isbn,
                    b.tahun_terbit,
                    b.j_buku_baik,
                    b.j_buku_rusak,
                    COALESCE(b.j_buku_hilang, 0) AS j_buku_hilang,
                    (b.j_buku_baik + b.j_buku_rusak + COALESCE(b.j_buku_hilang, 0)) AS total_stok,
                    (SELECT COUNT(*) FROM peminjaman WHERE id_buku = b.id_buku) AS total_pinjam
                FROM `buku` b
                WHERE b.id_kategori = ?
                ORDER BY total_pinjam DESC, b.judul_buku ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idKategori]);
        return $stmt->fetchAll();
    }

    public function getKategoriById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `kategori` WHERE `id_kategori` = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createKategori(array $data): bool {
        try {
            $stmt = $this->db->prepare("INSERT INTO `kategori` (`kode_kategori`, `nama_kategori`) VALUES (?, ?)");
            return $stmt->execute([
                $data['kode_kategori'] ?? $this->getNextKodeKategori(),
                $data['nama_kategori'] ?? ''
            ]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function updateKategori(int $id, array $data): bool {
        try {
            $stmt = $this->db->prepare("UPDATE `kategori` SET `nama_kategori` = ? WHERE `id_kategori` = ?");
            return $stmt->execute([
                $data['nama_kategori'],
                $id
            ]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function deleteKategori(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM `kategori` WHERE `id_kategori` = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            $this->lastError = "Tidak dapat menghapus kategori karena masih berelasi dengan data buku aktif (Integritas Referensial).";
            return false;
        }
    }

    public function getNextKodeKategori(): string {
        $stmt = $this->db->query("SELECT `kode_kategori` FROM `kategori` ORDER BY `id_kategori` DESC LIMIT 1");
        $row = $stmt->fetch();
        if ($row && preg_match('/K(\d+)/', $row['kode_kategori'], $matches)) {
            $num = (int)$matches[1] + 1;
            return sprintf("K%03d", $num);
        }
        return 'K001';
    }

    public function findKategoriId(string $nameOrId): int {
        if (is_numeric($nameOrId) && (int)$nameOrId > 0) {
            return (int)$nameOrId;
        }
        $stmt = $this->db->prepare("SELECT id_kategori FROM `kategori` WHERE `nama_kategori` = ? LIMIT 1");
        $stmt->execute([$nameOrId]);
        $val = $stmt->fetchColumn();
        return $val ? (int)$val : 1;
    }

    // ==========================================
    // BUKU TABLE METHODS (Dengan Relasi FK & View)
    // ==========================================
    public static function resolveCoverUrl(array|object $buku, string $baseUrl): string {
        $cleanIsbn = preg_replace('/[^0-9X]/i', '', (string)($buku['isbn'] ?? ''));
        $title = trim($buku['judul_buku'] ?? '');
        $slug = preg_replace('/[^a-z0-9_-]/i', '_', strtolower($title));
        $coverDb = trim($buku['cover'] ?? '');

        $coverDir = dirname(__DIR__) . '/cover_buku/';

        // 1. Cover eksplisit di database
        if (!empty($coverDb) && file_exists($coverDir . basename($coverDb))) {
            return $baseUrl . 'cover_buku/' . rawurlencode(basename($coverDb));
        }

        // 2. Berdasarkan ISBN (misal 9786020312583.jpg)
        if (!empty($cleanIsbn)) {
            foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
                if (file_exists($coverDir . $cleanIsbn . '.' . $ext)) {
                    return $baseUrl . 'cover_buku/' . $cleanIsbn . '.' . $ext;
                }
            }
        }

        // 3. Berdasarkan slug judul buku (misal cantik_itu_luka.jpg)
        if (!empty($slug)) {
            foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
                if (file_exists($coverDir . $slug . '.' . $ext)) {
                    return $baseUrl . 'cover_buku/' . $slug . '.' . $ext;
                }
            }
        }

        // 4. Berdasarkan judul buku persis (misal Cantik Itu Luka.jpg)
        if (!empty($title)) {
            foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
                if (file_exists($coverDir . $title . '.' . $ext)) {
                    return $baseUrl . 'cover_buku/' . rawurlencode($title . '.' . $ext);
                }
            }
        }

        // 5. Fallback ke placeholder SVG
        return $baseUrl . 'assets/img/cover_placeholder.svg';
    }

    public function getAllBuku(): array {
        if (!$this->db) return [];
        // Menggunakan JOIN / View untuk relasi formal kategori dan penerbit serta kalkulasi sirkulasi
        $sql = "SELECT 
                    b.id_buku,
                    b.id_kategori,
                    b.id_penerbit,
                    b.id_pengarang,
                    b.judul_buku,
                    COALESCE(k.nama_kategori, b.kategori_buku) AS kategori_buku,
                    COALESCE(p.nama_penerbit, b.penerbit_buku) AS penerbit_buku,
                    COALESCE(pg.nama_pengarang, b.pengarang) AS pengarang,
                    pg.kode_pengarang,
                    b.tahun_terbit,
                    b.isbn,
                    b.j_buku_baik,
                    b.j_buku_rusak,
                    COALESCE(b.j_buku_hilang, 0) AS j_buku_hilang,
                    b.sinopsis,
                    b.cover,
                    COALESCE(b.harga, 65000) AS harga,
                    (SELECT COUNT(*) FROM `peminjaman` WHERE id_buku = b.id_buku AND (status_transaksi = 'Sedang Dipinjam' OR kondisi_buku_saat_dikembalikan IS NULL OR kondisi_buku_saat_dikembalikan = '')) AS sedang_dipinjam,
                    (SELECT COUNT(*) FROM `peminjaman` WHERE id_buku = b.id_buku) AS total_pinjam,
                    (SELECT COUNT(*) FROM `peminjaman` WHERE id_buku = b.id_buku AND status_transaksi = 'Selesai') AS total_kembali,
                    (b.j_buku_baik + b.j_buku_rusak + COALESCE(b.j_buku_hilang, 0) + 
                     (SELECT COUNT(*) FROM `peminjaman` WHERE id_buku = b.id_buku AND (status_transaksi = 'Sedang Dipinjam' OR kondisi_buku_saat_dikembalikan IS NULL OR kondisi_buku_saat_dikembalikan = ''))) AS jumlah_awal,
                    (b.j_buku_baik + b.j_buku_rusak + COALESCE(b.j_buku_hilang, 0)) AS total_buku
                FROM `buku` b
                LEFT JOIN `kategori` k ON b.id_kategori = k.id_kategori
                LEFT JOIN `penerbit` p ON b.id_penerbit = p.id_penerbit
                LEFT JOIN `pengarang` pg ON b.id_pengarang = pg.id_pengarang
                ORDER BY b.id_buku ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getBukuById(int $id): ?array {
        if (!$this->db) return null;
        $sql = "SELECT 
                    b.id_buku,
                    b.id_kategori,
                    b.id_penerbit,
                    b.id_pengarang,
                    b.judul_buku,
                    COALESCE(k.nama_kategori, b.kategori_buku) AS kategori_buku,
                    COALESCE(p.nama_penerbit, b.penerbit_buku) AS penerbit_buku,
                    COALESCE(pg.nama_pengarang, b.pengarang) AS pengarang,
                    pg.kode_pengarang,
                    b.tahun_terbit,
                    b.isbn,
                    b.j_buku_baik,
                    b.j_buku_rusak,
                    COALESCE(b.j_buku_hilang, 0) AS j_buku_hilang,
                    b.sinopsis,
                    b.cover,
                    COALESCE(b.harga, 65000) AS harga,
                    (SELECT COUNT(*) FROM `peminjaman` WHERE id_buku = b.id_buku AND (status_transaksi = 'Sedang Dipinjam' OR kondisi_buku_saat_dikembalikan IS NULL OR kondisi_buku_saat_dikembalikan = '')) AS sedang_dipinjam,
                    (SELECT COUNT(*) FROM `peminjaman` WHERE id_buku = b.id_buku) AS total_pinjam,
                    (SELECT COUNT(*) FROM `peminjaman` WHERE id_buku = b.id_buku AND status_transaksi = 'Selesai') AS total_kembali,
                    (b.j_buku_baik + b.j_buku_rusak + COALESCE(b.j_buku_hilang, 0) + 
                     (SELECT COUNT(*) FROM `peminjaman` WHERE id_buku = b.id_buku AND (status_transaksi = 'Sedang Dipinjam' OR kondisi_buku_saat_dikembalikan IS NULL OR kondisi_buku_saat_dikembalikan = ''))) AS jumlah_awal,
                    (b.j_buku_baik + b.j_buku_rusak + COALESCE(b.j_buku_hilang, 0)) AS total_buku
                FROM `buku` b
                LEFT JOIN `kategori` k ON b.id_kategori = k.id_kategori
                LEFT JOIN `penerbit` p ON b.id_penerbit = p.id_penerbit
                LEFT JOIN `pengarang` pg ON b.id_pengarang = pg.id_pengarang
                WHERE b.id_buku = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createBuku(array $data): bool {
        try {
            $idKategori = isset($data['id_kategori']) && (int)$data['id_kategori'] > 0
                ? (int)$data['id_kategori']
                : $this->findKategoriId($data['kategori_buku'] ?? '');

            // 1. Cek & Otomatis Tambah Penerbit jika belum ada
            $namaPenerbit = trim($data['penerbit_buku_custom'] ?? $data['penerbit_buku'] ?? '');
            $idPenerbit = isset($data['id_penerbit']) && (int)$data['id_penerbit'] > 0 ? (int)$data['id_penerbit'] : 0;
            if ($idPenerbit > 0) {
                $pRow = $this->getPenerbitById($idPenerbit);
                if ($pRow) {
                    $namaPenerbit = $pRow['nama_penerbit'];
                }
            }
            if ($idPenerbit <= 0 && !empty($namaPenerbit)) {
                $stmtP = $this->db->prepare("SELECT id_penerbit, nama_penerbit FROM `penerbit` WHERE LOWER(TRIM(nama_penerbit)) = LOWER(TRIM(?)) LIMIT 1");
                $stmtP->execute([$namaPenerbit]);
                $foundP = $stmtP->fetch();
                if ($foundP) {
                    $idPenerbit = (int)$foundP['id_penerbit'];
                    $namaPenerbit = $foundP['nama_penerbit'];
                } else {
                    $kodeP = $this->getNextKodePenerbit();
                    $stmtInsP = $this->db->prepare("INSERT INTO `penerbit` (`kode_penerbit`, `nama_penerbit`, `verif_penerbit`) VALUES (?, ?, 'Penerbit Terverifikasi')");
                    $stmtInsP->execute([$kodeP, $namaPenerbit]);
                    $idPenerbit = (int)$this->db->lastInsertId();
                }
            }
            if ($idPenerbit <= 0) {
                $idPenerbit = 1;
            }

            // 2. Cek & Otomatis Tambah Pengarang jika belum ada
            $idPengarang = isset($data['id_pengarang']) && (int)$data['id_pengarang'] > 0 ? (int)$data['id_pengarang'] : 0;
            $namaPengarang = trim($data['pengarang'] ?? '');
            if ($idPengarang > 0) {
                $pRow = $this->getPengarangById($idPengarang);
                if ($pRow) {
                    $namaPengarang = $pRow['nama_pengarang'];
                }
            }
            if ($idPengarang <= 0 && !empty($namaPengarang)) {
                $stmtA = $this->db->prepare("SELECT id_pengarang, nama_pengarang FROM `pengarang` WHERE LOWER(TRIM(nama_pengarang)) = LOWER(TRIM(?)) LIMIT 1");
                $stmtA->execute([$namaPengarang]);
                $foundA = $stmtA->fetch();
                if ($foundA) {
                    $idPengarang = (int)$foundA['id_pengarang'];
                    $namaPengarang = $foundA['nama_pengarang'];
                } else {
                    $kodeA = $this->getNextKodePengarang();
                    $stmtInsA = $this->db->prepare("INSERT INTO `pengarang` (`kode_pengarang`, `nama_pengarang`, `status`, `biografi`) VALUES (?, ?, 'Aktif', ?)");
                    $stmtInsA->execute([$kodeA, $namaPengarang, 'Penulis buku ' . ($data['judul_buku'] ?? '')]);
                    $idPengarang = (int)$this->db->lastInsertId();
                }
            }
            if ($idPengarang <= 0) {
                $firstA = $this->db->query("SELECT id_pengarang FROM `pengarang` LIMIT 1")->fetchColumn();
                $idPengarang = $firstA ? (int)$firstA : 1;
            }

            // Ambil nama kategori & penerbit sinkron
            $kategoriRow = $this->getKategoriById($idKategori);
            $namaKategori = $kategoriRow['nama_kategori'] ?? ($data['kategori_buku'] ?? '');

            $stmt = $this->db->prepare("INSERT INTO `buku` 
                (`id_kategori`, `id_penerbit`, `id_pengarang`, `judul_buku`, `kategori_buku`, `penerbit_buku`, `pengarang`, `tahun_terbit`, `isbn`, `j_buku_baik`, `j_buku_rusak`, `j_buku_hilang`, `sinopsis`, `cover`, `harga`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([
                $idKategori,
                $idPenerbit,
                $idPengarang,
                $data['judul_buku'],
                $namaKategori,
                $namaPenerbit,
                $namaPengarang,
                $data['tahun_terbit'],
                (string)($data['isbn'] ?? ''),
                (int)($data['j_buku_baik'] ?? 0),
                (int)($data['j_buku_rusak'] ?? 0),
                (int)($data['j_buku_hilang'] ?? 0),
                $data['sinopsis'] ?? '',
                $data['cover'] ?? null,
                (int)($data['harga'] ?? 65000)
            ]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function updateBuku(int $id, array $data): bool {
        try {
            $idKategori = isset($data['id_kategori']) && (int)$data['id_kategori'] > 0
                ? (int)$data['id_kategori']
                : $this->findKategoriId($data['kategori_buku'] ?? '');

            $idPenerbit = isset($data['id_penerbit']) && (int)$data['id_penerbit'] > 0
                ? (int)$data['id_penerbit']
                : $this->findPenerbitId($data['penerbit_buku'] ?? '');

            $kategoriRow = $this->getKategoriById($idKategori);
            $namaKategori = $kategoriRow['nama_kategori'] ?? ($data['kategori_buku'] ?? '');

            $penerbitRow = $this->getPenerbitById($idPenerbit);
            $namaPenerbit = $penerbitRow['nama_penerbit'] ?? ($data['penerbit_buku'] ?? '');

            // Sinkronisasi id_pengarang
            $idPengarang = isset($data['id_pengarang']) && (int)$data['id_pengarang'] > 0 ? (int)$data['id_pengarang'] : 0;
            $namaPengarang = trim($data['pengarang'] ?? '');
            if ($idPengarang > 0) {
                $pRow = $this->getPengarangById($idPengarang);
                if ($pRow) {
                    $namaPengarang = $pRow['nama_pengarang'];
                }
            }
            if ($idPengarang <= 0 && !empty($namaPengarang)) {
                $stmtA = $this->db->prepare("SELECT id_pengarang, nama_pengarang FROM `pengarang` WHERE LOWER(TRIM(nama_pengarang)) = LOWER(TRIM(?)) LIMIT 1");
                $stmtA->execute([$namaPengarang]);
                $foundA = $stmtA->fetch();
                if ($foundA) {
                    $idPengarang = (int)$foundA['id_pengarang'];
                    $namaPengarang = $foundA['nama_pengarang'];
                } else {
                    $kodeA = $this->getNextKodePengarang();
                    $stmtInsA = $this->db->prepare("INSERT INTO `pengarang` (`kode_pengarang`, `nama_pengarang`, `status`, `biografi`) VALUES (?, ?, 'Aktif', ?)");
                    $stmtInsA->execute([$kodeA, $namaPengarang, 'Penulis buku literasi perpustakaan.']);
                    $idPengarang = (int)$this->db->lastInsertId();
                }
            }
            if ($idPengarang <= 0) {
                $curPeng = $this->db->query("SELECT id_pengarang FROM `buku` WHERE id_buku = $id")->fetchColumn();
                $idPengarang = $curPeng ? (int)$curPeng : 1;
            }

            if (isset($data['cover']) && $data['cover'] !== null) {
                $stmt = $this->db->prepare("UPDATE `buku` SET 
                    `id_kategori` = ?, `id_penerbit` = ?, `id_pengarang` = ?, `judul_buku` = ?, `kategori_buku` = ?, `penerbit_buku` = ?, `pengarang` = ?, 
                    `tahun_terbit` = ?, `isbn` = ?, `j_buku_baik` = ?, `j_buku_rusak` = ?, `j_buku_hilang` = ?, `sinopsis` = ?, `cover` = ?, `harga` = ? 
                    WHERE `id_buku` = ?");
                return $stmt->execute([
                    $idKategori,
                    $idPenerbit,
                    $idPengarang,
                    $data['judul_buku'],
                    $namaKategori,
                    $namaPenerbit,
                    $namaPengarang,
                    $data['tahun_terbit'],
                    (string)($data['isbn'] ?? ''),
                    (int)($data['j_buku_baik'] ?? 0),
                    (int)($data['j_buku_rusak'] ?? 0),
                    (int)($data['j_buku_hilang'] ?? 0),
                    $data['sinopsis'] ?? '',
                    $data['cover'],
                    (int)($data['harga'] ?? 65000),
                    $id
                ]);
            } else {
                $stmt = $this->db->prepare("UPDATE `buku` SET 
                    `id_kategori` = ?, `id_penerbit` = ?, `id_pengarang` = ?, `judul_buku` = ?, `kategori_buku` = ?, `penerbit_buku` = ?, `pengarang` = ?, 
                    `tahun_terbit` = ?, `isbn` = ?, `j_buku_baik` = ?, `j_buku_rusak` = ?, `j_buku_hilang` = ?, `sinopsis` = ?, `harga` = ? 
                    WHERE `id_buku` = ?");
                return $stmt->execute([
                    $idKategori,
                    $idPenerbit,
                    $idPengarang,
                    $data['judul_buku'],
                    $namaKategori,
                    $namaPenerbit,
                    $namaPengarang,
                    $data['tahun_terbit'],
                    (string)($data['isbn'] ?? ''),
                    (int)($data['j_buku_baik'] ?? 0),
                    (int)($data['j_buku_rusak'] ?? 0),
                    (int)($data['j_buku_hilang'] ?? 0),
                    $data['sinopsis'] ?? '',
                    (int)($data['harga'] ?? 65000),
                    $id
                ]);
            }
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function deleteBuku(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM `buku` WHERE `id_buku` = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            $this->lastError = "Tidak dapat menghapus buku karena masih terdapat riwayat peminjaman terkait (Integritas Referensial).";
            return false;
        }
    }

    public function countBuku(): int {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM `buku`");
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    public function findBukuId(string $judulOrId): int {
        if (is_numeric($judulOrId) && (int)$judulOrId > 0) {
            return (int)$judulOrId;
        }
        $stmt = $this->db->prepare("SELECT id_buku FROM `buku` WHERE `judul_buku` = ? LIMIT 1");
        $stmt->execute([$judulOrId]);
        $val = $stmt->fetchColumn();
        return $val ? (int)$val : 1;
    }

    // ==========================================
    // PEMINJAMAN TABLE METHODS (Dengan Relasi FK & Trigger Mutasi Stok)
    // ==========================================
    public function getAllPeminjaman(): array {
        $sql = "SELECT 
                    p.id_peminjaman,
                    p.id_user,
                    p.id_buku,
                    COALESCE(p.status_transaksi, CASE WHEN p.tanggal_pengembalian != '' AND p.tanggal_pengembalian != '0000-00-00' THEN 'Selesai' ELSE 'Sedang Dipinjam' END) AS status_transaksi,
                    COALESCE(u.username, p.nama_anggota) AS nama_anggota,
                    COALESCE(b.judul_buku, p.judul_buku) AS judul_buku,
                    p.tanggal_peminjaman,
                    p.tanggal_pengembalian,
                    p.kondisi_buku_saat_dipinjam,
                    p.kondisi_buku_saat_dikembalikan,
                    p.denda
                FROM `peminjaman` p
                LEFT JOIN `user` u ON p.id_user = u.id_user
                LEFT JOIN `buku` b ON p.id_buku = b.id_buku
                ORDER BY p.id_peminjaman DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getPeminjamanById(int $id): ?array {
        $sql = "SELECT 
                    p.id_peminjaman,
                    p.id_user,
                    p.id_buku,
                    COALESCE(p.status_transaksi, CASE WHEN p.tanggal_pengembalian != '' AND p.tanggal_pengembalian != '0000-00-00' THEN 'Selesai' ELSE 'Sedang Dipinjam' END) AS status_transaksi,
                    COALESCE(u.username, p.nama_anggota) AS nama_anggota,
                    COALESCE(b.judul_buku, p.judul_buku) AS judul_buku,
                    p.tanggal_peminjaman,
                    p.tanggal_pengembalian,
                    p.kondisi_buku_saat_dipinjam,
                    p.kondisi_buku_saat_dikembalikan,
                    p.denda
                FROM `peminjaman` p
                LEFT JOIN `user` u ON p.id_user = u.id_user
                LEFT JOIN `buku` b ON p.id_buku = b.id_buku
                WHERE p.id_peminjaman = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getPeminjamanByAnggota(string $namaAnggota): array {
        $sql = "SELECT 
                    p.id_peminjaman,
                    p.id_user,
                    p.id_buku,
                    COALESCE(p.status_transaksi, CASE WHEN p.tanggal_pengembalian != '' AND p.tanggal_pengembalian != '0000-00-00' THEN 'Selesai' ELSE 'Sedang Dipinjam' END) AS status_transaksi,
                    COALESCE(u.username, p.nama_anggota) AS nama_anggota,
                    COALESCE(b.judul_buku, p.judul_buku) AS judul_buku,
                    p.tanggal_peminjaman,
                    p.tanggal_pengembalian,
                    p.kondisi_buku_saat_dipinjam,
                    p.kondisi_buku_saat_dikembalikan,
                    p.denda
                FROM `peminjaman` p
                LEFT JOIN `user` u ON p.id_user = u.id_user
                LEFT JOIN `buku` b ON p.id_buku = b.id_buku
                WHERE (p.nama_anggota = ? OR u.username = ?)
                ORDER BY p.id_peminjaman DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$namaAnggota, $namaAnggota]);
        return $stmt->fetchAll();
    }

    public function getActivePeminjamanByAnggota(string $namaAnggota): array {
        $sql = "SELECT 
                    p.id_peminjaman,
                    p.id_user,
                    p.id_buku,
                    COALESCE(p.status_transaksi, CASE WHEN p.tanggal_pengembalian != '' AND p.tanggal_pengembalian != '0000-00-00' THEN 'Selesai' ELSE 'Sedang Dipinjam' END) AS status_transaksi,
                    COALESCE(u.username, p.nama_anggota) AS nama_anggota,
                    COALESCE(b.judul_buku, p.judul_buku) AS judul_buku,
                    p.tanggal_peminjaman,
                    p.tanggal_pengembalian,
                    p.kondisi_buku_saat_dipinjam,
                    p.kondisi_buku_saat_dikembalikan,
                    p.denda
                FROM `peminjaman` p
                LEFT JOIN `user` u ON p.id_user = u.id_user
                LEFT JOIN `buku` b ON p.id_buku = b.id_buku
                WHERE (p.nama_anggota = ? OR u.username = ?) 
                  AND (p.status_transaksi = 'Sedang Dipinjam' OR ((p.status_transaksi IS NULL OR p.status_transaksi = '') AND (p.tanggal_pengembalian = '' OR p.tanggal_pengembalian IS NULL OR p.tanggal_pengembalian = '0000-00-00')))
                ORDER BY p.id_peminjaman DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$namaAnggota, $namaAnggota]);
        return $stmt->fetchAll();
    }

    /**
     * Membuat Transaksi Peminjaman Baru.
     * Stok buku baik berkurang secara otomatis melalui database trigger (trg_setelah_pinjam_kurang_stok).
     * Jika stok buku 0, trigger (trg_sebelum_pinjam_cek_stok) akan membatalkan insert secara atomic.
     */
    public function createPeminjaman(array $data): bool {
        try {
            $idUser = isset($data['id_user']) && (int)$data['id_user'] > 0
                ? (int)$data['id_user']
                : $this->findUserId($data['nama_anggota'] ?? '');

            $idBuku = isset($data['id_buku']) && (int)$data['id_buku'] > 0
                ? (int)$data['id_buku']
                : $this->findBukuId($data['judul_buku'] ?? '');

            // Dapatkan username dan judul buku yang valid
            $userRow = $this->getUserById($idUser);
            $namaAnggota = $userRow['username'] ?? ($data['nama_anggota'] ?? '');

            $bukuRow = $this->getBukuById($idBuku);
            $judulBuku = $bukuRow['judul_buku'] ?? ($data['judul_buku'] ?? '');

            $status = $data['status_transaksi'] ?? 'Menunggu Konfirmasi';

            $stmt = $this->db->prepare("INSERT INTO `peminjaman` 
                (`id_user`, `id_buku`, `nama_anggota`, `judul_buku`, `tanggal_peminjaman`, `tanggal_pengembalian`, `status_transaksi`, `kondisi_buku_saat_dipinjam`, `kondisi_buku_saat_dikembalikan`, `denda`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([
                $idUser,
                $idBuku,
                $namaAnggota,
                $judulBuku,
                $data['tanggal_peminjaman'] ?? date('d-m-Y'),
                $data['tanggal_pengembalian'] ?? '',
                $status,
                $data['kondisi_buku_saat_dipinjam'] ?? 'Baik',
                $data['kondisi_buku_saat_dikembalikan'] ?? '',
                $data['denda'] ?? ''
            ]);
        } catch (PDOException $e) {
            // Tangkap pesan dari TRIGGER MySQL (SIGNAL SQLSTATE '45000')
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Memperbarui Transaksi Peminjaman / Pengembalian.
     * Stok buku bertambah secara otomatis melalui database trigger (trg_setelah_kembali_tambah_stok).
     */
    public function updatePeminjaman(int $id, array $data): bool {
        try {
            $status = $data['status_transaksi'] ?? 'Selesai';
            $stmt = $this->db->prepare("UPDATE `peminjaman` SET 
                `tanggal_pengembalian` = ?, `kondisi_buku_saat_dikembalikan` = ?, `denda` = ?, `status_transaksi` = ? 
                WHERE `id_peminjaman` = ?");
            return $stmt->execute([
                $data['tanggal_pengembalian'],
                $data['kondisi_buku_saat_dikembalikan'],
                $data['denda'] ?? 'Tidak ada',
                $status,
                $id
            ]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Admin Menyetujui Pengajuan Peminjaman Realtime.
     */
    public function konfirmasiPinjam(int $id): bool {
        try {
            $stmt = $this->db->prepare("UPDATE `peminjaman` SET `status_transaksi` = 'Sedang Dipinjam' WHERE `id_peminjaman` = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Admin Mengonfirmasi Penerimaan Pengembalian Buku Realtime.
     */
    public function konfirmasiKembali(int $id, array $data): bool {
        try {
            $tgl = !empty($data['tanggal_pengembalian']) ? $data['tanggal_pengembalian'] : date('d-m-Y');
            $kondisi = !empty($data['kondisi_buku_saat_dikembalikan']) ? $data['kondisi_buku_saat_dikembalikan'] : 'Baik';
            $denda = !empty($data['denda']) ? $data['denda'] : 'Tidak ada';

            $stmt = $this->db->prepare("UPDATE `peminjaman` SET 
                `tanggal_pengembalian` = ?, 
                `kondisi_buku_saat_dikembalikan` = ?, 
                `denda` = ?, 
                `status_transaksi` = 'Selesai' 
                WHERE `id_peminjaman` = ?");
            return $stmt->execute([$tgl, $kondisi, $denda, $id]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Hitung Pengajuan yang Menunggu Konfirmasi (Untuk Notifikasi Realtime Admin)
     */
    public function getPendingCount(): array {
        try {
            $stmt = $this->db->query("SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN `status_transaksi` = 'Menunggu Konfirmasi' THEN 1 ELSE 0 END) as pending_pinjam,
                SUM(CASE WHEN `status_transaksi` = 'Menunggu Konfirmasi Pengembalian' THEN 1 ELSE 0 END) as pending_kembali,
                MAX(id_peminjaman) as max_id
                FROM `peminjaman` 
                WHERE `status_transaksi` LIKE 'Menunggu Konfirmasi%'");
            $row = $stmt->fetch();
            return [
                'total' => (int)($row['total'] ?? 0),
                'pending_pinjam' => (int)($row['pending_pinjam'] ?? 0),
                'pending_kembali' => (int)($row['pending_kembali'] ?? 0),
                'max_id' => (int)($row['max_id'] ?? 0)
            ];
        } catch (Exception $e) {
            return ['total' => 0, 'pending_pinjam' => 0, 'pending_kembali' => 0, 'max_id' => 0];
        }
    }

    /**
     * Menghapus Transaksi Peminjaman.
     * Jika transaksi belum dikembalikan, trigger (trg_setelah_batal_pinjam) mengembalikan stok buku.
     */
    public function deletePeminjaman(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM `peminjaman` WHERE `id_peminjaman` = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function countPeminjaman(): int {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM `peminjaman`");
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    public function countPengembalian(): int {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM `peminjaman` WHERE `tanggal_pengembalian` != '' AND `tanggal_pengembalian` IS NOT NULL AND `tanggal_pengembalian` != '0000-00-00'");
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Mengambil Data Statistik Komprehensif untuk Dashboard Administrator:
     * - 8 Metrik Ringkasan (Cards)
     * - 6 Dataset Grafik Interaktif (Chart.js)
     */
    public function getDashboardAnalytics(): array {
        $analytics = [
            'metrics' => [
                'total_terpinjam'    => 0,
                'total_dikembalikan' => 0,
                'total_denda_rp'     => 0,
                'total_terlambat'    => 0,
                'total_rusak'        => 0,
                'total_hilang'       => 0,
                'total_judul'        => 0,
                'total_stok'         => 0,
                'total_stok_baik'    => 0,
                'total_stok_rusak'   => 0,
                'total_stok_hilang'  => 0,
            ],
            'charts' => [
                'monthly_loans' => [
                    'labels'       => ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                    'short_labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    'data'         => array_fill(0, 12, 0)
                ],
                'monthly_fines' => [
                    'labels'     => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    'monthly'    => array_fill(0, 12, 0),
                    'cumulative' => array_fill(0, 12, 0)
                ],
                'interest_categories' => [
                    'labels' => [],
                    'data'   => []
                ],
                'active_students' => [
                    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    'data'   => array_fill(0, 12, 0)
                ],
                'titles_by_year' => [
                    'labels' => [],
                    'data'   => []
                ],
                'monthly_stock' => [
                    'labels'    => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    'tersedia'  => array_fill(0, 12, 0),
                    'terpinjam' => array_fill(0, 12, 0)
                ]
            ]
        ];

        if (!$this->db) {
            return $analytics;
        }

        try {
            // 1. Data Master Buku & Stok Fisik
            $stmtBuku = $this->db->query("SELECT 
                COUNT(*) as total_judul,
                COALESCE(SUM(j_buku_baik), 0) as total_baik,
                COALESCE(SUM(j_buku_rusak), 0) as total_rusak,
                COALESCE(SUM(j_buku_hilang), 0) as total_hilang
            FROM `buku`");
            if ($rowBuku = $stmtBuku->fetch()) {
                $analytics['metrics']['total_judul']       = (int)$rowBuku['total_judul'];
                $analytics['metrics']['total_stok_baik']   = (int)$rowBuku['total_baik'];
                $analytics['metrics']['total_stok_rusak']  = (int)$rowBuku['total_rusak'];
                $analytics['metrics']['total_stok_hilang'] = (int)$rowBuku['total_hilang'];
                $analytics['metrics']['total_stok']        = (int)($rowBuku['total_baik'] + $rowBuku['total_rusak'] + $rowBuku['total_hilang']);
                $analytics['metrics']['total_hilang']      = (int)$rowBuku['total_hilang'];
            }

            // 2. Judul Buku Berdasarkan Tahun Terbit
            $stmtTahun = $this->db->query("SELECT tahun_terbit, COUNT(*) as jml 
                FROM `buku` 
                WHERE tahun_terbit IS NOT NULL AND tahun_terbit != '' 
                GROUP BY tahun_terbit 
                ORDER BY tahun_terbit ASC");
            $tahunRows = $stmtTahun->fetchAll();
            foreach ($tahunRows as $tRow) {
                $analytics['charts']['titles_by_year']['labels'][] = (string)$tRow['tahun_terbit'];
                $analytics['charts']['titles_by_year']['data'][]   = (int)$tRow['jml'];
            }

            // 3. Data Sirkulasi Peminjaman & Pengembalian
            $stmtTrans = $this->db->query("SELECT p.*, 
                    COALESCE(k.nama_kategori, b.kategori_buku, 'Umum') AS nama_kategori_buku
                FROM `peminjaman` p
                LEFT JOIN `buku` b ON p.id_buku = b.id_buku
                LEFT JOIN `kategori` k ON b.id_kategori = k.id_kategori
                ORDER BY p.id_peminjaman ASC");
            $transactions = $stmtTrans->fetchAll();

            $monthlyStudents = [];
            for ($m = 0; $m < 12; $m++) {
                $monthlyStudents[$m] = [];
            }
            $categoryCounts = [];
            $lostFromReturn = 0;

            foreach ($transactions as $t) {
                $isReturned = (!empty($t['tanggal_pengembalian']) && $t['tanggal_pengembalian'] !== '0000-00-00' && ($t['status_transaksi'] === 'Selesai' || !empty($t['tanggal_pengembalian'])));
                if ($isReturned) {
                    $analytics['metrics']['total_dikembalikan']++;
                } else {
                    $analytics['metrics']['total_terpinjam']++;
                }

                // Nominal Denda
                $dendaVal = 0;
                $rawDenda = $t['denda'] ?? '';
                if (!empty($rawDenda) && $rawDenda !== 'Tidak ada') {
                    $cleanDenda = preg_replace('/[^0-9]/', '', explode('(', $rawDenda)[0] ?? $rawDenda);
                    $dendaVal = is_numeric($cleanDenda) ? (int)$cleanDenda : 0;
                    $analytics['metrics']['total_denda_rp'] += $dendaVal;
                }

                // Pengembalian Terlambat
                if (stripos($rawDenda, 'terlambat') !== false || stripos($t['kondisi_buku_saat_dikembalikan'] ?? '', 'terlambat') !== false) {
                    $analytics['metrics']['total_terlambat']++;
                }

                // Pengembalian Rusak
                if (stripos($t['kondisi_buku_saat_dikembalikan'] ?? '', 'rusak') !== false || stripos($rawDenda, 'rusak') !== false || stripos($rawDenda, 'kerusakan') !== false) {
                    $analytics['metrics']['total_rusak']++;
                }

                // Buku Hilang dari Pengembalian
                if (stripos($t['kondisi_buku_saat_dikembalikan'] ?? '', 'hilang') !== false || stripos($rawDenda, 'hilang') !== false) {
                    $lostFromReturn++;
                }

                // Parse Bulan Peminjaman
                $pinjamMonthIdx = -1;
                $pinjamTgl = trim($t['tanggal_peminjaman'] ?? '');
                if (preg_match('/^(\d{1,2})[-|\/](\d{1,2})[-|\/](\d{4})$/', $pinjamTgl, $matches)) {
                    $pinjamMonthIdx = (int)$matches[2] - 1;
                } elseif (preg_match('/^(\d{4})[-|\/](\d{1,2})[-|\/](\d{1,2})$/', $pinjamTgl, $matches)) {
                    $pinjamMonthIdx = (int)$matches[2] - 1;
                }

                if ($pinjamMonthIdx >= 0 && $pinjamMonthIdx < 12) {
                    // Peminjaman bulanan
                    $analytics['charts']['monthly_loans']['data'][$pinjamMonthIdx]++;

                    // Keaktifan siswa saat meminjam
                    $userKey = !empty($t['id_user']) ? (string)$t['id_user'] : ($t['nama_anggota'] ?? 'guest');
                    $monthlyStudents[$pinjamMonthIdx][$userKey] = true;
                }

                // Parse Bulan Pengembalian
                $kembaliMonthIdx = -1;
                $kembaliTgl = trim($t['tanggal_pengembalian'] ?? '');
                if ($isReturned && !empty($kembaliTgl)) {
                    if (preg_match('/^(\d{1,2})[-|\/](\d{1,2})[-|\/](\d{4})$/', $kembaliTgl, $matches)) {
                        $kembaliMonthIdx = (int)$matches[2] - 1;
                    } elseif (preg_match('/^(\d{4})[-|\/](\d{1,2})[-|\/](\d{1,2})$/', $kembaliTgl, $matches)) {
                        $kembaliMonthIdx = (int)$matches[2] - 1;
                    }
                }

                // Denda per bulan
                $dendaTargetMonth = ($kembaliMonthIdx >= 0 && $kembaliMonthIdx < 12) ? $kembaliMonthIdx : $pinjamMonthIdx;
                if ($dendaTargetMonth >= 0 && $dendaTargetMonth < 12 && $dendaVal > 0) {
                    $analytics['charts']['monthly_fines']['monthly'][$dendaTargetMonth] += $dendaVal;
                }

                // Keaktifan siswa saat mengembalikan
                if ($kembaliMonthIdx >= 0 && $kembaliMonthIdx < 12) {
                    $userKey = !empty($t['id_user']) ? (string)$t['id_user'] : ($t['nama_anggota'] ?? 'guest');
                    $monthlyStudents[$kembaliMonthIdx][$userKey] = true;
                }

                // Minat Baca Kategori Buku
                $kat = !empty($t['nama_kategori_buku']) ? trim($t['nama_kategori_buku']) : 'Umum';
                $categoryCounts[$kat] = ($categoryCounts[$kat] ?? 0) + 1;
            }

            if ($lostFromReturn > $analytics['metrics']['total_hilang']) {
                $analytics['metrics']['total_hilang'] = $lostFromReturn;
            }

            // Hitung Akumulasi Denda & Stok Bulanan (Tersedia vs Terpinjam)
            $runningFineTotal = 0;
            $totalStokPerpus = $analytics['metrics']['total_stok'];
            for ($m = 0; $m < 12; $m++) {
                $runningFineTotal += $analytics['charts']['monthly_fines']['monthly'][$m];
                $analytics['charts']['monthly_fines']['cumulative'][$m] = $runningFineTotal;
                $analytics['charts']['active_students']['data'][$m] = count($monthlyStudents[$m]);

                // Stok bulanan: Ada di rak vs Terpinjam
                $terpinjamBulan = $analytics['charts']['monthly_loans']['data'][$m];
                $tersediaBulan  = max(0, $totalStokPerpus - $terpinjamBulan);
                $analytics['charts']['monthly_stock']['terpinjam'][$m] = $terpinjamBulan;
                $analytics['charts']['monthly_stock']['tersedia'][$m]  = $tersediaBulan;
            }

            // Minat Baca terurut
            arsort($categoryCounts);
            foreach ($categoryCounts as $katName => $katTotal) {
                $analytics['charts']['interest_categories']['labels'][] = $katName;
                $analytics['charts']['interest_categories']['data'][]   = $katTotal;
            }

        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
        }

        return $analytics;
    }

    /**
     * Mengambil Data Peminjaman Harian dalam Sebulan untuk Grafik Peminjaman
     */
    public function getMonthlyDailyLoans(?int $targetMonth = null, ?int $targetYear = null): array {
        if (!$this->db) {
            return [
                'month_name' => date('F Y'),
                'month'      => (int)date('m'),
                'year'       => (int)date('Y'),
                'days'       => range(1, 31),
                'counts'     => array_fill(0, 31, 0),
                'total'      => 0,
                'max_day'    => 1,
                'max_count'  => 0,
                'avg_day'    => 0
            ];
        }

        try {
            $stmt = $this->db->query("SELECT tanggal_peminjaman FROM `peminjaman` ORDER BY `id_peminjaman` DESC");
            $rows = $stmt->fetchAll();

            // Tentukan target bulan dan tahun jika tidak diberikan
            if ($targetMonth === null || $targetYear === null) {
                $curM = (int)date('n');
                $curY = (int)date('Y');
                $found = false;

                foreach ($rows as $r) {
                    if (preg_match('/^(\d{1,2})[-|\/](\d{1,2})[-|\/](\d{4})$/', trim($r['tanggal_peminjaman']), $m)) {
                        if ((int)$m[2] === $curM && (int)$m[3] === $curY) {
                            $found = true;
                            break;
                        }
                    }
                }

                if ($found) {
                    $targetMonth = $curM;
                    $targetYear  = $curY;
                } else {
                    if (!empty($rows[0]) && preg_match('/^(\d{1,2})[-|\/](\d{1,2})[-|\/](\d{4})$/', trim($rows[0]['tanggal_peminjaman']), $m)) {
                        $targetMonth = (int)$m[2];
                        $targetYear  = (int)$m[3];
                    } else {
                        $targetMonth = $curM;
                        $targetYear  = $curY;
                    }
                }
            }

            $daysInMonth = function_exists('cal_days_in_month') 
                ? cal_days_in_month(CAL_GREGORIAN, $targetMonth, $targetYear)
                : (int)date('t', strtotime("{$targetYear}-{$targetMonth}-01"));
            
            $dayLabels   = [];
            $dailyCounts = [];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dayLabels[] = "Tgl " . $d;
                $dailyCounts[$d] = 0;
            }

            $totalLoans = 0;
            foreach ($rows as $r) {
                if (preg_match('/^(\d{1,2})[-|\/](\d{1,2})[-|\/](\d{4})$/', trim($r['tanggal_peminjaman']), $m)) {
                    $d  = (int)$m[1];
                    $mo = (int)$m[2];
                    $y  = (int)$m[3];
                    if ($mo === $targetMonth && $y === $targetYear && $d >= 1 && $d <= $daysInMonth) {
                        $dailyCounts[$d]++;
                        $totalLoans++;
                    }
                }
            }

            $maxDay = 1;
            $maxCount = 0;
            foreach ($dailyCounts as $d => $c) {
                if ($c > $maxCount) {
                    $maxCount = $c;
                    $maxDay = $d;
                }
            }

            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            return [
                'month_name' => ($monthNames[$targetMonth] ?? '') . " " . $targetYear,
                'month'      => $targetMonth,
                'year'       => $targetYear,
                'days'       => $dayLabels,
                'counts'     => array_values($dailyCounts),
                'total'      => $totalLoans,
                'max_day'    => $maxDay,
                'max_count'  => $maxCount,
                'avg_day'    => round($totalLoans / max(1, $daysInMonth), 2)
            ];
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return [
                'month_name' => date('F Y'),
                'month'      => (int)date('m'),
                'year'       => (int)date('Y'),
                'days'       => range(1, 31),
                'counts'     => array_fill(0, 31, 0),
                'total'      => 0,
                'max_day'    => 1,
                'max_count'  => 0,
                'avg_day'    => 0
            ];
        }
    }

    /**
     * Data Grafik Pengembalian Harian dalam Sebulan untuk Grafik Pengembalian
     */
    public function getMonthlyDailyReturns(?int $targetMonth = null, ?int $targetYear = null): array {
        if (!$this->db) {
            return [
                'month_name'   => date('F Y'),
                'month'        => (int)date('m'),
                'year'         => (int)date('Y'),
                'days'         => range(1, 31),
                'counts'       => array_fill(0, 31, 0),
                'counts_baik'  => array_fill(0, 31, 0),
                'counts_rusak' => array_fill(0, 31, 0),
                'total'        => 0,
                'total_denda'  => 0,
                'max_day'      => 1,
                'max_count'    => 0
            ];
        }

        try {
            $stmt = $this->db->query("SELECT * FROM `peminjaman` WHERE `tanggal_pengembalian` != '' AND `tanggal_pengembalian` IS NOT NULL AND `tanggal_pengembalian` != '0000-00-00' ORDER BY `id_peminjaman` DESC");
            $rows = $stmt->fetchAll();

            if ($targetMonth === null || $targetYear === null) {
                $curM = (int)date('n');
                $curY = (int)date('Y');
                $found = false;

                foreach ($rows as $r) {
                    if (preg_match('/^(\d{1,2})[-|\/](\d{1,2})[-|\/](\d{4})$/', trim($r['tanggal_pengembalian']), $m)) {
                        if ((int)$m[2] === $curM && (int)$m[3] === $curY) {
                            $found = true;
                            break;
                        }
                    }
                }

                if ($found) {
                    $targetMonth = $curM;
                    $targetYear  = $curY;
                } else {
                    if (!empty($rows[0]) && preg_match('/^(\d{1,2})[-|\/](\d{1,2})[-|\/](\d{4})$/', trim($rows[0]['tanggal_pengembalian']), $m)) {
                        $targetMonth = (int)$m[2];
                        $targetYear  = (int)$m[3];
                    } else {
                        $targetMonth = $curM;
                        $targetYear  = $curY;
                    }
                }
            }

            $daysInMonth = function_exists('cal_days_in_month') 
                ? cal_days_in_month(CAL_GREGORIAN, $targetMonth, $targetYear)
                : (int)date('t', strtotime("{$targetYear}-{$targetMonth}-01"));

            $dayLabels   = [];
            $dailyCounts = [];
            $dailyBaik   = [];
            $dailyRusak  = [];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dayLabels[]     = "Tgl " . $d;
                $dailyCounts[$d] = 0;
                $dailyBaik[$d]   = 0;
                $dailyRusak[$d]  = 0;
            }

            $totalReturns = 0;
            $totalDenda   = 0;

            foreach ($rows as $r) {
                if (preg_match('/^(\d{1,2})[-|\/](\d{1,2})[-|\/](\d{4})$/', trim($r['tanggal_pengembalian']), $m)) {
                    $d  = (int)$m[1];
                    $mo = (int)$m[2];
                    $y  = (int)$m[3];
                    if ($mo === $targetMonth && $y === $targetYear && $d >= 1 && $d <= $daysInMonth) {
                        $dailyCounts[$d]++;
                        $totalReturns++;

                        if (stripos($r['kondisi_buku_saat_dikembalikan'] ?? '', 'rusak') !== false || stripos($r['kondisi_buku_saat_dikembalikan'] ?? '', 'hilang') !== false) {
                            $dailyRusak[$d]++;
                        } else {
                            $dailyBaik[$d]++;
                        }

                        $rawDenda = $r['denda'] ?? '';
                        if (!empty($rawDenda) && $rawDenda !== 'Tidak ada') {
                            $clean = preg_replace('/[^0-9]/', '', explode('(', $rawDenda)[0] ?? $rawDenda);
                            $totalDenda += is_numeric($clean) ? (int)$clean : 0;
                        }
                    }
                }
            }

            $maxDay = 1;
            $maxCount = 0;
            foreach ($dailyCounts as $d => $c) {
                if ($c > $maxCount) {
                    $maxCount = $c;
                    $maxDay = $d;
                }
            }

            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            return [
                'month_name'   => ($monthNames[$targetMonth] ?? '') . " " . $targetYear,
                'month'        => $targetMonth,
                'year'         => $targetYear,
                'days'         => $dayLabels,
                'counts'       => array_values($dailyCounts),
                'counts_baik'  => array_values($dailyBaik),
                'counts_rusak' => array_values($dailyRusak),
                'total'        => $totalReturns,
                'total_denda'  => $totalDenda,
                'max_day'      => $maxDay,
                'max_count'    => $maxCount
            ];
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return [
                'month_name'   => date('F Y'),
                'month'        => (int)date('m'),
                'year'         => (int)date('Y'),
                'days'         => range(1, 31),
                'counts'       => array_fill(0, 31, 0),
                'counts_baik'  => array_fill(0, 31, 0),
                'counts_rusak' => array_fill(0, 31, 0),
                'total'        => 0,
                'total_denda'  => 0,
                'max_day'      => 1,
                'max_count'    => 0
            ];
        }
    }

    /**
     * Mengambil Laporan Riwayat Sirkulasi Total dengan Filter Multi-Kriteria
     */
    public function getFilteredRiwayat(array $filters = []): array {
        if (!$this->db) {
            return ['list' => [], 'summary' => []];
        }

        try {
            $sql = "SELECT 
                        p.*,
                        u.fullname,
                        u.nis,
                        u.kelas,
                        u.kode_user,
                        b.isbn,
                        b.pengarang,
                        b.tahun_terbit,
                        COALESCE(k.nama_kategori, b.kategori_buku, 'Umum') AS kategori_buku
                    FROM `peminjaman` p
                    LEFT JOIN `user` u ON p.id_user = u.id_user
                    LEFT JOIN `buku` b ON p.id_buku = b.id_buku
                    LEFT JOIN `kategori` k ON b.id_kategori = k.id_kategori
                    WHERE 1=1";

            $params = [];

            // 1. Filter Jenis Transaksi
            $jenis = trim($filters['jenis'] ?? '');
            if ($jenis === 'pinjam') {
                $sql .= " AND (p.status_transaksi = 'Sedang Dipinjam' OR p.tanggal_pengembalian = '' OR p.tanggal_pengembalian IS NULL)";
            } elseif ($jenis === 'kembali') {
                $sql .= " AND (p.status_transaksi = 'Selesai' OR (p.tanggal_pengembalian != '' AND p.tanggal_pengembalian IS NOT NULL AND p.tanggal_pengembalian != '0000-00-00'))";
            }

            // 2. Filter Siswa
            if (!empty($filters['id_user'])) {
                $sql .= " AND p.id_user = ?";
                $params[] = (int)$filters['id_user'];
            }

            // 3. Filter Buku
            if (!empty($filters['id_buku'])) {
                $sql .= " AND p.id_buku = ?";
                $params[] = (int)$filters['id_buku'];
            }

            // 4. Filter Kondisi Buku
            $kondisi = trim($filters['kondisi'] ?? '');
            if ($kondisi === 'Baik') {
                $sql .= " AND (p.kondisi_buku_saat_dikembalikan = 'Baik' OR (p.kondisi_buku_saat_dipinjam = 'Baik' AND (p.kondisi_buku_saat_dikembalikan = '' OR p.kondisi_buku_saat_dikembalikan IS NULL)))";
            } elseif ($kondisi === 'Rusak') {
                $sql .= " AND (p.kondisi_buku_saat_dikembalikan = 'Rusak' OR p.kondisi_buku_saat_dipinjam = 'Rusak' OR p.denda LIKE '%Rusak%' OR p.denda LIKE '%Kerusakan%')";
            } elseif ($kondisi === 'Hilang') {
                $sql .= " AND (p.kondisi_buku_saat_dikembalikan = 'Hilang' OR p.denda LIKE '%Hilang%')";
            }

            // 5. Filter Denda
            $dendaFilter = trim($filters['denda'] ?? '');
            if ($dendaFilter === 'ada') {
                $sql .= " AND p.denda != '' AND p.denda != 'Tidak ada' AND p.denda IS NOT NULL";
            } elseif ($dendaFilter === 'bebas') {
                $sql .= " AND (p.denda = '' OR p.denda = 'Tidak ada' OR p.denda IS NULL)";
            } elseif ($dendaFilter === 'terlambat') {
                $sql .= " AND p.denda LIKE '%Terlambat%'";
            } elseif ($dendaFilter === 'rusak') {
                $sql .= " AND (p.denda LIKE '%Rusak%' OR p.denda LIKE '%Kerusakan%')";
            } elseif ($dendaFilter === 'hilang') {
                $sql .= " AND p.denda LIKE '%Hilang%'";
            }

            $sql .= " ORDER BY p.id_peminjaman DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            // Hitung Rekapitulasi Summary
            $summary = [
                'total_transaksi' => count($rows),
                'total_pinjam'    => 0,
                'total_kembali'   => 0,
                'total_denda_rp'  => 0,
                'total_rusak'     => 0,
                'total_hilang'    => 0
            ];

            foreach ($rows as $r) {
                $isReturned = (!empty($r['tanggal_pengembalian']) && $r['tanggal_pengembalian'] !== '0000-00-00' && ($r['status_transaksi'] === 'Selesai' || !empty($r['tanggal_pengembalian'])));
                if ($isReturned) {
                    $summary['total_kembali']++;
                } else {
                    $summary['total_pinjam']++;
                }

                $rawDenda = $r['denda'] ?? '';
                if (!empty($rawDenda) && $rawDenda !== 'Tidak ada') {
                    $clean = preg_replace('/[^0-9]/', '', explode('(', $rawDenda)[0] ?? $rawDenda);
                    $summary['total_denda_rp'] += is_numeric($clean) ? (int)$clean : 0;
                }

                if (stripos($r['kondisi_buku_saat_dikembalikan'] ?? '', 'rusak') !== false || stripos($rawDenda, 'rusak') !== false || stripos($rawDenda, 'kerusakan') !== false) {
                    $summary['total_rusak']++;
                }

                if (stripos($r['kondisi_buku_saat_dikembalikan'] ?? '', 'hilang') !== false || stripos($rawDenda, 'hilang') !== false) {
                    $summary['total_hilang']++;
                }
            }

            return [
                'list'    => $rows,
                'summary' => $summary
            ];
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return ['list' => [], 'summary' => []];
        }
    }

    // ==========================================
    // METODE SIRKULASI PERPUSTAKAAN (ADMIN & SCANNER)
    // ==========================================

    public function findBukuByBarcodeOrIsbn(string $code): ?array {
        $code = trim($code);
        if ($code === '') return null;
        $clean = preg_replace('/[^0-9A-Za-z]/', '', $code);

        $sql = "SELECT b.*, 
                    COALESCE(k.nama_kategori, b.kategori_buku) AS kategori_buku, 
                    COALESCE(p.nama_penerbit, b.penerbit_buku) AS penerbit_buku 
                FROM `buku` b
                LEFT JOIN `kategori` k ON b.id_kategori = k.id_kategori
                LEFT JOIN `penerbit` p ON b.id_penerbit = p.id_penerbit
                WHERE REPLACE(REPLACE(b.isbn, '-', ''), ' ', '') = ?
                   OR b.isbn = ?
                   OR b.id_buku = ?
                   OR b.judul_buku LIKE ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clean, $code, is_numeric($code) ? (int)$code : 0, "%{$code}%"]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findBukuByJudulDanPengarang(string $judul, string $pengarang): ?array {
        $judul = trim($judul);
        $pengarang = trim($pengarang);
        if ($judul === '') return null;

        $stmt = $this->db->prepare("SELECT b.*, 
                    COALESCE(k.nama_kategori, b.kategori_buku) AS kategori_buku, 
                    COALESCE(p.nama_penerbit, b.penerbit_buku) AS penerbit_buku 
                FROM `buku` b
                LEFT JOIN `kategori` k ON b.id_kategori = k.id_kategori
                LEFT JOIN `penerbit` p ON b.id_penerbit = p.id_penerbit
                WHERE LOWER(TRIM(b.judul_buku)) = LOWER(?)
                  AND (LOWER(TRIM(b.pengarang)) = LOWER(?) OR ? = '')
                LIMIT 1");
        $stmt->execute([$judul, $pengarang, $pengarang]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getActiveLoanCountByUser(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM `peminjaman` WHERE `id_user` = ? AND `status_transaksi` = 'Sedang Dipinjam'");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    public function getActiveLoansByUser(int $userId): array {
        $stmt = $this->db->prepare("SELECT p.*, b.judul_buku, b.isbn, b.pengarang 
            FROM `peminjaman` p 
            LEFT JOIN `buku` b ON p.id_buku = b.id_buku 
            WHERE p.id_user = ? AND p.status_transaksi = 'Sedang Dipinjam' 
            ORDER BY p.id_peminjaman DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getActiveLoanByBuku(int $bukuId): ?array {
        $stmt = $this->db->prepare("SELECT p.*, u.fullname, u.nis, u.kelas, u.kode_user, b.judul_buku, b.isbn, b.pengarang 
            FROM `peminjaman` p 
            LEFT JOIN `user` u ON p.id_user = u.id_user 
            LEFT JOIN `buku` b ON p.id_buku = b.id_buku 
            WHERE p.id_buku = ? AND p.status_transaksi = 'Sedang Dipinjam' 
            ORDER BY p.id_peminjaman ASC LIMIT 1");
        $stmt->execute([$bukuId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAllActiveLoans(): array {
        $stmt = $this->db->query("SELECT p.*, u.fullname, u.nis, u.kelas, u.kode_user, b.judul_buku, b.isbn, b.pengarang, b.j_buku_baik 
            FROM `peminjaman` p 
            LEFT JOIN `user` u ON p.id_user = u.id_user 
            LEFT JOIN `buku` b ON p.id_buku = b.id_buku 
            WHERE p.status_transaksi = 'Sedang Dipinjam' 
            ORDER BY p.id_peminjaman DESC");
        return $stmt->fetchAll();
    }

    public function createPeminjamanAdmin(int $userId, int $bukuId, string $tanggalPinjam, string $kondisi = 'Baik'): array {
        try {
            $activeCount = $this->getActiveLoanCountByUser($userId);
            if ($activeCount >= 2) {
                return [
                    'success' => false, 
                    'message' => 'Peminjaman ditolak! Siswa sudah meminjam 2 buku dan belum dikembalikan. Batas maksimal adalah 2 buku.'
                ];
            }

            $buku = $this->getBukuById($bukuId);
            if (!$buku || (int)$buku['j_buku_baik'] <= 0) {
                return [
                    'success' => false,
                    'message' => 'Peminjaman gagal! Stok buku baik sedang kosong (0).'
                ];
            }

            $user = $this->getUserById($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Data siswa tidak ditemukan.'
                ];
            }

            $stmt = $this->db->prepare("INSERT INTO `peminjaman` 
                (`id_user`, `id_buku`, `nama_anggota`, `judul_buku`, `tanggal_peminjaman`, `tanggal_pengembalian`, `status_transaksi`, `kondisi_buku_saat_dipinjam`, `kondisi_buku_saat_dikembalikan`, `denda`) 
                VALUES (?, ?, ?, ?, ?, '', 'Sedang Dipinjam', ?, '', 'Tidak ada')");
            $success = $stmt->execute([
                $userId,
                $bukuId,
                $user['username'],
                $buku['judul_buku'],
                $tanggalPinjam ?: date('d-m-Y'),
                $kondisi
            ]);

            if ($success) {
                $newId = (int)$this->db->lastInsertId();
                return [
                    'success' => true,
                    'id_peminjaman' => $newId,
                    'message' => 'Peminjaman buku berhasil dicatat.'
                ];
            } else {
                return ['success' => false, 'message' => 'Gagal mencatat peminjaman ke database.'];
            }
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function prosesPengembalianAdmin(int $peminjamanId, string $kondisiBuku, float $dendaKeterlambatan, float $dendaKerusakan, string $catatan = ''): bool {
        try {
            $totalDenda = $dendaKeterlambatan + $dendaKerusakan;
            if ($totalDenda > 0) {
                $dendaParts = [];
                if ($dendaKeterlambatan > 0) {
                    $dendaParts[] = "Terlambat: Rp " . number_format($dendaKeterlambatan, 0, ',', '.');
                }
                if ($dendaKerusakan > 0) {
                    if ($kondisiBuku === 'Hilang') {
                        $dendaParts[] = "Ganti Buku Hilang: Rp " . number_format($dendaKerusakan, 0, ',', '.');
                    } else {
                        $dendaParts[] = "Kerusakan: Rp " . number_format($dendaKerusakan, 0, ',', '.');
                    }
                }
                if (!empty($catatan)) {
                    $dendaParts[] = $catatan;
                }
                $dendaStr = "Rp " . number_format($totalDenda, 0, ',', '.') . " (" . implode(', ', $dendaParts) . ")";
            } else {
                $dendaStr = 'Tidak ada';
            }

            $stmt = $this->db->prepare("UPDATE `peminjaman` SET 
                `tanggal_pengembalian` = ?, 
                `kondisi_buku_saat_dikembalikan` = ?, 
                `denda` = ?, 
                `status_transaksi` = 'Selesai' 
                WHERE `id_peminjaman` = ?");
            $success = $stmt->execute([
                date('d-m-Y'),
                $kondisiBuku,
                $dendaStr,
                $peminjamanId
            ]);

            if ($success) {
                // Update inventaris fisik buku jika rusak atau hilang
                $stmtB = $this->db->prepare("SELECT id_buku FROM `peminjaman` WHERE id_peminjaman = ? LIMIT 1");
                $stmtB->execute([$peminjamanId]);
                $idBuku = $stmtB->fetchColumn();

                if ($idBuku) {
                    if ($kondisiBuku === 'Hilang') {
                        $this->db->exec("UPDATE `buku` SET `j_buku_hilang` = `j_buku_hilang` + 1 WHERE `id_buku` = " . (int)$idBuku);
                    } elseif ($kondisiBuku === 'Rusak') {
                        $this->db->exec("UPDATE `buku` SET `j_buku_rusak` = `j_buku_rusak` + 1 WHERE `id_buku` = " . (int)$idBuku);
                    }
                }
            }

            return $success;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function prosesPengembalianTanpaIzin(int $bukuId, float $denda, string $keterangan, ?int $userId = null): bool {
        try {
            $buku = $this->getBukuById($bukuId);
            if (!$buku) return false;

            $this->db->exec("UPDATE `buku` SET `j_buku_baik` = `j_buku_baik` + 1 WHERE `id_buku` = {$bukuId}");

            $uId = $userId ?: 1;
            $user = $this->getUserById($uId);
            $nama = $user ? $user['username'] : 'Siswa (Tanpa Izin)';

            $dendaStr = $denda > 0 ? "Rp " . number_format($denda, 0, ',', '.') . " (Sanksi: Pinjam Tanpa Izin - {$keterangan})" : "Tidak ada (Dikembalikan)";

            $stmt = $this->db->prepare("INSERT INTO `peminjaman` 
                (`id_user`, `id_buku`, `nama_anggota`, `judul_buku`, `tanggal_peminjaman`, `tanggal_pengembalian`, `status_transaksi`, `kondisi_buku_saat_dipinjam`, `kondisi_buku_saat_dikembalikan`, `denda`) 
                VALUES (?, ?, ?, ?, ?, ?, 'Selesai', 'Baik', 'Baik', ?)");
            return $stmt->execute([
                $uId,
                $bukuId,
                $nama,
                $buku['judul_buku'],
                date('d-m-Y'),
                date('d-m-Y'),
                $dendaStr
            ]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function getPeminjamanDetail(int $id): ?array {
        $sql = "SELECT p.*, 
                    u.fullname, u.nis, u.kelas, u.alamat as alamat_siswa, u.kode_user,
                    b.isbn, b.pengarang, b.tahun_terbit, b.kategori_buku, b.penerbit_buku
                FROM `peminjaman` p
                LEFT JOIN `user` u ON p.id_user = u.id_user
                LEFT JOIN `buku` b ON p.id_buku = b.id_buku
                WHERE p.id_peminjaman = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ==========================================
    // IDENTITAS TABLE METHODS
    // ==========================================
    public function getIdentitas(): array {
        if (!$this->db) {
            return [
                'id_identitas' => 1,
                'nama_app'     => 'Perpustakaan SMP Negeri 1 Kota Gajah',
                'alamat_app'   => 'Jl. Mansyur No.02, Sritejokencono, Kec. Kota Gajah, Kab. Lampung Tengah Prov. Lampung',
                'email_app'    => 'smpn1kotagajah@gmail.com',
                'nomor_hp'     => '085381768123',
                'latitude'     => '-4.9754',
                'longitude'    => '105.3188'
            ];
        }
        $stmt = $this->db->query("SELECT * FROM `identitas` LIMIT 1");
        $row = $stmt->fetch();
        if (!$row) {
            return [
                'id_identitas' => 1,
                'nama_app'     => 'Perpustakaan SMP Negeri 1 Kota Gajah',
                'alamat_app'   => 'Jl. Mansyur No.02, Sritejokencono, Kec. Kota Gajah, Kab. Lampung Tengah Prov. Lampung',
                'email_app'    => 'smpn1kotagajah@gmail.com',
                'nomor_hp'     => '085381768123',
                'latitude'     => '-4.9754',
                'longitude'    => '105.3188'
            ];
        }
        if (empty($row['latitude'])) {
            $row['latitude'] = '-4.9754';
        }
        if (empty($row['longitude'])) {
            $row['longitude'] = '105.3188';
        }
        return $row;
    }

    public function updateIdentitas(int $id, array $data): bool {
        try {
            // Cek apakah kolom latitude & longitude tersedia
            $hasLat = false;
            try {
                $check = $this->db->query("SHOW COLUMNS FROM `identitas` LIKE 'latitude'");
                $hasLat = (bool)$check->fetch();
            } catch (Exception $e) {}

            if ($hasLat && isset($data['latitude'], $data['longitude'])) {
                $stmt = $this->db->prepare("UPDATE `identitas` SET 
                    `nama_app` = ?, `alamat_app` = ?, `email_app` = ?, `nomor_hp` = ?, `latitude` = ?, `longitude` = ? 
                    WHERE `id_identitas` = ?");
                return $stmt->execute([
                    $data['nama_app'],
                    $data['alamat_app'],
                    $data['email_app'],
                    $data['nomor_hp'],
                    $data['latitude'] ?? '-4.9754',
                    $data['longitude'] ?? '105.3188',
                    $id
                ]);
            } else {
                $stmt = $this->db->prepare("UPDATE `identitas` SET 
                    `nama_app` = ?, `alamat_app` = ?, `email_app` = ?, `nomor_hp` = ? 
                    WHERE `id_identitas` = ?");
                return $stmt->execute([
                    $data['nama_app'],
                    $data['alamat_app'],
                    $data['email_app'],
                    $data['nomor_hp'],
                    $id
                ]);
            }
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }
}
