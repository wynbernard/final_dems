<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">Dashboard</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="#">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
</div>
<!--end::App Content Header-->
<!--begin::App Content-->
<div class="app-content">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <!--begin::Col-->
      <div class="col-lg-3 col-6">
        <!--begin::Small Box Widget 1-->
        <div class="small-box text-bg-primary">
          <div class="inner">
            <?php
            $query = "SELECT COUNT(*) AS total_admin FROM admin_table";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $total_admin = $row['total_admin'];
            ?>
            <h3><?php echo htmlspecialchars($total_admin) ?></h3>
            <p>Total Staff</p>
          </div>
          <svg
            class="small-box-icon"
            fill="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true">
            <path
              d="M4.619,15.479c0.888,3.39,3.752,6.513,7.382,6.513c3.684,0,6.594-3.109,7.504-6.49c0.346-0.039,0.632-0.303,0.663-0.663
				l0.115-1.336c0.029-0.348-0.189-0.646-0.506-0.756c-0.006-0.08-0.008-0.161-0.017-0.24c-0.068-3.062-0.6-5.534-3.01-6.556
				c-2.544-1.078-4.786-1.093-6.432-0.453C10.21,5.541,9.931,5.912,9.822,5.979C9.713,6.046,9.136,5.856,8.917,5.907
				c-3.61,0.516-4.801,3.917-4.538,6.569C4.371,12.55,4.366,12.625,4.36,12.7c-0.349,0.087-0.599,0.404-0.567,0.774l0.114,1.336
				C3.94,15.188,4.25,15.462,4.619,15.479z M5.388,12.833c1.581-0.579,4.622-1.79,4.952-2.426c1.383,1.437,6.267,2.244,8.411,2.513
				c0.009,0.139,0.021,0.274,0.021,0.414c0,3.525-2.958,7.623-6.771,7.623c-3.799,0-6.638-4.024-6.638-7.623
				C5.362,13.165,5.375,13,5.388,12.833z"></path>
            <path d="M17.818,20.777c-0.19-0.029-0.376,0.014-0.498,0.063l-3.041,4.113l-2.307-1.84l-0.014,0.012v0.013l-0.003-0.003
				l-2.307,1.84l-3.041-4.113c-0.121-0.05-0.308-0.093-0.498-0.064C0.364,21.608,0,34.584,0,34.584l11.969,0.008v-0.021
				l11.958-0.008C23.928,34.563,23.562,21.587,17.818,20.777z" />
          </svg>
          <a
            href="#"
            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
            More info <i class="bi bi-link-45deg"></i>
          </a>
        </div>
        <!--end::Small Box Widget 1-->
      </div>
      <!--end::Col-->
      <div class="col-lg-3 col-6">
        <!--begin::Small Box Widget 2-->
        <div class="small-box text-bg-success">
          <div class="inner">
            <?php
            $query = "SELECT COUNT(*) AS pre_reg FROM pre_reg_table";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $total_pre_reg = $row['pre_reg'];
            ?>
            <h3><?php echo htmlspecialchars($total_pre_reg) ?><sup class="fs-5"></sup></h3>
            <p>Pre-Registration</p>
          </div>
          <svg
            class="small-box-icon"
            fill="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true">
            <path
              d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v15.75c0 1.035.84 1.875 1.875 1.875h.75c1.035 0 1.875-.84 1.875-1.875V4.125c0-1.036-.84-1.875-1.875-1.875h-.75zM9.75 8.625c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-.75a1.875 1.875 0 01-1.875-1.875V8.625zM3 13.125c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v6.75c0 1.035-.84 1.875-1.875 1.875h-.75A1.875 1.875 0 013 19.875v-6.75z"></path>
          </svg>
          <a
            href="#"
            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
            More info <i class="bi bi-link-45deg"></i>
          </a>
        </div>
        <!--end::Small Box Widget 2-->
      </div>
      <!--end::Col-->
      <div class="col-lg-3 col-6">
        <!--begin::Small Box Widget 3-->
        <div class="small-box text-bg-warning">
          <div class="inner">
            <?php
            $query = "SELECT COUNT(*) AS evac_reg FROM evac_reg_table";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $total_evac_reg = $row['evac_reg'];
            ?>
            <h3><?php echo htmlspecialchars($total_evac_reg) ?></h3>
            <p>Evacuation Registration</p>
          </div>
          <svg
            class="small-box-icon"
            fill="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true">
            <path
              d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z"></path>
          </svg>
          <a
            href="#"
            class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
            More info <i class="bi bi-link-45deg"></i>
          </a>
        </div>
        <!--end::Small Box Widget 3-->
      </div>
      <!--end::Col-->
      <div class="col-lg-3 col-6">
        <!--begin::Small Box Widget 4-->
        <div class="small-box text-bg-danger">
          <div class="inner">
            <h3>65</h3>
            <p>Unique Visitors</p>
          </div>
          <svg
            class="small-box-icon"
            fill="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true">
            <path
              clip-rule="evenodd"
              fill-rule="evenodd"
              d="M2.25 13.5a8.25 8.25 0 018.25-8.25.75.75 0 01.75.75v6.75H18a.75.75 0 01.75.75 8.25 8.25 0 01-16.5 0z"></path>
            <path
              clip-rule="evenodd"
              fill-rule="evenodd"
              d="M12.75 3a.75.75 0 01.75-.75 8.25 8.25 0 018.25 8.25.75.75 0 01-.75.75h-7.5a.75.75 0 01-.75-.75V3z"></path>
          </svg>
          <a
            href="#"
            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
            More info <i class="bi bi-link-45deg"></i>
          </a>
        </div>
        <!--end::Small Box Widget 4-->
      </div>
      <div class="col-12 mb-4">
        <div class="card shadow-lg rounded border-success h-100">
          <div class="card-header bg-success text-white">
            <h3 class="card-title mb-0" style="font-size: 1.25rem;">Evacuation Room Availability</h3>
          </div>

          <div class="card-body">
            <!-- Evacuation Location Dropdown -->
            <div class="mb-3 col-md-3">
              <label for="locationSelect" class="form-label fw-bold">Select Location</label>
              <select id="locationSelect" class="form-select" onchange="fetchRoomInfo(this.value)">
                <option value="">-- Select a location --</option>
                <?php
                include '../db_connect.php';
                $res = $conn->query("SELECT evac_loc_id, name FROM evac_loc_table ORDER BY name ASC");
                while ($row = $res->fetch_assoc()) {
                  echo '<option value="' . htmlspecialchars($row['evac_loc_id']) . '">' . htmlspecialchars($row['name']) . '</option>';
                }
                ?>
              </select>
            </div>

            <!-- Room Info Display -->
            <div id="roomInfo" class="mt-3">
              <em>Please select a location to see available rooms.</em>
            </div>

            <script>
              function fetchRoomInfo(evacLocId) {
                const roomInfo = document.getElementById('roomInfo');

                if (!evacLocId) {
                  roomInfo.innerHTML = '<em>Please select a location to see available rooms.</em>';
                  return;
                }

                fetch(`../fetch_data/get_room_info.php?evac_loc_id=${encodeURIComponent(evacLocId)}`)
                  .then(res => res.json())
                  .then(data => {
                    if (data.success) {
                      const {
                        location,
                        rooms
                      } = data;

                      if (!rooms.length) {
                        roomInfo.innerHTML = `<em>No rooms found in ${location}.</em>`;
                        return;
                      }

                      const roomBoxes = rooms.map(room => `
                        <div class="room-box ${room.is_available ? 'available' : 'full'}">
                          <div class="room-name">${room.name}</div>
                          <div class="room-capacity">
                            Occupied: ${room.occupied} / ${room.capacity}<br>
                            Remaining: ${room.remaining}
                          </div>
                          <div class="room-status">
                            ${room.is_available ? '✅ Available' : '❌ Full'}
                          </div>
                        </div>
                      `).join('');

                                    roomInfo.innerHTML = `
                        <h5>${location}</h5>
                        <div class="room-box-container mt-3">${roomBoxes}</div>
                      `;
                    } else {
                      roomInfo.innerHTML = `<em>${data.message}</em>`;
                    }
                  })
                  .catch(error => {
                    console.error(error);
                    roomInfo.innerHTML = `<em>Error retrieving room data: ${error.message}</em>`;
                  });
              }
            </script>

            <div class="card-footer text-muted text-center small">
            </div>
          </div>
        </div>
        <!--end::Col-->
      </div>
    </div>
    <!--end::Container-->
  </div>
  <style>
    .room-box-container {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .room-box {
      background-color: #f1f5f9;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      padding: 1rem;
      width: 220px;
      text-align: center;
      transition: 0.3s ease;
    }

    .room-box.available {
      background-color: #dcfce7;
    }

    .room-box.full {
      background-color: #fee2e2;
    }

    .room-name {
      font-weight: bold;
      font-size: 1.1rem;
      margin-bottom: 0.5rem;
    }

    .room-capacity {
      font-size: 0.95rem;
      margin-bottom: 0.5rem;
    }

    .room-status {
      font-size: 0.9rem;
      font-weight: 600;
    }
  </style>