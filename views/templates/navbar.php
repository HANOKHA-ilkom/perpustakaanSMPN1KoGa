<?php
// Navbar Template
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$currentUser = $_SESSION['user'] ?? ['fullname' => 'Pengguna', 'role' => 'Guest'];
?>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-dark">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <span class="nav-link font-weight-bold">PERPUSTAKAAN SMPN 1 KOTA GAJAH</span>
    </li>
  </ul>

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">
    <li class="nav-item d-none d-md-inline-block">
      <?php $navBase = (new Controller())->baseUrl(); ?>
      <a class="nav-link text-info font-weight-bold" href="<?= $navBase ?>katalog" target="_blank" title="Buka Katalog Publik OPAC (Tampilan Siswa/Guest)">
        <i class="fas fa-globe mr-1"></i> Katalog Publik (OPAC)
      </a>
    </li>
    <li class="nav-item d-none d-md-inline-block">
      <a class="nav-link text-warning font-weight-bold" href="<?= $navBase ?>diagram_perpustakaan.html" target="_blank" title="Buka Dokumentasi Diagram, ERD & Primary Key">
        <i class="fas fa-project-diagram mr-1"></i> Diagram & ERD
      </a>
    </li>
    <li class="nav-item">
      <?php $navBase = (new Controller())->baseUrl(); ?>
      <?php if (strcasecmp($currentUser['role'] ?? '', 'Administrator') === 0 || strcasecmp($currentUser['role'] ?? '', 'Admin') === 0): ?>
        <a class="nav-link" href="<?= $navBase ?>admin/peminjaman" title="Notifikasi Peminjaman">
          <i class="far fa-bell"></i>
          <span class="badge badge-warning navbar-badge font-weight-bold" id="navbarPendingBadge" style="display:none;">0</span>
        </a>
      <?php else: ?>
        <a class="nav-link" href="<?= $navBase ?>katalog" title="Katalog Koleksi">
          <i class="far fa-bell"></i>
        </a>
      <?php endif; ?>
    </li>
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
        <i class="fas fa-user-circle mr-1"></i>
        <span><?= htmlspecialchars($currentUser['fullname']) ?></span>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-header"><?= htmlspecialchars($currentUser['role']) ?></span>
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item" data-toggle="modal" data-target="#modalLogout">
          <i class="fas fa-sign-out-alt mr-2 text-danger"></i> Keluar
        </a>
      </div>
    </li>
  </ul>
</nav>
<!-- /.navbar -->

