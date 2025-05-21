<?php
// This file starts the Google login process
// It will redirect the user to Google's login page

// Load all the required files
require_once '../vendor/autoload.php';

// Get the settings from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Set up Google login
$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT']);
$client->addScope('email');      // Get user's email
$client->addScope('profile');    // Get user's name and picture

// Send user to Google login page
header('location: ' . $client->createAuthUrl());

exit();
?>