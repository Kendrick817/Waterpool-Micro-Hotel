<?php
// This page shows detailed information about a specific room
// It displays room features, price, and related rooms

// Start the session to maintain user login state
session_start();

// Include required files
require('includes/db_config.php'); // Connect to database
require('includes/functions.php'); // Get helper functions

// Check if we have a valid room ID in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $room_id = $_GET['id'];

    // Get the details of this specific room from the database
    $query = "SELECT * FROM rooms WHERE room_ID = ?";
    $stmt = $conn->prepare($query); // Use prepared statement for security
    if (!$stmt) {
        die("Error preparing query: " . $conn->error);
    }
    $stmt->bind_param("i", $room_id); // "i" means integer
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the room exists
    if ($result->num_rows == 1) {
        $room = $result->fetch_assoc(); // Get room data as array
    } else {
        // If room doesn't exist, show error and go to homepage
        echo "<script>alert('Room not found!'); window.location.href='index.php';</script>";
        exit;
    }

    // Get 4 random rooms (excluding current room) to show as related rooms
    $related_rooms_query = "SELECT * FROM rooms WHERE room_ID != ? ORDER BY RAND() LIMIT 4";
    $related_stmt = $conn->prepare($related_rooms_query);
    if (!$related_stmt) {
        die("Error preparing related rooms query: " . $conn->error);
    }
    $related_stmt->bind_param("i", $room_id);
    $related_stmt->execute();
    $related_rooms_result = $related_stmt->get_result();
} else {
    // If no valid room ID in URL, show error and go to homepage
    echo "<script>alert('Invalid Room ID!'); window.location.href='index.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Details - <?php echo htmlspecialchars($room['name']); ?></title>
    <?php require('includes/links.php'); ?>

    <style>
        /* Style for the availability form */
        .availability-form {
            margin-top: -50px; /* Move form up to overlap with section above */
            z-index: 2; /* Make sure form appears above other elements */
            position: relative;
        }

        /* Change form style on small screens */
        @media screen and (max-width:575px) {
            .availability-form {
                margin-top: 25px; /* Add space on top for mobile */
                padding: 0 35px; /* Add padding on sides */
            }
        }

        /* Style for the main room image */
        .room-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover; /* Crop image to fit container */
            border-radius: 10px; /* Rounded corners */
        }

        /* Style for the related room images */
        .related-room-image {
            width: 100%;
            height: 200px;
            object-fit: cover; /* Crop image to fit container */
            border-radius: 10px; /* Rounded corners */
        }

        /* Style for the main room details container */
        .room-details-container {
            background-color: #fff;
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); /* Add shadow */
            padding: 25px;
            margin-bottom: 30px;
            transition: transform 0.3s, box-shadow 0.3s; /* Smooth animation */
        }

        /* Make container lift up when hovered */
        .room-details-container:hover {
            transform: translateY(-5px); /* Move up slightly */
            box-shadow: 0 10px 25px rgba(0,0,0,0.15); /* Stronger shadow */
        }

        /* Style for the facilities list */
        .facilities-list {
            padding-left: 1.2rem;
        }

        /* Add space between list items */
        .facilities-list li {
            margin-bottom: 0.5rem;
        }

        /* Style for the room name */
        .room-title {
            font-family: var(--heading-font);
            margin-bottom: 15px;
        }

        /* Style for the room price */
        .room-price {
            font-weight: 600; /* Make text bold */
            margin-bottom: 20px;
        }

        /* Style for related room cards */
        .hover-card {
            transition: transform 0.3s, box-shadow 0.3s; /* Smooth animation */
        }

        /* Make related room cards lift up when hovered */
        .hover-card:hover {
            transform: translateY(-10px); /* Move up */
            box-shadow: 0 15px 30px rgba(0,0,0,0.15) !important; /* Add shadow */
        }
    </style>
</head>
<body>
    <?php require('includes/header.php'); ?>

    <!-- Room Details Section -->
    <div class="container my-5">
        <div class="room-details-container">
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <img src="images/rooms/<?php echo htmlspecialchars($room['image']); ?>"
                         alt="Room Image"
                         class="room-image"
                         onerror="this.src='images/placeholder.png';">
                </div>
                <div class="col-lg-6">
                    <h2 class="fw-bold room-title"><?php echo htmlspecialchars($room['name']); ?></h2>
                    <h4 class="text-success room-price">₱<?php echo number_format($room['price'], 2); ?> per night</h4>
                    <div class="room-features mb-4">
                        <h6 class="fw-bold mb-2">Room Features</h6>
                        <div>
                            <?php echo display_features($room['features']); ?>
                        </div>
                    </div>
                    <div class="room-facilities mb-4">
                        <h6 class="fw-bold mb-2">Facilities</h6>
                        <ul class="facilities-list">
                            <li>Wifi</li>
                            <li>Television</li>
                            <li>Air Conditioning</li>
                            <li>Room Heater</li>
                        </ul>
                    </div>
                    <div class="booking-action">
                        <a href="booking.php?room_id=<?php echo $room['room_ID']; ?>" class="btn text-white custom-bg shadow-none">
                            <i class="bi bi-calendar-check me-1"></i> Book Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Rooms Section -->
    <div class="container my-5">
        <h3 class="fw-bold h-font text-center mb-4">Related Rooms</h3>
        <div class="row">
            <?php if ($related_rooms_result && $related_rooms_result->num_rows > 0) { ?>
                <?php while ($related_room = $related_rooms_result->fetch_assoc()) { ?>
                    <div class="col-lg-3 col-md-6 my-3">
                        <div class="card border-0 shadow hover-card" style="max-width: 350px; margin: auto;">
                            <img src="images/rooms/<?php echo htmlspecialchars($related_room['image']); ?>"
                                 class="related-room-image"
                                 alt="Room Image"
                                 onerror="this.src='images/placeholder.png';">
                            <div class="card-body">
                                <h5><?php echo htmlspecialchars($related_room['name']); ?></h5>
                                <h6 class="mb-4 text-success">₱<?php echo number_format($related_room['price'], 2); ?> per night</h6>
                                <a href="room_details.php?id=<?php echo $related_room['room_ID']; ?>" class="btn btn-outline-dark shadow-none">
                                    <i class="bi bi-info-circle me-1"></i> Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="col-12 text-center">
                    <h5 class="text-muted">No related rooms available.</h5>
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
                delay: 2500,
                disableOnInteraction: false,
            },
        });
    </script>
</body>
</html>