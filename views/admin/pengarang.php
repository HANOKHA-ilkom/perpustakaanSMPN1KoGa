<?php
// Data Pengarang View (Master Data Pengarang & Analitik Pengarang Terlaris)
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$pageTitle = 'Data Pengarang - Perpustakaan SMPN 1 Kota Gajah';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/sidebar_admin.php';
$base = (new Controller())->baseUrl();

// Hitung total ringkasan untuk metric cards
$sumPengarang = count($pengarangList ?? []);
$sumAktif     = 0;
$sumJudul     = 0;
$sumStok      = 0;
$sumPinjam    = 0;

foreach ($pengarangList as $p) {
    if (($p['status'] ?? 'Aktif') === 'Aktif') {
        $sumAktif++;
    }
    $sumJudul  += (int)($p['total_judul'] ?? 0);
    $sumStok   += (int)($p['total_stok_tersedia'] ?? 0);
    $sumPinjam += (int)($p['total_terpinjam'] ?? 0);
}

// Data untuk Chart Pengarang Terlaris
$chartLabels = [];
$chartLoans  = [];
$chartTitles = [];
$chartStock  = [];
$topAuthorName = '-';
$topAuthorLoans = 0;
$topAuthorTitles = 0;

if (!empty($topLaris)) {
    $first = $topLaris[0];
    $topAuthorName   = $first['nama_pengarang'];
    $topAuthorLoans  = (int)($first['total_dipinjam'] ?? 0);
    $topAuthorTitles = (int)($first['total_judul'] ?? 0);

    foreach ($topLaris as $tl) {
        $chartLabels[] = $tl['nama_pengarang'];
        $chartLoans[]  = (int)($tl['total_dipinjam'] ?? 0);
        $chartTitles[] = (int)($tl['total_judul'] ?? 0);
        $chartStock[]  = (int)($tl['total_stok'] ?? 0);
    }
}
?>

<style>
  /* Ensure all modals display cleanly above sidebar and navbar without clipping */
  .modal {
    z-index: 1060 !important;
  }
  .modal-backdrop {
    z-index: 1055 !important;
  }
  .modal-dialog-scrollable .modal-content {
    max-height: calc(100vh - 3.5rem);
  }
  .avatar-author {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
    box-shadow: 0 2px 6px rgba(102, 126, 234, 0.4);
  }
</style>

