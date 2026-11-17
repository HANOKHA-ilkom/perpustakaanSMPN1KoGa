<?php
// Print View: Laporan Riwayat Total Sirkulasi
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$base = (new Controller())->baseUrl();
$currentDateIndo = date('d-m-Y');

// Month translation for Indonesian formal document
$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$tglCetakFormal = date('d') . ' ' . $months[(int)date('m')] . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laporan Riwayat Sirkulasi Perpustakaan - SMPN 1 Kota Gajah</title>

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- Bootstrap 4 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Source Sans Pro', Arial, sans-serif;
      color: #212529;
    }
    .print-page {
      max-width: 980px;
      margin: 20px auto;
      background: #ffffff;
      padding: 35px 45px;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      border: 1px solid #dee2e6;
    }
    .kop-surat {
      border-bottom: 3px double #212529;
      padding-bottom: 15px;
      margin-bottom: 25px;
    }
    .kop-logo {
      width: 80px;
      height: 80px;
    }
    .table th {
      background-color: #f1f5f9;
      color: #1e293b;
      font-weight: 700;
      border-top: none;
    }
    .table td, .table th {
      padding: 8px 10px;
      vertical-align: middle;
      font-size: 0.88rem;
    }
    @media print {
      body {
        background-color: #ffffff;
        color: #000000;
      }
      .print-page {
        margin: 0;
        padding: 0;
        border: none;
        box-shadow: none;
        max-width: 100%;
      }
      .no-print {
        display: none !important;
      }
    }
  </style>
