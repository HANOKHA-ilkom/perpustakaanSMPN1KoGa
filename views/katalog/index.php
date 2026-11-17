<?php
// Katalog Publik (OPAC) View
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$pageTitle = 'Katalog Publik (OPAC) - Perpustakaan SMP Negeri 1 Kota Gajah';
$base = (new Controller())->baseUrl();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- Theme style: AdminLTE 3 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

  <style>
    body {
      background-color: #f4f6f9;
      font-family: 'Source Sans Pro', sans-serif;
    }
    .hero-banner {
      background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
      color: #ffffff;
      padding: 45px 0 35px 0;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .book-card {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      overflow: hidden;
    }
    .book-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.12);
      border-color: #cbd5e1;
    }
    .book-cover-container {
      height: 220px;
      background: #f8fafc;
      display: flex;
      align-items: center;
      justify-content: center;
      border-top-left-radius: 8px;
      border-top-right-radius: 8px;
      position: relative;
      overflow: hidden;
      border-bottom: 1px solid #edf2f7;
    }
    .book-cover-container img {
      max-height: 200px;
      max-width: 90%;
      object-fit: contain;
      transition: transform 0.25s ease;
      filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
    }
    .book-card:hover .book-cover-container img {
      transform: scale(1.05);
    }
    .category-pill {
      margin: 3px;
      border-radius: 20px;
      padding: 6px 14px;
      font-size: 13px;
      font-weight: 600;
      transition: all 0.2s ease;
    }
    .category-pill.active {
      background-color: #007bff !important;
      color: #ffffff !important;
      border-color: #007bff !important;
    }
  </style>
