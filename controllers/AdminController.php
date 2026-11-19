<?php
// Administrator Controller
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/PerpusModel.php';

class AdminController extends Controller {
    private PerpusModel $model;

    public function __construct() {
        parent::__construct();
        $this->requireAuth('Administrator');
        $this->model = new PerpusModel();
    }

    public function dashboard(): void {
        $identitas = $this->model->getIdentitas();
        $totalAnggota      = $this->model->countAnggota();
        $totalBuku         = $this->model->countBuku();
        $totalPeminjaman   = $this->model->countPeminjaman();
        $totalPengembalian = $this->model->countPengembalian();
        $analytics         = $this->model->getDashboardAnalytics();

        $this->view('admin/dashboard', [
            'identitas'         => $identitas,
            'totalAnggota'      => $totalAnggota,
            'totalBuku'         => $totalBuku,
            'totalPeminjaman'   => $totalPeminjaman,
            'totalPengembalian' => $totalPengembalian,
            'analytics'         => $analytics,
            'flash'             => $this->getFlash(),
            'activeMenu'        => 'dashboard'
        ]);
    }

    // ==========================================
    // DATA ANGGOTA
    // ==========================================
    public function anggota(): void {
        $anggotaList       = $this->model->getAnggotaListWithCirculation();
        $autoKode          = $this->model->getNextKodeAnggota();
        $chartDailyLoans   = $this->model->getMonthlyDailyLoans();
        $chartDailyReturns = $this->model->getMonthlyDailyReturns();
        $adminWa           = '082385495700';

        $this->view('admin/anggota', [
            'anggotaList'       => $anggotaList,
            'autoKode'          => $autoKode,
            'chartDailyLoans'   => $chartDailyLoans,
            'chartDailyReturns' => $chartDailyReturns,
            'adminWa'           => $adminWa,
            'flash'             => $this->getFlash(),
            'activeMenu'        => 'anggota'
        ]);
    }

    public function anggotaAdd(): void {
        if ($this->isPost()) {
            $data = [
                'kode_user'   => $this->post('kode_user') ?: $this->model->getNextKodeAnggota(),
                'nis'         => $this->post('nis', '-'),
                'fullname'    => $this->post('fullname', ''),
                'username'    => $this->post('username', ''),
                'password'    => $this->post('password', ''),
                'kelas'       => $this->post('kelas', '-'),
                'alamat'      => $this->post('alamat', '-'),
                'no_whatsapp' => $this->post('no_whatsapp') ?: '087767499895',
                'verif'       => 'Akun Terverifikasi',
                'role'        => 'Siswa',
                'join_date'   => date('Y-m-d')
            ];

            if ($this->model->createUser($data)) {
                $this->setFlash('success', 'Data anggota berhasil ditambahkan.');
            } else {
                $this->setFlash('danger', 'Gagal menambahkan data anggota.');
            }
        }
        $this->redirect('admin/anggota');
    }

    public function anggotaEdit(): void {
        if ($this->isPost()) {
            $id = (int)$this->post('id_user');
            $data = [
                'nis'         => $this->post('nis'),
                'fullname'    => $this->post('fullname'),
                'username'    => $this->post('username'),
                'password'    => $this->post('password'),
                'kelas'       => $this->post('kelas'),
                'alamat'      => $this->post('alamat'),
                'no_whatsapp' => $this->post('no_whatsapp') ?: '087767499895'
            ];

            if ($this->model->updateUser($id, $data)) {
                $this->setFlash('success', 'Data anggota berhasil diperbarui.');
            } else {
                $this->setFlash('danger', 'Gagal memperbarui data anggota.');
            }
        }
        $this->redirect('admin/anggota');
    }

    public function anggotaDelete(): void {
        $id = (int)$this->post('id_user', $this->get('id'));
        if ($id && $this->model->deleteUser($id)) {
            $this->setFlash('success', 'Data anggota berhasil dihapus.');
        } else {
            $err = $this->model->getLastError() ?: 'Gagal menghapus data anggota.';
            $this->setFlash('danger', $err);
        }
        $this->redirect('admin/anggota');
    }

    // ==========================================
    // DATA PENERBIT
    // ==========================================
    public function penerbit(): void {
        $penerbitList = $this->model->getAllPenerbitWithStats();
        $autoKode = $this->model->getNextKodePenerbit();
        $this->view('admin/penerbit', [
            'penerbitList' => $penerbitList,
            'autoKode'     => $autoKode,
            'flash'        => $this->getFlash(),
            'activeMenu'   => 'penerbit'
        ]);
    }

    public function penerbitAdd(): void {
        if ($this->isPost()) {
            $data = [
                'kode_penerbit'  => $this->post('kode_penerbit') ?: $this->model->getNextKodePenerbit(),
                'nama_penerbit'  => $this->post('nama_penerbit', ''),
                'verif_penerbit' => $this->post('verif_penerbit', 'Penerbit Terverifikasi')
            ];

            if ($this->model->createPenerbit($data)) {
                $this->setFlash('success', 'Data penerbit berhasil ditambahkan.');
            } else {
                $this->setFlash('danger', 'Gagal menambahkan data penerbit.');
            }
        }
        $this->redirect('admin/penerbit');
    }

    public function penerbitEdit(): void {
        if ($this->isPost()) {
            $id = (int)$this->post('id_penerbit');
            $data = [
                'nama_penerbit'  => $this->post('nama_penerbit'),
                'verif_penerbit' => $this->post('verif_penerbit')
            ];

            if ($this->model->updatePenerbit($id, $data)) {
                $this->setFlash('success', 'Data penerbit berhasil diperbarui.');
            } else {
                $this->setFlash('danger', 'Gagal memperbarui data penerbit.');
            }
        }
        $this->redirect('admin/penerbit');
    }

    public function penerbitDelete(): void {
        $id = (int)$this->post('id_penerbit', $this->get('id'));
        if ($id && $this->model->deletePenerbit($id)) {
            $this->setFlash('success', 'Data penerbit berhasil dihapus.');
        } else {
            $err = $this->model->getLastError() ?: 'Gagal menghapus data penerbit.';
            $this->setFlash('danger', $err);
        }
        $this->redirect('admin/penerbit');
    }

    public function penerbitSync(): void {
        $this->requireAuth('Administrator');
        $this->model->syncPenerbitDanPengarangDariBuku();
        $this->setFlash('success', 'Data penerbit berhasil disinkronkan sesuai seluruh buku yang ada di katalog.');
        $this->redirect('admin/penerbit');
    }

    // ==========================================
    // DATA PENGARANG (MASTER DATA)
    // ==========================================
    public function pengarang(): void {
        $pengarangList = $this->model->getAllPengarangWithStats();
        $autoKode = $this->model->getNextKodePengarang();
        $topLaris = $this->model->getTopLarisPengarang(8);
        $this->view('admin/pengarang', [
            'pengarangList' => $pengarangList,
            'autoKode'      => $autoKode,
            'topLaris'      => $topLaris,
            'flash'         => $this->getFlash(),
            'activeMenu'    => 'pengarang'
        ]);
    }

    public function pengarangAdd(): void {
        if ($this->isPost()) {
            $data = [
                'kode_pengarang' => $this->post('kode_pengarang') ?: $this->model->getNextKodePengarang(),
                'nama_pengarang' => trim($this->post('nama_pengarang', '')),
                'status'         => $this->post('status', 'Aktif'),
                'biografi'       => trim($this->post('biografi', ''))
            ];

            if (empty($data['nama_pengarang'])) {
                $this->setFlash('danger', 'Nama pengarang wajib diisi.');
            } elseif ($this->model->createPengarang($data)) {
                $this->setFlash('success', 'Data pengarang berhasil ditambahkan.');
            } else {
                $this->setFlash('danger', 'Gagal menambahkan data pengarang.');
            }
        }
        $this->redirect('admin/pengarang');
    }

