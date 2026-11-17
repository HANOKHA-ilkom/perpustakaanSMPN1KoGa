<?php
// Authentication Controller
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/PerpusModel.php';

class AuthController extends Controller {
    private PerpusModel $model;

    public function __construct() {
        parent::__construct();
        $this->model = new PerpusModel();
    }

    public function login(): void {
        // If already logged in, redirect to admin dashboard or catalog
        if (isset($_SESSION['user'])) {
            $role = $_SESSION['user']['role'] ?? '';
            if (strcasecmp($role, 'Administrator') === 0 || strcasecmp($role, 'Admin') === 0) {
                $this->redirect('admin/dashboard');
            } else {
                $this->redirect('katalog');
            }
        }

        if ($this->isPost()) {
            $username = $this->post('username', '');
            $password = $this->post('password', '');

            if (empty($username) || empty($password)) {
                $this->setFlash('danger', 'Nama Pengguna dan Kata Sandi wajib diisi!');
                $this->redirect('login');
            }

            $user = $this->model->getUserByUsername($username);

            // Plain text or md5 check to support exact thesis password
            if ($user && ($user['password'] === $password || md5($password) === $user['password'])) {
                $_SESSION['user'] = $user;
                $this->model->updateLastLogin((int)$user['id_user']);

                $this->setFlash('success', 'Selamat datang, ' . htmlspecialchars($user['fullname']) . '!');

                if (strcasecmp($user['role'], 'Administrator') === 0 || strcasecmp($user['role'], 'Admin') === 0) {
                    $this->redirect('admin/dashboard');
                } else {
                    $this->redirect('katalog');
                }
            } else {
                $this->setFlash('danger', 'Nama Pengguna atau Kata Sandi salah!');
                $this->redirect('login');
            }
        }

        $identitas = $this->model->getIdentitas();
        $flash = $this->getFlash();
        $this->view('auth/login', [
            'identitas' => $identitas,
            'flash'     => $flash
        ]);
    }

    public function logout(): void {
        unset($_SESSION['user']);
        session_destroy();
        header("Location: " . $this->baseUrl('login'));
        exit;
    }

    public function diagram(): void {
        require __DIR__ . '/../diagram_perpustakaan.html';
        exit;
    }
}