</head>
<body>

  <!-- Floating Print Controls (No Print) -->
  <div class="container text-center my-3 no-print">
    <button onclick="window.print()" class="btn btn-primary px-4 py-2 font-weight-bold shadow-sm mr-2">
      <i class="fas fa-print mr-1"></i> Cetak Dokumen / Simpan PDF
    </button>
    <button onclick="window.close()" class="btn btn-outline-secondary px-3 py-2">
      <i class="fas fa-times mr-1"></i> Tutup Jendela
    </button>
  </div>

  <div class="print-page">
    <!-- KOP SURAT RESMI SEKOLAH -->
    <div class="kop-surat d-flex align-items-center">
      <div class="mr-4">
        <img src="<?= $base ?>assets/img/logo.svg" alt="Logo SMPN 1 Kota Gajah" class="kop-logo">
      </div>
      <div class="text-center flex-grow-1">
        <h5 class="mb-0 font-weight-bold text-uppercase" style="letter-spacing: 1px;">Pemerintah Kabupaten Lampung Tengah</h5>
        <h4 class="mb-0 font-weight-bold text-uppercase" style="letter-spacing: 1.5px;">Dinas Pendidikan dan Kebudayaan</h4>
        <h3 class="mb-1 font-weight-bold text-uppercase text-primary" style="letter-spacing: 2px;">SMP Negeri 1 Kota Gajah</h3>
        <p class="mb-0 text-muted text-sm">
          <?= htmlspecialchars($identitas['alamat_app'] ?? 'Jl. Raya Kota Gajah, Kec. Kota Gajah, Lampung Tengah, Kode Pos 34153') ?>
        </p>
        <p class="mb-0 text-muted text-xs">
          Email: <?= htmlspecialchars($identitas['email_app'] ?? 'perpus@smpn1kotagajah.sch.id') ?> &bull; Telp: <?= htmlspecialchars($identitas['nomor_hp'] ?? '0725-12345') ?>
        </p>
      </div>
    </div>

    <!-- JUDUL DOKUMEN -->
    <div class="text-center mb-4">
      <h5 class="font-weight-bold text-uppercase mb-1" style="text-decoration: underline;">
        Laporan Rekapitulasi Total Sirkulasi Perpustakaan
      </h5>
      <p class="text-muted text-sm mb-0">
        Tanggal Cetak: <strong><?= $tglCetakFormal ?></strong> &bull; Petugas: Administrator Perpustakaan
      </p>
    </div>

    <!-- REKAPITULASI RINGKASAN DATA -->
    <div class="card border mb-3 bg-light">
      <div class="card-body p-3">
        <div class="row text-center">
          <div class="col-3 border-right">
            <span class="text-xs text-muted d-block text-uppercase font-weight-bold">Total Transaksi</span>
            <strong class="text-dark" style="font-size: 1.25rem;"><?= (int)($summary['total_transaksi'] ?? 0) ?></strong>
          </div>
          <div class="col-3 border-right">
            <span class="text-xs text-muted d-block text-uppercase font-weight-bold">Sedang Dipinjam</span>
            <strong class="text-warning" style="font-size: 1.25rem;"><?= (int)($summary['total_pinjam'] ?? 0) ?></strong>
          </div>
          <div class="col-3 border-right">
            <span class="text-xs text-muted d-block text-uppercase font-weight-bold">Sudah Kembali</span>
            <strong class="text-success" style="font-size: 1.25rem;"><?= (int)($summary['total_kembali'] ?? 0) ?></strong>
          </div>
          <div class="col-3">
            <span class="text-xs text-muted d-block text-uppercase font-weight-bold">Total Denda</span>
            <strong class="text-primary" style="font-size: 1.25rem;">Rp <?= number_format($summary['total_denda_rp'] ?? 0, 0, ',', '.') ?></strong>
          </div>
        </div>
      </div>
    </div>

    <!-- TABEL DATA SIRKULASI -->
    <table class="table table-bordered mb-4">
      <thead>
        <tr>
          <th style="width: 35px;" class="text-center">No</th>
          <th>Nama Siswa (Peminjam)</th>
          <th>Judul Buku & Kategori</th>
          <th style="width: 90px;" class="text-center">Tgl Pinjam</th>
          <th style="width: 90px;" class="text-center">Tgl Kembali</th>
          <th style="width: 95px;" class="text-center">Status</th>
          <th style="width: 75px;" class="text-center">Kondisi</th>
          <th style="width: 130px;">Denda</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($riwayatList)): ?>
          <tr>
            <td colspan="8" class="text-center text-muted py-4">Tidak ada catatan transaksi sirkulasi.</td>
          </tr>
        <?php else: ?>
          <?php $no = 1; foreach ($riwayatList as $row): 
            $isReturned = (!empty($row['tanggal_pengembalian']) && $row['tanggal_pengembalian'] !== '0000-00-00' && ($row['status_transaksi'] === 'Selesai' || !empty($row['tanggal_pengembalian'])));
          ?>
            <tr>
              <td class="text-center"><?= $no++ ?></td>
              <td>
                <strong><?= htmlspecialchars($row['fullname'] ?: $row['nama_anggota']) ?></strong>
                <div class="text-xs text-muted">NIS: <?= htmlspecialchars($row['nis'] ?: '-') ?> | Kls: <?= htmlspecialchars($row['kelas'] ?: '-') ?></div>
              </td>
              <td>
                <strong><?= htmlspecialchars($row['judul_buku']) ?></strong>
                <div class="text-xs text-muted"><?= htmlspecialchars($row['kategori_buku'] ?? '-') ?></div>
              </td>
              <td class="text-center"><?= htmlspecialchars($row['tanggal_peminjaman']) ?></td>
              <td class="text-center"><?= $isReturned ? htmlspecialchars($row['tanggal_pengembalian']) : '-' ?></td>
              <td class="text-center">
                <?= $isReturned ? 'Selesai' : 'Dipinjam' ?>
              </td>
              <td class="text-center">
                <?= $isReturned ? htmlspecialchars($row['kondisi_buku_saat_dikembalikan'] ?: 'Baik') : htmlspecialchars($row['kondisi_buku_saat_dipinjam'] ?: 'Baik') ?>
              </td>
              <td>
                <?= htmlspecialchars($row['denda'] ?: 'Tidak ada') ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- TANDA TANGAN RESMI -->
    <div class="row mt-5 pt-3">
      <div class="col-6 text-center">
        <p class="mb-1 text-sm">Mengetahui,</p>
        <p class="font-weight-bold mb-5">Kepala Perpustakaan</p>
        <br><br>
        <p class="font-weight-bold mb-0" style="text-decoration: underline;">Drs. H. Pustakawan Utama, M.Pd.</p>
        <p class="text-xs text-muted">NIP. 19750815 200212 1 003</p>
      </div>
      <div class="col-6 text-center">
        <p class="mb-1 text-sm">Kota Gajah, <?= $tglCetakFormal ?></p>
        <p class="font-weight-bold mb-5">Petugas Meja Sirkulasi</p>
        <br><br>
        <p class="font-weight-bold mb-0" style="text-decoration: underline;">Staf Administrasi Perpustakaan</p>
        <p class="text-xs text-muted">SMP Negeri 1 Kota Gajah</p>
      </div>
    </div>
  </div>

  <script>
    // Otomatis buka dialog cetak saat halaman dimuat
    window.onload = function() {
      // Tunggu 300ms agar styling selesai terender
      setTimeout(function() {
        window.print();
      }, 300);
    };
  </script>
</body>
</html>

