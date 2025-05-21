<?php
// This file processes the forgot password form
// It sends a reset code to the user's email

// Start a session to store messages and user data
session_start();

// Connect to the database and load email library
require_once 'includes/db_config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Import the PHPMailer classes for sending emails
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the email from the form and remove extra spaces
    $email = trim($_POST['email']);

    // Check if the email exists in our database
    $stmt = $pdo_database->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // If we found a user with that email
    if ($user) {
        // Generate a random 6-digit code
        $reset_code = rand(100000, 999999);

        // Save the reset code in the database
        $update = $pdo_database->prepare("UPDATE users SET reset_code = ? WHERE email = ?");
        $update->execute([$reset_code, $email]);

        // Save the email in the session for the next step
        $_SESSION['email'] = $email;

        // Create a new email message
        $mail = new PHPMailer(true);

        try {
            // Set up the email server settings
            $mail->isSMTP(); // Use SMTP protocol
            $mail->Host      = 'smtp.gmail.com'; // Gmail SMTP server
            $mail->SMTPAuth  = true; // Enable SMTP authentication
            $mail->Username  = 'kendrick.amparado817@gmail.com'; // SMTP username
            $mail->Password  = 'aihi rknn nkuo ewzb'; // SMTP password
            $mail->SMTPSecure  = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
            $mail->Port        =  587; // TCP port to connect to

            // Set who the message is from and to
            $mail->setFrom('kendrick.amparado817@gmail.com', 'Kendrick Amparado');
            $mail->addAddress($email, 'THIS IS YOUR CLIENT');

            // Set email format and content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = "Password Reset Code";

            // HTML version of the email body
            $mail->Body = "
                        <p> Hello, This is your password Reset Code: {$reset_code}</p>
            ";

            // Plain text version for email clients that don't support HTML
            $mail->AltBody = "Hello, Use the code below to reset your password: \n\n {$reset_code}\n\n";

            // Send the email
            $mail->send();

            // Mark that we've sent the email
            $_SESSION['email_sent'] = true;

            // Show success message and go to the code entry page
            $_SESSION['success'] = "A verification code has been sent to your email";
            header('Location: send-code.php');
            exit();

        } catch (Exception $e) {
            // If there was an error sending the email
            $_SESSION['error'] = "Mailer Error: " . $mail->ErrorInfo;
            header('Location: forgot-password.php');
            exit();
        }

    } else {
        // If no user was found with that email
        $_SESSION['error'] = "No user found with that email";
        header('Location: forgot-password.php');
        exit();
    }
}
?>
