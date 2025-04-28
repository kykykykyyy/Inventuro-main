<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../../phpmailer/src/Exception.php';
require '../../../phpmailer/src/PHPMailer.php';
require '../../../phpmailer/src/SMTP.php';

header('Content-Type: application/json');

// Check if the POST request contains required data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipientEmail = $_POST['recipientEmail'] ?? '';
    $machineName = $_POST['machineName'] ?? 'A Machine';
    $notifyDays = $_POST['notifyDays'] ?? 7;
    $notifyWeeks = $_POST['notifyWeeks'];

    // Validate recipient email
    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid recipient email address.']);
        exit;
    }

    if($notifyWeeks == 0) {
        // Build the message
        $message = "Hello, you have agreed to receive notifications about the warranty for the " .
        "$machineName machine,  which will be notified every $notifyDays days before its maintenance or replacement date.
        You have also agreed to receive notifications about its warranty, which will be notified every $notifyWeeks week(s) before its expiration date.";
    }
    else {
        // Build the message
        $message = "Hello, you have agreed to receive notifications about the " .
        "$machineName machine, which will be notified every $notifyDays days before its maintenance or replacement date.";
        
    }
    try {
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'contactusinventuro@gmail.com'; // Replace with your email address
        $mail->Password = 'rhll atlj trol bacy'; // Replace with your actual app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Sender and recipient details
        $mail->setFrom('contactusinventuro@gmail.com', 'Inventuro');
        $mail->addAddress($recipientEmail);

        // Email content
        $mail->Subject = 'Notification Subscription from Inventuro';
        $mail->Body = $message;

        // Send the email
        if ($mail->send()) {
            echo json_encode(['success' => true, 'message' => 'Email sent successfully.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to send email.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Mailer Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>