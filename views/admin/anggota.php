<?php
// Data Anggota View (Clean UI & Pop-Up Edition)
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$pageTitle = 'Data Anggota & Sirkulasi Siswa - Perpustakaan SMPN 1 Kota Gajah';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/sidebar_admin.php';
$base = (new Controller())->baseUrl();

// Helper URL WhatsApp Generator
function buildWaLink($phone, $text) {
    $clean = preg_replace('/[^0-9]/', '', $phone);
    if (strpos($clean, '0') === 0) {
        $clean = '62' . substr($clean, 1);
    }
    return 'https://api.whatsapp.com/send?phone=' . $clean . '&text=' . rawurlencode($text);
}
?>

<style>
  /* Ensure all modals display on top of AdminLTE sidebar & navbar */
  .modal {
    z-index: 1060 !important;
  }
  .modal-backdrop {
    z-index: 1055 !important;
  }
  .modal-dialog-scrollable .modal-content {
    max-height: calc(100vh - 3.5rem);
  }
</style>

<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-users text-primary mr-2"></i> Data Anggota & Sirkulasi Siswa
          </h1>
          <small class="text-muted">Master data anggota siswa, pemantauan status pinjaman, notifikasi WhatsApp, dan analitik sirkulasi.</small>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= $base ?>admin/dashboard">Home</a></li>
            <li class="breadcrumb-item active">Data Anggota</li>
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

      <!-- Main Table Card -->
      <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap py-3">
          <div>
            <h5 class="card-title font-weight-bold mb-0 text-dark">
              <i class="fas fa-address-book text-primary mr-2"></i> Daftar Anggota Siswa Perpustakaan
            </h5>
            <small class="text-muted d-block mt-1">
              WhatsApp Siswa Default: <strong class="text-success"><i class="fab fa-whatsapp"></i> 087767499895</strong> &bull; Kontak Admin: <strong class="text-primary"><i class="fab fa-whatsapp"></i> 082385495700</strong>
            </small>
          </div>
          <div class="card-tools mt-2 mt-md-0 d-flex align-items-center">
            <div class="input-group input-group-sm mr-2" style="width: 220px;">
              <input type="text" id="tableSearch" class="form-control" placeholder="Cari NIS, nama, kelas...">
              <div class="input-group-append">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
              </div>
            </div>
            <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalTambahAnggota">
              <i class="fas fa-user-plus mr-1"></i> Tambah Anggota
            </button>
          </div>
        </div>
        
        <!-- Table Responsive (Bersih & Rapi) -->
        <div class="card-body table-responsive p-0">
          <table class="table table-hover table-striped mb-0" id="dataTable">
            <thead class="bg-light">
              <tr>
                <th style="width: 45px;" class="text-center">No</th>
                <th>Identitas Siswa</th>
                <th>No. WhatsApp</th>
                <th class="text-center" style="width: 170px;">Status Pinjaman</th>
                <th class="text-center" style="width: 280px;">Informasi & Sirkulasi</th>
                <th style="width: 100px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($anggotaList)): ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-3x mb-2 text-secondary d-block"></i>
                    Belum ada data anggota siswa terdaftar.
                  </td>
                </tr>
              <?php else: ?>
                <?php $no = 1; foreach ($anggotaList as $row): 
                  $activeCount = (int)($row['active_loan_count'] ?? 0);
                  $waSiswa     = !empty($row['no_whatsapp']) ? $row['no_whatsapp'] : '087767499895';
                  $waChatUrl   = buildWaLink($waSiswa, "Halo {$row['fullname']}, ini adalah pemberitahuan dari Petugas Perpustakaan SMP Negeri 1 Kota Gajah.");
                ?>
                  <tr>
                    <td class="text-center font-weight-bold align-middle"><?= $no++ ?></td>
                    <td class="align-middle">
                      <div class="d-flex align-items-center">
                        <img src="<?= $base ?>assets/img/avatar.svg" alt="Avatar" class="img-circle elevation-1 mr-2" style="width: 38px; height: 38px;">
                        <div>
                          <strong class="text-dark d-block"><?= htmlspecialchars($row['fullname']) ?></strong>
                          <span class="text-xs text-muted">
                            NIS: <strong><?= htmlspecialchars($row['nis'] ?: '-') ?></strong> &bull; Kelas: <span class="badge badge-light border font-weight-bold"><?= htmlspecialchars($row['kelas'] ?: '-') ?></span> &bull; <?= htmlspecialchars($row['kode_user']) ?>
                          </span>
                        </div>
                      </div>
                    </td>
                    <td class="align-middle">
                      <a href="<?= $waChatUrl ?>" target="_blank" class="badge badge-success p-2 font-weight-bold shadow-xs" title="Chat via WhatsApp">
                        <i class="fab fa-whatsapp mr-1"></i> <?= htmlspecialchars($waSiswa) ?>
                      </a>
                    </td>
                    <td class="text-center align-middle">
                      <?php if ($activeCount === 0): ?>
                        <span class="badge badge-success py-1 px-2 font-weight-bold" style="font-size: 0.85rem;">
                          <i class="fas fa-check-circle mr-1"></i> Bebas Pinjam (0/2)
                        </span>
                      <?php elseif ($activeCount === 1): ?>
                        <span class="badge badge-warning text-dark py-1 px-2 font-weight-bold" style="font-size: 0.85rem;">
                          <i class="fas fa-book-reader mr-1"></i> 1 Buku Dipinjam (1/2)
                        </span>
                      <?php else: ?>
                        <span class="badge badge-danger py-1 px-2 font-weight-bold" style="font-size: 0.85rem;">
                          <i class="fas fa-ban mr-1"></i> Kuota Penuh (2/2)
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center align-middle">
                      <!-- Tombol Pop Up Buku Pinjaman & Riwayat -->
                      <button type="button" class="btn btn-info btn-sm font-weight-bold shadow-xs mr-1" data-toggle="modal" data-target="#modalBukuSiswa<?= $row['id_user'] ?>">
                        <i class="fas fa-book-reader mr-1"></i> Buku & Riwayat (<?= $activeCount ?>)
                      </button>

                      <!-- Tombol Pop Up Detail Siswa -->
                      <button type="button" class="btn btn-outline-secondary btn-sm font-weight-bold" data-toggle="modal" data-target="#modalDetailSiswa<?= $row['id_user'] ?>">
                        <i class="fas fa-id-card mr-1"></i> Profil
                      </button>
                    </td>
                    <td class="text-center align-middle">
                      <!-- Tombol Edit -->
                      <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalEditAnggota<?= $row['id_user'] ?>" title="Edit Siswa">
                        <i class="fas fa-edit"></i>
                      </button>

                      <!-- Tombol Hapus -->
                      <form action="<?= $base ?>admin/anggota/delete" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data anggota <?= addslashes($row['fullname']) ?>?');">
                        <input type="hidden" name="id_user" value="<?= $row['id_user'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
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

      <!-- ============================================================== -->
      <!-- 2 GRAFIK DI BAGIAN BAWAH (PEMINJAMAN & PENGEMBALIAN SEBULAN)   -->
      <!-- ============================================================== -->
      <h5 class="text-secondary font-weight-bold mb-3 mt-4">
        <i class="fas fa-chart-area mr-1 text-primary"></i> Analitik Sirkulasi Anggota dalam Sebulan
      </h5>

      <div class="row mb-4">
        <!-- Grafik 1: Peminjaman dalam Sebulan -->
        <div class="col-lg-6 col-12 mb-3">
          <div class="card card-outline card-primary shadow-sm h-100">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
              <h3 class="card-title font-weight-bold text-dark mb-0">
                <i class="fas fa-chart-line text-primary mr-2"></i> 1. Grafik Peminjaman dalam Sebulan
              </h3>
              <span class="badge badge-primary px-2 py-1"><?= htmlspecialchars($chartDailyLoans['month_name'] ?? date('F Y')) ?></span>
            </div>
            <div class="card-body pt-1">
              <p class="text-muted text-xs mb-3">
                Total Peminjaman: <strong><?= (int)($chartDailyLoans['total'] ?? 0) ?> buku</strong> &bull; Puncak: <strong>Tgl <?= (int)($chartDailyLoans['max_day'] ?? 1) ?> (<?= (int)($chartDailyLoans['max_count'] ?? 0) ?> buku)</strong>
              </p>
              <div style="position: relative; height: 260px; width: 100%;">
                <canvas id="chartAnggotaLoansCanvas"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Grafik 2: Pengembalian dalam Sebulan -->
        <div class="col-lg-6 col-12 mb-3">
          <div class="card card-outline card-success shadow-sm h-100">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
              <h3 class="card-title font-weight-bold text-dark mb-0">
                <i class="fas fa-chart-bar text-success mr-2"></i> 2. Grafik Pengembalian dalam Sebulan
              </h3>
              <span class="badge badge-success px-2 py-1"><?= htmlspecialchars($chartDailyReturns['month_name'] ?? date('F Y')) ?></span>
            </div>
            <div class="card-body pt-1">
              <p class="text-muted text-xs mb-3">
                Total Kembali: <strong><?= (int)($chartDailyReturns['total'] ?? 0) ?> buku</strong> &bull; Kas Denda: <strong>Rp <?= number_format($chartDailyReturns['total_denda'] ?? 0, 0, ',', '.') ?></strong>
              </p>
              <div style="position: relative; height: 260px; width: 100%;">
                <canvas id="chartAnggotaReturnsCanvas"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<!-- ============================================================== -->
