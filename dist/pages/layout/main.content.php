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
            $query = "SELECT COUNT(*) AS total_reservation FROM room_reservation_table";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $total_reservation = $row['total_reservation'];
            ?>
            <h3><?php echo htmlspecialchars($total_reservation) ?><sup class="fs-5"></sup></h3>
            <p>Reservation</p>
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
            <h3>44</h3>
            <p>User Registrations</p>
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
      <!--end::Col-->
    </div>
    <!--end::Row-->
    <!--begin::Row-->
    <div class="row">
      <!-- Start col -->
      <div class="col-lg-7 connectedSortable">
        <div class="weather-container">
          <div class="clouds"></div> <!-- Cloud Animation Layer -->
          <div class="card weather-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center p-4">
              <h3 class="h4 mb-0">Weather Forecast</h3>
              <span id="weather-icon" class="fs-1">☁️</span>
            </div>
            <div class="card-body text-center">
              <h4 id="weather-location" class="fw-bold"></h4>
              <p id="weather-description" class="fst-italic"></p>
              <p class="weather-temp">
                <span id="weather-temp-value"></span>°C
              </p>
              <div class="d-flex justify-content-around mt-4">
                <div class="text-center">
                  <div class="weather-info" id="weather-feels"></div>
                  <p class="small mb-0">Feels Like</p>
                </div>
                <div class="text-center">
                  <div class="weather-info" id="weather-wind"></div>
                  <p class="small mb-0">Wind Speed</p>
                </div>
                <div class="text-center">
                  <div class="weather-info" id="weather-humidity"></div>
                  <p class="small mb-0">Humidity</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /.card -->
        <!-- DIRECT CHAT -->
        <div class="card direct-chat direct-chat-primary mb-4">
          <div class="card-header">
            <h3 class="card-title">Direct Chat</h3>
            <div class="card-tools">
              <span title="3 New Messages" class="badge text-bg-primary"> 3 </span>
              <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
              </button>
              <button
                type="button"
                class="btn btn-tool"
                title="Contacts"
                data-lte-toggle="chat-pane">
                <i class="bi bi-chat-text-fill"></i>
              </button>
              <button type="button" class="btn btn-tool" data-lte-toggle="card-remove">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <!-- Conversations are loaded here -->
            <div class="direct-chat-messages">
              <!-- Message. Default to the start -->
              <div class="direct-chat-msg">
                <div class="direct-chat-infos clearfix">
                  <span class="direct-chat-name float-start"> Alexander Pierce </span>
                  <span class="direct-chat-timestamp float-end"> 23 Jan 2:00 pm </span>
                </div>
                <!-- /.direct-chat-infos -->
                <img
                  class="direct-chat-img"
                  src="../../dist/assets/img/user1-128x128.jpg"
                  alt="message user image" />
                <!-- /.direct-chat-img -->
                <div class="direct-chat-text">
                  Is this template really for free? That's unbelievable!
                </div>
                <!-- /.direct-chat-text -->
              </div>
              <!-- /.direct-chat-msg -->
              <!-- Message to the end -->
              <div class="direct-chat-msg end">
                <div class="direct-chat-infos clearfix">
                  <span class="direct-chat-name float-end"> Sarah Bullock </span>
                  <span class="direct-chat-timestamp float-start"> 23 Jan 2:05 pm </span>
                </div>
                <!-- /.direct-chat-infos -->
                <img
                  class="direct-chat-img"
                  src="../../dist/assets/img/user3-128x128.jpg"
                  alt="message user image" />
                <!-- /.direct-chat-img -->
                <div class="direct-chat-text">You better believe it!</div>
                <!-- /.direct-chat-text -->
              </div>
              <!-- /.direct-chat-msg -->
              <!-- Message. Default to the start -->
              <div class="direct-chat-msg">
                <div class="direct-chat-infos clearfix">
                  <span class="direct-chat-name float-start"> Alexander Pierce </span>
                  <span class="direct-chat-timestamp float-end"> 23 Jan 5:37 pm </span>
                </div>
                <!-- /.direct-chat-infos -->
                <img
                  class="direct-chat-img"
                  src="../../dist/assets/img/user1-128x128.jpg"
                  alt="message user image" />
                <!-- /.direct-chat-img -->
                <div class="direct-chat-text">
                  Working with AdminLTE on a great new app! Wanna join?
                </div>
                <!-- /.direct-chat-text -->
              </div>
              <!-- /.direct-chat-msg -->
              <!-- Message to the end -->
              <div class="direct-chat-msg end">
                <div class="direct-chat-infos clearfix">
                  <span class="direct-chat-name float-end"> Sarah Bullock </span>
                  <span class="direct-chat-timestamp float-start"> 23 Jan 6:10 pm </span>
                </div>
                <!-- /.direct-chat-infos -->
                <img
                  class="direct-chat-img"
                  src="../../dist/assets/img/user3-128x128.jpg"
                  alt="message user image" />
                <!-- /.direct-chat-img -->
                <div class="direct-chat-text">I would love to.</div>
                <!-- /.direct-chat-text -->
              </div>
              <!-- /.direct-chat-msg -->
            </div>
            <!-- /.direct-chat-messages-->
            <!-- Contacts are loaded here -->
            <div class="direct-chat-contacts">
              <ul class="contacts-list">
                <li>
                  <a href="#">
                    <img
                      class="contacts-list-img"
                      src="../../dist/assets/img/user1-128x128.jpg"
                      alt="User Avatar" />
                    <div class="contacts-list-info">
                      <span class="contacts-list-name">
                        Count Dracula
                        <small class="contacts-list-date float-end"> 2/28/2023 </small>
                      </span>
                      <span class="contacts-list-msg"> How have you been? I was... </span>
                    </div>
                    <!-- /.contacts-list-info -->
                  </a>
                </li>
                <!-- End Contact Item -->
                <li>
                  <a href="#">
                    <img
                      class="contacts-list-img"
                      src="../../dist/assets/img/user7-128x128.jpg"
                      alt="User Avatar" />
                    <div class="contacts-list-info">
                      <span class="contacts-list-name">
                        Sarah Doe
                        <small class="contacts-list-date float-end"> 2/23/2023 </small>
                      </span>
                      <span class="contacts-list-msg"> I will be waiting for... </span>
                    </div>
                    <!-- /.contacts-list-info -->
                  </a>
                </li>
                <!-- End Contact Item -->
                <li>
                  <a href="#">
                    <img
                      class="contacts-list-img"
                      src="../../dist/assets/img/user3-128x128.jpg"
                      alt="User Avatar" />
                    <div class="contacts-list-info">
                      <span class="contacts-list-name">
                        Nadia Jolie
                        <small class="contacts-list-date float-end"> 2/20/2023 </small>
                      </span>
                      <span class="contacts-list-msg"> I'll call you back at... </span>
                    </div>
                    <!-- /.contacts-list-info -->
                  </a>
                </li>
                <!-- End Contact Item -->
                <li>
                  <a href="#">
                    <img
                      class="contacts-list-img"
                      src="../../dist/assets/img/user5-128x128.jpg"
                      alt="User Avatar" />
                    <div class="contacts-list-info">
                      <span class="contacts-list-name">
                        Nora S. Vans
                        <small class="contacts-list-date float-end"> 2/10/2023 </small>
                      </span>
                      <span class="contacts-list-msg"> Where is your new... </span>
                    </div>
                    <!-- /.contacts-list-info -->
                  </a>
                </li>
                <!-- End Contact Item -->
                <li>
                  <a href="#">
                    <img
                      class="contacts-list-img"
                      src="../../dist/assets/img/user6-128x128.jpg"
                      alt="User Avatar" />
                    <div class="contacts-list-info">
                      <span class="contacts-list-name">
                        John K.
                        <small class="contacts-list-date float-end"> 1/27/2023 </small>
                      </span>
                      <span class="contacts-list-msg"> Can I take a look at... </span>
                    </div>
                    <!-- /.contacts-list-info -->
                  </a>
                </li>
                <!-- End Contact Item -->
                <li>
                  <a href="#">
                    <img
                      class="contacts-list-img"
                      src="../../dist/assets/img/user8-128x128.jpg"
                      alt="User Avatar" />
                    <div class="contacts-list-info">
                      <span class="contacts-list-name">
                        Kenneth M.
                        <small class="contacts-list-date float-end"> 1/4/2023 </small>
                      </span>
                      <span class="contacts-list-msg"> Never mind I found... </span>
                    </div>
                    <!-- /.contacts-list-info -->
                  </a>
                </li>
                <!-- End Contact Item -->
              </ul>
              <!-- /.contacts-list -->
            </div>
            <!-- /.direct-chat-pane -->
          </div>
          <!-- /.card-body -->
          <div class="card-footer">
            <form action="#" method="post">
              <div class="input-group">
                <input
                  type="text"
                  name="message"
                  placeholder="Type Message ..."
                  class="form-control" />
                <span class="input-group-append">
                  <button type="button" class="btn btn-primary">Send</button>
                </span>
              </div>
            </form>
          </div>
          <!-- /.card-footer-->
        </div>
        <!-- /.direct-chat -->
      </div>
      <!-- /.Start col -->
      <!-- Start col -->
      <div class="col-lg-5 connectedSortable">
        <div class="card text-white bg-primary bg-gradient border-primary mb-4">
          <div class="card-header border-0">
            <h3 class="card-title">Sales Value</h3>
            <div class="card-tools">
              <button
                type="button"
                class="btn btn-primary btn-sm"
                data-lte-toggle="card-collapse">
                <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <div id="world-map" style="height: 220px"></div>
          </div>
          <div class="card-footer border-0">
            <!--begin::Row-->
            <div class="row">
              <div class="col-4 text-center">
                <div id="sparkline-1" class="text-dark"></div>
                <div class="text-white">Visitors</div>
              </div>
              <div class="col-4 text-center">
                <div id="sparkline-2" class="text-dark"></div>
                <div class="text-white">Online</div>
              </div>
              <div class="col-4 text-center">
                <div id="sparkline-3" class="text-dark"></div>
                <div class="text-white">Sales</div>
              </div>
            </div>
            <!--end::Row-->
          </div>
        </div>
      </div>
      <!-- /.Start col -->
    </div>
    <!-- /.row (main row) -->
  </div>
  <!--end::Container-->
</div>