<?php
// Identitas Aplikasi View
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$pageTitle = 'Identitas Aplikasi - Perpustakaan SMPN 1 Kota Gajah';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/sidebar_admin.php';
$base = (new Controller())->baseUrl();

$lat = !empty($identitas['latitude']) ? htmlspecialchars($identitas['latitude']) : '-4.9754';
$lng = !empty($identitas['longitude']) ? htmlspecialchars($identitas['longitude']) : '105.3188';
$gmapsQuery = urlencode('SMP Negeri 1 Kota Gajah, Lampung Tengah');
$gmapsUrl = "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}";
?>

<style>
  .gmaps-card {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    background: linear-gradient(135deg, #e8f0fe 0%, #d2e3fc 100%);
    border: 2px solid #b8d5fb;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    min-height: 220px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 24px;
    text-decoration: none !important;
  }
  .gmaps-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(26, 115, 232, 0.22);
    border-color: #1a73e8;
  }
  .gmaps-pin-icon {
    width: 64px;
    height: 64px;
    background: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 16px rgba(234, 67, 53, 0.3);
    margin-bottom: 12px;
    animation: pinBounce 2s infinite;
  }
  @keyframes pinBounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
  }
  .school-spec-table th {
    width: 35%;
    background-color: #f8f9fa;
    color: #495057;
    font-size: 0.88rem;
    font-weight: 600;
    border-top: none;
  }
  .school-spec-table td {
    font-size: 0.9rem;
    border-top: none;
  }
</style>