<!-- POP-UP MODALS PER SISWA (DITEMPATKAN DI LUAR TABEL)            -->
<!-- ============================================================== -->
<?php if (!empty($anggotaList)): ?>
  <?php foreach ($anggotaList as $row): 
    $activeCount = (int)($row['active_loan_count'] ?? 0);
    $waSiswa     = !empty($row['no_whatsapp']) ? $row['no_whatsapp'] : '087767499895';
    $waChatUrl   = buildWaLink($waSiswa, "Halo {$row['fullname']}, ini adalah pemberitahuan resmi dari Perpustakaan SMP Negeri 1 Kota Gajah.");
  ?>

    <!-- 1. MODAL BUKU PINJAMAN, DETAIL & STATISTIK SIRKULASI -->
    <div class="modal fade" id="modalBukuSiswa<?= $row['id_user'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-primary text-white py-3">
            <h5 class="modal-title font-weight-bold mb-0">
              <i class="fas fa-book-reader mr-2"></i> Sirkulasi & Buku Pinjaman: <?= htmlspecialchars($row['fullname']) ?>
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body p-3">

            <!-- 4 KARTU STATISTIK RINGKAS SISWA -->
            <div class="row text-center mb-3">
              <div class="col-3">
                <div class="border rounded p-2 bg-light shadow-xs">
                  <span class="text-xs text-muted d-block font-weight-bold text-uppercase">Pernah Pinjam</span>
                  <strong class="text-primary font-weight-bold" style="font-size: 1.15rem;"><?= (int)$row['total_pinjam'] ?>x</strong>
                </div>
              </div>
              <div class="col-3">
                <div class="border rounded p-2 bg-light shadow-xs">
                  <span class="text-xs text-muted d-block font-weight-bold text-uppercase">Dikembalikan</span>
                  <strong class="text-success font-weight-bold" style="font-size: 1.15rem;"><?= (int)$row['total_kembali'] ?></strong>
                </div>
              </div>
              <div class="col-3">
                <div class="border rounded p-2 bg-light shadow-xs">
                  <span class="text-xs text-muted d-block font-weight-bold text-uppercase">Total Denda</span>
                  <strong class="text-danger font-weight-bold" style="font-size: 1.05rem;">Rp <?= number_format($row['total_denda_rp'], 0, ',', '.') ?></strong>
                </div>
              </div>
              <div class="col-3">
                <div class="border rounded p-2 bg-light shadow-xs">
                  <span class="text-xs text-muted d-block font-weight-bold text-uppercase">Kondisi Fisik</span>
                  <strong class="text-dark font-weight-bold" style="font-size: 0.95rem;">
                    <span class="text-success"><?= (int)$row['total_kembali_baik'] ?> Baik</span> | <span class="text-danger"><?= (int)$row['total_kembali_rusak'] ?> Rusak</span>
                  </strong>
                </div>
              </div>
            </div>

            <!-- SECTION A: BUKU YANG SEDANG DIPINJAM SAAT INI -->
            <div class="d-flex justify-content-between align-items-center mb-2 mt-2">
              <h6 class="font-weight-bold text-dark mb-0">
                <i class="fas fa-bookmark text-warning mr-1"></i> Buku yang Sedang Dipinjam Saat Ini:
              </h6>
              <span class="badge badge-<?= $activeCount > 0 ? 'warning' : 'success' ?> px-2 py-1">
                Pinjaman Aktif: <?= $activeCount ?> / 2 Buku
              </span>
            </div>

            <?php if (empty($row['active_loans'])): ?>
              <div class="alert alert-light border py-3 px-3 mb-3 text-muted d-flex align-items-center">
                <i class="fas fa-check-circle text-success fa-2x mr-3"></i>
                <div>
                  <strong class="text-dark">Siswa Bebas Pinjaman (0 Buku)</strong><br>
                  <small class="text-muted">Tidak ada buku yang sedang dipinjam saat ini. Kuota pinjaman 2 buku tersedia penuh.</small>
                </div>
              </div>
            <?php else: ?>
              <div class="mb-3">
                <?php foreach ($row['active_loans'] as $act): 
                  $isLate = ((int)$act['terlambat_hari'] > 0);
                  $slipPdfUrl = $base . "admin/peminjaman/slip?id=" . $act['id_peminjaman'];
                  
                  // Teks Pesan WhatsApp Kirim Struk
                  $waMsg = "*PERPUSTAKAAN SMP NEGERI 1 KOTA GAJAH*\n";
                  $waMsg .= "Halo *{$row['fullname']}* (NIS: {$row['nis']}, Kelas: {$row['kelas']}),\n";
                  $waMsg .= "Berikut bukti peminjaman buku perpustakaan Anda:\n";
                  $waMsg .= "📖 *Judul Buku:* {$act['judul_buku']}\n";
                  $waMsg .= "📅 *Tanggal Pinjam:* {$act['tanggal_peminjaman']}\n";
                  $waMsg .= "⏰ *Batas Jatuh Tempo:* {$act['tgl_jatuh_tempo']}\n";
                  if ($isLate) {
                    $waMsg .= "⚠️ *Status:* Terlambat {$act['terlambat_hari']} hari (Estimasi Denda: Rp " . number_format($act['estimasi_denda'], 0, ',', '.') . ")\n";
                  } else {
                    $waMsg .= "✅ *Status:* Sedang Dipinjam (Tepat Waktu)\n";
                  }
                  $waMsg .= "📄 *Struk Resmi Digital (PDF):* {$slipPdfUrl}\n\n";
                  $waMsg .= "Harap menjaga kondisi buku tetap baik dan mengembalikan sebelum batas jatuh tempo.\n";
                  $waMsg .= "_Admin Perpustakaan SMPN 1 Kota Gajah (WA: 082385495700)_";
                  
                  $sendStrukWaUrl = buildWaLink($waSiswa, $waMsg);
                ?>
                  <div class="card mb-2 border shadow-xs" style="border-left: 4px solid <?= $isLate ? '#dc3545' : '#ffc107' ?> !important; background: <?= $isLate ? '#fff9f9' : '#fffdf5' ?>;">
                    <div class="card-body p-3">
                      <div class="d-flex justify-content-between align-items-start flex-wrap">
                        <div style="flex: 1; min-width: 250px;" class="mr-2">
                          <h6 class="font-weight-bold text-dark mb-1">
                            <i class="fas fa-book text-primary mr-1"></i> <?= htmlspecialchars($act['judul_buku']) ?>
                          </h6>
                          <div class="text-xs text-muted mb-2">
                            Pengarang: <strong><?= htmlspecialchars($act['pengarang'] ?? '-') ?></strong> &bull; ISBN: <?= htmlspecialchars($act['isbn'] ?? '-') ?> &bull; Kondisi: <span class="badge badge-secondary"><?= htmlspecialchars($act['kondisi_buku_saat_dipinjam'] ?: 'Baik') ?></span>
                          </div>
                          <div class="text-xs text-secondary">
                            <span>Tgl Pinjam: <strong><?= htmlspecialchars($act['tanggal_peminjaman']) ?></strong></span>
                            <span class="mx-2">&bull;</span>
                            <span>Batas Kembali: <strong class="text-primary"><?= htmlspecialchars($act['tgl_jatuh_tempo']) ?></strong></span>
                          </div>
                          <div class="mt-2">
                            <?php if ($isLate): ?>
                              <span class="badge badge-danger px-2 py-1">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Terlambat <?= $act['terlambat_hari'] ?> Hari (Denda Rp <?= number_format($act['estimasi_denda'], 0, ',', '.') ?>)
                              </span>
                            <?php else: ?>
                              <span class="badge badge-success px-2 py-1">
                                <i class="fas fa-check mr-1"></i> Masa Pinjam Aktif (Tepat Waktu)
                              </span>
                            <?php endif; ?>
                          </div>
                        </div>
                        <div class="mt-2 mt-sm-0 d-flex flex-column align-items-end">
                          <a href="<?= $sendStrukWaUrl ?>" target="_blank" class="btn btn-success btn-xs font-weight-bold px-2 py-1 shadow-xs mb-2">
                            <i class="fab fa-whatsapp mr-1"></i> Kirim Struk (WA)
                          </a>
                          <a href="<?= $slipPdfUrl ?>" target="_blank" class="btn btn-outline-primary btn-xs font-weight-bold px-2 py-1">
                            <i class="fas fa-print mr-1"></i> Slip PDF
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <!-- SECTION B: RIWAYAT SEMUA PEMINJAMAN TERDAHULU -->
            <div class="d-flex justify-content-between align-items-center mb-2 mt-3">
              <h6 class="font-weight-bold text-dark mb-0">
                <i class="fas fa-history text-secondary mr-1"></i> Riwayat Sirkulasi Sebelumnya:
              </h6>
              <small class="text-muted">Total: <?= count($row['all_loans'] ?? []) ?> transaksi</small>
            </div>
            
            <div class="table-responsive border rounded" style="max-height: 220px; overflow-y: auto;">
              <table class="table table-sm table-striped table-hover mb-0 text-xs">
                <thead class="bg-light sticky-top">
                  <tr>
                    <th style="width: 30px;" class="text-center">No</th>
                    <th>Judul Buku</th>
                    <th style="width: 90px;">Tgl Pinjam</th>
                    <th style="width: 90px;">Tgl Kembali</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 80px;">Kondisi</th>
                    <th style="width: 90px;">Denda</th>
                    <th class="text-center" style="width: 50px;">Slip</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($row['all_loans'])): ?>
                    <tr>
                      <td colspan="8" class="text-center text-muted py-3">Belum pernah melakukan transaksi peminjaman buku.</td>
                    </tr>
                  <?php else: ?>
                    <?php $hNo = 1; foreach ($row['all_loans'] as $hist): 
                      $isHistReturned = (!empty($hist['tanggal_pengembalian']) && $hist['tanggal_pengembalian'] !== '0000-00-00');
                    ?>
                      <tr>
                        <td class="text-center text-muted"><?= $hNo++ ?></td>
                        <td><strong><?= htmlspecialchars($hist['judul_buku']) ?></strong></td>
                        <td><?= htmlspecialchars($hist['tanggal_peminjaman']) ?></td>
                        <td><?= $isHistReturned ? htmlspecialchars($hist['tanggal_pengembalian']) : '<span class="text-muted">-</span>' ?></td>
                        <td>
                          <?php if ($isHistReturned): ?>
                            <span class="badge badge-success">Selesai</span>
                          <?php else: ?>
                            <span class="badge badge-warning text-dark">Sedang Dipinjam</span>
                          <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($hist['kondisi_buku_saat_dikembalikan'] ?: ($hist['kondisi_buku_saat_dipinjam'] ?: 'Baik')) ?></td>
                        <td>
                          <?php if (!empty($hist['denda']) && $hist['denda'] !== 'Tidak ada' && $hist['denda'] !== '0'): ?>
                            <strong class="text-danger"><?= htmlspecialchars($hist['denda']) ?></strong>
                          <?php else: ?>
                            <span class="text-muted">Tidak ada</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-center">
                          <a href="<?= $base ?>admin/peminjaman/slip?id=<?= $hist['id_peminjaman'] ?>" target="_blank" class="btn btn-outline-secondary btn-xs" title="Cetak Slip">
                            <i class="fas fa-print"></i>
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

          </div>
          <div class="modal-footer bg-light py-2">
            <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Tutup</button>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. MODAL DETAIL PROFIL ANGGOTA SISWA -->
    <div class="modal fade" id="modalDetailSiswa<?= $row['id_user'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-primary text-white py-3">
            <h5 class="modal-title font-weight-bold mb-0">
              <i class="fas fa-id-card mr-2"></i> Profil Anggota Siswa: <?= htmlspecialchars($row['fullname']) ?>
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body p-4 text-center">
            <img src="<?= $base ?>assets/img/avatar.svg" alt="Avatar" class="img-circle elevation-2 mb-2" style="width: 80px; height: 80px;">
            <h5 class="font-weight-bold text-dark mb-0"><?= htmlspecialchars($row['fullname']) ?></h5>
            <p class="text-muted mb-2">
              <?= htmlspecialchars($row['kode_user']) ?> &bull; Siswa Kelas <strong><?= htmlspecialchars($row['kelas']) ?></strong>
            </p>
            <span class="badge badge-success px-2 py-1 mb-3">
              <i class="fas fa-check-circle mr-1"></i> <?= htmlspecialchars($row['verif'] ?: 'Akun Terverifikasi') ?>
            </span>

            <!-- Detail List -->
            <ul class="list-group list-group-flush text-left text-sm border-top border-bottom mb-3">
              <li class="list-group-item d-flex justify-content-between px-1">
                <span class="text-muted"><i class="fas fa-id-badge mr-1"></i> Nomor Induk Siswa (NIS):</span>
                <strong><?= htmlspecialchars($row['nis'] ?: '-') ?></strong>
              </li>
              <li class="list-group-item d-flex justify-content-between px-1">
                <span class="text-muted"><i class="fas fa-user mr-1"></i> Nama Pengguna (Username):</span>
                <strong><?= htmlspecialchars($row['username']) ?></strong>
              </li>
              <li class="list-group-item d-flex justify-content-between px-1 align-items-center">
                <span class="text-muted"><i class="fab fa-whatsapp mr-1 text-success"></i> Nomor WhatsApp Siswa:</span>
                <div>
                  <strong class="text-success mr-2"><?= htmlspecialchars($waSiswa) ?></strong>
                  <a href="<?= $waChatUrl ?>" target="_blank" class="btn btn-success btn-xs" title="Chat via WhatsApp">
                    <i class="fab fa-whatsapp"></i> Chat
                  </a>
                </div>
              </li>
              <li class="list-group-item d-flex justify-content-between px-1">
                <span class="text-muted"><i class="fas fa-map-marker-alt mr-1"></i> Alamat Domisili:</span>
                <span class="text-right" style="max-width: 250px;"><?= htmlspecialchars($row['alamat'] ?: '-') ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-1">
                <span class="text-muted"><i class="fas fa-calendar-alt mr-1"></i> Tanggal Bergabung:</span>
                <span><?= htmlspecialchars($row['join_date'] ?: '-') ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-1">
                <span class="text-muted"><i class="fas fa-history mr-1"></i> Terakhir Login:</span>
                <span class="text-muted"><?= htmlspecialchars($row['terakhir_login'] ?: '-') ?></span>
              </li>
            </ul>

          </div>
          <div class="modal-footer bg-light justify-content-between">
            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            <a href="<?= $waChatUrl ?>" target="_blank" class="btn btn-success btn-sm font-weight-bold">
              <i class="fab fa-whatsapp mr-1"></i> Hubungi Siswa (WA)
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. MODAL EDIT ANGGOTA -->
    <div class="modal fade" id="modalEditAnggota<?= $row['id_user'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title font-weight-bold">Edit Data Anggota Siswa</h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="<?= $base ?>admin/anggota/edit" method="post">
            <input type="hidden" name="id_user" value="<?= $row['id_user'] ?>">
            <div class="modal-body">
              <div class="form-group">
                <label>Kode Anggota</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($row['kode_user']) ?>" readonly>
              </div>
              <div class="form-group">
                <label>Nomor Induk Siswa (NIS) <small class="text-danger">* Wajib</small></label>
                <input type="text" name="nis" class="form-control" value="<?= htmlspecialchars($row['nis']) ?>" required>
              </div>
              <div class="form-group">
                <label>Nama Lengkap Siswa <small class="text-danger">* Wajib</small></label>
                <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($row['fullname']) ?>" required>
              </div>
              <div class="form-group">
                <label>Nomor WhatsApp Siswa (Untuk Kirim Struk) <small class="text-danger">* Wajib</small></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fab fa-whatsapp text-success"></i></span>
                  </div>
                  <input type="text" name="no_whatsapp" class="form-control" value="<?= htmlspecialchars($waSiswa) ?>" required>
                </div>
              </div>
              <div class="form-group">
                <label>Nama Pengguna (Username) <small class="text-danger">* Wajib</small></label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($row['username']) ?>" required>
              </div>
              <div class="form-group">
                <label>Kata Sandi (Password) <small class="text-danger">* Wajib</small></label>
                <input type="text" name="password" class="form-control" value="<?= htmlspecialchars($row['password']) ?>" required>
              </div>
              <div class="form-group">
                <label>Kelas <small class="text-danger">* Wajib</small></label>
                <select name="kelas" class="form-control" required>
                  <option value="VII" <?= $row['kelas'] === 'VII' ? 'selected' : '' ?>>VII</option>
                  <option value="VIII" <?= $row['kelas'] === 'VIII' ? 'selected' : '' ?>>VIII</option>
                  <option value="IX" <?= $row['kelas'] === 'IX' ? 'selected' : '' ?>>IX</option>
                </select>
              </div>
              <div class="form-group">
                <label>Alamat Domisili <small class="text-danger">* Wajib</small></label>
                <textarea name="alamat" class="form-control" rows="2" required><?= htmlspecialchars($row['alamat']) ?></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary font-weight-bold">Simpan Perubahan</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  <?php endforeach; ?>
