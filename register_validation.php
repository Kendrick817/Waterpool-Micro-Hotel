<?php
// Registration validation script
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
        header('Location: register.php');
        exit();
    }

    // Verify with Google
    $verify_url = "https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}";
    $verify = file_get_contents($verify_url);
    $captchaSuccess = json_decode($verify);

    if(!$captchaSuccess->success){
        $_SESSION['error'] = "Captcha verification failed. Please try again.";
        header('Location: register.php');
        exit();
    }

    // Get form data
    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm) {
        $_SESSION['error'] = "Passwords do not match.";
        header('Location: register.php');
        exit();
    }

    // Check if username already exists
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "Username already exists. Please choose a different username.";
        header('Location: register.php');
        exit();
    }

    // Check if email already exists
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "Email already exists. Please use a different email.";
        header('Location: register.php');
        exit();
    }

    // Hash password and insert user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO users (name, username, email, phone, password)
              VALUES ('$name', '$username', '$email', '$phone', '$hashedPassword')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Your Account has been created. You can now Login";
        header('Location: login.php');
        exit();
    } else {
        $_SESSION['error'] = "Registration failed: " . mysqli_error($conn);
        header('Location: register.php');
        exit();
    }
}
