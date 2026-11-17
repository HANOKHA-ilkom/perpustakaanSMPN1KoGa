<?php
// Data Penerbit View (Enhanced with Book Analytics & Detail Pop-Up)
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$pageTitle = 'Data Penerbit - Perpustakaan SMPN 1 Kota Gajah';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/sidebar_admin.php';
$base = (new Controller())->baseUrl();

// Hitung total ringkasan untuk header cards
$sumPenerbit = count($penerbitList ?? []);
$sumJudul    = 0;
$sumStok     = 0;
$sumPinjam   = 0;
foreach ($penerbitList as $p) {
    $sumJudul  += (int)($p['total_judul'] ?? 0);
    $sumStok   += (int)($p['total_stok_tersedia'] ?? 0);
    $sumPinjam += (int)($p['total_terpinjam'] ?? 0);
}
?>

<style>
  /* Ensure all modals display cleanly above sidebar and navbar */
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
            <i class="fas fa-building text-primary mr-2"></i> Data Master Penerbit Buku
          </h1>
          <small class="text-muted">Manajemen penerbit, pemantauan judul buku, stok fisik tersedia, dan statistik sirkulasi per penerbit.</small>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= $base ?>admin/dashboard">Home</a></li>
            <li class="breadcrumb-item active">Data Penerbit</li>
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
          <?= htmlspecialchars($flash['message']) ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <?php endif; ?>

      <!-- 4 Metric Cards Ringkasan Penerbit -->
      <div class="row mb-3">
        <div class="col-lg-3 col-6 mb-2">
          <div class="small-box bg-white border shadow-sm p-3 mb-0">
            <div class="inner">
              <span class="text-xs text-muted font-weight-bold text-uppercase d-block">Total Penerbit</span>
              <h3 class="font-weight-bold text-primary mb-0"><?= $sumPenerbit ?></h3>
            </div>
            <div class="icon text-primary opacity-25">
              <i class="fas fa-building"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6 mb-2">
          <div class="small-box bg-white border shadow-sm p-3 mb-0">
            <div class="inner">
              <span class="text-xs text-muted font-weight-bold text-uppercase d-block">Total Judul Buku</span>
              <h3 class="font-weight-bold text-info mb-0"><?= $sumJudul ?> <small style="font-size: 0.9rem;">Judul</small></h3>
            </div>
            <div class="icon text-info opacity-25">
              <i class="fas fa-book"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6 mb-2">
          <div class="small-box bg-white border shadow-sm p-3 mb-0">
            <div class="inner">
              <span class="text-xs text-muted font-weight-bold text-uppercase d-block">Stok Buku Tersedia</span>
              <h3 class="font-weight-bold text-success mb-0"><?= $sumStok ?> <small style="font-size: 0.9rem;">Eks</small></h3>
            </div>
            <div class="icon text-success opacity-25">
              <i class="fas fa-layer-group"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6 mb-2">
          <div class="small-box bg-white border shadow-sm p-3 mb-0">
            <div class="inner">
              <span class="text-xs text-muted font-weight-bold text-uppercase d-block">Buku Sedang Terpinjam</span>
              <h3 class="font-weight-bold text-warning mb-0"><?= $sumPinjam ?> <small style="font-size: 0.9rem;">Buku</small></h3>
            </div>
            <div class="icon text-warning opacity-25">
              <i class="fas fa-book-reader"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Table Card -->
      <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap py-3">
          <h5 class="card-title font-weight-bold mb-0 text-dark">
            <i class="fas fa-list text-primary mr-2"></i> Daftar Penerbit Buku Perpustakaan
          </h5>
          <div class="card-tools mt-2 mt-md-0 d-flex align-items-center">
            <div class="input-group input-group-sm mr-2" style="width: 220px;">
              <input type="text" id="tableSearch" class="form-control" placeholder="Cari kode atau nama penerbit...">
              <div class="input-group-append">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
              </div>
            </div>
            <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalTambahPenerbit">
              <i class="fas fa-plus mr-1"></i> Tambah Penerbit
            </button>
          </div>
        </div>
        
        <div class="card-body table-responsive p-0">
          <table class="table table-hover table-striped mb-0 text-sm" id="dataTable">
            <thead class="bg-light">
              <tr>
                <th style="width: 45px;" class="text-center align-middle">No</th>
                <th class="align-middle">Identitas & Nama Penerbit</th>
                <th class="text-center align-middle" style="width: 170px;">Total Judul Buku</th>
                <th class="text-center align-middle" style="width: 190px;">Stok Buku Tersedia</th>
                <th class="text-center align-middle" style="width: 180px;">Buku Terpinjam</th>
                <th style="width: 140px;" class="text-center align-middle">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($penerbitList)): ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-3x mb-2 text-secondary d-block"></i>
                    Belum ada data penerbit buku terdaftar.
                  </td>
                </tr>
              <?php else: ?>
                <?php $no = 1; foreach ($penerbitList as $row): 
                  $tJudul  = (int)($row['total_judul'] ?? 0);
                  $tStok   = (int)($row['total_stok_tersedia'] ?? 0);
                  $tPinjam = (int)($row['total_terpinjam'] ?? 0);
                ?>
                  <tr>
                    <td class="text-center font-weight-bold align-middle"><?= $no++ ?></td>
                    <td class="align-middle">
                      <div class="d-flex align-items-center">
                        <div class="mr-2 text-primary">
                          <i class="fas fa-building fa-lg"></i>
                        </div>
                        <div>
                          <strong class="text-dark d-block"><?= htmlspecialchars($row['nama_penerbit']) ?></strong>
                          <span class="badge badge-light border text-xs text-muted">
                            Kode: <?= htmlspecialchars($row['kode_penerbit']) ?>
                          </span>
                        </div>
                      </div>
                    </td>
                    <td class="text-center align-middle">
                      <?php if ($tJudul > 0): ?>
                        <span class="badge badge-primary px-2 py-1 font-weight-bold shadow-xs" style="font-size: 0.85rem;">
                          <i class="fas fa-book mr-1"></i> <?= $tJudul ?> Judul
                        </span>
                      <?php else: ?>
                        <span class="badge badge-secondary px-2 py-1 font-weight-normal text-xs">
                          Belum ada buku
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center align-middle">
                      <?php if ($tStok > 0): ?>
                        <span class="badge badge-success px-2 py-1 font-weight-bold shadow-xs" style="font-size: 0.85rem;">
                          <i class="fas fa-check-circle mr-1"></i> <?= $tStok ?> Eks Tersedia
                        </span>
                      <?php else: ?>
                        <span class="badge badge-danger px-2 py-1 font-weight-bold text-xs">
                          <i class="fas fa-times-circle mr-1"></i> Stok 0
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center align-middle">
                      <?php if ($tPinjam > 0): ?>
                        <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold shadow-xs" style="font-size: 0.85rem;">
                          <i class="fas fa-book-reader mr-1"></i> <?= $tPinjam ?> Buku Dipinjam
                        </span>
                      <?php else: ?>
                        <span class="badge badge-light border text-muted px-2 py-1 text-xs">
                          Tidak ada pinjaman
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center align-middle">
                      <!-- Tombol Pop-up Detail Penerbit -->
                      <button type="button" class="btn btn-info btn-xs font-weight-bold shadow-xs mr-1" data-toggle="modal" data-target="#modalDetailPenerbit<?= $row['id_penerbit'] ?>" title="Lihat Rincian & Koleksi Buku">
                        <i class="fas fa-eye mr-1"></i> Detail
                      </button>

                      <!-- Tombol Edit -->
                      <button type="button" class="btn btn-primary btn-xs mr-1" data-toggle="modal" data-target="#modalEditPenerbit<?= $row['id_penerbit'] ?>" title="Edit Data Penerbit">
                        <i class="fas fa-edit"></i>
                      </button>

                      <!-- Tombol Hapus -->
                      <form action="<?= $base ?>admin/penerbit/delete" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus penerbit <?= addslashes($row['nama_penerbit']) ?>?');">
                        <input type="hidden" name="id_penerbit" value="<?= $row['id_penerbit'] ?>">
                        <button type="submit" class="btn btn-danger btn-xs" title="Hapus">
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

    </div>
  </section>
