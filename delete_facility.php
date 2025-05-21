<?php
/**
 * Delete Facility
 * Deletes a facility from the database
 */

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require('includes/db_config.php');

// Check if ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $facility_id = $_GET['id'];

    try {
        // Check if the facility exists
        $check_query = "SELECT * FROM facilities WHERE facility_ID = ?";
        $stmt = $conn->prepare($check_query);

        if (!$stmt) {
            throw new Exception("Database query preparation failed: " . $conn->error);
        }

        $stmt->bind_param("i", $facility_id);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows === 0) {
            throw new Exception("Facility not found");
        }

        // Get facility details to delete image file
        $facility = $check_result->fetch_assoc();

        // Delete the facility
        $delete_query = "DELETE FROM facilities WHERE facility_ID = ?";
        $stmt = $conn->prepare($delete_query);

        if (!$stmt) {
            throw new Exception("Database query preparation failed: " . $conn->error);
        }

        $stmt->bind_param("i", $facility_id);

        if ($stmt->execute()) {
            // Delete the image file if it exists and is not the default image
            if (!empty($facility['icon']) && $facility['icon'] != 'default_feature.png' && file_exists("images/features/" . $facility['icon'])) {
                unlink("images/features/" . $facility['icon']);
            }

            $_SESSION['success_msg'] = "Facility deleted successfully.";
        } else {
            throw new Exception("Failed to delete facility: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error_msg'] = $e->getMessage();
    }

    // Redirect back to manage facilities page
    header('Location: manage_facilities.php');
    exit;
} else {
    $_SESSION['error_msg'] = "Invalid facility ID.";
    header('Location: manage_facilities.php');
    exit;
}
?>
