<?php
// Login View
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$base = (new Controller())->baseUrl();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Perpustakaan SMP Negeri 1 Kota Gajah</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- Theme style: AdminLTE -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

  <style>
    body {
      background-color: #f4f6f9;
      height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }
    .login-header-title {
      font-size: 2.2rem;
      font-weight: 700;
      color: #343a40;
      letter-spacing: 2px;
      margin-bottom: 25px;
      text-transform: uppercase;
    }
    .login-box {
      width: 420px;
      max-width: 95%;
    }
    .card {
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      border: none;
    }
    .card-body {
      padding: 35px 30px;
    }
    .logo-container {
      width: 100px;
      height: 100px;
      margin: 0 auto 20px auto;
    }
    .btn-masuk {
      background-color: #007bff;
      border-color: #007bff;
      color: #ffffff;
      font-weight: 600;
      padding: 10px;
      border-radius: 5px;
    }
    .btn-masuk:hover {
      background-color: #0069d9;
      color: #ffffff;
    }
    .btn-daftar {
      background-color: #f39c12;
      border-color: #e08e0b;
      color: #ffffff;
      font-weight: 600;
      padding: 10px;
      border-radius: 5px;
    }
    .btn-daftar:hover {
      background-color: #d68910;
      color: #ffffff;
    }
    .separator-text {
      text-align: center;
      margin: 15px 0;
      color: #888;
      font-size: 0.9rem;
      font-weight: 600;
    }
  </style>
</head>
<body>

<div class="login-header-title text-center">
  <span style="font-size: 1.8rem; display: block; font-weight: 800; color: #007bff;">PERPUSTAKAAN</span>
  <span style="font-size: 1.1rem; display: block; font-weight: 600; color: #6c757d; letter-spacing: 1px;">PORTAL PUSTAKAWAN & ADMIN</span>
</div>

<div class="login-box">
  <?php if (!empty($flash)): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($flash['message']) ?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">
      <!-- School Logo -->
      <div class="logo-container">
        <img src="<?= $base ?>assets/img/logo.svg" alt="Logo SMP Negeri 1 Kota Gajah" style="width:100%; height:100%;">
      </div>

      <!-- Form Login -->
      <form action="<?= $base ?>login" method="post">
        <div class="input-group mb-3">
          <input type="text" name="username" class="form-control" placeholder="Nama Pengguna" required autofocus>
          <div class="input-group-append">
            <div class="input-group-text bg-white">
              <span class="fas fa-user text-muted"></span>
            </div>
          </div>
        </div>

        <div class="input-group mb-4">
          <input type="password" name="password" class="form-control" placeholder="Kata Sandi" required>
          <div class="input-group-append">
            <div class="input-group-text bg-white">
              <span class="fas fa-lock text-muted"></span>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-masuk btn-block">Masuk</button>
          </div>
        </div>

        <div class="separator-text">- ATAU -</div>

        <div class="row">
          <div class="col-12">
            <a href="<?= $base ?>katalog" class="btn btn-outline-primary btn-block font-weight-bold">
              <i class="fas fa-book-open mr-1"></i> Buka Katalog Buku (Siswa / Guest)
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="text-center text-muted text-sm mt-3">
    Perpustakaan <?= htmlspecialchars($identitas['nama_app'] ?? 'SMP Negeri 1 Kota Gajah') ?>
  </div>
</div>

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

