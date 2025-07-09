<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
  <!--begin::Sidebar Brand-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <div class="sidebar-brand">
    <!--begin::Brand Link-->
    <a href="./Dashboard.php" class="brand-link">
      <!--begin::Brand Image-->
      <img
        src="../../../src/images/logo/logo.png"
        alt="AdminLTE Logo"
        class="brand-image opacity-75 shadow" />
      <!--end::Brand Image-->
      <!--begin::Brand Text-->
      <span class="brand-text fw-light">DEM System</span>
      <!--end::Brand Text-->
    </a>
    <!--end::Brand Link-->
  </div>
  <style>
    .nav-link.active {
      background: linear-gradient(145deg, #0062cc, #007bff);
      /* Gradient for depth */
      color: white !important;
      border-radius: 8px;
      /* Slightly rounded corners */
      border: 2px solid rgba(255, 255, 255, 0.2);
      /* Subtle border for contrast */
      box-shadow:
        4px 4px 8px rgba(0, 0, 0, 0.3),
        /* Outer shadow for depth */
        -2px -2px 6px rgba(255, 255, 255, 0.1);
      /* Inner shadow for 3D effect */
      transform: translateY(-2px);
      /* Slight upward shift for "pressed" effect */
      transition: all 0.3s ease-in-out;
    }

    .nav-link.active:hover {
      background: linear-gradient(145deg, #004c99, #0056b3);
      /* Darker gradient on hover */
      box-shadow:
        6px 6px 12px rgba(0, 0, 0, 0.4),
        /* Deeper outer shadow */
        -3px -3px 8px rgba(255, 255, 255, 0.1);
      /* Enhanced inner shadow */
      transform: translateY(-1px);
      /* Slight adjustment for hover state */
    }
  </style>


  <!--end::Sidebar Brand-->
  <!--begin::Sidebar Wrapper-->
  <div class="sidebar-wrapper">
    <nav class="mt-2">
      <!--begin::Sidebar Menu-->
      <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'menu-open' : ''; ?>">
          <a href="./dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" title="Dashboard">
            <i class="nav-icon bi bi-speedometer"></i>
            <p>Dashboard</p>
          </a>
        </li>
        <!-- Admin Access -->
        <?php
        if ($_SESSION['role'] == 'Admin') { ?>
          <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_user.php' ? 'menu-open' : ''; ?>">
            <a href="./admin_user.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_user.php' ? 'active' : ''; ?>">
              <i class="bi bi-person-gear nav-icon"></i>
              <p>Admin User's</p>
            </a>
          </li>
        <?php } ?>
        <li class="nav-item has-treeview
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        echo (in_array($current_page, ['idps_user.php', 'pre_reg.php', 'idps_log.php', 'resource_distribution.php'])) ? 'menu-open' : '';
        ?>">
          <a href="#" class="nav-link
          <?php
          echo (in_array($current_page, ['idps_user.php', 'pre_reg.php', 'idps_log.php', 'resource_distribution.php'])) ? 'active' : '';
          ?>">
            <i class="nav-icon bi bi-people-fill"></i>
            <p>
              IDPs Management
              <i class="right bi bi-chevron-down"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="./idps_user.php" class="nav-link <?php echo ($current_page == 'idps_user.php') ? 'active' : ''; ?>">
                <i class="fas fa-people-roof nav-icon"></i>
                <p>Registrations</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="./pre_reg.php" class="nav-link <?php echo ($current_page == 'pre_reg.php') ? 'active' : ''; ?>">
                <i class="bi bi-journal-text nav-icon"></i>
                <p>Pre-Registration</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="./idps_log.php" class="nav-link <?php echo ($current_page == 'idps_log.php') ? 'active' : ''; ?>">
                <i class="bi bi-journal-text nav-icon"></i>
                <p>Logs</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="./resource_distribution.php" class="nav-link <?php echo ($current_page == 'resource_distribution.php') ? 'active' : ''; ?>">
                <i class="bi bi-box-seam nav-icon"></i>
                <p>Distribution</p>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'disaster.php' ? 'menu-open' : ''; ?>">
          <a href="./disaster.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'disaster.php' ? 'active' : ''; ?>">
            <i class="nav-icon bi bi-cloud-lightning-rain"></i> <!-- Warning/Danger Icon -->
            <p>Kind of Disaster</p>
          </a>
        </li>
        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'loc_management.php' ? 'menu-open' : ''; ?>">
          <a href="./loc_management.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'loc_management.php' ? 'active' : ''; ?>">
            <i class="nav-icon bi bi-map"></i><!-- Location Pin Icon -->
            <p>Location Management</p>
          </a>
        </li>
        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'barangay_management.php' ? 'menu-open' : ''; ?>">
          <a href="./barangay_management.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'barangay_management.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-city"></i><!-- Location Pin Icon -->
            <p>Barangay Management</p>
          </a>
        </li>

        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'resource_inventory.php' ? 'menu-open' : ''; ?>">
          <a href="./resource_inventory.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'resource_inventory.php' ? 'active' : ''; ?>">
            <i class="nav-icon bi bi-boxes"></i>
            <p>Resources Inventory</p>
          </a>
        </li>
      </ul>
      <!--end::Sidebar Menu-->
    </nav>
  </div>
  <!--end::Sidebar Wrapper-->

</aside>
<!-- Quick Launch Mini Icons (visible only when sidebar is collapsed) -->
<!-- <div id="quickSidebarIcons" style="
  position: fixed;
  top: 100px;
  left: 0;
  z-index: 1050;
  background: #0d6efd;
  border-radius: 0 10px 10px 0;
  padding: 8px 6px;
  display: none;
  box-shadow: 2px 2px 8px rgba(0,0,0,0.2);
">
  <a href="dashboard.php" class="text-white d-block mb-3 text-center" title="Dashboard">
    <i class="bi bi-speedometer2 fs-5"></i>
  </a>
  <a href="admin_user.php" class="text-white d-block mb-3 text-center" title="Admin Member">
    <i class="bi bi-shield-lock fs-5"></i>
  </a>
  <a href="idps_user.php" class="text-white d-block mb-3 text-center" title="IDPs Registration">
    <i class="bi bi-people-fill fs-5"></i>
  </a>
  <a href="pre_reg.php" class="text-white d-block mb-3 text-center" title="Pre-Registration">
    <i class="bi bi-pencil-square fs-5"></i>
  </a>
  <a href="location_reservation.php" class="text-white d-block mb-3 text-center" title="Reservation">
    <i class="bi bi-calendar-check fs-5"></i>
  </a>
  <a href="idps_log.php" class="text-white d-block mb-3 text-center" title="IDPs Logs">
    <i class="bi bi-journal-text fs-5"></i>
  </a>
</div>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const quickSidebarIcons = document.getElementById('quickSidebarIcons');

    function updateQuickIcons() {
      if (body.classList.contains('sidebar-collapse')) {
        quickSidebarIcons.style.display = 'block';
      } else {
        quickSidebarIcons.style.display = 'none';
      }
    }

    // Initial state check
    updateQuickIcons();

    // Watch for sidebar toggle
    document.querySelectorAll('[data-lte-toggle="sidebar"]').forEach(btn => {
      btn.addEventListener('click', () => {
        setTimeout(updateQuickIcons, 200); // Wait for class update
      });
    });
  });
</script> -->