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
<html>
<head>
  <title>Reset Password</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="card mx-auto" style="max-width: 500px;">
      <div class="card-body">
        <h3 class="card-title text-center">🔐 Reset Your Password</h3>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= $error ?></div>
        <?php elseif (!empty($success)): ?>
          <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

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
</body>
</html>
