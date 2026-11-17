<?php
// Data Kategori View
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$pageTitle = 'Kategori Buku - Perpustakaan SMPN 1 Kota Gajah';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/sidebar_admin.php';
$base = (new Controller())->baseUrl();
?>

<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-tags mr-2 text-primary"></i>Kategori Buku
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= $base ?>admin/dashboard">Home</a></li>
            <li class="breadcrumb-item active">Kategori Buku</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">

      <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
          <i class="fas fa-info-circle mr-1"></i> <?= htmlspecialchars($flash['message']) ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <?php endif; ?>

      <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
          <h5 class="card-title font-weight-bold mb-0 text-dark">
            <i class="fas fa-list-ul mr-1 text-primary"></i> Daftar Kategori Buku &amp; Statistik Koleksi
          </h5>
          <button type="button" class="btn btn-primary btn-sm ml-auto font-weight-bold shadow-xs" data-toggle="modal" data-target="#modalTambahKategori">
            <i class="fas fa-plus mr-1"></i> Tambah Kategori
          </button>
        </div>
        <div class="card-body">
          
          <!-- Search box -->
          <div class="row mb-3">
            <div class="col-md-4 ml-auto">
              <div class="input-group input-group-sm">
                <input type="text" id="tableSearch" class="form-control" placeholder="Pencarian kode atau nama kategori...">
                <div class="input-group-append">
                  <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Table -->
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle" id="dataTable">
              <thead class="bg-light">
                <tr>
                  <th style="width: 45px;" class="text-center">No</th>
                  <th style="width: 140px;">Kode Kategori</th>
                  <th>Nama Kategori</th>
                  <th class="text-center" style="width: 130px;">Jumlah Judul</th>
                  <th class="text-center" style="width: 160px;">Total Eksemplar</th>
                  <th class="text-center" style="width: 140px;">Sedang Dipinjam</th>
                  <th style="width: 150px;" class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($kategoriList)): ?>
                  <tr>
                    <td colspan="7" class="text-center text-muted py-4">Belum ada kategori buku yang terdaftar.</td>
                  </tr>
                <?php else: ?>
                  <?php $no = 1; foreach ($kategoriList as $row): 
                    $totalJudul = (int)($row['total_judul'] ?? 0);
                    $totalStok  = (int)($row['total_stok'] ?? 0);
                    $dipinjam   = (int)($row['total_dipinjam'] ?? 0);
                  ?>
                    <tr>
                      <td class="text-center font-weight-bold text-muted"><?= $no++ ?></td>
                      <td>
                        <span class="badge badge-info px-2 py-1 shadow-xs font-weight-bold">
                          <i class="fas fa-tag mr-1"></i> <?= htmlspecialchars($row['kode_kategori']) ?>
                        </span>
                      </td>
                      <td>
                        <strong class="text-dark" style="font-size: 0.95rem;"><?= htmlspecialchars($row['nama_kategori']) ?></strong>
                      </td>
                      <td class="text-center">
                        <span class="badge badge-primary px-2 py-1 font-weight-bold shadow-xs">
                          <i class="fas fa-book mr-1"></i> <?= $totalJudul ?> Judul
                        </span>
                      </td>
                      <td class="text-center">
                        <span class="badge badge-light border text-dark font-weight-bold px-2 py-1">
                          <?= $totalStok ?> Eksemplar
                        </span>
                        <div class="text-xs text-muted mt-1">
                          Baik: <?= (int)($row['total_baik'] ?? 0) ?> | Rsk: <?= (int)($row['total_rusak'] ?? 0) ?> | Hlg: <?= (int)($row['total_hilang'] ?? 0) ?>
                        </div>
                      </td>
                      <td class="text-center">
                        <?php if ($dipinjam > 0): ?>
                          <span class="badge badge-warning text-dark font-weight-bold px-2 py-1 shadow-xs">
                            <i class="fas fa-hand-holding mr-1"></i> <?= $dipinjam ?> Buku
                          </span>
                        <?php else: ?>
                          <span class="badge badge-secondary px-2 py-1">0 Buku</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-center">
                        <!-- Tombol Pop-up Detail Kategori -->
                        <button type="button" class="btn btn-info btn-xs mr-1 shadow-xs font-weight-bold" data-toggle="modal" data-target="#modalDetailKategori<?= $row['id_kategori'] ?>" title="Lihat Rincian Kategori">
                          <i class="fas fa-eye mr-1"></i> Detail
                        </button>

                        <!-- Tombol Edit -->
                        <button type="button" class="btn btn-primary btn-xs mr-1" data-toggle="modal" data-target="#modalEditKategori<?= $row['id_kategori'] ?>" title="Edit Nama Kategori">
                          <i class="fas fa-edit"></i>
                        </button>

                        <!-- Tombol Hapus -->
                        <form action="<?= $base ?>admin/kategori/delete" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori <?= addslashes($row['nama_kategori']) ?>?');">
                          <input type="hidden" name="id_kategori" value="<?= $row['id_kategori'] ?>">
                          <button type="submit" class="btn btn-danger btn-xs" title="Hapus Kategori">
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

    </div>
  </section>
