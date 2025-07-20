<script src="tesseract.js"></script>
<script>
  document.getElementById('ic_image').addEventListener('change', function() {
    const file = this.files[0];
    const fname = document.getElementById('f_name').value.trim().toLowerCase();
    const lname = document.getElementById('l_name').value.trim().toLowerCase();

    if (!file || !fname || !lname) {
      Swal.fire({
        icon: 'warning',
        title: 'Missing Input',
        text: 'Please enter both first and last name before uploading the ID.'
      });
      this.value = "";
      return;
    }

    // Show loading modal
    Swal.fire({
      title: 'Scanning ID...',
      html: 'Please wait while we extract text from the image.<br><b>This may take a few seconds.</b>',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    Tesseract.recognize(file, 'eng', {
      logger: m => {
        if (m.status === "recognizing text") {
          Swal.update({
            html: `Recognizing text... <b>${Math.round(m.progress * 100)}%</b>`
          });
        }
      }
    }).then(({
      data: {
        text
      }
    }) => {
      const lowerText = text.toLowerCase();
      const fnameMatch = lowerText.includes(fname);
      const lnameMatch = lowerText.includes(lname);

      if (fnameMatch && lnameMatch) {
        Swal.fire({
          icon: 'success',
          title: 'Match Found',
          text: '✅ Name matched successfully!',
          confirmButtonColor: '#198754'
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Name Mismatch',
          text: '❌ Name on the ID does not match the input.',
          confirmButtonColor: '#dc3545'
        });
        document.getElementById('ic_image').value = "";
      }
    }).catch(err => {
      console.error(err);
      Swal.fire({
        icon: 'error',
        title: 'OCR Error',
        text: '⚠️ Error processing the image. Please try again.',
        confirmButtonColor: '#dc3545'
      });
    });
  });
</script>


<?php
// save as send-email.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validate input data
    if (!$data || !isset($data['name']) || !isset($data['email']) || !isset($data['subject']) || !isset($data['message'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    // Sanitize input
    $name = htmlspecialchars(trim($data['name']));
    $email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars(trim($data['subject']));
    $message = htmlspecialchars(trim($data['message']));
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email address']);
        exit;
    }
    
    try {
        // Method 1: Using native mail() function (simpler but less reliable)
        $result = sendEmailNative($name, $email, $subject, $message);
        
        // Method 2: Using SMTP socket connection (more reliable)
        // $result = sendEmailSMTP($name, $email, $subject, $message);
        
        echo json_encode(['success' => $result['success'], 'error' => $result['error'] ?? null]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// Method 1: Native mail() function
function sendEmailNative($name, $email, $subject, $message) {
    $to = 'dems_info@bccbsis.com';
    $headers = array();
    
    // Set headers
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'From: ' . $name . ' <dems_info@bccbsis.com>';
    $headers[] = 'Reply-To: ' . $email;
    $headers[] = 'X-Mailer: PHP/' . phpversion();
    
    // Format message
    $emailBody = "
    <html>
    <head>
        <title>Contact Form Submission</title>
    </head>
    <body>
        <h2>New Contact Form Submission</h2>
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Subject:</strong> {$subject}</p>
        <p><strong>Message:</strong></p>
        <p>" . nl2br($message) . "</p>
        <hr>
        <p><small>Sent from contact form</small></p>
    </body>
    </html>
    ";
    
    // Send email
    $success = mail($to, $subject, $emailBody, implode("\r\n", $headers));
    
    return ['success' => $success, 'error' => $success ? null : 'Failed to send email'];
}

// Method 2: SMTP Socket Connection (more reliable)
function sendEmailSMTP($name, $email, $subject, $message) {
    $smtp_server = 'smtp.hostinger.com';
    $smtp_port = 587;
    $smtp_username = 'dems_info@bccbsis.com';
    $smtp_password = '[nAgc/#^Jj7';
    
    // Create socket connection
    $socket = fsockopen($smtp_server, $smtp_port, $errno, $errstr, 30);
    
    if (!$socket) {
        return ['success' => false, 'error' => "Connection failed: $errstr ($errno)"];
    }
    
    // Read initial response
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) != '220') {
        fclose($socket);
        return ['success' => false, 'error' => 'SMTP server not ready'];
    }
    
    // SMTP commands
    $commands = [
        "EHLO " . $_SERVER['SERVER_NAME'] ?? 'localhost',
        "STARTTLS",
    ];
    
    foreach ($commands as $command) {
        fputs($socket, $command . "\r\n");
        $response = fgets($socket, 512);
        
        if ($command == "STARTTLS") {
            if (substr($response, 0, 3) == '220') {
                // Enable TLS
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                
                // Re-authenticate after TLS
                fputs($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
                $response = fgets($socket, 512);
            }
        }
    }
    
    // Authentication
    fputs($socket, "AUTH LOGIN\r\n");
    $response = fgets($socket, 512);
    
    fputs($socket, base64_encode($smtp_username) . "\r\n");
    $response = fgets($socket, 512);
    
    fputs($socket, base64_encode($smtp_password) . "\r\n");
    $response = fgets($socket, 512);
    
    if (substr($response, 0, 3) != '235') {
        fclose($socket);
        return ['success' => false, 'error' => 'Authentication failed'];
    }
    
    // Send email
    fputs($socket, "MAIL FROM: <{$smtp_username}>\r\n");
    $response = fgets($socket, 512);
    
    fputs($socket, "RCPT TO: <{$smtp_username}>\r\n");
    $response = fgets($socket, 512);
    
    fputs($socket, "DATA\r\n");
    $response = fgets($socket, 512);
    
    // Email headers and body
    $email_content = "From: {$name} <{$smtp_username}>\r\n";
    $email_content .= "Reply-To: {$email}\r\n";
    $email_content .= "Subject: {$subject}\r\n";
    $email_content .= "MIME-Version: 1.0\r\n";
    $email_content .= "Content-Type: text/html; charset=UTF-8\r\n";
    $email_content .= "\r\n";
    $email_content .= "<html><body>";
    $email_content .= "<h2>New Contact Form Submission</h2>";
    $email_content .= "<p><strong>Name:</strong> {$name}</p>";
    $email_content .= "<p><strong>Email:</strong> {$email}</p>";
    $email_content .= "<p><strong>Subject:</strong> {$subject}</p>";
    $email_content .= "<p><strong>Message:</strong></p>";
    $email_content .= "<p>" . nl2br($message) . "</p>";
    $email_content .= "</body></html>";
    $email_content .= "\r\n.\r\n";
    
    fputs($socket, $email_content);
    $response = fgets($socket, 512);
    
    // Quit
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    
    $success = substr($response, 0, 3) == '250';
    return ['success' => $success, 'error' => $success ? null : 'Failed to send email via SMTP'];
}

// Alternative Method 3: Using curl to send via external API
function sendEmailCurl($name, $email, $subject, $message) {
    // This would be for services like Mailgun, SendGrid, etc.
    $api_key = 'YOUR_API_KEY';
    $api_url = 'https://api.mailgun.net/v3/YOUR_DOMAIN/messages';
    
    $data = [
        'from' => 'Contact Form <dems_info@bccbsis.com>',
        'to' => 'dems_info@bccbsis.com',
        'subject' => $subject,
        'html' => "
            <h2>New Contact Form Submission</h2>
            <p><strong>Name:</strong> {$name}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Subject:</strong> {$subject}</p>
            <p><strong>Message:</strong></p>
            <p>" . nl2br($message) . "</p>
        "
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_USERPWD, "api:{$api_key}");
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $success = $http_code == 200;
    return ['success' => $success, 'error' => $success ? null : 'Failed to send via API'];
}
?>