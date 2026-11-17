<?php
// Data Administrator View (Enhanced with Jam Aktif, Total Aktivitas Sirkulasi & Cetak Laporan)
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$pageTitle = 'Data Administrator - Perpustakaan SMPN 1 Kota Gajah';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/sidebar_admin.php';
$base = (new Controller())->baseUrl();

$sumAdmin   = (int)($statsSummary['total_admin'] ?? count($adminList ?? []));
$sumPinjam  = (int)($statsSummary['total_pinjam_sukses'] ?? 0);
$sumKembali = (int)($statsSummary['total_kembali_sukses'] ?? 0);
$sumTotal   = (int)($statsSummary['total_aktivitas'] ?? ($sumPinjam + $sumKembali));
$jamOperasional = $statsSummary['jam_operasional'] ?? '07:30 - 15:30 WIB';
?>

<style>
  .modal {
    z-index: 1060 !important;
  }
  .modal-backdrop {
    z-index: 1055 !important;
  }
  .avatar-admin {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
    box-shadow: 0 2px 5px rgba(0, 123, 255, 0.3);
  }
</style>

<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-user-shield text-primary mr-2"></i> Data Administrator & Staf Perpustakaan
          </h1>
          <small class="text-muted">Pemantauan jam kerja operasional dinas, keaktifan pelayanan sirkulasi sukses, dan rekapitulasi kinerja.</small>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= $base ?>admin/dashboard">Home</a></li>
            <li class="breadcrumb-item">Master Data</li>
            <li class="breadcrumb-item active">Data Administrator</li>
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
              <span class="text-xs text-muted font-weight-bold text-uppercase d-block">Total Administrator</span>
              <h3 class="font-weight-bold text-primary mb-0"><?= $sumAdmin ?> <small style="font-size: 0.85rem;" class="text-muted">Petugas</small></h3>
            </div>
            <div class="icon text-primary opacity-25">
              <i class="fas fa-users-cog"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6 mb-2">
          <div class="small-box bg-white border shadow-sm p-3 mb-0">
            <div class="inner">
              <span class="text-xs text-muted font-weight-bold text-uppercase d-block">Jam Aktif Layanan</span>
              <h3 class="font-weight-bold text-dark mb-0" style="font-size: 1.55rem; padding-top: 5px;">
                <i class="far fa-clock text-info mr-1"></i> 07:30 - 15:30
              </h3>
              <small class="text-muted text-xs">Senin s.d. Jumat (Shift Sekolah)</small>
            </div>
            <div class="icon text-info opacity-25">
              <i class="fas fa-business-time"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6 mb-2">
          <div class="small-box bg-white border shadow-sm p-3 mb-0">
            <div class="inner">
              <span class="text-xs text-muted font-weight-bold text-uppercase d-block">Peminjaman Sukses</span>
              <h3 class="font-weight-bold text-success mb-0"><?= $sumPinjam ?> <small style="font-size: 0.85rem;" class="text-muted">Buku</small></h3>
            </div>
            <div class="icon text-success opacity-25">
              <i class="fas fa-check-circle"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6 mb-2">
          <div class="small-box bg-white border shadow-sm p-3 mb-0">
            <div class="inner">
              <span class="text-xs text-muted font-weight-bold text-uppercase d-block">Pengembalian Sukses</span>
              <h3 class="font-weight-bold text-teal mb-0"><?= $sumKembali ?> <small style="font-size: 0.85rem;" class="text-muted">Buku</small></h3>
            </div>
            <div class="icon text-teal opacity-25">
              <i class="fas fa-undo-alt"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Card Data Administrator -->
      <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap py-3">
          <h5 class="card-title font-weight-bold mb-0 text-dark">
            <i class="fas fa-id-badge text-primary mr-2"></i> Rekapitulasi Keaktifan & Aktivitas Administrator
          </h5>
          <div class="card-tools mt-2 mt-md-0 d-flex align-items-center">
            <!-- Tombol Cetak Laporan Keaktifan Admin untuk Staf TU / Kepala Sekolah -->
            <a href="<?= $base ?>admin/administrator/cetak" target="_blank" class="btn btn-outline-primary btn-sm font-weight-bold shadow-xs mr-2" title="Cetak Laporan Keaktifan Admin untuk Diserahkan ke Staf TU / Kepala Sekolah">
              <i class="fas fa-print mr-1"></i> Cetak Laporan Keaktifan Admin
            </a>

            <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-xs" data-toggle="modal" data-target="#modalTambahAdmin">
              <i class="fas fa-plus mr-1"></i> Tambah Administrator
            </button>
          </div>
        </div>
        <div class="card-body">
          
          <!-- Search box -->
          <div class="row mb-3">
            <div class="col-md-4 ml-auto">
              <div class="input-group input-group-sm">
                <input type="text" id="tableSearch" class="form-control" placeholder="Cari nama atau username admin...">
                <div class="input-group-append">
                  <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Table -->
          <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered mb-0 text-sm" id="dataTable">
              <thead class="bg-light">
                <tr>
                  <th style="width: 45px;" class="text-center align-middle">No</th>
                  <th class="align-middle">Identitas Administrator</th>
                  <th class="align-middle" style="width: 200px;">Jam Aktif Layanan</th>
                  <th class="align-middle" style="width: 170px;">Terakhir Login</th>
                  <th class="text-center align-middle" style="width: 150px;">Peminjaman Sukses</th>
                  <th class="text-center align-middle" style="width: 150px;">Pengembalian Sukses</th>
                  <th class="text-center align-middle" style="width: 150px;">Total Aktivitas</th>
                  <th style="width: 100px;" class="text-center align-middle">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($adminList)): ?>
                  <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                      <i class="fas fa-users-slash fa-2x mb-2 d-block text-secondary"></i>
                      Belum ada data administrator terdaftar.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php $no = 1; foreach ($adminList as $row): 
                    $initial = mb_substr($row['fullname'], 0, 1);
                    $tPinjamAdmin = (int)($row['total_pinjam_sukses'] ?? 0);
                    $tKembaliAdmin = (int)($row['total_kembali_sukses'] ?? 0);
                    $tAktivitasAdmin = (int)($row['total_aktivitas'] ?? ($tPinjamAdmin + $tKembaliAdmin));
                    $jamAktif = !empty($row['jam_aktif']) ? $row['jam_aktif'] : '07:30 - 15:30 WIB';
                  ?>
                    <tr>
                      <td class="text-center font-weight-bold align-middle"><?= $no++ ?></td>
                      <td class="align-middle">
                        <div class="d-flex align-items-center">
                          <div class="avatar-admin mr-2 flex-shrink-0">
                            <?= htmlspecialchars($initial) ?>
                          </div>
                          <div>
                            <strong class="text-dark d-block"><?= htmlspecialchars($row['fullname']) ?></strong>
                            <small class="text-muted mr-1">Username: <code><?= htmlspecialchars($row['username']) ?></code></small>
                            <span class="badge badge-success text-xs px-2 py-0 shadow-xs"><i class="fas fa-shield-alt mr-1"></i> Terverifikasi</span>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle">
                        <div class="d-flex align-items-center mb-1">
                          <span class="badge badge-light border text-dark font-weight-bold px-2 py-1">
                            <i class="far fa-clock text-primary mr-1"></i> <?= htmlspecialchars($jamAktif) ?>
                          </span>
                        </div>
                        <small class="text-success font-weight-bold">
                          <i class="fas fa-circle text-xs mr-1"></i> Aktif Bertugas (Sirkulasi)
                        </small>
                      </td>
                      <td class="align-middle text-sm">
                        <span class="text-dark font-weight-bold d-block">
                          <?= htmlspecialchars($row['terakhir_login'] ?: '-') ?>
                        </span>
                        <small class="text-muted"><i class="fas fa-history mr-1"></i> Sesi Akses Sistem</small>
                      </td>
                      <td class="text-center align-middle">
                        <span class="badge badge-success px-2 py-1 font-weight-bold shadow-xs" style="font-size: 0.85rem;">
                          <i class="fas fa-arrow-circle-right mr-1"></i> <?= $tPinjamAdmin ?> Sukses
                        </span>
                      </td>
                      <td class="text-center align-middle">
                        <span class="badge badge-info px-2 py-1 font-weight-bold shadow-xs" style="font-size: 0.85rem;">
                          <i class="fas fa-check-circle mr-1"></i> <?= $tKembaliAdmin ?> Sukses
                        </span>
                      </td>
                      <td class="text-center align-middle">
                        <span class="badge badge-primary px-3 py-1 font-weight-bold shadow-xs" style="font-size: 0.9rem;">
                          <?= $tAktivitasAdmin ?> Transaksi
                        </span>
                      </td>
                      <td class="text-center align-middle">
                        <button type="button" class="btn btn-primary btn-xs mr-1" data-toggle="modal" data-target="#modalEditAdmin<?= $row['id_user'] ?>" title="Edit Data & Jam Aktif">
                          <i class="fas fa-edit"></i>
                        </button>
                        <form action="<?= $base ?>admin/administrator/delete" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun administrator <?= addslashes($row['fullname']) ?>?');">
                          <input type="hidden" name="id_user" value="<?= $row['id_user'] ?>">
                          <button type="submit" class="btn btn-danger btn-xs" title="Hapus Administrator">
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

