<?php
/**
 * Delete Message
 * Deletes a message from the database
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
    $message_id = $_GET['id'];

    try {
        // Check which table exists
        $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'contact_messages'");

        if (mysqli_num_rows($check_table) > 0) {
            // Use contact_messages table
            $query = "DELETE FROM contact_messages WHERE id = ?";
        } else {
            // Use messages table as fallback
            $query = "DELETE FROM messages WHERE id = ?";
        }

        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Database query preparation failed: " . $conn->error);
        }

        $stmt->bind_param("i", $message_id);

        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Message deleted successfully.";
        } else {
            throw new Exception("Failed to delete message: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error_msg'] = $e->getMessage();
    }

    // Redirect back to manage messages page
    header('Location: manage_messages.php');
    exit;
} else {
    $_SESSION['error_msg'] = "No message ID provided.";
    header('Location: manage_messages.php');
    exit;
}
?>