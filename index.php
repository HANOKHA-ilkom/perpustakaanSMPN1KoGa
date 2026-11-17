<?php
// Front Controller Entry Point
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
// Berdasarkan Skripsi Sidik Prastio Utomo (NPM: 19430028)

// Suppress deprecated warnings on PHP 8.2 for strict compatibility
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', '1');

// Pastikan session path aman di serverless / read-only filesystem (/tmp)
if (session_status() === PHP_SESSION_NONE) {
    if (function_exists('session_save_path') && (!is_dir(session_save_path()) || !is_writable(session_save_path()))) {
        @session_save_path('/tmp');
    }
    @session_start();
}

// Autoload Core Classes
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/models/PerpusModel.php';
require_once __DIR__ . '/controllers/KatalogController.php';

// Instantiate Router
$router = new Router();

// Verifikasi status koneksi database (Cegah crash serverless jika belum terhubung)
$db = Database::getConnection();
if ($db === null && !in_array($router->resolveUri(), ['diagram'])) {
    $dbErr = Database::getError();
    $config = require __DIR__ . '/config/database.php';
    http_response_code(503);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Cloud Belum Terhubung - SMPN 1 Kota Gajah</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    </head>
    <body class="bg-light d-flex align-items-center min-vh-100 py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-danger text-white py-3 px-4">
                            <h5 class="mb-0 fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i>Koneksi Database Cloud Belum Terhubung</h5>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-secondary">Aplikasi berhasil aktif di Vercel Serverless, namun saat ini belum dapat terhubung ke database cloud Anda.</p>
                            
                            <div class="alert alert-danger py-2 px-3 small">
                                <strong>Log Sistem:</strong> <?= htmlspecialchars($dbErr ?: 'Koneksi ke host database tidak merespons.') ?>
                            </div>

                            <div class="bg-white border rounded-3 p-3 mb-4">
                                <h6 class="fw-bold text-dark mb-1">Host Database Terdeteksi:</h6>
                                <code class="text-primary fs-6"><?= htmlspecialchars($config['host']) ?>:<?= (int)$config['port'] ?></code>
                                <div class="small text-muted mt-1">Nama Database: <code><?= htmlspecialchars($config['database']) ?></code> | SSL: <code><?= $config['ssl'] ? 'Aktif' : 'Non-aktif' ?></code></div>
                            </div>

                            <h6 class="fw-bold text-dark">Langkah Perbaikan di Vercel:</h6>
                            <ol class="small text-muted mb-4">
                                <li class="mb-2">Buka Dashboard Vercel &rarr; Pilih proyek Anda &rarr; Masuk ke menu <strong>Settings</strong> &rarr; <strong>Environment Variables</strong>.</li>
                                <li class="mb-2">Pastikan variabel berikut sudah terdaftar dengan kredensial TiDB Cloud:
                                    <div class="table-responsive mt-1">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-light"><tr><th>Key</th><th>Contoh Value</th></tr></thead>
                                            <tbody>
                                                <tr><td><code>DB_HOST</code></td><td><code>gateway01.ap-southeast-1.prod.aws.tidbcloud.com</code></td></tr>
                                                <tr><td><code>DB_PORT</code></td><td><code>4000</code></td></tr>
                                                <tr><td><code>DB_NAME</code></td><td><code>db_perpustakaan</code></td></tr>
                                                <tr><td><code>DB_USER</code></td><td><code>xxxxxx.root</code></td></tr>
                                                <tr><td><code>DB_PASS</code></td><td><code>PasswordTiDBAnda</code></td></tr>
                                                <tr><td><code>DB_SSL</code></td><td><code>true</code></td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </li>
                                <li class="text-danger fw-bold">PENTING: Setelah mengisi Environment Variables, buka menu Deployments &rarr; klik ikon (...) pada deployment &rarr; pilih Redeploy agar variabel baru diterapkan.</li>
                            </ol>
                            
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <a href="diagram_perpustakaan.html" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-diagram-project me-1"></i>Buka Diagram ERD</a>
                                <button onclick="location.reload()" class="btn btn-primary btn-sm"><i class="fa-solid fa-rotate-right me-1"></i>Muat Ulang Halaman</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ==========================================
// KATALOG PUBLIK (OPAC - GUEST / SISWA)
// ==========================================
$router->add('katalog',                            ['KatalogController', 'index']);
$router->add('katalog/detail',                     ['KatalogController', 'detailJson']);

// ==========================================
// AUTHENTICATION & DIAGRAM ROUTES
// ==========================================
$router->add('login',              ['AuthController', 'login']);
$router->add('logout',             ['AuthController', 'logout']);
$router->add('diagram',            ['AuthController', 'diagram']);

// ==========================================
// ADMINISTRATOR ROUTES
// ==========================================
$router->add('admin/dashboard',            ['AdminController', 'dashboard']);
$router->add('admin/anggota',              ['AdminController', 'anggota']);
$router->add('admin/anggota/add',          ['AdminController', 'anggotaAdd']);
$router->add('admin/anggota/edit',         ['AdminController', 'anggotaEdit']);
$router->add('admin/anggota/delete',       ['AdminController', 'anggotaDelete']);

$router->add('admin/penerbit',             ['AdminController', 'penerbit']);
$router->add('admin/penerbit/add',         ['AdminController', 'penerbitAdd']);
$router->add('admin/penerbit/edit',        ['AdminController', 'penerbitEdit']);
$router->add('admin/penerbit/delete',      ['AdminController', 'penerbitDelete']);

$router->add('admin/pengarang',            ['AdminController', 'pengarang']);
$router->add('admin/pengarang/add',        ['AdminController', 'pengarangAdd']);
$router->add('admin/pengarang/edit',       ['AdminController', 'pengarangEdit']);
$router->add('admin/pengarang/delete',     ['AdminController', 'pengarangDelete']);

$router->add('admin/administrator',        ['AdminController', 'administrator']);
$router->add('admin/administrator/add',    ['AdminController', 'administratorAdd']);
$router->add('admin/administrator/edit',   ['AdminController', 'administratorEdit']);
$router->add('admin/administrator/delete', ['AdminController', 'administratorDelete']);
$router->add('admin/administrator/cetak',  ['AdminController', 'administratorCetak']);

// Sirkulasi Peminjaman (Admin / Scanner)
$router->add('admin/peminjaman',                      ['AdminController', 'peminjaman']);
$router->add('admin/peminjaman/add-direct',           ['AdminController', 'peminjamanAddDirect']);
$router->add('admin/peminjaman/api-scan-buku',        ['AdminController', 'apiScanBuku']);
$router->add('admin/peminjaman/api-check-siswa-loan', ['AdminController', 'apiCheckSiswaLoan']);
$router->add('admin/peminjaman/slip',                 ['AdminController', 'peminjamanSlip']);
$router->add('admin/peminjaman/edit',                 ['AdminController', 'peminjamanEdit']);
$router->add('admin/peminjaman/delete',               ['AdminController', 'peminjamanDelete']);
$router->add('admin/peminjaman/konfirmasi-pinjam',    ['AdminController', 'peminjamanKonfirmasiPinjam']);
$router->add('admin/peminjaman/konfirmasi-kembali',   ['AdminController', 'peminjamanKonfirmasiKembali']);
$router->add('admin/peminjaman/realtime-check',       ['AdminController', 'peminjamanRealtimeCheck']);

// Sirkulasi Pengembalian (Admin / Scanner)
$router->add('admin/pengembalian',                    ['AdminController', 'pengembalian']);
$router->add('admin/pengembalian/api-scan-kembali',   ['AdminController', 'apiScanKembali']);
$router->add('admin/pengembalian/proses',             ['AdminController', 'pengembalianProses']);
$router->add('admin/pengembalian/proses-tanpa-izin',  ['AdminController', 'pengembalianProsesTanpaIzin']);

// Laporan Riwayat Total Sirkulasi & Cetak
$router->add('admin/riwayat',                         ['AdminController', 'riwayat']);
$router->add('admin/riwayat/cetak',                   ['AdminController', 'riwayatCetak']);

// Data Buku & Scan Tambah Buku
$router->add('admin/buku',                 ['AdminController', 'buku']);
$router->add('admin/buku/add',             ['AdminController', 'bukuAdd']);
$router->add('admin/buku/edit',            ['AdminController', 'bukuEdit']);
$router->add('admin/buku/delete',          ['AdminController', 'bukuDelete']);
$router->add('admin/buku/api-scan-isbn',   ['AdminController', 'apiScanIsbn']);
$router->add('admin/buku/api-lookup-isbn', ['AdminController', 'apiLookupIsbn']);
$router->add('admin/scan-image-api',       ['AdminController', 'apiScanImage']);

$router->add('admin/kategori',             ['AdminController', 'kategori']);
$router->add('admin/kategori/add',         ['AdminController', 'kategoriAdd']);
$router->add('admin/kategori/edit',        ['AdminController', 'kategoriEdit']);
$router->add('admin/kategori/delete',      ['AdminController', 'kategoriDelete']);

$router->add('admin/identitas',            ['AdminController', 'identitas']);

// Dispatch the current request
$router->dispatch();

