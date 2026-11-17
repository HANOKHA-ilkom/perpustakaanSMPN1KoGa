<?php
// Data Peminjaman View (Admin - Sirkulasi & Scanner Edition)
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$pageTitle = 'Sirkulasi Peminjaman - Perpustakaan SMPN 1 Kota Gajah';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/sidebar_admin.php';
$base = (new Controller())->baseUrl();
$currentDate = date('d-m-Y');
?>

<!-- Barcode Scanning Libraries (ZXing & Perpus Omni-Directional Engine) -->
<script src="https://unpkg.com/@zxing/library@0.21.3/umd/index.min.js"></script>
<script src="<?= $base ?>assets/js/perpus-scanner.js"></script>

<style>
/* Styling Elemen Selection Buku, Siswa & Enum */
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
  border-color: #dc3545 !important;
  background-color: #fef2f2 !important;
  box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.2);
}
.book-select-item {
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  cursor: pointer;
  transition: all 0.18s ease;
  position: relative;
}
.book-select-item:hover {
  border-color: #3b82f6;
  background: #f8fafc;
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.08);
}
.book-select-item.selected {
  border-color: #007bff !important;
  background: #ebf5ff !important;
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25) !important;
}
.student-avatar-badge {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  color: #fff;
  background: linear-gradient(135deg, #4f46e5, #06b6d4);
  font-size: 15px;
  flex-shrink: 0;
}
</style>

<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-book-reader text-primary mr-2"></i> Meja Sirkulasi Peminjaman Buku
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= $base ?>admin/dashboard">Home</a></li>
            <li class="breadcrumb-item active">Sirkulasi Peminjaman</li>
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

      <!-- Quick Action Card -->
      <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-7 mb-3 mb-md-0">
              <h5 class="font-weight-bold text-dark mb-1">Catat Peminjaman Buku Fisik Baru</h5>
              <p class="text-muted text-sm mb-0">
                Pindai barcode / ISBN di sampul belakang buku dengan kamera laptop atau DroidCam HP. Sistem otomatis memvalidasi kuota maksimal 2 buku per siswa, mengurangi stok fisik buku, dan menerbitkan slip peminjaman PDF resmi.
              </p>
            </div>
            <div class="col-md-5 text-md-right">
              <button type="button" class="btn btn-primary font-weight-bold px-3 py-2 shadow-sm mr-2" id="btnBukaScannerPinjam">
                <i class="fas fa-barcode mr-1"></i> Scan Barcode Peminjaman
              </button>
              <button type="button" class="btn btn-outline-secondary font-weight-bold px-3 py-2" data-toggle="modal" data-target="#modalPinjamManual">
                <i class="fas fa-keyboard mr-1"></i> Input Manual
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Table Peminjaman -->
      <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
          <h5 class="card-title font-weight-bold mb-0">
            <i class="fas fa-history mr-1 text-primary"></i> Riwayat Sirkulasi Peminjaman
          </h5>
          <div class="ml-auto d-flex align-items-center">
            <div class="input-group input-group-sm mr-2" style="width: 240px;">
              <input type="text" id="tableSearch" class="form-control" placeholder="Cari nama anggota / judul...">
              <div class="input-group-append">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
              </div>
            </div>
            <button type="button" onclick="location.reload()" class="btn btn-outline-secondary btn-sm" title="Segarkan Data">
              <i class="fas fa-redo-alt"></i>
            </button>
          </div>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover table-striped mb-0" id="dataTable">
            <thead class="bg-light">
              <tr>
                <th style="width: 40px;">No</th>
                <th>Nama Anggota</th>
                <th>Judul Buku</th>
                <th>Tgl Pinjam</th>
                <th>Tgl Kembali</th>
                <th class="text-center">Status Transaksi</th>
                <th>Kondisi Pinjam</th>
                <th>Kondisi Kembali</th>
                <th>Denda</th>
                <th style="width: 140px;" class="text-center">Aksi / Cetak</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($peminjamanList)): ?>
                <tr>
                  <td colspan="10" class="text-center text-muted py-4">Belum ada data sirkulasi peminjaman.</td>
                </tr>
              <?php else: ?>
                <?php $no = 1; foreach ($peminjamanList as $row): 
                  $status = $row['status_transaksi'] ?? '';
                  if (empty($status)) {
                    $status = !empty($row['tanggal_pengembalian']) ? 'Selesai' : 'Sedang Dipinjam';
                  }
                ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td><strong><?= htmlspecialchars($row['nama_anggota']) ?></strong></td>
                    <td><?= htmlspecialchars($row['judul_buku']) ?></td>
                    <td><?= htmlspecialchars($row['tanggal_peminjaman']) ?></td>
                    <td>
                      <?php if (empty($row['tanggal_pengembalian'])): ?>
                        <span class="text-muted">-</span>
                      <?php else: ?>
                        <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i><?= htmlspecialchars($row['tanggal_pengembalian']) ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <?php if ($status === 'Sedang Dipinjam'): ?>
                        <span class="badge badge-warning text-dark py-1 px-2 font-weight-bold shadow-xs">
                          <i class="fas fa-clock mr-1"></i> Sedang Dipinjam
                        </span>
                      <?php elseif ($status === 'Selesai'): ?>
                        <span class="badge badge-success py-1 px-2 font-weight-bold shadow-xs">
                          <i class="fas fa-check-circle mr-1"></i> Selesai
                        </span>
                      <?php else: ?>
                        <span class="badge badge-secondary py-1 px-2 font-weight-bold shadow-xs">
                          <?= htmlspecialchars($status) ?>
                        </span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($row['kondisi_buku_saat_dipinjam'] === 'Baik'): ?>
                        <span class="badge badge-success px-2 py-1 shadow-xs"><i class="fas fa-check mr-1"></i> Baik</span>
                      <?php elseif ($row['kondisi_buku_saat_dipinjam'] === 'Rusak'): ?>
                        <span class="badge badge-warning text-dark px-2 py-1 shadow-xs"><i class="fas fa-tools mr-1"></i> Rusak</span>
                      <?php else: ?>
                        <span class="badge badge-secondary px-2 py-1 shadow-xs"><?= htmlspecialchars($row['kondisi_buku_saat_dipinjam']) ?></span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (empty($row['kondisi_buku_saat_dikembalikan'])): ?>
                        <span class="text-muted text-xs"><i class="fas fa-minus mr-1"></i> Belum Kembali</span>
                      <?php elseif ($row['kondisi_buku_saat_dikembalikan'] === 'Baik'): ?>
                        <span class="badge badge-success px-2 py-1 shadow-xs"><i class="fas fa-check-double mr-1"></i> Baik</span>
                      <?php elseif ($row['kondisi_buku_saat_dikembalikan'] === 'Rusak'): ?>
                        <span class="badge badge-danger px-2 py-1 shadow-xs"><i class="fas fa-exclamation-triangle mr-1"></i> Rusak</span>
                      <?php elseif ($row['kondisi_buku_saat_dikembalikan'] === 'Hilang'): ?>
                        <span class="badge badge-dark px-2 py-1 shadow-xs"><i class="fas fa-times-circle mr-1"></i> Hilang</span>
                      <?php else: ?>
                        <span class="badge badge-secondary px-2 py-1 shadow-xs"><?= htmlspecialchars($row['kondisi_buku_saat_dikembalikan']) ?></span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (empty($row['denda']) || $row['denda'] === 'Tidak ada' || $row['denda'] === '0'): ?>
                        <span class="text-muted text-xs">Rp 0 (Bebas Denda)</span>
                      <?php else: ?>
                        <span class="badge badge-danger px-2 py-1 shadow-xs font-weight-bold">
                          <i class="fas fa-coins mr-1"></i> <?= htmlspecialchars($row['denda']) ?>
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <!-- Cetak Slip PDF -->
                      <a href="<?= $base ?>admin/peminjaman/slip?id=<?= $row['id_peminjaman'] ?>" target="_blank" class="btn btn-info btn-xs shadow-sm mb-1" title="Cetak Slip Peminjaman (PDF)">
                        <i class="fas fa-print mr-1"></i> Slip
                      </a>
                      
                      <!-- Hapus Transaksi -->
                      <form action="<?= $base ?>admin/peminjaman/delete" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data transaksi peminjaman ini?');">
                        <input type="hidden" name="id_peminjaman" value="<?= $row['id_peminjaman'] ?>">
                        <button type="submit" class="btn btn-danger btn-xs shadow-sm mb-1" title="Hapus">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ==================================================== -->
      <!-- GRAFIK JUMLAH PEMINJAMAN DALAM SEBULAN (CHART.JS)   -->
      <!-- ==================================================== -->
      <div class="card card-outline card-primary shadow-sm mt-4">
        <div class="card-header border-0 d-flex justify-content-between align-items-center flex-wrap">
          <div>
            <h5 class="card-title font-weight-bold text-dark mb-0">
              <i class="fas fa-chart-line text-primary mr-2"></i> Grafik Tren Peminjaman Buku dalam Sebulan
            </h5>
            <small class="text-muted d-block mt-1">
              Visualisasi distribusi frekuensi sirkulasi peminjaman buku per hari pada periode <strong><?= htmlspecialchars($chartDailyLoans['month_name'] ?? date('F Y')) ?></strong>
            </small>
          </div>
          <div class="mt-2 mt-md-0">
            <span class="badge badge-primary px-3 py-2 mr-1">
              <i class="fas fa-book-reader mr-1"></i> Total Pinjam: <strong><?= (int)($chartDailyLoans['total'] ?? 0) ?> buku</strong>
            </span>
            <span class="badge badge-light border px-2 py-2">
              <i class="fas fa-calendar-day mr-1 text-primary"></i> Puncak: <strong>Tgl <?= (int)($chartDailyLoans['max_day'] ?? 1) ?> (<?= (int)($chartDailyLoans['max_count'] ?? 0) ?> buku)</strong>
            </span>
          </div>
        </div>
        <div class="card-body pt-2">
          <div style="position: relative; height: 260px; width: 100%;">
            <canvas id="chartDailyLoansCanvas"></canvas>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<!-- ============================================================== -->
