<?php
// This page shows all the facilities and amenities of our hotel
// It displays them in a grid with icons and descriptions

// Connect to the database
require('includes/db_config.php');

// Get all facilities from the database, sorted by ID
$query = "SELECT * FROM facilities ORDER BY facility_id";
$result = mysqli_query($conn, $query);
?>

<?php
// Start the session to maintain user login state
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waterpool Suites - FACILITIES</title>
    <?php require('includes/links.php'); ?>

    <style>
        /* This style makes facility cards "pop up" when hovered */
        .pop:hover {
            border-top-color: var(--teal) !important; /* Change border color to teal */
            transform: scale(1.03); /* Make card slightly bigger */
            transition: all 0.3s; /* Add smooth animation */
        }
    </style>
</head>
<body class="bg-white">
    <?php require('includes/header.php'); ?>

    <!-- Facilities Section -->
    <div class="my-5 px-4">
        <h2 class="fw-bold h-font text-center">OUR AMENITIES & FACILITIES</h2>
        <div class="h-line bg-dark"></div>
        <p class="text-center mt-3">
        designed to provide guests with a comfortable and enjoyable stay.
        </p>
    </div>

    <div class="container">
        <div class="row">
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($facility = mysqli_fetch_assoc($result)) {
                echo "
                <div class='col-lg-4 col-md-6 mb-5 px-4'>
                    <div class='bg-white rounded shadow p-4 border-top border-4 border-dark pop'>
                        <div class='d-flex align-items-center mb-2'>
                            <img src='images/features/{$facility['icon']}' alt='{$facility['name']}' width='48px' onerror=\"this.src='images/features/default_feature.png';\">
                            <h5 class='m-0 ms-3'>{$facility['name']}</h5>
                        </div>
                        <p>{$facility['description']}</p>
                    </div>
                </div>
                ";
            }
        } else {
            echo "<h5 class='text-center'>No facilities available at the moment.</h5>";
        }
        ?>
        </div>
    </div>

    <?php require('includes/footer.php'); ?>
</body>
</html>