<?php
// Laporan Riwayat Total Sirkulasi View
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$pageTitle = 'Laporan Riwayat Total Sirkulasi - Perpustakaan SMPN 1 Kota Gajah';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/sidebar_admin.php';
$base = (new Controller())->baseUrl();

// Query String params for Print URL
$printParams = http_build_query($filters ?? []);
$printUrl = $base . 'admin/riwayat/cetak?' . $printParams;
?>

<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-history text-info mr-2"></i> Laporan Riwayat Total Sirkulasi
          </h1>
          <small class="text-muted">Rekapitulasi lengkap peminjaman, pengembalian, status kondisi buku, dan kas denda.</small>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= $base ?>admin/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= $base ?>admin/peminjaman">Sirkulasi</a></li>
            <li class="breadcrumb-item active">Riwayat Total</li>
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

      <!-- ==================================================== -->
      <!-- FILTER MULTI-KRITERIA                               -->
      <!-- ==================================================== -->
      <div class="card card-outline card-info shadow-sm mb-4">
        <div class="card-header bg-light">
          <h3 class="card-title font-weight-bold text-dark">
            <i class="fas fa-filter text-info mr-2"></i> Filter Laporan Riwayat Sirkulasi
          </h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <form action="<?= $base ?>admin/riwayat" method="get">
            <div class="row">
              <!-- Filter 1: Jenis Transaksi -->
              <div class="col-lg-3 col-md-6 form-group">
                <label class="font-weight-bold text-xs text-muted mb-1">
                  <i class="fas fa-exchange-alt mr-1"></i> 1. Jenis Transaksi
                </label>
                <select name="jenis" class="form-control form-control-sm">
                  <option value="">-- Semua Jenis Transaksi --</option>
                  <option value="pinjam" <?= ($filters['jenis'] ?? '') === 'pinjam' ? 'selected' : '' ?>>Sedang Dipinjam (Belum Kembali)</option>
                  <option value="kembali" <?= ($filters['jenis'] ?? '') === 'kembali' ? 'selected' : '' ?>>Selesai (Sudah Dikembalikan)</option>
                </select>
              </div>

              <!-- Filter 2: Siswa / Peminjam -->
              <div class="col-lg-3 col-md-6 form-group">
                <label class="font-weight-bold text-xs text-muted mb-1">
                  <i class="fas fa-user-graduate mr-1"></i> 2. Siswa / Anggota
                </label>
                <select name="id_user" class="form-control form-control-sm">
                  <option value="">-- Semua Siswa / Anggota --</option>
                  <?php foreach ($siswaList as $s): ?>
                    <option value="<?= $s['id_user'] ?>" <?= (int)($filters['id_user'] ?? 0) === (int)$s['id_user'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($s['nis'] ? $s['nis'] . ' - ' : '') ?><?= htmlspecialchars($s['fullname'] ?: $s['username']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Filter 3: Buku -->
              <div class="col-lg-3 col-md-6 form-group">
                <label class="font-weight-bold text-xs text-muted mb-1">
                  <i class="fas fa-book mr-1"></i> 3. Judul Buku
                </label>
                <select name="id_buku" class="form-control form-control-sm">
                  <option value="">-- Semua Judul Buku --</option>
                  <?php foreach ($bukuList as $b): ?>
                    <option value="<?= $b['id_buku'] ?>" <?= (int)($filters['id_buku'] ?? 0) === (int)$b['id_buku'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($b['judul_buku']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Filter 4: Kondisi Buku -->
              <div class="col-lg-3 col-md-6 form-group">
                <label class="font-weight-bold text-xs text-muted mb-1">
                  <i class="fas fa-check-circle mr-1"></i> 4. Kondisi Fisik Buku
                </label>
                <select name="kondisi" class="form-control form-control-sm">
                  <option value="">-- Semua Kondisi Buku --</option>
                  <option value="Baik" <?= ($filters['kondisi'] ?? '') === 'Baik' ? 'selected' : '' ?>>Kondisi Baik</option>
                  <option value="Rusak" <?= ($filters['kondisi'] ?? '') === 'Rusak' ? 'selected' : '' ?>>Kondisi Rusak / Cacat</option>
                  <option value="Hilang" <?= ($filters['kondisi'] ?? '') === 'Hilang' ? 'selected' : '' ?>>Buku Hilang / Ganti Rugi</option>
                </select>
              </div>
            </div>

            <div class="row align-items-center">
              <!-- Filter 5: Denda -->
              <div class="col-lg-4 col-md-6 form-group mb-lg-0">
                <label class="font-weight-bold text-xs text-muted mb-1">
                  <i class="fas fa-coins mr-1"></i> 5. Status & Jenis Denda
                </label>
                <select name="denda" class="form-control form-control-sm">
                  <option value="">-- Semua Status Denda --</option>
                  <option value="ada" <?= ($filters['denda'] ?? '') === 'ada' ? 'selected' : '' ?>>Ada Denda (Rp > 0)</option>
                  <option value="bebas" <?= ($filters['denda'] ?? '') === 'bebas' ? 'selected' : '' ?>>Bebas Denda / Tidak Ada</option>
                  <option value="terlambat" <?= ($filters['denda'] ?? '') === 'terlambat' ? 'selected' : '' ?>>Denda Keterlambatan Pengembalian</option>
                  <option value="rusak" <?= ($filters['denda'] ?? '') === 'rusak' ? 'selected' : '' ?>>Denda Kerusakan Buku Fisik</option>
                  <option value="hilang" <?= ($filters['denda'] ?? '') === 'hilang' ? 'selected' : '' ?>>Denda Penggantian Buku Hilang</option>
                </select>
              </div>

              <!-- Tombol Aksi Filter & Cetak -->
              <div class="col-lg-8 col-md-6 text-md-right mt-2 mt-md-0">
                <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm mr-1">
                  <i class="fas fa-filter mr-1"></i> Terapkan Filter
                </button>
                <a href="<?= $base ?>admin/riwayat" class="btn btn-outline-secondary btn-sm px-3 mr-2">
                  <i class="fas fa-undo-alt mr-1"></i> Reset
                </a>
                <a href="<?= $printUrl ?>" target="_blank" class="btn btn-success btn-sm px-3 font-weight-bold shadow-sm">
                  <i class="fas fa-print mr-1"></i> Cetak Laporan Total (PDF)
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- ==================================================== -->
      <!-- RINGKASAN REKAPITULASI STATISTIK HASIL FILTER       -->
      <!-- ==================================================== -->
      <div class="row mb-3">
        <!-- 1. Total Transaksi -->
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info shadow-sm mb-3">
            <div class="inner">
              <h3><?= (int)($summary['total_transaksi'] ?? 0) ?></h3>
              <p class="mb-0">Total Transaksi</p>
              <small class="text-white-50">Hasil filter sirkulasi</small>
            </div>
            <div class="icon">
              <i class="fas fa-list-alt"></i>
            </div>
          </div>
        </div>

        <!-- 2. Terpinjam vs Kembali -->
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning text-white shadow-sm mb-3">
            <div class="inner">
              <h3><?= (int)($summary['total_pinjam'] ?? 0) ?> <span style="font-size: 1.1rem; font-weight: normal;">/ <?= (int)($summary['total_kembali'] ?? 0) ?> kembali</span></h3>
              <p class="mb-0 font-weight-bold">Sedang Dipinjam</p>
              <small class="text-white-50"><?= (int)($summary['total_kembali'] ?? 0) ?> transaksi sudah selesai</small>
            </div>
            <div class="icon">
              <i class="fas fa-book-reader"></i>
            </div>
          </div>
        </div>

        <!-- 3. Total Denda -->
        <div class="col-lg-3 col-6">
          <div class="small-box bg-primary shadow-sm mb-3">
            <div class="inner">
              <h3>Rp <?= number_format($summary['total_denda_rp'] ?? 0, 0, ',', '.') ?></h3>
              <p class="mb-0 font-weight-bold">Total Kas Denda</p>
              <small class="text-white-50">Keterlambatan & kerusakan</small>
            </div>
            <div class="icon">
              <i class="fas fa-coins"></i>
            </div>
          </div>
        </div>

        <!-- 4. Rusak & Hilang -->
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger shadow-sm mb-3">
            <div class="inner">
              <h3><?= (int)($summary['total_rusak'] ?? 0) ?> <span style="font-size: 1.1rem; font-weight: normal;">rusak / <?= (int)($summary['total_hilang'] ?? 0) ?> hilang</span></h3>
              <p class="mb-0 font-weight-bold">Buku Rusak / Hilang</p>
              <small class="text-white-50">Catatan kondisi pengembalian</small>
            </div>
            <div class="icon">
              <i class="fas fa-exclamation-triangle"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- ==================================================== -->
      <!-- TABEL DATA RIWAYAT TOTAL SIRKULASI                   -->
      <!-- ==================================================== -->
      <div class="card card-outline card-secondary shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center flex-wrap">
          <h3 class="card-title font-weight-bold text-dark">
            <i class="fas fa-table mr-2 text-secondary"></i> Data Histori Lengkap Sirkulasi Perpustakaan
          </h3>
          <div class="card-tools d-flex align-items-center">
            <div class="input-group input-group-sm mr-2" style="width: 220px;">
              <input type="text" id="tableSearch" class="form-control" placeholder="Cari nama, judul, denda...">
              <div class="input-group-append">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
              </div>
            </div>
            <a href="<?= $printUrl ?>" target="_blank" class="btn btn-outline-success btn-sm font-weight-bold" title="Cetak Riwayat">
              <i class="fas fa-print mr-1"></i> Cetak
            </a>
          </div>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover table-striped mb-0 text-sm" id="dataTable">
            <thead class="bg-light">
              <tr>
                <th style="width: 40px;">No</th>
                <th>Peminjam (Siswa)</th>
                <th>Buku & Koleksi</th>
                <th>Tgl Pinjam</th>
                <th>Tgl Kembali</th>
                <th class="text-center">Status</th>
                <th>Kondisi Pinjam</th>
                <th>Kondisi Kembali</th>
                <th>Denda</th>
                <th style="width: 130px;" class="text-center">Aksi / Cetak</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($riwayatList)): ?>
                <tr>
                  <td colspan="10" class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-3x mb-3 text-secondary d-block"></i>
                    Tidak ada data riwayat transaksi yang cocok dengan kriteria filter.
                  </td>
                </tr>
              <?php else: ?>
                <?php $no = 1; foreach ($riwayatList as $row): 
                  $isReturned = (!empty($row['tanggal_pengembalian']) && $row['tanggal_pengembalian'] !== '0000-00-00' && ($row['status_transaksi'] === 'Selesai' || !empty($row['tanggal_pengembalian'])));
                  $status = $row['status_transaksi'] ?? '';
                  if (empty($status)) {
                    $status = $isReturned ? 'Selesai' : 'Sedang Dipinjam';
                  }
                ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td>
                      <strong><?= htmlspecialchars($row['fullname'] ?: $row['nama_anggota']) ?></strong>
                      <div class="text-xs text-muted">
                        NIS: <?= htmlspecialchars($row['nis'] ?: '-') ?> | Kelas: <?= htmlspecialchars($row['kelas'] ?: '-') ?>
                      </div>
                    </td>
                    <td>
                      <strong><?= htmlspecialchars($row['judul_buku']) ?></strong>
                      <div class="text-xs text-muted">
                        ISBN: <?= htmlspecialchars($row['isbn'] ?: '-') ?> | <?= htmlspecialchars($row['kategori_buku'] ?? '-') ?>
                      </div>
                    </td>
                    <td><span class="badge badge-light border"><?= htmlspecialchars($row['tanggal_peminjaman']) ?></span></td>
                    <td>
                      <?php if (!$isReturned): ?>
                        <span class="text-muted"><i class="fas fa-clock mr-1 text-warning"></i> Belum Kembali</span>
                      <?php else: ?>
                        <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> <?= htmlspecialchars($row['tanggal_pengembalian']) ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <?php if (!$isReturned || $status === 'Sedang Dipinjam'): ?>
                        <span class="badge badge-warning text-dark py-1 px-2 font-weight-bold">
                          <i class="fas fa-book-reader mr-1"></i> Sedang Dipinjam
                        </span>
                      <?php else: ?>
                        <span class="badge badge-success py-1 px-2">
                          <i class="fas fa-check-double mr-1"></i> Selesai
                        </span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="badge badge-secondary"><?= htmlspecialchars($row['kondisi_buku_saat_dipinjam'] ?: 'Baik') ?></span>
                    </td>
                    <td>
                      <?php if (!$isReturned): ?>
                        <span class="text-muted">-</span>
                      <?php elseif ($row['kondisi_buku_saat_dikembalikan'] === 'Baik'): ?>
                        <span class="badge badge-success">Baik</span>
                      <?php elseif ($row['kondisi_buku_saat_dikembalikan'] === 'Rusak'): ?>
                        <span class="badge badge-danger">Rusak</span>
                      <?php elseif ($row['kondisi_buku_saat_dikembalikan'] === 'Hilang'): ?>
                        <span class="badge badge-dark">Hilang</span>
                      <?php else: ?>
                        <span class="badge badge-secondary"><?= htmlspecialchars($row['kondisi_buku_saat_dikembalikan']) ?></span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (empty($row['denda']) || $row['denda'] === 'Tidak ada'): ?>
                        <span class="text-muted text-xs">Tidak ada</span>
                      <?php else: ?>
                        <span class="badge badge-danger font-weight-bold"><?= htmlspecialchars($row['denda']) ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <!-- Tombol Cetak Slip Resmi -->
                      <a href="<?= $base ?>admin/peminjaman/slip?id=<?= $row['id_peminjaman'] ?>" target="_blank" class="btn btn-primary btn-xs font-weight-bold shadow-sm" title="Cetak Slip Bukti">
                        <i class="fas fa-print mr-1"></i> Cetak Slip
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Live Search Cepat Tabel Riwayat
  $('#tableSearch').on('input keyup paste change', function () {
    var query = $(this).val().toLowerCase().trim();
    var matchCount = 0;

    $('#dataTable tbody tr').each(function () {
      if ($(this).attr('id') === 'rowNoSearchResultRiwayat') return;
      if ($(this).find('td[colspan]').length > 0 && $(this).attr('id') !== 'rowNoSearchResultRiwayat') return;

      var rowText = $(this).text().toLowerCase();
      if (query === '' || rowText.indexOf(query) !== -1) {
        $(this).show();
        matchCount++;
      } else {
        $(this).hide();
      }
    });

    var emptyRow = $('#rowNoSearchResultRiwayat');
    if (matchCount === 0 && query !== '') {
      if (emptyRow.length === 0) {
        $('#dataTable tbody').append(
          '<tr id="rowNoSearchResultRiwayat"><td colspan="10" class="text-center py-4 text-muted"><i class="fas fa-search mr-2"></i>Tidak ada riwayat sirkulasi yang cocok dengan kata kunci "<strong>' + $('<div>').text(query).html() + '</strong>".</td></tr>'
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

<?php require_once __DIR__ . '/../templates/footer.php'; ?>

