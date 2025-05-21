<?php
// This is the login page
// Users can login with username/email and password or with Google

// Start a session to keep track of logged in users
session_start();

// Connect to the database
require('includes/db_config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Waterpool Suites</title>
    <?php require('includes/links.php'); ?>
    <style>
        .login-background {
            background-image: url('images/carousel/867612_15071314210032174192.webp');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
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

        .form-label {
            font-weight: 600; /* Bolder labels for better visibility on glass background */
            font-size: 16px; /* Consistent font size */
            margin-bottom: 8px; /* More space between label and input */
            color: #222; /* Darker text for better readability on glass background */
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5); /* Subtle text shadow for better visibility */
        }

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
                    <h2 class="fw-bold h-font text-center" style="font-size: 2.5rem;">Login</h2>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success" role="alert">
                            <?=$_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="login_validation.php">
                        <div class="mb-3">
                            <label class="form-label">Username or Email</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="g-recaptcha" data-sitekey="6LcgMT4rAAAAAGljCtA-2DuY_kxbfW7wJbDWLM1q"></div>
                        <br>
                        <button type="submit" class="btn btn-login w-100">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span>LOGIN</span>
                        </button>
                    </form>
                    <div class="text-center mt-3">
                        <p class="mb-2">OR</p>
                        <a href="googleAuth/google-login.php" class="btn btn-outline-danger w-100" style="height: 50px; font-size: 18px; font-weight: 600; display: flex; align-items: center; justify-content: center;">
                            <i class="fab fa-google" style="font-size: 22px; margin-right: 10px;"></i>
                            <span>CONTINUE WITH GOOGLE</span>
                        </a>
                    </div>
                    <br>
                    <p>Don't have an account? <a href="register.php" class="no-underline">SignUp</a></p>
                    <p>Forgot Password? <a href="forgot-password.php" class="no-underline">Click here</a></p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>