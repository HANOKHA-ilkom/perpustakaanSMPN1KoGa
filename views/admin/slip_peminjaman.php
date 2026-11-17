<?php
// Slip Bukti Peminjaman Buku (Cetak & Unduh PDF)
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$base = (new Controller())->baseUrl();

$tglPinjam = $loan['tanggal_peminjaman'];
$tglPinjamObj = DateTime::createFromFormat('d-m-Y', $tglPinjam) ?: new DateTime();
$tglJatuhTempo = clone $tglPinjamObj;
$tglJatuhTempo->modify('+7 days');

// WhatsApp Struk Link
$siswaWa = '087767499895';
$currentSlipUrl = $base . "admin/peminjaman/slip?id=" . $loan['id_peminjaman'];
$waText = "*PERPUSTAKAAN SMP NEGERI 1 KOTA GAJAH*\n";
$waText .= "Halo *{$loan['fullname']}* (NIS: {$loan['nis']}, Kelas: {$loan['kelas']}),\n";
$waText .= "Berikut bukti peminjaman buku perpustakaan Anda:\n";
$waText .= "📖 *Judul Buku:* {$loan['judul_buku']}\n";
$waText .= "📅 *Tanggal Pinjam:* {$loan['tanggal_peminjaman']}\n";
$waText .= "⏰ *Batas Kembali:* {$tglJatuhTempo->format('d-m-Y')}\n";
$waText .= "📄 *Struk Digital (PDF):* {$currentSlipUrl}\n\n";
$waText .= "Harap mengembalikan buku tepat waktu. Terima kasih!\n";
$waText .= "_Petugas Perpustakaan SMPN 1 Kota Gajah (WA: 082385495700)_";
$waStrukUrl = 'https://api.whatsapp.com/send?phone=6287767499895&text=' . rawurlencode($waText);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Slip Peminjaman #<?= $loan['id_peminjaman'] ?> - <?= htmlspecialchars($loan['fullname']) ?></title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- Bootstrap 4 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Source Sans Pro', Arial, sans-serif;
      color: #333;
    }
    .slip-container {
      max-width: 720px;
      margin: 30px auto;
      background: #ffffff;
      padding: 35px 40px;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      border: 1px solid #e2e8f0;
    }
    .kop-surat {
      border-bottom: 3px double #333;
      padding-bottom: 15px;
      margin-bottom: 20px;
    }
    .kop-logo {
      width: 75px;
      height: 75px;
    }
    .table-detail th {
      background-color: #f8fafc;
      font-weight: 600;
      width: 35%;
    }
    .badge-status {
      font-size: 13px;
      padding: 5px 12px;
      border-radius: 20px;
    }
    .ttd-box {
      margin-top: 40px;
    }
    .ttd-line {
      margin-top: 60px;
      border-bottom: 1px solid #333;
      display: inline-block;
      width: 180px;
    }

    @media print {
      body {
        background-color: #ffffff !important;
        margin: 0;
        padding: 0;
      }
      .no-print {
        display: none !important;
      }
      .slip-container {
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 10px 0 !important;
        max-width: 100% !important;
      }
    }
  </style>
</head>
<body>

<!-- Action Bar (Hidden during print) -->
<div class="container text-center my-4 no-print">
  <button type="button" class="btn btn-primary px-4 font-weight-bold shadow-sm mr-2" onclick="window.print()">
    <i class="fas fa-print mr-1"></i> Cetak / Simpan PDF
  </button>
  <a href="<?= $waStrukUrl ?>" target="_blank" class="btn btn-success px-3 font-weight-bold shadow-sm mr-2">
    <i class="fab fa-whatsapp mr-1"></i> Kirim Struk ke Siswa (087767499895)
  </a>
  <a href="<?= $base ?>admin/peminjaman" class="btn btn-secondary px-3 shadow-sm">
    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Meja Sirkulasi
  </a>
</div>

