<?php
// This is the new password page
// Users can set a new password after verifying their code

// Start a session to get saved information
session_start();

// Connect to the database
require 'includes/db_config.php';

// Make sure the user verified their code first
if (!isset($_SESSION['email']) || !isset($_SESSION['reset_code_verified']) || !$_SESSION['reset_code_verified']) {
    header('Location: send-code.php');
    exit();
}

// When the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the passwords from the form
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Check if both passwords match
    if ($newPassword === $confirmPassword) {
        // Make the password secure by hashing it
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Save the new password in the database
        $stmt = $pdo_database->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $_SESSION['reset_email']]);

        // Clear the temporary information
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_code_verified']);

        // Show success message and go to login page
        $_SESSION['success'] = "Your password has been reset successfully.";
        header('Location: login.php');
        exit();

    } else {
        // Show error if passwords don't match
        $_SESSION['error'] = "Passwords do not match. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Password</title>
    <?php require('includes/links.php'); ?>
    <style>
        /* Style for the full-screen background image */
        .login-background {
            background-image: url('images/carousel/867612_15071314210032174192.webp');
            background-size: cover; /* Make image cover the whole screen */
            background-position: center; /* Center the image */
            background-attachment: fixed; /* Keep image fixed when scrolling */
            min-height: 100vh; /* Make it at least full screen height */
            display: flex; /* Use flexbox for centering */
            align-items: center; /* Center vertically */
            justify-content: center; /* Center horizontally */
            padding: 2rem 0;
        }

        /* Glassmorphism container */
        .transparent-container {
            background-color: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        /* Style for the page title */
        .login-title {
            color: #343a40; /* Dark gray color */
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        /* Glassmorphism styling for form inputs */
        .form-control {
            height: 50px; /* Increased height */
            border-radius: 10px; /* Increased border radius */
            padding: 10px 15px; /* More padding for better text positioning */
            font-size: 16px; /* Consistent font size */
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #333;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 10px rgba(59, 139, 150, 0.3);
        }

        /* Style for form labels */
        .form-label {
            font-weight: 600; /* Bolder labels for better visibility on glass background */
            font-size: 16px; /* Consistent font size */
            margin-bottom: 8px; /* More space between label and input */
            color: #222; /* Darker text for better readability on glass background */
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5); /* Subtle text shadow for better visibility */
        }

        /* Glassmorphism login button */
        .btn-login {
            background-color: rgba(59, 139, 150, 0.7); /* Semi-transparent teal */
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(59, 139, 150, 0.3);
            color: white;
            transition: all 0.3s ease;
            height: 50px; /* Increased height */
            font-size: 18px; /* Larger font size */
            font-weight: 600; /* Bolder text */
            display: flex;
            align-items: center;
            justify-content: center;
            letter-spacing: 0.5px; /* Improved text spacing */
            border-radius: 10px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2); /* Subtle text shadow */
        }

        /* Style for submit button when hovered */
        .btn-login:hover {
            background-color: rgba(56, 169, 184, 0.8); /* Semi-transparent teal hover */
            border-color: rgba(56, 169, 184, 0.4);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .btn-login i {
            font-size: 22px; /* Larger icon */
            margin-right: 10px; /* More space between icon and text */
        }

        /* Remove underline from links */
        .no-underline {
            text-decoration: none;
            color: var(--teal);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .no-underline:hover {
            color: var(--teal_hover);
        }
    </style>
</head>
<body>
    <div class="login-background">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7 col-sm-10 transparent-container">
                    <h2 class="fw-bold h-font text-center login-title">Set New Password</h2>
                    <?php
                    if (isset($_SESSION['success'])) {
                        echo '<div class="alert alert-success text-center">' . $_SESSION['success'] . '</div>';
                        unset($_SESSION['success']);
                    }
                    if (isset($_SESSION['error'])) {
                        echo '<div class="alert alert-danger text-center">' . $_SESSION['error'] . '</div>';
                        unset($_SESSION['error']);
                    }
                    ?>
                    <form action="new-password.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control shadow-none" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control shadow-none" required>
                        </div>
                         <br>
                        <button type="submit" class="btn btn-login w-100 shadow-none">
                            <i class="bi bi-key"></i>
                            <span>RESET PASSWORD</span>
                        </button>
                    </form>
                    <br>
                </div>
            </div>
        </div>
    </div>
</body>
</html>