<?php
// Database Configuration
// Mendukung Dynamic Environment Variables (Vercel / Cloud MySQL) & Fallback ke Localhost (XAMPP)

$getEnv = function(string $key, string $default = ''): string {
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return (string)$_ENV[$key];
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return (string)$_SERVER[$key];
    $val = getenv($key);
    return ($val !== false && $val !== '') ? (string)$val : $default;
};

$dbUrl = $getEnv('DATABASE_URL', $getEnv('MYSQL_URL'));

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
    $dbHost = $getEnv('DB_HOST', $getEnv('MYSQLHOST', '127.0.0.1'));
    $dbPort = (int)$getEnv('DB_PORT', $getEnv('MYSQLPORT', '3306'));
    $dbUser = $getEnv('DB_USER', $getEnv('MYSQLUSER', 'root'));
    $dbPass = $getEnv('DB_PASS', $getEnv('MYSQLPASSWORD', ''));
    $dbName = $getEnv('DB_NAME', $getEnv('MYSQLDATABASE', 'db_perpustakaan'));
    
    // Otomatis aktifkan SSL jika terhubung ke server cloud remote (bukan localhost)
    $isRemote = ($dbHost !== '127.0.0.1' && $dbHost !== 'localhost');
    $sslEnv   = strtolower($getEnv('DB_SSL', ''));
    $dbSsl    = ($sslEnv === 'true' || $sslEnv === '1' || $isRemote);
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
