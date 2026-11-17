<?php
// Setup / Import Script for db_perpustakaan (Advanced Database Edition)
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah

$config = require __DIR__ . '/config/database.php';
$host = $config['host'];
$port = $config['port'];
$user = $config['username'];
$pass = $config['password'];
$db   = $config['database'];

echo "======================================================================\n";
echo " Setup Basis Data Lanjut: {$db}\n";
echo " Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah\n";
echo " Fitur: Foreign Key, Referential Integrity, Indexing, View, & Triggers\n";
echo "======================================================================\n\n";

try {
    echo "[1/4] Menghubungkan ke MySQL server ({$host}:{$port})...\n";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ];
    if (!empty($config['ssl'])) {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, $options);
    echo "      Berhasil terhubung ke MySQL Server!\n\n";

    echo "[2/4] Membuat / Menyiapkan database `{$db}`...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$db}`");
    echo "      Database `{$db}` aktif.\n\n";

    echo "[3/4] Membaca skrip SQL database/db_perpustakaan.sql...\n";
    $sqlFile = __DIR__ . '/database/db_perpustakaan.sql';
    if (!file_exists($sqlFile)) {
        die("ERROR: File SQL tidak ditemukan di {$sqlFile}\n");
    }
    $rawSql = file_get_contents($sqlFile);
    echo "      Ukuran berkas SQL: " . number_format(strlen($rawSql)) . " bytes.\n\n";

    echo "[4/4] Mengeksekusi tabel, relasi FK, index, view, dan trigger...\n";

    // Parser khusus untuk menangani multi-delimiter (termasuk DELIMITER $$ untuk Trigger)
    $lines = explode("\n", $rawSql);
    $delimiter = ';';
    $currentQuery = '';
    $executedCount = 0;
    $triggerCount = 0;

    foreach ($lines as $line) {
        $trimmedLine = trim($line);

        // Abaikan komentar murni jika belum ada kueri berjalan
        if (empty($currentQuery) && (str_starts_with($trimmedLine, '--') || str_starts_with($trimmedLine, '/*'))) {
            continue;
        }

        // Cek perubahan DELIMITER (misal: DELIMITER $$ atau DELIMITER ;)
        if (preg_match('/^DELIMITER\s+(.+)$/i', $trimmedLine, $matches)) {
            $delimiter = trim($matches[1]);
            continue;
        }

        $currentQuery .= $line . "\n";

        // Cek apakah query saat ini diakhiri dengan delimiter aktif
        if (str_ends_with(rtrim($trimmedLine), $delimiter)) {
            // Hapus delimiter dari ujung query
            $len = strlen($delimiter);
            $queryToExecute = trim(substr(rtrim($currentQuery), 0, -$len));

            if (!empty($queryToExecute)) {
                try {
                    $pdo->exec($queryToExecute);
                    $executedCount++;
                    if (stripos($queryToExecute, 'CREATE TRIGGER') !== false) {
                        $triggerCount++;
                    }
                } catch (PDOException $ex) {
                    echo "      [Peringatan pada kueri]: " . substr($queryToExecute, 0, 60) . "...\n";
                    echo "      Pesan: " . $ex->getMessage() . "\n";
                }
            }
            $currentQuery = '';
        }
    }

    echo "\n======================================================================\n";
    echo " SUKSES! Basis Data Tingkat Lanjut Berhasil Diinisialisasi!\n";
    echo " Detail Eksekusi:\n";
    echo " - Total Perintah Dieksekusi : {$executedCount}\n";
    echo " - Trigger Mutasi Stok Aktif : {$triggerCount} (Cek Stok, Kurang Stok, Tambah Stok, Batal)\n";
    echo " - Relasi Foreign Key        : id_user, id_buku, id_kategori, id_penerbit\n";
    echo " - Integritas Referensial    : ON UPDATE CASCADE, ON DELETE RESTRICT\n";
    echo " - Akun Default Siap Pakai:\n";
    echo "   * Admin: username 'admin', password 'admin'\n";
    echo "   * Siswa: username 'sidik', password 'sidik'\n";
    echo "======================================================================\n";

} catch (PDOException $e) {
    echo "\n[ERROR KRITIS] Gagal melakukan setup database: " . $e->getMessage() . "\n";
    echo "Pastikan modul MySQL di XAMPP Control Panel sudah dalam status 'Running'.\n";
}