<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-school mr-2 text-primary"></i>Identitas Aplikasi &amp; Sekolah
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= $base ?>admin/dashboard">Home</a></li>
            <li class="breadcrumb-item active">Identitas Aplikasi</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">

      <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show shadow-xs" role="alert">
          <i class="fas fa-check-circle mr-1"></i> <?= htmlspecialchars($flash['message']) ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <?php endif; ?>

      <!-- 1. KARTU PENJELASAN SINGKAT APLIKASI -->
      <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-body p-4">
          <div class="row align-items-center">
            <div class="col-md-2 text-center mb-3 mb-md-0">
              <img src="<?= $base ?>assets/img/logo.svg" alt="Logo SMPN 1 Kota Gajah" style="width: 85px; height: 85px;">
            </div>
            <div class="col-md-10">
              <div class="d-flex align-items-center flex-wrap mb-1">
                <h4 class="font-weight-bold text-dark mb-0 mr-2">
                  <?= htmlspecialchars($identitas['nama_app']) ?>
                </h4>
                <span class="badge badge-primary px-2 py-1 font-weight-bold">Versi 2.0 LTS</span>
                <span class="badge badge-success px-2 py-1 font-weight-bold ml-1">Katalog Resmi Terverifikasi</span>
              </div>
              <p class="text-secondary mb-2" style="font-size: 0.95rem; line-height: 1.6;">
                Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah adalah platform digital resmi sekolah yang dirancang khusus untuk memfasilitasi administrasi perpustakaan, pencatatan otomatis katalog buku nasional berstandar ISBN, sirkulasi peminjaman dan pengembalian berbasis barcode scanner, manajemen denda keterlambatan/kehilangan, serta pengawasan aktivitas sirkulasi literasi seluruh siswa dan tenaga pendidik.
              </p>
              <div class="d-flex flex-wrap gap-2 text-xs text-muted">
                <span class="mr-3"><i class="fas fa-check text-success mr-1"></i> Multi-Source Scanner ISBN (Gramedia, BukuKita, Google Books)</span>
                <span class="mr-3"><i class="fas fa-check text-success mr-1"></i> Integrasi Otomatis Data Anggota Siswa &amp; Guru</span>
                <span><i class="fas fa-check text-success mr-1"></i> Cetak Laporan Formal Tata Usaha / Kepala Sekolah</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        
        <!-- Left: Form Edit Identitas Aplikasi -->
        <div class="col-lg-6">
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
              <h5 class="card-title font-weight-bold mb-0 text-dark">
                <i class="fas fa-edit mr-2 text-primary"></i>Edit Identitas Perpustakaan
              </h5>
              <span class="badge badge-light border text-muted">Form Pembaruan Data</span>
            </div>
            <div class="card-body">
              <form action="<?= $base ?>admin/identitas" method="post" id="formIdentitas">
                <input type="hidden" name="id_identitas" value="<?= $identitas['id_identitas'] ?? 1 ?>">

                <div class="form-group mb-3">
                  <label class="font-weight-bold text-dark">Nama Aplikasi / Instansi <small class="text-danger">* Wajib</small></label>
                  <input type="text" name="nama_app" id="namaAppInput" class="form-control" value="<?= htmlspecialchars($identitas['nama_app']) ?>" required>
                </div>

                <div class="form-group mb-3">
                  <label class="font-weight-bold text-dark">Alamat Lengkap Perpustakaan &amp; Sekolah</label>
                  <textarea name="alamat_app" id="alamatAppInput" class="form-control" rows="3" required><?= htmlspecialchars($identitas['alamat_app']) ?></textarea>
                  <small class="text-muted">Alamat fisik resmi gedung SMP Negeri 1 Kota Gajah.</small>
                </div>

                <div class="row">
                  <div class="col-md-6 form-group mb-3">
                    <label class="font-weight-bold text-dark">Email Resmi</label>
                    <div class="input-group">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>
                      <input type="email" name="email_app" class="form-control" value="<?= htmlspecialchars($identitas['email_app']) ?>" required>
                    </div>
                  </div>
                  <div class="col-md-6 form-group mb-3">
                    <label class="font-weight-bold text-dark">Nomor Telepon / WhatsApp</label>
                    <div class="input-group">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
                      <input type="text" name="nomor_hp" class="form-control" value="<?= htmlspecialchars($identitas['nomor_hp']) ?>" required>
                    </div>
                  </div>
                </div>

                <!-- Koordinat Peta Google Maps -->
                <div class="row">
                  <div class="col-md-6 form-group mb-3">
                    <label class="text-xs text-secondary font-weight-bold mb-1">Latitude (Gmaps)</label>
                    <div class="input-group input-group-sm">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-map-pin text-danger"></i></span></div>
                      <input type="text" name="latitude" id="latitudeInput" class="form-control bg-light" value="<?= $lat ?>" placeholder="-4.9754">
                    </div>
                  </div>
                  <div class="col-md-6 form-group mb-3">
                    <label class="text-xs text-secondary font-weight-bold mb-1">Longitude (Gmaps)</label>
                    <div class="input-group input-group-sm">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-map-pin text-danger"></i></span></div>
                      <input type="text" name="longitude" id="longitudeInput" class="form-control bg-light" value="<?= $lng ?>" placeholder="105.3188">
                    </div>
                  </div>
                </div>

                <div class="pt-2">
                  <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow-sm">
                    <i class="fas fa-save mr-1"></i> Simpan Perubahan Identitas
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Right: Detail Sekolah & Peta Google Maps Interaktif (Klik Menuju Website Gmaps) -->
        <div class="col-lg-6">
          
          <!-- DETAIL SEKOLAH RESMI -->
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
              <h5 class="card-title font-weight-bold mb-0 text-dark">
                <i class="fas fa-university mr-2 text-success"></i>Detail Profil Sekolah
              </h5>
              <span class="badge badge-success px-2 py-1 font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Terakreditasi A</span>
            </div>
            <div class="card-body p-0">
              <table class="table table-striped school-spec-table mb-0">
                <tbody>
                  <tr>
                    <th><i class="fas fa-id-card text-muted mr-1"></i> Nama Satuan Pendidikan</th>
                    <td class="font-weight-bold text-dark">SMP Negeri 1 Kota Gajah</td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-barcode text-muted mr-1"></i> NPSN</th>
                    <td><span class="badge badge-dark px-2 py-1 font-weight-bold">10802068</span></td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-award text-muted mr-1"></i> Akreditasi</th>
                    <td><strong class="text-success">A (Sangat Baik / Unggul)</strong> &bull; No. SK BAN-S/M</td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-user-tie text-muted mr-1"></i> Kepala Sekolah</th>
                    <td class="font-weight-bold text-dark">Sukisno, S.Pd., M.Pd.</td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-book-reader text-muted mr-1"></i> Kepala Perpustakaan</th>
                    <td>Dra. Hj. Sri Wahyuni, M.Pd.</td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-map-marked-alt text-muted mr-1"></i> Alamat Sekolah</th>
                    <td>Jl. Kauman No. 1, Kota Gajah, Kec. Kotagajah, Kab. Lampung Tengah, Lampung (34153)</td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-clock text-muted mr-1"></i> Jam Layanan Perpus</th>
                    <td><span class="badge badge-info">Senin - Jumat: 07.30 - 15.30 WIB</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- PETA GOOGLE MAPS CARD (KLIK KE WEBSITE GMAPS) -->
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
              <h5 class="card-title font-weight-bold mb-0 text-dark">
                <i class="fas fa-map-marker-alt mr-2 text-danger"></i>Lokasi Google Maps Sekolah
              </h5>
              <span class="badge badge-primary"><i class="fas fa-external-link-alt mr-1"></i> Buka Website Gmaps</span>
            </div>
            <div class="card-body p-3">
              <a href="<?= $gmapsUrl ?>" target="_blank" rel="noopener noreferrer" class="gmaps-card" title="Klik untuk membuka lokasi SMP Negeri 1 Kota Gajah di Google Maps">
                <div class="gmaps-pin-icon">
                  <i class="fas fa-map-marker-alt fa-2x text-danger"></i>
                </div>
                <h5 class="font-weight-bold text-dark mb-1">
                  SMP Negeri 1 Kota Gajah
                </h5>
                <p class="text-secondary mb-2" style="font-size: 0.88rem; max-width: 440px;">
                  Jl. Kauman No. 1 Kota Gajah, Kec. Kotagajah, Kabupaten Lampung Tengah, Lampung 34153
                </p>
                <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 mb-2">
                  <span class="badge badge-primary px-3 py-2 font-weight-bold shadow-xs">
                    <i class="fas fa-directions mr-1"></i> Klik untuk Buka di Google Maps
                  </span>
                  <span class="badge badge-light border text-muted px-2 py-2 ml-1">
                    <i class="fas fa-crosshairs mr-1"></i> <?= $lat ?>, <?= $lng ?>
                  </span>
                </div>
                <small class="text-muted font-italic">
                  <i class="fas fa-info-circle mr-1"></i> Peta akan terbuka secara interaktif dan menampilkan rute navigasi langsung di Google Maps.
                </small>
              </a>
            </div>
          </div>

        </div>

      </div>

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