<?php endif; ?>

<!-- ============================================================== -->
<!-- MODAL TAMBAH ANGGOTA SISWA BARU                                -->
<!-- ============================================================== -->
<div class="modal fade" id="modalTambahAnggota" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title font-weight-bold">Tambah Anggota Siswa Baru</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?= $base ?>admin/anggota/add" method="post">
        <div class="modal-body">
          <div class="form-group">
            <label>Kode Anggota <small class="text-danger">* Otomatis</small></label>
            <input type="text" name="kode_user" class="form-control bg-light" value="<?= htmlspecialchars($autoKode) ?>" readonly>
          </div>
          <div class="form-group">
            <label>Nomor Induk Siswa (NIS) <small class="text-danger">* Wajib</small></label>
            <input type="text" name="nis" class="form-control" placeholder="Contoh: 1230069" required>
          </div>
          <div class="form-group">
            <label>Nama Lengkap Siswa <small class="text-danger">* Wajib</small></label>
            <input type="text" name="fullname" class="form-control" placeholder="Masukkan Nama Lengkap" required>
          </div>
          <div class="form-group">
            <label>Nomor WhatsApp Siswa (Untuk Kirim Struk) <small class="text-danger">* Wajib</small></label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fab fa-whatsapp text-success"></i></span>
              </div>
              <input type="text" name="no_whatsapp" class="form-control" value="087767499895" required>
            </div>
            <small class="text-muted">Nomor default siswa diseragamkan ke: 087767499895</small>
          </div>
          <div class="form-group">
            <label>Nama Pengguna (Username) <small class="text-danger">* Wajib</small></label>
            <input type="text" name="username" class="form-control" placeholder="Masukkan Username Login" required>
          </div>
          <div class="form-group">
            <label>Kata Sandi (Password) <small class="text-danger">* Wajib</small></label>
            <input type="password" name="password" class="form-control" placeholder="Masukkan Password" required>
          </div>
          <div class="form-group">
            <label>Kelas <small class="text-danger">* Wajib</small></label>
            <select name="kelas" class="form-control" required>
              <option value="">-- Harap Pilih Kelas --</option>
              <option value="VII">VII</option>
              <option value="VIII">VIII</option>
              <option value="IX">IX</option>
            </select>
          </div>
          <div class="form-group">
            <label>Alamat Domisili <small class="text-danger">* Wajib</small></label>
            <textarea name="alamat" class="form-control" rows="2" placeholder="Masukkan Alamat Lengkap" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-primary px-4 font-weight-bold">Simpan Anggota</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
  // 1. Inisialisasi Grafik Peminjaman dalam Sebulan
  const dailyLoansData = <?= json_encode($chartDailyLoans ?? []) ?>;
  const ctxLoans = document.getElementById('chartAnggotaLoansCanvas');
  if (ctxLoans && dailyLoansData.days) {
    new Chart(ctxLoans.getContext('2d'), {
      type: 'bar',
      data: {
        labels: dailyLoansData.days,
        datasets: [{
          label: 'Jumlah Buku Dipinjam',
          data: dailyLoansData.counts || [],
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
              label: (ctx) => ` Peminjaman: ${ctx.raw} buku fisik`
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

  // 2. Inisialisasi Grafik Pengembalian dalam Sebulan
  const dailyReturnsData = <?= json_encode($chartDailyReturns ?? []) ?>;
  const ctxReturns = document.getElementById('chartAnggotaReturnsCanvas');
  if (ctxReturns && dailyReturnsData.days) {
    new Chart(ctxReturns.getContext('2d'), {
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

  // 3. Pencarian Live Tabel Anggota
  const searchInput = document.getElementById('tableSearch');
  if (searchInput) {
    ['input', 'keyup', 'paste', 'change'].forEach(function(evt) {
      searchInput.addEventListener(evt, function () {
        const filter = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#dataTable tbody tr');
        rows.forEach(function (row) {
          if (row.querySelector('td[colspan]')) return;
          const text = row.textContent.toLowerCase();
          row.style.display = (filter === '' || text.indexOf(filter) > -1) ? '' : 'none';
        });
      });
    });
  }
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