    public function pengarangEdit(): void {
        if ($this->isPost()) {
            $id = (int)$this->post('id_pengarang');
            $data = [
                'nama_pengarang' => trim($this->post('nama_pengarang', '')),
                'status'         => $this->post('status', 'Aktif'),
                'biografi'       => trim($this->post('biografi', ''))
            ];

            if (empty($data['nama_pengarang'])) {
                $this->setFlash('danger', 'Nama pengarang tidak boleh kosong.');
            } elseif ($this->model->updatePengarang($id, $data)) {
                $this->setFlash('success', 'Data pengarang berhasil diperbarui.');
            } else {
                $this->setFlash('danger', 'Gagal memperbarui data pengarang.');
            }
        }
        $this->redirect('admin/pengarang');
    }

    public function pengarangDelete(): void {
        $id = (int)$this->post('id_pengarang', $this->get('id'));
        if ($id && $this->model->deletePengarang($id)) {
            $this->setFlash('success', 'Data pengarang berhasil dihapus.');
        } else {
            $err = $this->model->getLastError() ?: 'Gagal menghapus data pengarang.';
            $this->setFlash('danger', $err);
        }
        $this->redirect('admin/pengarang');
    }

    public function pengarangSync(): void {
        $this->requireAuth('Administrator');
        $this->model->syncPenerbitDanPengarangDariBuku();
        $this->setFlash('success', 'Data pengarang berhasil disinkronkan sesuai seluruh buku yang ada di katalog.');
        $this->redirect('admin/pengarang');
    }

    // ==========================================
    // DATA ADMINISTRATOR
    // ==========================================
    public function administrator(): void {
        $data = $this->model->getAdministratorsWithActivityStats();
        $this->view('admin/administrator', [
            'adminList'    => $data['list'],
            'statsSummary' => $data['summary'],
            'flash'        => $this->getFlash(),
            'activeMenu'   => 'administrator'
        ]);
    }

    public function administratorCetak(): void {
        $data      = $this->model->getAdministratorsWithActivityStats();
        $identitas = $this->model->getIdentitas();
        $this->view('admin/administrator_print', [
            'adminList'    => $data['list'],
            'statsSummary' => $data['summary'],
            'identitas'    => $identitas
        ]);
    }

    public function administratorAdd(): void {
        if ($this->isPost()) {
            $data = [
                'kode_user' => 'ADM' . rand(100, 999),
                'nis'       => '-',
                'fullname'  => $this->post('fullname', ''),
                'username'  => $this->post('username', ''),
                'password'  => $this->post('password', ''),
                'kelas'     => '-',
                'alamat'    => 'SMP Negeri 1 Kota Gajah',
                'verif'     => 'Akun Terverifikasi',
                'role'      => 'Administrator',
                'jam_aktif' => $this->post('jam_aktif', '07:30 - 15:30 WIB'),
                'join_date' => date('Y-m-d')
            ];

            if ($this->model->createUser($data)) {
                $this->setFlash('success', 'Administrator baru berhasil ditambahkan.');
            } else {
                $this->setFlash('danger', 'Gagal menambahkan data administrator.');
            }
        }
        $this->redirect('admin/administrator');
    }

    public function administratorEdit(): void {
        if ($this->isPost()) {
            $id = (int)$this->post('id_user');
            $data = [
                'fullname'  => $this->post('fullname'),
                'username'  => $this->post('username'),
                'password'  => $this->post('password'),
                'jam_aktif' => $this->post('jam_aktif', '07:30 - 15:30 WIB')
            ];

            if ($this->model->updateAdmin($id, $data)) {
                $this->setFlash('success', 'Data administrator berhasil diperbarui.');
            } else {
                $this->setFlash('danger', 'Gagal memperbarui data administrator.');
            }
        }
        $this->redirect('admin/administrator');
    }

    public function administratorDelete(): void {
        $id = (int)$this->post('id_user', $this->get('id'));
        if ($id && $this->model->deleteUser($id)) {
            $this->setFlash('success', 'Data administrator berhasil dihapus.');
        } else {
            $this->setFlash('danger', 'Gagal menghapus data administrator.');
        }
        $this->redirect('admin/administrator');
    }

    // ==========================================
    // DATA PEMINJAMAN (SIRKULASI ADMIN & SCANNER)
    // ==========================================
    public function peminjaman(): void {
        $peminjamanList  = $this->model->getAllPeminjaman();
        $siswaList       = $this->model->getUsersByRole('Siswa');
        $bukuList        = $this->model->getAllBuku();
        $chartDailyLoans = $this->model->getMonthlyDailyLoans();

        $this->view('admin/peminjaman', [
            'peminjamanList'  => $peminjamanList,
            'siswaList'       => $siswaList,
            'bukuList'        => $bukuList,
            'chartDailyLoans' => $chartDailyLoans,
            'flash'           => $this->getFlash(),
            'activeMenu'      => 'peminjaman'
        ]);
    }

    public function peminjamanAddDirect(): void {
        if ($this->isPost()) {
            $idUser = (int)$this->post('id_user');
            $idBuku = (int)$this->post('id_buku');
            $tanggalPinjam = $this->post('tanggal_peminjaman') ?: date('d-m-Y');
            $kondisi = $this->post('kondisi_buku_saat_dipinjam') ?: 'Baik';

            $result = $this->model->createPeminjamanAdmin($idUser, $idBuku, $tanggalPinjam, $kondisi);

            if ($result['success']) {
                $newId = $result['id_peminjaman'];
                $slipUrl = $this->baseUrl("admin/peminjaman/slip?id={$newId}");
                $this->setFlash('success', "Peminjaman berhasil dicatat! <a href='{$slipUrl}' target='_blank' class='btn btn-xs btn-light font-weight-bold ml-2'><i class='fas fa-print mr-1'></i> Cetak Slip Peminjaman (PDF)</a>");
            } else {
                $this->setFlash('danger', $result['message']);
            }
        }
        $this->redirect('admin/peminjaman');
    }

    public function apiScanBuku(): void {
        $code = $this->get('code', '');
        $buku = $this->model->findBukuByBarcodeOrIsbn($code);

        if ($buku) {
            $this->json([
                'status' => 'success',
                'data'   => $buku
            ]);
        } else {
            $this->json([
                'status'  => 'error',
                'message' => 'Barcode atau buku tidak ditemukan dalam sistem katalog perpustakaan.'
            ], 404);
        }
    }

    public function apiCheckSiswaLoan(): void {
        $idUser = (int)$this->get('id_user', 0);
        if ($idUser <= 0) {
            $this->json(['status' => 'error', 'message' => 'ID siswa tidak valid.'], 400);
        }

        $activeCount = $this->model->getActiveLoanCountByUser($idUser);
        $activeLoans = $this->model->getActiveLoansByUser($idUser);
        $user = $this->model->getUserById($idUser);

        $this->json([
            'status'       => 'success',
            'user'         => $user,
            'active_count' => $activeCount,
            'can_borrow'   => ($activeCount < 2),
            'max_allowed'  => 2,
            'loans'        => $activeLoans
        ]);
    }

    public function peminjamanSlip(): void {
        $id = (int)$this->get('id', 0);
        $loan = $this->model->getPeminjamanDetail($id);

        if (!$loan) {
            $this->setFlash('danger', 'Data transaksi peminjaman tidak ditemukan.');
            $this->redirect('admin/peminjaman');
        }

        $identitas = $this->model->getIdentitas();

        $this->view('admin/slip_peminjaman', [
            'loan'      => $loan,
            'identitas' => $identitas
        ]);
    }