<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-feather-alt text-primary mr-2"></i> Data Master Pengarang Buku
          </h1>
          <small class="text-muted">Kelola profil pengarang/penulis, status keaktifan, inventaris buku di perpustakaan, dan statistik buku terlaris.</small>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= $base ?>admin/dashboard">Home</a></li>
            <li class="breadcrumb-item">Master Data</li>
            <li class="breadcrumb-item active">Data Pengarang</li>
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

      <!-- 4 Metric Summary Cards -->
      <div class="row mb-3">
        <div class="col-lg-3 col-6 mb-2">
          <div class="small-box bg-white border shadow-sm p-3 mb-0">
            <div class="inner">
              <span class="text-xs text-muted font-weight-bold text-uppercase d-block">Total Pengarang</span>
              <h3 class="font-weight-bold text-primary mb-0"><?= $sumPengarang ?></h3>
            </div>
            <div class="icon text-primary opacity-25">
              <i class="fas fa-feather-alt"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6 mb-2">
          <div class="small-box bg-white border shadow-sm p-3 mb-0">
            <div class="inner">
              <span class="text-xs text-muted font-weight-bold text-uppercase d-block">Pengarang Aktif</span>
              <h3 class="font-weight-bold text-success mb-0"><?= $sumAktif ?> <small style="font-size: 0.85rem;" class="text-muted">/ <?= $sumPengarang ?></small></h3>
            </div>
            <div class="icon text-success opacity-25">
              <i class="fas fa-user-check"></i>
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
            <i class="fas fa-list text-primary mr-2"></i> Daftar Pengarang / Penulis Buku
          </h5>
          <div class="card-tools mt-2 mt-md-0 d-flex align-items-center">
            <div class="input-group input-group-sm mr-2" style="width: 240px;">
              <input type="text" id="tableSearch" class="form-control" placeholder="Cari nama atau ID pengarang...">
              <div class="input-group-append">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
              </div>
            </div>
            <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalTambahPengarang">
              <i class="fas fa-plus mr-1"></i> Tambah Pengarang
            </button>
          </div>
        </div>
        
        <div class="card-body table-responsive p-0">
          <table class="table table-hover table-striped mb-0 text-sm" id="dataTable">
            <thead class="bg-light">
              <tr>
                <th style="width: 45px;" class="text-center align-middle">No</th>
                <th style="width: 100px;" class="text-center align-middle">ID Pengarang</th>
                <th class="align-middle">Nama Pengarang</th>
                <th style="width: 130px;" class="text-center align-middle">Status</th>
                <th class="text-center align-middle" style="width: 170px;">Jumlah Buku di Perpus</th>
                <th class="text-center align-middle" style="width: 180px;">Stok Buku Tersedia</th>
                <th class="text-center align-middle" style="width: 190px;">Buku Terpinjam</th>
                <th style="width: 150px;" class="text-center align-middle">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($pengarangList)): ?>
                <tr>
                  <td colspan="8" class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-3x mb-2 text-secondary d-block"></i>
                    Belum ada data pengarang buku terdaftar.
                  </td>
                </tr>
              <?php else: ?>
                <?php $no = 1; foreach ($pengarangList as $row): 
                  $tJudul  = (int)($row['total_judul'] ?? 0);
                  $tStok   = (int)($row['total_stok_tersedia'] ?? 0);
                  $tPinjam = (int)($row['total_terpinjam'] ?? 0);
                  $status  = $row['status'] ?? 'Aktif';
                  $initial = mb_substr($row['nama_pengarang'], 0, 1);
                ?>
                  <tr>
                    <td class="text-center font-weight-bold align-middle"><?= $no++ ?></td>
                    <td class="text-center align-middle">
                      <span class="badge badge-light border font-weight-bold text-dark px-2 py-1">
                        <?= htmlspecialchars($row['kode_pengarang']) ?>
                      </span>
                      <small class="d-block text-muted text-xs">#<?= $row['id_pengarang'] ?></small>
                    </td>
                    <td class="align-middle">
                      <div class="d-flex align-items-center">
                        <div class="avatar-author mr-2 flex-shrink-0">
                          <?= htmlspecialchars($initial) ?>
                        </div>
                        <div>
                          <strong class="text-dark d-block" style="font-size: 0.95rem;"><?= htmlspecialchars($row['nama_pengarang']) ?></strong>
                          <?php if (!empty($row['biografi'])): ?>
                            <small class="text-muted d-block text-truncate" style="max-width: 260px;" title="<?= htmlspecialchars($row['biografi']) ?>">
                              <?= htmlspecialchars($row['biografi']) ?>
                            </small>
                          <?php else: ?>
                            <small class="text-muted font-italic text-xs">Belum ada biografi</small>
                          <?php endif; ?>
                        </div>
                      </div>
                    </td>
                    <td class="text-center align-middle">
                      <?php if ($status === 'Aktif'): ?>
                        <span class="badge badge-success px-2 py-1 shadow-xs" style="font-size: 0.82rem;">
                          <i class="fas fa-check-circle mr-1"></i> Aktif
                        </span>
                      <?php else: ?>
                        <span class="badge badge-secondary px-2 py-1 shadow-xs" style="font-size: 0.82rem;">
                          <i class="fas fa-pause-circle mr-1"></i> Tidak Aktif
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center align-middle">
                      <?php if ($tJudul > 0): ?>
                        <span class="badge badge-primary px-2 py-1 font-weight-bold shadow-xs" style="font-size: 0.85rem;">
                          <i class="fas fa-book mr-1"></i> <?= $tJudul ?> Judul
                        </span>
                      <?php else: ?>
                        <span class="badge badge-light border text-muted px-2 py-1 text-xs">
                          Belum ada judul
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center align-middle">
                      <?php if ($tStok > 0): ?>
                        <span class="badge badge-success px-2 py-1 font-weight-bold shadow-xs" style="font-size: 0.85rem;">
                          <i class="fas fa-layer-group mr-1"></i> <?= $tStok ?> Eks Tersedia
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
                          <i class="fas fa-book-reader mr-1"></i> <?= $tPinjam ?> Terpinjam
                        </span>
                      <?php else: ?>
                        <span class="badge badge-light border text-muted px-2 py-1 text-xs">
                          Tidak ada pinjaman
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center align-middle">
                      <!-- Tombol Pop-up Detail Pengarang -->
                      <button type="button" class="btn btn-info btn-xs font-weight-bold shadow-xs mr-1" data-toggle="modal" data-target="#modalDetailPengarang<?= $row['id_pengarang'] ?>" title="Lihat Profil & Daftar Buku">
                        <i class="fas fa-eye mr-1"></i> Detail
                      </button>

                      <!-- Tombol Edit -->
                      <button type="button" class="btn btn-primary btn-xs mr-1" data-toggle="modal" data-target="#modalEditPengarang<?= $row['id_pengarang'] ?>" title="Edit Data Pengarang">
                        <i class="fas fa-edit"></i>
                      </button>

                      <!-- Tombol Hapus -->
                      <form action="<?= $base ?>admin/pengarang/delete" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pengarang <?= addslashes($row['nama_pengarang']) ?>?');">
                        <input type="hidden" name="id_pengarang" value="<?= $row['id_pengarang'] ?>">
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

      <!-- ============================================================== -->
      <!-- GRAFIK PENGARANG DENGAN BUKU PALING LARIS (CHART.JS)          -->
      <!-- ============================================================== -->
      <div class="card card-outline card-success shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap py-3">
          <div>
            <h5 class="card-title font-weight-bold mb-0 text-dark">
              <i class="fas fa-chart-bar text-success mr-2"></i> Peringkat Pengarang dengan Buku Paling Laris / Populer
            </h5>
            <small class="text-muted d-block">Analisis frekuensi peminjaman buku karya pengarang sepanjang sirkulasi perpustakaan.</small>
          </div>
          <div class="card-tools mt-2 mt-md-0">
            <span class="badge badge-success px-3 py-2 shadow-xs" style="font-size: 0.85rem;">
              <i class="fas fa-crown text-warning mr-1"></i> Terfavorit #1: <strong><?= htmlspecialchars($topAuthorName) ?></strong> (<?= $topAuthorLoans ?>x Dipinjam)
            </span>
          </div>
        </div>
        <div class="card-body">
          <div class="row align-items-center mb-3">
            <div class="col-md-8">
              <div style="position: relative; height: 320px; width: 100%;">
                <canvas id="chartPengarangLarisCanvas"></canvas>
              </div>
            </div>
            <div class="col-md-4">
              <div class="p-3 bg-light rounded border">
                <h6 class="font-weight-bold text-dark mb-2">
                  <i class="fas fa-info-circle text-primary mr-1"></i> Keterangan Metrik
                </h6>
                <ul class="list-unstyled text-sm mb-3">
                  <li class="mb-2">
                    <span class="badge badge-success mr-1">&nbsp;</span> <strong>Total Frekuensi Dipinjam:</strong> Jumlah akumulasi peminjaman siswa terhadap buku-buku yang ditulis oleh pengarang tersebut.
                  </li>
                  <li class="mb-2">
                    <span class="badge badge-primary mr-1">&nbsp;</span> <strong>Jumlah Judul Buku:</strong> Ragam koleksi judul buku karangan penulis yang telah diinput di sistem perpustakaan.
                  </li>
                </ul>
                <div class="alert alert-info text-xs mb-0 py-2">
                  <i class="fas fa-lightbulb mr-1"></i> Pengarang dengan frekuensi peminjaman tinggi direkomendasikan untuk penambahan judul atau stok cetak baru.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<!-- ============================================================== -->
