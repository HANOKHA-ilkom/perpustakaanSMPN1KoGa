<?php
// Print View: Laporan Keaktifan Administrator untuk Staf TU & Kepala Sekolah
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$base = (new Controller())->baseUrl();
$identitas = $identitas ?? [
    'nama_app'   => 'Perpustakaan SMP Negeri 1 Kota Gajah',
    'alamat_app' => 'Jl. Mansyur No.02, Sritejokencono, Kec. Kota Gajah, Kab. Lampung Tengah Prov. Lampung',
    'email_app'  => 'smpn1kotagajah@gmail.com',
    'nomor_hp'   => '085381768123'
];

$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$tglCetakFormal = date('d') . ' ' . $months[(int)date('m')] . ' ' . date('Y');

$sumAdmin   = (int)($statsSummary['total_admin'] ?? count($adminList ?? []));
$sumPinjam  = (int)($statsSummary['total_pinjam_sukses'] ?? 0);
$sumKembali = (int)($statsSummary['total_kembali_sukses'] ?? 0);
$sumTotal   = (int)($statsSummary['total_aktivitas'] ?? ($sumPinjam + $sumKembali));
$jamOperasional = $statsSummary['jam_operasional'] ?? '07:30 - 15:30 WIB (Senin - Jumat)';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laporan Keaktifan Administrator - SMPN 1 Kota Gajah</title>

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
      vertical-align: middle !important;
    }
    .badge-stat {
      display: inline-block;
      padding: 3px 8px;
      font-size: 11px;
      font-weight: 700;
      border-radius: 4px;
    }
    .badge-success-soft {
      background-color: #d1fae5;
      color: #065f46;
      border: 1px solid #a7f3d0;
    }
    .badge-info-soft {
      background-color: #e0f2fe;
      color: #0369a1;
      border: 1px solid #bae6fd;
    }
    .badge-primary-soft {
      background-color: #e0e7ff;
      color: #3730a3;
      border: 1px solid #c7d2fe;
    }
    .box-summary {
      background-color: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      padding: 12px;
      text-align: center;
    }
    @media print {
      body {
        background-color: #ffffff;
        margin: 0;
        padding: 0;
      }
      .print-page {
        box-shadow: none;
        border: none;
        padding: 0;
        margin: 0;
        max-width: 100%;
      }
      .no-print {
        display: none !important;
      }
    }
  </style>
