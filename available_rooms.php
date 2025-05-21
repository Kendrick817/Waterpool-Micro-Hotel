<?php
// This page shows available rooms based on search criteria
// It gets dates and guest numbers from the search form

// Include required files
require('includes/db_config.php'); // Connect to database
require('includes/functions.php'); // Get helper functions

// Make sure all form fields are filled out
if (empty($_POST['check_in']) || empty($_POST['check_out']) || empty($_POST['adults']) || empty($_POST['children'])) {
    // If any field is empty, show error and go back to homepage
    echo "<script>alert('Invalid input. Please fill out all fields.'); window.location.href='index.php';</script>";
    exit;
}

// Save the search information from the form
$check_in = $_POST['check_in'];
$check_out = $_POST['check_out'];
$adults = $_POST['adults'];
$children = $_POST['children'];

// Make sure check-in date is before check-out date
if ($check_in >= $check_out) {
    // If dates are invalid, show error and go back to homepage
    echo "<script>alert('Check-in date must be before check-out date.'); window.location.href='index.php';</script>";
    exit;
}

// Find rooms that aren't already booked for these dates
// This SQL query excludes rooms that have bookings overlapping with our dates
$query = "SELECT * FROM rooms WHERE
    room_ID NOT IN (
        SELECT room_ID FROM bookings
        WHERE ('$check_in' BETWEEN check_in AND check_out)
        OR ('$check_out' BETWEEN check_in AND check_out)
        OR (check_in BETWEEN '$check_in' AND '$check_out')
    )";
$result = mysqli_query($conn, $query);

// If there was a database error
if (!$result) {
    echo "<h5 class='text-center text-danger'>Error fetching rooms: " . mysqli_error($conn) . "</h5>";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Rooms</title>
    <?php require('includes/links.php'); ?>
    <style>
        /* These styles make the room cards look nice */

        /* Add smooth animation when hovering over cards */
        .card {
            transition: transform 0.3s, box-shadow 0.3s;
        }

        /* Make cards lift up when mouse hovers over them */
        .card:hover {
            transform: translateY(-10px); /* Move up by 10px */
            box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important; /* Add shadow */
        }

        /* Make all room images the same size */
        .card-img-top {
            height: 250px;
            object-fit: cover; /* Crop image to fit container */
        }

        /* Add space inside the cards */
        .card-body {
            padding: 1.5rem;
        }

        /* Make room names bigger */
        .card h5 {
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
        }

        /* Style the buttons */
        .card .btn {
            padding: 8px 15px;
            font-weight: 500;
            transition: all 0.3s; /* Smooth animation */
        }

        /* Make buttons lift up when hovered */
        .card .btn:hover {
            transform: translateY(-3px); /* Move up slightly */
        }
    </style>
</head>
<body class="bg-white">
    <?php require('includes/header.php'); ?>

    <div class="container my-5">
        <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font">AVAILABLE ROOMS</h2>
        <div class="h-line bg-dark"></div>
        <br>
        <div class="row">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "
                    <div class='col-lg-4 col-md-6 my-3'>
                        <div class='card border-0 shadow' style='max-width: 450px; margin: auto;'>
                            <img src='images/rooms/{$row['image']}' class='card-img-top' alt='Room Image'>
                            <div class='card-body'>
                                <h5>{$row['name']}</h5>
                                <h6 class='mb-4'>₱" . number_format($row['price'], 2) . " per night</h6>
                                <div class='features mb-4'>
                                    <h6 class='mb-1'>Features</h6>
                                    <div>
                                        " . display_features($row['features']) . "
                                    </div>
                                </div>
                                <div class='facilities mb-4'>
                                    <h6 class='mb-1'>Facilities</h6>
                                    <span class='badge rounded-pill bg-light text-dark text-wrap'>Wifi</span>
                                    <span class='badge rounded-pill bg-light text-dark text-wrap'>Television</span>
                                    <span class='badge rounded-pill bg-light text-dark text-wrap'>Aircon</span>
                                    <span class='badge rounded-pill bg-light text-dark text-wrap'>Room Heater</span>
                                </div>
                                <div class='ratings mb-4'>
                                    <h6 class='mb-1'>Rating</h6>
                                    <span class='badge rounded-pill bg-light'>
                                        <i class='bi bi-star-fill text-warning'></i>
                                        <i class='bi bi-star-fill text-warning'></i>
                                        <i class='bi bi-star-fill text-warning'></i>
                                        <i class='bi bi-star-fill text-warning'></i>
                                    </span>
                                </div>
                                <div class='d-flex justify-content-evenly mb-2'>
                                    <a href='booking.php?room_id={$row['room_ID']}' class='btn text-white custom-bg shadow-none'>
                                        <i class='bi bi-calendar-check me-1'></i> Book Now
                                    </a>
                                    <a href='room_details.php?id={$row['room_ID']}' class='btn btn-outline-dark shadow-none'>
                                        <i class='bi bi-info-circle me-1'></i> Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    ";
                }
            } else {
                echo "<h5 class='text-center'>No rooms available for the selected dates.</h5>";
            }
            ?>
        </div>
    </div>

    <?php require('includes/footer.php'); ?>
</body>
</html>