<!-- POP-UP MODALS DETAIL & EDIT PER PENGARANG                      -->
<!-- ============================================================== -->
<?php if (!empty($pengarangList)): ?>
  <?php foreach ($pengarangList as $row): 
    $tJudul  = (int)($row['total_judul'] ?? 0);
    $tStok   = (int)($row['total_stok_tersedia'] ?? 0);
    $tPinjam = (int)($row['total_terpinjam'] ?? 0);
    $tRiwayat = (int)($row['total_riwayat_pinjam'] ?? 0);
    $status  = $row['status'] ?? 'Aktif';
    $bukuCol = $row['buku_list'] ?? [];
  ?>
    <!-- Modal Pop-up Detail Pengarang -->
    <div class="modal fade" id="modalDetailPengarang<?= $row['id_pengarang'] ?>" tabindex="-1" role="dialog" aria-labelledby="detailLabel<?= $row['id_pengarang'] ?>" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-primary text-white py-3">
            <h5 class="modal-title font-weight-bold" id="detailLabel<?= $row['id_pengarang'] ?>">
              <i class="fas fa-user-edit mr-2"></i> Detail Pengarang: <?= htmlspecialchars($row['nama_pengarang']) ?>
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body p-4">
            
            <!-- Profil Singkat Pengarang -->
            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
              <div class="avatar-author mr-3" style="width:56px; height:56px; font-size:1.5rem;">
                <?= htmlspecialchars(mb_substr($row['nama_pengarang'], 0, 1)) ?>
              </div>
              <div class="flex-grow-1">
                <div class="d-flex align-items-center flex-wrap">
                  <h4 class="font-weight-bold text-dark mb-0 mr-2"><?= htmlspecialchars($row['nama_pengarang']) ?></h4>
                  <span class="badge badge-light border text-dark font-weight-bold mr-2">
                    Kode: <?= htmlspecialchars($row['kode_pengarang']) ?>
                  </span>
                  <?php if ($status === 'Aktif'): ?>
                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Status Aktif</span>
                  <?php else: ?>
                    <span class="badge badge-secondary"><i class="fas fa-pause-circle mr-1"></i> Tidak Aktif</span>
                  <?php endif; ?>
                </div>
                <small class="text-muted d-block mt-1">ID Sistem: #<?= $row['id_pengarang'] ?></small>
              </div>
            </div>

            <!-- Biografi Pengarang -->
            <div class="mb-4">
              <label class="text-xs text-muted text-uppercase font-weight-bold d-block mb-1">
                <i class="fas fa-align-left mr-1"></i> Biografi & Profil Singkat
              </label>
              <div class="p-3 bg-light rounded border text-sm text-dark" style="line-height: 1.6;">
                <?php if (!empty($row['biografi'])): ?>
                  <?= nl2br(htmlspecialchars($row['biografi'])) ?>
                <?php else: ?>
                  <span class="text-muted font-italic">Belum ada keterangan biografi yang dicatat untuk pengarang ini.</span>
                <?php endif; ?>
              </div>
            </div>

            <!-- 4 Statistik Badge -->
            <div class="row mb-4">
              <div class="col-sm-3 col-6 mb-2">
                <div class="p-2 border rounded bg-white text-center shadow-xs">
                  <small class="text-muted d-block text-xs text-uppercase font-weight-bold">Judul Buku</small>
                  <strong class="text-primary h5 mb-0 font-weight-bold"><?= $tJudul ?></strong>
                  <small class="text-muted d-block text-xs">Judul Terdaftar</small>
                </div>
              </div>
              <div class="col-sm-3 col-6 mb-2">
                <div class="p-2 border rounded bg-white text-center shadow-xs">
                  <small class="text-muted d-block text-xs text-uppercase font-weight-bold">Stok Siap Pinjam</small>
                  <strong class="text-success h5 mb-0 font-weight-bold"><?= $tStok ?></strong>
                  <small class="text-muted d-block text-xs">Kondisi Baik</small>
                </div>
              </div>
              <div class="col-sm-3 col-6 mb-2">
                <div class="p-2 border rounded bg-white text-center shadow-xs">
                  <small class="text-muted d-block text-xs text-uppercase font-weight-bold">Sedang Terpinjam</small>
                  <strong class="text-warning h5 mb-0 font-weight-bold"><?= $tPinjam ?></strong>
                  <small class="text-muted d-block text-xs">Di Tangan Siswa</small>
                </div>
              </div>
              <div class="col-sm-3 col-6 mb-2">
                <div class="p-2 border rounded bg-white text-center shadow-xs">
                  <small class="text-muted d-block text-xs text-uppercase font-weight-bold">Frekuensi Sirkulasi</small>
                  <strong class="text-info h5 mb-0 font-weight-bold"><?= $tRiwayat ?>x</strong>
                  <small class="text-muted d-block text-xs">Total Riwayat Pinjam</small>
                </div>
              </div>
            </div>

            <!-- Daftar Buku Karya Pengarang Ini -->
            <h6 class="font-weight-bold text-dark mb-2">
              <i class="fas fa-book-open text-primary mr-1"></i> Koleksi Buku Karya Pengarang Ini di Perpustakaan (<?= count($bukuCol) ?> Judul)
            </h6>
            
            <?php if (empty($bukuCol)): ?>
              <div class="alert alert-light border text-center py-4 mb-0">
                <i class="fas fa-book fa-2x text-muted mb-2 d-block"></i>
                <span class="text-muted">Belum ada judul buku yang terdaftar atas nama pengarang ini di sistem.</span>
              </div>
            <?php else: ?>
              <div class="table-responsive border rounded" style="max-height: 280px;">
                <table class="table table-sm table-hover mb-0 text-xs">
                  <thead class="bg-light text-dark font-weight-bold">
                    <tr>
                      <th style="width: 30px;" class="text-center align-middle">No</th>
                      <th class="align-middle">Judul Buku</th>
                      <th class="align-middle">Kategori & Penerbit</th>
                      <th style="width: 100px;" class="text-center align-middle">Tahun / ISBN</th>
                      <th style="width: 90px;" class="text-center align-middle">Stok Fisik</th>
                      <th style="width: 110px;" class="text-center align-middle">Status Sirkulasi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $bNo = 1; foreach ($bukuCol as $b): 
                      $sedangPinjam = (int)($b['sedang_dipinjam'] ?? 0);
                      $kaliPinjam   = (int)($b['kali_dipinjam'] ?? 0);
                    ?>
                      <tr>
                        <td class="text-center font-weight-bold align-middle"><?= $bNo++ ?></td>
                        <td class="align-middle font-weight-bold text-dark">
                          <?= htmlspecialchars($b['judul_buku']) ?>
                        </td>
                        <td class="align-middle">
                          <span class="badge badge-light border"><?= htmlspecialchars($b['kategori_buku'] ?? '-') ?></span>
                          <span class="badge badge-light border"><?= htmlspecialchars($b['penerbit_buku'] ?? '-') ?></span>
                        </td>
                        <td class="text-center align-middle text-muted">
                          <?= htmlspecialchars($b['tahun_terbit'] ?? '-') ?><br>
                          <small><?= htmlspecialchars($b['isbn'] ?? '-') ?></small>
                        </td>
                        <td class="text-center align-middle">
                          <span class="badge badge-success" title="Baik"><?= (int)$b['j_buku_baik'] ?> Baik</span>
                          <?php if ((int)$b['j_buku_rusak'] > 0): ?>
                            <span class="badge badge-warning text-dark" title="Rusak"><?= (int)$b['j_buku_rusak'] ?> R</span>
                          <?php endif; ?>
                          <?php if ((int)$b['j_buku_hilang'] > 0): ?>
                            <span class="badge badge-danger" title="Hilang"><?= (int)$b['j_buku_hilang'] ?> H</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-center align-middle">
                          <?php if ($sedangPinjam > 0): ?>
                            <span class="badge badge-warning text-dark font-weight-bold">
                              <i class="fas fa-clock mr-1"></i> <?= $sedangPinjam ?> Dipinjam
                            </span>
                          <?php else: ?>
                            <span class="badge badge-success">
                              <i class="fas fa-check mr-1"></i> Tersedia
                            </span>
                          <?php endif; ?>
                          <small class="d-block text-muted text-xs"><?= $kaliPinjam ?>x dipinjam</small>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>

          </div>
          <div class="modal-footer bg-light py-2">
            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
              <i class="fas fa-times mr-1"></i> Tutup
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Edit Pengarang -->
    <div class="modal fade" id="modalEditPengarang<?= $row['id_pengarang'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <form action="<?= $base ?>admin/pengarang/edit" method="post" class="modal-content shadow-lg border-0">
          <input type="hidden" name="id_pengarang" value="<?= $row['id_pengarang'] ?>">
          <div class="modal-header bg-primary text-white py-3">
            <h5 class="modal-title font-weight-bold">
              <i class="fas fa-edit mr-2"></i> Edit Data Pengarang
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body p-4">
            <div class="form-group mb-3">
              <label class="font-weight-bold text-sm">Kode Pengarang (Auto/Unik)</label>
              <input type="text" class="form-control bg-light font-weight-bold" value="<?= htmlspecialchars($row['kode_pengarang']) ?>" readonly>
            </div>
            <div class="form-group mb-3">
              <label class="font-weight-bold text-sm">Nama Pengarang <span class="text-danger">*</span></label>
              <input type="text" name="nama_pengarang" class="form-control" value="<?= htmlspecialchars($row['nama_pengarang']) ?>" required placeholder="Contoh: Andrea Hirata">
            </div>
            <div class="form-group mb-3">
              <label class="font-weight-bold text-sm">Status Keaktifan</label>
              <select name="status" class="form-control">
                <option value="Aktif" <?= ($status === 'Aktif') ? 'selected' : '' ?>>Aktif (Bisa Dipilih & Ditampilkan)</option>
                <option value="Tidak Aktif" <?= ($status === 'Tidak Aktif') ? 'selected' : '' ?>>Tidak Aktif (Nonaktifkan Sementara)</option>
              </select>
            </div>
            <div class="form-group mb-0">
              <label class="font-weight-bold text-sm">Biografi / Profil Singkat</label>
              <textarea name="biografi" class="form-control" rows="3" placeholder="Tuliskan latar belakang, prestasi, atau karya pengarang..."><?= htmlspecialchars($row['biografi'] ?? '') ?></textarea>
            </div>
          </div>
          <div class="modal-footer bg-light py-2">
            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary btn-sm font-weight-bold">
              <i class="fas fa-save mr-1"></i> Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<!-- ============================================================== -->
