<?php
// Katalog Publik (OPAC) Controller
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
// Diakses oleh Siswa / Pengunjung (Guest) tanpa perlu login

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/PerpusModel.php';

class KatalogController extends Controller {
    private PerpusModel $model;

    public function __construct() {
        parent::__construct();
        $this->model = new PerpusModel();
    }

    public function index(): void {
        $keyword = $this->get('q', '');
        $kategori = $this->get('kategori', '');

        $allBuku = $this->model->getAllBuku();
        $kategoriList = $this->model->getAllKategori();
        $identitas = $this->model->getIdentitas();

        // Filter katalog jika ada pencarian
        $filteredBuku = $allBuku;
        if ($keyword !== '' || $kategori !== '') {
            $filteredBuku = array_filter($allBuku, function($b) use ($keyword, $kategori) {
                $matchKeyword = true;
                if ($keyword !== '') {
                    $haystack = strtolower($b['judul_buku'] . ' ' . $b['pengarang'] . ' ' . ($b['penerbit_buku'] ?? '') . ' ' . ($b['isbn'] ?? ''));
                    $matchKeyword = strpos($haystack, strtolower($keyword)) !== false;
                }
                $matchKategori = true;
                if ($kategori !== '') {
                    $matchKategori = strcasecmp((string)$b['id_kategori'], $kategori) === 0 || strcasecmp((string)($b['kategori_buku'] ?? ''), $kategori) === 0;
                }
                return $matchKeyword && $matchKategori;
            });
        }

        $this->view('katalog/index', [
            'bukuList'     => array_values($filteredBuku),
            'kategoriList' => $kategoriList,
            'identitas'    => $identitas,
            'keyword'      => $keyword,
            'kategori'     => $kategori,
            'totalBuku'    => count($allBuku),
            'totalHasil'   => count($filteredBuku)
        ]);
    }

    public function detailJson(): void {
        $id = (int)$this->get('id', 0);
        $buku = $this->model->getBukuById($id);
        if ($buku) {
            $this->json(['status' => 'success', 'data' => $buku]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Buku tidak ditemukan.'], 404);
        }
    }
}

