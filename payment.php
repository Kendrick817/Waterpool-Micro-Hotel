<?php
// This is the payment page
// It lets users pay for their room booking using different payment methods

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php'); // Send to login page if not logged in
    exit;
}
require('includes/db_config.php');

// Create empty variables to use later
$error_msg = '';
$booking = null;
$room = null;

// Get booking details
if (isset($_GET['booking_id']) && is_numeric($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];

    try {
        // Use prepared statement for security
        $query = "SELECT b.*, r.name as room_name, r.price as room_price, r.image as room_image
                 FROM bookings b
                 JOIN rooms r ON b.room_ID = r.room_ID
                 WHERE b.booking_ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Error preparing query: " . $conn->error);
        }

        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $booking = $result->fetch_assoc();

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
            $num_nights = $interval->days;

            // Validate number of nights (1-30 nights is reasonable)
            if ($num_nights < 1) {
                $num_nights = 1;
            } elseif ($num_nights > 30) {
                $num_nights = 30; // Cap at 30 nights for reasonability
            }

            // Add to booking array
            $booking['num_nights'] = $num_nights;

            // Update the database with the validated nights
            $update_nights = "UPDATE bookings SET nights = ? WHERE booking_ID = ?";
            $update_stmt = $conn->prepare($update_nights);
            $update_stmt->bind_param("ii", $num_nights, $booking_id);
            $update_stmt->execute();

            // Calculate total price based on room price and number of nights
            if (!isset($booking['room_price']) || !is_numeric($booking['room_price']) || $booking['room_price'] <= 0) {
                // Set a default room price if invalid
                $booking['room_price'] = 1000; // Default price of 1000
            }

            // Calculate total price
            $total_price = $num_nights * $booking['room_price'];
            $booking['total_price'] = $total_price;

            // Update the database with the calculated total price
            $update_price = "UPDATE bookings SET total_price = ? WHERE booking_ID = ?";
            $update_stmt = $conn->prepare($update_price);
            $update_stmt->bind_param("di", $total_price, $booking_id);
            $update_stmt->execute();

            // Create price calculation string for display only (not stored in database)
            $booking['price_calculation'] = "₱" . number_format($booking['room_price'], 2) . " × " . $booking['num_nights'] . " night" . ($booking['num_nights'] > 1 ? "s" : "");
        } else {
            throw new Exception("Booking not found.");
        }
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
    }
} else {
    $error_msg = "Invalid booking ID.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Waterpool Suites</title>
    <?php require('includes/links.php'); ?>
    <style>
        /* Style for the room image in the booking summary */
        .room-image {
            width: 100%;
            height: 150px;
            object-fit: cover; /* Crop image to fit container */
            border-radius: 10px; /* Rounded corners */
        }

        /* Style for the payment method cards */
        .payment-card {
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Add shadow */
        }
    </style>
</head>
<body class="bg-white">
    <?php require('includes/header.php'); ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold h-font">Payment</h2>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif (isset($_SESSION['payment_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Payment Error:</strong> <?php echo $_SESSION['payment_error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['payment_error']); ?>
        <?php elseif ($booking): ?>
            <div class="row justify-content-center mt-4">
                <!-- Booking Summary -->
                <div class="col-lg-5 mb-4">
                    <div class="card border-0 shadow h-100">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Booking Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <img src="images/rooms/<?php echo htmlspecialchars($booking['room_image']); ?>"
                                    class="room-image mb-2" alt="Room Image"
                                    onerror="this.src='images/placeholder.png';">
                                <h4><?php echo htmlspecialchars($booking['room_name']); ?></h4>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <p class="mb-1"><strong>Check-in:</strong></p>
                                    <p class="text-muted"><?php echo date('F j, Y', strtotime($booking['check_in'])); ?></p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-1"><strong>Check-out:</strong></p>
                                    <p class="text-muted"><?php echo date('F j, Y', strtotime($booking['check_out'])); ?></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <p class="mb-1"><strong>Guests:</strong></p>
                                    <p class="text-muted"><?php echo $booking['adults']; ?> Adults, <?php echo $booking['children']; ?> Children</p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-1"><strong>Status:</strong></p>
                                    <span class="badge bg-warning"><?php echo $booking['status']; ?></span>
                                </div>
                            </div>

                            <hr>

                            <div class="row mb-2">
                                <div class="col-8">
                                    <p class="mb-0">Room Rate (per night)</p>
                                </div>
                                <div class="col-4 text-end">
                                    <p class="mb-0">₱<?php echo number_format($booking['room_price'], 2); ?></p>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-8">
                                    <p class="mb-0">Number of Nights</p>
                                </div>
                                <div class="col-4 text-end">
                                    <p class="mb-0"><?php echo $booking['num_nights']; ?></p>
                                </div>
                            </div>
                            <div class="alert alert-info mb-2 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small><i class="bi bi-info-circle-fill me-1"></i> Calculation:</small>
                                    <small><?php echo $booking['price_calculation']; ?></small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-8">
                                    <p class="fw-bold mb-0">Total Amount</p>
                                </div>
                                <div class="col-4 text-end">
                                    <p class="fw-bold mb-0">₱<?php echo number_format($booking['total_price'], 2); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <div class="col-lg-7 bg-white rounded shadow p-4">
                    <h4 class="mb-4">Payment Details</h4>
                    <form method="POST" action="process_payment.php" id="payment-form">
                        <!-- Payment Method Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Payment Method</label>
                            <div class="payment-methods">
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check payment-method-option">
                                            <input class="form-check-input" type="radio" name="payment_method" id="credit-card" value="credit_card" checked>
                                            <label class="form-check-label w-100" for="credit-card">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-credit-card me-2"></i> Credit/Debit Card
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check payment-method-option">
                                            <input class="form-check-input" type="radio" name="payment_method" id="gcash" value="gcash">
                                            <label class="form-check-label w-100" for="gcash">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-wallet2 me-2"></i> GCash
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check payment-method-option">
                                            <input class="form-check-input" type="radio" name="payment_method" id="paymaya" value="paymaya">
                                            <label class="form-check-label w-100" for="paymaya">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-phone me-2"></i> PayMaya
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Credit/Debit Card Form -->
                        <div id="credit-card-form">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Cardholder Name</label>
                                <input type="text" name="cardholder_name" class="form-control shadow-none" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Account Name</label>
                                <input type="text" name="card_account_name" class="form-control shadow-none" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Card Number</label>
                                <div class="input-group">
                                    <input type="text" name="card_number" class="form-control shadow-none"
                                        placeholder="XXXX XXXX XXXX XXXX" maxlength="19" required>
                                    <span class="input-group-text">
                                        <i class="bi bi-credit-card"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Expiry Date</label>
                                    <input type="month" name="expiry_date" class="form-control shadow-none" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">CVV</label>
                                    <input type="text" name="cvv" class="form-control shadow-none"
                                        placeholder="XXX" maxlength="3" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Billing Address</label>
                                <textarea name="billing_address" class="form-control shadow-none" rows="2" required></textarea>
                            </div>
                        </div>

                        <!-- GCash Form -->
                        <div id="gcash-form" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label fw-bold">GCash Mobile Number</label>
                                <div class="input-group">
                                    <span class="input-group-text">+63</span>
                                    <input type="text" name="gcash_number" class="form-control shadow-none"
                                        placeholder="9XX XXX XXXX" maxlength="12">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Account Name</label>
                                <input type="text" name="gcash_name" class="form-control shadow-none">
                            </div>
                        </div>

                        <!-- PayMaya Form -->
                        <div id="paymaya-form" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label fw-bold">PayMaya Mobile Number</label>
                                <div class="input-group">
                                    <span class="input-group-text">+63</span>
                                    <input type="text" name="paymaya_number" class="form-control shadow-none"
                                        placeholder="9XX XXX XXXX" maxlength="12">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Account Name</label>
                                <input type="text" name="paymaya_name" class="form-control shadow-none">
                            </div>
                        </div>

                        <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                        <input type="hidden" name="amount" value="<?php echo $booking['total_price']; ?>">

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-dark shadow-none">
                                <i class="bi bi-credit-card"></i> Pay ₱<?php echo number_format($booking['total_price'], 2); ?>
                            </button>
                            <br>
                        </div>

                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="bi bi-shield-lock"></i> Your payment information is secure and encrypted
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <h5>No booking information found</h5>
                <p>Please go back to <a href="rooms.php" class="alert-link">rooms</a> and make a booking.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php require('includes/footer.php'); ?>

    <script>
        // This script handles the payment form functionality

        // Add spaces to credit card numbers (like 1234 5678 9012 3456)
        document.querySelector('input[name="card_number"]').addEventListener('input', function(e) {
            // Remove any spaces and non-number characters
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = '';

            // Add a space after every 4 digits
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }

            // Update the input field with formatted value
            e.target.value = formattedValue;
        });

        // Make sure CVV only contains numbers
        document.querySelector('input[name="cvv"]').addEventListener('input', function(e) {
            // Remove any non-number characters
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });

        // Format phone numbers for GCash and PayMaya (like 917 555 1234)
        document.querySelectorAll('input[name="gcash_number"], input[name="paymaya_number"]').forEach(function(input) {
            input.addEventListener('input', function(e) {
                // Remove any non-number characters
                let value = e.target.value.replace(/[^0-9]/gi, '');
                let formattedValue = '';

                // Add spaces after the 3rd and 6th digits
                for (let i = 0; i < value.length && i < 10; i++) {
                    if (i === 3 || i === 6) {
                        formattedValue += ' ';
                    }
                    formattedValue += value[i];
                }

                // Update the input field with formatted value
                e.target.value = formattedValue;
            });
        });

        // Get references to the payment method radio buttons and form sections
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const creditCardForm = document.getElementById('credit-card-form');
        const gcashForm = document.getElementById('gcash-form');
        const paymayaForm = document.getElementById('paymaya-form');

        // This function makes fields required or not required based on payment method
        function toggleRequiredFields(method) {
            // First, make all fields not required
            document.querySelectorAll('#payment-form input, #payment-form textarea').forEach(field => {
                if (field.name !== 'booking_id' && field.name !== 'amount' && field.name !== 'payment_method') {
                    field.required = false;
                }
            });

            // Then make only the fields for the selected payment method required
            if (method === 'credit_card') {
                // Make credit card fields required
                document.querySelectorAll('#credit-card-form input, #credit-card-form textarea').forEach(field => {
                    field.required = true;
                });
            } else if (method === 'gcash') {
                // Make GCash fields required
                document.querySelectorAll('#gcash-form input').forEach(field => {
                    field.required = true;
                });
            } else if (method === 'paymaya') {
                // Make PayMaya fields required
                document.querySelectorAll('#paymaya-form input').forEach(field => {
                    field.required = true;
                });
            }
        }

        // When user changes payment method, show the right form
        paymentMethods.forEach(function(radio) {
            radio.addEventListener('change', function() {
                // Hide all payment forms
                creditCardForm.style.display = 'none';
                gcashForm.style.display = 'none';
                paymayaForm.style.display = 'none';

                // Show only the selected payment form
                if (this.value === 'credit_card') {
                    creditCardForm.style.display = 'block';
                } else if (this.value === 'gcash') {
                    gcashForm.style.display = 'block';
                } else if (this.value === 'paymaya') {
                    paymayaForm.style.display = 'block';
                }

                // Update which fields are required
                toggleRequiredFields(this.value);
            });
        });

        // When page loads, set up the form for credit card (the default option)
        toggleRequiredFields('credit_card');
    </script>
</body>
</html>