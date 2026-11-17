<?php
// Core Database Wrapper using PDO
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah

class Database {
    private static ?PDO $instance = null;
    private static string $errorMessage = '';

    public static function getConnection(): ?PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/database.php';
            $host = $config['host'];
            $port = $config['port'];
            $db   = $config['database'];
            $user = $config['username'];
            $pass = $config['password'];
            $charset = $config['charset'] ?? 'utf8mb4';

            try {
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_TIMEOUT            => 5,
                ];
                if (!empty($config['ssl'])) {
                    $caFile = '/etc/ssl/certs/ca-certificates.crt';
                    if (file_exists($caFile)) {
                        $options[PDO::MYSQL_ATTR_SSL_CA] = $caFile;
                    } elseif (file_exists('/etc/pki/tls/certs/ca-bundle.crt')) {
                        $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/pki/tls/certs/ca-bundle.crt';
                    } else {
                        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
                    }
                    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                }

                // Try connecting to the specific database first
                $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Untuk server lokal (XAMPP), coba buat database otomatis jika belum ada
                if ($host === '127.0.0.1' || $host === 'localhost') {
                    try {
                        $dsnWithoutDb = "mysql:host={$host};port={$port};charset={$charset}";
                        $tempPdo = new PDO($dsnWithoutDb, $user, $pass, $options);
                        $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                        $tempPdo->exec("USE `{$db}`;");

                        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
                        self::$instance = new PDO($dsn, $user, $pass, $options);
                    } catch (PDOException $e2) {
                        self::$errorMessage = "Koneksi database lokal gagal ({$host}:{$port}): " . $e2->getMessage();
                        self::$instance = null;
                    }
                } else {
                    self::$errorMessage = "Koneksi database cloud gagal ({$host}:{$port}/{$db}): " . $e->getMessage();
                    self::$instance = null;
                }
            }
        }
        return self::$instance;
    }

    public static function getError(): string {
        return self::$errorMessage;
    }
}

