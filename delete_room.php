<?php
/**
 * Delete Room
 * Handles the deletion of a room and its associated image
 */

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require('includes/db_config.php');

// Check if the room ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $room_id = $_GET['id'];

    try {
        // Start transaction
        mysqli_begin_transaction($conn);

        // Fetch the room details to delete the image - use correct column name (room_ID)
        $query = "SELECT * FROM rooms WHERE room_ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Database query preparation failed: " . $conn->error);
        }

        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $room = $result->fetch_assoc();

            // Delete the image file if it exists
            $image_path = "images/rooms/" . $room['image'];
            if (!empty($room['image']) && file_exists($image_path)) {
                if (!unlink($image_path)) {
                    // Log the error but continue with deletion
                    error_log("Failed to delete image file: " . $image_path);
                }
            }

            // First delete all bookings associated with this room
            $delete_bookings_query = "DELETE FROM bookings WHERE room_ID = ?";
            $delete_bookings_stmt = $conn->prepare($delete_bookings_query);

            if (!$delete_bookings_stmt) {
                throw new Exception("Database delete bookings query preparation failed: " . $conn->error);
            }

            $delete_bookings_stmt->bind_param("i", $room_id);

            if (!$delete_bookings_stmt->execute()) {
                throw new Exception("Failed to delete associated bookings: " . $delete_bookings_stmt->error);
            }

            // Get the number of bookings deleted
            $bookings_deleted = $delete_bookings_stmt->affected_rows;

            // Now delete the room from the database - use correct column name (room_ID)
            $delete_query = "DELETE FROM rooms WHERE room_ID = ?";
            $delete_stmt = $conn->prepare($delete_query);

            if (!$delete_stmt) {
                throw new Exception("Database delete query preparation failed: " . $conn->error);
            }

            $delete_stmt->bind_param("i", $room_id);

            if (!$delete_stmt->execute()) {
                throw new Exception("Failed to delete room: " . $delete_stmt->error);
            }

            // Commit transaction
            mysqli_commit($conn);

            // Redirect with success message
            $_SESSION['success_msg'] = "Room deleted successfully!";
            header('Location: manage_rooms.php');
            exit;
        } else {
            throw new Exception("Room not found!");
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);

        // Redirect with error message
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
        header('Location: manage_rooms.php');
        exit;
    }
} else {
    // Redirect with error message for invalid ID
    $_SESSION['error_msg'] = "Invalid Room ID!";
    header('Location: manage_rooms.php');
    exit;
}
?>