<!-- MODAL SCANNER BARCODE / ISBN (KAMERA LAPTOP & DROIDCAM)        -->
<!-- ============================================================== -->
<div class="modal fade" id="modalScannerPinjam" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title font-weight-bold">
          <i class="fas fa-barcode mr-2"></i> Pindai Barcode / ISBN Buku Fisik
        </h5>
        <button type="button" class="close text-white" id="btnCloseScannerPinjam">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-3 text-center">
        <!-- Camera Selector (DroidCam vs Laptop WebCam) -->
        <div class="form-group mb-2 text-left">
          <label class="text-xs text-muted mb-1 font-weight-bold">
            <i class="fas fa-video mr-1"></i> Pilih Sumber Kamera (Webcam / DroidCam):
          </label>
          <select id="cameraSelectPinjam" class="form-control form-control-sm"></select>
        </div>

        <!-- Scanner Viewport -->
        <div id="readerPinjam" style="width: 100%; min-height: 250px; background: #000; border-radius: 6px; overflow: hidden;"></div>

        <div id="scannerStatusPinjam" class="mt-2 text-sm text-muted">
          Arahkan barcode di sampul belakang buku ke kotak pemindaian...
        </div>

        <!-- Manual Barcode Input Fallback -->
        <div class="mt-3 pt-2 border-top">
          <label class="text-xs text-muted mb-1 d-block text-left font-weight-bold">Atau Ketik Kode Barcode / ISBN Manual:</label>
          <div class="input-group input-group-sm">
            <input type="text" id="manualCodePinjam" class="form-control" placeholder="Contoh: 9786020332956...">
            <div class="input-group-append">
              <button type="button" class="btn btn-outline-primary" id="btnManualSubmitPinjam">
                <i class="fas fa-search mr-1"></i> Cari Buku
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- MODAL FORM PEMINJAMAN LANGSUNG (HASIL SCAN & VALIDASI 2 BUKU)   -->
<!-- ============================================================== -->
<div class="modal fade" id="modalFormPinjamDirect" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content shadow-lg border-0">
      <form action="<?= $base ?>admin/peminjaman/add-direct" method="post" id="formPinjamDirect">
        <input type="hidden" name="id_buku" id="pinjamIdBuku">

        <div class="modal-header bg-gradient-primary text-white">
          <h5 class="modal-title font-weight-bold">
            <i class="fas fa-clipboard-list mr-2"></i> Konfirmasi Peminjaman Buku
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-4">
          <!-- Detail Buku Terpilih (Visual Card) -->
          <div class="card bg-light border mb-3 shadow-xs">
            <div class="card-body p-3 d-flex align-items-center">
              <img id="pinjamCoverBuku" src="<?= $base ?>assets/img/cover_placeholder.svg" alt="Cover" style="width: 54px; height: 74px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;" class="mr-3 flex-shrink-0 shadow-xs">
              <div class="flex-grow-1" style="min-width: 0;">
                <div class="d-flex align-items-center justify-content-between">
                  <h6 class="font-weight-bold text-primary mb-1 text-truncate" id="pinjamJudulBuku">-</h6>
                  <span id="pinjamStokBuku" class="badge badge-success px-2 py-1 ml-2">-</span>
                </div>
                <div class="text-xs text-muted mb-1 text-truncate">
                  <span id="pinjamPengarangBuku">-</span>
                </div>
                <div class="text-xs text-muted">
                  ISBN: <code id="pinjamIsbnBuku" class="text-dark font-weight-bold">-</code>
                </div>
              </div>
            </div>
          </div>

          <!-- Alert Stok Kosong (jika 0) -->
          <div id="alertStokHabis" class="alert alert-danger text-sm" style="display: none;">
            <i class="fas fa-exclamation-circle mr-1"></i>
            <strong>Peminjaman Ditolak!</strong> Stok buku fisik ini sedang habis (0) dan tidak tersedia untuk dipinjam.
          </div>

          <!-- Pilih Siswa Peminjam (Interactive Selection) -->
          <div class="form-group mb-2">
            <label class="font-weight-bold text-dark">
              <i class="fas fa-user-graduate mr-1 text-primary"></i> Pilih Siswa Peminjam:
            </label>
            <select name="id_user" id="pinjamSelectUser" class="form-control select2" required style="width: 100%;">
              <option value="">-- Ketik Nama atau NIS Siswa --</option>
              <?php foreach ($siswaList as $s): ?>
                <option value="<?= $s['id_user'] ?>"
                        data-fullname="<?= htmlspecialchars($s['fullname']) ?>"
                        data-nis="<?= htmlspecialchars($s['nis'] ?: '-') ?>"
                        data-kelas="<?= htmlspecialchars($s['kelas'] ?: '-') ?>">
                  <?= htmlspecialchars($s['fullname']) ?> (NIS: <?= htmlspecialchars($s['nis'] ?: '-') ?> | Kelas: <?= htmlspecialchars($s['kelas'] ?: '-') ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Kartu Preview Identitas Siswa Terpilih & Kuota Slot -->
          <div id="studentProfileCard" class="card border border-primary mb-3 bg-white shadow-xs" style="display: none;">
            <div class="card-body p-3 d-flex align-items-center">
              <div class="student-avatar-badge mr-3" id="studentAvatarInitials">AS</div>
              <div class="flex-grow-1">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <strong class="text-dark" style="font-size: 1.02rem;" id="studentCardFullname">-</strong>
                  <span class="badge badge-info px-2 py-1 font-weight-bold" id="studentCardKelas">-</span>
                </div>
                <div class="text-xs text-muted mb-2">
                  Nomor Induk Siswa (NIS): <strong id="studentCardNis" class="text-dark">-</strong>
                </div>
                <!-- Indikator Kuota Peminjaman (Maks 2 Buku) -->
                <div class="p-2 rounded bg-light border">
                  <div class="d-flex align-items-center justify-content-between text-xs mb-1">
                    <span class="font-weight-bold text-muted"><i class="fas fa-layer-group mr-1 text-primary"></i>Status Kuota Sirkulasi (Maks 2 Buku):</span>
                    <span class="font-weight-bold" id="studentQuotaText">0 dari 2 Buku</span>
                  </div>
                  <div class="d-flex" id="studentQuotaMeter" style="gap: 6px;">
                    <!-- Rendered by JS -->
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Indikator Kuota Pinjaman Siswa (Validasi Real-time) -->
          <div id="boxStatusKuotaSiswa" class="mb-3" style="display: none;">
            <!-- Rendered dynamically by JavaScript -->
          </div>

          <div class="row">
            <div class="col-md-5">
              <div class="form-group mb-3">
                <label class="font-weight-bold text-xs"><i class="fas fa-calendar-day mr-1 text-info"></i> Tanggal Pinjam:</label>
                <input type="text" name="tanggal_peminjaman" class="form-control" value="<?= $currentDate ?>" required>
              </div>
            </div>
            <div class="col-md-7">
              <!-- Enum Selector: Kondisi Buku -->
              <div class="form-group mb-3">
                <label class="font-weight-bold text-xs"><i class="fas fa-shield-alt mr-1 text-success"></i> Pilihan Kondisi Fisik Buku:</label>
                <div class="d-flex" style="gap: 10px;">
                  <div class="enum-card flex-fill p-2 text-center active-baik" id="cardKondisiBaikPinjam" onclick="selectEnumPinjam('Baik')">
                    <i class="fas fa-check-circle fa-lg text-success mb-1"></i>
                    <strong class="d-block text-dark text-xs font-weight-bold">Kondisi Baik</strong>
                    <small class="text-muted d-block" style="font-size: 0.68rem;">Fisik utuh &amp; bersih</small>
                  </div>
                  <div class="enum-card flex-fill p-2 text-center" id="cardKondisiRusakPinjam" onclick="selectEnumPinjam('Rusak')">
                    <i class="fas fa-tools fa-lg text-warning mb-1"></i>
                    <strong class="d-block text-dark text-xs font-weight-bold">Kondisi Rusak</strong>
                    <small class="text-muted d-block" style="font-size: 0.68rem;">Ada cacat/rusak ringan</small>
                  </div>
                </div>
                <input type="hidden" name="kondisi_buku_saat_dipinjam" id="inputKondisiPinjam" value="Baik">
              </div>
            </div>
          </div>

          <div class="alert alert-info text-xs mb-0">
            <i class="fas fa-info-circle mr-1"></i>
            Batas waktu peminjaman adalah <strong>7 hari</strong>. Keterlambatan dikenakan denda sebesar <strong>Rp 5.000 / hari</strong>.
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary font-weight-bold px-4" id="btnSubmitPinjam">
            <i class="fas fa-check mr-1"></i> Konfirmasi Peminjaman
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- MODAL INPUT PINJAM MANUAL (PILIH BUKU INTERAKTIF)              -->
<!-- ============================================================== -->
<div class="modal fade" id="modalPinjamManual" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-gradient-primary text-white">
        <h5 class="modal-title font-weight-bold">
          <i class="fas fa-book-reader mr-2"></i> Pilih Koleksi Buku untuk Peminjaman
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-3">
        <!-- Search & Filter Bar -->
        <div class="row mb-3">
          <div class="col-md-7 mb-2 mb-md-0">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-primary"></i></span>
              </div>
              <input type="text" id="searchBukuManual" class="form-control border-left-0" placeholder="Ketik judul buku, nama pengarang, atau nomor ISBN...">
            </div>
          </div>
          <div class="col-md-5">
            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
              <label class="btn btn-sm btn-outline-primary active flex-fill" id="btnFilterSemua">
                <input type="radio" name="filterStokBuku" checked> Semua Koleksi
              </label>
              <label class="btn btn-sm btn-outline-success flex-fill" id="btnFilterTersedia">
                <input type="radio" name="filterStokBuku"> Siap Pinjam (>0)
              </label>
            </div>
          </div>
        </div>

        <!-- Hidden Native Select (kompatibilitas backward) -->
        <select id="selectBukuManual" class="d-none">
          <option value="">-- Pilih Buku dari Katalog --</option>
          <?php foreach ($bukuList as $b): ?>
            <?php 
              $stok = (int)($b['j_buku_baik'] ?? 0); 
              $cover = PerpusModel::resolveCoverUrl($b, $base);
            ?>
            <option value="<?= $b['id_buku'] ?>" 
                    data-judul="<?= htmlspecialchars($b['judul_buku']) ?>"
                    data-pengarang="<?= htmlspecialchars($b['pengarang'] ?: '-') ?>"
                    data-isbn="<?= htmlspecialchars($b['isbn'] ?: '-') ?>"
                    data-cover="<?= $cover ?>"
                    data-stok="<?= $stok ?>">
              <?= htmlspecialchars($b['judul_buku']) ?> (Stok: <?= $stok ?> Eks)
            </option>
          <?php endforeach; ?>
        </select>

        <!-- Interactive Book Cards Gallery -->
        <div id="bookCardsContainer" style="max-height: 380px; overflow-y: auto; padding-right: 4px;">
          <div class="row" id="bookGridList">
            <?php foreach ($bukuList as $b): ?>
              <?php 
                $stok = (int)($b['j_buku_baik'] ?? 0);
                $coverUrl = PerpusModel::resolveCoverUrl($b, $base);
              ?>
              <div class="col-md-6 mb-2 book-card-wrapper" 
                   data-judul="<?= strtolower(htmlspecialchars($b['judul_buku'])) ?>"
                   data-pengarang="<?= strtolower(htmlspecialchars($b['pengarang'] ?? '')) ?>"
                   data-isbn="<?= htmlspecialchars($b['isbn'] ?? '') ?>"
                   data-stok="<?= $stok ?>">
                <div class="book-select-item p-2 d-flex align-items-center h-100 <?= $stok <= 0 ? 'opacity-75' : '' ?>"
                     data-id="<?= $b['id_buku'] ?>"
                     data-judul="<?= htmlspecialchars($b['judul_buku']) ?>"
                     data-pengarang="<?= htmlspecialchars($b['pengarang'] ?: '-') ?>"
                     data-isbn="<?= htmlspecialchars($b['isbn'] ?: '-') ?>"
                     data-cover="<?= $coverUrl ?>"
                     data-stok="<?= $stok ?>">
                  <img src="<?= $coverUrl ?>" alt="Cover" style="width: 44px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0;" class="mr-2 flex-shrink-0 shadow-xs">
                  <div class="flex-grow-1" style="min-width: 0;">
                    <strong class="d-block text-dark text-truncate book-item-title" title="<?= htmlspecialchars($b['judul_buku']) ?>" style="font-size: 0.9rem;">
                      <?= htmlspecialchars($b['judul_buku']) ?>
                    </strong>
                    <div class="text-xs text-muted text-truncate mb-1">
                      <i class="fas fa-user-edit mr-1"></i><?= htmlspecialchars($b['pengarang'] ?: '-') ?>
                    </div>
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                      <code class="text-xs text-muted"><?= htmlspecialchars($b['isbn'] ?: 'No ISBN') ?></code>
                      <?php if ($stok > 0): ?>
                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i><?= $stok ?> Eks</span>
                      <?php else: ?>
                        <span class="badge badge-danger px-2 py-1"><i class="fas fa-times mr-1"></i>Habis</span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="select-check-icon ml-2 text-primary" style="display: none;">
                    <i class="fas fa-check-circle fa-lg"></i>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div id="noBookFoundAlert" class="text-center py-4 text-muted" style="display: none;">
            <i class="fas fa-book-open fa-2x mb-2 text-secondary"></i>
            <p class="mb-0">Tidak ada koleksi buku yang cocok dengan pencarian.</p>
          </div>
        </div>

        <!-- Selected Book Preview Box -->
        <div id="selectedBookPreviewBox" class="mt-3 p-2 bg-light border rounded d-flex align-items-center justify-content-between" style="display: none;">
          <div class="d-flex align-items-center text-truncate">
            <span class="badge badge-primary mr-2 font-weight-bold px-2 py-1">Dipilih:</span>
            <strong id="previewSelectedJudul" class="text-dark mr-2 text-truncate">-</strong>
            <span id="previewSelectedStok" class="badge badge-success">-</span>
          </div>
          <small class="text-muted flex-shrink-0 ml-2"><i class="fas fa-mouse-pointer mr-1"></i>Klik 2x untuk langsung lanjut</small>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary btn-sm font-weight-bold px-4" id="btnLanjutPinjamManual" disabled>
          Lanjutkan Peminjaman <i class="fas fa-arrow-right ml-1"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var html5QrCode = null;

  // Sound chime
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

  // Open Scanner Modal
  // Inisialisasi PerpusScanner Omni-Directional untuk Sirkulasi Peminjaman
  var pinjamScanner = new PerpusScanner({
    containerId: 'readerPinjam',
    selectCameraId: 'cameraSelectPinjam',
    statusId: 'scannerStatusPinjam',
    baseApiUrl: '<?= $base ?>',
    onScanSuccess: function(decodedText, format) {
      if (typeof playScannerBeep === 'function') playScannerBeep();
      $('#modalScannerPinjam').modal('hide');
      prosesCekKodePinjam(decodedText);
    }
  });

  // Open Scanner Modal
  $('#btnBukaScannerPinjam').on('click', function() {
    $('#modalScannerPinjam').modal('show');
    pinjamScanner.start();
  });

  $('#btnCloseScannerPinjam').on('click', function() {
    pinjamScanner.stop();
    $('#modalScannerPinjam').modal('hide');
  });

  $('#modalScannerPinjam').on('hidden.bs.modal', function () {
    pinjamScanner.stop();
  });

  // Manual code input
  $('#btnManualSubmitPinjam').on('click', function() {
    var code = $('#manualCodePinjam').val().trim();
    if (!code) return;
    pinjamScanner.stop();
    $('#modalScannerPinjam').modal('hide');
    prosesCekKodePinjam(code);
  });

  // Check code with backend API
  function prosesCekKodePinjam(code) {
    $.getJSON('<?= $base ?>admin/peminjaman/api-scan-buku?code=' + encodeURIComponent(code), function(res) {
      if (res.status === 'success' && res.data) {
        var b = res.data;
        bukaModalFormPinjamDirect(b.id_buku, b.judul_buku, b.pengarang || '-', b.isbn || '-', parseInt(b.j_buku_baik) || 0);
      }
    }).fail(function(xhr) {
      var err = xhr.responseJSON ? xhr.responseJSON.message : 'Buku atau Barcode tidak valid/tidak terdaftar di sistem!';
      alert(err);
    });
  }

  // Lanjut dari modal pilihan buku manual
  $('#btnLanjutPinjamManual').on('click', function() {
    var sel = $('#selectBukuManual option:selected');
    var id = sel.val();
    if (!id) {
      alert('Silakan pilih salah satu buku terlebih dahulu.');
      return;
    }
    var judul = sel.data('judul');
    var pengarang = sel.data('pengarang');
    var isbn = sel.data('isbn');
    var cover = sel.data('cover');
    var stok = parseInt(sel.data('stok')) || 0;

    $('#modalPinjamManual').modal('hide');
    bukaModalFormPinjamDirect(id, judul, pengarang, isbn, stok, cover);
  });

  // Interaksi Klik Card Buku di Galeri Manual
  $('.book-select-item').on('click', function() {
    var id = $(this).data('id');
    var judul = $(this).data('judul');
    var pengarang = $(this).data('pengarang');
    var isbn = $(this).data('isbn');
    var cover = $(this).data('cover');
    var stok = parseInt($(this).data('stok')) || 0;

    $('.book-select-item').removeClass('selected');
    $('.select-check-icon').hide();
    $(this).addClass('selected');
    $(this).find('.select-check-icon').show();

    // Sync native hidden select
    $('#selectBukuManual').val(id);

    // Update preview box
    $('#previewSelectedJudul').text(judul);
    $('#previewSelectedStok').text(stok > 0 ? (stok + ' Eks Tersedia') : 'Stok Habis');
    $('#previewSelectedStok').removeClass('badge-success badge-danger').addClass(stok > 0 ? 'badge-success' : 'badge-danger');
    $('#selectedBookPreviewBox').show();

    $('#btnLanjutPinjamManual').prop('disabled', false);
  });

  // Double click untuk langsung lanjut
  $('.book-select-item').on('dblclick', function() {
    $(this).trigger('click');
    $('#btnLanjutPinjamManual').trigger('click');
  });

  // Live Search Buku Manual
  $('#searchBukuManual').on('input', function() {
    filterBookCards();
  });

  $('#btnFilterSemua').on('click', function() {
    setTimeout(filterBookCards, 50);
  });
  $('#btnFilterTersedia').on('click', function() {
    setTimeout(filterBookCards, 50);
  });

  function filterBookCards() {
    var query = ($('#searchBukuManual').val() || '').toLowerCase().trim();
    var onlyTersedia = $('#btnFilterTersedia').hasClass('active');
    var visibleCount = 0;

    $('.book-card-wrapper').each(function() {
      var j = $(this).data('judul') + '';
      var p = $(this).data('pengarang') + '';
      var i = $(this).data('isbn') + '';
      var stok = parseInt($(this).data('stok')) || 0;

      var matchQuery = (!query || j.indexOf(query) !== -1 || p.indexOf(query) !== -1 || i.indexOf(query) !== -1);
      var matchStock = (!onlyTersedia || stok > 0);

      if (matchQuery && matchStock) {
        $(this).show();
        visibleCount++;
      } else {
        $(this).hide();
      }
    });

    if (visibleCount === 0) {
      $('#noBookFoundAlert').show();
    } else {
      $('#noBookFoundAlert').hide();
    }
  }

  // Toggle Enum Kondisi Fisik Buku
  window.selectEnumPinjam = function(val) {
    $('#inputKondisiPinjam').val(val);
    if (val === 'Baik') {
      $('#cardKondisiBaikPinjam').addClass('active-baik');
      $('#cardKondisiRusakPinjam').removeClass('active-rusak');
    } else {
      $('#cardKondisiRusakPinjam').addClass('active-rusak');
      $('#cardKondisiBaikPinjam').removeClass('active-baik');
    }
  };

  // Buka Modal Form Peminjaman
  function bukaModalFormPinjamDirect(idBuku, judul, pengarang, isbn, stok, cover) {
    $('#pinjamIdBuku').val(idBuku);
    $('#pinjamJudulBuku').text(judul);
    $('#pinjamPengarangBuku').text('Pengarang: ' + pengarang);
    $('#pinjamIsbnBuku').text(isbn);
    $('#pinjamStokBuku').text(stok + ' Buku Baik');
    if (cover) {
      $('#pinjamCoverBuku').attr('src', cover);
    } else {
      $('#pinjamCoverBuku').attr('src', '<?= $base ?>assets/img/cover_placeholder.svg');
    }

    if (stok <= 0) {
      $('#alertStokHabis').show();
      $('#btnSubmitPinjam').prop('disabled', true);
    } else {
      $('#alertStokHabis').hide();
      $('#btnSubmitPinjam').prop('disabled', false);
    }

    // Reset pilihan siswa & preview profile
    $('#pinjamSelectUser').val('');
    $('#studentProfileCard').hide();
    $('#boxStatusKuotaSiswa').hide().html('');
    selectEnumPinjam('Baik');

    $('#modalFormPinjamDirect').modal('show');
  }

  // Validasi Kuota 2 Buku per Siswa secara Real-time saat memilih Siswa
  $('#pinjamSelectUser').on('change', function() {
    var userId = $(this).val();
    var selOption = $(this).find('option:selected');
    var box = $('#boxStatusKuotaSiswa');
    var btn = $('#btnSubmitPinjam');
    var profileCard = $('#studentProfileCard');

    if (!userId) {
      profileCard.hide();
      box.hide().html('');
      btn.prop('disabled', false);
      return;
    }

    var fullname = selOption.data('fullname') || selOption.text().split('(')[0].trim();
    var nis = selOption.data('nis') || '-';
    var kelas = selOption.data('kelas') || '-';

    // Inisial avatar
    var initials = fullname.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase();
    $('#studentAvatarInitials').text(initials || 'S');
    $('#studentCardFullname').text(fullname);
    $('#studentCardNis').text(nis);
    $('#studentCardKelas').text(kelas !== '-' ? ('Kelas ' + kelas) : 'Anggota');
    profileCard.slideDown(200);

    box.html('<small class="text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Memeriksa riwayat pinjaman siswa...</small>').show();

    $.getJSON('<?= $base ?>admin/peminjaman/api-check-siswa-loan?id_user=' + userId, function(res) {
      if (res.status === 'success') {
        var count = res.active_count;
        $('#studentQuotaText').text(count + ' dari 2 Buku');

        var quotaHtml = '';
        if (count === 0) {
          quotaHtml = `
            <div class="flex-fill p-1 bg-success text-white rounded text-center text-xs font-weight-bold shadow-xs">
              <i class="fas fa-check-circle mr-1"></i> Slot 1: Bebas
            </div>
            <div class="flex-fill p-1 bg-success text-white rounded text-center text-xs font-weight-bold shadow-xs">
              <i class="fas fa-check-circle mr-1"></i> Slot 2: Bebas
            </div>
          `;
        } else if (count === 1) {
          quotaHtml = `
            <div class="flex-fill p-1 bg-warning text-dark rounded text-center text-xs font-weight-bold shadow-xs">
              <i class="fas fa-book-reader mr-1"></i> Slot 1: Terpakai
            </div>
            <div class="flex-fill p-1 bg-success text-white rounded text-center text-xs font-weight-bold shadow-xs">
              <i class="fas fa-check-circle mr-1"></i> Slot 2: Tersedia
            </div>
          `;
        } else {
          quotaHtml = `
            <div class="flex-fill p-1 bg-danger text-white rounded text-center text-xs font-weight-bold shadow-xs">
              <i class="fas fa-ban mr-1"></i> Slot 1: Penuh
            </div>
            <div class="flex-fill p-1 bg-danger text-white rounded text-center text-xs font-weight-bold shadow-xs">
              <i class="fas fa-ban mr-1"></i> Slot 2: Penuh
            </div>
          `;
        }
        $('#studentQuotaMeter').html(quotaHtml);

        if (count >= 2) {
          // BATAL / DITOLAK
          box.html(`
            <div class="alert alert-danger p-2 mb-0 text-xs shadow-xs">
              <i class="fas fa-ban mr-1"></i>
              <strong>PEMINJAMAN DITOLAK!</strong> Siswa <strong>${res.user.fullname}</strong> sudah meminjam <strong>2 buku</strong> dan belum dikembalikan. Batas maksimal per siswa adalah 2 buku.
            </div>
          `);
          btn.prop('disabled', true);
        } else {
          // BISA MEMINJAM
          box.html(`
            <div class="alert alert-success p-2 mb-0 text-xs shadow-xs">
              <i class="fas fa-check-circle mr-1"></i>
              Siswa terverifikasi memenuhi syarat. Dapat meminjam <strong>${2 - count} buku lagi</strong>.
            </div>
          `);
          // Hanya aktifkan jika stok buku > 0
          var stok = parseInt($('#pinjamStokBuku').text()) || 0;
          btn.prop('disabled', stok <= 0);
        }
      }
    }).fail(function() {
      box.html('<div class="alert alert-warning p-2 mb-0 text-xs">Gagal memeriksa kuota peminjam.</div>');
    });
  });

  // ==========================================
  // Inisialisasi Grafik Peminjaman dalam Sebulan
  // ==========================================
  const dailyLoansData = <?= json_encode($chartDailyLoans ?? []) ?>;
  const ctxDailyLoan = document.getElementById('chartDailyLoansCanvas');
  if (ctxDailyLoan && dailyLoansData.days) {
    new Chart(ctxDailyLoan.getContext('2d'), {
      type: 'bar',
      data: {
        labels: dailyLoansData.days,
        datasets: [{
          label: 'Jumlah Buku Dipinjam',
          data: dailyLoansData.counts,
          backgroundColor: 'rgba(59, 130, 246, 0.85)',
          borderColor: '#2563eb',
          borderWidth: 1.5,
          borderRadius: 4,
          hoverBackgroundColor: '#1d4ed8'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => ` Peminjaman: ${ctx.raw} buku`
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1, precision: 0 },
            grid: { color: 'rgba(0, 0, 0, 0.05)' }
          },
          x: {
            grid: { display: false },
            ticks: { font: { size: 10 } }
          }
        }
      }
    });
  }

  // ==========================================
  // Live Search Riwayat Sirkulasi Peminjaman
  // ==========================================
  $('#tableSearch').on('input keyup paste change', function () {
    var query = $(this).val().toLowerCase().trim();
    var matchCount = 0;

    $('#dataTable tbody tr').each(function () {
      if ($(this).attr('id') === 'rowNoSearchResultPinjam') return;
      if ($(this).find('td[colspan]').length > 0 && $(this).attr('id') !== 'rowNoSearchResultPinjam') return;

      var rowText = $(this).text().toLowerCase();
      if (query === '' || rowText.indexOf(query) !== -1) {
        $(this).show();
        matchCount++;
      } else {
        $(this).hide();
      }
    });

    var emptyRow = $('#rowNoSearchResultPinjam');
    if (matchCount === 0 && query !== '') {
      if (emptyRow.length === 0) {
        $('#dataTable tbody').append(
          '<tr id="rowNoSearchResultPinjam"><td colspan="10" class="text-center py-4 text-muted"><i class="fas fa-search mr-2"></i>Tidak ada data peminjaman yang cocok dengan "<strong>' + $('<div>').text(query).html() + '</strong>".</td></tr>'
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
