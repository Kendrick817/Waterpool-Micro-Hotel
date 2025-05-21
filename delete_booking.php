<?php
/**
 * Delete Booking
 * Deletes a booking from the database
 */

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require('includes/db_config.php');

// Check if ID is provided
if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];

    try {
        // Delete the booking
        $query = "DELETE FROM bookings WHERE booking_ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Database query preparation failed: " . $conn->error);
        }

        $stmt->bind_param("i", $booking_id);

        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Booking deleted successfully.";
        } else {
            throw new Exception("Failed to delete booking: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error_msg'] = $e->getMessage();
    }

    // Redirect back to manage bookings page
    header('Location: manage_bookings.php');
    exit;
} else {
    $_SESSION['error_msg'] = "No booking ID provided.";
    header('Location: manage_bookings.php');
    exit;
}
?>