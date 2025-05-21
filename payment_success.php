<?php
/**
 * Payment Success Page
 * Shows booking confirmation after successful payment
 */

session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php'); // Redirect to login if the user is not logged in
    exit;
}

require('includes/db_config.php');

// Initialize variables
$booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : null;
$booking = null;
$payment_info = isset($_SESSION['payment_info']) ? $_SESSION['payment_info'] : null;
$error_msg = '';

// Fetch booking details if booking_id is provided
if ($booking_id) {
    try {
        // Use prepared statement for security
        $query = "SELECT b.*, r.name as room_name, r.price as room_price, r.image as room_image,
                 b.nights, b.total_price
                 FROM bookings b
                 JOIN rooms r ON b.room_ID = r.room_ID
                 WHERE b.booking_ID = ? AND b.user_email = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Error preparing query: " . $conn->error);
        }

        $user_email = $_SESSION['user_email'];
        $stmt->bind_param("is", $booking_id, $user_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $booking = $result->fetch_assoc();

            // Validate check-in and check-out dates
            if (empty($booking['check_in']) || strtotime($booking['check_in']) === false) {
                $booking['check_in'] = date('Y-m-d'); // Set to today if invalid
            }

            if (empty($booking['check_out']) || strtotime($booking['check_out']) === false) {
                // Set check-out to check-in + 1 day if invalid
                $check_in_date = new DateTime($booking['check_in']);
                $check_in_date->modify('+1 day');
                $booking['check_out'] = $check_in_date->format('Y-m-d');
            }

            // Validate and format check-in and check-out dates
            if (empty($booking['check_in']) || strtotime($booking['check_in']) === false) {
                $booking['check_in'] = date('Y-m-d'); // Set to today if invalid

                // Update the database with the corrected check-in date
                $update_checkin = "UPDATE bookings SET check_in = ? WHERE booking_ID = ?";
                $update_stmt = $conn->prepare($update_checkin);
                $update_stmt->bind_param("si", $booking['check_in'], $booking_id);
                $update_stmt->execute();
            }

            if (empty($booking['check_out']) || strtotime($booking['check_out']) === false) {
                // Set check-out to check-in + 1 day if invalid
                $check_in_date = new DateTime($booking['check_in']);
                $check_in_date->modify('+1 day');
                $booking['check_out'] = $check_in_date->format('Y-m-d');

                // Update the database with the corrected check-out date
                $update_checkout = "UPDATE bookings SET check_out = ? WHERE booking_ID = ?";
                $update_stmt = $conn->prepare($update_checkout);
                $update_stmt->bind_param("si", $booking['check_out'], $booking_id);
                $update_stmt->execute();
            }

            // Create DateTime objects for calculations
            $check_in = new DateTime($booking['check_in']);
            $check_out = new DateTime($booking['check_out']);

            // Ensure check-out is after check-in
            if ($check_out <= $check_in) {
                $check_out = clone $check_in;
                $check_out->modify('+1 day');
                $booking['check_out'] = $check_out->format('Y-m-d');

                // Update the database with the corrected check-out date
                $update_checkout = "UPDATE bookings SET check_out = ? WHERE booking_ID = ?";
                $update_stmt = $conn->prepare($update_checkout);
                $update_stmt->bind_param("si", $booking['check_out'], $booking_id);
                $update_stmt->execute();
            }

            // Calculate the exact difference in days
            $interval = $check_in->diff($check_out);
            $calculated_nights = $interval->days;

            // Ensure number of nights is reasonable (between 1 and 30)
            if ($calculated_nights < 1) {
                $calculated_nights = 1;
            } elseif ($calculated_nights > 30) {
                $calculated_nights = 30; // Cap at 30 nights for reasonability
            }

            // Use the nights value from the database if it's valid, otherwise use calculated value
            if (!isset($booking['nights']) || !is_numeric($booking['nights']) || $booking['nights'] < 1 || $booking['nights'] > 30) {
                $booking['nights'] = $calculated_nights;

                // Update the database with the validated nights
                $update_nights = "UPDATE bookings SET nights = ? WHERE booking_ID = ?";
                $update_stmt = $conn->prepare($update_nights);
                $update_stmt->bind_param("ii", $booking['nights'], $booking_id);
                $update_stmt->execute();
            }

            // Ensure room price is valid
            if (!isset($booking['room_price']) || !is_numeric($booking['room_price']) || $booking['room_price'] <= 0) {
                // Set a default room price if invalid
                $booking['room_price'] = 1000; // Default price of 1000
            }

            // Calculate total price based on room price and number of nights
            $booking['total_price'] = $booking['nights'] * $booking['room_price'];

            // Create price calculation string (for display only, not stored in database)
            $booking['price_calculation'] = "₱" . number_format($booking['room_price'], 2) . " × " . $booking['nights'] . " night" . ($booking['nights'] > 1 ? "s" : "");

            // Update the database with the calculated total price
            $update_price = "UPDATE bookings SET total_price = ? WHERE booking_ID = ?";
            $update_stmt = $conn->prepare($update_price);
            $update_stmt->bind_param("di", $booking['total_price'], $booking_id);
            $update_stmt->execute();

            // If payment_info session data exists, validate and use those values for consistency
            if ($payment_info && isset($payment_info['nights']) && isset($payment_info['amount'])) {
                // Use check-in and check-out dates from payment_info if available
                if (isset($payment_info['check_in']) && !empty($payment_info['check_in']) &&
                    isset($payment_info['check_out']) && !empty($payment_info['check_out'])) {

                    $booking['check_in'] = $payment_info['check_in'];
                    $booking['check_out'] = $payment_info['check_out'];

                    // Update the database with the correct check-in and check-out dates
                    $update_dates = "UPDATE bookings SET check_in = ?, check_out = ? WHERE booking_ID = ?";
                    $update_stmt = $conn->prepare($update_dates);
                    $update_stmt->bind_param("ssi", $booking['check_in'], $booking['check_out'], $booking_id);
                    $update_stmt->execute();
                }

                // Validate payment_info nights (ensure it's between 1 and 30)
                if (is_numeric($payment_info['nights']) && $payment_info['nights'] >= 1 && $payment_info['nights'] <= 30) {
                    $booking['nights'] = $payment_info['nights'];

                    // Use the amount from payment_info if it's valid
                    if (is_numeric($payment_info['amount']) && $payment_info['amount'] > 0) {
                        $booking['total_price'] = $payment_info['amount'];
                    } else {
                        // Recalculate total price based on validated nights
                        $booking['total_price'] = $booking['nights'] * $booking['room_price'];
                    }

                    // Recalculate price_calculation based on validated nights (for display only)
                    $booking['price_calculation'] = "₱" . number_format($booking['room_price'], 2) . " × " . $booking['nights'] . " night" . ($booking['nights'] > 1 ? "s" : "");

                    // Update the database with the final values
                    $update_final = "UPDATE bookings SET nights = ?, total_price = ? WHERE booking_ID = ?";
                    $update_stmt = $conn->prepare($update_final);
                    $update_stmt->bind_param("idi", $booking['nights'], $booking['total_price'], $booking_id);
                    $update_stmt->execute();
                }
            }
        } else {
            throw new Exception("Booking not found or you don't have permission to view it.");
        }
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - Waterpool Suites</title>
    <?php require('includes/links.php'); ?>
    <style>
        /* Simple styles for student work */
        .success-container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            border: 1px solid #ddd;
        }
        .success-header {
            background-color: #28a745;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .success-header.gcash {
            background-color: #0070E0; /* GCash blue */
            color: white;
        }
        .success-header.paymaya {
            background-color: #00A34E; /* PayMaya green */
            color: black;
        }
        .success-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        .payment-badge {
            display: inline-block;
            padding: 3px 8px;
            background-color: #343a40;
            color: white;
            margin-bottom: 10px;
        }
        .payment-badge.gcash {
            background-color: #0070E0; /* GCash blue */
            color: white;
        }
        .payment-badge.paymaya {
            background-color: #00A34E; /* PayMaya green */
            color: black;
        }
        .booking-details {
            padding: 15px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-table td {
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .info-table td:last-child {
            text-align: right;
        }
        .room-image {
            width: 100%;
            max-height: 180px;
            object-fit: cover;
        }
        .summary-box {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 15px;
        }
        .summary-box h5 {
            background-color: #28a745;
            color: white;
            padding: 5px;
            margin-top: 0;
        }
        .summary-box h5.gcash {
            background-color: #0070E0; /* GCash blue */
            color: white;
        }
        .summary-box h5.paymaya {
            background-color: #00A34E; /* PayMaya green */
            color: black;
        }
        .summary-table {
            width: 100%;
        }
        .summary-table td {
            padding: 3px;
        }
        .summary-table td:last-child {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            border-top: 1px solid #333;
            padding-top: 5px;
        }
        .action-buttons {
            text-align: center;
            margin-top: 15px;
        }
        .action-buttons button, .action-buttons a {
            margin: 0 5px;
        }
        .alert-info.gcash {
            background-color: rgba(0, 112, 224, 0.15); /* Light blue background */
            border-color: rgba(0, 112, 224, 0.3);
            color: #004a94; /* Dark blue text */
        }
        .alert-info.paymaya {
            background-color: rgba(0, 163, 78, 0.15); /* Light green background */
            border-color: rgba(0, 163, 78, 0.3);
            color: #006633; /* Dark green text */
        }
    </style>
</head>
<body class="bg-white">
    <?php require('includes/header.php'); ?>

    <div class="container my-5">
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger">
                <?php echo $error_msg; ?>
                <p>Please try again or contact support.</p>
            </div>
        <?php elseif ($booking): ?>
            <div class="success-container">
                <?php
                $header_class = 'success-header';
                $payment_icon = 'bi-check-circle';
                $payment_method_class = '';

                if ($payment_info && isset($payment_info['payment_method'])) {
                    if ($payment_info['payment_method'] === 'GCash') {
                        $header_class .= ' gcash';
                        $payment_icon = 'bi-wallet2';
                        $payment_method_class = 'gcash';
                    } elseif ($payment_info['payment_method'] === 'PayMaya') {
                        $header_class .= ' paymaya';
                        $payment_icon = 'bi-phone';
                        $payment_method_class = 'paymaya';
                    }
                }
                ?>
                <div class="<?php echo $header_class; ?>">
                    <div class="success-icon">
                        <i class="bi <?php echo $payment_icon; ?>"></i>
                    </div>
                    <h2>Booking Confirmed!</h2>

                    <?php if ($payment_info && isset($payment_info['payment_method'])): ?>
                        <div class="payment-badge <?php echo $payment_method_class; ?>">
                            <?php echo htmlspecialchars($payment_info['payment_method']); ?>
                        </div>

                        <?php if ($payment_info['payment_method'] === 'GCash'): ?>
                            <p>Your GCash payment was successful.</p>
                        <?php elseif ($payment_info['payment_method'] === 'PayMaya'): ?>
                            <p>Your PayMaya payment was successful.</p>
                        <?php else: ?>
                            <p>Your payment was successful.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>Your payment was successful.</p>
                    <?php endif; ?>
                </div>

                <div class="booking-details">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Booking Information</h4>
                            <table class="info-table">
                                <tr>
                                    <td>Booking ID:</td>
                                    <td><?php echo htmlspecialchars($booking['booking_ID']); ?></td>
                                </tr>
                                <tr>
                                    <td>Status:</td>
                                    <td><span class="badge bg-success"><?php echo htmlspecialchars($booking['status']); ?></span></td>
                                </tr>
                                <tr>
                                    <td>Booked On:</td>
                                    <td><?php
                                        $created_timestamp = isset($booking['created_at']) ? strtotime($booking['created_at']) : false;
                                        echo ($created_timestamp !== false) ? date('F j, Y', $created_timestamp) : date('F j, Y');
                                    ?></td>
                                </tr>
                                <tr>
                                    <td>Email:</td>
                                    <td><?php echo htmlspecialchars($booking['user_email']); ?></td>
                                </tr>
                                <tr>
                                    <td>Guests:</td>
                                    <td><?php echo htmlspecialchars($booking['adults']); ?> Adults, <?php echo htmlspecialchars($booking['children']); ?> Children</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h4>Room Details</h4>
                            <img src="images/rooms/<?php echo htmlspecialchars($booking['room_image']); ?>"
                                class="room-image mb-2" alt="Room Image"
                                onerror="this.src='images/placeholder.png';">
                            <h5><?php echo htmlspecialchars($booking['room_name']); ?></h5>
                            <table class="info-table">
                                <tr>
                                    <td>Check-in:</td>
                                    <td><?php
                                        // Use check-in date from payment_info if available, otherwise use from booking
                                        $check_in_date = isset($payment_info['check_in']) && !empty($payment_info['check_in'])
                                            ? $payment_info['check_in'] : $booking['check_in'];
                                        echo date('F j, Y', strtotime($check_in_date));
                                    ?></td>
                                </tr>
                                <tr>
                                    <td>Check-out:</td>
                                    <td><?php
                                        // Use check-out date from payment_info if available, otherwise use from booking
                                        $check_out_date = isset($payment_info['check_out']) && !empty($payment_info['check_out'])
                                            ? $payment_info['check_out'] : $booking['check_out'];
                                        echo date('F j, Y', strtotime($check_out_date));
                                    ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <?php if ($payment_info): ?>
                    <div class="summary-box">
                        <h5 class="<?php echo $payment_method_class; ?>">Payment Information</h5>
                        <table class="info-table">
                            <tr>
                                <td>Payment Method:</td>
                                <td>
                                    <?php
                                    $payment_method_icon = 'bi-credit-card';
                                    if ($payment_info['payment_method'] === 'GCash') {
                                        $payment_method_icon = 'bi-wallet2';
                                    } elseif ($payment_info['payment_method'] === 'PayMaya') {
                                        $payment_method_icon = 'bi-phone';
                                    }
                                    ?>
                                    <i class="bi <?php echo $payment_method_icon; ?>"></i>
                                    <?php echo htmlspecialchars($payment_info['payment_method']); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Transaction ID:</td>
                                <td><?php echo htmlspecialchars($payment_info['transaction_id']); ?></td>
                            </tr>
                            <tr>
                                <td>Payment Date:</td>
                                <td><?php echo date('F j, Y', strtotime($payment_info['payment_date'])); ?></td>
                            </tr>
                            <?php if (strpos($payment_info['payment_method'], 'Card') !== false): ?>
                            <tr>
                                <td>Card Number:</td>
                                <td><?php echo htmlspecialchars($payment_info['card_number']); ?></td>
                            </tr>
                            <tr>
                                <td>Cardholder Name:</td>
                                <td><?php echo htmlspecialchars($payment_info['cardholder_name'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <td>Account Name:</td>
                                <td><?php echo htmlspecialchars($payment_info['account_name'] ?? ''); ?></td>
                            </tr>
                            <?php elseif ($payment_info['payment_method'] === 'GCash'): ?>
                            <tr>
                                <td>GCash Number:</td>
                                <td><?php echo htmlspecialchars($payment_info['card_number']); ?></td>
                            </tr>
                            <tr>
                                <td>Account Name:</td>
                                <td><?php echo htmlspecialchars($payment_info['account_name'] ?? ''); ?></td>
                            </tr>
                            <?php elseif ($payment_info['payment_method'] === 'PayMaya'): ?>
                            <tr>
                                <td>PayMaya Number:</td>
                                <td><?php echo htmlspecialchars($payment_info['card_number']); ?></td>
                            </tr>
                            <tr>
                                <td>Account Name:</td>
                                <td><?php echo htmlspecialchars($payment_info['account_name'] ?? ''); ?></td>
                            </tr>
                            <?php endif; ?>

                        </table>
                    </div>
                    <?php endif; ?>

                    <div class="summary-box">
                        <h5 class="<?php echo $payment_method_class; ?>">Stay Summary</h5>

                        <div class="alert alert-info <?php echo $payment_method_class; ?>">
                            <h6><strong>Price Calculation</strong></h6>
                            <table class="summary-table">
                                <tr>
                                    <td>Room Rate:</td>
                                    <td>₱<?php echo number_format($booking['room_price'], 2); ?> per night</td>
                                </tr>
                                <tr>
                                    <td>Number of Nights:</td>
                                    <td><?php echo $booking['nights']; ?></td>
                                </tr>
                                <tr>
                                    <td>Calculation:</td>
                                    <td><?php
                                        // Always calculate the price calculation string on-the-fly
                                        echo "₱" . number_format($booking['room_price'], 2) . " × " . $booking['nights'] . " night" . ($booking['nights'] > 1 ? "s" : "");
                                    ?></td>
                                </tr>
                                <tr class="total-row">
                                    <td>Total:</td>
                                    <td>₱<?php echo number_format($booking['total_price'], 2); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <a href="generate_receipt_pdf.php?booking_id=<?php echo $booking['booking_ID']; ?>" class="btn btn-primary" target="_blank">
                            <i class="bi bi-file-pdf me-1"></i> Download PDF Receipt
                        </a>
                        <a href="my_bookings.php" class="btn btn-success">
                            <i class="bi bi-list-check me-1"></i> View My Bookings
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-house me-1"></i> Back to Home
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <h5>No booking information found</h5>
                <p>Please return to the <a href="index.php" class="alert-link">homepage</a> and try again.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php require('includes/footer.php'); ?>

    <?php
    // Clear payment info from session after displaying it
    if (isset($_SESSION['payment_info'])) {
        unset($_SESSION['payment_info']);
    }
    ?>
</body>
</html>