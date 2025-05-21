<?php
// Only start the session if it hasn't been started already
// This prevents "headers already sent" errors when this file is included after output has started
if (session_status() == PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Include database connection if not already included
if (!function_exists('mysqli_connect') || !isset($conn)) {
    require_once 'db_config.php';
}


?>
<!-- Header styles -->
<style>
    /* Styles for the user profile section in the header */

    /* Container that holds the profile picture and username */
    .user-profile-container {
        display: flex; /* Use flexbox layout */
        align-items: center; /* Center items vertically */
        transition: all 0.3s ease; /* Smooth animation */
        background-color: #f8f9fa; /* Light gray background */
        padding: 5px 10px; /* Space inside the container */
        border-radius: 20px; /* Rounded corners */
    }

    /* Style for the username text */
    .user-profile-container .user-name {
        font-weight: 500; /* Make text slightly bold */
        color: #333; /* Dark gray text color */
        max-width: 150px; /* Limit width to prevent long names from breaking layout */
        overflow: hidden; /* Hide text that doesn't fit */
        text-overflow: ellipsis; /* Add ... at the end of cut-off text */
        white-space: nowrap; /* Prevent text from wrapping to next line */
    }

    /* Change styles on smaller screens (mobile devices) */
    @media (max-width: 991.98px) {
        .user-profile-container {
            margin-bottom: 10px; /* Add space below */
            padding: 8px 15px; /* More padding on small screens */
            border-bottom: 1px solid #eee; /* Add a light border at bottom */
            width: 100%; /* Make container full width */
            justify-content: center; /* Center content horizontally */
            border-radius: 10px; /* Less rounded corners */
        }
        .user-profile-container .user-name {
            font-size: 1rem; /* Slightly larger text */
            max-width: none; /* Allow text to use full width */
        }
    }
</style>

<nav class="navbar navbar-expand-lg navbar-light bg-white px-lg-3 py-lg-2 shadow-sm sticky-top">
    <div class="container-fluid">
        <a href="index.php" class="navbar-brand d-flex align-items-center">
            <img src="images/logo/logo.png" alt="logo" width="40" height="40" class="d-inline-block align-text-top">
            <span class="navbar-brand me-5 fw-bold fs-3 h-font">Waterpool Micro</span>
        </a>
        <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
            <a class="nav-link me-2" aria-current="page" href="index.php">Home</a>
            </li>
            <li class="nav-item">
            <a class="nav-link me-2" href="rooms.php">Rooms</a>
            </li>
            <li class="nav-item">
            <a class="nav-link me-2" href="facilities.php">Amenities & Facilities</a>
            </li>
            <li class="nav-item">
            <a class="nav-link me-2" href="contact.php">Contact Us</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="about.php">About</a>
            </li>
        </ul>
            <div class="d-flex">
            <?php
                // We already started the session at the top of the file

                // Check if user is logged in
                if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
                    // User is logged in, so we need to get their information
                    $user_data = null;

                    // Try to get user data using their ID
                    if (isset($_SESSION['user_id'])) {
                        // Get user ID from session
                        $user_id = $_SESSION['user_id'];

                        // Create SQL query to get user data
                        $user_query = "SELECT * FROM users WHERE user_ID = ?";
                        $user_stmt = $conn->prepare($user_query);
                        $user_stmt->bind_param("i", $user_id); // "i" means integer
                        $user_stmt->execute();
                        $user_result = $user_stmt->get_result();

                        // Check if we found the user
                        if ($user_result->num_rows > 0) {
                            $user_data = $user_result->fetch_assoc();
                        }
                    }
                    // If we don't have user ID, try using their email
                    elseif (isset($_SESSION['user_email'])) {
                        // Get user email from session
                        $user_email = $_SESSION['user_email'];

                        // Create SQL query to get user data by email
                        $user_query = "SELECT * FROM users WHERE email = ?";
                        $user_stmt = $conn->prepare($user_query);
                        $user_stmt->bind_param("s", $user_email); // "s" means string
                        $user_stmt->execute();
                        $user_result = $user_stmt->get_result();

                        // Check if we found the user
                        if ($user_result->num_rows > 0) {
                            $user_data = $user_result->fetch_assoc();
                        }
                    }

                    // Create the container for user profile info
                    echo '<div class="d-flex align-items-center me-lg-3 me-2 user-profile-container">';

                    // Set default profile picture
                    $profile_pic = 'images/users/default.png'; // Default image

                    // If user logged in with Google, use their Google profile picture
                    if (isset($_SESSION['user_image'])) {
                        $profile_pic = $_SESSION['user_image'];
                    }

                    // Show the profile picture - handle differently based on where it comes from
                    if (strpos($profile_pic, 'http') === 0) {
                        // If it's an external URL (like Google profile picture)
                        echo '<img src="' . htmlspecialchars($profile_pic) . '" alt="Profile" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">';
                    } else {
                        // If it's a local file
                        // The onerror attribute shows the default image if the profile picture can't be loaded
                        echo '<img src="' . htmlspecialchars($profile_pic) . '" alt="Profile" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;" onerror="this.src=\'images/users/default.png\'">';
                    }

                    // Figure out what name to display
                    $display_name = '';

                    // Try different sources for the name in this order:
                    // 1. Google name
                    if (isset($_SESSION['user_name'])) {
                        $display_name = $_SESSION['user_name'];
                    }
                    // 2. Name from our database
                    elseif ($user_data && isset($user_data['name']) && !empty($user_data['name'])) {
                        $display_name = $user_data['name'];
                    }
                    // 3. Username from our database
                    elseif ($user_data && isset($user_data['username']) && !empty($user_data['username'])) {
                        $display_name = $user_data['username'];
                    }
                    // 4. Use part of their email address
                    elseif (isset($_SESSION['user_email'])) {
                        // Get everything before the @ symbol
                        $email_parts = explode('@', $_SESSION['user_email']);
                        $display_name = $email_parts[0];
                    }

                    // Show the user's name
                    echo '<span class="me-3 user-name">' . htmlspecialchars($display_name) . '</span>';
                    echo '</div>';

                    // Show My Bookings button with calendar icon
                    echo '<a href="my_bookings.php">
                            <button type="button" class="btn btn-dark shadow-none me-lg-3 me-2"><i class="bi bi-calendar-check me-1"></i> My Bookings</button>
                          </a>';

                    // Show Logout button with exit icon
                    echo '<a href="logout.php">
                            <button type="button" class="btn btn-outline-dark shadow-none me-lg-3 me-2"><i class="bi bi-box-arrow-right me-1"></i> Logout</button>
                          </a>';
                } else {
                    // User is not logged in, so show login and signup buttons
                    echo '<a href="login.php">
                            <button type="button" class="btn btn-dark shadow-none me-lg-3 me-2" data-bs-toggle="modal" data-bs-target="#LoginModal"><i class="bi bi-box-arrow-in-right me-1"></i> Login</button>
                          </a>
                          <a href="register.php">
                            <button type="button" class="btn btn-outline-dark shadow-none" data-bs-toggle="modal" data-bs-target="#registerModal"><i class="bi bi-person-plus me-1"></i> SignUp</button>
                          </a>';
                }
                ?>
            </div>
        </div>
    </div>
</nav>
