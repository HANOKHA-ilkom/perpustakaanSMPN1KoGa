<?php
// Data Buku View
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$pageTitle = 'Data Buku - Perpustakaan SMPN 1 Kota Gajah';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/sidebar_admin.php';
$base = (new Controller())->baseUrl();
?>

<!-- Barcode Scanning Libraries (ZXing & Perpus Omni-Directional Engine) -->
<script src="https://unpkg.com/@zxing/library@0.21.3/umd/index.min.js"></script>
<script src="<?= $base ?>assets/js/perpus-scanner.js"></script>

<style>
  .modal-dialog-scrollable .modal-content {
    max-height: calc(100vh - 2rem) !important;
  }
  .modal-dialog-scrollable .modal-body {
    overflow-y: auto !important;
    max-height: calc(100vh - 170px) !important;
  }
  .modal-dialog-scrollable .modal-footer {
    position: sticky;
    bottom: 0;
    z-index: 10;
  }
</style>

<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-book mr-2 text-primary"></i> Data Katalog Buku
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= $base ?>admin/dashboard">Home</a></li>
            <li class="breadcrumb-item active">Data Buku</li>
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
          <?= htmlspecialchars($flash['message']) ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <?php endif; ?>

      <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
          <h5 class="card-title font-weight-bold mb-0">Katalog Data Koleksi Buku</h5>
          <div class="ml-auto d-flex align-items-center">
            <button type="button" class="btn btn-success btn-sm font-weight-bold shadow-sm mr-2" id="btnBukaScannerTambahBuku">
              <i class="fas fa-barcode mr-1"></i> Tambah Buku (Scan Barcode & ISBN)
            </button>
            <button type="button" class="btn btn-primary btn-sm font-weight-bold" data-toggle="modal" data-target="#modalTambahBuku">
              <i class="fas fa-plus mr-1"></i> Tambah Manual
            </button>
          </div>
        </div>
        <div class="card-body">
          
          <!-- Search box -->
          <div class="row mb-3">
            <div class="col-md-4 ml-auto">
              <div class="input-group input-group-sm">
                <input type="text" id="tableSearch" class="form-control" placeholder="Pencarian judul, pengarang, ISBN...">
                <div class="input-group-append">
                  <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Table -->
          <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable">
              <thead class="bg-light">
                <tr>
                  <th style="width: 35px;" class="text-center">No</th>
                  <th>Buku &amp; Sampul</th>
                  <th>ISBN / Barcode</th>
                  <th>Pengarang &amp; Penerbit</th>
                  <th class="text-center">Jumlah Awal</th>
                  <th class="text-center">Baik</th>
                  <th class="text-center">Rusak</th>
                  <th class="text-center">Hilang</th>
                  <th class="text-center">Dipinjam</th>
                  <th class="text-center">Total Pinjam</th>
                  <th class="text-center">Total Kembali</th>
                  <th style="width: 140px;" class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($bukuList)): ?>
                  <tr>
                    <td colspan="12" class="text-center text-muted py-3">Belum ada koleksi buku.</td>
                  </tr>
                <?php else: ?>
                  <?php $no = 1; foreach ($bukuList as $row): 
                    $coverUrl = PerpusModel::resolveCoverUrl($row, $base);
                    $jumlahAwal = (int)($row['jumlah_awal'] ?? ((int)$row['j_buku_baik'] + (int)$row['j_buku_rusak'] + (int)($row['j_buku_hilang'] ?? 0) + (int)($row['sedang_dipinjam'] ?? 0)));
                    $sedangDipinjam = (int)($row['sedang_dipinjam'] ?? 0);
                    $totalPinjam = (int)($row['total_pinjam'] ?? 0);
                    $totalKembali = (int)($row['total_kembali'] ?? 0);
                  ?>
                    <tr>
                      <td class="text-center font-weight-bold text-muted"><?= $no++ ?></td>
                      <td>
                        <div class="d-flex align-items-center">
                          <img src="<?= $coverUrl ?>" alt="Cover" style="width: 42px; height: 58px; object-fit: cover; border-radius: 4px; border: 1px solid #e0e0e0;" class="mr-2 flex-shrink-0 shadow-xs">
                          <div>
                            <strong class="text-dark d-block"><?= htmlspecialchars($row['judul_buku']) ?></strong>
                            <div class="mt-1">
                              <span class="badge badge-light border text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars($row['kategori_buku'] ?? 'Umum') ?></span>
                              <span class="badge badge-success text-xs font-weight-bold ml-1">Rp <?= number_format($row['harga'] ?? 65000, 0, ',', '.') ?></span>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td>
                        <code><?= htmlspecialchars($row['isbn'] ?: '-') ?></code>
                        <small class="d-block text-muted">Th: <?= htmlspecialchars($row['tahun_terbit']) ?></small>
                      </td>
                      <td>
                        <div class="text-sm font-weight-bold"><?= htmlspecialchars($row['pengarang']) ?></div>
                        <small class="text-muted"><i class="fas fa-building mr-1"></i><?= htmlspecialchars($row['penerbit_buku']) ?></small>
                      </td>
                      <td class="text-center font-weight-bold">
                        <span class="badge badge-secondary px-2 py-1"><?= $jumlahAwal ?> Eks</span>
                      </td>
                      <td class="text-center">
                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i><?= htmlspecialchars($row['j_buku_baik']) ?></span>
                      </td>
                      <td class="text-center">
                        <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-tools mr-1"></i><?= htmlspecialchars($row['j_buku_rusak']) ?></span>
                      </td>
                      <td class="text-center">
                        <span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i><?= htmlspecialchars($row['j_buku_hilang'] ?? 0) ?></span>
                      </td>
                      <td class="text-center">
                        <span class="badge badge-info px-2 py-1"><i class="fas fa-user-clock mr-1"></i><?= $sedangDipinjam ?></span>
                      </td>
                      <td class="text-center font-weight-bold text-primary">
                        <?= $totalPinjam ?>x
                      </td>
                      <td class="text-center font-weight-bold text-success">
                        <?= $totalKembali ?>x
                      </td>
                      <td class="text-center">
                        <button type="button" class="btn btn-info btn-xs mr-1" data-toggle="modal" data-target="#modalDetailBuku<?= $row['id_buku'] ?>" title="Detail Buku">
                          <i class="fas fa-eye"></i> Detail
                        </button>
                        <button type="button" class="btn btn-primary btn-xs mr-1" data-toggle="modal" data-target="#modalEditBuku<?= $row['id_buku'] ?>" title="Edit">
                          <i class="fas fa-edit"></i>
                        </button>
                        <form action="<?= $base ?>admin/buku/delete" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku ini?');">
                          <input type="hidden" name="id_buku" value="<?= $row['id_buku'] ?>">
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

      <!-- ============================================================== -->
      <!-- KARTU ANALISIS AI SIRKULASI KOLEKSI & BUKU TERLARIS            -->
      <!-- ============================================================== -->
      <?php
      $sortedBuku = $bukuList;
      usort($sortedBuku, function ($a, $b) {
          return ($b['total_pinjam'] ?? 0) <=> ($a['total_pinjam'] ?? 0);
      });
      $topBuku = array_slice($sortedBuku, 0, 5);

      $totalSeluruhPinjam = 0;
      $totalSeluruhKembali = 0;
      $totalSeluruhDipinjam = 0;
      $totalSeluruhHilang = 0;
      $kategoriCounter = [];

      foreach ($bukuList as $b) {
          $totalSeluruhPinjam += (int)($b['total_pinjam'] ?? 0);
          $totalSeluruhKembali += (int)($b['total_kembali'] ?? 0);
          $totalSeluruhDipinjam += (int)($b['sedang_dipinjam'] ?? 0);
          $totalSeluruhHilang += (int)($b['j_buku_hilang'] ?? 0);
          
          $katName = $b['kategori_buku'] ?: 'Umum';
          if (!isset($kategoriCounter[$katName])) {
              $kategoriCounter[$katName] = 0;
          }
          $kategoriCounter[$katName] += (int)($b['total_pinjam'] ?? 0);
      }
      arsort($kategoriCounter);
      $topKategoriNama = !empty($kategoriCounter) ? array_key_first($kategoriCounter) : '-';
      $topKategoriJumlah = !empty($kategoriCounter) ? reset($kategoriCounter) : 0;
      $topBukuUtama = !empty($topBuku) ? $topBuku[0] : null;
      ?>
      <div class="card card-outline card-purple shadow-sm mt-4 border-0">
        <div class="card-header bg-gradient-dark text-white d-flex justify-content-between align-items-center">
          <h5 class="card-title font-weight-bold mb-0">
            <i class="fas fa-brain text-warning mr-2"></i> Analisis AI: Sirkulasi Koleksi &amp; Tren Buku Paling Laris
          </h5>
          <span class="badge badge-warning text-dark font-weight-bold px-2 py-1">
            <i class="fas fa-magic mr-1"></i> Smart Library Intelligence
          </span>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- Ranking Buku Terlaris -->
            <div class="col-lg-7 mb-4 mb-lg-0 border-right">
              <h6 class="font-weight-bold text-dark mb-3">
                <i class="fas fa-trophy text-warning mr-1"></i> Top 5 Buku Paling Sering Dipinjam Siswa:
              </h6>
              <?php if (empty($topBuku)): ?>
                <p class="text-muted">Belum ada data sirkulasi peminjaman.</p>
              <?php else: ?>
                <div class="list-group list-group-flush">
                  <?php 
                  $ranks = [
                    1 => ['badge' => 'badge-warning text-dark', 'icon' => 'fa-crown text-warning'],
                    2 => ['badge' => 'badge-secondary', 'icon' => 'fa-medal text-secondary'],
                    3 => ['badge' => 'badge-info', 'icon' => 'fa-award text-info'],
                    4 => ['badge' => 'badge-light border', 'icon' => 'fa-book text-muted'],
                    5 => ['badge' => 'badge-light border', 'icon' => 'fa-book text-muted'],
                  ];
                  $rankNo = 1;
                  foreach ($topBuku as $tb):
                    $rankMeta = $ranks[$rankNo] ?? $ranks[5];
                    $tbCover = PerpusModel::resolveCoverUrl($tb, $base);
                  ?>
                    <div class="list-group-item d-flex align-items-center p-2 mb-2 rounded bg-light border">
                      <div class="mr-3 text-center" style="width: 32px;">
                        <span class="badge <?= $rankMeta['badge'] ?> font-weight-bold p-1 d-block">#<?= $rankNo ?></span>
                        <i class="fas <?= $rankMeta['icon'] ?> mt-1"></i>
                      </div>
                      <img src="<?= $tbCover ?>" alt="Cover" style="width: 40px; height: 55px; object-fit: cover; border-radius: 4px;" class="mr-3 shadow-xs">
                      <div class="flex-grow-1">
                        <strong class="text-dark d-block mb-1"><?= htmlspecialchars($tb['judul_buku']) ?></strong>
                        <div class="text-xs text-muted">
                          <span class="mr-2"><i class="fas fa-user-edit mr-1"></i><?= htmlspecialchars($tb['pengarang']) ?></span>
                          <span class="badge badge-light border"><?= htmlspecialchars($tb['kategori_buku'] ?? '-') ?></span>
                        </div>
                      </div>
                      <div class="text-right ml-2 flex-shrink-0">
                        <span class="badge badge-primary font-weight-bold px-2 py-1" style="font-size: 0.85rem;">
                          <?= (int)($tb['total_pinjam'] ?? 0) ?> Kali Dipinjam
                        </span>
                        <div class="text-xs text-muted mt-1">
                          Tersedia: <?= (int)$tb['j_buku_baik'] ?> Eks
                        </div>
                      </div>
                    </div>
                  <?php $rankNo++; endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <!-- Insight AI & Rekomendasi Literasi -->
            <div class="col-lg-5">
              <h6 class="font-weight-bold text-dark mb-3">
                <i class="fas fa-lightbulb text-info mr-1"></i> Rekomendasi &amp; Temuan Algoritma:
              </h6>
              
              <div class="callout callout-info bg-light p-3 mb-3 border-left-info shadow-xs">
                <div class="font-weight-bold text-info mb-1"><i class="fas fa-chart-line mr-1"></i> Pola Minat Literasi Siswa</div>
                <p class="small text-muted mb-0">
                  Kategori <strong><?= htmlspecialchars($topKategoriNama) ?></strong> memimpin antusiasme membaca dengan total akumulasi <strong><?= $topKategoriJumlah ?> sirkulasi</strong>. Siswa SMPN 1 Kota Gajah menunjukkan daya tarik tertinggi pada novel sastra inspiratif dan buku referensi tematik.
                </p>
              </div>

              <div class="callout callout-warning bg-light p-3 mb-3 border-left-warning shadow-xs">
                <div class="font-weight-bold text-warning mb-1"><i class="fas fa-shopping-cart mr-1"></i> Saran Pengadaan &amp; Restock</div>
                <p class="small text-muted mb-0">
                  <?php if ($topBukuUtama): ?>
                    Buku <em>"<?= htmlspecialchars($topBukuUtama['judul_buku']) ?>"</em> memiliki frekuensi peminjaman tertinggi (<strong><?= (int)$topBukuUtama['total_pinjam'] ?>x</strong>). Direkomendasikan menambah stok pengadaan eksemplar sebesar <strong>5 - 10 eksemplar tambahan</strong> guna mencegah antrean sirkulasi.
                  <?php else: ?>
                    Perbanyak buku-buku sastra fiksi pemenang penghargaan untuk meningkatkan frekuensi kunjungan perpustakaan.
                  <?php endif; ?>
                </p>
              </div>

              <div class="callout callout-danger bg-light p-3 border-left-danger shadow-xs">
                <div class="font-weight-bold text-danger mb-1"><i class="fas fa-shield-alt mr-1"></i> Kontrol Inventaris &amp; Kehilangan</div>
                <p class="small text-muted mb-0">
                  Tercatat <strong><?= $totalSeluruhHilang ?> eksemplar buku hilang</strong> dan <strong><?= $totalSeluruhDipinjam ?> eksemplar sedang dipinjam aktif</strong>. Pastikan pengingat jatuh tempo via WhatsApp diaktifkan untuk menjaga ketersediaan aset buku di rak fisik perpustakaan.
                </p>
              </div>

            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<!-- ============================================================== -->