    // ==========================================
    // SIRKULASI PENGEMBALIAN (ADMIN & SCANNER)
    // ==========================================
    public function pengembalian(): void {
        $activeLoans       = $this->model->getAllActiveLoans();
        $allBuku           = $this->model->getAllBuku();
        $siswaList         = $this->model->getUsersByRole('Siswa');
        $chartDailyReturns = $this->model->getMonthlyDailyReturns();

        $this->view('admin/pengembalian', [
            'activeLoans'       => $activeLoans,
            'allBuku'           => $allBuku,
            'siswaList'         => $siswaList,
            'chartDailyReturns' => $chartDailyReturns,
            'flash'             => $this->getFlash(),
            'activeMenu'        => 'pengembalian'
        ]);
    }

    // ==========================================
    // LAPORAN RIWAYAT TOTAL SIRKULASI
    // ==========================================
    public function riwayat(): void {
        $filters = [
            'jenis'   => $this->get('jenis', ''),
            'id_user' => (int)$this->get('id_user', 0),
            'id_buku' => (int)$this->get('id_buku', 0),
            'kondisi' => $this->get('kondisi', ''),
            'denda'   => $this->get('denda', '')
        ];

        $riwayatData = $this->model->getFilteredRiwayat($filters);
        $siswaList   = $this->model->getUsersByRole('Siswa');
        $bukuList    = $this->model->getAllBuku();

        $this->view('admin/riwayat', [
            'riwayatList' => $riwayatData['list'],
            'summary'     => $riwayatData['summary'],
            'filters'     => $filters,
            'siswaList'   => $siswaList,
            'bukuList'    => $bukuList,
            'flash'       => $this->getFlash(),
            'activeMenu'  => 'riwayat'
        ]);
    }

    public function riwayatCetak(): void {
        $filters = [
            'jenis'   => $this->get('jenis', ''),
            'id_user' => (int)$this->get('id_user', 0),
            'id_buku' => (int)$this->get('id_buku', 0),
            'kondisi' => $this->get('kondisi', ''),
            'denda'   => $this->get('denda', '')
        ];

        $riwayatData = $this->model->getFilteredRiwayat($filters);
        $identitas   = $this->model->getIdentitas();

        $this->view('admin/riwayat_print', [
            'riwayatList' => $riwayatData['list'],
            'summary'     => $riwayatData['summary'],
            'filters'     => $filters,
            'identitas'   => $identitas
        ]);
    }

    public function apiScanKembali(): void {
        $code = $this->get('code', '');
        $buku = $this->model->findBukuByBarcodeOrIsbn($code);

        if (!$buku) {
            $this->json([
                'status'  => 'error_not_found',
                'message' => 'Scan Gagal! Kode batang / ISBN tidak valid atau tidak terdaftar di sistem.'
            ], 404);
        }

        // Cek apakah ada transaksi peminjaman aktif untuk buku ini
        $activeLoan = $this->model->getActiveLoanByBuku((int)$buku['id_buku']);

        if (!$activeLoan) {
            // Buku ada di katalog, tapi tidak ada peminjaman aktif!
            $this->json([
                'status'  => 'no_active_loan',
                'buku'    => $buku,
                'message' => 'Buku belum pernah dipinjam! Apakah siswa tersebut meminjam tanpa sepengetahuan?'
            ]);
        }

        // Hitung denda keterlambatan jika melewati batas waktu (default 7 hari pinjam)
        $tglPinjamStr = $activeLoan['tanggal_peminjaman'];
        $dendaPerHari = 5000;
        $terlambatHari = 0;
        $dendaTelat = 0;

        $tglPinjamObj = DateTime::createFromFormat('d-m-Y', $tglPinjamStr);
        if ($tglPinjamObj) {
            $tglJatuhTempo = clone $tglPinjamObj;
            $tglJatuhTempo->modify('+7 days');

            $today = new DateTime();
            $today->setTime(0, 0, 0);
            $tglJatuhTempo->setTime(0, 0, 0);

            if ($today > $tglJatuhTempo) {
                $interval = $tglJatuhTempo->diff($today);
                $terlambatHari = (int)$interval->days;
                $dendaTelat = $terlambatHari * $dendaPerHari;
            }
            $tglJatuhTempoFormatted = $tglJatuhTempo->format('d-m-Y');
        } else {
            $tglJatuhTempoFormatted = '-';
        }

        $this->json([
            'status'         => 'success',
            'buku'           => $buku,
            'loan'           => $activeLoan,
            'tgl_jatuh_tempo'=> $tglJatuhTempoFormatted,
            'terlambat_hari' => $terlambatHari,
            'denda_telat'    => $dendaTelat,
            'tarif_denda'    => $dendaPerHari
        ]);
    }

    public function pengembalianProses(): void {
        if ($this->isPost()) {
            $idPeminjaman = (int)$this->post('id_peminjaman');
            $kondisiBuku  = $this->post('kondisi_buku_saat_dikembalikan') ?: 'Baik';
            $dendaTelat   = (float)$this->post('denda_keterlambatan', 0);
            $dendaRusak   = (float)$this->post('denda_kerusakan', 0);
            $catatan      = $this->post('catatan_denda', '');

            if ($this->model->prosesPengembalianAdmin($idPeminjaman, $kondisiBuku, $dendaTelat, $dendaRusak, $catatan)) {
                $totalDenda = $dendaTelat + $dendaRusak;
                $pesan = "Buku berhasil dikembalikan dan diterima pustakawan. Stok buku otomatis bertambah.";
                if ($totalDenda > 0) {
                    $pesan .= " Total denda: Rp " . number_format($totalDenda, 0, ',', '.');
                }
                $this->setFlash('success', $pesan);
            } else {
                $err = $this->model->getLastError() ?: 'Gagal memproses pengembalian buku.';
                $this->setFlash('danger', $err);
            }
        }
        $this->redirect('admin/pengembalian');
    }

    public function pengembalianProsesTanpaIzin(): void {
        if ($this->isPost()) {
            $idBuku   = (int)$this->post('id_buku');
            $denda    = (float)$this->post('nominal_denda', 0);
            $ket      = $this->post('keterangan', 'Pinjam Tanpa Izin');
            $idUser   = (int)$this->post('id_user', 0) ?: null;

            if ($this->model->prosesPengembalianTanpaIzin($idBuku, $denda, $ket, $idUser)) {
                $this->setFlash('success', "Buku berhasil dicatat kembali ke perpustakaan dengan sanksi denda sebesar Rp " . number_format($denda, 0, ',', '.') . ". Stok buku fisik bertambah.");
            } else {
                $err = $this->model->getLastError() ?: 'Gagal memproses pengembalian buku.';
                $this->setFlash('danger', $err);
            }
        }
        $this->redirect('admin/pengembalian');
    }

    public function apiScanIsbn(): void {
        $isbn = $this->get('isbn', '');
        $cleanIsbn = preg_replace('/[^0-9X]/i', '', $isbn);

        $existing = $this->model->findBukuByBarcodeOrIsbn($cleanIsbn);
        $this->json([
            'status'   => 'success',
            'isbn'     => $cleanIsbn,
            'existing' => $existing
        ]);
    }

