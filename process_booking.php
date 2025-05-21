<?php
session_start();
require('includes/db_config.php');

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in'])) {
    echo "<script>alert('Please log in to book a room.'); window.location.href='login.php';</script>";
    exit;
}

// Get the booking data from the form
$room_id = $_POST['room_id'];
$check_in = $_POST['check_in'];
$check_out = $_POST['check_out'];
$adults = $_POST['adults'];
$children = $_POST['children'];
$user_email = $_SESSION['user_email']; // Get the logged-in user's email

// Validate that the room_id exists in the rooms table
$room_check_query = "SELECT * FROM rooms WHERE room_ID = ?";
$stmt = $conn->prepare($room_check_query);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$room_check_result = $stmt->get_result();

if ($room_check_result->num_rows == 0) {
    // If the room_id does not exist, show an error message
    echo "<script>alert('Invalid Room ID. Please select a valid room.'); window.location.href='booking.php';</script>";
    exit;
}

// Insert the booking into the database
$query = "INSERT INTO bookings (room_ID, user_email, check_in, check_out, adults, children)
          VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("isssii", $room_id, $user_email, $check_in, $check_out, $adults, $children);

if ($stmt->execute()) {
    // Display success message with a "Back to Home" button
    echo "<script>
        alert('Booking successful!');
        window.location.href='my_bookings.php';
    </script>";
} else {
    echo "<script>alert('Error: " . $stmt->error . "'); window.location.href='index.php';</script>";
}
?>