</div>

<!-- ============================================================== -->
<!-- POP-UP MODALS DETAIL & EDIT PER PENERBIT                       -->
<!-- ============================================================== -->
<?php if (!empty($penerbitList)): ?>
  <?php foreach ($penerbitList as $row): 
    $tJudul  = (int)($row['total_judul'] ?? 0);
    $tStok   = (int)($row['total_stok_tersedia'] ?? 0);
    $tPinjam = (int)($row['total_terpinjam'] ?? 0);
    $bukuCol = $row['buku_list'] ?? [];
  ?>

    <!-- 1. MODAL DETAIL PENERBIT & KOLEKSI BUKU -->
    <div class="modal fade" id="modalDetailPenerbit<?= $row['id_penerbit'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-primary text-white py-3">
            <h5 class="modal-title font-weight-bold mb-0">
              <i class="fas fa-building mr-2"></i> Rincian Penerbit: <?= htmlspecialchars($row['nama_penerbit']) ?>
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body p-4">

            <!-- 3 KARTU STATISTIK RINGKAS PENERBIT -->
            <div class="row text-center mb-4">
              <div class="col-4">
                <div class="border rounded p-2 bg-light shadow-xs">
                  <span class="text-xs text-muted d-block font-weight-bold text-uppercase">Total Judul Buku</span>
                  <strong class="text-primary font-weight-bold" style="font-size: 1.25rem;">
                    <i class="fas fa-book mr-1"></i> <?= $tJudul ?> Judul
                  </strong>
                </div>
              </div>
              <div class="col-4">
                <div class="border rounded p-2 bg-light shadow-xs">
                  <span class="text-xs text-muted d-block font-weight-bold text-uppercase">Stok Tersedia</span>
                  <strong class="text-success font-weight-bold" style="font-size: 1.25rem;">
                    <i class="fas fa-check-circle mr-1"></i> <?= $tStok ?> Eks
                  </strong>
                </div>
              </div>
              <div class="col-4">
                <div class="border rounded p-2 bg-light shadow-xs">
                  <span class="text-xs text-muted d-block font-weight-bold text-uppercase">Buku Terpinjam</span>
                  <strong class="text-warning font-weight-bold" style="font-size: 1.25rem;">
                    <i class="fas fa-book-reader mr-1"></i> <?= $tPinjam ?> Buku
                  </strong>
                </div>
              </div>
            </div>

            <!-- IDENTITAS PENERBIT -->
            <div class="card card-outline card-light border mb-3">
              <div class="card-body p-3">
                <div class="row text-sm">
                  <div class="col-md-4 mb-2 mb-md-0">
                    <span class="text-muted d-block text-xs">Kode Penerbit:</span>
                    <strong class="text-dark font-weight-bold"><?= htmlspecialchars($row['kode_penerbit']) ?></strong>
                  </div>
                  <div class="col-md-5 mb-2 mb-md-0">
                    <span class="text-muted d-block text-xs">Nama Resmi Penerbit:</span>
                    <strong class="text-primary font-weight-bold"><?= htmlspecialchars($row['nama_penerbit']) ?></strong>
                  </div>
                  <div class="col-md-3">
                    <span class="text-muted d-block text-xs">Status Verifikasi:</span>
                    <span class="badge badge-success px-2 py-1 font-weight-bold">
                      <i class="fas fa-shield-alt mr-1"></i> Terverifikasi
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- TABEL DAFTAR BUKU PENERBIT -->
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="font-weight-bold text-dark mb-0">
                <i class="fas fa-list-ul text-primary mr-1"></i> Daftar Judul Buku yang Diterbitkan:
              </h6>
              <span class="badge badge-primary px-2 py-1">Total: <?= count($bukuCol) ?> Judul Buku</span>
            </div>

            <div class="table-responsive border rounded" style="max-height: 250px; overflow-y: auto;">
              <table class="table table-sm table-striped table-hover mb-0 text-xs">
                <thead class="bg-light sticky-top">
                  <tr>
                    <th style="width: 35px;" class="text-center">No</th>
                    <th>Judul Buku</th>
                    <th style="width: 120px;">Kategori</th>
                    <th style="width: 130px;">Pengarang</th>
                    <th style="width: 100px;">ISBN</th>
                    <th class="text-center" style="width: 80px;">Stok Baik</th>
                    <th class="text-center" style="width: 100px;">Status Pinjam</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($bukuCol)): ?>
                    <tr>
                      <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-info-circle mr-1"></i> Belum ada koleksi buku yang terdaftar dari penerbit ini.
                      </td>
                    </tr>
                  <?php else: ?>
                    <?php $bNo = 1; foreach ($bukuCol as $bk): 
                      $bkDipinjam = (int)($bk['sedang_dipinjam'] ?? 0);
                    ?>
                      <tr>
                        <td class="text-center font-weight-bold text-muted"><?= $bNo++ ?></td>
                        <td>
                          <strong><?= htmlspecialchars($bk['judul_buku']) ?></strong>
                          <small class="text-muted d-block"><?= htmlspecialchars($bk['tahun_terbit'] ?: '-') ?></small>
                        </td>
                        <td><span class="badge badge-light border"><?= htmlspecialchars($bk['kategori_buku'] ?: '-') ?></span></td>
                        <td><?= htmlspecialchars($bk['pengarang'] ?: '-') ?></td>
                        <td><code><?= htmlspecialchars($bk['isbn'] ?: '-') ?></code></td>
                        <td class="text-center font-weight-bold text-success"><?= (int)$bk['j_buku_baik'] ?></td>
                        <td class="text-center">
                          <?php if ($bkDipinjam > 0): ?>
                            <span class="badge badge-warning text-dark font-weight-bold">
                              <i class="fas fa-book-reader mr-1"></i> <?= $bkDipinjam ?> Pinjam
                            </span>
                          <?php else: ?>
                            <span class="badge badge-success font-weight-normal">
                              <i class="fas fa-check mr-1"></i> Tersedia
                            </span>
                          <?php endif; ?>
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

    <!-- 2. MODAL EDIT PENERBIT -->
    <div class="modal fade" id="modalEditPenerbit<?= $row['id_penerbit'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-primary text-white py-3">
            <h5 class="modal-title font-weight-bold mb-0">
              <i class="fas fa-edit mr-2"></i> Edit Data Penerbit
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="<?= $base ?>admin/penerbit/edit" method="post">
            <input type="hidden" name="id_penerbit" value="<?= $row['id_penerbit'] ?>">
            <div class="modal-body p-4">
              <div class="form-group">
                <label>Kode Penerbit</label>
                <input type="text" class="form-control bg-light font-weight-bold" value="<?= htmlspecialchars($row['kode_penerbit']) ?>" readonly>
              </div>
              <div class="form-group">
                <label>Nama Penerbit <small class="text-danger">* Wajib</small></label>
                <input type="text" name="nama_penerbit" class="form-control" value="<?= htmlspecialchars($row['nama_penerbit']) ?>" required>
              </div>
              <input type="hidden" name="verif_penerbit" value="Penerbit Terverifikasi">
            </div>
            <div class="modal-footer bg-light py-2">
              <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-3">Simpan Perubahan</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  <?php endforeach; ?>