<!-- MODAL TAMBAH PENGARANG BARU                                   -->
<!-- ============================================================== -->
<div class="modal fade" id="modalTambahPengarang" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form action="<?= $base ?>admin/pengarang/add" method="post" class="modal-content shadow-lg border-0">
      <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title font-weight-bold">
          <i class="fas fa-plus-circle mr-2"></i> Tambah Pengarang Buku Baru
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-4">
        <div class="form-group mb-3">
          <label class="font-weight-bold text-sm">Kode Pengarang (Otomatis)</label>
          <input type="text" name="kode_pengarang" class="form-control bg-light font-weight-bold text-primary" value="<?= htmlspecialchars($autoKode ?? 'AUT001') ?>" readonly>
          <small class="text-muted">Kode ID otomatis digenerate urut oleh sistem perpustakaan.</small>
        </div>
        <div class="form-group mb-3">
          <label class="font-weight-bold text-sm">Nama Lengkap Pengarang <span class="text-danger">*</span></label>
          <input type="text" name="nama_pengarang" class="form-control" required placeholder="Contoh: Andrea Hirata, Tere Liye, Sukino">
        </div>
        <div class="form-group mb-3">
          <label class="font-weight-bold text-sm">Status Pengarang</label>
          <select name="status" class="form-control">
            <option value="Aktif" selected>Aktif</option>
            <option value="Tidak Aktif">Tidak Aktif</option>
          </select>
        </div>
        <div class="form-group mb-0">
          <label class="font-weight-bold text-sm">Biografi / Profil Singkat</label>
          <textarea name="biografi" class="form-control" rows="3" placeholder="Tuliskan latar belakang, asal, penghargaan, atau profil karya pengarang..."></textarea>
        </div>
      </div>
      <div class="modal-footer bg-light py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary btn-sm font-weight-bold">
          <i class="fas fa-save mr-1"></i> Simpan Data Pengarang
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
  // 1. Live Search Filter untuk Tabel Pengarang
  const searchInput = document.getElementById('tableSearch');
  const table = document.getElementById('dataTable');
  if (searchInput && table) {
    ['input', 'keyup', 'paste', 'change'].forEach(function(evt) {
      searchInput.addEventListener(evt, function () {
        const term = this.value.toLowerCase().trim();
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(function (row) {
          if (row.querySelector('td[colspan]')) return;
          const text = row.textContent.toLowerCase();
          row.style.display = (term === '' || text.indexOf(term) > -1) ? '' : 'none';
        });
      });
    });
  }

  // 2. Inisialisasi Chart Pengarang Buku Paling Laris
  const chartLabels = <?= json_encode($chartLabels) ?>;
  const chartLoans  = <?= json_encode($chartLoans) ?>;
  const chartTitles = <?= json_encode($chartTitles) ?>;
  const canvasEl    = document.getElementById('chartPengarangLarisCanvas');

  if (canvasEl && chartLabels.length > 0) {
    new Chart(canvasEl.getContext('2d'), {
      type: 'bar',
      data: {
        labels: chartLabels,
        datasets: [
          {
            label: 'Total Kali Dipinjam (Buku Terlaris)',
            data: chartLoans,
            backgroundColor: 'rgba(40, 167, 69, 0.85)',
            borderColor: '#28a745',
            borderWidth: 1.5,
            borderRadius: 6,
            barPercentage: 0.65
          },
          {
            label: 'Jumlah Koleksi Judul Buku',
            data: chartTitles,
            backgroundColor: 'rgba(0, 123, 255, 0.7)',
            borderColor: '#007bff',
            borderWidth: 1.5,
            borderRadius: 6,
            barPercentage: 0.65
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
            labels: {
              boxWidth: 14,
              font: { size: 12, weight: 'bold' }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(30, 41, 59, 0.95)',
            titleFont: { size: 13, weight: 'bold' },
            bodyFont: { size: 12 },
            padding: 10,
            cornerRadius: 6,
            callbacks: {
              afterLabel: function(context) {
                if (context.datasetIndex === 0) {
                  return 'Frekuensi peminjaman siswa';
                }
                return 'Judul buku terdaftar';
              }
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: {
              font: { size: 11, weight: '600' },
              maxRotation: 25,
              minRotation: 0
            }
          },
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0,
              font: { size: 11 }
            },
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          }
        }
      }
    });
  }
});
</script>