<!-- Modal Edit Administrator -->
<?php if (!empty($adminList)): ?>
  <?php foreach ($adminList as $row): 
    $jamAktif = !empty($row['jam_aktif']) ? $row['jam_aktif'] : '07:30 - 15:30 WIB';
  ?>
    <div class="modal fade" id="modalEditAdmin<?= $row['id_user'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-primary text-white py-3">
            <h5 class="modal-title font-weight-bold">
              <i class="fas fa-user-edit mr-2"></i> Edit Data Administrator
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="<?= $base ?>admin/administrator/edit" method="post">
            <input type="hidden" name="id_user" value="<?= $row['id_user'] ?>">
            <div class="modal-body p-4">
              <div class="form-group mb-3">
                <label class="font-weight-bold text-sm">Nama Lengkap <small class="text-danger">*</small></label>
                <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($row['fullname']) ?>" required>
              </div>
              <div class="form-group mb-3">
                <label class="font-weight-bold text-sm">Nama Pengguna (Username) <small class="text-danger">*</small></label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($row['username']) ?>" required>
              </div>
              <div class="form-group mb-3">
                <label class="font-weight-bold text-sm">Kata Sandi (Password) <small class="text-danger">*</small></label>
                <input type="text" name="password" class="form-control" value="<?= htmlspecialchars($row['password']) ?>" required>
              </div>
              <div class="form-group mb-0">
                <label class="font-weight-bold text-sm">Jam Aktif Layanan Perpustakaan</label>
                <input type="text" name="jam_aktif" class="form-control" value="<?= htmlspecialchars($jamAktif) ?>" placeholder="Contoh: 07:30 - 15:30 WIB">
                <small class="text-muted">Jam kerja dinas pelayanan meja sirkulasi perpustakaan.</small>
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
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<!-- Modal Tambah Administrator Baru -->
<div class="modal fade" id="modalTambahAdmin" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title font-weight-bold">
          <i class="fas fa-user-plus mr-2"></i> Tambah Administrator Baru
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?= $base ?>admin/administrator/add" method="post">
        <div class="modal-body p-4">
          <div class="form-group mb-3">
            <label class="font-weight-bold text-sm">Nama Lengkap <small class="text-danger">* Wajib diisi</small></label>
            <input type="text" name="fullname" class="form-control" placeholder="Contoh: Ratna Sari Dewi, S.Pd." required>
          </div>
          <div class="form-group mb-3">
            <label class="font-weight-bold text-sm">Nama Pengguna (Username) <small class="text-danger">* Wajib diisi</small></label>
            <input type="text" name="username" class="form-control" placeholder="Contoh: ratna" required>
          </div>
          <div class="form-group mb-3">
            <label class="font-weight-bold text-sm">Kata Sandi (Password) <small class="text-danger">* Wajib diisi</small></label>
            <input type="password" name="password" class="form-control" placeholder="Masukkan Kata Sandi" required>
          </div>
          <div class="form-group mb-0">
            <label class="font-weight-bold text-sm">Jam Aktif Layanan Perpustakaan</label>
            <input type="text" name="jam_aktif" class="form-control" value="07:30 - 15:30 WIB" placeholder="Contoh: 07:30 - 15:30 WIB">
            <small class="text-muted">Jadwal operasional shift pelayanan sirkulasi.</small>
          </div>
        </div>
        <div class="modal-footer bg-light py-2">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-4">
            <i class="fas fa-save mr-1"></i> Simpan Administrator
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
  // Live search filter untuk tabel administrator
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
});
</script>