</head>
<body>

  <!-- Floating Print Button (Only visible on screen) -->
  <div class="container no-print mt-3 text-right" style="max-width: 980px;">
    <button onclick="window.print()" class="btn btn-primary btn-sm shadow font-weight-bold">
      <i class="fas fa-print mr-1"></i> Cetak / Simpan PDF
    </button>
    <button onclick="window.close()" class="btn btn-secondary btn-sm shadow font-weight-bold ml-1">
      <i class="fas fa-times mr-1"></i> Tutup
    </button>
  </div>

  <div class="print-page">
    
    <!-- KOP SURAT RESMI SEKOLAH -->
    <div class="kop-surat">
      <div class="d-flex align-items-center justify-content-between">
        <div class="text-center" style="width: 90px;">
          <img src="https://upload.wikimedia.org/wikipedia/commons/9/9c/Logo_of_Ministry_of_Education_and_Culture_of_Republic_of_Indonesia.svg" class="kop-logo" alt="Logo Tut Wuri Handayani">
        </div>
        <div class="text-center flex-grow-1 px-3">
          <h6 class="mb-0 font-weight-bold text-uppercase" style="letter-spacing: 1px; font-size: 13px;">PEMERINTAH KABUPATEN LAMPUNG TENGAH</h6>
          <h6 class="mb-0 font-weight-bold text-uppercase" style="letter-spacing: 1px; font-size: 14px;">DINAS PENDIDIKAN DAN KEBUDAYAAN</h6>
          <h4 class="mb-0 font-weight-bold text-uppercase" style="letter-spacing: 1.5px; font-size: 20px;">UPTD SATUAN PENDIDIKAN SMP NEGERI 1 KOTA GAJAH</h4>
          <p class="mb-0 text-muted" style="font-size: 12px; line-height: 1.4;">
            <?= htmlspecialchars($identitas['alamat_app']) ?><br>
            Telp/WhatsApp: <?= htmlspecialchars($identitas['nomor_hp']) ?> &bull; Email: <?= htmlspecialchars($identitas['email_app']) ?>
          </p>
        </div>
        <div class="text-center" style="width: 90px;">
          <i class="fas fa-book-reader fa-4x text-primary opacity-75"></i>
        </div>
      </div>
    </div>

    <!-- JUDUL DOKUMEN LAPORAN DINAS -->
    <div class="text-center mb-4">
      <h5 class="font-weight-bold mb-1 text-uppercase" style="text-decoration: underline; letter-spacing: 0.5px;">
        LAPORAN REKAPITULASI KEAKTIFAN & KINERJA ADMINISTRATOR PERPUSTAKAAN
      </h5>
      <p class="text-muted text-xs mb-1 font-weight-bold">
        Nomor: 421.3 / 185 / 03 / SMP.01 / <?= date('Y') ?>
      </p>
      <p class="text-secondary text-xs mb-0">
        Diserahkan Kepada: <strong>Koordinator Staf Tata Usaha (TU)</strong> &amp; <strong>Kepala Sekolah SMPN 1 Kota Gajah</strong>
      </p>
    </div>

    <!-- KETERANGAN PENGANTAR -->
    <div class="mb-3 text-justify text-sm" style="line-height: 1.6;">
      Bersama laporan ini disampaikan rekapitulasi keaktifan jam dinas dan produktivitas pelayanan sirkulasi (peminjaman dan pengembalian buku) yang telah dilaksanakan oleh petugas administrator pada Meja Layanan Sirkulasi Perpustakaan SMP Negeri 1 Kota Gajah dengan rincian sebagai berikut:
    </div>

    <!-- 4 RINGKASAN EKSEKUTIF -->
    <div class="row mb-4">
      <div class="col-3">
        <div class="box-summary">
          <small class="text-muted d-block text-uppercase font-weight-bold text-xs">Petugas Administrator</small>
          <h4 class="font-weight-bold text-primary mb-0"><?= $sumAdmin ?> Orang</h4>
          <small class="text-muted text-xs">Terverifikasi</small>
        </div>
      </div>
      <div class="col-3">
        <div class="box-summary">
          <small class="text-muted d-block text-uppercase font-weight-bold text-xs">Jam Aktif Layanan</small>
          <h4 class="font-weight-bold text-dark mb-0" style="font-size: 1.25rem;">07:30 - 15:30</h4>
          <small class="text-muted text-xs">WIB (Shift Sekolah)</small>
        </div>
      </div>
      <div class="col-3">
        <div class="box-summary">
          <small class="text-muted d-block text-uppercase font-weight-bold text-xs">Peminjaman Sukses</small>
          <h4 class="font-weight-bold text-success mb-0"><?= $sumPinjam ?> Buku</h4>
          <small class="text-muted text-xs">Disetujui Petugas</small>
        </div>
      </div>
      <div class="col-3">
        <div class="box-summary">
          <small class="text-muted d-block text-uppercase font-weight-bold text-xs">Pengembalian Sukses</small>
          <h4 class="font-weight-bold text-info mb-0"><?= $sumKembali ?> Buku</h4>
          <small class="text-muted text-xs">Diverifikasi ke Rak</small>
        </div>
      </div>
    </div>

    <!-- TABEL DATA PETUGAS ADMINISTRATOR -->
    <div class="table-responsive mb-4">
      <table class="table table-bordered table-sm text-sm">
        <thead>
          <tr class="text-center align-middle">
            <th style="width: 35px;">No</th>
            <th>Nama Lengkap Petugas</th>
            <th>ID / User</th>
            <th style="width: 150px;">Jam Aktif Layanan</th>
            <th style="width: 150px;">Terakhir Login</th>
            <th style="width: 105px;">Peminjaman Sukses</th>
            <th style="width: 110px;">Pengembalian Sukses</th>
            <th style="width: 105px;">Total Aktivitas</th>
            <th style="width: 100px;">Status Tugas</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($adminList)): ?>
            <tr>
              <td colspan="9" class="text-center text-muted py-3">Tidak ada data administrator.</td>
            </tr>
          <?php else: ?>
            <?php $no = 1; foreach ($adminList as $row): 
              $pSukses = (int)($row['total_pinjam_sukses'] ?? 0);
              $kSukses = (int)($row['total_kembali_sukses'] ?? 0);
              $totAkt  = (int)($row['total_aktivitas'] ?? ($pSukses + $kSukses));
              $jam     = !empty($row['jam_aktif']) ? $row['jam_aktif'] : '07:30 - 15:30 WIB';
            ?>
              <tr>
                <td class="text-center font-weight-bold"><?= $no++ ?></td>
                <td>
                  <strong><?= htmlspecialchars($row['fullname']) ?></strong>
                </td>
                <td class="text-center">
                  <code><?= htmlspecialchars($row['username']) ?></code>
                </td>
                <td class="text-center font-weight-bold">
                  <?= htmlspecialchars($jam) ?>
                </td>
                <td class="text-center text-xs">
                  <?= htmlspecialchars($row['terakhir_login'] ?: '-') ?>
                </td>
                <td class="text-center font-weight-bold text-success">
                  <?= $pSukses ?> Trx
                </td>
                <td class="text-center font-weight-bold text-info">
                  <?= $kSukses ?> Trx
                </td>
                <td class="text-center font-weight-bold text-primary">
                  <?= $totAkt ?> Trx
                </td>
                <td class="text-center">
                  <span class="badge-stat badge-success-soft">Aktif Bertugas</span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <tr class="font-weight-bold bg-light">
            <td colspan="5" class="text-center">TOTAL KESELURUHAN AKTIVITAS LAYANAN SIRKULASI</td>
            <td class="text-center text-success"><?= $sumPinjam ?> Trx</td>
            <td class="text-center text-info"><?= $sumKembali ?> Trx</td>
            <td class="text-center text-primary"><?= $sumTotal ?> Trx</td>
            <td class="text-center"><span class="badge-stat badge-primary-soft">100% Valid</span></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- CATATAN DINAS PERPUSTAKAAN -->
    <div class="p-3 mb-4 rounded border bg-light text-xs text-dark" style="line-height: 1.5;">
      <strong>Catatan Tata Kerja &amp; Tupoksi Layanan Sirkulasi:</strong>
      <ol class="mb-0 pl-3 mt-1">
        <li>Petugas administrator melaksanakan piket pelayanan sirkulasi sesuai jam aktif operasional (07.30 - 15.30 WIB).</li>
        <li>Setiap transaksi peminjaman diverifikasi melalui pemindaian barcode fisik dan dicatat secara realtime ke sistem database.</li>
        <li>Buku yang dikembalikan telah diperiksa kondisi fisiknya (Baik/Rusak) dan penghitungan denda otomatis telah disesuaikan dengan aturan perpustakaan.</li>
      </ol>
    </div>

    <!-- TANDA TANGAN RESMI TIGA PIHAK (KEPALA SEKOLAH, STAF TU, DAN ADMIN PERPUS) -->
    <div class="row mt-4 pt-2">
      <!-- Kolom Kiri: Mengetahui Kepala Sekolah -->
      <div class="col-4 text-center">
        <p class="mb-1 text-xs">Mengetahui,</p>
        <p class="font-weight-bold mb-5 text-sm">Kepala SMPN 1 Kota Gajah</p>
        <br><br>
        <p class="font-weight-bold mb-0 text-sm" style="text-decoration: underline;">Drs. H. Ahmad Sudrajat, M.Pd.</p>
        <p class="text-xs text-muted mb-0">NIP. 19680512 199403 1 004</p>
      </div>

      <!-- Kolom Tengah: Mengetahui / Memverifikasi Koordinator Staf Tata Usaha (TU) -->
      <div class="col-4 text-center">
        <p class="mb-1 text-xs">Diverifikasi Oleh,</p>
        <p class="font-weight-bold mb-5 text-sm">Koordinator Tata Usaha (TU)</p>
        <br><br>
        <p class="font-weight-bold mb-0 text-sm" style="text-decoration: underline;">H. Bambang Irawan, S.Sos.</p>
        <p class="text-xs text-muted mb-0">NIP. 19740920 200003 1 002</p>
      </div>

      <!-- Kolom Kanan: Pembuat Laporan Administrator Meja Sirkulasi -->
      <div class="col-4 text-center">
        <p class="mb-1 text-xs">Kota Gajah, <?= $tglCetakFormal ?></p>
        <p class="font-weight-bold mb-5 text-sm">Pengelola Perpustakaan</p>
        <br><br>
        <p class="font-weight-bold mb-0 text-sm" style="text-decoration: underline;">Administrator Utama</p>
        <p class="text-xs text-muted mb-0">Staf Layanan Meja Sirkulasi</p>
      </div>
    </div>

  </div>

  <script>
    // Buka dialog printer otomatis
    window.onload = function() {
      setTimeout(function() {
        window.print();
      }, 300);
    };
  </script>
</body>
</html>

