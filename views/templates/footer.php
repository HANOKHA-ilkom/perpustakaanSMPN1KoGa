<?php
// Footer Template
// Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
$identitas = (new PerpusModel())->getIdentitas();
?>
  <!-- Main Footer -->
  <footer class="main-footer text-sm text-center">
    <strong>Copyright &copy; 2024 <a href="#"><?= htmlspecialchars($identitas['nama_app']) ?></a>.</strong>
    All rights reserved.
  </footer>
</div>
<!-- ./wrapper -->

<?php require_once __DIR__ . '/modal_logout.php'; ?>

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- Search Table Helper Script -->
<script>
  $(document).ready(function(){
    $("#tableSearch").on("keyup", function() {
      var value = $(this).val().toLowerCase();
      $("#dataTable tbody tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
      });
    });
  });
</script>
</body>
</html>

