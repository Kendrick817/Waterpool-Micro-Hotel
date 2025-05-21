<?php
// This is the admin dashboard page
// It shows statistics and quick links for hotel management

// Start a session to check if admin is logged in
session_start();

// Check if admin is logged in - if not, send them to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Go to login page
    header('Location: admin_login.php');
    exit;
}

// Connect to the database
require('includes/db_config.php');

// Count how many rooms we have in the database
$room_query = "SELECT COUNT(*) as count FROM rooms";
$room_result = mysqli_query($conn, $room_query);
$room_count = ($room_result) ? mysqli_fetch_assoc($room_result)['count'] : 0;

// Count how many bookings we have in the database
$booking_query = "SELECT COUNT(*) as count FROM bookings";
$booking_result = mysqli_query($conn, $booking_query);
$booking_count = ($booking_result) ? mysqli_fetch_assoc($booking_result)['count'] : 0;

// We removed the confirmed booking count section

// Count how many messages we have
// First check which message table exists in our database
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'contact_messages'");
if (mysqli_num_rows($check_table) > 0) {
    // If contact_messages table exists, use it
    $message_query = "SELECT COUNT(*) as count FROM contact_messages";
} else {
    // Otherwise use the messages table
    $message_query = "SELECT COUNT(*) as count FROM messages";
}
$message_result = mysqli_query($conn, $message_query);
$message_count = ($message_result) ? mysqli_fetch_assoc($message_result)['count'] : 0;