    public function apiLookupIsbn(): void {
        $isbn = $this->get('isbn', '');
        $cleanIsbn = preg_replace('/[^0-9X]/i', '', $isbn);
        if (empty($cleanIsbn)) {
            $this->json(['status' => 'error', 'message' => 'ISBN kosong.'], 400);
            return;
        }

        // 1. Kamus Lokal Buku Nasional / Best Seller Terverifikasi
        $curated = [
            '9786020305882' => [
                'title'       => '131 Pintu Cahaya dari Timur',
                'authors'     => 'A. Fuadi',
                'publisher'   => 'Gramedia Pustaka Utama',
                'year'        => '2014',
                'category'    => 'Novel',
                'description' => 'Kumpulan 131 mutiara kata, pepatah, dan kata bijak dari novelis Negeri 5 Menara, A. Fuadi, yang dikurasi untuk menginspirasi pengembangan diri dan motivasi generasi muda.'
            ],
            '9786020312583' => [
                'title'       => 'Cantik Itu Luka',
                'authors'     => 'Eka Kurniawan',
                'publisher'   => 'Gramedia Pustaka Utama',
                'year'        => '2002',
                'category'    => 'Novel',
                'description' => 'Kisah magis dan berlatar sejarah tentang Dewi Ayu dan anak-anak perempuannya di Halimunda.'
            ],
            '9786020658049' => [
                'title'       => 'Home Sweet Loan',
                'authors'     => 'Almira Bastari',
                'publisher'   => 'Gramedia Pustaka Utama',
                'year'        => '2022',
                'category'    => 'Novel',
                'description' => 'Menceritakan perjuangan Kaluna mewujudkan impian memiliki rumah sendiri di tengah tuntutan sandwich generation.'
            ],
            '9789793062792' => [
                'title'       => 'Laskar Pelangi',
                'authors'     => 'Andrea Hirata',
                'publisher'   => 'Bentang Pustaka',
                'year'        => '2005',
                'category'    => 'Novel',
                'description' => 'Kisah inspiratif sepuluh anak Belitung Timur menggapai mimpi di sekolah serba terbatas.'
            ],
            '9789799731234' => [
                'title'       => 'Bumi Manusia',
                'authors'     => 'Pramoedya Ananta Toer',
                'publisher'   => 'Gramedia Pustaka Utama',
                'year'        => '2005',
                'category'    => 'Novel',
                'description' => 'Mahakarya roman sejarah Indonesia tentang Minke dan Annelies di masa kolonial akhir abad ke-19.'
            ],
            '9786020822341' => [
                'title'       => 'Hujan',
                'authors'     => 'Tere Liye',
                'publisher'   => 'Gramedia Pustaka Utama',
                'year'        => '2016',
                'category'    => 'Novel',
                'description' => 'Novel tentang persahabatan, cinta, perpisahan, dan teknologi fiksi ilmiah masa depan yang mengharukan karya Tere Liye.'
            ],
            '9786020324784' => [
                'title'       => 'Bumi',
                'authors'     => 'Tere Liye',
                'publisher'   => 'Gramedia Pustaka Utama',
                'year'        => '2014',
                'category'    => 'Novel',
                'description' => 'Petualangan fantasi tiga sahabat di dunia paralel klan Bumi, Bulan, dan Matahari.'
            ],
            '9786020332956' => [
                'title'       => 'Bulan',
                'authors'     => 'Tere Liye',
                'publisher'   => 'Gramedia Pustaka Utama',
                'year'        => '2015',
                'category'    => 'Novel',
                'description' => 'Sekuel petualangan Raib, Seli, dan Ali menjelajahi klan Matahari dalam Festival Bunga Matahari.'
            ],
            '9786020352947' => [
                'title'       => 'Matahari',
                'authors'     => 'Tere Liye',
                'publisher'   => 'Gramedia Pustaka Utama',
                'year'        => '2016',
                'category'    => 'Novel',
                'description' => 'Petualangan fantasi klan Bintang mencari rahasia masa lalu peradaban dunia paralel.'
            ],
            '9786020367200' => [
                'title'       => 'Bintang',
                'authors'     => 'Tere Liye',
                'publisher'   => 'Gramedia Pustaka Utama',
                'year'        => '2017',
                'category'    => 'Novel',
                'description' => 'Kisah puncak perjuangan tiga sekawan menyelamatkan klan Bumi dari kehancuran total.'
            ],
            '9789792248616' => [
                'title'       => 'Negeri 5 Menara',
                'authors'     => 'A. Fuadi',
                'publisher'   => 'Gramedia Pustaka Utama',
                'year'        => '2009',
                'category'    => 'Novel',
                'description' => 'Kisah persahabatan enam santri dari berbagai pelosok Indonesia di Pondok Madani dengan semboyan Man Jadda Wajada.'
            ],
            '9789792277630' => [
                'title'       => 'Ranah 3 Warna',
                'authors'     => 'A. Fuadi',
                'publisher'   => 'Gramedia Pustaka Utama',
                'year'        => '2011',
                'category'    => 'Novel',
                'description' => 'Perjuangan Alif Fikri menembus batas mimpi kuliah hingga ke benua Amerika dengan mantra Man Shabara Zhafira.'
            ],
            '9789791227025' => [
                'title'       => 'Ayat-Ayat Cinta',
                'authors'     => 'Habiburrahman El Shirazy',
                'publisher'   => 'Republika',
                'year'        => '2004',
                'category'    => 'Novel',
                'description' => 'Kisah cinta dan nilai-nilai religius mahasiswa Indonesia Fahri di Al-Azhar Kairo yang sangat populer.'
            ],
            '9789791227032' => [
                'title'       => 'Ketika Cinta Bertasbih',
                'authors'     => 'Habiburrahman El Shirazy',
                'publisher'   => 'Republika',
                'year'        => '2007',
                'category'    => 'Novel',
                'description' => 'Kisah Azzam gigih memperjuangkan ilmu dan nafkah keluarga di Kairo dengan penuh integritas.'
            ],
            '9786022912828' => [
                'title'       => 'Filosofi Kopi',
                'authors'     => 'Dee Lestari',
                'publisher'   => 'Bentang Pustaka',
                'year'        => '2006',
                'category'    => 'Novel',
                'description' => 'Kumpulan cerita pendek dan prosa puitis Dee Lestari tentang kehidupan, impian, dan racikan kopi.'
            ],
            '9789791227889' => [
                'title'       => 'Perahu Kertas',
                'authors'     => 'Dee Lestari',
                'publisher'   => 'Bentang Pustaka',
                'year'        => '2009',
                'category'    => 'Novel',
                'description' => 'Kisah cinta dan pencarian jati diri antara Kugy yang unik dan Keenan pemuda berbakat seni.'
            ],
            '9786024246945' => [
                'title'       => 'Laut Bercerita',
                'authors'     => 'Leila S. Chudori',
                'publisher'   => 'Gramedia Pustaka Utama',
                'year'        => '2017',
                'category'    => 'Novel',
                'description' => 'Novel bertema sejarah kemanusiaan tentang aktivis mahasiswa tahun 1998 yang diculik dan keluarganya yang mencari kebenaran.'
            ],
            '9786020331409' => [
                'title'       => 'Ronggeng Dukuh Paruk',
                'authors'     => 'Ahmad Tohari',
                'publisher'   => 'Gramedia Pustaka Utama',
                'year'        => '2003',
                'category'    => 'Novel',
                'description' => 'Karya sastra legendaris tentang penari ronggeng Srintil dan Rasus dengan latar sosial pedesaan Jawa.'
            ],
            '9786022443261' => [
                'title'       => 'Ilmu Pengetahuan Alam SMP Kelas VII',
                'authors'     => 'Victoriani Inabuy dkk.',
                'publisher'   => 'Erlangga',
                'year'        => '2021',
                'category'    => 'Karya Ilmiah',
                'description' => 'Buku teks resmi kurikulum nasional pembelajaran sains dan IPA terpadu untuk siswa SMP Kelas VII.'
            ],
            '9786022443278' => [
                'title'       => 'Matematika SMP Kelas VII',
                'authors'     => 'Tim Kemendikbud',
                'publisher'   => 'Erlangga',
                'year'        => '2021',
                'category'    => 'Panduan (how to)',
                'description' => 'Buku panduan dan ajar matematika siswa SMP Kelas VII kurikulum sekolah menengah pertama.'
            ],
            '9786022443285' => [
                'title'       => 'Bahasa Indonesia SMP Kelas VII',
                'authors'     => 'Rakhma Subarna dkk.',
                'publisher'   => 'Erlangga',
                'year'        => '2021',
                'category'    => 'Panduan (how to)',
                'description' => 'Buku ajar resmi mata pelajaran Bahasa Indonesia untuk kelas VII SMP/MTs.'
            ],
            '9786022443292' => [
                'title'       => 'English for Nusantara SMP Kelas VII',
                'authors'     => 'Ika Lestari Damayanti dkk.',
                'publisher'   => 'Erlangga',
                'year'        => '2021',
                'category'    => 'Panduan (how to)',
                'description' => 'Buku teks pembelajaran Bahasa Inggris kontekstual untuk siswa SMP Kelas VII.'
            ],
            '9786022443308' => [
                'title'       => 'Ilmu Pengetahuan Sosial SMP Kelas VII',
                'authors'     => 'M. Nursa\'ban dkk.',
                'publisher'   => 'Erlangga',
                'year'        => '2021',
                'category'    => 'Panduan (how to)',
                'description' => 'Buku teks IPS terpadu geografi, sejarah, ekonomi, dan sosiologi untuk SMP Kelas VII.'
            ],
            '9786022443315' => [
                'title'       => 'Pendidikan Pancasila dan Kewarganegaraan SMP Kelas VII',
                'authors'     => 'Zaim Uchrowi, Ruslin Ismail',
                'publisher'   => 'Erlangga',
                'year'        => '2021',
                'category'    => 'Panduan (how to)',
                'description' => 'Buku teks utama PPKn untuk SMP Kelas VII Kurikulum Merdeka.'
            ],
            '9786022443322' => [
                'title'       => 'Informatika SMP Kelas VII',
                'authors'     => 'Irya Wisnubhadra dkk.',
                'publisher'   => 'Erlangga',
                'year'        => '2021',
                'category'    => 'Panduan (how to)',
                'description' => 'Buku teks pembelajaran informatika, algoritma, dan teknologi digital SMP Kelas VII.'
            ],
            '9786020523316' => [
                'title'       => 'Komet',
                'authors'     => 'Tere Liye',
                'publisher'   => 'Gramedia Pustaka Utama',
                'year'        => '2018',
                'category'    => 'Novel',
                'description' => 'Petualangan klan Bumi, Bulan, dan Matahari menuju Pulau Terapung di klan Komet.'
            ],
            '9786020523323' => [
                'title'       => 'Komet Minor',
                'authors'     => 'Tere Liye',
                'publisher'   => 'Gramedia Pustaka Utama',
                'year'        => '2019',
                'category'    => 'Novel',
                'description' => 'Pertarungan pamungkas melawan Si Tanpa Mahkota di klan Komet Minor.'
            ],
            '9786020522883' => [
                'title'       => 'Atomic Habits: Perubahan Kecil yang Memberikan Hasil Luar Biasa',
                'authors'     => 'James Clear',
                'publisher'   => 'Gramedia Pustaka Utama',
                'year'        => '2019',
                'category'    => 'Panduan (how to)',
                'description' => 'Cara praktis dan terbukti untuk membentuk kebiasaan baik dan meruntuhkan kebiasaan buruk.'
            ],
            '9786020633177' => [
                'title'       => 'Sebuah Seni untuk Bersikap Bodo Amat',
                'authors'     => 'Mark Manson',
                'publisher'   => 'Gramedia Pustaka Utama',
                'year'        => '2018',
                'category'    => 'Panduan (how to)',
                'description' => 'Pendekatan waras dan realistis demi menjalani hidup yang lebih bermakna dan tenang.'
            ],
            '9786020653600' => [
                'title'       => 'Filosofi Teras: Filsafat Yunani-Kuno untuk Mental Tangguh',
                'authors'     => 'Henry Manampiring',
                'publisher'   => 'BukuKita / Kompas Gramedia',
                'year'        => '2018',
                'category'    => 'Panduan (how to)',
                'description' => 'Penerapan praktis filsafat Stoisisme dalam mengelola emosi negatif dan kekhawatiran hidup sehari-hari.'
            ],
            '9786020633176' => [
                'title'       => 'Garis Waktu',
                'authors'     => 'Fiersa Besari',
                'publisher'   => 'Media Kita / BukuKita',
                'year'        => '2016',
                'category'    => 'Novel',
                'description' => 'Untaian prosa dan kisah perjalanan rasa, waktu, patah hati, dan menemukan kembali tujuan diri.'
            ],
            '9789797945398' => [
                'title'       => 'Konspirasi Alam Semesta',
                'authors'     => 'Fiersa Besari',
                'publisher'   => 'Media Kita / BukuKita',
                'year'        => '2017',
                'category'    => 'Novel',
                'description' => 'Album musik sastra tentang Juang Astrajingga dan Ana Tidae di bawah langit nusantara.'
            ],
            '9786022916628' => [
                'title'       => 'Orang-Orang Biasa',
                'authors'     => 'Andrea Hirata',
                'publisher'   => 'Bentang Pustaka',
                'year'        => '2019',
                'category'    => 'Novel',
                'description' => 'Kisah satir jenaka sepuluh sahabat kota Belantik yang berencana melakukan misi nekat demi masa depan.'
            ]
        ];

        if (isset($curated[$cleanIsbn])) {
            $matched = $curated[$cleanIsbn];
            $pub = $matched['publisher'] ?? '';
            $source = 'Database Kurasi Nasional';
            if (stripos($pub, 'Gramedia') !== false) {
                $source = 'Katalog Gramedia';
            } elseif (stripos($pub, 'BukuKita') !== false || stripos($pub, 'Media Kita') !== false) {
                $source = 'Katalog BukuKita';
            } elseif (stripos($pub, 'Erlangga') !== false || stripos($pub, 'Kemendikbud') !== false) {
                $source = 'Buku Pelajaran Nasional';
            }
            $this->json([
                'status' => 'success',
                'source' => $source,
                'data'   => $matched
            ]);
            return;
        }

        // 2. Cek apakah sudah ada di tabel buku lokal
        $existing = $this->model->findBukuByBarcodeOrIsbn($cleanIsbn);
        if ($existing) {
            $this->json([
                'status' => 'success',
                'source' => 'Local Perpustakaan Database',
                'data'   => [
                    'title'       => $existing['judul_buku'],
                    'authors'     => $existing['pengarang'],
                    'publisher'   => $existing['penerbit_buku'],
                    'year'        => $existing['tahun_terbit'],
                    'category'    => $existing['kategori_buku'],
                    'description' => $existing['sinopsis'] ?? ''
                ]
            ]);
            return;
        }

        // 3. Pencarian Live via Katalog BukuKita
        try {
            $bkData = $this->lookupBukuKitaOnline($cleanIsbn);
            if ($bkData) {
                $this->json([
                    'status' => 'success',
                    'source' => 'Katalog BukuKita',
                    'data'   => $bkData
                ]);
                return;
            }
        } catch (\Throwable $eBk) {}

        // 4. Pencarian Live via Katalog Gramedia Online
        try {
            $gramData = $this->lookupGramediaOnline($cleanIsbn);
            if ($gramData) {
                $this->json([
                    'status' => 'success',
                    'source' => 'Katalog Gramedia',
                    'data'   => $gramData
                ]);
                return;
            }
        } catch (\Throwable $eGm) {}

        // 5. Coba Google Books API (Server cURL Fallback)
        try {
            $gbUrl = 'https://www.googleapis.com/books/v1/volumes?q=isbn:' . urlencode($cleanIsbn);
            $rawGb = $this->curlHttpGet($gbUrl, 3);
            if ($rawGb) {
                $gb = json_decode($rawGb, true);
                if (!empty($gb['items'][0]['volumeInfo'])) {
                    $info = $gb['items'][0]['volumeInfo'];
                    $this->json([
                        'status' => 'success',
                        'source' => 'Google Books API',
                        'data'   => [
                            'title'       => $info['title'] ?? '',
                            'authors'     => !empty($info['authors']) ? implode(', ', $info['authors']) : '',
                            'publisher'   => $info['publisher'] ?? '',
                            'year'        => !empty($info['publishedDate']) ? substr($info['publishedDate'], 0, 4) : '',
                            'category'    => !empty($info['categories']) ? implode(', ', $info['categories']) : '',
                            'description' => $info['description'] ?? ''
                        ]
                    ]);
                    return;
                }
            }
        } catch (\Throwable $eGb) {}

        // 6. Coba OpenLibrary API
        try {
            $olUrl = 'https://openlibrary.org/api/books?bibkeys=ISBN:' . urlencode($cleanIsbn) . '&format=json&jscmd=data';
            $rawOl = $this->curlHttpGet($olUrl, 3);
            if ($rawOl) {
                $olData = json_decode($rawOl, true);
                $olKey = 'ISBN:' . $cleanIsbn;
                if (!empty($olData[$olKey])) {
                    $b = $olData[$olKey];
                    $authors = [];
                    if (!empty($b['authors'])) {
                        foreach ($b['authors'] as $a) {
                            $authors[] = $a['name'] ?? '';
                        }
                    }
                    $publishers = [];
                    if (!empty($b['publishers'])) {
                        foreach ($b['publishers'] as $p) {
                            $publishers[] = $p['name'] ?? '';
                        }
                    }
                    $year = !empty($b['publish_date']) ? preg_replace('/[^0-9]/', '', substr($b['publish_date'], -4)) : '';
                    $desc = '';
                    if (!empty($b['description'])) {
                        $desc = is_string($b['description']) ? $b['description'] : ($b['description']['value'] ?? '');
                    }

                    $this->json([
                        'status' => 'success',
                        'source' => 'OpenLibrary',
                        'data'   => [
                            'title'       => $b['title'] ?? '',
                            'authors'     => implode(', ', array_filter($authors)),
                            'publisher'   => implode(', ', array_filter($publishers)),
                            'year'        => $year,
                            'category'    => !empty($b['subjects'][0]['name']) ? $b['subjects'][0]['name'] : 'Buku Referensi',
                            'description' => $desc
                        ]
                    ]);
                    return;
                }
            }
        } catch (\Throwable $eOl) {}

        $this->json([
            'status'  => 'not_found',
            'message' => 'Metadata buku untuk ISBN ini belum terindeks di Gramedia, BukuKita, Google Books, atau OpenLibrary.'
        ]);
    }

