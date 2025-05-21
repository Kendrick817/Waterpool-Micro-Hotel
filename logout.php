<?php
/**
 * Logout Script
 * Handles both user and admin logout
 */

session_start();

// Check if this is an admin logout
if (isset($_GET['admin']) && $_GET['admin'] == 1) {
    // Clear admin session variables
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_id']);

    // Redirect to admin login page
    header('Location: admin_login.php');
    exit;
} else {
    // Clear user session variables
    unset($_SESSION['user_logged_in']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_id']);
    unset($_SESSION['user_type']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_image']);
    unset($_SESSION['user_username']);

    // Destroy the entire session
    session_destroy();

    // Redirect to the homepage
    header('Location: index.php');
    exit;
}
?>