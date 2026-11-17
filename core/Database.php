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
                ];
                if (!empty($config['ssl'])) {
                    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                }

                // Try connecting to the specific database first
                $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // If database doesn't exist, connect to server and create it automatically
                try {
                    $dsnWithoutDb = "mysql:host={$host};port={$port};charset={$charset}";
                    $tempPdo = new PDO($dsnWithoutDb, $user, $pass, $options);
                    $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                    $tempPdo->exec("USE `{$db}`;");

                    // Execute initial SQL if file exists
                    $sqlFile = __DIR__ . '/../database/db_perpustakaan.sql';
                    if (file_exists($sqlFile)) {
                        $sqlContent = file_get_contents($sqlFile);
                        $tempPdo->exec($sqlContent);
                    }

                    // Now re-connect to the created database
                    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
                    self::$instance = new PDO($dsn, $user, $pass, $options);
                } catch (PDOException $e2) {
                    self::$errorMessage = "Koneksi database gagal: " . $e2->getMessage();
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

