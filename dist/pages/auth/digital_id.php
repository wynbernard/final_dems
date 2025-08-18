<?php
include '../../../database/conn.php';
include '../layout/head_links.php';

$showLogin = true;
$result = null;
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check credentials
    $sql = "SELECT prt.pre_reg_id, prt.f_name, prt.m_name, prt.l_name, prt.name_ext, prt.gender AS gender, prt.date_of_birth, prt.profile_pic AS photo, prt.password, er.evac_reg_id, qr.code AS code
            FROM pre_reg_table prt
            JOIN evac_reg_table er ON er.pre_reg_id = prt.pre_reg_id
            LEFT JOIN qr_table qr ON prt.qr_id = qr.qr_id
            WHERE prt.email_address = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    // If you store hashed passwords, use password_verify
    if ($user && password_verify($password, $user['password'])) {
        $showLogin = false;
        $result = $user;
    } else {
        $error = "Invalid email or password.";
    }

   
    $userPhoto  = $user['photo'] ?? '';
    $gender = $result['gender'] ?? '';

    // Directory paths
    $userDir    = "uploads/photos/";   // for uploaded images
    $defaultDir = "src/images/";       // for default male/female

    // If user has uploaded a photo → use it
    if (!empty($userPhoto)) {
        $photoPath = $userDir . $userPhoto;
    } else {
      if ($gender === 'Female') {
          $photoPath = $defaultDir . "female_default_profile.jpg";
      } else {
          $photoPath = $defaultDir . "male_default_profile.jpg";
      }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Evacuee Digital ID</title>
  <script src="qrcode.min.js"></script>
</head>
<body class="bg-light d-flex justify-content-center align-items-center min-vh-100">

  <?php if ($showLogin): ?>
    <!-- Login Card -->
    <div class="card shadow-sm p-4 mx-2 mx-sm-auto" style="max-width: 400px; width: 100%;">
      <h2 class="text-center mb-3">Evacuee Login</h2>
      <?php if ($error): ?>
        <p class="text-danger text-center"><?php echo $error; ?></p>
      <?php endif; ?>
      <form method="post">
        <div class="mb-3">
          <input type="email" class="form-control" name="email" placeholder="Enter Email" required>
        </div>
        <div class="mb-3">
          <input type="password" class="form-control" name="password" placeholder="Enter Password" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Login</button>
      </form>
    </div>

  <?php else: ?>
    <!-- ID Card -->
    <div class="card shadow-lg mx-2 mx-sm-auto" style="max-width: 420px; width: 100%; border-radius: 12px; overflow: hidden;">
      
      <!-- Header -->
      <div class="card-header bg-success text-white py-3 text-center">
        <h5 class="mb-0">EVACUATION DIGITAL ID</h5>
      </div>
      
      <!-- Body -->
      <div class="card-body">
        <div class="row align-items-center">
          <!-- Profile Photo (Left) -->
          <div class="col-4 text-center">
            <img src="<?php echo htmlspecialchars("../../../" . $photoPath); ?>" 
                 alt="Profile Photo" 
                 class="rounded-circle border border-3 border-success img-fluid"
                 style="width: 100px; height: 100px; object-fit: cover;">
          </div>

          <!-- Details (Right) -->
          <div class="col-8 text-start">
            <h6 class="fw-bold mb-1">
              <?php echo htmlspecialchars($result['f_name']." ".$result['m_name']." ".$result['l_name']." ".$result['name_ext']); ?>
            </h6>
            <p class="mb-1"><strong>DOB:</strong> <?php echo htmlspecialchars($result['date_of_birth']); ?></p>
            <p class="mb-0"><strong>ID CODE:</strong> DEMS-000<?php echo !empty($result['pre_reg_id']) ? htmlspecialchars($result['pre_reg_id']) : '0'; ?></p>
          </div>
        </div>
      </div>

      <!-- Footer with Big QR -->
      <div class="card-footer bg-light text-center">
        <div id="qrcode" class="mb-2">
          <img src="<?php echo htmlspecialchars("../../../" . $result['code']); ?>" 
               alt="QR Code" 
               class="img-fluid"
               style="width: 75%; max-width: 250px;">
        </div>
        <p class="small text-muted mb-0">Scan for Verification</p>
      </div>
    </div>
  <?php endif; ?>

</body>


</html>