<!-- Slip Document Box -->
<div class="slip-container">
  
  <!-- Kop Surat -->
  <div class="kop-surat d-flex align-items-center">
    <div class="mr-3">
      <img src="<?= $base ?>assets/img/logo.svg" alt="Logo" class="kop-logo">
    </div>
    <div class="flex-grow-1 text-center">
      <h6 class="mb-0 text-muted font-weight-bold text-uppercase" style="letter-spacing: 1px;">PEMERINTAH KABUPATEN LAMPUNG TENGAH</h6>
      <h4 class="mb-0 font-weight-bold text-dark">SMP NEGERI 1 KOTA GAJAH</h4>
      <h5 class="mb-1 text-primary font-weight-bold">UNIT PERPUSTAKAAN SEKOLAH</h5>
      <small class="text-muted d-block"><?= htmlspecialchars($identitas['alamat_app'] ?? 'Jl. Mansyur No.02 Kota Gajah') ?></small>
      <small class="text-muted">Telp: <?= htmlspecialchars($identitas['nomor_hp'] ?? '-') ?> | Email: <?= htmlspecialchars($identitas['email_app'] ?? '-') ?></small>
    </div>
  </div>

  <!-- Judul Dokumen -->
  <div class="text-center my-3">
    <h5 class="font-weight-bold text-uppercase mb-1" style="text-decoration: underline;">SLIP BUKTI PEMINJAMAN BUKU</h5>
    <span class="badge badge-light border text-muted">
      No. Transaksi: <strong>TRX-<?= str_pad((string)$loan['id_peminjaman'], 6, '0', STR_PAD_LEFT) ?></strong>
    </span>
  </div>

  <!-- Data Identitas Peminjam -->
  <h6 class="font-weight-bold text-secondary mb-2 mt-4">
    <i class="fas fa-user mr-1 text-primary"></i> I. DATA PEMINJAM (SISWA)
  </h6>
  <table class="table table-sm table-bordered table-detail mb-3">
    <tr>
      <th>Nomor Induk Siswa (NIS)</th>
      <td class="font-weight-bold"><?= htmlspecialchars($loan['nis'] ?: '-') ?></td>
    </tr>
    <tr>
      <th>Nama Lengkap Siswa</th>
      <td class="font-weight-bold text-primary"><?= htmlspecialchars($loan['fullname'] ?: $loan['nama_anggota']) ?></td>
    </tr>
    <tr>
      <th>Kelas / Tingkat</th>
      <td><?= htmlspecialchars($loan['kelas'] ?: '-') ?></td>
    </tr>
  </table>

  <!-- Data Buku yang Dipinjam -->
  <h6 class="font-weight-bold text-secondary mb-2 mt-4">
    <i class="fas fa-book mr-1 text-primary"></i> II. DATA BUKU YANG DIPINJAM
  </h6>
  <table class="table table-sm table-bordered table-detail mb-3">
    <tr>
      <th>Judul Buku</th>
      <td class="font-weight-bold"><?= htmlspecialchars($loan['judul_buku']) ?></td>
    </tr>
    <tr>
      <th>Nomor ISBN / Kode Barcode</th>
      <td><?= htmlspecialchars($loan['isbn'] ?: '-') ?></td>
    </tr>
    <tr>
      <th>Pengarang</th>
      <td><?= htmlspecialchars($loan['pengarang'] ?: '-') ?></td>
    </tr>
    <tr>
      <th>Kondisi Buku Saat Dipinjam</th>
      <td><span class="badge badge-success px-2 py-1"><?= htmlspecialchars($loan['kondisi_buku_saat_dipinjam'] ?: 'Baik') ?></span></td>
    </tr>
  </table>

  <!-- Periode Peminjaman & Batas Kembali -->
  <h6 class="font-weight-bold text-secondary mb-2 mt-4">
    <i class="fas fa-calendar-alt mr-1 text-primary"></i> III. JADWAL PEMINJAMAN
  </h6>
  <table class="table table-sm table-bordered table-detail mb-3">
    <tr>
      <th>Tanggal Pinjam</th>
      <td><?= htmlspecialchars($loan['tanggal_peminjaman']) ?></td>
    </tr>
    <tr class="table-warning">
      <th class="text-danger">Batas Maksimal Pengembalian (Jatuh Tempo)</th>
      <td class="font-weight-bold text-danger">
        <i class="fas fa-exclamation-circle mr-1"></i> <?= $tglJatuhTempo->format('d-m-Y') ?>
      </td>
    </tr>
    <tr>
      <th>Status Transaksi</th>
      <td>
        <span class="badge badge-info badge-status">
          <i class="fas fa-clock mr-1"></i> <?= htmlspecialchars($loan['status_transaksi']) ?>
        </span>
      </td>
    </tr>
  </table>

  <!-- Aturan & Ketentuan Denda -->
  <div class="alert alert-secondary text-xs my-3 p-2">
    <strong>Ketentuan Perpustakaan SMP Negeri 1 Kota Gajah:</strong>
    <ol class="mb-0 pl-3">
      <li>Maksimal peminjaman adalah 2 buku per siswa.</li>
      <li>Keterlambatan pengembalian buku melewati batas jatuh tempo dikenakan denda sebesar <strong>Rp 5.000,- / hari</strong>.</li>
      <li>Peminjam bertanggung jawab menjaga keutuhan buku fisik. Kerusakan atau kehilangan buku akan dikenakan denda penggantian sesuai kebijakan sekolah.</li>
    </ol>
  </div>

  <!-- Tanda Tangan -->
  <div class="row ttd-box text-center">
    <div class="col-6">
      <p class="mb-1 text-sm">Peminjam (Siswa)</p>
      <div class="ttd-line"></div>
      <p class="mt-1 font-weight-bold text-sm mb-0"><?= htmlspecialchars($loan['fullname'] ?: $loan['nama_anggota']) ?></p>
      <small class="text-muted">NIS: <?= htmlspecialchars($loan['nis'] ?: '-') ?></small>
    </div>
    <div class="col-6">
      <p class="mb-1 text-sm">Kota Gajah, <?= htmlspecialchars($loan['tanggal_peminjaman']) ?><br>Petugas Pustakawan</p>
      <div class="ttd-line"></div>
      <p class="mt-1 font-weight-bold text-sm mb-0"><?= htmlspecialchars($_SESSION['user']['fullname'] ?? 'Pustakawan SMPN 1') ?></p>
      <small class="text-muted">NIP/Kode: <?= htmlspecialchars($_SESSION['user']['kode_user'] ?? 'PST-01') ?></small>
    </div>
  </div>

</div>

</body>
</html>

