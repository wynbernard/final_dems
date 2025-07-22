<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
include 'db_config.php'; // this should define $conn

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);
$email = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

// Check if email exists in database
$stmt = $conn->prepare("SELECT pre_reg_id FROM pre_reg_table WHERE email_address = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Email not found.']);
    exit;
}

// Generate token and save to DB
$token = bin2hex(random_bytes(32));
$expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

$stmt = $conn->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?");
$stmt->bind_param("sss", $token, $expiry, $email);
$stmt->execute();

// Send reset email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'dems_info@bccbsis.com';
    $mail->Password = '[nAgc/#^Jj7';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('dems_info@bccbsis.com', 'DEMS System');
    $mail->addAddress($email);

    $resetLink = "http://localhost/reset_password.php?token=$token";

    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request';
    $mail->Body    = "
        <h2>Password Reset</h2>
        <p>Hello,</p>
        <p>Click the link below to reset your password. This link will expire in 15 minutes.</p>
        <p><a href='$resetLink'>$resetLink</a></p>
    ";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Reset link sent to your email.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Mailer Error: ' . $mail->ErrorInfo]);
}
