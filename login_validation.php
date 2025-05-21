<?php
// Login validation script
session_start();
require 'includes/db_config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify reCAPTCHA
    $recaptcha_secret = $_ENV['RECAPTCHA_SECRET_KEY'];
    $recaptcha_response = $_POST['g-recaptcha-response'];

    // Check if recaptcha response is empty
    if(empty($recaptcha_response)) {
        $_SESSION['error'] = "Please complete the reCAPTCHA verification.";
        header('Location: login.php');
        exit();
    }

    // Verify with Google
    $verify_url = "https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}";
    $verify = file_get_contents($verify_url);
    $captchaSuccess = json_decode($verify);

    if(!$captchaSuccess->success){
        $_SESSION['error'] = "Captcha verification failed. Please try again.";
        header('Location: login.php');
        exit();
    }

    // Get login data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if user exists by username
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 0) {
        // If not found by username, try email
        $query = "SELECT * FROM users WHERE email = '$username'";
        $result = mysqli_query($conn, $query);
    }

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_username'] = $username;
            $_SESSION['user_id'] = $user['user_ID'];
            header('Location: index.php');
            exit();
        } else {
            // Wrong password
            $_SESSION['error'] = "Invalid Username or Password";
            header('Location: login.php');
            exit();
        }
    } else {
        // User not found
        $_SESSION['error'] = "Invalid Username or Password";
        header('Location: login.php');
        exit();
    }
}
?>