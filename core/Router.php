<?php
// Core URL Router
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah

class Router {
    private array $routes = [];

    public function add(string $route, array $handler): void {
        $this->routes[trim($route, '/')] = $handler;
    }

    public function dispatch(): void {
        $uri = $this->resolveUri();

        // Check exact match
        if (isset($this->routes[$uri])) {
            $this->executeHandler($this->routes[$uri]);
            return;
        }

        // Check fallback for default root
        if ($uri === '' || $uri === 'app') {
            if (isset($_SESSION['user'])) {
                $role = $_SESSION['user']['role'] ?? '';
                if (strcasecmp($role, 'Administrator') === 0 || strcasecmp($role, 'Admin') === 0) {
                    header("Location: " . $this->getBaseUrl('admin/dashboard'));
                    exit;
                } else {
                    header("Location: " . $this->getBaseUrl('katalog'));
                    exit;
                }
            } else {
                header("Location: " . $this->getBaseUrl('katalog'));
                exit;
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'>";
        echo "<h1>404 - Halaman Tidak Ditemukan</h1>";
        echo "<p>Rute: <code>{$uri}</code> tidak tersedia.</p>";
        echo "<a href='" . $this->getBaseUrl('login') . "' style='padding:10px 20px; background:#007bff; color:#fff; text-decoration:none; border-radius:5px;'>Kembali ke Beranda</a>";
        echo "</div>";
    }

    private function resolveUri(): string {
        // 1. URL query param (e.g. index.php?url=admin/dashboard)
        if (!empty($_GET['url'])) {
            $uri = trim((string)$_GET['url'], '/');
        } elseif (!empty($_SERVER['PATH_INFO'])) {
            $uri = trim((string)$_SERVER['PATH_INFO'], '/');
        } else {
            // Parse from REQUEST_URI
            $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
            $scriptDir = rtrim(str_replace(basename($_SERVER['SCRIPT_NAME'] ?? ''), '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
            
            if ($scriptDir !== '' && strpos($requestUri, $scriptDir) === 0) {
                $uri = substr($requestUri, strlen($scriptDir));
            } else {
                $uri = $requestUri;
            }
            $uri = trim($uri, '/');
            // Remove 'index.php' if present
            if (strpos($uri, 'index.php') === 0) {
                $uri = trim(substr($uri, 9), '/');
            }
        }

        // Strip leading 'app/' if present (like in localhost/perpustakaan/app/admin/...)
        if (strpos($uri, 'app/') === 0) {
            $uri = substr($uri, 4);
        }

        return trim($uri, '/');
    }

    private function executeHandler(array $handler): void {
        [$controllerClass, $method] = $handler;
        $controllerFile = __DIR__ . '/../controllers/' . $controllerClass . '.php';

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controller = new $controllerClass();
            if (method_exists($controller, $method)) {
                $controller->$method();
                return;
            }
        }
        die("Handler tidak ditemukan: {$controllerClass}@{$method}");
    }

    private function getBaseUrl(string $path = ''): string {
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $dir = rtrim(str_replace(basename($script), '', $script), '/\\');
        return rtrim("{$scheme}://{$host}{$dir}", '/') . '/' . ltrim($path, '/');
    }
}