    private function curlHttpGet(string $url, int $timeout = 4): ?string {
        if (!function_exists('curl_init')) {
            $ctx = stream_context_create(['http' => ['timeout' => $timeout]]);
            $res = @file_get_contents($url, false, $ctx);
            return $res !== false ? $res : null;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res !== false ? $res : null;
    }

    private function lookupBukuKitaOnline(string $cleanIsbn): ?array {
        $url = 'https://bukukita.com/katalogbuku.php?buku=' . urlencode($cleanIsbn);
        $html = $this->curlHttpGet($url, 4);
        if (!$html || strpos($html, 'No. ISBN') === false) {
            return null;
        }
        $data = [];
        if (preg_match('/<title>(.*?)<\/title>/i', $html, $m)) {
            $title = trim(explode('|', $m[1])[0]);
            $title = preg_replace('/^Buku\s+/i', '', $title);
            $data['title'] = trim($title);
        }
        $text = strip_tags($html);
        if (preg_match('/Penulis\s*[\r\n\t]+([^\r\n\t]+)/i', $text, $m)) {
            $data['authors'] = trim($m[1]);
        }
        if (preg_match('/Penerbit\s*[\r\n\t]+([^\r\n\t]+)/i', $text, $m)) {
            $data['publisher'] = trim(html_entity_decode(str_replace('&nbsp;', ' ', $m[1])));
        }
        if (preg_match('/Tanggal terbit\s*[\r\n\t]+([^\r\n\t]+)/i', $text, $m)) {
            if (preg_match('/(19|20)\d{2}/', $m[1], $my)) {
                $data['year'] = $my[0];
            }
        }
        if (preg_match('/Kategori\s*[\r\n\t]+([^\r\n\t]+)/i', $text, $m)) {
            $data['category'] = trim($m[1]);
        }
        if (preg_match('/DESCRIPTION\s*[\r\n\t]+(.*?)(?=Goodreads|WHY CHOOSE US|Produk digital|$)/is', $text, $m)) {
            $desc = trim($m[1]);
            $desc = preg_replace('/^\[NOTE\].*?\n+/i', '', $desc);
            $data['description'] = trim($desc);
        }
        return !empty($data['title']) ? $data : null;
    }

    private function lookupGramediaOnline(string $cleanIsbn): ?array {
        $url = 'https://www.gramedia.com/api/products/?search=' . urlencode($cleanIsbn);
        $raw = $this->curlHttpGet($url, 3);
        if ($raw) {
            $json = json_decode($raw, true);
            if (!empty($json[0]['name']) || !empty($json['data'][0]['name'])) {
                $item = $json[0] ?? $json['data'][0];
                return [
                    'title'       => $item['name'] ?? '',
                    'authors'     => !empty($item['authors'][0]['name']) ? $item['authors'][0]['name'] : ($item['author'] ?? ''),
                    'publisher'   => $item['publisher'] ?? 'Gramedia Pustaka Utama',
                    'year'        => !empty($item['publish_date']) ? substr($item['publish_date'], 0, 4) : date('Y'),
                    'category'    => $item['category'] ?? 'Novel',
                    'description' => $item['description'] ?? ''
                ];
            }
        }
        return null;
    }

    public function apiScanImage(): void {
        $tempPath = '';
        if (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
            $tempPath = $_FILES['image']['tmp_name'];
        } else {
            $base64 = $this->post('image_base64', '');
            if (!$base64) {
                $raw = file_get_contents('php://input');
                $json = json_decode($raw, true);
                $base64 = $json['image_base64'] ?? '';
            }

            if ($base64) {
                if (strpos($base64, ',') !== false) {
                    $base64 = explode(',', $base64)[1];
                }
                $decoded = base64_decode($base64);
                if ($decoded) {
                    $tempDir = __DIR__ . '/../assets/tmp';
                    if (!is_dir($tempDir)) {
                        @mkdir($tempDir, 0777, true);
                    }
                    $tempFile = $tempDir . '/scan_' . uniqid() . '.jpg';
                    file_put_contents($tempFile, $decoded);
                    $tempPath = $tempFile;
                }
            }
        }

        if (empty($tempPath) || !file_exists($tempPath)) {
            $this->json(['status' => 'error', 'message' => 'Tidak ada gambar yang dikirim untuk dipindai.'], 400);
            return;
        }

        $pythonPaths = [
            'C:\\Users\\yasha\\AppData\\Local\\Programs\\Python\\Python313\\python.exe',
            'python',
            'python3',
            'py'
        ];
        $pythonExec = 'python';
        foreach ($pythonPaths as $p) {
            if (file_exists($p)) {
                $pythonExec = $p;
                break;
            }
        }

        $script = __DIR__ . '/../models/scan_barcode.py';
        $cmd = escapeshellarg($pythonExec) . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($tempPath);
        $output = shell_exec($cmd);

        if (isset($tempFile) && file_exists($tempFile)) {
            @unlink($tempFile);
        }

        if ($output) {
            $res = json_decode(trim($output), true);
            if ($res && !empty($res['code'])) {
                $this->json([
                    'status' => 'success',
                    'code'   => $res['code'],
                    'angle'  => $res['angle'] ?? 0
                ]);
                return;
            }
        }

        $this->json([
            'status'  => 'not_found',
            'message' => 'Barcode tidak terdeteksi pada gambar.'
        ]);
    }

    public function peminjamanEdit(): void {
        if ($this->isPost()) {
            $id = (int)$this->post('id_peminjaman');
            $data = [
                'tanggal_pengembalian'           => $this->post('tanggal_pengembalian'),
                'kondisi_buku_saat_dikembalikan' => $this->post('kondisi_buku_saat_dikembalikan'),
                'denda'                          => $this->post('denda') ?: 'Tidak ada',
                'status_transaksi'               => 'Selesai'
            ];

            if ($this->model->updatePeminjaman($id, $data)) {
                $this->setFlash('success', 'Data transaksi peminjaman/pengembalian berhasil diperbarui. Stok buku diperbarui secara otomatis.');
            } else {
                $err = $this->model->getLastError() ?: 'Gagal memperbarui transaksi peminjaman.';
                $this->setFlash('danger', $err);
            }
        }
        $this->redirect('admin/peminjaman');
    }

    public function peminjamanKonfirmasiPinjam(): void {
        if ($this->isPost()) {
            $id = (int)$this->post('id_peminjaman');
            if ($id && $this->model->konfirmasiPinjam($id)) {
                $this->setFlash('success', 'Peminjaman buku oleh siswa BERHASIL DISETUJUI (Dikonfirmasi).');
            } else {
                $err = $this->model->getLastError() ?: 'Gagal mengonfirmasi peminjaman.';
                $this->setFlash('danger', $err);
            }
        }
        $this->redirect('admin/peminjaman');
    }

    public function peminjamanKonfirmasiKembali(): void {
        if ($this->isPost()) {
            $id = (int)$this->post('id_peminjaman');
            $data = [
                'tanggal_pengembalian'           => $this->post('tanggal_pengembalian') ?: date('d-m-Y'),
                'kondisi_buku_saat_dikembalikan' => $this->post('kondisi_buku_saat_dikembalikan') ?: 'Baik',
                'denda'                          => $this->post('denda') ?: 'Tidak ada'
            ];

            if ($id && $this->model->konfirmasiKembali($id, $data)) {
                $this->setFlash('success', 'Pengembalian buku berhasil diverifikasi & diterima. Stok buku otomatis bertambah.');
            } else {
                $err = $this->model->getLastError() ?: 'Gagal memverifikasi pengembalian.';
                $this->setFlash('danger', $err);
            }
        }
        $this->redirect('admin/peminjaman');
    }

    public function peminjamanRealtimeCheck(): void {
        header('Content-Type: application/json; charset=utf-8');
        $pending = $this->model->getPendingCount();
        $totalLoans = $this->model->countPeminjaman();
        $totalReturns = $this->model->countPengembalian();
        echo json_encode([
            'status'         => 'success',
            'pending_total'  => $pending['total'],
            'pending_pinjam' => $pending['pending_pinjam'],
            'pending_kembali'=> $pending['pending_kembali'],
            'max_id'         => $pending['max_id'],
            'total_loans'    => $totalLoans,
            'total_returns'  => $totalReturns,
            'timestamp'      => time()
        ]);
        exit;
    }

    public function peminjamanDelete(): void {
        $id = (int)$this->post('id_peminjaman', $this->get('id'));
        if ($id && $this->model->deletePeminjaman($id)) {
            $this->setFlash('success', 'Data peminjaman berhasil dihapus.');
        } else {
            $err = $this->model->getLastError() ?: 'Gagal menghapus data peminjaman.';
            $this->setFlash('danger', $err);
        }
        $this->redirect('admin/peminjaman');
    }

    // ==========================================
    // DATA BUKU
    // ==========================================
    public function buku(): void {
        $bukuList      = $this->model->getAllBuku();
        $kategoriList  = $this->model->getAllKategori();
        $penerbitList  = $this->model->getAllPenerbit();
        $pengarangList = $this->model->getAllPengarang();
        $this->view('admin/buku', [
            'bukuList'      => $bukuList,
            'kategoriList'  => $kategoriList,
            'penerbitList'  => $penerbitList,
            'pengarangList' => $pengarangList,
            'flash'         => $this->getFlash(),
            'activeMenu'    => 'buku'
        ]);
    }

    public function bukuAdd(): void {
        if ($this->isPost()) {
            $isbn = trim($this->post('isbn', ''));
            $cleanIsbn = preg_replace('/[^0-9X]/i', '', $isbn);
            $action = $this->post('duplicate_action', 'insert');

            $data = [
                'id_kategori'          => (int)$this->post('id_kategori', 0),
                'id_penerbit'          => (int)$this->post('id_penerbit', 0),
                'id_pengarang'         => (int)$this->post('id_pengarang', 0),
                'penerbit_buku_custom' => trim($this->post('penerbit_buku_custom', '')),
                'judul_buku'           => $this->post('judul_buku'),
                'kategori_buku'        => $this->post('kategori_buku'),
                'penerbit_buku'        => $this->post('penerbit_buku'),
                'pengarang'            => $this->post('pengarang'),
                'tahun_terbit'         => $this->post('tahun_terbit'),
                'isbn'                 => $cleanIsbn,
                'j_buku_baik'          => (int)$this->post('j_buku_baik', 0),
                'j_buku_rusak'         => (int)$this->post('j_buku_rusak', 0),
                'j_buku_hilang'        => (int)$this->post('j_buku_hilang', 0),
                'harga'                => (int)preg_replace('/[^0-9]/', '', (string)$this->post('harga', '65000')),
                'sinopsis'             => $this->post('sinopsis', ''),
                'cover'                => null
            ];

            // Upload cover gambar manual jika disediakan
            if (!empty($_FILES['cover_image']['tmp_name']) && is_uploaded_file($_FILES['cover_image']['tmp_name'])) {
                $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $coverName = (!empty($cleanIsbn) ? $cleanIsbn : preg_replace('/[^a-z0-9_-]/i', '_', strtolower($data['judul_buku']))) . '.' . $ext;
                    $destDir = dirname(__DIR__) . '/cover_buku/';
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0777, true);
                    }
                    if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $destDir . $coverName)) {
                        $data['cover'] = $coverName;
                    }
                }
            }

            // Cek Pencegahan Duplikat Buku (Berdasarkan ISBN atau Judul + Pengarang)
            $existing = null;
            if (!empty($cleanIsbn)) {
                $existing = $this->model->findBukuByBarcodeOrIsbn($cleanIsbn);
            }
            if (!$existing && !empty($data['judul_buku']) && !empty($data['pengarang'])) {
                $existing = $this->model->findBukuByJudulDanPengarang($data['judul_buku'], $data['pengarang']);
            }

            if ($existing) {
                if ($action === 'tambah_stok') {
                    // Update penambahan stok pada buku yang sudah ada (tanpa membuat baris baru)
                    $tambahBaik   = (int)$data['j_buku_baik'];
                    $tambahRusak  = (int)$data['j_buku_rusak'];
                    $tambahHilang = (int)$data['j_buku_hilang'];

                    $newBaik   = (int)$existing['j_buku_baik'] + $tambahBaik;
                    $newRusak  = (int)$existing['j_buku_rusak'] + $tambahRusak;
                    $newHilang = (int)$existing['j_buku_hilang'] + $tambahHilang;

                    $updateData = [
                        'id_kategori'   => (int)$existing['id_kategori'],
                        'id_penerbit'   => (int)$existing['id_penerbit'],
                        'judul_buku'    => $existing['judul_buku'],
                        'kategori_buku' => $existing['kategori_buku'],
                        'penerbit_buku' => $existing['penerbit_buku'],
                        'pengarang'     => $existing['pengarang'],
                        'tahun_terbit'  => $existing['tahun_terbit'],
                        'isbn'          => $existing['isbn'],
                        'j_buku_baik'   => $newBaik,
                        'j_buku_rusak'  => $newRusak,
                        'j_buku_hilang' => $newHilang,
                        'sinopsis'      => !empty($data['sinopsis']) ? $data['sinopsis'] : ($existing['sinopsis'] ?? ''),
                        'cover'         => $data['cover'] ?: ($existing['cover'] ?? null)
                    ];

                    if ($this->model->updateBuku((int)$existing['id_buku'], $updateData)) {
                        $this->setFlash('success', "Pencegahan Duplikat: Buku '{$existing['judul_buku']}' sudah terdaftar. Stok berhasil ditambahkan (+{$tambahBaik} eksemplar). Total stok saat ini: {$newBaik} buku.");
                    } else {
                        $this->setFlash('danger', 'Gagal memperbarui stok buku yang sudah ada.');
                    }
                } else {
                    // Blokir duplikat dan beri tahu admin
                    $this->setFlash('warning', "Pencegahan Duplikat: Buku dengan ISBN/Judul tersebut sudah terdaftar di perpustakaan ('{$existing['judul_buku']}'). Sistem mencegah duplikasi entri buku yang sama.");
                }
            } else {
                // Buku belum terdaftar -> simpan sebagai buku baru
                if ($this->model->createBuku($data)) {
                    $this->setFlash('success', "Data buku baru '{$data['judul_buku']}' berhasil ditambahkan ke katalog perpustakaan.");
                } else {
                    $err = $this->model->getLastError() ?: 'Gagal menambahkan buku.';
                    $this->setFlash('danger', $err);
                }
            }
        }
        $this->redirect('admin/buku');
    }

    public function bukuEdit(): void {
        if ($this->isPost()) {
            $id = (int)$this->post('id_buku');
            $cleanIsbn = preg_replace('/[^0-9X]/i', '', $this->post('isbn', ''));
            $data = [
                'id_kategori'   => (int)$this->post('id_kategori', 0),
                'id_penerbit'   => (int)$this->post('id_penerbit', 0),
                'id_pengarang'  => (int)$this->post('id_pengarang', 0),
                'judul_buku'    => $this->post('judul_buku'),
                'kategori_buku' => $this->post('kategori_buku'),
                'penerbit_buku' => $this->post('penerbit_buku'),
                'pengarang'     => $this->post('pengarang'),
                'tahun_terbit'  => $this->post('tahun_terbit'),
                'isbn'          => $cleanIsbn,
                'j_buku_baik'   => (int)$this->post('j_buku_baik', 0),
                'j_buku_rusak'  => (int)$this->post('j_buku_rusak', 0),
                'j_buku_hilang' => (int)$this->post('j_buku_hilang', 0),
                'harga'         => (int)preg_replace('/[^0-9]/', '', (string)$this->post('harga', '65000')),
                'sinopsis'      => $this->post('sinopsis', '')
            ];

            // Upload cover gambar baru jika diunggah
            if (!empty($_FILES['cover_image']['tmp_name']) && is_uploaded_file($_FILES['cover_image']['tmp_name'])) {
                $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $coverName = (!empty($cleanIsbn) ? $cleanIsbn : preg_replace('/[^a-z0-9_-]/i', '_', strtolower($data['judul_buku']))) . '.' . $ext;
                    $destDir = dirname(__DIR__) . '/cover_buku/';
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0777, true);
                    }
                    if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $destDir . $coverName)) {
                        $data['cover'] = $coverName;
                    }
                }
            }

            if ($this->model->updateBuku($id, $data)) {
                $this->model->cleanOrphanPengarang();
                $this->model->cleanOrphanPenerbit();
                $this->setFlash('success', 'Data buku berhasil diperbarui.');
            } else {
                $err = $this->model->getLastError() ?: 'Gagal memperbarui data buku.';
                $this->setFlash('danger', $err);
            }
        }
        $this->redirect('admin/buku');
    }

    public function bukuDelete(): void {
        $id = (int)$this->post('id_buku', $this->get('id'));
        if ($id && $this->model->deleteBuku($id)) {
            $this->model->cleanOrphanPengarang();
            $this->model->cleanOrphanPenerbit();
            $this->setFlash('success', 'Data buku berhasil dihapus.');
        } else {
            $err = $this->model->getLastError() ?: 'Gagal menghapus data buku.';
            $this->setFlash('danger', $err);
        }
        $this->redirect('admin/buku');
    }

    // ==========================================
    // DATA KATEGORI
    // ==========================================
    public function kategori(): void {
        $kategoriList = $this->model->getAllKategori();
        $autoKode = $this->model->getNextKodeKategori();

        $bukuPerKategori = [];
        foreach ($kategoriList as $kat) {
            $bukuPerKategori[$kat['id_kategori']] = $this->model->getBukuByKategoriId((int)$kat['id_kategori']);
        }

        $this->view('admin/kategori', [
            'kategoriList'    => $kategoriList,
            'bukuPerKategori' => $bukuPerKategori,
            'autoKode'        => $autoKode,
            'flash'           => $this->getFlash(),
            'activeMenu'      => 'kategori'
        ]);
    }

    public function kategoriAdd(): void {
        if ($this->isPost()) {
            $data = [
                'kode_kategori' => $this->post('kode_kategori') ?: $this->model->getNextKodeKategori(),
                'nama_kategori' => $this->post('nama_kategori', '')
            ];

            if ($this->model->createKategori($data)) {
                $this->setFlash('success', 'Kategori buku berhasil ditambahkan.');
            } else {
                $err = $this->model->getLastError() ?: 'Gagal menambahkan kategori.';
                $this->setFlash('danger', $err);
            }
        }
        $this->redirect('admin/kategori');
    }

    public function kategoriEdit(): void {
        if ($this->isPost()) {
            $id = (int)$this->post('id_kategori');
            $data = [
                'nama_kategori' => $this->post('nama_kategori')
            ];

            if ($this->model->updateKategori($id, $data)) {
                $this->setFlash('success', 'Kategori buku berhasil diperbarui.');
            } else {
                $err = $this->model->getLastError() ?: 'Gagal memperbarui kategori.';
                $this->setFlash('danger', $err);
            }
        }
        $this->redirect('admin/kategori');
    }

    public function kategoriDelete(): void {
        $id = (int)$this->post('id_kategori', $this->get('id'));
        if ($id && $this->model->deleteKategori($id)) {
            $this->setFlash('success', 'Kategori buku berhasil dihapus.');
        } else {
            $err = $this->model->getLastError() ?: 'Gagal menghapus kategori.';
            $this->setFlash('danger', $err);
        }
        $this->redirect('admin/kategori');
    }

    // ==========================================
    // IDENTITAS APLIKASI
    // ==========================================
    public function identitas(): void {
        if ($this->isPost()) {
            $id = (int)$this->post('id_identitas', 1);
            $data = [
                'nama_app'   => $this->post('nama_app'),
                'alamat_app' => $this->post('alamat_app'),
                'email_app'  => $this->post('email_app'),
                'nomor_hp'   => $this->post('nomor_hp'),
                'latitude'   => $this->post('latitude', '-4.9754'),
                'longitude'  => $this->post('longitude', '105.3188')
            ];

            if ($this->model->updateIdentitas($id, $data)) {
                $this->setFlash('success', 'Identitas aplikasi perpustakaan berhasil diperbarui.');
            } else {
                $this->setFlash('danger', 'Gagal memperbarui identitas aplikasi.');
            }
            $this->redirect('admin/identitas');
        }

        $identitas = $this->model->getIdentitas();
        $this->view('admin/identitas', [
            'identitas'  => $identitas,
            'flash'      => $this->getFlash(),
            'activeMenu' => 'identitas'
        ]);
    }
}

