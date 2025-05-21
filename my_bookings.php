<?php
// This is the My Bookings page
// It shows all the rooms that the logged-in user has booked

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php'); // Send to login page if not logged in
    exit;
}
require('includes/db_config.php');

// Create empty arrays to store data
$bookings = [];
$error_msg = '';

// Get the user's email from their session
if (isset($_SESSION['user_email'])) {
    $user_email = $_SESSION['user_email'];

    try {
        // Get all bookings for this user
        // This SQL joins the bookings table with the rooms table to get room details
        $query = "SELECT b.booking_ID, b.room_ID, b.check_in, b.check_out, b.adults, b.children, b.status,
                 b.created_at, r.name as room_name, r.price as room_price, r.image as room_image,
                 DATEDIFF(b.check_out, b.check_in) as nights
                 FROM bookings b
                 JOIN rooms r ON b.room_ID = r.room_ID
                 WHERE b.user_email = ?
                 ORDER BY b.created_at DESC";

        // Use prepared statement for security
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("s", $user_email); // "s" means string
        $stmt->execute();
        $result = $stmt->get_result();

        // Loop through all bookings and save them
        while ($row = $result->fetch_assoc()) {
            // Calculate the total price (nights × room price)
            $row['total_price'] = $row['nights'] * $row['room_price'];
            $bookings[] = $row; // Add to bookings array
        }
    } catch (Exception $e) {
        // If there's an error, save the error message
        $error_msg = $e->getMessage();
    }
} else {
    // If user email is not in session
    $error_msg = "User email not found in session. Please log in again.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Waterpool Suites</title>
    <?php require('includes/links.php'); ?>
    <style>
        /* Style for each booking card */
        .booking-card {
            transition: transform 0.3s; /* Smooth animation */
            margin-bottom: 20px;
            border-radius: 10px; /* Rounded corners */
            overflow: hidden; /* Hide anything outside the card */
        }

        /* Make cards lift up when hovered */
        .booking-card:hover {
            transform: translateY(-5px); /* Move up slightly */
            box-shadow: 0 10px 20px rgba(0,0,0,0.1); /* Add shadow */
        }

        /* Make all room images the same size */
        .room-image {
            height: 200px;
            object-fit: cover; /* Crop image to fit container */
        }

        /* Style for the status badge (Confirmed, Pending, etc.) */
        .status-badge {
            position: absolute; /* Position on top of the image */
            top: 10px;
            right: 10px;
            z-index: 1; /* Make sure it's above the image */
        }

        /* Add padding to booking details section */
        .booking-details {
            padding: 15px;
        }

        /* Style for the booking ID number */
        .booking-id {
            font-family: monospace; /* Use a typewriter-style font */
            background: #f0f0f0; /* Light gray background */
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        /* Style for each row of booking details */
        .detail-row {
            display: flex;
            justify-content: space-between; /* Put label on left, value on right */
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee; /* Add a light border between rows */
        }

        /* Style for the labels (Check-in, Check-out, etc.) */
        .detail-label {
            font-weight: bold;
            color: #555;
        }

        /* Style for the values */
        .detail-value {
            text-align: right;
        }

        /* Make the total price row stand out */
        .total-row {
            font-weight: bold;
            color: #28a745; /* Green color */
        }

        /* Style for the "No Bookings Found" message */
        .empty-bookings {
            text-align: center;
            padding: 50px 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body class="bg-white">
    <?php require('includes/header.php'); ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold h-font">My Bookings</h2>
            <a href="rooms.php" class="btn btn-outline-dark shadow-none">
                <i class="bi bi-plus-circle"></i> Book Another Room
            </a>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger">
                <?php echo $error_msg; ?>
            </div>
        <?php elseif (!empty($bookings)): ?>
            <div class="row">
                <?php foreach ($bookings as $booking): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card booking-card shadow-sm">
                            <div class="position-relative">
                                <img src="images/rooms/<?php echo htmlspecialchars($booking['room_image']); ?>"
                                    class="card-img-top room-image" alt="Room Image"
                                    onerror="this.src='images/placeholder.png';">
                                <div class="status-badge">
                                    <?php if ($booking['status'] == 'Confirmed'): ?>
                                        <span class="badge bg-success">Confirmed</span>
                                    <?php elseif ($booking['status'] == 'Pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php elseif ($booking['status'] == 'Cancelled'): ?>
                                        <span class="badge bg-danger">Cancelled</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($booking['status']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($booking['room_name']); ?></h5>
                                <p class="card-text text-muted mb-2">
                                    Booking ID: <span class="booking-id"><?php echo $booking['booking_ID']; ?></span>
                                </p>

                                <div class="booking-details mt-3">
                                    <div class="detail-row">
                                        <span class="detail-label">Check-in:</span>
                                        <span class="detail-value"><?php echo date('M j, Y', strtotime($booking['check_in'])); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Check-out:</span>
                                        <span class="detail-value"><?php echo date('M j, Y', strtotime($booking['check_out'])); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Guests:</span>
                                        <span class="detail-value"><?php echo $booking['adults']; ?> Adults, <?php echo $booking['children']; ?> Children</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Nights:</span>
                                        <span class="detail-value"><?php echo $booking['nights']; ?></span>
                                    </div>
                                    <div class="detail-row total-row">
                                        <span class="detail-label">Total:</span>
                                        <span class="detail-value">₱<?php echo number_format($booking['total_price'], 2); ?></span>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 mt-3">
                                    <a href="payment_success.php?booking_id=<?php echo $booking['booking_ID']; ?>"
                                        class="btn btn-outline-dark btn-sm">
                                        <i class="bi bi-receipt"></i> View Details
                                    </a>
                                </div>
                            </div>
                            <div class="card-footer text-muted">
                                <small>Booked on: <?php echo date('F j, Y, g:i a', strtotime($booking['created_at'])); ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-bookings">
                <i class="bi bi-calendar-x" style="font-size: 4rem; color: #6c757d;"></i>
                <h4 class="mt-3">No Bookings Found</h4>
                <p class="text-muted">You haven't made any bookings yet.</p>
                <a href="rooms.php" class="btn btn-dark mt-3">
                    <i class="bi bi-building"></i> Browse Rooms
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php require('includes/footer.php'); ?>
</body>
</html>