<!-- MODAL DETAIL BUKU & EDIT BUKU                                  -->
<!-- ============================================================== -->
<?php if (!empty($bukuList)): ?>
  <?php foreach ($bukuList as $row): 
    $coverUrl = PerpusModel::resolveCoverUrl($row, $base);
    $jumlahAwal = (int)($row['jumlah_awal'] ?? ((int)$row['j_buku_baik'] + (int)$row['j_buku_rusak'] + (int)($row['j_buku_hilang'] ?? 0) + (int)($row['sedang_dipinjam'] ?? 0)));
    $sedangDipinjam = (int)($row['sedang_dipinjam'] ?? 0);
    $totalPinjam = (int)($row['total_pinjam'] ?? 0);
    $totalKembali = (int)($row['total_kembali'] ?? 0);
  ?>
    <!-- MODAL DETAIL BUKU -->
    <div class="modal fade" id="modalDetailBuku<?= $row['id_buku'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-gradient-info text-white">
            <h5 class="modal-title font-weight-bold">
              <i class="fas fa-book-open mr-2"></i> Detail Koleksi Buku: <?= htmlspecialchars($row['judul_buku']) ?>
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body p-4">
            <div class="row">
              <!-- Sampul Buku -->
              <div class="col-md-4 text-center mb-3 mb-md-0">
                <div class="p-2 border rounded bg-light shadow-sm mb-2">
                  <img src="<?= $coverUrl ?>" alt="Cover <?= htmlspecialchars($row['judul_buku']) ?>" style="max-height: 240px; width: 100%; object-fit: contain; border-radius: 6px;">
                </div>
                <div class="mt-2">
                  <span class="badge badge-dark px-2 py-1"><i class="fas fa-barcode mr-1"></i> <?= htmlspecialchars($row['isbn'] ?: 'Tanpa ISBN') ?></span>
                </div>
                <small class="text-muted d-block mt-1">Cover resmi koleksi perpustakaan</small>
              </div>

              <!-- Metadata & Statistik Sirkulasi -->
              <div class="col-md-8">
                <h4 class="font-weight-bold text-dark mb-1"><?= htmlspecialchars($row['judul_buku']) ?></h4>
                <div class="mb-3">
                  <span class="badge badge-primary mr-1"><i class="fas fa-tag mr-1"></i><?= htmlspecialchars($row['kategori_buku'] ?? '-') ?></span>
                  <span class="badge badge-secondary"><i class="fas fa-calendar mr-1"></i>Tahun <?= htmlspecialchars($row['tahun_terbit']) ?></span>
                </div>

                <table class="table table-sm table-borderless text-sm mb-3">
                  <tr>
                    <td style="width: 130px;" class="font-weight-bold text-muted"><i class="fas fa-user-edit mr-1 text-primary"></i> Pengarang</td>
                    <td style="width: 10px;">:</td>
                    <td class="font-weight-bold text-dark"><?= htmlspecialchars($row['pengarang']) ?></td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold text-muted"><i class="fas fa-building mr-1 text-info"></i> Penerbit</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($row['penerbit_buku']) ?></td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold text-muted"><i class="fas fa-barcode mr-1 text-success"></i> ISBN / Barcode</td>
                    <td>:</td>
                    <td><code><?= htmlspecialchars($row['isbn'] ?: '-') ?></code></td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold text-muted"><i class="fas fa-tag mr-1 text-success"></i> Harga Aset Buku</td>
                    <td>:</td>
                    <td><strong class="text-success">Rp <?= number_format($row['harga'] ?? 65000, 0, ',', '.') ?></strong></td>
                  </tr>
                </table>

                <h6 class="font-weight-bold text-dark border-bottom pb-1 mb-2">
                  <i class="fas fa-chart-pie text-warning mr-1"></i> Metrik Stok &amp; Riwayat Sirkulasi
                </h6>
                <div class="row text-center mb-3">
                  <div class="col-3 p-1">
                    <div class="bg-light p-2 rounded border">
                      <small class="text-muted d-block">Jumlah Awal</small>
                      <strong class="text-secondary"><?= $jumlahAwal ?> Eks</strong>
                    </div>
                  </div>
                  <div class="col-3 p-1">
                    <div class="bg-light p-2 rounded border">
                      <small class="text-muted d-block">Kondisi Baik</small>
                      <strong class="text-success"><?= (int)$row['j_buku_baik'] ?> Eks</strong>
                    </div>
                  </div>
                  <div class="col-3 p-1">
                    <div class="bg-light p-2 rounded border">
                      <small class="text-muted d-block">Rusak</small>
                      <strong class="text-warning"><?= (int)$row['j_buku_rusak'] ?> Eks</strong>
                    </div>
                  </div>
                  <div class="col-3 p-1">
                    <div class="bg-light p-2 rounded border">
                      <small class="text-muted d-block">Hilang</small>
                      <strong class="text-danger"><?= (int)($row['j_buku_hilang'] ?? 0) ?> Eks</strong>
                    </div>
                  </div>
                  <div class="col-4 p-1 mt-1">
                    <div class="bg-light p-2 rounded border">
                      <small class="text-muted d-block">Sedang Dipinjam</small>
                      <strong class="text-info"><?= $sedangDipinjam ?> Eks</strong>
                    </div>
                  </div>
                  <div class="col-4 p-1 mt-1">
                    <div class="bg-light p-2 rounded border">
                      <small class="text-muted d-block">Total Peminjaman</small>
                      <strong class="text-primary"><?= $totalPinjam ?> Kali</strong>
                    </div>
                  </div>
                  <div class="col-4 p-1 mt-1">
                    <div class="bg-light p-2 rounded border">
                      <small class="text-muted d-block">Total Pengembalian</small>
                      <strong class="text-success"><?= $totalKembali ?> Kali</strong>
                    </div>
                  </div>
                </div>

                <h6 class="font-weight-bold text-dark border-bottom pb-1 mb-1">
                  <i class="fas fa-align-left text-primary mr-1"></i> Sinopsis / Ringkasan Buku
                </h6>
                <div class="p-3 bg-light rounded border text-muted small" style="max-height: 140px; overflow-y: auto; line-height: 1.5;">
                  <?= !empty($row['sinopsis']) ? nl2br(htmlspecialchars($row['sinopsis'])) : '<em>Belum ada sinopsis untuk buku ini. Anda dapat menambahkannya melalui form edit data buku.</em>' ?>
                </div>

              </div>
            </div>
          </div>
          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-secondary px-3" data-dismiss="modal">Tutup</button>
            <button type="button" class="btn btn-primary px-3" data-dismiss="modal" data-toggle="modal" data-target="#modalEditBuku<?= $row['id_buku'] ?>">
              <i class="fas fa-edit mr-1"></i> Edit Buku
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL EDIT BUKU -->
    <div class="modal fade" id="modalEditBuku<?= $row['id_buku'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-primary text-white py-2">
            <h5 class="modal-title font-weight-bold mb-0">
              <i class="fas fa-edit mr-2"></i> Edit Data Buku
            </h5>
            <div class="ml-auto d-flex align-items-center">
              <button type="submit" form="formEditBuku<?= $row['id_buku'] ?>" class="btn btn-sm btn-light text-primary font-weight-bold mr-2 shadow-xs">
                <i class="fas fa-save mr-1"></i> Simpan
              </button>
              <button type="button" class="close text-white ml-0" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          </div>
          <form action="<?= $base ?>admin/buku/edit" method="post" id="formEditBuku<?= $row['id_buku'] ?>" enctype="multipart/form-data">
            <input type="hidden" name="id_buku" value="<?= $row['id_buku'] ?>">
            <div class="modal-body">
              <div class="form-group">
                <label>Judul Buku <small class="text-danger">* Wajib diisi</small></label>
                <input type="text" name="judul_buku" class="form-control" value="<?= htmlspecialchars($row['judul_buku']) ?>" required>
              </div>
              <div class="row">
                <div class="col-md-6 form-group">
                  <label>Kategori Buku <small class="text-danger">* Wajib diisi</small></label>
                  <select name="id_kategori" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($kategoriList as $k): ?>
                      <option value="<?= (int)$k['id_kategori'] ?>" <?= ((int)$row['id_kategori'] === (int)$k['id_kategori'] || $row['kategori_buku'] === $k['nama_kategori']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['nama_kategori']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6 form-group">
                  <label>Penerbit Buku <small class="text-danger">* Wajib diisi</small></label>
                  <select name="id_penerbit" class="form-control" required>
                    <option value="">-- Pilih Penerbit --</option>
                    <?php foreach ($penerbitList as $p): ?>
                      <option value="<?= (int)$p['id_penerbit'] ?>" <?= ((int)$row['id_penerbit'] === (int)$p['id_penerbit'] || $row['penerbit_buku'] === $p['nama_penerbit']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nama_penerbit']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 form-group">
                  <label>Pengarang / Penulis <small class="text-danger">* Relasi Master</small></label>
                  <select name="id_pengarang" class="form-control" required>
                    <option value="">-- Pilih Pengarang Terdaftar --</option>
                    <?php if (!empty($pengarangList)): ?>
                      <?php foreach ($pengarangList as $pg): ?>
                        <option value="<?= (int)$pg['id_pengarang'] ?>" <?= ((int)($row['id_pengarang'] ?? 0) === (int)$pg['id_pengarang'] || strtolower(trim($row['pengarang'])) === strtolower(trim($pg['nama_pengarang']))) ? 'selected' : '' ?>>
                          [<?= htmlspecialchars($pg['kode_pengarang']) ?>] <?= htmlspecialchars($pg['nama_pengarang']) ?>
                        </option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                  <input type="hidden" name="pengarang" value="<?= htmlspecialchars($row['pengarang']) ?>">
                </div>
                <div class="col-md-3 form-group">
                  <label>Tahun Terbit <small class="text-danger">* Wajib diisi</small></label>
                  <input type="text" name="tahun_terbit" class="form-control" value="<?= htmlspecialchars($row['tahun_terbit']) ?>" required>
                </div>
                <div class="col-md-3 form-group">
                  <label>ISBN</label>
                  <input type="text" name="isbn" class="form-control" value="<?= htmlspecialchars($row['isbn'] ?? '') ?>">
                </div>
              </div>
              <div class="row">
                <div class="col-md-3 form-group">
                  <label>Buku Baik <small class="text-danger">* Wajib</small></label>
                  <input type="number" name="j_buku_baik" class="form-control" value="<?= (int)$row['j_buku_baik'] ?>" required>
                </div>
                <div class="col-md-3 form-group">
                  <label>Buku Rusak <small class="text-danger">* Wajib</small></label>
                  <input type="number" name="j_buku_rusak" class="form-control" value="<?= (int)$row['j_buku_rusak'] ?>" required>
                </div>
                <div class="col-md-2 form-group">
                  <label>Buku Hilang</label>
                  <input type="number" name="j_buku_hilang" class="form-control" value="<?= (int)($row['j_buku_hilang'] ?? 0) ?>">
                </div>
                <div class="col-md-4 form-group">
                  <label class="font-weight-bold"><i class="fas fa-tag text-success mr-1"></i> Harga Buku (Rp) <small class="text-danger">*</small></label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text bg-light font-weight-bold text-xs">Rp</span>
                    </div>
                    <input type="number" name="harga" class="form-control font-weight-bold" value="<?= (int)($row['harga'] ?? 65000) ?>" min="0" step="1000" required>
                  </div>
                </div>
              </div>
              <div class="form-group mb-3">
                <label class="font-weight-bold"><i class="fas fa-image mr-1 text-primary"></i> Cover Buku</label>
                <div class="d-flex align-items-center mb-2">
                  <img src="<?= $coverUrl ?>" alt="Cover" style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;" class="mr-3 shadow-xs">
                  <div class="flex-grow-1">
                    <input type="file" name="cover_image" class="form-control-file" accept="image/jpeg,image/png,image/webp">
                    <small class="text-muted d-block mt-1">Upload gambar baru jika ingin mengganti cover (JPG, PNG, WEBP).</small>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label>Sinopsis Buku</label>
                <textarea name="sinopsis" class="form-control" rows="3" placeholder="Masukkan sinopsis atau ringkasan buku..."><?= htmlspecialchars($row['sinopsis'] ?? '') ?></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
              <button type="submit" class="btn btn-primary px-4">Update</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

    </div>
  </section>
</div>

<!-- ============================================================== -->
<!-- MODAL SCANNER BARCODE & ISBN KAMERA UNTUK TAMBAH BUKU         -->
<!-- ============================================================== -->
<div class="modal fade" id="modalScannerTambahBuku" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title font-weight-bold">
          <i class="fas fa-barcode mr-2"></i> Pindai Barcode / ISBN Buku Fisik Baru
        </h5>
        <button type="button" class="close text-white" id="btnCloseScannerTambahBuku">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-3 text-center">
        <!-- Camera Selector (DroidCam vs Laptop WebCam) -->
        <div class="form-group mb-2 text-left">
          <label class="text-xs text-muted mb-1 font-weight-bold">
            <i class="fas fa-video mr-1"></i> Sumber Kamera (Webcam / DroidCam):
          </label>
          <select id="cameraSelectTambahBuku" class="form-control form-control-sm"></select>
        </div>

        <!-- Scanner Viewport -->
        <div id="readerTambahBuku" style="width: 100%; min-height: 250px; background: #000; border-radius: 6px; overflow: hidden;"></div>

        <div id="scannerStatusTambahBuku" class="mt-2 text-sm text-muted">
          Arahkan barcode di sampul belakang buku ke kotak pemindaian...
        </div>

        <!-- Manual Barcode Input Fallback -->
        <div class="mt-3 pt-2 border-top">
          <label class="text-xs text-muted mb-1 d-block text-left font-weight-bold">Atau Ketik Kode Barcode / ISBN Manual:</label>
          <div class="input-group input-group-sm">
            <input type="text" id="manualCodeTambahBuku" class="form-control" placeholder="Contoh: 9786020332956...">
            <div class="input-group-append">
              <button type="button" class="btn btn-outline-success" id="btnManualSubmitTambahBuku">
                <i class="fas fa-search mr-1"></i> Lookup Buku
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- MODAL TAMBAH BUKU (DAPAT TERISI OTOMATIS HASIL SCAN & MANUAL)   -->
<!-- ============================================================== -->
<div class="modal fade" id="modalTambahBuku" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-primary text-white py-2">
        <h5 class="modal-title font-weight-bold d-flex align-items-center flex-wrap mb-0" id="modalTambahBukuTitle">
          <span><i class="fas fa-plus-circle mr-1"></i> Form Tambah Data Buku Baru</span>
          <span class="badge badge-light text-dark font-weight-bold ml-2" id="modalTambahBukuModeBadge" style="display: none; font-size: 0.75rem;"></span>
        </h5>
        <div class="ml-auto d-flex align-items-center">
          <button type="submit" form="formTambahBuku" class="btn btn-sm btn-light text-primary font-weight-bold mr-2 shadow-xs" id="btnHeaderSubmitTambahBuku">
            <i class="fas fa-save mr-1"></i> Simpan Buku
          </button>
          <button type="button" class="close text-white ml-0" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      </div>
      <form action="<?= $base ?>admin/buku/add" method="post" id="formTambahBuku" enctype="multipart/form-data">
        <input type="hidden" name="duplicate_action" id="duplicateActionInput" value="insert">
        <div class="modal-body">
          <div id="autoFillAlert" class="alert alert-success p-3 mb-3 shadow-xs" style="display: none; border-radius: 6px;">
            <i class="fas fa-magic mr-1"></i> Data buku berhasil diisi otomatis dari hasil scan barcode & metadata katalog online. Anda dapat memodifikasi data di bawah sebelum menyimpan.
          </div>

          <!-- Alert Kotak Pencegahan Duplikat Buku -->
          <div id="duplicateWarningBox" class="alert alert-warning border border-warning shadow-sm p-3 mb-3" style="display: none;">
            <div class="d-flex align-items-center">
              <i class="fas fa-exclamation-triangle fa-2x text-warning mr-3"></i>
              <div>
                <strong class="d-block text-dark font-weight-bold" style="font-size: 1.05rem;" id="duplicateWarningTitle">
                  Pencegahan Duplikat: Buku Ini Sudah Ada di Perpustakaan!
                </strong>
                <span class="text-xs text-muted d-block mt-1" id="duplicateWarningDetails"></span>
              </div>
            </div>
            <hr class="my-2">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
              <small class="text-danger font-weight-bold">
                <i class="fas fa-ban mr-1"></i> Menghindari duplikasi entri data buku yang sama.
              </small>
              <div class="mt-2 mt-md-0">
                <button type="button" class="btn btn-warning btn-xs font-weight-bold mr-1" id="btnActionTambahStok">
                  <i class="fas fa-layer-group mr-1"></i> Tambahkan ke Stok yang Ada (+10)
                </button>
                <button type="button" class="btn btn-outline-secondary btn-xs" data-dismiss="modal">
                  Batal
                </button>
              </div>
            </div>
          </div>

          <!-- 1. BAGIAN UTAMA (PALING ATAS): INPUT ISBN / BARCODE BUKU -->
          <div class="card card-outline card-primary shadow-xs mb-3 bg-light border">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap">
                <label class="mb-0 font-weight-bold text-dark" style="font-size: 0.95rem;">
                  <i class="fas fa-barcode text-primary mr-1"></i> Nomor ISBN / Barcode Buku
                  <span class="badge badge-primary text-xs ml-1 font-weight-bold">Input Utama</span>
                </label>
                <div class="d-flex align-items-center">
                  <span class="badge badge-success text-xs font-weight-bold mr-1" id="isbnScanStatusBadge" style="display:none;"></span>
                  <span class="badge badge-info text-xs font-weight-bold mr-1" id="isbnLiveSourceBadge" style="display:none;"></span>
                </div>
              </div>
              <div class="input-group input-group-lg mt-2">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-white"><i class="fas fa-search text-primary" id="iconCekIsbn"></i></span>
                </div>
                <input type="text" name="isbn" id="addIsbnBuku" class="form-control font-weight-bold text-primary" placeholder="Ketik / tempel ISBN 10 atau 13 digit (Contoh: 9786020312583)" autocomplete="off" autofocus>
                <div class="input-group-append">
                  <button type="button" class="btn btn-primary font-weight-bold px-3" id="btnCekOnlineUlang" title="Cari Metadata Buku Secara Live">
                    <i class="fas fa-sync-alt mr-1"></i> Cari Otomatis
                  </button>
                </div>
              </div>
              <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap text-xs">
                <span class="text-muted" id="isbnHelpText">
                  <i class="fas fa-info-circle mr-1 text-info"></i> Ketik ISBN untuk <strong>live auto-fill</strong> dari <strong>Google Books, OpenLibrary, &amp; Kamus Nasional</strong>.
                </span>
                <span class="text-primary font-weight-bold" id="isbnLiveStatusText"></span>
              </div>
            </div>
          </div>

          <!-- 2. DATA BUKU YANG DAPAT DIMODIFIKASI SECARA BEBAS -->
          <div class="form-group mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="mb-0 font-weight-bold">Judul Buku <small class="text-danger">* Wajib diisi</small></label>
              <small class="text-muted font-italic">(Dapat diubah secara bebas)</small>
            </div>
            <input type="text" name="judul_buku" id="addJudulBuku" class="form-control" placeholder="Masukkan Judul Buku" required>
          </div>
          <div class="row">
            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold">Kategori Buku <small class="text-danger">* Wajib diisi</small></label>
              <select name="id_kategori" id="addKategoriBuku" class="form-control" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($kategoriList as $k): ?>
                  <option value="<?= (int)$k['id_kategori'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold">Penerbit Buku <small class="text-danger">* Wajib diisi</small></label>
              <select name="id_penerbit" id="addPenerbitBuku" class="form-control" required>
                <option value="">-- Pilih Penerbit --</option>
                <?php foreach ($penerbitList as $p): ?>
                  <option value="<?= (int)$p['id_penerbit'] ?>"><?= htmlspecialchars($p['nama_penerbit']) ?></option>
                <?php endforeach; ?>
                <option value="new">+ Penerbit Baru (Ketik Manual)</option>
              </select>
              <input type="text" name="penerbit_buku_custom" id="addPenerbitCustom" class="form-control mt-2" placeholder="Ketik nama penerbit baru..." style="display: none;">
            </div>
          </div>
          <div class="row">
            <div class="col-md-7 form-group mb-3">
              <label class="font-weight-bold">Pengarang Buku <small class="text-danger">* Relasi Master</small></label>
              <select name="id_pengarang" id="addPengarangSelect" class="form-control" required>
                <option value="">-- Pilih Pengarang Terdaftar --</option>
                <?php if (!empty($pengarangList)): ?>
                  <?php foreach ($pengarangList as $pg): ?>
                    <option value="<?= (int)$pg['id_pengarang'] ?>" data-name="<?= htmlspecialchars($pg['nama_pengarang']) ?>">[<?= htmlspecialchars($pg['kode_pengarang']) ?>] <?= htmlspecialchars($pg['nama_pengarang']) ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
                <option value="new">+ Pengarang Baru / Ketik Mandiri</option>
              </select>
              <input type="text" name="pengarang" id="addPengarangBuku" class="form-control mt-2" placeholder="Ketik nama pengarang baru..." style="display: none;">
            </div>
            <div class="col-md-5 form-group mb-3">
              <label class="font-weight-bold">Tahun Terbit <small class="text-danger">* Wajib diisi</small></label>
              <input type="text" name="tahun_terbit" id="addTahunTerbit" class="form-control" placeholder="Contoh: 2024" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-3 form-group">
              <label>Buku Baik <small class="text-danger">* Wajib</small></label>
              <input type="number" name="j_buku_baik" class="form-control" value="10" required>
            </div>
            <div class="col-md-3 form-group">
              <label>Buku Rusak <small class="text-danger">* Wajib</small></label>
              <input type="number" name="j_buku_rusak" class="form-control" value="0" required>
            </div>
            <div class="col-md-2 form-group">
              <label>Buku Hilang</label>
              <input type="number" name="j_buku_hilang" class="form-control" value="0">
            </div>
            <div class="col-md-4 form-group">
              <label class="font-weight-bold"><i class="fas fa-tag text-success mr-1"></i> Harga Buku (Rp) <small class="text-danger">*</small></label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-light font-weight-bold text-xs">Rp</span>
                </div>
                <input type="number" name="harga" id="addHargaBuku" class="form-control font-weight-bold" value="65000" min="0" step="1000" placeholder="65000" required>
              </div>
              <small class="text-muted text-xs d-block mt-1">Dasar nilai aset &amp; ganti rugi.</small>
            </div>
          </div>
          <div class="form-group mb-3">
            <label class="font-weight-bold"><i class="fas fa-image mr-1 text-primary"></i> Upload Sampul / Cover Buku <small class="text-muted">(Opsional)</small></label>
            <input type="file" name="cover_image" class="form-control-file" accept="image/jpeg,image/png,image/webp">
            <small class="text-muted d-block mt-1">
              <i class="fas fa-info-circle mr-1 text-info"></i> Sistem otomatis mendownload sampul resmi jika buku ditemukan secara online lewat ISBN. Anda juga dapat mengunggah file manual di sini (JPG, PNG, WEBP) atau meletakkannya di folder <code>cover_buku/</code>.
            </small>
          </div>
          <div class="form-group">
            <label>Sinopsis Buku</label>
            <textarea name="sinopsis" id="addSinopsisBuku" class="form-control" rows="3" placeholder="Masukkan sinopsis atau ringkasan cerita buku..."></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light border-top py-2 d-flex justify-content-between align-items-center">
          <span class="text-xs text-muted d-none d-sm-inline">
            <i class="fas fa-check-circle text-success mr-1"></i> Form siap disimpan
          </span>
          <div>
            <button type="button" class="btn btn-secondary px-3 mr-2" data-dismiss="modal">
              <i class="fas fa-times mr-1"></i> Batal
            </button>
            <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow-sm" id="btnSubmitTambahBuku">
              <i class="fas fa-save mr-1"></i> Simpan Buku
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var html5QrCode = null;

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

  // Inisialisasi PerpusScanner Omni-Directional untuk Tambah Buku
  var bukuScanner = new PerpusScanner({
    containerId: 'readerTambahBuku',
    selectCameraId: 'cameraSelectTambahBuku',
    statusId: 'scannerStatusTambahBuku',
    baseApiUrl: '<?= $base ?>',
    onScanSuccess: function(decodedText, format) {
      if (typeof playScannerBeep === 'function') playScannerBeep();
      $('#modalScannerTambahBuku').modal('hide');
      lookupIsbnDanBukaForm(decodedText);
    }
  });

  // Open Scanner Modal
  $('#btnBukaScannerTambahBuku').on('click', function() {
    $('#manualCodeTambahBuku').val('');
    $('#modalScannerTambahBuku').modal('show');
    bukuScanner.start();
  });

  $('#btnCloseScannerTambahBuku').on('click', function() {
    bukuScanner.stop();
    $('#modalScannerTambahBuku').modal('hide');
  });

  $('#modalScannerTambahBuku').on('hidden.bs.modal', function () {
    bukuScanner.stop();
    $('#manualCodeTambahBuku').val('');
  });

  $('#modalTambahBuku').on('hidden.bs.modal', function () {
    resetBookFormFields(false);
  });

  // Toggle input penerbit baru jika dipilih mode manual
  $('#addPenerbitBuku').on('change', function() {
    if ($(this).val() === 'new') {
      $('#addPenerbitCustom').show().prop('required', true).focus();
    } else {
      $('#addPenerbitCustom').hide().prop('required', false).val('');
    }
  });

  // Toggle input pengarang baru jika dipilih mode manual
  $('#addPengarangSelect').on('change', function() {
    if ($(this).val() === 'new') {
      $('#addPengarangBuku').show().prop('required', true).focus();
    } else {
      var selName = $(this).find('option:selected').data('name') || '';
      $('#addPengarangBuku').hide().prop('required', false).val(selName);
    }
  });

  // Manual code input
  $('#btnManualSubmitTambahBuku').on('click', function() {
    var code = $('#manualCodeTambahBuku').val().trim();
    if (!code) return;
    bukuScanner.stop();
    $('#modalScannerTambahBuku').modal('hide');
    lookupIsbnDanBukaForm(code);
  });

  // Reset & inisialisasi form saat modal tambah buku dibuka
  $('#modalTambahBuku').on('show.bs.modal', function () {
    if (!$('#addIsbnBuku').val()) {
      resetBookFormFields(false);
    }
    setTimeout(function() {
      $('#addIsbnBuku').focus();
    }, 350);
  });

  // Helper Reset Nilai Form Tambah Buku (Live Search Reset)
  function resetBookFormFields(keepIsbn) {
    if (!keepIsbn) {
      $('#addIsbnBuku').val('');
    }
    $('#addJudulBuku').val('');
    $('#addPengarangSelect').val('');
    $('#addPengarangBuku').hide().prop('required', false).val('');
    $('#addPenerbitBuku').val('');
    $('#addPenerbitCustom').hide().prop('required', false).val('');
    $('#addKategoriBuku').val('');
    $('#addTahunTerbit').val('');
    $('#addSinopsisBuku').val('');
    $('input[name="j_buku_baik"]').val('10');
    $('input[name="j_buku_rusak"]').val('0');
    $('input[name="j_buku_hilang"]').val('0');
    $('#addHargaBuku').val('65000');

    $('#autoFillAlert').hide();
    $('#duplicateWarningBox').hide();
    $('#duplicateActionInput').val('insert');
    $('#isbnLiveStatusText').text('');
    $('#isbnScanStatusBadge').hide().text('');
    $('#isbnLiveSourceBadge').hide().text('');
    $('#modalTambahBukuModeBadge').hide().text('');
    $('#iconCekIsbn').attr('class', 'fas fa-search text-primary');
    $('#btnCekOnlineUlang').prop('disabled', false);
    $('#btnSubmitTambahBuku')
      .prop('disabled', false)
      .removeClass('btn-warning')
      .addClass('btn-primary')
      .html('<i class="fas fa-save mr-1"></i> Simpan Buku');
    $('#addJudulBuku, #addPengarangBuku, #addTahunTerbit, #addSinopsisBuku').removeClass('border-success border-warning border-primary');

    lastLookedUpIsbn = '';
    isResolvingIsbn = false;
  }

  // Kamus Kurasi Terverifikasi Lokal Perpustakaan (0ms Instant)
  var localDict = {
    '9786020305882': {
      title: '131 Pintu Cahaya dari Timur',
      authors: 'A. Fuadi',
      publisher: 'Gramedia Pustaka Utama',
      year: '2014',
      category: 'Novel',
      description: 'Kumpulan 131 mutiara kata, pepatah, dan kata bijak dari novelis Negeri 5 Menara, A. Fuadi, yang dikurasi untuk menginspirasi pengembangan diri dan motivasi generasi muda.'
    },
    '9786020312583': {
      title: 'Cantik Itu Luka',
      authors: 'Eka Kurniawan',
      publisher: 'Gramedia Pustaka Utama',
      year: '2002',
      category: 'Novel',
      description: 'Kisah magis dan berlatar sejarah tentang Dewi Ayu dan anak-anak perempuannya di Halimunda.'
    },
    '9786020658049': {
      title: 'Home Sweet Loan',
      authors: 'Almira Bastari',
      publisher: 'Gramedia Pustaka Utama',
      year: '2022',
      category: 'Novel',
      description: 'Menceritakan perjuangan Kaluna mewujudkan impian memiliki rumah sendiri di tengah tuntutan sandwich generation.'
    },
    '9789793062792': {
      title: 'Laskar Pelangi',
      authors: 'Andrea Hirata',
      publisher: 'Bentang Pustaka',
      year: '2005',
      category: 'Novel',
      description: 'Kisah inspiratif sepuluh anak Belitung Timur menggapai mimpi di sekolah serba terbatas.'
    },
    '9789799731234': {
      title: 'Bumi Manusia',
      authors: 'Pramoedya Ananta Toer',
      publisher: 'Gramedia Pustaka Utama',
      year: '2005',
      category: 'Novel',
      description: 'Mahakarya roman sejarah Indonesia tentang Minke dan Annelies di masa kolonial akhir abad ke-19.'
    },
    '9786020822341': {
      title: 'Hujan',
      authors: 'Tere Liye',
      publisher: 'Gramedia Pustaka Utama',
      year: '2016',
      category: 'Novel',
      description: 'Novel tentang persahabatan, cinta, dan perpisahan berlatar masa depan teknologi fiksi ilmiah.'
    },
    '9786020324784': {
      title: 'Bumi',
      authors: 'Tere Liye',
      publisher: 'Gramedia Pustaka Utama',
      year: '2014',
      category: 'Novel',
      description: 'Petualangan fantasi tiga sahabat di dunia paralel klan Bumi, Bulan, dan Matahari.'
    },
    '9786020332956': {
      title: 'Bulan',
      authors: 'Tere Liye',
      publisher: 'Gramedia Pustaka Utama',
      year: '2015',
      category: 'Novel',
      description: 'Sekuel petualangan Raib, Seli, dan Ali menjelajahi klan Matahari dalam Festival Bunga Matahari.'
    },
    '9786020352947': {
      title: 'Matahari',
      authors: 'Tere Liye',
      publisher: 'Gramedia Pustaka Utama',
      year: '2016',
      category: 'Novel',
      description: 'Petualangan fantasi klan Bintang mencari rahasia masa lalu peradaban dunia paralel.'
    },
    '9786020367200': {
      title: 'Bintang',
      authors: 'Tere Liye',
      publisher: 'Gramedia Pustaka Utama',
      year: '2017',
      category: 'Novel',
      description: 'Kisah puncak perjuangan tiga sekawan menyelamatkan klan Bumi dari kehancuran total.'
    },
    '9789792248616': {
      title: 'Negeri 5 Menara',
      authors: 'A. Fuadi',
      publisher: 'Gramedia Pustaka Utama',
      year: '2009',
      category: 'Novel',
      description: 'Kisah persahabatan enam santri dari berbagai pelosok Indonesia di Pondok Madani dengan semboyan Man Jadda Wajada.'
    },
    '9789792277630': {
      title: 'Ranah 3 Warna',
      authors: 'A. Fuadi',
      publisher: 'Gramedia Pustaka Utama',
      year: '2011',
      category: 'Novel',
      description: 'Perjuangan Alif Fikri menembus batas mimpi kuliah hingga ke benua Amerika dengan mantra Man Shabara Zhafira.'
    },
    '9789791227025': {
      title: 'Ayat-Ayat Cinta',
      authors: 'Habiburrahman El Shirazy',
      publisher: 'Republika',
      year: '2004',
      category: 'Novel',
      description: 'Kisah cinta dan nilai-nilai religius mahasiswa Indonesia Fahri di Al-Azhar Kairo yang sangat populer.'
    },
    '9789791227032': {
      title: 'Ketika Cinta Bertasbih',
      authors: 'Habiburrahman El Shirazy',
      publisher: 'Republika',
      year: '2007',
      category: 'Novel',
      description: 'Kisah Azzam gigih memperjuangkan ilmu dan nafkah keluarga di Kairo dengan penuh integritas.'
    },
    '9786022912828': {
      title: 'Filosofi Kopi',
      authors: 'Dee Lestari',
      publisher: 'Bentang Pustaka',
      year: '2006',
      category: 'Novel',
      description: 'Kumpulan cerita pendek dan prosa puitis Dee Lestari tentang kehidupan, impian, dan racikan kopi.'
    },
    '9789791227889': {
      title: 'Perahu Kertas',
      authors: 'Dee Lestari',
      publisher: 'Bentang Pustaka',
      year: '2009',
      category: 'Novel',
      description: 'Kisah cinta dan pencarian jati diri antara Kugy yang unik dan Keenan pemuda berbakat seni.'
    },
    '9786024246945': {
      title: 'Laut Bercerita',
      authors: 'Leila S. Chudori',
      publisher: 'Gramedia Pustaka Utama',
      year: '2017',
      category: 'Novel',
      description: 'Novel bertema sejarah kemanusiaan tentang aktivis mahasiswa tahun 1998 yang diculik dan keluarganya yang mencari kebenaran.'
    },
    '9786020331409': {
      title: 'Ronggeng Dukuh Paruk',
      authors: 'Ahmad Tohari',
      publisher: 'Gramedia Pustaka Utama',
      year: '2003',
      category: 'Novel',
      description: 'Karya sastra legendaris tentang penari ronggeng Srintil dan Rasus dengan latar sosial pedesaan Jawa.'
    },
    '9786022443261': {
      title: 'Ilmu Pengetahuan Alam SMP Kelas VII',
      authors: 'Victoriani Inabuy dkk.',
      publisher: 'Erlangga',
      year: '2021',
      category: 'Karya Ilmiah',
      description: 'Buku teks resmi kurikulum nasional pembelajaran sains dan IPA terpadu untuk siswa SMP Kelas VII.'
    },
    '9786022443278': {
      title: 'Matematika SMP Kelas VII',
      authors: 'Tim Kemendikbud',
      publisher: 'Erlangga',
      year: '2021',
      category: 'Panduan (how to)',
      description: 'Buku panduan dan ajar matematika siswa SMP Kelas VII kurikulum sekolah menengah pertama.'
    },
    '9786022443285': {
      title: 'Bahasa Indonesia SMP Kelas VII',
      authors: 'Rakhma Subarna dkk.',
      publisher: 'Erlangga',
      year: '2021',
      category: 'Panduan (how to)',
      description: 'Buku ajar resmi mata pelajaran Bahasa Indonesia untuk kelas VII SMP/MTs.'
    },
    '9786022443292': {
      title: 'English for Nusantara SMP Kelas VII',
      authors: 'Ika Lestari Damayanti dkk.',
      publisher: 'Erlangga',
      year: '2021',
      category: 'Panduan (how to)',
      description: 'Buku teks pembelajaran Bahasa Inggris kontekstual untuk siswa SMP Kelas VII.'
    },
    '9786022443308': {
      title: 'Ilmu Pengetahuan Sosial SMP Kelas VII',
      authors: 'M. Nursa\'ban dkk.',
      publisher: 'Erlangga',
      year: '2021',
      category: 'Panduan (how to)',
      description: 'Buku teks IPS terpadu geografi, sejarah, ekonomi, dan sosiologi untuk SMP Kelas VII.'
    },
    '9786022443315': {
      title: 'Pendidikan Pancasila dan Kewarganegaraan SMP Kelas VII',
      authors: 'Zaim Uchrowi, Ruslin Ismail',
      publisher: 'Erlangga',
      year: '2021',
      category: 'Panduan (how to)',
      description: 'Buku teks utama PPKn untuk SMP Kelas VII Kurikulum Merdeka.'
    },
    '9786022443322': {
      title: 'Informatika SMP Kelas VII',
      authors: 'Irya Wisnubhadra dkk.',
      publisher: 'Erlangga',
      year: '2021',
      category: 'Panduan (how to)',
      description: 'Buku teks pembelajaran informatika, algoritma, dan teknologi digital SMP Kelas VII.'
    },
    '9786020523316': {
      title: 'Komet',
      authors: 'Tere Liye',
      publisher: 'Gramedia Pustaka Utama',
      year: '2018',
      category: 'Novel',
      description: 'Petualangan klan Bumi, Bulan, dan Matahari menuju Pulau Terapung di klan Komet.'
    },
    '9786020523323': {
      title: 'Komet Minor',
      authors: 'Tere Liye',
      publisher: 'Gramedia Pustaka Utama',
      year: '2019',
      category: 'Novel',
      description: 'Pertarungan pamungkas melawan Si Tanpa Mahkota di klan Komet Minor.'
    },
    '9786020522883': {
      title: 'Atomic Habits: Perubahan Kecil yang Memberikan Hasil Luar Biasa',
      authors: 'James Clear',
      publisher: 'Gramedia Pustaka Utama',
      year: '2019',
      category: 'Panduan (how to)',
      description: 'Cara praktis dan terbukti untuk membentuk kebiasaan baik dan meruntuhkan kebiasaan buruk.'
    },
    '9786020633177': {
      title: 'Sebuah Seni untuk Bersikap Bodo Amat',
      authors: 'Mark Manson',
      publisher: 'Gramedia Pustaka Utama',
      year: '2018',
      category: 'Panduan (how to)',
      description: 'Pendekatan waras dan realistis demi menjalani hidup yang lebih bermakna dan tenang.'
    },
    '9786020653600': {
      title: 'Filosofi Teras: Filsafat Yunani-Kuno untuk Mental Tangguh',
      authors: 'Henry Manampiring',
      publisher: 'BukuKita / Kompas Gramedia',
      year: '2018',
      category: 'Panduan (how to)',
      description: 'Penerapan praktis filsafat Stoisisme dalam mengelola emosi negatif dan kekhawatiran hidup sehari-hari.'
    },
    '9786020633176': {
      title: 'Garis Waktu',
      authors: 'Fiersa Besari',
      publisher: 'Media Kita / BukuKita',
      year: '2016',
      category: 'Novel',
      description: 'Untaian prosa dan kisah perjalanan rasa, waktu, patah hati, dan menemukan kembali tujuan diri.'
    },
    '9789797945398': {
      title: 'Konspirasi Alam Semesta',
      authors: 'Fiersa Besari',
      publisher: 'Media Kita / BukuKita',
      year: '2017',
      category: 'Novel',
      description: 'Album musik sastra tentang Juang Astrajingga dan Ana Tidae di bawah langit nusantara.'
    },
    '9786022916628': {
      title: 'Orang-Orang Biasa',
      authors: 'Andrea Hirata',
      publisher: 'Bentang Pustaka',
      year: '2019',
      category: 'Novel',
      description: 'Kisah satir jenaka sepuluh sahabat kota Belantik yang berencana melakukan misi nekat demi masa depan.'
    }
  };

  // Helper untuk mengisi form & mencocokkan dropdown
  function applyBookData(book) {
    if (book.title) $('#addJudulBuku').val(book.title);
    if (book.authors) {
      var authorStr = Array.isArray(book.authors) ? book.authors.join(', ') : book.authors;
      var matchedAuthor = false;
      var aNameLower = authorStr.toLowerCase();
      $('#addPengarangSelect option').each(function() {
        var optText = $(this).text().toLowerCase();
        if (optText && optText.indexOf('pilih') === -1 && $(this).val() !== 'new') {
          if (aNameLower.indexOf(optText) !== -1 || optText.indexOf(aNameLower) !== -1) {
            $(this).prop('selected', true);
            matchedAuthor = true;
            $('#addPengarangBuku').hide().prop('required', false).val(authorStr);
            return false;
          }
        }
      });
      if (!matchedAuthor) {
        $('#addPengarangSelect').val('new');
        $('#addPengarangBuku').show().prop('required', true).val(authorStr);
      }
    }
    if (book.year) $('#addTahunTerbit').val(book.year);
    if (book.description) $('#addSinopsisBuku').val(book.description);

    // Pastikan stok default terisi
    if (!$('input[name="j_buku_baik"]').val() || $('input[name="j_buku_baik"]').val() === '0') {
      $('input[name="j_buku_baik"]').val('10');
    }
    if (!$('input[name="j_buku_rusak"]').val()) {
      $('input[name="j_buku_rusak"]').val('0');
    }
    if (!$('input[name="j_buku_hilang"]').val()) {
      $('input[name="j_buku_hilang"]').val('0');
    }

    // Cocokkan dropdown Penerbit Buku
    var matchedPenerbit = false;
    if (book.publisher) {
      var pName = (Array.isArray(book.publisher) ? book.publisher.join(', ') : book.publisher).toLowerCase();
      $('#addPenerbitBuku option').each(function() {
        var optText = $(this).text().toLowerCase();
        if (optText && optText.indexOf('pilih') === -1 && $(this).val() !== 'new') {
          if (pName.indexOf(optText) !== -1 || optText.indexOf(pName) !== -1) {
            $(this).prop('selected', true);
            matchedPenerbit = true;
            $('#addPenerbitCustom').hide().prop('required', false).val('');
            return false;
          }
        }
      });
    }
    if (!matchedPenerbit && book.publisher) {
      $('#addPenerbitBuku').val('new');
      var pubStr = Array.isArray(book.publisher) ? book.publisher.join(', ') : book.publisher;
      $('#addPenerbitCustom').show().prop('required', true).val(pubStr);
    } else if (!matchedPenerbit) {
      $('#addPenerbitCustom').hide().prop('required', false).val('');
    }

    // Cocokkan dropdown Kategori Buku
    var matchedKategori = false;
    var catHint = (book.category || (Array.isArray(book.categories) ? book.categories.join(' ') : '') || '').toLowerCase();
    if (catHint) {
      $('#addKategoriBuku option').each(function() {
        var optText = $(this).text().toLowerCase();
        if (optText && optText.indexOf('pilih') === -1) {
          if (catHint.indexOf(optText) !== -1 || optText.indexOf(catHint) !== -1) {
            $(this).prop('selected', true);
            matchedKategori = true;
            return false;
          }
        }
      });
    }
    if (!matchedKategori) {
      $('#addKategoriBuku option').each(function() {
        if ($(this).val()) {
          $(this).prop('selected', true);
          return false;
        }
      });
    }

    // Beri efek highlight lembut hijau bahwa data terisi otomatis namun bisa diedit
    $('#addJudulBuku, #addPengarangBuku, #addTahunTerbit, #addSinopsisBuku')
      .addClass('border-success')
      .delay(1800)
      .queue(function(next) {
        $(this).removeClass('border-success');
        next();
      });
  }

  // Tampilkan notifikasi auto-fill sukses dengan keterangan sumber
  function showAutoFillSuccess(bookData, sourceName, cleanIsbn) {
    applyBookData(bookData);

    var badgeClass = 'badge-success';
    var badgeIcon = 'fas fa-check-circle';
    if (sourceName.indexOf('Gramedia') !== -1) {
      badgeClass = 'badge-primary';
      badgeIcon = 'fas fa-book-reader';
    } else if (sourceName.indexOf('BukuKita') !== -1) {
      badgeClass = 'badge-info';
      badgeIcon = 'fas fa-shopping-bag';
    } else if (sourceName.indexOf('Google') !== -1) {
      badgeClass = 'badge-danger';
      badgeIcon = 'fab fa-google';
    } else if (sourceName.indexOf('OpenLibrary') !== -1) {
      badgeClass = 'badge-secondary';
      badgeIcon = 'fas fa-globe';
    }

    $('#modalTambahBukuModeBadge')
      .show()
      .removeClass('badge-warning badge-secondary badge-primary badge-info badge-danger badge-success')
      .addClass(badgeClass + ' text-white')
      .html('<i class="' + badgeIcon + ' mr-1"></i> Auto-Fill: ' + sourceName);

    $('#isbnScanStatusBadge')
      .show()
      .html('<i class="fas fa-barcode mr-1"></i> ' + cleanIsbn);

    $('#isbnLiveSourceBadge')
      .show()
      .removeClass('badge-secondary badge-warning badge-primary badge-info badge-danger badge-success')
      .addClass(badgeClass + ' text-white')
      .html('<i class="' + badgeIcon + ' mr-1"></i> ' + sourceName);

    $('#isbnLiveStatusText')
      .html('<span class="text-success font-weight-bold"><i class="' + badgeIcon + ' mr-1"></i> Tervalidasi via ' + sourceName + ' (Data dapat diedit)</span>');

    $('#autoFillAlert')
      .removeClass('alert-info alert-danger alert-warning')
      .addClass('alert-success border-success shadow-xs')
      .css({ 'border-left': '5px solid #28a745', 'background-color': '#f2fdf5' })
      .html(
        '<div class="d-flex align-items-center">' +
          '<div class="mr-3 text-success">' +
            '<i class="' + badgeIcon + ' fa-2x"></i>' +
          '</div>' +
          '<div style="flex:1;">' +
            '<div class="d-flex justify-content-between align-items-center flex-wrap">' +
              '<h6 class="font-weight-bold text-success mb-1">' +
                '<i class="fas fa-magic mr-1"></i> Data Buku Tervalidasi & Terisi Otomatis!' +
              '</h6>' +
              '<span class="badge ' + badgeClass + ' px-2 py-1 mb-1 font-weight-bold">Sumber: ' + sourceName + '</span>' +
            '</div>' +
            '<p class="mb-0 text-sm text-dark">' +
              'Ditemukan: <strong>"' + (bookData.title || 'Buku') + '"</strong> (Pengarang: ' + (bookData.authors || '-') + '). ' +
              '<em>Form telah terisi otomatis dan tetap dapat Anda modifikasi sebelum menyimpan.</em>' +
            '</p>' +
          '</div>' +
        '</div>'
      )
      .show();

    $('#iconCekIsbn').attr('class', 'fas fa-check text-success');
    $('#btnCekOnlineUlang').prop('disabled', false);
  }

  // Multi-Provider Resolver ISBN (Gramedia, BukuKita, Google Books, OpenLibrary, Kamus Nasional, Anti-Duplikat)
  var lastLookedUpIsbn = '';
  var isResolvingIsbn = false;

  async function performIsbnLookup(isbnCode, isLiveTyping) {
    var cleanIsbn = (isbnCode || '').replace(/[^0-9X]/gi, '');
    if (!cleanIsbn || cleanIsbn.length < 9) {
      return;
    }

    if (isResolvingIsbn && lastLookedUpIsbn === cleanIsbn) {
      return;
    }

    isResolvingIsbn = true;
    lastLookedUpIsbn = cleanIsbn;

    // Indikator loading live
    $('#iconCekIsbn').attr('class', 'fas fa-spinner fa-spin text-primary');
    $('#btnCekOnlineUlang').prop('disabled', true);
    $('#isbnLiveStatusText').html('<i class="fas fa-spinner fa-spin mr-1"></i> Memvalidasi ISBN ke Gramedia, BukuKita & Google Books...');

    // 1. Cek Pencegahan Duplikat Buku di Database Perpustakaan
    try {
      var resDup = await fetch('<?= $base ?>admin/buku/api-scan-isbn?isbn=' + encodeURIComponent(cleanIsbn));
      var dataDup = await resDup.json();
      if (dataDup && dataDup.existing) {
        var ex = dataDup.existing;
        $('#duplicateWarningBox').show();
        $('#duplicateWarningTitle').text('Pencegahan Duplikat: Buku Ini Sudah Terdaftar!');
        $('#duplicateWarningDetails').html(
          'Buku: <strong>"' + ex.judul_buku + '"</strong> (Pengarang: ' + ex.pengarang + ')<br>' +
          'Kategori: <span class="badge badge-secondary">' + ex.kategori_buku + '</span> &bull; Penerbit: ' + ex.penerbit_buku + '<br>' +
          'Stok Saat Ini: <strong>' + ex.j_buku_baik + ' Buku Baik</strong> | <strong>' + ex.j_buku_rusak + ' Rusak</strong> | <strong>' + ex.j_buku_hilang + ' Hilang</strong>'
        );

        applyBookData({
          title: ex.judul_buku,
          authors: ex.pengarang,
          year: ex.tahun_terbit,
          publisher: ex.penerbit_buku,
          category: ex.kategori_buku,
          description: ex.sinopsis || ''
        });

        $('#autoFillAlert').hide();
        $('#isbnLiveStatusText').html('<span class="text-warning font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> Buku sudah ada di perpustakaan</span>');
        $('#isbnLiveSourceBadge').show().removeClass('badge-success').addClass('badge-warning text-dark').html('Katalog Internal');
        $('#iconCekIsbn').attr('class', 'fas fa-database text-warning');
        $('#btnCekOnlineUlang').prop('disabled', false);

        $('#btnActionTambahStok').off('click').on('click', function() {
          $('#duplicateActionInput').val('tambah_stok');
          $('#btnSubmitTambahBuku')
            .removeClass('btn-primary')
            .addClass('btn-warning')
            .html('<i class="fas fa-layer-group mr-1"></i> Simpan & Tambahkan Stok (+10 Eksemplar)');
          $('#duplicateWarningDetails').append('<br><span class="text-success font-weight-bold"><i class="fas fa-check mr-1"></i> Mode Tambah Stok Aktif (+10 Eksemplar).</span>');
        });

        isResolvingIsbn = false;
        return;
      } else {
        $('#duplicateWarningBox').hide();
        $('#duplicateActionInput').val('insert');
      }
    } catch (eDup) {}

    // 2. Cek Kamus Kurasi Terverifikasi Nasional / Gramedia / BukuKita (0ms Instant)
    if (localDict[cleanIsbn]) {
      var itemDict = localDict[cleanIsbn];
      var pName = (itemDict.publisher || '').toLowerCase();
      var srcLabel = 'Database Kurasi Nasional';
      if (pName.indexOf('gramedia') !== -1) {
        srcLabel = 'Katalog Gramedia';
      } else if (pName.indexOf('bukukita') !== -1 || pName.indexOf('media kita') !== -1) {
        srcLabel = 'Katalog BukuKita';
      } else if (pName.indexOf('erlangga') !== -1 || pName.indexOf('kemendikbud') !== -1) {
        srcLabel = 'Buku Pelajaran Nasional';
      }
      showAutoFillSuccess(itemDict, srcLabel, cleanIsbn);
      isResolvingIsbn = false;
      return;
    }

    // 3. Coba Server Agregator Perpustakaan (Gramedia Store, BukuKita Online, Google Books, OpenLibrary)
    try {
      var resBackend = await fetch('<?= $base ?>admin/buku/api-lookup-isbn?isbn=' + encodeURIComponent(cleanIsbn));
      var dataBackend = await resBackend.json();
      if (dataBackend && dataBackend.status === 'success' && dataBackend.data) {
        showAutoFillSuccess(dataBackend.data, dataBackend.source || 'Database Kurasi', cleanIsbn);
        isResolvingIsbn = false;
        return;
      }
    } catch (eBk) {}

    // 4. Coba Google Books API Online (Volume Search)
    try {
      var resGb = await fetch('https://www.googleapis.com/books/v1/volumes?q=isbn:' . encodeURIComponent(cleanIsbn));
      var dataGb = await resGb.json();
      if (dataGb && dataGb.items && dataGb.items.length > 0) {
        var info = dataGb.items[0].volumeInfo;
        showAutoFillSuccess({
          title: info.title || '',
          authors: info.authors || [],
          year: info.publishedDate ? info.publishedDate.substring(0, 4) : '',
          publisher: info.publisher || '',
          category: info.categories ? info.categories.join(', ') : '',
          description: info.description || ''
        }, 'Google Books API', cleanIsbn);
        isResolvingIsbn = false;
        return;
      }
    } catch (eGb) {}

    // 5. Coba OpenLibrary Data API (Internet Archive)
    try {
      var resOl = await fetch('https://openlibrary.org/api/books?bibkeys=ISBN:' + encodeURIComponent(cleanIsbn) + '&format=json&jscmd=data');
      var dataOl = await resOl.json();
      var olKey = 'ISBN:' + cleanIsbn;
      if (dataOl && dataOl[olKey]) {
        var b = dataOl[olKey];
        var desc = typeof b.description === 'string' ? b.description : (b.description ? b.description.value : '');
        showAutoFillSuccess({
          title: b.title || '',
          authors: b.authors ? b.authors.map(function(a){ return a.name; }) : [],
          year: b.publish_date ? b.publish_date.replace(/[^0-9]/g, '').substring(0, 4) : '',
          publisher: b.publishers && b.publishers.length > 0 ? b.publishers[0].name : '',
          category: b.subjects && b.subjects.length > 0 ? b.subjects[0].name : '',
          description: desc
        }, 'OpenLibrary', cleanIsbn);
        isResolvingIsbn = false;
        return;
      }
    } catch (eOl) {}

    // 6. Coba OpenLibrary Search API (Cakupan Luas Alternatif)
    try {
      var resOlSearch = await fetch('https://openlibrary.org/search.json?isbn=' + encodeURIComponent(cleanIsbn));
      var dataOlSearch = await resOlSearch.json();
      if (dataOlSearch && dataOlSearch.docs && dataOlSearch.docs.length > 0) {
        var doc = dataOlSearch.docs[0];
        showAutoFillSuccess({
          title: doc.title || '',
          authors: doc.author_name || [],
          year: doc.first_publish_year ? String(doc.first_publish_year) : '',
          publisher: doc.publisher ? (Array.isArray(doc.publisher) ? doc.publisher[0] : doc.publisher) : '',
          category: doc.subject ? (Array.isArray(doc.subject) ? doc.subject[0] : doc.subject) : '',
          description: ''
        }, 'OpenLibrary Search', cleanIsbn);
        isResolvingIsbn = false;
        return;
      }
    } catch (eOlS) {}

    // 7. Jika ISBN Tidak Ditemukan Online -> Aktifkan Mode Input Manual
    $('#iconCekIsbn').attr('class', 'fas fa-search text-primary');
    $('#btnCekOnlineUlang').prop('disabled', false);

    $('#modalTambahBukuModeBadge')
      .show()
      .removeClass('badge-success')
      .addClass('badge-warning text-dark font-weight-bold')
      .html('<i class="fas fa-pencil-alt mr-1"></i> Mode Input Manual');

    $('#isbnLiveSourceBadge')
      .show()
      .removeClass('badge-success')
      .addClass('badge-secondary')
      .html('<i class="fas fa-edit mr-1"></i> Input Manual');

    $('#isbnLiveStatusText')
      .html('<span class="text-secondary"><i class="fas fa-info-circle mr-1"></i> ISBN belum terindeks online. Silakan lengkapi judul & rincian di bawah secara manual.</span>');

    $('#autoFillAlert')
      .removeClass('alert-info alert-success')
      .addClass('alert-warning border-warning shadow-xs')
      .css({ 'border-left': '5px solid #ff9800', 'background-color': '#fff9e6' })
      .html(
        '<div class="d-flex align-items-start">' +
          '<div class="mr-3 text-warning mt-1">' +
            '<i class="fas fa-exclamation-triangle fa-2x"></i>' +
          '</div>' +
          '<div style="flex:1;">' +
            '<h6 class="font-weight-bold text-dark mb-1">' +
              '<i class="fas fa-barcode text-primary mr-1"></i> ISBN: <span class="badge badge-dark px-2 py-1 font-weight-bold">' + cleanIsbn + '</span> Siap' +
            '</h6>' +
            '<p class="mb-0 text-sm text-secondary">' +
              'Nomor ISBN telah siap di kolom atas. Karena rincian buku belum ada di Google Books / OpenLibrary, silakan isi <strong>Judul Buku, Pengarang, dan Penerbit</strong> secara mandiri.' +
            '</p>' +
          '</div>' +
        '</div>'
      )
      .show();

    // Default stok awal
    if (!$('input[name="j_buku_baik"]').val() || $('input[name="j_buku_baik"]').val() === '0') {
      $('input[name="j_buku_baik"]').val('10');
    }
    if (!$('#addTahunTerbit').val()) {
      $('#addTahunTerbit').val(new Date().getFullYear());
    }

    if (!isLiveTyping) {
      setTimeout(function() {
        $('#addJudulBuku').focus();
      }, 300);
    }

    isResolvingIsbn = false;
  }

  // Handler Scanner Kamera Barcode: Buka Form & Otomatis Jalankan Lookup
  async function lookupIsbnDanBukaForm(isbnCode) {
    var cleanIsbn = (isbnCode || '').replace(/[^0-9X]/gi, '');
    resetBookFormFields(false);
    $('#addIsbnBuku').val(cleanIsbn);
    $('#modalTambahBuku').modal('show');
    performIsbnLookup(cleanIsbn, false);
  }

  // Live Auto-Search Listener saat Admin mengetik / menempel / menghapus ISBN di input paling atas
  var isbnDebounceTimer = null;
  $('#addIsbnBuku').on('input paste keyup change', function (e) {
    var val = $(this).val();
    var clean = val.replace(/[^0-9X]/gi, '');

    // 1. Jika nomor ISBN kosong atau seluruh digit dihapus -> segera reset form hasil pencarian
    if (!clean) {
      clearTimeout(isbnDebounceTimer);
      resetBookFormFields(true);
      return;
    }

    // 2. Jika nomor ISBN berubah dari yang sebelumnya sudah ditemukan -> kosongkan form lama
    if (lastLookedUpIsbn && clean !== lastLookedUpIsbn) {
      resetBookFormFields(true);
    }

    // 3. Jika tombol Enter ditekan -> langsung lakukan pencarian tanpa menunggu debounce
    if (e.keyCode === 13) {
      e.preventDefault();
      clearTimeout(isbnDebounceTimer);
      if (clean.length >= 9) {
        performIsbnLookup(clean, false);
      }
      return;
    }

    // 4. Jika panjang digit masih < 9 -> bersihkan notifikasi & indikator live
    if (clean.length < 9) {
      clearTimeout(isbnDebounceTimer);
      $('#isbnLiveStatusText').text('');
      $('#isbnLiveSourceBadge').hide();
      $('#duplicateWarningBox').hide();
      $('#autoFillAlert').hide();
      $('#modalTambahBukuModeBadge').hide();
      return;
    }

    // 5. Live Search otomatis jika nomor ISBN telah valid (>= 10 karakter)
    clearTimeout(isbnDebounceTimer);
    $('#isbnLiveStatusText').html('<i class="fas fa-spinner fa-spin mr-1"></i> Mendeteksi ISBN & mencari buku...');
    isbnDebounceTimer = setTimeout(function () {
      performIsbnLookup(clean, true);
    }, 400);
  });

  // Tombol "Cari Otomatis" di sebelah input ISBN
  $('#btnCekOnlineUlang').on('click', function () {
    var val = $('#addIsbnBuku').val();
    var clean = val.replace(/[^0-9X]/gi, '');
    if (!clean) {
      alert('Silakan masukkan nomor ISBN terlebih dahulu.');
      $('#addIsbnBuku').focus();
      return;
    }
    performIsbnLookup(clean, false);
  });

  // ==========================================
  // Live Search Cepat Tabel Katalog Data Buku
  // ==========================================
  $('#tableSearch').on('input keyup paste change', function () {
    var query = $(this).val().toLowerCase().trim();
    var matchCount = 0;

    $('#dataTable tbody tr').each(function () {
      if ($(this).attr('id') === 'rowNoSearchResultBuku') return;
      if ($(this).find('td[colspan]').length > 0 && $(this).attr('id') !== 'rowNoSearchResultBuku') return;

      var rowText = $(this).text().toLowerCase();
      if (query === '' || rowText.indexOf(query) !== -1) {
        $(this).show();
        matchCount++;
      } else {
        $(this).hide();
      }
    });

    var emptyRow = $('#rowNoSearchResultBuku');
    if (matchCount === 0 && query !== '') {
      if (emptyRow.length === 0) {
        $('#dataTable tbody').append(
          '<tr id="rowNoSearchResultBuku"><td colspan="12" class="text-center py-4 text-muted"><i class="fas fa-search mr-2"></i>Tidak ditemukan koleksi buku dengan kata kunci "<strong>' + $('<div>').text(query).html() + '</strong>".</td></tr>'
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