<?php endif; ?>

<!-- ============================================================== -->
<!-- MODAL TAMBAH PENERBIT BARU                                     -->
<!-- ============================================================== -->
<div class="modal fade" id="modalTambahPenerbit" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable" role="document">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title font-weight-bold mb-0">
          <i class="fas fa-plus-circle mr-2"></i> Tambah Penerbit Buku Baru
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?= $base ?>admin/penerbit/add" method="post">
        <div class="modal-body p-4">
          <div class="form-group">
            <label>Kode Penerbit <small class="text-danger">* Otomatis Terisi</small></label>
            <input type="text" name="kode_penerbit" class="form-control bg-light font-weight-bold" value="<?= htmlspecialchars($autoKode) ?>" readonly>
          </div>
          <div class="form-group">
            <label>Nama Penerbit <small class="text-danger">* Wajib diisi</small></label>
            <input type="text" name="nama_penerbit" class="form-control" placeholder="Contoh: Erlangga, Gramedia, Balai Pustaka..." required>
          </div>
          <input type="hidden" name="verif_penerbit" value="Penerbit Terverifikasi">
        </div>
        <div class="modal-footer bg-light py-2">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-4">Simpan Penerbit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Live Search Cepat Tabel Penerbit
  $('#tableSearch').on('input keyup paste change', function () {
    var query = $(this).val().toLowerCase().trim();
    var matchCount = 0;

    $('#dataTable tbody tr').each(function () {
      if ($(this).attr('id') === 'rowNoSearchResultPenerbit') return;
      if ($(this).find('td[colspan]').length > 0 && $(this).attr('id') !== 'rowNoSearchResultPenerbit') return;

      var rowText = $(this).text().toLowerCase();
      if (query === '' || rowText.indexOf(query) !== -1) {
        $(this).show();
        matchCount++;
      } else {
        $(this).hide();
      }
    });

    var emptyRow = $('#rowNoSearchResultPenerbit');
    if (matchCount === 0 && query !== '') {
      if (emptyRow.length === 0) {
        $('#dataTable tbody').append(
          '<tr id="rowNoSearchResultPenerbit"><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-search mr-2"></i>Tidak ditemukan penerbit dengan kata kunci "<strong>' + $('<div>').text(query).html() + '</strong>".</td></tr>'
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
