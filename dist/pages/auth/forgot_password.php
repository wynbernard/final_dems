<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include '../../../database/conn.php'; // only needed if you're still using DB for user existence check

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
$email = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

// ✅ OPTIONAL: Check if user exists in DB
$stmt = $conn->prepare("SELECT pre_reg_id FROM pre_reg_table WHERE email_address = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Email not found.']);
    exit;
}

// ✅ Generate token and expiry
$token = bin2hex(random_bytes(32));
$expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

// ✅ Save to file cache
$cacheDir = __DIR__ . '/cache';
if (!file_exists($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// Clean expired cache files
foreach (glob($cacheDir . '/*.json') as $file) {
    $data = json_decode(file_get_contents($file), true);
    if (isset($data['expires']) && strtotime($data['expires']) < time()) {
        unlink($file);
    }
}

// Save current token
$cacheFile = $cacheDir . '/' . base64_encode($email) . '.json';
file_put_contents($cacheFile, json_encode([
    'token' => $token,
    'expires' => $expiry
]));

// ✅ Send email
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

    $resetLink = "http://dems.bccbsis.com/dist/pages/auth/reset_password.php?email=" . urlencode($email) . "&token=" . urlencode($token);

    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request';
    $mail->Body    = "
        <h2>Password Reset</h2>
        <p>Click the link below to reset your password. This link will expire in 15 minutes.</p>
        <p><a href='$resetLink'>$resetLink</a></p>
    ";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Reset link sent to your email.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Mailer Error: ' . $mail->ErrorInfo]);
}
