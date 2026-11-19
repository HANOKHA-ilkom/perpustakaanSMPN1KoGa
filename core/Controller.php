<?php
// Core Controller
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah

class Controller {
    protected ?PDO $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            $isHttps = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'));
            @session_set_cookie_params([
                'lifetime' => 2592000,
                'path'     => '/',
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            @session_start();
        }
        $this->db = Database::getConnection();
        self::tryRestoreAuthFromCookie();
    }

    public function view(string $viewPath, array $data = []): void {
        extract($data);
        $file = __DIR__ . '/../views/' . $viewPath . '.php';
        if (file_exists($file)) {
            require $file;
        } else {
            die("View [{$viewPath}] tidak ditemukan pada: {$file}");
        }
    }

    public function redirect(string $path): void {
        $url = $this->baseUrl($path);
        header("Location: {$url}");
        exit;
    }

    public function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    public function baseUrl(string $path = ''): string {
        $scheme = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $dir = rtrim(str_replace(basename($script), '', $script), '/\\');

        // Di Vercel serverless, hilangkan prefix /api dari base URL publik
        if ($dir === '/api' || str_ends_with($dir, '/api')) {
            $dir = substr($dir, 0, -4);
        }
        
        $base = "{$scheme}://{$host}{$dir}";
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }

    public function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public function post(string $key, $default = null) {
        return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
    }

    public function get(string $key, $default = null) {
        return isset($_GET[$key]) ? trim((string)$_GET[$key]) : $default;
    }

    public function setFlash(string $type, string $message): void {
        $_SESSION['flash'] = [
            'type'    => $type, // 'success', 'danger', 'warning', 'info'
            'message' => $message
        ];
    }

    public function getFlash(): ?array {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    public static function setAuthCookie(array $user): void {
        $idUser = (int)($user['id_user'] ?? 0);
        if ($idUser <= 0) return;
        $timestamp = time();
        $secret = 'perpus_smpn1kotagajah_secret_key_2026';
        $password = $user['password'] ?? '';
        $hmac = hash_hmac('sha256', "{$idUser}:{$timestamp}:{$password}", $secret);
        $cookieValue = "{$idUser}|{$timestamp}|{$hmac}";
        
        $expire = time() + (30 * 86400); // 30 hari
        $isHttps = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'));
        
        @setcookie('perpus_auth', $cookieValue, [
            'expires'  => $expire,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        $_COOKIE['perpus_auth'] = $cookieValue;
    }

    public static function clearAuthCookie(): void {
        if (isset($_COOKIE['perpus_auth'])) {
            unset($_COOKIE['perpus_auth']);
        }
        $isHttps = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'));
        @setcookie('perpus_auth', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    public static function tryRestoreAuthFromCookie(): bool {
        if (isset($_SESSION['user']) && !empty($_SESSION['user']['id_user'])) {
            return true;
        }

        if (empty($_COOKIE['perpus_auth'])) {
            return false;
        }

        $parts = explode('|', (string)$_COOKIE['perpus_auth']);
        if (count($parts) !== 3) {
            return false;
        }

        [$idUser, $timestamp, $hmac] = $parts;
        $idUser = (int)$idUser;
        $timestamp = (int)$timestamp;

        // Validasi expired (30 hari)
        if ($idUser <= 0 || (time() - $timestamp) > (30 * 86400)) {
            return false;
        }

        try {
            $db = Database::getConnection();
            if (!$db) return false;

            $stmt = $db->prepare("SELECT * FROM `user` WHERE `id_user` = ? LIMIT 1");
            $stmt->execute([$idUser]);
            $user = $stmt->fetch();
            if (!$user) return false;

            $secret = 'perpus_smpn1kotagajah_secret_key_2026';
            $expectedHmac = hash_hmac('sha256', "{$idUser}:{$timestamp}:{$user['password']}", $secret);

            if (hash_equals($expectedHmac, $hmac)) {
                $_SESSION['user'] = $user;
                return true;
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }

    public function requireAuth(?string $allowedRole = null): void {
        self::tryRestoreAuthFromCookie();
        if (!isset($_SESSION['user'])) {
            $this->setFlash('danger', 'Silakan login terlebih dahulu.');
            $this->redirect('login');
        }

        if ($allowedRole !== null) {
            $currentRole = $_SESSION['user']['role'] ?? '';
            // Match Administrator / Admin or Siswa / Member
            $isAdminRole = (strcasecmp($currentRole, 'Administrator') === 0 || strcasecmp($currentRole, 'Admin') === 0);
            $isSiswaRole = (strcasecmp($currentRole, 'Siswa') === 0 || strcasecmp($currentRole, 'Member') === 0);

            if ($allowedRole === 'Administrator' && !$isAdminRole) {
                $this->setFlash('danger', 'Akses ditolak: Hanya untuk Administrator.');
                $this->redirect('siswa/dashboard');
            } elseif ($allowedRole === 'Siswa' && !$isSiswaRole) {
                $this->setFlash('danger', 'Akses ditolak: Hanya untuk Siswa/Member.');
                $this->redirect('admin/dashboard');
            }
        }
    }
}

