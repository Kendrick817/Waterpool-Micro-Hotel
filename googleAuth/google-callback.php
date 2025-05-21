<?php
// This file handles what happens after Google login
// Google sends back a code that we use to get user info

// Load required files
require_once '../vendor/autoload.php';

// Start a session to save user info
session_start();

// Get settings from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Set up Google client again
$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT']);

// Check if Google sent us a code
if(isset($_GET['code'])){
    // Use the code to get access to user's account
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    // If we got access successfully
    if(!isset($token['error'])){
        $client->setAccessToken($token['access_token']);

        // Get user information from Google
        $oauth2 = new \Google\Service\Oauth2($client);
        $userInfo = $oauth2->userinfo->get();

        // Save user details
        $name = $userInfo->name;
        $email = $userInfo->email;
        $picture = $userInfo->picture;

        // Connect to our database
        require_once '../includes/db_config.php';

        // Check if this user already exists in our database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // If user doesn't exist, we need to create a new account
            // Make a username from their name (remove spaces and add random numbers)
            $baseUsername = strtolower(str_replace(' ', '', $name));
            $username = $baseUsername . rand(100, 999);

            // Make sure username isn't already taken
            $usernameExists = true;
            while ($usernameExists) {
                $checkStmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
                $checkStmt->bind_param("s", $username);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();

                if ($checkResult->num_rows == 0) {
                    // Username is available
                    $usernameExists = false;
                } else {
                    // Try a different random number
                    $username = $baseUsername . rand(100, 999);
                }
                $checkStmt->close();
            }

            // Create a random password (user won't need it for Google login)
            $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

            // Add the new user to our database
            $insertStmt = $conn->prepare("INSERT INTO users (name, username, email, password) VALUES (?, ?, ?, ?)");
            $insertStmt->bind_param("ssss", $name, $username, $email, $password);
            $insertStmt->execute();

            // Get the ID of the new user
            $userId = $conn->insert_id;
            $insertStmt->close();

            // Save user info in session variables
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_type'] = 'google';
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_image'] = $picture;
            $_SESSION['user_username'] = $username;

            $_SESSION['google_login_success'] = 'Account created and logged in with Google!';
        } else {
            // If user already exists, just log them in
            $user = $result->fetch_assoc();

            // Save user info in session variables
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['user_ID'];
            $_SESSION['user_type'] = 'google';
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_image'] = $picture;
            $_SESSION['user_username'] = $user['username'];

            $_SESSION['google_login_success'] = 'Login with Google!';
        }

        $stmt->close();

        // Go to homepage
        header('location: ../index.php');
        exit();
    } else {
        // If there was a problem with the token
        $_SESSION['error'] = 'Login Failed';
        header('location: ../login.php');
        exit();
    }
} else {
    // If no code was received from Google
    $_SESSION['error'] = 'Invalid Login';
    header('location: ../login.php');
    exit();
}
