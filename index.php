<?php
// This is the homepage of our hotel website
// It shows a slideshow of images, a search form, and our available rooms

// Start the session to maintain user login state
session_start();

// Include the files we need
require('includes/db_config.php'); // Connect to database
require('includes/functions.php'); // Get helper functions

// Get all rooms from database to show in "OUR ROOMS" section
$query = "SELECT * FROM rooms ORDER BY room_ID";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waterpool Suites - HOME</title>
    <?php require('includes/links.php'); ?>

    <style>

        /* Room card styles */
        .card {
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
        }

        .card-img-top {
            height: 250px;
            object-fit: cover;
        }

        .card-body {
            padding: 1.5rem;
        }

        .card h5 {
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
        }

        /* Button styles */
        .card .btn {
            padding: 8px 15px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .card .btn:hover {
            transform: translateY(-3px);
        }

        .availability-form {
            margin-top: -50px;
            z-index: 2;
            position: relative;
        }

        @media screen and (max-width:575px) {
            .availability-form {
                margin-top: 25px;
                padding: 0 35px;
            }
        }
    </style>
</head>
<body class="bg-white">
    <?php require('includes/header.php'); ?>

    <?php if (isset($_SESSION['google_login_success'])): ?>
    <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['google_login_success']; unset($_SESSION['google_login_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Carousel -->
    <div class="container-fluid px-lg-4 mt-4">
        <div class="swiper swiper-container">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <img src="images/carousel/867612_15071314210032174190.webp" class="w-100 d-block" />
                </div>
                <div class="swiper-slide">
                    <img src="images/carousel/867612_15071314210032174192.webp" class="w-100 d-block" />
                </div>
                <div class="swiper-slide">
                    <img src="images/carousel/867612_15071314210032174209.webp" class="w-100 d-block" />
                </div>
                <div class="swiper-slide">
                    <img src="images/carousel/867612_15071314210032174219.webp" class="w-100 d-block" />
                </div>
            </div>
        </div>
    </div>

    <!-- Availability Form -->
    <div class="container availability-form">
        <div class="row">
            <div class="col-lg-12 bg-white shadow p-4 rounded">
                <h5 class="mb-4">Check Booking Availability</h5>
                <form action="available_rooms.php" method="POST">
                    <div class="row align-items-end">
                        <div class="col-lg-3 mb-3">
                            <label class="form-label" style="font-weight: 500;">Check-in</label>
                            <input type="date" name="check_in" class="form-control shadow-none" required>
                        </div>
                        <div class="col-lg-3 mb-3">
                            <label class="form-label" style="font-weight: 500;">Check-out</label>
                            <input type="date" name="check_out" class="form-control shadow-none" required>
                        </div>
                        <div class="col-lg-3 mb-3">
                            <label class="form-label" style="font-weight: 500;">Adults</label>
                            <select name="adults" class="form-select shadow-none" required>
                                <option value="1">One</option>
                                <option value="2">Two</option>
                                <option value="3">Three</option>
                                <option value="4">Four</option>
                                <option value="5">Five</option>
                            </select>
                        </div>
                        <div class="col-lg-2 mb-3">
                            <label class="form-label" style="font-weight: 500;">Children</label>
                            <select name="children" class="form-select shadow-none">
                                <option value="0">None</option>
                                <option value="1">One</option>
                                <option value="2">Two</option>
                                <option value="3">Three</option>
                                <option value="4">Four</option>
                                <option value="5">Five</option>
                            </select>
                        </div>
                        <div class="col-lg-1 mb-lg-3 mt-2">
                            <button type="submit" class="btn text-white shadow-none custom-bg w-100">
                                Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rooms Section -->
    <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font">OUR ROOMS</h2>
    <div class="container">
        <div class="row">
            <?php while ($room = mysqli_fetch_assoc($result)) { ?>
                <div class="col-lg-4 col-md-6 my-3">
                    <div class="card border-0 shadow" style="max-width: 450px; margin: auto;">
                        <img src="images/rooms/<?php echo htmlspecialchars($room['image']); ?>" class="card-img-top" alt="Room Image">
                        <div class="card-body">
                            <h5><?php echo htmlspecialchars($room['name']); ?></h5>
                            <h6 class="mb-4">₱<?php echo number_format($room['price'], 2); ?> per night</h6>
                            <div class="features mb-4">
                                <h6 class="mb-1">Features</h6>
                                <div>
                                    <?php echo display_features($room['features']); ?>
                                </div>
                            </div>
                            <div class="facilities mb-4">
                                <h6 class="mb-1">Facilities</h6>
                                <span class="badge rounded-pill bg-light text-dark text-wrap">Wifi</span>
                                <span class="badge rounded-pill bg-light text-dark text-wrap">Television</span>
                                <span class="badge rounded-pill bg-light text-dark text-wrap">Aircon</span>
                                <span class="badge rounded-pill bg-light text-dark text-wrap">Water Heater</span>
                            </div>

                            <div class="ratings mb-4">
                                <h6 class="mb-1">Rating</h6>
                                <span class="badge rounded-pill bg-light">
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <i class="bi bi-star-fill text-warning"></i>
                                </span>
                            </div>
                            <div class="d-flex justify-content-evenly mb-2">
                                <a href="booking.php?room_id=<?php echo $room['room_ID']; ?>" class="btn text-white custom-bg shadow-none">
                                    <i class="bi bi-calendar-check me-1"></i> Book Now
                                </a>
                                <a href="room_details.php?id=<?php echo $room['room_ID']; ?>" class="btn btn-outline-dark shadow-none">
                                    <i class="bi bi-info-circle me-1"></i> Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php require('includes/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".swiper-container", {
            spaceBetween: 30,
            effect: "fade",
            loop: true,
            autoplay: {
                delay: 2000,
                disableOnInteraction: false,
            },
        });

    </script>
</body>
</html>