</div>

<!-- ============================================================== -->
<!-- MODAL POP-UP DETAIL KATEGORI BUKU                              -->
<!-- ============================================================== -->
<?php if (!empty($kategoriList)): ?>
  <?php foreach ($kategoriList as $row): 
    $kId = (int)$row['id_kategori'];
    $bukuListKat = $bukuPerKategori[$kId] ?? [];
    $totalJudul = (int)($row['total_judul'] ?? 0);
    $totalStok  = (int)($row['total_stok'] ?? 0);
    $baik       = (int)($row['total_baik'] ?? 0);
    $rusak      = (int)($row['total_rusak'] ?? 0);
    $hilang     = (int)($row['total_hilang'] ?? 0);
    $dipinjam   = (int)($row['total_dipinjam'] ?? 0);
    $riwayat    = (int)($row['total_riwayat_pinjam'] ?? 0);
  ?>
    <div class="modal fade" id="modalDetailKategori<?= $kId ?>" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-primary text-white py-3">
            <h5 class="modal-title font-weight-bold mb-0">
              <i class="fas fa-tag mr-2"></i> Detail Kategori: <?= htmlspecialchars($row['nama_kategori']) ?>
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body p-4">
            
            <!-- HEADER INFO CARD -->
            <div class="card card-outline card-info shadow-xs mb-3 bg-light border">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                  <div>
                    <h5 class="font-weight-bold text-dark mb-1">
                      Kategori <?= htmlspecialchars($row['nama_kategori']) ?>
                    </h5>
                    <span class="badge badge-info px-2 py-1 font-weight-bold">
                      <i class="fas fa-barcode mr-1"></i> Kode: <?= htmlspecialchars($row['kode_kategori']) ?>
                    </span>
                    <span class="badge badge-light border text-muted ml-1">
                      Perpustakaan SMPN 1 Kota Gajah
                    </span>
                  </div>
                  <div class="text-right mt-2 mt-sm-0">
                    <span class="badge badge-success px-3 py-2 font-weight-bold shadow-xs" style="font-size: 0.88rem;">
                      <i class="fas fa-check-circle mr-1"></i> Kategori Aktif
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- METRIC TILES -->
            <div class="row mb-3">
              <div class="col-sm-6 col-md-3 mb-2">
                <div class="card bg-light border p-2 text-center h-100 shadow-xs">
                  <span class="text-secondary text-xs text-uppercase font-weight-bold mb-1">Total Judul</span>
                  <h4 class="font-weight-bold text-primary mb-0"><?= $totalJudul ?></h4>
                  <small class="text-muted">Koleksi Judul</small>
                </div>
              </div>
              <div class="col-sm-6 col-md-3 mb-2">
                <div class="card bg-light border p-2 text-center h-100 shadow-xs">
                  <span class="text-secondary text-xs text-uppercase font-weight-bold mb-1">Total Eksemplar</span>
                  <h4 class="font-weight-bold text-dark mb-0"><?= $totalStok ?></h4>
                  <small class="text-muted">Baik: <?= $baik ?> | Hlg: <?= $hilang ?></small>
                </div>
              </div>
              <div class="col-sm-6 col-md-3 mb-2">
                <div class="card bg-light border p-2 text-center h-100 shadow-xs">
                  <span class="text-secondary text-xs text-uppercase font-weight-bold mb-1">Sedang Dipinjam</span>
                  <h4 class="font-weight-bold text-warning mb-0"><?= $dipinjam ?></h4>
                  <small class="text-muted">Di Tangan Siswa</small>
                </div>
              </div>
              <div class="col-sm-6 col-md-3 mb-2">
                <div class="card bg-light border p-2 text-center h-100 shadow-xs">
                  <span class="text-secondary text-xs text-uppercase font-weight-bold mb-1">Total Sirkulasi</span>
                  <h4 class="font-weight-bold text-success mb-0"><?= $riwayat ?></h4>
                  <small class="text-muted">Riwayat Transaksi</small>
                </div>
              </div>
            </div>

            <!-- DAFTAR BUKU DALAM KATEGORI INI -->
            <div class="card shadow-xs mb-0">
              <div class="card-header bg-white py-2">
                <h6 class="font-weight-bold mb-0 text-dark">
                  <i class="fas fa-book mr-1 text-primary"></i> Daftar Koleksi Buku dalam Kategori Ini
                </h6>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 280px;">
                  <table class="table table-sm table-striped table-hover mb-0">
                    <thead class="bg-light text-xs text-muted sticky-top">
                      <tr>
                        <th style="width: 35px;" class="text-center">No</th>
                        <th>Judul Buku</th>
                        <th>Pengarang</th>
                        <th>Penerbit</th>
                        <th class="text-center" style="width: 80px;">Tahun</th>
                        <th class="text-center" style="width: 90px;">Stok Fisik</th>
                        <th class="text-center" style="width: 90px;">Terpinjam</th>
                      </tr>
                    </thead>
                    <tbody class="text-sm">
                      <?php if (empty($bukuListKat)): ?>
                        <tr>
                          <td colspan="7" class="text-center text-muted py-3">
                            <i class="fas fa-info-circle mr-1"></i> Belum ada buku yang didaftarkan pada kategori ini.
                          </td>
                        </tr>
                      <?php else: ?>
                        <?php $bNo = 1; foreach ($bukuListKat as $bk): ?>
                          <tr>
                            <td class="text-center font-weight-bold text-muted"><?= $bNo++ ?></td>
                            <td>
                              <strong class="text-dark"><?= htmlspecialchars($bk['judul_buku']) ?></strong>
                              <?php if (!empty($bk['isbn'])): ?>
                                <br><small class="text-muted"><code>ISBN: <?= htmlspecialchars($bk['isbn']) ?></code></small>
                              <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($bk['pengarang']) ?></td>
                            <td><span class="badge badge-light border"><?= htmlspecialchars($bk['penerbit_buku']) ?></span></td>
                            <td class="text-center"><?= htmlspecialchars($bk['tahun_terbit'] ?: '-') ?></td>
                            <td class="text-center">
                              <span class="badge badge-light border text-dark font-weight-bold">
                                <?= (int)$bk['total_stok'] ?> Eks
                              </span>
                            </td>
                            <td class="text-center">
                              <span class="badge badge-info px-2 py-1 font-weight-bold">
                                <?= (int)$bk['total_pinjam'] ?>x
                              </span>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

          </div>
          <div class="modal-footer bg-light py-2">
            <button type="button" class="btn btn-secondary btn-sm font-weight-bold" data-dismiss="modal">
              <i class="fas fa-times mr-1"></i> Tutup
            </button>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<!-- ============================================================== -->
