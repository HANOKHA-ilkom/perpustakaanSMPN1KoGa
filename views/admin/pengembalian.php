<?php
// Sirkulasi Pengembalian Buku View
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$pageTitle = 'Sirkulasi Pengembalian Buku - Perpustakaan SMPN 1 Kota Gajah';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/sidebar_admin.php';
$base = (new Controller())->baseUrl();
?>

<!-- Barcode Scanning Libraries (ZXing & Perpus Omni-Directional Engine) -->
<script src="https://unpkg.com/@zxing/library@0.21.3/umd/index.min.js"></script>
<script src="<?= $base ?>assets/js/perpus-scanner.js"></script>

<style>
/* Styling Elemen Enum & Selection Pengembalian */
.enum-card {
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
  position: relative;
  user-select: none;
}
.enum-card:hover {
  border-color: #cbd5e1;
  background: #f8fafc;
  transform: translateY(-1px);
}
.enum-card.active-baik {
  border-color: #28a745 !important;
  background-color: #f0fdf4 !important;
  box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
}
.enum-card.active-rusak {
  border-color: #f59e0b !important;
  background-color: #fffbeb !important;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.25);
}
.enum-card.active-hilang {
  border-color: #dc3545 !important;
  background-color: #fef2f2 !important;
  box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.25);
}

/* Styling Card List Input Manual */
.loan-manual-card {
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease-in-out;
}
.loan-manual-card:hover {
  background-color: #f8fafc;
  border-color: #28a745 !important;
  transform: translateY(-1px);
}
.loan-manual-card.selected {
  background-color: #f0fdf4 !important;
  border-color: #28a745 !important;
  box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.25);
}

/* Modal Dialog Scrollable Helpers */
.modal-dialog-scrollable .modal-content {
  max-height: calc(100vh - 2rem) !important;
}
.modal-dialog-scrollable .modal-body {
  overflow-y: auto !important;
  max-height: calc(100vh - 160px) !important;
}
</style>

