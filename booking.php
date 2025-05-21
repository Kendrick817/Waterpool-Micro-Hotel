<?php
// This is the booking page
// Users can book a room by entering their details

// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php'); // Send to login page if not logged in
    exit;
}
require('includes/db_config.php');

// Set up empty variables we'll use later
$error_msg = '';
$room = null;
$room_id = null;

// Get room_id from URL parameter
if (isset($_GET['room_id']) && is_numeric($_GET['room_id'])) {
    $room_id = $_GET['room_id'];

    // Fetch room details to display on the form
    try {
        $query = "SELECT * FROM rooms WHERE room_ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Error preparing query: " . $conn->error);
        }

        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $room = $result->fetch_assoc();
        } else {
            throw new Exception("Room not found!");
        }
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
    }
} else if (!isset($_POST['room_id'])) {
    // If no room_id in URL and not a form submission, redirect to rooms page
    header('Location: rooms.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Get form data
        $room_id = isset($_POST['room_id']) ? $_POST['room_id'] : null;
        $check_in = $_POST['check_in'];
        $check_out = $_POST['check_out'];
        $adults = $_POST['adults'];
        $children = isset($_POST['children']) ? $_POST['children'] : 0;
        $user_email = $_SESSION['user_email']; // Get the logged-in user's email

        // Validate input data
        if (empty($room_id) || empty($check_in) || empty($check_out) || empty($adults)) {
            throw new Exception("Please fill out all required fields.");
        }

        if ($check_in >= $check_out) {
            throw new Exception("Check-in date must be before check-out date.");
        }

        // Check if the room is available for the selected dates
        $availability_query = "SELECT * FROM bookings
                              WHERE room_ID = ?
                              AND ((check_in BETWEEN ? AND ?)
                              OR (check_out BETWEEN ? AND ?)
                              OR (? BETWEEN check_in AND check_out))";
        $avail_stmt = $conn->prepare($availability_query);

        if (!$avail_stmt) {
            throw new Exception("Error checking room availability: " . $conn->error);
        }

        $avail_stmt->bind_param("isssss", $room_id, $check_in, $check_out, $check_in, $check_out, $check_in);
        $avail_stmt->execute();
        $avail_result = $avail_stmt->get_result();

        if ($avail_result->num_rows > 0) {
            throw new Exception("This room is not available for the selected dates. Please choose different dates.");
        }

        // Get nights and total_price from form if available, otherwise calculate
        // Get room price first (needed in all code paths)
        $room_query = "SELECT price FROM rooms WHERE room_ID = ?";
        $room_stmt = $conn->prepare($room_query);

        if (!$room_stmt) {
            throw new Exception("Error preparing room query: " . $conn->error);
        }

        $room_stmt->bind_param("i", $room_id);
        $room_stmt->execute();
        $room_result = $room_stmt->get_result();

        if ($room_result->num_rows == 0) {
            throw new Exception("Room not found.");
        }

        $room_data = $room_result->fetch_assoc();
        $room_price = $room_data['price'];

        // Get nights and total_price from form if available
        if (isset($_POST['nights']) && isset($_POST['total_price']) &&
            is_numeric($_POST['nights']) && is_numeric($_POST['total_price']) &&
            $_POST['nights'] > 0 && $_POST['total_price'] > 0) {

            $nights = intval($_POST['nights']);
            // Cap at 30 nights for reasonability
            if ($nights > 30) {
                $nights = 30;
            }

            $total_price = floatval($_POST['total_price']);
        } else {
            // Calculate number of nights
            $check_in_date = new DateTime($check_in);
            $check_out_date = new DateTime($check_out);

            // Ensure check-out is after check-in
            if ($check_out_date <= $check_in_date) {
                throw new Exception("Check-out date must be after check-in date.");
            }

            $interval = $check_in_date->diff($check_out_date);
            $nights = $interval->days;

            // Validate number of nights (1-30 nights is reasonable)
            if ($nights < 1) {
                $nights = 1;
            } elseif ($nights > 30) {
                $nights = 30; // Cap at 30 nights instead of throwing an exception
            }

            // Calculate total price with validation
            $total_price = $nights * $room_price;

            // Validate total price
            if ($total_price <= 0) {
                throw new Exception("Invalid total price calculation. Please try again.");
            }

        }

        // Validate nights and total_price
        if ($nights <= 0) {
            throw new Exception("Invalid number of nights. Please select valid check-in and check-out dates.");
        }

        // Insert booking into the database using prepared statement
        $insert_query = "INSERT INTO bookings (room_ID, user_email, check_in, check_out, adults, children, status, nights, total_price)
                        VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);

        if (!$insert_stmt) {
            throw new Exception("Error preparing booking query: " . $conn->error);
        }

        $insert_stmt->bind_param("issiiiid", $room_id, $user_email, $check_in, $check_out, $adults, $children, $nights, $total_price);

        if ($insert_stmt->execute()) {
            // Redirect to payment page with booking details
            $booking_id = $insert_stmt->insert_id; // Get the last inserted booking ID
            header("Location: payment.php?booking_id=$booking_id");
            exit;
        } else {
            throw new Exception("Error creating booking: " . $insert_stmt->error);
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
    <title>Book a Room - Waterpool Suites</title>
    <?php require('includes/links.php'); ?>
    <style>
        .room-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }
        .card-title {
            color: #2c3e50;
            font-weight: 600;
        }
        .feature-list {
            margin-bottom: 0;
            padding-left: 1.2rem;
        }
        .feature-list li {
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }
        .booking-form-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        .room-details-card {
            border: 1px solid rgba(0,0,0,0.125);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        .room-details-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .room-details-img {
            height: 100%;
            object-fit: cover;
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }
        @media (max-width: 767.98px) {
            .room-details-img {
                height: 200px;
                border-radius: 8px 8px 0 0;
            }
        }
    </style>
</head>
<body class="bg-light">
    <?php require('includes/header.php'); ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold h-font">Book a Room</h2>
        </div>
        <div class="row justify-content-center mt-4">
            <!-- Booking Form Section -->
            <div class="col-lg-8 bg-white rounded shadow p-4">
                 <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
                <h4 class="mb-4">Booking Details</h4>
                <form method="POST">
                    <?php if ($room): ?>
                        <input type="hidden" name="room_id" value="<?php echo $room['room_ID']; ?>">
                        <input type="hidden" id="room_price" value="<?php echo $room['price']; ?>">

                        <!-- Room Details Card -->
                        <div class="card room-details-card mb-4">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <img src="images/rooms/<?php echo htmlspecialchars($room['image']); ?>"
                                        class="img-fluid room-details-img" alt="Room Image"
                                        onerror="this.src='images/placeholder.png';">
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h4 class="card-title mb-0"><?php echo htmlspecialchars($room['name']); ?></h4>
                                            <span class="badge bg-success rounded-pill">Available</span>
                                        </div>
                                        <h5 class="text-success mb-3">₱<?php echo number_format($room['price'], 2); ?> per night</h5>
                                        <div class="card-text">
                                            <p class="mb-1"><strong><i class="bi bi-list-check me-2"></i>Room Features:</strong></p>
                                            <ul class="feature-list">
                                                <?php
                                                $features = explode("\n", $room['features']);
                                                foreach ($features as $feature) {
                                                    $feature = trim($feature);
                                                    if (!empty($feature)) {
                                                        echo "<li>" . htmlspecialchars($feature) . "</li>";
                                                    }
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="booking-summary" class="card mb-3" style="display: none;">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">Booking Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <p><strong>Check-in:</strong></p>
                                        <p id="summary-check-in">-</p>
                                    </div>
                                    <div class="col-6">
                                        <p><strong>Check-out:</strong></p>
                                        <p id="summary-check-out">-</p>
                                    </div>
                                </div>
                                <div class="alert alert-light border mb-3">
                                    <p><strong>Stay Duration:</strong> <span id="num-nights">0</span> night(s)</p>
                                </div>
                                <hr>
                                <div class="row mb-2">
                                    <div class="col-8">
                                        <p>Room Rate (per night)</p>
                                    </div>
                                    <div class="col-4 text-end">
                                        <p>₱<?php echo number_format($room['price'], 2); ?></p>
                                    </div>
                                </div>
                                <div class="alert alert-info mb-2">
                                    <p><strong>Calculation:</strong> <span id="price-calculation">₱<?php echo number_format($room['price'], 2); ?> × 0 nights</span></p>
                                </div>
                                <div class="row">
                                    <div class="col-8">
                                        <p><strong>Total Amount</strong></p>
                                    </div>
                                    <div class="col-4 text-end">
                                        <p><strong id="total-amount">₱0.00</strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Room ID</label>
                            <input type="number" name="room_id" class="form-control shadow-none" required>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Check-in Date</label>
                            <input type="date" name="check_in" class="form-control shadow-none"
                                min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Check-out Date</label>
                            <input type="date" name="check_out" class="form-control shadow-none"
                                min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Adults</label>
                            <input type="number" name="adults" min="1" max="10" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Children</label>
                            <input type="number" name="children" min="0" max="10" value="0" class="form-control shadow-none">
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-dark shadow-none">
                            <i class="bi bi-calendar-check"></i> Confirm Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require('includes/footer.php'); ?>

    <script>
        // Simple function to format a date as YYYY-MM-DD
        function formatDate(date) {
            var year = date.getFullYear();
            var month = (date.getMonth() + 1).toString().padStart(2, '0');
            var day = date.getDate().toString().padStart(2, '0');
            return year + '-' + month + '-' + day;
        }

        // Calculate number of nights between two dates
        function calculateNights(checkInDate, checkOutDate) {
            var timeDiff = checkOutDate.getTime() - checkInDate.getTime();
            return Math.ceil(timeDiff / (1000 * 3600 * 24));
        }

        // Format date for display (e.g., "January 1, 2023")
        function formatDateForDisplay(date) {
            var options = { year: 'numeric', month: 'long', day: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }

        // Update the booking summary
        function updateBookingSummary(nights, totalAmount, checkInDate, checkOutDate) {
            var bookingSummary = document.getElementById('booking-summary');
            var roomPrice = parseFloat(document.getElementById('room_price').value);

            // Update nights and total amount
            document.getElementById('num-nights').textContent = nights;
            document.getElementById('total-amount').textContent = '₱' + totalAmount.toFixed(2);

            // Update calculation text
            var nightText = nights > 1 ? 'nights' : 'night';
            document.getElementById('price-calculation').textContent = '₱' + roomPrice.toFixed(2) + ' × ' + nights + ' ' + nightText;

            // Update check-in and check-out dates
            document.getElementById('summary-check-in').textContent = formatDateForDisplay(checkInDate);
            document.getElementById('summary-check-out').textContent = formatDateForDisplay(checkOutDate);

            // Show the booking summary
            bookingSummary.style.display = 'block';

            // Add hidden fields for nights and total price
            var nightsInput = document.getElementById('nights-input');
            var totalPriceInput = document.getElementById('total-price-input');

            if (!nightsInput) {
                nightsInput = document.createElement('input');
                nightsInput.type = 'hidden';
                nightsInput.name = 'nights';
                nightsInput.id = 'nights-input';
                document.querySelector('form').appendChild(nightsInput);
            }

            if (!totalPriceInput) {
                totalPriceInput = document.createElement('input');
                totalPriceInput.type = 'hidden';
                totalPriceInput.name = 'total_price';
                totalPriceInput.id = 'total-price-input';
                document.querySelector('form').appendChild(totalPriceInput);
            }

            nightsInput.value = nights;
            totalPriceInput.value = totalAmount;
        }

        // Calculate booking summary
        function calculateBookingSummary() {
            var checkInInput = document.querySelector('input[name="check_in"]');
            var checkOutInput = document.querySelector('input[name="check_out"]');
            var roomPriceInput = document.getElementById('room_price');
            var bookingSummary = document.getElementById('booking-summary');

            if (!checkInInput || !checkOutInput || !roomPriceInput || !bookingSummary) {
                return; // Exit if any element is not found
            }

            if (checkInInput.value && checkOutInput.value) {
                var checkInDate = new Date(checkInInput.value);
                var checkOutDate = new Date(checkOutInput.value);

                // Calculate the difference in days
                var nights = calculateNights(checkInDate, checkOutDate);

                if (nights > 0) {
                    var roomPrice = parseFloat(roomPriceInput.value);
                    var totalAmount = nights * roomPrice;

                    // Update the booking summary with dates
                    updateBookingSummary(nights, totalAmount, checkInDate, checkOutDate);

                    // Update the check-out message
                    var checkOutMessage = document.getElementById('check-out-message');
                    if (checkOutMessage) {
                        var nightText = nights > 1 ? 'nights' : 'night';
                        checkOutMessage.innerHTML =
                            '<i class="bi bi-info-circle-fill me-1"></i>' +
                            'Your stay is for ' + nights + ' ' + nightText + ' from ' +
                            formatDateForDisplay(checkInDate) + ' to ' + formatDateForDisplay(checkOutDate) + '.';
                    }
                }
            }
        }

        // Set minimum check-out date based on check-in date
        document.querySelector('input[name="check_in"]').addEventListener('change', function() {
            var checkInDate = new Date(this.value);
            var checkOutInput = document.querySelector('input[name="check_out"]');

            // Default to 1 night stay (next day)
            var nextDay = new Date(checkInDate);
            nextDay.setDate(checkInDate.getDate() + 1);

            var formattedDate = formatDate(nextDay);
            checkOutInput.min = formattedDate;

            // If current check-out date is before or equal to check-in date, update it to next day
            if (new Date(checkOutInput.value) <= checkInDate) {
                checkOutInput.value = formattedDate;
            }

            // Calculate booking summary
            calculateBookingSummary();
        });

        // Recalculate when check-out date changes
        document.querySelector('input[name="check_out"]').addEventListener('change', function() {
            calculateBookingSummary();
        });

        // Add a small info message about nights calculation
        var nightsInfoDiv = document.createElement('div');
        nightsInfoDiv.className = 'mb-3';
        nightsInfoDiv.innerHTML =
            '<small id="check-out-message" class="text-muted">' +
            '<i class="bi bi-info-circle-fill me-1"></i>' +
            'The number of nights will be calculated automatically based on your check-in and check-out dates.' +
            '</small>';

        // Insert the info message after the check-out date field
        var checkOutField = document.querySelector('input[name="check_out"]').closest('.mb-3');
        checkOutField.parentNode.insertBefore(nightsInfoDiv, checkOutField.nextSibling);

        // Calculate on page load if dates are already set
        window.addEventListener('load', calculateBookingSummary);
    </script>
</body>
</html>