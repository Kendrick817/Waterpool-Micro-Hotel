<?php
// This file processes the admin login form
// It checks if the username and password are correct

// Start a session to store login information
session_start();
require('includes/db_config.php');

// If admin is already logged in, send them to the dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin.php');
    exit;
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Make sure username and password fields are not empty
    if (empty($_POST['username']) || empty($_POST['password'])) {
        // Save error message and go back to login page
        $_SESSION['admin_login_error'] = "Username and password are required";
        header('Location: admin_login.php');
        exit;
    }

    // Get the username and password from the form
    $username = trim($_POST['username']); // Remove extra spaces
    $password = md5($_POST['password']); // Convert password to MD5 hash

    try {
        // Create a secure database query using prepared statement
        $query = "SELECT * FROM admin WHERE username = ? AND password = ?";
        $stmt = $conn->prepare($query);

        // Check if query preparation was successful
        if (!$stmt) {
            throw new Exception("Database query preparation failed: " . $conn->error);
        }

        // Add the username and password to the query
        $stmt->bind_param("ss", $username, $password); // "ss" means two strings
        $stmt->execute(); // Run the query
        $result = $stmt->get_result(); // Get the results

        // Check if we found a matching admin account
        if ($result->num_rows == 1) {
            // Login successful - get admin information
            $admin = $result->fetch_assoc();

            // Save admin information in session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_id'] = $admin['admin_ID'];

            // Go to admin dashboard
            header('Location: admin.php');
            exit;
        } else {
            // Login failed - username or password is wrong
            $_SESSION['admin_login_error'] = "Invalid username or password";
            header('Location: admin_login.php');
            exit;
        }
    } catch (Exception $e) {
        // If there was a database error
        $_SESSION['admin_login_error'] = "An error occurred: " . $e->getMessage();
        header('Location: admin_login.php');
        exit;
    }
} else {
    // If someone tries to access this page directly (not through the form)
    header('Location: admin_login.php');
    exit;
}
?>