<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-undo-alt text-success mr-2"></i> Sirkulasi Pengembalian Buku
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= $base ?>admin/dashboard">Home</a></li>
            <li class="breadcrumb-item active">Pengembalian Buku</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">

      <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show shadow-sm" role="alert">
          <?= $flash['message'] ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <?php endif; ?>

      <!-- Action Card & Quick Buttons -->
      <div class="card card-outline card-success shadow-sm mb-4">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-7 mb-3 mb-md-0">
              <h5 class="font-weight-bold text-dark mb-1">Meja Sirkulasi Pengembalian Buku Fisik</h5>
              <p class="text-muted text-sm mb-0">
                Pindai kode batang (barcode EAN-13 / ISBN) di sampul belakang buku menggunakan kamera laptop atau DroidCam HP. Sistem otomatis mengecek data peminjaman, menghitung denda keterlambatan (Rp 5.000/hari), dan mencatat kondisi fisik buku.
              </p>
            </div>
            <div class="col-md-5 text-md-right">
              <button type="button" class="btn btn-success font-weight-bold px-3 py-2 shadow-sm mr-2" id="btnBukaScannerKembali">
                <i class="fas fa-barcode mr-1"></i> Scan Barcode Pengembalian
              </button>
              <button type="button" class="btn btn-outline-secondary font-weight-bold px-3 py-2 shadow-sm" data-toggle="modal" data-target="#modalKembaliManual">
                <i class="fas fa-keyboard mr-1"></i> Input Manual
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Active Loans Table (Buku Sedang Dipinjam) -->
      <div class="card shadow-sm">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
          <h5 class="card-title font-weight-bold mb-0">
            <i class="fas fa-clock text-warning mr-1"></i> Daftar Buku yang Sedang Dipinjam (<?= count($activeLoans) ?> Transaksi Aktif)
          </h5>
          <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 250px;">
              <input type="text" id="tableSearch" class="form-control" placeholder="Cari siswa, NIS, judul...">
              <div class="input-group-append">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover table-striped mb-0" id="dataTable">
            <thead class="bg-light">
              <tr>
                <th width="5%">No</th>
                <th>Siswa Peminjam</th>
                <th>Buku yang Dipinjam</th>
                <th>Tgl Pinjam</th>
                <th>Jatuh Tempo</th>
                <th>Status Keterlambatan</th>
                <th width="15%" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($activeLoans)): ?>
                <tr>
                  <td colspan="7" class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i>
                    Tidak ada buku yang sedang dipinjam saat ini. Semua buku telah dikembalikan ke perpustakaan.
                  </td>
                </tr>
              <?php else: ?>
                <?php 
                $no = 1;
                $today = new DateTime();
                $today->setTime(0, 0, 0);

                foreach ($activeLoans as $row): 
                  $tglPinjamStr = $row['tanggal_peminjaman'];
                  $tglPinjamObj = DateTime::createFromFormat('d-m-Y', $tglPinjamStr) ?: new DateTime();
                  $tglJatuhTempo = clone $tglPinjamObj;
                  $tglJatuhTempo->modify('+7 days');
                  $tglJatuhTempo->setTime(0, 0, 0);

                  $terlambatHari = 0;
                  $dendaTelat = 0;
                  if ($today > $tglJatuhTempo) {
                    $diff = $tglJatuhTempo->diff($today);
                    $terlambatHari = (int)$diff->days;
                    $dendaTelat = $terlambatHari * 5000;
                  }
                ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td>
                      <strong class="text-primary"><?= htmlspecialchars($row['fullname'] ?: $row['nama_anggota']) ?></strong>
                      <div class="text-xs text-muted">NIS: <?= htmlspecialchars($row['nis'] ?: '-') ?> | Kelas: <?= htmlspecialchars($row['kelas'] ?: '-') ?></div>
                    </td>
                    <td>
                      <strong class="text-dark"><?= htmlspecialchars($row['judul_buku']) ?></strong>
                      <div class="text-xs text-muted">ISBN: <?= htmlspecialchars($row['isbn'] ?: '-') ?></div>
                    </td>
                    <td><?= htmlspecialchars($row['tanggal_peminjaman']) ?></td>
                    <td>
                      <span class="<?= $terlambatHari > 0 ? 'text-danger font-weight-bold' : '' ?>">
                        <?= $tglJatuhTempo->format('d-m-Y') ?>
                      </span>
                    </td>
                    <td>
                      <?php if ($terlambatHari > 0): ?>
                        <span class="badge badge-danger px-2 py-1">
                          <i class="fas fa-exclamation-triangle mr-1"></i> Telat <?= $terlambatHari ?> Hari (Denda Rp <?= number_format($dendaTelat, 0, ',', '.') ?>)
                        </span>
                      <?php else: ?>
                        <span class="badge badge-success px-2 py-1">
                          <i class="fas fa-check-circle mr-1"></i> Tepat Waktu
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <button type="button" class="btn btn-sm btn-success btn-proses-kembali"
                              data-id="<?= $row['id_peminjaman'] ?>"
                              data-nama="<?= htmlspecialchars($row['fullname'] ?: $row['nama_anggota']) ?>"
                              data-nis="<?= htmlspecialchars($row['nis'] ?: '-') ?>"
                              data-kelas="<?= htmlspecialchars($row['kelas'] ?: '-') ?>"
                              data-judul="<?= htmlspecialchars($row['judul_buku']) ?>"
                              data-isbn="<?= htmlspecialchars($row['isbn'] ?: '-') ?>"
                              data-pinjam="<?= htmlspecialchars($row['tanggal_peminjaman']) ?>"
                              data-tempo="<?= $tglJatuhTempo->format('d-m-Y') ?>"
                              data-telat="<?= $terlambatHari ?>"
                              data-dendatelat="<?= $dendaTelat ?>">
                        <i class="fas fa-check mr-1"></i> Terima Buku
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ==================================================== -->
      <!-- GRAFIK JUMLAH PENGEMBALIAN DALAM SEBULAN (CHART.JS) -->
      <!-- ==================================================== -->
      <div class="card card-outline card-success shadow-sm mt-4">
        <div class="card-header border-0 d-flex justify-content-between align-items-center flex-wrap">
          <div>
            <h5 class="card-title font-weight-bold text-dark mb-0">
              <i class="fas fa-chart-bar text-success mr-2"></i> Grafik Tren Pengembalian Buku dalam Sebulan
            </h5>
            <small class="text-muted d-block mt-1">
              Statistik harian penerimaan kembali buku fisik ke perpustakaan pada periode <strong><?= htmlspecialchars($chartDailyReturns['month_name'] ?? date('F Y')) ?></strong>
            </small>
          </div>
          <div class="mt-2 mt-md-0">
            <span class="badge badge-success px-3 py-2 mr-1">
              <i class="fas fa-undo-alt mr-1"></i> Total Kembali: <strong><?= (int)($chartDailyReturns['total'] ?? 0) ?> buku</strong>
            </span>
            <span class="badge badge-primary px-3 py-2 mr-1">
              <i class="fas fa-coins mr-1"></i> Kas Denda: <strong>Rp <?= number_format($chartDailyReturns['total_denda'] ?? 0, 0, ',', '.') ?></strong>
            </span>
            <span class="badge badge-light border px-2 py-2">
              <i class="fas fa-calendar-check mr-1 text-success"></i> Puncak: <strong>Tgl <?= (int)($chartDailyReturns['max_day'] ?? 1) ?> (<?= (int)($chartDailyReturns['max_count'] ?? 0) ?> buku)</strong>
            </span>
          </div>
        </div>
        <div class="card-body pt-2">
          <div style="position: relative; height: 260px; width: 100%;">
            <canvas id="chartDailyReturnsCanvas"></canvas>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<!-- ============================================================== -->
