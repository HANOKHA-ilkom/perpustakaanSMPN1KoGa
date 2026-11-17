<?php
// Front Controller Entry Point
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
// Berdasarkan Skripsi Sidik Prastio Utomo (NPM: 19430028)

// Suppress deprecated warnings on PHP 8.2 for strict compatibility
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload Core Classes
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/models/PerpusModel.php';
require_once __DIR__ . '/controllers/KatalogController.php';

// Instantiate Router
$router = new Router();

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