<!-- MODAL EDIT KATEGORI                                            -->
<!-- ============================================================== -->
<?php if (!empty($kategoriList)): ?>
  <?php foreach ($kategoriList as $row): ?>
    <div class="modal fade" id="modalEditKategori<?= $row['id_kategori'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title font-weight-bold">Edit Kategori</h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="<?= $base ?>admin/kategori/edit" method="post">
            <input type="hidden" name="id_kategori" value="<?= $row['id_kategori'] ?>">
            <div class="modal-body">
              <div class="form-group mb-3">
                <label class="font-weight-bold">Kode Kategori</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($row['kode_kategori']) ?>" readonly>
              </div>
              <div class="form-group mb-3">
                <label class="font-weight-bold">Nama Kategori <small class="text-danger">* Wajib</small></label>
                <input type="text" name="nama_kategori" class="form-control" value="<?= htmlspecialchars($row['nama_kategori']) ?>" required>
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
<!-- MODAL TAMBAH KATEGORI                                          -->
<!-- ============================================================== -->
<div class="modal fade" id="modalTambahKategori" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-1"></i> Tambah Kategori Buku</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?= $base ?>admin/kategori/add" method="post">
        <div class="modal-body">
          <div class="form-group mb-3">
            <label class="font-weight-bold">Kode Kategori</label>
            <input type="text" name="kode_kategori" class="form-control" value="<?= htmlspecialchars($autoKode) ?>" readonly>
          </div>
          <div class="form-group mb-3">
            <label class="font-weight-bold">Nama Kategori <small class="text-danger">* Wajib diisi</small></label>
            <input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: Novel, Sains, Agama" required autofocus>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary font-weight-bold">Simpan Kategori</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  $('#tableSearch').on('input keyup paste change', function() {
    var val = $(this).val().toLowerCase().trim();
    $('#dataTable tbody tr').each(function() {
      if ($(this).find('td[colspan]').length > 0) return;
      $(this).toggle(val === '' || $(this).text().toLowerCase().indexOf(val) > -1);
    });
  });
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
