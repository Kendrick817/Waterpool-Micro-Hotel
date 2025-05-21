<?php
// This file updates the status of a booking
// It changes a booking to Confirmed, Cancelled, or Pending

// Start a session to check if admin is logged in
session_start();

// If admin is not logged in, send them to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Connect to the database
require('includes/db_config.php');

// Check if we have both booking ID and new status in the URL
if (isset($_GET['id']) && isset($_GET['status'])) {
    // Get the booking ID and status from the URL
    $booking_id = $_GET['id'];
    $status = $_GET['status'];

    // Make sure the status is one of our allowed values
    $allowed_statuses = ['Pending', 'Confirmed', 'Cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        // If status is not valid, show error and go back
        $_SESSION['error_msg'] = "Invalid status value.";
        header('Location: manage_bookings.php');
        exit;
    }

    try {
        // Create SQL query to update the booking status
        $query = "UPDATE bookings SET status = ? WHERE booking_ID = ?";
        $stmt = $conn->prepare($query);

        // Check if query preparation was successful
        if (!$stmt) {
            throw new Exception("Database query preparation failed: " . $conn->error);
        }

        // Add parameters to the query
        // "si" means string and integer
        $stmt->bind_param("si", $status, $booking_id);

        // Run the query
        if ($stmt->execute()) {
            // If successful, save success message
            $_SESSION['success_msg'] = "Booking status updated to $status successfully.";
        } else {
            // If query failed, throw an error
            throw new Exception("Failed to update booking status: " . $stmt->error);
        }
    } catch (Exception $e) {
        // Save error message if something went wrong
        $_SESSION['error_msg'] = $e->getMessage();
    }

    // Go back to the bookings list page
    header('Location: manage_bookings.php');
    exit;
} else {
    // If booking ID or status is missing from URL
    $_SESSION['error_msg'] = "Missing booking ID or status.";
    header('Location: manage_bookings.php');
    exit;
}
?>