// Count how many payments we have in the database
$payment_query = "SELECT COUNT(*) as count FROM payments";
$payment_result = mysqli_query($conn, $payment_query);
$payment_count = ($payment_result) ? mysqli_fetch_assoc($payment_result)['count'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Waterpool Hotel</title>
    <?php require('includes/links.php'); ?>
    <link rel="stylesheet" href="styles/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <?php require('includes/admin_sidebar.php'); ?>

        <!-- Main Content -->
        <div class="admin-content">
            <div class="container-fluid p-4">
                <h2 class="fw-bold h-font mb-4">Dashboard</h2>

                <!-- Stats Row -->
                <div class="row mb-4">
                    <!-- Rooms Card -->
                    <div class="col-md-6 col-lg-3">
                        <div class="dashboard-card text-center">
                            <div class="dashboard-card-icon text-primary">
                                <i class="bi bi-door-closed-fill"></i>
                            </div>
                            <div class="dashboard-card-title">Total Rooms</div>
                            <div class="dashboard-card-value"><?php echo $room_count; ?></div>
                            <a href="manage_rooms.php" class="btn btn-sm btn-outline-primary mt-3">Manage Rooms</a>
                        </div>
                    </div>

                    <!-- Bookings Card -->
                    <div class="col-md-6 col-lg-3">
                        <div class="dashboard-card text-center">
                            <div class="dashboard-card-icon text-success">
                                <i class="bi bi-calendar-check-fill"></i>
                            </div>
                            <div class="dashboard-card-title">Total Bookings</div>
                            <div class="dashboard-card-value"><?php echo $booking_count; ?></div>
                            <a href="manage_bookings.php" class="btn btn-sm btn-outline-success mt-3">Manage Bookings</a>
                        </div>
                    </div>

                    <!-- Payments Card -->
                    <div class="col-md-6 col-lg-3">
                        <div class="dashboard-card text-center">
                            <div class="dashboard-card-icon text-danger">
                                <i class="bi bi-credit-card-fill"></i>
                            </div>
                            <div class="dashboard-card-title">Total Payments</div>
                            <div class="dashboard-card-value"><?php echo $payment_count; ?></div>
                            <a href="all_payments.php" class="btn btn-sm btn-outline-danger mt-3">View Payments</a>
                        </div>
                    </div>

                    <!-- Messages Card -->
                    <div class="col-md-6 col-lg-3">
                        <div class="dashboard-card text-center">
                            <div class="dashboard-card-icon text-info">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <div class="dashboard-card-title">Messages</div>
                            <div class="dashboard-card-value"><?php echo $message_count; ?></div>
                            <a href="manage_messages.php" class="btn btn-sm btn-outline-info mt-3">View Messages</a>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <h4 class="mb-3">Quick Actions</h4>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <a href="add_room.php" class="btn btn-outline-dark w-100 py-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-plus-circle me-2"></i> Add New Room
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="manage_bookings.php" class="btn btn-outline-dark w-100 py-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-calendar-check me-2"></i> View Bookings
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="all_payments.php" class="btn btn-outline-dark w-100 py-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-credit-card me-2"></i> View Payments
                        </a>
                    </div>

                    <div class="col-md-3">
                        <a href="manage_messages.php" class="btn btn-outline-dark w-100 py-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-envelope-fill me-2"></i> View Messages
                        </a>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <h4 class="mb-3">Recent Bookings</h4>
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Room</th>
                                        <th>Guest</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Get the 5 most recent bookings from the database
                                    $recent_query = "SELECT b.booking_ID, b.user_email, b.check_in, b.check_out, b.status, r.name as room_name
                                                    FROM bookings b
                                                    JOIN rooms r ON b.room_ID = r.room_ID
                                                    ORDER BY b.created_at DESC LIMIT 5";
                                    $recent_result = mysqli_query($conn, $recent_query);

                                    // Check if we found any bookings
                                    if ($recent_result && mysqli_num_rows($recent_result) > 0) {
                                        // Loop through each booking
                                        while ($booking = mysqli_fetch_assoc($recent_result)) {
                                            // Set the color for the status badge
                                            $status_class = '';
                                            if ($booking['status'] == 'Confirmed') {
                                                $status_class = 'bg-success'; // Green for confirmed
                                            } elseif ($booking['status'] == 'Pending') {
                                                $status_class = 'bg-warning text-dark'; // Yellow for pending
                                            } elseif ($booking['status'] == 'Cancelled') {
                                                $status_class = 'bg-danger'; // Red for cancelled
                                            }

                                            // Display the booking information in a table row
                                            echo '<tr>
                                                <td>' . htmlspecialchars($booking['booking_ID']) . '</td>
                                                <td>' . htmlspecialchars($booking['room_name']) . '</td>
                                                <td>' . htmlspecialchars($booking['user_email']) . '</td>
                                                <td>' . date('M j, Y', strtotime($booking['check_in'])) . '</td>
                                                <td>' . date('M j, Y', strtotime($booking['check_out'])) . '</td>
                                                <td><span class="badge ' . $status_class . '">' . htmlspecialchars($booking['status']) . '</span></td>
                                            </tr>';
                                        }
                                    } else {
                                        // If no bookings were found, show a message
                                        echo '<tr><td colspan="6" class="text-center">No recent bookings found</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="manage_bookings.php" class="btn btn-sm btn-outline-dark">View All Bookings</a>
                        </div>
                    </div>
                </div>

                <!-- Recent Payments -->
                <h4 class="mb-3">Recent Payments</h4>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Booking</th>
                                        <th>User</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Get the 5 most recent payments from the database
                                    // We join with bookings and rooms tables to get user email and room name
                                    $recent_payments_query = "SELECT p.*, b.user_email, r.name as room_name
                                                           FROM payments p
                                                           JOIN bookings b ON p.booking_ID = b.booking_ID
                                                           JOIN rooms r ON b.room_ID = r.room_ID
                                                           ORDER BY p.payment_date DESC LIMIT 5";
                                    $recent_payments_result = mysqli_query($conn, $recent_payments_query);

                                    // Check if we found any payments
                                    if ($recent_payments_result && mysqli_num_rows($recent_payments_result) > 0) {
                                        // Loop through each payment
                                        while ($payment = mysqli_fetch_assoc($recent_payments_result)) {
                                            // Set the color for the status badge
                                            $status_class = '';
                                            if ($payment['status'] == 'completed' || $payment['status'] == 'Completed') {
                                                $status_class = 'bg-success'; // Green for completed
                                            } elseif ($payment['status'] == 'pending' || $payment['status'] == 'Pending') {
                                                $status_class = 'bg-warning text-dark'; // Yellow for pending
                                            } elseif ($payment['status'] == 'failed' || $payment['status'] == 'Failed') {
                                                $status_class = 'bg-danger'; // Red for failed
                                            }

                                            // Display the payment information in a table row
                                            echo '<tr>
                                                <td>' . htmlspecialchars($payment['payment_ID']) . '</td>
                                                <td>
                                                    <span class="fw-bold">#' . htmlspecialchars($payment['booking_ID']) . '</span><br>
                                                    <small>' . htmlspecialchars($payment['room_name']) . '</small>
                                                </td>
                                                <td>' . htmlspecialchars($payment['user_email']) . '</td>
                                                <td>₱' . number_format($payment['amount'], 2) . '</td>
                                                <td>' . htmlspecialchars($payment['payment_method']) . '</td>
                                                <td>' . date('M j, Y', strtotime($payment['payment_date'])) . '</td>
                                                <td><span class="badge ' . $status_class . '">' . htmlspecialchars($payment['status']) . '</span></td>
                                            </tr>';
                                        }
                                    } else {
                                        // If no payments were found, show a message
                                        echo '<tr><td colspan="7" class="text-center">No recent payments found</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="all_payments.php" class="btn btn-sm btn-outline-dark">View All Payments</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/admin.js"></script>
</body>
</html>
