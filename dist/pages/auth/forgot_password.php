<?php
header('Content-Type: application/json');

require '../../../vendor/autoload.php'; // Adjust if needed
use PHPMailer\PHPMailer\PHPMailer;

$data = json_decode(file_get_contents('php://input'), true);
$email = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

// 🔐 TODO: Check if email exists in your database
// For example:
include '../../../database/conn.php';

$stmt = $conn->prepare("SELECT pre_reg_id FROM pre_reg_table WHERE email_address = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Email not found.']);
    exit;
}

// ✅ Optional: generate token + save to DB
$token = bin2hex(random_bytes(32));
$expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
$conn->query("UPDATE users SET reset_token = '$token', token_expiry = '$expiry' WHERE email = '$email'");

// ✉️ Send reset email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'dems_info@bccbsis.com';
    $mail->Password = '[nAgc/#^Jj7';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('dems_info@bccbsis.com', 'DEMS Reset');
    $mail->addAddress($email);

    $resetLink = "http://yourdomain.com/reset_password.php?token=$token";

    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request';
    $mail->Body    = "<p>Click the link below to reset your password:</p><p><a href='$resetLink'>$resetLink</a></p>";
    $mail->send();

    echo json_encode(['success' => true, 'message' => 'Reset link sent to your email.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Mail Error: ' . $mail->ErrorInfo]);
}
