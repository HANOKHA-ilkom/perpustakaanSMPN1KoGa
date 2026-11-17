<?php
// Dashboard Admin View
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$pageTitle = 'Dashboard Analytics - Perpustakaan SMPN 1 Kota Gajah';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/sidebar_admin.php';
$base = (new Controller())->baseUrl();

// Indonesian Date
$days = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$months = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];
$currentDateStr = $days[date('l')] . ', ' . date('d') . ' ' . $months[date('F')] . ' ' . date('Y');

// Extract metrics & chart data with fallback defaults
$metrics = $analytics['metrics'] ?? [];
$charts  = $analytics['charts'] ?? [];

$totalTerpinjam    = (int)($metrics['total_terpinjam'] ?? 0);
$totalDikembalikan = (int)($metrics['total_dikembalikan'] ?? 0);
$totalDendaRp      = (int)($metrics['total_denda_rp'] ?? 0);
$totalTerlambat    = (int)($metrics['total_terlambat'] ?? 0);
$totalRusak        = (int)($metrics['total_rusak'] ?? 0);
$totalHilang       = (int)($metrics['total_hilang'] ?? 0);
$totalJudul        = (int)($metrics['total_judul'] ?? 0);
$totalStok         = (int)($metrics['total_stok'] ?? 0);
$totalStokBaik     = (int)($metrics['total_stok_baik'] ?? 0);
$totalStokRusak    = (int)($metrics['total_stok_rusak'] ?? 0);
$totalStokHilang   = (int)($metrics['total_stok_hilang'] ?? 0);
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-chart-line text-primary mr-2"></i>Dashboard & Visualisasi Perpustakaan
          </h1>
          <small class="text-muted"><i class="far fa-calendar-alt mr-1"></i><?= $currentDateStr ?> &bull; SMP Negeri 1 Kota Gajah</small>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= $base ?>admin/dashboard">Home</a></li>
            <li class="breadcrumb-item active">Dashboard Analytics</li>
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

      <!-- Welcome Banner -->
      <div class="alert alert-light border shadow-sm mb-4 d-flex justify-content-between align-items-center flex-wrap">
        <div>
          <h6 class="mb-1 text-dark font-weight-bold">
            <i class="fas fa-school mr-2 text-primary"></i> 
            Panel Pusat Informasi & Sirkulasi Perpustakaan SMP Negeri 1 Kota Gajah
          </h6>
          <p class="mb-0 text-muted text-sm">
            Selamat datang, <strong>Administrator / Pustakawan</strong>. Pantau metrik sirkulasi, inventaris buku fisik, dan visualisasi aktivitas siswa secara realtime.
          </p>
        </div>
        <div class="mt-2 mt-md-0">
          <a href="<?= $base ?>admin/peminjaman" class="btn btn-sm btn-primary mr-1 shadow-sm">
            <i class="fas fa-barcode mr-1"></i> Scan Peminjaman
          </a>
          <a href="<?= $base ?>admin/pengembalian" class="btn btn-sm btn-success shadow-sm">
            <i class="fas fa-qrcode mr-1"></i> Scan Pengembalian
          </a>
        </div>
      </div>

      <!-- ==================================================== -->
      <!-- SECTION 1: 8 METRIK RINGKASAN STATISTIK (STAT BOXES) -->
      <!-- ==================================================== -->
      <h5 class="text-secondary font-weight-bold mb-3">
        <i class="fas fa-tachometer-alt mr-1 text-primary"></i> 8 Metrik Ringkasan Sirkulasi & Koleksi
      </h5>

      <!-- Baris 1: 4 Cards -->
      <div class="row">
        <!-- 1. Total Buku Terpinjam -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
          <div class="small-box bg-warning text-white shadow-sm" style="border-radius: 8px;">
            <div class="inner">
              <h3 class="font-weight-bold"><?= $totalTerpinjam ?></h3>
              <p class="font-weight-bold mb-0">Total Buku Terpinjam</p>
              <small class="text-white-50"><i class="fas fa-clock mr-1"></i>Buku sedang di tangan siswa</small>
            </div>
            <div class="icon">
              <i class="fas fa-book-reader"></i>
            </div>
            <a href="<?= $base ?>admin/peminjaman" class="small-box-footer text-white">
              Lihat Detail Sirkulasi <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <!-- 2. Total Buku Dikembalikan -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
          <div class="small-box bg-success shadow-sm" style="border-radius: 8px;">
            <div class="inner">
              <h3 class="font-weight-bold"><?= $totalDikembalikan ?></h3>
              <p class="font-weight-bold mb-0">Total Buku Dikembalikan</p>
              <small class="text-white-50"><i class="fas fa-check-double mr-1"></i>Sirkulasi berhasil selesai</small>
            </div>
            <div class="icon">
              <i class="fas fa-undo-alt"></i>
            </div>
            <a href="<?= $base ?>admin/pengembalian" class="small-box-footer">
              Lihat Pengembalian <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <!-- 3. Total Denda Diterima -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
          <div class="small-box bg-primary shadow-sm" style="border-radius: 8px;">
            <div class="inner">
              <h3 class="font-weight-bold" style="font-size: 1.85rem;">Rp <?= number_format($totalDendaRp, 0, ',', '.') ?></h3>
              <p class="font-weight-bold mb-0">Total Denda Diterima</p>
              <small class="text-white-50"><i class="fas fa-hand-holding-usd mr-1"></i>Kas denda masuk kas perpus</small>
            </div>
            <div class="icon">
              <i class="fas fa-coins"></i>
            </div>
            <a href="<?= $base ?>admin/pengembalian" class="small-box-footer">
              Rincian Denda <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <!-- 4. Total Pengembalian Terlambat -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
          <div class="small-box shadow-sm text-white" style="background-color: #fd7e14; border-radius: 8px;">
            <div class="inner">
              <h3 class="font-weight-bold"><?= $totalTerlambat ?></h3>
              <p class="font-weight-bold mb-0">Pengembalian Terlambat</p>
              <small class="text-white-50"><i class="fas fa-exclamation-circle mr-1"></i>Melebihi batas pinjam 7 hari</small>
            </div>
            <div class="icon">
              <i class="fas fa-user-clock"></i>
            </div>
            <a href="<?= $base ?>admin/pengembalian" class="small-box-footer text-white">
              Data Terlambat <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Baris 2: 4 Cards -->
      <div class="row">
        <!-- 5. Total Pengembalian Rusak -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
          <div class="small-box bg-danger shadow-sm" style="border-radius: 8px;">
            <div class="inner">
              <h3 class="font-weight-bold"><?= $totalRusak ?></h3>
              <p class="font-weight-bold mb-0">Pengembalian Rusak</p>
              <small class="text-white-50"><i class="fas fa-tools mr-1"></i>Buku rusak saat pengembalian</small>
            </div>
            <div class="icon">
              <i class="fas fa-heart-broken"></i>
            </div>
            <a href="<?= $base ?>admin/pengembalian" class="small-box-footer">
              Catatan Kerusakan <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <!-- 6. Total Buku Hilang -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
          <div class="small-box shadow-sm text-white" style="background-color: #495057; border-radius: 8px;">
            <div class="inner">
              <h3 class="font-weight-bold"><?= $totalHilang ?></h3>
              <p class="font-weight-bold mb-0">Total Buku Hilang</p>
              <small class="text-white-50"><i class="fas fa-ban mr-1"></i>Laporan buku hilang / ganti denda</small>
            </div>
            <div class="icon">
              <i class="fas fa-exclamation-triangle"></i>
            </div>
            <a href="<?= $base ?>admin/buku" class="small-box-footer text-white">
              Status Inventaris <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <!-- 7. Total Judul Buku -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
          <div class="small-box bg-info shadow-sm" style="border-radius: 8px;">
            <div class="inner">
              <h3 class="font-weight-bold"><?= $totalJudul ?></h3>
              <p class="font-weight-bold mb-0">Total Judul Buku</p>
              <small class="text-white-50"><i class="fas fa-layer-group mr-1"></i>Variasi judul buku terdaftar</small>
            </div>
            <div class="icon">
              <i class="fas fa-book"></i>
            </div>
            <a href="<?= $base ?>admin/buku" class="small-box-footer">
              Kelola Judul Buku <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <!-- 8. Total Stok Buku -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
          <div class="small-box shadow-sm text-white" style="background-color: #20c997; border-radius: 8px;">
            <div class="inner">
              <h3 class="font-weight-bold"><?= $totalStok ?></h3>
              <p class="font-weight-bold mb-0">Total Stok Buku (Eksemplar)</p>
              <small class="text-white-50">
                Baik: <strong><?= $totalStokBaik ?></strong> | Rusak: <strong><?= $totalStokRusak ?></strong> | Hilang: <strong><?= $totalStokHilang ?></strong>
              </small>
            </div>
            <div class="icon">
              <i class="fas fa-boxes"></i>
            </div>
            <a href="<?= $base ?>admin/buku" class="small-box-footer text-white">
              Kelola Fisik Stok <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- ==================================================== -->
      <!-- SECTION 2: 6 GRAFIK VISUALISASI INTERAKTIF (CHART.JS) -->
      <!-- ==================================================== -->
      <h5 class="text-secondary font-weight-bold mt-4 mb-3">
        <i class="fas fa-chart-pie mr-1 text-success"></i> 6 Grafik Visualisasi Analitik Perpustakaan
      </h5>

      <!-- ROW GRAFIK 1 & 2 -->
      <div class="row">
        <!-- Grafik 1: Peminjaman Bulanan -->
        <div class="col-lg-6 col-12 mb-4">
          <div class="card card-outline card-primary shadow-sm h-100">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
              <h3 class="card-title font-weight-bold text-dark">
                <i class="fas fa-chart-bar text-primary mr-2"></i>1. Grafik Peminjaman Bulanan
              </h3>
              <span class="badge badge-primary px-2 py-1">Januari - Desember</span>
            </div>
            <div class="card-body pt-1">
              <p class="text-muted text-sm mb-3">
                Total frekuensi peminjaman buku oleh siswa pada tiap bulan dalam setahun.
              </p>
              <div style="position: relative; height: 280px; width: 100%;">
                <canvas id="chartPeminjamanBulanan"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Grafik 2: Denda Bulanan dan Total Akumulasi -->
        <div class="col-lg-6 col-12 mb-4">
          <div class="card card-outline card-success shadow-sm h-100">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
              <h3 class="card-title font-weight-bold text-dark">
                <i class="fas fa-money-bill-wave text-success mr-2"></i>2. Grafik Denda Bulanan & Total Akumulasi
              </h3>
              <span class="badge badge-success px-2 py-1">Rupiah (Rp)</span>
            </div>
            <div class="card-body pt-1">
              <p class="text-muted text-sm mb-3">
                Perolehan denda per bulan (bar hijau) dan grafik pertumbuhan total denda (garis oranye).
              </p>
              <div style="position: relative; height: 280px; width: 100%;">
                <canvas id="chartDendaBulanan"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ROW GRAFIK 3 & 4 -->
      <div class="row">
        <!-- Grafik 3: Minat Baca Siswa -->
        <div class="col-lg-6 col-12 mb-4">
          <div class="card card-outline card-info shadow-sm h-100">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
              <h3 class="card-title font-weight-bold text-dark">
                <i class="fas fa-pie-chart text-info mr-2"></i>3. Grafik Minat Baca Siswa
              </h3>
              <span class="badge badge-info px-2 py-1">Kategori Buku</span>
            </div>
            <div class="card-body pt-1">
              <p class="text-muted text-sm mb-3">
                Distribusi peminjaman berdasarkan kategori buku untuk mengetahui topik bacaan favorit siswa.
              </p>
              <div style="position: relative; height: 280px; width: 100%;">
                <canvas id="chartMinatBaca"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Grafik 4: Siswa Beraktivitas di Perpus Bulanan -->
        <div class="col-lg-6 col-12 mb-4">
          <div class="card card-outline card-warning shadow-sm h-100">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
              <h3 class="card-title font-weight-bold text-dark">
                <i class="fas fa-users text-warning mr-2"></i>4. Grafik Anggota/Siswa Beraktivitas Bulanan
              </h3>
              <span class="badge badge-warning text-white px-2 py-1">Siswa Aktif</span>
            </div>
            <div class="card-body pt-1">
              <p class="text-muted text-sm mb-3">
                Jumlah siswa unik yang tercatat melakukan sirkulasi peminjaman atau pengembalian tiap bulan.
              </p>
              <div style="position: relative; height: 280px; width: 100%;">
                <canvas id="chartAktivitasSiswa"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ROW GRAFIK 5 & 6 -->
      <div class="row">
        <!-- Grafik 5: Jumlah Judul Buku yang Tersedia dalam Setahun -->
        <div class="col-lg-6 col-12 mb-4">
          <div class="card card-outline card-purple shadow-sm h-100" style="border-top: 3px solid #6f42c1;">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
              <h3 class="card-title font-weight-bold text-dark">
                <i class="fas fa-calendar-alt text-purple mr-2"></i>5. Grafik Jumlah Judul Buku Berdasarkan Tahun Terbit
              </h3>
              <span class="badge text-white px-2 py-1" style="background-color: #6f42c1;">Koleksi Setahun</span>
            </div>
            <div class="card-body pt-1">
              <p class="text-muted text-sm mb-3">
                Klasifikasi variasi judul koleksi buku perpustakaan berdasarkan tahun publikasi / cetak.
              </p>
              <div style="position: relative; height: 280px; width: 100%;">
                <canvas id="chartJudulTahun"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Grafik 6: Stok Buku Ada (Tersedia) vs Tidak Ada (Sedang Dipinjam) Bulanan -->
        <div class="col-lg-6 col-12 mb-4">
          <div class="card card-outline card-teal shadow-sm h-100" style="border-top: 3px solid #20c997;">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
              <h3 class="card-title font-weight-bold text-dark">
                <i class="fas fa-warehouse text-teal mr-2"></i>6. Grafik Stok Buku Ada vs Tidak Ada Bulanan
              </h3>
              <span class="badge text-white px-2 py-1" style="background-color: #20c997;">Ketersediaan Stok</span>
            </div>
            <div class="card-body pt-1">
              <p class="text-muted text-sm mb-3">
                Ketersediaan stok buku di rak (Ada/Tersedia) dibandingkan buku yang sedang dipinjam (Tidak Ada).
              </p>
              <div style="position: relative; height: 280px; width: 100%;">
                <canvas id="chartStokBulanan"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ==================================================== -->
      <!-- SECTION 3: PROFIL IDENTITAS PERPUSTAKAAN             -->
      <!-- ==================================================== -->
      <div class="row mt-2 mb-4">
        <div class="col-12">
          <div class="card card-outline card-primary shadow-sm text-center py-4">
            <div class="card-body">
              <div style="width: 110px; height: 110px; margin: 0 auto 15px auto;">
                <img src="<?= $base ?>assets/img/logo.svg" alt="Logo SMP Negeri 1 Kota Gajah" style="width:100%; height:100%;">
              </div>
              <h3 class="font-weight-bold text-dark mb-1"><?= htmlspecialchars($identitas['nama_app'] ?? 'Perpustakaan SMPN 1 Kota Gajah') ?></h3>
              <p class="text-muted mb-2" style="max-width: 650px; margin: 0 auto; font-size: 1rem;">
                Alamat : <?= htmlspecialchars($identitas['alamat_app'] ?? 'Jl. Raya Kota Gajah, Kec. Kota Gajah, Lampung Tengah') ?>
              </p>
              <div class="text-secondary text-sm">
                <span class="mr-3"><i class="fas fa-envelope mr-1 text-primary"></i> <?= htmlspecialchars($identitas['email_app'] ?? 'perpus@smpn1kotagajah.sch.id') ?></span>
                <span class="mr-3"><i class="fas fa-phone mr-1 text-success"></i> <?= htmlspecialchars($identitas['nomor_hp'] ?? '0725-12345') ?></span>
                <span><i class="fas fa-map-marker-alt mr-1 text-danger"></i> Lat: <?= htmlspecialchars($identitas['latitude'] ?? '-4.9754') ?>, Long: <?= htmlspecialchars($identitas['longitude'] ?? '105.3188') ?></span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<!-- Load Chart.js CDN Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
  // Global Chart.js Defaults
  Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
  Chart.defaults.color = '#495057';

  // Format Helper Rupiah
  const formatRupiah = (num) => {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
  };

  // PHP Datasets Encoded to JavaScript
  const chartLoansData = <?= json_encode($charts['monthly_loans'] ?? []) ?>;
  const chartFinesData = <?= json_encode($charts['monthly_fines'] ?? []) ?>;
  const chartInterestData = <?= json_encode($charts['interest_categories'] ?? []) ?>;
  const chartStudentsData = <?= json_encode($charts['active_students'] ?? []) ?>;
  const chartTitlesData = <?= json_encode($charts['titles_by_year'] ?? []) ?>;
  const chartStockData = <?= json_encode($charts['monthly_stock'] ?? []) ?>;

  const monthLabels = chartLoansData.short_labels || ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

  // ==========================================
  // 1. GRAFIK PEMINJAMAN BULANAN (BAR)
  // ==========================================
  const ctxLoans = document.getElementById('chartPeminjamanBulanan').getContext('2d');
  new Chart(ctxLoans, {
    type: 'bar',
    data: {
      labels: monthLabels,
      datasets: [{
        label: 'Jumlah Peminjaman',
        data: chartLoansData.data || [],
        backgroundColor: 'rgba(59, 130, 246, 0.85)',
        borderColor: '#2563eb',
        borderWidth: 1.5,
        borderRadius: 6,
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
            label: (ctx) => ` Peminjaman: ${ctx.raw} transaksi buku`
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
            precision: 0
          },
          grid: { color: 'rgba(0, 0, 0, 0.05)' }
        },
        x: {
          grid: { display: false }
        }
      }
    }
  });

  // ==========================================
  // 2. GRAFIK DENDA BULANAN & TOTAL AKUMULASI (BAR + LINE)
  // ==========================================
  const ctxFines = document.getElementById('chartDendaBulanan').getContext('2d');
  new Chart(ctxFines, {
    data: {
      labels: chartFinesData.labels || monthLabels,
      datasets: [
        {
          type: 'line',
          label: 'Total Denda Akumulatif (Rp)',
          data: chartFinesData.cumulative || [],
          borderColor: '#f59e0b',
          backgroundColor: 'rgba(245, 158, 11, 0.1)',
          fill: true,
          tension: 0.35,
          borderWidth: 2.5,
          pointRadius: 4,
          pointBackgroundColor: '#f59e0b',
          yAxisID: 'y'
        },
        {
          type: 'bar',
          label: 'Denda Bulanan (Rp)',
          data: chartFinesData.monthly || [],
          backgroundColor: 'rgba(16, 185, 129, 0.85)',
          borderColor: '#059669',
          borderWidth: 1.5,
          borderRadius: 6,
          hoverBackgroundColor: '#047857',
          yAxisID: 'y'
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
            label: (ctx) => ` ${ctx.dataset.label}: ${formatRupiah(ctx.raw)}`
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: (val) => val >= 1000 ? 'Rp ' + (val / 1000) + 'k' : 'Rp ' + val
          },
          grid: { color: 'rgba(0, 0, 0, 0.05)' }
        },
        x: {
          grid: { display: false }
        }
      }
    }
  });

  // ==========================================
  // 3. GRAFIK MINAT BACA SISWA (DOUGHNUT)
  // ==========================================
  const ctxInterest = document.getElementById('chartMinatBaca').getContext('2d');
  const catColors = [
    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', 
    '#8b5cf6', '#ec4899', '#06b6d4', '#64748b', '#84cc16'
  ];
  new Chart(ctxInterest, {
    type: 'doughnut',
    data: {
      labels: chartInterestData.labels || [],
      datasets: [{
        data: chartInterestData.data || [],
        backgroundColor: catColors.slice(0, (chartInterestData.labels || []).length),
        borderWidth: 2,
        borderColor: '#ffffff',
        hoverOffset: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'right',
          labels: { boxWidth: 12, font: { size: 11 } }
        },
        tooltip: {
          callbacks: {
            label: (ctx) => {
              const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
              return ` ${ctx.label}: ${ctx.raw} pinjaman (${percentage}%)`;
            }
          }
        }
      },
      cutout: '62%'
    }
  });

  // ==========================================
  // 4. GRAFIK SISWA BERAKTIVITAS BULANAN (LINE/SPLINE)
  // ==========================================
  const ctxStudents = document.getElementById('chartAktivitasSiswa').getContext('2d');
  new Chart(ctxStudents, {
    type: 'line',
    data: {
      labels: chartStudentsData.labels || monthLabels,
      datasets: [{
        label: 'Siswa Aktif',
        data: chartStudentsData.data || [],
        borderColor: '#8b5cf6',
        backgroundColor: 'rgba(139, 92, 246, 0.15)',
        borderWidth: 3,
        tension: 0.35,
        fill: true,
        pointRadius: 5,
        pointHoverRadius: 7,
        pointBackgroundColor: '#8b5cf6',
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => ` Siswa Aktif: ${ctx.raw} orang`
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
            precision: 0
          },
          grid: { color: 'rgba(0, 0, 0, 0.05)' }
        },
        x: {
          grid: { display: false }
        }
      }
    }
  });

  // ==========================================
  // 5. GRAFIK JUMLAH JUDUL BUKU PER TAHUN TERBIT (BAR)
  // ==========================================
  const ctxTitles = document.getElementById('chartJudulTahun').getContext('2d');
  new Chart(ctxTitles, {
    type: 'bar',
    data: {
      labels: chartTitlesData.labels || [],
      datasets: [{
        label: 'Jumlah Judul Buku',
        data: chartTitlesData.data || [],
        backgroundColor: 'rgba(111, 66, 193, 0.85)',
        borderColor: '#59339d',
        borderWidth: 1.5,
        borderRadius: 6,
        hoverBackgroundColor: '#4e2889'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => ` Terbit Tahun ${ctx.label}: ${ctx.raw} judul buku`
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
            precision: 0
          },
          grid: { color: 'rgba(0, 0, 0, 0.05)' }
        },
        x: {
          grid: { display: false }
        }
      }
    }
  });

  // ==========================================
  // 6. GRAFIK STOK BUKU ADA VS TIDAK ADA (GROUPED BAR)
  // ==========================================
  const ctxStock = document.getElementById('chartStokBulanan').getContext('2d');
  new Chart(ctxStock, {
    type: 'bar',
    data: {
      labels: chartStockData.labels || monthLabels,
      datasets: [
        {
          label: 'Stok Ada (Tersedia di Rak)',
          data: chartStockData.tersedia || [],
          backgroundColor: 'rgba(32, 201, 151, 0.85)',
          borderColor: '#199973',
          borderWidth: 1.5,
          borderRadius: 4
        },
        {
          label: 'Stok Tidak Ada (Sedang Dipinjam)',
          data: chartStockData.terpinjam || [],
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
            label: (ctx) => ` ${ctx.dataset.label}: ${ctx.raw} eksemplar`
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0, 0, 0, 0.05)' }
        },
        x: {
          grid: { display: false }
        }
      }
    }
  });
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
