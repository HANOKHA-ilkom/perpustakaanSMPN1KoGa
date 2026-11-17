<?php
// Database Configuration
// Mendukung Dynamic Environment Variables (Vercel / Cloud MySQL) & Fallback ke Localhost (XAMPP)

$dbUrl = getenv('DATABASE_URL') ?: (getenv('MYSQL_URL') ?: ($_ENV['DATABASE_URL'] ?? ($_ENV['MYSQL_URL'] ?? '')));

if (!empty($dbUrl)) {
    // Dukungan format Connection String URL: mysql://user:pass@host:port/dbname
    $parsed = parse_url($dbUrl);
    $dbHost = $parsed['host'] ?? '127.0.0.1';
    $dbPort = (int)($parsed['port'] ?? 3306);
    $dbUser = isset($parsed['user']) ? urldecode($parsed['user']) : 'root';
    $dbPass = isset($parsed['pass']) ? urldecode($parsed['pass']) : '';
    $dbName = isset($parsed['path']) ? ltrim($parsed['path'], '/') : 'db_perpustakaan';
    $dbSsl  = true;
} else {
    // Dukungan variabel individual (Standar Vercel / TiDB / Railway / Aiven)
    $dbHost = getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: ($_ENV['DB_HOST'] ?? ($_ENV['MYSQLHOST'] ?? '127.0.0.1')));
    $dbPort = (int)(getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: ($_ENV['DB_PORT'] ?? ($_ENV['MYSQLPORT'] ?? 3306))));
    $dbUser = getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: ($_ENV['DB_USER'] ?? ($_ENV['MYSQLUSER'] ?? 'root')));
    $dbPass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : ($_ENV['DB_PASS'] ?? ($_ENV['MYSQLPASSWORD'] ?? '')));
    $dbName = getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: ($_ENV['DB_NAME'] ?? ($_ENV['MYSQLDATABASE'] ?? 'db_perpustakaan')));
    
    // Otomatis aktifkan SSL jika terhubung ke server cloud remote (bukan localhost)
    $isRemote = ($dbHost !== '127.0.0.1' && $dbHost !== 'localhost');
    $dbSsl    = (getenv('DB_SSL') === 'true' || ($_ENV['DB_SSL'] ?? '') === 'true' || $isRemote);
}

return [
    'host'     => $dbHost,
    'port'     => $dbPort,
    'username' => $dbUser,
    'password' => $dbPass,
    'database' => $dbName,
    'charset'  => 'utf8mb4',
    'ssl'      => $dbSsl
];
