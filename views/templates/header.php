<?php
// Header Template
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$pageTitle = $pageTitle ?? 'Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah';
$currentUser = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- Theme style: AdminLTE -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

  <style>
    /* Styling to match exact Skripsi appearance */
    .main-header {
      background-color: #007bff !important;
      border-bottom: none;
    }
    .main-header .navbar-nav .nav-link,
    .main-header .navbar-brand {
      color: #ffffff !important;
    }
    .main-sidebar {
      background-color: #2c343b !important;
    }
    .brand-link {
      background-color: #232a30 !important;
      color: #ffffff !important;
      font-weight: bold;
      border-bottom: 1px solid #3d464d;
    }
    .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active,
    .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link:hover {
      background-color: #007bff !important;
      color: #ffffff !important;
    }
    .small-box .inner h3 {
      font-size: 2.4rem;
      font-weight: bold;
    }
    .badge-verified {
      background-color: #28a745;
      color: #fff;
      font-size: 11px;
      padding: 3px 8px;
      border-radius: 12px;
    }
    .table thead th {
      background-color: #f4f6f9;
      color: #495057;
      border-top: none;
    }
    .card-header {
      background-color: #ffffff;
      border-bottom: 1px solid #dee2e6;
    }
    .modal-header-warning {
      background-color: #f8f9fa;
      border-bottom: 1px solid #dee2e6;
    }
    @media print {
      .main-sidebar, .main-header, .main-footer, .no-print {
        display: none !important;
      }
      .content-wrapper {
        margin-left: 0 !important;
        padding: 0 !important;
      }
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

