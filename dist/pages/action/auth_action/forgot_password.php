<?php
// Configuration
const SMTP_HOST = 'smtp.hostinger.com';
const SMTP_PORT = 587; // TLS
const SMTP_USER = 'dems_info@bccbsis.com';
const SMTP_PASS = 'nAgc/#^Jj7';
const BASE_URL  = 'http://dems.bccbsis.com'; // Change to your real base URL


include '../../../../database/conn.php';
header('Content-Type: application/json');

// Get email from request
$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

// Check if user exists
$stmt = $conn->prepare("SELECT pre_reg_id FROM pre_reg_table WHERE email_address = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['success' => true, 'message' => 'If the email exists, a reset link was sent.']);
    exit;
}

// Generate token (encoded JSON with email + expiry)
$expires = time() + 3600; // 1 hour
$payload = base64_encode(json_encode(['email' => $email, 'exp' => $expires]));
$resetLink = BASE_URL . "/dist/pages/auth/reset_password.php?token=" . urlencode($payload);

// Compose email
$subject = "Reset your password";
$body = "Click this link to reset your password:\n\n$resetLink\n\nThis link expires in 1 hour.";

// Send email using raw SMTP
function sendMailSMTP($to, $subject, $body) {
    $socket = fsockopen("ssl://" . SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
    if (!$socket) return false;

    $read = fn() => fgets($socket);
    $write = fn($msg) => fputs($socket, $msg . "\r\n");

    $read();
    $write("EHLO localhost");  while (strpos($read(), "250-") === 0);
    $write("AUTH LOGIN");       $read();
    $write(base64_encode(SMTP_USER)); $read();
    $write(base64_encode(SMTP_PASS)); $read();
    $write("MAIL FROM:<" . SMTP_USER . ">"); $read();
    $write("RCPT TO:<$to>"); $read();
    $write("DATA"); $read();

    $msg = "From: DEMS Info <" . SMTP_USER . ">\r\n";
    $msg .= "To: <$to>\r\n";
    $msg .= "Subject: $subject\r\n";
    $msg .= "MIME-Version: 1.0\r\n";
    $msg .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $msg .= $body . "\r\n.";
    $write($msg); $read();

    $write("QUIT"); fclose($socket);
    return true;
}

if (sendMailSMTP($email, $subject, $body)) {
    echo json_encode(['success' => true, 'message' => 'Reset link sent.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send email.']);
}
