<?php
// This is the rooms page
// It shows all available rooms and lets users filter them

// Start the session to maintain user login state
session_start();

// Include required files
require('includes/db_config.php'); // Connect to database
require('includes/functions.php'); // Get helper functions

// Create an empty array to store filter options
$filters = [];

// Check if the form was submitted (when user clicks "Apply" on filters)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save the check-in and check-out dates if provided
    if (!empty($_POST['checkin']) && !empty($_POST['checkout'])) {
        $filters['checkin'] = $_POST['checkin'];
        $filters['checkout'] = $_POST['checkout'];
    }

    // Save the selected facilities (like Wi-Fi, TV, etc.)
    if (!empty($_POST['facilities']) && is_array($_POST['facilities'])) {
        $filters['facilities'] = $_POST['facilities'];
    }

    // Save the number of adults
    if (!empty($_POST['adults'])) {
        $filters['adults'] = intval($_POST['adults']);
    }

    // Save the number of children
    if (!empty($_POST['children'])) {
        $filters['children'] = intval($_POST['children']);
    }

    // For testing - uncomment to see what filters are being applied
    // echo "<pre>Filters: "; print_r($filters); echo "</pre>";
}

// Get rooms that match the filters (or all rooms if no filters)
$rooms = filter_rooms($conn, $filters);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waterpool Suites - ROOMS</title>
    <?php require('includes/links.php'); ?>
    <style>
        /* These styles make the room cards look nice */

        /* Basic card style with smooth animation when hovering */
        .card {
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 30px !important;
        }

        /* Make cards lift up when hovered */
        .card:hover {
            transform: translateY(-10px); /* Move up by 10px */
            box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important; /* Add shadow */
        }

        /* Make all room images the same size */
        .card .img-fluid {
            height: 300px;
            width: 100%;
            object-fit: cover; /* Crop image to fit container */
        }

        /* Style for room names */
        .card h5 {
            font-size: 1.4rem;
            font-weight: 600;
            font-family: var(--heading-font);
        }

        /* Style for section headings (Features, Facilities, etc.) */
        .card h6 {
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* Style for buttons */
        .card .btn {
            padding: 8px 15px;
            font-weight: 500;
            transition: all 0.3s;
        }

        /* Make buttons lift up when hovered */
        .card .btn:hover {
            transform: translateY(-3px);
        }

        /* Add padding inside cards */
        .card .row {
            padding: 20px !important;
        }

        /* Style for feature badges */
        .badge {
            padding: 5px 10px;
            margin: 2px;
            font-weight: 500;
        }

        /* Add space between sections */
        .features, .facilities, .rating {
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        /* Fixed position for filters sidebar */
        .filters-container {
            position: sticky;
            top: 80px; /* Adjust this value based on your header height */
            max-height: calc(100vh - 100px); /* Adjust based on header/footer */
            overflow-y: auto;
            background-color: #fff; /* Add background color */
            z-index: 100; /* Ensure it appears above other content */
            padding: 5px; /* Add some padding */
        }

        /* Make images smaller on mobile devices */
        @media (max-width: 768px) {
            .card .img-fluid {
                height: 250px;
            }
            /* On mobile, filters are not sticky */
            .filters-container {
                position: static;
                max-height: none;
                overflow-y: visible;
                background-color: transparent;
                padding: 0;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body class="bg-white">
    <?php require('includes/header.php'); ?>

    <div class="my-5 px-4">
        <h2 class="fw-bold h-font text-center">OUR ROOMS</h2>
        <div class="h-line bg-dark"></div>
        <p class="text-center mt-3">
        designed for comfort and simplicity, offering a clean and relaxing space for guests. <br>
        Each room is equipped with essential amenities such as air-conditioning, a private bathroom, comfortable beds, and Wi-Fi access
        </p>
    </div>

    <div class="container-fluid px-4">
        <div class="row">

            <!-- FILTER SIDEBAR -->
            <div class="col-lg-3 col-md-12 mb-lg-0 mb-4 px-lg-0">
                <div class="filters-container">
                    <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow w-100">
                        <div class="container-fluid flex-lg-column align-items-stretch">
                            <h4 class="mt-2">FILTERS</h4>
                            <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterDropdown" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            <div class="collapse navbar-collapse align-items-stretch flex-column mt-2" id="filterDropdown">
                                <form method="POST" action="">
                                <div class="border bg-light p-3 rounded mb-3">
                                    <h5 class="mb-3" style="font-size: 18px;">CHECK AVAILABILITY</h5>
                                    <label class="form-label">Check-in</label>
                                    <input type="date" name="checkin" class="form-control shadow-none mb-3" value="<?php echo isset($filters['checkin']) ? $filters['checkin'] : ''; ?>">
                                    <label class="form-label">Check-out</label>
                                    <input type="date" name="checkout" class="form-control shadow-none" value="<?php echo isset($filters['checkout']) ? $filters['checkout'] : ''; ?>">
                                </div>
                                <div class="border bg-light p-3 rounded mb-3">
                                    <h5 class="mb-3" style="font-size: 18px;">FACILITIES</h5>
                                    <div class="mb-2">
                                        <input type="checkbox" id="f1" name="facilities[]" value="Wi-Fi" class="form-check-input shadow-none me-1" <?php echo (isset($filters['facilities']) && in_array('Wi-Fi', $filters['facilities'])) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="f1"> Wi-Fi</label>
                                    </div>
                                    <div class="mb-2">
                                        <input type="checkbox" id="f2" name="facilities[]" value="Aircon" class="form-check-input shadow-none me-1" <?php echo (isset($filters['facilities']) && in_array('Aircon', $filters['facilities'])) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="f2"> Aircon</label>
                                    </div>
                                    <div class="mb-2">
                                        <input type="checkbox" id="f3" name="facilities[]" value="Television" class="form-check-input shadow-none me-1" <?php echo (isset($filters['facilities']) && in_array('Television', $filters['facilities'])) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="f3"> Television</label>
                                    </div>
                                </div>
                                <div class="border bg-light p-3 rounded mb-3">
                                    <h5 class="mb-3" style="font-size: 18px;">GUESTS</h5>
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <label class="form-label">Adults</label>
                                            <input type="number" name="adults" min="1" class="form-control shadow-none" value="<?php echo isset($filters['adults']) ? $filters['adults'] : '1'; ?>">
                                        </div>
                                        <div>
                                            <label class="form-label">Children</label>
                                            <input type="number" name="children" min="0" class="form-control shadow-none" value="<?php echo isset($filters['children']) ? $filters['children'] : '0'; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mt-3">
                                    <button type="submit" class="btn btn-sm flex-grow-1 text-white custom-bg shadow-none">
                                        <i class="bi bi-filter-square me-1"></i> Apply
                                    </button>
                                    <a href="rooms.php" class="btn btn-sm flex-grow-1 btn-outline-dark shadow-none">
                                        <i class="bi bi-x-circle me-1"></i> Clear
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </nav>
                </div>
            </div>

            <!-- ROOMS LIST -->
            <div class="col-lg-9 col-md-12 px-4">
                <?php if (!empty($rooms)) { ?>
                    <?php foreach ($rooms as $room) { ?>
                        <div class="card mb-4 border-0 shadow">
                            <div class="row g-0 p-3 align-items-center">
                                <div class="col-md-4 mb-lg-0 mb-md-0 mb-3">
                                    <img src="images/rooms/<?php echo htmlspecialchars($room['image']); ?>"
                                         class="img-fluid rounded"
                                         alt="Room Image"
                                         onerror="this.src='images/placeholder.png';">
                                </div>
                                <div class="col-md-6 px-lg-3 px-md-3 px-0">
                                    <h5 class="mb-3"><?php echo htmlspecialchars($room['name']); ?></h5>
                                    <div class="features mb-3">
                                        <h6 class="mb-1">Features</h6>
                                        <div>
                                            <?php echo display_features($room['features']); ?>
                                        </div>
                                    </div>
                                    <div class="facilities mb-3">
                                        <h6 class="mb-1">Facilities</h6>
                                        <span class="badge rounded-pill bg-light text-dark text-wrap">Wifi</span>
                                        <span class="badge rounded-pill bg-light text-dark text-wrap">Television</span>
                                        <span class="badge rounded-pill bg-light text-dark text-wrap">Aircon</span>
                                        <span class="badge rounded-pill bg-light text-dark text-wrap">Water Heater</span>
                                    </div>
                                    <div class="guests mb-3">
                                        <h6 class="mb-1">Max Guests</h6>
                                        <span class="badge rounded-pill bg-light text-dark">
                                            <i class="bi bi-people-fill me-1"></i>
                                            <?php echo isset($room['max_guests']) ? htmlspecialchars($room['max_guests']) : '2'; ?> persons
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-2 mt-lg-0 mt-md-0 mt-4 text-center">
                                    <h6 class="mb-4 text-success fw-bold">₱<?php echo number_format($room['price'], 2); ?> per night</h6>
                                    <a href="booking.php?room_id=<?php echo $room['room_ID']; ?>" class="btn w-100 text-white custom-bg shadow-none mb-2">
                                        <i class="bi bi-calendar-check me-1"></i> Book Now
                                    </a>
                                    <a href="room_details.php?id=<?php echo $room['room_ID']; ?>" class="btn w-100 btn-outline-dark shadow-none">
                                        <i class="bi bi-info-circle me-1"></i> Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="col-12 text-center p-5">
                        <h3 class="text-muted">No rooms match your search</h3>
                        <p>Try adjusting your filters or <a style="text-decoration: none;" href="rooms.php">view all rooms</a>.</p>
                    </div>
                <?php } ?>


            </div>
        </div>
    </div>

    <?php require('includes/footer.php'); ?>
</body>
</html>