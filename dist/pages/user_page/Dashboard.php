<!doctype html>
<html lang="en" data-bs-theme="light">
<!--begin::Head-->
<?php
include '../../../database/user_session.php';
include '../layout_user/head_links.php';
?>
<!--end::Head-->

<!--begin::Body-->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <!-- Error Notification -->
  <?php if (isset($_SESSION['error'])): ?>
    <div id="errorAlert" class="alert-toast position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 1100;">
      <div class="alert alert-danger alert-dismissible fade show shadow-lg" role="alert">
        <div class="d-flex align-items-center">
          <i class="fas fa-exclamation-circle me-2 fs-4"></i>
          <strong class="me-auto">Error</strong>
          <small>Just now</small>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div class="mt-2"><?php echo $_SESSION['error']; ?></div>
      </div>
    </div>
    <?php unset($_SESSION['error']); ?>

    <script>
      document.addEventListener("DOMContentLoaded", function() {
        let errorAlert = document.querySelector('.alert-toast .alert');
        if (errorAlert) {
          // Show alert with slide-down animation
          errorAlert.style.transform = 'translateY(0)';
          errorAlert.style.opacity = '1';

          // Auto-dismiss after 5 seconds
          setTimeout(() => {
            const bsAlert = new bootstrap.Alert(errorAlert);
            bsAlert.close();
          }, 5000);
        }
      });
    </script>
  <?php endif; ?>

  <!-- Main App Wrapper -->
  <div class="app-wrapper">
    <!-- Header -->
    <?php include '../layout_user/header.php'; ?>

    <!-- Sidebar -->
    <?php include '../layout_user/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="app-main">
      <div class="app-content">
        <div class="container-fluid">
          <?php include '../layout_user/main.content.php'; ?>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <?php include '../layout_user/footer.php'; ?>
  </div>

  <!-- Scripts -->
  <?php include '../scripts/scripts.php';
  include '../css/user_dashboard.php';
  ?>

  <script>
    // Add smooth page transitions
    document.addEventListener('DOMContentLoaded', function() {
      // Enable tooltips everywhere
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });

      // Add active class to current nav item
      const currentPath = window.location.pathname.split('/').pop();
      document.querySelectorAll('.nav-link').forEach(link => {
        if (link.getAttribute('href') === currentPath) {
          link.classList.add('active');
        }
      });
    });
  </script>
</body>
<!--end::Body-->

</html>