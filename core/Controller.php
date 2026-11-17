<?php
// Core Controller
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah

class Controller {
    protected ?PDO $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = Database::getConnection();
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

    public function requireAuth(?string $allowedRole = null): void {
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