<!-- MODAL SCANNER BARCODE / ISBN KAMERA (DROIDCAM & WEBCAM)        -->
<!-- ============================================================== -->
<div class="modal fade" id="modalScannerKembali" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title font-weight-bold">
          <i class="fas fa-barcode mr-2"></i> Pindai Barcode Pengembalian
        </h5>
        <button type="button" class="close text-white" id="btnCloseScannerKembali">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-3 text-center">
        <!-- Camera Selector (DroidCam vs Laptop WebCam) -->
        <div class="form-group mb-2 text-left">
          <label class="text-xs text-muted mb-1 font-weight-bold">
            <i class="fas fa-video mr-1"></i> Pilih Sumber Kamera (Webcam / DroidCam):
          </label>
          <select id="cameraSelectKembali" class="form-control form-control-sm"></select>
        </div>

        <!-- Scanner Viewport -->
        <div id="readerKembali" style="width: 100%; min-height: 250px; background: #000; border-radius: 6px; overflow: hidden;"></div>

        <div id="scannerStatusKembali" class="mt-2 text-sm text-muted">
          Arahkan barcode di belakang buku ke area kotak kamera...
        </div>

        <!-- Manual Barcode Input Fallback -->
        <div class="mt-3 pt-2 border-top">
          <label class="text-xs text-muted mb-1 d-block text-left font-weight-bold">Atau Ketik Kode Barcode / ISBN Manual:</label>
          <div class="input-group input-group-sm">
            <input type="text" id="manualCodeKembali" class="form-control" placeholder="Contoh: 9786020332956...">
            <div class="input-group-append">
              <button type="button" class="btn btn-outline-success" id="btnManualSubmitKembali">
                <i class="fas fa-search mr-1"></i> Cek Buku
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- MODAL INPUT MANUAL PENGEMBALIAN BUKU                          -->
<!-- ============================================================== -->
<div class="modal fade" id="modalKembaliManual" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-gradient-success text-white py-3">
        <h5 class="modal-title font-weight-bold mb-0">
          <i class="fas fa-keyboard mr-2"></i> Input Manual Pengembalian Buku
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-3">
        <!-- 1. Pencarian Kode Barcode / ISBN Cepat -->
        <div class="card card-outline card-success shadow-xs mb-3 bg-light border">
          <div class="card-body p-3">
            <label class="text-xs text-muted mb-1 font-weight-bold text-uppercase">
              <i class="fas fa-barcode text-success mr-1"></i> Cari Cepat via Nomor ISBN / Barcode / Kode Buku:
            </label>
            <div class="input-group">
              <input type="text" id="manualInputIsbnField" class="form-control font-weight-bold text-success" placeholder="Ketik atau tempel nomor ISBN / Barcode (Contoh: 978602...)" autocomplete="off">
              <div class="input-group-append">
                <button type="button" class="btn btn-success font-weight-bold px-3" id="btnCariIsbnManualKembali">
                  <i class="fas fa-search mr-1"></i> Cek Barcode
                </button>
              </div>
            </div>
            <small class="text-muted d-block mt-1">
              <i class="fas fa-info-circle text-info mr-1"></i> Tekan Enter atau klik Cek Barcode untuk mendeteksi transaksi secara instan.
            </small>
          </div>
        </div>

        <!-- 2. Pilihan Daftar Peminjaman Aktif -->
        <div class="form-group mb-2">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="font-weight-bold mb-0 text-dark">
              <i class="fas fa-list-check text-primary mr-1"></i> Pilih dari Daftar Buku yang Sedang Dipinjam:
            </label>
            <span class="badge badge-light border text-muted">
              <?= count($activeLoans) ?> Transaksi Aktif
            </span>
          </div>
          <div class="input-group input-group-sm mb-2">
            <div class="input-group-prepend">
              <span class="input-group-text bg-white"><i class="fas fa-filter text-muted"></i></span>
            </div>
            <input type="text" id="filterLoanSearch" class="form-control" placeholder="Ketik nama siswa, NIS, atau judul buku untuk menyaring...">
          </div>

          <div class="border rounded p-2 bg-white" style="max-height: 250px; overflow-y: auto;" id="listLoansContainer">
            <?php if (empty($activeLoans)): ?>
              <div class="text-center py-4 text-muted">
                <i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i>
                Tidak ada buku yang sedang dipinjam saat ini.
              </div>
            <?php else: ?>
              <?php foreach ($activeLoans as $al): 
                $tglPStr = $al['tanggal_peminjaman'];
                $tglPObj = DateTime::createFromFormat('d-m-Y', $tglPStr) ?: new DateTime();
                $tglJt = clone $tglPObj;
                $tglJt->modify('+7 days');
                $tglJt->setTime(0, 0, 0);

                $tHari = 0;
                $dTelat = 0;
                if ($today > $tglJt) {
                  $diff = $tglJt->diff($today);
                  $tHari = (int)$diff->days;
                  $dTelat = $tHari * 5000;
                }
                $namaSiswa = $al['fullname'] ?: $al['nama_anggota'];
              ?>
                <div class="loan-manual-card p-2 border rounded mb-2 d-flex justify-content-between align-items-center"
                     style="cursor: pointer;"
                     data-id="<?= $al['id_peminjaman'] ?>"
                     data-nama="<?= htmlspecialchars($namaSiswa) ?>"
                     data-nis="<?= htmlspecialchars($al['nis'] ?: '-') ?>"
                     data-kelas="<?= htmlspecialchars($al['kelas'] ?: '-') ?>"
                     data-judul="<?= htmlspecialchars($al['judul_buku']) ?>"
                     data-isbn="<?= htmlspecialchars($al['isbn'] ?: '-') ?>"
                     data-pinjam="<?= htmlspecialchars($al['tanggal_peminjaman']) ?>"
                     data-tempo="<?= $tglJt->format('d-m-Y') ?>"
                     data-telat="<?= $tHari ?>"
                     data-dendatelat="<?= $dTelat ?>">
                  <div class="loan-card-info" style="flex: 1;">
                    <div class="d-flex align-items-center flex-wrap">
                      <strong class="text-primary mr-2"><?= htmlspecialchars($namaSiswa) ?></strong>
                      <span class="badge badge-secondary text-xs mr-2"><?= htmlspecialchars($al['kelas'] ?: '-') ?></span>
                      <?php if ($tHari > 0): ?>
                        <span class="badge badge-danger text-xs font-weight-bold">
                          <i class="fas fa-exclamation-triangle mr-1"></i> Telat <?= $tHari ?> Hari (Rp <?= number_format($dTelat, 0, ',', '.') ?>)
                        </span>
                      <?php else: ?>
                        <span class="badge badge-success text-xs font-weight-bold">
                          <i class="fas fa-clock mr-1"></i> Tepat Waktu
                        </span>
                      <?php endif; ?>
                    </div>
                    <div class="text-dark font-weight-bold text-sm mt-1">
                      <i class="fas fa-book text-muted mr-1"></i> <?= htmlspecialchars($al['judul_buku']) ?>
                    </div>
                    <div class="text-xs text-muted">
                      ISBN: <?= htmlspecialchars($al['isbn'] ?: '-') ?> | Dipinjam: <?= htmlspecialchars($al['tanggal_peminjaman']) ?> | Batas: <?= $tglJt->format('d-m-Y') ?>
                    </div>
                  </div>
                  <div class="loan-card-action ml-2">
                    <span class="btn btn-outline-success btn-xs font-weight-bold px-2 py-1 select-loan-btn">
                      <i class="fas fa-check mr-1"></i> Pilih
                    </span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- 3. Preview Transaksi Terpilih -->
        <div id="previewManualKembaliBox" class="alert alert-success p-2 mb-0 border-success" style="display: none;">
          <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div>
              <small class="text-muted text-uppercase font-weight-bold d-block">Transaksi Terpilih:</small>
              <strong id="previewManualNama" class="text-dark"></strong>
              <span id="previewManualJudul" class="text-muted d-block text-xs"></span>
            </div>
            <span id="previewManualStatus" class="badge"></span>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light py-2 d-flex justify-content-between align-items-center">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success px-4 font-weight-bold shadow-sm" id="btnLanjutManualKembali" disabled>
          <i class="fas fa-arrow-right mr-1"></i> Lanjut Proses Pengembalian
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- MODAL PROSES PENGEMBALIAN BUKU (VALIDASI DENDA & KONDISI FISIK) -->
<!-- ============================================================== -->
<div class="modal fade" id="modalFormKembali" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <form action="<?= $base ?>admin/pengembalian/proses" method="post" id="formProsesKembali">
        <input type="hidden" name="id_peminjaman" id="formKembaliId">
        <input type="hidden" name="denda_keterlambatan" id="formDendaKeterlambatan" value="0">
        <input type="hidden" name="denda_kerusakan" id="formDendaKerusakan" value="0">

        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title font-weight-bold">
            <i class="fas fa-clipboard-check mr-2"></i> Konfirmasi Pengembalian Buku
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <!-- Info Siswa & Buku -->
            <div class="col-md-6 border-right">
              <h6 class="font-weight-bold text-secondary mb-2">
                <i class="fas fa-user-graduate text-primary mr-1"></i> Data Siswa Peminjam
              </h6>
              <table class="table table-sm table-bordered">
                <tr><th width="40%" class="bg-light">Nama Siswa</th><td id="infoKembaliNama" class="font-weight-bold text-primary">-</td></tr>
                <tr><th class="bg-light">NIS / Kelas</th><td id="infoKembaliNisKelas">-</td></tr>
              </table>

              <h6 class="font-weight-bold text-secondary mb-2 mt-3">
                <i class="fas fa-book text-primary mr-1"></i> Data Buku Fisik
              </h6>
              <table class="table table-sm table-bordered">
                <tr><th width="40%" class="bg-light">Judul Buku</th><td id="infoKembaliJudul" class="font-weight-bold text-dark">-</td></tr>
                <tr><th class="bg-light">Nomor ISBN</th><td id="infoKembaliIsbn">-</td></tr>
              </table>
            </div>

            <!-- Jadwal & Denda -->
            <div class="col-md-6">
              <h6 class="font-weight-bold text-secondary mb-2">
                <i class="fas fa-calendar-alt text-primary mr-1"></i> Jadwal & Keterlambatan
              </h6>
              <table class="table table-sm table-bordered">
                <tr><th width="45%" class="bg-light">Tgl Pinjam</th><td id="infoKembaliTglPinjam">-</td></tr>
                <tr><th class="bg-light">Batas Kembali</th><td id="infoKembaliTglTempo" class="font-weight-bold">-</td></tr>
                <tr><th class="bg-light">Hari Terlambat</th><td id="infoKembaliTelatHari">-</td></tr>
                <tr><th class="bg-light">Denda Terlambat</th><td id="infoKembaliDendaTelat" class="font-weight-bold text-danger">Rp 0</td></tr>
              </table>

              <!-- Kondisi Buku: Baik vs Rusak vs Hilang (Segmented Enum Cards) -->
              <h6 class="font-weight-bold text-secondary mb-2 mt-3">
                <i class="fas fa-shield-alt text-primary mr-1"></i> Status Kondisi Buku Dikembalikan:
              </h6>
              <div class="d-flex mb-2" style="gap: 8px;">
                <div class="enum-card flex-fill p-2 text-center active-baik" id="cardKondisiBaikKembali" onclick="selectEnumKembali('Baik')">
                  <i class="fas fa-check-circle fa-lg text-success mb-1"></i>
                  <strong class="d-block text-dark text-xs font-weight-bold">Kondisi Baik</strong>
                  <small class="text-success d-block" style="font-size: 0.68rem;">Bebas Denda</small>
                </div>
                <div class="enum-card flex-fill p-2 text-center" id="cardKondisiRusakKembali" onclick="selectEnumKembali('Rusak')">
                  <i class="fas fa-exclamation-triangle fa-lg text-warning mb-1"></i>
                  <strong class="d-block text-dark text-xs font-weight-bold">Buku Rusak</strong>
                  <small class="text-warning d-block" style="font-size: 0.68rem;">+ Denda Cacat</small>
                </div>
                <div class="enum-card flex-fill p-2 text-center" id="cardKondisiHilangKembali" onclick="selectEnumKembali('Hilang')">
                  <i class="fas fa-times-circle fa-lg text-danger mb-1"></i>
                  <strong class="d-block text-dark text-xs font-weight-bold">Buku Hilang</strong>
                  <small class="text-danger d-block" style="font-size: 0.68rem;">+ Ganti Buku</small>
                </div>
              </div>
              <input type="radio" id="kondisiBaik" name="kondisi_buku_saat_dikembalikan" value="Baik" class="d-none" checked>
              <input type="radio" id="kondisiRusak" name="kondisi_buku_saat_dikembalikan" value="Rusak" class="d-none">
              <input type="radio" id="kondisiHilang" name="kondisi_buku_saat_dikembalikan" value="Hilang" class="d-none">

              <!-- Input Denda Kerusakan / Ganti Buku Hilang -->
              <div id="boxDendaKerusakan" class="p-2 bg-light border rounded mb-2" style="display: none;">
                <label class="text-xs font-weight-bold text-danger mb-1" id="labelDendaKhusus">Nominal Biaya Denda Kerusakan Buku (Rp):</label>
                <input type="number" id="inputDendaKerusakan" class="form-control form-control-sm" placeholder="Contoh: 15000" min="0" step="1000" value="0">
                <small class="text-muted d-block" id="keteranganDendaKhusus">Masukkan biaya penggantian kerusakan halaman/jilid buku.</small>
              </div>

              <!-- Total Denda Akumulasi -->
              <div class="alert alert-warning py-2 mb-0 d-flex justify-content-between align-items-center">
                <span class="font-weight-bold text-sm">TOTAL DENDA:</span>
                <span class="font-weight-bold text-lg text-danger" id="displayTotalDenda">Rp 0</span>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success font-weight-bold px-4">
            <i class="fas fa-check mr-1"></i> Simpan & Terima Pengembalian
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- MODAL KONFIRMASI BUKU BELUM PERNAH DIPINJAM (PINJAM TANPA IZIN) -->
<!-- ============================================================== -->
<div class="modal fade" id="modalTanpaIzin" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <form action="<?= $base ?>admin/pengembalian/proses-tanpa-izin" method="post" id="formTanpaIzin">
        <input type="hidden" name="id_buku" id="tanpaIzinIdBuku">

        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title font-weight-bold">
            <i class="fas fa-exclamation-triangle mr-2"></i> Buku Belum Tercatat Dipinjam!
          </h5>
          <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="alert alert-danger font-weight-bold text-sm">
            <i class="fas fa-info-circle mr-1"></i>
            Buku <span id="tanpaIzinJudulBuku" class="text-dark font-weight-bold"></span> tidak memiliki catatan transaksi peminjaman aktif dari siswa manapun!
          </div>

          <p class="font-weight-bold text-dark text-center" style="font-size: 15px;">
            "Apakah siswa tersebut meminjam buku fisik ini tanpa sepengetahuan / tanpa izin pustakawan?"
          </p>

          <div id="boxPilihanTanpaIzin" class="text-center my-3">
            <button type="button" class="btn btn-danger font-weight-bold px-4 mr-2" id="btnYaTanpaIzin">
              <i class="fas fa-check mr-1"></i> Ya (Catat Sanksi Denda)
            </button>
            <button type="button" class="btn btn-secondary px-4" id="btnTidakTanpaIzin">
              <i class="fas fa-times mr-1"></i> Tidak (Batalkan)
            </button>
          </div>

          <!-- Form Input Denda Sanksi jika YA -->
          <div id="formInputDendaSanksi" style="display: none;" class="border-top pt-3 mt-3">
            <div class="form-group">
              <label class="font-weight-bold text-xs">Pilih Siswa yang Mengembalikan (Opsional):</label>
              <select name="id_user" class="form-control form-control-sm">
                <option value="">-- Pilih Siswa (Jika Ada) --</option>
                <?php foreach ($siswaList as $s): ?>
                  <option value="<?= $s['id_user'] ?>"><?= htmlspecialchars($s['fullname']) ?> (NIS: <?= htmlspecialchars($s['nis']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label class="font-weight-bold text-xs text-danger">Nominal Biaya Denda Sanksi (Rp):</label>
              <input type="number" name="nominal_denda" class="form-control form-control-sm" placeholder="Contoh: 20000" min="0" step="1000" required value="10000">
            </div>

            <div class="form-group mb-0">
              <label class="font-weight-bold text-xs">Keterangan / Catatan Sanksi:</label>
              <input type="text" name="keterangan" class="form-control form-control-sm" value="Membawa buku tanpa izin sirkulasi">
            </div>

            <div class="mt-3 text-right">
              <button type="submit" class="btn btn-danger btn-sm px-4 font-weight-bold">
                <i class="fas fa-save mr-1"></i> Simpan Sanksi & Tambah Stok Buku
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var html5QrCode = null;
  var currentTelatDenda = 0;
  var currentRusakDenda = 0;

  // Sound chime feedback
  function playBeep() {
    try {
      var ctx = new (window.AudioContext || window.webkitAudioContext)();
      var osc = ctx.createOscillator();
      var gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(880, ctx.currentTime);
      gain.gain.setValueAtTime(0.15, ctx.currentTime);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start();
      osc.stop(ctx.currentTime + 0.15);
    } catch(e) {}
  }

  // Inisialisasi PerpusScanner Omni-Directional untuk Sirkulasi Pengembalian
  var kembaliScanner = new PerpusScanner({
    containerId: 'readerKembali',
    selectCameraId: 'cameraSelectKembali',
    statusId: 'scannerStatusKembali',
    baseApiUrl: '<?= $base ?>',
    onScanSuccess: function(decodedText, format) {
      if (typeof playScannerBeep === 'function') playScannerBeep();
      $('#modalScannerKembali').modal('hide');
      prosesCekKodeKembali(decodedText);
    }
  });

  // Buka Modal Scanner
  $('#btnBukaScannerKembali').on('click', function() {
    $('#modalScannerKembali').modal('show');
    kembaliScanner.start();
  });

  $('#btnCloseScannerKembali').on('click', function() {
    kembaliScanner.stop();
    $('#modalScannerKembali').modal('hide');
  });

  $('#modalScannerKembali').on('hidden.bs.modal', function () {
    kembaliScanner.stop();
  });

  // ==========================================
  // Logika Modal Input Manual Pengembalian
  // ==========================================
  var selectedManualLoanData = null;

  // 1. Klik kartu peminjaman di modal manual
  $(document).on('click', '.loan-manual-card', function() {
    $('.loan-manual-card').removeClass('selected');
    $(this).addClass('selected');

    selectedManualLoanData = {
      id_peminjaman: $(this).data('id'),
      nama: $(this).data('nama'),
      nis: $(this).data('nis'),
      kelas: $(this).data('kelas'),
      judul: $(this).data('judul'),
      isbn: $(this).data('isbn'),
      tgl_pinjam: $(this).data('pinjam'),
      tgl_tempo: $(this).data('tempo'),
      telat_hari: parseInt($(this).data('telat')) || 0,
      denda_telat: parseFloat($(this).data('dendatelat')) || 0
    };

    // Update preview box
    $('#previewManualNama').text(selectedManualLoanData.nama + ' (' + selectedManualLoanData.nis + ' / ' + selectedManualLoanData.kelas + ')');
    $('#previewManualJudul').text('Buku: ' + selectedManualLoanData.judul + ' (ISBN: ' + selectedManualLoanData.isbn + ')');
    if (selectedManualLoanData.telat_hari > 0) {
      $('#previewManualStatus')
        .removeClass('badge-success')
        .addClass('badge-danger')
        .html('<i class="fas fa-exclamation-triangle mr-1"></i> Telat ' + selectedManualLoanData.telat_hari + ' Hari (Rp ' + selectedManualLoanData.denda_telat.toLocaleString('id-ID') + ')');
    } else {
      $('#previewManualStatus')
        .removeClass('badge-danger')
        .addClass('badge-success')
        .html('<i class="fas fa-check-circle mr-1"></i> Tepat Waktu');
    }
    $('#previewManualKembaliBox').slideDown(150);
    $('#btnLanjutManualKembali').prop('disabled', false);
  });

  // Double click kartu untuk langsung lanjut
  $(document).on('dblclick', '.loan-manual-card', function() {
    $(this).trigger('click');
    $('#btnLanjutManualKembali').trigger('click');
  });

  // 2. Tombol Lanjut Proses Pengembalian dari modal manual
  $('#btnLanjutManualKembali').on('click', function() {
    if (!selectedManualLoanData) {
      alert('Silakan pilih salah satu transaksi peminjaman terlebih dahulu.');
      return;
    }
    $('#modalKembaliManual').modal('hide');
    setTimeout(function() {
      bukaModalFormKembali(selectedManualLoanData);
    }, 350);
  });

  // 3. Filter Live Search daftar transaksi peminjaman aktif
  $('#filterLoanSearch').on('input', function() {
    var query = $(this).val().toLowerCase().trim();
    $('.loan-manual-card').each(function() {
      var n = ($(this).data('nama') || '').toString().toLowerCase();
      var nis = ($(this).data('nis') || '').toString().toLowerCase();
      var k = ($(this).data('kelas') || '').toString().toLowerCase();
      var j = ($(this).data('judul') || '').toString().toLowerCase();
      var isbn = ($(this).data('isbn') || '').toString().toLowerCase();

      if (!query || n.indexOf(query) !== -1 || nis.indexOf(query) !== -1 || k.indexOf(query) !== -1 || j.indexOf(query) !== -1 || isbn.indexOf(query) !== -1) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  });

  // 4. Cari cepat via ISBN / Barcode input field
  $('#btnCariIsbnManualKembali').on('click', function() {
    var code = $('#manualInputIsbnField').val().trim();
    if (!code) {
      alert('Silakan masukkan nomor ISBN atau kode barcode.');
      $('#manualInputIsbnField').focus();
      return;
    }
    $('#modalKembaliManual').modal('hide');
    setTimeout(function() {
      prosesCekKodeKembali(code);
    }, 350);
  });

  $('#manualInputIsbnField').on('keypress', function(e) {
    if (e.which === 13) {
      e.preventDefault();
      $('#btnCariIsbnManualKembali').trigger('click');
    }
  });

  // Reset modal manual saat dibuka
  $('#modalKembaliManual').on('show.bs.modal', function() {
    $('#manualInputIsbnField').val('');
    $('#filterLoanSearch').val('');
    $('.loan-manual-card').show().removeClass('selected');
    $('#previewManualKembaliBox').hide();
    $('#btnLanjutManualKembali').prop('disabled', true);
    selectedManualLoanData = null;
    setTimeout(function() {
      $('#manualInputIsbnField').focus();
    }, 400);
  });

  // Manual code input trigger
  $('#btnManualSubmitKembali').on('click', function() {
    var code = $('#manualCodeKembali').val().trim();
    if (!code) return;
    kembaliScanner.stop();
    $('#modalScannerKembali').modal('hide');
    prosesCekKodeKembali(code);
  });

  // Proses scan ke backend API
  function prosesCekKodeKembali(code) {
    $.getJSON('<?= $base ?>admin/pengembalian/api-scan-kembali?code=' + encodeURIComponent(code), function(res) {
      if (res.status === 'success') {
        bukaModalFormKembali({
          id_peminjaman: res.loan.id_peminjaman,
          nama: res.loan.fullname || res.loan.nama_anggota,
          nis: res.loan.nis || '-',
          kelas: res.loan.kelas || '-',
          judul: res.buku.judul_buku,
          isbn: res.buku.isbn || '-',
          tgl_pinjam: res.loan.tanggal_peminjaman,
          tgl_tempo: res.tgl_jatuh_tempo,
          telat_hari: res.terlambat_hari,
          denda_telat: res.denda_telat
        });
      } else if (res.status === 'no_active_loan') {
        // Pop-up buku belum pernah dipinjam!
        $('#tanpaIzinIdBuku').val(res.buku.id_buku);
        $('#tanpaIzinJudulBuku').text('"' + res.buku.judul_buku + '"');
        $('#formInputDendaSanksi').hide();
        $('#boxPilihanTanpaIzin').show();
        $('#modalTanpaIzin').modal('show');
      }
    }).fail(function(xhr) {
      var err = xhr.responseJSON ? xhr.responseJSON.message : 'Scan gagal: kode tidak terbaca!';
      alert(err);
    });
  }

  // Handle tombol Ya / Tidak pada popup buku belum pernah dipinjam
  $('#btnYaTanpaIzin').on('click', function() {
    $('#formInputDendaSanksi').slideDown();
    $('#boxPilihanTanpaIzin').hide();
  });

  $('#btnTidakTanpaIzin').on('click', function() {
    var judul = $('#tanpaIzinJudulBuku').text();
    $('#modalTanpaIzin').modal('hide');
    alert('Pengembalian gagal, tidak ada data buku ' + judul + ' dipinjam.');
  });

  // Klik tombol Terima Buku di tabel manual
  $('.btn-proses-kembali').on('click', function() {
    bukaModalFormKembali({
      id_peminjaman: $(this).data('id'),
      nama: $(this).data('nama'),
      nis: $(this).data('nis'),
      kelas: $(this).data('kelas'),
      judul: $(this).data('judul'),
      isbn: $(this).data('isbn'),
      tgl_pinjam: $(this).data('pinjam'),
      tgl_tempo: $(this).data('tempo'),
      telat_hari: parseInt($(this).data('telat')) || 0,
      denda_telat: parseFloat($(this).data('dendatelat')) || 0
    });
  });

  // Buka Modal Form Pengembalian
  function bukaModalFormKembali(data) {
    $('#formKembaliId').val(data.id_peminjaman);
    $('#infoKembaliNama').text(data.nama);
    $('#infoKembaliNisKelas').text((data.nis || '-') + ' / ' + (data.kelas || '-'));
    $('#infoKembaliJudul').text(data.judul);
    $('#infoKembaliIsbn').text(data.isbn);
    $('#infoKembaliTglPinjam').text(data.tgl_pinjam);
    $('#infoKembaliTglTempo').text(data.tgl_tempo);

    currentTelatDenda = data.denda_telat;
    $('#formDendaKeterlambatan').val(currentTelatDenda);

    if (data.telat_hari > 0) {
      $('#infoKembaliTelatHari').html('<span class="text-danger font-weight-bold">' + data.telat_hari + ' Hari</span>');
      $('#infoKembaliDendaTelat').text('Rp ' + currentTelatDenda.toLocaleString('id-ID'));
    } else {
      $('#infoKembaliTelatHari').html('<span class="text-success font-weight-bold">0 Hari (Tepat Waktu)</span>');
      $('#infoKembaliDendaTelat').text('Rp 0');
    }

    // Reset kondisi buku ke Baik via Enum Card
    selectEnumKembali('Baik');
    $('#boxDendaKerusakan').hide();
    $('#inputDendaKerusakan').val(0);
    currentRusakDenda = 0;
    $('#formDendaKerusakan').val(0);

    updateTotalDenda();
    $('#modalFormKembali').modal('show');
  }

  // Toggle Enum Kondisi Fisik Pengembalian
  window.selectEnumKembali = function(val) {
    $('#cardKondisiBaikKembali').removeClass('active-baik');
    $('#cardKondisiRusakKembali').removeClass('active-rusak');
    $('#cardKondisiHilangKembali').removeClass('active-hilang');

    if (val === 'Baik') {
      $('#kondisiBaik').prop('checked', true).trigger('change');
      $('#cardKondisiBaikKembali').addClass('active-baik');
    } else if (val === 'Rusak') {
      $('#kondisiRusak').prop('checked', true).trigger('change');
      $('#cardKondisiRusakKembali').addClass('active-rusak');
    } else if (val === 'Hilang') {
      $('#kondisiHilang').prop('checked', true).trigger('change');
      $('#cardKondisiHilangKembali').addClass('active-hilang');
    }
  };

  // Toggle Radio Kondisi Buku (Baik vs Rusak vs Hilang)
  $('input[name="kondisi_buku_saat_dikembalikan"]').on('change', function() {
    var val = $(this).val();
    if (val === 'Rusak') {
      $('#labelDendaKhusus').text('Nominal Biaya Denda Kerusakan Buku (Rp):');
      $('#keteranganDendaKhusus').text('Masukkan biaya penggantian kerusakan fisik/jilid buku.');
      $('#boxDendaKerusakan').slideDown();
      currentRusakDenda = parseFloat($('#inputDendaKerusakan').val()) || 10000;
      $('#inputDendaKerusakan').val(currentRusakDenda);
    } else if (val === 'Hilang') {
      $('#labelDendaKhusus').text('Nominal Biaya Penggantian Buku Hilang (Rp):');
      $('#keteranganDendaKhusus').text('Siswa wajib mengganti biaya buku yang hilang (default: Rp 50.000).');
      $('#boxDendaKerusakan').slideDown();
      currentRusakDenda = parseFloat($('#inputDendaKerusakan').val()) || 50000;
      $('#inputDendaKerusakan').val(currentRusakDenda);
    } else {
      $('#boxDendaKerusakan').slideUp();
      currentRusakDenda = 0;
      $('#inputDendaKerusakan').val(0);
    }
    $('#formDendaKerusakan').val(currentRusakDenda);
    updateTotalDenda();
  });

  $('#inputDendaKerusakan').on('input', function() {
    currentRusakDenda = parseFloat($(this).val()) || 0;
    $('#formDendaKerusakan').val(currentRusakDenda);
    updateTotalDenda();
  });

  function updateTotalDenda() {
    var total = currentTelatDenda + currentRusakDenda;
    $('#displayTotalDenda').text('Rp ' + total.toLocaleString('id-ID'));
  }

  // ==========================================
  // Inisialisasi Grafik Pengembalian dalam Sebulan
  // ==========================================
  const dailyReturnsData = <?= json_encode($chartDailyReturns ?? []) ?>;
  const ctxDailyReturn = document.getElementById('chartDailyReturnsCanvas');
  if (ctxDailyReturn && dailyReturnsData.days) {
    new Chart(ctxDailyReturn.getContext('2d'), {
      type: 'bar',
      data: {
        labels: dailyReturnsData.days,
        datasets: [
          {
            label: 'Buku Kondisi Baik',
            data: dailyReturnsData.counts_baik || [],
            backgroundColor: 'rgba(16, 185, 129, 0.85)',
            borderColor: '#059669',
            borderWidth: 1.5,
            borderRadius: 4
          },
          {
            label: 'Buku Rusak / Hilang',
            data: dailyReturnsData.counts_rusak || [],
            backgroundColor: 'rgba(239, 68, 68, 0.85)',
            borderColor: '#dc2626',
            borderWidth: 1.5,
            borderRadius: 4
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'top',
            labels: { boxWidth: 12, font: { size: 11 } }
          },
          tooltip: {
            callbacks: {
              label: (ctx) => ` ${ctx.dataset.label}: ${ctx.raw} buku`
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            stacked: true,
            ticks: { stepSize: 1, precision: 0 },
            grid: { color: 'rgba(0, 0, 0, 0.05)' }
          },
          x: {
            stacked: true,
            grid: { display: false },
            ticks: { font: { size: 10 } }
          }
        }
      }
    });
  }

  // ==========================================
  // Live Search Buku yang Sedang Dipinjam
  // ==========================================
  $('#tableSearch').on('input keyup paste change', function () {
    var query = $(this).val().toLowerCase().trim();
    var matchCount = 0;

    $('#dataTable tbody tr').each(function () {
      if ($(this).attr('id') === 'rowNoSearchResultKembali') return;
      if ($(this).find('td[colspan]').length > 0 && $(this).attr('id') !== 'rowNoSearchResultKembali') return;

      var rowText = $(this).text().toLowerCase();
      if (query === '' || rowText.indexOf(query) !== -1) {
        $(this).show();
        matchCount++;
      } else {
        $(this).hide();
      }
    });

    var emptyRow = $('#rowNoSearchResultKembali');
    if (matchCount === 0 && query !== '') {
      if (emptyRow.length === 0) {
        $('#dataTable tbody').append(
          '<tr id="rowNoSearchResultKembali"><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-search mr-2"></i>Tidak ditemukan transaksi peminjaman aktif dengan kata kunci "<strong>' + $('<div>').text(query).html() + '</strong>".</td></tr>'
        );
      } else {
        emptyRow.find('strong').text(query);
        emptyRow.show();
      }
    } else {
      emptyRow.hide();
    }
  });
});
</script>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>