</head>
<body class="layout-top-nav">
<div class="wrapper">

  <!-- Navbar Publik -->
  <nav class="main-header navbar navbar-expand-md navbar-light bg-white border-bottom shadow-sm">
    <div class="container">
      <a href="<?= $base ?>katalog" class="navbar-brand d-flex align-items-center">
        <img src="<?= $base ?>assets/img/logo.svg" alt="Logo" class="brand-image img-circle elevation-1 mr-2" style="width:36px; height:36px;">
        <span class="brand-text font-weight-bold text-dark text-sm text-md-lg">
          Perpustakaan SMPN 1 Kota Gajah
        </span>
      </a>

      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <?php if (isset($_SESSION['user'])): ?>
            <a href="<?= $base ?>admin/dashboard" class="btn btn-outline-primary btn-sm px-3">
              <i class="fas fa-tachometer-alt mr-1"></i> Dashboard Admin (<?= htmlspecialchars($_SESSION['user']['username']) ?>)
            </a>
          <?php else: ?>
            <a href="<?= $base ?>login" class="btn btn-primary btn-sm px-3 shadow-sm font-weight-bold">
              <i class="fas fa-user-shield mr-1"></i> Login Pustakawan
            </a>
          <?php endif; ?>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Hero Search Banner -->
  <div class="hero-banner">
    <div class="container text-center">
      <h2 class="font-weight-bold mb-2">Katalog Buku Perpustakaan (OPAC)</h2>
      <p class="lead mb-4 text-white-50 text-sm-md">
        Cari ketersediaan buku fisik, pengarang, penerbit, nomor ISBN, dan lokasi rak secara langsung.
      </p>

      <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
          <form action="<?= $base ?>katalog" method="get">
            <?php if (!empty($kategori)): ?>
              <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori) ?>">
            <?php endif; ?>
            <div class="input-group input-group-lg shadow-sm">
              <input type="text" name="q" class="form-control" placeholder="Ketik judul buku, nama pengarang, penerbit, atau nomor ISBN..." value="<?= htmlspecialchars($keyword) ?>" autofocus>
              <div class="input-group-append">
                <button type="submit" class="btn btn-warning px-4 font-weight-bold">
                  <i class="fas fa-search mr-1"></i> Cari Buku
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Kategori Filter Pills -->
      <div class="mt-3">
        <a href="<?= $base ?>katalog<?= !empty($keyword) ? '?q=' . urlencode($keyword) : '' ?>" 
           class="btn btn-sm btn-outline-light category-pill <?= empty($kategori) ? 'active bg-white text-primary' : '' ?>">
          Semua Kategori
        </a>
        <?php foreach ($kategoriList as $kat): ?>
          <?php 
            $isActive = strcasecmp((string)$kat['id_kategori'], $kategori) === 0 || strcasecmp($kat['nama_kategori'], $kategori) === 0;
            $urlKat = $base . 'katalog?kategori=' . urlencode($kat['nama_kategori']) . (!empty($keyword) ? '&q=' . urlencode($keyword) : '');
          ?>
          <a href="<?= $urlKat ?>" class="btn btn-sm btn-outline-light category-pill <?= $isActive ? 'active bg-white text-primary' : '' ?>">
            <?= htmlspecialchars($kat['nama_kategori']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Content Section -->
  <div class="content py-4">
    <div class="container">

      <!-- Status Hasil Pencarian -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h5 class="font-weight-bold mb-0 text-dark">
            <i class="fas fa-book-open text-primary mr-1"></i> Daftar Koleksi Buku
          </h5>
          <small class="text-muted">
            Menampilkan <?= count($bukuList) ?> dari total <?= $totalBuku ?> judul buku terdaftar
            <?php if (!empty($keyword)): ?>
              untuk kata kunci: "<strong><?= htmlspecialchars($keyword) ?></strong>"
            <?php endif; ?>
            <?php if (!empty($kategori)): ?>
              pada kategori: <span class="badge badge-info"><?= htmlspecialchars($kategori) ?></span>
            <?php endif; ?>
          </small>
        </div>
        <?php if (!empty($keyword) || !empty($kategori)): ?>
          <a href="<?= $base ?>katalog" class="btn btn-xs btn-outline-secondary">
            <i class="fas fa-sync-alt mr-1"></i> Reset Filter
          </a>
        <?php endif; ?>
      </div>

      <!-- Grid Koleksi Buku -->
      <?php if (empty($bukuList)): ?>
        <div class="card shadow-sm py-5 text-center">
          <div class="card-body">
            <i class="fas fa-search text-muted fa-4x mb-3"></i>
            <h5 class="font-weight-bold text-secondary">Buku Tidak Ditemukan</h5>
            <p class="text-muted">Tidak ada buku yang sesuai dengan kriteria pencarian Anda. Coba gunakan kata kunci lain.</p>
            <a href="<?= $base ?>katalog" class="btn btn-primary btn-sm px-4">
              <i class="fas fa-arrow-left mr-1"></i> Lihat Semua Koleksi
            </a>
          </div>
        </div>
      <?php else: ?>
        <div class="row">
          <?php foreach ($bukuList as $buku): ?>
            <?php
              $coverUrl = PerpusModel::resolveCoverUrl($buku, $base);
              $stokBaik = (int)($buku['j_buku_baik'] ?? 0);
              $stokRusak = (int)($buku['j_buku_rusak'] ?? 0);
              $stokHilang = (int)($buku['j_buku_hilang'] ?? 0);
              $hargaBuku = (float)($buku['harga'] ?? 65000);
              $isAvailable = $stokBaik > 0;
            ?>
            <div class="col-lg-4 col-md-6 mb-4 katalog-book-col">
              <div class="card book-card shadow-sm h-100">
                <!-- Gambar Sampul Buku, Badge Kategori & Harga -->
                <div class="book-cover-container position-relative">
                  <img src="<?= $coverUrl ?>" alt="Cover <?= htmlspecialchars($buku['judul_buku']) ?>" loading="lazy">
                  <!-- Badge Kategori -->
                  <span class="position-absolute badge badge-primary px-2 py-1 shadow-sm font-weight-bold" style="top: 10px; right: 10px; font-size: 11px;">
                    <i class="fas fa-bookmark mr-1"></i><?= htmlspecialchars($buku['kategori_buku'] ?? 'Umum') ?>
                  </span>
                  <!-- Badge Harga Buku -->
                  <span class="position-absolute badge badge-warning text-dark font-weight-bold px-2 py-1 shadow-sm" style="top: 10px; left: 10px; font-size: 11px;">
                    <i class="fas fa-tag mr-1"></i>Rp <?= number_format($hargaBuku, 0, ',', '.') ?>
                  </span>
                </div>

                <div class="card-body d-flex flex-column justify-content-between p-3">
                  <div>
                    <h5 class="card-title font-weight-bold text-dark mb-1" style="font-size: 16px; min-height: 42px;">
                      <?= htmlspecialchars($buku['judul_buku']) ?>
                    </h5>
                    <p class="card-text text-muted text-xs mb-1">
                      <i class="fas fa-user-edit mr-1 text-primary"></i> <?= htmlspecialchars($buku['pengarang'] ?: '-') ?>
                    </p>
                    <p class="card-text text-muted text-xs mb-2">
                      <i class="fas fa-barcode mr-1 text-info"></i> ISBN: <code class="font-weight-bold"><?= htmlspecialchars($buku['isbn'] ?: '-') ?></code>
                    </p>

                    <!-- Harga Buku -->
                    <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded border mb-2 text-xs">
                      <span class="text-muted"><i class="fas fa-coins mr-1 text-warning"></i>Harga Buku:</span>
                      <strong class="text-primary font-weight-bold" style="font-size: 0.95rem;">Rp <?= number_format($hargaBuku, 0, ',', '.') ?></strong>
                    </div>

                    <?php if (!empty($buku['sinopsis'])): ?>
                      <p class="card-text text-secondary text-xs mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4; height: 32px;" title="<?= htmlspecialchars($buku['sinopsis']) ?>">
                        <?= htmlspecialchars($buku['sinopsis']) ?>
                      </p>
                    <?php else: ?>
                      <p class="card-text text-muted text-xs mb-3 font-italic" style="height: 32px;">
                        Belum ada sinopsis untuk buku ini.
                      </p>
                    <?php endif; ?>
                  </div>

                  <div class="border-top pt-2">
                    <!-- Stok Rusak dan Baik -->
                    <div class="d-flex align-items-center justify-content-between mb-2">
                      <span class="badge badge-success px-2 py-1 shadow-xs" title="Stok Kondisi Baik (Siap Dipinjam)">
                        <i class="fas fa-check-circle mr-1"></i> Baik: <strong><?= $stokBaik ?> Eks</strong>
                      </span>
                      <span class="badge badge-warning text-dark px-2 py-1 shadow-xs" title="Stok Kondisi Rusak">
                        <i class="fas fa-tools mr-1"></i> Rusak: <strong><?= $stokRusak ?> Eks</strong>
                      </span>
                      <?php if ($stokHilang > 0): ?>
                        <span class="badge badge-danger px-2 py-1 shadow-xs" title="Stok Hilang">
                          <i class="fas fa-times-circle mr-1"></i> Hilang: <strong><?= $stokHilang ?></strong>
                        </span>
                      <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                      <small class="text-xs text-muted">
                        <?php if ($isAvailable): ?>
                          <span class="text-success font-weight-bold"><i class="fas fa-circle mr-1" style="font-size: 8px;"></i>Bisa Dipinjam</span>
                        <?php else: ?>
                          <span class="text-danger font-weight-bold"><i class="fas fa-circle mr-1" style="font-size: 8px;"></i>Stok Habis</span>
                        <?php endif; ?>
                      </small>
                      <button type="button" class="btn btn-sm btn-outline-primary btn-detail" 
                              data-id="<?= $buku['id_buku'] ?>"
                              data-judul="<?= htmlspecialchars($buku['judul_buku']) ?>"
                              data-pengarang="<?= htmlspecialchars($buku['pengarang'] ?: '-') ?>"
                              data-penerbit="<?= htmlspecialchars($buku['penerbit_buku'] ?: '-') ?>"
                              data-tahun="<?= htmlspecialchars($buku['tahun_terbit'] ?: '-') ?>"
                              data-kategori="<?= htmlspecialchars($buku['kategori_buku'] ?: '-') ?>"
                              data-isbn="<?= htmlspecialchars($buku['isbn'] ?: '-') ?>"
                              data-cover="<?= $coverUrl ?>"
                              data-harga="Rp <?= number_format($hargaBuku, 0, ',', '.') ?>"
                              data-stokbaik="<?= $stokBaik ?>"
                              data-stokrusak="<?= $stokRusak ?>"
                              data-stokhilang="<?= $stokHilang ?>"
                              data-sinopsis="<?= htmlspecialchars($buku['sinopsis'] ?? '') ?>">
                        <i class="fas fa-info-circle mr-1"></i> Detail
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <!-- Modal Detail Buku -->
  <div class="modal fade" id="modalDetailBuku" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content shadow-lg border-0">
        <div class="modal-header bg-gradient-primary text-white">
          <h5 class="modal-title font-weight-bold">
            <i class="fas fa-book-reader mr-2"></i> Detail Informasi Buku (OPAC)
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-4">
          <div class="row">
            <!-- Cover Buku & Badge -->
            <div class="col-md-4 text-center mb-3 mb-md-0">
              <div class="p-2 border rounded bg-light shadow-xs mb-2">
                <img id="detailCover" src="<?= $base ?>assets/img/cover_placeholder.svg" alt="Cover" style="max-height: 230px; width: 100%; object-fit: contain; border-radius: 6px;">
              </div>
              <div class="mt-2">
                <span class="badge badge-primary px-2 py-1 mb-1 font-weight-bold" id="detailKategori">-</span>
                <span class="badge badge-warning text-dark px-2 py-1 mb-1 font-weight-bold" id="detailHarga">-</span>
              </div>
            </div>

            <!-- Rincian Metadata, Stok & Harga -->
            <div class="col-md-8">
              <h4 class="font-weight-bold text-dark mb-1" id="detailJudul">-</h4>
              <div class="text-xs text-muted mb-3">
                <span id="detailPengarang">-</span> | Th: <span id="detailTahun">-</span>
              </div>

              <table class="table table-sm table-bordered mb-3">
                <tr>
                  <th width="35%" class="bg-light">Penerbit</th>
                  <td id="detailPenerbit">-</td>
                </tr>
                <tr>
                  <th class="bg-light">Nomor ISBN</th>
                  <td id="detailIsbn" class="font-weight-bold">-</td>
                </tr>
                <tr>
                  <th class="bg-light">Kategori Buku</th>
                  <td id="detailKategoriTable" class="font-weight-bold text-primary">-</td>
                </tr>
                <tr>
                  <th class="bg-light">Harga Buku</th>
                  <td id="detailHargaTable" class="font-weight-bold text-success">-</td>
                </tr>
                <tr>
                  <th class="bg-light">Rincian Stok</th>
                  <td id="detailStokMetrics">-</td>
                </tr>
                <tr>
                  <th class="bg-light">Status Peminjaman</th>
                  <td id="detailStok">-</td>
                </tr>
              </table>

              <!-- Sinopsis Buku -->
              <div class="mb-3">
                <h6 class="font-weight-bold text-dark mb-1">
                  <i class="fas fa-align-left text-primary mr-1"></i> Sinopsis &amp; Ringkasan Buku:
                </h6>
                <div class="p-3 bg-light rounded border text-sm text-secondary" id="detailSinopsis" style="line-height: 1.6; text-align: justify; max-height: 150px; overflow-y: auto;">
                  -
                </div>
              </div>
            </div>
          </div>

          <div class="alert alert-info text-xs mb-0 mt-2">
            <i class="fas fa-info-circle mr-1"></i>
            <strong>Informasi Peminjaman:</strong> Untuk meminjam buku fisik ini, siswa/pengunjung silakan langsung mendatangi <strong>Meja Sirkulasi Perpustakaan SMPN 1 Kota Gajah</strong> dan menunjukkan kartu pelajar atau nomor NIS kepada Pustakawan yang bertugas.
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer Publik -->
  <footer class="main-footer text-center text-sm py-3 bg-white border-top">
    <div class="container">
      <strong>Copyright &copy; 2024 <a href="<?= $base ?>katalog"><?= htmlspecialchars($identitas['nama_app']) ?></a>.</strong>
      <div class="text-muted text-xs mt-1">
        <i class="fas fa-map-marker-alt mr-1 text-danger"></i> <?= htmlspecialchars($identitas['alamat_app']) ?>
      </div>
    </div>
  </footer>

</div>

<!-- REQUIRED SCRIPTS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
  $('.btn-detail').on('click', function() {
    var judul = $(this).data('judul');
    var pengarang = $(this).data('pengarang');
    var penerbit = $(this).data('penerbit');
    var tahun = $(this).data('tahun');
    var kategori = $(this).data('kategori');
    var isbn = $(this).data('isbn');
    var cover = $(this).data('cover');
    var harga = $(this).data('harga');
    var stokBaik = parseInt($(this).data('stokbaik')) || 0;
    var stokRusak = parseInt($(this).data('stokrusak')) || 0;
    var stokHilang = parseInt($(this).data('stokhilang')) || 0;
    var sinopsis = $(this).data('sinopsis') || 'Belum ada sinopsis untuk buku ini.';

    $('#detailJudul').text(judul);
    $('#detailPengarang').text('Pengarang: ' + pengarang);
    $('#detailPenerbit').text(penerbit);
    $('#detailTahun').text(tahun);
    $('#detailKategori').text(kategori);
    $('#detailKategoriTable').text(kategori);
    $('#detailIsbn').text(isbn);
    $('#detailHarga').text(harga);
    $('#detailHargaTable').text(harga);
    $('#detailSinopsis').text(sinopsis);
    if (cover) {
      $('#detailCover').attr('src', cover);
    }

    $('#detailStokMetrics').html(
      '<span class="badge badge-success mr-1 shadow-xs"><i class="fas fa-check-circle mr-1"></i>Baik: ' + stokBaik + ' Eks</span>' +
      '<span class="badge badge-warning text-dark mr-1 shadow-xs"><i class="fas fa-tools mr-1"></i>Rusak: ' + stokRusak + ' Eks</span>' +
      (stokHilang > 0 ? '<span class="badge badge-danger shadow-xs"><i class="fas fa-times-circle mr-1"></i>Hilang: ' + stokHilang + ' Eks</span>' : '')
    );

    if (stokBaik > 0) {
      $('#detailStok').html('<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Siap Dipinjam (' + stokBaik + ' eksemplar tersedia)</span>');
    } else {
      $('#detailStok').html('<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i> Stok Habis (Sedang dipinjam / dalam perbaikan)</span>');
    }

    $('#modalDetailBuku').modal('show');
  });

  // Live Search Instan Katalog Publik (OPAC)
  $('input[name="q"]').on('input keyup paste change', function() {
    var query = $(this).val().toLowerCase().trim();
    var matchCount = 0;

    $('.katalog-book-col').each(function() {
      var text = $(this).text().toLowerCase();
      if (query === '' || text.indexOf(query) !== -1) {
        $(this).show();
        matchCount++;
      } else {
        $(this).hide();
      }
    });

    var emptyBox = $('#katalogLiveEmptyAlert');
    if (matchCount === 0 && query !== '') {
      if (emptyBox.length === 0) {
        $('.katalog-book-col').parent().append(
          '<div id="katalogLiveEmptyAlert" class="col-12 py-5 text-center text-muted"><i class="fas fa-search fa-3x mb-3 text-secondary"></i><h5>Koleksi Buku Tidak Ditemukan</h5><p>Tidak ada buku yang cocok dengan kata kunci "<strong>' + $('<div>').text(query).html() + '</strong>".</p></div>'
        );
      } else {
        emptyBox.find('strong').text(query);
        emptyBox.show();
      }
    } else {
      emptyBox.hide();
    }
  });
});
</script>
</body>
</html>

