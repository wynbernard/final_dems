<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer manually
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';


// Get form input
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$message = $_POST['message'] ?? '';

// Setup email
$mail = new PHPMailer(true);
try {
	// SMTP config
	$mail->isSMTP();
	$mail->Host       = 'smtp.hostinger.com'; // your SMTP server
	$mail->SMTPAuth   = true;
	$mail->Username   = 'dems_info@bccbsis.com';
	$mail->Password   = '[nAgc/#^Jj7';
	$mail->SMTPSecure = 'tls'; // or 'ssl'
	$mail->Port       = 465;   // or 587 for SSL

	// Email headers
	$mail->setFrom('dems_info@bccbsis.com', 'Website Contact');
	$mail->addAddress('dems_info@bccbsis.com'); // recipient
	$mail->isHTML(true);
	$mail->Subject = "New Message from $name";
	$mail->Body    = "
        <h3>Message from Website</h3>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Message:</strong><br>$message</p>
    ";

	// Send the email
	$mail->send();
	echo "Message sent successfully.";
} catch (Exception $e) {
	echo "Message failed. Error: " . $mail->ErrorInfo;
}
