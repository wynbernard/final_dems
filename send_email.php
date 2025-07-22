<?php
// Load Composer's autoloader
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email   = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    if (!$email || empty($name) || empty($message)) {
        exit("❌ Invalid form input.");
    }

    $mail = new PHPMailer(true);

    try {
        // Enable verbose debug output for testing (remove on live)
        // $mail->SMTPDebug = 2;
        // $mail->Debugoutput = 'html';

        // SMTP server configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'dems_info@bccbsis.com';     // your Hostinger email
        $mail->Password   = '[nAgc/#^Jj7';                // your Hostinger email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // 'ssl' or use PHPMailer::ENCRYPTION_SMTPS
        $mail->Port       = 465;

        // Sender and recipient
        $mail->setFrom('dems_info@bccbsis.com', 'DEMS Website');
        $mail->addAddress('dems_info@bccbsis.com', 'DEMS Admin'); // Or another recipient
        $mail->addReplyTo($email, $name); // Allow replies to sender

        // Email content
        $mail->isHTML(true);
        $mail->Subject = "📬 Contact Form: $name";
        $mail->Body    = "
            <h2>New Contact Form Submission</h2>
            <p><strong>Name:</strong> {$name}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Message:</strong><br>" . nl2br($message) . "</p>
        ";
        $mail->AltBody = "Name: $name\nEmail: $email\nMessage:\n$message";

        $mail->send();
        echo "✅ Message sent successfully!";
    } catch (Exception $e) {
        echo "❌ Message could not be sent.<br>Error: " . $mail->ErrorInfo;
    }
} else {
    echo "Form not submitted properly.";
}
