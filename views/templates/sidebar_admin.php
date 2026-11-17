<?php
// Sidebar Admin Template
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$activeMenu = $activeMenu ?? 'dashboard';
$base = (new Controller())->baseUrl();
$user = $_SESSION['user'] ?? ['fullname' => 'Administrator'];
?>
<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="<?= $base ?>admin/dashboard" class="brand-link">
    <i class="fas fa-book-reader ml-3 mr-2"></i>
    <span class="brand-text font-weight-light">PERPUSTAKAAN</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user panel -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
      <div class="image">
        <img src="<?= $base ?>assets/img/avatar.svg" class="img-circle elevation-2" alt="User Image" style="width:36px; height:36px;">
      </div>
      <div class="info">
        <a href="#" class="d-block font-weight-bold"><?= htmlspecialchars($user['fullname']) ?></a>
        <small class="text-success"><i class="fas fa-check-circle"></i> Akun Terverifikasi</small>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        
        <!-- Dashboard -->
        <li class="nav-item">
          <a href="<?= $base ?>admin/dashboard" class="nav-link <?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <!-- Sirkulasi Perpustakaan (Peminjaman, Pengembalian & Riwayat Total) -->
        <?php $isSirkulasi = in_array($activeMenu, ['peminjaman', 'pengembalian', 'riwayat']); ?>
        <li class="nav-item <?= $isSirkulasi ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= $isSirkulasi ? 'active' : '' ?>">
            <i class="nav-icon fas fa-exchange-alt"></i>
            <p>
              Sirkulasi Meja Pelayanan
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= $base ?>admin/peminjaman" class="nav-link <?= $activeMenu === 'peminjaman' ? 'active' : '' ?>">
                <i class="fas fa-barcode nav-icon text-primary"></i>
                <p>Peminjaman (Scan)</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= $base ?>admin/pengembalian" class="nav-link <?= $activeMenu === 'pengembalian' ? 'active' : '' ?>">
                <i class="fas fa-undo-alt nav-icon text-success"></i>
                <p>Pengembalian (Scan)</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= $base ?>admin/riwayat" class="nav-link <?= $activeMenu === 'riwayat' ? 'active' : '' ?>">
                <i class="fas fa-history nav-icon text-info"></i>
                <p>Laporan Riwayat Total</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Master Data -->
        <?php $isMaster = in_array($activeMenu, ['anggota', 'penerbit', 'pengarang', 'administrator']); ?>
        <li class="nav-item <?= $isMaster ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= $isMaster ? 'active' : '' ?>">
            <i class="nav-icon fas fa-folder"></i>
            <p>
              Master Data
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= $base ?>admin/anggota" class="nav-link <?= $activeMenu === 'anggota' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Data Anggota (Siswa)</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= $base ?>admin/penerbit" class="nav-link <?= $activeMenu === 'penerbit' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Data Penerbit</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= $base ?>admin/pengarang" class="nav-link <?= $activeMenu === 'pengarang' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Data Pengarang</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= $base ?>admin/administrator" class="nav-link <?= $activeMenu === 'administrator' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Administrator</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Katalog Buku -->
        <?php $isKatalog = in_array($activeMenu, ['buku', 'kategori']); ?>
        <li class="nav-item <?= $isKatalog ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= $isKatalog ? 'active' : '' ?>">
            <i class="nav-icon fas fa-book"></i>
            <p>
              Katalog Buku
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= $base ?>admin/buku" class="nav-link <?= $activeMenu === 'buku' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Data Buku</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= $base ?>admin/kategori" class="nav-link <?= $activeMenu === 'kategori' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Kategori Buku</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Identitas Aplikasi -->
        <li class="nav-item">
          <a href="<?= $base ?>admin/identitas" class="nav-link <?= $activeMenu === 'identitas' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-info-circle"></i>
            <p>Identitas Aplikasi</p>
          </a>
        </li>

        <!-- Diagram & Pemodelan Basis Data -->
        <li class="nav-item">
          <a href="<?= $base ?>diagram_perpustakaan.html" target="_blank" class="nav-link text-warning">
            <i class="nav-icon fas fa-project-diagram"></i>
            <p>Diagram & ERD <span class="badge badge-warning right">3NF</span></p>
          </a>
        </li>

        <!-- Katalog Publik OPAC (Siswa / Guest) -->
        <li class="nav-item">
          <a href="<?= $base ?>katalog" target="_blank" class="nav-link text-info">
            <i class="nav-icon fas fa-globe"></i>
            <p>Katalog Publik (OPAC) <span class="badge badge-info right">Kiosk</span></p>
          </a>
        </li>

        <!-- Keluar -->
        <li class="nav-item">
          <a href="#" class="nav-link" data-toggle="modal" data-target="#modalLogout">
            <i class="nav-icon fas fa-sign-out-alt text-danger"></i>
            <p>Keluar</p>
          </a>
        </li>

      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>

