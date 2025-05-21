<?php
// This is the add room page
// It lets admins create new rooms with details and an image

// Start a session to check if admin is logged in
session_start();

// If admin is not logged in, send them to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Connect to the database
require('includes/db_config.php');

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and clean up the form data
    $name = trim($_POST['name']); // Remove extra spaces
    $price = floatval($_POST['price']); // Convert to decimal number
    $features = trim($_POST['features']); // Remove extra spaces
    $max_guests = intval($_POST['max_guests']); // Convert to integer

    // Make sure all required fields are filled correctly
    if (empty($name) || empty($features) || $price <= 0 || $max_guests <= 0) {
        echo "<script>alert('Please fill all required fields with valid values.');</script>";
    } else {
        // Check if an image was uploaded without errors
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            // Check if the file is an allowed image type
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            $file_type = $_FILES['image']['type'];

            if (!in_array($file_type, $allowed_types)) {
                echo "<script>alert('Only JPG, JPEG, PNG and GIF files are allowed.');</script>";
            } else {
                // Get the original filename
                $original_filename = basename($_FILES['image']['name']);
                $image_tmp_name = $_FILES['image']['tmp_name'];

                // Check if this image already exists in the rooms folder
                $upload_dir = "images/rooms/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // First check if the exact same filename already exists
                if (file_exists($upload_dir . $original_filename)) {
                    // If it exists, use the existing filename without saving again
                    $image_name = $original_filename;
                    $image_path = $upload_dir . $image_name;

                    // We'll skip the file upload since it already exists
                    $file_already_exists = true;
                } else {
                    // If it doesn't exist, create a unique filename and save it
                    $image_name = time() . "_" . $original_filename;
                    $image_path = $upload_dir . $image_name;
                    $file_already_exists = false;
                }

                // Only move the file if it doesn't already exist
                if ($file_already_exists || move_uploaded_file($image_tmp_name, $image_path)) {
                    try {
                        // Set default facilities for all rooms
                        // This automatically adds these facilities to every room without needing a form field
                        $default_facilities = "Wi-Fi, Television, Aircon, Water Heater";

                        // Add the room to the database with default facilities
                        $query = "INSERT INTO rooms (name, price, max_guests, features, facilities, image) VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($query);

                        if (!$stmt) {
                            throw new Exception("Database query preparation failed: " . $conn->error);
                        }

                        // Add parameters to the query
                        // "sdisss" means string, double, integer, string, string, string
                        $stmt->bind_param("sdisss", $name, $price, $max_guests, $features, $default_facilities, $image_name);

                        // Run the query
                        if ($stmt->execute()) {
                            // If successful, show message and go back to room list
                            echo "<script>
                                    alert('Room added successfully!');
                                    window.location.href = 'manage_rooms.php';
                                  </script>";
                            exit;
                        } else {
                            throw new Exception("Failed to add room: " . $stmt->error);
                        }
                    } catch (Exception $e) {
                        // If there was a database error
                        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
                        // Delete the uploaded image if we couldn't add it to the database
                        if (file_exists($image_path)) {
                            unlink($image_path);
                        }
                    }
                } else {
                    echo "<script>alert('Failed to upload image. Please try again.');</script>";
                }
            }
        } else {
            // If no image was uploaded or there was an error
            $error_message = "Please select an image file.";

            // Check what kind of upload error occurred
            if (isset($_FILES['image']) && $_FILES['image']['error'] > 0) {
                switch ($_FILES['image']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                        $error_message = "The uploaded file is too large (exceeds PHP limit).";
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $error_message = "The uploaded file is too large (exceeds form limit).";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $error_message = "The file was only partially uploaded.";
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $error_message = "No file was uploaded.";
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $error_message = "Missing a temporary folder.";
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $error_message = "Failed to write file to disk.";
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $error_message = "A PHP extension stopped the file upload.";
                        break;
                    default:
                        $error_message = "Unknown upload error.";
                }
            }
            echo "<script>alert('Error: " . $error_message . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Room - Admin Panel</title>
    <?php require('includes/links.php'); ?>
    <link rel="stylesheet" href="styles/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <?php require('includes/admin_sidebar.php'); ?>

        <!-- Main Content -->
        <div class="admin-content">
            <div class="container-fluid">
                <div class="mb-4">
                    <h2 class="fw-bold h-font">Add New Room</h2>
                </div>

        <div class="row justify-content-center mt-4">
            <div class="col-lg-8 bg-white rounded shadow p-4">
                <!-- Form for adding a new room - note enctype is needed for file uploads -->
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <!-- Room name input -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Room Name</label>
                            <input type="text" name="name" class="form-control shadow-none" required>
                        </div>

                        <!-- Price input - allows decimal numbers -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Price (per night)</label>
                            <input type="number" name="price" min="0" step="0.01" class="form-control shadow-none" required>
                        </div>

                        <!-- Maximum guests input - default is 2 -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Max Guests</label>
                            <input type="number" name="max_guests" min="1" value="2" class="form-control shadow-none" required>
                        </div>
                    </div>

                    <!-- Features textarea - for listing room amenities -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Features</label>
                        <textarea name="features" class="form-control shadow-none" rows="4" required></textarea>
                    </div>



                    <!-- Image upload field - only accepts image files -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Room Image</label>
                        <input type="file" name="image" accept="image/*" class="form-control shadow-none" required>
                    </div>



                    <!-- Form buttons -->
                    <div class="d-flex">
                        <!-- Submit button -->
                        <button type="submit" class="btn btn-dark shadow-none me-2">
                            <i class="bi bi-plus-circle"></i> Add Room
                        </button>

                        <!-- Cancel button - returns to room list -->
                        <a href="manage_rooms.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
            </div>
        </div>
    </div>

    <script src="js/admin.js"></script>
</body>
</html>