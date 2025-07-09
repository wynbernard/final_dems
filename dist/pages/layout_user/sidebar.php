<!-- Sidebar for Desktop -->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">


  <!-- Sidebar Brand -->
  <div class="sidebar-brand">
    <a href="./Dashboard.php" class="brand-link">
      <img src="../../../src/images/logo/logo.png" alt="AdminLTE Logo" class="brand-image opacity-75 shadow" />
      <span class="brand-text fw-light">DEM System</span>
    </a>
  </div>

  <!-- Sidebar Menu for Desktop -->
  <div class="sidebar-wrapper">
    <nav class="mt-2">
      <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
        <li class="nav-item">
          <a href="./dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="nav-icon bi bi-speedometer"></i>
            <p>Dashboard</p>
          </a>
        </li>
        <?php
        if ($_SESSION['registered_as'] == 'Family'): ?>
          <li class="nav-item">
            <a href="./family.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'family.php' ? 'active' : ''; ?>">
              <i class="nav-icon bi bi-gear"></i>
              <p>Family Members</p>
            </a>
          </li>
        <?php else: ?>
          <!-- solo -->
        <?php endif; ?>

        <li class="nav-item">
          <a href="./room_reservation.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'room_reservation.php' ? 'active' : ''; ?>">
            <i class="nav-icon bi bi-calendar-check"></i>
            <p>Room Reservation</p>
          </a>
        </li>
      </ul>
    </nav>
  </div>
</aside>

<style>
  .nav-link.active {
    background: linear-gradient(145deg, #0062cc, #007bff);
    color: white !important;
    border-radius: 8px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    box-shadow:
      4px 4px 8px rgba(0, 0, 0, 0.3),
      -2px -2px 6px rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
    transition: all 0.3s ease-in-out;
  }

  .nav-link.active:hover {
    background: linear-gradient(145deg, #004c99, #0056b3);
    box-shadow:
      6px 6px 12px rgba(0, 0, 0, 0.4),
      -3px -3px 8px rgba(255, 255, 255, 0.1);
    transform: translateY(-1px);
  }
</style>