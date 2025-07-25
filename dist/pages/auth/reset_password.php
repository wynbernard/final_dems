<?php
// File-based cache location
$cacheDir = __DIR__ . '/cache';
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

// Validate input
if (!$email || !$token) {
    die("❌ Invalid reset link.");
}

$cacheFile = $cacheDir . '/' . base64_encode($email) . '.json';

if (!file_exists($cacheFile)) {
    die("❌ Token not found or already used.");
}

$data = json_decode(file_get_contents($cacheFile), true);

// Check token match
if ($data['token'] !== $token) {
    die("❌ Invalid token.");
}

// Check expiration
if (strtotime($data['expires']) < time()) {
    unlink($cacheFile);
    die("⏰ Token expired.");
}

// Handle password reset
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newPassword = $_POST['password'] ?? '';
    $confirm     = $_POST['confirm'] ?? '';

    if (strlen($newPassword) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($newPassword !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // ✅ Save new password to database (adjust table name & field)
        include '../../../database/conn.php';
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE pre_reg_table SET password = ? WHERE email_address = ?");
        $stmt->bind_param("ss", $hashed, $email);
        $stmt->execute();

        // ✅ Remove used token
        unlink($cacheFile);

        $success = "✅ Password successfully updated!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reset Password</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="card mx-auto shadow" style="max-width: 100%; width: 100%; max-width: 480px;">
      <div class="card-body">
        <h3 class="card-title text-center mb-4">🔐 Reset Your Password</h3>

        <?php
          // These would typically be set after form processing
          $error = $error ?? '';
          $success = $success ?? '';
        ?>

        <?php if (empty($success)): ?>
        <form method="POST">
          <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <div class="mb-3">
            <label for="confirm" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" name="confirm" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Update Password</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
    <?php if (!empty($error)): ?>
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: <?= json_encode($error) ?>,
        confirmButtonColor: '#d33'
      });
    <?php elseif (!empty($success)): ?>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: <?= json_encode($success) ?>,
        confirmButtonColor: '#3085d6'
      });
    <?php endif; ?>
  </script>
</body>
</html>

