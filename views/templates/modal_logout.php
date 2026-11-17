<?php
// Modal Logout Template
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$base = (new Controller())->baseUrl();
?>
<!-- Modal Logout -->
<div class="modal fade" id="modalLogout" tabindex="-1" role="dialog" aria-labelledby="modalLogoutLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header modal-header-warning">
        <h5 class="modal-title font-weight-bold" id="modalLogoutLabel">Peringatan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center py-4">
        <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
        <h6 class="font-weight-bold">Apa anda yakin ingin keluar dari Applikasi ?</h6>
        <p class="text-muted mb-0">Anda harus login kembali jika ingin masuk Applikasi Perpustakaan</p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-danger px-4" data-dismiss="modal">Batal</button>
        <a href="<?= $base ?>logout" class="btn btn-primary px-4">Iya, Logout</a>
      </div>
    </div>
  </div>
</div>

