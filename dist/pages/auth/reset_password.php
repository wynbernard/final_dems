<?php
require '../../../database/conn.php';

$token = $_GET['token'] ?? '';
if (!$token) die("Invalid reset link.");

$data = json_decode(base64_decode($token), true);
if (!$data || !isset($data['email'], $data['exp'])) die("Invalid token format.");

if (time() > $data['exp']) die("This reset link has expired.");

$email = $data['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($pass) < 6) {
        echo "<p>Password must be at least 6 characters.</p>";
    } elseif ($pass !== $confirm) {
        echo "<p>Passwords do not match.</p>";
    } else {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE pre_reg_table SET password = ? WHERE email_address = ?");
        $stmt->bind_param("ss", $hash, $email);
        $stmt->execute();

        echo "<p>Password successfully reset. <a href='log_in.php'>Log in</a></p>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Reset Password</title></head>
<body>
  <h2>Reset Password for <?= htmlspecialchars($email) ?></h2>
  <form method="POST">
    <input type="password" name="password" placeholder="New Password" required><br><br>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required><br><br>
    <button type="submit">Reset Password</button>
  </form>
</body>
</html>
